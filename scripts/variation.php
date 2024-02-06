<?php
    

    function sm_get_variation($oid,$data,$id,$con,$attributes,$init){
        $path = $init["pluginpath"];
        
        
        $con = mysqli_connect($init["dbhost"],$init["dbuser"],$init["dbpass"],$init["dbname"]);
        if ($con->connect_error) {
            die("Connection failed: " . $con->connect_error);
        }
        $con -> set_charset("utf8");
        $sql = "SELECT * FROM ".$init["dbpre"]."postmeta WHERE meta_value = '$oid' AND meta_key = '_oid'";
        $ex_result = $con->query($sql);
        
        
        if ($ex_result->num_rows > 0) {
		    while($row = $ex_result->fetch_assoc()) {
    		    $wp_id = $row["post_id"];
    		    $post_type = get_post_type($wp_id);
    		    
    		    $post_parent = wp_get_post_parent_id($row["post_id"]);
    		    
    		    //echo $wp_id.' => '.$post_parent.' => '.$post_type.'</br>';
    		    //echo get_post_type($post_parent).'</br>';
    		    
    		    if($post_type == 'product'){                                                                                    // If post_type is product means that the variation was added as regular product
    		        wp_delete_post($wp_id,true);                                                                                // delete the regular product
    		        // add the variation
        		    echo 'adding variation... ('.$oid.')</br>';
                    add_variation($oid,$data,$path,$id,$con,$attributes,$init);
    		    }elseif(get_post_type($post_parent) == ""){                                                                     // if parent is not product then delete variation
    		        wp_delete_post($wp_id,true);
    		        echo 'update variation... ('.$oid.')</br>';
                    update_variation($oid,$data,$path,$id,$con,$attributes,$init,$wp_id);
    		    }else{                                                                                                          // If post_type is product_variation means that the variation exists and needs update
    		        
        		    echo 'update variation... ('.$oid.')</br>';
                    update_variation($oid,$data,$path,$id,$con,$attributes,$init,$wp_id);
    		    }
    		    
		    }
        }else{
            echo 'adding variation... ('.$oid.')</br>';    
            add_variation($oid,$data,$path,$id,$con,$attributes,$init);
        }
        
    }
    
    function update_variation($oid,$data,$path,$id,$con,$attributes,$init,$wp_id){
        //require($path.'init'.$init["lang"].'.php');
         $data_array = json_decode($data);
        
		if(empty($data_array)){
		    echo '<em>Σφάλμα μετατροπής json σε array</em></br>';
		    $sql = "UPDATE smartenize SET status = '100' WHERE id = '$id'";
            $con->query($sql);
		}else{
            $parent_id = wp_get_post_parent_id($wp_id);
            $current_attributes = get_post_meta( $parent_id , '_product_attributes')[0];
            $atts_array = [];
            $barcode_atts = $data_array->_barcode_attributes;
            //print_r($barcode_atts);
            //echo '</br>';
            
            $supplier = $data_array->_SupplierCode;
            $sale_price_dates_from = $data_array->_sale_price_dates_from;
            $sale_price_dates_to = $data_array->_sale_price_dates_to;
            $date_from_tmp = explode('/',$sale_price_dates_from);
            $date_to_tmp = explode('/',$sale_price_dates_to);
            $fix_date_from = $date_from_tmp[2].'-'.$date_from_tmp[1].'-'.$date_from_tmp[0];
            $fix_date_to = $date_to_tmp[2].'-'.$date_to_tmp[1].'-'.$date_to_tmp[0];
            //echo $sale_price_dates_from.' => '.strtotime($sale_price_dates_from).' => '.strtotime($fix_date_from).'</br>';
            //echo $sale_price_dates_to.' => '.strtotime($sale_price_dates_to).' => '.strtotime($fix_date_to).'</br>';
            
            if(!empty($barcode_atts)){
            
                for($i = 0; $i < count($barcode_atts); $i++){
                    $att_oid = $data_array->_barcode_attributes[$i]->attr_Oid;
                    $option_oid = $data_array->_barcode_attributes[$i]->option_Value;
                    echo $att_oid.' => '.$option_oid.'</br>';
                    $temp_oid = explode('-',$att_oid);
                    unset($temp_oid[(count($temp_oid) - 1)]);
                    unset($temp_oid[(count($temp_oid) - 1)]);
                    unset($temp_oid[(count($temp_oid) - 1)]);
                    $att_slug = 'pa_'.implode('-',$temp_oid);
                    $atts_array[] = $att_slug;
                    
                    $sql123 = "SELECT * FROM ".$init["dbpre"]."termmeta WHERE meta_value = '$option_oid' AND meta_key = 'Oid'";
                    $ex_result5 = $con->query($sql123);
                    $option_id = '';
                    $option_slug = '';
                    $option_name = '';
                    if ($ex_result5->num_rows > 0) {                                                                                                                                            
                    	while($row5 = $ex_result5->fetch_assoc()) {
                    	    $option_id = $row5["term_id"];
                    	    $option_slug = get_term_by('id',$row5["term_id"],$att_slug)->slug;
                    	    $option_name = get_term_by('id',$row5["term_id"],$att_slug)->name;
                    	}
                    }
                    
                    foreach($atts_array as $atts){
                        //echo $atts.'<br/>';
                        $current_attributes[$atts] = array('name' => $atts,'position' => '1','value' => '','is_visible' => '0','is_variation' => '1','is_taxonomy' => '1');    
                    }
                    
                    update_post_meta($wp_id,"attribute_".$att_slug,$option_slug);
                    
                    $post_term_names =  wp_get_post_terms( $parent_id, $att_slug, array('fields' => 'slugs') );
                    
                    if( ! in_array( $option_slug, $post_term_names ) ){
                        $sql456 = "INSERT INTO ".$init["dbpre"]."term_relationships (object_id,term_taxonomy_id,term_order) VALUES ('$parent_id','$option_id','0')";
                        $ex_result456 = $con->query($sql456);
                    }else{
                        
                    }
                    
                }
                
                
                $metas = get_post_meta($wp_id);
                foreach($metas as $key => $value){
                    if(substr($key,0,13) == "attribute_pa_"){
                        
                        if(in_array(substr($key,10),$atts_array)){
                            
                        }else{
                            delete_post_meta($wp_id,$key);
                            foreach($current_attributes as $k => $v){
                                if($k == substr($key,10)){
                                    unset($current_attributes[substr($key,10)]);
                                }
                            }
                        }
                        
                    }
                }
                
                foreach($current_attributes as $k => $v){
                    if($v["is_variation"] == 1){
                        if(in_array($k,$atts_array)){
                            
                        }else{
                            unset($current_attributes[$k]);
                        }
                    }
                }
                
                
                
                update_post_meta($parent_id,'_product_attributes',$current_attributes);
                update_post_meta($wp_id,'_supplier',$supplier); 
                if($data_array->_variation_description != ""){
                    update_post_meta($wp_id,'_variation_description',$data_array->_variation_description);    
                }
                if($data_array->_tax_status != ""){
                    update_post_meta($wp_id,'_tax_status',$data_array->_tax_status);    
                }
                if($data_array->_tax_class != ""){
                    update_post_meta($wp_id,'_tax_class',$data_array->_tax_class);    
                }
                //echo $data_array->_manage_stock.'</br>';
                //echo $data_array->_backorders.'</br>';
                //echo $data_array->_stock.'</br>';
                update_post_meta($wp_id,'_manage_stock',$data_array->_manage_stock);
                if(($data_array->_manage_stock == "no") && ($data_array->_backorders == 'notify')){
                    update_post_meta($wp_id,'_stock_status','onbackorder');
                }else{
                    update_post_meta($wp_id,'_stock_status',$data_array->_stock_status);
                }
                update_post_meta($wp_id,'_backorders',$data_array->_backorders);
                if($data_array->_stock != ""){
                    //update_post_meta($wp_id,'_stock',$data_array->_stock);    
                }
                //echo $wp_id.' => '.$data_array->_stock.'</br>';
                update_post_meta($wp_id,'_stock',$data_array->_stock);
                if($data_array->_price != ""){
                    update_post_meta($wp_id,'_price',$data_array->_price);
                    update_post_meta($wp_id,'_regular_price',$data_array->_price);
                }
                
                if($data_array->_sale_price != ""){
                    update_post_meta($wp_id,'_sale_price',$data_array->_sale_price);    
                }else{
                    update_post_meta($wp_id,'_sale_price','');    
                }
                
                if($data_array->_sale_price_dates_from != "1/1/0001"){
                    update_post_meta($wp_id,'_sale_price_dates_from',strtotime($fix_date_from));    
                }else{
                    delete_post_meta($wp_id,'_sale_price_dates_to');    
                }
                
                if($data_array->_sale_price_dates_to != "1/1/0001"){
                    update_post_meta($wp_id,'_sale_price_dates_to',strtotime($fix_date_to));    
                }else{
                    delete_post_meta($wp_id,'_sale_price_dates_to');    
                }
                
                if($data_array->_barcode_attributes != ""){
                    update_post_meta($wp_id,'_barcode_attributes',$data_array->_barcode_attributes);    
                }
                
                if($data_array->_sku != ""){
                    update_post_meta($wp_id,'_sku',$data_array->_sku);    
                }
                
                if($data_array->_parentOid != ""){
                    update_post_meta($wp_id,'_parentOid',$data_array->_parentOid);    
                }
                
                if(get_post_meta($wp_id,'_regular_price') == "0"){
                
                }else{
                    $sql7 = "UPDATE ".$init["dbpre"]."posts SET post_status = 'publish' WHERE ID = '".$wp_id."'";
                    $ex_result7 = $con->query($sql7);
                }
                
                /* Refresh parent */
                
                /*
                $get_parent_oid = get_post_meta($parent_id,'_oid')[0];
                
                $sql_refresh_parent = "SELECT * FROM smartenize WHERE entity = 'Product' AND oid = '$get_parent_oid' ORDER BY entry_date DESC LIMIT 1";
                $ex_result_refresh_parent = $con->query($sql_refresh_parent);
            
                if ($ex_result_refresh_parent->num_rows > 0) {
    		        while($row_refreh_parent = $ex_result_refresh_parent->fetch_assoc()) {
    		            $id = $row_refreh_parent["id"];
    		            $sql_reset = "UPDATE smartenize SET status = '' WHERE id = '$id'";
    		            $con->query($sql_reset);
    		        }
                }
                */
                
                $sql = "UPDATE smartenize SET status = '2' WHERE id = '$id'";
                $con->query($sql);
                
                wc_delete_product_transients($parent_id);
                wc_update_product_lookup_tables();
            }else{
                $sql = "UPDATE smartenize SET status = '107' WHERE id = '$id'";
                $con->query($sql);
                
                $message = 'This variation has empty attributes ('.$data_array->_variation_description.')';
                $sql_log = "INSERT INTO smartenize_log (sid, message) VALUES ('".$id."','".$message."')";
                $con->query($sql_log);
            }
            
            
            
		}
    }
    
    function add_variation($oid,$data,$path,$id,$con,$attributes,$init){
        require($path.'init'.$init["lang"].'.php');
        $data_array = json_decode($data);
        
		if(empty($data_array)){
		    echo '<em>Σφάλμα μετατροπής json σε array</em></br>';
		    $sql = "UPDATE smartenize SET status = '100' WHERE id = '$id'";
            $con->query($sql);
		}else{
		    
            $variation_description = $data_array->_variation_description;
            $tax_status = $data_array->_tax_status;
            $tax_class = $data_array->_tax_class;
            $manage_stock = $data_array->_manage_stock;
            $backorders = $data_array->_backorders;
            $stock = $data_array->_stock;
            $stock_status = $data_array->_stock_status;
            $price = $data_array->_price;
            $sale_price = $data_array->_sale_price;
            $sale_price_dates_from = $data_array->_sale_price_dates_from;
            $sale_price_dates_to = $data_array->_sale_price_dates_to;
            $supplier = $data_array->_SupplierCode;
            $date_from_tmp = explode('/',$sale_price_dates_from);
            $date_to_tmp = explode('/',$sale_price_dates_to);
            $fix_date_from = $date_from_tmp[2].'-'.$date_from_tmp[1].'-'.$date_from_tmp[0];
            $fix_date_to = $date_to_tmp[2].'-'.$date_to_tmp[1].'-'.$date_to_tmp[0];
            //echo $sale_price_dates_from.' => '.strtotime($sale_price_dates_from).' => '.strtotime($fix_date_from).'</br>';
            //echo $sale_price_dates_to.' => '.strtotime($sale_price_dates_to).' => '.strtotime($fix_date_to).'</br>';
            
            $barcode_attributes = $data_array->_barcode_attributes;
            $sku = $data_array->_sku;
            $product_parent = $data_array->_parentOid;
            
            if(!empty($barcode_attributes)){
            
                if($product_parent != ""){
                    
                    $product_parent_id = 0;
                
                    $get_parent = "SELECT * FROM ".$init["dbpre"]."postmeta WHERE meta_value = '$product_parent' AND meta_key = '_oid'";
                    $ex_result5 = $con->query($get_parent);
                    
                    if ($ex_result5->num_rows > 0) {                                                                                                                                            
                    	while($row5 = $ex_result5->fetch_assoc()) {
                            $product_parent_id = $row5["post_id"];	    
                    	}
                    }
                    if($product_parent_id != ""){
                    // Get parent product Object
                    $product = wc_get_product($product_parent_id);
                    if($product){
                    //print_r($product);    
                    
                        // Construct variation data array
                        $variation_post = array(                                                                                                                                                        
                            'post_title'  => $product->get_name(),
                            'post_name'   => 'product-'.$product_parent_id.'-variation',
                            'post_status' => 'publish',
                            'post_parent' => $product_parent_id,
                            'post_type'   => 'product_variation',
                            'guid'        => $product->get_permalink()
                        );
                        // Add variation as post and get the variation ID
                        $variation_id = wp_insert_post( $variation_post );                                                                                                                              
                        echo $product_parent_id.' => '.$variation_id.'</br>';
                        
                        
                        $current_attributes = get_post_meta( $product_parent_id , '_product_attributes')[0];
                        
                        // Scan all the attributes connected with the variation
            		    foreach($barcode_attributes as $b_atts){                                                                                                                                                   
            		        $option = $b_atts->option_Value;
            		        //echo $option.'</br>';
            		        // Get term_id of attribute option 
            		        $find_option = "SELECT * FROM ".$init["dbpre"]."termmeta WHERE meta_value LIKE '".$option."' AND meta_key = 'Oid' LIMIT 1";                                                 
            		        $ex_result1 = $con->query($find_option);
            		        
            		        // The option exist
            		        if ($ex_result1->num_rows > 0) {                                                                                                                                            
                    		    while($row1 = $ex_result1->fetch_assoc()) {
                    		        //echo $row1["term_id"].' => ';
                    		        $db_option_id = $row1["term_id"];
                    		        $db_option_name = '';
                    		        $db_option_slug = '';
                    		        $db_option_taxonomy = '';
                    		        $find_option_info = "SELECT * FROM ".$init["dbpre"]."terms WHERE term_id = ".$db_option_id;                                                                         
                    		        
                    		        // Get attribute option NAME and SLUG
                    		        $ex_result3 = $con->query($find_option_info);
                        		    if ($ex_result3->num_rows > 0) {
                    		            while($row3 = $ex_result3->fetch_assoc()) {
                    		                $db_option_name = $row3["name"];
                    		                $db_option_slug = $row3["slug"];
                    		            }
                        		    }
                    		        
                    		        // Get taxonomy of option
                        		    $find_taxonomy = "SELECT * FROM ".$init["dbpre"]."term_taxonomy WHERE term_id = ".$db_option_id;                                                                    
                        		    $ex_result2 = $con->query($find_taxonomy);
                        		    
                        		    // If taxonomy exist
                        		    if ($ex_result2->num_rows > 0) { // taxonomy exists
                    		            while($row2 = $ex_result2->fetch_assoc()) {
                        		            $db_option_taxonomy = $row2["taxonomy"];
                        		            $current_attributes[$db_option_taxonomy] = array('name' => $db_option_taxonomy,'position' => '1','value' => '','is_visible' => '0','is_variation' => '1','is_taxonomy' => '1');
                        		            
                        		            // Get the post Terms names from the parent variable product.
                        		            $post_term_names =  wp_get_post_terms( $product_parent_id, $db_option_taxonomy, array('fields' => 'names') );
                        		            
                        		            // Check if the post term exist and if not we set it in the parent variable product.
                                            if( ! in_array( $db_option_name, $post_term_names ) ){
                                                wp_set_post_terms( $product_parent_id, $db_option_name, $db_option_taxonomy, true );
                                            }else{
                                                
                                            }
                                            
                                            // Set/save the attribute data in the product variation
                                            update_post_meta( $variation_id, 'attribute_'.$db_option_taxonomy, $db_option_slug );
                        		            
                    		            }
                        		    }else{ 
                        		        // If taxonomy not exist
                        		    }
                    		    }
                            }else{ // option not exist
                                
                            }
            		        
            		   }
            		   
            		    update_post_meta($variation_id,'_supplier',$supplier); 
            		   
            		     if($data_array->_variation_description != ""){
                            update_post_meta($variation_id,'_variation_description',$data_array->_variation_description);    
                        }
                        if($data_array->_tax_status != ""){
                            update_post_meta($variation_id,'_tax_status',$data_array->_tax_status);    
                        }
                        if($data_array->_tax_class != ""){
                            update_post_meta($variation_id,'_tax_class',$data_array->_tax_class);    
                        }
                        if(($data_array->_manage_stock == "no") && ($data_array->_backorders == 'notify')){
                            update_post_meta($variation_id,'_stock_status','onbackorder');
                        }else{
                            update_post_meta($variation_id,'_stock_status',$stock_status);
                        }
                        update_post_meta($wp_id,'_backorders',$data_array->_backorders);
                        if($data_array->_stock != ""){
                            update_post_meta($variation_id,'_stock',$data_array->_stock);    
                        }
                        
                        if($data_array->_price != ""){
                            update_post_meta($variation_id,'_price',$data_array->_price);
                            update_post_meta($variation_id,'_regular_price',$data_array->_price);
                        }
                        
                        if($data_array->_sale_price != ""){
                            update_post_meta($variation_id,'_sale_price',$data_array->_sale_price);    
                        }
                        
                        if($data_array->_sale_price_dates_from != "1/1/0001"){
                            update_post_meta($variation_id,'_sale_price_dates_from',strtotime($fix_date_from));    
                        }
                        
                        if($data_array->_sale_price_dates_to != "1/1/0001"){
                            update_post_meta($variation_id,'_sale_price_dates_to',strtotime($fix_date_to));    
                        }
                        
                        if($data_array->_barcode_attributes != ""){
                            update_post_meta($variation_id,'_barcode_attributes',$data_array->_barcode_attributes);    
                        }
                        
                        if($data_array->_sku != ""){
                            update_post_meta($variation_id,'_sku',$data_array->_sku);    
                        }
                        
                        if($data_array->_parentOid != ""){
                            update_post_meta($variation_id,'_parentOid',$data_array->_parentOid);    
                        }
            		   
            		    update_post_meta($variation_id,'_oid',$oid);
            		    update_post_meta( $product_parent_id, '_product_attributes', $current_attributes );
            		    
            		    wp_set_object_terms( $product_parent_id, 'variable', 'product_type', true );
            		    //$sql = "UPDATE smartenize SET status = '1' WHERE id = '$id'";
                        //$con->query($sql);    
                    }
                    }else{
                        echo '<em>Variation without parent Oid</em></br>';
                        $sql = "UPDATE smartenize SET status = '110' WHERE id = '$id'";
                        $con->query($sql); 
                    }
                }else{
                    echo '<em>Variation without parent Oid</em></br>';
                    $sql = "UPDATE smartenize SET status = '105' WHERE id = '$id'";
                    $con->query($sql);    
                }
            }else{
                $sql = "UPDATE smartenize SET status = '107' WHERE id = '$id'";
                $con->query($sql);
                
                $message = 'This variation has empty attributes ('.$data_array->_variation_description.')';
                $sql_log = "INSERT INTO smartenize_log (sid, message) VALUES ('".$id."','".$message."')";
                $con->query($sql_log);
            }
    		   
		}
        
    }
    
?>
    
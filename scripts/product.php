<?php
    

    function sm_get_product($oid,$data,$id,$con,$product_terms,$product_tags,$init){
        
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
    		    echo 'updating '.$wp_id.'...</br>';
    		    update_product($oid,$data,$path,$id,$con,$product_terms,$wp_id,$init,$product_tags);
		    }
        }else{
            echo 'adding..</br>';
            add_product($oid,$data,$path,$id,$con,$product_terms,$init,$product_tags);
        }
        
        
    }
    
    
    function add_product($oid,$data,$path,$id,$con,$product_terms,$init,$product_tags){
        
        require($path.'init'.$init["lang"].'.php');

		$data_array = json_decode($data);
		
		
		if(empty($data_array)){
		    $sql = "UPDATE smartenize SET status = '100' WHERE id = '$id'";
            $con->query($sql);
		}else{
		    $post_title = (string)$data_array->post_title;
		    if($post_title == ""){
		        $sql = "UPDATE smartenize SET status = '104' WHERE id = '$id'";
                $con->query($sql);
		    }else{
                
                $post_content = (string)$data_array->post_content;
                $post_excerpt = (string)$data_array->post_excerpt;
                $post_name = (string)$data_array->post_name;
                $menu_order = (string)$data_array->menu_order;
                $post_images = $data_array->post_images;
                $post_categories = $data_array->post_categories;
                $post_attributes = $data_array->post_attributes;
                $post_tags = $data_array->post_tags;
                $supplier = $data_array->SupplierCode;
                wp_defer_term_counting( true );
        		wp_defer_comment_counting( true );
        		
        		$my_post = array();
                $my_post['post_title']    = iconv('UTF-8', 'UTF-8//IGNORE', $post_title);
                $my_post['post_content']    = $post_content;
                $my_post['post_excerpt']    = $post_excerpt;
                if($post_name == ""){
                    $my_post['post_name'] = fix_slug($post_title);
                }else{
                    $my_post['post_name'] = $post_name;
                }
                $my_post['post_status']   = 'publish';
                $my_post['post_type']   = 'product';
                $my_post['menu_order'] = $menu_order;
                
                $product_id = wp_insert_post( $my_post );
                
                update_post_meta($product_id,'_product_version',wpbo_get_woo_version_number());
                update_post_meta($product_id,'_supplier',$supplier);
                
                
                
                if($product_id != 0){
                    update_post_meta($product_id,'_oid',$oid);
            		
            		// ADD CATEGORIES
            		
            		if(count($post_categories) != 0){
            		    add_categories($product_id,$post_categories,$product_terms);
            		}else{
            		    $message = 'Empty categories';
                        $date = date('d-m-Y H:i:s');
                        $sql_log = "INSERT INTO smartenize_log (sid, message, product_id, error_date) VALUES ('".$id."','".$message."','".$product_id."','".$date."')";
                        $con->query($sql_log);
            		}
            		
            		// ADD TAGS
            		
            		if(count($post_tags) != 0){
            		    add_tags($product_id,$post_tags,$product_tags);
            		}else{
            		    remove_tags($wp_id,$post_tags,$product_tags);
            		    $message = 'Empty tags';
                        $date = date('d-m-Y H:i:s');
                        $sql_log = "INSERT INTO smartenize_log (sid, message, product_id, error_date) VALUES ('".$id."','".$message."','".$product_id."','".$date."')";
                        $con->query($sql_log);
            		}
            		
            		
            		// ADD ATTRIBUTES
            		
            		if(count($post_attributes) != 0){
            		    add_attributes($product_id,$post_attributes,$con,$init,$id);
            		}
            		
            		//$sql = "UPDATE smartenize SET status = '1' WHERE id = '$id'";
                    //$con->query($sql);
                }else{
                    $sql = "UPDATE smartenize SET status = '104' WHERE id = '$id'";
                    $con->query($sql);        
                }
        		
        		
        		wp_defer_term_counting( false );
        		wp_defer_comment_counting( false );
		    }     
    		
		}
		
    }
    
    function update_product($oid,$data,$path,$id,$con,$product_terms,$wp_id,$init,$product_tags){
        require($path.'init'.$init["lang"].'.php');
        
        $data_array = json_decode($data);
        $post_title = (string)$data_array->post_title;
        $post_content = (string)$data_array->post_content;
        $post_excerpt = (string)$data_array->post_excerpt;
        $post_name = (string)$data_array->post_name;
        $menu_order = (string)$data_array->menu_order;
        $post_images = $data_array->post_images;
        $post_categories = $data_array->post_categories;
        $post_attributes = $data_array->post_attributes;
        $post_tags = $data_array->post_tags;
        $supplier = $data_array->SupplierCode;
        
        if($post_title == ""){
            $my_post = array();
            $my_post['ID'] = $wp_id;
            //$my_post['post_title']    = iconv('UTF-8', 'UTF-8//IGNORE', $post_title);
            $my_post['post_content']    = $post_content;
            $my_post['post_excerpt']    = $post_excerpt;
            if($post_name == ""){
                $my_post['post_name'] = fix_slug($post_title);
            }else{
                $my_post['post_name'] = $post_name;
            }
            $my_post['post_status']   = 'publish';
            $my_post['post_type']   = 'product';
            $my_post['menu_order'] = $menu_order;
            
            
            wp_update_post($my_post);
            update_post_meta($wp_id,'_product_version',wpbo_get_woo_version_number());
            update_post_meta($wp_id,'_supplier',$supplier);
            if(count($post_categories) != 0){
    		    add_categories($wp_id,$post_categories,$product_terms);
    		}else{
    		    $message = 'Empty categories';
                $date = date('d-m-Y H:i:s');
                $sql_log = "INSERT INTO smartenize_log (sid, message, product_id, error_date) VALUES ('".$id."','".$message."','".$wp_id."','".$date."')";
                $con->query($sql_log);
    		
    		}
        	
        	
        	
        	// ADD TAGS
            	
    		if(count($post_tags) != 0){
    		    add_tags($wp_id,$post_tags,$product_tags);
    		}else{
    		    remove_tags($wp_id,$post_tags,$product_tags);
    		    $message = 'Empty tags';
                $date = date('d-m-Y H:i:s');
                $sql_log = "INSERT INTO smartenize_log (sid, message, product_id, error_date) VALUES ('".$id."','".$message."','".$wp_id."','".$date."')";
                $con->query($sql_log);
    		}
        		
            if(count($post_attributes) != 0){
                add_attributes($wp_id,$post_attributes,$con,$init,$id);
            }
            
            $sql = "UPDATE smartenize SET status = '2' WHERE id = '$id'";
            $con->query($sql);
            
            $message = 'Empty title';
            $date = date('d-m-Y H:i:s');
            $sql_log = "INSERT INTO smartenize_log (sid, message, product_id, error_date) VALUES ('".$id."','".$message."','".$wp_id."','".$date."')";
            $con->query($sql_log);
            
        }else{
            $my_post = array();
            $my_post['ID'] = $wp_id;
            $my_post['post_title']    = iconv('UTF-8', 'UTF-8//IGNORE', $post_title);
            $my_post['post_content']    = $post_content;
            $my_post['post_excerpt']    = $post_excerpt;
            if($post_name == ""){
                $my_post['post_name'] = fix_slug($post_title);
            }else{
                $my_post['post_name'] = $post_name;
            }
            $my_post['post_status']   = 'publish';
            $my_post['post_type']   = 'product';
            $my_post['menu_order'] = $menu_order;
            
            
            wp_update_post($my_post);
            update_post_meta($wp_id,'_product_version',wpbo_get_woo_version_number());
            
            if(count($post_categories) != 0){
    		    add_categories($wp_id,$post_categories,$product_terms);
    		}
        	
        	// ADD TAGS
            		
    		if(count($post_tags) != 0){
    		    add_tags($wp_id,$post_tags,$product_tags);
    		}else{
    		    remove_tags($wp_id,$post_tags,$product_tags);
    		    $message = 'Empty tags';
                $date = date('d-m-Y H:i:s');
                $sql_log = "INSERT INTO smartenize_log (sid, message, product_id, error_date) VALUES ('".$id."','".$message."','".$wp_id."','".$date."')";
                $con->query($sql_log);
    		}
        		
            if(count($post_attributes) != 0){
                add_attributes($wp_id,$post_attributes,$con,$init,$id);
            }
            
            $sql = "UPDATE smartenize SET status = '2' WHERE id = '$id'";
            $con->query($sql);
        
        }
        
        
    }
    
    
    
    function remove_tags($product_id,$post_tags,$product_tags){
        for($i = 0; $i < count($post_tags); $i++){
            $post_tags[$i]->tag_Oid;
		    $product_tag = $post_tags[$i]->tag_Oid;
		    
		    if(in_array($product_tag,$product_tags)){
		        $pkey = array_search($product_tag,$product_tags);
		        
		        wp_remove_object_terms($product_id,'4442','product_tag');
		        
		    }
	    }
    }
    
    function add_tags($product_id,$post_tags,$product_tags){
        for($i = 0; $i < count($post_tags); $i++){
            $post_tags[$i]->tag_Oid;
		    $product_tag = $post_tags[$i]->tag_Oid;
		    
		    if(in_array($product_tag,$product_tags)){
		        $pkey = array_search($product_tag,$product_tags);
		        
		        if($i == 0){
		            wp_set_object_terms($product_id,$pkey,'product_tag',false);      
		        }else{
		            wp_set_object_terms($product_id,$pkey,'product_tag',true);
		        }    
		    }
	    }
    }
    
    function add_categories($product_id,$post_categories,$product_terms){
        
        for($i = 0; $i <= count($post_categories); $i++){
            
		    $product_cat = $post_categories[$i];
		    
		    if(in_array($product_cat,$product_terms)){
		        $pkey = array_search($product_cat,$product_terms);
		        if($i == 0){
		            wp_set_object_terms($product_id,$pkey,'product_cat',false);      
		        }else{
		            wp_set_object_terms($product_id,$pkey,'product_cat',true);
		        }    
		    }
	    }
    }
    
    function add_attributes($product_id,$post_attributes,$con,$init,$id){
        $path = $init["pluginpath"];
        require($path.'init'.$init["lang"].'.php');
        $current_attributes = get_post_meta($product_id,'_product_attributes')[0];
        foreach($current_attributes as $key => $value){
            if($value["is_variation"] == 0){
                unset($current_attributes[$key]);
            }
        }
        
        // ADD NEW ATTRIBUTES
        
        for($i = 0; $i < count($post_attributes); $i++){
            
            if(array_key_exists('option_Value', $post_attributes[$i])){
                $att_oid = $post_attributes[$i]->attr_Oid;
                $option_oid = $post_attributes[$i]->option_Value;
                
                //echo $att_oid.' => '.$option_oid.'</br>';
                $temp_oid = explode('-',$att_oid);
                unset($temp_oid[(count($temp_oid) - 1)]);
                unset($temp_oid[(count($temp_oid) - 1)]);
                unset($temp_oid[(count($temp_oid) - 1)]);
                
                $att_slug_strip = implode('-',$temp_oid);
                $att_slug = 'pa_'.implode('-',$temp_oid);
                
                $attributes = wc_get_attribute_taxonomies();            
                $slugs = wp_list_pluck( $attributes, 'attribute_name' ); 
                
                if (in_array( $att_slug_strip, $slugs ) ) {
                    $options = get_terms( array('taxonomy' => $att_slug,'hide_empty' => false) );
                    
                    foreach($options as $option){
                        $opt_oid = get_term_meta($option->term_id,'Oid')[0];
                        
                        if($option_oid == $opt_oid){
                            
                            if(array_key_exists($att_slug, $current_attributes)){
                                //echo 'exist term => ';
                                //echo $option_oid.'</br>';
                                wp_set_object_terms($product_id,$option->term_id,$att_slug,true);
                            }else{
                                //echo 'add term => ';
                                //echo $option_oid.'</br>';
                                $current_attributes[$att_slug] = array('name'=>$att_slug,'position' => '1','value'=>'','is_visible'=>'1','is_variation'=>'0','is_taxonomy'=>'1');
                                //echo $product_id.' => '.$option->term_id.' => '.$att_slug.'</br>';
                                wp_set_object_terms($product_id,$option->term_id,$att_slug,true);
                            }
                            
            		        
                        }
                        
                    }
                }else{
                    $message = 'This product has attribute assigned that does not exist in Woocommerce list ('.$att_slug_strip.')';
                    $sql_log = "INSERT INTO smartenize_log (sid, message) VALUES ('".$id."','".$message."')";
                    $con->query($sql_log);
                }
                
            }
        }
        
        update_post_meta($product_id,'_product_attributes',$current_attributes);
        
        wc_delete_product_transients($product_id);
        wc_update_product_lookup_tables();
    }
    
    
    function wpbo_get_woo_version_number() {
            // If get_plugins() isn't available, require it
    	if ( ! function_exists( 'get_plugins' ) )
    		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    	
            // Create the plugins folder and file variables
    	$plugin_folder = get_plugins( '/' . 'woocommerce' );
    	$plugin_file = 'woocommerce.php';
    	
    	// If the plugin version number is set, return it 
    	if ( isset( $plugin_folder[$plugin_file]['Version'] ) ) {
    		return $plugin_folder[$plugin_file]['Version'];
    
    	} else {
    	// Otherwise return null
    		return NULL;
    	}
    }
?>
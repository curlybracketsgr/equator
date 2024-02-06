<?php
    function sm_get_general($oid,$data,$id,$con,$init){
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
    		    
    		    set_general($oid,$data,$path,$id,$con,$wp_id);
		    }
        }else{
            //echo $id.' => '.$oid.' => 103</bR>';
            $sql = "UPDATE smartenize SET status = '103' WHERE id = '$id'";
            $con->query($sql);
        }
    }
    
    function set_general($oid,$data,$path,$id,$con,$wp_id){
        require($path.'init'.$init["lang"].'.php');
        $data_array = json_decode($data);
        if(empty($data_array)){
		    $sql = "UPDATE smartenize SET status = '100' WHERE id = '$id'";
            $con->query($sql);
		}else{
            $price = $data_array->_price;
            if($price == ''){
                $price = 0;
                
    		    $message = 'Empty price';
                $date = date('d-m-Y H:i:s');
                $sql_log = "INSERT INTO smartenize_log (sid, message, product_id, error_date) VALUES ('".$id."','".$message."','".$wp_id."','".$date."')";
                $con->query($sql_log);
            	
            }
            if($data_array->_sale_price == 0){
                $sale_price = '';
            }else{
                $sale_price = $data_array->_sale_price;
            }
            $sale_price_dates_from = $data_array->_sale_price_dates_from;
            $sale_price_dates_to = $data_array->_sale_price_dates_to;
            $tax_status = $data_array->_tax_status;
            $tax_class = $data_array->_tax_class;
            
            update_post_meta($wp_id,'_regular_price',$price);
            if($sale_price != ''){
                update_post_meta($wp_id,'_price',$sale_price);
            }else{
                update_post_meta($wp_id,'_price',$price);
            }
            update_post_meta($wp_id,'_sale_price',$sale_price);
            
            if($sale_price_dates_from == '1/1/0001'){
                delete_post_meta($wp_id,'_sale_price_dates_from'); 
                delete_post_meta($wp_id,'_sale_price_dates_to'); 
            }else{
                if($sale_price_dates_to == '1/1/0001'){
                    delete_post_meta($wp_id,'_sale_price_dates_from'); 
                    delete_post_meta($wp_id,'_sale_price_dates_to'); 
                }else{
                    $date_from_tmp = explode('/',$sale_price_dates_from);
                    $date_to_tmp = explode('/',$sale_price_dates_to);
                    $fix_date_from = $date_from_tmp[2].'-'.$date_from_tmp[1].'-'.$date_from_tmp[0];
                    $fix_date_to = $date_to_tmp[2].'-'.$date_to_tmp[1].'-'.$date_to_tmp[0];
                    //echo $sale_price_dates_from.' => '.strtotime($sale_price_dates_from).' => '.strtotime($fix_date_from).'</br>';
                    //echo $sale_price_dates_to.' => '.strtotime($sale_price_dates_to).' => '.strtotime($fix_date_to).'</br>';
                    update_post_meta($wp_id,'_sale_price_dates_from',$fix_date_from);
                    update_post_meta($wp_id,'_sale_price_dates_to',$fix_date_to);        
                }
            }
            
            update_post_meta($wp_id,'_tax_status',$tax_status);
            update_post_meta($wp_id,'_tax_class',$tax_class);
            
            if(get_post_meta($wp_id,'_regular_price') == "0"){
                
            }else{
                $sql7 = "UPDATE ".$init["dbpre"]."posts SET post_status = 'publish' WHERE ID = '".$wp_id."'";
                $ex_result7 = $con->query($sql7);
            }
            
            $sql = "UPDATE smartenize SET status = '2' WHERE id = '$id'";
            $con->query($sql);
		}    
    }
    
?>
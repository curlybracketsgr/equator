<?php
    function sm_get_inventory($oid,$data,$id,$con,$init){
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
    		    set_inventory($oid,$data,$path,$id,$con,$wp_id);
		    }
        }else{
            $sql = "UPDATE smartenize SET status = '103' WHERE id = '$id'";
            $con->query($sql);    
        }
        
    }
    
    function set_inventory($oid,$data,$path,$id,$con,$wp_id){
        require($path.'init'.$init["lang"].'.php');
        $data_array = json_decode($data);
        if(empty($data_array)){
		    $sql = "UPDATE smartenize SET status = '100' WHERE id = '$id'";
            $con->query($sql);
		}else{
            $sku = $data_array->_sku;
            $manage_stock = $data_array->_manage_stock;
            $stock = $data_array->_stock;
            $backorders = $data_array->_backorders;
            $sold_individually = $data_array->_sold_individually;
            $stock_status = $data_array->_stock_status;
            
            update_post_meta($wp_id,'_sku',$sku);
            update_post_meta($wp_id,'_manage_stock',$manage_stock);
            if($stock <= 0){
                update_post_meta($wp_id,'_stock',0);
            }else{
                update_post_meta($wp_id,'_stock',$stock);
            }
            if(($manage_stock == "no") && ($backorders == "notify")){
                
                update_post_meta($wp_id,'_stock_status','onbackorder');
                update_post_meta($wp_id,'_backorders',$backorders);
            }else{
                update_post_meta($wp_id,'_stock_status',$stock_status);
                update_post_meta($wp_id,'_backorders',$backorders);
            }
            update_post_meta($wp_id,'_sold_individually',$sold_individually);
            
            
            $sql = "UPDATE smartenize SET status = '2' WHERE id = '$id'";
            $con->query($sql);
		}
        
    }
    
?>
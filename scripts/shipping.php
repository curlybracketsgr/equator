<?php
    function sm_get_shipping($oid,$data,$id,$con,$init){
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
    		    set_shipping($oid,$data,$path,$id,$con,$wp_id);
		    }
        }else{
            //echo $id.' => '.$oid.' => 103</bR>';
            $sql = "UPDATE smartenize SET status = '103' WHERE id = '$id'";
            $con->query($sql);    
        }
        
    }
    
    function set_shipping($oid,$data,$path,$id,$con,$wp_id){
        require($path.'init'.$init["lang"].'.php');
        
        $data_array = json_decode($data);
        if(empty($data_array)){
		    $sql = "UPDATE smartenize SET status = '100' WHERE id = '$id'";
            $con->query($sql);
		}else{
		    
            $weight = $data_array->_weight;
            $length = $data_array->_lenght;
            $width = $data_array->_width;
            $height = $data_array->_height;
            
            update_post_meta($wp_id,'_weight',$weight);
            update_post_meta($wp_id,'_length',$length);
            update_post_meta($wp_id,'_width',$width);
            update_post_meta($wp_id,'_height',$height);
            
            
            
            $sql = "UPDATE smartenize SET status = '2' WHERE id = '$id'";
            $con->query($sql);
            
		}
        
    }
    
?>
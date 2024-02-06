<?php

    function product_files($oid,$data,$id,$con,$init){
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
    		    
		        echo 'updating files for '.$wp_id.'</br>';
		        update_files($oid,$data,$path,$id,$con,$wp_id,$init);
		    }
        }else{
            //echo $id.' => '.$oid.' => 103</bR>';
            $sql = "UPDATE smartenize SET status = '103' WHERE id = '$id'";
            $con->query($sql);    
        }
        
    }
    
    function update_files($oid,$data,$path,$id,$con,$product_id,$init){
        require($path.'init'.$init["lang"].'.php');
        
        $data_array = json_decode($data);
		if(empty($data_array)){
		    $sql = "UPDATE smartenize SET status = '100' WHERE id = '$id'";
            $con->query($sql);
		}else{
		    echo $product_id.'</br>';
		    $previous_files = explode(',',get_post_meta($product_id,'files')[0]);
		    foreach($data_array->post_files as $files){ 
		        if(in_array($files->filename,$previous_files)){
		            
		        }else{
		            $previous_files[] = $files->filename;    
		        }
		    }
		    $current_files = implode(',',$previous_files);
		    update_post_meta($product_id,'files',$current_files);
		    
		    $sql = "UPDATE smartenize SET status = '1' WHERE id = '$id'";
            $con->query($sql);
		}
    }

?>
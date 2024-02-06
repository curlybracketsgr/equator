<?php

    function sm_get_linked_products($oid,$data,$id,$con,$init){
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
    		    echo $wp_id.' => linked_products</br>';
    		    set_linked_products($oid,$data,$path,$id,$con,$wp_id,$init);
		    }
        }else{
            
            $sql = "UPDATE smartenize SET status = '103' WHERE id = '$id'";
            $con->query($sql);    
        }
        
    }
    
    
    function set_linked_products($oid,$data,$path,$id,$con,$wp_id,$init){
        require($path.'init'.$init["lang"].'.php');
        $data_array = json_decode($data);
        if(empty($data_array)){
            
		    $sql = "UPDATE smartenize SET status = '100' WHERE id = '$id'";
            $con->query($sql);
		}else{
		    
		    if(count($data_array->_upsell_ids) != 0){
		        //_upsell_ids      
		        $upsell_array = [];
		        foreach($data_array->_crosssell_ids as $pr){
		            $linked_oid = $pr->Linked_Oid;
		            $sql1 = "SELECT * FROM ".$init["dbpre"]."postmeta WHERE meta_value = '$linked_oid' AND meta_key = '_oid'";
                    $ex_result1 = $con->query($sql1);
        
                    if ($ex_result1->num_rows > 0) {
                        while($row1 = $ex_result1->fetch_assoc()) {
                            $upsell_array[] = intval($row1["post_id"]);
                        }
                    }
		        }
		        
		        update_post_meta($wp_id,'_crosssell_ids',$upsell_array);
		    }else{
		        delete_post_meta($wp_id,'_crosssell_ids',$crosssell_array);
		    }
		    
		    if(count($data_array->_crosssell_ids) != 0){
		        
		        $crosssell_array = [];
		        foreach($data_array->_crosssell_ids as $pr){
		            $linked_oid = $pr->Linked_Oid;
		            $sql1 = "SELECT * FROM ".$init["dbpre"]."postmeta WHERE meta_value = '$linked_oid' AND meta_key = '_oid'";
                    $ex_result1 = $con->query($sql1);
        
                    if ($ex_result1->num_rows > 0) {
                        while($row1 = $ex_result1->fetch_assoc()) {
                            $crosssell_array[] = intval($row1["post_id"]);
                        }
                    }
		        }
		        
		        update_post_meta($wp_id,'_upsell_ids',$crosssell_array);
		        
		    }else{
		        delete_post_meta($wp_id,'_upsell_ids',$crosssell_array);
		    }
		    
            $sql = "UPDATE smartenize SET status = '2' WHERE id = '$id'";
            $con->query($sql);
            
		}
        
    }
    

?>

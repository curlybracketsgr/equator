<?php
    function delete_variations($oid,$data,$id,$con,$init){
        
        $path = $init["pluginpath"];
        require($path.'init'.$init["lang"].'.php');
        
        $sql = "SELECT * FROM ".$init["dbpre"]."postmeta WHERE meta_key = '_oid'";
        $ex_result = $con->query($sql);
        $existing_products = [];
        if ($ex_result->num_rows > 0) {
		    while($row = $ex_result->fetch_assoc()) {
		        if(get_post_type($row["post_id"]) == 'product_variation'){
		            $existing_products[$row["post_id"]] = $row["meta_value"];        
		        }
		    }
        }
        
        $extract_data = json_decode($data);
        
        
        if(empty($extract_data)){
		    $sql = "UPDATE smartenize SET status = '100' WHERE id = '$id'";
            $con->query($sql);
		}else{
            $current_products = [];
            foreach($extract_data->attr_Oid as $ex_data){
                $current_products[] = $ex_data;
            }
            
            if(count($current_products) != 0){
                
                $result = array_diff($existing_products,$current_products);
                //echo count($result).'</br>';
                foreach($result as $key => $value){
                    echo $key.' delete</br>';
                    wp_delete_post($key,true);
                }
            }
            
            $sql = "UPDATE smartenize SET status = '1' WHERE id = '$id'";
            $con->query($sql);
            
            
		}
		
        
    }
?>
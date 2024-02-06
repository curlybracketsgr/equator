<?php
    function delete_categories($oid,$data,$id,$con,$product_terms,$init){
        $path = $init["pluginpath"];
        require($path.'init'.$init["lang"].'.php');
        
        if($data != ""){
            $extract_data = json_decode($data);
            if(empty($extract_data)){
    		    $sql = "UPDATE smartenize SET status = '100' WHERE id = '$id'";
                $con->query($sql);
    		}else{
                $all_cats = [];
                foreach($extract_data->Categories as $to_del){
                    $all_cats[] = $to_del->Oid;
                }
                
                $result = array_diff($product_terms,$all_cats);
                
                foreach($result as $key => $value){
                    if($value == ""){
                        
                    }else{
                        wp_delete_term($key,'product_cat');
                        
                    }
                }
                $sql = "UPDATE smartenize SET status = '3' WHERE id = '$id'";
                $con->query($sql);
    		}
        }else{
            $sql = "UPDATE smartenize SET status = '106' WHERE id = '$id'";
            $con->query($sql);
        }
    }
?>
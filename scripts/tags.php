<head>
  <meta charset="UTF-8">
</head>
<?php
    function sm_get_tags($oid,$data,$id,$con,$product_tags,$init){
        $path = $init["pluginpath"];
        require($path.'init'.$init["lang"].'.php');
        
        
        $extract_data = json_decode($data);
        if(empty($extract_data)){
		    $sql = "UPDATE smartenize SET status = '100' WHERE id = '$id'";
            $con->query($sql);
		}else{
            foreach($extract_data->_tags_item as $tags){
                
                $tag_Oid = $tags->tag_Oid;
                $tag_name = $tags->cat_name;
                $tag_slug = fix_slug($tag_name);
                if(in_array($tag_Oid,$product_tags)){
                    $tag_key = array_search($tag_Oid,$product_tags);    
                    $update_tag = wp_update_term($tag_key,'product_tag',array('name'=>$tag_name,'slug'=>$tag_slug));
                    $sql = "UPDATE smartenize SET status = '2' WHERE id = '$id'";
                    $con->query($sql);
                }else{
                    $create_tag = wp_insert_term($tag_name,'product_tag',array('slug'=>$tag_slug));                             // ADD CHILD TERM
                    
                    if(is_array($create_tag)){                                                                                  // if child category successfully added
        				update_term_meta($create_tag["term_id"],'Oid',$tag_Oid);                                                // set custom term meta of Oid
        				$sql = "UPDATE smartenize SET status = '1' WHERE id = '$id'";
                        $con->query($sql);                                                                                      // Update status to smartenize as SUCCESS
        			}else{
        			    
        			}
                }
            }
		}
        
    }
    
    

?>
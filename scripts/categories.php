<head>
  <meta charset="UTF-8">
</head>
<?php
    function sm_get_categories($oid,$data,$id,$con,$product_terms,$init){
        $path = $init["pluginpath"];
        require($path.'init'.$init["lang"].'.php');
        
        $extract_data = json_decode($data);
        
        $product_terms_temp = get_terms( array('taxonomy' => 'product_cat','hide_empty' => false) );
    
        $product_terms = [];		
    	foreach($product_terms_temp as $term){			
    		$term_meta = get_term_meta($term->term_id,'Oid');			
    		$product_terms[$term->term_id] = $term_meta[0];
    	}
        
        if(empty($extract_data)){
		    $sql = "UPDATE smartenize SET status = '100' WHERE id = '$id'";
            $con->query($sql);
		}else{
		    foreach($extract_data->_category_item as $category_item){
                $oid = (string)$category_item->cat_oid;
                $aa = $category_item->aa;
                $name = $category_item->cat_name;
                $parent = $category_item->cat_parent_Oid;
                $slug = fix_slug($name);
                //print_r($category_item);
                //echo '</br>';
                //echo $oid.' => '.$aa.' => '.$name.' => '.$parent.' => '.$slug.'</bR></br>';
                
                if($parent == ""){                                                                                                      // If is parent category
                
                    if(in_array($oid,$product_terms)){                                                                                  // if parent category exists
                        $pkey = array_search($oid,$product_terms);
                        wp_update_term($pkey,'product_cat',array('name'=>$name,'parent' => 0));
                        update_term_meta($pkey,'order',$aa);
                        $sql = "UPDATE smartenize SET status = '2' WHERE id = '$id'";
                        $con->query($sql);  
                    }else{                                                                                                              // if parent category not exist
                        $create_term = wp_insert_term($name,'product_cat',array('slug'=>$slug,'parent' => 0));                          // ADD PARENT TERM
                        //print_r($create_term);
                        //echo '</br></br>';
                        if(is_array($create_term)){                                                                                     // if parent category successfully added
        					update_term_meta($create_term["term_id"],'Oid',$oid);                                                       // set custom term meta of Oid
        					update_term_meta($create_term["term_id"],'order',$aa);
        					$sql = "UPDATE smartenize SET status = '1' WHERE id = '$id'";
                            $con->query($sql);                                                                                          // Update status to smartenize as SUCCESS
        				}
                    }
                    
                }else{                                                                                                                  // if is child category
                    
                    if(in_array($parent,$product_terms)){                                                                               // check if parent exist
                        $key = array_search($parent,$product_terms);                                                                    // get term id of existing parent
                        
                        if($key != ""){
                            if(in_array($oid,$product_terms)){                                                                          // if child category exist
                                $key1 = array_search($oid,$product_terms);
                                
                                wp_update_term($key1,'product_cat',array('name'=>$name,'parent'=>$key));
                                update_term_meta($key1,'order',$aa);
                                $sql = "UPDATE smartenize SET status = '2' WHERE id = '$id'";
                                $con->query($sql);      
                            }else{                                                                                                      // if child category not exist
                                
                                $create_term = wp_insert_term($name,'product_cat',array('slug'=>$slug,'parent'=>$key));                 // ADD CHILD TERM
                                //print_r($create_term);
                                //echo '</br></br>';
                                if(is_array($create_term)){                                                                             // if child category successfully added
                					update_term_meta($create_term["term_id"],'Oid',$oid);                                               // set custom term meta of Oid
                					update_term_meta($create_term["term_id"],'order',$aa);
                					$sql = "UPDATE smartenize SET status = '1' WHERE id = '$id'";
                                    $con->query($sql);                                                                                  // Update status to smartenize as SUCCESS
                				}
                				
                            }
                        }
                        
                    }else{
                        $sql = "UPDATE smartenize SET status = '103' WHERE id = '$id'";
                        $con->query($sql);
                    }
                    
                        
                }
                
		    }
		}
		
        
    }
    
    

?>
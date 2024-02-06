<?php
    function delete_attributes($oid,$data,$id,$con,$init){
        $path = $init["pluginpath"];
        require($path.'init'.$init["lang"].'.php');
        
        if($data != ""){
            $extract_data = json_decode($data);
            $attributes = wc_get_attribute_taxonomies();                                                                                    // get list of all existing attributes
            $slugs = wp_list_pluck( $attributes, 'attribute_name' );
            
            $current_attributes = [];
            foreach($extract_data->Attributes as $key => $value){
                //echo $value->Oid.'</br>';
                $temp_oid = explode('-',$value->Oid);
                unset($temp_oid[(count($temp_oid) - 1)]);
                unset($temp_oid[(count($temp_oid) - 1)]);
                unset($temp_oid[(count($temp_oid) - 1)]);
                
                $att_slug = implode('-',$temp_oid);
                //echo $att_slug.'</br>';
                $current_attributes[] = $att_slug;
            }
            
            $existing_attributes = [];
            foreach($attributes as $key => $value){
                $existing_attributes[$value->attribute_id] = $value->attribute_name;
            }
            
            $existing_options = [];
            foreach($existing_attributes as $exatt){
                
                $options = get_terms('pa_'.$exatt);
                foreach($options as $option){
                    $existing_options[$option->term_id] = get_term_meta($option->term_id,'Oid')[0];
                }
                
            }
            
            $layer_nav_widget_cont = get_option('widget_woocommerce_layered_nav');
            
            
            $result = array_diff($existing_attributes,$current_attributes);
            if(count($result) != 0){
                foreach($result as $f){
                    for($i = 0; $i < count($layer_nav_widget_cont); $i++){
                        if($layer_nav_widget_cont[$i]["attribute"] == $f){
                            unset($layer_nav_widget_cont[$i]);
                        }
                    }
                    
                    $terms = get_terms( 'pa_'.$f, array(
                        'hide_empty' => false,
                    ) );
                    foreach($terms as $term){
                        wp_delete_term($term->term_id,'pa_'.$layer_nav_widget_cont[$i]["attribute"]);
                    }
                    $sql2 = "DELETE FROM ".$init["dbpre"]."woocommerce_attribute_taxonomies WHERE attribute_name = '$f'";
                    $delete_attr = $con->query($sql2);
                    
                }
                
                update_option('widget_woocommerce_layered_nav',$layer_nav_widget_cont);
            }
            
            $sql = "UPDATE smartenize SET status = '1' WHERE id = '$id'";
            $con->query($sql);
        }else{
            $sql = "UPDATE smartenize SET status = '106' WHERE id = '$id'";
            $con->query($sql);
        }
    }
?>
<?php
    

    function sm_filters($oid,$data,$id,$con,$sidebar,$attributes,$init){
        $path = $init["pluginpath"];
        require($path.'init'.$init["lang"].'.php');
        
        $con = mysqli_connect($init["dbhost"],$init["dbuser"],$init["dbpass"],$init["dbname"]);
        if ($con->connect_error) {
            die("Connection failed: " . $con->connect_error);
        }
        $con -> set_charset("utf8");
        $sql = "SELECT * FROM ".$init["dbpre"]."postmeta WHERE meta_value = '$oid' AND meta_key = '_oid'";
        $ex_result = $con->query($sql);
        
        //print_r($attributes);
        
        $clean_attributes = [];
        $clean_attributes_label = [];
        foreach($attributes as $attribute){
            $clean_attributes[$attribute->attribute_id] = $attribute->attribute_name;
            $clean_attributes_label[$attribute->attribute_id] = $attribute->attribute_label;
        }
        
        $categories_widget = array_keys(get_option('widget_woocommerce_product_categories'))[0];
        $layer_nav_widget_cont = get_option('widget_woocommerce_layered_nav');
        $easy_layer = [];
        
        foreach($layer_nav_widget_cont as $key => $value){
            if($key != "_multiwidget"){
                $easy_layer[$key] = $value['attribute'];    
            }
        }
        $layer_nav_widget =  array_keys(get_option('widget_woocommerce_layered_nav'));
        $price_widget =  array_keys(get_option('widget_woocommerce_price_filter'))[0];
        
        
        $extract_data = json_decode($data);
        print_r($extract_data);
        echo '</br></br>';
        
        if(empty($extract_data)){
		    $sql = "UPDATE smartenize SET status = '100' WHERE id = '$id'";
            $con->query($sql);
		}else{
        
            $new_widget_set = [];
            if(count($extract_data->_Filtres_item) != 0){
                foreach($extract_data->_Filtres_item as $key => $value){
                    $temp_oid = explode('-',$value->attr_Oid);
                    unset($temp_oid[(count($temp_oid) - 1)]);
                    unset($temp_oid[(count($temp_oid) - 1)]);
                    unset($temp_oid[(count($temp_oid) - 1)]);
                    
                    $att_slug = implode('-',$temp_oid);
                    
                    echo "Key : ".$key.' => ';
                    print_r($value);
                    echo '</br/></br>';
                    
                    if($value->attr_IsFilter == 'yes'){
                        
                        if(in_array($att_slug,$easy_layer)){
                            
                        }else{
                            if(in_array($att_slug,$clean_attributes)){
                                $k = array_search($att_slug,$clean_attributes);
                                //dropdown
                                $layer_nav_widget_cont[] = array("title" => $clean_attributes_label[$k], "attribute" => $att_slug, "display_type" => "list", "query_type" => "and");
                            }
                            
                        }
                    }else{
                        $sql10 = "UPDATE smartenize SET status = '1' WHERE id = '$id'";
                        $con->query($sql10);  
                        if(in_array($att_slug,$easy_layer)){
                            $kmin = array_search($att_slug,$easy_layer);
                            unset($layer_nav_widget_cont[$kmin]);
                        }else{
                            
                        }
                    }
                }
            }
            
            update_option('widget_woocommerce_layered_nav',$layer_nav_widget_cont);
            
            $final_sidebar = [];
            $final_sidebar[0] = 'woocommerce_product_categories-'.$categories_widget;
            foreach($layer_nav_widget_cont as $key => $value){
                if($key != "_multiwidget"){
                    $final_sidebar[] = 'woocommerce_layered_nav-'.$key;
                }
            }
            $final_sidebar[] = 'woocommerce_price_filter-'.$price_widget;
            //print_r($final_sidebar);
            
            $sidebar["sidebar-woocommerce-shop"] = $final_sidebar;
            update_option('sidebars_widgets',$sidebar);
            
            $sql10 = "UPDATE smartenize SET status = '1' WHERE id = '$id'";
            $con->query($sql10);  
            
		}
		
    }
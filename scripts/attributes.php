<head>
    <meta charset="UTF-8">
</head>
<?php
    
    function sm_get_attributes($oid,$data,$id,$con,$init){
        $path = $init["pluginpath"];
        require($path.'init'.$init["lang"].'.php');
        
        $extract_data = json_decode($data);
        
        if(empty($extract_data)){
		    $sql = "UPDATE smartenize SET status = '100' WHERE id = '$id'";
            $con->query($sql);
		}else{
            $att_oid = $extract_data->_attribute_item[0]->attr_Oid;
            $att_name = $extract_data->_attribute_item[0]->attr_name;
            $att_type = $extract_data->_attribute_item[0]->attr_Type;
            $att_options = $extract_data->_attribute_item[0]->Options;
            $attr_Style = strtolower($extract_data->_attribute_item[0]->attr_Style);
            //print_r($att_options);
            //echo '</br></br>';
            if(($attr_Style == "") || ($attr_Style == "label")){
                $attr_Style = 'select';
            }
            $temp_oid = explode('-',$att_oid);
            unset($temp_oid[(count($temp_oid) - 1)]);
            unset($temp_oid[(count($temp_oid) - 1)]);
            unset($temp_oid[(count($temp_oid) - 1)]);
            
            $att_slug = implode('-',$temp_oid);
            
            if($att_type == "MultiOptions"){                                                                                                    // if type is MultiOptions then it is a valid attribute
                
                $counter = 0;
                $attributes = wc_get_attribute_taxonomies();                                                                                    // get list of all existing attributes
                $slugs = wp_list_pluck( $attributes, 'attribute_name' );                                                                        // get existing slugs of attributes
                
                $sql_1 = "SELECT attribute_name FROM ".$init["dbpre"]."woocommerce_attribute_taxonomies WHERE attribute_name = '".$att_slug."'";
                $ex_result_1 = $con->query($sql_1);
                //echo $att_slug.'</br>';
                //echo $ex_result_1->num_rows.'</br>';
                if($ex_result_1->num_rows == 0){
                    echo 'Add attribute</br>';
                    $sql0 = "INSERT INTO ".$init["dbpre"]."woocommerce_attribute_taxonomies (attribute_name,attribute_label,attribute_type,attribute_orderby,attribute_public) VALUES ('".$att_slug."','".$att_name."','".$attr_Style."','menu_order',0)";
                    $ex_result0 = $con->query($sql0);
                    $attr_id = $con -> insert_id;
                    
                    /*
                    $transient = new stdClass();
                    $transient->attribute_id = strval($attr_id);
                    $transient->attribute_name = $att_slug;
                    $transient->attribute_label = $att_name;
                    $transient->attribute_type = $attr_Style;
                    $transient->attribute_orderby = "menu_order";
                    $transient->attribute_public = 0;
                    
                    $transient_name = '_transient_wvs_get_wc_attribute_taxonomy_pa_'.$att_slug;
                    */
                    $sql_trans = "DELETE FROM ".$init["dbpre"]."options WHERE option_name = '_transient_wc_attribute_taxonomies'";
                    $ex_result_trans = $con->query($sql_trans);
                    
                    if(count($att_options) != 0){
                        foreach($att_options as $key => $value){                                                                            // after adding attribute check for options
                            $option_oid = (string)$value->option_Oid;
                            $option_name = (string)$value->option_name;
                            $rand = explode('-',$option_oid)[0];
                            $sql1 = "SELECT term_id FROM ".$init["dbpre"]."termmeta WHERE meta_value LIKE ".$option_oid;
                            $ex_result1 = $con->query($sql1);
                            
                            if ($ex_result1->num_rows == 1) {
	                            while($row1 = $ex_result1->fetch_assoc()) {
	                                //echo $option_oid.' => '.$row1["term_id"].'</br>';
	                                wp_update_term($row1["term_id"],'pa_'.$att_slug,array("name" => $option_name,'slug' => $rand));
	                                
	                                if( ((string)$value->option_Color != "") && ((string)$value->option_Color != "null") ){
	                                    update_term_meta($row1["term_id"],'color',(string)$value->option_Color);
	                                }
	                                //echo $value->option_image.'</br>';
	                                if(get_term_meta($row1["term_id"],'image')[0] == ""){
    	                                if( ((string)$value->option_image != "") && ((string)$value->option_image != "null") ){
                        					$file = $path.'images/'.$value->option_image;
                        					
                                			$filename1 = basename($file);
                                			$split_filename = explode('.',$filename1);
                                			
                            				$upload_file = wp_upload_bits($filename1, null, file_get_contents($file));
                            				$attachment_id = "";
                            				if (!$upload_file['error']) {
                            					$wp_filetype = wp_check_filetype($filename1, null );
                            					$attachment = array(
                            						'post_mime_type' => $wp_filetype['type'],
                            						'post_title' => preg_replace('/\.[^.]+$/', '', $filename1),
                            						'post_content' => '',
                            						'post_status' => 'inherit'
                            					);
                            					$attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], $product_id );
                            					
                            					$main_image_id = $attachment_id;
                            					if (!is_wp_error($attachment_id)) {
                            						require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                            						$attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
                            						wp_update_attachment_metadata( $attachment_id,  $attachment_data );
                            					}
                            				}
                            				update_term_meta($row1["term_id"],'image',$attachment_id);
                            				echo $attachment_id.'</br>';
    	                                }
	                                }
	                                
	                            }
                            }elseif($ex_result1->num_rows > 1){
                                while($row1 = $ex_result1->fetch_assoc()) {
                                    $sql2 = "DELETE FROM ".$init["dbpre"]."termmeta WHERE term_id = ".$row1["term_id"];
                                    $ex_result2 = $con->query($sql2);
                                    wp_delete_term($row1["term_id"],'pa_'.$att_slug);
                                }
                            }else{
                                $rand = explode('-',$option_oid)[0];
                                $add_attribute = wp_insert_term($option_name,'pa_'.$att_slug ,array("slug" => fix_slug($rand)));       // add option attribute
                                print_r($add_attribute);
                                echo '</br></br>';
                                if(is_array($add_attribute)){                                                                                 // if adding option returns array is successful
                    			    $add_attribute_id = $add_attribute["term_id"];
                    			    update_term_meta($add_attribute_id,'Oid',$option_oid);                                                    // add oid to option term
                    			    $counter++;
                        		}    
                            }
                            
                            
                        }    
                    }
                        
                    //$sql = "UPDATE smartenize SET status = '1' WHERE id = '$id'";
                    //$con->query($sql);
                    
                }else{
                    echo 'UPDATE  attribute</br>';
                    $sql0 = "UPDATE ".$init["dbpre"]."woocommerce_attribute_taxonomies SET attribute_label = '".$att_name."',attribute_type = '".$attr_Style."' WHERE attribute_name = '".$att_slug."'";
                    $ex_result0 = $con->query($sql0);
                    //echo $sql0.'</br>';
                    //echo count($att_options).'</br>';
                    if(count($att_options) != 0){
                        
                        $unique[$att_name] = [];
                        foreach($att_options as $key => $value){                                                                           // after adding attribute check for options
                            $option_oid = (string)$value->option_Oid;
                            $option_name = (string)$value->option_name;
                            
                            $sql1 = "SELECT term_id FROM ".$init["dbpre"]."termmeta WHERE meta_value LIKE '".$option_oid."'";
                            $ex_result1 = $con->query($sql1);
                            //echo $sql1.'</br>';
                            //echo $option_oid.' => '.$ex_result1->num_rows.'</br>';
                            if ($ex_result1->num_rows == 1) {
	                            while($row1 = $ex_result1->fetch_assoc()) {
	                                
	                                $t = $row1["term_id"];
	                                $sql8 = "SELECT * FROM ".$init["dbpre"]."term_taxonomy WHERE term_id = '".$t."'";
                                    $ex_result8 = $con->query($sql8);
                                    
                                    while($row8 = $ex_result8->fetch_assoc()) {
                                        
                                        if($row8["taxonomy"] != 'pa_'.$att_slug){
                                            
                                                $tax = 'pa_'.$att_slug;
                                                $rand = explode('-',$option_oid)[0];
                                                $sql9 = "UPDATE ".$init["dbpre"]."term_taxonomy SET taxonomy = '".$tax."' WHERE term_id = ".$t;
                                                $sql10 = "UPDATE ".$init["dbpre"]."terms SET slug = '".$rand."' WHERE term_id = ".$t;
                                                $con->query($sql9);
                                                $con->query($sql10);
                                            
                                            
                                        }else{
                                            $rand = explode('-',$option_oid)[0];
                                            wp_update_term($row1["term_id"],'pa_'.$att_slug,array("name" => $option_name,'slug' => $rand));
                                        }
                                    }
	                                
	                                if( ((string)$value->option_Color != "") && ((string)$value->option_Color != "null") ){
	                                    update_term_meta($row1["term_id"],'color',(string)$value->option_Color);
	                                }
	                                
	                                //echo $value->option_image.'</br>';
	                                if(get_term_meta($row1["term_id"],'image')[0] == ""){
    	                                if( ((string)$value->option_image != "") && ((string)$value->option_image != "null") ){
        	                                $file = $path.'images/'.$value->option_image;
                        					
                                			$filename1 = basename($file);
                                			$split_filename = explode('.',$filename1);
                                			
                            				$upload_file = wp_upload_bits($filename1, null, file_get_contents($file));
                            				$attachment_id = "";
                            				if (!$upload_file['error']) {
                            					$wp_filetype = wp_check_filetype($filename1, null );
                            					$attachment = array(
                            						'post_mime_type' => $wp_filetype['type'],
                            						'post_title' => preg_replace('/\.[^.]+$/', '', $filename1),
                            						'post_content' => '',
                            						'post_status' => 'inherit'
                            					);
                            					$attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], $product_id );
                            					
                            					$main_image_id = $attachment_id;
                            					if (!is_wp_error($attachment_id)) {
                            						require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                            						$attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
                            						wp_update_attachment_metadata( $attachment_id,  $attachment_data );
                            					}
                            				}
                            				
                            				update_term_meta($row1["term_id"],'image',$attachment_id);
                            				echo $attachment_id.'</br>';
    	                                }
	                                }else{
	                                    
	                                    echo get_term_meta($row1["term_id"],'image')[0].'</br>';
	                                    //delete_image(get_term_meta($row1["term_id"],'image')[0],$con,$init,$path);
	                                    
	                                    
	                                    if( ((string)$value->option_image != "") && ((string)$value->option_image != "null") ){
        	                                $file = $path.'images/'.$value->option_image;
                        					
                                			$filename1 = basename($file);
                                			$split_filename = explode('.',$filename1);
                                			
                            				$upload_file = wp_upload_bits($filename1, null, file_get_contents($file));
                            				$attachment_id = "";
                            				if (!$upload_file['error']) {
                            					$wp_filetype = wp_check_filetype($filename1, null );
                            					$attachment = array(
                            						'post_mime_type' => $wp_filetype['type'],
                            						'post_title' => preg_replace('/\.[^.]+$/', '', $filename1),
                            						'post_content' => '',
                            						'post_status' => 'inherit'
                            					);
                            					$attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], $product_id );
                            					
                            					$main_image_id = $attachment_id;
                            					if (!is_wp_error($attachment_id)) {
                            						require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                            						$attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
                            						wp_update_attachment_metadata( $attachment_id,  $attachment_data );
                            					}
                            				}
                            				
                            				update_term_meta($row1["term_id"],'image',$attachment_id);
                            				echo $attachment_id.'</br>';
    	                                }
    	                                
	                                }
	                                
	                            }
	                            
                            }elseif($ex_result1->num_rows > 1){ // Check if term has Duplicate Oid and remove it
                                while($row1 = $ex_result1->fetch_assoc()) {
                                    //echo $option_oid.' => '.get_term_by('id',$row1["term_id"],'pa_'.$att_slug)[0]->name.'</br>';
                                    
                                    $sql2 = "DELETE FROM ".$init["dbpre"]."termmeta WHERE term_id = ".$row1["term_id"];
                                    $ex_result2 = $con->query($sql2);
                                    wp_delete_term($row1["term_id"],'pa_'.$att_slug);
                                    
                                }
                            }else{ // Add a new term
                                $rand = explode('-',$option_oid)[0];
                                $add_attribute = wp_insert_term($option_name,'pa_'.$att_slug ,array("slug" => fix_slug($rand)));       // add option attribute
                                print_r($add_attribute);
                                echo '</br></br>';
                                if(is_array($add_attribute)){                                                                                 // if adding option returns array is successful
                    			    $add_attribute_id = $add_attribute["term_id"];
                    			    update_term_meta($add_attribute_id,'Oid',$option_oid);                                                    // add oid to option term
                    			    $counter++;
                        		}    
                            }
                            
                        }
                        
                    }else{
                        //echo 'has_not_options</br>';
                    }
                    
                    $sql = "UPDATE smartenize SET status = '2' WHERE id = '$id'";
                    $con->query($sql);
                    
                }
                
            }else{
                $sql = "UPDATE smartenize SET status = '101' WHERE id = '$id'";
                $con->query($sql);
            }
		}
    }
    
    function generateRandomSlug($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    
    
    /*
    function delete_image($id,$con,$init,$path){
		require($path.'init'.$init["lang"].'.php');
		
		
		$sql_test = "SELECT * FROM ".$init["dbpre"]."postmeta WHERE post_id = '$id'";
		$ex_result_test = $con->query($sql_test);
		if ($ex_result_test->num_rows > 0) {  
			while($row_test = $ex_result_test->fetch_assoc()) {
								
				$add_path_temp = '';
				$init_file = '';				
				
				if($row_test["meta_key"] == '_wp_attached_file'){
					$add_path_temp = explode('/',$row_test["meta_value"]);													
					$year = $add_path_temp[0];
					$month = $add_path_temp[1];
					$init_file = $add_path_temp[2];
					
				}
				
				
				if(is_dir($path.'/'.$year.'/'.$month.'/'.$init_file)){
					//echo 'is_dir</br>';
				}else{
					//echo $path.'/'.$year.'/'.$month.'/'.$init_file.'</br>';
					//echo 'is_file</br>';
					$file_exist = glob($path.'/'.$year.'/'.$month.'/'.$init_file);
					if(!empty(glob($path.'/'.$year.'/'.$month.'/'.$init_file))){
						//echo 'exist</br>';
						
						if (!unlink($path.'/'.$year.'/'.$month.'/'.$init_file)) {
							//echo $path.'/'.$year.'/'.$month.'/'.$init_file.' could not delete.</br>';
						}else{
							//echo $path.'/'.$year.'/'.$month.'/'.$init_file.' deleted.</br>';
						}
						
					}else{
						//echo 'notexist</br>';
					}
				}
				
				$final_path = $path.$add_path;
				
				if($row_test["meta_key"] == '_wp_attachment_metadata'){								
					$data = unserialize($row_test["meta_value"]);
					foreach($data["sizes"] as $key => $value){									
						if(is_dir($path.'/'.$year.'/'.$month.'/'.$value["file"])){
							
						}else{
							//echo $path.'/'.$year.'/'.$month.'/'.$value["file"].'</br>';
							$file_exist = glob($path.'/'.$year.'/'.$month.'/'.$value["file"]);									
							if(!empty(glob($path.'/'.$year.'/'.$month.'/'.$value["file"]))){
								//echo 'exist</br>';
								
								if (!unlink($path.'/'.$year.'/'.$month.'/'.$value["file"])) {
									//echo $path.'/'.$year.'/'.$month.'/'.$value["file"].' could not delete.</br>';
								}else{
									//echo $path.'/'.$year.'/'.$month.'/'.$value["file"].' deleted.</br>';
								}
								
							}else{
								//echo 'notexist</br>';
								
							}
						}
					}
					
					
				}
				
				
			}
		}
		
		wp_delete_attachment($id,true);
		
	}
	*/
	
?>
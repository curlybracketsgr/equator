<?php

    function product_images($oid,$data,$id,$con,$init){
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
    		    
		        echo 'updating images for '.$wp_id.'</br>';
		        update_images($oid,$data,$path,$id,$con,$wp_id,$init);
		    }
        }else{
            //echo $id.' => '.$oid.' => 103</bR>';
            $sql = "UPDATE smartenize SET status = '103' WHERE id = '$id'";
            $con->query($sql);    
        }   
        
        
        
    }
    
    function update_images($oid,$data,$path,$id,$con,$product_id,$init){
        require($path.'init'.$init["lang"].'.php');
        
        $data_array = json_decode($data);
        $trigger = 0;
		if(empty($data_array)){
		    
		    $sql = "UPDATE smartenize SET status = '100' WHERE id = '$id'";
            $con->query($sql);
		}else{
		    $gallery_ids = "";
    	    $current_thumbnail = get_post_meta( $product_id, '_thumbnail_id')[0];
    	    $current_gallery = array_filter(explode(',',get_post_meta( $product_id, '_product_image_gallery')[0]));
    	    $gallery_array = [];
    	    foreach($current_gallery as $cimage){
    	        $sql0 = "SELECT * FROM ".$init["dbpre"]."postmeta WHERE post_id = '$cimage'";
                $ex_result0 = $con->query($sql0);
                if($ex_result0->num_rows > 0) {
                    while($row0 = $ex_result0->fetch_assoc()) {
                        $gallery_array[$row0["post_id"]] = get_post_meta($row0["post_id"],'smartenize_oid')[0];
                    }
                }
    	    }
    	    
    	    $incoming_images = [];
    	    foreach($data_array as $images){
    	        if($images->aa != 0){
    	            $incoming_images[$images->aa] = $images->Oid;   
    	        }
    	    }
    	    
    	    
    	    foreach($data_array->post_images as $images){                                                                                                                                                        // Parse images object
    	        
    	        if($images->aa == 0){                                                                                                                                                               // If aa = 0 then it's main image
    	            
    	            $files = glob($path."/images/".$images->filename);                                                                                                                              // get file path
                    $lastModifiedTimestamp = filemtime($files[0]);                                                                                                                                  // get ftp upload date
    	            $sql = "SELECT * FROM ".$init["dbpre"]."postmeta WHERE post_id = '$current_thumbnail' AND meta_value = '$lastModifiedTimestamp' AND meta_key = 'smartenize_time'";              // sql search of file according to date
                    $ex_result = $con->query($sql);
                    
                    if(!empty($files)){
                        if($ex_result->num_rows > 0) {                                                                                                                                                  // if the file exist and has same date IGNORE
                            //echo $current_thumbnail.' same date</br>';
                        }else{ 
                            //echo $current_thumbnail.' diff date</br>';
                            if($current_thumbnail == ""){                                                                                                                                               // The file doesn't exist
                                add_single_image($files,$product_id);                                                                                                                               // ADD FILE
                            }else{                                                                                                                                                                      // The file exist but it's old
                                delete_image($current_thumbnail,$con,$init,$path);                                                                                                                      // Delete file from database and FTP
                                add_single_image($files,$product_id);                                                                                                                               // ADD FILE
                    			
                            }
                        }
                    }else{
                        $trigger++;
                        $sql = "UPDATE smartenize SET status = '106' WHERE id = '$id'";
                        $con->query($sql);    
                    }
        			
    	        }else{
    	            $files = glob($path."/images/".$images->filename);
    	            $lastModifiedTimestamp = filemtime($files[0]);
                    
                    $removeable_image = array_diff($gallery_array,$incoming_images);                                                                                                                // Find differences in gallery images
                    foreach($removeable_image as $key => $value){
                        if (($master_key = array_search($key, $current_gallery)) !== false) {
                            unset($current_gallery[$master_key]);                                                                                                                                   // remove images that not appear in refreshed gallery options
                        }
                    }
                    
    	            if(in_array($images->Oid,$gallery_array)){
    	                $image_key = array_search($images->Oid,$gallery_array);
    	                if($lastModifiedTimestamp == get_post_meta($image_key,'smartenize_time')[0]){
    	                    //echo 'same time</br>';
    	                }else{
    	                    //echo 'diff time</br>';    
    	                    if (($master_key = array_search($image_key, $current_gallery)) !== false) {
                                unset($current_gallery[$master_key]);
                            }
                            if(!empty($files)){
                		        $file = $files[0];
                							
                    			$filename1 = basename($file);
                    			$split_filename = explode('.',$filename1);
                    			
                				$upload_file = wp_upload_bits($filename1, null, file_get_contents($file));
                				$attachment_id = "";
                				if (!$upload_file['error']) {
                					$wp_filetype = wp_check_filetype($filename1, null );
                					$attachment = array(
                						'post_mime_type' => $wp_filetype['type'],
                						'post_parent' => $product_id,
                						'post_title' => preg_replace('/\.[^.]+$/', '', $filename1),
                						'post_content' => '',
                						'post_status' => 'inherit'
                					);
                					$attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], $product_id );
                					update_post_meta($attachment_id,'smartenize_oid',$images->Oid);
                					update_post_meta($attachment_id,'smartenize_time',$lastModifiedTimestamp);
                					echo $attachment_id.' => '.$lastModifiedTimestamp.'</br>';
                					$main_image_id = $attachment_id;
                					if (!is_wp_error($attachment_id)) {
                						require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                						$attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
                						wp_update_attachment_metadata( $attachment_id,  $attachment_data );
                					}
                				}
                				$current_gallery[] = $attachment_id;
                		    }else{
                		        $trigger++;
                                $sql = "UPDATE smartenize SET status = '106' WHERE id = '$id'";
                                $con->query($sql);    
                            }
    	                }
    	            }else{
    	                //echo 'image not exist so add it</bR>';
    	                if(!empty($files)){
            		        $file = $files[0];
            							
                			$filename1 = basename($file);
                			$split_filename = explode('.',$filename1);
                			
            				$upload_file = wp_upload_bits($filename1, null, file_get_contents($file));
            				$attachment_id = "";
            				if (!$upload_file['error']) {
            					$wp_filetype = wp_check_filetype($filename1, null );
            					$attachment = array(
            						'post_mime_type' => $wp_filetype['type'],
            						'post_parent' => $product_id,
            						'post_title' => preg_replace('/\.[^.]+$/', '', $filename1),
            						'post_content' => '',
            						'post_status' => 'inherit'
            					);
            					$attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], $product_id );
            					update_post_meta($attachment_id,'smartenize_oid',$images->Oid);
            					update_post_meta($attachment_id,'smartenize_time',$lastModifiedTimestamp);
            					echo $attachment_id.' => '.$lastModifiedTimestamp.'</br>';
            					$main_image_id = $attachment_id;
            					if (!is_wp_error($attachment_id)) {
            						require_once(ABSPATH . "wp-admin" . '/includes/image.php');
            						$attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
            						wp_update_attachment_metadata( $attachment_id,  $attachment_data );
            					}
            				}
            				$current_gallery[] = $attachment_id;
            		    }else{
            		        $trigger++;
                            $sql = "UPDATE smartenize SET status = '106' WHERE id = '$id'";
                            $con->query($sql);    
                        }
    	            }
    	            
    	        }
    	        
    	    }
    	    
    	    update_post_meta( $product_id, '_product_image_gallery', implode(',',$current_gallery) );
		}
		
		
		if($trigger == 0){
		    $sql = "UPDATE smartenize SET status = '1' WHERE id = '$id'";
            $con->query($sql); 
		}
    }
    
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
	
	function add_single_image($files,$product_id){
	    $file = $files[0];
            								
		$filename = basename($file);
		$split_filename = explode('.',$filename);
		
		$upload_file = wp_upload_bits($filename, null, file_get_contents($file));
		$attachment_id = "";
		if (!$upload_file['error']) {
			$wp_filetype = wp_check_filetype($filename, null );
			$attachment = array(
				'post_mime_type' => $wp_filetype['type'],
				'post_parent' => $product_id,
				'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
				'post_content' => '',
				'post_status' => 'inherit'
			);
			$attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], $product_id );
			update_post_meta($attachment_id,'smartenize_oid',$images->Oid);
			update_post_meta($attachment_id,'smartenize_time',$lastModifiedTimestamp);
			$main_image_id = $attachment_id;
			if (!is_wp_error($attachment_id)) {
				require_once(ABSPATH . "wp-admin" . '/includes/image.php');
				$attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
				wp_update_attachment_metadata( $attachment_id,  $attachment_data );
			}
		}
		update_post_meta( $product_id, '_thumbnail_id', $main_image_id );    
	}
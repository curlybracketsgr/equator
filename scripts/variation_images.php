<?php
    function variation_images($oid,$data,$id,$con,$init){
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
    		    
		        echo 'updating variation images for '.$wp_id.'</br>';
		        update_variation_images($oid,$data,$path,$id,$con,$wp_id,$init);
		    }
        }else{
            echo 'Variation not found to add images</br>';
            $sql = "UPDATE smartenize SET status = '103' WHERE id = '$id'";
            $con->query($sql);
        }    
        
        
        
    }
    
    function update_variation_images($oid,$data,$path,$id,$con,$product_id,$init){
        require($path.'init'.$init["lang"].'.php');
        
        $data_array = json_decode($data);
        
        $trigger = 0;
		if(empty($data_array)){
		    
		    $sql = "UPDATE smartenize SET status = '100' WHERE id = '$id'";
            $con->query($sql);
		}else{
		    
		    
		    $gallery_ids = "";
    	    $current_thumbnail = get_post_meta( $product_id, '_thumbnail_id')[0];
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
    	    foreach($data_array->post_images as $images){
    	        if($images->aa != 0){
    	            $incoming_images[$images->aa] = $images->Oid;   
    	        }
    	    }
    	    
    	                                                                                                                                 // Parse images object
    	        
	        if($data_array->post_images[0]->aa == 0){                                                                                                                                                               // If aa = 0 then it's main image
	            //echo $path."images/".$data_array->filename.'</br>';
	            $files = glob($path."images/".$data_array->post_images[0]->filename);                                                                                                                              // get file path
	            
                
                add_variation_image($files,$product_id);                                                                                                                               // ADD FILE
                $sql = "UPDATE smartenize SET status = '1' WHERE id = '$id'";
                $con->query($sql);     
	        }
    	}
		
		
		if($trigger == 0){
		    
		}
    }
    
    
    function add_variation_image($files,$product_id){
        
	    
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
		//echo $main_image_id.'</br>';
		update_post_meta( $product_id, '_thumbnail_id', $main_image_id );
		
	}
    
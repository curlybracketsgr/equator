<?php
    $path_temp = explode('/',__FILE__);
    unset($path_temp[count($path_temp) - 1]);
    $path = implode('/',$path_temp).'/';
    require($path.'/init_en.php');
    
    
    foreach (glob($path.'scripts/*.php') as $filename){
        include_once($filename);
    }
    
   
    $con = mysqli_connect($init["dbhost"],$init["dbuser"],$init["dbpass"],$init["dbname"]);
    if ($con->connect_error) {
      die("Connection failed: " . $con->connect_error);
    }
    
    
    $con->set_charset('utf8');
    $sql = "SELECT * FROM smartenize WHERE (status IS NULL OR status = '') AND entity LIKE '%_En' ORDER BY FIELD(entity, 'Tags_EN', 'Category_EN', 'Attribute_EN','Filters_EN','Categories_EN','Attributes_EN','Products_EN','Variations_EN', 'Product_EN', 'Inventory_EN', 'Shipping_EN', 'General_EN', 'Variation_EN', 'Product Images_EN','Product Relative Files_EN', 'Variation Images_EN','Linked Products_EN')";
    
    
    $ex_result = $con->query($sql);
    
    // INITIALIZE EXISTING CATEGORIES
    $product_terms_temp = get_terms( array('taxonomy' => 'product_cat','hide_empty' => false) );
    
    $product_terms = [];		
	foreach($product_terms_temp as $term){			
		$term_meta = get_term_meta($term->term_id,'Oid');			
		$product_terms[$term->term_id] = $term_meta[0];
	}
	
	// INITIALIZE EXISTING TAGS
    $product_tags_temp = get_terms( array('taxonomy' => 'product_tag','hide_empty' => false) );
    
    $product_tags = [];		
	foreach($product_tags_temp as $tag){			
		$tag_meta = get_term_meta($tag->term_id,'Oid');			
		$product_tags[$tag->term_id] = $tag_meta[0];
	}
	
	// INITIALIZE ATTRIBUTES
	$sidebar = get_option('sidebars_widgets');
    $attributes = wc_get_attribute_taxonomies();
	
    $limit_products = 0;
    
    $products_query = "SELECT post_id,meta_value FROM ".$init['dbpre']."posts,".$init['dbpre']."postmeta WHERE ID = post_id AND (post_type = 'product' OR post_type = 'product_variation') AND post_status = 'publish' AND meta_key = '_sale_price'";
	$today = strtotime(date('Y-m-d'));
	$ex_result1 = $con->query($products_query);
	
    if ($ex_result1->num_rows > 0) {  
        while($row1 = $ex_result1->fetch_assoc()) {
            if($row1["meta_value"] != ''){
                
                if(get_post_meta($row1["post_id"],'_sale_price_dates_to')[0] != ""){
                    /*
                    if(get_post_meta($row1["post_id"],'_sale_price_dates_to')[0] < $today){
                        echo $row1["post_id"].' => '.date('d-m-Y',get_post_meta($row1["post_id"],'_sale_price_dates_to')[0]).' => '.date('d-m-Y',$today).' expire</br>';
                    }else{
                        echo $row1["post_id"].' => '.date('d-m-Y',get_post_meta($row1["post_id"],'_sale_price_dates_to')[0]).' => '.date('d-m-Y',$today).' keep</br>';
                    }
                    echo get_post_meta($row1["post_id"],'_sale_price_dates_to')[0].' => '.$today.'</br>';
                    */
                    
                    if(get_post_meta($row1["post_id"],'_sale_price_dates_to')[0] < $today){
                        echo $row1["post_id"].' => '.$row1["meta_value"].' => '.get_post_type($row1["post_id"]).'</br>';
                        echo date('d-m-Y',get_post_meta($row1["post_id"],'_sale_price_dates_to')[0]).' => '.date('d-m-Y',$today).'</br>';
                        update_post_meta($row1["post_id"],'_sale_price_dates_to','');
                        update_post_meta($row1["post_id"],'_sale_price_dates_from','');
                        update_post_meta($row1["post_id"],'_sale_price','');
                    }
                    
                }
                
            }
        }
    }
    
    if ($ex_result->num_rows > 0) {  
        echo $ex_result->num_rows.' works to process</br>';
		
		while($row = $ex_result->fetch_assoc()) {
		    
		    $id = $row["id"];
			$entity = $row["entity"];
			$oid = $row["oid"];
			$data = $row["data"];
			
			echo $entity.'</br>';
			
			
			switch ($entity) {
                case "Product_En":
                    sm_get_product($oid,$data,$id,$con,$product_terms,$product_tags,$init);
                    usleep(1000000);
                    $limit_products++;
                    break;
                
                case "Category_En":
                    sm_get_categories($oid,$data,$id,$con,$product_terms,$init);
                    break;
                case "Attribute_En":
                    sm_get_attributes($oid,$data,$id,$con,$init);
                    break;
                case "Inventory_En":
                    sm_get_inventory($oid,$data,$id,$con,$init);
                    break;
                case "Shipping_En":
                    sm_get_shipping($oid,$data,$id,$con,$init);
                    break;    
                case "General_En":
                    sm_get_general($oid,$data,$id,$con,$init);
                    break;
                case "Barcode_En":
                    break;
                case "Filtres_En":
                    sm_filters($oid,$data,$id,$con,$sidebar,$attributes,$init);
                    break;
                case "Tags_En":
                    sm_get_tags($oid,$data,$id,$con,$product_tags,$init);
                    break;
                case "Products_En":
                    delete_products($oid,$data,$id,$con,$init);
                    break;
                case "Categories_En":
                    //delete_categories($oid,$data,$id,$con,$product_terms,$init);
                    break;
                case "Attributes_En":
                    delete_attributes($oid,$data,$id,$con,$init);
                    break;    
                case "Variation_En":
                    sm_get_variation($oid,$data,$id,$con,$attributes,$init);
                    usleep(1000000);
                    break;      
                case "Product Images_En":
                    product_images($oid,$data,$id,$con,$init);
                    usleep(1000000);
                    break;  
                case "Product Relative Files_En":
                    product_files($oid,$data,$id,$con,$init);
                    
                    break;      
                case "Variation Images_En":
                    variation_images($oid,$data,$id,$con,$init);
                    usleep(1000000);
                    break;      
                case "Variations_En":
                    delete_variations($oid,$data,$id,$con,$init);
                    break;
                case "Linked Products_En":
                    sm_get_linked_products($oid,$data,$id,$con,$init);
                    break;
                default:
                echo "";
                
            }
            
            
		}
		
	}else{
	    echo 'All clear!!!</br>';
	}
	
    
    
    $con->close();
   
    function fix_slug($text){
		$str = sanitize_title_with_dashes(strtolower(slugify_text( trim(strval($text)) )));
		return $str;
	}
	
	function slugify_text($text){  	
		$str = str_replace(" ","-",$text);
		$str = str_replace("(","",$str);
		$str = str_replace(")","",$str);
		$str = preg_replace("/&#91;^a-zA-Z0-9&#93;/", "", $str);

		$str = str_replace(array("α","Α","ά","Ά"),'a',$str);
		$str = str_replace(array("β","Β"),'b',$str);
		$str = str_replace(array("γ","Γ"),'g',$str);
		$str = str_replace(array("δ","Δ"),'d',$str);
		$str = str_replace(array("ε","Ε","έ","Έ"),'e',$str);
		$str = str_replace(array("ζ","Ζ"),'z',$str);
		$str = str_replace(array("η","Η","ή","Ή"),'i',$str);
		$str = str_replace(array("θ","Θ"),'th',$str);
		$str = str_replace(array("ι","Ι","ϊ","ί","Ί","Ϊ","ΐ"),'i',$str);
		$str = str_replace(array("κ","Κ"),'k',$str);
		$str = str_replace(array("λ","Λ"),'l',$str);
		$str = str_replace(array("μ","Μ"),'m',$str);
		$str = str_replace(array("ν","Ν"),'n',$str);
		$str = str_replace(array("ξ","Ξ"),'ks',$str);
		$str = str_replace(array("ο","Ο","ό","Ό"),'o',$str);
		$str = str_replace(array("π","Π"),'p',$str);
		$str = str_replace(array("ρ","Ρ"),'r',$str);
		$str = str_replace(array("σ","ς","Σ"),'s',$str);
		$str = str_replace(array("τ","Τ"),'t',$str);
		$str = str_replace(array("υ","Υ","ϋ","ύ","Ύ","Ϋ","ΰ"),'u',$str);
		$str = str_replace(array("φ","Φ"),'f',$str);
		$str = str_replace(array("χ","Χ"),'x',$str);
		$str = str_replace(array("ψ","Ψ"),'ps',$str);
		$str = str_replace(array("ω","Ω","ώ","Ώ"),'w',$str);
		return $str;
	}
	
	
?>
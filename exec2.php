<?php
    $path_temp = explode('/',__FILE__);
    unset($path_temp[count($path_temp) - 1]);
    $path = implode('/',$path_temp).'/';
    require($path.'/init.php');
    
    foreach (glob($path.'scripts/*.php') as $filename){
        include_once($filename);
    }
    
    $con = mysqli_connect($init["dbhost"],$init["dbuser"],$init["dbpass"],$init["dbname"]);
    if ($con->connect_error) {
      die("Connection failed: " . $con->connect_error);
    }
    
    
    $con->set_charset('utf8');
    //$sql = "SELECT * FROM smartenize WHERE (status = '' OR status = '103' OR status = '2') AND entry_date LIKE '$date%' ORDER BY FIELD(entity, 'Tags', 'Category', 'Attribute','Filters','Categories','Attributes','Products','Variations', 'Product', 'Inventory', 'Shipping', 'General', 'Variation', 'Product Images','Product Relative Files', 'Variation Images','Linked Products')";
    //OR status = '103'
    $sql = "SELECT * FROM smartenize WHERE status = '103' ORDER BY FIELD(entity, 'Tags', 'Category', 'Attribute','Filters','Categories','Attributes','Products','Variations', 'Product', 'Inventory', 'Shipping', 'General', 'Variation', 'Product Images','Product Relative Files', 'Variation Images','Linked Products')";
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
    
    
    if ($ex_result->num_rows > 0) {  
        echo $ex_result->num_rows.' works to process</br>';
		
		while($row = $ex_result->fetch_assoc()) {
		    
		    $id = $row["id"];
			$entity = $row["entity"];
			$oid = $row["oid"];
			$data = $row["data"];
			
			switch ($entity) {
                case "Product":
                    sm_get_product($oid,$data,$id,$con,$product_terms,$product_tags);
                    
                    $limit_products++;
                    break;
                case "Category":
                    sm_get_categories($oid,$data,$id,$con,$product_terms);
                    break;
                case "Attribute":
                    sm_get_attributes($oid,$data,$id,$con);
                    break;
                case "Inventory":
                    sm_get_inventory($oid,$data,$id,$con);
                    break;
                case "Shipping":
                    sm_get_shipping($oid,$data,$id,$con);
                    break;    
                case "General":
                    sm_get_general($oid,$data,$id,$con);
                    break;
                case "Barcode":
                    break;
                case "Filters":
                    sm_filters($oid,$data,$id,$con,$sidebar,$attributes);
                    break;
                case "Tags":
                    sm_get_tags($oid,$data,$id,$con,$product_tags);
                    break;
                case "Products":
                    delete_products($oid,$data,$id,$con);
                    break;
                case "Categories":
                    delete_categories($oid,$data,$id,$con,$product_terms);
                    break;
                case "Attributes":
                    delete_attributes($oid,$data,$id,$con);
                    break;    
                case "Variation":
                    sm_get_variation($oid,$data,$id,$con,$attributes);
                    break;      
                case "Product Images":
                    product_images($oid,$data,$id,$con);
                    break;  
                case "Product Relative Files":
                    product_files($oid,$data,$id,$con);
                    break;      
                case "Variation Images":
                    variation_images($oid,$data,$id,$con);
                    break;      
                case "Variations":
                    delete_variations($oid,$data,$id,$con,$attributes);
                    break;
                case "Linked Products":
                    sm_get_linked_products($oid,$data,$id,$con);
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
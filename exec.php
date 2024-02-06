<?php
    $path_temp = explode('/',__FILE__);
    unset($path_temp[count($path_temp) - 1]);
    $path = implode('/',$path_temp).'/';
    require($path.'/init.php');
    //print_r($init);
    //echo '</br></br>';
    
    foreach (glob($path.'scripts/*.php') as $filename){
        include_once($filename);
    }
    
    $con = mysqli_connect($init["dbhost"],$init["dbuser"],$init["dbpass"],$init["dbname"]);
    if ($con->connect_error) {
      die("Connection failed: " . $con->connect_error);
    }
    
    
    
    $con->set_charset('utf8');
    $sql = "SELECT * FROM smartenize WHERE (status IS NULL OR status = '') AND entity NOT LIKE '%_en' ORDER BY FIELD(entity, 'Tags', 'Category', 'Attribute','Filters','Categories','Attributes','Products','Variations', 'Product', 'Inventory', 'Shipping', 'General', 'Variation', 'Product Images','Product Relative Files', 'Variation Images','Linked Products')";
    
    /*
    $current_day = date('d');
    if($current_day == 29){
        $current_month = date('m');
        $current_year = date('Y');
        $clear_records = "DELETE FROM smartenize WHERE entry_date NOT LIKE '%".$current_year."-".$current_month."%'";
        
        $clear_table = $con->query($clear_records);
    }
    OR status = '103'
    $sql = "SELECT * FROM smartenize WHERE status = '' AND (";
    for($i = 0; $i < 10; $i++){
        $date = date('Y-m-d',strtotime('-'.$i.' days', strtotime( date('Y-m-d'))));
        if($i == 9){
            $sql .= "entry_date LIKE '$date%'";
        }else{
            $sql .= "entry_date LIKE '$date%' OR ";
        }
    }
    
    $sql .= ") ORDER BY FIELD(entity, 'Tags', 'Category', 'Attribute','Filters','Categories','Attributes','Products','Variations', 'Product', 'Inventory', 'Shipping', 'General', 'Variation', 'Product Images','Product Relative Files', 'Variation Images','Linked Products')";
    */
    
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
                if(!empty(get_post_meta($row1["post_id"],'_sale_price_dates_to'))){
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
    }
    
    //echo $ex_result->num_rows;
    if ($ex_result->num_rows > 0) {  
        echo $ex_result->num_rows.' works to process</br>';
		
		while($row = $ex_result->fetch_assoc()) {
		    
		    $id = $row["id"];
			$entity = $row["entity"];
			$oid = $row["oid"];
			$data = $row["data"];
			echo $entity.'</bR>';
			//if($limit_products < 1){
			
			switch ($entity) {
                
                case "Product":
                    usleep(10000);
                    sm_get_product($oid,$data,$id,$con,$product_terms,$product_tags,$init);
                    
                    $limit_products++;
                    break;
                case "Attribute":
                	usleep(10000);
                	sm_get_attributes($oid,$data,$id,$con,$init);
                	break;
                
                case "Inventory":
                	sm_get_inventory($oid,$data,$id,$con,$init);
                	break;
                	
                case "Shipping":
                	sm_get_shipping($oid,$data,$id,$con,$init);
                	break;    
                case "General":
                	sm_get_general($oid,$data,$id,$con,$init);
                	break;
                
                case "Category":
                	sm_get_categories($oid,$data,$id,$con,$product_terms,$init);
                	break;
                case "Barcode":
                	break;
                case "Filtres":
                	sm_filters($oid,$data,$id,$con,$sidebar,$attributes,$init);
                	break;
                case "Variation":
                	sm_get_variation($oid,$data,$id,$con,$attributes,$init);
                	break;      
                case "Products":
                	delete_products($oid,$data,$id,$con,$init);
                	break;
                case "Categories":
                	//delete_categories($oid,$data,$id,$con,$product_terms,$init);
                	break;
                case "Attributes":
                	delete_attributes($oid,$data,$id,$con,$init);
                	break;  
                case "Variation Images":
                	usleep(10000);
                	variation_images($oid,$data,$id,$con,$init);
                	$limit_products++;
                	break; 
                case "Product Images":
                	usleep(10000);
                	product_images($oid,$data,$id,$con,$init);
                	break;  
                case "Product Relative Files":
                	usleep(10000);
                	product_files($oid,$data,$id,$con,$init);
                	break;      
                case "Variations":
                	usleep(10000);
                	delete_variations($oid,$data,$id,$con,$init);
                	break; 
                case "Tags":
                    sm_get_tags($oid,$data,$id,$con,$product_tags,$init);
                	break;
                case "Linked Products":
                	sm_get_linked_products($oid,$data,$id,$con,$init);
                	break;
                default:
                echo "";
                
            }
			//}
			
            
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
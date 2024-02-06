<?php
    $path_temp = explode('/',__FILE__);
    unset($path_temp[count($path_temp) - 1]);
    $path = implode('/',$path_temp).'/';
    require($path.'/init_en.php');
    
    $con = mysqli_connect($init["dbhost"],$init["dbuser"],$init["dbpass"],$init["dbname"]);
    if ($con->connect_error) {
      die("Connection failed: " . $con->connect_error);
    }
    
    $con->set_charset('utf8');
    
    $sql = "SELECT * FROM ".$init["dbpre"]."woocommerce_attribute_taxonomies";
    $ex_result = $con->query($sql);
        
    if ($ex_result->num_rows > 0) {
	    while($row = $ex_result->fetch_assoc()) {
	        
	        
	        $existing_option = get_option('_transient_wvs_get_wc_attribute_taxonomy_pa_'.$row["attribute_name"]);
	        if($existing_option){
	            echo $row["attribute_type"].'</br>';
	            echo 'exist</br>';
	            print_r($existing_option);
    	        echo '</br>';
	            $existing_option->attribute_type = $row["attribute_type"];
	            
    	        echo $existing_option->attribute_type.'</br>';
    	        print_r($existing_option);
    	        echo '</br></br>';
    	        update_option('_transient_wvs_get_wc_attribute_taxonomy_pa_'.$row["attribute_name"],$existing_option);
	        }else{
	            
	        }
	        
	    }
    }

//_transient_wvs_get_wc_attribute_taxonomy_pa_3229b96d-5f58
?>
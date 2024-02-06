<?php
    $path_temp = explode('/',__FILE__);
    unset($path_temp[count($path_temp) - 1]);
    $path = implode('/',$path_temp).'/';
    require($path.'/init.php');
    
    
    $_pf = new WC_Product_Factory();  
    $con = mysqli_connect($init["dbhost"],$init["dbuser"],$init["dbpass"],$init["dbname"]);
    if ($con->connect_error) {
      die("Connection failed: " . $con->connect_error);
    }
    
    
    $con -> set_charset("utf8");
    
    $sql = "SELECT * FROM ".$init["dbpre"]."posts WHERE post_type = 'product'";
    $ex_result = $con->query($sql);
    echo '
        <table>
            <tr>
                <td align="center" style="background:#000; color:#fff;">ID</td>
                <td align="center" style="background:#000; color:#fff;">Product</td>
                <td align="center" style="background:#000; color:#fff;">Price</td>
                <td align="center" style="background:#000; color:#fff;">Weight</td>
                <td align="center" style="background:#000; color:#fff;">Image</td>
                <td align="center" style="background:#000; color:#fff;">Status</td>
                <td align="center" style="background:#000; color:#fff;">OID</td>
                <td align="center" style="background:#000; color:#fff;">terms</td>
            </tr>
    ';
    if ($ex_result->num_rows > 0) {
	    while($row = $ex_result->fetch_assoc()) {
            $id = $row["ID"];
            $oid = get_post_meta($row["ID"],'_oid')[0];
            $product = $_pf->get_product($id);
            $weight = get_post_meta($id,'_weight')[0];
            $price = get_post_meta($id,'_regular_price')[0];
            $status = get_post_status($id);
            $image = has_post_thumbnail($id);
            $terms = get_the_terms($id,'product_cat');
            
            if($weight == 0){
                if($product->is_type( 'variable' )){
                    echo '
                        <tr>                
                    ';
                }else{
                    $sql7 = "UPDATE ".$init["dbpre"]."posts SET post_status = 'draft' WHERE ID = '".$id."'";
                    $ex_result7 = $con->query($sql7);
                    
                    $sql6 = "UPDATE smartenize SET status = '' WHERE oid = '".$oid."' AND entity = 'Shipping' LIMIT 1";
                    $ex_result6 = $con->query($sql6);
                    
                    $sql2 = "SELECT * FROM smartenize WHERE oid = '".$oid."' AND entity = 'Shipping' LIMIT 1";
                    $ex_result2 = $con->query($sql2);
                    
                    if ($ex_result2->num_rows > 0) {
                        while($row2 = $ex_result2->fetch_assoc()) {
                            echo '
                                <tr style="background:purple;">
                                    <td style="border:solid thin black; color:#fff;" colspan="7">
                            ';
                                    echo "Data received : ".$row2["data"].'</br>';
    	                            echo "Date : ".$row2["entry_date"];
                                    //$sql3 = "UPDATE smartenize SET status = '' WHERE id = ".$row2["id"];
    	                            //$ex_result3 = $con->query($sql3);
                            echo '
                                    </td>
                               </tr>
                            ';
                        }
                    }
                    
                    echo '
                        <tr style="background:orange;">                
                    ';
                }
            }elseif(($price == 0) || ($price == "")){
                
                if($product->is_type( 'variable' )){
                    
                }else{
                    $sql7 = "UPDATE ".$init["dbpre"]."posts SET post_status = 'draft' WHERE ID = '".$id."'";
                    $ex_result7 = $con->query($sql7);
                        
                    $sql6 = "UPDATE smartenize SET status = '' WHERE oid = '".$oid."' AND entity = 'General' LIMIT 1";
                    $ex_result6 = $con->query($sql6);
                    
                    $sql2 = "SELECT * FROM smartenize WHERE oid = '".$oid."' AND entity = 'General' LIMIT 1";
	                $ex_result2 = $con->query($sql2);
	                
	                if ($ex_result2->num_rows > 0) {
	                    while($row2 = $ex_result2->fetch_assoc()) {
	                        echo '
	                            <tr style="background:purple;">
	                                <td style="border:solid thin black; color:#fff;" colspan="7">
	                        ';
	                                echo "Data received : ".$row2["data"].'</br>';
    	                            echo "Date : ".$row2["entry_date"];
	                                //$sql3 = "UPDATE smartenize SET status = '' WHERE id = ".$row2["id"];
    	                            //$ex_result3 = $con->query($sql3);
	                        echo '
	                                </td>
	                           </tr>
	                        ';
	                    }
	                }
                    echo '
                        <tr style="background:red;">
                            
                    ';
                }
            }elseif($image == 0){
                echo '
                    <tr style="background:cyan;">                
                ';
            }elseif(empty($terms)){
                
                echo '
                    <tr style="background:brown;">                
                ';
            }else{
                echo '
                    <tr>                
                ';
            }
            echo '
                <td style="border:solid thin black;">'.$id.'</td>
                <td style="border:solid thin black;">'.get_the_title($id).'</td>
                <td style="border:solid thin black;">'.$price.'</td>
                <td style="border:solid thin black;">'.$weight.'</td>
                <td style="border:solid thin black;">'.$image.'</td>
                <td style="border:solid thin black;"></td>
                <td style="border:solid thin black;">'.$oid.'</td>
                <td style="border:solid thin black;">
            ';
                if(empty($terms)){
                    $sql4 = "SELECT * FROM smartenize WHERE entity = 'Product' AND oid = '".$oid."' LIMIT 1";
                    $ex_result4 = $con->query($sql4);  
                    
                    if ($ex_result4->num_rows > 0) {
                        while($row4 = $ex_result4->fetch_assoc()) {
                            $current_record = $row4["id"];
                            $extract_data = json_decode($row4["data"]);
                            foreach($extract_data->post_categories as $category){
                                $cat_Oid = $category->Oid;
                                
                                $sql5 = "SELECT term_id FROM ".$init["dbpre"]."termmeta WHERE meta_value = '".$cat_Oid."'";
                                $ex_result5 = $con->query($sql5);  
                                
                                if ($ex_result5->num_rows > 0) {
                                    while($row5 = $ex_result5->fetch_assoc()) {
                                        echo 'Category not assigned</br>';
                                        $sql6 = "UPDATE smartenize SET status = '' WHERE id = '".$current_record."'";
                                        $ex_result6 = $con->query($sql6);
                                    }
                                }else{
                                    echo 'Category not exist</br>'.$cat_Oid;
                                    $sql6 = "UPDATE smartenize SET status = '' WHERE oid = '".$cat_Oid."'";
                                    $ex_result6 = $con->query($sql6);
                                }
                            }
                        }
                    }    
                }else{
                    foreach($terms as $term){
                       echo $term->name.', ';
                    }
                }
            echo '
                </td>
            </tr>
            ';
            $sql1 = "SELECT * FROM ".$init["dbpre"]."posts WHERE post_parent = '".$id."' AND post_type = 'product_variation'";
	        $ex_result1 = $con->query($sql1);
	        
	        if ($ex_result1->num_rows > 0) {
	            while($row1 = $ex_result1->fetch_assoc()) {
	                $vid = $row1["ID"];
	                $vweight = get_post_meta($vid,'_weight')[0];
                    $vprice = get_post_meta($vid,'_regular_price')[0];
                    $void = get_post_meta($row1["ID"],'_oid')[0];
                    
                    if($vweight == 0){
                        
                        $sql7 = "UPDATE ".$init["dbpre"]."posts SET post_status = 'draft' WHERE ID = '".$vid."'";
                        $ex_result7 = $con->query($sql7);
                        
                        $sql6 = "UPDATE smartenize SET status = '' WHERE oid = '".$void."' AND entity = 'Shipping' LIMIT 1";
                        $ex_result6 = $con->query($sql6);
                        
                        $sql2 = "SELECT * FROM smartenize WHERE oid = '".$void."' AND entity = 'Shipping' LIMIT 1";
	                    $ex_result2 = $con->query($sql2);
	                    
	                    if ($ex_result2->num_rows > 0) {
    	                    while($row2 = $ex_result2->fetch_assoc()) {
    	                        echo '
    	                            <tr style="background:purple;">
    	                                <td style="border:solid thin black; color:#fff;" colspan="7">
    	                        ';
    	                                echo "Data received : ".$row2["data"].'</br>';
    	                                echo "Date : ".$row2["entry_date"];
    	                                //$sql3 = "UPDATE smartenize SET status = '' WHERE id = ".$row2["id"];
    	                                //$ex_result3 = $con->query($sql3);
    	                        echo '
    	                                </td>
    	                           </tr>
    	                        ';
    	                    }
    	                }
                        echo '
                            <tr style="background:orange;">                
                        ';
                    }elseif(($vprice == 0) || ($vprice == "")){
                        $sql7 = "UPDATE ".$init["dbpre"]."posts SET post_status = 'draft' WHERE ID = '".$vid."'";
                        $ex_result7 = $con->query($sql7);
                        
                        $sql6 = "UPDATE smartenize SET status = '' WHERE oid = '".$void."' AND entity = 'General' LIMIT 1";
                        $ex_result6 = $con->query($sql6);
                        
                        $sql2 = "SELECT * FROM smartenize WHERE oid = '".$void."' AND entity = 'General' LIMIT 1";
                        $ex_result2 = $con->query($sql2);
                        
                        if ($ex_result2->num_rows > 0) {
                            while($row2 = $ex_result2->fetch_assoc()) {
                                echo '
                                    <tr style="background:purple;">
                                        <td style="border:solid thin black; color:#fff;" colspan="7">
                                ';
                                        echo "Data received : ".$row2["data"].'</br>';
    	                                echo "Date : ".$row2["entry_date"];
                                        $sql3 = "UPDATE smartenize SET status = '' WHERE id = ".$row2["id"];
        	                            $ex_result3 = $con->query($sql3);
                                echo '
                                        </td>
                                   </tr>
                                ';
                            }
                        }
                        echo '
                            <tr style="background:red;">                
                        ';   
                    }else{
    	                echo '
        	                <tr>
        	            ';
                    }
                    echo '
                            <td style="border:solid thin #ebebeb;">'.$vid.'</td>
                            <td style="border:solid thin #ebebeb;">'.get_the_title($vid).'</td>
                            <td style="border:solid thin #ebebeb;">'.$vprice.'</td>
                            <td style="border:solid thin #ebebeb;">'.$vweight.'</td>
                            <td style="border:solid thin #ebebeb;"></td>
                            <td style="border:solid thin #ebebeb;"></td>
                            <td style="border:solid thin #ebebeb;">'.$void.'</td>
                        </tr>
	               '; 
	            }
	        }
            /*
            if($product->is_type( 'variable' )){
                echo $id.' (variable) => '.$weight .' => '.$price.'</br>';
            }else{
                echo $id.' (simple) => '.$weight .' => '.$price.'</br>';
            }
            */
            
	    }
    }
    
    echo '
        </table>
    ';
?>
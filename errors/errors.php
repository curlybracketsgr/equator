<?php
    $path_temp = explode('/',__FILE__);
    unset($path_temp[count($path_temp) - 1]);
    unset($path_temp[count($path_temp) - 1]);
    $path = implode('/',$path_temp).'/';
    require($path.'init.php');
        
    $con = mysqli_connect($init["dbhost"],$init["dbuser"],$init["dbpass"],$init["dbname"]);
    if ($con->connect_error) {
        die("Connection failed: " . $con->connect_error);
    }
    $con -> set_charset("utf8");
    
    
    echo '
        <table>
            <tr>
                <td style="background:#000; color:#fff; text-align:center;">A/A</td>
                <td style="background:#000; color:#fff; text-align:center;" width="400">Προϊόν</td>
                <td style="background:#000; color:#fff; text-align:center;" width="100">Μεταβλητά</td>
                <td style="background:#000; color:#fff; text-align:center;" width="50">Βάρος</td>
                <td style="background:#000; color:#fff; text-align:center;" width="200">Tags</td>
                <td style="background:#000; color:#fff; text-align:center;" width="200">Τιμές</td>
            </tr>
    ';
    
    $sql = "SELECT * FROM at_posts AS a INNER JOIN at_postmeta AS b ON a.ID=b.post_id WHERE post_type = 'product' AND meta_key='_price'";
    $ex_result = $con->query($sql);
    
    if ($ex_result->num_rows > 0) {
	    while($row = $ex_result->fetch_assoc()) {
	        $id = $row["ID"];
	        $sql1 = "SELECT * FROM at_posts AS a INNER JOIN at_postmeta AS b ON a.ID=b.post_id WHERE a.post_parent = '".$id."' AND a.post_type = 'product_variation' AND meta_key='_price'";
	        $ex_result1 = $con->query($sql1);
            
            echo '
                <tr>
                    <td style="border:solid thin #000;" align="center" valign="middle">'.$id.'</td>
                    <td style="border:solid thin #000;" align="center" valign="middle">'.$row["post_title"].'</td>
            ';
                    if($ex_result1->num_rows == 0){
                        echo '<td style="border:solid thin #000;" align="center" valign="middle">Απλό προϊόν</td>';    
                    }else{
                        echo '<td style="border:solid thin #000;" align="center" valign="middle">'.$ex_result1->num_rows.'</td>';
                    }
            echo '
                    <td style="border:solid thin #000;" align="center" valign="middle">'.get_post_meta($id,'_weight')[0].'kg</td>
            ';
            
                    $tags = get_the_terms($id,'product_tag');
                    if(!empty($tags)){
                        echo '
                            <td style="border:solid thin #000;" align="center" valign="middle">
                        ';
                                foreach($tags as $tag){
                                    echo $tag->name.',';
                                }
                        echo '
                            </td>
                        ';            
                    }else{
                        echo '<td style="border:solid thin #000;" align="center" valign="middle"></td>';
                    }
                    echo '
                            <td style="border:solid thin #000;" align="center" valign="middle">'.$row["meta_value"].'</td>
                    ';
            
            echo '        
                </tr>
            ';
            ///// Πρεπει να βάλουμε και για τα tabs στο content! 
            if ($ex_result1->num_rows > 0) {
	            while($row1 = $ex_result1->fetch_assoc()) {
	                echo '
	                    <tr style="background:#f9f9f9;">
	                        <td></td>
	                        <td style="border:solid thin #000;" align="center" valign="middle">'.$row1["post_title"].'</td>
	                        <td style="border:solid thin #000;" align="center" valign="middle"></td>
	                        <td style="border:solid thin #000;" align="center" valign="middle">'.get_post_meta($id,'_weight')[0].'kg</td>
	                        <td style="border:solid thin #000;" align="center" valign="middle"></td>
	                        <td style="border:solid thin #000;" align="center" valign="middle">'.$row1["meta_value"].'</td>
	                    </tr>
	                ';       
	            }
            }
            
	    }
    }
    
    echo '
        </table>
    ';
?>
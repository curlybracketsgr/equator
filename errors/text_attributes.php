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
    
    $sql = "SELECT * FROM smartenize WHERE entity LIKE 'Attribute' AND data LIKE '%text%'";
    $ex_result = $con->query($sql);
    
     
    if ($ex_result->num_rows > 0) {
	    while($row = $ex_result->fetch_assoc()) {
	        $data = json_decode($row['data']);
	        
	        $sql1 = "SELECT * FROM smartenize WHERE entity LIKE 'Product' AND data LIKE '%".$data->_attribute_item->attr_Oid."%'";
            $ex_result1 = $con->query($sql1);
	        
	        
	        if ($ex_result1->num_rows > 0) {
	            while($row1 = $ex_result1->fetch_assoc()) {
	                $data1 = json_decode($row1['data']);       
	                
	                foreach($data1->post_attributes as $att){
	                    if( ($att->attr_Oid == "b5df95c0-4847-439a-8a6e-5348405bccc0")
	                    || ($att->attr_Oid == "c4154ae4-a985-4e08-8946-ab80c276002a")
	                    || ($att->attr_Oid == "cb847781-1ebb-4bad-a968-1b9684070ae4")
	                    || ($att->attr_Oid == "1cd9c754-a5d9-478d-a4a9-08c2588f9610")
	                    || ($att->attr_Oid == "49e3a608-137a-4181-b70d-413c586c2262")
	                    || ($att->attr_Oid == "ef9f4722-6d2e-4cf5-aeba-48a7af03eaa0")
	                    || ($att->attr_Oid == "6c436609-4f3b-4508-81cb-81654565eda0")
	                    || ($att->attr_Oid == "e4c1b212-ac84-4bbb-8e6e-c47978046a0b")
	                    || ($att->attr_Oid == "4765233b-872b-4f2d-aa84-cfbc8bcba68b")
	                    || ($att->attr_Oid == "2673e90e-8eac-4e6f-aaaf-18769f78d0fb")
	                    || ($att->attr_Oid == "1097644e-b622-4343-b3bb-104e53b7707b")
	                    || ($att->attr_Oid == "c0f05b8c-8ee1-4580-8ba7-3c8f17dffb91")
	                    || ($att->attr_Oid == "b4eb74dc-fac8-43e8-9e2c-b3623accd7d7")
	                    || ($att->attr_Oid == "8f5ff2fc-aa48-4ef6-8b1f-ddb314ba3901")
	                    || ($att->attr_Oid == "f33f2ffd-736b-4ebb-b59a-2a7c9aa12c1d")
	                    ){
	                        //echo $row["id"].' => '.$row["oid"].' => '.$att->option_Value.'</br>';
	                    
	                    }else{
    	                    if($att->attr_Oid == $data->_attribute_item->attr_Oid){
    	                        if($att->option_Value != ""){
    	                            echo $data->_attribute_item->attr_Oid.' => '.$data->_attribute_item->attr_name.'</br>';
    	                            echo $data1->post_title.' => '.$att->option_Value.'</br></br>';
    	                        }
    	                        
    	                    }
	                    }
	                    
	                }
	                
	            }
	        }
	        
	    }
    }
?>
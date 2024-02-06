<?php
    $path_temp = explode('/',__FILE__);
    unset($path_temp[count($path_temp) - 1]);
    $path = implode('/',$path_temp).'/';
    require($path.'/init.php');
    
    
    
    if(isset($_GET["oid"])){
        $oid = $_GET["oid"];
        $entity = $_GET["entity"];
        $data = $_GET["data"];
        $entry_date = $_GET["entry_date"];
        
        $con = mysqli_connect($init["dbhost"],$init["dbuser"],$init["dbpass"],$init["dbname"]);
        if ($con->connect_error) {
          die("Connection failed: " . $con->connect_error);
        }
        
        $sql = "INSERT INTO smartenize (oid,entity,data,entry_date,status) VALUES ('$oid','$entity','$data','$entry_date','1')";
        
        if ($con->query($sql) === TRUE) {
          echo "New record created successfully";
        }else{
          echo "Error: " . $sql . "<br>" . $con->error;
        }
        
        $con->close();
        
    }
    
?>
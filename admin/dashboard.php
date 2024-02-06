<?php



add_action('admin_menu', 'test_plugin_setup_menu');
 
function test_plugin_setup_menu(){
    add_menu_page( 'Smartenize Page', 'Smartenize', 'manage_options', 'smartenize', 'smartenize_init' , '', 3);
}
 
function smartenize_init(){
    ?>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <style>
            .smartenize_cont{width:90%; display:flex; justify-content:center; align-items:flex-start; flex-direction:column;}
            .smartenize_info_row{width:100%; display:flex; justify-content:flex-start; align-items:center; flex-wrap:wrap; margin-bottom:44px;}
            .smartenize_info_box{width:calc(100% / 6); display:flex; justify-content:center; align-items:center; flex-direction:column; min-width:180px;}
            .smartenize_info_box .smartenize_info_number{height:85px; font-size:72px; display:flex; justify-content:center; align-items:center; border-bottom:solid thin #c2c2c2;}
            .smartenize_info_box .smartenize_info_title{height:44px; font-size:22px;  display:flex; justify-content:center; align-items:center;}
            .row_list{display:flex; justify-content:flex-start; align-items:flex-start; flex-direction:column;}
        </style>
    <?php
    $path_temp = explode('/',__FILE__);
    unset($path_temp[count($path_temp) - 1]);
    unset($path_temp[count($path_temp) - 1]);
    $path = implode('/',$path_temp).'/';
    require($path.'init.php');
    
    $con = mysqli_connect($init["dbhost"],$init["dbuser"],$init["dbpass"],$init["dbname"]);
    $con->set_charset('utf8');
    ?>
        <h1>Smartenize Dashboard</h1>
        <div class="smartenize_cont">
            
            
    <?php
        $date = date('Y-m-d');
        $products = "SELECT * FROM ".$init["dbpre"]."posts WHERE post_type = 'product' AND post_status = 'publish'";
        $variations = "SELECT * FROM ".$init["dbpre"]."posts WHERE post_type = 'product_variation' AND post_status = 'publish'";
        $product_categories = "SELECT * FROM ".$init["dbpre"]."term_taxonomy WHERE taxonomy = 'product_cat'";
        $product_tags = "SELECT * FROM ".$init["dbpre"]."term_taxonomy WHERE taxonomy = 'product_tag'";
        $attributes = "SELECT * FROM ".$init["dbpre"]."woocommerce_attribute_taxonomies";
        $records_today = "SELECT * FROM smartenize WHERE entry_date LIKE '$date%'";
        
        
        echo '
            <div class="smartenize_info_row">
                <div class="smartenize_info_box">
                    <div class="smartenize_info_number">'.$con->query($records_today)->num_rows.'</div>
                    <div class="smartenize_info_title">Records today</div>
                </div>
            </div>
            
            <div class="smartenize_info_row">
                <div class="smartenize_info_box">
                    <div class="smartenize_info_number">'.$con->query($products)->num_rows.'</div>
                    <div class="smartenize_info_title">Products</div>
                </div>
                
                <div class="smartenize_info_box">
                    <div class="smartenize_info_number">'.$con->query($variations)->num_rows.'</div>
                    <div class="smartenize_info_title">Variations</div>
                </div>
                
                <div class="smartenize_info_box">
                    <div class="smartenize_info_number">'.$con->query($product_categories)->num_rows.'</div>
                    <div class="smartenize_info_title">Product categories</div>
                </div>
                
                <div class="smartenize_info_box">
                    <div class="smartenize_info_number">'.$con->query($product_tags)->num_rows.'</div>
                    <div class="smartenize_info_title">Product tags</div>
                </div>
                
                <div class="smartenize_info_box">
                    <div class="smartenize_info_number">'.$con->query($attributes)->num_rows.'</div>
                    <div class="smartenize_info_title">Attributes</div>
                </div>
            </div>
            
            
        ';
    ?>
    
        </div>
    <?php
        /*    
    
        $sql = "SELECT * FROM smartenize";
        $ex_result = $con->query($sql);
        
        $records = [];
        $records_counter = 0;
        while($row = $ex_result->fetch_assoc()) {
            $entry_date_exp = explode('T',$row["entry_date"]);
            $entry_date = $entry_date_exp[0];
            
            $records[$row["id"]] = array("type" => $row["entity"], "date" => $entry_date);
        }
        
        
        $dates = [];
        $types = [];
        foreach($records as $key => $value){
            $dates[$key] = $value["date"];    
            $types[$key] = $value["type"];    
        }
        $type_per_date = [];
        foreach($records as $key => $value){
            $type_per_date[$value["date"]][$value["type"]][] = '';    
        }
        $udates = array_unique($dates);
        $utypes = array_unique($types);
        
    ?>
            <script>
                google.charts.load('current', {'packages':['bar']});
                google.charts.setOnLoadCallback(drawChart);
            
                function drawChart() {
                    var data = google.visualization.arrayToDataTable([
                    <?php
                        echo '
                            ["Date","Product","Variation"],
                        ';
                        
                        foreach($udates as $udate){
                            echo '[';
                            echo "'".$udate."'";
                            
                            foreach($type_per_date as $k1 => $v1){
                                if($k1 == $udate){
                                    
                                    echo ",'".count($v1["Product"])."','".count($v1["Variation"])."'";
                                     
                                }
                            }
                            
                            echo '],';
                        }
                    ?>
                        
                        
                    ]);
                    
                    var options = {
                        chart: {
                            title: 'Company Performance',
                            subtitle: 'Sales, Expenses, and Profit: 2014-2017',
                        }
                    };
                    
                    var chart = new google.charts.Bar(document.getElementById('columnchart_material'));
                    
                    chart.draw(data, google.charts.Bar.convertOptions(options));
                }
            </script>
    
    
            <div id="columnchart_material" style="width: 100%; height: 500px;"></div>
        </div>
    <?php
    */
}

?>
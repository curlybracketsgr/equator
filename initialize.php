<?php
/**
 * Plugin Name: Smartenize
 * Plugin URI: https://www.smartenize.gr
 * Description: A magic world to Smart Lob Application
 * Version: 1.0
 * Author: Adrenalize
 * Author URI: https://www.adrenalize.gr
 */
 


$path_temp = explode('/',__FILE__);
unset($path_temp[count($path_temp) - 1]);
$path = implode('/',$path_temp).'/';
require($path.'/init.php');

check_database($init);

function check_database($init){
    
    $con = mysqli_connect($init["dbhost"],$init["dbuser"],$init["dbpass"],$init["dbname"]);
    if ($con->connect_error) {
      die("Connection failed: " . $con->connect_error);
    }
    
    $exist = $con->query("SHOW TABLES LIKE 'smartenize'");
    if($exist->num_rows == 0){
        create_database($init);
    }else{
		
    }
    
    $exist1 = $con->query("SHOW TABLES LIKE 'smartenize_log'");
    if($exist1->num_rows == 0){
        create_log_database($init);
    }else{
		
    }
    $con->close();
}
 
function create_database($init){
    $con = mysqli_connect($init["dbhost"],$init["dbuser"],$init["dbpass"],$init["dbname"]);
    if ($con->connect_error) {
      die("Connection failed: " . $con->connect_error);
    }
    
    $sql = "CREATE TABLE smartenize ( id INT NOT NULL AUTO_INCREMENT , oid TEXT NOT NULL , entity TEXT NOT NULL , data LONGTEXT NOT NULL , entry_date TEXT NOT NULL , status VARCHAR(1) NOT NULL , ref_id TEXT NOT NULL, PRIMARY KEY (id)) ENGINE = MyISAM CHARSET=utf8 COLLATE utf8_unicode_ci;";
    
    if ($con->query($sql) === TRUE) {
      echo "Table smartenize created successfully";
    } else {
      echo "Error creating table: " . $con->error;
    }
    
    $con->close();
}

function create_log_database($init){
    $con = mysqli_connect($init["dbhost"],$init["dbuser"],$init["dbpass"],$init["dbname"]);
    if ($con->connect_error) {
      die("Connection failed: " . $con->connect_error);
    }
    
    $sql1 = "CREATE TABLE smartenize_log ( id INT NOT NULL AUTO_INCREMENT , sid TEXT NOT NULL , message LONGTEXT NOT NULL , status TEXT NOT NULL, PRIMARY KEY (id)) ENGINE = MyISAM CHARSET=utf8 COLLATE utf8_unicode_ci;";
    
    if ($con->query($sql1) === TRUE) {
      echo "Table smartenize created successfully";
    } else {
      echo "Error creating table: " . $con->error;
    }
    
    $con->close();
}




function exelixis_tab( $original_tabs) {
	// Key should be exactly the same as in the class product_type
	$new_tab['exelixis_data'] = array(
		'label'	 => __( 'Exelixis Data', 'wcpt' ),
		'target' => 'exelixis_data_options',
		'class'  => ('show_if_exelixis_data'),
	);
	
	$insert_at_position = 150; // This can be changed
	$tabs = array_slice( $original_tabs, 0, $insert_at_position, true ); // First part of original tabs
	$tabs = array_merge( $tabs, $new_tab ); // Add new
	$tabs = array_merge( $tabs, array_slice( $original_tabs, $insert_at_position, null, true ) ); // Glue the second part of original
	
	return $tabs;
}

function wcpp_custom_style() {
?>
	<style>
		#woocommerce-product-data ul.wc-tabs li.exelixis_data_options a:before { font-family: WooCommerce; content: '\e002'; }
	</style>
<?php
}


function exelixis_data_content(){
?>
	<div id="exelixis_data_options" class="panel woocommerce_options_panel">
		<?php
			woocommerce_wp_text_input( array(
				'id'				=> '_oid',
				'label'				=> __( 'Oid', 'woocommerce' ),
				'desc_tip'			=> 'true',
				'description'		=> __( 'The unique Oid of the product', 'woocommerce' ),
				'type' 				=> 'string',
				
			) );
			
			
						
		?>
	</div>
<?php
	
}

//create_dashboard($path,$init);
function create_dashboard($path,$init){
    require_once($path.'admin/dashboard.php');
}


 
function exelixis_variation_oid( $loop, $variation_data, $variation ) {
   woocommerce_wp_text_input( array(
        'id' => 'exelixis_oid',
        'wrapper_class' => 'form-row',
        'label' => __( 'Exelixis Oid', 'woocommerce' ),
        'value' => get_post_meta( $variation->ID, '_oid', true )
   ) );
}


function save_exelixis_option_fields( $post_id ) {
	
	//update_post_meta( $post_id, '_oid', $_POST['_oid'] );
	
}
add_action( 'woocommerce_process_product_meta_simple', 'save_exelixis_option_fields'  );
add_action( 'woocommerce_process_product_meta_variable', 'save_exelixis_option_fields'  );

add_action( 'admin_head', 'wcpp_custom_style' );
add_filter( 'woocommerce_product_data_tabs', 'exelixis_tab' );
add_action( 'woocommerce_product_data_panels', 'exelixis_data_content' );
add_action( 'woocommerce_variation_options_pricing', 'exelixis_variation_oid', 50, 10 );

add_action( 'woocommerce_thankyou', 'send_order_to_exelixis', 20, 1 );
function send_order_to_exelixis($order_id){

    // ORDER XML
	
	$order = wc_get_order( $order_id );
	$user_id = get_current_user_id();
	$user_erp_id = get_user_meta($user_id, 'oid')[0];

	$order_data = $order->get_data();
	
	//["additional_comments"]
	
	$billing_first_name = $order_data['billing']['first_name'];
	$billing_last_name = $order_data['billing']['last_name'];
	$billing_company = $order_data['billing']['company'];
	$billing_doy = get_user_meta($user_id, 'billing_doy')[0];
	$billing_afm = get_post_meta( $order_id,'billing_afm')[0];
	
	$billing_doy = get_post_meta( $order_id,'billing_doy')[0];
	$billing_email = $order_data['billing']['email'];
	$billing_address_1 = $order_data['billing']['address_1'];
	$billing_address_2 = $order_data['billing']['address_2'];
	$billing_city = $order_data['billing']['city'];
	$billing_country = $order_data['billing']['country'];
	$billing_phone = $order_data['billing']['phone'];
	$billing_mobile = $order_data['billing']['mobile'];
	$billing_postcode = $order_data['billing']['postcode'];
	$billing_state = $order_data['billing']['state'];
	$billing_state_name = WC()->countries->get_states( $billing_country )[$order->get_billing_state()];
	$shipping_state_name = WC()->countries->get_states( $shipping_country )[$order->get_shipping_state()];
	$shipping_first_name = $order_data['shipping']['first_name'];
	$shipping_last_name = $order_data['shipping']['last_name'];
	$shipping_address_1 = $order_data['shipping']['address_1'];
	$shipping_address_2 = $order_data['shipping']['address_2'];
	$shipping_city = $order_data['shipping']['city'];
	$shipping_company = $order_data['shipping']['company'];
	$shipping_country = $order_data['shipping']['country'];
	$shipping_postcode = $order_data['shipping']['postcode'];
	$shipping_state = $order_data['shipping']['state'];

	$order_date1 = $order->order_date;
	$order_date = $order->order_date;
	$order_status = $order->status;
	$order_payment_method = $order->get_payment_method_title();
	$order_shipping_method = $order->get_shipping_method();

	$order_shipping_total = (float)($order->shipping_total + $order->shipping_tax);
	$order_discount_total = (float)$order->discount_total;
	$order_products_total = (float)'0';
	$order_price_with_tax = (float)'0';
	$order_price_tax = (float)'0';
	$order_price_no_tax = (float)'0';
	$order_antikatavoli = (float)'0';
	$order_note = $order_data["customer_note"];
	
	$order_courier_note = get_post_meta($order_id,'additional_comments')[0];//$order_data["additional_comments"];
	
	$items = $order->get_items();
	$fee_name = '';
	$fee_total = '';
	$fee_total_tax = '';
	foreach( $order->get_items('fee') as $item_id => $item_fee ){
    	// The fee name
    	$fee_name = $item_fee->get_name();
    
    	// The fee total amount
    	$fee_total = $item_fee->get_total();
    
    	// The fee total tax amount
    	$fee_total_tax = $item_fee->get_total_tax();
	}

    
    
	$xml_string = '<invoice>';
	    $xml_string .= '<order>';
	        $xml_string .= "<order_id>".$order_id."</order_id>";
	        $xml_string .= "<order_date>".$order_date1."</order_date>";
	        $xml_string .= "<order_customer_id>WP-".$user_id."</order_customer_id>";
	        $xml_string .= "<order_customer_Oid>".$user_erp_id."</order_customer_Oid>";
	        
        	if($billing_afm == '-'){
        	    $xml_string .= "<order_invoice>1</order_invoice>"; // 1 apodeiksi 2 timologio
        	}else{
        	    $xml_string .= "<order_invoice>2</order_invoice>"; // 1 apodeiksi 2 timologio
        	    $xml_string .= "<order_invoice_company>".$billing_company."</order_invoice_company>";
        	    $xml_string .= "<order_invoice_afm>".$billing_afm."</order_invoice_afm>";
        	    $xml_string .= "<order_invoice_doy>".$billing_doy."</order_invoice_doy>";
        	    
        	}
        	$xml_string .= "<order_status>".$order_status."</order_status>";
	        $xml_string .= "<order_total>".$order->get_total()."</order_total>";
	
        	if($fee_name == "Αντικαταβολή"){
        		$xml_string .= "<cod_fee>".$fee_total."</cod_fee>";
        	}else{
        		//$xml_string .= "<antikatavolixrewsi>0</antikatavolixrewsi>";
        	}
            $xml_string .= "<order_discount>".$order->discount_total."</order_discount>";
            $xml_string .= "<order_shipping_cost>".$order_shipping_total."</order_shipping_cost>";
        	$xml_string .= "<order_payment_title>".$order->get_payment_method_title()."</order_payment_title>";
        	$xml_string .= "<order_shipping_title>".$order->get_shipping_method()."</order_shipping_title>";
        	$xml_string .= "<order_info>".$order_note."</order_info>";
        	$xml_string .= "<order_courier_info>".$order_courier_note."</order_courier_info>";
        $xml_string .= '</order>';
        
        $xml_string .= '<coupon>'; 
            foreach( $order->get_coupon_codes() as $coupon_code ) {
                // Get the WC_Coupon object
                $coupon = new WC_Coupon($coupon_code);
                $xml_string .= "<coupon_name>".$coupon_code."</coupon_name>";
                $xml_string .= "<coupon_amount>".$coupon->get_amount()."</coupon_amount>";
                $xml_string .= "<coupon_type>".$coupon->get_discount_type()."</coupon_type>";
            }
	        
	    $xml_string .= '</coupon>';    
    
        $xml_string .= '<billing>';
            $xml_string .= "<billing_first_name>".$billing_first_name."</billing_first_name>";
            $xml_string .= "<billing_last_name>".$billing_last_name."</billing_last_name>";
        	$xml_string .= "<billing_address>".$billing_address_1."</billing_address>";
        	$xml_string .= "<billing_address_number>".$billing_address_2."</billing_address_number>";
        	$xml_string .= "<billing_state>".$billing_state_name."</billing_state>";
        	$xml_string .= "<billing_post_code>".$billing_postcode."</billing_post_code>";
        	$xml_string .= "<billing_city>".$billing_city."</billing_city>";
        	$xml_string .= "<billing_country>".$billing_country."</billing_country>";
        	$xml_string .= "<billing_tel>".$billing_phone."</billing_tel>";
        	$xml_string .= "<billing_mobile>".$billing_mobile."</billing_mobile>";
        	$xml_string .= "<billing_email>".$billing_email."</billing_email>";
        	if( $order->get_billing_address_1() != $order->get_shipping_address_1() ) {
		        $xml_string .= "<sameAddressFlag>0</sameAddressFlag>"; // 1=Παράδοση στην ίδια διεύθυνση 0=Παράδοση σε διαφορετική διεύθυνση
	        }else{
		        $xml_string .= "<sameAddressFlag>1</sameAddressFlag>"; // 1=Παράδοση στην ίδια διεύθυνση 0=Παράδοση σε διαφορετική διεύθυνση
	        }
    	$xml_string .= '</billing>';
    	
    	$xml_string .= '<shipping>';
        	$xml_string .= "<shipping_first_name>".$shipping_first_name."</shipping_first_name>";
        	$xml_string .= "<shipping_last_name>".$shipping_last_name."</shipping_last_name>";
        	$xml_string .= "<shipping_address>".$shipping_address_1."</shipping_address>";
        	$xml_string .= "<shipping_address_number>".$shipping_address_2."</shipping_address_number>";
        	$xml_string .= "<shipping_state>".$shipping_state_name."</shipping_state>";
        	$xml_string .= "<shipping_post_code>".$shipping_postcode."</shipping_post_code>";
        	$xml_string .= "<shipping_city>".$shipping_city."</shipping_city>";
        	$xml_string .= "<shipping_country>".$shipping_country."</shipping_country>";
    	$xml_string .= '</shipping>';
        
        $xml_string .= "<items>";
    	foreach ( $items as $item_id => $item_data ) {
    		$product_id = $item_data->get_product_id();
    		$variation_id = $item_data->get_variation_id();
    		$product_oid = get_post_meta($product_id,'_oid')[0];
    		$variation_oid = get_post_meta($variation_id,'_oid')[0];
    		$quantity = $item_data->get_quantity();
    		$price = $item_data->get_subtotal();
    		
    		$xml_string .= "<item>";
    		    $xml_string .= "<item_Oid>".$product_oid."</item_Oid>";
    		    $xml_string .= "<item_variation_Oid>".$variation_oid."</item_variation_Oid>";
    			$xml_string .= "<item_qnt>".$quantity."</item_qnt>";
    		    $xml_string .= "<item_price>".($price / $quantity)."</item_price>";
    		    $xml_string .= "<item_full_price>".$price."</item_full_price>";
    		$xml_string .= "</item>";
    	}
        $xml_string .= "</items>";

	$xml_string .= "</invoice>";


	$text = preg_replace("/[\r\n\t\f\v]+/", "", $xml_string);
	$text = preg_replace("/(\d)\.(\d)/", "\\1.\\2", $text);
	$text = preg_replace("/(\d)\,(\d)/", "", $text);
	$text = nl2br($text);

	$data = [    
		'type' => 'order',
		'xmldata'  => $text
	];
	
    //echo $data['xmldata'].'</br></br>';
	
	
	$username = 'agathonikos';
	$password = '@gath0nikos';
	$ip = '193.92.12.164';
	$port = '8888';
	
	
	$post_url = 'http://'.$ip.':'.$port.'/webapi/api/PutDataXml?Type=order';
	
	
	$headers = array(
		'Content-Type: application/xml; charset=utf-8',
		'Cache-Control: no-cache', 
		'Accept-Encoding: gzip,deflate',
		'Authorization: Basic '.base64_encode($username.":".$password),
		'Host:'.$ip.':'.$port,
		'Connection:Keep-Alive',
		'Expires:-1'
	);
	$ch = curl_init();


	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_URL,$post_url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$text);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$server_output = curl_exec($ch);
	//echo $server_output.'</br>';
	update_post_meta($order_id,'order_received',$server_output);
	curl_close ($ch);
	
}

?>
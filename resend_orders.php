<?php
ini_set('memory_limit', '-1');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$path_temp = explode('/',__FILE__);
unset($path_temp[count($path_temp) - 1]);
$path = implode('/',$path_temp).'/';
require($path.'/init.php');
    

$args = array(
    'post_type' => 'shop_order',
    'posts_per_page' => '6',
    'post_status' => 'any'
);
$my_query = new WP_Query($args);

if ( $my_query->have_posts() ) {
	
	while ( $my_query->have_posts() ) {
		$my_query->the_post();
		
		$order_id = get_the_ID();
		
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
    	
        echo $data['xmldata'].'</br></br>';
    	
    	
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
    	//print_r($headers);
    	$ch = curl_init();
        
    
    	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    	curl_setopt($ch, CURLOPT_URL,$post_url);
    	curl_setopt($ch, CURLOPT_POST, 1);
    	curl_setopt($ch, CURLOPT_POSTFIELDS,$text);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	
        if(curl_exec($ch) === false){
            echo 'Curl error: ' . curl_error($ch);
        }else{
        	$server_output = curl_exec($ch);
        	echo $server_output.'</br>';
        	update_post_meta($order_id,'order_received',$server_output);
        	curl_close ($ch);
        }
        
    		
    }
	
}

?>
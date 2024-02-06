<?php
    header('content-type:text/html');
    
    $useragent = "Opera/9.80 (J2ME/MIDP; Opera Mini/4.2.14912/870; U; id) Presto/2.4.15";
    $ch = curl_init ("");
    curl_setopt ($ch, CURLOPT_URL, "https://www.google.com/search?q=site%3Aarkoudis-toolbox.gr&rlz=1C1GCEU_elGR821GR821&oq=site%3A&aqs=chrome.2.69i57j69i59l3j69i58.3807j1j7&sourceid=chrome&ie=UTF-8");
    curl_setopt ($ch, CURLOPT_USERAGENT, $useragent); // set user agent
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    $output = curl_exec ($ch);
    
    //print_r($output);
    preg_match_all("'<div class=\"kCrYT\">(.*?)</div>'si", $output, $match);
    print_r($match);
    //if($match) echo "result=".$match[1];
    
    /*
    $preg_match_arr = array(
		"url"=>"/<div\s[^>]*class=\"kCrYT\"\s[^>]* ><\/div>/",
		
	);
    foreach($preg_match_arr as $key => $value){
        if (preg_match_all($value, $output, $matches)) {
    		$product_info[$key] = $matches[1];			
    	}
    }
    print_r($product_info);
    */
    //"title"=>"/<meta\s[^>]*name=\"twitter:title\" content=\"([^>]*)\">/",
    curl_close($ch);
    
    

?>
<?php
    $abspath_temp = explode('/',__FILE__);
    $abspath = $abspath_temp[0].'/'.$abspath_temp[1].'/'.$abspath_temp[2].'/'.$abspath_temp[3].'/';
    
	define('ABSPATH', $abspath);
	
	include(ABSPATH.'/wp-config.php');
	require_once(ABSPATH.'/wp-settings.php');
	require_once(ABSPATH.'/wp-admin/includes/user.php' );
    
    switch_to_blog(2);
    global $wpdb, $woocommerce;
    $init = [];
    $init['pluginpath'] = plugin_dir_path(__FILE__);
    $init['abspath'] = ABSPATH;
    $init['dbname'] = $wpdb->dbname;
    $init['dbuser'] = $wpdb->dbuser;
    $init['dbpass'] = $wpdb->dbpassword;
    $init['dbhost'] = $wpdb->dbhost;
    $init['dbpre'] = $wpdb->prefix;  
    $init["lang"] = '_en';
    
?>
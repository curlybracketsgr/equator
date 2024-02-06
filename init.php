<?php
    $abspath_temp = explode('/',__FILE__);
    $abspath = $abspath_temp[0].'/'.$abspath_temp[1].'/'.$abspath_temp[2].'/'.$abspath_temp[3].'/';
    
    
	
	define('ABSPATH1', $abspath);
	
	
	include(ABSPATH1.'/wp-config.php');
	require_once(ABSPATH1.'/wp-settings.php');
	require_once(ABSPATH1.'/wp-admin/includes/user.php' );
    
    
    global $wpdb, $woocommerce;
    
    $init = [];
    $init['pluginpath'] = plugin_dir_path(__FILE__);
    $init['abspath'] = ABSPATH1;
    $init['dbname'] = $wpdb->dbname;
    $init['dbuser'] = $wpdb->dbuser;
    $init['dbpass'] = $wpdb->dbpassword;
    $init['dbhost'] = $wpdb->dbhost;
    $init['dbpre'] = $wpdb->prefix;  
    $init["lang"] = '';
    
?>
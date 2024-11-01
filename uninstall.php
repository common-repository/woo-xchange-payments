<?php

/*File to unistall Plugin from Wordpress, removes tables created and fields on the WP options table.*/


//https://developer.wordpress.org/plugins/wordpress-org/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;  // Exit if not Uninstallation is called
}


//Erase the only table that Xchange creates
global $wpdb;
delete_option( 'woocommerce_xchange_settings' );
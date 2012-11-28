<?php
/*
 * WPEC-DD Admin functions, etc.
 */

// admin includes
require_once( WPEC_DD_FILE_PATH . '/wpec-gd-admin/admin-functions.php' );
require_once( WPEC_DD_FILE_PATH . '/wpec-gd-admin/settings.php' );


function wpec_dd_purchase_metaboxes() {

	$pagename = 'wpec-dd-purchase';
	add_meta_box( 'wpec_dd_customer','Customer Details','wpec_dd_customer',$pagename );
	add_meta_box( 'wpec_dd_business','Business Details','wpec_dd_business',$pagename );
	add_meta_box( 'wpec_dd_purchase_details','Purchase Details','wpec_dd_purchase_details',$pagename );
}
function wpec_dd_vendors() {

        $pagename = 'wpec-dd-vendor';
	add_meta_box( 'wpec_dd_vendor_details','Business Details','wpec_dd_vendor_details',$pagename );
	add_meta_box( 'wpec_dd_vendor_deals','Daily Deals','wpec_dd_vendor_deals',$pagename );
	add_meta_box( 'wpec_dd_vendor_financials','Financial Details','wpec_dd_vendor_financials',$pagename );
}
function wpec_dd_metaboxes() {

        $pagename = 'toplevel_page_wpsc-edit-products';
	add_meta_box( 'wpec_dd_details','Daily Deal Details','wpec_dd_details',$pagename );
}

add_action( 'admin_menu', 'wpec_dd_metaboxes' );


?>

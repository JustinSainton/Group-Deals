<?php

/*
 * General bootstrap for included files
 */

// Admin
if ( is_admin() )
    include_once( WPEC_DD_FILE_PATH . '/wpec-gd-admin/admin.php' );

//User Profile (Front-end and Back-end)    
include_once( WPEC_DD_FILE_PATH . '/wpec-gd-admin/class.user_profile.php' );

//AJAX
include_once( WPEC_DD_FILE_PATH . '/wpec-gd-includes/wpec-gd-ajax.php' );

//Checkout
include_once( WPEC_DD_FILE_PATH . '/wpec-gd-includes/wpec-gd-checkout.php' );

//Widgets
include_once( WPEC_DD_FILE_PATH . '/wpec-gd-widgets/buy-now-price-box.php' );
include_once( WPEC_DD_FILE_PATH . '/wpec-gd-widgets/progress-bar.php' );
include_once( WPEC_DD_FILE_PATH . '/wpec-gd-widgets/time-left-to-buy-box.php' );
include_once( WPEC_DD_FILE_PATH . '/wpec-gd-widgets/ajax-reg-form.php' );
include_once( WPEC_DD_FILE_PATH . '/wpec-gd-widgets/side-deals.php' );

//Social
include_once( WPEC_DD_FILE_PATH . '/wpec-gd-social/facebook-like.php' );
include_once( WPEC_DD_FILE_PATH . '/wpec-gd-social/facebook-connect.php' );

//Custom Payment Gateway ( Currently only gateway available. )
include_once( WPEC_DD_FILE_PATH . '/wpec-gd-includes/wpec-dd-payment.php' );

//Location
include_once( WPEC_DD_FILE_PATH . '/wpec-gd-location/functions.php' );

//Mobile
include_once( WPEC_DD_FILE_PATH . '/wpec-gd-mobile/functions.php' );

?>
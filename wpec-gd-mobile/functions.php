<?php
/*
 *  Let's serve up some mobile themes!
 */
$options = get_option( 'wpec_gd_options_array' );

$isMobile = false;

$op = isset( $_SERVER['HTTP_X_OPERAMINI_PHONE'] ) ? strtolower( $_SERVER['HTTP_X_OPERAMINI_PHONE'] ) : '';
$ua = strtolower( $_SERVER['HTTP_USER_AGENT'] );
$ac = strtolower( $_SERVER['HTTP_ACCEPT'] );

$isMobile = $op != ''
	|| strpos( $ac, 'application/vnd.wap.xhtml+xml' ) !== false
	|| strpos( $ua, 'iPhone' ) !== false 	
	|| strpos( $ua, 'iPad' ) !== false 	
	|| strpos( $ua, 'iPod' ) !== false 	
	|| strpos( $ua, 'mobile' ) !== false 	
	|| strpos( $ua, 'Android' ) !== false 	
	|| strpos( $ua, 'Windows CE' ) !== false 	
	|| strpos( $ua, 'Windows Phone' ) !== false 	
	|| strpos( $ua, 'Opera Mini' ) !== false;

if( ( $isMobile || isset( $_REQUEST['mobile'] ) ) && $options['gd_mobile_theme_enable'] ){
      // add_filter( 'stylesheet', 'get_mobile_template' );
    //	add_filter( 'template', 'get_mobile_template' );
}

function get_mobile_template(){
	$options = get_option( 'wpec_gd_options_array' );

	$mobiletheme =  $options['gd_mobile_theme'];
	$themes = get_themes();

	foreach( $themes as $theme_data )
	  if( $theme_data['Name'] == $mobiletheme )
	      return $theme_data['Stylesheet'];
}
?>
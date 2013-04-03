<?php

/*
 * WPEC Daily Deal theme redirect for home page.
 * Whelp, turns out, people want control.  Refactored to allow users to choose what page the group deal shows on.
 * The logic is this - First check for defined Group Deals page from settings.  If this doesn't exist, then we fallback to the static home page set.
 *
 * @todo Allow for multiple deals.  Probably means including a single template.
 * 
 */

function is_group_deals_home() {
	
	global $wp_query, $original_page_id;
	
	$group_deals_options = get_option( 'wpec_gd_options_array' );

	if( $group_deals_options['gd_page'] )
		$group_deals_page = (int) $group_deals_options['gd_page'];
	else
		$group_deals_page = get_option( 'page_on_front' );
	
	return is_page( $group_deals_page );
}

//add_action( 'pre_get_posts', 'gd_set_up_home_page' );
add_action( 'init', 'gd_vendor_setup' );

/*
 * Sets global variable for vendors
 */
function gd_vendor_setup() {
	global $gd_vendors;
		
	 $gd_vendors = array();
	 $vendors = get_posts( 'post_type=wpec-dd-vendor' );
	 
   foreach ( (array) $vendors as $vendor )
	  $gd_vendors[] = $vendor->ID;    
	
}

/*
 * Sets up home page 
 * @todo Order by post_meta expiration date
 */
function gd_set_up_home_page( $query ) {
	global $wp_query, $gd_vendors;
	
	 if( is_admin() || ! is_group_deals_home() )
		  return $query;
	 
	   $meta = array(
			array(
				'key' => '_wpec_dd_business',
				'value' => $gd_vendors,
				'compare' => 'IN'
			)
		);
	   
		$query->set( 'meta_query', $meta );
		$query->set( 'meta_key', '_wpec_dd_business');
		$query->set( 'post_type', 'wpsc-product' );
		$query->set( 'post_status', 'publish' );
		$query->set( 'page_id', '' );
		$query->set( 'pagename', '' );
		$query->set( 'order', 'DESC' );
		
}

function wpec_group_deal_home_template() {
	global $wpec_theme_files;
	
	if( function_exists( 'wpsc_register_theme_file' ) )
		wpsc_register_theme_file( 'wpec-group-deal-home.php' );
}

add_action( 'admin_init', 'wpec_group_deal_home_template' );

function wpec_gd_api_notice() {

	$api_key = get_option( 'wpec_gd_options_array' );

	if( ! isset( $api_key['gd_api_id'] ) || empty( $api_key['gd_api_id'] ) ) {

	?>
	<div class="error fade">
		<p>
		<?php printf( __( 'Uh-oh!  Looks like you have not entered your Group Deals API key yet.  You will definitely need to do that for Group Deals to work.  Head over to the <a href="%1s">Group Deals Settings</a> page and enter it.  It was displayed to you when you purchased Group Deals and was also emailed to you.', 'wpec-group-deals' ), admin_url( 'options-general.php?page=wpec_gd_options' ) ) ?>
		</p>
	</div>
<?php
	} elseif( ! wpec_gd_authenticate() ) {
		?>
		<div class="error fade">
			<p><?php _e( 'Uh-oh!  You appear to have entered your API key incorrectly.  Make sure your site URL in your General Settings matches the domain you entered and the API key you entered matches what you received.', 'wpec-group-deals' ); ?></p>
		</div>
		<?php
	}
}

add_action( 'admin_notices', 'wpec_gd_api_notice' );

 /**
  * Bakes a cookie if the user was a referral
  *
  * Has filterable cookie expiration time, defaults to 30 days.
  * @uses setcookie
  * @returns null
  */

 function wpec_gd_check_ref() {

	$cookie_exp = apply_filters( 'wpec_gd_ref_cookieexp', time() + 60 * 60 * 24 * 30 );

	if( isset( $_REQUEST['ref'] ) )
		$un = esc_attr( ( int )$_REQUEST['ref'] );
	else
		return;

	$user_info = get_userdata( $un );

	if( isset( $un ) && $user_info->ID )
		setcookie( 'wpec_gd_ref', $un, $cookie_exp );
 }

 add_action( 'wp_loaded', 'wpec_gd_check_ref' );
 

//START DEBUG
function wpec_dd_schedules() {
	echo date('F j, Y H:i:s', strtotime( "-8 hours" ,wp_next_scheduled( 'deal_over_email' )));
	echo date('F j, Y H:i:s', strtotime( "-8 hours" ,wp_next_scheduled( 'new_group_deal_email' )));
}

//add_action( 'admin_footer', 'wpec_dd_schedules' );
//add_filter( 'cron_schedules', 'wpec_gd_every_five' );

function wpec_gd_every_five( $schedules ) {
	$schedules['every_five'] = array(
		'interval' => 300,
		'display' => __( 'Every Five Minutes', 'wpec-group-deals' )
	);
	return $schedules;
}

//END DEBUG
function is_daily_deal() {
	global $wp_query;

	return ( isset( $wp_query->queried_object->post_type ) && 'wpsc-product' == $wp_query->queried_object->post_type );

}
function wpec_dd_company_info( $mobile = '' ) {
	global $wpdb, $post;

	if( ! is_object( $post ) )
		return;

	$business = get_post( $post->post_parent );
	$business_meta = get_post_meta( $business->ID, '_wpec_dd_vendor_details', true );

	$business_name = $business->post_title;
	$business_url = esc_url( $business_meta["url"] );
	$business_address = nl2br( wp_kses_data( $business_meta["address"] ) );
	$business_phone = esc_attr( $business_meta["phone"] );
	
	switch( $mobile ) :
		case 'name':
			return $business_name;
		break;
		
		case 'address':
			return $business_address;
		break;
		
		case 'phone':
			return $business_phone;
		break;
		
		case 'url':
			return $business_url;
		break;
	endswitch;

 ?>
	<ul>
		<li>
			<span class="bold fn">
				<?php echo $business_name; ?>
			</span>
		</li>
		<li>
			<a href="<?php echo $business_url; ?>">
				<?php apply_filters( 'wpec_gd_company_website_text', __( 'Company website', 'wpec-group-deals' ) ); ?>
			</a>
		</li>
		<div class="original_location_list">
			<ul class="adr">
				<li class="street-address"><?php echo $business_address; ?></li>
			</ul>
		</div>
	<?php

		if( get_post_meta( $post->ID, '_show_google_map', true ) ) {

	?>
		<li id="wpec_google_map" style="width:225px; height:225px"></li>
<?php
	}
?>
	</ul>
<?php

}
function get_wpec_dd_price( $id, $format = 'deal' ) {
	global $post;

	if ( !is_object( $post ) )
		$post_id = $id;
	else
		$post_id = $post->ID;

	$full_price = get_post_meta( $post_id, '_wpsc_price', true );
	$deal_price = get_post_meta( $post_id, '_wpsc_special_price', true );
	$savings = $full_price - $deal_price;

	$discount = '0%';
	
	if( $full_price > 0 )
		$discount = round( ( $savings / $full_price ) * 100 ).'%';
  
	switch ( $format ) {
		case 'retail':
			$number = wpsc_currency_display( $full_price );
			break;
		case 'discount':
			$number = $discount;
			break;
		case 'savings':
			$number = wpsc_currency_display( $savings );
			break;
		case 'deal':
		default:
			$number = wpsc_currency_display( $deal_price );
	}

	return str_replace( ".00", "", $number );

}
function wpec_dd_price( $id, $format ) {

	echo get_wpec_dd_price( $id, $format );

}
function wpec_dd_theme_fine_print() {
	global $post;

	if( ! is_object( $post ) )
		return;

	$fine_print = get_post_meta( $post->ID, '_wpec_dd_fine_print', true );

	echo nl2br( $fine_print );

}
function wpec_dd_theme_highlights() {

	global $post;

	if( ! is_object( $post ) )
		return;

	$highlights = get_post_meta( $post->ID, '_wpec_dd_highlights', true );

	$highlights = explode( "\n", $highlights );

	foreach( (array) $highlights as $highlight )
		echo '<li>' . $highlight . '</li>';
	
}
function get_wpec_ajax_countdown( $id, $noscript = '' ){
	global $post;
	
	$post_id = $id;
		
	$time = get_post_meta( $post_id, '_wpec_dd_details', true );
	
	$weekend = $dayend = $hourend = $start_time = '';
		
	if( ! empty( $time["stopw"] ) )
		$weekend = '+' . (string)$time['stopw'] . ' week';

	if( ! empty( $time['stopd'] ) )
		$dayend = '+' . $time['stopd'] . ' day';

	if( ! empty( $time['stoph'] ) )
		$hourend = '+' . $time['stoph'] . ' hour';
	
	if( isset( $time["start"] ) )
		$start_time_array = $time["start"];
	
	$start_time = '';
	
	if( ! empty( $start_time_array['yy'] ) )
		$start_time .= $start_time_array['yy'];

	if( ! empty( $start_time['mm'] ) )
		$start_time .= '-' . $start_time_array['mm'];

	if( ! empty( $start_time['dd'] ) )
		$start_time .= '-' . $start_time_array['dd'];

	if( ! empty( $start_time['hh'] ) )
		$start_time .= ' ' . $start_time_array['hh'];

	if( ! empty( $start_time['mn'] ) )
		$start_time .= ':' . $start_time_array['mn'] . ':00';

	if( ! empty( $start_time['A'] ) )
		$start_time .= $start_time_array['A'];
		
	if ( 'noscript' == $noscript )
		return '<noscript>' . date( 'F j, Y H:i:s', strtotime( "$weekend $dayend $hourend", strtotime ( $start_time ) ) ) . '</noscript>';
	else
	   return date( 'F j, Y H:i:s', strtotime( "$weekend $dayend $hourend", strtotime ( $start_time ) ) );
}

function wpec_ajax_countdown( $id, $noscript = '' ){
   
	echo get_wpec_ajax_countdown( $id, $noscript );

}
function wpec_deals_purchased( $deal_id = '' ) {
	global $post, $wpdb;

	if( empty( $deal_id ) && is_object( $post ) )
		$deal_id = $post->ID;
		
	if( ! is_object( $post ) )
		return;

	$purchases = $wpdb->get_results( $wpdb->prepare( "SELECT COUNT(id) as purchases FROM $wpdb->posts WHERE post_parent = %d AND post_type = 'wpec-dd-purchase'", $deal_id ) );

	return apply_filters( 'wpec_gd_deals_purchased', $purchases[0]->purchases, $deal_id );
}
function wpec_is_quantity_limited() {
	global $post;

	if( ! is_object( $post ) )
		return;

	$limited = get_post_meta( $post->ID, '_wpsc_stock', true );
	if ( is_numeric( $limited ) )
		echo apply_filters( 'wpec_gd_limited_quantity', __( 'Limited quantity available', 'wpec-group-deals' ) );

	return false;
}

function wpec_dd_time_of_tipping_purchase( $deal_id = '' ) {
	global $post, $wpdb;

	if( ! is_object( $post ) && empty( $deal_id ) )
		return;
	
	$deal_id = empty( $deal_id ) ? $post->ID : $deal_id;

	$tipping_point = get_post_meta( $deal_id, '_wpec_dd_details', true );
	$tipping_point = $tipping_point['tipping_point'];

	if( ! isset( $tipping_point ) || empty( $tipping_point ) )
		return;

	$tipper = absint( $tipping_point - 1 );

	$results = $wpdb->get_var( $wpdb->prepare( "SELECT post_date FROM $wpdb->posts WHERE post_type = 'wpec-dd-purchase' AND post_parent = %d AND post_status = 'publish' ORDER BY ID ASC LIMIT %d, %d", $deal_id, $tipper, $tipping_point ) );

	$time = '';

	if( ! empty( $results ) )
		$time = date( apply_filters( 'wpec_gd_time_of_tipping_purchase_format', 'F jS \a\t g:iA' ), strtotime( $results ) );

	return $time;
}

function wpec_is_deal_on( $mobile = false ) {
	global $post, $wpdb;

	if( ! is_object( $post ) )
		return;

	$tipping_point = get_post_meta( $post->ID, '_wpec_dd_details', true );
	$tipping_point = $tipping_point['tipping_point'];
	$purchased = wpec_deals_purchased();
	
	$time = wpec_dd_time_of_tipping_purchase();
	
	if( $mobile )
		return $tipping_point;
	
	if( $purchased >= $tipping_point ) {
  ?>
		<div class='tipped_check_mark'>
			<span>
				<img alt="" class="ib" height="28" src="<?php echo WPEC_DD_URL.'/wpec-gd-images/'; ?>check_mark.png" title="" width="27" />
				<?php echo apply_filters( 'wpec_gd_the_deal_is_on', __( 'The deal is on!', 'wpec-group-deals' ) ); ?>
			</span>
		</div>
		<div class='tipped_at'>
			<p class='tipping'>
				<?php 
					printf( apply_filters( 'wpec_gd_tipped_at', __( 'Tipped on %1$s with <span class="number">%2$d</span> bought', 'wpec-group-deals' ) ), $time, $tipping_point );
				?>
			</p>
		</div>
	<?php
   } else {
   ?>
		<div class="tipometer">
			<div class="progress_bar">
			</div>
			<span class="min">0</span>
			<span class="max"><?php echo $tipping_point; ?></span>
		</div>
		<p class="tipping"><span class="number">
		<?php echo ( $tipping_point - $purchased ); ?></span> <?php echo apply_filters( 'wpec_gd_blank_more_need_to_get_deal', __( 'more needed to get the deal', 'wpec-group-deals' ) );  ?>
		</p>

   <?php
   }
   
}

/**
 * Emails new deals to subscribers
 *
 * @package WordPress E-Commerce Group Deals
 * @since 1.0
 * @global class $wpdb WP DB
 *
 * @uses apply_filters | Allows users to turn off cron emails.  Some may want to do this manually.
 * @param none
 * @return none
 * @todo Integrate with third-party mailing providers
 * @todo Extend expiration date
 */

function wpec_dd_email_subscriber() {
	global $wpec_gd_ajax;

	$is_auto_send = apply_filters( 'wpec_gd_email_subscribers_switch', true );

	if( ! $is_auto_send )
		return;

	if( ! is_object( $wpec_gd_ajax ) )
		$wpec_gd_ajax = new WPEC_GD_Ajax;

	return $wpec_gd_ajax->send_subscriber_emails();
	
}

/*
 * Notifies GroupDeals that offer is over.  Updates post_meta if tipped/untipped, total sales/revs, unpublishes.
 */
function wpec_gd_offer_over_api( $deal_id, $tipped = true, $total_sales = 0, $total_revenue = 0 ) {
	global $authorization;

	//Allows for override, for people not wanting to submit data to Group Deals.  There is really no overly private data shared, but we recognize some people want total control.
	$allow_post = apply_filters( 'wpec_gd_allow_deal_details_api_push', true );
	
	if( ! $allow_post )
		return;
	
	if( ! wpec_gd_authenticate() )
		return;

	$remote_id = get_post_meta( $deal_id, '_remote_id', true );
	$domain = $authorization["domain"];
	$api_id = $authorization["api_id"];

	$args = array();
	
	$args['body'] = array(
		'domain' => $domain,
		'api_id' => $api_id,
		'tipped' => (bool) $tipped,
		'total_sales' => $total_sales,
		'total_revenue' => $total_revenue
	);
	
	$args['timeout'] = 15;

	$url = "http://api.groupdealsplugin.com/push/$remote_id/";

	$post = wp_remote_post( $url, $args );

}

/*
 * Provides HTML for the QR code verification
 *
 * @uses pre and post actions for each template.  Can be used for overriding altogether (Just add die() ) or prepending and appending other function/form.
 */

function verify_template( $temp = 'verify', $customer = '', $item = '' ) {
?>
<!DOCTYPE html> 
<html> 
<head> 
	<meta charset='utf-8' />
	<title>Verify</title> 
	<link href='http://fonts.googleapis.com/css?family=Lato:300' rel='stylesheet' type='text/css'> 
	<link href='http://fonts.googleapis.com/css?family=Lobster' rel='stylesheet' type='text/css'> 
	<style type="text/css">
			h2, h2 a { font-family: 'Lobster', cursive; font-size:72px; color:white }
			<?php if( 'verify' == $temp ) : ?>body,html {background-color:#e9c300;}
			<?php elseif( 'good' == $temp ) : ?>body,html {background-color:#65bf0b;}
			<?php else : ?>body,html {background-color:#be0404;}
			<?php endif; ?>
			p {font-size:35px; color:white; font-family: 'Lato', sans-serif; text-rendering: optimizeLegibility; }
			div {width:50%; height:50%; margin:0 auto;}
			<?php do_action( 'qr_code_template_styles', $temp, $customer, $item ); ?>
	</style> 

	</head> 
	<body> 
	<div> 
		<?php 
					if( $temp == 'verify' ) : 
					do_action( 'pre_yellow_page', $customer, $item );
				?>

			<h2>Redeem Deal?</h2> 
			<p><em><?php echo $customer ?></em> purchased <strong><?php echo $item ?></strong></p> 
			<h2><a href="<?php echo add_query_arg( 'redeemed', time() ); ?>">Tap Here To Redeem</a></h2>


		<?php
					do_action( 'post_yellow_page', $customer, $item );
					elseif( $temp == 'good' ) :
					do_action( 'pre_green_page', $customer, $item );
				?>
			<h2>Redeemed!</h2> 
			<p>You have successfully redeemed this group deal!</p> 
		<?php
					do_action( 'post_green_page', $customer, $item );
					else :
					do_action( 'pre_red_page', $customer, $item );
				?>
			<h2>Uh-Oh!</h2> 
			<p>Our records show that <?php echo $customer ?> has <em>already</em> redeemed this code.</p> 
			<p>This is kind of awkward...</p> 
		<?php 
					do_action( 'post_red_page', $customer, $item );
					endif;
				?>
	</div> 
</body> 
</html>
<?php
}

function wpec_verify_code() {
	global $post, $wpdb, $purchlogitem;
	
	require_once( WPSC_FILE_PATH . '/wpsc-includes/purchaselogs.class.php' );
	
	if( ! isset( $_REQUEST['purchase_id'] ) || ! isset( $_REQUEST['verify_deal'] ) )
		return;

	$purchase_id = (int) $_REQUEST['purchase_id'];
	$product_id = get_post_field( 'post_parent', $purchase_id );
	$product_title = get_post_field( 'post_title', $product_id );

	$redeemed = get_post_meta( $purchase_id, '_redeemed', true );
	
	//All this sillyness, just to get a name.

	$session_id = get_post_meta( $purchase_id, '_session_id', true );
	$sql = "SELECT id FROM ".WPSC_TABLE_PURCHASE_LOGS." WHERE sessionid = $session_id ";
	$refsql = "SELECT user_ID FROM ".WPSC_TABLE_PURCHASE_LOGS." WHERE sessionid = $session_id ";
	$purch_log_id = $wpdb->get_var( $sql );
	$referrer_by = $wpdb->get_var( $refsql );
	$purchlogitem = new wpsc_purchaselogs_items( $purch_log_id );
	
		//Checking if we haven't set the redemption time - that means we're at square one.
		if( ! $redeemed && ! isset( $_REQUEST['redeemed'] ) ) :

			verify_template( 'verify', wpsc_display_purchlog_buyers_name(), $product_title ); // Deal has NOT been redeemed...

		//If we're visiting this page and we've ALREADY set the redemption time, that means it's a duplicate cheater!
		elseif( $redeemed ) :

			verify_template( 'bad', wpsc_display_purchlog_buyers_name() ); // Deal HAS been redeemed..

		//Finally, if we're good to go, show the good page.
 
		elseif( ! $redeemed && isset( $_REQUEST['redeemed'] ) ) :

			update_post_meta( $purchase_id, '_redeemed', (int)$_REQUEST['redeemed'] );
			
			verify_template( 'good' );
			
		endif;

		exit;

	}

add_action( 'init', 'wpec_verify_code' );
function gd_pdf_bypass( $filearray ) {
	$filearray['type'] = 'image/jpeg';
	$filearray['ext'] = 1;
	return $filearray;
}

// Redemption certificate HTML.
function wpec_redemption_certificate( $purchase_id ){
	global $post, $wpdb, $purchlogitem, $product_id, $business;
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	require_once( ABSPATH . 'wp-admin/includes/media.php' );
	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	
	add_filter( 'wp_check_filetype_and_ext', 'gd_pdf_bypass' );

	$options = get_option( 'wpec_gd_options_array' );

	// Some of these could probably be removed.
	$purchase_id = (int) $_REQUEST['purchase_id'];
	$product_id = get_post_field( 'post_parent', $purchase_id );
	$product_title = get_post_field( 'post_title', $product_id );	    
	
	$product_meta = get_post_meta( $product_id, "_wpec_dd_fine_print", true );
	$product_price = get_post_meta( $product_id, "_wpsc_special_price", true );
	$product_fp = $product_meta["fine_print"];
	
	$exp_date = get_post_meta( $product_id, '_wpec_dd_details', true );
	
	$session_id = get_post_meta( $purchase_id, '_session_id', true );
	$sql = "SELECT id FROM ".WPSC_TABLE_PURCHASE_LOGS." WHERE sessionid = $session_id ";
	$purch_log_id = $wpdb->get_var( $sql );
	$purchlogitem = new wpsc_purchaselogs_items( $purch_log_id );
	
	$redemption =  wpec_dd_redemption_code($_REQUEST['purchase_id']);
	
	$business_id = $wpdb->get_var( "SELECT post_parent FROM $wpdb->posts WHERE ID = $product_id" );
	$business = get_post($business_id);
	$business_meta = get_post_meta( $business_id, "_wpec_dd_vendor_details", true );
	
	$ufp = __( "Not valid for cash back (unless required by law). Must use in one visit. Doesn't cover tax or gratuity. Can't be combined with other offers.", 'wpec-group-deals' );
?>
<html>
<head>
	<style>
		body { margin:0; font: 200 11px/1.2 "Helvetica Neue", Arial, Helvetica, Geneva, sans-serif; }
		strong { font-weight: 900; }
		h1, h2, h3, h4, h5, h6 { margin: 0; padding: 0; }
		h1 { margin: 0; padding: 0; float: right; font-size: 30px; }
		h2 { font-size: 20px; margin: 0px 0 0px 0; }
		h3 { margin: 0 0 10px 0; font-size: 16px; font-weight: 500; }
		ol, li { margin: 0; padding: 0; }
		ol { margin-bottom: 20px; }
		li { margin-left:  20px; }
	</style>
</head>

<table style="border: 2px solid black; width: 100%; padding: 20px">
	<?php do_action( 'wpec_gd_pdf_pre_header', $purchase_id ); ?>
	<tr>
		<td valign="top" style="width: 100px;">
			<?php 
				if ( ! empty( $options['gd_logo_upload'] ) ) 
					echo "<img style='height: 80px' src='" . str_replace( home_url() . '/',  '', esc_url( $options['gd_logo_upload'] ) ) . "' />"; 
			?>
		</td>
		<td valign="middle" style="text-align:right">
			<h1><?php echo $redemption ?></h1> 
		</td>
	</tr>
	
	<tr>
		<td colspan="2">
		<hr>
			<h2><?php echo $business->post_title; ?></h2>
			<h3><?php echo $product_title; ?></h3>
		
		</td>
	</tr>
	
	<?php do_action( 'wpec_gd_pdf_after_header', $purchase_id ); ?>
	<tr>
		<td valign="top" colspan="2">
			<table style="width:100%">
				<tr>
					<td valign="top" style="width:50%;">
					<?php do_action( 'wpec_gd_pdf_pre_leftsection', $purchase_id ); ?>
						<p><strong>Recipients:</strong><br />
						<?php echo wpsc_display_purchlog_buyers_name(); ?></p>
					
						<p><strong>Expires On:</strong><br />
						<?php echo date( "F d, Y", mktime( 0, 0, 0, $exp_date["expdate"]["mm"], $exp_date["expdate"]["dd"], $exp_date["expdate"]["yy"] ) ); ?>					
						
						<p><small><strong>Fine Print:</strong><br />
						<?php echo $product_meta; ?></small></p>		
					
						<small style="line-height:1;">
							<strong>Universal Fine Print</strong><br />
							<?php echo apply_filters( 'wpec_dd_universal_fine_print', $ufp, $purchase_id ); ?>
						</small>
					<?php do_action( 'wpec_gd_pdf_after_leftsection', $purchase_id ); ?>
					</td>

					<td valign="top" style="width:50%; position:relative">
					<?php do_action( 'wpec_gd_pdf_pre_rightsection', $purchase_id ); ?>
											
																											   
						<p><strong>Redeem at: </strong><br />
							<?php echo $business->post_title; ?><br />
							<em><?php echo nl2br( $business_meta["address"] ); ?><br />
							<?php echo nl2br( $business_meta["phone"] ); ?></em>
						</p>
					
						<p style="text-align:right;margin:0;padding:0">
							<?php
							echo str_replace( home_url() . '/', '', wpec_qr_code( $purchase_id, $product_id ) ); 
							?>
						</p>
					<?php do_action( 'wpec_gd_pdf_after_rightsection', $purchase_id ); ?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<table style="padding:20px">
	<tr>
		<td valign="top" style="width:50%; padding-right: 30px">
		<?php do_action( 'wpec_gd_pdf_pre_howtouse', $purchase_id ); ?>
			<h2>How to use this:</h2>
			<ol>
				<li>Print this voucher and present it to the merchant when redeeming.</li>
				<li>Anytime before the expiration date, present this certificate at <?php echo $business->post_title; ?>.</li>
				<li>Enjoy!</li>
			</ol>
			<small>*Remember: <?php bloginfo('name'); ?> customers tip on the full
			amount of the pre-discounted bill (and tip
			generously). That's why we are the coolest
			customers out there.</small>
		<?php do_action( 'wpec_gd_pdf_after_howtouse', $purchase_id ); ?>		
		</td>
		
		<td valign="top" style="width:50%;">
		<?php do_action( 'wpec_gd_pdf_pre_map', $purchase_id ); ?>
			<h2>Map:</h2>
			<?php
				$url = "http://maps.google.com/maps/api/staticmap?center=" . preg_replace( "/ /","+", $business_meta["address"] ) . "&zoom=9&size=350x170&markers=color:blue|label:|" . preg_replace( "/ /", "+", $business_meta["address"] ) . "&sensor=false&ext=.jpg";
				echo str_replace( home_url() . '/', '', media_sideload_image( $url, $purchase_id ) );
			?>
									<?php do_action( 'wpec_gd_pdf_after_map', $purchase_id ); ?>
		</td>
	</tr>
	
	<tr>
		<td valign="top" colspan="2">
		<?php do_action( 'wpec_gd_pdf_pre_support', $purchase_id ); ?>
		<?php do_action( 'wpec_gd_pdf_after_support', $purchase_id ); ?>
		</td>
	</tr>
	<?php
		$meta = get_post_meta( $product_id, '_wpec_dd_details', true );
		$meta = $meta['expdate'];
		$meta = $meta['mm'] . '/' . $meta['dd'] . '/' . $meta['yy'];
		$exp_date = date( 'M jS, Y', strtotime( $meta ) );
	?>
	<tr>
		<td valign="top" colspan="2" style="padding-top: 20px;">
		<?php do_action( 'wpec_gd_pdf_pre_footer', $purchase_id ); ?>
		<small style="font-size:11px; line-height:1">
			<strong>Legal Stuff We Have To Say:</strong>
			General terms applicable to all Vouchers (unless otherwise set forth below, in <?php bloginfo('name'); ?>'s Terms of Sale, or in the Fine Print): Unless prohibited by applicable law the following restrictions also apply. See below for
			further details. Discount Voucher Expires On: <?php echo $expdate; ?>. However, even if the promotional offer stated on your <?php bloginfo('name'); ?> has expired, applicable law may require the merchant to allow you to redeem your Voucher
			beyond its expiration date for goods/services equal to the amount you paid for it. If you have gone to the merchant and the merchant has refused to redeem the cash value of your expired Voucher, and if applicable
			law entitles you to such redemption, <?php bloginfo('name'); ?> will refund the purchase price of the Voucher per its Terms of Sale. Partial Redemptions: If you redeem the Voucher for less than its face value, you only will be entitled to
			a credit or cash equal to the difference between the face value and the amount you redeemed from the merchant if applicable law requires it.If you redeem this <?php bloginfo('name'); ?> Voucher for less than the total face value, you
			will not be entitled to receive any credit or cash for the difference between the face value and the amount you redeemed, (unless otherwise required by applicable law). You will only be entitled to a redemption value
			equal to the amount you paid for the <?php bloginfo('name'); ?> less the amount actually redeemed. Redemption Value: If not redeemed by the discount voucher expiration date, this <?php bloginfo('name'); ?> will continue to have a redemption value
			equal to the amount you paid (<?php echo wpsc_currency_display( $product_price ); ?>) at the named merchant for the period specified by applicable law. The redemption value will be reduced by the amount of purchases made. This <?php bloginfo('name'); ?> expiration date above, the
			merchant will, in its discretion: (1) allow you to redeem this Voucher for the product or service specified on the Voucher or (2) allow you to redeem the Voucher to purchase other goods or services from the merchant
			for up to the amount you paid (<?php echo wpsc_currency_display( $product_price ); ?>) for the Voucher. This Voucher can only be used for making purchases of goods/services at the named merchant. Merchant is solely responsible for Voucher redemption. Vouchers
			cannot be redeemed for cash or applied as payment to any account unless required by applicable law. Neither <?php bloginfo('name'); ?>, Inc. nor the named merchant shall be responsible for <?php bloginfo('name'); ?>s Vouchers that are lost or
			damaged. Voucher is for promotional purposes. Use of Vouchers are subject to <?php bloginfo('name'); ?>'s Terms of Sale found at <?php bloginfo('url'); ?>.com/terms
		</small>
		<?php do_action( 'wpec_gd_pdf_after_footer', $purchase_id ); ?>
		</td>
	</tr>	
</table>
</body>
</html>
<?php
	remove_filter( 'wp_check_filetype_and_ext', 'gd_pdf_bypass' );
}

// Show the redemption certificate (HTML or PDF)
function wpec_get_certificate() {
	global $post;

	if( ! isset( $_REQUEST['certificate'] ) || ! isset( $_REQUEST['purchase_id'] ) )
		return;

	$purchase_id = (int) $_REQUEST['purchase_id'];
	
	$redemption =  wpec_dd_redemption_code( $_REQUEST['purchase_id'] );
	
	$plugin_dir = WP_PLUGIN_DIR . '/' . str_replace( basename( __FILE__ ),"",plugin_basename( __FILE__ ) );
					
				$url = add_query_arg( array( 'certificate' => 1, 'purchase_id' => $purchase_id ), home_url() );
					
	// Print to HTML or PDF
	if( ! isset( $_REQUEST['pdf'] ) ) :
		// Print HTML certificate...
		wpec_redemption_certificate( $purchase_id );
	else :
									// Load dompdf library
									require_once( $plugin_dir . "dompdf/dompdf_config.inc.php" );
									$html = wp_remote_get( $url );
									$html = $html['body'];
									// Print PDF certificate.
		$dompdf = new DOMPDF();
		$dompdf->load_html( $html );
		$dompdf->render();
		$dompdf->stream( $redemption . ".pdf" );
	endif;
	
	die();
}

add_action( 'init', 'wpec_get_certificate' );


/* When the offer is over, GetShopped, the plugin purchaser, consumers and the business are all notified
 *  with relevant info (financials to GS, biz, plugin purchaser - email with “Group Deal” to consumer.) and
 *  the order is batched automatically.
 *
 * @todo Convert sales and revenue to shortcodes
 */

function wpec_dd_offer_over() {
	global $wpdb, $wpsc_page_titles;

	//Loop through all deals (Should have a post_parent {vendor}, be a product, and be published.  Should weed out variations, product files, other products etc. )
	//Not concerned about any meta except details at this point
 
	$deals = $wpdb->get_results( "SELECT p.ID, p.post_title, p.post_parent, pm.meta_key, pm.meta_value
	FROM $wpdb->posts AS p
	LEFT JOIN $wpdb->postmeta AS pm ON p.ID = pm.post_id
	WHERE p.post_type =  'wpsc-product'
	AND p.post_status =  'publish'
	AND p.post_parent > 0
	AND pm.meta_key = '_wpec_dd_details'", ARRAY_A );

	if( ! $deals )
		return;

	//Loop through all deals who expired between now and 24 hours from now.

	$expired_deals = array();

	foreach ( $deals as $deal ) {
		
		$timesend = get_post_meta( get_the_ID(), '_wpec_dd_details', true );

		$offer_over = get_post_meta( $deal["ID"], "_offer_over", true );
		$product_meta = maybe_unserialize( $deal["meta_value"] );
		$product_expiration_date = strtotime( get_wpec_ajax_countdown( $deal["ID"], 'internal' ) );
	   
		$weekend = "-".$timesend["stopw"]." week";
		$dayend = "-".$timesend["stopw"]." day";
		$hourend = "-".$timesend["stopw"]." hour";
	   
		$now =  strtotime( date_i18n( 'F j, Y H:i:s' ) );
		$yesterday = strtotime( "$weekend $dayend $hourend", strtotime( date_i18n( 'F j, Y H:i:s' ) ) );
		
		/*  
		 *  CHRIS'S NOTE: Using the same function as the time remaining widget, just replaced the 
		 *  pluses with minuses and am using basically all the same functionality that was already
		 *  there.  Added the 'stopn' below to prevent this loop from adding deals that don't expire
		 *  to the expired deals array.
		 *
		 */
		 
		//This should indicate deals that have expired in the last day and have not already been marked as 'over'
		if( $product_expiration_date < $now && $product_expiration_date > $yesterday && empty( $offer_over ) && $timesend["stopn"] !== 1 )
			$expired_deals[] = array( "ID" => $deal["ID"], "post_parent" => $deal["post_parent"] );
	}

	if( empty( $expired_deals ) )
		return;

	//Now we loop through these deals, and email the relevant info to each interested party
	$store_name = wp_specialchars_decode( get_option('blogname'), ENT_QUOTES );
	$login_url = home_url( $wpsc_page_titles['userlog'] );

	foreach( $expired_deals as $key => $deal ) {
		
		update_post_meta( $deal['ID'], '_offer_over', 'true' );

		$tipping_point = get_post_meta( $deal['ID'], '_wpec_dd_details', true );
		$tipping_point = (int)$tipping_point["tipping_point"];
		$purchased = absint( wpec_deals_purchased( $deal['ID'] ) );

		$tipped = ( $purchased >= $tipping_point ) ? true : false;

		//Should potentially convert these to shortcodes
		$total_sales = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_type = 'wpec-dd-purchase' AND post_parent = {$deal["ID"]} " ) );
		$total_revenue = wpsc_currency_display( ( $total_sales * get_post_meta( $deal["ID"], '_wpsc_special_price', true ) ) );
		
		$customer_emails = wpec_dd_get_customer_emails( $deal["ID"] );        
		$emails = stripslashes_deep( wpec_get_email_content( $deal, $customer_emails["ID"] ) );

		//Financials ( Goes to Business, Site Owner and GetShopped )
		
		if( ! $tipped ) {
			$business_subject = $emails["business_owner"]["untipped"]["subject"];
			$business_message = $emails["business_owner"]["untipped"]["body"];

			$site_owner_subject = $emails["site_owner"]["untipped"]["subject"];
			$site_owner_message = $emails["site_owner"]["untipped"]["body"];

		} else {
			$business_subject = $emails["business_owner"]["expired"]["subject"];
			$business_message = $emails["business_owner"]["expired"]["body"];

			$site_owner_subject = $emails["site_owner"]["expired"]["subject"];
			$site_owner_message = $emails["site_owner"]["expired"]["body"];
		}

		$business_email = get_post_meta( $deal['post_parent'], "_wpec_dd_vendor_details", true );
		$business_email = apply_filters( 'redemption_csv_to_email_address', $business_email['email'] );

		add_filter( 'wp_mail_content_type','wpec_dd_set_contenttype' );

		$headers = '';

		$attachment = wpec_gd_generate_redemption_csv( $deal["ID"] );
		wp_mail( $business_email, $business_subject, $business_message, $headers, $attachment );
		wp_mail( get_option( 'admin_email' ), $site_owner_subject, $site_owner_message );
		
		wpec_gd_offer_over_api( $deal["ID"], $tipped, $total_sales, $total_revenue );

		remove_filter( 'wp_mail_content_type','wpec_dd_set_contenttype' );
		//If deal has tipped...let them know.
		//If it hasn't tipped, let's email them here and let them know the sad news :(
		
		if( $tipped ) {
			do_action( 'tipped_and_expired', $deal['ID'], $customer_emails );
			$subject = $emails["deal_purchaser"]["expired"]["subject"];
			$message = $emails["deal_purchaser"]["expired"]["body"];
			wpec_gd_final_payment( $deal["ID"], true );

		} else {
			do_action( 'untipped_and_expired', $deal['ID'], $customer_emails );
			$subject = $emails["deal_purchaser"]["untipped"]["subject"];
			$message = $emails["deal_purchaser"]["untipped"]["body"];
			wpec_gd_final_payment( $deal["ID"], false );
		}

		$send_customer_emails = apply_filters( 'wpec_gd_send_email_switch', true );

		add_filter( 'wp_mail_content_type','wpec_dd_set_contenttype' );
		if( $send_customer_emails )
			foreach( $customer_emails as $key => $user_email )
				@wp_mail( $user_email, $subject, $message );
		
		remove_filter( 'wp_mail_content_type','wpec_dd_set_contenttype' );

	}
}
/*
 * wpec_gd_final_payment
 *
 * Either refunds payment or pays secondary receivers, depending on whether or not deal tipped
 *
 * Called on offer expiration
 *
 * @param $deal_id ID of Deal
 * @param $tipped (bool) if true, deal is tipped, pay secondary receivers.  If false, deal didn't tip, refund buyer.
 *
 * @todo Get smarter about handling errors for payment request
 *
 */

function wpec_gd_final_payment( $deal_id = '', $tipped = true ) {
	global $wpdb;

	//Get all pay keys and email addresses for deal
	$sql = "SELECT posts.ID, postmeta.meta_key, postmeta.meta_value, checkout.unique_name, formdata.value, plogs.totalprice FROM $wpdb->posts posts
	LEFT JOIN $wpdb->postmeta postmeta ON postmeta.post_id = posts.ID
	LEFT JOIN ".WPSC_TABLE_PURCHASE_LOGS." plogs ON postmeta.meta_value = plogs.sessionid
	LEFT JOIN ".WPSC_TABLE_SUBMITED_FORM_DATA." formdata ON formdata.log_id = plogs.id
	LEFT JOIN ".WPSC_TABLE_CHECKOUT_FORMS." checkout ON formdata.form_id = checkout.id
	WHERE posts.post_type =  'wpec-dd-purchase'
	AND posts.post_parent = $deal_id
	AND postmeta.meta_key IN ( '_session_id', '_pay_key')
AND ( checkout.unique_name IS NULL OR checkout.unique_name = 'billingemail' )";

	$results = $wpdb->get_results( $sql );

	$purchases = array();
	
	foreach( $results as $row ) {
		if( $row->meta_key == '_session_id' )
			$purchases[$row->ID] = array( 'session_id' => $row->meta_value, 'email' => $row->value , 'total_price' => $row->totalprice );
		else
			$purchases[$row->ID]['pay_key'] = $row->meta_value;

	}
	$currency_code = $wpdb->get_var( "SELECT `code` FROM `" . WPSC_TABLE_CURRENCY_LIST . "` WHERE `id`='" . get_option( 'currency_type' ) . "' LIMIT 1" );

	foreach( $purchases as $purchase ) {

		$pay_key = $purchase['pay_key'];

	 //Foreach paykey, if $tipped, pay receivers
		if( $tipped ) {
			$executePaymentRequest = new ExecutePaymentRequest();
			$executePaymentRequest->payKey = $pay_key;

			$executePaymentRequest->requestEnvelope = new RequestEnvelope();
			$executePaymentRequest->requestEnvelope->errorLanguage = "en_US";
			$ap = new AdaptivePayments();
			$response = $ap->ExecutePayment( $executePaymentRequest );
			
		} else {

		//If not, refund sender
			$refundRequest = new RefundRequest();
			$refundRequest->currencyCode =  $currency_code;
			$refundRequest->payKey = $pay_key;
			$refundRequest->requestEnvelope = new RequestEnvelope();
			$refundRequest->requestEnvelope->errorLanguage = "en_US";

			$ap = new AdaptivePayments();
			$response = $ap->Refund( $refundRequest );
			
		}

	}


}

/*
 * Generates CSV file of Names and Redemption codes
 * Basically a handy wrapper function
 */

function wpec_gd_generate_redemption_csv( $deal_id = '' ) {
	global $wpec_gd_ajax;
	
	if( ! is_object( $wpec_gd_ajax ) )
		$wpec_gd_ajax = new WPEC_GD_Ajax;

	return $wpec_gd_ajax->send_vendor_emails( $deal_id );	

}

function wpec_dd_get_customer_emails( $deal_id ) {
	global $wpdb;
	// Pretty hardcore, and potentially less than optimal SQL Query to get all email addresses for people that have ordered the deal.
	$consumer_email_sql = "SELECT formdata.value, posts.ID FROM  $wpdb->posts posts
	LEFT JOIN $wpdb->postmeta postmeta ON postmeta.post_id = posts.ID
	LEFT JOIN ".WPSC_TABLE_PURCHASE_LOGS." plogs ON postmeta.meta_value = plogs.sessionid
	LEFT JOIN ".WPSC_TABLE_SUBMITED_FORM_DATA." formdata ON formdata.log_id = plogs.id
	LEFT JOIN ".WPSC_TABLE_CHECKOUT_FORMS." checkout ON formdata.form_id = checkout.id
	WHERE posts.post_type =  'wpec-dd-purchase'
	AND posts.post_parent = $deal_id
	AND postmeta.meta_key =  '_session_id'
	AND checkout.unique_name =  'billingemail'";

	$user_emails = $wpdb->get_results( $wpdb->prepare( $consumer_email_sql, $deal_id ), ARRAY_A );
	$user_emails = array_unique( $user_emails );

	return (array) $user_emails;

}

add_action( 'deal_over_email', 'wpec_dd_offer_over' );
add_action( 'new_group_deal_email', 'wpec_dd_email_subscriber' );

/*
 * Emails site owner, consumers and business owner as email tips
 *
 * @param $order_id
 * @return null
 */

function wpec_dd_email_on_tip( $order_id ) {

	$order = get_post_field( 'post_parent', $order_id );
	$deal = get_post( $order );
	$business = get_post( $deal->post_parent );
	$post = $deal;

	$business_email = get_post_meta( $business->ID, "_wpec_dd_vendor_details", true );
	$business_email = $business_email["email"];
	
	$emailed = get_post_meta( $post->ID, '_emailed', true );
	$tipping_point = get_post_meta( $post->ID, '_wpec_dd_details', true );
	$tipping_point = (int)$tipping_point["tipping_point"];
	$purchased = wpec_deals_purchased( $post->ID );

	$user_emails = wpec_dd_get_customer_emails( $post->ID );

	$emails = wpec_get_email_content( $deal );

   if( $purchased < $tipping_point || 'sent' == $emailed )
	   return;

	//These could eventually be templates in the options area, or better yet, integrate with MailChimp :) :) :)
	$store_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

	 
	add_filter( 'wp_mail_content_type','wpec_dd_set_contenttype' );
	//Email to site owner

	@wp_mail( get_option( 'admin_email' ), $emails["site_owner"]["tipped"]["subject"], $emails["site_owner"]["tipped"]["body"] );

	//Email to business owner

	@wp_mail( $business_email, $emails["business_owner"]["tipped"]["subject"], $emails["business_owner"]["tipped"]["body"] );

	//Email all current recipients - using array_unique to only send the email to each person once

	foreach( (array)$user_emails as $key => $user_email )
		@wp_mail( $user_email["value"], $emails["deal_purchaser"]["tipped"]["subject"], $emails["deal_purchaser"]["tipped"]["body"] );

	add_post_meta( $post->ID, '_emailed', 'sent', true );
	
	remove_filter( 'wp_mail_content_type','wpec_dd_set_contenttype' );

}

function wpec_dd_get_content( $deal_id ) {
	global $wpdb;

	$output = $wpdb->get_var( "SELECT `post_content` FROM `" . $wpdb->posts . "` WHERE `id`='{$deal_id}' LIMIT 1" );

	return $output;
}


// Generate QR code
function wpec_qr_code( $purchase_id, $deal_id = '' ) {
	
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	require_once( ABSPATH . 'wp-admin/includes/media.php' );
	require_once( ABSPATH . 'wp-admin/includes/image.php' );

	if( isset( $purchase_id ) && isset( $deal_id ) ) {
			$qr = media_sideload_image( "http://chart.apis.google.com/chart?ext=.jpg&cht=qr&chs=150x150&chl=" . urlencode( add_query_arg( array( 'verify_deal' => $deal_id, 'purchase_id' => $purchase_id  ), home_url() ) ) . "&chld=H|0", $purchase_id );
		
	  }

	return $qr;
}



/*
 * Gets all email content set in options on Settings page
 * Email array format - [To][Action][Content]
 *
 * @param (int)$order_id ID in post database of specific purchase
 * @param (obj) $deal get_post() on the actual deal itself.
 *
 * @return (array) Returns multidimensional associative array of all emails in settings
 */
 
 

function wpec_get_email_content( $deal, $purchase_id = false ) {
	global $wpsc_page_titles;

	if( is_array( $deal ) )
		$deal = (object) $deal;

	//Email options
	//Quick review - Tipped = Sent as soon as the deal tips.  Untipped = Sent upon deal expiry if the deal doesn't tip. Expired = Sent on expiry if it does tip

	$site_owner_tipped = get_option( 'site_owner_tipped' );
	$site_owner_untipped = get_option( 'site_owner_untipped' );
	$site_owner_expired = get_option( 'site_owner_expired' );

	$business_owner_tipped = get_option( 'business_owner_tipped' );
	$business_owner_untipped = get_option( 'business_owner_untipped' );
	$business_owner_expired = get_option( 'business_owner_expired' );

	$deal_purchaser_tipped = get_option( 'deal_purchaser_tipped' );
	$deal_purchaser_untipped = get_option( 'deal_purchaser_untipped' );
	$deal_purchaser_expired = get_option( 'deal_purchaser_expired' );
	$deal_purchaser_new_deal = get_option( 'deal_purchaser_new_deal' );

	//Email bodies array

	$emails = array(
		'site_owner' => array(
			'tipped' => array(
				'subject' => $site_owner_tipped['subject'],
				'body' => $site_owner_tipped['body']
			),
			'untipped' => array(
				'subject' => $site_owner_untipped['subject'],
				'body' => $site_owner_untipped['body']
			),
			'expired' => array(
				'subject' => $site_owner_expired['subject'],
				'body' => $site_owner_expired['body']
			)
		),
		'business_owner' => array(
			'tipped' => array(
				'subject' => $business_owner_tipped['subject'],
				'body' => $business_owner_tipped['body']
			),
			'untipped' => array(
				'subject' => $business_owner_untipped['subject'],
				'body' => $business_owner_untipped['body']
			),
			'expired' => array(
				'subject' => $business_owner_expired['subject'],
				'body' => $business_owner_expired['body']
			)
		),
		'deal_purchaser' => array(
			'tipped' => array(
				'subject' => $deal_purchaser_tipped['subject'],
				'body' =>$deal_purchaser_tipped['body']
			),
			'untipped' => array(
				'subject' => $deal_purchaser_untipped['subject'],
				'body' =>$deal_purchaser_untipped['body']
			),
			'expired' => array(
				'subject' => $deal_purchaser_expired['subject'],
				'body' =>$deal_purchaser_expired['body']
			),
			'new_deal' => array(
				'subject' => $deal_purchaser_new_deal['subject'],
				'body' => $deal_purchaser_new_deal['body']
			)
		)
	);

	//Replacement array
	$tipping_point = get_post_meta( $deal->ID, '_wpec_dd_details', true );
	$tipping_point = (int)$tipping_point["tipping_point"];
	$highlights = get_post_meta( $deal->ID, '_wpec_dd_highlights', true );
	$fine_print = get_post_meta( $deal->ID, '_wpec_dd_fine_print', true );
	$business_name = get_post_field( 'post_title', $deal->post_parent );
	$company_info = get_post_field( 'post_content', $deal->post_parent );

	$business_meta = get_post_meta( $deal->post_parent, '_wpec_dd_vendor_details', true );
	$business_link = $business_meta['url'];
	$business_address = nl2br( $business_meta['address'] );
	$business_map_link = "http://maps.google.com/maps?hl=en&nord=1&q=" . urlencode( $business_meta['address'] );
	$location = wp_get_object_terms( $deal->ID, 'wpec-gd-location', array( 'orderby' => 'term_id', 'order' => 'DESC', 'fields' => 'names' ) );
	$location = $location[0];
	
	
	$shortcode_replacements = array(
		'[deal_title]' => get_the_title( $deal->ID ),
		'[deal_name]' => get_the_title( $deal->ID ),
		'[deal_price]' => get_wpec_dd_price( $deal->ID, 'retail' ),
		'[deal_image]' => get_the_post_thumbnail( $deal->ID ),
		'[deal_sale_price]' =>  get_wpec_dd_price( $deal->ID, 'deal' ),
		'[deal_discount]' =>  get_wpec_dd_price( $deal->ID, 'discount' ),
		'[deal_savings]' =>  get_wpec_dd_price( $deal->ID, 'savings' ),
		'[deal_link]' => get_permalink( $deal->ID ),
		'[store_name]' => wp_specialchars_decode( get_option('blogname'), ENT_QUOTES ),
		'[login_url]' => home_url( $wpsc_page_titles['userlog'] ),
		'[tipping_point]' => $tipping_point,
		'[description]' => wpec_dd_get_content( $deal->ID ),
		'[highlights]' => $highlights,
		'[fine_print]' => $fine_print,
		'[company_info]' => $company_info,
		'[business_name]' => $business_name,
		'[qr_code]' => wpec_qr_code( $purchase_id, $deal->ID ),
		'[location]' => $location,
		'[date]' => date( 'F j, Y' ),
		'[business_address]' => $business_address,
		'[business_map_link]' => $business_map_link,
		'[business_link]' => $business_link,
	);

	$shortcode_replacements = apply_filters( 'wpec_gd_email_shortcodes', $shortcode_replacements );

	$new_email = array();
	foreach( $emails as $to=>$action ) {
		$new_email[$to] = $action;
		foreach( $action as $action=>$content )
			$new_email[$to][$action] = str_replace( array_keys( $shortcode_replacements ), $shortcode_replacements, $content );
		}

	return $new_email;
}
/*
 * Authenticates API key and domain against Group Deals API
 * If it's a legitimate key on a known domain, returns true
 * If not, returns WP_Error
 */
function wpec_gd_authenticate() {
	global $authorization;

	$override = apply_filters( 'wpec_gd_authenticate_override', false );

	if( $override )
		return true;

	$api_id = get_option( 'wpec_gd_options_array' );

	if ( ! isset( $api_id['gd_api_id'] ) || empty( $api_id['gd_api_id'] ) )
		return false;

	$api_id = $api_id['gd_api_id'];
	$domain = untrailingslashit( str_replace( array( 'http://', 'www.', 'https://' ), '', network_site_url() ) );
	
	$authorization = array(
			'domain' => base64_encode( $domain ),
			'api_id' => base64_encode( $api_id )
		);

	$args = array(
		'headers' => array(
			'content-type' => 'Content-Type: application/json'
		),
		'body' => $authorization
	);

	$api_call = wp_remote_post( 'http://api.groupdealsplugin.com/auth/', $args );

	if( is_wp_error( $api_call ) )
		return new WP_Error( 'group_deals_auth_failed', __( 'Authentication Failed.  Either you are hacking or you need to call us.', 'wpec-group-deals' ), $api_call->get_error_message() );
	
		
	$result = json_decode( $api_call['body'] );
	$api_domain = $result->api_key->domain;

	return ( $domain == $api_domain );
	
}

function wpec_gd_api_info() {
	global $authorization;

	if( ! wpec_gd_authenticate() )
		return false;

	$plugin_info = wp_remote_get( "http://api.groupdealsplugin.com/auth/?domain={$authorization["domain"]}&api_id={$authorization["api_id"]}" );

	 if( is_wp_error( $plugin_info ) ) {
		return new WP_Error( 'group_deals_auth_failed', __( 'Authentication Failed.  Either you are hacking or you need to call us.', 'wpec-group-deals' ), $plugin_info->get_error_message() );
	} else {

		$result = json_decode( $plugin_info["body"] );
		$api_domain = $result->site_info->domain;

		if( base64_decode( $authorization["domain"] ) == $api_domain )
			return $result->site_info->user_meta;
		else
			return false;
	}
}
/*
 * Creates Group Order
 * 
 * @todo Also, only creates multiple Group Orders if quantity greater than one, not multiple sales.  So only one email goes out.
 */
function wpec_dd_add_order( $purchase_log_object, $sessionid, $display_to_screen ) {

	if ( ! $display_to_screen )
		return;

	global $user_ID;

	if ( ! isset( $user_ID ) )
		$user_ID = 1;

	$cart_items = $purchase_log_object->get_cart_contents();
	$product_id = $cart_items[0]->prodid;
		
	 for( $quantity = $cart_items[0]->quantity; $quantity > 0; $quantity = $quantity - 1 ) {
		$args = array(
			'post_status' => 'publish',
			'post_type' => 'wpec-dd-purchase',
			'post_parent' => $product_id,
			'post_title' => "Order from...",
			'post_content' => ''
		);
		   
		$post_id = wp_insert_post( $args );

		add_post_meta( $post_id, "_session_id", $sessionid, true );

		$order_args = array(
			'ID' => $post_id,
			'post_status' => 'publish',
			'post_type' => 'wpec-dd-purchase',
			'post_parent' => $product_id,
			'post_title' => "Order #$post_id",
			'post_content' => ''
		);

		$order_id = wp_insert_post( $order_args );

		if(  isset( $_SESSION["payKey"] ) )
			add_post_meta( $order_id, '_pay_key', $_SESSION["payKey"], true );
		
		//Checks if this purchase is what tipped it, if so, act accordingly
		wpec_dd_email_on_tip( $order_id );
		
	 }
	 	
	 	//Handles redemption action.

		//Get the referrer's ID and determine whether or not the user has made a purchase yet.          
		$referrer = (int) get_user_meta( $user_ID, 'referred_from', true );
		$already_made_first_purchase = (bool) get_user_meta( $user_ID, '_made_first_purchase', true );

		//If the purchaser was referred and has not yet made first purchase
		if( $referrer && ! $already_made_first_purchase ) {

			$total_refer_count = (int) get_user_meta( $referrer, 'referrals', true );
			$total_refer_new = $total_refer_count + 1;
			
			//Give the referrer credit, and mark the purchaser as having made their first purchase...
			update_user_meta( $referrer, 'referrals', $total_refer_new, $total_refer_count );
			update_user_meta( $user_ID, '_made_first_purchase', 'true' );
			         
		}
			  

		echo "<p>". apply_filters( 'wpec_gd_thank_you_message', __( 'Thanks for your Group Deal order!', 'wpec-group-deals' ) ) ."<br /></p>";
}

add_action( 'wpsc_transaction_results_shutdown', 'wpec_dd_add_order', 15, 3 );

function wpec_dd_set_contenttype( $content_type ){
	return 'text/html';
}
function wpec_dd_redemption_code( $purchase_id ) {

	global $wpdb;

	$product_id = get_post_field( 'post_parent', $purchase_id );
	$business_id = get_post_field( 'post_parent', $product_id );
	$business = get_post( $business_id );

	$redemption_code = strtoupper( substr( $business->post_title, 0, 3 ) ).'-'.$purchase_id.'-';
	$redemption_code .= get_post_meta( $purchase_id, "_session_id", true );

	return apply_filters( 'wpec_gd_redemption_code', $redemption_code, $purchase_id, $product_id, $business );

}
function wpec_show_redemption_code( $purchase ) {
	global $wpdb;
	
	$purchase_id = esc_html( $purchase["sessionid"] );
	$purchase_id = $wpdb->get_col( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_session_id' AND meta_value = $purchase_id" );
	
	if( count( $purchase_id ) > 1 ) : 
	?>
	<p><?php _e( 'You purchased multiple quantites of this deal, therefore, you have multiple certificates', 'wpec-group-deals' ); ?>.</p>
	<?php
	endif;
	
	foreach( $purchase_id as $id ) :
		
		$product_id = get_post_field( 'post_parent', $id );
		
		$tipping_point = get_post_meta( $product_id, '_wpec_dd_details', true );
		$tipping_point = $tipping_point['tipping_point'];
		$purchased = wpec_deals_purchased( $product_id );
	
		if( $purchased < $tipping_point )
			continue;

	?>
	<p>
		<strong><?php _e( 'Your redemption code', 'wpec-group-deals' ); ?>:</strong> <a href="<?php echo home_url() . "?certificate&pdf=true&purchase_id=$id"; ?>">PDF</a> | <a href="<?php echo home_url() . "?certificate&purchase_id=$id"; ?>">HTML</a><br />
		<em><?php _e( 'Use this code at the business to redeem your group deal', 'wpec-group-deals' ); ?>.</em>
	</p>
	<?php
	$i++;
endforeach;
}

add_action( 'wpsc_user_log_after_order_status', 'wpec_show_redemption_code' );

function wpec_dd_ajax_registration_form() {

			?>
	<form id="ajax-registration-form" class='ajax-registration-form'>
		<?php echo wp_nonce_field( 'submit_ajax-registration', '_registration_nonce', true, false ); ?>
		
		<ul id='ajax-registration-list'>
		<?php if( isset( $_COOKIE["wpec_gd_ref"] ) ) : ?>
		<input type="hidden" id="wpec_gd_ref" name="wpec_gd_ref" value="<?php echo $_COOKIE["wpec_gd_ref"]; ?>" />
		<?php endif; ?>
	   <?php do_action( 'wpec_gd_ajax_form_list_items' ); ?>
		<li><input type='text' size='30' name='email' class="email" id='email' value="<?php echo apply_filters( 'wpec_gd_subscribe_form_email_text', __( 'Enter your email address...', 'wpec-group-deals' ) ); ?>" /></li>
		<li><input type='submit' class="large green awesome ajax-submit" value='<?php echo apply_filters( 'wpec_gd_subscribe_form', __( 'Subscribe', 'wpec-group-deals' ) ); ?>' name='ajax-submit' id='ajax-submit' /></li>
		<li id="registration-status-message" class='registration-status-message'></li>
		</ul>
	</form>
		<?php

		}

add_action( 'wp_print_scripts', 'wpec_theme_script', 10 );
add_action( 'wp_print_scripts', 'wpec_localize_scripts', 12 );
add_action( 'wp_print_styles', 'wpec_core_styles' );
add_action( 'wp_head', 'wpec_front_end_center_hack' );
add_action( 'admin_print_styles', 'wpec_core_styles' );


/*
 * Definitely doing core.js wrong - fix Google Map dependency
 * @todo Only show fancybox script on GD home.
 */
function wpec_theme_script() {
	
	if( is_admin() )
		return;
	
		wp_register_script( 'countdown', plugins_url( 'js/jquery.countdown.js', __FILE__ ), array( 'jquery' ), '1.0' );
		wp_register_script( 'wpec_group_deals', plugins_url( 'js/core.js', __FILE__ ), array( 'jquery', 'wpec_google_maps', 'accounting' ), '1.1.10' );
		wp_register_script( 'progressbar', plugins_url( 'js/jquery.progressbar.min.js', __FILE__ ), array( 'jquery' ), '1.0' );
		wp_register_script( 'fancybox', plugins_url( 'js/fancybox.js', __FILE__ ), array( 'jquery' ), '1.0' );
		wp_register_script( 'accounting', plugins_url( 'js/accounting.js', __FILE__ ), array( 'jquery' ), '1.0' );
		wp_enqueue_script( 'countdown' );
		wp_enqueue_script( 'wpec_group_deals' );
		wp_enqueue_script( 'fancybox' );
		wp_enqueue_script( 'accounting' );
}

/*
 * @todo Load popup CSS only on home page.  Currently
 */
function wpec_core_styles() {
	wp_register_style( 'wpec_gd_core', WPEC_DD_URL.'/wpec-gd-admin/wpec_dd.css' );
	wp_register_style( 'wpec_fancybox', WPEC_DD_URL.'/wpec-gd-location/popup.css' );
	wp_enqueue_style( 'wpec_gd_core' );
	wp_enqueue_style( 'wpec_fancybox' );
}
function wpec_front_end_center_hack() {
	?>
	
	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		
		$( 'form.ajax-registration-form' ).delegate("li.error, li.success","class_added", function(){
			 $(this).css( 'right', ( $(this).width() / 2 ) );
		});

	});
	</script>
		
	<?php
}
function wpec_localize_scripts() {
	global $post, $wp_locale;

	if( ! is_object( $post ) )
		return;

	if( is_admin() )
		return;

	$tipping_point = get_post_meta( $post->ID, '_wpec_dd_details', true );

	$tipping_point = absint( $tipping_point["tipping_point"] );
		
	if ( $tipping_point < 1 || empty( $tipping_point ) )
		$tipping_point = 1;
	else
		$tipping_point =  round( ( ( wpec_deals_purchased() / absint( $tipping_point ) ) * 100 ) );

	wp_enqueue_script( 'progressbar' );
	wp_localize_script( 'progressbar', 'progressbar', array(
		'dealExpiresCountdown' => get_wpec_ajax_countdown( get_the_ID(), 'ajax' ),
		'img' => WPEC_DD_URL.'/wpec-gd-images/progressbg_red.gif',
		'boximg' => WPEC_DD_URL.'/wpec-gd-images/progressbar.gif',
		'tippingPoint' => $tipping_point
		) );

	wp_localize_script( 'countdown', 'gd_countdown_labels', array(
			'years' => _x( 'Years', 'countdown javascript', 'wpec-group-deals' ),
			'months' => _x( 'Months', 'countdown javascript', 'wpec-group-deals' ),
			'weeks' => _x( 'Weeks', 'countdown javascript', 'wpec-group-deals' ),
			'days' => _x( 'Days', 'countdown javascript', 'wpec-group-deals' ),
			'hours' => _x( 'Hours', 'countdown javascript', 'wpec-group-deals' ),
			'minutes' => _x( 'Minutes', 'countdown javascript', 'wpec-group-deals' ),
			'seconds' => _x( 'Seconds', 'countdown javascript', 'wpec-group-deals' ),
			'year' => _x( 'Year', 'countdown javascript', 'wpec-group-deals' ),
			'month' => _x( 'Month', 'countdown javascript', 'wpec-group-deals' ),
			'week' => _x( 'Week', 'countdown javascript', 'wpec-group-deals' ),
			'day' => _x( 'Day', 'countdown javascript', 'wpec-group-deals' ),
			'hour' => _x( 'Hour', 'countdown javascript', 'wpec-group-deals' ),
			'minute' => _x( 'Minute', 'countdown javascript', 'wpec-group-deals' ),
			'second' => _x( 'Second', 'countdown javascript', 'wpec-group-deals' )
		) );

	$google_map = get_post_meta( $post->ID, '_show_google_map', true );
	$address = get_post_meta( $post->post_parent, '_wpec_dd_vendor_details', true );
	$address = isset( $address["address"] ) ? $address["address"] : '';
	$scheme = ( is_ssl() ) ? 'https' : 'http';

		wp_register_script( 'wpec_google_maps', $scheme.'://maps.google.com/maps/api/js?sensor=false', array( 'jquery' ), '1.0' );
		wp_enqueue_script( 'wpec_google_maps' );
		wp_localize_script( 'wpec_group_deals', 'gd_object', array(
			'address' => str_replace( array( "\r\n", "\n", "\r" ), ' ', $address ),
			'soldout' => __( 'Sold Out', 'wpec-group-deals' ),
			'sending_data' => __( 'One moment while we create your account...', 'wpec-group-deals' ),
			'decimals' => addslashes( $wp_locale->number_format['decimal_point'] ),
			'thousands' => addslashes( $wp_locale->number_format['thousands_sep'] ),
			'currency_symbol' => wpsc_get_currency_symbol( $post->ID ),
			'in_stock' => (string) wpec_gd_is_in_stock( $post->ID ),
			'woohoo' => sprintf( __( 'You will only use %1$s of your %2$s in available credit on this purchase. You won\'t pay a dime for out of pocket!  Good job!', 'wpec-group-deals' ), '<span class="first_value"></span>', '<span class="second_value"></span>' )
			) );
  
}

/*
 * Retrieves Group Deals Paypal details.  Will be removed in first update
 */

function wpec_gd_api_email() {
	global $authorization;

	if( !wpec_gd_authenticate() )
		return;

	$args["body"] = $authorization;

	$email = wp_remote_post( "http://api.groupdealsplugin.com/auth/email/", $args );

	if( !is_wp_error( $email ) )
		$email = json_decode( $email["body"] );
	
	return $email->option_value;

}
/*
 * Constant retrieval API
 */

function wpec_gd_paypal_constants() {
	global $authorization;

	if( ! wpec_gd_authenticate() )
		return;

	$args['body'] = $authorization;
	$constants = wp_remote_post( 'http://api.groupdealsplugin.com/auth/constants/', $args );

	if( ! is_wp_error( $constants ) )
		$constants = json_decode( $constants["body"] );

	return apply_filters( 'wpec_gd_paypal_constants', $constants );
}


add_filter( 'post_thumbnail_html', 'gd_my_post_image_html', 10, 3 );

function gd_my_post_image_html( $html, $post_id, $post_image_id ) {

	if( is_admin() )
		return $html;

	if( ! is_ssl() )
		return $html;
	
	return str_replace( 'http', 'https', $html );
}

/*
 * Replace header image with user defined logo
 */
 
function wpec_change_logo() {
	$options = get_option( 'wpec_gd_options_array' );

	if( ! empty( $options['gd_logo_upload'] ) ) :
	?>
	<style>
	h1#logo a {
		background: url( '<?php echo esc_url( $options['gd_logo_upload'] ); ?>' ) no-repeat;
	}
	</style>
	<?php
	endif;
}

add_action( 'wp_head', 'wpec_change_logo' );

function wpec_gd_cart_url( $url, $id ) {
	
	return get_permalink( $id );
		
}
/*
 * Surprisingly difficult to modify the image in the cart. 
 * A notable deficiency in this is that if a cart has multiple items from the same vendor, I think we might have unexpected results
 */
function wpec_gd_cart_thumbnail( $image_url ) {
	global $wpdb, $wpsc_cart;

	$ext = substr( $image_url, -4 );
	$urlstrpos = strrpos( $image_url, '-' );
	
	if( $urlstrpos === false )
		$guid = $image_url;
	else
		$guid = substr( $image_url, 0, $urlstrpos ) . $ext;
	
		$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT post_parent FROM $wpdb->posts WHERE guid='" . $guid . "'" ) );
		
	if( ! 'wpec-dd-vendor' == get_post_type( $attachment[0] ) )
		return $image_url;
	
	// Now that we have confirmed presence of the faulty image, we need to cycle through the cart_items, checking if they belong to this vendor ID.
	// If they do, we'll grab the thumbnail from that product_id and display it at a filterable 
	// An expensive query, to be sure.
	
		foreach( $wpsc_cart->cart_items as &$cart_item ) {

			$vendor = get_post_field( 'post_parent', $cart_item->product_id );

			if( $vendor == $attachment[0] ) {
				$image_id = get_post_thumbnail_id( $cart_item->product_id );  
				$image_url = wp_get_attachment_image_src( $image_id, array( 50, 50 ) );  
				$image_url = $image_url[0];
			}

		}
		
	return $image_url;
}

add_filter( 'wpsc_cart_item_url', 'wpec_gd_cart_url', 13, 2 );
add_filter( 'wpsc_product_image', 'wpec_gd_cart_thumbnail' );

/**
 * Looking at Groupon, Living Social, Tippr, etc. - they all only allow one item per checkout.  Lots of potential reasons, but doing this eliminates a lot of headache.
 * 
 */

function wpec_gd_one_item() {
	global $wpsc_cart;

	$wpsc_cart->empty_cart();
     
 }

add_action( 'wpsc_set_cart_item', 'wpec_gd_one_item' );

/**
 * Returns maximum quantity that can be purchased per customer, set in product meta.
 * @param numeric $post_id 
 * @return numeric
 */

function wpec_gd_get_max_qty( $post_id = '' ) {
	global $wpsc_cart;

	if( empty( $post_id ) && is_object( $wpsc_cart ) )
		$post_id = $wpsc_cart->cart_items[0]->product_id;

	$meta = get_post_meta( $post_id, '_wpec_dd_details', true );

	if( isset( $meta['max_qty'] ) )
		$max_qty = (int) $meta['max_qty'];
	else
		$max_qty = 10;
	
	return $max_qty;

}

/**
 * Returns dropdown quantity box with maximum number.
 * @param numeric $post_id 
 * @uses wpec_gd_get_max_qty()
 * @return html
 */

function wpec_gd_max_qty_dropdown( $post_id = '' ) {
	
	$max_qty = wpec_gd_get_max_qty( $post_id );

	$i = 1;

	echo '<select name="quantity" class="max_qty">';
	while( $i <= $max_qty ) :
		echo '<option value="' . $i . '">' . $i . '</option>';
		$i++;
	endwhile;
	echo '</select>';	
}

/**
 * Returns id of single item in cart
 * @return int ID of post
 */

function wpec_gd_single_item_id() {
	global $wpsc_cart;

	if( ! is_object( $wpsc_cart ) )
		return;

	return $wpsc_cart->cart_items[0]->product_id;
	
}
/**
 * Returns name of single item in cart
 * @param numeric $post_id 
 * @return string
 */

function wpec_gd_single_item_name( $post_id = '' ) {
	global $wpsc_cart;

	if( empty( $post_id ) && is_object( $wpsc_cart ) )
		$post_id = $wpsc_cart->cart_items[0]->product_id;
	
	$product_title = get_post_field( 'post_title', $post_id );

	return apply_filters( 'wpsc_cart_product_title', $product_title, $post_id );
	
}

/**
 * Returns unit price.  Don't need to handle total price, we'll do that on the front-end.
 * @param numeric $post_id 
 * @return string
 */

function wpec_gd_single_item_price( $post_id = '' ) {
	global $wpsc_cart;

	if( ! is_object( $wpsc_cart ) )
		return;

	return wpsc_currency_display( $wpsc_cart->cart_items[0]->unit_price, array( 'display_as_html' => false ) );
	
}

/**
 * Processes extra checkout goodies.  If a new user is signing up, it handles the referrer
 * Also, if credit is used, it subtracts the credit from the user's available credit.
 * 
 * @param array $args | user ID and purchase log ID
 * @return none
 */

function wpec_gd_process_checkout( $args ) {
	global $wpsc_cart;
	
	$user = $args['our_user_id'];

	if( is_numeric( $_POST['wpec_gd_ref'] ) )
		update_user_meta( $user, 'referred_from', absint( $_POST['wpec_gd_ref'] ) );
	
	if( $wpsc_cart->used_credits ) {
		$credits = get_user_meta( $user, '_credit_used', true );
		$credits = $wpsc_cart->coupons_amount + floatval( $credits );
		update_user_meta( $user, '_credit_used', $credits );
	}

}

add_action( 'wpsc_submit_checkout', 'wpec_gd_process_checkout' );

/**
 * Returns all fields with a unique name that are not headers.
 * @return array | checkout fields
 */
function wpec_gd_get_unique_names() {

	$checkout = new wpsc_checkout;

	$unique_names = array();

	foreach( $checkout->checkout_items as $key => $checkout_field ) {

		if( empty( $checkout_field->unique_name ) || 'delivertoafriend' == $checkout_field->unique_name )
			continue;

		$unique_names[$checkout_field->name] = $checkout_field->unique_name;
	}

	return array_filter( $unique_names );

}

function wpec_gd_kill_user_session() {
	unset( $_SESSION['wpsc_checkout_saved_values'] );
}

add_action( 'wp_logout', 'wpec_gd_kill_user_session' );

function gd_hide_admin_bar() {
	
	if( current_user_can( 'manage_options' ) )
		return true;

	if( current_user_can( 'wpec_dd_subscriber' ) )
		return false;

}

add_filter( 'show_admin_bar', 'gd_hide_admin_bar' );

/**
 * Checks array or object of location term IDs for the most recent active one.  Makes sure post_status is 'pubish'.  That ensures we're not loading 'future' scheduled posts, drafts, trash, etc.  
 * 
 * Also grabs post_meta for active date (when the deal starts) and how long the deal lasts (when it ends).  We only return the most recently active deal that is STILL active.  It could feasibly happen that a more recently active deal would last for a shorter duration than an older deal, rendering it expired.
 *  
 * @param object | array $deals.  Most often generated from get_object_in_terms()
 * @return integer | deal ID
 */

function wpec_gd_get_latest_active_deal( $deals, $active_only = true ) {

	if( ! is_object( $deals ) && ! is_array( $deals ) )
		return;
	
	$current_time = time();
	$active_deals = array();

	$deals = array_unique( (array) $deals );

	foreach( (array) $deals as $deal_id ) {
		
		$time = get_post_meta( $deal_id, '_wpec_dd_details', true );

		if( 'publish' != get_post_field( 'post_status', $deal_id ) )
			continue;

		$is_eternal = ( isset( $time['stopn'] ) && $time['stopn'] ) ? true : false;

		$start_time_array = $time["start"];
	
		$start_time = '';
		
		if( ! empty( $start_time_array['yy'] ) )
			$start_time .= $start_time_array['yy'];

		if( ! empty( $start_time['mm'] ) )
			$start_time .= '-' . $start_time_array['mm'];

		if( ! empty( $start_time['dd'] ) )
			$start_time .= '-' . $start_time_array['dd'];

		if( ! empty( $start_time['hh'] ) )
			$start_time .= ' ' . $start_time_array['hh'];

		if( ! empty( $start_time['mn'] ) )
			$start_time .= ':' . $start_time_array['mn'] . ':00';

		if( ! empty( $start_time['A'] ) )
			$start_time .= $start_time_array['A'];

		$start_time = strtotime( $start_time );
		$end_date = strtotime( get_wpec_ajax_countdown( $deal_id ) );

		if( ( ( $start_time < $current_time ) && ( $end_date > $current_time ) ) || $is_eternal || ! $active_only )
			$active_deals[$deal_id] = $start_time;
	}

	arsort( $active_deals );

	$deal = array_shift( array_keys( $active_deals ) );
	
	return $deal;
}

function wpec_gd_is_in_stock( $deal_id = '' ) {
	global $post;

	if( empty( $deal_id ) && is_object( $post ) )
		$deal_id = $post->ID;
	
	$stock = get_post_meta( $deal_id, '_wpsc_stock', true );

	if( empty( $stock ) )
		return true;
	
	$stock = (int) $stock;

	$purchased = (int) wpec_deals_purchased( $deal_id );

	return ( $purchased < $stock );

}

?>

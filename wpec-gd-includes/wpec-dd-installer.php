<?php
/*
 * WPEC-DD Installer
 * Roles, custom post types all registered here.
 */

function wpec_dd_install() {

   $labels_ven = array(
        'name' => __( 'Vendors', 'wpec-dd-vendor' ),
        'singular_name' => __( 'Vendor', 'wpec-dd-vendor '),
        'add_new' => _x( 'Add New', 'wpec-gd-vendor', 'wpec-group-deals' ),
        'add_new_item' => __( 'Add New Vendor', 'wpec-group-deals' ),
        'edit_item' => __( 'Edit Vendor', 'wpec-group-deals' ),
        'new_item' => __( 'New Vendor', 'wpec-group-deals' ),
        'view_item' => __( 'View Vendor', 'wpec-group-deals' ),
        'search_items' => __( 'Search Vendors', 'wpec-group-deals' ),
        'not_found' =>  __( 'No vendors found', 'wpec-group-deals' ),
        'not_found_in_trash' => __( 'No vendors found in Trash', 'wpec-group-deals' ),
        'parent_item_colon' => ''
  );

    register_post_type( 
        'wpec-dd-vendor', 
        array(
            'capability_type' => 'page',
            'hierarchical' => true,
            'exclude_from_search' => true,
            'public' => true,
            'show_ui' => true,
            'show_in_nav_menus' => false,
            'labels' => $labels_ven,
            'query_var' => true,
            'register_meta_box_cb' => 'wpec_dd_vendors'
        ) 
    );
    /* Register Custom Content Types for Daily Deal Purchases and Vendors */

   $labels = array(
        'name' => __( 'Group Orders', 'wpec-daily-deal-purchase', 'wpec-group-deals' ),
        'singular_name' => __( 'Group Order', 'wpec-daily-deal-purchase', 'wpec-group-deals' ),
        'add_new' => _x( 'Add New', 'wpec-daily-deal-purchase', 'wpec-group-deals' ),
        'add_new_item' => __( 'Add New Order', 'wpec-group-deals' ),
        'edit_item' => __( 'Edit Order', 'wpec-group-deals' ),
        'new_item' => __( 'New Order', 'wpec-group-deals' ),
        'view_item' => __( 'View Order', 'wpec-group-deals' ),
        'search_items' => __( 'Search Orders', 'wpec-group-deals' ),
        'not_found' =>  __( 'No orders found', 'wpec-group-deals' ),
        'not_found_in_trash' => __( 'No orders found in Trash', 'wpec-group-deals' ),
        'parent_item_colon' => ''
  );

	register_post_type( 'wpec-dd-purchase', array(
                'capability_type' => 'post',
		'hierarchical' => true,
		'exclude_from_search' => true,
		'public' => true,
		'show_ui' => true,
		'show_in_nav_menus' => false,
		'labels' => $labels,
		'query_var' => true,
		'register_meta_box_cb' => 'wpec_dd_purchase_metaboxes'

	) );

        	$labels = array(
		'name' => _x( 'Locations', 'taxonomy general name', 'wpec-group-deals' ),
		'singular_name' => _x( 'Location', 'taxonomy singular name', 'wpec-group-deals' ),
		'search_items' => __( 'Search Locations', 'wpec-group-deals' ),
		'all_items' => __( 'All Locations', 'wpec-group-deals' ),
		'parent_item' => __( 'State/Province', 'wpec-group-deals' ),
		'parent_item_colon' => __( 'States/Provinces:', 'wpec-group-deals' ),
		'edit_item' => __( 'Edit Location', 'wpec-group-deals' ),
		'update_item' => __( 'Update Location', 'wpec-group-deals' ),
		'add_new_item' => __( 'Add New Location', 'wpec-group-deals' ),
		'new_item_name' => __( 'New Location Name', 'wpec-group-deals' ),
	);


            register_taxonomy( 'wpec-gd-location', 'wpsc-product', array(
		'hierarchical' => true,
                'query_var' => 'location',
		'rewrite' => array(
                    'hierarchical' => true,
                    'with_front' => false,
                    'slug' => ''
                ),
		'public' => true,
		'labels' => $labels
	) );

    /* Register User Roles for Deal Purchasers */

    $wpec_subscriber = add_role( 'wpec_dd_subscriber', __( 'Daily Deal Hunter', 'wpec-group-deals' ) );

    $options = get_option( 'wpec_gd_options_array' );
    $image_width = ( isset( $options['wpec_gd_img_width']  ) ) ? $options['wpec_gd_img_width'] : '';
    $image_height = ( isset( $options['wpec_gd_img_height']  ) ) ?  $options['wpec_gd_img_height'] : '';
    $image_crop = ( isset( $options['wpec_gd_crop_img'] ) ) ? $options['wpec_gd_crop_img'] : '';

    add_image_size( 'wpec_dd_home_image', $image_width, $image_height, $image_crop );
    
    /*
     * Email Settings - Default values, as such, no i18n
     */

    $site_owner_tipped = array(
        'subject' => 'Great news! [deal_title] at your site, [store_name], just tipped!',
        'body' => 'That\'s right!  [deal_title] just tipped with [tipping_point] sales!  Super exciting!  Now you just have to wait for the deal to expire!'
    );
    $site_owner_untipped = array(
        'subject' => 'Bummer! [deal_title] expired and it did not tip.',
        'body' => 'True, unfortunately, [deal_title] didn\t reach [tipping_point] sales.  You can definitely contact the business owner to see if they would like to re-run the deal.'
    );
    $site_owner_expired = array(
        'subject' => '[deal_title] just expired on your site! Time to rake in the dough!',
        'body' =>  '[deal_title] just tipped!  Congratulations!  All of the payments to you, the business owner and any affiliates are already being handled; the business owner and all subscribers have been notified via email.'
    );
    $business_owner_tipped = array(
        'subject' => 'Congratulations!  Your deal, [deal_title], at [store_name] just tipped!',
        'body' =>  'That\s right! Your deal just tipped at [store_name].  We will send you another email as soon as the deal expires.  When it expires, you will automatically be paid via your preferred payment method and emailed a list of all the customers and their redemption codes.'
    );
    $business_owner_untipped = array(
        'subject' => 'Bummer!  Your deal, [deal_name] at [store_name] has expired and didn\'t tip.',
        'body' =>  'Sad, but true.  Unfortunately, we were not able to get your desired amount of sales.  Shoot us an email, we\'d be happy to look at re-running the deal for a longer period or with a lower tipping point if you want to try that.'
    );
    $business_owner_expired = array(
        'subject' => 'Your deal, [deal_name], just expired at [store_name]! Time to rake in the dough!',
        'body' =>  'Congratulations!  Your deal, [deal_name], just expired! All of the payments to you, [store_name] and any affiliates are already being handled and you should receive a separate email with the CSV file of customers and redemption codes.'
    );
    $deal_purchaser_tipped = array(
        'subject' => 'Congratulations!  The deal you purchased. [deal_name],  at [store_name] just tipped!',
        'body' =>  'That\'s right! That means you DEFINITELY get the deal, way to go!  Your card will be charged just as soon as the deal expires.  You will be able to login at [login_url] to get your redemption code.'
    );
    $deal_purchaser_untipped = array(
        'subject' => 'Bummer!  The deal you purchased didn\'t tip',
        'body' => 'Sad day!  The deal you purchased, [deal_name], never tipped.  That means the minimum amount of sales, [tipping_point] was not met.  Good news?  Your card won\'t be charged.  Bad news?  Missed out a killer deal.  Next time?  Tell ALL your friends about it so you get the deal!'
    );
    $deal_purchaser_expired = array(
        'subject' => 'Congratulations!  The deal you purchased, [deal_name] just expired and is ready',
        'body' =>  'Congratulations!  The deal you purchased, [deal_name], just expired! That means your card will be billed shortly.  You can login at [login_url] with your username and password to retreive the redemption code you will need to show the business when you redeem your group deal.  Way to save a ton of money!'
    );
    $deal_purchaser_new_deal = array(
        'subject' => '[deal_name]',
        'body' =>  'New Deal at [store_name] from [business_name]<br /><br />Deal: [deal_name] <br /> Original Price: [deal_price] <br /> Group Deal Price: [deal_sale_price] <br />Total Discount: [deal_discount] <br /> Total Savings: [deal_savings]<br /> <a href="[deal_link]">Buy Now</a><br />Description: [description]<br />Highlights: [highlights]<br /> Fine Print: [fine_print]<br /> Company Information: [company_info]'
    );

    $wpec_gd_options_array = array(
        'wpec_gd_img_width' => '',
        'wpec_gd_img_height' => '',
        'wpec_gd_crop_img' => '',
        'wpec_paypal_email' => '',
        'gd_api_id' => ''
    );

    $social_array = array(
        'facebook_api_key' => '',
        'facebook_app_secret' => ''
    );

    add_option( 'site_owner_tipped', $site_owner_tipped );
    add_option( 'site_owner_untipped', $site_owner_untipped );
    add_option( 'site_owner_expired', $site_owner_expired );

    add_option( 'business_owner_tipped', $business_owner_tipped );
    add_option( 'business_owner_untipped', $business_owner_untipped );
    add_option( 'business_owner_expired', $business_owner_expired );

    add_option( 'deal_purchaser_tipped', $deal_purchaser_tipped );
    add_option( 'deal_purchaser_untipped', $deal_purchaser_untipped );
    add_option( 'deal_purchaser_expired', $deal_purchaser_expired );
    add_option( 'deal_purchaser_new_deal', $deal_purchaser_new_deal );

    add_option( 'wpec_gd_options_array', $wpec_gd_options_array );
    add_option( 'social_settings', $social_array );


}
/**
* Moves front-page template to active stylesheet directory
*/

function wpec_move_theme_file() {

    $active_theme = trailingslashit( get_stylesheet_directory() );
    $wpec_gd_theme_dir = trailingslashit( WPEC_DD_FILE_PATH.'/wpec-gd-theme' );
    $filename = 'wpec-gd-group-deals-home.php';

    if( ! file_exists( $active_theme.$filename ) )
        $copy = @copy( $wpec_gd_theme_dir.$filename, $active_theme.$filename );

    wpsc_flush_theme_transients( true );

}

add_action( 'switch_theme', 'wpec_move_theme_file' );

?>

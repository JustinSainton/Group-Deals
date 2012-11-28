<?php

/**
 * 
 * Contains all general functionality for the WPEC GD Checkout process
 */

class WPEC_GD_Checkout {
	
	public function __construct() {
			
			//Add actions for quantity changing in checkout(logged-in or out)
			add_action( 'wp_ajax_qtychange', array( &$this, 'process_quantity_change' ) );
			add_action( 'wp_ajax_nopriv_qtychange', array( &$this, 'process_quantity_change' ) );

			//Processes login during checkout
			add_action( 'wp_ajax_nopriv_process_gd_login', array( &$this, 'process_login' ) );
			add_action( 'wp_ajax_nopriv_get_zip', array( &$this, 'get_location_by_zip' ) );

			//Processes sign-up during checkout.
			add_action( 'wp_ajax_nopriv_process_gd_signup', array( &$this, 'process_signup' ) );
	}

	/**
	 * Attemtps to login user with email and password credentials
	 * @return object WP_User on success, WP_Error on failure
	 */

	public function process_login() {
		
		//Verify the nonce
		check_ajax_referer( 'gd_login', '_ajax_nonce' );
		
		$creds = array();

		$creds['user_login'] = sanitize_email( $_POST['email'] );
		$creds['user_password'] = $_POST['password'];
		$creds['remember'] = true;

		$user = wp_signon( $creds, false );
		
		//If error, return it
		if( is_wp_error( $user ) ) {
			echo json_encode( $user );
			exit;
		}
		
		//Otherwise, we need to return the credits and usermeta, if exists.
		$checkout_values = get_user_meta( $user->ID, 'wpshpcrt_usr_profile', true );

		$checkout_values['credits'] = wpec_gd_user_credits_available( $user_ID );

		echo json_encode( $checkout_values );
		exit;
	}

	/**
	 * Processes quantity update on max_qty dropdown change. Expects quantity and key params
	 * @return json
	 */

	public function process_quantity_change() {
		global $wpsc_cart, $user_ID;
		
		//Verify the nonce
		check_ajax_referer( 'qty_change', '_ajax_nonce' );

		wpsc_update_item_quantity();

		$available_credit = wpec_gd_unformat_currency( wpec_gd_user_credits_available( $user_ID ) );
		$posted_credits = wpec_gd_unformat_currency( $_POST['credits'] );

		$wpsc_cart->coupons_amount = null;
		$wpsc_cart->coupons_name = null;
		unset( $wpsc_cart->used_credits );

		if( is_numeric( $posted_credits ) && $posted_credits <= $available_credit ) {
			
			$wpsc_cart->used_credits = true;
			$wpsc_cart->apply_coupons( $posted_credits );
			
		}

		echo json_encode( array( 'data' => 'Success!' ) );

		exit;
	}

	/**
	 * Processes sign-up during checkout.  This information is submitted with the checkout
	 * 
	 * Recommended checkout work flow is no shipping, mandatory billing info is First Name, Last Name, ZIP Code.
	 * 
	 * That said, between this method and core.js, we try to accommodate shipping and other such fields. Core.js handles hiding the name/address fields, gently parses Full Name field, applying properly to First and Last Name fields, also gathers city, state and country information from ZIP field automagically, if any are required, and applies properly to only those fields that are required.
	 * 
	 * Once checkout is submitted, we should have all necessary information passed through  - all we are doing here is hooking into wpsc_submit_checkout, creating user account based on Create account information.
	 *  
	 * @return JSON object
	 */
	public function process_signup() {
		
		check_ajax_referer( 'gd_signup', '_signup_ajax_nonce' );

		if( ! $_POST['email'] || ! $_POST['password'] )
			return;

		$email = sanitize_email( $_POST['email'] );
		$password = esc_attr( $_POST['password'] );

		$args = array(
				'user_pass' => $password,
				'user_login' => $email,
				'user_email' => $email,
				'role' => 'wpec_dd_subscriber'
			);

		$user = wp_insert_user( $args );

		if( is_wp_error( $user ) ) {
			$_SESSION['wpec_gd_errors'][] = $user->get_error_message();
			die( json_encode( array( 'errors' => $user->get_error_message() ) ) );
		}
			
		$user_data = get_userdata( $user );

		$credentials = array( 
			'user_login' => $user_data->user_login, 
			'user_password' => $password, 
			'remember' => true 
			);

		$login = wp_signon( $credentials );

		if( is_wp_error( $login ) ) {
			$_SESSION['wpec_gd_errors'][] = $login->get_error_message();
			die( '0' );
		}
		
		$location = $this->get_signup_location();		
		
        $locations[] = $location;
        update_user_meta( $user_data->ID, 'location', $locations );	

        $meta_data = array();
        $meta_data = parse_str( $_POST['collected_data'], $meta_data );
		$meta_data = array_map( 'esc_attr', $meta_data );
		$meta_data = apply_filters( 'wpsc_checkout_user_profile_update', $meta_data, $user_data->ID );
		update_user_meta( $user_data->ID, 'wpshpcrt_usr_profile', $meta_data );

		wp_new_user_notification( $user, $password );

		die( json_encode( array( 'response' => 'success' ) ) );
	}

	public function get_signup_location() {

		global $wpsc_cart;

		$product_id = $wpsc_cart->cart_items[0]->product_id;

		$location_slugs = wp_get_object_terms( $wpsc_cart->cart_items[0]->product_id, 'wpec-gd-location' );

		$cities = array();

		foreach( $location_slugs as $location )
			if( wp_get_term_taxonomy_parent_id( $location->term_id, 'wpec-gd-location' ) )
				$cities[] = $location->slug;

		return $cities[0];

	}

	/**
	 * Gets City, State and Country by ZIP. Used primarily via AJAX.
	 * @return JSON object
	 */
	public function get_location_by_zip() {
		global $wpdb;
		
		if( ! isset( $_POST['zip_code'] ) || ! is_numeric( $_POST['zip_code'] ) )
			die();

		$zip_code = esc_attr( $_POST['zip_code'] );

		$get = wp_remote_get( "http://maps.googleapis.com/maps/api/geocode/json?address=$zip_code&sensor=false" );

		$results = json_decode( $get['body'], true );
		$results = $results['results'][0]['address_components'];

		$city = $results[1]['long_name'];
		$state = $results[3]['short_name']; 
		$country = $results[4]['short_name'];

		$state_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `" . WPSC_TABLE_REGION_TAX . "` WHERE code = %s", $state ) );

		echo json_encode( 
			array( 
					'city' => $city,
					'state' => $state_id,
					'country' => $country
				) 
			);
		exit;			
	}

}

$GLOBALS['wpec_gd_checkout'] = new WPEC_GD_Checkout();

?>
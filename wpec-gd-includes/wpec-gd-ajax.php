<?php

/**
 * 
 * Contains all general AJAX functionality for Group Deals
 */

class WPEC_GD_Ajax {
	
	/**
	 * Enqueues scripts, registers AJAX actions on front and back-end.
	 * 
	 */

	public function __construct() {
			
			//Enqueue scripts
			add_action( 'wp_enqueue_scripts', array( &$this, 'add_registration_scripts' ) );

			//Admin AJAX
			add_action( 'wp_ajax_submitajaxregistration', array( &$this, 'process_registration' ) );
			add_action( 'wp_ajax_send_vendor_emails', array( &$this, 'send_vendor_emails' ) );
			add_action( 'wp_ajax_send_subscriber_emails', array( &$this, 'send_subscriber_emails' ) );

			//Front-end AJAX
			add_action( 'wp_ajax_nopriv_submitajaxregistration', array( &$this, 'process_registration' ) );
	}

	/**
	 * Enqueues jQuery form, registration javascript
	 */

	public function add_registration_scripts() { 
			
			wp_enqueue_script( 'jquery-form' );
			wp_enqueue_script( 'ajax-registration-js', plugins_url( 'js/registration.js', __FILE__ ), array( 'jquery', 'jquery-form' ), '1.0' );
			wp_localize_script( 'ajax-registration-js', 'ajaxregistration', array( 'Ajax_Url' => admin_url( 'admin-ajax.php' ) ) );

	}
	/**
	 * Processes registration, whether from widget, drop-down or pop-up
	 * @return json_encoded string
	 */

	public function process_registration() {

			//Verify the nonce
			check_ajax_referer( 'submit_ajax-registration' );
			
			//Get the form fields
			$email = sanitize_email( $_POST['email'] );
			$errors = new WP_Error();
			
			$response = '';
			
			//Using this action to check if email exists AND location meta doesn't exists.  If so, add meta, succeed, die.  If not, return.
			do_action_ref_array( 'wpec_gd_ajax_form_pre_add_user', array( &$response, $email, $_POST ) );
			
			if( is_wp_error( $response ) ) {
				$errors->add( 'email', $response->get_error_messages() );
				echo json_encode( $errors );
				exit;
			}
				
			if ( email_exists( $email ) ) 
				$errors->add( 'email',  __( 'E-mail address is already in use.', 'wpec-group-deals' ) );
			
			//Check required fields
			if ( empty( $email ) )
				$errors->add( 'email', __( 'You must fill out an e-mail address.', 'wpec-group-deals' ) );

			//Do e-mail address validation
			if ( ! is_email( $email ) ) 
				$errors->add( 'email', __( 'E-mail address is invalid.', 'wpec-group-deals' ) );
			
			//Everything has been validated, proceed with creating the user
			//Create the user
			$user_pass = wp_generate_password();

			$user = array(
				'user_login' => $email,
				'user_pass' => $user_pass,
				'user_email' => $email,
				'role' => 'wpec_dd_subscriber',
			);
			
			$user_id = wp_insert_user( $user );

			if( is_wp_error( $user_id ) )
				$errors->add( 'user', __( 'Could not add user.', 'wpec-group-deals' ) );
			
			//If any further errors, send response
			if ( count ( $errors->get_error_codes() ) > 0 ) {
				echo json_encode( $errors );
				exit;
			}
			
			do_action( 'wpec_gd_ajax_submission_success_callback', $_POST, $user_id );
			
			$user_login = get_userdata( $user_id );
			wp_signon(
					array(
						'user_login'    => $user_login->user_login,
						'user_password' => $user_pass,
						'remember'      => false
					)
				);
			
			/*Send e-mail to admin and new user */
			wp_new_user_notification( $user_id, $user_pass );

			echo json_encode( $response );
			exit;

	}

	/*
	 * Generates CSV file of Names and Redemption codes.  Used in cron and manually on product admin via AJAX
	 *
	 * @uses a crap ton of filters
	 * @note Redemption code will always end up last
	 * @note Inherent limitation - First Name, Last Name, and any fields addeed via filters must be required.  If left empty, shenanigans occur.
	 * @todo Use WP_Filesystem API
	 * @return array Path to CSV file
	 */

	public function send_vendor_emails( $deal_id = '' ) {
		global $wpdb;

		if( empty( $deal_id ) && isset( $_POST['post_id'] ) )
			$deal_id = $_POST['post_id'];

		$deal_id = (int) $deal_id;

		if( ! $deal_id )
			return;

		if( isset( $_POST['action'] ) )
			check_ajax_referer( 'send_vendor_emails' );
		
		//Create array of names and redemption codes
		$business_csv_sql_select = apply_filters( 'redemption_csv_sql_select', 'SELECT formdata.value, posts.ID, checkout.unique_name' );
 
		$business_csv_sql_from = apply_filters( 'redemption_csv_sql_from', " FROM " . WPSC_TABLE_CHECKOUT_FORMS . " checkout" );

		$business_csv_sql_left_join = apply_filters( 'redemption_csv_sql_left_join', " LEFT JOIN " . WPSC_TABLE_SUBMITED_FORM_DATA . " formdata ON formdata.form_id = checkout.id
JOIN " . WPSC_TABLE_PURCHASE_LOGS . " plogs ON formdata.log_id = plogs.id
JOIN $wpdb->postmeta postmeta ON postmeta.meta_value = ( plogs.sessionid
COLLATE utf8_unicode_ci ) 
JOIN $wpdb->posts posts ON postmeta.post_id = posts.ID" );

		$business_csv_sql_where = apply_filters( 'redemption_csv_sql_where', $wpdb->prepare( " WHERE checkout.unique_name IN (
		'billingemail',  'billingfirstname',  'billinglastname'
		)
		AND posts.post_parent = %d
		AND postmeta.meta_key =  '_session_id' ORDER BY posts.ID, checkout.checkout_order", $deal_id ), $deal_id );

		$business_csv_sql = $business_csv_sql_select . $business_csv_sql_from . $business_csv_sql_left_join . $business_csv_sql_where;

		$user_info = $wpdb->get_results( $business_csv_sql );

		$total = array();

		foreach( $user_info as $row )
			$total[] = $row->ID;

		$total = count( array_unique( $total ) );

		$i = 0;
		$csv_row = apply_filters( 'redemption_csv_headers', "First Name, Last Name, Email, Redemption Code \r" );

		do_action( 'before_csv_redemption_loop' );
		
		foreach( $user_info as $user_field ) {

			if( is_numeric( $user_field->value ) && strlen( $user_field->value ) < 4 )
				$user_field->value = wpsc_get_state_by_id( $user_field->value, 'name' );

			if( ! isset( $last_id ) )
				$last_id = $user_field->ID;

			$redemption = apply_filters( 'redemption_csv_redemption_code', wpec_dd_redemption_code( $last_id ), $last_id );

			if( $last_id == $user_field->ID )
				$csv_row .= apply_filters( 'redemption_csv_non_redemption_code_value_' . $user_field->unique_name, "{$user_field->value}, ", $user_field->value );
			else
				$csv_row .= apply_filters( 'redemption_csv_redemption_code_field', "$redemption \r{$user_field->value}, ", $user_field->value );

			$last_id = $user_field->ID;
			$i++;
		}

		do_action( 'after_csv_redemption_loop' );
		
		if( isset( $redemption ) )
			$csv_row .= "$redemption";

		$uploads = wp_upload_dir();

		$csv_file = fopen( $uploads["path"] . "/$deal_id-redemption_codes.csv", 'w+' );
		fwrite( $csv_file, $csv_row );
		fclose( $csv_file );

		//If this is coming from an admin AJAX request, then we need to make sure we're mailing
		if( ! empty( $_POST['action'] ) ) {

			if( ! $total ) {
				$response = array(
						'errors' => 'no rows',
						'data' => __( 'There haven\'t been any sales on this product yet, so no email was sent.', 'wpec-group-deals' )
							);
				echo json_encode( $response );
				exit;
			}

			$business_id = get_post_field( 'post_parent', $deal_id );

			$business_title = get_post_field( 'post_title', $business_id );
			$business_email = get_post_meta( $business_id, "_wpec_dd_vendor_details", true );
			$business_email = apply_filters( 'redemption_csv_to_email_address', $business_email['email'] );

			add_filter( 'wp_mail_content_type','wpec_dd_set_contenttype' );
			
			$headers = '';
			
			wp_mail( $business_email, __( 'CSV File from Deal', 'wpec-group-deals' ), __( 'Please find attatched the CSV file of customers and redemption codes from your deal on our website.', 'wpec-group-deals' ), $headers, $uploads["path"] . "/$deal_id-redemption_codes.csv" );
			
			remove_filter( 'wp_mail_content_type','wpec_dd_set_contenttype' );

			$response = array(
						'data' => sprintf( 
											_n( 'You just emailed the CSV file with %1$d customer to %3$s at %2$s!', 'You just emailed the CSV file with %1$d customers to %3$s at %2$s!', $total, 'wpec-group-deals' ), 
											$total, 
											$business_title,
											$business_email 
									)
							);
			echo json_encode( $response );
			exit;

		}

		return array( $uploads["path"] ."/$deal_id-redemption_codes.csv" );

	}

	public function send_subscriber_emails( $deal_id = '' ) {
		global $wpdb;

		if( isset( $_POST['action'] ) )
			check_ajax_referer( 'send_subscriber_emails' );

		$extra_where = '';

		if( ! empty( $_POST['post_id'] ) )
			$deal_id = $_POST['post_id'];

		if( ! empty( $deal_id ) )
			$extra_where = $wpdb->prepare( ' AND p.ID = %d', $deal_id );

		//Loop through all deals - extra_where var is for non-cron (AJAX, manual, or otherwise) use.

		$deals = $wpdb->get_results( "SELECT p.ID, p.post_title, p.post_parent, pm.meta_key, pm.meta_value
		FROM $wpdb->posts AS p
		LEFT JOIN $wpdb->postmeta AS pm ON p.ID = pm.post_id
		WHERE p.post_type =  'wpsc-product'
		AND p.post_status =  'publish'
		AND p.post_parent > 0
		AND pm.meta_key = '_wpec_dd_details'" . $extra_where, ARRAY_A );

		if( ! $deals )
			return;

		//Get deals that JUST started in the last two hours AND have not been emailed out.

		$new_deals = array();

		//We really only want the new deal logic if cron.  If deal id is set or AJAX, we can override.

		if( ! isset( $_POST['action'] ) && empty( $deal_id ) ) {

			foreach( $deals as $deal ) {

				$offer_email_sent = get_post_meta( $deal['ID'], '_offer_sent', true );

				$time = get_post_meta( $deal['ID'], '_wpec_dd_details', true );
				$start_time = $time['start'];
				$start_time = $start_time['yy'] . '-' . $start_time['mm'] . '-' . $start_time['dd'] . ' ' . $start_time['hh'] . ':' . $start_time['mn'] . ':00';

				$start_time = strtotime( $start_time );
				$now =  strtotime( date_i18n( 'F j, Y H:i:s' ) ) ;
				$two_hours = strtotime( '-2 hours', strtotime( date_i18n( 'F j, Y H:i:s' ) ) );

				//This should indicate deals that have started recently but not already been sent out
				if( $start_time < $now && $start_time > $two_hours && $offer_email_sent != 'sent' )
					$new_deals[] = array( 'ID' => $deal['ID'], 'post_parent' => $deal['post_parent'] );

			}

		} else {
			$new_deals = $deals;
		}

		if( empty( $new_deals ) )
			return;

		//Get list of Daily Deal Hunters    

		$daily_deal_hunters = get_users('role=wpec_dd_subscriber');

		if( empty( $daily_deal_hunters ) )
			return;

		//Creates multi-dimensional array of email addresses and location slugs
		$email_list = array();
	
		foreach( (array) $daily_deal_hunters as $user ) 
			$email_list[$user->user_email] = array_filter( (array) get_user_meta( $user->ID, 'location', true ) );
	
		$email_list = array_filter( $email_list );
	
		//Loop through Deals

		add_filter( 'wp_mail_content_type','wpec_dd_set_contenttype' );

	
		foreach( $new_deals as $deal ) {
	   
			$emails = wpec_get_email_content( $deal );

			$subject = $emails['deal_purchaser']['new_deal']['subject'];
			$message = $emails['deal_purchaser']['new_deal']['body'];

			//Loop through Daily deal hunters, emailing this to each of them, if the deal is in their location.
			$count = 0;
			foreach( $email_list as $email => $location_array ) {
				
				if( is_object_in_term( $deal['ID'], 'wpec-gd-location', $location_array ) ) {
					@wp_mail( $email, $subject, $message );
					$count++;
				}

			}

			//Mark each deal as _offer_sent as it's emailed out.
			update_post_meta( $deal['ID'], '_offer_sent', 'sent' );

		}
	
		remove_filter( 'wp_mail_content_type','wpec_dd_set_contenttype' );

		//If called via AJAX on product admin, we need to return some JSON goodness, alerting the admin that we sent the emails
		if( ! empty( $_POST['action'] ) ) {

			if( $count > 0 )
				$response = array(
						'data' => sprintf( _n( 'You just sent the deal email to %d subscriber!', 'You just sent the deal email to %d subscribers!', $count, 'wpec-group-deals' ), $count )
					);
			else
				$response = array(
						'errors' => '1',
						'data' => __( 'There are currently no subscribers in this location, so no email was sent.', 'wpec-group-deals' )
					);

			echo json_encode( $response );
			exit;
		
		}
	}

}

$GLOBALS['wpec_gd_ajax'] = new WPEC_GD_Ajax();

?>
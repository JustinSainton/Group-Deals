<?php
/*
 * WPEC-DD Admin functions
 */

/*
 * WPEC Vendor Management Metaboxes
 *
 */

function wpec_dd_vendor_details( $post ) {

	$wpec_dd_vendor_details = get_post_meta( $post->ID, '_wpec_dd_vendor_details', true );

	$address = isset( $wpec_dd_vendor_details["address"] ) ? $wpec_dd_vendor_details["address"] : '';
	$phone   = isset( $wpec_dd_vendor_details["phone"] )   ? $wpec_dd_vendor_details["phone"]   : '';
	$url     = isset( $wpec_dd_vendor_details["url"] )     ? $wpec_dd_vendor_details["url"]     : '';
	$email   = isset( $wpec_dd_vendor_details["email"] )   ? $wpec_dd_vendor_details["email"]   : '';
				
		?>
		<input type="hidden" name="wpec_dd_vendor_details_noncename" id="wpec_dd_vendor_details_noncename" value="<?php echo wp_create_nonce( 'wpec_dd_vendor_details' ); ?>" />
		<p><?php _e( 'Business Address', 'wpec-group-deals' ); ?>:</p>
		<textarea id="wpec_dd_vendor_details_address" name="wpec_dd_vendor_details[address]"><?php echo esc_textarea( $address ); ?></textarea><br />
		<p><?php _e( 'Business Phone #', 'wpec-group-deals' ); ?>:</p>
		<input type="text" id="wpec_dd_vendor_details_phone" name="wpec_dd_vendor_details[phone]" value="<?php esc_attr_e( $phone ); ?>" /><br />
		<p><?php _e( 'Business URL', 'wpec-group-deals' ); ?></p>
		<input type="text" id="wpec_dd_vendor_details_url" name="wpec_dd_vendor_details[url]" value="<?php esc_attr_e( $url ); ?>" /><br />
		<p><?php _e( 'Business Email', 'wpec-group-deals' ); ?></p>
		<input type="text" id="wpec_dd_vendor_details_email" name="wpec_dd_vendor_details[email]" value="<?php esc_attr_e( $email ); ?>" /><br />

<?php

}
function wpec_dd_vendor_deals( $post ) {
				$deals = get_posts( 'post_parent='.$post->ID.'&post_type=wpsc-product' );
		?>
		<p><?php _e( 'All daily deals for this vendor are listed below.  Clicking on the link will take you to the admin page for that daily deal.', 'wpec-group-deals' ); ?></p>
		<ol>
				<?php
						foreach( (array) $deals as $deal ) :
				?>
							<li><a href='post.php?post=<?php echo $deal->ID; ?>&action=edit'><?php echo $deal->post_title; ?></a></li>
				<?php
						 endforeach;
				?>
		</ol>

				<?php
}
function wpec_dd_vendor_financials( $post ) {
		$wpec_dd_vendor_financials = get_post_meta( $post->ID, '_wpec_dd_vendor_financials', true );

		$payment_method = isset( $wpec_dd_vendor_financials["payment_method"] )    ? $wpec_dd_vendor_financials["payment_method"]    : '';
		$paypal_email   = isset( $wpec_dd_vendor_financials["paypal_email"] )      ? $wpec_dd_vendor_financials["paypal_email"]      : '';
		$percentage     = isset( $wpec_dd_vendor_financials["profit_percentage"] ) ? $wpec_dd_vendor_financials["profit_percentage"] : '';

?>
		<input type="hidden" name="wpec_dd_vendor_financials_noncename" id="wpec_dd_vendor_financials_noncename" value="<?php echo wp_create_nonce( 'wpec_dd_vendor_financials' ); ?>" />
		<p><?php _e( 'Fill out all financial details for your vendor here. Fill this out as completely and accurately as possible.', 'wpec-group-deals' ); ?></p>

		<p><?php _e( 'Preferred method of payment:', 'wpec-group-deals' ); ?></p>
		<label for="wpec_dd_vendor_payment_paypal"><input id="wpec_dd_vendor_payment_paypal" type="radio" name="wpec_dd_vendor_financials[payment_method]" value="Paypal" <?php checked( $payment_method, 'Paypal' ); ?> /> <?php _e( 'Paypal', 'wpec-group-deals' ); ?></label><br />
		<!--<label for="wpec_dd_vendor_payment_check"><input id="wpec_dd_vendor_payment_check" type="radio" name="wpec_dd_vendor_financials[payment_method]" value="Check" <?php checked( $payment_method, 'Check' ); ?> /> <?php _e( 'Check', 'wpec-group-deals' ); ?></label>-->

		<p><?php _e( 'Paypal email address, if applicable:', 'wpec-group-deals' ); ?></p>
		<!--<p><?php _e( 'Paypal email address, if applicable:', 'wpec-group-deals' ); ?></p>-->
		<input type="text" id="wpec_dd_vendor_financials_paypal_email" name="wpec_dd_vendor_financials[paypal_email]" value="<?php esc_attr_e( $paypal_email ); ?>" /><br />

		<p><?php _e( 'You can change the amount of profit sharing you do with each vendor.  Many sites do 50%/50%, but you can do anything you want. Enter the percentage the <strong>vendor</strong> should receive, just the number, without the percentage sign.', 'wpec-group-deals' ); ?></p>
		<input size="3" type="text" name="wpec_dd_vendor_financials[profit_percentage]" id="wpec_dd_vendor_financials_percentage" value="<?php esc_attr_e( $percentage ); ?>" />
<?php
}

// Add to admin_init function
add_action( 'save_post', 'wpec_dd_save_vendor_details' );

function wpec_dd_save_vendor_details( $post_id ) {
		global $post;
	
	if( ! isset( $_POST['wpec_dd_vendor_details_noncename'] ) || ! isset( $_POST['wpec_dd_vendor_financials_noncename'] ) )
		return;
	
		// verify this came from the our screen and with proper authorization.
	if ( ! wp_verify_nonce( $_POST['wpec_dd_vendor_details_noncename'], 'wpec_dd_vendor_details' ) || ! wp_verify_nonce( $_POST['wpec_dd_vendor_financials_noncename'], 'wpec_dd_vendor_financials' ) )
		return $post_id;

		// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want to do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
				return $post_id;

		// Check permissions
		if ( !current_user_can( 'edit_post', $post_id ) )
				return $post_id;

		if( $post->post_type != 'wpec-dd-vendor' )
				return $post_id;

		// OK, we're authenticated: we need to find and save the data
		$post = get_post( $post_id );
		
		$sanitized_vendor_details = array();
		$sanitized_vendor_financials = array();

		//Sanitize each element of vendor details
		$sanitized_vendor_details["phone"] = esc_html( $_POST["wpec_dd_vendor_details"]["phone"] );
		$sanitized_vendor_details["url"] = esc_url( $_POST["wpec_dd_vendor_details"]["url"] );
		$sanitized_vendor_details["address"] = esc_html( $_POST["wpec_dd_vendor_details"]["address"] );
		$sanitized_vendor_details["email"] = esc_html( $_POST["wpec_dd_vendor_details"]["email"] );

		//Sanitize each element of vendor financials

		$sanitized_vendor_financials["payment_method"] = esc_html( $_POST["wpec_dd_vendor_financials"]["payment_method"] );
		$sanitized_vendor_financials["paypal_email"] = esc_html( $_POST["wpec_dd_vendor_financials"]["paypal_email"] );
		$sanitized_vendor_financials["profit_percentage"] = absint( $_POST["wpec_dd_vendor_financials"]["profit_percentage"] );

		update_post_meta( $post_id, '_wpec_dd_vendor_details', $sanitized_vendor_details );
		update_post_meta( $post_id, '_wpec_dd_vendor_financials', $sanitized_vendor_financials );

}

/*
 * End WPEC Vendor Management Metaboxes
 *
 * WPEC Daily Deal metaboxes and functions
 *
 */

add_filter( 'gettext','wpec_filter_price_text', 12, 3 );
add_filter( 'gettext','wpec_filter_sales_price_text', 12, 3 );

function wpec_filter_price_text( $translation, $text, $domain ) {
	if( ! is_admin() )
		return;

		if( 'Price' == $text ) {
				$translations = &get_translations_for_domain( $domain );
				return $translations->translate( 'Advertised Price' ) ;
		}

		return $translation;
}
function wpec_filter_sales_price_text( $translation, $text, $domain ) {

	if( ! is_admin() )
		return;

	if( 'Sale Price' == $text ) {
		$translations = &get_translations_for_domain( $domain );
		return $translations->translate( 'Daily Deal Price' ) ;
	}

	return $translation;
}
function wpec_dd_details( $post ) {
	global $wp_locale;
				$wpec_dd_details = get_post_meta( $post->ID, '_wpec_dd_details', true );

				if ( ! $wpec_dd_details ) {
						$wpec_dd_details = array(
								"tipping_point" => "",
								'max_qty' => '2',
								"expdate" => array(
										"mm" => date_i18n('m'),
										"dd" => date_i18n('j'),
										'yy' => date_i18n('Y')
								),
								"start" => array(
										"mm" => date_i18n('m'),
										"dd" => date_i18n('j'),
										"yy" => date_i18n('Y'),
										"hh" => date_i18n('h'),
										"mn" => date_i18n('i'),
										"A" => date_i18n('A'),
								),
								"stopw" => "0",
								"stopd" => "0",
								"stoph" => "0",
								"stopn" => 0
						);
				}
		?>
		
						<input type="hidden" name="wpec_dd_details_noncename" id="wpec_dd_details_noncename" value="<?php echo wp_create_nonce( 'wpec_dd_details' );?>" />

						<p><?php _e( 'How many purchases until the deal is on', 'wpec-group-deals' ); ?>?</p>
						<input type="text" size="5" id="wpec_dd_details_tipping_point" name="wpec_dd_details[tipping_point]" value="<?php echo $wpec_dd_details["tipping_point"]; ?>" /><br />

						<p><?php _e( 'What is the maximum quantity per purchase', 'wpec-group-deals' ); ?>?</p>
						<select id="wpec_dd_details_tipping_point" name="wpec_dd_details[max_qty]" />
						<?php
							$i = 1;
							while( $i < 10 ) :
								echo '<option value="' . $i . '" ' . selected( $wpec_dd_details["max_qty"], $i, false ) . '>' . $i . '</option>';
								$i++;
							endwhile;
						?>
						</select><br />
						
						<p><?php _e( 'When is the deal active', 'wpec-group-deals' );?>?<br /><span class="howto"></span></p>
							 <div class="timestamp-wrap">
									 <select id="wpec_dd_details_start_mm" name="wpec_dd_details[start][mm]" tabindex="4">
											<option value="01"<?php selected( '01', $wpec_dd_details["start"]["mm"] ); ?>>Jan</option>
											<option value="02"<?php selected( '02', $wpec_dd_details["start"]["mm"] ); ?>>Feb</option>
											<option value="03"<?php selected( '03', $wpec_dd_details["start"]["mm"] ); ?>>Mar</option>
											<option value="04"<?php selected( '04', $wpec_dd_details["start"]["mm"] ); ?>>Apr</option>
											<option value="05"<?php selected( '05', $wpec_dd_details["start"]["mm"] ); ?>>May</option>
											<option value="06"<?php selected( '06', $wpec_dd_details["start"]["mm"] ); ?>>Jun</option>
											<option value="07"<?php selected( '07', $wpec_dd_details["start"]["mm"] ); ?>>Jul</option>
											<option value="08"<?php selected( '08', $wpec_dd_details["start"]["mm"] ); ?>>Aug</option>
											<option value="09"<?php selected( '09', $wpec_dd_details["start"]["mm"] ); ?>>Sep</option>
											<option value="10"<?php selected( '10', $wpec_dd_details["start"]["mm"] ); ?>>Oct</option>
											<option value="11"<?php selected( '11', $wpec_dd_details["start"]["mm"] ); ?>>Nov</option>
											<option value="12"<?php selected( '12', $wpec_dd_details["start"]["mm"] ); ?>>Dec</option>
									 </select>
									 
									 <input type="text" id="jj" name="wpec_dd_details[start][dd]" value="<?php echo $wpec_dd_details["start"]["dd"]; ?>" size="26" maxlength="2" tabindex="4" autocomplete="off" />,
									 <input type="text" id="aa" name="wpec_dd_details[start][yy]" value="<?php echo $wpec_dd_details["start"]["yy"]; ?>" size="4" maxlength="4" tabindex="4" autocomplete="off" /> @
									 <input type="text" id="hh" name="wpec_dd_details[start][hh]" value="<?php echo $wpec_dd_details["start"]["hh"]; ?>" size="2" maxlength="2" tabindex="4" autocomplete="off" /> :
									 <input type="text" id="mn" name="wpec_dd_details[start][mn]" value="<?php echo $wpec_dd_details["start"]["mn"]; ?>" size="2" maxlength="2" tabindex="4" autocomplete="off" />
									 <select name='wpec_dd_details[start][A]'>
									 	<option value='<?php echo $wp_locale->meridiem['AM']; ?>'<?php selected( $wp_locale->meridiem['AM'], $wpec_dd_details["start"]["A"] ); ?>><?php echo $wp_locale->meridiem['AM']; ?></option>
									 	<option value='<?php echo $wp_locale->meridiem['PM']; ?>'<?php selected( $wp_locale->meridiem['PM'], $wpec_dd_details["start"]["A"] ); ?>><?php echo $wp_locale->meridiem['PM']; ?></option>
									 </select>


									 <p><?php _e( 'How long until the deal expires', 'wpec-group-deals' );?>?</p>
										<div id="exptime">
									 
									 <input type="text" id="jj" name="wpec_dd_details[stopw]" value="<?php echo $wpec_dd_details["stopw"]; ?>" size="26" maxlength="2" tabindex="4" autocomplete="off" /> weeks
									 <input type="text" id="jj" name="wpec_dd_details[stopd]" value="<?php echo $wpec_dd_details["stopd"]; ?>" size="26" maxlength="2" tabindex="4" autocomplete="off" /> days
									 <input type="text" id="jj" name="wpec_dd_details[stoph]" value="<?php echo $wpec_dd_details["stoph"]; ?>" size="26" maxlength="2" tabindex="4" autocomplete="off" /> hours
									
									 </div>
					 <div id="exptime_options">
						 <p><em><?php _e( 'Because the deal never expires, you will need to manually email the vendor the current list of customers and redemption codes.', 'wpec-group-deals' ); ?></em></p>
						 <p><input class="button secondary" type="button" name="email_vendor_list" value="<?php _e( 'Email Vendor List', 'wpec-group-deals' ); ?>" /></p>

						<?php echo wp_nonce_field( 'send_vendor_emails', '_send_vendor_email_nonce', true, false ); ?>
					 </div>
									
					<p><input type="checkbox" id="forever" name="wpec_dd_details[stopn]" value="1" <?php checked( $wpec_dd_details["stopn"], '1' ) ?> /> <?php _e( 'Deal never expires', 'wpec-group-deals' ); ?></p>
					<p><?php _e( 'What is the redemption expiration date? Unlike the value above, this is the date the vendor actually stops honoring the redemption code.', 'wpec-group-deals' );?></p>
					<select id="wpec_dd_details_mm" name="wpec_dd_details[expdate][mm]" tabindex="4">
											<option value="01"<?php selected( '01', $wpec_dd_details["expdate"]["mm"] ); ?>>Jan</option>
											<option value="02"<?php selected( '02', $wpec_dd_details["expdate"]["mm"] ); ?>>Feb</option>
											<option value="03"<?php selected( '03', $wpec_dd_details["expdate"]["mm"] ); ?>>Mar</option>
											<option value="04"<?php selected( '04', $wpec_dd_details["expdate"]["mm"] ); ?>>Apr</option>
											<option value="05"<?php selected( '05', $wpec_dd_details["expdate"]["mm"] ); ?>>May</option>
											<option value="06"<?php selected( '06', $wpec_dd_details["expdate"]["mm"] ); ?>>Jun</option>
											<option value="07"<?php selected( '07', $wpec_dd_details["expdate"]["mm"] ); ?>>Jul</option>
											<option value="08"<?php selected( '08', $wpec_dd_details["expdate"]["mm"] ); ?>>Aug</option>
											<option value="09"<?php selected( '09', $wpec_dd_details["expdate"]["mm"] ); ?>>Sep</option>
											<option value="10"<?php selected( '10', $wpec_dd_details["expdate"]["mm"] ); ?>>Oct</option>
											<option value="11"<?php selected( '11', $wpec_dd_details["expdate"]["mm"] ); ?>>Nov</option>
											<option value="12"<?php selected( '12', $wpec_dd_details["expdate"]["mm"] ); ?>>Dec</option>
									 </select>

									 <input type="text" id="jj" name="wpec_dd_details[expdate][dd]" value="<?php echo $wpec_dd_details["expdate"]["dd"]; ?>" size="26" maxlength="2" tabindex="4" autocomplete="off" />,
									 <input type="text" id="aa" name="wpec_dd_details[expdate][yy]" value="<?php echo $wpec_dd_details["expdate"]["yy"]; ?>" size="4" maxlength="4" tabindex="4" autocomplete="off" />

							 </div>
			<div id="email_deal_to_subscribers">
				<?php
					if( 'sent' == get_post_meta( $post->ID, '_offer_sent', true ) ) :
				?>
				<p><?php _e( 'The email for this deal has <strong>already</strong> been sent out to subscribers.', 'wpec-group-deals' ); ?></p>
				<p><input type="button" class="button secondary" value='<?php _ex( 'Send the email again?', 'For sending new deal email to subscribers after already sent', 'wpec-group-deals' ); ?>' name='send_new_deal_email' /></p>
				<?php
					else:
				?>
				<p><?php _e( 'The email for this deal <strong>has not</strong> been sent out to subscribers.', 'wpec-group-deals' ); ?></p>
				<p><input type="button" class="button secondary" value='<?php _ex( 'Send the email?', 'For sending new deal email to subscribers if it has not been sent', 'wpec-group-deals' ); ?>' name='send_new_deal_email' /></p>
				<?php	
					endif;
				?>
				<?php echo wp_nonce_field( 'send_subscriber_emails', '_send_subscriber_email_nonce', true, false ); ?>
			</div>

<?php
}
function wpec_dd_business_details( $post ) {
		$wpec_dd_business = get_post_meta( $post->ID, '_wpec_dd_business', true );
		$google_map = get_post_meta( $post->ID, '_show_google_map', true );

?>
										<p><?php _e( 'Select a business', 'wpec-group-deals' ); ?>:</p>
												<select name="business_id" id="business_id">
														<?php
																 $businesses = get_posts( 'post_type=wpec-dd-vendor' );
																 if ( empty( $businesses ) )
																		 echo "<option value=''>" . __( '--No Vendors Created--', 'wpec-group-deals' ) . "</option>";
																 else
																		 echo "<option value=''>" . __( '--Select One--', 'wpec-group-deals' ) . "</option>";
																 foreach( (array) $businesses as $business ) :
														?>
															 <option value="<?php echo $business->ID; ?>" <?php selected( $wpec_dd_business, $business->ID ); ?>><?php echo $business->post_title; ?></option>
														<?php endforeach; ?>
												</select>
										<p><input value="N" <?php checked( $google_map, 'N' ) ?> type="checkbox" name="show_google_map" id="show_google_map" /> <?php _e( 'Show Google Map?', 'wpec-group-deals' ); ?></p>
<?php
}
function wpec_dd_fine_print( $post ) {
		$wpec_fine_print =  get_post_meta( $post->ID, '_wpec_dd_fine_print', true );

?>
			
<p><?php esc_html_e( 'Enter fine print here. <strong> and <em> tags are allowed.', 'wpec-group-deals' ); ?>:</p>
<textarea name="wpec_dd_fine_print" id="wpec_dd_fine_print"><?php echo $wpec_fine_print; ?></textarea>

<?php
}
function wpec_dd_vendor_expiration_date( $post_id = 0, $format = 'm J, Y' ) {

		$exp_date = get_post_meta( $post_id, '_wpec_dd_details', true );
		$exp_date = $exp_date['expdate'];

		$exp_date = $expdate['mm'] . ' ' . $expdate['dd'] . ', ' . $expdate['yy'];

		$timestamp = strtotime( $exp_date );

		return date( $format, $timestamp );

}
function wpec_dd_highlights( $post ) {
		$wpec_highlights = get_post_meta( $post->ID, '_wpec_dd_highlights', true );

?>
<p><?php _e( 'Enter highlights here.  One per line, no HTML.', 'wpec-group-deals' ); ?>:</p>
<textarea name="wpec_dd_highlights" id="wpec_dd_highlights"><?php echo esc_textarea( $wpec_highlights ); ?></textarea>

<?php
}

/**
 * Description
 * @param int $post_id 
 * @param object $post
 * @todo Look at refactoring to be less hacky, otherwise, at least add proper handling for all statuses. 
 * @return void
 */
function save_wpec_dd_data( $post_id, $post ) {
		global $allowed_tags;
		
		if( ! isset( $_POST['wpec_dd_details_noncename'] ) )
			return; 
		
	// verify this came from the our screen and with proper authorization.
		if ( ! wp_verify_nonce( $_POST['wpec_dd_details_noncename'], 'wpec_dd_details' ) )
			return;
	
	// verify if this is an auto save routine. If it is our form has not been submitted, so we don't want to do anything
				if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
						return;
 
	// Check permissions
				if ( ! current_user_can( 'edit_post', $post_id ) )
						return;

				update_post_meta( $post_id, '_wpec_dd_details', $_POST['wpec_dd_details'] );
				update_post_meta( $post_id, '_wpec_dd_business', (int)$_POST["business_id"] );
				update_post_meta( $post_id, '_wpec_dd_highlights', $_POST['wpec_dd_highlights'] );
				update_post_meta( $post_id, '_wpec_dd_fine_print', $_POST['wpec_dd_fine_print'] );
				update_post_meta( $post_id, '_show_google_map', $_POST['show_google_map'] );

				$sendback = wp_get_referer();

				if ( strpos( $sendback, 'post-new.php' ) !== false && 'publish' == $post->post_status )
						$sendback = admin_url( "post.php?post=$post_id&action=edit&message=6" );
				else if ( 'future' == $post->post_status )
						$sendback = admin_url( "post.php?post=$post_id&action=edit&message=9" );
				else
						$sendback = add_query_arg( 'message', '1', $sendback );
				wp_redirect( $sendback );
				exit;

}
function wpec_daily_deal_metaboxes() {
		global $post;

		if( is_object( $post ) && $post->post_status != "inherit" ) {
				add_meta_box( 'wpec_dd_details', __( 'Group Deal Details', 'wpec-group-deals' ), 'wpec_dd_details', 'wpsc-product', 'side', 'low' );
				add_meta_box( 'wpec_dd_business_details', __( 'Business Details', 'wpec-group-deals' ), 'wpec_dd_business_details', 'wpsc-product', 'side', 'low' );
				add_meta_box( 'wpec_dd_fine_print', __( 'Fine Print', 'wpec-group-deals' ), 'wpec_dd_fine_print', 'wpsc-product', 'side', 'low' );
				add_meta_box( 'wpec_dd_highlights', __( 'Highlights', 'wpec-group-deals' ), 'wpec_dd_highlights', 'wpsc-product', 'side', 'low' );
		}

	}
function wpec_dd_pre_update( $data, $postarr ) {

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return $data;
	
	if( 'wpsc-product' != $data['post_type'] )
		return $data;

	if( isset( $postarr["business_id"] ) && $postarr["business_id"] > 0 )
		$data["post_parent"] = $postarr["business_id"];

	$data['post_status'] = $postarr['post_status'];

	if( $data["post_status"] == "trash" && isset( $_REQUEST['untrash'] ) )
		$data["post_status"] = "publish";

	if( isset( $postarr['post_date_gmt'] ) && time() < strtotime( $postarr['post_date_gmt'] . ' +0000' ) ) 
		$data["post_status"] = "future";

	if( isset( $_REQUEST['trashed'] ) || 'trash' == $postarr['post_status'] )
		$data["post_status"] = "trash";

	//die( '<h2>$data array</h2><pre>' . print_r( $data, 1 ) . '</pre><h2>$postarr array</h2><pre>' . print_r( $postarr, 1 ) . '</pre>' );

	return $data;
}

add_action( 'admin_head', 'wpec_daily_deal_metaboxes' );
add_action( 'save_post', 'save_wpec_dd_data', 10, 2 );
add_filter( 'wp_insert_post_data','wpec_dd_pre_update', 100, 2 );

function wpec_dd_styles(){
		wp_register_style( 'wpec_dd_styles', WPEC_DD_URL . '/wpec-gd-admin/wpec_dd.css' );
		wp_enqueue_style( 'wpec_dd_styles' );
}

add_action( 'admin_print_styles', 'wpec_dd_styles' );

/*
 * First run at some admin-ajax action.  Gonna populate the Business area based on dropdown.
 * @todo only on wpsc-product
 */
add_action('admin_head', 'wpec_dd_get_business_javascript');

function wpec_dd_get_business_javascript() {    

?>
<script type="text/javascript" >
jQuery(document).ready(function($) {

		<?php if ( isset( $_REQUEST['post_status'] ) && 'trash' != $_REQUEST['post_status'] ) : ?>

	 $('td.post-title').each(function(){

			 $('strong', this).replaceWith($('a.row-title', this));

		
	 });

	 <?php endif; ?>

		if( $('select#business_id').length ) load_wpec_business();
		
				$('select#business_id').change(function(){

						load_wpec_business();
						
				});
	 
		function load_wpec_business(){

					 biz_id = $('select#business_id').val();

	var data = {
		action: 'wpec_show_business',
		business_id:  biz_id
	};

	jQuery.post(ajaxurl, data, function(response) {
						if( $('div#business_info').length ) {
								$('div#business_info').empty();
						}

						$('<div id="business_info" />').insertAfter('#wpec_dd_business_details div.inside select');

						$('<p>Name: ' + response.name + '</p>').appendTo('#business_info');
						$('<p>Address: <br /><em>' + response.address + '</em></p>').appendTo('#business_info');
						$('<p>Phone #: ' + response.phone + '</p>').appendTo('#business_info');
						$('<p>URL: <a href="' + response.url + '">'+ response.url +'</p>').appendTo('#business_info');
						$('<p><a href="post.php?post='+ response.id +'&action=edit">Edit this business</a>').appendTo('#business_info');

	}, "json");
		}
});
</script>
<?php
}
add_action('wp_ajax_wpec_show_business', 'wpec_business_callback');

function wpec_business_callback() {
		
		global $wpdb, $wp_query; 

		$business_id = (int)$_POST['business_id'];
	
		$business = get_posts( 'post_type=wpec-dd-vendor&include='.$business_id );

		$business_meta = get_post_meta( $business_id, '_wpec_dd_vendor_details', true );

		foreach ( $business as $business ) {

				$response = array();

				$response["name"] = $business->post_title;
				$response["address"] = nl2br($business_meta["address"]);
				$response["phone"] = $business_meta["phone"];
				$response["url"] = $business_meta["url"];
				$response["id"] = $business->ID;

				echo json_encode( $response );
		}

		die();
}
function wpec_post_parent_in( $where ) {
			global $wpdb, $current_screen;

		// verify if this is an auto save routine. If it is our form has not been submitted, so we don't want to do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
				return $where;

		 if( $current_screen->id != 'edit-wpsc-product' )
				 return $where;

		$where = str_replace( "AND $wpdb->posts.post_parent = 0 ", "", $where );
		$vendors = $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE post_type = 'wpec-dd-vendor'" );

		if( !is_array( $vendors ) )
				return $where;

		 foreach( $vendors as $vendor )
				 $vendor_ids[] = $vendor->ID;

		if( is_array( $vendor_ids ) ) {
				$ids = implode( ', ',$vendor_ids );
				$where .= " AND post_parent IN ($ids, 0)";
		}
		
		return $where;
	}
function wpec_dd_columns( $columns ){
		
		$columns['dd_count'] = __( 'Daily Deal Count', 'wpec-group-deals' );
		
		return $columns;
}
function wpec_dd_daily_count_column( $column_name, $product_id ) {

		global $wpdb;

		if( 'dd_count' == $column_name ) {
			$deals = $wpdb->get_var("SELECT COUNT(id) FROM $wpdb->posts WHERE post_type = 'wpsc-product' AND post_parent = $product_id");

			echo $deals." deal(s)";
	}
}

add_filter( 'posts_where', 'wpec_post_parent_in' );
add_action( 'manage_wpec-dd-vendor_custom_column', 'wpec_dd_daily_count_column', 10, 2 );
add_filter( 'manage_edit-wpec-dd-vendor_sortable_columns', 'wpec_dd_columns' );
add_filter( 'manage_edit-wpec-dd-vendor_columns', 'wpec_dd_columns' );

function price_column_orderby($orderby, $wp_query) {
	global $wpdb;

	$wp_query->query = wp_parse_args($wp_query->query);

	if ( isset( $wp_query->query['orderby'] ) && 'dd_count' == $wp_query->query['orderby'] )
		$orderby = "(SELECT COUNT(id) FROM $wpdb->posts WHERE post_type = 'wpsc-product') " . $wp_query->get('order');

	return $orderby;
}
add_filter( 'posts_orderby', 'price_column_orderby', 10, 2 );


/**
 * Flushes rewrite rules on Location deletion, should fix any potential 404 issues that would occur otherwise.
 */

function wpec_gd_flush_permalinks() {
	flush_rewrite_rules( false );
}

add_action( 'delete_wpec-gd-location', 'wpec_gd_flush_permalinks' );

/*
 * WPEC Daily Deal Purchases metaboxes
 */

function wpec_dd_customer() {
		global $post, $wpdb, $purchlogitem;
		
		$session_id = get_post_meta( $post->ID, '_session_id', true);
		$purch_log_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM ".WPSC_TABLE_PURCHASE_LOGS." WHERE sessionid = %s", $session_id ) );
		$purchlogitem = new wpsc_purchaselogs_items( $purch_log_id );
	
		?>
							 <p><strong><?php _e( 'Purchase Log Date:', 'wpec-group-deals' ); ?> </strong><?php echo date( get_option( 'date_format' ), strtotime( wpsc_purchaselog_details_date() ) ); ?> </p>
							 <p><strong><?php _e( 'Buyers Name:', 'wpec-group-deals' ); ?> </strong><?php echo wpsc_display_purchlog_buyers_name(); ?></p>
							 <p><strong><?php _e( 'Address:', 'wpec-group-deals' ); ?> </strong><?php echo wpsc_display_purchlog_buyers_address(); ?></p>
							 <p><strong><?php _e( 'Phone:', 'wpec-group-deals' ); ?> </strong><?php echo wpsc_display_purchlog_buyers_phone(); ?></p>
							 <p><strong><?php _e( 'Email:', 'wpec-group-deals' ); ?> </strong><a href="mailto:<?php echo wpsc_display_purchlog_buyers_email(); ?>?subject=<?php _e( 'Message From', 'wpec-group-deals' ); ?> '<?php echo get_option('siteurl'); ?>'"><?php echo wpsc_display_purchlog_buyers_email(); ?></a></p>
<?php
		}
function wpec_dd_business() {
		global $post, $wpdb, $product_id, $business;

		$product_id = $wpdb->get_var( "SELECT post_parent FROM $wpdb->posts WHERE ID = $post->ID" );
		$business_id = $wpdb->get_var( "SELECT post_parent FROM $wpdb->posts WHERE ID = $product_id" );
		$business = get_post( $business_id );
		$business_meta = get_post_meta( $business_id, "_wpec_dd_vendor_details", true );
	?>
	 <p><strong><?php echo $business->post_title; ?></strong></p>
	 <p><?php echo $business->post_content; ?></p>
	 <p><strong><?php _e( 'Address', 'wpec-group-deals' ); ?>:</strong><br /><em><?php echo nl2br( $business_meta["address"] ); ?></em></p>
	 <p><strong><?php _e( 'Phone', 'wpec-group-deals' ); ?>:</strong><br /><em><?php echo nl2br( $business_meta["phone"] ); ?></em></p>
	 <p><strong><?php _e( 'URL', 'wpec-group-deals' ); ?>:</strong><br /><em><a href="<?php echo $business_meta["url"]; ?>"><?php echo nl2br( $business_meta["url"] ); ?></a></em></p>
	 <?php
}
function wpec_dd_purchase_details() {
		global $post, $wpdb, $product_id, $business;

		//Shows product fine print / highlights / redemption code

		$fine_print = nl2br( strip_tags( get_post_meta( $product_id, "_wpec_dd_highlights", true ) ) );
		$highlights = nl2br( strip_tags( get_post_meta( $product_id, "_wpec_dd_fine_print", true ) ) );
		$redemption_code = wpec_dd_redemption_code( $post->ID );
		$product = get_post($product_id);

		?>
	 <p><strong><?php _e( 'Product link', 'wpec-group-deals' ); ?>: </strong> <a href="post.php?post=<?php echo $product_id; ?>&action=edit"><?php echo $product->post_title; ?></a></p>
	 <p><strong><?php _e( 'Customer\'s redemption code', 'wpec-group-deals' ); ?>:</strong> <?php echo $redemption_code; ?></p>
	 <p><strong><?php _e( 'Product Fine Print', 'wpec-group-deals' ); ?>:</strong><br /><?php echo $fine_print; ?></p>
	 <p><strong><?php _e( 'Product Highlights', 'wpec-group-deals' ); ?>:</strong><br /><?php echo $highlights; ?></p>
	 <?php
}

/*
 * End WPEC Daily Deal Purchases metaboxes
 */

function wpec_dd_array_walk( &$v,$k ) {

		$v = $k."=".urlencode( (string)$v ) ;

}
function wpec_dd_push_product_gs( $post_id ) {
		global $authorization;
		
		if( !wpec_gd_authenticate() )
				return;
	 
		//Product info
		$product_info = get_post( $post_id, ARRAY_A );

		//Image info
		$image  = get_posts( "post_type=attachment&post_parent=".$post_id );
		$image_id = $image[0]->ID;
		$image_info = (array)wp_get_attachment_image_src( $image_id, 'wpec_dd_home_image' );

		//Price Info
		$price = array();
		$price["price"] = get_post_meta( $post_id, '_wpsc_price', true );
		$price["deal"] = get_post_meta( $post_id, '_wpsc_special_price', true );
		$price["savings"]= get_wpec_dd_price( $post_id, 'savings' );
		$price["discount"] = get_wpec_dd_price( $post_id, 'discount' );

		//Start date
		$end_date = "end_date=".urlencode( get_wpec_ajax_countdown( $post_id, 'ajax' ) );
		
		array_walk( $price, 'wpec_dd_array_walk' );
		array_walk( $image_info, 'wpec_dd_array_walk' );
		array_walk( $product_info, 'wpec_dd_array_walk' );

		$product_info = implode( "&", $product_info );
		$image_info = implode( "&", $image_info );
		$price_info = implode( "&", $price );

		$url = "http://api.groupdealsplugin.com/push/";
		$url_string = $product_info."&".$image_info."&".$price_info."&".$end_date."&domain=".$authorization["domain"]."&api_id=".$authorization['api_id'];

		if( get_post_meta( $post_id, '_remote_id', true ) ) {
				$remote_id = get_post_meta( $post_id, '_remote_id', true );
				$url_string .= '&remote_id='.$remote_id;
		}

		$body = array(
					 "wpec_gd_url_string" => $url_string
				);
		
		$args["body"] = $body;
		
		$push = wp_remote_post( $url, $args );

		if( !is_wp_error( $push ) )
				$object = json_decode( $push["body"] );
		
		if( isset( $object->remote_id ) )
				update_post_meta( $post_id, '_remote_id', $object->remote_id );
	 
}

add_action( 'wpsc_edit_product', 'wpec_dd_push_product_gs' );
add_action( 'admin_notices', 'wpec_gd_location_notice' );

function wpec_gd_location_notice() {
		global $current_screen;

		if( 'edit-wpec-gd-location' != $current_screen->id )
				return;

		if( get_option( 'hide_wpec_gd_location_notice' ) )
				return;

		echo "<div id='update' class='updated fade'><p>" . __( "Welcome to the Locations page! Locations are a special kind of taxonomy.  They are divided into regions - generally these regions are states or provinces.  After creating a state or province (which you do by simply typing in the name of the region and then clicking 'Add New Location'), you should add cities to that region.  You can do that by typing the name of the City in the 'Name' field, and choosing the region you created from the 'Parent' drop-down.", 'wpec-group-deals' ) . "</p><p><a href='" . add_query_arg( 'hide_link', '1' ) . "'>" . _x( 'Click here to hide this notice forever', 'Location taxonomy nag', 'wpec-group-deals' ) . "</a>.</p></div>";

		if( isset( $_REQUEST['hide_link'] ) )
				update_option( 'hide_wpec_gd_location_notice', '1' );

}

?>
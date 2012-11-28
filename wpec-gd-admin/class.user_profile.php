<?php
	
	/**
	 * Returns user_id => email address array of all referrals from a user.
	 * @internal Returns all users, not only active users (those who have made a purchase).
	 * 
	 * @param int|string $user_id 
	 * @return array
	 */
	
	function wpec_gd_show_user_referrals( $user_id ) {
		global $wpec_gd_user_profile;

		return $wpec_gd_user_profile->print_referrals( $user_id );
	}
	
	/**
	 * Returns number of active referrals from a user.  These are users who have made a purchase.
	 * @param int|string $user_id 
	 * @return string Number of active referrals.
	 */

	function wpec_gd_show_active_referrals( $user_id ) {
		global $wpec_gd_user_profile;

		return $wpec_gd_user_profile->number_of_active_referrals( $user_id );
	}

	/**
	 * Returns, in appropriate currency, the amount of available credit a user has.
	 * @param int|string $user_id 
	 * @return currency
	 */
	function wpec_gd_user_credits_available( $user_id = '' ) {
		global $wpec_gd_user_profile;

		return $wpec_gd_user_profile->get_available_credit( $user_id );
		
	}

	/**
	 * Returns, in appropriate currency, the amount of available credit a user has.
	 * @param int|string $float - whatever the format the price is in.
	 * @return currency
	 */
	function wpec_gd_unformat_currency( $float ) {
		global $wpec_gd_user_profile;

		return $wpec_gd_user_profile->unformat( $float );
		
	}

	/**
	 * Encapsulates all of the user profile functions for Group Deals.  
	 * 
	 */

	class WPEC_GD_User_Profile {


		/**
		 * Returns referral credit amount, as set in options area.
		 * 
		 * @type float
		 * @var referral_credit
		 */

		var $referral_credit;

		public function __construct() {

			$this->set_referral_credit();

			add_action( 'show_user_profile', array( &$this, 'user_profile_meta' ) );
			add_action( 'edit_user_profile', array( &$this, 'user_profile_meta' ) );
			add_action( 'personal_options_update', array( &$this, 'update_custom_credit_meta' ) );
			add_action( 'edit_user_profile_update', array( &$this, 'update_custom_credit_meta' ) );
			add_action( 'wpsc_additional_user_profile_links', array( &$this, 'add_referral_link' ), 15 );

		}
		
		/**
		 * Sets referral credit variable
		 * @return float referral_credit
		 */

		public function set_referral_credit() {
			
		    $referral_credit = get_option( 'wpec_gd_options_array' );
		    $referral_credit = (float) $referral_credit['referral_credit'];
		    $this->referral_credit = $referral_credit;
		}

		/**
		 * Displays subscribed locations, who referred them, who they've referred, and their referral credits.
		 * Note: Limitation by design, referral amount can be increased only, not decreased
		 * 
		 * @param object $user 
		 * @return html
		 */

		public function user_profile_meta( $user ) {

		  if( 'wpec_dd_subscriber' != $user->roles[0] )
		    return;

		    $locations = array_map( array( $this, 'ucspace' ), get_user_meta( $user->ID, 'location', true ) ); 

			$referrer = (int) get_user_meta( $user->ID, 'referred_from', true );
			$referrer = $referrer ? get_userdata( $referrer ) : __( 'No one', 'wpec-group-deals' );
			
			if( is_object( $referrer ) )
				$referrer = $referrer->user_email;
			
			//We'll keep track of the standard amount, the custom deviations, and what the user has used.

			$available_credit = $this->get_available_credit( $user->ID );

			$used = (float) get_user_meta( $user->ID, '_credit_used', true );

		?>
		 <h3><?php _e( 'Group Deals Info', 'wpec-group-deals' ); ?></h3>
		  <table class="form-table">
		    <tr>
		      <th>
		        <label for="locations">
		        	<?php _e( 'Emails for the following location(s)', 'wpec-group-deals' ); ?>
				</label>
		      </th>
		      <td>
		        <?php echo esc_html( implode( ', ', $locations ) ); ?>
		      </td>
		    </tr>
		    <tr>
		    	<th>
		    		<label><?php _e( 'Referred by', 'wpec-group-deals' ); ?></label>
		    	</th>
		    	<td>
	    			<?php
	    				echo esc_html( $referrer );
	    			?>
		    	</td>
		    </tr>
		    <tr>
		    	<th>
		    		<label><?php _e( 'Total Referred', 'wpec-group-deals' ); ?></label>
	    		</th>
		    	<td>
		    		<?php
		    			printf( 
			    			_n( 
				    			'%d customer referred', 
				    			'%d customers referred.', 
				    			count( $this->print_referrals( $user->ID ) ), 
				    			'wpec-group-deals' 
			    				), 
		    			count( $this->print_referrals( $user->ID ) ) 
	    			);
		    			
		    			echo '<ul style="overflow:auto; max-height:200px">'; 
		    			foreach( $this->print_referrals( $user->ID ) as $referral ) {
		    				echo '<li>' . $referral . '</li>';
		    			}
		    			echo '</ul>';
		    		?>
		    	</td>
		   	</tr>
		   	<tr>
		   		<th>
		   			<label><?php _e( 'Referral Credits', 'wpec-group-deals' ); ?></label>
		   		</th>
		   		<td>
		   			<input type='text' name="available_credit" value="<?php echo esc_attr( $available_credit ); ?>" /><br />
		   			<span class="description"><?php _e( 'By default, this field shows the referral credits available based on the number of referrals from this user that have made a purchase, less what they have already redeemed (see below).  If you need to increase this number (perhaps add more for a refund), you can do that here.', 'wpec-group-deals' ); ?></span>
		   		</td>
		   	</tr>
		   	<tr>
		   		<th>
		   			<label><?php _e( 'Credit Amount Redeemed', 'wpec-group-deals' ); ?></label>
		   		</th>
		   		<td>
		   			<?php echo wpsc_currency_display( $used, array( 'display_as_html' => false ) ); ?>
		   		</td>
		   	</tr>

		  </table>
		<?php  

	  	}

	  	/**
	  	 * Can be used to get an array of referrals from any user.
	  	 * 
		 * @param int|string $user 
	  	 * @return array Users the current user has referred
	  	 */
	  	public function print_referrals( $user = '' ) {
			global $wpdb, $user_ID;

			if( empty( $user ) )
				$user = $user_ID;

			$results = $wpdb->get_results( $wpdb->prepare("SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'referred_from' AND meta_value = %d", $user ) );
				
			$referrals = array();

			foreach( (array) $results as $result ) {
					$user = get_userdata($result->user_id);
					$referrals[$user->ID] = $user->user_email;
			}	

			return $referrals;
		}

	  	/**
	  	 * Can be used to get an array of referrals from any user.
	  	 * 
		 * @param int|string $user 
	  	 * @return string Users the current user has referred
	  	 */
	  	public function number_of_active_referrals( $user = '' ) {
			global $wpdb, $user_ID;

			if( empty( $user ) )
				$user = $user_ID;

			if( ! count( $this->print_referrals( $user ) ) )
				return 0;
					
			$referral_ids = implode( ', ', array_keys( $this->print_referrals( $user ) ) );

			$count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(user_id) FROM $wpdb->usermeta WHERE meta_key = '_made_first_purchase' AND meta_value = 'true' AND user_id IN ( $referral_ids )") );
				
			return $count;
		}

		/**
		 * Converts location slugs to uppercase words and trades dashes for spaces.
		 * Seems like WordPress should have a native function for this, but I haven't seen it yet.
		 * 
		 * @param string $value 
		 * @return string $value
		 */

		public function ucspace( $value ) {

			return ucwords( str_replace( '-', ' ', $value ) ); 
			
		}

		/**
		 * Updates _custom_credit meta, for admins increasing referral credit for users.
		 * @param int|string $user_id 
		 */

		public function update_custom_credit_meta( $user_id ) {
			global $wpdb;

			$symbol = $wpdb->get_var( "SELECT symbol FROM " . WPSC_TABLE_CURRENCY_LIST . " WHERE id = " . get_option( 'currency_type' ) );

			$posted_credit = $this->unformat( $_POST['available_credit'] );
			$actual_credit = (float) ( ( $this->number_of_active_referrals( $user_id ) * $this->referral_credit ) - (float) get_user_meta( $user_id, '_credit_used', true ) );

			if( $posted_credit > $actual_credit )
				update_user_meta( $user_id, '_custom_credit', ( $posted_credit - $actual_credit ) );

		}

		/**
		 * Simple handler function for interpreting prices as float values.
		 * @param float Expects float value of price 
		 * @return float value of currency entered
		 */

		public function unformat( $float ) {
			global $wpdb;
			

			$symbol = $wpdb->get_var( "SELECT symbol FROM " . WPSC_TABLE_CURRENCY_LIST . " WHERE id = " . get_option( 'currency_type' ) );

			$float = str_replace( $symbol, '', $float );

			return floatval( $float );
		}

		/**
		 * Adds referral link on front-end to user profile page.
		 * @return html
		 */

		public function add_referral_link() {
			global $user_ID;

			$options = get_option( 'wpec_gd_options_array' );
			$ref_amt = $options['referral_credit'];

			if( false == $ref_amt )
				return;
				
			$ref_id = add_query_arg( 'ref', $user_ID, home_url() );

			echo '<p class="credit_link">' . __( 'Use the following link to refer friends to our website and earn referral credits', 'wpec-group-deals' ) . ': <a href="' . $ref_id . '">' . $ref_id .'</a></p>';

		}

		/**
		 * Gets the available credits of a user.
		 * @param int|string $user_id 
		 * @return currency display of available credits.
		 */

		public function get_available_credit( $user_id ) {
			global $user_ID;

			if( empty( $user_id ) && empty( $user_ID ) )
				return wpsc_currency_display( 0, array( 'display_as_html' => false ) );

			if( empty( $user_id ) )
				$user_id = $user_ID;

			$referrals = $this->number_of_active_referrals( $user_id ) * $this->referral_credit;
			$custom = (float) get_user_meta( $user_id, '_custom_credit', true );
			$used = (float) get_user_meta( $user_id, '_credit_used', true );
			
			return wpsc_currency_display( ( $referrals + $custom ) - $used, array( 'display_as_html' => false ) );
			
		}

	}

$GLOBALS['wpec_gd_user_profile'] = new WPEC_GD_User_Profile;

?>
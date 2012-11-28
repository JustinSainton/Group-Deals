<?php

/* 
 * This is a sacred file.  Only for location related functions.  Except, of course, for the register_taxonomy function.
 */

class WPEC_GD_Location {
    
    static $instance;
    static $location_terms =  '';
    static $location_distance_threshold = '';
    static $location_threshold_unit = '';
    
	public function __construct() {
        
        self::$instance = $this;

        add_filter( 'rewrite_rules_array', array( $this, 'filter_rewrite_rules_array' ) );
        add_filter( 'post_type_link', array( $this, 'post_type_link' ), 10, 2 );
        add_action( 'init', array( $this, 'init' ) );
        add_action( 'wpec_gd_ajax_submission_success_callback', array( $this, 'ajax_form_success_callback' ), 12, 2 );
        add_action( 'wpec_gd_ajax_form_pre_add_user', array( $this, 'ajax_form_pre_user' ), 12, 3 );
        add_action( 'wpec_gd_ajax_form_list_items', array( $this, 'ajax_form_location_dropdown' ) );
        add_action( 'template_redirect', array( $this, 'set_nearest_location_as_cookie' ), 12 );
        add_action( 'wp', array( $this, 'to_popup_or_not' ), 14 );
        
    //g. This means we ALSO need to implement the "Cities" feature to change your current city on the home page, sub-header.
    //h. Should have some AJAX in the backend to only allow one featured deal per city at a time.

    }

    public function init() {
        
            global $wp_rewrite;
            $wp_rewrite->use_verbose_page_rules = true;
            
        }
	
	public function redirect_user_to_product( $location = '' ) {
		
		global $user_ID;

		if( ! is_user_logged_in() )
			return;
        
		$locations = get_user_meta( $user_ID, 'location', true );
        
        $options_array = get_option( 'wpec_gd_options_array' );
        $default_location = $options_array['wpec_default_location']; 

		if( empty( $location ) && is_array( $locations ) )
			$primary_location = get_term_by( 'slug', $locations[0], 'wpec-gd-location' );
		else if( empty( $location ) )
            $primary_location = get_term_by( 'slug', $default_location, 'wpec-gd-location' );
        else
			$primary_location = get_term_by( 'slug', $location, 'wpec-gd-location' );
			
        if( is_object( $primary_location ) )
		  $primary_location_id = $primary_location->term_id;
		
		$deals_in_location = get_objects_in_term( $primary_location_id, 'wpec-gd-location' );
		
		if( empty( $deals_in_location ) ) {
					
			$available_locations = array();

			foreach( get_terms( 'wpec-gd-location' ) as $location )
				$available_locations[] = $location->term_id;

			$deals_in_location = get_objects_in_term( $available_locations, 'wpec-gd-location' );
		}
		
		$first_deal = wpec_gd_get_latest_active_deal( $deals_in_location );

        if( empty( $first_deal ) )             
            $first_deal = wpec_gd_get_latest_active_deal( $deals_in_location, false );
        
        if( ! $first_deal ) {
            wp_redirect( home_url() );
            exit;
        }
		
		wp_redirect( get_permalink( $first_deal ) );
		exit;
	}
	
    public function filter_rewrite_rules_array( $rules ) {

        //This helps avoid collisions with pages and locations.  Hoping to refactor this whole deal later on.  Feels hacky
           
            $rule = $rules['(.+?)/?$'];
            unset( $rules['(.+?)/?$'] );
            $rules['(.+?)/?$'] = $rule;
            
            
        //Building city/state strings for rewrite rules.
        $get_terms = get_terms( 'wpec-gd-location' );
        
        $cities = array();
        
        foreach( $get_terms as $terms ) :
            if( $terms->parent == 0 )
               continue;
            $cities[] = get_term_field( 'slug', $terms->parent, 'wpec-gd-location' ) . '/' .$terms->slug;
        endforeach;
        
        $newrules = array();
        foreach( $cities as $location )
            $newrules[$location . '/(.+?)/?$'] = 'index.php?wpsc-product=$matches[1]';
        
        return $newrules + $rules;

    }
    
    /*
     * This function allows us to change the way WPEC interacts with the post_type_link filter.  Instead of categories for the permalinks, we want locations.

     @todo Potentially check if group deal or not, this would be necessary if we look at a co-existing e-commerce AND group deals site.
     */
    public function post_type_link( $link, $post ) {

        if (  'wpsc-product' != $post->post_type ) 
                return $link;
        
        $slug = basename( $link );
        
        remove_filter( 'post_type_link', 'wpsc_product_link', 10, 3 );
        
        //Get city this deal is in.  Only one city.
        $get_terms = get_terms( 'wpec-gd-location' );
        
        $cities = array();
        foreach( $get_terms as $terms ) :
            if( $terms->parent == 0 || ! is_object_in_term( $post->ID, 'wpec-gd-location', $terms->slug ) )
               continue;
            $cities[] = array( 
                'city' => $terms->slug,
                'state' => get_term_field( 'slug', $terms->parent, 'wpec-gd-location' )
                );
        endforeach;
        
		$city = $state = '';
        //Now that we have the city, get the state
		if( isset( $cities[0] ) ) {
			$city = $cities[0]['city'];
			$state = $cities[0]['state'];
		}

		$permalink = apply_filters( 'wpec_gd_post_type_link', $state . '/' . $city . '/' . $slug, compact( 'link', 'post', 'state', 'city', 'slug' ) );
		
        $permalink = home_url( user_trailingslashit( $permalink , 'single' ) );
        
        return $permalink;
        
    }
    /*
     * GeoIP functions
     * 
     * In the future, we may do checks for the geoip apache module (which would be ideally always installed).
     * It is, however, rarely installed.  So we have the following functions and API interactions to get the IP 
     * of the visiting computer and returning the location.  Obviously IP can be spoofed.  Doesn't matter.
     * 
     *
     * @since 1.1
     * @returns The IP Address
     */
    
    private function get_ip_address() {
        
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) )   //check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )   //to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else
            $ip = $_SERVER['REMOTE_ADDR'];
        
        return $ip;
    }

    private function get_meta_data( $html ) {

        $meta = array();

        preg_match_all( '/<meta[^>]*?name\s*=\s*(["\'])([^\1>]*?)\1[^>]*?content\s*=\s*(["\'])([^\3>]*?)\3/sim', $html, $result, PREG_SET_ORDER );
        
        foreach( $result as $M )
            $meta[$M[2]] =$M[4];
        
        preg_match_all( '/<meta[^>]*?content\s*=\s*(["\'])([^\1>]*?)\1[^>]*?name\s*=\s*(["\'])([^\3>]*?)\3/sim', $html, $result, PREG_SET_ORDER );

        foreach($result as $M)
            $meta[$M[2]] =$M[4];
        
        return $meta;
    }

    private function location_set( $ip ) {

        if( false === ( $city = get_transient( 'location_' . $ip ) ) )
            return false;
        else
            return $city;

    }

    /*
     * It should be noted that this API is simply the most accurate, free one we could find.
     * Given the non-vital role GeoIP plays, we're okay with the limitations imposed here.
     *
     * The chief limitation is that you can only do 20 IP lookups per hour.
     *
     * We do try to help out by caching the city as a transient for an hour.
     *
     * @todo Document the limitation and enable people the options necessary to upgrade service.
     *
     */

    private function get_location() {

        if( $this->location_set( $this->get_ip_address() ) )
            return $this->location_set( $this->get_ip_address() );
	
	$url = apply_filters( 'wpec_gd_geoip_uri', 'http://www.geobytes.com/IpLocator.htm?GetLocation&template=php3.txt&IpAddress=' . $this->get_ip_address(), $this->get_ip_address());

        $tags = wp_remote_get( $url );

        if( is_wp_error( $tags ) )
            return;
        else
            $meta = $this->get_meta_data( $tags['body'] );

        $meta =  array(
            'city' => $meta['city'],
            'state' => $meta['region'],
            'country' => $meta['iso2'],
            'lat' => $meta['latitude'],
            'long' => $meta['longitude'],
            );

        set_transient( 'location_' . $this->get_ip_address(), $meta, 60*60 );

        return apply_filters( 'wpec_gd_get_location_array', $meta );
    }

    /*
     * Checks to see if we have an exact match on location
     *
     * This function checks first to see if we've already done this and stored it as a cookie.  If so, use that.
     *  If not, check if the city exists as a slug.
     * If exact city doesn't exist, we'll check the city against distances from cities within a radius.
     * If the city doesn't exist, then we check state.  If the state exists as a slug, we'll return that
     * Otherwise, we return the default location, set by the site admin.
     *
     */

    public function check_location_match() {

        self::$location_terms = get_terms( 'wpec-gd-location', 'hide_empty=0' );
        
         if( isset( $_COOKIE['group_deal_user_set_location'] ) )
             return $_COOKIE['group_deal_user_set_location'];
        
        //Setting or getting user location, site locations, location threshold and default location.
        $user_location = $this->get_location();

        $options_array = get_option( 'wpec_gd_options_array' );
        $default_location = $options_array['wpec_default_location'];
        $this->location_distance_threshold = $options_array['location_threshold'];
        $this->location_threshold_unit = $options_array['location_unit'];
        
        //Gets nearest location.
        $nearest_location = $this->find_nearest_location( $user_location );
        
        //Gets array of location slugs.  This way, we should be able to sanitize_title the terms against the slugs
        foreach( self::$location_terms as $location )
            $locations[] = $location->slug;

        // If user city matches a city we've set, all good!
        if( in_array( sanitize_title( $user_location['city'] ), $locations ) )
            return sanitize_title( $user_location['city'] );

        //This is checking what the closest defined location to the user's city is.  If it is within the threshold, it will return the city.
        if( $nearest_location && (int) $nearest_location['distance'] <= (int) $this->location_distance_threshold )
            return sanitize_title( $nearest_location['city'] );

        // If we didn't match the city, or find a close city, we'll try to match the state.
        if( in_array( sanitize_title( $user_location['state'] ), $locations ) )
            return sanitize_title( $user_location['state'] );

        // Finally, last resort, do the default location.
        return $default_location;
        
    }
    
    /*
     * Sets cookie on user's computer of their nearest location
     * 
     * @param user_set | If not set, we're just running this automatically and setting the fields/home page to the user's nearest location.  If set, ignores 
     * @uses apply_filters() on wpec_gd_location_cookie_duration - defaults to one day.
     */
    
    public function set_nearest_location_as_cookie( $user_set = false, $user_input = '' ) {
        
        if( isset( $_COOKIE['group_deal_user_set_location'] ) && ! $user_set )
            return;
        
        if( ! $user_set )
            setcookie( 'group_deal_user_guess_location', $this->check_location_match(), apply_filters( 'wpec_gd_location_guess_cookie_duration', time() + ( 60*60*24 ) ) );
        else
            setcookie( 'group_deal_user_set_location', $user_input, apply_filters( 'wpec_gd_user_location_cookie_duration', time() + ( 60*60*24 ) ) );
            
    }
    
    /*
     * This function runs before the user is added and email_exists error is thrown in AJAX registration.
     * 
     * We can check first if a user doesn't exist, then return.  If the user does exists, and the location user_meta doesn't, we update_user_meta
     * and return success and die.
     * 
     * Otherwise, we'll return to the regularly scheduled programming, which will recognize the existence of the email and die.
     */
    public function ajax_form_pre_user( $response, $email, $form_data ) {
				
			$location = sanitize_text_field( $form_data['user_location'] );
			$user = email_exists( $email );
			
			$locations = array();
			$locations = (array)get_user_meta( $user, 'location', true );
			
            $location_term = get_term_by( 'slug', $location, 'wpec-gd-location' );
            $location_name = $location_term->name;
			
            //If user exists AND they've already subscribed to this location, say so.        
            if( in_array( $location, $locations ) ) :
				
				$errors = new WP_Error();
                $errors->add( 'user', sprintf( __( 'This email address should already receive daily deal emails for %s!', 'wpec-group-deals' ), $location_name ) );
                
				$response = $errors;
				return $response;

            else : 
				$sendback = '';
				$sendback = get_objects_in_term( $location_term->term_id, 'wpec-gd-location' );
				
				if( empty( $sendback ) ) {
					
					$available_locations = array();
					
					foreach( get_terms( 'wpec-gd-location' ) as $location )
						$available_locations[] = $location->term_id;
					
					$sendback = get_objects_in_term( $available_locations, 'wpec-gd-location' );
				}

                $sendback = wpec_gd_get_latest_active_deal( $sendback );

                if( empty( $sendback ) )
                    wpec_gd_get_latest_active_deal( $sendback, false );

				$sendback = get_permalink( $sendback );
				
                $locations[] = $location;
                update_user_meta( $user, 'location', $locations );

                if( ! $sendback )
                    $sendback = home_url();
            
                //Send back a response
                $response = array(
                    'data' => sprintf( __( 'You have successfully subscribed to deals from %s! You should receive your username and password shortly.  Wait just a moment to see the latest and greatest deal we have!', 'wpec-group-deals' ), $location_name ),
					'url' => $sendback
			);
                
                return $response;
                
            endif;
			
			return;
    }
    
    public function ajax_form_success_callback( $form_data, $user_id ) {
        
			$location = sanitize_key( $form_data['user_location'] );
			$locations = array();
			
			if( isset( $form_data['user_location'] ) )
				$location = $form_data['user_location'];
			
			$locations = (array)get_user_meta( $user_id, 'location', true );
			$locations = array_unique( array_filter( array_merge( (array) $location, $locations ) ) );
			update_user_meta( $user_id, 'location', $locations );
			
			//Checking for a referral cookie
			$referred = ! empty( $_COOKIE['wpec_gd_ref'] ) ? $_COOKIE['wpec_gd_ref'] : 0;
			 
            update_user_meta( $user_id, 'referrals', 0 );
            update_user_meta( $user_id, 'referred_from', $referred );
			
			if( ! empty( $_COOKIE['wpec_gd_ref'] ) )
                setcookie( "wpec_gd_ref", "", time() - 3600 );
			
			$this->set_nearest_location_as_cookie( true, $location );
			
		return $form_data;
        
    }

    public function ajax_form_location_dropdown() {
        
        $nearest_location = $this->check_location_match();
        
		echo "<li>";
			echo '<select name="user_location" id="user_location">';
                    foreach( self::$location_terms as $locations )
                        echo '<option value="' .  $locations->slug. '"'. selected( $nearest_location, $locations->slug, false ) .'>' . $locations->name . '</option>';
                echo '</select>';
		echo "</li>";
    }
    
    public function popup_modal_no_locations() {
        return '<p>' .  __( 'No locations have been set yet. For this site to work, we need at least one location!', 'wpec-group-deals' ) . '</p>';
    }
    
    public function popup_modal_no_deals() {
        return '<p>' . __( 'We are not currently running any deals, check back soon!', 'wpec-group-deals' ) . '</p>';
    }
    
    public function to_popup_or_not( $wp ) {
        
		//If we aren't on Group Deals Home page, definitely don't pop up.
        if( ! is_group_deals_home() )
            return;
		
		//If user is logged in, grab the first location they have set, redirect them to that page
		$this->redirect_user_to_product();
        
        //Done simply to instantiate the location_terms, as that has not happened yet.
        $this->check_location_match();
        
        // If no locations are set, encourage site owner to set at least one location
        
        if( count( self :: $location_terms ) < 2  ) {
            add_filter( 'the_content', array( $this, 'popup_modal_no_locations' ) );
            return;
       }
        
        // If there is only one location, redirect to the first active product there
        
        if( count( self :: $location_terms ) == 2 ) {
            $single_location = array_merge( (array) self::$location_terms[0]->term_id, (array) self::$location_terms[1]->term_id );

            $deal = get_objects_in_term( $single_location, 'wpec-gd-location' );
            
            if( empty( $deal ) ) {
                add_filter( 'the_content', array( $this, 'popup_modal_no_deals' ) );
                return;
            }

            $latest_deal = wpec_gd_get_latest_active_deal( $deal );
            
            if( empty( $latest_deal ) )             
                $latest_deal = wpec_gd_get_latest_active_deal( $deal, false );

            if( ! $latest_deal ) {
                wp_redirect( home_url() );
                exit;
            }
            
            $url = get_permalink( $latest_deal );
            
            wp_redirect( $url );
            exit;
            
        }
        
        // Next, if we have more than two locations and user has set cookie, simply redirect to featured deal in that location
        
		if( count( self::$location_terms ) > 2 && isset( $_COOKIE['group_deal_user_set_location'] ) )
			$this->redirect_user_to_product( $_COOKIE['group_deal_user_set_location'] );
            
        // Otherwise, if there are two or more locations and user has not set cookie, popup for them to set location.
		// We have an action here that will allow people to unhook popup_html from the_content and they can do something else here - maybe a flat box instead of a pop-up
        
        if( count( self::$location_terms ) > 2 && ! isset( $_COOKIE['group_deal_user_set_location'] )  ) {
            
			do_action( 'pre_popup_html', $this );
			add_filter( 'the_content', array( $this, 'popup_html' ) );
			return;
           
        }
        
    }
    
    /*
     * Function for showing the modal window.
     */
    
    public function popup_html( $content ) {
        global $wpsc_page_titles;
        define( 'DONOTCACHEPAGE', true );        
        
    ?>
		<link href='http://fonts.googleapis.com/css?family=Raleway:100' rel='stylesheet' type='text/css' />
        <?php echo $content; ?>
        <a href="#location_modal_popup" style="display:none" id="lmp"></a>
        <div id="lmp_container" style="display:none">
			<div id="location_modal_popup">
				<form class='ajax-registration-form'>
					<?php echo wp_nonce_field( 'submit_ajax-registration', '_registration_nonce', true, false ); ?>
					<?php if( isset( $_COOKIE["wpec_gd_ref"] ) ) : ?>
					<input type="hidden" id="wpec_gd_ref" name="wpec_gd_ref" value="<?php echo $_COOKIE["wpec_gd_ref"]; ?>" />
					<?php endif; ?>
					<div id="step_1">
						<label class="large green awesome">1</label>
						<h2><?php _e( 'Confirm your location', 'wpec-group-deals' ); ?></h2>
						<?php 
							$this->ajax_form_location_dropdown(); 
						?>
						<p class="large green awesome next_step"><?php _e( 'Next', 'wpec-group-deals' ); ?></p>
					</div>
					<div id="step_2">
						<label class="large green awesome">2</label>
						<h2><?php _e( 'Enter your email address:', 'wpec-group-deals' ); ?></h2>
						<input type="text" name="email" class="email" />
						<input class="large green awesome next_step ajax-submit" value="<?php _e( 'Next', 'wpec-group-deals' ); ?>" type="submit" />
						<p class="registration-status-message"></p>
					</div>
					<div id="lmp_footer">
						<a href="<?php echo home_url( $wpsc_page_titles['userlog'] ); ?>">Sign-In</a>
					</div>
				</form>
			</div>
		</div>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
                $('a#lmp').fancybox(
                        {
                                                'transitionIn'	:	'elastic',
                                                'transitionOut'	:	'elastic',
                                                'speedIn'		:	600, 
                                                'speedOut'		:	200, 
                                                'overlayOpacity' : .9,
                                                'hideOnOverlayClick' : false,
                                                'overlayColor' : '#000',
                                                'autoDimensions': true,
                                                'modal' : true
                        }
                );
				$('a#lmp').trigger('click');
				$('div#step_1 p.next_step').click(function(){
					$("div#step_1").animate({
					height: 'toggle'
					}, { duration: 500, queue: false });
					
					$("div#step_2").show('fast').animate({
					height: '+=219'
					}, { duration: 500, queue: false });
					
				});
        });    
        </script>
<?php       
}
    
    /*
     * Returns nearest location to address provided.
     *
     * Lots of cool stuff happening here.  We already have the user's latitude and longitude.  We loop through each of the locations we have.
     * We create URL-encoded city-state pairs.  Then we send them to Google to get the lat/long back for each of them.
     *
     * When we have that, we use the haversine forumla to determine which of the location terms is closest to the users location.
     *
     * Finally, we return the nearest location's proximty and city.
     *
     * @todo Maybe get lat/long on taxonomy_create.  That may save us a lot of performance crazyness.  Need to benchmark.
     *
     * @uses Google API
     *
     */

    private function find_nearest_location( $user_location_array ) {

        $user_lat = $user_location_array['lat'];
        $user_long = $user_location_array['long'];

        //This loop creates the city/state array from the location taxonomy
        
        $locations = array();
        
        foreach( self::$location_terms as $location ) :

             if( $location->parent != 0 ) {
                $state = get_term_by( 'id', $location->parent, 'wpec-gd-location' );
                $locations[] =  array(
                    'city' => $location->name,
                    'state' => $state->name
                );
            }
            
         endforeach;

         //This loop returns each of the taxonomies with their latitude and longitude.  Set a transient so we can avoid the expensive operation as much as possible.
         
         $location_lat_longs = array();
         
            if( false === ( $location_lat_longs = get_transient( 'location_lng_lat_pairs' ) ) ) : 

                foreach( $locations as $location_pairs ) :

                $url_encoded_location = urlencode( $location_pairs['city'] . ' ' . $location_pairs['state'] );
                $results = wp_remote_get( "http://maps.googleapis.com/maps/api/geocode/json?address=$url_encoded_location&sensor=false" );
                $location_info = json_decode( $results['body'], true );

                $location_lat_longs[ sanitize_title( $location_pairs['city'] ) ]['lat'] = $location_info['results'][0]['geometry']['location']['lat'];
                $location_lat_longs[ sanitize_title( $location_pairs['city'] ) ]['lng'] = $location_info['results'][0]['geometry']['location']['lng'];

                endforeach;
                
                set_transient( 'location_lng_lat_pairs', $location_lat_longs, apply_filters( 'wpec_gd_location_lat_long_pairs_transient_duration', 60 * 60 ) );

            endif;

         //Finally, this loops takes each of these, runs it through haversine and the user lng/lat, spits out the closest one.

         foreach( (array) $location_lat_longs as $location => $lat_longs )
             $location_lat_longs[$location]['distance'] = $this->haversine( $user_lat, $user_long, $lat_longs['lat'], $lat_longs['lng'] );

        $this->sort_by_column( $location_lat_longs, 'distance' );

        $nearest_location = array();

        $nearest_location['city'] = array_shift( array_keys( $location_lat_longs ) );
        $distance = array_shift( $location_lat_longs );
        $nearest_location['distance'] = ( 'km' == $this->location_threshold_unit ) ? $distance['distance'] : ( $distance['distance'] * 0.621371192 );

        return $nearest_location;
         
    }
    private function haversine( $latitude1, $longitude1, $latitude2, $longitude2 ) {

        $earth_radius = 6371;

        $dLat = deg2rad( $latitude2 - $latitude1 );
        $dLon = deg2rad( $longitude2 - $longitude1 );

        $a = sin( $dLat / 2 ) * sin( $dLat / 2 ) + cos( deg2rad( $latitude1 ) ) * cos( deg2rad( $latitude2 ) ) * sin( $dLon / 2 ) * sin( $dLon / 2 );
        $c = 2 * asin( sqrt( $a ) );

        return ( $earth_radius * $c );

    }
    private function sort_by_column( &$arr, $col, $dir = SORT_ASC ) {

        $sort_col = array();
        foreach ( (array) $arr as $key=> $row )
            $sort_col[$key] = $row[$col];

        array_multisort( $sort_col, $dir, $arr );
    }
    
}

$GLOBALS['wpec_gd_location'] = new WPEC_GD_Location;

?>

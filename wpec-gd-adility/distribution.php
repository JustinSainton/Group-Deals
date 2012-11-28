<?php
/**
 * 
 * 
 */

class WPEC_Adility_Distribution {

    /**
     * API Key.
     *
     * @var string
     */

    public $api_key = 'aab8ed27ccbb9b65e93774d29916a940';

    /**
     * Options.
     *
     * @var array
     * @access public
     */

    public $options = array();

    /**
     * API Endpoint - returns JSON (XML also possible)
     *
     * @var string
     * @access public
     */

    public $endpoint = 'https://testapi.offersdb.com/distribution/beta/offers.json';

    public function __construct() {

        //Instantiate properties
        
        add_action( 'init', array( $this, 'set_properties' ) );
        
        //Add meta boxes to deal pages
        
        add_action( 'add_meta_boxes', array( $this, 'add_deal_metaboxes' ) );

        //Set up options page

        //Add Adility/Group Deals to custom post type

        add_action( 'admin_init', array( $this, 'adility_gd_merge' ) );
       
    }
    
    public function set_properties() {
        
        $this->options = get_option( 'wpec_gd_adility' );
        
        if( ! empty( $this->options['api_key'] ) ) {
            $this->api_key = $this->options['api_key'];
            $this->endpoint = 'https://api.offersdb.com/distribution/beta';
        }
        
        $this->city = $this->options['city'];
        $this->state = $this->options['state'];
        $this->postal_code = $this->options['postal_code'];
        $this->country_code = $this->options['country_code'];
        $this->lng = $this->options['lng'];
        $this->lat = $this->options['lat'];
        
        
    }

    public function add_deal_metaboxes() {

        add_meta_box( 'deal_meta', 'Adility Deal Details', array( $this, 'deal_meta' ), 'wpsc-product', 'side', 'default' );
        add_meta_box( 'vendor_meta', 'Adlity Vendor Details', array( $this, 'vendor_meta' ), 'wpsc-product', 'side', 'default' );

    }

    public function deal_meta() {
        global $post;

        // Get the location data if its already been entered

        $fine_print = array_pop( get_post_meta( $post->ID, '_fine_print' ) );
        $price = array_pop( get_post_meta( $post->ID, '_wpsc_price' ) );
        $sales_price = array_pop( get_post_meta( $post->ID, '_wpsc_special_price' ) );
        $start_date = array_pop( get_post_meta( $post->ID, '_start_date' ) );
        $end_date = array_pop( get_post_meta( $post->ID, '_end_date' ) );


    ?>
        <p>Fine Print <br /><br /><em><?php echo esc_attr( $fine_print ); ?></em></p>
        <p>Original Value:  <br /><br /><em><?php echo esc_attr( $price ); ?></em></p>
        <p>Deal Price: <br /><br /> <em><?php echo esc_attr( $sales_price ); ?></em></p>
        <p>Savings: <br /><br /> <em><?php echo round( ( ( ( absint( str_replace( '$', '', $price ) ) - absint( str_replace( '$', '', $sales_price ) ) ) / absint( str_replace( '$', '', $price ) ) ) * 100 ), 2 ) ; ?>%</em></p>
        <p>Start Date:  <br /><br /><em><?php echo esc_attr( $start_date ); ?></em></p>
        <p>End Date: <br /><br /> <em><?php echo esc_attr( $end_date ); ?></em></p>
    <?php
    }
    public function vendor_meta() {
        global $post;

        $vendor = array_pop( get_post_meta( $post->ID, '_vendor' ) );


    ?>
        <p>Vendor Name <br /><br /><em><?php echo esc_attr( $vendor['name'] ); ?></em></p>
        <p>Vendor Description:  <br /><br /><em><?php echo esc_attr( $vendor['bio'] ); ?></em></p>
        <p>Vendor Location (Coordinates): <br /><br /> <em><?php echo esc_attr( $vendor['lat'] ); ?>,<?php echo esc_attr( $vendor['lng'] ); ?></em></p>
    <?php
    }


    public function options_page() {

    }

    private function get_adility_deals() {

        //Builds GET request for Adility API.

        $endpoint = $this->endpoint . '?api_key=' . $this->api_key . '&types=deal';

        //For the sake of testing (Will run through list of options soon enough)

        $endpoint .= '&city=atlanta&state_code=GA&radius=100&per_page=50';

        //Arguments for remote get.  Must pass API key in URL and as header.  Also set SSL verify to false.
       
        $args = array(
            'headers' => array(
                'X-OFFERSDB-API-KEY' => $this->api_key
            ),
            'sslverify' => false
        );
        
        //Filtering user agent string to append HOST plugin version number.

        add_filter( 'http_headers_useragent', array( &$this, 'user_agent_string' ) );

        //Initiate request

        $response = wp_remote_get( $endpoint, $args );
        
        //Check for errors

        if ( is_wp_error( $response ) )
            echo '<div id="message" class="error"><p>HOST Plugin Error' . $response->get_error_message() . '</p></div>';
        else
            //Returns decoded JSON object as associative array
            return json_decode( $response['body'], true );

    }

    private function get_adility_deals() {

        //Builds GET request for Adility API.

        $endpoint = $this->endpoint . '?api_key=' . $this->api_key . '&types=deal';
        
        if( ! empty( $this->postal_code ) )
            $endpoint .= "postal_code=$this->postal_code&country_code=$this->country_code&radius=$this->radius";
        else if( ! empty( $this->lat ) )
            $endpoint .= "lat=$this->lat&lng=$this->lng&radius=$this->radius";
        else
            $endpoint .= "city=$this->city&state_code=$this->state&country_code=$this->country_code&radius=$this->radius";

        //Arguments for remote get.  Must pass API key in URL and as header.  Also set SSL verify to false.

        $args = array(
            'headers' => array(
                'X-OFFERSDB-API-KEY' => $this->api_key,
                'content-type' => 'application/json',
                'accept' => 'application/json'
            ),
            'sslverify' => false
        );

        //Filtering user agent string to append HOST plugin version number.

        add_filter( 'http_headers_useragent', array( &$this, 'user_agent_string' ) );

        //Initiate request

        $response = wp_remote_get( $endpoint, $args );

        //Check for errors

        if ( is_wp_error( $response ) )
            echo '<div id="message" class="error"><p>HOST Plugin Error' . $response->get_error_message() . '</p></div>';

        //Returns decoded JSON object as associative array

        return json_decode( $response['body'], true );
       
    }

    public function title_exists( $title ) {
        global $wpdb;

        $name = sanitize_title( $title );

        $result = $wpdb->get_var( $wpdb->prepare( "SELECT post_name FROM $wpdb->posts WHERE post_type = 'wpsc-product' AND post_name = '$name'") );

        if( is_null( $result ) )
            return false;
        else
            return true;

    }

    public function adility_gd_merge() {
        global $wp_query, $user_ID;
        
        if( ! isset( $user_ID ) )
            $user_ID = '1';

        if( false === ( $check = get_transient( 'adility_check_hourly' ) ) ) {
     
            $this->adility_deals = $this->get_adility_deals();

            foreach( (array) $this->adility_deals['offers'] as $index => $offers ) {

                if( $this->title_exists( $offers['title'] ) )
                    continue;

                $args = array(
                    'post_content' => $offers['creative']['description'],
                    'post_title' => $offers['title'],
                    'post_status' => 'publish',
                    'post_type' => 'wpsc-product',
                    'post_author' => $user_ID
                );

                $post_id = wp_insert_post( $args );

                $thumbnail = media_sideload_image( $offers['creative']['illustrations'][0]['url'], $post_id );

                $images = get_posts( 'post_type=attachment&post_parent='.$post_id );

                $thumbnail_id = $images[0]->ID;
                
                add_post_meta( $post_id, '_thumbnail_id', $thumbnail_id );
                add_post_meta( $post_id, '_fine_print', $offers['fine_print'] );
                add_post_meta( $post_id, '_wpsc_price', str_replace( '$', '', $offers['value']['formatted_amount'] ) );
                add_post_meta( $post_id, '_wpsc_special_price', str_replace ( '$', '', $offers['price']['formatted_amount'] ) );
                add_post_meta( $post_id, '_start_date', $offers['start_date'] );
                add_post_meta( $post_id, '_end_date', $offers['end_date'] );
                add_post_meta( $post_id, '_offer_id', $offers['id'] );

                $vendor = array();
                $vendor['name'] = $offers['advertiser']['name'];
                $vendor['bio'] = $offers['advertiser']['description'];
                $vendor['lat'] = $offers['advertiser']['redemption_locations'][0]['lat'];
                $vendor['lng'] = $offers['advertiser']['redemption_locations'][0]['lng'];

                add_post_meta( $post_id, '_vendor', $vendor );

            }

            $check = set_transient( 'adility_check_hourly', 'retrieved at ' . time(), 3600 );
       }
    }

    public function user_agent_string( $ua_string ) {

        return $ua_string . ' /HOST Plugin v' . $this->host_version;
        
    }

}

/*
 * Gentlemen, start your engines.
 */

$GLOBALS['host_plugin'] = new WPEC_Adility_HOST;


?>
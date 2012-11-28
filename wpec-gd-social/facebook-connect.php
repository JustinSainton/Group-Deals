<?php

/**
 * Implementation of Facebook Connect, thanks to Valentinas for the plugin :)
 * Also mashes with the "Like" plugin
 *
 */
class WPEC_GD_Facebook {
    
    //Constructors
	function WPEC_GD_Facebook() {
		$this->__construct();
	}
	function __construct() {

            //constants - Application API ID and Application Secret
            $options_array = get_option( 'social_settings' );
            if( isset( $options_array["facebook_api_key"] ) && isset( $options_array["facebook_app_secret"] ) && ( !defined( 'FACEBOOK_APP_ID' ) || !defined( 'FACEBOOK_SECRET' ) ) ) {
                define( 'FACEBOOK_APP_ID', $options_array["facebook_api_key"] );
                define( 'FACEBOOK_SECRET', $options_array["facebook_app_secret"] );
            } elseif( !defined( 'FACEBOOK_APP_ID' ) || !defined( 'FACEBOOK_SECRET' ) ) {
                define( 'FACEBOOK_APP_ID', '' );
                define( 'FACEBOOK_SECRET', '' );
            }


            //includes necessary files
            add_action( 'plugins_loaded', array( &$this, 'includes' ) );
            
            //Adds Facebook Javascript to header on front-end
            add_action( 'init', 'facebook_header' );

            //Performs Facebook Login process
            add_action( 'init', 'fb_login_user' );

            //Adds Facebook markup to footer
            add_action( 'wp_footer', 'fb_footer' );
            add_action( 'admin_print_footer_scripts', 'fb_footer' );
            
            //Modify logout URL
            add_filter( 'logout_url', 'fb_logout_url' );

            //Replace avatar
            add_filter( 'get_avatar', 'fb_connect_replace_avatar', 5, 10 );
            
            //Adds Facebook widgets
            add_action( 'widgets_init', create_function( '', 'return register_widget( "FB_Connect_Widget" );' ) );
            add_action( 'widgets_init', create_function( '', 'return register_widget( "FB_Like_Widget" );' ) );



            //Registers uninstall hook
            register_uninstall_hook( __FILE__, 'uninstall_facebook_connect' );

	}

        /*
         * Includes files for main functions, shortcode functions, avatar functions and widgets functions
         */

        function includes() {

            $plugin_path = plugin_dir_path( __FILE__ );

            require_once( $plugin_path . 'functions.php' );
            require_once( $plugin_path . 'avatar.php' );
            require_once( $plugin_path . 'widget.php' );

        }


        /*
         * Removes options from database on uninstall.
         */

        function uninstall_facebook_connect() {

            delete_option( 'fbconnect_api_id' );
            delete_option( 'fbconnect_secret' );

        }
}

if( !function_exists( 'fb_connect_options' ) ) 
    $facebook = new WPEC_GD_Facebook();



?>

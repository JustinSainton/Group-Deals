<?php

/*
  Plugin Name: WPEC Group Deals
  Plugin URI: http://groupdealsplugin.com/
  Description: A plugin that extends WP E-Commerce to allow for the creation and curation of a daily deals website.
  Author: Instinct Entertainment & Zao Web Design
  Version: 1.1
  Author URI: http://www.zao.is
  License: GPLv2
 */

/**
 *
 * WPEC Group Deal Plugin
 *
 * @description - Goal of this plugin is to create a group deal plugin that hooks into WPEC 3.8+  Allows for user to
 * create website that curates user sign-up, emails users specific deals based on schedule products.  Product is shown
 * daily on home page.  Deals are essentially products with additional "group deal" specific meta data.  Built-in logic
 * for "tipping point" concept.  Purchases are essentially CPTs that are child-products of the daily deal.
 *
 *
 * @todo I could see a great functionality if people wanted to off-load the emailing to something like MailChimp or
 *       CampaignMonitor, especially with their Analytics, that would make a lot of sense. - We could tie in pretty easily via API.
 * @todo Reporting - Probably not realistic to have comprehensive reporting in this initial version, but I could see a
 *       ton of reports that would be relevant for this.
 * @todo Groupon also has a really solid integration with Yelp, CitySearch, and other review sites, as well as
 *       awesome and super easy social media integration.  Both would be great to integrate later.
 *
 */

/**
 * wpec_group_deals
 *
 * Main WPEC Group Deal Plugin Class
 *
 * @package wpec_group_deals
 */
class wpec_group_deals {

    static $instance;
    
    /**
    * Start WPEC Group Deals on init
    */
    public function __construct() {
        
        self::$instance = $this;

        add_action( 'init', array( &$this, 'load_text_domain' ) );
        add_action( 'wpsc_pre_init', array( &$this, 'pre_init' ) );
        add_action( 'wpsc_init', array( $this, 'init' ) );
    }

    public function load_text_domain() {
        load_plugin_textdomain( 'wpec-group-deals', false, basename( dirname( __FILE__ ) ) . '/wpec-gd-languages/' );
    }

    public function pre_init() {
        $this->start();
        include_once( WPEC_DD_FILE_PATH . '/wpec-gd-includes/wpec-dd-payment.php' );
    }

    /**
    * Takes care of loading up WPEC Group Deals
    */
    public function init() {

        // Initialize
        $this->include_last();
        $this->load();
        $this->auto_updater();
    }

    /**
    * Initialize the basic WPEC Group Deals constants
    */
    public function start() {
        // Set the core file path
        define( 'WPEC_DD_FILE_PATH', dirname( __FILE__ ) );

        // Define the path to the plugin folder
        define( 'WPEC_DD_DIR_NAME',  basename( WPEC_DD_FILE_PATH ) );

        // Define the URL to the plugin folder
        define( 'WPEC_DD_FOLDER',    dirname( plugin_basename( __FILE__ ) ) );
        define( 'WPEC_DD_URL',       plugins_url( '', __FILE__ ) );

    }

    /**
    * Setup the WPEC Group Deals core constants
    */
    public function constants() {
        require_once( WPEC_DD_FILE_PATH . '/wpec-gd-includes/wpec-dd-constants.php' );
    }

    /**
    * Include the rest of WPEC Group Deal's files
    */
    public function include_last() {
        require_once( WPEC_DD_FILE_PATH . '/wpec-gd-includes/wpec-dd-functions.php' );
        require_once( WPEC_DD_FILE_PATH . '/wpec-gd-includes/wpec-dd-installer.php' );
        require_once( WPEC_DD_FILE_PATH . '/wpec-gd-includes/wpec-dd-includes.php' );

    }
    /**
    * Schedules email events
    */
    public function schedule_emails() {

        wp_schedule_event( time(), 'hourly', 'deal_over_email' );
        wp_schedule_event( time(), 'hourly', 'new_group_deal_email' );

    }

    /**
    * Setup the WPEC Group Deals core
    */
    public function load() {

        add_action( 'init', 'wpec_dd_install' );

    }

    /*
     * Called on activation.  Just checking for PHP5, WordPress 3.1+ and WPEC 3.8+
     */
    
    public function check_dependencies() {
        
    }
    
    /**
    * WPEC Group Deals Activation Hook
    */
    public function install() {
        $this->check_dependencies();
        load_plugin_textdomain( 'wpec-group-deals', false, basename( dirname( __FILE__ ) ) . '/wpec-gd-languages/' );
        define( 'WPEC_DD_FILE_PATH', dirname( __FILE__ ) );
        require_once( WPEC_DD_FILE_PATH . '/wpec-gd-includes/wpec-dd-installer.php' );
        $this->constants();
        $this->schedule_emails();
        wpec_move_theme_file();
    }

    public function auto_updater() {
        global $wp_version, $wpdb, $pagenow;

        if( ! is_admin() )
            return;

        include_once( WPEC_DD_FILE_PATH . '/wpec-gd-updates/updates.php' );

        add_filter( 'puc_request_info_query_args-wpec-group-deals', array( &$this, 'auto_update_options' ) );
        $update_check = new GD_PluginUpdateChecker( 'http://api.groupdealsplugin.com/updates/', __FILE__ );
        $update_check->checkForUpdates();

        $wpec_gd_version = $update_check->getInstalledVersion();

        $body = array(
            'IP' => $_SERVER["SERVER_ADDR"],
            'wpec_gd_version' => $wpec_gd_version,
            'wp_version' => $wp_version,
            'php_version' =>  phpversion(),
            'mysql_version' => $wpdb->db_version(),
            'wpec_version' => WPSC_VERSION
        );

        if( is_array( $this->auto_update_options( null ) ) )
            $body = array_merge( $this->auto_update_options( null ), $body );

        $args["body"] = $body;

        if( $pagenow == 'edit.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] == 'wpec-dd-vendor' )
            $api_post = wp_remote_post( 'http://api.groupdealsplugin.com/updates/', $args );
    }

    public function auto_update_options( $args ) {
        global $authorization;

        if ( ! wpec_gd_authenticate() ) {
            return $args;
        }
        else {
            $args["domain"] = $authorization["domain"];
            $args["api_id"] = $authorization["api_id"];
        }
        
        return $args;
    }

    public function deactivate(){
        global $wp_rewrite;
        
        $wp_rewrite->flush_rules();
        remove_role( 'wpec_dd_subscriber' );
        wp_clear_scheduled_hook( 'deal_over_email' );
        wp_clear_scheduled_hook( 'new_group_deal_email' );
    }
}

// Start WPEC
$wpec_group_deals = new wpec_group_deals();

// Activation
register_activation_hook( __FILE__, array( $wpec_group_deals, 'install' ) );
register_deactivation_hook( __FILE__, array( $wpec_group_deals, 'deactivate' ) );

//Appears to be the only way to plug pluggable functions.  Tried mu_plugins, plugins_loaded, init...
include_once( dirname( __FILE__ ) . '/wpec-gd-includes/wpec-gd-emailoverride.php' );

?>
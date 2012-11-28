<?php
/*
 * Class for entire payment process
 *
 * Integrates with GD API and PayPal's Adaptive Payment API
 * Specifically interacts with Preapproval, Chained and Embedded APIs
 *
 * 1. User initiates payment
 * 2. The payment is preapproved in the embedded checkout process
 * 3. Upon product expiry, if it tips, the preapproval goes through into a chained payment
 * 4. Depending on preferred payment method, chained payment either goes as three-way split ( variable percentage to group deals, variable percentage to vendor, remainder to site owner )
 * 4a. Or, if preferred payment method is check, chained payment is two-way split ( variable + vendor amount to GD, remainder to site owner. ) and GD API is hit with info to ping third-party check cutting API.
 *
 */

class WPEC_Group_Deals_Payment {

        public $vendor_info;
        public $site_license_info;

        public function __construct() {

                add_filter( 'wpsc_merchants_modules', array( &$this, 'new_gateways' ) );
           
                //add_action( 'wp_print_scripts', array( &$this, 'embedded_payment_scripts' ) );

                add_action( 'init', array( &$this, 'initiate_pay_method' ) );

            }

            function new_gateways( $nzshpcrt_gateways ) {
                
                $wpec_gd_gateway = array( 
                    'wpec_gd_paypal_ap', 
                    'wpec_auth_net', 
                    'wpsc_merchant_testmode_gd',
                    'wpsc_merchant_paypal_pro_gd',
                    'wpsc_merchant_paypal_standard_gd'
                     );

                update_option( 'payment_gateway', $wpec_gd_gateway );
                update_option( 'payment_gateway_names', $wpec_gd_gateway );
                //delete_option( 'custom_gateway_options', $wpec_gd_gateway );
                
                unset($nzshpcrt_gateways);

                include( WPEC_DD_FILE_PATH.'/wpec-gd-includes/merchants/wpec_gd_paypal_ap.php' );
                include( WPEC_DD_FILE_PATH.'/wpec-gd-includes/merchants/wpec_gd_paypal_standard.php' );
                include( WPEC_DD_FILE_PATH.'/wpec-gd-includes/merchants/wpec_gd_paypal_pro.php' );
                include( WPEC_DD_FILE_PATH.'/wpec-gd-includes/merchants/authorize_net.php' );
                include( WPEC_DD_FILE_PATH.'/wpec-gd-includes/merchants/test_gateway.php' );

                return $nzshpcrt_gateways;

            }

            function initiate_pay_method() {

                require_once WPEC_DD_FILE_PATH . '/wpec-gd-includes/payment_lib/AdaptivePayments.php';
                require_once WPEC_DD_FILE_PATH . '/wpec-gd-includes/payment_lib/Stub/AP/AdaptivePaymentsProxy.php';
 			
            }

            function embedded_payment_scripts() {
                global $wpsc_page_titles;

                
                if( is_page( $wpsc_page_titles['checkout'] ) || is_page( $wpsc_page_titles['transaction_results'] ) ) {
                    wp_register_script( 'paypal_embedded_payments', 'https://www.paypalobjects.com/js/external/dg.js', array( 'jquery' ) );
                    wp_enqueue_script( 'paypal_embedded_payments' );
                }
                
            }
    }

$wpec_payments = new WPEC_Group_Deals_Payment();


?>

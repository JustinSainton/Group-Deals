<?php
    $nzshpcrt_gateways[0] = array(
        'name' => 'WPEC Group Deals PayPal Gateway',
        'api_version' => 2.0,
        'image' => WPSC_URL . '/images/paypal.gif',
        'class_name' => 'wpec_gd_paypal_ap',
        'has_recurring_billing' => false,
        'wp_admin_cannot_cancel' => true,
        'display_name' => 'PayPal Adaptive Payments',
        'requirements' => array(
                /// so that you can restrict merchant modules to PHP 5, if you use PHP 5 features
                'php_version' => 5.0,
                 /// for modules that may not be present, like curl
                'extra_modules' => array()
        ),

        // this may be legacy, not yet decided
        'internalname' => 'wpec_gd_paypal_ap',

        // All array members below here are legacy, and use the code in paypal_multiple.php
        'form' => '__return_false',
        'submit_function' => '__return_false',
        'payment_type' => 'paypal',
        'supported_currencies' => array(
                'currency_list' =>  array('AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'MYR', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'),
                'option_name' => 'paypal_curcode'
        )
);


/**
    * WP eCommerce PayPal Standard Merchant Class
    *
    * This is the actual merchant class
    * Eventually, PayPal will support a structure that allows for guest payments, embedded, preapproved and chained.
    *
    * For now, we have to sacrifice embedded and preapproved for guest and chained
    * We basically run the chained payment, delay payment to secondary receivers, on tip/expire, pay them, on expire/no-tip, refund the money
    *
    * @package wpec-group-deals
    * @since 1.0
    * @subpackage wpec-dd-payment
*/

class wpec_gd_paypal_ap extends wpsc_merchant {

    public $name = 'PayPal AP';
    public $paypal_ipn_values = array();
    public $vendor_info;
    public $vendor_percentage;
    public $site_license_info;

        function site_license_info() {
            if ( wpec_gd_authenticate() )
                return wpec_gd_api_info();
            else
                return false;
         }

	/**
	* construct value array method, converts the data gathered by the base class code to something acceptable to the gateway
	* @access protected
	* @param boolean $aggregate Whether to aggregate the cart data or not. Defaults to false.
    * @todo Gonna be tricky getting this working with carts with multiple deals
	* @return array $paypal_vars The paypal vars
	*/
    public function construct_value_array($aggregate = false) {

            global $wpdb;

            $deal_id = (string)$this->cart_items[0]['product_id'];

            $vendor_id = get_post_field( 'post_parent', $deal_id );

            $vendor_name = get_post_field( 'post_title', $vendor_id );
            $vendor_name = array( 'name' => $vendor_name );
            $vendor_info = get_post_meta( $vendor_id, '_wpec_dd_vendor_details', true );
            $vendor_financials = get_post_meta( $vendor_id, '_wpec_dd_vendor_financials', true );

            $this->vendor_info = (object)array_merge( $vendor_name, (array) $vendor_info, (array) $vendor_financials );
            $this->site_license_info = $this->site_license_info();
            $this->vendor_percentage = ( absint( $this->vendor_info->profit_percentage ) / 100 );

            $site_owner_email = get_option( 'wpec_gd_options_array' );
            $site_owner_email = $site_owner_email['wpec_paypal_email'];

            $returnURL = add_query_arg( 'sessionid', $this->cart_data['session_id'], $this->cart_data['transaction_results_url'] );
            $cancelURL = $this->cart_data['transaction_results_url'];
            $currencyCode = $this->cart_data['store_currency'];
            
            $actionType = ( $this->vendor_percentage == 0 ) ? 'PAY' : 'PAY_PRIMARY';
            
            try {

                //Handles pay request to produce paykey

                $payRequest = new PayRequest();
                $payRequest->actionType = $actionType;
                $payRequest->cancelUrl = $cancelURL ;
                $payRequest->returnUrl = $returnURL;
                $payRequest->clientDetails = new ClientDetailsType();
                $payRequest->clientDetails->applicationId = X_PAYPAL_APPLICATION_ID;
                $payRequest->currencyCode = $currencyCode;
                if( $this->vendor_percentage != 0 )
                    $payRequest->feesPayer = 'PRIMARYRECEIVER';
                $payRequest->requestEnvelope = new RequestEnvelope();
                $payRequest->requestEnvelope->errorLanguage = "en_US";

                /*
                 * Creating the pricing structure
                 * If vendor prefers PayPal, we split the payment with their percentage, GD's percentage and remainder to shop owner
                 * If not, we split between shop owner and GD and run an API call to GD for check cutting to vendor.
                 * Parts of this adapted from aggregate PayPal code in other gateways.
             *
             */
                 
                $currency_code = $wpdb->get_var("
                        SELECT `code`
                        FROM `".WPSC_TABLE_CURRENCY_LIST."`
                        WHERE `id`='".get_option('currency_type')."'
                        LIMIT 1
                ");
                $local_currency_code = $currency_code;
                $paypal_currency_code = get_option( 'paypal_curcode', 'USD' );

                if ( $paypal_currency_code != $local_currency_code ) {
                        $curr = new CURRENCYCONVERTER();
                        $total_price = $curr->convert(
                                $this->cart_data['total_price'],
                                $paypal_currency_code,
                                $local_currency_code
                        );
                } else {
                        $total_price =  $this->cart_data['total_price'];
                }

                $site_amount = $total_price;
                
                //If vendor's preferred payment method is a check, keep their portion in GD, where we send info through check-cutting API
                if( $this->vendor_info->payment_method == "Check" ) {

                    $vendor_paypal = false;
                    $vendor_amount = ( $total_price * $this->vendor_percentage );

                } else {
                    $vendor_paypal = true;
                    $vendor_amount = ( $total_price * $this->vendor_percentage );
                }
                
                $receiver1 = new receiver();
                $receiver1->email = (string)$site_owner_email;
                $receiver1->amount = (string)$site_amount;
                $receiver1->invoiceId = (string)$this->cart_data['session_id'];
                if( $this->vendor_percentage != 0 )
                    $receiver1->primary = true;

                if( $vendor_paypal && $vendor_amount > 0 ) {

                    $receiver2 = new receiver();
                    $receiver2->email = (string)$this->vendor_info->paypal_email;
                    $receiver2->amount = (string)$vendor_amount;
                    $receiver2->invoiceId = (string)$this->cart_data['session_id'];

                    $payRequest->receiverList = array( $receiver1, $receiver2 );
                } else {
                    $payRequest->receiverList = array( $receiver1 );
                }
                
                do_action_ref_array( 'wpec_gd_ap_paypal_receivers', array( &$payRequest->receiverList )  );
                
               
                /* Make the call to PayPal to get the Pay token
                If the API call succeded, we have the paykey.
                 * Now we have to do a preapproval request to get the preapproval_key
             
             */
                $ap = new AdaptivePayments();
                $response=$ap->Pay($payRequest);

                $spoRequest = new SetPaymentOptionsRequest();
                $spoRequest->payKey = $response->payKey;
                $spoRequest->displayOptions->businessName = apply_filters( 'wpec_gd_paypal_ap_business_name', get_bloginfo( 'name' ) );

                $spoEnvelope = new RequestEnvelope();
                $spoEnvelope->errorLanguage = "en_US";
                $spoRequest->requestEnvelope = $spoEnvelope;

                $spo_response = $ap->SetPaymentOptions( $spoRequest );

                if(strtoupper($ap->isSuccess) == 'FAILURE') {
                    $_SESSION['FAULTMSG']=$ap->getLastError();
                    $location = WPEC_DD_URL."/wpec-gd-includes/payment_lib/APIError.php";
                    header("Location: $location");
                } else {
                    $_SESSION['payKey'] = $response->payKey;


                    if($response->paymentExecStatus == "CREATED") {
                        if( isset( $_GET['cs'] ) )
                            $_SESSION['payKey'] = '';
                    try {
                        if(isset($_REQUEST["payKey"])){
                        $payKey = $_REQUEST["payKey"];}
                        if(empty($payKey))
                        {
                        $payKey = $_SESSION['payKey'];
                        }

                        $pdRequest = new PaymentDetailsRequest();
                        $pdRequest->payKey = $payKey;
                        $rEnvelope = new RequestEnvelope();
                        $rEnvelope->errorLanguage = "en_US";
                        $pdRequest->requestEnvelope = $rEnvelope;

                        $ap = new AdaptivePayments();
                        $response=$ap->PaymentDetails($pdRequest);

                        /* Display the API response back to the browser.
                        If the response from PayPal was a success, display the response parameters'
                        If the response was an error, display the errors received using APIError.php.
             * 
             */
                        
                        if(strtoupper($ap->isSuccess) == 'FAILURE')
                        {
                            $_SESSION['FAULTMSG']=$ap->getLastError();
                            $location = WPEC_DD_URL."/wpec-gd-includes/payment_lib/APIError.php";
                            header("Location: $location");

                        }
                    }
                    catch(Exception $ex) {

                    $fault = new FaultMessage();
                    $errorData = new ErrorData();
                    $errorData->errorId = $ex->getFile() ;
                    $errorData->message = $ex->getMessage();
                    $fault->error = $errorData;
                    $_SESSION['FAULTMSG']=$fault;
                    $location = "APIError.php";
                    header("Location: $location");
                    }
                  }

                }
            }
        catch(Exception $ex) {

            $fault = new FaultMessage();
            $errorData = new ErrorData();
            $errorData->errorId = $ex->getFile() ;
            $errorData->message = $ex->getMessage();
            $fault->error = $errorData;
            $_SESSION['FAULTMSG']=$fault;
            $location = WPEC_DD_URL."/wpec-gd-includes/payment_lib/APIError.php";
            header("Location: $location");
        }

    }

	/**
	* submit method, sends the received data to the payment gateway
	* @access public
	*/
	public function submit() {
            
         $redirect = "https://www.paypal.com/webscr?cmd=_ap-payment&paykey=".$_SESSION['payKey'];
		if (defined('WPSC_ADD_DEBUG_PAGE') && WPSC_ADD_DEBUG_PAGE) {
                    
			echo "<a href='".esc_url($redirect)."'>Test the URL here</a>";
			exit();
		} else {
			wp_redirect($redirect);
			exit();
		}
	}


	/**
	* parse_gateway_notification method, receives data from the payment gateway
	* @access public
	*/
	public function parse_gateway_notification() {
		/// PayPal first expects the IPN variables to be returned to it within 30 seconds, so we do this first.
		$paypal_url = get_option('paypal_multiple_url');
		$received_values = array();
		$received_values['cmd'] = '_notify-validate';
  		$received_values += $_POST;
		$options = array(
			'timeout' => 5,
			'body' => $received_values,
			'user-agent' => ('WP e-Commerce/'.WPSC_PRESENTABLE_VERSION)
		);

		$response = wp_remote_post($paypal_url, $options);
		if( 'VERIFIED' == $response['body'] ) {
			$this->paypal_ipn_values = $received_values;
			$this->session_id = $received_values['invoice'];
			$this->set_purchase_processed_by_sessionid(3);

		} else {
			exit("IPN Request Failure");
		}
	}

	/**
	* process_gateway_notification method, receives data from the payment gateway
	* @access public
	*/
	public function process_gateway_notification() {
	  // Compare the received store owner email address to the set one
		if(strtolower($this->paypal_ipn_values['receiver_email']) == strtolower(get_option('paypal_multiple_business'))) {
			switch($this->paypal_ipn_values['txn_type']) {
				case 'cart':
				case 'express_checkout':
					if((float)$this->paypal_ipn_values['mc_gross'] == (float)$this->cart_data['total_price']) {
						$this->set_transaction_details($this->paypal_ipn_values['txn_id'], 3);
						transaction_results($this->cart_data['session_id'],false);
					}
				break;

				case 'subscr_signup':
				case 'subscr_payment':
					$this->set_transaction_details($this->paypal_ipn_values['subscr_id'], 3);
					foreach($this->cart_items as $cart_row) {
						if($cart_row['is_recurring'] == true) {
							do_action('wpsc_activate_subscription', $cart_row['cart_item_id'], $this->paypal_ipn_values['subscr_id']);
						}
					}
					transaction_results($this->cart_data['session_id'],false);
				break;

				case 'subscr_cancel':
				case 'subscr_eot':
				case 'subscr_failed':
					foreach($this->cart_items as $cart_row) {
						$altered_count = 0;
						if((bool)$cart_row['is_recurring'] == true) {
							$altered_count++;
							wpsc_update_cartmeta($cart_row['cart_item_id'], 'is_subscribed', 0);
						}
					}
				break;

				default:
				break;
			}
		}

		$message = "
		{$this->paypal_ipn_values['receiver_email']} => ".get_option('paypal_multiple_business')."
		{$this->paypal_ipn_values['txn_type']}
		{$this->paypal_ipn_values['mc_gross']} => {$this->cart_data['total_price']}
		{$this->paypal_ipn_values['txn_id']}

		".print_r($this->cart_items, true)."
		{$altered_count}
		";
	}

}
                

?>

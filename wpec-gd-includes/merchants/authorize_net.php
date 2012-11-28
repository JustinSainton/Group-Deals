<?php 
  define('WPECAUTHNET_PLUGIN_NAME','wpec_auth_net');
  //define('WPECAUTHNET_ADMIN',plugin_dir_path( __FILE__ ) . 'admin/');
  define('WPECAUTHNET_CLASSES', plugin_dir_path( __FILE__ ) . 'wpec_auth_net/classes/');
     
  $nzshpcrt_gateways[3]= array(
             'name'                   => 'Authorize.net AIM/CIM/ARB',
             'api_version'            => 2,
             'class_name'             => WPECAUTHNET_PLUGIN_NAME,
             'image'                  => WPSC_URL . '/images/cc.gif',
             'requirements'           => array(),
             'has_recurring_billing'  => true,
             'wp_admin_cannot_cancel' => true,
             'display_name'           => 'Authorize.Net',
             'form'                   => 'form_auth_net',
             'submit_function'        => 'submit_auth_net_gd',
             'payment_type'           => 'credit_card',
             'internalname'           => WPECAUTHNET_PLUGIN_NAME
         );

  include_once('wpec_auth_net/wpec_auth_net.php');

?>

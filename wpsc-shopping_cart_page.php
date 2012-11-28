<?php
global $wpsc_cart, $wpdb, $wpsc_checkout, $wpsc_gateway, $wpsc_coupons;
$wpsc_checkout = new wpsc_checkout();
$wpsc_gateway = new wpsc_gateways();
$alt = 0;
if(isset($_SESSION['coupon_numbers']))
   $wpsc_coupons = new wpsc_coupons($_SESSION['coupon_numbers']);

if(wpsc_cart_item_count() < 1) :
   _e('Oops, there is nothing in your cart.', 'wpsc') . "<a href=".get_option("product_list_url").">" . __('Please visit our shop', 'wpsc') . "</a>";
   return;
endif;
?>
<div id="checkout_page_container">

<?php do_action('wpsc_before_form_of_shopping_cart'); ?>

<h3><?php _e('Your Purchase', 'wpsc'); ?></h3>

<form class='wpsc_checkout_forms' action='<?php echo get_option( 'shopping_cart_url' ); ?>' method='post' enctype="multipart/form-data">
<?php echo wp_nonce_field( 'qty_change', '_qty_change_nonce', true, false ); ?>
<input name='product_id' type='hidden' value='<?php echo wpec_gd_single_item_id(); ?>' />
<div class='checkout_cart'>
<h3><a href="<?php echo esc_url( get_permalink( wpec_gd_single_item_id() ) ); ?>"><?php echo wpec_gd_single_item_name(); ?></a></h3>
   <div class='subtotal_box'>
      <span class="column">
         <span class="label">Price</span>
         <span class="value"><?php echo str_replace( '.00', '', wpec_gd_single_item_price() ); ?></span>
      </span>

      <span class="column">
         <span class="operator">X</span>
      </span>

      <span class="column">
         <span class="label">How Many?</span>
         <span class="value"><?php wpec_gd_max_qty_dropdown(); ?></span>
      </span>

      <span class="column">
         <span class="operator">=</span>
      </span>
      
      <span class="column">
         <span class="label">Subtotal</span>
         <span class="value"><?php echo str_replace( '.00', '', wpec_gd_single_item_price() ); ?></span>
      </span>
   </div>
   <span class="column">
      <span class="operator">-</span>
   </span>
   <div class='credits'>
      <span class="column">
         <span class="label">Credits</span>
         <span class="value"><?php echo str_replace( '.00', '', wpec_gd_user_credits_available() ); ?></span>
         <span class="hidden"><?php echo str_replace( '.00', '', wpec_gd_user_credits_available() ); ?></span>
      </span>
   </div>
   <span class="column">
      <span class="operator">=</span>
   </span>
   <div class='total'>
      <span class="column">
         <span class="label">You pay</span>
         <span class="value"><?php echo str_replace( '.00', '', wpsc_cart_total() ); ?></span>
      </span>
   </div>

   <br class="clear" />

   <div class='middle_third'>
     <?php if ( ! is_user_logged_in() ) : ?>
      <div class='left_part'>
         <div class="personal_info">
            <h3><span>1. </span><?php _e( 'Your Personal Information' )?></h3>
            <label for='sign_in'>
               <input type='radio' id='sign_in' value='sign_in' name='personal_info' class='sign_in' checked='checked' />
               <?php _e( 'Sign In with Existing Account', 'wpsc' ); ?>
            </label>
            <div id='sign_in_set' class="wpsc_registration_form">
            
            </div>
            <label for='sign_up'>
               <input type='radio' id='sign_up' value='sign_up' name='personal_info' class='sign_in' /> 
               <?php _e( 'Create New Account', 'wpsc' );?>
            </label>
            <div id='sign_up_set' class="wpsc_registration_form">
               <fieldset class='wpsc_registration_form wpsc_right_registration'>

                  <p class="full_row"><label><?php _e( 'Full Name', 'wpsc' ); ?>
                  <input type="text" name="full_name" id="full_name" value="" size="20"/></label></p>
                  
                  <p class="two_thirds"><label><?php _e( 'E-mail', 'wpsc' ); ?>
                  <input type="text" name="email" id="signup_email" value="" size="20" /></label></p>

                  <p class="one_third"><label><?php _e( 'Zip Code', 'wpsc' ); ?>
                  <input type="text" name="zip_code" id="zip_code" value="" size="20" /></label></p>

                  <p class="half"><label><?php _e( 'Password', 'wpsc' ); ?>
                  <input type="password" name="password" id="password" value="" size="20" /></label></p>

                  <p class="half"><label><?php _e(' Password Confirm', 'wpsc' ); ?>
                  <input type="password" name="password_confirm" id="password_confirm" value="" size="20" /></label></p>
                  
                  <?php if( isset( $_COOKIE["wpec_gd_ref"] ) ) : ?>
                     <input type="hidden" id="wpec_gd_ref" name="wpec_gd_ref" value="<?php echo $_COOKIE["wpec_gd_ref"]; ?>" />
                  <?php endif; ?>
                  <input type='hidden' name='action' value='process_gd_signup' />
                  <?php echo wp_nonce_field( 'gd_signup', '_gd_signup_nonce', true, false ); ?>
              </fieldset>
            </div>
         <br class='clear' />
         </div>
      </div>
      <?php endif; ?>
      <div class='billing_info<?php echo is_user_logged_in() ? ' logged_in' : ''; ?>'>
      <?php if(wpsc_gateway_count() > 1): // if we have more than one gateway enabled, offer the user a choice ?>
         <tr>
         <td colspan='2' class='wpsc_gateway_container'>
            <h3><span><?php echo is_user_logged_in() ? '1. ' : '2. '; ?></span><?php _e('Your Billing Information', 'wpsc');?></h3>
            <?php while (wpsc_have_gateways()) : wpsc_the_gateway(); ?>
               <div class="custom_gateway">
                     <label><input type="radio" value="<?php echo wpsc_gateway_internal_name();?>" <?php echo wpsc_gateway_is_checked(); ?> name="custom_gateway" class="custom_gateway"/><?php echo wpsc_gateway_name(); ?> 
                        <?php if( wpsc_show_gateway_image() ): ?>
                        <img src="<?php echo wpsc_gateway_image_url(); ?>" alt="<?php echo wpsc_gateway_name(); ?>" style="position:relative; top:5px;" />
                        <?php endif; ?>
                     </label>

                  <?php if(wpsc_gateway_form_fields()): ?>
                     <table class='wpsc_checkout_table <?php echo wpsc_gateway_form_field_style();?>'>
                        <?php echo wpsc_gateway_form_fields();?>
                     </table>
                  <?php endif; ?>
               </div>
            <?php endwhile; ?>
            </td></tr>
         <?php else: // otherwise, there is no choice, stick in a hidden form ?>
            <tr><td colspan="2" class='wpsc_gateway_container'>
            <?php while (wpsc_have_gateways()) : wpsc_the_gateway(); ?>
               <input name='custom_gateway' value='<?php echo wpsc_gateway_internal_name();?>' type='hidden' />

                  <?php if(wpsc_gateway_form_fields()): ?>
                     <table class='wpsc_checkout_table <?php echo wpsc_gateway_form_field_style();?>'>
                        <?php echo wpsc_gateway_form_fields();?>
                     </table>
                  <?php endif; ?>
            <?php endwhile; ?>
         </td>
         </tr>
         <?php endif; ?>
   
   </div>
   
   </div>
   <div class='wpsc_make_purchase'>
      <span>
         <?php if(!wpsc_has_tnc()) : ?>
            <input type='hidden' value='yes' name='agree' />
         <?php endif; ?>
            <input type='hidden' value='submit_checkout' name='wpsc_action' />
            <input type='submit' value='<?php _e('Purchase', 'wpsc');?>' name='submit_cart' class='make_purchase wpsc_buy_button' />
      </span>
   </div>
      <br class="clear" />
</div>


   <table class='wpsc_checkout_table table-1'>
      <?php $i = 0;
      while (wpsc_have_checkout_items()) : wpsc_the_checkout_item(); ?>

        <?php if( wpsc_checkout_form_is_header() ){
               $i++;
               //display headers for form fields ?>
               <?php if($i > 1):?>
                  </table>
                  <table class='wpsc_checkout_table table-<?php echo $i; ?>'>
               <?php endif; ?>

               <tr <?php echo wpsc_the_checkout_item_error_class();?>>
                  <td <?php wpsc_the_checkout_details_class(); ?> colspan='2'>
                     <h4><?php echo wpsc_checkout_form_name();?></h4>
                  </td>
               </tr>
               <?php if(wpsc_is_shipping_details()):?>
               <tr class='same_as_shipping_row'>
                  <td colspan ='2'>
                  <?php $checked = '';
                  if(isset($_POST['shippingSameBilling']) && $_POST['shippingSameBilling'])
                  	$_SESSION['shippingSameBilling'] = true;
                  elseif(isset($_POST['submit']) && !isset($_POST['shippingSameBilling']))
                  	$_SESSION['shippingSameBilling'] = false;

                  	if( isset( $_SESSION['shippingSameBilling'] ) && $_SESSION['shippingSameBilling'] == 'true' )
                  		$checked = 'checked="checked"';
                   ?>
					<label for='shippingSameBilling'><?php _e('Same as billing address:','wpsc'); ?></label>
					<input type='checkbox' value='true' name='shippingSameBilling' id='shippingSameBilling' <?php echo $checked; ?> />
					<br/><span id="shippingsameasbillingmessage"><?php _e('Your order will be shipped to the billing address', 'wpsc'); ?></span>
                  </td>
               </tr>
               <?php endif;

            // Not a header so start display form fields
            }elseif(wpsc_disregard_shipping_state_fields()){
            ?>
               <tr class='wpsc_hidden'>
                  <td class='<?php echo wpsc_checkout_form_element_id(); ?>'>
                     <label for='<?php echo wpsc_checkout_form_element_id(); ?>'>
                     <?php echo wpsc_checkout_form_name();?>
                     </label>
                  </td>
                  <td>
                     <?php echo wpsc_checkout_form_field();?>
                      <?php if(wpsc_the_checkout_item_error() != ''): ?>
                             <p class='validation-error'><?php echo wpsc_the_checkout_item_error(); ?></p>
                     <?php endif; ?>
                  </td>
               </tr>
            <?php
            }elseif(wpsc_disregard_billing_state_fields()){
            ?>
               <tr class='wpsc_hidden'>
                  <td class='<?php echo wpsc_checkout_form_element_id(); ?>'>
                     <label for='<?php echo wpsc_checkout_form_element_id(); ?>'>
                     <?php echo wpsc_checkout_form_name();?>
                     </label>
                  </td>
                  <td>
                     <?php echo wpsc_checkout_form_field();?>
                      <?php if(wpsc_the_checkout_item_error() != ''): ?>
                             <p class='validation-error'><?php echo wpsc_the_checkout_item_error(); ?></p>
                     <?php endif; ?>
                  </td>
               </tr>
            <?php
            } else { ?>
			<tr>
               <td class='<?php echo wpsc_checkout_form_element_id(); ?>'>
                  <label for='<?php echo wpsc_checkout_form_element_id(); ?>'>
                  <?php echo wpsc_checkout_form_name();?>
                  </label>
               </td>
               <td>
                  <?php echo wpsc_checkout_form_field();?>
                   <?php if(wpsc_the_checkout_item_error() != ''): ?>
                          <p class='validation-error'><?php echo wpsc_the_checkout_item_error(); ?></p>
                  <?php endif; ?>
               </td>
            </tr>

         <?php }//endif; ?>

      <?php endwhile; ?>

      <?php if(wpsc_has_tnc()) : ?>
         <tr>
            <td colspan='2'>
                <label for="agree"><input id="agree" type='checkbox' value='yes' name='agree' /> <?php printf(__("I agree to the <a class='thickbox' target='_blank' href='%s' class='termsandconds'>Terms and Conditions</a>", "wpsc"), site_url("?termsandconds=true&amp;width=360&amp;height=400")); ?> <span class="asterix">*</span></label>
               </td>
         </tr>
      <?php endif; ?>

      </table>
<div class='clear'></div>
</form>
</div>
</div><!--close checkout_page_container-->
<?php
   do_action('wpsc_bottom_of_shopping_cart');
?>
<script type="text/javascript">

   jQuery(document).ready(function($) { 

      $('fieldset.gd_login').hide().appendTo($('div#sign_in_set')).show();

   });

</script>

<fieldset class='wpsc_registration_form gd_login'>
   <?php echo wp_nonce_field( 'gd_login', '_gd_login_nonce', true, false ); ?>
      <?php
      $args = array(
         'remember' => false,
         'redirect' => home_url( $_SERVER['REQUEST_URI'] ),
         'label_username' => __( 'Email Address' ),
         'label_log_in' => __( 'Continue' )
      );

      wp_login_form( $args );

      ?>
</fieldset>
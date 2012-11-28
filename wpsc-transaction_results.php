<?php
	/**
	 * The Transaction Results Theme.
	 *
	 * Displays everything within transaction results.  Hopefully much more useable than the previous implementation.
	 *
	 * @package WPSC
	 * @since WPSC 3.8
	 */

	global $purchase_log, $errorcode, $sessionid, $echo_to_screen, $cart, $message_html;
?>
<div class="wrap">

<?php

echo wpsc_transaction_theme();	
		
	if ( $echo_to_screen && ! is_null( $cart ) && ! $errorcode && ! is_null( $sessionid ) ) {			
		
		//Do something here, if you like.  Otherwise, we're hooking in up top.
			
	} elseif ( $echo_to_screen && ! isset( $purchase_log ) ) {
			_e( 'Oops, there is nothing in your cart.', 'wpsc' ) . "<a href=" . get_option( "product_list_url" ) . ">" . __( 'Please visit our shop', 'wpsc' ) . "</a>";
	}
?>	
	
</div>
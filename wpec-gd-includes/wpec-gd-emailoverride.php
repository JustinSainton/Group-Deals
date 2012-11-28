<?php

if ( ! function_exists( 'wp_new_user_notification' ) )  :

	function wp_new_user_notification($user_id, $plaintext_pass = '') {

			$user = new WP_User( $user_id );

			add_filter( 'wp_mail_content_type','wpec_dd_set_contenttype' );
			
			$user_login = stripslashes( $user->user_login );
			$user_email = stripslashes( $user->user_email );

			// The blogname optreverseion is escaped with esc_html on the way into the database in sanitize_option
			// we want to reverse this for the plain text arena of emails.
			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

			$message  = sprintf( __( 'New user registration on your site %s:', 'wpec-group-deals' ), $blogname ) . "<br /><br />";
			$message .= sprintf( __( 'Username: %s', 'wpec-group-deals' ), $user_login ) . "<br /><br />";
			$message .= sprintf( __( 'E-mail: %s', 'wpec-group-deals' ), $user_email ) . "<br />";

			@wp_mail( get_option( 'admin_email' ), sprintf( __( '[%s] New User Registration', 'wpec-group-deals' ), $blogname ), $message );

			if ( empty( $plaintext_pass ) )
					return;
			
			$ref_link = add_query_arg( 'ref', $user_id, home_url() );

			$message  = sprintf( __( 'Username: %s', 'wpec-group-deals' ), $user_login ) . "<br />";
			$message .= sprintf( __( 'Password: %s', 'wpec-group-deals' ), $plaintext_pass ) . "<br /><br />";
			$message .= wp_login_url() . "<br /><br />";

			$options = get_option( 'wpec_gd_options_array' );
			$ref_amt = $options['referral_credit'];

			if( $ref_amt ) {
				$message .= __( 'You can refer friends to our website and earn credits towards purchase with the following link: ', 'wpec-group-deals' );
				$message .= '<br /><a href="' . $ref_link . '">' . $ref_link . '</a>';
			}
			
			apply_filters( 'wpec_gd_email_message', $message, $ref_link, $user_login, $plaintext_pass, $blogname );

			wp_mail( $user_email, sprintf( __( '[%s] Your username and password', 'wpec-group-deals' ), $blogname ), $message );
			
			remove_filter( 'wp_mail_content_type','wpec_dd_set_contenttype' );
	}

endif;


?>
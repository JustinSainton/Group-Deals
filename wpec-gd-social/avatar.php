<?php
function fb_connect_replace_avatar($avatar, $id_or_email, $size, $default, $alt){
	global $wpdb;
	
	//this if/elseif/else statment is taken from 
	// wp-includes/pluggable.php, line 1610
	// http://phpxref.com/xref/wordpress/wp-includes/pluggable.php.source.html#l1610
	$id = 0;
	if ( is_numeric($id_or_email) ) {
		$id = (int) $id_or_email;
	} elseif ( is_object($id_or_email) ) {
		// No avatar for pingbacks or trackbacks
		$allowed_comment_types = apply_filters( 'get_avatar_comment_types', array( 'comment' ) );
		if ( ! empty( $id_or_email->comment_type ) && ! in_array( $id_or_email->comment_type, (array) $allowed_comment_types ) )
			return $avatar;

		if ( !empty($id_or_email->user_id) ) {
			$id = (int) $id_or_email->user_id;
		} elseif ( !empty($id_or_email->comment_author_email) ) {
			$id = $existing_user = $wpdb->get_var( 'SELECT DISTINCT `u`.`ID` FROM `' . $wpdb->users . '` `u` WHERE user_email = "' . $id_or_email->comment_author_email . '" LIMIT 1 ' );
		}
	} else {
		$id = $existing_user = $wpdb->get_var( 'SELECT DISTINCT `u`.`ID` FROM `' . $wpdb->users . '` `u` WHERE user_email = "' . $id_or_email . '" LIMIT 1 ' );
	}
	
	$fb_uid = get_user_meta($id, 'fb_uid', true);
	
	if($fb_uid)
		return '<fb:profile-pic uid="' . $fb_uid . '" facebook-logo="true" class="avatar avatar-' . $size . ' photo" height="' . $size . '" width="' . $size . '" linked="false"></fb:profile-pic>';
	else
		return $avatar;		
}
?>
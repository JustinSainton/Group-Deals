<?php

/* WPEC-DD Settings
 *
 * Registers settings for WPEC-DD
 *
 */

// Specify Hooks/Filters
add_action( 'admin_init', 'wpec_gd_settings_init' );
add_action( 'admin_menu', 'wpec_gd_options_page' );

// Register our settings, and some styles. Add the settings section, and settings fields
function wpec_gd_settings_init(){

        register_setting( 'social_settings', 'social_settings', 'wpec_validate_options' );
        add_settings_section( 'facebook_section', 'Social Media Configuration', 'social_media_section_text', 'wpec_gd_options' );
        add_settings_field( 'facebook_api_key', 'Facebook Application API ID:', 'facebook_api_id', 'wpec_gd_options', 'facebook_section' );
        add_settings_field( 'facebook_app_secret', 'Faceook Application Secret:', 'facebook_app_secret', 'wpec_gd_options', 'facebook_section' );

	
        //Register Settings for each email.  Arrays of Subject and Body
        register_setting( 'wpec_gd_main_options', 'wpec_gd_options_array', 'wpec_validate_options' );
        register_setting( 'wpec_gd_emails', 'site_owner_tipped', 'wpec_validate_emails' );
        register_setting( 'wpec_gd_emails', 'site_owner_untipped', 'wpec_validate_emails' );
        register_setting( 'wpec_gd_emails', 'site_owner_expired', 'wpec_validate_emails' );
        register_setting( 'wpec_gd_emails', 'business_owner_tipped', 'wpec_validate_emails' );
        register_setting( 'wpec_gd_emails', 'business_owner_untipped', 'wpec_validate_emails' );
        register_setting( 'wpec_gd_emails', 'business_owner_expired', 'wpec_validate_emails' );
        register_setting( 'wpec_gd_emails', 'deal_purchaser_tipped', 'wpec_validate_emails' );
        register_setting( 'wpec_gd_emails', 'deal_purchaser_untipped', 'wpec_validate_emails' );
        register_setting( 'wpec_gd_emails', 'deal_purchaser_expired', 'wpec_validate_emails' );
        register_setting( 'wpec_gd_emails', 'deal_purchaser_new_deal', 'wpec_validate_emails' );

        add_settings_section( 'main_section', __( 'Main Settings', 'wpec-group-deals' ), 'wpec_options_intro_text', 'wpec_gd_options' );
        add_settings_section( 'email_templates', __( 'Email Templates', 'wpec-group-deals' ), 'wpec_options_email_intro_text', 'wpec_gd_options' );
        
        add_settings_field( 'gd_api_id', __( 'Group Deals API ID', 'wpec-group-deals' ), 'gd_api_id', 'wpec_gd_options', 'main_section' );
        add_settings_field( 'gd_referral_credit', __( 'Referral Credit', 'wpec-group-deals' ), 'gd_referral_credit', 'wpec_gd_options', 'main_section' );
        add_settings_field( 'gd_logo_upload', __( 'Logo Upload', 'wpec-group-deals' ), 'gd_logo_upload', 'wpec_gd_options', 'main_section' );
        add_settings_field( 'wpec_dd_home_image_width', __( 'Group Deal Image Width', 'wpec-group-deals' ), 'wpec_options_img_width', 'wpec_gd_options', 'main_section' );
        add_settings_field( 'wpec_dd_home_image_height', __( 'Group Deal Image Height', 'wpec-group-deals' ), 'wpec_options_img_height', 'wpec_gd_options', 'main_section' );
        add_settings_field( 'wpec_dd_home_image_crop', __( 'Crop Images?', 'wpec-group-deals' ), 'wpec_options_crop_img', 'wpec_gd_options', 'main_section' );
        add_settings_field( 'wpec_paypal_email', __( 'Paypal Email:', 'wpec-group-deals' ), 'wpec_paypal_email', 'wpec_gd_options', 'main_section' );
        add_settings_field( 'wpec_default_location', __( 'Default Location:', 'wpec-group-deals' ), 'wpec_default_location', 'wpec_gd_options', 'main_section' );
        add_settings_field( 'wpec_default_page', __( 'What page should be used for the Group Deals landing page? NOTE: If you do not have multiple locations to choose from, the popup will not show.   Going to the home page will show the featured deal you have created. :', 'wpec-group-deals' ), 'wpec_default_page', 'wpec_gd_options', 'main_section' );
        add_settings_field( 'wpec_location_threshold', __( 'When determining a user\'s location, how wide of a radius should the GeoIP system allow for nearby locations?', 'wpec-group-deals' ), 'wpec_location_threshold', 'wpec_gd_options', 'main_section' );
        add_settings_field( 'gd_mobile_theme', __( 'Mobile Theme?', 'wpec-group-deals' ), 'gd_mobile_theme', 'wpec_gd_options', 'main_section' );
        add_settings_field( 'wpec_site_owner_tipped_subject', __( 'Site Owner - Deal Tipped {Subject}', 'wpec-group-deals' ), 'wpec_site_owner_tipped_subject', 'wpec_gd_options', 'email_templates' );
        add_settings_field( 'wpec_site_owner_tipped_body', __( 'Site Owner - Deal Tipped {Body}', 'wpec-group-deals' ), 'wpec_site_owner_tipped_body', 'wpec_gd_options', 'email_templates' );
        add_settings_field( 'wpec_site_owner_untipped_subject', __( 'Site Owner - Deal Untipped {Subject}', 'wpec-group-deals' ), 'wpec_site_owner_untipped_subject', 'wpec_gd_options', 'email_templates' );
        add_settings_field( 'wpec_site_owner_untipped_body', __( 'Site Owner - Deal Untipped {Body}', 'wpec-group-deals' ), 'wpec_site_owner_untipped_body', 'wpec_gd_options', 'email_templates' );
        add_settings_field( 'wpec_site_owner_expired_subject', __( 'Site Owner - Deal Expired {Subject}', 'wpec-group-deals' ), 'wpec_site_owner_expired_subject', 'wpec_gd_options', 'email_templates' );
        add_settings_field( 'wpec_site_owner_expired_body', __( 'Site Owner - Deal Expired {Body}', 'wpec-group-deals' ), 'wpec_site_owner_expired_body', 'wpec_gd_options', 'email_templates' );
        add_settings_field( 'wpec_business_owner_tipped_subject', __( 'Business Owner - Deal Tipped {Subject}', 'wpec-group-deals' ), 'wpec_business_owner_tipped_subject', 'wpec_gd_options', 'email_templates' );
        add_settings_field( 'wpec_business_owner_tipped_body', __( 'Business Owner - Deal Tipped {Body}', 'wpec-group-deals' ), 'wpec_business_owner_tipped_body', 'wpec_gd_options', 'email_templates' );
        add_settings_field( 'wpec_business_owner_untipped_subject', __( 'Business Owner - Deal Untipped {Subject}', 'wpec-group-deals' ), 'wpec_business_owner_untipped_subject', 'wpec_gd_options', 'email_templates' );
        add_settings_field( 'wpec_business_owner_untipped_body', __( 'Business Owner - Deal Untipped {Body}', 'wpec-group-deals' ), 'wpec_business_owner_untipped_body', 'wpec_gd_options', 'email_templates' );
        add_settings_field( 'wpec_business_owner_expired_subject', __( 'Business Owner - Deal Expired {Subject}', 'wpec-group-deals' ), 'wpec_business_owner_expired_subject', 'wpec_gd_options', 'email_templates' );
        add_settings_field( 'wpec_business_owner_expired_body', __( 'Business Owner - Deal Expired {Body}', 'wpec-group-deals' ), 'wpec_business_owner_expired_body', 'wpec_gd_options', 'email_templates' );
        add_settings_field( 'wpec_deal_purchaser_tipped_subject', __( 'Deal Purchaser - Deal Tipped {Subject}', 'wpec-group-deals' ), 'wpec_deal_purchaser_tipped_subject', 'wpec_gd_options', 'email_templates' );
        add_settings_field( 'wpec_deal_purchaser_tipped_body', __( 'Deal Purchaser - Deal Tipped {Body}', 'wpec-group-deals' ), 'wpec_deal_purchaser_tipped_body', 'wpec_gd_options', 'email_templates' );
        add_settings_field( 'wpec_deal_purchaser_untipped_subject', __( 'Deal Purchaser - Deal Untipped {Subject}', 'wpec-group-deals' ), 'wpec_deal_purchaser_untipped_subject', 'wpec_gd_options', 'email_templates' );
        add_settings_field( 'wpec_deal_purchaser_untipped_body', __( 'Deal Purchaser - Deal Untipped {Body}', 'wpec-group-deals' ), 'wpec_deal_purchaser_untipped_body', 'wpec_gd_options', 'email_templates' );
        add_settings_field( 'wpec_deal_purchaser_expired_subject', __( 'Deal Purchaser - Deal Expired {Subject}', 'wpec-group-deals' ), 'wpec_deal_purchaser_expired_subject', 'wpec_gd_options', 'email_templates' );
        add_settings_field( 'wpec_deal_purchaser_expired_body', __( 'Deal Purchaser - Deal Expired {Body}', 'wpec-group-deals' ), 'wpec_deal_purchaser_expired_body', 'wpec_gd_options', 'email_templates' );
        add_settings_field( 'wpec_deal_purchaser_new_deal_subject', __( 'Deal Purchaser - New Deal {Subject}', 'wpec-group-deals' ), 'wpec_deal_purchaser_new_deal_subject', 'wpec_gd_options', 'email_templates' );
        add_settings_field( 'wpec_deal_purchaser_new_deal_body', __( 'Deal Purchaser - New Deal {Body}', 'wpec-group-deals' ), 'wpec_deal_purchaser_new_deal_body', 'wpec_gd_options', 'email_templates' );
       
    }

// Add sub page to the Settings Menu
function wpec_gd_options_page() {
	add_options_page( __( 'WPEC Group Deal Options', 'wpec-group-deals' ), __( 'Group Deals', 'wpec-group-deals' ), 'administrator', 'wpec_gd_options', 'wpec_gd_options_page_form' );
}


/*
 * Settings API functions
 */
add_action( 'admin_print_scripts', 'wpec_settings_scripts' );
add_action( 'admin_print_styles', 'wpec_settings_styles' );

function wpec_settings_styles() {  
    wp_enqueue_style( 'thickbox' );
}

function wpec_settings_scripts() {  
    wp_register_script( 'wpec_group_deals_admin_script', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), '1.0' );
    wp_enqueue_script( 'wpec_group_deals_admin_script' );
    wp_enqueue_script( 'media-upload' ); 
    wp_enqueue_script( 'thickbox' ); 


}
function wpec_options_intro_text() {
    _e( 'Fill out your primary configuration settings here.', 'wpec-group-deals' );
}
function wpec_options_email_intro_text() {
    ?>
   <p><?php _e( 'There are several emails that will be automatically sent.  By default, they are plain-text emails.  Change the text or even input HTML templates based on your website (Hint: Click the "HTML" button).  Need some HTML email templates created?  <a href="http://groupdealsplugin.com/services/theming" target="_blank">We can help with that</a>.  Here is the <a href="#" class="list_email_tags">list of tags</a> to use in your template.', 'wpec-group-deals' ); ?></p>
   <p><?php _e( 'There are three separate events that will trigger an email.  When a new deal launches, when a deal tips and when a deal expires.  When a new deal launches, subscribers are emailed.  When a deal tips or expires; you, the business and the deal purchasers are all emailed.  Subjects and content for each of those emails are listed below.', 'wpec-group-deals' ); ?></p>
    <div class="wpec_email_tags">
        <dl class="border-around">
            <div class="list_container">
            <dt>[deal_title]</dt>
                <dd><?php _e( 'Shows deal title', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[deal_price]</dt>
                <dd><?php _e( 'Shows deal value', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[deal_image]</dt>
                <dd><?php _e( 'Shows deal image', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[deal_sale_price]</dt>
                <dd><?php _e( 'Shows actual deal price', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[deal_discount]</dt>
                <dd><?php _e( 'Shows actual deal discount as a percentage', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[deal_savings]</dt>
                <dd><?php _e( 'Shows actual deal savings as currency, e.g. $50.00', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[deal_link]</dt>
                <dd><?php _e( 'Shows link to deal', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[store_name]</dt>
                <dd><?php _e( 'Shows name of your site', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[login_url]</dt>
                <dd><?php _e( 'Displays user account login url', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[tipping_point]</dt>
                <dd><?php _e( 'Displays "tipping point", the number of sales necessary for the deal to become active', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[description]</dt>
                <dd><?php _e( 'Shows deal description', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[highlights]</dt>
                <dd><?php _e( 'Displays highlights of the deal', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[fine_print]</dt>
                <dd><?php _e( 'Displays fine print of the deal', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[company_info]</dt>
                <dd><?php _e( 'Displays description of vendor.', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[business_name]</dt>
                <dd><?php _e( 'Displays name of vendor.', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[location]</dt>
                <dd><?php _e( 'Shows deal location', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[business_address]</dt>
                <dd><?php _e( 'Displays address of vendor.', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[business_link]</dt>
                <dd><?php _e( 'Displays website URL of vendor.', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[business_map_link]</dt>
                <dd><?php _e( 'Displays link to Google Map of vendor address.', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[date]</dt>
                <dd><?php _e( 'Displays current date.', 'wpec-group-deals' ); ?></dd>
            </div>
    </dl>
   </div>
   <?php

}
function setting_textarea_fn() {
	$options = get_option( 'wpec_gd_options' );
	echo "<textarea id='plugin_textarea_string' name='wpec_gd_options[text_area]' rows='7' cols='50' type='textarea'>{$options['text_area']}</textarea>";
}
function wpec_options_img_width() {
	$options = get_option( 'wpec_gd_options_array' );
    
	echo "<input id='wpec_gd_img_width' name='wpec_gd_options_array[wpec_gd_img_width]' size='8' type='text' value='{$options['wpec_gd_img_width']}' />";
}
function wpec_options_img_height() {
	$options = get_option( 'wpec_gd_options_array' );
        if( !isset( $options['wpec_gd_img_height'] ) )
            $options['wpec_gd_img_height'] = '';
	echo "<input id='wpec_gd_img_height' name='wpec_gd_options_array[wpec_gd_img_height]' size='8' type='text' value='{$options['wpec_gd_img_height']}' />";
}
function wpec_options_crop_img() {
	$options = get_option( 'wpec_gd_options_array' );

        $checked = '';
	if( isset( $options['wpec_gd_crop_img'] ) && $options['wpec_gd_crop_img'] == "on" )
            $checked = ' checked="checked" ';

	echo "<input ".$checked." id='wpec_gd_crop_img' name='wpec_gd_options_array[wpec_gd_crop_img]' type='checkbox' />";
}
function wpec_paypal_email() {
	$options = get_option( 'wpec_gd_options_array' );
        if( !isset( $options['wpec_paypal_email'] ) )
            $options['wpec_paypal_email'] = '';
	echo "<input id='wpec_paypal_email' name='wpec_gd_options_array[wpec_paypal_email]' size='16' type='text' value='{$options['wpec_paypal_email']}' />";
}
function wpec_default_location() {

	$options = get_option( 'wpec_gd_options_array' );

        if( ! isset( $options['wpec_default_location'] ) )
            $options['wpec_default_location'] = '';

        $locations = get_terms( 'wpec-gd-location', 'hide_empty=0' );

        echo "<select id='wpec_default_location' name='wpec_gd_options_array[wpec_default_location]'>";

        foreach( $locations as $location ) {
            echo '<option value="' . $location->slug . '" ' . selected( $options['wpec_default_location'], $location->slug, false ) . '>' . $location->name . '</option>';
        }

        echo "</select>";
}
function wpec_default_page() {

	$options = get_option( 'wpec_gd_options_array' );

        if( ! isset( $options['gd_page'] ) )
            $options['gd_page'] = '';

            wp_dropdown_pages( 'name=wpec_gd_options_array[gd_page]&id=gd_page&show_option_none=None (Use static home page)&selected=' . $options['gd_page'] );

}
function wpec_location_threshold() {

	$options = get_option( 'wpec_gd_options_array' );

        if( ! isset( $options['location_threshold'] ) || ! isset( $options['location_unit'] ) )
            $options['location_threshold'] = $options['location_unit'] = '';

    echo "<input id='location_threshold' name='wpec_gd_options_array[location_threshold]' size='4' type='text' value='{$options['location_threshold']}' />";
    echo " <select id='location_unit' name='wpec_gd_options_array[location_unit]'>";
    echo "<option value='km' " . selected( 'km', $options['location_unit'], false ) . ">". _x( 'km', 'location unit drop-down', 'wpec-group-deals' ) ."</option>";
    echo "<option value='miles' " . selected( 'miles', $options['location_unit'], false ) . ">" . _x( 'miles', 'location unit drop-down', 'wpec-group-deals' ) . "</option>";
    echo "</select>";

}
function gd_api_id() {
    
    $options = get_option( 'wpec_gd_options_array' );
    if( !isset( $options['gd_api_id'] ) )
        $options['gd_api_id'] = '';
    echo "<input id='gd_api_id' name='wpec_gd_options_array[gd_api_id]' size='16' type='text' value='{$options['gd_api_id']}' />";

}

function gd_referral_credit() {
    $options = get_option( 'wpec_gd_options_array' );
    if( !isset( $options['referral_credit'] ) )
        $options['referral_credit'] = '';
    echo "<input id='referral_credit' name='wpec_gd_options_array[referral_credit]' size='16' type='text' value='{$options['referral_credit']}' />";

}

function gd_mobile_theme() {
	$options = get_option( 'wpec_gd_options_array' );

	if( ! isset( $options['gd_mobile_theme_enable'] ) )
	    $options['gd_mobile_theme_enable'] = '';

    echo "<input id='gd_mobile_theme_enable' name='wpec_gd_options_array[gd_mobile_theme_enable]' type='checkbox' " . checked( $options['gd_mobile_theme_enable'], 1 ) . "/>";

	$themes = get_themes();
	$current_theme = get_current_theme();
	$mobiletheme = $options['gd_mobile_theme'];
	
	if (count($themes) > 1) {
	    $theme_names = array_keys($themes);
	    natcasesort($theme_names); 
	    $html = '</td></tr><tr valign="top" id="gd_mobile_theme_picker"><th scope="row">' . __( 'Select Mobile Theme', 'wpec-group-deals' ) . '</th><td>';
	    $html .= '<select style="height:20px" id="gd_mobile_theme" name="wpec_gd_options_array[gd_mobile_theme]">' . "\n";
	    foreach ($theme_names as $theme_name) {              
	        if ( $mobiletheme == $theme_name )
	            $html .= '<option value="' . $theme_name . '" selected="selected">' . esc_html( $theme_name ) . '</option>' . "\n";
	        else
	            $html .= '<option value="' . $theme_name . '">' . esc_html( $theme_name ) . '</option>' . "\n";
	        
	    }
	    $html .= '</select>' . "\n\n";
	    $html .= '</td></tr>';
	}
	echo $html;
}



function gd_logo_upload() {
	$options = get_option( 'wpec_gd_options_array' );
    
    if( ! isset( $options['gd_logo_upload'] ) )
        $image = '';
        
     if ( ! empty( $options['gd_logo_upload'] ) )
         echo "<img class='gd-thumb-preview' src='" . esc_url( $options['gd_logo_upload'] ) . "' /><br /><br />";
     
         echo "<input id='gd_logo_upload' name='wpec_gd_options_array[gd_logo_upload]' size='16' type='text' value='" . esc_url( $options['gd_logo_upload'] ) . "' />";
     
     ?>
     <a href="media-upload.php?parent_page=settings&type=image&TB_iframe=1&width=640&height=566" rel='gd-image-upload' title="<?php _e( 'Upload Logo', 'wpec-group-deals' ); ?>" class="gd_logo_upload button-secondary"><?php _e( 'Upload Logo', 'wpec-group-deals' ); ?></a>
<?php
    }

    /**
     * Replaces "Insert into Post" with "Use Image as Logo"
     *
     * @param string $translated_text text that has already been translated (normally passed straight through)
     * @param string $source_text text as it is in the code
     * @param string $domain domain of the text
     * @return void
     */
    function wpec_gd_replace_settings_text( $translated_text, $source_text, $domain ) {
                    if ( 'Insert into Post' == $source_text ) {
                            return __( 'Use Image as Logo', 'wpec-group-deals');
                    }
            return $translated_text;
    }

function gd_media_upload_url( $form_action_url ) {

    $form_action_url = esc_url( add_query_arg( array( 'parent_page' => 'settings' ) ) );

    return $form_action_url;

}

function add_editor_object() {
    ?>
     
     <script type="text/javascript">
         
         
                    window.send_to_editor = function(html) {

                      imgURL = $('img',html).attr('src');

                      $('#' + formfield).val(imgURL);

                      tb_remove();  
	}

     </script>
    <?php
}

function wpec_gd_settings_overrides() {

    if( isset( $_REQUEST['parent_page'] ) && 'settings' == $_REQUEST['parent_page'] ) {
        add_filter( 'gettext', 'wpec_gd_replace_settings_text', 12, 3 );
        add_filter( 'media_upload_form_url', 'gd_media_upload_url', 9, 1 );
        add_action( 'admin_head', 'add_editor_object' );

    }

}
add_action( 'init', 'wpec_gd_settings_overrides' );


/*
 * Settings Callback Functions for all the emails.
 * @todo Refactor in a class that walks through function
 */

add_filter( 'teeny_mce_buttons', 'wpec_code_mce' );
function wpec_code_mce( $buttons ) {

    $buttons[] = 'code';

    return $buttons;
}
function wpec_site_owner_tipped_subject() {
    $option = get_option( 'site_owner_tipped' );

    echo "<input style='width:100%' id='site_owner_tipped_subject' name='site_owner_tipped[subject]' type='text' value='{$option['subject']}' />";

}
function wpec_site_owner_tipped_body() {

$option = get_option( 'site_owner_tipped' );
wp_tiny_mce( true , // true makes the editor "teeny"
	array(
		"editor_selector" => "wpec_gd_tiny_mce_1",
                "height" => 150,
                "theme" => 'advanced',
            	)
);

?>

<textarea class="wpec_gd_tiny_mce_1" id="site_owner_tipped_body" name="site_owner_tipped[body]"><?php echo $option["body"]; ?></textarea>

<?php
}

function wpec_site_owner_untipped_subject() {
    $option = get_option( 'site_owner_untipped' );

    echo "<input style='width:100%' id='site_owner_untipped_subject' name='site_owner_untipped[subject]' type='text' value='{$option['subject']}' />";

}
function wpec_site_owner_untipped_body() {

$option = get_option( 'site_owner_untipped' );
wp_tiny_mce( true , // true makes the editor "teeny"
	array(
		"editor_selector" => "wpec_gd_tiny_mce_2",
                "height" => 150,
                "theme" => 'advanced',
            	)
);
?>
<textarea class="wpec_gd_tiny_mce_2" id="site_owner_untipped_body" name="site_owner_untipped[body]"><?php echo $option["body"]; ?></textarea>
<?php
}

function wpec_site_owner_expired_subject() {
    $option = get_option( 'site_owner_expired' );

    echo "<input style='width:100%' id='site_owner_expired_subject' name='site_owner_expired[subject]' type='text' value='{$option['subject']}' />";

}
function wpec_site_owner_expired_body() {

$option = get_option( 'site_owner_expired' );

wp_tiny_mce( true , // true makes the editor "teeny"
	array(
		"editor_selector" => "wpec_gd_tiny_mce_3",
                "height" => 150,
                "theme" => 'advanced',
            	)
);
?>
<textarea class="wpec_gd_tiny_mce_3" id="site_owner_expired_body" name="site_owner_expired[body]"><?php echo $option["body"]; ?></textarea>
<?php
}

function wpec_business_owner_tipped_subject() {
    $option = get_option( 'business_owner_tipped' );

    echo "<input style='width:100%' id='business_owner_tipped_subject' name='business_owner_tipped[subject]' type='text' value='{$option['subject']}' />";

}
function wpec_business_owner_tipped_body() {

$option = get_option( 'business_owner_tipped' );

wp_tiny_mce( true , // true makes the editor "teeny"
	array(
		"editor_selector" => "wpec_gd_tiny_mce_4",
                "height" => 150,
                "theme" => 'advanced',
            	)
);
 ?>
<textarea class="wpec_gd_tiny_mce_4" id="business_owner_tipped_body" name="business_owner_tipped[body]"><?php echo $option["body"]; ?></textarea>
<?php
}

function wpec_business_owner_untipped_subject() {
    $option = get_option( 'business_owner_untipped' );

    echo "<input style='width:100%' id='business_owner_untipped_subject' name='business_owner_untipped[subject]' type='text' value='{$option['subject']}' />";

}
function wpec_business_owner_untipped_body() {

$option = get_option( 'business_owner_untipped' );

wp_tiny_mce( true , // true makes the editor "teeny"
	array(
		"editor_selector" => "wpec_gd_tiny_mce_5",
                "height" => 150,
                "theme" => 'advanced',
            	)
);
 ?>
<textarea class="wpec_gd_tiny_mce_5" id="business_owner_untipped_body" name="business_owner_untipped[body]"><?php echo $option["body"]; ?></textarea>
<?php
}

function wpec_business_owner_expired_subject() {
    $option = get_option( 'business_owner_expired' );

    echo "<input style='width:100%' id='business_owner_expired_subject' name='business_owner_expired[subject]' type='text' value='{$option['subject']}' />";

}
function wpec_business_owner_expired_body() {

$option = get_option( 'business_owner_expired' );

wp_tiny_mce( true , // true makes the editor "teeny"
	array(
		"editor_selector" => "wpec_gd_tiny_mce_6",
                "height" => 150,
                "theme" => 'advanced',
            	)
);
 ?>
<textarea class="wpec_gd_tiny_mce_6" id="business_owner_expired_body" name="business_owner_expired[body]"><?php echo $option["body"]; ?></textarea>
<?php
}

function wpec_deal_purchaser_tipped_subject() {
    $option = get_option( 'deal_purchaser_tipped' );

    echo "<input style='width:100%' id='deal_purchaser_tipped_subject' name='deal_purchaser_tipped[subject]' type='text' value='{$option['subject']}' />";

}
function wpec_deal_purchaser_tipped_body() {

$option = get_option( 'deal_purchaser_tipped' );

wp_tiny_mce( true , // true makes the editor "teeny"
	array(
		"editor_selector" => "wpec_gd_tiny_mce_7",
                "height" => 150,
                "theme" => 'advanced',
            	)
);
 ?>
<textarea class="wpec_gd_tiny_mce_7" id="deal_purchaser_tipped_body" name="deal_purchaser_tipped[body]"><?php echo $option["body"]; ?></textarea>
<?php
}

function wpec_deal_purchaser_untipped_subject() {
    $option = get_option( 'deal_purchaser_untipped' );

    echo "<input style='width:100%' id='deal_purchaser_untipped_subject' name='deal_purchaser_untipped[subject]' type='text' value='{$option['subject']}' />";

}
function wpec_deal_purchaser_untipped_body() {

$option = get_option( 'deal_purchaser_untipped' );

wp_tiny_mce( true , // true makes the editor "teeny"
	array(
		"editor_selector" => "wpec_gd_tiny_mce_8",
                "height" => 150,
                "theme" => 'advanced',
            	)
);
 ?>
<textarea class="wpec_gd_tiny_mce_8" id="deal_purchaser_untipped_body" name="deal_purchaser_untipped[body]"><?php echo $option["body"]; ?></textarea>
<?php
}

function wpec_deal_purchaser_expired_subject() {
    $option = get_option( 'deal_purchaser_expired' );

    echo "<input style='width:100%' id='deal_purchaser_expired_subject' name='deal_purchaser_expired[subject]' type='text' value='{$option['subject']}' />";

}
function wpec_deal_purchaser_expired_body() {

$option = get_option( 'deal_purchaser_expired' );

wp_tiny_mce( true , // true makes the editor "teeny"
	array(
		"editor_selector" => "wpec_gd_tiny_mce_9",
                "height" => 150,
                "theme" => 'advanced',
            	)
);
 ?>
<textarea class="wpec_gd_tiny_mce_9" id="deal_purchaser_expired_body" name="deal_purchaser_expired[body]"><?php echo $option["body"]; ?></textarea>
<?php
}

function wpec_deal_purchaser_new_deal_subject() {
    $option = get_option( 'deal_purchaser_new_deal' );

    echo "<input style='width:100%' id='deal_purchaser_new_deal_subject' name='deal_purchaser_new_deal[subject]' type='text' value='{$option['subject']}' />";

}
function wpec_deal_purchaser_new_deal_body() {

$option = get_option( 'deal_purchaser_new_deal' );

wp_tiny_mce( true , // true makes the editor "teeny"
	array(
		"editor_selector" => "wpec_gd_tiny_mce_10",
                "height" => 150,
                "theme" => 'advanced',
            	)
);
; ?>
<textarea class="wpec_gd_tiny_mce_10" id="deal_purchaser_new_deal_body" name="deal_purchaser_new_deal[body]"><?php echo $option["body"]; ?></textarea>
<?php
}

function facebook_api_id() {

    $facebook_api_id = get_option( 'social_settings' );

    if( isset( $facebook_api_id["facebook_api_key"] ) )
        $facebook_api_id = $facebook_api_id["facebook_api_key"];
    else
       $facebook_api_id = '';

    echo '<input type="text" name="social_settings[facebook_api_key]" value="'.$facebook_api_id.'" />';
}
function facebook_app_secret() {

    $facebook_app_secret = get_option( 'social_settings' );

    if( isset( $facebook_app_secret["facebook_app_secret"] ) )
        $facebook_app_secret = $facebook_app_secret["facebook_app_secret"];
    else
        $facebook_app_secret = '';

    echo '<input type="text" name="social_settings[facebook_app_secret]" value="'.$facebook_app_secret.'" />';
}

/*
 * Until the Settings API allows for multiple groups on one page, we have to be hacky.
 */
function wpec_gd_options_page_form() {
?>
    <div class="wrap">
        <div class="icon32" id="icon-options-general"><br></div>
        <h2><?php _e( 'WPEC Group Deal Options', 'wpec-group-deals' ); ?> </h2>
        <p><?php _e( 'Configuring your group deals website properly is very important - make sure you go through all of these fields carefully.', 'wpec-group-deals' ); ?></p>
        <form action="options.php" method="post">
            
        <?php settings_fields( 'wpec_gd_main_options' ); ?>
        <h3><?php _e( 'Main Settings', 'wpec-group-deals' ); ?></h3>
        <p><?php  _e( 'Fill out your primary configuration settings here.', 'wpec-group-deals' ); ?></p>
            <table class="form-table">
                <?php do_settings_fields( 'wpec_gd_options', 'main_section' ); ?>
            </table>

            <p class="submit">
                <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Main Options', 'wpec-group-deals' ); ?>" />
            </p>
            
        </form>

        <form action="options.php" method="post">

        <?php settings_fields( 'wpec_gd_emails' ); ?>
         <h3><?php _e( 'Email Templates', 'wpec-group-deals' ); ?></h3>
         <p><?php _e( 'There are several emails that will be automatically sent.  By default, they are plain-text emails.  Change the text or even input HTML templates based on your website (Hint: Click the "HTML" button).  Need some HTML email templates created?  <a href="http://groupdealsplugin.com/services/theming" target="_blank">We can help with that</a>.  Here is the <a href="#" class="list_email_tags">list of tags</a> to use in your template.', 'wpec-group-deals' ); ?></p>
   <p><?php _e( 'There are three separate events that will trigger an email.  When a new deal launches, when a deal tips and when a deal expires.  When a new deal launches, subscribers are emailed.  When a deal tips or expires; you, the business and the deal purchasers are all emailed.  Subjects and content for each of those emails are listed below.', 'wpec-group-deals' ); ?></p>
    <div class="wpec_email_tags">
        <dl class="border-around">
            <div class="list_container">
            <dt>[deal_title]</dt>
                <dd><?php _e( 'Shows deal title', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[deal_price]</dt>
                <dd><?php _e( 'Shows deal value', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[deal_image]</dt>
                <dd><?php _e( 'Shows deal image', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[deal_sale_price]</dt>
                <dd><?php _e( 'Shows actual deal price', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[deal_discount]</dt>
                <dd><?php _e( 'Shows actual deal discount as a percentage', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[deal_savings]</dt>
                <dd><?php _e( 'Shows actual deal savings as currency, e.g. $50.00', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[deal_link]</dt>
                <dd><?php _e( 'Shows link to deal', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[store_name]</dt>
                <dd><?php _e( 'Shows name of your site', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[login_url]</dt>
                <dd><?php _e( 'Displays user account login url', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[tipping_point]</dt>
                <dd><?php _e( 'Displays "tipping point", the number of sales necessary for the deal to become active', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[description]</dt>
                <dd><?php _e( 'Shows deal description', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[highlights]</dt>
                <dd><?php _e( 'Displays highlights of the deal', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[fine_print]</dt>
                <dd><?php _e( 'Displays fine print of the deal', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[company_info]</dt>
                <dd><?php _e( 'Displays description of vendor.', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[business_name]</dt>
                <dd><?php _e( 'Displays name of vendor.', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[date]</dt>
                <dd><?php _e( 'Displays current date.', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[location]</dt>
                <dd><?php _e( 'Shows deal location', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[business_address]</dt>
                <dd><?php _e( 'Displays address of vendor.', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[business_link]</dt>
                <dd><?php _e( 'Displays website URL of vendor.', 'wpec-group-deals' ); ?></dd>
            </div>
            <div class="list_container">
            <dt>[business_map_link]</dt>
                <dd><?php _e( 'Displays link to Google Map of vendor address.', 'wpec-group-deals' ); ?></dd>
            </div><div class="list_container">
            <dt>[qr_code]</dt>
                <dd><?php _e( 'Shows a QR code linking to deal redemption.  Expired deal emails only.', 'wpec-group-deals' ); ?></dd>
            </div>
    </dl>
   </div>
        <table class="form-table">
            <?php do_settings_fields( 'wpec_gd_options', 'email_templates' ); ?>
        </table>

            <p class="submit">
                <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Email Templates', 'wpec-group-deals' ); ?>" />
            </p>
        </form>

        <form action="options.php" method="post">

        <?php settings_fields( 'social_settings' ); ?>
         <h3><?php _e( 'Social Media Configuration', 'wpec-group-deals' ); ?></h3>
         <p><?php _e( "We're packed full of social media integration!  You can like, tweet, share and connect however you like.  In order for everything to work, it needs to be properly configured.  You definitely need to get a Facebook API key, here's <a href='http://www.youtube.com/watch?v=jYqx-RtmkeU&feature=player_embedded'>a great tutorial</a> on how to do just that.", 'wpec-group-deals' );
 ?></p>
        <table class="form-table">
            <?php do_settings_fields( 'wpec_gd_options', 'facebook_section' ); ?>
        </table>

            <p class="submit">
                <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Social Options', 'wpec-group-deals' ); ?>" />
            </p>
        </form>
    </div>
<?php
}

// Validate user data for some/all of your input fields
function wpec_validate_options($input) {

	// Check our textbox option field contains no HTML tags - if so strip them out
	//$input['text_string'] =  wp_filter_nohtml_kses($input['text_string']);

    return $input;
}
function wpec_validate_emails($input) {

    return $input;
}


?>
jQuery(document).ready(function($) {

    $( 'div.wpec_email_tags' ).hide();
    $( 'a.list_email_tags' ).click(function(e){
		e.preventDefault();
        $('div.wpec_email_tags').toggle("slow");
    });
    
	if( $('#gd_mobile_theme_enable').is(':checked') )
		$("#gd_mobile_theme_picker").show();
	else
		$("#gd_mobile_theme_picker").hide();

	$('#gd_mobile_theme_enable').live('click',function(){
		if( $(this).is(':checked') )
			$("#gd_mobile_theme_picker").show();
		else
			$("#gd_mobile_theme_picker").hide();
	});   
    
    var upload_title = $('a.gd_logo_upload').text();
    var formfield = "";
    
	$('a[rel="gd-image-upload"]').click(function() {
		formfield = "gd_logo_upload";
		tb_show(upload_title, 'media-upload.php?parent_page=settings&type=image&TB_iframe=1&width=640&height=566');
		return false;
	});
    
	if( 'settings_page_wpec_gd_options' == pagenow ) {
		window.send_to_editor = function(html) {
			imgURL = $('img', html).attr('src');
			$('#' + formfield).val(imgURL);
			tb_remove();  
		}
	}
	if( $('#forever').is(':checked') ) {
			$("#exptime").hide(); 
			$('#exptime_options').show();
		} else {
			$("#exptime").show();
			$('#exptime_options').hide();
		}
		$('#forever').live('click', function(){
			if( $(this).is(':checked') ) {
				$("#exptime").hide('fast');
				$('#exptime_options').show('fast');
			} else {
				$("#exptime").show('fast');
				$('#exptime_options').hide('fast');
			}
		});
		
		$('input[name="email_vendor_list"]').click(function(){
			$(this).after('<img src="images/wpspin_light.gif" />');
			$.post( ajaxurl, { 
						"action" : "send_vendor_emails", 
						"_ajax_nonce": $('#_send_vendor_email_nonce').val(),
						"post_id" : $('#post_ID').val()
					},
				function(response) {
					var div_class;
					div_class = response.errors ? 'gd_ajax_message error' : 'gd_ajax_message success';
					$('#exptime_options img').remove();
					$('input[name="email_vendor_list"]').after('<div class="' + div_class + '">' + response.data + '</div>');
					$('div.gd_ajax_message').delay(4500).fadeOut('slow');
				},
				'json'
			);
		});
		
		$('input[name="send_new_deal_email"]').click(function(){
			$(this).after('<img src="images/wpspin_light.gif" />');
			$.post( ajaxurl, { 
						"action" : "send_subscriber_emails", 
						"_ajax_nonce": $('#_send_subscriber_email_nonce').val(),
						"post_id" : $('#post_ID').val()
					},
				function(response) {
					var div_class;
					div_class = response.errors ? 'gd_ajax_message error' : 'gd_ajax_message success';
					$('#email_deal_to_subscribers img').remove();
					$('input[name="send_new_deal_email"]').after('<div class="' + div_class + '">' + response.data + '</div>');
					$('div.gd_ajax_message').delay(3000).fadeOut('slow');
				},
				'json'
			);
		});
        
});

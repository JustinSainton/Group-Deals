jQuery(document).ready(function($) {
	// prepare Options Object 
	var options = { 
		url:			ajaxregistration.Ajax_Url,
		dataType:		'json',
		data:			 { "action" : "submitajaxregistration", "_ajax_nonce": $('#_registration_nonce', this).val() },
		type:			'post',
		beforeSubmit:	function(arr, $form, options) {
							$('.registration-status-message').html('').removeClass('error success').trigger('class_added').html(gd_object.sending_data);
							$('.ajax-submit' ).attr("disabled", true);
					},
		success:		function(response, status) {
			
							if(response.errors) {
								$('.registration-status-message').html('').addClass('error');
								$.each(response.errors, function(i) {
									var error_code = i;
									$.each(this, function(ii,e) {
										$('.registration-status-message').append(e + '<br />').trigger('class_added');
									});
								});
							} else {
								$('.registration-status-message').addClass('success').html('').append(response.data).trigger('class_added').show().delay(6500).fadeOut('slow', function(){
									window.location.replace(response.url);
								});
							}
							
							$('.ajax-submit' ).attr("disabled", false);
						}
	}; 
	
	$('form.ajax-registration-form').ajaxForm(options);
    $('a.reg_toggle, span.close').click(function(e) {
        e.preventDefault();
        $('#drawer').slideToggle('slow');
    });
});
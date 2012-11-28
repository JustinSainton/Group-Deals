jQuery(document).ready(function($) {

    /**
     * Modifies shopping cart quantities on page load and on qty change.  
     */

    function max_qty_change() {
        
        var sub_total;
        var total;
        var woohoo = gd_object.woohoo;
        var credits;
        var quantity = $('select.max_qty').val();
        var unit_price = $('div.subtotal_box span.column span.value:first').text();
        var garbage = gd_object.decimals + '00';
        var credits_float = accounting.unformat( $('div.credits span.value').text(), gd_object.decimals );
        
        unit_price = accounting.unformat( unit_price, gd_object.decimals );
        hidden_unit = accounting.unformat( $('div.credits span.hidden').text(), gd_object.decimals );
        
        if( credits_float != hidden_unit )
            credits_float = hidden_unit;

        sub_total_float = ( quantity * unit_price );

        sub_total = accounting.formatMoney(sub_total_float, gd_object.currency_symbol, 2, gd_object.thousands, gd_object.decimals);
        sub_total = sub_total.replace( garbage, '' );

        total = ( sub_total_float - credits_float );

        if( $('p em.woohoo').length )
            $('p em.woohoo').remove();

        if( sub_total_float < credits_float ) {

            total_credits = $('div.credits span.value').text();
            credits_remaining = accounting.formatMoney( ( credits_float - sub_total_float ), gd_object.currency_symbol, 2, gd_object.thousands, gd_object.decimals );
            credits_remaining = credits_remaining.replace( garbage, '' );

        $('div.credits span.value').fadeOut('fast', function(){
                $(this).text(sub_total).fadeIn('fast');
                $('div.total').after('<p><em class="woohoo" /></p>');
                $('p em.woohoo').hide().html(woohoo);
                $('p em.woohoo span.first_value').text(sub_total);
                $('p em.woohoo span.second_value').text(total_credits);
                $('p em.woohoo').fadeIn('fast');
            });

            total = 0;
        } else {
            $('div.credits span.value').fadeOut('fast', function(){
                $(this).text( $('div.credits span.hidden').text() ).fadeIn('fast');
            });
        }

        post_credits = (sub_total_float < credits_float) ? sub_total_float : credits_float;

        $.post( 
            ajaxregistration.Ajax_Url, 
            { 
                "action" : "qtychange", 
                "_ajax_nonce": $('#_qty_change_nonce').val(), 
                "key" : '0', 
                "quantity" : quantity,
                "credits" : post_credits
            }
            );

        total = accounting.formatMoney( total, gd_object.currency_symbol, 2, gd_object.thousands, gd_object.decimals );
        
        total = total.replace( garbage, '' );

        $('div.subtotal_box span.column:eq(4) span.value').fadeOut('fast', function(){
            $(this).text(sub_total).fadeIn('fast');
        });

        $('div.total span.pricedisplay').fadeOut('fast', function(){
            $(this).text(total).fadeIn('fast');
        });

    }

    max_qty_change();

    $('select.max_qty').change(function(){
        max_qty_change();
    });

     $('input#user_pass').keypress(function(event){
         if( event.keyCode == 13 ) {
            event.preventDefault();
            $('input#wp-submit').click();
         }
     });

    /** 
     * Submits login attempt via AJAX.  
     * Returns errors on error.  
     * @todo Returns normal checkout process if user has full name and ZIP filled out
     * @todo Returns normal checkout + ZIP full name fields if meta doesn't exist.  One time only.
     */

    $('p.login-submit').delegate( 'input', 'click submit', function(e){

        e.preventDefault();
        $('p.error').fadeOut(350).remove();
        $.post( 
            ajaxregistration.Ajax_Url, 
            { 
                "action" : "process_gd_login", 
                "_ajax_nonce": $('#_gd_login_nonce').val(),
                "email" : $('input#user_login').val(),
                "password" : $('input#user_pass').val(),
            },
            function(data) {
                //There are errors, return them.
                if(data.errors) {

                    $.each(data.errors, function(i) {
                        var error_code = i;
                        $.each(this, function(ii,e) {
                            $('p.login-password').after('<p class="error">' + e + '</p>');
                        });
                    });

                    // If we have no ZIP, we have no meta and need to gather it.  Add meta fields, slide billing over, add credits, logout link
                } else if( typeof data.zip == 'undefined' ) {

                        $('ul.account_links').fadeOut('slow').load( document.URL + ' ul.account_links', function(response, status, xhr) {
                            $(this).fadeIn();
                        });
                        $("div.left_part").animate(
                            { height: 'toggle' }, 
                            { duration: 500, queue: false }
                        );
                        
                        $("div.billing_info").show('fast').animate(
                            { height: 'auto' }, 
                            { duration: 500, queue: false }
                        );

                        $.each(data, function(key, value){
                            $('input[name="collected_data[' + key + ']"], textarea[name="collected_data[' + key + ']"]').val(value).removeClass('intra-field-label');
                        });


                    // No errors, we have meta...let's populate the meta, credits, logout link, billing option, etc.
                } else {
                    
                        $('ul.account_links').fadeOut('slow').load( document.URL + ' ul.account_links', function(response, status, xhr) {
                            $(this).fadeIn();
                        });
                        $("div.left_part").animate(
                            { height: 'toggle' }, 
                            { duration: 500, queue: false }
                        );
                        
                        $("div.billing_info").show('fast').animate(
                            { height: 'auto' }, 
                            { duration: 500, queue: false }
                        );
                         $.each(data, function(key, value){
                            $('input[name="collected_data[' + key + ']"], textarea[name="collected_data[' + key + ']"]').val(value);
                        });

                    }
            },
            'json'
            );
    });

    /**
     * Basic form maniupulation functions.  Buy buttons and email addresses and such
     */

    $('form.product_form_dd a#buy_btn').live('click', function(e){
        e.preventDefault();
        $('form.product_form_dd').submit();
    });

    $('form#ajax-registration-form input.email').live('focus', function(e){

        if( $(this).val() == 'Enter your email address...' )
            $(this).val('');
    });

    $('form#ajax-registration-form input.email').live('blur', function(e){

        if( $(this).val() == '' )
            $(this).val('Enter your email address...');
    });
    
    /**
     * Handles manipulation required to have the Create Account information used for standard data
     * Hides all contact information except street address - we'll get everything else from name/ZIP
     * 
     */
    $('div.personal_info input#full_name').live('blur change', function(e){

        var full_name;
        var first_name;
        var last_name;

        full_name = $(this).val().split(' ');
        last_name = full_name.pop();
        first_name = full_name.join(' ');

        $('input[title="billingfirstname"]').val(first_name).removeClass('intra-field-label');
        $('input[title="billinglastname"]').val(last_name).removeClass('intra-field-label');
    
    });

    $('div.personal_info input#signup_email').live('blur change', function(e){
        $('input[title="billingemail"]').val($(this).val()).removeClass('intra-field-label');   
    });

    $('div.personal_info input#zip_code').live('blur change', function(e){

        $('input[title="billingpostcode"]').val($(this).val()).removeClass('intra-field-label');
            
        if( $('input[title="billingcity"]').parents('tr').find('span.asterix').length > 0 ) {
            $.post( 
                ajaxregistration.Ajax_Url, 
                { 
                    "action" : "get_zip", 
                    "zip_code" : $('input#zip_code').val()
                },
                function(data) {
                    $('input[title="billingcity"]').val(data.city).removeClass('intra-field-label');

                    if( $('input[title="billingstate"]').length > 0 )
                        $('input[title="billingstate"]').val(data.state).removeClass('intra-field-label');

                    if( $('select[title="billingstate"]').length > 0 )
                        $('select[title="billingstate"]').val(data.state).removeClass('intra-field-label');

                    if( $('input[title="billingcountry"]').length > 0 )
                        $('input[title="billingcountry"]').val(data.country).removeClass('intra-field-label');

                    if( $('select[title="billingcountry"]').length > 0 )
                        $('select[title="billingcountry"]').val(data.country).removeClass('intra-field-label');
                },
                'json'
                );
        } 
    });

    

    /**
     * Handles disabling Buy Now button on expiration
     */
    if (typeof progressbar != "undefined") {

    function disableBuy() {
       $('form.product_form_dd a#buy_btn').attr( 'disabled', 'disabled' );
       $('form.product_form_dd a#buy_btn').click(function(){
           return false;
       });
       $('form.product_form_dd a#buy_btn').addClass('gd_sold_out');
       $('form.product_form_dd a#buy_btn').text(gd_object.soldout);

        return false;
    };

    if( gd_object.in_stock == '' ) {
        disableBuy();
    }

    var date_format = '';
    var date_layout = '';
    var dealExpires = new Date();
    var plus_one_day = new Date();
    plus_one_day.setDate(plus_one_day.getDate()+1);
    dealExpires = new Date(progressbar.dealExpiresCountdown);
	if ( dealExpires > plus_one_day  ) {
		date_format = 'DHM';
        date_layout = '<ul>{o<}<li>{on} {ol}</li>{o>}' +
    '{d<}<li>{dn} {dl}</li>{d>}{h<}<li>{hn} {hl}</li>{h>}' +
    '{m<}<li>{mn} {ml}</li>{m>}</ul>';
	} else {
		date_format = 'HMS';
		date_layout = '<ul>{h<}<li>{hn} {hl}</li>{h>}' +
    '{m<}<li>{mn} {ml}</li>{m>}{s<}<li>{sn} {sl}</li>{s>}</ul>';
		}
    $('ul#counter li#ajax').countdown({until: dealExpires, format: date_format,
    layout: date_layout, alwaysExpire: true, onExpiry: disableBuy });
    $(".progress_bar").progressBar(progressbar.tippingPoint, { showText: false, width : 198, barImage : progressbar.img, boxImage : progressbar.boximg } );

}
    /**
     * Handles Google Map display
     */
    if ( typeof gd_object != "undefined" && $('#wpec_google_map').length )  {

        var geocoder;
        var map;
        var address = gd_object.address;
        geocoder = new google.maps.Geocoder();
     
        var myOptions = {
          zoom: 12,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        }

        map = new google.maps.Map(document.getElementById("wpec_google_map"), myOptions);

         geocoder.geocode( { 'address': address }, function(results, status) {
                map.setCenter(results[0].geometry.location);
                var marker = new google.maps.Marker({
                    map: map,
                    position: results[0].geometry.location
                });
            });

    }

    /* General Layout manipulations for the checkout page.  Some shuffling aroung, AJAX sign-in/up and so on */
    function show_or_hide_personal_info() {
        
        if( $('div.personal_info input[type="radio"]:eq(0)').is(':checked') ) {
            $('div.personal_info div.wpsc_registration_form:eq(0)').slideDown(300);
            $('div.billing_info').slideUp(300);
        } else {
            $('div.personal_info div.wpsc_registration_form:eq(0)').slideUp(300);
            $('div.billing_info').slideDown(300);
        }
        
        if( $('div.personal_info input[type="radio"]:eq(1)').is(':checked') )
            $('div.personal_info div.wpsc_registration_form:eq(1)').slideDown(300);
        else
            $('div.personal_info div.wpsc_registration_form:eq(1)').slideUp(300);
    }       
    show_or_hide_personal_info();
    $('div.personal_info input[type="radio"]').click(show_or_hide_personal_info);

    $('form.wpsc_checkout_forms').submit(function(e){
            
            e.preventDefault();

            errors = false;
            
            var errors;
            $('p.error').remove();

            if( $('input#password').val() != $('input#password_confirm').val() && $('input#password').is( ":visible" ) ) {
                $('<p class="error" style="white-space:nowrap">Passwords do not match.</p>').insertAfter($('input#password_confirm'));
                errors = true;
            }

            if( ( $('input#password').val() == '' || $('input#password_confirm').val() == '' ) && $('input#password').is( ":visible" ) ) {
                $('<p class="error" style="white-space:nowrap">Passwords cannot be empty.</p>').insertAfter($('input#password_confirm'));
                errors = true;
            }

            $('table.wpsc_checkout_table tr:has(span)').each(function(i,e){
                if( $( 'input, textarea', this).hasClass('intra-field-label') || $( 'input, textarea', this).val() == '' ) {
                    $('<p class="error" style="white-space:nowrap">This field is required.</p>').appendTo( $('td:eq(1)', this) );
                    errors = true;
                }

            });

            if( errors )
                return false;

            //Client-side input has been validated, now we need to verify that the user doesn't already exist - if it does not, create user, sign in, proceed with purchase.
            $.ajaxSetup({
                async : false
            });

            var $form = $(this);
            
            $.post( 
                ajaxregistration.Ajax_Url, 
                { 
                    "action" : "process_gd_signup", 
                    "_signup_ajax_nonce": $('#_gd_signup_nonce').val(),
                    "full_name" : $('#sign_up_set input#full_name').val(),
                    "email" : $('#sign_up_set input#signup_email').val(),
                    "password" : $('#sign_up_set input#password').val(),
                    "collected_data" : $('table.wpsc_checkout_table input, table.wpsc_checkout_table textarea, table.wpsc_checkout_table select').serialize()
                },
                function(data) {
                //There are errors, return them.
                    if(data.errors) {
                        $('label[for="sign_up"]').after('<p class="error">' + data.errors + '</p>');
                        return false;
                    } else {
                       $form.unbind('submit').submit();
                    }
            },
            'json'
            );

        });

});
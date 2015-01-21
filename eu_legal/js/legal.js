/**
 * EU Legal - Better security for German and EU merchants.
 *
 * @version   : 1.0.2
 * @date      : 2014 08 26
 * @author    : Markus Engel/Chris Gurk @ Onlineshop-Module.de | George June/Alexey Dermenzhy @ Silbersaiten.de
 * @copyright : 2014 Onlineshop-Module.de | 2014 Silbersaiten.de
 * @contact   : info@onlineshop-module.de | info@silbersaiten.de
 * @homepage  : www.onlineshop-module.de | www.silbersaiten.de
 * @license   : http://opensource.org/licenses/osl-3.0.php
 * @changelog : see changelog.txt
 * @compatibility : PS == 1.6.0.9
 */

var legal = {
    paymentChosen: '',
    
    tosApproved: false,
    revocationTermsApproved: true,
    
    init: function(){
	$('[data-hide-if-js]').hide();
	$('[data-show-if-js]').show();
	$('[data-remove-if-js]').remove();
	
	this.bindPaymentOptionClick();
	
	is_partially_virtual = typeof(is_partially_virtual) != 'undefined' ? is_partially_virtual : false;
	
	if (is_partially_virtual) {
	    this.revocationTermsApproved = false;
	}
	
	if (this.localStorageEnabled()) {
	    var pref = localStorage.getItem('preferredPaymentMethod');
	    
	    if (pref) {
		var radio = $('#choose_' + pref);
		
		if (radio) {
		    radio.prop('checked', true);
		    
		    this.paymentChosen = pref;
		    this.toggleChosenForm(true);
		}
	    }
	}
	
	$(document).on('change', '#cgv', function(){
	    legal.tosApproved = $(this).is(':checked');
	    legal.updateConfirmButton();
	});
	
	$(document).on('change', '#revocation_terms_aggreed', function(){
	    legal.revocationTermsApproved = $(this).is(':checked');
	    legal.updateConfirmButton();
	});
	
	$(document).ready( function(){
		var cgv = $("#cgv");
		if (cgv.length == 0)
            legal.tosApproved = true;
        else
            legal.tosApproved = cgv.is(":checked");

	    legal.updateConfirmButton();
	});
	
	if (!!$.prototype.fancybox){
	    $('a.iframe').fancybox({
		'type': 'iframe',
		'width': 600,
		'height': 600
	    });
	}
	
	this.bindAjaxHandlers();
    },
    
    bindPaymentOptionClick: function() {
	$(document).on('change click', 'input:radio[name=payment_option], .payment-option', function(evt){
	    evt.preventDefault();
	    // Hide currently displayed form if there is one
	    legal.toggleChosenForm(false);
		
		var val = $(this).val();
		//handle row click instead of direct radio button
		if( $(this).hasClass("payment-option") )
		{
			var $rowClickedInput = $(this).find("input:radio[name=payment_option]");
			$rowClickedInput.prop('checked',true);
			val = $rowClickedInput.val();
			$.uniform.update("input[name=payment_option]");
		}
		
	    if (val) {
			legal.paymentChosen = val;
			// Display form if there is one
			legal.toggleChosenForm(true);

			if (legal.localStorageEnabled()) {
				localStorage.setItem('preferredPaymentMethod', val);
			}
	    }
	    else {
			legal.paymentChosen = '';
	    }

	    legal.updateConfirmButton();
	});
    },
    
    localStorageEnabled: function() {
	var mod = "psCheckIfLocalStorageEnabled";
	
	try {
	    localStorage.setItem(mod, mod);
	    localStorage.removeItem(mod);
	    return true;
	} catch(e) {
	    return false;
	}
    },
    
     confirmOrder: function() {
        if (this.paymentChosen && this.tosApproved && this.revocationTermsApproved) {
            $('#' + this.paymentChosen + '_payment form').submit();
        } else {
            if (!this.paymentChosen) {
                if (typeof txtNoPaymentMethodIsSelected !== 'undefined') {
                    alert(txtNoPaymentMethodIsSelected);
					$('html, body').animate({
						scrollTop: $('#HOOK_PAYMENT').offset().top + 'px'
					}, 'fast');
                }
            } else if (!this.tosApproved) {
                if (typeof txtTOSIsNotAccepted !== 'undefined') {
					alert(txtTOSIsNotAccepted);
					$('html, body').animate({
						scrollTop: $('#tos').offset().top + 'px'
					}, 'fast');
                }
            } else if (!this.revocationTermsApproved) {
                if (typeof  txtRevocationTermIsNotAccepted !== 'undefined') {
					alert(txtRevocationTermIsNotAccepted);
					$('html, body').animate({
						scrollTop: $('#tos').offset().top + 'px'
					}, 'fast');
                }
            }
        }
    },
    
    toggleChosenForm: function(show, undefined) {
	if (this.paymentChosen) {
	    this.setActiveState();
	    var elt = $('#' + this.paymentChosen + '_form_container');
	    
	    if (elt.length && elt.attr('data-do-not-toggle') != 1) {
		if (show === undefined) {
		    elt.toggle();
		}
		else if (show) {
		    elt.show();
		}
		else {
		    elt.hide();
		}
	    }
	}
    },
    
    setActiveState: function() {
	$('table.payment-summary tbody').removeClass('active');
	
	if (this.paymentChosen) {
	    $('input:radio[name=payment_option][id=choose_' + this.paymentChosen + ']').parents('tbody:first').addClass('active');
	}
    },
    
    updateConfirmButton: function() {
        $('#confirmOrder').removeAttr('disabled');
        /*
        if (this.paymentChosen && this.tosApproved && this.revocationTermsApproved) {
            $('#confirmOrder').removeAttr('disabled');
        }
        else {
            $('#confirmOrder').attr('disabled', 'disabled');
        }
        */
    },
    
    bindAjaxHandlers: function(){
	$(document).ajaxSuccess(function(event, jqXHR, ajaxOptions, data){
	    var ajaxOpts = {},
		temp;
	    
	    if (typeof(ajaxOptions) != 'undefined' && typeof(ajaxOptions.data) != 'undefined') {
		temp = ajaxOptions.data.split('&');
		
		if (temp.length) {
		    for (var i in temp) {
			if (temp[i].split('=')[0] == 'SubmitLogin') {
			    legal.onLogin();
			}
		    }
		}
	    }
	});
    },
    
    onLogin: function(){
	$('#opc_payment_methods-overlay').fadeIn('slow', function(){
	    $.ajax({
		type: 'POST',
		headers: { "cache-control": "no-cache" },
		url: orderOpcUrl + '?rand=' + new Date().getTime(),
		async: true,
		cache: false,
		dataType : "json",
		data: 'ajax=true&method=getCartSummary&checked=' + legal.tosApproved + '&token=' + static_token,
		success: function(json) {
		    $('#opc_payment_methods #orderSummaryWrapper').replaceWith(json.summary)
		}
	    });
	    
	    $(this).fadeOut('slow');		
	});
    }
}

$(function(){
    legal.init();
});
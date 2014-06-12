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
	$(document).on('change', 'input:radio[name=payment_option]', function(evt){
	    evt.preventDefault();
	    // Hide currently displayed form if there is one
	    legal.toggleChosenForm(false);

	    var val = $(this).val();
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
	if (this.paymentChosen && this.tosApproved && this.revocationTermsApproved) {
	    $('#confirmOrder').removeAttr('disabled');
	}
	else {
	    $('#confirmOrder').attr('disabled', 'disabled');
	}
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
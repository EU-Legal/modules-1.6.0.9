var legal = {
    paymentChosen: '',
    
    tosApproved: false,
    
    init: function(){
	$('[data-hide-if-js]').hide();
	$('[data-show-if-js]').show();
	$('[data-remove-if-js]').remove();
	
	this.bindPaymentOptionClick();
	
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
    },
    
    bindPaymentOptionClick: function() {
	$(document).on('change', 'input:radio[name=payment_option]', function(evt){
	    evt.preventDefault();
	    // Hide currently displayed form if there is one
	    legal.toggleChosenForm(false);

	    var val = $(this).val();
	    if (val) {
		paymentChosen = val;
		// Display form if there is one
		legal.toggleChosenForm(true);

		if (legal.localStorageEnabled()) {
		    localStorage.setItem('preferredPaymentMethod', val);
		}
	    }
	    else {
		paymentChosen = '';
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
	if (this.paymentChosen && this.tosApproved) {
	    $('#' + this.paymentChosen + '_payment form').submit();
	}
    },
    
    toggleChosenForm: function(show, undefined) {
	if (this.paymentChosen) {
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
    
    updateConfirmButton: function() {
	if (this.paymentChosen && this.tosApproved) {
	    $('#confirmOrder').removeAttr('disabled');
	}
	else {
	    $('#confirmOrder').attr('disabled', 'disabled');
	}
    }
}

$(function(){
    legal.init();
});
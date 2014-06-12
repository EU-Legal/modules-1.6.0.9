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
		if (this.paymentChosen && this.tosApproved && this.revocationTermsApproved) {
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


$(document).ready(function(){
	
	legal.init();
	
	/* category pages => list and grid view */
	if (typeof page_name != 'undefined' && !in_array(page_name, ['product']))
		bindGridLegal(); 
	
	/* product pages combinations/attributes */
	else {
		
		$(document).on('click', '.color_pick', function(e){
			findCombinationLegal();
		});

		$(document).on('change', '.attribute_select', function(e){
			findCombinationLegal();
		});

		$(document).on('click', '.attribute_radio', function(e){
			findCombinationLegal();
		});
		
		var url_found = checkUrl();
		
		if (typeof productHasAttributes != 'undefined' && productHasAttributes && !url_found)
			findCombinationLegal();
			
		$('.old-price .old-price-display').replaceWith($('#old_price_display'));
		
	}
	
});

function findCombinationLegal() {
	
	//create a temporary 'choice' array containing the choices of the customer
	var choice = [];
	$('#attributes select, #attributes input[type=hidden], #attributes input[type=radio]:checked').each(function(){
		choice.push(parseInt($(this).val()));
	});

	if (typeof product_weight == 'undefined' || !product_weight)
		product_weight = 0;
	
	if (typeof combinations == 'undefined' || !combinations)
		combinations = [];
	//testing every combination to find the conbination's attributes' case of the user
	for (var combination = 0; combination < combinations.length; ++combination)
	{
		//verify if this combinaison is the same that the user's choice
		var combinationMatchForm = true;
		$.each(combinations[combination]['idsAttributes'], function(key, value)
		{
			if (!in_array(parseInt(value), choice))
				combinationMatchForm = false;
		});

		if (combinationMatchForm)
		{
			
			var weight_combination = combinationsFromController[combinations[combination]['idCombination']]['weight'];
			
			$('.weight-info .weight-value').text(parseFloat(product_weight) + parseFloat(weight_combination));
			
			//leave the function because combination has been found
			return;
		}
	}
	
}

function bindGridLegal()
{
	var view = $.totalStorage('displayLegal');
	if (view && view != 'grid')
		displayLegal('view');
	else
		displayLegal('grid');
		
	$(document).on('click', '#grid', function(e){
		e.preventDefault();
		displayLegal('grid');
	});
	$(document).on('click', '#list', function(e){
		e.preventDefault();
		displayLegal('list');
	});
} 

function displayLegal(view)
{
    /* List-View */
	if (view == 'list')
    {
        $('.product_list > li').each(function(index, element) {
			/* add delivery-info after center-block availability */
			var deliveryinfo = $(element).find('.delivery-info').html();
			if (deliveryinfo != null) { 
				$(element).find('.availability').after('<span class="delivery-info eu-legal">'+deliveryinfo+'</span>');
			}
			/* don't duplicate weight-info if already exists */
			if($(element).find('.right-block .weight-info').length <= 0) {
				/* append weight-info in right-block content-price */
				var weightinfo = $(element).find('.weight-info').html();
				if (weightinfo != null) { 
					$(element).find('.content_price').append('<span class="weight-info eu-legal">'+weightinfo+'</span>');
				}
			}
		});
                
        $.totalStorage('displayLegal', 'list');
    }
	/* Grid-View */
    else 
    {
        $('.product_list > li').each(function(index, element) {
			/* add weight-info after right-block availability and delivery-info */
			var weightinfo = $(element).find('.weight-info').html();
			if (weightinfo != null) { 
				$(element).find('.availability').after('<span class="weight-info eu-legal">'+weightinfo+'</span>');
			}
			/* add delivery-info after right-block availability */
			var deliveryinfo = $(element).find('.delivery-info').html();
			if (deliveryinfo != null) { 
				$(element).find('.availability').after('<span class="delivery-info eu-legal">'+deliveryinfo+'</span>');
			}
		});
                
        $.totalStorage('displayLegal', 'grid');
    }    
}
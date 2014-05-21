$(document).ready(function(){
	
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
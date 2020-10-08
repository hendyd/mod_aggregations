function uiMessage(msg, status){
    UIkit.notification({
	message: msg,
	status: status,
	pos: 'top-center',
	timeout: 5000
    });
}

function submitForm()
{
	var formData = jQuery('#agg-form').serializeArray().reduce(
		function(a, x) { 
			if(!a[x.name]){
				a[x.name] = x.value;
			} else {
				var b = a[x.name];
				a[x.name] = [];
				a[x.name].push(b);
				a[x.name].push(x.value); 
			}
			return a; 
		}, 
		{}
	);
	var submit = jQuery.ajax({
		url: 'index.php?option=com_ajax&module=aggregations&format=raw&method=submitForm',
		data: {'formData': formData},
		type: 'POST',
		dataType: 'JSON',
		beforeSend: function(){}
	}).done(function(data){
		console.log(data);
		if(data.success){
			jQuery('#message').html(data.data.message);
			pageTransition(2);
		}
	}).error(function(xhr, textStatus, error){
		console.log(xhr);
		console.log(textStatus);
		console.log(error);
	});
}

function pageTransition(id)
{
	jQuery('#agg-form fieldset').addClass('uk-hidden');
	jQuery('#agg-form #agg-form'+id).removeClass('uk-hidden');
}

function address()
{
	jQuery('#billing_street').val(jQuery('#shipping_street').val());
	jQuery('#billing_street2').val(jQuery('#shipping_street2').val());
	jQuery('#billing_city').val(jQuery('#shipping_city').val());
	jQuery('#billing_postalcode').val(jQuery('#shipping_postalcode').val());
}

function isValid(id)
{
	var fieldsEmpty = [];
	jQuery('#agg-form'+id+' input, #agg-form'+id+' select, #agg-form'+id+' textarea').filter('[required]').each(function(){
		var input = jQuery(this),
		    isCheckbox = input[0].type == 'checkbox',
		    labelId = jQuery(this).attr('data-name'),
		    labelText = jQuery(this).attr('data-name');
		if((input.val() === '' && !isCheckbox) || (isCheckbox && !anyChecked(input[0]))) {
		    	labelText = jQuery(this).attr('data-name');
		    if (labelText) {
				labelText = labelText.replace('*', '').trim();
				fieldsEmpty.push(labelText.replaceAll('_', ' '));
		    }
		}
    });
    return fieldsEmpty;
}

jQuery(document).on('submit', '#agg-form', function(e){
	var invalidAnswers = jQuery.merge(isValid(0), isValid(1));
	if(invalidAnswers.length > 0){
		e.preventDefault();
		jQuery(invalidAnswers).each(function(key, value){
			uiMessage('Please enter an answer for: '+value, 'danger');
		});
	} else {
		return;
	}
});

jQuery(document).on('click', '#next', function(e){
	var invalidAnswers = isValid(0);
	if(invalidAnswers.length > 0){
		e.preventDefault();
		jQuery(invalidAnswers).each(function(key, value){
			uiMessage('Please enter an answer for: '+value, 'danger');
		});
	} else {
		pageTransition(1);
	}
});

jQuery(document).on('click', '#prev', function(e){
	e.preventDefault();
	pageTransition(0);
});

jQuery(document).on('change', '#sameasshipping', function(){
	if(jQuery(this).is(':checked')){
		address();
	}
});


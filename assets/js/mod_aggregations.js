function uiMessage(msg, status){
    UIkit.notification({
	message: msg,
	status: status,
	pos: 'top-center',
	timeout: 5000
    });
}

function populateForm(email, subcat)
{
	if(email.length > 0){
		var populate = jQuery.ajax({
			url: 'index.php?option=com_ajax&module=aggregations&format=raw&method=populateForm',
			data: {'email': email, 'subcategory': subcat},
			type: 'POST',
			dataType: 'JSON',
			beforeSend: function(){}
		}).done(function(data){
			var response = data.data;
			jQuery('#account_name').val(response.crm.account.name);

			jQuery('#shipping_street').val(response.crm.account.shipping_address_street);
			jQuery('#shipping_street2').val(response.crm.account.shipping_address_street_2);
			jQuery('#shipping_city').val(response.crm.account.shipping_address_city);
			jQuery('#shipping_postalcode').val(response.crm.account.shipping_address_postalcode);

			jQuery('#billing_street').val(response.crm.account.billing_address_street);
			jQuery('#billing_street2').val(response.crm.account.billing_address_street_2);
			jQuery('#billing_city').val(response.crm.account.billing_address_city);
			jQuery('#billing_postalcode').val(response.crm.account.billing_address_postalcode);

			jQuery('#contactname').val(response.joomla.user.name);
			jQuery('#contacttel').val(response.crm.account.phone_work);
			jQuery('#userid').val(response.joomla.user.id);
		}).error(function(xhr, textStatus, error){
			console.log(xhr);
			console.log(textStatus);
			console.log(error);
		});
	}
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

function isValid()
{
	var fieldsEmpty = [];
	jQuery('input, select, textarea').filter('[required]').each(function(){
		var input = jQuery(this),
		    isCheckbox = input[0].type == 'checkbox',
		    labelId = isCheckbox ? input.attr('id').substr(0, input.attr('id').length-1): input.attr('id'),
		    labelText;
		if((input.val() === '' && !isCheckbox )
		   ||  (  isCheckbox && !anyChecked(input[0]) )) {
		    labelText = jQuery("label[for='"+labelId+"']").text();
		    if ( labelText ) {
			labelText = labelText.replace('*', '').trim();
			fieldsEmpty.push(labelText);
		    }
		}
    });
    return fieldsEmpty;
}

jQuery(document).ready(function(){
	populateForm(jQuery('#contactemail').val(), jQuery('#subcategory').val());
});

jQuery(document).on('click', '#agg-form #submit', function(e){
	e.preventDefault();
	var invalidAnswers = isValid();
	if(invalidAnswers.length > 0){
		jQuery(invalidAnswers).each(function(key, value){
			uiMessage('Please enter an answer for: '+value, 'danger');
		});
	} else {
		submitForm();
	}
});

jQuery(document).on('click', '#next', function(e){
	e.preventDefault();
	pageTransition(1);
});

jQuery(document).on('click', '#prev', function(e){
	e.preventDefault();
	pageTransition(0);
});

jQuery(document).on('change', '#sameasshipping', function(e){
	e.preventDefault();
	if(jQuery(this).is(':checked')){
		address();
	}
});


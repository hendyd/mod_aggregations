<?php
defined('_JEXEC') or die;
define('MOD_AGGREGATIONS', '/modules/mod_aggregations');

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$doc = Factory::getDocument();
$doc->addScript('http://localhost/modules/mod_aggregations/assets/js/mod_aggregations.js');


$module = new modAggregationsHelper;
?>

<form method="post" action="index.php?option=com_ajax&module=aggregations&format=raw&method=submitForm" id="agg-form" class="uk-form-stacked">
	<fieldset id="agg-form0" data-id="0">
		<div class="uk-margin">
			<progress class="uk-progress" value="1" max="2"></progress>
		</div>
		<div class="uk-margin">
			<h3>School information</h3>
		</div>
		<div class="uk-margin">
			<label class="uk-form-label" for="account_name">School name *</label>
			<div class="uk-form-controls">
				<input type="text" id="account_name" data-name="account_name" name="agg_form[account_name]" value="<?= $populateForm->crm->account->name; ?>" required />
			</div>
		</div>
		<div class="uk-margin-remove-left uk-child-width-1-2@m" uk-grid>
			<div class="uk-padding-remove">
				<label class="uk-form-label" for="account_name">School shipping address</label>
				<div class="uk-form-controls">
					<input type="text" id="shipping_street" data-name="shipping_street" name="agg_form[shipping_street]" placeholder="Street *" class="uk-margin-small-bottom uk-input" value="<?= $populateForm->crm->account->shipping_address_street; ?>" required />
					<input type="text" id="shipping_street2" data-name="shipping_street2" name="agg_form[shipping_street2]" placeholder="Line 2" class="uk-margin-small-bottom uk-input" value="<?= $populateForm->crm->account->shipping_address_street_2; ?>" />
					<input type="text" id="shipping_city" data-name="shipping_city" name="agg_form[shipping_city]" placeholder="Town / City *" class="uk-margin-small-bottom uk-input" value="<?= $populateForm->crm->account->shipping_address_city; ?>" required />
					<input type="text" id="shipping_postalcode" data-name="shipping_postalcode" name="agg_form[shipping_postalcode]" placeholder="Postcode *" value="<?= $populateForm->crm->account->shipping_address_postalcode; ?>" required />
				</div>
			</div>
			<div class="">
				<label class="uk-form-label" for="account_name">School billing address</label>
				<div class="uk-form-controls">
					<input type="text" id="billing_street" data-name="billing_street" name="agg_form[billing_street]" placeholder="Street *" class="uk-margin-small-bottom uk-input" value="<?= $populateForm->crm->account->billing_address_street; ?>" required />
					<input type="text" id="billing_street2" data-name="billing_street2" name="agg_form[billing_street2]" placeholder="Line 2" class="uk-margin-small-bottom uk-input" value="<?= $populateForm->crm->account->billing_address_street_2; ?>" />
					<input type="text" id="billing_city" data-name="billing_city" name="agg_form[billing_city]" placeholder="Town / City *" class="uk-margin-small-bottom uk-input" value="<?= $populateForm->crm->account->billing_address_city; ?>" required />
					<input type="text" id="billing_postalcode" data-name="billing_postalcode" name="agg_form[billing_postalcode]" placeholder="Postcode *" value="<?= $populateForm->crm->account->billing_address_postalcode; ?>" required />
					<div class="uk-margin uk-grid-small uk-child-width-auto uk-grid">
	            		<label><input class="uk-checkbox uk-margin-small-right" id="sameasshipping" type="checkbox">Same as shipping address</label>
	            	</div>
				</div>
			</div>
		</div>
		<div class="uk-margin-remove-left uk-child-width-1-2@m" uk-grid>
			<div class="uk-padding-remove">
				<label class="uk-form-label" for="school_opening_times">School opening times *</label>
				<div class="uk-form-controls">
					<input type="text" id="school_opening_times" data-name="school_opening_times" class="uk-input" name="agg_form[school_opening_times]" required />
				</div>
			</div>
			<div>
				<label class="uk-form-label" for="estdelivery">Estimated delivery frequency <span class="uk-text-small">(ie. 2 times per week)</span> *</label>
				<div class="uk-form-controls" uk-grid>
					<div>
						<select data-name="estimated_delivery_frequency" name="agg_form[estimated_delivery_frequency]">
							<option value="">Please select</option>
							<?php foreach($helper->formOptions('estdelivery_number') as $key => $value): ?>
								<option value="<?= $value; ?>"><?= ucfirst($value); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div>
						<select data-name="estimated_delivery_term" name="agg_form[estimated_delivery_term]">
							<option value="">Please select</option>
							<?php foreach($helper->formOptions('estdelivery_term') as $key => $value): ?>
								<option value="<?= $value; ?>"><?= ucfirst($value); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			</div>
			<div class="uk-padding-remove">
				<label class="uk-form-label" for="estimated_annual_spend">Estimated annual spend *</label>
				<div class="uk-form-controls">
					<input type="text" id="estimated_annual_spend" data-name="estimated_annual_spend" class="uk-input" name="agg_form[estimated_annual_spend]" required />
				</div>
			</div>
			<div>
				<label class="uk-form-label" for="order_method">Preferred ordering method *</label>
				<div class="uk-form-controls">
					<div class="uk-margin uk-grid-small uk-child-width-auto uk-grid">
						<?php foreach($helper->formOptions('ordermethod') as $key => $value): ?>
							<div>
								<label><input class="uk-checkbox uk-margin-small-right" id="order_method<?= $key; ?>" name="agg_form[order_method][]" data-name="additional_delivery_information" type="checkbox" value="<?= $value; ?>"><?= ucfirst($value); ?></label>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
		<div class="uk-margin">
			<label class="uk-form-label" for="additional_delivery_information">Any mandatory information / delivery instructions requiring capture?</label>
			<div class="uk-form-controls">
				<textarea class="uk-textarea" data-name="additional_delivery_information" id="additional_delivery_information" name="agg_form[additional_delivery_information]"></textarea>
			</div>
		</div>
		<div class="uk-margin">
			<p>* required field</p>
		</div>
		<div class="uk-float-right uk-margin">
			<button type="button" id="next" class="uk-button uk-button-secondary">Next</button>
		</div>
	</fieldset>	
	<fieldset class="uk-hidden" id="agg-form1" data-id="1">
		<div class="uk-margin">
			<progress class="uk-progress" value="2" max="2"></progress>
		</div>
		<div class="uk-margin">
			<h3>Your information</h3>
		</div>
		<div class="uk-grid uk-child-width-1-3@m uk-child-width-1-1" uk-grid>
			<div class="uk-margin">
				<label class="uk-form-label" for="contact_name">Your name *</label>
				<div class="uk-form-controls">
					<input type="text" id="contact_name" data-name="contact_name" class="uk-input" name="agg_form[contact_name]" value="<?= $populateForm->joomla->user->name; ?>" required />
				</div>
			</div>
			<div class="uk-margin-remove-top">
				<label class="uk-form-label" for="contact_email">Your email address *</label>
				<div class="uk-form-controls">
					<input type="text" id="contact_email" data-name="contact_email" class="uk-input" name="agg_form[contact_email]" value="<?= $populateForm->joomla->user->email; ?>" required />
				</div>
			</div>
			<div class="uk-margin-remove-top">
				<label class="uk-form-label" for="contact_telephone_number">Your telephone number *</label>
				<div class="uk-form-controls">
					<input type="text" id="contact_telephone_number" data-name="contact_telephone_number" class="uk-input" name="agg_form[contact_telephone_number]" value="<?= $populateForm->crm->account->phone_work; ?>" required />
				</div>
			</div>
		</div>
		<div class="uk-margin">
			<h3>Invoice information</h3>
		</div>
		<div class="uk-grid uk-child-width-1-3@m uk-child-width-1-1" uk-grid>
			<div class="uk-margin">
				<label class="uk-form-label" for="invoice_name">Invoice contact name *</label>
				<div class="uk-form-controls">
					<input type="text" id="invoice_name" class="uk-input" data-name="invoice_name" name="agg_form[invoice_name]" value="<?= $populateForm->joomla->user->name; ?>" required />
				</div>
			</div>
			<div class="uk-margin-remove-top">
				<label class="uk-form-label" for="invoice_email">Invoice email address *</label>
				<div class="uk-form-controls">
					<input type="text" id="invoice_email" class="uk-input" data-name="invoice_email" name="agg_form[invoice_email]" value="<?= $populateForm->joomla->user->email; ?>" required />
				</div>
			</div>
			<div class="uk-margin-remove-top">
				<label class="uk-form-label" for="invoice_telephone_number">Invoice telephone number *</label>
				<div class="uk-form-controls">
					<input type="text" id="invoice_telephone_number" class="uk-input" data-name="invoice_telephone_number" name="agg_form[invoice_telephone_number]" value="<?= $populateForm->crm->account->phone_work; ?>" required />
				</div>
			</div>
		</div>
		<div class="uk-margin">
			<p>* required field</p>
		</div>

		<input type="hidden" name="info[campaign]" value="<?= $campaign; ?>" />
		<input type="hidden" name="info[userid]" value="<?= $populateForm->joomla->user->id; ?>" />
		<input type="hidden" name="agg_form[category]" value="<?= $cat; ?>" />
		<input type="hidden" name="agg_form[subcategory]" value="<?= $subcat; ?>" />
		<input type="hidden" name="info[redirect]" value="<?= $redirect; ?>" />
		<?php foreach($adminEmails as $key => $value): ?>
			<input type="hidden" name="info[adminemail][]" value="<?= $module->encryptData($value->{'admin-email-address'}); ?>" />
		<?php endforeach; ?>
		<?php foreach($supplier as $key => $value): ?>
			<input type="hidden" name="info[supplier][]" value='<?= $value; ?>' />
		<?php endforeach; ?>

		<div class="uk-float-right uk-margin">
			<button type="button" id="prev" class="uk-button uk-button-secondary uk-margin-right">Previous</button>
			<button type="submit" id="submit" class="uk-button uk-button-primary">Submit</button>
		</div>
	</fieldset>
</form>
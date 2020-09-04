<?php
defined('_JEXEC') or die;
define('MOD_AGGREGATIONS', '/modules/mod_aggregations');

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

$doc = Factory::getDocument();
$doc->addScript(MOD_AGGREGATIONS.'/assets/js/mod_aggregations.js');


$module = new modAggregationsHelper;
?>

<form method="post" action="" id="agg-form" class="uk-form-stacked">
	<fieldset id="agg-form0" data-id="0">
		<div class="uk-margin">
			<progress class="uk-progress" value="1" max="3"></progress>
		</div>
		<div class="uk-margin">
			<h3>School information</h3>
		</div>
		<div class="uk-margin">
			<label class="uk-form-label" for="account_name">School name *</label>
			<div class="uk-form-controls">
				<input type="text" id="account_name" name="account_name" required />
			</div>
		</div>
		<div class="uk-margin-remove-left uk-child-width-1-2@m" uk-grid>
			<div class="uk-padding-remove">
				<label class="uk-form-label" for="account_name">School shipping address</label>
				<div class="uk-form-controls">
					<input type="text" id="shipping_street" name="shipping_street" placeholder="Street *" class="uk-margin-small-bottom uk-input" required />
					<input type="text" id="shipping_street2" name="shipping_street2" placeholder="Line 2" class="uk-margin-small-bottom uk-input" />
					<input type="text" id="shipping_city" name="shipping_city" placeholder="Town / City *" class="uk-margin-small-bottom uk-input" required />
					<input type="text" id="shipping_postalcode" name="shipping_postalcode" placeholder="Postcode *" required />
				</div>
			</div>
			<div class="">
				<label class="uk-form-label" for="account_name">School billing address</label>
				<div class="uk-form-controls">
					<input type="text" id="billing_street" name="billing_street" placeholder="Street *" class="uk-margin-small-bottom uk-input" required />
					<input type="text" id="billing_street2" name="billing_street2" placeholder="Line 2" class="uk-margin-small-bottom uk-input" />
					<input type="text" id="billing_city" name="billing_city" placeholder="Town / City *" class="uk-margin-small-bottom uk-input" required />
					<input type="text" id="billing_postalcode" name="billing_postalcode" placeholder="Postcode *" required />
					<div class="uk-margin uk-grid-small uk-child-width-auto uk-grid">
	            		<label><input class="uk-checkbox uk-margin-small-right" id="sameasshipping" type="checkbox">Same as shipping address</label>
	            	</div>
				</div>
			</div>
		</div>
		<div class="uk-margin-remove-left uk-child-width-1-3@m" uk-grid>
			<div class="uk-padding-remove">
				<label class="uk-form-label" for="opening">School opening times *</label>
				<div class="uk-form-controls">
					<input type="text" id="opening" class="uk-input" name="opening" required />
				</div>
			</div>
			<div>
				<label class="uk-form-label" for="estdelivery">Estimated delivery frequency (ie. 2 times per week) *</label>
				<div class="uk-form-controls">
					<input type="text" id="estdelivery" class="uk-input" name="estdelivery" required />
				</div>
			</div>
			<div>
				<label class="uk-form-label" for="ordermethod">Preferred ordering method *</label>
				<div class="uk-form-controls">
					<div class="uk-margin uk-grid-small uk-child-width-auto uk-grid">
						<div>
							<label><input class="uk-checkbox uk-margin-small-right" id="ordermethod0" name="ordermethod" type="checkbox" value="online">Online</label>
						</div>
						<div>
							<label><input class="uk-checkbox uk-margin-small-right" id="ordermethod1" name="ordermethod" type="checkbox" value="phone">Phone</label>
						</div>
						<div>
							<label><input class="uk-checkbox uk-margin-small-right" id="ordermethod2" name="ordermethod" type="checkbox" value="email">Email</label>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="uk-margin">
			<label class="uk-form-label" for="deliveryinfo">Any mandatory information / delivery instructions requiring capture?</label>
			<div class="uk-form-controls">
				<textarea class="uk-textarea" id="deliveryinfo" name="deliveryinfo"></textarea>
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
			<progress class="uk-progress" value="2" max="3"></progress>
		</div>
		<div class="uk-margin">
			<h3>Your information</h3>
		</div>
		<div class="uk-margin">
			<label class="uk-form-label" for="contactname">Your name *</label>
			<div class="uk-form-controls">
				<input type="text" id="contactname" class="uk-input" name="contactname" required />
			</div>
		</div>
		<div class="uk-margin">
			<label class="uk-form-label" for="contactemail">Your email address *</label>
			<div class="uk-form-controls">
				<?php if(!$user->guest): ?>
					<input type="text" id="contactemail" class="uk-input" name="contactemail" value="<?= $user->email; ?>" required />
				<?php else: ?>
					<input type="text" id="contactemail" class="uk-input" name="contactemail" required />
				<?php endif; ?>
			</div>
		</div>
		<div class="uk-margin">
			<label class="uk-form-label" for="contacttel">Your telephone number *</label>
			<div class="uk-form-controls">
				<input type="text" id="contacttel" class="uk-input" name="contacttel" required />
			</div>
		</div>
		<input type="hidden" id="userid" name="userid" value="" />
		<input type="hidden" id="category" name="category" value="<?= $cat; ?>" />
		<input type="hidden" id="subcategory" name="subcategory" value="<?= $subcat; ?>" />
		<?php foreach($supplier as $key => $value): ?>
			<input type="hidden" id="supplier<?= $key; ?>" name="supplier" value='<?= $value; ?>' />
		<?php endforeach; ?>

		<div class="uk-margin">
			<p>* required field</p>
		</div>

		<div class="uk-float-right uk-margin">
			<button type="button" id="prev" class="uk-button uk-button-secondary uk-margin-right">Previous</button>
			<button type="button" id="submit" class="uk-button uk-button-primary">Submit</button>
		</div>
	</fieldset>
	<fieldset class="uk-hidden" id="agg-form2" data-id="2">
		<div class="uk-margin">
			<progress class="uk-progress" value="3" max="3"></progress>
		</div>
		<div class="uk-margin">
			<h3>Thank you for submitting the form</h3>
			<p id="message"></p>
			<a href="<?= Route::_('index.php?option=com_content&view=article&id=153'); ?>" class="uk-button uk-button-primary">View more deals</a>
		</div>

	</fieldset>
</form>
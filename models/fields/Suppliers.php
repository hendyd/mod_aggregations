<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
define('MOD_AGGREGATIONS', JPATH_SITE.'/modules/mod_aggregations');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Log\Log;

FormHelper::loadFieldClass('list');

class JFormFieldSuppliers extends JFormFieldList {
	
	protected $type = 'Suppliers';

	protected function mapCategories($name)
	{
		$db = Factory::getDbo();
		$query = $db
		->getQuery(true)
		->select($db->quoteName('crm_value'))
		->from($db->quoteName('#__crm_subcategories'))
		->where($db->quoteName('mod_aggregation_supplier_form_field').' = '.$db->quote($name));
		return $db->setQuery($query)->loadResult();
	}

	public function getOptions()
	{
		if(!@include_once MOD_AGGREGATIONS.'/libraries/sugarcrm.php'):
			Log::add('Not included sugarcrm.php file. getOptions() not executed', Log::ERROR, 'mod_aggregations');
			echo 'not included'; die();
		endif;

		$name = str_replace(array('jform[params][', '][]'), array('',''), $this->name);


		// get applicable suppliers
		$json = 
		SugarCRM::getCRMData('fm_Suppliers', '', array(
			'subcategory_c' => array(
				'$contains' => JFormFieldSuppliers::mapCategories($name)
			)
		));
		
		// iterate all CRM suppliers and add to Array
		foreach($json->records as $supplier){
			$supplierOptions[] = array(
				'value' => $supplier->id,
				'text' => $supplier->name
			);
		}

		// merge Default array and new Array of options
		$options = array_merge(parent::getOptions(), $supplierOptions);

		// Return all options to the front end form
        return $options;
	}
}

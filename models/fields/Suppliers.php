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
		$name = str_replace(array('jform[params][', '][]'), array('',''), $name);
		switch($name)
		{
			case 'select-supplier-auditor':
				$cat = 'Auditor Services';
				break;
			case 'select-supplier-bfms':
				$cat = 'Building and Facilities Management Services';
				break;
			case 'select-supplier-catering':
				$cat = 'Catering';
				break;
			case 'select-supplier-cleaning':
				$cat = 'Cleaning';
				break;
			case 'select-supplier-consultancy':
				$cat = 'Consultancy';
				break;
			case 'select-supplier-dbs':
				$cat = 'DBS_Checks';
				break;

			default:
				$cat = 'Stationery';
				break;
		}
		return $cat;
	}

	public function getOptions()
	{
		if(!@include_once MOD_AGGREGATIONS.'/libraries/sugarcrm.php'):
			Log::add('Not included sugarcrm.php file. getOptions() not executed', Log::ERROR, 'mod_aggregations');
			echo 'not included'; die();
		endif;
		
		// get applicable suppliers
		$json = 
		SugarCRM::getCRMData('fm_Suppliers', '', array(
			'subcategory_c' => array(
				'$contains' => $this->mapCategories($this->name)
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

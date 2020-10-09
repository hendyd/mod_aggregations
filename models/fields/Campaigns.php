<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
define('MOD_AGGREGATIONS', JPATH_SITE.'/modules/mod_aggregations');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Log\Log;

FormHelper::loadFieldClass('list');

class JFormFieldCampaigns extends JFormFieldList {
	
	protected $type = 'Campaigns';

	protected function getCampaigns()
	{
		include_once(MOD_AGGREGATIONS.'/libraries/sugarcrm.php');
		
		$option = (object) array(
			'host' => '192.168.254.8',
			'user' => 'root',
			'password' => 'G0d15Gr34t!',
			'database' => 'crm_live'
		);
		$conn = new mysqli($option->host, $option->user, $option->password, $option->database);
		$sql = "SELECT id, name FROM campaigns WHERE name != '' AND deleted = 0";
		$result = $conn->query($sql);
		while($row = $result->fetch_assoc()){
			$campaigns[] = array(
				'value' => $row['id'],
				'text' => $row['name']
			);
		}
		return $campaigns;
	}

	public function getOptions()
	{
		// merge Default array and new Array of options
		$options = array_merge(parent::getOptions(), $this->getCampaigns());
		// Return all options to the front end form
        return $options;
	}
}

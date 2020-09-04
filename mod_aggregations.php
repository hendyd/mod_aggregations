<?php
// No direct access
defined('_JEXEC') or die;
// Include the syndicate functions only once

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
//require_once(JPATH_SITE.'/components/com_jsn/helpers/helper.php');
require_once(dirname(__FILE__).'/helper.php');

$helper = new modAggregationsHelper;
$subcat = $helper->getParams($params, 'subcategory');
$cat = $helper->mapCategory($subcat);
$template = $helper->getParams($params, 'template');
$supplier = $helper->getParams($params, 'crm_supplier');
$user = Factory::getUser();
require ModuleHelper::getLayoutPath('mod_aggregations', $template);

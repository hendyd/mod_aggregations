<?php
// No direct access
defined('_JEXEC') or die;
// Include the syndicate functions only once

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
require_once(dirname(__FILE__).'/helper.php');

$helper = new modAggregationsHelper;
$subcat = $helper->getParams($params, 'subcategory');
$adminEmails = $helper->getParams($params, 'admin-email');
$cat = $helper->mapCategory($subcat);
$template = $helper->getParams($params, 'template');
$supplier = $helper->getParams($params, 'select-supplier-'.strtolower($subcat));
$campaign = $helper->getParams($params, 'campaign');
$redirect = $helper->getParams($params, 'redirecturl');

$populateForm = $helper->populateForm();

$user = Factory::getUser();
require ModuleHelper::getLayoutPath('mod_aggregations', $template);

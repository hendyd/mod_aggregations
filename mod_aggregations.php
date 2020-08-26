<?php
// No direct access
defined('_JEXEC') or die;
// Include the syndicate functions only once

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
require_once(JPATH_SITE.'/components/com_jsn/helpers/helper.php');
require_once(dirname(__FILE__).'/helper.php');

$helper = new modAggregationsHelper;
$existing_user = Factory::getUser();

require ModuleHelper::getLayoutPath('mod_staffspotlight', 'default');

<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Exception\ExceptionHandler;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Mail\Mail;
use Joomla\CMS\Input\Input;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Response\JsonResponse;

require_once JPATH_BASE.'/includes/SugarCode.php';

class modStaffSpotlightHelper{

	function __construct()
	{
		$this->db = Factory::getDbo();
		$this->user = Factory::getUser();
		$this->app = Factory::getApplication();
	}

	public function getParams($params, $type)
	{
		return $params->get($type);
	}

	public function getCRMData($base_url, $oauth2_token_response, $module, $record = null, $filter = null)
	{
		if(!empty($record)):
			$url = $base_url.'/'.$module.'/'.$record;
			$record_response = call($url, $oauth2_token_response->access_token, 'GET');
		elseif(!empty($filter)):
			$params = array(
				'filter' => array($filter)
			);
			$url = $base_url.'/'.$module.'/filter';
			$record_response = call($url, $oauth2_token_response->access_token, 'GET', $params);
		endif;
		if(isset($record_response->error)){
			if(is_array($record_response->error_message)){
				$error = $record_response->error_message[0];
			} else {
				$error = $record_response->error_message;
			}
		} else {
			return $record_response;
		}
	}

	public function getRegionRep($region)
	{
		$query = $this->db
		->getQuery(true)
		->select('id')
		->from('#__jsn_users')
		->where($this->db->quoteName('region').' LIKE "%'.$region.'%"');
		$this->db->setQuery($query);
		return $this->db->loadResult();
	}

	public function isAdmin($user)
	{
		if(in_array(6, $user->getAuthorisedViewLevels())):
			return true;
		else:
			return false;
		endif;
	}

	public function submitMessageAjax()
	{
		$post = Factory::getApplication()->input->post;
		$staff = Factory::getUser($_POST['staff']);
		$body = '
		<p>Hi '.$staff->name.',</p>
		<p>The following message has been sent to you from the Schools\' Buying Hub North West website:</p>
		<ul>
			<li><strong>Name: </strong>'.$post->get("name").'</li>
			<li><strong>Email: </strong><a href="mailto:'.$post->get("email").'">'.$post->get("email").'</a></li>
			<li><strong>Account name: </strong>'.$post->get("account").'</li>
			<li><strong>Message: </strong>'.$post->get("message").'</li>
		</ul>
		';
		$config = Factory::getConfig();
		$mail = Factory::getMailer()
		->setSender(
			array( 
			    $config->get( 'mailfrom' ),
			    $config->get( 'fromname' ) 
			)
		)
		//->addRecipient(Factory::getUser($_POST['staff'])->email)
		->addRecipient('david.hendy@2buy2.com')
		->setSubject('Contact from SBHNW website')
		->isHTML(true)
		->setBody($body);
		if($mail->send()):
			echo new JsonResponse('Thank you for messaging '.$staff->name.'. Please allow 2 working days for a response to your message.', 'Success', false, true);
		else:
			echo new JsonResponse('Unfortunately your message has not gotten through to '.$staff->name.'. If this issue persists, please get in touch with us via LiveChat to resolve this for you.', 'Failure', false, true);
		endif;			
	}
}
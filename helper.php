<?php
defined('_JEXEC') or die;
define('MOD_AGGREGATIONS', JPATH_SITE.'/modules/mod_aggregations');

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
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Response\JsonResponse;

if(!@include_once MOD_AGGREGATIONS.'/libraries/sugarcrm.php'):
	Log::add('Not included sugarcrm.php file. populateFormAjax() not executed', Log::ERROR, 'mod_aggregations');
	echo 'not included'; die();
endif;

class modAggregationsHelper{

	function __construct()
	{
		$this->db = Factory::getDbo();
		$this->user = Factory::getUser();
		$this->app = Factory::getApplication();
	}

	public function getParams($params,$type)
	{
		return $params->get($type);
	}

	public function mapCategory(string $subcat): string
	{
		switch($subcat){
			case 'Auditor Services':
			case 'Consultancy':
			case 'DBS_Checks':
			case 'Energy Management Services':
			case 'Educational':
			case 'Furniture':
			case 'Human Resources':
			case 'Insurance':
			case 'Legal':
			case 'Payroll':
			case 'Project Management Services':
			case 'Stationery':
			case 'Supply and Agency Staff':
			case 'Transport Services':
				$cat = 'business';
				break;
			case 'Building and Facilities Management Services':
			case 'Catering':
			case 'Cleaning':
			case 'Fire Safety':
			case 'Grounds Maintenance':
			case 'LED Lighting':
			case 'Mechanical and Electrical Engineering Services':
			case 'Security Services':
			case 'Solar Energy Panels':
			case 'Waste Management':
				$cat = 'facilities';
				break;
			case 'Financial Software':
			case 'IT Hardware':
			case 'IT Maintenance and Support':
			case 'IT Software':
			case 'Photocopying':
			case 'Telecoms Broadband':
			case 'Telecoms Landline':
			case 'Telecoms Mobile':
			case 'Telecoms Systems':
			case 'Web Development':
				$cat = 'ict';
				break;
			case 'Electricity':
			case 'Gas':
			case 'Oil':
			case 'Water':
				$cat = 'utilities';
				break;
		}
		return $cat;
	}

	public function populateFormAjax()
	{
		$post = Factory::getApplication()->input->post;
		$user = Factory::getUser();
		$getContact = SugarCRM::getCRMData('Contacts', '', array('email1' => $post->get('email', '', 'string'), 'platform_c' => 'sbhnw'));
		$getAccount = SugarCRM::getCRMData('Accounts', $getContact->records[0]->account_id);
		if(!empty($getContact) && !empty($getAccount)):
			echo new JsonResponse(
				(object) array(
					'joomla' => (object) array(
						'user' => (object) array(
							'id' => $user->id, 
							'name' => $user->name, 
							'email' => $user->email
						)
					), 
					'crm' => (object) array(
						'contact' => $getContact->records[0], 
						'account' => $getAccount
					)
				), 
				'Success', false, true
			);
			Log::add('Form values populated on pageload', Log::DEBUG, 'mod_aggregations');
		else:
			echo new JsonResponse('Error retrieving account data', 'Failure', true, true);
			Log::add('Form values not populated on pageload', Log::ERROR, 'mod_aggregations');
		endif;
	}

	public function submitFormAjax()
	{
		$helper = new modAggregationsHelper;
		$post = (object) $_POST['formData'];
		if(!empty($post)){
			$createDbRecord = $helper->addSubmission($post);
			$sendCustomerEmail = $helper->customerEmail($post);
			$sendSupplierEmail = $helper->supplierEmail($post);
			$sendStaffEmail = $helper->staffEmail($post);
			if($sendCustomerEmail && $sendSupplierEmail && $sendStaffEmail):
				echo new JsonResponse(
					array(
						'data' => $post,
						'message' => 'Thank you for submitting the form. One of our Procurement Specialists will be in touch with you shortly.',
						), 
					'Success', false, true
				);
			else:
				echo new JsonResponse(
				array(
					'data' => $post,
					'message' => 'Not all automated emails have been sent. Customer: '.$sendCustomerEmail.'. Staff: '.$sendStaffEmail.'. Supplier: '.$sendSupplierEmail
				), 
				'Failure', true, true
			);
			endif;
		} else {
			echo new JsonResponse(
				array(
					'data' => $post,
					'message' => 'Your submission has not been accepted'
				), 
				'Failure', true, true
			);
		}
	}

	protected function addSubmission(object $post)
	{
		$insert = (object) array(
			'userid' => $post->userid,
			'category' => $post->category,
			'subcategory' => $post->subcategory,
			'data' => json_encode($post)
		);
		Factory::getDbo()->insertObject('#__aggregation_form', $insert);
	}

	protected function sendEmail(string $body, string $subject, string $recipient)
	{
		$config = Factory::getConfig();
		$mail = Factory::getMailer()
		->setSender(array($config->get('mailfrom'),$config->get('fromname')))
		//->addRecipient($recipient)
		->addRecipient('david.hendy@2buy2.com')
		->setSubject($subject)
		->isHTML(true)
		->setBody($body);
		if($mail->send()):
			Log::add('Email sent to: '.$recipient.' with Subject: '.$subject, Log::DEBUG, 'mod_aggregations');
			return true;
		else:
			Log::add('Email send failutre to: '.$recipient.' with Subject: '.$subject, Log::ERROR, 'mod_aggregations');
			return false;
		endif;
	}

	protected function supplierEmail(object $post)
	{
		$message = 
		'<p>Hi [SUPPLIERNAME]</p>
		<p>'.$post->contactname.', on behalf of '.$post->account_name.', has submitted a web form to register for [SUPPLIERNAME].</p>
		<p>Please find their submitted details below:</p>
		<ul>[SUBMISSIONDETAILS]</ul>
		<p>Regards,<br />The Schools\' Buying Hub North West</p>';

		$submission_data = '';
		foreach($post as $key => $value){
			if(is_array($value)){
				foreach($value as $answer){
					$val .= $value.', ';
				}
			} else {
				$val = $value;
			}
			$submission_data.= '<li><strong>'.ucfirst($key).': </strong>'.$val.'</li>';
		}

		$helper = new modAggregationsHelper;
		$crm_supplier = $helper->getSupplier($post->supplier);
		$message = str_replace(array('[SUPPLIERNAME]', '[SUBMISSIONDETAILS]'), array($crm_supplier->name, $helper->parseSubmissionForEmail($post)), $message);
		foreach($crm_supplier->email as $email){
			$send = $helper->sendEmail($message, 'A new form submission from Schools\' Buying Hub North West',$email->email_address);
		}
		return $send;
	}

	protected function customerEmail(object $post)
	{
		$helper = new modAggregationsHelper;

		switch($post->subcategory){
			case 'Stationery':
				$post->subcategory = 'Office Supplies';
				break;
			default:
				break;
		}

		$message = 
		"<p>Hi ".$post->contactname."</p>
		<p>Thank you for completing our ".ucfirst($post->subcategory)." form. One of our Procurement specialists will be in touch shortly to discuss this group deal further and answer any questions you may have.</p>
		<p>Many thanks,<br />The Schools' Buying Hub North West team</p>";
		return $helper->sendEmail($message, 'Thank you for completing the web form', $post->contactemail);
	}

	protected function staffEmail(object $post)
	{
		$helper = new modAggregationsHelper;
		$message = 
		'<p>Hi [SUPPLIERNAME]</p>
		<p>'.$post->contactname.', on behalf of '.$post->account_name.', has submitted a web form to register for [SUPPLIERNAME].</p>
		<p>Please find their submitted details below:</p>
		<ul>[SUBMISSIONDETAILS]</ul>
		<p>Regards,<br />The Schools\' Buying Hub North West</p>';

		$message = str_replace(array('[SUPPLIERNAME]', '[SUBMISSIONDETAILS]'), array('Schools\' Buying Hub North West', $helper->parseSubmissionForEmail($post)), $message);
		return $helper->sendEmail($message, '[SBHNW]'.$post->contactname.' has completed the '.ucfirst($post->subcategory).' form', $post->contactemail);
	}

	protected function parseSubmissionForEmail($post)
	{
		$response = '';
		foreach($post as $key => $value){
			if(is_array($value)){
				foreach($value as $answer){
					$val .= $value.', ';
				}
			} else {
				$val = $value;
			}
			$response.= '<li><strong>'.ucfirst(str_replace('_', ' ', $key)).': </strong>'.$val.'</li>';
		}
		return $response;
	}

	
	protected function getSupplier(string $id = null): object
	{
		return SugarCRM::getCRMData('fm_Suppliers', $id);
	}

	public function getSuppliersAjax()
	{	
		$post = Factory::getApplication()->input->post;
		$subcat = $post->get('subcat', '', 'string');

		$suppliers = SugarCRM::getCRMData('fm_Suppliers', '', array(
			'subcategory_c' => array(
				'$contains' => $subcat
			)
		));

		echo new JsonResponse(
			(object) array(
				'suppliers' => $suppliers
			), 
			'Success', false, true
		);
	}



	public function isAdmin($user)
	{
		if(in_array(6, $user->getAuthorisedViewLevels())):
			return true;
		else:
			return false;
		endif;
	}
}
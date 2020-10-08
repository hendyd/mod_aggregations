<?php
defined('_JEXEC') or die;
define('MOD_AGGREGATIONS', JPATH_SITE.'/modules/mod_aggregations');

use Joomla\CMS\Factory;
use Joomla\CMS\Exception\ExceptionHandler;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\Mail\Mail;
use Joomla\CMS\Input\Input;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;

if(!@include_once MOD_AGGREGATIONS.'/libraries/sugarcrm.php'):
	Log::add('Not included sugarcrm.php file.', Log::ERROR, 'mod_aggregations');
	echo 'not included'; die();
endif;

class modAggregationsHelper {

	private $secret = '0F2TO9myj~cVpRkO\@8oaS*Igd_6h9eo';

	private $encryptionMethod = SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;

	private $debug = false;

	private $admin_emails = array();

	function __construct()
	{
		$this->db = Factory::getDbo();
		$this->user = Factory::getUser();
		$this->app = Factory::getApplication();
		$this->input - $this->app->input;
	}

	public function getParams(object $params = null ,string $type = null)
	{
		return $params->get($type);
	}

	public function mapCategory(string $subcat = null): ?string
	{
		if(!is_null($subcat)):
			$db = Factory::getDbo();
			$query = $db
			->getQuery(true)
			->select($db->quoteName('b.crm_value'))
			->from($db->quoteName('#__crm_categories', 'b'))
			->join('INNER', $db->quoteName('#__crm_subcategories', 'a').' ON '.$db->quoteName('a.cat_id').' = '.$db->quoteName('b.id'))
			->where($db->quoteName('a.name').' = '.$db->quote($subcat));
			return $db->setQuery($query)->loadResult();
		else:
			return null;
		endif;
	}

	public function populateForm(): object
	{
		$user = Factory::getUser();
		$getContact = SugarCRM::crmCall('GET', 'Contacts', '', array('email1' => $user->email, 'platform_c' => 'sbhnw'));
		$getAccount = SugarCRM::crmCall('GET', 'Accounts', $getContact->records[0]->account_id);
		if(!empty($getContact) && !empty($getAccount)):
			return (object) array(
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
			);
			Log::add('Form values populated on pageload', Log::DEBUG, 'mod_aggregations');
		else:
			return (object) array(
				'joomla' => (object) array(
					'user' => (object) array(
						'id' => '', 
						'name' => '', 
						'email' => ''
					)
				), 
				'crm' => (object) array(
					'contact' => '', 
					'account' => ''
				)
			);
			Log::add('Form values not populated on pageload', Log::ERROR, 'mod_aggregations');
		endif;
	}

	private function debugCode($var)
	{
		echo '<pre>'; print_r($var); echo '</pre>';
	}

	public function submitFormAjax()
	{
		$helper = new modAggregationsHelper;
		$post = (object) Factory::getApplication()->input->post->get('agg_form', '', 'array');
		$info = (object) Factory::getApplication()->input->post->get('info', '', 'array');
		$allData = (object )array_merge(
			Factory::getApplication()->input->post->get('agg_form', '', 'array'), 
			Factory::getApplication()->input->post->get('info', '', 'array')
		);

		if(!empty($post) && !empty($info)){
			$getContact = SugarCRM::crmCall(
				'GET',
				'Contacts', 
				'', 
				array('email1' => $post->contact_email, 'platform_c' => 'sbhnw')
			);
			$searchOpp = SugarCRM::crmCall(
				'GET',
				'Accounts', 
				$getContact->records[0]->account_id,
				$helper->crmOpportunity('GET', $allData), 
				'accounts_so_saving_opportunity_1'
			);

			if(empty($searchOpp->records)):
				// create Opp
				$opp = SugarCRM::crmCall(
					'POST', 
					'Accounts',
					$getContact->records[0]->account_id, 
					$helper->crmOpportunity('POST', $allData), 
					'accounts_so_saving_opportunity_1'
				);
			else:
				// edit Opp
				$opp = SugarCRM::crmCall(
					'PUT', 
					'SO_Saving_Opportunity',
					$searchOpp->records[0]->id, 
					$helper->crmOpportunity('PUT', $allData)
				);
			endif;

			$createDbRecord = $helper->addSubmission($allData);
			if($helper->debug):
				$sendCustomerEmail = $sendSupplierEmail = $sendStaffEmail = true;
			else:
				$sendCustomerEmail = $helper->customerEmail($post, $info);
				$sendSupplierEmail = $helper->supplierEmail($post, $info);
				$sendStaffEmail = $helper->staffEmail($post, $info);
			endif;
			if($sendCustomerEmail && $sendSupplierEmail && $sendStaffEmail):
				$helper->app->redirect(Route::_('index.php?Itemid='.$info->redirect));
			else:
				echo new JsonResponse(
				array(
					'data' => $allData,
					'message' => 'Not all automated emails have been sent. Customer: '.$sendCustomerEmail.'. Staff: '.$sendStaffEmail.'. Supplier: '.$sendSupplierEmail
				), 
				'Failure', true, true
			);
			endif;
		} else {
			echo new JsonResponse(
				array(
					'data' => $allData,
					'message' => 'Your submission has not been accepted'
				), 
				'Failure', true, true
			);
		}
	}

	protected function addSubmission(object $post = null)
	{
		if(!is_null($post)):
			$insert = (object) array(
				'userid' => $post->userid,
				'category' => $post->category,
				'subcategory' => $post->subcategory,
				'data' => json_encode($post)
			);
			Factory::getDbo()->insertObject('#__aggregation_form', $insert);
		endif;
	}

	protected function sendEmail(string $body = null, string $subject = null, string $recipient = null): int
	{
		$config = Factory::getConfig();
		$mail = Factory::getMailer()
		->setSender(
			array(
				$config->get('mailfrom'),
				$config->get('fromname')
			)
		)
		//->addRecipient($recipient)
		->addRecipient('david.hendy@2buy2.com')
		->setSubject($subject)
		->isHTML(true)
		->setBody($body);
		if($mail->send()):
			Log::add('Email sent to: '.$recipient.' with Subject: '.$subject, Log::DEBUG, 'mod_aggregations');
			return 1;
		else:
			Log::add('Email send failutre to: '.$recipient.' with Subject: '.$subject, Log::ERROR, 'mod_aggregations');
			return 0;
		endif;
	}

	protected function supplierEmail(object $post, object $info): array
	{
		$helper = new modAggregationsHelper;
		$send = array();
		foreach($info->supplier as $key => $supplier_id):
			$crm_supplier = $helper->getSupplier($supplier_id);
			$message = str_replace(
				array('[SUPPLIERNAME]', '[SUBMISSIONDETAILS]'), 
				array($crm_supplier->name, $helper->parseSubmissionForEmail($post)), 
				'<p>Hi [SUPPLIERNAME]</p>
				<p>'.$post->contact_name.', on behalf of '.$post->account_name.', has submitted a web form to register for [SUPPLIERNAME].</p>
				<p>Please find their submitted details below:</p>
				<ul>[SUBMISSIONDETAILS]</ul>
				<p>Regards,<br />The Schools\' Buying Hub North West</p>'
			);
			foreach($crm_supplier->email as $email):
				$send[] = $helper->sendEmail($message, 'A new form submission from Schools\' Buying Hub North West',$email->email_address);
			endforeach;
		endforeach;
		return $send;
	}

	protected function customerEmail(object $post = null, object $info = null): int
	{
		$helper = new modAggregationsHelper;
		switch($post->subcategory){
			case 'Stationery':
				$post->subcategory = 'Office Supplies';
				break;
			default:
				break;
		}
		foreach($info->supplier as $key => $supplier_id):
			$crm_supplier = $helper->getSupplier($supplier_id);
			$message = str_replace(
				'[SUPPLIERNAME]', 
				$crm_supplier->name, 
				"<p>Hi ".$post->contact_name."</p>
				<p>One of our Procurement specialists will be in touch shortly. [SUPPLIERNAME] will also be in touch to provide your account and ordering information.</p>
				<p>Many thanks,<br />The Schools' Buying Hub North West team</p>"
			);
		endforeach;
		return $helper->sendEmail($message, 'Thank you for completing the web form', $post->contact_email);
	}

	protected function staffEmail(object $post = null, object $info = null): array
	{
		$helper = new modAggregationsHelper;
		$send = array();
		foreach($info->supplier as $key => $supplier_id):
			$crm_supplier = $helper->getSupplier($supplier_id);
			$message = str_replace(
				array('[SUPPLIERNAME]', '[SUBMISSIONDETAILS]'), 
				array($crm_supplier->name, $helper->parseSubmissionForEmail($post)), 
				'<p>Hi Schools\' Buying Hub North West</p>
				<p>'.$post->contact_name.', on behalf of '.$post->account_name.', has submitted a web form to register for [SUPPLIERNAME].</p>
				<p>Please find their submitted details below:</p>
				<ul>[SUBMISSIONDETAILS]</ul>
				<p>Regards,<br />The Schools\' Buying Hub North West</p>'
			);
			foreach($crm_supplier->email as $email):
				$send[] = $helper->sendEmail($message, 'A new form submission from Schools\' Buying Hub North West',$email->email_address);
			endforeach;
		endforeach;
		return $send;
	}

	protected function parseSubmissionForEmail(object $post = null): ?string
	{
		$response = '';
		foreach($post as $key => $value){
			$val = '';
			if(is_array($value)){
				foreach($value as $answer){
					$val .= $answer.', ';
				}
			} else {
				$val = $value;
			}
			$response.= '<li><strong>'.ucwords(str_replace('_', ' ', $key)).': </strong>'.ucfirst($val).'</li>';
		}
		return $response;
	}

	
	protected function getSupplier(string $id = null)
	{
		return SugarCRM::crmCall('GET', 'fm_Suppliers', $id);
	}

	public function getSuppliersAjax()
	{	
		$post = Factory::getApplication()->input->post;
		$subcat = $post->get('subcat', '', 'string');

		$suppliers = SugarCRM::crmCall('GET', 'fm_Suppliers', '', array(
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

	private function crmOpportunity(string $type = null, object $post = null): array
	{
		if(count($post->supplier) == 1):
			$helper = new modAggregationsHelper;
			$crm_supplier = $helper->getSupplier($post->supplier[0]);
			switch($crm_supplier->name){
				case 'Banner Group Limited':
					$framework = 'b1d2719c-e2f3-11ea-bfec-ccf4a4a02244';
					break;
				default:
					$framework = '';
					break;
			}
		else:
			$framework = '';
		endif;
		switch($type){
			case 'GET':
				return array(
					'category' => $post->category,
					'subcategory' => $post->subcategory
				);
				break;
			case 'POST':
				return array(
					'name' => $post->account_name.' - '.ucwords($post->category).' - '.ucwords($post->subcategory),
					'pactivity_startdate_c' => date('c'),
					'opportunity_status_c' => 'closed',
					'lead_source_c' => 'Web Site',
					'savings_opp_type_c' => 'multi_school_complex',
					'request_type_c' => 'hub_service',
					'category' => $post->category,
					'subcategory' => $post->subcategory,
					'campaign_c' => $post->campaign,
					'spend_c' => str_replace('&pound;', '', $post->spend),
					'phc_framework_so_saving_opportunity_1phc_framework_ida' => $framework,
					'phc_framework_id_c' => $framework,
					'framework' => $framework,
					'pactivity_actcompdate_c' => date('c'),
					'pactivity_procstage_c' => 'contract',
					'pactivity_aggregation_c' => 'Completed',
					'year_cash_status_c' => 'Realised',
					'current_spend_est_or_act_c' => 'Actual',
					'required_agreement_type_c' => 'contract',
					'route_to_market_c' => 'other',
					'route_to_market_reason_c' => 'school_preference',
					'achievement_method' => 'New_Supplier',
					'description' => 'Banner Web Form',
					'supplier_new_c' => $crm_supplier->name
				);
				break;
			case 'PUT':
				return array(
					'opportunity_status_c' => 'closed',
					'lead_source_c' => 'Web Site',
					'savings_opp_type_c' => 'multi_school_complex',
					'request_type_c' => 'hub_service',
					'campaign_c' => $post->campaign,
					'spend_c' => str_replace('&pound;', '', $post->spend),
					'phc_framework_so_saving_opportunity_1phc_framework_ida' => $framework,
					'phc_framework_id_c' => $framework,
					'framework' => $framework,
					'pactivity_actcompdate_c' => date('c'),
					'pactivity_procstage_c' => 'contract',
					'pactivity_aggregation_c' => 'Completed',
					'year_cash_status_c' => 'Realised',
					'current_spend_est_or_act_c' => 'Actual',
					'required_agreement_type_c' => 'contract',
					'route_to_market_c' => 'other',
					'route_to_market_reason_c' => 'school_preference',
					'achievement_method' => 'New_Supplier',
					'supplier_new_c' => $crm_supplier->name
				);
				break;
		}
	}

	public function encryptData(string $data = null): ?string
	{
		if(!is_null($data)):
			$nonce = random_bytes($this->encryptionMethod);
			$ciphertext = sodium_crypto_secretbox($data, $nonce, $this->secret);
			$encoded = base64_encode($nonce . $ciphertext);
			return $encoded;
		else:
			return null;
		endif;
	}

	protected function decryptData(string $data = null): ?string
	{
		if(!is_null($data)):
			$decoded = base64_decode($data);
			$nonce = mb_substr($decoded, 0, $this->encryptionMethod, '8bit');
			$ciphertext = mb_substr($decoded, $this->encryptionMethod, null, '8bit');
			$plaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, $this->secret);
			return $plaintext;
		else:
			return null;
		endif;
	}

	public function formOptions(string $field = null): array
	{
		switch($field){
			case 'ordermethod':
				return array(
					'online',
					'phone',
					'email'
				);
				break;
			case 'estdelivery_number':
				return array(
					'1-2',
					'3-4',
					'5-6',
					'7-8',
					'9+'
				);
				break;
			case 'estdelivery_term':
				return array(
					'per day',
					'per week',
					'per fortnight',
					'per month',
					'per 2 months',
					'per school half term',
					'per school term',
					'per school year'
				);
				break;
			default:
				return array();
				break;
		}
	}

	public function isAdmin(object $user = null): int
	{
		if(in_array(6, $user->getAuthorisedViewLevels())):
			return 1;
		else:
			return 0;
		endif;
	}
}
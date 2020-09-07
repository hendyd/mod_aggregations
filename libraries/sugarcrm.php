<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Log\Log;

class SugarCRM {

	const base_url = 'https://crm.2buy2.com/rest/v11';

	const oauth = array(
		'url' => SugarCRM::base_url.'/oauth2/token',
		'params' => array(
	        "grant_type" => "password",
	        "client_id" => "IHJMBLGz98FDjrxA",
	        "client_secret" => "RC10iEWXRCjrNjO8",
	        "username" => "api_user",
	        "password" => "source!serve!save123",
	        "platform" => "sbhnw_website"
	    )
	);

	// getCRM Data function
	public function getCRMData(string $module,string $record = null,array $filter = array())
	{
		$oauth2_token_response = SugarCRM::call(SugarCRM::oauth['url'], '', 'POST', SugarCRM::oauth['params']);
		if(!empty($record)):
			$url = SugarCRM::base_url.'/'.$module.'/'.$record;
			$record_response = SugarCRM::call($url, $oauth2_token_response->access_token, 'GET');
		elseif(!empty($filter)):
			$params = array(
				'filter' => array($filter)
			);
			$url = SugarCRM::base_url.'/'.$module.'/filter';
			$record_response = SugarCRM::call($url, $oauth2_token_response->access_token, 'GET', $params);
		endif;
		if(isset($record_response->error)){
			if(is_array($record_response->error_message)){
				$error = $record_response->error_message[0];
			} else {
				$error = $record_response->error_message;
			}
			Log::add('CRM Data error: '.$error, Log::ERROR, 'mod_aggregations');
			return $error; die();
		} else {
			return $record_response;
			Log::add('CRM integration worked as expected. Module: '.$module, Log::DEBUG, 'mod_aggregations');
		}
	}	

	protected function call($url,$oauthtoken='',$type='GET',$arguments=array(),$encodeData=true,$returnHeaders=false)
	{
        $type = strtoupper($type);
        if ($type == 'GET'){
            $url .= "?" . http_build_query($arguments);
        }
        $curl_request = curl_init($url);
        if ($type == 'POST'){
            curl_setopt($curl_request, CURLOPT_POST, 1);
        } elseif ($type == 'PUT'){
            curl_setopt($curl_request, CURLOPT_CUSTOMREQUEST, "PUT");
        } elseif ($type == 'DELETE'){
            curl_setopt($curl_request, CURLOPT_CUSTOMREQUEST, "DELETE");
        }
        curl_setopt($curl_request, CURLOPT_HEADER, $returnHeaders);
        curl_setopt($curl_request, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_request, CURLOPT_FOLLOWLOCATION, 0);
        if(empty($oauthtoken)):
            curl_setopt($curl_request, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json"
            ));
        else:
            curl_setopt($curl_request, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "oauth-token: {$oauthtoken}"
            ));
        endif;
        if (!empty($arguments) && $type !== 'GET'){
            if ($encodeData){
                $arguments = json_encode($arguments);
            }
            curl_setopt($curl_request, CURLOPT_POSTFIELDS, $arguments);
        }
        $result = curl_exec($curl_request);
        if ($returnHeaders){
            list($headers, $content) = explode("\r\n\r\n", $result ,2);
            foreach (explode("\r\n",$headers) as $header){
                header($header);
            }
            return trim($content);
        }
        curl_close($curl_request);
        $response = json_decode($result);
        return $response;
    }



}
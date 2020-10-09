<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Response\JsonResponse;

class SugarCRM {

    const status = 'live';

    public function dbCredentials(): object
    {
        switch(SugarCRM::status){
            case 'live':
                return (object) array(
                    'host' => '192.168.254.8',
                    'user' => 'root',
                    'password' => 'G0d15Gr34t!',
                    'database' => 'crm_live'
                );
                break;
            case 'dev':
                return (object) array(
                    'host' => '172.16.39.2',
                    'user' => 'root',
                    'password' => 'G0d15Gr34t!',
                    'database' => 'crm_live'
                );
                break;
        }
    }

    private function base_url(): string
    {
        $db = Factory::getDbo();
        $query = $db
        ->getQuery(true)
        ->select($db->quoteName('crm_baseurl'))
        ->from($db->quoteName('#__crm'))
        ->where($db->quoteName('status').' = '.$db->quote(SugarCRM::status));
        try {
            $db->setQuery($query);
            return $db->loadResult();
        } catch(Exception $e){
            echo new JsonResponse($e);
            exit;
        }
    }

    private function oauthCredentials(): array
    {
        $db = Factory::getDbo();
        $query = $db
        ->getQuery(true)
        ->select($db->quoteName(array('crm_username', 'crm_password', 'crm_client_id', 'crm_client_secret')))
        ->from($db->quoteName('#__crm'))
        ->where($db->quoteName('status').' = '.$db->quote(SugarCRM::status));
        $db->setQuery($query);
        try{
            $crm_result = $db->loadObject();
            return array(
                "grant_type" => "password",
                "client_id" => $crm_result->crm_client_id,
                "client_secret" => $crm_result->crm_client_secret,
                "username" => $crm_result->crm_username,
                "password" => $crm_result->crm_password,
                "platform" => "sbhnw_website"
            );
        } catch(Exception $e){
            echo new JsonResponse($e);
            exit;
        }
    }

    private function getOauth()
    {
        try{
            $oauth2_token_response = SugarCRM::call(SugarCRM::base_url(). "/oauth2/token", '', 'POST', SugarCRM::oauthCredentials());
            return $oauth2_token_response->access_token;
        } catch(Exception $e){
            echo new JsonResponse($e);
            exit;
        }     
    }

    public function api(string $type = 'POST', string $module = null, $data = null, string $record = null, string $related = null, string $method = null)
    {
        $access_token = SugarCRM::getOauth();
        switch(true){
            case $type == 'GET' && $related != null && !empty($record) && !empty($data):
                $record_response = SugarCRM::call(SugarCRM::base_url().'/'.$module.'/'.$record.'/link/'.$related.'/filter', $access_token, $type, $data);
                break;
            case $type == 'GET' && $related != null && !empty($record):
                $record_response = SugarCRM::call(SugarCRM::base_url().'/'.$module.'/'.$record.'/link/'.$related, $access_token, $type);
                break;
            case $type == 'GET' && empty($record):
                $record_response = SugarCRM::call(SugarCRM::base_url().'/'.$module.'/filter', $access_token, $type, array("filter" => array($data)));
                break;
            default:
                switch($record){
                    case 'link':
                        $record_response =  SugarCRM::call(SugarCRM::base_url().'/'.$data, $access_token, $type);
                        break;
                    case 'bulk':
                        $record_response = SugarCRM::bulk_call(SugarCRM::base_url().'/bulk', $data, $access_token);
                        break;                        
                    default:
                        switch($type){
                            case 'GET':
                            case 'DELETE':
                                $record_response = SugarCRM::call(SugarCRM::base_url().'/'.$module.'/'.$record, $access_token, $type);
                               break;
                            case 'PUT':
                                $record_response = SugarCRM::call(SugarCRM::base_url().'/'.$module.'/'.$record, $access_token, $type, $data);
                                break;
                            case 'POST':
                                if(empty($related)):
                                    $record_response = SugarCRM::call(SugarCRM::base_url().'/'.$module, $access_token, $type, $data);
                                else:
                                    $record_response = SugarCRM::call(SugarCRM::base_url().'/'.$module.'/'.$record.'/link/'.$related, $access_token, $type, $data);
                                endif;
                                break;
                        }
                        break;
                }
                break;
        }
        if(isset($record_response->error)):
            return $record_response->error_message;
            exit;
        elseif($method == 'AJAX'):
            $responsejson = json_encode($record_response);
            print_r($responsejson);
        else:
            return $record_response;
        endif;
    }

    protected function bulk_call(string $filter_url = null, array $filter_arguments = null, string $token = null): object
    { 
        $filter_request = curl_init($filter_url);
        curl_setopt($filter_request, CURLOPT_HEADER, false);
        curl_setopt($filter_request, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($filter_request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($filter_request, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($filter_request, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "oauth-token: {$token}"
        ));
        $json_arguments = json_encode($filter_arguments);
        curl_setopt($filter_request, CURLOPT_POSTFIELDS, $json_arguments);
        $filter_response = curl_exec($filter_request);
        $filter_response_obj = json_decode($filter_response);
        return $filter_response_obj;
    }


	public function call($url,$access_token='',$type='GET',$arguments=array(),$encodeData=true,$returnHeaders=false)
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
        if(empty($access_token)):
            curl_setopt($curl_request, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json"
            ));
        else:
            curl_setopt($curl_request, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "oauth-token: {$access_token}"
            ));
        endif;
        if (!empty($arguments) && $type !== 'GET'){
            if ($encodeData){
                $arguments = json_encode($arguments);
            }
            curl_setopt($curl_request, CURLOPT_POSTFIELDS, $arguments);
        }
        try {
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
            Log::add('SugarCRM - made a call', JLog::DEBUG, get_class($this).' - '.__FUNCTION__);
        } catch (RuntimeException $e){
            echo new JsonResponse($e);
            exit;
        }
    }



}
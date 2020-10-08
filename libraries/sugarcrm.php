<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

class SugarCRM {
    const status = 'live';

    protected function base_url()
    {
        $db = Factory::getDbo();
        $query = $db
        ->getQuery(true)
        ->select($db->quoteName('crm_baseurl'))
        ->from($db->quoteName('#__crm'))
        ->where($db->quoteName('status').' = '.$db->quote(SugarCRM::status));
        $db->setQuery($query);
        return $db->loadResult();
    }

    protected function oauth()
    {
        $db = Factory::getDbo();
        $query = $db
        ->getQuery(true)
        ->select($db->quoteName(array('crm_username', 'crm_password', 'crm_client_id', 'crm_client_secret')))
        ->from($db->quoteName('#__crm'))
        ->where($db->quoteName('status').' = '.$db->quote(SugarCRM::status));
        $db->setQuery($query);
        $crm_result = $db->loadObject();
        return (object) array(
            'url' => SugarCRM::base_url().'/oauth2/token',
            'params' => array(
                "grant_type" => "password",
                "client_id" => $crm_result->crm_client_id,
                "client_secret" => $crm_result->crm_client_secret,
                "username" => $crm_result->crm_username,
                "password" => $crm_result->crm_password,
                "platform" => "sbhnw_website"
            )
        );
    }

    public function crmCall(string $type = 'POST', string $module = null, string $record = null, array $criteria = array(), string $link_name = null): object
    {
        $oauth2_token_response = SugarCRM::call(SugarCRM::oauth()->url, '', 'POST', SugarCRM::oauth()->params);
        switch($type){
            case 'POST':
            case 'PUT':
                if(!empty($link_name)):
                    $url = SugarCRM::base_url().'/'.$module.'/'.$record.'/link/'.$link_name;
                elseif(!empty($record)):
                    $url = SugarCRM::base_url().'/'.$module.'/'.$record;
                else:
                    $url = SugarCRM::base_url().'/'.$module;
                endif;
                $record_response = SugarCRM::call($url, $oauth2_token_response->access_token, $type, $criteria);
                break;
            case 'GET':
                if(!empty($link_name)):
                    if(!empty($criteria)):
                        $params = array(
                            'filter' => array($criteria)
                        );
                        $url = SugarCRM::base_url().'/'.$module.'/'.$record.'/link/'.$link_name.'/filter';
                        $record_response = SugarCRM::call($url, $oauth2_token_response->access_token, $type, $params);
                    else:
                        $url = SugarCRM::base_url().'/'.$module.'/'.$record.'/link/'.$link_name;
                        $record_response = SugarCRM::call($url, $oauth2_token_response->access_token, $type);
                    endif;
                elseif(!empty($record) && !empty($criteria)):
                    $params = array(
                        'filter' => array($criteria)
                    );
                    $url = SugarCRM::base_url().'/'.$module.'/'.$record.'/link/'.$link_name.'/filter';
                    $record_response = SugarCRM::call($url, $oauth2_token_response->access_token, $type, $params);
                elseif(!empty($record)):
                    $url = SugarCRM::base_url().'/'.$module.'/'.$record;
                    $record_response = SugarCRM::call($url, $oauth2_token_response->access_token, $type);
                elseif(!empty($criteria)):
                    $params = array(
                        'filter' => array($criteria)
                    );
                    $url = SugarCRM::base_url().'/'.$module.'/filter';
                    $record_response = SugarCRM::call($url, $oauth2_token_response->access_token, $type, $params);
                endif;
                break;
            default:
                break;
        }
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
            Log::add('CRM '.$type.' integration worked as expected. Module: '.$module, Log::DEBUG, 'mod_aggregations');
        }
    }




    public function postCRMData(string $type = 'POST', string $module = '', string $record = '', array $params = array(), string $link_name = '')
    {
        $oauth2_token_response = SugarCRM::call(SugarCRM::oauth()->url, '', 'POST', SugarCRM::oauth()->params);
        if(!empty($link_name)):
            $url = SugarCRM::base_url().'/'.$module.'/'.$record.'/link/'.$link_name;
        elseif(!empty($record)):
            $url = SugarCRM::base_url().'/'.$module.'/'.$record;
        else:
            $url = SugarCRM::base_url().'/'.$module;
        endif;
        $record_response = SugarCRM::call($url, $oauth2_token_response->access_token, $type, $params);
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
            Log::add('CRM '.$type.' integration worked as expected. Module: '.$module, Log::DEBUG, 'mod_aggregations');
        }
    }

    // getCRM Data function
    public function getCRMData(string $module,string $record = null,array $filter = array(), string $link_name = null)
    {
        $oauth2_token_response = SugarCRM::call(SugarCRM::oauth()->url, '', 'POST', SugarCRM::oauth()->params);
        if(!empty($link_name)):
            if(!empty($filter)):
                $params = array(
                    'filter' => array($filter)
                );
                $url = SugarCRM::base_url().'/'.$module.'/'.$record.'/link/'.$link_name.'/filter';
                $record_response = SugarCRM::call($url, $oauth2_token_response->access_token, 'GET', $params);
            else:
                $url = SugarCRM::base_url().'/'.$module.'/'.$record.'/link/'.$link_name;
                $record_response = SugarCRM::call($url, $oauth2_token_response->access_token, 'GET');
            endif;
        elseif(!empty($record) && !empty($filter)):
            $params = array(
                'filter' => array($filter)
            );
            $url = SugarCRM::base_url().'/'.$module.'/'.$record.'/link/'.$link_name.'/filter';
            $record_response = SugarCRM::call($url, $oauth2_token_response->access_token, 'GET', $params);
        elseif(!empty($record)):
            $url = SugarCRM::base_url().'/'.$module.'/'.$record;
            $record_response = SugarCRM::call($url, $oauth2_token_response->access_token, 'GET');
        elseif(!empty($filter)):
            $params = array(
                'filter' => array($filter)
            );
            $url = SugarCRM::base_url().'/'.$module.'/filter';
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
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Subscriptions_cron extends MY_Controller
{
	function index()
	{
          $this->load->database();
          $this->settings = Setting::first();
          
          if( isset($_REQUEST['schedule_date']) )
          {
              $next_schedule_date = $_REQUEST['schedule_date'];
          }
          else
          {
              $next_schedule_date = date('Y-m-d',strtotime('now -1 day'));
          }
          
          $orderQry = "SELECT pus.user_id,u.firstname,u.lastname,u.email,pus.package_id,pus.start_date,pus.end_date,pus.id as pusID
                       FROM propay_user_subscription as pus
                       INNER JOIN users as u on u.id = pus.user_id
                       WHERE pus.cron_status = 1 AND pus.status=0 AND u.status = 'active' AND pus.end_date = '".$next_schedule_date."' 
                       LIMIT 5";//
          
          $orderRes = $this->db->query($orderQry)->result_array();
          if(!empty($orderRes))
          {
              foreach($orderRes as $orderResVal)
              {
                  $this->db->query("UPDATE propay_user_subscription SET cron_status = 2 WHERE id = ".$orderResVal['pusID']);
              }
              
              foreach($orderRes as $orderResVal)
              {
                  $propay_dataQry = "SELECT pd.*
                                     FROM propay_data as pd
                                     WHERE pd.user_id = '".$orderResVal['user_id']."' AND pd.set_default = 1";//

                  $propay_dataRes = $this->db->query($propay_dataQry)->result_array();
                  if(!empty($propay_dataRes))
                  {
                        $futureQry = "SELECT pus.* FROM propay_user_subscription as pus
                                      WHERE pus.status=1 AND pus.start_date = '".date('Y-m-d',strtotime($next_schedule_date.' +1 day'))."' AND pud.user_id=".$orderResVal['user_id'];//
                        $futureRes = $this->db->query($futureQry)->result_array();
                        if(!empty($futureRes))
                        {
                            $this->db->query('UPDATE propay_user_subscription SET status="0" WHERE id = '.$futureRes[0]['id']);
                        }
                        else
                        {
                            $payerID = $propay_dataRes[0]['payer_account_id'];
                            $paymentMethodID = $propay_dataRes[0]['payment_method_id'];
                            
                            $package_id = $orderResVal['package_id'];
                            $package_dataVal = $this->db->query('SELECT * FROM package WHERE id = '.$package_id)->result_array();
                            $trial_version = $package_dataVal[0]['trial_version'];        
                            $discount = $package_dataVal[0]['discount'];        
                            $duration = $package_dataVal[0]['duration'];        
                            $amount = $package_dataVal[0]['amount'];        
                            $amount = ($amount*100);
                            
                                        
                            $pkg_last_date = strtotime("now");
                            $start_date = date('Y-m-d', $pkg_last_date);
                            $end_date = strtotime($start_date." +".$duration." month");
                            $end_date = date('Y-m-d', $end_date);
                            
                            
                            $processPaymentMethoddata = array(
                                "package_id"           =>  $package_id,
                                "Amount"            =>  $amount,
                                "CurrencyCode"      =>  $this->settings->propay_currency,
                                "PayerAccountId"    =>  $payerID,
                                "PaymentMethodID"   =>  $paymentMethodID,
                                "start_date"   =>  $start_date,
                                "end_date"   =>  $end_date,
                                "propay_data_last_inserted_ID"   =>  $propay_dataRes[0]['propay_data_id'],
                                "user_id"   =>  $orderResVal['user_id']
                            );
                            
                            $args = array(
                                $this->settings->propay_auth_token,
                                $this->settings->propay_biller_id,
                                $this->settings->propay_profile_id,
                                $payerID,                                         //Payer ID
                                $paymentMethodID,                                //Payment Method ID
                                $amount,                                        //Amount
                                $this->settings->propay_currency,
                                "test111",                                        //Comment
                                "test111",                                        //Invoice Number
                                "123"
                            );        
                            
                            $processPaymentMethod = $this->process_payment_method($args,$processPaymentMethoddata);
                            
                            if($processPaymentMethod == "success"){
                                //$this->session->set_flashdata('message', 'success:Thanks for subscription! Please check your email.');
                                echo $processPaymentMethod;
                            } else {
                                //$this->session->set_flashdata('message', 'error:Your transaction is not completed successfully. Please try again.');
                                echo 'error';
                            }
                        }
                  }
                  $this->db->query('UPDATE propay_user_subscription SET cron_status = 3, status="2" WHERE id = '.$orderResVal['pusID']);
              }
          }
          else
          {
              echo 'Empty';
          }
          die();
	}	
    
    /*Existing CC payment[start]*/
    function process_payment_method($arguments,$processPaymentMethoddata)
    {             
        $envelope=
        '<?xml version="1.0"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:con="http://propay.com/SPS/contracts" xmlns:typ="http://propay.com/SPS/types">
        <soapenv:Header/>
        <soapenv:Body>
        <con:ProcessPaymentMethodTransaction>
            <con:id>
                <typ:AuthenticationToken>'.$arguments[0].'</typ:AuthenticationToken>
                <typ:BillerAccountId>'.$arguments[1].'</typ:BillerAccountId>
            </con:id>
            <con:transaction>
                <typ:Amount>'.$arguments[5].'</typ:Amount>
                <typ:Comment1>test</typ:Comment1>
                <typ:Comment2>test</typ:Comment2>
                <typ:Invoice>test</typ:Invoice>
                <typ:MerchantProfileId>'.$arguments[2].'</typ:MerchantProfileId>
                <typ:PayerAccountId>'.$arguments[3].'</typ:PayerAccountId>
            </con:transaction>
            <con:paymentMethodID>'.$arguments[4].'</con:paymentMethodID>
        </con:ProcessPaymentMethodTransaction>
        </soapenv:Body>
        </soapenv:Envelope>';
        
        $SOAP_Action = "ProcessPaymentMethodTransaction"; 
        $process_payment_submit_request = $this->process_payment_submit_request($envelope, $SOAP_Action ,$processPaymentMethoddata);
        return $process_payment_submit_request;
    }
    function process_payment_submit_request($envelope, $SOAP_Action ,$processPaymentMethoddata)
    {
        /* The HTTP header must include the SOAPAction */ 
        $header = array(
        "Content-type:text/xml; charset=\"utf-8\"",
        "Accept: text/xml",
        "SOAPAction: http://propay.com/SPS/contracts/SPSService/".$SOAP_Action
        );
        $soap_do = curl_init();
        /*Change the following URL to point to production instead of integration */
        //curl_setopt($soap_do, CURLOPT_URL, "https://protectpaytest.propay.com/API/SPS.svc");
        curl_setopt($soap_do, CURLOPT_URL, $this->settings->propay_apiroute ."/protectpay/sps.svc");
        curl_setopt($soap_do, CURLOPT_TIMEOUT, 30);
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($soap_do, CURLOPT_POST, true);
        curl_setopt($soap_do, CURLOPT_POSTFIELDS, $envelope);
        curl_setopt($soap_do, CURLOPT_HTTPHEADER, $header);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($soap_do, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        $response = curl_exec($soap_do);
        $err = curl_error($soap_do);
        curl_close($soap_do);
        /*Call Parse Function for the XML response*/
        $process_payment_parse_results = $this->process_payment_parse_results($response,$processPaymentMethoddata);
        return $process_payment_parse_results;
    }
    function process_payment_parse_results($api_response,$processPaymentMethoddata)
    {
        $doc = new \DOMDocument();
        $doc->loadXML($api_response);
        //Pretty Print response
        $api_result = new \DOMDocument('1.0');
        $api_result->preserveWhiteSpace = false;
        $api_result->formatOutput = true;
        $api_result->loadXML($api_response);
        
        if(isset($doc->getElementsByTagName('ResultCode')->item(0)->nodeValue))
        {
            $result_code = $doc->getElementsByTagName('ResultCode')->item(0)->nodeValue;
            $result_value = $doc->getElementsByTagName('ResultValue')->item(0)->nodeValue;
            if($result_code != '00' || $result_value == "FAILURE")
            {
                $result_message = $doc->getElementsByTagName('ResultMessage')->item(0)->nodeValue;
                $dataVal = 'fail';
                return $dataVal;
            }
            else
            {
                $AVSCode = $doc->getElementsByTagName('AVSCode')->item(0)->nodeValue;
                $AuthorizationCode = $doc->getElementsByTagName('AuthorizationCode')->item(0)->nodeValue;
                $CurrencyConversionRate = $doc->getElementsByTagName('CurrencyConversionRate')->item(0)->nodeValue;
                $CurrencyConvertedAmount = $doc->getElementsByTagName('CurrencyConvertedAmount')->item(0)->nodeValue;
                $CurrencyConvertedCurrencyCode = $doc->getElementsByTagName('CurrencyConvertedCurrencyCode')->item(0)->nodeValue;
                $TransactionHistoryId = $doc->getElementsByTagName('TransactionHistoryId')->item(0)->nodeValue;
                $TransactionId = $doc->getElementsByTagName('TransactionId')->item(0)->nodeValue;
                $TransactionResult = $doc->getElementsByTagName('TransactionResult')->item(0)->nodeValue;
                $CVVResponseCode = $doc->getElementsByTagName('CVVResponseCode')->item(0)->nodeValue;
                $GrossAmt = $doc->getElementsByTagName('GrossAmt')->item(0)->nodeValue;
                $NetAmt = $doc->getElementsByTagName('NetAmt')->item(0)->nodeValue;
                $PerTransFee = $doc->getElementsByTagName('PerTransFee')->item(0)->nodeValue;
                $Rate = $doc->getElementsByTagName('Rate')->item(0)->nodeValue;
                $GrossAmtLessNetAmt = $doc->getElementsByTagName('GrossAmtLessNetAmt')->item(0)->nodeValue;
                        
                if((!empty($result_code)) && ($result_code ==  00) ){
                    
                    
                    $this->db->query('UPDATE propay_data SET set_default="0" WHERE user_id = '.$processPaymentMethoddata['user_id']);
                    $this->db->query('UPDATE propay_data SET set_default="1" WHERE id = '.$processPaymentMethoddata['propay_data_last_inserted_ID']);
                    
                    $propay_payment_detail = array('user_id' => $processPaymentMethoddata['user_id'], 
                                                    'propay_data_id' => $processPaymentMethoddata['propay_data_last_inserted_ID'],
                                                    'avscode' => $AVSCode,
                                                    'authorization_code' => $AuthorizationCode,
                                                    'currency_conversion_rate' => $CurrencyConversionRate,
                                                    'currency_converted_amount' => $CurrencyConvertedAmount,
                                                    'currency_converted_currency_code' => $CurrencyConvertedCurrencyCode,
                                                    'gross_amt' => $GrossAmt,
                                                    'gross_amt_less_net_amt' => $GrossAmtLessNetAmt,
                                                    'net_amt' => $NetAmt,
                                                    'per_trans_fee' => $PerTransFee,
                                                    'rate' => $Rate,
                                                    'result_code' => $result_code,
                                                    'result_value' => $result_value,
                                                    'result_message' => "",
                                                    'transaction_history_id' => $TransactionHistoryId,
                                                    'transaction_id' => $TransactionId,
                                                    'transaction_result' => $TransactionResult,
                                                    'cvv_response_code' => $CVVResponseCode);
                    $insert_data = PropayPaymentDetail::create($propay_payment_detail);
                    $propay_payment_detail_last_inserted_ID = $insert_data->id;    
                    
                    
                    $propay_user_data = array('payment_detail_id' => $propay_payment_detail_last_inserted_ID, 
                                               'package_id' => $processPaymentMethoddata['package_id'],
                                               'user_id' => $processPaymentMethoddata['user_id'],
                                               'start_date' => $processPaymentMethoddata['start_date'],
                                               'end_date' => $processPaymentMethoddata['end_date'],
                                               'status' => 0);
                    $insert_data = PropayUserSubscription::create($propay_user_data);
                    
                    $dataVal = 'success';
                    return $dataVal;
                }
            }         
        }    
        else
        {
            $dataVal = 'fail';
            return $dataVal;
        };
        
    } 
    /*Existing CC payment[end]*/
}

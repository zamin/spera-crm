<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Aosettings extends MY_Controller
{
	
	function __construct()
	{
		parent::__construct();
		$access = FALSE;
		unset($_POST['DataTables_Table_0_length']);
		$this->view_data['update'] = FALSE;
		//echo "<pre>";print_r($this->sessionArr);exit;
		if (!$this->user) {
            $this->view_data['error'] = "true";
            $this->session->set_flashdata('message', 'error: You have no access to any modules!');
            redirect('login');
        }
        if(!$this->sessionArr['company_id']) {
            $this->view_data['error'] = "true";
            $this->session->set_flashdata('message', 'error: You have no access to any modules!');
            redirect('login');
        }
        $this->load->database();
        
		$user_company_id = $this->sessionArr['company_id'];
        $user_role_id = $this->sessionArr['role_id'];

        $subscriptions_new = Subscription::find_by_sql('SELECT u.*,pus.start_date,pus.end_date, pd.name
                                                    FROM users u
                                                    INNER JOIN propay_user_subscription as pus on pus.user_id = u.id
                                                    INNER JOIN package as pd on pd.id = pus.package_id
                                                    WHERE u.id = '.$this->sessionArr['user_id'].' and pus.package_id = 3
                                                    and pus.start_date <= "'.date('Y-m-d').'" and pus.end_date >= "'.date('Y-m-d').'"
                                                    order by pus.id desc');
        $user_package_id = $subscriptions_new[0]->id;
        $user_package_name = $subscriptions_new[0]->name;
        //echo '<pre>';print_r($subscriptions_new);die;
        //and pus.start_date >= `'.date('Y-m-d').'` and pus.end_date <= `'.date('Y-m-d').'`
        
        if($this->sessionArr['user_id'] == $user_package_id)
        {	
        	$this->view_data['submenu'] = array(
		 		$this->lang->line('application_settings') => base_url().'aosettings',
		 		$this->lang->line('application_calendar') => base_url().'aosettings/calendar',
		 		$this->lang->line('application_theme_options') => base_url().'aosettings/themeoptions',
		 		$this->lang->line('application_propay') => base_url().'aosettings/paymentaccount',
                $this->lang->line('application_billing') => base_url().'aosettings/allsubscriptions',
                $this->lang->line('application_enable_api') => base_url().'aosettings/apisettingview',
		 		);	
        }
        else
        {
        	$this->view_data['submenu'] = array(
		 		$this->lang->line('application_settings') => base_url().'aosettings',
		 		$this->lang->line('application_calendar') => base_url().'aosettings/calendar',
		 		$this->lang->line('application_theme_options') => base_url().'aosettings/themeoptions',
		 		$this->lang->line('application_propay') => base_url().'aosettings/paymentaccount',
                $this->lang->line('application_billing') => base_url().'aosettings/allsubscriptions',
		 		);	
        }
		
		$this->config->load('defaults');
		$this->settings = Setting::first();
		$this->view_data['update_count'] = FALSE;
	}

	function index()
	{
		//echo $this->lang->line('application_settings');exit;
		//echo "<pre>";print_r($this->sessionArr);exit;
		$company_id=$this->sessionArr['company_id'];
		$this->view_data['breadcrumb'] = $this->lang->line('application_settings');
		$this->view_data['breadcrumb_id'] = "settings";
		$company=Company::find($company_id);
		$this->view_data['company']=$company;
		//echo "<pre>";print_r($company);exit;
		$settings = $this->db->query('Select * from company_details where company_id="'.$company_id.'"')->result();
		$this->view_data['settings']=$settings[0];
		//echo "<pre>";print_r($settings);exit;
		$this->view_data['form_action'] = base_url().'aosettings/settings_update';
		$this->content_view = 'settings/aosettings/settings_all';

		$this->load->helper('curl');
		$object = remote_get_contents('http://fc2.luxsys-apps.com/updates/xml.php?code='.$this->view_data['settings']->pc, 1);
		$object = json_decode($object);
		
		if(isset($object->error) && isset($object->lastupdate)) {
			if($object->error == FALSE && $object->lastupdate > $this->view_data['settings']->version){
			$this->view_data['update_count'] = "1";
			}
		}

	}
	function paymentaccountdelete($id = FALSE) {
        $sql = 'DELETE FROM spera_accounts WHERE spera_accounts_id = "' . $id . '"';
        $this->db->query($sql);
        $this->session->set_flashdata('message', 'success:' . $this->lang->line('messages_delete_paymentaccount_success'));
        redirect('aosettings/paymentaccount/');
    }

    function paymentaccount(){
    	//echo $this->lang->line('application_payment_account');exit;
        $this->view_data['breadcrumb'] = "PROPAY";
        $this->view_data['breadcrumb_id'] = "paymentaccount";

        if($_POST){

         }else{
                $spera_accounts = Subscription::find_by_sql('SELECT u.* FROM spera_accounts u WHERE u.user_id = '.$this->sessionArr['user_id']);
        
                if(!empty($spera_accounts))
                {   
                    $account_all = array();
                    $i=0;
                    
                    foreach($spera_accounts as $key =>$value)
                    {
                        $account_all[$i]['spera_accounts_id']=$value->spera_accounts_id;
                        $account_all[$i]['account_number']=$value->account_number;
                        $account_all[$i]['profile_id']=$value->merchant_profile_id;
                        $account_all[$i]['is_default']=$value->is_default;
                        $i++;
                    }
                }
                
                $this->view_data['accounts']=$account_all;
                
             //$this->view_data['settings'] = Setting::first();
             $this->view_data['states'] = States::find('all',array('conditions' => array('1=?','1')));
             $this->view_data['form_action'] = base_url().'aosubscriptions/create_payment_account/';
             $this->content_view = 'settings/aosettings/paymentaccount';
         }
    }
    
    function paymentaccountcreate(){
        //echo $this->lang->line('application_payment_account');exit;
        $this->view_data['breadcrumb'] = "PROPAY";
        $this->view_data['breadcrumb_id'] = "paymentaccount";

        if($_POST){
            
            $userID = $this->sessionArr['user_id'];
            $cerStr          = $this->settings->propay_certstring;//*CertString
            $termID          = $this->settings->propay_termid; // termid use for authentication
            $statusCode      = "";
            $account_number  = $_POST['account_number'];
            $email           = $_POST['source_email'];

            $envelope= '<?xml version="1.0"?>
                    <!DOCTYPE Request.dtd>
                    <XMLRequest>
                    <certStr>'.$cerStr.'</certStr>
                    <class>partner</class>
                        <XMLTrans>
                            <transType>13</transType>
                            <sourceEmail>'.$email.'</sourceEmail>
                        </XMLTrans>
                    </XMLRequest>';

            $api_response = $this->Submit_Request($envelope);
            
            $result = simplexml_load_string($api_response); 
            
            $array =  (array) $result;
            $array1 =  (array) $array['XMLTrans'];
            
            $response = "";
            
            if(isset($result->XMLTrans->status)) {

                $status = $result->XMLTrans->status; // status code return from api
                $statusCode=$status;
                
                if($status != '00') {    
                    
                    $this->session->set_flashdata('message', 'error:The account number or username does not exist !');
                
                } else {       
                    //success
                    $tranType     = $array1['transType'];
                    $sourceEmail  = $array1['sourceEmail'];
                    $accountNum   = $array1['accountNum'];
                    $tier         = $array1['tier'];

                    $expiration   = $array1['expiration'];
                    //$date1        = new \DateTime($expiration);
                    $expiration   = date('Y-m-d H:i:s',strtotime($expiration));

                    $signupDate   = $array1['signupDate'];
                    //$date2        = new \DateTime($signupDate);
                    //$signupDate   = $date2->format('Y-m-d H:i:s');
					$signupDate   = date('Y-m-d H:i:s',strtotime($signupDate));

                    $affiliation  = $array1['affiliation'];
                    $accntStatus  = $array1['accntStatus'];
                    $addr         = $array1['addr'];
                    $city         = $array1['city'];
                    $state        = $array1['state'];
                    $apiReady     = $array1['apiReady'];
                    
                    if ( $account_number == $accountNum && $email == $sourceEmail )
                    {
                        $spera_accounts = Spera_accounts::find_by_sql('SELECT u.* FROM spera_accounts u WHERE u.account_number="'.$accountNum.'" AND u.user_id = '.$userID);
                        $spera_accounts1 = Spera_accounts::find_by_sql('SELECT u.* FROM spera_accounts u WHERE u.user_id = '.$userID);
                        
                        if(!empty($spera_accounts)) {
                            $this->session->set_flashdata('message', 'error:The account number already exist in our record !');
                            redirect('aosettings/paymentaccount/');
                            die();
                        }
                        $is_default = 1;
                        if( !empty($spera_accounts1) ){$is_default = 0;}
                        
                        $newArr = array('account_number' => $accountNum, 
                                        'account_username' => $sourceEmail,
                                        'account_type' => $tier,
                                        'account_balance' => 0,
                                        'account_status' => $accntStatus,
                                        'account_expiration' => $expiration,
                                        'account_signup' => $signupDate,
                                        'user_id' => $userID,
                                        'is_default' => $is_default );
                       
						//exit;
                        $insert_data = Spera_accounts::create($newArr);
						
                        $Args = array(
                            $this->settings->propay_auth_token,
                            $this->settings->propay_biller_id,
                            "LegacyProPay",
                            $this->settings->propay_certstring,
                            $accountNum,
                            $this->settings->propay_termid
                            );
                        $this->CreateMerchantProfile($Args,$insert_data->spera_accounts_id);
                        
                        $this->session->set_flashdata('message', 'success:The spera account number has been added successfully!');
                    }
                    else
                    {
                        $this->session->set_flashdata('message', 'error:The account number or username does not exist !');
                    }
                }              
            }
            else
            {
                // $response="XML parse error - please verify your XMLRequest is well-formed, in the proper format and encoding and try again.";
                // print_r($response);
            }
            redirect('aosettings/paymentaccount/');
            
         }else{
                $spera_accounts = Subscription::find_by_sql('SELECT u.* FROM spera_accounts u WHERE u.user_id = '.$this->sessionArr['user_id']);
        
                if(!empty($spera_accounts))
                {   
                    $account_all = array();
                    $i=0;
                    
                    foreach($spera_accounts as $key =>$value)
                    {
                        $account_all[$i]['spera_accounts_id']=$value->spera_accounts_id;
                        $account_all[$i]['account_number']=$value->account_number;
                        $account_all[$i]['profile_id']=$value->merchant_profile_id;
                        $account_all[$i]['is_default']=$value->is_default;
                        $i++;
                    }
                }
                
                $this->view_data['accounts']=$account_all;
                
             //$this->view_data['settings'] = Setting::first();
             $this->view_data['states'] = States::find('all',array('conditions' => array('1=?','1')));
             $this->view_data['form_action'] = base_url().'aosettings/paymentaccountcreate/';
             $this->content_view = 'settings/aosettings/paymentaccountcreate';
         }
    }
    
    function CreateMerchantProfile($Arguments,$spera_accounts_id)
    { 
        $request = $this->settings->propay_apiroute."/protectpay/merchantprofiles/";
        $method = "PUT";
        $data = array(
            "ProfileName"           =>'Spera',
            "PaymentProcessor"      =>"LegacyProPay",
            "ProcessorData"         => [
                array(
                    "ProcessorField"   =>"certStr",
                    "Value"            =>$Arguments[3] // provided by propay
                ),
                array(
                    "ProcessorField"   =>"accountNum",
                    "Value"            =>$Arguments[4] // propay account number
                ),
                array(
                    "ProcessorField"   =>"termId",
                    "Value"            =>$Arguments[5] // propay termId number
                )
            ],
        );
        $postData = json_encode($data); 
        $responseData = $this->Submit_Request_get_profile($request,$postData,$method,$spera_accounts_id);
        return $responseData;
    }

    function Submit_Request_get_profile($apiUrl,$postData,$methodType, $spera_accounts_id)
    {
        $err = "";
        $result = "";
        $response = "";
        $auth_billerID = $this->settings->propay_biller_id.":".$this->settings->propay_auth_token;  //billerID: Authentication Token provided by propay
        
        $headers = array(
            'Accept:application/json',
            'Content-Type:application/json',
            'Authorization: Basic '.base64_encode($auth_billerID),
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $methodType);
        if($postData!="")
        {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        $result = curl_exec($ch);   
        $err = curl_error($ch);
        curl_close($ch); 
         
        if($result!="")
        {
            $response = json_decode($result);
            $this->db->query('UPDATE spera_accounts SET merchant_profile_id="'.$response->ProfileId.'" WHERE spera_accounts_id = '.$spera_accounts_id);
        }
    }
    
    function Submit_Request($envelope)
    {
        $header = array(
        "Content-type:text/xml; charset=\"utf-8\"",
        "Accept: text/xml"
        );
        $MSAPI_Call = curl_init();
        curl_setopt($MSAPI_Call, CURLOPT_URL, 'https://epay.propay.com/api/propayapi.aspx');
        curl_setopt($MSAPI_Call, CURLOPT_TIMEOUT, 30);
        curl_setopt($MSAPI_Call, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($MSAPI_Call, CURLOPT_POST, true);
        curl_setopt($MSAPI_Call, CURLOPT_POSTFIELDS, $envelope);
        curl_setopt($MSAPI_Call, CURLOPT_HTTPHEADER, $header);
        curl_setopt($MSAPI_Call, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($MSAPI_Call, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($MSAPI_Call, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        $response = curl_exec($MSAPI_Call);
        $result = simplexml_load_string($response); 
        $err = curl_error($MSAPI_Call);
        curl_close($MSAPI_Call); 
        return $response;
    }
    
    
    function calendar(){
		$this->view_data['breadcrumb'] = $this->lang->line('application_calendar');
		$this->view_data['breadcrumb_id'] = "calendar";
		$company_id=$this->sessionArr['company_id'];
		if($_POST){		
			unset($_POST['send']);
			//echo "<pre>";print_r($_POST);exit;
			$calendar_google_api_key=$_POST['calendar_google_api_key'];
			$calendar_google_event_address=$_POST['calendar_google_event_address'];
			$get_company_details=$this->db->query("select count(*) as count from company_details where company_id='".$company_id."'")->row_array();
			//echo "<pre>";print_r($get_company_details['count']);exit;
			
			if($get_company_details['count']==0)
			{
				
				$company_arr=array(
					"company_id"=>$company_id,
					"calendar_google_api_key"=>$calendar_google_api_key,
					"calendar_google_event_address"=>$calendar_google_event_address
				);
				$this->db->insert('company_details',$company_arr);
				//echo $insert_id=$this->db->insert_id();
				//exit;
				if(!empty($insert_id))
				{
					$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_save_settings_success'));
	 				redirect('aosettings/calendar');
				}
				else
				{
					$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_save_settings_error'));
		 			redirect('aosettings/calendar');
				}
			}
			else
			{
				$company_arr=array(
					"company_id"=>$company_id,
					"calendar_google_api_key"=>$calendar_google_api_key,
					"calendar_google_event_address"=>$calendar_google_event_address
				);
				$this->db->where('company_id',$company_id);
				$this->db->update('company_details',$company_arr);
				if($this->db->affected_rows() > 0)
				{
					$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_save_settings_success'));
	 				redirect('aosettings/calendar');
				}
				else
				{
					$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_save_settings_error'));
		 			redirect('aosettings/calendar');
				}
			}
		
 		}else{
 			
 		$this->view_data['settings'] = CompanyDetails::find_by_company_id($company_id);
		$this->view_data['form_action'] = base_url().'aosettings/calendar';
		$this->content_view = 'settings/aosettings/calendar';
 		}
	}

	function themeoptions($val = FALSE){
		$company_id=$this->sessionArr['company_id'];
		//echo $this->lang->line('application_theme_options');exit;
		$this->view_data['breadcrumb'] = $this->lang->line('application_theme_options');
		$this->view_data['breadcrumb_id'] = "themeoptions";
		$settings = $this->db->query('Select * from  company_details where company_id="'.$company_id.'"')->result();
		$this->view_data['settings']=$settings[0];
		
		if($_POST)
		{
			//echo "<pre>";print_r($_POST);exit;
			$get_company_details=$this->db->query("select count(*) as count from company_details where company_id='".$company_id."'")->row_array();
			if($get_company_details['count']==0)
			{
				if(is_uploaded_file($_FILES['userfile']['tmp_name'])){
					$config['upload_path'] = './assets/blueline/images/backgrounds/';
					$config['encrypt_name'] = FALSE;
					$config['overwrite'] = TRUE;
					$config['allowed_types'] = 'gif|jpg|jpeg|png';

					$this->load->library('upload', $config);

					if ( $this->upload->do_upload())
					{
						$data = array('upload_data' => $this->upload->data());
						$_POST['login_background'] = $data['upload_data']['file_name'];
					}
				}
				if(is_uploaded_file($_FILES['userfile2']['tmp_name'])){

					$config['upload_path'] = './files/media/';
					$config['encrypt_name'] = FALSE;
					$config['overwrite'] = TRUE;
					$config['allowed_types'] = 'gif|jpg|jpeg|png|svg';

					$this->load->library('upload', $config);

					if ( $this->upload->do_upload("userfile2"))
					{
						$data = array('upload_data' => $this->upload->data());
						$_POST['login_logo'] = "files/media/".$data['upload_data']['file_name'];
					}
				}

				if(!isset($_POST['custom_colors']))
			    {
			    	$_POST['custom_colors'] = 0;
			    }
				unset($_POST['file-name']);
				unset($_POST['userfile2']);
				unset($_POST['send']);
				$this->db->insert('company_details',$_POST);
				$insert_id=$this->db->insert_id();
				//exit;
				if(!empty($insert_id))
				{
					$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_save_settings_success'));
	 				redirect('aosettings/themeoptions');
				}
				else
				{
					$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_save_settings_error'));
		 			redirect('aosettings/themeoptions');
				}
			}
			else
			{
				if(is_uploaded_file($_FILES['userfile']['tmp_name']))
				{
					$config['upload_path'] = './assets/blueline/images/backgrounds/';
					$config['encrypt_name'] = FALSE;
					$config['overwrite'] = TRUE;
					$config['allowed_types'] = 'gif|jpg|jpeg|png';

					$this->load->library('upload', $config);

					if($this->upload->do_upload())
					{
						$data = array('upload_data' => $this->upload->data());
						$_POST['login_background'] = $data['upload_data']['file_name'];
					}
				}
				if(is_uploaded_file($_FILES['userfile2']['tmp_name']))
				{

					$config['upload_path'] = './files/media/';
					$config['encrypt_name'] = FALSE;
					$config['overwrite'] = TRUE;
					$config['allowed_types'] = 'gif|jpg|jpeg|png|svg';

					$this->load->library('upload', $config);

					if($this->upload->do_upload("userfile2"))
					{
						$data = array('upload_data' => $this->upload->data());
						$_POST['login_logo'] = "files/media/".$data['upload_data']['file_name'];
					}
				}
				if(!isset($_POST['custom_colors']))
			    {
			    	$_POST['custom_colors'] = 0;
			    }
				unset($_POST['send']);
				//echo "<pre>";print_r($_POST);exit;
	 			$this->db->where('company_id',$company_id);
				$this->db->update('company_details',$_POST);
				if($this->db->affected_rows() > 0)
				{
					$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_save_settings_success'));
	 				redirect('aosettings/themeoptions');
				}
				else
				{
					$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_save_settings_error'));
		 			redirect('aosettings/themeoptions');
				}
			}
		}

		$this->load->helper('file');
 		$backgrounds =	get_filenames('./assets/blueline/images/backgrounds/');
 		$this->view_data['backgrounds'] = array_diff($backgrounds, array("index.html"));
 		
 		
		$this->view_data['form_action'] = base_url().'aosettings/themeoptions';
		$this->content_view = 'settings/aosettings/themeoptions';
 		
	}

	function settings_update()
	{
		$company_id=$this->sessionArr['company_id'];
		
		if($_POST)
		{
			//echo "<pre>";print_r($_POST);exit;
			$invoice_contact = $_POST['invoice_contact'];
			$invoice_address = $_POST['invoice_address'];
			$country = $_POST['country'];
			$state = $_POST['state'];
			$zipcode = $_POST['zipcode'];
			$invoice_city    = $_POST['invoice_city'];
			$invoice_tel     = $_POST['invoice_tel'];
			$domain 		 = $_POST['domain'];
			$template = $_POST['template'];
			$tax = $_POST['tax'];
			$second_tax = $_POST['second_tax'];
			$vat = $_POST['vat'];
			$currency = $_POST['currency'];
			//$invoice_terms = $_POST['invoice_terms'];
			//$estimate_terms = $_POST['estimate_terms'];
			
			$company_arr=array(
				"company_id"=>$company_id,
				"state" => $state,
				"zipcode" => $zipcode,
				"country"=>$country,
				"contact" => $invoice_contact,
				"address" => $invoice_address,
				"city"    => $invoice_city,
				"phone"     => $invoice_tel,
				"domain" 		 => $domain,
				//"template" => $template,
				"tax" => $tax,
				"second_tax" => $second_tax,
				"vat" => $vat,
				"currency" => $currency,
				//"invoice_terms" => $invoice_terms,
				//"estimate_terms" => $estimate_terms
			);

			$get_company_details=$this->db->query("select count(*) as count from company_details where company_id='".$company_id."'")->row_array();
			if($get_company_details['count']==0)
			{
				$config['upload_path'] = './files/media/';
				$config['allowed_types'] = 'gif|jpg|png';
				$config['max_size']	= '600';
				$config['max_width']  = '300';
				$config['max_height']  = '300';

				$this->load->library('upload', $config);

				if(!$this->upload->do_upload())
				{
					$error = $this->upload->display_errors('', ' ');
					if($error != "You did not select a file to upload.")
					{
						//$this->session->set_flashdata('message', 'error:'.$error);
					}
				}
				else
				{
					$data = array('upload_data' => $this->upload->data());
					$company_arrT['logo'] = "files/media/".$data['upload_data']['file_name'];
						
				}
				/*if(!$this->upload->do_upload("userfile2"))
				{
					$error = $this->upload->display_errors('', ' ');
					if($error != "You did not select a file to upload."){
						//$this->session->set_flashdata('message', 'error:'.$error);	
				    }
				}
				else
				{
					$data = array('upload_data' => $this->upload->data());
					$company_arr['invoice_logo'] = "files/media/".$data['upload_data']['file_name'];
						
				}*/
				

				$company=Company::find_by_id($company_id);
				$company_name=trim(htmlspecialchars($_POST['company_name']));
				$company->name=$company_name;
	            $company->save();
				
				unset($_POST['send']);
				$this->db->insert('company_details',$company_arr);
				$insert_id=$this->db->insert_id();
				//exit;
				if(!empty($insert_id) || $company)
				{
					if($company)
					{
						$company_slug = $this->slug($company_name);
						$this->db->query('Update company_details set slug="'.$company_slug.'" where company_id="'.$this->sessionArr['company_id'].'"');
					}

					$this->session->unset_userdata('company_name');
					$this->session->set_userdata(array('company_name'=>$company_name));
					$session_c_name=$this->session->userdata('company_name');
					$this->session->CI->sessionArr['company_name']=$session_c_name;
					//echo "<pre>";print_r($this->sessionArr);exit;
					
					$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_save_settings_success'));
	 				redirect('aosettings');
				}
				else
				{
					$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_save_settings_error'));
		 			redirect('aosettings');
				}
			}
			else
			{	
				$config['upload_path'] = './files/media/';
				$config['allowed_types'] = 'gif|jpg|png';
				$config['max_size']	= '600';
				$config['max_width']  = '300';
				$config['max_height']  = '300';

				$this->load->library('upload', $config);

				if(!$this->upload->do_upload())
				{
					$error = $this->upload->display_errors('', ' ');
					if($error != "You did not select a file to upload.")
					{
						//$this->session->set_flashdata('message', 'error:'.$error);
					}
				}
				else
				{
					$data = array('upload_data' => $this->upload->data());
					$company_arr['logo'] = "files/media/".$data['upload_data']['file_name'];
						
				}
				/*if(!$this->upload->do_upload("userfile2"))
				{
					$error = $this->upload->display_errors('', ' ');
					if($error != "You did not select a file to upload."){
						//$this->session->set_flashdata('message', 'error:'.$error);	
				    }
				}
				else
				{
					$data = array('upload_data' => $this->upload->data());
					$company_arr['invoice_logo'] = "files/media/".$data['upload_data']['file_name'];
						
				}	*/		
				unset($_POST['send']);

				$company=Company::find_by_id($company_id);
				$company_name=$_POST['company'];
				$company->name=$company_name;
	            $company->save();

				$this->db->where('company_id',$company_id);
				$this->db->update('company_details',$company_arr);
				//echo $this->db->affected_rows();exit;
				if($this->db->affected_rows() > 0 || $company)
				{
					if($company)
					{
						$company_slug = $this->slug($company_name);
						$this->db->query('Update company_details set slug="'.$company_slug.'" where company_id="'.$this->sessionArr['company_id'].'"');
					}
					$this->session->unset_userdata('company_name');
					$this->session->set_userdata(array('company_name'=>$company_name));
					$session_c_name=$this->session->userdata('company_name');
					$this->session->CI->sessionArr['company_name']=$session_c_name;
					$this->sessionArr = $this->session->userdata;
					//echo "<pre>";print_r($this->sessionArr);
					$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_save_settings_success'));
	 				redirect('aosettings');
				}
				else
				{
					$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_save_settings_error'));
		 			redirect('aosettings');
				}
			}
		}
	}


	function slug($string, $spaceRepl = "-") 
	{
		// Replace "&" char with "and"
		$string = str_replace("&", "and", $string);
		// Delete any chars but letters, numbers, spaces and _, -
		$string = preg_replace("/[^a-zA-Z0-9 _-]/", "", $string);
		// Optional: Make the string lowercase
		$string = strtolower($string);
		// Optional: Delete double spaces
		$string = preg_replace("/[ ]+/", " ", $string);
		// Replace spaces with replacement
		$string = str_replace(" ", $spaceRepl, $string);
		return $string;
	}
	
	
	function allsubscriptions()
	{
        $this->view_data['breadcrumb'] = $this->lang->line('application_billing');
        $this->view_data['breadcrumb_id'] = "allsubscriptions";
		$subscriptions = Subscription::find_by_sql('SELECT u.*,pus.start_date,pus.end_date, pd.name
                                                    FROM users u
                                                    INNER JOIN propay_user_subscription as pus on pus.user_id = u.id
                                                    INNER JOIN package as pd on pd.id = pus.package_id
                                                    WHERE u.id = '.$this->sessionArr['user_id'].'
                                                    order by pus.id desc ');
        if(!empty($subscriptions))
        {   
            $subscription_all =array();
            $i=0;
            foreach($subscriptions as $key =>$value)
            {
                $subscription_all[$i]['type']=$value->name;
                $subscription_all[$i]['name']=$value->firstname.' '.$value->lastname;
                $subscription_all[$i]['email']=$value->email;
                $subscription_all[$i]['start_date']=$value->start_date;
                $subscription_all[$i]['end_date']=$value->end_date;
                $i++;
            }
        }
		$this->view_data['propay_data'] = PropayData::find('all',array('conditions' => array('user_id=?',$this->sessionArr['user_id'] )));
        $this->view_data['subscriptions']=$subscription_all;
		$this->content_view = 'settings/aosettings/ao_subscriptions/all';
	}
	
	
	
	/* API */
	function apisettingview()
	{
		$this->view_data['breadcrumb'] = $this->lang->line('application_enable_api');
		$this->view_data['breadcrumb_id'] = "enableapi";

		if($_POST['submit'] == 'Enable')
		{
			$user_id = $this->sessionArr['user_id'];
			$email = $this->sessionArr['email'];
			$company_id = $this->sessionArr['company_id'];

			$sql_check_api_details = 'SELECT * FROM user_api_details WHERE user_id = "'.$user_id.'"';
            $check_api_id = $this->db->query($sql_check_api_details)->row_array();
            //$company_id = $check_company_id['company_id'];
            //print_r($check_api_id);die;

            if(empty($check_api_id))
            {
				$sql_check_company_password = 'SELECT u.id, u.email, u.hashed_password, ur.id as ur_id, ur.role_id, ur.company_id,ur.user_login_token FROM users AS u LEFT JOIN user_roles AS ur ON u.id = ur.user_id WHERE u.status = "active" AND u.email = "'.$email.'" AND ur.company_id = "'.$company_id.'"';
	            $check_user_validation = $this->db->query($sql_check_company_password)->row_array();
	            //echo '<pre>';print_r($check_user_validation);die;

	            if(!empty($check_user_validation))
	            {
	                $access_token = $this->RandomString(42);
	                $login_token = $this->RandomString(42);
	                $current_date = date("Y-m-d H:i:s");
	                $expired_date = date("Y-m-d H:i:s", strtotime('+72 hours'));
	                
	                $data = array(
					        'user_id' => $user_id,
					        'user_access_token' => $access_token,
					        'user_login_token' => $login_token,
					        'expired_access_date' => $expired_date,
					        'expired_login_date' => $expired_date,
					        'created_date' => $current_date,
					        'status' => 'enable'
					);
					$this->db->insert('user_api_details', $data);
					$id = $this->db->insert_id();
					
					$this->view_data['id'] = $id;
					$this->view_data['access_token'] = $access_token;
					$this->view_data['login_token'] = $login_token;
					$this->view_data['enabled'] = 'true';
					$this->view_data['disabled'] = 'false';
					$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_enable_api_success'));
	            }
            }
            else
            {
            	$id = $_POST['accessapiid'];
            	$sql_check_api_details = 'SELECT * FROM user_api_details WHERE user_id = "'.$user_id.'"';
	            $check_api_id = $this->db->query($sql_check_api_details)->row_array();
	            $check_expired_time = $check_api_id['expired_date'];
	            $check_status = $check_api_id['status'];
            	
            	//print_r($check_api_id);die;	
	            //$date = date('Y-m-d H:i:s');
	            	$updated_date = date("Y-m-d H:i:s");
            		$data = array(
					        'updated_date' => $updated_date,
					        'status' => 'enable'
					);
					$this->db->where('id', $id);
					$this->db->update('user_api_details', $data); 
					$this->view_data['enabled'] = 'true';
					$this->view_data['disabled'] = 'false';
					$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_enable_api_success'));
            }
			redirect('aosettings/apisettingview');
		}
		elseif($_POST['submit'] == 'Disable')
		{
			$id = $_POST['accessapiid'];
			$user_id = $this->sessionArr['user_id'];
			$email = $this->sessionArr['email'];
			$company_id = $this->sessionArr['company_id'];

			$sql_check_api_details = 'SELECT * FROM user_api_details WHERE user_id = "'.$user_id.'"';
            $check_api_id = $this->db->query($sql_check_api_details)->row_array();

            if(!empty($check_api_id))
            {
				$sql_check_company_password = 'SELECT u.id, u.email, u.hashed_password, ur.id as ur_id, ur.role_id, ur.company_id,ur.user_login_token FROM users AS u LEFT JOIN user_roles AS ur ON u.id = ur.user_id WHERE u.status = "active" AND u.email = "'.$email.'" AND ur.company_id = "'.$company_id.'"';
	            $check_user_validation = $this->db->query($sql_check_company_password)->row_array();
	            
	            if(!empty($check_user_validation))
	            {
	                //$access_token = $this->RandomString(42);
	                //$login_token = $this->RandomString(42);
	                //$expired_date = date("Y-m-d H:i:s", strtotime('+72 hours'));
	                $updated_date = date("Y-m-d H:i:s");

	                $data = array(
					        'user_id' => $user_id,
					        'updated_date' => $updated_date,
					        'status' => 'disable'
					);
					$this->db->where('id', $id);
					$this->db->update('user_api_details', $data); 

					$this->view_data['id'] = $id;
					$this->view_data['access_token'] = $access_token;
					$this->view_data['login_token'] = $login_token;
					$this->view_data['enabled'] = 'false';
					$this->view_data['disabled'] = 'true';
					$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_disable_api_success'));
					redirect('aosettings/apisettingview');
	            }
	        }
		}
		elseif($_POST['submit'] == 'Yes reset access token')
		{
			$id = $_POST['accessapiid'];
			$sql_check_api_details = 'SELECT * FROM user_api_details WHERE id = "'.$id.'"';
            $check_api_id = $this->db->query($sql_check_api_details)->row_array();

            if(!empty($check_api_id) && $check_api_id['status']!='disable')
            {	
	            $login_token = $check_api_id['user_login_token'];
				$access_token = $this->RandomString(42);
	            //$login_token = $this->RandomString(42);
	            $updated_date = date("Y-m-d H:i:s");
	            $expired_date = date("Y-m-d H:i:s", strtotime('+72 hours'));
	            
	            $data = array(
				        'user_access_token' => $access_token,
				        'expired_access_date' => $expired_date,
				        'updated_date' => $updated_date,
				        'status' => 'enable'
				);
				$this->db->where('id', $id);
				$this->db->update('user_api_details', $data); 

				$this->view_data['id'] = $id;
				$this->view_data['access_token'] = $access_token;
				$this->view_data['login_token'] = $login_token;
				$this->view_data['enabled'] = 'true';
				$this->view_data['disabled'] = 'false';
				$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_reset_api_access_success'));
			}
			else
			{
				$this->view_data['id'] = $id;
				$this->view_data['access_token'] = $check_api_id['user_access_token'];
				$this->view_data['login_token'] = $check_api_id['user_login_token'];
				$this->view_data['enabled'] = 'false';
				$this->view_data['disabled'] = 'true';
				$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_reset_api_access_error'));
			}
			redirect('aosettings/apisettingview');
		}
		elseif($_POST['submit'] == 'Yes reset login token')
		{
			$id = $_POST['accessapiid'];
			$sql_check_api_details = 'SELECT * FROM user_api_details WHERE id = "'.$id.'"';
            $check_api_id = $this->db->query($sql_check_api_details)->row_array();
            
            if(!empty($check_api_id) && $check_api_id['status']!='disable')
            {	
	            $access_token = $check_api_id['user_access_token'];
            	//$access_token = $this->RandomString(42);
	            $login_token = $this->RandomString(42);
	            $updated_date = date("Y-m-d H:i:s");
	            $expired_date = date("Y-m-d H:i:s", strtotime('+72 hours'));
	            
	            $data = array(
				        'user_login_token' => $login_token,
				        'expired_login_date' => $expired_date,
				        'updated_date' => $updated_date,
				        'status' => 'enable'
				);
				$this->db->where('id', $id);
				$this->db->update('user_api_details', $data); 

				$this->view_data['id'] = $id;
				$this->view_data['access_token'] = $access_token;
				$this->view_data['login_token'] = $login_token;
				$this->view_data['enabled'] = 'true';
				$this->view_data['disabled'] = 'false';
				$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_reset_api_login_success'));
			}
			else
			{
				$this->view_data['id'] = $id;
				$this->view_data['access_token'] = $check_api_id['user_access_token'];
				$this->view_data['login_token'] = $check_api_id['user_login_token'];
				$this->view_data['enabled'] = 'false';
				$this->view_data['disabled'] = 'true';
				$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_reset_api_login_error'));
			}
			redirect('aosettings/apisettingview');
		}
		elseif($_POST['submit'] == 'Yes reset both')
		{
			$id = $_POST['accessapiid'];
			$sql_check_api_details = 'SELECT * FROM user_api_details WHERE id = "'.$id.'"';
            $check_api_id = $this->db->query($sql_check_api_details)->row_array();
            
            if(!empty($check_api_id) && $check_api_id['status']!='disable')
            {	
				$access_token = $this->RandomString(42);
	            $login_token = $this->RandomString(42);
	            $updated_date = date("Y-m-d H:i:s");
	            $expired_date = date("Y-m-d H:i:s", strtotime('+72 hours'));
	            
	            $data = array(
				        'user_access_token' => $access_token,
				        'user_login_token' => $login_token,
				        'expired_access_date' => $expired_date,
				        'expired_login_date' => $expired_date,
				        'updated_date' => $updated_date,
				        'status' => 'enable'
				);
				$this->db->where('id', $id);
				$this->db->update('user_api_details', $data); 

				$this->view_data['id'] = $id;
				$this->view_data['access_token'] = $access_token;
				$this->view_data['login_token'] = $login_token;
				$this->view_data['enabled'] = 'true';
				$this->view_data['disabled'] = 'false';
				$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_reset_api_success'));
			}
			else
			{
				$this->view_data['id'] = $id;
				$this->view_data['access_token'] = $check_api_id['user_access_token'];
				$this->view_data['login_token'] = $check_api_id['user_login_token'];
				$this->view_data['enabled'] = 'false';
				$this->view_data['disabled'] = 'true';
				$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_reset_api_error'));
			}
			redirect('aosettings/apisettingview');
		}
		else
		{
			$user_id = $this->sessionArr['user_id'];
			$sql_check_api_details = 'SELECT * FROM user_api_details WHERE user_id = "'.$user_id.'"';
            $check_api_id = $this->db->query($sql_check_api_details)->row_array();
            $check_expired_access_time = $check_api_id['expired_access_date'];
            $check_expired_login_time = $check_api_id['expired_login_date'];
            $check_status = $check_api_id['status'];

            if(!empty($check_api_id))
            {
            	if($check_status == 'enable')
            	{
            		if(date('Y-m-d H:i:s') <= date('Y-m-d H:i:s',strtotime($check_expired_access_time)) )
            		{
            			if(date('Y-m-d H:i:s') <= date('Y-m-d H:i:s',strtotime($check_expired_login_time)) )
            			{
	            			//echo 'rea if';die;
			            	$this->view_data['id'] = $check_api_id['id'];
							$this->view_data['access_token'] = $check_api_id['user_access_token'];
							$this->view_data['login_token'] = $check_api_id['user_login_token'];
							$this->view_data['enabled'] = 'true';
							$this->view_data['disabled'] = 'false';
						}
						else
						{
							$user_id = $this->sessionArr['user_id'];
							$updated_date = date("Y-m-d H:i:s");
		            		$data = array(
							        'expired_login_date' => '',
							        'updated_date' => $updated_date,
							        'status' => 'disable'
							);
							$this->db->where('user_id', $user_id);
							$this->db->update('user_api_details', $data); 

	            			$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_login_token_expired'));
	            			redirect('aosettings/apisettingview');
						}
            		}
            		else
            		{
            			//echo 'rea';die;
            			$user_id = $this->sessionArr['user_id'];
						$updated_date = date("Y-m-d H:i:s");
	            		$data = array(
						        'expired_access_date' => '',
						        'updated_date' => $updated_date,
						        'status' => 'disable'
						);
						$this->db->where('user_id', $user_id);
						$this->db->update('user_api_details', $data); 

            			$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_access_token_expired'));
            			redirect('aosettings/apisettingview');
            		}
            	}
            	else
            	{
            		$this->view_data['id'] = $check_api_id['id'];
            		$this->view_data['access_token'] = $check_api_id['user_access_token'];
					$this->view_data['login_token'] = $check_api_id['user_login_token'];
            		$this->view_data['enabled'] = 'false';
            		$this->view_data['disabled'] = 'true';
            		/*$this->view_data['access_token'] = $check_api_id['user_access_token'];
					$this->view_data['login_token'] = $check_api_id['user_login_token'];*/
            	}
            }
            else
            {
            	$this->view_data['id'] = '';
				$this->view_data['access_token'] = '';
				$this->view_data['login_token'] = '';
				$this->view_data['enabled'] = 'false';
				$this->view_data['disabled'] = 'true';
            }
		}

		$this->view_data['form_action'] = base_url().'aosettings/apisettingview';
		$this->content_view = 'settings/aosettings/enableapi'; 		
	}

	function RandomString($length)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randstring = '';
        for ($i = 0; $i < $length; $i++) {
            $randstring .= $characters[rand(0, strlen($characters))];
        }
        return $randstring;
    }
}
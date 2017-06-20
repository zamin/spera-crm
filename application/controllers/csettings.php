<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Csettings extends MY_Controller
{
	private $check_ao_api_id;
    private $ao_access_token;
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
        
        //print_r($this->sessionArr);die;
        $user_company_id = $this->sessionArr['company_id'];
        $user_role_id = $this->sessionArr['role_id'];
        //echo date('Y-m-d');die;

        $subscriptions_new = Subscription::find_by_sql('SELECT u.*,pus.start_date,pus.end_date, pd.name, uad.user_access_token as ao_access_token
                                                    FROM user_roles u
                                                    INNER JOIN user_api_details as uad on uad.user_id = u.user_id 
                                                    INNER JOIN propay_user_subscription as pus on pus.user_id = u.user_id 
                                                    INNER JOIN package as pd on pd.id = pus.package_id
                                                    WHERE u.company_id = '.$user_company_id.' and pus.package_id = 3 and pus.start_date <= "'.date('Y-m-d').'" and pus.end_date >= "'.date('Y-m-d').'"
                                                    order by pus.id desc');
        $user_package_id = $subscriptions_new[0]->name;
        $account_owner_user_id = $subscriptions_new[0]->user_id;
        $this->ao_access_token = $subscriptions_new[0]->ao_access_token;
        //echo '<pre>';print_r($subscriptions_new);die;
        //and pus.start_date >= `'.date('Y-m-d').'` and pus.end_date <= `'.date('Y-m-d').'`
        $sql_check_api_details = 'SELECT * FROM user_api_details WHERE user_id = "'.$account_owner_user_id.'" and status="enable"';
        $this->check_ao_api_id = $this->db->query($sql_check_api_details)->row_array();    
        
        if($user_package_id == 'Business')
        {	
        	$this->view_data['submenu'] = array(
                $this->lang->line('application_enable_api') => base_url().'csettings/apisettingview',
                );  
        }
        else
        {
            //redirect('cdashboard');
            $this->view_data['submenu'] = array(
                $this->lang->line('application_enable_api') => base_url().'csettings/apisettingview',
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
		$this->apisettingview();

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
	                //$access_token = $this->RandomString(42);
	                $login_token = $this->RandomString(42);
	                $current_date = date("Y-m-d H:i:s");
	                $expired_date = date("Y-m-d H:i:s", strtotime('+72 hours'));
	                $expired_access_date = $this->check_ao_api_id['expired_access_date'];

        					//'user_access_token' => $this->ao_access_token,
                    $data = array(
                            'user_id' => $user_id,
                            'user_login_token' => $login_token,
					        'expired_access_date' => $expired_access_date,
					        'expired_login_date' => $expired_date,
					        'created_date' => $current_date,
					        'status' => 'enable'
					);
					$this->db->insert('user_api_details', $data);
					$id = $this->db->insert_id();
					
					$this->view_data['id'] = $id;
					$this->view_data['access_token'] = $this->ao_access_token;
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
                $expired_access_date = $this->check_ao_api_id['expired_access_date'];
            	/*if($current_date_time <= $expired_date_time)
            	{*/
            		//print_r($check_api_id);die;
            		//$access_token = $this->RandomString(42);
	                //$login_token = $this->RandomString(42);
	                //$expired_date = date("Y-m-d H:i:s", strtotime('+72 hours'));
                    $updated_date = date("Y-m-d H:i:s");
	                
                    $data = array(
        					'expired_access_date' => $expired_access_date,
					        'updated_date' => $updated_date,
					        'status' => 'enable'
					);
					$this->db->where('id', $id);
					$this->db->update('user_api_details', $data); 
					$this->view_data['enabled'] = 'true';
					$this->view_data['disabled'] = 'false';
					$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_enable_api_success'));
            	//}
            }
			redirect('csettings/apisettingview');
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
	                $updated_date = date("Y-m-d H:i:s");
                    $expired_access_date = $this->check_ao_api_id['expired_access_date'];
	                //$expired_date = date("Y-m-d H:i:s", strtotime('+72 hours'));

	                $data = array(
					        'user_id' => $user_id,
                            'expired_access_date' => $expired_access_date,
					        'updated_date' => $updated_date,
					        'status' => 'disable'
					);
					$this->db->where('id', $id);
					$this->db->update('user_api_details', $data); 

					$this->view_data['id'] = $id;
					$this->view_data['access_token'] = $this->ao_access_token;
					$this->view_data['login_token'] = $login_token;
					$this->view_data['enabled'] = 'false';
					$this->view_data['disabled'] = 'true';
					$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_disable_api_success'));
					redirect('csettings/apisettingview');
	            }
	        }
		}
		elseif($_POST['submit'] == 'Yes reset login token')
        {
            $id = $_POST['accessapiid'];
            $sql_check_api_details = 'SELECT * FROM user_api_details WHERE id = "'.$id.'"';
            $check_api_id = $this->db->query($sql_check_api_details)->row_array();
            
            if(!empty($check_api_id) && $check_api_id['status']!='disable')
            {   
                //$access_token = $this->RandomString(42);
                $login_token = $this->RandomString(42);
                $updated_date = date("Y-m-d H:i:s");
                $expired_date = date("Y-m-d H:i:s", strtotime('+72 hours'));
                $expired_access_date = $this->check_ao_api_id['expired_access_date'];

                        //'user_access_token' => $this->ao_access_token,
                $data = array(
                        'user_login_token' => $login_token,
                        'expired_access_date' => $expired_access_date,
                        'expired_login_date' => $expired_date,
                        'updated_date' => $updated_date,
                        'status' => 'enable'
                );
                $this->db->where('id', $id);
                $this->db->update('user_api_details', $data); 

                $this->view_data['id'] = $id;
                $this->view_data['access_token'] = $this->ao_access_token;
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
			redirect('csettings/apisettingview');
		}
		else
		{
            if(empty($this->check_ao_api_id))
            {
                $this->view_data['id'] = '';
                $this->view_data['access_token'] = '';
                $this->view_data['login_token'] = '';
                $this->view_data['enabled'] = 'true';
                $this->view_data['disabled'] = 'true';
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
                		if(date('Y-m-d H:i:s') <= date('Y-m-d H:i:s',strtotime($check_expired_access_time)))
                		{
                            if(date('Y-m-d H:i:s') <= date('Y-m-d H:i:s',strtotime($check_expired_login_time)))
                            {
        		            	$this->view_data['id'] = $check_api_id['id'];
        						$this->view_data['access_token'] = $this->ao_access_token;
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
                                redirect('csettings/apisettingview');
                            }
                		}
                		else
                		{
                			$user_id = $this->sessionArr['user_id'];
    						$updated_date = date("Y-m-d H:i:s");
    	            		$data = array(
    						        'expired_date' => '',
    						        'updated_date' => $updated_date,
    						        'status' => 'disable'
    						);
    						$this->db->where('user_id', $user_id);
    						$this->db->update('user_api_details', $data); 

                			$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_access_token_expired'));
                			redirect('csettings/apisettingview');
                		}
                	}
                	else
                	{
                		$this->view_data['id'] = $check_api_id['id'];
                		$this->view_data['enabled'] = 'false';
                		$this->view_data['disabled'] = 'true';
                		$this->view_data['access_token'] = $this->check_ao_api_id['user_access_token'];
    					$this->view_data['login_token'] = $check_api_id['user_login_token'];
                	}
                }
                else
                {
                	$this->view_data['id'] = '';
    				$this->view_data['access_token'] = $this->ao_access_token;
    				$this->view_data['login_token'] = '';
    				$this->view_data['enabled'] = 'false';
    				$this->view_data['disabled'] = 'true';
                }
            }
		}

		$this->view_data['form_action'] = base_url().'csettings/apisettingview';
		$this->content_view = 'settings/csettings/enableapi'; 		
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
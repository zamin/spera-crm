<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ClassName: Auth
 * Function Name: login
 * This class is used to validate user login details, multiple user login, logout and languages
 **/
class Auth extends MY_Controller
{
	/* contructor function */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	
	/** 
	 * This function is used to validate login details entered by user
	 */
	function login($cid = FALSE, $cid2 = FALSE, $cid3 = FALSE, $cid4 = FALSE, $cid5 = FALSE)
	{
		
        $cid = $cid  ? $cid : '0';
        $action = $cid ? $cid : 'login';

		$check_invoice_url = $this->session->userdata['invoice_url'];
        if($cid > 0)
        {

            $company_name=Company::find($cid);
            $cname = $company_name->name;

			if(!empty($cid2)) {
				$set_invoice_url = base_url().$cid.'/'.$cid2.'/'.$cid3.'/'.$cid4;
				$this->session->set_userdata('invoice_url',$set_invoice_url);
			}
			//echo 'here -> '.$set_invoice_url;exit;

            if($this->sessionArr)
            {
                $set_page = $this->authenticateuser_by_id( $this->sessionArr['user_id'], $cid);
				if(!empty($this->session->userdata['invoice_url'])) {
					//print_r($this->session->userdata['invoice_url']);exit;
					redirect($this->session->userdata['invoice_url']);
				} else {
					redirect(base_url().$set_page."/");
				}
            }

			$this->content_view = 'auth/clogin';
		} else {

            if($this->sessionArr['user_id'])
            {
                redirect(base_url()."dashboard/");
                exit;
            }
            //$array_items = array('company_name' => '');
            //$this->session->unset_userdata($array_items);
        }
        
        $this->view_data['company_id']=$cid;
        $this->view_data['form_action']=$action;
        $this->view_data['error'] = "false";		
		$this->theme_view = 'login';
		$userEmail = '';
        
        if($_POST)
		{
            if( isset($cid) && $cid > 0 )
            {
                $_POST['email'] = $this->security->xss_clean($_POST['email']);
                $user = User::validate_login($_POST['email'], $_POST['password'], $cid);
                
                if($user){
                    $userEmail = $_POST['email'];
                    
                    if($this->input->cookie('fc2_link') != ""){
                        redirect($this->input->cookie('fc2_link'));
                    }else{

                        $sql_admin = 'SELECT * from users WHERE status= "active" AND admin = "1" AND email = "'.$userEmail.'"';
                        $queryUser = User::find_by_sql($sql_admin);

                        if(!empty($queryUser) && is_array($queryUser)) {
                            $this->view_data['error'] = "true";
                            $this->view_data['email'] = $this->security->xss_clean($_POST['email']);
                            $this->view_data['message'] = 'error:'.$this->lang->line('messages_login_incorrect');
                        } else {

							$set_page = $this->authenticateuser( $userEmail, $cid);

							$get_invoice_url = $this->session->userdata['invoice_url'];
							if(isset($get_invoice_url) && !empty($get_invoice_url)) {
								redirect($get_invoice_url);
							} else {
								redirect(base_url().$set_page."/");								
							}

                        }
                    }
                }
                else {
                    $this->view_data['error'] = "true";
                    $this->view_data['email'] = $this->security->xss_clean($_POST['email']);
                    $this->view_data['message'] = 'error:'.$this->lang->line('messages_login_incorrect');
                }
            }
            else
            {
                $_POST['email'] = $this->security->xss_clean($_POST['email']);
			    $user = User::validate_login($_POST['email'], $_POST['password'], $cid='admin');
			    if($user){
				    $userEmail = $_POST['email'];
				    //$this->session->set_userdata('user_email',$_POST['email']);
				    if($this->input->cookie('fc2_link') != ""){
					    redirect($this->input->cookie('fc2_link'));
				    }else{

					    $sql_admin = 'SELECT * from users WHERE status= "active" AND admin = "1" AND email = "'.$_POST['email'].'"';
					    $queryUser = User::find_by_sql($sql_admin);

					    if(!empty($queryUser) && is_array($queryUser)) {
						    redirect('');
					    } else {
						    redirect('auth/multipleuserlogin');
					    }
				    }
			    }
			    else {
				    $this->view_data['error'] = "true";
				    $this->view_data['email'] = $this->security->xss_clean($_POST['email']);
				    $this->view_data['message'] = 'error:'.$this->lang->line('messages_login_incorrect');
					$this->theme_view = 'login';
			    }
            }
		}
	}

	function email_validate() {
		
		$email = $_POST['emailid'];

		$sql_admin_user = 'SELECT u.id, u.email FROM users AS u WHERE u.status = "active" AND u.admin = "1" AND u.email = "'.$email.'"';
		
		$get_user_list = $this->db->query($sql_admin_user)->row_array();
		$user_value = "";
		
		if(!empty($get_user_list) && is_array($get_user_list)) {
			$response = 'success';
			$user_value .= "<div class='form-group'>";
			$user_value .= "<label for='password'>".$this->lang->line('application_password')." *</label>";
			//$user_value .= "</br>";	
			$user_value .= "<input id='password' type='password' name='password' class='form-control' value='' required />";
			$user_value .= "</div>";
			$user_value .= "<input type='submit' class='btn btn-primary' id='submitcompany' value='".$this->lang->line('application_login')."' />";
			$user_value .= "<div id='nameError'>";
			$user_value	.= "</div>";
			
		} else {

			$user_value = "";
			$sql_user = 'SELECT u.id, u.email, c.id, c.name, ur.role_id, ur.company_id, r.role_id, r.roles FROM users AS u
									LEFT JOIN user_roles AS ur ON u.id = ur.user_id
									LEFT JOIN roles AS r ON ur.role_id = r.role_id
									LEFT JOIN companies AS c ON ur.company_id = c.id
									WHERE u.status = "active" AND u.email = "'.$email.'"';

			$get_user_list = $this->db->query($sql_user)->result_array();


			if(!empty($get_user_list)) {
				$response = 'success';
				$user_value .= "<div class='form-group'>";
				$user_value .= "<label for='Select Company'>".$this->lang->line('application_please_select_company')." *</label>";
				$user_value .= "</br>";

				$options = array();
                $options['0'] = '-';

				foreach ($get_user_list as $value):  
					$options[$value['id']] = $value['name'];
                endforeach;

				$c = "";

				$user_value .= form_dropdown('companytype', $options, $c, 'style="width:100%" name="companytype" id="companytype" class="chosen-select"');
				$user_value .= "</div>";
				if(!isset($_POST['forgot_pass'])) {
					$user_value .= "<div class='form-group'>";
					$user_value .= "<label for='password'>".$this->lang->line('application_password')." *</label>";
					$user_value .= "<input id='password' type='password' name='password' class='form-control' value='' required />";
					$user_value .= "</div>";
					$user_value .= "<input type='submit' class='btn btn-primary' id='submitcompany' value='".$this->lang->line('application_login')."' />";
					$user_value .= "<div id='nameError'>";
					$user_value	.= "</div>";
				}
			} else {
				$response = 'error';
				$user_value = "<div id='error'>";
				$user_value .= $this->lang->line('messages_email_not_exist');
				$user_value	.= "</div>";
			}
		}

		echo json_encode(array('validate' => $response, 'html_response' => $user_value));
		exit;

	}

   /**
	*Validate User data for mail login
	*/
	function user_validate() {
       
		$email = $_POST['emailid'];
		$cid = $company_name = $_POST['companytype'];
		$password = $_POST['password'];
        
		$sql_check_company_password = 'SELECT u.id, u.email,u.hashed_password, c.name, ur.role_id, ur.company_id FROM 	users AS u LEFT JOIN user_roles AS ur ON u.id = ur.user_id LEFT JOIN companies AS c ON ur.company_id = c.id WHERE u.status = "active" AND u.email = "'.$email.'" AND c.id = "'.$company_name.'"';

		$check_user_validation = $this->db->query($sql_check_company_password)->row_array();
		$validate = '';
		$html_response = '';

		if(!empty($check_user_validation)){
			
			$user_validate = User::validate_login($email, $password, $cid);
			if($user_validate) {
				$validate = 'success';

				if($this->input->cookie('fc2_link') != ""){
					$html_response = $this->input->cookie('fc2_link');
				}else{
					
					$set_page = $this->authenticateuser($email, $cid);

					$html_response = base_url().$cid.'/'.$set_page."/";
				}

			} else {
				$validate = 'error';
				$html_response = $this->lang->line('messages_password_incorrect');
			}
			
		} else {

			$sql_admin = 'SELECT * from users WHERE status= "active" AND admin = "1" AND email = "'.$email.'"';
			$queryUser = User::find_by_sql($sql_admin);
			
			if($queryUser) {
				$user_validate = User::validate_login($email, $password, $cid='admin');
				if($user_validate) { 
					$validate = 'success';
					$html_response = base_url().'dashboard';
				} else {
					$validate = 'error';
					$html_response = $this->lang->line('messages_password_incorrect');
				}
			} else {
				$validate = 'error';
				$html_response = $this->lang->line('messages_email_not_exist');
			}
			
			
					
		}
		echo json_encode(array('validate' => $validate, 'html_response' => $html_response));
		exit;
	}

	/**
	 * Validates the user as per the roles and redirects to login page
	 */
	function authenticateuser($email, $cid) {

		if(!empty($email) && !empty($cid) ) {

			$sql_user = 'SELECT u.id, u.status, u.admin, u.email,u.hashed_password, c.name, ur.role_id as roleid, ur.company_id FROM users AS u LEFT JOIN user_roles AS ur ON u.id = ur.user_id LEFT JOIN companies AS c ON ur.company_id = c.id WHERE u.status = "active" AND u.email = "'.$email.'" AND c.id = "'.$cid.'"';

			$check_user_validation = $this->db->query($sql_user)->row_array();

			if(!empty($check_user_validation)) {

				$sql_user_role = 'SELECT r.roles FROM user_roles AS ur LEFT JOIN roles AS r ON ur.role_id = r.role_id WHERE ur.user_id = "'.$check_user_validation['id'].'" AND ur.company_id = "'.$cid.'"';

				$get_role = $this->db->query($sql_user_role)->row_array();

				if($get_role['roles'] == 'Freelancer'):
					$set_page = 'aodashboard'; 
				elseif($get_role['roles'] == 'Client'):
					$set_page = 'cdashboard'; 
				elseif($get_role['roles'] == 'Sub-Contractor'):
					$set_page = 'scdashboard'; 
				endif;

				//echo "<pre>";print_r($this->sessionArr);exit;
				return $set_page;
			} else {
				return FALSE;
			}

		}
	}
    
    function authenticateuser_by_id($uid, $cid) {

        if(!empty($uid) && !empty($cid) ) {

            $sql_user = 'SELECT u.id, u.status, u.admin, u.email,u.hashed_password, c.name, ur.role_id as roleid, ur.company_id FROM users AS u LEFT JOIN user_roles AS ur ON u.id = ur.user_id LEFT JOIN companies AS c ON ur.company_id = c.id WHERE u.status = "active" AND u.id = "'.$uid.'" AND c.id = "'.$cid.'"';

            $check_user_validation = $this->db->query($sql_user)->row_array();

            if(!empty($check_user_validation)) {

                $sql_user_role = 'SELECT r.roles FROM user_roles AS ur LEFT JOIN roles AS r ON ur.role_id = r.role_id WHERE ur.user_id = "'.$check_user_validation['id'].'" AND ur.company_id = "'.$cid.'"';

                $get_role = $this->db->query($sql_user_role)->row_array();

                if($get_role['roles'] == 'Freelancer'):
                    $set_page = 'aodashboard'; 
                elseif($get_role['roles'] == 'Client'):
                    $set_page = 'cdashboard'; 
                elseif($get_role['roles'] == 'Sub-Contractor'):
                    $set_page = 'scdashboard'; 
                endif;


                return $set_page;
            } else {
                return FALSE;
            }

        }
    }

	/** 
	 * This function is used when after user is validated user will be redirected to screen that shows multiple login
	 */
	function multipleuserlogin() {

                if(!$this->user)
                {
                    redirect('login');
                }
		$user_email = $this->session->userdata('user_email');

		$sql_user = 'SELECT u.id, u.userpic, c.name, ur.role_id, ur.company_id, r.role_id, r.roles FROM users AS u
		LEFT JOIN user_roles AS ur ON u.id = ur.user_id
		LEFT JOIN roles AS r ON ur.role_id = r.role_id
		LEFT JOIN companies AS c ON ur.company_id = c.id
		WHERE u.status = "active" AND u.email = "'.$user_email.'"';

		$queryUser = User::find_by_sql($sql_user);

		$this->view_data['title'] = $this->lang->line('LOGIN FORM');
		$this->view_data['resultset'] = array_filter($queryUser);
		$this->view_data['form_action'] = 'auth/multipleuserlogin';

		$resultset = $this->view_data['resultset'];
	}
	
	/** 
	 * This function is called when user opts to logout
	 */
	function logout($cid = false)
	{
        $cid = $this->sessionArr['company_id'] ? $this->sessionArr['company_id'] : 0;
        if($this->user){ 
		    $update = User::find($this->user->id); 
			$update->last_active = 0;
			$update->save();
		}elseif($this->client){
		    $update = Client::find($this->client->id);
			$update->last_active = 0;
			$update->save();
		}
		User::logout($cid);
        if($cid){redirect('login');}
        else{redirect('login');}

	}
	
	/** 
	 * Language function
	 */
	function language($lang = false){
		$folder = 'application/language/';
		$languagefiles = scandir($folder);
		if(in_array($lang, $languagefiles)){
		$cookie = array(
                   'name'   => 'fc2language',
                   'value'  => $lang,
                   'expire' => '31536000',
               );
 
		$this->input->set_cookie($cookie);
		}
		redirect(''); 
	}
	
}

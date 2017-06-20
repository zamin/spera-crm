<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ClassName: Register
 * Function Name: index 
 * This class is used for registering Account Owner
 **/
class Register extends MY_Controller
{
	/* contructor function */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->settings = Setting::first();
	}

	/* Index function will first check if the registration data is submitted or not if submitted 
	   then it will enter if condition 
	 * Other wise it will enter else condition 
	 */
	function index()
	{	
			$core_settings = Setting::first();
			if($core_settings->registration != 1){ redirect('login');}
		
		/* It will enter this condition if data is fetched  */
		if($_POST)
		{
			
			$this->load->library('parser');
			$this->load->helper('file');
			$this->load->helper('notification');

			$sql_user_details = 'SELECT u.id, u.firstname, u.lastname, u.email, c.name, c.user_id 
									FROM users AS u JOIN companies AS c ON u.id = c.user_id
									WHERE u.email = "'.$_POST['email'].'" AND c.name="'.$_POST['company_name'].'" ';
            $check_user_registration = $this->db->query($sql_user_details)->row_array();
            $promo_code_exists = false;
            $has_promo_code = false;
            $promo_code = null;
	
			/* Company Name has to be unique */
			$check_company = Company::find_by_name(trim(htmlspecialchars($_POST['company_name'])));

            if(!empty($_POST['promo_code'])) {
                $promo_code = PromoCodes::find_by_sql("SELECT * FROM `promo_codes` WHERE promo_code = '" . $_POST['promo_code'] . "'");

                $has_promo_code = true;
                $promo_code_exists = !empty($promo_code);

                if ($promo_code_exists)
                    $promo_code = $promo_code[0];
            }
			
			/* It will enter this condition if email of that user is not in the same company */
			if( empty($check_user_registration) && (($has_promo_code && $promo_code_exists) || !$has_promo_code) && !$check_company && trim(htmlspecialchars($_POST['company_name'])) != "" && trim(htmlspecialchars($_POST['email'])) != "" && $_POST['password'] != "" && $_POST['firstname'] != "" && $_POST['lastname'] != "" && $_POST['confirmcaptcha'] != ""){

				/* Company Details */
				$user_attr = array();
				$company_attr['name'] = trim(htmlspecialchars($_POST['company_name']));
				$company_attr['reference'] = $core_settings->company_reference;
				
				$core_settings->company_reference = $core_settings->company_reference+1;
				$core_settings->save();
				
				$company = Company::create($company_attr);
				
				$company_id=$company->id;
				$company_slug = $this->slug($company_attr['name']);
				
				$company_slug_arr=array(
					'company_id'=>$company_id,
					'slug'=>$company_slug
				);
				
				$this->db->insert('company_details',$company_slug_arr);
				

				if(!$company){
					$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_registration_error'));
					redirect('register');
				}

				/* User Details */
				$lastuser = User::last();
				$user_attr = array();
				$user_attr['email'] = trim(htmlspecialchars($_POST['email']));
				$user_attr['firstname'] = trim(htmlspecialchars($_POST['firstname']));
				$user_attr['lastname'] = trim(htmlspecialchars($_POST['lastname']));
				$user_attr['status'] = 'active';
				$user_attr['admin'] = '0';

				$user_attr['access'] = $core_settings->default_account_owner_modules;

				/* If user created then it will enter this condition */
				$user = User::create($user_attr);
				if($user){
                    
                    $user->password = $user->set_password($_POST['password']);
					$user->save();
                    
					if(empty($_POST['s_package']))
                    {
                        $package_explode=explode('-',$_POST['package']);
                    }
                    else
                    {
                        $package_explode=explode('-',$_POST['s_package']);
                    }
                    $package_id = substr($package_explode[1], -1);

					if(empty($package_id) && !empty($_POST['package']))
                        $package_id = preg_replace("([a-zA-Z-]+)", "", $_POST['package']);

                    $package_type = $package_explode[0];

                    $package_dataVal = $this->db->query('SELECT * FROM package WHERE id = '.$package_id)->result_array();
                    $trial_version = $package_dataVal[0]['trial_version'];        
                    $discount = $package_dataVal[0]['discount'];        
                    $duration = $package_dataVal[0]['duration'];
					$trial_duration = $package_dataVal[0]['trial_duration'];   					
                    $amount = $package_dataVal[0]['amount'];        
                    $amount = ($amount*100);

                    $start_date = date('Y-m-d', time());  
                    $end_date = strtotime($start_date." +".$trial_duration." days");
                    $end_date = date('Y-m-d', $end_date);
                    
                    $propay_user_data = array('payment_detail_id' => 0, 
                                                'package_id' => $package_id,
                                                'user_id' => $user->id,
                                                'start_date' => $start_date,
                                                'end_date' => $end_date,
												'created_at' => date('Y-m-d H:i:s'),
												'status' => 0,
												'type'=>$package_type
												);
                    if($has_promo_code)
                        $propay_user_data["promo_code_id"] = $promo_code->id;

                    PropayUserSubscription::create($propay_user_data);
                    
					$company->user_id = $user->id;
					$company->save();
					
					//$package_id = $_POST['package_id'];

					/* User Package Details */
					
					/*
					$user_package_attr = array();
					$user_package_attr['user_id'] = $user->id;
					if(!empty($package_id)) {
						$user_package_attr['package_id'] = trim(htmlspecialchars($package_id));						
					} else {
						$user_package_attr['package_id'] = 1;
					}

					$user_package_attr['package_start_date'] = date('Y-m-d H:i:s');
					$user_package_attr['created_at'] = date('Y-m-d H:i:s');

					//$userPackageDetail = UserPackageDetail::create($user_package_attr);
					*/
					
					/* Enter User Role Details */
					$user_role_attr = array();
					$user_role_attr['user_id'] = $user->id;
					$user_role_attr['role_id'] = 2;
					if($package_id == 3)
					{
						$user_role_attr['user_access_token'] = $this->RandomString(42);
					}
					$user_role_attr['company_id'] = $company->id;
					//$user_role_attr['created_at'] = date('Y-m-d', time());

					$user_role_attr = UserRole::create($user_role_attr);
					
					$this->email->from($this->settings->from_email_id,$this->settings->from_email_name);
					//$this->email->from($core_settings->email, $core_settings->company);
					$this->email->to($user_attr['email']); 

					$this->email->subject($this->lang->line('application_your_account_has_been_created'));
					$parse_data = array(
	            					'ticket_link' => site_url().'login/',
	            					'link' => site_url().'login/',
	            					'company' => $core_settings->company,
	            					'company_name'=> $company_attr['name'],
	            					'company_reference' => $company->reference,
	            					'logo' => '<img src="'.site_url().''.$core_settings->logo.'" alt="'.$core_settings->company.'"/>',
	            					'invoice_logo' => '<img src="'.site_url().''.$core_settings->invoice_logo.'" alt="'.$core_settings->company.'"/>'
	            					);
		  			$email = read_file('./application/views/'.$core_settings->template.'/templates/email_create_account.html');
		  			$message = $this->parser->parse_string($email, $parse_data);
					$this->email->message($message);
					$this->email->send();
					send_notification($core_settings->email, $this->lang->line('application_new_freelance_user_has_registered'), $this->lang->line('application_new_freelance_user_has_registered').': <br><strong>'.$company_attr['name'].'</strong><br>'.$user_attr['firstname'].' '.$user_attr['lastname'].'<br>'.$user_attr['email']);


            		$tickets = Ticket::find("all", array('conditions' => array( '`from` LIKE CONCAT("%", ? ,"%")', $user_attr['email'] ))); 
            		if($tickets){
						foreach ($tickets as $ticket) {
							$ticket->user_id = $user->id;
							$ticket->company_id = $user_attr['company_id'];
							$ticket->save();
						}
					}
					$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_registration_success'));
					redirect('login');
				}else{
					$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_registration_error'));
					redirect('login');
				}

			} /* It will enter this condition if email or company exists i.e it will fire an error, in that case it will restore data on registration page */
			  else{

                if($has_promo_code && !$promo_code_exists){$this->view_data['error'] = $this->lang->line('messages_promo_code_not_exist');}

				if($user){$this->view_data['error'] = $this->lang->line('messages_email_already_taken');}
				
				if($check_company){$this->view_data['error'] = $this->lang->line('application_enter_unique_company_name');}
				
				if(!empty($check_user_registration)) {$this->view_data['error'] = $this->lang->line('application_enter_unique_email');}
				
				$this->theme_view = 'login';
				$this->content_view = 'auth/register';
				$this->view_data['form_action'] = 'register';

				$_POST['name'] = trim(htmlspecialchars($_POST['name']));

				$options=array('status'=>1,'is_deleted'=>0);
				$this->view_data['packages']= Package::find('all',$options);

				// foreach ($this->view_data['packages'] as $key => $val) 
				// {
					// if($val->id == $_POST['package']) {
						// echo "<option value='" . $val->id . " selected'>" . $val->name . "</option>";
					// } else {
						// echo "<option value='" . $val->id . "'>" . $val->name . "</option>";
					// }
				// }

				$_POST['package'] = trim(htmlspecialchars($_POST['package']));
				$_POST['email'] = trim(htmlspecialchars($_POST['email']));
				$_POST['company_name'] = trim(htmlspecialchars($_POST['company_name']));
				$_POST['firstname'] = trim(htmlspecialchars($_POST['firstname']));
				$_POST['lastname'] = trim(htmlspecialchars($_POST['lastname']));
				$this->view_data['registerdata'] = array_map('htmlspecialchars', $_POST);

			}

		}
		/* Display Registration Form */
		else{
			
			if($this->uri->segment(2) && $this->uri->segment(3))
			{
				$name_of_package=$this->uri->segment(2).'-'.$this->uri->segment(3);
			}
			if(isset($name_of_package)&& !empty($name_of_package))
			{
				$this->view_data['package_name']=$name_of_package;
				$this->view_data['form_action'] = 'register/'.$this->uri->segment(2).'/'.$this->uri->segment(3);
			}
			else
			{
				$this->view_data['form_action'] = 'register';
			}
					
			$options=array('status'=>1,'is_deleted'=>0);
            $this->view_data['packages']= Package::find('all',$options);

			$this->view_data['error'] = 'false';
			$this->theme_view = 'login';
			$this->content_view = 'auth/register';
			//$this->view_data['form_action'] = 'register';
		}
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
}

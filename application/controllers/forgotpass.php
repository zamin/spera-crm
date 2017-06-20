<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Forgotpass extends MY_Controller
{
	function __construct()
	{
		parent::__construct();
	}
	function index()
	{	
			$this->load->database();
			$this->view_data['error'] = 'false';
			$this->theme_view = 'login';
			$this->content_view = 'auth/forgotpass';
			$sql = "DELETE FROM pw_reset WHERE `timestamp`+ (24 * 60 * 60) < timestamp";
			$query = $this->db->query($sql);
		
		if($_POST)
		{
			unset($_POST['forgot_pass']);
			$user_exist = User::validate_company_user($_POST['emailid'], $_POST['companytype']);
		
			// $usertrue = "1";
			// if(!$user){$user = Client::find_by_email(trim(htmlspecialchars($_POST['email']))); $usertrue = "0";}
			// if(($user && $usertrue == "1" && $user->status == "active") || ($user && $usertrue == "0" &&  $user->inactive == "0")){
			if( $user_exist ){
			    $user = User::find($user_exist->id);
				
				$data["core_settings"] = Setting::first();
				$company_detail = CompanyDetails::find('all', array( 'conditions' => array('company_id=?',$_POST['companytype']) ));
				$company = Company::find('all', array( 'conditions' => array('id=?',$_POST['companytype']) ));
				
				$logo = $data["core_settings"]->logo; 
				if(!empty($company_detail) && !empty($company_detail[0]->logo)) {
					$logo = $company_detail[0]->logo; 
				}
				
				$token = md5(uniqid(rand(), true));
				$today = date('Y-m-d H:i:s');
				
				$reset_link = site_url().'resetpass/token/'.$user->id.'/'.$_POST['companytype'].'/'.$token;
				$timestamp = strtotime('+2 day', strtotime($today));
					
				$contact_name = trim($user->firstname." ".$user->lastname);

				$this->load->library('parser');
				$this->load->helper('file');
				
				$delete_existing = "DELETE FROM pw_reset WHERE email = '".$user->email."'";
				$this->db->query($delete_existing);
				
				$sql = "INSERT INTO `pw_reset` (`email`, `timestamp`, `token`) VALUES ('".$user->email."', '".$timestamp."', '".$token."');";
				$query = $this->db->query($sql);			
				
				// $config['protocol']    = 'smtp';
				// $config['smtp_host']    = 'smtp.gmail.com';
				// $config['smtp_port']    = '465';
				// $config['smtp_timeout'] = '7';
				// $config['smtp_user']    = 'emailtesterone@gmail.com';
				// $config['smtp_pass']    = 'kgn@123456';
				// $config['charset']    = 'utf-8';
				// $config['newline']    = "\r\n";
				// $config['mailtype'] = 'html';
				// $config['validation'] = TRUE; // bool whether to validate email or not      

				// $this->email->initialize($config);
				
				$this->load->library('email');
				// $this->email->from($data["core_settings"]->email, $data["core_settings"]->company);
				//$this->email->from('emailtesterone@gmail.com', $company[0]->name);
				$this->email->from($data["core_settings"]->from_email_id,$data["core_settings"]->from_email_name);
				$this->email->to($user->email); 

				$this->email->subject($data["core_settings"]->pw_reset_link_mail_subject);
				$parse_data = array(
            					'link' => $reset_link,
            					'company' => $company[0]->name,
            					'client_contact' => $contact_name,
            					'logo' => '<img src="'.site_url().$logo.'" alt="'.$data["core_settings"]->company.'"/>',
            					'invoice_logo' => '<img src="'.site_url().$logo.'" alt="'.$data["core_settings"]->company.'"/>'
            					);
	  			$email = read_file('./application/views/'.$data["core_settings"]->template.'/templates/email_pw_reset_link.html');
	  			$message = $this->parser->parse_string($email, $parse_data);				
				$this->email->message($message);
				if($this->email->send()) {
					$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_password_reset_email'));					
				} else {
					$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_password_reset_email_error'));
				}
			    redirect('login');
			}else{
				$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_password_reset_email_error'));

			    redirect('login');
			}
			
			
				
		}
		
	}
	
	function token($token = FALSE){
				$this->load->database();
				$sql = "SELECT * FROM `pw_reset` WHERE token = '".$token."'";
				$query = $this->db->query($sql);
				$result = $query->result();
				if($result){
					$lees = $result[0]->timestamp + (24 * 60 * 60);
					if(time() < $lees){
						$new_password = substr(str_shuffle(strtolower(sha1(rand() . time() . "nekdotlggjaoudlpqwejvlfk"))),0, 8);
						if($result[0]->user == "1"){
							$user = User::find_by_email($result[0]->email);	
							$user->set_password($new_password);
							$user->save();
							$contact_name = $user->firstname." ".$user->lastname;
						}else{
							$client = Client::find_by_email($result[0]->email);	
							$client->password = $client->set_password($new_password);
							$client->save();
							$contact_name = $client->firstname." ".$client->lastname;

						}
						$sql = "DELETE FROM `pw_reset` WHERE `email`='".$result[0]->email."'";
						$query = $this->db->query($sql);

						$data["core_settings"] = Setting::first();
						//$this->email->from($data["core_settings"]->email, $data["core_settings"]->company);
						$this->email->from($data["core_settings"]->from_email_id,$data["core_settings"]->from_email_name);
						$this->email->to($result[0]->email); 
						$this->load->library('parser');
						$this->load->helper('file');
						$this->email->subject($data["core_settings"]->pw_reset_mail_subject);
						$parse_data = array(
										'password' => $new_password,
		            					'link' => base_url(),
		            					'company' => $data["core_settings"]->company,
		            					'client_contact' => $contact_name,
		            					'logo' => '<img src="'.base_url().''.$data["core_settings"]->logo.'" alt="'.$data["core_settings"]->company.'"/>',
		            					'invoice_logo' => '<img src="'.base_url().''.$data["core_settings"]->invoice_logo.'" alt="'.$data["core_settings"]->company.'"/>'
		            					);
			  			$email = read_file('./application/views/'.$data["core_settings"]->template.'/templates/email_pw_reset.html');
			  			$message = $this->parser->parse_string($email, $parse_data);
						$this->email->message($message);
						$this->email->send();
						$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_password_reset'));
						redirect('forgotpass');
					}

				}else{
					redirect('login');
				}	
	}
	
}

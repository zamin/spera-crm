<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Resetpass extends MY_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	function index()
	{	
		if($_POST && $_POST['token']) {
			
			$user_id = $_POST['user_id'];
			$company_id = $_POST['company_id'];
			$email = $_POST['email'];
			$token = $_POST['token'];
			$token_query = "SELECT * FROM `pw_reset` WHERE email = '".$email."' AND token = '".$token."'";
			$result = $this->db->query($token_query)->row_array();
		
			$user = User::find_by_email($result['email']);
			if($user) {

				# convert password to hash
				$user->set_password($_POST['password']);
				
				// $_POST = array_map('htmlspecialchars', $_POST);
				// $password_saved = $user->update_attributes($_POST);
				$this->db->set('hashed_password', $user->hashed_password);
				$this->db->where('id', $user_id);
				$password_saved = $this->db->update('users');
				
				# do login process if password saved successfully
				if( $password_saved ){
					$delete_existing = "DELETE FROM pw_reset WHERE email = '".$user->email."'";
					$this->db->query($delete_existing);
					$user = User::validate_login($result['email'], $_POST['password'], $company_id);
					if($user){	
					
						# set user data in session 
						// $this->session->set_userdata('user_email', $result['email']);
						$user_query = $this->db->select('*')->from('users')->join('user_roles', 'user_roles.user_id = users.id')->where("users.id = ".$user_id)->get()->row_array();
						
						$user_role = $this->db->select('*')->from('roles')->where("role_id = ". $user_query['role_id'])->get()->row_array();
						
						if($user_role['roles'] == 'Freelancer'):
							$set_page = 'aodashboard'; 
						elseif($user_role['roles'] == 'Client'):
							$set_page = 'cdashboard'; 
						elseif($user_role['roles'] == 'Sub-Contractor'):
							$set_page = 'scdashboard'; 
						endif;
						
						$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_password_changed'));
						redirect(base_url().$company_id.'/'.$set_page."/");
					} else {
						$this->session->set_flashdata('message', 'error:Sorry due to missing some valid details, we are unable to reset your password.'.$this->lang->line('messages_save_client_error'));
						redirect('login');
					}
				}
				/* 
				[password] => 121212
				[invite_url] => 
				[user_id] => 
				[company_id] => 
				[user_roles_id] => 
				[send] => Save
				*/
			}
			
		} else {			
			redirect('login');
		}
	}
	function token($user_id = FALSE){
		
		// $user_id = $this->uri->segment(3);
		$company_id = $this->uri->segment(4);
		$invite_token = $this->uri->segment(5);
		
		$token_query = "SELECT * FROM `pw_reset` WHERE token = '".$invite_token."' AND timestamp >= ". time();
		$result = $this->db->query($token_query)->row_array();
		
		if($result){
		
			$userdata['user_id'] = $user_id;
			$userdata['company_id'] = $company_id;
			$userdata['email'] = $result['email'];
			$userdata['token'] = $invite_token;
			$this->view_data['userdata'] = $userdata;
			$this->theme_view = 'login';
			$this->content_view = 'auth/resetpassword';
			// $new_password = substr(str_shuffle(strtolower(sha1(rand() . time() . "nekdotlggjaoudlpqwejvlfk"))),0, 8);
			// if($result[0]->user == "1"){
				// $user = User::find_by_email($result[0]->email);	
				// $user->set_password($new_password);
				// $user->save();
				// $contact_name = $user->firstname." ".$user->lastname;
			// }else{
				// $client = Client::find_by_email($result[0]->email);	
				// $client->password = $client->set_password($new_password);
				// $client->save();
				// $contact_name = $client->firstname." ".$client->lastname;

			// }
			// $sql = "DELETE FROM `pw_reset` WHERE `email`='".$result[0]->email."'";
			// $query = $this->db->query($sql);

			// $data["core_settings"] = Setting::first();
			// $this->email->from($data["core_settings"]->email, $data["core_settings"]->company);
			// $this->email->to($result[0]->email); 
			// $this->load->library('parser');
			// $this->load->helper('file');
			// $this->email->subject($data["core_settings"]->pw_reset_mail_subject);
			// $parse_data = array(
							// 'password' => $new_password,
							// 'link' => base_url(),
							// 'company' => $data["core_settings"]->company,
							// 'client_contact' => $contact_name,
							// 'logo' => '<img src="'.base_url().''.$data["core_settings"]->logo.'" alt="'.$data["core_settings"]->company.'"/>',
							// 'invoice_logo' => '<img src="'.base_url().''.$data["core_settings"]->invoice_logo.'" alt="'.$data["core_settings"]->company.'"/>'
							// );
			// $email = read_file('./application/views/'.$data["core_settings"]->template.'/templates/email_pw_reset.html');
			// $message = $this->parser->parse_string($email, $parse_data);
			// $this->email->message($message);
			// $this->email->send();
			// $this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_password_reset'));
			// redirect('forgotpass');
		

		}else{
			$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_reset_password_token_expired'));
			redirect('login');
		}	
	}
	
}

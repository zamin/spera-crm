<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ClassName: Invitation
 * Function Name: index 
 * This class is used for adding invited user via email
 **/
class Invitation extends MY_Controller
{
	/* contructor function */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/* Index function will first check if the registration data is submitted or not if submitted 
	   then it will enter if condition 
	 * Other wise it will enter else condition 
	 */
	function index()
	{	
		redirect('login');
		// $this->content_view = 'invitation';
	}
	function accept()
	{	
		$invite_url = site_url(uri_string());
		$user_roles_id = $this->uri->segment(3);
		$company_id = $this->uri->segment(4);
		$invite_token = $this->uri->segment(5);
		
		$user_token_expired = $this->db->select('*')->from('user_meta')->where("user_roles_id = ".$user_roles_id." AND meta_key LIKE 'invite_token_expiry_status' AND meta_value = 'expired'")->get();
		
		/* 
		# update to expired if expired time > today
		if($user_token_expired->num_rows() != 0) { 
			$this->db->set('meta_value', 'expired');
			$this->db->where('meta_key', 'invite_token_expiry_status');
			$this->db->update('user_meta');
		} */
		
		if(!empty($user_roles_id) && !empty($company_id) && !empty($invite_token) && $user_token_expired->num_rows() == 0) {
			
			# get data to check valid token
			$user_meta_token = $this->db->select('*')->from('user_meta')->where("user_roles_id = ".$user_roles_id." AND meta_key = 'invite_token_".$company_id."' AND meta_value = '".$invite_token."'")->get();
			if($user_meta_token->num_rows() > 0) {
				
				# get token expiry
				$user_meta_query = $this->db->select('*')->from('user_meta')->where("user_roles_id = ".$user_roles_id." AND meta_key = 'invite_token_expiry' AND  ".strtotime(date('Y-m-d H:i:s'))." <= CAST(meta_value AS UNSIGNED INTEGER)")->get();
				
				if($user_meta_query->num_rows() > 0) {
				
					# get user id from user roles table
					$user_roles_query = $this->db->select('*')->from('user_roles')->where("id = ".$user_roles_id)->get();
					if($user_roles_query->num_rows() > 0) {
						$user_roles_data = $user_roles_query->row_array();
						
						# get user details
						$users_query = $this->get_user_data( $user_roles_data['user_id'] );
						if($users_query->num_rows() > 0) {
							$user_data = $users_query->row_array();
							
							$this->view_data['company_id'] = $company_id;
							$this->view_data['user_roles_id'] = $user_roles_id;
							$this->view_data['invite_url'] = $invite_url;
							$this->view_data['user_data'] = $user_data;
							$this->view_data['title'] = $this->lang->line('application_edit_client');
							$this->view_data['form_action'] = 'invitation/update';
							$this->theme_view = 'login';
							$this->content_view = 'invitation/invitation';
						} else {
							redirect('login');
						}

					} else {
						redirect('login');
					}

				} else {
					$this->view_data['token_expired'] = 'Your invitation token is expired. Please contact site administrator';
					$this->theme_view = 'login';
					$this->content_view = 'invitation/invitation';
					// redirect('login');
				}

			} else {
				redirect('login');
			}

		} else {
			redirect('login');
		}
		
	}
	function update($id = FALSE, $getview = FALSE)
	{	
		if($_POST) {
		
			if(!empty($_POST['user_id']) && !empty($_POST['password'])) {
				$user_id = $_POST['user_id'];
				$user_roles_id = $_POST['user_roles_id'];
				$company_id = $_POST['company_id'];
				
				# check if user password already exist
				
				$user = User::find($user_id);
				unset($_POST['company_id']);
				unset($_POST['user_roles_id']);
				unset($_POST['email']);
				unset($_POST['user_id']);
				unset($_POST['send']);
				unset($_POST['invite_url']);
								
				$this->db->set('firstname', $_POST['firstname']);
				$this->db->set('lastname', $_POST['lastname']);
				$this->db->where('id', $user_id);
				$this->db->update('users');
				
				# convert password to hash
				$user->set_password($_POST['password']);
				
				// $_POST = array_map('htmlspecialchars', $_POST);
				// $password_saved = $user->update_attributes($_POST);
				$this->db->set('hashed_password', $user->hashed_password);
				$this->db->where('id', $user_id);
				$password_saved = $this->db->update('users');
				
				# do login process if password saved successfully
				if( $password_saved ){
					$this->db->set('status', 'active');
					$this->db->where('id', $user_id);
					$this->db->update('users');
					
					$this->db->set('meta_value', 'expired');
					$this->db->where('meta_key', 'invite_token_expiry_status');
					$this->db->where('user_roles_id', $user_roles_id);
					$this->db->update('user_meta');
					
					$users_query = $this->get_user_data($user_id);
					$user_data = $users_query->row_array();
					
					# validate and login user to site
					$user = User::validate_login($user_data['email'], $_POST['password'], $company_id);
					if($user){	
					
						# set user data in session 
						$this->session->set_userdata('user_email', $user_data['email']);
						$user_query = $this->db->select('*')->from('users')->join('user_roles', 'user_roles.user_id = users.id')->where("users.id = ".$user_id)->get()->row_array();
						
						$user_role = $this->db->select('*')->from('roles')->where("role_id = ". $user_query['role_id'])->get()->row_array();
						
						if($user_role['roles'] == 'Client'):
							$set_page = 'cdashboard'; 
						elseif($user_role['roles'] == 'Sub-Contractor'):
							$set_page = 'scdashboard'; 
						endif;
						redirect(base_url().$company_id.'/'.$set_page."/");
						// if($this->input->cookie('fc2_link') != ""){
						// }
					} else {
							redirect('login');
					}
				} else {
					$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_save_client_error'));
					redirect($_POST['invite_url']);	
				}
			
				
			} else {
				$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_save_client_error'));
				redirect($_POST['invite_url']);	
			}
			
		} else {
			redirect('login');
		}
	}
	
	function get_user_data($user_id = null) {
		if( !empty( $user_id ) ){
			return $this->db->select('*')->from('users')->where("id = ".$user_id)->get();		
		}else{
			return '';
		}
	}
}
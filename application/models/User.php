<?php
/**
 * ClassName: User
 * Table Name: user
 **/
 
class User extends ActiveRecord\Model
{
	static $has_many = array(
	     array('company_has_admins'),
	     array('tickets'),
	     array('project_has_workers'),
	     array('companies', 'through' => 'company_has_admins'),
	     array('projects', 'through' => 'project_has_workers'),
	     array('project_has_tasks'),
	     array('project_has_timesheets'),
	     array('quotes'),
	     array('quoterequests'),

    );
      
    static $belongs_to = array(
     array('queue', 'primary_key' => 'queue'),
  	);

	var $password = FALSE;
	function before_save()
	{
        if($this->password)
			$this->hashed_password = $this->hash_password($this->password);
	}
	
	function set_password($plaintext)
	{
        $this->hashed_password = $this->hash_password($plaintext);
	}
	private function hash_password($password)
	{
        $salt = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
		$hash = hash('sha256', $salt . $password);
		
		return $salt . $hash;
	}
	
	private function validate_password($password)
	{
        $salt = substr($this->hashed_password, 0, 64);
        $hash = substr($this->hashed_password, 64, 64);
		$password_hash = hash('sha256', $salt . $password);
		
        return $password_hash == $hash;
	}

	public static function validate_login($email, $password, $cid)
	{
		// $user = User::find_by_email_and_status($email, "active");
		if($cid != 'admin') {
			$user = User::validate_company_user($email, $cid);
			$email = $valid_company_user->email;
		} else {
			$user = User::find_by_email_and_status($email, "active");
		}

		if($user && $user->validate_password($password) && $user->status == 'active')
		{

			// $get_user_details = $user->validate_company_user($email,$cid);

			if($cid == 'admin') {
			// if( ($cid == 'admin') && !empty($get_user_details)) {

					User::login($user->id, 'user_id');
					User::login($user->email, 'email');
					$update = User::find($user->id);
					$update->last_login = time();
					$update->save();
					return $user;

			} elseif(!empty($user)) {
                
                    
                    $array = array();
                    $array['user_id'] = $user->id;
                    $array['company_id'] = $user->company_id;
                    $array['email'] = $user->email;
                    $array['role_id'] = $user->role_id;

                    $CI =& get_instance();
                    User::login($array, $user->company_id);
                    








					$update = User::find($user->id);
					$update->last_login = time();
					$update->save();
					return $user;				

			} else {
				return FALSE;
			}
		}
        else{
			return FALSE;
		}
	}
	
	public static function login($user_id, $type)
	{
		$CI =& get_instance();
		$CI->session->set_userdata($type, $user_id);
        
	}

	public static function logout($cid = false)
	{
		$CI =& get_instance();
        if($cid)
        {
            $CI->session->unset_userdata($cid);
        }
        else
        {
		    $CI->session->unset_userdata();
		    $CI->session->sess_destroy();
        }
	}
	
	public static function validate_company_user($email,$cid) {

		if($cid == 'admin') {

			$validate_admin = 'SELECT * from users WHERE status= "active" AND admin = "1" AND email = "'.$email.'"';

			$user_validation = User::find_by_sql($validate_admin);

			return $user_validation[0];
		} elseif(!empty($cid)) {

			$validate_user_in_company = 'SELECT u.*, c.name, ur.role_id, ur.company_id FROM 	users AS u LEFT JOIN user_roles AS ur ON u.id = ur.user_id LEFT JOIN companies AS c ON ur.company_id = c.id WHERE u.status = "active" AND u.email = "'.$email.'" AND c.id = "'.$cid.'"';
			$user_validation = User::find_by_sql($validate_user_in_company);

			return $user_validation[0];
		} else {

			return FALSE;
		}

	}
}

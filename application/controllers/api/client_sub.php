<?php

/**
 * ClassName: Api Client Subcontractor
 * Function Name: index 
 * */
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Client_sub extends MY_Api_Controller {

    function __construct() {
        parent::__construct();
        $this->load->database();
        $this->settings = Setting::first();
        $this->headers = apache_request_headers();
        //var_dump($headers);exit;
//        $user_access_token = $this->headers['User-Access-Token'] ? $this->headers['User-Access-Token'] : '';
//        if (empty($user_access_token)) {
//            $newdata = array('result' => 'fail', 'response' => array('code'=>404,'message'=>'Access token not found'));
//            $this->response($newdata);
//        }
//
//        $this->headers['User-Login-Token'] = $this->headers['User-Login-Token'] ? $this->headers['User-Login-Token'] : '';
//        if (empty($this->headers['User-Login-Token'])) {
//            $newdata = array('result' => 'fail', 'response' => array('code'=>404,'message'=>'Login token not found'));
//            $this->response($newdata);
//        }
//        $this->user_id = $this->checklogin($user_access_token, $this->headers['User-Login-Token']);
        $user_access_token = $this->headers['user_access_token'] ? $this->headers['user_access_token'] : '';
        if (empty($user_access_token)) {
            $newdata = array('result' => 'fail', 'response' => 'Access token not found');
            $this->response($newdata);
        }

        $this->headers['user_login_token'] = $this->headers['user_login_token'] ? $this->headers['user_login_token'] : '';
        if (empty($this->headers['user_login_token'])) {
            $newdata = array('result' => 'fail', 'response' => 'Login token not found');
            $this->response($newdata);
        }
        $this->user_id = $this->checklogin($user_access_token, $this->headers['user_login_token']);
    }

    /* :: start create client or sub contracotor :: */
    function create() {
        $newdata = array();
        if (empty($_REQUEST)) {
            $newdata = array('result' => 'fail', 'response' => array('code'=>404,'message' => 'No Data Found'));
            $this->response($newdata);
        }

        if ($this->user_id == 0) {
            $newdata = array('result' => 'fail', 'response' => array('code'=>404,'message' => 'No Data Found'));
            $this->response($newdata);
        }

        $get_data = $this->db->query('SELECT r.*,u.email,u.firstname,u.lastname from user_roles r join users u on u.id=r.user_id where r.user_id="' . $this->user_id . '"')->row_array();
        if (empty($get_data)) {
            $newdata = array('result' => 'fail', 'response' => array('code'=>404,'message' => 'No Data Found'));
            $this->response($newdata);
        }

        if ($get_data['role_id'] == 2) 
        {
			$check_package_query="Select * from propay_user_subscription where user_id='".$this->user_id."' AND CURDATE() between start_date and end_date and payment_detail_id !=0 ";
            
            $check_package_current_date=$this->db->query($check_package_query)->row_array();
            
            $package_id=$check_package_current_date['package_id'];
            
            if($package_id != 3)
            {
                $newdata = array('result' => 'fail', 'response' => array('code'=>400,'message' => 'You are not Business User So Can`t Use This API'));
                $this->response($newdata);
            }
            
            $check_users_company=$this->db->query('Select count(*) as total from user_roles r join users u on r.user_id=u.id where r.role_id != 2 and r.company_id="'.$get_data['company_id'].'" and u.status != "deleted"')->row_array();
            
            $total_users = $check_users_company['total'];
            
            if($total_users > 25)
            {
                $newdata = array('result' => 'fail', 'response' => array('code'=>400,'message' => 'Your sub accounts users limit exceeded'));
                $this->response($newdata);
            }
			 
            $user_type = trim(htmlspecialchars($_REQUEST['user_type'])) ? trim(htmlspecialchars($_REQUEST['user_type'])) : '';
            $firstname = trim(htmlspecialchars($_REQUEST['firstname'])) ? trim(htmlspecialchars($_REQUEST['firstname'])) : '';
            $lastname = trim(htmlspecialchars($_REQUEST['lastname'])) ? trim(htmlspecialchars($_REQUEST['lastname'])) : '';
            $email = trim(htmlspecialchars($_REQUEST['email'])) ? trim(htmlspecialchars($_REQUEST['email'])) : '';
            if (empty($user_type)) {
                $newdata = array('result' => 'fail', 'response' => array('code'=>400,'message' => "Please enter user type"));
                $this->response($newdata);
            }
            if (empty($firstname)) {
                $newdata = array('result' => 'fail', 'response' => array('code'=>400,'message' => "Please enter firstname"));
                $this->response($newdata);
            }
            if (empty($lastname)) {
                $newdata = array('result' => 'fail', 'response' => array('code'=>400,'message' => "Please enter lastname"));
                $this->response($newdata);
            }
            if (empty($email)) {
                $newdata = array('result' => 'fail', 'response' => array('code'=>400,'message' => "Please enter email"));
                $this->response($newdata);
            }

            if ($user_type != "client" && $user_type != "subcontractor") {
                $newdata = array('result' => 'fail', 'response' => array('code'=>400,'message' => "Please enter user type client or subcontractor"));
                $this->response($newdata);
            }

            $company_id = $get_data['company_id'];
            $get_company = Company::find($company_id);
            $role_id = $get_data['role_id'];
            $user_email = $get_data['email'];
            if ($user_type == 'client') {
                $email_exist = $this->db->select('*')->from('users')->join('user_roles', 'user_roles.user_id = users.id')->where("email = '" . $email . "' AND user_roles.company_id = " . $company_id)->get()->num_rows();
                if ($email_exist != 0) {
                    $newdata = array('result' => 'fail', 'response' => array('code'=>400,'message' => 'Email already exits'));
                    $this->response($newdata);
                }

                $modules = Module::find('all', array('order' => 'sort asc', 'conditions' => array('type = ?', 'client')));
                $access = array();
                foreach ($modules as $key => $value) {
                    $access[] = $value->id;
                }
                $access = implode(",", $access);

                $status = 'inactive';
                $admin = '0';
                $userdata = array(
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'email' => $email,
                    'access' => $access,
                    'status' => $status,
                    'admin' => $admin
                );
                $userdata = array_map('htmlspecialchars', $userdata);
                $user = User::create($userdata);
                if (!$user) {
                    $newdata = array('result' => 'fail', 'response' => array('code'=>400,'message' => 'Client Added Unsuccessfully'));
                    $this->response($newdata);
                }

                $user->save();
                $insert_id = $user->id;
                $company_details = array('user_id' => $insert_id, 'role_id' => 3, 'company_id' => $company_id, 'created_at' => date('Y-m-d H:i:s'));
                $company_details = array_map('htmlspecialchars', $company_details);
                $user_role = UserRole::create($company_details);
                if (!$user_role) {
                    $newdata = array('result' => 'fail', 'response' => array('code'=>400,'message' => 'UserRole Added Unsuccessfully'));
                    $this->response($newdata);
                }

                $user_role->save();
                $roles_insert_id = $user_role->id;
                $unique_key = md5(uniqid(rand(), true));
                $today = date('Y-m-d H:i:s');
                $invite_url = site_url() . 'invitation/accept/' . $roles_insert_id . '/' . $company_id . '/' . $unique_key;
                $invite_token_expiry = strtotime('+2 day', strtotime($today));

                $user_meta = array(
                    'user_roles_id' => $roles_insert_id,
                    'meta_key' => 'invite_token_' . $company_id,
                    'meta_value' => $unique_key
                );
                $this->db->insert('user_meta', $user_meta);

                $user_meta_2 = array(
                    'user_roles_id' => $roles_insert_id,
                    'meta_key' => 'invite_token_expiry',
                    'meta_value' => $invite_token_expiry
                );
                $this->db->insert('user_meta', $user_meta_2);
                $user_meta_3 = array(
                    'user_roles_id' => $roles_insert_id,
                    'meta_key' => 'invite_token_expiry_status',
                    'meta_value' => ''
                );
                $this->db->insert('user_meta', $user_meta_3);
                /* $config['protocol'] = 'smtp';
                  $config['smtp_host'] = 'ssl://smtp.gmail.com';
                  $config['smtp_port'] = '465';
                  $config['smtp_timeout'] = '7';
                  $config['smtp_user'] = 'emailtesterone@gmail.com';
                  $config['smtp_pass'] = 'kgn@123456';
                  $config['charset'] = 'utf-8';
                  $config['newline'] = "\r\n";
                  $config['mailtype'] = 'html';
                  $config['validation'] = TRUE; // bool whether to result email or not

                  $this->email->initialize($config); */
                //$newdata = array('result' => 'success', 'response' => array('message' => "Client Added Successfully"));
                //$this->response($newdata);
                $this->load->library('email');
                $this->email->from($this->settings->from_email_id,$this->settings->from_email_name);
                $this->email->to($email);
                $this->email->subject('Spera Invitation');
                $this->email->message('Click on <a href="' . $invite_url . '">Spera Invitation Link</a> to complete signup and access your profile.');

                $mail_sent = null;
                if ($this->email->send()) {
                    $mail_sent = 'Invitation mail sent.';
                    $newdata = array('result' => 'success', 'response' => array('code'=>200,'user_id' => $user->id, 'role_id' => 3, 'message' => "Client Added Successfully"));
                    $this->response($newdata);
                }

                $newdata = array('result' => 'fail', 'response' => array('code'=>400,'user_id' => $user->id, 'role_id' => 3, 'message' => "Client Added Successfully But mail not sent"));
                $this->response($newdata);
            }
            if ($user_type == 'subcontractor') {
                $email_exist = $this->db->select('*')->from('users')->join('user_roles', 'user_roles.user_id = users.id')->where("email = '" . $email . "' AND user_roles.company_id = " . $company_id)->get()->num_rows();
                if ($email_exist != 0) {
                    $newdata = array('result' => 'fail', 'response' => array('code'=>400,'message' => 'Email already exits'));
                    $this->response($newdata);
                }

                $modules = Module::find('all', array('order' => 'sort asc', 'conditions' => array('type = ?', 'sub-contractor')));
                $access = array();
                foreach ($modules as $key => $value) {
                    $access[] = $value->id;
                }
                $access = implode(",", $access);

                $status = 'inactive';
                $admin = '0';
                $userdata = array(
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'email' => $email,
                    'access' => $access,
                    'status' => $status,
                    'admin' => $admin
                );
                $userdata = array_map('htmlspecialchars', $userdata);
                $user = User::create($userdata);
                if (!$user) {
                    $newdata = array('result' => 'fail', 'response' => array('code'=>400,'message' => 'Subcontractor Added Unsuccessfully'));
                    $this->response($newdata);
                }


                $user->save();
                $insert_id = $user->id;
                $company_details = array('user_id' => $insert_id, 'role_id' => 4, 'company_id' => $company_id, 'created_at' => date('Y-m-d H:i:s'));
                $company_details = array_map('htmlspecialchars', $company_details);
                $user_role = UserRole::create($company_details);
                if (!$user_role) {
                    $newdata = array('result' => 'fail', 'response' => array('code'=>400,'message' => 'UserRole Added Unsuccessfully'));
                    $this->response($newdata);
                }

                $user_role->save();
                $roles_insert_id = $user_role->id;
                $unique_key = md5(uniqid(rand(), true));
                $today = date('Y-m-d H:i:s');
                $invite_url = site_url() . 'invitation/accept/' . $roles_insert_id . '/' . $company_id . '/' . $unique_key;
                $invite_token_expiry = strtotime('+2 day', strtotime($today));

                $user_meta = array(
                    'user_roles_id' => $roles_insert_id,
                    'meta_key' => 'invite_token_' . $company_id,
                    'meta_value' => $unique_key
                );
                $this->db->insert('user_meta', $user_meta);

                $user_meta_2 = array(
                    'user_roles_id' => $roles_insert_id,
                    'meta_key' => 'invite_token_expiry',
                    'meta_value' => $invite_token_expiry
                );
                $this->db->insert('user_meta', $user_meta_2);
                $user_meta_3 = array(
                    'user_roles_id' => $roles_insert_id,
                    'meta_key' => 'invite_token_expiry_status',
                    'meta_value' => ''
                );
                $this->db->insert('user_meta', $user_meta_3);
                /* $config['protocol'] = 'smtp';
                  $config['smtp_host'] = 'ssl://smtp.gmail.com';
                  $config['smtp_port'] = '465';
                  $config['smtp_timeout'] = '7';
                  $config['smtp_user'] = 'emailtesterone@gmail.com';
                  $config['smtp_pass'] = 'kgn@123456';
                  $config['charset'] = 'utf-8';
                  $config['newline'] = "\r\n";
                  $config['mailtype'] = 'html';
                  $config['validation'] = TRUE; // bool whether to result email or not

                  $this->email->initialize($config); */
                //$newdata = array('result' => 'success', 'response' => array('message' => "Subcontractor Added Successfully"));
                //$this->response($newdata);
                $this->load->library('email');
                $this->email->from($this->settings->from_email_id,$this->settings->from_email_name);
                $this->email->to($email);
                $this->email->subject('Spera Invitation');
                $this->email->message('Click on <a href="' . $invite_url . '">Spera Invitation Link</a> to complete signup and access your profile.');

                $mail_sent = null;
                if ($this->email->send()) {
                    $mail_sent = 'Invitation mail sent.';
                    $newdata = array('result' => 'success', 'response' => array('code'=>200,'user_id' => $user->id, 'role_id' => 4, 'message' => "Subcontractor Added Successfully"));
                    $this->response($newdata);
                }

                $newdata = array('result' => 'fail', 'response' => array('code'=>400,'user_id' => $user->id, 'role_id' => 4, 'message' => "Subcontractor Added Successfully But mail not sent"));
                $this->response($newdata);
            }
        }
        $newdata = array('result' => 'fail', 'response' => array('code'=>400,'message' => 'You are not Business User So Can`t Use This API'));
        $this->response($newdata);
    }

    /* Client Update Password */
    function update() {
        $newdata = array();
        if (empty($_REQUEST)) {
            $newdata = array('result' => 'fail', 'response' => array('code'=>404,'message' => 'No Data Found'));
            $this->response($newdata);
        }
        $user_id = trim(htmlspecialchars($_REQUEST['user_id'])) ? trim(htmlspecialchars($_REQUEST['user_id'])) : '';
        if (empty($user_id)) {
            $newdata = array('result' => 'fail', 'response' => array('message' => "User does not exit"));
            $this->response($newdata);
        }
        $firstname = trim(htmlspecialchars($_REQUEST['firstname'])) ? trim(htmlspecialchars($_REQUEST['firstname'])) : '';
        $lastname = trim(htmlspecialchars($_REQUEST['lastname'])) ? trim(htmlspecialchars($_REQUEST['lastname'])) : '';
        $password = trim(htmlspecialchars($_REQUEST['password'])) ? trim(htmlspecialchars($_REQUEST['password'])) : '';
        if (empty($firstname)) {
            $newdata = array('result' => 'fail', 'response' => array('message' => "Please enter firstname"));
            $this->response($newdata);
        }
        if (empty($lastname)) {
            $newdata = array('result' => 'fail', 'response' => array('message' => "Please enter lastname"));
            $this->response($newdata);
        }
        if (empty($password)) {
            $newdata = array('result' => 'fail', 'response' => array('message' => "Please enter password"));
            $this->response($newdata);
        }
        $user = User::find($user_id);
        if (!$user) {
            $newdata = array('result' => 'fail', 'response' => array('message' => "User does not exit"));
            $this->response($newdata);
        }
        $this->db->set('firstname', $firstname);
        $this->db->set('lastname', $lastname);
        $this->db->where('id', $user_id);
        $this->db->update('users');

        # convert password to hash
        $user->set_password($password);
        $this->db->set('hashed_password', $user->hashed_password);
        $this->db->where('id', $user_id);
        $password_saved = $this->db->update('users');
        if (!$password_saved) {
            $newdata = array('result' => 'fail', 'response' => array('message' => "Password saved Unsuccessfully"));
            $this->response($newdata);
        }
        $newdata = array('result' => 'success', 'response' => array('user_id' => $user_id, 'message' => "Password saved Successfully"));
        $this->response($newdata);
    }

    /* Accept Invitation */
    function accept() {
        $invite_url = site_url(uri_string());
        $user_roles_id = $this->uri->segment(4);
        $company_id = $this->uri->segment(5);
        $invite_token = $this->uri->segment(6);
        $newarr = array();
        $user_token_expired = $this->db->select('*')->from('user_meta')->where("user_roles_id = " . $user_roles_id . " AND meta_key LIKE 'invite_token_expiry_status' AND meta_value = 'expired'")->get();

        if (!empty($user_roles_id) && !empty($company_id) && !empty($invite_token) && $user_token_expired->num_rows() == 0) {

            # get data to check valid token
            $user_meta_token = $this->db->select('*')->from('user_meta')->where("user_roles_id = " . $user_roles_id . " AND meta_key = 'invite_token_" . $company_id . "' AND meta_value = '" . $invite_token . "'")->get();
            if ($user_meta_token->num_rows() > 0) {

                # get token expiry
                $user_meta_query = $this->db->select('*')->from('user_meta')->where("user_roles_id = " . $user_roles_id . " AND meta_key = 'invite_token_expiry' AND  " . strtotime(date('Y-m-d H:i:s')) . " <= CAST(meta_value AS UNSIGNED INTEGER)")->get();

                if ($user_meta_query->num_rows() > 0) {

                    # get user id from user roles table
                    $user_roles_query = $this->db->select('*')->from('user_roles')->where("id = " . $user_roles_id)->get();
                    if ($user_roles_query->num_rows() > 0) {
                        $user_roles_data = $user_roles_query->row_array();

                        # get user details
                        $users_query = $this->get_user_data($user_roles_data['user_id']);
                        if ($users_query->num_rows() > 0) {
                            $user_data = $users_query->row_array();
                            $dataArr = array(
                                'company_id' => $company_id,
                                'user_roles_id' => $user_roles_id,
                                'invite_url' => $invite_url,
                            );
                            $newdata = array('result' => 'success', 'response' => $dataArr);
                            $this->response($newdata);
                        }
                        $newdata = array('result' => 'fail', 'response' => array('message' => 'User does not exits'));
                        $this->response($newdata);
                    }
                    $newdata = array('result' => 'fail', 'response' => array('message' => 'UserRole does not exits'));
                    $this->response($newdata);
                }
                $newdata = array('result' => 'fail', 'response' => array('message' => 'Your invitation token is expired. Please contact site administrator'));
                $this->response($newdata);
            }
            $newdata = array('result' => 'fail', 'response' => array('message' => 'Your token is not result'));
            $this->response($newdata);
        }
        $newdata = array('result' => 'fail', 'response' => array('message' => 'No Data Found'));
        $this->response($newdata);
    }

    function get_user_data($user_id = null) {
        if (!empty($user_id)) {
            return $this->db->select('*')->from('users')->where("id = " . $user_id)->get();
        } else {
            return '';
        }
    }

    /* :: end client or sub contracotor :: */
    
    /* Client List */
    function client_list()
    {
        $newdata=array();
        
        //echo $this->user_id;exit;
        if($this->user_id == 0) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }
        $get_data = $this->db->query('SELECT r . * , u.email, u.firstname, u.lastname, u.email FROM user_roles r LEFT JOIN users u ON r.user_id = u.id WHERE r.user_id = "' . $this->user_id . '"')->row_array();
        if(empty($get_data)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'User data not found'));
            $this->response($newdata);
        }
        $role_id = $get_data['role_id'];
        $company_id = $get_data['company_id'];
        if($role_id==2)
        {
            if(empty($company_id))
            {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'Company not found'));
                $this->response($newdata);
            }
            $company_name=Company::find_by_id($company_id);
            $client_query = $this->db->query('select * from users u join user_roles r on u.id=r.user_id where u.status IN ("active", "inactive") AND r.role_id=3 and company_id="'.$company_id.'"')->result_array();
            if(!empty($client_query))
            {
                $newArr=array();
                //$newArr['code']=200;
                $i=0;
                foreach($client_query as $key=>$value)
                {
                    $newArr[$i]['id']=$value['id'];
                    $newArr[$i]['company_name']=$company_name->name;
                    $newArr[$i]['company_id']=$company_id;
                    $newArr[$i]['firstname']=$value['firstname'];
                    $newArr[$i]['lastname']=$value['lastname'];
                    $newArr[$i]['email']=$value['email'];
                    $newArr[$i]['status']=$value['status'];
                    $i++;
                }
                //echo "<pre>";print_r($newArr);exit;
                $newdata = array('result' => 'success','response' => $newArr,'code'=>200);
                $this->response($newdata);
            }
            $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'Not added any clients'));
            $this->response($newdata);
        }
        $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'You are not Business User So can`t show client list'));
        $this->response($newdata);
    }
    
    
    /* Sub-contractor List */
    function subcontractor_list()
    {
        $newdata=array();
        if($this->user_id == 0) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }
        $get_data = $this->db->query('SELECT r . * , u.email, u.firstname, u.lastname, u.email FROM user_roles r LEFT JOIN users u ON r.user_id = u.id WHERE r.user_id = "' . $this->user_id . '"')->row_array();
        if(empty($get_data)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'User data not found'));
            $this->response($newdata);
        }
        $role_id = $get_data['role_id'];
        $company_id = $get_data['company_id'];
        if($role_id==2)
        {
            if(empty($company_id))
            {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'Company not found'));
                $this->response($newdata);
            }
            $company_name=Company::find_by_id($company_id);
            $client_query = $this->db->query('select * from users u join user_roles r on u.id=r.user_id where u.status IN ("active", "inactive") AND r.role_id=4 and company_id="'.$company_id.'"')->result_array();
            if(!empty($client_query))
            {
                $newArr=array();
                //$newArr['code']=200;
                $i=0;
                foreach($client_query as $key=>$value)
                {
                    $newArr[$i]['id']=$value['id'];
                    $newArr[$i]['company_name']=$company_name->name;
                    $newArr[$i]['company_id']=$company_id;
                    $newArr[$i]['firstname']=$value['firstname'];
                    $newArr[$i]['lastname']=$value['lastname'];
                    $newArr[$i]['email']=$value['email'];
                    $newArr[$i]['status']=$value['status'];
                    $i++;
                }
                //echo "<pre>";print_r($newArr);exit;
                $newdata = array('result' => 'success','response' => $newArr,'code'=>200);
                $this->response($newdata);
            }
            $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'Not added any clients'));
            $this->response($newdata);
        }
        $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'You are not Business User So can`t show subcontractor list'));
        $this->response($newdata);
    }
}

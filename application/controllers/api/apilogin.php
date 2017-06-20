<?php

/**
 * ClassName: APIlogin
 * Function Name: index 
 * This class is used Login Api
 * */
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Apilogin extends MY_Api_Controller {

    public $accesstoken = null;

    function __construct() {
        parent::__construct();
        $this->load->database();
        $this->settings = Setting::first();
        $headers = apache_request_headers();
        //echo "<pre>";print_r($headers);exit;
//        $user_access_token = $headers['User-Access-Token'] ? $headers['User-Access-Token'] : '';
//        if(empty($user_access_token)) {
//            $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Access token not found'));
//            $this->response($newdata);
//        }
//        $this->accesstoken = $user_access_token;
        $user_access_token = $headers['user_access_token'] ? $headers['user_access_token'] : '';
        if(empty($user_access_token)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Access token not found'));
            $this->response($newdata);
        }
        $this->accesstoken = $user_access_token;
        /* $user_login_token = $headers['user_login_token'] ? $headers['user_login_token'] : '';
          if(empty($user_login_token))
          {
          $newdata = array('validate' => 'error', 'response' => 'Login token not found');
          $this->response($newdata);
          } */
        //$this->user_sessoin_id = $this->checklogin($user_login_token);
    }

    /* :: start login and logout :: */

    function login() {
        //echo "<pre>";print_r($_REQUEST);exit;
        if (!empty($_REQUEST)) {
            $email = $_REQUEST['email'] ? $_REQUEST['email'] : '';
            $password = $_REQUEST['password'] ? $_REQUEST['password'] : '';
            if (!empty($email) && !empty($password)) {
                $sql_check_company_details = 'SELECT r.company_id,d.user_login_token,d.status FROM user_roles r JOIN user_api_details d ON r.user_id = d.user_id WHERE d.user_access_token = "' . $this->accesstoken . '"';
                $check_company_id = $this->db->query($sql_check_company_details)->row_array();
                if (empty($check_company_id)) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => "User does not exit"));
                    $this->response($newdata);
                }
                $company_id = $check_company_id['company_id'];
                $sql_check_company_password = 'SELECT u.id, u.email,u.hashed_password, ur.user_id as ur_id, ur.role_id, ur.company_id FROM users AS u LEFT JOIN user_roles AS ur ON u.id = ur.user_id WHERE u.status = "active" AND u.email = "' . $email . '" AND ur.company_id = "' . $company_id . '"';
                $check_user_validation = $this->db->query($sql_check_company_password)->row_array();
                if (empty($check_user_validation)) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'User does not validate'));
                    $this->response($newdata);
                }
                $user_id = $check_user_validation['ur_id'];
                $role_id = $check_user_validation['role_id'];
                $status = $check_company_id['status'];
                if ($role_id == 2 && $status == "disable") {
                    $user_login_token = $check_company_id['user_login_token'];
                    if(empty($user_login_token))
                    {
                        $user_login_token=$this->RandomString(42);
                        $update_api_token = array(
                            'user_login_token' => $user_login_token
                        );
                        $this->db->where('user_id', $user_id);
                        $this->db->update('user_api_details', $update_api_token);
                    }
                    $user_validate = User::validate_login($email, $password, $company_id);
                    if ($user_validate) {
                        $api_array = array(
                            'status' => 'enable'
                        );
                        $this->db->where('user_id', $user_id);
                        $this->db->update('user_api_details', $api_array);

                        $result = 'success';
                        $html_response['code'] = 200;
                        $html_response['message']['user_id'] = $user_id;
                        $html_response['message']['user_access_token'] = $this->accesstoken;
                        $html_response['message']['user_login_token'] = $user_login_token;
                        $newdata = array('result' => $result, 'response' => $html_response);
                        $this->response($newdata);
                    }
                    $result = 'fail';
                    $html_response['code'] = 400;
                    $html_response['message'] = 'Please enter correct password';
                    $newdata = array('result' => $result, 'response' => $html_response);
                    $this->response($newdata);
                }
                if ($role_id == 2 && $status == "enable") {
                    
                    $user_login_token = $check_company_id['user_login_token'];
                    if(empty($user_login_token))
                    {
                        $user_login_token=$this->RandomString(42);
                        $update_api_token = array(
                            'user_login_token' => $user_login_token
                        );
                        $this->db->where('user_id', $user_id);
                        $this->db->update('user_api_details', $update_api_token);
                    }
                    $user_validate = User::validate_login($email, $password, $company_id);
                    if ($user_validate) {
                        $result = 'success';
                        $html_response['code'] = 200;
                        $html_response['message']['user_id'] = $user_id;
                        $html_response['message']['user_access_token'] = $this->accesstoken;
                        $html_response['message']['user_login_token'] = $user_login_token;
                        $newdata = array('result' => $result, 'response' => $html_response);
                        $this->response($newdata);
                    }
                    $result = 'fail';
                    $html_response['code'] = 400;
                    $html_response['message'] = 'Please enter correct password';
                    $newdata = array('result' => $result, 'response' => $html_response);
                    $this->response($newdata);
                }
                if ($status == 'enable') {
                    $user_id = $check_user_validation['ur_id'];
                    $get_login_token = $this->db->query('Select * from user_api_details where user_id="' . $user_id . '"')->row_array();
                    if (empty($get_login_token)) {
                        $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'User Login Token not Found'));
                        $this->response($newdata);
                    }
                    $get_user_status = $get_login_token['status'];
                    $user_login_token = $get_login_token['user_login_token'];
                    if(empty($user_login_token))
                    {
                        $user_login_token=$this->RandomString(42);
                        $update_api_token = array(
                            'user_login_token' => $user_login_token
                        );
                        $this->db->where('user_id', $user_id);
                        $this->db->update('user_api_details', $update_api_token);
                    }
                    if ($get_user_status == 'enable') {
                        $user_validate = User::validate_login($email, $password, $company_id);
                        if ($user_validate) {
                            $result = 'success';
                            $html_response['code'] = 200;
                            $html_response['message']['user_id'] = $user_id;
                            $html_response['message']['user_access_token'] = $this->accesstoken;
                            $html_response['message']['user_login_token'] = $user_login_token;
                            $newdata = array('result' => $result, 'response' => $html_response);
                            $this->response($newdata);
                        }
                        $result = 'fail';
                        $html_response['code'] = 400;
                        $html_response['message'] = 'Please enter correct password';
                        $newdata = array('result' => $result, 'response' => $html_response);
                        $this->response($newdata);
                    }
                    $user_validate = User::validate_login($email, $password, $company_id);
                    if ($user_validate) {
                        $api_array = array(
                            'status' => 'enable'
                        );
                        $this->db->where('user_id', $user_id);
                        $this->db->update('user_api_details', $api_array);
                        $result = 'success';
                        $html_response['code'] = 200;
                        $html_response['message']['user_id'] = $user_id;
                        $html_response['message']['user_access_token'] = $this->accesstoken;
                        $html_response['message']['user_login_token'] = $user_login_token;
                        $newdata = array('result' => $result, 'response' => $html_response);
                        $this->response($newdata);
                    }
                    $result = 'fail';
                    $html_response['code'] = 400;
                    $html_response['message'] = 'Please enter correct password';
                    $newdata = array('result' => $result, 'response' => $html_response);
                    $this->response($newdata);
                }
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => "Your owner can`t permission to api access"));
                $this->response($newdata);
            }
            $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Please enter required fields'));
            $this->response($newdata);
        }
        $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
        $this->response($newdata);
    }

    function logout() {
        $headers = apache_request_headers();
        //$user_access_token = $headers['User-Access-Token'] ? $headers['User-Access-Token'] : '';
        //$user_login_token = $headers['User-Login-Token'] ? $headers['User-Login-Token'] : '';
        $user_access_token = $headers['user_access_token'] ? $headers['user_access_token'] : '';
        $user_login_token = $headers['user_login_token'] ? $headers['user_login_token'] : '';
        if(empty($user_login_token)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Login token not found'));
            $this->response($newdata);
        }
        
        $sql_check_company_details = 'SELECT * FROM user_api_details WHERE user_login_token = "' . $user_login_token . '" and status="enable"';
        $check_company_id = $this->db->query($sql_check_company_details)->row_array();
        //print_r($check_company_id);die;
        if(empty($check_company_id)) {
            $validate = 'fail';
            $html_response['code'] = 400;
            $html_response['message'] = 'Your login token not found or your api status is disabled';
            $newdata = array('result' => $validate, 'response' => $html_response);
            $this->response($newdata);
        } 
        $sql_check_api_status = 'SELECT * FROM user_api_details WHERE user_access_token = "' . $user_access_token . '" and status="enable"';
        $check_api_status = $this->db->query($sql_check_api_status)->row_array();
        if(empty($check_api_status))
        {
            $newdata = array('result' => 'fail', 'response' => array('code'=>400,'message'=>'5ZdwBQbI06WZq0ikrhCc97Kz9g0VxVhbTOSSj8EVoE So Can`t User Logout'));
            $this->response($newdata);
        }
        $user_id = $check_company_id['user_id'];
        $data = array(
            'user_login_token' => ''
        );
        $this->db->where('user_id', $user_id);
        $this->db->update('user_api_details', $data);
        $validate = 'success';
        $html_response['code'] = 200;
        $html_response['message'] = 'User logout successfully';
        $newdata = array('result' => $validate, 'response' => $html_response);
        $this->response($newdata);
    }

    function RandomString($length) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randstring = '';
        for ($i = 0; $i < $length; $i++) {
            $randstring .= $characters[rand(0, strlen($characters))];
        }
        return $randstring;
    }

    /* :: end login and logout :: */

    /* :: start forgot password && reset password :: */

    function forgot_password() {
        $newdata = array();
        if (empty($_REQUEST)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => "Data not found"));
            $this->response($newdata);
        }
        
        $email = trim(htmlspecialchars($_REQUEST['email'])) ? trim(htmlspecialchars($_REQUEST['email'])) : '';
        $user_access_token = $this->accesstoken;
        if(empty($email)) 
        {
            $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => "Please enter Email"));
            $this->response($newdata);
        }
        $sql_check_company_details = 'SELECT r.company_id,d.status FROM user_roles r JOIN user_api_details d ON r.user_id = d.user_id WHERE d.user_access_token = "'.$user_access_token.'" and d.status="enable"';
        $check_company_id = $this->db->query($sql_check_company_details)->row_array();
        if(empty($check_company_id))
        {
            $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => "API Status is disabled so can`t use this api"));
            $this->response($newdata);
        }
        $company_id = $check_company_id['company_id'];
        
        $sql_check_company_password = 'SELECT u.id, u.email, u.hashed_password, ur.user_id as ur_id, ur.role_id, ur.company_id FROM users AS u LEFT JOIN user_roles AS ur ON u.id = ur.user_id WHERE u.status = "active" AND u.email = "' . $email . '" AND ur.company_id = "' . $company_id . '"';
        $check_user_validation = $this->db->query($sql_check_company_password)->row_array();
        if(empty($check_user_validation)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => "User is not validate"));
            $this->response($newdata);
        }
        if (count($check_user_validation) == 0) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => "Your Email or Token is wrong"));
            $this->response($newdata);
        }
        $check_user_status=$this->db->query('select * from user_api_details where user_id="'.$check_user_validation['ur_id'].'" and status="enable"')->row_array();
        if(empty($check_user_status))
        {
            $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => "API Status is disabled so can`t use this api"));
            $this->response($newdata);
        }
        $user_exist = User::validate_company_user($email, $company_id);
        if (empty($user_exist)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => "User does not exit"));
            $this->response($newdata);
        }
        $usertrue = "1";
        $user = User::find($user_exist->id);
        $data["core_settings"] = Setting::first();
        $company_detail = CompanyDetails::find('all', array('conditions' => array('company_id=?', $company_id)));
        $company = Company::find('all', array('conditions' => array('id=?', $company_id)));

        $logo = $data["core_settings"]->logo;
        if (!empty($company_detail) && !empty($company_detail[0]->logo)) {
            $logo = $company_detail[0]->logo;
        }
        $token = md5(uniqid(rand(), true));
        $today = date('Y-m-d H:i:s');

        //$reset_link = site_url().'api/apilogin/token/'.$user->id.'/'.$company_id.'/'.$token;
        $reset_link = site_url() . 'resetpass/token/' . $user->id . '/' . $company_id . '/' . $token;
        $timestamp = strtotime('+2 day', strtotime($today));

        $contact_name = trim($user->firstname . " " . $user->lastname);

        $this->load->library('parser');
        $this->load->helper('file');

        $user_email = $user->email;
        $this->db->where('email', $user_email);
        $this->db->delete('pw_reset');

        $insert_arr = array(
            'email' => $user_email,
            'timestamp' => $timestamp,
            'token' => $token,
            'user' => $usertrue
        );
        $this->db->insert('pw_reset', $insert_arr);
        $this->load->library('email');
        $this->email->from($this->settings->from_email_id,$this->settings->from_email_name);
        $this->email->to($user->email);

        $this->email->subject($data["core_settings"]->pw_reset_link_mail_subject);
        $parse_data = array(
            'link' => $reset_link,
            'company' => $company[0]->name,
            'client_contact' => $contact_name,
            'logo' => '<img src="' . site_url() . $logo . '" alt="' . $data["core_settings"]->company . '"/>',
            'invoice_logo' => '<img src="' . site_url() . $logo . '" alt="' . $data["core_settings"]->company . '"/>'
        );
        $email = read_file('./application/views/' . $data["core_settings"]->template . '/templates/email_pw_reset_link.html');
        $message = $this->parser->parse_string($email, $parse_data);
        $this->email->message($message);
        if ($this->email->send()) {
            $newdata = array('result' => 'success', 'response' => array('code' => 200,'token'=>$token, 'message' => "Change Password token sent successfully"));
            $this->response($newdata);
        }
        $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => "Error in Password reset"));
        $this->response($newdata);
    }

    /* Reset Password */

    function reset_password() {
        $newdata = array();
        if (empty($_REQUEST)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => "Data not found"));
            $this->response($newdata);
        }
        $email = trim(htmlspecialchars($_REQUEST['email'])) ? trim(htmlspecialchars($_REQUEST['email'])) : '';
        $token = trim(htmlspecialchars($_REQUEST['token'])) ? trim(htmlspecialchars($_REQUEST['token'])) : '';
        $user_access_token = $this->accesstoken;
        if(empty($email)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => "Please enter Email"));
            $this->response($newdata);
        }
        if(empty($token)) 
        {
            $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => "Please enter token"));
            $this->response($newdata);
        }
        $sql_check_company_details = 'SELECT r.company_id,d.status FROM user_roles r JOIN user_api_details d ON r.user_id = d.user_id WHERE d.user_access_token = "'.$user_access_token.'" and d.status="enable"';
        $check_company_id = $this->db->query($sql_check_company_details)->row_array();
        if(empty($check_company_id))
        {
            $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => "API Status is disabled so can`t use this api"));
            $this->response($newdata);
        }
        $company_id = $check_company_id['company_id'];
        
        $sql_check_company_password = 'SELECT u.id, u.email, u.hashed_password, ur.user_id as ur_id, ur.role_id, ur.company_id FROM users AS u LEFT JOIN user_roles AS ur ON u.id = ur.user_id WHERE u.status = "active" AND u.email = "' . $email . '" AND ur.company_id = "' . $company_id . '"';
        $check_user_validation = $this->db->query($sql_check_company_password)->row_array();
        if(empty($check_user_validation)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => "User is not validate"));
            $this->response($newdata);
        }
        
        $check_user_status=$this->db->query('select * from user_api_details where user_id="'.$check_user_validation['ur_id'].'" and status="enable"')->row_array();
        if(empty($check_user_status))
        {
            //echo "GDg";exit;
            $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => "API Status is disabled so can`t use this api"));
            $this->response($newdata);
        }
        $token_query = "SELECT * FROM `pw_reset` WHERE email = '" . $email . "' AND token = '" . $token . "'";
        $result = $this->db->query($token_query)->row_array();
        $user = User::find_by_email($result['email']);
        if (empty($user)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => "User does not exit"));
            $this->response($newdata);
        }

        $password = trim(htmlspecialchars($_REQUEST['password'])) ? trim(htmlspecialchars($_REQUEST['password'])) : '';
        if(empty($password))
        {
            $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => "Please Enter Password for password reset"));
            $this->response($newdata);
        }
        $user_id = trim(htmlspecialchars($check_user_validation['id']));
        $user->set_password($password);
        $this->db->set('hashed_password', $user->hashed_password);
        $this->db->where('id', $user_id);
        $this->db->update('users');
        //$newdata=array('result'=>'success','response'=>array('user_id'=>$user_id,'company_id'=>$company_id,'message'=>"Password reset successfully"));
        $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => "Password reset successfully"));
        $this->response($newdata);
    }

    function token() {
        // $user_id = $this->uri->segment(3);
        $newdata = array();
        $company_id = $this->uri->segment(5);
        $invite_token = $this->uri->segment(6);
        $token_query = "SELECT * FROM `pw_reset` WHERE token = '" . $invite_token . "' AND timestamp >= " . time();
        $result = $this->db->query($token_query)->row_array();
        if (count($result) > 0) {
            $email = $result['email'];
            $dataArr = array(
                'user_id' => $user_id,
                'company_id' => $company_id,
                'email' => $email,
                'token' => $invite_token
            );
            $newdata = array('result' => 'success', 'response' => $dataArr);
            $this->response($newdata);
        }
        //else{
        $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => "reset_password_token_expired"));
        $this->response($newdata);
        //}
    }

    /* :: end forgot password && reset password :: */
}

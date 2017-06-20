<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Subcontractors extends MY_Controller {

    public $company_id;

    function __construct() {
        parent::__construct();
        $access = FALSE;
        if (!$this->user) {
            if ($this->cid) {
                redirect($this->cid);
            } else {
                redirect('login');
            }
        }
        #set default value if found null from session
        $this->company_id = 0;
        $this->company_id = $this->sessionArr['company_id'];
        $this->load->database();
        $this->settings = Setting::first();
    }

    function index() {
        $company_id = $this->company_id;
        $this->view_data['sub_contractor_title'] = 'Subcontractors';
        $this->view_data['owner_id'] = $this->sessionArr['user_id'];
        $this->view_data['company_id'] = $this->sessionArr['company_id'];
        $this->view_data['subcontractors'] = null;
        $get_company = Company::find_by_id($this->sessionArr['company_id']);
        $this->view_data['name_of_company'] = $get_company->name;
        if (!empty($company_id)) {
            $client_companies = $this->db->query('select * from client_companies cc join client_assign_companies ca on ca.client_id = cc.client_id where ca.user_id="' . $this->sessionArr['user_id'] . '"')->result_array();
            if (!empty($client_companies)) {
                $this->view_data['client_companies'] = $client_companies;
            } else {
                $this->view_data['client_companies'] = '';
            }
            $subcontractor_query = $this->db->select('*')->from('users')->join('user_roles', 'user_roles.user_id = users.id')->where("admin = '0' AND users.status IN ('active', 'inactive') AND user_roles.role_id = 4 AND company_id = " . $company_id)->get();
            if ($subcontractor_query->num_rows() > 0) {
                $this->view_data['subcontractors'] = $subcontractor_query->result_object();
            }
        }
        $this->content_view = 'subcontractors/all';
    }

    function email_check() {
        $message = '';
        // allow only Ajax request    
        if ($this->input->is_ajax_request()) {
            // grab the email value from the post variable.
            $email = $this->input->get('email');
            $company_id = $this->company_id;

            // $user_query = $this->db->select('*')->from('users')->where('email', $email)->get();
            $user_query = $this->db->select('*')->from('users')->join('user_roles', 'user_roles.user_id = users.id')->where("email = '" . $email . "' AND user_roles.company_id = " . $company_id)->get();
            $message = ( $user_query->num_rows() > 0 ) ? 'The email is already taken, choose another one' : '';

            if ($user_query->num_rows() > 0) {
                $user_query_del = $this->db->select('*')->from('users')->join('user_roles', 'user_roles.user_id = users.id')->where("email = '" . $email . "' AND status = 'deleted' AND user_roles.company_id = " . $company_id)->get();
                if ($user_query_del->num_rows() > 0) {
                    $message = 'This email is already registered, Do you want to restore ?';
                } else {
                    $message = 'This email is already taken, choose another one';
                }
                //echo json_encode(array('message' => $message));
            }
        }
        echo json_encode(array('message' => $message));
        die();
    }

    function company_email_check() {
        $message = '';
        // allow only Ajax request    
        if ($this->input->is_ajax_request()) {
            // grab the email value from the post variable.
            $email = $this->input->get('email');
            $owner_id = $this->input->get('owner_id');
            $company_id = $this->company_id;

            // $user_query = $this->db->select('*')->from('users')->where('email', $email)->get();
            $user_query = $this->db->select('*')->from('client_companies cc')->join('client_assign_companies ca', 'cc.client_id=ca.client_id')->where("cc.email = '" . $email . "'")->where("ca.user_id = '" . $owner_id . "'")->get();
            $message = ( $user_query->num_rows() > 0 ) ? 'The email is already taken, choose another one' : '';

            if ($user_query->num_rows() > 0) {
                $user_query_del = $this->db->select('*')->from('client_companies cc')->join('client_assign_companies ca', 'cc.client_id=ca.client_id')->where("cc.email = '" . $email . "'")->where("ca.user_id = '" . $owner_id . "'")->get();
                if ($user_query_del->num_rows() > 0) {
                    $message = 'This email is already registered, Do you want to restore ?';
                } else {
                    $message = 'This email is already taken, choose another one';
                }
                //echo json_encode(array('message' => $message));		
            }
        }
        echo json_encode(array('message' => $message));
        die();
    }

    function create($company_id = FALSE) {
        $company_id = $company_id ? $company_id : $this->sessionArr['company_id'];
        $get_company = Company::find_by_id($this->sessionArr['company_id']);
        $this->view_data['name_of_company'] = $get_company->name;
        /* new flow */
        $client_companies = $this->db->query('select * from client_companies cc join client_assign_companies ca on ca.client_id = cc.client_id where ca.user_id="' . $this->sessionArr['user_id'] . '" ')->result_array();
        if (!empty($client_companies)) {
            $this->view_data['client_companies'] = $client_companies;
        } else {
            $this->view_data['client_companies'] = '';
        }
        //echo "<pre>";print_r($this->sessionArr['company_id']);exit;
        $this->view_data['company_id'] = $this->sessionArr['company_id'];
        $this->view_data['owner_id'] = $this->sessionArr['user_id'];
        if ($_POST) {
            $company_id = $this->company_id;

            $email_exist = $this->db->select('*')->from('users')->join('user_roles', 'user_roles.user_id = users.id')->where("email = '" . $_POST['email'] . "' AND user_roles.company_id = " . $company_id)->get()->num_rows();
            if ($email_exist == 0) {


                $role_id = $_POST['role_id'];

                unset($_POST['role_id']);
                unset($_POST['send']);

                $modules = Module::find('all', array('order' => 'sort asc', 'conditions' => array('type = ?', 'sub-contractor')));
                $access = array();
                foreach ($modules as $key => $value) {
                    $access[] = $value->id;
                }
                $_POST["access"] = implode(",", $access);

                $_POST['status'] = 'inactive';
                $_POST['admin'] = '0';
                $client_arr = array(
                    'firstname' => $this->input->post('firstname'),
                    'lastname' => $this->input->post('lastname'),
                    'email' => $this->input->post('email'),
                    'access' => $_POST["access"],
                    'status' => $_POST['status'],
                    'admin' => $_POST['admin']
                );
                $_POST = array_map('htmlspecialchars', $_POST);
                $user = User::create($client_arr);
                // $client->password = $client->set_password($_POST['password']);
                $user->save();
                $insert_id = $user->id;
                if ($_POST['sub_c_id'] == 'other') {
                    $sub_company = strtolower(trim($_POST['c_name']));
                } else {
                    $sub_company = strtolower(trim($_POST['sub_c_id']));
                }

                $client_companies_arr = array('client_id' => $insert_id, 'sub_company' => $sub_company, 'created_at' => date('Y-m-d H:i:s'));
                $insert_client_companies = $this->db->insert('client_companies', $client_companies_arr);
                $client_company_id = $this->db->insert_id();
                if (!empty($insert_client_companies)) {
                    $client_assign_arr = array('sub_c_id' => $client_company_id, 'user_id' => $this->sessionArr['user_id'], 'client_id' => $insert_id, 'created_at' => date('Y-m-d H:i:s'));
                    //echo "<pre>";print_r($client_assign_arr);exit;
                    $insert_client_assign_arr = $this->db->insert('client_assign_companies', $client_assign_arr);
                }

                $company_details = array('user_id' => $insert_id, 'role_id' => $role_id, 'company_id' => $company_id, 'created_at' => date('Y-m-d H:i:s'));
                $company_details = array_map('htmlspecialchars', $company_details);
                $user_role = UserRole::create($company_details);
                // $client->password = $client->set_password($_POST['password']);
                $user_role->save();
                $roles_insert_id = $user_role->id;

                if (!$user) {
                    $this->session->set_flashdata('message', 'error:' . $this->lang->line('messages_subcontractor_add_error'));
                } else {
                    // $this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_subcontractor_add_success'));

                    $unique_key = md5(uniqid(rand(), true));
                    $today = date('Y-m-d H:i:s');
                    $invite_token_expiry = strtotime('+2 day', strtotime($today));
                    $invite_url = site_url() . 'invitation/accept/' . $roles_insert_id . '/' . $company_id . '/' . $unique_key;

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

                    /* $config['protocol']    = 'smtp';
                      $config['smtp_host']    = 'ssl://smtp.gmail.com';
                      $config['smtp_port']    = '465';
                      $config['smtp_timeout'] = '7';
                      $config['smtp_user']    = 'emailtesterone@gmail.com';
                      $config['smtp_pass']    = 'kgn@123456';
                      $config['charset']    = 'utf-8';
                      $config['newline']    = "\r\n";
                      $config['mailtype'] = 'html';
                      $config['validation'] = TRUE; // bool whether to validate email or not

                      $this->email->initialize($config);

                      $this->load->library('email');
                      $this->email->from('emailtesterone@gmail.com', trim($_POST['firstname'].' '.$_POST['lastname']));
                      $this->email->to($_POST["email"]);
                      $this->email->subject('Spera Invitation');
                      $this->email->message('Click on <a href="'.$invite_url.'">Spera Invitation Link</a> to complete signup and access your profile.'); */

                    $created_msg = $this->lang->line('messages_subcontractor_add_success');
                    $mail_sent = null;
                    // if($this->email->send()) {
                    if ($this->invite_mail($invite_url, $_POST)) {
                        $mail_sent = 'Invitation mail sent.';
                    }
                    $this->session->set_flashdata('message', 'success:' . $created_msg . ' ' . $mail_sent);
                }
            } else {

                $email_exist = $this->db->select('*')->from('users')->join('user_roles', 'user_roles.user_id = users.id')->where("email = '" . $_POST['email'] . "' AND status = 'deleted' AND user_roles.company_id = " . $company_id)->get()->row_object();
                $user = User::find($email_exist->user_id);

                if (!empty($user)) {
                    $_POST['status'] = 'inactive';
                    $_POST['hashed_password'] = null;
                    unset($_POST['send']);
                    $_POST = array_map('htmlspecialchars', $_POST);
                    unset($_POST['role_id']);
                    $client_arr = array(
                        'status' => $_POST['status'],
                        'hashed_password' => $_POST['hashed_password']
                    );
                    $user->update_attributes($client_arr);


                    $roles_insert_id = $email_exist->role_id;

                    $company_id = $email_exist->company_id;

                    $user_data = $this->db->select('*,user_roles.id as user_roles_id')->from('users')->join('user_roles', 'user_roles.user_id = users.id')->where("admin = '0' AND role_id = " . $roles_insert_id . " AND user_roles.company_id=" . $company_id . " AND users.id=" . $email_exist->user_id)->get()->row_array();
                    $roles_user_id = $user_data['user_roles_id'];

                    $unique_key = md5(uniqid(rand(), true));
                    $today = date('Y-m-d H:i:s');
                    $invite_token_expiry = strtotime('+2 day', strtotime($today));
                    $invite_url = site_url() . 'invitation/accept/' . $roles_user_id . '/' . $company_id . '/' . $unique_key;

                    $user_token = $this->db->select('*')->from('user_meta')->where("user_roles_id = " . $roles_user_id . " AND meta_key = 'invite_token_" . $company_id . "'")->get()->num_rows();
                    $user_meta = array(
                        'user_roles_id' => $roles_user_id,
                        'meta_key' => 'invite_token_' . $company_id,
                        'meta_value' => $unique_key
                    );
                    if ($user_token == 0) {
                        $this->db->insert('user_meta', $user_meta);
                    } else {
                        $this->db->where('user_roles_id', $roles_user_id);
                        $this->db->where('meta_key', 'invite_token_' . $company_id);
                        $this->db->update('user_meta', $user_meta);
                    }

                    $user_token_expiry = $this->db->select('*')->from('user_meta')->where("user_roles_id = " . $roles_user_id . " AND meta_key = 'invite_token_expiry'")->get()->num_rows();
                    $user_meta1 = array(
                        'user_roles_id' => $roles_user_id,
                        'meta_key' => 'invite_token_expiry',
                        'meta_value' => $invite_token_expiry
                    );
                    if ($user_token_expiry == 0) {
                        $this->db->insert('user_meta', $user_meta1);
                    } else {
                        $this->db->where('user_roles_id', $roles_user_id);
                        $this->db->where('meta_key', 'invite_token_expiry');
                        $this->db->update('user_meta', $user_meta1);
                    }

                    $user_token_expiry_status = $this->db->select('*')->from('user_meta')->where("user_roles_id = " . $roles_user_id . " AND meta_key = 'invite_token_expiry_status'")->get()->num_rows();
                    $user_meta2 = array(
                        'user_roles_id' => $roles_user_id,
                        'meta_key' => 'invite_token_expiry_status',
                        'meta_value' => ''
                    );
                    if ($user_token_expiry == 0) {
                        $this->db->insert('user_meta', $user_meta2);
                    } else {
                        $this->db->where('user_roles_id', $roles_user_id);
                        $this->db->where('meta_key', 'invite_token_expiry_status');
                        $this->db->update('user_meta', $user_meta2);
                    }

                    $created_msg = $this->lang->line('messages_subcontractor_add_success');
                    $mail_sent = null;
                    // if($this->email->send()) {
                    if ($this->invite_mail($invite_url, $_POST)) {
                        $mail_sent = 'Invitation mail sent.';
                    }
                    $this->session->set_flashdata('message', 'success:' . $created_msg . ' ' . $mail_sent);
                } else {
                    $this->session->set_flashdata('message', 'error:' . $this->lang->line('messages_email_already_taken'));
                }
            }
            redirect(base_url() . 'subcontractors');
        } else {
            // $this->view_data['users'] = User::find('all',array('conditions' => array('inactive=?','0')));
            // $this->view_data['modules'] = Module::find('all', array('order' => 'sort asc', 'conditions' => array('type = ?', 'client')));
            // $this->view_data['next_reference'] = Client::last();
            $this->theme_view = 'modal';
            $this->view_data['title'] = $this->lang->line('application_create_subcontractor');
            $this->view_data['form_action'] = base_url() . 'subcontractors/create/';
            $this->content_view = 'subcontractors/_subcontractor';
        }
    }

    function update($id = FALSE, $getview = FALSE) {
        $get_company = Company::find_by_id($this->sessionArr['company_id']);
        $this->view_data['name_of_company'] = $get_company->name;
        /* new flow */
        $client_companies = $this->db->query('select * from client_companies cc join client_assign_companies ca on ca.client_id = cc.client_id where ca.user_id="' . $this->sessionArr['user_id'] . '" ')->result_array();
        if (!empty($client_companies)) {
            $this->view_data['client_companies'] = $client_companies;
        } else {
            $this->view_data['client_companies'] = '';
        }
        //echo "<pre>";print_r($this->sessionArr['company_id']);exit;
        $this->view_data['company_id'] = $this->sessionArr['company_id'];
        //$this->view_data['user_id'] = $this->sessionArr['user_id'];
        $get_client_assign_company = $this->db->query('select cc.* from client_assign_companies ca join client_companies cc on ca.client_id=cc.client_id where ca.client_id="' . $id . '"')->row_array();
        if (!empty($get_client_assign_company)) {
            $this->view_data['get_client_assign_company'] = $get_client_assign_company['sub_company'];
        } else {
            $this->view_data['get_client_assign_company'] = $get_company->name;
        }
        $this->view_data['owner_id'] = $this->sessionArr['user_id'];
        if ($_POST) {

            $post_id = $_POST['id'];
            //echo "<pre>";print_r($_POST);exit;
            $user = User::find_by_id($post_id);

            unset($_POST['send']);
            /* unset($_POST['userfile']);
              unset($_POST['file-name']); */
            if (empty($_POST["password"])) {
                unset($_POST['password']);
            } else {
                $salt = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
                $hash = hash('sha256', $salt . $_POST['password']);
                $_POST['password'] = $salt . $hash;
            }
            if (!empty($_POST["access"])) {
                $_POST["access"] = implode(",", $_POST["access"]);
            }

            if (isset($_POST['view'])) {
                $view = $_POST['view'];
                unset($_POST['view']);
            }
            $_POST = array_map('htmlspecialchars', $_POST);

            $client_arr = array(
                'firstname' => $this->input->post('firstname'),
                'lastname' => $this->input->post('lastname'),
                'hashed_password' => $_POST['password']
            );
            $user->update_attributes($client_arr);
            if ($_POST['sub_c_id'] == 'other') {
                $sub_company = strtolower(trim($_POST['c_name']));
            } else {
                $sub_company = strtolower(trim($_POST['sub_c_id']));
            }

            $get_client_assign_company_dc = $this->db->query('select * from client_assign_companies ca join client_companies cc on ca.client_id=cc.client_id where ca.client_id="' . $post_id . '"')->num_rows();
            if ($get_client_assign_company_dc == 0) {
                $client_companies_arr_dc = array('client_id' => $post_id, 'sub_company' => $sub_company, 'created_at' => date('Y-m-d H:i:s'));
                $insert_client_companies = $this->db->insert('client_companies', $client_companies_arr_dc);
                $client_company_id = $this->db->insert_id();
                if (!empty($insert_client_companies)) {
                    $client_assign_arr_dc = array('sub_c_id' => $client_company_id, 'user_id' => $this->sessionArr['user_id'], 'client_id' => $post_id, 'created_at' => date('Y-m-d H:i:s'));
                    //echo "<pre>";print_r($client_assign_arr);exit;
                    $insert_client_assign_arr = $this->db->insert('client_assign_companies', $client_assign_arr_dc);
                }
            } else {
                $client_companies_arr = array('client_id' => $post_id, 'sub_company' => $sub_company, 'updated_at' => date('Y-m-d H:i:s'));
                $this->db->where('client_id', $post_id);
                if ($this->db->update('client_companies', $client_companies_arr)) {
                    $client_assign_arr = array('sub_c_id' => $get_client_assign_company['id'], 'user_id' => $this->sessionArr['user_id'], 'client_id' => $post_id, 'updated_at' => date('Y-m-d H:i:s'));
                    $update_client_assign_arr = $this->db->where('client_id', $post_id)->update('client_assign_companies', $client_assign_arr);
                }
            }
            if (!$user) {
                $this->session->set_flashdata('message', 'error:' . $this->lang->line('messages_save_subcontractor_error'));
            } else {
                $this->session->set_flashdata('message', 'success:' . $this->lang->line('messages_save_subcontractor_success'));
            }

            redirect(base_url() . 'subcontractors/view/' . $user->id);
        } else {
            // $this->view_data['user'] = User::find_by_sql("SELECT * FROM `users` WHERE admin = '0' AND STATUS = 'active' AND id=".$id);
            $company_id = $this->company_id;
            $this->view_data['user'] = $this->db->select('*')->from('users')->join('user_roles', 'user_roles.user_id = users.id')->where("admin = '0' AND status = 'active' AND company_id=" . $company_id . " AND users.id=" . $id)->get()->row_object();
            // $this->view_data['modules'] = Module::find('all', array('order' => 'sort asc', 'conditions' => array('type = ?', 'client')));
            if ($getview == "view") {
                $this->view_data['view'] = "true";
            }
            $this->theme_view = 'modal';
            $this->view_data['title'] = $this->lang->line('application_edit_subcontractor');
            $this->view_data['form_action'] = base_url() . 'subcontractors/update';
            $this->content_view = 'subcontractors/_subcontractor';
        }
    }

    function delete($id = FALSE) {
        $user = User::find($id);
        $user->status = 'deleted';
        $user->save();
        $this->content_view = 'subcontractors/all';
        if (!$user) {
            $this->session->set_flashdata('message', 'error:' . $this->lang->line('messages_delete_a_user_error'));
        } else {
            $this->session->set_flashdata('message', 'success:' . $this->lang->line('messages_delete_user_success'));
        }
        redirect(base_url() . 'subcontractors');
    }

    function view($id = FALSE) {
        // $this->view_data['company'] = Company::find($id);
        $company_id = $this->sessionArr['company_id'];
        if (!empty($company_id)) {
            $user_query = $this->db->select('*')->from('users')->join('user_roles', 'user_roles.user_id = users.id')->where("admin = '0' AND users.id=" . $id . " AND company_id=" . $company_id)->get();
            // $this->db->select('*')->from('users')->where("admin = '0' AND STATUS = 'active' AND id=".$id);
            // $user_query = $this->db->get();
            if ($user_query->num_rows() > 0) {
                $user_data = $user_query->row_object();
                $user_role = $this->db->select('*')->from('roles')->where("role_id = " . $user_data->role_id)->get()->row_object();
                $user_data->role_name = $user_role->roles;
                $this->view_data['user'] = $user_data;
                $this->content_view = 'subcontractors/view';
            } else {
                redirect(base_url() . 'subcontractors');
            }
        } else {
            redirect(base_url() . 'subcontractors');
        }
    }

    function invite($id = FALSE) {
        $company_id = $this->company_id;
        $user_data = $this->db->select('*,user_roles.id as user_roles_id')->from('users')->join('user_roles', 'user_roles.user_id = users.id')->where("admin = '0' AND role_id = " . $_REQUEST['role'] . " AND user_roles.company_id=" . $company_id . " AND users.id=" . $id)->get()->row_array();

        $unique_key = md5(uniqid(rand(), true));
        $today = date('Y-m-d H:i:s');
        $invite_token_expiry = strtotime('+2 day', strtotime($today));
        $invite_url = site_url() . 'invitation/accept/' . $user_data['user_roles_id'] . '/' . $company_id . '/' . $unique_key;

        $roles_insert_id = $user_data['user_roles_id'];

        /* $data = array(
          'title' => $title,
          'name' => $name,
          'date' => $date
          );
          $this->db->where('user_roles_id', $id);
          $this->db->update('mytable', $data); */

        $user_token = $this->db->select('*')->from('user_meta')->where("user_roles_id = " . $roles_insert_id . " AND meta_key = 'invite_token_" . $company_id . "'")->get()->num_rows();
        $user_meta = array(
            'user_roles_id' => $roles_insert_id,
            'meta_key' => 'invite_token_' . $company_id,
            'meta_value' => $unique_key
        );
        if ($user_token == 0) {
            $this->db->insert('user_meta', $user_meta);
        } else {
            $this->db->where('user_roles_id', $roles_insert_id);
            $this->db->where('meta_key', 'invite_token_' . $company_id);
            $this->db->update('user_meta', $user_meta);
        }

        $user_token_expiry = $this->db->select('*')->from('user_meta')->where("user_roles_id = " . $roles_insert_id . " AND meta_key = 'invite_token_expiry'")->get()->num_rows();
        $user_meta1 = array(
            'user_roles_id' => $roles_insert_id,
            'meta_key' => 'invite_token_expiry',
            'meta_value' => $invite_token_expiry
        );
        if ($user_token_expiry == 0) {
            $this->db->insert('user_meta', $user_meta1);
        } else {
            $this->db->where('user_roles_id', $roles_insert_id);
            $this->db->where('meta_key', 'invite_token_expiry');
            $this->db->update('user_meta', $user_meta1);
        }

        $user_token_expiry_status = $this->db->select('*')->from('user_meta')->where("user_roles_id = " . $roles_insert_id . " AND meta_key = 'invite_token_expiry_status'")->get()->num_rows();
        $user_meta2 = array(
            'user_roles_id' => $roles_insert_id,
            'meta_key' => 'invite_token_expiry_status',
            'meta_value' => ''
        );
        if ($user_token_expiry == 0) {
            $this->db->insert('user_meta', $user_meta2);
        } else {
            $this->db->where('user_roles_id', $roles_insert_id);
            $this->db->where('meta_key', 'invite_token_expiry_status');
            $this->db->update('user_meta', $user_meta2);
        }

        if ($this->invite_mail($invite_url, $user_data)) {
            $result['mail_sent'] = 'Invitation mail sent.';
            $result['error'] = 0;
        } else {
            $result['mail_sent'] = 'There may some issue in sending invitation mail.';
            $result['error'] = 1;
        }
        //$this->session->set_flashdata('message', $mail_sent);
        //redirect('subcontractors');
        echo json_encode($result);
        die();
    }

    function invite_mail($invite_url, $data) {
        $data['company_id'] = $this->company_id;
        $user_data = $this->db->select('users.*')->from('users')->join('user_roles', 'user_roles.user_id = users.id')->where("user_roles.role_id = 2 AND user_roles.company_id=" . $data['company_id'])->get()->row_array();
//		$config['protocol']    = 'smtp';
//		$config['smtp_host']    = 'ssl://smtp.gmail.com';
//		$config['smtp_port']    = '465';
//		$config['smtp_timeout'] = '7';
//		$config['smtp_user']    = 'emailtesterone@gmail.com';
//		$config['smtp_pass']    = 'kgn@123456';
//		$config['charset']    = 'utf-8';
//		$config['newline']    = "\r\n";
//		$config['mailtype'] = 'html';
//		$config['validation'] = TRUE; // bool whether to validate email or not      
//
//		$this->email->initialize($config);

        $this->load->library('email');
        //$this->email->from('emailtesterone@gmail.com', trim($user_data['firstname'].' '.$user_data['lastname']));
        $this->email->from($this->settings->from_email_id, $this->settings->from_email_name);
        $this->email->to($data["email"]);
        $this->email->subject('Spera Invitation');
        $this->email->message('Click on <a href="' . $invite_url . '">Spera Invitation Link</a> to complete signup and access your profile.');
        return $this->email->send();
    }

    function check_user_restriction() {
        //echo "<pre>";print_r($_REQUEST);exit;
        if (!empty($_REQUEST)) {
            $user_id = $_REQUEST['owner_id'];
            $company_id = $_REQUEST['company_id'];
            $check_package_query = "Select * from propay_user_subscription where user_id='" . $user_id . "' AND CURDATE() between start_date and end_date and payment_detail_id !=0 ";
            $check_package_current_date = $this->db->query($check_package_query)->row_array();
            //echo "<pre>";print_r($check_package_current_date);exit;
            if (!empty($check_package_current_date)) {
                $check_users_company = $this->db->query('Select count(*) as total from user_roles r join users u on r.user_id=u.id where r.role_id != 2 and r.company_id="' . $company_id . '" and u.status != "deleted"')->row_array();
                if (empty($check_users_company)) {
                    $total_users = 0;
                } else {
                    $total_users = $check_users_company['total'];
                }
                $package_id = $check_package_current_date['package_id'];
                $user_limit_arr = Package::find_by_id($package_id);
                $user_limit = $user_limit_arr->user_limit;
                if ($package_id == 3) {
                    if ($total_users >= $user_limit) {
                        $this->session->set_flashdata('message', 'warning:' . $this->lang->line('application_user_restriction'));
                        echo "false";
                        exit;
                    } else {
                        echo "true";
                        exit;
                    }
                } elseif ($package_id == 2) {
                    if ($total_users >= $user_limit) {
                        $this->session->set_flashdata('message', 'warning:' . $this->lang->line('application_user_restriction'));
                        echo "false";
                        exit;
                    } else {
                        echo "true";
                        exit;
                    }
                } elseif ($package_id == 1) {
                    if ($total_users >= $user_limit) {
                        $this->session->set_flashdata('message', 'warning:' . $this->lang->line('application_user_restriction'));
                        echo "false";
                        exit;
                    } else {
                        echo "true";
                        exit;
                    }
                }
                //echo "<pre>";print_r($user_limit_arr->user_limit);exit;
            } else {
                $t_check_package_query = "Select * from propay_user_subscription where user_id='" . $user_id . "' AND CURDATE() between start_date and end_date and payment_detail_id =0 ";
                $t_check_package_current_date = $this->db->query($t_check_package_query)->row_array();
                if (!empty($t_check_package_current_date)) {
                    $t_check_users_company = $this->db->query('Select count(*) as total from user_roles r join users u on r.user_id=u.id where r.role_id != 2 and r.company_id="' . $company_id . '" and u.status != "deleted"')->row_array();
                    if (empty($t_check_users_company)) {
                        $total_users = 0;
                    } else {
                        $total_users = $t_check_users_company['total'];
                    }
                    $package_id = $t_check_package_current_date['package_id'];
                    $user_limit_arr = Package::find_by_id($package_id);
                    $user_limit = $user_limit_arr->user_limit;

                    if ($package_id == 3) {
                        if ($total_users >= $user_limit) {
                            $this->session->set_flashdata('message', 'warning:' . $this->lang->line('application_user_restriction'));
                            echo "false";
                            exit;
                        } else {
                            echo "true";
                            exit;
                        }
                    } elseif ($package_id == 2) {
                        if ($total_users >= $user_limit) {
                            $this->session->set_flashdata('message', 'warning:' . $this->lang->line('application_user_restriction'));
                            echo "false";
                            exit;
                        } else {
                            echo "true";
                            exit;
                        }
                    } elseif ($package_id == 1) {
                        if ($total_users >= $user_limit) {
                            $this->session->set_flashdata('message', 'warning:' . $this->lang->line('application_user_restriction'));
                            echo "false";
                            exit;
                        } else {
                            echo "true";
                            exit;
                        }
                    }
                } else {
                    echo "error";
                    exit;
                }
            }
        }
    }

    function search_client_companies() {
        if (!empty($_REQUEST)) {
            $company_id = $this->sessionArr['company_id'];
            $company = $_REQUEST['sub_c_id_all'];
            $owner_id = $_REQUEST['owner_id'];
            $get_company = Company::find_by_id($this->sessionArr['company_id']);
            $name_of_company = $get_company->name;
            if (!empty($company)) {
                $this->db->select('cc.*');
                $this->db->from('client_companies cc');
                $this->db->join('client_assign_companies ca', 'cc.client_id=ca.client_id');
                $this->db->join('user_roles r', 'cc.client_id=r.user_id');
                $this->db->where('ca.user_id', $owner_id);
                $this->db->where('r.role_id', 4);
                $this->db->where('r.company_id', $company_id);
                $this->db->like('cc.sub_company', strtolower(trim($company)));
                $get_all_company = $this->db->get()->result_array();
                //echo "<pre>";print_r($get_all_company);exit;
                $newArr = array();
                $i = 0;
                foreach ($get_all_company as $key => $value) {
                    $client_id = $value['client_id'];
                    //echo 'Select * from users where id="'.$client_id.'" AND status !="deleted"';exit;
                    $user_detail = $this->db->query('Select * from users where id="' . $client_id . '" AND status !="deleted"')->row();
                    //echo "<pre>";print_r($user_detail);
                    if (!empty($user_detail)) {
                        $newArr[$i]['company_name'] = $value['sub_company'];
                        $newArr[$i]['id'] = $user_detail->id;
                        $newArr[$i]['sub_id'] = $value['id'];
                        $newArr[$i]['firstname'] = $user_detail->firstname;
                        $newArr[$i]['lastname'] = $user_detail->lastname;
                        $newArr[$i]['email'] = $user_detail->email;
                        $newArr[$i]['status'] = $user_detail->status;
                    }
                    $i++;
                }
                //echo "<pre>";print_r($newArr);exit;
                $newhtml = "";
                if (!empty($newArr)) {
                    $user_cnt = 0;
                    $count_elements = count($newArr);
                    foreach ($newArr as $newAr) {
                        $user_cnt++;
                        $content1 = "<a class='btn btn-danger po-delete ajax-silent'>" . htmlspecialchars($this->lang->line('application_yes_im_sure'), ENT_QUOTES) . "</a>";
                        $content = '<a class="btn btn-danger po-delete ajax-silent" href="' . base_url() . 'subcontractors/delete/' . $newAr['id'] . '" >' . htmlspecialchars($this->lang->line('application_yes_im_sure'), ENT_QUOTES) . '  </a><button class="btn po-close">' . $this->lang->line('application_no') . '</button><input type="hidden" name="td-id" class="id" value="' . $newAr['id'] . '">';
                        $main_content = "data-content='" . $content . "' data-original-title='<b>" . htmlspecialchars($this->lang->line('application_really_delete'), ENT_QUOTES) . "</b>'";
                        $newhtml .= "<tr id='" . $newAr['id'] . "'>";
                        $newhtml .= "<td class='hidden-xs' style='width:70px'>" . $core_settings->company_prefix . $user_cnt . "</td>";
                        $newhtml .= "<td class='hidden-xs'><a href='" . base_url() . 'subcontractors/view/' . $newAr['id'] . "'>" . $newAr['firstname'] . "</a></td>";
                        $newhtml .= "<td class='hidden-xs'>" . $newAr['lastname'] . "</td>";
                        $newhtml .= "<td class='hidden-xs'><a href='" . base_url() . 'subcontractors/view/' . $newAr['id'] . "'>" . $newAr['email'] . "</a></td>";
                        $newhtml .= "<td class='hidden-xs'>" . $newAr['status'] . "</td>";
                        if (!empty($newAr['company_name'])) {
                            $newhtml .= "<td class='option option-left'><a href='" . base_url() . 'subcontractors/edit_company/' . $newAr['id'] . '/' . $newAr['sub_id'] . "' data-toggle='mainmodal'>" . $newAr['company_name'] . "</a></td>";
                        } else {
                            $newhtml .= "<td>" . $name_of_company . "</td>";
                        }
                        $newhtml .= "<td class='option' width='8%'>";
                        $newhtml .= '<button type="button" class="btn-option delete po" data-toggle="popover" data-placement="left" ' . $main_content . '  ><i class="fa fa-times"></i></button>';
                        if ($newAr['status'] == "active") {
                            $newhtml .= "<a href='" . base_url() . 'subcontractors/update/' . $newAr['id'] . "' class='btn-option' data-toggle='mainmodal'><i class='fa fa-cog'></i></a>";
                            //$newhtml .= "<a href='" . base_url() . 'clients/edit_company/' . $newAr['id'] .'/'.$newAr['sub_id']."' class='btn-option' data-toggle='mainmodal'><i class='fa fa-pencil-square-o' aria-hidden='true'></i></a>";
                        } else {
                            $newhtml .= "<a href='javascript:void(0);' data-id=" . $newAr['id'] . " data-role='3' class='btn-option resend-invitaion-email'><i class='fa fa-envelope'></i></a>";
                        }
                        $newhtml .= "</td>";
                        $newhtml .= "</tr>";
                    }
                } else {
                    $count_elements = 0;
                    $newhtml .= "<tr class='odd'><td colspan='7' class='dataTables_empty' valign='top'>No matching records found</td></tr>";
                }
                $json_arr = array('count' => $count_elements, 'result' => $newhtml);
                echo json_encode($json_arr);
                exit;
            } else {
                $client_query = $this->db->select('*')->from('users')->join('user_roles', 'user_roles.user_id = users.id')->where("admin = '0' AND users.status IN ('active', 'inactive') AND user_roles.role_id = 4 AND company_id = " . $company_id)->get();
                if ($client_query->num_rows() > 0) {
                    $clients = $client_query->result_array();

                    $newhtml = "";
                    if (!empty($clients)) {
                        $user_cnt = 0;
                        $count_elements = count($clients);
                        foreach ($clients as $newAr) {
                            //echo "<pre>";print_r($newAr['user_id']);exit;
                            $user_cnt++;
                            $content = '<a class="btn btn-danger po-delete ajax-silent" href="' . base_url() . 'subcontractors/delete/' . $newAr['user_id'] . '" >' . htmlspecialchars($this->lang->line('application_yes_im_sure'), ENT_QUOTES) . '  </a><button class="btn po-close">' . $this->lang->line('application_no') . '</button><input type="hidden" name="td-id" class="id" value="' . $newAr['user_id'] . '">';
                            $main_content = "data-content='" . $content . "' data-original-title='<b>" . htmlspecialchars($this->lang->line('application_really_delete'), ENT_QUOTES) . "</b>'";
                            $newhtml .= "<tr id='" . $newAr['user_id'] . "'>";
                            $newhtml .= "<td class='hidden-xs' style='width:70px'>" . $core_settings->company_prefix . $user_cnt . "</td>";
                            $newhtml .= "<td class='hidden-xs'><a href='" . base_url() . 'subcontractors/view/' . $newAr['user_id'] . "'>" . $newAr['firstname'] . "</a></td>";
                            $newhtml .= "<td class='hidden-xs'>" . $newAr['lastname'] . "</td>";
                            $newhtml .= "<td class='hidden-xs'><a href='" . base_url() . 'subcontractors/view/' . $newAr['user_id'] . "'>" . $newAr['email'] . "</a></td>";
                            $newhtml .= "<td class='hidden-xs'>" . $newAr['status'] . "</td>";
                            $get_client_assign_company = $this->db->query('select cc.* from client_assign_companies ca join client_companies cc on ca.client_id=cc.client_id where ca.client_id="' . $newAr['user_id'] . '" AND ca.user_id="' . $owner_id . '"')->row_array();
                            if (empty($get_client_assign_company)) {
                                $newhtml .= "<td>" . $name_of_company . "</td>";
                            } else {
                                $newhtml .= "<td class='option option-left'><a href='" . base_url() . 'subcontractors/edit_company/' . $newAr['user_id'] . '/' . $get_client_assign_company['id'] . "' data-toggle='mainmodal'>" . $get_client_assign_company['sub_company'] . "</a></td>";
                            }
                            //echo "<pre>";print_r($get_client_assign_company);exit;

                            $newhtml .= "<td class='option' width='8%'>";
                            $newhtml .= '<button type="button" class="btn-option delete po" data-toggle="popover" data-placement="left" ' . $main_content . '  ><i class="fa fa-times"></i></button>';
                            if ($newAr['status'] == "active") {
                                $newhtml .= "<a href='" . base_url() . 'subcontractors/update/' . $newAr['user_id'] . "' class='btn-option' data-toggle='mainmodal'><i class='fa fa-cog'></i></a>";
                            } else {
                                $newhtml .= "<a href='javascript:void(0);' data-id=" . $newAr['user_id'] . " data-role='3' class='btn-option resend-invitaion-email'><i class='fa fa-envelope'></i></a>";
                            }
                            $newhtml .= "</td>";
                            $newhtml .= "</tr>";
                        }
                    } else {
                        $count_elements = 0;
                        $newhtml .= "<tr class='odd'><td colspan='7' class='dataTables_empty' valign='top'>No matching records found</td></tr>";
                    }
                    $json_arr = array('count' => $count_elements, 'result' => $newhtml);
                    echo json_encode($json_arr);
                    exit;
                }
            }
        }
    }

    function edit_company($client_id = FALSE, $c_id = FALSE) {

        $this->view_data['company_id'] = $this->sessionArr['company_id'];
        $this->view_data['owner_id'] = $this->sessionArr['user_id'];
        $this->view_data['client_id'] = $client_id;
        $this->view_data['c_id'] = $c_id;
        $get_company = Company::find_by_id($this->sessionArr['company_id']);
        $this->view_data['name_of_company'] = $get_company->name;
        if ($_POST) {
            $sub_id = $this->input->post('sub_id');
            $update_c = ClientCompanies::find_by_id($sub_id);
            //echo "<pre>";print_r($update_c);exit;
            $sub_company_name = strtolower(trim($this->input->post('sub_company')));
            $client_arr = array(
                'sub_company' => $sub_company_name,
                'email' => $this->input->post('email'),
                'website' => $this->input->post('website'),
                'phone' => $this->input->post('phone'),
                'fax' => $this->input->post('fax'),
                'updated_at' => date('Y-m-d H:i:s')
            );
            $newcompany_arr = array(
                'sub_company' => $sub_company_name
            );
            $this->db->where('id', $sub_id);
            if (!$this->db->update('client_companies', $client_arr)) {
                $this->session->set_flashdata('message', 'error: ' . $this->lang->line('application_company_error'));
            } else {
                $this->db->where('sub_company', $update_c->sub_company);
                $this->db->update('client_companies', $newcompany_arr);
                //$this->db->query('update client_companies set sub_company="'.$this->input->post('sub_company').'" where sub_company="'.$update_c->sub_company.'" ');
                $this->session->set_flashdata('message', 'success: ' . $this->lang->line('application_company_success'));
            }
            redirect(base_url() . 'subcontractors');
        } else {
            $company_id = $this->company_id;
            $this->view_data['user'] = $this->db->select('*')->from('client_companies')->where("client_id = '" . $client_id . "' AND id = '" . $c_id . "'")->get()->row_object();
            $this->theme_view = 'modal';
            $this->view_data['title'] = $this->lang->line('application_edit_company');
            $this->view_data['form_action'] = base_url() . 'subcontractors/edit_company';
            $this->content_view = 'subcontractors/_company';
        }
    }

    function search_companies() {
        if (!empty($_REQUEST)) {
            $company_id = $this->sessionArr['company_id'];
            $owner_id = $_REQUEST['owner_id'];
            $get_company = Company::find_by_id($this->sessionArr['company_id']);
            $name_of_company = $get_company->name;

            $subcontractor_query = $this->db->select('u.*,r.user_id')->from('users u')->join('user_roles r', 'r.user_id = u.id')->where("admin = '0' AND u.status IN ('active', 'inactive') AND r.role_id = 4 AND r.company_id = " . $company_id)->get()->result_array();
            $newhtml = "";
            if (!empty($subcontractor_query)) {
                //echo "<pre>";print_r($subcontractor_query);
                $get_subcontractor_query_client = $this->db->select('cc.client_id')->from('users u')->join('user_roles r', 'r.user_id = u.id')->join('client_companies cc', 'cc.client_id = r.user_id')->where("admin = '0' AND u.status IN ('active', 'inactive') AND r.role_id = 4 AND r.company_id = " . $company_id)->like('cc.sub_company', strtolower(trim($name_of_company)))->get()->result_array();
                if (empty($get_subcontractor_query_client)) {
                    $count_elements = 0;
                    $newhtml .= "<tr class='odd'><td colspan='7' class='dataTables_empty' valign='top'>No matching records found</td></tr>";
                } else {
                    //echo "<pre>";print_r($get_subcontractor_query_client);
                    $j = 0;
                    $newArr1 = array();
                    for ($j = 0; $j < count($get_subcontractor_query_client); $j++) {
                        $newArr1[$j] = $get_subcontractor_query_client[$j]['client_id'];
                    }
                    $implode_clients = implode(',', $newArr1);

                    $main_subcontractor_query = $this->db->query('SELECT * FROM users u JOIN user_roles r ON r.user_id = u.id WHERE r.user_id NOT IN ("' . $implode_clients . '") AND admin = "0" AND u.status IN ("active", "inactive") AND r.role_id = 4 AND r.company_id = "' . $company_id . '"')->result_array();
                    //exit;
                    if (!empty($main_subcontractor_query)) {
                        $user_cnt = 0;
                        $count_elements = count($main_subcontractor_query);
                        foreach ($main_subcontractor_query as $newAr) {
                            $user_cnt++;
                            $content1 = "<a class='btn btn-danger po-delete ajax-silent'>" . htmlspecialchars($this->lang->line('application_yes_im_sure'), ENT_QUOTES) . "</a>";
                            $content = '<a class="btn btn-danger po-delete ajax-silent" href="' . base_url() . 'subcontractors/delete/' . $newAr['id'] . '" >' . htmlspecialchars($this->lang->line('application_yes_im_sure'), ENT_QUOTES) . '  </a><button class="btn po-close">' . $this->lang->line('application_no') . '</button><input type="hidden" name="td-id" class="id" value="' . $newAr['id'] . '">';
                            $main_content = "data-content='" . $content . "' data-original-title='<b>" . htmlspecialchars($this->lang->line('application_really_delete'), ENT_QUOTES) . "</b>'";
                            $newhtml .= "<tr id='" . $newAr['user_id'] . "'>";
                            $newhtml .= "<td class='hidden-xs' style='width:70px'>" . $core_settings->company_prefix . $user_cnt . "</td>";
                            $newhtml .= "<td class='hidden-xs'><a href='" . base_url() . 'subcontractors/view/' . $newAr['user_id'] . "'>" . $newAr['firstname'] . "</a></td>";
                            $newhtml .= "<td class='hidden-xs'>" . $newAr['lastname'] . "</td>";
                            $newhtml .= "<td class='hidden-xs'><a href='" . base_url() . 'subcontractors/view/' . $newAr['user_id'] . "'>" . $newAr['email'] . "</a></td>";
                            $newhtml .= "<td class='hidden-xs'>" . $newAr['status'] . "</td>";

                            $newhtml .= "<td>" . $name_of_company . "</td>";
                            $newhtml .= "<td class='option' width='8%'>";
                            $newhtml .= '<button type="button" class="btn-option delete po" data-toggle="popover" data-placement="left" ' . $main_content . '  ><i class="fa fa-times"></i></button>';
                            if ($newAr['status'] == "active") {
                                $newhtml .= "<a href='" . base_url() . 'subcontractors/update/' . $newAr['user_id'] . "' class='btn-option' data-toggle='mainmodal'><i class='fa fa-cog'></i></a>";
                            } else {
                                $newhtml .= "<a href='javascript:void(0);' data-id=" . $newAr['user_id'] . " data-role='3' class='btn-option resend-invitaion-email'><i class='fa fa-envelope'></i></a>";
                            }
                            $newhtml .= "</td>";
                            $newhtml .= "</tr>";
                        }
                    } else {
                        $count_elements = 0;
                        $newhtml .= "<tr class='odd'><td colspan='7' class='dataTables_empty' valign='top'>No matching records found</td></tr>";
                    }
                }
            } else {
                $count_elements = 0;
                $newhtml .= "<tr class='odd'><td colspan='7' class='dataTables_empty' valign='top'>No matching records found</td></tr>";
            }
            $json_arr = array('count' => $count_elements, 'result' => $newhtml);
            echo json_encode($json_arr);
            exit;
        }
    }

    function company_name_check() {
        if (!empty($_REQUEST)) {
            $company_id = $this->sessionArr['company_id'];
            $sub_company = strtolower(trim($_REQUEST['sub_company']));
            $owner_id = $_REQUEST['owner_id'];

            $check_company_by_user = $this->db->query('select cc.* from client_companies cc join client_assign_companies ca ON cc.client_id = ca.client_id WHERE cc.sub_company = "' . $sub_company . '" AND ca.user_id = "' . $owner_id . '"')->num_rows();
            //echo $check_company_by_user;exit;
            if ($check_company_by_user > 0) {
                echo 1;
                exit;
            } else {
                echo 0;
                exit;
            }
        }
    }

}

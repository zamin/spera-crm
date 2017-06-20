<?php
/**
 * ClassName: Message API
 * Function Name: index 
 * This class is used Message Api
 **/

if (!defined('BASEPATH')) exit('No direct script access allowed');

class Message extends MY_Api_Controller 
{   
    public $user_sessoin_id = null;

    function __construct() 
    {
        parent::__construct();
        $this->load->database();
        
        $headers = apache_request_headers();
        $this->settings = Setting::first();
//        $user_access_token = $headers['User-Access-Token'] ? $headers['User-Access-Token'] : '';
//        if(empty($user_access_token))
//        {
//            $newdata = array('result' => 'fail', 'response' =>array( 'code'=> 400, 'message' => 'Access token not found'));
//            $this->response($newdata);
//        }
//        
//        $user_login_token = $headers['User-Login-Token'] ? $headers['User-Login-Token'] : '';
//        if(empty($user_login_token))
//        {
//            $newdata = array('result' => 'fail', 'response' =>array( 'code'=> 400, 'message' => 'Login token not found'));
//            $this->response($newdata);
//        }
        $user_access_token = $headers['user_access_token'] ? $headers['user_access_token'] : '';
        if(empty($user_access_token))
        {
            $newdata = array('result' => 'fail', 'response' =>array( 'code'=> 400, 'message' => 'Access token not found'));
            $this->response($newdata);
        }
        
        $user_login_token = $headers['user_login_token'] ? $headers['user_login_token'] : '';
        if(empty($user_login_token))
        {
            $newdata = array('result' => 'fail', 'response' =>array( 'code'=> 400, 'message' => 'Login token not found'));
            $this->response($newdata);
        }
        $this->user_sessoin_id = $this->checklogin($user_access_token,$user_login_token);
    }

    // this function is use for the write new message and replay
    function write()
    {   
        if(!empty($_REQUEST))
        {
            $recipient = trim(htmlspecialchars($_REQUEST['recipient'])) ? trim(htmlspecialchars($_REQUEST['recipient'])) : '';
            $subject = trim(htmlspecialchars($_REQUEST['subject'])) ? trim(htmlspecialchars($_REQUEST['subject'])) : '';
            $message = trim(htmlspecialchars($_REQUEST['message'])) ? trim(htmlspecialchars($_REQUEST['message'])) : '';
            $new_data = array();

            if(!empty($recipient) && !empty($subject)) 
            {
                $user_sessoin_id = $this->user_sessoin_id;
                if($user_sessoin_id != 0) 
                {
                    $check_user_login = $this->db->query('SELECT r.*,u.* from user_roles r join users u on u.id=r.user_id where u.id="' . $user_sessoin_id . '"')->row_array();
                    $sender_company = $check_user_login['company_id'];
                    $sender_email = $check_user_login['email'];
                    
                    //if($recipient == $user_sessoin_id)
                    if($recipient == $sender_email)
                    {
                        $newdata = array('result' => 'fail', 'response' => array( 'code' => 400, 'message' => 'You can not send message to yourself'));
                        $this->response($newdata);   
                    }
                    
                    if(!empty($recipient))
                    {
                        $receiver_company_details = $this->db->query('select users.id as recever_id,users.*,user_roles.* from users join user_roles on users.id = user_roles.user_id where users.email = "'.$recipient.'" and user_roles.company_id ="'.$sender_company.'"')->row_array();

                        //$receiver_company_details = User::find('all',array('conditions' => array('email = ? AND ',$recipient)));
                        //  echo '<pre>';print_r($receiver_company_details);die;
                        $receiver_id = $receiver_company_details['recever_id'];
                        $receiver_email = $receiver_company_details['email'];
                        $receivercompany = $receiver_company_details['company_id'];
                        
                        //echo $sender_company;die;
                        /*echo '<pre>';print_r($_REQUEST);die;*/

                        if($receivercompany != $sender_company)
                        {
                            $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'You can not send message to other company user'));
                            $this->response($newdata);   
                        }

                        $_REQUEST['recipient'] = 'u'.$receiver_id;
                        $receiver = User::find($receiver_id);
                        $receiveremail = $receiver_email;
                    }
                    //$_REQUEST['recipient'] check the company details
                    //$message = $_REQUEST['message'];

                    unset($_REQUEST['ci_session']);
                    unset($_REQUEST['user_login_token']);
                    unset($_REQUEST['user_access_token']);

                    /*$config['upload_path'] = './files/media/';
                    $config['encrypt_name'] = TRUE;
                    $config['allowed_types'] = '*';

                    $this->load->library('upload', $config);
                    $this->load->helper('notification');

                    unset($_REQUEST['userfile']);
                    unset($_REQUEST['file-name']);

                    unset($_REQUEST['send']);
                    unset($_REQUEST['note-codable']);
                    unset($_REQUEST['files']);*/

                    
                    /*$attachment = FALSE;
                    if (!$this->upload->do_upload())
                    {
                        $newdata = array('validate' => 'error', 'response' => array('responce' => 'Media not uploaded'));
                        header('Content-Type: application/json');
                        echo json_encode($newdata);
                        exit;

                        // $error = $this->upload->display_errors('', ' ');
                        // if($error != "You did not select a file to upload."){
                        //     //$this->session->set_flashdata('message', 'error:'.$error);
                        // }
                    }
                    else
                    {
                        $newdata = array('validate' => 'error', 'response' => array('responce' => 'Media uploaded'));
                        header('Content-Type: application/json');
                        echo json_encode($newdata);
                        exit;

                        $data = array('upload_data' => $this->upload->data());
                        $_REQUEST['attachment'] = $data['upload_data']['orig_name'];
                        $_REQUEST['attachment_link'] = $data['upload_data']['file_name'];
                        $attachment = $data['upload_data']['file_name'];
                    }*/

                    $_REQUEST = array_map('htmlspecialchars', $_REQUEST);
                    $_REQUEST['message'] = $message;
                    $_REQUEST['time'] = date('Y-m-d H:i', time());
                    $_REQUEST['sender'] = "u".$user_sessoin_id;
                    $_REQUEST['status'] = "New";
                    
                    if(!isset($_REQUEST['conversation']))
                    {
                        $_REQUEST['conversation'] = random_string('sha1');
                    }

                    if(isset($_REQUEST['previousmessage']))
                    {
                        $status = Privatemessage::find_by_id($_REQUEST['previousmessage']);
                        if($receiveremail == $this->user->email)
                        {
                            $receiverart = substr($status->recipient, 0, 1);
                            $receiverid = substr($status->recipient, 1, 9999);
                            $_REQUEST['recipient'] = $status->recipient;

                            $receiver = User::find($receiverid);
                            $receiveremail = $receiver->email;   
                        }
                        
                        $status->status = 'Replied';
                        $status->save();
                        unset($_REQUEST['previousmessage']);
                    }
                    //print_r($_REQUEST);die;
                    $message = Privatemessage::create($_REQUEST);
                    
                    if(!$message)
                    {
                        //$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_write_message_error'));
                        $newdata = array('result' => 'fail', 'response' => array( 'code' => 400, 'message' => 'Message not send sucessfully'));
                        $this->response($newdata);
                    }
                    else
                    {
                        $this->load->library('email');
                        $this->email->from($this->settings->from_email_id,$this->settings->from_email_name);
                        $this->email->to($receiveremail);
                        $this->email->subject('Notification :: Message');
                        $this->email->message("You got a new message.<br>");
                        if($this->email->send()) 
                        {
                            $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'Message send sucessfully'));
                            $this->response($newdata);
                        }
                        $newdata = array('result' => 'success', 'response' => array('code' => 400, 'message' => 'Message send sucessfully But mail not sent'));
                        $this->response($newdata);
                    }
                }
            }
            else
            {
                $newdata = array('result' => 'fail', 'response' => array( 'code' => 400, 'message' => 'Please enter required fields'));
                $this->response($newdata);
            }
        }
        else
        {
            $newdata = array('result' => 'noData', 'response' => array( 'code' => 404, 'message' => 'Data not found'));
            $this->response($newdata);
        }   
    }   

    // this function is use for display all the inbox messagelist
    function messagelist()
    {
        $con = (!empty($_REQUEST['con']) && isset($_REQUEST['con'])) ? $_REQUEST['con'] : FALSE;
        $deleted = (!empty($_REQUEST['deleted']) && isset($_REQUEST['deleted'])) ? $_REQUEST['deleted'] : FALSE;

        /*if(!empty($_REQUEST))
        {*/
            $new_data = array();
            $user_sessoin_id = $this->user_sessoin_id;             
            if($user_sessoin_id != 0) 
            {
                $max_value = 60;
                if($deleted == "deleted"){ $qdeleted = " AND privatemessages.status = 'deleted' OR privatemessages.deleted = 1 ";}else{ $qdeleted = ' AND privatemessages.status != "deleted" AND privatemessages.deleted = 0 ';  }

                if(is_numeric($con)){ $limit = $con.','; } else{$limit = FALSE;}
                $sql = 'SELECT privatemessages.id, privatemessages.`status`, privatemessages.`deleted`, privatemessages.attachment, privatemessages.attachment_link, privatemessages.subject, privatemessages.conversation, privatemessages.sender, privatemessages.recipient, privatemessages.message, privatemessages.`time`, users.`userpic` as userpic_c, users.`userpic` as userpic_u , users.`email` as email_u , users.`email` as email_c , CONCAT(users.firstname," ", users.lastname) as sender_u, CONCAT(users.firstname," ", users.lastname) as sender_c
                        FROM privatemessages
                        LEFT JOIN users ON CONCAT("u",users.id) = privatemessages.sender OR CONCAT("c",users.id) = privatemessages.sender OR CONCAT("s",users.id) = privatemessages.sender
                        where privatemessages.receiver_delete != 1
                        GROUP by privatemessages.id HAVING privatemessages.recipient = "u'.$user_sessoin_id.'" '.$qdeleted.' ORDER BY privatemessages.`time` DESC LIMIT '.$limit.$max_value.'';

                    //echo '<br>';
                 $sql2 = 'SELECT privatemessages.id, privatemessages.`status`, privatemessages.`deleted`, privatemessages.attachment, privatemessages.attachment_link, privatemessages.subject, privatemessages.conversation, privatemessages.sender, privatemessages.recipient, privatemessages.message, privatemessages.`time`, users.`userpic` as userpic_c, users.`userpic` as userpic_u , users.`email` as email_u , users.`email` as email_c , CONCAT(users.firstname," ", users.lastname) as sender_u, CONCAT(users.firstname," ", users.lastname) as sender_c
                        FROM privatemessages
                        LEFT JOIN users ON CONCAT("u",users.id) = privatemessages.sender OR CONCAT("c",users.id) = privatemessages.sender OR CONCAT("s",users.id) = privatemessages.sender
                        where privatemessages.receiver_delete != 1
                        GROUP by privatemessages.id HAVING privatemessages.recipient = "u'.$user_sessoin_id.'" '.$qdeleted.' ORDER BY privatemessages.`time` DESC';


                $query = $this->db->query($sql);
                $query2 = $this->db->query($sql2);
                //echo '<pre>';print_r($query2);die;
                
                $rows = $query2->num_rows();        
                $this->view_data["message"] = array_filter($query->result());
                //---------
                $newmessageData = $this->view_data["message"];
                if(!empty($newmessageData))
                {
                    foreach ($newmessageData as $key => $value) 
                    {
                        foreach ($value as $k => $v) 
                        {
                            if($k == 'message')
                            {
                                $newmessageData[$key]->message = strip_tags($v);
                            }   
                        }
                    }
                }
                $this->view_data["message"] = $newmessageData;
                //---------
                $resultArr['code'] = 200;
                $resultArr['message'] = $this->view_data["message"];

                $newdata = array('result' => 'success', 'response' => $resultArr);
                $this->response($newdata);
                //$this->view_data["message_rows"] = $rows;
            }
            else
            {
                $newdata = array('result' => 'fail', 'response' => array( 'code' => 400, 'message' => 'Not valid user'));
                $this->response($newdata);
            }
        /*}
        else
        {
            $newdata = array('validate' => 'error', 'response' => 'Empty request');
            $this->response($newdata);
        }*/
    }

    // this function is use to display messagelist with different condition (sent/marked/deleted)
    function filter()
    {
        $condition = (!empty($_REQUEST['condition']) && isset($_REQUEST['condition'])) ? $_REQUEST['condition'] : FALSE;
        $con = (!empty($_REQUEST['con']) && isset($_REQUEST['con'])) ? $_REQUEST['con'] : FALSE;

        if(!empty($_REQUEST))
        {
            $new_data = array();

            if(!empty($condition)) 
            {
                $user_sessoin_id = $this->user_sessoin_id;
                if($user_sessoin_id != 0) 
                {
                    $max_value = 60;
                    if(is_numeric($con)){ $limit = $con.','; } else{$limit = FALSE;}
                    switch ($condition) 
                    {
                        case 'sent':
                            $sql = 'SELECT privatemessages.id, privatemessages.`status`, privatemessages.subject, privatemessages.attachment, privatemessages.attachment_link, privatemessages.message, privatemessages.sender, privatemessages.recipient, privatemessages.`time`, users.`userpic` as userpic_c, users.`userpic` as userpic_u , users.`email` as email_u , users.`email` as email_c , CONCAT(users.firstname," ", users.lastname) as sender_u, CONCAT(users.firstname," ", users.lastname) as sender_c
                            FROM privatemessages
                            LEFT JOIN users ON CONCAT("u",users.id) = privatemessages.recipient 
                            where privatemessages.deleted != 1
                            GROUP by privatemessages.id HAVING privatemessages.sender = "u'.$user_sessoin_id.'" ORDER BY privatemessages.`time` DESC LIMIT '.$limit.$max_value;
                            //echo '<br>';
                            $sql2 = 'SELECT privatemessages.id, privatemessages.`status`, privatemessages.subject, privatemessages.attachment, privatemessages.attachment_link, privatemessages.conversation, privatemessages.sender, privatemessages.recipient, privatemessages.message, privatemessages.`time`, users.`userpic` as userpic_c, users.`userpic` as userpic_u , users.`email` as email_u , users.`email` as email_c , CONCAT(users.firstname," ", users.lastname) as sender_u, CONCAT(users.firstname," ", users.lastname) as sender_c
                            FROM privatemessages
                            LEFT JOIN users ON CONCAT("u",users.id) = privatemessages.recipient 
                            where privatemessages.deleted != 1
                            GROUP by privatemessages.id HAVING privatemessages.sender = "u'.$user_sessoin_id.'" ORDER BY privatemessages.`time` DESC';

                            $this->view_data["filter"] = "Sent";
                            break;
                        case 'marked':
                        
                            $sql = 'SELECT privatemessages.id, privatemessages.`status`, privatemessages.`deleted`, privatemessages.attachment, privatemessages.attachment_link, privatemessages.subject, privatemessages.conversation, privatemessages.sender, privatemessages.recipient, privatemessages.message, privatemessages.`time`, users.`userpic` as userpic_c, users.`userpic` as userpic_u , users.`email` as email_u , users.`email` as email_c , CONCAT(users.firstname," ", users.lastname) as sender_u, CONCAT(users.firstname," ", users.lastname) as sender_c
                            FROM privatemessages
                            LEFT JOIN users ON ( CONCAT("u",users.id) = privatemessages.sender )
                            GROUP by privatemessages.id HAVING ( privatemessages.recipient = "u'.$user_sessoin_id.'" )  AND privatemessages.`status`="Marked" ORDER BY privatemessages.`time` DESC LIMIT '.$limit.$max_value;
                            //echo '<br>';
                            $sql2 = 'SELECT privatemessages.id, privatemessages.`status`, privatemessages.`deleted`, privatemessages.attachment, privatemessages.attachment_link, privatemessages.subject, privatemessages.conversation, privatemessages.sender, privatemessages.recipient, privatemessages.message, privatemessages.`time`, users.`userpic` as userpic_c, users.`userpic` as userpic_u , users.`email` as email_u , users.`email` as email_c , CONCAT(users.firstname," ", users.lastname) as sender_u, CONCAT(users.firstname," ", users.lastname) as sender_c
                            FROM privatemessages
                            LEFT JOIN users ON ( CONCAT("u",users.id) = privatemessages.sender) 
                            GROUP by privatemessages.id HAVING ( privatemessages.recipient = "u'.$user_sessoin_id.'") AND privatemessages.`status`="Marked" ORDER BY privatemessages.`time` DESC';


                            $this->view_data["filter"] = "Marked";
                            break;
                        case 'deleted':
                            $sql = 'SELECT privatemessages.id, privatemessages.`status`, privatemessages.`deleted`, privatemessages.attachment, privatemessages.attachment_link, privatemessages.subject, privatemessages.conversation, privatemessages.sender, privatemessages.receiver_delete, privatemessages.recipient, privatemessages.message, privatemessages.`time`, users.`userpic` as userpic_c, users.`userpic` as userpic_u , users.`email` as email_u , users.`email` as email_c , CONCAT(users.firstname," ", users.lastname) as sender_u, CONCAT(users.firstname," ", users.lastname) as sender_c
                            FROM privatemessages
                            LEFT JOIN users ON CONCAT("u",users.id) = privatemessages.sender 
                            WHERE (privatemessages.recipient = "u'.$user_sessoin_id.'" AND privatemessages.receiver_delete = 1 ) OR (privatemessages.sender = "u'.$user_sessoin_id.'" AND privatemessages.status = "deleted" )
                            GROUP by privatemessages.id ORDER BY privatemessages.`time` DESC LIMIT '.$limit.$max_value;
                        //echo '<br>';
                            $sql2 = 'SELECT privatemessages.id, privatemessages.`status`, privatemessages.`deleted`, privatemessages.attachment, privatemessages.attachment_link, privatemessages.subject, privatemessages.conversation, privatemessages.sender, privatemessages.receiver_delete,privatemessages.recipient, privatemessages.message, privatemessages.`time`, users.`userpic` as userpic_c, users.`userpic` as userpic_u , users.`email` as email_u , users.`email` as email_c , CONCAT(users.firstname," ", users.lastname) as sender_u, CONCAT(users.firstname," ", users.lastname) as sender_c
                            FROM privatemessages
                            LEFT JOIN users ON CONCAT("u",users.id) = privatemessages.sender 
                            WHERE (privatemessages.recipient = "u'.$user_sessoin_id.'" AND privatemessages.receiver_delete = 1 ) OR (privatemessages.sender = "u'.$user_sessoin_id.'" AND privatemessages.status = "deleted"  )
                            GROUP by privatemessages.id ORDER BY privatemessages.`time` DESC';
                            $this->view_data["filter"] = "Deleted";
                            break;
                        default:
                            $sql = 'SELECT privatemessages.id, privatemessages.`status`, privatemessages.subject, privatemessages.attachment, privatemessages.attachment_link, privatemessages.message, privatemessages.sender, privatemessages.recipient, privatemessages.`time`, users.`userpic` as userpic_c, users.`userpic` as userpic_u , users.`email` as email_u , users.`email` as email_c , CONCAT(users.firstname," ", users.lastname) as sender_u, CONCAT(users.firstname," ", users.lastname) as sender_c
                            FROM privatemessages
                            LEFT JOIN users ON CONCAT("u",users.id) = privatemessages.sender OR CONCAT("c",users.id) = privatemessages.sender OR CONCAT("s",users.id) = privatemessages.sender
                            GROUP by privatemessages.conversation HAVING privatemessages.recipient = "u'.$user_sessoin_id.'" AND privatemessages.`status`="New" ORDER BY privatemessages.`time` DESC LIMIT '.$limit.$max_value;
                            $this->view_data["filter"] = FALSE;
                            break;
                    }
                    
                    $query = $this->db->query($sql);
                    $query2 = $this->db->query($sql2);
                    $rows = $query2->num_rows();
                    $this->view_data["message"] = array_filter($query->result());
                    //---------
                    $newmessageData = $this->view_data["message"];
                    if(!empty($newmessageData))
                    {
                        foreach ($newmessageData as $key => $value) 
                        {
                            foreach ($value as $k => $v) 
                            {
                                if($k == 'message')
                                {
                                    $newmessageData[$key]->message = strip_tags($v);
                                }   
                            }
                        }
                    }
                    else
                    {
                        $newmessageData = 'No Data Found';   
                    }

                    $this->view_data["message"] = $newmessageData;
                    //---------
                    //$this->view_data["message_rows"] = $rows;
                    $resultArr['code'] = 200;
                    $resultArr['message'] = $this->view_data["message"];

                    $newdata = array('result' => 'success', 'response' => $resultArr);
                    $this->response($newdata);
                }
                else
                {
                    $newdata = array('result' => 'fail', 'response' => array( 'code' => 400, 'message' => 'Not valid user'));
                    $this->response($newdata);
                }
            }
            else
            {
                $newdata = array('result' => 'fail', 'response' => array( 'code' => 400, 'message' => 'Please enter required fields'));
                $this->response($newdata);
            }   
        }
        else
        {
            $newdata = array('result' => 'fail', 'response' => array( 'code' => 404, 'message' => 'Empty request'));
            $this->response($newdata);
        }
    }

    // this function is use to view a single message (with old message conversation)
    function get()
    {
        if(!empty($_REQUEST))
        {
            $id  = (!empty($_REQUEST['id']) && isset($_REQUEST['id'])) ? $_REQUEST['id'] : FALSE;
            $new_data = array();

            if(!empty($id)) 
            {
                $user_sessoin_id = $this->user_sessoin_id;
                if($user_sessoin_id != 0) 
                {
                    //$id  = (!empty($_REQUEST['id']) && isset($_REQUEST['id'])) ? $_REQUEST['id'] : FALSE;
                    $filter = (!empty($_REQUEST['filter']) && isset($_REQUEST['filter'])) ? $_REQUEST['filter'] : FALSE;
                    $additional = (!empty($_REQUEST['additional']) && isset($_REQUEST['additional'])) ? $_REQUEST['additional'] : FALSE;
                    
                    $this->view_data['submenu'] = array(
                                    $this->lang->line('application_back') => 'aomessages',
                                    );  
                    $message = Privatemessage::find_by_id($id);
                    //echo $filter;
                    //echo '<pre>';print_r($message);die;
                    $this->view_data["count"] = "1";
                    if(!$filter || $filter == "Marked")
                    {
                        // echo 'reach';
                        if($message->status == "New")
                        {
                            $message->status = 'Read';
                            $message->save();
                        }
                        $this->view_data["filter"] = FALSE;
                        $sql = 'SELECT privatemessages.id, privatemessages.conversation FROM privatemessages
                                WHERE ( privatemessages.recipient = "u'.$user_sessoin_id.'" OR privatemessages.sender = "u'.$user_sessoin_id.'") AND privatemessages.`id`="'.$id.'"';
                        $query = $this->db->query($sql);
                        $row = $query->row();   
                        //print_r($row);die;
                        $sql2 = 'SELECT privatemessages.id, privatemessages.`status`, privatemessages.conversation, privatemessages.attachment, privatemessages.attachment_link, privatemessages.subject, privatemessages.message, privatemessages.sender, privatemessages.recipient, privatemessages.`time`, privatemessages.`sender` , u1.`userpic` as userpic_c, users.`userpic` as userpic_u , users.`email` as email_u , u1.`email` as email_c , CONCAT(users.firstname," ", users.lastname) as sender_u, CONCAT(u1.firstname," ", u1.lastname) as sender_c, CONCAT(rec_u.firstname," ", rec_u.lastname) as recipient_u, CONCAT(rec_c.firstname," ", rec_c.lastname) as recipient_c
                                FROM privatemessages
                                LEFT JOIN users AS u1 ON (CONCAT("u",u1.id) = privatemessages.recipient OR CONCAT("u",u1.id) = privatemessages.recipient)
                                LEFT JOIN users ON ( CONCAT("u",users.id) = privatemessages.sender OR CONCAT("u",users.id) = privatemessages.sender)
                                LEFT JOIN users AS rec_c ON CONCAT("u",rec_c.id) = privatemessages.recipient
                                LEFT JOIN users AS rec_u ON CONCAT("u",rec_u.id) = privatemessages.recipient

                                GROUP by privatemessages.id HAVING privatemessages.conversation = "'.$row->conversation.'" ORDER BY privatemessages.`id` DESC LIMIT 100';
                        $query2 = $this->db->query($sql2);
                        /*echo '<pre>';
                        print_r($query2);
                        die;*/
                        
                        $this->view_data["conversation"] = array_filter($query2->result());
                        //---------
                        $newmessageData = $this->view_data["conversation"];
                        if(!empty($newmessageData))
                        {
                            foreach ($newmessageData as $key => $value) 
                            {
                                foreach ($value as $k => $v) 
                                {
                                    if($k == 'message')
                                    {
                                        $newmessageData[$key]->message = strip_tags($v);
                                    }   
                                }
                            }
                        }
                        $this->view_data["conversation"] = $newmessageData;
                        //---------
                        //$this->view_data["count"] = count ($this->view_data["conversation"]);
                        $resultArr['code'] = 200;
                        $resultArr['message'] = $this->view_data["conversation"];

                        $newdata = array('result' => 'success', 'response' => $resultArr);
                        $this->response($newdata);
                        //echo '<pre>';print_r($this->view_data["conversation"]);die;
                    }
                    else
                    {
                        if($filter == 'Deleted')
                        {
                            $sql = 'SELECT privatemessages.id, privatemessages.`status`, privatemessages.`deleted`, privatemessages.conversation, privatemessages.attachment, privatemessages.attachment_link,privatemessages.receiver_delete, privatemessages.subject, privatemessages.message, privatemessages.sender, privatemessages.recipient, privatemessages.`time`, privatemessages.`sender` , users.`userpic` as userpic_c, clients.`userpic` as userpic_u , users.`email` as email_u , clients.`email` as email_c , CONCAT(users.firstname," ", users.lastname) as sender_u, CONCAT(clients.firstname," ", clients.lastname) as sender_c, CONCAT(users.firstname," ", users.lastname) as recipient_u, CONCAT(clients.firstname," ", clients.lastname) as recipient_c
                                FROM privatemessages
                                LEFT JOIN users AS clients ON (CONCAT("u",clients.id) = privatemessages.recipient) 
                                LEFT JOIN users ON (CONCAT("u",users.id) = privatemessages.sender) 
                                GROUP by privatemessages.id HAVING privatemessages.id = "'.$id.'" AND (privatemessages.sender = "u'.$user_sessoin_id.'" OR privatemessages.recipient = "u'.$user_sessoin_id.'") ORDER BY privatemessages.`id` DESC LIMIT 100';
                       
                        }
                        else
                        {
                            if($filter == "Sent")
                            {
                                $sql = 'SELECT privatemessages.id, privatemessages.`status`, privatemessages.`deleted`, privatemessages.conversation, privatemessages.attachment, privatemessages.attachment_link, privatemessages.subject, privatemessages.message, privatemessages.sender, privatemessages.receiver_delete, privatemessages.recipient, privatemessages.`time`, privatemessages.`sender` , u1.`userpic` as userpic_c, users.`userpic` as userpic_u , users.`email` as email_u , u1.`email` as email_c , CONCAT(users.firstname," ", users.lastname) as sender_u, CONCAT(u1.firstname," ", u1.lastname) as sender_c, CONCAT(users.firstname," ", users.lastname) as recipient_u, CONCAT(u1.firstname," ", u1.lastname) as recipient_c
                                FROM privatemessages
                                LEFT JOIN users AS u1 ON CONCAT("u",u1.id) = privatemessages.recipient 
                                LEFT JOIN users ON  CONCAT("u",users.id) = privatemessages.sender 
                                GROUP by privatemessages.id HAVING privatemessages.id = "'.$id.'" AND privatemessages.sender = "u'.$user_sessoin_id.'" ORDER BY privatemessages.`id` DESC LIMIT 100';
                                
                                $receiverart = substr($additional, 0, 1);
                                $receiverid = substr($additional, 1, 9999);

                                //if( $receiverart == "u"){
                                $receiver = User::find($receiverid);
                                $this->view_data["recipient"] = $receiver->firstname.' '.$receiver->lastname;
                                //}
                            }
                            else
                            {
                                if(isset($message->conversation) && !empty($message->conversation))
                                {
                                    $sql = 'SELECT privatemessages.id, privatemessages.`status`, privatemessages.`deleted`, privatemessages.conversation, privatemessages.attachment, privatemessages.attachment_link, privatemessages.subject, privatemessages.message, privatemessages.sender,privatemessages.receiver_delete, privatemessages.recipient, privatemessages.`time`, privatemessages.`sender` , clients.`userpic` as userpic_c, users.`userpic` as userpic_u , users.`email` as email_u , clients.`email` as email_c , CONCAT(users.firstname," ", users.lastname) as sender_u, CONCAT(users.firstname," ", users.lastname) as sender_c, CONCAT(clients.firstname," ", clients.lastname) as recipient_u, CONCAT(clients.firstname," ", clients.lastname) as recipient_c
                                FROM privatemessages
                                LEFT JOIN users AS clients ON (CONCAT("c",clients.id) = privatemessages.recipient) OR (CONCAT("s",clients.id) = privatemessages.recipient) OR (CONCAT("u",clients.id) = privatemessages.recipient)
                                LEFT JOIN users ON (CONCAT("u",users.id) = privatemessages.sender) OR (CONCAT("c",users.id) = privatemessages.sender) OR (CONCAT("s",users.id) = privatemessages.sender)
                                GROUP by privatemessages.id HAVING privatemessages.conversation = "'.$message->conversation.'" AND (privatemessages.sender = "u'.$user_sessoin_id.'" OR privatemessages.recipient = "u'.$user_sessoin_id.'") ORDER BY privatemessages.`id` DESC LIMIT 100';
                                }
                                else
                                {
                                    $sql = 'SELECT privatemessages.id, privatemessages.`status`, privatemessages.`deleted`, privatemessages.conversation, privatemessages.attachment, privatemessages.attachment_link, privatemessages.subject, privatemessages.message, privatemessages.sender,privatemessages.receiver_delete, privatemessages.recipient, privatemessages.`time`, privatemessages.`sender` , clients.`userpic` as userpic_c, users.`userpic` as userpic_u , users.`email` as email_u , clients.`email` as email_c , CONCAT(users.firstname," ", users.lastname) as sender_u, CONCAT(users.firstname," ", users.lastname) as sender_c, CONCAT(clients.firstname," ", clients.lastname) as recipient_u, CONCAT(clients.firstname," ", clients.lastname) as recipient_c
                                FROM privatemessages
                                LEFT JOIN users AS clients ON (CONCAT("c",clients.id) = privatemessages.recipient) OR (CONCAT("s",clients.id) = privatemessages.recipient) OR (CONCAT("u",clients.id) = privatemessages.recipient)
                                LEFT JOIN users ON (CONCAT("u",users.id) = privatemessages.sender) OR (CONCAT("c",users.id) = privatemessages.sender) OR (CONCAT("s",users.id) = privatemessages.sender)
                                GROUP by privatemessages.id HAVING privatemessages.id = "'.$id.'" AND (privatemessages.sender = "u'.$user_sessoin_id.'" OR privatemessages.recipient = "u'.$user_sessoin_id.'") ORDER BY privatemessages.`id` DESC LIMIT 100';
                                }
                            }
                        }
                        $query = $this->db->query($sql);
                        
                        $this->view_data["conversation"] = array_filter($query->result());
                        //---------
                        $newmessageData = $this->view_data["conversation"];
                        if(!empty($newmessageData))
                        {
                            foreach ($newmessageData as $key => $value) 
                            {
                                foreach ($value as $k => $v) 
                                {
                                    if($k == 'message')
                                    {
                                        $newmessageData[$key]->message = strip_tags($v);
                                    }   
                                }
                            }
                        }
                        $this->view_data["conversation"] = $newmessageData;
                        //---------
                        $resultArr['code'] = 200;
                        $resultArr['message'] = $this->view_data["conversation"];

                        $newdata = array('result' => 'success', 'response' => $resultArr);
                        $this->response($newdata);
                        //$this->view_data["count"] = count ($this->view_data["conversation"]);
                        //$this->view_data["filter"] = $filter;
                    }
                }
                else
                {
                    $newdata = array('result' => 'fail', 'response' => array( 'code' => 400, 'message' => 'Not valid user'));
                    $this->response($newdata);
                }
            }
            else
            {
                $newdata = array('result' => 'fail', 'response' => array( 'code' => 400, 'message' => 'Please enter required fields'));
                $this->response($newdata);
            }
        }
        else
        {
            $newdata = array('result' => 'fail', 'response' => array( 'code' => 404, 'message' => 'Empty request'));
            $this->response($newdata);
        }
    }

    // this function is use to delete single message 
    function delete()
    {   
        if(!empty($_REQUEST))
        {
            $id = (!empty($_REQUEST['id']) && isset($_REQUEST['id'])) ? $_REQUEST['id'] : FALSE;
            $new_data = array();

            if(!empty($id)) 
            {
                $user_sessoin_id = $this->user_sessoin_id;
                if($user_sessoin_id != 0) 
                {
                    $message = Privatemessage::find_by_id($id);
                    //print_r($message);die;
                    if($user_sessoin_id == substr($message->recipient, 1, 9999))
                    {
                        $message->receiver_delete = '1';
                        $message->save();
                    }
                    elseif($user_sessoin_id == substr($message->sender, 1, 9999))
                    {
                        $message->status = 'deleted';
                        $message->deleted = '1';
                        $message->save();
                    }
                    else
                    {
                        $newdata = array('result' => 'fail', 'response' => array( 'code' => 400, 'message' => 'Message Not related to you'));
                        $this->response($newdata);
                    }

                    //$this->content_view = 'messages/accountowner_views/all';
                    if(!$message)
                    {
                        //$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_delete_message_error'));
                        $validate = 'fail';
                        $resultArr['code'] = 400;
                        $resultArr['message'] = 'Message not deleted';
                    }
                    else
                    {
                        //$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_delete_message_success'));
                        $validate = 'success';
                        $resultArr['code'] = 200;
                        $resultArr['message'] = 'Message deleted sucessfully';
                    }
                    //redirect('aomessages');

                    $newdata = array('result' => $validate, 'response' => $resultArr);
                    $this->response($newdata);
                } 
                else
                {
                    $newdata = array('result' => 'fail', 'response' => array( 'code' => 400, 'message' => 'Not valid user'));
                    $this->response($newdata);
                }
            }  
            else
            {
                $newdata = array('result' => 'fail', 'response' => array( 'code' => 400, 'message' => 'Please enter required fields'));
                $this->response($newdata);
            }
        }
        else
        {
            $newdata = array('result' => 'fail', 'response' => array( 'code' => 404, 'message' => 'Empty request'));
            $this->response($newdata);
        }
    }   
    
    // this function is use to view all sent messages
    function allsentmessage()
    {
        $condition =  'sent';
        $con = (!empty($_REQUEST['con']) && isset($_REQUEST['con'])) ? $_REQUEST['con'] : FALSE;

        /*if(!empty($_REQUEST))
        {*/
            $new_data = array();
            /*if(!empty($user_login_token) && !empty($user_access_token)) 
            {*/
                $user_sessoin_id = $this->user_sessoin_id;
                if($user_sessoin_id != 0) 
                {
                    $max_value = 60;
                    if(is_numeric($con)){ $limit = $con.','; } else{$limit = FALSE;}

                    $sql = 'SELECT privatemessages.id, privatemessages.`status`, privatemessages.subject, privatemessages.attachment, privatemessages.attachment_link, privatemessages.message, privatemessages.sender, privatemessages.recipient, privatemessages.`time`, users.`userpic` as userpic_c, users.`userpic` as userpic_u , users.`email` as email_u , users.`email` as email_c , CONCAT(users.firstname," ", users.lastname) as sender_u, CONCAT(users.firstname," ", users.lastname) as sender_c
                        FROM privatemessages
                        LEFT JOIN users ON CONCAT("u",users.id) = privatemessages.recipient 
                        where privatemessages.deleted != 1
                        GROUP by privatemessages.id HAVING privatemessages.sender = "u'.$user_sessoin_id.'" ORDER BY privatemessages.`time` DESC LIMIT '.$limit.$max_value;
                        //echo '<br>';
                        $sql2 = 'SELECT privatemessages.id, privatemessages.`status`, privatemessages.subject, privatemessages.attachment, privatemessages.attachment_link, privatemessages.conversation, privatemessages.sender, privatemessages.recipient, privatemessages.message, privatemessages.`time`, users.`userpic` as userpic_c, users.`userpic` as userpic_u , users.`email` as email_u , users.`email` as email_c , CONCAT(users.firstname," ", users.lastname) as sender_u, CONCAT(users.firstname," ", users.lastname) as sender_c
                        FROM privatemessages
                        LEFT JOIN users ON CONCAT("u",users.id) = privatemessages.recipient 
                        where privatemessages.deleted != 1
                        GROUP by privatemessages.id HAVING privatemessages.sender = "u'.$user_sessoin_id.'" ORDER BY privatemessages.`time` DESC';

                    $this->view_data["filter"] = "Sent";
                
                    $query = $this->db->query($sql);
                    $query2 = $this->db->query($sql2);
                    $rows = $query2->num_rows();
                    $this->view_data["message"] = array_filter($query->result());
                    //---------
                    $newmessageData = $this->view_data["message"];
                    if(!empty($newmessageData))
                    {
                        foreach ($newmessageData as $key => $value) 
                        {
                            foreach ($value as $k => $v) 
                            {
                                if($k == 'message')
                                {
                                    $newmessageData[$key]->message = strip_tags($v);
                                }   
                            }
                        }
                    }
                    $this->view_data["message"] = $newmessageData;
                    //---------
                    //$this->view_data["message_rows"] = $rows;
                    $resultArr['code'] = 200;
                    $resultArr['message'] = $this->view_data["message"];

                    $newdata = array('result' => 'success', 'response' => $resultArr);
                    $this->response($newdata);
                }
                else
                {
                    $newdata = array('result' => 'fail', 'response' => array( 'code' => 400, 'message' => 'Not valid user'));
                    $this->response($newdata);
                }
            /*}
            else
            {
                $newdata = array('validate' => 'error', 'response' => 'Please enter required fields');
                $this->response($newdata);
            }*/
        /*}
        else
        {
            $newdata = array('validate' => 'error', 'response' => 'Empty request');
            $this->response($newdata);
        }*/
    }
}
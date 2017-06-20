<?php
/**
 * ClassName: aomessages
 * Function Name: ____construct 
 * This class is used for account owner message
 **/
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Aomessages extends MY_Controller {

  function __construct()
    {
       //echo 'reach here';die;
       parent::__construct();
       if (!$this->user) {
            $this->view_data['error'] = "true";
            $this->session->set_flashdata('message', 'error: You have no access to any modules!');
            redirect('login');
        }
        if(!$this->sessionArr['company_id']) {
            $this->view_data['error'] = "true";
            $this->session->set_flashdata('message', 'error: You have no access to any modules!');
            redirect('login');
        }
        //echo 'nick';die;
        $this->load->database();
		$this->settings = Setting::first();
    }   
    function index()
    {   
        //echo 'reach';die;
        $this->content_view = 'messages/accountowner_views/all';
    }
    function messagelist($con = FALSE, $deleted = FALSE)
    {
        $max_value = 60;
        if($deleted == "deleted"){ $qdeleted = " AND privatemessages.status = 'deleted' OR privatemessages.deleted = 1 ";}else{ $qdeleted = ' AND privatemessages.status != "deleted" AND privatemessages.deleted = 0 ';  }

        if(is_numeric($con)){ $limit = $con.','; } else{$limit = FALSE;}
        $sql = 'SELECT privatemessages.id, privatemessages.`status`, privatemessages.`deleted`, privatemessages.attachment, privatemessages.attachment_link, privatemessages.subject, privatemessages.conversation, privatemessages.sender, privatemessages.recipient, privatemessages.message, privatemessages.`time`, users.`userpic` as userpic_c, users.`userpic` as userpic_u , users.`email` as email_u , users.`email` as email_c , CONCAT(users.firstname," ", users.lastname) as sender_u, CONCAT(users.firstname," ", users.lastname) as sender_c
                FROM privatemessages
                LEFT JOIN users ON CONCAT("u",users.id) = privatemessages.sender OR CONCAT("c",users.id) = privatemessages.sender OR CONCAT("s",users.id) = privatemessages.sender
                where privatemessages.receiver_delete != 1
                GROUP by privatemessages.id HAVING privatemessages.recipient = "u'.$this->user->id.'" '.$qdeleted.' ORDER BY privatemessages.`time` DESC LIMIT '.$limit.$max_value.'';

            //echo '<br>';
         $sql2 = 'SELECT privatemessages.id, privatemessages.`status`, privatemessages.`deleted`, privatemessages.attachment, privatemessages.attachment_link, privatemessages.subject, privatemessages.conversation, privatemessages.sender, privatemessages.recipient, privatemessages.message, privatemessages.`time`, users.`userpic` as userpic_c, users.`userpic` as userpic_u , users.`email` as email_u , users.`email` as email_c , CONCAT(users.firstname," ", users.lastname) as sender_u, CONCAT(users.firstname," ", users.lastname) as sender_c
                FROM privatemessages
                LEFT JOIN users ON CONCAT("u",users.id) = privatemessages.sender OR CONCAT("c",users.id) = privatemessages.sender OR CONCAT("s",users.id) = privatemessages.sender
                where privatemessages.receiver_delete != 1
                GROUP by privatemessages.id HAVING privatemessages.recipient = "u'.$this->user->id.'" '.$qdeleted.' ORDER BY privatemessages.`time` DESC';


        $query = $this->db->query($sql);
        $query2 = $this->db->query($sql2);
        //echo '<pre>';print_r($query2);die;
        
        $rows = $query2->num_rows();        
        $this->view_data["message"] = array_filter($query->result());
        //print_r($this->view_data["message"]);die;
        $this->view_data["message_rows"] = $rows;
        if($deleted){$this->view_data["deleted"] = "/".$deleted;}
        $this->view_data["message_list_page_next"] = $con+$max_value;
        $this->view_data["message_list_page_prev"] = $con-$max_value;
        $this->view_data["filter"] = FALSE;
        $this->theme_view = 'ajax';
        $this->content_view = 'messages/accountowner_views/list';
    }
    function filter($condition = FALSE, $con = FALSE)
    {
        $max_value = 60;
        if(is_numeric($con)){ $limit = $con.','; } else{$limit = FALSE;}
        switch ($condition) {
            case 'read':
                $sql = 'SELECT privatemessages.id, privatemessages.`status`, privatemessages.subject, privatemessages.attachment, privatemessages.attachment_link, privatemessages.message, privatemessages.sender, privatemessages.recipient, privatemessages.`time`, users.`userpic` as userpic_c, users.`userpic` as userpic_u , users.`email` as email_u , users.`email` as email_c , CONCAT(users.firstname," ", users.lastname) as sender_u, CONCAT(users.firstname," ", users.lastname) as sender_c
                FROM privatemessages
                /*LEFT JOIN clients ON CONCAT("c",clients.id) = privatemessages.sender*/
                LEFT JOIN users ON CONCAT("u",users.id) = privatemessages.sender OR CONCAT("c",users.id) = privatemessages.sender OR CONCAT("s",users.id) = privatemessages.sender
                GROUP by privatemessages.conversation HAVING privatemessages.sender = "u'.$this->user->id.'" AND (privatemessages.`status`="Replied" OR privatemessages.`status`="Read") ORDER BY privatemessages.`time` DESC LIMIT '.$limit.$max_value;
                
                $sql2 = 'SELECT privatemessages.id, privatemessages.`status`, privatemessages.subject, privatemessages.attachment, privatemessages.attachment_link, privatemessages.conversation, privatemessages.sender, privatemessages.recipient, privatemessages.message, privatemessages.`time`, usersusers.`userpic` as userpic_c, users.`userpic` as userpic_u , users.`email` as email_u , users.`email` as email_c , CONCAT(users.firstname," ", users.lastname) as sender_u, CONCAT(usersusers.firstname," ", users.lastname) as sender_c
                FROM privatemessages
                /*LEFT JOIN clients ON CONCAT("c",clients.id) = privatemessages.sender*/
                LEFT JOIN users ON CONCAT("u",users.id) = privatemessages.sender OR CONCAT("c",users.id) = privatemessages.sender OR CONCAT("s",users.id) = privatemessages.sender
                GROUP by privatemessages.conversation HAVING privatemessages.sender = "u'.$this->user->id.'" ORDER BY privatemessages.`time` DESC';
                $this->view_data["filter"] = "Read";
                break;
            case 'sent':
                $sql = 'SELECT privatemessages.id, privatemessages.`status`, privatemessages.subject, privatemessages.attachment, privatemessages.attachment_link, privatemessages.message, privatemessages.sender, privatemessages.recipient, privatemessages.`time`, users.`userpic` as userpic_c, users.`userpic` as userpic_u , users.`email` as email_u , users.`email` as email_c , CONCAT(users.firstname," ", users.lastname) as sender_u, CONCAT(users.firstname," ", users.lastname) as sender_c
                FROM privatemessages
                LEFT JOIN users ON CONCAT("u",users.id) = privatemessages.recipient 
                where privatemessages.deleted != 1
                GROUP by privatemessages.id HAVING privatemessages.sender = "u'.$this->user->id.'" ORDER BY privatemessages.`time` DESC LIMIT '.$limit.$max_value;
                //echo '<br>';
                $sql2 = 'SELECT privatemessages.id, privatemessages.`status`, privatemessages.subject, privatemessages.attachment, privatemessages.attachment_link, privatemessages.conversation, privatemessages.sender, privatemessages.recipient, privatemessages.message, privatemessages.`time`, users.`userpic` as userpic_c, users.`userpic` as userpic_u , users.`email` as email_u , users.`email` as email_c , CONCAT(users.firstname," ", users.lastname) as sender_u, CONCAT(users.firstname," ", users.lastname) as sender_c
                FROM privatemessages
                LEFT JOIN users ON CONCAT("u",users.id) = privatemessages.recipient 
                where privatemessages.deleted != 1
                GROUP by privatemessages.id HAVING privatemessages.sender = "u'.$this->user->id.'" ORDER BY privatemessages.`time` DESC';

                $this->view_data["filter"] = "Sent";
                break;
            case 'marked':
            
                $sql = 'SELECT privatemessages.id, privatemessages.`status`, privatemessages.`deleted`, privatemessages.attachment, privatemessages.attachment_link, privatemessages.subject, privatemessages.conversation, privatemessages.sender, privatemessages.recipient, privatemessages.message, privatemessages.`time`, users.`userpic` as userpic_c, users.`userpic` as userpic_u , users.`email` as email_u , users.`email` as email_c , CONCAT(users.firstname," ", users.lastname) as sender_u, CONCAT(users.firstname," ", users.lastname) as sender_c
                FROM privatemessages
                LEFT JOIN users ON ( CONCAT("u",users.id) = privatemessages.sender )
                GROUP by privatemessages.id HAVING ( privatemessages.recipient = "u'.$this->user->id.'" )  AND privatemessages.`status`="Marked" ORDER BY privatemessages.`time` DESC LIMIT '.$limit.$max_value;
                //echo '<br>';
                $sql2 = 'SELECT privatemessages.id, privatemessages.`status`, privatemessages.`deleted`, privatemessages.attachment, privatemessages.attachment_link, privatemessages.subject, privatemessages.conversation, privatemessages.sender, privatemessages.recipient, privatemessages.message, privatemessages.`time`, users.`userpic` as userpic_c, users.`userpic` as userpic_u , users.`email` as email_u , users.`email` as email_c , CONCAT(users.firstname," ", users.lastname) as sender_u, CONCAT(users.firstname," ", users.lastname) as sender_c
                FROM privatemessages
                LEFT JOIN users ON ( CONCAT("u",users.id) = privatemessages.sender) 
                GROUP by privatemessages.id HAVING ( privatemessages.recipient = "u'.$this->user->id.'") AND privatemessages.`status`="Marked" ORDER BY privatemessages.`time` DESC';


                $this->view_data["filter"] = "Marked";
                break;
            case 'deleted':
                $sql = 'SELECT privatemessages.id, privatemessages.`status`, privatemessages.`deleted`, privatemessages.attachment, privatemessages.attachment_link, privatemessages.subject, privatemessages.conversation, privatemessages.sender, privatemessages.receiver_delete, privatemessages.recipient, privatemessages.message, privatemessages.`time`, users.`userpic` as userpic_c, users.`userpic` as userpic_u , users.`email` as email_u , users.`email` as email_c , CONCAT(users.firstname," ", users.lastname) as sender_u, CONCAT(users.firstname," ", users.lastname) as sender_c
                FROM privatemessages
                LEFT JOIN users ON CONCAT("u",users.id) = privatemessages.sender 
                WHERE (privatemessages.recipient = "u'.$this->user->id.'" AND privatemessages.receiver_delete = 1 ) OR (privatemessages.sender = "u'.$this->user->id.'" AND privatemessages.status = "deleted" )
                GROUP by privatemessages.id ORDER BY privatemessages.`time` DESC LIMIT '.$limit.$max_value;
            //echo '<br>';
                $sql2 = 'SELECT privatemessages.id, privatemessages.`status`, privatemessages.`deleted`, privatemessages.attachment, privatemessages.attachment_link, privatemessages.subject, privatemessages.conversation, privatemessages.sender, privatemessages.receiver_delete,privatemessages.recipient, privatemessages.message, privatemessages.`time`, users.`userpic` as userpic_c, users.`userpic` as userpic_u , users.`email` as email_u , users.`email` as email_c , CONCAT(users.firstname," ", users.lastname) as sender_u, CONCAT(users.firstname," ", users.lastname) as sender_c
                FROM privatemessages
                LEFT JOIN users ON CONCAT("u",users.id) = privatemessages.sender 
                WHERE (privatemessages.recipient = "u'.$this->user->id.'" AND privatemessages.receiver_delete = 1 ) OR (privatemessages.sender = "u'.$this->user->id.'" AND privatemessages.status = "deleted"  )
                GROUP by privatemessages.id ORDER BY privatemessages.`time` DESC';
                $this->view_data["filter"] = "Deleted";
                break;
            default:
                $sql = 'SELECT privatemessages.id, privatemessages.`status`, privatemessages.subject, privatemessages.attachment, privatemessages.attachment_link, privatemessages.message, privatemessages.sender, privatemessages.recipient, privatemessages.`time`, users.`userpic` as userpic_c, users.`userpic` as userpic_u , users.`email` as email_u , users.`email` as email_c , CONCAT(users.firstname," ", users.lastname) as sender_u, CONCAT(users.firstname," ", users.lastname) as sender_c
                FROM privatemessages
                LEFT JOIN users ON CONCAT("u",users.id) = privatemessages.sender OR CONCAT("c",users.id) = privatemessages.sender OR CONCAT("s",users.id) = privatemessages.sender
                GROUP by privatemessages.conversation HAVING privatemessages.recipient = "u'.$this->user->id.'" AND privatemessages.`status`="New" ORDER BY privatemessages.`time` DESC LIMIT '.$limit.$max_value;
                $this->view_data["filter"] = FALSE;
                break;
        }
        
        $query = $this->db->query($sql);
        $query2 = $this->db->query($sql2);
        $rows = $query2->num_rows();
        $this->view_data["message"] = array_filter($query->result());
        $this->view_data["message_rows"] = $rows;
        $this->view_data["message_list_page_next"] = $con+$max_value;
        $this->view_data["message_list_page_prev"] = $con-$max_value;
        
        $this->theme_view = 'ajax';
        $this->content_view = 'messages/accountowner_views/list';
    }
    function write($ajax = FALSE)
    {   
        if($_POST){
            /*echo '<pre>';
            print_r($_POST);
            print_r($_FILES);
            die;*/
            $config['upload_path'] = './files/media/';
            $config['encrypt_name'] = TRUE;
            $config['allowed_types'] = '*';

            $this->load->library('upload', $config);
            //$this->load->helper('notification');
            $this->load->library('email');

            unset($_POST['userfile']);
            unset($_POST['file-name']);

            unset($_POST['send']);
            unset($_POST['note-codable']);
            unset($_POST['files']);
            $message = $_POST['message'];


            if(isset($_POST['recipient']) && !empty($_POST['recipient']))
            {
                $receiver = User::find( substr($_POST['recipient'], 1, 9999));
                $receiveremail = $receiver->email;
                //echo '<pre>';print_r($user);die;
                /*foreach($_POST['recipient'] as $key => $value)
                {
                    if(!empty($value))
                    {
                        $receiverart = substr($value, 0, 1);
                        $receiverid = substr($value, 1, 9999);
                        
                        if( $receiverart == "c"){
                            $receiver = Client::find($receiverid);
                            $receiveremail[] = $receiver->email;
                        }
                        elseif($receiverart == "s"){
                            $receiver = User::find($receiverid);
                            $receiveremail[] = $receiver->email;
                        }
                    }
                }*/
            }
            //echo '<pre>';print_r($receiveremail);die;
            $attachment = FALSE;
            if ( ! $this->upload->do_upload())
            {
                $error = $this->upload->display_errors('', ' ');
                if($error != "You did not select a file to upload."){
                    //$this->session->set_flashdata('message', 'error:'.$error);
                }
            }
            else
            {
                $data = array('upload_data' => $this->upload->data());
                $_POST['attachment'] = $data['upload_data']['orig_name'];
                $_POST['attachment_link'] = $data['upload_data']['file_name'];
                $attachment = $data['upload_data']['file_name'];
            }

            $_POST = array_map('htmlspecialchars', $_POST);
            $_POST['message'] = $message;
            $_POST['time'] = date('Y-m-d H:i', time());
            $_POST['sender'] = "u".$this->user->id;
            $_POST['status'] = "New";
            if(!isset($_POST['conversation'])){$_POST['conversation'] = random_string('sha1');}

            if(isset($_POST['previousmessage'])){

                $status = Privatemessage::find_by_id($_POST['previousmessage']);
                if($receiveremail == $this->user->email){
                    
                    //print_r($status);die;
                    $receiverart = substr($status->recipient, 0, 1);
                    $receiverid = substr($status->recipient, 1, 9999);
                    $_POST['recipient'] = $status->recipient;

                    if( $receiverart == "c"){
                        $receiver = User::find($receiverid);
                        $receiveremail = $receiver->email;
                    }
                    elseif($receiverart == "s"){
                        $receiver = User::find($receiverid);
                        $receiveremail = $receiver->email;
                    }
                    else
                    {
                        $receiver = User::find($receiverid);
                        $receiveremail = $receiver->email;   
                    }
                }
                
                $status->status = 'Replied';
                $status->save();
                unset($_POST['previousmessage']);
            }
            $message = Privatemessage::create($_POST);
            if(!$message){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_write_message_error'));}
            else{
                    $this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_write_message_success'));
                    //$this->load->helper('notification');
                    //send_notification($receiveremail, $this->lang->line('application_notification_new_message_subject'), $this->lang->line('application_notification_new_message').'<br><hr style="border-top: 1px solid #CCCCCC; border-left: 1px solid whitesmoke; border-bottom: 1px solid whitesmoke;"/>'.$_POST['message'].'<hr style="border-top: 1px solid #CCCCCC; border-left: 1px solid whitesmoke; border-bottom: 1px solid whitesmoke;"/>', $attachment);

                    //$this->email->from($this->sessionArr['email'], $this->sessionArr['company_name']);
					$this->email->from($this->settings->from_email_id,$this->settings->from_email_name);
                    $this->email->to($value->email);
                    //$this->email->to('nikhil@kgntechnologies.com');
                    //$this->email->to('emailtestersix@gmail.com');
					
                    $this->email->subject($this->lang->line('application_notification_new_message_subject'));
                    $this->email->message($this->lang->line('application_notification_new_message').'<br><hr style="border-top: 1px solid #CCCCCC; border-left: 1px solid whitesmoke; border-bottom: 1px solid whitesmoke;"/>'.$_POST['message'].'<hr style="border-top: 1px solid #CCCCCC; border-left: 1px solid whitesmoke; border-bottom: 1px solid whitesmoke;"/>');
                    if($this->email->send()) {
                        echo $mail_sent = 'Message mail sent.';
                    }
                }
            if($ajax != "reply"){ redirect('aomessages'); }
            else{
                    $this->theme_view = 'ajax';
                }
            redirect('aomessages/');
        }else
        {
             $clients = $this->db->query('SELECT DISTINCT(u.id), u.firstname, u.lastname, r.role_id FROM users u JOIN user_roles r ON u.id = r.user_id WHERE (r.role_id =3 OR r.role_id = 4) and r.company_id="'.$this->sessionArr['company_id'].'" and u.status="active"')->result_array();
            if(!empty($clients))
            {
                $client_arr=array();
                $j=0;
                foreach($clients as $newclient)
                {
                    if($newclient['role_id']==4)
                    {
                        $client_arr['Sub-contractors'][$j]['id'] = $newclient['id'];
                        $client_arr['Sub-contractors'][$j]['firstname'] = $newclient['firstname'];
                        $client_arr['Sub-contractors'][$j]['lastname'] = $newclient['lastname'];
                    }
                    else
                    {
                        $client_arr['Clients'][$j]['id'] = $newclient['id'];
                        $client_arr['Clients'][$j]['firstname'] = $newclient['firstname'];
                        $client_arr['Clients'][$j]['lastname'] = $newclient['lastname'];    
                    }
                    $j++;
                }
            }

            if($this->user->admin == 0) {
                $this->view_data['companies'] = Company::find_by_sql("SELECT c.* FROM companies c LEFT JOIN user_roles r ON c.id = r.company_id WHERE c.user_id = '" . $this->user->id . "' AND r.role_id = '" . $this->sessionArr['role_id'] . "' and c.inactive='0'");
            } else {
                $this->view_data['companies'] = Company::find('all', array('conditions' => array('inactive=?', '0')));
            }

            $this->view_data['clients'] = $client_arr;
            
           /* echo 'reach here<pre>';
            print_r($this->view_data['clients']);
            //$this->view_data['users'] = User::find('all',array('conditions' => array('status=?','active')));
            die;*/
            $this->theme_view = 'modal';
            $this->view_data['title'] = $this->lang->line('application_write_message');
            $this->view_data['form_action'] = base_url().'aomessages/write';
            $this->content_view = 'messages/accountowner_views/_messages';
        }   
    }   
    function update($id = FALSE, $getview = FALSE)
    {   
        if($_POST){
            unset($_POST['send']);
            unset($_POST['_wysihtml5_mode']);
            unset($_POST['files']);
            $id = $_POST['id'];
            $message = Privatemessage::find($id);
            $message->update_attributes($_POST);
            if(!$message){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_write_message_error'));}
            else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_write_message_success'));}
            if(isset($view)){redirect('messages/accountowner_views/view/'.$id);}else{redirect('messages');}
            
        }else
        {
            $this->view_data['id'] = $id;
            $this->theme_view = 'modal';
            $this->view_data['title'] = $this->lang->line('application_edit_message');
            $this->view_data['form_action'] = 'messages/accountowner_views/update';
            $this->content_view = 'messages/_messages_update';
        }   
    }
    function delete($id = FALSE)
    {   
        
        $message = Privatemessage::find_by_id($id);
        if($this->user->id == substr($message->recipient, 1, 9999))
        {
            $message->receiver_delete = '1';
        }
        else
        {
            $message->status = 'deleted';
            $message->deleted = '1';
        }
        $message->save();

        $this->content_view = 'messages/accountowner_views/all';
        if(!$message){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_delete_message_error'));}
            else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_delete_message_success'));}
            redirect('aomessages');
    }   
    function mark($id = FALSE)
    {   
        $message = Privatemessage::find_by_id($id);
        if($message->status == 'Marked'){
            $message->status = 'Read';
        }else{
            $message->status = 'Marked';
        }
        $message->save();
        $this->content_view = 'messages/accountowner_views/all';
        
    }
    function attachment($id = FALSE){
                $this->load->helper('download');
                $this->load->helper('file');

        $attachment = Privatemessage::find_by_id($id);

        $file = './files/media/'.$attachment->attachment_link;
        $mime = get_mime_by_extension($file);

        if(file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: '.$mime);
            header('Content-Disposition: attachment; filename='.basename($attachment->attachment));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            ob_clean();
            flush();
            exit; 
        }
    }
    function view($id = FALSE, $filter = FALSE, $additional = FALSE)
    {
        $this->view_data['submenu'] = array(
                        $this->lang->line('application_back') => 'aomessages',
                        );  
        $message = Privatemessage::find_by_id($id);
        //echo $filter;
        //echo '<pre>';print_r($message);die;
        $this->view_data["count"] = "1";
        if(!$filter || $filter == "Marked"){
            // echo 'reach';
                if($message->status == "New"){
                    $message->status = 'Read';
                    $message->save();
                }
                $this->view_data["filter"] = FALSE;
                $sql = 'SELECT privatemessages.id, privatemessages.conversation FROM privatemessages
                        WHERE ( privatemessages.recipient = "u'.$this->user->id.'" OR privatemessages.sender = "u'.$this->user->id.'") AND privatemessages.`id`="'.$id.'"';
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
               /* echo '<pre>';
                print_r($query2);*/
                
                $this->view_data["conversation"] = array_filter($query2->result());
                $this->view_data["count"] = count ($this->view_data["conversation"]);
            }else{
                if($filter == 'Deleted')
                {
                    $sql = 'SELECT privatemessages.id, privatemessages.`status`, privatemessages.`deleted`, privatemessages.conversation, privatemessages.attachment, privatemessages.attachment_link,privatemessages.receiver_delete, privatemessages.subject, privatemessages.message, privatemessages.sender, privatemessages.recipient, privatemessages.`time`, privatemessages.`sender` , users.`userpic` as userpic_c, clients.`userpic` as userpic_u , users.`email` as email_u , clients.`email` as email_c , CONCAT(users.firstname," ", users.lastname) as sender_u, CONCAT(clients.firstname," ", clients.lastname) as sender_c, CONCAT(users.firstname," ", users.lastname) as recipient_u, CONCAT(clients.firstname," ", clients.lastname) as recipient_c
                        FROM privatemessages
                        LEFT JOIN users AS clients ON (CONCAT("u",clients.id) = privatemessages.recipient) 
                        LEFT JOIN users ON (CONCAT("u",users.id) = privatemessages.sender) 
                        GROUP by privatemessages.id HAVING privatemessages.id = "'.$id.'" AND (privatemessages.sender = "u'.$this->user->id.'" OR privatemessages.recipient = "u'.$this->user->id.'") ORDER BY privatemessages.`id` DESC LIMIT 100';
               
                }else{
                    if($filter == "Sent"){
                         $sql = 'SELECT privatemessages.id, privatemessages.`status`, privatemessages.`deleted`, privatemessages.conversation, privatemessages.attachment, privatemessages.attachment_link, privatemessages.subject, privatemessages.message, privatemessages.sender, privatemessages.receiver_delete, privatemessages.recipient, privatemessages.`time`, privatemessages.`sender` , u1.`userpic` as userpic_c, users.`userpic` as userpic_u , users.`email` as email_u , u1.`email` as email_c , CONCAT(users.firstname," ", users.lastname) as sender_u, CONCAT(u1.firstname," ", u1.lastname) as sender_c, CONCAT(users.firstname," ", users.lastname) as recipient_u, CONCAT(u1.firstname," ", u1.lastname) as recipient_c
                        FROM privatemessages
                        LEFT JOIN users AS u1 ON CONCAT("u",u1.id) = privatemessages.recipient 
                        LEFT JOIN users ON  CONCAT("u",users.id) = privatemessages.sender 
                        GROUP by privatemessages.id HAVING privatemessages.id = "'.$id.'" AND privatemessages.sender = "u'.$this->user->id.'" ORDER BY privatemessages.`id` DESC LIMIT 100';
                        
                            $receiverart = substr($additional, 0, 1);
                            $receiverid = substr($additional, 1, 9999);

                            if( $receiverart == "u"){
                                $receiver = User::find($receiverid);
                                $this->view_data["recipient"] = $receiver->firstname.' '.$receiver->lastname;
                                
                            }
                            elseif( $receiverart == "c"){
                                $receiver = User::find($receiverid);
                                $this->view_data["recipient"] = $receiver->firstname.' '.$receiver->lastname;
                                
                            }else{
                                $receiver = User::find($receiverid);
                                $this->view_data["recipient"] = $receiver->firstname.' '.$receiver->lastname;
                            }
                        
                    }else{
                        if(isset($message->conversation) && !empty($message->conversation))
                        {
                            $sql = 'SELECT privatemessages.id, privatemessages.`status`, privatemessages.`deleted`, privatemessages.conversation, privatemessages.attachment, privatemessages.attachment_link, privatemessages.subject, privatemessages.message, privatemessages.sender,privatemessages.receiver_delete, privatemessages.recipient, privatemessages.`time`, privatemessages.`sender` , clients.`userpic` as userpic_c, users.`userpic` as userpic_u , users.`email` as email_u , clients.`email` as email_c , CONCAT(users.firstname," ", users.lastname) as sender_u, CONCAT(users.firstname," ", users.lastname) as sender_c, CONCAT(clients.firstname," ", clients.lastname) as recipient_u, CONCAT(clients.firstname," ", clients.lastname) as recipient_c
                        FROM privatemessages
                        LEFT JOIN users AS clients ON (CONCAT("c",clients.id) = privatemessages.recipient) OR (CONCAT("s",clients.id) = privatemessages.recipient) OR (CONCAT("u",clients.id) = privatemessages.recipient)
                        LEFT JOIN users ON (CONCAT("u",users.id) = privatemessages.sender) OR (CONCAT("c",users.id) = privatemessages.sender) OR (CONCAT("s",users.id) = privatemessages.sender)
                        GROUP by privatemessages.id HAVING privatemessages.conversation = "'.$message->conversation.'" AND (privatemessages.sender = "u'.$this->user->id.'" OR privatemessages.recipient = "u'.$this->user->id.'") ORDER BY privatemessages.`id` DESC LIMIT 100';
                        }
                        else
                        {
                            $sql = 'SELECT privatemessages.id, privatemessages.`status`, privatemessages.`deleted`, privatemessages.conversation, privatemessages.attachment, privatemessages.attachment_link, privatemessages.subject, privatemessages.message, privatemessages.sender,privatemessages.receiver_delete, privatemessages.recipient, privatemessages.`time`, privatemessages.`sender` , clients.`userpic` as userpic_c, users.`userpic` as userpic_u , users.`email` as email_u , clients.`email` as email_c , CONCAT(users.firstname," ", users.lastname) as sender_u, CONCAT(users.firstname," ", users.lastname) as sender_c, CONCAT(clients.firstname," ", clients.lastname) as recipient_u, CONCAT(clients.firstname," ", clients.lastname) as recipient_c
                        FROM privatemessages
                        LEFT JOIN users AS clients ON (CONCAT("c",clients.id) = privatemessages.recipient) OR (CONCAT("s",clients.id) = privatemessages.recipient) OR (CONCAT("u",clients.id) = privatemessages.recipient)
                        LEFT JOIN users ON (CONCAT("u",users.id) = privatemessages.sender) OR (CONCAT("c",users.id) = privatemessages.sender) OR (CONCAT("s",users.id) = privatemessages.sender)
                        GROUP by privatemessages.id HAVING privatemessages.id = "'.$id.'" AND (privatemessages.sender = "u'.$this->user->id.'" OR privatemessages.recipient = "u'.$this->user->id.'") ORDER BY privatemessages.`id` DESC LIMIT 100';
                        }
                    }
                  }
                $query = $this->db->query($sql);
                
                $this->view_data["conversation"] = array_filter($query->result());
                $this->view_data["count"] = count ($this->view_data["conversation"]);
                $this->view_data["filter"] = $filter;
            }
        $this->theme_view = 'ajax';
        $this->view_data['form_action'] = 'aomessages/write';
        $this->view_data['id'] = $id;
        $this->content_view = 'messages/accountowner_views/view';
    }
}

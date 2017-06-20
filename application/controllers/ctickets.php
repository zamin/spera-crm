<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ctickets extends MY_Controller {
               
	function __construct()
	{
		parent::__construct();
		
        if(!$this->user){
			$this->view_data['error'] = "true";
            $this->session->set_flashdata('message', 'error: You have no access to any modules!');
            redirect('login');
		}
        if(!$this->sessionArr['company_id']) {
            $this->view_data['error'] = "true";
            $this->session->set_flashdata('message', 'error: You have no access to any modules!');
            redirect('login');
        }
        $this->load->database();
       
        
		$ticketsCount = Ticket::find_by_sql('SELECT count(*) as c FROM tickets t
                                            INNER JOIN ticket_assignment ta ON ta.ticket_id = t.id
                                            WHERE t.company_id = "' . $this->sessionArr['company_id'] . '" AND ta.user_id = "' . $this->sessionArr['user_id'] . '"');
        $this->view_data['tickets_assigned_to_me'] = $ticketsCount[0]->c;
        
        $ticketsCount = Ticket::find_by_sql('SELECT count(*) as c FROM tickets t WHERE t.company_id = "' . $this->sessionArr['company_id'] . '" AND t.user_id="'.$this->sessionArr['user_id'].'"');
        $this->view_data['tickets_in_my_queue'] = $ticketsCount[0]->c;
	}
	function index()
	{
        
        $cid = $this->sessionArr['company_id'];
        if(empty($cid))
        {
            $this->view_data['error'] = "true";
            $this->session->set_flashdata('message', 'error: You have no access to any modules!');
            redirect('login');
        }
        $this->view_data['ticketStatus']='All';
		$tickets = Ticket::find_by_sql('
                                        SELECT tt.*,q.name as queue_name FROM (
                                                SELECT t.* FROM tickets t
                                                INNER JOIN ticket_assignment ta ON ta.ticket_id = t.id
                                                WHERE t.company_id = "' . $this->sessionArr['company_id'] . '" AND ta.user_id = "' . $this->sessionArr['user_id'] . '"
                                                
                                                UNION
                                                
                                                SELECT t.* FROM tickets t
                                                WHERE t.company_id = "' . $this->sessionArr['company_id'] . '" AND t.user_id="'.$this->sessionArr['user_id'].'"
                                            ) as tt
                                            LEFT JOIN queues as q on q.id = tt.queue_id
                                        order by tt.id desc 
                                        ');
		if(!empty($tickets))
        {   
            $ticket_all =array();
            $i=0;
            foreach($tickets as $key =>$value)
            {
                //echo "<pre>";var_dump($value->id);
                $ticket_all[$i]['id']=$value->id;
                $ticket_all[$i]['subject']=$value->subject;
                $ticket_all[$i]['reference']=$value->reference;
                //$ticket_all[$i]['text']=$value->text;
                $ticket_all[$i]['status']=$value->status;
                $ticket_all[$i]['queue_name']=$value->queue_name;
                $ticket_all[$i]['user_id']=$value->user_id;
                
                $assign_user_details=$this->db->query('SELECT u.* FROM ticket_assignment ta
                                                       INNER JOIN users u ON ta.user_id = u.id
                                                       WHERE ta.ticket_id = "'.$value->id.'" AND u.status="active"')->result_array();
                                                       
                if(!empty($assign_user_details))
                {
                    $j=0;
                    foreach ($assign_user_details as $key1 => $value1) 
                    {
                        $ticket_all[$i]['assign_user'][$j]['user_id'] = $value1['id'];
                        $ticket_all[$i]['assign_user'][$j]['firstname'] = $value1['firstname'];
                        $ticket_all[$i]['assign_user'][$j]['lastname'] = $value1['lastname'];
                        $ticket_all[$i]['assign_user'][$j]['email'] = $value1['email'];
                        $ticket_all[$i]['assign_user'][$j]['userpic'] = $value1['userpic'];
                        $j++;
                    }
                }
                $i++;
            }
        }
        $this->view_data['ticket']=$ticket_all;
		$this->content_view = 'tickets/c_view/all';
		
		
		
	}
	function queues($id)
	{
		if($this->user->admin == 0){ 
			$comp_array = array();
			$thisUserHasNoCompanies = (array) $this->user->companies;
					if(!empty($thisUserHasNoCompanies)){
				foreach ($this->user->companies as $value) {
					array_push($comp_array, $value->id);
				}
				if($this->user->queue == $id){

					$options = array('conditions' => array('status != ? AND queue_id = ? ',"closed", $id));
				}else{
				$options = array('conditions' => array('status != ? AND queue_id = ? AND company_id in (?)',"closed", $id, $comp_array));
				}
			}else{
				if($this->user->queue == $id){
					$options = array('conditions' => array('status != ? AND queue_id = ? ',"closed", $id));
				}else{
					$options = array('conditions' => array('status != ? AND queue_id = ? AND user_id = ?',"closed", $id, $this->user->id));
				}
				
			}
		}else{
			$options = array('conditions' => array('status != "closed" AND queue_id = '.$id));
			$this->view_data['queues'] = Queue::find('all',array('conditions' => array('inactive=?','0')));
		}
		
		$this->view_data['ticketFilter'] = $this->lang->line('application_all');
		$this->view_data['activeQueue'] = Queue::find_by_id($id);
		$this->view_data['queues'] = Queue::find('all',array('conditions' => array('inactive=?','0')));
		$this->view_data['ticket'] = Ticket::find('all', $options);
		$this->content_view = 'tickets/all';
	}
	function filter($condition)
	{
		$cid = $this->sessionArr['company_id'];
        if(empty($cid))
        {
            $this->view_data['error'] = "true";
            $this->session->set_flashdata('message', 'error: You have no access to any modules!');
            redirect('login');
        }
        switch ($condition) {
            case 'all':
                $option = '';
                $this->view_data['ticketStatus']='All';
                break;
            case 'open':
                $option = ' AND t.status = "open"';
                $this->view_data['ticketStatus']='open';
                break;
            case 'onhold':
                $option = ' AND t.status = "onhold"';
                $this->view_data['ticketStatus']='onhold';
                break;
            case 'inprogress':
                $option = ' AND t.status = "inprogress"';
                $this->view_data['ticketStatus']='inprogress';
                break;
            case 'reopened':
                $option = ' AND t.status = "reopened"';
                $this->view_data['ticketStatus']='reopened';
                break;
            case 'closed':
                $option = ' AND t.status = "closed"';
                $this->view_data['ticketStatus']='closed';
                break;
        }
        
        $tickets = Ticket::find_by_sql('
                                        SELECT tt.*,q.name as queue_name FROM (
                                                SELECT t.* FROM tickets t
                                                INNER JOIN ticket_assignment ta ON ta.ticket_id = t.id
                                                WHERE t.company_id = "' . $this->sessionArr['company_id'] . '" AND ta.user_id = "' . $this->sessionArr['user_id'] . '"
                                                '.$option.'
                                                
                                                UNION
                                                
                                                SELECT t.* FROM tickets t
                                                WHERE t.company_id = "' . $this->sessionArr['company_id'] . '" AND t.user_id="'.$this->sessionArr['user_id'].'"
                                                '.$option.'
                                            ) as tt
                                            LEFT JOIN queues as q on q.id = tt.queue_id
                                        order by tt.id desc 
                                        ');
        if(!empty($tickets))
        {   
            $ticket_all =array();
            $i=0;
            foreach($tickets as $key =>$value)
            {
                //echo "<pre>";var_dump($value->id);
                $ticket_all[$i]['id']=$value->id;
                $ticket_all[$i]['subject']=$value->subject;
                $ticket_all[$i]['reference']=$value->reference;
                //$ticket_all[$i]['text']=$value->text;
                $ticket_all[$i]['status']=$value->status;
                $ticket_all[$i]['queue_name']=$value->queue_name;
                
                $assign_user_details=$this->db->query('SELECT u.* FROM ticket_assignment ta
                                                       INNER JOIN users u ON ta.user_id = u.id
                                                       WHERE ta.ticket_id = "'.$value->id.'" AND u.status="active"')->result_array();
                                                       
                if(!empty($assign_user_details))
                {
                    $j=0;
                    foreach ($assign_user_details as $key1 => $value1) 
                    {
                        $ticket_all[$i]['assign_user'][$j]['user_id'] = $value1['id'];
                        $ticket_all[$i]['assign_user'][$j]['firstname'] = $value1['firstname'];
                        $ticket_all[$i]['assign_user'][$j]['lastname'] = $value1['lastname'];
                        $ticket_all[$i]['assign_user'][$j]['email'] = $value1['email'];
                        $ticket_all[$i]['assign_user'][$j]['userpic'] = $value1['userpic'];
                        $j++;
                    }
                }
                $i++;
            }
        }
        $this->view_data['ticket']=$ticket_all;
        $this->content_view = 'tickets/c_view/all';
	}
	function create()
	{	
		if($_POST){
			$config['upload_path'] = './files/media/';
			$config['encrypt_name'] = TRUE;
			$config['allowed_types'] = '*';

			$this->load->library('upload', $config);
			$this->load->helper('notification');

			unset($_POST['userfile']);
			unset($_POST['file-name']);

			unset($_POST['send']);
			unset($_POST['_wysihtml5_mode']);
			unset($_POST['files']);
            
            $assign_user_id = $_POST['assign_user_id'];
            unset($_POST['assign_user_id']);

			$user = User::find_by_id($this->sessionArr['user_id']);
			$_POST['from'] = $this->user->firstname.' '.$this->user->lastname.' - '.$this->user->email;
			$_POST['company_id'] = $this->sessionArr['company_id'];
            $_POST['user_id'] = $this->sessionArr['user_id'];
			
			$_POST['created'] = time();
			$_POST['updated'] = "1";
			$_POST['subject'] = htmlspecialchars($_POST['subject']);
			$ticket_reference = Setting::first();
			$_POST['reference'] = $ticket_reference->ticket_reference;
			$_POST['status'] = $ticket_reference->ticket_default_status;
            
            $ticket = Ticket::create($_POST);
            
            $new_ticket_reference = $_POST['reference']+1;			
			$ticket_reference->update_attributes(array('ticket_reference' => $new_ticket_reference));
			$email_attachment = false;
			if ( ! $this->upload->do_upload())
			{
				$error = $this->upload->display_errors('', ' ');
				$this->session->set_flashdata('message', 'error:'.$error);

			}
			else
			{
				$data = array('upload_data' => $this->upload->data());

				$attributes = array('ticket_id' => $ticket->id, 'filename' => $data['upload_data']['orig_name'], 'savename' => $data['upload_data']['file_name']);
				$attachment = TicketHasAttachment::create($attributes);
				$email_attachment = $data['upload_data']['file_name'];
			}
            if (!empty($assign_user_id)) 
            {
                $delete_ticket_assignment = "DELETE from ticket_assignment where ticket_id ='" . $ticket->id . "'";
                $this->db->query($delete_ticket_assignment);
                $assign_arr = count($assign_user_id);
                
                $get_company_details=$this->db->query('select * from company_details where company_id="'.$this->sessionArr['company_id'].'"')->row_array();
                    if(!empty($get_company_details))
                    {
                        $c_logo=$get_company_details['logo'];
                        if(!empty($c_logo))
                        {
                            $company_logo=site_url().$c_logo;
                        }
                        else
                        {
                            $company_logo=site_url().'files/media/FC2_logo_dark.png';
                        }
                    }
                    else
                    {
                        $company_logo=site_url().'files/media/FC2_logo_dark.png';
                    }
                for ($i = 0; $i < $assign_arr; $i++) {
                    $assign_id = $assign_user_id[$i];
                    $newArr = array('ticket_id' => $ticket->id, 'user_id' => $assign_id);
                    $insert_data = TicketAssign::create($newArr);
                    
                    $set_page = $this->authenticateuser_by_id( $assign_id, $this->sessionArr['company_id']);
                    $ticket_url = base_url().$set_page."/view/".$ticket->id;
                    $user = User::find_by_id($assign_id);
                    
                    send_ticket_notification($user->email, '[Ticket#'.$ticket->reference.'] - '.$_POST['subject'], $_POST['message'], $ticket->id, $ticket_url,$company_logo);
                }
            }

       		if(!$ticket){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_create_ticket_error'));
       			redirect('ctickets');
       		}
       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_create_ticket_success'));
				//redirect('ctickets/view/'.$ticket->id);
                redirect('ctickets');
       		}
			
		}else
		{
			$clients = $this->db->query('SELECT DISTINCT(u.id), u.firstname, u.lastname, r.role_id FROM users u JOIN user_roles r ON u.id = r.user_id WHERE r.company_id="'.$this->sessionArr['company_id'].'" and u.status="active" AND u.id != '.$this->sessionArr['user_id'].' order by r.role_id')->result_array();
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
                    else if($newclient['role_id']==2)
                    {
                        $client_arr['Account-Owner'][$j]['id'] = $newclient['id'];
                        $client_arr['Account-Owner'][$j]['firstname'] = $newclient['firstname'];
                        $client_arr['Account-Owner'][$j]['lastname'] = $newclient['lastname'];    
                    }
                    else if($newclient['role_id']==3)
                    {
                        $client_arr['Clients'][$j]['id'] = $newclient['id'];
                        $client_arr['Clients'][$j]['firstname'] = $newclient['firstname'];
                        $client_arr['Clients'][$j]['lastname'] = $newclient['lastname'];    
                    }
                    $j++;
                }
            }
            $this->view_data['users']=$client_arr;
			$this->view_data['queues'] = Queue::find('all',array('conditions' => array('inactive=?','0')));
			$this->view_data['types'] = Type::find('all',array('conditions' => array('inactive=?','0')));
			$this->view_data['settings'] = Setting::first();

			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_create_ticket');
			$this->view_data['form_action'] = base_url().'ctickets/create';
            $this->content_view = 'tickets/c_view/_ticket';
		}	
	}
    
    function update($id = FALSE) {
       /* $t_assign_query_new = 'SELECT assign_user_id from project_assign_clients where project_id="' . $id . '"';
        $task_assign_clients_new = $this->db->query($t_assign_query)->result_array();*/
        if ($_POST) {
            $config['upload_path'] = './files/media/';
            $config['encrypt_name'] = TRUE;
            $config['allowed_types'] = '*';

            $this->load->library('upload', $config);
            $this->load->helper('notification');

            unset($_POST['userfile']);
            unset($_POST['file-name']);

            unset($_POST['send']);
            unset($_POST['_wysihtml5_mode']);
            unset($_POST['files']);
            
            $assign_user_id = $_POST['assign_user_id'];
            unset($_POST['assign_user_id']);

            $user = User::find_by_id($this->sessionArr['user_id']);
            
            $_POST['from'] = $this->user->firstname.' '.$this->user->lastname.' - '.$this->user->email;
            $_POST['company_id'] = $this->sessionArr['company_id'];
            $_POST['user_id'] = $this->sessionArr['user_id'];
            
            $_POST['created'] = time();
            $_POST['updated'] = "1";
            $_POST['subject'] = htmlspecialchars($_POST['subject']);
            
            $ticket = Ticket::find($id);
            $ticket->update_attributes($_POST);
            
            $email_attachment = false;
            if ( ! $this->upload->do_upload())
            {
                $error = $this->upload->display_errors('', ' ');
                $this->session->set_flashdata('message', 'error:'.$error);
            }
            else
            {
                $data = array('upload_data' => $this->upload->data());
                $Assignedclients = $this->db->query('SELECT * FROM ticket_has_attachments WHERE ticket_id = '.$id)->result_array();
                if(!empty($Assignedclients))
                {
                    $this->db->query('UPDATE ticket_has_attachments SET filename="'.$data['upload_data']['orig_name'].'",savename="'.$data['upload_data']['file_name'].'" WHERE ticket_id = '.$id);
                }
                else
                {
                    $attributes = array('ticket_id' => $ticket->id, 'filename' => $data['upload_data']['orig_name'], 'savename' => $data['upload_data']['file_name']);
                    $attachment = TicketHasAttachment::create($attributes);
                    $email_attachment = $data['upload_data']['file_name'];
                }
            }
            if (!empty($assign_user_id)) 
            {
                $assign_arr = count($assign_user_id);
                for ($i = 0; $i < $assign_arr; $i++) {
                    
                    $assign_id = $assign_user_id[$i];
                    $Assignedclients = $this->db->query('SELECT * FROM ticket_assignment WHERE user_id = '.$assign_id.' AND ticket_id = '.$id)->result_array();
                    if ( empty($Assignedclients) )
                    {
                        $newArr = array('ticket_id' => $ticket->id, 'user_id' => $assign_id);
                        $insert_data = TicketAssign::create($newArr);
                        
                        $set_page = $this->authenticateuser_by_id( $assign_id, $this->sessionArr['company_id']);
                        $ticket_url = base_url().$set_page."/view/".$ticket->id;
                        $user = User::find_by_id($assign_id);
                        
                        $get_company_details=$this->db->query('select * from company_details where company_id="'.$this->sessionArr['company_id'].'"')->row_array();
                        if(!empty($get_company_details))
                        {
                            $c_logo=$get_company_details['logo'];
                            if(!empty($c_logo))
                            {
                                $company_logo=site_url().$c_logo;
                            }
                            else
                            {
                                $company_logo=site_url().'files/media/FC2_logo_dark.png';
                            }
                        }
                        else
                        {
                            $company_logo=site_url().'files/media/FC2_logo_dark.png';
                        }
                        send_ticket_notification($user->email, '[Ticket#'.$ticket->reference.'] - '.$_POST['subject'], $_POST['message'], $ticket->id, $ticket_url,$company_logo);
                    }
                }
            }

               if(!$ticket){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_create_ticket_error'));
                   redirect('ctickets');
               }
               else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_create_ticket_success'));
                  //redirect('ctickets/view/'.$ticket->id);
                  redirect('ctickets');
               }
        } else {
            
            $this->view_data['ticket'] = Ticket::find_by_id($id);
            
            $this->view_data['attachments'] = $this->db->query('SELECT * FROM ticket_has_attachments WHERE ticket_id = '.$id)->result_array();
            
            $Assignedclients = $this->db->query('SELECT * FROM ticket_assignment WHERE ticket_id = '.$id)->result_array();
            if(!empty($Assignedclients))
            {
                $Assignedclient_arr=array();
                $j=0;
                foreach($Assignedclients as $newclient)
                {
                    $Assignedclient_arr[] = $newclient['user_id'];
                    $j++;
                }
            }
            $this->view_data['task_assign_users']=$Assignedclient_arr;
            
            $clients = $this->db->query('SELECT DISTINCT(u.id), u.firstname, u.lastname, r.role_id FROM users u JOIN user_roles r ON u.id = r.user_id WHERE r.company_id="'.$this->sessionArr['company_id'].'" and u.status="active" AND u.id != '.$this->sessionArr['user_id'].' order by r.role_id')->result_array();
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
                    else if($newclient['role_id']==2)
                    {
                        $client_arr['Account-Owner'][$j]['id'] = $newclient['id'];
                        $client_arr['Account-Owner'][$j]['firstname'] = $newclient['firstname'];
                        $client_arr['Account-Owner'][$j]['lastname'] = $newclient['lastname'];    
                    }
                    else if($newclient['role_id']==3)
                    {
                        $client_arr['Clients'][$j]['id'] = $newclient['id'];
                        $client_arr['Clients'][$j]['firstname'] = $newclient['firstname'];
                        $client_arr['Clients'][$j]['lastname'] = $newclient['lastname'];    
                    }
                    $j++;
                }
            }
            $this->view_data['users']=$client_arr;
            $this->view_data['queues'] = Queue::find('all',array('conditions' => array('inactive=?','0')));
            $this->view_data['types'] = Type::find('all',array('conditions' => array('inactive=?','0')));
            $this->view_data['settings'] = Setting::first();

            $this->theme_view = 'modal';
            $this->view_data['title'] = $this->lang->line('application_create_ticket');
            $this->view_data['form_action'] = base_url().'ctickets/update/'.$id;
            $this->content_view = 'tickets/c_view/_ticket';
        }
    }
    
	function assign($id = FALSE)
	{	
        $cid = $this->sessionArr['company_id'];
		$this->load->helper('notification');
		if($_POST){
			unset($_POST['send']);
			unset($_POST['_wysihtml5_mode']);
			$id = $_POST['id'];
			unset($_POST['id']);
			unset($_POST['files']);
			$user = User::find_by_id($_POST['user_id']);
			$assign = Ticket::find_by_id($id);
			$attr = array('user_id' => $_POST['user_id']);
			$assign->update_attributes($attr);
            
            $set_page = $this->authenticateuser_by_id( $_POST['user_id'], $cid);
            $ticket_url = base_url().$set_page."/view/".$id;
            
            $get_company_details=$this->db->query('select * from company_details where company_id="'.$this->sessionArr['company_id'].'"')->row_array();
            if(!empty($get_company_details))
            {
                $c_logo=$get_company_details['logo'];
                if(!empty($c_logo))
                {
                    $company_logo=site_url().$c_logo;
                }
                else
                {
                    $company_logo=site_url().'files/media/FC2_logo_dark.png';
                }
            }
            else
            {
                $company_logo=site_url().'files/media/FC2_logo_dark.png';
            }
            send_ticket_notification($user->email, '[Ticket#'.$assign->reference.'] - '.$_POST['subject'], $_POST['message'], $id, $ticket_url,$company_logo);
            
			unset($_POST['notify']);
			$_POST['subject'] = htmlspecialchars($_POST['subject']);
			$_POST['datetime'] = time();
			$_POST['from'] = $this->user->firstname." ".$this->user->lastname.' - '.$this->user->email;
			$_POST['reply_to'] = $this->user->email;
			$_POST['ticket_id'] = $id;
			$_POST['to'] = $_POST['user_id'];
			unset($_POST['user_id']);
			$article = TicketHasArticle::create($_POST);
       		if(!$assign){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_save_ticket_error'));}
       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_assign_ticket_success'));}
			redirect('ctickets/view/'.$id);
		}else
		{
			$clients = $this->db->query('
                                        select * from (
                                         SELECT DISTINCT(u.id), u.firstname, u.lastname, r.role_id 
                                         FROM ticket_assignment as ta
                                         INNER JOIN (users u JOIN user_roles r ON u.id = r.user_id ) on ta.user_id = u.id
                                         WHERE u.status="active" AND ta.ticket_id="'.$id.'"
                                         
                                         UNION 
                                         
                                         SELECT DISTINCT(u.id), u.firstname, u.lastname, r.role_id 
                                         FROM tickets as ta
                                         INNER JOIN (users u JOIN user_roles r ON u.id = r.user_id ) on ta.user_id = u.id
                                         WHERE u.status="active" AND ta.id="'.$id.'" AND r.company_id = '.$cid.'
                                         ) as tt
                                         where tt.id != '.$this->sessionArr['user_id'].'
                                         order by tt.role_id
                                         ')->result_array();
                                         
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
                    else if($newclient['role_id']==2)
                    {
                        $client_arr['Account-Owner'][$j]['id'] = $newclient['id'];
                        $client_arr['Account-Owner'][$j]['firstname'] = $newclient['firstname'];
                        $client_arr['Account-Owner'][$j]['lastname'] = $newclient['lastname'];    
                    }
                    else if($newclient['role_id']==3)
                    {
                        $client_arr['Clients'][$j]['id'] = $newclient['id'];
                        $client_arr['Clients'][$j]['firstname'] = $newclient['firstname'];
                        $client_arr['Clients'][$j]['lastname'] = $newclient['lastname'];    
                    }
                    $j++;
                }
            }
            $this->view_data['users']=$client_arr;
			$this->view_data['ticket'] = Ticket::find($id);
			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_assign_to_agents');
			$this->view_data['form_action'] = base_url().'ctickets/assign';
			$this->content_view = 'tickets/_assign';
		}	
	}
	function client($id = FALSE)
	{	
		$this->load->helper('notification');
		if($_POST){
			unset($_POST['send']);
			unset($_POST['_wysihtml5_mode']);
			unset($_POST['files']);
			$id = $_POST['id'];
			unset($_POST['id']);
			$client = Client::find_by_id($_POST['client_id']);
			$assign = Ticket::find_by_id($id);
			$attr = array('client_id' => $client->id, 'company_id' => $client->company->id);
			$assign->update_attributes($attr);

			if(isset($_POST['notify'])){
                
                $get_company_details=$this->db->query('select * from company_details where company_id="'.$this->sessionArr['company_id'].'"')->row_array();
                if(!empty($get_company_details))
                {
                    $c_logo=$get_company_details['logo'];
                    if(!empty($c_logo))
                    {
                        $company_logo=site_url().$c_logo;
                    }
                    else
                    {
                        $company_logo=site_url().'files/media/FC2_logo_dark.png';
                    }
                }
                else
                {
                    $company_logo=site_url().'files/media/FC2_logo_dark.png';
                }
			send_ticket_notification($client->email, '[Ticket#'.$assign->reference.'] - '.$_POST['subject'], $_POST['message'], $assign->id,$company_logo);
			$_POST['internal'] = "0";
			}
			unset($_POST['notify']);
			$_POST['subject'] = htmlspecialchars($_POST['subject']);
			$_POST['datetime'] = time();
			$_POST['from'] = $this->user->firstname." ".$this->user->lastname.' - '.$this->user->email;
			$_POST['reply_to'] = $this->user->email;
			$_POST['ticket_id'] = $id;
			$_POST['to'] = $_POST['client_id'];
			unset($_POST['client_id']);
			$article = TicketHasArticle::create($_POST);
       		if(!$assign){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_save_ticket_error'));}
       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_assign_ticket_success'));}
			redirect('tickets/view/'.$id);
		}else
		{
			if($this->user->admin != 1){
				$comp_array = array();
				foreach ($this->user->companies as $value) {
					array_push($comp_array, $value->id);
				}
				$this->view_data['clients'] = Client::find('all',array('conditions' => array('inactive=? AND company_id in (?)','0', $comp_array)));
			}else{
				$this->view_data['clients'] = Client::find('all',array('conditions' => array('inactive=?','0')));
			}
			$this->view_data['ticket'] = Ticket::find($id);
			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_client');
			$this->view_data['form_action'] = 'tickets/client';
			$this->content_view = 'tickets/_client';
		}	
	}
	function queue($id = FALSE)
	{	
		$this->load->helper('notification');
		if($_POST){
			unset($_POST['send']);
			unset($_POST['_wysihtml5_mode']);
			unset($_POST['files']);
			$id = $_POST['id'];
			unset($_POST['id']);
			$ticket = Ticket::find_by_id($id);
			$attr = array('queue_id' => $_POST['queue_id']);
			$ticket->update_attributes($attr);

       		if(!$ticket){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_assign_queue_error'));}
       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_assign_queue_success'));}
			redirect('ctickets/view/'.$id);
		}else
		{
			$this->view_data['queues'] = Queue::find('all',array('conditions' => array('inactive=?','0')));
			$this->view_data['ticket'] = Ticket::find_by_id($id);
			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_queue');
			$this->view_data['form_action'] = base_url().'ctickets/queue';
			$this->content_view = 'tickets/_queue';
		}	
	}
	function type($id = FALSE)
	{	
		$this->load->helper('notification');
		if($_POST){
			unset($_POST['send']);
			unset($_POST['_wysihtml5_mode']);
			unset($_POST['files']);
			$id = $_POST['id'];
			unset($_POST['id']);
			$ticket = Ticket::find_by_id($id);
			$attr = array('type_id' => $_POST['type_id']);
			$ticket->update_attributes($attr);

       		if(!$ticket){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_assign_type_error'));}
       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_assign_type_success'));}
			redirect('ctickets/view/'.$id);
		}else
		{
			$this->view_data['types'] = Type::find('all',array('conditions' => array('inactive=?','0')));
			$this->view_data['ticket'] = Ticket::find_by_id($id);
			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_type');
			$this->view_data['form_action'] = base_url().'ctickets/type';
			$this->content_view = 'tickets/_type';
		}	
	}	
	function status($id = FALSE)
	{	
		$this->load->helper('notification');
		if($_POST){
			unset($_POST['send']);
			unset($_POST['_wysihtml5_mode']);
			unset($_POST['files']);
			$id = $_POST['id'];
			unset($_POST['id']);
			$ticket = Ticket::find_by_id($id);
			$attr = array('status' => $_POST['status']);
			$ticket->update_attributes($attr);

       		if(!$ticket){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_status_error'));}
       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_status_success'));}
			redirect('ctickets/view/'.$id);
		}else
		{
			
			$this->view_data['ticket'] = Ticket::find_by_id($id);
			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_status');
			$this->view_data['form_action'] = base_url().'ctickets/status';
			$this->content_view = 'tickets/_status';
		}	
	}	
	function close($id = FALSE)
	{	
        $cid = $this->sessionArr['company_id'];
		$this->load->helper('notification');
		if($_POST){
			unset($_POST['send']);
			unset($_POST['_wysihtml5_mode']);
			unset($_POST['files']);
			$id = $_POST['ticket_id'];
			unset($_POST['ticket_id']);
			$ticket = Ticket::find_by_id($id);
			$attr = array('status' => "closed");
			$ticket->update_attributes($attr);
			if(isset($ticket->client->email)){ $email = $ticket->client->email; } else {$emailex = explode(' - ', $ticket->from); $email = $emailex[1]; }
			
            $clients = $this->db->query('
                                        select * from (
                                         SELECT DISTINCT(u.id), u.firstname, u.lastname, r.role_id 
                                         FROM ticket_assignment as ta
                                         INNER JOIN (users u JOIN user_roles r ON u.id = r.user_id ) on ta.user_id = u.id
                                         WHERE u.status="active" AND ta.ticket_id="'.$id.'"
                                         
                                         UNION 
                                         
                                         SELECT DISTINCT(u.id), u.firstname, u.lastname, r.role_id 
                                         FROM tickets as ta
                                         INNER JOIN (users u JOIN user_roles r ON u.id = r.user_id ) on ta.user_id = u.id
                                         WHERE u.status="active" AND ta.id="'.$id.'" AND r.company_id = '.$cid.'
                                         ) as tt
                                         where tt.id != '.$this->sessionArr['user_id'].'
                                         order by tt.role_id
                                         ')->result_array();
            if(!empty($clients))
            {
                $get_company_details=$this->db->query('select * from company_details where company_id="'.$this->sessionArr['company_id'].'"')->row_array();
                    if(!empty($get_company_details))
                    {
                        $c_logo=$get_company_details['logo'];
                        if(!empty($c_logo))
                        {
                            $company_logo=site_url().$c_logo;
                        }
                        else
                        {
                            $company_logo=site_url().'files/media/FC2_logo_dark.png';
                        }
                    }
                    else
                    {
                        $company_logo=site_url().'files/media/FC2_logo_dark.png';
                    }
                foreach($clients as $newclient)
                {
                    $set_page = $this->authenticateuser_by_id( $newclient['id'], $cid);
                    $ticket_url = base_url().$set_page."/view/".$id;
                    $user = User::find_by_id($newclient['id']);
                    
                    
                    send_ticket_notification($user->email, '[Ticket#'.$ticket->reference.'] - '.$_POST['subject'], $_POST['message'], $ticket->id, $ticket_url,$company_logo);
                }
            }
            
			$_POST['internal'] = "0";
			unset($_POST['notify']);
			$_POST['subject'] = htmlspecialchars($_POST['subject']);
			$_POST['datetime'] = time();
			$_POST['from'] = $this->user->firstname." ".$this->user->lastname.' - '.$this->user->email;
			$_POST['reply_to'] = $this->user->email;
			$_POST['ticket_id'] = $id;
			$_POST['to'] = $email;
			unset($_POST['client_id']);
			$article = TicketHasArticle::create($_POST);
       		if(!$ticket){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_save_ticket_error'));}
       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_ticket_close_success'));}
			redirect('ctickets');
		}else
		{
			$this->view_data['ticket'] = Ticket::find($id);
			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_close');
			$this->view_data['form_action'] = base_url().'ctickets/close';
			$this->content_view = 'tickets/_close';
		}	
	}	
	function view($id = FALSE)
	{ 
        $this->view_data['submenu'] = array();
		$this->content_view = 'tickets/c_view/view';
		$this->view_data['ticket'] = Ticket::find_by_id($id);

		if($this->view_data['ticket']->status == "new"){
			$this->view_data['ticket']->status = "open"; 
			$this->view_data['ticket']->save();
		}
		if(isset($this->view_data['ticket']->user->id)){ $ticket_id = $this->view_data['ticket']->user->id;}else{ $ticket_id = "0"; }
		if($this->view_data['ticket']->updated == "1" AND $ticket_id == $this->user->id){
			$this->view_data['ticket']->updated = "0"; 
			$this->view_data['ticket']->save();
		}
		$this->view_data['form_action'] = base_url().'ctickets/article/'.$id.'/add';
        
        $assign_user_details=$this->db->query('SELECT u.* FROM ticket_assignment ta
                                               INNER JOIN users u ON ta.user_id = u.id
                                               WHERE ta.ticket_id = "'.$id.'" AND u.status="active"')->result_array();
                     
        $ticket_all =array();                          
        if(!empty($assign_user_details))
        {
            $j=0;
            foreach ($assign_user_details as $key1 => $value1) 
            {
                $ticket_all[$j]['user_id'] = $value1['id'];
                $ticket_all[$j]['firstname'] = $value1['firstname'];
                $ticket_all[$j]['lastname'] = $value1['lastname'];
                $ticket_all[$j]['email'] = $value1['email'];
                $ticket_all[$j]['userpic'] = $value1['userpic'];
                $j++;
            }
        }
        $this->view_data['assign_user']=$ticket_all;
		if(!$this->view_data['ticket']){redirect('tickets');}
	}
	function article($id = FALSE, $condition = FALSE, $article_id = FALSE)
	{
		$this->view_data['submenu'] = array(
								$this->lang->line('application_back') => 'tickets',
								$this->lang->line('application_overview') => 'tickets/view/'.$id,
						 		);
		switch ($condition) {
			case 'add':
				$this->content_view = 'tickets/c_view/_note';
				if($_POST){
					$config['upload_path'] = './files/media/';
					$config['encrypt_name'] = TRUE;
					$config['allowed_types'] = '*';

					$this->load->library('upload', $config);
					$this->load->helper('notification');
					

					unset($_POST['userfile']);
					unset($_POST['file-name']);

					unset($_POST['send']);
					unset($_POST['_wysihtml5_mode']);
					unset($_POST['files']);
					$ticket = Ticket::find($id);
					$_POST['internal'] = "0";
					$_POST['subject'] = htmlspecialchars($_POST['subject']);
					$_POST['datetime'] = time();
					$_POST['from'] = $this->user->firstname.' '.$this->user->lastname.' - '.$this->user->email;
					$_POST['reply_to'] = $this->user->email;
					$article = TicketHasArticle::create($_POST);
					$email_attachment = "";
					if ( ! $this->upload->do_upload())
						{
							$error = $this->upload->display_errors('', ' ');
							$this->session->set_flashdata('message', 'error:'.$error);

						}
						else
						{
							$data = array('upload_data' => $this->upload->data());

							$attributes = array('article_id' => $article->id, 'filename' => $data['upload_data']['orig_name'], 'savename' => $data['upload_data']['file_name']);
							$attachment = ArticleHasAttachment::create($attributes);
							$email_attachment = "files/media/".$data['upload_data']['file_name'];
						}
		       		if(!$article){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_save_article_error'));}
		       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_save_article_success'));}
					redirect('ctickets/view/'.$id);
				}else
				{
					$this->theme_view = 'modal';
					$this->view_data['ticket'] = Ticket::find($id);
					$this->view_data['title'] = $this->lang->line('application_add_note');
					$this->view_data['form_action'] = base_url().'ctickets/article/'.$id.'/add';
					$this->content_view = 'tickets/c_view/_note';
				}	
				break;

			default:
				redirect('tickets');
				break;
		}

	}
	function bulk($action)
	{    
        $cid = $this->sessionArr['company_id'];
        $this->load->helper('notification');
        
        if($_POST){
            
            if(empty($_POST['bulk'])){redirect('ctickets');}
            
            $attr = array('status' => "closed");
            $email_message = $this->lang->line('messages_bulk_ticket_closed');
            $success_message = $this->lang->line('messages_bulk_ticket_closed_success');
            
            foreach ($_POST['bulk'] as $value) {
                $ticket = Ticket::find_by_id($value);
                $ticket->update_attributes($attr);
                
                $clients = $this->db->query('
                                            select * from (
                                             SELECT DISTINCT(u.id), u.firstname, u.lastname, r.role_id 
                                             FROM ticket_assignment as ta
                                             INNER JOIN (users u JOIN user_roles r ON u.id = r.user_id ) on ta.user_id = u.id
                                             WHERE u.status="active" AND ta.ticket_id="'.$value.'"
                                             
                                             UNION 
                                             
                                             SELECT DISTINCT(u.id), u.firstname, u.lastname, r.role_id 
                                             FROM tickets as ta
                                             INNER JOIN (users u JOIN user_roles r ON u.id = r.user_id ) on ta.user_id = u.id
                                             WHERE u.status="active" AND ta.id="'.$value.'" AND r.company_id = '.$cid.'
                                             ) as tt
                                             where tt.id != '.$this->sessionArr['user_id'].'
                                             order by tt.role_id
                                             ')->result_array();
                if(!empty($clients))
                {
                    $get_company_details=$this->db->query('select * from company_details where company_id="'.$this->sessionArr['company_id'].'"')->row_array();
                        if(!empty($get_company_details))
                        {
                            $c_logo=$get_company_details['logo'];
                            if(!empty($c_logo))
                            {
                                $company_logo=site_url().$c_logo;
                            }
                            else
                            {
                                $company_logo=site_url().'files/media/FC2_logo_dark.png';
                            }
                        }
                        else
                        {
                            $company_logo=site_url().'files/media/FC2_logo_dark.png';
                        }
                    foreach($clients as $newclient)
                    {
                        $set_page = $this->authenticateuser_by_id( $newclient['id'], $cid);
                        $ticket_url = base_url().$set_page."/view/".$id;
                        $user = User::find_by_id($newclient['id']);
                        
                        
                        send_ticket_notification($user->email, '[Ticket#'.$ticket->reference.'] - '.$ticket->subject, $email_message, $ticket->id, $ticket_url,$company_logo);
                    }
                }
                if(!$ticket){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_save_ticket_error'));}
                else{$this->session->set_flashdata('message', 'success:'.$success_message);}
            }
            redirect('ctickets');
        }else
        {
            $this->view_data['ticket'] = Ticket::find($id);
            $this->theme_view = 'modal';
            $this->view_data['title'] = $this->lang->line('application_close');
            $this->view_data['form_action'] = 'tickets/close';
            $this->content_view = 'tickets/_close';
        }    
    }	

	function attachment($id = FALSE){
		$this->load->helper('file');
		$this->load->helper('download');
		$attachment = TicketHasAttachment::find_by_savename($id);

		$file = './files/media/'.$attachment->savename;
		$mime = get_mime_by_extension($file);
		if(file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: '.$mime);
            header('Content-Disposition: attachment; filename='.basename($attachment->filename));
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
	function articleattachment($id = FALSE){
		$this->load->helper('download');
		$this->load->helper('file');

		$attachment = ArticleHasAttachment::find_by_savename($id); 
		$file = './files/media/'.$attachment->savename;
		$mime = get_mime_by_extension($file);
		if(file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: '.$mime);
            header('Content-Disposition: attachment; filename='.basename($attachment->filename));
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
    
    function authenticateuser_by_id($uid, $cid) {

        if(!empty($uid) && !empty($cid) ) {

            $sql_user = 'SELECT u.id, u.status, u.admin, u.email,u.hashed_password, c.name, ur.role_id as roleid, ur.company_id FROM users AS u LEFT JOIN user_roles AS ur ON u.id = ur.user_id LEFT JOIN companies AS c ON ur.company_id = c.id WHERE u.status = "active" AND u.id = "'.$uid.'" AND c.id = "'.$cid.'"';

            $check_user_validation = $this->db->query($sql_user)->row_array();

            if(!empty($check_user_validation)) {

                $sql_user_role = 'SELECT r.roles FROM user_roles AS ur LEFT JOIN roles AS r ON ur.role_id = r.role_id WHERE ur.user_id = "'.$check_user_validation['id'].'" AND ur.company_id = "'.$cid.'"';

                $get_role = $this->db->query($sql_user_role)->row_array();

                if($get_role['roles'] == 'Freelancer'):
                    $set_page = 'aotickets'; 
                elseif($get_role['roles'] == 'Client'):
                    $set_page = 'ctickets'; 
                elseif($get_role['roles'] == 'Sub-Contractor'):
                    $set_page = 'sctickets'; 
                endif;


                return $set_page;
            } else {
                return FALSE;
            }

        }
    }

}
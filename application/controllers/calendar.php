<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    
class Calendar extends MY_Controller {
           
	function __construct()
	{
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
        $this->load->database();
        
        $this->view_data['submenu'] = array(
            //$this->lang->line('application_my_projects') => 'aoprojects/index/' . $this->sessionArr['company_id'],
            $this->lang->line('application_all') => 'aoprojects/filter/all',
            $this->lang->line('application_open') => 'aoprojects/filter/open',
            $this->lang->line('application_closed') => 'aoprojects/filter/closed'
        );	
		$this->load->database();
		
	}	
	function index()
	{
		$role_id=$this->sessionArr['role_id'];
        if($role_id==2)
        {
            $project = Project::find_by_sql('SELECT DISTINCT (p.id),p.* FROM projects p
                                            LEFT JOIN user_roles u ON p.company_id = u.company_id
                                            WHERE u.company_id = "' . $this->sessionArr['company_id'] . '"
                                            AND u.role_id = "' . $this->sessionArr['role_id'] . '" and u.user_id="'.$this->sessionArr['user_id'].'" order by p.id desc');
            //var_dump($project);exit;
            if(!empty($project))
            {   
                $project_all =array();
                $i=0;
                foreach($project as $key =>$value)
                {
                    //echo "<pre>";var_dump($value->id);
                    $project_all[$i]['id']=$value->id;
                    $project_all[$i]['name']=$value->name;
                    $project_all[$i]['reference']=$value->reference;
                    $project_all[$i]['description']=$value->description;
                    $project_all[$i]['start']=$value->start;
                    $project_all[$i]['end']=$value->end;
                    $project_all[$i]['company_name']=$this->sessionArr['company_name'];
                    $project_all[$i]['progress']=$value->progress;
                    $project_all[$i]['phases']=$value->phases;
                    $project_all[$i]['reference']=$value->reference;
                    $project_all[$i]['tracking']=$value->tracking;
                    $project_all[$i]['datetime']=$value->datetime;
                    $project_all[$i]['category']=$value->category;
                    $project_all[$i]['company_id']=$value->company_id;
                    
                    $assign_client_details=$this->db->query('SELECT DISTINCT (p.assign_user_id), u.* FROM project_assign_clients p
                                                            LEFT JOIN user_roles r ON p.company_id = r.company_id
                                                            LEFT JOIN users u ON p.assign_user_id = u.id
                                                            WHERE p.project_id = "'.$value->id.'"
                                                            AND r.company_id = "'.$this->sessionArr['company_id'].'" AND r.role_id="'.$this->sessionArr['role_id'].'" and u.status="active"')->result_array();
                    if(!empty($assign_client_details))
                    {
                        $j=0;
                        foreach ($assign_client_details as $key1 => $value1) 
                        {
                            $project_all[$i]['clients'][$j]['user_id'] = $value1['assign_user_id'];
                            $project_all[$i]['clients'][$j]['firstname'] = $value1['firstname'];
                            $project_all[$i]['clients'][$j]['lastname'] = $value1['lastname'];
                            $project_all[$i]['clients'][$j]['email'] = $value1['email'];
                            $project_all[$i]['clients'][$j]['userpic'] = $value1['userpic'];
                            $j++;
                        }
                    }
                    //var_dump($assign_client_details);
                    $i++;
                }
            }
        }
        elseif($role_id==3)
        {
            $project_all = $this->db->query("SELECT p.* from projects p join project_assign_clients c on p.id=c.project_id and p.company_id=c.company_id left join user_roles r on c.company_id=r.company_id and c.assign_user_id=r.user_id where r.company_id='".$this->sessionArr['company_id']."' and r.role_id='".$this->sessionArr['role_id']."' and r.user_id='".$this->sessionArr['user_id']."' order by p.id desc")->result_array();
        }
        elseif($role_id==4)
        {
            //echo "Fsf";exit;
             $project_all = $this->db->query("SELECT p.* from projects p join project_assign_clients c on p.id=c.project_id and p.company_id=c.company_id left join user_roles r on c.company_id=r.company_id and c.assign_user_id=r.user_id where r.company_id='".$this->sessionArr['company_id']."' and r.role_id='".$this->sessionArr['role_id']."' and r.user_id='".$this->sessionArr['user_id']."' order by p.id desc")->result_array();
        }
        
        //exit;
        //echo "<pre>";
        //print_r($project_all);
        //exit;
		
		$project_events = "";
		foreach ($project_all as $value) {
			$descr = preg_replace( "/\r|\n/", "", $value['description'] );
			$project_events .= "{
                          title: '".$this->lang->line('application_project').": ".addslashes($value['name'])."',
                          start: '".$value['start']."',
                          end: '".$value['end']."',
                          url: '".base_url()."aoprojects/view/".$value['id']."',
                          className: 'project-event',
                          description: '".addslashes($descr)."'
                      },";
		}

		//events
		$events = Event::all();
		
		$event_list = "";
		foreach ($events as $value) {
			$event_list .= "{
                          title: '".addslashes($value->title)."',
                          start: '".$value->start."',
                          end: '".$value->end."',
                          url: '".base_url()."calendar/edit_event/".$value->id."',
                          className: '".$value->classname."',
                          modal: 'true',
                          description: '".addslashes(preg_replace( "/\r|\n/", "", $value->description))."',

                      },";
		}

		$this->view_data['core_settings'] = Setting::first();
		//var_dump($this->view_data['settings']);exit;
        $this->view_data['project_events'] = $project_events;
		$this->view_data['events_list'] = $event_list;
		$this->content_view = 'calendar/full';
		
	}

	function create(){
		if($_POST){
			//echo "<pre>";print_r($_POST);exit;
			unset($_POST['send']);
			$_POST['title'] = htmlspecialchars($_POST['title']);
			$_POST['start'] = new DateTime($_POST['start']);
			$_POST['start'] = $_POST['start']->format('Y-m-d H:i');
			$_POST['end'] = new DateTime($_POST['end']);
			$_POST['end'] = $_POST['end']->format('Y-m-d H:i');
			$_POST['description'] = htmlspecialchars($_POST['description']); 
			$_POST['user_id'] = $this->user->id;
			$Event = Event::create($_POST);
       		if(!$Event){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_create_event_error'));}
       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_create_event_success'));}
			redirect('calendar');
			
		}else
		{
			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_create_event');
			$this->view_data['form_action'] = base_url().'calendar/create';
			$this->content_view = 'calendar/_event';
		}	
	}

	function edit_event($id = FALSE){
		if($_POST){
			unset($_POST['send']);
			$event = Event::find_by_id($_POST['id']);
			unset($_POST['id']);
			$_POST['title'] = htmlspecialchars($_POST['title']);
			$_POST['start'] = new DateTime($_POST['start']);
			$_POST['start'] = $_POST['start']->format('Y-m-d H:i');
			$_POST['end'] = new DateTime($_POST['end']);
			$_POST['end'] = $_POST['end']->format('Y-m-d H:i');
			$_POST['description'] = htmlspecialchars($_POST['description']);
			$event = $event->update_attributes($_POST);
       		if(!$event){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_create_event_error'));}
       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_create_event_success'));}
			redirect('calendar');
			
		}else
		{
			$this->view_data['event'] = Event::find_by_id($id);
			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_update_event');
			$this->view_data['form_action'] = base_url().'calendar/edit_event';
			$this->content_view = 'calendar/_event';
		}	
	}

	function delete($id = FALSE){
		$event = Event::find_by_id($id);
		$event->delete();
		if(!$event){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_delete_event_error'));}
       	else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_delete_event_success'));}
		redirect('calendar');
	}

}
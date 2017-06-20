<?php
/**
 * ClassName: Scprojects 
 * This class is used for sub-contractor projects
 * */
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Scprojects extends MY_Controller {

    //default action
    function __construct() {
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
            //$this->lang->line('application_my_projects') => 'scprojects/index/' . $this->sessionArr['company_id'],
            $this->lang->line('application_all') => 'scprojects/filter/all',
            $this->lang->line('application_open') => 'scprojects/filter/open',
            $this->lang->line('application_closed') => 'scprojects/filter/closed'
        );
		$this->settings = Setting::first();
    }

    function submenu($id) {
        return array(
            $this->lang->line('application_back') => 'scprojects/',
            $this->lang->line('application_overview') => 'scprojects/view/' . $id,
            $this->lang->line('application_media') => 'scprojects/media/' . $id,
        );
    }
    
    //status wise filter projects
    function filter($condition) {
        //echo "<pre>";print_r($this->session);exit;
        $options = array('conditions' => 'progress < 100');
        if ($this->user->admin == 0) {
            switch ($condition) {
                case 'open':
                    $options = 'p.progress < 100';
                    $options2 = 't.progress < 100';
                    break;
                case 'closed':
                    $options = 'p.progress = 100';
                    $options2 = 't.progress = 100';
                    break;
                case 'all':
                    $options  = '(p.progress = 100 OR p.progress < 100)';
                    $options2 ='(t.progress = 100 OR t.progress < 100)';
                    break;
            }

            $project_array = array();
            
            $open_projects = Project::find_by_sql("SELECT p.* from projects p join project_assign_clients c on p.id=c.project_id and p.company_id=c.company_id left join user_roles r on c.company_id=r.company_id and c.assign_user_id=r.user_id where ".$options." and r.company_id='".$this->sessionArr['company_id']."' and r.role_id='".$this->sessionArr['role_id']."' and r.user_id='".$this->sessionArr['user_id']."' order by p.id desc");
            
            if (!empty($open_projects)) {
                foreach ($open_projects as $value) {        
                    array_push($project_array, $value->id);
                }
            }
            
            $thisUserHasNoCompanies = $this->sessionArr['company_id'];
            if (!empty($thisUserHasNoCompanies)) {
                
                //merge projects by client admin and assigned to projects
                $result = $open_projects;
                //duplicate objects will be removed
                $result = array_map("unserialize", array_unique(array_map("serialize", $result)));
                //array is sorted on the bases of id
                
                sort($result);

                $this->view_data['project'] = $result;
            } else {
                $this->view_data['project'] = Project::find_by_sql('SELECT DISTINCT (p.id),p.* from projects p left join user_roles r on p.company_id=r.company_id where p.id in ('.$project_array.') and r.role_id="'.$this->sessionArr['role_id'].'" and p.'.$options.'');
            }
        } else {
            switch ($condition) {
                case 'open':
                    $options = 'p.progress < 100';
                    $options2 = 't.progress < 100';
                    break;
                case 'closed':
                    $options = 'p.progress = 100';
                    $options2 = 't.progress = 100';
                    break;
                case 'all':
                    $options  = '(p.progress = 100 OR p.progress < 100)';
                    $options2 ='(t.progress = 100 OR t.progress < 100)';
                    break;
            }
            $this->view_data['project'] = Project::all($options);
        }


        $this->content_view = 'projects/sc_views/all';

        $this->view_data['projects_assigned_to_me'] = ProjectHasWorker::find_by_sql("SELECT count(*) as amount from projects p join project_assign_clients c on p.id=c.project_id and p.company_id=c.company_id left join user_roles r on c.company_id=r.company_id and c.assign_user_id=r.user_id where ".$options." and r.company_id='".$this->sessionArr['company_id']."' and r.role_id='".$this->sessionArr['role_id']."' and r.user_id='".$this->sessionArr['user_id']."' order by p.id desc");
        
        $tasks_assigned_to_me = ProjectHasTask::find_by_sql('SELECT t.* FROM project_has_tasks t JOIN projects p ON t.project_id = p.id JOIN user_roles r ON t.user_id = r.user_id AND p.company_id = r.company_id WHERE '.$options2.' and r.company_id = "' . $this->sessionArr['company_id'] . '" AND r.role_id ="' . $this->sessionArr['role_id'] . '" and r.user_id="' . $this->sessionArr['user_id'] . '" 
UNION
SELECT t.* from project_has_tasks t join project_assign_tasks at on t.id=at.task_id and t.project_id=at.project_id
join user_roles r on at.assign_user_id=r.user_id where '.$options2.' and r.company_id="' . $this->sessionArr['company_id'] . '" and r.role_id="' . $this->sessionArr['role_id'] . '" and r.user_id="' . $this->sessionArr['user_id'] . '" 
');
        //var_dump(count($tasks_assigned_to_me));exit;
        $this->view_data['tasks_assigned_to_me'] = count($tasks_assigned_to_me);
    }

    //all sub-contractor projects
    function index() {
        $cid = $this->sessionArr['company_id'];
        if(empty($cid))
        {
            $this->view_data['error'] = "true";
            $this->session->set_flashdata('message', 'error: You have no access to any modules!');
            redirect('login');
        }
        $this->view_data['projects_assigned_to_me'] = ProjectHasWorker::find_by_sql("SELECT count(*) as amount from projects p join project_assign_clients c on p.id=c.project_id and p.company_id=c.company_id left join user_roles r on c.company_id=r.company_id and c.assign_user_id=r.user_id where r.company_id='".$this->sessionArr['company_id']."' and r.role_id='".$this->sessionArr['role_id']."' and r.user_id='".$this->sessionArr['user_id']."' order by p.id desc");
        
        //var_dump($this->view_data['projects_assigned_to_me']);exit;
        $tasks_assigned_to_me = ProjectHasTask::find_by_sql('SELECT t.* FROM project_has_tasks t JOIN projects p ON t.project_id = p.id JOIN user_roles r ON t.user_id = r.user_id AND p.company_id = r.company_id WHERE r.company_id = "' . $this->sessionArr['company_id'] . '" AND r.role_id ="' . $this->sessionArr['role_id'] . '" and r.user_id="' . $this->sessionArr['user_id'] . '" 
UNION
SELECT t.* from project_has_tasks t join project_assign_tasks at on t.id=at.task_id and t.project_id=at.project_id
join user_roles r on at.assign_user_id=r.user_id where r.company_id="' . $this->sessionArr['company_id'] . '" and r.role_id="' . $this->sessionArr['role_id'] . '" and r.user_id="' . $this->sessionArr['user_id'] . '" 
');
        //var_dump(count($tasks_assigned_to_me));exit;
        $this->view_data['tasks_assigned_to_me'] = count($tasks_assigned_to_me);
        
        $this->view_data['project'] = Project::find_by_sql("SELECT p.* from projects p join project_assign_clients c on p.id=c.project_id and p.company_id=c.company_id left join user_roles r on c.company_id=r.company_id and c.assign_user_id=r.user_id where r.company_id='".$this->sessionArr['company_id']."' and r.role_id='".$this->sessionArr['role_id']."' and r.user_id='".$this->sessionArr['user_id']."' order by p.id desc");
        $this->content_view = 'projects/sc_views/all';
    }

    //sub-contractor see project details
    function view($id = FALSE) {
        $this->view_data['submenu'] = array(
            $this->lang->line('application_back') => 'scprojects/',
            $this->lang->line('application_overview') => 'scprojects/view/' . $id,
            $this->lang->line('application_media') => 'scprojects/media/' . $id,
        );
        $this->view_data['project'] = Project::find($id);
        $this->view_data['project_has_invoices'] = Invoice::find('all', array('conditions' => array('project_id = ? AND company_id=? AND estimate != ? AND issue_date<=?', $id, $this->client->company->id, 1, date('Y-m-d', time()))));
        $tasks = ProjectHasTask::count(array('conditions' => array('project_id = ? AND public = ?', $id, 1)));
        $tasks_done = ProjectHasTask::count(array('conditions' => array('status = ? AND project_id = ? AND public = ?', 'done', $id, 1)));
        @$this->view_data['opentaskspercent'] = $tasks_done / $tasks * 100;
        
        $mytasks = $this->db->query("select count(*) as mytasks from (SELECT t.* FROM project_has_tasks t JOIN projects p ON t.project_id = p.id JOIN user_roles r ON t.user_id = r.user_id AND p.company_id = r.company_id WHERE t.status != 'done' and t.project_id = '".$id."' AND r.company_id = '".$this->sessionArr['company_id']."' AND r.role_id ='".$this->sessionArr['role_id']."' and r.user_id='".$this->sessionArr['user_id']."'UNION
            SELECT t.* from project_has_tasks t join project_assign_tasks at on t.id=at.task_id and t.project_id=at.project_id
            join user_roles r on at.assign_user_id=r.user_id where t.status != 'done' and r.company_id='".$this->sessionArr['company_id']."' and r.role_id='".$this->sessionArr['role_id']."' and r.user_id='".$this->sessionArr['user_id']."' AND t.project_id = '".$id."') as d
            ")->row_array();
        $this->view_data['mytasks']=$mytasks['mytasks'];
        
        $this->view_data['time_days'] = round((human_to_unix($this->view_data['project']->end . ' 00:00') - human_to_unix($this->view_data['project']->start . ' 00:00')) / 3600 / 24);
        $this->view_data['time_left'] = $this->view_data['time_days'];
        $this->view_data['timeleftpercent'] = 100;

        if (human_to_unix($this->view_data['project']->start . ' 00:00') < time() && human_to_unix($this->view_data['project']->end . ' 00:00') > time()) {
            $this->view_data['time_left'] = round((human_to_unix($this->view_data['project']->end . ' 00:00') - time()) / 3600 / 24);
            $this->view_data['timeleftpercent'] = $this->view_data['time_left'] / $this->view_data['time_days'] * 100;
        }
        if (human_to_unix($this->view_data['project']->end . ' 00:00') < time()) {
            $this->view_data['time_left'] = 0;
            $this->view_data['timeleftpercent'] = 0;
        }
        @$this->view_data['opentaskspercent'] = $tasks_done / $tasks * 100;
        $tracking = $this->view_data['project']->time_spent;
        if (!empty($this->view_data['project']->tracking)) {
            $tracking = (time() - $this->view_data['project']->tracking) + $this->view_data['project']->time_spent;
        }
        $this->view_data['timertime'] = $tracking;
        $this->view_data['time_spent_from_today'] = time() - $this->view_data['project']->time_spent;
        $tracking = floor($tracking / 60);
        $tracking_hours = floor($tracking / 60);
        $tracking_minutes = $tracking - ($tracking_hours * 60);

        $task_list = ProjectHasTask::find_by_sql("SELECT t.* FROM project_has_tasks t JOIN projects p ON t.project_id = p.id JOIN user_roles r ON t.user_id = r.user_id AND p.company_id = r.company_id WHERE t.project_id = '".$id."' AND r.company_id = '".$this->sessionArr['company_id']."' AND r.role_id ='".$this->sessionArr['role_id']."' and r.user_id='".$this->sessionArr['user_id']."'UNION
            SELECT t.* from project_has_tasks t join project_assign_tasks at on t.id=at.task_id and t.project_id=at.project_id
            join user_roles r on at.assign_user_id=r.user_id where r.company_id='".$this->sessionArr['company_id']."' and r.role_id='".$this->sessionArr['role_id']."' and r.user_id='".$this->sessionArr['user_id']."' AND t.project_id = '".$id."'
            ");
        
        if (!empty($task_list)) {
            $newArr = array();
            $i = 0;
            foreach ($task_list as $key => $value) {
                $newArr[$i]['id'] = $value->id;
                $newArr[$i]['user_id'] = $value->user_id;
                $newArr[$i]['task_name'] = $value->name;
                $newArr[$i]['status'] = $value->status;
                $newArr[$i]['priority'] = $value->priority;
                $newArr[$i]['public'] = $value->public;
                $newArr[$i]['datetime'] = $value->datetime;
                $newArr[$i]['due_date'] = $value->due_date;
                $newArr[$i]['description'] = $value->description;
                $newArr[$i]['value'] = $value->value;
                $newArr[$i]['tracking'] = $value->tracking;
                $newArr[$i]['time_spent'] = $value->time_spent;
                $newArr[$i]['milestone_id'] = $value->milestone_id;
                $newArr[$i]['invoice_id'] = $value->invoice_id;
                $newArr[$i]['milestone_order'] = $value->milestone_order;
                $newArr[$i]['progress'] = $value->progress;
                $newArr[$i]['task_order'] = $value->task_order;
                $newArr[$i]['created_at'] = $value->created_at;
                //$newArr[$i]['task_attach_file'] = $value->task_attach_file;
                $newArr[$i]['start_date'] = $value->start_date;

                /*$tasks_attachment_by_task_id=$this->db->query('select * from project_has_tasks_attachment where task_id="'.$value->id.'"')->result_array();
                
                if (!empty($tasks_attachment_by_task_id)) {
                    $k = 0;
                    foreach ($tasks_attachment_by_task_id as $kk => $vv) 
                    {
                        $newArr[$i]['task_attchment'][$k]['attachment_id'] = $vv['id'];
                        $newArr[$i]['task_attchment'][$k]['project_id'] = $vv['project_id'];
                        $newArr[$i]['task_attchment'][$k]['task_id'] = $vv['task_id'];
                        $newArr[$i]['task_attchment'][$k]['company_id'] = $vv['company_id'];
                        $newArr[$i]['task_attchment'][$k]['user_id'] = $vv['user_id'];
                        $newArr[$i]['task_attchment'][$k]['role_id'] = $vv['role_id'];
                        $newArr[$i]['task_attchment'][$k]['task_attach_file'] = $vv['task_attach_file'];
                        $k++;
                    }
                       
                }*/
                $get_assign_clients = $this->db->query('select assign_user_id from project_assign_tasks where task_id="' . $value->id . '"')->result_array();
                
                if (!empty($get_assign_clients)) {
                    $j = 0;
                    foreach ($get_assign_clients as $k => $v) {
                        $get_client_details = $this->db->query('select firstname,lastname,email,userpic from users where id="' . $v['assign_user_id'] . '"')->row_array();
                        if (!empty($get_client_details)) {
                            $newArr[$i]['clients'][$j]['firstname'] = $get_client_details['firstname'];
                            $newArr[$i]['clients'][$j]['lastname'] = $get_client_details['lastname'];
                            $newArr[$i]['clients'][$j]['email'] = $get_client_details['email'];
                            $newArr[$i]['clients'][$j]['userpic'] = $get_client_details['userpic'];
                            $j++;
                        }
                    }
                }
                $i++;
            }
            $this->view_data['task_list'] = $newArr;
        }

        $not_milestone_task_list = ProjectHasTask::find_by_sql("SELECT t.* FROM project_has_tasks t JOIN projects p ON t.project_id = p.id JOIN user_roles r ON t.user_id = r.user_id AND p.company_id = r.company_id WHERE t.milestone_id=0 and t.project_id = '".$id."' AND r.company_id = '".$this->sessionArr['company_id']."' AND r.role_id ='".$this->sessionArr['role_id']."' and r.user_id='".$this->sessionArr['user_id']."'UNION
            SELECT t.* from project_has_tasks t join project_assign_tasks at on t.id=at.task_id and t.project_id=at.project_id
            join user_roles r on at.assign_user_id=r.user_id where t.milestone_id=0 and r.company_id='".$this->sessionArr['company_id']."' and r.role_id='".$this->sessionArr['role_id']."' and r.user_id='".$this->sessionArr['user_id']."' AND t.project_id = '".$id."'
            ");
        
        if (!empty($not_milestone_task_list)) {
            $not_milestone_newArr = array();
            $c = 0;
            foreach ($not_milestone_task_list as $key => $value) {
                $not_milestone_newArr[$c]['id'] = $value->id;
                $not_milestone_newArr[$c]['user_id'] = $value->user_id;
                $not_milestone_newArr[$c]['task_name'] = $value->name;
                $not_milestone_newArr[$c]['status'] = $value->status;
                $not_milestone_newArr[$c]['priority'] = $value->priority;
                $not_milestone_newArr[$c]['public'] = $value->public;
                $not_milestone_newArr[$c]['datetime'] = $value->datetime;
                $not_milestone_newArr[$c]['due_date'] = $value->due_date;
                $not_milestone_newArr[$c]['description'] = $value->description;
                $not_milestone_newArr[$c]['value'] = $value->value;
                $not_milestone_newArr[$c]['tracking'] = $value->tracking;
                $not_milestone_newArr[$c]['time_spent'] = $value->time_spent;
                $not_milestone_newArr[$c]['milestone_id'] = $value->milestone_id;
                $not_milestone_newArr[$c]['invoice_id'] = $value->invoice_id;
                $not_milestone_newArr[$c]['milestone_order'] = $value->milestone_order;
                $not_milestone_newArr[$c]['progress'] = $value->progress;
                $not_milestone_newArr[$c]['task_order'] = $value->task_order;
                $not_milestone_newArr[$c]['created_at'] = $value->created_at;
                //$not_milestone_newArr[$c]['task_attach_file'] = $value->task_attach_file;
                $not_milestone_newArr[$c]['start_date'] = $value->start_date;

                
                $not_milestone_get_assign_clients = $this->db->query('select assign_user_id from project_assign_tasks where task_id="' . $value->id . '"')->result_array();
                //var_dump(expression)
            
                if (!empty($not_milestone_get_assign_clients)) {
                    $jk = 0;
                    foreach ($not_milestone_get_assign_clients as $k => $v) {
                        $not_milestone_get_client_details = $this->db->query('select firstname,lastname,email,userpic from users where id="' . $v['assign_user_id'] . '"')->row_array();
                        if (!empty($not_milestone_get_client_details)) {
                            $not_milestone_newArr[$c]['clients'][$jk]['firstname'] = $not_milestone_get_client_details['firstname'];
                            $not_milestone_newArr[$c]['clients'][$jk]['lastname'] = $not_milestone_get_client_details['lastname'];
                            $not_milestone_newArr[$c]['clients'][$jk]['email'] = $not_milestone_get_client_details['email'];
                            $not_milestone_newArr[$c]['clients'][$jk]['userpic'] = $not_milestone_get_client_details['userpic'];
                            $jk++;
                        }
                    }
                }
                $c++;
            }
            //echo "<pre>";print_r($not_milestone_newArr);exit;
            $this->view_data['tasksWithoutMilestone'] = $not_milestone_newArr;
        }

        $this->view_data['time_spent'] = $tracking_hours . " " . $this->lang->line('application_hours') . " " . $tracking_minutes . " " . $this->lang->line('application_minutes');
        $this->view_data['time_spent_counter'] = sprintf("%02s", $tracking_hours) . ":" . sprintf("%02s", $tracking_minutes);

        if (!isset($this->view_data['project_has_invoices'])) {
            $this->view_data['project_has_invoices'] = array();
        }
        if ($this->view_data['project']->company_id != $this->sessionArr['company_id']) {
            redirect('scprojects/');
        }
        $this->content_view = 'projects/sc_views/view';
    }

    //media for single project 
    function media($id = FALSE, $condition = FALSE, $media_id = FALSE) {
        $this->load->helper('notification');
        $this->view_data['submenu'] = array(
            $this->lang->line('application_back') => 'scprojects/index/'.$this->sessionArr['company_id'],
            $this->lang->line('application_overview') => 'scprojects/view/' . $id,
            $this->lang->line('application_media') => 'scprojects/media/' . $id,
        );
        switch ($condition) {
            case 'view':
                if ($_POST) {
                    unset($_POST['send']);
                    unset($_POST['_wysihtml5_mode']);
                    unset($_POST['files']);
                    $_POST['text'] = $_POST['message'];
                    unset($_POST['message']);
                    $_POST['project_id'] = $id;
                    $_POST['media_id'] = $media_id;
                    $_POST['from'] = $this->user->firstname . ' ' . $this->user->lastname;
                    $this->view_data['project'] = Project::find_by_id($id);
                    $this->view_data['media'] = ProjectHasFile::find($media_id);
                    $message = Message::create($_POST);
                    if (!$message) {
                        $this->session->set_flashdata('message', 'error:' . $this->lang->line('messages_save_message_error'));
                    } else {
                        $this->session->set_flashdata('message', 'success:' . $this->lang->line('messages_save_message_success'));
                        foreach ($this->view_data['project']->project_has_workers as $workers) {
                            send_notification($workers->user->email, "[" . $this->view_data['project']->name . "] New comment", 'New comment on meida file: ' . $this->view_data['media']->name . '<br><strong>' . $this->view_data['project']->name . '</strong>');
                        }
                    }
                    redirect('scprojects/media/' . $id . '/view/' . $media_id);
                }
                $this->content_view = 'projects/sc_views/view_media';
                $this->view_data['media'] = ProjectHasFile::find($media_id);
                $project = Project::find_by_id($id);
                if($project->company_id != $this->this->session->userdata('company_id')) 
                {
                    redirect('scprojects/');
                }
                $this->view_data['form_action'] = base_url().'scprojects/media/' . $id . '/view/' . $media_id;
                $this->view_data['filetype'] = explode('.', $this->view_data['media']->filename);
                $this->view_data['filetype'] = $this->view_data['filetype'][1];
                $this->view_data['backlink'] = 'scprojects/view/' . $id.'#media-tab';
                break;
            case 'add':
                $this->content_view = 'projects/sc_views/_media';
                $this->view_data['project'] = Project::find($id);
                if ($_POST) {
                    $config['upload_path'] = './files/media/';
                    $config['encrypt_name'] = TRUE;
                    $config['allowed_types'] = '*';

                    $this->load->library('upload', $config);
                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors('', ' ');
                        $this->session->set_flashdata('message', 'error:' . $error);
                        redirect('scprojects/view/' . $id);
                    } else {
                        $data = array('upload_data' => $this->upload->data());

                        $_POST['filename'] = $data['upload_data']['orig_name'];
                        $_POST['savename'] = $data['upload_data']['file_name'];
                        $_POST['type'] = $data['upload_data']['file_type'];
                    }

                    unset($_POST['send']);
                    unset($_POST['userfile']);
                    unset($_POST['file-name']);
                    unset($_POST['files']);
                    $_POST = array_map('htmlspecialchars', $_POST);
                    $_POST['project_id'] = $id;
                    $_POST['user_id'] = $this->user->id;
                    $media = ProjectHasFile::create($_POST);
                    if (!$media) {
                        $this->session->set_flashdata('message', 'error:' . $this->lang->line('messages_save_media_error'));
                    } else {
                        $this->session->set_flashdata('message', 'success:' . $this->lang->line('messages_save_media_success'));
                        $attributes = array('subject' => $this->lang->line('application_new_media_subject'), 'message' => '<b>' . $this->user->firstname . ' ' . $this->user->lastname . '</b> ' . $this->lang->line('application_uploaded') . ' ' . $_POST['name'], 'datetime' => time(), 'project_id' => $id, 'type' => 'media', 'user_id' => $this->user->id);
                        $activity = ProjectHasActivity::create($attributes);

                        foreach ($this->view_data['project']->project_has_workers as $workers) {
                            send_notification($workers->user->email, "[" . $this->view_data['project']->name . "] " . $this->lang->line('application_new_media_subject'), $this->lang->line('application_new_media_file_was_added') . ' <strong>' . $this->view_data['project']->name . '</strong>');
                        }
                        if ($this->sessionArr['email']) {
                            send_notification($this->sessionArr['email'], "[" . $this->view_data['project']->name . "] " . $this->lang->line('application_new_media_subject'), $this->lang->line('application_new_media_file_was_added') . ' <strong>' . $this->view_data['project']->name . '</strong>');
                        }
                    }
                    redirect('scprojects/view/' . $id.'#media-tab');
                } else {
                    $this->theme_view = 'modal';
                    $this->view_data['title'] = $this->lang->line('application_add_media');
                    $this->view_data['form_action'] = base_url().'scprojects/media/' . $id . '/add';
                    $this->content_view = 'projects/sc_views/_media';
                }
                break;
            case 'update':
                $this->content_view = 'projects/sc_views/_media';
                $this->view_data['media'] = ProjectHasFile::find($media_id);
                $this->view_data['project'] = Project::find($id);
                if ($_POST) {
                    unset($_POST['send']);
                    unset($_POST['_wysihtml5_mode']);
                    unset($_POST['files']);
                    $_POST = array_map('htmlspecialchars', $_POST);
                    $media_id = $_POST['id'];
                    $media = ProjectHasFile::find($media_id);
                    if ($this->view_data['media']->user_id != "0") {
                        $media->update_attributes($_POST);
                    }
                    if (!$media) {
                        $this->session->set_flashdata('message', 'error:' . $this->lang->line('messages_save_media_error'));
                    } else {
                        $this->session->set_flashdata('message', 'success:' . $this->lang->line('messages_save_media_success'));
                    }
                    redirect('scprojects/view/' . $id.'#media-tab');
                } else {
                    $this->theme_view = 'modal';
                    $this->view_data['title'] = $this->lang->line('application_edit_media');
                    $this->view_data['form_action'] = base_url().'scprojects/media/' . $id . '/update/' . $media_id;
                    $this->content_view = 'projects/sc_views/_media';
                }
                break;
            case 'delete':
                $media = ProjectHasFile::find($media_id);
                if ($media->user_id != "0") {
                    $media->delete();
                    $this->load->database();
                    $sql = "DELETE FROM messages WHERE media_id = $media_id";
                    $this->db->query($sql);
                }
                if (!$media) {
                    $this->session->set_flashdata('message', 'error:' . $this->lang->line('messages_delete_media_error'));
                } else {
                    unlink('./files/media/' . $media->savename);
                    $this->session->set_flashdata('message', 'success:' . $this->lang->line('messages_delete_media_success'));
                }
                redirect('scprojects/view/' . $id.'#media-tab');
                break;
            default:
                $this->view_data['project'] = Project::find($id);
                $this->content_view = 'projects/sc_views/media';
                break;
        }
    }

    //task chage attributes
    function task_change_attribute() {
        if ($_POST) {
            $name = $_POST["name"];
            $taskId = $_POST["pk"];
            $value = $_POST["value"];
            $task = ProjectHasTask::find_by_id($taskId);
            $task->{$name} = $value;
            $task->save();
        }
        $this->theme_view = 'blank';
    }

    //timer for tasks
    function task_start_stop_timer($taskId) {
        $task = ProjectHasTask::find_by_id($taskId);
        if ($task->tracking != 0) {
            $diff = time() - $task->tracking;
            $task->time_spent = $task->time_spent + $diff;
            $task->tracking = "";
        } else {
            $task->tracking = time();
        }
        $task->save();
        $this->theme_view = 'blank';
    }

    //Tasks Add/Update/Delete
    function tasks($id = FALSE, $condition = FALSE, $task_id = FALSE) {
        $this->view_data['submenu'] = array(
            $this->lang->line('application_back') => 'scprojects/',
            $this->lang->line('application_overview') => 'scprojects/view/' . $id,
        );
        switch ($condition) {
            case 'comment':
                if ($_POST) {
                    unset($_POST['send']);
                    if($_POST['message'] != '<p><br></p>')
                    {
                        $_POST['message'] = strip_tags($_POST['message'], '<br><br/><p></p><a></a><b></b><i></i><u></u><span></span>');
                        $_POST['project_id'] = $id;
                        $_POST['company_id'] = $this->sessionArr['company_id'];
                        $_POST['task_id'] = $task_id;
                        $_POST['user_id'] = $this->user->id;
                        unset($_POST['files']);
                        $_POST['datetime'] = time();
                         //echo "<pre>";print_r($_POST);exit;
                        $comment = ProjectHasTasksComment::create($_POST);

                        $subject = "task-comment";
                        $message = $_POST['message'];
                        $user_id = $_POST['user_id'];
                        $datetime = $_POST['datetime'];
                        $type="comment";
                        
                        $activity_arr=array(
                            "subject"=>$subject,
                            "message"=>$message,
                            "project_id"=>$id,
                            "user_id"=>$user_id,
                            "datetime"=>$datetime,
                            "type"=>$type
                        );
                        $activity = ProjectHasActivity::create($activity_arr);

                        if (!$comment) {
                            $this->session->set_flashdata('message', 'error:' . $this->lang->line('messages_save_error'));
                        } else {
                            $this->session->set_flashdata('message', 'success:' . $this->lang->line('messages_save_success'));
                        }
                    }
                    //redirect('projects/view/'.$id);
                }


                
                $task = ProjectHasTask::find_by_id($task_id);
                if(!empty($task))
                {
                    $this->content_view = 'projects/sc_views/view_task_comment';
                    $this->view_data['task'] = $task;
                    $get_task_comment=$this->db->query('SELECT * from project_has_tasks_comment where task_id="'.$task_id.'" and project_id="'.$id.'" order by id desc')->result_array();
                    if(!empty($get_task_comment))
                    {
                        $this->view_data['task_comments']=$get_task_comment;
                    }
                    
                    $project = Project::find_by_id($id);
                    if ($project->company_id != $this->sessionArr['company_id']) {
                        redirect('scprojects/');
                    }
                    $this->view_data['form_action'] = base_url().'scprojects/tasks/' . $id . '/view/' . $task_id;
                    $this->view_data['backlink'] = 'scprojects/view/' . $id.'#tasks-tab';
                }
                else
                {
                    redirect('scdashboard');
                }
                break;
            case 'view':
                if($_POST) 
                {
                    //echo "<pre>";print_r($_FILES);exit;
                    $directoryName = './files/tasks_attachment/';
                    if (!is_dir($directoryName)) {
                        //Directory does not exist, so lets create it.
                        mkdir($directoryName, 0755);
                    }
                    if(!empty($_FILES['task_attach_file']['name'])) 
                    {
                        
                        $filesCount = count($_FILES['task_attach_file']['name']);
                        $uploadData=array();
                        for($i = 0; $i < $filesCount; $i++)
                        {
                            if($_FILES['task_attach_file']['error'][$i]==0)
                            {
                                $_FILES['task_attach_file[]']['name'] = $_FILES['task_attach_file']['name'][$i];
                                $_FILES['task_attach_file[]']['type'] = $_FILES['task_attach_file']['type'][$i];
                                $_FILES['task_attach_file[]']['tmp_name'] = $_FILES['task_attach_file']['tmp_name'][$i];
                                $_FILES['task_attach_file[]']['error'] = $_FILES['task_attach_file']['error'][$i];
                                $_FILES['task_attach_file[]']['size'] = $_FILES['task_attach_file']['size'][$i];

                                $config['upload_path'] = './files/tasks_attachment/';
                                $config['allowed_types'] = '*';
                                
                                $this->load->library('upload', $config);
                                $this->upload->initialize($config);
                                if($this->upload->do_upload('task_attach_file[]')){
                                    $fileData = $this->upload->data();
                                    $uploadData[$i]['task_attach_file'] = $fileData['file_name'];
                                    $uploadData[$i]['task_id'] = $task_id;
                                    $uploadData[$i]['project_id'] = $id;
                                    $uploadData[$i]['company_id'] = $this->sessionArr['company_id']; 
                                    $uploadData[$i]['role_id'] = $this->sessionArr['role_id'];
                                    $uploadData[$i]['user_id'] = $this->sessionArr['user_id'];
                                    $insert_task_attach = ProjectHasTasksAttachment::create($uploadData[$i]);

                                    $attributes = array('subject' => $this->lang->line('application_new_media_subject'), 'message' => '<b>' . $this->user->firstname . ' ' . $this->user->lastname . '</b> ' . $this->lang->line('application_uploaded'), 'datetime' => time(), 'project_id' => $id, 'type' => 'media', 'user_id' => $uploadData[$i]['user_id']);
                                    $activity = ProjectHasActivity::create($attributes);
                                }
                            }
                        }

                        
                        //echo "<pre>";print_r($uploadData);exit;
                    }
                    /*$message = Message::create($_POST);
                    if (!$message) {
                        $this->session->set_flashdata('message', 'error:' . $this->lang->line('messages_save_message_error'));
                    } else {
                        $this->session->set_flashdata('message', 'success:' . $this->lang->line('messages_save_message_success'));
                    }*/
                    redirect('scprojects/tasks/' . $id . '/view/' . $task_id);
                }
                $task = ProjectHasTask::find_by_id($task_id);
                if(!empty($task))
                {
                    $this->content_view = 'projects/sc_views/view_task';
                    $this->view_data['task'] = $task;
                    $get_task_attachment=$this->db->query('SELECT * from project_has_tasks_attachment where task_id="'.$task_id.'" and project_id="'.$id.'"')->result_array();
                    //echo "<pre>";print_r($get_task_attachment);exit;
                    //echo site_url();exit;
                    if(!empty($get_task_attachment))
                    {
                        $this->view_data['task_attachments']=$get_task_attachment;
                    }
                    
                    $project = Project::find_by_id($id);
                    if ($project->company_id != $this->sessionArr['company_id']) {
                        redirect('scprojects/');
                    }
                    $this->view_data['form_action'] = base_url().'scprojects/tasks/' . $id . '/view/' . $task_id;
                    //$this->view_data['filetype'] = explode('.', $this->view_data['media']->filename);
                    //$this->view_data['filetype'] = $this->view_data['filetype'][1];
                    $this->view_data['backlink'] = 'scprojects/view/' . $id.'#tasks-tab';
                }
                else
                {
                    redirect('scdashboard');
                }
                break;
            case 'add':
                $this->content_view = 'projects/sc_views/_tasks';
                if ($_POST) {
                    $public = $_POST['public'];
                    $user_id = $_POST['user_id'];
                    $name = $_POST['name'];
                    $priority = $_POST['priority'];
                    $status = $_POST['status'];
                    $value = $_POST['value'];
                    $due_date = $_POST['due_date'];
                    $description = $_POST['description'];
                    $post_arr = array(
                        'public' => $public,
                        'user_id' => $user_id,
                        'name' => $name,
                        'priority' => $priority,
                        'status' => $status,
                        'value' => $value,
                        'due_date' => $due_date,
                        'description' => $description,
                    );
                    
                    $post_arr['project_id'] = $id;
                    $task = ProjectHasTask::create($post_arr);

                    if (!empty($_POST['assign_client_id'])) {
                        $assign_arr = count($_POST['assign_client_id']);

                       /* $config_email['protocol']    = 'smtp';
                        $config_email['smtp_host']    = 'ssl://smtp.gmail.com';
                        $config_email['smtp_port']    = '465';
                        $config_email['smtp_timeout'] = '7';
                        $config_email['smtp_user']    = 'emailtesterone@gmail.com';
                        $config_email['smtp_pass']    = 'kgn@123456';
                        $config_email['charset']    = 'utf-8';
                        $config_email['newline']    = "\r\n";
                        $config_email['mailtype'] = 'html';
                        $config_email['validation'] = TRUE; // bool whether to validate email or not

                        $this->email->initialize($config_email);*/
                        
                        $this->load->library('email');

                        for ($i = 0; $i < $assign_arr; $i++) {
                            $assign_id = $_POST['assign_client_id'][$i];
                            $newArr = array('task_id' => $task->id, 'project_id' => $task->project_id, 'assign_user_id' => $assign_id);
                            $insert_data = ProjectAssignTasks::create($newArr);
                            
                            $get_user_details= User::find_by_id($_POST['assign_client_id'][$i]);
                            
                            $project_details= Project::find_by_id($id);

                            $get_user_role= $this->db->query('select * from user_roles where company_id="'.$this->sessionArr['company_id'].'" and user_id="'.$_POST['assign_client_id'][$i].'"')->row_array();
                            
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

                            if(!empty($get_user_role))
                            {
                                $role_id=$get_user_role['role_id'];
                                $project_link;
                                if($role_id==3)
                                {
                                    $project_link = base_url().'cprojects/view/'.$id;
                                }
                                elseif($role_id==4)
                                {
                                    $project_link = base_url().'scprojects/view/'.$id;
                                }
                                else
                                {
                                    $project_link = base_url().'aoprojects/view/'.$id;
                                }
                                
                            }
                            //echo "<pre>";print_r($get_user_details->email);
                            $this->email->from($this->settings->from_email_id,$this->settings->from_email_name);
                            $this->email->to($get_user_details->email);
                            $this->email->subject('Spera '.$name.' Assign for '.$project_details->name.'');
                            
                            $send_message="Hi ".trim($get_user_details->firstname." ".$get_user_details->lastname)."<br/>
                              <p>Company_Name: ".$this->sessionArr['company_name']."</p><br/>
                              <p>Company_Logo: <img src='".$company_logo."' alt='image'/></p><br/>
                              <p>Task Link: ".$project_link."</p><br/>
                              <p>Project Name: ".$project_details->name."</p><br/>
                              <p>Project Description: ".$project_details->description."</p><br/>
                              <p>Task Name: ".$name."</p><br/>
                              <p>Task Description: ".$description."</p><br/><br/><br/>
                              Thanks<br/>
                              Spera Team";  
                            $this->email->message($send_message);           
                            $mail_sent = null;
                            if($this->email->send()) {
                                $mail_sent = 'Task Assign mail sent.';
                            }
                        }
                    }
                    if (!$task) {
                        $this->session->set_flashdata('message', 'error:' . $this->lang->line('messages_save_task_error'));
                    } else {
                        $this->session->set_flashdata('message', 'success:' . $this->lang->line('messages_save_task_success'));
                    }
                    redirect('scprojects/view/' . $id.'#tasks-tab');
                } else {
                    $this->theme_view = 'modal';
                    $this->view_data['project'] = Project::find($id);
                    $this->view_data['title'] = $this->lang->line('application_add_task');
                    
                    $clients = $this->db->query('SELECT u.*,r.role_id FROM project_assign_clients c INNER JOIN user_roles r ON r.company_id = c.company_id AND r.user_id = c.assign_user_id INNER JOIN users u ON c.assign_user_id = u.id WHERE c.project_id = "'.$id.'" AND c.company_id = "'.$this->sessionArr['company_id'].'" AND u.status = "active"')->result_array();
                    //var_dump($clients);exit;
                    if(!empty($clients))
                    {
                        
                        $get_account_owner = $this->db->query('SELECT u. * FROM user_roles r LEFT JOIN users u ON r.user_id = u.id WHERE r.company_id ="'.$this->sessionArr['company_id'].'" AND r.role_id = "2" and u.status="active"')->row_array();
                        //var_dump($get_account_owner);
                        $client_arr=array();
                        $client_arr['Account-owner'][0]['id'] =         $get_account_owner['id'];
                        $client_arr['Account-owner'][0]['firstname'] =  $get_account_owner['firstname'];
                        $client_arr['Account-owner'][0]['lastname'] =   $get_account_owner['lastname'];
                        $j=0;
                        foreach($clients as $newclient)
                        {
                            if($newclient['role_id']==4)
                            {
                                if($this->user->id==$newclient['id'])
                                {
                                    $client_arr['Me'][$j]['id'] = $newclient['id'];
                                    $client_arr['Me'][$j]['firstname'] = $newclient['firstname'];
                                    $client_arr['Me'][$j]['lastname'] = $newclient['lastname'];    
                                }
                                else
                                {
                                    $client_arr['Sub-contractors'][$j]['id'] = $newclient['id'];
                                    $client_arr['Sub-contractors'][$j]['firstname'] = $newclient['firstname'];
                                    $client_arr['Sub-contractors'][$j]['lastname'] = $newclient['lastname'];    
                                }
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
                    $this->view_data['clients']=$client_arr;
                    $this->view_data['form_action'] = base_url().'scprojects/tasks/' . $id . '/add';
                    $this->content_view = 'projects/sc_views/_tasks';
                }
                break;
            case 'update':
                $this->content_view = 'projects/sc_views/_tasks';
                $this->view_data['task'] = ProjectHasTask::find_by_id($task_id);
                $t_assign_query = 'SELECT assign_user_id from project_assign_tasks where task_id="' . $task_id . '"';
                $task_assign_users = $this->db->query($t_assign_query)->result_array();
                if(!empty($task_assign_users))
                {
                    $this->view_data['task_assign_users'] = array_column($task_assign_users, 'assign_user_id');
                }
                else
                {
                    $this->view_data['task_assign_users'] = array();
                }

                if ($_POST) {
                    $public = $_POST['public'];
                    $user_id = $_POST['user_id'];
                    $name = $_POST['name'];
                    $priority = $_POST['priority'];
                    $status = $_POST['status'];
                    $value = $_POST['value'];
                    $due_date = $_POST['due_date'];
                    $description = $_POST['description'];
                    $post_arr = array(
                        'public' => $public,
                        'user_id' => $user_id,
                        'name' => $name,
                        'priority' => $priority,
                        'status' => $status,
                        'value' => $value,
                        'due_date' => $due_date,
                        'description' => $description,
                    );
                
                    $task = ProjectHasTask::find_by_id($task_id);
                    if ($task->user_id != $user_id) {
                        //stop timer and add time to timesheet
                        if ($task->tracking != 0) {
                            $now = time();
                            $diff = $now - $task->tracking;
                            $timer_start = $task->tracking;
                            $task->time_spent = $task->time_spent + $diff;
                            $task->tracking = "";
                            $attributes = array(
                                'task_id' => $task->id,
                                'user_id' => $task->user_id,
                                'project_id' => $task->project_id,
                                'client_id' => 0,
                                'time' => $diff,
                                'start' => $timer_start,
                                'end' => $now
                            );
                            $timesheet = ProjectHasTimesheet::create($attributes);
                        }
                    }
                    $task->update_attributes($post_arr);

                    if (!empty($_POST['assign_client_id'])) {
                        $delete_task_assign_user = "DELETE from project_assign_tasks where task_id='" . $task_id . "'";
                        $this->db->query($delete_task_assign_user);
                        $update_assign_arr = count($_POST['assign_client_id']);
                        
                        /*$config_email['protocol']    = 'smtp';
                        $config_email['smtp_host']    = 'ssl://smtp.gmail.com';
                        $config_email['smtp_port']    = '465';
                        $config_email['smtp_timeout'] = '7';
                        $config_email['smtp_user']    = 'emailtesterone@gmail.com';
                        $config_email['smtp_pass']    = 'kgn@123456';
                        $config_email['charset']    = 'utf-8';
                        $config_email['newline']    = "\r\n";
                        $config_email['mailtype'] = 'html';
                        $config_email['validation'] = TRUE; // bool whether to validate email or not

                        $this->email->initialize($config_email);*/
                        
                        $this->load->library('email');

                        $task_assigned_users=array_diff($_POST['assign_client_id'],$this->view_data['task_assign_users']);
                        
                        if(!empty($task_assigned_users))
                        {
                            $count_task_assign_users=count($task_assigned_users);
                            for($mn=0; $mn < $count_task_assign_users; $mn++)
                            {
                                $get_user_details= User::find_by_id($task_assigned_users[$mn]);
                            
                                $project_details= Project::find_by_id($id);

                                $get_user_role= $this->db->query('select * from user_roles where company_id="'.$this->sessionArr['company_id'].'" and user_id="'.$task_assigned_users[$mn].'"')->row_array();
                                
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

                                if(!empty($get_user_role))
                                {
                                    $role_id=$get_user_role['role_id'];
                                    $project_link;
                                    if($role_id==3)
                                    {
                                        $project_link = base_url().'cprojects/view/'.$id;
                                    }
                                    elseif($role_id==4)
                                    {
                                        $project_link = base_url().'scprojects/view/'.$id;
                                    }
                                    else
                                    {
                                        $project_link = base_url().'aoprojects/view/'.$id;
                                    }
                                    
                                }
                                //echo "<pre>";print_r($get_user_details->email);
                                $this->email->from($this->settings->from_email_id,$this->settings->from_email_name);
                                $this->email->to($get_user_details->email);
                                $this->email->subject('Spera '.$name.' Assign for '.$project_details->name.'');
                                
                                $send_message="Hi ".trim($get_user_details->firstname." ".$get_user_details->lastname)."<br/>
                                  <p>Company_Name: ".$this->sessionArr['company_name']."</p><br/>
                                  <p>Company_Logo: <img src='".$company_logo."' alt='image'/></p><br/>
                                  <p>Task Link: ".$project_link."</p><br/>
                                  <p>Project Name: ".$project_details->name."</p><br/>
                                  <p>Project Description: ".$project_details->description."</p><br/>
                                  <p>Task Name: ".$name."</p><br/>
                                  <p>Task Description: ".$description."</p><br/><br/><br/>
                                  Thanks<br/>
                                  Spera Team";  
                                $this->email->message($send_message);           
                                $mail_sent = null;
                                if($this->email->send()) {
                                    $mail_sent = 'Task Assign mail sent.';
                                }
                            }
                        }
                        
                        for ($j = 0; $j < $update_assign_arr; $j++) {
                            $update_assign_id = $_POST['assign_client_id'][$j];
                            $update_newArr = array('task_id' => $task->id, 'project_id' => $task->project_id, 'assign_user_id' => $update_assign_id);
                            $update_data = ProjectAssignTasks::create($update_newArr);
                        }
                    }
                    else
                    {
                        $delete_task_assign_user = "DELETE from project_assign_tasks where task_id='" . $task_id . "'";
                        $this->db->query($delete_task_assign_user);
                    }
                    if (!$task) {
                        $this->session->set_flashdata('message', 'error:' . $this->lang->line('messages_save_task_error'));
                    } else {
                        if(isset($files_duplicate))
                        {
                            $this->session->set_flashdata('message', 'success:' . $this->lang->line('messages_save_task_success').' '.$files_duplicate);
                        }
                        else
                        {
                            $this->session->set_flashdata('message', 'success:' . $this->lang->line('messages_save_task_success'));
                        }
                    }
                    redirect('scprojects/view/' . $id.'#tasks-tab');
                } else {
                    $this->theme_view = 'modal';
                    $this->view_data['project'] = Project::find($id);
                    $this->view_data['title'] = $this->lang->line('application_edit_task');
                    $this->view_data['form_action'] = base_url().'scprojects/tasks/' . $id . '/update/' . $task_id;
                    $clients = $this->db->query('SELECT u.*,r.role_id FROM project_assign_clients c INNER JOIN user_roles r ON r.company_id = c.company_id AND r.user_id = c.assign_user_id INNER JOIN users u ON c.assign_user_id = u.id WHERE c.project_id = "'.$id.'" AND c.company_id = "'.$this->sessionArr['company_id'].'" AND u.status = "active"')->result_array();

                    if(!empty($clients))
                    {
                        $client_arr=array();
                        $get_account_owner = $this->db->query('SELECT u. * FROM user_roles r LEFT JOIN users u ON r.user_id = u.id WHERE r.company_id ="'.$this->sessionArr['company_id'].'" AND r.role_id = "2" and u.status="active"')->row_array();
                        //var_dump($get_account_owner);
                        $client_arr=array();
                        $client_arr['Account-owner'][0]['id'] =         $get_account_owner['id'];
                        $client_arr['Account-owner'][0]['firstname'] =  $get_account_owner['firstname'];
                        $client_arr['Account-owner'][0]['lastname'] =   $get_account_owner['lastname'];
                        $j=0;
                        foreach($clients as $newclient)
                        {
                            if($newclient['role_id']==4)
                            {
                                if($this->user->id==$newclient['id'])
                                {
                                    $client_arr['Me'][$j]['id'] = $newclient['id'];
                                    $client_arr['Me'][$j]['firstname'] = $newclient['firstname'];
                                    $client_arr['Me'][$j]['lastname'] = $newclient['lastname'];    
                                }
                                else
                                {
                                    $client_arr['Sub-contractors'][$j]['id'] = $newclient['id'];
                                    $client_arr['Sub-contractors'][$j]['firstname'] = $newclient['firstname'];
                                    $client_arr['Sub-contractors'][$j]['lastname'] = $newclient['lastname'];    
                                }
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
                    $this->view_data['clients']=$client_arr;
                    $this->content_view = 'projects/sc_views/_tasks';
                }
                break;
            case 'check':
                $task = ProjectHasTask::find($task_id);
                if ($task->status == 'done') {
                    $task->status = 'open';
                } else {
                    $task->status = 'done';
                }
                $task->save();
                $project = Project::find($id);
                $tasks = ProjectHasTask::count(array('conditions' => 'project_id = '.$id));
                $tasks_done = ProjectHasTask::count(array('conditions' => array('status = ? AND project_id = ?', 'done', $id)));
                if ($project->progress_calc == 1) {
                    if ($tasks) {
                        $progress = round($tasks_done / $tasks * 100);
                    }
                    $attr = array('progress' => $progress);
                    $project->update_attributes($attr);
                }
                if (!$task) {
                    $this->session->set_flashdata('message', 'error:' . $this->lang->line('messages_save_task_error'));
                }
                $this->theme_view = 'ajax';
                $this->content_view = 'projects/sc_views/tasks';
                break;
            case 'delete':

                $tasks_delete_attachment = $this->db->query("SELECT * from project_has_tasks_attachment where task_id='".$task_id."'")->result_array();
                if(!empty($tasks_delete_attachment))
                {
                    foreach ($tasks_delete_attachment as $kk => $vv) 
                    {
                       $path = FCPATH.'files/tasks_attachment/'.$vv['task_attach_file'];
                       if(file_exists($path))
                       {
                          unlink($path);
                       }
                    }
                    $delete_task_attachment=$this->db->query('DELETE From project_has_tasks_attachment where task_id="'.$task_id.'"');
                }
                else
                {
                    $delete_attachement_error= 'Error in delete tasks attachement';
                }
                
                $delete_assign_users = ProjectAssignTasks::find('all', array('task_id' => $task_id));
                if (count($delete_assign_users) > 0) {
                    $delete_task_assign_user = "DELETE from project_assign_tasks where task_id='" . $task_id . "'";
                    $this->db->query($delete_task_assign_user);
                }
                $task = ProjectHasTask::find($task_id);
                $task->delete();
                if (!$task) {
                    $this->session->set_flashdata('message', 'error:' . $this->lang->line('messages_delete_task_error'));
                } else {
                    if(isset($delete_attachement_error))
                    {
                        $this->session->set_flashdata('message', 'success:' . $this->lang->line('messages_delete_task_success').' '.$delete_attachement_error);

                    }
                    else
                    {
                        $this->session->set_flashdata('message', 'success:' . $this->lang->line('messages_delete_task_success'));

                    }
                }
                redirect('scprojects/view/' . $id.'#tasks-tab');
                break;
            default:
                $this->view_data['project'] = Project::find($id);
                $this->content_view = 'projects/sc_views/tasks';
                break;
        }
    }

    //write note for single project
    function notes($id = FALSE) {
        if ($_POST) {
            unset($_POST['send']);
            $_POST = array_map('htmlspecialchars', $_POST);
            $_POST['note'] = strip_tags($_POST['note']);
            $project = Project::find($id);
            $project->update_attributes($_POST);
        }
        $this->theme_view = 'ajax';
    }

    //delete message for single product
    function deletemessage($project_id, $media_id, $id) {
        $from = $this->user->firstname . ' ' . $this->user->lastname;
        $message = Message::find($id);
        if ($message->from == $this->user->firstname . " " . $this->user->lastname) {
            $message->delete();
        }
        if (!$message) {
            $this->session->set_flashdata('message', 'error:' . $this->lang->line('messages_delete_message_error'));
        } else {
            $this->session->set_flashdata('message', 'success:' . $this->lang->line('messages_delete_message_success'));
        }
        redirect('scprojects/media/' . $project_id . '/view/' . $media_id);
    }

    //download files
    function download($media_id = FALSE) {

        $this->load->helper('download');
        $media = ProjectHasFile::find($media_id);
        $project = Project::find_by_id($media->project_id);
        if ($project->company_id != $this->sessionArr['company_id']) {
            redirect('scprojects/');
        }
        $media->download_counter = $media->download_counter + 1;
        $media->save();

        $data = file_get_contents('./files/media/' . $media->savename);
        $name = $media->filename;
        force_download($name, $data);
    }

    //Activities for single project
    function activity($id = FALSE, $condition = FALSE, $activityID = FALSE) {
        $this->load->helper('notification');
        $project = Project::find_by_id($id);
        //$activity = ProjectHasAktivity::find_by_id($activityID);
        switch ($condition) {
            case 'add':
                if ($_POST) {
                    unset($_POST['send']);
                    if($_POST['message'] != '<p><br></p>')
                    {
                        $_POST['subject'] = htmlspecialchars($_POST['subject']);
                        $_POST['message'] = strip_tags($_POST['message'], '<br><br/><p></p><a></a><b></b><i></i><u></u><span></span>');
                        $_POST['project_id'] = $id;
                        $_POST['user_id'] = $this->user->id;
                        $_POST['type'] = "comment";
                        unset($_POST['files']);
                        $_POST['datetime'] = time();
                        $activity = ProjectHasActivity::create($_POST);
                        if (!$activity) {
                            $this->session->set_flashdata('message', 'error:' . $this->lang->line('messages_save_error'));
                        } else {
                            $this->session->set_flashdata('message', 'success:' . $this->lang->line('messages_save_success'));
                            foreach ($project->project_has_workers as $workers) {
                                send_notification($workers->user->email, "[" . $project->name . "] " . $_POST['subject'], $_POST['message'] . '<br><strong>' . $project->name . '</strong>');
                            }
                            if ($this->sessionArr['email']) {
                                send_notification($this->sessionArr['email'], "[" . $project->name . "] " . $_POST['subject'], $_POST['message'] . '<br><strong>' . $project->name . '</strong>');
                            }
                        }
                    }
                    //redirect('projects/view/'.$id);
                }
                break;
            case 'update':

                break;
            case 'delete':

                break;
        }
    }
    
    //All timesheets for tasks
    function timesheets($taskid) {

        $this->view_data['timesheets'] = ProjectHasTimesheet::find("all", array("conditions" => array("task_id = ?", $taskid)));
        $this->view_data['task'] = ProjectHasTask::find_by_id($taskid);

        $this->theme_view = 'modal';
        $this->view_data['title'] = $this->lang->line('application_timesheet');
        $this->view_data['form_action'] = base_url().'scprojects/timesheet_add';
        $this->content_view = 'projects/sc_views/_timesheets';
    }

    //Add timesheet
    function timesheet_add() {
        if ($_POST) {
            $time = ($_POST["hours"] * 3600) + ($_POST["minutes"] * 60);
            if($_POST["start"]=='')
            {
                $_POST["start"]=date('Y-m-d');
            }
            if($_POST["end"]=='')
            {
                $_POST["end"]=date('Y-m-d');
            }
            if($_POST["description"]=='')
            {
                $_POST["description"]="desc";
            }
            $attr = array(
                "project_id" => $_POST["project_id"],
                "user_id" => $_POST["user_id"],
                "time" => $time,
                "client_id" => 0,
                "task_id" => $_POST["task_id"],
                "start" => $_POST["start"],
                "end" => $_POST["end"],
                "invoice_id" => 0,
                "description" => $_POST["description"],
            );
            $timesheet = ProjectHasTimesheet::create($attr);
            $task = ProjectHasTask::find_by_id($timesheet->task_id);
            $task->time_spent = $task->time_spent + $time;
            $task->save();
            echo $timesheet->id;
        }
        $this->theme_view = 'blank';
    }

    //delete single timesheet
    function timesheet_delete($timesheet_id) {

        $timesheet = ProjectHasTimesheet::find_by_id($timesheet_id);
        $task = ProjectHasTask::find_by_id($timesheet->task_id);
        $task->time_spent = $task->time_spent - $timesheet->time;
        $task->save();
        $timesheet->delete();
        $this->theme_view = 'blank';
    }

    //Add/update/delete milestoens
    function milestones($id = FALSE, $condition = FALSE, $milestone_id = FALSE) {
        
        $this->view_data['submenu'] = array(
            $this->lang->line('application_back') => 'scprojects',
            $this->lang->line('application_overview') => 'scprojects/view/' . $id,
        );
        switch ($condition) {
            case 'comment':
                if ($_POST) {
                    unset($_POST['send']);
                    if($_POST['message'] != '<p><br></p>')
                    {
                        $_POST['message'] = strip_tags($_POST['message'], '<br><br/><p></p><a></a><b></b><i></i><u></u><span></span>');
                        $_POST['project_id'] = $id;
                        $_POST['company_id'] = $this->sessionArr['company_id'];
                        $_POST['milestone_id'] = $milestone_id;
                        $_POST['user_id'] = $this->user->id;
                        unset($_POST['files']);
                        $_POST['datetime'] = time();
                         //echo "<pre>";print_r($_POST);exit;
                        $comment = ProjectHasMilestonesComment::create($_POST);
                        $subject = "milestone-comment";
                        $message = $_POST['message'];
                        $user_id = $_POST['user_id'];
                        $datetime = $_POST['datetime'];
                        $type="comment";
                        
                        $activity_arr=array(
                            "subject"=>$subject,
                            "message"=>$message,
                            "project_id"=>$id,
                            "user_id"=>$user_id,
                            "datetime"=>$datetime,
                            "type"=>$type
                        );
                        $activity = ProjectHasActivity::create($activity_arr);
                        if (!$comment) {
                            $this->session->set_flashdata('message', 'error:' . $this->lang->line('messages_save_error'));
                        } else {
                            $this->session->set_flashdata('message', 'success:' . $this->lang->line('messages_save_success'));
                        }
                    }
                    //redirect('projects/view/'.$id);
                }

                $milestone = ProjectHasMilestone::find_by_id($milestone_id);
                if(!empty($milestone))
                {
                    $this->view_data['milestone'] = $milestone;
                    $this->content_view = 'projects/sc_views/view_comment';
                    $get_milestone_comment=$this->db->query('SELECT * from project_has_milestones_comment where milestone_id="'.$milestone_id.'" and project_id="'.$id.'" order by id desc')->result_array();
                    
                    if(!empty($get_milestone_comment))
                    {
                        $this->view_data['milestone_comments']=$get_milestone_comment;
                    }
                    
                    $project = Project::find_by_id($id);
                    if ($project->company_id != $this->sessionArr['company_id']) {
                        redirect('scprojects/');
                    }
                    $this->view_data['form_action'] = base_url().'scprojects/milestones/' . $id . '/view/' . $milestone_id;
                    $this->view_data['backlink'] = 'scprojects/view/' . $id.'#milestones-tab';
                }
                else
                {
                    redirect('scdashboard');
                }
                break;
            case 'view':
                if($_POST) 
                {
                    //echo "<pre>";print_r($_FILES);exit;
                    $directoryName = './files/milestone_attachment/';
                    if (!is_dir($directoryName)) {
                        //Directory does not exist, so lets create it.
                        mkdir($directoryName, 0755);
                    }
                    if(!empty($_FILES['milestone_attach_file']['name'])) 
                    {
                        
                        $filesCount = count($_FILES['milestone_attach_file']['name']);
                        $uploadData=array();
                        for($i = 0; $i < $filesCount; $i++)
                        {
                            if($_FILES['milestone_attach_file']['error'][$i]==0)
                            {
                                $_FILES['milestone_attach_file[]']['name'] = $_FILES['milestone_attach_file']['name'][$i];
                                $_FILES['milestone_attach_file[]']['type'] = $_FILES['milestone_attach_file']['type'][$i];
                                $_FILES['milestone_attach_file[]']['tmp_name'] = $_FILES['milestone_attach_file']['tmp_name'][$i];
                                $_FILES['milestone_attach_file[]']['error'] = $_FILES['milestone_attach_file']['error'][$i];
                                $_FILES['milestone_attach_file[]']['size'] = $_FILES['milestone_attach_file']['size'][$i];

                                $config['upload_path'] = './files/milestone_attachment/';
                                $config['allowed_types'] = '*';
                                
                                $this->load->library('upload', $config);
                                $this->upload->initialize($config);
                                if($this->upload->do_upload('milestone_attach_file[]')){
                                    $fileData = $this->upload->data();
                                    $uploadData[$i]['milestone_attach_file'] = $fileData['file_name'];
                                    $uploadData[$i]['milestone_id'] = $milestone_id;
                                    $uploadData[$i]['project_id'] = $id;
                                    $uploadData[$i]['company_id'] = $this->sessionArr['company_id']; 
                                    $uploadData[$i]['role_id'] = $this->sessionArr['role_id'];
                                    $uploadData[$i]['user_id'] = $this->sessionArr['user_id'];
                                    $insert_milestone_attach = ProjectHasMilestonesAttachment::create($uploadData[$i]);

                                    $attributes = array('subject' => $this->lang->line('application_new_media_subject'), 'message' => '<b>' . $this->user->firstname . ' ' . $this->user->lastname . '</b> ' . $this->lang->line('application_uploaded'), 'datetime' => time(), 'project_id' => $id, 'type' => 'media', 'user_id' => $uploadData[$i]['user_id']);
                                    $activity = ProjectHasActivity::create($attributes);
                                }
                            }
                        }
                        //echo "<pre>";print_r($uploadData);exit;
                    }
                    /*$message = Message::create($_POST);
                    if (!$message) {
                        $this->session->set_flashdata('message', 'error:' . $this->lang->line('messages_save_message_error'));
                    } else {
                        $this->session->set_flashdata('message', 'success:' . $this->lang->line('messages_save_message_success'));
                    }*/
                    redirect('scprojects/milestones/' . $id . '/view/' . $milestone_id);
                }

                $milestone = ProjectHasMilestone::find_by_id($milestone_id);
                if(!empty($milestone))
                {
                    $this->view_data['milestone'] = $milestone;
                    $this->content_view = 'projects/sc_views/view_milestone';
                    $get_milestone_attachment=$this->db->query('SELECT * from project_has_milestones_attachment where milestone_id="'.$milestone_id.'" and project_id="'.$id.'"')->result_array();
                    //echo "<pre>";print_r($get_milestone_attachment);exit;
                    //echo site_url();exit;
                    if(!empty($get_milestone_attachment))
                    {
                        $this->view_data['milestone_attachments']=$get_milestone_attachment;
                    }
                    //$this->view_data['attachement'] = ProjectHasMilestonesAttachment::find_by_milestone_id($milestone_id);
                    //var_dump($this->view_data['attachement']);exit;
                    $project = Project::find_by_id($id);
                    if ($project->company_id != $this->sessionArr['company_id']) {
                        redirect('scprojects/');
                    }
                    $this->view_data['form_action'] = base_url().'scprojects/milestones/' . $id . '/view/' . $milestone_id;
                    //$this->view_data['filetype'] = explode('.', $this->view_data['media']->filename);
                    //$this->view_data['filetype'] = $this->view_data['filetype'][1];
                    $this->view_data['backlink'] = 'scprojects/view/' . $id.'#milestones-tab';
                }
                else
                {   
                    redirect('scdashboard');
                }
                break;
            case 'add':
                $this->content_view = 'projects/sc_views/_milestones';
                if ($_POST) {
                    //unset($_POST['send']);
                    //unset($_POST['files']);
                    //$description = $_POST['description'];
                    /*$_POST = array_map('htmlspecialchars', $_POST);
                    $_POST['description'] = $description;
                    $_POST['project_id'] = $id;*/

                    $name = isset($_POST['name'])?$_POST['name']:'';
                    $start_date = isset($_POST['start_date'])?$_POST['start_date']:'';
                    $due_date = isset($_POST['due_date'])? $_POST['due_date']:'';
                    $description = isset($_POST['description']) ?$_POST['description']:'';
                    $project_id = $id;

                    $milestone_arr=array(
                        'name'=>$name,
                        'start_date'=>$start_date,
                        'due_date'=>$due_date,
                        'description'=>$description,
                        'project_id'=>$project_id
                    );
                    //echo "<pre>";print_r($_FILES);exit;
                    $milestone = ProjectHasMilestone::create($milestone_arr);
                    

                    $directoryName = './files/milestone_attachment/';
                    if (!is_dir($directoryName)) {
                        //Directory does not exist, so lets create it.
                        mkdir($directoryName, 0755);
                    }

                    if(!empty($_FILES['milestone_attach_file']['name'])) 
                    {
                        
                        $filesCount = count($_FILES['milestone_attach_file']['name']);
                        $uploadData=array();
                        for($i = 0; $i < $filesCount; $i++)
                        {
                            if($_FILES['milestone_attach_file']['error'][$i]==0)
                            {
                                $_FILES['milestone_attach_file[]']['name'] = $_FILES['milestone_attach_file']['name'][$i];
                                $_FILES['milestone_attach_file[]']['type'] = $_FILES['milestone_attach_file']['type'][$i];
                                $_FILES['milestone_attach_file[]']['tmp_name'] = $_FILES['milestone_attach_file']['tmp_name'][$i];
                                $_FILES['milestone_attach_file[]']['error'] = $_FILES['milestone_attach_file']['error'][$i];
                                $_FILES['milestone_attach_file[]']['size'] = $_FILES['milestone_attach_file']['size'][$i];

                                $config['upload_path'] = './files/milestone_attachment/';
                                $config['allowed_types'] = '*';
                                
                                $this->load->library('upload', $config);
                                $this->upload->initialize($config);
                                if($this->upload->do_upload('milestone_attach_file[]')){
                                    $fileData = $this->upload->data();
                                    $uploadData[$i]['milestone_attach_file'] = $fileData['file_name'];
                                    $uploadData[$i]['milestone_id'] = $milestone->id;
                                    $uploadData[$i]['project_id'] = $project_id;
                                    $uploadData[$i]['company_id'] = $this->sessionArr['company_id']; 
                                    $uploadData[$i]['role_id'] = $this->sessionArr['role_id'];
                                    $uploadData[$i]['user_id'] = $this->sessionArr['user_id'];
                                    $insert_milestone_attach = ProjectHasMilestonesAttachment::create($uploadData[$i]);
                                }
                            }
                        }
                        //echo "<pre>";print_r($uploadData);exit;
                    }
                    if (!$milestone) {
                        $this->session->set_flashdata('message', 'error:' . $this->lang->line('messages_save_milestone_error'));
                    } else {
                        $this->session->set_flashdata('message', 'success:' . $this->lang->line('messages_save_milestone_success'));
                    }
                    redirect('scprojects/view/' . $id.'#milestones-tab');
                } else {
                    $this->theme_view = 'modal';
                    $this->view_data['project'] = Project::find($id);
                    $this->view_data['title'] = $this->lang->line('application_add_milestone');
                    $this->view_data['form_action'] = base_url().'scprojects/milestones/' . $id . '/add';
                    $this->content_view = 'projects/sc_views/_milestones';
                }
                break;
            case 'update':
                $this->content_view = 'projects/sc_views/_milestones';
                $this->view_data['milestone'] = ProjectHasMilestone::find($milestone_id);
                
                $get_milestone_attachment=$this->db->query('SELECT * from project_has_milestones_attachment where milestone_id="'.$milestone_id.'"')->result_array();
                //echo "<pre>";print_r($get_milestone_attachment);exit;
                //echo site_url();exit;
                if(!empty($get_milestone_attachment))
                {
                    $this->view_data['milestone_attachments']=$get_milestone_attachment;
                }

                if ($_POST) {
                    //unset($_POST['send']);
                    //unset($_POST['files']);
                    //$description = $_POST['description'];
                    //$_POST = array_map('htmlspecialchars', $_POST);
                    //$_POST['description'] = $description;
                    
                    $milestone_id = $_POST['id'];
                    $name = isset($_POST['name'])?$_POST['name']:'';
                    $start_date = isset($_POST['start_date'])?$_POST['start_date']:'';
                    $due_date = isset($_POST['due_date'])? $_POST['due_date']:'';
                    $description = isset($_POST['description']) ?$_POST['description']:'';
                    $project_id = $id;

                    $milestone_arr=array(
                        'name'=>$name,
                        'start_date'=>$start_date,
                        'due_date'=>$due_date,
                        'description'=>$description,
                        'project_id'=>$project_id,
                    );

                    $milestone = ProjectHasMilestone::find($milestone_id);
                    $milestone->update_attributes($milestone_arr);

                    $directoryName = './files/milestone_attachment/';
                    if (!is_dir($directoryName)) {
                        //Directory does not exist, so lets create it.
                        mkdir($directoryName, 0755);
                    }

                    if(!empty($_FILES['milestone_attach_file']['name'])) 
                    {
                        
                        $filesCount = count($_FILES['milestone_attach_file']['name']);
                        $uploadData=array();
                        for($i = 0; $i < $filesCount; $i++)
                        {
                            if($_FILES['milestone_attach_file']['error'][$i]==0)
                            {
                                $_FILES['milestone_attach_file[]']['name'] = $_FILES['milestone_attach_file']['name'][$i];
                                $_FILES['milestone_attach_file[]']['type'] = $_FILES['milestone_attach_file']['type'][$i];
                                $_FILES['milestone_attach_file[]']['tmp_name'] = $_FILES['milestone_attach_file']['tmp_name'][$i];
                                $_FILES['milestone_attach_file[]']['error'] = $_FILES['milestone_attach_file']['error'][$i];
                                $_FILES['milestone_attach_file[]']['size'] = $_FILES['milestone_attach_file']['size'][$i];

                                $config['upload_path'] = './files/milestone_attachment/';
                                $config['allowed_types'] = '*';
                                
                                if($get_milestone_attachment[$i]['milestone_attach_file'] != $_FILES['milestone_attach_file']['name'][$i])
                                {
                                    //if($get_milestone_attachment[])
                                    $this->load->library('upload', $config);
                                    $this->upload->initialize($config);
                                    if($this->upload->do_upload('milestone_attach_file[]')){
                                        $fileData = $this->upload->data();
                                        $uploadData[$i]['milestone_attach_file'] = $fileData['file_name'];
                                        $uploadData[$i]['milestone_id'] = $milestone->id;
                                        $uploadData[$i]['project_id'] = $id;
                                        $uploadData[$i]['company_id'] = $this->sessionArr['company_id']; 
                                        $uploadData[$i]['role_id'] = $this->sessionArr['role_id'];
                                        $uploadData[$i]['user_id'] = $this->sessionArr['user_id'];
                                        $insert_milestone_attach = ProjectHasMilestonesAttachment::create($uploadData[$i]);
                                    }
                                }
                                else
                                {
                                    $files_duplicate = $_FILES['milestone_attach_file']['name'][$i].'Attachment already exits';
                                }
                            }
                        }
                        //exit;
                        //echo "<pre>";print_r($uploadData);exit;
                    }

                    if (!$milestone) {
                        $this->session->set_flashdata('message', 'error:' . $this->lang->line('messages_save_milestone_error'));
                    } else {
                        //$this->session->set_flashdata('message', 'success:' . $this->lang->line('messages_save_milestone_success'));
                        if(isset($files_duplicate))
                        {
                            $this->session->set_flashdata('message', 'success:' . $this->lang->line('messages_save_milestone_success').' '.$files_duplicate);
                        }
                        else
                        {
                            $this->session->set_flashdata('message', 'success:' . $this->lang->line('messages_save_milestone_success'));
                        }
                    }
                    redirect('scprojects/view/' . $id.'#milestones-tab');
                } else {
                    $this->theme_view = 'modal';
                    $this->view_data['project'] = Project::find($id);
                    $this->view_data['title'] = $this->lang->line('application_edit_milestone');
                    $this->view_data['form_action'] = base_url().'scprojects/milestones/' . $id . '/update/' . $milestone_id;
                    $this->content_view = 'projects/sc_views/_milestones';
                }
                break;
            case 'delete':
                $milestone_delete_attachment = $this->db->query("SELECT * from project_has_milestones_attachment where milestone_id='".$milestone_id."'")->result_array();
                if(!empty($milestone_delete_attachment))
                {
                    foreach ($milestone_delete_attachment as $kk => $vv) 
                    {
                       $path = FCPATH.'files/milestone_attachment/'.$vv['milestone_attach_file'];
                       if(file_exists($path))
                       {
                          unlink($path);
                       }
                    }
        $delete_mile_attachment=$this->db->query('DELETE From project_has_milestones_attachment where milestone_id="'.$milestone_id.'"');
                }
                
                $milestone = ProjectHasMilestone::find($milestone_id);
                foreach ($milestone->project_has_tasks as $value) {
                    $value->milestone_id = "";
                    $value->save();
                }
                $milestone->delete();

                if (!$milestone) {
                    $this->session->set_flashdata('message', 'error:' . $this->lang->line('messages_delete_milestone_error'));
                } else {
                    if(isset($delete_attachement_error))
                    {
                        $this->session->set_flashdata('message', 'success:' . $this->lang->line('messages_delete_milestone_success').' '.$delete_attachement_error);
                    }
                    else
                    {
                        $this->session->set_flashdata('message', 'success:' . $this->lang->line('messages_delete_milestone_success'));
                    }
                }
                redirect('scprojects/view/' . $id.'#milestones-tab');
                break;
            default:
                $this->view_data['project'] = Project::find($id);
                $this->content_view = 'projects/sc_views/milestones/';
                break;
        }
    }

    function delete_milestone_attachement($project_id=FALSE,$condition=FALSE,$milestone_id=FALSE,$milestone_attach_id=FALSE)
    {
        if(!empty($condition) && !empty($milestone_attach_id))
        {
            if($condition=='delete')
            {
                $get_attachment_name=ProjectHasMilestonesAttachment::find_by_id($milestone_attach_id);
                //echo "<pre>";print_r($get_attachment_name->milestone_attach_file);exit;
                $path=FCPATH.'files/milestone_attachment/'.$get_attachment_name->milestone_attach_file;
                unlink($path);
                $delete_attachment=$this->db->query("DELETE from project_has_milestones_attachment where id=".$milestone_attach_id."");
                if (!$delete_attachment) {
                    $this->session->set_flashdata('message', 'error: Error in milestone attachment');
                } else {
                    $this->session->set_flashdata('message', 'success: Successfully deleted milestone attachment');  
                }
            }
            redirect('scprojects/view/' . $project_id.'#milestones-tab');
        }
    }

    function delete_tasks_attachement($project_id=FALSE,$condition=FALSE,$task_id=FALSE,$task_attach_id=FALSE)
    {
        if(!empty($condition))
        {
            if($condition=='delete' && !empty($task_attach_id))
            {
                $get_attachment_name=ProjectHasTasksAttachment::find_by_id($task_attach_id);
                //echo "<pre>";print_r($get_attachment_name->milestone_attach_file);exit;
                $path=FCPATH.'files/tasks_attachment/'.$get_attachment_name->task_attach_file;
                unlink($path);
                $delete_attachment=$this->db->query("DELETE from  project_has_tasks_attachment where id=".$task_attach_id."");
                if (!$delete_attachment) {
                    $this->session->set_flashdata('message', 'error: Error in Delete Task attachment');
                } else {
                    $this->session->set_flashdata('message', 'success: Successfully deleted task attachment');  
                }
            } 
            redirect('scprojects/view/' . $project_id.'#tasks-tab');
        }
    }

    function sortlist($sort = FALSE, $list = FALSE){
        if($sort){
            $sort = explode("-", $sort);
            $sortnumber = 1;
            foreach($sort as $value){
                $task = ProjectHasTask::find_by_id($value);
                if($list != "task-list"){
                    $task->milestone_order = $sortnumber;
                }else{
                    $task->task_order = $sortnumber;
                }
                $task->save();
                $sortnumber = $sortnumber+1;
            }
        }
        $this->theme_view = 'blank';
    }
    function sort_milestone_list($sort = FALSE, $list = FALSE){
        if($sort){
            $sort = explode("-", $sort);
            $sortnumber = 1;
            foreach($sort as $value){
                $task = ProjectHasMilestone::find_by_id($value);
                $task->orderindex = $sortnumber;
                $task->save();
                $sortnumber = $sortnumber+1;
            }
        }
        $this->theme_view = 'blank';
    }
    function move_task_to_milestone($taskId = FALSE, $listId = FALSE)
    {
            if($listId && $taskId){
                $task = ProjectHasTask::find_by_id($taskId);
                $task->milestone_id = $listId;
                $task->save();
            }
        $this->theme_view = 'blank';
    }

    function task_list()
    {
        //echo $_GET['id'];exit;
        if($_GET['id'])
        {
            $id=$_GET['id'];
            $task_list = ProjectHasTask::find_by_sql("SELECT t.* FROM project_has_tasks t JOIN projects p ON t.project_id = p.id JOIN user_roles r ON t.user_id = r.user_id AND p.company_id = r.company_id WHERE t.project_id = '".$id."' AND r.company_id = '".$this->sessionArr['company_id']."' AND r.role_id ='".$this->sessionArr['role_id']."' and r.user_id='".$this->sessionArr['user_id']."'UNION
            SELECT t.* from project_has_tasks t join project_assign_tasks at on t.id=at.task_id and t.project_id=at.project_id
            join user_roles r on at.assign_user_id=r.user_id where r.company_id='".$this->sessionArr['company_id']."' and r.role_id='".$this->sessionArr['role_id']."' and r.user_id='".$this->sessionArr['user_id']."' AND t.project_id = '".$id."'
            ");
            $newArr = array();
            if (!empty($task_list)) {
                $i = 0;
                foreach ($task_list as $key => $value) {
                    $newArr[$i]['id'] = $value->id;
                    $newArr[$i]['user_id'] = $value->user_id;
                    $newArr[$i]['task_name'] = $value->name;
                    $newArr[$i]['status'] = $value->status;
                    $newArr[$i]['priority'] = $value->priority;
                    $newArr[$i]['public'] = $value->public;
                    $newArr[$i]['datetime'] = $value->datetime;
                    $newArr[$i]['due_date'] = $value->due_date;
                    $newArr[$i]['description'] = $value->description;
                    $newArr[$i]['value'] = $value->value;
                    $newArr[$i]['tracking'] = $value->tracking;
                    $newArr[$i]['time_spent'] = $value->time_spent;
                    $newArr[$i]['milestone_id'] = $value->milestone_id;
                    $newArr[$i]['invoice_id'] = $value->invoice_id;
                    $newArr[$i]['milestone_order'] = $value->milestone_order;
                    $newArr[$i]['progress'] = $value->progress;
                    $newArr[$i]['task_order'] = $value->task_order;
                    $newArr[$i]['created_at'] = $value->created_at;
                    //$newArr[$i]['task_attach_file'] = $value->task_attach_file;
                    $newArr[$i]['start_date'] = $value->start_date;

                    $tasks_attachment_by_task_id=$this->db->query('select * from project_has_tasks_attachment where task_id="'.$value->id.'"')->result_array();
                    
                    if (!empty($tasks_attachment_by_task_id)) {
                        $k = 0;
                        foreach ($tasks_attachment_by_task_id as $kk => $vv) 
                        {
                            $newArr[$i]['task_attchment'][$k]['attachment_id'] = $vv['id'];
                            $newArr[$i]['task_attchment'][$k]['project_id'] = $vv['project_id'];
                            $newArr[$i]['task_attchment'][$k]['task_id'] = $vv['task_id'];
                            $newArr[$i]['task_attchment'][$k]['company_id'] = $vv['company_id'];
                            $newArr[$i]['task_attchment'][$k]['user_id'] = $vv['user_id'];
                            $newArr[$i]['task_attchment'][$k]['role_id'] = $vv['role_id'];
                            $newArr[$i]['task_attchment'][$k]['task_attach_file'] = $vv['task_attach_file'];
                            $k++;
                        }
                           
                    }
                    $get_assign_clients = $this->db->query('select assign_user_id from project_assign_tasks where task_id="' . $value->id . '"')->result_array();
                    //var_dump(expression)
                
                    if (!empty($get_assign_clients)) {
                        $j = 0;
                        foreach ($get_assign_clients as $k => $v) {
                            $get_client_details = $this->db->query('select firstname,lastname,email,userpic from users where id="' . $v['assign_user_id'] . '"')->row_array();
                            if (!empty($get_client_details)) {
                                $newArr[$i]['clients'][$j]['firstname'] = $get_client_details['firstname'];
                                $newArr[$i]['clients'][$j]['lastname'] = $get_client_details['lastname'];
                                $newArr[$i]['clients'][$j]['email'] = $get_client_details['email'];
                                $newArr[$i]['clients'][$j]['userpic'] = $get_client_details['userpic'];
                                $j++;
                            }
                        }
                    }
                    $i++;
                } 
            }
            //echo "<pre>";print_r($newArr);exit;
            $new_html;
            foreach ($newArr as $key => $value) {
                $new_html.='<div id="task-details-'.$value['id'].'" class="todo-details"><i class="ion-close pull-right todo__close"></i>';
                $new_html.='<h4>'.$value['task_name'].'<h4>';
                $new_html.='<div class="grid grid--bleed task__options">';
                if($value['tracking'] != 0 && $value['tracking'] != ""){ $start = "hidden"; $stop = ""; }else{$start = ""; $stop = "hidden";}
                $new_html.= '<a href="'.base_url().'scprojects/task_start_stop_timer/'.$value['id'].'" data-timerid="timer'.$value['id'].'" class="grid__col-6 grid__col--bleed center ajax-silent task__options__button task__options__button--green task__options__timer timer'.$value['id'].''.$start.'">
                              '.$this->lang->line('application_start_timer').'</a>';
              $new_html.= '<a href="'.base_url().'scprojects/task_start_stop_timer/'.$value['id'].'" data-timerid="timer'.$value['id'].'" class="grid__col-6 grid__col--bleed center ajax-silent task__options__button task__options__button--red task__options__timer timer'.$value['id'].' hidden">
              '.$this->lang->line('application_stop_timer').'</a>';
              $new_html.= '<a href="'.base_url().'scprojects/tasks/'.$id.'/update/'.$value['id'].'" class="grid__col-6 grid__col--bleed task__options__button" data-toggle="mainmodal">
                              '.$this->lang->line('application_edit').'
                          </a>';
                $new_html.='</div>';
                $new_html.='<ul class="details">';
                $new_html.='<li>';
                $new_html.='<span>'.$this->lang->line('application_time_spent').'</span>';
                if($value['tracking'] != 0 && $value['tracking'] != "")
                { 
                  $timertime = (time() - $value['tracking']) + $value['time_spent'];
                  $state = "resume";
                }else{ 
                    $timertime = ($value['time_spent'] != 0 && $value['time_spent'] != "") ? $value['time_spent'] : 0; 
                    $state = "pause";
                }
                $new_html.='<span id="timer'.$value['id'].'" class="badge timer__badge '.$state.'"></span>';
                $new_html.='<script>$( document ).ready(function(){startTimer("'.$state.'", "'.$timertime.'", "#timer'.$value['id'].'"); });</script>';
                $new_html.='<a href="'.base_url().'scprojects/timesheets/'.$value['id'].'" class="timer__icon_button tt timespentp" data-original-title="'.$this->lang->line('application_timesheet').'" data-toggle="mainmodal"><i class="ion-android-list"></i></a>';
                $new_html.='</li>';
                $new_html.='<li>';
                $new_html.='<span>'.$this->lang->line('application_priority').'</span>';
                switch($value['priority'])
                {
                    case "0": 
                       $new_html.= $this->lang->line('application_no_priority'); 
                    break; 
                    case "1": 
                       $new_html.= $this->lang->line('application_low_priority'); 
                    break; 
                    case "2": 
                       $new_html.=$this->lang->line('application_med_priority'); 
                    break; 
                    case "3": 
                        $new_html.=$this->lang->line('application_high_priority'); 
                    break;
                }
                $new_html.='</li>';
                $new_html.='<li>';
                $new_html.='<span>'.$this->lang->line('application_progress').'</span>';
                $new_html.='<a href="#" data-name="progress" class="editable synced-process-edit" data-syncto="progress-bar'.$value['id'].'" data-type="range" data-pk="'.$value['id'].'" data-url="'.base_url().'scprojects/task_change_attribute"> 
                                  '.$value['progress'].'</a>';
                $new_html.='</li>';
                if($value['value'] != 0)
                {
                    $new_html.='<li>';
                    $new_html.='<span>'.$this->lang->line('application_value').'</span>';
                    $new_html.= $value['value'];
                    $new_html.='</li>';
                }
                if($value['start_date'] != "")
                {
                    $new_html.='<li>';
                    $new_html.='<span>'.$this->lang->line('application_start_date').'</span>';
                    $new_html.= date('Y/m/d',strtotime($value['start_date']));
                    $new_html.='</li>';
                }
                if($value['due_date'] != "")
                {
                    $new_html.='<li>';
                    $new_html.='<span>'.$this->lang->line('application_due_date').'</span>';
                    $new_html.= date('Y/m/d',strtotime($value['due_date']));
                    $new_html.='</li>';
                }
                $new_html.='<li>';
                $new_html.='<span>'.$this->lang->line('application_description').'</span>';
                $new_html.= $value['description'];
                $new_html.='</li>';
                $new_html.='</ul>';
                $new_html.='</div>';

            }
            echo $new_html;exit;
        }
    }
}

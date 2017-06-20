<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Projects extends MY_Controller {
               
	function __construct()
	{
		parent::__construct();
		$access = FALSE;
		if($this->client){	
			if($this->input->cookie('fc2_link') != ""){
					$link = $this->input->cookie('fc2_link');
					$link = str_replace("/tickets/", "/ctickets/", $link);
					redirect($link);
			}else{
				redirect('cprojects');
			}
			
		}elseif($this->user){
			$this->view_data['invoice_access'] = FALSE;
			foreach ($this->view_data['menu'] as $key => $value) { 
				if($value->link == "invoices"){ $this->view_data['invoice_access'] = TRUE;}
				if($value->link == "projects"){ $access = TRUE;}
			}
			if(!$access){redirect('login');}
		}else{
			redirect('login');
		}
		$this->view_data['submenu'] = array(
				 		$this->lang->line('application_all') => 'projects/filter/all',
				 		$this->lang->line('application_open') => 'projects/filter/open',
				 		$this->lang->line('application_closed') => 'projects/filter/closed'
				 		);	

		
	}
	function index()
	{
		$options = array('conditions' => 'progress < 100');
		if($this->user->admin == 0){ 
			$comp_array = array();
			$thisUserHasNoCompanies = (array) $this->user->companies;
					if(!empty($thisUserHasNoCompanies)){
				foreach ($this->user->companies as $value) {
					array_push($comp_array, $value->id);
				}
				$projects_by_client_admin = Project::find('all', array('conditions' => array('progress < ? AND company_id in (?)', '100', $comp_array)));

					//merge projects by client admin and assigned to projects
					$result = array_merge( $projects_by_client_admin, $this->user->projects );
					//duplicate objects will be removed
					$result = array_map("unserialize", array_unique(array_map("serialize", $result)));
					//array is sorted on the bases of id
					sort( $result );

					$this->view_data['project'] = $result;
			}else{
				$this->view_data['project'] = $this->user->projects;
			}
		}else{
			$this->view_data['project'] = Project::all($options);
		}
		$this->content_view = 'projects/all';
		$this->view_data['projects_assigned_to_me'] = ProjectHasWorker::find_by_sql('select count(distinct(projects.id)) AS "amount" FROM projects, project_has_workers WHERE projects.progress != "100" AND (projects.id = project_has_workers.project_id AND project_has_workers.user_id = "'.$this->user->id.'") ');
		$this->view_data['tasks_assigned_to_me'] = ProjectHasTask::count(array('conditions' => 'user_id = '.$this->user->id.' and status = "open"'));

		$now = time();
		$beginning_of_week = strtotime('last Monday', $now); // BEGINNING of the week
		$end_of_week = strtotime('next Sunday', $now) + 86400; // END of the last day of the week
		$this->view_data['projects_opened_this_week'] = Project::find_by_sql('select count(id) AS "amount", DATE_FORMAT(FROM_UNIXTIME(`datetime`), "%w") AS "date_day", DATE_FORMAT(FROM_UNIXTIME(`datetime`), "%Y-%m-%d") AS "date_formatted" from projects where datetime >= "'.$beginning_of_week.'" AND datetime <= "'.$end_of_week.'" ');

	}
	function filter($condition)
	{
		$options = array('conditions' => 'progress < 100');
		if($this->user->admin == 0){ 
			switch ($condition) {
				case 'open':
					$options = 'progress < 100';
					break;
				case 'closed':
					$options = 'progress = 100';
					break;
				case 'all':
					$options = '(progress = 100 OR progress < 100)';
					break;
			}

			$project_array = array();
				if($this->user->projects){
					foreach ($this->user->projects as $value) {
						array_push($project_array, $value->id);
					}
				}

			$thisUserHasNoCompanies = (array) $this->user->companies;
					if(!empty($thisUserHasNoCompanies)){
				$comp_array = array();
				foreach ($this->user->companies as $value) {
					array_push($comp_array, $value->id);
				}

			
			$projects_by_client_admin = Project::find('all', array('conditions' => array($options.' AND company_id in (?)', $comp_array)));

				//merge projects by client admin and assigned to projects
				$result = array_merge( $projects_by_client_admin, $this->user->projects );
				//duplicate objects will be removed
				$result = array_map("unserialize", array_unique(array_map("serialize", $result)));
				//array is sorted on the bases of id
				sort( $result );

				$this->view_data['project'] = $result;
			}else{
				$this->view_data['project'] = Project::find('all', array('conditions' => array($options.' AND id in (?)', $project_array)));
			}
		}else{
				switch ($condition) {
				case 'open':
					$options = array('conditions' => 'progress < 100');
					break;
				case 'closed':
					$options = array('conditions' => 'progress = 100');
					break;
				case 'all':
					$options = array('conditions' => 'progress = 100 OR progress < 100');
					break;
			}
			$this->view_data['project'] = Project::all($options);
		}


		$this->content_view = 'projects/all';

		$this->view_data['projects_assigned_to_me'] = ProjectHasWorker::find_by_sql('select count(distinct(projects.id)) AS "amount" FROM projects, project_has_workers WHERE projects.progress != "100" AND (projects.id = project_has_workers.project_id AND project_has_workers.user_id = "'.$this->user->id.'") ');
		$this->view_data['tasks_assigned_to_me'] = ProjectHasTask::count(array('conditions' => 'user_id = '.$this->user->id.' and status = "open"'));
		
		$now = time();
		$beginning_of_week = strtotime('last Monday', $now); // BEGINNING of the week
		$end_of_week = strtotime('next Sunday', $now) + 86400; // END of the last day of the week
		$this->view_data['projects_opened_this_week'] = Project::find_by_sql('select count(id) AS "amount", DATE_FORMAT(FROM_UNIXTIME(`datetime`), "%w") AS "date_day", DATE_FORMAT(FROM_UNIXTIME(`datetime`), "%Y-%m-%d") AS "date_formatted" from projects where datetime >= "'.$beginning_of_week.'" AND datetime <= "'.$end_of_week.'" ');

	}
	function create()
	{	
		if($_POST){
			unset($_POST['send']);
			$_POST['datetime'] = time();
			$_POST = array_map('htmlspecialchars', $_POST);
			unset($_POST['files']);

			$project = Project::create($_POST);
			$new_project_reference = $_POST['reference']+1;
			$project_reference = Setting::first();
			$project_reference->update_attributes(array('project_reference' => $new_project_reference));
       		if(!$project){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_create_project_error'));}
       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_create_project_success'));
				$attributes = array('project_id' => $project->id, 'user_id' => $this->user->id);
				ProjectHasWorker::create($attributes);
       			}
			redirect('projects');
		}
		else
		{
			if($this->user->admin == 0){
				$this->view_data['companies'] = $this->user->companies;
			}else{
				$this->view_data['companies'] = Company::find('all',array('conditions' => array('inactive=?','0')));
			}
			$this->view_data['next_reference'] = Project::last();
			$this->view_data['category_list'] = Project::get_categories();
			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_create_project');
			$this->view_data['form_action'] = 'projects/create';
			$this->content_view = 'projects/_project';
		}	
	}	
	function update($id = FALSE)
	{	
		if($_POST){
			unset($_POST['send']);
			$id = $_POST['id'];
			unset($_POST['files']);
			$_POST = array_map('htmlspecialchars', $_POST);
			if (!isset($_POST["progress_calc"])) {
				$_POST["progress_calc"] = 0;
			}
			if($this->user->admin == 1){
				if (!isset($_POST["hide_tasks"])) 
				{
					$_POST["hide_tasks"] = 0;
				}

			}
			if (!isset($_POST["enable_client_tasks"])) 
				{
					$_POST["enable_client_tasks"] = 0;
				}
			
			$project = Project::find($id);
			$project->update_attributes($_POST);
       		if(!$project){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_save_project_error'));}
       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_save_project_success'));}
			redirect('projects/view/'.$id);
		}else
		{
			if($this->user->admin == 0){
				$this->view_data['companies'] = $this->user->companies;
			}else{
			$this->view_data['companies'] = Company::find('all',array('conditions' => array('inactive=?','0')));
			}
			$this->view_data['project'] = Project::find($id);
			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_edit_project');
			$this->view_data['form_action'] = 'projects/update';
			$this->content_view = 'projects/_project';
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
	function task_change_attribute()
	{
		if($_POST){
				$name = $_POST["name"];
				$taskId = $_POST["pk"];
				$value = $_POST["value"];
				$task = ProjectHasTask::find_by_id($taskId);
				$task->{$name} = $value;
				$task->save();
		}
		$this->theme_view = 'blank';
	}
	function task_start_stop_timer($taskId)
	{
				$task = ProjectHasTask::find_by_id($taskId);
				if($task->tracking != 0){
					$now = time();
					$diff = $now - $task->tracking;
					$timer_start = $task->tracking;
					$task->time_spent = $task->time_spent+$diff;
					$task->tracking = "";
					//add time to timesheet
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

				}else{
					$task->tracking = time();
				}
				$task->save();
				$this->theme_view = 'blank';
	}
	function get_milestone_list($projectId)
	{
				$milestone_list = "";
				$project = Project::find_by_id($projectId);
				foreach ($project->project_has_milestones as $value) {
					$milestone_list .= '{value:'.$value->id.', text: "'.$value->name.'"},';
				}
				echo $milestone_list; 
		$this->theme_view = 'blank';
	}
	function copy($id = FALSE)
	{	
		if($_POST){
			unset($_POST['send']);
			$id = $_POST['id'];
			unset($_POST['id']);
			$_POST['datetime'] = time();
			$_POST = array_map('htmlspecialchars', $_POST);
			unset($_POST['files']);
			if(isset($_POST['tasks'])){
				unset($_POST['tasks']);
				$tasks = TRUE;
			}

			$project = Project::create($_POST);
			$new_project_reference = $_POST['reference']+1;
			$project_reference = Setting::first();
			$project_reference->update_attributes(array('project_reference' => $new_project_reference));

			if($tasks){
			unset($_POST['tasks']);
				$source_project	= Project::find_by_id($id);
				foreach ($source_project->project_has_tasks as $row) {
					$attributes = array(
						'project_id' => $project->id, 
						'name' => $row->name, 
						'user_id' => '',
						'status' => 'open', 
						'public' => $row->public, 
						'datetime' => $project->start,
						'due_date' => $project->end,
						'description' => $row->description,
						'value' => $row->value,
						'priority' => $row->priority,

						);
					ProjectHasTask::create($attributes);
				}
				
			}

       		if(!$project){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_create_project_error'));}
       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_create_project_success'));
				$attributes = array('project_id' => $project->id, 'user_id' => $this->user->id);
				ProjectHasWorker::create($attributes);
       			}
       		redirect('projects/view/'.$id);
		}else
		{
			$this->view_data['companies'] = Company::find('all',array('conditions' => array('inactive=?','0')));
			$this->view_data['project'] = Project::find($id);
			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_copy_project');
			$this->view_data['form_action'] = 'projects/copy';
			$this->content_view = 'projects/_copy';
		}	
	}	
	function assign($id = FALSE)
	{	
		$this->load->helper('notification');
		if($_POST){
			unset($_POST['send']);
			$id = addslashes($_POST['id']);
			$project = Project::find_by_id($id);
			if(!isset($_POST["user_id"])){
				$_POST["user_id"] = array();
			}
			$query = array();
			foreach ($project->project_has_workers as $key => $value) {
				array_push($query, $value->user_id);
			}

			$added = array_diff($_POST["user_id"], $query);
			$removed = array_diff($query, $_POST["user_id"]);

			foreach ($added as $value){
				$atributes = array('project_id' => $id, 'user_id' => $value);
				$worker = ProjectHasWorker::create($atributes);
				send_notification($worker->user->email, $this->lang->line('application_notification_project_assign_subject'), $this->lang->line('application_notification_project_assign').'<br><strong>'.$project->name.'</strong>');
			}

			foreach ($removed as $value){
				$atributes = array('project_id' => $id, 'user_id' => $value);
				$worker = ProjectHasWorker::find($atributes);
				$worker->delete();
			}

       		$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_save_project_success'));
			redirect('projects/view/'.$id);
		}else
		{
			$this->view_data['users'] = User::find('all',array('conditions' => array('status=?','active')));
			$this->view_data['project'] = Project::find($id);
			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_assign_to_agents');
			$this->view_data['form_action'] = 'projects/assign';
			$this->content_view = 'projects/_assign';
		}	
	}	
	function delete($id = FALSE)
	{	
		$project = Project::find($id);
		$project->delete();
		$sql = 'DELETE FROM project_has_tasks WHERE project_id = "'.$id.'"';
		$this->db->query($sql);
		$this->content_view = 'projects/all';
		if(!$project){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_delete_project_error'));}
       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_delete_project_success'));}
			if(isset($view)){redirect('projects/view/'.$id);}else{redirect('projects');}
	}
	function timer_reset($id = FALSE){
		$project = Project::find($id);
		$attr = array('time_spent' => '0');
		$project->update_attributes($attr);
		$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_timer_reset'));
		redirect('projects/view/'.$id);
	}
	function timer_set($id = FALSE){
		if($_POST){
		$project = Project::find_by_id($_POST['id']);
		$hours = $_POST['hours'];
		$minutes = $_POST['minutes'];
		$timespent = ($hours*60*60)+($minutes*60);
		$attr = array('time_spent' => $timespent);
		$project->update_attributes($attr);
		$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_timer_set'));
		redirect('projects/view/'.$_POST['id']);
		}else{
			$this->view_data['project'] = Project::find($id);
			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_timer_set');
			$this->view_data['form_action'] = 'projects/timer_set';
			$this->content_view = 'projects/_timer';
		}
	}
	function view($id = FALSE, $taskId = FALSE)
	{ 
		$this->view_data['submenu'] = array();
		$this->view_data['project'] = Project::find($id);
		$this->view_data['go_to_taskID'] = $taskId;
		$this->view_data['project_has_invoices'] = Invoice::all(array('conditions' => array('project_id = ? AND estimate != ?', $id, 1)));
		if(!isset($this->view_data['project_has_invoices'])){$this->view_data['project_has_invoices'] = array();}
		$tasks = ProjectHasTask::count(array('conditions' => 'project_id = '.$id));
		$this->view_data['alltasks'] = $tasks;
		$this->view_data['opentasks'] = ProjectHasTask::count(array('conditions' => array('status != ? AND project_id = ?', 'done', $id)));
		$this->view_data['usercountall'] = User::count(array('conditions' => array('status = ?', 'active')));
		$this->view_data['usersassigned'] = ProjectHasWorker::count(array('conditions' => array('project_id = ?', $id)));

		$this->view_data['assigneduserspercent'] = round($this->view_data['usersassigned']/$this->view_data['usercountall']*100);
		
		
		//Format statistic labels and values
			$this->view_data["labels"] = "";
			$this->view_data["line1"] = "";
			$this->view_data["line2"] = "";

			$daysOfWeek = getDatesOfWeek();
			$this->view_data['dueTasksStats'] = ProjectHasTask::getDueTaskStats($id, $daysOfWeek[0], $daysOfWeek[6]);
			$this->view_data['startTasksStats'] = ProjectHasTask::getStartTaskStats($id, $daysOfWeek[0], $daysOfWeek[6]);


		      foreach ($daysOfWeek as $day) {
		      	$counter = "0";
		      	$counter2 = "0";
		        foreach ($this->view_data['dueTasksStats'] as $value):
		          if($value->due_date == $day){  
		            $counter = $value->tasksdue; 
		          } 
		        endforeach; 
		        foreach ($this->view_data['startTasksStats'] as $value):
		          if($value->start_date == $day){  
		            $counter2 = $value->tasksdue; 
		          } 
		        endforeach; 
		         $this->view_data["labels"] .= '"'.$day.'"'; $this->view_data["labels"] .= ',';
		         $this->view_data["line1"] .= $counter.",";
		         $this->view_data["line2"] .= $counter2.",";

		        } 




		$this->view_data['time_days'] = round((human_to_unix($this->view_data['project']->end.' 00:00') - human_to_unix($this->view_data['project']->start.' 00:00')) / 3600 / 24);
		$this->view_data['time_left'] = $this->view_data['time_days'];
		$this->view_data['timeleftpercent'] = 100;

		if(human_to_unix($this->view_data['project']->start.' 00:00') < time() && human_to_unix($this->view_data['project']->end.' 00:00') > time()){
			$this->view_data['time_left'] = round((human_to_unix($this->view_data['project']->end.' 00:00') - time()) / 3600 / 24);
			$this->view_data['timeleftpercent'] = $this->view_data['time_left']/$this->view_data['time_days']*100;
		}
		if(human_to_unix($this->view_data['project']->end.' 00:00') < time()){
			$this->view_data['time_left'] = 0;
			$this->view_data['timeleftpercent'] = 0;
		}
		$this->view_data['allmytasks'] = ProjectHasTask::all(array('conditions' => array('project_id = ? AND user_id = ?', $id, $this->user->id)));
		$this->view_data['mytasks'] = ProjectHasTask::count(array('conditions' => array('status != ? AND project_id = ? AND user_id = ?', 'done', $id, $this->user->id)));
		$this->view_data['tasksWithoutMilestone'] = ProjectHasTask::find('all', array('conditions' => array('milestone_id = ? AND project_id = ? ', '0', $id)));
		
		$tasks_done = ProjectHasTask::count(array('conditions' => array('status = ? AND project_id = ?', 'done', $id)));
		$this->view_data['progress'] = $this->view_data['project']->progress;
		if($this->view_data['project']->progress_calc == 1){
			if ($tasks) {@$this->view_data['progress'] = round($tasks_done/$tasks*100);}
			$attr = array('progress' => $this->view_data['progress']);
			$this->view_data['project']->update_attributes($attr);
		}
		@$this->view_data['opentaskspercent'] = ($tasks == 0 ? 0 : $tasks_done/$tasks*100);
		$projecthasworker = ProjectHasWorker::all(array('conditions' => array('user_id = ? AND project_id = ?', $this->user->id, $id)));
		@$this->view_data['worker_is_client_admin'] = CompanyHasAdmin::all(array('conditions' => array('user_id = ? AND
		 company_id = ?', 
		 $this->user->id, 
		 $this->view_data['project']->company->id)));
		if(!$projecthasworker && $this->user->admin != 1 && !$this->view_data['worker_is_client_admin']){ 
				$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_no_access_error'));
				redirect('projects'); 
		}
		$tracking = $this->view_data['project']->time_spent;
		if(!empty($this->view_data['project']->tracking)){ $tracking=(time()-$this->view_data['project']->tracking)+$this->view_data['project']->time_spent; }
		$this->view_data['timertime'] = $tracking;
		$this->view_data['time_spent_from_today'] = time() - $this->view_data['project']->time_spent;	
		$tracking = floor($tracking/60);
		$tracking_hours = floor($tracking/60);
		$tracking_minutes = $tracking-($tracking_hours*60);

		

		$this->view_data['time_spent'] = $tracking_hours." ".$this->lang->line('application_hours')." ".$tracking_minutes." ".$this->lang->line('application_minutes');
		$this->view_data['time_spent_counter'] = sprintf("%02s", $tracking_hours).":".sprintf("%02s", $tracking_minutes);

		$this->content_view = 'projects/view';

	}
	function ganttChart($id){
		    $gantt_data = "["; 
		    $project = Project::find_by_id($id);      
            foreach ($project->project_has_milestones as $milestone):  
              
              $counter = 0;
             foreach ($milestone->project_has_tasks as $value): 
             $milestone_Name = ($counter == 0) ? $milestone->name : "";
             $counter++;
             $start = ($value->start_date) ? $value->start_date : $milestone->start_date;
             $end = ($value->due_date) ? $value->due_date : $milestone->due_date;

             $gantt_data .= '{ name: "'.$milestone_Name.'", desc: "'.$value->name.'", values: [';

              $gantt_data .= '{ label: "'.$value->name.'", from: "'.$start.'", to: "'.$end.'" }'; 
              $gantt_data .= ']},';
             endforeach;
             
      endforeach; 
      $gantt_data .= "]";
      $this->theme_view = 'blank';
     
		
		echo $gantt_data;


     
	}
	function quickTask(){
		if($_POST){
					unset($_POST['send']);
					unset($_POST['files']);
					$task = ProjectHasTask::create($_POST);
					echo $task->id;
				}

		$this->theme_view = 'blank';
	}
	function generate_thumbs($id = FALSE){
		if($id){
				$medias = Project::find_by_id($id)->project_has_files;
										//check image processor extension
										if (extension_loaded('gd2')) {
										    $lib = 'gd2';
										}else{
										    $lib = 'gd';
										}
							foreach ($medias as $value) {
								if(!file_exists('./files/media/thumb_'.$value->savename)){
										
										$config['image_library'] = $lib;
										$config['source_image']	= './files/media/'.$value->savename;
										$config['new_image']	= './files/media/thumb_'.$value->savename;
										$config['create_thumb'] = TRUE;
										$config['thumb_marker'] = "";
										$config['maintain_ratio'] = TRUE;
										$config['width']	= 170;
										$config['height']	= 170;
										$config['master_dim']	= "height";
										$config['quality']	= "100%";
										$this->load->library('image_lib', $config); 
										$this->image_lib->resize();
										$this->image_lib->clear();

										}
						}
				redirect('projects/view/'.$id);
				}
	}
	function dropzone($id = FALSE){
					
					$attr = array();
					$config['upload_path'] = './files/media/';
					$config['encrypt_name'] = TRUE;
					$config['allowed_types'] = '*';

					$this->load->library('upload', $config);


					if ( $this->upload->do_upload("file"))
						{
							$data = array('upload_data' => $this->upload->data());

							$attr['name'] = $data['upload_data']['orig_name'];
							$attr['filename'] = $data['upload_data']['orig_name'];
							$attr['savename'] = $data['upload_data']['file_name'];
							$attr['type'] = $data['upload_data']['file_type'];
							$attr['date'] = date("Y-m-d H:i", time());
							$attr['phase'] = "";

							$attr['project_id'] = $id;
							$attr['user_id'] = $this->user->id;
							$media = ProjectHasFile::create($attr);
							echo $media->id;

							//check image processor extension
							if (extension_loaded('gd2')) {
							    $lib = 'gd2';
							}else{
							    $lib = 'gd';
							}
							$config['image_library'] = $lib;
							$config['source_image']	= './files/media/'.$attr['savename'];
							$config['new_image']	= './files/media/thumb_'.$attr['savename'];
							$config['create_thumb'] = TRUE;
							$config['thumb_marker'] = "";
							$config['maintain_ratio'] = TRUE;
							$config['width']	= 170;
							$config['height']	= 170;
							$config['master_dim']	= "height";
							$config['quality']	= "100%";

							

							
							$this->load->library('image_lib', $config); 
							$this->image_lib->resize();
						}else{
							echo "Upload faild";
							$error = $this->upload->display_errors('', ' ');
							$this->session->set_flashdata('message', 'error:'.$error);
							echo $error;
						
						}

					
					
					

				$this->theme_view = 'blank';
	}
	function timesheets($taskid){
		
			$this->view_data['timesheets'] = ProjectHasTimesheet::find("all", array("conditions" => array("task_id = ?", $taskid)));
			$this->view_data['task'] = ProjectHasTask::find_by_id($taskid);

			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_timesheet');
			$this->view_data['form_action'] = 'projects/timesheet_add';
			$this->content_view = 'projects/_timesheets';
	}
	function timesheet_add(){
			if($_POST)
			{
				$time = ($_POST["hours"]*3600)+($_POST["minutes"]*60);
					$attr = array(
						"project_id" => $_POST["project_id"],
						"user_id" => $_POST["user_id"],
						"time" => $time,
						"client_id" => 0,
						"task_id" => $_POST["task_id"],
						"start" => $_POST["start"],
						"end" => $_POST["end"],
						"invoice_id" => 0,
						"description" => "",
					);
					$timesheet = ProjectHasTimesheet::create($attr);
						$task = ProjectHasTask::find_by_id($timesheet->task_id); 
						$task->time_spent =	$task->time_spent+$time;
						$task->save();
					echo $timesheet->id;
			}
			$this->theme_view = 'blank';
	}
	function timesheet_delete($timesheet_id){
		
			$timesheet = ProjectHasTimesheet::find_by_id($timesheet_id);
			$task = ProjectHasTask::find_by_id($timesheet->task_id);
			$task->time_spent = $task->time_spent-$timesheet->time;
			$task->save();
			$timesheet->delete();
			$this->theme_view = 'blank';
	}
	function tasks($id = FALSE, $condition = FALSE, $task_id = FALSE)
	{
		$this->view_data['submenu'] = array(
								$this->lang->line('application_back') => 'projects',
								$this->lang->line('application_overview') => 'projects/view/'.$id,
						 		);
		switch ($condition) {
			case 'add':
				$this->content_view = 'projects/_tasks';
				if($_POST){
					unset($_POST['send']);
					unset($_POST['files']);
					$description = $_POST['description'];
					$_POST = array_map('htmlspecialchars', $_POST);
					$_POST['description'] = $description;
					$_POST['project_id'] = $id;
					$task = ProjectHasTask::create($_POST);
		       		if(!$task){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_save_task_error'));}
		       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_save_task_success'));}
					redirect('projects/view/'.$id);
				}else
				{
					$this->theme_view = 'modal';
					$this->view_data['project'] = Project::find($id);
					$this->view_data['title'] = $this->lang->line('application_add_task');
					$this->view_data['form_action'] = 'projects/tasks/'.$id.'/add';
					$this->content_view = 'projects/_tasks';
				}	
				break;
			case 'update':
				$this->content_view = 'projects/_tasks';
				$this->view_data['task'] = ProjectHasTask::find($task_id);
				if($_POST){
					unset($_POST['send']);
					unset($_POST['files']);
					if(!isset($_POST['public'])){$_POST['public'] = 0;}
					$description = $_POST['description'];
					$_POST = array_map('htmlspecialchars', $_POST);
					$_POST['description'] = $description;
					$task_id = $_POST['id'];
					$task = ProjectHasTask::find($task_id);

						if($task->user_id != $_POST['user_id']){
								//stop timer and add time to timesheet
								if($task->tracking != 0){
									$now = time();
									$diff = $now - $task->tracking;
									$timer_start = $task->tracking;
									$task->time_spent = $task->time_spent+$diff;
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


					$task->update_attributes($_POST);
		       		if(!$task){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_save_task_error'));}
		       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_save_task_success'));}
					redirect('projects/view/'.$id);
				}else
				{
					$this->theme_view = 'modal';
					$this->view_data['project'] = Project::find($id);
					$this->view_data['title'] = $this->lang->line('application_edit_task');
					$this->view_data['form_action'] = 'projects/tasks/'.$id.'/update/'.$task_id;
					$this->content_view = 'projects/_tasks';
				}	
				break;
			case 'check':
					$task = ProjectHasTask::find($task_id);
					if ($task->status == 'done'){$task->status = 'open';}else{$task->status = 'done';}
					$task->save();
					$project = Project::find($id);
					$tasks = ProjectHasTask::count(array('conditions' => 'project_id = '.$id));
					$tasks_done = ProjectHasTask::count(array('conditions' => array('status = ? AND project_id = ?', 'done', $id)));
					if($project->progress_calc == 1){
						if ($tasks) {$progress = round($tasks_done/$tasks*100);}
						$attr = array('progress' => $progress);
						$project->update_attributes($attr);
					}
		       		if(!$task){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_save_task_error'));}
		       		$this->theme_view = 'ajax';
		       		$this->content_view = 'projects';
				break;
			case 'delete':
					$task = ProjectHasTask::find($task_id);
					$task->delete();
		       		if(!$task){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_delete_task_error'));}
		       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_delete_task_success'));}
					redirect('projects/view/'.$id);
				break;
			default:
				$this->view_data['project'] = Project::find($id);
				$this->content_view = 'projects/tasks';
				break;
		}

	}
	function milestones($id = FALSE, $condition = FALSE, $milestone_id = FALSE)
	{
		$this->view_data['submenu'] = array(
								$this->lang->line('application_back') => 'projects',
								$this->lang->line('application_overview') => 'projects/view/'.$id,
						 		);
		switch ($condition) {
			case 'add':
				$this->content_view = 'projects/_milestones';
				if($_POST){
					unset($_POST['send']);
					unset($_POST['files']);
					$description = $_POST['description'];
					$_POST = array_map('htmlspecialchars', $_POST);
					$_POST['description'] = $description;
					$_POST['project_id'] = $id;
					$milestone = ProjectHasMilestone::create($_POST);
		       		if(!$milestone){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_save_milestone_error'));}
		       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_save_milestone_success'));}
					redirect('projects/view/'.$id);
				}else
				{
					$this->theme_view = 'modal';
					$this->view_data['project'] = Project::find($id);
					$this->view_data['title'] = $this->lang->line('application_add_milestone');
					$this->view_data['form_action'] = 'projects/milestones/'.$id.'/add';
					$this->content_view = 'projects/_milestones';
				}	
				break;
			case 'update':
				$this->content_view = 'projects/_milestones';
				$this->view_data['milestone'] = ProjectHasMilestone::find($milestone_id);
				if($_POST){
					unset($_POST['send']);
					unset($_POST['files']);
					$description = $_POST['description'];
					$_POST = array_map('htmlspecialchars', $_POST);
					$_POST['description'] = $description;
					$milestone_id = $_POST['id'];
					$milestone = ProjectHasMilestone::find($milestone_id);
					$milestone->update_attributes($_POST);
		       		if(!$milestone){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_save_milestone_error'));}
		       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_save_milestone_success'));}
					redirect('projects/view/'.$id);
				}else
				{
					$this->theme_view = 'modal';
					$this->view_data['project'] = Project::find($id);
					$this->view_data['title'] = $this->lang->line('application_edit_milestone');
					$this->view_data['form_action'] = 'projects/milestones/'.$id.'/update/'.$milestone_id;
					$this->content_view = 'projects/_milestones';
				}	
				break;
			case 'delete':
					$milestone = ProjectHasMilestone::find($milestone_id);
					
					foreach ($milestone->project_has_tasks as $value) {
						$value->milestone_id = "";
						$value->save();
					}
					$milestone->delete();
		       		if(!$milestone){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_delete_milestone_error'));}
		       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_delete_milestone_success'));}
					redirect('projects/view/'.$id);
				break;
			default:
				$this->view_data['project'] = Project::find($id);
				$this->content_view = 'projects/milestones';
				break;
		}

	}
	function notes($id = FALSE)
	{	
		if($_POST){
			unset($_POST['send']);
			$_POST = array_map('htmlspecialchars', $_POST);
			$_POST['note'] = strip_tags($_POST['note']);
			$project = Project::find($id);
			$project->update_attributes($_POST);
		}
		$this->theme_view = 'ajax';
	}	
	function media($id = FALSE, $condition = FALSE, $media_id = FALSE)
	{
	    $this->load->helper('notification');
		$this->view_data['submenu'] = array(
								$this->lang->line('application_back') => 'projects',
								$this->lang->line('application_overview') => 'projects/view/'.$id,
						 		$this->lang->line('application_tasks') => 'projects/tasks/'.$id,
						 		$this->lang->line('application_media') => 'projects/media/'.$id,
						 		);
		switch ($condition) {
			case 'view':

				if($_POST){
					unset($_POST['send']);
					unset($_POST['_wysihtml5_mode']);
					unset($_POST['files']);
					//$_POST = array_map('htmlspecialchars', $_POST);
					$_POST['text'] = $_POST['message'];
					unset($_POST['message']);
					$_POST['project_id'] = $id;
					$_POST['media_id'] = $media_id;
					$_POST['from'] = $this->user->firstname.' '.$this->user->lastname;
					$this->view_data['project'] = Project::find_by_id($id);
					$this->view_data['media'] = ProjectHasFile::find($media_id);
					$message = Message::create($_POST);
       				if(!$message){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_save_message_error'));}
       				else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_save_message_success'));

       					foreach ($this->view_data['project']->project_has_workers as $workers){
            			    send_notification($workers->user->email, "[".$this->view_data['project']->name."] New comment", 'New comment on media file: '.$this->view_data['media']->name.'<br><strong>'.$this->view_data['project']->name.'</strong>');
            			}
            			if(isset($this->view_data['project']->company->client->email)){
            				$access = explode(',', $this->view_data['project']->company->client->access); 
            				if(in_array('12', $access)){
            					send_notification($this->view_data['project']->company->client->email, "[".$this->view_data['project']->name."] New comment", 'New comment on media file: '.$this->view_data['media']->name.'<br><strong>'.$this->view_data['project']->name.'</strong>');
            				}
            			}
       				}
       				redirect('projects/media/'.$id.'/view/'.$media_id);
				}
				$this->content_view = 'projects/view_media';
				$this->view_data['media'] = ProjectHasFile::find($media_id);
				$this->view_data['form_action'] = 'projects/media/'.$id.'/view/'.$media_id;
				$this->view_data['filetype'] = explode('.', $this->view_data['media']->filename);
				$this->view_data['filetype'] = $this->view_data['filetype'][1];
				$this->view_data['backlink'] = 'projects/view/'.$id;
				break;
			case 'add':
				$this->content_view = 'projects/_media';
				$this->view_data['project'] = Project::find($id);
				if($_POST){
					$config['upload_path'] = './files/media/';
					$config['encrypt_name'] = TRUE;
					$config['allowed_types'] = '*';

					$this->load->library('upload', $config);

					if ( ! $this->upload->do_upload())
						{
							$error = $this->upload->display_errors('', ' ');
							$this->session->set_flashdata('message', 'error:'.$error);
							redirect('projects/media/'.$id);
						}
						else
						{
							$data = array('upload_data' => $this->upload->data());

							$_POST['filename'] = $data['upload_data']['orig_name'];
							$_POST['savename'] = $data['upload_data']['file_name'];
							$_POST['type'] = $data['upload_data']['file_type'];

							//check image processor extension
							if (extension_loaded('gd2')) {
							    $lib = 'gd2';
							}else{
							    $lib = 'gd';
							}
							$config['image_library'] = $lib;
							$config['source_image']	= './files/media/'.$_POST['savename'];
							$config['new_image']	= './files/media/thumb_'.$_POST['savename'];
							$config['create_thumb'] = TRUE;
							$config['thumb_marker'] = "";
							$config['maintain_ratio'] = TRUE;
							$config['width']	= 170;
							$config['height']	= 170;
							$config['master_dim']	= "height";
							$config['quality']	= "100%";

							$this->load->library('image_lib', $config); 
							$this->image_lib->resize();
						}

					unset($_POST['send']);
					unset($_POST['userfile']);
					unset($_POST['file-name']);
					unset($_POST['files']);
					$_POST = array_map('htmlspecialchars', $_POST);
					$_POST['project_id'] = $id;
					$_POST['user_id'] = $this->user->id;
					$media = ProjectHasFile::create($_POST);
		       		if(!$media){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_save_media_error'));}
		       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_save_media_success'));
		       		
		       		    $attributes = array('subject' => $this->lang->line('application_new_media_subject'), 'message' => '<b>'.$this->user->firstname.' '.$this->user->lastname.'</b> '.$this->lang->line('application_uploaded'). ' '.$_POST['name'], 'datetime' => time(), 'project_id' => $id, 'type' => 'media', 'user_id' => $this->user->id);
					    $activity = ProjectHasActivity::create($attributes);
    		       		
    		       		foreach ($this->view_data['project']->project_has_workers as $workers){
            			    send_notification($workers->user->email, "[".$this->view_data['project']->name."] ".$this->lang->line('application_new_media_subject'), $this->lang->line('application_new_media_file_was_added').' <strong>'.$this->view_data['project']->name.'</strong>');
            			}
            			if(isset($this->view_data['project']->company->client->email)){
            				$access = explode(',', $this->view_data['project']->company->client->access); 
            				if(in_array('12', $access)){
            					send_notification($this->view_data['project']->company->client->email, "[".$this->view_data['project']->name."] ".$this->lang->line('application_new_media_subject'), $this->lang->line('application_new_media_file_was_added').' <strong>'.$this->view_data['project']->name.'</strong>');
            				}
            			}

		       		}
					redirect('projects/view/'.$id);
				}else
				{
					$this->theme_view = 'modal';
					$this->view_data['title'] = $this->lang->line('application_add_media');
					$this->view_data['form_action'] = 'projects/media/'.$id.'/add';
					$this->content_view = 'projects/_media';
				}	
				break;
			case 'update':
				$this->content_view = 'projects/_media';
				$this->view_data['media'] = ProjectHasFile::find($media_id);
				$this->view_data['project'] = Project::find($id);
				if($_POST){
					unset($_POST['send']);
					unset($_POST['_wysihtml5_mode']);
					unset($_POST['files']);
					$_POST = array_map('htmlspecialchars', $_POST);
					$media_id = $_POST['id'];
					$media = ProjectHasFile::find($media_id);
					$media->update_attributes($_POST);
		       		if(!$media){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_save_media_error'));}
		       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_save_media_success'));}
					redirect('projects/view/'.$id);
				}else
				{
					$this->theme_view = 'modal';
					$this->view_data['title'] = $this->lang->line('application_edit_media');
					$this->view_data['form_action'] = 'projects/media/'.$id.'/update/'.$media_id;
					$this->content_view = 'projects/_media';
				}	
				break;
			case 'delete':
					$media = ProjectHasFile::find($media_id);
					$media->delete();
					$this->load->database();
					$sql = "DELETE FROM messages WHERE media_id = $media_id";
					$this->db->query($sql);
		       		if(!$media){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_delete_media_error'));}
		       		else{	unlink('./files/media/'.$media->savename);
		       				$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_delete_media_success'));
		       			}
					redirect('projects/view/'.$id);
				break;
			default:
				$this->view_data['project'] = Project::find($id);
				$this->content_view = 'projects/view/'.$id;
				break;
		}

	}
	function deletemessage($project_id, $media_id, $id){
					$message = Message::find($id);
					if($message->from == $this->user->firstname." ".$this->user->lastname || $this->user->admin == "1"){
					$message->delete();
					}
		       		if(!$message){
		       			$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_delete_message_error'));
		       		}
		       		else{ 
		       			$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_delete_message_success'));
		       		}
					redirect('projects/media/'.$project_id.'/view/'.$media_id);
	}
	function tracking($id = FALSE)
	{
		$project = Project::find($id);
		if(empty($project->tracking)){
			$project->update_attributes(array('tracking' => time()));	
			
		}else{
		$timeDiff=time()-$project->tracking;
		$project->update_attributes(array('tracking' => '', 'time_spent' => $project->time_spent+$timeDiff));
		}
		redirect('projects/view/'.$id);

	}
	function sticky($id = FALSE)
	{
		$project = Project::find($id);
		if($project->sticky == 0){
			$project->update_attributes(array('sticky' => '1'));
       		$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_make_sticky_success'));	
			
		}else{
		$project->update_attributes(array('sticky' => '0'));
		$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_remove_sticky_success'));
		}
		redirect('projects/view/'.$id);

	}
	function download($media_id = FALSE){

        $this->load->helper('download');
        $this->load->helper('file');
		$media = ProjectHasFile::find($media_id);
		$media->download_counter = $media->download_counter+1;
		$media->save();

		$file = './files/media/'.$media->savename;
		$mime = get_mime_by_extension($file);
		if(file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: '.$mime);
            header('Content-Disposition: attachment; filename='.basename($media->filename));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            @ob_clean();
            @flush();
            exit; 
        }
	}
        
       
	function activity($id = FALSE, $condition = FALSE, $activityID = FALSE)
	{
	    $this->load->helper('notification');
		$project = Project::find_by_id($id);
		//$activity = ProjectHasAktivity::find_by_id($activityID);
		switch ($condition) {
			case 'add':
				if($_POST){
					unset($_POST['send']);
					$_POST['subject'] = htmlspecialchars($_POST['subject']);
					$_POST['message'] = strip_tags($_POST['message'], '<br><br/><p></p><a></a><b></b><i></i><u></u><span></span>');
					$_POST['project_id'] = $id;
					$_POST['user_id'] = $this->user->id;
					$_POST['type'] = "comment";
					unset($_POST['files']);
					$_POST['datetime'] = time();
					$activity = ProjectHasActivity::create($_POST);
		       		if(!$activity){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_save_error'));}
		       		else{
		       		    $this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_save_success'));
		       		    foreach ($project->project_has_workers as $workers){
            			    send_notification($workers->user->email, "[".$project->name."] ".$_POST['subject'], $_POST['message'].'<br><strong>'.$project->name.'</strong>');
            			}
            			if(isset($project->company->client->email)){
            				$access = explode(',', $project->company->client->access); 
            				if(in_array('12', $access)){
            					send_notification($project->company->client->email, "[".$project->name."] ".$_POST['subject'], $_POST['message'].'<br><strong>'.$project->name.'</strong>');
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

}
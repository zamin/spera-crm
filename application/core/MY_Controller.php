<?php

/**

 * ClassName: My_Controller

 * Function Name: __construct

 * This class is the core class of the site where we set user data, active users

 **/

class My_Controller extends CI_Controller {

    var $user = FALSE;

    var $client = FALSE;

    var $core_settings = FALSE;

    // Theme functionality

    protected $theme_view = 'application';

    protected $content_view = '';

    protected $view_data = array();



	/* Construct function is used to set core settings related to modules permission, language options, user details and user access

	 */

    function __construct() {

        parent::__construct();

        $this->load->database();

        /*if($this->uri->segment(3) && is_numeric($this->uri->segment(3)))

        {

            $this->cid = $this->uri->segment(3);

        }

        else if($this->uri->segment(2) && is_numeric($this->uri->segment(2)))

        {

            $this->cid = $this->uri->segment(2);

        }

        else*/ if($this->uri->segment(1) && is_numeric($this->uri->segment(1)))

        {

            $this->cid = $this->uri->segment(1);

        }

        else

        {

            $this->cid = 0;

        }

        /*if($_POST) {

			echo '<pre>';print_r($_POST);

			echo '<br> Session';

			print_r($this->session->userdata);



		}*/

		

        if (!empty($_POST)) {



            $fieldList = array("description", "message", "terms", "note", "invoice_terms", "estimate_terms", "bank_transfer_text");



            function remove_bad_tags_from($field) {

                $_POST[$field] = preg_replace('/(&lt;|<)\?php(.*)(\?(&gt;|>))/imx', '[php] $2 [php]', $_POST[$field]);

                $_POST[$field] = preg_replace('/((&lt;|<)(\s*|\/)script(.*?)(&gt;|>))/imx', ' [script] ', $_POST[$field]);

                $_POST[$field] = preg_replace('/((&lt;|<)(\s*)link(.*?)\/?(&gt;|>))/imx', '[link $4 ]', $_POST[$field]);

                $_POST[$field] = preg_replace('/((&lt;|<)(\s*)(\/*)(\s*)style(.*?)(&gt;|>))/imx', ' [style] ', $_POST[$field]);

            }



            foreach ($_POST as $key => $value) {

                if (in_array($key, $fieldList)) {

                    remove_bad_tags_from($key);

                } else {

                    $_POST[$key] = $this->security->xss_clean($_POST[$key]);

                }

            }

        }

        $this->view_data['core_settings'] = Setting::first();

        $this->view_data['datetime'] = date('Y-m-d H:i', time());

        $date = date('Y-m-d', time());



        //Languages

        if ($this->input->cookie('fc2language') != "") {

            $language = $this->input->cookie('fc2language');

        } else {

            if (isset($this->view_data['language'])) {

                $language = $this->view_data['language'];

            } else {

                if (!empty($this->view_data['core_settings']->language)) {

                    $language = $this->view_data['core_settings']->language;

                } else {

                    $language = "english";

                }

            }

        }

        $this->view_data['time24hours'] = "true";

        switch ($language) {



            case "english": $this->view_data['langshort'] = "en";

                $this->view_data['timeformat'] = "h:i K";

                $this->view_data['dateformat'] = "F j, Y";

                $this->view_data['time24hours'] = "false";

                break;

            case "dutch": $this->view_data['langshort'] = "nl";

                $this->view_data['timeformat'] = "H:i";

                $this->view_data['dateformat'] = "d-m-Y";

                break;

            case "french": $this->view_data['langshort'] = "fr";

                $this->view_data['timeformat'] = "H:i";

                $this->view_data['dateformat'] = "d-m-Y";

                break;

            case "german": $this->view_data['langshort'] = "de";

                $this->view_data['timeformat'] = "H:i";

                $this->view_data['dateformat'] = "d.m.Y";

                break;

            case "italian": $this->view_data['langshort'] = "it";

                $this->view_data['timeformat'] = "H:i";

                $this->view_data['dateformat'] = "d/m/Y";

                break;

            case "norwegian": $this->view_data['langshort'] = "no";

                $this->view_data['timeformat'] = "H:i";

                $this->view_data['dateformat'] = "d.m.Y";

                break;

            case "polish": $this->view_data['langshort'] = "pl";

                $this->view_data['timeformat'] = "H:i";

                $this->view_data['dateformat'] = "d.m.Y";

                break;

            case "portuguese": $this->view_data['langshort'] = "pt";

                $this->view_data['timeformat'] = "H:i";

                $this->view_data['dateformat'] = "d/m/Y";

                break;

            case "portuguese_pt": $this->view_data['langshort'] = "pt";

                $this->view_data['timeformat'] = "H:i";

                $this->view_data['dateformat'] = "d/m/Y";

                break;

            case "russian": $this->view_data['langshort'] = "ru";

                $this->view_data['timeformat'] = "H:i";

                $this->view_data['dateformat'] = "d.m.Y";

                break;

            case "spanish": $this->view_data['langshort'] = "es";

                $this->view_data['timeformat'] = "H:i";

                $this->view_data['dateformat'] = "d/m/Y";

                break;

            default: $this->view_data['langshort'] = "en";

                $this->view_data['timeformat'] = "h:i K";

                $this->view_data['dateformat'] = "F j, Y";

                $this->view_data['time24hours'] = "false";

                break;

        }



        //fetch installed languages

        $installed_languages = array();

        if ($handle = opendir('application/language/')) {

            while (false !== ($entry = readdir($handle))) {

                if ($entry != "." && $entry != "..") {

                    array_push($installed_languages, $entry);

                }

            }

            closedir($handle);

        }



        $this->lang->load('application', $language);

        $this->lang->load('messages', $language);

        $this->lang->load('event', $language);

        $this->view_data['current_language'] = $language;

        $this->view_data['installed_languages'] = $installed_languages;



        //userdata

        

        if($this->cid)

        {

            $this->sessionArr = $this->session->userdata[$this->cid];

            $this->view_data['settings']=CompanyDetails::find_by_company_id($this->cid);

            

            $cid = $this->sessionArr['company_id'];

            

            $role_id = $this->sessionArr['role_id'];



            $company_name=Company::find($cid);

            

            $cname = $company_name->name;

            

            $sessionArr = $this->session->userdata[$cid];

            

            $sessionArr['company_name'] = $cname;

            

            $this->session->set_userdata($cid,$sessionArr);

            

            $sessionArr = $this->session->userdata[$cid];

            

            $user_id     = $this->sessionArr['user_id'];

            

            if($role_id==2)

            {

                //echo "<pre>";print_r($this->sessionArr);exit;

                $get_package_with_access=$this->db->query("SELECT upd.*,p.access from propay_user_subscription upd left join package p on upd.package_id=p.id where upd.user_id='".$user_id."'")->row_array();

                //echo "<pre>";print_r($get_package_with_access);exit;

                if(!empty($get_package_with_access))

                {   

                    $this->theme_view = 'application_owner';

                    

                    $package_id =$get_package_with_access['package_id'];

                    

                    $package_user_access = $get_package_with_access['access'];

                    

                    $ao_access = $package_user_access;



                    if($package_id==1)

                    {

                        $ao_access = explode(",", $ao_access);

                    }

                    elseif($package_id==2)

                    {

                        $ao_access = explode(",", $ao_access);

                    }

                    else

                    {

                        $ao_access = explode(",", $ao_access);

                    }

                    

                    $this->menu_data = Module::find('all', array('order' => 'sort asc', 'conditions' => array('id in (?) AND type = ?', $ao_access, 'account-owner')));



                    

                    $this->module_permissions = array();

                    //echo "<pre>";print_r($this->menu_data);exit;

                    foreach ($this->menu_data as $key => $value) {

                        array_push($this->module_permissions, $value->link);

                    }
					array_push($this->module_permissions, 'agent');
                    array_push($this->module_permissions, 'logout');
                    array_push($this->module_permissions, 'quotation');
                    array_push($this->module_permissions, 'aosubscriptions');
					
					$segment_data = $this->uri->segment(2);
					//echo "<pre>";print_r($segment_data);exit;
                    if (!empty($segment_data)) {
                        if (!in_array($segment_data, $this->module_permissions)) {
                            $this->session->set_flashdata('message', 'error:' . $this->lang->line('messages_unauthorized_to_access'));
                            redirect('aodashboard');
                        }
                    }
                    $ao_user_online=$this->db->query('Select u.* from users u join user_roles r on u.id=r.user_id where r.company_id="'.$this->cid.'" and role_id=2 and u.last_active+(30 * 60) >'.time().' and u.status="active"')->result();
                    $this->view_data['ao_user_online'] = $ao_user_online;
         
                    $client_online=$this->db->query('Select u.* from users u join user_roles r on u.id=r.user_id where r.company_id="'.$this->cid.'" and (role_id=3 OR role_id=4) and u.last_active+(30 * 60) >'.time().'')->result();
                    $this->view_data['ao_client_online']=$client_online;
                }

            }

            else

            {

                $get_role_with_access=$this->db->query("SELECT u.* from users u left join user_roles r on u.id=r.user_id where u.id='".$user_id."'")->row_array();

                //echo "<pre>";print_r($get_role_with_access);exit;

                if(!empty($get_role_with_access))

                {

                    $role_user_access = $get_role_with_access['access'];

                    if($role_id==3)

                    {

                        $this->theme_view = 'application_client';

                        $client_access = $role_user_access;

                        $client_access = explode(",", $client_access);

                        $this->menu_data = Module::find('all', array('order' => 'sort asc', 'conditions' => array('id in (?) AND type = ?', $client_access, 'client')));

                    }

                    else

                    {

                        $this->theme_view = 'application_sub_contractor';

                        $sub_access = $role_user_access;

                        $sub_access = explode(",", $sub_access);

                        $this->menu_data = Module::find('all', array('order' => 'sort asc', 'conditions' => array('id in (?) AND type = ?', $sub_access, 'sub-contractor')));

                    }

                    //var_dump($this->menu_data);exit;

                    $this->module_permissions = array();

                    foreach ($this->menu_data as $key => $value) {

                        array_push($this->module_permissions, $value->link);

                    }
					
					array_push($this->module_permissions, 'agent');
                    array_push($this->module_permissions, 'logout');
                    $segment_data = $this->uri->segment(2);
                    //echo "<pre>";print_r($this->module_permissions);
                    //echo "<pre>";print_r($segment_data);exit;
                    if (!empty($segment_data)) {
                        if (!in_array($segment_data, $this->module_permissions)) {
                            if ($role_id == 3) {
                                $this->session->set_flashdata('message', 'error:' . $this->lang->line('messages_unauthorized_to_access'));
                                redirect('cdashboard');
                            } else {
                                $this->session->set_flashdata('message', 'error:' . $this->lang->line('messages_unauthorized_to_access'));
                                redirect('scdashboard');
                                exit;
                            }
                        }
                    }

                }

            }

            //echo "<pre>";print_r($user_role);exit;

        }

        else

        {

            $this->sessionArr = $this->session->userdata;

        }

        //echo "<pre>";print_r($this->sessionArr);exit;

        $this->user = isset($this->sessionArr['user_id']) ? User::find_by_id($this->sessionArr['user_id']) : FALSE;

        

        if ($this->user) {



            //check if user or client

            if ($this->user) {

                $access = $this->user->access;

                $access = explode(",", $access);

                

                $update = $this->user;

                $email = 'u' . $this->user->id;

                $userIsSuperAdmin = ($this->user->admin == '1') ? true : false;

                $comp_array=array();

                /*$user_role = UserRole::find_by_user_id($this->user->id);



                //menus and views by user roles

                else {*/

                    $this->view_data['menu'] = Module::find('all', array('order' => 'sort asc', 'conditions' => array('id in (?) AND type = ?', $access, 'main')));

                    //echo Module::connection()->last_query;

                    //echo "<pre>";print_r($this->view_data['menu']);exit;

                    $this->view_data['module_permissions'] = array();

                    $notification_list = array();

                    foreach ($this->view_data['menu'] as $key => $value) {

                        array_push($this->view_data['module_permissions'], $value->link);

                    }



                    $this->view_data['widgets'] = Module::find('all', array('conditions' => array('id in (?) AND type = ?', $access, 'widget')));

                    $this->view_data['user_online'] = User::all(array('conditions' => array('last_active+(30 * 60) > ? AND status = ?', time(), "active")));





                    $this->view_data['sticky'] = Project::find_by_sql("select distinct(projects.name), projects.id, projects.tracking, projects.progress from projects, project_has_workers where projects.sticky = 1 AND projects.id = project_has_workers.project_id AND project_has_workers.user_id=" . $this->user->id);



                    $this->view_data['tickets_access'] = false;

                    if (in_array("tickets", $this->view_data['module_permissions'])) {

                        $this->view_data['tickets_access'] = true;

                        $this->view_data['tickets_new'] = Ticket::newTicketCount($this->user->id, $comp_array);

                    }



                    if (in_array("invoices", $this->view_data['module_permissions'])) {

                        $overdueInvoices = Invoice::overdueByDate($comp_array, $date);

                        foreach ($overdueInvoices as $key => $value) {

                            $line = str_replace("{invoice_number}", '<a href="' . base_url() . 'invoices/view/' . $value->id . '">#' . $this->view_data['core_settings']->invoice_prefix . $value->reference . '</a>', $this->lang->line('event_invoice_overdue'));

                            $notification_list[$value->due_date . "." . $value->id] = $line;

                        }

                    }

                    if (in_array("subscriptions", $this->view_data['module_permissions'])) {

                        $outstandingInvoices = Subscription::newInvoiceOutstanding($comp_array, $date);

                        foreach ($outstandingInvoices as $key2 => $value2) {

                            $eventline = str_replace("{subscription_number}", '<a href="' . base_url() . 'subscriptions/view/' . $value2->id . '">#' . $this->view_data['core_settings']->subscription_prefix . $value2->reference . '</a>', $this->lang->line('event_subscription_new_invoice'));

                            $notification_list[$value2->next_payment . "." . $value2->id] = $eventline;

                        }

                    }



                    if (in_array("projects", $this->view_data['module_permissions'])) {

                        $overdueProjects = Project::overdueByDate($this->user->id, $comp_array, $date);

                        //task notification

                        $this->view_data['projects_icon'] = true;

                        $this->view_data['task_notifications'] = ProjectHasTask::find('all', array('conditions' => array('user_id = ? AND tracking != ?', $this->user->id, 0)));

                        foreach ($overdueProjects as $key2 => $value2) {

                            if ($this->user->admin == 0) {

                                $sql = "SELECT id FROM `project_has_workers` WHERE project_id = " . $value->id . " AND user_id = " . $this->user->id;

                                $res = Project::find_by_sql($sql);

                                //$res = $query;

                                if ($res) {

                                    $eventline = str_replace("{project_number}", '<a href="' . base_url() . 'projects/view/' . $value2->id . '">#' . $this->view_data['core_settings']->project_prefix . $value2->reference . '</a>', $this->lang->line('event_project_overdue'));

                                    $notification_list[$value2->end . "." . $value2->id] = $eventline;

                                }

                            } else {

                                $eventline = str_replace("{project_number}", '<a href="' . base_url() . 'projects/view/' . $value2->id . '">#' . $this->view_data['core_settings']->project_prefix . $value2->reference . '</a>', $this->lang->line('event_project_overdue'));

                                $notification_list[$value2->end . "." . $value2->id] = $eventline;

                            }

                        }

                    }



                    krsort($notification_list);

                    $this->view_data["notification_list"] = $notification_list;

                    $this->view_data["notification_count"] = count($notification_list);



            }



            //Update user last active

            $update->last_active = time();

            $update->save();



           /* $this->view_data['messages_new'] = Privatemessage::find_by_sql("select count(id) as amount from privatemessages where `status`='New' AND recipient = '" . $email . "'");*/

           

            $this->view_data['messages_new'] = Privatemessage::find_by_sql("select count(id) as amount from privatemessages where `status`='New' AND privatemessages.receiver_delete != 1 AND recipient = '" . $email . "'");



            //$this->view_data['quotations_new'] = Quote::count(array('conditions' => array('status = ?', "New")));

            //$this->view_data['custom_quotation_new'] = Quoterequest::count(array('conditions' => array('status = ?', "New")));

            
			$this->view_data['quotations_new'] = Quote::count(array('conditions' => array('status = "New" and user_id = "'.$this->sessionArr['user_id'].'"')));
            $this->view_data['custom_quotation_new'] = Quoterequest::count(array('conditions' => array('status = "New" and user_id = "'.$this->sessionArr['user_id'].'"')));
			
			if ($this->sessionArr['role_id'] == 2 && stripos($_SERVER["REQUEST_URI"], '/allsubscriptions') === false && stripos($_SERVER["REQUEST_URI"], '/create')===false && stripos($_SERVER["REQUEST_URI"], '/logout') === false && stripos($_SERVER["REQUEST_URI"], '/existing') === false) {//AO
			
            //if ($this->sessionArr['role_id'] == 2 && stripos($_SERVER["REQUEST_URI"], '/allsubscriptions') === false && stripos($_SERVER["REQUEST_URI"], '/logout') === false) {//AO
				
				$this->db->query("UPDATE propay_user_subscription SET status = 2 WHERE user_id = " . $this->sessionArr['user_id'] . " AND end_date < '" . date('Y-m-d', strtotime('now')) . "'");
				
                $user_current_subscriptions = $this->db->query('SELECT * FROM propay_user_subscription WHERE user_id = ' . $this->sessionArr['user_id'] . ' AND status = 0')->result_array();

                if (empty($user_current_subscriptions)) {
					
					$user_future_subscriptions = $this->db->query('SELECT * FROM propay_user_subscription WHERE user_id = ' . $this->sessionArr['user_id'] . ' AND status = 1 ORDER BY id limit 1')->result_array();
					if (empty($user_future_subscriptions)) {
						$this->session->set_flashdata('message', 'error:Your subscription is expired please subscribe your account!');
						redirect('aosettings/allsubscriptions/');
						exit;
					}
					elseif(!empty($user_future_subscriptions)){
						$this->db->query('UPDATE propay_user_subscription SET status = 0 WHERE id = ' . $user_future_subscriptions[0]['id']);
					}
                } 
            } 
			elseif (($this->sessionArr['role_id'] == 3 || $this->sessionArr['role_id'] == 4) && (stripos($_SERVER["REQUEST_URI"], '/logout') === false)) {//Client // SC

                $current_ao = $this->db->query('SELECT * FROM user_roles WHERE role_id=2 AND company_id = ' . $this->cid)->result_array();

                if (empty($current_ao)) {

                    $this->session->set_flashdata('message', 'error:Your account is expired!');

                    redirect('logout/');

                    exit;
                }


				$this->db->query("UPDATE propay_user_subscription SET status = 2 WHERE user_id = " . $current_ao[0]['user_id'] . " AND end_date < '" . date('Y-m-d', strtotime('now')) . "'");
				
                $user_current_subscriptions = $this->db->query('SELECT * FROM propay_user_subscription WHERE user_id = ' . $current_ao[0]['user_id'] . ' AND status = 0')->result_array();

                if (empty($user_current_subscriptions)) {
					
					$user_future_subscriptions = $this->db->query('SELECT * FROM propay_user_subscription WHERE user_id = ' . $current_ao[0]['user_id'] . ' AND status = 1 ORDER BY id limit 1')->result_array();
					if (empty($user_future_subscriptions)) {
						$this->session->set_flashdata('message', 'error:Your account is expired!');
						redirect('logout/');
						exit;
					}
					elseif(!empty($user_future_subscriptions)){
						$this->db->query('UPDATE propay_user_subscription SET status = 0 WHERE id = ' . $user_future_subscriptions[0]['id']);
					}
                }
				
            }

            

        }

        

        $this->view_data["note_templates"] = ""; //$query->result();

        

        if($this->cid)

        {

            $ticketsNoteCount = $this->db->query('SELECT count(*) as c FROM tickets t

                                                INNER JOIN ticket_assignment ta ON ta.ticket_id = t.id

                                                WHERE t.status="new" AND t.company_id = "' . $this->sessionArr['company_id'] . '" AND ta.user_id = "' . $this->sessionArr['user_id'] . '"')->result_array();

            $this->tickets_assigned_note = $ticketsNoteCount[0]['c'];

        }

    }

    public function _output($output) {

        // set the default content view

        if ($this->content_view !== FALSE && empty($this->content_view))

            $this->content_view = $this->router->class . '/' . $this->router->method;

        //render the content view

        $yield = file_exists(APPPATH . 'views/' . $this->view_data['core_settings']->template . '/' . $this->content_view . EXT) ? $this->load->view($this->view_data['core_settings']->template . '/' . $this->content_view, $this->view_data, TRUE) : FALSE;



        //render the theme

        if ($this->theme_view)

            echo $this->load->view($this->view_data['core_settings']->template . '/' . 'theme/' . $this->theme_view, array('yield' => $yield), TRUE);

        else

            echo $yield;



        echo $output;

    }
}

class MY_Api_Controller extends CI_Controller
{
    var $user = FALSE;
    var $core_settings = FALSE;

    function __construct()
    {
        parent::__construct();
        $this->load->database();

        //echo 'reach';die;

    }
    
    function checklogin($user_access_token=FALSE,$user_login_token=FALSE)
    {
        $newdata=array();
		$check_user_login = $this->db->query('SELECT r.* from user_roles r join user_api_details d on d.user_id=r.user_id where d.user_login_token="'.$user_login_token . '" and d.status="enable"')->row_array();
		if(!empty($check_user_login))
		{
			$company_id = $check_user_login['company_id'];
			$sql_check_company_details = 'SELECT r.*, d.status FROM user_roles r JOIN user_api_details d ON r.user_id = d.user_id WHERE d.user_access_token = "' . $user_access_token . '" and d.status="enable" and r.company_id="'.$company_id.'"';
			$check_company_id = $this->db->query($sql_check_company_details)->row_array();
			if(empty($check_company_id)) {
				$newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => "Account owner api status is disabled so can`t access api"));
				$this->response($newdata);
			}
			if($check_company_id['company_id']==$company_id)
			{
				$user_id=$check_user_login['user_id'];
				return $user_id;
			}
			$newdata = array('result' => 'fail', 'response' => array('code'=>400,'message' => 'User Login Unsuccessfully so please login again'));
			$this->response($newdata);  
		}
		$newdata = array('result' => 'fail', 'response' => array('code'=>400,'message' => 'Your API Status is disabled so can`t use any api'));
		$this->response($newdata);
    }
    
    function response($newdata=FALSE)
    {
        header('Content-Type: application/json');
        //echo '<pre>';
        //print_r(json_encode($newdata));
		
		$jsonData = json_encode($newdata, JSON_PRETTY_PRINT);
		//echo "<pre>" . $jsonData . "</pre>";
		echo  $jsonData;
        exit;
    }
}
<?php

/**
 * ClassName: Cdashboard
 * Function Name: index 
 * This class is used for client dashboard
 * */
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Cdashboard extends MY_Controller {

    function __construct() {
        parent::__construct();
        
        if (!$this->user) {
            $this->view_data['error'] = "true";
            $this->session->set_flashdata('message', 'error: You have no access to any modules!');
            if($this->cid){redirect($this->cid);}else{redirect('login');}
        }
        $this->load->database();
    }

    //client role activities
    function index($year=FALSE) {
        $cid = $this->sessionArr['company_id'];
        if(!$cid) {
            $this->view_data['error'] = "true";
            $this->session->set_flashdata('message', 'error: You have no access to any modules!');
            redirect('login');
        }
        if (!$year) {
            $year = date('Y', time());
        }
        $currentYearMonth = date('Y-m', time());
        $thismonth = date('m');
        $yearMonth = $year . '-' . $thismonth;
        
        $company_name=Company::find($cid);
        $cname = $company_name->name;
        
        $sessionArr = $this->session->userdata[$cid];
        $sessionArr['company_name'] = $cname;
        $this->session->set_userdata($cid,$sessionArr);
        
        $sessionArr = $this->session->userdata[$cid];
        
        $this->view_data['company_id']=$sessionArr['company_id'];
        $this->view_data['company_name']=$sessionArr['company_name'];
        $this->view_data['role_id']=$sessionArr['role_id'];
        $this->view_data['user_id']=$sessionArr['user_id'];
        $this->view_data['email']=$sessionArr['email'];
        
        $events = array();
        $date = date('Y-m-d', time());
        $eventcount = 0;
        if (in_array("cprojects", $this->module_permissions)) {
            //echo "fsf";exit;
            $sql = 'SELECT t.* FROM project_has_tasks t JOIN projects p ON t.project_id = p.id JOIN user_roles r ON t.user_id = r.user_id AND p.company_id = r.company_id WHERE t.status ="open" and r.company_id = "' . $this->sessionArr['company_id'] . '" AND r.role_id ="' . $this->sessionArr['role_id'] . '" and r.user_id="' . $this->sessionArr['user_id'] . '" 
UNION
SELECT t.* from project_has_tasks t join project_assign_tasks at on t.id=at.task_id and t.project_id=at.project_id
join user_roles r on at.assign_user_id=r.user_id where t.status ="open" and r.company_id="' . $this->sessionArr['company_id'] . '" and r.role_id="' . $this->sessionArr['role_id'] . '" and r.user_id="' . $this->sessionArr['user_id'] . '" 
';
            $taskquery = ProjectHasTask::find_by_sql($sql);
            //var_dump($taskquery);exit;
            $this->view_data["tasks"] = $taskquery;
        }
        

        // Projects Stats
         $open_projects = Project::find_by_sql("SELECT count(*) as opcount from projects p join project_assign_clients c on p.id=c.project_id and p.company_id=c.company_id left join user_roles r on c.company_id=r.company_id and c.assign_user_id=r.user_id where r.company_id='".$this->sessionArr['company_id']."' and r.role_id='".$this->sessionArr['role_id']."' and r.user_id='".$this->sessionArr['user_id']."' and p.progress < 100 order by p.id desc");
        if (!empty($open_projects[0]->opcount)) {
            $this->view_data["projects_open"] = $open_projects[0]->opcount;
        } else {
            $this->view_data["projects_open"] = 0;
        }

        $all_projects = Project::find_by_sql("SELECT count(*) as pcount from projects p join project_assign_clients c on p.id=c.project_id and p.company_id=c.company_id left join user_roles r on c.company_id=r.company_id and c.assign_user_id=r.user_id where r.company_id='".$this->sessionArr['company_id']."' and r.role_id='".$this->sessionArr['role_id']."' and r.user_id='".$this->sessionArr['user_id']."' order by p.id desc");
        //var_dump($all_projects);exit;
        if (!empty($all_projects[0]->pcount)) {
            $this->view_data["projects_all"] = $all_projects[0]->pcount;
        } else {
            $this->view_data["projects_all"] = 0;
        }

        // Invoices Stats	
        $this->view_data["invoices_open"] = Invoice::count(array('conditions' => array('status != ? AND status != ? AND estimate != ? AND user_id = ?', 'Paid', 'Canceled', 1, $this->user->id))); // Get all but canceled and Paid invoices
        $this->view_data["invoices_all"] = Invoice::count(array('conditions' => array('status != ? AND estimate != ? AND user_id = ?', 'Canceled', 1, $this->user->id))); // Get all but canceled invoices

        $this->view_data["invoices_open"] = Invoice::count(array('conditions' => array('status != ? AND status != ? AND estimate != ? AND user_id = ?', 'Paid', 'Canceled', 1, $this->user->id))); // Get all but canceled and Paid invoices
        $this->view_data["invoices_all"] = Invoice::count(array('conditions' => array('status != ? AND estimate != ? AND user_id = ?', 'Canceled', 1, $this->user->id))); // Get all but canceled invoices
        $this->sessionArr['company_id'];
        //echo $currentYearMonth;
        //echo $this->sessionArr['user_id'];
        $this->view_data["payments"] = Invoice::paymentsForMonthByUserId($this->sessionArr['company_id'],$this->sessionArr['user_id'], $currentYearMonth);
        $this->view_data["paymentsOutstandingMonth"] = Invoice::outstandingPaymentsByUserId($this->sessionArr['company_id'],$this->sessionArr['user_id'], $currentYearMonth);

       $this->view_data["paymentsoutstanding"] = Invoice::outstandingPaymentsByUserId($this->sessionArr['company_id'],$this->sessionArr['user_id'], $currentYearMonth);
       //exit;
       //var_dump($paymentsoutstanding);exit; 

       $this->view_data["totalExpenses"] = Invoice::totalExpensesForYearByUserId($this->sessionArr['user_id'],$year);

       $this->view_data["totalIncomeForYear"] = Invoice::totalIncomeForYearByUserId($this->sessionArr['company_id'],$this->sessionArr['user_id'],$year);
       
       $this->view_data["totalProfit"] = $this->view_data["totalIncomeForYear"] - $this->view_data["totalExpenses"];
       
       $this->view_data["paymentsForThisMonthInPercent"] = ($this->view_data["payments"] == 0) ? 0 : @round($this->view_data["payments"] / $this->view_data["paymentsOutstandingMonth"] * 100);
       
       $this->view_data["openProjectsPercent"] = ($this->view_data["projects_open"] == 0) ? 0 : @round($this->view_data["projects_open"] / $this->view_data["projects_all"] * 100);
       
       $this->view_data["openInvoicePercent"] = ($this->view_data["invoices_open"] == 0) ? 0 : @round($this->view_data["invoices_open"] / $this->view_data["invoices_all"] * 100);
       
       $this->view_data["paymentsOutstandingPercent"] = ($this->view_data["paymentsoutstanding"] == 0) ? 0 : @round($this->view_data["paymentsoutstanding"] / $this->view_data["totalIncomeForYear"] * 100);
       
       $this->view_data["paymentsOutstandingPercent"] = ($this->view_data["paymentsOutstandingPercent"] > 100) ? 100 : $this->view_data["paymentsOutstandingPercent"];
        //exit;        
        
        
       //get new tickets
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
                                        order by tt.id desc limit 5
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

        if(count($ticket_all) > 0)
        {
            $this->view_data['ticketcounter'] = count($ticket_all);
        }
        else
        {
            $this->view_data['ticketcounter'] = 0;
        }
        $this->view_data['recent_activities'] = ProjectHasActivity::find_by_sql('SELECT p.* from project_has_activities p left join user_roles r on p.user_id=r.user_id where r.company_id="'.$this->sessionArr['company_id'].'" order by p.datetime desc limit 10'); //TODO
        $this->content_view = 'dashboard/clientdashboard';
    }

}

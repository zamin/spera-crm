<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class reports extends MY_Controller
{
	function __construct()
	{

		parent::__construct();
		//$access = FALSE;	
		$this->view_data['update'] = FALSE;
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
        

		
		//Events
		$events = array();
		$date = date('Y-m-d', time());
		$eventcount = 0;
 					
		if(in_array("aoprojects", $this->view_data['module_permissions'])){
			$sql = 'SELECT * FROM project_has_tasks WHERE status != "done" AND user_id = "'.$this->user->id.'" ORDER BY project_id';
			$taskquery = ProjectHasTask::find('all', array('conditions' => array('status != ? and user_id = ?', 'done', $this->user->id), 'order' => 'project_id desc'));
			$this->view_data["tasks"] = $taskquery;
		} 

		
		
	}

	function period()
	{ 
		if($_POST){
			$report = $_POST['report'];
			$start = $_POST['start']; 
			$end = $_POST['end'];
		}
		
		if($report == "clients"){
			$this->income_by_clients($start, $end); 
			$this->view_data["report_selected"] = $report;
		}else{
		$this->index($start, $end);
		}
	}
	function index($start = FALSE, $end = FALSE)
	{
		$core_settings = Setting::first();
		$year = date('Y', time()); 
		if(!$start){
			$start = date('Y', time())."-01-01";
		}
		if(!$end){
			$end = date('Y', time())."-12-31";	
		} 

		$company_id = $this->sessionArr['company_id'];
		$this->view_data["stats_start_short"] = $start;
		$this->view_data["stats_end_short"] = $end;

		$this->view_data["stats_start"] = human_to_unix($start.' 00:00');
		$this->view_data["stats_start"] = date($core_settings->date_format, $this->view_data["stats_start"]);
		$this->view_data["stats_end"] = human_to_unix($end.' 00:00');
		$this->view_data["stats_end"] = date($core_settings->date_format, $this->view_data["stats_end"]);
		$currentYearMonth = date('Y-m', time());
		$thismonth = date('m');
		$yearMonth = $year.'-'.$thismonth;


			// View Values
			$this->view_data["month"] = date('M');
			$this->view_data["year"] = $year;
			$this->view_data["stats"] 						= Invoice::getStatisticFor($start, $end,$company_id);
			//echo "<pre>";print_r($this->view_data['stats']);exit;
			$this->view_data["stats_expenses"] 				= Invoice::getExpensesStatisticFor($start, $end);
			$this->view_data["totalExpenses"] 				= 0;
			$this->view_data["totalIncomeForYear"] 			= 0;
			


			//Format main statistic labels and values
			$line1 = '';
			$line2 = '';
		    $labels = '';

			$start_month    = new DateTime($start);
			$start_month->modify('first day of this month');
			$end_month      = new DateTime($end);
			$end_month->modify('first day of next month');
			$interval = DateInterval::createFromDateString('1 month');
			$period   = new DatePeriod($start_month, $interval, $end_month);

		    foreach ($period as $dt) {
				$monthname = $dt->format("M");
				$monthname = $this->lang->line('application_'.$monthname);
		        $num = "0";
		        $num2 = "0";
		        foreach ($this->view_data["stats"] as $value):
		          $act_month = explode("-", $value->paid_date); 
		          if($act_month[1] == $dt->format("m")){  
		            $num = sprintf("%02.2d", $value->summary); 
		          }
		        endforeach; 
		        foreach ($this->view_data["stats_expenses"] as $value):
		          $act_month = explode("-", $value->date_month); 
		          if($act_month[1] == $dt->format("m")){  
		            $num2 = sprintf("%02.2d", $value->summary); 
		          }
		        endforeach; 
		          $labels .= '"'.$monthname.'"';
		          $line1 .= $num;
		          $this->view_data["totalIncomeForYear"] = $this->view_data["totalIncomeForYear"]+$num;
		          $line2 .= $num2;
		          $this->view_data["totalExpenses"] = $this->view_data["totalExpenses"]+$num2;
		          $line1 .= ","; $line2 .= ","; $labels .= ",";
		        } 
		    $this->view_data["labels"] = rtrim($labels, ",");
		    $this->view_data["line1"] = rtrim($line1);
		    $this->view_data["line2"] = rtrim($line2);
		    $this->view_data["totalProfit"] 				= $this->view_data["totalIncomeForYear"]-$this->view_data["totalExpenses"];

		    $this->view_data['form_action'] = base_url().'reports/period';
			$this->content_view = 'reports/reports';
		
	}

	function income_by_clients($start = FALSE, $end = FALSE)
	{
		$company_id = $this->sessionArr['company_id'];
		$core_settings = Setting::first();
		$year = date('Y', time()); 
		if(!$start){
			$start = date('Y', time())."-01-01";
		}
		if(!$end){
			$end = date('Y', time())."-12-31";	
		} 
		$this->view_data["stats_start_short"] = $start;
		$this->view_data["stats_end_short"] = $end;

		$this->view_data["stats_start"] = human_to_unix($start.' 00:00');
		$this->view_data["stats_start"] = date($core_settings->date_format, $this->view_data["stats_start"]);
		$this->view_data["stats_end"] = human_to_unix($end.' 00:00');
		$this->view_data["stats_end"] = date($core_settings->date_format, $this->view_data["stats_end"]);
		$currentYearMonth = date('Y-m', time());
		$thismonth = date('m');
		$yearMonth = $year.'-'.$thismonth;


			// View Values
			$this->view_data["month"] = date('M');
			$this->view_data["year"] = $year;

			$this->view_data["stats"] 						= Invoice::getStatisticForClients($start, $end, $company_id);
			//echo "<pre>"; print_r($this->view_data["stats"]); die();
			$this->view_data["stats_expenses"] 				= Invoice::getExpensesStatisticFor($start, $end);
			$this->view_data["totalExpenses"] 				= 0;
			$this->view_data["totalIncomeForYear"] 			= 0;

			//Format main statistic labels and values
			$line1 = '';
			$line2 = '';
		    $labels = '';
		    $untilMonth = ($end) ? date_format(date_create_from_format('Y-m-d', $end), 'm') : 12;
		


		        $num = "0";
		        $num2 = "0";
		        foreach ($this->view_data["stats"] as $value):
		        	$company = Company::find_by_id($value->company_id);
		            $line1 .= sprintf("%02.2d", $value->summary).","; 
		       	 	$labels .= '"'.$company->name.'",';

		        endforeach; 

		        
		    $this->view_data["labels"] = $labels;
		    $this->view_data["line1"] = $line1;
		    $this->view_data["line2"] = $line2;
		    $this->view_data["totalProfit"] 				= $this->view_data["totalIncomeForYear"]-$this->view_data["totalExpenses"];

		    $this->view_data['form_action'] = base_url().'reports/period';
			$this->content_view = 'reports/reports';
		
	}

}



<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class cEstimates extends MY_Controller {
               
	function __construct()
	{
		parent::__construct();
		$access = FALSE;
		if (!$this->user) {
			$this->view_data['error'] = "true";
            $this->session->set_flashdata('message', 'error: You have no access to any modules!');
            redirect('login');
        } 
		$this->load->database();
		$this->company_id = ($this->sessionArr['company_id']) ? $this->sessionArr['company_id'] : 0;
		
		$this->view_data['submenu'] = array(
				 		$this->lang->line('application_all') => 'cestimates',
				 		);	
		
		$this->settings = Setting::first();
	}	
	function index()
	{
		$this->view_data['estimates'] = Invoice::find('all', array('conditions' => array('estimate != ? and company_id = ? and estimate_status != ?', 0, $this->company_id, 'Open')));

		$this->content_view = 'estimates/client_views/all';
	}
	function filter($condition = FALSE)
	{

		switch ($condition) {
			case 'open':
				$this->view_data['estimates'] = Invoice::find('all', array('conditions' => array('estimate_status = ? and estimate != ? and company_id = ?', 'Open', 0, $this->company_id)));
				break;
			case 'sent':
				$this->view_data['estimates'] = Invoice::find('all', array('conditions' => array('estimate_status = ? and estimate != ? and company_id = ?', 'Sent', 0, $this->company_id)));
				break;
			case 'accepted':
				$this->view_data['estimates'] = Invoice::find('all', array('conditions' => array('estimate_status = ? and estimate != ? and company_id = ?', 'Accepted', 0, $this->company_id)));
				break;
			case 'declined':
				$this->view_data['estimates'] = Invoice::find('all', array('conditions' => array('estimate_status = ? and estimate != ? and company_id = ?', 'Declined', 0, $this->company_id)));
				break;
			case 'invoiced':
				$this->view_data['estimates'] = Invoice::find('all', array('conditions' => array('estimate_status = ? and estimate != ? and company_id = ?', 'Invoiced', 0, $this->company_id)));
				break;
			default:
				$this->view_data['estimates'] = Invoice::find('all', array('conditions' => array('estimate != ? and company_id = ?', 0, $this->company_id)));
				break;
		}
		
		$this->content_view = 'estimates/client_views/all';
	}
	
		
	function accept($id = FALSE)
	{	
		$account_owner = $this->db->select('u.email, c.name as company_name ')->from('users as u')->join('user_roles as ur', 'ur.user_id = u.id')->join('companies as c', 'c.id = ur.company_id')->where('ur.role_id = 2 AND ur.company_id = '.$this->company_id)->get()->row_array();
	
		// $this->load->helper('notification');
		$data["core_settings"] = Setting::first();
			
		$this->view_data['estimate'] = Invoice::find_by_id($id);
		$this->view_data['estimate']->estimate_status = "Accepted";
		$this->view_data['estimate']->estimate_accepted_date = date("Y-m-d");

		$this->view_data['estimate']->save();
		
		$project = Project::find($this->view_data['estimate']->project_id); 
		$text = $this->lang->line('messages_estimate_accepted').'<br>Client name : '.trim($this->user->firstname . ' ' .$this->user->lastname).'<br>Project name : '.$project->name;
		
		$company_detail = CompanyDetails::find('all', array( 'conditions' => array('company_id=?',$this->company_id) ));
		$company_detail[0]->email = $this->sessionArr['email'];
			
		$account_owner["invoice_logo"] = $data["core_settings"]->invoice_logo; 
		if(!empty($company_detail)) {
			if(!empty($company_detail[0]->invoice_logo)) {
				$account_owner["invoice_logo"] = $company_detail[0]->invoice_logo; 
			} else if(!empty($company_detail[0]->logo)) {
				$account_owner["invoice_logo"] = $company_detail[0]->logo; 
			}
		}
		
		// send_notificationsend_notification($account_owner_email['email'], $data["core_settings"]->estimate_prefix.$this->view_data['estimate']->estimate_reference.' - '.$this->lang->line('application_Accepted'), $this->lang->line('messages_estimate_accepted'));
		$this->estimate_notification($account_owner, $data["core_settings"]->estimate_prefix.$this->view_data['estimate']->estimate_reference.' - '.$this->lang->line('application_Accepted'), $text);

		redirect('cestimates/view/'.$id);
			
	}	
	function decline($id = FALSE)
	{	
		// $this->load->helper('notification');
		$data["core_settings"] = Setting::first();
		if($_POST){

			$this->view_data['estimate'] = Invoice::find_by_id($_POST['invoice_id']);
			$this->view_data['estimate']->estimate_status = "Declined";
			//$this->view_data['estimate']->estimate_decline_message = $_POST['reason'];
			$this->view_data['estimate']->save();
			
			$project = Project::find($this->view_data['estimate']->project_id); 
			$text = $_POST['reason'].'<br>Client name : '.trim($this->user->firstname . ' ' .$this->user->lastname).'<br>Project name : '.$project->name;
			
			$account_owner = $this->db->select('u.email, c.name as company_name ')->from('users as u')->join('user_roles as ur', 'ur.user_id = u.id')->join('companies as c', 'c.id = ur.company_id')->where('ur.role_id = 2 AND ur.company_id = '.$this->company_id)->get()->row_array();
			
			$company_detail = CompanyDetails::find('all', array( 'conditions' => array('company_id=?',$this->company_id) ));
			$company_detail[0]->email = $this->sessionArr['email'];
				
			$account_owner["invoice_logo"] = $data["core_settings"]->invoice_logo; 
			if(!empty($company_detail)) {
				if(!empty($company_detail[0]->invoice_logo)) {
					$account_owner["invoice_logo"] = $company_detail[0]->invoice_logo; 
				} else if(!empty($company_detail[0]->logo)) {
					$account_owner["invoice_logo"] = $company_detail[0]->logo; 
				}
			}
			
			// send_notification($account_owner_email['email'], $data["core_settings"]->estimate_prefix.$this->view_data['estimate']->estimate_reference.' - '.$this->lang->line('application_Declined'), $_POST['reason']);
			$this->estimate_notification($account_owner, $data["core_settings"]->estimate_prefix.$this->view_data['estimate']->estimate_reference.' - '.$this->lang->line('application_Declined'), $text);

			redirect('cestimates/view/'.$_POST['invoice_id']);
		}else{
			$this->view_data['estimate'] = Invoice::find($id);

			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_Declined');
			$this->view_data['form_action'] = base_url().'cestimates/decline';
			$this->content_view = 'estimates/client_views/_decline';
		}
		
	}
	
	function view($id = FALSE)
	{

		$company_detail = CompanyDetails::find('all', array( 'conditions' => array('company_id=?',$this->company_id) ));
		$this->view_data['company_detail'] = $company_detail[0];
		
		$this->view_data['submenu'] = array(
						$this->lang->line('application_back') => 'cestimates',
				 		);	
		$this->view_data['estimate'] = Invoice::find($id);
		if($this->view_data['estimate']->company_id != $this->company_id){ redirect('cestimates');}
		$data["core_settings"] = Setting::first();
		$estimate = $this->view_data['estimate'];
		$this->view_data['items'] = InvoiceHasItem::find('all',array('conditions' => array('invoice_id=?',$id)));
		

		//calculate sum
		$i = 0; $sum = 0;
		foreach ($this->view_data['items'] as $value){
			$sum = $sum+$estimate->invoice_has_items[$i]->amount*$estimate->invoice_has_items[$i]->value; $i++;
		}
		if(substr($estimate->discount, -1) == "%"){ 
			$discount = sprintf("%01.2f", round(($sum/100)*substr($estimate->discount, 0, -1), 2)); 
		}
		else{
			$discount = $estimate->discount;
		}
		$sum = $sum-$discount;

		if($estimate->tax != ""){
			$tax_value = $estimate->tax;
		} else if(isset($company_detail)) {
			$tax_value = $company_detail[0]->tax;
		} else {
			$tax_value = $data["core_settings"]->tax;
		}

		$tax = sprintf("%01.2f", round(($sum/100)*$tax_value, 2));
		$sum = sprintf("%01.2f", round($sum+$tax, 2));

		$estimate->sum = $sum;
			$estimate->save();
		$this->content_view = 'estimates/client_views/view';
	}


	function preview($id = FALSE){
		$this->load->helper(array('dompdf', 'file')); 
		$this->load->library('parser');
		$data["estimate"] = Invoice::find($id); 
			
		$data['items'] = InvoiceHasItem::find('all',array('conditions' => array('invoice_id=?',$id)));
		$data["core_settings"] = Setting::first();
		
		$project = Project::find($data["estimate"]->project_id); 
		$data["project"] = ' : '.$project->name;
		
		$company_detail = CompanyDetails::find('all', array( 'conditions' => array('company_id=?',$this->company_id) ));
		$company_detail[0]->email = $this->sessionArr['email'];
		$data['company_detail'] = $company_detail[0];

		$data["invoice_logo"] = $data["core_settings"]->invoice_logo; 
		if(!empty($company_detail)) {
			if(!empty($company_detail[0]->invoice_logo)) {
				$data["invoice_logo"] = $company_detail[0]->invoice_logo; 
			} else if(!empty($company_detail[0]->logo)) {
				$data["invoice_logo"] = $company_detail[0]->logo; 
			}
		}
		
		$due_date = date($data["core_settings"]->date_format, human_to_unix($data["estimate"]->due_date.' 00:00:00'));  
		$parse_data = array(
								'due_date' => $due_date,
								'estimate_id' => $data["core_settings"]->estimate_prefix.$data["estimate"]->estimate_reference,
								'client_link' => $data["core_settings"]->domain,
								'company' => $data["estimate"]->company->name,
								);
		$html = $this->load->view($data["core_settings"]->template. '/' .$data["core_settings"]->estimate_pdf_template, $data, true); 
		$html = $this->parser->parse_string($html, $parse_data);

		$filename = $this->lang->line('application_estimate').'_'.$data["core_settings"]->estimate_prefix.$data["estimate"]->estimate_reference;
		pdf_create($html, $filename, TRUE);
	}

	
	function estimate_notification( $account_owner, $subject, $text, $attachment = FALSE ) {
		
		$this->load->helper(array('dompdf', 'file'));
		$this->load->library('parser');
		// $config['protocol']    = 'smtp';
		// $config['smtp_host']    = 'smtp.gmail.com';
		// $config['smtp_port']    = '465';
		// $config['smtp_timeout'] = '7';
		// $config['smtp_user']    = 'emailtesterone@gmail.com';
		// $config['smtp_pass']    = 'kgn@123456';
		// $config['charset']    = 'utf-8';
		// $config['newline']    = "\r\n";
		// $config['mailtype'] = 'html';
		// $config['validation'] = TRUE; // bool whether to validate email or not      

		// $this->email->initialize($config);
		
		$this->load->library('email');
		
		$data["core_settings"] = Setting::first();
		//$this->email->from($this->sessionArr['email'], $account_owner['company_name']);
		$this->email->from($this->settings->from_email_id,$this->settings->from_email_name);
		$this->email->to($account_owner['email']); 
		$this->email->subject($subject); 
		if($attachment){
		  if(is_array($attachment)){
			foreach ($attachment as $value) {
			  $this->email->attach('files/media/'.$value);
			}

		  }else{ 
			$this->email->attach('files/media/'.$attachment);
		  }
		}
		//Set parse values
		$parse_data = array(
							'company' => $account_owner['company_name'],
							'link' => site_url(),
							'logo' => '<img src="'.site_url().''.$account_owner['invoice_logo'].'" alt="'.$account_owner['company_name'].'"/>',
							'invoice_logo' => '<img src="'.site_url().''.$account_owner['invoice_logo'].'" alt="'.$account_owner['company_name'].'"/>',
							'message' => $text,
						    'client_contact' => '',
						    'client_company' => ''
							);
        $find_client = User::find_by_email($account_owner['email']);
        if(isset($find_client->firstname)){
			$parse_data["client_contact"] = $find_client->firstname." ".$find_client->lastname; 
			$parse_data["client_company"] = $account_owner['company_name'];
        }

		$email_message = read_file('./application/views/'.$data["core_settings"]->template.'/templates/email_notification.html');
		
		$message = $this->parser->parse_string($email_message, $parse_data);

		$this->email->message($message);
		return $send = $this->email->send();
		
	}

	
}
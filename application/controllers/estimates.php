<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Estimates extends MY_Controller {
               
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
        
		foreach ($this->menu_data as $key => $value) { 
			if($value->link == "estimates"){ $access = TRUE;}
		}
		if(!$access){redirect('login');}
		
		$this->view_data['submenu'] = array(
				 		$this->lang->line('application_all') => 'estimates',
				 		$this->lang->line('application_open') => 'estimates/filter/open',
				 		$this->lang->line('application_Sent') => 'estimates/filter/sent',
				 		$this->lang->line('application_Accepted') => 'estimates/filter/accepted',
				 		$this->lang->line('application_Invoiced') => 'estimates/filter/invoiced',

				 		);	
		$this->settings = Setting::first();
	}	
	function index()
	{
		
		if($this->user->admin == 0){ 
			$comp_array = array();
			$thisUserHasNoCompanies = array($this->company_id);
			// $thisUserHasNoCompanies = (array) $this->user->companies;
			if(!empty($thisUserHasNoCompanies)){
				// foreach ($this->user->companies as $value) {
					// array_push($comp_array, $value->id);
				// }
				$options = array('conditions' => array('estimate != ? AND company_id in (?)',0,$thisUserHasNoCompanies));
				$this->view_data['estimates'] = Invoice::find('all', $options);
			}else{
				$this->view_data['estimates'] = (object) array(); 
			}
		}else{
			$options = array('conditions' => array('estimate != ?',0));
			$this->view_data['estimates'] = Invoice::find('all', $options);
		}
		
		$days_in_this_month = days_in_month(date('m'), date('Y'));
		$lastday_in_month =  strtotime(date('Y')."-".date('m')."-".$days_in_this_month);
		$firstday_in_month =  strtotime(date('Y')."-".date('m')."-01");

		$this->view_data['estimates_paid_this_month'] = Invoice::count(array('conditions' => 'UNIX_TIMESTAMP(`paid_date`) <= '.$lastday_in_month.' and UNIX_TIMESTAMP(`paid_date`) >= '.$firstday_in_month.' AND estimate != 0 AND company_id = '. $this->company_id));
		$this->view_data['estimates_due_this_month'] = Invoice::count(array('conditions' => 'UNIX_TIMESTAMP(`due_date`) <= '.$lastday_in_month.' and UNIX_TIMESTAMP(`due_date`) >= '.$firstday_in_month.' AND estimate != 0 AND company_id = '. $this->company_id));
		
		//statistic
		$now = time();
		$beginning_of_week = strtotime('last Monday', $now); // BEGINNING of the week
		$end_of_week = strtotime('next Sunday', $now) + 86400; // END of the last day of the week
		$this->view_data['estimates_due_this_month_graph'] = Invoice::find_by_sql('select count(id) AS "amount", DATE_FORMAT(`due_date`, "%w") AS "date_day", DATE_FORMAT(`due_date`, "%Y-%m-%d") AS "date_formatted" from invoices where UNIX_TIMESTAMP(`due_date`) >= "'.$beginning_of_week.'" AND UNIX_TIMESTAMP(`due_date`) <= "'.$end_of_week.'" AND estimate != 0 AND company_id = '. $this->company_id);
		$this->view_data['estimates_paid_this_month_graph'] = Invoice::find_by_sql('select count(id) AS "amount", DATE_FORMAT(`paid_date`, "%w") AS "date_day", DATE_FORMAT(`paid_date`, "%Y-%m-%d") AS "date_formatted" from invoices where UNIX_TIMESTAMP(`paid_date`) >= "'.$beginning_of_week.'" AND UNIX_TIMESTAMP(`paid_date`) <= "'.$end_of_week.'" AND estimate != 0 AND company_id = '. $this->company_id);


		$this->content_view = 'estimates/all';
	}
	function filter($condition = FALSE)
	{
		$days_in_this_month = days_in_month(date('m'), date('Y'));
		$lastday_in_month =  date('Y')."-".date('m')."-".$days_in_this_month;
		$firstday_in_month =  date('Y')."-".date('m')."-01";
		$this->view_data['estimates_paid_this_month'] = Invoice::count(array('conditions' => 'paid_date <= '.$lastday_in_month.' and paid_date >= '.$firstday_in_month.' AND estimate != 0'));
		$this->view_data['estimates_due_this_month'] = Invoice::count(array('conditions' => 'due_date <= '.$lastday_in_month.' and due_date >= '.$firstday_in_month.' AND estimate != 0'));

		switch ($condition) {
			case 'open':
				$option = 'estimate_status = "Open" and estimate != 0';
				break;
			case 'sent':
				$option = 'estimate_status = "Sent" and estimate != 0';
				break;
			case 'accepted':
				$option = 'estimate_status = "Accepted" and estimate != 0';
				break;
			case 'declined':
				$option = 'estimate_status = "Declined" and estimate != 0';
				break;
			case 'invoiced':
				$option = 'estimate_status = "Invoiced" and estimate != 0';
				break;
			default:
				$option = 'estimate != 0';
				break;
		}

		if($this->user->admin == 0){ 
			$comp_array = array();
			$thisUserHasNoCompanies = array($this->company_id);
				if(!empty($thisUserHasNoCompanies)){
					// foreach ($this->user->companies as $value) {
						// array_push($comp_array, $value->id);
					// }
				$options = array('conditions' => array($option.' AND company_id in (?)',$thisUserHasNoCompanies));
				$this->view_data['estimates'] = Invoice::find('all', $options);
			}else{
				$this->view_data['estimates'] = (object) array();
			}
	
		}else{
			$options = array('conditions' => array($option));
			$this->view_data['estimates'] = Invoice::find('all', $options);
		}

		
		$this->content_view = 'estimates/all';
	}
	function create()
	{	
		if($_POST){
			$client_list = $_POST['client_list'];
			unset($_POST['send']);
			unset($_POST['_wysihtml5_mode']);
			unset($_POST['files']);
			unset($_POST['client_list']);
			
			$_POST['company_id'] = $this->company_id;
			$_POST['user_id'] = $this->user->id;
			$_POST['estimate'] = 1;
			$_POST['estimate_status'] = "Open";
			$estimate = Invoice::create($_POST);
			
			$new_estimate_reference = $_POST['estimate_reference']+1;
			$estimate_reference = Setting::first();
			$estimate_reference->update_attributes(array('estimate_reference' => $new_estimate_reference));
       		if(!$estimate){
				$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_create_estimate_error'));
			} else {
				$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_create_estimate_success'));
				if( !empty( $client_list ) ){				
					foreach( $client_list as $client ){
						$invoice_user = array(
							'invoice_id' => $estimate->id,
							'user_id' => $client,
						);
						InvoiceUsers::create($invoice_user);
					}
				}
			}
			redirect('estimates');
		}else
		{
			if($this->user->admin != 1 && $this->company_id != 0){
				// $comp_array = array();
				
				// $projects = $this->db->query( "SELECT p.* FROM projects as p JOIN companies AS c ON c.id = p.company_id JOIN users AS u ON ( u.id = c.user_id ) WHERE c.user_id = ". $this->user->id ." AND p.company_id = ". $this->company_id )->result();
				/* $projects = Project::find_by_sql('SELECT DISTINCT (p.id),p.* FROM projects p
                                                LEFT JOIN user_roles u ON p.company_id = u.company_id
                                                WHERE u.company_id = "' . $this->sessionArr['company_id'] . '"
                                                AND u.role_id = "' . $this->sessionArr['role_id'] . '" order by p.id desc '); */
				
				$this->view_data['estimates'] = Invoice::find('all', array('conditions' => array('company_id in (?)', array($this->company_id))));
				$this->view_data['projects'] = $this->get_projects();
				$this->view_data['company_id'] = $this->company_id;
				
			}else{
				$this->view_data['estimates'] = Invoice::all();
				$this->view_data['projects'] = Project::all();
				$this->view_data['company_id'] = Company::find('all',array('conditions' => array('inactive=?','0')));	
			}
			$this->view_data['company_detail'] = CompanyDetails::find('all', array( 'conditions' => array('company_id=?',$this->company_id) ));
			$this->view_data['next_reference'] = Invoice::last();
			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_create_estimate');
			$this->view_data['form_action'] = base_url().'estimates/create';
			$this->content_view = 'estimates/_estimate';
		}	
	}	
	function update($id = FALSE, $getview = FALSE)
	{	
		if($_POST){
			$client_list = $_POST['client_list'];
			unset($_POST['send']);
			unset($_POST['_wysihtml5_mode']);
			unset($_POST['files']);
			unset($_POST['client_list']);
			
			$id = $_POST['id'];
			$view = FALSE;
			if(isset($_POST['view'])){$view = $_POST['view']; }
			unset($_POST['view']);
			if($_POST['status'] == "Paid"){ $_POST['paid_date'] = date('Y-m-d', time());}
			$estimate = Invoice::find($id);
			$estimate->update_attributes($_POST);
			
       		if(!$estimate){
				$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_save_estimate_error'));
			} else {
				$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_save_estimate_success'));
				if( !empty( $client_list ) ){	
				
					$this->delete_invoice_users( $id );	
					
					foreach( $client_list as $client ){
						$invoice_user = array(
							'invoice_id' => $id,
							'user_id' => $client,
						);
						InvoiceUsers::create($invoice_user);
					}
				} else {
					$this->delete_invoice_users( $id );	
				}
			}
			redirect('estimates/view/'.$id);
			
		}else
		{
			
			if($this->user->admin != 1){
				$this->view_data['projects'] = $this->get_projects();
				$this->view_data['company_id'] = $this->company_id;
			}
			$estimate = Invoice::find($id);
			$this->view_data['project'] = $estimate->project_id;
			$this->view_data['clients'] = $this->get_clients($estimate->project_id);
			$this->view_data['company_detail'] = CompanyDetails::find('all', array( 'conditions' => array('company_id=?',$this->company_id) ));
			
			$this->view_data['selected_invoice_user'] = $this->get_selected_clients($id);
			
			$this->view_data['estimate'] = Invoice::find($id);
			if($getview == "view"){$this->view_data['view'] = "true";}
			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_edit_estimate');
			$this->view_data['form_action'] = base_url().'estimates/update';
			$this->content_view = 'estimates/_estimate';
		}	
	}	
	
	function view($id = FALSE)
	{

		$company_detail = CompanyDetails::find('all', array( 'conditions' => array('company_id=?',$this->company_id) ));
		$this->view_data['company_detail'] = $company_detail[0];
		
		$this->view_data['submenu'] = array(
						$this->lang->line('application_back') => 'estimates',
				 		);	
		$this->view_data['estimate'] = Invoice::find($id);

		if($this->user->admin != 1){
				$comp_array = array($this->company_id);
			// foreach ($this->user->companies as $value) {
				// array_push($comp_array, $value->id);
			// }
			if(!in_array($this->view_data['estimate']->company_id, $comp_array)){redirect('estimates');}
		}

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
		
		$this->view_data['selected_invoice_user'] = $this->get_selected_clients($id);
		$this->content_view = 'estimates/view';
	}

	function estimateToInvoice($id = FALSE, $getview = FALSE)
	{	
			$settings = Setting::first();
			$estimate = Invoice::find($id);
			$estimate->estimate = 2;
			$estimate->estimate_status = "Invoiced";
			$estimate->reference = $settings->invoice_reference;
			$estimate->save();
			$settings->invoice_reference = $settings->invoice_reference+1;
			$settings->save();
			
       		if(!$estimate){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_save_invoice_error'));}
       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_save_invoice_success'));}
			redirect('invoices/view/'.$id);
			
		
	}
	

	function delete($id = FALSE)
	{	
		$estimate = Invoice::find($id);
		$estimate->delete();
		$this->content_view = 'estimates/all';
		if(!$estimate){
			$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_delete_estimate_error'));
		} else {
			$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_delete_estimate_success'));
			$this->delete_invoice_users( $id );
		}
		redirect('estimates');
	}
	
	function preview($id = FALSE, $attachment = FALSE){
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
		pdf_create($html, $filename, TRUE, $attachment);
	}
	
	function sendestimate($id = FALSE){
			$data["core_settings"] = Setting::first();
			$this->load->helper(array('dompdf', 'file'));
			$this->load->library('parser');
			$data["estimate"] = Invoice::find($id); 
			
			$project = Project::find($data["estimate"]->project_id); 
			$data["project"] = ' : '.$project->name; 
			
			$company_detail = CompanyDetails::find('all', array( 'conditions' => array('company_id=?',$this->company_id) ));
			$data['company_detail'] = $company_detail[0];
			
			$data["invoice_logo"] = $data["core_settings"]->invoice_logo; 
			if(!empty($company_detail)) {
				if(!empty($company_detail[0]->invoice_logo)) {
					$data["invoice_logo"] = $company_detail[0]->invoice_logo; 
				} else if(!empty($company_detail[0]->logo)) {
					$data["invoice_logo"] = $company_detail[0]->logo; 
				}
			}
			//check if client contact has permissions for estimates and grant if not
			// if(isset($data["estimate"]->company->client->id)){
				// $access = explode(",", $data["estimate"]->company->client->access);
				// if(!in_array("107", $access)){
					// $client_estimate_permission = Client::find_by_id($data["estimate"]->company->client->id);
					// if($client_estimate_permission){
						// $client_estimate_permission->access = $client_estimate_permission->access.",107";
						// $client_estimate_permission->save();
					// }
				// }
				
			// }
			$data["estimate"]->estimate_sent = date("Y-m-d");
			$data["estimate"]->estimate_status = "Sent";


			
			$data['items'] = InvoiceHasItem::find('all',array('conditions' => array('invoice_id=?',$id)));
     		$data["core_settings"] = Setting::first();
    		$due_date = date($data["core_settings"]->date_format, human_to_unix($data["estimate"]->due_date.' 00:00:00')); 
			
			$invoice_users = InvoiceUsers::find( 'all', array('conditions' => array('invoice_id=?',$id)) );
			if($invoice_users) {
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
				foreach( $invoice_users as $invoice_user ){
					$user_data = User::find( array( 'conditions' => array('id=?', $invoice_user->user_id) ) );
					if($user_data) {
						
						$this->email->clear(TRUE);
						//Set parse values
						$parse_data = array(
											'project' => 'for '.$project->name,
											'client_contact' => $user_data->firstname.' '.$user_data->lastname,
											'client_company' => $data["estimate"]->company->name,
											'due_date' => $due_date,
											'estimate_id' => $data["core_settings"]->estimate_prefix.$data["estimate"]->estimate_reference,
											'client_link' => base_url().'estimates/view/'.$id,
											'company' => $data["estimate"]->company->name,
											'logo' => '<img src="'.site_url().$data["core_settings"]->logo.'" alt="'.$data["estimate"]->company->name.'"/>',
											'invoice_logo' => '<img src="'.site_url().''.$data["invoice_logo"].'" alt="'.$data["estimate"]->company->name.'"/>'
											);
						// Generate PDF     
						$html = $this->load->view($data["core_settings"]->template. '/' .$data["core_settings"]->estimate_pdf_template, $data, true);
						$html = $this->parser->parse_string($html, $parse_data);
						
						$filename = $this->lang->line('application_estimate').'_'.$data["core_settings"]->estimate_prefix.$data["estimate"]->estimate_reference;
						pdf_create($html, $filename, FALSE);
						//email
						$subject = $this->parser->parse_string($data["core_settings"]->estimate_mail_subject, $parse_data);
						//$this->email->from($this->sessionArr['email'], $data["estimate"]->company->name);
						$this->email->from($this->settings->from_email_id,$this->settings->from_email_name);
						// if(!isset($data["estimate"]->company->client->email)){
							// $this->session->set_flashdata('message', 'error:This client company has no primary contact! Just add a primary contact.');
							// redirect('estimates/view/'.$id);
						// }
						$this->email->to($user_data->email); 
						$this->email->subject($subject); 
						$this->email->attach("files/temp/".$filename.".pdf");
						


						$email_estimate = read_file('./application/views/'.$data["core_settings"]->template.'/templates/email_estimate.html');
						$message = $this->parser->parse_string($email_estimate, $parse_data);
						
						$this->email->message($message);
						
						if($this->email->send()){
							$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_send_estimate_success'));
							$data["estimate"]->update_attributes(array('status' => 'Sent', 'sent_date' => date("Y-m-d")));
							log_message('error', 'Estimate #'.$data["core_settings"]->estimate_prefix.$data["estimate"]->estimate_reference.' has been send to '.$user_data->email);
						}
						else{
							$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_send_estimate_error'));
							log_message('error', 'ERROR: Estimate #'.$data["core_settings"]->estimate_prefix.$data["estimate"]->estimate_reference.' has not been send to '.$user_data->email.'. Please check your servers email settings.');
						}
						unlink("files/temp/".$filename.".pdf");
					}
				}
			}
			redirect('estimates/view/'.$id);
	}
	
	function item($id = FALSE)
	{	
		if($_POST){
			unset($_POST['send']);
			$_POST = array_map('htmlspecialchars', $_POST);
			if($_POST['name'] != ""){
				$_POST['name'] = $_POST['name'];
				$_POST['value'] = $_POST['value'];
				$_POST['type'] = $_POST['type'];
			}else{
				if($_POST['item_id'] == "-" || empty($_POST['item_id']) ){
					$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_add_item_error'));
					redirect('estimates/view/'.$_POST['invoice_id']);

				}else{
					// $itemvalue = Item::find_by_id($_POST['item_id']);
					$itemvalue = $this->db->select("items.*")->from("items")->join("item_company", "item_company.item_id = items.id")->where("item_company.company_id = ".$this->company_id." AND items.inactive = 0 AND items.id = ".$_POST['item_id'])->get()->row_object();
					$_POST['name'] = $itemvalue->name;
					$_POST['type'] = $itemvalue->type;
					$_POST['value'] = $itemvalue->value;
				}
			}

			$_new_item = $_POST;
			$item = InvoiceHasItem::create($_POST);
       		if(!$item){
				$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_add_item_error'));
			} else {
				$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_add_item_success'));
				
				if($_POST['name'] != ""){
					unset($_new_item['invoice_id']);
					unset($_new_item['item_id']);
					unset($_new_item['amount']);
					$create_item = Item::create($_new_item);
					if($create_item){
						$this->db->insert('item_company', array('item_id' => $create_item->id, 'company_id' => $this->company_id));	
					}
				}
				
			}
			redirect('estimates/view/'.$_POST['invoice_id']);
			
		}else
		{
			$this->view_data['estimate'] = Invoice::find($id);
			// $this->view_data['items'] = Item::find('all',array('conditions' => array('inactive=?','0')));
			$this->view_data['items'] = $this->db->select("items.*")->from("items")->join("item_company", "item_company.item_id = items.id")->where("item_company.company_id = ".$this->company_id." AND items.inactive = 0")->get()->result_object();
			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_add_item');
			$this->view_data['form_action'] = base_url().'estimates/item';
			$this->content_view = 'estimates/_item';
		}	
	}	
	function item_update($id = FALSE)
	{	
		if($_POST){
			unset($_POST['send']);
			$_POST = array_map('htmlspecialchars', $_POST);
			$item = InvoiceHasItem::find($_POST['id']);
			$item = $item->update_attributes($_POST);
       		if(!$item){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_save_item_error'));}
       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_save_item_success'));}
			redirect('estimates/view/'.$_POST['invoice_id']);
			
		}else
		{
			$this->view_data['estimate_has_items'] = InvoiceHasItem::find($id);
			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_edit_item');
			$this->view_data['form_action'] = base_url().'estimates/item_update';
			$this->content_view = 'estimates/_item';
		}	
	}	
	function item_delete($id = FALSE, $estimate_id = FALSE)
	{	
		$item = InvoiceHasItem::find($id);
		$item->delete();
		$this->content_view = 'estimates/view';
		if(!$item){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_delete_item_error'));}
       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_delete_item_success'));}
			redirect('estimates/view/'.$estimate_id);
	}	

	function get_projects() {
		return $this->db->query( "SELECT p.* FROM projects as p JOIN companies AS c ON c.id = p.company_id JOIN users AS u ON ( u.id = c.user_id ) WHERE c.user_id = ". $this->user->id ." AND p.company_id = ". $this->company_id )->result();
	}
	
	function get_selected_clients($invoice_id) {
		$invoice_user_query = $this->db->select('iu.user_id, u.firstname, u.lastname')->from('invoice_users as iu')->join('users as u', 'u.id =iu.user_id')->where("iu.invoice_id = ".$invoice_id)->get();
			
		$selected_invoice_user = array();
		if($invoice_user_query->num_rows > 0) {
			$invoice_users = $invoice_user_query->result_object();
			foreach( $invoice_users as $invoice_user ){
				$selected_invoice_user['id'][] = $invoice_user->user_id;
				$selected_invoice_user['name'][] = trim($invoice_user->firstname.' '.$invoice_user->lastname);
			}
		}
		return $selected_invoice_user;
	}
	
	function get_clients($project_id) {
		return $this->db->query( "SELECT u.id, u.firstname, u.lastname FROM project_assign_clients AS pac
			JOIN users AS u ON (u.id = pac.assign_user_id) 
			JOIN user_roles AS ur ON (ur.user_id = u.id) 
			WHERE pac.company_id = ".$this->company_id." AND pac.company_id = ur.company_id AND pac.project_id = ".$project_id." AND ur.role_id = 3"
			 )->result();
	}
	
	function get_project_clients() {
		$message = '';
		$project_id = $this->input->get('project_id');
		if( !empty( $project_id ) ){ 
			$clients = $this->get_clients($project_id);
			if(!empty($clients)) {
				foreach( $clients as $client ){
					$message .= '<option value="'.$client->id.'">'.trim($client->firstname.' '.$client->lastname).'</option>';
				}
			}
		}
		echo json_encode(array('message' => $message));
		die();
	}

	function delete_invoice_users( $invoice_id ) {
		$this->db->where('invoice_id', $invoice_id);
		$this->db->delete('invoice_users');
	}
}
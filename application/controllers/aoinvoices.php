<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ClassName: aoInvoices
 * Function Name: __construct
 * This class is used for Account Owner Create /Add /Edit /Delete Invoices
 **/
class aoInvoices extends MY_Controller {
               
	function __construct() {
		parent::__construct();
		$access = FALSE;
		$this->load->database();


	}
    
	function index() {
		$company_id = $this->sessionArr['company_id'];
		$user_id = $this->sessionArr['user_id'];

		$days_in_this_month = days_in_month(date('m'), date('Y'));
		$lastday_in_month =  strtotime(date('Y')."-".date('m')."-".$days_in_this_month);
		$firstday_in_month =  strtotime(date('Y')."-".date('m')."-01");

		$this->view_data['invoices_due_this_month'] = Invoice::count(array('conditions' => 'UNIX_TIMESTAMP(`due_date`) <= '.$lastday_in_month.' and UNIX_TIMESTAMP(`due_date`) >= '.$firstday_in_month.' AND estimate != 1 AND status != "paid" AND company_id = '.$company_id.' AND status != "canceled"'));

		$this->view_data['invoices_paid_this_month'] = Invoice::count(array('conditions' => 'UNIX_TIMESTAMP(`paid_date`) <= '.$lastday_in_month.' and UNIX_TIMESTAMP(`paid_date`) >= '.$firstday_in_month.' AND estimate != 1 AND status = "paid" AND company_id = '.$company_id));

		//$this->view_data['invoicehastask'] = Invoice::find('all',array('conditions' => array('user_id=? AND estimate != ? AND issue_date<=?',$user_id,1,date('Y-m-d', time()))));


		/*$sql_project_details = 'SELECT pr.id AS prid, pr.name AS prname, inv.id, inv.reference, inv.issue_date, inv.due_date, inv.sent_date, inv.currency, inv.sum, inv.status, iprh.invoice_id, iprh.project_id
		FROM invoices AS inv
		LEFT JOIN invoices_has_project_tasks AS iprh ON inv.id = iprh.invoice_id
		LEFT JOIN projects AS pr ON pr.id = iprh.project_id
		WHERE inv.user_id = "'.$user_id.'"
		AND inv.status = "Open"
		AND inv.issue_date >= "'.date('Y-m-d', time()).'"
		AND inv.estimate != 1
		LIMIT 0 , 30';*/

		
		$sql_project_details = $this->db->query('SELECT pr.id AS prid, pr.name AS prname, inv.id, inv.reference, inv.issue_date, inv.due_date, inv.sent_date, inv.currency, inv.sum, inv.status, iprh.invoice_id, iprh.project_id
		FROM invoices AS inv
		LEFT JOIN invoices_has_project_tasks AS iprh ON inv.id = iprh.invoice_id
		LEFT JOIN projects AS pr ON pr.id = iprh.project_id
		WHERE inv.user_id = "'.$user_id.'"
		AND inv.status = "Open"
		AND inv.issue_date <= "'.date('Y-m-d', time()).'"
		AND inv.estimate != 1')->result_array();


		$this->view_data['invoicehastask'] = $sql_project_details;

		//$this->view_data['invoicehastask'] = InvoicesHasProjectTask::find('all',array('conditions' => array('user_id=? AND estimate != ? AND issue_date<=?',$user_id,1,date('Y-m-d', time()))));

		$this->content_view = 'invoices/accountowner_views/all';

	}

	function create(){
		$company_id = $this->sessionArr['company_id'];
		$user_id = $this->sessionArr['user_id'];
		if($_POST){
			unset($_POST['send']);
			unset($_POST['_wysihtml5_mode']);
			unset($_POST['files']);

			
			$invoice = array();
			$invoice['status'] = 'Open';
			$invoice['user_id'] = $user_id;
			$invoice['company_id'] = $company_id;
			$invoice['reference'] = trim(htmlspecialchars($_POST['reference']));
			$invoice['project_id'] = trim(htmlspecialchars($_POST['project_type']));
			$invoice['issue_date'] = trim(htmlspecialchars($_POST['issue_date']));
			$invoice['due_date'] = trim(htmlspecialchars($_POST['due_date']));
			$invoice['currency'] = trim(htmlspecialchars($_POST['currency']));
			$invoice['discount'] = trim(htmlspecialchars($_POST['discount']));
			$invoice['terms'] = trim(htmlspecialchars($_POST['terms']));

			$invoice['tax'] = trim(htmlspecialchars($_POST['tax']));
			$invoice['second_tax'] = trim(htmlspecialchars($_POST['second_tax']));

			$invoice = Invoice::create($invoice);

			$new_invoice_reference = $_POST['reference']+1;

			$invoice_reference = Setting::first();
			$invoice_reference->update_attributes(array('invoice_reference' => $new_invoice_reference));

			if($invoice) {

				$lastinvoice = Invoice::last();
				$invoice_id = $lastinvoice->id;

				$new_invoice_reference = $_POST['reference']+1;
			
				$invoice_has_task = array();
				$invoice_has_task['invoice_id'] = $invoice_id;
				$invoice_has_task['user_id'] = $user_id;
				$invoice_has_task['company_id'] = $company_id;
				$invoice_has_task['project_id'] = $_POST['project_type'];
				$invoice_has_task['project_has_task_id'] = $_POST['task_type'];

				$invoice_task = InvoicesHasProjectTask::create($invoice_has_task);
						$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_create_invoice_success'));
				} else {
					
					$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_create_invoice_error'));
				} 
				redirect('aoinvoices/');
		}	else {

			$query_project_result = $this->db->query('SELECT ur.user_id, ur.role_id, ur.company_id, c.id as cid, c.name as cname, p.id as pid, p.name as pname, p.company_id as pcid FROM user_roles AS ur JOIN companies AS c ON ur.company_id = c.id
			JOIN projects AS p ON p.company_id = c.id
			AND ur.user_id = "'.$user_id.'"')->result_array();

			
			$sql_task_details = $this->db->query('SELECT p.id as pid, p.name as pname, p.company_id as pcid, pt.id as ptid, pt.name as ptname, pt.status as ptstatus FROM user_roles AS ur JOIN companies AS c ON ur.company_id = c.id
			JOIN projects AS p ON p.company_id = c.id
			JOIN project_has_tasks AS pt ON pt.project_id = p.id
			AND ur.user_id = "'.$user_id.'"
			AND pt.status = "open"')->result_array();

			$this->view_data['tasks'] = $sql_task_details;

			$project_array = array();
			if(!empty($query_project_result)) {
				$this->view_data['projects'] = array_filter($query_project_result);
			}

			if(!empty($sql_task_details)) {
					$this->view_data['task'] = array_filter($sql_task_details);
			}
			
			$this->view_data['user_id'] = $user_id;

			$this->view_data['invoices'] = Invoice::all();
			$this->view_data['next_reference'] = Invoice::last();

			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_create_invoice');
			$this->view_data['form_action'] = base_url().'aoinvoices/create';
			$this->content_view = 'invoices/accountowner_views/_invoice';
		}
	}

	function get_tasks() {

		$project_id = $_GET['project_id']['project_id'];

		$sql_task_details = 'SELECT pt.id, pt.project_id, pt.name, pt.status
		FROM project_has_tasks AS pt 
		WHERE pt.status = "open" AND
		pt.project_id = "'.$project_id.'"';

		$task = array();
		$query_task_result = $this->db->query($sql_task_details)->result_array();
		$user_value = "";

		if(!empty($query_task_result)) {

				$options = array();
                $options['0'] = 'Please Select Task';

				foreach ($query_task_result as $value):  
					$options[$value['id']] = $value['name'];
                endforeach;

				$c = "";
		} else{
			$options = array();
            $options['0'] = 'Please Select Task';
		}
		$user_value .= form_dropdown('task_type', $options, $c, 'style="width:100%" name="task_type" id="task_type" class="chosen-select"');
		print_r($user_value);

		exit;

	}
	
	function view($id = FALSE){
		$company_id = $this->sessionArr['company_id'];
		$user_id = $this->sessionArr['user_id'];
		$this->view_data['submenu'] = array(
						$this->lang->line('application_back') => 'invoices',
				 		);	
		$this->view_data['invoice'] = Invoice::find($id);
		$data["core_settings"] = Setting::first();
		$invoice = $this->view_data['invoice'];

		$this->view_data['invoicehastask'] = $this->db->query('SELECT inpht.id, inpht.invoice_id, inpht.user_id,  inpht.company_id, inpht.project_id, inpht.project_has_task_id, inpht.minute, inpht.hours, inpht.rate, inpht.description, inpht.type, inpht.value, prht.id, prht.project_id, prht.name, prht.status FROM invoices_has_project_tasks AS inpht JOIN project_has_tasks as prht ON inpht.project_has_task_id = prht.id WHERE inpht.user_id = "'.$user_id.'" AND inpht.invoice_id = "'.$id.'"')->result_array();

		$invoice_items = $this->view_data['invoicehastask'];

		$i = 0; $sum = 0;
		foreach ($invoice_items as $value){
			$sum = $sum+$value['rate']*$value['value']; 
		}
		if(substr($invoice->discount, -1) == "%"){ 
			$discount = sprintf("%01.2f", round(($sum/100)*substr($invoice->discount, 0, -1), 2)); 
		}
		else{
			$discount = $invoice->discount;
		}
		$sum = $sum-$discount;

		if($invoice->tax != ""){
			$tax_value = $invoice->tax;
		}else{
			$tax_value = $data["core_settings"]->tax;
		}

		if($invoice->second_tax != ""){
	      $second_tax_value = $invoice->second_tax;
	    }else{
	      $second_tax_value = $data["core_settings"]->second_tax;
	    }

		$tax = sprintf("%01.2f", round(($sum/100)*$tax_value, 2));
		$second_tax = sprintf("%01.2f", round(($sum/100)*$second_tax_value, 2));

    	$sum = sprintf("%01.2f", round($sum+$tax+$second_tax, 2));

    	$payment = 0;
    	$i = 0;
    	$payments = $invoice->invoice_has_payments;
    	if(isset($payments)){
    		foreach ($payments as $value) {
    			$payment = sprintf("%01.2f", round($payment+$payments[$i]->amount, 2));
    			$i++;
    		}
    	$invoice->paid = $payment;
    	$invoice->outstanding = sprintf("%01.2f", round($sum-$payment, 2));
		}

		$invoice->sum = $sum;
			$invoice->save();

		if($this->view_data['invoice']->company_id != $company_id){ redirect('aoinvoices');}
		$this->content_view = 'invoices/accountowner_views/view';
	}
	
	function download($id = FALSE){
     $this->load->helper(array('dompdf', 'file')); 
     $this->load->library('parser');
     $data["invoice"] = Invoice::find($id); 
     $data['items'] = InvoiceHasItem::find('all',array('conditions' => array('invoice_id=?',$id)));
     if($data['invoice']->company_id != $this->client->company->id){ redirect('cinvoices');}
     $data["core_settings"] = Setting::first(); 
     $due_date = date($data["core_settings"]->date_format, human_to_unix($data["invoice"]->due_date.' 00:00:00'));  
     $parse_data = array(
            					'due_date' => $due_date,
            					'invoice_id' => $data["core_settings"]->invoice_prefix.$data["invoice"]->reference,
            					'client_link' => $data["core_settings"]->domain,
            					'company' => $data["core_settings"]->company,
            					); 
  	$html = $this->load->view($data["core_settings"]->template. '/' .$data["core_settings"]->invoice_pdf_template, $data, true); 
     $html = $this->parser->parse_string($html, $parse_data); 
     $filename = $this->lang->line('application_invoice').'_'.$data["core_settings"]->invoice_prefix.$data["invoice"]->reference;
     pdf_create($html, $filename, TRUE);
       
	}

	function banktransfer($id = FALSE, $sum = FALSE){

		$this->theme_view = 'modal';
		$this->view_data['title'] = $this->lang->line('application_bank_transfer');
	
		$data["core_settings"] = Setting::first();
		$this->view_data['invoice'] = Invoice::find($id);
		$this->content_view = 'invoices/accountowner_views/_banktransfer';
	}

    function twocheckout($id = FALSE, $sum = FALSE){
		$data["core_settings"] = Setting::first();
		$this->load->helper('notification');
		
		if($_POST){ 
					$invoice = Invoice::find_by_id($_POST['id']);
					$invoice_reference = $data["core_settings"]->invoice_prefix.$invoice->reference;
					$this->load->file(APPPATH.'helpers/2checkout/Twocheckout.php', true);
					$token = $_POST["token"];
					Twocheckout::privateKey($data["core_settings"]->twocheckout_private_key);
					Twocheckout::sellerId($data["core_settings"]->twocheckout_seller_id);
					//Twocheckout::sandbox(true);  #Uncomment to use Sandbox

					//Get currency
					$currency = $invoice->currency;
				    $currency_codes = getCurrencyCodesForTwocheckout();
					if(!array_key_exists($currency, $currency_codes)){
						$currency = $data["core_settings"]->twocheckout_currency;
					}

					try {
					    $charge = Twocheckout_Charge::auth(array(
					        "merchantOrderId" => $invoice_reference,
					        "token"      => $_POST['token'],
					        "currency"   => $currency,
					        "total"      =>$_POST['sum'],
					        "billingAddr" => array(
					            "name" => $invoice->company->name,
					            "addrLine1" => $invoice->company->address,
					            "city" => $invoice->company->city,
					            "state" => $invoice->company->province,
					            "zipCode" => $invoice->company->zipcode,
					            "country" => $invoice->company->country,
					            "email" => $invoice->company->client->email,
					            "phoneNumber" => $invoice->company->phone
					        )
					    ));

					    if ($charge['response']['responseCode'] == 'APPROVED') {

					        $attr= array();
							$paid_date = date('Y-m-d', time());
							$payment_reference = $invoice->reference.'00'.InvoiceHasPayment::count(array('conditions' => 'invoice_id = '.$invoice->id))+1;
							$attributes = array('invoice_id' => $invoice->id, 'reference' => $payment_reference, 'amount' => $_POST['sum'], 'date' => $paid_date, 'type' => 'credit_card', 'notes' => '');
							$invoiceHasPayment = InvoiceHasPayment::create($attributes);
								
							if($_POST['sum'] >= $invoice->outstanding){
								$invoice->update_attributes(array('paid_date' => $paid_date, 'status' => 'Paid'));
							}else{
								$invoice->update_attributes(array('status' => 'PartiallyPaid'));
							}
							
							$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_payment_complete'));
					        log_message('error', '2Checkout: Payment of '.$_POST['sum'].' for invoice '.$invoice_reference.' received!');
					        //send receipt to client
							receipt_notification($this->client->id, FALSE, $invoiceHasPayment->id);
							//send email to admin
							send_notification($data["core_settings"]->email, $this->lang->line('application_notification_payment_processed_subject'), $this->lang->line('application_notification_payment_processed').' #'.$data["core_settings"]->invoice_prefix.$invoiceHasPayment->invoice->reference);

					    }
					} catch (Twocheckout_Error $e) {
						$this->session->set_flashdata('message', 'error: Your payment could NOT be processed (i.e., you have not been charged) because the payment system rejected the transaction.');
					    log_message('error', '2Checkout: Payment of invoice '.$invoice_reference.' failed - '.$e->getMessage());
					}
					redirect('cinvoices/view/'.$_POST['id']);
			}else{
				$this->view_data['invoices'] = Invoice::find_by_id($id);
			
				$this->view_data['publishable_key'] = $data["core_settings"]->twocheckout_publishable_key;
				$this->view_data['seller_id'] = $data["core_settings"]->twocheckout_seller_id;

				$this->view_data['sum'] = $sum;
				$this->theme_view = 'modal';
				$this->view_data['title'] = $this->lang->line('application_pay_with_credit_card');
				$this->view_data['form_action'] = base_url().'cinvoices/twocheckout';
				$this->content_view = 'invoices/_2checkout';
			}
	}

	function stripepay($id = FALSE, $sum = FALSE){
		$data["core_settings"] = Setting::first();
		$this->load->helper('notification');
		$stripe_keys = array(
		  "secret_key"      => $data["core_settings"]->stripe_p_key, 
		  "publishable_key" => $data["core_settings"]->stripe_key 
		);


		if($_POST){
			unset($_POST['send']);
			$invoice = Invoice::find($_POST['id']);
			
			// Stores errors:
	$errors = array();
	
	// Need a payment token:
	if (isset($_POST['stripeToken'])) {
		
		$token = $_POST['stripeToken'];
		
		// Check for a duplicate submission, just in case:
		// Uses sessions, you could use a cookie instead.
		if (isset($_SESSION['token']) && ($_SESSION['token'] == $token)) {
			$errors['token'] = 'You have apparently resubmitted the form. Please do not do that.';
			$this->session->set_flashdata('message', 'error: You have apparently resubmitted the form. Please do not do that.');
		
		} else { // New submission.
			$_SESSION['token'] = $token;
		}		
		
	} else {
		$this->session->set_flashdata('message', 'error: The order cannot be processed. Please make sure you have JavaScript enabled and try again.');
		$errors['token'] = 'The order cannot be processed. Please make sure you have JavaScript enabled and try again.';
		log_message('error', 'Stripe: ERROR - Payment canceled for invoice #'.$data["core_settings"]->invoice_prefix.$invoice->reference.'.');
			
	}
	
	// Set the order amount somehow:
	$sum_exp = explode('.', $_POST['sum']);
	$amount = $sum_exp[0]*100+$sum_exp[1]; // in cents

	//Get currency
	$currency = $invoice->currency;
    $currency_codes = getCurrencyCodes();
	if(!array_key_exists($currency, $currency_codes)){
		$currency = $data["core_settings"]->stripe_currency;
	}

	// Validate other form data!

	// If no errors, process the order:
	if (empty($errors)) {
		
		// create the charge on Stripe's servers - this will charge the user's card
		try {
			
			// Include the Stripe library:
			$this->load->file(APPPATH.'helpers/stripe/lib/Stripe.php', true);

			// set your secret key
			// see your keys here https://manage.stripe.com/account
			Stripe::setApiKey($stripe_keys["secret_key"]);

			// Charge the order:
			$charge = Stripe_Charge::create(array(
				"amount" => $amount, // amount in cents, again
				"currency" => $currency,
				"card" => $token,
				"description" => $data["core_settings"]->invoice_prefix.$invoice->reference
				)
			);

			// Check that it was paid:
			if ($charge->paid == true) {
				$attr= array();
				$paid_date = date('Y-m-d', time());
				$payment_reference = $invoice->reference.'00'.InvoiceHasPayment::count(array('conditions' => 'invoice_id = '.$invoice->id))+1;
				$attributes = array('invoice_id' => $invoice->id, 'reference' => $payment_reference, 'amount' => $_POST['sum'], 'date' => $paid_date, 'type' => 'credit_card', 'notes' => '');
				$invoiceHasPayment = InvoiceHasPayment::create($attributes);
					
				
				if($_POST['sum'] >= $invoice->outstanding){
					$invoice->update_attributes(array('paid_date' => $paid_date, 'status' => 'Paid'));
				}else{
					$invoice->update_attributes(array('status' => 'PartiallyPaid'));
				}
				$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_payment_complete'));
				log_message('error', 'Stripe: Payment for Invoice #'.$data["core_settings"]->invoice_prefix.$invoice->reference.' successfully made');
				//send receipt to client
				receipt_notification($this->client->id, FALSE, $invoiceHasPayment->id);
				//send email to admin
				send_notification($data["core_settings"]->email, $this->lang->line('application_notification_payment_processed_subject'), $this->lang->line('application_notification_payment_processed').' #'.$data["core_settings"]->invoice_prefix.$invoiceHasPayment->invoice->reference);
			} else { // Charge was not paid!	
				$this->session->set_flashdata('message', 'error: Your payment could NOT be processed (i.e., you have not been charged) because the payment system rejected the transaction.');
				log_message('error', 'Stripe: ERROR - Payment for Invoice #'.$data["core_settings"]->invoice_prefix.$invoice->reference.' was not successful!');

				}
			
		} catch (Stripe_CardError $e) {
		    // Card was declined.
			$e_json = $e->getJsonBody();
			$err = $e_json['error'];
			$errors['stripe'] = $err['message'];
			$this->session->set_flashdata('message', 'error: Card was declined!');
			log_message('error', 'Stripe: ERROR - Credit Card was declined by Stripe! Payment process canceled for invoice #'.$data["core_settings"]->invoice_prefix.$invoice->reference.'.');
		
		} catch (Stripe_ApiConnectionError $e) {
		    log_message('error', 'Stripe: '.$e);
		} catch (Stripe_InvalidRequestError $e) {
			log_message('error', 'Stripe: '.$e);
		} catch (Stripe_ApiError $e) {
		    log_message('error', 'Stripe: '.$e);
		} catch (Stripe_CardError $e) {
		    log_message('error', 'Stripe: '.$e);
		}

	}else{
		
		$this->session->set_flashdata('message', 'error: '.$errors["token"]);
		log_message('error', 'Stripe: '.$errors["token"]);
		
	} 



			redirect('cinvoices/view/'.$_POST['id']);
		}else
		{
			$this->view_data['invoices'] = Invoice::find_by_id($id);
			
			$this->view_data['public_key'] = $data["core_settings"]->stripe_key;
			$this->view_data['sum'] = $sum;
			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_pay_with_credit_card');
			$this->view_data['form_action'] = base_url().'cinvoices/stripepay';
			$this->content_view = 'invoices/_stripe';
		}

	}

    function success($id = FALSE){
		$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_payment_success'));
		redirect('cinvoices/view/'.$id);
	}

	function authorizenet($id = FALSE){

		if($_POST){
				// Authorize.net lib
				$data["core_settings"] = Setting::first();
				$this->load->library('authorize_net');
				$invoice = Invoice::find_by_id($_POST['invoice_id']);
				log_message('error', 'Authorize.net: Payment process started for invoice: #'.$data["core_settings"]->invoice_prefix.$invoice->reference);
				
				$amount = $_POST["sum"];

				$auth_net = array(
					'x_card_num'			=> str_replace(' ', '', $_POST['x_card_num']),
					'x_exp_date'			=> $_POST['x_card_month'].'/'.$_POST['x_card_year'],
					'x_card_code'			=> $_POST['x_card_code'],
					'x_description'			=> $this->lang->line('application_invoice').' #'.$data["core_settings"]->invoice_prefix.$invoice->reference,
					'x_amount'				=> $amount,
					'x_first_name'			=> $invoice->company->client->firstname,
					'x_last_name'			=> $invoice->company->client->lastname,
					'x_address'				=> $invoice->company->address,
					'x_city'				=> $invoice->company->city,
					//'x_state'				=> 'KY',
					'x_zip'					=> $invoice->company->zipcode,
					//'x_country'			=> 'US',
					'x_phone'				=> $invoice->company->phone,
					'x_email'				=> $invoice->company->client->email,
					'x_customer_ip'			=> $this->input->ip_address(),
					);
				$this->authorize_net->setData($auth_net);
				// Try to AUTH_CAPTURE
				if( $this->authorize_net->authorizeAndCapture() )
				{
					
					$this->session->set_flashdata('message', 'success: '.$this->lang->line('messages_payment_complete'));
					
					log_message('error', 'Authorize.net: Transaction ID: ' . $this->authorize_net->getTransactionId());
					log_message('error', 'Authorize.net: Approval Code: ' . $this->authorize_net->getApprovalCode());
					log_message('error', 'Authorize.net: Payment completed.');
					$invoice->status = "Paid";
					$invoice->paid_date = date('Y-m-d', time());

					$invoice->save();
					$attributes = array('invoice_id' => $invoice->id, 'reference' => $this->authorize_net->getTransactionId(), 'amount' => $amount, 'date' => date('Y-m-d', time()), 'type' => 'credit_card', 'notes' => $this->authorize_net->getApprovalCode());
					$invoiceHasPayment = InvoiceHasPayment::create($attributes);
					//send receipt to client
					receipt_notification($this->client->id, FALSE, $invoiceHasPayment->id);
					//send email to admin
					send_notification($data["core_settings"]->email, $this->lang->line('application_notification_payment_processed_subject'), $this->lang->line('application_notification_payment_processed').' #'.$data["core_settings"]->invoice_prefix.$invoiceHasPayment->invoice->reference);
					redirect('cinvoices/view/'.$invoice->id);
				}
				else
				{
					
				log_message('error', 'Authorize.net: Payment failed.');
				log_message('error', 'Authorize.net: '.$this->authorize_net->getError());

				

					$this->view_data['return_link'] = "invoices/view/".$invoice->id;

					$this->view_data['message'] = $this->authorize_net->getError();
					//$this->authorize_net->debug();


					$this->content_view = 'error/error';
				}
		}else{

			$this->view_data['invoices'] = Invoice::find_by_id($id);
			$this->view_data["settings"] = Setting::first();
			$this->view_data["sum"] = sprintf("%01.2f", $this->view_data['invoices']->outstanding);

			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_pay_with_credit_card');
			$this->view_data['form_action'] = base_url().'cinvoices/authorizenet';
			$this->content_view = 'invoices/_authorizenet';
		}



	}	
}
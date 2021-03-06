<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ClassName: Invoices
 * Function Name: __construct
 * This class is used for Admin Create /Add /Edit /Delete Invoices
 **/
class Subscriptioninvoices extends MY_Controller {
               
	function __construct()
	{
		parent::__construct();
		$access = FALSE;
		$this->load->database();
		$this->settings = Setting::first();
        if(!$this->user){
            $this->view_data['error'] = "true";
            $this->session->set_flashdata('message', 'error: You have no access to any modules!');
            redirect('login');
        }
		/*$this->view_data['submenu'] = array(
				 		$this->lang->line('application_all') => 'invoices',
				 		$this->lang->line('application_open') => 'invoices/filter/open',
				 		$this->lang->line('application_Sent') => 'invoices/filter/sent',
				 		$this->lang->line('application_Paid') => 'invoices/filter/paid',
				 		$this->lang->line('application_Canceled') => 'invoices/filter/canceled',
				 		$this->lang->line('application_Overdue') => 'invoices/filter/overdue',
				 		);	*/
	}

	function index()
	{
		$invoices = Invoice::find_by_sql('SELECT u.*,pus.id as invoiceid,pus.start_date,pus.end_date,ppd.currency_converted_amount,ppd.result_value
                                        FROM users u 
                                        INNER JOIN propay_user_subscription as pus ON pus.user_id = u.id
                                        INNER JOIN propay_payment_detail as ppd ON ppd.id = pus.payment_detail_id
                                        order by pus.id desc ');
        if(!empty($invoices))
        {   
            $invoices_all =array();
            $i=0;
            foreach($invoices as $key =>$value)
            {
                $invoices_all[$i]['id']=$value->id;
                $invoices_all[$i]['invoiceid']=$value->invoiceid;
                $invoices_all[$i]['name']=$value->firstname.' '.$value->lastname;
                $invoices_all[$i]['email']=$value->email;
                $invoices_all[$i]['start_date']=$value->start_date;
                $invoices_all[$i]['end_date']=$value->end_date;
                $invoices_all[$i]['amount']=$value->currency_converted_amount;
                $invoices_all[$i]['status']=$value->status;
                $invoices_all[$i]['result_value']=$value->result_value;
                $invoices_all[$i]['next_payment']=date('Y-m-d',strtotime($value->end_date.' +1 day'));
                $i++;
            }
        }
        $this->view_data['invoices']=$invoices_all;
        $this->content_view = 'subscriptioninvoices/all';
	}
	function calc()
	{
		$invoices = Invoice::find('all', array('conditions' => array('estimate != ?', 1)));
		foreach ($invoices as $invoice) {
		
		$settings = Setting::first();

		$items = InvoiceHasItem::find('all',array('conditions' => array('invoice_id=?',$invoice->id)));

		//calculate sum
		$i = 0; $sum = 0;
		foreach ($items as $value){
			$sum = $sum+$invoice->invoice_has_items[$i]->amount*$invoice->invoice_has_items[$i]->value; $i++;
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
			$tax_value = $settings->tax;
		}

		if($invoice->second_tax != ""){
	      $second_tax_value = $invoice->second_tax;
	    }else{
	      $second_tax_value = $core_settings->second_tax;
	    }

		$tax = sprintf("%01.2f", round(($sum/100)*$tax_value, 2));
		$second_tax = sprintf("%01.2f", round(($sum/100)*$second_tax_value, 2));

    $sum = sprintf("%01.2f", round($sum+$tax+$second_tax, 2));


		$invoice->sum = $sum;
		$invoice->save();

		}
		redirect('invoices');

	}
	function filter($condition = FALSE)
	{
		$days_in_this_month = days_in_month(date('m'), date('Y'));
		$lastday_in_month =  date('Y')."-".date('m')."-".$days_in_this_month;
		$firstday_in_month =  date('Y')."-".date('m')."-01";
		$this->view_data['invoices_paid_this_month'] = Invoice::count(array('conditions' => 'paid_date <= '.$lastday_in_month.' and paid_date >= '.$firstday_in_month.' AND estimate != 1'));
		$this->view_data['invoices_due_this_month'] = Invoice::count(array('conditions' => 'due_date <= '.$lastday_in_month.' and due_date >= '.$firstday_in_month.' AND estimate != 1'));

		//statistic
		$now = time();
		$beginning_of_week = strtotime('last Monday', $now); // BEGINNING of the week
		$end_of_week = strtotime('next Sunday', $now) + 86400; // END of the last day of the week
		$this->view_data['invoices_due_this_month_graph'] = Invoice::find_by_sql('select count(id) AS "amount", DATE_FORMAT(`due_date`, "%w") AS "date_day", DATE_FORMAT(`due_date`, "%Y-%m-%d") AS "date_formatted" from invoices where UNIX_TIMESTAMP(`due_date`) >= "'.$beginning_of_week.'" AND UNIX_TIMESTAMP(`due_date`) <= "'.$end_of_week.'" AND estimate != 1');
		$this->view_data['invoices_paid_this_month_graph'] = Invoice::find_by_sql('select count(id) AS "amount", DATE_FORMAT(`paid_date`, "%w") AS "date_day", DATE_FORMAT(`paid_date`, "%Y-%m-%d") AS "date_formatted" from invoices where UNIX_TIMESTAMP(`paid_date`) >= "'.$beginning_of_week.'" AND UNIX_TIMESTAMP(`paid_date`) <= "'.$end_of_week.'" AND estimate != 1');

		switch ($condition) {
				case 'open':
					$option = 'status = "Open" and estimate != 1';
					break;
				case 'sent':
					$option = 'status = "Sent" and estimate != 1';
					break;
				case 'paid':
					$option = 'status = "Paid" and estimate != 1';
					break;
				case 'canceled':
					$option = 'status = "Canceled" and estimate != 1';
					break;
				case 'overdue':
					$option = '(status = "Open" OR status = "Sent" OR status = "PartiallyPaid") and estimate != 1 and due_date < "'.date('Y')."-".date('m').'-'.date('d').'" ';
					break;
				default:
					$option = 'estimate != 1';					
					break;
			}

		if($this->user->admin == 0){ 
			$comp_array = array();
			$thisUserHasNoCompanies = (array) $this->user->companies;
					if(!empty($thisUserHasNoCompanies)){
				foreach ($this->user->companies as $value) {
					array_push($comp_array, $value->id);
				}
				$options = array('conditions' => array($option.' AND company_id in (?)',$comp_array));
				$this->view_data['invoices'] = Invoice::find('all', $options);
			}else{
				$this->view_data['invoices'] = (object) array();
			}
		}else{
			$options = array('conditions' => array($option));
			$this->view_data['invoices'] = Invoice::find('all', $options);
		}
		
					
		
		
		$this->content_view = 'invoices/all';
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
			
			$invoice = Invoice::create($_POST);
			$new_invoice_reference = $_POST['reference']+1;
			
			$invoice_reference = Setting::first();
			$invoice_reference->update_attributes(array('invoice_reference' => $new_invoice_reference));
       		if(!$invoice){
				$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_create_invoice_error'));
			} else {
				$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_create_invoice_success'));
				if( !empty( $client_list ) ){				
					foreach( $client_list as $client ){
						$invoice_user = array(
							'invoice_id' => $invoice->id,
							'user_id' => $client,
						);
						InvoiceUsers::create($invoice_user);
					}
				}
			}
			redirect('invoices');
		} else {
			
			// $this->view_data['invoice'] = Invoice::find_by_sql('SELECT inv.*, p.name AS project_name, p.id AS project_id FROM invoices AS inv JOIN projects AS p ON p.id = inv.project_id WHERE inv.company_id = '.$this->company_id.' AND inv.estimate != 1 AND inv.id = '.$id)[0];
			
			$this->view_data['projects'] = $this->get_projects();
			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_create_invoice');
			$this->view_data['form_action'] = base_url().'invoices/create';
			$this->content_view = 'invoices/_invoice';
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
			$invoice = Invoice::find($id);
			if($_POST['status'] == "Paid" && !isset($_POST['paid_date'])){ $_POST['paid_date'] = date('Y-m-d', time());}
			if($_POST['status'] == "Sent" && $invoice->status != "Sent" && !isset($_POST['sent_date'])){ $_POST['sent_date'] = date('Y-m-d', time());}

			
			$invoice->update_attributes($_POST);
			
       		if(!$invoice){
				$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_save_invoice_error'));
			} else {
				$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_save_invoice_success'));
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
			if($view == 'true'){redirect('invoices/view/'.$id);}else{redirect('invoices');}
			
		}else
		{
			
			$this->view_data['projects'] = $this->get_projects();
			$this->view_data['company_id'] = $this->company_id;
			
			$invoice = Invoice::find($id);
			$this->view_data['project'] = $invoice->project_id;
			$this->view_data['clients'] = $this->get_clients($invoice->project_id);
			
			
			$this->view_data['selected_invoice_user'] = $this->get_selected_clients($id);
			$this->view_data['invoice'] = Invoice::find($id);
			
			if($getview == "view"){$this->view_data['view'] = "true";}
			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_edit_invoice');
			$this->view_data['form_action'] = base_url().'invoices/update';
			$this->content_view = 'invoices/_invoice';
		}
	}	
	
	function view($id = FALSE)
	{

		
		$this->view_data['submenu'] = array(
						$this->lang->line('application_back') => 'invoices',
				 		);	
		
		// $this->view_data['invoice'] = Invoice::find($id);

		$this->view_data['invoice'] = Invoice::find_by_sql('SELECT inv.*, p.name AS project_name, p.id AS project_id FROM invoices AS inv JOIN projects AS p ON p.id = inv.project_id WHERE inv.company_id = '.$this->company_id.' AND inv.estimate != 1 AND inv.id = '.$id)[0];
		
		# ***
		# Default readonly values is false when using find_by_sql
		# set readonly false to allow save(), otherwise it will throw exception
		# ***
		$this->view_data['invoice']->readonly(false);
		
		if($this->view_data['invoice']->company_id != $this->company_id){redirect('invoices');}
		
		$data["core_settings"] = Setting::first();
		$invoice = $this->view_data['invoice'];
		$this->view_data['items'] = $invoice->invoice_has_items;

		//calculate sum
		$i = 0; $sum = 0;
		foreach ($this->view_data['items'] as $value){
			$sum = $sum+$invoice->invoice_has_items[$i]->amount*$invoice->invoice_has_items[$i]->value; $i++;
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
		$this->view_data['selected_invoice_user'] = $this->get_selected_clients($id);
		$this->content_view = 'invoices/view';
	}
	function banktransfer($id = FALSE, $sum = FALSE){

		$this->theme_view = 'modal';
		$this->view_data['title'] = $this->lang->line('application_bank_transfer');
	
		$data["core_settings"] = Setting::first();
		$this->view_data['invoice'] = Invoice::find($id);
		$this->content_view = 'invoices/_banktransfer';
	}
	function payment($id = FALSE){

		if($_POST){
			unset($_POST['send']);
			unset($_POST['_wysihtml5_mode']);
			unset($_POST['files']);
			$_POST['user_id'] = $this->user->id;
			$invoice = Invoice::find_by_id($_POST['invoice_id']);
			$invoiceHasPayment = InvoiceHasPayment::create($_POST);
			
			if($invoice->outstanding == $_POST['amount']){
				$new_status = "Paid";
				$payment_date = $_POST['date'];
			}else{
				$new_status = "PartiallyPaid";
			}
			
			$invoice->update_attributes(array('status' => $new_status));
			if(isset($payment_date)){ $invoice->update_attributes(array('paid_date' => $payment_date)); }
       		if(!$invoiceHasPayment){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_create_payment_error'));}
       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_create_payment_success'));}
			redirect('invoices/view/'.$_POST['invoice_id']);
		}else
		{
			$this->view_data['invoice'] = Invoice::find_by_id($id);
			$this->view_data['payment_reference'] = InvoiceHasPayment::count(array('conditions' => 'invoice_id = '.$id))+1;
	    	$this->view_data['sumRest'] = sprintf("%01.2f", round($this->view_data['invoice']->sum-$this->view_data['invoice']->paid, 2));
	    	
	    	

			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_add_payment');
			$this->view_data['form_action'] = base_url().'invoices/payment';
			$this->content_view = 'invoices/_payment';
		}
	}
	function payment_update($id = FALSE){

		if($_POST){
			unset($_POST['send']);
			unset($_POST['_wysihtml5_mode']);
			unset($_POST['files']);

			$payment = InvoiceHasPayment::find_by_id($_POST['id']);
			$invoice_id = $payment->invoice_id;
			$payment = $payment->update_attributes($_POST);


			$invoice = Invoice::find_by_id($invoice_id);
			$payment = 0;
	    	$i = 0;
	    	$payments = $invoice->invoice_has_payments;
	    	if(isset($payments)){
	    		foreach ($payments as $value) {
	    			$payment = sprintf("%01.2f", round($payment+$payments[$i]->amount, 2));
	    			$i++;
	    		}

			}
			$paymentsum = sprintf("%01.2f", round($payment+$_POST['amount'], 2));
			if($invoice->sum <= $paymentsum){
				$new_status = "Paid";
				$payment_date = $_POST['date'];
				
			}else{
				$new_status = "PartiallyPaid";
			}
			$invoice->update_attributes(array('status' => $new_status));
			if(isset($payment_date)){ $invoice->update_attributes(array('paid_date' => $payment_date)); }
       		if(!$payment){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_edit_payment_error'));}
       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_edit_payment_success'));}
			redirect('invoices/view/'.$_POST['invoice_id']);

		}else
		{
			$this->view_data['payment'] = InvoiceHasPayment::find_by_id($id);
			$this->view_data['invoice'] = Invoice::find_by_id($this->view_data['payment']->invoice_id);
			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_add_payment');
			$this->view_data['form_action'] = base_url().'invoices/payment_update';
			$this->content_view = 'invoices/_payment';
		}
	}
	function payment_delete($id = FALSE, $invoice_id = FALSE)
	{	
		$item = InvoiceHasPayment::find_by_id($id);
		$item->delete();
		$this->content_view = 'invoices/view';
		if(!$item){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_delete_payment_error'));}
       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_delete_payment_success'));}
			redirect('invoices/view/'.$invoice_id);
	}
	function twocheckout($id = FALSE, $sum = FALSE){
		$data["core_settings"] = Setting::first();
		
		
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
					        "merchantOrderId" => $invoice->reference,
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
					        echo "Thanks for your Order!";
					        echo "<h3>Return Parameters:</h3>";
					        echo "<pre>";
					        print_r($charge);
					        echo "</pre>";

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

					    }
					} catch (Twocheckout_Error $e) {
						$this->session->set_flashdata('message', 'error: Your payment could NOT be processed (i.e., you have not been charged) because the payment system rejected the transaction.');
					    log_message('error', '2Checkout: Payment of invoice '.$invoice_reference.' failed - '.$e->getMessage());
					}
					redirect('invoices/view/'.$_POST['id']);
			}else{
				$this->view_data['invoices'] = Invoice::find_by_id($id);
			
				$this->view_data['publishable_key'] = $data["core_settings"]->twocheckout_publishable_key;
				$this->view_data['seller_id'] = $data["core_settings"]->twocheckout_seller_id;

				$this->view_data['sum'] = $sum;
				$this->theme_view = 'modal';
				$this->view_data['title'] = $this->lang->line('application_pay_with_credit_card');
				$this->view_data['form_action'] = base_url().'invoices/twocheckout';
				$this->content_view = 'invoices/_2checkout';
			}
	}
	function stripepay($id = FALSE, $sum = FALSE){
		$data["core_settings"] = Setting::first();

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

			Stripe::setApiKey($stripe_keys["secret_key"]);

			// Charge the order:
			$charge = Stripe_Charge::create(array(
				"amount" => $amount, // amount in cents, again
				"currency" => $currency,
				"card" => $token,
				"receipt_email" => $invoice->company->client->email,
				"description" => $data["core_settings"]->invoice_prefix.$invoice->reference,
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



			redirect('invoices/view/'.$_POST['id']);
		}else
		{
			$this->view_data['invoices'] = Invoice::find_by_id($id);
			
			$this->view_data['public_key'] = $data["core_settings"]->stripe_key;
			$this->view_data['sum'] = $sum;
			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_pay_with_credit_card');
			$this->view_data['form_action'] = base_url().'invoices/stripepay';
			$this->content_view = 'invoices/_stripe';
		}

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
					redirect('invoices/view/'.$invoice->id);
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
			$this->view_data['form_action'] = base_url().'invoices/authorizenet';
			$this->content_view = 'invoices/_authorizenet';
		}



	}
	function delete($id = FALSE)
	{	
		$invoice = Invoice::find($id);
		$invoice->delete();
		$this->content_view = 'invoices/all';
		if(!$invoice){
			$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_delete_invoice_error'));
		} else { 
			$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_delete_invoice_success'));
			$this->delete_invoice_users( $id );
		}
		redirect('invoices');
	}
	function preview($id = FALSE, $attachment = FALSE){
     $this->load->helper(array('dompdf', 'file')); 
     $this->load->library('parser');
     $data["invoice"] = Invoice::find($id); 
     $data['items'] = InvoiceHasItem::find('all',array('conditions' => array('invoice_id=?',$id)));
     $data["core_settings"] = Setting::first();
   
     $due_date = date($data["core_settings"]->date_format, human_to_unix($data["invoice"]->due_date.' 00:00:00'));  
     $parse_data = array(
            					'due_date' => $due_date,
            					'invoice_id' => $data["core_settings"]->invoice_prefix.$data["invoice"]->reference,
            					'client_link' => $data["core_settings"]->domain,
            					'company' => $data["core_settings"]->company,
            					'client_id' => $data["invoice"]->company->reference,
            					);
  	$html = $this->load->view($data["core_settings"]->template. '/' .$data["core_settings"]->invoice_pdf_template, $data, true); 
  	$html = $this->parser->parse_string($html, $parse_data);
     
     $filename = $this->lang->line('application_invoice').'_'.$data["core_settings"]->invoice_prefix.$data["invoice"]->reference;
     pdf_create($html, $filename, TRUE, $attachment);
	}
	function previewHTML($id = FALSE){
     $this->load->helper(array('file')); 
     $this->load->library('parser');
     $data["htmlPreview"] = true;
     $data["invoice"] = Invoice::find($id); 
     $data['items'] = InvoiceHasItem::find('all',array('conditions' => array('invoice_id=?',$id)));
     $data["core_settings"] = Setting::first();
   
     $due_date = date($data["core_settings"]->date_format, human_to_unix($data["invoice"]->due_date.' 00:00:00'));  
     $parse_data = array(
            					'due_date' => $due_date,
            					'invoice_id' => $data["core_settings"]->invoice_prefix.$data["invoice"]->reference,
            					'client_link' => $data["core_settings"]->domain,
            					'company' => $data["core_settings"]->company,
            					'client_id' => $data["invoice"]->company->reference,
            					);
  	$html = $this->load->view($data["core_settings"]->template. '/' .$data["core_settings"]->invoice_pdf_template, $data, true); 
  	$html = $this->parser->parse_string($html, $parse_data);
     $this->theme_view = 'blank';
	$this->content_view = 'invoices/_preview';
	}
	function sendinvoice($id = FALSE){
			$this->load->helper(array('dompdf', 'file'));
			$this->load->library('parser');

			$data["invoice"] = Invoice::find($id); 
			$data['items'] = InvoiceHasItem::find('all',array('conditions' => array('invoice_id=?',$id)));
     		$data["core_settings"] = Setting::first();
    		$due_date = date($data["core_settings"]->date_format, human_to_unix($data["invoice"]->due_date.' 00:00:00')); 
  			//Set parse values
  			$parse_data = array(
            					'client_contact' => $data["invoice"]->company->client->firstname.' '.$data["invoice"]->company->client->lastname,
            					'client_company' => $data["invoice"]->company->name,
            					'due_date' => $due_date,
            					'invoice_id' => $data["core_settings"]->invoice_prefix.$data["invoice"]->reference,
            					'invoice_value' => $data["invoice"]->sum,
            					'client_link' => $data["core_settings"]->domain,
            					'company' => $data["core_settings"]->company,
            					'logo' => '<img src="'.base_url().''.$data["core_settings"]->logo.'" alt="'.$data["core_settings"]->company.'"/>',
            					'invoice_logo' => '<img src="'.base_url().''.$data["core_settings"]->invoice_logo.'" alt="'.$data["core_settings"]->company.'"/>'
            					);
            // Generate PDF     
  			$html = $this->load->view($data["core_settings"]->template. '/' .$data["core_settings"]->invoice_pdf_template, $data, true); 
    		$html = $this->parser->parse_string($html, $parse_data);
    		$filename = $this->lang->line('application_invoice').'_'.$data["core_settings"]->invoice_prefix.$data["invoice"]->reference;
     		pdf_create($html, $filename, FALSE);
     		//email
     		$subject = $this->parser->parse_string($data["core_settings"]->invoice_mail_subject, $parse_data);
			//$this->email->from($data["core_settings"]->email, $data["core_settings"]->company);
			$this->email->from($this->settings->from_email_id,$this->settings->from_email_name);
			if(!isset($data["invoice"]->company->client->email)){
				$this->session->set_flashdata('message', 'error:This client company has no primary contact! Just add a primary contact.');
				redirect('invoices/view/'.$id);
			}
			$this->email->to($data["invoice"]->company->client->email); 
			$this->email->subject($subject); 
  			$this->email->attach("files/temp/".$filename.".pdf");
  			


  			$email_invoice = read_file('./application/views/'.$data["core_settings"]->template.'/templates/email_invoice.html');
  			$message = $this->parser->parse_string($email_invoice, $parse_data);
			$this->email->message($message);			
			if($this->email->send()){$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_send_invoice_success'));
			if($data["invoice"]->status == "Open"){
				$data["invoice"]->update_attributes(array('status' => 'Sent', 'sent_date' => date("Y-m-d")));
			}
			log_message('error', 'Invoice #'.$data["core_settings"]->invoice_prefix.$data["invoice"]->reference.' has been send to '.$data["invoice"]->company->client->email);
			}
       		else{$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_send_invoice_error'));
       		log_message('error', 'ERROR: Invoice #'.$data["core_settings"]->invoice_prefix.$data["invoice"]->reference.' has not been send to '.$data["invoice"]->company->client->email.'. Please check your servers email settings.');
       		}
			unlink("files/temp/".$filename.".pdf");
			redirect('invoices/view/'.$id);
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
				if($_POST['item_id'] == "-"){
					$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_add_item_error'));
					redirect('invoices/view/'.$_POST['invoice_id']);

				}else{
					$rebill = explode("_", $_POST['item_id']);
					if($rebill[0] == "rebill"){
						$itemvalue = Expense::find_by_id($rebill[1]);
						$_POST['name'] = $itemvalue->description;
						$_POST['type'] = $_POST['item_id'];
						$_POST['value'] = $itemvalue->value;
						$itemvalue->rebill = 2;
						$itemvalue->invoice_id = $_POST['invoice_id'];
						$itemvalue->save();
					}else{
						$itemvalue = Item::find_by_id($_POST['item_id']);
						$_POST['name'] = $itemvalue->name;
						$_POST['type'] = $itemvalue->type;
						$_POST['value'] = $itemvalue->value;
					}
				
				}
			}

			$item = InvoiceHasItem::create($_POST);
       		if(!$item){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_add_item_error'));}
       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_add_item_success'));}
			redirect('invoices/view/'.$_POST['invoice_id']);
			
		}else
		{
			$this->view_data['invoice'] = Invoice::find($id);
			$this->view_data['items'] = Item::find('all',array('conditions' => array('inactive=?','0')));
			$this->view_data['rebill'] = Expense::find('all',array('conditions' => array('project_id=? and (rebill=? or invoice_id=?)',$this->view_data['invoice']->project_id, 1, $id)));
	

			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_add_item');
			$this->view_data['form_action'] = base_url().'invoices/item';
			$this->content_view = 'invoices/_item';
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
			redirect('invoices/view/'.$_POST['invoice_id']);
			
		}else
		{
			$this->view_data['invoice_has_items'] = InvoiceHasItem::find($id);
			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_edit_item');
			$this->view_data['form_action'] = base_url().'invoices/item_update';
			$this->content_view = 'invoices/_item';
		}	
	}	
	function item_delete($id = FALSE, $invoice_id = FALSE)
	{	
		$item = InvoiceHasItem::find($id);
		$item->delete();
		$this->content_view = 'invoices/view';
		if(!$item){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_delete_item_error'));}
       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_delete_item_success'));}
			redirect('invoices/view/'.$invoice_id);
	}	

	function changestatus($id = FALSE, $status = FALSE)
	{	
		$invoice= Invoice::find_by_id($id);
		if($this->user->admin != 1){
				$comp_array = array();
			foreach ($this->user->companies as $value) {
				array_push($comp_array, $value->id);
			}
			if(!in_array($invoice->company_id, $comp_array)){
				return false;
			}
		}
		$invoice->status = $status;
		$invoice->save();
		die();
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
	function get_projects() {
		return $this->db->query( "SELECT p.* FROM projects as p JOIN companies AS c ON c.id = p.company_id JOIN users AS u ON ( u.id = c.user_id ) WHERE c.user_id = ". $this->user->id ." AND p.company_id = ". $this->company_id )->result();
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

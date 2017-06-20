<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class cInvoices extends MY_Controller {
               
	function __construct()
	{
		parent::__construct();
		$access = FALSE;
		$this->load->database();
		if(!$this->user) {
			$this->view_data['error'] = "true";
            $this->session->set_flashdata('message', 'error: You have no access to any modules!');
			redirect('login');
		}
        $this->company_id = ($this->sessionArr['company_id']) ? $this->sessionArr['company_id'] : 0;
		
		$this->view_data['submenu'] = array(
				 		$this->lang->line('application_all_invoices') => 'cinvoices',
				 		);	
        $this->settings = Setting::first();
	}	
	function index()
	{
		// $this->view_data['invoices'] = Invoice::find('all',array('conditions' => array('company_id=? AND estimate != ? AND issue_date<=?',$this->company_id,1,date('Y-m-d', time()))));
		if($this->company_id){
			// $options = array('conditions' => array('estimate != ? AND company_id = (?)', 1, $this->company_id));
			// $this->view_data['invoices'] = Invoice::find('all', $options);
			$this->view_data['invoices'] = Invoice::find_by_sql('SELECT inv.*, p.name AS project_name FROM invoices AS inv JOIN projects AS p ON p.id = inv.project_id WHERE inv.company_id='.$this->company_id.' AND inv.estimate != 1 AND inv.issue_date <= "'.date('Y-m-d', time()).'"');
		}else{
			$this->view_data['invoices'] = (object) array();
		}
		$this->content_view = 'invoices/client_views/all';
	}

	function view($id = FALSE)
	{
		$company_detail = CompanyDetails::find('all', array( 'conditions' => array('company_id=?',$this->company_id) ));
		$this->view_data['company_detail'] = $company_detail[0];
		
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
		
		if($this->view_data['invoice']->company_id != $this->company_id){redirect('cinvoices');}
		
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
	    } else if(isset($company_detail)) {
			$tax_value = $company_detail[0]->tax;
		} else {
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

		if($this->view_data['invoice']->company_id != $this->company_id){ redirect('cinvoices');}
		$this->content_view = 'invoices/client_views/view';
	}
    
    function pullpropay($id = FALSE)
    {
        if($_POST)
        {
            $certString     = $this->settings->propay_certstring; //*
            $termID         = $this->settings->propay_termid;
            $statusCode     = "";
            $response       = "";
            $accountTo      = $_POST['receiver_account_number'];
            $accountFrom    = $_POST['sender_account_number']; //32247953  31883389  receiver account number
            $amount         = $_POST['amount'];
            $amount_dollar  = $_POST['amount'];

            $invoice_id='';
            if($id)
            {
                $invoice_id=$id;
            }
            $amount*=100; // convert dollar to cents
            $envelope       = '<?xml version="1.0"?>
                <!DOCTYPE Request.dtd>
                <XMLRequest>
                <certStr>'.$certString.'</certStr>
                <class>partner</class>
                    <XMLTrans>
                        <transType>11</transType>
                        <amount>'.$amount.'</amount>
                        <accountNum>'.$accountFrom.'</accountNum>
                        <recAccntNum>'.$accountTo.'</recAccntNum>
                        <allowPending></allowPending>
                        <comment1>test</comment1>
                    </XMLTrans>
                </XMLRequest>';

            $api_response = $this->Submit_Request($envelope);
            $result = simplexml_load_string($api_response); 
            
            if ( isset($result->XMLTrans->status) ) {

                $status = $result->XMLTrans->status; // status code return from api
                $statusCode = $status;

                if ( $status != '00' ) {

                    $status = '_'.$status; 
                    $status = $response_status->status->$status;
                    $response = "Request incomplete";
                    $response .= "<br/>Transaction Status: ". $status;
                    $response .= "<br/>Status Code: ". $statusCode; 
                    
                    $response_to_json = json_encode($result->XMLTrans);                
                    $resultData = json_decode($response_to_json,TRUE);
                    
                    if( $resultData['status'] == '44' || $resultData['status'] == '63' ) {
                        $this->session->set_flashdata('message', 'error:Invalid amount or insufficient funds in your account to proceed.');
                    }
                    else
                    {
                        $this->session->set_flashdata('message', 'error:Your transaction is not completed successfully. Please try again.');
                    }
                    redirect('cinvoices/view/'.$invoice_id);
                
                } else {

                    $tranType = $result->XMLTrans->transType;
                    $accountNum = $result->XMLTrans->accountNum;
                    $transNum = $result->XMLTrans->transNum;

                    $status = '_'.$status;          
                    $status = $response_status->status->$status;
                    $response_to_json = json_encode($result->XMLTrans);                
                    $resultData = json_decode($response_to_json,TRUE);

                    if( $resultData['status'] == '00')
                    {
                        if($invoice_id!='')
                        {
                            $itemvalues = array(
                                'project_invoice_id' => $invoice_id,
                                'payment_amount' => $amount_dollar,
                                'payment_type' =>  1,
                                'created_at' => date('Y-m-d', time()),
                                'updated_at' => date('Y-m-d', time()));
                            Payments::create($itemvalues);
                            $this->session->set_flashdata('message', 'success:Your transaction is completed successfully.');
                        }
                    }
                    redirect('cinvoices/view/'.$invoice_id);
                }
            }
            else
            {
                redirect('cinvoices/view/'.$id);      
            }
            exit;
        }
        else
        {
            $company_detail = CompanyDetails::find('all', array( 'conditions' => array('company_id=?',$this->company_id) ));
            $this->view_data['company_detail'] = $company_detail[0];

            $this->view_data['submenu'] = array( $this->lang->line('application_back') => 'invoices' );

            $this->view_data['invoice'] = Invoice::find_by_sql('SELECT inv.*, p.name AS project_name, p.id AS project_id FROM invoices AS inv JOIN projects AS p ON p.id = inv.project_id WHERE inv.company_id = '.$this->company_id.' AND inv.estimate != 1 AND inv.id = '.$id)[0];
            $this->view_data['invoice']->readonly(false);
            if($this->view_data['invoice']->company_id != $this->company_id){redirect('cinvoices');}

            if($this->view_data['invoice']->company_id != $this->company_id){ redirect('cinvoices');}
            $this->view_data['form_action'] = base_url().'cinvoices/pullpropay/'.$id;
            $this->content_view = 'invoices/client_views/pullpropay';
        }
    }
    
    
    function creditcardpay($id = FALSE)
    {
            $company_detail = CompanyDetails::find('all', array( 'conditions' => array('company_id=?',$this->company_id) ));
            $this->view_data['company_detail'] = $company_detail[0];

            $this->view_data['submenu'] = array( $this->lang->line('application_back') => 'invoices' );

            $this->view_data['invoice'] = Invoice::find_by_sql('SELECT inv.*, p.name AS project_name, p.id AS project_id FROM invoices AS inv JOIN projects AS p ON p.id = inv.project_id WHERE inv.company_id = '.$this->company_id.' AND inv.estimate != 1 AND inv.id = '.$id)[0];
            $this->view_data['invoice']->readonly(false);
            if($this->view_data['invoice']->company_id != $this->company_id){redirect('cinvoices');}

            if($this->view_data['invoice']->company_id != $this->company_id){ redirect('cinvoices');}
            //$this->view_data['form_action'] = 'https://protectpaytest.propay.com/pmi/spr.aspx';
            $this->view_data['form_action'] = 'https://protectpay.propay.com/pmi/spr.aspx';
            $this->content_view = 'invoices/client_views/creditcardpay';
    }
    
    function download($id = FALSE){
		$this->load->helper(array('dompdf', 'file')); 
		$this->load->library('parser');
		$data["invoice"] = Invoice::find($id); 
		$data['items'] = InvoiceHasItem::find('all',array('conditions' => array('invoice_id=?',$id)));
		if($data['invoice']->company_id != $this->company_id){ redirect('cinvoices');}
		$data["core_settings"] = Setting::first(); 
		
		$project = Project::find($data["invoice"]->project_id); 
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
		
		$due_date = date($data["core_settings"]->date_format, human_to_unix($data["invoice"]->due_date.' 00:00:00'));  
		$parse_data = array(
								'due_date' => $due_date,
								'invoice_id' => $data["core_settings"]->invoice_prefix.$data["invoice"]->reference,
								'client_link' => $data["core_settings"]->domain,
								'company' => $data["invoice"]->company->name,
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
		$this->content_view = 'invoices/client_views/_banktransfer';
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
				$this->view_data['form_action'] = 'cinvoices/twocheckout';
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
			$this->view_data['form_action'] = 'cinvoices/stripepay';
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
			$this->view_data['form_action'] = 'cinvoices/authorizenet';
			$this->content_view = 'invoices/_authorizenet';
		}



	}

	function Submit_Request($envelope)
    {
        $header = array(
        "Content-type:text/xml; charset=\"utf-8\"",
        "Accept: text/xml"
        );
        $MSAPI_Call = curl_init();
        //curl_setopt($MSAPI_Call, CURLOPT_URL, 'https://xmltest.propay.com/api/propayapi.aspx');
        curl_setopt($MSAPI_Call, CURLOPT_URL, 'https://epay.propay.com/api/propayapi.aspx'); 
        curl_setopt($MSAPI_Call, CURLOPT_TIMEOUT, 30);
        curl_setopt($MSAPI_Call, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($MSAPI_Call, CURLOPT_POST, true);
        curl_setopt($MSAPI_Call, CURLOPT_POSTFIELDS, $envelope);
        curl_setopt($MSAPI_Call, CURLOPT_HTTPHEADER, $header);
        curl_setopt($MSAPI_Call, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($MSAPI_Call, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($MSAPI_Call, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        $response = curl_exec($MSAPI_Call);
        $err = curl_error($MSAPI_Call);
        curl_close($MSAPI_Call); 
        return $response;
    }
    
    
    
    /*credit card payment [start]*/
    function register_response($res)
    {
        $responseCipher = $_REQUEST['ResponseCipher'];
        $tempToken='';
        
        if ( $this->session->userdata('tempToken') )
        {
            $tempToken=$this->session->userdata('tempToken');
            $response = $this->decryptResponseCipher($tempToken,$responseCipher);
            parse_str($response,$parseArray);
            
            if (array_key_exists('ProcessResultResultCode', $parseArray)) 
            {
                if($parseArray['ProcessResultResultCode']=='00')
                {
                    $itemvalues = array(
                        'project_invoice_id' => $this->session->userdata('InvoiceID'),
                        'payment_amount' => $this->session->userdata('amount'),
                        'payment_type' =>  1,
                        'created_at' => date('Y-m-d', time()),
                        'updated_at' => date('Y-m-d', time()));
                    Payments::create($itemvalues);
                    $this->session->set_flashdata('message', 'success:Your transaction is completed successfully.');
                }            
            }
            elseif(array_key_exists('ProcErrCode', $parseArray))
            {
                if($parseArray['ProcErrCode']=='14')
                {
                    $this->session->set_flashdata('message', 'error:Your transaction is not completed successfully. The transaction has invalid card number.');
                } 
                elseif($parseArray['ProcErrCode']=='51')
                {
                    $this->session->set_flashdata('message', 'error:Your transaction is not completed successfully. This is because of Insufficient funds/over credit limit.');
                }
                elseif($parseArray['ProcErrCode']=='204')
                {
                    $this->session->set_flashdata('message', 'error:Your transaction is not completed successfully. This is because of your propay account expiry.');
                } 
                elseif($parseArray['ProcErrCode']=='301')
                {
                    $this->session->set_flashdata('message', 'error:Your transaction is not completed successfully. This is because of invalid cipher setting.');
                } 
                else
                {
                    $this->session->set_flashdata('message', 'error:Your transaction is not completed successfully. Please try again.');
                }
            }
            elseif(array_key_exists('ErrCode', $parseArray))
            {
                if($parseArray['ErrCode']=='301')
                {
                    $this->session->set_flashdata('message', 'error:Your transaction is not completed successfully. This is because of invalid cipher setting.');
                } 
                else
                {
                    $this->session->set_flashdata('message', 'error:Your transaction is not completed successfully. Please try again.');
                }
            }
            elseif(array_key_exists('StoreErrCode', $parseArray))
            {
                if($parseArray['StoreErrCode']=='308')
                {
                    $this->session->set_flashdata('message', 'error:'.$parseArray['StoreErrMsg']);
                } 
                else
                {
                    $this->session->set_flashdata('message', 'error:Your transaction is not completed successfully. Please try again.');
                }
            }
            else
            {
                $this->session->set_flashdata('message', 'error:Your transaction is not completed successfully. Please try again.');
            }
            redirect('cinvoices/view/'.$this->session->userdata('InvoiceID'));
        }
        exit;
    }
    
    function getsettoken()
    {
        $username = '';
        $user = User::find_by_id($this->sessionArr['user_id']);
        $users_id = $user->id;
        $users_email = $user->email;    
        $username = $user->firstname;
        
        $payerID = '';
        
        $tempTokenData = $this->getTempToken($payerID,$username);
        
        //$tempTokenData = json_decode($tempTokenResponse);
        
        $Spera_accounts_default = Spera_accounts::find('all',array('conditions' => array('user_id=?',$_POST['userID'] )));
        
        if(!empty($Spera_accounts_default)){
            $profileID = $Spera_accounts_default[0]->merchant_profile_id;
        }        
        if( $tempTokenData['payerID'] )
        {
            $payerID = $tempTokenData['payerID'];
            $tempToken = $tempTokenData['tempToken'];
            $credentiaID = $tempTokenData['credentiaID'];
            $this->session->set_userdata('tempToken',$tempToken);
            
            $reqURL = base_url().'cinvoices/register_response';
            $keyValuePair = 
            "AuthToken=".$tempToken."&PayerID=".$payerID."&CurrencyCode=USD&ProcessMethod=Capture&PaymentMethodStorageOption=OnSuccess&InvoiceNumber=Invoice123&Comment1=comment1&Comment2=comment2&echo=echotest&ReturnURL=".$reqURL."&ProfileId=".$profileID."&PaymentProcessType=CreditCard&StandardEntryClassCode=&DisplayMessage=True&Protected=False";
            
            $settingsCipher = $this->spiEncrypt($tempToken, $keyValuePair);
            
            $amount = $_POST['amount'];

            $amount = number_format((float)$amount, 2, '.', '');
            $result['staus'] = true;
            $result['amount'] = $amount;
            $result['payerID'] = $payerID;
            $result['tempToken'] = $tempToken;
            $result['credentiaID'] = $credentiaID;
            $result['settingsCipher'] = $settingsCipher;
            
            $CI =& get_instance();
            $CI->session->set_userdata('amount', $amount);
            $CI->session->set_userdata('InvoiceID', $_POST['InvoiceID']);
            
            echo json_encode($result);
        }
        else
        {
            $result['payerID'] = '';
            $this->session->set_flashdata('message', 'error:'.$tempTokenResponse);
            echo json_encode($result);
        }
        die();
    }

    function getTempToken($payerID,$payerName)
    {
        $envelope=
        '<?xml version="1.0"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:con="http://propay.com/SPS/contracts" xmlns:typ="http://propay.com/SPS/types">
        <soapenv:Header/>
        <soapenv:Body>
        <con:GetTempToken>
         <con:tempTokenRequest>
           <typ:Identification>
             <typ:AuthenticationToken>' . $this->settings->propay_auth_token .'</typ:AuthenticationToken>
             <typ:BillerAccountId>' . $this->settings->propay_biller_id . '</typ:BillerAccountId>
           </typ:Identification>
           <typ:PayerInfo>
             <typ:Id></typ:Id>
             <typ:Name>' . $payerName .'</typ:Name>
           </typ:PayerInfo>
           <typ:TokenProperties>
             <typ:DurationSeconds>6000</typ:DurationSeconds>
           </typ:TokenProperties>
         </con:tempTokenRequest>
        </con:GetTempToken>
        </soapenv:Body>
        </soapenv:Envelope>';

        $SOAP_Action = "GetTempToken"; 
        
        return $this->makeCurlRequest($envelope, $SOAP_Action);
    }
    
    function makeCurlRequest($envelope, $SOAP_Action)
    {
        /* The HTTP header must include the SOAPAction */ 
        $header = array(
        "Content-type:text/xml; charset=\"utf-8\"",
        "Accept: text/xml",
        "SOAPAction: http://propay.com/SPS/contracts/SPSService/".$SOAP_Action
        );
        $soap_do = curl_init();
        /*Change the following URL to point to production instead of integration */
        //curl_setopt($soap_do, CURLOPT_URL, "https://protectpaytest.propay.com/API/SPS.svc");
        curl_setopt($soap_do, CURLOPT_URL, "https://api.propay.com/protectpay/sps.svc");
        curl_setopt($soap_do, CURLOPT_TIMEOUT, 30);
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($soap_do, CURLOPT_POST, true);
        curl_setopt($soap_do, CURLOPT_POSTFIELDS, $envelope);
        curl_setopt($soap_do, CURLOPT_HTTPHEADER, $header);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($soap_do, CURLOPT_HTTPAUTH, CURLAUTH_ANY);

        $response = curl_exec($soap_do);
        $err = curl_error($soap_do);
        curl_close($soap_do);
        /*Call Parse Function for the XML response*/

        return $this->Parse_Results($response);
    }
    
    function Parse_Results($api_response)
    {
        $doc = new DOMDocument();
        $doc->loadXML($api_response);
        //Pretty Print response
        $api_result = new DOMDocument('1.0');
        $api_result->preserveWhiteSpace = false;
        $api_result->formatOutput = true;
        $api_result->loadXML($api_response);
        
        if(isset($doc->getElementsByTagName('ResultCode')->item(0)->nodeValue))
        {
            $result_code = $doc->getElementsByTagName('ResultCode')->item(0)->nodeValue;
            $result_value = $doc->getElementsByTagName('ResultValue')->item(0)->nodeValue;
            $result = "";
            //$result = "Request Results:";
            //$result .= "\nResult Code: " . $result_code;
            //$result .= "\nResult Value: " . $result_value;
            if($result_code != '00' || $result_value == "FAILURE")
            {
                $result_message = $doc->getElementsByTagName('ResultMessage')->item(0)->nodeValue;
            }
            else
            {
                global $credential_id,$payer_id,$temp_token;
                $credential_id = $doc->getElementsByTagName('CredentialId')->item(0)->nodeValue;
                $payer_id = $doc->getElementsByTagName('PayerId')->item(0)->nodeValue;
                $temp_token = $doc->getElementsByTagName('TempToken')->item(0)->nodeValue;            
                
                $result['payerID'] = $payer_id;
                $result['tempToken'] = $temp_token;
                $result['credentiaID'] = $credential_id;
                return $result;
            }         
        }    
        else
        {
            print_r($api_result->saveXML());
        };    
    } 
    
    
    function spiEncrypt($tempToken, $keyValuePair)
    {
        $key = hash('MD5', utf8_encode($tempToken), true);  //generate an MD5 hash 
        $iv = $key;
        $settingsCipher = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $this->padData($keyValuePair), MCRYPT_MODE_CBC, $iv);
        return base64_encode($settingsCipher);
    }
    
    function padData($data)
    {
        $padding = 16 - (strlen($data) % 16);
        $data .= str_repeat(chr($padding), $padding); 
        return $data;
    }
    
    function unPadData($data)
    {
        $padding = ord($data[strlen($data) - 1]);
        return substr($data, 0, -$padding);
    }
    
    function decryptResponseCipher($tempToken,$responseCipher)
    {   
        $key = hash('MD5', utf8_encode($tempToken), true);
        $iv = $key;
        $spiResponse = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode($responseCipher), MCRYPT_MODE_CBC, $iv);
        return $this->unPadData($spiResponse);      
    }
    /*credit card payment [end]*/
	
}

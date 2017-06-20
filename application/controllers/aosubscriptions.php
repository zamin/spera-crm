<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Aosubscriptions extends MY_Controller {
               
	function __construct()
	{
		parent::__construct();
		
        if(!$this->user){
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
        $this->settings = Setting::first();
	}	
	function index()
	{
		$subscriptions = Subscription::find_by_sql('SELECT u.*,pus.start_date,pus.end_date, pd.name
                                                    FROM users u
                                                    INNER JOIN propay_user_subscription as pus on pus.user_id = u.id
                                                    INNER JOIN package as pd on pd.id = pus.package_id
                                                    WHERE u.id = '.$this->sessionArr['user_id'].'
                                                    order by pus.id desc ');
        if(!empty($subscriptions))
        {   
            $subscription_all =array();
            $i=0;
            foreach($subscriptions as $key =>$value)
            {
                $subscription_all[$i]['type']=$value->name;
                $subscription_all[$i]['name']=$value->firstname.' '.$value->lastname;
                $subscription_all[$i]['email']=$value->email;
                $subscription_all[$i]['start_date']=$value->start_date;
                $subscription_all[$i]['end_date']=$value->end_date;
                $i++;
            }
        }
		$this->view_data['propay_data'] = PropayData::find('all',array('conditions' => array('user_id=?',$this->sessionArr['user_id'] )));
        $this->view_data['subscriptions']=$subscription_all;
		$this->content_view = 'subscriptions/ao_subscriptions/all';
		/* $days_in_this_month = days_in_month(date('m'), date('Y'));
		$lastday_in_month =  strtotime(date('Y')."-".date('m')."-".$days_in_this_month);
		$firstday_in_month =  strtotime(date('Y')."-".date('m')."-01");

		$this->view_data['subscription_paid_this_month'] = Subscription::count(array('conditions' => 'UNIX_TIMESTAMP(`paid_date`) <= '.$lastday_in_month.' and UNIX_TIMESTAMP(`paid_date`) >= '.$firstday_in_month.''));
		$this->view_data['subscription_due_this_month'] = Subscription::count(array('conditions' => 'UNIX_TIMESTAMP(`due_date`) <= '.$lastday_in_month.' and UNIX_TIMESTAMP(`due_date`) >= '.$firstday_in_month.''));
		
		//statistic
		$now = time();
		$beginning_of_week = strtotime('last Monday', $now); // BEGINNING of the week
		$end_of_week = strtotime('next Sunday', $now) + 86400; // END of the last day of the week
		$this->view_data['subscription_due_this_month_graph'] = Subscription::find_by_sql('select count(id) AS "amount", DATE_FORMAT(`due_date`, "%w") AS "date_day", DATE_FORMAT(`due_date`, "%Y-%m-%d") AS "date_formatted" from invoices where UNIX_TIMESTAMP(`due_date`) >= "'.$beginning_of_week.'" AND UNIX_TIMESTAMP(`due_date`) <= "'.$end_of_week.'" ');
		$this->view_data['subscription_paid_this_month_graph'] = Subscription::find_by_sql('select count(id) AS "amount", DATE_FORMAT(`paid_date`, "%w") AS "date_day", DATE_FORMAT(`paid_date`, "%Y-%m-%d") AS "date_formatted" from invoices where UNIX_TIMESTAMP(`paid_date`) >= "'.$beginning_of_week.'" AND UNIX_TIMESTAMP(`paid_date`) <= "'.$end_of_week.'" ');
 */
	}
	function filter($condition = FALSE)
	{
		$company_select = "";
		$comp_array = "";
		if($this->user->admin != 1){
				$comp_array = array();
				$thisUserHasNoCompanies = (array) $this->user->companies;
					if(!empty($thisUserHasNoCompanies)){
					foreach ($this->user->companies as $value) {
						array_push($comp_array, $value->id);
					}
					$company_select = ' AND company_id in (?)';
					$this->view_data['subscriptions'] = Subscription::find('all', array('conditions' => array('status = ?'.$company_select, ucfirst($condition),$comp_array )));
					if($condition == "ended"){
						$this->view_data['subscriptions'] = Subscription::find('all', array('conditions' => array('status = ? AND end_date < ?'.$company_select, 'Active',date('Y-m-d'),$comp_array)));	
					}
				}else{
					$this->view_data['subscriptions'] = (object) array();
				}
			}else{

				$this->view_data['subscriptions'] = Subscription::find('all', array('conditions' => array('status = ?', ucfirst($condition) )));
				if($condition == "ended"){
					$this->view_data['subscriptions'] = Subscription::find('all', array('conditions' => array('status = ? AND end_date < ?', 'Active',date('Y-m-d'))));	
				}
			}
		
		$this->content_view = 'subscriptions/all';
	}
	function create()
	{	
		//$this->view_data['propay_data'] = PropayData::find('all',array('conditions' => array('user_id=?',$this->sessionArr['user_id'] )));
        $this->view_data['packages'] = Package::find('all');
        $this->theme_view = 'modal';
		
		$check_package_query="Select * from propay_user_subscription where user_id='".$this->sessionArr['user_id']."' order by id desc";
        $check_package_current_date=$this->db->query($check_package_query)->row_array();
        if(!empty($check_package_current_date))
        {
            $this->view_data['package_id']=$check_package_current_date['package_id'];
            $this->view_data['package_type']=$check_package_current_date['type'];
            $this->view_data['get_package_data']=Package::find_by_id($check_package_current_date['package_id']);
        }
		
        // $this->view_data['title'] = $this->lang->line('application_create_subscription');
        $this->view_data['title'] = $this->lang->line('application_new_card_subscription');
        $this->view_data['form_action'] = 'https://protectpay.propay.com/pmi/spr.aspx';
        $this->content_view = 'subscriptions/ao_subscriptions/_subscription';
	}
	
	function existing()
	{	
		$this->view_data['propay_data'] = PropayData::find('all',array('conditions' => array('user_id=?',$this->sessionArr['user_id'] )));
        $this->view_data['packages'] = Package::find('all');
        
		$check_package_query="Select * from propay_user_subscription where user_id='".$this->sessionArr['user_id']."' order by id desc";
        $check_package_current_date=$this->db->query($check_package_query)->row_array();
        if(!empty($check_package_current_date))
        {
            $this->view_data['package_id']=$check_package_current_date['package_id'];
            $this->view_data['package_type']=$check_package_current_date['type'];
            $this->view_data['get_package_data']=Package::find_by_id($check_package_current_date['package_id']);
        }
		
        $this->theme_view = 'modal';
        // $this->view_data['title'] = $this->lang->line('application_create_subscription');
        $this->view_data['title'] = $this->lang->line('application_existing_card_subscription');
        $this->view_data['form_action'] = 'https://protectpay.propay.com/pmi/spr.aspx';
        $this->content_view = 'subscriptions/ao_subscriptions/_existing';
	}	
    
    function postcreate()
    {    
        $propay_data = $this->db->query('SELECT * FROM propay_data WHERE id = '.$_POST['propay_data_id'])->result_array();
        $payerID = $propay_data[0]['payer_account_id'];
        $paymentMethodID = $propay_data[0]['payment_method_id'];
        
        $user_subscriptions = $this->db->query('SELECT * FROM propay_user_subscription WHERE status=0 AND user_id = '.$this->sessionArr['user_id'].' ORDER BY id desc limit 1')->result_array();
        
		$package_explode=explode('-',$_POST['package_id']);
		$package_id = substr($package_explode[1], -1);
        $package_type = $package_explode[0];
        
        $package_dataVal = $this->db->query('SELECT * FROM package WHERE id = '.$package_id)->result_array();
        $trial_version = $package_dataVal[0]['trial_version'];        
        $discount = $package_dataVal[0]['discount'];        
        $duration = $package_dataVal[0]['duration'];        
        $amount = $package_dataVal[0]['amount'];
        
        /*if($discount > 0){
            $amount = $amount - (($discount / 100) * $amount);
        }*/
               
        //$amount = ($amount*100);
		$now = strtotime(date('Y-m-d'));
        $start_date = date('Y-m-d', time());  
        $status=0;
        if(!empty($user_subscriptions)){
            $status=1;
            $pkg_last_date = $user_subscriptions[0]['end_date'];
            $pkg_last_date = strtotime($pkg_last_date." +1 day");              
            $end_date = $user_subscriptions[0]['end_date'];
            $start_date = date('Y-m-d', $pkg_last_date);
            if (strtotime($end_date) <= $now) {
                $start_date = date('Y-m-d', time());
            }                            
        }
        if($package_type=='monthly')
        {
            $amount = ($amount*100); 
            $end_date = strtotime($start_date." +".$duration." month");
            $end_date = date('Y-m-d', $end_date);
        }
        else
        {
            if($discount > 0){
                $yearly_amount=round(($amount)*12);
                $yd_amount=(($yearly_amount/100)*$discount);
                $amount=$yearly_amount-$yd_amount;
            }
            $amount = ($amount*100);
            //$amount = ($amount*12);
            $end_date = strtotime($start_date." +".$duration." year");
            $end_date = date('Y-m-d', $end_date);
             
        }
		
        //$end_date = strtotime($start_date." +".$duration." month");
        //$end_date = date('Y-m-d', $end_date);
        
        
        $processPaymentMethoddata = array(
            "package_id"           =>  $package_id,
            "Amount"            =>  $amount,
			"package_type"      =>  $package_type,
            "CurrencyCode"      =>  $this->settings->propay_currency,
            "PayerAccountId"    =>  $payerID,
            "PaymentMethodID"   =>  $paymentMethodID,
			"status"   =>  $status,
            "start_date"   =>  $start_date,
            "end_date"   =>  $end_date,
            "propay_data_last_inserted_ID"   =>  $_POST['propay_data_id'],
            "user_id"   =>  $this->sessionArr['user_id']
        );
        
        $args = array(
            $this->settings->propay_auth_token,
            $this->settings->propay_biller_id,
            $this->settings->propay_profile_id,
            $payerID,
            $paymentMethodID,
            $amount,
            $this->settings->propay_currency,
            "test111",                                        //Comment
            "test111",                                        //Invoice Number
            "123"
        );        
        
        $processPaymentMethod = $this->process_payment_method($args,$processPaymentMethoddata);
        
        if($processPaymentMethod == "success"){
            $this->session->set_flashdata('message', 'success:Thanks for subscription! Please check your email.');
        } else {
            $this->session->set_flashdata('message', 'error:Your transaction is not completed successfully. Please try again.');
        }
        //redirect('aosubscriptions/');        
        redirect('aosettings/allsubscriptions/'); 
        die();
    }
	function update($id = FALSE, $getview = FALSE)
	{	
		if($_POST){
			unset($_POST['send']);
			unset($_POST['files']);
			unset($_POST['_wysihtml5_mode']);
			$id = $_POST['id'];
			$subscription = Subscription::find($id);
			if($_POST['issue_date'] != $subscription->issue_date){
				$_POST['next_payment'] = $_POST['issue_date']; 
			}
			
			$view = FALSE;
			if(isset($_POST['view'])){$view = $_POST['view']; }
			unset($_POST['view']);
			if($_POST['status'] == "Paid"){ $_POST['paid_date'] = date('Y-m-d', time());}
			
			$subscription->update_attributes($_POST);
       		if(!$subscription){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_save_subscription_error'));}
       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_save_subscription_success'));}
			if($view == 'true'){redirect('subscriptions/view/'.$id);}else{redirect('subscriptions');}
			
		}else
		{
			$this->view_data['subscription'] = Subscription::find($id);
			$this->view_data['companies'] = Company::find('all',array('conditions' => array('inactive=?','0')));
			if($getview == "view"){$this->view_data['view'] = "true";}
			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_edit_subscription');
			$this->view_data['form_action'] = 'subscriptions/update';
			$this->content_view = 'subscriptions/_subscription';
		}	
	}	
	function view($id = FALSE)
	{
		$subscriptions = Subscription::find_by_sql('SELECT u.*, (select id from propay_user_subscription as pus where pus.user_id = u.users_id order by pus.id DESC limit 1) as propay_user_subscription_id,
                                                                (select start_date from propay_user_subscription as pus where pus.user_id = u.users_id order by pus.id DESC limit 1) as propay_user_subscription_start_date,
                                                                (select end_date from propay_user_subscription as pus where pus.user_id = u.users_id order by pus.id DESC limit 1) as propay_user_subscription_end_date,
                                                                (select payment_detail_id from propay_user_subscription as pus where pus.user_id = u.users_id order by pus.id DESC limit 1) as propay_user_subscription_payment_detail_id
                                                     FROM users_x u WHERE u.users_id = '.$id);
        if(!empty($subscriptions))
        {
            $subscription_all =array();
            $i=0;
            foreach($subscriptions as $key =>$value)
            {
                $subscription_all[$i]['id']=$value->users_id;
                $subscription_all[$i]['name']=$value->name;
                $subscription_all[$i]['email']=$value->email;
                $subscription_all[$i]['status']=$value->user_status;
                $subscription_all[$i]['start_date']=$value->propay_user_subscription_start_date;
                $subscription_all[$i]['end_date']=$value->propay_user_subscription_end_date;
                $subscription_all[$i]['next_payment']=date('Y-m-d',strtotime($value->propay_user_subscription_end_date.' +1 day'));
                $i++;
            }
        }
        $this->view_data['subscriptions']=$subscription_all;
        
        $this->view_data['subscriptionsInvoice'] = Subscription::find_by_sql('SELECT pus.*,ppd.* FROM 
                                                                             propay_user_subscription AS pus
                                                                             INNER JOIN propay_payment_detail as ppd ON ppd.user_id = pus.user_id AND ppd.id = pus.payment_detail_id 
                                                                             WHERE ppd.user_id = '.$id.'
                                                                             ORDER BY pus.id');
        
		$this->content_view = 'subscriptions/view';
	}
	function create_invoice($id = FALSE)
	{	
		$subscription = Subscription::find($id);
		$invoice = Invoice::last();
		$invoice_reference = Setting::first();
		if($subscription){
			$_POST['subscription_id'] = $subscription->id;
			$_POST['company_id'] = $subscription->company_id;
			if($subscription->subscribed != 0){$_POST['status'] = "Paid";}else{$_POST['status'] = "Open";}
			$_POST['currency'] = $subscription->currency;
			$_POST['issue_date'] = $subscription->next_payment;
			$_POST['due_date'] = date('Y-m-d', strtotime('+14 day', strtotime ($subscription->next_payment)));
			$_POST['currency'] = $subscription->currency;
			$_POST['terms'] = $subscription->terms;
			$_POST['discount'] = $subscription->discount;
			$_POST['tax'] = $subscription->tax;
			$_POST['second_tax'] = $subscription->second_tax;
			$_POST['reference'] = $invoice_reference->invoice_reference;
			$invoice = Invoice::create($_POST);
			$invoiceid = Invoice::last();
			$items = SubscriptionHasItem::find('all',array('conditions' => array('subscription_id=?',$id)));
			foreach ($items as $value):
				$itemvalues = array(
					'invoice_id' => $invoiceid->id,
					'item_id' => $value->item_id,
					'amount' =>  $value->amount,
					'description' => $value->description,
					'value' => $value->value,
					'name' => $value->name,
					'type' => $value->type,
					);
				InvoiceHasItem::create($itemvalues);
			endforeach;
			$invoice_reference->update_attributes(array('invoice_reference' => $invoice_reference->invoice_reference+1));
       		if(!$invoice){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_create_invoice_error'));}
       		else{	$subscription->next_payment = date('Y-m-d', strtotime($subscription->frequency, strtotime ($subscription->next_payment)));
       				$subscription->save();
       				$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_create_invoice_success'));}
			redirect('subscriptions/view/'.$id);
		}
	}	
	function delete($id = FALSE)
	{	
		$subscription = Subscription::find($id);
		$subscription->delete();
		$this->content_view = 'subscriptions/all';
		if(!$subscription){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_delete_subscription_error'));}
       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_delete_subscription_success'));}
			redirect('subscriptions');
	}
	function sendsubscription($id = FALSE){
		$this->load->helper(array('dompdf', 'file'));
			$this->load->library('parser');

			$data["subscription"] = Subscription::find($id); 
			$data['items'] = SubscriptionHasItem::find('all',array('conditions' => array('subscription_id=?',$id)));
     		$data["core_settings"] = Setting::first();

  			$issue_date = date($data["core_settings"]->date_format, human_to_unix($data["subscription"]->issue_date.' 00:00:00')); 
  			//Set parse values
  			$parse_data = array(
            					'client_contact' => $data["subscription"]->company->client->firstname.' '.$data["subscription"]->company->client->lastname,
            					'issue_date' => $issue_date,
            					'subscription_id' => $data["core_settings"]->subscription_prefix.$data["subscription"]->reference,
            					'client_link' => $data["core_settings"]->domain,
            					'company' => $data["core_settings"]->company,
            					'logo' => '<img src="'.base_url().''.$data["core_settings"]->logo.'" alt="'.$data["core_settings"]->company.'"/>',
            					'invoice_logo' => '<img src="'.base_url().''.$data["core_settings"]->invoice_logo.'" alt="'.$data["core_settings"]->company.'"/>'
            					);
            //email
     		$subject = $this->parser->parse_string($data["core_settings"]->subscription_mail_subject, $parse_data);
			//$this->email->from($data["core_settings"]->email, $data["core_settings"]->company);
			$this->email->from($this->settings->from_email_id,$this->settings->from_email_name);
			if(!isset($data["subscription"]->company->client->email)){
				$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_send_subscription_error').' No client email!');
				redirect('subscriptions/view/'.$id);

			}
			$this->email->to($data["subscription"]->company->client->email); 
			$this->email->subject($subject); 
			
  			$email_subscription = read_file('./application/views/'.$data["core_settings"]->template.'/templates/email_subscription.html');
  			$message = $this->parser->parse_string($email_subscription, $parse_data);
			$this->email->message($message);			
			if($this->email->send()){$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_send_subscription_success'));
			//$data["subscription"]->update_attributes(array('status' => 'Sent', 'sent_date' => date("Y-m-d")));
			log_message('error', 'Subscription #'.$data["core_settings"]->subscription_prefix.$data["subscription"]->reference.' has been send to '.$data["subscription"]->company->client->email);
			}
       		else{$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_send_subscription_error'));
       		log_message('error', 'ERROR: Subscription #'.$data["core_settings"]->subscription_prefix.$data["subscription"]->reference.' has not been send to '.$data["subscription"]->company->client->email.'. Please check your servers email settings.');
       		}
			redirect('subscriptions/view/'.$id);
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
					redirect('subscriptions/view/'.$_POST['subscription_id']);

				}else{
					$itemvalue = Item::find($_POST['item_id']);
					$_POST['name'] = $itemvalue->name;
					$_POST['type'] = $itemvalue->type;
					$_POST['value'] = $itemvalue->value;
				}
			}

			$item = SubscriptionHasItem::create($_POST);
       		if(!$item){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_add_item_error'));}
       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_add_item_success'));}
			redirect('subscriptions/view/'.$_POST['subscription_id']);
			
		}else
		{
			$this->view_data['subscription'] = Subscription::find($id);
			$this->view_data['items'] = Item::find('all',array('conditions' => array('inactive=?','0')));
			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_add_item');
			$this->view_data['form_action'] = 'subscriptions/item';
			$this->content_view = 'subscriptions/_item';
		}	
	}	
	function item_update($id = FALSE)
	{	
		if($_POST){
			unset($_POST['send']);
			$_POST = array_map('htmlspecialchars', $_POST);
			$item = SubscriptionHasItem::find($_POST['id']);
			$item = $item->update_attributes($_POST);
       		if(!$item){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_save_item_error'));}
       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_save_item_success'));}
			redirect('subscriptions/view/'.$_POST['subscription_id']);
			
		}else
		{
			$this->view_data['subscription_has_items'] = SubscriptionHasItem::find($id);
			$this->theme_view = 'modal';
			$this->view_data['title'] = $this->lang->line('application_edit_item');
			$this->view_data['form_action'] = 'subscriptions/item_update';
			$this->content_view = 'subscriptions/_item';
		}	
	}	
	function item_delete($id = FALSE, $subscription_id = FALSE)
	{	
		$item = SubscriptionHasItem::find($id);
		$item->delete();
		$this->content_view = 'subscriptions/view';
		if(!$item){$this->session->set_flashdata('message', 'error:'.$this->lang->line('messages_delete_item_error'));}
       		else{$this->session->set_flashdata('message', 'success:'.$this->lang->line('messages_delete_item_success'));}
			redirect('subscriptions/view/'.$subscription_id);
	}
    
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
                    $this->db->query('UPDATE propay_data SET set_default="0" WHERE user_id = '.$this->sessionArr['user_id']);
                    $propay_data = array('payer_account_id' => $parseArray['PayerID'], 
                                        'payment_method_id' => $parseArray['PaymentMethodId'],
                                        'payment_method_type' => "",
                                        'cc_number' => $parseArray['ObfuscatedAccountNumber'],
                                        'set_default' => 1,
                                        'removed' => 0,
                                        'user_id' => $this->sessionArr['user_id'],
                                        'updated_at' => date('Y-m-d H:i:s'),
                                        'created_at' => date('Y-m-d H:i:s'));
                                        
                    $insert_data = PropayData::create($propay_data);
                    $propay_data_ID = $insert_data->id;
                    
                    $propay_payment_detail = array('user_id' => $this->sessionArr['user_id'], 
                                                    'propay_data_id' => $propay_data_ID,
                                                    'avscode' => $parseArray['ProcessResultAVSCode'],
                                                    'authorization_code' => $parseArray['ProcessResultAuthorizationCode'],
                                                    'currency_conversion_rate' => "",
                                                    'currency_converted_amount' => $parseArray['Amount'],
                                                    'currency_converted_currency_code' => "",
                                                    'gross_amt' => $parseArray['GrossAmt'],
                                                    'gross_amt_less_net_amt' => $parseArray['GrossAmtLessNetAmt'],
                                                    'net_amt' => $parseArray['NetAmt'],
                                                    'per_trans_fee' => $parseArray['PerTransFee'],
                                                    'rate' => $parseArray['Rate'],
                                                    'result_code' => $parseArray['ProcessResultResultCode'],
                                                    'result_value' => $parseArray['ProcessResult'],
                                                    'result_message' => $parseArray['ProcessResultResultMessage'],
                                                    'transaction_history_id' => $parseArray['ProcessResultTransactionHistoryID'],
                                                    'transaction_id' => $parseArray['ProcessResultTransactionId'],
                                                    'transaction_result' => "",
                                                    'cvv_response_code' => "",
                                                    'updated_at' => date('Y-m-d H:i:s'),
                                                    'created_at' => date('Y-m-d H:i:s'));
                    $insert_data = PropayPaymentDetail::create($propay_payment_detail);
                    $propay_payment_detail_last_inserted_ID = $insert_data->id;    
                    
                    $user_subscriptions = $this->db->query('SELECT * FROM propay_user_subscription WHERE status=0 AND user_id = '.$this->sessionArr['user_id'].' ORDER BY id desc limit 1')->result_array();
                    
                    $package_id = $this->session->userdata('package_id');
					$package_type = $this->session->userdata('package_type');
                    $package_dataVal = $this->db->query('SELECT * FROM package WHERE id = '.$package_id)->result_array();
                    $trial_version = $package_dataVal[0]['trial_version'];        
                    $discount = $package_dataVal[0]['discount'];        
                    $duration = $package_dataVal[0]['duration'];        
                    $amount = $package_dataVal[0]['amount'];        
                    $amount = ($amount*100);
                    $now = strtotime(date('Y-m-d'));

                    $start_date = date('Y-m-d', time());  
                    $status = 0;if(!empty($user_subscriptions)){
                        $status = 1;
                        $pkg_last_date = $user_subscriptions[0]['end_date'];
                        $pkg_last_date = strtotime($pkg_last_date." +1 day");                        
                        $end_date = $user_subscriptions[0]['end_date'];
                        $start_date = date('Y-m-d', $pkg_last_date);
                        if (strtotime($end_date) <= $now) {
                            $start_date = date('Y-m-d', time());
                        }                            
                    }
                    if($package_type=='monthly')
                    {
                        $end_date = strtotime($start_date." +".$duration." month");
                        $end_date = date('Y-m-d', $end_date);
                    }
                    else
                    {
                        $end_date = strtotime($start_date." +".$duration." year");
                        $end_date = date('Y-m-d', $end_date);

                    }
                    //$end_date = strtotime($start_date." +".$duration." month");
                    //$end_date = date('Y-m-d', $end_date);
                    $propay_user_data = array('payment_detail_id' => $propay_payment_detail_last_inserted_ID, 
                                                'package_id' => $package_id,
                                                'user_id' => $this->sessionArr['user_id'],
                                                'status' => $status,
                                                'start_date' => $start_date,
                                                'end_date' => $end_date,
                                                'updated_at' => time(),
                                                'created_at' => time(),
                                                'type'=>$package_type
                        );
                    $insert_data = PropayUserSubscription::create($propay_user_data);
                    $PropayUserSubscriptionID = $insert_data->id;    
                    
                    $this->session->set_flashdata('message', 'success:Thanks for subscription! Please check your email.');
                    
                    $user = User::find_by_id($this->sessionArr['user_id']);
                    
                    $message="Hi ".trim($user->firstname." ".$user->lastname)."<br/><br>
                          <p>Thanks for subscription!</p><br/>
                          Thanks
                          Spera Team
                          ";  
                    //send_subscription_notification($user->email, 'Spera | Thanks for subscription', $message);
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
            //redirect('aosubscriptions/');
			redirect('aosettings/allsubscriptions');
        }
        exit;
    }
    
    function getPackageAmount()
    {
        $sql_package = 'SELECT * from package where id = '.$_REQUEST['subscription'];
        $res_package = User::find_by_sql($sql_package);
        $trial_version = $res_package[0]->trial_version;        
        $discount = $res_package[0]->discount;        
        $duration = $res_package[0]->duration;        
        $amount = $res_package[0]->amount;
        if($_REQUEST['package_type']=='yearly'){
         if($discount > 0){
            $yearly_amount=round(($amount)*12);
            $yd_amount=(($yearly_amount/100)*$discount);
            $amount=$yearly_amount-$yd_amount;
          }
        }
        else
        {
            $amount = $amount;
        }
        $amount = number_format((float)$amount, 2, '.', '');
        $result['amount'] = $amount;
        echo json_encode($result);
        die();
    }
    
    /*credit card payment [start]*/
    function getsettoken()
    {
        $username = '';
        $user = User::find_by_id($this->sessionArr['user_id']);
        $users_id = $user->id;
        $users_email = $user->email;    
        $username = $user->firstname;

        $promo_code = null;
        $promo_code_exists = false;
        $has_promo_code = false;

        if(!empty($_REQUEST['promo_code'])) {
            $promo_code = PromoCodes::find_by_sql("SELECT * FROM `promo_codes` WHERE promo_code = '" . $_REQUEST['promo_code'] . "'");

            $promo_code_exists = !empty($promo_code);
            $has_promo_code = true;

            if ($promo_code_exists)
                $promo_code = $promo_code[0];
        }
        
        $payerID = '';
        $propay_data_list_default = PropayData::find('all',array('conditions' => array('set_default=? AND user_id=?','1',$users_id )));
        
        if(!empty($propay_data_list_default)){
           $payerID = $propay_data_list_default[0]->payer_account_id;
        }
        $tempTokenResponse = $this->Get_Temp_Token($payerID,$username);

        //$tempTokenData = json_decode($tempTokenResponse);
		$tempTokenData = $tempTokenResponse;

        if( $tempTokenData['PayerID'] && (($has_promo_code && $promo_code_exists) || !$has_promo_code) )
        {
            $profileID = $this->settings->propay_profile_id;
            $payerID = $tempTokenData['PayerID'];
            $tempToken = $tempTokenData['TempToken'];
            $credentiaID = $tempTokenData['CredentialID'];
            $this->session->set_userdata('tempToken',$tempToken);
            $this->session->set_userdata('package_id',$_REQUEST['subscription']);
            $this->session->set_userdata('package_type',$_REQUEST['package_type']);
            $reqURL = base_url().'aosubscriptions/register_response';
            
            $keyValuePair = "AuthToken=".$tempToken."&PayerID=".$payerID."&CurrencyCode=".$this->settings->propay_currency."&ProcessMethod=Capture&PaymentMethodStorageOption=OnSuccess&InvoiceNumber=Invoice12345&Comment1=Comment1&Comment2=comment2&echo=echotest&ReturnURL=".$reqURL."&ProfileId=".$profileID."&PaymentProcessType=CreditCard&StandardEntryClassCode=&DisplayMessage=True&Protected=False";
            $settingsCipher = $this->spiEncrypt($tempToken, $keyValuePair);
            
            $sql_package = 'SELECT * from package where id = '.$_REQUEST['subscription'];
            $res_package = User::find_by_sql($sql_package);
                
            $trial_version = $res_package[0]->trial_version;        
            $discount = $res_package[0]->discount;        
            $duration = $res_package[0]->duration;        
            $amount = $res_package[0]->amount;
            
			if($_REQUEST['package_type']=='monthly')
            {
                $amount = $amount; 
            }
            else
            {
                if($discount > 0){
                    $yearly_amount=round(($amount)*12);
                    $yd_amount=(($yearly_amount/100)*$discount);
                    $amount=$yearly_amount-$yd_amount;
                }
                //$amount=($amount*12);
            }

            if($promo_code_exists)
			    $amount -= ($amount * ($promo_code->discount / 100));
            
            $amount = number_format((float)$amount, 2, '.', '');
            $result['staus'] = true;
            $result['amount'] = $amount;
            $result['payerID'] = $payerID;
            $result['tempToken'] = $tempToken;
            $result['credentiaID'] = $credentiaID;
            $result['settingsCipher'] = $settingsCipher;
            
            echo json_encode($result);
        }
        else
        {
            $error = $tempTokenResponse;

            if($has_promo_code && !$promo_code_exists)
                $error = $this->lang->line('messages_promo_code_not_exist');

            $result['payerID'] = '';
            $this->session->set_flashdata('message', 'error:'.$error);
            echo json_encode($result);
        }
        die();
    }
    function Get_Temp_Token($payerID,$payerName)
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
			 <typ:Id>'.$payerID.'</typ:Id>
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
		
		return $this->Submit_Request_subscription($envelope, $SOAP_Action);
	}
	function Submit_Request_subscription($envelope, $SOAP_Action)
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
		
		return $this->Parse_Results_subscription($response);
	}
	function Parse_Results_subscription($api_response)
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
				//$result .= "\nTransaction Results:";
				//$result .= "\nCredential ID: " . $credential_id;
				//$result .= "\nPayer ID: " . $payer_id; 
				//$result .= "\nTemp Token: " . $temp_token;
				//$result .= "\n";
				
				$result['CredentialID'] = $credential_id;
				$result['PayerID'] = $payer_id;
				$result['TempToken'] = $temp_token;
				
				return $result;
			} 		
		}	
		else
		{
		};	
	} 
    function getTempToken($payerID,$payerName)
    {
        if(empty($payerName)){
            $request = $this->settings->propay_apiroute."/protectpay/TempTokens/?payerName=Spera&payerID=".$payerID."&durationSeconds=3600";
        } else {
            $payerName = str_replace(' ','',$payerName);
            $request = $this->settings->propay_apiroute."/protectpay/TempTokens/?payerName=".$payerName."&payerID=".$payerID."&durationSeconds=3600";
        }
        $method = "GET";
        $postData = ""; 
        $responseData = $this->makeCurlRequest($request,$postData,$method);
        if ( $responseData->RequestResult->ResultCode=='00' ) // response success
        {
            $payer_id = $responseData->PayerId;
            $tempToken = $responseData->TempToken;
            $credentialID = $responseData->CredentialId;
            return json_encode( array( 'PayerID'       =>  $payer_id,'TempToken'     =>  $tempToken,'CredentialID'  =>  $credentialID ));
        }
        else if ($responseData->RequestResult->ResultCode=='300') //authentication failed
        {
            return $responseData->RequestResult->ResultMessage;
        }
        else if ($responseData->RequestResult->ResultCode=='301') //invalid
        {
            return $responseData->RequestResult->ResultMessage;
        }
        else if ($responseData->RequestResult->ResultCode=='307') //invalid
        {
            return $responseData->RequestResult->ResultMessage;
        }
        else if ($responseData->RequestResult->ResultCode=='-1') // resource error i.e url mismatch
        {
            return 'Resource not found';
        }
    }
    
    function makeCurlRequest($apiUrl,$postData,$methodType)
    {
        $err = "";
        $result = "";
        $response = "";
        $auth_billerID = $this->settings->propay_biller_id.":".$this->settings->propay_auth_token;  //billerID: Authentication Token provided by propay
        
        $headers = array(
            'Accept:application/json',
            'Content-Type:application/json',
            'Authorization: Basic '.base64_encode($auth_billerID),
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $methodType);
        if($postData!="")
        {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        $result = curl_exec($ch);   
        $err = curl_error($ch);
        curl_close($ch); 
        
        if($result!="")
        {
            $response = json_decode($result);
            return $response;
        }
       
        if($err!="")
        {
            $msg='{"RequestResult":{"ResultCode":"-1"}}';
            $response = json_decode($msg);
            return $response;
        }
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
    
    
    /*creat propay account[start]*/
    function payment_account()
    {
        $spera_accounts = Subscription::find_by_sql('SELECT u.* FROM spera_accounts u WHERE u.user_id = '.$this->sessionArr['user_id']);
        
        if(!empty($spera_accounts))
        {   
            $account_all = array();
            $i=0;
            
            foreach($spera_accounts as $key =>$value)
            {
                $account_all[$i]['account_number']=$value->account_number;
                $account_all[$i]['profile_id']=$value->merchant_profile_id;
                $account_all[$i]['is_default']=$value->is_default;
                $i++;
            }
        }
        
        $this->view_data['accounts']=$account_all;
        $this->content_view = 'subscriptions/ao_subscriptions/payment_account';
    }

    function create_payment_account()
    {
        if($_POST){
            
            $username       = $_POST['source_email'];
            $f_name         = $_POST['fname'];
            $l_name         = $_POST['lname'];
            $dba            = '';
            $addr1          = $_POST['addr1'];
            $apt            = $_POST['apt'];
            $city           = $_POST['city'];
            $state          = $_POST['state'];
            $zip            = $_POST['zip'];
            $country        = "";
            $day_phone      = $_POST['dayphone'];
            $even_phone     = $_POST['evenphone'];
            $ext            = date('mdhis');
            
            $ssn_no         = $_POST['ssn'];
            $dob            = date('m-d-Y',strtotime($_POST['dob']));
            $tier           = "Premium"; // empty for card only 
            $response       = $this->SignUpPropay($username,$f_name,$l_name,$dba,$addr1,$apt,$city,$state,$zip,$country,$day_phone,$even_phone,$ext,$ssn_no,$dob,$tier);

            
            $resultData     = $response['resultData'];
            $status         = $response['status'];
            
            if ($resultData['status'] == '99' )
            {
                $this->session->set_flashdata('message', 'success:Your spera account has been created, Please check your email to activate !');
            }
            else if ($resultData['status'] == '87' )
            {
                $this->session->set_flashdata('message', 'error:The email address '.$resultData['sourceEmail'].' has already been taken. Try creating account using another email address');
            }
            else if ($resultData['status'] == '00' )
            {
                $this->session->set_flashdata('message', 'success:Your spera account has been successfully created.');
            }
            redirect('aosettings/paymentaccount/');

        }else
        {
            $this->theme_view = 'modal';
            $this->view_data['states'] = States::find('all',array('conditions' => array('1=?','1')));
            $this->view_data['form_action'] = base_url().'aosubscriptions/create_payment_account/';
            $this->content_view = 'subscriptions/ao_subscriptions/create_payment_account';
        }
    }
    
    function SignUpPropay($username,$f_name,$l_name,$dba,$addr1,$apt,$city,$state,$zip,$country,$day_phone,$even_phone,$ext,$ssn_no,$dob,$tier)
    {
        //(Required=*  Optional=**)
        $certString     = $this->settings->propay_certstring; //*
        //$termID         = "467bbe84"; // termid use for authentication to test env
        
        $sourceEmail    = $username;//*
        $firstName      = $f_name;//*
        $lastName       = $l_name;//*
        $doingBusinessAs= $dba;
        $address1       = $addr1;//*
        $aptNumber      = $apt;
        $city           = $city;//*
        $state          = $state;//*
        $zipCode        = $zip;//*
        $country        = $country;//**
        $dayPhone       = $day_phone;//*
        $evePhone       = $even_phone;//*
        $externalID     = $ext;
        $ssn            = $ssn_no;//* Required for USA
        $dob            = $dob;//* mm-dd-yyyy 
        $tier           = $tier;//**

        $response       = "";
        $email          = $sourceEmail;
        
        $envelope= '<?xml version="1.0"?>
            <!DOCTYPE Request.dtd>
            <XMLRequest>
                <certStr>'.$this->settings->propay_certstring.'</certStr>                
                <class>partner</class>
                <XMLTrans>  
                    <transType>01</transType>
                    <sourceEmail>'.$sourceEmail.'</sourceEmail>
                    <firstName>'.$firstName.'</firstName>
                    <lastName>'.$lastName.'</lastName>
                    <DoingBusinessAs>'.$doingBusinessAs.'</DoingBusinessAs>
                    <addr>'.$address1.'</addr>
                    <aptNum>'.$aptNumber.'</aptNum>
                    <city>'.$city.'</city>
                    <state>'.$state.'</state>
                    <zip>'.$zipCode.'</zip>
                    <country>'.$country.'</country>
                    <dayPhone>'.$dayPhone.'</dayPhone>
                    <evenPhone>'.$evePhone.'</evenPhone>
                    <externalId>' . $externalID. '</externalId>
                    <ssn>'.$ssn.'</ssn>
                    <dob>'.$dob.'</dob>
                    <tier>'.$tier.'</tier>
                </XMLTrans>
             </XMLRequest>';

        $SOAP_Action = "CreatePayerWithData"; 
        $api_response = $this->Submit_Request($envelope);
        //$api_response = $this->Submit_Request($envelope);
        
        $result = simplexml_load_string($api_response); 
        
        $array =  (array) $result;
        $array1 =  (array) $array['XMLTrans'];
        $user_account_number = $array1['accntNum'];
        
        
        if(isset($result->XMLTrans->status))    
        {
            $status = $result->XMLTrans->status; // status code return from api
            $statusCode = $status;
            
            if ( $status == '99' ) // account created but user have to pay to activate account
            {
                $status = '_'.$status;          
                //$status = $response_status->status->$status;
                $user_password = $result->XMLTrans->password;

                $tranType=$result->XMLTrans->transType;
                $user_name = $result->XMLTrans->sourceEmail;
                $user_password = $result->XMLTrans->password;
                //$user_account_number = $result->XMLTrans->accntNum[0];
                $user_tier = $result->XMLTrans->tier;    
                $account_status="Unpaid";
                $userID = $this->sessionArr['user_id'];
                $profile_id = '';
                
                $newArr = array('account_number' => $user_account_number, 
                                'account_username' => $user_name,
                                'account_type' => $user_tier,
                                'account_balance' => 0,
                                'account_status' => $account_status,
                                'user_id' => $userID,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                                'is_default' => 1,
                                'merchant_profile_id' => $profile_id);
                $insert_data = Spera_accounts::create($newArr);
                
                $Args = array(
                    $this->settings->propay_auth_token,
                    $this->settings->propay_biller_id,
                    "LegacyProPay",                                 //PaymentProcessor 
                    $this->settings->propay_certstring,
                    $user_account_number,                                         //accountNum
                    $this->settings->propay_termid
                    );

                $profile_id = $this->CreateMerchantProfile($Args,$insert_data->spera_accounts_id);

                $response_to_json = json_encode($result->XMLTrans);                
                $resultData = json_decode($response_to_json,TRUE);
          
                $ret_var=array("resultData"=>$resultData,"status"=>$status);   
                return $ret_var;             
            }
            elseif ($status == '66') // account created but denied 
            {
                $status = '_'.$status;          
                //$status = $response_status->status->$status;
                $user_password = $result->XMLTrans->password;

                $tranType=$result->XMLTrans->transType;
                $user_name = $result->XMLTrans->sourceEmail;
                $user_password = $result->XMLTrans->password;
                //$user_account_number = $result->XMLTrans->accntNum[0];
                $user_tier = $result->XMLTrans->tier;    
                $account_status="ClosedRisk";
                $userID = $this->sessionArr['user_id'];
                $profile_id = '';
                
                $newArr = array('account_number' => $user_account_number, 
                                'account_username' => $user_name,
                                'account_type' => $user_tier,
                                'account_balance' => 0,
                                'account_status' => $account_status,
                                'user_id' => $userID,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                                'is_default' => 1,
                                'merchant_profile_id' => $profile_id);
                $insert_data = Spera_accounts::create($newArr);
                
                $Args = array(
                    $this->settings->propay_auth_token,
                    $this->settings->propay_biller_id,
                    "LegacyProPay",                                 //PaymentProcessor 
                    $this->settings->propay_certstring,
                    $user_account_number,                                         //accountNum
                    $this->settings->propay_termid
                    );

                $profile_id = $this->CreateMerchantProfile($Args,$insert_data->spera_accounts_id);

                $response_to_json = json_encode($result->XMLTrans);                
                $resultData = json_decode($response_to_json,TRUE);
          
                $ret_var=array("resultData"=>$resultData,"status"=>$status);   
                return $ret_var;             
            }
            else if($status != '00')
            {                
                $status = '_'.$status;          
                //$status = $response_status->status->$status;
                $response  = "Request incomplete";
                $response .= "<br/>Transaction Status: " . $status;
                $response .= "<br/>Status Code: " . $statusCode;
 
                $response_to_json = json_encode($result->XMLTrans); 
                $resultData = json_decode($response_to_json,TRUE);
                $ret_var=array("resultData"=>$resultData,"status"=>$status);   
                return $ret_var;                    
            }
            else
            {    
        
                $user_name = $result->XMLTrans->sourceEmail;
                $user_password = $result->XMLTrans->password;
                //$user_account_number = $result->XMLTrans->accntNum[0];
                $user_tier = $result->XMLTrans->tier;    
                $account_status="Ready"; 
                $userID = $this->sessionArr['user_id'];
                $profile_id = '';
                
               
                
                $newArr = array('account_number' => $user_account_number, 
                                'account_username' => $user_name,
                                'account_type' => $user_tier,
                                'account_balance' => 0,
                                'account_status' => $account_status,
                                'user_id' => $userID,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                                'is_default' => 1,
                                'merchant_profile_id' => $profile_id);
                $insert_data = Spera_accounts::create($newArr);
                
                $Args = array(
                    $this->settings->propay_auth_token,
                    $this->settings->propay_biller_id,
                    "LegacyProPay",
                    $this->settings->propay_certstring,
                    $user_account_number,
                    $this->settings->propay_termid
                    );

                $this->CreateMerchantProfile($Args,$insert_data->spera_accounts_id);

                $response_to_json = json_encode($result->XMLTrans);                
                $resultData = json_decode($response_to_json,TRUE);
                $ret_var=array("resultData"=>$resultData,"status"=>$status);   
                return $ret_var;               
            }              
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
        
        $result = simplexml_load_string($response); 
        return $response;
    }
    
    function CreateMerchantProfile($Arguments,$spera_accounts_id)
    { 
        $request = $this->settings->propay_apiroute."/protectpay/merchantprofiles/";
        $method = "PUT";
        $data = array(
            "ProfileName"           =>'Spera',
            "PaymentProcessor"      =>"LegacyProPay",
            "ProcessorData"         => [
                array(
                    "ProcessorField"   =>"certStr",
                    "Value"            =>$Arguments[3] // provided by propay
                ),
                array(
                    "ProcessorField"   =>"accountNum",
                    "Value"            =>$Arguments[4] // propay account number
                ),
                array(
                    "ProcessorField"   =>"termId",
                    "Value"            =>$Arguments[5] // propay termId number
                )
            ],
        );
        $postData = json_encode($data); 
        $responseData = $this->Submit_Request_get_profile($request,$postData,$method,$spera_accounts_id);
        return $responseData;
    }

    function Submit_Request_get_profile($apiUrl,$postData,$methodType, $spera_accounts_id)
    {
        $err = "";
        $result = "";
        $response = "";
        $auth_billerID = $this->settings->propay_biller_id.":".$this->settings->propay_auth_token;  //billerID: Authentication Token provided by propay
        
        $headers = array(
            'Accept:application/json',
            'Content-Type:application/json',
            'Authorization: Basic '.base64_encode($auth_billerID),
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $methodType);
        if($postData!="")
        {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        $result = curl_exec($ch);   
        $err = curl_error($ch);
        curl_close($ch); 
        
        if($result!="")
        {
            $response = json_decode($result);
            $this->db->query('UPDATE spera_accounts SET merchant_profile_id="'.$response->ProfileId.'" WHERE spera_accounts_id = '.$spera_accounts_id);
        }
    }
    function Parse_Results($api_response, $spera_accounts_id)
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
            
            if($result_code != '00' || $result_value == "FAILURE")
            {
                
            }
            else
            {
                $profile_id = $doc->getElementsByTagName('ProfileId')->item(0)->nodeValue;
                $this->db->query('UPDATE spera_accounts SET merchant_profile_id="'.$profile_id.'" WHERE spera_accounts_id = '.$spera_accounts_id);
            }         
        }
    }
    /*creat propay account[end]*/
    
    
    /*Existing CC payment[start]*/
    function process_payment_method($arguments,$processPaymentMethoddata)
    {             
        $envelope=
        '<?xml version="1.0"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:con="http://propay.com/SPS/contracts" xmlns:typ="http://propay.com/SPS/types">
        <soapenv:Header/>
        <soapenv:Body>
        <con:ProcessPaymentMethodTransaction>
            <con:id>
                <typ:AuthenticationToken>'.$arguments[0].'</typ:AuthenticationToken>
                <typ:BillerAccountId>'.$arguments[1].'</typ:BillerAccountId>
            </con:id>
            <con:transaction>
                <typ:Amount>'.$arguments[5].'</typ:Amount>
                <typ:Comment1>test</typ:Comment1>
                <typ:Comment2>test</typ:Comment2>
                <typ:Invoice>test</typ:Invoice>
                <typ:MerchantProfileId>'.$arguments[2].'</typ:MerchantProfileId>
                <typ:PayerAccountId>'.$arguments[3].'</typ:PayerAccountId>
            </con:transaction>
            <con:paymentMethodID>'.$arguments[4].'</con:paymentMethodID>
        </con:ProcessPaymentMethodTransaction>
        </soapenv:Body>
        </soapenv:Envelope>';
        
        $SOAP_Action = "ProcessPaymentMethodTransaction"; 
        $process_payment_submit_request = $this->process_payment_submit_request($envelope, $SOAP_Action ,$processPaymentMethoddata);
        return $process_payment_submit_request;
    }
    function process_payment_submit_request($envelope, $SOAP_Action ,$processPaymentMethoddata)
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
        curl_setopt($soap_do, CURLOPT_URL, $this->settings->propay_apiroute."/protectpay/sps.svc");
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
        $process_payment_parse_results = $this->process_payment_parse_results($response,$processPaymentMethoddata);
        return $process_payment_parse_results;
    }
    function process_payment_parse_results($api_response,$processPaymentMethoddata)
    {
        $doc = new \DOMDocument();
        $doc->loadXML($api_response);
        //Pretty Print response
        $api_result = new \DOMDocument('1.0');
        $api_result->preserveWhiteSpace = false;
        $api_result->formatOutput = true;
        $api_result->loadXML($api_response);
        
        if(isset($doc->getElementsByTagName('ResultCode')->item(0)->nodeValue))
        {
            $result_code = $doc->getElementsByTagName('ResultCode')->item(0)->nodeValue;
            $result_value = $doc->getElementsByTagName('ResultValue')->item(0)->nodeValue;
            if($result_code != '00' || $result_value == "FAILURE")
            {
                $result_message = $doc->getElementsByTagName('ResultMessage')->item(0)->nodeValue;
                $dataVal = 'fail';
                return $dataVal;
            }
            else
            {
                $AVSCode = $doc->getElementsByTagName('AVSCode')->item(0)->nodeValue;
                $AuthorizationCode = $doc->getElementsByTagName('AuthorizationCode')->item(0)->nodeValue;
                $CurrencyConversionRate = $doc->getElementsByTagName('CurrencyConversionRate')->item(0)->nodeValue;
                $CurrencyConvertedAmount = $doc->getElementsByTagName('CurrencyConvertedAmount')->item(0)->nodeValue;
                $CurrencyConvertedCurrencyCode = $doc->getElementsByTagName('CurrencyConvertedCurrencyCode')->item(0)->nodeValue;
                $TransactionHistoryId = $doc->getElementsByTagName('TransactionHistoryId')->item(0)->nodeValue;
                $TransactionId = $doc->getElementsByTagName('TransactionId')->item(0)->nodeValue;
                $TransactionResult = $doc->getElementsByTagName('TransactionResult')->item(0)->nodeValue;
                $CVVResponseCode = $doc->getElementsByTagName('CVVResponseCode')->item(0)->nodeValue;
                $GrossAmt = $doc->getElementsByTagName('GrossAmt')->item(0)->nodeValue;
                $NetAmt = $doc->getElementsByTagName('NetAmt')->item(0)->nodeValue;
                $PerTransFee = $doc->getElementsByTagName('PerTransFee')->item(0)->nodeValue;
                $Rate = $doc->getElementsByTagName('Rate')->item(0)->nodeValue;
                $GrossAmtLessNetAmt = $doc->getElementsByTagName('GrossAmtLessNetAmt')->item(0)->nodeValue;
                        
                if((!empty($result_code)) && ($result_code ==  00) ){
                    
                    
                    $this->db->query('UPDATE propay_data SET set_default="0" WHERE user_id = '.$processPaymentMethoddata['user_id']);
                    $this->db->query('UPDATE propay_data SET set_default="1" WHERE id = '.$processPaymentMethoddata['propay_data_last_inserted_ID']);
                    
                    $propay_payment_detail = array('user_id' => $processPaymentMethoddata['user_id'], 
                                                    'propay_data_id' => $processPaymentMethoddata['propay_data_last_inserted_ID'],
                                                    'avscode' => $AVSCode,
                                                    'authorization_code' => $AuthorizationCode,
                                                    'currency_conversion_rate' => $CurrencyConversionRate,
                                                    'currency_converted_amount' => $CurrencyConvertedAmount,
                                                    'currency_converted_currency_code' => $CurrencyConvertedCurrencyCode,
                                                    'gross_amt' => $GrossAmt,
                                                    'gross_amt_less_net_amt' => $GrossAmtLessNetAmt,
                                                    'net_amt' => $NetAmt,
                                                    'per_trans_fee' => $PerTransFee,
                                                    'rate' => $Rate,
                                                    'result_code' => $result_code,
                                                    'result_value' => $result_value,
                                                    'result_message' => "",
                                                    'transaction_history_id' => $TransactionHistoryId,
                                                    'transaction_id' => $TransactionId,
                                                    'transaction_result' => $TransactionResult,
                                                    'cvv_response_code' => $CVVResponseCode,
                                                    'updated_at' => date('Y-m-d H:i:s'),
                                                    'created_at' => date('Y-m-d H:i:s'));
                    $insert_data = PropayPaymentDetail::create($propay_payment_detail);
                    $propay_payment_detail_last_inserted_ID = $insert_data->id;    
                    
                    
                    $propay_user_data = array('payment_detail_id' => $propay_payment_detail_last_inserted_ID, 
                                                'package_id' => $processPaymentMethoddata['package_id'],
                                                'user_id' => $processPaymentMethoddata['user_id'],
												'status' => $processPaymentMethoddata['status'],
                                                'start_date' => $processPaymentMethoddata['start_date'],
                                                'end_date' => $processPaymentMethoddata['end_date'],
                                                'updated_at' => date('Y-m-d H:i:s'),
                                                'created_at' => date('Y-m-d H:i:s'),
												'type'=>$processPaymentMethoddata['package_type']);
                    $insert_data = PropayUserSubscription::create($propay_user_data);
                    
                    $user = User::find_by_id($processPaymentMethoddata['user_id']);
                    $message="Hi ".trim($user->firstname." ".$user->lastname)."<br/><br>
                          <p>Thanks for subscription!</p><br/>
                          Thanks
                          Spera Team
                          ";  
                    //send_subscription_notification($user->email, 'Spera | Thanks for subscription', $message);
                    
                    $dataVal = 'success';
                    return $dataVal;
                }
            }         
        }    
        else
        {
            $dataVal = 'fail';
            return $dataVal;
        };
        
    } 
    /*Existing CC payment[end]*/
}

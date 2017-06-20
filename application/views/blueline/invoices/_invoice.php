<?php   
$attributes = array('class' => '', 'id' => '_invoices');
echo form_open($form_action, $attributes); 
?>
<div class="ajax-loader"></div>
<?php if(isset($invoice)){ ?>
<input id="id" type="hidden" name="id" value="<?=$invoice->id;?>" />
<?php } ?>
<?php if(isset($view)){ ?>
<input id="view" type="hidden" name="view" value="true" />
<?php } ?>
<input id="status" name="status" type="hidden" value="Open">  
<input id="reference" type="hidden" name="reference" class="form-control"  value="<?php if(isset($invoice)){echo $invoice->reference;} else{ echo $core_settings->invoice_reference; } ?>" />
 
 
<div class="form-group">
        <label for="project"><?=$this->lang->line('application_projects');?></label>
        <select name="project_id" id="getProjects" style="width:100%" class="chosen-select" required>
            <option value=""></option>           
			<?php foreach ($projects as $pro): ?>
			<option value="<?=$pro->id?>" <?php if($project == $pro->id){ ?>selected="selected"<?php } ?>><?=$pro->name?></option>
			<?php endforeach; ?>
        </select>

</div> 
<div class="form-group">
        <label for="client"><?=$this->lang->line('application_client');?></label>
        <?php 
			$options = array();	
			foreach ($clients as $client):  
				$options[$client->id] = trim($client->firstname.' '.$client->lastname);
			endforeach;
        $invoice_users = (isset($invoice)) ? $selected_invoice_user['id'] : ""; 
        echo form_multiselect('client_list[]', $options, $invoice_users, 'style="width:100%" class="chosen-select" id="client_list"');?>
</div>  

<?php if(isset($invoice)){ ?>
 <div class="form-group">
        <label for="status"><?=$this->lang->line('application_status');?></label>
        <?php $options = array(
                  'Open'  => $this->lang->line('application_Open'),
                  'Sent'    => $this->lang->line('application_Sent'),
                  'Paid' => $this->lang->line('application_Paid'),
                  'PartiallyPaid' => $this->lang->line('application_PartiallyPaid'),
                  'Canceled' => $this->lang->line('application_Canceled'),

                );
                echo form_dropdown('status', $options, $invoice->status, 'style="width:100%" class="chosen-select"'); ?>

 </div>
<?php } ?>
<?php if(isset($invoice)){ if($invoice->status == "Paid"){ ?>
 <div class="form-group">
        <label for="paid_date"><?=$this->lang->line('application_payment_date');?></label>
        <input id="paid_date" type="text" name="paid_date" class="datepicker form-control" value="<?php if(isset($invoice)){echo $invoice->paid_date;} ?>"  required/>
 </div>
 <?php }} ?>
 <div class="form-group">
        <label for="issue_date"><?=$this->lang->line('application_issue_date');?></label>
        <input id="issue_date" type="text" name="issue_date" class="datepicker form-control" value="<?php if(isset($invoice)){echo $invoice->issue_date;} ?>"  required/>
 </div>
 <div class="form-group">
        <label for="due_date"><?=$this->lang->line('application_due_date');?></label>
        <input id="due_date" type="text" name="due_date" class="required datepicker-linked form-control" value="<?php if(isset($invoice)){echo $invoice->due_date;} ?>"  required/>
 </div>
 <div class="form-group">
        <label for="currency"><?=$this->lang->line('application_currency');?></label>
        <input id="currency" type="text" name="currency" list="currencylist" class="required form-control no-numbers" value="<?php if(isset($invoice)){ echo $invoice->currency; }  else if(isset($company_detail)) { echo $company_detail[0]->currency; } else { echo $core_settings->currency; } ?>" required/>
        <datalist id="currencylist">
          <option value="AUD"></option>
          <option value="BRL"></option>
          <option value="CAD"></option>
          <option value="CZK"></option>
          <option value="DKK"></option>
          <option value="EUR"></option>
          <option value="HKD"></option>
          <option value="HUF"></option>
          <option value="ILS"></option>
          <option value="JPY"></option>
          <option value="MYR"></option>
          <option value="MXN"></option>
          <option value="NOK"></option>
          <option value="NZD"></option>
          <option value="PHP"></option>
          <option value="PLN"></option>
          <option value="GBP"></option>
          <option value="SGD"></option>
          <option value="SEK"></option>
          <option value="CHF"></option>
          <option value="TWD"></option>
          <option value="THB"></option>
          <option value="TRY"></option>
          <option value="USD"></option>
        </datalist>
 </div>
 <div class="form-group">
        <label for="currency"><?=$this->lang->line('application_discount');?></label>
        <input class="form-control" name="discount" id="appendedInput" type="text" value="<?php if(isset($invoice)){ echo $invoice->discount;} ?>"/>
 </div>
 <div class="form-group">
        <label for="terms"><?=$this->lang->line('application_terms');?></label>
        <textarea id="terms" name="terms" class="textarea required summernote-modal form-control" style="height:100px"><?php if(isset($invoice)){echo $invoice->terms;}else{ echo $core_settings->invoice_terms; }?></textarea>
 </div>
  <div class="form-group">
        <label for="terms"><?=$this->lang->line('application_custom_tax');?></label>
        <input class="form-control" name="tax" type="text" value="<?php if(isset($invoice)){ echo $invoice->tax;}else{echo $core_settings->tax;} ?>" />
 </div>
    <div class="form-group">
        <label for="terms"><?=$this->lang->line('application_second_tax');?></label>
        <input class="form-control" name="second_tax" type="text" value="<?php if(isset($invoice)){ echo $invoice->second_tax;} else if(isset($company_detail)) { echo $company_detail[0]->second_tax; } ?>"/>
 </div>

        <div class="modal-footer">
        <input type="submit" name="send" class="btn btn-primary" value="<?=$this->lang->line('application_save');?>"/>
        <a class="btn" data-dismiss="modal"><?=$this->lang->line('application_close');?></a>
        </div>


<?php echo form_close(); ?>
<script type="text/javascript">
$(document).ready(function() {
	$('#client_list').trigger('chosen:open');
	$('#client_list').trigger('chosen:close');
	if($('#getProjects').length > 0) {
		$('#getProjects').on('change', function() {
			$('.ajax-loader').show();
			var project_id = $(this).val();
			$.get("<?php echo base_url()?>estimates/get_project_clients", {
				project_id: project_id
			}, function(response){						
				$('.ajax-loader').hide();
				result = jQuery.parseJSON(response);
				// $('#loading').hide();
				if(result.message != '') {
					$('#client_list').empty().append(result.message).trigger("chosen:updated");
				}
			});
			return false;
		});
	}
});	
</script>
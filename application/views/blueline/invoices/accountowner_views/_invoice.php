<?php   
$attributes = array('class' => '', 'id' => '_invoices');
echo form_open($form_action, $attributes); 
?>

<?php if(isset($invoice)){ ?>
<input id="id" type="hidden" name="id" value="<?=$invoice->id;?>" />
<?php } ?>
<?php if(isset($view)){ ?>
<input id="view" type="hidden" name="view" value="true" />
<?php } ?>
<input id="status" name="status" type="hidden" value="Open"> 
 <div class="form-group">
        <label for="reference"><?=$this->lang->line('application_reference_id');?> *</label>
        <?php if(!empty($core_settings->invoice_prefix)){ ?>
       <div class="input-group"> <div class="input-group-addon"><?=$core_settings->invoice_prefix;?></div> <?php } ?>
        <input id="reference" type="text" name="reference" class="form-control"  value="<?php if(isset($invoice)){echo $invoice->reference;} else{ echo $core_settings->invoice_reference; } ?>" />
        <?php if(!empty($core_settings->invoice_prefix)){ ?> </div><?php } ?>
 </div>

 <div class="form-group">
        <label for="project"><?=$this->lang->line('application_projects');?></label>
        <?php $options = array();
                $options['0'] = 'Please Select Task';
                foreach ($projects as $value):  
					$options[$value['pid']] = $value['pname'];
					$projects[$i] = $value;
                endforeach;
		/** open this comment for editing */
        if(isset($invoice)){$client = $invoice->company_id; $project = $invoice->project_id;}else{$client = ""; $project = "";}
        echo form_dropdown('project_type', $options, $client, 'style="width:100%" name="project_type" id="project_type" class="chosen-select getProjects"');?>
 </div>
	
 <div class="form-group">
        <label for="project"><?=$this->lang->line('application_tasks');?></label>
		<div name='showtasktype' id='showtasktype'>
			<select name="task_type" style="width:100%" name="task_type" id="task_type" class="chosen-select">
				<option value="0">Please Select Task</option>
			</select>
		</div>
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
        <input id="currency" type="text" name="currency" list="currencylist" class="required form-control no-numbers" value="<?php if(isset($invoice)){ echo $invoice->currency; }else { echo $core_settings->currency; } ?>" required/>
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
        <input class="form-control" name="second_tax" type="text" value="<?php if(isset($invoice)){ echo $invoice->second_tax;} ?>"/>
 </div>

        <div class="modal-footer">
        <input type="submit" name="send" class="btn btn-primary" value="<?=$this->lang->line('application_save');?>"/>
        <a class="btn" data-dismiss="modal"><?=$this->lang->line('application_close');?></a>
        </div>
<?php echo form_close(); ?>
<script>
$(function(){
    $.ajaxSetup({
            headers: {
                'X_CSRF-TOKEN' : $('meta[name="_token"]').attr('content')
            }
        });
});
$("#project_type").change(function(e)
{
	var project_id = {"project_id" : $('#project_type').val()};
    $.ajax({
		type: "GET",
		url:"<?php echo base_url();?>aoinvoices/get_tasks/",
		data:{ project_id  : project_id },
        success:function(data){
			
			 if(data.length != 0) {

				$("#showtasktype").html(data);
			 }
        },
		complete: function (data) {
		  $(".chosen-select").chosen({scroll_to_highlighted: false, disable_search_threshold: 4, width: "100%"});
		}
    });
});
</script>

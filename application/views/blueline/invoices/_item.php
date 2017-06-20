<?php   
$attributes = array('class' => '', 'id' => '_task');
echo form_open($form_action, $attributes); 
?>

<?php if(isset($invoice)){ ?>
<input id="invoice_id" type="hidden" name="invoice_id" value="<?=$invoice->id;?>" />
<input id="project_id" type="hidden" name="project_id" value="<?=$invoice->project_id;?>" />
<?php }  ?>
<?php if(!isset($invoice_has_items)) { ?>


	 <div class="form-group">
		<label for="task_id"><?=$this->lang->line('application_tasks');?></label>
		<?php $options = array(); 
		$options['0'] = '-';
		foreach ($items as $value):
		$options[$value->id] = $value->name." - ".$value->value." ".$core_settings->currency;
		?><span class="hidden" id="item<?=$value->id;?>"><?=$value->description;?></span><?php
		endforeach;
		// foreach ($rebill as $value):
		// $options["rebill_".$value->id] = "[".$this->lang->line('application_rebill')."] ".$value->description." - ".$value->value." ".$core_settings->currency;
		// endforeach;
		echo form_dropdown('task_id', $options, '', 'style="width:100%" class="chosen-select" id="task_id"');?>
		<!-- <a class="btn btn-primary tt additem" titel="<?=$this->lang->line('application_custom_item');?>"><i class="fa fa-plus"></i></a> -->
	</div>     

<?php } else { ?>
	<input id="id" type="hidden" name="id" value="<?=$invoice_has_items->id;?>" />
	<input id="invoice_id" type="hidden" name="invoice_id" value="<?=$invoice_has_items->invoice_id;?>" />
<?php } ?>


 <div class="form-group">
        <label for="name"><?=$this->lang->line('application_name');?></label>
        <input id="name" name="name" type="text" class="required form-control"  value="<?=(isset($invoice_has_items->name)) ? $invoice_has_items->name : '';?>" readonly />
 </div>
 <div class="form-group">
        <label for="value"><?=$this->lang->line('application_value');?></label>
        <input id="value" type="text" name="value" class="required form-control number"  value="<?=(isset($invoice_has_items->value)) ? $invoice_has_items->value : '';?>" />
 </div>


 <div class="form-group">
	<?php 
	$hours = 00;
	$minutes = 00;
	if( isset($invoice_has_items->amount) ){
		$time_spent_value = explode('.', $invoice_has_items->amount);
		if(count($time_spent_value) == 2) {
			$hours = $time_spent_value[0];
			$minutes = $time_spent_value[1];
		} else if(count($time_spent_value) == 1) {
			$hours = $time_spent_value[0];
		}
	} ?>
	
	<label for="value"><?=$this->lang->line('application_time_spent');?></label>
	<input class="inline-textfield hours" type="number" min="0" max="1000" size="3" id="hours" name="hours" value="<?php echo $hours; ?>"> <?=$this->lang->line('application_hours');?> 
	<input class="inline-textfield minutes" type="number" min="0" max="60" size="2" id="minutes" name="minutes" value="<?php echo $minutes; ?>"> <?=$this->lang->line('application_minutes');?>
 </div>
 <div class="form-group">
	<label for="description"><?=$this->lang->line('application_description');?></label>
	<textarea id="description" class="form-control" name="description"><?php if(isset($invoice_has_items)){ echo $invoice_has_items->description; } ?></textarea>
 </div>

<div class="modal-footer">
<input type="submit" name="send" class="btn btn-primary" value="<?=$this->lang->line('application_save');?>"/>
<a class="btn" data-dismiss="modal"><?=$this->lang->line('application_close');?></a>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
$(document).ready(function() {
	if($('#task_id').length > 0) {
		$('#task_id').on('change', function() {
			var project_id = $('#project_id').val();
			var project_task_id = $(this).val();
			$.get("<?php echo base_url()?>invoices/get_project_task_detail", {
				project_id: project_id, project_task_id: project_task_id
			}, function(response){						
				result = jQuery.parseJSON(response);
				
				if(result.message != '') {
					$('#name').val(result.message.name);
					$('#value').val(result.message.value);
					$('#hours').val(result.message.hours);
					$('#minutes').val(result.message.minutes);
					$('#description').html(result.message.description);
				}
			});
			return false;
		});
	}
});	
</script>
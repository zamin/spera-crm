<div id="row">
		<div class="col-md-3">
			<div class="list-group">
				<?php foreach ($submenu as $name=>$value):
				$badge = "";
				$active = "";
				if($value == "settings/updates"){ $badge = '<span class="badge badge-success">'.$update_count.'</span>';}
				if($name == $breadcrumb){ $active = 'active';}?>
	               <a class="list-group-item <?=$active;?>" id="<?php $val_id = explode("/", $value); if(!is_numeric(end($val_id))){echo end($val_id);}else{$num = count($val_id)-2; echo $val_id[$num];} ?>" href="<?=$value;?>"><?=$badge?> <?=$name?></a>
	            <?php endforeach;?>
			</div>
		</div>


<div class="col-md-9">
	<div class="table-head"><?=$this->lang->line('application_api_setting');?></div>
	<div class="table-div settings">
	<?php   
	$attributes = array('class' => '', 'id' => 'enable_api');
	echo form_open_multipart($form_action, $attributes); 
	?>

	<input type="hidden" name="accessapiid" value="<?php echo $id;?>">
	<div class="form-header"><?=$this->lang->line('application_api_option');?></div>
	
	<div class="form-group">
			<label>User Access Token</label>
			<input type="text" name="calendar_google_api_key" class="form-control" value="<?php echo $access_token;?>" disabled>
	</div>

	<div class="form-group">
			<label>User Login Token</label>
			<input type="text" name="calendar_google_api_key" class="form-control" value="<?php echo $login_token;?>" disabled>
	</div>
	<div class="row">

	</div>

        <div class="form-group no-border">
		<?php
		if($enabled != 'true')
		{
		?>
		<input type="submit" name="submit" class="btn btn-primary" value="<?=$this->lang->line('application_enable');?>"/>
		<?php } ?>
		<?php
		if($disabled != 'true')
		{
		?>
		<!-- <input type="submit" name="submit" class="btn btn-primary" value="<?=$this->lang->line('application_disable');?>"/> -->
		<input type="button" class="btn btn-primary btn-option delete po" data-toggle="popover" data-placement="left" data-content="<button class='btn btn-danger' type='submit' name='submit' value='<?=$this->lang->line('application_disable');?>'><?=$this->lang->line('application_yes');?></button> <button class='btn po-close'><?=$this->lang->line('application_no');?></button>" data-original-title="<b><?=$this->lang->line('application_disable_message');?></b>" value="<?=$this->lang->line('application_disable');?>">

		<!-- <input type="submit" name="submit" class="btn btn-primary" value="<?=$this->lang->line('application_reset_login_token');?>"/> -->
		<!-- <button type="submit" name="submitButton" value="DeleteAnswer22">Delete Answer 22</button> -->
		<input type="button" class="btn btn-primary btn-option delete po" data-toggle="popover" data-placement="left" data-content="<button class='btn btn-danger' type='submit' name='submit' value='<?=$this->lang->line('application_yes_reset_login');?>'><?=$this->lang->line('application_yes');?></button> <button class='btn po-close'><?=$this->lang->line('application_no');?></button>" data-original-title="<b><?=$this->lang->line('application_login_token_reset');?></b>" value="<?=$this->lang->line('application_reset_login_token');?>">
		<?php } ?>
		</div>
	 	 
		<?php echo form_close(); ?>
		</div>
	</div></div>
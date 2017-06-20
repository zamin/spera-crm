<div id="row">
		<div class="col-md-3">
			<div class="list-group">
				<?php foreach ($submenu as $name=>$value):
				$badge = "";
				$active = "";
				if($value == "settings/updates"){ $badge = '<span class="badge badge-success">'.$update_count.'</span>';}
				if($name == $breadcrumb){ $active = 'active';}?>
	               <a class="list-group-item <?=$active;?>" id="<?php $val_id = explode("/", $value); if(!is_numeric(end($val_id))){echo end($val_id);}else{$num = count($val_id)-2; echo $val_id[$num];} ?>" href="<?=site_url($value);?>"><?=$badge?> <?=$name?></a>
	            <?php endforeach;?>
			</div>
		</div>


<div class="col-md-9">
		<div class="table-head"><?=$this->lang->line('application_propay');?> <?=$this->lang->line('application_settings');?></div>
		<div class="table-div">
		<?php   
        $attributes = array('class' => '', 'id' => 'propay');
		echo form_open_multipart($form_action, $attributes); 
		?>
		<br>

<div class="form-group"><label><?=$this->lang->line('application_propay_auth_token');?></label>
	<input type="text" name="propay_auth_token" class="form-control" value="<?=$settings->propay_auth_token;?>">
</div>
<div class="form-group"><label><?=$this->lang->line('application_propay_biller_id');?></label>
    <input type="text" name="propay_biller_id" class="form-control" value="<?=$settings->propay_biller_id;?>">
</div>

<div class="form-group"><label><?=$this->lang->line('application_propay_profile_id');?></label>
    <input type="text" name="propay_profile_id" class="form-control" value="<?=$settings->propay_profile_id;?>">
</div>
<div class="form-group">
			<label><?=$this->lang->line('application_propay_currency');?></label>

				<select name="propay_currency" class="formcontrol chosen-select ">
					<?php if($settings->propay_currency != ""){ ?><option value="<?=$settings->propay_currency;?>" selected=""><?=$settings->propay_currency;?></option><?php } ?>
					<option value="USD" title="$">USD</option>
					<option value="AUD" title="$">AUD</option>
					<option value="BRL" title="R$">BRL</option>
					<option value="GBP" title="£">GBP</option>
					<option value="CAD" title="$">CAD</option>
					<option value="CZK" title="">CZK</option>
					<option value="DKK" title="">DKK</option>
					<option value="EUR" title="€">EUR</option>
					<option value="HKD" title="$">HKD</option>
					<option value="HUF" title="">HUF</option>
					<option value="ILS" title="₪">ILS</option>
					<option value="JPY" title="¥">JPY</option>
					<option value="MXN" title="$">MXN</option>
					<option value="TWD" title="NT$">TWD</option>
					<option value="NZD" title="$">NZD</option>
					<option value="NOK" title="">NOK</option>
					<option value="PHP" title="P">PHP</option>
					<option value="PLN" title="">PLN</option>
					<option value="SGD" title="$">SGD</option>
					<option value="SEK" title="">SEK</option>
					<option value="CHF" title="">CHF</option>
					<option value="THB" title="฿">THB</option>
					<option value="TRY" title="TRY">TRY</option>
					
					</select>
</div>
<div class="form-group"><label><?=$this->lang->line('application_propay_apiRoute');?></label>
    <input type="text" name="propay_apiroute" class="form-control" value="<?=$settings->propay_apiroute;?>">
</div>

<div class="form-group no-border">

			 <input type="submit" name="send" class="btn btn-primary" value="<?=$this->lang->line('application_save');?>"/>
</div>
	 	 
		<?php echo form_close(); ?>
	
</div>
	</div>
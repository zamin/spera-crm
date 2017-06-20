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
		<div class="table-head"><?=$this->lang->line('application_payment_account');?> <?=$this->lang->line('application_settings');?></div>
		<div class="table-div"><br>

		<?php   
        $attributes = array('class' => '', 'id' => '_paym_ticketentaccount');
        echo form_open($form_action, $attributes); 
        ?>
        <br>

        <div class="form-group">
            <label for="account_number">Propay Account Number *</label>
            <input id="account_number" type="text" name="account_number" required class="required form-control" value="" maxlength="30"/>
        </div> 
        <div class="form-group">
            <label for="source_email">Propay Username *</label>
            <input id="source_email" type="email" name="source_email" required class="required form-control" value="" />
        </div> 
        
        <div class="form-group no-border">
             <input type="submit" name="send" class="btn btn-primary" value="<?=$this->lang->line('application_save');?>"/>
        </div>

        <?php echo form_close();?>

        </div>
	</div>
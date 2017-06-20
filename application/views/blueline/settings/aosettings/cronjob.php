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
		<div class="table-head"><?=$this->lang->line('application_cronjob');?> <?=$this->lang->line('application_settings');?></div>
		<div class="table-div settings">
		<?php   
		$attributes = array('class' => '', 'id' => 'cronjob');
		echo form_open_multipart($form_action, $attributes); 
		?>
		<br>
		<div class="form-group tt">
		 <label><?=$this->lang->line('application_cronjob_active');?> 
			 <a class="cursor po " data-toggle="popover" data-placement="right" data-content="<?=$this->lang->line('application_cronjob_help');?> <a target='_blank' href='https://luxsys.helpscoutdocs.com/article/16-cronjob-configuration'>More help!</a>" data-original-title="<?=$this->lang->line('application_cronjob_active');?>"><i class="ion-help-circled"></i></a>
		 </label>
		 <input name="cronjob" type="checkbox" class="checkbox" data-labelauty="<?=$this->lang->line('application_cronjob_active');?>" value="1" <?php if($settings->cronjob == "1"){ ?> checked="checked" <?php } ?>>
        </div>
		
		
        <div class="form-group tt" title="<?=$this->lang->line('application_autobackup_help');?>">
		   <label><?=$this->lang->line('application_autobackup');?></label>
		   <input name="autobackup" type="checkbox" class="checkbox" data-labelauty="<?=$this->lang->line('application_autobackup');?>" value="1" <?php if($settings->autobackup == "1"){ ?> checked="checked" <?php } ?>>
        </div>
		
		
        <div class="form-group">
		
		<label><?=$this->lang->line('application_cronjob_address');?>
		<a class="cursor po" href="#" data-toggle="popover" data-placement="right" rel="popover" data-content="<?=$this->lang->line('application_cronjob_address_help');?>" data-original-title="<?=$this->lang->line('application_cronjob_address');?>"><i class="ion-help-circled"></i></a> </label>  
		<br>
		<small style="padding-left:10px"> 
			<span class="tag"><?=base_url()?>cronjob</span>
		</small>
		</div>
		
		
        <div class="form-group">
		<label><?=$this->lang->line('application_last_cronjob_run');?></label> <br>
		<small style="padding-left:10px"> 
			<span class="tag tag--grey"><?php if(!empty($settings->last_cronjob)){echo date("Y-m-d H:i", $settings->last_cronjob); }else {echo "-";}?></span>
		</small>
		</div>
		<div class="form-group no-border">
				<small style="padding-left:10px" class="highlight-text">If cronjobs are not included in your hosting subscription, you can use a free cronjob service like <a href="http://www.easycron.com?ref=18097" target="_blank">Free Cronjob Service</a></small>
		</div>
		
        <div class="form-group no-border">
		<input type="submit" name="send" class="btn btn-primary" value="<?=$this->lang->line('application_save');?>"/>
		</div>
	 	 
		<?php echo form_close(); ?>
		</div>

	</div></div>
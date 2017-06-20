	
	<div class="col-sm-12  col-md-12 main">  
     <div class="row">
			<a href="<?=base_url()?>aosubscriptions/create" class="btn btn-primary" data-toggle="mainmodal"> <?=$this->lang->line('application_new_card_subscription');?></a>
			<?php if( !empty($propay_data) ){?>
			<a href="<?=base_url()?>aosubscriptions/existing" class="btn btn-primary" data-toggle="mainmodal"> <?=$this->lang->line('application_existing_card_subscription');?></a>
			<input type="hidden" name="existing" value="existing" />
			<?php } ?>
			
            <?php /*<div class="btn-group pull-right-responsive margin-right-3">
          <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
            <?php $last_uri = $this->uri->segment($this->uri->total_segments()); if($last_uri != "subscriptions"){echo $this->lang->line('application_'.$last_uri);}else{echo $this->lang->line('application_all');} ?> <span class="caret"></span>
          </button>
          <ul class="dropdown-menu pull-right" role="menu">
            <?php foreach ($submenu as $name=>$value):?>
	                <li><a id="<?php $val_id = explode("/", $value); if(!is_numeric(end($val_id))){echo end($val_id);}else{$num = count($val_id)-2; echo $val_id[$num];} ?>" href="<?=base_url().$value;?>"><?=$name?></a></li>
	            <?php endforeach;?>
          </ul>
          </div>*/?>


		</div>
		<div class="row">
		<div class="table-head"><?=$this->lang->line('application_subscriptions');?></div>
		<div class="table-div">
		<table class="data table" id="subscriptions" rel="<?=base_url()?>" cellspacing="0" cellpadding="0">
		<thead>
			<th class="hidden-xs" width="70px"><?=$this->lang->line('application_user_package');?></th>
			<th class="hidden-xs"><?=$this->lang->line('application_issue_date');?></th>
			<th class="hidden-xs"><?=$this->lang->line('application_end_date');?></th>
			<th class="hidden-xs"><?=$this->lang->line('application_status');?></th>
		</thead>
		<?php foreach ($subscriptions as $value):?>

		<tr id="<?=$value['id'];?>" >
			<td><span class="label label-info"><?php echo $value['type'];?> </span></td>
			<td class="hidden-xs"><span><?php $unix = human_to_unix($value['start_date'].' 00:00'); echo '<span class="hidden">'.$unix.'</span> '; echo date($core_settings->date_format, $unix);?></span></td>
			<td>
				<span class="label <?php if($value['end_date'] < date('Y-m-d') && $value['end_date'] != "" ){ echo ' label-important tt" title="'.$this->lang->line('application_subscription_has_ended'); }elseif($value['end_date'] >= date('Y-m-d')){ echo ' label-success tt" title="'.$this->lang->line('application_unlimited'); } ?>">
					<?php $unix = human_to_unix($value['end_date'].' 00:00'); echo '<span class="hidden">'.$unix.'</span> '; echo date($core_settings->date_format, $unix);?>
				</span>
			</td>
			<td class="hidden-xs"><span class="label <?php if($value['end_date'] < date('Y-m-d') ){echo 'label-important';}elseif($value['end_date'] >= date('Y-m-d') ){echo 'label-success tt';}?>"><?php if($value['end_date'] < date('Y-m-d') ){echo 'Ended';}elseif($value['end_date'] >= date('Y-m-d') ){echo 'Active';}?></span></td>
		</tr>
		<?php endforeach;?>
	 	</table>
	 	</div>
	 	</div>
	</div>
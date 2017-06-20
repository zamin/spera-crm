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
        if( empty($accounts) )
        {
        $attributes = array('class' => '', 'id' => '_paym_ticketentaccount');
        echo form_open($form_action, $attributes); 
        ?>
        <br>

        <div class="form-group">
            <label for="fname">First Name *</label>
            <input id="fname" type="text" name="fname" required class="required form-control" value="" maxlength="20"/>
        </div> 
        <div class="form-group">
            <label for="lname">Last Name *</label>
            <input id="lname" type="text" name="lname" required class="required form-control" value="" maxlength="25"/>
        </div> 
        <div class="form-group">
            <label for="addr1">Address *</label>
            <input id="addr1" type="text" name="addr1" required class="required form-control" value="" maxlength="100"/>
        </div> 
        <div class="form-group">
            <label for="apt">Address 2</label>
            <input id="apt" type="text" name="apt" class="form-control" value="" maxlength="100"/>
        </div> 

        <div class="form-group">
            <label for="city">City</label>
            <input id="city" type="text" name="city" class="form-control" value="" maxlength="20"/>
        </div> 
        <div class="form-group">
            <label for="state">State</label>
            <select class="chosen-select" name="state" id="state" style="width:100%" >
                <option value="">-</option>
                <?php foreach ($states as $value){?>
                <option value="<?php echo $value->state_code;?>"><?php echo $value->state;?></option>
                <?php }?>
            </select>
        </div> 

        <div class="form-group">
            <label for="zip">Zip Code</label>
            <input id="zip" type="text" name="zip" class="form-control" value="" maxlength="20"/>
        </div> 

        <div class="form-group">
            <label for="dayphone">Day Phone *</label>
            <input id="dayphone" data-mask="9999999999" placeholder="1234567890" required="" name="dayphone" class="form-control no_radius" data-parsley-id="22" type="tel">
        </div>
        <div class="form-group">
            <label for="evenphone">Evening Phone *</label>
            <input id="evenphone" data-mask="9999999999" placeholder="1234567890" required="" name="evenphone" class="form-control no_radius" data-parsley-id="24" type="tel">
        </div>
        <div class="form-group">
            <label for="dob">Date of Birth *</label>
            <input required="" data-parsley-errors-container=".datevalidation" class="form-control datepicker not-required flatpickr-input" name="dob" placeholder="The person must be at least 18 years old to obtain an account." id="dob" data-parsley-id="26" type="text">
        </div>
        <div class="form-group">
            <label for="source_email">Source Email *</label>
            <input id="source_email" data-parsley-maxlength="55" required="" name="source_email" class="form-control" data-parsley-id="28" type="email">
        </div>
        <div class="form-group">
            <label for="ssn">SSN</label>
            <input id="ssn" data-mask="999999999" placeholder="123456789" required="" name="ssn" class="form-control no_radius" data-parsley-id="30" type="text">
        </div>

        <div class="form-group no-border">
             <input type="submit" name="send" class="btn btn-primary" value="<?=$this->lang->line('application_save');?>"/>
        </div>

        <?php echo form_close();}else{ ?>
            <br>
            <table class="data table" cellspacing="0" cellpadding="0">
            <thead>
                <th class="hidden-xs" width="70px">Account number</th>
                <th class="hidden-xs">Profile id</th>
                <th class="hidden-xs">Account in-use</th>
                <th class="hidden-xs">Action</th>
            </thead>
            <?php foreach ($accounts as $value):?>

            <tr >
                <td><span class="label label-info"><?php echo $value['account_number'];?> </span></td>
                <td><span class="label label-info"><?php echo $value['profile_id'];?> </span></td>
                <td><span class="label label-info"><?php if($value['is_default']){echo 'In use';}else{echo 'Not in use';}?> </span></td>
                <td>
                    <button type="button" class="btn-option delete po" data-toggle="popover" data-placement="left" data-content="<a class='btn btn-danger po-delete ' href='<?=base_url()?>aosettings/paymentaccountdelete/<?=$value['spera_accounts_id'];?>'><?=$this->lang->line('application_yes_im_sure');?></a> <button class='btn po-close'><?=$this->lang->line('application_no');?></button> <input type='hidden' name='td-id' class='id' value='<?=$value['spera_accounts_id'];?>'>" data-original-title="<b><?=$this->lang->line('application_really_delete');?></b>"><i class="fa fa-times"></i></button>
                </td>
            </tr>
            <?php endforeach;?>
             </table>
            
        <?php }?>

        </div>
	</div>
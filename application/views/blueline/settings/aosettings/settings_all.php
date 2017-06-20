<div id="row">
		<div class="col-md-3">
			<div class="list-group">
				<?php foreach ($submenu as $name=>$value):
				//echo print_r($value);
				$badge = "";
				$active = "";
				if($value == "aosettings/updates" && $update_count){ $badge = '<span class="badge badge-success">'.$update_count.'</span>';}
				if($name == $breadcrumb){ $active = 'active';}?>
	               <a class="list-group-item <?=$active;?>" id="<?php $val_id = explode("/", $value); if(!is_numeric(end($val_id))){echo end($val_id);}else{$num = count($val_id)-2; echo $val_id[$num];} ?>" href="<?=$value;?>"><?=$badge?> <?=$name?></a>
	            <?php endforeach;?>
			</div>
		</div>

<div class="col-md-9">
<div class="table-head"><?=$this->lang->line('application_settings');?></div>
<?php   
$attributes = array('class' => '', 'id' => 'settings_form');
echo form_open_multipart($form_action, $attributes); 
?>
<div class="table-div">
	<div class="form-header"><?=$this->lang->line('application_personal_info');?></div>
	<div class="row">
		<div class="col-md-6">
				<div class="form-group">
					<label><?=$this->lang->line('application_company_name');?></label>
					<input type="text" name="company" class="required form-control" value="<?=$company->name;?>" required>
				</div>
		</div>
		<div class="col-md-6">
				<div class="form-group">
					<label><?=$this->lang->line('application_contact');?></label>
					<input type="text" name="invoice_contact" class="required form-control" value="<?=$settings->contact;?>" required>
				</div>
		</div>
		<div class="col-md-6">
				<div class="form-group">
					<label><?=$this->lang->line('application_address');?></label>
					<input type="text" name="invoice_address" class="required form-control" value="<?=$settings->address;?>" required>
				</div>
		</div>
		<div class="col-md-6">
				<div class="form-group">
					<label>Country</label>
					<input type="text" name="country" class="required form-control" value="<?=$settings->country;?>" required>
				</div>
		</div>
		<div class="col-md-6">
				<div class="form-group">
					<label>State</label>
					<input type="text" name="state" class="required form-control" value="<?=$settings->state;?>" required>
				</div>
		</div>
		<div class="col-md-6">
				<div class="form-group">
					<label><?=$this->lang->line('application_city');?></label>
					<input type="text" name="invoice_city" class="required form-control" value="<?=$settings->city;?>" required>
				</div>
		</div>
		<div class="col-md-6">
				<div class="form-group">
					<label>Zipcode</label>
					<input type="text" name="zipcode" class="required form-control" value="<?=$settings->zipcode;?>" required>
				</div>
		</div>
		<div class="col-md-6">
				<div class="form-group">
					<label><?=$this->lang->line('application_phone');?></label>
					<input type="text" name="invoice_tel" class="required form-control" value="<?=$settings->phone;?>" required>
				</div>
		</div>
		<div class="col-md-6">
					<div class="form-group">
					<label><?=$this->lang->line('application_email');?></label>
					<input type="text" name="email" class="required form-control" value="<?=$this->user->email;?>"  disabled=disabled>
				</div>
		</div>
		<div class="col-md-6">
				<div class="form-group">
					<label><?=$this->lang->line('application_domain');?></button>
					</label>
					<input type="text" name="domain" class="required form-control" value="<?=$settings->domain;?>" required>
				</div>
		</div>
	</div>
	<?php /*
	<div class="form-header"><?=$this->lang->line('application_branding');?></div>
	<div class="form-group">
            
		<label><?=$this->lang->line('application_logo');?> (max 160x200) 
                    <?php if($settings->logo==""){ ?>
                     <button type="button" class="btn-option po pull-right" data-toggle="popover" data-placement="right" data-content="<div class='logo' style='padding:10px'><img src='<?=site_url().$core_settings->logo;?>'></div>" data-original-title="<?=$this->lang->line('application_logo');?>"> <i class="ion-eye"></i></button>
                    <?php }else{?>
                     <button type="button" class="btn-option po pull-right" data-toggle="popover" data-placement="right" data-content="<div class='logo' style='padding:10px'><img src='<?= site_url().$settings->logo;?>'></div>" data-original-title="<?=$this->lang->line('application_logo');?>"> <i class="ion-eye"></i></button>
                    <?php } ?>
		</label>
                <div><input id="uploadFile" class="form-control uploadFile" placeholder="Choose File" disabled="disabled" />
                          <div class="fileUpload btn btn-primary">
                              <span><i class="fa fa-upload"></i><span class="hidden-xs"> <?=$this->lang->line('application_select');?></span></span>
                              <input id="uploadBtn" type="file" name="userfile" class="upload" />
                          </div>
        </div>
                	
	</div>
	*/ ?>
	<!-- <div class="form-group">
		<label><?=$this->lang->line('application_invoice');?> <?=$this->lang->line('application_logo');?> (max 160x200)
		</label>
                <div><input id="uploadFile2" class="form-control uploadFile" placeholder="Choose File" disabled="disabled" />
                          <div class="fileUpload btn btn-primary">
                              <span><i class="fa fa-upload"></i><span class="hidden-xs"> <?=$this->lang->line('application_select');?></span></span>
                              <input id="uploadBtn2" type="file" name="userfile2" class="upload" />
                          </div>
        </div>
                	
	</div> -->
	<div class="form-header"><?=$this->lang->line('application_tax_settings');?></div>
	<div class="row">
		<div class="col-md-6">
			<div class="form-group">
				<label><?=$this->lang->line('application_tax');?></label>
					<div class="input-group">
					  <span class="input-group-addon">%</span>
					  <input type="text"  name="tax"  value="<?=$settings->tax;?>" class="form-control" placeholder="">
					</div>
				
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
				<label><?=$this->lang->line('application_second_tax');?></label>
					<div class="input-group">
					  <span class="input-group-addon">%</span>
					  <input type="text"  name="second_tax"  value="<?=$settings->second_tax;?>" class="form-control" placeholder="">
					</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
				<label><?=$this->lang->line('application_vat');?></label>
					<div class="input-group col-md-12">  
					  	<input type="text"  name="vat"  value="<?=$settings->vat;?>" class="form-control" placeholder="">
					</div>
				
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
				<label><?=$this->lang->line('application_default_currency');?></label>
					<div class="input-group col-md-12">
					  <input type="text"  name="currency" list="currencylist" class="form-control" value="<?=$settings->currency;?>">
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
				
			</div>
		</div>
	</div>
		<div class="form-group no-border">
			 <input type="submit" name="send" class="btn btn-primary" value="<?=$this->lang->line('application_save');?>"/>
			
		</div> 
	
	<?php echo form_close(); ?>
	</div>
	</div>

	</div>

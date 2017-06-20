<?php $attributes = array('class' => 'form-signin', 'role'=> 'form', 'id' => 'resetpass'); ?>
<?=form_open('resetpass', $attributes)?>
<div class="logo"><img src="<?=base_url()?><?php if($core_settings->login_logo == ""){ echo $core_settings->invoice_logo;} else{ echo $core_settings->login_logo; }?>" alt="<?=$core_settings->company;?>"></div>
<?php if($this->session->flashdata('message')) { $exp = explode(':', $this->session->flashdata('message')); ?>
	<div class="resetpass-success">
	  <?=$exp[1]?>
	</div>
<?php }else{ ?>
	<div class="header"><?=$this->lang->line('application_new_password');?><hr></div>
	
	<div class="col-sm-12  col-md-12 main"> 
		
		<div class="form-group">
			<label for="password"><?=$this->lang->line('application_password');?> *</label>
			<input id="password" type="password" name="password" class="form-control" required/>
		</div>
		<div class="form-group">
			<label for="confirm_password"><?=$this->lang->line('application_confirm_password');?> *</label>
			<input id="confirm_password" type="password" class="form-control" data-match="#password" required/>
		</div>
		<div class="modal-footer">
			<input type="hidden" name="user_id" value="<?php echo $userdata['user_id']; ?>" />
			<input type="hidden" name="company_id" value="<?php echo $userdata['company_id']; ?>" />
			<input type="hidden" name="email" value="<?php echo $userdata['email']; ?>" />
			<input type="hidden" name="token" value="<?php echo $userdata['token']; ?>" />
			<input type="submit" name="send" class="btn btn-primary" value="<?=$this->lang->line('application_save');?>"/>
		</div>
		<div class="left"><a href="<?=site_url("login");?>"><?=$this->lang->line('application_go_to_login');?></a></div>
	</div>
		
<?php } ?>
          
<?=form_close()?>
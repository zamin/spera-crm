<div class="form-signin">
	<div class="logo">
	<?php 
		$image_path = '';
		$image_path .= base_url();
		$image_path .= ($core_settings->login_logo == "")?$core_settings->invoice_logo:$core_settings->login_logo;
	?>
	<img src="<?php echo $image_path?>" alt="<?php echo $core_settings->company;?>">
	</div>
	<div class="row">
	<?php if( !empty( $token_expired ) ){ ?>
		<div class="col-sm-12  col-md-12 main"> 
			<div class="header"><?=$token_expired;?><hr></div>
		</div>
	<?php } else { ?>
	
		
		<div class="header"><?=$this->lang->line('application_enter_password_to_complete_invitation_process');?><hr></div>
		<?php echo form_open_multipart($form_action, $attributes); ?>
		<div class="col-sm-12  col-md-12 main"> 
			<div class="form-group">
				<label for="firstname"><?=$this->lang->line('application_firstname');?> *</label>
				<input id="firstname" type="text" name="firstname" class="form-control" value="<?php echo (!empty($user_data['firstname'])) ? $user_data['firstname'] : ''; ?>" />
			</div>
			<div class="form-group">
				<label for="lastname"><?=$this->lang->line('application_lastname');?> *</label>
				<input id="lastname" type="text" name="lastname" class="form-control" value="<?php echo (!empty($user_data['lastname'])) ? $user_data['lastname'] : ''; ?>" />
			</div>
			<div class="form-group">
				<label for="email"><?=$this->lang->line('application_email');?> *</label>
				<input id="email" type="email" name="email" class="disabled email form-control" value="<?php echo (!empty($user_data['email'])) ? $user_data['email'] : ''; ?>" disabled />
			</div>
		
			<div class="form-group">
				<label for="password"><?=$this->lang->line('application_password');?> *</label>
				<input id="password" type="password" name="password" class="form-control" required/>
			</div>
			<div class="form-group">
				<label for="confirm_password"><?=$this->lang->line('application_confirm_password');?> *</label>
				<input id="confirm_password" type="password" class="form-control" data-match="#password" required/>
			</div>
			<div class="modal-footer">
				<input type="hidden" name="invite_url" value="<?php echo $invite_url; ?>" />
				<input type="hidden" name="user_id" value="<?php echo $user_data['id']; ?>" />
				<input type="hidden" name="company_id" value="<?php echo $company_id; ?>" />
				<input type="hidden" name="user_roles_id" value="<?php echo $user_roles_id; ?>" />
				<input type="submit" name="send" class="btn btn-primary" value="<?=$this->lang->line('application_save');?>"/>
			</div>
		</div>
		<?php echo form_close(); ?>
	<?php } ?>
	</div>
</div>
<?php $attributes = array('class' => 'form-signin', 'role'=> 'form', 'id' => 'forgotpass'); ?>
<?=form_open('forgotpass', $attributes)?>
        <div class="logo"><img src="<?=base_url()?><?php if($core_settings->login_logo == ""){ echo $core_settings->invoice_logo;} else{ echo $core_settings->login_logo; }?>" alt="<?=$core_settings->company;?>"></div>
        <?php if($this->session->flashdata('message')) { $exp = explode(':', $this->session->flashdata('message')); ?>
            <div class="forgotpass-success">
              <?=$exp[1]?>
            </div>
        <?php }else{ ?>
		<div class="form-forgot-error"></div>
		<div class="ajax-loader"></div>
          <div class="forgotpass-info"><?=$this->lang->line('application_identify_account');?></div>
          
          <div class="form-group">
            <label for="emailid"><?=$this->lang->line('application_email');?></label>
            <input type="text" class="form-control" name="emailid" id="emailid" placeholder="<?=$this->lang->line('application_email');?>">
          </div>
		  <div id="showusertypes"></div>

          <input type="hidden" name="forgot_pass" value="" />
          <input type="submit" class="btn btn-primary" value="<?=$this->lang->line('application_reset_password');?>" />
          <?php } ?>
          <div class="forgotpassword"><a href="<?=site_url("login");?>"><?=$this->lang->line('application_go_to_login');?></a></div>
<?=form_close()?>
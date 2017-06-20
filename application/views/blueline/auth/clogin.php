<?php $attributes = array('class' => 'form-signin', 'role'=> 'form', 'id' => 'clogin'); ?>

<?=form_open($form_action, $attributes)?>
        <div class="logo"><img src="<?=base_url()?><?php if($settings->login_logo == ""){ echo $core_settings->login_logo;} else{ echo $settings->login_logo; }?>" alt="<?=$core_settings->company;?>"></div>
        <?php if($error == "true") { $message = explode(':', $message)?>
            <div id="error">
              <?=$message[1]?>
            </div>
        <?php } ?>
       <div class="form-group">
            <label for="email"><?=$this->lang->line('application_email');?></label>
            <input type="email" class="form-control" id="email" name="email" placeholder="<?=$this->lang->line('application_enter_your_username');?>" />
          </div>

		  <div id="showusertypes"></div>
          <div class="form-group">
            <label for="password"><?=$this->lang->line('application_password');?></label>
            <input type="password" class="form-control" id="password" name="password" placeholder="<?=$this->lang->line('application_enter_your_password');?>" />
          </div>

          <input type="submit" class="btn btn-primary" id="csubmitbutton" value="<?=$this->lang->line('application_login');?>" />
          <div class="forgotpassword"><a href="<?=site_url("forgotpass");?>"><?=$this->lang->line('application_forgot_password');?></a></div>

          <div class="sub">
           <?php if($core_settings->registration == 1){ ?><div class="small"><small><?=$this->lang->line('application_you_dont_have_an_account');?></small></div><hr/><a href="<?=site_url("register");?>" class="btn btn-success"><?=$this->lang->line('application_create_account');?></a> <?php } ?>
          </div>
<?php echo form_close();?>


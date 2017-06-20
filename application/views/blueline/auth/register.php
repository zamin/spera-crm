<?php /* This form displays Registration Page */ ?>
<?php $attributes = array('class' => 'form-signin form-register', 'role'=> 'form', 'id' => 'register'); ?>
<?=form_open($form_action, $attributes)?>
        <div class="logo">
           <?php 
                $image_path = '';
                $image_path .= base_url();
                $image_path .= ($core_settings->login_logo == "")?$core_settings->invoice_logo:$core_settings->login_logo;
           ?>
            <img src="<?php echo $image_path?>" alt="<?php echo $core_settings->company;?>">
        </div>
        <?php if($error != 'false') { ?>
            <div id="error" style="display:block">
              <?=$error?>
            </div>
        <?php } ?>
<div class="row">
<div class="header"><?=$this->lang->line('application_enter_your_details_to_create_an_account');?><hr></div>
  <div class="col-md-12"> 
	<div class="form-group">
            <label for="firstname"><?=$this->lang->line('application_firstname');?> *</label>
            <input id="firstname" type="text" name="firstname" class=" form-control" value="<?php if(isset($registerdata)){echo $registerdata['firstname'];}?>" required/>
    </div>
    <div class="form-group">
            <label for="lastname"><?=$this->lang->line('application_lastname');?> *</label>
            <input id="lastname" type="text" name="lastname" class="required form-control" value="<?php if(isset($registerdata)){echo $registerdata['lastname'];}?>" required/>
    </div>
	<div class="form-group <?php if(isset($registerdata['email'])){echo 'has-error';} ?>">
            <label for="email"><?=$this->lang->line('application_email');?> *</label>
            <input id="email" type="email" name="email" class="required email form-control" value="<?php if(isset($registerdata)){echo $registerdata['email'];}?>" required/>
    </div>
    <div class="form-group <?php if(isset($registerdata['company_name'])){echo 'has-error';} ?>">
            <label for="company_name"><?=$this->lang->line('application_company');?> <?=$this->lang->line('application_name');?> *</label>
            <input id="company_name" type="text" name="company_name" class="required form-control" value="<?php if(isset($registerdata)){echo $registerdata['company_name'];} ?>"  required/>
    </div>
    
	<?php
        //echo "<pre>";print_r($packages);exit;
        if(!empty($packages)) { ?>

		<div class="form-group" id="package_div">
        <label for="package"><?=$this->lang->line('application_package');?> *</label>
        <?php 
                $options = array();
                $options[''] = '-';
                foreach ($packages as $value):  
			$options['monthly-tier'.$value->id] = 'Monthly '.$value->name.' : $'.$value->amount;
                endforeach;
                foreach($packages as $value)
                {
                    $yearly_amount=round(($value->amount)*12);
                    $yd_amount=(($yearly_amount/100)*$value->discount);
                    $y_main_amount=$yearly_amount-$yd_amount;
                    $options['yearly-tier'.$value->id] = 'Annual '.$value->name.' : $'.$y_main_amount;
                }
                if(isset($package_name) && !empty($package_name))
                {
                    $c = $package_name;
                    echo "<input type='hidden' name='s_package' value='".$package_name."'>";
                }
                else
                {
                    if(isset($registerdata))
                    {
                        if(empty($registerdata['s_package']))
                        {
                            $c =$registerdata['package'];
                            echo "<input type='hidden' name='s_package' value=''>";
                        }
                        else
                        {
                            $c =$registerdata['s_package'];
                            echo "<input type='hidden' name='s_package' value='".$registerdata['s_package']."'>";
                        }
                    }
                    else
                    {
                        $c ="";
                        echo "<input type='hidden' name='s_package' value=''>";
                    }
                }
                echo form_dropdown('package', $options, $c, 'style="width:100%" name="package" id="package" class="required chosen-select form-control"');
        ?>
		</div>

    <?php } ?>
	<div class="form-group <?php if(isset($registerdata['promo_code'])){echo 'has-error';} ?>">
      <label for="promo_code"><?=$this->lang->line('application_promo_code');?></label>
      <input id="promo_code" type="text" name="promo_code" class="form-control" value="<?php if(isset($registerdata)){echo $registerdata['promo_code'];} ?>" />
    </div>
    <div class="form-group">
        <label for="password"><?=$this->lang->line('application_password');?> *</label>
        <input id="password" type="password" name="password" class="form-control" value="" required />
    </div>
    <div class="form-group">
            <label for="password"><?=$this->lang->line('application_confirm_password');?> *</label>
            <input id="confirm_password" type="password" class="form-control" data-match="#password" required />
    </div>

    <?php   $number1 = rand(1, 10);
            $number2 = rand(1, 10);
            $captcha = $number1+$number2;

            //captcha
          $html_fields = '<input type="hidden" id="captcha" name="captcha" value="'.$captcha.'"><div class="form-group">';
          $html_fields .= '<label class="control-label-e">'.$number1.'+'.$number2.' = ?</label>';
          $html_fields .= '<input type="text" id="confirmcaptch" name="confirmcaptcha" data-match="#captcha" class="form-control" required/></div>';
          echo $html_fields;
    ?>
  </div>
 
</div>

<div class="row">
  <div class="col-md-12">
          
         <input id="register_submit" type="submit" class="btn btn-success" value="<?=$this->lang->line('application_send');?>" />
         <a class="login-link" href="<?=site_url("login");?>"><?=$this->lang->line('application_go_to_login');?></a>
  </div>
</div>
<?=form_close()?>

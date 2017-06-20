<?php   
$attributes = array('class' => '', 'id' => '_clients', 'autocomplete' => 'off');
echo form_open_multipart($form_action, $attributes); 
?>
<div class="ajax-loader"></div>
	<?php 
	// $profile_image = null;
	if(isset($user)) { 
		
		// $profile_pic = base_url().'files/media/'.$user->userpic;
		// $profile_pic_path = FCPATH.'/files/media/'.$user->userpic;
		// $profile_image = 'no-image';
		// if( !empty($user->userpic) && file_exists($profile_pic_path) ) {
			// $profile_image = '<img src="'. $profile_pic .'" />';
		// }
	} else {
		// $role = $this->uri->segment(3);
		// $role_id = null;
		// if($role == 'client') {
			// $role_id = 3;
		// } else if($role == 'subcontractor') {
			// $role_id = 4;
		// } 
	?>
	
		<input type="hidden" name="role_id" value="3" />
		<script type="text/javascript">
			$(document).ready(function() {
				/// make loader hidden in start
				// $('#loading').hide(); 
				$('#email').on('input', function(){
                                    
					var email_val = $("#email").val();
					var filter = /^[a-zA-Z0-9]+[a-zA-Z0-9_.-]+[a-zA-Z0-9_-]+@[a-zA-Z0-9]+[a-zA-Z0-9.-]+[a-zA-Z0-9]+.[a-z]{2,4}$/;
					if(filter.test(email_val)){
						// show loader
						$('#_clients > .ajax-loader').show();
						$.get("<?php echo base_url()?>clients/email_check", {
							email: email_val
						}, function(response){						
							result = jQuery.parseJSON(response);
							$('#_clients > .ajax-loader').hide();
							$('#_clients > .ajax-loader').hide();
							if(result.message != '') {
								$('.form-email-error').html($('#email').val()+" "+result.message);
								//$('#email').val('');
								if(result.message == 'This email is already taken, choose another one'){
									$('#email').val('');
								}
							}
						});
						return false;
					}
				});
				$('#email').on('input', function() {
					$('.form-email-error').html('');
				});
			});   
		</script>
	<?php } ?>
        <?php 
        echo "<input type='hidden' id='name_of_company' value='".$name_of_company."'>"; 
        echo "<input type='hidden' id='m_company_id' value='".$company_id."'>";
        echo "<input type='hidden' id='m_owner_id' value='".$owner_id."'>";
        ?>
	<div class="form-group" id="client_c_div">
		<label for="company"><?=$this->lang->line('application_company');?> *</label>
                <?php
                $options = array(); 
                $options[''] = '';
                if(empty($client_companies))
                {
                    $options[$name_of_company]=$name_of_company;
                }
                else
                {
                    if(!empty($user->user_id))
                    {
                        $c=$get_client_assign_company;
                    }
                    else
                    {
                        $c='';
                    }
                    $options[$name_of_company]=$name_of_company;
                    foreach($client_companies as $client_company)
                    {
                        $options[$client_company['sub_company']]=ucfirst($client_company['sub_company']);
                    }
                }
                $options['other']='Other';
                echo form_dropdown('sub_c_id', $options,$c, ' class="required chosen-select form-control" id="sub_c_id"');?>
	</div>
        <div class="form-group" id="client_c_text" style="display:none;"></div>
        <div id="company_error" class="form-email-error1"></div>
        <div class="form-group">
		<label for="firstname"><?=$this->lang->line('application_firstname');?> *</label>
		<input id="firstname" type="text" name="firstname" class="form-control" value="<?php echo (!empty($user->firstname)) ? $user->firstname : ''; ?>" required/>
	</div>
	<div class="form-group">
		<label for="lastname"><?=$this->lang->line('application_lastname');?> *</label>
		<input id="lastname" type="text" name="lastname" class="required form-control" value="<?php echo (!empty($user->lastname)) ? $user->lastname : ''; ?>" required/>
	</div>
	<div class="form-group">
		<label for="email"><?=$this->lang->line('application_email');?> *</label>
		<input id="email" type="email" name="email" class="required email form-control" value="<?php echo (!empty($user->email)) ? $user->email : ''; ?>" <?php echo (!empty($user->email)) ? 'disabled' : ''; ?> required/>
	</div>
	<div class="form-email-error"></div>
	
	<?php if(isset($user)) { ?>
		<input id="id" type="hidden" name="id" value="<?=$user->user_id;?>" />
	
	
		<?php /* ?>
		<div class="form-group">
            <label for="title"><?=$this->lang->line('application_title');?> </label>
            <input id="title" type="text" name="title" class="required form-control" value="<?php if(isset($registerdata)){echo $registerdata['title'];} ?>"  required/>
		</div>
		
		<div class="form-group">
			<label for="username"><?=$this->lang->line('application_username');?> *</label>
			<input id="username" type="text" name="username" class="form-control" value="" required/>
		</div>
		<?php */ ?>
		<div class="form-group">
			<label for="password"><?=$this->lang->line('application_password');?> <?php echo (empty($user)) ? '*' : ''; ?></label>
			<input id="password" type="password" name="password" class="form-control" <?php echo (empty($user)) ? 'required' : ''; ?>/>
		</div>
		<div class="form-group">
			<label for="confirm_password"><?=$this->lang->line('application_confirm_password');?> <?php echo (empty($user)) ? '*' : ''; ?></label>
			<input id="confirm_password" type="password" class="form-control" data-match="#password" <?php echo (empty($user)) ? 'required' : ''; ?>/>
		</div>
		
	<?php } ?>	
		
		
		<?php /* ?>
		<div class="form-group">
			<?php echo $profile_image; ?>
			<label for="userfile"><?=$this->lang->line('application_profile_picture');?></label>
			<div>
				<input id="uploadFile" class="form-control uploadFile" placeholder="Choose File" disabled="disabled" />
				<div class="fileUpload btn btn-primary">
					<span><i class="fa fa-upload"></i><span class="hidden-xs"> <?=$this->lang->line('application_select');?></span></span>
					<input id="uploadBtn" type="file" name="userfile" class="upload" />
				</div>
			</div>
		</div>
		<?php */ ?>

	<?php /* if( !empty( $modules ) ){ ?>
		<div class="form-group">
			<label><?=$this->lang->line('application_module_access');?></label>
			<ul class="accesslist">
				<?php foreach ($modules as $key => $value) { ?>
					<li> <input type="checkbox" class="checkbox" id="r_<?=$value->link;?>" name="access[]" value="<?=$value->id;?>" <?php if(in_array($value->id, $access)){ echo 'checked="checked"';}?> data-labelauty="<?=$this->lang->line('application_'.$value->link);?>"> </li>
				<?php } ?>
			</ul>
		</div>
	<?php } */ ?>
	<?php if(isset($view)){ ?>
		<input id="view" type="hidden" name="view" value="true" />
	<?php } ?>
	<div class="modal-footer">
		<input type="submit" id="client_submit" name="send" class="btn btn-primary" value="<?=$this->lang->line('application_save');?>"/>
		<a class="btn" data-dismiss="modal"><?=$this->lang->line('application_close');?></a>
	</div>
<?php echo form_close(); ?>
<script>
$(".chosen-select").find("option").eq(0).removeAttr('selected');
$(document).ready(function(){
   $('#sub_c_id').change(function(){
        var sub_c_id=$(this).val();
        if(sub_c_id != '')
        {
            if(sub_c_id=='other')
            {
                //$('#client_c_div').hide();
                $('#client_c_text').html('<label for="company"><?=$this->lang->line('application_company');?> *</label><input id="c_text" type="text" name="c_name" class="form-control">');
                //$('#client_c_text').after('<a id="client_company_submit" class="btn btn-success" href="#">TEst</a>');
                $('#client_c_text').show();
            }
            else
            {
                $('#client_c_text').hide();
            }
        }
   });
   $('#_clients #client_submit').on('click', function () {
        var sub_c_id=$('#sub_c_id').val();
        if(sub_c_id=='')
        {
            $('#client_c_div').addClass('error1');
        }
        else
        {
            $('#client_c_div').removeClass('error1');
            if(sub_c_id=='other')
            {
                var c_text=$('#c_text').val();
                if(c_text=='')
                {
                    $('#client_c_text').addClass('error1');
                }
                else
                {
                    $('#client_c_text').removeClass('error1');
                }
            }
        }
   });
    $('#_clients').on('blur','#c_text',function(){
        var main_company=$('#name_of_company').val();
        var sub_company=$('#c_text').val();
        if(sub_company !='')
        {
            if(sub_company==main_company)
            {
                $('#c_text').val('');
                $('#company_error').html("Your company name same as main company so please choose different company name");
            }
            else
            {
                var owner_id=$('#m_owner_id').val();
                $.ajax({
                   type:"GET",
                   cache:false,
                   url:"<?php echo base_url(); ?>clients/company_name_check",
                   data:{"sub_company":sub_company,"owner_id":owner_id},
                   success:function(response3){
                       if(response3 !='')
                       {
                           if(response3==1)
                           {
                                $('#c_text').val('');
                                $('#company_error').html("Entered company name already exits please choose different company name");
                           }
                           else
                           {
                               $('#company_error').html("");
                           }
                       }
                   },
                   error:function()
                   {
                       alert('error in ajax');
                   }
                });
            }
        }
   });
});

</script>
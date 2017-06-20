<?php   
$attributes = array('class' => '', 'id' => '_company', 'autocomplete' => 'off');
echo form_open_multipart($form_action, $attributes); 
?>
<div class="ajax-loader"></div>
                <input type="hidden" id="client_id" name="client_id" value="<?php echo $client_id; ?>" />
                <input type="hidden" id="sub_id" name="sub_id" value="<?php echo $c_id; ?>" />
                <input type="hidden" id="owner_id" name="owner_id" value="<?php echo $owner_id; ?>" />
                <input type="hidden" id="mc_id"  value="<?php echo $company_id; ?>" />
                <input type='hidden' id="name_of_company" value="<?php echo $name_of_company; ?>" >
        <div class="form-group">
		<label for="sub_company"><?=$this->lang->line('application_company');?> *</label>
		<input id="sub_company" type="text" name="sub_company" maxlength="50" class="form-control" value="<?php echo (!empty($user->sub_company)) ? $user->sub_company : ''; ?>" required/>
	</div>
        <div id="company_error" class="form-email-error1"></div>
	<div class="form-group">
		<label for="cemail"><?=$this->lang->line('application_email');?> *</label>
		<input id="cemail" type="email" name="email" class="required email form-control" maxlength="50" value="<?php echo (!empty($user->email)) ? $user->email : ''; ?>" required/>
	</div>
        <div class="form-email-error"></div>
        <div class="form-group">
		<label for="website"><?=$this->lang->line('application_website');?> *</label>
		<input id="website" type="text" name="website"  class="required form-control" value="<?php echo (!empty($user->website)) ? $user->website : ''; ?>" required/>
	</div>
	<div class="form-group">
		<label for="phone"><?=$this->lang->line('application_phone');?> *</label>
                <input id="phone" type="text" name="phone" class="form-control" maxlength="15" onkeyup="addDashes(this)" value="<?php echo (!empty($user->phone)) ? $user->phone : ''; ?>" required/>
	</div>
        <div class="form-group">
		<label for="fax"><?=$this->lang->line('application_fax');?> *</label>
		<input id="fax" type="text" name="fax" maxlength="15" onkeyup="addDashes(this)" class="form-control" value="<?php echo (!empty($user->fax)) ? $user->fax : ''; ?>" required/>
	</div>
	<div class="modal-footer">
		<input type="submit" id="company_submit" name="send" class="btn btn-primary" value="<?=$this->lang->line('application_save');?>"/>
		<a class="btn" data-dismiss="modal"><?=$this->lang->line('application_close');?></a>
	</div>
<?php echo form_close(); ?>
<script type="text/javascript">
                        function addDashes(f) {
                            var r = /(\D+)/g,
                                npa = '',
                                nxx = '',
                                last4 = '';
                            f.value = f.value.replace(r, '');
                            npa = f.value.substr(0, 3);
                            nxx = f.value.substr(3, 3);
                            last4 = f.value.substr(6, 4);
                            f.value = npa + '-' + nxx + '-' + last4;
                        }
			$(document).ready(function() {
                                
				$('#cemail').on('input', function(){
                                   
					var email_val = $("#cemail").val();
                                        var owner_id = $("#owner_id").val();
                                        //alert(email_val);
					var filter = /^[a-zA-Z0-9]+[a-zA-Z0-9_.-]+[a-zA-Z0-9_-]+@[a-zA-Z0-9]+[a-zA-Z0-9.-]+[a-zA-Z0-9]+.[a-z]{2,4}$/;
					if(filter.test(email_val)){
						// show loader
						$('#_company > .ajax-loader').show();
						$.get("<?php echo base_url()?>clients/company_email_check", {
							email: email_val,owner_id:owner_id
						}, function(response){						
							result = jQuery.parseJSON(response);
							$('#_company > .ajax-loader').hide();
							if(result.message != '') {
								$('.form-email-error').html($('#cemail').val()+" "+result.message);
								//$('#email').val('');
								if(result.message == 'This email is already taken, choose another one'){
									$('#cemail').val('');
								}
							}
						});
						return false;
					}
				});
				$('#cemail').on('input', function() {
					$('.form-email-error').html('');
				});
                                
                                $('#_company').on('blur','#sub_company',function(){
        var main_company=$('#name_of_company').val();
        var sub_company=$('#sub_company').val();
        if(sub_company !='')
        {
            if(sub_company==main_company)
            {
                $('#sub_company').val('');
                $('#company_error').html("Your company name same as main company so please choose different company name");
            }
            else
            {
                var owner_id=$('#owner_id').val();
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
                                $('#sub_company').val('');
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
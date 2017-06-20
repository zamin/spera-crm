<?php 
/**
 * @file        Login View
 * @author      Luxsys <support@spera-crm.com>
 * @copyright   By Luxsys (http://www.spera-crm.com)
 * @version     2.5.0
 */
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <META Http-Equiv="Cache-Control" Content="no-cache">
    <META Http-Equiv="Pragma" Content="no-cache">
    <META Http-Equiv="Expires" Content="0">
    <meta name="robots" content="none" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="18000">
    
    <title><?php if($this->session->userdata('company_name')) {echo $this->session->userdata('company_name');} else{echo $core_settings->company;}?></title>
    
    <link href="<?=site_url()?>assets/blueline/css/bootstrap.min.css?ver=<?=$core_settings->version;?>" rel="stylesheet">
    <link rel="stylesheet" href="<?=site_url()?>assets/blueline/css/plugins/animate.css?ver=<?=$core_settings->version;?>" />
    <link rel="stylesheet" href="<?=site_url()?>assets/blueline/css/plugins/nprogress.css" />
    <link href="<?=site_url()?>assets/blueline/css/blueline.css?ver=<?=$core_settings->version;?>" rel="stylesheet">
    <link href="<?=site_url()?>assets/blueline/css/user.css?ver=<?=$core_settings->version;?>" rel="stylesheet" /> 
    <?=get_theme_colors($settings);?>
	<link rel="stylesheet" href="<?=site_url()?>assets/blueline/css/app.css?ver=<?=$core_settings->version;?>"/>

    <script type="text/javascript">
  WebFontConfig = {
    google: { families: [ 'Open+Sans:400italic,400,300,600,700:latin' ] }
  };
  (function() {
    var wf = document.createElement('script');
    wf.src = ('https:' == document.location.protocol ? 'https' : 'http') +
      '://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
    wf.type = 'text/javascript';
    wf.async = 'true';
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(wf, s);
  })(); </script>
     <link rel="SHORTCUT ICON" href="<?=site_url()?>assets/blueline/img/favicon.ico"/>

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <?php if($settings->login_background==""){ ?>
  <body class="login" style="background-image:url('<?=site_url()?>assets/blueline/images/backgrounds/<?=$core_settings->login_background;?>')">
  <?php }else{ ?>
      <body class="login" style="background-image:url('<?=site_url()?>assets/blueline/images/backgrounds/<?=$settings->login_background;?>')">
  <?php }?>
  <div class="global-loader"></div>
    <div class="container-fluid">
      <div class="row" style="margin-bottom:0px">
        <?=$yield?>
      </div>
    </div>
     <!-- Notify -->
    <?php if($this->session->flashdata('message')) { $exp = explode(':', $this->session->flashdata('message'))?>
        <div class="notify <?=$exp[0]?>"><?=$exp[1]?></div>
    <?php } ?>
    <script src="<?=site_url()?>assets/blueline/js/plugins/jquery-1.12.4.min.js"></script>
    <script src="<?=site_url()?>assets/blueline/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/plugins/velocity.min.js"></script>
    <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/plugins/velocity.ui.min.js"></script>
    <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/plugins/validator.min.js"></script>
    <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/plugins/nprogress.js"></script>
	
	<script type="text/javascript" src="<?=site_url()?>assets/blueline/js/app.js?ver=<?=$core_settings->version;?>"></script>
	<script type="text/javascript" src="<?=site_url()?>assets/blueline/js/locales/flatpickr_english.js?ver=<?=$core_settings->version;?>"></script>

    <script type="text/javascript">
            $(document).ready(function(){
            //$("#package").find("option").eq(0).removeAttr('selected');
            
            $('#register_submit').click(function(){
                var package_option=$('#package_chosen a span').text();
                //alert(package_option);
                //var packet_type_option=$('#package_type').val();
                //alert(packet_type_option);
                if(package_option=='-')
                {
                    $('#package_div').addClass('error1');
                    //return false;
                }
//                if(packet_type_option=='')
//                {
//                    $('#package_type_div').addClass('error1');
//                }
            });
//            $('#package_type').change(function(){
//                var packet_type_option=$('#package_type').val();
//                if(packet_type_option=='')
//                {
//                    $('#package_type_div').addClass('error1');
//                }
//                else
//                {
//                    $('#package_type_div').removeClass('error1');
//                }
//            });
            $('#package').change(function(){
                var package_option=$('#package_chosen a span').text();
                if(package_option=='-')
                {
                    $('#package_div').addClass('error1');
                }
                else
                {
                    $('#package_div').removeClass('error1');
                }
            });
              fade = "Left";
              <?php if($settings->login_style==""){ ?>
              <?php if($core_settings->login_style == "center"){ ?>
                fade = "Up";
              <?php }?>
              <?php }else{ ?>
               <?php if($settings->login_style == "center"){ ?>
                fade = "Up";
              <?php }?>
              <?php } ?>
              $("form").validator();

           $(".form-signin").addClass("animated fadeIn"+fade);
           $( ".fadeoutOnClick" ).on( "click", function(){
              NProgress.start();
              $(".form-signin").addClass("animated fadeOut"+fade);
              NProgress.done();
            });
                <?php if($error == "true") { ?>
                    $("#error").addClass("animated shake"); 
                <?php } ?>

                //notify 
            $('.notify').velocity({
                  opacity: 1,
                  right: "10px",
                }, 900, function() {
                  $('.notify').delay( 4000 ).fadeOut();
                });

             /* 2.5.0 Form styling */

            $( ".form-control" ).each(function( index ) {          
              if ($( this ).val().length > 0 ) {
                    $( this ).closest('.form-group').addClass('filled');
                  }
            });
            $( "select.chosen-select" ).each(function( index ) {          
              if ($( this ).val().length > 0 ) {
                    $( this ).closest('.form-group').addClass('filled');
                  }
            });

            $( ".form-control" ).on( "focusin", function(){
                  $(this).closest('.form-group').addClass("focus");
              });
            $( ".chosen-select" ).on( "chosen:showing_dropdown", function(){
                  $(this).closest('.form-group').addClass("focus");
              });
            $( ".chosen-select" ).on( "chosen:hiding_dropdown", function(){
                  $(this).closest('.form-group').removeClass("focus");
              });
			
            $( ".form-control" ).on( "focusout", function(){
                  $(this).closest('.form-group').removeClass("focus");
                  if ($(this).val().length > 0 ) {
                      $(this).closest('.form-group').addClass('filled');
                  } else {
                      $(this).closest('.form-group').removeClass('filled');
                  }
            });

			$('#login').submit(function(event) {
				$('.ajax-loader').show();
				$('.form-login-error').html('');
				var filter = /^[a-zA-Z0-9]+[a-zA-Z0-9_.-]+[a-zA-Z0-9_-]+@[a-zA-Z0-9]+[a-zA-Z0-9.-]+[a-zA-Z0-9]+.[a-z]{2,4}$/;
				var email = $("#emailid").val();
				var password = $("#password").val();
				
				if(email == "") {
					$('.form-login-error').html('<div id="error"><?=$this->lang->line('messages_valid_email');?></div>');
				}
				
				if(!$(this).hasClass("email_validate"))
				{
					if(email == "") {
						$('.ajax-loader').hide();
						$('.success').fadeOut(200).hide();
						$('.error').fadeOut(200).show();
					} else if(filter.test(email)){
						var formData = $(this).serialize();
						$.ajax({
							type: "POST",
							url: "<?php echo base_url();?>auth/email_validate/",
							data: formData,
							success: function(response){
								var resultData = $.parseJSON(response);
								//console.log('email validate '+data);
								$('.ajax-loader').hide();

								if(resultData.validate == 'success') {
									// console.log(data);
									$("#showusertypes").html(resultData.html_response);
									$("#submitbutton").hide();
									$('#emailid').attr('readonly', true);
									$('#login').addClass("email_validate");

								} else {
									$('.form-login-error').html(resultData.html_response);
									return false;
								}
							},
							complete: function (data) {
								$('.ajax-loader').hide();
								$(".chosen-select").chosen({scroll_to_highlighted: false, disable_search_threshold: 4, width: "100%"});
							}
						});
					} else {
						$('.ajax-loader').hide();
						$('.form-login-error').html('<div id="error"><?=$this->lang->line('messages_valid_email');?></div>');
					}
				}
				else 
				{

					var companytype = $('#companytype');
					if(companytype.length > 0 && companytype.val() == 0) {
						$('.ajax-loader').hide();
						$('.form-login-error').html('<div id="error"><?=$this->lang->line('messages_select_company');?></div>');
						
					} else if(password != '') {
						$('.ajax-loader').show();
						var formData = $(this).serialize();
						$.ajax({
							type: "POST",
							url: "<?php echo base_url();?>auth/user_validate/",
							data: formData,
							success: function(response){
								var resultData = $.parseJSON(response);
								// console.log('user validate '+data);
								$('.ajax-loader').hide();

								if(resultData.validate == 'success') {
									// $("#showusertypes").html(data);
									$("#submitbutton").hide();
									$('#emailid').attr('readonly', true);
									$('#login').addClass("email_validate");

									//window.location.href = task.return;
									window.location.href = resultData.html_response;

								} else {
									$('.form-login-error').html('<div id="error">'+resultData.html_response+'</div>');
									// $("#nameError").html("Please Enter Valid Password").addClass("error-msg"); // chained methods
									return false;
								}
							}
						});
					} else {
						$('.ajax-loader').hide();
						$('.form-login-error').html('<div id="error"><?=$this->lang->line('messages_password_empty');?></div>');
					}
					
				}
				// stop the form from submitting the normal way and refreshing the page
				event.preventDefault();

			});
			
			
			$('#forgotpass').submit(function(event) {
				$('.ajax-loader').show();
				$('.form-forgot-error').html('');
				var filter = /^[a-zA-Z0-9]+[a-zA-Z0-9_.-]+[a-zA-Z0-9_-]+@[a-zA-Z0-9]+[a-zA-Z0-9.-]+[a-zA-Z0-9]+.[a-z]{2,4}$/;
				var email = $("#emailid").val();
				var password = $("#password").val();
				
				if(!$(this).hasClass("email_validate"))
				{	event.preventDefault();
					if(email == "") {
						$('.ajax-loader').hide();
						$('.success').fadeOut(200).hide();
						$('.error').fadeOut(200).show();
					} else if(filter.test(email)){
						var formData = $(this).serialize();
						$.ajax({
							type: "POST",
							url: "<?php echo base_url();?>auth/email_validate/",
							data: formData,
							success: function(response){
								var resultData = $.parseJSON(response);
								//console.log('email validate '+data);
								$('.ajax-loader').hide();

								if(resultData.validate == 'success') {
									// console.log(data);
									$("#showusertypes").html(resultData.html_response);
									$("#submitbutton").hide();
									$('#emailid').attr('readonly', true);
									$('#forgotpass').addClass("email_validate");

								} else {
									$('.form-forgot-error').html(resultData.html_response);
									return false;
								}
							},
							complete: function (data) {
								$('.ajax-loader').hide();
								$(".chosen-select").chosen({scroll_to_highlighted: false, disable_search_threshold: 4, width: "100%"});
							}
						});
					} else {
						$('.ajax-loader').hide();
						$('.form-forgot-error').html('<div id="error"><?=$this->lang->line('messages_valid_email');?></div>');
						return false;
					}
				}
				else 
				{

					var companytype = $('#companytype');
					if(companytype.length > 0 && companytype.val() == 0) {
						$('.ajax-loader').hide();
						$('.form-forgot-error').html('<div id="error"><?=$this->lang->line('messages_select_company');?></div>');
						return false;
					}
					
				}
				// stop the form from submitting the normal way and refreshing the page
				// event.preventDefault();

			});
      });
            


            
        </script> 

  </body>
</html>

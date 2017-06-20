<?php 

/**

 * @file        Fullpage View

 * @author      Luxsys <support@luxsys-apps.com>

 * @copyright   By Luxsys (http://www.luxsys-apps.com)

 * @version     2.2.0

 */

?>

<!DOCTYPE html>

<html>

  <head>

    <meta charset="utf-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <META Http-Equiv="Cache-Control" Content="no-cache">

    <META Http-Equiv="Pragma" Content="no-cache">

    <META Http-Equiv="Expires" Content="0">

    <meta name="robots" content="none" />

    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0">

    <link rel="SHORTCUT ICON" href="<?=site_url()?>assets/blueline/img/favicon.ico"/>

    <title><?=$core_settings->company;?></title> 



    <script src="<?=site_url()?>assets/blueline/js/plugins/jquery-1.12.4.min.js?ver=<?=$core_settings->version;?>"></script>





    <!-- Google Font Loader -->

    <link href="<?=site_url()?>assets/blueline/css/font-awesome.min.css" rel="stylesheet">

    <script type="text/javascript">

        WebFontConfig = {

          google: { families: [ 'Open+Sans:400italic,400,300,600,700:latin,latin-ext' ] }

        };

        (function() {

          var wf = document.createElement('script');

          wf.src = ('https:' == document.location.protocol ? 'https' : 'http') +

            '://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';

          wf.type = 'text/javascript';

          wf.async = 'true';

          var s = document.getElementsByTagName('script')[0];

          s.parentNode.insertBefore(wf, s);

        })(); 

    </script>

    <link rel="stylesheet" href="<?=site_url()?>assets/blueline/css/bootstrap.min.css?ver=<?=$core_settings->version;?>" />

    <!-- Plugins -->

    <link rel="stylesheet" href="<?=site_url()?>assets/blueline/css/plugins/jquery-ui-1.10.3.custom.min.css?ver=<?=$core_settings->version;?>" />

    <link rel="stylesheet" href="<?=site_url()?>assets/blueline/css/plugins/colorpicker.css?ver=<?=$core_settings->version;?>" />

    <link rel="stylesheet" href="<?=site_url()?>assets/blueline/css/plugins/jquery-slider.css?ver=<?=$core_settings->version;?>" />

    <link rel="stylesheet" href="<?=site_url()?>assets/blueline/css/plugins/summernote.css?ver=<?=$core_settings->version;?>" />

    <link rel="stylesheet" href="<?=site_url()?>assets/blueline/css/plugins/chosen.css?ver=<?=$core_settings->version;?>" />

    <link rel="stylesheet" href="<?=site_url()?>assets/blueline/css/plugins/datatables.min.css?ver=<?=$core_settings->version;?>" />

    <link rel="stylesheet" href="<?=site_url()?>assets/blueline/css/plugins/nprogress.css?ver=<?=$core_settings->version;?>" />

    <link rel="stylesheet" href="<?=site_url()?>assets/blueline/css/plugins/jquery-labelauty.css?ver=<?=$core_settings->version;?>" />

    <link rel="stylesheet" href="<?=site_url()?>assets/blueline/css/plugins/easy-pie-chart-style.css?ver=<?=$core_settings->version;?>" />

    <link rel="stylesheet" href="<?=site_url()?>assets/blueline/css/plugins/fullcalendar.css?ver=<?=$core_settings->version;?>" />

    <link rel="stylesheet" href="<?=site_url()?>assets/blueline/css/plugins/reflex.min.css?ver=<?=$core_settings->version;?>" />

    <link rel="stylesheet" href="<?=site_url()?>assets/blueline/css/plugins/animate.css?ver=<?=$core_settings->version;?>" />

    <link rel="stylesheet" href="<?=site_url()?>assets/blueline/css/plugins/flatpickr.dark.min.css?ver=<?=$core_settings->version;?>" />

    <link rel="stylesheet" href="<?=site_url()?>assets/blueline/css/font-awesome.min.css?ver=<?=$core_settings->version;?>" />

    <link rel="stylesheet" href="<?=site_url()?>assets/blueline/css/ionicons.min.css?ver=<?=$core_settings->version;?>" />

    

    <link rel="stylesheet" href="<?=site_url()?>assets/blueline/css/blueline.css?ver=<?=$core_settings->version;?>"/>

    <link rel="stylesheet" href="<?=site_url()?>assets/blueline/css/user.css?ver=<?=$core_settings->version;?>"/> 

    <?=get_theme_colors($core_settings);?>



    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->

    <!--[if lt IE 9]>

      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>

      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>

    <![endif]-->

    <style type="text/css">

      html{

        height: 100%;

      }

      body {

        padding-bottom: 40px;

        height: 100%;

      }  

    </style>

     

 </head>

  <body>

  <div class="container small-container">

  

  		<!-- <img class="fullpage-logo" src="<?=site_url()?><?=$core_settings->invoice_logo;?>" alt="<?=$core_settings->company;?>" /> -->
      <img class="fullpage-logo" src="<?=site_url()?><?=$company_detail->logo;?>" alt="<?=$this->sessionArr['company_name'];?>" />

     



    <div>

     <?php if($this->session->flashdata('message')) { $exp = explode(':', $this->session->flashdata('message'))?>

	    <div id="quotemessage" class="alert alert-success"><span><?=$exp[1]?></span></div>

	    <?php } ?>

<?=$yield?>

<br clear="all"/>

	</div>



</div>

  <!-- Bootstrap core JavaScript -->

        <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/bootstrap.min.js?ver=<?=$core_settings->version;?>"></script>

        <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/plugins/jquery-ui-1.10.3.custom.min.js?ver=<?=$core_settings->version;?>"></script>

    

    <!-- Plugins -->

        <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/plugins/bootstrap-colorpicker.min.js?ver=<?=$core_settings->version;?>"></script>

        <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/plugins/jquery.knob.min.js?ver=<?=$core_settings->version;?>"></script>

        <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/plugins/summernote.min.js?ver=<?=$core_settings->version;?>"></script>

        <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/plugins/chosen.jquery.min.js?ver=<?=$core_settings->version;?>"></script>

        <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/plugins/datatables.min.js?ver=<?=$core_settings->version;?>"></script> 

        <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/plugins/jquery.nanoscroller.min.js?ver=<?=$core_settings->version;?>"></script>

        <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/plugins/jqBootstrapValidation.js?ver=<?=$core_settings->version;?>"></script>

        <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/plugins/nprogress.js?ver=<?=$core_settings->version;?>"></script>

        <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/plugins/jquery-labelauty.js?ver=<?=$core_settings->version;?>"></script>

        <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/plugins/validator.min.js?ver=<?=$core_settings->version;?>"></script>

        <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/plugins/timer.jquery.min.js?ver=<?=$core_settings->version;?>"></script>

        <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/plugins/jquery.easypiechart.min.js?ver=<?=$core_settings->version;?>"></script>

        <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/plugins/velocity.min.js?ver=<?=$core_settings->version;?>"></script>

        <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/plugins/velocity.ui.min.js?ver=<?=$core_settings->version;?>"></script>

        <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/plugins/moment-with-locales.min.js?ver=<?=$core_settings->version;?>"></script>

        <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/plugins/chart.min.js?ver=<?=$core_settings->version;?>"></script>

        <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/plugins/countUp.min.js?ver=<?=$core_settings->version;?>"></script>

        <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/plugins/jquery.inputmask.bundle.min.js?ver=<?=$core_settings->version;?>"></script>

        <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/plugins/fullcalendar/fullcalendar.min.js?ver=<?=$core_settings->version;?>"></script>

        <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/plugins/fullcalendar/gcal.js?ver=<?=$core_settings->version;?>"></script>

        <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/plugins/fullcalendar/lang-all.js?ver=<?=$core_settings->version;?>"></script>    

        <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/plugins/flatpickr.min.js?ver=<?=$core_settings->version;?>"></script>

        <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/locales/flatpickr_<?=$current_language?>.js?ver=<?=$core_settings->version;?>"></script>

        

    <!-- Blueline Js -->  

        <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/blueline.js?ver=<?=$core_settings->version;?>"></script>





      <script type="text/javascript" charset="utf-8">

      

//Validation

  $("form").validator();



        $(document).ready(function(){ 



              $(".removehttp").change(function(e){

                $(this).val($(this).val().replace("http://",""));

              });



        });

    </script>



 </body>

</html>


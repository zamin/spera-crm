<?php 
/**
 * @file        Application View
 * @author      Luxsys <support@freelancecockpit.com>
 * @copyright   By Luxsys (http://www.freelancecockpit.com)
 * @version     2.5.0
 */

$act_uri = $this->uri->segment(2, 0);
$lastsec = $this->uri->total_segments();
$act_uri_submenu = $this->uri->segment($lastsec);
if(!$act_uri){ $act_uri = 'cdashboard'; }
if(is_numeric($act_uri_submenu)){ 
    $lastsec = $lastsec-1; 
    $act_uri_submenu = $this->uri->segment($lastsec);
}
$message_icon = false;
 ?> 
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <meta name="robots" content="none" />
    <link rel="SHORTCUT ICON" href="<?=site_url()?>assets/blueline/img/favicon.ico"/>
    <title><?php if($this->sessionArr['company_name']){echo $this->sessionArr['company_name'];}else{echo $company_name;}?></title> 

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

    <link rel="stylesheet" href="<?=site_url()?>assets/blueline/css/app.css?ver=<?=$core_settings->version;?>"/>
    <link rel="stylesheet" href="<?=site_url()?>assets/blueline/css/user.css?ver=<?=$core_settings->version;?>"/> 
    <?=get_theme_colors($settings);?>

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <?php /*<script type="">
        $( document ).ready(function() {
            $.ajax({
                type: "POST",
                url: "<?=base_url()?><?=$this->sessionArr['company_id'];?>/auth/validate_url/",
                data: "<?php echo $this->security->get_csrf_token_name(); ?>=<?php echo $this->security->get_csrf_hash(); ?>",
                success: function(data){
                    var responseData = JSON.parse(data);
                    $('a').each(function() {
                        
                        if( $(this).attr('href') != undefined && $(this).attr('href') != '#' && $(this).attr('href').indexOf("http") >= 0 )
                        {
                          var value = $(this).attr('href');
                          var res = value.replace( responseData.site_url , responseData.redirect_url); 
                          $(this).attr('href', res);
                        }
                    });
                    
                    $('[rel]').each(function() {
                        var r = $(this).attr('rel');
                        if(r.indexOf("http") >= 0)
                        {
                            var res = r.replace( responseData.site_url , responseData.redirect_url); 
                            $(this).attr('rel', res);
                        }
                    });
                },
                complete: function (data) {
                  $(".chosen-select").chosen({scroll_to_highlighted: false, disable_search_threshold: 4, width: "100%"});
                }
            });
        });
    </script>*/ ?>
    <script>
        var i = 0;
         function funSaveCreditcard(obj)
        {
            var oldhtml = $('.button-loader').html();
            var oldstr = '<i class="fa fa-spinner fa-spin"></i> ';
            //$('.button-loader').html( oldstr+oldhtml );
            var amount = $('#amount').val();
            var cardnumber = $('#cardnumber').val();
            var PaymentTypeId = $('#PaymentTypeId').val();
            var ExpMonth = $('#ExpMonth').val();
            var ExpYear = $('#ExpYear').val();
            var cvv = $('#cvv').val();
            var userID = $('#userID').val();
            var InvoiceID = $('#InvoiceID').val();
            
            var error = 0;
            
            if( amount.length==0 )
            {
                $('#amount').parent().addClass("has-error");
                error = 1;
            }
            if( cardnumber.length==0 )
            {
                $('#cardnumber').parent().addClass("has-error");
                error = 1;
            }
            if( PaymentTypeId.length==0 )
            {
                $('#PaymentTypeId').parent().addClass("has-error");
                error = 1;
            }
            if( ExpMonth.length==0 )
            {
                $('#ExpMonth').parent().addClass("has-error");
                error = 1;
            }
            if( ExpYear.length==0 )
            {
                $('#ExpYear').parent().addClass("has-error");
                error = 1;
            }
            if( cvv.length==0 )
            {
                $('#cvv').parent().addClass("has-error");
                error = 1;
            }
            
            if(error == 1)
            {
                var oldsend = $('.button-loader').html();
                var oldsend1 = oldsend.replace('<i class="fa fa-spinner fa-spin"></i> ', '');
                setTimeout(function(){$('.button-loader').html(oldsend1);},500);
                return false;
            }
            $('#'+obj.id+ ' input').attr('readonly', 'readonly');
            $.ajax({
                type: "POST",
                url: "<?php echo base_url();?>cinvoices/getsettoken/",
                data: "InvoiceID="+InvoiceID+"&userID="+userID+"&amount="+amount+"&<?php echo $this->security->get_csrf_token_name(); ?>=<?php echo $this->security->get_csrf_hash(); ?>",
                success: function(data){
                    //i = 1;
                    var responseData = JSON.parse(data);
                    if(responseData.payerID)
                    {
                        //if( responseData.payerID ) { $('#payerID').val(responseData.payerID); }
                        //if( responseData.tempToken ) { $('#tempToken').val(responseData.tempToken); }
                        if( responseData.settingsCipher ) { $('#SettingsCipher').val(responseData.settingsCipher); }
                        if( responseData.credentiaID ) { $('#CID').val(responseData.credentiaID); }
                        i = 1;
                        $(obj).submit();
                    }
                    else
                    {
                        window.location.reload(true);
                    }
                },
                complete: function (data) {
                  //$(".chosen-select").chosen({scroll_to_highlighted: false, disable_search_threshold: 4, width: "100%"});
                }
            });
        }
    </script>
  </head>

<body>
<div id="mainwrapper">
<div class="global-loader"></div>
    <div class="side">
    <div class="sidebar-bg"></div>
        <div class="sidebar">
        <div class="navbar-header">
<?php if(!empty($settings->login_logo)){ ?>
          <a class="navbar-brand" href="#"><img height="110" src="<?=site_url()?><?=$settings->login_logo;?>" alt="<?=$this->sessionArr['company'];?>"></a>
          <?php }else{ ?>
          <a class="navbar-brand" href="#"><img src="<?=site_url()?><?=$core_settings->logo;?>" alt="<?=$core_settings->company;?>"></a>
          <?php } ?>        </div>
          
          <ul class="nav nav-sidebar">
              <?php foreach ($this->menu_data as $key => $value) { ?>
               <?php 
               if(strtolower($value->link) == "cmessages"){$message_icon = true;}
               ?>
               <li id="<?=strtolower($value->name);?>" class="<?php if ($act_uri == strtolower($value->link)) {echo "active";}?>"><a href="<?=base_url().$value->link.'/';?>"><span class="menu-icon"><i class="fa <?=$value->icon;?>"></i></span><span class="nav-text"><?php echo $this->lang->line('application_'.$value->link);?></span>
                <?php if(strtolower($value->link) == "cmessages" && $messages_new[0]->amount != "0"){ ?><span class="notification-badge"><?=$messages_new[0]->amount;?></span><?php } ?>
                <?php if(strtolower($value->link) == "quotations" && $quotations_new[0]->amount != "0"){ ?><span class="notification-badge"><?=$quotations_new[0]->amount;?></span><?php } ?>
                <?php if(strtolower($value->link) == "ctickets" && $this->tickets_assigned_note > 0){ ?><span class="notification-badge"><?=$this->tickets_assigned_note;?></span><?php } ?>
                <?php /*if(strtolower($value->link) == "cestimates" && $estimates_new[0]->amount != "0"){ ?><span class="notification-badge"><?=$estimates_new[0]->amount;?></span><?php } */?>

               </a> </li>
              <?php } ?>
          </ul>
            
    
          
        </div>
    </div>

    <div class="content-area">
      <div class="row mainnavbar">
<div class="topbar__left noselect">
<a href="#" class="menu-trigger"><i class="ion-navicon visible-xs"></i></a>
            <?php if($message_icon){ ?>
              <span class="hidden-xs">
                  <a href="#" title="<?=$this->lang->line('application_messages');?>">
                     <i class="ion-archive topbar__icon"></i>
                  </a>
              </span>
            <?php } ?>
      </div>
      <div class="topbar noselect">
      <?php  $userimage = get_user_pic($this->user->userpic, $this->user->email); ?>
      <img class="img-circle topbar-userpic" src="<?=$userimage;?>" height="21px">  
      <span class="topbar__name fc-dropdown--trigger">
          <?php echo character_limiter($this->user->firstname." ".$this->user->lastname, 25);?> <i class="ion-chevron-down" style="padding-left: 2px;"></i>
      </span>
      <div class="fc-dropdown profile-dropdown">
        <ul>
          <li>
              <a href="<?=base_url()."agent";?>" data-toggle="mainmodal">
                <span class="icon-wrapper"><i class="ion-gear-a"></i></span> <?=$this->lang->line('application_profile');?>
              </a>
          </li>
          
          <li class="fc-dropdown__submenu--trigger">
              <span class="icon-wrapper"><i class="ion-ios-arrow-back"></i></span> <?=$current_language;?>
                <ul class="fc-dropdown__submenu">
                    <span class="fc-dropdown__title"><?=$this->lang->line('application_languages');?></span>
                    <?php foreach ($installed_languages as $entry)
                              { ?>
                                   <li>
                                       <a href="<?=base_url()?>agent/language/<?=$entry;?>">
                                          <img src="<?=site_url()?>assets/blueline/img/<?=$entry;?>.png" class="language-img"> <?=ucwords($entry);?>
                                        </a>
                                   </li>
                                         
                       <?php  } ?>  
                </ul>
              
          </li>
            <li class="profile-dropdown__logout">
                    <a href="<?=base_url()."logout/";?>" title="<?=$this->lang->line('application_logout');?>">
                         <?=$this->lang->line('application_logout');?> <i class="ion-power pull-right"></i>
                    </a>  
            </li>
          </ul>
      </div>
      
  </div>       
</div>
        
        
        
        
        <?=$yield?>
      
      
            

      

    </div>
    <!-- Notify -->
    <?php if($this->session->flashdata('message')) { $exp = explode(':', $this->session->flashdata('message'))?>
        <div class="notify <?=$exp[0]?>"><?=$exp[1]?></div>
    <?php } ?>

      
    <!-- Modal -->
    <div class="modal fade" id="mainModal" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="mainModalLabel" aria-hidden="true"></div>
    

  <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/app.js?ver=<?=$core_settings->version;?>"></script>
  <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/locales/flatpickr_<?=$current_language?>.js?ver=<?=$core_settings->version;?>"></script>

    
 </div> <!-- Mainwrapper end -->   

 <script type="text/javascript" charset="utf-8">

function flatdatepicker(activeform){
  
      Flatpickr.localize(Flatpickr.l10ns.<?=$current_language?>);
      var required = "required";
      if($(".datepicker").hasClass("not-required")){required = "";}
      var datepicker = flatpickr('.datepicker', {
            dateFormat: 'Y-m-d', 
            timeFormat: '<?=$timeformat;?>',
            time_24hr: <?=$time24hours;?>,
            altInput:true, 
            static:true,
            altFormat:'<?=$dateformat?>',
            altInputClass: 'form-control '+required,
            onChange: function(selectedDates, dateStr, instance){ 
                                    if(activeform && !$(".datepicker").hasClass("not-required")){activeform.validator('validate');}
                                    if($(".datepicker-linked")[0]){ 
                                              datepickerLinked.set("minDate", dateStr);
                                            } 
                                }
      });
       var required = "required";
      if($(".datepicker-time").hasClass("not-required")){required = "";}
      var datepicker = flatpickr('.datepicker-time', {
            //dateFormat: 'U', 
            timeFormat: '<?=$timeformat;?>',
            time_24hr: <?=$time24hours;?>,
            altInput:true, 
            static:true,
            altFormat:'<?=$dateformat?> <?=$timeformat;?>',
            onChange: function(selectedDates, dateStr, instance){ 
                                    if(activeform && !$(".datepicker").hasClass("not-required")){activeform.validator('validate');}
                                    if($(".datepicker-linked")[0]){ 
                                              datepickerLinked.set("minDate", dateStr);
                                            } 
                                }
      });
      if($(".datepicker-linked").hasClass("not-required")){var required = "";}else{var required = "required";}
      var datepickerLinked = flatpickr('.datepicker-linked', {
            dateFormat: 'Y-m-d', 
            timeFormat: '<?=$timeformat;?>',
            time_24hr: <?=$time24hours;?>,
            altInput:true, 
            altFormat:'<?=$dateformat?>',
            static:true,
            altInputClass: 'form-control '+required,
            onChange: function(selectedDates, dateStr, instance){ 
                                  if(activeform && !$(".datepicker-linked").hasClass("not-required")){activeform.validator('validate');}
                                }
      });
        //set dummyfields to be required
        $(".required").attr('required', 'required');
        
}
flatdatepicker();

      $(document).ready(function(){
        sorting_list("<?=base_url();?>");
        $("form").validator();

        $("#menu li a, .submenu li a").removeClass("active");
        if("" == "<?php echo $act_uri_submenu; ?>"){$("#sidebar li a").first().addClass("active");}  
        <?php if($act_uri_submenu != "0"){ ?>$(".submenu li a#<?php echo $act_uri_submenu; ?>").parent().addClass("active");<?php } ?>
        $("#menu li#<?php echo $act_uri; ?>").addClass("active");

        //Datatables

        var dontSort = [];
                $('.data-sorting thead th').each( function () {
                    if ( $(this).hasClass( 'no_sort' )) {
                        dontSort.push( { "bSortable": false } );
                    } else {
                        dontSort.push( null );
                    }
                } );


        $('table.data').dataTable({
          "initComplete": function () {
            var api = this.api();
            api.$('td.add-to-search').click( function () {
                api.search( $(this).data("tdvalue") ).draw();
            } );
        },
          "iDisplayLength": 25,
          stateSave: true,
          "bLengthChange": false,
          "aaSorting": [[ 0, 'desc']],
          "oLanguage": {
          "sSearch": "",
            "sInfo": "<?=$this->lang->line('application_showing_from_to');?>",
            "sInfoEmpty": "<?=$this->lang->line('application_showing_from_to_empty');?>",
            "sEmptyTable": "<?=$this->lang->line('application_no_data_yet');?>",
            "oPaginate": {
              "sNext": '<i class="fa fa-arrow-right"></i>',
              "sPrevious": '<i class="fa fa-arrow-left"></i>',
            }
          }
        });
        $('table.data-media').dataTable({
          "iDisplayLength": 15,
          stateSave: true,
          "bLengthChange": false,
          "bFilter": false, 
          "bInfo": false,
          "aaSorting": [[ 0, 'desc']],
          "oLanguage": {
          "sSearch": "",
            "sInfo": "<?=$this->lang->line('application_showing_from_to');?>",
            "sInfoEmpty": "<?=$this->lang->line('application_showing_from_to_empty');?>",
            "sEmptyTable": " ",
            "oPaginate": {
              "sNext": '<i class="fa fa-arrow-right"></i>',
              "sPrevious": '<i class="fa fa-arrow-left"></i>',
            }
          }
        });
        $('table.data-no-search').dataTable({
          "iDisplayLength": 8,
          stateSave: true,
          "bLengthChange": false,
          "bFilter": false, 
          "bInfo": false,
          "aaSorting": [[ 1, 'desc']],
          "oLanguage": {
          "sSearch": "",
            "sInfo": "<?=$this->lang->line('application_showing_from_to');?>",
            "sInfoEmpty": "<?=$this->lang->line('application_showing_from_to_empty');?>",
            "sEmptyTable": " ",
            "oPaginate": {
              "sNext": '<i class="fa fa-arrow-right"></i>',
              "sPrevious": '<i class="fa fa-arrow-left"></i>',
            }
          },
          fnDrawCallback: function (settings) {
              $(this).parent().toggle(settings.fnRecordsDisplay() > 0);
              if (settings._iDisplayLength > settings.fnRecordsDisplay()) {
            $(settings.nTableWrapper).find('.dataTables_paginate').hide();
        }

          }

        });
        $('table.data-sorting').dataTable({
          "iDisplayLength": 25,
          "bLengthChange": false,
          "aoColumns": dontSort,
          "aaSorting": [[ 1, 'desc']],
          "oLanguage": {
          "sSearch": "",
            "sInfo": "<?=$this->lang->line('application_showing_from_to');?>",
            "sInfoEmpty": "<?=$this->lang->line('application_showing_from_to_empty');?>",
            "sEmptyTable": "<?=$this->lang->line('application_no_data_yet');?>",
            "oPaginate": {
              "sNext": '<i class="fa fa-arrow-right"></i>',
              "sPrevious": '<i class="fa fa-arrow-left"></i>',
            }
          }
        });
        $('table.data-small').dataTable({
          "iDisplayLength": 5,
          "bLengthChange": false,
          "aaSorting": [[ 2, 'desc']],
          "oLanguage": {
          "sSearch": "",
            "sInfo": "<?=$this->lang->line('application_showing_from_to');?>", 
            "sInfoEmpty": "<?=$this->lang->line('application_showing_from_to_empty');?>",
            "sEmptyTable": "<?=$this->lang->line('application_no_data_yet');?>",
            "oPaginate": {
              "sNext": '<i class="fa fa-arrow-right"></i>',
              "sPrevious": '<i class="fa fa-arrow-left"></i>',
            }
          }
        });

        $('table.data-reports').dataTable({
          "iDisplayLength": 30,
          colReorder: true,
          buttons: [
            'copyHtml5',
            'excelHtml5',
            'csvHtml5',
            'pdfHtml5'
          ],

          "bLengthChange": false,
          "order": [[ 1, 'desc']],
          "columnDefs": [
                          { "orderable": false, "targets": 0 }
                        ],
          "oLanguage": {
          "sSearch": "",
            "sInfo": "<?=$this->lang->line('application_showing_from_to');?>", 
            "sInfoEmpty": "<?=$this->lang->line('application_showing_from_to_empty');?>",
            "sEmptyTable": "<?=$this->lang->line('application_no_data_yet');?>",
            "oPaginate": {
              "sNext": '<i class="fa fa-arrow-right"></i>',
              "sPrevious": '<i class="fa fa-arrow-left"></i>',
            }
          }
        });

      });
      
      
      </script>

 </body>
</html>

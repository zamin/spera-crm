<div class="col-sm-13  col-md-12 main"> 
     <div class="row tile-row">
      <div class="col-md-3 col-xs-6 tile"><div class="icon-frame hidden-xs"><i class="ion-ios-lightbulb"></i> </div><h1><?php if(isset($projects_assigned_to_me[0])){echo $projects_assigned_to_me[0]->amount;} ?> <span><?=$this->lang->line('application_projects');?></span></h1><h2 ><?=$this->lang->line('application_assigned_to_me');?></h2></div>
      <div class="col-md-3 col-xs-6 tile"><div class="icon-frame secondary hidden-xs"><i class="ion-ios-list-outline"></i> </div><h1> <?php if(isset($tasks_assigned_to_me)){echo $tasks_assigned_to_me;} ?> <span><?=$this->lang->line('application_tasks');?></span></h1><h2><?=$this->lang->line('application_assigned_to_me');?></h2></div>
      <div class="col-md-6 col-xs-12 tile hidden-xs">
        <div style="width:97%; margin-top: -4px; margin-bottom: 17px; height: 80px;">
            <canvas id="tileChart" width="auto" height="80"></canvas>
        </div>
      </div>
    </div> 
     <div class="row">
         <a href="<?=base_url()?>aoprojects/create" class="btn btn-primary" data-toggle="mainmodal"><?=$this->lang->line('application_create_new_project');?></a>
        <div class="btn-group pull-right margin-right-3">
          
          <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
            <?php $last_uri = $this->uri->segment($this->uri->total_segments()); if($last_uri != "aoprojects"){echo $this->lang->line('application_'.$last_uri);}else{echo $this->lang->line('application_open');} ?> <span class="caret"></span>
          </button>
          <ul class="dropdown-menu pull-right" role="menu">
            <?php foreach ($submenu as $name=>$value):?>
                  <li><a id="<?php $val_id = explode("/", $value); if(!is_numeric(end($val_id))){echo end($val_id);}else{$num = count($val_id)-2; echo $val_id[$num];} ?>" href="<?=base_url().$value;?>"><?=$name?></a></li>
              <?php endforeach;?>
          </ul>
      </div>
    </div>  
      <div class="row">

         <div class="table-head"><?=$this->lang->line('application_projects');?></div>
         <div class="table-div">
         <table class="data table" id="aoprojects" rel="<?=base_url()?>" cellspacing="0" cellpadding="0">
                <thead>
                  <tr>
                      <th width="20px" class="hidden-xs"><?=$this->lang->line('application_project_id');?></th>
                      <th class="hidden-xs" width="19px" class="no-sort sorting"></th>
                      <th><?=$this->lang->line('application_name');?></th>
                      <th class="hidden-xs">Company</th>
                      <th class="hidden-xs"><?=$this->lang->line('application_deadline');?></th>
                      <th class="hidden-xs"><?=$this->lang->line('application_assign_to');?></th>
                      <th><?=$this->lang->line('application_action');?></th>
                  </tr></thead>
                
                <tbody>
                    
                <?php 
                $count = 0;
                foreach ($project as $value):$count = $count+1

          ?>
                <tr id="<?=$value['id'];?>">
                  <td class="hidden-xs"><?=$core_settings->project_prefix;?><?=$value['reference'];?></td>
                  <td class="hidden-xs">
                    <div class="circular-bar tt" title="<?=$value['progress'];?>%">
                      <input type="hidden" class="dial" data-fgColor="<?php if($value->progress== "100"){ ?>#43AC6E<?php }else{ ?>#11A7DB<?php } ?>" data-width="19" data-height="19" data-bgColor="#e6eaed"  value="<?=$value['progress'];?>" >
                      </div>
                </td>
                  <td onclick=""><?=$value['name'];?></td>
                  <td class="hidden-xs"><a class="label label-info"><?php if(!isset($value['company_name'])){echo $this->lang->line('application_no_client_assigned'); }else{ echo $value['company_name']; }?></a></td>
                  <td class="hidden-xs"><span class="hidden-xs label label-success <?php if($value['end'] <= date('Y-m-d') && $value['progress'] != 100){ echo 'label-important tt" title="'.$this->lang->line('application_overdue'); } ?>"><?php $unix = human_to_unix($value['end'].' 00:00');echo '<span class="hidden">'.$unix.'</span> '; echo date($core_settings->date_format, $unix);?></span></td>
                  <td class="hidden-xs">
                    <?php
                        if(!empty($value['clients']))
                        {
                            //print_r($value['clients']) ;
                            foreach ($value['clients'] as $k => $v) 
                            {
                                $image = get_user_pic($v['userpic'], $v['email']);
                                if(!empty($v['userpic']))
                                {
                                    
                                    echo "<img src='".$image."' height='19px' class='img-circle tt' title='".$v['firstname'].' '.$v['lastname']."'>";
                                }
                                else
                                {
                                    echo "No image Availiable for ".$v['firstname']." ".$v['lastname'];
                                    //echo "<img alt='test' src='' height='19px' class='img-circle tt' title='".$v['firstname'].' '.$v['lastname']."'>";
                                }
                            }
                        }
                        else
                        {
                            echo "Project Not Assigned to clients";
                        }
                        
                    ?> 
                  </td>
                   <td class="option" width="8%">
                <button type="button" class="btn-option delete po" data-toggle="popover" data-placement="left" data-content="<a class='btn btn-danger po-delete ajax-silent' href='<?=base_url()?>aoprojects/delete/<?=$value['id'];?>'><?=$this->lang->line('application_yes_im_sure');?></a> <button class='btn po-close'><?=$this->lang->line('application_no');?></button> <input type='hidden' name='td-id' class='id' value='<?=$value['id'];?>'>" data-original-title="<b><?=$this->lang->line('application_really_delete');?></b>"><i class="fa fa-times"></i></button>
                <a href="<?=base_url()?>aoprojects/update/<?=$value['id'];?>" class="btn-option" data-toggle="mainmodal"><i class="fa fa-cog"></i></a>
                
             </td>
                </tr>
            <?php endforeach;?>
              </tbody>
            </table>
            </div>

      </div>
<script>
$(document).ready(function(){ 


$('.dial').each(function () { 

          var elm = $(this);
          var color = elm.attr("data-fgColor");  
          var perc = elm.attr("value");  
 
          elm.knob({ 
               'value': 0, 
                'min':0,
                'max':100,
                "skin":"tron",
                "readOnly":true,
                "thickness":.25,                 
                'dynamicDraw': true,                
                "displayInput":false
          });

          $({value: 0}).animate({ value: perc }, {
              duration: 1000,
              easing: 'swing',
              progress: function () {                  elm.val(Math.ceil(this.value)).trigger('change')
              }
          });

          //circular progress bar color
          $(this).append(function() {
              elm.parent().parent().find('.circular-bar-content').css('color',color);
              elm.parent().parent().find('.circular-bar-content label').text(perc+'%');
          });

          });
   
 


                       
});
</script>
  
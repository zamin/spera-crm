	<div class="col-sm-13  col-md-12 main">  
    
    
    <div class="row tile-row">
      <div class="col-md-3 col-xs-6 tile"><div class="icon-frame hidden-xs"><i class="ion-ios-pricetags"></i> </div><h1><?php if(isset($tickets_assigned_to_me)){echo $tickets_assigned_to_me;} ?> <span><?=$this->lang->line('application_tickets');?></span></h1><h2><?=$this->lang->line('application_assigned_to_me');?></h2></div>
      <div class="col-md-3 col-xs-6 tile"><a href="<?=base_url()?>tickets/queues/<?=$this->user->queue;?>"><div class="icon-frame secondary hidden-xs"><i class="ion-ios-albums"></i> </div><h1><?php if(isset($tickets_in_my_queue)){echo $tickets_in_my_queue;} ?> <span><?=$this->lang->line('application_tickets');?></span></h1><h2><?=$this->lang->line('application_in_my_queue');?></h2></a></div>
      <div class="col-md-6 col-xs-12 tile hidden-xs">
      <div style="width:97%; margin-top: -4px; margin-bottom: 17px; height: 80px;">
            <canvas id="tileChart" width="auto" height="80"></canvas>
        </div>
      </div>
    </div> 
    
    <div class="row"> 
		<a href="<?=base_url()?>ctickets/create" class="btn btn-primary" data-toggle="mainmodal"><?=$this->lang->line('application_create_new_ticket');?></a>
        <?php /*
        <div class="btn-group pull-right-responsive margin-right-3">
          <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
            <?php if(isset($activeQueue)){echo $activeQueue->name;}else{
                echo $this->lang->line('application_queue');
                }?>
             <span class="caret"></span>
          </button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li><a id="" href="<?=base_url()?>tickets/"><?=$this->lang->line('application_all');?></a></li>
            <?php foreach ($queues as $value):?>
                <li><a id="" href="<?=base_url()?>tickets/queues/<?=$value->id?>" <?php if($this->user->queue == $value->id){ echo 'style="font-weight: bold;"';}?>><?=$value->name?></a></li>
            <?php endforeach;?>
            
        </ul>
        </div>
        <div class="btn-group pull-right-responsive margin-right-3">
          <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
            <?php if(isset($ticketFilter)){echo $ticketFilter;} ?> <span class="caret"></span>
          </button>
        <ul class="dropdown-menu pull-right" role="menu">
            <?php foreach ($submenu as $name=>$value):?>
                <li><a id="<?php $val_id = explode("/", $value); if(!is_numeric(end($val_id))){echo end($val_id);}else{$num = count($val_id)-2; echo $val_id[$num];} ?>" href="<?=base_url().$value;?>"><?=$name?></a></li>
            <?php endforeach;?>
            
        </ul>
        </div>
        
        <div class="btn-group pull-right-responsive margin-right-3 hidden-xs">
          <button id="bulk-button" type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" >
             <?=$this->lang->line('application_bulk_actions');?> <span class="caret"></span>
          </button>
        <ul class="dropdown-menu pull-right bulk-dropdown" role="menu">
            
                <li data-action="close"><a id="" href="#"><?=$this->lang->line('application_close');?></a></li>

            
        </ul>
        <?php
            $form_action = base_url()."tickets/bulk/"; 
            $attributes = array('class' => '', 'id' => 'bulk-form');
            echo form_open($form_action, $attributes); ?>
          <input type="hidden" name="list" id="list-data"/>
        </form>
        </div>
        */
        $statuslist["open"] = $this->lang->line('application_ticket_status_open');
        $statuslist["onhold"] = $this->lang->line('application_ticket_status_onhold');
        $statuslist["inprogress"] = $this->lang->line('application_ticket_status_inprogress');
        $statuslist["reopened"] = $this->lang->line('application_ticket_status_reopened');
        $statuslist["closed"] = $this->lang->line('application_ticket_status_closed');
        ?>
        <div class="btn-group pull-right margin-right-3">
      
              <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                <?php echo $ticketStatus;?><span class="caret"></span>
              </button>
              <ul class="dropdown-menu pull-right" role="menu">
                <li><a href="<?=base_url().'ctickets/filter/all';?>">All</a></li>
                <?php foreach ($statuslist as $name=>$value):?>
                      <li><a id="<?php $val_id = explode("/", $name);?>" href="<?=base_url().'ctickets/filter/'.$name;?>"><?=$name?></a></li>
                  <?php endforeach;?>
              </ul>
          </div>
          
          <div class="btn-group pull-right-responsive margin-right-3">
          
                  <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                    Bulk Actions<span class="caret"></span>
                  </button>
                  <ul class="dropdown-menu pull-right" role="menu">
                    <li><a href="javascript:void(0);" onclick="jQuery('#frm_bulk').submit();">Close</a></li>
                  </ul>
              </div>
	</div>
	<div class="row">
		<div class="table-head"><?=$this->lang->line('application_tickets');?></div>
		<div class="table-div">
        <form action="<?php echo base_url().'ctickets/bulk';?>" method="post" name="frm_bulk" id="frm_bulk">
        
        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
		<table class="data-sorting table" id="ctickets" rel="<?=base_url()?>" cellspacing="0" cellpadding="0">
		<thead>
			<th class="hidden-xs no_sort simplecheckbox" style="width:16px"><input class="checkbox-nolabel" type="checkbox" id="checkAll" name="selectall" value=""></th>
			<th class="hidden-xs" style="width:70px"><?=$this->lang->line('application_ticket_id');?></th>
			<th style="width:50px"><?=$this->lang->line('application_status');?></th>
			<th><?=$this->lang->line('application_subject');?></th>
			<th class="hidden-xs"><?=$this->lang->line('application_queue');?></th>
			<th class="hidden-xs"><?=$this->lang->line('application_owner');?></th>
            <th class="hidden-xs"><?=$this->lang->line('application_action');?></th>
		</thead>
		<?php 
        foreach ($ticket as $value):?>
			<?php $lable = FALSE; if($value['status'] == "new"){ $lable = "label-important"; }elseif($value['status'] == "open"){$lable = "label-warning";}elseif($value['status'] == "closed" || $value['status'] == "inprogress"){$lable = "label-success";}elseif($value['status'] == "reopened"){$lable = "label-warning";} ?>
		<tr id="<?=$value['id'];?>" >
			<td class="hidden-xs noclick simplecheckbox" style="width:16px"> <input class="checkbox-nolabel bulk-box" type="checkbox" name="bulk[]" value="<?=$value['id']?>"></td>
			<td  class="hidden-xs" style="width:70px"><?=$value['reference'];?></td>
			<td style="width:50px"><span class="label <?php echo $lable; ?>"><?=$this->lang->line('application_ticket_status_'.$value['status']);?></span></td>
			<?php if(isset($value->user->id)){$user_id = $value->user->id; }else{ $user_id = FALSE; }?>
			<td><?=$value['subject'];?></td>
			<td class="hidden-xs"><span><?php if(isset($value['queue_name'])){ echo $value['queue_name'];}?></span></td>
			<td class="hidden-xs">
                <?php
                    if(!empty($value['assign_user']))
                    {
                        //print_r($value['clients']) ;
                        foreach ($value['assign_user'] as $k => $v) 
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
                <?php if( $value['user_id']==$this->sessionArr['user_id'] ){?>
                <a href="<?=base_url()?>ctickets/update/<?=$value['id'];?>" class="btn-option" data-toggle="mainmodal"><i class="fa fa-cog"></i></a>
                <?php }?>
             </td>

		</tr>

		<?php endforeach;?>
	 	</table>
	 	</form>
	 	</div>
	</div>
	</div>

<script>
$(document).ready(function(){ 


//chartjs
<?php
                                $days = array(); 
                                $data = "";
                                $this_week_days = array(
                                  date("Y-m-d",strtotime('monday this week')),
                                  date("Y-m-d",strtotime('tuesday this week')),
                                    date("Y-m-d",strtotime('wednesday this week')),
                                      date("Y-m-d",strtotime('thursday this week')),
                                        date("Y-m-d",strtotime('friday this week')),
                                          date("Y-m-d",strtotime('saturday this week')),
                                            date("Y-m-d",strtotime('sunday this week')));

                                $labels = '';
                                foreach ($tickets_opened_this_week as $value) {
                                  $days[$value->date_formatted] = $value->amount;
                                }
                                foreach ($this_week_days as $selected_day) {
                                  $y = 0;
                                  $labels .= '"'.$selected_day.'",';
                                  
                                    if(isset($days[$selected_day])){ $y = $days[$selected_day];}
                                      $data .= $y.",";
                                      $selday = $selected_day;
                                     } ?>


var ctx = document.getElementById("tileChart").getContext("2d");
    var myBarChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: [<?=$labels?>],
        datasets: [
        {
          label: "<?=$this->lang->line('application_new_tickets');?>",
          backgroundColor: "rgba(51, 195, 218, 0.3)",
          borderColor: "rgba(51, 195, 218, 1)",
          pointBorderColor: "rgba(51, 195, 218, 0)",
          pointBackgroundColor: "rgba(51, 195, 218, 1)",
          pointHoverBackgroundColor: "rgba(51, 195, 218, 1)",
          pointHitRadius: 25,
          pointRadius: 2,
          borderWidth: 2,
          data: [<?=$data;?>]
        }]
      },
       options: {
        title: {
            display: true,
            text: ' '
        },
       	maintainAspectRatio: false,
        tooltips:{
          enabled: true,
        },
        legend:{
          display: false
        },
        scales: {
          yAxes: [{
            gridLines: { 
                        display: false, 
                        lineWidth: 2,
                        color: "rgba(51, 195, 218, 0)"
                      },
            ticks: {
                        beginAtZero:true,
                        display: false,
                    }
          }],
          xAxes: [{
             gridLines: { 
                        display: false, 
                        lineWidth: 2,
                        color: "rgba(51, 195, 218, 0)"
                      },
            ticks: {
                        beginAtZero:true,
                        display: false,
                    }
          }]
        }
      }

    });

});
</script>
	
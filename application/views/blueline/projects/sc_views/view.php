<div class="row">
    <div class="col-xs-12 col-sm-12">

        <div class="row tile-row tile-view">
            <div class="col-md-1 col-xs-3">
                <div class="percentage easyPieChart" data-percent="<?= $project->progress; ?>"><span><?= $project->progress; ?>%</span></div>

            </div>
            <div class="col-md-11 col-xs-9 smallscreen"> 
                <h1><span class="nobold">#<?= $core_settings->project_prefix; ?><?= $project->reference; ?></span> - <?= $project->name; ?></h1>
                <p class="truncate description"><?= substr($project->description,0,50); ?></p>
            </div>

            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active hidden-xs"><a id="project_details_tab" href="#projectdetails-tab" aria-controls="projectdetails-tab" role="tab" data-toggle="tab"><?= $this->lang->line('application_project_details'); ?></a></li>
                <li role="presentation" class="hidden-xs"><a id="project_task_tab" href="#tasks-tab" aria-controls="tasks-tab" role="tab" data-toggle="tab"><?php if($mytasks != 0){?><span class="badge"><?=$mytasks?></span><?php } ?><?= $this->lang->line('application_tasks'); ?></a></li>
                <li role="presentation" class="hidden-xs"><a id="project_milestones_tab" href="#milestones-tab" aria-controls="tasks-tab" role="tab" data-toggle="tab"><?=$this->lang->line('application_milestones');?></a></li>
                <li role="presentation" class="hidden-xs"><a id="project_media_tab" href="#media-tab" aria-controls="media-tab" role="tab" data-toggle="tab"><?= $this->lang->line('application_media'); ?></a></li>
                <li role="presentation" class="hidden-xs"><a id="project_notes_tab" href="#notes-tab" aria-controls="notes-tab" role="tab" data-toggle="tab"><?= $this->lang->line('application_notes'); ?></a></li>
                <?php if ($invoice_access) { ?>
                    <li role="presentation" class="hidden-xs"><a id="project_invoices_tab" href="#invoices-tab" aria-controls="invoices-tab" role="tab" data-toggle="tab"><?= $this->lang->line('application_invoices'); ?></a></li>
                <?php } ?>
                <li role="presentation" class="hidden-xs"><a id="project_activities_tab" href="#activities-tab" aria-controls="activities-tab" role="tab" data-toggle="tab"><?= $this->lang->line('application_activities'); ?></a></li>

                <li role="presentation" class="dropdown visible-xs">
                    <a href="#" id="myTabDrop1" class="dropdown-toggle" data-toggle="dropdown" aria-controls="myTabDrop1-contents" aria-expanded="false"><?= $this->lang->line('application_overview'); ?> <span class="caret"></span></a>
                    <ul class="dropdown-menu" aria-labelledby="myTabDrop1" id="myTabDrop1-contents">
                        <li role="presentation" class="active"><a href="#projectdetails-tab" aria-controls="projectdetails-tab" role="tab" data-toggle="tab"><?= $this->lang->line('application_project_details'); ?></a></li>
                        <li role="presentation"><a href="#tasks-tab" aria-controls="tasks-tab" role="tab" data-toggle="tab"><?php if($mytasks != 0){?><span class="badge"><?=$mytasks?></span><?php } ?><?= $this->lang->line('application_tasks'); ?></a></li>
                        <li role="presentation" class="hidden-xs"><a href="#milestones-tab" aria-controls="tasks-tab" role="tab" data-toggle="tab"><?=$this->lang->line('application_milestones');?></a></li>
                        <li role="presentation"><a href="#media-tab" aria-controls="media-tab" role="tab" data-toggle="tab"><?= $this->lang->line('application_media'); ?></a></li>
                        <li role="presentation"><a href="#notes-tab" aria-controls="notes-tab" role="tab" data-toggle="tab"><?= $this->lang->line('application_notes'); ?></a></li>
                        <?php if ($invoice_access) { ?>
                            <li role="presentation"><a href="#invoices-tab" aria-controls="invoices-tab" role="tab" data-toggle="tab"><?= $this->lang->line('application_invoices'); ?></a></li>
                        <?php } ?>
                        <li role="presentation"><a href="#activities-tab" aria-controls="activities-tab" role="tab" data-toggle="tab"><?= $this->lang->line('application_activities'); ?></a></li>
                    </ul>
                </li>





            </ul>


        </div> 


    </div>
</div>
<div class="tab-content"> 

    <div class="row tab-pane fade in active" role="tabpanel" id="projectdetails-tab">

        <div class="col-xs-12 col-sm-9">
            <div class="table-head"><?= $this->lang->line('application_project_details'); ?></div>

            <div class="subcont">
                <ul class="details col-xs-12 col-sm-12 col-md-12">
                    <li><span><?= $this->lang->line('application_project_id'); ?>:</span> <?= $core_settings->project_prefix; ?><?= $project->reference; ?></li>
                    <!-- <li><span><?= $this->lang->line('application_category'); ?>:</span> <?= $project->category; ?></li> -->
                    <li><span>Company:</span> <?php if (!isset($project->company->name)) { ?> <a href="#" class="label label-default"><?php
                                echo $this->lang->line('application_no_client_assigned');
                            } else {
                                ?><a class="label label-success" href="#"><?php
                                    echo $project->company->name;
                                }
                                ?></a></li>
                    <li><span><?= $this->lang->line('application_start_date'); ?>:</span> <?php
                        $unix = human_to_unix($project->start . ' 00:00');
                        echo date($core_settings->date_format, $unix);
                                ?></li>
                    <li><span><?= $this->lang->line('application_deadline'); ?>:</span> <?php
                        $unix = human_to_unix($project->end . ' 00:00');
                        echo date($core_settings->date_format, $unix);
                                ?></li>
                    <li><span><?= $this->lang->line('application_time_spent'); ?>:</span> <?= $time_spent; ?> </li>
                    <li><span><?= $this->lang->line('application_created_on'); ?>:</span> <?php echo date($core_settings->date_format . ' ' . $core_settings->date_time_format, $project->datetime); ?></li>
                    <!-- <li><span><?= $this->lang->line('application_assigned_to'); ?>:</span> <?php foreach ($project->project_has_workers as $workers): ?> <a class="label label-info" style="padding: 2px 5px 3px;"><?php echo $workers->user->firstname . " " . $workers->user->lastname; ?></a><?php endforeach; ?> </li> -->

                </ul>
                <ul class="details col-xs-12 col-sm-12 col-md-6"><span class="visible-xs divider"></span>
                    <!-- <li><span><?= $this->lang->line('application_start_date'); ?>:</span> <?php
                        $unix = human_to_unix($project->start . ' 00:00');
                        echo date($core_settings->date_format, $unix);
                                ?></li>
                    <li><span><?= $this->lang->line('application_deadline'); ?>:</span> <?php
                        $unix = human_to_unix($project->end . ' 00:00');
                        echo date($core_settings->date_format, $unix);
                                ?></li> -->
                    <!-- <li><span><?= $this->lang->line('application_time_spent'); ?>:</span> <?= $time_spent; ?> </li>
                    <li><span><?= $this->lang->line('application_created_on'); ?>:</span> <?php echo date($core_settings->date_format . ' ' . $core_settings->date_time_format, $project->datetime); ?></li> -->
                </ul>
                <br clear="both">
            </div>


        </div>


        <div class="col-xs-12 col-sm-3">
            <div class="stdpad" > 
                <div class="table-head"><?= $this->lang->line('application_activities'); ?></div>
                <div id="main-nano-wrapper" class="nano">
                    <div class="nano-content">
                        <ul class="activity__list">
<?php foreach ($project->project_has_activities as $value) { ?>
                                <li>
                                    <h3 class="activity__list--header">
                                        <?php echo time_ago($value->datetime); ?>
                                    </h3>
                                    <p class="activity__list--sub truncate">
                                        <?php
                                        if (isset($value->user->id)) {
                                            echo $value->user->firstname . " " . $value->user->lastname . ' <a href="' . base_url() . 'scprojects/view/' . $value->project->id . '">' . $value->project->name . "</a>";
                                        }
                                        ?>
                                    </p>
                                    <div class="activity__list--body">
                                <?= character_limiter(str_replace(array("\r\n", "\r", "\n",), "", strip_tags($value->message)), 260); ?>
                                    </div>
                                </li>
    <?php
    $activities = true;
}
?>
                            <?php if (!isset($activities)) { ?>
                                <div class="empty">
                                    <i class="ion-ios-people"></i><br> 
    <?= $this->lang->line('application_no_recent_activities'); ?>
                                </div>
<?php } ?>
                        </ul>
                    </div>
                </div>


            </div>

        </div>



    </div>


    <div class="row tab-pane fade" role="tabpanel" id="tasks-tab">
        <div class="col-xs-12 col-sm-12  task-container-left">
            <div class="table-head"><?= $this->lang->line('application_tasks'); ?> 
                <span class=" pull-right">
                <!--<a class="btn btn-success toggle-closed-tasks tt" data-original-title="<?= $this->lang->line('application_hide_completed_tasks'); ?>" >
                         <i class="ion-checkmark-circled"></i>
                     </a>-->
                    <a href="<?= base_url() ?>scprojects/tasks/<?= $project->id; ?>/add" class="btn btn-primary" data-toggle="mainmodal">
<?= $this->lang->line('application_add_task'); ?>
                    </a>
                </span>
            </div>

            <div class="subcont no-padding min-height-410">

                <ul id="task-list" class="todo sortlist ">
                    <?php
                    $count = 0;
                    //echo "<pre>";print_r($task_list);exit;
                    foreach ($task_list as $value): $count = $count + 1;
                        //$disable = 'disabled="disabled"';
//              if($value->client_id == $this->client->id){ $disable = "";} 
//              if($value->created_by_client == $this->client->id){ $disable = "";} 
                        //var_dump($value);
                        ?>
                        <li id="task_<?php echo $value['id']; ?>" class="<?php echo $value['status']; ?> priority<?php echo $value['priority']; ?> list-item">

                            <a href="<?php echo base_url() ?>scprojects/tasks/<?php echo $project->id; ?>/check/<?php echo $value['id']; ?>" class="ajax-silent task-check"></a>

                            <input name="form-field-checkbox" class="checkbox-nolabel task-check dynamic-reload" data-reload="tile-pie" type="checkbox" data-link="<?php echo base_url() ?>scprojects/tasks/<?= $project->id; ?>/check/<?= $value['id']; ?>" <?php
                            if ($value['status'] == "done") {
                                echo "checked";
                            }
                        ?> />
                            <span class="lbl"> <p class="truncate name pointer" data-taskid="task-details-<?= $value['id']; ?>"><?= $value['task_name']; ?></p></span>
                            <span class="pull-right">
                                <!-- <?php if ($this->user->id != 0) { ?>
                                    <img class="img-circle list-profile-img tt"  title="<?= $this->user->firstname; ?> <?= $this->user->lastname; ?>"  src="<?= get_user_pic($this->user->userpic, $this->user->email); ?>">
                                <?php } ?> -->
                                <?php
                                foreach ($value['clients'] as $k => $v) {
                                    if (!empty($v['userpic'])) {
                                        echo "<img class='img-circle list-profile-img tt' title='" . $v['firstname'] . " " . $v['lastname'] . "' src='" . get_user_pic($v['userpic'], $v['email']) . "'>";
                                    } else {
                                        echo "<img class='img-circle list-profile-img tt' title='" . $v['firstname'] . " " . $v['lastname'] . "' src='' alt='image'>";
                                    }
                                }
                                ?>
                                <span class="list-button">
                                    <a class="edit-button" href="<?= base_url(); ?>scprojects/tasks/<?= $project->id ?>/update/<?= $value['id'] ?>" data-toggle="mainmodal">
                                        <i class="ion-android-settings"></i>
                                    </a>
                                    <a class="edit-button" href="<?= base_url(); ?>scprojects/tasks/<?= $project->id ?>/view/<?= $value['id'] ?>">
                                        <i class="fa fa-paperclip"></i>
                                    </a>
                                    <a  class="edit-button" href="<?= base_url(); ?>scprojects/tasks/<?= $project->id ?>/comment/<?= $value['id'] ?>">
                                        <i class="fa fa-comment"></i>
                                    </a>
                                </span>
                            </span>
                            </span>


                        </li>
<?php endforeach; ?>
<?php if ($count == 0) { ?>
                        <li class="notask list-item ui-state-disabled"><?= $this->lang->line('application_no_tasks_yet'); ?></li>
<?php } ?>



                </ul>
            </div>
        </div>
        <div class="col-sm-4 pin-to-top">

            <div class="subcont taskviewer-content">
                <?php
                foreach ($task_list as $value):
                    //$disable = 'disabled';
                    ?>
                    <div id="task-details-<?= $value['id']; ?>" class="todo-details">
                        <i class="ion-close pull-right todo__close"></i>
                        <h4>
                            <?= $value['name']; ?>
                        </h4> 
                            <?php //if ($disable == "") { ?>
                        <div class="grid grid--bleed task__options">
                            <?php
                            if ($value['tracking'] != 0 && $value['tracking'] != "") {
                                $start = "hidden";
                                $stop = "";
                            } else {
                                $start = "";
                                $stop = "hidden";
                            }
                            ?>
                            <a href="<?= base_url(); ?>scprojects/task_start_stop_timer/<?= $value['id']; ?>" data-timerid="timer<?= $value['id']; ?>" class="grid__col-6 grid__col--bleed center ajax-silent task__options__button task__options__button--green task__options__timer timer<?= $value['id']; ?> <?= $start ?>">
                                <?= $this->lang->line('application_start_timer'); ?>
                            </a>

                            <a href="<?= base_url(); ?>scprojects/task_start_stop_timer/<?= $value['id'] ?>" data-timerid="timer<?= $value['id']; ?>" class="grid__col-6 grid__col--bleed center ajax-silent task__options__button task__options__button--red task__options__timer timer<?= $value['id']; ?> <?= $stop ?>">
                                <?= $this->lang->line('application_stop_timer'); ?>
                            </a>

                            <a href="<?= base_url(); ?>scprojects/tasks/<?= $project->id ?>/update/<?= $value['id']; ?>" class="grid__col-6 grid__col--bleed task__options__button" data-toggle="mainmodal">
                        <?= $this->lang->line('application_edit'); ?>
                            </a>
                        </div>
    <?php //}  ?>
                        <ul class="details">

                            <li>
                                <span><?= $this->lang->line('application_time_spent'); ?></span>

                                <?php
                                if ($value['tracking'] != 0 && $value['tracking'] != "") {
                                    $timertime = (time() - $value['tracking']) + $value['time_spent'];
                                    $state = "resume";
                                } else {
                                    $timertime = ($value['time_spent'] != 0 && $value['time_spent'] != "") ? $value['time_spent'] : 0;
                                    $state = "pause";
                                }
                                ?> 

                                <span id="timer<?= $value['id']; ?>" class="badge timer__badge <?= $state ?>"></span>
                                <script>$(document).ready(function () {
                                        startTimer("<?= $state; ?>", "<?= $timertime; ?>", "#timer<?= $value['id']; ?>");
                                    });</script>
                                <a href="<?= base_url(); ?>scprojects/timesheets/<?= $value['id']; ?>" class="timer__icon_button tt" data-original-title="<?= $this->lang->line('application_timesheet'); ?>" data-toggle="mainmodal">
                                    <i class="ion-android-list "></i>
                                </a>

                            </li>
                            <li>
                                <span><?= $this->lang->line('application_priority'); ?></span> 
                                <?php
                                switch ($value['priority']) {
                                    case "0": echo $this->lang->line('application_no_priority');
                                        break;
                                    case "1": echo $this->lang->line('application_low_priority');
                                        break;
                                    case "2": echo $this->lang->line('application_med_priority');
                                        break;
                                    case "3": echo $this->lang->line('application_high_priority');
                                        break;
                                };
                                ?>
                            </li>
                            <li>
                                <span><?= $this->lang->line('application_progress'); ?></span> 
                                <a href="#" data-name="progress" class="editable synced-process-edit" data-syncto="progress-bar<?= $value['id']; ?>" data-type="range" data-pk="<?= $value['id']; ?>" data-url="<?= base_url() ?>scprojects/task_change_attribute"> 
                            <?= $value['progress']; ?>
                                </a> 
                            </li>
                                <?php if ($value['value'] != 0) { ?>
                                <li>
                                    <span><?= $this->lang->line('application_value'); ?></span> 
                                    <!-- <a href="#" data-name="value" class="editable" data-type="text" data-pk="<?= $value['id']; ?>" data-url="<?= base_url() ?>scprojects/task_change_attribute"> -->
                                <?= $value['value']; ?>
                                    <!-- </a> -->
                                </li>
                                <?php } ?>
                                <?php if ($value['start_date'] != "") { ?>
                                <li>
                                    <span><?= $this->lang->line('application_start_date'); ?></span> 
                                <?php
                                $unix = human_to_unix($value->start_date . ' 00:00');
                                echo date($core_settings->date_format, $unix);
                                ?>
                                </li>
                                <?php } ?>
                                <?php if ($value['due_date'] != "") { ?>
                                <li>
                                    <span><?= $this->lang->line('application_due_date'); ?></span> 
                                <?php
                                $unix = human_to_unix($value['due_date'] . ' 00:00');
                                echo date($core_settings->date_format, $unix);
                                ?>
                                </li>
                                <?php } ?>
                            <li>
                                <span><?= $this->lang->line('application_description'); ?></span> 
                                <p><?= $value['description']; ?></p>
                            </li>
                            <!-- <li>
                                <span>Attachment</span> 
                               <?php
                                if(!empty($value['task_attchment']))
                                {
                                    foreach ($value['task_attchment'] as $kk => $vv) 
                                    {
                                       $path=FCPATH.'files/tasks_attachment/'.$v['task_attach_file'];
                                       $attchment_url= site_url().'files/tasks_attachment/'.$vv['task_attach_file'];
                                       if(file_exists($path))
                                       {
                                          echo "<p><a class='btn btn-xs btn-success' href='".$attchment_url."' download='".$vv['task_attach_file']."'>Download</a>&nbsp;&nbsp;&nbsp;<a class='btn btn-xs btn-error' href='".base_url().'aoprojects/delete_tasks_attachement/'.$vv['project_id'].'/delete/'.$vv['task_id'].'/'.$vv['attachment_id']."' >Delete</a><br/></p>";
                                       }
                                       //echo "<span><span>";
                                    }
                                }
                                else
                                {
                                    echo "<p>No Attchment</p>"; 
                                }
                               ?>
                               </li> -->
                        </ul>

                    </div>
<?php endforeach; ?>


            </div>
        </div>
    </div>
    <div class="row tab-pane fade" role="tabpanel" id="milestones-tab">
     <div class="col-xs-12 col-sm-12 col-lg-6">
            <div class="table-head"><?=$this->lang->line('application_milestones');?> 
                 <span class=" pull-right">
                      <a href="<?=base_url()?>scprojects/milestones/<?=$project->id;?>/add" class="btn btn-primary" data-toggle="mainmodal">
                          <?=$this->lang->line('application_add_milestone');?>
                      </a>
                 </span>
            </div>
  

<div class="subcont no-padding min-height-410">
<ul id="milestones-list" class="todo sortlist sortable-list2">
    <?php  $count = 0; 
    foreach ($project->project_has_milestones as $milestone):  
            $count2 = 0; $count = $count+1; 
            //echo "<pre>";print_r($milestone);
        ?>
        
        <li id="milestoneLI_<?=$milestone->id;?>" class="hasItems">
            <h1 class="milestones__header ui-state-disabled">
               <i class="ion-android-list milestone__header__icon"></i>
                <?=$milestone->name;?>  
                <span class="pull-right"> 
                  <a href="<?=base_url()?>scprojects/milestones/<?=$milestone->project_id;?>/update/<?=$milestone->id;?>" data-toggle="mainmodal"><i class="ion-ios-gear milestone__header__right__icon"></i></a>
                  <a href="<?=base_url()?>scprojects/milestones/<?=$milestone->project_id;?>/view/<?=$milestone->id;?>" ><i class="fa fa-paperclip milestone__header__right__icon"></i></a>
                  <a href="<?=base_url()?>scprojects/milestones/<?=$milestone->project_id;?>/comment/<?=$milestone->id;?>" ><i class="fa fa-comment milestone__header__right__icon"></i></a>
                </span>      
            </h1>
            <ul id="milestonelist_<?=$milestone->id;?>" class="sortable-list">
                <?php  foreach ($milestone->project_has_tasks as $value):   $count2 =  $count2+1;  ?>
                <li id="milestonetask_<?=$value->id;?>" class="<?=$value->status;?> priority<?=$value->priority;?> list-item">
                    <a href="<?=base_url()?>scprojects/tasks/<?=$project->id;?>/check/<?=$value->id;?>" class="ajax-silent task-check"></a>
                    <input name="form-field-checkbox" class="checkbox-nolabel task-check dynamic-reload" data-reload="tile-pie" type="checkbox" data-link="<?=base_url()?>scprojects/tasks/<?=$project->id;?>/check/<?=$value->id;?>" <?php if($value->status == "done"){echo "checked";}?>/>
                    <span class="lbl">
                        <p class="truncate name"><?=$value->name;?></p>
                    </span>
                    <span class="pull-right">
                    <?php if ($value->user_id != 0) {  ?><img class="img-circle list-profile-img tt"  title="<?=$value->user->firstname;?> <?=$value->user->lastname;?>"  src="<?=get_user_pic($value->user->userpic, $value->user->email);?>"><?php } ?>
                    
                    <a href="<?=base_url()?>scprojects/tasks/<?=$project->id;?>/update/<?=$value->id;?>" class="edit-button" data-toggle="mainmodal"><i class="fa fa-cog"></i></a>
                    <a class="edit-button" href="<?= base_url(); ?>scprojects/tasks/<?= $project->id ?>/view/<?= $value->id; ?>">
                        <i class="fa fa-paperclip"></i>
                    </a>
                    <a class="edit-button" href="<?= base_url(); ?>scprojects/tasks/<?= $project->id ?>/comment/<?= $value->id; ?>">
                        <i class="fa fa-comment"></i>
                    </a>
                    </span>
                    
                </li>
                <?php endforeach;?>     
                    <?php if($count2 == 0){?> 
                      <li class="notask list-item ui-state-disabled"><?=$this->lang->line('application_no_tasks_yet');?></li>
                    <?php }?> 
                </ul>
          </li>
          <?php endforeach;?>
        
            <?php if($count == 0) { ?>
            <li class="notask list-item ui-state-disabled"><?=$this->lang->line('application_no_milestones_yet');?></li>
            <?php } ?>
</ul>
                </div>
               </div>
            


            <div class="col-xs-12 col-sm-12 col-lg-6">
             <div class="table-head">
                <?=$this->lang->line('application_tasks_without_milestone');?>   
            </div>
            <div class="subcont no-padding min-height-410">
            <ul id="task-list2" class="todo sortable-list">
                <?php $count3 = 0; foreach ($tasksWithoutMilestone as $value):   $count3 =  $count3+1;  ?>
                <li id="milestonetask_<?=$value['id'];?>" class="<?=$value['status'];?> priority<?=$value['priority'];?> list-item">
                    <a href="<?=base_url()?>scprojects/tasks/<?=$project->id;?>/check/<?=$value['id'];?>" class="ajax-silent task-check"></a>
                    <input name="form-field-checkbox" class="checkbox-nolabel task-check dynamic-reload" data-reload="tile-pie" type="checkbox" data-link="<?=base_url()?>scprojects/tasks/<?=$project->id;?>/check/<?=$value['id'];?>" <?php if($value['status'] == "done"){echo "checked";}?>/>
                    <span class="lbl">
                        <p class="truncate name"><?=$value['task_name'];?></p>
                    </span>
                    <span class="pull-right">
                    <?php if ($value['user_id'] != 0) { 
                        foreach ($value['clients'] as $k => $v) {
                            if (!empty($v['userpic'])) {
                                echo "<img class='img-circle list-profile-img tt' title='" . $v['firstname'] . " " . $v['lastname'] . "' src='" . get_user_pic($v['userpic'], $v['email']) . "'>";
                            } else {
                                echo "<img class='img-circle list-profile-img tt' title='" . $v['firstname'] . " " . $v['lastname'] . "' src='' alt='image'>";
                            }
                        }
                    } 
                    ?>
                    
                    <a href="<?=base_url()?>scprojects/tasks/<?=$project->id;?>/update/<?=$value['id'];?>" class="edit-button" data-toggle="mainmodal"><i class="fa fa-cog"></i></a>
                    <a class="edit-button" href="<?= base_url(); ?>scprojects/tasks/<?= $project->id ?>/view/<?= $value['id'] ?>">
                        <i class="fa fa-paperclip"></i>
                    </a>
                    <a class="edit-button" href="<?= base_url(); ?>scprojects/tasks/<?= $project->id ?>/comment/<?= $value['id'] ?>">
                        <i class="fa fa-comment"></i>
                    </a>
                    </span>
                </li>
                <?php endforeach;?>     
                    <?php if($count3 == 0){?> 
                      <li class="notask list-item ui-state-disabled"><?=$this->lang->line('application_no_tasks_without_milestone');?></li>
                    <?php }?> 
                </ul>
            </div>
            </div>


</div>
    <div class="row tab-pane fade" role="tabpanel" id="media-tab">
        <div class="col-xs-12 col-sm-12">
            <div class="table-head"><?= $this->lang->line('application_media'); ?> <span class=" pull-right"><a href="<?= base_url() ?>scprojects/media/<?= $project->id; ?>/add" class="btn btn-primary" data-toggle="mainmodal"><?= $this->lang->line('application_add_media'); ?></a></span></div>
            <div class="table-div min-height-410">
                <table id="media" class="table data-media" rel="<?= base_url() ?>scprojects/media/<?= $project->id; ?>" cellspacing="0" cellpadding="0">
                    <thead>
                        <tr>
                            <th  class="hidden"></th>
                            <th><?= $this->lang->line('application_name'); ?></th>
                            <th class="hidden-xs"><?= $this->lang->line('application_filename'); ?></th>
                            <th class="hidden-xs"><?= $this->lang->line('application_phase'); ?></th>
                            <th class="hidden-xs"><i class="fa fa-download"></i></th>
                        </tr></thead>

                    <tbody>
<?php foreach ($project->project_has_files as $value): ?>

                            <tr id="<?= $value->id; ?>">
                                <td class="hidden"><?= human_to_unix($value->date); ?></td>
                                <td onclick=""><?= $value->name; ?></td>
                                <td class="hidden-xs truncate" style="max-width: 80px;"><?= $value->filename; ?></td>
                                <td class="hidden-xs"><?= $value->phase; ?></td>
                                <td class="hidden-xs"><span class="label label-info tt" title="<?= $this->lang->line('application_download_counter'); ?>" ><?= $value->download_counter; ?></span></td>

                            </tr>

                <?php endforeach; ?>



                    </tbody></table>
                <?php if (!$project->project_has_files) { ?>
                    <div class="no-files">  
                        <i class="fa fa-cloud-upload"></i><br>
                        No files have been uploaded yet!
                    </div>
            <?php } ?>
            </div>
        </div>
    </div>
    <div class="row tab-pane fade" role="tabpanel" id="notes-tab">
        <div class="col-xs-12 col-sm-12">
<?php
$attributes = array('class' => 'note-form', 'id' => '_notes');
echo form_open(base_url() . "scprojects/notes/" . $project->id, $attributes);
?>
            <div class="table-head"><?= $this->lang->line('application_notes'); ?> <span class=" pull-right"><a id="send" name="send" class="btn btn-primary button-loader"><?= $this->lang->line('application_save'); ?></a></span><span id="changed" class="pull-right label label-warning"><?= $this->lang->line('application_unsaved'); ?></span></div>

            <textarea class="input-block-level summernote-note" name="note" id="textfield" ><?= $project->note; ?></textarea>
            </form>
        </div>

    </div>

<?php if ($invoice_access) { ?>
        <div class="row tab-pane fade" role="tabpanel" id="invoices-tab">
            <div class="col-xs-12 col-sm-12">
                <div class="table-head"><?= $this->lang->line('application_invoices'); ?> <span class=" pull-right"></span></div>
                <div class="table-div">
                    <table class="data table" id="cinvoices" rel="<?= base_url() ?>" cellspacing="0" cellpadding="0">
                        <thead>
                        <th width="70px" class="hidden-xs"><?= $this->lang->line('application_invoice_id'); ?></th>
                        <th><?= $this->lang->line('application_client'); ?></th>
                        <th class="hidden-xs"><?= $this->lang->line('application_issue_date'); ?></th>
                        <th class="hidden-xs"><?= $this->lang->line('application_due_date'); ?></th>
                        <th><?= $this->lang->line('application_status'); ?></th>
                        </thead>
                                    <?php foreach ($project_has_invoices as $value): ?>

                            <tr id="<?= $value->id; ?>" >
                                <td class="hidden-xs" onclick=""><?= $core_settings->invoice_prefix; ?><?= $value->reference; ?></td>
                                <td onclick=""><span class="label label-info"><?php
                                        if (isset($value->company->name)) {
                                            echo $value->company->name;
                                        }
                                        ?></span></td>
                                <td class="hidden-xs"><span><?php
                                    $unix = human_to_unix($value->issue_date . ' 00:00');
                                    echo '<span class="hidden">' . $unix . '</span> ';
                                    echo date($core_settings->date_format, $unix);
                                    ?></span></td>
                                <td class="hidden-xs"><span class="label <?php
                                                            if ($value->status == "Paid") {
                                                                echo 'label-success';
                                                            } if ($value->due_date <= date('Y-m-d') && $value->status != "Paid") {
                                                                echo 'label-important tt" title="' . $this->lang->line('application_overdue');
                                                            }
                                                            ?>"><?php
                                    $unix = human_to_unix($value->due_date . ' 00:00');
                                    echo '<span class="hidden">' . $unix . '</span> ';
                                    echo date($core_settings->date_format, $unix);
                                    ?></span> <span class="hidden"><?= $unix; ?></span></td>
                                <td onclick=""><span class="label <?php
                                    $unix = human_to_unix($value->sent_date . ' 00:00');
                                    if ($value->status == "Paid") {
                                        echo 'label-success';
                                    } elseif ($value->status == "Sent") {
                                        echo 'label-warning tt" title="' . date($core_settings->date_format, $unix);
                                    }
                                    ?>"><?= $this->lang->line('application_' . $value->status); ?></span></td>
                            </tr>

                        <?php endforeach; ?>
                    </table>
                    <?php if (!$project_has_invoices) { ?>
                        <div class="no-files">  
                            <i class="fa fa-file-text"></i><br>

        <?= $this->lang->line('application_no_invoices_yet'); ?>
                        </div>
        <?php } ?>
                </div>
            </div>             


        </div>
<?php } ?>



    <div class="row tab-pane fade" role="tabpanel" id="activities-tab">
        <div class="col-xs-12 col-sm-12">
            <div class="table-head"><?= $this->lang->line('application_activities'); ?>
                <span class=" pull-right"><a class="btn btn-primary open-comment-box"><?= $this->lang->line('application_new_comment'); ?></a></span>
            </div>
            <div class="subcont" > 

                <ul id="comments-ul" class="comments">
                    <li class="comment-item add-comment">
<?php
$attributes = array('class' => 'ajaxform', 'id' => 'replyform', 'data-reload' => 'comments-ul');
echo form_open(base_url().'scprojects/activity/' . $project->id . '/add', $attributes);
?>
                        <div class="comment-pic">
                            <img class="img-circle tt" title="<?= $this->user->firstname ?> <?= $this->user->lastname ?>"  src="<?= get_user_pic($this->user->userpic, $this->user->email); ?>">

                        </div>
                        <div class="comment-content">
                            <h5><input type="text" name="subject" class="form-control" id="subject" placeholder="<?= $this->lang->line('application_subject'); ?>..." required/></h5>
                            <p><small class="text-muted"><span class="comment-writer"><?= $this->client->firstname ?> <?= $this->client->lastname ?></span> <span class="datetime"><?php echo date($core_settings->date_format . ' ' . $core_settings->date_time_format, time()); ?></span></small></p>
                            <p><textarea class="input-block-level summernote" id="reply" name="message" placeholder="<?= $this->lang->line('application_write_message'); ?>..." required/></textarea></p>
                            <button id="send" name="send" class="btn btn-primary button-loader"><?= $this->lang->line('application_send'); ?></button>
                            <button id="cancel" name="cancel" class="btn btn-danger open-comment-box"><?= $this->lang->line('application_close'); ?></button>

                        </div>
                        </form>
                    </li>
                    <?php foreach ($project->project_has_activities as $value): ?>
                        <?php
                        $writer = FALSE;
                        if ($value->user_id != 0) { 
                          $get_user_details=User::find($value->user_id);
                          $writer = $get_user_details->firstname." ".$get_user_details->lastname;
                      $image = get_user_pic($get_user_details->userpic, $get_user_details->email);
                      
                      }
                        ?>
                        <li class="comment-item">
                            <div class="comment-pic">
    <?php if ($writer != FALSE) { ?>
                                    <img class="img-circle tt" title="<?= $writer ?>"  src="<?= $image ?>">
    <?php } else { ?> <i class="fa fa-rocket"></i> <?php } ?>
                            </div>
                            <div class="comment-content">
                                <h5><?= $value->subject; ?></h5>
                                <p><small class="text-muted"><span class="comment-writer"><?= $writer ?></span> <span class="datetime"><?php echo date($core_settings->date_format . ' ' . $core_settings->date_time_format, $value->datetime); ?></span></small></p>
                                <p><?= $value->message; ?></p>
                            </div>
                        </li>
<?php endforeach; ?>
                    <li class="comment-item">
                        <div class="comment-pic"><i class="fa fa-bolt"></i></div>
                        <div class="comment-content">
                            <h5><?= $this->lang->line('application_project_created'); ?></h5>
                            <p><small class="text-muted"><?php echo date($core_settings->date_format . ' ' . $core_settings->date_time_format, $project->datetime); ?></small></p>
                            <p><?= $this->lang->line('application_project_has_been_created'); ?></p>
                        </div>
                    </li>  
                </ul>            




            </div>
        </div>
    </div>
    <style type="text/css">

        .circular-bar{
            text-align: center;

            margin:10px 20px;
        }
        .circular-bar-content{
            margin-bottom: 70px;
            margin-top: -100px;
            text-align: center;
        }
        .circular-bar-content strong{
            display: block;
            font-weight: 400;
            @include font-size(18,24);
        }
        .circular-bar-content label, .circular-bar-content span{
            display: block;
            font-weight: 400;
            font-size: 18px;
            color: #505458;
            @include font-size(15,20);
        }


    </style>
    <script type="text/javascript">
        $(document).ready(function () {
            $('.dial').each(function () {

                var elm = $(this);
                var color = elm.attr("data-fgColor");
                var perc = elm.attr("value");

                elm.knob({
                    'value': 0,
                    'min': 0,
                    'max': 100,
                    "skin": "tron",
                    "readOnly": true,
                    "thickness": .13,
                    'dynamicDraw': true,
                    "displayInput": false,

                });

                $({value: 0}).animate({value: perc}, {
                    duration: 1000,
                    easing: 'swing',
                    progress: function () {
                        elm.val(Math.ceil(this.value)).trigger('change')
                    }
                });

                //circular progress bar color
                $(this).append(function () {
                    elm.parent().parent().find('.circular-bar-content').css('color', color);
                    elm.parent().parent().find('.circular-bar-content label').text(perc + '%');
                });

            });

        });

    </script> 
    <div id="tkKey" class="hidden"><?= $this->security->get_csrf_hash(); ?></div>
    <div id="baseURL" class="hidden"><?= base_url(); ?>scprojects/index/<?php echo $this->sessionArr['company_id']; ?></div>
    <div id="projectId" class="hidden"><?= $project->id; ?></div>



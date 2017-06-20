<?php   
$attributes = array('class' => '', 'id' => '_ticket');
echo form_open_multipart($form_action, $attributes); 
if(isset($ticket)){ ?>
<input id="id" type="hidden" name="id" value="<?php echo $ticket->id; ?>" />
<?php } ?>

 <div class="form-group">
        <label for="type"><?=$this->lang->line('application_type');?></label>
        <?php $options = array();
                foreach ($types as $value):  
                $options[$value->id] = $value->name;
                endforeach;
        if(isset($ticket) && isset($ticket->type->id)){$type = $ticket->type->id;}else{$type = $settings->ticket_default_type;}
        echo form_dropdown('type_id', $options, $type, 'style="width:100%" class="chosen-select"');?>
</div> 

 <div class="form-group">
        <label for="queue"><?=$this->lang->line('application_queue');?></label>
        <?php $options = array();
                foreach ($queues as $value):  
                $options[$value->id] = $value->name;
                endforeach;
        if(isset($ticket) && isset($ticket->queue->id)){$queue = $ticket->queue->id;}else{$queue = "";}
        echo form_dropdown('queue_id', $options, $queue, 'style="width:100%" class="chosen-select"');?>
</div> 

 <div class="form-group">
        <label for="user"><?=$this->lang->line('application_assign_to');?></label>
        <select name="assign_user_id[]" multiple  class="chosen-select">
            <?php
            foreach ($users as $key=>$value) 
            {
                echo "<optgroup label='".$key."'>";
                foreach($value as $k=>$v)
                {
                    if (!empty($task_assign_users)) 
                    {
                        if(in_array($v['id'],$task_assign_users))
                        {
                            echo "<option value='" . $v['id'] . "' selected='selected'>" . $v['firstname'] . ' ' . $v['lastname'] . "</option>";
                        }
                        else
                        {
                            echo "<option value='" . $v['id'] . "'>" . $v['firstname'] . ' ' . $v['lastname'] . "</option>";
                        }
                    }
                    else
                    {
                        echo "<option value='" . $v['id'] . "'>" . $v['firstname'] . ' ' . $v['lastname'] . "</option>";
                    }
                }
                echo "</optgroup>";
            }
            ?>
        </select>
        
        <?php /*$options = array();
                $options['0'] = '-';
                foreach ($users as $value):  
                $options[$value->id] = $value->firstname.' '.$value->lastname;
                endforeach;
        if(isset($ticket) && isset($ticket->user->id)){$user = $ticket->user->id;}else{$user = "";}
        echo form_dropdown('user_id', $options, $user, 'multiple style="width:100%" class="chosen-select"');*/?>
</div> 

 <div class="form-group">
        <label for="subject"><?=$this->lang->line('application_subject');?> *</label>
        <input id="subject" type="text" name="subject" class="form-control" value="<?php if(isset($ticket)){echo $ticket->subject;} ?>"  required/>
</div> 

 <div class="form-group">
        <label for="text"><?=$this->lang->line('application_message');?> *</label>
        <textarea id="text" name="text" rows="9" class="form-control summernote-modal"></textarea>
</div> 

<div class="form-group">
                <label for="userfile"><?=$this->lang->line('application_attachment');?></label><div>
                <input id="uploadFile" class="form-control uploadFile" placeholder="Choose File" disabled="disabled" />
                          <div class="fileUpload btn btn-primary">
                              <span><i class="fa fa-upload"></i><span class="hidden-xs"> <?=$this->lang->line('application_select');?></span></span>
                              <input id="uploadBtn" type="file" name="userfile" class="upload" />
                          </div>
                  </div>
              </div>
              <?php if( !empty($attachments) ){?>
          <a class="label label-info" href="<?=site_url()?>files/media/<?php echo $attachments[0]['savename']; ?>" download="<?php echo $attachments[0]['filename']; ?>"><?php echo $attachments[0]['filename']; ?></a>
      <?php }?>
        <div class="modal-footer">
        <input type="button" onclick="funTicketSub()" name="send" class="btn btn-primary" value="<?=$this->lang->line('application_save');?>"/>
        <a class="btn" data-dismiss="modal"><?=$this->lang->line('application_close');?></a>
        </div>


<?php echo form_close(); ?>

<?php 
  $attributes = array('class' => '', 'id' => '_message');
  echo form_open_multipart($form_action, $attributes);
?>
<div class="form-group">
  <label for="name"><?=$this->lang->line('application_to');?></label><br>
  <select name="recipient" class="chosen-select" placeholder="<?php echo $this->lang->line('application_clients');?>" >
  <option value='' selected='selected'></option>
  <?php 
  /*  $options = array();
    foreach ($clients as $value):  
      $options[$this->lang->line('application_clients')]["c".$value->id] = $value->firstname.' '.$value->lastname.' ['.$value->company->name.']';
    endforeach;

    foreach ($sub_contractors as $value):  
      $options[$this->lang->line('application_subcontractor')]["s".$value->id] = $value->firstname.' '.$value->lastname.' ['.$value->company->name.']';
    endforeach;

/*    foreach ($sub_contractors as $value):  
        if($value->id != $this->user->id)
        {
        $options[$this->lang->line('application_agents')]["s".$value->id] = $value->firstname.' '.$value->lastname;
        }
    endforeach;*/
    /*echo form_dropdown('recipient', $options, '', 'style="width:100%" class="chosen-select"');*/
    foreach ($clients as $key=>$value) 
    {
        echo "<optgroup label='".$key."'>";
        if($key == "Clients")
        {
            $user_prefix = 'c';
        }
        elseif ($key == "Sub-contractors") 
        {
            $user_prefix = 's';
        }
        foreach($value as $k=>$v)
        {
            if (!empty($project_assign_users)) 
            {
                if(in_array($v['id'],$project_assign_users))
                {
                    echo "<option value='" . $user_prefix . $v['id'] . "' selected='selected'>" . $v['firstname'] . ' ' . $v['lastname'] . "</option>";
                }
                else
                {
                    echo "<option value='" . $user_prefix . $v['id'] . "'>" . $v['firstname'] . ' ' . $v['lastname'] . "</option>";
                }
            }
            else
            {
                echo "<option value='" . $user_prefix . $v['id'] . "'>" . $v['firstname'] . ' ' . $v['lastname'] . "</option>";
            }
        }
        echo "</optgroup>";
    }
  ?>
  </select>
</div>

<div class="form-group">
  <label for="subject"><?=$this->lang->line('application_subject');?></label>
  <input type="text" name="subject" class="form-control" id="subject" placeholder="<?=$this->lang->line('application_subject');?>" required/>
</div>

<div class="form-group">
  <label for="message"><?=$this->lang->line('application_message');?></label>
  <textarea class="input-block-level summernote-modal"  id="textfield" name="message"></textarea>
</div>

<div class="form-group">
  <label><?=$this->lang->line('application_attachment');?></label>
  <div>
      <input id="uploadFile" class="form-control uploadFile" placeholder="Choose File" disabled="disabled" />
      <div class="fileUpload btn btn-primary">
          <span><i class="fa fa-upload"></i><span class="hidden-xs"> <?=$this->lang->line('application_select');?></span></span>
          <input id="uploadBtn" type="file" name="userfile" class="upload" />
      </div>
  </div>
</div>
<div class="modal-footer">
    <input type="submit" name="send" class="btn btn-primary" value="<?=$this->lang->line('application_send');?>"/>
    <a class="btn btn-default" data-dismiss="modal"><?=$this->lang->line('application_close');?></a>
</div>

<?php echo form_close(); ?>
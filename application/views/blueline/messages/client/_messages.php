<?php 
$attributes = array('class' => 'c_message_form', 'id' => '_message');
echo form_open_multipart($form_action, $attributes);
?>
<div class="form-group">
  <label for="name"><?=$this->lang->line('application_to');?></label><br>
  <select name="recipient" class="chosen-select" placeholder="<?php echo $this->lang->line('application_clients');?>" >
  <!-- <option value='' selected='selected'></option> -->
  <?php 
    foreach ($clients as $key=>$value) 
    {
      //print_r($value);
        echo "<optgroup label='".$key."'>";
       /* if($key == "Account-owner")
        {
            $user_prefix = 'u';
        }
        elseif ($key == "Clients") 
        {
            $user_prefix = 'c';
        }
        elseif ($key == "Sub-contractors") 
        {
            $user_prefix = 's';
        }*/
        $user_prefix = 'u';
        foreach($value as $k=>$v)
        {

            if (!empty($project_assign_users)) 
            {
              //print_r($project_assign_users);die;
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
                <div><input id="uploadFile" class="form-control uploadFile" placeholder="Choose File" disabled="disabled" />
                          <div class="fileUpload btn btn-primary">
                              <span><i class="fa fa-upload"></i><span class="hidden-xs"> <?=$this->lang->line('application_select');?></span></span>
                              <input id="uploadBtn" type="file" name="userfile" class="upload" />
                          </div>
            </div>
        </div>


        <div class="modal-footer">
        <input type="submit" name="send" class="btn btn-primary send" value="<?=$this->lang->line('application_send');?>"/>
        <a class="btn btn-default" data-dismiss="modal"><?=$this->lang->line('application_close');?></a>
        </div>

</form>
<?php echo form_close(); ?>
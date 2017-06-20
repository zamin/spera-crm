<link href="<?=site_url()?>assets/blueline/css/plugins/video-js.css" rel="stylesheet">
 <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/plugins/pdfobject.js"></script>
 <div class="row">
<div class="col-xs-12 col-sm-12">
	<div class="table-head">Milestone Comment Details
<span class=" pull-right btn-backp"><a class="btn btn-primary" href="<?=base_url()?><?=$backlink;?>"><i class="fa fa-arrow-left"></i> <?=$this->lang->line('application_back_to_milestones');?></a><a class="btn btn-primary open-comment-box"><?=$this->lang->line('application_new_comment');?></a></span>  </div>  
  <div class="subcont" > 
  <ul id="comments-ul" class="comments">
    <li class="comment-item add-comment">
      <?php   
                $attributes = array('class' => 'ajaxform', 'id' => 'replyform', 'data-reload' => 'comments-ul');
                echo form_open(base_url().'cprojects/milestones/'.$milestone->project_id.'/comment/'.$milestone->id, $attributes); 
                ?>
      <div class="comment-pic">
        <img class="img-circle tt" title="<?=$this->user->firstname?> <?=$this->user->lastname?>"  src="<?=get_user_pic($this->user->userpic, $this->user->email);?>">
      
      </div>
      <div class="comment-content">
            <p><small class="text-muted"><span class="comment-writer"><?=$this->user->firstname?> <?=$this->user->lastname?></span> <span class="datetime"><?php  echo date($core_settings->date_format.' '.$core_settings->date_time_format, time()); ?></span></small></p>
            <p><textarea class="input-block-level summernote" id="reply" name="message" placeholder="<?=$this->lang->line('application_write_message');?>..." required/></textarea></p>
            <button id="send" name="send" class="btn btn-primary button-loader"><?=$this->lang->line('application_send');?></button>
            <button id="cancel" name="cancel" class="btn btn-danger open-comment-box"><?=$this->lang->line('application_close');?></button>       
      </div>
       </form>
    </li>
  <?php foreach ($milestone_comments as $value):?>
      <?php 
      $writer = FALSE;
      if ($value['user_id'] != 0) { 
          $user=User::find_by_id($value['user_id']);
          $writer = $user->firstname." ".$user->lastname;
      $image = get_user_pic($user->userpic, $user->email);
      
      }
      ?>
      <li class="comment-item">
      <div class="comment-pic">
        <?php if ($writer != FALSE) {  ?>
        <img class="img-circle tt" title="<?=$writer?>"  src="<?=$image?>">
        <?php }else{?> <i class="fa fa-rocket"></i> <?php } ?>
      </div>
      <div class="comment-content">
            <p><small class="text-muted"><span class="comment-writer"><?=$writer?></span> <span class="datetime"><?php  echo date($core_settings->date_format.' '.$core_settings->date_time_format, $value['datetime']); ?></span></small></p>
            <p><?=$value['message'];?></p>
      </div>
      </li>
<?php endforeach;?>
  </ul>            
</div>
</div>

</div>



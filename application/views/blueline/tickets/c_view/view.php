<div class="row">
        <div class="col-xs-12 col-md-3">

	 	<div class="table-head"><?=$this->lang->line('application_ticket_details');?></div>
		<div class="subcont">
			<ul class="details">
			<?php $lable = FALSE; if($ticket->status == "new"){ $lable = "label-important"; }elseif($ticket->status == "open"){$lable = "label-warning";}elseif($ticket->status == "closed" || $ticket->status == "inprogress"){$lable = "label-success";}elseif($ticket->status == "reopened"){$lable = "label-warning";} ?>
	
				<li><span><?=$this->lang->line('application_ticket_number');?></span> #<?=$ticket->reference;?></li>
				<li><span><?=$this->lang->line('application_status');?></span> <a class="label <?php echo $lable; ?>"><?=$this->lang->line('application_ticket_status_'.$ticket->status);?></a></li>
				<li><span><?=$this->lang->line('application_type');?></span> <?php if(isset($ticket->type->name)){ ?><?=$ticket->type->name;?> <?php } ?></li>
				<li><span><?=$this->lang->line('application_from');?></span> <?php if(isset($ticket->user->email)){ echo '<a class="tt" title="'.$ticket->user->email.'">'.$ticket->user->firstname.' '.$ticket->user->lastname.'</a>';
				$emailsender = $ticket->client->email;
				}
				else{ 
    				$explode = explode(' - ', $ticket->from); 
    				if(isset($explode[1])){ 
    				$emailsender = $explode[1];
    			    $emailname = str_replace('"', '', $explode[0]);
    			    $emailname = str_replace('<', '', $emailname);
    			    $emailname = str_replace('>', '', $emailname);
    			    $emailname = explode(' ', $emailname); 
    			    $emailname = $emailname[0];
				}else{ $explodeemail = "-"; } 
				echo '<a class="tt" title="'.addslashes($emailsender).'">'.$emailname.'</a>'; } ?></li>
				<li>
						<span>
							<?=$this->lang->line('application_queue');?>
						</span> 
						<?php if(isset($ticket->queue->name)){ ?><?=$ticket->queue->name;?> <?php } ?>
				</li>
				<li>
                    <span>
                        <?=$this->lang->line('application_created');?>
                    </span> 
                    <?php if(isset($ticket->user->firstname)){ ?><?=$ticket->user->firstname;?> <?=$ticket->user->lastname;?> <?php } else{ echo "-";} ?> 
                </li>
                <li>
                    <span>
                        <?=$this->lang->line('application_created_on');?>
                    </span> 
                    <?php echo date($core_settings->date_format.'  '.$core_settings->date_time_format, $ticket->created); ?>
                </li>
                <li>
                <span><?=$this->lang->line('application_owner');?></span>
                <?php
                    if(!empty($assign_user))
                    {
                        //print_r($value['clients']) ;
                        foreach ($assign_user as $k => $v) 
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
                </li>
				
				</ul>

			
	 </div>
	 <br>

       <div class="table-head"><?=$this->lang->line('application_client');?></div>
        <div class="subcont">
            <ul class="details">
                <?php if(isset($ticket->user->firstname)){ ?><li><span><?=$this->lang->line('application_name');?>:</span>  <?php echo $ticket->user->firstname.' '.$ticket->user->lastname; ?> </li><?php } ?>
                <?php if(isset($ticket->user->email)){ ?> <li><span><?=$this->lang->line('application_email');?>:</span> <?=$ticket->user->email;?></li><?php } ?>
                </ul>
     </div>
	</div>
	 <div class="col-xs-12 col-md-9">


	 			<a id="fadein" class="btn btn-success" style="margin-top: -2px;"><?=$this->lang->line('application_reply_back');?></a>
	 		 	<div class="btn-group nav-tabs hidden-xs">

	                <a class="btn btn-primary backlink" id="back" href="<?=base_url()?>ctickets"><?=$this->lang->line('application_back');?></a>
	                <a class="btn btn-primary" id="note" data-toggle="mainmodal" href="<?=base_url()?>ctickets/article/<?=$ticket->id;?>/add"><?=$this->lang->line('application_add_note');?></a>
	                <?php /*<a class="btn btn-primary" id="queue" data-toggle="mainmodal" href="<?=base_url()?>ctickets/queue/<?=$ticket->id;?>"><?=$this->lang->line('application_queue');?></a>*/?>
	                <?php /*<a class="btn btn-primary" id="type" data-toggle="mainmodal" href="<?=base_url()?>ctickets/type/<?=$ticket->id;?>"><?=$this->lang->line('application_type');?></a>*/?>
	                <?php /*<a class="btn btn-primary" id="assign" data-toggle="mainmodal" href="<?=base_url()?>ctickets/assign/<?=$ticket->id;?>"><?=$this->lang->line('application_assign');?></a> */?>
  					<?php /*<a class="btn btn-primary" id="status" data-toggle="mainmodal" href="<?=base_url()?>ctickets/status/<?=$ticket->id;?>"><?=$this->lang->line('application_status');?></a>*/?>
  					<?php /*<a class="btn btn-primary" id="close" data-toggle="mainmodal" href="<?=base_url()?>ctickets/close/<?=$ticket->id;?>"><?=$this->lang->line('application_close');?></a>*/?>

	        </div> 
	        <div class="btn-group pull-right visible-xs">
			  <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
			    <i class="fa fa-cog"></i> <span class="caret"></span>
			  </button>
			  <ul class="dropdown-menu" role="menu">
			    <li><a class=" backlink" id="back" href="#"><?=$this->lang->line('application_back');?></a>
				<li><a id="note" data-toggle="mainmodal" href="<?=base_url()?>ctickets/article/<?=$ticket->id;?>/add"><?=$this->lang->line('application_add_note');?></a></li>
				<?php /*<li><a id="queue" data-toggle="mainmodal" href="<?=base_url()?>ctickets/queue/<?=$ticket->id;?>"><?=$this->lang->line('application_queue');?></a></li>*/?>
				<?php /*<li><a id="type" data-toggle="mainmodal" href="<?=base_url()?>ctickets/type/<?=$ticket->id;?>"><?=$this->lang->line('application_type');?></a></li>*/?>
				<?php /*<li><a id="assign" data-toggle="mainmodal" href="<?=base_url()?>ctickets/assign/<?=$ticket->id;?>"><?=$this->lang->line('application_assign');?></a></li>*/?>
			  	<?php /*<li><a id="close" data-toggle="mainmodal" href="<?=base_url()?>ctickets/close/<?=$ticket->id;?>"><?=$this->lang->line('application_close');?></a></li>*/?>
			  </ul>
			</div>

	        <div class="message-content-reply fadein no-padding">
					    <?php   
					    $attributes = array('class' => '', 'id' => 'replyform');
					    echo form_open(base_url().'ctickets/article/'.$ticket->id.'/add', $attributes); 
					    ?>
					    <input id="ticket_id" type="hidden" name="ticket_id" value="<?php echo $ticket->id; ?>" />
					    <input type="hidden" name="to" value="<?=addslashes($emailsender);?>">
					    <input type="hidden" name="internal" value="no">
					    <input type="hidden" name="subject" value="<?=$ticket->subject;?>">
					    <textarea id="reply" name="message" class="summernote" placeholder="<?=$this->lang->line('application_quick_reply');?>"></textarea>
					    <div class="ticket-textarea-footer">
					    <button id="send" name="send" class="btn btn-primary button-loader"><?=$this->lang->line('application_send');?></button>
					  	</div>
					    <?php echo form_close(); ?>

					  </div>
	        <div class="article-content">
					<h4><p class="truncate">[#<?=$ticket->reference;?>] <?=$ticket->subject;?></p></h4>
						<hr>
					
					<div class="article">
						<?=$ticket->text;?>

						<?php if(isset($ticket->ticket_has_attachments[0])){echo '<hr>'; } ?>
						<?php foreach ($ticket->ticket_has_attachments as $ticket_attachments):  ?>
			 				<a class="label label-info" href="<?=site_url()?>files/media/<?php echo $ticket_attachments->savename; ?>" download="<?php echo $ticket_attachments->filename; ?>"><?php echo $ticket_attachments->filename; ?></a>
			 				<?php endforeach;?>
					
					</div>
			</div>
					
					 

				<?php
			    $i = 0;
			    foreach ($ticket->ticket_has_articles as $value): 
			      $i = $i+1;
			  if($i == 1){ ?>
			  
			  <?php }
			  ?>	
			  <div class="article-content">
			 		<div class="article-header">
			 		<div class="article-title"><?=$value->subject;?> <?php /*if($value->internal == "0"){ ?><i class="fa fa-eye tt pull-right" title="<?=$this->lang->line('application_task_public');?>"> </i> <?php }*/ ?>
		</div>
			 			<span class="article-sub"><?php $from_explode = explode(' - ', $value->from); echo '<span class="tt" title="'.$from_explode[1].'">'.$from_explode[0].'</span>'; ?></span>  
				 		<span class="article-sub"><?php echo date($core_settings->date_format.' '.$core_settings->date_time_format, $value->datetime); ?></span>
						
				 		

			         <?php /* if($value->from == $this->user->firstname." ".$this->user->lastname || $this->user->admin == "1"){ ?>
			         <a href="<?=base_url()?>projects/deletemessage/<?=$ticket->project_id;?>/<?=$ticket->id;?>/<?=$value->id;?>" rel="" class="btn btn-mini pull-right btn-danger"><i class="icon-trash icon-white"></i></a>
			 		 <?php } */ ?>
			 		</div>
			 		<div class="article-body">
			 		<?php $text = preg_replace('#(^\w.+:\n)?(^>.*(\n|$))+#mi', "", $value->message); echo $text;?>

			 		<?php if(isset($value->article_has_attachments[0])){echo "<hr>"; } ?>
			 		<?php foreach ($value->article_has_attachments as $attachments):  ?>
			 				<a class="label label-success" href="<?=site_url()?>files/media/<?php echo $attachments->savename; ?>" download="<?php echo $attachments->filename; ?>"><?php echo $attachments->filename; ?></a>
			 		<?php endforeach;?>

			 		</div>
			 		</div>
			  <?php endforeach;?>

			 

	  </div>
	</div>
	</div>
</div>
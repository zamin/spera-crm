<div class="row">
        <div class="col-xs-12 col-md-3">

	 	<div class="table-head"><?=$this->lang->line('application_ticket_details');?></div>
		<div class="subcont">
			<ul class="details">
			<?php $lable = FALSE; if($ticket->status == "new"){ $lable = "label-important"; }elseif($ticket->status == "open"){$lable = "label-warning";}elseif($ticket->status == "closed"){$lable = "label-success";}elseif($ticket->status == "reopened"){$lable = "label-warning";} ?>
	
				<li><span><?=$this->lang->line('application_ticket_number');?></span> #<?=$ticket->reference;?></li>
				<li><span><?=$this->lang->line('application_status');?></span> <span class="label <?php echo $lable; ?>"><?=$this->lang->line('application_ticket_status_'.$ticket->status);?></span></li>
				<li><span><?=$this->lang->line('application_type');?></span> <?php if(isset($ticket->type->name)){ ?><?=$ticket->type->name;?> <?php } ?></li>
				<li><span><?=$this->lang->line('application_from');?></span> <?php if(isset($ticket->client->email)){ echo '<a class="tt" title="'.$ticket->client->email.'">'.$ticket->client->firstname.' '.$ticket->client->lastname.'</a>';
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
				<li><span><?=$this->lang->line('application_queue');?></span> <?php if(isset($ticket->queue->name)){ ?><?=$ticket->queue->name;?> <?php } ?></li>
				<li><span><?=$this->lang->line('application_created');?></span> <?php echo date($core_settings->date_format.'  '.$core_settings->date_time_format, $ticket->created); ?></li>
				<li><span><?=$this->lang->line('application_owner');?></span> <?php if(isset($ticket->user->firstname)){ ?><?=$ticket->user->firstname;?> <?=$ticket->user->lastname;?> <?php } else{ echo "-";} ?></li>
				
				</ul>

			
	 </div>
	 <br>


	 <div class="table-head"><?=$this->lang->line('application_client');?></div>
		<div class="subcont">
			<ul class="details">
				<?php if(isset($ticket->client->firstname)){ ?><li><span><?=$this->lang->line('application_name');?></span>  <?php echo $ticket->client->firstname.' '.$ticket->client->lastname; ?> </li><?php } ?>
				<li><span><?=$this->lang->line('application_company');?></span> <?php if(!isset($ticket->client->company->name)){ ?> <a href="#" class="label label-info"><?php echo $this->lang->line('application_no_client_assigned'); }else{ ?><a class="label label-info" href="<?=base_url()?>clients/view/<?=$ticket->client->company->id;?>"><?php echo $ticket->client->company->name;} ?></a></li>
				<?php if(isset($ticket->client->email)){ ?> <li><span><?=$this->lang->line('application_email');?></span> <?=$ticket->client->email;?></li><?php } ?>
				<?php if(isset($ticket->client->phone)){ ?> <li><span><?=$this->lang->line('application_phone');?></span> <?php echo $ticket->client->phone; ?></li><?php } ?>
				<?php if(isset($ticket->client->mobile)){ ?> <li><span><?=$this->lang->line('application_mobile');?></span> <?php echo $ticket->client->mobile; ?></li><?php } ?>
				</ul>
	 </div>
	</div>
	 <div class="col-xs-12 col-md-9">


	 			<a id="fadein" class="btn btn-success" style="margin-top: -2px;"><?=$this->lang->line('application_reply_back');?></a>
	 		 	<div class="btn-group nav-tabs hidden-xs">
				
	                <a class="btn btn-primary backlink" id="back" href="<?=base_url()?>ctickets"><?=$this->lang->line('application_back');?></a>
	                <a class="btn btn-primary" id="note" data-toggle="mainmodal" href="<?=base_url()?>ctickets/article/<?=$ticket->id;?>/add"><?=$this->lang->line('application_add_note');?></a>
	                
	        </div> 
	        <div class="btn-group pull-right visible-xs">
			  <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
			    <i class="fa fa-cog"></i> <span class="caret"></span>
			  </button>
			  <ul class="dropdown-menu" role="menu">
			    				<li><a class=" backlink" id="back" href="#"><?=$this->lang->line('application_back');?></a>
				                <li><a id="note" data-toggle="mainmodal" href="<?=base_url()?>ctickets/article/<?=$ticket->id;?>/add"><?=$this->lang->line('application_add_note');?></a></li>
				                
			  </ul>
			</div>

	        <div class="message-content-reply fadein no-padding">
					    <?php   
					    $attributes = array('class' => '', 'id' => 'replyform');
					    echo form_open('ctickets/article/'.$ticket->id.'/add', $attributes); 
					    ?>
					    <input id="ticket_id" type="hidden" name="ticket_id" value="<?php echo $ticket->id; ?>" />
					    <input type="hidden" name="to" value="<?php if($ticket->user_id != 0){echo addslashes($ticket->user->email);}?>">
					    <input type="hidden" name="notify" value="yes">
					    <input type="hidden" name="subject" value="<?=$ticket->subject;?>">
					    <textarea id="reply" name="message" class="summernote" placeholder="<?=$this->lang->line('application_quick_reply');?>"></textarea>
					    <div class="textarea-footer">
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
			 				<a class="label label-info" href="<?=base_url()?>ctickets/attachment/<?php echo $ticket_attachments->savename; ?>"><?php echo $ticket_attachments->filename; ?></a>
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
			  <?php if($value->internal == "0"){ ?>
			  <div class="article-content">
			 		<div class="article-header">
			 		<div class="article-title"><?=$value->subject;?> 
		</div>
			 			<span class="article-sub"><?php $from_explode = explode(' - ', $value->from); echo '<span class="tt" title="'.$from_explode[1].'">'.$from_explode[0].'</span>'; ?></span>  
				 		<span class="article-sub"><?php echo date($core_settings->date_format.' '.$core_settings->date_time_format, $value->datetime); ?></span>
						
				 	
			 		</div>
			 		<div class="article-body">
			 		<?php $text = preg_replace('#(^\w.+:\n)?(^>.*(\n|$))+#mi', "", $value->message); echo $text;?>

			 		<?php if(isset($value->article_has_attachments[0])){echo "<hr>"; } ?>
			 		<?php foreach ($value->article_has_attachments as $attachments):  ?>
			 				<a class="label label-success" href="<?=base_url()?>ctickets/articleattachment/<?php echo $attachments->savename; ?>"><?php echo $attachments->filename; ?></a>
			 		<?php endforeach;?>

			 		</div>
			 		</div>
			 		<?php } ?>
			  <?php endforeach;?>

			 

	  </div>
	</div>
	</div>
</div>
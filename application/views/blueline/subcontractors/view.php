<?php 
if(isset($user)) { 
?>
<div class="row">
	<div class="col-md-12">
		<h2><?=trim($user->firstname.' '.$user->lastname);?></h2> 
	</div>
</div>
<div class="row">
	<div class="col-md-12 marginbottom20">
		<div class="table-head">
			<?=$this->lang->line('application_user_details');?> 
			<?php if( $user->status == 'active' ){ ?>
			<span class="pull-right">
				<a href="<?=base_url()?>subcontractors/update/<?=$user->user_id;?>/view" class="btn btn-primary" data-toggle="mainmodal"><i class="icon-edit"></i> 
					<?=$this->lang->line('application_edit');?>
				</a>
			</span>
			<?php } ?>
		</div>
		<div class="subcont">
			<ul class="details col-md-12">
				<?php /* ?>
				<li>
					<span><?=$this->lang->line('application_user_profile_pic');?>:</span> 
					<?php 
					$profile_pic = base_url().'files/media/'.$user->userpic; 
					$profile_pic_path = FCPATH.'/files/media/'.$user->userpic; 
					?>
					<?php echo (!empty($user->userpic) && file_exists($profile_pic_path)) ? '<img src="'.$profile_pic.'" />' : '-'; ?>
				</li>
				<?php */ ?>
				<li>
					<span><?=$this->lang->line('application_firstname');?>:</span> 
					<?php echo (isset($user->firstname)) ? $user->firstname : "-"; ?>
				</li>
				<li>
					<span><?=$this->lang->line('application_lastname');?>:</span> 
					<?php echo (isset($user->lastname)) ? $user->lastname : "-"; ?>
				</li>
				<li>
					<span><?=$this->lang->line('application_email');?>:</span> 
					<?php echo (isset($user->email)) ? $user->email : "-"; ?>
				</li>
			</ul>
			<br clear="all">
		</div>
	</div>
</div>
<?php } ?>
		
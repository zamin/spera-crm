<?php 
$attributes = array('class' => '', 'id' => '_multipleuserlogin', 'autocomplete' => 'on');
echo form_open_multipart($form_action, $attributes); ?>
<div id="login" class="modal hide fade in”" tabindex="-1" role="dialog" aria-labelledby="login" aria-hidden="false" >
  <div class="modal-header">
        <div class="form-group">
		 <div class="row">

		  <div class="btn-group pull-right margin-right-3">

			  <ul class="dropdown-menu pull-right" role="menu">

				<?php foreach($resultset as $key=>$value): 
						if($value->roles == 'Freelancer'):
							$set_page = 'aodashboard'; 
						elseif($value->roles == 'Client'):
							$set_page = 'clientdashboard'; 
						elseif($value->roles == 'Sub-Contractor'):
							$set_page = 'scdashboard'; 
						endif;
						?>
						<li><a href="<?=base_url().$set_page."/".$value->id?>/" class="btn-option" data-toggle="mainmodal"><?=$value->id;?></a></li>
						<li><a href="<?=base_url().$set_page."/".$value->id?>/" class="btn-option" data-toggle="mainmodal"><?=$value->userpic;?></a></li>
						<li><a href="<?=base_url().$set_page."/".$value->id?>/" class="btn-option" data-toggle="mainmodal"><?=$value->name;?></a></li>
						<li><a href="<?=base_url().$set_page."/".$value->id?>/" class="btn-option" data-toggle="mainmodal">LOGIN</a></li>
						<br/>
				<?php endforeach; ?>
			  </ul>
		  </div>
		</div>  
		</div>
		 </div>
		 </div>

<?php echo form_close(); ?>
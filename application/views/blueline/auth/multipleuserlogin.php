<?php 
$attributes = array('class' => '', 'role'=> 'multipleuserlogin', 'id' => 'multipleuserlogin');
echo form_open($form_action, $attributes); ?>

  <div class="col-sm-13  col-md-12 main">  
  <?php echo $title; ?>
  	 <div class="row">

		  <div class="btn-group pull-center margin-right-3">

				<?php foreach($resultset as $key=>$value): 
						if($value->roles == 'Freelancer'):
							$set_page = 'aodashboard'; 
						elseif($value->roles == 'Client'):
							$set_page = 'cdashboard'; 
						elseif($value->roles == 'Sub-Contractor'):
							$set_page = 'scdashboard'; 
						endif;
						echo '<ul>';
						?>
						<li><a href="<?=base_url().$set_page."/index/".$value->company_id?>/" class="btn-option"><?=$value->id;?></a></li>
						<li><a href="<?=base_url().$set_page."/index/".$value->company_id?>/" class="btn-option"><?=$value->userpic;?></a></li>
						<li><a href="<?=base_url().$set_page."/index/".$value->company_id?>/" class="btn-option"><?=$value->name;?></a></li>
						<li><a href="<?=base_url().$set_page."/index/".$value->company_id?>/" class="btn-option">LOGIN</a></li>
						<br/>
				<?php echo '</ul>'; endforeach; ?>

		  </div>
		</div>  
	</div>

<?php echo form_close(); ?>
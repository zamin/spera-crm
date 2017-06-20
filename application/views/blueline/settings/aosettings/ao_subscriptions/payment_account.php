	
	<div class="col-sm-12  col-md-12 main">  
     <div class="row">
            <?php if(count($accounts)==0){?>
			<a href="<?=base_url()?>aosubscriptions/create_payment_account" class="btn btn-primary" data-toggle="mainmodal"> New Payment Account</a>
            <?php }?>
		</div>
		<div class="row">
		<div class="table-head"><?php echo 'Payment Accounts';?></div>
		<div class="table-div">
		<table class="data table" id="subscriptions" rel="<?=base_url()?>" cellspacing="0" cellpadding="0">
		<thead>
			<th class="hidden-xs" width="70px">Account number</th>
			<th class="hidden-xs">Profile id</th>
            <th class="hidden-xs">Account in-use</th>
		</thead>
		<?php foreach ($accounts as $value):?>

		<tr >
			<td><span class="label label-info"><?php echo $value['account_number'];?> </span></td>
			<td><span class="label label-info"><?php echo $value['profile_id'];?> </span></td>
            <td><span class="label label-info"><?php if($value['is_default']){echo 'In use';}else{echo 'Not in use';}?> </span></td>
		</tr>
		<?php endforeach;?>
	 	</table>
	 	</div>
	 	</div>
	</div>
 
<div class="row">

	<div class="col-md-12">
		<div class="table-head">Propay to Propay Transfer</div>
		<div class="subcont">
		    <br clear="all">
            <?php   
            $attributes = array('class' => '', 'id' => '_subscriptions');
            echo form_open($form_action, $attributes); 
            ?>
             <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>"  >

                <div class="form-group">
                    <label for="sender_account_number"><?=$this->lang->line('application_sender_account_number');?></label>
                    <input id="sender_account_number" type="text" name="sender_account_number" required class="required form-control" value=""/>
                </div> 
                <div class="form-group">
                    <label for="receiver_account_number"><?=$this->lang->line('application_receiver_account_number');?></label>
                    <input id="receiver_account_number" type="text" name="receiver_account_number" required class="required form-control" value=""/>
                </div> 
                <div class="form-group">
                    <label for="amount"><?=$this->lang->line('application_amount');?></label>
                    <input id="amount" type="text" name="amount" required class="required form-control" value=""/>
                </div> 
                <button name="send" class="btn btn-primary"><?=$this->lang->line('application_submit');?></button>
             <?php echo form_close(); ?>
		</div>
	</div>
</div>
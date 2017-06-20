<?php if( !empty($propay_data) ){?>
<?php   
$attributes = array('class' => '', 'id' => '_subscriptions_1');
echo form_open(base_url().'aosubscriptions/postcreate', $attributes); 
?>

<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
   
<div class="form-group">
<p id="recurring">
    <label for="package_id"><?=$this->lang->line('application_frequency');?></label>
    <select required class="required chosen-select" name="package_id" id="package_id" style="width:100%" onchange="funGettoken1()">
        <option value="">-</option>
        <?php foreach ($packages as $value){?>
        <option value="<?php echo $value->id;?>"><?php echo $value->name;?></option>
        <?php }?>
    </select>
</p>
</div> 

<div class="form-group">
    <label for="Amount"><?=$this->lang->line('application_currency');?></label>
    <input id="Amount" type="text" name="Amount" required class="required form-control" value=""/>
</div> 

<div class="form-group">
<p id="recurring">
    <label for="recurring">Cards</label>
    <select required class="required chosen-select" name="propay_data_id" id="propay_data_id" style="width:100%" >
        <option value="">-</option>
        <?php foreach ($propay_data as $value){?>
        <option value="<?php echo $value->id;?>"><?php echo $value->cc_number;?></option>
        <?php }?>
    </select>
</p>
</div> 

<div class="modal-footer">
    <button name="send" type="button" class="btn btn-primary send button-loader loadsave" onclick="funSubmitSubscription(this.form)"><?=$this->lang->line('application_save');?></button>
    <a class="btn" data-dismiss="modal"><?=$this->lang->line('application_close');?></a>
</div>

<?php echo form_close(); ?>
<?php }?>
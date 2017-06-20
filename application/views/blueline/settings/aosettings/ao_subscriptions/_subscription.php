<?php   
/*if( !empty($propay_data))
{
$attributes = array('class' => '', 'id' => '_subscriptions_1');
echo form_open(base_url().'aosubscriptions/postcreate', $attributes); 
?>

<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
   
<div class="form-group">
<p id="recurring">
    <label for="package_id1"><?=$this->lang->line('application_frequency');?></label>
    <select required class="required chosen-select" name="package_id" id="package_id1" style="width:100%" onchange="funGettoken1()">
        <option value="">-</option>
        <?php foreach ($packages as $value){?>
        <option value="<?php echo $value->id;?>"><?php echo $value->type;?></option>
        <?php }?>
    </select>
</p>
</div> 

<div class="form-group">
    <label for="Amount1"><?=$this->lang->line('application_currency');?></label>
    <input id="Amount1" type="text" name="Amount" required class="required form-control" readonly="readonly" value=""/>
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
    <button name="send" type="submit" class="btn btn-primary send loadsave" ><?=$this->lang->line('application_save');?></button>
    <a class="btn" data-dismiss="modal"><?=$this->lang->line('application_close');?></a>
</div>

<?php echo form_close(); }*/?>

<?php   
$attributes = array('class' => '', 'id' => '_subscriptions');
echo form_open($form_action, $attributes); 
?>
<?php if(isset($subscription)){ ?>
<input id="id" type="hidden" name="id" value="<?=$subscription->id;?>" />
<?php } ?>
<?php if(isset($view)){ ?>
<input id="view" type="hidden" name="view" value="true" />
<?php } ?>
<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
   
<div class="form-group">
<p id="recurring">
    <label for="recurring"><?=$this->lang->line('application_frequency');?></label>
    <select required class="required chosen-select" name="package_id" id="package_id" style="width:100%" onchange="funGettoken()">
        <option value="" selected>-</option>
        <?php foreach ($packages as $value){?>
        <option value="<?php echo $value->id;?>"><?php echo $value->name;?></option>
        <?php }?>
    </select>
</p>
</div> 
<div class="form-group">
    <label for="Amount"><?=$this->lang->line('application_currency');?></label>
    <input id="Amount" type="text" name="Amount" required class="required form-control" readonly="readonly" value=""/>
</div> 
<div class="form-group">
    <label for="cardnumber"><?=$this->lang->line('application_card_number');?></label>
    <input id="cardnumber" type="text" name="cardnumber" required class="required form-control" value=""  autocomplete="off" maxlength="16"/>
</div> 
<div class="form-group">
    <label for="PaymentTypeId">Card</label>
    <select required class="required chosen-select " name="PaymentTypeId" id="PaymentTypeId" style="width:100%">
        <option value="" selected>Select Card</option>
        <option value="Visa">Visa</option>
        <option value="MasterCard">MasterCard</option>
        <option value="AMEX">AMEX</option>
        <option value="Discover">Discover</option>
        <option value="DinersClub">DinersClub</option>
        <option value="JCB">JCB</option>
    </select>
</div>
<div class="form-group">
    <label for="ExpMonth">Expiry Month</label>
    <select id="ExpMonth" name="ExpMonth" required class="required chosen-select form-control" style="width:100%">
        <option value="" selected>Expiry Month</option>
        <option value="01">01-Jan</option>
        <option value="02" >02-Feb</option>
        <option value="03">03-Mar</option>
        <option value="04">04-Apr</option>
        <option value="05">05-May</option>
        <option value="06">06-June</option>
        <option value="07">07-July</option>
        <option value="08">08-Aug</option>
        <option value="09">09-Sep</option>
        <option value="10">10-Oct</option>
        <option value="11">11-Nov</option>
        <option value="12">12-Dec</option>
    </select>
</div> 
<div class="form-group">
    <label for="ExpYear">Expiry Year</label>
    <select id="ExpYear" name="ExpYear" required class="required chosen-select form-control" style="width:100%">
        <option value="" selected>Expiry Year</option>
        <?php for($y=0 ; $y<=19 ; $y++){?>
        <option value="<?php echo date('Y',strtotime('now +'.$y.' Year'));?>" ><?php echo date('Y',strtotime('now +'.$y.' Year'));?></option>
        <?php }?>
    </select>
</div> 
<div class="form-group">
    <label for="cvv">cvv *</label>
    <input type="password" name="cvv" class="required form-control" required id="cvv" data-parsley-type="number" data-parsley-length="[1, 4]" maxlength="4" autocomplete="off" />
</div> 
<input id="CID" name="CID" type="hidden" >
<input id="SettingsCipher" name="SettingsCipher" type="hidden" >
<input id="tempToken" name="tempToken" type="hidden">
<input id="payerID" name="payerID" type="hidden" >
<div class="modal-footer">
    <button name="send" type="button" class="btn btn-primary send button-loader ajaxsave" onclick="funSaveSubscription(this.form)"><?=$this->lang->line('application_save');?></button>
    <?php /*<input type="button" name="send" class="btn btn-primary" value="<?=$this->lang->line('application_save');?>"/>*/?>
    <a class="btn" data-dismiss="modal"><?=$this->lang->line('application_close');?></a>
</div>
<?php echo form_close(); ?>
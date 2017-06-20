 
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
                <input type="hidden" name="userID" id="userID" value="<?php echo $invoice->user_id;?>">
                <input type="hidden" name="InvoiceID" id="InvoiceID" value="<?php echo $invoice->id;?>">
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
                <div class="form-group">
                    <label for="amount"><?=$this->lang->line('application_amount');?></label>
                    <input id="amount" type="text" name="amount" required class="required form-control" value=""/>
                </div> 
                
                <input id="CID" name="CID" type="hidden" >
                <input id="SettingsCipher" name="SettingsCipher" type="hidden" >
                
                <button name="send" class="btn btn-primary button-loader ajaxsave" onclick="funSaveCreditcard(this.form)" type="button"><?=$this->lang->line('application_submit');?></button>
             <?php echo form_close(); ?>
		</div>
	</div>
</div>
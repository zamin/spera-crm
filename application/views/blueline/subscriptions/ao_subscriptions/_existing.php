<?php if( !empty($propay_data) ){?>
<?php   
$attributes = array('class' => 'existing-card', 'id' => '_subscriptions_1');
echo form_open(base_url().'aosubscriptions/postcreate', $attributes); 
?>

<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
   
<div class="form-group">
<p id="recurring">
    <label for="package_id"><?=$this->lang->line('application_package');?></label>
         <?php
         //echo $package_type;exit;
            $options = array();
            $options[''] = '-';
            foreach ($packages as $value):  
                $options['monthly-tier'.$value->id] = 'Monthly '.$value->name.' : $'.$value->amount;
            endforeach;
            foreach($packages as $value)
            {
                $yearly_amount=round(($value->amount)*12);
                $yd_amount=(($yearly_amount/100)*$value->discount);
                $y_main_amount=$yearly_amount-$yd_amount;
                $options['yearly-tier'.$value->id] = 'Annual '.$value->name.' : $'.$y_main_amount;
            }
            if($package_type=='monthly')
            {
                $c='monthly-tier'.$package_id;
            }
            else
            {
                $c='yearly-tier'.$package_id;
            }
            echo form_dropdown('package_id', $options, $c, 'style="width:100%" id="package_id" class="required chosen-select" required');
            //echo form_dropdown('package_id', $options, $c, 'style="width:100%" id="package_id" class="required chosen-select" required onchange="funGettoken1()"');
        ?>
</p>
</div> 

<?php /*
<div class="form-group">
    <label for="Amount"><?=$this->lang->line('application_currency');?></label>
    <?php 
    if($package_type=='monthly')
    {
        $y_main_amount=$get_package_data->amount;
    }
    else
    {
        $yearly_amount=round(($get_package_data->amount)*12);
        $yd_amount=(($yearly_amount/100)*$get_package_data->discount);
        $y_main_amount=$yearly_amount-$yd_amount;
    }
    ?>
    <input id="Amount" type="text" name="Amount" required class="required form-control" value="<?php echo $y_main_amount;?>"/>
</div> */ ?>

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
<?php

$language = $this->input->cookie('language');
if (!isset($language))
{
  $language = $core_settings->language;
}

   $status = $estimate->estimate_status;

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xml:lang="en" lang="en">
<head>
  <meta name="Author" content="<?= $core_settings->company?>"/> 
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style type="text/css">

    @font-face {
    font-family: "<?=$core_settings->pdf_font?>";
    src: url(<?php if( $core_settings->pdf_path == 1 ) {echo site_url(); } ?>assets/blueline/fonts/<?= $core_settings->pdf_font?>-Regular.ttf);
    font-weight: normal;
    }
    @font-face {
    font-family: "<?=$core_settings->pdf_font?>";
    src: url(<?php if( $core_settings->pdf_path == 1 ) {echo site_url(); } ?>assets/blueline/fonts/<?= $core_settings->pdf_font?>-Bold.ttf);
    font-weight: bold;
    }
body{
  color: #61686d;
  font: 12px "<?=$core_settings->pdf_font?>", Helvetica, Arial, Verdana, sans-serif;
  font-weight: normal;
  padding-bottom: 60px;
}
p{
  margin:0px;
  padding:0px;
}
.center{
  text-align: center !important;
}
.right{
  text-align: right !important;
}
.left{
  text-align: left !important;
}
.top-background{
  color:#000000;
  border-bottom:2px solid #11A7DB;
  width:100%; 
  margin:-44px -44px 0px;
  padding:40px 40px 5px;
}

.status {
  font-weight: normal;
  text-transform: uppercase;
  color: #FFF;
  font-size: 16px;
  margin-top: -5px;
  text-align: right;

}

.Accepted {color: #43AC6E; }
.Sent {color: #EAAA10; }
.Invoiced {color: #B361FF; }
.Declined {color: #FC704C; }

.company-logo {
  margin-bottom: 10px;
}

.company-address {
  line-height:11px;
}
.recipient-address {
  line-height:13px;
}
.invoicereference{
  font-size: 22px;
  font-weight: normal;
  margin:10px 0;
}

#table{
  width:100%;
  margin:20px 0px;
}

#table tr.header th{
  font-weight: bold;
  color:#777777;
  font-size: 10px;
  text-transform: uppercase;
  border-bottom:2px solid #DDDDDD;
  padding:0 5px 10px;
}
#table tr td{
  font-weight: lighter;
  color:#444444;
  font-size: 12px;
  border-bottom:1px solid #DDDDDD;
  padding:15px 5px;

}
#table tr td .item-name{
  font-weight: bold;
  color:#444444;
}
#table tr td .description{
  font-weight: normal;
  color:#888888;
  font-size: 10px;
}

.padding{
  padding: 5px 0px;
}
.total-amount {
  padding: 8px 20px 8px 0;
  color: #FFFFFF;
  font-size: 17px;
  font-weight: normal;
  margin: 0;
  text-align: right;
}

.custom-terms {
  padding:20px 2px;
  border-bottom:1px solid #DDDDDD;
  font-size: 12px;
}
.over{
  text-transform: uppercase;
  font-size: 10px;
  font-weight: bold;

}
.under{
  font-size: 16px;
}

.total-heading {
  background: #11A7DB;
  color: #FFFFFF;
  text-align: right;
  padding:10px;

}
.side{
  padding:10px;
  background: #EDF2F4;
}

.footer{
  padding:5px 1px;
  font-size: 9px;
  text-align:center;
}
<?php if(isset($htmlPreview)){ ?>
html{
   background: #3E4042;
}
body{
  padding:40px; width:750px;
  background:#FFFFFF;
  margin:50px auto;
  min-height:800px;
  box-shadow: 0px 0px 5px 0px #000;
}
.top-background {
    margin: -44px -40px 0px;
}
.notification-div{
  position:absolute;
  background:##ED5564;
  margin:0 auto;
  top:10px;
  color:#FFFFFF;
  font-size: 14px;
  font-weight: bold;
  padding:10px;
}
<?php  } ?>
    </style>

</head>

<body>

<div class="top-background">
   <table width="100%" cellspacing="0" >
         <tr>
           <td><img src="<?php echo site_url().$invoice_logo; ?>" class="company-logo" /></td>
           <td style="vertical-align: top;"><div class="status <?php echo $status;?>"> <?=$this->lang->line('application_'.$status);?></div></td>
         </tr>
         <tr>
			<?php /* ?>
            <td style="vertical-align:top"><?=$core_settings->company;?></td>
			<?php */ ?>
            <td class="left" style="vertical-align:top"><?=$estimate->company->name;?></td>
        </tr> 
		<?php /* ?>
        <tr>
            <td style="vertical-align:top"><?=$core_settings->invoice_contact;?></td>
            <td class="left" style="vertical-align:top"><strong><?php if(isset($estimate->company->client->firstname)){ ?> <?=$estimate->company->client->firstname;?> <?=$estimate->company->client->lastname;?></strong><?php } ?></td>
        </tr>
		<?php */ ?>
        <tr>
			<?php /* ?>
            <td style="vertical-align:top"><?=$core_settings->invoice_address;?></td>
			<?php */ ?>
            <td class="left" style="vertical-align:top"><?=$company_detail->address;?></td>
        </tr>
        <tr>
			<?php /* ?>
            <td style="vertical-align:top"><?=$core_settings->invoice_city;?></td>
			<?php */ ?>
            <td class="left" style="vertical-align:top"><?=trim($company_detail->city.', '.$company_detail->zipcode,',');?></td>
        </tr>
        <tr>
			<?php /* ?>
            <td style="vertical-align:top"></td>
			<?php */ ?>
            <td class="left" style="vertical-align:top"><?php if($company_detail->state != ""){?><?=$company_detail->state;?><?php } ?></td>
        </tr>
        <tr>
			<?php /* ?>
            <td style="vertical-align:top"></td>
			<?php */ ?>
            <td class="left" style="vertical-align:top"><?php if($company_detail->vat != ""){?><?=$this->lang->line('application_vat');?>: <?php echo $company_detail->vat; ?><?php } ?></td>
        </tr>
        <tr>
          <td class="padding" style="vertical-align:top">
          <span class="invoicereference"><?=$this->lang->line('application_estimate');?> <?=$core_settings->estimate_prefix;?><?=$estimate->estimate_reference;?><?=$project;?></span><br/>
          <span class="over"><?php $unix = human_to_unix($estimate->issue_date.' 00:00'); echo date($core_settings->date_format, $unix);?></span>
          </td>
          <td class="padding" align="right" style="vertical-align:bottom">
          <?=$this->lang->line('application_due_date');?> <?php echo date($core_settings->date_format, human_to_unix($estimate->due_date.' 00:00:00'));?>
          </td>
        </tr>
  </table>
 
</div>
<div class="content">
  <table id="table" cellspacing="0"> 
  <thead> 
  <tr class="header"> 
    <th class="left"><?=$this->lang->line('application_item');?></th>
    <th width="9%" class="center"><?=$this->lang->line('application_hrs_qty');?></th>
    <th width="15%" class="right"><?=$this->lang->line('application_unit_price');?></th>
    <th width="15%" class="right"><?=$this->lang->line('application_sub_total');?></th>
  </tr> 
  </thead> 
  <tbody> 
  <?php $i = 0; $sum = 0; $row=false; ?>
    <?php foreach ($items as $value):
    $description = preg_replace( "/\r|\n/", "<br>", $estimate->invoice_has_items[$i]->description);
    $description = str_replace("&lt;br&gt;", "<br>", $description);
    ?>
    <tr <?php if($row){?>class="even"<?php } ?>>
      <td>
        <span class="item-name"><?php if(!empty($value->name)){echo $value->name;}else{ echo $estimate->invoice_has_items[$i]->item->name; }?></span><br/>
        <span class="description"><?=$description;?><span class="item-name">
      </td>
      <td class="center"><?=$estimate->invoice_has_items[$i]->amount;?></td>
      <td class="right"><?php echo display_money(sprintf("%01.2f",$estimate->invoice_has_items[$i]->value));?></td>
      <td class="right"><?php echo display_money(sprintf("%01.2f",$estimate->invoice_has_items[$i]->amount*$estimate->invoice_has_items[$i]->value));?></td>
    </tr>
    <?php $sum = $sum+$estimate->invoice_has_items[$i]->amount*$estimate->invoice_has_items[$i]->value; $i++; if($row){$row=false;}else{$row=true;}?>
    
    <?php endforeach;
    if(empty($items)){ echo "<tr><td colspan='4'>".$this->lang->line('application_no_items_yet')."</td></tr>";}
    if(substr($estimate->discount, -1) == "%"){ $discount = sprintf("%01.2f", round(($sum/100)*substr($estimate->discount, 0, -1), 2)); }
    else{$discount = $estimate->discount;}
    $sum = $sum-$discount;
    $presum = $sum;

    if($estimate->tax != ""){
      $tax_value = $estimate->tax;
    }else{
      $tax_value = $core_settings->tax;
    }

     if($estimate->second_tax != ""){
      $second_tax_value = $estimate->second_tax;
    }else{
      $second_tax_value = $core_settings->second_tax;
    }

    $tax = sprintf("%01.2f", round(($sum/100)*$tax_value, 2));
    $second_tax = sprintf("%01.2f", round(($sum/100)*$second_tax_value, 2));

    $sum = sprintf("%01.2f", round($sum+$tax+$second_tax, 2));
    ?>
    
  </tbody> 
  </table> 
</div>
<div>

        <table width="100%">
          
        <tr>
          <?php if ($estimate->discount != 0): ?><td class="side"><span class="over"><?=$this->lang->line('application_discount');?></span><br/><span class="under">- <?=display_money($discount, $estimate->currency);?></span></td><?php endif ?>
          <td class="side"><span class="over"><?=$this->lang->line('application_sub_total');?></span><br/><span class="under"><?=display_money($presum, $estimate->currency);?></span></td>
          <?php if($tax_value != "0"){ ?><td class="side"><span class="over"><?=$this->lang->line('application_tax');?> (<?= $tax_value?>%)</span><br/><span class="under"><?=display_money($tax, $estimate->currency)?></span></td><?php } ?>
          <?php if($second_tax_value != "0" && $second_tax_value != ""){ ?><td class="side"><span class="over"><?=$this->lang->line('application_second_tax');?> (<?= $second_tax_value?>%)</span><br/><span class="under"><?=display_money($second_tax, $estimate->currency)?></span></td><?php } ?>
          <td class="total-heading"><span class="over"><?=$this->lang->line('application_total');?></span><br/><span class="under"><?=display_money($sum, $estimate->currency);?></span></td>
        </tr> 

        </table>



    <div class="custom-terms"><?php echo $estimate->terms; ?></div>
    <div class="footer"><b><?=$estimate->company->name;?></b> | <?=$company_detail->email;?><?php if($company_detail->phone != ""){echo " | ".$company_detail->phone;};?><?php if($company_detail->vat != ""){echo " | ".$this->lang->line('application_vat').": ".$company_detail->vat;}?>   </div>
    <script type='text/php'>
        if ( isset($pdf) ) { 
          $font = Font_Metrics::get_font('helvetica', 'normal');
          $size = 9;
          $y = $pdf->get_height() - 24;
          $x = $pdf->get_width() - 15 - Font_Metrics::get_text_width('1/1', $font, $size);
          $pdf->page_text($x, $y, '{PAGE_NUM}/{PAGE_COUNT}', $font, $size);
        } 
      </script>

</div>

</body>
</html>
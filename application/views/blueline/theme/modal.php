<script type="text/javascript" src="<?=site_url()?>assets/blueline/js/ajax.js?ver=<?=$core_settings->version;?>"></script>
<script>
$(document).ready(function()
{ 
/* Form Validator */
    var activeform = $("form").validator();
/* Load 2.5.0 Form styling */
    fancyforms();
/* Reload flatpickr plugin for modal and pass through the current validation form opject */
    flatdatepicker(activeform);
/* Button loaded on click */
    buttonLoader();
/* Custom Upload Button */
    uploaderButtons(".modal");
/* Checkbox Plugin - Labelauty */
   $(".modal .checkbox").labelauty(); 
/* Item-Selector */
   itemSelector();
/* Color Selector */
   colorSelector();
/* Row delete fucntion */
   deleteRow();
/* Custom Input Mask */
   customInputMask();
});
$.ajaxSetup ({
    cache: false
});

var i = 0;
 function funSaveSubscription(obj)
{
    var oldhtml = $('.button-loader').html();
    var oldstr = '<i class="fa fa-spinner fa-spin"></i> ';
    //$('.button-loader').html( oldstr+oldhtml );
    var p_id = $('#package_id').val();
    var package_id=p_id.slice(-1); 
    var package_type=p_id.split("-")[0];
    //var Amount = $('#Amount').val();
    var cardnumber = $('#cardnumber').val();
    var PaymentTypeId = $('#PaymentTypeId').val();
    var ExpMonth = $('#ExpMonth').val();
    var ExpYear = $('#ExpYear').val();
    var cvv = $('#cvv').val();
    var promo_code = $('#promo_code').val();
    var has_promo_code = promo_code !== "";
    
    var error = 0;
    if( package_id.length==0 )
    {
        $('#package_id').parent().addClass("has-error");
        error = 1;
    }
//    if( Amount.length==0 )
//    {
//        $('#Amount').parent().addClass("has-error");
//        error = 1;
//    }
    if( cardnumber.length==0 )
    {
        $('#cardnumber').parent().addClass("has-error");
        error = 1;
    }
    if( PaymentTypeId.length==0 )
    {
        $('#PaymentTypeId').parent().addClass("has-error");
        error = 1;
    }
    if( ExpMonth.length==0 )
    {
        $('#ExpMonth').parent().addClass("has-error");
        error = 1;
    }
    if( ExpYear.length==0 )
    {
        $('#ExpYear').parent().addClass("has-error");
        error = 1;
    }
    if( cvv.length==0 )
    {
        $('#cvv').parent().addClass("has-error");
        error = 1;
    }
    
    if(error == 1)
    {
        var oldsend = $('.button-loader').html();
        var oldsend1 = oldsend.replace('<i class="fa fa-spinner fa-spin"></i> ', '');
        setTimeout(function(){$('.button-loader').html(oldsend1);},500);
        return false;
    }
    $('#'+obj.id+ ' input').attr('readonly', 'readonly');
    $.ajax({
        type: "POST",
        url: "<?php echo base_url();?>aosubscriptions/getsettoken/",
        data: "subscription="+package_id+"&<?php echo $this->security->get_csrf_token_name(); ?>=<?php echo $this->security->get_csrf_hash(); ?>&package_type="+package_type+(has_promo_code ? "&promo_code="+promo_code : ""),
        success: function(data){
            //i = 1;
            var responseData = JSON.parse(data);
            if(responseData.payerID)
            {
                if( responseData.payerID ) { $('#payerID').val(responseData.payerID); }
                if( responseData.tempToken ) { $('#tempToken').val(responseData.tempToken); }
                if( responseData.settingsCipher ) { $('#SettingsCipher').val(responseData.settingsCipher); }
                if( responseData.credentiaID ) { $('#CID').val(responseData.credentiaID); }
                if( responseData.amount ) { $('#Amount').val(responseData.amount);}
                i = 1;
                $(obj).submit();
            }
            else
            {
                window.location.reload(true);
            }
        },
        complete: function (data) {
          //$(".chosen-select").chosen({scroll_to_highlighted: false, disable_search_threshold: 4, width: "100%"});
        }
    });
}


 function funSubmitSubscription(obj)
{
    var oldhtml = $('.button-loader').html();
    var oldstr = '<i class="fa fa-spinner fa-spin"></i> ';
    //$('.button-loader').html( oldstr+oldhtml );
    var p_id = $('#package_id').val();
    var package_id=p_id.slice(-1); 
    var package_type=p_id.split("-")[0];
    //var package_id = $('#package_id').val();
    //var Amount = $('#Amount').val();
    var propay_data_id = $('#propay_data_id').val();
    
    var error = 0;
    if( package_id.length==0 )
    {
        $('#package_id').parent().addClass("has-error");
        error = 1;
    }
    
//    if( Amount.length==0 )
//    {
//        $('#Amount').parent().addClass("has-error");
//        error = 1;
//    }
    if( propay_data_id.length==0 )
    {
        $('#propay_data_id').parent().addClass("has-error");
        error = 1;
    }
    if(error == 1)
    {
        var oldsend = $('.button-loader').html();
        var oldsend1 = oldsend.replace('<i class="fa fa-spinner fa-spin"></i> ', '');
        setTimeout(function(){$('.button-loader').html(oldsend1);},500);
        return false;
    }
    else
    {
        $('.'+obj.class+ ' input').attr('readonly', 'readonly');
        $(obj).submit();
    }
}

function funGettoken()
{
    var p_id = $('#package_id').val();
    var package_id=p_id.slice(-1); 
    var package_type=p_id.split("-")[0];
    $.ajax({
        type: "POST",
        url: "<?php echo base_url();?>aosubscriptions/getPackageAmount/",
        data: "subscription="+package_id+"&<?php echo $this->security->get_csrf_token_name(); ?>=<?php echo $this->security->get_csrf_hash(); ?>&package_type="+package_type,
        success: function(data){
            var responseData = JSON.parse(data);
            if( responseData.amount ) { $('#Amount').val(responseData.amount);}
        },
        complete: function (data) {
          //$(".chosen-select").chosen({scroll_to_highlighted: false, disable_search_threshold: 4, width: "100%"});
        }
    });
}
function funGettoken1()
{
    var p_id = $('#package_id').val();
    var package_id=p_id.slice(-1); 
    var package_type=p_id.split("-")[0];
    $.ajax({
        type: "POST",
        url: "<?php echo base_url();?>aosubscriptions/getPackageAmount/",
        data: "subscription="+package_id+"&<?php echo $this->security->get_csrf_token_name(); ?>=<?php echo $this->security->get_csrf_hash(); ?>&package_type="+package_type,
        success: function(data){
            var responseData = JSON.parse(data);
            if( responseData.amount ) { $('#Amount').val(responseData.amount);}
        },
        complete: function (data) {
          //$(".chosen-select").chosen({scroll_to_highlighted: false, disable_search_threshold: 4, width: "100%"});
        }
    });
}
</script>
 <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
          <h4 class="modal-title"><?=$title;?></h4>
        </div>
        <div class="modal-body">
          <?=$yield?>          
        </div>
    </div>




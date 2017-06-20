<?php $attributes = array('class' => '', 'role'=> 'form', 'id' => 'create', 'Content-Type: application/json'); ?>
<?=form_open($form_action, $attributes); ?>

<div class="row">
<div class="col-md-12 main"> 
<div class="table-head "><?=$this->lang->line('application_create_invoice');?></div>
        <div class="table-div table-padding-top">
			<div class="form-group">
				<table class="data table" id="aoinvoices" rel="<?=base_url()?>" cellspacing="0" cellpadding="0">
				<thead>
				<label for="project"><?=$this->lang->line('application_select_project');?> *</label>
				<?php 
				
				$options = array();
						$options['0'] = 'Please Select Project';
						foreach ($projects as $value):  
							$options[$value['pid']] = $value['pname'];
						endforeach;
				/** open this comment for editing */
				$c = "";
				//if(isset($invoice)){$client = $invoice->company_id; $project = $invoice->project_id;}else{$client = ""; $project = "";}
				echo form_dropdown('project_type', $options, $c, 'style="width:100%" name="project_type" id="project_type" class="chosen-select"');

				?>
				</thead>
				</table>
			</div>
			


			<table class="data table" id="addinvoices" rel="<?=base_url()?>" cellspacing="0" cellpadding="0">
			<thead>

				<th class="hidden-xs"><?=$this->lang->line('application_tasks');?></th>
				<th class="hidden-xs"><?=$this->lang->line('application_terms');?></th>
				<th class="hidden-xs"><?=$this->lang->line('application_hours');?></th>
				<th class="hidden-xs"><?=$this->lang->line('application_Minutes');?></th>
				<th class="hidden-xs"><?=$this->lang->line('application_rate');?></th>

			</thead>
			<tr id="addtask">

				<td>
					<div class="form-group">
						<div name='showtasktype' id='showtasktype'>
							<select name="task_type" style="width:100%" name="task_type" id="task_type" class="chosen-select">
								<option value="0">Please Select Task</option>
							</select>
						</div>
					</div>
				</td>
				<td><span><textarea id="terms" name="terms" class="textarea required form-control" style="height:100px"></textarea></span></td>
				<td class="hidden-xs"><span><input name="hours" id="hours" placeholder="0" type="text" value=""/></span></td>
				<td class="hidden-xs"><span><input name="minute" placeholder="0" id="minute" type="text" value=""/></span> <span class="hidden"></span></td>
				<td class="hidden-xs"><input name="rate" placeholder="0.00" id="rate" type="text" value=""/></td>
				
			</tr>

		</table>
		<button type="button" name="add" class="btn btn-primary"><?=$this->lang->line('application_add_invoices');?></button>
		
		<div class="table-head"><?=$this->lang->line('application_list_invoices');?></div>
		<table class="data table" id="listinvoices" rel="<?=base_url()?>" cellspacing="0" cellpadding="0">
			<thead>
			
				<th  width="70px" class="hidden-xs">#</th>
				<th class="hidden-xs"><?=$this->lang->line('application_tasks');?></th>
				<th class="hidden-xs"><?=$this->lang->line('application_terms');?></th>
				<th class="hidden-xs"><?=$this->lang->line('application_hours');?></th>
				<th class="hidden-xs"><?=$this->lang->line('application_Minutes');?></th>
				<th class="hidden-xs"><?=$this->lang->line('application_rate');?></th>
				<th class="hidden-xs"><?=$this->lang->line('application_total');?></th>
				<th class="hidden-xs"><?=$this->lang->line('application_action');?></th>

			</thead>
			</table>
			<div class="data table" id="listinvoices" rel="<?=base_url()?>">
			<!--tr-->
			 <div id="listtask">
			 </div>
				<!--td>
					<div class="form-group">
						<div name='showtasktype' id='showtasktype'>
							<select name="task_type" style="width:100%" name="task_type" id="task_type" class="chosen-select">
								<option value="0">Please Select Task</option>
							</select>
						</div>
					</div>
				</td>
				<td><span><textarea id="terms" name="terms" class="textarea required form-control" style="height:100px"></textarea></span></td>
				<td class="hidden-xs"><span><input class="form-control" name="hours" id="hours" placeholder="0" type="text" value=""/></span></td>
				<td class="hidden-xs"><span><input class="form-control" name="minute" placeholder="0" id="minute" type="text" value=""/></span> <span class="hidden"></span></td>
				<td class="hidden-xs"><input class="form-control" name="rate" placeholder="0.00" id="rate" type="text" value=""/></td-->
			<!--/tr-->
				<!--tr>
				<td><span><?//=$this->lang->line('application_sub_total');?>:</span></td></tr>
				<tr>
				<td><span><?//=$this->lang->line('application_discount');?>: <input class="form-control" name="discount" id="discount" placeholder="0" type="text" value=""/></span></td></tr>
				<tr><td><span><?//=$this->lang->line('application_tax');?>: <input class="form-control" name="tax" id="tax" placeholder="0" type="text" value=""/></span></td></tr>
				<tr><td><span><?//=$this->lang->line('application_due_date');?>: <input class="form-control" name="due_date" id="due_date" placeholder="0" type="text" value=""/></span></td>
				</tr>	
				<tr>
				<td><span><?//=$this->lang->line('application_balance_due');?>:</span></td></tr-->				
			<div class="col-xs-12 col-sm-12 col-md-3 col-md-offset-9 subtotal_wrapp">
				<p class="text-right m-t-5"><b><?=$this->lang->line('application_sub_total');?></b><span class="subtotal"></span></p>
				<p class="text-right m-t-5 clearfix"><span class="col-xs-6"><?=$this->lang->line('application_discount');?>: </span> <input type="text" class="col-xs-6 light_blue_brdbtm_around autonumber" data-v-max="100" data-v-min="0.00" id="discount" placeholder="%0.00" data-parsley-id="24" maxlength="3"></p>
				<p class="text-right m-t-5 clearfix"><span class="col-xs-6"><?=$this->lang->line('application_tax');?>:</span> <input type="text" class="col-xs-6 light_blue_brdbtm_around autonumber" data-v-max="100" data-v-min="0.00" id="tax" placeholder="%0.00" data-parsley-id="26" maxlength="3"></p>
				<p class="text-right m-t-5 m-b-15 clearfix"><span class="col-xs-6"><?=$this->lang->line('application_due_date');?>: </span> <input type="text" class="col-xs-6 light_blue_brdbtm_around dateFormat" required="" id="dueDate" data-parsley-id="28"></p>
				<p class="text-right"><b class="m-l-15 fonts_1-2em balDue"><?=$this->lang->line('application_balance_due');?> </b><span class="balDue" ></div></p>
		</div>

		<input type="submit" class="btn btn-success" value="<?=$this->lang->line('application_send');?>" />


		</div>

		
		<!--div class="row m-t-40">
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="col-xs-12 col-sm-4 col-md-3 m-b-10">
                                    <a class="btn delete_btn btn-block  waves-effect waves-light pull-right btn-rounded text-white">
                                        <i class="fa fa-close"></i>&nbsp;&nbsp;<span>Delete Invoice</span></a>
                                </div> -->
                                <!--div class="col-xs-12 col-sm-4 col-md-2 col-md-offset-5 m-b-10">
                                    <a data-toggle="modal" data-target="#invoicePreview" class="btn btn-block dark_blue_btn waves-effect waves-light pull-right btn-rounded text-white   btn btn-primary">
                                        <i class="fa fa-eye"></i>&nbsp;&nbsp;<span>Preview Invoice</span></a>
                                </div-->
                                	

								
								<!--div class="col-xs-12 col-sm-4 col-md-2 m-b-10" id="send-invoice-btn-new">
                                <button type="button" id="send_invoice_new" class="btn btn-block dark_blue_btn waves-effect waves-light pull-right btn-rounded text-white   btn btn-primary" disabled="disabled">
                                    <i class="fa fa-check"></i>&nbsp;&nbsp;<span>Send Invoice</span>
                                </button>
                                </div>
                                                            </div>
                        </div-->
			
		</div>
	</div> 

 



<?=form_close()?>
<script>
$(function(){
    $.ajaxSetup({
            headers: {
                'X_CSRF-TOKEN' : $('meta[name="_token"]').attr('content')
            }
        });
});
$("#project_type").change(function(e)
{
	var project_id = {"project_id" : $('#project_type').val()};
	//alert(project_id);
	//console.log(project_id);
    $.ajax({
		type: "GET",
		url:"<?php echo base_url();?>aoinvoices/get_tasks/",
		data:{ project_id  : project_id },
        success:function(data){
			console.log('DATA -> '+data);
			
			 if(data.length != 0) {

				$("#showtasktype").html(data);
			 } else {
				 return false;
			 }
        },
		complete: function (data) {
		  $(".chosen-select").chosen({scroll_to_highlighted: false, disable_search_threshold: 4, width: "100%"});
		}
    });


});

$("button").click(function(){

	//console.log('hiiii');
	var project_id = {"project_id" : $('#project_type').val()};
	var taskid = {"taskid" : $('#task_type').val()};
	var terms = {"terms" : $('#terms').val()};
	var hours = {"hours" : $('#hours').val()};
	var minute = {"minute" : $('#minute').val()};
	var rate = {"rate" : $('#rate').val()};
	var subtotal = $('span.subtotal').text();
	var balDue = $('span.balDue').text();
	console.log(subtotal);
	console.log(balDue);
	 $.ajax({
        type: 'GET',
        url: '<?php echo base_url();?>aoinvoices/showtask/',
		datatype: 'json',
		data:{ project_id  : project_id, taskid : taskid, terms : terms, hours : hours, minute : minute, rate : rate, subtotal : subtotal, balDue : balDue },
        success: function(data) {
			//debugger;
			console.log(data);

			var value = $.parseJSON(data);

			console.log(value.response);
            $("#listtask").append(value.response);
			$("span.subtotal").replaceWith('<span class="subtotal">'+value.subtotal+'</span>');
			$("span.balDue").replaceWith('<span class="balDue">'+value.balDue+'</span>');

        }
    });
});
function manage_price(){
	var oldValue = $('span.subtotal').text();
	var discount = $('#discount').val();
	var balDue = $('span.balDue').text();
	
	var result = (parseFloat(oldValue)) - (parseFloat(parseFloat(oldValue) * parseFloat(discount) / 100));
	
	$("span.balDue").html(result);
	
}

$( "#discount" ).change(function( event ) {
	manage_price();
});

function manage_tax(){
	var oldValue = $('span.subtotal').text();
	var tax = $('#tax').val();
	var balDue = $('span.balDue').text();
	
	var result = (parseFloat(oldValue)) + (parseFloat(parseFloat(oldValue) * parseFloat(discount) / 100));
	
	$("span.balDue").html(result);
	
}


$( "#tax" ).keyup(	function( event ) {
	manage_tax();
	
});

</script>

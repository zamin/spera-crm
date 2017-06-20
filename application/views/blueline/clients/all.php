<div class="col-sm-12  col-md-12 main"> 
	<div class="row">
            <input type="hidden" id="owner_id" value="<?php echo $owner_id;?>">
            <input type="hidden" id="company_id" value="<?php echo $company_id;?>">
		<a id="new_client_create" href="<?=base_url()?>clients/create" class="btn btn-primary" data-toggle="mainmodal_new">
			<?=$this->lang->line('application_add_new_client');?>
		</a>
             
	</div>
   
	<div class="ajax-loader"></div>
	<div class="row">
		<div class="table-head table-select-box"> 
                    <span id="label_text"><?=$this->lang->line('application_clients');?></span>
                        <?php
                            $options = array(); 
                            $options[''] = 'Select Company...';
                            if(empty($client_companies))
                            {
                                $options[$name_of_company]=$name_of_company;
                            }
                            else
                            {
                                
                                $options[$name_of_company]=$name_of_company;
                                foreach($client_companies as $client_company)
                                {
                                    $options[$client_company['sub_company']]=$client_company['sub_company'];
                                }
                            }
                            echo form_dropdown('sub_c_id1', $options,'', ' class="required form-control chosen-select" id="sub_c_id_all"');?>
                </div>
                <input type='hidden' value="<?php echo $name_of_company ; ?>" id='b_c_name' >
		<div class="table-div">
			
                        <table class="data table" id="clients" rel="<?=base_url()?>" cellspacing="0" cellpadding="0">
				<thead>
					<th class="hidden-xs" style="width:70px"><?=$this->lang->line('application_user_id');?></th>
					<?php /* ?><th><?=$this->lang->line('application_user_profile_pic');?></th><?php */ ?>
					<th class="hidden-xs"><?=$this->lang->line('application_firstname');?></th>
					<th class="hidden-xs"><?=$this->lang->line('application_lastname');?></th>
					<th class="hidden-xs"><?=$this->lang->line('application_email');?></th>
					<th class="hidden-xs"><?=$this->lang->line('application_status');?></th>
                                        <th><?=$this->lang->line('application_company');?></th>
					<th><?=$this->lang->line('application_action');?></th>
				</thead>
				<?php $user_cnt = 0; ?>
				<?php if( !empty( $clients ) ){ 
                                      echo "<input type='hidden' id='total_clients' value='".count($clients)."'>";
                                    ?>
					<?php foreach ($clients as $value): $user_cnt++;?>

					<tr  id="<?=$value->user_id;?>" >
						<td class="hidden-xs" style="width:70px"><?=$core_settings->company_prefix;?><?php  echo $user_cnt; ?></td>
						<?php /* ?>
						<?php $profile_pic = base_url().'files/media/'.$value->userpic; 
							  $profile_pic_path = FCPATH.'/files/media/'.$value->userpic;
						?>
						<td>
							<?php if( !empty($value->userpic) && file_exists($profile_pic_path) ) { ?>
									<a href="<?=base_url().'clients/view/'.$value->id?>"><img src="<?php echo $profile_pic; ?>" /></a>
							<?php } else { ?>
									<?php echo "no-image"; ?>
							<?php } ?>
						</td>
						<?php */ ?>
						<td class="hidden-xs"><a href="<?=base_url().'clients/view/'.$value->user_id?>"><?php echo $value->firstname; ?></a></td>
						<td class="hidden-xs"><?php echo $value->lastname; ?></td>
						<td class="hidden-xs"><a href="<?=base_url().'clients/view/'.$value->user_id?>"><?php echo $value->email; ?></a></td>
						<td class="hidden-xs"><?php echo $value->status; ?></td>
                                               <?php 
                                                $get_client_assign_company=$this->db->query('select cc.* from client_assign_companies ca join client_companies cc on ca.client_id=cc.client_id where ca.client_id="'.$value->user_id.'"')->row_array();
                                                if(empty($get_client_assign_company))
                                                {
                                                    
                                                ?>
                                                <td><?php echo $name_of_company; ?></td>
                                                <?php }else{
                                                ?>
                                                <td class="option option-left"><a href="<?=base_url()?>clients/edit_company/<?=$value->user_id;?>/<?=$get_client_assign_company['id'];?>"  data-toggle="mainmodal"><?php echo $get_client_assign_company['sub_company']; ?></a></td>
                                                <?php } ?>
						<td class="option" width="8%">
							<button type="button" class="btn-option delete po" data-toggle="popover" data-placement="left" data-content="<a class='btn btn-danger po-delete ajax-silent' href='<?=base_url()?>clients/delete/<?=$value->user_id;?>'><?=$this->lang->line('application_yes_im_sure');?></a> <button class='btn po-close'><?=$this->lang->line('application_no');?></button> <input type='hidden' name='td-id' class='id' value='<?=$value->user_id;?>'>" data-original-title="<b><?=$this->lang->line('application_really_delete');?></b>"><i class="fa fa-times"></i>
							</button>
							<?php if( $value->status == active ){ ?>
							<a href="<?=base_url()?>clients/update/<?=$value->user_id;?>" class="btn-option" data-toggle="mainmodal"><i class="fa fa-cog"></i></a>
							
                                                        <?php } else { ?>
                            <a href="javascript:void(0);" data-id="<?=$value->user_id;?>" data-role="3" class="btn-option resend-invitaion-email" ><i class="fa fa-envelope"></i></a>
                            <?php } ?>
						</td>
                                               
						
					</tr>
					<?php endforeach;?>
				<?php } ?>
			</table>
			<br clear="all">
		
		</div>
	</div>
</div>
<script>
$("#sub_c_id_all").find("option").eq(0).removeAttr('selected');
$(document).ready(function(){
    //$('#clients_previous a').html('<i class="fa fa-arrow-left"></i>');
    $('#sub_c_id_all').change(function(){
        var sub_c_id_all= $(this).val();
        var owner_id= $('#owner_id').val();
        var b_c_name = $('#b_c_name').val();
        $.ajax({
            type:'GET',
            cache:false,
            url:"<?php echo base_url();?>clients/search_client_companies",
            data:{'sub_c_id_all':sub_c_id_all,'owner_id':owner_id},
            success:function(response)
            {
                //alert(response);
                var data_result=$.parseJSON(response);
                var total_clients=$('#total_clients').val();
                if(data_result.count != 0){
                    $('table.data').dataTable().fnDestroy();
                    $('table#clients tbody').html(data_result.result);
                    $('table.data').dataTable({
                        "oLanguage": {
                            "sSearch": "",
                        "oPaginate": {
                        "sFirst": "First page", // This is the link to the first page
                        "sPrevious": "<i class='fa fa-arrow-left'></i>", // This is the link to the previous page
                        "sNext": "<i class='fa fa-arrow-right'></i>", // This is the link to the next page
                        "sLast": "Last page", // This is the link to the last page,
                        }
                        }
                        } );
                    $('#clients_length').addClass('test');
                    modalfunc();
                    $('.po').popover({html:true});
                    $(document).on("click", '.po-close', function (e) {
                        $('.po').popover('hide');
                    });
                    $(document).on("click", '.po-delete', function (e) {
                        $(this).closest('tr').velocity("transition.slideRightOut");
                    });
                }
                else
                {
                   if(b_c_name==sub_c_id_all)
                   {
                        $.ajax({
                            type:'GET',
                            cache:false,
                            url:"<?php echo base_url();?>clients/search_companies",
                            data:{'owner_id':owner_id},
                            success:function(response2)
                            {
                                //alert(response2);
                                var data_result2=$.parseJSON(response2);
                                if(data_result2.count != 0){
                                    $('table.data').dataTable().fnDestroy();
                                    $('table#clients tbody').html(data_result2.result);
                                    $('table.data').dataTable({
                                        "oLanguage": {
                                            "sSearch": "",
                                        "oPaginate": {
                                        "sFirst": "First page", // This is the link to the first page
                                        "sPrevious": "<i class='fa fa-arrow-left'></i>", // This is the link to the previous page
                                        "sNext": "<i class='fa fa-arrow-right'></i>", // This is the link to the next page
                                        "sLast": "Last page", // This is the link to the last page,
                                        }
                                        }
                                        } );
                                    $('#clients_length').addClass('test');
                                    modalfunc();
                                    $('.po').popover({html:true});
                                    $(document).on("click", '.po-close', function (e) {
                                        $('.po').popover('hide');
                                    });
                                    $(document).on("click", '.po-delete', function (e) {
                                        $(this).closest('tr').velocity("transition.slideRightOut");
                                    });
                                }
                                else
                                {
                                    $('table#clients tbody').html(data_result.result);
                                    $('#clients_info').hide();
                                    $('#clients_paginate').hide();
                                }
                            },
                            error:function()
                            {
                                alert('ajax in error');
                            }
                        });
                   }
                   else
                   {
                        $('table#clients tbody').html(data_result.result);
                        $('#clients_info').hide();
                        $('#clients_paginate').hide();
                   }
                }
            },
            error:function()
            {
                alert('ajax in error');
            }
        });
    });
});
</script>

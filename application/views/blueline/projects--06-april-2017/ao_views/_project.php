<?php 
$attributes = array('class' => '', 'id' => '_project','enctype' => "multipart/form-data");
echo form_open($form_action, $attributes);
if(isset($project)){ ?>
<input id="id" type="hidden" name="id" value="<?php echo $project->id; ?>" />
<?php } ?>

<!-- <div class="form-group">
        <label for="reference"><?=$this->lang->line('application_reference_id');?> *</label> -->
        
       <?php if(!empty($core_settings->project_prefix)){ ?>
       <!-- <div class="input-group"> <div class="input-group-addon"> --><?=$core_settings->project_prefix;?><!-- </div> --> <?php } ?>
        <input type="hidden" name="reference" class="form-control" id="reference" value="<?php if(isset($project)){echo $project->reference;} else{ echo $core_settings->project_reference;} ?>" required/>
        <?php if(!empty($core_settings->project_prefix)){ ?><!-- </div> --><?php } ?>
<!-- </div> -->
<!--<div class="form-group">
    <label for="company">Companies</label><br>
    <?php $options = array();
            $options['0'] = '-';
            foreach ($companies as $value):  
            $options[$value->id] = $value->name;
            endforeach;
    if(isset($project) && isset($project->company->id)){$client = $project->company->id;}else{$client = "";}
    echo form_dropdown('company_id', $options, $client, 'style="width:100%" class="chosen-select"');?>
        
</div>-->
<div class="form-group">
        <label for="client">Users</label><br>
        <select name="project_assign_clients[]" multiple class="chosen-select" >
        <option value="" disabled >Select Users</option>
        <?php
        foreach ($clients as $key=>$value) 
        {
            echo "<optgroup label='".$key."'>";
            foreach($value as $k=>$v)
            {
                if (!empty($project_assign_users)) 
                {
                    if(in_array($v['id'],$project_assign_users))
                    {
                        echo "<option value='" . $v['id'] . "' selected='selected'>" . $v['firstname'] . ' ' . $v['lastname'] . "</option>";
                    }
                    else
                    {
                        echo "<option value='" . $v['id'] . "'>" . $v['firstname'] . ' ' . $v['lastname'] . "</option>";
                    }
                }
                else
                {
                    echo "<option value='" . $v['id'] . "'>" . $v['firstname'] . ' ' . $v['lastname'] . "</option>";
                }
            }
            echo "</optgroup>";
        }
        ?>
        </select>
</div>
<div class="form-group">
                        <label for="progress"><?=$this->lang->line('application_progress');?> <span id="progress-amount"><?php if(isset($project)){echo $project->progress;}else{echo "0";} ?></span> %</label>
                          <div class="slider-group">
                             <div id="slider-range"></div>
                          </div>
                          <input type="hidden" class="hidden" id="progress" name="progress" value="<?php if(isset($project)){echo $project->progress;}else{echo "0";} ?>">
</div>
<div class="checkbox checkbox-attached">
                           <label>
                            <input name="progress_calc" value="1" type="checkbox" <?php if(isset($project) && $project->progress_calc == "1"){ ?> checked="checked" <?php } ?>/>
                            <span class="lbl"> <?=$this->lang->line('application_calculate_progress');?> </span>
                          </label>
                          <script>
                          $(document).ready(function(){ 
                              //slider config
                                $( "#slider-range" ).slider({
                                  range: "min",
                                  min: 0,
                                  max: 100,
                                  <?php if(isset($project) && $project->progress_calc == "1"){ ?>disabled: true,<?php } ?>
                                  value: <?php if(isset($project)){echo $project->progress;}else{echo "0";} ?>,
                                  slide: function( event, ui ) {
                                    $( "#progress-amount" ).html( ui.value );
                                    $( "#progress" ).val( ui.value );
                                  }
                                });
                            });
                          </script>
</div>


<div class="form-group">
                          <label for="name"><?=$this->lang->line('application_name');?> *</label>
                          <input type="text" name="name" class="form-control" id="name"  value="<?php if(isset($project)){echo $project->name;} ?>" required/>
</div>

<div class="form-group">
                          <label for="start"><?=$this->lang->line('application_start_date');?> *</label>
                          <input class="form-control datepicker" name="start" id="start" type="text" value="<?php if(isset($project)){echo $project->start;} ?>" required/>
</div>
<div class="form-group">
                          <label for="end"><?=$this->lang->line('application_deadline');?> *</label>
                          <input class="form-control datepicker-linked" name="end" id="end" type="text" value="<?php if(isset($project)){echo $project->end;} ?>" required/>
</div>

<!-- <div class="form-group">
                          <label for="category"><?=$this->lang->line('application_category');?></label>
                          <input type="text" list="Projectcategorylist" autocomplete="off" name="category" class="form-control typeahead" id="category"  value="<?php if(isset($project)){echo $project->category;} ?>"/>
                          <datalist id="Projectcategorylist">
                          <?php if(isset($category_list)){ foreach ($category_list as $value):  ?>
                                  <option value="<?=$value->category?>">
                          <?php endforeach; } ?>
                          </datalist>
</div> -->

<div class="form-group">
                          <label for="phases"><?=$this->lang->line('application_phases');?> *</label>
                          <input type="text" name="phases" class="form-control" id="phases"  value="<?php if(isset($project)){echo $project->phases;}else{echo "Planning, Developing, Testing";} ?>" required/>
</div>

 <div class="form-group">
                        <label for="textfield"><?=$this->lang->line('application_description');?></label>
                        <textarea class="input-block-level form-control"  id="textfield" name="description"><?php if(isset($project)){echo $project->description;} ?></textarea>
</div>

        <div class="modal-footer">
        <input type="submit" name="send" id="project_submit" class="btn btn-primary" value="<?=$this->lang->line('application_save');?>"/>
        <a class="btn btn-default" data-dismiss="modal"><?=$this->lang->line('application_close');?></a>
        </div>

<?php echo form_close(); ?>
<script type="text/javascript">
  
  $(document).ready(function(){
      $('#project_submit').click(function(){
        var start=$('#start').val();
        var end =$('#end').val();
        if(end=='')
        {
          $('#end').parent().addClass('error1');
        }
        if(start =="")
        {
          $('#start').parent().addClass('error1');
        }
      });
  });
</script>
<?php
$attributes = array('class' => 'dynamic-form', 'data-reload' => 'media', 'id' => '_media');
echo form_open_multipart($form_action, $attributes);
?>
<?php if (isset($media)) { ?>
    <input id="id" type="hidden" name="id" value="<?php echo $media->id; ?>" />
<?php } ?>
<input id="date" type="hidden" name="date" value="<?php echo $datetime; ?>" />
<input id="project_id" type="hidden" value="<?php echo $project_id; ?>" />
<div class="form-group">
    <label for="name"><?= $this->lang->line('application_name'); ?> *</label>
    <input id="name" type="text" name="name" class="required form-control" value="<?php if (isset($media)) {
    echo $media->name;
} ?>"  required/>
</div>
<div class="form-group">
    <label for="description"><?= $this->lang->line('application_description'); ?> *</label>
    <input id="description" type="text" name="description" class="form-control" value="<?php if (isset($media)) {
    echo $media->description;
} ?>"  required/>
</div>
<div class="form-group">
    <label for="phase"><?= $this->lang->line('application_phase'); ?></label>
    <?php
    $options = explode(',', $project->phases);
    $options2 = array();
    foreach ($options as $value):
        $options2[$value] = $value;
    endforeach;
    $phase = FALSE;
    if (isset($media)) {
        $phase = $media->phase;
    }
    echo form_dropdown('phase', $options2, $phase, 'style="width:100%" class="chosen-select"');
    ?>
</div> 
<?php if (!isset($media)) { ?>

    <div class="form-group">
        <label for="userfile"><?= $this->lang->line('application_file'); ?></label><div>
            <input id="uploadFile" class="form-control uploadFile" placeholder="Choose File" disabled="disabled" />
            <div class="fileUpload btn btn-primary">
                <span><i class="fa fa-upload"></i><span class="hidden-xs"> <?= $this->lang->line('application_select'); ?></span></span>
                <input id="uploadBtn" type="file" name="userfile" class="upload" required="required" />
            </div>
        </div>
    </div>

<?php } ?>  

<div class="modal-footer">
    <button type="submit" id="send" name="send" class="btn btn-primary send"><?= $this->lang->line('application_save'); ?></button>
    <a class="btn btn-default" data-dismiss="modal"><?= $this->lang->line('application_close'); ?></a>
</div>

<?php echo form_close(); ?>
<script>
    $(document).ready(function () {
        $('#_media #uploadBtn').on('change', function () {
            var select_file = $('#uploadBtn').val();
            if (select_file != '')
            {
                $('#uploadFile').parent().parent().removeClass('error1');
                $('#_media #send').addClass('button-loader');
            } else
            {
                $('#uploadFile').parent().parent().addClass('error1');
                $('#_media #send').removeClass('button-loader');
            }
        });
        $('#_media #send').on('click', function () {
            var upbtn = $('#uploadBtn').val();
            if (upbtn == '')
            {
                $('#uploadFile').parent().parent().addClass('error1');
            } else
            {
                $('#uploadFile').parent().parent().removeClass('error1');
                var project_id = $('#project_id').val();
                $.ajax({
                    type: 'GET',
                    cache: false,
                    url: "<?php echo base_url(); ?>scprojects/check_media",
                    data: {'project_id': project_id},
                    success: function (response)
                    {
                        if (response != '')
                        {
                            if (response == 1)
                            {
                                $('#media_paginate ul li:first').after('<li class="paginate_button active"><a href="#" aria-controls="media" data-dt-idx="1" tabindex="0">1</a></li>');
                            } else
                            {
                                return true;
                            }
                        }
                    },
                    error: function ()
                    {
                        alert("error in ajax request");
                    }
                });
            }
        });
    });
</script>
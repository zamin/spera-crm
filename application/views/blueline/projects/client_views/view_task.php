<link href="<?=site_url()?>assets/blueline/css/plugins/video-js.css" rel="stylesheet">
 <script type="text/javascript" src="<?=site_url()?>assets/blueline/js/plugins/pdfobject.js"></script>

          
       <div class="row">
       <div class="col-xs-12 col-sm-6">
	 	<div class="table-head">Task Details</div>
		<div class="subcont">
			<ul class="details">
				<li><span><?=$this->lang->line('application_name');?>:</span> <?=$task->name;?></li>
				<li><span>Description:</span> <?=$task->description;?></li>

				<!-- <?php 
				    if(!empty($task_attachments))
				    {
				        foreach ($task_attachments as $k => $v) {
				           $path=FCPATH.'files/tasks_attachment/'.$v['task_attach_file'];
				           $attchment_url= site_url().'files/tasks_attachment/'.$v['task_attach_file'];
				           if(file_exists($path))
				           {
				              //echo "<a href='".$attchment_url."' download='".$v['milestone_attach_file']."'>Download</a><br/>";
				              echo "<li><span>".$this->lang->line('application_download').":</span><a href='".$attchment_url."' download='".$v['task_attach_file']."' class='btn btn-xs btn-success'><i class='icon-download icon-white'>".$this->lang->line('application_download')."</i></a>&nbsp;&nbsp;&nbsp;<a href='".base_url().'cprojects/delete_tasks_attachement/'.$v['project_id'].'/delete/'.$v['task_id'].'/'.$v['id']."' class='btn btn-xs btn-error' >Delete</a><br/></li>";
				           }
				        }
				    }
				?> -->
			</ul>
			<br clear="both">
			<form class="dynamic-form" method='POST' action="<?php echo base_url();?>cprojects/tasks/<?php echo $task->project_id;?>/view/<?php echo $task->id;?>" enctype ="multipart/form-data" >
    	 <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>"  >

    	 	<div class="form-group">
		    	<label for="milestone_attach_file">File</label>
		    	<div>
		        	<input id="uploadFile" class="form-control uploadFile" placeholder="Choose File" disabled="disabled">
			        <div class="fileUpload btn btn-primary">
			            <span><i class="fa fa-upload"></i><span class="hidden-xs"> Select...</span></span>
			            <input id="uploadBtn" type="file" name="task_attach_file[]" class="upload">
			        </div>
		    	</div>
			</div>
			<button name="send" class="btn btn-primary"><?=$this->lang->line('application_upload');?></button>
    	 </form>
    	 </div>
    	 <br>
    	 <a class="btn btn-primary" href="<?=base_url()?><?=$backlink;?>"><i class="fa fa-arrow-left"></i> <?=$this->lang->line('application_back_to_tasks');?></a>
    	 </div>
     
		<div class="col-xs-12 col-sm-6">
	 	<div class="table-head">Tasks Attachment List</div>
		
		<div class="subcont">
		<table class="data table" id="ao_t_attachements" rel="<?=base_url()?>" cellspacing="0" cellpadding="0" > 
		   <thead>
              <tr>
                  <th>Image</th>
                  <th>Image Name</th>
                  <th>Uploaded By</th>
                  <th><?=$this->lang->line('application_action');?></th>
              </tr>
            </thead>
            <tbody>
            	<?php 
				    if(!empty($task_attachments))
				    {
				        foreach ($task_attachments as $k => $v) {
				        	
				           	$path=FCPATH.'files/tasks_attachment/'.$v['_attach_file'];
				           	$attchment_url= site_url().'files/tasks_attachment/'.$v['task_attach_file'];
				            if(file_exists($path))
				            {
				            	$ext = pathinfo($v['task_attach_file'], PATHINFO_EXTENSION);
				            	//var_dump($ext);
				            	if($ext=='jpg' || $ext=='jpeg' || $ext=='png' || $ext=='gif' || $ext =='bmp' ||$ext=='JPG' || $ext=='JPEG' || $ext=='PNG' || $ext=='GIF' || $ext =='BMP' || $ext =='SVG' || $ext =='svg')
				            	{

				            		$icon_image=site_url().'files/media/image.png';

				            	}
				            	elseif($ext=='doc' || $ext=='DOC' || $ext=='dbf' || $ext=='DBF' || $ext=='DIF' || $ext=='dif' || $ext=='EPS' || $ext=='eps'|| $ext=='DOCX' || $ext=='docx')
				            	{
				            		$icon_image=site_url().'files/media/document.png';
				            	}
				            	elseif($ext=='pdf')
				            	{
				            		$icon_image=site_url().'files/media/pdf.png';
				            	}
				            	elseif($ext=='xls' || $ext=='xlsb' || $ext=='xlsm' || $ext=='xlsx' || $ext=='csv' || $ext=='CSV')
				            	{
				            		$icon_image=site_url().'files/media/excel.png';
				            	}
				            	elseif($ext=='mkv' || $ext=='avi' || $ext=='wmv' || $ext=='mp4' || $ext=='vob' || $ext=='mpeg' || $ext=='mpg' || $ext=='3gp')
				            	{
				            		$icon_image=site_url().'files/media/video.png';
				            	}
				            	else
				            	{
				            	   $icon_image=$attchment_url;
				            	}

				            	$get_user_details=User::find($v['user_id']);
				            	echo "<tr>";
				            	echo "<td><img src=".$icon_image."  width='50' height='50' ></td>";
				            	echo "<td><b>".$v['task_attach_file']."</b></td>";
				            	echo "<td><b>by ".$get_user_details->firstname." ".$get_user_details->lastname."</b></td>";
				            	echo "<td><a href='".$attchment_url."' download='".$v['task_attach_file']."' class='btn btn-xs btn-success'>".$this->lang->line('application_download')."</a>&nbsp;&nbsp;&nbsp;<a href='".base_url().'cprojects/delete_tasks_attachement/'.$v['project_id'].'/delete/'.$v['task_id'].'/'.$v['id']."' class='btn btn-xs btn-error' >Delete</a></td>";
				              echo "</tr>";
				            }
				           
				        }
				    }
				    else
				    {
				    	"<tr>No Data yet</tr>";
				    }
				?>
            </tbody>
		</table>
			
    	 </div>
    	 </div>
			 </div>




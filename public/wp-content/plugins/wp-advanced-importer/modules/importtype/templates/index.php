<?php
/* Module : Post
   Author : Fredrick
   Owner  : smackcoders.com
   Date	  : Feb11,2014
 */ 
$impCE = new WPAdvImporter_includes_helper(); 
global $wpdb;
?>
        <div style="width:158%;">
	<div id="accordion">
	<h3 style='padding-top:7px;padding-left:10px;'> Debug mode </h3>
	<div class="debug"><div class="squarecheck">
		
	        <input type="checkbox" class="" name="debug_mode" id="debug_mode" onclick = "debugoption()"><label for = "debug_mode"></label></div><label id="optiontext" style="margin-left:28px;margin-top:-32px">You can enable/disable the debug mode</label>

        </div>
	<table class="table-importer">
	<tr>
	<td>
	<div class="steplist">
	<h3 style="width:15%"><label <?php if(isset($_REQUEST['step']) &&$_REQUEST['step'] =='uploadfile') {?> class= "selected" <?php } ?> style="height:29px"><p style="margin-left:17px;margin-top:4px">Import XML File</p></label></h3><h3 style="width:15%;margin-left:141px;margin-top:-40px"> <label <?php if(isset($_REQUEST['step']) &&$_REQUEST['step'] =='user_mapping') {?> class= "selectuser" <?php } ?>><p style="margin-left:24px;margin-top:4px">User Mapping</p></label></h3><h3 style="width:15%;margin-left:282px;margin-top:-40px;"><label <?php if(isset($_REQUEST['step']) &&$_REQUEST['step'] =='content_mapping') {?> class= "contentselect" <?php } ?>><p style="margin-left:18px;margin-top:4px">Content Mapping</p></label></h3><h3 style="width:14%;margin-left:434px;margin-top:-40px;"><label <?php if(isset($_REQUEST['step']) &&$_REQUEST['step'] =='media_handle') {?> class= "contentselect" <?php } ?>><p style="margin-left:15px;margin-top:4px">Media Handling</p></label></h3><h3 style="width:16%;margin-left:575px;margin-top:-40px;"><label <?php if(isset($_REQUEST['step']) &&$_REQUEST['step'] =='import_option') {?> class= "contentselect" <?php } ?>><p style="margin-left:23px;margin-top:4px;-webkit-margin-start: 12px;">Import Option</p></label></h3><h3 style="width:34%;;float:right;margin-top:-40px;height:30px;"></h3>
	</div>
	<div id='sec-one' <?php if(isset($_REQUEST['step']) && $_REQUEST['step'] != 'uploadfile') {?> style='display:none' <?php } ?>>
	<?php if(is_dir($impCE->getUploadDirectory('default'))){ ?>
		<input type='hidden' id='is_uploadfound' name='is_uploadfound' value='found' />
	<?php } else { ?>
		<input type='hidden' id='is_uploadfound' name='is_uploadfound' value='notfound' />
        <div class="warning" id="warning" name="warning" style="display:none;margin: 4% 0 4% 10%;"></div>
	<?php } ?>
	<form action='<?php echo admin_url().'admin.php?page='.WP_CONST_ADVANCED_XML_IMP_SLUG.'/index.php&__module='.$_REQUEST['__module'].'&step=user_mapping'?>' id='browsefile' method='post' name='browsefile' onsubmit="return import_csv();" />
	<div class="importfile" align='center'>
	<div id='filenamedisplay'></div><!--<form class="add:the-list: validate" style="clear:both;" method="post" enctype="multipart/form-data" onsubmit="return file_exist();">-->
	<div class="container">
	<!-- The fileinput-button span is used to style the file input field as button -->
	<span class="btn btn-success fileinput-button">
	<span>Browse</span>
	<!-- The file input field used as target for the file upload widget -->
	<input type ='hidden' id="pluginurl"value="<?php echo  WP_CONTENT_URL ;?>">
	<?php $uploadDir = wp_upload_dir(); ?>
	<input type="hidden" id="uploaddir" value="<?php echo isset($uploadDir['basedir']) ? $uploadDir['basedir'] : ''; ?>">
	<input type="hidden" id="uploadFileName" name="uploadfilename" value="">
	<input type="hidden" id="module" name="module" value="xml_import" />
       	<input type='hidden' id = 'uploadedfilename' name = 'uploadedfilename' value = ''>
        <input type="hidden" id="currentlimit" name="currentlimit" value="0"/>
        <input type="hidden" id="authcnt" name="authcnt" value="1"/>
        <input type='hidden' id = 'total' name = 'total' value = ''/>
        <input type='hidden' id = 'tmpcnt' name = 'tmpcnt' value = '1'/>
        <input type = 'hidden' id = 'postcount' name = 'postcount' value = ''/>
        <input type = 'hidden' id = 'implimit' name = 'implimit' value = '0'/>
        <input type = 'hidden' id = 'authorcount' name = 'authorcount' value = ''/>
        <input type = 'hidden' id = 'upload_csv_realname' name = 'upload_csv_realname' value =''>
        <input type='hidden' id="siteurl" name="siteurl" value='<?php echo get_site_url(); ?>'>
        <input type = 'hidden' id = 'current_file_version' name = 'current_file_version' value = ''>
        <input type = 'hidden' id = 'current_module' name = 'current_module' value = '<?php echo isset($_REQUEST['__module']) ? $_REQUEST['__module'] : ''; ?>' >
        <input type ='hidden' id = 'changetype' name = 'changetype' value =''>
	<input id="fileupload" type="file" name="files[]" multiple>
	</span>
	<!-- The global progress bar -->
	<div id="progress" class="progress">
	<div class="progress-bar progress-bar-success"></div>
	</div>
        
	<div class = "form-group" style="margin-top: 30px;float:right;margin-right:40px;">
	<input type = 'button' name='clearform' id='clearform' value='Clear' onclick="Reload();" class = 'btn btn-warning' /> 
           <input type = 'submit' name='importfile' id='importfile' value='Next>>'  class = 'btn btn-primary'  disabled/> 
 
	</div>
	<div class="warning" id="warning" name="warning" style="display:none"></div>
	<!-- The container for the uploaded files -->
	<div id="files" class="files"></div>
	<div class='modal fade' id = 'modal_zip' tabindex='-1' role='dialog' aria-labelledby='mymodallabel' aria-hidden='true'>
	<div class='modal-dialog'>
	<div class='modal-content'>
	<div class='modal-header'>
	<button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>
	<h4 class='modal-title' id='mymodallabel'> Xml File Info </h4>
	</div>
	<div class='modal-body' id = 'choose_file'>
	...
	</div>
	<div class='modal-footer'>
	<!--<button type='button' class='btn btn-default' data-dismiss='modal'>close</button>  -->
	<button type='button' class='btn btn-primary' data-dismiss='modal'>Close</button>
	</div>
	</div>
	</div>
	</div>
	</div>
	<script language="javascript" type="text/javascript">
	var check_upload_dir = document.getElementById('is_uploadfound').value;  
	if(check_upload_dir == 'notfound'){
		$('#defaultpanel').css('visibility','hidden');
		$('<p/>').text("").appendTo('#warning');
		$( "#warning" ).empty();
		$('#warning').css('display','inline');
		$('<p/>').text("Warning:   Sorry. There is no uploads directory Please create it with write permission.").appendTo('#warning');
		$('#warning').css('color','red');
		$('#warning').css('font-weight','bold');
		$('#progress .progress-bar').css('visibility','hidden');
	}
	else{
	$(function () {
		'use strict';
		var uploadPath = document.getElementById('uploaddir').value;
                 var url = (document.getElementById('pluginurl').value+'/plugins/<?php echo WP_CONST_ADVANCED_XML_IMP_SLUG;?>/lib/jquery-plugins/uploader.php')+'?uploadPath='+uploadPath+'&curr_action=<?php echo isset($_REQUEST['__module']) ? $_REQUEST['__module']  : ''; ?>';
		$('#fileupload').fileupload({
		url: url, 
		dataType: 'json',
		done: function (e, data) {
		$.each(data.result.files, function (index, file) {
		document.getElementById('uploadFileName').value=file.name;
                var filewithmodule = file.uploadedname.split(".");
                var check_file = filewithmodule[filewithmodule.length - 1];

               if(check_file != "xml") {  showMapMessages('error', 'Kindly Upload the XML/WXR File.'); }
               else {                    
                       var real_xml_name = file.name;
                       var doaction  = new Array({xml_name:real_xml_name }); 
                                                                jQuery.ajax({
                                                                type: 'POST',
                                                                url: ajaxurl,
                                                                data: {
                                                                'action': 'process_xml_file',
                                                                'postdata': doaction,
                                                                  },
                                                                 success: function (data) {
                                                                //alert(data);
                                                                 data = JSON.parse(data);
								document.getElementById('choose_file').innerHTML = '<div><label id="optiontext"> Total no.of available authors <p class="circlelayout">'+ data['author']+ '</p> </label> <br> <label id="optiontext"> Total no.of available posts <p class="circlelayout">' +data['post'] + '</p></label><br/><label id="optiontext"> Total no.of available page <p class="circlelayout">' +data['page'] + '</p></label><br/> <label id="optiontext"> Total no.of available custom posts <p class="circlelayout">' +data['custom'] + '</p></label><br/> </div> ';
                                                                 document.getElementById('total').value = data['total'];
                                                                 document.getElementById('postcount').value = data['postcount'];
                                                                 document.getElementById('authorcount').value = data['authorcount'];
                                                                jQuery('#modal_zip').modal('show');
                                                                  },
                                                                 error: function (errorThrown) {
                                                                 console.log(errorThrown);
                                                                  }
                                                                  });
                       }
                file.uploadedname = filewithmodule[0]+"-<?php echo isset($_REQUEST['__module']) ? $_REQUEST['__module'] : ''; ?>"+".xml";
                document.getElementById('upload_csv_realname').value = file.uploadedname; 
                var get_version1 = file.name.split("-<?php echo isset($_REQUEST['__module']) ? $_REQUEST['__module'] : ''; ?>"); 
                var get_version2 = get_version1[1].split(".xml");
                var get_version3 = get_version2[0].split("-");
                document.getElementById('current_file_version').value = get_version3[1];
		$('#uploadedfilename').val(file.uploadedname);
		$( "#filenamedisplay" ).empty();
		if(file.size>1024 && file.size<(1024*1024))
		{
			var fileSize =(file.size/1024).toFixed(2)+' kb';
		}
		else if(file.size>(1024*1024))
		{
			var fileSize =(file.size/(1024*1024)).toFixed(2)+' mb';
		}
		else
		{
			var fileSize= (file.size)+' byte';
		}
		$('<p/>').text((file.name)+' - '+fileSize).appendTo('#filenamedisplay');
		$('#importfile').attr('disabled', false);
		});
		},progressall: function (e, data) {
		var progress = parseInt(data.loaded / data.total * 100, 10);
		$('#progress .progress-bar').css('width', progress + '%' );
		}
		}).prop('disabled', !$.support.fileInput)
		.parent().addClass($.support.fileInput ? undefined : 'disabled');
		});
	}
	</script>
	<input type = 'hidden' name = 'importid' id = 'importid' >
		</form>
	</div>
	</div>
	</td>
	</tr>
        <tr>
        <td>
<!--         <h3> User Mapping </h3>    User-->
         <form name='mappingConfig' action="<?php echo admin_url(); ?>admin.php?page=<?php echo WP_CONST_ADVANCED_XML_IMP_SLUG;?>/index.php&__module=<?php echo $_REQUEST['__module']?>&step=content_mapping"  method="post"  onsubmit="return user_mapping();"  />
        <div class='msg' id = 'showMsg' style = 'display:none;'></div>
          <?php  if(isset($_REQUEST['step']) && $_REQUEST['step'] == 'user_mapping')  {   ?>
        <div id='sec-two' <?php if($_REQUEST['step']!= 'user_mapping'){ ?> style='display:none;' <?php } ?> >
           <?php $_SESSION['xml_values'] = $_POST;
                 $impCE = new WPAdvImporter_includes_helper();
                 echo $impCE->get_user_map_option();
             ?>
       </div>
            <input type = "submit"  id = "user_map" value = "Next >>" class = "btn btn-primary" onclick="user_mapping();" style="margin-top:-50px">
        <?php } ?>
      </form>
        </td>
        </tr>
        <tr>
        <td>
<!--         <h3> Content Mapping </h3>-->
         <form name='mappingConfig' action="<?php echo admin_url(); ?>admin.php?page=<?php echo WP_CONST_ADVANCED_XML_IMP_SLUG;?>/index.php&__module=<?php echo $_REQUEST['__module']?>&step=media_handle"  method="post"  >
	<input type="hidden" id="module" name="module" value="content_mapping" />
        <div class='msg' id = 'showMsg' style = 'display:none;'></div>
          <?php  if(isset($_REQUEST['step']) && $_REQUEST['step'] == 'content_mapping')  {     $impCE = new WPAdvImporter_includes_helper(); 
                   $res =  $impCE->save_user($_POST);
                 ?>
        <div id='sec-three' <?php if($_REQUEST['step']!= 'content_mapping'){ ?> style='display:none;' <?php } ?> >
        <div style="margin-left:15px;margin-top:15px;">
             <div>
			<label class="textalign" style="margin-bottom:12px">Content Mapping :</label>
                        <div class="squarecheck"><input type ="checkbox" name = "all" id = "all" value = "all"   onclick ="content_mapping(this.id);"><label for = "all"></label></div><label id="optiontext" style="margin-left:28px;margin-top:-32px"> Check All</label>
                        <div class="squarecheck"><input type ="checkbox" name = "contentmap" id = "posts" value = "post"   onclick ="content_mapping(this.id);"><label for = "posts"></label></div><label id="optiontext" style="margin-left:28px;margin-top:-32px">Post</label>
              <div class="squarecheck"><input type ="checkbox" name = "contentmap" id = "pages" value = "page" onclick ="content_mapping(this.id);" /><label for="pages"></label></div><label id="optiontext" style="margin-left:28px;margin-top:-32px">Page</label>
              <div class="squarecheck"><input type ="checkbox" name = "contentmap" id = "customposts" value = "customposts" onclick ="content_mapping(this.id);" /><label for="customposts"></label></div><label id="optiontext" style="margin-left:28px;margin-top:-32px">Custom Posts</label>
             </div>
             <div id = "view_mapping" >
                     <label class="textalign"> Edit Mapping</label> <select class="optiontext"  id = 'edit_mapping' onchange= "view_mapping(this.value);" style="margin-left:10px;"/> 
                       <option value = 'Default' /> -- Default-- </option>
                      </select>
            </div>
            <div id = 'view'>  </div> 
            <input type = 'submit' name = 'cont_map' id = 'cont_map'  value = "Next >>" class = "btn btn-primary " style="float:right;margin-right:50px;margin-top:-50px;"/>
       </div>
       </div>
        <?php } ?>
      </form>
        </td>
        </tr>
        <tr>
        <td>
<!--          <h3> Media Handling </h3>-->
         <form name='media_handling' action="<?php echo admin_url(); ?>admin.php?page=<?php echo WP_CONST_ADVANCED_XML_IMP_SLUG;?>/index.php&__module=<?php echo $_REQUEST['__module']?>&step=import_option"  method="post"  enctype="multipart/form-data" onsubmit = "return media_check();" >
        <div class='msg' id = 'showMsg' style = 'display:none;'></div>
          <?php  if(isset($_REQUEST['step']) && $_REQUEST['step'] == 'media_handle')  {   ?>
        <div id='sec-four' <?php if($_REQUEST['step']!= 'media_handle'){ ?> style='display:none;' <?php } ?> >
	<div style="margin-top:20px;margin-left:10px;margin-bottom:35px;">
				<label class="textalign" style="margin-bottom:10px;">Media Handling :</label>
				<div id="circlecheck">
              	  			<input type = 'radio' name = 'attachment' id = 'ex_attachment' class="circlecheckbox" value = 'dwld_url' onclick = "dwnld_attachment(this.id);" /><label id="optiontext" class="circle-label" for="ex_attachment"> Download Attachment </label> 
				</div>
		                <div id="circlecheck">
		 			<input type = 'radio' name = 'attachment' id = 'zip_upload' class="circlecheckbox" value = 'dwld_local' onclick = "dwnld_attachment(this.id);" > <label id="optiontext" class="circle-label" for="zip_upload">Upload Media Zip    </label>
				</div>
		                <label style = "margin-left:20px;"> <input type = "file" name = "adv_media" id = adv_media style = 'display:none;' /> </label>
				<input type = 'submit' name = 'media' value = 'Next >>' class = "btn btn-primary" style="float:right;margin-right:60px;">
	</div>
         </div>
        <?php } ?> 
          </form>
        </td>
        </tr>
        <tr>
        <td>
<!--          <h3> Import Options </h3>-->
         <form name='media_handling' action="<?php echo admin_url(); ?>admin.php?page=<?php echo WP_CONST_ADVANCED_XML_IMP_SLUG;?>/index.php&__module=<?php echo $_REQUEST['__module']?>&step=import_option"  method="post"  >
        <div class='msg' id = 'showMsg' style = 'display:none;'></div>
          <?php  if(isset($_REQUEST['step']) && $_REQUEST['step'] == 'import_option')  {   ?>
          <div id='sec-five' <?php if($_REQUEST['step']!= 'import_option'){ ?> style='display:none;' <?php } ?> >
          <?php
               if($_POST['attachment'] == 'dwld_local') {  
              if(isset($_FILES['adv_media'])) {
               $uploaded_compressedFile = $_FILES['adv_media']['tmp_name'];
               $get_basename_zipfile = explode('.', $_FILES['adv_media']['name']);
               $basename_zipfile = $get_basename_zipfile[0];
               $location_to_extract = $uploadDir['basedir'] . '/adv_imp_attachment/' ;
               $extracted_image_location = $uploadDir['baseurl'] . '/adv_imp_attachment/' . $basename_zipfile;
               $extracted_image_location_dir = $uploadDir['basedir'] . '/adv_imp_attachment/' ;
               $zip = new ZipArchive;
               if ($zip->open($uploaded_compressedFile) === TRUE) {
                       $zip->extractTo($location_to_extract);
                       $zip->close();
                       $extracted_status = 1;
               } else {
                       $extracted_status = 0;?>
              <?php           
               }
             
       } }  ?>        <?php if(isset($_POST['attachment'])) {
                           $attach = $_POST['attachment']; 
                                  if($attach == 'dwld_url')   {
                                        $value = 'yes';
                                   }
                                   else {
                                        $value = 'no';
                                        $_SESSION['img_path'] = $extracted_image_location_dir;
                                        $_SESSION['img_path_url'] = $extracted_image_location;
                                        
                                    }                        
} ?>
                 <input type = "hidden" name = "attach" id = "attach" value = "<?php echo $value; ?>" >
                  <?php if(isset($_SESSION['xml_values'])) {     $file_name = $_SESSION['xml_values']['uploadfilename'];
                                                                 $total     = $_SESSION['xml_values']['total'];
                                                                 $auth_cnt  = $_SESSION['xml_values']['authcnt'];
                                                                 $post_cnt  = $_SESSION['xml_values']['postcount'];
                                                                  ?> 
                 <input type = "hidden" name = "file_name" id = "file_name" value = "<?php echo $file_name; ?> " />
                 <input type = "hidden" name = "total_cnt" id = "total_cnt" value = "<?php echo $total; ?> " />
                 <input type = "hidden" name = "auth_cnt" id = "auth_cnt" value = "<?php echo $auth_cnt; ?> " />
                 <input type = "hidden" name = "post_cnt" id = "post_cnt" value = "<?php echo $post_cnt; ?> " />
                  <?php } if(isset($_SESSION['user']))  { $ex_user = $_SESSION['user']; } 
                            if(isset($_SESSION['user_imp_type'])) { $user_type = $_SESSION['user_imp_type']; } ?>
                 <input type = "hidden" id ="ex_user" name = "ex_user"  value = "<?php echo $ex_user; ?>" />
                 <input type = "hidden" id ="user_type" name = "user_type" value = "<?php echo $user_type; ?>" />

	<div style="margin-top:15px;margin-left:15px;">
	<label class="textalign" style="margin-bottom:10px;">Import Option :</label>
        <div class="squarecheck"><input name='duptitle' id='duptitle' type="checkbox" value="" ><label for = "duptitle"></label></div><label id="optiontext" style="margin-left:28px;margin-top:-32px"> Detect duplicate post title</label>
        <div class="squarecheck"><input name='dupcontent' id='dupcontent' type="checkbox" value="" ><label for = "dupcontent"></label></div><label id="optiontext" style="margin-left:28px;margin-top:-35px"> Detect duplicate post content</label> 
	</div>
        <input type="hidden" id="authcnt" name="authcnt" value="1"/>
        <input type='hidden' id = 'tmpcnt' name = 'tmpcnt' value = '1'/>
        <input type= 'hidden' id = 'implimit' name = 'implimit' value = '0'/>
                 <label id="optiontext" style="margin-left:15px;"> Enter the no. of imports per server requests   <input type = "text" name = "imp_limit" id = "imp_limit" value = "1"  onblur="check_allnumeric(this.value);" style="margin-left:10px"/></label>
        <input type = 'button' name='importfile' id='importtype' value='Import' onclick = "importRecordsbySettings();" class = 'btn btn-primary'    style="float:right;margin-top:24px;margin-right:45px"/>  
        <div id="ajaxloader" style="display:none"><img src="<?php echo WP_CONST_ADVANCED_XML_IMP_DIR; ?>images/ajax-loader.gif"> Processing...</div>
 
                 
         </div>
        <?php } ?>
          </form>
        </td>
        </tr>

		   
				</table>
			</div>
                <input type = 'hidden' id = 'current_step' name = 'current_step' value = 'content_mapping' >
		<div id='reportLog' class='reportLog' style="width:62%;">
		<h3>Logs :</h3>
		<div id="logtabs" class="logcontainer">
		<div id="log" class='log'>	
		</div>
		</div>
		</div>
	</div>


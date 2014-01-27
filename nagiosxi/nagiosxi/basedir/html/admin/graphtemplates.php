<?php
//
// Copyright (c) 2011 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: monitoringplugins.php 451 2011-01-13 18:04:47Z egalstad $

require_once(dirname(__FILE__).'/../includes/common.inc.php');

// initialization stuff
pre_init();

// start session
init_session();

// grab GET or POST variables 
grab_request_vars();

// check prereqs
check_prereqs();

// check authentication
check_authentication(false);

// only admins can access this page
if(is_admin()==false){
	echo $lstr['NotAuthorizedErrorText'];
	exit();
	}

// route request
route_request();


function route_request(){
	global $request;
	
	//if(in_demo_mode()==true)
		//header("Location: main.php");
		
	if(isset($request["download"]))
		do_download();
	else if (isset($request["upload"]))
		do_upload();
	else if (isset($request["delete"]))
		do_delete();
	else if (isset($request["save"]))
		do_save(false);
	else if (isset($request["apply"]))
		do_save(true);
	else if (isset($request["cancel"]))
		show_templates();
	else if (isset($request["edit"]))
		do_edit();
	else
		show_templates();
	
	exit;
	}
	
	
function show_templates($error=false,$msg=""){
	global $request;
	global $lstr;
	

	$templates=get_graph_templates();
	

	do_page_start(array("page_title"=>$lstr['GraphTemplatesPageTitle']),true);

?>

	
	<h1><?php echo $lstr['GraphTemplatesPageHeader'];?></h1>
	

<?php
	display_message($error,false,$msg);
?>

	<?php echo $lstr['GraphTemplatesPageNotes'];?>
	
	
	
	<br clear="all">
	<br clear="all">
	
	<?php 
		//print_r($plugins);
	?>
	
	<form enctype="multipart/form-data" action="" method="post">
	<input type="hidden" name="upload" value="1">
	<?php echo get_nagios_session_protector();?>
	<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
	<label><?php echo $lstr["UploadNewGraphTemplateBoxText"];?>:</label><br>
	<input name="uploadedfile" class="textfield"  type="file" />	<input type="submit" class="submitbutton" value="<?php echo $lstr['UploadGraphTemplateButton'];?>" />
	</form>
	
	<br>

	<table class="standardtable">
	<thead> 
	<tr><th><?php echo $lstr['FileTableHeader'];?></th><th><?php echo $lstr['GraphTemplateDirTableHeader'];?></th><th><?php echo $lstr['FileOwnerTableHeader'];?></th><th><?php echo $lstr['FileGroupTableHeader'];?></th><th><?php echo $lstr['FilePermsTableHeader'];?></th><th><?php echo $lstr['DateTableHeader'];?></th><th><?php echo $lstr['ActionsTableHeader'];?></th></tr>
	</thead>
	<tbody>
	
<?php
	$x=0;
	foreach($templates as $template){
	
		$x++;
	
		$rowclass="";
			
		if(($x%2)!=0)
			$rowclass.=" odd";
		else
			$rowclass.=" even";
	
		echo "<tr class=".$rowclass.">";
		echo "<td>".$template["file"]."</td>";
		echo "<td>".$template["dir"]."</td>";
		echo "<td>".$template["owner"]."</td>";
		echo "<td>".$template["group"]."</td>";
		echo "<td>".$template["permstring"]."</td>";
		echo "<td>".$template["date"]."</td>";
		echo "<td>";
		echo "<a href='?edit=".$template["file"]."&dir=".$template["dir"]."'><img src='".theme_image("editfile.png")."' alt='".$lstr['EditAlt']."' title='".$lstr['EditAlt']."'></a> ";
		echo "<a href='?download=".$template["file"]."&dir=".$template["dir"]."'><img src='".theme_image("download.png")."' alt='".$lstr['DownloadAlt']."' title='".$lstr['DownloadAlt']."'></a> ";
		echo "<a href='?delete=".$template["file"]."&dir=".$template["dir"]."&nsp=".get_nagios_session_protector_id()."'><img src='".theme_image("delete.png")."' alt='".$lstr['DeleteAlt']."' title='".$lstr['DeleteAlt']."'></a>";
		echo "</td>";
		echo "</tr>\n";
		}
?>
	
	</tbody>
	</table>

<?php

	do_page_end(true);
	exit();
	}

	
function do_edit($error=false,$msg=""){
	global $request;
	global $lstr;
	
	$file=grab_request_var("edit","");
	$tdir=grab_request_var("dir","templates");
	
		
	// clean the filename
	$file=str_replace("..","",$file);
	$file=str_replace("/","",$file);
	$file=str_replace("\\","",$file);

	// clean the directory
	$tdir=str_replace("..","",$tdir);
	$tdir=str_replace("/","",$tdir);
	$tdir=str_replace("\\","",$tdir);
	
	$dir=get_graph_template_dir()."/".$tdir;
	$thefile=$dir."/".$file;
	
	
	// read file
	$fc=file_get_contents($thefile);
	

	do_page_start(array("page_title"=>$lstr['EditGraphTemplatePageTitle']),true);

?>

	
	<h1><?php echo $lstr['EditGraphTemplatePageHeader'];?></h1>
	

<?php
	display_message($error,false,$msg);
?>

	<?php echo $lstr['EditGraphTemplatePageNotes'];?>
	

	
	<form enctype="multipart/form-data" action="" method="post">
	<?php echo get_nagios_session_protector();?>
	<input type="hidden" name="dir" value="<?php echo htmlentities($tdir);?>">
	<input type="hidden" name="file" value="<?php echo htmlentities($file);?>">
	
	<br>
	
	<strong><?php echo $tdir."/".$file;?></strong><br>
<textarea cols="80" rows="20" name="fc"><?php echo htmlentities($fc);?></textarea><br clear="all">

	<input type="submit" class="submitbutton" name="save" value="<?php echo $lstr['SaveButton'];?>" />
	<input type="submit" class="submitbutton" name="apply" value="<?php echo $lstr['ApplyButton'];?>" />
	<input type="submit" class="submitbutton" name="cancel" value="<?php echo $lstr['CancelButton'];?>" />
	</form>

<?php

	do_page_end(true);
	exit();
	}
	
function do_save($apply=false){
	global $cfg;
	global $lstr;
	global $request;
	
		
	// demo mode
	if(in_demo_mode()==true)
		show_templates(true,$lstr['DemoModeChangeError']);
		
	// check session
	check_nagios_session_protector();

	$file=grab_request_var("file","");
	$tdir=grab_request_var("dir","templates");
	$fc=grab_request_var("fc","");
	
	// clean the filename
	$file=str_replace("..","",$file);
	$file=str_replace("/","",$file);
	$file=str_replace("\\","",$file);
	
	// clean the directory
	$tdir=str_replace("..","",$tdir);
	$tdir=str_replace("/","",$tdir);
	$tdir=str_replace("\\","",$tdir);
	
	$dir=get_graph_template_dir()."/".$tdir;
	$thefile=$dir."/".$file;
	
	$result=file_put_contents($thefile,$fc);
	if($result===FALSE){
		$msg=$lstr['FileWriteErrorText'];
		$error=true;
		}
	else{
		$msg=$lstr['FileSavedText'];
		$error=false;
		}
	
	// log it
	send_to_audit_log("User edited graph template '".$file."'",AUDITLOGTYPE_CHANGE);

	if($apply==true){
		do_edit($error,$msg);
		}
	else{
		show_templates($error,$msg);
		}
	}


	
function do_download(){
	global $cfg;
	
	$result=grab_request_var("result","ok");
	$file=grab_request_var("download","");
	$tdir=grab_request_var("dir","templates");
	
		
	// clean the filename
	$file=str_replace("..","",$file);
	$file=str_replace("/","",$file);
	$file=str_replace("\\","",$file);

	// clean the directory
	$tdir=str_replace("..","",$tdir);
	$tdir=str_replace("/","",$tdir);
	$tdir=str_replace("\\","",$tdir);
	
	$dir=get_graph_template_dir()."/".$tdir;
	$thefile=$dir."/".$file;
	
	$mime_type="";
	header('Content-type: '."text/plain");
	header("Content-length: " . filesize($thefile)); 
	header('Content-Disposition: attachment; filename="'.basename($thefile).'"');
	readfile($thefile); 
	exit();
	}
	
function do_upload(){
	global $cfg;
	global $lstr;
	global $request;
	
	// demo mode
	if(in_demo_mode()==true)
		show_templates(true,$lstr['DemoModeChangeError']);
		
	// check session
	check_nagios_session_protector();

	//print_r($request);
	
	$uploaded_file=grab_request_var("uploadedfile");
	//if(have_value($uploaded_file)==false)
	//	show_templates(true,$lstr['NoMIBUploadedText']);
	
	$target_path=get_graph_template_dir()."/templates";
	$target_path.="/";
	$target_path.=basename( $_FILES['uploadedfile']['name']); 
	
	//echo "TEMP NAME: ".$_FILES['uploadedfile']['tmp_name']."<BR>\n";

	if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)){
		chmod($target_path,0664);
		
		// log it
		send_to_audit_log("User uploaded graph template '".$_FILES['uploadedfile']['name']."'",AUDITLOGTYPE_CHANGE);

		// success!
		show_templates(false,$lstr['GraphTemplateUploadedText']);
		}
	else{
		// error
		show_templates(true,$lstr['GraphTemplateUploadFailedText']);
		}

	exit();
	}

function do_delete(){
	global $cfg;
	global $lstr;
	global $request;
	
		
	// demo mode
	if(in_demo_mode()==true)
		show_templates(true,$lstr['DemoModeChangeError']);
		
	// check session
	check_nagios_session_protector();

	$file=grab_request_var("delete","");
	$tdir=grab_request_var("dir","templates");
	
	// clean the filename
	$file=str_replace("..","",$file);
	$file=str_replace("/","",$file);
	$file=str_replace("\\","",$file);
	
	// clean the directory
	$tdir=str_replace("..","",$tdir);
	$tdir=str_replace("/","",$tdir);
	$tdir=str_replace("\\","",$tdir);
	
	$dir=get_graph_template_dir()."/".$tdir;
	$thefile=$dir."/".$file;
	
	if(unlink($thefile)===TRUE){

		// log it
		send_to_audit_log("User deleted graph template '".$file."'",AUDITLOGTYPE_CHANGE);

		// success!
		show_templates(false,$lstr['GraphTemplateDeletedText']);
		}
	else{
		// error
		show_templates(true,$lstr['GraphTemplateeleteFailedText']);
		}
	}
?>
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
	else
		show_mibs();
	
	exit;
	}
	
	
function show_mibs($error=false,$msg=""){
	global $request;
	global $lstr;
	

	$mibs=get_mibs();
	

	do_page_start(array("page_title"=>$lstr['MIBsPageTitle']),true);

?>

	
	<h1><?php echo $lstr['MIBsPageHeader'];?></h1>
	

<?php
	display_message($error,false,$msg);
?>

	<?php echo $lstr['MIBsPageNotes'];?>
	
	
	<p>
	<div class="bluebutton" style="width: 150px; float: right;">
	<a href="http://www.mibdepot.com/" target="_blank">Get MIBs</a>
	</div>
	You can find hundreds of additional MIBs at the following sites:
	</p>
	<ul>
	<li><a href="http://www.mibdepot.com/" target="_blank">http://www.mibdepot.com</a></li>
	<li><a href="http://www.oidview.com/mibs/detail.html" target="_blank">http://www.oidview.com/mibs/</a></li>
	</ul>
	
	<br clear="all">
	<br clear="all">
	
	<?php 
		//print_r($plugins);
	?>
	
	<form enctype="multipart/form-data" action="" method="post">
	<input type="hidden" name="upload" value="1">
	<?php echo get_nagios_session_protector();?>
	<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
	<label><?php echo $lstr["UploadNewMIBBoxText"];?>:</label><br>
	<input name="uploadedfile" class="textfield"  type="file" />	<input type="submit" class="submitbutton" value="<?php echo $lstr['UploadMIBButton'];?>" />
	</form>
	
	<br>

	<table class="standardtable">
	<thead> 
	<tr><th><?php echo $lstr['MIBTableHeader'];?></th><th><?php echo $lstr['FileTableHeader'];?></th><th><?php echo $lstr['FileOwnerTableHeader'];?></th><th><?php echo $lstr['FileGroupTableHeader'];?></th><th><?php echo $lstr['FilePermsTableHeader'];?></th><th><?php echo $lstr['DateTableHeader'];?></th><th><?php echo $lstr['ActionsTableHeader'];?></th></tr>
	</thead>
	<tbody>
	
<?php
	$x=0;
	foreach($mibs as $mib){
	
		$x++;
	
		$rowclass="";
			
		if(($x%2)!=0)
			$rowclass.=" odd";
		else
			$rowclass.=" even";
	
		echo "<tr class=".$rowclass.">";
		echo "<td>".$mib["mibname"]."</td>";
		echo "<td>".$mib["file"]."</td>";
		echo "<td>".$mib["owner"]."</td>";
		echo "<td>".$mib["group"]."</td>";
		echo "<td>".$mib["permstring"]."</td>";
		echo "<td>".$mib["date"]."</td>";
		echo "<td>";
		echo "<a href='?download=".$mib["file"]."'><img src='".theme_image("download.png")."' alt='".$lstr['DownloadAlt']."' title='".$lstr['DownloadAlt']."'></a> ";
		echo "<a href='?delete=".$mib["file"]."&nsp=".get_nagios_session_protector_id()."'><img src='".theme_image("delete.png")."' alt='".$lstr['DeleteAlt']."' title='".$lstr['DeleteAlt']."'></a>";
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

function do_download(){
	global $cfg;
	
	$result=grab_request_var("result","ok");
	$file=grab_request_var("download","");
	
		
	// clean the filename
	$file=str_replace("..","",$file);
	$file=str_replace("/","",$file);
	$file=str_replace("\\","",$file);
	
	$dir=get_mib_dir();
	$thefile=$dir."/".$file;
	
	$mime_type="";
	header('Content-type: '.$mime_type);
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
		show_mibs(true,$lstr['DemoModeChangeError']);
		
	// check session
	check_nagios_session_protector();

	//print_r($request);
	
	$uploaded_file=grab_request_var("uploadedfile");
	//if(have_value($uploaded_file)==false)
	//	show_mibs(true,$lstr['NoMIBUploadedText']);
	
	$target_path=get_mib_dir();
	$target_path.="/";
	$target_path.=basename( $_FILES['uploadedfile']['name']); 
	
	//echo "TEMP NAME: ".$_FILES['uploadedfile']['tmp_name']."<BR>\n";

	if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)){
		chmod($target_path,0664);
		// success!
		show_mibs(false,$lstr['MIBUploadedText']);
		}
	else{
		// error
		show_mibs(true,$lstr['MIBUploadFailedText']);
		}

	exit();
	}

function do_delete(){
	global $cfg;
	global $lstr;
	global $request;
	
		
	// demo mode
	if(in_demo_mode()==true)
		show_mibs(true,$lstr['DemoModeChangeError']);
		
	// check session
	check_nagios_session_protector();

	$file=grab_request_var("delete","");
		
	// clean the filename
	$file=str_replace("..","",$file);
	$file=str_replace("/","",$file);
	$file=str_replace("\\","",$file);
	
	$dir=get_mib_dir();
	$thefile=$dir."/".$file;
	
	if(unlink($thefile)===TRUE){
		// success!
		show_mibs(false,$lstr['MIBDeletedText']);
		}
	else{
		// error
		show_mibs(true,$lstr['MIBDeleteFailedText']);
		}
	}
?>
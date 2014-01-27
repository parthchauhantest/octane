<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: monitoringplugins.php 1208 2012-06-09 18:00:37Z egalstad $

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
		show_plugins();
	
	exit;
	}
	
	
function show_plugins($error=false,$msg=""){
	global $request;
	global $lstr;
	

	$plugins=get_nagioscore_plugins();
	

	do_page_start(array("page_title"=>$lstr['MonitoringPluginsPageTitle']),true);

?>

	
	<h1><?php echo $lstr['MonitoringPluginsPageHeader'];?></h1>
	

<?php
	display_message($error,false,$msg);
?>

	<?php echo $lstr['MonitoringPluginsPageNotes'];?>
	
	
	<p>
	<div class="bluebutton" style="width: 150px; float: right;">
	<a href="http://exchange.nagios.org/directory/Plugins" target="_blank">Get Plugins</a>
	</div>
	Find hundreds of community-developed plugins to extend Nagios XI's capabilities at <a href="http://exchange.nagios.org/directory/Plugins" target="_blank">http://exchange.nagios.org</a>.
	</p>
	
	<br clear="all">
	<br clear="all">
	
	<?php 
		//print_r($plugins);
	?>
	
	<form enctype="multipart/form-data" action="" method="post">
	<input type="hidden" name="upload" value="1">
	<?php echo get_nagios_session_protector();?>
	<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
	<label><?php echo $lstr["UploadNewPluginBoxText"];?>:</label><br>
	<input name="uploadedfile" class="textfield"  type="file" />	<input type="submit" class="submitbutton" value="<?php echo $lstr['UploadPluginButton'];?>" />
	</form>
	
	<br>

	<table class="standardtable">
	<thead> 
	<tr><th><?php echo $lstr['FileTableHeader'];?></th><th><?php echo $lstr['FileOwnerTableHeader'];?></th><th><?php echo $lstr['FileGroupTableHeader'];?></th><th><?php echo $lstr['FilePermsTableHeader'];?></th><th><?php echo $lstr['DateTableHeader'];?></th><th><?php echo $lstr['ActionsTableHeader'];?></th></tr>
	</thead>
	<tbody>
	
<?php
	$x=0;
	foreach($plugins as $plugin){
	
		$x++;
	
		$rowclass="";
			
		if(($x%2)!=0)
			$rowclass.=" odd";
		else
			$rowclass.=" even";
	
		echo "<tr class=".$rowclass.">";
		echo "<td>".$plugin["file"]."</td>";
		echo "<td>".$plugin["owner"]."</td>";
		echo "<td>".$plugin["group"]."</td>";
		echo "<td>".$plugin["permstring"]."</td>";
		echo "<td>".$plugin["date"]."</td>";
		echo "<td>";
		echo "<a href='?download=".$plugin["file"]."'><img src='".theme_image("download.png")."' alt='".$lstr['DownloadAlt']."' title='".$lstr['DownloadAlt']."'></a> ";
		echo "<a href='?delete=".$plugin["file"]."&nsp=".get_nagios_session_protector_id()."'><img src='".theme_image("delete.png")."' alt='".$lstr['DeleteAlt']."' title='".$lstr['DeleteAlt']."'></a>";
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
	
	$dir=$cfg['component_info']['nagioscore']['plugin_dir'];
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
		show_plugins(true,$lstr['DemoModeChangeError']);
		
	// check session
	check_nagios_session_protector();

	//print_r($request);
	
	$uploaded_file=grab_request_var("uploadedfile");
	//if(have_value($uploaded_file)==false)
	//	show_plugins(true,$lstr['NoPluginUploadedText']);
	
	$target_path=$cfg['component_info']['nagioscore']['plugin_dir'];
	$target_path.="/";
	$target_path.=basename( $_FILES['uploadedfile']['name']); 
	
	//echo "TEMP NAME: ".$_FILES['uploadedfile']['tmp_name']."<BR>\n";
	
	$plugin_file=$_FILES['uploadedfile']['name'];

	// log it
	send_to_audit_log("User installed plugin '".$plugin_file."'",AUDITLOGTYPE_CHANGE);

	if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)){
		// make the plugin executable
		chmod($target_path,0755);
		// success!
		show_plugins(false,$lstr['PluginUploadedText']);
		}
	else{
		// error
		show_plugins(true,$lstr['PluginUploadFailedText']);
		}

	exit();
	}

function do_delete(){
	global $cfg;
	global $lstr;
	global $request;
	
		
	// demo mode
	if(in_demo_mode()==true)
		show_plugins(true,$lstr['DemoModeChangeError']);
		
	// check session
	check_nagios_session_protector();

	$file=grab_request_var("delete","");
		
	// clean the filename
	$file=str_replace("..","",$file);
	$file=str_replace("/","",$file);
	$file=str_replace("\\","",$file);
	
	// log it
	send_to_audit_log("User deleted plugin '".$file."'",AUDITLOGTYPE_DELETE);

	$dir=$cfg['component_info']['nagioscore']['plugin_dir'];
	$thefile=$dir."/".$file;
	
	if(unlink($thefile)===TRUE){
		// success!
		show_plugins(false,$lstr['PluginDeletedText']);
		}
	else{
		// error
		show_plugins(true,$lstr['PluginDeleteFailedText']);
		}
	}
?>
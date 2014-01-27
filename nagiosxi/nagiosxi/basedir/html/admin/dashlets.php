<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: dashlets.php 1208 2012-06-09 18:00:37Z egalstad $

define("SKIPDASHLETS",1);  // skips auto-inclusion of dashlets

require_once(dirname(__FILE__).'/../includes/common.inc.php');

//require_once(dirname(__FILE__).'/../includes/configwizards.inc.php');

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
	
//	if(in_demo_mode()==true)
		//header("Location: main.php");
		
	if(isset($request["download"]))
		do_download();
	else if (isset($request["upload"]))
		do_upload();
	else if (isset($request["delete"]))
		do_delete();
	else
		show_dashlets();
	
	exit;
	}
	
	
function show_dashlets($error=false,$msg=""){
	global $request;
	global $lstr;
	global $dashlets;


	
	do_page_start(array("page_title"=>$lstr['ManageDashletsPageTitle']),true);

?>

	
	<h1><?php echo $lstr['ManageDashletsPageHeader'];?></h1>
	

<?php
	display_message($error,false,$msg);
?>

	<?php echo $lstr['ManageDashletsPageNotes'];?>
	
	<p>
	<div class="bluebutton" style="width: 150px; float: right;">
	<a href="http://exchange.nagios.org/directory/Addons/Dashlets" target="_blank">Get Dashlets</a>
	</div>
	 You can find additional dashlets for Nagios XI at <a href='http://exchange.nagios.org/directory/Addons/Dashlets' target='_blank'>Nagios Exchange</a>.
	</p>
	
	<br clear="all">
	<br>

	
	<?php 
		//echo "INITIAL DASHLETS:<BR>";
		//print_r($dashlets);
	?>
	
	<form enctype="multipart/form-data" action="" method="post">
	<?php echo get_nagios_session_protector();?>
	<input type="hidden" name="upload" value="1">
	<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
	<label><?php echo $lstr["UploadNewDashletBoxText"];?>:</label><br>
	<input name="uploadedfile" class="textfield"  type="file" />	<input type="submit" class="submitbutton" value="<?php echo $lstr['UploadDashletButton'];?>" />
	</form>
	
	<br>

	<table class="standardtable">
	<thead> 
	<tr><th><?php echo $lstr['DashletNameTableHeader'];?></th><th><?php echo $lstr['ActionsTableHeader'];?></th></tr>
	</thead>
	<tbody>
	
<?php

	$x=0;

	// reset the array - only system dashlets should have been in the array at this point
	$dashlets=array();
	reset($dashlets);	
	//echo "<BR>NEW DASHLETS<BR>";
	//print_r($dashlets);
	
	$p=dirname(__FILE__)."/../includes/dashlets/";
	$subdirs=scandir($p);
	foreach($subdirs as $sd){
	
		if($sd=="." || $sd=="..")
			continue;
			
		$d=$p.$sd;
		
		if(is_dir($d)){
		
			$cf=$d."/$sd.inc.php";
			if(file_exists($cf)){
			
				include_once($cf);
				
				//echo "INCLUDED: $sd<BR>";
				//echo "DASHLETS:<BR>";
				//print_r($dashlets);
				
				$dashlet_dir=basename($d);
				
				// display thedashlet
				foreach($dashlets as $name => $darray){
					show_dashlet($dashlet_dir,$name,$darray,$x);
					}
				
				// reset the array
				$dashlets=array();
				reset($dashlets);
				
				$x++;
				}
			}
		}
	
?>
	
	</tbody>
	</table>

<?php

	do_page_end(true);
	exit();
	}
	
	
function show_dashlet($dashlet_dir,$dashlet_name,$darray,$x){
	global $lstr;

	$rowclass="";
	
	if(($x%2)!=0)
		$rowclass.=" odd";
	else
		$rowclass.=" even";

	echo "<tr class=".$rowclass.">";
	
	echo "<td>";
	display_dashlet_preview($dashlet_name,$darray);
	//echo $dashlet_name;
	echo "</td>";
	
	echo "<td>";
	echo "<a href='?download=".$dashlet_dir."'><img src='".theme_image("download.png")."' alt='".$lstr['DownloadAlt']."' title='".$lstr['DownloadAlt']."'></a> ";
	echo "<a href='?delete=".$dashlet_dir."&nsp=".get_nagios_session_protector_id()."'><img src='".theme_image("delete.png")."' alt='".$lstr['DeleteAlt']."' title='".$lstr['DeleteAlt']."'></a>";
	echo "</td>";
	echo "</tr>\n";

	}

	
function do_download(){
	global $cfg;
	global $lstr;

	// demo mode
	if(in_demo_mode()==true)
		show_dashlets(true,$lstr['DemoModeChangeError']);
	
	$dashlet_dir=grab_request_var("download");
	if(have_value($dashlet_dir)==false)
		show_dashlets();
	
	// clean the name
	$dashlet_dir=str_replace("..","",$dashlet_dir);
	$dashlet_dir=str_replace("/","",$dashlet_dir);
	$dashlet_dir=str_replace("\\","",$dashlet_dir);
	
	$id=submit_command(COMMAND_PACKAGE_DASHLET,$dashlet_dir);
	if($id<=0)
		show_dashlets(true,$lstr['ErrorSubmittingCommandText']);
	else{
		for($x=0;$x<40;$x++){
			$status_code=-1;
			$result_code=-1;
			$args=array(
				"cmd" => "getcommands",
				"command_id" => $id,
				);
			$xml=get_backend_xml_data($args);
			if($xml){
				if($xml->command[0]){
					$status_code=intval($xml->command[0]->status_code);
					$result_code=intval($xml->command[0]->result_code);
					}
				}
			if($status_code==2){
				if($result_code==0){
				
					// wizard was packaged, send it to user
					$dir="/usr/local/nagiosxi/tmp";
					$thefile=$dir."/dashlet-".$dashlet_dir.".zip";
					
					//chdir($dir);
					
					$mime_type="";
					header('Content-type: '.$mime_type);
					header("Content-length: " . filesize($thefile)); 
					header('Content-Disposition: attachment; filename="'.basename($thefile).'"');
					readfile($thefile); 					
					}
				else
					show_dashlets(true,$lstr['DashletPackagingTimedOutText']);
				exit();
				}
			usleep(500000);
			}
		}

	exit();
	}
	
	
function do_upload(){
	global $cfg;
	global $lstr;
	global $request;
	
	// demo mode
	if(in_demo_mode()==true)
		show_dashlets(true,$lstr['DemoModeChangeError']);

	// check session
	check_nagios_session_protector();

	//print_r($request);
	//exit();
	
	$uploaded_file=grab_request_var("uploadedfile");

	
	$target_path="/usr/local/nagiosxi/tmp";
	$target_path.="/";
	$dashlet_file=basename($_FILES['uploadedfile']['name']);
	$target_path.="dashlet-".$dashlet_file; 
	
	// log it
	send_to_audit_log("User installed dashlet '".$dashlet_file."'",AUDITLOGTYPE_CHANGE);

	//echo "TEMP NAME: ".$_FILES['uploadedfile']['tmp_name']."<BR>\n";
	//echo "TARGET: ".$target_path."<BR>\n";

	if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)){

		// fix perms
		chmod($target_path,0550);
		chgrp($target_path,"nagios");

		$id=submit_command(COMMAND_INSTALL_DASHLET,$dashlet_file);
		if($id<=0)
			show_dashlets(true,$lstr['ErrorSubmittingCommandText']);
		else{
			for($x=0;$x<20;$x++){
				$status_code=-1;
				$result_code=-1;
				$args=array(
					"cmd" => "getcommands",
					"command_id" => $id,
					);
				$xml=get_backend_xml_data($args);
				if($xml){
					if($xml->command[0]){
						$status_code=intval($xml->command[0]->status_code);
						$result_code=intval($xml->command[0]->result_code);
						$result_text=strval($xml->command[0]->result);
						}
					}
				if($status_code==2){
					if($result_code==0)
						show_dashlets(false,$lstr['DashletInstalledText']);
					else{
						$emsg="";
						if($result_text!="")
							$emsg.=" ".$result_text."";
						show_dashlets(true,$lstr['DashletInstallFailedText'].$emsg);
						//show_dashlets(true,$lstr['DashletInstallFailedText']);
						}
					exit();
					}
				usleep(500000);
				}
			}
		show_dashlets(false,$lstr['DashletScheduledForInstallationText']);
		}
	else{
		// error
		show_dashlets(true,$lstr['DashletUploadFailedText']);
		}

	exit();
	}

function do_delete(){
	global $cfg;
	global $lstr;
	global $request;
	
	// demo mode
	if(in_demo_mode()==true)
		show_dashlets(true,$lstr['DemoModeChangeError']);

	// check session
	check_nagios_session_protector();

	$dir=grab_request_var("delete","");
		
	// clean the filename
	$dir=str_replace("..","",$dir);
	$dir=str_replace("/","",$dir);
	$dir=str_replace("\\","",$dir);
	
	if($dir=="")
		show_dashlets();
		
	// log it
	send_to_audit_log("User deleted dashlet '".$dir."'",AUDITLOGTYPE_DELETE);

	$id=submit_command(COMMAND_DELETE_DASHLET,$dir);
	if($id<=0)
		show_dashlets(true,$lstr['ErrorSubmittingCommandText']);
	else{
		for($x=0;$x<14;$x++){
			$status_code=-1;
			$result_code=-1;
			$args=array(
				"cmd" => "getcommands",
				"command_id" => $id,
				);
			$xml=get_backend_xml_data($args);
			if($xml){
				if($xml->command[0]){
					$status_code=intval($xml->command[0]->status_code);
					$result_code=intval($xml->command[0]->result_code);
					}
				}
			if($status_code==2){
				show_dashlets(false,$lstr['DashletDeletedText']);
				exit();
				}
			usleep(500000);
			}
		}
	show_dashlets(false,$lstr['DashletScheduledForDeletionText']);
	exit();
	}
?>
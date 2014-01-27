<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: configwizards.php 1208 2012-06-09 18:00:37Z egalstad $

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
	
	//if(in_demo_mode()==true)
		//header("Location: main.php");
		
	if(isset($request["download"]))
		do_download();
	else if (isset($request["upload"]))
		do_upload();
	else if (isset($request["delete"]))
		do_delete();
	else
		show_wizards();
	
	exit;
	}
	
	
function show_wizards($error=false,$msg=""){
	global $request;
	global $lstr;
	global $configwizards;
	

	
	do_page_start(array("page_title"=>$lstr['ManageConfigWizardsPageTitle']),true);

?>

	
	<h1><?php echo $lstr['ManageConfigWizardsPageHeader'];?></h1>
	

<?php
	display_message($error,false,$msg);
?>

	<?php echo $lstr['ManageConfigWizardsPageNotes'];?>
	
	<p>
	<div class="bluebutton" style="width: 150px; float: right;">
	<a href="http://exchange.nagios.org/directory/Addons/Configuration/Configuration-Wizards" target="_blank">Get Wizards</a>
	</div>
	 You can find additional configuration wizards for Nagios XI at <a href='http://exchange.nagios.org/directory/Addons/Configuration/Configuration-Wizards' target='_blank'>Nagios Exchange</a>.
	</p>
	
	<br clear="all">
	<br>

	
	<?php 
		//print_r($plugins);
	?>
	
	<form enctype="multipart/form-data" action="" method="post">
	<input type="hidden" name="upload" value="1">
	<?php echo get_nagios_session_protector();?>
	<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
	<label><?php echo $lstr["UploadNewWizardBoxText"];?>:</label><br>
	<input name="uploadedfile" class="textfield"  type="file" />	<input type="submit" class="submitbutton" value="<?php echo $lstr['UploadWizardButton'];?>" />
	</form>
	
	<br>

	<table class="standardtable">
	<thead> 
	<tr><th><?php echo $lstr['WizardNameTableHeader'];?></th><th><?php echo $lstr['WizardTypeTableHeader'];?></th><th><?php echo $lstr['ActionsTableHeader'];?></th></tr>
	</thead>
	<tbody>
	
<?php

	$x=0;

	
	$p=dirname(__FILE__)."/../includes/configwizards/";
	$subdirs=scandir($p);
	foreach($subdirs as $sd){
	
		if($sd=="." || $sd=="..")
			continue;
			
		$d=$p.$sd;
		
		if(is_dir($d)){
		
			$cf=$d."/$sd.inc.php";
			if(file_exists($cf)){
			
				include_once($cf);
				
				//echo "WIZARDS:<BR>";
				//print_r($configwizards);
				
				$wizard_dir=basename($d);
				
				// display the wizard
				show_wizard($wizard_dir,current($configwizards),$x);
				
				// reset the array
				$configwizards=array();
				reset($configwizards);
				
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
	
	
function show_wizard($wizard_dir,$cw,$x){
	global $lstr;

	$rowclass="";
	
	$wizard_name=$cw[CONFIGWIZARD_NAME];
	$wizard_typeid=$cw[CONFIGWIZARD_TYPE];
	switch($wizard_typeid){
		case CONFIGWIZARD_TYPE_MONITORING:
			$wizard_type="Monitoring";
			break;
		default:
			$wizard_type="?";
			break;
		}
		
	if(($x%2)!=0)
		$rowclass.=" odd";
	else
		$rowclass.=" even";

	echo "<tr class=".$rowclass.">";
	
	echo "<td>";
	$img=$cw[CONFIGWIZARD_PREVIEWIMAGE];
	if($img!=""){
		echo "<div style='float: left; margin-right: 10px;'>";
		echo "<img src='".get_base_url()."includes/components/nagioscore/ui/images/logos/".$img."'>";
		echo "</div>";
		}
	echo "<div style='float: left;'>";
	echo "<b>".$cw[CONFIGWIZARD_DISPLAYTITLE]."</b><br>".$cw[CONFIGWIZARD_DESCRIPTION];
	$about="";
	if(array_key_exists(CONFIGWIZARD_VERSION,$cw))
		$about.="Version: ".$cw[CONFIGWIZARD_VERSION];
	if(array_key_exists(CONFIGWIZARD_DATE,$cw)){
		if($about!="")
			$about.=", ";
		$about.="Date: ".$cw[CONFIGWIZARD_DATE];
		}
	if(array_key_exists(CONFIGWIZARD_AUTHOR,$cw)){
		if($about!="")
			$about.=", ";
		$about.="Author: ".$cw[CONFIGWIZARD_AUTHOR];
		}
	if(array_key_exists(CONFIGWIZARD_COPYRIGHT,$cw)){
		if($about!="")
			$about.=", ";
		$about.=$cw[CONFIGWIZARD_COPYRIGHT];
		}
	if($about!="")
		echo "<br>".$about;
	echo "</div>";
	echo "</td>";
	
	echo "<td>".$wizard_type."</td>";
	
	echo "<td>";
	echo "<a href='?download=".$wizard_dir."'><img src='".theme_image("download.png")."' alt='".$lstr['DownloadAlt']."' title='".$lstr['DownloadAlt']."'></a> ";
	echo "<a href='?delete=".$wizard_dir."&nsp=".get_nagios_session_protector_id()."'><img src='".theme_image("delete.png")."' alt='".$lstr['DeleteAlt']."' title='".$lstr['DeleteAlt']."'></a>";
	echo "</td>";
	echo "</tr>\n";

	}

	
function do_download(){
	global $cfg;
	global $lstr;

	// demo mode
	if(in_demo_mode()==true)
		show_wizards(true,$lstr['DemoModeChangeError']);
	
	$wizard_dir=grab_request_var("download");
	if(have_value($wizard_dir)==false)
		show_wizards();
	
	// clean the name
	$wizard_dir=str_replace("..","",$wizard_dir);
	$wizard_dir=str_replace("/","",$wizard_dir);
	$wizard_dir=str_replace("\\","",$wizard_dir);
	
	$id=submit_command(COMMAND_PACKAGE_CONFIGWIZARD,$wizard_dir);
	if($id<=0)
		show_wizards(true,$lstr['ErrorSubmittingCommandText']);
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
					$thefile=$dir."/configwizard-".$wizard_dir.".zip";
					
					//chdir($dir);
					
					$mime_type="";
					header('Content-type: '.$mime_type);
					header("Content-length: " . filesize($thefile)); 
					header('Content-Disposition: attachment; filename="'.basename($thefile).'"');
					readfile($thefile); 					
					}
				else
					show_wizards(true,$lstr['WizardPackagingTimedOutText']);
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
		show_wizards(true,$lstr['DemoModeChangeError']);

	// check session
	check_nagios_session_protector();

	//print_r($request);
	//exit();
	
	$uploaded_file=grab_request_var("uploadedfile");
	//if(have_value($uploaded_file)==false)
	//	show_wizards(true,$lstr['NoWizardUploadedText']);
	
	$target_path="/usr/local/nagiosxi/tmp";
	$target_path.="/";
	$wizard_file=basename($_FILES['uploadedfile']['name']);
	$target_path.="configwizard-".$wizard_file; 
	
	//echo "TEMP NAME: ".$_FILES['uploadedfile']['tmp_name']."<BR>\n";
	//echo "TARGET: ".$target_path."<BR>\n";

	// log it
	send_to_audit_log("User installed configuration wizard '".$wizard_file."'",AUDITLOGTYPE_CHANGE);
	
	if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)){
	
		// fix perms
		chmod($target_path,0550);
		chgrp($target_path,"nagios");

		$id=submit_command(COMMAND_INSTALL_CONFIGWIZARD,$wizard_file);
		if($id<=0)
			show_wizards(true,$lstr['ErrorSubmittingCommandText']);
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
						show_wizards(false,$lstr['WizardInstalledText']);
					else{
						$emsg="";
						if($result_text!="")
							$emsg.=" ".$result_text."";
						show_wizards(true,$lstr['WizardInstallFailedText'].$emsg);
						//show_wizards(true,$lstr['WizardInstallFailedText']);
						}
					exit();
					}
				usleep(500000);
				}
			}
		show_wizards(false,$lstr['WizardScheduledForInstallationText']);
		}
	else{
		// error
		show_wizards(true,$lstr['WizardUploadFailedText']);
		}

	exit();
	}

function do_delete(){
	global $cfg;
	global $lstr;
	global $request;
	
	// demo mode
	if(in_demo_mode()==true)
		show_wizards(true,$lstr['DemoModeChangeError']);

	// check session
	check_nagios_session_protector();

	$dir=grab_request_var("delete","");
		
	// log it
	send_to_audit_log("User deleted configuration wizard '".$dir."'",AUDITLOGTYPE_DELETE);

	// clean the filename
	$dir=str_replace("..","",$dir);
	$dir=str_replace("/","",$dir);
	$dir=str_replace("\\","",$dir);
	
	if($dir=="")
		show_wizards();
		
	$id=submit_command(COMMAND_DELETE_CONFIGWIZARD,$dir);
	if($id<=0)
		show_wizards(true,$lstr['ErrorSubmittingCommandText']);
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
				show_wizards(false,$lstr['WizardDeletedText']);
				exit();
				}
			usleep(500000);
			}
		}
	show_wizards(false,$lstr['WizardScheduledForDeletionText']);
	exit();
	}
?>
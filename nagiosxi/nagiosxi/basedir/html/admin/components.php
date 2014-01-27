<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: components.php 1208 2012-06-09 18:00:37Z egalstad $

//define("SKIPCOMPONENTS",1);  // skips auto-inclusion of components

require_once(dirname(__FILE__).'/../includes/common.inc.php');


// initialization stuff
pre_init();

// start session
init_session();

// grab GET or POST variables 
grab_request_vars();
decode_request_vars();

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
	global $lstr;
	
//	if(in_demo_mode()==true)
		//header("Location: main.php");
		
	if(isset($request["download"]))
		do_download();
	else if (isset($request["upload"]))
		do_upload();
	else if (isset($request["delete"]))
		do_delete();
	else if (isset($request["config"])){
		if(isset($request["cancelButton"]))
			show_components();
		else if(isset($request["update"]))
			do_configure();
		else
			show_configure();
		}
	else if (isset($request["installedok"]))
		show_components(false,$lstr['ComponentInstalledText']);
	else
		show_components();
	
	exit;
	}
	
	
function show_components($error=false,$msg=""){
	global $request;
	global $lstr;
	global $components;


	
	do_page_start(array("page_title"=>$lstr['ManageComponentsPageTitle']),true);

?>

	
	<h1><?php echo $lstr['ManageComponentsPageHeader'];?></h1>
	

<?php
	display_message($error,false,$msg);
?>

	<?php echo $lstr['ManageComponentsPageNotes'];?>
	
	<p>
	<div class="bluebutton" style="width: 150px; float: right;">
	<a href="http://exchange.nagios.org/directory/Addons/Components" target="_blank">Get Components</a>
	</div>
	 You can find additional components for Nagios XI at <a href='http://exchange.nagios.org/directory/Addons/Components' target='_blank'>Nagios Exchange</a>.
	</p>
	
	<br clear="all">
	<br>

	
	<?php 
		//echo "INITIAL COMPONENTS:<BR>";
		//print_r($components);
	?>
	
	<form enctype="multipart/form-data" action="" method="post">
	<input type="hidden" name="upload" value="1">
	<?php echo get_nagios_session_protector();?>
	<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
	<label><?php echo $lstr["UploadNewComponentBoxText"];?>:</label><br>
	<input name="uploadedfile" class="textfield"  type="file" />	<input type="submit" class="submitbutton" value="<?php echo $lstr['UploadComponentButton'];?>" />
	</form>
	
	<br>

	<table class="standardtable">
	<thead> 
	<tr><th><?php echo $lstr['ComponentNameTableHeader'];?></th><th><?php echo $lstr['ComponentTypeTableHeader'];?></th><th><?php echo $lstr['ComponentSettingsTableHeader'];?></th><th><?php echo $lstr['ActionsTableHeader'];?></th></tr>
	</thead>
	<tbody>
	
<?php

	$x=0;

	// reset the array
	/*
	$components=array();
	reset($components);	
	
	$p=dirname(__FILE__)."/../includes/components/";
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
				//echo "COMPONENTS:<BR>";
				//print_r($components);
				
				$component_dir=basename($d);
				
				// display the component
				foreach($components as $name => $carray){
					show_component($component_dir,$name,$carray,$x);
					}
				
				// reset the array
				$components=array();
				reset($components);
				
				$x++;
				}
			}
		}
	*/
	
	foreach($components as $name => $carray){
	
		// component may have just been deleted
		if(!file_exists(dirname(__FILE__)."/../includes/components/".$carray[COMPONENT_DIRECTORY]))
			continue;
			
		show_component($carray[COMPONENT_DIRECTORY],$name,$carray[COMPONENT_ARGS],$x);
		
		$x++;
		}
	
?>
	
	</tbody>
	</table>

<?php

	do_page_end(true);
	exit();
	}
	
	
function show_component($component_dir,$component_name,$carray,$x){
	global $lstr;

	$rowclass="";
	
	if(($x%2)!=0)
		$rowclass.=" odd";
	else
		$rowclass.=" even";

	// grab variables
	$type=grab_array_var($carray,COMPONENT_TYPE,"");
	$title=grab_array_var($carray,COMPONENT_TITLE,"");
	$desc=grab_array_var($carray,COMPONENT_DESCRIPTION,"");
	$version=grab_array_var($carray,COMPONENT_VERSION,"");
	$date=grab_array_var($carray,COMPONENT_DATE,"");
	$author=grab_array_var($carray,COMPONENT_AUTHOR,"");
	$license=grab_array_var($carray,COMPONENT_LICENSE,"");
	$copyright=grab_array_var($carray,COMPONENT_COPYRIGHT,"");
	$homepage=grab_array_var($carray,COMPONENT_HOMEPAGE,"");

	$configfunc=grab_array_var($carray,COMPONENT_CONFIGFUNCTION,"");
	$protected=grab_array_var($carray,COMPONENT_PROTECTED,false);

	echo "<tr class=".$rowclass.">";
	
	$displaytitle=$component_name;
	if($title!="")
		$displaytitle=$title;
	
	echo "<td>";
	echo "<b>".$displaytitle."</b><br>";
	
	if($desc!="")
		echo $desc."<br>";
	
	if($version!="")
		echo "Version: $version ";
	if($date!="")
		echo "Date: $date ";
	if($author!="")
		echo "Author: $author ";
	if($homepage!="")
		echo "Website: <a href='$homepage' target='_blank'>$homepage<a/>";
	
	echo "</td>";
	
	echo "<td>";
	switch($type){
		case "core":
			echo "Core";
			break;
		default:
			echo "User";
			break;
		}
	echo "</td>";
	
	echo "<td>";
	if($configfunc!=""){
		echo "<a href='?config=".$component_dir."'><img src='".theme_image("editsettings.png")."' alt=".$lstr['EditSettingsAlt']."' title='".$lstr['EditSettingsAlt']."'></a>";
		}
	else
		echo "-";
	echo "</td>";
	
	echo "<td>";
	if($protected==false){
		echo "<a href='?download=".$component_dir."'><img src='".theme_image("download.png")."' alt='".$lstr['DownloadAlt']."' title='".$lstr['DownloadAlt']."'></a> ";
		echo "<a href='?delete=".$component_dir."&nsp=".get_nagios_session_protector_id()."'><img src='".theme_image("delete.png")."' alt='".$lstr['DeleteAlt']."' title='".$lstr['DeleteAlt']."'></a>";
		}
	else
		echo "-";
	echo "</td>";
	echo "</tr>\n";

	}

	
function do_download(){
	global $cfg;
	global $lstr;

	// demo mode
	if(in_demo_mode()==true)
		show_components(true,$lstr['DemoModeChangeError']);
	
	$component_dir=grab_request_var("download");
	if(have_value($component_dir)==false)
		show_components();
	
	// clean the name
	$component_dir=str_replace("..","",$component_dir);
	$component_dir=str_replace("/","",$component_dir);
	$component_dir=str_replace("\\","",$component_dir);
	
	$id=submit_command(COMMAND_PACKAGE_COMPONENT,$component_dir);
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
					$thefile=$dir."/component-".$component_dir.".zip";
					
					//chdir($dir);
					
					$mime_type="";
					header('Content-type: '.$mime_type);
					header("Content-length: " . filesize($thefile)); 
					header('Content-Disposition: attachment; filename="'.basename($thefile).'"');
					readfile($thefile); 					
					}
				else
					show_components(true,$lstr['ComponentPackagingTimedOutText']);
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
	
	//print_r($request);
	//exit();

	// demo mode
	if(in_demo_mode()==true)
		show_components(true,$lstr['DemoModeChangeError']);
	
	// check session
	check_nagios_session_protector();

	$uploaded_file=grab_request_var("uploadedfile");

	
	$target_path="/usr/local/nagiosxi/tmp";
	$target_path.="/";
	$component_file=basename($_FILES['uploadedfile']['name']);
	$target_path.="component-".$component_file; 
	
	// log it
	send_to_audit_log("User installed component '".$component_file."'",AUDITLOGTYPE_CHANGE);

	//echo "TEMP NAME: ".$_FILES['uploadedfile']['tmp_name']."<BR>\n";
	//echo "TARGET: ".$target_path."<BR>\n";

	if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)){

		// fix perms
		chmod($target_path,0550);
		chgrp($target_path,"nagios");

		$id=submit_command(COMMAND_INSTALL_COMPONENT,$component_file);
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
					if($result_code==0){
						// redirect to show install message (so the list will include the new component)
						header("Location: ?installedok");
						exit();
						}
					else{
						//print_r($xml);
						//echo "<BR>RESULT TEXT=$result_text<BR>";
						$emsg="";
						if($result_text!="")
							$emsg.=" ".$result_text."";
						show_components(true,$lstr['ComponentInstallFailedText'].$emsg);
						//show_components(true,"ERROR=".$emsg);
						}
					exit();
					}
				usleep(500000);
				}
			}
		show_components(false,$lstr['ComponentScheduledForInstallationText']);
		}
	else{
		// error
		show_components(true,$lstr['ComponentUploadFailedText']);
		}

	exit();
	}

function do_delete(){
	global $cfg;
	global $lstr;
	global $request;
	
	// demo mode
	if(in_demo_mode()==true)
		show_components(true,$lstr['DemoModeChangeError']);

	// check session
	check_nagios_session_protector();

	$dir=grab_request_var("delete","");
		
	// clean the filename
	$dir=str_replace("..","",$dir);
	$dir=str_replace("/","",$dir);
	$dir=str_replace("\\","",$dir);
	
	if($dir=="")
		show_components();
		
	// log it
	send_to_audit_log("User deleted component '".$dir."'",AUDITLOGTYPE_DELETE);

	$id=submit_command(COMMAND_DELETE_COMPONENT,$dir);
	if($id<=0)
		show_components(true,$lstr['ErrorSubmittingCommandText']);
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
				show_components(false,$lstr['ComponentDeletedText']);
				exit();
				}
			usleep(500000);
			}
		}
	show_components(false,$lstr['ComponentScheduledForDeletionText']);
	exit();
	}
	
	
function show_configure($error=false,$msg=""){
	global $request;
	global $lstr;
	global $components;
	
	$dir=grab_request_var("config","");
		
	// clean the filename
	$dir=str_replace("..","",$dir);
	$dir=str_replace("/","",$dir);
	$dir=str_replace("\\","",$dir);
	
	$component_name=$dir;
	
	if($component_name=="")
		show_components();
		
	$component=$components[$component_name];

	
	$title=grab_array_var($component[COMPONENT_ARGS],COMPONENT_TITLE,"");
	
	do_page_start(array("page_title"=>$lstr['ConfigureComponentPageTitle']." - ".$title),true);

?>

	
	<h1><?php echo $title;?></h1>
	

<?php
	display_message($error,false,$msg);
?>

	<form method="post" action="">
	<input type="hidden" name="config" value="<?php echo encode_form_val($component_name);?>">
	<input type="hidden" name="update" value="1">
	<?php echo get_nagios_session_protector();?>
	
<?php
	// get component output
	$configfunc=grab_array_var($component[COMPONENT_ARGS],COMPONENT_CONFIGFUNCTION,"");
	if($configfunc!=""){
		$inargs=$request;
		$outargs=array();
		$output=$configfunc(COMPONENT_CONFIGMODE_GETSETTINGSHTML,$inargs,$outargs,$result);
		echo $output;
		}
	else
		echo "Component function does not exist.";
	
?>
	
	<div id="formButtons">
	<input type="submit" class="submitbutton" name="submitButton" value="<?php echo $lstr['ApplySettingsButton'];?>"/>
	<input type="submit" class="submitbutton" name="cancelButton" value="<?php echo $lstr['CancelButton'];?>"/>

	<form>

<?php
	}

function do_configure($error=false,$msg=""){
	global $request;
	global $lstr;
	global $components;
	
	// demo mode
	if(in_demo_mode()==true)
		show_configure(true,$lstr['DemoModeChangeError']);

	// check session
	check_nagios_session_protector();

	$dir=grab_request_var("config","");
		
	// clean the filename
	$dir=str_replace("..","",$dir);
	$dir=str_replace("/","",$dir);
	$dir=str_replace("\\","",$dir);
	
	$component_name=$dir;
	
	if($component_name=="")
		show_components();
		
	$component=$components[$component_name];

	// log it
	send_to_audit_log("User configured component '".$component."'",AUDITLOGTYPE_CHANGE);
	

	// save component settings
	$configfunc=grab_array_var($component[COMPONENT_ARGS],COMPONENT_CONFIGFUNCTION,"");
	if($configfunc!=""){
	
		// pass request vars to component
		$inargs=$request;
		
		// initialize return values
		$outargs=array("test"=> "test2");
		$result=0;
		
		// tell component to save settings
		$output=$configfunc(COMPONENT_CONFIGMODE_SAVESETTINGS,$inargs,$outargs,$result);
		
		// handle errors thrown by component
		if($result!=0)
			show_configure(true,$outargs[COMPONENT_ERROR_MESSAGES]);
			
		// handle success
		else{
			$msg=$lstr['ComponentSettingsUpdatedText'];
			if(array_key_exists(COMPONENT_INFO_MESSAGES,$outargs))
				$msg=$outargs[COMPONENT_INFO_MESSAGES];
			show_configure(false,$msg);
			}
		}
	else
		echo "Component function does not exist.";
	
	exit();
	}

?>
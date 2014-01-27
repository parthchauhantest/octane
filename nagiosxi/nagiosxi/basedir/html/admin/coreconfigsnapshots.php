<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: coreconfigsnapshots.php 1300 2012-07-17 19:47:23Z swilkerson $

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
	
//	if(in_demo_mode()==true)
	//	header("Location: main.php");
		
	if(isset($request["download"]))
		do_download();
	else if(isset($request["view"]))
		do_view();
	else if (isset($request["delete"]))
		do_delete();
	else if (isset($request["restore"]))
		do_restore();
	else
		show_log();
	
	exit;
	}
	
	
function show_log($error=false,$msg=""){
	global $request;
	global $lstr;
	

	$snapshots=get_nagioscore_config_snapshots();
	

	do_page_start(array("page_title"=>$lstr['CoreConfigSnapshotsPageTitle']),true);

?>

	
	<h1><?php echo $lstr['CoreConfigSnapshotsPageHeader'];?></h1>
	

<?php
	display_message($error,false,$msg);
?>

	<?php echo $lstr['CoreConfigSnapshotsPageNotes'];?>
	
	<br><br>

	
	<?php 
		//print_r($snapshots);
	?>
    <script type="text/javascript">
    function verify()
{
    var answer = confirm("Are you sure you want to restore the NagiosQL database?")
    if (answer){
        $("#childcontentthrobber").css("visibility","visible");
        return true;
    }
    
    return false;  
}  
    </script>
	<table class="standardtable">
	<thead> 
	<tr><th><?php echo $lstr['DateTableHeader'];?></th><th><?php echo $lstr['SnapshotResultTableHeader'];?></th><th><?php echo $lstr['FileTableHeader'];?></th><th><?php echo $lstr['ActionsTableHeader'];?></th></tr>
	</thead>
	<tbody>
	
<?php
	$x=0;
	foreach($snapshots as $snapshot){
	
		$x++;
	
		$resultstring="Config Ok";
		$rowclass="";
		$qstring="result=ok";
		if($snapshot["error"]==true){
			$resultstring="Config Error";
			$rowclass="alert";
			$qstring="result=error";
			}
			
		if(($x%2)!=0)
			$rowclass.=" odd";
		else
			$rowclass.=" even";
	
		echo "<tr class=".$rowclass.">";
		echo "<td>".$snapshot["date"]."</td>";
		echo "<td>".$resultstring."</td>";
		echo "<td>".$snapshot["file"]."</td>";
		echo "<td>";
		echo "<a href='?download=".$snapshot["timestamp"]."&".$qstring."'><img src='".theme_image("download.png")."' alt='".$lstr['DownloadAlt']."' title='".$lstr['DownloadAlt']."'></a> ";
		echo "<a href='?view=".$snapshot["timestamp"]."&".$qstring."'><img src='".theme_image("detail.png")."' alt='".$lstr['ViewOutputAlt']."' title='".$lstr['ViewOutputAlt']."'></a>";
		if($snapshot["error"]==true){
			echo "<a href='?delete=".$snapshot["timestamp"]."'><img src='".theme_image("delete.png")."' alt='".$lstr['DeleteAlt']."' title='".$lstr['DeleteAlt']."'></a>";
			}
		else{
		if(use_2012_features()==true)
            echo "<a href='?restore=".$snapshot["timestamp"]."'><img src='".theme_image("reload.png")."' alt='".$lstr['RestoreAlt']."' title='".$lstr['RestoreAlt']."'  onclick='return verify();'></a>";
			}
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
	$ts=grab_request_var("download","");
	
	// base checkpoints dir
	$dir=$cfg['nom_checkpoints_dir'];
	if($result=="error")
		$dir.="errors/";
		
	// clean the timestamp
	$ts=str_replace(".","",$ts);
	$ts=str_replace("/","",$ts);
	$ts=str_replace("\\","",$ts);
	
	$thefile=$dir.$ts.".tar.gz";
	
	header('Content-type: application/x-gzip');
	header("Content-length: " . filesize($thefile)); 
	header('Content-Disposition: attachment; filename="'.basename($thefile).'"');
	readfile($thefile); 
	exit();
	}
	
function do_view(){
	global $cfg;
	
	$result=grab_request_var("result","ok");
	$ts=grab_request_var("view","");
	
	// base checkpoints dir
	$dir=$cfg['nom_checkpoints_dir'];
	if($result=="error")
		$dir.="errors/";
		
	// clean the timestamp
	$ts=str_replace(".","",$ts);
	$ts=str_replace("/","",$ts);
	$ts=str_replace("\\","",$ts);
	
	$thefile=$dir.$ts.".txt";
	
	header('Content-type: application/text');
	header("Content-length: " . filesize($thefile)); 
	header('Content-Disposition: attachment; filename="'.basename($thefile).'"');
	readfile($thefile); 
	exit();
	}

function do_delete(){
	global $cfg;
	global $lstr;
	global $request;
	
	// demo mode
	if(in_demo_mode()==true)
		show_log(true,$lstr['DemoModeChangeError']);

	$ts=grab_request_var("delete","");
		
	// log it
	send_to_audit_log("User deleted core config snapsnot '".$ts."'",AUDITLOGTYPE_DELETE);

	// clean the filename
	$ts=str_replace("..","",$ts);
	$ts=str_replace("/","",$ts);
	$ts=str_replace("\\","",$ts);
	
	if($ts=="")
		show_log();
		
	$id=submit_command(COMMAND_DELETE_CONFIGSNAPSHOT,$ts);
	if($id<=0)
		show_log(true,$lstr['ErrorSubmittingCommandText']);
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
				show_log(false,$lstr['ConfigSnapshotDeletedText']);
				exit();
				}
			usleep(500000);
			}
		}
	show_log(false,$lstr['ConfigSnapshotScheduledForDeletionText']);
	exit();
	}
	
function do_restore(){
	global $cfg;
	global $lstr;
	global $request;
	
	// demo mode
	if(in_demo_mode()==true)
		show_log(true,$lstr['DemoModeChangeError']);

	$ts=grab_request_var("restore","");
	
    $baseurl=get_base_url();
	// log it
	send_to_audit_log("User restored system to config snapsnot '".$ts."'",AUDITLOGTYPE_CHANGE);

	// clean the filename
	$ts=str_replace("..","",$ts);
	$ts=str_replace("/","",$ts);
	$ts=str_replace("\\","",$ts);
	
	if($ts=="")
		show_log();
	$dir=$cfg['nom_checkpoints_dir'].'/../nagiosxi';
	if (!file_exists($dir."/".$ts."_nagiosql.sql.gz")){
        show_log(true,"This snapshot doesn't exist");
        exit();
    }
	$id=submit_command(COMMAND_RESTORE_NAGIOSQL_SNAPSHOT,$ts." restore");
	if($id<=0)
		show_log(true,$lstr['ErrorSubmittingCommandText']);
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
				show_log(false,"NagiosQL Snapshot Restored.</br><strong><a href='".nagioscorecfg_get_component_url(true)."applyconfig.php?cmd=confirm'>".$lstr['ApplyConfigText']."</a></strong> &nbsp;<a href='".$baseurl."config/nagioscorecfg' target='_top'>View Config</a>");
				exit();
				}
			usleep(500000);
			}
		}
	show_log(false,$lstr['ConfigSnapshotScheduledForRestoreText']);
	exit();
	}
	

?>
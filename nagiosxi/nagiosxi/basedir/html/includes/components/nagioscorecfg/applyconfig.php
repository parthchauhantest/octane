<?php
// Nagios Core Config Sub-Component Functions
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: applyconfig.php 1164 2012-05-08 15:25:21Z egalstad $

include_once(dirname(__FILE__).'/../componenthelper.inc.php');


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

if(is_authorized_to_configure_objects()==false){
	echo "Not authorized";
	exit();
	}

route_request();

function route_request(){

	$cmd=grab_request_var("cmd","");
	
	check_perms();
	
	switch($cmd){
		case "confirm":
			nagioscorecfg_confirm_apply();
			break;
		default:
			nagioscorecfg_apply_config();
			break;
		}
	}
	
function check_perms(){

	nagiosql_check_setuid_files($scripts_ok,$goodscripts,$badscripts);
	nagiosql_check_file_perms($config_ok,$goodfiles,$badfiles);
	
	if($scripts_ok==true && $config_ok==true)
		return;
	
	/*
	echo "RESULT=$result<BR>";
	echo "GOODFILES:<BR>";
	print_r($goodfiles);
	echo "BADFILES:<BR>";
	print_r($badfiles);
	*/


	do_page_start(array("page_title"=>"Permissions Problem"),true);
	
?>

<h1>Permissions Problem</h1>

<p>
<img src="<?php echo theme_image("error_small.png");?>"> <b>Error:</b> The permissions on one or more configuration files or scripts appear to be incorrect.  This will prevent your configuration from being applied properly.
</p>

<p>
<a href="<?php echo get_base_url()."/admin/?xiwindow=configpermscheck.php";?>" target="_top"><b>Click here to resolve this problem</b></a>.
</p>

<?php
	do_page_end(true);	
	
	exit();
	}
	
function nagioscorecfg_apply_config(){
	global $lstr;
	
	// log it
	send_to_audit_log("User applied a new monitoring configuration",AUDITLOGTYPE_INFO);

	$return_url=grab_request_var("return","");

	do_page_start(array("page_title"=>$lstr['ApplyingNagiosCoreConfigPageTitle']),true);
	
?>


<h1><?php echo $lstr['ApplyingNagiosCoreConfigPageTitle'];?></h1>




<ul class="commandresult">
<?php
	$error=false;
	$id=submit_command(COMMAND_NAGIOSCORE_APPLYCONFIG);
	//echo "COMMAND ID: $id<BR>";
	if($id>0){
		echo "<li class='commandresultok'>"."Command submitted for processing..."."</li>\n";
	
		echo "<li class='commandresultwaiting' id='commandwaiting'>"."Waiting for configuration verification...</li>"."</li>\n";
		}
	else{
		echo "<li class='commandresulterror'>"."An error occurred during command submission.  If this problem persists, contact your Nagios administrator.</li>\n";
		$error=true;
		}
?>
</ul>



<div id="commandsuccesscontent" style="visibility: hidden;">
<?php
	echo "<p>\n";
	echo $lstr['ApplyConfigSuccessMessage'];
	if(is_admin()==true){
		echo "<p><a href='".get_base_url()."admin/?xiwindow=coreconfigsnapshots.php' target='_top'>".$lstr['ViewConfigSuccessSnapshotMessage']."</a></p>";
		}
	if($return_url!=""){
		echo "<p>\n";
		echo "<form method='get' action='".htmlentities($return_url)."'><input type='submit' name='continue' value='".$lstr['ContinueText']."'></form>\n";
		}
?>
</div>
<div id="commanderrorcontent" style="visibility: hidden;">
<?php
		echo "<p>\n";
		echo $lstr['ApplyConfigErrorMessage'];
		echo "<p>\n";
		if(is_admin()==true){
			echo "<p><a href='".get_base_url()."admin/?xiwindow=coreconfigsnapshots.php' target='_top'>".$lstr['ViewConfigErrorSnapshotMessage']."</a></p>";
			}
		echo "<form method='post' action=''><input type='hidden' name='cmd' value=''><input type='submit' name='continue' value='".$lstr['TryAgainText']."'></form>\n";
?>
</div>

<script type="text/javascript">

get_apply_config_result(<?php echo $id;?>);

function get_apply_config_result(command_id){

	$(this).everyTime(1 * 1000, "commandstatustimer", function(i) {
	
		$(".commandresultwaiting").append(".");

		var csdata=get_ajax_data("getcommandstatus",command_id);
		eval('var csobj='+csdata);
		if(csobj.status_code==2){
			if(csobj.result_code==0){
				$('.commandresultwaiting').each(function(){
					$(this).removeClass("commandresultwaiting");
					$(this).addClass("commandresultok");
					});
				$('#commandsuccesscontent').each(function(){
					$(this).css("visibility","visible");
					});
				$('ul.commandresult').append("<li class='commandresultok'>Configuration applied successfully.</li>");
				}
			else{
				
				$('.commandresultwaiting').each(function(){
					$(this).removeClass("commandresultwaiting");
					$(this).addClass("commandresulterror");
					});
				$('#commandsuccesscontent').each(function(){
					$(this).css("display","none")
					});
				$('#commanderrorcontent').each(function(){
					$(this).css("visibility","visible")
					});
					
					//display message based on error code 					
					/* exit codes:
					#       1       config verification failed
					#       2       nagiosql login failed
					#       3       nagiosql import failed
					#       4       reset_config_perms failed
					#       5       nagiosql_exportall.php failed (write configs failed)
					#       6       /etc/init.d/nagios restart failed
					#       7       db_connect failed
					*/ 
					
					var returnCode = csobj.result_code; 
					switch(returnCode)
					{
						case '7': //db connect failed 
						$('ul.commandresult').append("<li class='commandresulterror'>Failed to connect to the database.</li>");
						break; 
						case '6': //nagios restart failed 
						$('ul.commandresult').append("<li class='commandresulterror'>Nagios restart command failed.</li>");
						break
						case '5': //write configs failed 
						$('ul.commandresult').append("<li class='commandresulterror'>Configurations failed to write to file.</li>");
						break;
						case '4': //reset config perms failed 
						$('ul.commandresult').append("<li class='commandresulterror'>Reset config permissions failed.</li>");
						break; 
						case '3':  //nagiosql import (wizard import) failed 
						$('ul.commandresult').append("<li class='commandresulterror'>Configuration import failed.</li>");
						break;
						case '2':  //nagiosql login failed 
						$('ul.commandresult').append("<li class='commandresulterror'>Backend login to the Core Config Manager failed.</li>");
						break; 					
						case '1': //config verification failed 
							$('ul.commandresult').append("<li class='commandresulterror'>Configuration verification failed.</li>");
						break;	
						default:
						$('ul.commandresult').append("<li class='commandresulterror'>There was an error while attempting to apply configuration. Error code: "+returnCode+".</li>");
						break; 
					}	
				}
			$(this).stopTime("commandstatustimer");
			}
		});
		
	}
</script>

<?php
	do_page_end(true);
	}
	
	

function nagioscorecfg_confirm_apply(){
	global $lstr;
	
	do_page_start(array("page_title"=>$lstr['ApplyNagiosCoreConfigPageTitle']),true);
	
	//print_r($dashlets);
?>


<h1><?php echo $lstr['ApplyNagiosCoreConfigPageTitle'];?></h1>


<p>
<?php echo $lstr['ApplyNagiosCoreConfigMessage'];?>
</p>

<p>
<?php
	echo "<form method='post' action=''><input type='hidden' name='cmd' value=''><input type='submit' name='continue' value='".$lstr['ApplyConfigText']."'></form>\n";
?>

<?php
	do_page_end(true);
	}

?>
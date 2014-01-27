<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: mobilecarriers.php 1169 2012-05-08 20:07:43Z egalstad $

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

	if(isset($request['update']))
		do_update_settings();
	if(isset($request['resetdefaults']))
		do_reset_defaults();
	else
		show_settings();
	exit;
	}
	
	
function show_settings($error=false,$msg=""){
	global $request;
	global $lstr;
	
	// get defaults
	$mpinfo=get_mobile_provider_info();
			
	// get variables submitted to us
	$mp=grab_request_var("mp");
	if(is_array($mp))
		$mpinfo=$mp;

	// pre-process...
	foreach($mpinfo as $mpid => $mparr){
		$id=grab_array_var($mparr,"id");
		$description=grab_array_var($mparr,"description");
		$format=grab_array_var($mparr,"format");
		$delete=grab_array_var($mparr,"delete");
		
		// remove empty lines
		if($id=="" && $description=="" && $format=="")
			unset($mpinfo[$mpid]);
		// remove lines user wants deleted
		if($delete!=""){
			unset($mpinfo[$mpid]);
			continue;
			}
		}		
		
	do_page_start(array("page_title"=>$lstr['MobileCarriersPageTitle']),true);
?>

	
	<h1><?php echo $lstr['MobileCarriersPageTitle'];?></h1>
	

<?php
	display_message($error,false,$msg);
?>

	<p>
	<?php echo $lstr['MobileCarriersPageMessage'];?>
	</p>

	<form id="manageMobileCarriersForm" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">

	
	<input type="hidden" name="update" value="1">
	<?php echo get_nagios_session_protector();?>
	
	<table>
	<thead>
	<tr><th>#</th><th>Unique Id</th><th>Description</th><th>Email-To-Text Address Format</th><th>Delete</th></tr>
	</thead>
	
	<tbody>
<?php
	$x=0;
	foreach($mpinfo as $mparr){
		echo "<tr>";
		echo "<td>".($x+1)."</td>";
		echo "<td><input type='text' name='mp[".$x."][id]' value='".htmlentities($mparr["id"])."'></td>";
		echo "<td><input type='text' name='mp[".$x."][description]' value='".htmlentities($mparr["description"])."'></td>";
		echo "<td><input type='text' name='mp[".$x."][format]' value='".htmlentities($mparr["format"])."' size='50'></td>";
		echo "<td><input type='checkbox' name='mp[".$x."][delete]'></td>";
		echo "</tr>";
		$x++;
		}
	for($y=0;$y<2;$y++){
		echo "<tr>";
		echo "<td>".($x+1)."</td>";
		echo "<td><input type='text' name='mp[".$x."][id]' value=''></td>";
		echo "<td><input type='text' name='mp[".$x."][description]' value=''></td>";
		echo "<td><input type='text' name='mp[".$x."][format]' value='' size='50'></td>";
		echo "</tr>";
		$x++;
		}
?>
	</tbody>
	</table>
	
	<div id="formButtons">
	<input type="submit" class="submitbutton" name="updateButton" value="<?php echo $lstr['UpdateSettingsButton'];?>" id="updateButton">
	<input type="submit" class="submitbutton" name="cancelButton" value="<?php echo $lstr['CancelButton'];?>" id="cancelButton">
	</div>
	

	<!--</fieldset>-->
	</form>
	
	<p><a href="?resetdefaults">Restore defaults</a></p>


<?php

	do_page_end(true);
	exit();
	}


function do_update_settings(){
	global $request;
	global $lstr;
	
	// user pressed the cancel button
	if(isset($request["cancelButton"]))
		header("Location: main.php");
	
	// check session
	check_nagios_session_protector();

	$errmsg=array();
	$errors=0;
	
	// defaults
	

	// get variables submitted to us
	$mp=grab_request_var("mp");

	// make sure we have requirements
	if(in_demo_mode()==true)
		$errmsg[$errors++]=$lstr['DemoModeChangeError'];
	if(!is_array($mp))
		$errmsg[$errors++]="Could not process request";
	foreach($mp as $mpid => $mparr){
		$id=grab_array_var($mparr,"id");
		$description=grab_array_var($mparr,"description");
		$format=grab_array_var($mparr,"format");
		$delete=grab_array_var($mparr,"delete");
		
		// remove empty lines
		if($id=="" && $description=="" && $format==""){
			unset($mp[$mpid]);
			continue;
			}
		// remove lines user wants deleted
		if($delete!=""){
			unset($mp[$mpid]);
			continue;
			}
			
		if($id=="")
			$errmsg[$errors++]="Unique ID missing on line #".$mpid;
		if($description=="")
			$errmsg[$errors++]="Description missing on line #".$mpid;
		if($format=="")
			$errmsg[$errors++]="Format missing on line #".$mpid;
		}
		
	// update settings
	if(in_demo_mode()==false){
		set_option("mobile_provider_info",serialize($mp));
		}
		
	// handle errors
	if($errors>0)
		show_settings(true,$errmsg);
		
	// log it
	send_to_audit_log("User updated global mobile carrier settings",AUDITLOGTYPE_CHANGE);

	// success!
	show_settings(false,$lstr['MobileCarriersUpdatedText']);
	}
		
		
function do_reset_defaults(){
	if(in_demo_mode()==false){
		$mpinfo=get_default_mobile_provider_info();
		set_option("mobile_provider_info",serialize($mpinfo));
		}
	show_settings(false,"Defaults restored");
	}
	


?>
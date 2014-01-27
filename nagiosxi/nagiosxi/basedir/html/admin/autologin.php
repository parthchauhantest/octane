<?php
//
// Copyright (c) 2011 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: autologin.php 1172 2012-05-08 21:14:42Z egalstad $

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
		
	if(isset($request['update']))
		do_update_options();
	else
		show_options();
	exit;
	}
	
	
function show_options($error=false,$msg=""){
	global $request;
	global $lstr;
	
	// get options
	//$url=grab_request_var("url",$url);

	do_page_start(array("page_title"=>$lstr['AutoLoginPageTitle']),true);

?>
<?php

	$opt_s=get_option("autologin_options");
	if($opt_s=="")
		$opts=array(
			"autologin_enabled" => 0,
			"autologin_username" => "",
			"autologin_password" => "",
			);
	else
		$opts=unserialize($opt_s);		
		
	$autologin_enabled=checkbox_binary(grab_request_var("autologin_enabled",grab_array_var($opts,"autologin_enabled")));
	$autologin_username=grab_request_var("autologin_username",grab_array_var($opts,"autologin_username"));
	$autologin_password=grab_request_var("autologin_password","");
?>

	
	<h1><?php echo $lstr['AutoLoginPageHeader'];?></h1>
	

<?php
	display_message($error,false,$msg);
?>

	<?php echo $lstr['AutoLoginPageNotes'];?>


	<form id="manageOptionsForm" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">

	
	<input type="hidden" name="options" value="1">
	<?php echo get_nagios_session_protector();?>
	<input type="hidden" name="update" value="1">
	
	<div class="sectionTitle">Auto-Login Settings</div>
	
	<table>

	<tr>
	<td>
	<label>Enable Auto-Login:</label><br class="nobr" />	
	</td>
	<td>
	<input type="checkbox" class="checkbox" name="autologin_enabled" <?php echo is_checked($autologin_enabled,1);?>>
	<br>
	</td>

	<tr>
	<td>
	<label>Account:</label><br class="nobr" />	
	</td>
	<td>
	<select name="autologin_username">
	<option value=""></option>
<?php
	$users=get_xml_users();
	//print_r($users);
	foreach($users->user as $u){
		$username=$u->username;
		$name=$u->name;
		echo "<option value='".encode_form_val($username)."' ".is_selected($autologin_username,$username).">".$username." (".$name.")</option>";
		}
?>
	</select>
	<!--
	<input type="text" size="15" name="autologin_username" value="<?php echo encode_form_val($autologin_username);?>" class="textfield" />
	//-->
	<br>
	</td>

	<!--
	<tr>
	<td>
	<label>Password:</label><br class="nobr" />	
	</td>
	<td>
	<input type="password" size="15" name="autologin_password" value="<?php echo encode_form_val($autologin_password);?>" class="textfield" />
	<br>
	</td>
	<tr>
	//-->
		

	</table>


	<div id="formButtons">
	<input type="submit" class="submitbutton" name="updateButton" value="<?php echo $lstr['UpdateSettingsButton'];?>" id="updateButton">
	<input type="submit" class="submitbutton" name="cancelButton" value="<?php echo $lstr['CancelButton'];?>" id="cancelButton">
	</div>
	

	<!--</fieldset>-->
	</form>
	
	


<?php

	do_page_end(true);
	exit();
	}


function do_update_options(){
	global $request;
	global $lstr;
	
	// user pressed the cancel button
	if(isset($request["cancelButton"]))
		header("Location: main.php");
	
		
	// check session
	check_nagios_session_protector();

	$errmsg=array();
	$errors=0;

	// get values
	$autologin_enabled=checkbox_binary(grab_request_var("autologin_enabled"));
	$autologin_username=grab_request_var("autologin_username");
	$autologin_password=grab_request_var("autologin_password");
	

	// make sure we have requirements
	if(in_demo_mode()==true)
		$errmsg[$errors++]=$lstr['DemoModeChangeError'];
	if($autologin_enabled==1){
		if(have_value($autologin_username)==false)
			$errmsg[$errors++]="No account specified";
		else if(is_valid_user($autologin_username)==false)
			$errmsg[$errors++]="Invalid user account";
		//if(have_value($autologin_password)==false)
		//	$errmsg[$errors++]="No password specified";
		}

		
	// handle errors
	if($errors>0)
		show_options(true,$errmsg);
		
		
	// original options (for auditing)
	$opts_s=get_option("autologin_options");
	$opts=unserialize($opts_s);
	if(is_array($opts))
		$old_enabled=$opts["autologin_enabled"];
	else
		$old_enabled=0;

	// save options
	$opts=array(
		"autologin_enabled" => $autologin_enabled,
		"autologin_username" => $autologin_username,
		"autologin_password" => $autologin_password,
		);
	$opts_s=serialize($opts);
	set_option("autologin_options",$opts_s);
		
	// log it
	if($autologin_enabled==0)
		send_to_audit_log("User disabled auto-login functionality",AUDITLOGTYPE_SECURITY);
	else if($old_enabled!=$autologin_enabled)
		send_to_audit_log("User enabled auto-login functionality.  Auto-login user='".$autologin_username."'",AUDITLOGTYPE_SECURITY);
	else
		send_to_audit_log("User updated auto-login functionality.  Auto-login user='".$autologin_username."'",AUDITLOGTYPE_SECURITY);

	// success!
	show_options(false,$lstr['OptionsUpdatedText']);
	}
		
		

function draw_menu(){
	//$m=get_admin_menu_items();
	//draw_menu_items($m);
	}
	
	

?>
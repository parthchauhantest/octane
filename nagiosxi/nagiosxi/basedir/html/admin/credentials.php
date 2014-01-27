<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: credentials.php 1172 2012-05-08 21:14:42Z egalstad $

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
		
	// don't cache credentials, etc.
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Cache-Control: no-cache");
	header("Pragma: no-cache");

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

	do_page_start(array("page_title"=>$lstr['SecurityCredentialsPageTitle']),true);

?>
<?php

	$opt=get_option("security_credentials_updated");
	
	if($opt==1)
		$config_admin_password="";
	else
		$config_admin_password=random_string(6);

	$old_subsystem_ticket=get_subsystem_ticket();
	if($opt==1)
		$subsystem_ticket=$old_subsystem_ticket;
	else
	$subsystem_ticket=random_string(12);
	
	if($opt==1)
		$config_backend_password=get_component_credential("nagiosql","password");
	else
		$config_backend_password=random_string(6);

	$old_nagioscore_backend_password=get_component_credential("pnp","password");
	if($opt==1)
		$nagioscore_backend_password=$old_nagioscore_backend_password;
	else
		$nagioscore_backend_password=random_string(8);

	// demo mode
	if(in_demo_mode()==true){
		$config_admin_password="********";
		$old_subsystem_ticket="********";
		$subsystem_ticket="********";
		$config_backend_password="********";
		$nagioscore_backend_password="********";
		}
		

		
?>


	
	<h1><?php echo $lstr['SecurityCredentialsPageTitle'];?></h1>
	

<?php
	display_message($error,false,$msg);
?>

	<?php echo $lstr['SecurityCredentialsPageNotes'];?>


	<form id="manageOptionsForm" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">

	
	<input type="hidden" name="options" value="1">
	<?php echo get_nagios_session_protector();?>
	<input type="hidden" name="update" value="1">
	
	<div class="sectionTitle"><?php echo $lstr['ComponentCredentialsSectionTitle']?></div>
	
	<?php echo $lstr['ComponentCredentialsNote'];?>
	<br>
	<br>

	<table>

	<tr>
	<td>
	<label><?php echo $lstr['ConfigManagerAdminPasswordText'];?>:</label><br class="nobr" />	
	</td>
	<td>
	<input type="password" size="15" name="config_admin_password" id="config_admin_password" value="<?php echo encode_form_val($config_admin_password);?>" class="textfield" />
	<br class="nobr" />
	</td>
	<td>
	<a href="<?php echo get_base_url()."config/nagioscorecfg";?>" target="_blank">Open Config Manager</a><br>
	<?php echo $lstr['ConfigManagerAdminUsernameText'];?>: nagiosadmin
	</td>
	<tr>
		

	</table>

	<div class="sectionTitle"><?php echo $lstr['SubsystemCredentialsSectionTitle'];?></div>
	
	<?php echo $lstr['SubsystemCredentialsNote'];?>
	
	<table>

	<tr>
	<td>
	<label><?php echo $lstr['SubsystemTicketText'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="password" size="15" name="subsystem_ticket" id="subsystem_ticket" value="<?php echo $subsystem_ticket;?>" class="textfield" /><!-- (<?php echo $lstr['CurrentText'];?>: <?php echo $old_subsystem_ticket;?>)--><br class="nobr" />
	</td>
	<tr>
		
	<tr>
	<td>
	<label><?php echo $lstr['ConfigManagerBackendPasswordText'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="password" size="15" name="config_backend_password" id="config_backend_password" value="<?php echo encode_form_val($config_backend_password);?>" class="textfield" /><br class="nobr" />
	</td>
	<tr>
		
	<tr>
	<td>
	<label><?php echo $lstr['NagiosCoreBackendPasswordText'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="password" size="15" name="nagioscore_backend_password" id="nagioscore_backend_password" value="<?php echo encode_form_val($nagioscore_backend_password);?>" class="textfield" /><br class="nobr" />
	</td>
	<tr>
		

		
	</table>



	<div id="formButtons">
	<input type="submit" class="submitbutton" name="updateButton" value="<?php echo $lstr['UpdateCredentialsButton'];?>" id="updateButton">
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
	$subsystem_ticket=grab_request_var("subsystem_ticket");
	$config_backend_password=grab_request_var("config_backend_password");
	$nagioscore_backend_password=grab_request_var("nagioscore_backend_password");
	$config_admin_password=grab_request_var("config_admin_password");

	// make sure we have requirements
	if(in_demo_mode()==true)
		$errmsg[$errors++]=$lstr['DemoModeChangeError'];
	if(have_value($subsystem_ticket)==false)
		$errmsg[$errors++]=$lstr["NoSubsystemTicketError"];
	if(have_value($config_backend_password)==false)
		$errmsg[$errors++]=$lstr["NoConfigBackendPasswordError"];
	if(have_value($nagioscore_backend_password)==false)
		$errmsg[$errors++]=$lstr["NoNagiosCoreBackendPasswordError"];

		
	// handle errors
	if($errors>0)
		show_options(true,$errmsg);
		
		
	// UPDATE PASSWORDS/TOKENS...
	
	// config manager (naigosql) admin password
	if(have_value($config_admin_password)==true){
	
		nagiosql_update_user_password("nagiosadmin",$config_admin_password);
		
		// log it
		send_to_audit_log("User changed Core Config Manager admin password",AUDITLOGTYPE_SECURITY);
		}
	
	// backend subsystem ticket
	set_option("subsystem_ticket",$subsystem_ticket);
	
	// config manager (nagiosql) backend password
	set_component_credential("nagiosql","password",$config_backend_password);
	$nagiosql_username=get_component_credential("nagiosql","username");
	nagiosql_update_user_password($nagiosql_username,$config_backend_password);
	
	// nagios core backend password (used by pnp)
	$pnp_username=get_component_credential("pnp","username");
	set_component_credential("pnp","password",$nagioscore_backend_password);
	$args=array(
		"username" => $pnp_username,
		"password" => $nagioscore_backend_password
		);
	submit_command(COMMAND_NAGIOSXI_SET_HTACCESS,serialize($args));
	
	
	
	// mark that security credentials were updates
	set_option("security_credentials_updated",1);
		
	// log it
	send_to_audit_log("User updated system security credentials",AUDITLOGTYPE_SECURITY);
	
	// success!
	show_options(false,$lstr['SecurityCredentialsUpdatedText']);
	}
		
		

function draw_menu(){
	//$m=get_admin_menu_items();
	//draw_menu_items($m);
	}
	
	

?>
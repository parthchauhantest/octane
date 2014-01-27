<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: install.php 295 2010-08-25 02:19:54Z egalstad $

require_once(dirname(__FILE__).'/config.inc.php');
require_once(dirname(__FILE__).'/includes/auth.inc.php');
require_once(dirname(__FILE__).'/includes/utils.inc.php');
require_once(dirname(__FILE__).'/includes/pageparts.inc.php');

// initialization stuff
pre_init();

// start session
init_session();

// grab GET or POST variables 
grab_request_vars();

// check prereqs
check_prereqs();


route_request();

function route_request(){
	global $request;
	
	if(install_needed()==false){
		header("Location: ".get_base_url());
		exit();
		}
	
	$pageopt=get_pageopt("");
	
	switch($pageopt){
		case "install":
			do_install();
			break;
		default:
			show_install();
			break;
		}

	}

function show_install($error=false,$msg=""){
	global $cfg;
	global $request;
	global $lstr;
	
	$url=get_base_url();
	$admin_name="Nagios Administrator";
	$admin_email="root@localhost";
	$admin_password=random_string(6);
	
	// page start
	do_page_start(array("page_title"=>$lstr['InstallPageTitle']));

?>
	<h1><?php echo $lstr['InstallPageHeader'];?></h1>
	
<?php
	display_message($error,"",$msg);
?>

	<p><?php echo $lstr['InstallPageMessage'];?></p>


	<form id="manageOptionsForm" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">

	<!--
	<fieldset>
	<legend><?php echo $lstr['GeneralOptionsFormLegend'];?></legend>
	//-->
	
	<input type="hidden" name="install" value="1">
	<?php echo get_nagios_session_protector();?>
	
	<div class="sectionTitle"><?php echo $lstr['GeneralProgramSettingsSectionTitle'];?></div>

	<table class="manageOptionsTable">

	<tr>
	<td>
	<label for="urlBox"><?php echo $lstr['ProgramURLText'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="text" size="45" name="url" id="urlBox" value="<?php echo encode_form_val($url);?>" class="textfield" /><br class="nobr" />
	</td>
	<tr>
	
	<tr>
	<td>
	<label for="adminNameBox"><?php echo $lstr['AdminNameText'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="text" size="30" name="admin_name" id="adminNameBox" value="<?php echo encode_form_val($admin_name);?>" class="textfield" /><br class="nobr" />
	</td>
	<tr>
	
	<tr>
	<td>
	<label for="adminEmailBox"><?php echo $lstr['AdminEmailText'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="text" size="30" name="admin_email" id="adminEmailBox" value="<?php echo encode_form_val($admin_email);?>" class="textfield" /><br class="nobr" />
	</td>
	<tr>
	
	<tr>
	<td>
	<label for="adminPasswordBox"><?php echo $lstr['AdminPasswordText'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="text" size="30" name="admin_password" id="adminPasswordBox" value="<?php echo encode_form_val($admin_password);?>" class="textfield" /><br class="nobr" />
	</td>
	<tr>
	
	</table>




	<div id="formButtons">
	<input type="submit" class="submitbutton" name="updateButton" value="<?php echo $lstr['InstallButton'];?>" id="updateButton">
	</div>
	

	<!--</fieldset>-->
	</form>
	
<?php
	// page end
	do_page_end();
	
	exit();
	}

	
function do_install(){
	global $lstr;

	
	// check session
	check_nagios_session_protector();

	// get values
	$url=grab_request_var("url","");
	$admin_name=grab_request_var("admin_name","");
	$admin_email=grab_request_var("admin_email","");
	$admin_password=grab_request_var("admin_password","");


	// check for errors
	$errors=0;
	$errmsg=array();
	if(have_value($url)==false)
		$errmsg[$errors++]=$lstr['BlankURLError'];
	else if(!valid_url($url))
		$errmsg[$errors++]=$lstr['InvalidURLError'];
	if(have_value($admin_name)==false)
		$errmsg[$errors++]=$lstr['BlankNameError'];
	if(have_value($admin_email)==false)
		$errmsg[$errors++]=$lstr['BlankEmailError'];
	else if(!valid_email($admin_email))
		$errmsg[$errors++]=$lstr['InvalidEmailAddressError'];
	if(have_value($admin_password)==false)
		$errmsg[$errors++]=$lstr['BlankPasswordError'];

	$uid=get_user_id("nagiosadmin");
	if($uid<=0)
		$errmsg[$errors++]="Unable to get user id for admin account.";

	// handle errors
	if($errors>0){
		//echo "ERRORS: $errors<BR>\n";
		//print_r($errmsg);
		//exit();
		show_install(true,$errmsg);
		}

	// set global options
	set_option("admin_name",$admin_name);
	set_option("admin_email",$admin_email);
	set_option("url",$url);
	
	// modify the admin account
	//$errmsg="";
	//add_user_account("nagiosadmin",$admin_password,$admin_name,$admin_email,L_GLOBALADMIN,0,$errmsg);
	change_user_attr($uid,"email",$admin_email);
	change_user_attr($uid,"name",$admin_name);
	change_user_attr($uid,"password",md5($admin_password));
	change_user_attr($uid,"backend_ticket",random_string(8));
	
	
	// random PNP / nagios core backend password (used for performance graphs)
	$nagioscore_backend_password=random_string(6);
	$pnp_username=get_component_credential("pnp","username");
	set_component_credential("pnp","password",$nagioscore_backend_password);
	$args=array(
		"username" => $pnp_username,
		"password" => $nagioscore_backend_password
		);
	submit_command(COMMAND_NAGIOSXI_SET_HTACCESS,serialize($args));

	// clear license acceptance for nagiosadmin
	set_user_meta($uid,"license_version",-1,false);
	
	// clear inital task settings
	set_option("system_settings_configured",0);
	set_option("security_credentials_updated",0);
	set_option("mail_settings_configured",0);
	
	// set installation flags
	set_db_version();
	set_install_version();
	
	// check trial start date
	$ts=get_trial_start();
	// make sure something didn't get whacked with the customer's install
	if($ts==0 || $ts>time()){
		// todo...
		}
	
	// delete force install file if it exists
	if(file_exists("/tmp/nagiosxi.forceinstall"))
		unlink("/tmp/nagiosxi.forceinstall");

	// tun on automatic update checks
	set_option('auto_update_check',true);
	
	// do an update check
	do_update_check(true,true);
		
	show_install_complete();
	}
	



function show_install_complete($error=false,$msg=""){
	global $request;
	global $lstr;
	
	// get variables
	$admin_password=grab_request_var("admin_password","");
	
	// display page
	do_page_start(array("page_title"=>$lstr['InstallCompletePageTitle']));
?>
	<h1><?php echo $lstr['InstallCompletePageHeader'];?></h1>
<?php	
	display_message($error,false,$msg);
?>

	<p>
	<?php echo $lstr['InstallCompletePageMessage'];?>
	</p>
	
	<p>
	You may now login to Nagios XI using the following credentials:
	</p>
	
	<table>
	<tr><td><?php echo $lstr['UsernameText'];?>:</td><td><b>nagiosadmin</b></td></tr>
	<tr><td><?php echo $lstr['PasswordText'];?>:</td><td><b><?php echo $admin_password;?></b></td></tr>
	</table>
	
	<p>
	<a href="login.php" target="_blank"><b>Login to Nagios XI</b></a>
	</p>
	

<?php

	do_page_end();
	exit();
	}
	
	

?>



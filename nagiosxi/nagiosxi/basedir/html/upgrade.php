<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: upgrade.php 923 2011-12-19 18:33:29Z agriffin $

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
	
	if(upgrade_needed()==false || is_admin()==false){
		header("Location: ".get_base_url());
		exit();
		}
	
	$pageopt=get_pageopt("");
	
	switch($pageopt){
		case "upgrade":
			do_upgrade();
			break;
		default:
			show_upgrade();
			break;
		}

	}

function show_upgrade($error=false,$msg=""){
	global $cfg;
	global $request;
	global $lstr;
	

	// page start
	do_page_start(array("page_title"=>$lstr['UpgradePageTitle']));

?>
	<h1><?php echo $lstr['UpgradePageHeader'];?></h1>
	
<?php
	display_message($error,"",$msg);
?>

	<p><?php echo $lstr['UpgradePageMessage'];?></p>


	<form id="manageOptionsForm" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">

	
	<input type="hidden" name="upgrade" value="1">
	<?php echo get_nagios_session_protector();?>
	



	<div id="formButtons">
	<input type="submit" class="submitbutton" name="updateButton" value="<?php echo $lstr['UpgradeButton'];?>" id="updateButton">
	</div>
	

	<!--</fieldset>-->
	</form>
	
<?php
	// page end
	do_page_end();
	
	exit();
	}

	
function do_upgrade(){
	global $lstr;

	
	// check session
	check_nagios_session_protector();

	// get values
	//$url=grab_request_var("url","");

	// check for errors
	$errors=0;
	$errmsg=array();
	
	
	///////////////////////////////////////////////////////
	////// 2009R1.2 FIXES /////////////////////////////////
	///////////////////////////////////////////////////////

	// random PNP / nagios core backend password (used for performance graphs)
	if(get_component_credential("pnp","username")!="nagiosxi"){
		$nagioscore_backend_password=random_string(6);
		$pnp_username="nagiosxi";
		set_component_credential("pnp","username",$pnp_username);
		set_component_credential("pnp","password",$nagioscore_backend_password);
		$args=array(
			"username" => $pnp_username,
			"password" => $nagioscore_backend_password
			);
		submit_command(COMMAND_NAGIOSXI_SET_HTACCESS,serialize($args));
		}
	
	///////////////////////////////////////////////////////
	////// 2009R1.2D FIXES /////////////////////////////////
	///////////////////////////////////////////////////////

	// randomize default nagiosadmin backend ticket
	$uid=get_user_id("nagiosadmin");
	if($uid>0){
		$backend_ticket=get_user_attr($uid,"backend_ticket");
		if($backend_ticket=="1234")
			change_user_attr($uid,"backend_ticket",random_string(8));
		}
	
	
	// set installation flags
	set_db_version();
	set_install_version();
		
	show_upgrade_complete();
	}
	



function show_upgrade_complete($error=false,$msg=""){
	global $lstr;
	
	// display page
	do_page_start($lstr['UpgradeCompletePageTitle']);
?>
	<h1><?php echo $lstr['UpgradeCompletePageHeader'];?></h1>
<?php	
	display_message($error,false,$msg);
?>

	<p>
	<?php echo $lstr['UpgradeCompletePageMessage'];?>
	</p>

	<p>
	<a href="index.php"><b>Continue</b></a>
	</p>
	

<?php

	do_page_end();
	exit();
	}
	
	

?>



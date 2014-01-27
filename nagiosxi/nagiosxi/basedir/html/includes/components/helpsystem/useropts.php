<?php
// USER OPTIONS FOR HOME PAGE
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: useropts.php 903 2012-10-30 21:15:05Z mguthrie $

require_once(dirname(__FILE__).'/../../common.inc.php');

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


route_request();

function route_request(){
	global $request;
	
	if(isset($request['update']))
		do_update_options();
	else
		show_options();
	}

function show_options($error=false,$msg=""){
	global $lstr;
	
	// get settings specified by admin
	$settings_raw=get_option("helpsystem_component_options");
	if(empty($settings_raw))
	// get our settings
	$settings_raw=get_option("helpsystem_component_options");
	if(empty($settings_raw)){
		$settings=array(
			"enabled" => 1,
            "allow_user_override" => 1,
			);
		}
	else
		$settings=unserialize($settings_raw);	
    
    $allow_override = grab_array_var($settings,"allow_user_override", 1);
	// default settings
	$settings_default=array(
		"enabled" => 1,
		);

	// saved settings
	$settings_raw=get_user_meta(0,"helpsystem_component_options");
	if($settings_raw!=""){
		$settings_default=unserialize($settings_raw);
		}

	// settings passed to us
	$settings=grab_request_var("settings",$settings_default);
				
	$title="Help System Options";
	
    
	//let the user know if they can't override the help system
	if($allow_override!=1) {
		$error=true;
		$msg.=gettext("Help system is currently disabled.");
	}
	


	// start the HTML page
	do_page_start(array("page_title"=>$title),true);
	
?>
	<h1><?php echo $title;?></h1>

<?php
	display_message($error,false,$msg);
?>

	<p>
	<?php echo gettext("You can use the settings on this page to enable/disable the Help System."); ?>
	</p>
	
	<form id="manageOptionsForm" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">

	<input type="hidden" name="options" value="1">
	<?php echo get_nagios_session_protector();?>
	<input type="hidden" name="update" value="1">

<?php
	echo '
	<div class="sectionTitle">'.gettext('Help System Settings').'</div>
	
	<table>

	<tr>
	<td valign="top">
	<label for="enabled">'.gettext('Enable Help System:').'</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="enabled" name="settings[enabled]" '.is_checked($settings["enabled"],1).'>
<br class="nobr" />
	'.gettext('Enables the help system.').'<br><br>
	</td>
	</tr>

	</table>
	';
?>
	<div id="formButtons">
	<input type="submit" class="submitbutton" name="updateButton" value="<?php echo $lstr['UpdateSettingsButton'];?>" id="updateButton">
	<input type="submit" class="submitbutton" name="cancelButton" value="<?php echo $lstr['CancelButton'];?>" id="cancelButton">
	</div>

	</form>
<?php		
	
	// closes the HTML page
	do_page_end(true);
	}
	
	
function do_update_options(){
	global $request;
	global $lstr;
	
	// user pressed the cancel button
	if(isset($request["cancelButton"]))
		header("Location: ".get_base_url()."/account/");
		
	// check session
	check_nagios_session_protector();
	
	$errmsg=array();
	$errors=0;

	// get values
	// settings passed to us
	$settings=grab_request_var("settings",array());
	
    // fix checkboxes
    $settings["enabled"]=checkbox_binary(grab_array_var($settings,"enabled",""));

	// make sure we have requirements
	if(in_demo_mode()==true)
		$errmsg[$errors++]=$lstr['DemoModeChangeError'];

		
	// handle errors
	if($errors>0)
		show_options(true,$errmsg);
		
	// update options
	set_user_meta(0,"helpsystem_component_options",serialize($settings),false);
	set_user_meta(0,"helpsystem_component_options_configured",1,false);
			
	// success!
	show_options(false,$lstr['GlobalConfigUpdatedText']);
	}

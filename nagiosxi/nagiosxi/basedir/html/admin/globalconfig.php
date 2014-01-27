<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: globalconfig.php 1244 2012-06-22 05:59:04Z egalstad $

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
		do_update_options();
	else
		show_options();
	exit;
	}
	
	
function show_options($error=false,$msg=""){
	global $request;
	global $lstr;
	
	$url=get_option('url');
	if(have_value($url)==false)
		$url=get_base_url();

	// get options
	$url=grab_request_var("url",$url);
	$external_url=grab_request_var("external_url",get_option('external_url'));
	$admin_name=grab_request_var("admin_name",get_option('admin_name'));
	$admin_email=grab_request_var("admin_email",get_option('admin_email'));
	$language=grab_request_var("defaultLanguage",get_option('default_language'));
	$theme=grab_request_var("defaultTheme",get_option('default_theme'));
	$date_format=grab_request_var("defaultDateFormat",get_option('default_date_format'));
	$number_format=grab_request_var("defaultNumberFormat",intval(get_option('default_number_format')));
	
	if($admin_name=="")
		$admin_name="Nagios XI Admin";
	if($admin_email=="")
		$admin_email="root@localhost";
	
	// default to check for updates unless overridden
	$auc=get_option('auto_update_check');
	if($auc=="")
		$auc=1;
	$auto_update_check=grab_request_var("auto_update_check",$auc);
	if($auto_update_check=="on")
		$auto_update_check=1;
		
	//allow html in status text?
	$allow_html = grab_request_var('allow_html',get_option('allow_status_html')); 

	// get global variables
	$languages=get_languages();
	$themes=get_themes();
	$number_formats=get_number_formats();
	$date_formats=get_date_formats();

	do_page_start(array("page_title"=>$lstr['GlobalConfigPageTitle']),true);

?>

	
	<h1><?php echo $lstr['GlobalConfigPageTitle'];?></h1>
	

<?php
	display_message($error,false,$msg);
?>

	<form id="manageOptionsForm" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">

	<!--
	<fieldset>
	<legend><?php echo $lstr['GeneralOptionsFormLegend'];?></legend>
	//-->
	
	<input type="hidden" name="options" value="1">
	<?php echo get_nagios_session_protector();?>
	<input type="hidden" name="update" value="1">
	
	<div class="sectionTitle"><?php echo $lstr['GeneralProgramSettingsSectionTitle'];?></div>

	<table class="manageOptionsTable">

	<tr>
	<td valign="top">
	<label for="urlBox"><?php echo $lstr['ProgramURLText'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="text" size="45" name="url" id="urlBox" value="<?php echo encode_form_val($url);?>" class="textfield" /><br class="nobr" />
	The default URL used to access Nagios XI directly from your internal network.<br><br>
	</td>
	<tr>
	
	<tr>
	<td valign="top">
	<label for="externalurlBox">External URL:</label><br class="nobr" />
	</td>
	<td>
	<input type="text" size="45" name="external_url" id="externalurlBox" value="<?php echo encode_form_val($external_url);?>" class="textfield" /><br class="nobr" />
	The URL used to access Nagios XI from outside of your internal network (if different than the default above).  If defined, this URL will be referenced in email alerts to allow quick access to the XI interface.<br><br>
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
	<label for="autoUpdateCheckBox"><?php echo $lstr['AutoUpdateCheckBoxTitle'];?>:</label> <a href="<?php echo get_update_check_url();?>" target="_blank"><br /><?php echo $lstr['CheckForUpdateNowText'];?></a><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="autoUpdateCheckBox" name="auto_update_check" <?php echo is_checked($auto_update_check,1);?> /><br class="nobr" />
	</td>
	</tr>
	
	<!-- option to allow html tags -->
	<tr>
		<td><label for="allow_html">Allow HTML Tags in Host/Service Status</label></td>
		<td><input type="checkbox" id="allow_html" name="allow_html" <?php echo is_checked($allow_html); ?> /><br class="nobr" /></td>
	</tr>

	</table>

	<div class="sectionTitle"><?php echo $lstr['DefaultUserSettingsSectionTitle'];?></div>

	<table class="manageOptionsTable">

	<tr>
	<td>
	<label for="defaultLanguage"><?php echo $lstr['DefaultLanguageBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<select name="defaultLanguage" class="languageList dropdown">
<?php
	foreach($languages as $lang => $title){
?>
	<option value="<?php echo $lang;?>" <?php echo is_selected($language,$lang);?>><?php echo $title."</option>\n";?>
<?php
		}
?>
	</select><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label for="defaultTheme"><?php echo $lstr['DefaultThemeBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<select name="defaultTheme" class="themeList dropdown">
<?php
	foreach($themes as $th){
?>
	<option value="<?php echo $th;?>" <?php echo is_selected($theme,$th);?>><?php echo $th."</option>\n";?>
<?php
		}
?>
	</select><br class="nobr" />
	</td>
	</tr>
	
	<tr>
	<td>
	<label for="defaultDateFormat"><?php echo $lstr['DefaultDateFormatBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<select name="defaultDateFormat" class="dateformatList dropdown">
<?php
	foreach($date_formats as $id => $txt){
?>
	<option value="<?php echo $id;?>" <?php echo is_selected($id,$date_format);?>><?php echo $txt;?></option>
<?php
		}
?>
	</select><br class="nobr" />
	</td>
	</tr>
	
	<tr>
	<td>
	<label for="defaultNumberFormat"><?php echo $lstr['DefaultNumberFormatBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<select name="defaultNumberFormat" class="numberformatList dropdown">
<?php
	foreach($number_formats as $id => $txt){
?>
	<option value="<?php echo $id;?>" <?php echo is_selected($id,$number_format);?>><?php echo $txt;?></option>
<?php
		}
?>
	</select><br class="nobr" />
	</td>
	</tr>
	
	</table>

	<!--
	<div class="sectionTitle"><?php echo $lstr['AdvancedProgramSettingsSectionTitle'];?></div>

	<table class="manageOptionsTable">

	</table>
	//-->

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
	$auto_check_update=grab_request_var("auto_update_check","");
	if(have_value($auto_check_update)==true)
		$auto_check_updates=1;
	else
		$auto_check_updates=0;
	$admin_name=grab_request_var("admin_name","");
	$admin_email=grab_request_var("admin_email","");
	$url=grab_request_var("url","");
	$external_url=grab_request_var("external_url","");
	$date_format=grab_request_var("defaultDateFormat",DF_ISO8601);
	$number_format=grab_request_var("defaultNumberFormat",NF_2);
	$language=grab_request_var("defaultLanguage","");
	$theme=grab_request_var("defaultTheme","");
	//allow html
	$allow_html = grab_request_var('allow_html',false); 

	// make sure we have requirements
	if(in_demo_mode()==true)
		$errmsg[$errors++]=$lstr['DemoModeChangeError'];
	if(have_value($admin_name)==false)
		$errmsg[$errors++]=$lstr["NoAdminNameError"];
	if(have_value($admin_email)==false)
		$errmsg[$errors++]=$lstr["NoAdminEmailError"];
	else if(!valid_email($admin_email))
		$errmsg[$errors++]=$lstr["InvalidAdminEmailError"];
	if(have_value($url)==false)
		$errmsg[$errors++]=$lstr['BlankURLError'];
	else if(!valid_url($url))
		$errmsg[$errors++]=$lstr['InvalidURLError'];
	if(have_value($language)==false)
		$errmsg[$errors++]=$lstr['BlankDefaultLanguageError'];
	if(have_value($theme)==false)
		$errmsg[$errors++]=$lstr['BlankDefaultThemeError'];

		
	// handle errors
	if($errors>0)
		show_options(true,$errmsg);
		
	// update options
	set_option("admin_name",$admin_name);
	set_option("admin_email",$admin_email);
	set_option("url",$url);
	set_option("external_url",$external_url);
	set_option("default_language",$language);
	set_option("default_theme",$theme);
	set_option("auto_update_check",$auto_check_updates);
	set_option("default_date_format",$date_format);
	set_option("default_number_format",$number_format);
	set_option('allow_status_html',$allow_html); 
	
	// mark that system settings were configured
	set_option("system_settings_configured",1);

	// log it
	send_to_audit_log("User updated global program settings",AUDITLOGTYPE_CHANGE);
	
	// success!
	show_options(false,$lstr['GlobalConfigUpdatedText']);
	}
		
		

function draw_menu(){
	//$m=get_admin_menu_items();
	//draw_menu_items($m);
	}
	
	

?>
<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: main.php 1166 2012-05-08 15:58:07Z egalstad $

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
check_authentication();


// route request
route_request();


function route_request(){
	global $request;

	if(isset($request['update']))
		do_updateprefs();
	else
		show_updateprefs();
	exit;
	}
	
	
function show_updateprefs($error=false,$msg=""){
	global $request;
	global $lstr;
	
	// get defaults
	$email=grab_request_var("email",get_user_attr(0,'email'));
	$name=grab_request_var("name",get_user_attr(0,'name'));
	$language=grab_request_var("defaultLanguage",get_user_meta(0,'language'));
	$theme=grab_request_var("defaultTheme",get_user_meta(0,'theme'));
	$date_format=grab_request_var("defaultDateFormat",intval(get_user_meta(0,'date_format')));
	$number_format=grab_request_var("defaultNumberFormat",intval(get_user_meta(0,'number_format')));
	$ignore_notice_update=grab_request_var("ignore_notice_update",get_user_meta(0,'ignore_notice_update'));
	if($ignore_notice_update!="on")
		$ignore_notice_update=0;
	else
		$ignore_notice_update=1;
	$show_login_alert_screen=grab_request_var("show_login_alert_screen",get_user_meta(0,"show_login_alert_screen"));
	
	
	// get global variables
	$languages=get_languages();
	$themes=get_themes();
	$number_formats=get_number_formats();
	$date_formats=get_date_formats();
	
	do_page_start(array("page_title"=>$lstr['AccountSettingsPageTitle']),true);
?>

	<h1><?php echo $lstr['AccountSettingsPageHeader'];?></h1>
	

<?php
	display_message($error,false,$msg);
?>

	<form id="updateUserPreferencesForm" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>?page=<?php echo PAGE_ACCTINFO;?>">
	<input type="hidden" name="update" value="1" />
	<?php echo get_nagios_session_protector();?>
	
	<!--
	<fieldset>
	<legend><?php echo $lstr['UpdateUserPrefsFormLegend'];?></legend>
	//-->
	
	<div class="sectionTitle"><?php echo $lstr['MyAccountSettingsSectionTitle'];?></div>

	<table class="updateUserPreferencesTable">

	<tr>
	<td>
	<label for="passwordBox1"><?php echo $lstr['NewPassword1BoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="password" size="10" name="password1" id="passwordBox1" class="textfield" /><br class="nobr" />
	</td>
	</tr>
	
	<tr>
	<td>
	<label for="passwordBox2"><?php echo $lstr['NewPassword2BoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="password" size="10" name="password2" id="passwordBox2" class="textfield" /><br class="nobr" />
	</td>
	</tr>
	
	<tr>
	<td>
	<label for="nameBox"><?php echo $lstr['NameBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="text" size="30" name="name" id="nameBox" value="<?php echo encode_form_val($name);?>" class="textfield" /><br class="nobr" />
	</td>
	</tr>
	
	<tr>
	<td>
	<label for="emailAddressBox"><?php echo $lstr['EmailBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="text" size="30" name="email" id="emailAddressBox" value="<?php echo encode_form_val($email);?>" class="textfield" /><br class="nobr" />
	</td>
	</tr>
	
	</table>
	
	<div class="sectionTitle"><?php echo $lstr['MyAccountPreferencesSectionTitle'];?></div>

	<table class="updateUserPreferencesTable">

	<!--
	<tr>
	<td>
	<label for="languageListForm"><?php echo $lstr['DefaultLanguageBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<select name="defaultLanguage" id="languageListForm" class="languageListForm dropdown" >
<?php
	foreach($languages as $lang => $title){
?>
	<option value="<?php echo $lang;?>" <?php echo is_selected($language,$lang);?>><?php echo $title;?></option><?php echo "\n";?>
<?php
		}
?>
	</select><br class="nobr" />	
	</td>
	</tr>

	<tr>
	<td>
	<label for="themeListForm"><?php echo $lstr['DefaultThemeBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<select name="defaultTheme" id="themeListForm" class="themeListForm dropdown">
<?php
	foreach($themes as $th){
?>
	<option value="<?php echo $th;?>" <?php echo is_selected($theme,$th);?>><?php echo $th;?></option><?php echo "\n";?>
<?php
		}
?>
	</select><br class="nobr" />
	</td>
	</tr>
	//-->
	
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
	
	<!--
	<tr>
	<td>
	<label for="ignoreNoticeUpdateCheckBox"><?php echo $lstr['IngoreUpdateNotices'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="ignoreNoticeUpdateCheckBox" name="ignore_notice_update" <?php echo is_checked($ignore_notice_update,1);?>><br class="nobr" />
	</td>
	</tr>
	//-->
	
	<tr>
	<td>
	<label for="show_login_alert_screenCheckBox">Show Login Alert Screen:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="show_login_alert_screenCheckBox" name="show_login_alert_screen" <?php echo is_checked($show_login_alert_screen,1);?>><br class="nobr" />
	</td>
	</tr>	

	</table>

	<!--<div class="sectionTitle">-->
	<div id="formButtons">
	<input type="submit" class="submitbutton" name="updateButton" value="<?php echo $lstr['UpdateSettingsButton'];?>" id="updateButton" />
	<input type="submit" class="submitbutton" name="cancelButton" value="<?php echo $lstr['CancelButton'];?>" id="cancelButton" />
	</div>
	<!--</div>-->
	
	<!--</fieldset>-->
	</form>

	
	<script type="text/javascript" language="JavaScript">
	document.forms['updateUserPreferencesForm'].elements['passwordBox1'].focus();
	</script>
	
	
<?php

	do_page_end(true);
	exit();
	}


function do_updateprefs(){
	global $request;
	global $lstr;
	
	// check session
	check_nagios_session_protector();

	// user pressed the cancel button
	if(isset($request["cancelButton"]))
		header("Location: main.php");
	
	$errmsg=array();
	$errors=0;
	
	// grab variables
	$password1=grab_request_var("password1","");
	$password2=grab_request_var("password2","");
	$email=grab_request_var("email","");
	$name=grab_request_var("name","");
	$default_language=grab_request_var("defaultLanguage","");
	$default_theme=grab_request_var("defaultTheme","");
	$date_format=grab_request_var("defaultDateFormat",DF_ISO8601);
	$number_format=grab_request_var("defaultNumberFormat",NF_2);
	$show_login_alert_screen=checkbox_binary(grab_request_var("show_login_alert_screen",1));

	// check for errors
	if(in_demo_mode()==true)
		$errmsg[$errors++]=$lstr['DemoModeChangeError'];
		
	// should we change password?
	$changepass=false;
	if(have_value($password1)==true){
		// user has entered a password
		if(strcmp($password1,$password2))
			$errmsg[$errors++]=$lstr['MismatchedPasswordError'];
		else
			$changepass=true;
		}
	
	if(have_value($name)==false)
		$errmsg[$errors++]=$lstr['BlankNameError'];
	if(have_value($email)==false)
		$errmsg[$errors++]=$lstr['BlankEmailError'];
	else if(!valid_email($email))
		$errmsg[$errors++]=$lstr['InvalidEmailAddressError'];
	//if(have_value($default_language)==false)
		//$errmsg[$errors++]=$lstr['BlankDefaultLanguageError'];
	//if(have_value($default_theme)==false)
		//$errmsg[$errors++]=$lstr['BlankDefaultThemeError'];
		
	
	// handle errors
	if($errors>0)
		show_updateprefs(true,$errmsg);
	
	//$ignore_notice_update=grab_request_var("ignore_notice_update",0);
	//if($ignore_notice_update=="on")
	//	$ignore_notice_update=1;

	// set new prefs
	if($changepass==true)
		change_user_attr(0,'password',md5($password1));
	change_user_attr(0,'email',$email);
	change_user_attr(0,'name',$name);
	set_user_meta(0,'language',$default_language);
	set_user_meta(0,'theme',$default_theme);
	set_user_meta(0,"date_format",$date_format);
	set_user_meta(0,"number_format",$number_format);
	set_user_meta(0,"show_login_alert_screen",$show_login_alert_screen);
	//set_user_meta(0,'ignore_notice_update',$ignore_notice_update);
	
		
	// set session vars
	$_SESSION["language"]=$default_language;
	$_SESSION["theme"]=$default_theme;
	$_SESSION["date_format"]=$date_format;
	$_SESSION["number_format"]=$number_format;
	//$_SESSION["ignore_notice_update"]=$ignore_notice_update;
	
	// reset language notice warning
	//unset($_SESSION["ignore_notice_language"]);

	// log it
	send_to_audit_log("User updated their account settings",AUDITLOGTYPE_CHANGE);
	if($changepass==true)
		send_to_audit_log("User changed their password",AUDITLOGTYPE_SECURITY);
	
	// success!
	show_updateprefs(false,$lstr['UserPrefsUpdatedText']);
	}
	
function draw_menu(){
	$m=get_menu_items();
	draw_menu_items($m);
	}
	
	


?>
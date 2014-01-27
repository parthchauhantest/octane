<?php
// XI Core Ajax Helper Functions
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: ajaxhelpers-tasks.inc.php 377 2010-11-15 19:18:37Z egalstad $

include_once(dirname(__FILE__).'/../componenthelper.inc.php');
	

////////////////////////////////////////////////////////////////////////
// TASK AJAX FUNCTIONS
////////////////////////////////////////////////////////////////////////
	
function xicore_ajax_get_admin_tasks_html($args=null){
	global $lstr;
	
	$output='';

	if(is_admin()==false)
		return $lstr['NotAuthorizedErrorText'];

	else{
		$output.='<div class="infotable_title">Administrative Tasks</div>';

		$output.='
		<table class="infotable">
		<thead>
		<tr><th>Task</th><th></th></tr>
		</thead>
		<tbody>
		';
		
		$base_url=get_base_url();
		$admin_base=$base_url."admin/";
		$config_base=$base_url."config/";
		
		// check for problems
		$problemoutput="";
		
		nagiosql_check_setuid_files($scripts_ok,$goodscripts,$badscripts);
		nagiosql_check_file_perms($config_ok,$goodfiles,$badfiles);
		if($scripts_ok==false || $config_ok==false){
			$problemoutput.="<li><a href='".$admin_base."?xiwindow=configpermscheck.php' target='_top'><b>Fix permissions problems</b></a><br>One or more configuration files or scripts has incorrect settings, which will cause configuration changes to fail.</li>";
			}
		
		if($problemoutput!=""){
			$output.="<tr><td><span class='infotable_subtitle'><img src='".theme_image("error_small.png")."'> Problems Needing Attention:</span></td></tr>";
			$output.="<tr><td>";
			$output.="<ul>";
			$output.=$problemoutput;
			$output.="</ul>";
			$output.="</td></tr>";
			}


		// check for setup tasks that need to be done
		$setupoutput="";
		
		$opt=get_option("system_settings_configured");
		if($opt!=1)
			$setupoutput.="<li><a href='".$admin_base."?xiwindow=globalconfig.php' target='_top'><b>Configure system settings</b></a><br>Configure basic settings for your XI system.</li>";
		
		$opt=get_option("security_credentials_updated");
		if($opt!=1)
			$setupoutput.="<li><a href='".$admin_base."?xiwindow=credentials.php' target='_top'><b>Reset security credentials</b></a><br>Change the default credentials used by the XI system.</li>";
		
		$opt=get_option("mail_settings_configured");
		if($opt!=1)
			$setupoutput.="<li><a href='".$admin_base."?xiwindow=mailsettings.php' target='_top'><b>Configure mail settings</b></a><br>Configure email settings for your XI system.</li>";
		
		if($setupoutput!=""){
			$output.="<tr><td><span class='infotable_subtitle'>Initial Setup Tasks:</span></td></tr>";
			$output.="<tr><td>";
			$output.="<ul>";
			$output.=$setupoutput;
			$output.="</ul>";
			$output.="</td></tr>";
		}

		// check for important tasks that need to be done
		$alertoutput="";

		$update_info=array(
			"last_update_check_succeeded" => get_option("last_update_check_succeeded"),
			"update_available" => get_option("update_available"),
			);
		$updateurl=get_base_url()."admin/?xiwindow=updates.php";
		if($update_info["last_update_check_succeeded"]==0){
			$alertoutput.="<li><div style='float: left; margin-right: 5px;'><img src='".theme_image("unknown_small.png")."'></div>The last <a href='".$updateurl."' target='_top'>update check failed</a>.</li>";
			}
		else if($update_info["update_available"]==1){
			$alertoutput.="<li><div style='float: left; margin-right: 5px;'><img src='".theme_image("critical_small.png")."'></div>A new Nagios XI <a href='".$updateurl."' target='_top'>update is available</a>.</li>";
		
			}
		
			

		if($alertoutput!=""){
			$output.="<tr><td><span class='infotable_subtitle'>Important Tasks:</span></td></tr>";
			$output.="<tr><td>";
			$output.="<ul>";
			$output.=$alertoutput;
			$output.="</ul>";
			$output.="</td></tr>";
		}

		$output.="<tr><td><span class='infotable_subtitle'>Ongoing Tasks:</span></td></tr>";
		$output.="<tr><td>";
		$output.="<ul>";
		$output.="<li><a href='".$config_base."' target='_top'>Configure your monitoring setup</a><br>Add or modify items to be monitored.</li>";
		$output.="<li><a href='".$admin_base."?xiwindow=users.php' target='_top'>Add new user accounts</a><br>Setup new users with access to Nagios XI.</li>";
		$output.="</ul>";
		$output.="</td></tr>";
		
		$output.='
		</tbody>
		</table>
		';
		}
		
	$output.='
	<div class="ajax_date">Last Updated: '.get_datetime_string(time()).'</div>
	';
	
	return $output;
	}
	
	
	
function xicore_ajax_get_getting_started_html($args=null){
	global $lstr;
	
	$output='';

	$output.='<div class="infotable_title">Getting Started Guide</div>';

	$output.='
	<table class="infotable">
	<thead>
	<tr><th>&nbsp;</th></tr>
	</thead>
	<tbody>
	';
		
	$base_url=get_base_url();
	$account_base=$base_url."account/";
	$config_base=$base_url."config/";
	
	$backend_url=get_product_portal_backend_url();
	
	$output.="<tr><td><span class='infotable_subtitle'>Common Tasks:</span></td></tr>";
	$output.="<tr><td>";
	$output.="<ul>";
	$output.="<li><a href='".$account_base."' target='_top'>Change your account settings</a><br>Change your account password and general preferences.</li>";
	$output.="<li><a href='".$account_base."?xiwindow=notifyprefs.php' target='_top'>Change your notifications settings</a><br>Change how and when you receive alert notifications.</li>";
	$output.="<li><a href='".$config_base."' target='_top'>Configure your monitoring setup</a><br>Add or modify items to be monitored with easy-to-use wizards.</li>";
	$output.="</ul>";
	$output.="</td></tr>";
	
	$output.="<tr><td><span class='infotable_subtitle'>Getting Started:</span></td></tr>";
	$output.="<tr><td>";
	$output.="<ul>";
	$output.="<li><a href='".$backend_url."&opt=learn' target='_blank'><b>Learn about XI</b></a><br>Learn more about XI and its capabilities.</li>";
	//$output.="<li><a href='".$backend_url."&opt=bootcamp' target='_blank'><b>Enroll in XI Bootcamp</b></a><br>Get up and running with XI with professional training.</li>";
	$output.="<li><a href='".$backend_url."&opt=newsletter' target='_blank'><b>Signup for XI news</b></a><br>Stay informed of the latest updates and happenings for XI.</li>";
	//$output.="<li><a href='".$backend_url."&opt=supportcontract' target='_blank'>Purchase a Support Contract</a><br>Purchase an XI support contract for your organization and ensure fast access to professional technical assistance.</li>";
	//$output.="<li><a href='".$backend_url."&opt=proservices' target='_blank'>Find Professional Services</a><br>Get professional implementation, consulting, and integration services for XI.</li>";
	$output.="</ul>";
	$output.="</td></tr>";

	
	$output.='
	</tbody>
	</table>
	';
		
	$output.='
	<div class="ajax_date">Last Updated: '.get_datetime_string(time()).'</div>
	';
	
	return $output;
	}
	
	
	
?>
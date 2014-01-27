<?php //ccm.inc.php

// CCM COMPONENT
//
// Copyright (c) 2010 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: ccm.inc.php 115 2010-08-16 16:15:26Z mguthrie $

//include the helper file
require_once(dirname(__FILE__).'/../componenthelper.inc.php');

// respect the name
$ccm_component_name="ccm";

// run the initialization function
ccm_component_init();

////////////////////////////////////////////////////////////////////////
// COMPONENT INIT FUNCTIONS
////////////////////////////////////////////////////////////////////////

function ccm_component_init(){
	global $ccm_component_name;
	global $cfg; 
	
	//boolean to check for latest version
	$versionok=ccm_component_checkversion();
	
	//component description
	$desc="This component is the new revision of the Nagios Core Configuration Manager and is used to manage object configuration files for Nagios XI.";
	
	if(!$versionok)
		$desc="<b>Error: This component requires Nagios XI 2011R3.4 or later.</b>";
	
	//all components require a few arguments to be initialized correctly.  
	$args=array(

		// need a name
		COMPONENT_NAME => $ccm_component_name,
		COMPONENT_VERSION => '2.1', 
		COMPONENT_DATE => '07/03/2013',

		// informative information
		COMPONENT_AUTHOR => "Mike Guthrie. Nagios Enterprises, LLC",
		COMPONENT_DESCRIPTION => $desc,
		COMPONENT_TITLE => "Core Configuration Manager (CCM)",
		
		//do not delete
		COMPONENT_PROTECTED => true,
		COMPONENT_TYPE => COMPONENT_TYPE_CORE,

		// configuration function (optional)
		//COMPONENT_CONFIGFUNCTION => "ccm_component_config_func",
		);
	
	//register this component with XI 
	register_component($ccm_component_name,$args);
	
	// register the addmenu function
	define('MENU_CCM','ccm'); 
	if($versionok) {

		//new CCM menu 
		if(use_2012_features()==true) {
			register_callback(CALLBACK_MENUS_DEFINED,'add_ccm_menu');
			register_callback(CALLBACK_MENUS_INITIALIZED,'ccm_component_addmenu');
			register_callback(CALLBACK_CONFIG_SPLASH_SCREEN,'ccm_component_addsplash'); 
			register_callback(CALLBACK_PAGE_HEAD,'ccm_component_head_include'); 
			ccm_component_update_ccm_config(); 
		}
	}	
}	

function ccm_component_head_include($cbtype='',$args=null) {
    global $components;
    $component_base = get_base_url().'includes/components/ccm/';
    echo "<link rel='stylesheet' type='text/css' href='".$component_base."css/style.css?".$components['ccm']['args']['version']."' />";
    echo '<script type="text/javascript" src="'.$component_base.'javascript/main_js.js?'.$components['ccm']['args']['version'].'"></script><style type="text/css">
#contentWrapper { margin: 0px auto; width: 95%; } 
</style>
<script type="text/javascript">
var NAGIOSXI=true
</script>';
	
}

function ccm_component_checkversion(){

	if(!function_exists('get_product_release'))
		return false;
	//requires greater than 2011R2.1
	if(get_product_release()<217)
		return false;

	return true;
	}
	
	
function ccm_component_update_ccm_config() {
	global $cfg; 
	
	//we don't want subsystem jobs messing up file ownership
	if(defined('SUBSYSTEM'))
		return; 
	
	//make sure we can interact with the db
	db_connect_all(); 

	$base = grab_array_var($cfg,'root_dir','/usr/local/nagiosxi'); 
	$ccm_cfg = $base.'/etc/components/ccm_config.inc.php'; 
	$ccm_last_update = filemtime($ccm_cfg); 
	$default_language = get_option("default_language");
	$mtime = filemtime($base.'/html/config.inc.php');
	
	//log to apache if we can't update CCM credentials
	if(file_exists($ccm_cfg) && !is_writable($ccm_cfg)) {
		trigger_error("CCM Config File: {$ccm_cfg} is not writable by apache!",E_USER_NOTICE); 
	}	
	
	//update config hourly or if config.inc.php has been updated
	if(!file_exists($ccm_cfg) || $mtime > $ccm_last_update || filesize($ccm_cfg)==0) {
	
		//echo "FILE UPDATED<br />"; 
		$plugins = grab_array_var($cfg['component_info'],'plugin_dir','/usr/local/nagios/libexec');
		$nagcmd = grab_array_var($cfg['component_info'],'plugin_dir','/usr/local/nagios/var/rw/nagios.cmd');
		$server = grab_array_var($cfg['db_info']['nagiosql'],'dbserver','localhost');
		$port = 3306; 
		$db = grab_array_var($cfg['db_info']['nagiosql'],'db','nagiosql');
		$user = grab_array_var($cfg['db_info']['nagiosql'],'user','nagiosql');
		$password = grab_array_var($cfg['db_info']['nagiosql'],'pwd','n@gweb'); 
		
		
		$content = '<?php
		/** DO NOT MANUALLY EDIT THIS FILE
		This file is used internally by Nagios CCM.
		Nagios XI will override this file automatically with the latest settings. */';
				
		$content.='
		$CFG["plugins_directory"] = "'.$plugins.'";
		$CFG["command_file"] = "'.$nagcmd.'"; 
		$CFG["default_language"] = "'.$default_language.'";
		
		 
		//mysql database connection info 
		$CFG["db"] = array(
			"server"       => "'.$server.'",
			"port"     		=> "'.$port.'",
			"database"     => "'.$db.'",
			"username"     => "'.$user.'",
			"password"     => "'.$password.'",
			);	
			
		?>'; //end content string 

		file_put_contents($ccm_cfg,$content); 
		//save the last time we've updated this
		//set_option('ccm_last_update',time()); 
	}
	

}	
	
///////////////////////////////////////////////////	
//	Menu Functions 
//////////////////////////////////////////////////	
function ccm_component_addmenu($arg=null){
	global $autodiscovery_component_name;
	
	$mi=find_menu_item(MENU_CONFIGURE,"menu-configure-coreconfigmanager","id");
	if($mi==null)
		return;
		
	$order=grab_array_var($mi,"order","");
	if($order=="")
		return;
		
	$neworder=$order-1;

	add_menu_item(MENU_CONFIGURE,array(
		"type" => "link",
		"title" => gettext("Core Config Manager"),
		"id" => "menu-configure-ccm",
		"order" => $neworder,
		"opts" => array(
			"href" => get_base_url().'includes/components/ccm/xi-index.php',
			"img" => theme_image("menuredirect.png"),
			"target" => "_top"
			),
		"function" => "is_advanced_user",	
		));
	
	}

function ccm_component_addsplash($arg=null){

	//advanced users 
	if(is_advanced_user()==false)
		return; 

	$url=get_base_url().'includes/components/ccm/';

	$output='
	<br clear="all">
	<p>
	<a href="'.$url.'xi-index.php" target="_top"><img src="'.$url.'ccm.jpg" style="float: left; margin-right: 10px;"> '.gettext('Core Configuration Manager').' </a><br>
	'.gettext('Configure monitored elements using an advanced web interface for modifying your Nagios XI monitoring configuration.  Recommended for experienced users.').'
	</p>
	';

	echo $output;
	}


// BUILD NEW CCM MENU To overlay the old one 

function add_ccm_menu()
{
	add_menu(MENU_CCM);	
	$ccm_home = get_base_url()."/includes/components/ccm/"; 
	$corecfg_path=get_base_url()."/includes/components/nagioscorecfg/";
	
	add_menu_item(MENU_CCM,array(
		"type" => "html",
		"title" => gettext("Nagios Core Config Manager"),
		"id" => "menu-ccm-logo",
		"order" => 100,
		"opts" => array(
			"html" => "<img src='".$ccm_home."ccm.jpg' title='".gettext("Nagios Core Config Manager")."'><br><br>",
			"target" => "_top"
			)
		));
	
	// Quick Tools
	add_menu_item(MENU_CCM,array(
		"type" => "menusection",
		"title" => gettext("Quick Tools"),
		"id" => "menu-ccm-section-quicktools",
		"order" => 200,
		"opts" => array(
			"id" => "quicktools",
			"expanded" => true,
			"url" => $ccm_home,
			)
		));
	add_menu_item(MENU_CCM,array(
		"type" => "link",
		"title" => gettext("Legacy CCM"),
		"id" => "menu-ccm-configmanagerhome",
		"order" => 201.5,
		"opts" => array(
			"href" => get_base_url()."/config/nagioscorecfg/",
			"target" => "_top",
			)
		));
		
	add_menu_item(MENU_CCM,array(
		"type" => "link",
		"title" => gettext("Core Config Manager"),
		"id" => "menu-ccm-configmanagerhome",
		"order" => 201,
		"opts" => array(
			"href" => $ccm_home,
			)
		));		
	
	
	add_menu_item(MENU_CCM,array(
		"type" => "linkspacer",
		"order" => 202,
		));
	add_menu_item(MENU_CCM,array(
		"type" => "link",
		"title" => gettext("Apply Configuration"),
		"id" => "menu-ccm-applyconfiguration",
		"order" => 210,
		"opts" => array(
			"href" => $corecfg_path."applyconfig.php?cmd=confirm",
			//"target" => "_top",
			)
		));
	add_menu_item(MENU_CCM,array(
		"type" => "linkspacer",
		"order" => 211,
		));

	add_menu_item(MENU_CCM,array(
		"type" => "link",
		"title" => gettext("Configuration Snapshots"),
		"id" => "menu-ccm-configsnapshots",
		"order" => 220,
		"opts" => array(
			"img" => theme_image("menuredirect.png"),
			"href" => get_base_url()."admin/?xiwindow=coreconfigsnapshots.php",
			"target" => "_parent",
			),
		"function" => "is_admin",
		));
	add_menu_item(MENU_CCM,array(
		"type" => "link",
		"title" => gettext("Monitoring Plugins"),
		"id" => "menu-ccm-monitoringplugins",
		"order" => 221,
		"opts" => array(
			"img" => theme_image("menuredirect.png"),
			"href" => get_base_url()."admin/?xiwindow=monitoringplugins.php",
			"target" => "_parent",
			),
		"function" => "is_admin",
		));

	add_menu_item(MENU_CCM,array(
		"type" => "link",
		"title" => gettext("Configuration Wizards"),
		"id" => "menu-ccm-configwizards",
		"order" => 222,
		"opts" => array(
			"img" => theme_image("menuredirect.png"),
			"href" => get_base_url()."config/",
			"target" => "_parent",
			)
		));

	add_menu_item(MENU_CCM,array(
		"type" => "menusectionend",
		"id" => "menu-ccm-sectionend-quicktools",
		"order" => 223,
		"title" => "",
		"opts" => ""
		));
	
	/////////////////////////////////////////////////////////////////////	
	// Monitoring
	add_menu_item(MENU_CCM,array(
		"type" => "menusection",
		"title" => gettext("Monitoring"),
		"id" => "menu-ccm-section-monitoring",
		"order" => 300,
		"opts" => array(
			"id" => "monitoring",
			"expanded" => true,
			//"url" => $nagiosql_path."/admin/monitoring.php",
			)
		));
	add_menu_item(MENU_CCM,array(
		"type" => "link",
		"title" => gettext("Hosts"),
		"id" => "menu-ccm-hosts",
		"order" => 301,
		"opts" => array(
			"href" => $ccm_home.'?cmd=view&type=host',
			)
		));
	add_menu_item(MENU_CCM,array(
		"type" => "link",
		"title" => gettext("Services"),
		"id" => "menu-ccm-services",
		"order" => 302,
		"opts" => array(
			"href" => $ccm_home.'?cmd=view&type=service',
			)
		));
	add_menu_item(MENU_CCM,array(
		"type" => "link",
		"title" => gettext("Host Groups"),
		"id" => "menu-ccm-hostgroups",
		"order" => 303,
		"opts" => array(
			"href" => $ccm_home.'?cmd=view&type=hostgroup',
			)
		));
	add_menu_item(MENU_CCM,array(
		"type" => "link",
		"title" => gettext("Service Groups"),
		"id" => "menu-ccm-servicegroups",
		"order" => 304,
		"opts" => array(
			"href" => $ccm_home.'?cmd=view&type=servicegroup',
			)
		));
	add_menu_item(MENU_CCM,array(
		"type" => "menusectionend",
		"title" => "",
		"id" => "menu-ccm-sectionend-monitoring",
		"order" => 305,
		"opts" => ""
		));


	// Alerting
	add_menu_item(MENU_CCM,array(
		"type" => "menusection",
		"title" => gettext("Alerting"),
		"id" => "menu-ccm-section-alerting",
		"order" => 400,
		"opts" => array(
			"id" => "alerting",
			"expanded" => true,
			//"url" => $nagiosql_path."/admin/alarming.php",
			)
		));
	add_menu_item(MENU_CCM,array(
		"type" => "link",
		"title" => gettext("Contacts"),
		"id" => "menu-ccm-contacts",
		"order" => 401,
		"opts" => array(
			"href" => $ccm_home.'?cmd=view&type=contact',
			)
		));
	add_menu_item(MENU_CCM,array(
		"type" => "link",
		"title" => gettext("Contact Groups"),
		"id" => "menu-ccm-contactgroups",
		"order" => 402,
		"opts" => array(
			"href" => $ccm_home.'?cmd=view&type=contactgroup',
			)
		));
	add_menu_item(MENU_CCM,array(
		"type" => "link",
		"title" => gettext("Time Periods"),
		"id" => "menu-ccm-timeperiods",
		"order" => 403,
		"opts" => array(
			"href" => $ccm_home.'?cmd=view&type=timeperiod',
			)
		));
	add_menu_item(MENU_CCM,array(
		"type" => "link",
		"title" => gettext("Host Escalations"),
		"id" => "menu-ccm-hostescalations",
		"order" => 404,
		"opts" => array(
			"href" => $ccm_home."?cmd=view&type=hostescalation",
			)
		));
	add_menu_item(MENU_CCM,array(
		"type" => "link",
		"title" => gettext("Service Escalations"),
		"id" => "menu-ccm-serviceescalations",
		"order" => 405,
		"opts" => array(
			"href" => $ccm_home."?cmd=view&type=serviceescalation",
			)
		));
	add_menu_item(MENU_CCM,array(
		"type" => "menusectionend",
		"title" => "",
		"id" => "menu-ccm-sectionend-alerting",
		"order" => 406,
		"opts" => ""
		));

	// Templates
	add_menu_item(MENU_CCM,array(
		"type" => "menusection",
		"title" => gettext("Templates"),
		"id" => "menu-ccm-section-templates",
		"order" => 500,
		"opts" => array(
			"id" => "templates",
			"expanded" => false,
			//"url" => $nagiosql_path."/admin/hosttemplates.php",
			)
		));
	add_menu_item(MENU_CCM,array(
		"type" => "link",
		"title" => gettext("Host Templates"),
		"id" => "menu-ccm-hosttemplates",
		"order" => 501,
		"opts" => array(
			"href" => $ccm_home.'?cmd=view&type=hosttemplate',
			)
		));
	add_menu_item(MENU_CCM,array(
		"type" => "link",
		"title" => gettext("Service Templates"),
		"id" => "menu-ccm-servicetemplates",
		"order" => 502,
		"opts" => array(
			"href" => $ccm_home.'?cmd=view&type=servicetemplate',
			)
		));
	add_menu_item(MENU_CCM,array(
		"type" => "link",
		"title" => gettext("Contact Templates"),
		"id" => "menu-ccm-contacttemplates",
		"order" => 503,
		"opts" => array(
			"href" => $ccm_home.'?cmd=view&type=contacttemplate',
			)
		));
	add_menu_item(MENU_CCM,array(
		"type" => "menusectionend",
		"title" => "",
		"id" => "menu-ccm-sectionend-templates",
		"order" => 504,
		"opts" => ""
		));

	// Commands
	add_menu_item(MENU_CCM,array(
		"type" => "menusection",
		"title" => gettext("Commands"),
		"id" => "menu-ccm-section-commands",
		"order" => 600,
		"opts" => array(
			"id" => "commands",
			"expanded" => false,
			//"url" => $nagiosql_path."/admin/checkcommands.php",
			)
		));
	add_menu_item(MENU_CCM,array(
		"type" => "link",
		"title" => "Commands",
		"id" => "menu-ccm-commands",
		"order" => 601,
		"opts" => array(
			"href" => $ccm_home.'?cmd=view&type=command',
			)
		));
	add_menu_item(MENU_CCM,array(
		"type" => "menusectionend",
		"title" => "",
		"id" => "menu-ccm-sectionend-commands",
		"order" => 602,
		"opts" => ""
		));


	// Advanced
	add_menu_item(MENU_CCM,array(
		"type" => "menusection",
		"title" => gettext("Advanced"),
		"id" => "menu-ccm-section-advanced",
		"order" => 700,
		"opts" => array(
			"id" => "advanced",
			"expanded" => false,
			//"url" => $nagiosql_path."/admin/specials.php",
			)
		));
	add_menu_item(MENU_CCM,array(
		"type" => "link",
		"title" => gettext("Host Dependencies"),
		"id" => "menu-ccm-hostdependencies",
		"order" => 701,
		"opts" => array(
			"href" => $ccm_home."?cmd=view&type=hostdependency",
			)
		));
	add_menu_item(MENU_CCM,array(
		"type" => "link",
		"title" => gettext("Service Dependencies"),
		"id" => "menu-ccm-servicedependencies",
		"order" => 702,
		"opts" => array(
			"href" => $ccm_home."?cmd=view&type=servicedependency",
			)
		));
		
		
	add_menu_item(MENU_CCM,array(
		"type" => "link",
		"title" => gettext("Nagios Core Main Config"),
		"id" => "menu-ccm-coremainconfig",
		"order" => 703,
		"opts" => array(
			"href" => $ccm_home."?cmd=admin&type=corecfg",
			)
		));
	add_menu_item(MENU_CCM,array(
		"type" => "link",
		"title" => gettext("Nagios Core CGI Config"),
		"id" => "menu-ccm-corecgiconfig",
		"order" => 704,
		"opts" => array(
			"href" => $ccm_home."?cmd=admin&type=cgicfg",
			)
		));
	add_menu_item(MENU_CCM,array(
		"type" => "menusectionend",
		"title" => "",
		"id" => "menu-ccm-sectionend-advanced",
		"order" => 705,
		"opts" => ""
		));

	// Tools
	add_menu_item(MENU_CCM,array(
		"type" => "menusection",
		"title" => gettext("Tools"),
		"id" => "menu-ccm-section-tools",
		"order" => 800,
		"opts" => array(
			"id" => "tools",
			"expanded" => false,
			//"url" => $nagiosql_path."/admin/tools.php",
			)
		));
	add_menu_item(MENU_CCM,array(
		"type" => "link",
		"title" => gettext("Static Configurations"),
		"id" => "menu-ccm-staticconfigurations",
		"order" => 801,
		"opts" => array(
			"href" => $ccm_home."?cmd=admin&type=static",
			)
		));		

	/*
	add_menu_item(MENU_CCM,array(
		"type" => "link",
		"title" => "Bulk Modifications",
		"id" => "menu-ccm-bulkmodifications",
		"order" => 802,
		"opts" => array(
			"href" => $ccm_home."?cmd=admin&type=bulk",
			)
		));		
	*/	
		
	add_menu_item(MENU_CCM,array(
		"type" => "link",
		"title" => gettext("Import Config Files"),
		"id" => "menu-ccm-importconfigfiles",
		"order" => 803,
		"opts" => array(
			"href" => $ccm_home."?cmd=admin&type=import",
			)
		));
	add_menu_item(MENU_CCM,array(
		"type" => "link",
		"title" => gettext("Write Config Files"),
		"id" => "menu-ccm-writeconfigfiles",
		"order" => 804,
		"opts" => array(
			"href" => $ccm_home.'?cmd=apply',
			)
		));
	add_menu_item(MENU_CCM,array(
		"type" => "menusectionend",
		"title" => "",
		"id" => "menu-ccm-sectionend-tools",
		"order" => 810,
		"opts" => ""
		));


	// NagiosQL Admin
	add_menu_item(MENU_CCM,array(
		"type" => "menusection",
		"title" => gettext("Config Manager Admin"),
		"id" => "menu-ccm-section-admin",
		"order" => 900,
		"opts" => array(
			"id" => "nagiosqladmin",
			"expanded" => false,
			//"url" => $nagiosql_path."/admin/user.php",
			)
		));
	add_menu_item(MENU_CCM,array(
		"type" => "link",
		"title" => gettext("Manage Config Access"),
		"id" => "menu-ccm-manageconfigaccess",
		"order" => 901,
		"opts" => array(
			"href" => $ccm_home."?cmd=admin&type=user",
			)
		));

	add_menu_item(MENU_CCM,array(
		"type" => "link",
		"title" => gettext("Config Manager Log"),
		"id" => "menu-ccm-configmanagerlog",
		"order" => 903,
		"opts" => array(
			"href" => $ccm_home."?cmd=admin&type=log",
			)
		));
	add_menu_item(MENU_CCM,array(
		"type" => "link",
		"title" => gettext("Config Manager Settings"),
		"id" => "menu-ccm-configmanagersettings",
		"order" => 904,
		"opts" => array(
			"href" => $ccm_home."?cmd=admin&type=settings",
			)
		));

	add_menu_item(MENU_CCM,array(
		"type" => "menusectionend",
		"title" => "",
		"id" => "menu-ccm-sectionend-admin",
		"order" => 910,
		"opts" => ""
		));
	

}	//end build_ccm_menu()  



?>
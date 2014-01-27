<?php 
// Bulk Modifications COMPONENT
//
// Copyright (c) 2010 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: bulkmodifications.inc.php 115 2010-08-16 16:15:26Z mguthrie $

//include the helper file
require_once(dirname(__FILE__).'/../componenthelper.inc.php');

// respect the name
$bulkmodifications_component_name="bulkmodifications";

// run the initialization function
bulkmodifications_component_init();

////////////////////////////////////////////////////////////////////////
// COMPONENT INIT FUNCTIONS
////////////////////////////////////////////////////////////////////////

function bulkmodifications_component_init(){
	global $bulkmodifications_component_name;
	
	//boolean to check for latest version
	$versionok=bulkmodifications_component_checkversion();
	
	//component description
	$desc=gettext("This component allows administrators to submit bulk configurations changes for selected hosts and services. ");
	
	if(!$versionok)
		$desc="<b>".gettext("Error: This component requires Nagios XI 2012R1.0 or later.")."</b>";
	
	//all components require a few arguments to be initialized correctly.  
	$args=array(

		// need a name
		COMPONENT_NAME => $bulkmodifications_component_name,
		COMPONENT_VERSION => '1.1', 
		COMPONENT_DATE => '02/04/2013',

		// informative information
		COMPONENT_AUTHOR => "Mike Guthrie. Nagios Enterprises, LLC",
		COMPONENT_DESCRIPTION => $desc,
		COMPONENT_TITLE => "Bulk Modifications",

		// configuration function (optional)
		//COMPONENT_CONFIGFUNCTION => "bulkmodifications_component_config_func",
		);
	
	//register this component with XI 
	register_component($bulkmodifications_component_name,$args);
	
	// register the addmenu function
	if($versionok)
		register_callback(CALLBACK_MENUS_INITIALIZED,'bulkmodifications_component_addmenu');
	}
	



///////////////////////////////////////////////////////////////////////////////////////////
// MISC FUNCTIONS
///////////////////////////////////////////////////////////////////////////////////////////

function bulkmodifications_component_checkversion(){

	if(!function_exists('get_product_release'))
		return false;
	//requires greater than 2011R3.1
	if(get_product_release()<215)
		return false;

	return true;
	}
	
function bulkmodifications_component_addmenu($arg=null){
	global $bulkmodifications_component_name;
	global $menus; 
	//retrieve the URL for this component
	$urlbase=get_component_url_base($bulkmodifications_component_name);

	//add this to the core config manager menu 
	add_menu_item(MENU_CORECONFIGMANAGER,array(
		"type" => "link",
		"title" => gettext("Bulk Modifications"),
		"id" => "menu-coreconfigmanager-bulkmodifications",
		"order" => 802.7,
		"opts" => array(
			"href" => $urlbase."/bulkmodifications.php",
			)
		));
	//add to the new ccm if it is installed	
	add_menu_item(MENU_CCM,array(
		"type" => "link",
		"title" => gettext("Bulk Modifications"),
		"id" => "menu-ccm-bulkmodifications",
		"order" => 802.7,
		"opts" => array(
			"href" => $urlbase."/bulkmodifications.php",
			)
		));		
	
}

?>
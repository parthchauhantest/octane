<?php 
// GLOBAL NOTIFICATION MANAGEMENT COMPONENT
//
// Copyright (c) 2011 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: deploynotification.inc.php 423 2011-10-21 17:49:29Z mguthrie $

//include the helper file
require_once(dirname(__FILE__).'/../componenthelper.inc.php');

// respect the name
$deploynotification_component_name="deploynotification";

// run the initialization function
deploynotification_component_init();

////////////////////////////////////////////////////////////////////////
// COMPONENT INIT FUNCTIONS
////////////////////////////////////////////////////////////////////////

function deploynotification_component_init(){
	global $deploynotification_component_name;
	
	//boolean to check for latest version
	$versionok=deploynotification_component_checkversion();
	
	//component description
	$desc=gettext("This component allows administrators to create, save, 
	and deploy notification message to a list of Nagios XI users or contact groups.");
	
	if(!$versionok)
		$desc="<b>".gettext("Error: This component requires Nagios XI 2012R1.0 Enterprise edition or later.")."</b>";
	
	//all components require a few arguments to be initialized correctly.  
	$args=array(

		// need a name
		COMPONENT_NAME => $deploynotification_component_name,
		
		// informative information
		COMPONENT_AUTHOR => "Nagios Enterprises, LLC",
		COMPONENT_DESCRIPTION => $desc,
		COMPONENT_TITLE => "Notification Management",
		COMPONENT_VERSION => 1.1,
		COMPONENT_DATE => "5/1/2012",

		// configuration function (optional)  //see example below for this 
		//COMPONENT_CONFIGFUNCTION => "deploynotification_component_config_func",
		);
	
	//register this component with XI 
	register_component($deploynotification_component_name,$args);
	
	// register the addmenu function
	if($versionok)
		register_callback(CALLBACK_MENUS_INITIALIZED,'deploynotification_component_addmenu');
	}
	



///////////////////////////////////////////////////////////////////////////////////////////
// MISC FUNCTIONS
///////////////////////////////////////////////////////////////////////////////////////////

function deploynotification_component_checkversion(){

	if(!function_exists('get_product_release'))
		return false;
	//requires greater than 2011
	if(get_product_release()<214)
		return false;

	return true;
	}
	
function deploynotification_component_addmenu($arg=null){
	global $deploynotification_component_name;
	//retrieve the URL for this component
	$urlbase=get_component_url_base($deploynotification_component_name);
	//figure out where I'm going on the menu	
	$mi=find_menu_item(MENU_ADMIN,"menu-admin-manageusers","id");
	if($mi==null) //bail if I didn't find the above menu item 
		return;
		
	$order=grab_array_var($mi,"order","");  //extract this variable from the $mi array 
	if($order=="")
		return;
		
	$neworder=$order+0.1; //determine my menu order 

	//add this to the main home menu 
	add_menu_item(MENU_ADMIN,array(
		"type" => "link",
		"title" => gettext("Notification Management"),
		"id" => "menu-admin-deploynotification",
		"order" => $neworder,
		"opts" => array(
			//this is the page the menu will actually point to.
			//all of my actual component workings will happen on this script
			"href" => $urlbase."/deploynotification.php",      
			)
		));

	}

?>
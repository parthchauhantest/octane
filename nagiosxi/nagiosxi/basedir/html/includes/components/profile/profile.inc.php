<?php 
// MASS ACKNOWLEDGE COMPONENT
//
// Copyright (c) 2010 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: profile.inc.php 115 2010-08-16 16:15:26Z mguthrie $

//include the helper file
require_once(dirname(__FILE__).'/../componenthelper.inc.php');

// respect the name
$profile_component_name="profile";

// run the initialization function
profile_component_init();

////////////////////////////////////////////////////////////////////////
// COMPONENT INIT FUNCTIONS
////////////////////////////////////////////////////////////////////////

function profile_component_init(){
	global $profile_component_name;
	
	//boolean to check for latest version
	$versionok=profile_component_checkversion();
	
	//component description
	$desc="This component creates a system profile page in the Admin panel 
	which can be used for troubleshooting purposes.";
	
	if(!$versionok)
		$desc="<b>Error: This component requires Nagios XI 20011R1.1 or later.</b>";
	
	//all components require a few arguments to be initialized correctly.  
	$args=array(

		// need a name
		COMPONENT_NAME => $profile_component_name,
		COMPONENT_VERSION => '1.0', 
		COMPONENT_DATE => '1/3/2012',

		// informative information
		COMPONENT_AUTHOR => "Nagios Enterprises, LLC",
		COMPONENT_DESCRIPTION => $desc,
		COMPONENT_TITLE => "System Profile",

		);
	
	//register this component with XI 
	register_component($profile_component_name,$args);
	
	// register the addmenu function
	if($versionok)
		register_callback(CALLBACK_MENUS_INITIALIZED,'profile_component_addmenu');
	}
	



///////////////////////////////////////////////////////////////////////////////////////////
// MISC FUNCTIONS
///////////////////////////////////////////////////////////////////////////////////////////

function profile_component_checkversion(){

	if(!function_exists('get_product_release'))
		return false;
	//requires greater than 2011R1
	if(get_product_release()<201)
		return false;

	return true;
	}
	
function profile_component_addmenu($arg=null){
	global $profile_component_name;
	//retrieve the URL for this component
	$urlbase=get_component_url_base($profile_component_name);
	//figure out where I'm going on the menu	
	$mi=find_menu_item(MENU_ADMIN,"menu-admin-managesystemconfig","id");
	if($mi==null) //bail if I didn't find the above menu item 
		return;
		
	$order=grab_array_var($mi,"order","");  //extract this variable from the $mi array 
	if($order=="")
		return;
		
	$neworder=$order+0.1; //determine my menu order 

	//add this to the main home menu 
	add_menu_item(MENU_ADMIN,array(
		"type" => "link",
		"title" => "System Profile",
		"id" => "menu-admin-profile",
		"order" => $neworder,
		"opts" => array(
			//this is the page the menu will actually point to.
			//all of my actual component workings will happen on this script
			"href" => $urlbase."/profile.php",      
			)
		));

	}


?>
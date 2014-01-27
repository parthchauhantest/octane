<?php
// NAGIOS CORE COMPONENT
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: nagioscore.inc.php 144 2010-06-07 22:16:34Z egalstad $

require_once(dirname(__FILE__).'/../componenthelper.inc.php');

// run the initialization function
nagioscore_component_init();

////////////////////////////////////////////////////////////////////////
// COMPONENT INIT FUNCTIONS
////////////////////////////////////////////////////////////////////////

function nagioscore_component_init(){

	$name="nagioscore";
	
	$args=array(

		// need a name
		COMPONENT_NAME => $name,
		
		// informative information
		//COMPONENT_VERSION => "1.1",
		//COMPONENT_DATE => "11-27-2009",
		COMPONENT_TITLE => "Nagios Core Integration",
		COMPONENT_AUTHOR => "Nagios Enterprises, LLC",
		COMPONENT_DESCRIPTION => "Provides integration with Nagios Core.",
		//COMPONENT_COPYRIGHT => "Copyright (c) 2009 Nagios Enterprises",
		//COMPONENT_HOMEPAGE => "http://www.nagios.com",
		
		// do not delete
		COMPONENT_PROTECTED => true,
		COMPONENT_TYPE => COMPONENT_TYPE_CORE,

		// configuration function (optional)
		//COMPONENT_CONFIGFUNCTION => "nagioscore_component_config_func",
		);
		
	register_component($name,$args);
	}
	


///////////////////////////////////////////////////////////////////////////////////////////
// URL FUNCTIONS
///////////////////////////////////////////////////////////////////////////////////////////

// gets url used to access core ui
function nagioscore_get_ui_url(){
	$url=get_base_url()."includes/components/nagioscore/ui/";
	return $url;
	}
	
	
	

///////////////////////////////////////////////////////////////////////////////////////////
// IMAGE FUNCTIONS
///////////////////////////////////////////////////////////////////////////////////////////

function nagioscore_image($img=""){
	$base_path=nagioscore_get_ui_url();
	return $base_path."images/".$img;
	}

function nagioscore_object_image($img=""){
	$base_path=nagioscore_get_ui_url();
	return $base_path."images/logos/".$img;
	}
	
?>
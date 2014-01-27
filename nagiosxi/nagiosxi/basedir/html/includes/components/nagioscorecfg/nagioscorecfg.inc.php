<?php
// NAGIO CORE CONFIG COMPONENT
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: nagioscorecfg.inc.php 75 2010-04-01 19:40:08Z egalstad $

require_once(dirname(__FILE__).'/../componenthelper.inc.php');


// run the initialization function
nagioscorecfg_component_init();

////////////////////////////////////////////////////////////////////////
// COMPONENT INIT FUNCTIONS
////////////////////////////////////////////////////////////////////////

function nagioscorecfg_component_init(){

	$name="nagioscorecfg";
	
	$args=array(

		// need a name
		COMPONENT_NAME => $name,
		
		// informative information
		//COMPONENT_VERSION => "1.1",
		//COMPONENT_DATE => "11-27-2009",
		COMPONENT_TITLE => "Nagios Core Configuration Manager",
		COMPONENT_AUTHOR => "Nagios Enterprises, LLC",
		COMPONENT_DESCRIPTION => "Provides core configuration UI integration.",
		//COMPONENT_COPYRIGHT => "Copyright (c) 2009 Nagios Enterprises",
		//COMPONENT_HOMEPAGE => "http://www.nagios.com",
		
		// do not delete
		COMPONENT_PROTECTED => true,
		COMPONENT_TYPE => COMPONENT_TYPE_CORE,

		// configuration function (optional)
		//COMPONENT_CONFIGFUNCTION => "nagioscorecfg_component_config_func",
		);
		
	register_component($name,$args);
	}
	


///////////////////////////////////////////////////////////////////////////////////////////
// URL FUNCTIONS
///////////////////////////////////////////////////////////////////////////////////////////

function nagioscorecfg_get_component_url($directory_only=false){
	$url=get_base_url();
	$url.="/includes/components/nagioscorecfg/";
	if($directory_only==false)
		$url.="nagioscorecfg.php";
	return $url;
	}

	
?>
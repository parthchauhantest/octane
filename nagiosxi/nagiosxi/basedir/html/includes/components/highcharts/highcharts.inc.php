<?php
// HIGHCHARTS COMPONENT
//
// Copyright (c) 2010 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: highcharts.inc.php 197 2010-12-01 16:34:55Z tyarusso $

require_once(dirname(__FILE__).'/../componenthelper.inc.php');

// respect the name
$highcharts_component_name="highcharts";

// run the initialization function
highcharts_component_init();

////////////////////////////////////////////////////////////////////////
// COMPONENT INIT FUNCTIONS
////////////////////////////////////////////////////////////////////////

function highcharts_component_init(){
	global $highcharts_component_name;
	
	$args=array(

		// need a name
		COMPONENT_NAME => $highcharts_component_name,
		
		// informative information
		COMPONENT_AUTHOR => "Nagios Enterprises, LLC",
		COMPONENT_DESCRIPTION => "Provides dynamic reports. Protected by copyright and licensed by Nagios Enterprises.  NOT FOR DISTRIBUTION.",
		COMPONENT_TITLE => "Highcharts",
		
		// do not delete
		COMPONENT_PROTECTED => true,
		COMPONENT_TYPE => COMPONENT_TYPE_CORE,
		
		// configuration function (optional)
		//COMPONENT_CONFIGFUNCTION => "highcharts_component_config_func",
		);
		
	register_component($highcharts_component_name,$args);
	
	}
	



///////////////////////////////////////////////////////////////////////////////////////////
//CONFIG FUNCTIONS
///////////////////////////////////////////////////////////////////////////////////////////

function highcharts_component_config_func($mode="",$inargs,&$outargs,&$result){
	global $highcharts_component_name;

	// initialize return code and output
	$result=0;
	$output="";
	
	//delete_option("highcharts_component_options");
	

	switch($mode){
		case COMPONENT_CONFIGMODE_GETSETTINGSHTML:
			break;
			
		case COMPONENT_CONFIGMODE_SAVESETTINGS:	
			break;
		
		default:
			break;
			
		}
		
	return $output;
	}



?>
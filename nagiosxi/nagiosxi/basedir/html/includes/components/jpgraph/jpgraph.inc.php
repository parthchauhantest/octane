<?php
// JPGRAPH COMPONENT
//
// Copyright (c) 2010 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: jpgraph.inc.php 197 2010-12-01 16:34:55Z tyarusso $

require_once(dirname(__FILE__).'/../componenthelper.inc.php');

// respect the name
$jpgraph_component_name="jpgraph";

// run the initialization function
jpgraph_component_init();

////////////////////////////////////////////////////////////////////////
// COMPONENT INIT FUNCTIONS
////////////////////////////////////////////////////////////////////////

function jpgraph_component_init(){
	global $jpgraph_component_name;
	
	$args=array(

		// need a name
		COMPONENT_NAME => $jpgraph_component_name,
		
		// informative information
		COMPONENT_AUTHOR => "Nagios Enterprises, LLC",
		COMPONENT_DESCRIPTION => "Provides graphics generation capabilities for reports. Protected by copyright and licensed by Nagios Enterprises.  NOT FOR DISTRIBUTION.",
		COMPONENT_TITLE => "JpGraph Addon",
		
		// do not delete
		COMPONENT_PROTECTED => true,
		COMPONENT_TYPE => COMPONENT_TYPE_CORE,
		
		// configuration function (optional)
		//COMPONENT_CONFIGFUNCTION => "jpgraph_component_config_func",
		);
		
	register_component($jpgraph_component_name,$args);
	
	}
	



///////////////////////////////////////////////////////////////////////////////////////////
//CONFIG FUNCTIONS
///////////////////////////////////////////////////////////////////////////////////////////

function jpgraph_component_config_func($mode="",$inargs,&$outargs,&$result){
	global $jpgraph_component_name;

	// initialize return code and output
	$result=0;
	$output="";
	
	//delete_option("jpgraph_component_options");
	

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
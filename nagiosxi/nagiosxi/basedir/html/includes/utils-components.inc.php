<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// Development Started 03/22/2008
// $Id: utils-components.inc.php 1090 2012-03-27 15:23:41Z egalstad $

//require_once(dirname(__FILE__).'/common.inc.php');



////////////////////////////////////////////////////////////////////////
// COMPONENT FUNCTIONS
////////////////////////////////////////////////////////////////////////

function register_component($name="",$args=null){
	global $components;
	
	
	if($name=="")
		return false;
	
	if(!isset($components)){
		$components=array();
		}
	
	//echo "REGISTERING: $name<BR>";
		
	$components[$name]=array(
		COMPONENT_DIRECTORY => "",
		COMPONENT_ARGS => $args,
		);
	
	return true;
	}
	
	
function get_component_url_base($name="",$fullpath=true){
	$url=get_base_url($fullpath)."includes/components/".$name;
	return $url;
	}
	
function get_component_dir_base($name=""){
	$url=get_base_dir()."/includes/components/".$name;
	return $url;
	}
	
	
// tests if component is installed
function is_component_installed($name){
	global $components;
	
	if(array_key_exists($name,$components))
		return true;

	return false;
	}
	

// called by the command sub-system
function install_component($args=null){

	if($args==null)
		return 0;
	if(!is_array($args))
		return 0;
		
	$component_name=grab_array_var($args,"component_name");
	$component_dir=grab_array_var($args,"component_dir");
	
	echo "INSTALLING COMPONENT: $component_name\n";
	
	// post-install script
	$install_script=$component_dir."/install.sh";
	echo "CHECKING FOR INSTALL SCRIPT ".$install_script."\n";
	if(file_exists($install_script)){
	
		echo "RUNNING INSTALL SCRIPT...\n";
	
		// make the script executable
		chmod($install_script,0755);
		
		// run the script
		system($install_script,$retval);
		
		echo "INSTALL SCRIPT FINISHED. RESULT='$retval'\n";
		return $retval;
		}

	return 0;
	}




?>
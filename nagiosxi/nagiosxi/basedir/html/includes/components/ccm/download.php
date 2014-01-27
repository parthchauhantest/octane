<?php

session_start(); 
define('BASEDIR',dirname(__FILE__).'/');   
require_once('includes/session.inc.php'); 


//
$cmd = ''; 
$token = ccm_grab_request_var('token',''); 
//authorization check 
if($AUTH!==true) $cmd = 'login'; 
//verify that the command was submitted from the form, route to login page if it's an illegal operation 
verify_token($cmd,$token); 

route_request($cmd);



/*function route_request()
*	directs page navigation and input requests for config downloads, verifies auth 
*	@param string $cmd requires a valid command to do anything, if auth it bad this will be '' | 'login' 
*/ 
function route_request($cmd='')
{
	if($cmd=='login')
		header('Location: index.php?cmd=login'); 
	
	//proceed if access is authorized 	
	if($cmd =='') download_config(); 	

}

/*function download_config() 
*	generates the config file on the fly based ont the object type 
*	@global object $myConfigClass nagiosql config object 
*	@global object $myDataClass  nagiosql data object 
*/
function download_config()
{

	global $myConfigClass;
	global $myDataClass; 

	//request vars 
	$chkTable   = 'tbl_'.ccm_grab_request_var('type', "");
	$chkConfig  = ccm_grab_request_var('config', "");
	$chkLine    = ccm_grab_request_var('line', 0);
	
	//print_r($_REQUEST); 
	
	//
	// Header ausgeben
	// ===============
	switch($chkTable) {
	  case "tbl_timeperiod":      $strFile = "timeperiods.cfg"; break;
	  case "tbl_command":       $strFile = "commands.cfg"; break;
	  case "tbl_contact":       $strFile = "contacts.cfg"; break;
	  case "tbl_contacttemplate":   $strFile = "contacttemplates.cfg"; break;
	  case "tbl_contactgroup":    $strFile = "contactgroups.cfg"; break;
	  case "tbl_hosttemplate":    $strFile = "hosttemplates.cfg"; break;
	  case "tbl_servicetemplate":   $strFile = "servicetemplates.cfg"; break;
	  case "tbl_hostgroup":     $strFile = "hostgroups.cfg"; break;
	  case "tbl_servicegroup":    $strFile = "servicegroups.cfg"; break;
	  case "tbl_servicedependency": $strFile = "servicedependencies.cfg"; break;
	  case "tbl_hostdependency":    $strFile = "hostdependencies.cfg"; break;
	  case "tbl_serviceescalation": $strFile = "serviceescalations.cfg"; break;
	  case "tbl_hostescalation":    $strFile = "hostescalations.cfg"; break;
	  case "tbl_hostextinfo":     $strFile = "hostextinfo.cfg"; break;
	  case "tbl_serviceextinfo":    $strFile = "serviceextinfo.cfg"; break;
	  default:            $strFile = $chkConfig.".cfg";
	}
	if ($strFile == ".cfg") 
	{	
		print "Error: Invalid Config Option."; 
		exit;
	}
	//header("Content-Disposition: attachment; filename=".$strFile);
	header("Content-Type: text/plain");
	//
	// Create config output 
	// ==========================
	if ($chkLine == 0) {
	  $myConfigClass->createConfig($chkTable,1);
	} else {
	  $myConfigClass->createConfigSingle($chkTable,$chkLine,1);
	}
	$myDataClass->writeLog(gettext('Download')." ".$strFile);

}

?>
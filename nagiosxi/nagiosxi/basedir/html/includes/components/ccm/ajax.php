<?php  //ajax.php //generic handler for CCM ajax requests 

session_start(); 
define('BASEDIR',dirname(__FILE__).'/');   
require_once('includes/session.inc.php'); 

//
$cmd=ccm_grab_request_var('cmd','login');
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
function route_request($cmd='login')
{
	if($cmd=='login')
		header('Location: index.php?cmd=login'); 
		
	$opt = ccm_grab_request_var('opt',false);	
	
	//dump_request();
	//proceed if access is authorized 	
	//TODO turn this into a switch statement once there are more items to grab 
	switch($cmd) {
	
	case 'getcontacts':	 
		print get_ajax_relationship_table($opt); 
	break; 	
	
	case 'getcontactgroups':	 
		print get_cg_ajax_relationship_table($opt); 
	break;	
	
	case 'getinfo':
		$type = ccm_grab_request_var('type','');
		print get_ajax_documentation($type,$opt);
	break; 
	
	case 'getfile':
		print get_ajax_file($opt);		
	break; 
	
	default:
		echo $cmd;
		echo $opt;
	break;
	
	}	
}



/**
*	loads a static config file into a text area
*/
function get_ajax_file($file) {
	
	if(is_readable($file)) 
	$contents = file_get_contents($file);
	return $contents;

}



/**
*	fetch config documentation for selected option 
*
*/
function get_ajax_documentation($type,$opt) {

	global $ccmDB; 
	
	$query="SELECT `infotext` FROM tbl_info WHERE `key1`='{$type}' AND `key2`='{$opt}'"; 
	$array = $ccmDB->query($query);
	if(isset($array[0]['infotext'])) {
		print $array[0]['infotext']; 	
		print "<div class='closeOverlay'>
				<a class='linkBox' style='width:50px;' href='javascript:killOverlay(\"documentation\")' title='Close'>Close</a>
			</div><!-- end closeOverlay -->\n";   
	}
}


/**
*	gets relationship table for contacts 
*
*/
function get_ajax_relationship_table($opt='host') 
{
	global $ccmDB;
	$contact = ccm_grab_request_var('contact',false);
	$id = ccm_grab_request_var('id',false);
	$html = "<div class='bulk_wizard'>\n"; 

	//echo "{$objectType}s related to this contact<br />"; 
	//SELECT * FROM `tbl_lnkHostToContact` LEFT JOIN `tbl_contact` ON `idSlave` = `id` WHERE `idMaster` = 61 AND `idSlave`=1 AND tbl_contact.active = '1'
	//$primaryTable = relationship table
	//$secondaryTable = table with all of the string data in it 
	//SELECT $secondaryFields FROM $primaryTable LEFT JOIN  $secondaryTable ON $primaryField1 = $secondaryField1 WHERE $primaryField2 = $secondaryFieldValue 
	$query = "SELECT `id`,`host_name` FROM `tbl_lnkHostToContact` LEFT JOIN `tbl_host` ON `idMaster` = `id` WHERE `idSlave` = '{$id}'"; 
	$results  = $ccmDB->query($query); 
	//sql_output(); 
	//ccm_array_dump($results);
	$html .= "<div class='leftBox'>
			<h4>".gettext("Hosts directly assigned to contact").": {$contact}</h4>
			<p class='ccm-label'>".gettext("Check any relationships you wish to")." <strong>".gettext("remove")."</strong></p>
			<table class='standardtable' style='text-align:center;'>
			<tr>
			<th>".gettext("Host")."</th>							
			<th>".gettext("Assigned as Contact")."<br />
			<a id='checkAllhost' style='float:none;' title='Check All' href='javascript:checkAllType(\"host\");'>".gettext("Check All")."</a>				
			</th></tr>\n"; 

	if(empty($results)) 
		$html .="<tr><td colspan='2'>".gettext("No relationships for this contact")."</td></tr>\n";
				
	foreach($results as $r) 
		$html .="<tr><td>".$r['host_name']."</td><td style='text-align:center;'>
		<input class='host' type='checkbox' name='hostschecked[]' value='".$r['id']."' /></td></tr>\n"; 
	
	$html .="</table></div>"; //close first table 
	$html .= "<div class='rightBox'>
			<h4>".gettext("Service directly assigned to contact").": {$contact}</h4>
			<p class='ccm-label'>".gettext("Check any relationships you wish to")." <strong>".gettext("remove")."</strong></p>
			<table class='standardtable' style='text-align:center;'>
			<tr>
				<th>".gettext("Config Name")."</th><th>".gettext("Service Description")."</th>							
				<th>".gettext("Assigned as Contact")."<br />
				<a id='checkAllservice' style='float:none;' title='Check All' href='javascript:checkAllType(\"service\");'>".gettext("Check All")."</a>
				</th></tr>\n"; 
	
	//get option list 			
	$query = "SELECT `id`,`config_name`,`service_description` FROM `tbl_lnkServiceToContact` LEFT JOIN `tbl_service` ON `idMaster` = `id` WHERE `idSlave` = '{$id}'"; 
	$results  = $ccmDB->query($query); 			
				
	if(empty($results)) 
		$html .="<tr><td colspan='3'>".gettext("No relationships for this contact")."</td></tr>\n";		
	//display list 
	foreach($results as $r) 
		$html .="<tr><td>".$r['config_name']."</td><td>".$r['service_description']."</td><td style='text-align:center;'>
		<input class='service' type='checkbox' name='serviceschecked[]' value='".$r['id']."' /></td></tr>\n"; 
	
	$html .="</table></div>";			
//	}	
	return $html; 
	
}



/**
*	gets relationship table for contact groups  
*	@TODO: this can be rolled into the same function as contact
*
*/
function get_cg_ajax_relationship_table($opt='host') 
{
	global $ccmDB;
	$contactgroup = ccm_grab_request_var('contactgroup',false); 
	$id = ccm_grab_request_var('id',false);
	
	$query = "SELECT `id`,`host_name` FROM `tbl_lnkHostToContactgroup` LEFT JOIN `tbl_host` ON `idMaster` = `id` WHERE `idSlave` = '{$id}'"; 
	$results  = $ccmDB->query($query); 
	
	//sql_output(); 
	//ccm_array_dump($results);
	$html = "<div class='bulk_wizard'>\n"; 
	$html .= "<div class='leftBox'>
			<h4>".gettext("Hosts directly assigned to contact").": {$contactgroup}</h4>
			<p class='ccm-label'>".gettext("Check any relationships you wish to")." <strong>".gettext("remove")."</strong></p>
			<table class='standardtable' style='text-align:center;'>
			<tr>
			<th>".gettext("Host")."</th>							
			<th>".gettext("Assigned as Contact Group")."<br />
			<a id='checkAllhost' style='float:none;' title='Check All' href='javascript:checkAllType(\"host\");'>Check All</a>				
			</th></tr>\n"; 

	if(empty($results)) 
		$html .="<tr><td colspan='2'>".gettext("No relationships for this contact group")."</td></tr>\n";
				
	foreach($results as $r) 
		$html .="<tr><td>".$r['host_name']."</td><td style='text-align:center;'>
		<input class='host' type='checkbox' name='hostschecked[]' value='".$r['id']."' /></td></tr>\n"; 
	
	$html .="</table></div>"; //close first table 
	$html .= "<div class='rightBox'>
			<h4>".gettext("Service directly assigned to contact").": {$contactgroup}</h4>
			<p class='ccm-label'>".gettext("Check any relationships you wish to")." <strong>".gettext("remove")."</strong></p>
			<table class='standardtable' style='text-align:center;'>
			<tr>
				<th>".gettext("Config Name")."</th><th>".gettext("Service Description")."</th>							
				<th>".gettext("Assigned as Contact")."<br />
				<a id='checkAllservice' style='float:none;' title='Check All' href='javascript:checkAllType(\"service\");'>".gettext("Check All")."</a>
				</th></tr>\n"; 
	
	//get option list 			
	$query = "SELECT `id`,`config_name`,`service_description` FROM `tbl_lnkServiceToContactgroup` LEFT JOIN `tbl_service` ON `idMaster` = `id` WHERE `idSlave` = '{$id}'"; 
	$results  = $ccmDB->query($query); 			
	//sql_output(); 
	//ccm_array_dump($results);
	
	if(empty($results)) 
		$html .="<tr><td colspan='3'>".gettext("No relationships for this contact group")."</td></tr>\n";		
	//display list 
	foreach($results as $r) 
		$html .="<tr><td>".$r['config_name']."</td><td>".$r['service_description']."</td><td style='text-align:center;'>
		<input class='service' type='checkbox' name='serviceschecked[]' value='".$r['id']."' /></td></tr>\n"; 
	
	$html .="</table></div>";			
//	}	
	return $html; 
	
}



?>
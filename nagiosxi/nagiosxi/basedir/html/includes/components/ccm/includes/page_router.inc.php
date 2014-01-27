<?php  //page_router.php


/** function page_router() 
*
*	main page content routing function.  Handles ALL requests for action in the CCM to build appropriate page
*
*	@global object $Menu menu object for navigation menu 
*	@global object $myDataClass NagiosQL object to work with copy and delete commands 
*	@global bool $AUTH main authorization boolean
*	@return string $html html page output
*/
function page_router()
{
	global $Menu; 
	global $myDataClass;
	global $AUTH; 
	
	//DEBUG 
	if($debug=ccm_grab_array_var($_SESSION,'debug',false)) {
		ccm_array_dump($_REQUEST); 
	}

	//process input variables 	
	$cmd = ccm_grab_request_var('cmd',"");
	$type = ccm_grab_request_var('type',"");
	$id = ccm_grab_request_var('id',false); 
	$objectName = ccm_grab_request_var('objectName',''); 
	$token = ccm_grab_request_var('token',''); 
	//authorization check 
	if($AUTH!==true) $cmd = 'login'; 
	//verify that the command was submitted from the form, route to login page if it's an illegal operation 
	verify_token($cmd,$token); 
	//XSS check 
	verify_type($cmd,$type); 
	//echo "CMD: $cmd, TYPE: $type"; 
			
	switch($cmd)
	{
		case 'login':
			include_once(TPLDIR.'login.inc.php'); 
			$html = build_login_form();				 
		return $html; 
		
		case 'logout':
			//kill session vars 
			//echo "LOGGING OUT!"; 
			$username = $_SESSION['ccm_username']; 
			
			unset($_SESSION['ccm_username']); 
		 	unset($_SESSION['ccm_login']); 
		 	unset($_SESSION['token']); 
		 	unset($_SESSION['loginMessage']);  
		 	unset($_SESSION['startsite']);
	 		//$_SESSION['username'] = $username;   //don't kill nagios XI login 
	 		unset($_SESSION['keystring']); 
	 		unset($_SESSION['strLoginMessage']); 
			
			audit_log(AUDITLOGTYPE_SECURITY,$username.gettext(" successfully logged out of Nagios CCM"));
	 		
			include_once(TPLDIR.'login.inc.php'); 
			$html = build_login_form();	
		return $html; 		
	
		case 'view':
			$Menu->print_menu_html(); 			
			$args = route_view($type);
			$html = ccm_table($args);
		return $html;   
		
		case 'admin':
			$Menu->print_menu_html(); 			
			$html = route_admin_view($type);
		return $html;
		
		case 'delete':
			//build page  
			if($type=='user') //special cases for user 
				$html = route_admin_view($type);
			else {
				include_once(INCDIR.'delete_object.inc.php');		
				$returnContent = delete_object($type,$id);
				$args = route_view($type);
				$Menu->print_menu_html(); 
				$html = ccm_table($args,$returnContent); 
			}
		return $html;
		
		case 'delete_multi':
			include_once(INCDIR.'delete_object.inc.php');
			$returnContent = delete_multi($type); 
			//build page 
			$args = route_view($type); 
			$Menu->print_menu_html(); 
			$html = ccm_table($args,$returnContent); 
		return $html; 	
		
		case 'deactivate':
		case 'deactivate_multi':
		case 'activate':
		case 'activate_multi':
			include_once(INCDIR.'activate.inc.php');
			$returnContent = route_activate($cmd,$type,$id); 
			//build page 
			$args = route_view($type); 
			$Menu->print_menu_html(); 
		return ccm_table($args,$returnContent); 		
		
		case 'modify':
		case 'insert':
			$ccmDB = new Db; //instantiate DB object 
			$FIELDS = array(); //global form fields array 
			//bail without a type 
			if($type=='') return;				
			//build appropriate form 
			$Form = new Form($type,$cmd);  //instantiate new form object
			//preload the form with data when necessary, else blank form 
			$Form->prepare_data();
			$Form->build_form();
			//no return output, form printed as direct browser output with return links 
		break; 
		
		case 'copy':  //set for single copy 
			$keyField = $myDataClass->getKeyField($type); 
			//echo "$type $keyField $id <br />"; 
			$bool = $myDataClass->dataCopyEasy('tbl_'.$type,$keyField,$id); 
			$returnContent = array($bool,$myDataClass->strDBMessage); 
			//build page 
			$args = route_view($type);
			$Menu->print_menu_html(); 
			$html = ccm_table($args,$returnContent); 
		return $html;  
 
		
		case 'copy_multi':
			$checks = ccm_grab_request_var('checked',array()); 
			$keyField = $myDataClass->getKeyField($type);
			$copyCount=0;
			$failCount=0; 
			$returnMessage=''; 
			foreach($checks as $id) {
				$bool = $myDataClass->dataCopyEasy('tbl_'.$type,$keyField,$id);
				//if copy successful 
				if($bool==0) $copyCount++; 
				else $failCount++;//copy failed 					
				//Feedback message 							
				$returnMessage.=$myDataClass->strDBMessage."<br />"; 
			}
			//determine return status and message 
			if($copyCount==0) 	$returnContent = array(1,"<strong>".gettext("No objects copied.")."</strong><br />".$returnMessage); 
			elseif($failCount > 0)  $returnContent = array(1,"$copyCount ".gettext("objects copied").".<strong>$failCount ".gettext("objects failed to copy.")."</strong><br />".$returnMessage);
			else $returnContent = array(0,"$copyCount ".gettext("objects copied succesfully!")."<br />".$returnMessage);
			//build page 
			$args = route_view($type);
			$Menu->print_menu_html(); 
			$html = ccm_table($args,$returnContent); 
		return $html;  				
		
		case 'info':
			$table = 'tbl_'.$type; 
			$myDataClass->fullTableRelations($table,$arrRelations);
			$bool = $myDataClass->infoRelation($table,$id,"id",1);
			$returnMessage = "<h3 class='h3_dbRelations'>".gettext("Database Relationships for")." $type : $objectName</h3>
									".gettext("Items labeled as: 'Dependent relationships' will prohibit deletion")."<br />".
									$myDataClass->strDBMessage; 
			$returnContent = array(0,$returnMessage);   				
			//build page 
			$args = route_view($type);
			$Menu->print_menu_html(); 
			$html = ccm_table($args,$returnContent); 
		return $html; 						
					
		case 'purge':
		//do stuff 
		break;
		
		case 'submit':
			//submit objecy form and return status array 
			$returnContent = route_submission($type);  				
			//build page 
			$Menu->print_menu_html(); 
			$args = route_view($type);				
			$html = ccm_table($args,$returnContent); 
		return $html; 		
		
		case 'apply':
			$Menu->print_menu_html(); 
			require_once(INCDIR.'applyconfig.inc.php'); 
			$html = apply_config($type);
		return $html; 
		
		case 'default':
		default:
			//build page 
			$Menu->print_menu_html(); 
			$html = default_page(); 
		return $html;  
	}//end switch 	

} //end page_router() definition 





/** function route_view() 
*
*	determines and fetches information to be presented in in the main CCM display tables based on object $type 
*	@param string $type Nagios object type (host,service,contact, etc) 
*	@TODO add cases for remaining nagios object configurations (depencencies, escalations, etc)  
*	@return array $return_args['data'] array of filtered DB results
*									['keyName'] string used for table header
*									['keyDesc'] string used for table description 
*									['config_names'] array of config_names for services table | empty array 					
*/
function route_view($type,$returnData=array())
{
	global $ccmDB;
	global $request; 
	$txt_search = ccm_grab_request_var('search',''); 
	$name_filter = ccm_grab_request_var('name_filter',''); 
	$orderby = ccm_grab_request_var('orderby',''); 
	$sort = ccm_grab_request_var('sort','ASC'); 
	$sortlist = ccm_grab_request_var('sortlist',false); 
	
	$query = ''; 
	$session_search = ccm_grab_array_var($_SESSION,$type.'_search',''); 
	
	//get relevant entries	
	list($table,$typeName,$typeDesc) = get_table_and_fields($type); 	
												
	//required params for standard views 
	if(isset($typeName, $typeDesc))
	{
		//build SQL query based on filters and type 
		//XXX TODO: grab only the fields that we need for the main display 
		$query = "SELECT id,{$typeName},{$typeDesc},last_modified,active FROM `{$table}` WHERE `config_id`={$_SESSION['domain']} ";  //allow for filtering later on 	
		//search filters 
		$searchQuery='';  //filter results from search 
		$config_names = array();
		//if clear has been pressed, clear search values
		if($txt_search=='false' || (isset($_POST['search']) && $_POST['search']=='') ) {
			$txt_search='';
			$session_search='';
			unset($_SESSION[$type.'_search']); 
			unset($request['search']); 
		}
		if ($txt_search != "" || $session_search != '') 
		{
			//use text search first, else use what is in the session
			$search = ($txt_search!='') ? $txt_search : $session_search; 
			$searchQuery = "AND (`$typeName` LIKE '%".$search."%' OR `$typeDesc` LIKE '%".$search."%'"; 		    
	    	if($type=='host')
	    		$searchQuery.=" OR `display_name` LIKE '%".$search."%' OR `address` LIKE '%".$search."%'";
	    	$searchQuery .=')'; 	
	  	}	
		
	  	//"config_name" filter only used on services page
	  	if($name_filter!='' && $name_filter != 'null')  			 
			$_SESSION['name_filter'] = $name_filter; 
			
		//clear name filter is empty has been selected OR if clear button has been pressed 
		if($name_filter=='null' || $txt_search=='false')
			unset($_SESSION['name_filter']);			
			
		//add to query if relevant
		if($type=='service' && isset($_SESSION['name_filter']) && $_SESSION['name_filter'] !='' && $_SESSION['name_filter'] !='null')
			$query.="AND `config_name`='{$_SESSION['name_filter']}' ";	
			
		if($sortlist!='false' && $sortlist!=false)
			$query.=" ORDER BY `$orderby`"; 
		else
			$query.= "$searchQuery ORDER BY `$typeName`";
		if($type=='service')
				$query.=",`service_description` "; //secondary order by is not working correctly 
		//ASC | DESC?		
		$query.= " {$sort} ";	
			
	  	//grab config names for services page if needed 		  	  
	  	if($typeName=='config_name') 
	  		$config_names = $ccmDB->query("SELECT DISTINCT config_name FROM tbl_service;");  

		//print "$typeName : $typeDesc <br />";
		 
		//echo $query."<br />"; 
		//build return array  
		$return_args = array( 	'data' => $ccmDB->query($query), //database results 
										'keyName' => $typeName, 		//object type 
										'keyDesc' => $typeDesc,			//description/alias field 
										'config_names' => $config_names, 
									);
		//echo $ccmDB->last_query;							
		return $return_args;
	}
	else 
		trigger_error(gettext('Unable to route view, missing neccessary values')."<br />",E_USER_ERROR);  

}//end route_view() 






/** function route_submission 
*
*	switch that handles submissions for adding and modifying config objects 
*	@param string $type nagios object type (host,service,contact, etc) 
*	@return array $returnData (int exitCode, string exitMessage) 
*
*/
function route_submission($type)
{

	$returnData = array(0,''); //initialize return data array 
		
	switch($type)
	{
		case 'host':
		case 'service':
		case 'hosttemplate';
		case 'servicetemplate':	
			require_once('hostservice.inc.php'); 	
			$returnData = process_ccm_submission();
		break;
		case 'hostgroup':
		case 'servicegroup':
		case 'contactgroup':
			require_once(INCDIR.'group.inc.php'); 
			$returnData =process_ccm_group();
		break;
		case 'timeperiod':
			require_once(INCDIR.'objects.inc.php'); 
			$returnData =process_timeperiod_submission(); 
		break;
		case 'command':
			require_once(INCDIR.'objects.inc.php'); 
			$returnData =process_command_submission(); 
		break; 	
		case 'contact':
		case 'contacttemplate':
			require_once(INCDIR.'contact.inc.php');
			$returnData =process_contact_submission(); 
		break;
		
		case 'serviceescalation':
		case 'hostescalation':
			require_once(INCDIR.'objects.inc.php');
			$returnData = process_escalation_submission(); 
		break; 
		
		case 'servicedependency':
		case 'hostdependency':
			require_once(INCDIR.'objects.inc.php');
			$returnData = process_dependency_submission(); 
		break;
		
		default:
			$returnData =array(1, "Missing arguments! No type specified for 'route_submission()'"); 
		break;
				
	}
	//print_r($returnData); 
	return $returnData; 

}//end route_submission() 


function route_admin_view($type)
{
	global $ccmDB;
	require_once(INCDIR.'admin_views.inc.php'); 
		
	switch($type) 
	{
		case 'user':
		$mode = ccm_grab_request_var('mode',false);
		$returnData = array(0,''); 							
		//handle submissions							
		if($mode=='insert' || $mode == 'modify') 
			$returnData = process_user_submission(); 
					
		$query = "SELECT `id`,`username`,`alias`,`active` FROM `tbl_user`;";
		$return_args = array( 	'data' => $ccmDB->query($query), //database results 
								'keyName' => 'username', 		//object type 
								'keyDesc' => 'alias',			//description/alias field 
								'config_names' => array() );			
		 																				
		return ccm_table($return_args,$returnData);					
 							
		case 'import':
		return ccm_import_page(); 		

		case 'corecfg':
		return ccm_corecfg();

		case 'cgicfg': 
		return ccm_cgicfg();
		
		case 'log':
			require_once(INCDIR.'ccm_log.inc.php');
		return ccm_log();
		
		case 'settings':
		return ccm_settings();
		
		//case 'bulk':
		//	require_once(INCDIR.'ccm_bulk_edit.inc.php'); 
		//return ccm_bulk_edit(); 
		
		case 'static':
		return ccm_static_editor(); 
		
		default:
		return default_page(); 		
	}
}




?>
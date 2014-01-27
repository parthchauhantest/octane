<?php

/** function process_ccm_submission() 
*	handles form submissions for all host,service,hosttempate, and servicetemplate object configurations 
*
*	@global object $myVisClass nagiosql templating handler
*	@global object $myDataClass nagiosql data handler
*	@global object $myConfigClass nagiosql config handler  	
*	@global object $myDBClass nagiosql database handler
*	@return array array(int $returnCode,string $returnMessage) return output for browser
*/
function process_ccm_submission()
{
 
	global $myVisClass;
	global $myDataClass;
	global $myConfigClass; 	
	global $myDBClass;
		
	// Declaring variables
	// =====================
	$strMessage     = "";
	$errors = 0;  
	
//	grab_request_vars(); 
	$chkModus = ccm_grab_request_var('mode');  
	$chkDataId = ccm_grab_request_var('hidId'); 
	$exactType = ccm_grab_request_var('exactType');  
	$genericType = ccm_grab_request_var('genericType'); 
	$ucType = ucfirst($exactType); 
	
	//grabbing all $_REQUEST variables 
	// =================
	$chkTfSearch      = ccm_grab_request_var('txtSearch');
	$chkTfName        = ccm_grab_request_var('tfName','');
	$chkOldHost       = ccm_grab_request_var('hidName','');
	$chkServiceDesc 	= ccm_grab_request_var('tfServiceDescription',''); 
	$chkTfFriendly    = ccm_grab_request_var('tfFriendly','',true);
	$chkTfDisplay     = ccm_grab_request_var('tfDisplayName','',true);
	$chkTfAddress     = ccm_grab_request_var('tfAddress','') ;
	$chkTfGenericName = ccm_grab_request_var('tfGenericName','');
	//host assignsments 
	$chkSelHosts        = ccm_grab_request_var('hosts',array(""));
	$chkRadHosts        = ccm_grab_request_var('radHost',2);        
	//servicegroups
	$chkSelServiceGroups    = ccm_grab_request_var('servicegroups', array(""));
	$chkRadServiceGroups    = ccm_grab_request_var('radServicegroup',2); 
	//$chkSelParents      = ccm_grab_request_var('selParents'])       ? $_POST['selParents']            : array("");
	$chkSelParents    = ccm_grab_request_var('parents', array('') );	
	$chkRadParent     = ccm_grab_request_var('radParent', 2); 
	//$chkSelHostGroups     = ccm_grab_request_var('selHostGroups'])    ? $_POST['selHostGroups']           : array("");
	$chkSelHostGroups = ccm_grab_request_var('hostgroups', array('') );
	$chkRadHostGroups = ccm_grab_request_var('radHostgroup', 2);
	$chkSelHostCommand= ccm_grab_request_var('selHostCommand',0);
	$chkTfArg1        = ccm_grab_request_var('tfArg1','',true);
	$chkTfArg2        = ccm_grab_request_var('tfArg2','',true);
	$chkTfArg3        = ccm_grab_request_var('tfArg3','',true);
	$chkTfArg4        = ccm_grab_request_var('tfArg4','',true);
	$chkTfArg5        = ccm_grab_request_var('tfArg5','',true);
	$chkTfArg6        = ccm_grab_request_var('tfArg6','',true);
	$chkTfArg7        = ccm_grab_request_var('tfArg7','',true);
	$chkTfArg8        = ccm_grab_request_var('tfArg8','',true);
	$chkRadTemplates  = ccm_grab_request_var('radTemplate',2);   


	//TODO - fix this, logic won't be right 
	$chkTfRetryInterval   	= checkNull(ccm_grab_request_var('tfRetryInterval', 'NULL') );   // && ($_POST['tfRetryInterval'] != ""))   ? $myVisClass->checkNull($_POST['tfRetryInterval'])   : "NULL";
	$chkTfMaxCheckAttempts  = checkNull(ccm_grab_request_var('tfMaxCheckAttempts', 'NULL') ); 
	$chkTfCheckInterval   	= checkNull(ccm_grab_request_var('tfCheckInterval', 'NULL') ); 
	
	//Active/Passive checks 
	$chkActiveChecks    = ccm_grab_request_var('radActiveChecksEnabled',2);
	$chkPassiveChecks   = ccm_grab_request_var('radPassiveChecksEnabled',2);  
	$chkSelCheckPeriod    = ccm_grab_request_var('selCheckPeriod',2);    
	$chkTfFreshTreshold   = checkNull(ccm_grab_request_var('tfFreshThreshold','NULL') );   
	$chkFreshness       = ccm_grab_request_var('radFreshness',2);
	$chkObsess          = ccm_grab_request_var('radObsess',2); 
	$chkSelEventHandler = checkNull( ccm_grab_request_var('selEventHandler','NULL')  );
	$chkEventEnable     = ccm_grab_request_var('radEventEnable',2); 
	$chkTfLowFlat       = checkNull( ccm_grab_request_var('tfLowFlat','NULL') );    
	$chkTfHighFlat      = checkNull(ccm_grab_request_var('tfHighFlat', 'NULL') ) ;
	$chkFlapEnable      = ccm_grab_request_var('radFlapEnable',2); 
	$chkIsVolatile		= ccm_grab_request_var('radIsVolatile',2); 
	
	//////////////////////////////////////////////////////////////////////
	//options checkboxes: flapping, stalking, notification options  
	$strFL = get_FL_string($exactType); 
	$strST = get_ST_string($exactType);
	$strNO = get_NO_string($exactType); 
	
	$strIS = (ccm_grab_request_var('radIS','') =='') ? '' : ccm_grab_request_var('radIS');
	//retain status 
	$chkStatusInfos     = intval(ccm_grab_request_var('radStatusInfos',2)); 
	$chkNonStatusInfos  = intval(ccm_grab_request_var('radNoStatusInfos',2)); 
	$chkPerfData        = intval(ccm_grab_request_var('radPerfData',2)); 
	
	//contacts 
	$chkSelContacts       = ccm_grab_request_var('contacts', array(''));  
	$chkSelContactGroups  = ccm_grab_request_var('contactgroups', array('') );   
	$chkRadContacts       = intval(ccm_grab_request_var('radContact',2));
	$chkRadContactGroups  = intval(ccm_grab_request_var('radContactgroup',2)); 
	
	//notifications 
	$chkSelNotifPeriod  = ccm_grab_request_var('selNotifPeriod',2)+0; 
	$chkNotifInterval   = checkNull( ccm_grab_request_var('tfNotifInterval', 'NULL') );  
	$chkNotifDelay      = checkNull( ccm_grab_request_var('tfFirstNotifDelay', 'NULL') );
	$chkNotifEnabled    = ccm_grab_request_var('radNotifEnabled',2);
	
	// misc settings 
	$chkTfNotes         = ccm_grab_request_var('tfNotes','');
	$chkTfVmrlImage     = ccm_grab_request_var('tfVmrlImage','');
	$chkTfNotesURL      = ccm_grab_request_var('tfNotesURL','',true);
	$chkTfStatusImage   = ccm_grab_request_var('tfStatusImage','');
	$chkTfActionURL     = ccm_grab_request_var('tfActionURL','',true);
	$chkTfIconImage     = ccm_grab_request_var('tfIconImage','');
	$chkTfD2Coords      = ccm_grab_request_var('tfD2Coords','');
	$chkTfIconImageAlt  = ccm_grab_request_var('tfIconImageAlt','');
	$chkTfD3Coords      = ccm_grab_request_var('tfD3Coords','');
	//active? 
	$chkActive 				= ccm_grab_request_var('chbActive',0);  
	//hidden debugger 
	//$showsql = ccm_grab_request_var
	

	// Check for templates 
	// =================================
	$templates = ccm_grab_request_var('templates',array()); 
	//are templates being used? 
	$intTemplates = (count($templates) > 0) ? 1 : 0;  
	
	//check for Free Variables 
	// ================================ 	
	$variables = ccm_grab_request_var('variables', array(), true) ; 
	$definitions = ccm_grab_request_var('variabledefs', array(), true); 
	
	//ccm_array_dump($definitions);
	
	//freeform variables being used?  
	$intVariables = (count($variables) ) > 0 ? 1 : 0;  
	
	//domain ID for now 
	$chkDomainId = $_SESSION['domain']; //domain is localhost 

	// Data post-processing
	// =================
	//if ($chkISnull == "") {$strIS = substr($chkISo.$chkISd.$chkISu,0,-1);} else {$strIS = "null";}
	//if ($chkFLnull == "") {$strFL = substr($chkFLo.$chkFLd.$chkFLu,0,-1);} else {$strFL = "null";}
	//if ($chkNOnull == "") {$strNO = substr($chkNOd.$chkNOu.$chkNOr.$chkNOf.$chkNOs,0,-1);} else {$strNO = "null";}
	//if ($chkSTnull == "") {$strST = substr($chkSTo.$chkSTd.$chkSTu,0,-1);} else {$strST = "null";}
	
	if (($chkSelParents[0] == "")     || ($chkSelParents[0] == "0"))     {$intSelParents = 0;}     else {$intSelParents = 1;}
	if (($chkSelHostGroups[0] == "")    || ($chkSelHostGroups[0] == "0"))    {$intSelHostGroups = 0;}    else {$intSelHostGroups = 1;}
	if (($chkSelContacts[0] == "")    || ($chkSelContacts[0] == "0"))    {$intSelContacts = 0;}    else {$intSelContacts = 1;}
	if ($chkSelContacts[0] == "*")        $intSelContacts = 2;
	if (($chkSelContactGroups[0] == "") || ($chkSelContactGroups[0] == "0")) {$intSelContactGroups = 0;} else {$intSelContactGroups = 1;}
	if ($chkSelContactGroups[0] == "*")     $intSelContactGroups = 2;
		//service relationships 
	if (($chkSelHosts[0] == "")       || ($chkSelHosts[0] == "0"))  {$intSelHosts = 0;}     else {$intSelHosts = 1;}
	if ($chkSelHosts[0] == "*")     $intSelHosts = 2;
	if (($chkSelServiceGroups[0] == "") || ($chkSelServiceGroups[0] == "0"))  {$intSelServiceGroups = 0;} else {$intSelServiceGroups = 1;}

	// Check Command compile
	$strCheckCommand = $chkSelHostCommand;
	if ($chkSelHostCommand != "") {
	  for ($i=1;$i<=8;$i++) {
	  	// XI MOD 02-10-2010 EG - Added support for empty $ARGx$ macros
	  	$strCheckCommand .= "!".${"chkTfArg$i"};
	  	/*
	    if (${"chkTfArg$i"} != "") $strCheckCommand .= "!".${"chkTfArg$i"};
	    */   
	  }
	}
		
	
		
	/////////////////////////////////////INSERT/MODIFY/////////////////////////////////// 
		
	// Modify or add files
	if (($chkModus == "insert") || ($chkModus == "modify")) 
	{
		$table = 'tbl_'.$exactType;  	
		//begin SQL query build 
	  $strSQLx = "`$table` SET ";
	  //define field entries based on $exactType 
	  //host specific
	  if($exactType=='host') $strSQLx .= "`host_name`='$chkTfName', `alias`='$chkTfFriendly', `address`='$chkTfAddress', 
											`parents`=$intSelParents, `parents_tploptions`=$chkRadParent, \n";
	  //hosttemplate specific 
	  if($exactType=='hosttemplate' ) 
	  		$strSQLx.=" `parents`=$intSelParents, `parents_tploptions`=$chkRadParent,`alias`='$chkTfFriendly',";  													
	  //template specific 
	  if($exactType=='hosttemplate' || $exactType=='servicetemplate') 
	  		$strSQLx .= "`template_name`='$chkTfName',\n"; 	  
	  //display name field 
	  if($exactType=='host' || $exactType=='service' || $exactType=='servicetemplate') $strSQLx .="`display_name`='$chkTfDisplay',\n";
	  
	  if($exactType=='service') $strSQLx .="`config_name`='$chkTfName',\n";  
	  if($exactType=='service' || $exactType=='servicetemplate') $strSQLx .="`service_description`='$chkServiceDesc',"; 
	  
	  //common fields 
	//  $strSQLx.="
	  //      `name`='$chkTfGenericName', ";
	        
	  if($exactType=='host' || $exactType =='hosttemplate')
	    $strSQLx.= "`hostgroups`=$intSelHostGroups, `hostgroups_tploptions`=$chkRadHostGroups, `obsess_over_host`=$chkObsess,\n";	
	            
	  if($exactType=='service' || $exactType =='servicetemplate')
	  {
		    $strSQLx.= "`hostgroup_name`=$intSelHostGroups, `hostgroup_name_tploptions`=$chkRadHostGroups,\n
	    					`servicegroups`=$intSelServiceGroups, `servicegroups_tploptions`=$chkRadServiceGroups, 
	    						`host_name`='$intSelHosts', `host_name_tploptions`='$chkRadHosts', `is_volatile`=$chkIsVolatile, `obsess_over_service`=$chkObsess, ";
	   }
	    
	  $strSQLx .="  
	        `check_command`='$strCheckCommand', `use_template`=$intTemplates,\n
	        `use_template_tploptions`=$chkRadTemplates, `initial_state`='$strIS', `max_check_attempts`=$chkTfMaxCheckAttempts,\n
	        `check_interval`=$chkTfCheckInterval, `retry_interval`=$chkTfRetryInterval, `active_checks_enabled`=$chkActiveChecks,\n
	        `passive_checks_enabled`=$chkPassiveChecks, `check_period`=$chkSelCheckPeriod, \n
	        `check_freshness`=$chkFreshness, `freshness_threshold`=$chkTfFreshTreshold, `event_handler`=$chkSelEventHandler,\n
	        `event_handler_enabled`=$chkEventEnable, `low_flap_threshold`=$chkTfLowFlat, `high_flap_threshold`=$chkTfHighFlat,\n
	        `flap_detection_enabled`=$chkFlapEnable, `flap_detection_options`='$strFL', `process_perf_data`=$chkPerfData,\n
	        `retain_status_information`=$chkStatusInfos, `retain_nonstatus_information`=$chkNonStatusInfos,`contacts`=$intSelContacts,\n
	        `contacts_tploptions`=$chkRadContacts, `contact_groups`=$intSelContactGroups, `contact_groups_tploptions`=$chkRadContactGroups,\n
	        `notification_interval`=$chkNotifInterval, `notification_period`=$chkSelNotifPeriod,\n
	        `first_notification_delay`=$chkNotifDelay, `notification_options`='$strNO', `notifications_enabled`=$chkNotifEnabled,\n
	        `stalking_options`='$strST', `notes`='$chkTfNotes', `notes_url`='$chkTfNotesURL', `action_url`='$chkTfActionURL',\n
	        `icon_image`='$chkTfIconImage', `icon_image_alt`='$chkTfIconImageAlt',`active`='$chkActive',`use_variables`=$intVariables,\n
	        `config_id`=$chkDomainId, `last_modified`=NOW() \n";
	   
	  if($exactType=='host' || $exactType=='service')  $strSQLx .=",`name`='$chkTfGenericName' "; 
	  //fields for host and hosttemplate  
	  if($exactType=='host' || $exactType=='hosttemplate')     
	  	$strSQLx.=",`vrml_image`='$chkTfVmrlImage',`statusmap_image`='$chkTfStatusImage', `2d_coords`='$chkTfD2Coords', `3d_coords`='$chkTfD3Coords' \n";
	        
	  //echo "Query built is: $strSQLx <br />"; 
	  	
	  //DEFINE $strSQL      
	  if ($chkModus == "insert")  $strSQL = "INSERT INTO ".$strSQLx;
	  //mode = modify 
	  else  $strSQL = "UPDATE ".$strSQLx." WHERE `id`=$chkDataId";
	 	
	 //DEBUG 	   
	  //print "Full query is: $strSQL <br />"; 
	
		//hostname is required 
	  if ($chkTfName != "") 
	  {
	    $intInsert = $myDataClass->dataInsert($strSQL,$intInsertId);
	    //error handling, bail if the initial query fails  
	    if($intInsert > 0)
	    {	
	    	$strMessage.=$myDataClass->strDBMessage; 
	    	 $errors++; 
	    	 return array($errors,$strMessage); 
	    }	 
	    //DEBUG 
	    //print "intInsert is: $intInsert"; 
	   // print "<p>SQL Response: ".mysql_error()."<br /> Rows affected: ".$myDBClass->intAffectedRows."</p>"; 
	    	    	    
	    if ($chkModus == "insert") $chkDataId = $intInsertId;
	    
	    if ($intInsert == 1) 
	    {
	      $strMessage = $myDataClass->strDBMessage;
	      $intReturn = 1;
	    } 
	    else 
	    {
	      if ($chkModus  == "insert")   $myDataClass->writeLog(gettext('New '.$exactType.' inserted:')." ".$chkTfName);
	      if ($chkModus  == "modify")   $myDataClass->writeLog(gettext($ucType.' modified:')." ".$chkTfName);
	      //
	      // Relations Register / update
	      // ============================
	      if ($chkModus == "insert") 
	      {		//service-specific relations 
	      
	      		//DEBUG 
					//print_r($chkSelServiceGroups); 
					//print_r($chkSelHostGroups); 	      
	      
	      	if($exactType =='service' || $exactType=='servicetemplate') 
	      	{
					if ($intSelServiceGroups == 1)  
						$myDataClass->dataInsertRelation("tbl_lnk".$ucType."ToServicegroup",$chkDataId,$chkSelServiceGroups);	
					if($intSelHosts == 1) 	
						$myDataClass->dataInsertRelation("tbl_lnk".$ucType."ToHost",$chkDataId,$chkSelHosts);      	
	      	}
				//host specific 
	        if (($exactType=='host' || $exactType=='hosttemplate' ) && $intSelParents == 1)  
	        			$myDataClass->dataInsertRelation("tbl_lnk".$ucType."ToHost",$chkDataId,$chkSelParents);
	        if ($intSelHostGroups    == 1)  $myDataClass->dataInsertRelation("tbl_lnk".$ucType."ToHostgroup",$chkDataId,$chkSelHostGroups);	        
	        if ($intSelContacts    == 1)  $myDataClass->dataInsertRelation("tbl_lnk".$ucType."ToContact",$chkDataId,$chkSelContacts);
	        if ($intSelContactGroups == 1)  $myDataClass->dataInsertRelation("tbl_lnk".$ucType."ToContactgroup",$chkDataId,$chkSelContactGroups);
	      } 
	      
	      /////////////////////////////////////process table relations //////////////////////////
	      elseif  ($chkModus == "modify") 
	      {	
	        if($exactType=='host' || $exactType=='hosttemplate')
	        {
	        		//parents 
	        		if ($intSelParents == 1) $myDataClass->dataUpdateRelation("tbl_lnk".$ucType."ToHost",$chkDataId,$chkSelParents);							        
	         	else $myDataClass->dataDeleteRelation("tbl_lnk".$ucType."ToHost",$chkDataId);
				}
				//hostgroups 
	        if ($intSelHostGroups == 1) $myDataClass->dataUpdateRelation("tbl_lnk".$ucType."ToHostgroup",$chkDataId,$chkSelHostGroups);
	        else $myDataClass->dataDeleteRelation("tbl_lnk".$ucType."ToHostgroup",$chkDataId);
       
				//contacts 
	        if ($intSelContacts == 1) $myDataClass->dataUpdateRelation("tbl_lnk".$ucType."ToContact",$chkDataId,$chkSelContacts);
	        else $myDataClass->dataDeleteRelation("tbl_lnk".$ucType."ToContact",$chkDataId);
				//contact groups 
	        if ($intSelContactGroups == 1) $myDataClass->dataUpdateRelation("tbl_lnk".$ucType."ToContactgroup",$chkDataId,$chkSelContactGroups);
	        else  $myDataClass->dataDeleteRelation("tbl_lnk".$ucType."ToContactgroup",$chkDataId);
	        //handle service-specific relations 
	        if($exactType == 'service' || $exactType =='servicetemplate')
	        {
		        //hosts   
		        if ($intSelHosts == 1) $myDataClass->dataUpdateRelation("tbl_lnk".$ucType."ToHost",$chkDataId,$chkSelHosts);
	         	else  $myDataClass->dataDeleteRelation("tbl_lnk".$ucType."ToHost",$chkDataId);     
	         	//servicegroups 
	        	  if ($intSelServiceGroups == 1) $myDataClass->dataUpdateRelation("tbl_lnk".$ucType."ToServicegroup",$chkDataId,$chkSelServiceGroups);
	           else  $myDataClass->dataDeleteRelation("tbl_lnk".$ucType."ToServicegroup",$chkDataId);  	        
	        }
	        
	        
	      }  //end ELSEIF $checkModus=='modify'
	      
		  ///////////////////////////////////////////////////////////////////////
		  // If the host/config name was changed, delete old configuration        
		  //FIND $chkOldHost value 
		  if (($chkModus == "modify") && ($chkOldHost != $chkTfName) && ($exactType=='host' || $exactType=='service'))  {
			$intReturn = $myConfigClass->moveFile($exactType,$chkOldHost.".cfg");
			if ($intReturn == 0) {
				$strMessage .=  gettext('The assigned, no longer used configuration files were deleted successfully!');
				$myDataClass->writeLog(gettext('Configuration file deleted:')." ".$chkOldHost.".cfg");
			} 
			else {
				$strMessage .=  gettext('Errors while deleting the old configuration file - please check!:')."<br>".$myConfigClass->strDBMessage;
				$errors++; 				  
			}		      
		  }
	      //////////////////////////////////////////////////////////////////////
	      // If the host has been disabled - Delete File
	      if (($chkModus == "modify") && ($chkActive == 0) && ($exactType=='host' || $exactType=='service' ) ) {
      		$moveType = $exactType; 
      		$cfg = $chkTfName.".cfg";
	      	
	      	//echo "$cfg<br />";
	        $intReturn = $myConfigClass->moveFile($moveType,$cfg);
	        if ($intReturn == 0) {
	          $strMessage .=  gettext('The assigned, no longer used configuration files were deleted successfully!<br />');
	          $myDataClass->writeLog(gettext('Config file deleted:')." ".$cfg);
	        } 
	        else {
	          	$strMessage .=  gettext('Errors while deleting the old configuration file: '.$cfg.' - please check permissions!')."<br>".$myConfigClass->strDBMessage;
	        	 $errors++; 
	        }
	      }
	      /////////////////////////////////////////////////////////
	      // Update Template Relationships 
	      // ========================================   
	      	$tblTemplate = ($exactType =='hosttemplate' || $exactType == 'host') ? 'Hosttemplate' : 'Servicetemplate';    
	      //clear out previous template relationships 
	      if ($chkModus == "modify") {
	        $strSQL   = "DELETE FROM `tbl_lnk".$ucType."To".$tblTemplate."` WHERE `idMaster`=$chkDataId";
	        $booReturn  = $myDataClass->dataInsert($strSQL,$intInsertId);
	      }      
	      ///THERE ARE TEMPLATES! 
	      
	      
	      //if (isset($_SESSION['templatedefinition']) && is_array($_SESSION['templatedefinition']) && (count($_SESSION['templatedefinition']) != 0)) 
			if ($intTemplates = 1)       
	      {
	        $intSortId = 1; //increment counter 
	        
	        /////TEMPLATE DEFS PASSED AS SESSION VARS in NagiosQL!!!        
	        /*template array needs 
	        $chkDataId = current host ID 
	        $idtSortId - array index starting at 1 
	        $t['status'] - NO LONGER USED, only active elements will be sent to form  
	        $t['idSlave'] - template ID number 
	        $t['idTable'] - appears to do NOTHING, always == 1 --> done for backwards compatibility?? -> 'template_name' vs 'name'         
	        */ 
			//TODO - turn this into a function                 
	        //$templates = $_POST['templates']; 
	        foreach($templates as $elem){
		    	$idTable = 1;
		   	 	if(strpos($elem,'::2')) { //hostname as template
					$idTable = 2;
					$elem = str_replace('::2','',$elem); 			
		    	} 
	            $strSQL = "INSERT INTO `tbl_lnk".$ucType."To".$tblTemplate."` (`idMaster`,`idSlave`,`idTable`,`idSort`)
	                   VALUES ($chkDataId, $elem, $idTable , $intSortId)"; 	//NOTE: replaced $elem['idTable'] with 1  
//echo $strSQL.'<br />';           
	            $booReturn  = $myDataClass->dataInsert($strSQL,$intInsertId);   
	          $intSortId++;
	        }
	      } //END IF templates are set 
	            
	      /////////////////CUSTOM VARIABLE DEFS///////////////////////////////////
	      //
	      // Update Variable definition relationships 
	      // ========================================
	      
	      //clear out old variable definition 
	      if ($chkModus == "modify") 
	      {
	        $strSQL   = "SELECT * FROM `tbl_lnk".$ucType."ToVariabledefinition` WHERE `idMaster`=$chkDataId";
	        $booReturn  = $myDBClass->getDataArray($strSQL,$arrData,$intDataCount);
	        if ($intDataCount != 0) 
	        {
	          foreach ($arrData AS $elem) 
	          {
	            $strSQL   = "DELETE FROM `tbl_variabledefinition` WHERE `id`=".$elem['idSlave'];
	            $booReturn  = $myDataClass->dataInsert($strSQL,$intInsertId);
	          }
	        }
	        $strSQL   = "DELETE FROM `tbl_lnk".$ucType."ToVariabledefinition` WHERE `idMaster`=$chkDataId";
	        $booReturn  = $myDataClass->dataInsert($strSQL,$intInsertId);
	      }//end if MODIFY 
	      
	      //if there are variables to insert... 
	      if ($intVariables == 1 ) 
	      {
	      	$vars = $variables;
	      	$defs = $definitions; 	     	  
	      	//print "count of vars is ".count($vars)."<br />"; 
	      	$count=0; 
	        for($i=0; $i<count($vars); $i++)
	        {
	        		//print " <p>count is: $i  current var: {$vars[$i]} Def: {$defs[$i]}</p>";
	            $strSQL = "INSERT INTO `tbl_variabledefinition` (`name`,`value`,`last_modified`)
	                   VALUES ('{$vars[$i]}','".html_entity_decode($defs[$i])."',now())";
	            $booReturn  = $myDataClass->dataInsert($strSQL,$intInsertId);
	            $strSQL = "INSERT INTO `tbl_lnk".$ucType."ToVariabledefinition` (`idMaster`,`idSlave`)
	                   VALUES ($chkDataId,$intInsertId)";
	            $booReturn  = $myDataClass->dataInsert($strSQL,$intInsertId);
	            
	        }//end foreach 
	      }//end IF variables defined  
	      $intReturn = 0;
	    }
	  } 
	  else 
	  {
	    $strMessage .= gettext('Database entry failed! Not all necessary data filled in!');
	    $errors++; 
	  }  
	  
	}  //end if 'modify' || 'insert' 
	if($errors==0) $strMessage.=gettext("Database entry for {$exactType} {$chkTfName} successfully updated!");
	//echo "FORM SUBMITTED!"; 
	return array($errors, $strMessage); 
}	


///////////////HELPER FUNCTIONS///////////////

//flap detection options string 
function get_FL_string($type){
	if($type=='host') { 
		$chkFLo = (ccm_grab_request_var('chbFLo','') =='') ? '' : ccm_grab_request_var('chbFLo').',';    
		$chkFLd = (ccm_grab_request_var('chbFLd','') =='') ? '' : ccm_grab_request_var('chbFLd').',';   
		$chkFLu = (ccm_grab_request_var('chbFLu','') =='') ? '' : ccm_grab_request_var('chbFLu').','; 
		$strFL = $chkFLo.$chkFLd.$chkFLu;
	}
	else { //service 
		$chkFLo = (ccm_grab_request_var('chbFLo','') =='') ? '' : ccm_grab_request_var('chbFLo').',';    
		$chkFLw = (ccm_grab_request_var('chbFLw','') =='') ? '' : ccm_grab_request_var('chbFLw').',';   
		$chkFLc = (ccm_grab_request_var('chbFLc','') =='') ? '' : ccm_grab_request_var('chbFLc').','; 	
		$chkFLu = (ccm_grab_request_var('chbFLu','') =='') ? '' : ccm_grab_request_var('chbFLu').',';
		$strFL = $chkFLo.$chkFLw.$chkFLc.$chkFLu;
	}
	return $strFL; 
}

//notification options string 
function get_NO_string($type){
	if($type=='host' || $type=='hosttemplate') {   
		$chkNOd = (ccm_grab_request_var('chbNOd','') =='') ? '' : ccm_grab_request_var('chbNOd').',';   
		$chkNOu = (ccm_grab_request_var('chbNOu','') =='') ? '' : ccm_grab_request_var('chbNOu').','; 
		$strNO = $chkNOd.$chkNOu;
	}
	else { //service 
		$chkNOw = (ccm_grab_request_var('chbNOw','') =='') ? '' : ccm_grab_request_var('chbNOw').',';   
		$chkNOc = (ccm_grab_request_var('chbNOc','') =='') ? '' : ccm_grab_request_var('chbNOc').','; 	
		$chkNOu = (ccm_grab_request_var('chbNOu','') =='') ? '' : ccm_grab_request_var('chbNOu').',';
		$chkNOo = (ccm_grab_request_var('chbNOo','') =='') ? '' : ccm_grab_request_var('chbNOo').',';
		$strNO = $chkNOo.$chkNOw.$chkNOc.$chkNOu;
	}
	$chkNOr = (ccm_grab_request_var('chbNOr','') =='') ? '' : ccm_grab_request_var('chbNOr').',';  
	$chkNOf = (ccm_grab_request_var('chbNOf','') =='') ? '' : ccm_grab_request_var('chbNOf').',';
	$chkNOs = (ccm_grab_request_var('chbNOs','') =='') ? '' : ccm_grab_request_var('chbNOs').',';
	$strNO.=	$chkNOr.$chkNOf.$chkNOs;
		
	return $strNO; 
}

/*
//initial state string
function get_IS_string($type){
	if($type=='host') {
		$chkISo = (ccm_grab_request_var('chbISo','') =='') ? '' : ccm_grab_request_var('chbISo').',';    
		$chkISd = (ccm_grab_request_var('chbISd','') =='') ? '' : ccm_grab_request_var('chbISd').',';   
		$chkISu = (ccm_grab_request_var('chbISu','') =='') ? '' : ccm_grab_request_var('chbISu').','; 
		$strIS = $chkISo.$chkISd.$chkISu;
	}
	else { //service 
		$chkISo = (ccm_grab_request_var('chbISo','') =='') ? '' : ccm_grab_request_var('chbISo').',';    
		$chkISw = (ccm_grab_request_var('chbISw','') =='') ? '' : ccm_grab_request_var('chbISw').',';   
		$chkISc = (ccm_grab_request_var('chbISc','') =='') ? '' : ccm_grab_request_var('chbISc').','; 	
		$chkISu = (ccm_grab_request_var('chbISu','') =='') ? '' : ccm_grab_request_var('chbISu').',';
		$strIS = $chkISo.$chkISw.$chkISc.$chkISu;
	}
	return $strIS; 

}
*/
//stalking option string 
function get_ST_string($type){
	if($type=='host') {
		$chkSTo = (ccm_grab_request_var('chbSTo','') =='') ? '' : ccm_grab_request_var('chbSTo').',';    
		$chkSTd = (ccm_grab_request_var('chbSTd','') =='') ? '' : ccm_grab_request_var('chbSTd').',';   
		$chkSTu = (ccm_grab_request_var('chbSTu','') =='') ? '' : ccm_grab_request_var('chbSTu').','; 
		$strST = $chkSTo.$chkSTd.$chkSTu;
	}
	else { //service 
		$chkSTo = (ccm_grab_request_var('chbSTo','') =='') ? '' : ccm_grab_request_var('chbSTo').',';    
		$chkSTw = (ccm_grab_request_var('chbSTw','') =='') ? '' : ccm_grab_request_var('chbSTw').',';   
		$chkSTc = (ccm_grab_request_var('chbSTc','') =='') ? '' : ccm_grab_request_var('chbSTc').','; 	
		$chkSTu = (ccm_grab_request_var('chbSTu','') =='') ? '' : ccm_grab_request_var('chbSTu').',';
		$strST = $chkSTo.$chkSTw.$chkSTc.$chkSTu;
	}
	return $strST; 
}

?>

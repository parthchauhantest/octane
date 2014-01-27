<?php //objects.inc.php    

/** function process_timeperiod_submission()
*	handles form submissions for timeperiod object configurations 
*
*	@global object $myVisClass nagiosql templating handler
*	@global object $myDataClass nagiosql data handler
*	@global object $myConfigClass nagiosql config handler  	
*	@global object $myDBClass nagiosql database handler
*	@return array array(int $returnCode,string $returnMessage) return output for browser
*/
function process_timeperiod_submission()
{
	global $myVisClass;
	global $myDataClass;
	global $myConfigClass; 	
	global $myDBClass;

	$strMessage     = "";
	$errors = 0;
	
	//process form variables 
	$chkModus = ccm_grab_request_var('mode');  
	$chkDataId = ccm_grab_request_var('hidId'); 
	$exactType = ccm_grab_request_var('exactType');  
	$genericType = ccm_grab_request_var('genericType'); 
	$ucType = ucfirst($exactType); 

	$chkInsName     = ccm_grab_request_var('tfName','');    
	$chkInsAlias    = ccm_grab_request_var('tfFriendly','');
	$chkInsTplName  = ccm_grab_request_var('tfTplName','');
	$chkSelExclude  = ccm_grab_request_var('excludes',array('')); 
	$chkActive		 = ccm_grab_request_var('chbActive',0);  
	//timeperiod arrays 
	$timeDefs		 = ccm_grab_request_var('timedefinitions',array()); 
	$timeRanges		 = ccm_grab_request_var('timeranges',array()); 
	
	$chkDomainId	 = $_SESSION['domain']; 
	//
	//
	// handle input arrays 
	// =================
	if ( ($chkSelExclude[0] == "") || ($chkSelExclude[0] == "0") ) $intSelExclude = 0;  
	else $intSelExclude = 1;
	
	//build SQL query 
	$strSQLx = "`tbl_timeperiod` SET `timeperiod_name`='$chkInsName', `alias`='$chkInsAlias', `exclude`=$intSelExclude,
	        `name`='$chkInsTplName', `active`='$chkActive', `config_id`=$chkDomainId, `last_modified`=NOW()";
	
	//insert or modify? 
	if ($chkModus == "insert") $strSQL = "INSERT INTO ".$strSQLx;	  
	else	$strSQL = "UPDATE ".$strSQLx." WHERE `id`=$chkDataId";
	    
	  
	//exec SQL query 
	$intInsert = $myDataClass->dataInsert($strSQL,$intInsertId);
	//bail if initial query fails 
	if($intInsert > 0) 
	{
		$errors++; 
		$strMessage .=$myDataClass->strDBMessage; 
		return array($errors,$strMessage); 
	}
	//sql_output($strSQL); 	
	    
	if ($chkModus == "insert")  $chkDataId = $intInsertId;
	  
	else //if the initial query was successful 
	{
		if ($chkModus  == "insert")   $myDataClass->writeLog(gettext('New time period inserted:')." ".$chkInsName);
		if ($chkModus  == "modify")   $myDataClass->writeLog(gettext('Time period modified:')." ".$chkInsName);
		//
		// update relationships 
		// ============================
		if ($chkModus == "insert") 
		{
	 		if($intSelExclude == 1)  $myDataClass->dataInsertRelation("tbl_lnkTimeperiodToTimeperiod",$chkDataId,$chkSelExclude);
	  		$intTipId = $intInsertId;
			//sql_output(); 	  		
		} 
		if ($chkModus == "modify") 
		{
			if($intSelExclude == 1) 
			{  $myDataClass->dataUpdateRelation("tbl_lnkTimeperiodToTimeperiod",$chkDataId,$chkSelExclude);
				//sql_output(); 		 
   		}
			else 
			{
		   	$myDataClass->dataDeleteRelation("tbl_lnkTimeperiodToTimeperiod",$chkDataId);
				//sql_output(); 		
			}		
		}//end IF 'modify' 
		
		// update relationships 
		// ==================================
		//clear out old time definitions 
		if ($chkModus == "modify") 
		{
		  $strSQL   = "DELETE FROM `tbl_timedefinition` WHERE `tipId`=$chkDataId";
		  $booReturn  = $myDataClass->dataInsert($strSQL,$intInsertId);
			//sql_output(); 		  
		  $intTipId = $chkDataId; 
		  //echo "ID $intTipId<br />"; 
		}
		
		//process timedefinitions and timeranges 
		//tipId = timeperiod id 
		for($i=0; $i < count($timeDefs); $i++) 
		{					
			$def = strtolower($timeDefs[$i]); 
		   $range = str_replace(" ","",$timeRanges[$i]);	//strip whitespace             
		   $strSQL = "INSERT INTO `tbl_timedefinition` (`tipId`,`definition`,`range`,`last_modified`)
		             VALUES ($intTipId,'$def','$range',now())";
		             
		   //echo $strSQL;           
		   $booReturn  = $myDataClass->dataInsert($strSQL,$intInsertId);	
		   if($booReturn > 0) $errors++; 
			//sql_output(); 		             
		}//end FOR 
	
		$intReturn = 0;
	}//end ELSE 
	
	//return data 
	$strMessage .=	$myDataClass->strDBMessage; 	
	return array($errors,$strMessage);	
	  
}//end function process_timeperiod_submission()  



/** function process_command_submission()
*	handles form submissions for command object configurations 
*
*	@global object $myVisClass nagiosql templating handler
*	@global object $myDataClass nagiosql data handler
*	@global object $myConfigClass nagiosql config handler  	
*	@global object $myDBClass nagiosql database handler
*	@return array array(int $returnCode,string $returnMessage) return output for browser
*/
function process_command_submission()
{

	global $myVisClass;
	global $myDataClass;
	global $myConfigClass; 	
	global $myDBClass;

	$strMessage     = "";
	$errors = 0; 
	
	//process form variables 
	$chkModus = ccm_grab_request_var('mode');  
	$chkDataId = ccm_grab_request_var('hidId'); 
	$exactType = ccm_grab_request_var('exactType');  
	$genericType = ccm_grab_request_var('genericType'); 
	$ucType = ucfirst($exactType); 

	//command form values 
	$chkInsName     = ccm_grab_request_var('tfName','');    
	$chkInsCommand  = ccm_grab_request_var('tfCommand','',true);
	$chkInsType		 = ccm_grab_request_var('selCommandType',''); 
	$chkActive		 = ccm_grab_request_var('chbActive',0);  

	//tmp session item 
	$chkDomainId	 = $_SESSION['domain']; 
	

	//Data processing  
	$strSQLx = "tbl_command SET command_name='$chkInsName', command_line='$chkInsCommand', command_type=$chkInsType,
				active='$chkActive', config_id=$chkDomainId, last_modified=NOW()";
	if ($chkModus == "insert") 
		$strSQL = "INSERT INTO ".$strSQLx; 		
	else 
		$strSQL = "UPDATE ".$strSQLx." WHERE id=$chkDataId";   
		
	//run SQL query 			
	$intInsert = $myDataClass->dataInsert($strSQL,$intInsertId);

	//sql_output($strSQL); 	

	if ($intInsert == 1) 
	{
		$intReturn = 1;	
		$errors++; 		 
	}	
	else 
	{
		if ($chkModus  == "insert") 	$myDataClass->writeLog(gettext('New command inserted:')." ".$chkInsName);
		if ($chkModus  == "modify") 	$myDataClass->writeLog(gettext('Command modified:')." ".$chkInsName);
		$intReturn = 0;
	}


	// return status 
	if (isset($intReturn) && ($intReturn == 1)) $strMessage = $myDataClass->strDBMessage;
	if (isset($intReturn) && ($intReturn == 0)) $strMessage = $myDataClass->strDBMessage;
	//
	// Last database update and Filedatum
	// ======================================
	$myConfigClass->intDomainId = $_SESSION['domain'];
	$myConfigClass->lastModified("tbl_command",$strLastModified,$strFileDate,$strOld);

	//return data 	
	return array($errors,$strMessage);	

}//end process_command_submission() 

function process_escalation_submission() {
	global $myVisClass;
	global $myDataClass;
	global $myConfigClass; 	
	global $myDBClass;
	
	$strMessage     = "";
	$errors = 0; 

	//expected $_REQUEST variables for all forms 
	$chkModus = ccm_grab_request_var('mode');  
	$chkDataId = ccm_grab_request_var('hidId'); 
	$exactType = ccm_grab_request_var('exactType');  
	$genericType = ccm_grab_request_var('genericType'); 
	$ucType = ucfirst($exactType); 
	//select lists 	
	$chkSelHost       =  ccm_grab_request_var('hosts',array());
	$chkSelHostGroup  =  ccm_grab_request_var('hostgroups',array());
	$chkSelService    =  ccm_grab_request_var('services',array());
	$chkSelContact    =  ccm_grab_request_var('contacts',array());
	$chkSelContactGroup = ccm_grab_request_var('contactgroups',array());
	//misc 
	$chkTfFirstNotif    = ccm_grab_request_var('tfFirstNotif',"NULL");
	$chkTfLastNotif     = ccm_grab_request_var('tfLastNotif',"NULL");
	$chkTfNotifInterval = ccm_grab_request_var('tfNotifInterval',"NULL");
	$chkSelEscPeriod    = ccm_grab_request_var('selPeriod',0);
	$chkActive 		 = ccm_grab_request_var('chbActive',0); 
	//escalation options 
	$chkEOd         = (ccm_grab_request_var('chbEOd',false)) ? 'd' : ''; //add commas 
	$chkEOw         = (ccm_grab_request_var('chbEOw',false)) ? 'w' : ''; 
	$chkEOu         = (ccm_grab_request_var('chbEOu',false)) ? 'u' : '';
	$chkEOc         = (ccm_grab_request_var('chbEOc',false)) ? 'c' : '';
	$chkEOr         = (ccm_grab_request_var('chbEOr',false)) ? 'r' : '';

	$chkTfConfigName    = ccm_grab_request_var('tfConfigName','');
	$chkDomainId	 = $_SESSION['domain']; 
	
	//Process variables as needed 
	
	//build escalation option string
	$strEO = ''; 	
	//build $strEO 
	foreach(array($chkEOw,$chkEOu,$chkEOc,$chkEOr,$chkEOd) as $a)
		if($a !='') $strEO.=$a.','; 
 	
	//set markers if there are selections
	$intSelHost = empty($chkSelHost) ? 0 : 1;
	$intSelHostGroup = empty($chkSelHostGroup) ? 0 : 1;
	$intSelService = empty($chkSelService) ? 0 : 1;
	$intSelContact = empty($chkSelContact) ? 0 : 1;
	$intSelContactGroup = empty($chkSelContactGroup) ? 0 : 1;
	
	//wildcards?
	$intSelHost =  (is_array($chkSelHost) && in_array("*",$chkSelHost) ) ? 2 : $intSelHost;
	$intSelHostGroup =  (is_array($chkSelHostGroup) && in_array("*",$chkSelHostGroup) ) ? 2 : $intSelHostGroup;
	$intSelService =  (is_array($chkSelService) && in_array("*",$chkSelService) ) ? 2 : $intSelService;
	$intSelContact =  (is_array($chkSelContact) && in_array("*",$chkSelContact) ) ? 2 : $intSelContact;
	$intSelContactGroup =  (is_array($chkSelContactGroup) && in_array("*",$chkSelContactGroup) ) ? 2 : $intSelContactGroup;
				
	//Build SQL Query 
	$strSQLx = "`tbl_{$exactType}` SET `config_name`='$chkTfConfigName', `host_name`=$intSelHost,
         `hostgroup_name`=$intSelHostGroup, `contacts`=$intSelContact,
        `contact_groups`=$intSelContactGroup, `first_notification`=$chkTfFirstNotif, `last_notification`=$chkTfLastNotif,
        `notification_interval`=$chkTfNotifInterval, `escalation_period`='$chkSelEscPeriod', `escalation_options`='$strEO',
        `config_id`=$chkDomainId, `active`='$chkActive', `last_modified`=NOW()";
    if($exactType=='serviceescalation')
    	$strSQLx .= ",`service_description`=$intSelService";    
    	 
	if($chkModus == "insert") 
	    $strSQL = "INSERT INTO ".$strSQLx;  
	else 
	    $strSQL = "UPDATE ".$strSQLx." WHERE `id`=$chkDataId";
	 
	// sql_output($strSQL);    
	//send query to SQL     
	$intInsert = $myDataClass->dataInsert($strSQL,$intInsertId);
    if($chkModus == "insert") 
    	$chkDataId = $intInsertId;
    
    if($intInsert == 1) { //there was an error updating the DB, BAIL! 
        $errors++;
	    $strMessage = $myDataClass->strDBMessage;
    }  
    else //first SQL query succeeded 
    {
        if($chkModus  == "insert")   $myDataClass->writeLog(gettext('New service escalation inserted:')." ".$chkTfConfigName);
        if($chkModus  == "modify")   $myDataClass->writeLog(gettext('Service escalation modified:')." ".$chkTfConfigName);
      
      // Update Relations 
      // ============================
      if ($chkModus == "insert") 
      {
        if (!empty($chkSelHost)) $myDataClass->dataInsertRelation("tbl_lnk{$ucType}ToHost",$chkDataId,$chkSelHost);
        if (!empty($chkSelHostGroup)) $myDataClass->dataInsertRelation("tbl_lnk{$ucType}ToHostgroup",$chkDataId,$chkSelHostGroup);
        if (!empty($chkSelService)) $myDataClass->dataInsertRelation("tbl_lnk{$ucType}ToService",$chkDataId,$chkSelService);
        if (!empty($chkSelContact)) $myDataClass->dataInsertRelation("tbl_lnk{$ucType}ToContact",$chkDataId,$chkSelContact);
        if (!empty($chkSelContactGroup)) $myDataClass->dataInsertRelation("tbl_lnk{$ucType}ToContactgroup",$chkDataId,$chkSelContactGroup);
      } 
      if ($chkModus == "modify") 
      {
      	//update hosts 
        if (!empty($chkSelHost) && $intSelHost!=2) $myDataClass->dataUpdateRelation("tbl_lnk{$ucType}ToHost",$chkDataId,$chkSelHost);
        else  $myDataClass->dataDeleteRelation("tbl_lnk{$ucType}ToHost",$chkDataId);
        //update hostgroups 
        if (!empty($chkSelHostGroup) && $intSelHostGroup!=2) $myDataClass->dataUpdateRelation("tbl_lnk{$ucType}ToHostgroup",$chkDataId,$chkSelHostGroup);
        else $myDataClass->dataDeleteRelation("tbl_lnk{$ucType}ToHostgroup",$chkDataId);
        //services 
        if (!empty($chkSelService)  && $intSelService!=2) $myDataClass->dataUpdateRelation("tbl_lnk{$ucType}ToService",$chkDataId,$chkSelService);
        else $myDataClass->dataDeleteRelation("tbl_lnk{$ucType}Service",$chkDataId);
   		//contacts 
        if (!empty($chkSelContact)  && $intSelContact!=2) $myDataClass->dataUpdateRelation("tbl_lnk{$ucType}ToContact",$chkDataId,$chkSelContact);
        else $myDataClass->dataDeleteRelation("tbl_lnk{$ucType}ToContact",$chkDataId);
       	//contact groups 
        if (!empty($chkSelContactGroup)  && $intSelContactGroup!=2) $myDataClass->dataUpdateRelation("tbl_lnk{$ucType}ToContactgroup",$chkDataId,$chkSelContactGroup);
        else  $myDataClass->dataDeleteRelation("tbl_lnk{$ucType}ToContactgroup",$chkDataId);
      }//end IF mode is modify 	
    } //end IF no errors 
     
    // return status 
	if($errors == 0) $strMessage = $myDataClass->strDBMessage;
    return array($errors, $strMessage);
    
}//end function 


function process_dependency_submission() {
	global $myVisClass;
	global $myDataClass;
	global $myConfigClass; 	
	global $myDBClass;
	
	$strMessage     = "";
	$errors = 0; 

	//expected $_REQUEST variables for all forms 
	$chkModus = ccm_grab_request_var('mode');  
	$chkDataId = ccm_grab_request_var('hidId'); 
	$exactType = ccm_grab_request_var('exactType');  
	$genericType = ccm_grab_request_var('genericType'); 
	$ucType = ucfirst($exactType); 
	//select lists 	
	$chkSelHost       =  ccm_grab_request_var('hosts',array());
	$chkSelHostgroup  =  ccm_grab_request_var('hostgroups',array());
	$chkSelService    =  ccm_grab_request_var('services',array());
	$chkSelHostDepend   = ccm_grab_request_var('hostdependencys',array());
	$chkSelHostgroupDepend   = ccm_grab_request_var('hostgroupdependencys',array());
	$chkSelServiceDepend  = ccm_grab_request_var('servicedependencys',array());	
	//misc 
	$chkInherit    = ccm_grab_request_var('chbInherit',1);
	$chkSelDependPeriod    = ccm_grab_request_var('selPeriod',0);
	$chkActive 		 = ccm_grab_request_var('chbActive',0); 
	//execution failure options 
	$chkEOo         = (ccm_grab_request_var('chbEOo',false)) ? 'o' : ''; //add commas 
	$chkEOd         = (ccm_grab_request_var('chbEOd',false)) ? 'd' : ''; 
	$chkEOu         = (ccm_grab_request_var('chbEOu',false)) ? 'u' : '';
	$chkEOp         = (ccm_grab_request_var('chbEOp',false)) ? 'p' : '';
	$chkEOn         = (ccm_grab_request_var('chbEOn',false)) ? 'n' : '';
	$chkEOw         = (ccm_grab_request_var('chbEOw',false)) ? 'w' : '';	
	$chkEOc         = (ccm_grab_request_var('chbEOc',false)) ? 'c' : '';	
	
	//notification failure options 
	$chkNOo         = (ccm_grab_request_var('chbNOo',false)) ? 'o' : ''; //add commas 
	$chkNOd         = (ccm_grab_request_var('chbNOd',false)) ? 'd' : ''; 
	$chkNOu         = (ccm_grab_request_var('chbNOu',false)) ? 'u' : '';
	$chkNOp         = (ccm_grab_request_var('chbNOp',false)) ? 'p' : '';
	$chkNOn         = (ccm_grab_request_var('chbNOn',false)) ? 'n' : '';	
	$chkNOw         = (ccm_grab_request_var('chbNOw',false)) ? 'w' : '';	
	$chkNOc         = (ccm_grab_request_var('chbNOc',false)) ? 'c' : '';
			
	$chkTfConfigName    = ccm_grab_request_var('tfConfigName','');
	$chkDomainId	 = $_SESSION['domain']; 
	
	//Process variables as needed 
	
	//build execution failure criteria option string
	$strEO = ''; 	
	//build $strEO 
	foreach(array($chkEOw,$chkEOu,$chkEOc,$chkEOd,$chkEOo,$chkEOp,$chkEOn ) as $a)
		if($a !='') $strEO.=$a.','; 
		
	//build notification failure criteria option string
	$strNO = ''; 	
	//build $strEO 
	foreach(array($chkNOw,$chkNOu,$chkNOc,$chkNOd,$chkNOo,$chkNOp,$chkNOn ) as $a)
		if($a !='') $strNO.=$a.','; 	
 	
	//set booleans 
	$intSelHost = empty($chkSelHost) ? 0 : 1;
	$intSelHostgroup = empty($chkSelHostgroup) ? 0 : 1;
	$intSelService = empty($chkSelService) ? 0 : 1;
	$intSelHostDepend = empty($chkSelHostDepend) ? 0 : 1;
	$intSelHostgroupDepend = empty($chkSelHostgroupDepend) ? 0 : 1;
	$intSelServiceDepend = empty($chkSelServiceDepend) ? 0 : 1;
	
	//wildcards?
	$intSelHost =  (is_array($chkSelHost) && in_array("*",$chkSelHost) ) ? 2 : $intSelHost;
	$intSelService =  (is_array($chkSelService) && in_array("*",$chkSelService) ) ? 2 : $intSelService;
	$intSelServiceDepend =  (is_array($chkSelServiceDepend) && in_array("*",$chkSelServiceDepend) ) ? 2 : $intSelServiceDepend;
	
	//build SQL query 	
	$strSQLx = "`tbl_{$exactType}` SET `dependent_host_name`=$intSelHostDepend, `dependent_hostgroup_name`=$intSelHostgroupDepend,
	         `host_name`=$intSelHost, `hostgroup_name`=$intSelHostgroup,
	         `config_name`='$chkTfConfigName', `inherits_parent`='$chkInherit',
	        `execution_failure_criteria`='$strEO', `notification_failure_criteria`='$strNO', `dependency_period`=$chkSelDependPeriod,
	        `active`='$chkActive', `config_id`=$chkDomainId, `last_modified`=NOW()";
	if($exactType=='servicedependency')
		$strSQLx .=",`dependent_service_description`=$intSelServiceDepend,`service_description`=$intSelService ";        
	//mode?         
	if ($chkModus == "insert") 
	    $strSQL = "INSERT INTO ".$strSQLx;
	else 
	    $strSQL = "UPDATE ".$strSQLx." WHERE `id`=$chkDataId";
	    
	//sql_output($strSQL);    
	//exec SQL query   
	$intInsert = $myDataClass->dataInsert($strSQL,$intInsertId);
	
	if($chkModus == "insert") 
		$chkDataId = $intInsertId;
			    
	if($intInsert == 1) //there was a problem with the first entry 
		$errors++;	   	
	else { //no SQL errors from the first entry 
		if ($chkModus == "insert") $myDataClass->writeLog(gettext('New service dependency inserted:')." ".$chkTfConfigName);
		if ($chkModus == "modify") $myDataClass->writeLog(gettext('Service dependency modified:')." ".$chkTfConfigName);
	      
    	// UPDATE RELATIONS 
      	// ============================
      	//INSERT MODE 
      	if ($chkModus == "insert") 
      	{
        	if ($intSelHostDepend   == 1)   $myDataClass->dataInsertRelation("tbl_lnk{$ucType}ToHost_DH",$chkDataId,$chkSelHostDepend);
        	if ($intSelHostgroupDepend   == 1)   $myDataClass->dataInsertRelation("tbl_lnk{$ucType}ToHostgroup_DH",$chkDataId,$chkSelHostgroupDepend);
        	if ($intSelServiceDepend  == 1)   $myDataClass->dataInsertRelation("tbl_lnk{$ucType}ToService_DS",$chkDataId,$chkSelServiceDepend);
        	if ($intSelHost       == 1)   $myDataClass->dataInsertRelation("tbl_lnk{$ucType}ToHost_H",$chkDataId,$chkSelHost);
        	if ($intSelHostgroup    == 1)   $myDataClass->dataInsertRelation("tbl_lnk{$ucType}ToHostgroup_H",$chkDataId,$chkSelHostgroup);
        	if ($intSelService      == 1)   $myDataClass->dataInsertRelation("tbl_lnk{$ucType}ToService_S",$chkDataId,$chkSelService);
      	}
      	//MODIFY MODE  
      	if ($chkModus == "modify") 
      	{
      		//host deps 
        	if($intSelHostDepend == 1) $myDataClass->dataUpdateRelation("tbl_lnk{$ucType}ToHost_DH",$chkDataId,$chkSelHostDepend);
        	else $myDataClass->dataDeleteRelation("tbl_lnk{$ucType}ToHost_DH",$chkDataId);
	        //hostgroup deps 
	        if($intSelHostgroupDepend == 1) $myDataClass->dataUpdateRelation("tbl_lnk{$ucType}ToHostgroup_DH",$chkDataId,$chkSelHostgroupDepend);
	        else $myDataClass->dataDeleteRelation("tbl_lnk{$ucType}ToHostgroup_DH",$chkDataId);

			//hosts 
	        if ($intSelHost == 1) $myDataClass->dataUpdateRelation("tbl_lnk{$ucType}ToHost_H",$chkDataId,$chkSelHost);
	        else $myDataClass->dataDeleteRelation("tbl_lnk{$ucType}ToHost_H",$chkDataId);
			//hostgroup 
	        if ($intSelHostgroup == 1) $myDataClass->dataUpdateRelation("tbl_lnk{$ucType}ToHostgroup_H",$chkDataId,$chkSelHostgroup);
	        else $myDataClass->dataDeleteRelation("tbl_lnk{$ucType}ToHostgroup_H",$chkDataId);
	        
	        //service deps only 
	        if($exactType=='servicedependency') {
		        //service 
		        if ($intSelService == 1) 
		        	$myDataClass->dataUpdateRelation("tbl_lnk{$ucType}ToService_S",$chkDataId,$chkSelService);
		        else $myDataClass->dataDeleteRelation("tbl_lnk{$ucType}ToService_S",$chkDataId);
		       	//service dependencies 
		        if ($intSelServiceDepend == 1) 
		        	$myDataClass->dataUpdateRelation("tbl_lnk{$ucType}ToService_DS",$chkDataId,$chkSelServiceDepend);
		        else $myDataClass->dataDeleteRelation("tbl_lnk{$ucType}ToService_DS",$chkDataId);	    
	        }    
      	}
	}   				
		     
    // return status 
	$strMessage = $myDataClass->strDBMessage;
    return array($errors, $strMessage);
    
} //end process_dependency_submission() 



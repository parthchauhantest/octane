<?php  //contact.inc.php    form input handling for CCM contacts page 


/*
*	handles form submissions for contact and contacttemplate object configurations 
*
*	@global object $myVisClass nagiosql templating handler
*	@global object $myDataClass nagiosql data handler
*	@global object $myConfigClass nagiosql config handler  	
*	@global object $myDBClass nagiosql database handler
*	@return array array(int $returnCode,string $returnMessage) return output for browser
*/
function process_contact_submission()
{
	//global classes 
	global $myVisClass;
	global $myDataClass;
	global $myConfigClass; 	
	global $myDBClass;
	
	// Declaring variables
	// =====================
	$strMessage     = "";
	$errors = 0;
	
	//grab form variables  
	$chkModus = ccm_grab_request_var('mode');  
	$chkDataId = ccm_grab_request_var('hidId'); 
	$exactType = ccm_grab_request_var('exactType');  
	$genericType = ccm_grab_request_var('genericType'); 
	$ucType = ucfirst($exactType);
	//domain ID for now 
	$chkDomainId = $_SESSION['domain']; //domain is localhost  

	$chkTfName        = ccm_grab_request_var('tfName','');  
	$chkTfFriendly    = ccm_grab_request_var('tfFriendly','');
	$chkTfEmail       = ccm_grab_request_var('tfEmail',"");
	$chkTfPager       = ccm_grab_request_var('tfPager','');         
	$chkTfAddress1    = ccm_grab_request_var('tfAddress1','');       
	$chkTfAddress2    = ccm_grab_request_var('tfAddress2','');        
	$chkTfAddress3    = ccm_grab_request_var('tfAddress3','');        
	$chkTfAddress4    = ccm_grab_request_var('tfAddress4','');        
	$chkTfAddress5    = ccm_grab_request_var('tfAddress5','');      
	$chkTfAddress6    = ccm_grab_request_var('tfAddress6','');      
		
	$chkSelContactGroup   = ccm_grab_request_var('contactgroups', array(""));
	$chkRadContactGroup   = ccm_grab_request_var('radContactgroup',2);
	$chkHostNotifEnable   = ccm_grab_request_var('radHostNotifEnabled', 2);
	$chkServiceNotifEnable= ccm_grab_request_var('radServiceNotifEnabled',2);
		
	$chkSelHostPeriod     = ccm_grab_request_var('selHostPeriod',0); 
	$chkSelServicePeriod  = ccm_grab_request_var('selServicePeriod',0);
	
	$chkSelHostCommand    = ccm_grab_request_var('hostcommands', array(""));
	$chkRadHostCommand    = ccm_grab_request_var('radHostcommand',2);
	$chkSelServiceCommand = ccm_grab_request_var('servicecommands', array(""));
	$chkRadServiceCommand = ccm_grab_request_var('radServicecommand',2); 
	
	$chkRetStatInf      = ccm_grab_request_var('radStatusInfos',2);
	$chkRetNonStatInf   = ccm_grab_request_var('radNoStatusInfos',2);
	$chkCanSubCmds      = ccm_grab_request_var('radCanSubCmds',2);
	
	//template name 
	$chkTfGeneric   = ccm_grab_request_var('tfGenericName',"");
	
	//checkbox options 
	$chbHOd        = ccm_grab_request_var('chbHOd','');       
	$chbHOu        = ccm_grab_request_var('chbHOu','');         
	$chbHOr        = ccm_grab_request_var('chbHOr','');        
	$chbHOf        = ccm_grab_request_var('chbHOf','');         
	$chbHOs        = ccm_grab_request_var('chbHOs','');         
	$chbHOn        = ccm_grab_request_var('chbHOn','');      
//	$chbHOnull3     = ccm_grab_request_var('chbHOnull3','');  
	 
	$chbSOw        = ccm_grab_request_var('chbSOw','');       
	$chbSOu        = ccm_grab_request_var('chbSOu','');      
	$chbSOc        = ccm_grab_request_var('chbSOc','');     
	$chbSOr        = ccm_grab_request_var('chbSOr','');    
	$chbSOf        = ccm_grab_request_var('chbSOf','');    
	$chbSOs        = ccm_grab_request_var('chbSOs','');      
	$chbSOn        = ccm_grab_request_var('chbSOn','');   
		
	$chkActive 		 = ccm_grab_request_var('Active',0);  	
	//unused in current CCM and NagiosQL? 
	$chkRadTemplates= ccm_grab_request_var('radTemplate',2); 
	
	//build host/service notification options strings, add commas where needed 
	$strHO = '';
	foreach(array($chbHOd,$chbHOu,$chbHOr,$chbHOf,$chbHOs,$chbHOn) as $item) {
		if($item!='')  $strHO.=$item.','; //appending commas 
	}//end FOREACH 
	$strSO = ''; 
	foreach(array($chbSOw,$chbSOu,$chbSOc,$chbSOr,$chbSOf,$chbSOs,$chbSOn) as $item) {
		if($item!='')  $strSO.=$item.','; //appending commas 
	}

	// Check for templates 
	// =================================
	$templates = ccm_grab_request_var('contacttemplates',array()); 
	//are templates being used? 
	$intTemplates = (count($templates) > 0) ? 1 : 0;  
	
	//check for Free Variables 
	// ================================ 
	$variables = ccm_grab_request_var('variables', array() ) ;
	$definitions = ccm_grab_request_var('variabledefs', array() ); 
	//freeform variables being used?  
	$intVariables = (count($variables) ) > 0 ? 1 : 0;  
		  
	//check submitted arrays
	if (($chkSelContactGroup[0] == "")   || ($chkSelContactGroup[0] == "0"))   {$intContactGroups = 0;}  else {$intContactGroups = 1;}
	if (($chkSelHostCommand[0] == "")    || ($chkSelHostCommand[0] == "0"))    {$intHostCommand = 0;}    else {$intHostCommand = 1;}
	if ($chkSelHostCommand[0] == "*")    $intHostCommand = 2;
	if (($chkSelServiceCommand[0] == "") || ($chkSelServiceCommand[0] == "0")) {$intServiceCommand = 0;} else {$intServiceCommand = 1;}
	if ($chkSelServiceCommand[0] == "*") $intServiceCommand = 2;
	$intContactGroups =  (is_array($chkSelContactGroup) && in_array("*",$chkSelContactGroup) ) ? 2 : $intContactGroups;

	// prepare SQL query 
	  $strSQLx = "`tbl_".$exactType."` SET `alias`='$chkTfFriendly', `contactgroups`=$intContactGroups,
	        `contactgroups_tploptions`=$chkRadContactGroup, `host_notifications_enabled`='$chkHostNotifEnable',
	        `service_notifications_enabled`='$chkServiceNotifEnable', `host_notification_period`='$chkSelHostPeriod',
	        `service_notification_period`='$chkSelServicePeriod', `host_notification_options`='$strHO',
	        `host_notification_commands_tploptions`=$chkRadHostCommand, `service_notification_options`='$strSO',
	        `host_notification_commands`=$intHostCommand, `service_notification_commands`=$intServiceCommand,
	        `service_notification_commands_tploptions`=$chkRadServiceCommand, `can_submit_commands`='$chkCanSubCmds ',
	        `retain_status_information`='$chkRetStatInf', `retain_nonstatus_information`='$chkRetNonStatInf', `email`='$chkTfEmail',
	        `pager`='$chkTfPager', `address1`='$chkTfAddress1', `address2`='$chkTfAddress2', `address3`='$chkTfAddress3',
	        `address4`='$chkTfAddress4', `address5`='$chkTfAddress5', `address6`='$chkTfAddress6', 
	        `use_variables`='$intVariables', `use_template`=$intTemplates, `use_template_tploptions`='$chkRadTemplates',
	        `active`='$chkActive', `config_id`='$chkDomainId', `last_modified`=NOW(),";
	if($exactType=='contact') $strSQLx .= "`contact_name`='$chkTfName',`name`='$chkTfGeneric' "; 
	else $strSQLx .= "`template_name`= '$chkTfName' ";     
	        
	if ($chkModus == "insert") 
		$strSQL = "INSERT INTO ".$strSQLx;
	else  $strSQL = "UPDATE ".$strSQLx." WHERE `id`=$chkDataId";
	
//echo "QUERY IS: $strSQL <br />"; 	

	$intInsert = $myDataClass->dataInsert($strSQL,$intInsertId);
	
//sql_output($strSQL); 	
	
	if ($chkModus == "insert")  $chkDataId = $intInsertId;
	//bail on error    
	if ($intInsert == 1) 
		return array(1, $myDataClass->strDBMessage); 		
	else 
	{
		if($chkModus  == "insert")   $myDataClass->writeLog(gettext('New contact inserted:')." ".$chkTfName);
		if($chkModus  == "modify")   $myDataClass->writeLog(gettext('Contact modified:')." ".$chkTfName);
	      //
		// Relationen eintragen/updaten
		// ============================
		if ($chkModus == "insert") 
		{
		  if ($intContactGroups  == 1) $myDataClass->dataInsertRelation("tbl_lnk".$ucType."ToContactgroup",$chkDataId,$chkSelContactGroup);
//sql_output('contact to cg');		  
		  if ($intHostCommand    == 1) $myDataClass->dataInsertRelation("tbl_lnk".$ucType."ToCommandHost",$chkDataId,$chkSelHostCommand);
//sql_output('c to CommandHost');			  
		  if ($intServiceCommand == 1) $myDataClass->dataInsertRelation("tbl_lnk".$ucType."ToCommandService",$chkDataId,$chkSelServiceCommand);
//sql_output('c to CommandService');		  
		} 
		else if ($chkModus == "modify") 
		{
			if ($intContactGroups == 1) 
		   	$myDataClass->dataUpdateRelation("tbl_lnk".$ucType."ToContactgroup",$chkDataId,$chkSelContactGroup);		   
			else  $myDataClass->dataDeleteRelation("tbl_lnk".$ucType."ToContactgroup",$chkDataId);
//sql_output('contact to cg');			  
			if($intHostCommand == 1)  
		  		$myDataClass->dataUpdateRelation("tbl_lnk".$ucType."ToCommandHost",$chkDataId,$chkSelHostCommand);		   
		  	else $myDataClass->dataDeleteRelation("tbl_lnk".$ucType."ToCommandHost",$chkDataId);
//sql_output('c to CommandHost');			  
			if($intServiceCommand == 1)
				$myDataClass->dataUpdateRelation("tbl_lnk".$ucType."ToCommandService",$chkDataId,$chkSelServiceCommand);		   
			else  $myDataClass->dataDeleteRelation("tbl_lnk".$ucType."ToCommandService",$chkDataId);	
//sql_output('c to CommandService');					  
		}
	      
      // update template information 
      // ========================================
      if ($chkModus == "modify") 
      {
        $strSQL   = "DELETE FROM `tbl_lnk".$ucType."ToContacttemplate` WHERE `idMaster`=$chkDataId";
        $booReturn  = $myDataClass->dataInsert($strSQL,$intInsertId);  
        if($booReturn > 0) $errors++;  
      }
  
      //if there are templates      
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
        $tblTemplate = 'Contacttemplate';
        foreach($templates as $elem)  
        {

            $idTable = 1;
            if(strpos($elem,'::2')) //contact name as template
            {
                $idTable = 2;
                $elem = str_replace('::2','',$elem);

            }
            $strSQL = "INSERT INTO `tbl_lnk".$ucType."To".$tblTemplate."` (`idMaster`,`idSlave`,`idTable`,`idSort`)
                   VALUES ($chkDataId, $elem, $idTable , $intSortId)"; 	//NOTE: replaced $elem['idTable'] with 1  
//echo $strSQL.'<br />';           
            $booReturn  = $myDataClass->dataInsert($strSQL,$intInsertId);  
            if($booReturn > 0) $errors++;  
//sql_output($strSQL);	            
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
        //sql_output(); 
        //if($booReturn > 0) $errors++; 
        if ($intDataCount != 0) 
        {
          foreach ($arrData AS $elem) 
          {
            $strSQL   = "DELETE FROM `tbl_variabledefinition` WHERE `id`=".$elem['idSlave'];
            $booReturn  = $myDataClass->dataInsert($strSQL,$intInsertId);	
            if($booReturn > 0) $errors++;          
          }
        }
        $strSQL   = "DELETE FROM `tbl_lnk".$ucType."ToVariabledefinition` WHERE `idMaster`=$chkDataId";
        $booReturn  = $myDataClass->dataInsert($strSQL,$intInsertId);  
        if($booReturn > 0) $errors++;     
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
                   VALUES ('{$vars[$i]}','{$defs[$i]}',now())";
            $booReturn  = $myDataClass->dataInsert($strSQL,$intInsertId);
            if($booReturn > 0) $errors++;  
			//sql_output($strSQL);	            
            $strSQL = "INSERT INTO `tbl_lnk".$ucType."ToVariabledefinition` (`idMaster`,`idSlave`)
                   VALUES ($chkDataId,$intInsertId)";
            $booReturn  = $myDataClass->dataInsert($strSQL,$intInsertId);
            if($booReturn > 0) $errors++;  
			//sql_output($strSQL);	            
            
        }//end foreach 
      }//end IF variables defined  
	      
	      	      
	}//end ELSE query successful 
	 
		// Status messages and share
	if ($errors > 0) $strMessage .= gettext("There were ").$errors.gettext(" errors while processing this request")."<br />".$myDataClass->strDBMessage;
	if ($errors==0) $strMessage .= $ucType." <strong>".$chkTfName."</strong>".gettext(" sucessfully updated. ");
	//
	// Last database update and file date
	// ======================================
	$myConfigClass->lastModified("tbl_".$exactType,$strLastModified,$strFileDate,$strOld);
	//
	
	return array($errors, $strMessage.'<br />');    

}//end process_contact_submission() 


?>

<?php //group.inc.php





/** function process_ccm_group() 
*	handles form submissions for hostgroup, contactgroup, and servicegroup object configurations 
*
*	@global object $myVisClass nagiosql templating handler
*	@global object $myDataClass nagiosql data handler
*	@global object $myConfigClass nagiosql config handler  	
*	@global object $myDBClass nagiosql database handler
*	@return array array(int $returnCode,string $returnMessage) return output for browser
*/
function process_ccm_group()
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

	$chkTfName        = ccm_grab_request_var('tfName','');
	$chkTfFriendly      = ccm_grab_request_var('tfFriendly','');
	
	//changed from chckSelMembers 
	$chkSelHostMembers      = ccm_grab_request_var('hosts',array('')); 
	$chkSelHostgroupMembers = ccm_grab_request_var('hostgroups',array("")); 
	$chkSelServicegroupMembers = ccm_grab_request_var('servicegroups',array(""));
	$chkSelHostServiceMembers = ccm_grab_request_var('hostservices',array());  
	
	//contactgroup specific vars	
	$chkSelContactMembers		= ccm_grab_request_var('contacts',array(''));
	$chkSelContactgroupMembers = ccm_grab_request_var('contactgroups',array(''));  
	
	
	$chkTfNotes       = ccm_grab_request_var('tfNotes','');
	$chkTfNotesURL      = ccm_grab_request_var('tfNotesURL','');
	$chkTfActionURL     = ccm_grab_request_var('tfActionURL','');
	//active? 
	$chkActive 				= ccm_grab_request_var('chbActive',0);  
	
	//domain ID?
	$chkDomainId = $_SESSION['domain']; 
	//
	//
	// Handle Lists 
	// =================
	//determine host memberships 
	if ($chkSelHostMembers[0] == "" || $chkSelHostMembers[0] == "0")     $intSelHostMembers = 0;       
	else $intSelHostMembers = 1;
	if ($chkSelHostMembers[0] == "*")     $intSelHostMembers = 2;
	
	//determine service memberships 
	if (count($chkSelHostServiceMembers) == 0)     $intSelHostServiceMembers = 0;       
	else $intSelHostServiceMembers = 1;
	if (isset($chkSelHostServiceMembers[0]) && $chkSelHostServiceMembers[0] == "*")     $intSelHostServiceMembers = 2;
	
	
	//determine hostgroup memberships 
	if ($chkSelHostgroupMembers[0] == ""  || $chkSelHostgroupMembers[0] == "0") $intSelHostgroupMembers = 0;  
	else $intSelHostgroupMembers = 1;
	if ($chkSelHostgroupMembers[0] == "*")  $intSelHostgroupMembers = 2;
	
	
	//determine servicegroup memberships 
	if ($chkSelServicegroupMembers[0] == ""  || $chkSelServicegroupMembers[0] == "0") $intSelServicegroupMembers = 0;  
	else $intSelServicegroupMembers = 1;
	if ($chkSelServicegroupMembers[0] == "*")  $intSelServicegroupMembers = 2;
	
	//determine contact memberships
	if ($chkSelContactMembers[0] == ""  || $chkSelContactMembers[0] == "0") $intSelContactMembers = 0;  
	else $intSelContactMembers = 1;
	if ($chkSelContactMembers[0] == "*")  $intSelContactMembers = 2;	
	
	//determine contactgroup memberships 
	if ($chkSelContactgroupMembers[0] == ""  || $chkSelContactgroupMembers[0] == "0") $intSelContactgroupMembers = 0;  
	else $intSelContactgroupMembers = 1;
	if ($chkSelContactgroupMembers[0] == "*")  $intSelContactgroupMembers = 2;	
	
	
	
	// Build SQL Query based on mode and object type 
	if (($chkModus == "insert") || ($chkModus == "modify")) 
	{
	  
	  $strSQLx = "`tbl_{$exactType}` SET `{$exactType}_name`='$chkTfName', `alias`='$chkTfFriendly', 
	  					`active`='$chkActive', `config_id`=$chkDomainId, `last_modified`=NOW(), ";
	  
	  if($exactType != 'contactgroup') $strSQLx .="`notes`='$chkTfNotes', `notes_url`='$chkTfNotesURL',
	        `action_url`='$chkTfActionURL', ";
	  
	  if($exactType=='hostgroup') $strSQLx .= "`members`=$intSelHostMembers,`{$exactType}_members`=$intSelHostgroupMembers";
	  if($exactType=='servicegroup') $strSQLx .= "`members`=$intSelHostServiceMembers,`{$exactType}_members`=$intSelServicegroupMembers";
	  if($exactType=='contactgroup') $strSQLx .= "`members`=$intSelContactMembers,`{$exactType}_members`=$intSelContactgroupMembers";
	        
	  if ($chkModus == "insert") 
	  {
	    $strSQL = "INSERT INTO ".$strSQLx;
	  } 
	  else //mode is modify 
	  {
	    $strSQL = "UPDATE ".$strSQLx." WHERE `id`=$chkDataId";
	  }
	  
			//echo "QUERY IS: <br />".$strSQL; 	  
	  
	  //if all required fields are present, continue 
	//  if (($chkTfName != "") && ($chkTfFriendly != "") && (($intSelHostMembers != 0) || ($intVersion == 3))) 
	//  {
	    $intInsert = $myDataClass->dataInsert($strSQL,$intInsertId);
	    //bail if initial insert fails 
		 if($intInsert > 0) 
		 {		    
				//print "<p>SQL Response: ".mysql_error()."<br /> Rows affected: ".$myDBClass->intAffectedRows."</p>"; 
				$errors++; 
				$strMessage.=$myDataClass->strDBMessage; 
				return array($errors,$strMessage); 
		 } 
	    
	    if ($chkModus == "insert") 
	    {
	      $chkDataId = $intInsertId;
	    }
	    
	    if ($intInsert == 1) 
	    {
	      $intReturn = 1;
	    } 
	    else 
	    {
	      if ($chkModus  == "insert")   $myDataClass->writeLog(gettext('New host group inserted:')." ".$chkTfName);
	      if ($chkModus  == "modify")   $myDataClass->writeLog(gettext('Host group modified:')." ".$chkTfName);
	      //
	      // Update Relations 
	      // ============================
	      if ($chkModus == "insert") 
	      {
	      	if($intSelHostMembers  == 1)       $myDataClass->dataInsertRelation("tbl_lnk".$ucType."ToHost",$chkDataId,$chkSelHostMembers);
	      	if($intSelHostgroupMembers  == 1)  $myDataClass->dataInsertRelation("tbl_lnk".$ucType."ToHostgroup",$chkDataId,$chkSelHostgroupMembers);
	      	if($intSelServicegroupMembers == 1)$myDataClass->dataInsertRelation("tbl_lnk".$ucType."ToServicegroup",$chkDataId,$chkSelServicegroupMembers);	
	      	        
	      	if($intSelHostServiceMembers == 1) $myDataClass->dataInsertRelation("tbl_lnk".$ucType."ToService",$chkDataId,$chkSelHostServiceMembers,1);

			   if($intSelContactMembers == 1)     $myDataClass->dataInsertRelation("tbl_lnk".$ucType."ToContact",$chkDataId,$chkSelContactMembers);
			   if($intSelContactgroupMembers == 1)$myDataClass->dataInsertRelation("tbl_lnk".$ucType."ToContactgroup",$chkDataId,$chkSelContactgroupMembers);
				//print "<p>SQL Response: ".mysql_error()."<br /> Rows affected: ".$myDBClass->intAffectedRows."</p>";
					        
	        //update_sg_to_service_relations($chkModus,$chkDataId,$chkSelHostServiceMembers); 
	      } 
	 ///////////////////////////////////MODIFY//////////////////////////////////
	      else if ($chkModus == "modify") 
	      {
	      	switch($exactType)
	      	{
	      	
	      	case 'hostgroup':
		      	//host links 
		        if ($intSelHostMembers == 1) 
		          $myDataClass->dataUpdateRelation("tbl_lnk".$ucType."ToHost",$chkDataId,$chkSelHostMembers);
		        else  $myDataClass->dataDeleteRelation("tbl_lnk".$ucType."ToHost",$chkDataId);
	
		        //hostgroup links 
				  //print "<p>SQL Response: ".mysql_error()."<br /> Rows affected: ".$myDBClass->intAffectedRows."</p>";
	
		        if ($intSelHostgroupMembers == 1) 
		          $myDataClass->dataUpdateRelation("tbl_lnk".$ucType."ToHostgroup",$chkDataId,$chkSelHostgroupMembers);
		        else  $myDataClass->dataDeleteRelation("tbl_lnk".$ucType."ToHostgroup",$chkDataId);
	
				  //print "<p>SQL Response: ".mysql_error()."<br /> Rows affected: ".$myDBClass->intAffectedRows."</p>";
				break; //end 'hostgroup' case
	////////////////////////////////////////////////////////
				case 'servicegroup':
	
					//servicegroup links 	        
			       if ($intSelServicegroupMembers == 1) 
			          $myDataClass->dataUpdateRelation("tbl_lnk".$ucType."ToServicegroup",$chkDataId,$chkSelServicegroupMembers);
			       else  $myDataClass->dataDeleteRelation("tbl_lnk".$ucType."ToServicegroup",$chkDataId);	  
			        
					//print "<p>SQL Response: ".mysql_error()."<br /> Rows affected: ".$myDBClass->intAffectedRows."</p>";	
					//service links 	        
			       if ($intSelHostServiceMembers == 1) 
			          $myDataClass->dataUpdateRelation("tbl_lnk".$ucType."ToService",$chkDataId,$chkSelHostServiceMembers,1);
			       else  $myDataClass->dataDeleteRelation("tbl_lnk".$ucType."ToService",$chkDataId);		 
			        
				    //print "<p>SQL Response: ".mysql_error()."<br /> Rows affected: ".$myDBClass->intAffectedRows."</p>";	 
	           
	         break;    
	         case 'contactgroup':
	         	 //contact member links 	        
			       if ($intSelContactMembers == 1) 
			          $myDataClass->dataUpdateRelation("tbl_lnk".$ucType."ToContact",$chkDataId,$chkSelContactMembers);
			       else  $myDataClass->dataDeleteRelation("tbl_lnk".$ucType."ToContact",$chkDataId);	  
			        
					 //print "<p>SQL Response: ".mysql_error()."<br /> Rows affected: ".$myDBClass->intAffectedRows."</p>";
					 
					 //contactgroup links 
					 if($intSelContactgroupMembers == 1) 
			          $myDataClass->dataUpdateRelation("tbl_lnk".$ucType."ToContactgroup",$chkDataId,$chkSelContactgroupMembers);
			       else  $myDataClass->dataDeleteRelation("tbl_lnk".$ucType."ToContactgroup",$chkDataId);
			       
	         	//print "<p>SQL Response: ".mysql_error()."<br /> Rows affected: ".$myDBClass->intAffectedRows."</p>";	
	         	
	         break;
				default:
				break;
	        
				} //END SWITCH 	        
	        
	      }//end modify IF 
	      $intReturn = 0;
	    }

	}
	
		
	// log return status and send back to page router 
	if (isset($intReturn) && ($intReturn == 1)) $strMessage .= $myDataClass->strDBMessage;
	if (isset($intReturn) && ($intReturn == 0)) $strMessage .= $ucType." <strong>".$chkTfName."</strong>".gettext(" sucessfully updated. ");
	//
	// Last database update and file date
	// ======================================
	$myConfigClass->lastModified("tbl_".$exactType,$strLastModified,$strFileDate,$strOld);
	//
	
	return array($errors, $strMessage.'<br />');  
}

?>
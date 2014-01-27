<?php  //delete_object.php




/** function delete_object() 
*	deletes a single object configuration from the nagiosql database, also removes relations 
*	@param string $table the appropriate object database table
*	@param int $id the object id/primary key for the nagios object 
*	@global object $myDataClass nagiosql data handler
*	@return array array(int $intReturn, string $strMessage) return data for browser output 
*/ 
function delete_object($table,$id,$audit=false) 
{
	global $myDataClass;
	global $ccmDB; 
	//bail if missing id 
	if(!$id) 
		return gettext("Cannot delete data, no object ID specified!")."<br />"; 
	
	if($table=='log') $table = 'logbook'; 	

	$strMessage = ''; 
	$intReturn = $myDataClass->dataDeleteFull("tbl_".$table,$id,0,$audit);
	$strMessage .= $myDataClass->strDBMessage;
	if($audit)
		audit_log(AUDITLOGTYPE_DELETE,$strMessage);
	//return success or failure message 
	return array($intReturn,$strMessage); 

}



/** function delete_multi() 
*	deletes multiple object configurations from the nagiosql database, also removes relations 
*	@param string $table the appropriate object database table
*	@global array $_REQUEST['checked'] array of $ids of the objects, id/primary key for the nagios object 
*	@return array array(int $intReturn, string $strMessage) return data for browser output 
*/ 
function delete_multi($table)
{
	$checks = ccm_grab_request_var('checked',array()); 
	//print_r($checks); 
	//delete all checked objects 
	$failMessage= ''; 
	$itemsDeleted = 0; 
	$itemsFailed = 0; 
	foreach($checks as $c)
	{
		$r = delete_object($table,$c,false); 
		if($r[0]==0) $itemsDeleted++; 
		else 
		{
			$itemsFailed++; 
			$failMessage .= $r[1]; //append DB return messages 	
		}	
	}	
	
	$intReturn = 0;
	$returnMessage = ''; 
	if($itemsFailed ==0 && $itemsDeleted==0) $returnMessage .= gettext("No items were deleted from the database.")."<br />"; 
	if ($itemsDeleted > 0) $returnMessage .= $itemsDeleted." ".gettext("items deleted from database")."<br />"; 
	if($itemsFailed > 0) 
	{
		$returnMessage .= "<strong>".$itemsFailed." ".gettext("items failed to delete.")."</strong><br />
													".gettext("Items may have dependent relationships that prevent deletion").".<br /> 
													".gettext("Use the 'info'  button to see all relationships.")."
													<img src='/nagiosql/images/info.gif' alt='' /><br />   
													$failMessage"; 
		$intReturn = 1; 	
	}	
	
	//audit log
	if($itemsDeleted > 0)
		audit_log(AUDITLOGTYPE_DELETE,$returnMessage);
	
	//return success or failure message 
	return array($intReturn,$returnMessage); 
}




?>
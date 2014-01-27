<?php //activate.inc.php //toggles active/inactive items in bulk



function route_activate($cmd,$type,$id) {

	$active = ($cmd == 'activate' || $cmd =='activate_multi') ? 1 : 0;
	
	if($cmd == 'deactivate' || $cmd =='activate')
		return single_toggle_active($id,$type,$active); 
	else			
		return multi_toggle_activate($type,$active);  	
}


/**
*	deactivates/activates a single entry from specified table
*	@param int $id db item ID
*	@param string $type db table type 
*	@param int $active the value to set the `active` field to: 0 | 1 
*	@return mixed array($errors, $message) 
*/
function single_toggle_active($id,$type,$active,$name=false) {

	global $ccmDB; 
	global $myConfigClass; 
	global $myDataClass;
	$errors = 0;	
	$message = ''; 
	
	if($name==false)
		$name = ccm_grab_request_var('objectName',''); 
	
	//hacks 
	if(!$id || !$type || $name =='') 
		trigger_error('Missing required arguments for "single_toggle_actiive()"',E_USER_ERROR);  
		
	//check to make sure this item can be disabled:
	if($active==0) {
		$bool = @$myDataClass->infoRelation('tbl_'.$type,$id,"id",1); 
		//echo $myDataClass->strDBMessage;
		//echo $bool; 
		//item cannot be disabled, dependent relationships
		if(intval($bool) == 1) {
			$message.=gettext("Item")." {$name} ".gettext("cannot be disabled because it has dependent relationships")."<br />"; 	
			return array(1,$message); 
		}
	}
				
	$query = "UPDATE tbl_{$type} SET `active`='{$active}' WHERE `id`={$id};";
	//echo $query; 
	//run query and capture any errors 
	$return = $ccmDB->query($query,false);
	if(!empty($return)) {
		$message .=gettext("Update query failed.")." <br />".mysql_error(); 
		$errors++; 
	}
					
    // If the host has been disabled - Delete File
	if(($active == 0) && ($type=='host' || $type=='service' ) ) {
  		$cfg = $name.".cfg";  	
  		//echo "$cfg<br />";
    	$intReturn = $myConfigClass->moveFile($type,$cfg);
    	if ($intReturn == 0) {
      		$message .=  gettext('Configuration files were deleted successfully!').'<br />';
      		$myDataClass->writeLog(gettext('Config file deleted:')." ".$cfg);
    	}	 
    	else {
      		$message .=  gettext('Errors while deleting the old configuration file: ').$cfg.gettext(' - please check permissions!')."<br />".$myConfigClass->strDBMessage;
    	 	$errors++; 
		}
	}

	if($errors == 0) 
		return array($errors, gettext("Item updated successfully!")."<br />".$message);   
	else 
		return array($errors, gettext("There was a problem updating the selected item type").": {$type}<br /> ID: {$id}<br />".$message); 
}




/**
*	enables / disables a selected array of objects 
*	@param string $type nagios object type
*	@param int $active boolean to set in the DB 
*	@return mixed $array( int $errors, string $message) 
*/
function multi_toggle_activate($type,$active) {

	global $ccmDB; 
	global $myConfigClass; 
	global $myDataClass;

	$failMessage= ''; 
	$itemsUpdated = 0; 
	$itemsFailed = 0; 
	
	$checks = ccm_grab_request_var('checked',array());
	$idString = implode($checks,',');  
	list($table,$name,$desc) = get_table_and_fields($type); 
		
	//fetch list of selected items 
	$query = "SELECT `id`,`{$name}` FROM tbl_{$type} WHERE `id` IN({$idString})"; 	
	$results = $ccmDB->query($query); 	
	
	foreach($results as $row) {		
		//handle each item individually and delete files if neccessary 
		$r = single_toggle_active($row['id'],$type,$active,$row[$name]); 
		//ccm_array_dump($r); 
		if($r[0]===0) $itemsUpdated++; 
		else {
			$itemsFailed++; 
			$failMessage .= $r[1]; //append DB return messages 	
		}		
	}	

	if($itemsFailed > 0) 
		return array($itemsFailed,$failMessage); 
	else 	 
		return array($itemsFailed,$itemsUpdated." ".gettext('items updated successfully!')." <br />"); 

}




?>
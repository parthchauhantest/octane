<?php //ccm_bulk_edit.inc.php  wizard page for making bulk changes in the CCM database 



function ccm_bulk_edit() {
	global $ccmDB;
	global $FIELDS; 
	$FIELDS = array(); 
	$returnClass = 'hidden'; 
	$feedback = ''; 
	
	$submitted = ccm_grab_request_var('submitted',false);
	
	if($submitted) {
		//ccm_array_dump($_REQUEST); 		
		list($errors,$feedback) = process_ccm_bulk_submission(); 
		$returnClass = $errors > 0 ? 'error' : 'success'; 
	}

	//display main page content 	
?>
	<script type="text/javascript" src="javascript/form_js.js"></script> <!-- jquery functions for form manipulation -->

	<div id='contentWrapper'>
	<h2 id='objectHeader'><?php echo gettext('Bulk Modification Tool'); ?></h2>
	<br /><br />
	<div id='returnContent' class='<?php echo $returnClass; ?>'><?php echo $feedback; ?></div>
	<div class='infoHeader'><?php echo gettext('The bulk modification tool allows for modifications to be made to specific host and service configurations.  
	This tool does not interact with settings or relationships defined in templates, and any settings applied by this tool will
	override any template settings.'); ?></div>
		
	<form action='index.php' method='post' id='formInput' name='frmInput'>
	
	<div id='bulk_stage1' class='bulk_wizard'>
		<h4><?php echo gettext('What would you like to modify?'); ?></h4>		
		<input type='button' class='ccmButton wideCcmButton' id='changeConfig' value="<?php echo gettext('Change A Single Config Option'); ?>" onclick='bulkWizard1("changeConfig")' /><br />
		<input type='button' class='ccmButton wideCcmButton' id='addContact' value="<?php echo gettext('Add a Contact'); ?>" onclick='bulkWizard1("addContact")' /><br />
		<input type='button' class='ccmButton wideCcmButton' id='removeContact' value="<?php echo gettext('Remove a Contact'); ?>" onclick='bulkWizard1("removeContact")' /><br />
		<input type='button' class='ccmButton wideCcmButton' id='addContactGroup' value="<?php echo gettext('Add a Contact Group'); ?>" onclick='bulkWizard1("addContactGroup")' /><br />
		<input type='button' class='ccmButton wideCcmButton' id='removeContactGroup' value="<?php echo gettext('Remove a Contact Group'); ?>" onclick='bulkWizard1("removeContactGroup")' /><br />		
	</div>	
	
	<!-- /// //  -->

	<div id='bulk_change_option' class='bulk_wizard' style='display:none'>
		<h4><?php echo gettext('Change a single configuration option'); ?></h4>
		<div id='config_options'>
			<select name='option_list' id='option_list' onchange='updateBulkForm()'>
				<?php echo get_bulk_hostservice_options(); ?>			
			</select>	
			<br />	
			<div class='padd'></div>			
			<input type='button' class="ccmButton" onclick="overlay('hostBox')" value="<?php echo gettext('Select Hosts'); ?>" />
			<input type='button' class="ccmButton"  onclick="overlay('serviceBox')" value="<?php echo gettext('Select Services'); ?>" />	
			<div class='padd'></div>
			<div id='inner_config_option'></div>	
		</div>
		<!-- select list of viable options, based on object type -->	
	</div>
		
	<!-- remove contact form -->
	
	<div id='contact_edit' style='display:none' class='bulk_wizard'>
		<h4><?php echo gettext('Select a Contact'); ?></h4>
		<label for='contact'><?php echo gettext('Contacts'); ?></label><br />
		<select name='contact' id='contact' onchange='$("#saveButton").css("display","inline")'>
			<option value='null'></option>
			<?php echo get_contact_list(); ?>
		</select><br /> 
		<div class='padd'></div>	
		
		<!-- hidden box -->
		<div id='findRelationships' style='display:none'>
			<input type='button' class='ccmButton' value='<?php echo gettext('Find Relationships'); ?>' onclick='getContactRelationships("<?php echo $_SESSION['token']; ?>");' />
		</div>
		
		<!-- hidden box -->
		<div id='overlayOptions' style='display:none'>
			<input type='button' class="ccmButton" onclick="overlay('hostBox')" value="<?php echo gettext('Select Hosts'); ?>" />
			<input type='button' class="ccmButton"  onclick="overlay('serviceBox')" value="<?php echo gettext('Select Services'); ?>" />	
		</div>
	
	</div>
	
	<!-- remove contactgroup form -->
	
	<div id='contactgroup_edit' style='display:none' class='bulk_wizard'>
		<h4><?php echo gettext('Select a Contact Group'); ?></h4>
		<label for='contactgroups'><?php echo gettext('Contact Groups'); ?></label><br />
		<select name='contactgroup' id='contactgroups' onchange='$("#saveButton").css("display","inline")'>
			<option value='null'></option>
			<?php echo get_contactgroup_list(); ?>
		</select><br /> 
		<div class='padd'></div>	
		
		<!-- hidden box -->
		<div id='findCgRelationships' style='display:none'>
			<input type='button' class='ccmButton' value='<?php echo gettext('Find Relationships'); ?>' onclick='getContactGroupRelationships("<?php echo $_SESSION['token']; ?>");' />
		</div>
		
		<!-- hidden box -->
		<div id='overlayOptionsCg' style='display:none'>
			<input type='button' class="ccmButton" onclick="overlay('hostBox')" value="<?php echo gettext('Select Hosts'); ?>" />
			<input type='button' class="ccmButton"  onclick="overlay('serviceBox')" value="<?php echo gettext('Select Services'); ?>" />	
		</div>
	
	</div>	
	
	
	<div id='relationships'></div> <!-- ajax loaded container -->
	
	<!-- add contact form -->
			
	<!-- bottom buttons -->
	<div class='bottombuttons' style="clear:both;">
	<div class='padd'></div>
<?php
	if($submitted)
		echo '<input type="button" value="'.gettext('Apply Configuration').'" onclick="apply_config()" id="applyConfig" class="ccmButton" name="applyConfig" /><br />';
	else 
		echo '<input type="button" class="ccmButton" value="'.gettext('Abort').'" id="abort" name="abort" onclick="window.location=\'index.php?cmd=admin&type=bulk\';"/>';	
?>	
	
	<div id='saveButton'><input type="submit" class='ccmButton' value="Save" id="subForm" name="subForm" /></div>
	<input type="hidden" value="true" id="submitted" name="submitted" />
	<input type='hidden' value='' id='bulkCmd' name='bulkCmd' />
	<input type='hidden' name='cmd' value='admin' />
	<input type='hidden' name='type' value='bulk' />
	</div>
	
<?php 	
	
	//create necessary $FIELDS arrays (selHosts, selServices, selContacts, and empty pre_arrays)  
	$FIELDS['selHostOpts'] = $ccmDB->get_tbl_opts('host');	
	$services = $ccmDB->query('SELECT `id`,`config_name`,`service_description` FROM tbl_service ORDER BY `config_name`,`service_description`');
	$FIELDS['selServiceOpts'] = array();
	//refactor array  
	foreach($services as $s)
		$FIELDS['selServiceOpts'][] = array('id'=> $s['id'], 'service_description' => $s['config_name'].'::'.$s['service_description']); 
	
	unset($services); //save a little memory 
	$FIELDS['pre_hosts'] = array(); 
	$FIELDS['pre_services'] = array();
	
	//ccm_array_dump($FIELDS['selServiceOpts']); 
	///////////////HIDDEN OVERLAYS/////////////////////

	
	
	echo "<div id='mainWrapper'>\n"; 
	echo  build_hidden_overlay('host','host_name');   //hosts 
	echo build_hidden_overlay('service','service_description');
	//echo build_hidden_overlay('contact','contact_name'); 
	echo "</div> <!-- end mainWrapper -->
		</form>
	</div> <!-- end contentWrapper div -->"; 
	
} //end ccm_bulk_edit() 


/**
*
*
*/
function get_bulk_hostservice_options() {

	$options = array(
		//text fields 
		'',
		'max_check_attempts',        
		'check_interval',        
		'retry_interval',               
		'freshness_threshold',  
		'low_flap_threshold',   
		'high_flap_threshold', 
		'notification_interval',  
		'notification_period',   
		'first_notification_delay',
		//integer form entries 
		'active_checks_enabled',
		'passive_checks_enabled',
		'check_freshness',
		'event_handler_enabled',
		'flap_detection_enabled',
		'retain_status_information',
		'retain_nonstatus_information',
		'process_perf_data',
		'notifications_enabled',			
	);
	
	$list = ''; 
	foreach($options as $opt)
		$list .="<option value='$opt'>$opt</option>\n";	
	
	return $list; 
}



/**
*	fetches an html select list of available nagios contacts 
*	@return string $html html option list 
*/
function get_contact_list() {
	global $ccmDB; 
	
	$contacts = $ccmDB->get_tbl_opts('contact');
	$html =''; 
	foreach($contacts as $c)
		$html.="<option value='".$c['id']."'>".$c['contact_name']."</option>\n"; 
		
	return $html; 		
}

/**
*	fetches an html select list of available nagios contactgroups 
*	@return string $html html option list 
*/
function get_contactgroup_list() {
	global $ccmDB; 
	
	$contacts = $ccmDB->get_tbl_opts('contactgroup');
	$html =''; 
	foreach($contacts as $c)
		$html.="<option value='".$c['id']."'>".$c['contactgroup_name']."</option>\n"; 
		
	return $html; 		
}


/**
*	handles server side form submissions for bulk modification tool 
*
*/
function process_ccm_bulk_submission() {
	//DEBUG:
	//dump_request(); 

	$config_option = ccm_grab_request_var('option_list',''); 
	$strValue = ccm_grab_request_var('txtForm','NULL'); 
	$intValue = ccm_grab_request_var('intForm','NULL');
	$contact = ccm_grab_request_var('contact',''); //contact id 
	$contactgroup = ccm_grab_request_var('contactgroup',''); 
	$bulkCmd = ccm_grab_request_var('bulkCmd',false); //change | add | remove 
	$hosts = ccm_grab_request_var('hosts',array());
	$services = ccm_grab_request_var('services',array()); 
	$hostschecked = ccm_grab_request_var('hostschecked',array());
	$serviceschecked = ccm_grab_request_var('serviceschecked',array()); 
	$errors = 0;
	$msg = '';
	
	//handle blank form value
	$strValue = ($strValue=='') ? 'NULL' : $strValue; 
	$intValue = ($intValue=='') ? 'NULL' : $intValue;
	
	switch($bulkCmd) {
	
		case 'add':
		$log = gettext("Add Contact Relationships"); 
		$errors = add_contact_relationships($contact,$hosts,$services,$msg);			
		break;
		
		case 'remove':
		$log = gettext("Remove Contact Relationships"); 
		$errors = remove_contact_relationships($contact,$hostschecked,$serviceschecked,$msg);
		break;
		
		case 'addcg':
		$log = gettext("Add Contact Group Relationships");
		$errors = add_contactgroup_relationships($contactgroup,$hosts,$services,$msg);			
		break;
		
		case 'removecg':
		$log = gettext("Remove Contact Group Relationships");
		$errors = remove_contactgroup_relationships($contactgroup,$hostschecked,$serviceschecked,$msg);
		break;
		
		case 'change':
		$log = gettext("Change Single Config Option");
		$errors = change_single_config($config_option,$strValue,$intValue,$hosts,$services,$msg);
		break;
		
		default:
			$errors=1;
			$msg.="".gettext('Invalid bulk command specified!')."<br />";
		break;
	
	}
	if($msg=='') {
		$msg = "".gettext('Updates saved successfully!')."<br />";
		audit_log(AUDITLOGTYPE_MODIFY,"".gettext('Bulk Modification command').": '$log' ".gettext('executed successfully').""); 
	}
	
	return array($errors,$msg); 

}

/**
*	adds relationships for a contact to selected hosts and services 
*	@param int $contact contact ID 
*	@param mixed $hosts array of host ID's
*	@param mixed $services array of service ID's 
*	@return int $errors count of any sql errors 
*/
function add_contact_relationships($contact,$hosts,$services,&$msg){

	global $ccmDB; 
	$errors=0;
		
	//update host booleans 
	if(!empty($hosts)) {
		$hoststring = implode(',',$hosts); 
		$query = "UPDATE tbl_host SET `contacts`='1' WHERE `id` IN ({$hoststring})"; 
		$ccmDB->query($query);
		//echo $query; 
		if(mysql_error() !='') {
			$errors++; 
			$msg .= $ccmDB->error; 	
		}	
		//add host relations 
		foreach($hosts as $host) {
			$query = "INSERT INTO tbl_lnkHostToContact SET `idMaster`='{$host}',`idSlave`='{$contact}'";  
			$ccmDB->query($query);
			if(mysql_error() !='') {
				$errors++; 	
				$msg .= $ccmDB->error; 	
			}	
		}		
	}//end if hosts 
		
	//update service booleans 
	if(!empty($services)) {
		$servicestring = implode(',',$services); 
		$query = "UPDATE tbl_service SET `contacts`='1' WHERE `id` IN ({$servicestring})"; 
		//echo $query;
		$ccmDB->query($query);
		if(mysql_error() !='') {
			$errors++; 
			$msg .= $ccmDB->error; 	
		}							
		//add service relations 
		foreach($services as $service) {
			$query = "INSERT INTO tbl_lnkServiceToContact SET `idMaster`='$service',`idSlave`='{$contact}';";  
			$ccmDB->query($query);
			if(mysql_error() !='') {
				$errors++; 	
				$msg .= $ccmDB->error; 
			}	
		}	
	}//end if services 	

	return $errors;
}//end add_contact_relationships


/**
*	adds relationships for a contactgroup to selected hosts and services 
*	@param int $contactgroup contactgroup ID 
*	@param mixed $hosts array of host ID's
*	@param mixed $services array of service ID's 
*	@return int $errors count of any sql errors 
*/
function add_contactgroup_relationships($contactgroup,$hosts,$services,&$msg){

	global $ccmDB; 
	$errors=0;
	
	//echo "CG: $contactgroup<br />"; 
		
	//update host booleans 
	if(!empty($hosts)) {
		$hoststring = implode(',',$hosts); 
		$query = "UPDATE tbl_host SET `contact_groups`='1' WHERE `id` IN ({$hoststring})"; 
		$ccmDB->query($query);
		//echo $query; 
		if(mysql_error() !='') {
			$errors++; 
			$msg .= $ccmDB->error; 	
		}	
		//add host relations 
		foreach($hosts as $host) {
			$query = "INSERT INTO tbl_lnkHostToContactgroup SET `idMaster`='{$host}',`idSlave`='{$contactgroup}'";  
			$ccmDB->query($query);
			if(mysql_error() !='') {
				$errors++; 	
				$msg .= $ccmDB->error; 	
			}	
		}		
	}//end if hosts 
		
	//update service booleans 
	if(!empty($services)) {
		$servicestring = implode(',',$services); 
		$query = "UPDATE tbl_service SET `contact_groups`='1' WHERE `id` IN ({$servicestring})"; 
		//echo $query;
		$ccmDB->query($query);
		if(mysql_error() !='') {
			$errors++; 
			$msg .= $ccmDB->error; 	
		}							
		//add service relations 
		foreach($services as $service) {
			$query = "INSERT INTO tbl_lnkServiceToContactgroup SET `idMaster`='$service',`idSlave`='{$contactgroup}';";  
			$ccmDB->query($query);
			if(mysql_error() !='') {
				$errors++; 	
				$msg .= $ccmDB->error; 
			}	
		}	
	}//end if services 	

	return $errors;
}//end add_contact_relationships




/**
*	removes relationships for a contact to selected hosts and services 
*	@param int $contact contact ID 
*	@param mixed $hosts array of host ID's
*	@param mixed $services array of service ID's 
*	@return int $errors count of any sql errors 
*/
function remove_contact_relationships($contact,$hosts,$services,&$msg){

	global $ccmDB; 
	$errors=0;
			
	//add host relations 
	if(!empty($hosts)) {
		foreach($hosts as $host) {
			$query = "DELETE FROM tbl_lnkHostToContact WHERE `idMaster`='$host' AND`idSlave`='{$contact}';";  
			$ccmDB->query($query);
			if(mysql_error() !='') {
				$errors++; 	
				$msg .= $ccmDB->error; 
			}	
		}
	}
	//add service relations 
	if(!empty($services)) {
		foreach($services as $service) {
			$query = "DELETE FROM tbl_lnkServiceToContact WHERE `idMaster`='$service' AND`idSlave`='{$contact}';";  
			$ccmDB->query($query);
			if(mysql_error() !='') {
				$errors++; 	
				$msg .= $ccmDB->error; 
			}	
		}		
	}

	return $errors;
}



/**
*	removes relationships for a contact to selected hosts and services 
*	@param int $contactgroup contactgroup ID 
*	@param mixed $hosts array of host ID's
*	@param mixed $services array of service ID's 
*	@return int $errors count of any sql errors 
*/
function remove_contactgroup_relationships($contact,$hosts,$services,&$msg){

	global $ccmDB; 
	$errors=0;
			
	//add host relations 
	if(!empty($hosts)) {
		foreach($hosts as $host) {
			$query = "DELETE FROM tbl_lnkHostToContactgroup WHERE `idMaster`='$host' AND`idSlave`='{$contactgroup}';";  
			$ccmDB->query($query);
			if(mysql_error() !='') {
				$errors++; 	
				$msg .= $ccmDB->error; 
			}	
		}
	}
	//add service relations 
	if(!empty($services)) {
		foreach($services as $service) {
			$query = "DELETE FROM tbl_lnkServiceToContactgroup WHERE `idMaster`='$service' AND`idSlave`='{$contactgroup}';";  
			$ccmDB->query($query);
			if(mysql_error() !='') {
				$errors++; 	
				$msg .= $ccmDB->error; 
			}	
		}		
	}

	return $errors;
}


/**
*	updates a single config setting for a list of hosts and services 
*	@param string $config config table option to modify
*	@param string $value the field value 
*	@param mixed $hosts array of host ID's
*	@param mixed $services array of service ID's 
*	@return int $errors count of any sql errors 
*/
function change_single_config($config,$strValue,$intValue,$hosts,$services,&$msg){

	global $ccmDB; 
	$errors=0;
	$intConfigs = array('active_checks_enabled','passive_checks_enabled','check_freshness',
						'obsess_over_host','event_handler_enabled','flap_detection_enabled',
						'retain_status_information','retain_nonstatus_information',
						'process_perf_data','notifications_enabled'); 
	$value = (in_array($config,$intConfigs)) ? $intValue : $strValue;
	
	//handler for NULL, int, and string entries 
	if(is_string($value) && $value !='NULL')
		$value = "'".$value."'"; 
	
	//update host booleans 
	if(!empty($hosts)) {
		$hoststring = implode(',',$hosts); 
		$query = "UPDATE tbl_host SET `$config`=$value WHERE `id` IN ({$hoststring})"; 
		$ccmDB->query($query);
		if(mysql_error() !='') {
			$errors++; 
			$msg .= $ccmDB->error; 
		}	
	}
		
	//update service booleans 
	if(empty($services)) 
		return $errors;
		
	$servicestring = implode(',',$services); 
	$query = "UPDATE tbl_service SET `$config`=$value WHERE `id` IN ({$servicestring})"; 
	$ccmDB->query($query);
	
	//sql_output($query); 
	
	if(mysql_error() !='') {
		$errors++; 
		$msg .= $ccmDB->error; 
	}	

	return $errors;		
}		

?>
<?php   //Form_class.php     form class for all CCM forms 


class Form
{
	

	//class properties 
	var $type;  //general form type: host, service, contact, timeperiods, commands
	var $template; //boolean: is this for a template? 
	var $preload;  //boolean: do we need to load this form with data?
	var $exactType; //establishes exact form type: example: host, hostgroup, hosttemplate, etc. 
//	var $formOutput; //html output for form  
	var $hostgroups_tploptions; 
	var $ucType; //upper-case first letter 
	var $cmd; 
	
	var $unique; 	
	
	private $mainTypes = array('host','service','hosttemplate','servicetemplate'); 
	
	/**
	* constructor:
	* establishes necessary properties to make decisions for object 
	*
	*/ 	
	function __construct($type,$cmd)
	{
		//setup class variables upon instantiation 
		$this->type=$type;
		$this->exactType=$type; 
		$this->cmd = $cmd; 
		$this->preload = ($cmd == 'modify') ? true : false;	
		$this->template = strstr($type,'template');
		if($this->template == 'template') $this->exactType = $type;
		
		//unique field entry for service/service template tables 
		$this->hostgroups_tploptions = ($this->type =='service') ? 'hostgroup_name_tploptions': 'hostgroups_tploptions';	
		$this->ucType = ucfirst($this->type); 			
		$this->unique = 100;
	} 

	/**
	* 	Function acquire data: grabs the appropriate element data from the database.  Used only for loaded forms
	* 									
	*	@param string $table: sql data table
	*	@param int $id table row ID 
	*	@return array $results all SQL data in assoc array for object ID 
	*/	
	function acquire_data($tbl, $id)
	{
		global $ccmDB; //global database object 
		$table = "tbl_".$tbl;		
		$query = "SELECT * FROM `$table` WHERE `id`=$id LIMIT 1"; 
		$results = $ccmDB->query($query);	
		return $results;
	}	
	
	
	/**
	*	prepares all acquired data for CCM form creation, assigns all data to $FIELDS array  
	*	@global array $FIELDS main array for form fields 
	*	@global object $ccmDB ccm DB handler
	*/ 
	function prepare_data()
	{
		global $FIELDS;
		global $ccmDB;
		//pass the table type and the table id, grab the data from the table, and then insert into the main $FIELDS array 
		$tbl = $this->exactType;
		$id = ($this->preload) ? ccm_grab_request_var('id','') : ''; //MUST HAVE ID  
		
		//figure out the template field entry??				
		$tbl_grp = $tbl.'group';
		if(!$this->template) $tbl_tmpl = $tbl.'template';
		else $tbl_tmpl = $tbl; 
				
		//if the mode is to 'modify', grab the DB info and load the $FIELDS array 
		if($id != '') {
			$data = $this->acquire_data($tbl,$id); 
			//print_r($data);	
			//add assoc keys with data to $FIELDS array 
			foreach($data as $d){
				foreach($d as $key =>$value) $FIELDS[$key] = $value;			
			}
		}//end 'modify' IF 
		
		//figure out if we're using host_name, config_name, template_name, etc 
		$opts = array('host','hostgroup','servicegroup','timeperiod','command','contact','contactgroup');		
		$name_field = (in_array($this->exactType,$opts)) ? $this->exactType.'_name' : 'template_name';
		$hidName = ($this->exactType=='service' || preg_match('/(escalation|dependency)/',$this->exactType) && $this->preload==true) ? $FIELDS['config_name'] : $FIELDS[$name_field];

		//hidden form fields 
		$FIELDS['hidName'] = $hidName;  
		$FIELDS['mode'] = $this->cmd; 
		$FIELDS['hidId'] = $id; 
		$FIELDS['exactType'] = $this->exactType; 
		$FIELDS['genericType'] = $this->type; 
		$FIELDS['returnUrl'] = ccm_grab_request_var('returnUrl',''); 
		
		//create base $FIELDS arrays 
		$this->init_field_arrays($tbl_tmpl); 
		$this->init_help_items();
		
		//special cases by object type 
		switch($this->exactType)
		{
			case 'host':
			case 'service':
			case 'hosttemplate':
			case 'servicetemplate':
				//get check commands if needed 
				$this->init_check_commands();
			break;
			case 'timeperiod':
				$this->init_timeperiod_vals(); 
			break;//end timeperiod 
			//unique service list for these pages 
			case 'serviceescalation':
				$this->init_unique_services();
				 
			break;
						
			case 'servicedependency':
				$this->init_unique_services();
				$this->init_dependency_arrays();
			break;
			
			case 'hostdependency':
				$this->init_dependency_arrays(); 							
			break;
			
			default:
			break;  
		}//end switch
		

		///////////////TODO:: move these functions to the switch above		
		
//		echo "Preload: $this->preload";
		if($this->preload==true)
		{
			//host relationships?
			$this->find_host_relationships();
			//parent relationships?
			$this->find_parent_relationships(); 		
			//hostgroups?
			$this->find_hostgroup_relationships(); 
			//servicegroups?
			$this->find_servicegroup_relationships(); 
			//contacts?
			$this->find_contact_relationships(); 
			//contactgroups?
			$this->find_contactgroup_relationships(); 
			//templates?
			$this->find_template_relationships();
			//variables?
			$this->find_variable_relationships(); 	
			
			//servicegroup to service relationships 
			$this->find_service_relationships(); 
			
		}
	}//end prepare_data 
	
		
	
	/**
	*	 initializes necessary arrays for $FIELDS array, which will populate the form 
	*
	*	@global object $ccmDB CCM DB handler 
	*	@global mixed $FIELDS main array for all form fields 
	*	@TODO clean up so that only necessary arrays are created 
	*	@return null 
	*/ 
	private function init_field_arrays($tbl_tmpl)
	{
		global $FIELDS; 
		global $ccmDB; 	
		
		//XXX TODO: only select data that is actually needed for config, separate function  
		//global field variables for select lists 
		// multi-dimensional of array('id' => #, 'object_name') 
		$FIELDS['selParentOpts'] = array();
		$FIELDS['selParentOpts'] = $ccmDB->get_tbl_opts('host');
		$FIELDs['selHostOpts'] = array(); 
		$FIELDS['selHostOpts'] = $ccmDB->get_tbl_opts('host');					
		$FIELDS['selHostgroupOpts'] = array();
		$FIELDS['selHostgroupOpts'] = $ccmDB->get_tbl_opts('hostgroup');		
		$FIELDS['selServicegroupOpts'] = array();
		$FIELDS['selServicegroupOpts'] = $ccmDB->get_tbl_opts('servicegroup');		
		$FIELDS['selHostServiceOpts'] = array(); 
		$FIELDS['selTemplateOpts'] = array();
		$FIELDS['selTimeperiods'] = array();
		$FIELDS['selTimeperiods'] = $ccmDB->get_tbl_opts('timeperiod');
		$FIELDS['selContactOpts'] = array();
		$FIELDS['selContactOpts'] = $ccmDB->get_tbl_opts('contact');					
		$FIELDS['selContactgroupOpts'] = array();
		$FIELDS['selContactgroupOpts'] = $ccmDB->get_tbl_opts('contactgroup');				
		$FIELDS['selEventHandlers'] = array();
		$FIELDS['selEventHandlers'] = $ccmDB->get_command_opts(2);		
		$FIELDS['freeVariables'] = array();
					
		//arrays for preloaded forms,
		//these arrays are used to determine what values should be preselected on page load
		//  AB and BA arrays are for showing two-way DB relationships 
		$FIELDS['pre_parents']=array(); 
		$FIELDS['pre_hosts_AB'] = array(); 
		$FIELDS['pre_hosts_BA'] = array();	
		$FIELDS['pre_hosts'] = array();  //used for escalations / dependencies only 
		$FIELDS['pre_services'] = array(); // used for escalations / dependencies only 
		$FIELDS['pre_hostgroups'] = array(); 
		$FIELDS['pre_hostgroups_AB']=array();
		$FIELDS['pre_hostgroups_BA']=array();			
		$FIELDS['pre_servicegroups_AB']=array();
		$FIELDS['pre_servicegroups_BA']=array();
		$FIELDS['pre_templates']=array();
		$FIELDS['pre_contacttemplates'] =& $FIELDS['pre_templates'];
		$FIELDS['pre_contacts_AB']=array();
		$FIELDS['pre_contacts_BA']=array();
		$FIELDS['pre_contactgroups_AB']=array();
		$FIELDS['pre_contactgroups_BA']=array();
		$FIELDS['selCommandOpts'] = array();
		
		//servicegroup specific 
		$FIELDS['pre_hostservices_AB'] = array(); 
		$FIELDS['pre_hostservices_BA'] = array(); 
				
		//contacts specific 
		$FIELDS['pre_hostcommands'] = array();
		$FIELDS['pre_servicecommands'] = array();
		
		
		/////////////////////////LOGIC BASED array initializers //////////////////		
		//for servicegroups page only 
		if($this->exactType=='servicegroup') 
			$FIELDS['selHostServiceOpts'] = $ccmDB->get_hostservice_opts(); 
			
		//host,service,hosttemplate,servicetemplate, 
		if(in_array($this->exactType,$this->mainTypes)) {
			$FIELDS['selCommandOpts'] = $ccmDB->get_command_opts(1);			
			$FIELDS['selTemplateOpts'] = $ccmDB->get_tbl_opts($tbl_tmpl);	
		}
			
		//special stuff for contact/contacttemplate 
		if($this->exactType=='contact' || $this->exactType=='contacttemplate') {
			$FIELDS['selTemplateOpts'] = $ccmDB->get_tbl_opts('contacttemplate');			  
			$this->find_command_relationships(); 
			$FIELDS['selContacttemplateOpts'] =& $FIELDS['selTemplateOpts'];		
		}//end contact IF 	
							
		//  "use as template options" 
		if(in_array($this->exactType,array('host','service','contact')) ) {
			$nameTemplates = $ccmDB->query("SELECT id,name FROM tbl_".$this->exactType." WHERE name!='' AND name!='NULL';");  
			foreach($nameTemplates as $t) 
			    $FIELDS['selTemplateOpts'][] = array('id' => $t['id'].'::2','template_name'=>$t['name']); 		
		}		
		 		
		///////////////////add wildcards where appropriate/////////////////////// 
		if(in_array($this->exactType, array('contact','contacttemplate','serviceescalation')) )
			$FIELDS['selContactgroupOpts'][] = array('id' => '*', 'contactgroup_name' => '*'); 	
		if(in_array($this->exactType,array('contactgroup','serviceescalation')))    
			$FIELDS['selContactOpts'][] = array('id' => '*', 'contact_name' => '*'); 				
		if(in_array($this->exactType, array('service','servicetemplate','serviceescalation')) ) 
			$FIELDS['selHostgroupOpts'][] = array('id' => '*', 'hostgroup_name' => '*');  		
		if(in_array($this->exactType, array('service','servicetemplate','hostgroup','serviceescalation'))  )
			$FIELDS['selHostOpts'][] = array('id' => '*', 'host_name' => '*');  

	}
	
	
	/** init_check_commands
	*	
	*	 initializes check_command values/arrays for $FIELDS array as necessary 
	*
	*	@global mixed $FIELDS
	*	@return null 
	*/ 
	function init_check_commands()
	{
		global $FIELDS; 
		
		//check command, is there is a check command defined for this 
		if(isset($FIELDS['check_command']) && $FIELDS['check_command'] != NULL) {						
			//explode commandline arguments     //example: // '44!3000.0!80%!5000.0!100%'
			$cmd_vals = explode('!', $FIELDS['check_command']);
			
			//first items in the check command string is the field ID 
			$FIELDS['sel_check_command'] = isset($cmd_vals[0]) ? $cmd_vals[0] : "";
			//print "<p> selected command is: .".$FIELDS['sel_check_command'].".</p>";
			//grab the actual command to print out in the form 
			foreach($FIELDS['selCommandOpts'] as $opt) {
				if($cmd_vals[0]==$opt['id'])
				$FIELDS['fullcommand'] = $opt['command_line'];	
			}
			//assign any command line arguments to their own text field 
			$FIELDS['tfArg1'] = isset($cmd_vals[1]) ? $cmd_vals[1] : '';
			$FIELDS['tfArg2'] = isset($cmd_vals[2]) ? $cmd_vals[2] : '';
			$FIELDS['tfArg3'] = isset($cmd_vals[3]) ? $cmd_vals[3] : '';
			$FIELDS['tfArg4'] = isset($cmd_vals[4]) ? $cmd_vals[4] : '';
			$FIELDS['tfArg5'] = isset($cmd_vals[5]) ? $cmd_vals[5] : '';
			$FIELDS['tfArg6'] = isset($cmd_vals[6]) ? $cmd_vals[6] : '';
			$FIELDS['tfArg7'] = isset($cmd_vals[7]) ? $cmd_vals[7] : '';
			$FIELDS['tfArg8'] = isset($cmd_vals[8]) ? $cmd_vals[8] : '';							
		}//end IF 
		else {//set necessary variables 
			$FIELDS['check_command'] = ''; 
			$FIELDS['sel_check_command'] = '';
		}
		
	}
	
	
	/** find_host_relationships
	*	
	*	 finds and creates host relationship arrays for $FIELDS array (as necessary) 
	*
	*	@global array $FIELDS
	*	@return null 
	*/ 
	function find_host_relationships()
	{
		global $FIELDS; 
		global $ccmDB; 
		if(!strpos($this->exactType,'dependency')) { //everything other than a dependency 
			//does this object have host relationships (service specific)
			if( (isset($FIELDS['host_name']) && $FIELDS['host_name'] ==1) || (isset($FIELDS['members']) && $FIELDS['members']== 1) ) {
				//grab active host relationships 
				$hosts1 = $ccmDB->find_links($FIELDS['id'], $this->ucType.'ToHost','master');
				foreach($hosts1 as $h) $FIELDS['pre_hosts_AB'][] = $h['idSlave'];					
			}
			//find other DB relationships 
			$hosts2 = $ccmDB->find_links($FIELDS['id'], 'HostTo'.$this->ucType,'slave');		
			foreach($hosts2 as $h) $FIELDS['pre_hosts_BA'][] = $h['idMaster'];
		}
		else { //special case for dependencies 
			if( (isset($FIELDS['host_name']) && $FIELDS['host_name'] ==1)) {
				//grab active host relationships 
				$hosts = $ccmDB->find_links($FIELDS['id'], $this->ucType.'ToHost_H','master');
				foreach($hosts as $h) $FIELDS['pre_hosts'][] = $h['idSlave'];					
			}
		}	
		
		//wildcard selection
		if((isset($FIELDS['host_name']) && $FIELDS['host_name'] ==2) ||
			(isset($FIELDS['members']) && $FIELDS['members']== 2 && 
			!strpos($this->exactType,'dependency'))) {
			$FIELDS['pre_hosts'][] = '*'; 
			$FIELDS['pre_hosts_AB'][] = '*';	
		}	
	}	
	
	/**
	*	 finds and creates parent relationship arrays for $FIELDS array (as necessary) 
	*
	*	@global array $FIELDS
	*	@return null 
	*/ 
	function find_parent_relationships()
	{
		global $FIELDS;
		global $ccmDB;
		//does this object have parents?
		if(isset($FIELDS['parents']) && $FIELDS['parents'] == 1) {
			//grab parent array
			$parents = $ccmDB->find_links($FIELDS['id'], $this->ucType.'ToHost', 'master');
			foreach($parents as $p) $FIELDS['pre_parents'][] = $p['idSlave'];	 
		}	
	}
	
	/** find_hostgroup_relationships
	*	
	*	 finds and creates hostgroup relationship arrays for $FIELDS array (as necessary) 
	*
	*	@global array $FIELDS
	*	@return null 
	*/ 	
	function find_hostgroup_relationships()
	{
		global $FIELDS;
		global $ccmDB;
		
		if(!strpos($this->exactType,'dependency')) { 
			//does this object have hostgroup relationships?  							//services handled differently 
			if( isset($FIELDS['hostgroups']) && $FIELDS['hostgroups'] ==1 || isset($FIELDS['hostgroup_name'])) {
				//grab hostgroup memberships 
				$h_groups1 = $ccmDB->find_links($FIELDS['id'], $this->ucType.'ToHostgroup','master');
				foreach($h_groups1 as $h) $FIELDS['pre_hostgroups_AB'][] = $h['idSlave'];	
			}	
			//find indirect hostgroup relationships 
			if($this->exactType != 'hostgroup') {
				$h_groups2 = $ccmDB->find_links($FIELDS['id'], 'HostgroupTo'.$this->ucType,'slave');
				foreach($h_groups2 as $h) $FIELDS['pre_hostgroups_BA'][] = $h['idMaster'];	//hostgroups to <object> relationships 
			}
			else { //special case for hostgroups form 
				$h_groups2 = $ccmDB->find_links($FIELDS['id'], 'HostgroupTo'.$this->ucType,'slave');
				foreach($h_groups2 as $h) $FIELDS['pre_hostgroups_BA'][] = $h['idMaster'];	//hostgroups to <object> relationships 
			}					
		} //if NOT dependency
		else { //dependency has special cases 
			$FIELDS['pre_hostgroups'] = array(); 
			if( (isset($FIELDS['hostgroup_name']) && $FIELDS['hostgroup_name'] ==1)) {
				//grab active host relationships 
				$hosts = $ccmDB->find_links($FIELDS['id'], $this->ucType.'ToHostgroup_H','master');
				foreach($hosts as $h) $FIELDS['pre_hostgroups'][] = $h['idSlave'];					
			}
			//ccm_array_dump($FIELDS['pre_hosts']);			
		}

		//wildcard selection
		if( (isset($FIELDS['hostgroup_name']) && $FIELDS['hostgroup_name'] ==2) ||
			(isset($FIELDS['hostgroups']) && $FIELDS['hostgroups'] ==2) ) {
			$FIELDS['pre_hostgroups'][] = '*';
			$FIELDS['pre_hostgroups_AB'][] = '*';
		}		
	}
	
	/** find_servicegroup_relationships
	*	
	*	 finds and creates servicegroup relationship arrays for $FIELDS array (as necessary) 
	*
	*	@global array $FIELDS
	*	@return null 
	*/ 
	function find_servicegroup_relationships()
	{
		global $FIELDS; 
		global $ccmDB;
		//does this object have servicegroup relationships? 
		if(isset($FIELDS['servicegroups']) && $FIELDS['servicegroups'] ==1 || isset($FIELDS['servicegroup_name']) ) {
			//grab servicegroup memberships		 
			$s_groups1 = $ccmDB->find_links($FIELDS['id'], $this->ucType.'ToServicegroup','master');
			foreach($s_groups1 as $s) $FIELDS['pre_servicegroups_AB'][] = $s['idSlave'];	
			//find indirect hostgroup relationships 
	
			//TODO: show service->servicegroup relationships. Needs another fancy query since the 
					//tbl_lnkServicegroupToService table is yet ANOTHER exception to the norm. 
	
			//print_r($FIELDS['pre_hostgroups']);							
		}
		//servicegroup to service relationships
		//Takes a special query for this:
		if($this->exactType=='servicegroup') {
			$q = "SELECT sg.servicegroup_name, hosts.id as hostid,service.id as serviceid
					FROM tbl_lnkServiceToServicegroup as sglinks
					INNER JOIN tbl_service as service on sglinks.idMaster=service.id
					INNER JOIN tbl_lnkServiceToHost on tbl_lnkServiceToHost.idMaster=service.id
					INNER JOIN tbl_host as hosts on tbl_lnkServiceToHost.idSlave=hosts.id
					INNER JOIN tbl_servicegroup as sg on sg.id=sglinks.idSlave
					WHERE sg.id='".$FIELDS['id']."'";
			
			$s_groups2 = $ccmDB->query($q);
			foreach($s_groups2 as $s) $FIELDS['pre_hostservices_BA'][] = $s['hostid'].'::0::'.$s['serviceid'];	
		}
	}
	
	/** find_service_relationships
	*	
	*	 finds and creates servicegroup relationship arrays for $FIELDS array (as necessary) 
	*	 (servicegroup page specific)
	*
	*	@global array $FIELDS
	*	@return null 
	*/ 
	function find_service_relationships()
	{
		global $FIELDS; 
		global $ccmDB;
		if(!strpos($this->exactType,'dependency')) {
			//does this object have servicegroup relationships? 
			if(isset($FIELDS['members']) && $FIELDS['members'] ==1 && $this->exactType=='servicegroup') {
				$services = $ccmDB->find_service_links($FIELDS['id']);
				foreach($services as $s) $FIELDS['pre_hostservices_AB'][] = $s;		
				//print_r($services); 
			}
			//servicegroup to <object> relationships 
			$s_groups2 = $ccmDB->find_links($FIELDS['id'], 'ServiceTo'.$this->ucType,'slave');
			//print_r($s_groups2); 
			foreach($s_groups2 as $s) $FIELDS['pre_hostservices_BA'][] = $s['idMaster'];	
		}
		else { //special circumstance for dependencies 
			$FIELDS['pre_services'] = array(); 
			if( (isset($FIELDS['service_description']) && $FIELDS['service_description'] ==1)) {
				//grab active host relationships 
				$service = $ccmDB->find_links($FIELDS['id'], $this->ucType.'ToService_S','master');
				foreach($service as $s) $FIELDS['pre_services'][] = $s['idSlave'];					
			}	
		}
		
		//wildcard selection
		if((isset($FIELDS['service_description']) && $FIELDS['service_description'] ==2) || isset($FIELDS['members']) && $FIELDS['members'] ==2) {
			$FIELDS['pre_services'][] = '*';
			$FIELDS['pre_hostservices_AB'][] = '*';
		}		
	}
	
	/** find_template_relationships
	*	
	*	 finds and creates template relationship arrays for $FIELDS array (as necessary) 
	*
	*	@global array $FIELDS
	*	@return null 
	*/ 
	function find_template_relationships()
	{
		global $FIELDS; 
		global $ccmDB;
		//is this object using a template?  
		if(isset($FIELDS['use_template']) && $FIELDS['use_template'] ==1)
		{
			//grab all templates	
			$tblTemplate = ($this->exactType =='hosttemplate' || 
							$this->exactType=='servicetemplate' || 
							$this->exactType=='contacttemplate') ? $this->ucType : $this->ucType.'template';   

			$dep1 = $ccmDB->find_links($FIELDS['id'], $this->ucType.'To'.$tblTemplate,'master',1);
			//check for named templates 
			$dep2 = $ccmDB->find_links($FIELDS['id'], $this->ucType.'To'.$tblTemplate,'master',2);
			//preselected templates
            
            		// sort the results
            		foreach ($dep1 as $key => $row) {
            		    $sort[$key]  = $row['idSort'];
            		}
            		array_multisort($sort, SORT_ASC, $dep1);

			foreach($dep1 as $h) $FIELDS['pre_templates'][] = $h['idSlave'];	
			foreach($dep2 as $h) $FIELDS['pre_templates'][] = $h['idSlave'].'::2'; 
			//array order matters for template 		
            //arsort($FIELDS['pre_templates']);

		}		
	}

	/** find_contact_relationships
	*	
	*	 finds and creates contact relationship arrays for $FIELDS array (as necessary) 
	*
	*	@global array $FIELDS
	*	@return null 
	*/ 
	function find_contact_relationships()
	{
		global $FIELDS; 
		global $ccmDB;
		//does this object have contacts?  
		if( (isset($FIELDS['contacts']) && $FIELDS['contacts']==1) || ($this->exactType=='contactgroup' && $FIELDS['members']==1) ) {
			//get contacts	
			$dep1 = $ccmDB->find_links($FIELDS['id'], $this->ucType.'ToContact','master');			
			foreach($dep1 as $h) $FIELDS['pre_contacts_AB'][] = $h['idSlave'];
		}	
		//indirect DB relationships 
		$dep2 = $ccmDB->find_links($FIELDS['id'], 'ContactTo'.$this->ucType,'slave');
		foreach($dep2 as $h) $FIELDS['pre_contacts_BA'][] = $h['idMaster'];
		
		//wildcard selection
		if((isset($FIELDS['contacts']) && $FIELDS['contacts'] ==2) || ($this->exactType=='contactgroup' && $FIELDS['members']==2)) {
			$FIELDS['pre_contacts'][] ='*';
			$FIELDS['pre_contacts_AB'][] ='*';
		}		
		
	}
	
	/** find_contactgroup_relationships
	*	
	*	 finds and creates contactgroup relationship arrays for $FIELDS array (as necessary) 
	*
	*	@global array $FIELDS
	*	@return null 
	*/ 
	function find_contactgroup_relationships()
	{
		global $FIELDS; 
		global $ccmDB;
		//does this object have contact groups?  
		if(( isset($FIELDS['contact_groups']) && $FIELDS['contact_groups'] ==1) || 
				($this->exactType=='contactgroup' && $FIELDS['contactgroup_members']==1) ||
				isset($FIELDS['contactgroups']) && $FIELDS['contactgroups']==1 )
		{
			//get contactgroups	
			$dep1 = $ccmDB->find_links($FIELDS['id'], $this->ucType.'ToContactgroup','master');
			foreach($dep1 as $h) $FIELDS['pre_contactgroups_AB'][] = $h['idSlave'];
			//get two way dependencies 
			if($this->exactType=='contact' || $this->exactType=='contacttemplate') {
				$dep2 = $ccmDB->find_links($FIELDS['id'], 'ContactgroupTo'.$this->ucType,'slave');				
				foreach($dep2 as $h) $FIELDS['pre_contactgroups_BA'][] = $h['idMaster'];
			} //end IF contact | contactTemplate  				
		}//end main IF 	
		
		//wildcard selection
		if( (isset($FIELDS['contacts_groups']) && $FIELDS['contact_groups'] ==2) ||
			(isset($FIELDS['contactgroups_members']) && $FIELDS['contactgroup_members'] ==2) ||	
			isset($FIELDS['contactgroups']) && $FIELDS['contactgroups']==2) {
			$FIELDS['pre_contactgroups'][] ='*';
			$FIELDS['pre_contactgroups_AB'][] ='*';
		}	

	} //end find_contactgroup_relationships 
	
	
	/** find_command_relationships
	*	
	*	 finds and creates contact to host/service command relationship arrays for $FIELDS array (as necessary) 
	*
	*	@global array $FIELDS
	*	@return null 
	*/ 
	function find_command_relationships()
	{
		global $FIELDS; 
		global $ccmDB;
		//does this object have host/service command relationships?  
		if(isset($FIELDS['host_notification_commands']) && $FIELDS['host_notification_commands'] ==1) {
			//get command links 
			$dep1 = $ccmDB->find_links($FIELDS['id'], $this->ucType.'ToCommandHost','master');
			foreach($dep1 as $h) $FIELDS['pre_hostcommands'][] = $h['idSlave'];	
		}	
		if(isset($FIELDS['service_notification_commands']) && $FIELDS['service_notification_commands'] ==1)  {
			//get command links 
			$dep1 = $ccmDB->find_links($FIELDS['id'], $this->ucType.'ToCommandService','master');
			foreach($dep1 as $h) $FIELDS['pre_servicecommands'][] = $h['idSlave'];		
		}	
	}//end find_command_relationships() 
	
	/** find_variable_relationships
	*	
	*	 finds and creates variable relationship arrays for $FIELDS array (as necessary) 
	*
	*	@global mixed $FIELDS
	*	@return null 
	*/ 
	function find_variable_relationships()
	{
		global $FIELDS;
		global $ccmDB;
		//if free variables in use? 
		if(isset($FIELDS['use_variables']) && $FIELDS['use_variables']==1)
		{
			$varDefs = $ccmDB->find_links($FIELDS['id'], $this->ucType.'ToVariabledefinition', 'master');
			if($varDefs > 0) {
				$results = array();
				foreach($varDefs as $v) {
					$results = $ccmDB->search_query('tbl_variabledefinition', 'id', $v['idSlave']);
					$array = array( 'name' => $results[0]['name'], 'value' => $results[0]['value']);
					$FIELDS['freeVariables'][] = $array;
				}//end foreach 
			}// end IF 	
		}// end if variables in use 		
	} //end find_variable_relationships() 
	
	/** init_timeperiod_vals() 
	*	
	*	 initializes form fields and values for timeperiods page  
	*
	*	@global mixed $FIELDS
	*	@return null 
	*/ 
	function init_timeperiod_vals()
	{
		global $FIELDS;
		global $ccmDB;
		
		$query = "SELECT id,timeperiod_name FROM tbl_timeperiod;";  
		$FIELDS['selExcludeOpts'] = $ccmDB->query($query);  
		$FIELDS['pre_excludes'] = array(); 
		$FIELDS['timedefinitions'] = array();
		$FIELDS['timeranges'] = array(); 
		
		//fetch timeperiod list 
		if($FIELDS['mode']=='modify' && $FIELDS['exclude']==1) {			
		   $links = $ccmDB->find_links($FIELDS['id'], $this->ucType.'ToTimeperiod','master');
			foreach($links as $link) $FIELDS['pre_excludes'][] = $link['idSlave'];		
		} 	
		//fetch preload values 
		if($FIELDS['mode']=='modify') {
			$query = "SELECT `definition`,`range` FROM tbl_timedefinition where tipId='".$FIELDS['id']."';";
			$results = $ccmDB->query($query); 
			foreach($results as $r) {
				$FIELDS['timedefinitions'][] = $r['definition'];
				$FIELDS['timeranges'][]		 = $r['range']; 
			} //end foreach 
		}//end IF 	
	} //end init_timeperiod_vals()
	
	/**
	*	build unique services list and pre-select list 
	*/
	private function init_unique_services() 
	{
		global $FIELDS;
		global $ccmDB;
		$query = "SELECT DISTINCT (`service_description`), `id` FROM `tbl_service` WHERE `active`='1' 
  					 GROUP BY hex(`service_description`) ORDER BY `service_description`";
		$FIELDS['selServiceOpts'] = $ccmDB->query($query);
		if( (isset($FIELDS['service_description']) && $FIELDS['service_description'] ==1) ) {
			//grab service relationships 
			$services = $ccmDB->find_links($FIELDS['id'], $this->ucType.'ToService','master');
			foreach($services as $s) $FIELDS['pre_services'][] = $s['idSlave'];					
		}	 
		
		//handle wildcard selections
		if(in_array($this->exactType,array('serviceescalation','servicedependency')))
			$FIELDS['selServiceOpts'][] = array('id' => '*', 'service_description' => '*');
		// #2 means they chose a wildcard
		if( (isset($FIELDS['service_description']) && $FIELDS['service_description'] ==2) )
			$FIELDS['pre_services'][] = '*';
			
	}//end init_unique_services() 
	
	
	/**
	* create necessary arrays for host,service,hostgroup dependencies 
	*/ 
	private function init_dependency_arrays() {
		global $FIELDS;
		global $ccmDB;
		$FIELDS['selHostDepOpts'] = &$FIELDS['selHostOpts'];
		$FIELDS['selHostgroupDepOpts'] = &$FIELDS['selHostgroupOpts'];
		$FIELDS['selServiceDepOpts'] = &$FIELDS['selServiceOpts'];	//unique service list
		$FIELDS['pre_hostdependencys'] = array(); //yes, I'm aware of the grammar error here, and it bothers me - MG 
		$FIELDS['pre_hostgroupdependencys'] = array();
		$FIELDS['pre_servicedependencys'] = array();
		$FIELDS['pre_hosts_AB'] = &$FIELDS['pre_hosts']; //this is ugly and probably shouldn't be done this way 
		$FIELDS['pre_hostgroups_AB'] = &$FIELDS['pre_hostgroups'];
		$FIELDS['pre_services_AB'] = &$FIELDS['pre_services'];
		//build pre_arrays for prepopulated form 
		
		//host and hostgroups arrays are established in the the methods: find_host_relationships and find_hostgroup_relationships 
		//dependent host
		if( (isset($FIELDS['dependent_host_name']) && $FIELDS['dependent_host_name'] ==1) ) {
			//grab service relationships 
			$objects = $ccmDB->find_links($FIELDS['id'], $this->ucType.'ToHost_DH','master');
			foreach($objects as $s) $FIELDS['pre_hostdependencys'][] = $s['idSlave'];					
		}			
		//dependent hostgroup 
		if( (isset($FIELDS['dependent_hostgroup_name']) && $FIELDS['dependent_hostgroup_name'] ==1) ) {
			//grab service relationships 
			$objects = $ccmDB->find_links($FIELDS['id'], $this->ucType.'ToHostgroup_DH','master');
			foreach($objects as $s) $FIELDS['pre_hostgroupdependencys'][] = $s['idSlave'];					
		}
		//dependent services 	
		if( (isset($FIELDS['dependent_service_description']) && $FIELDS['dependent_service_description'] ==1) ) {
			//grab service relationships 
			$objects = $ccmDB->find_links($FIELDS['id'], $this->ucType.'ToService_DS','master');
			foreach($objects as $s) $FIELDS['pre_servicedependencys'][] = $s['idSlave'];					
		}		

		//wildcard selection
		if( (isset($FIELDS['dependent_service_description']) && $FIELDS['dependent_service_description'] ==2) ) {
			$FIELDS['pre_servicedependencys'][] = '*';
		}
		
	}	
	
	
	/**
	*	creates documentation listings on any appropriate forms 
	*/
	private function init_help_items() {
		global $ccmDB;
		global $FIELDS;
		$type = $this->exactType;
		//handle exceptions for templates 
		if($type=='hosttemplate') $type='host';
		if($type=='servicetemplate') $type='service';
		if($type=='contacttemplate') $type='contact';
		$FIELDS['infotype'] = $type;
		
		$query = "SELECT `key2` FROM tbl_info WHERE `key1`='{$type}' ORDER BY `key2` ASC"; 
		$FIELDS['info'] = $ccmDB->query($query);
		//sql_output($query); 		
	}
	
	
	/** build_form
	*	
	*	 calls and include appropriate form templates based on object type. Prints direct html output to screen.    
	*	@global array $CFG main config array 
	*	@global mixed $FIELDS
	*	@return null 
	*/ 
	function build_form()
	{
		global $FIELDS;
		global $CFG; 
		//is form loaded?
		
		//DEBUG 
		$debug=ccm_grab_array_var($_SESSION,'debug',false);
		if($debug && $this->preload) {
			ccm_array_dump($FIELDS);
		}		

		include(TPLDIR.'form_header.php'); //common page start for all CCM forms 
		// load form parts based on object type from template files 
		

		switch($this->exactType)
		{		
			case 'host':
			case 'service':
			case 'hosttemplate':
			case 'servicetemplate':
				//if loaded, process array so that it can be passed into the form 				
				include(TPLDIR.'common_settings.php');
				include(TPLDIR.'check_settings.php');
				include(TPLDIR.'alert_settings.php');
				include(TPLDIR.'misc_settings.php');
				include(TPLDIR.'hidden_elements.php');				 
			break; 	
			
			case 'hostgroup':
			case 'servicegroup':
			case 'contactgroup':
				include(TPLDIR.'group_template.php');
				include(TPLDIR.'hidden_elements.php');
			break;
			
			case 'timeperiod':
				include(TPLDIR.'timeperiod_template.php'); 
				include(TPLDIR.'hidden_elements.php'); 
			break; 
			
			case 'contact':
			case 'contacttemplate':
				include(TPLDIR.'contact_template.php');
				include(TPLDIR.'misc_settings.php');
				include(TPLDIR.'hidden_elements.php'); 
			break;
			
			case 'command':
				include(TPLDIR.'command_template.php'); 				
			break; 
			
			case 'hostescalation':
			case 'hostdependency':
			case 'serviceescalation':
			case 'servicedependency':
				include(TPLDIR.'escalation_dependency.inc.php');
				include(TPLDIR.'hidden_elements.php');
			break; 
			
			case 'user':
				require_once(INCDIR.'admin_views.inc.php'); 
				manage_user_html(); 
			break; 
			
			default:
				echo "no template defined yet!";
				echo $this->exactType;
			break;
		}//end switch 
		
		include(TPLDIR.'form_footer.php');
				
	}//end build_form() 
	
	
} //end Form class 






?>

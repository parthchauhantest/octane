<?php   //hidden_elements.php 


//all hidden overlay functions are defined on INCDIR/hidden_overlay_functions.inc.php 
/** function build_hidden_overlay() 
*	builds a hidden overlay div and populates values based on parameters given 
*
*	@param string 	$type nagios object type (host, service, command, etc)
*	@param string 	$optionValue the DB fieldname for that objects name (host_name, service_description, template_name)
*	@param bool	$BA boolean switch, are there two-way relationships possible for this object (host->hostgroup, hostgroup->host) 
*	@param bool $tplOpts boolean switch for showing template options 
*	@param string $fieldArray optional specification for which select list to use 
*	@return string returns populated html select lists for the $type object  
*/

switch($this->exactType)
{   
 
	case 'host': 
	case 'hosttemplate':
		echo build_hidden_overlay('parent','host_name',false,true);   					//parents 
		echo build_hidden_overlay('hostgroup','hostgroup_name',true,true); 				//hostgroups 
		echo build_hidden_overlay('template','template_name',false,false); 				//templates 
		echo build_hidden_overlay('contactgroup','contactgroup_name',false,true);		//contactgroups  
		echo build_hidden_overlay('contact','contact_name',true,true); 					//contacts 
		echo build_command_output_box(); 															//command test 
		echo build_variable_box(); 	
	break;
	case 'service':
	case 'servicetemplate': 		
		echo build_hidden_overlay('host','host_name',true,true);   							//hosts 
		echo build_hidden_overlay('hostgroup','hostgroup_name',true,true); 				//hostgroups 
		echo build_hidden_overlay('servicegroup','servicegroup_name',true,true); 		//servicegroups 
		echo build_hidden_overlay('template','template_name',false,false); 				//templates 
		echo build_hidden_overlay('contactgroup','contactgroup_name',false,true);		//contactgroups  
		echo build_hidden_overlay('contact','contact_name',true,true); 					//contacts 
		echo build_command_output_box(); 															//command test 
		echo build_variable_box(); 																	//free variables 
	break;
	case 'hostgroup':
		echo build_hidden_overlay('host','host_name',true,false);   							//hosts 
		echo build_hidden_overlay('hostgroup','hostgroup_name',true,false); 				//hostgroups 
	break; 
	case 'servicegroup':
		echo build_hidden_overlay('servicegroup','servicegroup_name',true,false); 		//servicegroups
		echo build_hidden_overlay('hostservice','servicegroup_name',false,false);		//services 
	break; 
	case 'contactgroup':
		echo build_hidden_overlay('contactgroup','contactgroup_name',false,false);		//contactgroups  
		echo build_hidden_overlay('contact','contact_name',true,false); 					//contacts 		
	break; 	
	
	case 'contact':
	case 'contacttemplate':
		echo build_hidden_overlay('contactgroup','contactgroup_name',true,true);						//contactgroups 
		echo build_hidden_overlay('servicecommand','command_name',false,true,'selEventHandlers');//service commands 
		echo build_hidden_overlay('hostcommand','command_name',false,true,'selEventHandlers');	//host commands 
		echo build_hidden_overlay('contacttemplate','template_name',false,false);						//contact templates 
		echo build_variable_box();
	break; 
	
	case 'timeperiod':
		echo build_hidden_overlay('exclude','timeperiod_name',false,false);
	break; 
	
	case 'serviceescalation':
	case 'hostescalation':
	case 'servicedependency':
	case 'hostdependency': 
		echo build_hidden_overlay('host','host_name');   							//hosts
		echo build_hidden_overlay('hostgroup','hostgroup_name'); 				//hostgroups 
		if(strpos($this->exactType,'escalation')) {
			echo build_hidden_overlay('contact','contact_name',true); 					//contacts 
			echo build_hidden_overlay('contactgroup','contactgroup_name');		//contactgroups 
		}
		if($this->exactType =='serviceescalation' || $this->exactType=='servicedependency') 
			echo build_hidden_overlay('service','service_description'); 
		//create dependency boxes 	
		if($this->exactType =='hostdependency' || $this->exactType=='servicedependency') {
			echo build_hidden_overlay('hostdependency','host_name',false,false,'selHostDepOpts');
			echo build_hidden_overlay('hostgroupdependency','hostgroup_name',false,false,'selHostgroupDepOpts'); 
		}
		if($this->exactType=='servicedependency')
			echo build_hidden_overlay('servicedependency','service_description',false,false,'selServiceDepOpts');
	break; 
	
	default:
	break; 


}
 
//documentation overlay 
?>
<div id='documentation' class='overlay'></div>

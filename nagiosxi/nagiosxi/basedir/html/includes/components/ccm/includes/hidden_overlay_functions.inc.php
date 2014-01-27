<?php  //hidden_overlay_functions.inc.php

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
 
function build_hidden_overlay($type,$optionValue,$BA=false, $tplOpts=false,$fieldArray='')
{
	global $FIELDS; 
	global $unique;
	//echo "UNIQUE: $unique<br />";
	
	//echo "TYPE $type<br />"; 	
	$Title = ucfirst($type); 
	$Titles = ucfirst($type).'s'; 
	if($fieldArray=='') 
		$fieldArray = 'sel'.$Title.'Opts'; 
		
		$html = "<!-- ------------------------------------ {$Titles} --------------------- -->

<div class='overlay' id='{$type}Box'>

	<div class='left'>
		<div class='listDiv'>
      <label for='sel{$Titles}'>{$Titles}</label><br />
			<select name='sel{$Titles}[]' size='9' multiple='multiple' class='lists' id='sel{$Titles}'>
					<!-- option value is tbl ID -->
	
	"; 
	
	if($type=='hostservice') { //special case for hostService array //TODO: improve this 
		foreach($FIELDS['selHostServiceOpts'] as $key => $opt) { 					
			$html .= "<option ";
			if(in_array($key, $FIELDS['pre_hostservices_AB'])) $html .= "selected='selected' "; 
			if(in_array($key, $FIELDS['pre_hostservices_BA'])) $html .= "disabled='disabled' class='hiddenDependency' ";
			$html .= " id='".$unique++."' title='{$opt}' value='".$key."'>".$opt."</option>\n";
  		}
	}
	elseif($BA==true) {  //if there are two-way database relationships for this object  	
		foreach($FIELDS[$fieldArray] as $opt) { 					
			$html .= '<option ';
			if(in_array($opt['id'], $FIELDS['pre_'.$type.'s_AB'])) $html .= "selected='selected' ";
			if(in_array($opt['id'], $FIELDS['pre_'.$type.'s_BA'])) 
				$html .= "disabled='disabled' class='hiddenDependency' title='Object has a relationship established elsewhere' ";
			$html .= " id='".$unique++."' title='{$opt[$optionValue]}' value='".$opt['id']."'>".$opt[$optionValue].'</option>\n';
  		}
	}
	else { //only one-way DB relationships 	
		$pre_array = isset($FIELDS['pre_'.$type.'s_AB']) ? $FIELDS['pre_'.$type.'s_AB'] : $FIELDS['pre_'.$type.'s'] ; 
		//echo 'pre_'.$type.'s<br />';
		//ccm_array_dump($pre_array);
		//echo "<pre>PRESELECT \n".print_r($pre_array,true)."</pre>";
		//echo "<pre>FIELDS ARRAY \n".print_r($FIELDS[$fieldArray],true)."</pre>"; 
				
		foreach($FIELDS[$fieldArray] as $opt) {			
			$html.= '<option ';
			if(in_array($opt['id'], $pre_array)) $html .= "selected='selected' orderid='".array_search($opt['id'], $pre_array)."'"; 
			$html .= " id='".$unique++."' title='{$opt[$optionValue]}' value='".$opt['id']."'>".$opt[$optionValue].'</option>\n';	 			
		}
	}
	$html .="  </select>
				</div><!-- end listDiv -->
						<div class='ccm-label'>\"=>\" ".gettext("Denotes object relationship elsewhere")."</div>
	              <p><a href='javascript:void(0)' class='linkBox' onclick='transferMembers(\"sel{$Titles}\", \"tbl{$Titles}\", \"{$type}s\")' title='Add'>".gettext("Add Selected")."</a></p>
	                ";
	 if($tplOpts==true) { //template options 	 
		
		$radType = $type.'s';
		//deal with inconsistent DB naming convention in NagiosQL. Make sure we have the correct form field name
		$radType = (isset($FIELDS['contact_groups_tploptions']) && $type=='contactgroup') ? 'contact_groups' : $radType;
		$radType = (isset($FIELDS['host_name_tploptions']) && $type=='host') ? 'host_name' : $radType;
		$radType = (isset($FIELDS['hostgroup_name_tploptions']) && $type=='hostgroup') ? 'hostgroup_name' : $radType;
		$radType = ($type=='hostcommand') ? 'host_notification_commands': $radType;
		$radType = ($type=='servicecommand') ? 'service_notification_commands': $radType;
		
		//echo "RAD: $radType<br />\n";
		
		$html .= " 	             
	      <div class='tplOptions'>
	             <p class='ccm-label'>$Title ".gettext('Option')."s</p>
	             <input type='radio' name='rad{$Title}' id='rad{$Title}0' value='0' ". check($radType.'_tploptions', '0',true). " />	              
				      <label for='rad{$Title}0'> + </label><br />
				    <input type='radio' name='rad{$Title}' id='rad{$Title}1' value='1' ". check($radType.'_tploptions', '1',true). " />
				    	<label for='rad{$Title}1'> ".gettext('null')." </label><br />
				    <input type='radio' name='rad{$Title}' id='rad{$Title}2' value='2' ". check($radType.'_tploptions', '2',true). " />
				    	<label for='rad{$Title}1'> ".gettext('standard')." </label><br />        
	      </div> <!-- end tplOptions div -->  
		"; 
	}	 
    else $html.="<div class='tplOptions empty'></div>"; 
	     
   
	     
	$html .="   
	
			<div class='closeOverlay'>
				<a class='linkBox' style='width:50px;' href='javascript:killOverlay(\"{$type}Box\")' title='Close'>".gettext("Close")."</a>
			</div><!-- end closeOverlay -->             
	  </div><!-- end leftBox -->
	  <div class='right'>
	  					
	              <table class='outputTable' id='tbl{$Titles}'>
	               <tr>
	               	<th><div class='thMember'>".gettext("Assigned")."</div></th>
	               	<th><div class='thRemove'>
	               	<a title='Remove All' href='javascript:void(0)' onclick=\"removeAll('tbl{$Titles}')\">".gettext("Remove All")."</a></div>
	               	</th>
	               </tr>	
	              <!-- insert selected items here -->
	              </table>
	   </div>
	              	              
	       <!-- $type radio buttons -->  
	      	             				
	</div> <!-- end {$type}box --> ";  

	return $html; 
}


/** function build_command_output_box()
*	creates html overlay for the command-line test output
*	@return string html returns div html as an overlay 
*/
function build_command_output_box()
{  
	$html="
	
	<!-- command output -->
	<div class='overlay' id='commandOutputBox'>
	<h4>".gettext('Testing check from command line...')."</h4><br />
	<div id='command_output'>
		<img src='images/throbber1.gif' height='32' width='32' alt='' />
	</div>
	<br /><br />
	<p><a class='linkBox width100' href=\"javascript:killOverlay('commandOutputBox')\" title='Close'>".gettext("Close")."</a></p>
	</div>
	<!-- end command output overlay --> "; 
	
	return $html; 
}//end build_command_output_box() 

 
 
/** build_variable_box()
*	creates html overlay for the free-variable definition form 
*	@return string html returns div html as an overlay 
*/ 
function build_variable_box()
{
	$html="
	
<!-- ------------------------------------ free variables --------------------- -->
	 	 
<div class='overlay' id='variableBox'>             	
		<div class='left'>
          	<label for='txtVariablename'>".gettext("Variable name")." </label>
          	<img src='/nagiosql/images/tip.gif' class='helpImage' alt='Help' title='Help'  onclick=\"dialoginit('common','free_variables_name','all','Info')\"  /> 
             <br />
            <input type='text' name='txtVariablename' id='txtVariablename' style='width:225px' /><br />

             
             <label for='txtVariablename'>".gettext("Variable value")."</label> 
             <img src='/nagiosql/images/tip.gif' class='helpImage' alt='Help' title='Help'  onclick=\"dialoginit('common','free_variables_value','all','Info')\"  /> <br />
             <input type='text' name='txtVariablevalue' id='txtVariablevalue' style='width:225px' /><br />
             
           	<!-- write special function for variable defs --> 
             <div><a class='linkBox' href=\"javascript:void(0)\" onclick='insertDefinition(false,false)'>".gettext("Insert")."</a></div> 
           		<br />	
      </div><!-- end left div -->     		
		<div class='right'>        
		  <table class='outputTable' id='tblVariables'>
          <tr><th>".gettext("Variable Name")."</th><th>".gettext("Variable Definition")."</th><th>".gettext("Delete")."</th></tr>	
        <!-- insert selected items here -->
        </table>
		</div><!-- end right div -->  
					
		<div class='closeOverlay'>
				<a class='linkBox' href=\"javascript:killOverlay('variableBox')\" title='Close'>".gettext("Close")."</a>
			</div><!-- end closeOverlay -->	
           			
</div> <!-- end variableBox -->	

"; 

	return $html; 
}



?>

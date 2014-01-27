<?php  //config_forms.php   form functions for configuration editor for Nagios BPI

// Nagios BPI (Business Process Intelligence) 
// Copyright (c) 2010 Nagios Enterprises, LLC.
// Written by Mike Guthrie <mguthrie@nagios.com>
//
// LICENSE:
//
// This work is made available to you under the terms of Version 2 of
// the GNU General Public License. A copy of that license should have
// been provided with this software, but in any event can be obtained
// from http://www.fsf.org.
// 
// This work is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
// General Public License for more details.
// 
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
// 02110-1301 or visit their web page on the internet at
// http://www.fsf.org.
//
//
// CONTRIBUTION POLICY:
//
// (The following paragraph is not intended to limit the rights granted
// to you to modify and distribute this software under the terms of
// licenses that may apply to the software.)
//
// Contributions to this software are subject to your understanding and acceptance of
// the terms and conditions of the Nagios Contributor Agreement, which can be found 
// online at:
//
// http://www.nagios.com/legal/contributoragreement/
//
//
// DISCLAIMER:
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
// INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A 
// PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT 
// HOLDERS BE LIABLE FOR ANY CLAIM FOR DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
// OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE 
// GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, STRICT LIABILITY, TORT (INCLUDING 
// NEGLIGENCE OR OTHERWISE) OR OTHER ACTION, ARISING FROM, OUT OF OR IN CONNECTION 
// WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.



/**
*	builds the add/edit group form and preloads values depending on $cmd call 
*	if preloaded, expecting array from get_config_array() that has relevant details for the form 
*	@param mixed $array() [optional] data array for preloaded form
*	@return string $form html form, either empty or preloaded
*/ 
function build_form($array=array() ) 
{
	global $bpi_objects;
	//global $service_details;
	global $host_details;
		//begin heredoc string  
	
	//set default variables based on whether or not the form is preloaded 	
	$primary = ( (isset($array['primary']) && $array['primary'] == 1) || !isset($array['primary']) ) ? " checked='checked' " : '' ;
	$priority = grab_array_var($array,'priority',MEDIUM);
	$warn = grab_array_var($array,'warning_threshold', 90);
	$crit = grab_array_var($array,'critical_threshold',80);	
	$id = grab_request_var('arg',''); 
	$disabled = ($id == '') ? '' : "disabled='disabled'"; 
	$hidden = ($id == '') ? 'addSubmitted' : 'editSubmitted'; 
	$title = grab_array_var($array,'title','');
	$desc = grab_array_var($array,'desc',''); 
	$info = grab_array_var($array,'info',''); 
	$type = grab_array_var($array,'type','default'); 
	$auth_users =isset($array['auth_users']) ? explode(',',$array['auth_users']) : array() ; 
	
	
	//build html 
	$hiddenInput = ($id=='') ? '' : "<input type='hidden' value='{$id}' name='hiddenID' />";  
	//disabled form field?? 
	
	$action = ($id!='') ? "?cmd=edit&arg={$id}" : '?cmd=add'; 
	$form = '';				
	$form.= "
	<div id='helpBox'></div> <!-- end helpbox --> 
	<div id='container'>	
	<form id='outputform' method='post' action='{$_SERVER['PHP_SELF']}{$action}'/>
		
	  <div class='floatLeft'> 
		<label for='groupIdInput'>*".gettext("Group ID")."</label>
		<a href='javascript:infobox(\"groupid\");'>
			<img class='tooltip' src='images/tip.gif' width='15' height='15' alt='Info' title='Get More Info' />
		</a>
		<br />			
			<input id='groupIdInput' size='40' type='text' {$disabled} name='groupID' value='{$id}' />
			{$hiddenInput}
			<input type='hidden' name='groupType' id='groupType' value='{$type}' />
			<br />			
		<label for='groupTitleInput'>*".gettext("Display Name")."</label>
		<a href='javascript:infobox(\"displayname\");'>
			<img class='tooltip' src='images/tip.gif' width='15' height='15' alt='Info' title='Get More Info' />
		</a>		
		<br />
			<input id='groupTitleInput' size='40' type='text' name='groupTitle' value='{$title}' />
		<br />
		<label for='groupDescInput'>".gettext("Group Description")."</label><br />
			<input id='groupDescInput' size='40' type='text' name='groupDesc' value='{$desc}' /><br />
			
		<label for='groupInfoUrl'>".gettext("Info URL")."</label><br />
			<input id='groupInfoUrl' size='40' type='text' name='groupInfoUrl' value='{$info}' /><br /><br />			
		
		<input id='groupPrimaryInput' type='checkbox' name='groupPrimary' value='true' {$primary} />
		<label for='groupPrimaryInput'>".gettext("Primary Group")."</label>
		<a href='javascript:infobox(\"primary\");'>
			<img class='tooltip' src='images/tip.gif' width='15' height='15' alt='Info' title='Get More Info' /><br /><br />
		</a>		
		<div class='label'><label for=''><strong>".gettext("Health Thresholds")."</strong></label></div>
							
		<!-- WARNING THRESHOLD -->	
		<input type='text' id='groupWarn' name='groupWarn' value='{$warn}' size='1' />
		<label for='groupWarn'>".gettext("Warning")." (0-100)%</label>
		<a href='javascript:infobox(\"warning\");'>
			<img class='tooltip' src='images/tip.gif' width='15' height='15' alt='Info' title='Get More Info' />					
		</a><br />			
		<!-- CRITICAL THRESHOLD -->	
		<input type='text' id='groupCrit' name='groupCrit' value='{$crit}' size='1' />
		<label for='groupCrit'>".gettext("Critical")." (0-100)%. ".gettext("Must be lower than Warning Threshold.")."</label>
		<a href='javascript:infobox(\"critical\");'>
			 <img class='tooltip' src='images/tip.gif' width='15' height='15' alt='Info' title='Get More Info' /><br />				
		</a><br />						
		<label for='groupDisplayInput'>".gettext("Priority")."</label>
		<a href='javascript:infobox(\"priority\");'>
			<img class='tooltip' src='images/tip.gif' width='15' height='15' alt='Info' title='Get More Info' />
		</a><br />
			<select id='groupDisplayInput' name='groupDisplay'> "; 
	
	//switch for display priority 
	switch($priority)
	{

		
		case LOW:
		$form.="<option value='1'>".gettext("High")."</option>
				<option value='2'>".gettext("Medium")."</option>
				<option selected='selected' value='3'>".gettext("Low")."</option>";
		if($type=='hostgroup' || $type=='servicegroup') 
			$form .= "<option value='0'>".gettext("None")."</option>";		
		break;
		
		case HIGH:
		default:
		$form.="<option selected='selected' value='1'>".gettext("High")."</option>
				<option value='2'>".gettext("Medium")."</option>
				<option value='3'>".gettext("Low")."</option>";
		if($type=='hostgroup' || $type=='servicegroup') 
			$form .= "<option value='0'>".gettext("None")."</option>";				
		break;
		
		case 0:
		$form.="<option value='1'>".gettext("High")."</option>
			<option value='2'>".gettext("Medium")."</option>
			<option value='3'>".gettext("Low")."</option>
			<option value='0' selected='selected'>".gettext("None")."</option>";			
		break;
		
		case MEDIUM:
		default:
		$form.="<option value='1'>".gettext("High")."</option>
				<option selected='selected' value='2'>".gettext("Medium")."</option>
				<option value='3'>".gettext("Low")."</option>";
		if($type=='hostgroup' || $type=='servicegroup') 
			$form .= "<option value='0'>".gettext("None")."</option>";
		break;
		
	}

	//close priority select list 
	$form.= "</select><br /><br />";
	
	///////////////////////////Auth Users Select list////////////////////
	$form .="<label for='auth_users'>".gettext("Authorized Users")."</label>
			<a href='javascript:infobox(\"authusers\");'>
			<img class='tooltip' src='images/tip.gif' width='15' height='15' alt='Info' title='Get More Info' />					
			</a><br />
			<select multiple='multiple' size='4' name='auth_users[]' id='auth_users'>"; 
	$form .= create_user_options($auth_users);  
	$form .="</select><br />"; 					
	//////////////////////////////////Select list for all groups and services ///////////////////
	$form.= "<label for='multiple'>".gettext("Available Hosts")." (<strong>H:</strong>), 
															".gettext("Services")." (<strong>S:</strong>), 
														".gettext("and BPI Groups")." (<strong>G:</strong>)</label><br />
				<select id='multiple' multiple='multiple' size='10'>";

	$form .= create_option_list(); 

	///////////////////////////////////end select list ///////////////////////////
	$form.="</select><br />
				<div id='addMembers'>
					<a href='javascript:void(0)' onclick='dostuff()'>".gettext("Add Member(s)")." 
  					  <img width='13' height='8' alt='=>' title='Add Member(s)' src='images/children.png' />
					</a>
				</div>
				<div class='note'> * ".gettext("denotes required field")."</div>
			</div>
				<!-- end float left -->"; //end float left 
	
	//RIGHT FORM 
	//begin heredoc string 	
	$form.="
		
		<div class='floatRight'>
		<div id='writeConfig'><a href='javascript:void(0)' onclick='submitForm()'>".gettext("Write Configuration")."</a></div>
		<div id='memberWrapper'>
		<label for='selectoutput'>*".gettext("Group Members").":</label>
		<a href='javascript:infobox(\"groupmembers\");'>
			<img class='tooltip' src='images/tip.gif' width='15' height='15' alt='Info' title='Get More Info' />					
		</a>		

			<a id='clearMembersLink' onclick='clearMembers()' href='javascript:void(0)'>".gettext("Clear All")."</a>
		<br />		
		<table id='selectoutput'>
		<tr><th><div class='wide'>".gettext("Member Name")."</div></th>
			<th>".gettext("Essential")."<br /> ".gettext("Member")."</th>
			<th><div class='short'>".gettext("Remove")."</div></th></tr> 					
			<!-- insert javascript content here -->	
		</table>
		</div><!-- end memberWrapper div -->
		
		<input type='hidden' name='{$hidden}' value='true' />
		</div>
		</form>
	</div><!-- end container div -->
	";
	//end heredoc 
	
	//handle preloaded form values with JS 	
	if($id!='')
		build_preload_js($form,$id,$warn,$crit); 
		 
	return $form; 	

} //end function loaded_form() 



/**
*	creates list of hosts, services, and BPI groups
*	@return string $options an html option list for select form element 
*/ 
function create_option_list()
{
	global $host_details;
	global $bpi_objects; 

	$options = ''; 
	
	bpi_init('default',false); 
	
			//add groups to select list as options 
	foreach($bpi_objects as $object)
		$options.="<option value='$".$object->name."'>G: ".$object->title." (Group) </option>\n";
	 	
	//add host definitions with services 
	foreach($host_details as $host)
	{
		$options.= "<option value='{$host['host_name']};NULL'>H: {$host['host_name']}</option>\n";
		foreach($host as $item)
		{
			if(!is_array($item))  continue;
			$var = $item['host_name'].';'.$item['service_description'];
			$options.= "<option value='$var'>S: $var</option>\n";
		}
	}
				
	return $options; 

}



/*	
*	preloads form by adding javascript to load preselected group members
*	@param string $form REFERENCE variable to main $form string
*	@param string $id	group ID / global array index
*	@param int $warn warning threshold percentage ##NO LONGER USED
*	@param int $crit critical threshold percentage ##NO LONGER USED
*
*/ 
function build_preload_js(&$form,$id,$warn,$crit) 
{
	global $bpi_objects;
	$obj = $bpi_objects[$id];

	//add members upon page load through the javascript function 
	$members = $obj->get_memberlist(); //get group members array 
	// xxx Fixed bug where group members weren't repopulating in the form correctly with a group EDIT
	$members = array_merge($obj->get_group_children(),$members); 
	//print_r($members);
	$form.="\n<script type='text/javascript'>\n";
	//loop through members and print javascript for group or service 
	foreach($members as $member)
	{
		if($member['type'] == 'group')
		{
			//do group stuff
			$title = 'G: '.$member['title'];
			$value = '$'.$member['index'];
			$opt = $member['option'];
			$form.="\npreload('$title','$value', '$opt');\n";
			
		}
		if($member['type'] == 'service')
		{
			//do service stuff
			$title = 'S: '.$member['host_name'].';'.$member['service_description'];
			$value = $member['host_name'].';'.$member['service_description'];
			$opt = $member['option'];
			$form.="\npreload('$title','$value','$opt');\n";
			
		}
		if($member['type'] == 'host')
		{
			//do service stuff
			$title = 'H: '.$member['host_name'];
			$value = $member['host_name'].';NULL';
			$opt = $member['option'];
			$form.="\npreload('$title','$value','$opt');\n";
			
		}
	}
	//set the warning value via javascript 
//	$form.="\nsetThresholds('$warn', '$crit')\n;";
	//close script 
	$form.="\n</script>\n";
	

}

/**
*	fetches list of non-admin users and returs an html option list
*	@param mixed $selected array of selected users for preloaded form
*	return string $options html option list of non-admin users 
*/ 
function create_user_options($selected=array()) 
{
	//var_dump($selected); 
	
	$reg_users = get_regular_users(); 
//	print_r($reg_users); 
	$options = ''; 
	foreach($reg_users as $username => $fullname)
	{
		$options .="<option value='{$username}'"; 
		if(in_array($username,$selected)) $options .=" selected='selected'"; 
		$options .=">{$fullname}</option>\n";  
			
	}	
	return $options; 
		
}

?>
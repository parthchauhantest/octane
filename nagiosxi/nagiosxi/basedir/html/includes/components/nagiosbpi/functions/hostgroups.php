<?php //hostgroups.php   

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
*	fetches current list of hostgroups and either adds or updates BPI group accordingly
*	@return mixed array(int $errorcode, string $message)
*/ 
function build_bpi_hostgroups()
{
	global $bpi_objects; 
	$errors = 0; 
	$msg = ''; 
	$addString = ''; 
	$xml = get_xml_hostgroup_member_objects();

	foreach($xml->hostgroup as $hostgroup)
	{
		$key = "$hostgroup->hostgroup_name";
		$id = str_replace(' ','',$key); 
		$array = array( 'groupTitle' => 'HG: '.$key, 
					'groupID' => $id, //strip whitespace 
					'members' => array(),
					'groupDisplay' => 0,
					'groupPrimary' => 1,
					'groupType'	=> 'hostgroup',
					);

		foreach($hostgroup->members->host as $member) {   
			$name = strval($member->host_name); 
			if(empty($name)) continue; //an empty group will have a blank entry 
			$array['members'][] = "{$name};NULL"; //host only, service is null
		}	

		//echo $id."<br />"; 
		//foreach($bpi_groups

		//add or edit 
		if(!isset($bpi_objects[$id])) { 		           			   
			$addString .= process_post($array); 
		}
		else  { //group exists, use edit command 
		
			//add existing group properties back into array for data persistance 
			$array['critical'] = array(); //essential members array 
			$obj = $bpi_objects[$id];
			//handle existing members 
			$members = $obj->get_memberlist();	
			foreach($members as $member) {
				if($member['option']=='|') $array['critical'][] = $member['host_name'].';NULL';  
			
			}			
			//handle existing thresholds
			$array['groupWarn'] = $obj->warning_threshold; 
			$array['groupCrit'] = $obj->critical_threshold;
			//handle group description
			$array['groupDesc'] = $obj->desc; 
			//handle existing display priority 
			$array['groupPrimary'] = $obj->primary;
			//handle primary display group 
			$array['groupDisplay'] = $obj->priority;			
			//handle info URL  
			$array['groupInfoUrl'] = $obj->info;
			
			//pass array to processer 
			$editString = process_post($array); 
			//commit the changes 
			list($error,$errmsg) = edit_group($id,$editString);
			if($error > 0) 
				$msg.=$errmsg; 
			$errors+=$error;  
			//print "Group edited<br />";  
		}//end IF 					
	} //end hostgroups loop 

	list($error,$errmsg) = add_group($addString); 
	$errors+=$error; 
	$msg .=$errmsg; 
	
	return array($errors,$msg);  
}//end function 


/**
*	wrapper function for bpi hostgroup viewing in the browser
*	@return string $content html output generated by bpi_view_object_html('hostgroup')
*/
function bpi_view_hostgroups()
{
	$content = ''; 
	//enterprise stuff
	$content.=enterprise_message(); 
	
	//check auth 
	if(can_control_bpi_groups($_SESSION['username']) && enterprise_features_enabled())
		$content .= '<div class="syncmessage"><a href="index.php?cmd=synchostgroups" title="Sync Hostgroups">'.gettext('Sync Hostgroup').'s</a></div>'; 
		
	//explain why features are hidden	
	if(!enterprise_features_enabled()) {
		$content .= '<div class="syncmessage">'.gettext('BPI hostgroups feature is only available for Nagios XI Enterprise Edition').'</div>';
		return $content; 
	}	
		
	//get html		
	$content .= bpi_view_object_html('hostgroup');
	return $content; 
}



?>
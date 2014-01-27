<?php //process_post.php    processes data from config editor 

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



/*
*
*	@param mixed $array string['groupID']
*						string['groupDisplay'] groups UI display tab
*						array['members']  master array of members 
*						array['critical'] array of designated essential members 
*						string['groupDesc'] text description of a group
*						boolean['groupPrimary'] 0 | 1 is the group a primary display group?
*						string['groupInfoUrl'] hyperlink for group
*						int['groupWarn'] warning threshold for group
*						int['groupCrit'] critical threshold for group
*						string['groupType'] type (default | hostgroup | servicegroup | depedency | false ) 
*						array['auth_users'] list of auth users 
*						array['critical'] array of members that are essential members 
*
*						
*	
*/
function process_post($array)
{
	//global $objects;
	//clean post data 
	//verify that all required elements are set	
	//print "<pre>".print_r($array,true)."</pre>";	
	 
	if( (isset($array['groupID']) || isset($array['hiddenID'])) && isset($array['groupTitle'], $array['groupDisplay']))
	{
		//XXX TODO: change this to use grab_request_var and grab_array_var
		//input variables 	
        
        if (preg_match('/\s/', trim($array['groupID']))){
            
            print '<p class="error">'.gettext('Group Id cannot contain spaces.').'</p>';
            return false; 
            
        }
        
		$groupID = isset($array['groupID']) ? htmlentities(trim($array['groupID'])) : htmlentities(trim($array['hiddenID']));
		$title = htmlentities(trim($array['groupTitle']));
		$display = htmlentities(trim($array['groupDisplay']));
		$members = grab_array_var($array,'members',array());
		//optional config parameters 
		$desc = (isset($array['groupDesc']) ? htmlentities(trim($array['groupDesc'])) : '');
		$primary = (isset($array['groupPrimary']) ? 1 : 0);
		$critical = (isset($array['critical']) ? $array['critical'] : false);
		$info = ( isset($array['groupInfoUrl']) ? htmlspecialchars(trim($array['groupInfoUrl'])) : '');
		$warning = (isset($array['groupWarn']) ? htmlentities(trim($array['groupWarn'])) : '0');
		$crit = (isset($array['groupCrit']) ? htmlentities(trim($array['groupCrit'])) : '0');
		$type = (isset($array['groupType']) ? htmlentities($array['groupType']) : 'default'); 
		$auth_users = grab_array_var($array,'auth_users',array()); 
		
		$auth_user_string = empty($auth_users) ? '' : implode(',',$auth_users);  
			
		//echo "<p>Printing members list:</p>";
		//print_r($members);
		
		$memberString = '';
		if(!empty($members)) {
			if($critical) { //there are group ID's in the critical[] array 			
				foreach($members as $member) {
					if(empty($member)) continue; 
					
					if(in_array($member, $critical)) //if member is in critical array add the | symbol
						$memberString .= $member.';|, ';				 		
					else //else add the & symbol
						$memberString .= $member.';&, ';
				} //end foreach 

			} //end if 
			else { //all members are & members, assign & values  
				foreach($members as $member) {
					if(empty($member)) continue;
					$memberString .= $member.';&, ';
				}	
			}
		}
		//create config output, using heredoc string syntax  
		$config=<<<TEST

define {$groupID} {
		title={$title}
		desc={$desc}
		primary={$primary}
		info={$info}
		members={$memberString}
		warning_threshold={$warning}
		critical_threshold={$crit} 
		priority={$display}
		type={$type}
		auth_users={$auth_user_string}		
}
			
TEST;
//end heredoc 
		//echo "<pre>$config</pre>"; 
		//return configuration definition string 
		//print "<pre>New Config String:\n$config</pre>";
		return $config;
	}	
	else {
		print '<p class="error">'.gettext('Missing data from required fields. Please go back and complete all fields.').'</p>';
		//print "<pre>".print_r($array,true)."</pre>";
		return false; 	
	}
}



?>
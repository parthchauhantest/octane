<?php  //api_tool.php  function written to access BPI info from a nagios check.  Use with check_bpi.php 

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

//ini_set('display_errors','off'); 

// CLI Specifics
define('CLI',true); 
//if(!isset($_SESSION)) {
//	$_SESSION = array(); 
///	$_SESSION['user_id']=0; 
//} 	

if(!isset($_REQUEST)) $_REQUEST = array(); 
if(!isset($_GET)) $_GET = array(); 
if(!isset($_POST)) $_POST = array(); 

@require(dirname(__FILE__).'/inc.inc.php'); 

//preserve sanity before we do anything else 
if(isset($argv[1])) {
	$input = trim($argv[1]);	
	$args = parse_argv($argv); 
	
	//check for help flag
	if(trim($argv[1])=='-h') {
		display_api_help(); 
		exit(0); 
	}		
}	
elseif(isset($_GET['cmd'])) { //web test
	$input = $_GET['cmd']; 
	$args=$_GET; //api calls allows from web or CLI
}	
else {
	echo "Error: Not enough arguments to process command. Use -h to see available commands.\n";
	exit(3);
}


$ret = route_command($args); 
exit($ret); 




///////////////////////////////////////////////////
//	FUNCTIONS 
//////////////////////////////////////////////////
function display_api_help() {
	echo "Usage: ./api_tool --cmd=<command> [--group=<GroupID>] [--host=<host_name>] [--service=<service_descrption>] [--essential=<1|0>]
  Available commands:
    addmember: Removes a host or service from a specified BPI group 
    Usage: ./api_tool.php --cmd=addmember --group=<GroupID> --host=<host_name> [--service=<service_description>] [--essential=<1|0>]
		
    removemember: Removes a host or service from a specified BPI group
    Usage: ./api_tool.php --cmd=removemember --group=<GroupID> --host=<host_name> [--service=<service_description>]
		
    synchostgroups: Syncs current list of hostgroups to BPI hostgroups
    Usage ./api_tool.php --cmd=synchostgroups
		
    syncservicegroups: Syncs current list of servicegroups to BPI servicegroups
    Usage ./api_tool.php --cmd=syncservicegroups
\n"; 	
	exit(0); 
}



/**
*	processes input array from either GET or argv and handles executes appropriate command
*	@param mixed $args: array from either argv or $_GET
*	@return int $err: error code for success or failure
*/ 
function route_command($args) {
	
	$cmd = grab_array_var($args,'cmd',''); 
	$group = grab_array_var($args,'group','');
	$host = grab_array_var($args,'host',''); 
	$service = grab_array_var($args,'service','NULL'); 
	$essential = grab_array_var($args,'essential',0); 
		
	switch($cmd) {
		case 'addmember':
		case 'removemember':
			list($err,$msg) = modify_group_members($cmd,$group,$host,$service,$essential); 
		break;
				
		//case 'deletegroup': do we even mess with this right now???
		//break; 
		
		case 'synchostgroups':
			list($err,$msg) =  build_bpi_hostgroups(); 
		break;
		
		case 'syncservicegroups':
			list($err,$msg) = build_bpi_servicegroups();
		break; 
		
		default:
		return do_bpi_check(); 
	
	}
	
	echo "CMD: $cmd\n"; 
	echo "MSG: ".strip_tags($msg)."\n"; 
	return $err; 
}


function modify_group_members($mode,$group,$host,$service='NULL',$essential=0) {
	
	//echo "Doing {$mode}\n"; 
	//sanity check
	$arr = get_config_array($group); 
	if(empty($arr))
		return array(1,"Unable to find BPI group with ID: {$group}\n"); 
		
	//print_r($arr); 
	
	//refactor array as if it was posted from web form 
	$members = explode(',',$arr['members']); 	
	
	//reset the members array
	$arr['members'] = array();
	$critical = array(); 
	$chars = array(';&',';|'); 
	
	//refactor members array to spoof the form
	foreach($members as $m) {			
		$new = trim(str_replace($chars,'',$m)); 
		if(trim($m)=='') continue; //skip any ghosts
		if(strpos($m,'|')!==false)
			$critical[] = $new; 
		
		//echo "M:$new. =={$host};{$service}\n";
		
		//are we removing a member?	
		if($mode=='removemember' && trim($new)=="{$host};{$service}") {
			echo "Member: {$host};{$service} removed!\n";
			continue; //member will be removed from the list 
		}	
		//else add the member back in 	
		$arr['members'][] = $new; 		
	}
	
	//add the new member?
	if($mode=='addmember') {
		$memberstring = "{$host};{$service}";
		$arr['members'][] = $memberstring;
		if($essential==1)	
			$critical[] = $memberstring; 
		echo "Member: {$host};{$service} added!\n"; 	
	}		
		
	//same steps for all group modification functions, just refactoring the array a bit  		
	$users = grab_array_var($arr,'auth_users',''); 
	//refactor auth users from string to array
	$arr['auth_users'] = explode(',',$users); 
	//reference the other values for the form array. Not going to break the UI form at this time with alterations
	$arr['groupTitle'] = &$arr['title'];
	$arr['groupDesc'] = &$arr['desc'];	
	$arr['groupPrimary'] = &$arr['primary'];
	$arr['groupInfoUrl'] = &$arr['info'];
	$arr['groupWarn'] = &$arr['warning_threshold'];
	$arr['groupCrit'] = &$arr['critical_threshold'];
	$arr['groupType'] = &$arr['type'];
	$arr['critical'] = $critical; 
	$arr['groupDisplay']=&$arr['priority']; 

	//print_r($arr); 
	
	//generate config string to save to file
	$config_string = process_post($arr);  
	//echo $config_string."\n";
	if($config_string) {
		list($err,$msg) = edit_group($group, $config_string);
		return array($err,strip_tags($msg));
	}	
	else
		return array(1,"Failed to process configuration array. Missing required values\n"); 
	
}



function do_bpi_check() {
	global $bpi_options; 
	global $input; 

	//add optional argument for XML freshness theshold 
	$now = time(); 
	clearstatcache(); 

	//echo "TIME: ".($now - filemtime($bpi_options['XMLFILE']))."\n"; 
	//echo "THRESHOLD: ".$bpi_options['XMLTHRESHOLD']."\n";  

	if( file_exists($bpi_options['XMLFILE']) && 
		($now - filemtime($bpi_options['XMLFILE']) < $bpi_options['XMLTHRESHOLD'] && filemtime($bpi_options['XMLFILE'])!=false) ) 
	{
		$XML = simplexml_load_file($bpi_options['XMLFILE']); 
		if(!$XML) 
			return bpi_fresh_results();  
		else {		
			foreach($XML->group as $group){
				if("$group->name" == $input) {
					$state = intval("$group->current_state");
					$status_text = "$group->status_text";
					$perfdata = "Health={$group->health}%"; 
					print "XML: ".return_state($state)." : ".$status_text.' | '.$perfdata."\n"; 
					return $state; 
				}		
			}
		}
		//if we haven't found anything, get fresh results 
		return bpi_fresh_results(); 

	}
	else //run fresh results 
		return bpi_fresh_results();	
	
}	
	
/////////////////////END MAIN /////////////////////////


function bpi_fresh_results()
{
	global $input; 
	global $bpi_objects; 
	
	//crunch group states
	bpi_init(); 
	//dump new xml to cache file 
	xml_dump(); 	
				
	if(isset($bpi_objects[$input]))
	{
		$obj = $bpi_objects[$input];
		list($state,$status_text,$perfdata) = $obj->return_state_details();
		//echo $output['msg'];
		// Exit correctly
		
		print $status_text.' | '.$perfdata."\n"; 
	
//	//	fwrite(STDOUT, trim($output['msg'])."\n");
		//echo $output['code']."\n";
		return intval($state);
	}
	else
	{
		echo "Unknown BPI Group Index\n";
		return 3;
	}


}


?>
<?php  //bpi_page_router.php 


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
*	main page routing NOTE: there is a second page routing function on bpi_display.php that routes the ajax based content
*	@param string $cmd a command option so this function can be specified from the command-line API
*	@global $bpi_options
*	@return string $content pre-built html output to pass to the browser 
*/ 
function bpi_page_router($cmd=false) 
{
	global $bpi_options;
	
	//processes $_GET and $_POST data 
	if(!$cmd) $cmd = grab_request_var('cmd',false); //can be overridden from CLI check 
	$tab = grab_request_var('tab','high'); 
	$msg = grab_request_var('msg',''); 	
	$valid_tabs = array('low','medium','high','hostgroups','servicegroups','default','all','add'); 
	
	//page content string 
	$content = ''; 
	$content .= unserialize($msg); //return message from any previous actions 
	
	/////////////////////COMMANDS//////////////////////////
	if($cmd)
	{	
		$errors = 0;
		//path home 
		//$content .= "<div class='gohome'><a href='index.php'>BPI Home</a></div>";			
		
		//auth check 
		if(CLI==false && !can_control_bpi_groups()) 
			return "<div class='error'>You are not authorized to access this feature.</div>"; 
		
		//echo "TAB: $tab INIT ALL GROUPS"; 
		//$init_msg = bpi_init('all'); //initialize all groups 
				
		switch($cmd)
		{
			//delete
			case 'delete': 				//handle group deletion
				//do stuff
				list($errors,$msg) = handle_delete_command($content); 
			break; 
			case 'edit':
				$init_msg = bpi_init('all',false); //initialize all groups 
				list($errors,$msg) = handle_edit_command($content); 
			break; 
			case 'add':
				$init_msg = bpi_init('all',false); //initialize all groups but don't determine states
				list($errors, $msg) = handle_add_command($content);				
			break; 
			case 'fixconfig':
				$init_msg = bpi_init('all',false); //initialize all groups but don't determine states
				$content.=$init_msg; //display debugging info in browser 
				list($errors,$msg) = handle_fixconfig_command($content); //UPDATE to new error handling and content return 
			break;
			
			case 'synchostgroups':
				//explain why features are hidden	
				if(!enterprise_features_enabled()){ 
					$msg = gettext('Hostgroup syncing is only available for Nagios XI Enterprise Edition');
					$errors++; 
				}	
				else {
					//sync hostgroups function
					$init_msg = bpi_init('all',false); //initialize all groups but don't determine states
					list($errors,$msg) = build_bpi_hostgroups(); 
				}
			break; 
			case 'syncservicegroups':
			if(!enterprise_features_enabled()) {
				$msg = gettext('Servicegroup syncing is only available for Nagios XI Enterprise Edition');
				$errors++; 
			}	
			else {	
				$init_msg = bpi_init('all',false); //initialize all groups but don't determine states
				list($errors,$msg) = build_bpi_servicegroups(); 
			}	
			break;

			case 'checkgroupstatus':
				//do a sanity check and make sure it's a subsystem call, else return error 
				if(CLI==false) die('Illegal action!'); 
				
				//do something that prints status text to STDOUT and exits with an error message 
				//check file age
					//if file age is ok, use XML data file 
				//else file is too old
					//crunch new data
					//write to file
					//print fresh check result 	
				
			break; 
			
			default:
			return "unknown command";
		
		}
		
		//generic error handler 		
		if($errors > 0) 
			$content .= "<div class='error'>".gettext("ERROR").": $msg</div>";
			
		//display a generic success message for a config change?	
		if($errors==0 && 
			(	isset($_REQUEST['addSubmitted']) || 
				isset($_REQUEST['editSubmitted']) || 
				isset($_REQUEST['configeditor']) || 
				$cmd=='delete'	|| 
				$cmd=='synchostgroups' || 
				$cmd=='syncservicegroups' 
			) )	
			$content .=	"<div class='success'>$msg</div>";	
			
		return $content; 
	}	
			
	/////////////////VIEWs for TAB Routing/////////////// ->See bpi_display for routing tabs 
	if(in_array($tab,$valid_tabs))
	{	
		$_SESSION['tab'] = $tab; 
		$content .= "
		<div id='notes' class='note'>".gettext("Essential group members are denoted with").": **"; 
		if($bpi_options['IGNORE_PROBLEMS']==true) 
			$content .= "<br />".gettext("Handled problems are denoted with").": <img src='images/enable_small2.png' height='10' width='10' alt='' />";  		
		
		$content .="<br /></div>
		<script type='text/javascript'>
			$(document).ready(function() {
				bpi_load();	
			}); 
		</script>
		<div id='bpiContent'><img src='images/throbber1.gif' height='32' width='32' alt='' /></div>"; 
	}
	/////////////////////////ERROR/////////////////////////////////	
	else //no get variables, page defaults to 'view' mode 
		echo "ERROR: INVALID TAB!<br />"; 		
	
	return $content; 
	
}//end bpi_page_router()  



?>
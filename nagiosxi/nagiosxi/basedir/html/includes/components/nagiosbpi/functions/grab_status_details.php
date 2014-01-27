<?php //grab_status_details.php 

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
*	fetches host/service status details from XI backend
*	@global string $bpi_errors global error messages 
*	@return mixed array($hostdetails,$servicedetails) 
*
*/
function grab_status_details()
{
	global $bpi_errors; 
	$max = 128; //max character length for plugin output
	
	///////////////get host status
	$backendargs["cmd"]="gethoststatus";
	$backendargs["brevity"]=1;
	$xml=get_xml_host_status($backendargs);
	$hosts = array(); 
	//turn XML object into array used by BPI 
	if(!$xml)
	{
		$bpi_errors.= "Error retrieving host status XML!<br />"; 
		return; 
	}
	foreach($xml->hoststatus as $x)
	{
		$plugin_output = (strlen("$x->status_text") > $max) ? substr("$x->status_text",0,$max) : "$x->status_text";  
		$hosts["$x->name"] = array('host_name' => "$x->name",
						'current_state' => "$x->current_state",
						'last_check' => "$x->last_check",
						'has_been_checked' => "$x->has_been_checked",
						'plugin_output' => $plugin_output,
						'problem_has_been_acknowledged' => "$x->problem_acknowledged",
						'scheduled_downtime_depth' => "$x->scheduled_downtime_depth", 
						); 
	
	}
	unset($xml); 
											
	///////////get service status 
	$backendargs = array("cmd" 			=> "getservicestatus",
					//	"combinedhost" 	=> 1,	//not sure if I need this yet 
						"brevity" 		=>1,
						); 
	$xml=get_xml_service_status($backendargs);	
	//$services = array(); 	
	if(!$xml)
	{	
		$bpi_errors.= "Error retrieving service status XML!<br />"; 
		return; 
	}
	else
	{
		foreach($xml->servicestatus as $x)
		{
			$plugin_output = (strlen("$x->status_text") > $max) ? substr(0,$max,strlen("$x->status_text")) : "$x->status_text"; 
			$hosts["$x->host_name"]["$x->name"] = array('host_name' => "$x->host_name",
					'service_description' => "$x->name",
					'current_state' => "$x->current_state",
					'last_check' => "$x->last_check",
					'has_been_checked' => "$x->has_been_checked",
					'plugin_output' => $plugin_output,
					'problem_has_been_acknowledged' => "$x->problem_acknowledged",
					'scheduled_downtime_depth' => "$x->scheduled_downtime_depth", 		
					); 
			
		}
		unset($xml);
	}
	//return array($hosts,$services); 
	return $hosts;
	

}



?>
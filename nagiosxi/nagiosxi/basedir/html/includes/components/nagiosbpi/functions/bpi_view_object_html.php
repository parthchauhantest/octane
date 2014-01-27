<?php //bpi_view.php 


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
*	main display function for all bpi group trees, filters group's processed by the $arg param
*	@param string $arg group type to be filtered (high, medium, low, hostgroup, servicegroup
*	@return string $content processed html content based on $arg  	
*/
function bpi_view_object_html($arg)
{
	global $bpi_objects;
	global $bpi_unique;
	$tds = @unserialize(grab_request_var('tds')); 
	$divs = @unserialize(grab_request_var('divs')); 
	$sorts = @unserialize(grab_request_var('sorts'));  
	$content =''; 
	
	//create javascript for reload 
	$content .=	"<script type='text/javascript'>
		$(document).ready(function() { "; 
	//toggled groups 
	if(is_array($divs)) {
		for($i=0;$i<count($divs);$i++)
			$content .= "reShowHide('{$divs[$i]}','{$tds[$i]}');\n"; 	
	}	
	//sorted groups 
	if(is_array($sorts)) {
		foreach($sorts as $s)
			$content .="sortchildren('{$s}',true);"; 
	}		
	//close JS 				
	$content .=" }); \n\n </script>"; 
	
	$resultCount = 0; 
	
	foreach($bpi_objects as $object)
	{
		//determine info for html display 
		//if($arg && 
		if($object->get_primary() > 0 && ($object->priority==$arg || $object->type==$arg) ) //removed  
		{
			if(!is_authorized_for_bpi_group($object,$_SESSION['username'])) continue; //auth filtering 
		
		
			$state = return_state($object->state);
			if($object->has_group_children==true)
				$gpc_icon = "<th><img src='images/children.png' title='Contains Child Groups' height='8' width='13' alt='C' /></th>"; 
			else $gpc_icon = ''; 	
			
			$td_id = 'td'.$bpi_unique;
			$info_th=$object->get_info_html();
			$desc_td = (trim($object->desc) == '') ? '' : "<td>{$object->desc}</td>"; //object description 
			//display for only primary groups.  See the $object->display_tree() for subgroup displays 		
			//build string 			
			$content.="
			 <table class='primary'>
				<tr>
					<td class='{$state}'><div class='fixedwidth'>{$state}</div></th>
					<td class='group' >
				<a id='{$td_id}' href='javascript:void(0)' title='Group ID: {$object->name}' 
					onclick='showHide(\"{$object->name}\",\"$td_id\")' class='grouphide'>".$object->get_title()."</a>
					</td>
					<td>															 
						<a class='sortlink' title='Sort By Priority' href=\"javascript:sortchildren('{$object->name}',false);\">
						 	<img class='sorter' src='images/sort1.png' height='16' width='16' alt='&nbsp;' /> 
						</a>
									
					</td>
					{$gpc_icon}
					{$info_th}
					<td>{$object->status_text}</td>							
					{$desc_td}\n"; 
					
			//for auth_users with full permissions		
			if(can_control_bpi_groups($_SESSION['username'])) 
				$content.="		
					<td><a href='index.php?cmd=edit&arg={$object->name}'>Edit</a></td>
					<td><a href=\"javascript:deleteGroup('index.php?cmd=delete&arg={$object->name}')\">Delete</a></td>"; 
			
			//close table 
			$content.="		
				</tr>
			</table>"; 
					
			//print $table;
			//end heredoc string 
			//recursively display groups 
			$content .="<div class='hidden toplevel' id='{$object->name}'>";	
				
			$object->display_tree($content);
			$content .="</div>\n\n";		
			$bpi_unique++;
			$resultCount++; 	
		}
		//else $content .="<p class='error'>No matching groups for this filter PR:{$object->priority} TYPE:{$object->type} </p>"; 
	}
	$content .=""; 
	
	if($resultCount==0) $content .="<div class='message'>".gettext("No BPI Group results for this filter.")."</div>"; 
	
	
	return $content; 
}

?>
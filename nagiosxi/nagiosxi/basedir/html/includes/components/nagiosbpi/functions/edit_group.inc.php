<?php //edit_group.php

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
*	take in group ID from $arg variable, retrieve object data and plug data into existing form 
*	@param string $arg the group ID
*	@param string $replacement the rebuilt configuration string to replace the old one
*	@return mixed array(int $errorcode, string $message)
*/ 
 
function edit_group($arg, $replacement)
{
	//pass groupName as arg and get it's config 
	$errors = 0; 
	$msg = ''; 
	$original = get_config_string($arg);
	if($original) {
		//print "<pre>Original: $original</pre>";
		//backup the config file first 
		if(copy(BPI_CONFIGFILE, BPI_CONFIGBACKUP)) {
			//print "<p>Backup successfully created.</p>";
			$contents = file_get_contents(BPI_CONFIGFILE);
			//replace old config string with new 			
			$new = str_replace($original, $replacement, $contents, $count0);
			
			//print "<p>Group configs replaced: $count0</p>"; 
			//print "<pre>$new</pre>";
			if($new && $count0 > 0) {
				file_put_contents(BPI_CONFIGFILE, $new);
				$msg =  gettext("File successfully written!");
			}
			else {
				$msg = "<span class='error'>".gettext("Unable to match string in config file.")."</span><br />";
				$errors++; 
			}	
		}
		else {
			$msg =  "<span class='error'>".gettext("Backup failed.  Aborting change.  
						Verify that backup directory is writeable.")."</span><br />";
			$errors++; 
		}
	}
	else {
		$msg =  "<span class='error'>".gettext("Unable to find group in config file, no changes made.")."</span><br />";
		$errors++; 
	}		
		
	return array($errors,$msg); 	
}//end function edit_group();


?>
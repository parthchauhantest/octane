<?php //fix_config.php  function called to debug bpi.conf configuration file 

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
*	manages the configuration editor from the browser
*	@param string $content REFERENCE variable to main content string
*	@return mixed array(int $errors, string $msg) 
*/ 
function fix_config(&$content)
{
	$errors = 0;
	$msg = ''; 

	if(isset($_POST['newconfig']) )
	{
		$newconfig = grab_request_var('newconfig');
		list($errors,$msg) = fix_config_file($newconfig);
	}
	else
	{

			$content .= "
			<h3>".gettext("BPI Configuration Editor")."</h3>
			<p class='error'>".gettext("Warning: Do NOT make changes to this file unless you know what you're doing!")."<br /><br />
			</p><br />";
			$content .= config_editor();

	}

	return array($errors,$msg); 
}


/**
*	this function pulls up the contents of the config file and inserts them into a text editor 
*	@return string $output html output string 
*/
function config_editor()
{
	$contents = file_get_contents(BPI_CONFIGFILE);
	$output =  "<div id='configEditor'><form id='configedit' action='index.php?cmd=fixconfig' method='post'>\n
				<textarea name='newconfig' id='newconfig1' rows='30' cols='120'>$contents</textarea><br />
				<input type='submit' name='submit' value='Save' />
				<input type='hidden' name='configeditor' value='true' />
			</form></div>";
	return $output;		
}


/**
*	expecting replacement contents for configuration file, writes a new config file   
*	@param string $new the new configuration string for the bpi.conf file 
*	@return mixed array(int $errorcode, string $message) 
*/
function fix_config_file($new)
{
	$errors = 0;
	$msg = ''; 
	
	//backup the config file first 
	if(copy(BPI_CONFIGFILE, BPI_CONFIGBACKUP))
	{
		$msg.= "Backup successfully created.<br />";
		//print "<pre>$new</pre>";  
		if(file_put_contents(BPI_CONFIGFILE, $new))
			$msg.= "File successfully written!<br />";
		else {
			$errors++;
			$msg.= BPI_CONFIGFILE." ".gettext("failed to save successfully")."<br />"; 
		}	

	}
	else
	{
		$errors++; 
		$msg .= gettext( "Backup failed.  Aborting change. Verify that backup file and directory is writeable."); 
	}
	return array($errors,$msg); 

}//end function fix_config_file();





?>
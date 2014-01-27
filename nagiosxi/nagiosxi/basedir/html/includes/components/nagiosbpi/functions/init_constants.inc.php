<?php //constants.inc.php  file for nagios bpi addon 

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


//hardcoded constants 
define('HIGH',1); 
define('MEDIUM',2); 
define('LOW',3); 

/**
*	initializes constants based on the global config options
*	@TODO eventually changes this to a global config array only.  Shouldn't be constants
*/ 
function init_constants()
{
	global $bpi_options; 
	//grab config info from XI
	$bpi_options = bpi_fetch_options();  //where is this function???
	
	//
	define('BPI_BASE', str_replace('/functions','',dirname(__FILE__)) ); //assigns current directory as root 
	define('BPI_VERSION','2.31'); 

	//only used for web front-end 
	define('SERVERBASE', get_base_url());  //@TODO add handling for subsystem runs 

	//assign constants if they've been set correctly 
	define('BPI_CONFIGBACKUP', $bpi_options['CONFIGBACKUP']);
	define('BPI_CONFIGFILE', $bpi_options['CONFIGFILE']);
	define('BPI_LOGFILE',$bpi_options['LOGFILE']); 
	define('BPI_XMLFILE',$bpi_options['XMLFILE']); 
		
	//xml option @TODO - deal with this later 
	//if($bpi_options['XMLOUTPUT']!=false) 
	//	define('XMLOUTPUT', $bpi_options['XMLOUTPUT']);  

		
	if(file_exists(dirname(__FILE__).'/../../componenthelper.inc.php')) //if installation is Nagios XI 
	{
		//nagiosxi 
		//define('NAGIOSURL', SERVERBASE.'/nagiosxi/');
		define('NAGIOSURL', SERVERBASE);
		define('HOSTDETAIL', NAGIOSURL.'/includes/components/xicore/status.php?show=hostdetail&host=');
		define('SERVICEDETAIL', NAGIOSURL.'/includes/components/xicore/status.php?show=servicedetail&host=');
		define('NAGV', 'XI'); 
	}
}







?>
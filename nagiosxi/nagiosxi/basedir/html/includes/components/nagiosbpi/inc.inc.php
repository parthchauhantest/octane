<?php  //inc.inc.php  master include file for Nagios BPI

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


///////////////////////Function includes////////////////////////// 
include(dirname(__FILE__).'/functions/parse_bpi_conf.inc.php');
include(dirname(__FILE__).'/functions/bpi_functions.php');
include(dirname(__FILE__).'/functions/bpi_init.php');
include(dirname(__FILE__).'/functions/bpi_view_object_html.php');
include(dirname(__FILE__).'/functions/bpi_page_router.php');
include(dirname(__FILE__).'/functions/grab_status_details.php');
include(dirname(__FILE__).'/functions/hostgroups.php');
include(dirname(__FILE__).'/functions/servicegroups.inc.php');
include(dirname(__FILE__).'/functions/init_constants.inc.php');
include(dirname(__FILE__).'/functions/get_config_string.inc.php');
include(dirname(__FILE__).'/functions/get_config_array.inc.php');
//include(dirname(__FILE__).'/functions/read_service_status.php'); //will be used for CE version 
include(dirname(__FILE__).'/functions/process_post.inc.php');
include(dirname(__FILE__).'/functions/bpi_commands.php');
include(dirname(__FILE__).'/functions/add_group.inc.php');
include(dirname(__FILE__).'/functions/edit_group.inc.php');
include(dirname(__FILE__).'/functions/delete_group.inc.php');
include(dirname(__FILE__).'/functions/build_form.inc.php');
include(dirname(__FILE__).'/functions/fix_config.inc.php');
include(dirname(__FILE__).'/classes/BpGroup_class.php');


//XI Specific stuff
require_once(dirname(__FILE__).'/../../common.inc.php');

// initialization stuff
pre_init();

if(CLI==false) 
{
	// start session
	init_session();
	// grab GET or POST variables 
	grab_request_vars();
	// check prereqs
	check_prereqs();
	// check authentication
	check_authentication(false);
}
else {
	define('SUBSYSTEM',1);
	db_connect_all(); 

}

//BPI
$bpi_options = array();  
init_constants(); //constants.inc.php   


//global bpi variables  
$bpi_config = true;
$bpi_errors = '';
$bpi_objects = array(); //main array of bpi group objects 
$bpi_config = true;
$bpi_errors = '';
$bpi_obj_count = 0;
$bpi_state_count = 0;
$bpi_unique = 0;  
//$service_details = array(); 
$host_details = array(); 




?>
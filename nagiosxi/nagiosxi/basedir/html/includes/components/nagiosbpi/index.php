<?php 	ob_start(); 

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

//ini_set('display_errors','on'); 

//BPI Specific stuff 
define('CLI',false); 
require_once('inc.inc.php');  //master include file for all functions and classes, session stuff, and global variables 

?>
<!DOCTYPE html>
<html>
<head>
<title>Nagios BPI</title>
<meta http-equiv="X-UA-Compatible" content="IE=Edge" />
<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
<meta http-equiv="content-style-type" content="text/css"/>
<link rel="stylesheet" href="bpi_style.css?<?php echo get_product_release(); ?>" type="text/css" media="screen" />

<?php		do_page_head_links();  ?> 

<script type="text/javascript" src="bpi.js"></script>
<script type="text/javascript">

<?php
	//dynamic stuff here 
$tab = grab_request_var('tab',false); 
$cmd = grab_request_var('cmd',false); 
if($cmd=='add') echo "var tab='tabcreate';"; 
elseif($tab) echo "var tab='tab{$tab}';"; 
else echo "var tab=false;"; 

echo "MULTIPLIER = ".$bpi_options['MULTIPLIER'].';';  
?>
</script>
</head>
<body>

<h2 class='mainheader'><?php echo gettext("Nagios Business Process Intelligence"); ?></h2>

<div id='categoryDiv'><?php echo gettext("Business Process Categories"); ?></div>
<div id='tabs'>	
  <ul id='bpiTabs'><!-- removed ui-helper-reset ui-helper-clearfix ui-widget-header -->
	<li class='bpiTab'><a class="tab" id='tabhigh' href='index.php?tab=high' title="View High Priority Processes"> <?php echo gettext("High Priority"); ?> </a></li>
	<li class='bpiTab'><a class="tab" id='tabmedium' href='index.php?tab=medium' title="View Medium Priority Processes"> <?php echo gettext("Medium Priority"); ?> </a></li>
	<li class='bpiTab'><a class="tab" id='tablow' href='index.php?tab=low' title="View Low Priority Processes"> <?php echo gettext("Low Priority"); ?> </a></li>	
	<li class='bpiTab'><a class="tab" id='tabhostgroups' href='index.php?tab=hostgroups' title="View Hostgroups as BPI Groups"> <?php echo gettext("Hostgroups"); ?> </a></li>
	<li class='bpiTab'><a class="tab" id='tabservicegroups' href='index.php?tab=servicegroups' title="View Servicegroups as BPI Groups"> <?php echo gettext("Servicegroups"); ?> </a></li>

<?php 
	if(can_control_bpi_groups($_SESSION['username'])) 
		print "<li class='bpiTab'><a class='tab' id='tabcreate' href='index.php?cmd=add&tab=add' title='Create New Business Process'> ".gettext('Create New BPI Group')." </a></li>"; 

?>
  </ul>
</div>
<div id='lastUpdate'></div> <!-- jquery updated -->
<div id='addgrouplink'>		
	<p class='note'>Nagios BPI v<?php echo BPI_VERSION; ?>
		 <br />Nagios Enterprises, LLC<br />
		 <a href="http://assets.nagios.com/downloads/nagiosxi/docs/Using_Nagios_BPI_v2.pdf" target='_blank' title="BPI Documentation"><?php echo gettext("BPI Documentation"); ?></a><br />
<?php if(is_admin()) { ?>
		 <a href="index.php?cmd=fixconfig" title="Manually Edit Config">Manually Edit Config</a>
<?php } ?>		 
	</p>
  </div>
	
<?php 

//check for correct permissions and file status 
error_check(); 

//handle any page requests and redirection
//see bpi_functions.php for function details 
print bpi_page_router();
	   

//close html page 
?>
</body>
</html>

<?php 
ob_end_flush();
?>
<?php //bpi_functions.php  misc functions for Nagios BPI 

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
*	function to trim spaces from array using array_walk()
*/ 
function trim_value(&$value) 
{ 
    $value = trim($value); 
}


/** 
*	wrapper function for host/service state code -> string
*	@param int $arg expecting int 0-3
* 	@param string $type host | service 
*	@return int $state host/service state code: UP, DOWN, UNREACHABLE  | service state code: OK, WARNING, CRITICAL, UNKNOWN 
*/ 
function return_state($arg,$type='')
{
	if($type=='host')
	{
		switch($arg)
		{
			case 0:  	return "Up";			
			case 1: 	return "Down";					
			default:	return "Unreachable";			
		}//end switch 
	}
	else 
	{
		switch($arg)
		{
			case 0:	return "Ok";			
			case 1:	return "Warning";			
			case 2:	return "Critical";			
			case 3:	return "Unknown";			
			default: return "Unknown";			
		}//end switch 
	}
}//end method return_service_state() 


/**
*	redirects user to home page 
*/ 
function send_home() //redirects user to index page 
{
	header('Location: '.BASEURL);
}


/**
*	sanity check for basic BPI inititalization
*	@return boolean true | false 
*/ 
function error_check()
{
	global $bpi_errors;
	global $bpi_config; 
	
	/*
	if(is_null(get_option('bpi_configfile') ) )
	{
		echo "<span class='error'>Nagios BPI has not been configured yet.  
								Please set initial configuration options in the Admin->Manage Components page, 
								and select the 'Edit Settings' icon next to the Nagios BPI component listing<br /></span>"; 
		return; 						 
	}
	*/
	
	if(!check_files()) 
	{
		echo $bpi_errors;
		return; 
	} 

	//handler for bad configurations 
	//make this form disappear once fixconfig is accessed 
	if($bpi_config!=true || $bpi_errors != '')
	{
		//print_r($bpi_errors);
		//allow for manual editing of the configuration file, and send error messages to that page 

        if(isset($_POST['errors']) || isset($_POST['configeditor']) || isset($_GET['cmd']) ) return;  //do nothing
        else
		{	//submit error messages as posts 
			print "<p class='error'>".gettext("WARNING: Errors in configuration file.")."</p>
					<form id='errorlog' method='post' action='index.php?cmd=fixconfig'>
					   <input type='submit' value='".gettext("Edit Configuration File")."' name='submit' />
					   <input type='hidden' name='errors' value=\"$bpi_errors\" />
					</form>"; 
		}
		
	}
}

/**
*	verifies that necessary files exists and are writeable 
*	@return boolean true | false 
*/
function check_files()
{
	global $bpi_errors; 

	//check if bpi.conf file location is already specified and if it exists yet.  If not create it and specify it with set_options 	
	if(!file_exists(BPI_CONFIGFILE)) {
		//create it
		$default = file_get_contents('bpi.conf'); 
		if(!file_put_contents(BPI_CONFIGFILE,$default)) {
			$bpi_errors .=gettext("Unable to write to BPI Configuration file").": ".BPI_CONFIGFILE.". ".gettext("Check file permissions.")."<br />";  
			return false; 
		}	
	}
	//make sure it's writable, bail if it's not  
	if(!is_writable(BPI_CONFIGFILE)) {
		$bpi_errors .=gettext("Unable to write to BPI Configuration file").": ".BPI_CONFIGFILE.". ".gettext("Check file permissions.")."<br />";  
		return false; 
	}
	////////////////backup file 
	if(!file_exists(BPI_CONFIGBACKUP)) {
		//create it
		$default = file_get_contents('bpi.conf'); 
		if(!file_put_contents(BPI_CONFIGBACKUP,$default))
		{
			$bpi_errors .=gettext("Unable to write to BPI Configuration backup file: ").BPI_CONFIGBACKUP.". ".gettext("Check file permissions.")."<br />";  
			return false; 
		}	
	}
	//make sure it's writable, bail if it's not  
	if(!is_writable(BPI_CONFIGBACKUP)) 
	{
		$bpi_errors .=gettext("Unable to write to BPI Configuration backup file: ").BPI_CONFIGBACKUP.". ".gettext("Check file permissions").".<br />";  
		return false; 
	}
	
	
	////////////////logfile 
	if(!file_exists(BPI_LOGFILE)) {
		//create it
		if(!file_put_contents(BPI_LOGFILE,"BEGIN BPI LOG ".time()."\n")) 
		{
			$bpi_errors .=gettext("Unable to write to BPI log file: ").BPI_LOGFILE.". ".gettext("Check file permissions.")."<br />";  
			return false; 
		}	
	}
	//make sure it's writable, bail if it's not  
//	if(!is_writable(BPI_LOGFILE)) 
//	{
//		$bpi_errors .="Unable to write to BPI log file: ".BPI_LOGFILE.". Check file permissions.<br />";  
//		return false; 
//	}
			
	chmod(BPI_CONFIGFILE,0775);
	chmod(BPI_CONFIGBACKUP,0775); 
	chmod(BPI_LOGFILE,0775);
	
	return true; 

}


/**
*	reads globals.conf file and returns an array for directory and URL locations 
*	@return mixed array of global config options for BPI 
*/	
function bpi_fetch_options()
{
	global $bpi_options; 
	
	//set defaults if they're not there
	if(is_null(get_option('bpi_configfile')))
		set_option('bpi_configfile','/usr/local/nagiosxi/etc/components/bpi.conf');
		
	if(is_null(get_option('bpi_backupfile')))
		set_option('bpi_backupfile','/usr/local/nagiosxi/etc/components/bpi.conf.backup');	
	if(is_null(get_option('bpi_xmlfile')))
		set_option('bpi_xmlfile','/usr/local/nagiosxi/var/components/bpi.xml');	
	if(is_null(get_option('bpi_xmlthreshold')))
		set_option('bpi_xmlthreshold',90);
	if(is_null(get_option('bpi_logfile')))
		set_option('bpi_logfile','/usr/local/nagiosxi/var/components/bpi.log');		
	if(is_null(get_option('bpi_ignore_handled')))
		set_option('bpi_ignore_handled',false);
	if(is_null(get_option('bpi_multiplier')))
		set_option('bpi_multiplier',30);	
	if(is_null(get_option('bpi_showallgroups')))
		set_option('bpi_showallgroups',false);		
	
	//get options from DB 
	$bpi_options['CONFIGFILE'] = get_option('bpi_configfile');
	$bpi_options['CONFIGBACKUP'] = get_option('bpi_backupfile'); 
	$bpi_options['XMLFILE'] = get_option('bpi_xmlfile'); 
	$bpi_options['XMLTHRESHOLD'] = get_option('bpi_xmlthreshold'); 
	$bpi_options['IGNORE_PROBLEMS'] = get_option('bpi_ignore_handled'); 
	$bpi_options['LOGFILE']= get_option('bpi_logfile');
	$bpi_options['MULTIPLIER'] = get_option('bpi_multiplier');
	$bpi_options['SHOWALLGROUPS'] = get_option('bpi_showallgroups'); 
	
//	$bpi_options['AUTH_USERS'] = unserialize(get_option('bpi_auth_users'));  //use for community edition 
	
	return $bpi_options; 	
}	


/**
*	auth check for non-adminsitrative users to see if they can view the group
*	@param object $obj a reference variable to a BPI group object 
*	@param string $username the session username
*	@return boolean true | false 
*/ 
function is_authorized_for_bpi_group(&$obj,$username)
{
	global $bpi_options; 
	if(grab_array_var($bpi_options,'SHOWALLGROUPS',false)==true)
		return true; 

	if(is_admin($_SESSION['user_id'])) 
		return true;
		
	if(is_authorized_for_all_objects($_SESSION['user_id']))	
		return true;
		
	if(in_array($username,$obj->auth_users) || in_array('*',$obj->auth_users)) 
		return true;
			
	return false; 		

}

/*
*	checks to see if user is an admin or if they have global write access to BPI groups 
*	@return boolean true | false 
*/ 

function can_control_bpi_groups($username='') 
{
	global $bpi_options; 

	if(is_admin($_SESSION['user_id'])) 
		return true;
	//not currently implemented 
//	if(is_array($bpi_options['AUTH_USERS']) && in_array($username, $bpi_options['AUTH_USERS']) ) 
//		return true; 	
		
	return false; 	

}

/**
*	NOT CURRENTLY USED: fetches list of all non-administrative users 
*	@return mixed array of non-admin users 
*/ 
function get_regular_users()
{

	$userlist = get_xml_users();
	//create list of non-admin users 
	$reg_users = array(); 
	foreach($userlist->user as $user) 
	{
		if(get_user_meta($user['id'],'userlevel')!=L_GLOBALADMIN)
			$reg_users["$user->username"] = "$user->name";
	}
	return $reg_users; 

}


/**
*	writes various messages to the BPI log file
*	@param string $msg the unformatted log message to write
*/
function bpi_log_errors($msg)
{
	//is the logfile bigger than 10MB? 
	$write = (filesize(BPI_LOGFILE) > 10485760) ? 'wb' : 'ab'; 

	$f = fopen(BPI_LOGFILE,$write);
	fwrite($f,$msg."\n\n");
	fclose($f); 
}

/**
*	subsystem function used by the check plugin to cache XML data for greater CPU efficiency 
*/ 
function xml_dump()
{
	global $bpi_options;
	global $bpi_objects; 
	
	//write fresh xml data to file 
	$xml =  '<?xml version="1.0" encoding="iso-8859-1"?>'."\n"; //doctype
	$xml .= "<bpigroups>\n"; //root tag
	foreach($bpi_objects as $obj)
		$xml .= $obj->get_xml(); 
	
	//close xml 
	$xml .=	"</bpigroups>\n";
	$f = fopen($bpi_options['XMLFILE'],'w');
	fwrite($f,$xml); 
	fclose($f); 	
	@chmod($bpi_options['XMLFILE'],0775); 

}

/**
*	fetches bpi xml group status and returns xml
*
*/ 
function bpi_xml_fetch() {
	global $bpi_options; 
	
	$f = file_get_contents($bpi_options['XMLFILE']); 
	return $f; 
}


?>
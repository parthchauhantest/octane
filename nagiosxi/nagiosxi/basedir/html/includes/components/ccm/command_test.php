<?php //command_test.php    receives a check command and a set of arguments to test on the commandline

session_start(); 
define('BASEDIR',dirname(__FILE__).'/');   
require_once(BASEDIR.'includes/session.inc.php'); 


//TODO: add token check 

$cmd = ccm_grab_request_var('cmd',''); 
$token = ccm_grab_request_var('token',''); 


//authorization check 
if($AUTH!==true) $cmd = 'login'; 
//verify that the command was submitted from the form, route to login page if it's an illegal operation 
verify_token($cmd,$token); 
//echo "TOKEN IS: $token<br />"; 
//echo "SESSION IS: {$_SESSION['token']}<br />"; 
//echo "CMD is: $cmd<br />"; 

route_request($cmd);


/*function route_request()
*	directs page navigation and input requests for command tests, verifies auth 
*	@param string $cmd requires a valid command to do anything, if auth it bad this will be '' | 'login' 
*/ 
function route_request($cmd='')
{

	//bail on a bad auth 
	if($cmd=='login')
	{
		header('Location: index.php?cmd=login'); 
		return; 
	}

	$mode = ccm_grab_request_var('mode',''); 
	
	switch($mode)
	{
		case 'help':
			$plugin = escapeshellcmd(ccm_grab_request_var('plugin','')); 
			
			//array of security vulnerabilities 
			$hacks = array('&&', '../', 'cd /',';','\\'); 
			foreach($hacks as $h) 
				if(strpos($h,$plugin)) break; 			
					
			
			//print plugin help output 
			get_plugin_doc($plugin); 		
		break; 
		
		case 'test':
			test_command(); 
		break; 
		
		default:
		break; 
	}
	

}

/** test_command()
*	cleans input variables and executes them from the command-line, returns output to browser
*	@global class $ccmDB global database class 
*	@global array $CFG global config array, used to fetch plugins directory 
*/
function test_command()
{
	$ccmDB = new Db(); 
	global $CFG;
	//global $request;

	//command ID
	$cid  = intval(ccm_grab_request_var('cid')); 
	$address = escapeshellcmd(ccm_grab_request_var('address','')); 
	$host = escapeshellcmd(ccm_grab_request_var('host','')); 
	//$cid = $_GET['cid']; 
	$arg1 = escapeshellcmd(ccm_grab_array_var($_REQUEST,'arg1', ''));
	$arg2 = escapeshellcmd(ccm_grab_array_var($_REQUEST,'arg2', ''));
	$arg3 = escapeshellcmd(ccm_grab_array_var($_REQUEST,'arg3', ''));
	$arg4 = escapeshellcmd(ccm_grab_array_var($_REQUEST,'arg4', ''));
	$arg5 = escapeshellcmd(ccm_grab_array_var($_REQUEST,'arg5', ''));
	$arg6 = escapeshellcmd(ccm_grab_array_var($_REQUEST,'arg6', ''));
	$arg7 = escapeshellcmd(ccm_grab_array_var($_REQUEST,'arg7', ''));
	$arg8 = escapeshellcmd(ccm_grab_array_var($_REQUEST,'arg8', ''));
	
	//$cid = mysql_real_escape_string($cid); 

	$query = "SELECT `command_name`,`command_line` FROM tbl_command WHERE `id`='$cid' && command_type=1 LIMIT 1;"; 
	//print $query; 
	$command = $ccmDB->query($query); 
	
	//print_r($command); 
	if(!isset($command[0]['command_name'])) {
		print "ERROR: Unable to locate the command in the database<br />"; 
		exit(); 
	}
	
	$name = $command[0]['command_name'];
	$cmd_line = $command[0]['command_line']; 
	
	$haystack = array($CFG['plugins_directory'],$address,$arg1,$arg2,$arg3,$arg4,$arg5,$arg6,$arg7,$arg8); 
	$needles = array('$USER1$','$HOSTADDRESS$','$ARG1$','$ARG2$','$ARG3$','$ARG4$','$ARG5$','$ARG6$','$ARG7$','$ARG8$'); 
	
	$fullcommand = str_replace($needles,$haystack, $cmd_line); 
	
	  
	
	$bool = exec($fullcommand,$plugin_output); 

	echo "<pre>COMMAND: $fullcommand\n";
	echo "OUTPUT: "; 
	foreach($plugin_output as $line) print $line."\n"; 
	echo "</pre>";
	
	
	//create full commandline string 
		//check for use of $ARGx$ in command-line string 
		//add args to the commandline string where appropriate
		
	//execute command on commandline, save output, pass output back to screen  exec()? or passthru()??   	
	
	//print_r($commands); 	

} //end test_command() 



/* function get_plugin_doc()
*	executes plugin from the command-line with -h flag and prints output between pre tags 
*	@param string $plugin the plugin name 
*/
function get_plugin_doc($plugin)
{
	global $CFG; 
	
	exec($CFG['plugins_directory'].'/'.$plugin.' -h',$output); 
	print "<pre>"; 
	foreach($output as $line) print $line."\n";
	print "</pre>"; 

}
?>

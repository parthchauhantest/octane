<?php //applyconfig.inc.php


/** function apply_config() 
*	
*	routes page commands, display page html with feedback 
*	@param string $mode handles the page commands, passed from page_router(), $_REQUEST['type'] 
*	@return string $html returns html output to index.php 
*/ 
function apply_config($mode='') {

	/////Benchmarking//
	/*
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$start = $time;
	*/ 
	
	// request vars 
	switch($mode)
	{
		case 'writeConfig':
		case 'verify':
		case 'restart':
			list($returnCode,$returnMessage)=write_config_tool($mode); 
			$html = write_config_html($returnCode,$returnMessage); 
		break; 
		case 'applyfull':
			$msg = gettext("Applying Configuration").'...<br />'; 
			$errors = 0; 
			list($verifyMsg,$verifyCode)=write_config_tool('verify');
			if($verifyCode > 0) 
			{
				$html = write_config_html($verifyCode,$verifyMessage); 
				break; 
			}
			$msg .= gettext("Configuration verification successful!")." <br />"; 
			list($writeCode,$writeMessage)=write_config_tool('writeConfig');
			if($writeCode > 0)
			{
				$html = write_config_html($writeCode,$writeMessage); 
				break;				
			}
			$msg .=gettext("Configurations successfully written to file!")." <br />"; 
			list($restartCode,$restartMessage)=write_config_tool('restart');
			if($restartCode > 0)
			{
				$errors=1; 
				$html = write_config_html($errors,$restartMessage);
				break;
			}
			$msg .= gettext("Nagios process restarted successfully!")."<br />"; 	
			$html = write_config_html($errors,$msg); 		
		break; 
		
		default:
			//display form options
			$html = write_config_html(); 	
		break; 		
	}

	/*
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish = $time;
	$total_time = round(($finish - $start), 4);
	echo 'Page generated in '.$total_time.' seconds.'."\n";
	echo "TOTAL MEMORY PEAK: ".memory_get_peak_usage()."<br />";
	*/
	
	return $html; 
}


/** function write_config_html()  
*	write the page html based on success or failure of the command submitted
*	@param int $code error code returned from the command submitted
*	@param string $message return message from any submitted command
*	@return string $html html string to be displayed in the browser 
*/
function write_config_html($code=0,$message='')
{

	//return output messages 
	if($message=='') $retClass='invisible';
	else $retClass = ($code > 0) ? 'error' : 'success'; 

	//build html string 
	$html = "
	<div id='contentWrapper'>
		<h3>".gettext("Write Database Configs To File")."</h3>
		<p>".gettext("Use this tool to manually write Nagios object configurations to physical configuration files.")." </p>
		
		<div id='writeConfigDiv' class='floatLeft'>
			<form action='index.php?cmd=apply' method='post' id='writeConfigForm'>
				<label for='writeConfig'>".gettext("Write Configs To File")."</label><br />
				<input onclick=\"doConfig('writeConfig')\" class='ccmButton ac_button' type='button' name='writeConfig' id='writeConfig' value='".gettext("Write")."' /><br />
				<label for='verify'>".gettext("Verify Configuration")."</label><br />
				<input onclick=\"doConfig('verify')\" class='ccmButton ac_button' type='button' name='verify' id='verify' value='".gettext("Verify")."' /><br />
				<label for='restart'>".gettext("Restart Nagios")."</label><br />
				<input onclick=\"doConfig('restart')\" class='ccmButton ac_button' type='button' name='restart' id='restart' value='".gettext("Restart")."' /><br />
				<input type='hidden' name='type' id='type' value='' /> 
				<input type='hidden' name='key' id='key' value='' />				
			
			</form>	
		</div>
		
		<div id='applyConfigOutput' class='{$retClass} floatLeft'>{$message}</div> <!-- end applyConfigOutput div -->
	</div><!-- end contentWrapper -->
	"; 	
	return $html; 
}



/** function write_config_tool() 
*	submits commands based on the $mode (write | verify | restart) 
*	@param string $mode (write | verify | restart)
*	@return array array(int errorCode, string returnMessage) 
*/
function write_config_tool($mode='')
{
	global $myConfigClass; 
	global $myDataClass;
	global $myDBClass; 
	global $CFG;
	$chkDomainId = $_SESSION['domain']; 
	$strMessage = ''; 

	//route command by mode 
	switch($mode)   
	{
		//verify nagios config 
		case 'verify': 
			$errorString = verify_configs($strMessage); 

			//return output 
			if($errorString)   				 
				return array(1,"<span class='urgent'>$errorString</span>$strMessage");
			else 
				return array(0,$strMessage);							


		//restart nagios process 
		case 'restart':
			$strMessage = ''; 
			$code = 0;
			//need to make sure the www-data user can do this on a core install 
		/*	$cmd = "/etc/init.d/nagios restart"; 
			//echo $cmd; 
			//$msg = exec($cmd,$output);
			$msg = system($cmd,$output); 

			if(strpos($msg,'Starting nagios: done.') ===false) $code = 1; 
			foreach($msg as $line) $strMessage .=$line."<br />";  
			//echo "MSG is: $msg"; 
			foreach($msg as $m) print "M is: $m<br />"; 
			print_r($msg); 
 			print_r($output); 
 		*/ 
 			$errors = verify_configs($strMessage);  
 			
			//echo "ERRORS: $errors";   			
 			//bail if config is bad 
 			if($errors!== false) 
 				return array(1,gettext('RESTART FAILED. CONFIG ERROR:')."<br />".$errors);
 			//if config verified, continue 
			$now = time(); 
			$commandfile='/usr/local/nagios/var/rw/nagios.cmd';			
 			$cmd = '/usr/bin/printf "[%lu] RESTART_PROGRAM\n" '.$now.' > '.$commandfile; 
 			//echo $cmd; 
			//$msg = exec($cmd,$code);
			$msg = system($cmd,$code); 
			//print "MSG $msg CODE: $code";  
			//print_r($msg);
			//print_r($code); 
			if($code ==0) $strMessage = gettext("Restart command successfully sent to Nagios"); 
   		 	  
		return array($code,$strMessage);  
	
		//write DB to config files 
		case 'writeConfig': 
	
			$intError = 0; 
		  // Write host configurations
		  $strInfo = gettext("Write host configurations")." ...<br />";
		  $strSQL  = "SELECT `id`,`host_name` FROM `tbl_host` WHERE `config_id` = $chkDomainId AND `active`='1'";
		  //echo $strSQL; 
		  $myDBClass->getDataArray($strSQL,$arrData,$intDataCount);
		  $intError = 0;
		  if ($intDataCount != 0)  {
		    foreach ($arrData AS $data)  {
				/////////experimental////only write to file if it's older///
				//$myConfigClass->lastModifiedDir($data['host_name'],$data['id'],'host',$strTime,$strTimeFile,$intOlder);
				//if($intOlder == 1) {
					//echo "OLDER TIME: $strTime TF: $strTimeFile <br />"; 
					$myConfigClass->createConfigSingle("tbl_host",$data['id']);
					// echo "HOST: ".$myConfigClass->strDBMessage."<br />"; 
					if ($myConfigClass->strDBMessage != gettext("Configuration file successfully written!")) 
						$intError++;
				//}
		    }//end foreach 
		  }//end IF 
		  
		  //error output  
		  if ($intError == 0) 		  
		    $strInfo .= "<span class=\"verify-ok\">".gettext("Host configuration files successfully written!")."</span><br /><br />";		  
		  else 		  
		    $strInfo .= "<span class='urgent'>".gettext("Cannot open/overwrite the host configuration files (check the permissions)!")."</span><br>";
		 
		  // Write service configuration
		  $strInfo .= gettext("Write service configurations")." ...<br>";
		  $strSQL   = "SELECT `id`, `config_name` FROM `tbl_service` WHERE `config_id` = $chkDomainId AND `active`='1' GROUP BY HEX(`config_name`) ";
		  $myDBClass->getDataArray($strSQL,$arrData,$intDataCount);
		  $intError = 0;
		  if ($intDataCount != 0) {
		      foreach ($arrData AS $data) {
				  /////////experimental////only write to file if it's older/// --> CAN'T USE, if someone renames an object, it screws up everything - MG
				  //$myConfigClass->lastModifiedDir($data['config_name'],$data['id'],'service',$strTime,$strTimeFile,$intOlder);
				  //if($intOlder == 1) {			  
					  $myConfigClass->createConfigSingle("tbl_service",$data['id']);
					  if ($myConfigClass->strDBMessage != gettext("Configuration file successfully written!")) $intError++;
				  //} //end if older  
			  } //end foreach 
		  }
		  if ($intError == 0) 
		    $strInfo .= "<span class=\"verify-ok\">".gettext("Service configuration files successfully written!")."</span><br><br>";
		  else 
		    $strInfo .= "<span class='urgent'>".gettext("Cannot open/overwrite service configuration files (check the permissions)!")."</span><br>";
		  
			//write configs for single config files 
		  $myConfigClass->createConfig("tbl_hostgroup");
		  $strInfo .= $myConfigClass->strDBMessage."<br>";
		  $myConfigClass->createConfig("tbl_servicegroup");
		  $strInfo .= $myConfigClass->strDBMessage."<br>";
		  $myConfigClass->createConfig("tbl_hosttemplate");
		  $strInfo .= $myConfigClass->strDBMessage."<br>";
		  $myConfigClass->createConfig("tbl_servicetemplate");
		  $strInfo .= $myConfigClass->strDBMessage."<br>";
		  $myConfigClass->createConfig("tbl_timeperiod");
		  $strInfo .= $myConfigClass->strDBMessage."<br>";
		  $myConfigClass->createConfig("tbl_command");
		  $strInfo .= $myConfigClass->strDBMessage."<br>";
		  $myConfigClass->createConfig("tbl_contact");
		  $strInfo .= $myConfigClass->strDBMessage."<br>";
		  $myConfigClass->createConfig("tbl_contactgroup");
		  $strInfo .= $myConfigClass->strDBMessage."<br>";
		  $myConfigClass->createConfig("tbl_contacttemplate");
		  $strInfo .= $myConfigClass->strDBMessage."<br>";
		  $myConfigClass->createConfig("tbl_servicedependency");
		  $strInfo .= $myConfigClass->strDBMessage."<br>";
		  $myConfigClass->createConfig("tbl_hostdependency");
		  $strInfo .= $myConfigClass->strDBMessage."<br>";
		  $myConfigClass->createConfig("tbl_serviceescalation");
		  $strInfo .= $myConfigClass->strDBMessage."<br>";
		  $myConfigClass->createConfig("tbl_hostescalation");
		  $strInfo .= $myConfigClass->strDBMessage."<br>";
		  $myConfigClass->createConfig("tbl_serviceextinfo");
		  $strInfo .= $myConfigClass->strDBMessage."<br>";
		  $myConfigClass->createConfig("tbl_hostextinfo");
		  $strInfo .= $myConfigClass->strDBMessage."<br>";
	  
	  //echo $myConfigClass->strDBMessage;  
	  //echo $strInfo; 
	  return array($intError,$strInfo); 
	  break; 
	  
	  
 	  
	  default:
	  //do nothing break; 
	  break; 
	}//end switch 

} //end write_config_tool() 

/*function verify_configs () 
*	runs the nagios verification command and returns the text output from it
	@global object $myConfigClass nagiosql config object 
	@global object $myDataClass nagiosql data object
	@global object $myDBClass 	nagiosql database handler 
*	@param string $strMessage REFERENCE variable to the return message 
*	@return string $errorString
*
*/
function verify_configs(&$strMessage)
{

	global $myConfigClass; 
	global $myDataClass;
	global $myDBClass; 
	$chkDomainId = $_SESSION['domain']; 

	$myConfigClass->getConfigData("binaryfile",$strBinary);
	$myConfigClass->getConfigData("basedir",$strBaseDir);
	$myConfigClass->getConfigData("nagiosbasedir",$strNagiosBaseDir);
	$errorString = false; 
	
	if (file_exists($strBinary) && is_executable($strBinary)) 
	{
		$resFile = popen($strBinary." -v ".str_replace("//","/",$strNagiosBaseDir."/nagios.cfg"),"r");
		if($resFile)
		{
			$output =''; 
			//echo "there is output!"; 
			while(!feof($resFile))
			{
				$line = fgets($resFile); 
				//capture error output 
				if(strpos($line,'Error:') !==false) $errorString.= $line.'<br />';  
				$strMessage.= $line."<br />";  
			} 	  
		}//end IF file handle 				  	
		else $errorString= gettext("Can't find Nagios binary!"); 
		pclose($resFile); 
	} 
	else 
	{
		$errorString = gettext('Cannot find the Nagios binary or no rights for execution!');
		//echo "Can't find or execute nagios binary!"; 
	}
	
	return $errorString; 
	
}

?>
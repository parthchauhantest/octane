<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// Development Started 03/22/2008
// $Id: utils-configwizards.inc.php 1090 2012-03-27 15:23:41Z egalstad $

//require_once(dirname(__FILE__).'/common.inc.php');



////////////////////////////////////////////////////////////////////////
//CONFIG WIZARD  FUNCTIONS
////////////////////////////////////////////////////////////////////////

function register_configwizard($name="",$args=null){
	global $configwizards;
	
	
	if($name=="")
		return false;
	
	if(!isset($configwizards)){
		$configwizards=array();
		}
	
	$configwizards[$name]=$args;
	
	return true;
	}


function get_configwizard_by_name($name=""){
	global $configwizards;
	
	$configwizard=null;

	if($name=="")
		return null;
		
	if(!array_key_exists($name,$configwizards))
		return null;
	$configwizard=$configwizards[$name];
		
	return $configwizard;
	}
	
// tests if a wizard is installed
function is_configwizard_installed($name){

	if(get_configwizard_by_name($name)!=null)
		return true;

	return false;
	}
	
function make_configwizard_callback($name="",$mode="",$inargs,&$outargs,&$result){
	
	// USE THIS FOR DEBUGGING!
	//return "<BR>NAME: $name, ID: $id, MODE: $mode, ARGS: ".serialize($args)."<BR>";
	
	$w=get_configwizard_by_name($name);
	if($w==null)
		return "";
		
	$output="";
		
	// run the  function
	if(array_key_exists(CONFIGWIZARD_FUNCTION,$w) && have_value($w[CONFIGWIZARD_FUNCTION])==true){
		$f=$w[CONFIGWIZARD_FUNCTION];
		if(function_exists($f))
			$output=$f($mode,$inargs,$outargs,$result);
		else
			$output="CONFIG WIZARD FUNCTION '".$f."' DOES NOT EXIST";
		}
	// nothing to do...
	else
		return $output;
		
	return $output;
	}

function clean_configwizard_request_vars(){
	global $request;
	
	// clear some request variables that shouldn't be passed to wizards
	$clear=array(
		//"nextstep",
		"update",
		"submitButton",
		"cancelButton",
		);
	foreach($clear as $c){
		if(array_key_exists($c,$request))
			unset($request[$c]);
		}
	}
	
// called by the command sub-system
function install_configwizard($args=null){

	if($args==null)
		return 0;
	if(!is_array($args))
		return 0;
		
	$wizard_name=grab_array_var($args,"wizard_name");
	$wizard_dir=grab_array_var($args,"wizard_dir");
	$allow_restart=grab_array_var($args,"allow_restart",true);
	
	// process config xml file
	$cfgfile=$wizard_dir."/config.xml";
	echo "PROCESSING CONFIG FILE $cfgfile\n";
	if(file_exists($cfgfile)){
		$xml=simplexml_load_file($cfgfile);
		if($xml){
			//print_r($xml);
			if($xml->logos){
				foreach($xml->logos->logo as $logo){
					echo "LOGO: \n";
					//print_r($logo);
					foreach($logo->attributes() as $a => $b){
						echo "LOGO ATTRIBUTE $a => $b\n";
						
						if($a=="filename"){
							$fname=$wizard_dir."/logos/".$b;
							$dest="/usr/local/nagiosxi/html/includes/components/nagioscore/ui/images/logos/".$b;
							if(file_exists($fname)){
								echo "COPYING LOGO: $fname\n";
								echo "          TO: $dest\n";
								copy($fname,$dest);
								}
							else
								echo "LOGO NOT FOUND: $fname\n";
							}
						}
					}
				}
			if($xml->plugins){
				foreach($xml->plugins->plugin as $plugin){
					echo "PLUGIN: \n";
					foreach($plugin->attributes() as $a => $b){
						echo "PLUGIN ATTRIBUTE $a => $b\n";
						
						if($a=="filename"){
							$fname=$wizard_dir."/plugins/".$b;
							$dest="/usr/local/nagios/libexec/".$b;
							if(file_exists($fname)){
								echo "COPYING PLUGIN: $fname\n";
								echo "             TO: $dest\n";
								if(copy($fname,$dest)){
									// make the plugin executable
									chmod($dest,0755);
									}
								}
							else
								echo "PLUGIN NOT FOUND: $fname\n";
							}
						}
					}
				}
			if($xml->templates){
				$restart_nagios=false;
				foreach($xml->templates->template as $template){
					echo "TEMPLATE: \n";
					foreach($template->attributes() as $a => $b){
						echo "TEMPLATE ATTRIBUTE $a => $b\n";

						if($a=="filename"){
							$fname=$wizard_dir."/templates/".$b;
							$dest="/usr/local/nagios/etc/import/configwizard-".$b;
							if(file_exists($fname)){
								echo "COPYING TEMPLATE: $fname\n";
								echo "              TO: $dest\n";
								if(copy($fname,$dest)){
									$restart_nagios=true;
									}
								}
							else
								echo "TEMPLATE NOT FOUND: $fname\n";
							}
						}
					}
				if($restart_nagios==true){
					if($allow_restart==true){
						echo "RESTARTING NAGIOS CORE...\n";
						reconfigure_nagioscore();
						}
					else
						echo "SKIPPING NAGIOS CORE RESTART...\n";
					}
				}

			}
		else
			echo "BAD XML!\n";
		}
	else
		echo "CONFIG FILE DOESN'T EXIST\n";
		
	// post-install script
	$install_script=$wizard_dir."/install.sh";
	echo "CHECKING FOR INSTALL SCRIPT '".$install_script."'...\n";
	if(file_exists($install_script)){
	
		echo "RUNNING INSTALL SCRIPT...\n";
	
		// make the script executable
		chmod($install_script,0755);
		
		// run the script
		system($install_script,$retval);
		
		echo "INSTALL SCRIPT FINISHED. RESULT='$retval'\n";
		return $retval;
		}
	else{
		echo "INSTALL SCRIPT DOES NOT EXIST.\n";
		}

	return 0;
	}
?>
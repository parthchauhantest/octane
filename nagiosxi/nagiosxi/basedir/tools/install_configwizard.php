#!/usr/bin/php -q
<?php
// MANUALLY CONFIG WIZARD INSTALL SCRIPT
//
// Copyright (c) 2011 Nagios Enterprises, LLC.
//  

define("SUBSYSTEM",1);

require_once(dirname(__FILE__).'/../html/config.inc.php');
require_once(dirname(__FILE__).'/../html/includes/utils.inc.php');
//require_once(dirname(__FILE__).'/../html/includes/utils-configwizards.inc.php');

doit();

	
function doit(){
	global $argv;
	
	$wizard_base_dir="/usr/local/nagiosxi/html/includes/configwizards/";
	
	$wizard="";
	$restart="true";
	$refresh=0;
	$allow_restart=false;

	$args=parse_argv($argv);
	
	$file=grab_array_var($args,"file");
	$restart=grab_array_var($args,"restart","true");
	$refresh=grab_array_var($args,"refresh",0);
		
	if($restart=="true")
		$allow_restart=true;
	
	if($file==""){
		echo "Nagios XI Wizard Installation Tool\n";
		echo "Copyright (c) 2011 Nagios Enterprises, LLC\n";
		echo "\n";
		echo "Usage: ".$argv[0]." --file=<zipfile> [--restart=<true/false>] [--refresh=<0/1>]\n";
		echo "\n";
		echo "Installs a new configuration wizard from a zip file.\n";
		exit(1);
		}
		
	$zipfile=realpath($file);
		
	if(!file_exists($zipfile)){
		echo "ERROR: File '$file' does not exist\n";
		exit(1);
		}
	
	// make database connections
	$dbok=db_connect_all();
	if($dbok==false){
		echo "ERROR CONNECTING TO DATABASES!\n";
		exit();
		}
		
		
	// create a new temp directory for holding the unzipped wizard
	$tmpname=random_string(5);
	echo "TMPNAME: $tmpname\n";
	$tmpdir="/usr/local/nagiosxi/tmp/".$tmpname;
	system("rm -rf ".$tmpdir);
	mkdir($tmpdir);
	
	// unzip wizard to temp directory
	$cmdline="cd ".$tmpdir." && unzip -o ".$zipfile;
	system($cmdline);
	
	// determine wizard directory/file name
	$cdir=system("ls -1 ".$tmpdir."/");
	$wizard_name=$cdir;
	
	echo "WIZARD NAME: $wizard_name\n";
	
	// make sure this is a config wizard
	$cmdline="grep register_configwizard ".$tmpdir."/".$cdir."/".$wizard_name.".inc.php | wc -l";
	echo "CMD=$cmdline\n";
	$out=system($cmdline,$rc);
	echo "OUT=$out\n";
	if($out=="0"){
	
		// delete temp directory
		$cmdline="rm -rf ".$tmpdir;
		echo "CMD: $cmdline\n";
		system($cmdline);
		
		$output="Zip file is not a config wizard.";
		echo $output."\n";
		exit(1);
		}
		
	echo "Wizard looks ok...\n";
	
	// where should the wizard go?
	$wizard_dir="/usr/local/nagiosxi/html/includes/configwizards/".$wizard_name;
	
	// check times
	if($refresh==1){
		$newfile=$tmpdir."/".$cdir."/".$wizard_name.".inc.php";
		$ziptime=filemtime($newfile);
		$oldfile=$wizard_dir."/".$wizard_name.".inc.php";
		if(!file_exists($oldfile))
			$oldtime=0;
		else
			$oldtime=filemtime($oldfile);
		echo "ZIPTIME: $ziptime\n";
		echo "OLDTIME: $oldtime\n";
		if($ziptime<=$oldtime){
		
			echo "Wizard does not need to be updated.\n";

			// delete temp directory
			$cmdline="rm -rf ".$tmpdir;
			echo "CMD: $cmdline\n";
			system($cmdline);
			
			exit(0);
			}
		else{
			echo "Wizard needs to be updated...\n";
			}
		}
	
	// make new wizard directory (might exist already)
	@mkdir($wizard_dir);
	
	// move wizard to production directory and delete temp directory
	$cmdline="cp -rf ".$tmpdir."/".$cdir." /usr/local/nagiosxi/html/includes/configwizards/";
	echo "CMD: $cmdline\n";
	system($cmdline);
	
	// delete temp directory
	$cmdline="rm -rf ".$tmpdir;
	echo "CMD: $cmdline\n";
	system($cmdline);

	
	// run internal wizard installation functions
	$args=array(
		"wizard_name" => $wizard_name,
		"wizard_dir" => $wizard_dir,
		"allow_restart" => $allow_restart,
		);
	install_configwizard($args);
	
	// fix permissions
	$cmdline="chown -R nagios ".$wizard_dir;
	echo "CMD: $cmdline\n";
	system($cmdline);
	
	echo "\n\nDone!\n";
		
	exit(0);
	}
	


?>
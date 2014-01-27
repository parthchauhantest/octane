#!/usr/bin/php -q
<?php
// MANUAL COMPONENT INSTALLATION SCRIPT
//
// Copyright (c) 2011 Nagios Enterprises, LLC.
//  

define("SUBSYSTEM",1);

require_once(dirname(__FILE__).'/../html/config.inc.php');
require_once(dirname(__FILE__).'/../html/includes/utils.inc.php');

doit();

	
function doit(){
	global $argv;
	
	$component_base_dir="/usr/local/nagiosxi/html/includes/components/";
	
	$component="";
	$refresh=0;

	$args=parse_argv($argv);
	
	$file=grab_array_var($args,"file");
	$refresh=grab_array_var($args,"refresh",0);
		
	if($file==""){
		echo "Nagios XI Component Installation Tool\n";
		echo "Copyright (c) 2011 Nagios Enterprises, LLC\n";
		echo "\n";
		echo "Usage: ".$argv[0]." --file=<zipfile>  [--refresh=<0/1>]\n";
		echo "\n";
		echo "Installs a new component from a zip file.\n";
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
		
		
	// create a new temp directory for holding the unzipped component
	$tmpname=random_string(5);
	echo "TMPNAME: $tmpname\n";
	$tmpdir="/usr/local/nagiosxi/tmp/".$tmpname;
	system("rm -rf ".$tmpdir);
	mkdir($tmpdir);
	
	// unzip component to temp directory
	$cmdline="cd ".$tmpdir." && unzip -o ".$zipfile;
	system($cmdline);
	
	// determine component directory/file name
	$cdir=system("ls -1 ".$tmpdir."/");
	$component_name=$cdir;
	
	echo "COMPONENT NAME: $component_name\n";
	
	// make sure this is a component
	$cmdline="grep register_component ".$tmpdir."/".$cdir."/".$component_name.".inc.php | wc -l";
	echo "CMD=$cmdline\n";
	$out=system($cmdline,$rc);
	echo "OUT=$out\n";
	if($out=="0"){
	
		// delete temp directory
		$cmdline="rm -rf ".$tmpdir;
		echo "CMD: $cmdline\n";
		system($cmdline);
		
		$output="Zip file is not a component.";
		echo $output."\n";
		exit(1);
		}
		
	echo "Component looks ok...\n";
	
	// where should the component go?
	$component_dir="/usr/local/nagiosxi/html/includes/components/".$component_name;
	
	// check times
	if($refresh==1){
		$newfile=$tmpdir."/".$cdir."/".$component_name.".inc.php";
		$ziptime=filemtime($newfile);
		$oldfile=$component_dir."/".$component_name.".inc.php";
		if(!file_exists($oldfile))
			$oldtime=0;
		else
			$oldtime=filemtime($oldfile);
		echo "ZIPTIME: $ziptime\n";
		echo "OLDTIME: $oldtime\n";
		if($ziptime<=$oldtime){
		
			echo "Component does not need to be updated.\n";

			// delete temp directory
			$cmdline="rm -rf ".$tmpdir;
			echo "CMD: $cmdline\n";
			system($cmdline);
			
			exit(0);
			}
		else{
			echo "Component needs to be updated...\n";
			}
		}
	
	// make new component directory (might exist already)
	@mkdir($component_dir);
	
	// move component to production directory and delete temp directory
	$cmdline="cp -rf ".$tmpdir."/".$cdir." /usr/local/nagiosxi/html/includes/components/";
	echo "CMD: $cmdline\n";
	system($cmdline);
	
	// delete temp directory
	$cmdline="rm -rf ".$tmpdir;
	echo "CMD: $cmdline\n";
	system($cmdline);

	
	// run internal component installation functions
	$args=array(
		"component_name" => $component_name,
		"component_dir" => $component_dir,
		);
	install_component($args);
	
	// fix permissions
	$cmdline="chown -R nagios ".$component_dir;
	echo "CMD: $cmdline\n";
	system($cmdline);
	
	echo "\n\nDone!\n";
		
	exit(0);
	}
	


?>
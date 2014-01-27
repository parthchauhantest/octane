#!/usr/bin/php -q
<?php
// MANUAL DASHLET INSTALLATION SCRIPT
//
// Copyright (c) 2011 Nagios Enterprises, LLC.
//  

define("SUBSYSTEM",1);

require_once(dirname(__FILE__).'/../html/config.inc.php');
require_once(dirname(__FILE__).'/../html/includes/utils.inc.php');

doit();

	
function doit(){
	global $argv;
	
	$dashlet_base_dir="/usr/local/nagiosxi/html/includes/dashlets/";
	
	$dashlet="";
	$refresh=0;

	$args=parse_argv($argv);
	
	$file=grab_array_var($args,"file");
	$refresh=grab_array_var($args,"refresh",0);
		
	if($file==""){
		echo "Nagios XI Dashlet Installation Tool\n";
		echo "Copyright (c) 2011 Nagios Enterprises, LLC\n";
		echo "\n";
		echo "Usage: ".$argv[0]." --file=<zipfile>  [--refresh=<0/1>]\n";
		echo "\n";
		echo "Installs a new dashlet from a zip file.\n";
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
		
		
	// create a new temp directory for holding the unzipped dashlet
	$tmpname=random_string(5);
	echo "TMPNAME: $tmpname\n";
	$tmpdir="/usr/local/nagiosxi/tmp/".$tmpname;
	system("rm -rf ".$tmpdir);
	mkdir($tmpdir);
	
	// unzip dashlet to temp directory
	$cmdline="cd ".$tmpdir." && unzip -o ".$zipfile;
	system($cmdline);
	
	// determine dashlet directory/file name
	$cdir=system("ls -1 ".$tmpdir."/");
	$dashlet_name=$cdir;
	
	echo "DASHLET NAME: $dashlet_name\n";
	
	// make sure this is a dashlet
	$cmdline="grep register_dashlet ".$tmpdir."/".$cdir."/".$dashlet_name.".inc.php | wc -l";
	echo "CMD=$cmdline\n";
	$out=system($cmdline,$rc);
	echo "OUT=$out\n";
	if($out=="0"){
	
		// delete temp directory
		$cmdline="rm -rf ".$tmpdir;
		echo "CMD: $cmdline\n";
		system($cmdline);
		
		$output="Zip file is not a dashlet.";
		echo $output."\n";
		exit(1);
		}
		
	echo "Dashlet looks ok...\n";
	
	// where should the dashlet go?
	$dashlet_dir="/usr/local/nagiosxi/html/includes/dashlets/".$dashlet_name;
	
	// check times
	if($refresh==1){
		$newfile=$tmpdir."/".$cdir."/".$dashlet_name.".inc.php";
		$ziptime=filemtime($newfile);
		$oldfile=$dashlet_dir."/".$dashlet_name.".inc.php";
		if(!file_exists($oldfile))
			$oldtime=0;
		else
			$oldtime=filemtime($oldfile);
		echo "ZIPTIME: $ziptime\n";
		echo "OLDTIME: $oldtime\n";
		if($ziptime<=$oldtime){
		
			echo "Dashlet does not need to be updated.\n";

			// delete temp directory
			$cmdline="rm -rf ".$tmpdir;
			echo "CMD: $cmdline\n";
			system($cmdline);
			
			exit(0);
			}
		else{
			echo "Dashlet needs to be updated...\n";
			}
		}
	
	// make new dashlet directory (might exist already)
	@mkdir($dashlet_dir);
	
	// move dashlet to production directory and delete temp directory
	$cmdline="cp -rf ".$tmpdir."/".$cdir." /usr/local/nagiosxi/html/includes/dashlets/";
	echo "CMD: $cmdline\n";
	system($cmdline);
	
	// delete temp directory
	$cmdline="rm -rf ".$tmpdir;
	echo "CMD: $cmdline\n";
	system($cmdline);
	
	// fix permissions
	$cmdline="chown -R nagios ".$dashlet_dir;
	echo "CMD: $cmdline\n";
	system($cmdline);
	
	echo "\n\nDone!\n";
		
	exit(0);
	}
	


?>
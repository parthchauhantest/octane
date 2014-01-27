#!/usr/bin/php -q
<?php
// UTILITY TO CREATE OBJECTS
//
// Copyright (c) 2010 Nagios Enterprises, LLC.
//  

define("SUBSYSTEM",1);

require_once(dirname(__FILE__).'/../html/config.inc.php');
require_once(dirname(__FILE__).'/../html/includes/utils.inc.php');



doit();


function doit(){
	global $argv;
	
	$args=parse_argv($argv);
	
	$host=doClean(grab_array_var($args,"host"));
	$service=doClean(grab_array_var($args,"service",''));
	$start=grab_array_var($args,"start",'');
	$end = grab_array_var($args, 'end',''); 
	
	if($host=="") print_usage(); 
	
	$perfdata = '/usr/local/nagios/share/perfdata/';
	
	if($service == '') $rrdfile = '_HOST_.rrd';
	else $rrdfile = $service.'.rrd'; 
	
	$path = $perfdata.$host.'/'.$rrdfile; 
	$start = ($start == '') ? '' : " -s $start "; 
	$end = ($end == '') ? '' : " -e $end "; 
	
	$cmd = "/usr/bin/rrdtool fetch $path AVERAGE $start $end";
	
	$bool = exec($cmd, $results); 
	if($bool == 1) print_usage(); 
	
	$data['sets'] = array(); 
	
	foreach($results as $line)
	{
		//$line = fgets($f); 
		//check line syntax, ignore bad data 
		if(strlen(trim($line))<10 || trim($line)=='' ) continue;			
		//echo "should be grabbing a line with data: $line<br />";
		$values = explode(' ', trim($line));
		$time = substr(trim($values[0]), 0,10);
		if(strlen($time<9)) continue; //skip if there's no timestamp, data is bad 
		
		//$times[] = $time; //assign valid time to array   //currently unused 
		
		for($i=1;$i<count($values);$i++)  
		{
			//create comma delineated list for JSON object			 
			if(!isset($data['sets'][$i-1])) $data['sets'][$i-1] = ''; //create new string index if none exists 
			//chop down string a raw float 
			$str = substr(trim($values[$i]), 0,11); 
			if(strstr($str, 'nan')) $data['sets'][$i-1].= 'null, ';  //replace nan's with 0			
			else	$data['sets'][$i-1].=$str.', '; 
			
			//grab data into arrays by column
			//$data['col'.$i][] = substr(trim($values[$i]), 0,11); 			 
		}
							
	} //end of while
	
	
	//print each set of data with a label in between 
	foreach($data['sets'] as $set)
	{
		echo "####################################";
		echo "DATASET"; 
		echo "####################################\n";
		//rrd field data in CSV 
		echo $set; 
		echo "\n\n";
	}
		
}		

function print_usage()
{
		global $argv;
		echo "\nNagios XI RRD To CSV Tool\n";
		echo "Copyright (c) 2011 Nagios Enterprises, LLC\n";
		echo "\n";
		echo "Usage: ".$argv[0]." --host=<host> [--service='<service>'] [--start=<start>] [--end=<end>]\n";
		echo "\n";
		echo "Options:\n";
		echo "  <host>     = Host name [required]\n";
		echo "  <service>  = Service description: Use single quotes if spaces in name\n"; 
		echo "  <start>    = Start time. Defaults to 'today.' Accepts -12h, -3d, or YYYYMMDD format. \n";  
		echo "  <end>      = End time. Defaults to 'today.' Accepts -12h, -3d, or YYYYMMDD format. \n";
		echo "\n";
		
		echo "This utility creates host and service definitions for use in testing.\n";
		exit(1);
}

//function used from PNP 0.4
function doClean($string) {
	$string = preg_replace('/[ :\/\\\\]/', "_", $string);
	return $string;
}

?>
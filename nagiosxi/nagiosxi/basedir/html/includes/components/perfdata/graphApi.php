<?php 

// Perfdata Sub-Component Functions
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: perfdata.php 75 2010-04-01 19:40:08Z egalstad $

ini_set('display_errors','off'); //hide error message so it doesn't break the header declaration 

include_once(dirname(__FILE__).'/../componenthelper.inc.php');


// initialization stuff
pre_init();

// start session
init_session();

// grab GET or POST variables 
grab_request_vars();

// check prereqs
check_prereqs();

// check authentication
check_authentication(false);

grab_request_vars(); 


//GET Vars 
$hostname=doClean(urldecode(grab_request_var("host","")));
$servicename=doClean(urldecode(grab_request_var("service","")));
$source=grab_request_var("source",1);
$view=grab_request_var("view",PNP_VIEW_DEFAULT);
$start=grab_request_var("start","");
$end=grab_request_var("end","");

//vars for rrdtool graph query 
$rrdgraph = "/usr/bin/rrdtool graph";
//$perfDir = '/usr/local/nagios/share/perfdata';
$perfDir = $cfg['component_info']['pnp']['perfdata_dir']; 
$opts = ' --width=500 --height=100 ';
$timeperiod = calculate_graph_timeperiod($view,$start,$end);

//clear cached file stats to make sure we have fresh data 
clearstatcache(); 

//build rrd file location
if($servicename != '') $rrdfile = $perfDir.'/'.$hostname.'/'.$servicename.'.rrd'; 
else $rrdfile = $perfDir.'/'.$hostname.'/_HOST_.rrd';
//stop script is rrd doesn't exist 
if(!file_exists($rrdfile)) die('No performance data available'); 
$xmlfile = str_replace('rrd', 'xml', $rrdfile); 
//fetch template 
$templatestring = fetch_graph_template($hostname, $servicename, $rrdfile, $xmlfile,$source);


///////////////////////////////////////////////////////////////////////////////
//generate graph image
header("Content-type: image/png");
$cmdString = $rrdgraph . ' - ' . $opts . $timeperiod . $templatestring;
//execute command and direct output to browser
passthru($cmdString,$bool);

//ERROR LOGGING: log command being run on error  /usr/local/nagios/var/graphapi.log
if($bool > 0)  
{
        $f = fopen('/usr/local/nagios/var/graphapi.log', 'a');
        $errString = "GRAPH ERROR: ".date('c',time())."\n".$cmdString."\n\n";
        fwrite($f, $errString);
        fclose($f);
}


/////////////////////
//checks if template exists
//returns defined template string on success, default template string on failure 
function fetch_graph_template($hostname,$servicedesc,$rrdfile,$xmlfile,$source)
{
	//TODO
	//$source is the XML index for templates and datasources 

	//necessary template vars 
	//force indexes to start at 1 for PNP template compatibility
	$RRDFILE = array('');
	$TEMPLATE = array('');
	$DS = array(''); 
	$NAME = array('');
	$LABEL = array('');
	$UNIT = array('');
	$ACT = array('');
	$MIN = array('');
	$MAX = array(''); 
	$WARN = array('');
	$CRIT = array(''); 
	$WARN_MIN = array('');
	$WARN_MAX = array('');
	$CRIT_MIN = array('');
	$CRIT_MAX = array(''); 

	//load xml file for data
	$xmlDat = simplexml_load_file($xmlfile); 
	//load xml into arrays 
	$RRDFILE = array_merge($RRDFILE,$xmlDat->xpath('/NAGIOS/DATASOURCE/RRDFILE'));
	$TEMPLATE = array_merge($TEMPLATE,$xmlDat->xpath('/NAGIOS/DATASOURCE/TEMPLATE'));
	$DS = array_merge($DS,$xmlDat->xpath('/NAGIOS/DATASOURCE/DS'));
	$NAME = array_merge($NAME,$xmlDat->xpath('/NAGIOS/DATASOURCE/NAME'));
	$LABEL = array_merge($LABEL,$xmlDat->xpath('/NAGIOS/DATASOURCE/LABEL'));
	$UNIT = array_merge($UNIT,$xmlDat->xpath('/NAGIOS/DATASOURCE/UNIT'));
	$ACT = array_merge($ACT,$xmlDat->xpath('/NAGIOS/DATASOURCE/ACT'));
	$MIN = array_merge($MIN,$xmlDat->xpath('/NAGIOS/DATASOURCE/MIN'));
	$MAX = array_merge($MAX,$xmlDat->xpath('/NAGIOS/DATASOURCE/MAX')); 
	$WARN = array_merge($WARN,$xmlDat->xpath('/NAGIOS/DATASOURCE/WARN'));
	$CRIT = array_merge($CRIT,$xmlDat->xpath('/NAGIOS/DATASOURCE/CRIT')); 
	$NAGIOS_TIMET = $xmlDat->xpath('/NAGIOS/NAGIOS_TIMET');
	$NAGIOS_LASTHOSTDOWN = $xmlDat->xpath('/NAGIOS/NAGIOS_LASTHOSTDOWN');
	$NAGIOS_TIMET = $NAGIOS_TIMET[0];
	$NAGIOS_LASTHOSTDOWN = isset($NAGIOS_LASTHOSTDOWN[0]) ? $NAGIOS_LASTHOSTDOWN[0] : '';

	
	//refactor DS array  --> moved for all templates on 11/15/2011, all DS arrays need to be refactored. 
	$ds = array();
	foreach($DS as $D) {
		if("$D" !='') $ds["$D"] = "$D";
	}
	$DS = $ds;
	$lower = ' ';
	
	//check if template exists
	//usr/local/nagios/share/pnp/templates
	//usr/local/nagios/share/pnp/templates.dist
	//usr/local/nagios/share/pnp/templates.special
	if(file_exists('/usr/local/nagios/share/pnp/templates/'.$TEMPLATE[$source].'.php') ) 
		$template = '/usr/local/nagios/share/pnp/templates/'.$TEMPLATE[$source].'.php';
	elseif(file_exists('/usr/local/nagios/share/pnp/templates.dist/'.$TEMPLATE[$source].'.php'))
		$template = '/usr/local/nagios/share/pnp/templates.dist/'.$TEMPLATE[$source].'.php';
	elseif(file_exists('/usr/local/nagios/share/pnp/templates.special/'.$TEMPLATE[$source].'.php'))	  
		$template = '/usr/local/nagios/share/pnp/templates.special/'.$TEMPLATE[$source].'.php';
	else {
		//default template 
		$template = '/usr/local/nagios/share/pnp/templates.dist/default.php'; 

	}	
	//call appropriate template include 
	include($template); 
	
	//build template command string
	//what to do about datasource label????
	$optString = isset($opt[$source]) ? $opt[$source] : ''; 
	$defString = isset($def[$source]) ? $def[$source] : ''; 
	//assemble full string 
	$templateString = $optString.' '.$defString;
	return $templateString; 


}

//function used from PNP 0.4
function doClean($string) {
	$string = preg_replace('/[ :\/\\\\]/', "_", $string);
	$string = rawurldecode($string);
	return $string;
}

//calculates graph duration and returns the string commmand value for rrdtool 
function calculate_graph_timeperiod($view,$start,$end)
{
	//time selections from frontend
	$pnpviews = array('-4h', '-24h', '-7d', '-30d','-365d'); 
	//timestamp conversions for PNP views 
	$pnpstamps = array((60*60*4), (60*60*24), (60*60*24*7), (60*60*24*30), (60*60*24*365) ); 
	
	//calculate all possible combos and return start/end string 	 
	//default PNP views, no start/end defined   
	if($view !='' && $start == '' && $end == '') $timeperiod = " --start={$pnpviews[$view]} "; 
	//specific date
	elseif($start != '' && $end != '') $timeperiod = " --start=$start --end=$end ";
	//end/view combo
	elseif($view != '' && $end !='' && $start == '') 
	{
		$s = $end - $pnpstamps[$view]; //calculate starting timestamp
		$timeperiod = " --start={$s} --end={$end} ";  
	}	
	
	else $timeperiod = '';
	
	return $timeperiod;

}




?>
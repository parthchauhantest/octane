<?php
// Auto-discovery Component
//
// Copyright (c) 2010 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: autodiscovery.inc.php 1096 2013-02-04 21:51:43Z mguthrie $

require_once(dirname(__FILE__).'/../componenthelper.inc.php');


// respect the name
$autodiscovery_component_name="autodiscovery";

// run the initialization function
autodiscovery_component_init();

////////////////////////////////////////////////////////////////////////
// COMPONENT INIT FUNCTIONS
////////////////////////////////////////////////////////////////////////

function autodiscovery_component_init(){
	global $autodiscovery_component_name;
	
	$versionok=autodiscovery_component_checkversion();
	
	$desc="";
	if(!$versionok)
		$desc="<br><b>".gettext("Error: This component requires Nagios XI 2011R1 or later.")."</b>";

	$installok=autodiscovery_component_checkinstall($installed,$prereqs,$missing_components);
	if(!$installok)
		$desc.="<br><b>".gettext("Error: Required setup has not been completed.")."  
		<a href='".get_base_url()."/includes/components/autodiscovery/'>".gettext("Learn more")."</a>.</b>";
			
	$args=array(

		// need a name
		COMPONENT_NAME => $autodiscovery_component_name,
		
		// informative information
		COMPONENT_AUTHOR => "Nagios Enterprises, LLC",
		COMPONENT_DESCRIPTION => gettext("Provides device and service auto-discovery. ").$desc,
		COMPONENT_TITLE => "Auto-Discovery",
		COMPONENT_VERSION => '2.03',
		// configuration function (optional)
		//COMPONENT_CONFIGFUNCTION => "autodiscovery_component_config_func",
		);
		
	register_component($autodiscovery_component_name,$args);
	
	if($versionok){
		// add a menu link
		register_callback(CALLBACK_MENUS_INITIALIZED,'autodiscovery_component_addmenu');
		// add a configure screen link
		register_callback(CALLBACK_CONFIG_SPLASH_SCREEN,'autodiscovery_component_addsplash');
		}
	}
	

///////////////////////////////////////////////////////////////////////////////////////////
// JOB FUNCTIONS
///////////////////////////////////////////////////////////////////////////////////////////

function autodiscovery_component_getjobs(){

	$jobs=array();

	$jobs_s=get_option("autodiscovery_jobs");
	if($jobs_s=="" || $jobs_s==null)
		autodiscovery_component_savejobs($jobs);
	else
		$jobs=unserialize($jobs_s);
		
	return $jobs;
	}
	
function autodiscovery_component_savejobs($jobs){
	set_option("autodiscovery_jobs",serialize($jobs));
	}
	
function autodiscovery_component_addjob($jobid,$job){	

	$jobs=autodiscovery_component_getjobs();
	$jobs[$jobid]=$job;
	autodiscovery_component_savejobs($jobs);
	}

function autodiscovery_component_get_jobid($jobid){
	$jobs=autodiscovery_component_getjobs();
	if(array_key_exists($jobid,$jobs))
		return $jobs[$jobid];
	return null;
	}
	
function autodiscovery_component_delete_jobid($jobid){
	$jobs=autodiscovery_component_getjobs();
	if(array_key_exists($jobid,$jobs))
		unset($jobs[$jobid]);
	autodiscovery_component_savejobs($jobs);
	
	// update cron
	autodiscovery_component_delete_cron($jobid);	
	}
	
	
	
function autodiscovery_component_update_cron($id){

	$croncmd=autodiscovery_component_get_cron_cmdline($id);
	$crontimes=autodiscovery_component_get_cron_times($id);
	
	$cronline=sprintf("%s\t%s > /dev/null 2>&1\n",$crontimes,$croncmd);
	$tmpfile=get_tmp_dir()."/scheduledreport.".$id;
	file_put_contents($tmpfile,$cronline);
	
	$cmd="crontab -l | grep -v '".escapeshellcmd($croncmd)."' | cat - ".$tmpfile." | crontab - ; rm -f ".$tmpfile;
	//echo "<BR>UPDATE CMD: $cmd<BR>";
	exec($cmd);
	//exit();
	}
	
function autodiscovery_component_delete_cron($id){

	//$croncmd=autodiscovery_component_get_cron_cmdline($id);
	$croncmd="/usr/local/nagiosxi/html/includes/components/autodiscovery/jobs/".$id;
	$cmd="crontab -l | grep -v '".escapeshellcmd($croncmd)."' | crontab -";
	//echo "<BR>DELETE CMD: $cmd<BR>";
	exec($cmd);
	//exit();
	}
	
function autodiscovery_component_get_cron_cmdline($id){
	$cmdline=autodiscovery_component_get_cmdline($id);
	$cmd=$cmdline;
	return $cmd;
	}
	
	
function autodiscovery_component_prep_job_files($jobid){

	$base_dir=get_component_dir_base("autodiscovery");
	$jobs_dir=$base_dir."/jobs/";
	
	$watch_file=$jobs_dir.$jobid.".watch";
	$out_file=$jobs_dir.$jobid.".out";
	$xml_file=$jobs_dir.$jobid.".xml";

	// make sure permissions are correct on existing file (if we're rerunning the job) 
	chmod($watch_file,0660);
	chmod($out_file,0660);
	chmod($xml_file,0660);
	
	// delete existing xml file (if we're rerunning the job) 
	unlink($xml_file);
	}	

function autodiscovery_component_get_cmdline($jobid){


	$base_dir=get_component_dir_base("autodiscovery");
	$script_dir=$base_dir."/scripts/";
	$jobs_dir=$base_dir."/jobs/";
	
	$watch_file=$jobs_dir.$jobid.".watch";
	$out_file=$jobs_dir.$jobid.".out";
	$xml_file=$jobs_dir.$jobid.".xml";
	
	$jarr=autodiscovery_component_get_jobid($jobid);

	$address=grab_array_var($jarr,"address","127.0.0.1");
	$exclude_address=grab_array_var($jarr,"exclude_address");
	$os_detection=grab_array_var($jarr,"os_detection","off");
	$topology_detection=grab_array_var($jarr,"topology_detection","off");	
	
	$osd="";
	if($os_detection=="on")
		$osd="--detectos=1";
	$topod="";
	if($topology_detection=="on")
		$topod="--detecttopo=1";

	$cmd="rm -f ".$xml_file."; touch ".$watch_file."; /usr/bin/php ".$script_dir."autodiscover_new.php --addresses=\"".escapeshellcmd($address)."\"  --exclude=\"".escapeshellcmd($exclude_address)."\" --output=".$xml_file." --watch=".$watch_file." --onlynew=0 --debug=1 ".$osd." ".$topod." > ".$out_file." 2>&1 & echo $!";
	
	//$cmd="touch ".$watch_file."; /usr/bin/php ".$script_dir."autodiscover_new.php --addresses=\"".escapeshellcmd($address)."\"  --exclude=\"".escapeshellcmd($exclude_address)."\" --output=".$xml_file." --watch=".$watch_file." --onlynew=0 --debug=1 ".$osd." ".$topod." > ".$out_file." 2>&1 & echo $!";
	
	return $cmd;
	}	
	
function autodiscovery_component_get_cron_times($jobid){

	$times="";
	
	$sj=autodiscovery_component_get_jobid($jobid);
	if($sj==null)
		return $times;
		
	$frequency=grab_array_var($sj,"frequency","");

	$sched=grab_array_var($sj,"schedule",array());
	$hour=grab_array_var($sched,"hour",0);
	$minute=grab_array_var($sched,"minute",0);
	$ampm=grab_array_var($sched,"ampm","AM");
	$dayofweek=grab_array_var($sched,"dayofweek",0);
	$dayofmonth=grab_array_var($sched,"dayofmonth",1);
	
	$h=intval($hour);
	$m=intval($minute);
	if($ampm=="PM")
		$h+=12;
	if($frequency=="Monthly")
		$dom=$dayofmonth;
	else
		$dom="*";
	if($frequency=="Weekly")
		$dow=$dayofweek;
	else
		$dow="*";
		
	$times=sprintf("%d %d %s * %s",$m,$h,$dom,$dow);
	
	echo "CRON TIMES: $times<BR>";
	//exit();
		
	return $times;
	}
	
	
///////////////////////////////////////////////////////////////////////////////////////////
// MISC FUNCTIONS
///////////////////////////////////////////////////////////////////////////////////////////

function autodiscovery_component_checkversion(){

	if(!function_exists('get_product_release'))
		return false;
	//requires greater than 2011R1
	if(get_product_release()<200)
		return false;

	return true;
	}
	
function autodiscovery_component_checkinstall(&$installed,&$prereqs,&$missing_components){	
	global $autodiscovery_component_name;
	
	$installed=true;
	$prereqs=true;
	$missing_components="";
	
	$base_dir=get_component_dir_base($autodiscovery_component_name);
	
	// check for setup marker
	if(!file_exists($base_dir."/setup.done"))
		$installed=false;
		
	// make sure pre-reqs are installed
	if(!file_exists("/usr/sbin/fping")){
		$missing_components.="<li><b>fping</b> (/usr/sbin/fping)</li>";
		$prereqs=false;
		}
	if(!file_exists("/bin/traceroute")){
		$missing_components.="<li><b>traceroute</b> (/bin/traceroute)</li>";
		$prereqs=false;
		}
	if(!file_exists("/usr/bin/nmap")){
		$missing_components.="<li><b>nmap</b> (/usr/bin/nmap)</li>";
		$prereqs=false;
		}
	
	if($installed==false || $prereqs==false)
		return false;
	return true;
	}
	
function autodiscovery_component_addmenu($arg=null){
	global $autodiscovery_component_name;
	
	$mi=find_menu_item(MENU_CONFIGURE,"menu-configure-monitoringwizard","id");
	if($mi==null)
		return;
		
	$order=grab_array_var($mi,"order","");
	if($order=="")
		return;
		
	$neworder=$order+0.1;

	add_menu_item(MENU_CONFIGURE,array(
		"type" => "link",
		"title" => gettext("Auto-Discovery Wizard"),
		"id" => "menu-configure-autodiscovery",
		"order" => $neworder,
		"opts" => array(
			"href" => get_base_url().'includes/components/autodiscovery/',
			)
		));
	
	}

function autodiscovery_component_addsplash($arg=null){

	$url=get_base_url().'includes/components/autodiscovery/';
	$img_url=$url."images/";

	$output='';
	
	$output.='
	<br clear="all">
	<p>
	<a href="'.$url.'"><img src="'.$img_url.'autodiscovery.png" style="float: left; margin-right: 10px;"> '.
	gettext('Run the Auto-Discovery Wizard').'</a><br>
	'.gettext('Auto-discover new devices and services to monitor.').'
	</p>
	';

	echo $output;
	}

function autodiscovery_component_parse_job_data($jobid="",&$new_hosts=0,&$total_hosts=0){

	$services=array();

	$base_dir=get_component_dir_base("autodiscovery");
	$output_file=$base_dir."/jobs/".$jobid.".xml";
	
	$total_hosts=0;
	$new_hosts=0;
	$xml=@simplexml_load_file($output_file);
	if($xml){
	
		foreach($xml->device as $d){

			$status=strval($d->status);
			$address=strval($d->address);
			$fqdns=strval($d->fqdns);
			
			$total_hosts++;
			if($status=="new")
				$new_hosts++;
				
			$services[$address]=array(
				"address" => $address,
				"fqdns" => $fqdns,
				"type" => "Unknown",
				"os" => "",
				"status" => $status,
				"ports" => array(),
				);
				
			// get ports
			foreach($d->ports->port as $p){
				
				$protocol=strval($p->protocol);
				$port=strval($p->port);
				$state=strval($p->state);
				
				if($state!="open")
					continue;
			
				$services[$address]["ports"][]=array(
					"protocol" => $protocol,
					"port" => $port,
					"service" => getservbyport($port,strtolower($protocol)),
					);
				}

			// get operating system and device type
			foreach($d->operatingsystems->osinfo as $o){
			
				$ostype=strval($o->ostype);
				$osvendor=strval($o->osvendor);
				$osfamily=strval($o->osfamily);
				$osgen=strval($o->osgen);
				
				$services[$address]["ostype"]=$osvendor;
				$services[$address]["osvendor"]=$osvendor;
				$services[$address]["osfamily"]=$osfamily;
				$services[$address]["osgen"]=$osgen;

				autodiscovery_component_get_device_info($ostype,$osvendor,$osfamily,$osgen,$os,$type);
				
				$services[$address]["os"]=$os;
				$services[$address]["type"]=$type;			
				
				}
				
			}
		}

	
	return $services;
	}
	
// get operating system and device type
function autodiscovery_component_get_device_info($ostype,$osvendor,$osfamily,$osgen,&$os,&$type){

	trim($ostype);
	trim($osvendor);
	trim($osfamily);
	trim($osgen);

	// sperating system
	$os=trim($osfamily." ".$osgen);
						
	// guess device type
	switch($os){
		case "Windows XP":
		case "Windows 7":
			$type="Windows Workstation";
			break;
		case "Windows 2000":
		case "Windows 2003":
		case "Windows 2008":
			$type="Windows Server";
			break;
		default:
			// non-exact matches
			if(strstr($os,"Windows")!==FALSE)
				$type="Windows Server";
			else if(strstr($os,"Linux")!==FALSE)
				$type="Linux Server";
			else if(strstr($os,"Solaris")!==FALSE)
				$type="UNIX Server";
			else if(strstr($os,"AIX")!==FALSE)
				$type="UNIX Server";
			else if(strstr($os,"HP-UX")!==FALSE)
				$type="UNIX Server";
			else{
				if($osvendor!="" && $ostype!=""){
					$type=trim(ucwords($osvendor." ".$ostype));
					}
				else{
					if(strstr($os,"ArubaOS")!==FALSE)
						$type="Network Device";
					else if(strstr($os,"RouterOS")!==FALSE)
						$type="Network Device";
					else if(strstr($os,"IOS")!==FALSE)
						$type="Cisco Network Device";
					else
						$type="Other";
					}
				}
			break;
		}
		
	trim($os);
	trim($type);

	if($os=="")
		$os="Unknown";
	if($type=="")
		$type="Unknown";

	}
?>
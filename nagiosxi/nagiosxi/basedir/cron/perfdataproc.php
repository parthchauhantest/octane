#!/usr/bin/php -q
<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: perfdataproc.php 262 2010-08-12 21:22:20Z egalstad $

define("SUBSYSTEM",1);
//define("BACKEND",1);

require_once(dirname(__FILE__).'/../html/config.inc.php');
require_once(dirname(__FILE__).'/../html/includes/utils.inc.php');

$max_time=55;
$sleep_time=10; //increased from 5 to 10.  PNP's default dirscan is 15. -MG 3/21/2012

$nsca_target_hosts=array();
$nrdp_target_hosts=array();
$enable_nsca=0;
$enable_nrdp=0;
$enable_outbound=0;


init_dataprocessor();
process_data();



function init_dataprocessor(){
	global $nsca_target_hosts;
	global $nrdp_target_hosts;
	global $enable_nsca;
	global $enable_nrdp;
	global $enable_outbound;
	global $outbound_filter_mode;
	global $outbound_host_filters;

	// make database connections
	$dbok=db_connect_all();
	if($dbok==false){
		echo "ERROR CONNECTING TO DATABASES!\n";
		exit();
		}
		
	//$th=array("192.168.5.88","192.168.5.89");
	//set_option("nsca_target_hosts",serialize($th));
	//set_option("enable_nsca_output",1);
	
	$thr=get_option("nsca_target_hosts");
	if($thr!="")
		$nsca_target_hosts=unserialize($thr);
		
	$thr=get_option("nrdp_target_hosts");
	if($thr!="")
		$nrdp_target_hosts=unserialize($thr);

	$enable_nsca=get_option("enable_nsca_output");
	$enable_nrdp=get_option("enable_nrdp_output");
	$enable_outbound=get_option("enable_outbound_data_transfer");
	$outbound_filter_mode=get_option("outbound_data_filter_mode");
	$outbound_host_filters=get_option("outbound_data_host_name_filters");

	if(!$enable_outbound)
		echo "Outbound data DISABLED ".date('r')."\n";
	
	return;
	}

function process_data(){
	global $max_time;
	global $sleep_time;
	global $enable_outbound; 
	global $cfg;

	$start_time=time();
	$n=0;
	$t=0;
	
	//cfg varialbes for bulk filemove 
	$dest=grab_array_var($cfg,'perfdata_spool',"/usr/local/nagios/var/spool/perfdata/");
	$xidpe=grab_array_var($cfg,'xidpe_dir',"/usr/local/nagios/var/spool/xidpe/"); 

	while(1){
	
		// bail if if we've been here too long
		$now=time();
		if(($now-$start_time)>$max_time)
			break;
	
		//only process files if outbound transfers are enabled
		if($enable_outbound) {
		$n=0;
		$n+=process_data_files();
		$t+=$n;
		}
		else { //simple bulk file move so pnp picks up perfdata
			$cmd = 'mv '.$xidpe.'* '.$dest; 
			exec($cmd);			
		}
	
		// sleep for a bit if we didn't do anything...
		if($n==0){
			update_sysstat();
			//echo ".";
			sleep($sleep_time);
			}
		}
		
	update_sysstat();
	echo "\n";
	echo "DONE. Processed $t files.\n";
	}
	
function process_data_files(){
	global $cfg;
	
	$dir=grab_array_var($cfg,'xidpe_dir',"/usr/local/nagios/var/spool/xidpe/");

	$n=0;
	
	/*
	if($dh=opendir($dir)){
		echo "READING DIR: $dir\n";
        while(($file=readdir($dh))!==false){
			if(filetype($dir.$file)!="file")
				continue;
			echo "Processing file: $file\n";
			process_data_file($dir,$file);
			$n++;
			}
        closedir($dh);
		}
	else
		echo "FAILED TO OPEN DIR! $dir\n";
	*/
	
	$cmd="ls -tr1 $dir";
	exec($cmd,$lines);
	foreach($lines as $file){
		if(filetype($dir.$file)!="file")
			continue;
		//echo "Processing file: $file\n";
		process_data_file($dir,$file);
		$n++;
		}
	
	return $n;
	}
	
function process_data_file($d,$f){

	$parts=explode(".",$f);
	
	$filetype=$parts[1];
	switch($filetype){
		// performance data file
		case "perfdata":
			$timestamp=$parts[0];
			$type=$parts[2];
			process_perfdata_file($type,$timestamp,$d,$f);
			break;
		default:
			break;
		}

	// remove the file
	unlink($d.$f);
	}
	
function process_perfdata_file($type,$timestamp,$d,$f){
	global $cfg;
	global $nsca_target_hosts;
	global $nrdp_target_hosts;
	global $enable_nsca;
	global $enable_nrdp;
	global $enable_outbound;

	$use_nsca=false;
	$use_nrdp=false;
	
	echo "\n";
	echo "Processing perfdata file '".$d.$f."'\n";
	
	// copy the file to the perfdata spool dir, so pnp can graph the data
	$dest=grab_array_var($cfg,'perfdata_spool',"/usr/local/nagios/var/spool/perfdata/");
	//$dest="/usr/local/nagios/var/spool/perfdata/";
	$newf=$type."-perfdata.".$timestamp;
	echo "Copying perfdata file to ".$dest.$newf."\n";
	copy($d.$f,$dest.$newf);
	
	if($enable_outbound==false){
		echo "Outbound data DISABLED - Data will not be send via NSCA or NRDP.\n";
		return;
		}

	// parse data file
	$data=parse_perfdata_file($d.$f);
	//echo "DATA:\n";
	//print_r($data);
	
	if($enable_nsca==1 && count($nsca_target_hosts)>0)
		$use_nsca=true;
	
	// pass it to nsca
	if($use_nsca==true){
	
		echo "Sending passive check data to NSCA server(s)...\n";
		
		$total_checks=0;
	
		// create the file
		$tmpfname=tempnam("/tmp","NSCAOUT");
		$fh=fopen($tmpfname,"w");
		foreach($data as $did => $darr){
		
			$l="";

			$datatype=grab_array_var($darr,"DATATYPE");
			if($datatype=="SERVICEPERFDATA"){
			
				$output=grab_array_var($darr,"SERVICEOUTPUT","SERVICEOUTPUT macro not found in perfdata - no output available.");
                $output.='|'.grab_array_var($darr,"SERVICEPERFDATA","");
				$hostname=grab_array_var($darr,"HOSTNAME","[NOHOSTNAME]");
				$servicename=grab_array_var($darr,"SERVICEDESC","[NOSERVICEDESCRIPTION]");
				$stateid=grab_array_var($darr,"STATEID",-2);

				$l=$hostname."\t".$servicename."\t".$stateid."\t".$output."\n";

				// check filter
				$fres=filter_outbound_data($hostname,$servicename);
				}
			else{
				$output=grab_array_var($darr,"HOSTOUTPUT","HOSTOUTPUT macro not found in perfdata - no output available.");
                $output.='|'.grab_array_var($darr,"HOSTPERFDATA","");
				$hostname=grab_array_var($darr,"HOSTNAME","[NOHOSTNAME]");
				$stateid=grab_array_var($darr,"STATEID",-2);

				$l=$hostname."\t".$stateid."\t".$output."\n";

				// check filter
				$fres=filter_outbound_data($hostname,"");
				}

			// did the host/service name pass the filters?
			if($fres==true){
				//echo "Filter passed!\n";
				if($l!=""){
					//echo $l;
					fputs($fh,$l);
					$total_checks++;
					}
				}
			else{
				//echo "FILTER FAILED - skipping\n";
				}
			}
		fclose($fh);
		
		// send it!
		if($total_checks>0){
			foreach($nsca_target_hosts as $tharr){
			
				$address=trim(grab_array_var($tharr,"address"));
				
				if($address=="")
					continue;
				
				echo "  Sending to NSCA target host: $address\n";
				$cmdline="/bin/cat $tmpfname | /usr/local/nagios/libexec/send_nsca -H ".$address." -to 10 -c /usr/local/nagios/etc/send_nsca-".$address.".cfg";
				echo "    CMDLINE: $cmdline\n";
				system($cmdline);
				}
			}
		else{
			echo "No checks to send via NSCA\n";
			}
		
		unlink($tmpfname);
		}
	else{
		//echo "NSCA disabled or not configured - skipping.\n";
		}
	
	if($enable_nrdp==1 && count($nrdp_target_hosts)>0)
		$use_nrdp=true;
	
	// pass it to nrdp
	if($use_nrdp==true){
	
		echo "Sending passive check data to NRDP server(s)...\n";
		
		$total_checks=0;
	
		// create the file
		$tmpfname=tempnam("/tmp","NRDPOUT");
		$fh=fopen($tmpfname,"w");
		foreach($data as $did => $darr){
		
			$l="";

			$datatype=grab_array_var($darr,"DATATYPE");
			if($datatype=="SERVICEPERFDATA"){
				$output=grab_array_var($darr,"SERVICEOUTPUT");
                $output.='|'.grab_array_var($darr,"SERVICEPERFDATA","");
				$hostname=grab_array_var($darr,"HOSTNAME");
				$servicename=grab_array_var($darr,"SERVICEDESC");
				$stateid=grab_array_var($darr,"STATEID");

				$l=$hostname."\t".$servicename."\t".$stateid."\t".$output."\n";

				// check filter
				$fres=filter_outbound_data($hostname,$servicename);
				}
			else{
				$output=grab_array_var($darr,"HOSTOUTPUT");
                $output.='|'.grab_array_var($darr,"HOSTPERFDATA","");
				$hostname=grab_array_var($darr,"HOSTNAME");
				$stateid=grab_array_var($darr,"STATEID");

				$l=$hostname."\t".$stateid."\t".$output."\n";

				// check filter
				$fres=filter_outbound_data($hostname,"");
				}

			// did the host/service name pass the filters?
			if($fres==true){
				//echo "Filter passed!\n";
				if($l!=""){
					//echo $l;
					fputs($fh,$l);
					$total_checks++;
					}
				}
			else{
				//echo "FILTER FAILED - skipping\n";
				}
			}
		fclose($fh);
		
		// send it!
		if($total_checks>0){
			foreach($nrdp_target_hosts as $tharr){

				$address=trim(grab_array_var($tharr,"address"));
				if($address=="")
					continue;

				$method=trim(grab_array_var($tharr,"method","http"));			
				$token=grab_array_var($tharr,"token");
				
				echo "  Sending to NRDP target host: $address\n";
				$cmdline="/bin/cat $tmpfname | php /usr/local/nrdp/clients/send_nrdp.php --url=".$method."://".$address."/nrdp/ --token=".$token." --usestdin";
				echo "    CMDLINE: $cmdline\n";
				
				system($cmdline);
				}
			}
		
		unlink($tmpfname);
		}
	else{
		//echo "NRDP disabled or not configured - skipping.\n";
		}
	}
	
function filter_outbound_data($hn,$sn){
	global $outbound_filter_mode;
	global $outbound_host_filters;
	
	/////////////////////////////////////////////////////
	// HOST FILTERS
	/////////////////////////////////////////////////////
	if($outbound_host_filters==""){
		// always let data through if filters aren't defined
		return true;
		/*
		if($outbound_filter_mode=="exclude")
			return true;
		else
			return false;
		*/
		}
	$filters=explode("\r\n",$outbound_host_filters);
	foreach($filters as $filterraw){
		$filter=trim($filterraw);
		if($filter=="")
			continue;
		//echo "MATCHING HOSTNAME '$hn' AGAINST FILTER '$filter'\n";
		$res=preg_match($filter,$hn);
		if($res){
			if($outbound_filter_mode=="exclude")
				return false;
			else
				return true;
			}
		else{
			continue; // we might match a future filter
			}
		}
		
	// we reached the end - is this good or bad?
	if($outbound_filter_mode=="exclude")
		return true;
	else
		return false;
	}
	
function parse_perfdata_file($f){

	$discard_soft_states=false;

	$d=array();

	$contents=file_get_contents($f);
	$lines=explode("\n",$contents);
	
	foreach($lines as $line){
		$elements=explode("\t",$line);
		$e=array();
		foreach($elements as $element){
			$parts=explode("::",$element);
			$var=$parts[0];
			if($var=="")
				continue;
			unset($parts[0]);
			$val=implode("::",$parts);
			$e[$var]=$val;
			}
			
		// do some checks
		$datatype=grab_array_var($e,"DATATYPE");
		if($datatype=="SERVICEPERFDATA"){
		
			$state=grab_array_var($e,"SERVICESTATE");
			$e["STATEID"]=state_str_to_id($state);

			$statetype=grab_array_var($e,"SERVICESTATETYPE");
			if($discard_soft_states==true && $statetype=="SOFT")
				continue;
			}
		else if($datatype=="HOSTPERFDATA"){

			$state=grab_array_var($e,"HOSTSTATE");
			$e["STATEID"]=state_str_to_id($state);

			$statetype=grab_array_var($e,"HOSTSTATETYPE");
			if($discard_soft_states==true && $statetype=="SOFT")
				continue;
			}
		else
			continue;
			
		if(count($e)>0)
			$d[]=$e;
		}
		
	return $d;
	}
	
function state_str_to_id($str){
	$sid=-1;
	switch($str){
		case "UP":
			$sid=0;
			break;
		case "DOWN":
			$sid=1;
			break;
		case "UNREACHABLE":
			$sid=2;
			break;
		case "OK":
			$sid=0;
			break;
		case "WARNING":
			$sid=1;
			break;
		case "UNKNOWN":
			$sid=3;
			break;
		case "CRITICAL":
			$sid=2;
			break;
		default:
			echo "NO MATCH FOR STATE '$str'\n";
			break;
		}
	return $sid;
	}

	
function update_sysstat(){
	// record our run in sysstat table
	$arr=array(
		"last_check" => time(),
		);
	$sdata=serialize($arr);
	update_systat_value("perfdataprocessor",$sdata);
	}

?>
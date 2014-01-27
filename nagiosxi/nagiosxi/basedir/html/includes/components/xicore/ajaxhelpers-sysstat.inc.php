<?php
// XI Core Ajax Helper Functions
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: ajaxhelpers-sysstat.inc.php 1122 2012-04-16 14:16:09Z mguthrie $

include_once(dirname(__FILE__).'/../componenthelper.inc.php');
	

////////////////////////////////////////////////////////////////////////
// SYSSTAT AJAX FUNCTIONS
////////////////////////////////////////////////////////////////////////



function xicore_ajax_get_stat_bar_html($rawval,$label,$displayval,$mult=20,$maxval=200,$level2=10,$level3=50){

	$val=(floatval($rawval) * $mult);
	if($val>$maxval)
		$val=$maxval;
	if($val<=1)
		$val=1;		
	else if($val<=0)
		$val=0;
	
	$spanval="0,$maxval,$val";
	
	if($val>$level3)
		$spanval="<div style='height: 10px; width: ".$val."px; background-color:  ".COMMONCOLOR_RED.";'>&nbsp;</div>";
	else if($val>$level2)
		$spanval="<div style='height: 10px; width: ".$val."px; background-color:  ".COMMONCOLOR_YELLOW.";'>&nbsp;</div>";
	else
		$spanval="<div style='height: 10px; width: ".$val."px; background-color:  ".COMMONCOLOR_GREEN.";'>&nbsp;</div>";
	
	$barclass="";
	
	$output='<tr><td><span class="sysstat_stat_subtitle">'.$label.'</span></td><td>'.$displayval.'</td><td><span class="statbar'.$barclass.'">'.$spanval.'</span></td></tr>';
	
	return $output;
	}

	
function xicore_ajax_get_server_stats_html($args=null){
	global $lstr;

	if(is_admin()==false)
		return $lstr['NotAuthorizedErrorText'];


	// get sysstat data
	//$xml=get_backend_xml_sysstat_data();
	$xml=get_xml_sysstat_data();
	//print_r($xml);
	
	//echo "ARGS2\n";
	//print_r($args);
	
	$id=random_string(6);
	
	$output='';
	$output.='<div class="infotable_title">Server Statistics</div>';
	if($xml==null){
		$output.="No data";
		}
	else{
		$output.='
		<table class="infotable">
		<thead>
		<tr><th><div style="width: 75px;">Metric</div></th><th><div style="width: 60px;">Value</div></th><th><div style="width: 105px;"></div></th></tr>
		</thead>
		<tbody>
		';
        // added to account for multiple processors -SW
		$getprocessorcount = "cat /proc/cpuinfo | grep processor | wc -l";
        $processor_count = exec($getprocessorcount);
		$output.='<tr><td><span class="sysstat_stat_title">Load</span></td></tr>';
		// load 1
		$output.=xicore_ajax_get_stat_bar_html($xml->load->load1,"1-min",$xml->load->load1,10,100,25*$processor_count,75*$processor_count);
		// load 5
		$output.=xicore_ajax_get_stat_bar_html($xml->load->load5,"5-min",$xml->load->load5,10,100,25*$processor_count,75*$processor_count);
		// load 15
		$output.=xicore_ajax_get_stat_bar_html($xml->load->load15,"15-min",$xml->load->load15,10,100,25*$processor_count,75*$processor_count);
		
		
		$output.='<tr><td><span class="sysstat_stat_title">CPU Stats</span></td></tr>';
		$output.=xicore_ajax_get_stat_bar_html($xml->iostat->user,"User",$xml->iostat->user."%",1,100,75,95);
		$output.=xicore_ajax_get_stat_bar_html($xml->iostat->nice,"Nice",$xml->iostat->nice."%",1,100,75,95);
		$output.=xicore_ajax_get_stat_bar_html($xml->iostat->system,"System",$xml->iostat->system."%",1,100,75,95);
		$output.=xicore_ajax_get_stat_bar_html($xml->iostat->iowait,"I/O Wait",$xml->iostat->iowait."%",1,100,5,15);
		$output.=xicore_ajax_get_stat_bar_html($xml->iostat->steal,"Steal",$xml->iostat->steal."%",1,100,5,15);
		$output.=xicore_ajax_get_stat_bar_html($xml->iostat->idle,"Idle",$xml->iostat->idle."%",1,100,100,100);


		$output.='<tr><td><span class="sysstat_stat_title">Memory</span></td></tr>';
		$total=intval($xml->memory->total);
		$output.='<tr><td><span class="sysstat_stat_subtitle">Total</div></td><td>'.$xml->memory->total.' MB</td></tr>';
		$t=intval($xml->memory->used);
		$output.=xicore_ajax_get_stat_bar_html($xml->memory->used,"Used",get_formatted_number($xml->memory->used,0)." MB",(1/$total)*100,100,98,99);
		$t=intval($xml->memory->free);
		$output.=xicore_ajax_get_stat_bar_html($xml->memory->free,"Free",get_formatted_number($xml->memory->free,0)." MB",(1/$total)*100,100,101,101);
		$t=intval($xml->memory->shared);
		$output.=xicore_ajax_get_stat_bar_html($xml->memory->shared,"Shared",get_formatted_number($xml->memory->shared,0)." MB",(1/$total)*100,100,101,101);
		$t=intval($xml->memory->buffers);
		$output.=xicore_ajax_get_stat_bar_html($xml->memory->buffers,"Buffers",get_formatted_number($xml->memory->buffers,0)." MB",(1/$total)*100,100,101,101);
		$t=intval($xml->memory->cached);
		$output.=xicore_ajax_get_stat_bar_html($xml->memory->cached,"Cached",get_formatted_number($xml->memory->cached,0)." MB",(1/$total)*100,100,101,101);

		$total=intval($xml->swap->total);
        if ($total>0){ // changed to remove if no swap and remove possibility of division by zero - SW
        $output.='<tr><td><span class="sysstat_stat_title">Swap</span></td></tr>';
		$output.='<tr><td><span class="sysstat_stat_subtitle">Total</td></td><td>'.get_formatted_number($xml->swap->total,0).' MB</td></tr>';
		$t=intval($xml->swap->used);
		$output.=xicore_ajax_get_stat_bar_html($xml->swap->used,"Used",get_formatted_number($xml->swap->used,0)." MB",(1/$total)*100,100,50,80);
		$t=intval($xml->swap->free);
		$output.=xicore_ajax_get_stat_bar_html($xml->swap->free,"Free",get_formatted_number($xml->swap->free,0)." MB",(1/$total)*100,100,100,100);
        }

		$output.='
		</tbody>
		</table>';

		}
		
	$output.='
	<div class="ajax_date">Last Updated: '.get_datetime_string(time()).'</div>
	';
	
	return $output;
	}
	
	
	
function xicore_ajax_get_component_states_html($args=null){
	global $lstr;

	if(is_admin()==false)
		return $lstr['NotAuthorizedErrorText'];


	// get sysstat data
	//$xml=get_backend_xml_sysstat_data();
	$xml=get_xml_sysstat_data();
	//print_r($xml);
	
	//echo "ARGS2\n";
	//print_r($args);
	
	$output='<div class="infotable_title">XI System Component Status</div>
';
	if($xml==null){
		$output.="No data";
		}
	else{
		$output.='
		<table class="infotable">
		<thead>
		<tr><th>Component</th><th>Status</th><th>Action</th></tr>
		</thead>
		<tbody>
		';
		
		$components=array(
			"nagioscore",
			"npcd",
			"ndo2db",
			"dbmaint",
			//"dbbackend",
			"cmdsubsys",
			"eventman",
			"feedproc",
			"reportengine",
			"cleaner",
			"nom",
			"sysstat",
			);
		foreach($components as $c){
			$output.=xicore_ajax_get_component_state_html($c,$xml);
			}
		$output.='
		</tbody>
		</table>';
		}
		
	$output.='
	<div class="ajax_date">Last Updated: '.get_datetime_string(time()).'</div>
	';
	
	return $output;
	}
	
function xicore_ajax_get_component_state_html($c,$xml){
	global $lstr;

	if(is_admin()==false)
		return $lstr['NotAuthorizedErrorText'];


	if($xml==null){
		return "No data";
		return;
		}


	$actionimg="action_small.gif";
	$actionimgtitle="Actions";
	$img="unknown_small.png";
	$imgtitle="Unknown state";
	$status=SUBSYS_COMPONENT_STATUS_UNKNOWN;
	$description="";
	$menu="";
	
	switch($c){
		case "nagioscore":
			$title="Monitoring Engine";
			foreach($xml->daemons->daemon as $d){
				if($d["id"]=="nagioscore"){
				
					$imgtitle=strval($d->output);
					$status=intval($d->status);
					
					$menu="<ul class='hiddendropdown'>\n";
					if($status!=SUBSYS_COMPONENT_STATUS_OK)
						$menu.="<li class='start' onClick='submit_command(".COMMAND_NAGIOSCORE_START.")'><a href='#' 	class='start_nagioscore'>Start</a></li>\n";
					if($status!=SUBSYS_COMPONENT_STATUS_ERROR)
						$menu.="<li class='restart' onClick='submit_command(".COMMAND_NAGIOSCORE_RESTART.")'><a href='#' 	class='restart_nagioscore'>Restart</a></li>\n";
					if($status!=SUBSYS_COMPONENT_STATUS_ERROR)
						$menu.="<li class='stop' onClick='submit_command(".COMMAND_NAGIOSCORE_STOP.")'><a href='#' class='stop_nagioscore'>Stop</a></li>\n";
					$menu.="</ul>\n";
					}
				}
			break;
		case "npcd":
			$title="Performance Grapher";
			foreach($xml->daemons->daemon as $d){
				if($d["id"]=="pnp"){
				
					$imgtitle=strval($d->output);
					$status=intval($d->status);

					$menu="<ul class='hiddendropdown'>\n";
					if($status!=SUBSYS_COMPONENT_STATUS_OK)
						$menu.="<li class='start' onClick='submit_command(".COMMAND_NPCD_START.")'><a href='#' class='start_npcd'>Start</a></li>\n";
					if($status!=SUBSYS_COMPONENT_STATUS_ERROR)
						$menu.="<li class='restart' onClick='submit_command(".COMMAND_NPCD_RESTART.")'><a href='#' class='restart_npcd'>Restart</a></li>\n";
					if($status!=SUBSYS_COMPONENT_STATUS_ERROR)
						$menu.="<li class='stop' onClick='submit_command(".COMMAND_NPCD_STOP.")'><a href='#' class='stop_npcd'>Stop</a></li>\n";
					$menu.="</ul>\n";
					}
				}
			break;
		case "ndo2db":
			$title="Database Backend";
			foreach($xml->daemons->daemon as $d){
				if($d["id"]=="ndoutils"){
				
					$imgtitle=strval($d->output);
					$status=intval($d->status);

					$menu="<ul class='hiddendropdown'>\n";
					if($status!=SUBSYS_COMPONENT_STATUS_OK)
						$menu.="<li class='start' onClick='submit_command(".COMMAND_NDO2DB_START.")'><a href='#' class='start_ndo2db'>Start</a></li>\n";
					if($status!=SUBSYS_COMPONENT_STATUS_ERROR)
						$menu.="<li class='restart' onClick='submit_command(".COMMAND_NDO2DB_RESTART.")'><a href='#' class='restart_ndo2db'>Restart</a></li>\n";
					if($status!=SUBSYS_COMPONENT_STATUS_ERROR)
						$menu.="<li class='stop' onClick='submit_command(".COMMAND_NDO2DB_STOP.")'><a href='#' class='stop_ndo2db'>Stop</a></li>\n";
					$menu.="</ul>\n";
					}
				}
			break;
		case "dbbackend":
			$title="Event Data";
			$x=$xml->dbbackend;
			$lastupdate=strtotime($x->last_checkin);
			$diff=time()-$lastupdate;
			if($diff<0)
				$diff=0;
			if($diff<=600) // 10 minutes
				$status=SUBSYS_COMPONENT_STATUS_OK;
			else if($diff<=1200) // 20 minutes
				$status=SUBSYS_COMPONENT_STATUS_UNKNOWN;
			else
				$status=SUBSYS_COMPONENT_STATUS_ERROR;
			$ustr=get_duration_string($diff);
			$imgtitle="Last Event Data Transfer Was ".$ustr. " Ago";
			break;
		case "dbmaint":
			$title="Database Maintenance";
			$x=$xml->dbmaint;
			$lastupdate=intval($x->last_check);
			$diff=time()-$lastupdate;
			if($diff<=3600)
				$status=SUBSYS_COMPONENT_STATUS_OK;
			else
				$status=SUBSYS_COMPONENT_STATUS_ERROR;
			$ustr=get_duration_string($diff);
			$imgtitle="Last Run ".$ustr. " Ago";
			if($lastupdate==0){
				$status=SUBSYS_COMPONENT_STATUS_UNKNOWN;
				$imgtitle="Not Run Yet";
				}
			break;
		case "cmdsubsys":
			$title="Command Subsystem";
			$x=$xml->cmdsubsys;
			$lastupdate=intval($x->last_check);
			$diff=time()-$lastupdate;
			if($diff<=120)
				$status=SUBSYS_COMPONENT_STATUS_OK;
			else
				$status=SUBSYS_COMPONENT_STATUS_ERROR;
			$ustr=get_duration_string($diff);
			$imgtitle="Last Run ".$ustr. " Ago";
			break;
		case "eventman":
			$title="Event Manager";
			$x=$xml->eventman;
			$lastupdate=intval($x->last_check);
			$diff=time()-$lastupdate;
			if($diff<=120)
				$status=SUBSYS_COMPONENT_STATUS_OK;
			else
				$status=SUBSYS_COMPONENT_STATUS_ERROR;
			$ustr=get_duration_string($diff);
			$imgtitle="Last Run ".$ustr. " Ago";
			break;
		case "feedproc":
			$title="Feed Processor";
			$x=$xml->feedprocessor;
			$lastupdate=intval($x->last_check);
			$diff=time()-$lastupdate;
			if($diff<=120)
				$status=SUBSYS_COMPONENT_STATUS_OK;
			else
				$status=SUBSYS_COMPONENT_STATUS_ERROR;
			$ustr=get_duration_string($diff);
			$imgtitle="Last Run ".$ustr. " Ago";
			break;
		case "reportengine":
			$title="Report Engine";
			$x=$xml->reportengine;
			$lastupdate=intval($x->last_check);
			$diff=time()-$lastupdate;
			if($diff<=120)
				$status=SUBSYS_COMPONENT_STATUS_OK;
			else
				$status=SUBSYS_COMPONENT_STATUS_ERROR;
			$ustr=get_duration_string($diff);
			$imgtitle="Last Run ".$ustr. " Ago";
			break;
		case "nom":
			$title="Nonstop Operations Manager";
			$x=$xml->nom;
			$lastupdate=intval($x->last_check);
			$diff=time()-$lastupdate;
			if($diff<=3600)
				$status=SUBSYS_COMPONENT_STATUS_OK;
			else
				$status=SUBSYS_COMPONENT_STATUS_ERROR;
			$ustr=get_duration_string($diff);
			$imgtitle="Last Run ".$ustr. " Ago";
			if($lastupdate==0){
				$status=SUBSYS_COMPONENT_STATUS_UNKNOWN;
				$imgtitle="Not Run Yet";
				}
			break;
		case "cleaner":
			$title="Cleaner";
			$x=$xml->cleaner;
			$lastupdate=intval($x->last_check);
			$diff=time()-$lastupdate;
			if($diff<=3600)
				$status=SUBSYS_COMPONENT_STATUS_OK;
			else
				$status=SUBSYS_COMPONENT_STATUS_ERROR;
			$ustr=get_duration_string($diff);
			$imgtitle="Last Run ".$ustr. " Ago";
			if($lastupdate==0){
				$status=SUBSYS_COMPONENT_STATUS_UNKNOWN;
				$imgtitle="Not Run Yet";
				}
			break;
		case "sysstat":
			$title="System Statistics";
			$x=$xml->sysstat;
			$lastupdate=intval($x->last_check);
			$diff=time()-$lastupdate;
			if($diff<=120)
				$status=SUBSYS_COMPONENT_STATUS_OK;
			else
				$status=SUBSYS_COMPONENT_STATUS_ERROR;
			$ustr=get_duration_string($diff);
			$imgtitle="Last Updated ".$ustr. " Ago";
			break;
		default:
			break;
		}
		
	if($xml==null){
		$img="unknown_small.png";
		$imgtile="Data unavailable";
		}
	else{
		switch($status){
			case SUBSYS_COMPONENT_STATUS_OK:
				$img="ok_small.png";
				break;
			case SUBSYS_COMPONENT_STATUS_ERROR:
				$img="critical_small.png";
				break;
			case SUBSYS_COMPONENT_STATUS_UNKNOWN:
				$img="unknown_small.png";
				break;
			default:
				break;
			}
		}


	switch($c){
		case "nagioscore":
		case "npcd":
		case "ndo2db":
			$action_div='<div class="sysstate_componentstate_image"><img src="'.theme_image($actionimg).'" class="actionimage" title="'.$actionimgtitle.'">'.$menu.'</div>';
			break;
		default:
			$action_div='';
			break;
		}
		
	$output='
	<tr>
	<td>
	<div class="sysstat_componentstate_title">'.$title.'</div>
	<div class="sysstat_componentstate_description">'.$description.'</div>
	</td>
	<td><div class="sysstate_componentstate_image"><img src="'.theme_image($img).'" title="'.$imgtitle.'"></div></td>
	<td>'.$action_div.'</td>
	</tr>
	';
	
	return $output;
	}

	
	
?>
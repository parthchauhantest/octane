<?php
// XI Core Dashlet Functions
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: dashlets-perfdata.inc.php 816 2011-09-04 15:16:26Z egalstad $

include_once(dirname(__FILE__).'/../componenthelper.inc.php');
include_once(dirname(__FILE__).'/../../utils-dashlets.inc.php');


////////////////////////////////////////////////////////////////////////
// CORE PERFDATA DASHLETS
////////////////////////////////////////////////////////////////////////


// performance graph
function xicore_dashlet_perfdata_chart($mode=DASHLET_MODE_PREVIEW,$id="",$args=null){
	global $lstr;
	global $cfg;

	$output="";
	
	if($args==null)
		$args=array();
		
	$dashletmode=$mode;
		
	switch($mode){
		case DASHLET_MODE_GETCONFIGHTML:
			$output='';
			break;
		case DASHLET_MODE_OUTBOARD:
		case DASHLET_MODE_INBOARD:
		
			$id="perfgraph_".random_string(6);
			
			$output='<div class="perfgraph" id="'.$id.'">';
			
			$hostname=grab_array_var($args,"hostname","");
			$host_id=grab_array_var($args,"host_id",-1);
			$servicename=grab_array_var($args,"servicename","");
			$service_id=grab_array_var($args,"service_id",-1);
			$source=grab_array_var($args,"source","");
			$sourcename=grab_array_var($args,"sourcename","");
			$sourcetemplate=grab_array_var($args,"sourcetemplate","");
			$view=grab_array_var($args,"view","");
			$start=grab_array_var($args,"start","");
			$end=grab_array_var($args,"end","");
			$startdate=grab_array_var($args,"startdate","");
			$enddate=grab_array_var($args,"enddate","");
			$width=grab_array_var($args,"width","");
			$height=grab_array_var($args,"height","");
			$mode=grab_array_var($args,"mode","");
			
			if($service_id>0)
				$auth=is_authorized_for_object_id(0,$service_id);
			else
				$auth=is_authorized_for_object_id(0,$host_id);
			if($auth==false){
				//return "SID=$service_id, HID=$host_id, HOST='$hostname', SERVICE='$servicename'";
				return $lstr['NotAuthorizedErrorText'];
				break;
				}
				
			$title="";
			$imagetitle="";
			$url="";
			$imgurla="";
			$imgurlb="";
				
			switch($mode){
				case PERFGRAPH_MODE_HOSTSOVERVIEW:
					$title=$hostname." Host Performance Graph";
					$imagetitle="View All ".htmlentities($hostname)." Performance Graphs";
					$url=xicore_dashlet_perfdata_chart_get_chart_url($hostname,$servicename,$source,$view,$start,$end,$startdate,$enddate,PERFGRAPH_MODE_HOSTOVERVIEW,$host_id,$service_id);
					$imgurla="<a href='".$url."'>";
					$imgurlb="</a>";
					break;
				case PERFGRAPH_MODE_HOSTOVERVIEW:
					if($servicename=="_HOST_")
						$title="Host Performance";
					else
						$title=$servicename;
					$imagetitle="View Detailed ".htmlentities($servicename)." Performance Graphs";
					$url=xicore_dashlet_perfdata_chart_get_chart_url($hostname,$servicename,$source,$view,$start,$end,$startdate,$enddate,PERFGRAPH_MODE_SERVICEDETAIL,$host_id,$service_id);
					$imgurla="<a href='".$url."'>";
					$imgurlb="</a>";
					break;
				case PERFGRAPH_MODE_GOTOSERVICEDETAIL:
					$title="Datasource: ".perfdata_get_friendly_source_name($sourcename,$sourcetemplate);
					$url=xicore_dashlet_perfdata_chart_get_chart_url($hostname,$servicename,$source,$view,$start,$end,$startdate,$enddate,PERFGRAPH_MODE_SERVICEDETAIL,$host_id,$service_id);
					$imgurla="<a href='".$url."'>";
					$imgurlb="</a>";
					break;
				case PERFGRAPH_MODE_SERVICEDETAIL:
					$title="Datasource: ".perfdata_get_friendly_source_name($sourcename,$sourcetemplate);
					break;
				default:
					break;
				}

			$id="perfdata_chart_".random_string(6);

			/*
			$sizestr="";
			if($width=="")
				$width="600px";
			$sizestr.="width:".$width.";";
			*/
				
			$output.="<div class='perfgraphtitle'>".$title."</div>\n";
			$output.="<div id='throbber_".$id."'>".get_throbber_html()."</div>";
			
			$divwidth="";
			if($dashletmode==DASHLET_MODE_INBOARD)
				$divwidth="width: 100%;";
			$output.="<div style='".$divwidth."'>&nbsp;";
			$output.=$imgurla;
			$output.="<img src='";
			
			//$output.=xicore_ajax_get_perfdata_chart_html($args);
			
			$imgwidth="";
			if($dashletmode==DASHLET_MODE_INBOARD)
				$imgwidth="width='100%'";
			
			$output.="' title='".$imagetitle."' id='".$id."' ".$imgwidth.">";
			$output.=$imgurlb;
		
			$output.="</div>";
			$output.='</div>';
			
			// refresh rate
			//$refresh_rate=grab_array_var($cfg,"performance_graph_refresh_rate",60);

			// build args for javascript
			$n=0;
			$jargs="{";
			foreach($args as $var => $val){
				if($n>0)
					$jargs.=", ";
				$jargs.="\"".htmlentities($var)."\" : \"".htmlentities($val)."\"";
				$n++;
				}
			$jargs.="}";
			
			$output.='
			<script type="text/javascript">
			$(document).ready(function(){
				
				get_'.$id.'_content();
					
				$("#'.$id.'").everyTime('.get_dashlet_refresh_rate(60,"perfdata_chart").', "timer-'.$id.'", function(i) {
					get_'.$id.'_content();
				});
				
			});
			
			function delete_'.$id.'_throbber(){
				$("#throbber_'.$id.'").each(function(){
					$(this).remove();  // remove the throbber if it exists
					});
				}
			
			function get_'.$id.'_content(){
				$("#'.$id.'").each(function(){
					var optsarr = {
						"func": "get_perfdata_chart_html",
						"args": '.$jargs.'
						}
					var opts=array2json(optsarr);
					//get_ajax_data_imagesrc("getxicoreajax",opts,true,this);
					get_ajax_data_imagesrc_with_callback("getxicoreajax",opts,true,this,"delete_'.$id.'_throbber");
					});
				}
			
			</script>
			';
			
			break;
			
		case DASHLET_MODE_PREVIEW:
			$imgurl=get_component_url_base()."xicore/images/dashlets/perfdata_chart_preview.png";
			$output='
			<img src="'.$imgurl.'">
			';
			break;			
		}
		
	return $output;
	}


function xicore_dashlet_perfdata_chart_get_chart_url($hostname="",$servicename="_HOST_",$source="",$view="",$start="",$end="",$startdate="",$enddate="",$mode=PERFGRAPH_MODE_HOSTSOVERVIEW,$host_id=-1,$service_id=-1){

	//$url=get_base_url()."includes/page-perfgraphs-main.php?";
	$url=get_base_url()."perfgraphs/?";
	
	$url.="&host=$hostname";
	$url.="&service=$servicename";
	$url.="&source=$source";
	$url.="&view=$view";
	$url.="&start=$start";
	$url.="&end=$end";
	$url.="&startdate=$startdate";
	$url.="&enddate=$enddate";
	$url.="&mode=$mode";
	if($host_id>0)
		$url.="&host_id=$host_id";
	if($service_id>0)
		$url.="&service_id=$service_id";

	return $url;
	}
	

?>
<?php
// XI Core Dashlet Functions
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: dashlets-status.inc.php 273 2010-08-13 21:28:51Z egalstad $

include_once(dirname(__FILE__).'/../componenthelper.inc.php');
include_once(dirname(__FILE__).'/../../utils-dashlets.inc.php');


////////////////////////////////////////////////////////////////////////
// TAC DASHLETS
////////////////////////////////////////////////////////////////////////

// network outages summary
function xicore_dashlet_network_outages_summary($mode=DASHLET_MODE_PREVIEW,$id="",$args=null){
	global $lstr;

	$output="";
	
	if($args==null)
		$args=array();
		
	switch($mode){
		case DASHLET_MODE_GETCONFIGHTML:
			$output='';
			break;
		case DASHLET_MODE_OUTBOARD:
		case DASHLET_MODE_INBOARD:
		
			$id="network_outages_summary_".random_string(6);
			
			// ajax updater args
			$ajaxargs=$args;
			$ajaxargs["mode"]=$mode;
			// build args for javascript
			$n=0;
			$jargs="{";
			foreach($ajaxargs as $var => $val){
				if($n>0)
					$jargs.=", ";
				$jargs.="\"".htmlentities($var)."\" : \"".htmlentities($val)."\"";
				$n++;
				}
			$jargs.="}";

			$output.='
			<div class="network_outages_summary_dashlet" id="'.$id.'">
			
			<div class="infotable_title">Network Outages</div>
			'.get_throbber_html().'			
			
			</div><!--network_outages_summary_dashlet-->

			<script type="text/javascript">
			$(document).ready(function(){
			
				get_'.$id.'_content();
					
				$("#'.$id.'").everyTime('.get_dashlet_refresh_rate(30,"network_outages_summary").', "timer-'.$id.'", function(i) {
					get_'.$id.'_content();
				});
				
				function get_'.$id.'_content(){
					$("#'.$id.'").each(function(){
						var optsarr = {
							"func": "get_network_outages_summary_html",
							"args": '.$jargs.'
							}
						var opts=array2json(optsarr);
						get_ajax_data_innerHTML("getxicoreajax",opts,true,this);
						});
					}
			});
			</script>
			';
			
			break;
		case DASHLET_MODE_PREVIEW:
			$imgurl=get_component_url_base()."xicore/images/dashlets/network_outages_summary_preview.png";
			$output='
			<img src="'.$imgurl.'">
			';
			break;
		default:
			break;
		}
	return $output;
	}

// network health
function xicore_dashlet_network_health($mode=DASHLET_MODE_PREVIEW,$id="",$args=null){
	global $lstr;

	$output="";
	
	if($args==null)
		$args=array();
		
	switch($mode){
		case DASHLET_MODE_GETCONFIGHTML:
			$output='';
			break;
		case DASHLET_MODE_OUTBOARD:
		case DASHLET_MODE_INBOARD:
		
			$id="network_health_".random_string(6);
			
			// ajax updater args
			$ajaxargs=$args;
			// build args for javascript
			$n=0;
			$jargs="{";
			foreach($ajaxargs as $var => $val){
				if($n>0)
					$jargs.=", ";
				$jargs.="\"".htmlentities($var)."\" : \"".htmlentities($val)."\"";
				$n++;
				}
			$jargs.="}";

			$output.='
			<div class="network_health_dashlet" id="'.$id.'">
			
			<div class="infotable_title">Network Health</div>
			'.get_throbber_html().'			
			
			</div><!--network_health_dashlet-->

			<script type="text/javascript">
			$(document).ready(function(){
			
				get_'.$id.'_content();
					
				$("#'.$id.'").everyTime('.get_dashlet_refresh_rate(30,"network_health").', "timer-'.$id.'", function(i) {
					get_'.$id.'_content();
				});
				
				function get_'.$id.'_content(){
					$("#'.$id.'").each(function(){
						var optsarr = {
							"func": "get_network_health_html",
							"args": '.$jargs.'
							}
						var opts=array2json(optsarr);
						get_ajax_data_innerHTML("getxicoreajax",opts,true,this);
						});
					}
			});
			</script>
			';
			
			break;
		case DASHLET_MODE_PREVIEW:
			$imgurl=get_component_url_base()."xicore/images/dashlets/network_health_preview.png";
			$output='
			<img src="'.$imgurl.'">
			';
			break;
		default:
			break;
		}
	return $output;
	}


// host status tac summary
function xicore_dashlet_host_status_tac_summary($mode=DASHLET_MODE_PREVIEW,$id="",$args=null){
	global $lstr;

	$output="";
	
	if($args==null)
		$args=array();
		
	switch($mode){
		case DASHLET_MODE_GETCONFIGHTML:
			$output='';
			break;
		case DASHLET_MODE_OUTBOARD:
		case DASHLET_MODE_INBOARD:
		
			$id="host_status_tac_summary_".random_string(6);
			
			// ajax updater args
			$ajaxargs=$args;
			$ajaxargs["mode"]=$mode;
			// build args for javascript
			$n=0;
			$jargs="{";
			foreach($ajaxargs as $var => $val){
				if($n>0)
					$jargs.=", ";
				$jargs.="\"".htmlentities($var)."\" : \"".htmlentities($val)."\"";
				$n++;
				}
			$jargs.="}";

			$output.='
			<div class="host_status_tac_summary_dashlet" id="'.$id.'">
			
			<div class="infotable_title">Hosts</div>
			'.get_throbber_html().'			
			
			</div><!--host_status_tac_summary_dashlet-->

			<script type="text/javascript">
			$(document).ready(function(){
			
				get_'.$id.'_content();
					
				$("#'.$id.'").everyTime('.get_dashlet_refresh_rate(30,"host_status_tac_summary").', "timer-'.$id.'", function(i) {
					get_'.$id.'_content();
				});
				
				function get_'.$id.'_content(){
					$("#'.$id.'").each(function(){
						var optsarr = {
							"func": "get_host_status_tac_summary_html",
							"args": '.$jargs.'
							}
						var opts=array2json(optsarr);
						get_ajax_data_innerHTML("getxicoreajax",opts,true,this);
						});
					}
			});
			</script>
			';
			
			break;
		case DASHLET_MODE_PREVIEW:
			$imgurl=get_component_url_base()."xicore/images/dashlets/host_status_tac_summary_preview.png";
			$output='
			<img src="'.$imgurl.'">
			';
			break;
		default:
			break;
		}
	return $output;
	}
	


// service status tac summary
function xicore_dashlet_service_status_tac_summary($mode=DASHLET_MODE_PREVIEW,$id="",$args=null){
	global $lstr;

	$output="";
	
	if($args==null)
		$args=array();
		
	switch($mode){
		case DASHLET_MODE_GETCONFIGHTML:
			$output='';
			break;
		case DASHLET_MODE_OUTBOARD:
		case DASHLET_MODE_INBOARD:
		
			$id="service_status_tac_summary_".random_string(6);
			
			// ajax updater args
			$ajaxargs=$args;
			$ajaxargs["mode"]=$mode;
			// build args for javascript
			$n=0;
			$jargs="{";
			foreach($ajaxargs as $var => $val){
				if($n>0)
					$jargs.=", ";
				$jargs.="\"".htmlentities($var)."\" : \"".htmlentities($val)."\"";
				$n++;
				}
			$jargs.="}";

			$output.='
			<div class="service_status_tac_summary_dashlet" id="'.$id.'">
			
			<div class="infotable_title">Services</div>
			'.get_throbber_html().'			
			
			</div><!--service_status_tac_summary_dashlet-->

			<script type="text/javascript">
			$(document).ready(function(){
			
				get_'.$id.'_content();
					
				$("#'.$id.'").everyTime('.get_dashlet_refresh_rate(30,"service_status_tac_summary").', "timer-'.$id.'", function(i) {
					get_'.$id.'_content();
				});
				
				function get_'.$id.'_content(){
					$("#'.$id.'").each(function(){
						var optsarr = {
							"func": "get_service_status_tac_summary_html",
							"args": '.$jargs.'
							}
						var opts=array2json(optsarr);
						get_ajax_data_innerHTML("getxicoreajax",opts,true,this);
						});
					}
			});
			</script>
			';
			
			break;
		case DASHLET_MODE_PREVIEW:
			$imgurl=get_component_url_base()."xicore/images/dashlets/service_status_tac_summary_preview.png";
			$output='
			<img src="'.$imgurl.'">
			';
			break;
		default:
			break;
		}
	return $output;
	}
	


// feature status tac summary
function xicore_dashlet_feature_status_tac_summary($mode=DASHLET_MODE_PREVIEW,$id="",$args=null){
	global $lstr;

	$output="";
	
	if($args==null)
		$args=array();
		
	switch($mode){
		case DASHLET_MODE_GETCONFIGHTML:
			$output='';
			break;
		case DASHLET_MODE_OUTBOARD:
		case DASHLET_MODE_INBOARD:
		
			$id="feature_status_tac_summary_".random_string(6);
			
			// ajax updater args
			$ajaxargs=$args;
			$ajaxargs["mode"]=$mode;
			// build args for javascript
			$n=0;
			$jargs="{";
			foreach($ajaxargs as $var => $val){
				if($n>0)
					$jargs.=", ";
				$jargs.="\"".htmlentities($var)."\" : \"".htmlentities($val)."\"";
				$n++;
				}
			$jargs.="}";

			$output.='
			<div class="feature_status_tac_summary_dashlet" id="'.$id.'">
			
			<div class="infotable_title">Features</div>
			'.get_throbber_html().'			
			
			</div><!--feature_status_tac_summary_dashlet-->

			<script type="text/javascript">
			$(document).ready(function(){
			
				get_'.$id.'_content();
					
				$("#'.$id.'").everyTime('.get_dashlet_refresh_rate(30,"feature_status_tac_summary").', "timer-'.$id.'", function(i) {
					get_'.$id.'_content();
				});
				
				function get_'.$id.'_content(){
					$("#'.$id.'").each(function(){
						var optsarr = {
							"func": "get_feature_status_tac_summary_html",
							"args": '.$jargs.'
							}
						var opts=array2json(optsarr);
						get_ajax_data_innerHTML("getxicoreajax",opts,true,this);
						});
					}
			});
			</script>
			';
			
			break;
		case DASHLET_MODE_PREVIEW:
			$imgurl=get_component_url_base()."xicore/images/dashlets/feature_status_tac_summary_preview.png";
			$output='
			<img src="'.$imgurl.'">
			';
			break;
		default:
			break;
		}
	return $output;
	}
	
?>
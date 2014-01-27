<?php
// XI Core Dashlet Functions
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: dashlets-status.inc.php 714 2011-07-12 20:08:21Z egalstad $

include_once(dirname(__FILE__).'/../componenthelper.inc.php');
include_once(dirname(__FILE__).'/../../utils-dashlets.inc.php');


////////////////////////////////////////////////////////////////////////
// STATUS DASHLETS
////////////////////////////////////////////////////////////////////////

// network outages
function xicore_dashlet_network_outages($mode=DASHLET_MODE_PREVIEW,$id="",$args=null){
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
		
			$id="network_outages_".random_string(6);
			
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
			<div class="network_outages_dashlet" id="'.$id.'">
			
			<div class="infotable_title">Network Outages</div>
			'.get_throbber_html().'			
			
			</div><!--network_outages_dashlet-->

			<script type="text/javascript">
			$(document).ready(function(){
			
				get_'.$id.'_content();
					
				$("#'.$id.'").everyTime('.get_dashlet_refresh_rate(30,"network_outages").', "timer-'.$id.'", function(i) {
					get_'.$id.'_content();
				});
				
				function get_'.$id.'_content(){
					$("#'.$id.'").each(function(){
						var optsarr = {
							"func": "get_network_outages_html",
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
			$imgurl=get_component_url_base()."xicore/images/dashlets/network_outages_preview.png";
			$output='
			<img src="'.$imgurl.'">
			';
			break;
		default:
			break;
		}
	return $output;
	}
	
// host status summary
function xicore_dashlet_host_status_summary($mode=DASHLET_MODE_PREVIEW,$id="",$args=null){
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
		
			$output="";
			//$output.='HOST STATUS SUMMARY: '.serialize($args);
			
			$id="host_status_summary_".random_string(6);
			
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
			<div class="host_status_summary_dashlet" id="'.$id.'">
			
			<div class="infotable_title">Host Status Summary</div>
			'.get_throbber_html().'			
			
			</div><!--ahost_status_summary_dashlet-->

			<script type="text/javascript">
			$(document).ready(function(){
			
				get_'.$id.'_content();
					
				$("#'.$id.'").everyTime('.get_dashlet_refresh_rate(30,"host_status_summary").', "timer-'.$id.'", function(i) {
					get_'.$id.'_content();
				});
				
				function get_'.$id.'_content(){
					$("#'.$id.'").each(function(){
						var optsarr = {
							"func": "get_host_status_summary_html",
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
			$imgurl=get_component_url_base()."xicore/images/dashlets/host_status_summary.png";
			$output='
			<img src="'.$imgurl.'">
			';
			break;			
		}
		
	return $output;
	}

// service status summary
function xicore_dashlet_service_status_summary($mode=DASHLET_MODE_PREVIEW,$id="",$args=null){
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
		
			$output="";
			//$output.='SERVICE STATUS SUMMARY: '.serialize($args);
			
			$id="host_status_summary_".random_string(6);
			
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
			<div class="service_status_summary_dashlet" id="'.$id.'">
			
			<div class="infotable_title">Service Status Summary</div>
			'.get_throbber_html().'			
			
			</div><!--service_status_summary_dashlet-->

			<script type="text/javascript">
			$(document).ready(function(){
			
				get_'.$id.'_content();
					
				$("#'.$id.'").everyTime('.get_dashlet_refresh_rate(30,"service_status_summary").', "timer-'.$id.'", function(i) {
					get_'.$id.'_content();
				});
				
				function get_'.$id.'_content(){
					$("#'.$id.'").each(function(){
						var optsarr = {
							"func": "get_service_status_summary_html",
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
			$imgurl=get_component_url_base()."xicore/images/dashlets/service_status_summary.png";
			$output='
			<img src="'.$imgurl.'">
			';
			break;			
		}
		
	return $output;
	}


// hostgroup status overview
function xicore_dashlet_hostgroup_status_overview($mode=DASHLET_MODE_PREVIEW,$id="",$args=null){
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
		
			$hostgroup=grab_array_var($args,"hostgroup");
			$hostgroup_alias=grab_array_var($args,"hostgroup_alias");
		
			$output="";
			
			$id="hostgroup_status_overview_".random_string(6);
			
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
			<div class="hostgroup_status_overview_dashlet" id="'.$id.'">
			
			<div class="infotable_title">'.htmlentities($hostgroup_alias).' ('.htmlentities($hostgroup).')</div>

			'.get_throbber_html().'			
			
			</div><!--hostgroup_status_overview_dashlet-->

			<script type="text/javascript">
			$(document).ready(function(){
			
				get_'.$id.'_content();
					
				$("#'.$id.'").everyTime('.get_dashlet_refresh_rate(60,"hostgroup_status_overview").', "timer-'.$id.'", function(i) {
					get_'.$id.'_content();
				});
				
				function get_'.$id.'_content(){
					$("#'.$id.'").each(function(){
						var optsarr = {
							"func": "get_hostgroup_status_overview_html",
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
			$output='
			';
			break;			
		}
		
	return $output;
	}


// hostgroup status grid
function xicore_dashlet_hostgroup_status_grid($mode=DASHLET_MODE_PREVIEW,$id="",$args=null){
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
		
			$hostgroup=grab_array_var($args,"hostgroup");
			$hostgroup_alias=grab_array_var($args,"hostgroup_alias");
		
			$output="";
			
			$id="hostgroup_status_grid_".random_string(6);
			
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
			<div class="hostgroup_status_grid_dashlet" id="'.$id.'">
			
			<div class="infotable_title">'.htmlentities($hostgroup_alias).' ('.htmlentities($hostgroup).')</div>
	
			'.get_throbber_html().'			
			
			</div><!--hostgroup_status_grid_dashlet-->

			<script type="text/javascript">
			$(document).ready(function(){
			
				get_'.$id.'_content();
					
				$("#'.$id.'").everyTime('.get_dashlet_refresh_rate(60,"hostgroup_status_grid").', "timer-'.$id.'", function(i) {
					get_'.$id.'_content();
				});
				
				function get_'.$id.'_content(){
					$("#'.$id.'").each(function(){
						var optsarr = {
							"func": "get_hostgroup_status_grid_html",
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
			$output='
			';
			break;			
		}
		
	return $output;
	}


// servicegroup status overview
function xicore_dashlet_servicegroup_status_overview($mode=DASHLET_MODE_PREVIEW,$id="",$args=null){
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
		
			$servicegroup=grab_array_var($args,"servicegroup");
			$servicegroup_alias=grab_array_var($args,"servicegroup_alias");
		
			$output="";
			
			$id="servicegroup_status_overview_".random_string(6);
			
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
			<div class="servicegroup_status_overview_dashlet" id="'.$id.'">
			
			<div class="infotable_title">'.htmlentities($servicegroup_alias).' ('.htmlentities($servicegroup).')</div>
	
			'.get_throbber_html().'			
			
			</div><!--servicegroup_status_overview_dashlet-->

			<script type="text/javascript">
			$(document).ready(function(){
			
				get_'.$id.'_content();
					
				$("#'.$id.'").everyTime('.get_dashlet_refresh_rate(60,"servicegroup_status_overview").', "timer-'.$id.'", function(i) {
					get_'.$id.'_content();
				});
				
				function get_'.$id.'_content(){
					$("#'.$id.'").each(function(){
						var optsarr = {
							"func": "get_servicegroup_status_overview_html",
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
			$output='
			';
			break;			
		}
		
	return $output;
	}



// servicegroup status grid
function xicore_dashlet_servicegroup_status_grid($mode=DASHLET_MODE_PREVIEW,$id="",$args=null){
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
		
			$servicegroup=grab_array_var($args,"servicegroup");
			$servicegroup_alias=grab_array_var($args,"servicegroup_alias");

			$output="";
			
			$id="servicegroup_status_grid_".random_string(6);
			
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
			<div class="servicegroup_status_grid_dashlet" id="'.$id.'">
			
			<div class="infotable_title">'.htmlentities($servicegroup_alias).' ('.htmlentities($servicegroup).')</div>

			'.get_throbber_html().'			
			
			</div><!--servicegroup_status_grid_dashlet-->

			<script type="text/javascript">
			$(document).ready(function(){
			
				get_'.$id.'_content();
					
				$("#'.$id.'").everyTime('.get_dashlet_refresh_rate(60,"servicegroup_status_grid").', "timer-'.$id.'", function(i) {
					get_'.$id.'_content();
				});
				
				function get_'.$id.'_content(){
					$("#'.$id.'").each(function(){
						var optsarr = {
							"func": "get_servicegroup_status_grid_html",
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
			$output='
			';
			break;			
		}
		
	return $output;
	}

	
// hostgroup status summary
function xicore_dashlet_hostgroup_status_summary($mode=DASHLET_MODE_PREVIEW,$id="",$args=null){
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
		
			$hostgroup=grab_array_var($args,"hostgroup");
			$hostgroup_alias=grab_array_var($args,"hostgroup_alias");
		
			$output="";
			
			$id="hostgroup_status_summary_".random_string(6);
			
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
			<div class="hostgroup_status_summary_dashlet" id="'.$id.'">
			
			<div class="infotable_title">Status Summary For All Host Groups</div>

			'.get_throbber_html().'			
			
			</div><!--hostgroup_status_summary_dashlet-->

			<script type="text/javascript">
			$(document).ready(function(){
			
				get_'.$id.'_content();
					
				$("#'.$id.'").everyTime('.get_dashlet_refresh_rate(60,"hostgroup_status_summary").', "timer-'.$id.'", function(i) {
					get_'.$id.'_content();
				});
				
				function get_'.$id.'_content(){
					$("#'.$id.'").each(function(){
						var optsarr = {
							"func": "get_hostgroup_status_summary_html",
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
			$imgurl=get_component_url_base()."xicore/images/dashlets/hostgroup_status_summary.png";
			$output='
			<img src="'.$imgurl.'">
			';
			break;			
		}
		
	return $output;
	}

	
// servicegroup status summary
function xicore_dashlet_servicegroup_status_summary($mode=DASHLET_MODE_PREVIEW,$id="",$args=null){
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
		
			$servicegroup=grab_array_var($args,"servicegroup");
			$servicegroup_alias=grab_array_var($args,"servicegroup_alias");
		
			$output="";
			
			$id="servicegroup_status_summary_".random_string(6);
			
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
			<div class="servicegroup_status_summary_dashlet" id="'.$id.'">
			
			<div class="infotable_title">Status Summary For All Service Groups</div>

			'.get_throbber_html().'			
			
			</div><!--servicegroup_status_summary_dashlet-->

			<script type="text/javascript">
			$(document).ready(function(){
			
				get_'.$id.'_content();
					
				$("#'.$id.'").everyTime('.get_dashlet_refresh_rate(60,"servicegroup_status_summary").', "timer-'.$id.'", function(i) {
					get_'.$id.'_content();
				});
				
				function get_'.$id.'_content(){
					$("#'.$id.'").each(function(){
						var optsarr = {
							"func": "get_servicegroup_status_summary_html",
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
			$imgurl=get_component_url_base()."xicore/images/dashlets/servicegroup_status_summary.png";
			$output='
			<img src="'.$imgurl.'">
			';
			break;			
		}
		
	return $output;
	}


?>
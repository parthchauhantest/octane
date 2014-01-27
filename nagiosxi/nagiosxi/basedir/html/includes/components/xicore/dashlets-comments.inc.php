<?php
// XI Core Dashlet Functions
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: dashlets-comments.inc.php 715 2011-07-12 20:15:45Z egalstad $

include_once(dirname(__FILE__).'/../componenthelper.inc.php');
include_once(dirname(__FILE__).'/../../utils-dashlets.inc.php');


////////////////////////////////////////////////////////////////////////
// COMMENT DASHLETS
////////////////////////////////////////////////////////////////////////
	
//  comments
function xicore_dashlet_comments($mode=DASHLET_MODE_PREVIEW,$id="",$args=null){
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
			
			$id="comments_".random_string(6);
			
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
			<div class="comments_dashlet" id="'.$id.'">
			
			<div class="infotable_title">Acknowledgements and Comments</div>
			'.get_throbber_html().'			
			
			</div><!--comment_dashlet-->

			<script type="text/javascript">
			$(document).ready(function(){
			
				get_'.$id.'_content();
					
				$("#'.$id.'").everyTime('.get_dashlet_refresh_rate(30,"comments_dashlet").', "timer-'.$id.'", function(i) {
					get_'.$id.'_content();
				});
				
				function get_'.$id.'_content(){
					$("#'.$id.'").each(function(){
						var optsarr = {
							"func": "get_comments_html",
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
			$imgurl=get_component_url_base()."xicore/images/dashlets/comments.png";
			$output='
			<img src="'.$imgurl.'">
			';
			break;			
		}
		
	return $output;
	}

?>
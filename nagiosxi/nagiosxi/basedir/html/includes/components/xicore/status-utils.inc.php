<?php
// XI Status Functions
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: status-utils.inc.php 871 2011-11-16 17:47:02Z egalstad $

include_once(dirname(__FILE__).'/../componenthelper.inc.php');

include_once(dirname(__FILE__).'/status-object-detail.inc.php');

	
////////////////////////////////////////////////////////////////////////
// HELPER FUNCTIONS
////////////////////////////////////////////////////////////////////////

function show_object_icon($host,$service="",$usehost=false){
	echo get_object_icon($host,$service,$usehost);
	}

function get_object_icon($host,$service="",$usehost=false){

	$html="";

	$tryhost=false;
	$iconimage="";
	
	// we are showing a host icon
	if($service=="")
		$tryhost=true;

	// we are showing a service icon
	else{
		$sid=get_service_id($host,$service);
		//echo "SID=$sid\n";
		$xml=get_xml_service_objects(array("service_id"=>$sid));
		//print_r($xml);
		if($xml!=null && $xml->recordcount>0){
			//echo "USING SVC\n";
			$iconimage=strval($xml->service->icon_image);
			$iconimagealt=strval($xml->service->icon_image_alt);
			if($iconimage=="" && $usehost==true)
				$tryhost=true;
			}
		else{
			//echo "NULL SVC\n";
			if($usehost==true)
				$tryhost=true;
			}
		}
	
	if($tryhost==true){
		$hid=get_host_id($host);
		//echo "HID=$hid\n";
		$xml=get_xml_host_objects(array("host_id"=>$hid));
		if($xml!=null && $xml->recordcount>0){
			//echo "USING HOST\n";
			$iconimage=strval($xml->host->icon_image);
			$iconimagealt=strval($xml->host->icon_image_alt);
			}
		//else
			//echo "NULL HOST\n";
		}
		
	//echo "IMG='".$iconimage."'";

	if($iconimage!="")
		$html=get_object_icon_html($iconimage,$iconimagealt);
	//else
		//$html="HOST=".$host."=".$hid.",SVC=".$service."=".$sid;
	
	return $html;
	}
	
function get_object_icon_image($host,$service="",$usehost=false){

	$iconimage="";

	$tryhost=false;
	
	// we are showing a host icon
	if($service=="")
		$tryhost=true;

	// we are showing a service icon
	else{
		$sid=get_service_id($host,$service);
		//echo "SID=$sid\n";
		$xml=get_xml_service_objects(array("service_id"=>$sid));
		//print_r($xml);
		if($xml!=null && $xml->recordcount>0){
			//echo "USING SVC\n";
			$iconimage=strval($xml->service->icon_image);
			if($iconimage=="" && $usehost==true)
				$tryhost=true;
			}
		else{
			//echo "NULL SVC\n";
			if($usehost==true)
				$tryhost=true;
			}
		}
	
	if($tryhost==true){
		$hid=get_host_id($host);
		//echo "HID=$hid\n";
		$xml=get_xml_host_objects(array("host_id"=>$hid));
		if($xml!=null && $xml->recordcount>0){
			//echo "USING HOST\n";
			$iconimage=strval($xml->host->icon_image);
			}
		//else
			//echo "NULL HOST\n";
		}
		
	return $iconimage;
	}
	
function get_object_icon_html($img,$imgalt){

	$html="";
	
	if($img!="")
		$html="<img class='objecticon' src='".get_object_icon_url($img)."' title='".htmlentities($imgalt)."' alt='".htmlentities($imgalt)."'>";
	
	return $html;
	}
	
function get_service_status_note_image($img,$imgalt){
	$html="";
	$html="<img src='".theme_image($img)."' title='".htmlentities($imgalt)."' alt='".htmlentities($imgalt)."'>";
	return $html;
	}
	
function get_host_status_note_image($img,$imgalt){
	$html="";
	$html="<img src='".theme_image($img)."' title='".htmlentities($imgalt)."' alt='".htmlentities($imgalt)."'>";
	return $html;
	}
	
function get_object_icon_url($img){
	$url=get_base_url()."includes/components/nagioscore/ui/images/logos/".$img;
	return $url;
	}
	
	

function get_object_command_icon($img,$alt){
	return "<img src='".get_object_command_icon_url($img)."' title='".htmlentities($alt)."' alt='".htmlentities($alt)."'>";
	}
	
function get_object_command_icon_url($img){
	$url=get_base_url()."includes/components/nagioscore/ui/images/".$img;
	return $url;
	}
	
function get_object_command_link($url,$img,$title){
	return '<div class="commandimage"><a href="'.$url.'">'.get_object_command_icon($img,$title).'</a></div><div class="commandtext"><a href="'.$url.'">'.htmlentities($title).'</a></div>';
	}
	
function show_object_command_link($url,$img,$title){
	echo get_object_command_link($url,$img,$title);
	}
	
function get_nagioscore_command_ajax_code($cmdarr){
	$args=array();
	if($cmdarr["command_args"]!=null){
		foreach($cmdarr["command_args"] as $var => $val){
			$args[$var]=$val;
			}
		}
	$cmddata=json_encode($args);
	$clickcmd="onClick='submit_command(".COMMAND_NAGIOSCORE_SUBMITCOMMAND.",".$cmddata.")'";
	return $clickcmd;
	}
	
function get_service_detail_command_link($cmdarr,$img,$text){

	$clickcmd=get_nagioscore_command_ajax_code($cmdarr);

	return '<div class="commandimage"><a href="#" '.$clickcmd.'><img src="'.theme_image($img).'" alt="'.$text.'" title="'.$text.'"></a></div><div class="commandtext"><a href="#"  '.$clickcmd.'>'.$text.'</a></div>';
	}
	
	
function get_host_detail_command_link($cmdarr,$img,$text){

	$clickcmd=get_nagioscore_command_ajax_code($cmdarr);

	return '<div class="commandimage"><a href="#" '.$clickcmd.'><img src="'.theme_image($img).'" alt="'.$text.'" title="'.$text.'"></a></div><div class="commandtext"><a href="#"  '.$clickcmd.'>'.$text.'</a></div>';
	}
	
	
function get_service_detail_inplace_action_link($clickcmd,$img,$text){

	return '<div class="commandimage"><a href="#" onClick="'.$clickcmd.'"><img src="'.theme_image($img).'" alt="'.$text.'" title="'.$text.'"></a></div><div class="commandtext"><a href="#"   onClick="'.$clickcmd.'">'.$text.'</a></div>';
	}
	
	
function draw_service_detail_links($hostname,$servicename){
	global $lstr;

	echo "<div class='statusdetaillinks'>";
	
	echo "<div class='statusdetaillink'><a href='".get_host_status_link($hostname)."'><img src='".theme_image("statusdetailmulti.png")."' alt='".$lstr['ViewHostServiceStatusAlt']."' title='".$lstr['ViewHostServiceStatusAlt']."'></a></div>";
	echo "<div class='statusdetaillink'><a href='".get_service_notifications_link($hostname,$servicename)."'><img src='".theme_image("notifications.png")."' alt='".$lstr['ViewServiceNotificationsAlt']."' title='".$lstr['ViewServiceNotificationsAlt']."'></a></div>";
	echo "<div class='statusdetaillink'><a href='".get_service_history_link($hostname,$servicename)."'><img src='".theme_image("history.png")."' alt='".$lstr['ViewServiceHistoryAlt']."' title='".$lstr['ViewServiceHistoryAlt']."'></a></div>";
	if(use_new_features()==false)
		echo "<div class='statusdetaillink'><a href='".get_service_trends_link($hostname,$servicename)."'><img src='".theme_image("trends.png")."' alt='".$lstr['ViewServiceTrendsAlt']."' title='".$lstr['ViewServiceTrendsAlt']."'></a></div>";
	echo "<div class='statusdetaillink'><a href='".get_service_availability_link($hostname,$servicename)."'><img src='".theme_image("availability.png")."' alt='".$lstr['ViewServiceAvailabilityAlt']."' title='".$lstr['ViewServiceAvailabilityAlt']."'></a></div>";
	//echo "<div class='perfgraphlink'><a href='".get_service_histogram_link($hostname,$servicename)."'><img src='".theme_image("histogram.png")."' alt='".$lstr['ViewServiceHistogramAlt']."' title='".$lstr['ViewServiceHistogramAlt']."'></a></div>";
	
	echo "</div>";
	}
	
function draw_host_detail_links($hostname){
	global $lstr;

	echo "<div class='statusdetaillinks'>";
	
	echo "<div class='statusdetaillink'><a href='".get_host_status_link($hostname)."'><img src='".theme_image("statusdetailmulti.png")."' alt='".$lstr['ViewHostServiceStatusAlt']."' title='".$lstr['ViewHostServiceStatusAlt']."'></a></div>";
	echo "<div class='statusdetaillink'><a href='".get_host_notifications_link($hostname)."'><img src='".theme_image("notifications.png")."' alt='".$lstr['ViewHostNotificationsAlt']."' title='".$lstr['ViewHostNotificationsAlt']."'></a></div>";
	echo "<div class='statusdetaillink'><a href='".get_host_history_link($hostname)."'><img src='".theme_image("history.png")."' alt='".$lstr['ViewHostHistoryAlt']."' title='".$lstr['ViewHostHistoryAlt']."'></a></div>";
	if(use_new_features()==false)
		echo "<div class='statusdetaillink'><a href='".get_host_trends_link($hostname)."'><img src='".theme_image("trends.png")."' alt='".$lstr['ViewHostTrendsAlt']."' title='".$lstr['ViewHostTrendsAlt']."'></a></div>";
	echo "<div class='statusdetaillink'><a href='".get_host_availability_link($hostname)."'><img src='".theme_image("availability.png")."' alt='".$lstr['ViewHostAvailabilityAlt']."' title='".$lstr['ViewHostAvailabilityAlt']."'></a></div>";
	
	echo "</div>";
	}
	

function draw_hostgroup_viewstyle_links($hostgroupname){
	global $lstr;
	
	if($hostgroupname=="all")
		$hgname="";
	else
		$hgname=$hostgroupname;
		
	$xistatus_url=get_base_url()."includes/components/xicore/status.php";

	echo "<div class='statusdetaillinks'>";
	
	echo "<div class='statusdetaillink'><a href='".$xistatus_url."?show=services&hostgroup=".urlencode($hostgroupname)."'><img src='".theme_image("statusdetailmulti.png")."' alt='".$lstr['ViewHostgroupServiceStatusAlt']."' title='".$lstr['ViewHostgroupServiceStatusAlt']."'></a></div>";
	//echo "<div class='statusdetaillink'><a href='".get_hostgroup_status_link($hostgroupname,"detail")."'><img src='".theme_image("statusdetailmulti.png")."' alt='".$lstr['ViewHostgroupServiceStatusAlt']."' title='".$lstr['ViewHostgroupServiceStatusAlt']."'></a></div>";
	echo "<div class='statusdetaillink'><a href='".get_hostgroup_status_link($hostgroupname,"summary")."'><img src='".theme_image("vssummary.png")."' alt='".$lstr['ViewHostgroupSummaryAlt']."' title='".$lstr['ViewHostgroupSummaryAlt']."'></a></div>";
	echo "<div class='statusdetaillink'><a href='".get_hostgroup_status_link($hostgroupname,"overview")."'><img src='".theme_image("vsoverview.png")."' alt='".$lstr['ViewHostgroupOverviewAlt']."' title='".$lstr['ViewHostgroupOverviewAlt']."'></a></div>";
	echo "<div class='statusdetaillink'><a href='".get_hostgroup_status_link($hostgroupname,"grid")."'><img src='".theme_image("vsgrid.png")."' alt='".$lstr['ViewHostgroupGridAlt']."' title='".$lstr['ViewHostgroupGridAlt']."'></a></div>";
	
	echo "</div>";
	}

function draw_servicegroup_viewstyle_links($servicegroupname){
	global $lstr;

	if($servicegroupname=="all")
		$sgname="";
	else
		$sgname=$servicegroupname;
		
	$xistatus_url=get_base_url()."includes/components/xicore/status.php";

	echo "<div class='statusdetaillinks'>";
	
	echo "<div class='statusdetaillink'><a href='".$xistatus_url."?show=services&servicegroup=".urlencode($servicegroupname)."'><img src='".theme_image("statusdetailmulti.png")."' alt='".$lstr['ViewServicegroupServiceStatusAlt']."' title='".$lstr['ViewServicegroupServiceStatusAlt']."'></a></div>";
	//echo "<div class='statusdetaillink'><a href='".get_servicegroup_status_link($servicegroupname,"detail")."'><img src='".theme_image("statusdetailmulti.png")."' alt='".$lstr['ViewServicegroupServiceStatusAlt']."' title='".$lstr['ViewServicegroupServiceStatusAlt']."'></a></div>";
	echo "<div class='statusdetaillink'><a href='".get_servicegroup_status_link($servicegroupname,"summary")."'><img src='".theme_image("vssummary.png")."' alt='".$lstr['ViewServicegroupSummaryAlt']."' title='".$lstr['ViewServicegroupSummaryAlt']."'></a></div>";
	echo "<div class='statusdetaillink'><a href='".get_servicegroup_status_link($servicegroupname,"overview")."'><img src='".theme_image("vsoverview.png")."' alt='".$lstr['ViewServicegroupOverviewAlt']."' title='".$lstr['ViewServicegroupOverviewAlt']."'></a></div>";
	echo "<div class='statusdetaillink'><a href='".get_servicegroup_status_link($servicegroupname,"grid")."'><img src='".theme_image("vsgrid.png")."' alt='".$lstr['ViewServicegroupGridAlt']."' title='".$lstr['ViewServicegroupGridAlt']."'></a></div>";
	
	echo "</div>";
	}

function draw_statusmap_viewstyle_links(){
	global $lstr;

	echo "<div class='statusdetaillinks'>";
	
	echo "<div class='statusdetaillink'><a href='".get_statusmap_link(6)."'><img src='".theme_image("statusmapballoon.png")."' alt='".$lstr['ViewStatusMapBalloonAlt']."' title='".$lstr['ViewStatusMapBalloonAlt']."'></a></div>";
	echo "<div class='statusdetaillink'><a href='".get_statusmap_link(3)."'><img src='".theme_image("statusmaptree.png")."' alt='".$lstr['ViewStatusMapTreeAlt']."' title='".$lstr['ViewStatusMapTreeAlt']."'></a></div>";
	
	echo "</div>";
	}
	
	
function draw_servicestatus_table(){
	global $request;
	global $lstr;
	
	// what meta key do we use to save user prefs?
	$meta_pref_option='servicestatus_table_options';

	// defaults
	//$sortby="host_name:a,service_description";
	$sortby="";
	$sortorder="asc";
	$page=1;
	$records=15;
	$search="";
	
	// default to use saved options
	$s=get_user_meta(0,$meta_pref_option);
	$saved_options=unserialize($s);
	if(is_array($saved_options)){
		if(isset($saved_options["sortby"]))
			$sortby=$saved_options["sortby"];
		if(isset($saved_options["sortorder"]))
			$sortorder=$saved_options["sortorder"];
		if(isset($saved_options["records"]))
			$records=$saved_options["records"];
		//if(array_key_exists("search",$saved_options))
			//$search=$saved_options["search"];
		}
	//echo "SAVED OPTIONS: ";
	//print_r($saved_options);

	// grab request variables
	$show=grab_request_var("show","services");
	$host=grab_request_var("host","");
	$hostgroup=grab_request_var("hostgroup","");
	$servicegroup=grab_request_var("servicegroup","");
	$hostattr=grab_request_var("hostattr",0);
	$serviceattr=grab_request_var("serviceattr",0);
	$hoststatustypes=grab_request_var("hoststatustypes",0);
	$servicestatustypes=grab_request_var("servicestatustypes",0);

	// fix for "all" options
	if($hostgroup=="all")
		$hostgroup="";
	if($servicegroup=="all")
		$servicegroup="";
	if($host=="all")
		$host="";

	$sortby=grab_request_var("sortby",$sortby);
	$sortorder=grab_request_var("sortorder",$sortorder);
	$records=grab_request_var("records",$records);
	$page=grab_request_var("page",$page);
	$search=grab_request_var("search",$search);
	if($search==$lstr['SearchBoxText'])
		$search="";

	// save options for later
	$saved_options=array(
		"sortby" => $sortby,
		"sortorder" => $sortorder,
		"records" => $records,
		//"search" => $search
		);
	$s=serialize($saved_options);
	set_user_meta(0,$meta_pref_option,$s,false);
	


	$output='';
	
	$output.="<form action='".get_base_url()."includes/components/xicore/status.php'>";
	$output.="<input type='hidden' name='show' value='".encode_form_val($show)."'>\n";
	$output.="<input type='hidden' name='sortby' value='".encode_form_val($sortby)."'>\n";
	$output.="<input type='hidden' name='sortorder' value='".encode_form_val($sortorder)."'>\n";
	$output.="<input type='hidden' name='host' value='".encode_form_val($host)."'>\n";
	$output.="<input type='hidden' name='hostgroup' value='".encode_form_val($hostgroup)."'>\n";
	$output.="<input type='hidden' name='servicegroup' value='".encode_form_val($servicegroup)."'>\n";
	
	$output.='<div class="servicestatustablesearch">';

	$searchclass="textfield";
	if(have_value($search)){
		$searchstring=$search;
		$searchclass.=" newdata";
		}
	else
		$searchstring=$lstr['SearchBoxText'];

	$output.='
	<input type="text" size="15" name="search" id="hostsearchBox" value="'.htmlentities($searchstring).'" class="'.$searchclass.'" />
	<input type="submit" class="submitbutton" name="searchButton" value="'.$lstr['GoButton'].'" id="searchButton">
	</div><!--table list search -->
	</form>
	';

	// ajax updater args
	$ajaxargs=array();
	$ajaxargs["host"]=$host;
	$ajaxargs["hostgroup"]=$hostgroup;
	$ajaxargs["servicegroup"]=$servicegroup;
	$ajaxargs["sortby"]=$sortby;
	$ajaxargs["sortorder"]=$sortorder;
	$ajaxargs["records"]=$records;
	$ajaxargs["page"]=$page;
	$ajaxargs["search"]=$search;
	$ajaxargs["hostattr"]=$hostattr;
	$ajaxargs["serviceattr"]=$serviceattr;
	$ajaxargs["hoststatustypes"]=$hoststatustypes;
	$ajaxargs["servicestatustypes"]=$servicestatustypes;

	$id="servicestatustable_".random_string(6);
	
	$output.="<div class='servicestatustable' id='".$id."'>\n";
	$output.=get_throbber_html();
	$output.="</div>";

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

	// ajax updater
	$output.='
	<script type="text/javascript">
	$(document).ready(function(){
	
		get_'.$id.'_content();
			
		$("#'.$id.'").everyTime(30*1000, "timer-'.$id.'", function(i) {
			get_'.$id.'_content();
		});
		
		function get_'.$id.'_content(){
			$("#'.$id.'").each(function(){
				var optsarr = {
					"func": "get_servicestatus_table",
					"args": '.$jargs.'
					}
				var opts=array2json(optsarr);
				get_ajax_data_innerHTML("getxicoreajax",opts,true,this);
				});
			}

	});
	</script>
	';

	//return $output;
	echo $output;
	}
	
	
function draw_hoststatus_table(){
	global $request;
	global $lstr;
	
	// what meta key do we use to save user prefs?
	$meta_pref_option='hoststatus_table_options';

	// defaults
	//$sortby="host_name:a,service_description";
	$sortby="";
	$sortorder="asc";
	$page=1;
	$records=15;
	$search="";
	
	// default to use saved options
	$s=get_user_meta(0,$meta_pref_option);
	if($s){
		$saved_options=unserialize($s);
		if(is_array($saved_options)){
			if(isset($saved_options["sortby"]))
				$sortby=$saved_options["sortby"];
			if(isset($saved_options["sortorder"]))
				$sortorder=$saved_options["sortorder"];
			if(isset($saved_options["records"]))
				$records=$saved_options["records"];
			//if(array_key_exists("search",$saved_options))
			//	$search=$saved_options["search"];
			}
		//echo "SAVED OPTIONS: ";
		//print_r($saved_options);
		}

	// grab request variables
	$show=grab_request_var("show","services");
	$host=grab_request_var("host","");
	$hostgroup=grab_request_var("hostgroup","");
	$servicegroup=grab_request_var("servicegroup","");
	$hostattr=grab_request_var("hostattr",0);
	$serviceattr=grab_request_var("serviceattr",0);
	$hoststatustypes=grab_request_var("hoststatustypes",0);
	$servicestatustypes=grab_request_var("servicestatustypes",0);

	// fix for "all" options
	if($hostgroup=="all")
		$hostgroup="";
	if($servicegroup=="all")
		$servicegroup="";
	if($host=="all")
		$host="";

	$sortby=grab_request_var("sortby",$sortby);
	$sortorder=grab_request_var("sortorder",$sortorder);
	$records=grab_request_var("records",$records);
	$page=grab_request_var("page",$page);
	$search=grab_request_var("search",$search);
	if($search==$lstr['SearchBoxText'])
		$search="";

	// save options for later
	$saved_options=array(
		"sortby" => $sortby,
		"sortorder" => $sortorder,
		"records" => $records,
		//"search" => $search
		);
	$s=serialize($saved_options);
	set_user_meta(0,$meta_pref_option,$s,false);
	


	$output='';
	
	$output.="<form action='".get_base_url()."includes/components/xicore/status.php'>";
	$output.="<input type='hidden' name='show' value='".encode_form_val($show)."'>\n";
	$output.="<input type='hidden' name='sortby' value='".encode_form_val($sortby)."'>\n";
	$output.="<input type='hidden' name='sortorder' value='".encode_form_val($sortorder)."'>\n";
	$output.="<input type='hidden' name='host' value='".encode_form_val($host)."'>\n";
	$output.="<input type='hidden' name='hostgroup' value='".encode_form_val($hostgroup)."'>\n";
	$output.="<input type='hidden' name='servicegroup' value='".encode_form_val($servicegroup)."'>\n";
	
	$output.='<div class="servicestatustablesearch">';

	$searchclass="textfield";
	if(have_value($search)){
		$searchstring=$search;
		$searchclass.=" newdata";
		}
	else
		$searchstring=$lstr['SearchBoxText'];

	$output.='
	<input type="text" size="15" name="search" id="hostsearchBox" value="'.htmlentities($searchstring).'" class="'.$searchclass.'" />
	<input type="submit" class="submitbutton" name="searchButton" value="'.$lstr['GoButton'].'" id="searchButton">
	</div><!--table list search -->
	</form>
	';

	// ajax updater args
	$ajaxargs=array();
	$ajaxargs["host"]=$host;
	$ajaxargs["hostgroup"]=$hostgroup;
	$ajaxargs["servicegroup"]=$servicegroup;
	$ajaxargs["sortby"]=$sortby;
	$ajaxargs["sortorder"]=$sortorder;
	$ajaxargs["records"]=$records;
	$ajaxargs["page"]=$page;
	$ajaxargs["search"]=$search;
	$ajaxargs["hostattr"]=$hostattr;
	$ajaxargs["serviceattr"]=$serviceattr;
	$ajaxargs["hoststatustypes"]=$hoststatustypes;
	$ajaxargs["servicestatustypes"]=$servicestatustypes;

	$id="hoststatustable_".random_string(6);
	
	$output.="<div class='hoststatustable' id='".$id."'>\n";
	$output.=get_throbber_html();
	$output.="</div>";

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

	// ajax updater
	$output.='
	<script type="text/javascript">
	$(document).ready(function(){

		get_'.$id.'_content();
			
		$("#'.$id.'").everyTime(30*1000, "timer-'.$id.'", function(i) {
			get_'.$id.'_content();
		});
		
		function get_'.$id.'_content(){
			$("#'.$id.'").each(function(){
				var optsarr = {
					"func": "get_hoststatus_table",
					"args": '.$jargs.'
					}
				var opts=array2json(optsarr);
				get_ajax_data_innerHTML("getxicoreajax",opts,true,this);
				});
			}

	});
	</script>
	';

	//return $output;
	echo $output;
	}
	
	
function get_status_view_filters_html($show,$urlargs,$hostattr,$serviceattr,$hoststatustypes,$servicestatustypes,$url=""){
	global $lstr;
	
	
	if($url=="")
		$theurl=get_current_page();
	else
		$theurl=$url;

	$show_filter=true;

	$output='';
	
	// no filter is being used...
	if($hostattr==0 && ($hoststatustypes==0 || $hoststatustypes==HOSTSTATE_ANY) && $serviceattr==0 && ($servicestatustypes==0 || $servicestatustypes==SERVICESTATE_ANY)){
		//$output.='HA='.$hostattr.', HS='.$hoststatustypes.', SA='.$serviceattr.', SS='.$servicestatustypes;
		return '';
		}
	
	if($show=="openproblems" || $show=="serviceproblems")
		$show="services";
	else if($show=="hostproblems")
		$show="hosts";

	$theurl.="?show=".$show;
	foreach($urlargs as $var => $val){
		if($var=="show" || $var=="hostattr" || $var=="serviceattr" || $var=="hoststatustypes" || $var=="servicestatustypes")
			continue;
		$theurl.="&".$var."=".$val;
		}

	$output.='<BR>';
	$output.='<img src="'.theme_image("filter.png").'"> Filters:';
	
	$filters="";
	
	if($hostattr!=0 || ($hoststatustypes!=0 && $hoststatustypes!=HOSTSTATE_ANY)){
		$filters.=" <b>Host</b>=";
		$filterstrs=array();
		
		if(($hoststatustypes & HOSTSTATE_UP))
			$filterstrs[]="Up";
		if(($hoststatustypes & HOSTSTATE_DOWN))
			$filterstrs[]="Down";
		if(($hoststatustypes & HOSTSTATE_UNREACHABLE))
			$filterstrs[]="Unreachable";
		if(($hostattr & HOSTSTATUSATTR_ACKNOWLEDGED))
			$filterstrs[]="Acknowledged";
		if(($hostattr & HOSTSTATUSATTR_NOTACKNOWLEDGED))
			$filterstrs[]="Not Acknowledged";
		if(($hostattr & HOSTSTATUSATTR_INDOWNTIME))
			$filterstrs[]="In Downtime";
		if(($hostattr & HOSTSTATUSATTR_NOTINDOWNTIME))
			$filterstrs[]="Not In Downtime";
		if(($hostattr & HOSTSTATUSATTR_ISFLAPPING))
			$filterstrs[]="Flapping";
		if(($hostattr & HOSTSTATUSATTR_ISNOTFLAPPING))
			$filterstrs[]="Not Flapping";
		if(($hostattr & HOSTSTATUSATTR_CHECKSENABLED))
			$filterstrs[]="Checks Enabled";
		if(($hostattr & HOSTSTATUSATTR_CHECKSDISABLED))
			$filterstrs[]="Checks Disabled";
		if(($hostattr & HOSTSTATUSATTR_NOTIFICATIONSENABLED))
			$filterstrs[]="Notifications Enabled";
		if(($hostattr & HOSTSTATUSATTR_NOTIFICATIONSDISABLED))
			$filterstrs[]="Notifications Disabled";
		if(($hostattr & HOSTSTATUSATTR_HARDSTATE))
			$filterstrs[]="Hard State";
		if(($hostattr & HOSTSTATUSATTR_SOFTSTATE))
			$filterstrs[]="Soft State";
	
		if(($hostattr & HOSTSTATUSATTR_EVENTHANDLERDISABLED))
			$filterstrs[]="Event Handler Disabled";
		if(($hostattr & HOSTSTATUSATTR_EVENTHANDLERENABLED))
			$filterstrs[]="Event Handler Enabled";
		if(($hostattr & HOSTSTATUSATTR_FLAPDETECTIONDISABLED))
			$filterstrs[]="Flap Detection Disabled";
		if(($hostattr & HOSTSTATUSATTR_FLAPDETECTIONENABLED))
			$filterstrs[]="Flap Detection Enabled";
		if(($hostattr & HOSTSTATUSATTR_PASSIVECHECKSDISABLED))
			$filterstrs[]="Passive Checks Disabled";
		if(($hostattr & HOSTSTATUSATTR_PASSIVECHECKSENABLED))
			$filterstrs[]="Passive Checks Enabled";
		if(($hostattr & HOSTSTATUSATTR_PASSIVECHECK))
			$filterstrs[]="Passive Check";
		if(($hostattr & HOSTSTATUSATTR_ACTIVECHECK))
			$filterstrs[]="Active Check";
		if(($hostattr & HOSTSTATUSATTR_HARDSTATE))
			$filterstrs[]="Hard State";
		if(($hostattr & HOSTSTATUSATTR_SOFTSTATE))
			$filterstrs[]="Soft State";
			
		$x=0;
		foreach($filterstrs as $f){
			if($x>0)
				$filters.=",";
			$filters.=$f;
			$x++;
			}
		}
	
	if($serviceattr!=0 || ($servicestatustypes!=0 && $servicestatustypes!=SERVICESTATE_ANY)){
		//if($filters!="")
		//	$filters.="<BR>";
		$filters.=" <b>Service</b>=";
		$filterstrs=array();
		
		if(($servicestatustypes & SERVICESTATE_OK))
			$filterstrs[]="Ok";
		if(($servicestatustypes & SERVICESTATE_WARNING))
			$filterstrs[]="Warning";
		if(($servicestatustypes & SERVICESTATE_UNKNOWN))
			$filterstrs[]="Unknown";
		if(($servicestatustypes & SERVICESTATE_CRITICAL))
			$filterstrs[]="Critical";
		if(($serviceattr & SERVICESTATUSATTR_ACKNOWLEDGED))
			$filterstrs[]="Acknowledged";
		if(($serviceattr & SERVICESTATUSATTR_NOTACKNOWLEDGED))
			$filterstrs[]="Not Acknowledged";
		if(($serviceattr & SERVICESTATUSATTR_INDOWNTIME))
			$filterstrs[]="In Downtime";
		if(($serviceattr & SERVICESTATUSATTR_NOTINDOWNTIME))
			$filterstrs[]="Not In Downtime";
		if(($serviceattr & SERVICESTATUSATTR_ISFLAPPING))
			$filterstrs[]="Flapping";
		if(($serviceattr & SERVICESTATUSATTR_ISNOTFLAPPING))
			$filterstrs[]="Not Flapping";
		if(($serviceattr & SERVICESTATUSATTR_CHECKSENABLED))
			$filterstrs[]="Checks Enabled";
		if(($serviceattr & SERVICESTATUSATTR_CHECKSDISABLED))
			$filterstrs[]="Checks Disabled";
		if(($serviceattr & SERVICESTATUSATTR_NOTIFICATIONSENABLED))
			$filterstrs[]="Notifications Enabled";
		if(($serviceattr & SERVICESTATUSATTR_NOTIFICATIONSDISABLED))
			$filterstrs[]="Notifications Disabled";
		if(($serviceattr & SERVICESTATUSATTR_HARDSTATE))
			$filterstrs[]="Hard State";
		if(($serviceattr & SERVICESTATUSATTR_SOFTSTATE))
			$filterstrs[]="Soft State";
			
		if(($serviceattr & SERVICESTATUSATTR_EVENTHANDLERDISABLED))
			$filterstrs[]="Event Handler Disabled";
		if(($serviceattr & SERVICESTATUSATTR_EVENTHANDLERENABLED))
			$filterstrs[]="Event Handler Enabled";
		if(($serviceattr & SERVICESTATUSATTR_FLAPDETECTIONDISABLED))
			$filterstrs[]="Flap Detection Disabled";
		if(($serviceattr & SERVICESTATUSATTR_FLAPDETECTIONENABLED))
			$filterstrs[]="Flap Detection Enabled";
		if(($serviceattr & SERVICESTATUSATTR_PASSIVECHECKSDISABLED))
			$filterstrs[]="Passive Checks Disabled";
		if(($serviceattr & SERVICESTATUSATTR_PASSIVECHECKSENABLED))
			$filterstrs[]="Passive Checks Enabled";
		if(($serviceattr & SERVICESTATUSATTR_PASSIVECHECK))
			$filterstrs[]="Passive Check";
		if(($serviceattr & SERVICESTATUSATTR_ACTIVECHECK))
			$filterstrs[]="Active Check";
		if(($serviceattr & SERVICESTATUSATTR_HARDSTATE))
			$filterstrs[]="Hard State";
		if(($serviceattr & SERVICESTATUSATTR_SOFTSTATE))
			$filterstrs[]="Soft State";
			
		$x=0;
		foreach($filterstrs as $f){
			if($x>0)
				$filters.=",";
			$filters.=$f;
			$x++;
			}
		}
	
	$output.=$filters;
	
	$output.=" <a href='".$theurl."'><img src='".theme_image("clearfilter.png")."' alt='".$lstr['ClearFilterAlt']."' title='".$lstr['ClearFilterAlt']."'></a>";
	
		
	return $output;
	}

?>
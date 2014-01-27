<?php
// XI Status Functions
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: status.php 1318 2012-08-15 21:38:02Z mguthrie $

include_once(dirname(__FILE__).'/../componenthelper.inc.php');

include_once(dirname(__FILE__).'/../nagioscore/coreuiproxy.inc.php');


// initialization stuff
pre_init();

// start session
init_session(true);

// grab GET or POST variables 
grab_request_vars();

// check prereqs
check_prereqs();

// check authentication
check_authentication(false);

route_request();

function route_request(){

	$view=grab_request_var("show","");

	// process commands first
	/*
	$submitcommand=grab_request_var("submitcommand","");
	$cmd=grab_request_var("cmd","");
	if(have_value($cmd)==true || have_value($submitcommand)==true)
		process_status_ui_command();
	*/
	
	//echo "VIEW=$view<BR>\n";
	//print_r($request);
	//exit();
	
	switch($view){
		case "process":
			show_monitoring_process();
			break;
		case "performance":
			show_monitoring_performance();
			break;
		case "comments":
			show_comments();
			break;
		case "services":
			show_services();
			break;
		case "hosts":
			show_hosts();
			break;
		case "hostgroups":
			show_hostgroups();
			break;
		case "servicegroups":
			show_servicegroups();
			break;
		case "servicedetail":
			show_service_detail();
			break;
		case "hostdetail":
			show_host_detail();
			break;
		case "tac":
			show_tac();
			break;
		case "outages":
			show_network_outages();
			break;
		case "map":
			show_status_map();
			break;
		case "search":
			search_status_target();
			break;
		default:
			show_services();
			break;
		}
	}
	
	
function show_comments(){
	global $request;
	global $lstr;
	
	do_page_start(array("page_title"=>$lstr['CommentsPageTitle']),true);

?>
	<h1><?php echo $lstr['CommentsPageHeader'];?></h1>
	
	
<div style="margin-top: 25px;">
<?php
	$dargs=array(
		DASHLET_ARGS => array(
			),
		);
	display_dashlet("xicore_comments","",$dargs,DASHLET_MODE_OUTBOARD);
?>
</div>

<?php
	do_page_end(true);
	}
	
	
function show_services(){
	global $request;
	global $lstr;
	
	// check licensing
	licensed_feature_check(true,true);
	
	$show=grab_request_var("show","services");
	$host=grab_request_var("host","");
	$hostgroup=grab_request_var("hostgroup","");
	$servicegroup=grab_request_var("servicegroup","");
	$hostattr=grab_request_var("hostattr",0);
	$serviceattr=grab_request_var("serviceattr",0);
	$hoststatustypes=grab_request_var("hoststatustypes",0);
	$servicestatustypes=grab_request_var("servicestatustypes",0);
	
	$search=grab_request_var("search","");
	$searchbutton=grab_request_var("searchButton","");
	
	// fix search
	if($search==$lstr['SearchBoxText'])
		$search="";

	// fix for "all" options
	if($hostgroup=="all")
		$hostgroup="";
	if($servicegroup=="all")
		$servicegroup="";
	if($host=="all")
		$host="";

	// if user was searching for a host, and no matching services are found, redirect them to the host status screen
	if($search!="" && $searchbutton!=""){
	
		// GET TOTAL SERVICE MATCHES FROM BACKEND...
		$backendargs=array();
		$backendargs["cmd"]="getservicestatus";
		// search by IP address, name, display name - 08/09/2011 EG
		$backendargs["host_name"]="lk:".$search.";name=lk:".$search.";host_address=lk:".$search.";host_display_name=lk:".$search.";display_name=lk:".$search;
		//$backendargs["name"]="ls:".$search.";address=lk:".$search.";display_name=lk:".$search;
		$backendargs["combinedhost"]=true;  // need combined view for host search fields

		$backendargs["limitrecords"]=false;  // don't limit records
		$backendargs["totals"]=1; // only get recordcount		
		
		//print_r($backendargs);
		//exit();
		
		// get result from backend
		$xml=get_xml_service_status($backendargs);
		// how many total services do we have?
		$total_records=0;
		if($xml)
			$total_records=intval($xml->recordcount);

		// redirect to host status screen
		if($total_records==0)
			header("Location: status.php?show=hosts&search=".urlencode($search)."&noservices=1");
		}
		

	$target_text="All services";
	if($hostgroup!="")
		$target_text="Hostgroup: <b>".htmlentities($hostgroup)."</b>";
	if($servicegroup!="")
		$target_text="Servicegroup: <b>".htmlentities($servicegroup)."</b>";
	if($host!="")
		$target_text="Host: <b>".htmlentities($host)."</b>";
	
	do_page_start(array("page_title"=>$lstr['ServiceStatusPageTitle']),true);

?>
<div style="float: left;">
	<h1><?php echo $lstr['ServiceStatusPageHeader'];?></h1>
	<div class="servicestatustargettext"><?php echo $target_text;?></div>
</div>

<?php
	$t1=get_timer();
?>
	
<div style="float: right; margin-top: 10px;">
<div style="float: left; margin-right: 25px;">
<?php
	$dargs=array(
		DASHLET_ARGS => array(
			"host" => $host,
			"hostgroup" => $hostgroup,
			"servicegroup" => $servicegroup,
			"hostattr" => $hostattr,
			"serviceattr" => $serviceattr,
			"hoststatustypes" => $hoststatustypes,
			"servicestatustypes" => $servicestatustypes,
			"show" => $show,
			),
		);
	display_dashlet("xicore_host_status_summary","",$dargs,DASHLET_MODE_OUTBOARD);
?>
</div>
<?php
	$t2=get_timer();
?>
<div style="float: left;">
<?php
	display_dashlet("xicore_service_status_summary","",$dargs,DASHLET_MODE_OUTBOARD);
?>
</div>
<?php
	$t3=get_timer();
?>
</div>	

<br clear="all">

<?php 
	draw_servicestatus_table(); 
?>
<?php
	$t4=get_timer();
?>

<?php
/*
	echo "T1-T2: ".get_timer_diff($t1,$t2)."<BR>";
	echo "T2-T3: ".get_timer_diff($t2,$t3)."<BR>";
	echo "T3-T4: ".get_timer_diff($t3,$t4)."<BR>";
*/
?>

<?php
	do_page_end(true);
	}
	
	
function show_hosts($error=false,$msg=""){
	global $lstr;

	// check licensing
	licensed_feature_check(true,true);
	
	$show=grab_request_var("show","services");
	$host=grab_request_var("host","");
	$hostgroup=grab_request_var("hostgroup","");
	$servicegroup=grab_request_var("servicegroup","");
	$hostattr=grab_request_var("hostattr",0);
	$serviceattr=grab_request_var("serviceattr",0);
	$hoststatustypes=grab_request_var("hoststatustypes",0);
	$servicestatustypes=grab_request_var("servicestatustypes",0);
	
	$noservices=grab_request_var("noservices",0);
	
	// no services found during search - user was redirected
	if($noservices==1){
		$error=false;
		$msg="No matching services found - showing matching hosts instead.";
		}

	// fix for "all" options
	if($hostgroup=="all")
		$hostgroup="";
	if($servicegroup=="all")
		$servicegroup="";
	if($host=="all")
		$host="";

	$target_text="All hosts";
	if($hostgroup!="")
		$target_text="Hostgroup: <b>".htmlentities($hostgroup)."</b>";
	if($servicegroup!="")
		$target_text="Servicegroup: <b>".htmlentities($servicegroup)."</b>";
	if($host!="")
		$target_text="Host: <b>".htmlentities($host)."</b>";
	
	do_page_start(array("page_title"=>$lstr['HostStatusPageTitle']),true);

?>
<div style="float: left;">
	<h1><?php echo $lstr['HostStatusPageHeader'];?></h1>
	<div class="hoststatustargettext"><?php echo $target_text;?></div>
</div>

<div style="float: right; margin-top: 10px;">
<div style="float: left; margin-right: 25px;">
<?php
	$dargs=array(
		DASHLET_ARGS => array(
			"host" => $host,
			"hostgroup" => $hostgroup,
			"servicegroup" => $servicegroup,
			"hostattr" => $hostattr,
			"serviceattr" => $serviceattr,
			"hoststatustypes" => $hoststatustypes,
			"servicestatustypes" => $servicestatustypes,
			"show" => $show,
			),
		);
	display_dashlet("xicore_host_status_summary","",$dargs,DASHLET_MODE_OUTBOARD);
?>
</div>
<div style="float: left;">
<?php
	display_dashlet("xicore_service_status_summary","",$dargs,DASHLET_MODE_OUTBOARD);
?>
</div>
</div>	

<?php
	if(is_array($msg) || $msg!=""){
		echo "<br clear='all'>";
		display_message($error,false,$msg);
		}
?>

<div style="clear: both; padding-top: 10px;">
<?php 
	draw_hoststatus_table(); 
?>
</div>

<?php
	do_page_end(true);
	}
	
	
function show_hostgroups(){
	global $lstr;

	// check licensing
	licensed_feature_check(true,true);
	
	// grab request vars
	$hostgroup=grab_request_var("hostgroup","all");
	$style=grab_request_var("style","overview");

	// performance optimization
	$opt=get_option("use_unified_hostgroup_screens");
	if($opt==1)
		header("Location: ".get_base_url()."includes/components/nagioscore/ui/status.php?hostgroup=".$hostgroup."&style=".$style);


	do_page_start(array("page_title"=>$lstr['HostGroupStatusPageTitle']),true);
	
	$target_text="";
	switch($style){
		case "summary":
			$target_text="Summary View";
			break;
		case "overview":
			$target_text="Overview";
			break;
		case "grid":
			$target_text="Grid View";
			break;
		default:
			break;
		}

?>
<div style="float: left;">
	<h1><?php echo $lstr['HostGroupStatusPageHeader'];?></h1>
	<div class="servicestatustargettext"><?php echo $target_text;?></div>
	
	<?php draw_hostgroup_viewstyle_links($hostgroup);?>
</div>

<div style="float: right; margin-top: 10px;">
<div style="float: left; margin-right: 25px;">
<?php
	$dargs=array(
		DASHLET_ARGS => array(
			"hostgroup" => $hostgroup,
			"show" => "services",
			),
		);
	display_dashlet("xicore_host_status_summary","",$dargs,DASHLET_MODE_OUTBOARD);
?>
</div>
<div style="float: left;">
<?php
	display_dashlet("xicore_service_status_summary","",$dargs,DASHLET_MODE_OUTBOARD);
?>
</div>
</div>	

<div style="clear: both; margin-bottom: 35px;"></div>

<?php
	if($style=="summary"){
		$dargs=array(
			DASHLET_ARGS => array(
				"style" => $style,
				),
			);
		display_dashlet("xicore_hostgroup_status_summary","",$dargs,DASHLET_MODE_OUTBOARD);
		}
	
	// overview or grid styles
	else{
		$args=array(
			"orderby" => "hostgroup_name:a",
			);
		if($hostgroup!="" && $hostgroup!="all")
			$args["hostgroup_name"]=$hostgroup;
		$xml=get_xml_hostgroup_objects($args);
		
		if($xml){
			foreach($xml->hostgroup as $hg){
				$hgname=strval($hg->hostgroup_name);
				$hgalias=strval($hg->alias);
				//echo "HG: $hgname<BR>";
				
				echo "<div class=hostgroup".$style."-hostgroup>";
				$dargs=array(
					DASHLET_ARGS => array(
						"hostgroup" => $hgname,
						"hostgroup_alias" => $hgalias,
						"style" => $style,
						),
					);
				display_dashlet("xicore_hostgroup_status_".$style,"",$dargs,DASHLET_MODE_OUTBOARD);
				echo "</div>";
				}
			}
		}
?>

<br clear="all">

<?php
	$url="status.php?noheader&hostgroup=".urlencode($hostgroup)."&style=".urlencode($style);

	$args=array(
		"url" => $url,
		);

	// build args for javascript
	$n=0;
	$jargs="{";
	foreach($args as $var => $val){
		if($n>0)
			$jargs.=", ";
		$jargs.="\"$var\" : \"$val\"";
		$n++;
		}
	$jargs.="}";

	$id="nagioscore_cgi_output_".random_string(6);
	$output='
	<div class="nagioscore_cgi_output" id="'.$id.'">
	'.xicore_ajax_get_nagioscore_cgi_html($args).'
	</div><!--nagioscore_cgi_output-->
	<script type="text/javascript">
	$(document).ready(function(){
		$("#'.$id.'").everyTime(15*1000, "timer-'.$id.'", function(i) {
		var optsarr = {
			"func": "get_nagioscore_cgi_html",
			"args": '.$jargs.'
			}
		var opts=array2json(optsarr);
		get_ajax_data_innerHTML("getxicoreajax",opts,true,this);
		});
		
	});
	</script>
	';
	
	//if($style!="summary")
		//$output="";
?>
	<!--
	<div class="hostgroupstatusview">
	<?php //echo $output;?>
	</div>
	//-->


<?php
	do_page_end(true);
	}
	
	
function show_servicegroups(){
	global $lstr;
	
	// check licensing
	licensed_feature_check(true,true);
	
	// grab request vars
	$servicegroup=grab_request_var("servicegroup","all");
	$style=grab_request_var("style","overview");
	
	// performance optimization
	$opt=get_option("use_unified_servicegroup_screens");
	if($opt==1)
		header("Location: ".get_base_url()."includes/components/nagioscore/ui/status.php?servicegroup=".$servicegroup."&style=".$style);


	do_page_start(array("page_title"=>$lstr['ServiceGroupStatusPageTitle']),true);
	
	$target_text="";
	switch($style){
		case "summary":
			$target_text="Summary View";
			break;
		case "overview":
			$target_text="Overview";
			break;
		case "grid":
			$target_text="Grid View";
			break;
		default:
			break;
		}

?>
<div style="float: left;">
	<h1><?php echo $lstr['ServiceGroupStatusPageHeader'];?></h1>
	<div class="servicestatustargettext"><?php echo $target_text;?></div>
	
	<?php draw_servicegroup_viewstyle_links($servicegroup);?>
</div>

<div style="float: right; margin-top: 10px;">
<div style="float: left; margin-right: 25px;">
<?php
	$dargs=array(
		DASHLET_ARGS => array(
			"servicegroup" => $servicegroup,
			"show" => "services",
			),
		);
	display_dashlet("xicore_host_status_summary","",$dargs,DASHLET_MODE_OUTBOARD);
?>
</div>
<div style="float: left;">
<?php
	display_dashlet("xicore_service_status_summary","",$dargs,DASHLET_MODE_OUTBOARD);
?>
</div>
</div>	

<div style="clear: both; margin-bottom: 35px;"></div>
<?php
	if($style=="summary"){
		$dargs=array(
			DASHLET_ARGS => array(
				"style" => $style,
				),
			);
		display_dashlet("xicore_servicegroup_status_summary","",$dargs,DASHLET_MODE_OUTBOARD);

		}
	
	// overview or grid styles
	else{
		$args=array(
			"orderby" => "servicegroup_name:a",
			);
		if($servicegroup!="" && $servicegroup!="all")
			$args["servicegroup_name"]=$servicegroup;
		$xml=get_xml_servicegroup_objects($args);
		
		if($xml){
			foreach($xml->servicegroup as $sg){
				$sgname=strval($sg->servicegroup_name);
				$sgalias=strval($sg->alias);
				
				echo "<div class=servicegroup".htmlentities($style)."-servicegroup>";
				$dargs=array(
					DASHLET_ARGS => array(
						"servicegroup" => $sgname,
						"servicegroup_alias" => $sgalias,
						"style" => $style,
						),
					);
				display_dashlet("xicore_servicegroup_status_".$style,"",$dargs,DASHLET_MODE_OUTBOARD);
				echo "</div>";
				}
			}
		}
?>
<br clear="all">


<?php
	$url="status.php?noheader&servicegroup=".urlencode($servicegroup)."&style=".urlencode($style);

	$args=array(
		"url" => $url,
		);

	// build args for javascript
	$n=0;
	$jargs="{";
	foreach($args as $var => $val){
		if($n>0)
			$jargs.=", ";
		$jargs.="\"$var\" : \"$val\"";
		$n++;
		}
	$jargs.="}";

	$id="nagioscore_cgi_output_".random_string(6);
	$output='
	<div class="nagioscore_cgi_output" id="'.$id.'">
	'.xicore_ajax_get_nagioscore_cgi_html($args).'
	</div><!--nagioscore_cgi_output-->
	<script type="text/javascript">
	$(document).ready(function(){
		$("#'.$id.'").everyTime(15*1000, "timer-'.$id.'", function(i) {
		var optsarr = {
			"func": "get_nagioscore_cgi_html",
			"args": '.$jargs.'
			}
		var opts=array2json(optsarr);
		get_ajax_data_innerHTML("getxicoreajax",opts,true,this);
		});
		
	});
	</script>
	';
	
	//if($style!="summary")
		//$output="";
?>
	<!--
	<div class="servicegroupstatusview">
	<?php //echo $output;?>
	</div>
	//-->

<?php
	do_page_end(true);
	}
	

	
function show_tac(){
	global $lstr;

	do_page_start(array("page_title"=>$lstr['TacticalOverviewPageTitle']),true);

?>
	<h1><?php echo $lstr['TacticalOverviewPageHeader'];?></h1>

<?php
	do_page_end(true);
	}
	
	
function show_open_problems(){
	global $lstr;

	do_page_start(array("page_title"=>$lstr['OpenProblemsPageTitle']),true);

?>
	<h1><?php echo $lstr['OpenProblemsPageHeader'];?></h1>

<?php
	do_page_end(true);
	}
	
	
function show_host_problems(){
	global $lstr;

	do_page_start(array("page_title"=>$lstr['HostProblemsPageTitle']),true);

?>
	<h1><?php echo $lstr['HostProblemsPageHeader'];?></h1>

<?php
	do_page_end(true);
	}
	
	
function show_service_problems(){
	global $lstr;

	do_page_start(array("page_title"=>$lstr['ServiceProblemsPageTitle']),true);

?>
	<h1><?php echo $lstr['ServiceProblemsPageHeader'];?></h1>

<?php
	do_page_end(true);
	}
	
	
function show_network_outages(){
	global $lstr;

	do_page_start(array("page_title"=>$lstr['NetworkOutagesPageTitle']),true);

?>
	<h1><?php echo $lstr['NetworkOutagesPageHeader'];?></h1>
	
<?php
/*
	$url="outages.php?noheader";

	$args=array(
		"url" => $url,
		);

	// build args for javascript
	$n=0;
	$jargs="{";
	foreach($args as $var => $val){
		if($n>0)
			$jargs.=", ";
		$jargs.="\"$var\" : \"$val\"";
		$n++;
		}
	$jargs.="}";

	$id="nagioscore_cgi_output_".random_string(6);
	$output='
	<div class="nagioscore_cgi_output" id="'.$id.'">
	'.xicore_ajax_get_nagioscore_cgi_html($args).'
	</div><!--nagioscore_cgi_output-->
	<script type="text/javascript">
	$(document).ready(function(){
		$("#'.$id.'").everyTime(15*1000, "timer-'.$id.'", function(i) {
		var optsarr = {
			"func": "get_nagioscore_cgi_html",
			"args": '.$jargs.'
			}
		var opts=array2json(optsarr);
		get_ajax_data_innerHTML("getxicoreajax",opts,true,this);
		});
		
	});
	</script>
	';
	*/
?>
	<!--
	<div class="networkoutages">
	<?php //echo $output;?>
	</div>
	//-->
	
	<?php //echo xicore_ajax_get_network_outages_html();?>
	
	<div style="float: left;">
<?php
	$dargs=array(
		DASHLET_ARGS => array(
			),
		);
	display_dashlet("xicore_network_outages","",$dargs,DASHLET_MODE_OUTBOARD);
?>
	</div>

<?php
	do_page_end(true);
	}
	
	
function show_status_map(){
	global $request;
	global $lstr;

	do_page_start(array("page_title"=>$lstr['StatusMapPageTitle']),true);

?>
	<h1><?php echo $lstr['StatusMapPageHeader'];?></h1>
	
	<?php draw_statusmap_viewstyle_links();?>
	
<?php
	$url="statusmap.php?noheader";
	// add most request args to url
	foreach($request as $var => $val){
		if($var=="show")
			$continue;
		$url.="&$var=$val";
		}
	//$html=coreuiproxy_get_embedded_cgi_output($url);
	//echo $html;
?>
<?php

	$args=array(
		"url" => $url,
		);
	// build args for javascript
	$n=0;
	$jargs="{";
	foreach($args as $var => $val){
		if($n>0)
			$jargs.=", ";
		$jargs.="\"$var\" : \"$val\"";
		$n++;
		}
	$jargs.="}";

	$id="nagioscore_cgi_output_".random_string(6);
	$output='
	<div class="nagioscore_cgi_output" id="'.$id.'">
	'.xicore_ajax_get_nagioscore_cgi_html($args).'
	</div><!--nagioscore_cgi_output-->
	<script type="text/javascript">
	$(document).ready(function(){
		$("#'.$id.'").everyTime(30*1000, "timer-'.$id.'", function(i) {
		var optsarr = {
			"func": "get_nagioscore_cgi_html",
			"args": '.$jargs.'
			}
		var opts=array2json(optsarr);
		get_ajax_data_innerHTML("getxicoreajax",opts,true,this);
		});
		
	});
	</script>
	';
?>
	<div class="statusmap">
	<?php echo $output;?>
	</div>

<?php
	do_page_end(true);
	}
	
	
function search_status_target(){
	}
	
	
function show_not_authorized_for_object_page(){
	global $lstr;

	do_page_start(array("page_title"=>$lstr['NotAuthorizedPageTitle']),true);

?>
	<h1><?php echo $lstr['NotAuthorizedPageHeader'];?></h1>

	<?php echo $lstr['NotAuthorizedForObjectMessage'];?>
	
<?php
	do_page_end(true);
	exit();
	}

/*
function show_object_does_not_exist_page(){
	global $lstr;

	do_page_start(array("page_title"=>$lstr['ObjectDoesntExistPageTitle']),true);

?>
	<h1><?php echo $lstr['ObjectDoesntExistPageHeader'];?></h1>

	<?php echo $lstr['ObjectDoesntExistMessage'];?>
	
<?php
	do_page_end(true);
	}
*/


function show_monitoring_process(){
	global $lstr;

	do_page_start(array("page_title"=>$lstr['MonitoringProcessPageTitle']),true);

?>
	<h1><?php echo $lstr['MonitoringProcessPageHeader'];?></h1>

	<div style="float: left; margin: 0 30px 30px 0;">
<?php
	display_dashlet("xicore_monitoring_process","",null,DASHLET_MODE_OUTBOARD);
?>
	</div>
	
	<div style="float: left; margin: 0 30px 30px 0;">
<?php
	display_dashlet("xicore_eventqueue_chart","",null,DASHLET_MODE_OUTBOARD);
?>
	</div>
	
<?php
	do_page_end(true);
	}
	
function show_monitoring_performance(){
	global $lstr;

	do_page_start(array("page_title"=>$lstr['MonitoringPerformancePageTitle']),true);

?>
	<h1><?php echo $lstr['MonitoringPerformancePageHeader'];?></h1>

	<div style="float: left; margin: 0 30px 30px 0;">
<?php
	display_dashlet("xicore_monitoring_stats","",null,DASHLET_MODE_OUTBOARD);
?>
	</div>
	
	<div style="float: left; margin: 0 30px 30px 0;">
<?php
	display_dashlet("xicore_monitoring_perf","",null,DASHLET_MODE_OUTBOARD);
?>
	</div>
	

	
<?php
	do_page_end(true);
	}
	


?>
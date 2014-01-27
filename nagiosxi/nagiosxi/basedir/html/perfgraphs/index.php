<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: index.php 1109 2012-04-03 14:12:13Z mguthrie $

require_once(dirname(__FILE__).'/../includes/common.inc.php');

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

route_request();

function route_request(){

	$cmd=grab_request_var("cmd","");
	
	switch($cmd){
		default:
			display_page();
			break;
		}
	}


function display_page(){
	draw_perfgraphs();
	}

	
function draw_perfgraphs_sidebar($indashboard=false){
	global $lstr;

	if($indashboard==true)
		return;

	$search=grab_request_var("search","");

	$host=grab_request_var("host","");
	$service=grab_request_var("service","");
	$startdate=grab_request_var("startdate","");
	$enddate=grab_request_var("enddate","");
	$mode=grab_request_var("mode",PERFGRAPH_MODE_HOSTSOVERVIEW);
	$host_id = grab_request_var('host_id',''); 
	$service_id = grab_request_var('service_id',''); 
	$source = grab_request_var('source',1);

	$view=grab_request_var("view",get_perfgraph_default_setting("view",PNP_VIEW_DEFAULT));

	/*
	$u=get_component_credential("pnp","username");
	echo "USER: $u<BR>";
	$p=get_component_credential("pnp","password");
	echo "PASS: $p<BR>";
	*/

	// display search box
	$searchclass="textfield";
	if(have_value($search)==true){
		$searchstring=$search;
		$searchclass.=" newdata";
		}
	else
		$searchstring=$lstr['SearchBoxText'];
?>
	<div class="perfgraphsidebar">

	<div class="perfgraphsidebartitle">Host Selection</div>
	<div class="perfgraphsuggest">
	<form action="" method="get">
	<input type="text" size="15" name="search" id="searchBox" value="<?php echo encode_form_val($searchstring);?>" class="<?php echo $searchclass;?>" />
	<input type="submit" class="submitbutton" name="searchButton" value="<?php echo $lstr['GoButton'];?>" id="searchButton">
	</div>
	
	<script type="text/javascript">
	$(document).ready(function(){
		$('#searchBox').click(function(){
			if($('#searchBox').val()=='<?php echo $lstr['SearchBoxText'];?>'){
				$('#searchBox').val('');
				}
			});
		
		});
	</script>
	
<?php

	if($mode==PERFGRAPH_MODE_HOSTOVERVIEW || $mode==PERFGRAPH_MODE_SERVICEDETAIL){
?>
	<div class="perfgraphsidebartitle">Graph Selection</div>
	<hr />
	<div class='perfgraphnav'>
<?php
		}
		
	switch($mode){
		case PERFGRAPH_MODE_HOSTOVERVIEW:
			$url=build_url_from_current(array(
				"mode"=>PERFGRAPH_MODE_HOSTSOVERVIEW,
				"service"=>"",
				"host"=>"",
				"search"=>"",
				));
			echo "<a href='".$url."'>All Host Graphs</a>";
			break;
		case PERFGRAPH_MODE_SERVICEDETAIL:
			$url=build_url_from_current(array(
				"mode"=>PERFGRAPH_MODE_HOSTOVERVIEW,
				"service"=>"",
				));
			echo "<a href='".$url."'>All ".$host." Graphs</a>";
			break;
		default:
			break;
		}


	if($mode==PERFGRAPH_MODE_HOSTOVERVIEW || $mode==PERFGRAPH_MODE_SERVICEDETAIL){
?>
	</div>
<?php
		}
?>
	
	<hr>
	
	<div class="perfgraphtimeranges">
	<div class="perfgraphsidebartitle">Standard Periods</div>
	<ul class="perfgraphtimeranges">
	<li><a href="<?php echo build_url_from_current(array("view"=>0,"start"=>"","end"=>"","startdate"=>"","enddate"=>""));?>">4 Hour View</a></li>
	<li><a href="<?php echo build_url_from_current(array("view"=>1,"start"=>"","end"=>"","startdate"=>"","enddate"=>""));?>">24 Hour View</a></li>
	<li><a href="<?php echo build_url_from_current(array("view"=>2,"start"=>"","end"=>"","startdate"=>"","enddate"=>""));?>">Week View</a></li>
	<li><a href="<?php echo build_url_from_current(array("view"=>3,"start"=>"","end"=>"","startdate"=>"","enddate"=>""));?>">Month View</a></li>
	<li><a href="<?php echo build_url_from_current(array("view"=>4,"start"=>"","end"=>"","startdate"=>"","enddate"=>""));?>">Year View</a></li>
	</ul>
	</div>
	
	<br>
	<b>Custom Period</b><br>
	<label>Start Date:</label><br>
	<div class="perfgraphdatepicker" id="startdatepicker"><img src="<?php echo theme_image("calendar_small.png");?>" ></div><div id="startdatepickercontainer"></div>
	
	<script type="text/javascript">
		var perfgraphurl="<?php echo build_url_from_current(array("start"=>"","end"=>""));?>";
	</script>
	<input class="textfield" type="text" id='startdateBox' name="startdate" value="<?php echo encode_form_val(get_datetime_from_timestring($startdate)); ?>" size="16" />

<?php
	$auto_start_date=date('m/d/Y H:i:s',strtotime('yesterday'));
?>
	
	<script type="text/javascript">
	$(document).ready(function(){
		$('#startdateBox').click(function(){
			if($('#startdateBox').val()==''){
				$('#startdateBox').val('<?php echo $auto_start_date;?>');
				}
			});
		
		});
	</script>	

	<br clear="all">
	<label>End Date:</label><br>
	<div class="perfgraphdatepicker" id="enddatepicker"><img src="<?php echo theme_image("calendar_small.png");?>" ></div><div id="enddatepickercontainer"></div>
	
	<script type="text/javascript">
		var perfgraphurl="<?php echo build_url_from_current(array("start"=>"","end"=>""));?>";
	</script>
	<input class="textfield" type="text" id='enddateBox' name="enddate" value="<?php echo encode_form_val(get_datetime_from_timestring($enddate)); ?>" size="16" />

<?php
//	$auto_start_date=date('m/d/Y',strtotime('yesterday'));
	$auto_end_date=date('m/d/Y H:i:s');
?>
	
	<script type="text/javascript">
	$(document).ready(function(){
		$('#enddateBox').click(function(){
			if($('#enddateBox').val()==''){
				$('#enddateBox').val('<?php echo $auto_end_date;?>');
				}
			});
		
		});
	</script>	
	
	<br clear="all">
	<input type="submit" class="submitbutton" name="searchButton" value="<?php echo $lstr['GoButton'];?>" id="searchButton" />
	<!-- added hidden inputs, fixes bug #202 -->
	<input type='hidden' name='host' value='<?php echo htmlentities($host);?>' />
	<input type='hidden' name='host_id' value='<?php echo $host_id;?>' />
	<input type='hidden' name='service_id' value='<?php echo $service_id;?>' />
	<input type='hidden' name='service' value='<?php echo htmlentities($service);?>' />
	<input type='hidden' name='source' value='<?php echo $source;?>' />
	<input type='hidden' name='mode' value='<?php echo $mode;?>' />

	</form>	

	
	</div>
<?php
	}
	
function do_perfgraphs_page_titles(){
	global $lstr;

	$host=grab_request_var("host","");
	$service=grab_request_var("service","");
	$view=grab_request_var("view",get_perfgraph_default_setting("view",PNP_VIEW_DEFAULT));
	$mode=grab_request_var("mode",PERFGRAPH_MODE_HOSTSOVERVIEW);
	$start=grab_request_var("start","");
	$end=grab_request_var("end","");
	
	// custom start date
	$startdate=grab_request_var("startdate","");
	if($startdate!=""){
		$start=strtotime($startdate);
		}
	// custom end date
	$enddate=grab_request_var("enddate","");
	if($enddate!=""){
		$end=strtotime($enddate);
		}
		
	// custom dates
	if($startdate!="" && $enddate!="")
		$view=PNP_VIEW_CUSTOM;
		
	//bug fix for potential blank view of graphs on initial load -MG	
	if($start=="" && $end=="" && $view==PNP_VIEW_CUSTOM)
		$view = PNP_VIEW_DEFAULT;	
	
	$title="";
	$subtitle="";

	switch($mode){
		case PERFGRAPH_MODE_HOSTSOVERVIEW:
			$title="Host Performance Graphs";
			break;
		case PERFGRAPH_MODE_HOSTOVERVIEW:
			$title=$host." Performance Graphs";
			break;
		case PERFGRAPH_MODE_SERVICEDETAIL:
			if($service=="_HOST_")
				$servicename="Host";
			else
				$servicename=$service;
			$title=$host." ".$servicename." Performance Graphs";
			break;
		default:
			break;
		}
		
	switch($view){
		case PNP_VIEW_4HOURS:
			$title.=" - 4 Hour View";
			if($end!="")
				$start=$end-(60*60*4);
			break;
		case PNP_VIEW_1DAY:
			$title.=" - 24 Hour View";
			if($end!="")
				$start=$end-(60*60*24);
			break;
		case PNP_VIEW_1WEEK:
			$title.=" - 1 Week View";
			if($end!="")
				$start=$end-(60*60*24*7);
			break;
		case PNP_VIEW_1MONTH:
			$title.=" - 1 Month View";
			if($end!="")
				$start=$end-(60*60*24*30);
			break;
		case PNP_VIEW_1YEAR:
			$title.=" - 1 Year View";
			if($end!="")
				$start=$end-(60*60*24*265);
			break;
		case PNP_VIEW_CUSTOM:
			$title.=" - Custom Period";
			}
			
	if($end!=""){
	
		$url=build_url_from_current(array("start"=>"","end"=>"","startdate"=>"","enddate"=>""));
		
		$daterange="<b>".get_datetime_string($start)."</b> - <b>".get_datetime_string($end)."</b>";
		
		$subtitle=$daterange."&nbsp;<a href='".$url."'><img src='".theme_image("b_clearsearch.png")."' title='".$lstr['ClearDateAlt']."'></a>";
		}

	echo "<div class='perfgraphstitle'>".$title."</div>\n";
	echo "<div class='perfgraphssubtitle'>".$subtitle."</div>\n";
	}
	
	
function do_perfgraphs_page_start(){
	global $lstr;

	// check licensing
	licensed_feature_check(true,true);
	
	do_page_start(array(
		"page_title" => $lstr['PerformanceGraphsPageTitle'],
		"page_id" => "perfgraphspage",
		),
		true);

	echo "<div class='perfgraphspage'>\n";
	echo "<h1>".$lstr['PerformanceGraphsPageHeader']."</h1>\n";

	draw_perfgraphs_sidebar();

	echo "<div class='perfgraphscontainer'>\n";
	}
	
function do_perfgraphs_page_end(){

	echo "</div><!--perfgraphscontainer-->\n";
	echo "</div><!--perfgraphspage-->\n";
	
	do_page_end(true);
	}

	
function draw_perfgraphs($indashboard=false){
	global $lstr;
	
	// get request vars
	$mode=grab_request_var("mode",PERFGRAPH_MODE_HOSTSOVERVIEW);

	switch($mode){
		case PERFGRAPH_MODE_HOSTSOVERVIEW:
			draw_hosts_overview_graphs();
			break;
		case PERFGRAPH_MODE_HOSTOVERVIEW:
			draw_host_overview_graphs();
			break;
		case PERFGRAPH_MODE_SERVICEDETAIL:
			draw_service_detail_graphs();
			break;
		default:
			break;
		}
	}
	
function get_perfgraph_default_setting($setting,$default=""){

	$value=$default;
	
	//echo "SETTING=$setting<BR>";
	
	// get saved value (user's preference)
	$savedval=get_user_meta(0,"perfgraph_default_".$setting);
	if($savedval!=null){
		//echo "USED SAVED.  VAL=$savedval<BR>";
		$value=$savedval;
		}
	
	// save new setting (use value passed in client request)
	$requestval=grab_request_var($setting,"");
	if($requestval!=""){
		$value=$requestval;
		set_user_meta(0,"perfgraph_default_".$setting,$value,false);
		//echo "SAVED $value!<BR>";
		}
		
	//echo "VALUE: $value<BR><BR>";

	return $value;
	}
	
function draw_service_detail_graphs(){
	global $lstr;
	
	// get request vars
	$search=grab_request_var("search","");
	$host=grab_request_var("host","");
	$host_id=grab_request_var("host_id",-1);
	$service=grab_request_var("service","");
	$service_id=grab_request_var("service_id",-1);
	$source=grab_request_var("source",1);
	$view=grab_request_var("view",get_perfgraph_default_setting("view",PNP_VIEW_DEFAULT));
	$start=grab_request_var("start","");
	$end=grab_request_var("end","");
	$mode=grab_request_var("mode",PERFGRAPH_MODE_HOSTSOVERVIEW);
	$sortby=grab_request_var("sortby","host_name:a");
	$records=grab_request_var("records",get_perfgraph_default_setting("records",5));

	// custom start date
	$startdate=grab_request_var("startdate","");
	if($startdate!=""){
		$start=strtotime($startdate);
		}
	// custom end date
	$enddate=grab_request_var("enddate","");
	if($enddate!=""){
		$end=strtotime($enddate);
		}
		
	// custom dates
	if($startdate!="" && $enddate!="")
		$view=PNP_VIEW_CUSTOM;
		
	//bug fix for potential blank view of graphs on initial load -MG	
	if($start=="" && $end=="" && $view==PNP_VIEW_CUSTOM)
		$view = PNP_VIEW_DEFAULT;		
				
	// fix search...
	if($search==$lstr['SearchBoxText'])
		$search="";

	do_perfgraphs_page_start();

	do_perfgraphs_page_titles();

	$sources=perfdata_get_service_sources($host,$service);
	foreach($sources as $s){
	
		echo "<div class='serviceperfgraphcontainer'>\n";
		
		$dargs=array(
			DASHLET_ADDTODASHBOARDTITLE => "Add This Performance Graph To A Dashboard",
			DASHLET_ARGS => array(
				"hostname" => $host,
				"host_id" => $host_id,
				"servicename" => $service,
				"service_id" => $service_id,
				"source" => $s["id"],
				"sourcename" => $s["name"],
				"sourcetemplate" => $s["template"],
				"view" => $view,
				"start" => $start,
				"end" => $end,
				"startdate" => $startdate,
				"enddate" => $enddate,
				"width" => "",
				"height" => "",
				"mode" => PERFGRAPH_MODE_SERVICEDETAIL,
				),
			DASHLET_TITLE => $host." ".$service." Performance Graph",
			);
		
		display_dashlet("xicore_perfdata_chart","",$dargs,DASHLET_MODE_OUTBOARD);
		
		if($service=="_HOST_")
			draw_host_perfgraph_links($host);
		else
			draw_service_perfgraph_links($host,$service);
		
		echo "</div>";
		}
		
	if(count($sources)==0)
		echo $lstr['NoPerformanceGraphDataSourcesMessage'];

	do_perfgraphs_page_end();
	}
	
function draw_host_overview_graphs(){
	global $lstr;
	
	// get request vars
	$search=grab_request_var("search","");
	$host=grab_request_var("host","");
	$service=grab_request_var("service","");
	$source=grab_request_var("source",1);
	$view=grab_request_var("view",get_perfgraph_default_setting("view",PNP_VIEW_DEFAULT));
	$start=grab_request_var("start","");
	$end=grab_request_var("end","");
	$mode=grab_request_var("mode",PERFGRAPH_MODE_HOSTSOVERVIEW);
	$sortby=grab_request_var("sortby","host_name:a");
	$records=grab_request_var("records",get_perfgraph_default_setting("records",5));
	$page=grab_request_var("page",1);
	
	// custom start date
	$startdate=grab_request_var("startdate","");
	if($startdate!=""){
		$start=strtotime($startdate);
		}
	// custom end date
	$enddate=grab_request_var("enddate","");
	if($enddate!=""){
		$end=strtotime($enddate);
		}
		
	// custom dates
	if($startdate!="" && $enddate!="")
		$view=PNP_VIEW_CUSTOM;
		
	//bug fix for potential blank view of graphs on initial load -MG	
	if($start=="" && $end=="" && $view==PNP_VIEW_CUSTOM)
		$view = PNP_VIEW_DEFAULT;				

	// fix search...
	if($search==$lstr['SearchBoxText'])
		$search="";

	// first get total records
	$args=array(
		"cmd" => "getservicestatus",
		"host_name" => $host,
		"brevity" => 2,
		);
	$xml=get_xml_service_status($args);
	$total_records=0;
	if($xml)
		$total_records=intval($xml->recordcount);
	// adjust records to account for host performance graph
	$total_records++;
	
	// get paging info - reset page number if necessary
	$pager_args=array(
		//"sortby" => $sortby,
		//"search" => $search,
		"host" => $host,
		"source" => $source,
		"view" => $view,
		"mode" => $mode,
		"start" => $start,
		"end" => $end,		
		"startdate" => $startdate,
		"enddate" => $enddate,
		);
	$pager_results=get_table_pager_info("",$total_records,$page,$records,$pager_args);

	// adjust start/end records to compensate for first record being host performance graph
	$first_record=(($pager_results["current_page"]-1)*$records);
	$records_this_page=$records;
	if($page==1)
		$records_this_page--;
	else
		$first_record--;
	
	// run record-limiting query
	$args=array(
		"cmd" => "getservicestatus",
		"host_name" => $host,
		"brevity" => 2,
		"records" => $records_this_page.":".$first_record,
		);
	//$xml=get_backend_xml_data($args);
	$xml=get_xml_service_status($args);
	//print_r($xml);

	
	do_perfgraphs_page_start();

	do_perfgraphs_page_titles();
	
?>
	<div class="recordcounttext">
	<?php
	$clear_args=array(
		//"search" => "",
		"host" => $host,
		"source" => $source,
		"view" => $view,
		"mode" => $mode,
		"start" => $start,
		"end" => $end,
		"startdate" => $startdate,
		"enddate" => $enddate,
		);
	echo table_record_count_text($pager_results,$search,true,$clear_args);
	?>
	</div>
	
	<div class="perfgraphsheader"></div>
	
<?php

	if($page==1){
		// primary host performance graph
		echo "<div class='serviceperfgraphcontainer'>\n";
		$dargs=array(
			DASHLET_ADDTODASHBOARDTITLE => "Add This Performance Graph To A Dashboard",
			DASHLET_ARGS => array(
				"host_id" => get_host_id($host),
				"hostname" => $host,
				"servicename" => "_HOST_",
				"source" => $source,
				"view" => $view,
				"start" => $start,
				"end" => $end,
				"startdate" => $startdate,
				"enddate" => $enddate,
				"width" => "",
				"height" => "",
				"mode" => PERFGRAPH_MODE_HOSTOVERVIEW,
				),
			DASHLET_TITLE => $host." Host Performance Graph",
			);
		display_dashlet("xicore_perfdata_chart","",$dargs,DASHLET_MODE_OUTBOARD);
		draw_host_perfgraph_links($host);
		echo "</div>";
		}

	// loop over all services
	foreach($xml->servicestatus as $s){
	
		$hostname=strval($s->host_name);
		$servicename=strval($s->name);
	
		// skip this if the service doesn't have any perfdata
		if(perfdata_chart_exists($hostname,$servicename)==false)
			continue;
	
		echo "<div class='serviceperfgraphcontainer'>\n";
		
		$dargs=array(
			DASHLET_ADDTODASHBOARDTITLE => "Add This Performance Graph To A Dashboard",
			DASHLET_ARGS => array(
				"service_id" => intval($s->service_id),
				"hostname" => $hostname,
				"servicename" => $servicename,
				"source" => $source,
				"view" => $view,
				"start" => $start,
				"end" => $end,
				"startdate" => $startdate,
				"enddate" => $enddate,
				"width" => "",
				"height" => "",
				"mode" => PERFGRAPH_MODE_HOSTOVERVIEW,
				),
			DASHLET_TITLE => $hostname." ".$servicename." Performance Graph",
			);
		
		display_dashlet("xicore_perfdata_chart","",$dargs,DASHLET_MODE_OUTBOARD);
		
		draw_service_perfgraph_links($hostname,$servicename);
		
		echo "</div>";
		}
?>

	<div class="perfgraphsfooter"></div>

	<div class='recordpagerlinks'>
	<form method="get" action="">
	<?php
		$opts=array(
			"search" => $search,
			"host" => $host,
			"mode" => $mode,
			"view" => $view,
			"start" => $start,
			"end" => $end,
			"startdate" => $startdate,
			"enddate" => $enddate,
			);
	?>
	<?php table_record_pager($pager_results,null,$opts);?>
	</form>
	</div>

<?php
	do_perfgraphs_page_end();
	}


	
function draw_hosts_overview_graphs(){
	global $lstr;
	global $request;
	
	// get request vars
	$search=grab_request_var("search","");
	$host=grab_request_var("host","");
	$service=grab_request_var("service","");
	$source=grab_request_var("source",1);
	$view=grab_request_var("view",get_perfgraph_default_setting("view",PNP_VIEW_DEFAULT));
	$start=grab_request_var("start","");
	$end=grab_request_var("end","");
	$mode=grab_request_var("mode",PERFGRAPH_MODE_HOSTSOVERVIEW);
	$sortby=grab_request_var("sortby","host_name:a");
	$records=grab_request_var("records",get_perfgraph_default_setting("records",5));
	$page=grab_request_var("page",1);

	// custom start date
	$startdate=grab_request_var("startdate","");
	if($startdate!=""){
		$start=strtotime($startdate);
		}
	// custom end date
	$enddate=grab_request_var("enddate","");
	if($enddate!=""){
		$end=strtotime($enddate);
		}
		
	// custom dates
	if($startdate!="" && $enddate!="")
		$view=PNP_VIEW_CUSTOM;

	//bug fix for potential blank view of graphs on initial load -MG	
	if($start=="" && $end=="" && $view==PNP_VIEW_CUSTOM)
		$view = PNP_VIEW_DEFAULT;				
	
	// fix search...
	if($search==$lstr['SearchBoxText'])
		$search="";


	// first get total records
	/*
	$args=array(
		"cmd" => "gethoststatus",
		"brevity" => 2,
		);
	if(have_value($search)==true)
		$args["host_name"]="lks:".$search;
	if(have_value($host)==true)  // specific host
		$args["host_name"]=$host;
	$xml=get_xml_host_status($args);
	$total_records=0;
	if($xml)
		$total_records=intval($xml->recordcount);
	*/
	

	
	// run record-limiting query
	$args=array(
		"cmd" => "gethoststatus",
		"brevity" => 2,
		//"records" => $records.":".(($pager_results["current_page"]-1)*$records),
		);
	if(have_value($search)==true)
		$args["host_name"]="lks:".$search;
	if(have_value($host)==true)  // specific host
		$args["host_name"]=$host;
	if(have_value($sortby)==true)
		$args["orderby"]=$sortby;
	//$xml=get_backend_xml_data($args);
	$xml=get_xml_host_status($args);
	
	// adjust total based on hosts with working perfgraphs
	$total_records=0;
	foreach($xml->hoststatus as $h){	
		if(pnp_chart_exists(strval($h->name))==false)
			continue;
		$total_records++;
		}

	// get paging info - reset page number if necessary
	$pager_args=array(
		"sortby" => $sortby,
		"search" => $search,
		);
	$pager_results=get_table_pager_info("",$total_records,$page,$records,$pager_args);
	
	//print_r($pager_results);

	// first record
	$first_record=(($pager_results["current_page"]-1)*$records);

	
	// ONE SEARCH MATCH - REDIRECT
	// only one match found in search - redirect to host overview screen
	if($xml!=null && intval($xml->recordcount)==1 && have_value($search)==true){
		$hostname=strval($xml->hoststatus[0]->name);
		$newurl=build_url_from_current(array(
				"mode"=>PERFGRAPH_MODE_HOSTOVERVIEW,
				"service"=>"",
				"host"=>$hostname,
				));
		header("Location: $newurl");
		return;
		}
		
	do_perfgraphs_page_start();

	// print title
	do_perfgraphs_page_titles();
	
?>
	<div class="recordcounttext">
	<?php
	$clear_args=array(
		"search" => "",
		//"host" => $host,
		"source" => $source,
		"view" => $view,
		"mode" => $mode,
		"start" => $start,
		"end" => $end,
		"startdate" => $startdate,
		"enddate" => $enddate,
		);
	echo table_record_count_text($pager_results,$search,true,$clear_args);
	?>
	</div>
	
	<div class="perfgraphsheader"></div>
	
<?php
	// loop over all hosts
	$current_record=0;
	$processed_records=0;
	//echo "FIRST=$first_record, MAX=$records<BR>";
	//print_r($xml);
	foreach($xml->hoststatus as $h){
	
		//echo "<br clear='all'>PROCESSING ".$h->name."<BR>";
	
		// skip hosts with no perfdata
		if(pnp_chart_exists(strval($h->name))==false){
			//echo "CHART DOES NOT EXIST FOR ".$h->name."!<BR>";
			continue;
			}
		else{
			if($processed_records>=$records){
				//echo "REACHED $processed_records RECORDS<BR>";
				break;
				}
			//echo "CHART EXISTS FOR ".$h->name."<BR>";
			}
	
		$current_record++;
		if($current_record<=$first_record)
			continue;
		$processed_records++;

		echo "<div class='hostperfgraphcontainer'>\n";
		//echo "NAME: ".$h->name."<BR>\n";
		//echo "ID: ".$h->host_id."<BR>\n";
		
		$dargs=array(
			DASHLET_ADDTODASHBOARDTITLE => "Add This Performance Graph To A Dashboard",
			DASHLET_ARGS => array(
				"host_id" => intval($h->host_id),
				"hostname" => strval($h->name),
				"servicename" => "",
				"source" => $source,
				"view" => $view,
				"start" => $start,
				"end" => $end,
				"startdate" => $startdate,
				"enddate" => $enddate,
				"width" => "",
				"height" => "",
				"mode" => PERFGRAPH_MODE_HOSTSOVERVIEW,
				),
			DASHLET_TITLE => strval($h->name). " Performance Graph",
			);
		
		display_dashlet("xicore_perfdata_chart","",$dargs,DASHLET_MODE_OUTBOARD);
		
		draw_host_perfgraph_links($h->name);
		
		echo "</div>";
		}
?>

	<div class="perfgraphsfooter"></div>

	<div class='recordpagerlinks'>
	<form method="get" action="">
	<?php
		$opts=array(
			"search" => $search,
			"host" => $host,
			"mode" => $mode,
			"view" => $view,
			"start" => $start,
			"end" => $end,
			"startdate" => $startdate,
			"enddate" => $enddate,
			);
	?>
	<?php table_record_pager($pager_results,null,$opts);?>
	</form>
	</div>

<?php
	do_perfgraphs_page_end();
	}

	
function draw_host_perfgraph_links($hostname){
	global $lstr;

	echo "<div class='perfgraphlinks'>";
	
	echo "<div class='perfgraphlink'><a href='".get_host_status_link($hostname)."'><img src='".theme_image("statusdetail.png")."' alt='".$lstr['ViewHostStatusAlt']."' title='".$lstr['ViewHostStatusAlt']."'></a></div>";
	echo "<div class='perfgraphlink'><a href='".get_host_notifications_link($hostname)."'><img src='".theme_image("notifications.png")."' alt='".$lstr['ViewHostNotificationsAlt']."' title='".$lstr['ViewHostNotificationsAlt']."'></a></div>";
	echo "<div class='perfgraphlink'><a href='".get_host_history_link($hostname)."'><img src='".theme_image("history.png")."' alt='".$lstr['ViewHostHistoryAlt']."' title='".$lstr['ViewHostHistoryAlt']."'></a></div>";
	if(use_new_features()==false)
		echo "<div class='perfgraphlink'><a href='".get_host_trends_link($hostname)."'><img src='".theme_image("trends.png")."' alt='".$lstr['ViewHostTrendsAlt']."' title='".$lstr['ViewHostTrendsAlt']."'></a></div>";
	echo "<div class='perfgraphlink'><a href='".get_host_availability_link($hostname)."'><img src='".theme_image("availability.png")."' alt='".$lstr['ViewHostAvailabilityAlt']."' title='".$lstr['ViewHostAvailabilityAlt']."'></a></div>";
	//echo "<div class='perfgraphlink'><a href='".get_host_histogram_link($hostname)."'><img src='".theme_image("histogram.png")."' alt='".$lstr['ViewHostHistogramAlt']."' title='".$lstr['ViewHostHistogramAlt']."'></a></div>";
	
	echo "</div>";
	}
	
function draw_service_perfgraph_links($hostname,$servicename){
	global $lstr;

	echo "<div class='perfgraphlinks'>";
	
	echo "<div class='perfgraphlink'><a href='".get_service_status_link($hostname,$servicename)."'><img src='".theme_image("statusdetail.png")."' alt='".$lstr['ViewServiceStatusAlt']."' title='".$lstr['ViewServiceStatusAlt']."'></a></div>";
	echo "<div class='perfgraphlink'><a href='".get_service_notifications_link($hostname,$servicename)."'><img src='".theme_image("notifications.png")."' alt='".$lstr['ViewServiceNotificationsAlt']."' title='".$lstr['ViewServiceNotificationsAlt']."'></a></div>";
	echo "<div class='perfgraphlink'><a href='".get_service_history_link($hostname,$servicename)."'><img src='".theme_image("history.png")."' alt='".$lstr['ViewServiceHistoryAlt']."' title='".$lstr['ViewServiceHistoryAlt']."'></a></div>";
	if(use_new_features()==false)
		echo "<div class='perfgraphlink'><a href='".get_service_trends_link($hostname,$servicename)."'><img src='".theme_image("trends.png")."' alt='".$lstr['ViewServiceTrendsAlt']."' title='".$lstr['ViewServiceTrendsAlt']."'></a></div>";
	echo "<div class='perfgraphlink'><a href='".get_service_availability_link($hostname,$servicename)."'><img src='".theme_image("availability.png")."' alt='".$lstr['ViewServiceAvailabilityAlt']."' title='".$lstr['ViewServiceAvailabilityAlt']."'></a></div>";
	//echo "<div class='perfgraphlink'><a href='".get_service_histogram_link($hostname,$servicename)."'><img src='".theme_image("histogram.png")."' alt='".$lstr['ViewServiceHistogramAlt']."' title='".$lstr['ViewServiceHistogramAlt']."'></a></div>";
	
	echo "</div>";
	}
?>

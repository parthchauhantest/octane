<?php
// HISTOGRAM REPORT
//
// Copyright (c) 2010 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: histogram.php 1117 2012-04-12 15:37:13Z mguthrie $

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
	global $request;
	
	$mode=grab_request_var("mode","");
	switch($mode){
		case "image":
			get_histogram_image();
			break;
		case "csv":
			get_histogram_csv();
			break;
		case "pdf":
			get_histogram_pdf();
			break;
		default:
			display_histogram();
			break;
		}
	}


///////////////////////////////////////////////////////////////////
// BACKEND DATA FUNCTIONS
///////////////////////////////////////////////////////////////////
	
// this function gets state history data in XML format from the backend
function get_histogram_data($args){

	$xml=get_xml_histogram($args);
	
	return $xml;
	}
	
///////////////////////////////////////////////////////////////////
// REPORT GENERATION FUCNTIONS
///////////////////////////////////////////////////////////////////

// this function displays data in HTML
function display_histogram(){
	global $lstr;
	global $request;

	// makes sure user has appropriate license level
	licensed_feature_check();
	
	// get values passed in GET/POST request
	$page=grab_request_var("page",1);
	$records=grab_request_var("records",25);
	$reportperiod=grab_request_var("reportperiod","last24hours");
	$startdate=grab_request_var("startdate","");
	$enddate=grab_request_var("enddate","");
	$search=grab_request_var("search","");
	$host=grab_request_var("host","");
	$service=grab_request_var("service","");
	$hostgroup=grab_request_var("hostgroup","");
	$servicegroup=grab_request_var("servicegroup","");
	$groupby=grab_request_var("groupby","hour_of_day");
	$statetype=grab_request_var("statetype","both");
	
	// fix search
	if($search==$lstr['SearchBoxText'])
		$search="";
	
	// special "all" stuff
	if($hostgroup=="all")
		$hostgroup="";
	if($servicegroup=="all")
		$servicegroup="";
	if($host=="all")
		$host="";

	// can do hostgroup OR servicegroup OR host
	if($hostgroup!=""){
		$servicegroup="";
		$host="";
		}
	else if($servicegroup!=""){
		$host="";
		}

	//  limit hosts by hostgroup or host
	$host_ids=array();
	//  limit by hostgroup
	if($hostgroup!=""){
		$host_ids=get_hostgroup_member_ids($hostgroup);
		}
	//  limit by host
	//else if($host!=""){
	///	$host_ids[]=get_host_id($host);
	//	}
	//  limit service by servicegroup
	$service_ids=array();
	if($servicegroup!=""){
		$service_ids=get_servicegroup_member_ids($servicegroup);
		}
		
	$object_ids=array();
	$object_ids_str="";
	$y=0;
	foreach($host_ids as $hid){
		if($y>0)
			$object_ids_str.=",";
		$object_ids_str.=$hid;
		$y++;
		}
	foreach($service_ids as $sid){
		if($y>0)
			$object_ids_str.=",";
		$object_ids_str.=$sid;
		$y++;
		}
		
	// start/end times must be in unix timestamp format
	// if they weren't specified, default to last 24 hours
	//$endtime=grab_request_var("endtime",time());
	//$starttime=grab_request_var("starttime",$endtime-(24*60*60));
	
	// determine start/end times based on period
	get_times_from_report_timeperiod($reportperiod,$starttime,$endtime,$startdate,$enddate);
	
	/**/
	// SPECIFIC RECORDS (FOR PAGING): if you want to get specific records, use this type of format:
	/**/	
	$args=array(
		"starttime" => $starttime,
		"endtime" => $endtime,
		"groupby" => $groupby,
		);
	//if($search)
//		$args["output"]="lk:".$search;
	// object id limiters
	if($object_ids_str!="")
		$args["object_id"]="in:".$object_ids_str;
	else{
		if($host!=""){
			$args["host_name"]=$host;
			$args["objecttype_id"]=OBJECTTYPE_HOST;
			}
		if($service!=""){
			$args["service_description"]=$service;
			$args["objecttype_id"]=OBJECTTYPE_SERVICE;
			}
		}
	$xml=get_histogram_data($args);
	//$xml=null;
	/**/
	//print_r($xml);

	
	// start the HTML page
	do_page_start(array("page_title"=>"Alert Histogram"),true);
	
?>
	<h1>Alert Histogram</h1>
	
<?php
	if($service!=""){
?>
	<div class="servicestatusdetailheader">
	<div class="serviceimage">
	<!--image-->
	<?php show_object_icon($host,$service,true);?>
	</div>
	<div class="servicetitle">
	<div class="servicename"><a href="<?php echo get_service_status_detail_link($host,$service);?>"><?php echo htmlentities($service);?></a></div>
	<div class="hostname"><a href="<?php echo get_host_status_detail_link($host);?>"><?php echo htmlentities($host);?></a></div>
	</div>
	</div>
	<br clear="all">

<?php
		}
	else if($host!=""){
?>
	<div class="hoststatusdetailheader">
	<div class="hostimage">
	<!--image-->
	<?php show_object_icon($host,"",true);?>
	</div>
	<div class="hosttitle">
	<div class="hostname"><a href="<?php echo get_host_status_detail_link($host);?>"><?php echo htmlentities($host);?></a></div>
	</div>
	</div>
	<br clear="all">
<?php
		}
?>
	
<?php
	
?>
	<form method="get" action="<?php echo htmlentities($_SERVER["REQUEST_URI"]);?>">
	<input type="hidden" name="host" value="<?php echo htmlentities($host);?>">
	<input type="hidden" name="service" value="<?php echo htmlentities($service);?>">
	
	<div class="reportexportlinks">
	<?php echo get_add_myreport_html("Alert Histogram",$_SERVER["REQUEST_URI"],array());?>
<?php
	$url="?1";
	foreach($request as $var => $val)
		$url.="&".urlencode($var)."=".urlencode($val);
?>
	<a href="<?php echo $url;?>&mode=csv" alt="Export As CSV" title="Export As CSV"><img src="<?php echo theme_image("csv.png");?>"></a>
	<a href="<?php echo $url;?>&mode=pdf" alt="Download As PDF" title="Download As PDF"><img src="<?php echo theme_image("pdf.png");?>"></a>
	</div>
	
	<!--
	<div class="reportsearchbox">
<?php
	// search box
	$searchclass="textfield";
	if(have_value($search)==true){
		$searchstring=$search;
		$searchclass.=" newdata";
		}
	else
		$searchstring=$lstr['SearchBoxText'];
?>
	<input type="text" size="15" name="search" id="searchBox" value="<?php echo encode_form_val($searchstring);?>" class="<?php echo $searchclass;?>" />
	</div>
	//-->
	
<?php
	$auto_start_date=get_datetime_string(strtotime('yesterday'),DT_SHORT_DATE);
	$auto_end_date=get_datetime_string(strtotime('Today'),DT_SHORT_DATE);
?>
	
	<script type="text/javascript">
	$(document).ready(function(){
		$('#startdateBox').click(function(){
			$('#reportperiodDropdown').val('custom');
			if($('#startdateBox').val()=='' && $('#enddateBox').val()==''){
				$('#startdateBox').val('<?php echo $auto_start_date;?>');
				$('#enddateBox').val('<?php echo $auto_end_date;?>');
				}
			});
		$('#enddateBox').click(function(){
			$('#reportperiodDropdown').val('custom');
			if($('#startdateBox').val()=='' && $('#enddateBox').val()==''){
				$('#startdateBox').val('<?php echo $auto_start_date;?>');
				$('#enddateBox').val('<?php echo $auto_end_date;?>');
				}
			});
		
		});
	</script>

	<div class="reporttimepicker">
	Period&nbsp;
	<select id='reportperiodDropdown' name="reportperiod">
<?php
	$tp=get_report_timeperiod_options();
	foreach($tp as $shortname => $longname){
		echo "<option value='".$shortname."' ".is_selected($shortname,$reportperiod).">".$longname."</option>";
		}
?>
	</select>
	&nbsp;From&nbsp;
	<input class="textfield" type="text" id='startdateBox' name="startdate" value="<?php echo $startdate; ?>" size="16" />
	<div class="reportstartdatepicker"><img src="<?php echo theme_image("calendar_small.png");?>"></div>
	&nbsp;To&nbsp;
	<input class="textfield" type="text" id='enddateBox' name="enddate" value="<?php echo $enddate; ?>" size="16" />
	<div class="reportenddatepicker"><img src="<?php echo theme_image("calendar_small.png");?>"></div>
	&nbsp;
	<input type='submit' class='reporttimesubmitbutton' name='reporttimesubmitbutton' value='Go'>
	</div>
		
	<div class="reportoptionpicker">

	Limit To&nbsp;
	
	<select name="host" id="hostList">
	<option value="">Host:</option>
<?php
	$args=array('brevity' => 1); 
	$oxml=get_xml_host_objects($args);
	if($oxml){
		foreach($oxml->host as $hostobject){
			$name=strval($hostobject->host_name);
			echo "<option value='".$name."' ".is_selected($hostobject,$name).">$name</option>\n";
			}
		}
?>
	</select>		
			
	<select name="hostgroup" id="hostgroupList">
	<option value="">Hostgroup:</option>
<?php
	$oxml=get_xml_hostgroup_objects();
	if($oxml){
		foreach($oxml->hostgroup as $hg){
			$name=strval($hg->hostgroup_name);
			echo "<option value='".$name."' ".is_selected($hostgroup,$name).">$name</option>\n";
			}
		}
?>
	</select>
	<select name="servicegroup" id="servicegroupList">
	<option value="">Servicegroup:</option>
<?php
	$oxml=get_xml_servicegroup_objects();
	if($oxml){
		foreach($oxml->servicegroup as $sg){
			$name=strval($sg->servicegroup_name);
			echo "<option value='".$name."' ".is_selected($servicegroup,$name).">$name</option>\n";
			}
		}
?>
	</select>

	<script type="text/javascript">
	$(document).ready(function(){
	
		$('#hostList').change(function() {
			$('#hostgroupList').val('');
			$('#servicegroupList').val('');
			});
		$('#servicegroupList').change(function() {
			$('#hostList').val('');
			$('#hostgroupList').val('');
			});
		$('#hostgroupList').change(function() {
			$('#servicegroupList').val('');
			$('#hostList').val('');
			});
		});
	</script>

	Group By&nbsp;
	<select id='groupbyDropdown' name="groupby">
	<option value='hour_of_day' <?php echo is_selected("hour_of_day",$groupby);?>>Hour of Day</option>
	<option value='day_of_week' <?php echo is_selected("day_of_week",$groupby);?>>Day of Week</option>
	<option value='day_of_month' <?php echo is_selected("day_of_month",$groupby);?>>Day of Month</option>
	<option value='month' <?php echo is_selected("month",$groupby);?>>Month</option>
	</select>
	&nbsp;State Types&nbsp;
	<select id='statetypeDropdown' name="statetype">
	<option value='soft' <?php echo is_selected("soft",$statetype);?>>Soft</option>
	<option value='hard' <?php echo is_selected("hard",$statetype);?>>Hard</option>
	<option value='both' <?php echo is_selected("both",$statetype);?>>Both</option>
	</select>
	</div>	
	
	<div>
	From: <b><?php echo get_datetime_string($starttime,DT_SHORT_DATE_TIME,DF_AUTO,"null");?></b> To: <b><?php echo get_datetime_string($endtime,DT_SHORT_DATE_TIME,DF_AUTO,"null");?></b>
	</div>

<?php
	$url=urlencode(get_current_page())."?".time();
	foreach($request as $var => $val)
		$url.="&".urlencode($var)."=".urlencode($val);
?>
	<br />
	<div id='alert_histogram_image'>
		<img src="<?php echo $url;?>&mode=image" border="0" />
	</div>
	
	</form>

<?php		

/*
	$xml=get_histogram_xml();
	print_r($xml);
	// build array
	$xdata=array();
	$ydata=array();
	process_histogram_xml($xml,$xdata,$ydata,$xtitle);
	echo "<BR>";
	echo "<BR>X=<BR>";
	print_r($xdata);
	echo "<BR>Y=<BR>";
	print_r($ydata);
*/
	
	// closes the HTML page
	do_page_end(true);
	}
	
	
// this function gets the XML records of state history data for multiple
// output formats (CSV, PDF, HTML)
function get_histogram_xml(){
	global $lstr;

	// makes sure user has appropriate license level
	licensed_feature_check();
	
	// get values passed in GET/POST request
	$page=grab_request_var("page",1);
	$records=grab_request_var("records",25);
	$reportperiod=grab_request_var("reportperiod","last24hours");
	$startdate=grab_request_var("startdate","");
	$enddate=grab_request_var("enddate","");
	$search=grab_request_var("search","");
	$host=grab_request_var("host","");
	$service=grab_request_var("service","");
	$hostgroup=grab_request_var("hostgroup","");
	$servicegroup=grab_request_var("servicegroup","");
	$groupby=grab_request_var("groupby","hour_of_day");
	$statetype=grab_request_var("statetype","both");
	
	// fix search
	if($search==$lstr['SearchBoxText'])
		$search="";
	
	// special "all" stuff
	if($hostgroup=="all")
		$hostgroup="";
	if($servicegroup=="all")
		$servicegroup="";
	if($host=="all")
		$host="";

	// can do hostgroup OR servicegroup OR host
	if($hostgroup!=""){
		$servicegroup="";
		$host="";
		}
	else if($servicegroup!=""){
		$host="";
		}

	//  limit hosts by hostgroup or host
	$host_ids=array();
	//  limit by hostgroup
	if($hostgroup!=""){
		$host_ids=get_hostgroup_member_ids($hostgroup);
		}
	//  limit by host
	//else if($host!=""){
	///	$host_ids[]=get_host_id($host);
	//	}
	//  limit service by servicegroup
	$service_ids=array();
	if($servicegroup!=""){
		$service_ids=get_servicegroup_member_ids($servicegroup);
		}
		
	$object_ids=array();
	$object_ids_str="";
	$y=0;
	foreach($host_ids as $hid){
		if($y>0)
			$object_ids_str.=",";
		$object_ids_str.=$hid;
		$y++;
		}
	foreach($service_ids as $sid){
		if($y>0)
			$object_ids_str.=",";
		$object_ids_str.=$sid;
		$y++;
		}
		

	// determine start/end times based on period
	get_times_from_report_timeperiod($reportperiod,$starttime,$endtime,$startdate,$enddate);
	
	// get XML data from backend - the most basic example
	// this will return all records (no paging), so it can be used for CSV export
	$args=array(
		"starttime" => $starttime,
		"endtime" => $endtime,
		"groupby" => $groupby,
		);
	switch($statetype){
		case "soft":
			$args["state_type"]=0;
			break;
		case "hard":
			$args["state_type"]=1;
			break;
		default:
			break;
		}
	// object id limiters
	if($object_ids_str!="")
		$args["object_id"]="in:".$object_ids_str;
	else{
		if($host!=""){
			$args["host_name"]=$host;
			$args["objecttype_id"]=OBJECTTYPE_HOST;
			}
		if($service!=""){
			$args["service_description"]=$service;
			$args["objecttype_id"]=OBJECTTYPE_HOST;
			}
		}
	if($search){
		$args["output"]="lk:".$search;
		}
	//echo "ARGS:<BR>";
	//print_r($args);
	$xml=get_histogram_data($args);
	//exit();
	return $xml;
	}
	
function process_histogram_xml($xml,&$xarr,&$yarr,&$xtitle){
	$groupby=grab_request_var("groupby","hour_of_day");
	
	$bucket=0;

	// Setup titles and X-axis labels
	$xtitle="";
	switch($groupby){
		case "month":
			$xtitle="Month";
			$buckets=12;
			break;
		case "day_of_month":
			$xtitle="Day of the Month";
			$buckets=31;
			break;
		case "day_of_week":
			$xtitle="Day of the Week";
			$buckets=7;
			break;
		case "hour_of_day":
			$xtitle="Hour of the Day";
			$buckets=24;
			break;
		default:
			break;
		}
		
	// initialize arrays
	for($x=0;$x<$buckets;$x++){
	
		$yarr[]=0;
		
		switch($groupby){
			case "month":
				$xarr[]=($x);
				break;
			case "day_of_month":
				$xarr[]=($x);
				break;
			case "day_of_week":
				$xarr[]=($x);
				break;
			case "hour_of_day":
				$xarr[]=($x);
				break;
			default:
				break;
			}		
		}

	// extra one for hour of day (jpgraph bug)
	//if($groupby=="hour_of_day")
	//	$xarr[24]=24;
		
	// insert real data
	if($xml){
		foreach($xml->histogramelement as $he){
		
			$total=intval($he->total);
			
			$month=intval($he->month);
			$day_of_month=intval($he->day_of_month);
			$day_of_week=intval($he->day_of_week);
			$hour_of_day=intval($he->hour_of_day);
			
			$index=0;
			switch($groupby){
				case "month":
					$index=$month-1;
					break;
				case "day_of_month":
					$index=$day_of_month-1;
					break;
				case "day_of_week":
					$index=$day_of_week;
					break;
				case "hour_of_day":
					$index=$hour_of_day;
					break;
				default:
					break;
				}	

			$yarr[$index]=$total;
			}
		}
		
	// last bucket is same as first (only for hour of day)
	//if($groupby=="hour_of_day")
//		$yarr[24]=$yarr[0];
	//$yarr[$buckets]=$yarr[0];
	}
	
function histogram_xaxis_callback($val){

	$groupby=grab_request_var("groupby","hour_of_day");

	switch($groupby){
		case "month":
			$month=$val%12;
			$months=array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
			$out=$months[$month];
			break;
		case "day_of_month":
			$out=$val+1;
			break;
		case "day_of_week":
			$day=$val%7;
			$days=array("Mon","Tue","Wed","Thu","Fri","Sat","Sun");
			$out=$days[$day];
			break;
		case "hour_of_day":
			$hour=$val%24;
			$hours=array("12am","1am","2am","3am","4am","5am","6am","7am","8am","9am","10am","11am","Noon","1pm","2pm","3pm","4pm","5pm","6pm","7pm","8pm","9pm","10pm","11pm");
			$out=$hours[$hour];
			//$out=$val;
			break;
		default:
			$out=$val;
			break;
		}	

	return $out;
	}
	

// this function generates an image from histogram data
function get_histogram_image(){
	
	$groupby=grab_request_var("groupby","hour_of_day");
		
	$xml=get_histogram_xml();
	// build array
	$xdata=array();
	$ydata=array();
	process_histogram_xml($xml,$xdata,$ydata,$xtitle);

	/*
	echo "<BR>";
	echo "<BR>X=<BR>";
	print_r($xdata);
	echo "<BR>Y=<BR>";
	print_r($ydata);	
	exit();
	*/
	
	$path=get_component_dir_base("jpgraph")."/src";
	require_once($path."/jpgraph.php");
	require_once($path."/jpgraph_line.php");
	//require_once($path."/jpgraph_bar.php");	
	
	 // Width and height of the graph
	$width = 600; 
	$height = 225;
	 
	// Create a graph instance
	$graph = new Graph($width,$height);
	
	// set margin
	$graph->SetMargin(60,25,25,50);
	 
	// Specify what scale we want to use,
	// int = integer scale for the X-axis
	// int = integer scale for the Y-axis
	$graph->SetScale('intint');
	if($groupby=="hour_of_day")
		$graph->SetScale('intint',0,0,0,23);
	 
	// Setup a title for the graph
	//$graph->title->Set('Alert Histogram');
	 
	//$graph->xaxis->title->Set($title);
	$graph->xaxis->SetTitle($xtitle,'middle'); 
	
	$graph->xaxis->SetLabelFormatCallback("histogram_xaxis_callback");
	 
	// Setup Y-axis title
	$graph->yaxis->title->Set('Number of Alerts');
	$graph->yaxis->SetTitlemargin(40);
	 
	// Create the linear plot
	/*
	$xdata=array();
	$xdata[]=100;
	$xdata[]=150;
	$xdata[]=200;
	$ydata=array();
	$ydata[]=4;
	$ydata[]=6;
	$ydata[]=7;
	*/
	$lineplot=new LinePlot($ydata,$xdata);
//	$lineplot=new BarPlot($ydata,$xdata);
	 
	// Add the plot to the graph
	$graph->Add($lineplot);
	 
	// Display the graph
	header("Content-type: image/png");
	$graph->Stroke();
	}
	
	
// this function generates a CSV file of histogram data
function get_histogram_csv(){
	$groupby=grab_request_var("groupby","hour_of_day");
	
		
	$xml=get_histogram_xml();
	// build array
	$xdata=array();
	$ydata=array();
	process_histogram_xml($xml,$xdata,$ydata,$xtitle);	

	// output header for csv
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=\"histogram.csv\"");

	// column definitions
	echo $groupby.",total_alerts\n";
	
	//print_r($xml);
	//exit();

	$x=0;
	foreach($xdata as $xd){
		echo $xd.",".$ydata[$x]."\n";
		$x++;
		}
	/*
	if($xml){
		foreach($xml->histogramelement as $he){
		
			// what type of log entry is this?  we change the image used for each line based on what type it is...
			//$object_type=intval($he->objecttype_id);
			//$host_name=strval($he->host_name);
			//$service_description=strval($he->service_description);
			$total=intval($he->total);
			$month=intval($he->month);
			$dom=intval($he->day_of_month);
			$dow=intval($he->day_of_week);
			$hod=intval($he->hour_of_day);
			
			$index=0;
			switch($groupby){
				case "month":
					$index=$month;
					break;
				case "day_of_month":
					$index=$dom;
					break;
				case "day_of_week":
					$index=$dow;
					break;
				case "hour_of_day":
					$index=$hod;
					break;
				default:
					break;
				}			
			
			
			echo $total.",".$index."\n";
			}
		}
	*/
	}
	

// This function generates a PDF of histogram data.
// Requires the "fpdp.php" library and the "fonts" directory for font metrics.
function get_histogram_pdf(){
	global $request;
	global $lstr;

	//require_once(dirname(__FILE__).'/../includes/fpdf/fpdf.php');
	require_once(dirname(__FILE__).'/../includes/fpdf/fpdf_alpha.php');
	require_once(dirname(__FILE__).'/../includes/fpdf/nagiospdf.php');
	
	// get values passed in GET/POST request
	$page=grab_request_var("page",1);
	$records=grab_request_var("records",25);
	$reportperiod=grab_request_var("reportperiod","last24hours");
	$startdate=grab_request_var("startdate","");
	$enddate=grab_request_var("enddate","");
	$search=grab_request_var("search","");
	$host=grab_request_var("host","");
	$service=grab_request_var("service","");
	$hostgroup=grab_request_var("hostgroup","");
	$servicegroup=grab_request_var("servicegroup","");
	$statetype=grab_request_var("statetype","hard");
	
	// fix search
	if($search==$lstr['SearchBoxText'])
		$search="";

	// determine start/end times based on period
	get_times_from_report_timeperiod($reportperiod,$starttime,$endtime,$startdate,$enddate);


	$date_text="".get_datetime_string($starttime,DT_SHORT_DATE_TIME,DF_AUTO,"null")." To ".get_datetime_string($endtime,DT_SHORT_DATE_TIME,DF_AUTO,"null")."";
	
	// get state history entries in XML
	$xml=get_histogram_xml();
	
	$records=0;
	if($xml){
		$records=intval($xml->recordcount);
		}

	$pdf=new NagiosReportPDF();
	$pdf->page_title='Alert Histogram';
	$pdf->page_subtitle="";
	if($service!=""){
		$pdf->page_subtitle.=$host."\n";
		$pdf->page_subtitle.=$service."\n\n";
		}
	else if($host!=""){
		$pdf->page_subtitle.=$host."\n\n";
		}
	else if($hostgroup!=""){
		$pdf->page_subtitle.=$hostgroup."\n\n";
		}
	else if($servicegroup!=""){
		$pdf->page_subtitle.=$servicegroup."\n\n";
		}
	$pdf->page_subtitle.=$date_text."";
//	$pdf->page_subtitle.=$date_text."\n\n"."Showing ".$records." Events";
	//if($search!="")
//		$pdf->page_subtitle.=" Matching '".$search."'";
	
	$pdf->AliasNbPages();
	$pdf->AddPage();
	$pdf->SetFont("Helvetica", "", 7);
	
	$type_image="";
	$type_text="";

	// image
	$url=get_base_url()."reports/histogram.php?".time();
	foreach($request as $var => $val)
		$url.="&".urlencode($var)."=".urlencode($val);
	$url.="&mode=image";
	// add authentication stuff
	$url.="&username=".$_SESSION["username"]."&ticket=".get_user_attr(0,"backend_ticket");

	// get raw image
	$imgdata=file_get_contents($url);
	// create tmp file
	$temp=tempnam("/tmp","histogram");
	// save image
	file_put_contents($temp,$imgdata);
	// rename file
	$imgfile=$temp.".png";
	rename($temp,$imgfile);
	
	// we have to scale the image...
	$width=600;
	$height=225;
	$imgscale=190/$width;
	$pdf->Image($imgfile,$pdf->GetX(),$pdf->GetY()+5,$width*$imgscale,$height*$imgscale);
	
	// delete temp image file
	unlink($imgfile);
	
	$pdf->Output("histogram.pdf", "I");
	}
	
	
	


?>

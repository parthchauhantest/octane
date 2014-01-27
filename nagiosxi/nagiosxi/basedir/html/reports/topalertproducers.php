<?php
// TOP ALERT PRODUCERS REPORT
//
// Copyright (c) 2010 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: topalertproducers.php 1117 2012-04-12 15:37:13Z mguthrie $

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
		case "csv":
			get_topalertproducers_csv();
			break;
		case "pdf":
			get_topalertproducers_pdf();
			break;
		default:
			display_topalertproducers();
			break;
		}
	}


///////////////////////////////////////////////////////////////////
// BACKEND DATA FUNCTIONS
///////////////////////////////////////////////////////////////////
	
// this function gets state history data in XML format from the backend
function get_topalertproducers_data($args){

	$xml=get_xml_topalertproducers($args);
	
	return $xml;
	}
	
///////////////////////////////////////////////////////////////////
// REPORT GENERATION FUCNTIONS
///////////////////////////////////////////////////////////////////

// this function displays in HTML
function display_topalertproducers(){
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
	$statetype=grab_request_var("statetype","hard");
	
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
	
	// NOTES: 
	// TOTAL RECORD COUNT (FOR PAGING): if you wanted to get the total count of records in a given timeframe (instead of the records themselves), use this:
	/**/
	$args=array(
		"starttime" => $starttime,
		"endtime" => $endtime,
		"totals" => 1,
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
	if($search)
		$args["output"]="lk:".$search;
	if($object_ids_str!="")
		$args["object_id"]="in:".$object_ids_str;
	else{
		if($host!="")
			$args["host_name"]=$host;
		}
	if($service!="")
		$args["service_description"]=$service;
	$xml=get_topalertproducers_data($args);
	//print_r($xml);
	$total_records=0;
	if($xml)
		$total_records=intval($xml->recordcount);
		

	// determine paging information
	$args=array(
		"reportperiod" => $reportperiod,
		"startdate" => $startdate,
		"enddate" => $enddate,
		"starttime" => $starttime,
		"endtime" => $endtime,
		"search" => $search,
		"host" => $host,
		"service" => $service,
		"hostgroup" => $hostgroup,
		"servicegroup" => $servicegroup,
		);
	$pager_results=get_table_pager_info("",$total_records,$page,$records,$args);
	$first_record=(($pager_results["current_page"]-1)*$records);
	
	/**/
	// SPECIFIC RECORDS (FOR PAGING): if you want to get specific records, use this type of format:
	/**/	
	$args=array(
		"starttime" => $starttime,
		"endtime" => $endtime,
		"records" => $records.":".$first_record,
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
	if($search)
		$args["output"]="lk:".$search;
	if($object_ids_str!="")
		$args["object_id"]="in:".$object_ids_str;
	else{
		if($host!="")
			$args["host_name"]=$host;
		}
	if($service!="")
		$args["service_description"]=$service;
	$xml=get_topalertproducers_data($args);
	//$xml=null;
	/**/
	//print_r($xml);

	
	// start the HTML page
	do_page_start(array("page_title"=>"Top Alert Producers"),true);
	
?>
	<h1>Top Alert Producers</h1>
	
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
	<?php echo get_add_myreport_html("Top Alert Producers",$_SERVER["REQUEST_URI"],array());?>
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
		$('#servicegroupList').change(function() {
			$('#hostgroupList').val('');
			});
		$('#hostgroupList').change(function() {
			$('#servicegroupList').val('');
			});
		});
	</script>
	
	State Types&nbsp;
	<select id='statetypeDropdown' name="statetype">
	<option value='soft' <?php echo is_selected("soft",$statetype);?>>Soft</option>
	<option value='hard' <?php echo is_selected("hard",$statetype);?>>Hard</option>
	<option value='both' <?php echo is_selected("both",$statetype);?>>Both</option>
	</select>
	</div>		
	
	
	
	<div>
	From: <b><?php echo get_datetime_string($starttime,DT_SHORT_DATE_TIME,DF_AUTO,"null");?></b> To: <b><?php echo get_datetime_string($endtime,DT_SHORT_DATE_TIME,DF_AUTO,"null");?></b>
	</div>


	<div class="recordcounttext">
	<?php
	$clear_args=array(
		"reportperiod" => $reportperiod,
		"startdate" => $startdate,
		"enddate" => $enddate,
		"starttime" => $starttime,
		"endtime" => $endtime,
		"host" => $host,
		"service" => $service,
		//"search" => $search,
		);
	echo table_record_count_text($pager_results,$search,true,$clear_args);
	?>
	</div>	
	
	<div id='top_alert_producers' class="topalertproducerentries">
	<table class="standardtable topalertproducerstable">
	<thead>
	<tr><th>Total Alerts</th><th>Host</th><th>Service</th><th>Latest Alert</th></tr>
	</thead>
	<tbody>
<?php
	if($xml){
		//echo "GOT XML!<BR>";
		//print_r($xml);
		if($total_records==0){
			echo "<tr><td colspan='4'>No matching results found.  Try expanding your search criteria.</td></tr>\n";
			}			
		else foreach($xml->producer as $p){
		
			// what type of log entry is this?  we change the image used for each line based on what type it is...
			//$entrytype=intval($le->logentry_type);
			//$entrytext=strval($le->logentry_data);
			
			//get_eventlog_type_info($entrytype,$entrytext,$type_img,$type_text);
			
			//echo "<div class='logentry logentry-".$entrytype."'><span class='logentrytype'><img src='".nagioscore_image($type_img)."' alt='".$type_text."' title='".$type_text."'></span><span class='logentrytime'>".$le->entry_time."</span><span class='logentrydata'>".$entrytext."</span></div>";
			
			$type_text="";
			$trclass="";
			$tdclass="";
						
			$total_alerts=intval($p->total_alerts);
			$host_name=strval($p->host_name);
			$service_description=strval($p->service_description);
						
			$base_url=get_base_url()."includes/components/xicore/status.php";
			$host_url=$base_url."?show=hostdetail&host=".urlencode($host_name);
			$service_url=$base_url."?show=servicedetail&host=".urlencode($host_name)."&service=".urlencode($service_description);
			
			$history_url=get_base_url()."reports/statehistory.php?host=".urlencode($host_name)."&service=".urlencode($service_description)."&reportperiod=".urlencode($reportperiod)."&startdate=".urlencode($startdate)."&enddate=".urlencode($enddate);
			

			//echo "<div class='statehistory '><span class='statehistorytype'><img src='' alt='".$type_text."' title='".$type_text."'></span><span class='statehistorytime'>".$se->state_time."</span><span class='statehistoryhost'>".$host_name."</span><span class='statehistoryservice'>".$service_description."</span><span class='statehistorydata'>".$output."</span></div>";
			
			echo "<tr class='".$trclass."'>";
			echo "<td><a href='".$history_url."'>".$total_alerts."</a></td>";
			echo "<td><a href='".$host_url."'>".$host_name."</a></td>";
			echo "<td><a href='".$service_url."'>".$service_description."</a></td>";
			echo "<td nowrap>".$p->state_time."</td>";
			echo "</tr>";
			}
		}
?>
	</tbody>
	</table>
	</div>
	
	<br clear="all">
	
	<div class='recordpagerlinks'>
	<form method="get" action="">
	<?php table_record_pager($pager_results);?>
	</form>
	</div>
	
	</form>

<?php		

	
	// closes the HTML page
	do_page_end(true);
	}
	
	
// this function gets the XML records of state history data for multiple
// output formats (CSV, PDF, HTML)
function get_topalertproducers_xml(){
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
	$statetype=grab_request_var("statetype","hard");
	
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
	if($object_ids_str!="")
		$args["object_id"]="in:".$object_ids_str;
	else{
		if($host!="")
			$args["host_name"]=$host;
		if($service!="")
			$args["service_description"]=$service;
		}
	//if($search){
	//	$args["output"]="lk:".$search;
	//	}
	$xml=get_topalertproducers_data($args);
	return $xml;
	}

// this function generates a CSV file of event log data
function get_topalertproducers_csv(){

	$xml=get_topalertproducers_xml();
	
	// output header for csv
	//header('Content-type: application/text');
	//header("Content-length: " . filesize($thefile)); 
	//header('Content-Disposition: attachment; filename="'.basename($thefile).'"');
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=\"topalertproducers.csv\"");

	// column definitions
	//echo "time,type,data\n";
	echo "totalalerts,host,service,lastalert\n";
	
	//print_r($xml);
	//exit();

	if($xml){
		foreach($xml->producer as $p){
		
			// what type of log entry is this?  we change the image used for each line based on what type it is...
			$object_type=intval($p->objecttype_id);
			$host_name=strval($p->host_name);
			$service_description=strval($p->service_description);
			$total_alerts=intval($p->total_alerts);
			
			echo $total_alerts.",\"".$host_name."\",\"".$service_description."\",".$p->state_time."\n";
			}
		}
	}
	
	
// This function generates a PDF of event log data.
// Requires the "fpdp.php" library and the "fonts" directory for font metrics.
function get_topalertproducers_pdf(){
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
	$hostgroup=grab_request_var("hostgroup","");
	$servicegroup=grab_request_var("servicegroup","");
	$service=grab_request_var("service","");
	
	// fix search
	if($search==$lstr['SearchBoxText'])
		$search="";

	// determine start/end times based on period
	get_times_from_report_timeperiod($reportperiod,$starttime,$endtime,$startdate,$enddate);


	$date_text="".get_datetime_string($starttime,DT_SHORT_DATE_TIME,DF_AUTO,"null")." To ".get_datetime_string($endtime,DT_SHORT_DATE_TIME,DF_AUTO,"null")."";
	
	// get state history entries in XML
	$xml=get_topalertproducers_xml();
	
	$records=0;
	if($xml){
		$records=intval($xml->recordcount);
		}

	$pdf=new NagiosReportPDF();
	$pdf->page_title='Top Alert Producers';
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
	$pdf->page_subtitle.=$date_text."\n\n"."Showing ".$records." Events";
	if($search!="")
		$pdf->page_subtitle.=" Matching '".$search."'";
	
	$pdf->AliasNbPages();
	$pdf->AddPage();
	$pdf->SetFont("Helvetica", "", 7);
	
	$type_image="";
	$type_text="";

	/*
	if($xml){
		foreach($xml->producer as $p){

			$object_type=intval($p->objecttype_id);
			$host_name=strval($p->host_name);
			$service_description=strval($p->service_description);
			$total_alerts=intval($p->total_alerts);
			
			if($service_description=="")
				$service_description="-";
			
			
			$text = $total_alerts."\t".$host_name."\t".$service_description."\t".$p->state_time;

			
			$pdf->SetX($pdf->GetX()+4);
			$pdf->MultiCellTag(0, 6, $text, 0, "L", 0, 0, 0, 0, 0);

			}
		}
	*/
	
	$fs=' style="times" size="10" style="bold"';
	
	$html='
	<table border="1" align="center">
	<tr	bgcolor="#cccccc" repeat>
	<td '.$fs.'>Total Alerts</td>
	<td '.$fs.'>Host</td>
	<td '.$fs.'>Service</td>
	<td '.$fs.'>Latest Alert</td>
	</tr>
	';
	
	$fs=' size="8" style=""';

	if($xml){
		foreach($xml->producer as $p){

			$object_type=intval($p->objecttype_id);
			$host_name=strval($p->host_name);
			$service_description=strval($p->service_description);
			$total_alerts=intval($p->total_alerts);
			
			if($service_description=="")
				$service_description="-";
			
			
			$html.= "<tr><td ".$fs.">".$total_alerts."</td><td ".$fs.">".$host_name."</td><td ".$fs.">".$service_description."</td><td ".$fs.">".$p->state_time."</td></tr>";
			}
		}
	
	$html.='</table>';
	
	//$pdf->setfont('times','I',8);
	$pdf->htmltable($html);		

	$pdf->Output("topalertproducers.pdf", "I");
	}
	
	
///////////////////////////////////////////////////////////////////
// HELPER FUNCTIONS
///////////////////////////////////////////////////////////////////

// return corresponding image and text to use...
function get_statehistory_type_info($objecttype,$state,$statetype,&$img,&$text){

	// initial/default values
	$img="info.png";
	$text="";
	//return;
	
	if($objecttype==OBJECTTYPE_HOST){
		switch($state){
			case 0:
				$img="recovery.png";
				break;
			case 1:
				$img="critical.png";
				break;
			case 2:
				$img="critical.png";
				break;
			}
		}
	else{
		switch($state){
			case 0:
				$img="recovery.png";
				break;
			case 1:
				$img="warning.png";
				break;
			case 2:
				$img="critical.png";
				break;
			case 3:
				$img="unknown.png";
				break;
			}
		}

	}
	
	


?>

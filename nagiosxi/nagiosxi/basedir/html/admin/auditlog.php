<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: auditlog.php 1242 2012-06-21 22:22:34Z egalstad $

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

// only admins can access this page
if(is_admin()==false){
	echo $lstr['NotAuthorizedErrorText'];
	exit();
	}

// route request
route_request();


function route_request(){
	global $request;
	
	$mode=grab_request_var("mode","");
	switch($mode){
		case "csv":
			get_auditlog_csv();
			break;
		default:
			show_auditlog();
			break;
		}
	}
	
	
function show_auditlog($error=false,$msg=""){
	global $request;
	global $lstr;
	

	do_page_start(array("page_title"=>$lstr['AuditLogPageTitle']),true);

?>

	
	<h1><?php echo $lstr['AuditLogPageTitle'];?></h1>
	

<?php
	display_message($error,false,$msg);
?>


<?php
	// Enterprise Edition message
	echo enterprise_message();

	if(enterprise_features_enabled()==true){
		show_auditlog_content(true);
		}
	else{
		show_auditlog_content(false);
		}
?>

<?php

	do_page_end(true);
	exit();
	}

	
function show_auditlog_content($fullaccess=false){
	global $lstr;
	global $request;


	echo "<p>".$lstr['AuditLogPageNotes']."</p>";
	
	// makes sure user has appropriate license level
	licensed_feature_check();
	
	// check enterprise license
	$efe=enterprise_features_enabled();

		
	// get values passed in GET/POST request
	$page=grab_request_var("page",1);
	$records=grab_request_var("records",25);
	$reportperiod=grab_request_var("reportperiod","last24hours");
	$startdate=grab_request_var("startdate","");
	$enddate=grab_request_var("enddate","");
	$search=grab_request_var("search","");
	$source=grab_request_var("source","");
	$type=grab_request_var("type","");
	$user=grab_request_var("user","");
	$ip_address=grab_request_var("ip_address","");
	
	// expired enterprise license can only stay on 1st page
	if($efe==false)
		$page=1;
	
	// fix search
	if($search==$lstr['SearchBoxText'])
		$search="";
	

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
	if($search)
		$args["message"]="lk:".$search.";source=lk:".$search.";user=lk:".$search;
	if($source!="")
		$args["source"]=$source;
	if($type!="")
		$args["type"]=$type;
	$xml=get_auditlog_xml($args);
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
		"source" => $source,
		"type" => $type,
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
	if($search)
		$args["message"]="lk:".$search.";source=lk:".$search.";user=lk:".$search;
	if($source!="")
		$args["source"]=$source;
	if($type!="")
		$args["type"]=$type;
	//print_r($args);
	$xml=get_auditlog_xml($args);
	//$xml=null;
	/**/
	//print_r($xml);

	
?>
	
	<form method="get" action="<?php echo htmlentities($_SERVER["REQUEST_URI"]);?>">
	
	<div class="reportexportlinks">
	<?php echo get_add_myreport_html("Audit Log",$_SERVER["REQUEST_URI"],array());?>
<?php
	$url="?1";
	foreach($request as $var => $val)
		$url.="&".urlencode($var)."=".urlencode($val);
?>
	<a href="<?php echo $url;?>&mode=csv" alt="Export As CSV" title="Export As CSV"><img src="<?php echo theme_image("csv.png");?>"></a>
	</div>
	
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
		"source" => $source,
		"type" => $type,
		//"search" => $search,
		);
	echo table_record_count_text($pager_results,$search,true,$clear_args);
	?>
	</div>	
	
	<div class="auditlogentries">
	<table class="standardtable auditlogtable">
	<thead>
	<tr><th>Date / Time</th><th>ID</th><th>Source</th><th>Type</th><th>User</th><th>IP Address</th><th>Message</th></tr>
	</thead>
	<tbody>
<?php
	if($xml){

		// show limited fields if enterprise expired
		$limited=false;
		if($efe==false)
			$limited=true;
			
		$x=0;

		//echo "GOT XML!<BR>";
		//print_r($xml);
		if($total_records==0){
			echo "<tr><td colspan='7'>No matching results found.  Try expanding your search criteria.</td></tr>\n";
			}			
		else foreach($xml->auditlogentry as $a){
		
			$x++;
			if($efe==false && $x>5)
				break;
		
			$user=strval($a->user);
			$ip=strval($a->ip_address);
			if($user=="NULL")
				$user="";
			if($ip=="NULL")
				$ip="";

			echo "<tr >";
			echo "<td nowrap><span class='notificationtime'>".$a->log_time."</span></td>";
			echo "<td>".$a->id."</td>";
			echo "<td>".$a->source."</td>";
			echo "<td>".$a->typestr."</td>";
			echo "<td>".$user."</td>";
			echo "<td>".$ip."</td>";
			echo "<td>".$a->message."</td>";
			echo "</tr>";
			}
		if($efe==false){
			echo "<tr><td colspan='7'>".enterprise_limited_feature_message("Limited messages shown.   Purchase Enterprise Edition to enable full functionality.  ")."</td></tr>\n";
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

	

	}

	
	
// this function gets the XML records of audit log data for multiple
// output formats (CSV, PDF, HTML)
function get_auditlog_xml(){
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
	$source=grab_request_var("source","");
	$type=grab_request_var("type","");
	$user=grab_request_var("user","");
	$ip_address=grab_request_var("ip_address","");
	
	// fix search
	if($search==$lstr['SearchBoxText'])
		$search="";
	

	// determine start/end times based on period
	get_times_from_report_timeperiod($reportperiod,$starttime,$endtime,$startdate,$enddate);
	
	// get XML data from backend - the most basic example
	// this will return all records (no paging), so it can be used for CSV export
	$args=array(
		"starttime" => $starttime,
		"endtime" => $endtime,
		);
	if($source!="")
		$args["source"]=$source;
	if($user!="")
		$args["user"]=$user;
	if($type!="")
		$args["type"]=$type;
	if($ip_address!="")
		$args["ip_address"]=$ip_address;
	if($search){
		$args["message"]="lk:".$search.";source=lk:".$search.";user=lk:".$search.";ip_address=lk:".$search;
		}
	$xml=get_xml_auditlog($args);
	return $xml;
	}

// this function generates a CSV file of notification data
function get_auditlog_csv(){

	$xml=get_auditlog_xml();
	
	// output header for csv
	//header('Content-type: application/text');
	//header("Content-length: " . filesize($thefile)); 
	//header('Content-Disposition: attachment; filename="'.basename($thefile).'"');
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=\"auditlog.csv\"");

	// column definitions
	//echo "time,type,data\n";
	echo "id,time,source,user,type,ip_address,message\n";
	
	// bail out of trial expired
	if(enterprise_features_enabled()==false)
		return;
	
	//print_r($xml);
	//exit();

	if($xml){
		foreach($xml->auditlogentry as $a){
		
			echo "\"".$a->id."\",\"".$a->log_time."\",\"".$a->source."\",\"".$a->user."\",\"".$a->type."\",\"".$a->ip_address."\",\"".$a->message."\"\n";
			}
		}
	}
	

?>
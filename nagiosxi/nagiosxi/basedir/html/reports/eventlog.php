<?php
// EVENT LOG REPORTS
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: eventlog.php 1291 2012-07-06 18:20:45Z swilkerson $

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
	
	// only admins can see the event log
	$user_id=grab_request_var("user_id");
	if(is_admin()==false && !get_user_meta($user_id,"authorized_for_monitoring_system")){
		echo "You are not authorized to view the event log";
		exit();
		}
	
	$mode=grab_request_var("mode","");
	switch($mode){
		case "csv":
			get_eventlog_csv();
			break;
		case "pdf":
			get_eventlog_pdf();
			break;
		default:
			display_eventlog();
			break;
		}
	}


///////////////////////////////////////////////////////////////////
// BACKEND DATA FUNCTIONS
///////////////////////////////////////////////////////////////////
	
// this function gets event log data in XML format from the backend
function get_eventlog_data($args){

	$xml=get_xml_logentries($args);
	
	return $xml;
	}
	
///////////////////////////////////////////////////////////////////
// HELPER FUNCTIONS
///////////////////////////////////////////////////////////////////

// given an event log type, return corresponding image and text to use...
function get_eventlog_type_info($entrytype,$entrytext,&$img,&$text){

	// initial/default values
	$img="info.png";
	$text="";
	//return;

	// what type of log entry is this?  we change the image used for each line based on what type it is...
	switch($entrytype){
		case NAGIOSCORE_LOGENTRY_RUNTIME_ERROR:
			$img="critical.png";
			$text="Runtime Error";
			break;
		case NAGIOSCORE_LOGENTRY_RUNTIME_WARNING:
			$img="warning.png";
			$text="Runtime Warning";
			break;
		case NAGIOSCORE_LOGENTRY_VERIFICATION_ERROR:
			$img="critical.png";
			$text="Verification Error";
			break;
		case NAGIOSCORE_LOGENTRY_VERIFICATION_WARNING:
			$img="warning.png";
			$text="Verification Warning";
			break;
		case NAGIOSCORE_LOGENTRY_CONFIG_ERROR:
			$img="critical.png";
			$text="Configuration Error";
			break;
		case NAGIOSCORE_LOGENTRY_CONFIG_WARNING:
			$img="warning.png";
			$text="Configuration Warning";
			break;
		case NAGIOSCORE_LOGENTRY_PROCESS_INFO:
			$text="Process Information";
			break;
		case NAGIOSCORE_LOGENTRY_EVENT_HANDLER:
			$img="action.gif";
			$text="Event Handler";
			break;
		case NAGIOSCORE_LOGENTRY_NOTIFICATION:
			$img="notify.gif";
			$text="Notification";
			break;
		case NAGIOSCORE_LOGENTRY_EXTERNAL_COMMAND:
			$img="command.png";
			$text="External Command";
			break;
		case NAGIOSCORE_LOGENTRY_HOST_UP:
			$img="recovery.png";
			$text="Host Recovery";
			break;
		case NAGIOSCORE_LOGENTRY_HOST_DOWN:
			$img="critical.png";
			$text="Host Down";
			break;
		case NAGIOSCORE_LOGENTRY_HOST_UNREACHABLE:
			$img="critical.png";
			$text="Host Unreachable";
			break;
		case NAGIOSCORE_LOGENTRY_SERVICE_OK:
			$img="recovery.png";
			$text="Service Recovery";
			break;
		case NAGIOSCORE_LOGENTRY_SERVICE_UNKNOWN:
			$img="unknown.png";
			$text="Service Unknown";
			break;
		case NAGIOSCORE_LOGENTRY_SERVICE_WARNING:
			$img="warning.png";
			$text="Service Warning";
			break;
		case NAGIOSCORE_LOGENTRY_SERVICE_CRITICAL:
			$img="critical.png";
			$text="Service Critical";
			break;
		case NAGIOSCORE_LOGENTRY_PASSIVE_CHECK:
			$img="passiveonly.gif";
			$text="Passive Check";
			break;
		case NAGIOSCORE_LOGENTRY_INFO_MESSAGE:
		
			$img="info.png";
			$text="Information";
			
			if(strstr($entrytext," starting...")){
					$img="start";
					$text="Program Start";
			        }
			else if(strstr($entrytext," shutting down...")){
					$img="stop.gif";
					$text="Program Stop";
			        }
			else if(strstr($entrytext,"Bailing out")){
					$img="stop.gif";
					$text="Program Halt";
			        }
			else if(strstr($entrytext," restarting...")){
					$img="restart.gif";
					$text="Program Restart";
			        }		

			else if(strstr($entrytext,"SERVICE EVENT HANDLER:")){
					$img="serviceevent.gif";
					$text="Service Event Handler";
			        }
			else if(strstr($entrytext,"HOST EVENT HANDLER:")){
					$img="hostevent.gif";
					$text="Host Event Handler";
			        }					
			
			else if(strstr($entrytext," FLAPPING ALERT:")){
				$img="flapping.gif";
				if(strstr($entrytext,";STARTED;"))
					$text="Flapping Start";
				else if(strstr($entrytext,";DISABLED;"))
					$text="Flapping Disabled";
				else
					$text="Flapping Stop";
				}
				
			else if(strstr($entrytext," DOWNTIME ALERT:")){
				$img="downtime.gif";
				if(strstr($entrytext,";STARTED;"))
					$text="Scheduled Downtime Start";
				else if(strstr($entrytext,";CANCELLED;"))
					$text="Scheduled Downtime Cancelled";
				else
					$text="Scheduled Downtime Stop";
				}
				
			break;
		case NAGIOSCORE_LOGENTRY_HOST_NOTIFICATION:
			$img="notify.gif";
			$text="Host Notification";
			break;
		case NAGIOSCORE_LOGENTRY_SERVICE_NOTIFICATION:
			$img="notify.gif";
			$text="Service Notification";
			break;
		
		default:
			$img="info.png";
			break;
		}
	}
	
	
///////////////////////////////////////////////////////////////////
// REPORT GENERATION FUCNTIONS
///////////////////////////////////////////////////////////////////

// this function displays event log data in HTML
function display_eventlog(){
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
	
	// fix search
	if($search==$lstr['SearchBoxText'])
		$search="";
	

	// start/end times must be in unix timestamp format
	// if they weren't specified, default to last 24 hours
	//$endtime=grab_request_var("endtime",time());
	//$starttime=grab_request_var("starttime",$endtime-(24*60*60));
	
	// determine start/end times based on period
	get_times_from_report_timeperiod($reportperiod,$starttime,$endtime,$startdate,$enddate);
	
	// get XML data from backend - the most basic example
	// this would return all records (no paging), so it could be used for CSV export
	/*
	$args=array(
		"starttime" => $starttime,
		"endtime" => $endtime,
		);
	$xml=get_eventlog_data($args);
	*/
	// NOTES: 
	// TOTAL RECORD COUNT (FOR PAGING): if you wanted to get the total count of records in a given timeframe (instead of the records themselves), use this:
	/**/
	$args=array(
		"starttime" => $starttime,
		"endtime" => $endtime,
		"totals" => 1,
		);
	if($search)
		$args["logentry_data"]="lk:".$search;
	$xml=get_eventlog_data($args);
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
		$args["logentry_data"]="lk:".$search;
	$xml=get_eventlog_data($args);
	//$xml=null;
	/**/
	
	// start the HTML page
	do_page_start(array("page_title"=>"Event Log"),true);
	
?>
	<h1>Event Log</h1>
	
<?php
	
?>
	<form method="get" action="<?php echo htmlentities($_SERVER["REQUEST_URI"]);?>">
	
	<div class="reportexportlinks">
	<?php echo get_add_myreport_html("Event Log",$_SERVER["REQUEST_URI"],array());?>
<?php
	$url="?1";
	foreach($request as $var => $val)
		$url.="&".urlencode($var)."=".urlencode($val);
?>
	<a href="<?php echo $url;?>&mode=csv" alt="Export As CSV" title="Export As CSV"><img src="<?php echo theme_image("csv.png");?>"></a>
	<a href="<?php echo $url;?>&mode=pdf" alt="Download As PDF" title="Download As PDF"><img src="<?php echo theme_image("pdf.png");?>"></a>
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
	<input type="text" size="15" name="search" id="searchBox" value="<?php echo htmlentities($searchstring);?>" class="<?php echo $searchclass;?>" />
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
		//"search" => $search,
		);
	echo table_record_count_text($pager_results,$search,true,$clear_args);
	?>
	</div>	
	
	<div class="eventlogentries">
<?php
	
	if($xml){
		foreach($xml->logentry as $le){
		
			// what type of log entry is this?  we change the image used for each line based on what type it is...
			$entrytype=intval($le->logentry_type);
			$entrytext=strval($le->logentry_data);
			
			get_eventlog_type_info($entrytype,$entrytext,$type_img,$type_text);
			
			echo "<div class='logentry logentry-".$entrytype."'><span class='logentrytype'><img src='".nagioscore_image($type_img)."' alt='".$type_text."' title='".$type_text."'></span><span class='logentrytime'>".$le->entry_time."</span><span class='logentrydata'>".$entrytext."</span></div>";
			}
		}
?>
	</div>
	
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
	
	
// this function gets the XML records of event log data for multiple
// output formats (CSV, PDF, HTML)
function get_eventlog_xml(){
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
	if($search)
		$args["logentry_data"]="lk:".$search;
	$xml=get_eventlog_data($args);
	return $xml;
	}

// this function generates a CSV file of event log data
function get_eventlog_csv(){
	$xml = get_eventlog_xml();
	
	// output header for csv
	//header('Content-type: application/text');
	//header("Content-length: " . filesize($thefile)); 
	//header('Content-Disposition: attachment; filename="'.basename($thefile).'"');
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=\"eventlog.csv\"");

	// column definitions
	//echo "time,type,data\n";
	echo "type,time,information\n";

	if($xml){
		foreach($xml->logentry as $le){
		
			// what type of log entry is this?  we change the image used for each line based on what type it is...
			$entrytype=intval($le->logentry_type);
			$entrytext=strval($le->logentry_data);
			
			// image
			get_eventlog_type_info($entrytype,$entrytext,$type_img,$type_text);

			//echo $le->entry_time.",".$entrytype.",".$entrytext."\n";
			echo $type_text.",".$le->entry_time.",\"".str_replace('&apos;', "'",html_entity_decode($entrytext))."\"\n";
			}
		}
	}
	
	
// This function generates a PDF of event log data.
// Requires the "fpdp.php" library and the "fonts" directory for font metrics.
function get_eventlog_pdf(){
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
	
	// fix search
	if($search==$lstr['SearchBoxText'])
		$search="";

	// determine start/end times based on period
	get_times_from_report_timeperiod($reportperiod,$starttime,$endtime,$startdate,$enddate);


	$date_text="".get_datetime_string($starttime,DT_SHORT_DATE_TIME,DF_AUTO,"null")." To ".get_datetime_string($endtime,DT_SHORT_DATE_TIME,DF_AUTO,"null")."";
	
	// get event log entries in XML
	$xml = get_eventlog_xml();
	
	$records=0;
	if($xml){
		$records=intval($xml->recordcount);
		}

	$pdf=new NagiosReportPDF();
	$pdf->page_title='Event Log';
	$pdf->page_subtitle=$date_text."\n\n"."Showing ".$records." Log Entries";
	if($search!="")
		$pdf->page_subtitle.=" Matching '".$search."'";
	
	$pdf->AliasNbPages();
	$pdf->AddPage();
	$pdf->SetFont("Helvetica", "", 7);
	
		
	$fs=' style="times" size="10" style="bold"';
	
	$html='
	<table border="1" align="center">
	<tr	bgcolor="#cccccc" repeat>
	<td '.$fs.'>Type</td>
	<td '.$fs.'>Date / Time</td>
	<td '.$fs.'>Information</td>
	</tr>
	';
	
	$fs=' size="8" style=""';
	
	$type_image="";
	$type_text="";

	if($xml){
		foreach($xml->logentry as $le){

			// what type of log entry is this?  we change the image used for each line based on what type it is...
			$entrytype=intval($le->logentry_type);
			$entrytext=strval($le->logentry_data);
			
			// image
			get_eventlog_type_info($entrytype,$entrytext,$type_img,$type_text);
			//$pdf->Image(get_base_dir().'/includes/components/nagioscore/ui/images/'.$type_img,$pdf->GetX(),$pdf->GetY(),4,4);
			
			//$text = $le->entry_time . " " . $entrytext;
			//<img src="'.get_base_dir().'/includes/components/nagioscore/ui/images/'.$type_img.'">
			
			$html.='<tr><td '.$fs.'>'.$type_text.'</td><td '.$fs.'>'.$le->entry_time.'</td><td '.$fs.'>'.str_replace('&apos;', "'",html_entity_decode($entrytext)).'</td></tr>';
			
			// MultiCell handles line breaks, keeps text from overflowing.
			//$pdf->SetX($pdf->GetX()+4);
			//$pdf->MultiCell(0, 6, $text, 0, "L");
			}
		}

	$html.='</table>';
	
	//$pdf->setfont('times','I',8);
	$pdf->htmltable($html);		

	$pdf->Output("eventlog.pdf", "I");
	}

?>

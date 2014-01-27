<?php
// NOTIFICATIONS REPORT
//
// Copyright (c) 2010 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: notifications.php 1216 2012-06-14 18:43:45Z egalstad $

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
			get_notifications_csv();
			break;
		case "pdf":
			get_notifications_pdf();
			break;
		default:
			display_notifications();
			break;
		}
	}


///////////////////////////////////////////////////////////////////
// BACKEND DATA FUNCTIONS
///////////////////////////////////////////////////////////////////
	
// this function gets notification data in XML format from the backend
function get_notifications_data($args){

	//$xml=get_xml_notifications($args);
	$xml=get_xml_notificationswithcontacts($args);
	
	return $xml;
	}
	
///////////////////////////////////////////////////////////////////
// REPORT GENERATION FUCNTIONS
///////////////////////////////////////////////////////////////////

// this function displays notifications in HTML
function display_notifications(){
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
		
	if($search!='') {
		$args['search']=$search; 
		/*
		$args['host_name'] = " lk:".$search.' ; '; 
		$args['service_description'] = "lk:".$search.' ; ';
		$args['contact_name'] = "lk:".$search.' ; ';
		$args["output"]="lk:".$search;
		*/
	}
		
	if($host!="")
		$args["host_name"]=$host;
	if($service!="")
		$args["service_description"]=$service;
		
	$xml=get_notifications_data($args);
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
		
	if($search!='') //searches host, service, contact, and output
		$args['search']=$search; 
	
	if($host!="")
		$args["host_name"]=$host;
	if($service!="")
		$args["service_description"]=$service;
	//print_r($args);

	$xml=get_notifications_data($args);
	//$xml=null;
	/**/
	//print_r($xml);

	
	// start the HTML page
	do_page_start(array("page_title"=>"Notifications"),true);
	
?>
	<h1>Notifications</h1>
	
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
	<?php echo get_add_myreport_html("Notifications",$_SERVER["REQUEST_URI"],array());?>
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
		"host" => $host,
		"service" => $service,
		//"search" => $search,
		);
	echo table_record_count_text($pager_results,$search,true,$clear_args);
	?>
	</div>	
	
	<div class="notificationentries">
	<table class="standardtable notificationtable">
	<thead>
	<tr><th>Date / Time</th><th>Host</th><th>Service</th><th>Reason</th><th>Escalated</th><th>State</th><th>Contact</th><th>Dispatcher</th><th>Information</th><th>IDs</th></tr>
	</thead>
	<tbody>
<?php
	if($xml){
		//echo "GOT XML!<BR>";
		//print_r($xml);
		if($total_records==0){
			echo "<tr><td colspan='10'>No matching results found.  Try expanding your search criteria.</td></tr>\n";
			}			
		else foreach($xml->notification as $not){
		
			// what type of log entry is this?  we change the image used for each line based on what type it is...
			//$entrytype=intval($le->logentry_type);
			//$entrytext=strval($le->logentry_data);
			
			//get_eventlog_type_info($entrytype,$entrytext,$type_img,$type_text);
			
			//echo "<div class='logentry logentry-".$entrytype."'><span class='logentrytype'><img src='".nagioscore_image($type_img)."' alt='".$type_text."' title='".$type_text."'></span><span class='logentrytime'>".$le->entry_time."</span><span class='logentrydata'>".$entrytext."</span></div>";
			
			$type_text="";
			$trclass="";
			$tdclass="";
						
			$object_type=intval($not->objecttype_id);
			$host_name=strval($not->host_name);
			$service_description=strval($not->service_description);
			$output=strval($not->output);
			$contact=strval($not->contact_name);
			$command=strval($not->notification_command);
			//$type=intval($not->notification_type);
			$reason=intval($not->notification_reason);
			$escalated=intval($not->escalated);
			
			$state=intval($not->state);
			
			$reason_text=get_notification_reason_string($reason,$object_type,$state);
			//$type_text=get_notification_type_string($type,$object_type,$state);
			
			if($escalated==0)
				$escalated_text="No";
			else
				$escalated_text="Yes";
			
			if($object_type==OBJECTTYPE_HOST){
				$state_text=host_state_to_string($state);
				switch($state){
					case 0:
						$trclass="hostrecovery";
						$tdclass="hostup";
						break;
					case 1:
						$trclass="hostproblem";
						$tdclass="hostdown";
						break;
					case 2:
						$trclass="hostproblem";
						$tdclass="hostunreachable";
						break;
					default:
						break;
					}
				}
			else{
				$state_text=service_state_to_string($state);
					switch($state){
						case 0:
							$trclass="servicerecovery";
							$tdclass="serviceok";
							break;
						case 1:
							$trclass="serviceproblem";
							$tdclass="servicewarning";
							break;
						case 2:
							$trclass="serviceproblem";
							$tdclass="servicecritical";
							break;
						case 2:
							$trclass="serviceproblem";
							$tdclass="serviceunknown";
							break;
						default:
							break;
						}				
					}
			$state_type_text=state_type_to_string($state);
			
			$dispatcher="";
			switch($command){
				case "xi_host_notification_handler":
				case "xi_service_notification_handler":
					$dispatcher="Nagios XI";
					break;
				default:
					$dispatcher="Custom: $command";
					break;
				}
			
			$base_url=get_base_url()."includes/components/xicore/status.php";
			$host_url=$base_url."?show=hostdetail&host=".urlencode($host_name);
			$service_url=$base_url."?show=servicedetail&host=".urlencode($host_name)."&service=".urlencode($service_description);
			

			//echo "<div class='statehistory '><span class='statehistorytype'><img src='' alt='".$type_text."' title='".$type_text."'></span><span class='statehistorytime'>".$se->state_time."</span><span class='statehistoryhost'>".$host_name."</span><span class='statehistoryservice'>".$service_description."</span><span class='statehistorydata'>".$output."</span></div>";


			echo "<tr class='".$trclass."'>";
			echo "<td nowrap><span class='notificationtime'>".$not->start_time."</span></td>";
			echo "<td><a href='".$host_url."'>".$host_name."</a></td>";
			
			if($service_description=="")
				echo "<td>-</td>";
			else
				echo "<td><a href='".$service_url."'>".$service_description."</a></td>";
			echo "<td>".$reason_text."</td>";
			echo "<td>".$escalated_text."</td>";
			//echo "<td>".$type_text."</td>";
			echo "<td class='".$tdclass."'>".$state_text."</td>";
			echo "<td>".$contact."</td>";
			echo "<td>".$dispatcher."</td>";
			echo "<td>".$output."</td>";
			echo "<td>NID: ".$not->notification_id.", COID: ".$not->contact_object_id.", CNID: ".$not->contactnotification_id.", CNMID: ".$not->contactnotificationmethod_id."</td>";
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
	
	
// this function gets the XML records of notification data for multiple
// output formats (CSV, PDF, HTML)
function get_notifications_xml(){
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
	if($host!="")
		$args["host_name"]=$host;
	if($service!="")
		$args["service_description"]=$service;
	if($search){
		$args["output"]="lk:".$search;
		}
	$xml=get_notifications_data($args);
	return $xml;
	}

// this function generates a CSV file of notification data
function get_notifications_csv(){

	$xml=get_notifications_xml();
	
	// output header for csv
	//header('Content-type: application/text');
	//header("Content-length: " . filesize($thefile)); 
	//header('Content-Disposition: attachment; filename="'.basename($thefile).'"');
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=\"notifications.csv\"");

	// column definitions
	//echo "time,type,data\n";
	echo "time,host,service,reason,escalated,state,contact,dispatcher,command,information\n";
	
	//print_r($xml);
	//exit();

	if($xml){
		foreach($xml->notification as $not){
		
			// what type of log entry is this?  we change the image used for each line based on what type it is...
			$object_type=intval($not->objecttype_id);
			$host_name=strval($not->host_name);
			$service_description=strval($not->service_description);
			if($object_type==OBJECTTYPE_HOST){
				$state=host_state_to_string(intval($not->state));
				}
			else{
				$state=service_state_to_string(intval($not->state));
				}
			$output=strval($not->output);
			$contact=strval($not->contact_name);
			$command=strval($not->notification_command);

			$reason=intval($not->notification_reason);
			$reason_text=get_notification_reason_string($reason,$object_type,$state);

			$escalated=intval($not->escalated);
			
			$dispatcher="";
			switch($command){
				case "xi_host_notification_handler":
				case "xi_service_notification_handler":
					$dispatcher="Nagios XI";
					break;
				default:
					$dispatcher="Custom";
					break;
				}
			
			if($service_description=="")
				$service_description="-";

			echo $not->start_time.",\"".$host_name."\",\"".$service_description."\",\"".$reason_text."\",\"".$escalated."\",\"".$state."\",\"".$contact."\",".$dispatcher.",".$command.",\"".str_replace('&apos;', "'",html_entity_decode($output))."\"\n";
			}
		}
	}
	
	
// This function generates a PDF of event log data.
// Requires the "fpdp.php" library and the "fonts" directory for font metrics.
function get_notifications_pdf(){
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
	
	// fix search
	if($search==$lstr['SearchBoxText'])
		$search="";

	// determine start/end times based on period
	get_times_from_report_timeperiod($reportperiod,$starttime,$endtime,$startdate,$enddate);


	$date_text="".get_datetime_string($starttime,DT_SHORT_DATE_TIME,DF_AUTO,"null")." To ".get_datetime_string($endtime,DT_SHORT_DATE_TIME,DF_AUTO,"null")."";
	
	// get data in XML
	$xml=get_notifications_xml();
	
	$records=0;
	if($xml){
		$records=intval($xml->recordcount);
		}

	$pdf=new NagiosReportPDF();
	$pdf->page_title='Notifications';
	$pdf->page_subtitle="";
	if($service!=""){
		$pdf->page_subtitle.=$host."\n";
		$pdf->page_subtitle.=$service."\n\n";
		}
	else if($host!=""){
		$pdf->page_subtitle.=$host."\n\n";
		}
	$pdf->page_subtitle.=$date_text."\n\n"."Showing ".$records." Notifications";
	if($search!="")
		$pdf->page_subtitle.=" Matching '".$search."'";
	
	$pdf->AliasNbPages();
	$pdf->AddPage();
	$pdf->SetFont("Helvetica", "", 7);
	
	$fs=' style="times" size="10" style="bold"';
	
	$html='
	<table border="1" align="center">
	<tr	bgcolor="#cccccc" repeat>
	<td '.$fs.'>Date / Time</td>
	<td '.$fs.'>Host</td>
	<td '.$fs.'>Service</td>
	<td '.$fs.'>Reason</td>
	<td '.$fs.'>State</td>
	<td '.$fs.'>Contact</td>
	<td '.$fs.'>Dispatcher</td>
	<td '.$fs.'>Information</td>
	</tr>
	';
	
	$fs=' size="8" style=""';

	$type_image="";
	$type_text="";

	if($xml){
		foreach($xml->notification as $not){

			$object_type=intval($not->objecttype_id);
			$host_name=strval($not->host_name);
			$service_description=strval($not->service_description);
			if($object_type==OBJECTTYPE_HOST){
				$state=host_state_to_string(intval($not->state));
				}
			else{
				$state=service_state_to_string(intval($not->state));
				}
			$output=strval($not->output);
			$contact=strval($not->contact_name);
			$command=strval($not->notification_command);

			$reason=intval($not->notification_reason);
			$reason_text=get_notification_reason_string($reason,$object_type,$state);
			
			$dispatcher="";
			switch($command){
				case "xi_host_notification_handler":
				case "xi_service_notification_handler":
					$dispatcher="Nagios XI";
					break;
				default:
					$dispatcher="Custom: $command";
					break;
				}

			if($service_description=="")
				$service_description="-";
			
			//$text = $not->start_time."\t".$host_name."\t".$service_description."\t".$state."\t".$contact."\t".$dispatcher."\t".$command."\t".str_replace('&apos;', "'",html_entity_decode($output));
			$html.= "<tr><td ".$fs.">".$not->start_time."</td><td ".$fs.">".$host_name."</td><td ".$fs.">".$service_description."</td><td ".$fs.">".$reason_text."</td><td ".$fs.">".$state."</td><td ".$fs.">".$contact."</td><td ".$fs.">".$dispatcher."</td><td ".$fs.">".str_replace('&apos;', "'",html_entity_decode($output))."</td></tr>";
						
			//$pdf->SetX($pdf->GetX()+4);
			//$pdf->MultiCellTag(0, 6, $text, 0, "L", 0, 0, 0, 0, 0);
			}
		}

	$html.='</table>';
	
	//$pdf->setfont('times','I',8);
	$pdf->htmltable($html);	

	$pdf->Output("notifications.pdf", "I");
	}
	
	
///////////////////////////////////////////////////////////////////
// HELPER FUNCTIONS
///////////////////////////////////////////////////////////////////

// return corresponding image and text to use...
function get_notification_type_info($objecttype,$state,$statetype,&$img,&$text){

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

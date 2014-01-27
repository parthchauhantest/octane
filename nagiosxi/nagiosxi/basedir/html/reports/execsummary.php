<?php
// EXECUTIVE SUMMARY REPORT
//
// Copyright (c) 211 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: execsummary.php 1117 2012-04-12 15:37:13Z mguthrie $

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
	
	/*
	if(is_admin()==false){
		echo "You are not authorized to view this report";
		exit();
		}
	*/
	
	$mode=grab_request_var("mode","");
	switch($mode){
		case "pdf":
			get_report_pdf();
			break;
		default:
			display_report();
			break;
		}
	}


	
///////////////////////////////////////////////////////////////////
// REPORT GENERATION FUCNTIONS
///////////////////////////////////////////////////////////////////

// this function displays report in HTML
function display_report(){
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
	//flag to hide select options (scheduled report and pdf)  
	$hideoptions = grab_request_var('hideoptions',false);
	
	// fix search
	if($search==$lstr['SearchBoxText'])
		$search="";
	
	$host=grab_request_var("host","");
	$service=grab_request_var("service","");
	$hostgroup=grab_request_var("hostgroup","");
	$servicegroup=grab_request_var("servicegroup","");

	
	// start/end times must be in unix timestamp format
	// if they weren't specified, default to last 24 hours
	//$endtime=grab_request_var("endtime",time());
	//$starttime=grab_request_var("starttime",$endtime-(24*60*60));
	
	// determine start/end times based on period
	get_times_from_report_timeperiod($reportperiod,$starttime,$endtime,$startdate,$enddate);
	
	
	// start the HTML page
	do_page_start(array("page_title"=>"Executive Summary"),true);
	
?>
	<h1>Executive Summary</h1>
	
<?php
	if(!$hideoptions) { //options can be suppressed for scheduled reports 
?>
	<form method="get" action="<?php echo htmlentities($_SERVER["REQUEST_URI"]);?>">
	
	<div class="reportexportlinks">
	<?php echo get_add_myreport_html("Event Log",htmlentities($_SERVER["REQUEST_URI"]),array());?>
<?php
	$url="?1";
	foreach($request as $var => $val)
		$url.="&".urlencode($var)."=".urlencode($val);
?>
	<!--
	<a href="<?php echo $url;?>&mode=pdf" alt="Download As PDF" title="Download As PDF"><img src="<?php echo theme_image("pdf.png");?>"></a>
	//-->
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
		
	<div class="reportoptionpicker">
	<!-- options go here... -->
	Limit To&nbsp;

	<select name="host" id="hostList">
	<option value="">Host:</option>
<?php
	$args=array('brevity' => 1); 
	$oxml=get_xml_host_objects($args);
	if($oxml){
		foreach($oxml->host as $hostobject){
			$name=strval($hostobject->host_name);
			echo "<option value='".$name."' ".is_selected($host,$name).">$name</option>\n";
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

	</div>
	</form>
<?php
} //end if $hide_options
?>	
	
	<div>
	From: <b><?php echo get_datetime_string($starttime,DT_SHORT_DATE_TIME,DF_AUTO,"null");?></b> 
	To: <b><?php echo get_datetime_string($endtime,DT_SHORT_DATE_TIME,DF_AUTO,"null");?></b>
	</div>

	
	<script type='text/javascript'>
	/* AJAX load the different report from around the site */
	$(document).ready(function() {
<?php
		$arg = '';
		foreach($request as $var => $val)
			$arg.="&".urlencode($var)."=".urlencode($val);
?>		
		$('#inner_availability').load('availability.php?smallimages=true<?php echo $arg;?> #availabilityreport');
		$('#inner_top_alert_producers').load('topalertproducers.php?records=10<?php echo $arg;?> #top_alert_producers');
		//load dashlet content into div 
		$('#inner_latest_alerts').each(function() {
			var optsarr = {
				"func": "get_latestalerts_dashlet_html",
				"args": {"type" : "", "host" : "", "hostgroup" : "<?php echo $hostgroup;?>", "servicegroup" : "<?php echo $servicegroup;?>", "maxitems" : "10"}
			}
			var opts=array2json(optsarr);
			get_ajax_data_innerHTML("getxicoreajax",opts,true,this);
		}); 
	
	});
	</script>

	<!-- remove inline styles -->
	<div id='allreports' style='margin:10px auto;'>
	
	<div class='availability' style='float:left; width:500px;'>
		<h3>Availability</h3>
		<div id='inner_availability'>
			<div class="childcontentthrobber" id='availabilitythrobber'>
				<img src="/nagiosxi/images/throbber1.gif" />
			</div>
		</div>	
	</div>	
	<div class='top_alert_producers' style='float:left; width: 450px;'>
		<h3>Top Alert Producers</h3>
		<div id='inner_top_alert_producers'>
			<div class="childcontentthrobber" id='topalertsthrobber'>
				<img src="/nagiosxi/images/throbber1.gif" />
			</div>			
		</div>
	</div>
	
	<br clear="all" />
	<!-- remove inline styles -->
	<div class='alert_histogram'>
		<h3>Alert Histogram</h3>
		<div id='inner_alert_histogram'>
			<div class="childcontentthrobber" id='histogramthrobber'>
<?php
		$url = 'histogram.php?'.time(); 
		foreach($request as $var => $val)
			$url.="&".urlencode($var)."=".urlencode($val);
?>			
				<img src="<?php echo $url; ?>&mode=image" height='225' width='600' alt='Alert Histogram Image' />
			</div>
		</div>
	</div>
	
	<!-- style="float: left; margin: 10px; padding: 10px; border: 1px solid gray;" -->

	
	<div class='most_recent_alerts'>
		<div id='inner_latest_alerts'>
			<div class="childcontentthrobber" id='latestalertsthrobber'>
				<img src="/nagiosxi/images/throbber1.gif" />
			</div>
		</div>
	</div>

	</div> <!-- end allreports div -->
<?php		
	
	// closes the HTML page
	do_page_end(true);
	}
	
?>
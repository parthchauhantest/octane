<?php
// AVAILABILITY REPORT
//
// Copyright (c) 2010 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: availability.php 1227 2012-06-19 19:50:08Z mguthrie $

ini_set('display_errors','off'); //graphs will not generate if error messaging turned on

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

	$host=grab_request_var("host","");
	$service=grab_request_var("service","");
	$hostgroup=grab_request_var("hostgroup","");
	$servicegroup=grab_request_var("servicegroup","");
	
	// check perms
	$auth=true;
	if($service!="")
		$auth=is_authorized_for_service(0,$host,$service);
	else if($host!="")
		$auth=is_authorized_for_host(0,$host);
	else if($hostgroup!="")
		$auth=is_authorized_for_hostgroup(0,$hostgroup);
	else if($servicegroup!="")
		$auth=is_authorized_for_servicegroup(0,$servicegroup);
	if($auth==false){
		echo "ERROR: You are not authorized to view this report.";
		exit;
		}


	$mode=grab_request_var("mode","");
	switch($mode){
		case "csv":
			get_availability_csv();
			break;
		case "pdf":
			get_availability_pdf();
			break;
		case "getchart":
			get_chart_image();
			break;
		default:
			display_availability();
			break;
		}
	}

	
///////////////////////////////////////////////////////////////////
// CHART FUNCTIONS
///////////////////////////////////////////////////////////////////


function get_chart_image(){

	$width=grab_request_var("width",350);
	$height=grab_request_var("height",250);
	$angle=grab_request_var("angle",45);
	$title=grab_request_var("title","");
	$rawdata=grab_request_var("data","");
	$type=grab_request_var("type","host");
	if($rawdata=="")
		$data=array();
	else
		$data=explode(",",$rawdata);
	$rawcolors=grab_request_var("colors","");
	if($rawcolors=="")
		$colors=array('1E90FF','2E8B57','ADFF2F','DC143C','BA55D3');
	else
		$colors=explode(",",$rawcolors);
	$rawlegend=grab_request_var("legend","");
	if($rawlegend=="")
		$legend=array();
	else
		$legend=explode(",",$rawlegend);
		
		
	// limit decimal points in data
	foreach($data as $idx => $d){
		$newd=number_format($d,3);
		if($newd>0.0){
			$data[$idx]=$newd;
			}
		else{
			unset($data[$idx]);
			unset($legend[$idx]);
			unset($colors[$idx]);
			}
		}
	
	
	// Some data
	//$data = array(40,21,17,14,23);

	
	if(use_pchart()==true){
		require_once ('../includes/components/pchart/pChart/pData.class');  
		require_once ('../includes/components/pchart/pChart/pChart.class');
		
		// dataset definition   
		$DataSet = new pData;  
		$DataSet->AddPoint($data,"Series1");  
		$DataSet->AddPoint($legend,"Series2");  
		$DataSet->AddAllSeries();  
		$DataSet->SetAbsciseLabelSerie("Series2");  

		// initialise the graph  
		$Test = new pChart($width,$height);  
		
		// initialize colors
		$Test->loadColorPalette("../includes/components/pchart/palettes/availability-".$type.".txt");  

		// graph background
		$Test->drawFilledRoundedRectangle(7,7,233,213,5,240,240,240);  
		$Test->drawRoundedRectangle(5,5,235,215,5,230,230,230);  

		// draw a shadow under the pie chart  
		$Test->drawFilledCircle(122,122,70,200,200,200);  
		// draw the pie chart  
		$Test->setFontProperties("../includes/components/pchart/Fonts/tahoma.ttf",8);  
		$Test->drawPieGraph($DataSet->GetData(),$DataSet->GetDataDescription(),120,120,70,PIE_PERCENTAGE,TRUE,100,10,5,2);  

		// draw the legend
		$Test->drawPieLegend(220,15,$DataSet->GetData(),$DataSet->GetDataDescription(),250,250,250);

		// draw the title
		$Test->setFontProperties("../includes/components/pchart/Fonts/tahoma.ttf",10);  
		$Test->drawTitle(5,10,$title,10,10,10,230,30); 

		// output the image
		$Test->Stroke("avail.png");  

		}
		
	// use jpgraph
	else{
		require_once ('../includes/components/jpgraph/src/jpgraph.php');
		require_once ('../includes/components/jpgraph/src/jpgraph_pie.php');
		

		// Create the Pie Graph. 
		$graph = new PieGraph($width,$height);

		$theme_class="DefaultTheme";
		//$graph->SetTheme(new $theme_class());

		// Set A title for the plot
		$graph->title->Set($title);
		$graph->SetBox(true);

		// Create
		$p1 = new PiePlot($data);
		$graph->Add($p1);
		
		// set start angle
		$p1->SetStartAngle($angle); 

		// Move center of pie to the left to make better room
		// for the legend
		if(count($legend)>0)
			$p1->SetCenter(0.35,0.5);
		
		// Legends
		$p1->SetLegends($legend);
		$graph->legend->Pos(0.05,0.15);
		$graph->legend->SetLayout(LEGEND_VER);

		$p1->ShowBorder();
		$p1->SetColor('black');
		$p1->SetSliceColors($colors);
		$p1->SetGuideLines();

		$graph->Stroke();
		}
	}
	

///////////////////////////////////////////////////////////////////
// BACKEND DATA FUNCTIONS
///////////////////////////////////////////////////////////////////
	
// this function gets state history data in XML format from the backend
function get_availability_data($type="host",$args,&$data){

	$data=get_xml_availability($type,$args);

	return true;
	}
	
///////////////////////////////////////////////////////////////////
// REPORT GENERATION FUCNTIONS
///////////////////////////////////////////////////////////////////

// this function displays event log data in HTML
function display_availability(){
	global $lstr;
	global $request;

	// makes sure user has appropriate license level
	licensed_feature_check();
	
	// get values passed in GET/POST request
	$reportperiod=grab_request_var("reportperiod","last24hours");
	$startdate=grab_request_var("startdate","");
	$enddate=grab_request_var("enddate","");

	$host=grab_request_var("host","");
	$service=grab_request_var("service","");
	$hostgroup=grab_request_var("hostgroup","");
	$servicegroup=grab_request_var("servicegroup","");
	
	//compact chart view
	$smallimages = grab_request_var('smallimages',false);
	$size = ($smallimages) ? " height='175' width='250'" : '';
	
	// should we show detail by default?
	$showdetail=1;
	if($host=="" && $service=="" && $hostgroup=="" && $servicegroup=="")
		$showdetail=0;
	
	$showdetail=grab_request_var("showdetail",$showdetail);

	// determine start/end times based on period
	get_times_from_report_timeperiod($reportperiod,$starttime,$endtime,$startdate,$enddate);
	
	
	// determine title
	if($service!="")
		$title="Service Availability";
	else if($host!="")
		$title="Host Availability";
	else if($hostgroup!="")
		$title="Hostgroup Availability";
	else if($servicegroup!="")
		$title="Servicegroup Availability";
	else
		$title="Availability Summary";
	

	// start the HTML page
	do_page_start(array("page_title"=>$title),true);
	
	/* TESTING */
	/*
	echo "REPORTPERIOD: $reportperiod<BR>";
	echo "STARTDATE: $startdate<BR>";
	echo "ENDDATE: $enddate<BR>";
	echo "STARTTIME: $starttime<BR>";
	echo "ENDTIME: $endtime<BR>";
	echo "START: ".strftime("%c",$starttime)."<BR>";
	echo "END: ".strftime("%c",$endtime)."<BR>";
	*/
	
?>
	<h1><?php echo $title;?></h1>
	
	
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
	else if($hostgroup!=""){
?>
	<div class="hoststatusdetailheader">
	<div class="hosttitle">
	<div class="hostname"><?php echo htmlentities($hostgroup);?></div>
	</div>
	</div>
<?php
	}
	else if($servicegroup!=""){
?>
	<div class="hoststatusdetailheader">
	<div class="hosttitle">
	<div class="hostname"><?php echo htmlentities($servicegroup);?></div>
	</div>
	</div>
<?php
	}
?>
	
<?php
	if($service!=""){
		$url="statehistory.php?host=".urlencode($host)."&service=".urlencode($service)."&reportperiod=".urlencode($reportperiod)."&startdate=".urlencode($startdate)."&enddate=".urlencode($enddate);
		echo "<a href='".$url."'><img src='".theme_image("history.png")."' alt='View State History' title='View State History'></a>";
		}
	else if($host!=""){
		$url="statehistory.php?host=".urlencode($host)."&reportperiod=".urlencode($reportperiod)."&startdate=".urlencode($startdate)."&enddate=".urlencode($enddate);
		echo "<a href='".$url."'><img src='".theme_image("history.png")."' alt='View State History' title='View State History'></a>";
		}
?>
	<form method="get" action="<?php echo htmlentities($_SERVER["REQUEST_URI"]);?>">
	<input type="hidden" name="host" value="<?php echo htmlentities($host);?>">
	<input type="hidden" name="service" value="<?php echo htmlentities($service);?>">
	
	<div class="reportexportlinks">
	<?php echo get_add_myreport_html($title,$_SERVER["REQUEST_URI"],array());?>
<?php
	$url="?1";
	foreach($request as $var => $val)
		$url.="&".urlencode($var)."=".urlencode($val);
?>
<?php
	if($service!=""){
?>
		<a href="<?php echo $url;?>&mode=csv" alt="Export As CSV" title="Export As CSV"><img src="<?php echo theme_image("csv.png");?>">
<?php
		}
	else{
?>
		<a href="<?php echo $url;?>&mode=csv&csvtype=host" alt="Export Host Data As CSV" title="Export Host Data As CSV"><img src="<?php echo theme_image("csv.png");?>">
		<a href="<?php echo $url;?>&mode=csv&csvtype=service" alt="Export Service Data As CSV" title="Export Service Data As CSV"><img src="<?php echo theme_image("csv.png");?>">
<?php
		}
?>
	<a href="<?php echo $url;?>&mode=pdf" alt="Download As PDF" title="Download As PDF"><img src="<?php echo theme_image("pdf.png");?>"></a>
	</div>
	
	<div class="reportsearchbox">
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
	
	<div>
	From: <b><?php echo get_datetime_string($starttime,DT_SHORT_DATE_TIME,DF_AUTO,"null");?></b> To: <b><?php echo get_datetime_string($endtime,DT_SHORT_DATE_TIME,DF_AUTO,"null");?></b>
	</div>


	
	<div id='availabilityreport' class="availabilityreport">
<?php
/*
	$args=array(
		"host" => $host,
		"service" => $service,
		"hostgroup" => $hostgroup,
		"servicegroup" => $servicegroup,
		"starttime" => $starttime,
		"endtime" => $endtime,
		);
	get_availability_data("host",$args,$hostdata);
	echo "HOSTS<BR>";
	print_r($hostdata);

	get_availability_data("service",$args,$servicedata);
	echo "SERVICES<BR>";
	print_r($servicedata);
*/

	
	///////////////////////////////////////////////////////////////////////////
	// SPECIFIC SERVICE
	///////////////////////////////////////////////////////////////////////////
	if($service!=""){

		// get service availability
		$args=array(
			"host" => $host,
			"service" => $service,
			"starttime" => $starttime,
			"endtime" => $endtime,
			);
		get_availability_data("service",$args,$servicedata);
		
		// check if we have data
		$have_data=false;
		if($servicedata && intval($servicedata->havedata)==1)
			$have_data=true;		
		if($have_data==false){
			echo "<p>Availability data is not available when monitoring engine is not running.</p>";
			}
			
		// we have data..
		else{
		
			$service_ok=0;
			$service_warning=0;
			$service_unknown=0;
			$service_critical=0;
			
			if($servicedata){
				foreach($servicedata->serviceavailability->service as $s){
					$service_ok=floatval($s->percent_known_time_ok);
					$service_warning=floatval($s->percent_known_time_warning);
					$service_unknown=floatval($s->percent_known_time_unknown);
					$service_critical=floatval($s->percent_known_time_critical);
					}
				}
				
			// service chart
			$url="availability.php?mode=getchart&title=Service+Availability&data=".$service_ok.",".$service_warning.",".$service_unknown.",".$service_critical."&legend=Ok,Warning,Unknown,Critical&colors=".get_avail_color("ok").",".get_avail_color("warning").",".get_avail_color("unknown").",".get_avail_color("critical");
			
			echo "<img src='{$url}' {$size}>";
			
			// service table
			if($servicedata){
				echo "<br>";
				echo "<b>Service Data</b>";
				echo "<table class='infotable'>";
				echo "<thead><tr><th>Host&nbsp;</th><th>Service&nbsp;</th><th>Ok&nbsp;</th><th>Warning&nbsp;</th><th>Unknown&nbsp;</th><th>Critical&nbsp;</th></tr></thead>";
				echo "<tbody>";
				$lasthost="";
				foreach($servicedata->serviceavailability->service as $s){
				
					$hn=strval($s->host_name);
					$sd=strval($s->service_description);
					$ok=floatval($s->percent_known_time_ok);
					$wa=floatval($s->percent_known_time_warning);
					$un=floatval($s->percent_known_time_unknown);
					$service_critical=floatval($s->percent_known_time_critical);
					
					// newline
					if($lasthost!=$hn && $lasthost!=""){
						echo "<tr><td colspan='6'><hr noshade size='1'></td></tr>";
						}
					
					echo "<tr>";
					if($lasthost!=$hn)
						echo "<td>".$hn."&nbsp;&nbsp;</td>";
					else
						echo "<td>&nbsp;&nbsp;</td>";
					echo "<td>".$sd."&nbsp;&nbsp;</td>";
					echo "<td>".$ok."%&nbsp;&nbsp;</td>";
					echo "<td>".$wa."%&nbsp;&nbsp;</td>";
					echo "<td>".$un."%&nbsp;&nbsp;</td>";
					echo "<td>".$service_critical."%&nbsp;&nbsp;</td>";
					echo "</tr>";	

					$lasthost=$hn;
					}
				
				echo "</tbody>";
				echo "</table>";
				}

			}

		}
		
	///////////////////////////////////////////////////////////////////////////
	// SPECIFIC HOST
	///////////////////////////////////////////////////////////////////////////
	else if($host!=""){
	
		// get host availability
		$args=array(
			"host" => $host,
			"starttime" => $starttime,
			"endtime" => $endtime,
			);
		get_availability_data("host",$args,$hostdata);

		// getservice availability
		$args=array(
			"host" => $host,
			"starttime" => $starttime,
			"endtime" => $endtime,
			);
		get_availability_data("service",$args,$servicedata);
		
		// check if we have data
		$have_data=false;
		if($hostdata && $servicedata && intval($hostdata->havedata)==1 && intval($servicedata->havedata)==1)
			$have_data=true;		
		if($have_data==false){
			echo "<p>Availability data is not available when monitoring engine is not running.</p>";
			}
			
		// we have data..
		else{

			
			$host_up=0;
			$host_down=0;
			$host_unreachable=0;
			
			if($hostdata){
				foreach($hostdata->hostavailability->host as $h){
					$host_up=floatval($h->percent_known_time_up);
					$host_down=floatval($h->percent_known_time_down);
					$host_unreachable=floatval($h->percent_known_time_unreachable);
					}
				}
				
			// title
			//echo "<h2>Host Data</h2>";
				
			// host chart
			$url="availability.php?mode=getchart&title=Host+Availability&data=".$host_up.",".$host_down.",".$host_unreachable."&legend=Up,Down,Unreachable&colors=".get_avail_color("up").",".get_avail_color("down").",".get_avail_color("unreachable");
			echo "<img src='{$url}' {$size}>";
			

			
			$service_ok=0;
			$service_warning=0;
			$service_unknown=0;
			$service_critical=0;
			
			$avg_service_ok=0;
			$avg_service_warning=0;
			$avg_service_unknown=0;
			$avg_service_critical=0;
			$count_service_critical=0;
			$count_service_warning=0;
			$count_service_unknown=0;
			$count_service_critical=0;
			
			if($servicedata){
				foreach($servicedata->serviceavailability->service as $s){
					$service_ok=floatval($s->percent_known_time_ok);
					$service_warning=floatval($s->percent_known_time_warning);
					$service_unknown=floatval($s->percent_known_time_unknown);
					$service_critical=floatval($s->percent_known_time_critical);
					
					update_avail_avg($avg_service_ok,$service_ok,$count_service_ok);
					update_avail_avg($avg_service_warning,$service_warning,$count_service_warning);
					update_avail_avg($avg_service_unknown,$service_unknown,$count_service_unknown);
					update_avail_avg($avg_service_critical,$service_critical,$count_service_critical);
					}
				}
				
			// title
			//echo "<h2>Service Data</h2>";
				
			// service chart
			$url="availability.php?mode=getchart&title=Average+Service+Availability&data=".$avg_service_ok.",".$avg_service_warning.",".$avg_service_unknown.",".$avg_service_critical."&legend=Ok,Warning,Unknown,Critical&colors=".get_avail_color("ok").",".get_avail_color("warning").",".get_avail_color("unknown").",".get_avail_color("critical");
			// only show service chart if there are services (some percent exists)
			if(($avg_service_ok+$avg_service_warning+$avg_service_unknown+$avg_service_critical)>0) 
				echo "<img src='{$url}' {$size}>";
				
			
			// host table
			if($hostdata){
				echo "<br>";
				echo "<b>Host Data</b>";
				echo "<table class='infotable'>";
				echo "<thead><tr><th>Host&nbsp;</th><th>Up&nbsp;</th><th>Down&nbsp;</th><th>Unreachable&nbsp;</th></tr></thead>";
				echo "<tbody>";
				foreach($hostdata->hostavailability->host as $h){
					/*
					echo "HOST: ".$h->host_name."<BR>";
					echo "UP: ".$h->percent_known_time_up."<BR>";
					echo "DOWN: ".$h->percent_known_time_down."<BR>";
					echo "UNREACHABLE: ".$h->percent_known_time_unreachable."<BR>";
					*/
					$hn=strval($h->host_name);
					$up=floatval($h->percent_known_time_up);
					$dn=floatval($h->percent_known_time_down);
					$un=floatval($h->percent_known_time_unreachable);
					
					echo "<tr>";
					echo "<td>".$hn."&nbsp;&nbsp;</td>";
					echo "<td>".$up."%&nbsp;&nbsp;</td>";
					echo "<td>".$dn."%&nbsp;&nbsp;</td>";
					echo "<td>".$un."%&nbsp;&nbsp;</td>";
					echo "</tr>";		
					}
				echo "</tbody>";
				echo "</table>";
				}

			// service table
			if($servicedata){
				echo "<br>";
				echo "<b>Service Data</b>";
				echo "<table class='infotable'>";
				echo "<thead><tr><th>Host&nbsp;</th><th>Service&nbsp;</th><th>Ok&nbsp;</th><th>Warning&nbsp;</th><th>Unknown&nbsp;</th><th>Critical&nbsp;</th></tr></thead>";
				echo "<tbody>";
				$lasthost="";
				foreach($servicedata->serviceavailability->service as $s){
				
					$hn=strval($s->host_name);
					$sd=strval($s->service_description);
					$ok=floatval($s->percent_known_time_ok);
					$wa=floatval($s->percent_known_time_warning);
					$un=floatval($s->percent_known_time_unknown);
					$service_critical=floatval($s->percent_known_time_critical);
					
					// newline
					if($lasthost!=$hn && $lasthost!=""){
						echo "<tr><td colspan='6'><hr noshade size='1'></td></tr>";
						}
					
					echo "<tr>";
					if($lasthost!=$hn)
						echo "<td>".$hn."&nbsp;&nbsp;</td>";
					else
						echo "<td>&nbsp;&nbsp;</td>";
					echo "<td>".$sd."&nbsp;&nbsp;</td>";
					echo "<td>".$ok."%&nbsp;&nbsp;</td>";
					echo "<td>".$wa."%&nbsp;&nbsp;</td>";
					echo "<td>".$un."%&nbsp;&nbsp;</td>";
					echo "<td>".$service_critical."%&nbsp;&nbsp;</td>";
					echo "</tr>";	

					$lasthost=$hn;
					}
				// averages
				echo "<tr><td colspan='6'><hr noshade size='1'></td></tr>";
				echo "<tr><td>&nbsp;</td><td><b>Average</b>&nbsp;&nbsp;</td><td>".number_format($avg_service_ok,3)."%&nbsp;&nbsp;</td><td>".number_format($avg_service_warning,3)."%&nbsp;&nbsp;</td><td>".number_format($avg_service_unknown,3)."%&nbsp;&nbsp;</td><td>".number_format($avg_service_critical,3)."%&nbsp;&nbsp;</td></tr>";
				
				echo "</tbody>";
				echo "</table>";
				}
			}
				
		}
		
	///////////////////////////////////////////////////////////////////////////
	// SPECIFIC HOSTGROUP OR SERVICEGROUP
	///////////////////////////////////////////////////////////////////////////
	else if($hostgroup!="" || $servicegroup!=""){
	
		//echo "STARTTIME2: $starttime<BR>";
		//echo "ENDTIME2: $endtime<BR>";
	
		// get host availability
		$args=array(
			"host" => "",
			"starttime" => $starttime,
			"endtime" => $endtime,
			);
		if($hostgroup!="")
			$args["hostgroup"]=$hostgroup;
		else
			$args["servicegroup"]=$servicegroup;
		get_availability_data("host",$args,$hostdata);

		// getservice availability
		$args=array(
			"host" => "",
			"starttime" => $starttime,
			"endtime" => $endtime,
			);
		if($hostgroup!="")
			$args["hostgroup"]=$hostgroup;
		else
			$args["servicegroup"]=$servicegroup;
		get_availability_data("service",$args,$servicedata);
		
		// check if we have data
		$have_data=false;
		if($hostdata && $servicedata && intval($hostdata->havedata)==1 && intval($servicedata->havedata)==1)
			$have_data=true;		
		if($have_data==false){
			echo "<p>Availability data is not available when monitoring engine is not running.</p>";
			}
			
		// we have data..
		else{
			
			$host_up=0;
			$host_down=0;
			$host_unreachable=0;
			
			$avg_host_up=0;
			$avg_host_down=0;
			$avg_host_unreachable=0;
			$count_host_up=0;
			$count_host_down=0;
			$count_host_unreachable=0;
			
			if($hostdata){
				foreach($hostdata->hostavailability->host as $h){
					$host_up=floatval($h->percent_known_time_up);
					$host_down=floatval($h->percent_known_time_down);
					$host_unreachable=floatval($h->percent_known_time_unreachable);

					update_avail_avg($avg_host_up,$host_up,$count_host_up);
					update_avail_avg($avg_host_down,$host_down,$count_host_down);
					update_avail_avg($avg_host_unreachable,$host_unreachable,$count_host_unreachable);
					}
				}
				
			// title
			//echo "<h2>Host Data</h2>";
				
			// host chart
			$url="availability.php?mode=getchart&title=Average+Host+Availability&data=".$avg_host_up.",".$avg_host_down.",".$avg_host_unreachable."&legend=Up,Down,Unreachable&colors=".get_avail_color("up").",".get_avail_color("down").",".get_avail_color("unreachable");
			echo "<img src='{$url}' {$size}>";
			
			
			$service_ok=0;
			$service_warning=0;
			$service_unknown=0;
			$service_critical=0;
			
			$avg_service_ok=0;
			$avg_service_warning=0;
			$avg_service_unknown=0;
			$avg_service_critical=0;
			$count_service_critical=0;
			$count_service_warning=0;
			$count_service_unknown=0;
			$count_service_critical=0;
			
			if($servicedata){
				foreach($servicedata->serviceavailability->service as $s){
					$service_ok=floatval($s->percent_known_time_ok);
					$service_warning=floatval($s->percent_known_time_warning);
					$service_unknown=floatval($s->percent_known_time_unknown);
					$service_critical=floatval($s->percent_known_time_critical);
					
					update_avail_avg($avg_service_ok,$service_ok,$count_service_ok);
					update_avail_avg($avg_service_warning,$service_warning,$count_service_warning);
					update_avail_avg($avg_service_unknown,$service_unknown,$count_service_unknown);
					update_avail_avg($avg_service_critical,$service_critical,$count_service_critical);
					}
				}
				
			// title
			//echo "<h2>Service Data</h2>";
				
			// service chart
			$url="availability.php?mode=getchart&title=Average+Service+Availability&data=".$avg_service_ok.",".$avg_service_warning.",".$avg_service_unknown.",".$avg_service_critical."&legend=Ok,Warning,Unknown,Critical&colors=".get_avail_color("ok").",".get_avail_color("warning").",".get_avail_color("unknown").",".get_avail_color("critical");
			echo "<img src='{$url}' {$size}>";
			
			/*
			echo "<BR>HOSTDATA<BR>";
			print_r($hostdata);
			echo "<BR><BR>";
			*/

			// host table
			if($hostdata){
				echo "<br>";
				echo "<b>Host Data</b>";
				echo "<table class='infotable'>";
				echo "<thead><tr><th>Host&nbsp;</th><th>Up&nbsp;</th><th>Down&nbsp;</th><th>Unreachable&nbsp;</th></tr></thead>";
				echo "<tbody>";
				if($showdetail==1){
					$lasthost="";
					foreach($hostdata->hostavailability->host as $h){
					
						$hn=strval($h->host_name);
						$up=floatval($h->percent_known_time_up);
						$dn=floatval($h->percent_known_time_down);
						$un=floatval($h->percent_known_time_unreachable);
						
						if($lasthost!="" && $lasthost!=$hn)
							echo "<tr><td colspan='4'><hr noshade size='1'></td></tr>";
					
						echo "<tr>";
						echo "<td>".$hn."&nbsp;&nbsp;</td>";
						echo "<td>".number_format($up,3)."%&nbsp;&nbsp;</td>";
						echo "<td>".number_format($dn,3)."%&nbsp;&nbsp;</td>";
						echo "<td>".number_format($un,3)."%&nbsp;&nbsp;</td>";
						echo "</tr>";		
						
						$lasthost=$hn;
						}
					}
				// averages
				if($showdetail==1)
					echo "<tr><td colspan='4'><hr noshade size='1'></td></tr>";
				echo "<tr><td><b>Average</b>&nbsp;&nbsp;</td><td>".number_format($avg_host_up,3)."%&nbsp;&nbsp;</td><td>".number_format($avg_host_down,3)."%&nbsp;&nbsp;</td><td>".number_format($avg_host_unreachable,3)."%&nbsp;&nbsp;</td></tr>";

				echo "</tbody>";
				echo "</table>";
				}

			// service table
			if($servicedata){
				echo "<br>";
				echo "<b>Service Data</b>";
				echo "<table class='infotable'>";
				echo "<thead><tr><th>Host&nbsp;</th><th>Service&nbsp;</th><th>Ok&nbsp;</th><th>Warning&nbsp;</th><th>Unknown&nbsp;</th><th>Critical&nbsp;</th></tr></thead>";
				echo "<tbody>";
				if($showdetail==1){
					$lasthost="";
					foreach($servicedata->serviceavailability->service as $s){
					
						$hn=strval($s->host_name);
						$sd=strval($s->service_description);
						$ok=floatval($s->percent_known_time_ok);
						$wa=floatval($s->percent_known_time_warning);
						$un=floatval($s->percent_known_time_unknown);
						$service_critical=floatval($s->percent_known_time_critical);
						
						// newline
						if($lasthost!=$hn && $lasthost!=""){
							echo "<tr><td colspan='6'><hr noshade size='1'></td></tr>";
							}
						
						echo "<tr>";
						if($lasthost!=$hn)
							echo "<td>".$hn."&nbsp;&nbsp;</td>";
						else
							echo "<td>&nbsp;&nbsp;</td>";
						echo "<td>".$sd."&nbsp;&nbsp;</td>";
						echo "<td>".number_format($ok,3)."%&nbsp;&nbsp;</td>";
						echo "<td>".number_format($wa,3)."%&nbsp;&nbsp;</td>";
						echo "<td>".number_format($un,3)."%&nbsp;&nbsp;</td>";
						echo "<td>".$service_critical."%&nbsp;&nbsp;</td>";
						echo "</tr>";	

						$lasthost=$hn;
						}
					}
				// averages
				if($showdetail==1)
					echo "<tr><td colspan='6'><hr noshade size='1'></td></tr>";
				echo "<tr><td>&nbsp;</td><td><b>Average</b>&nbsp;&nbsp;</td><td>".number_format($avg_service_ok,3)."%&nbsp;&nbsp;</td><td>".number_format($avg_service_warning,3)."%&nbsp;&nbsp;</td><td>".number_format($avg_service_unknown,3)."%&nbsp;&nbsp;</td><td>".number_format($avg_service_critical,3)."%&nbsp;&nbsp;</td></tr>";
				
				echo "</tbody>";
				echo "</table>";
				}
			}

		}
		
		
	///////////////////////////////////////////////////////////////////////////
	// OVERVIEW (ALL HOSTS AND SERVICES)
	///////////////////////////////////////////////////////////////////////////
	else{
	
		// get host availability
		$args=array(
			"host" => "all",
			"starttime" => $starttime,
			"endtime" => $endtime,
			);
		get_availability_data("host",$args,$hostdata);

		// getservice availability
		$args=array(
			"host" => "all",
			"starttime" => $starttime,
			"endtime" => $endtime,
			);
		get_availability_data("service",$args,$servicedata);
		
		// check if we have data
		$have_data=false;
		if($hostdata && $servicedata && intval($hostdata->havedata)==1 && intval($servicedata->havedata)==1)
			$have_data=true;		
		if($have_data==false){
			echo "<p>Availability data is not available when monitoring engine is not running.</p>";
			}
			
		// we have data..
		else{
			
			$host_up=0;
			$host_down=0;
			$host_unreachable=0;
			
			$avg_host_up=0;
			$avg_host_down=0;
			$avg_host_unreachable=0;
			$count_host_up=0;
			$count_host_down=0;
			$count_host_unreachable=0;
			
			if($hostdata){
				foreach($hostdata->hostavailability->host as $h){
					$host_up=floatval($h->percent_known_time_up);
					$host_down=floatval($h->percent_known_time_down);
					$host_unreachable=floatval($h->percent_known_time_unreachable);

					update_avail_avg($avg_host_up,$host_up,$count_host_up);
					update_avail_avg($avg_host_down,$host_down,$count_host_down);
					update_avail_avg($avg_host_unreachable,$host_unreachable,$count_host_unreachable);
					}
				}
				
			// title
			//echo "<h2>Host Data</h2>";
				
			// host chart
			$url="availability.php?mode=getchart&type=host&title=Average+Host+Availability&data=".$avg_host_up.",".$avg_host_down.",".$avg_host_unreachable."&legend=Up,Down,Unreachable&colors=".get_avail_color("up").",".get_avail_color("down").",".get_avail_color("unreachable");
			echo "<img src='{$url}' {$size}>";
			

			
			$service_ok=0;
			$service_warning=0;
			$service_unknown=0;
			$service_critical=0;
			
			$avg_service_ok=0;
			$avg_service_warning=0;
			$avg_service_unknown=0;
			$avg_service_critical=0;
			$count_service_critical=0;
			$count_service_warning=0;
			$count_service_unknown=0;
			$count_service_critical=0;
			
			if($servicedata){
				foreach($servicedata->serviceavailability->service as $s){
					$service_ok=floatval($s->percent_known_time_ok);
					$service_warning=floatval($s->percent_known_time_warning);
					$service_unknown=floatval($s->percent_known_time_unknown);
					$service_critical=floatval($s->percent_known_time_critical);
					
					update_avail_avg($avg_service_ok,$service_ok,$count_service_ok);
					update_avail_avg($avg_service_warning,$service_warning,$count_service_warning);
					update_avail_avg($avg_service_unknown,$service_unknown,$count_service_unknown);
					update_avail_avg($avg_service_critical,$service_critical,$count_service_critical);
					}
				}
				
			// title
			//echo "<h2>Service Data</h2>";
				
			// service chart
			$url="availability.php?mode=getchart&type=service&title=Average+Service+Availability&data=".$avg_service_ok.",".$avg_service_warning.",".$avg_service_unknown.",".$avg_service_critical."&legend=Ok,Warning,Unknown,Critical&colors=".get_avail_color("ok").",".get_avail_color("warning").",".get_avail_color("unknown").",".get_avail_color("critical");
			echo "<img src='{$url}' {$size}>";
			

			// host table
			if($hostdata){
				echo "<br>";
				echo "<b>Host Data</b>";
				echo "<table class='infotable'>";
				echo "<thead><tr><th>Host&nbsp;</th><th>Up&nbsp;</th><th>Down&nbsp;</th><th>Unreachable&nbsp;</th></tr></thead>";
				echo "<tbody>";
				if($showdetail==1){
					$lasthost="";
					foreach($hostdata->hostavailability->host as $h){
					
						$hn=strval($h->host_name);
						$up=floatval($h->percent_known_time_up);
						$dn=floatval($h->percent_known_time_down);
						$un=floatval($h->percent_known_time_unreachable);
						
						if($lasthost!="" && $lasthost!=$hn)
							echo "<tr><td colspan='4'><hr noshade size='1'></td></tr>";
					
						echo "<tr>";
						echo "<td>".$hn."&nbsp;&nbsp;</td>";
						echo "<td>".$up."%&nbsp;&nbsp;</td>";
						echo "<td>".$dn."%&nbsp;&nbsp;</td>";
						echo "<td>".$un."%&nbsp;&nbsp;</td>";
						echo "</tr>";		
						
						$lasthost=$hn;
						}
					}
				// averages
				if($showdetail==1)
					echo "<tr><td colspan='4'><hr noshade size='1'></td></tr>";
				echo "<tr><td><b>Average</b>&nbsp;&nbsp;</td><td>".number_format($avg_host_up,3)."%&nbsp;&nbsp;</td><td>".number_format($avg_host_down,3)."%&nbsp;&nbsp;</td><td>".number_format($avg_host_unreachable,3)."%&nbsp;&nbsp;</td></tr>";

				echo "</tbody>";
				echo "</table>";
				}

			// service table
			if($servicedata){
				echo "<br>";
				echo "<b>Service Data</b>";
				echo "<table class='infotable'>";
				echo "<thead><tr><th>Host&nbsp;</th><th>Service&nbsp;</th><th>Ok&nbsp;</th><th>Warning&nbsp;</th><th>Unknown&nbsp;</th><th>Critical&nbsp;</th></tr></thead>";
				echo "<tbody>";
				if($showdetail==1){
					$lasthost="";
					foreach($servicedata->serviceavailability->service as $s){
					
						$hn=strval($s->host_name);
						$sd=strval($s->service_description);
						$ok=floatval($s->percent_known_time_ok);
						$wa=floatval($s->percent_known_time_warning);
						$un=floatval($s->percent_known_time_unknown);
						$service_critical=floatval($s->percent_known_time_critical);
						
						// newline
						if($lasthost!=$hn && $lasthost!=""){
							echo "<tr><td colspan='6'><hr noshade size='1'></td></tr>";
							}
						
						echo "<tr>";
						if($lasthost!=$hn)
							echo "<td>".$hn."&nbsp;&nbsp;</td>";
						else
							echo "<td>&nbsp;&nbsp;</td>";
						echo "<td>".$sd."&nbsp;&nbsp;</td>";
						echo "<td>".$ok."%&nbsp;&nbsp;</td>";
						echo "<td>".$wa."%&nbsp;&nbsp;</td>";
						echo "<td>".$un."%&nbsp;&nbsp;</td>";
						echo "<td>".$service_critical."%&nbsp;&nbsp;</td>";
						echo "</tr>";	

						$lasthost=$hn;
						}
					}
				// averages
				if($showdetail==1)
					echo "<tr><td colspan='6'><hr noshade size='1'></td></tr>";
				echo "<tr><td>&nbsp;</td><td><b>Average</b>&nbsp;&nbsp;</td><td>".number_format($avg_service_ok,3)."%&nbsp;&nbsp;</td><td>".number_format($avg_service_warning,3)."%&nbsp;&nbsp;</td><td>".number_format($avg_service_unknown,3)."%&nbsp;&nbsp;</td><td>".number_format($avg_service_critical,3)."%&nbsp;&nbsp;</td></tr>";
				
				echo "</tbody>";
				echo "</table>";
				}
			}

	
		}
	
?>
	</div>
<?php		

	
	// closes the HTML page
	do_page_end(true);
	}
	
function update_avail_avg(&$apct,$npct,&$cnt){

	$newpct=(($apct*$cnt)+$npct)/($cnt+1);

	$cnt++;
	
	$apct=$newpct;
	}

function get_avail_color($state){
	$c="";
	switch($state){
		case "up":
		case "ok":
			$c="56DA56";
			break;
		case "down":
			$c="E9513D";
			break;
		case "unreachable":
			$c="CB2525";
			break;
		case "warning":
			$c="F6EB3A";
			break;
		case "critical":
			$c="F35F3D";
			break;
		case "unknown":
			$c="F3AC3D";
			break;
		default:
			$c="000000";
			break;
		}
	return "%23".$c;
	}

function get_availability_csv(){
	global $lstr;
	global $request;

	// get values passed in GET/POST request
	$reportperiod=grab_request_var("reportperiod","last24hours");
	$startdate=grab_request_var("startdate","");
	$enddate=grab_request_var("enddate","");

	$host=grab_request_var("host","");
	$service=grab_request_var("service","");
	$hostgroup=grab_request_var("hostgroup","");
	$servicegroup=grab_request_var("servicegroup","");
	
	$showdetail=grab_request_var("showdetail",1);
	
	$csvtype=grab_request_var("csvtype","service");

	// determine start/end times based on period
	get_times_from_report_timeperiod($reportperiod,$starttime,$endtime,$startdate,$enddate);
	
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=\"availability.csv\"");
	write_csv_header($csvtype);
	
	///////////////////////////////////////////////////////////////////////////
	// SPECIFIC SERVICE
	///////////////////////////////////////////////////////////////////////////
	if($service!=""){

		// get service availability
		$args=array(
			"host" => $host,
			"service" => $service,
			"starttime" => $starttime,
			"endtime" => $endtime,
			);
		get_availability_data("service",$args,$servicedata);
		
		// check if we have data
		$have_data=false;
		if($servicedata && intval($servicedata->havedata)==1)
			$have_data=true;		
		if($have_data==false){
			echo "Availability data is not available when monitoring engine is not running.\n";
			}
			
		// we have data..
		else{
		
			$service_ok=0;
			$service_warning=0;
			$service_unknown=0;
			$service_critical=0;
			
			if($servicedata){
				foreach($servicedata->serviceavailability->service as $s){
					$service_ok=floatval($s->percent_known_time_ok);
					$service_warning=floatval($s->percent_known_time_warning);
					$service_unknown=floatval($s->percent_known_time_unknown);
					$service_critical=floatval($s->percent_known_time_critical);
					}
				}
				
			// service table
			if($servicedata){
				foreach($servicedata->serviceavailability->service as $s){
				
					$hn=strval($s->host_name);
					$sd=strval($s->service_description);
					$ok=floatval($s->percent_known_time_ok);
					$wa=floatval($s->percent_known_time_warning);
					$un=floatval($s->percent_known_time_unknown);
					$service_critical=floatval($s->percent_known_time_critical);
					
					write_service_csv_data($hn,$sd,$ok,$wa,$un,$service_critical);
					}
				}

			}

		}
		
	///////////////////////////////////////////////////////////////////////////
	// SPECIFIC HOST
	///////////////////////////////////////////////////////////////////////////
	else if($host!=""){
	
		// get host availability
		if($csvtype=="host"){
			$args=array(
				"host" => $host,
				"starttime" => $starttime,
				"endtime" => $endtime,
				);
			get_availability_data("host",$args,$hostdata);
			}

		// getservice availability
		else{
			$args=array(
				"host" => $host,
				"starttime" => $starttime,
				"endtime" => $endtime,
				);
			get_availability_data("service",$args,$servicedata);
			}
		
		// check if we have data
		$have_data=false;
		if(($csvtype=="host" && $hostdata && intval($hostdata->havedata)==1) || ( $servicedata && intval($servicedata->havedata)==1))
			$have_data=true;		
		if($have_data==false){
			echo "Availability data is not available when monitoring engine is not running.\n";
			}
			
		// we have data..
		else{

			
			$host_up=0;
			$host_down=0;
			$host_unreachable=0;
			
			if($hostdata){
				foreach($hostdata->hostavailability->host as $h){
					$host_up=floatval($h->percent_known_time_up);
					$host_down=floatval($h->percent_known_time_down);
					$host_unreachable=floatval($h->percent_known_time_unreachable);
					}
				}
				
			
			$service_ok=0;
			$service_warning=0;
			$service_unknown=0;
			$service_critical=0;
			
			$avg_service_ok=0;
			$avg_service_warning=0;
			$avg_service_unknown=0;
			$avg_service_critical=0;
			$count_service_critical=0;
			$count_service_warning=0;
			$count_service_unknown=0;
			$count_service_critical=0;
			
			if($servicedata){
				foreach($servicedata->serviceavailability->service as $s){
					$service_ok=floatval($s->percent_known_time_ok);
					$service_warning=floatval($s->percent_known_time_warning);
					$service_unknown=floatval($s->percent_known_time_unknown);
					$service_critical=floatval($s->percent_known_time_critical);
					
					update_avail_avg($avg_service_ok,$service_ok,$count_service_ok);
					update_avail_avg($avg_service_warning,$service_warning,$count_service_warning);
					update_avail_avg($avg_service_unknown,$service_unknown,$count_service_unknown);
					update_avail_avg($avg_service_critical,$service_critical,$count_service_critical);
					}
				}
				

			// host table
			if($hostdata){
				foreach($hostdata->hostavailability->host as $h){
					$hn=strval($h->host_name);
					$up=floatval($h->percent_known_time_up);
					$dn=floatval($h->percent_known_time_down);
					$un=floatval($h->percent_known_time_unreachable);

					write_host_csv_data($hn,$up,$dn,$un);
					}
				}

			// service table
			if($servicedata){
				foreach($servicedata->serviceavailability->service as $s){
				
					$hn=strval($s->host_name);
					$sd=strval($s->service_description);
					$ok=floatval($s->percent_known_time_ok);
					$wa=floatval($s->percent_known_time_warning);
					$un=floatval($s->percent_known_time_unknown);
					$service_critical=floatval($s->percent_known_time_critical);
					
					write_service_csv_data($hn,$sd,$ok,$wa,$un,$service_critical);
					}
				// averages
				write_service_csv_data("","AVERAGE",$avg_service_ok,$avg_service_warning,$avg_service_unknown,$avg_service_critical);
				}
			}
				
		}
		
	///////////////////////////////////////////////////////////////////////////
	// SPECIFIC HOSTGROUP OR SERVICEGROUP
	///////////////////////////////////////////////////////////////////////////
	else if($hostgroup!="" || $servicegroup!=""){
	
		// get host availability
		if($csvtype=="host"){
			$args=array(
				"host" => "",
				"starttime" => $starttime,
				"endtime" => $endtime,
				);
			if($hostgroup!="")
				$args["hostgroup"]=$hostgroup;
			else
				$args["servicegroup"]=$servicegroup;
			get_availability_data("host",$args,$hostdata);
			}

		// getservice availability
		else{
			$args=array(
				"host" => "",
				"starttime" => $starttime,
				"endtime" => $endtime,
				);
			if($hostgroup!="")
				$args["hostgroup"]=$hostgroup;
			else
				$args["servicegroup"]=$servicegroup;
			get_availability_data("service",$args,$servicedata);
			}
		
		// check if we have data
		$have_data=false;
		if(($csvtype=="host" && $hostdata && intval($hostdata->havedata)==1) || ( $servicedata && intval($servicedata->havedata)==1))
			$have_data=true;		
		if($have_data==false){
			echo "Availability data is not available when monitoring engine is not running.\n";
			}
			
		// we have data..
		else{
			
			$host_up=0;
			$host_down=0;
			$host_unreachable=0;
			
			$avg_host_up=0;
			$avg_host_down=0;
			$avg_host_unreachable=0;
			$count_host_up=0;
			$count_host_down=0;
			$count_host_unreachable=0;
			
			if($hostdata){
				foreach($hostdata->hostavailability->host as $h){
					$host_up=floatval($h->percent_known_time_up);
					$host_down=floatval($h->percent_known_time_down);
					$host_unreachable=floatval($h->percent_known_time_unreachable);

					update_avail_avg($avg_host_up,$host_up,$count_host_up);
					update_avail_avg($avg_host_down,$host_down,$count_host_down);
					update_avail_avg($avg_host_unreachable,$host_unreachable,$count_host_unreachable);
					}
				}
				
			$service_ok=0;
			$service_warning=0;
			$service_unknown=0;
			$service_critical=0;
			
			$avg_service_ok=0;
			$avg_service_warning=0;
			$avg_service_unknown=0;
			$avg_service_critical=0;
			$count_service_critical=0;
			$count_service_warning=0;
			$count_service_unknown=0;
			$count_service_critical=0;
			
			if($servicedata){
				foreach($servicedata->serviceavailability->service as $s){
					$service_ok=floatval($s->percent_known_time_ok);
					$service_warning=floatval($s->percent_known_time_warning);
					$service_unknown=floatval($s->percent_known_time_unknown);
					$service_critical=floatval($s->percent_known_time_critical);
					
					update_avail_avg($avg_service_ok,$service_ok,$count_service_ok);
					update_avail_avg($avg_service_warning,$service_warning,$count_service_warning);
					update_avail_avg($avg_service_unknown,$service_unknown,$count_service_unknown);
					update_avail_avg($avg_service_critical,$service_critical,$count_service_critical);
					}
				}
				
			// host table
			if($hostdata){
				foreach($hostdata->hostavailability->host as $h){
				
					$hn=strval($h->host_name);
					$up=floatval($h->percent_known_time_up);
					$dn=floatval($h->percent_known_time_down);
					$un=floatval($h->percent_known_time_unreachable);

					write_host_csv_data($hn,$up,$dn,$un);
					}
				// averages
				write_host_csv_data("AVERAGE",$avg_host_up,$avg_host_down,$avg_host_unreachable);
				}

			// service table
			if($servicedata){
				foreach($servicedata->serviceavailability->service as $s){
				
					$hn=strval($s->host_name);
					$sd=strval($s->service_description);
					$ok=floatval($s->percent_known_time_ok);
					$wa=floatval($s->percent_known_time_warning);
					$un=floatval($s->percent_known_time_unknown);
					$service_critical=floatval($s->percent_known_time_critical);
					
					write_service_csv_data($hn,$sd,$ok,$wa,$un,$service_critical);
					}
				// averages
				write_service_csv_data("","AVERAGE",$avg_service_ok,$avg_service_warning,$avg_service_unknown,$avg_service_critical);
				}
			}

		}
		
		
	///////////////////////////////////////////////////////////////////////////
	// OVERVIEW (ALL HOSTS AND SERVICES)
	///////////////////////////////////////////////////////////////////////////
	else{
	
		// get host availability
		if($csvtype=="host"){
			$args=array(
				"host" => "all",
				"starttime" => $starttime,
				"endtime" => $endtime,
				);
			get_availability_data("host",$args,$hostdata);
			}

		// getservice availability
		else{
			$args=array(
				"host" => "all",
				"starttime" => $starttime,
				"endtime" => $endtime,
				);
			get_availability_data("service",$args,$servicedata);
			}
		
		// check if we have data
		$have_data=false;
		if(($csvtype=="host" && $hostdata && intval($hostdata->havedata)==1) || ($servicedata && intval($servicedata->havedata)==1))
			$have_data=true;		
		if($have_data==false){
			echo "Availability data is not available when monitoring engine is not running.\n";
			}
			
		// we have data..
		else{
			
			$host_up=0;
			$host_down=0;
			$host_unreachable=0;
			
			$avg_host_up=0;
			$avg_host_down=0;
			$avg_host_unreachable=0;
			$count_host_up=0;
			$count_host_down=0;
			$count_host_unreachable=0;
			
			if($hostdata){
				foreach($hostdata->hostavailability->host as $h){
					$host_up=floatval($h->percent_known_time_up);
					$host_down=floatval($h->percent_known_time_down);
					$host_unreachable=floatval($h->percent_known_time_unreachable);

					update_avail_avg($avg_host_up,$host_up,$count_host_up);
					update_avail_avg($avg_host_down,$host_down,$count_host_down);
					update_avail_avg($avg_host_unreachable,$host_unreachable,$count_host_unreachable);
					}
				}
				
			
			$service_ok=0;
			$service_warning=0;
			$service_unknown=0;
			$service_critical=0;
			
			$avg_service_ok=0;
			$avg_service_warning=0;
			$avg_service_unknown=0;
			$avg_service_critical=0;
			$count_service_critical=0;
			$count_service_warning=0;
			$count_service_unknown=0;
			$count_service_critical=0;
			
			if($servicedata){
				foreach($servicedata->serviceavailability->service as $s){
					$service_ok=floatval($s->percent_known_time_ok);
					$service_warning=floatval($s->percent_known_time_warning);
					$service_unknown=floatval($s->percent_known_time_unknown);
					$service_critical=floatval($s->percent_known_time_critical);
					
					update_avail_avg($avg_service_ok,$service_ok,$count_service_ok);
					update_avail_avg($avg_service_warning,$service_warning,$count_service_warning);
					update_avail_avg($avg_service_unknown,$service_unknown,$count_service_unknown);
					update_avail_avg($avg_service_critical,$service_critical,$count_service_critical);
					}
				}
			
			// host table
			if($hostdata){

				foreach($hostdata->hostavailability->host as $h){
				
					$hn=strval($h->host_name);
					$up=floatval($h->percent_known_time_up);
					$dn=floatval($h->percent_known_time_down);
					$un=floatval($h->percent_known_time_unreachable);
					
					write_host_csv_data($hn,$up,$dn,$un);
					}
				// averages
				write_host_csv_data("AVERAGE",$avg_host_up,$avg_host_down,$avg_host_unreachable);
				}

			// service table
			if($servicedata){
				foreach($servicedata->serviceavailability->service as $s){
				
					$hn=strval($s->host_name);
					$sd=strval($s->service_description);
					$ok=floatval($s->percent_known_time_ok);
					$wa=floatval($s->percent_known_time_warning);
					$un=floatval($s->percent_known_time_unknown);
					$service_critical=floatval($s->percent_known_time_critical);

					write_service_csv_data($hn,$sd,$ok,$wa,$un,$service_critical);
					}
				// averages
				write_service_csv_data("","AVERAGE",$avg_service_ok,$avg_service_warning,$avg_service_unknown,$avg_service_critical);
				}
			}

	
		}
	

	}
	
	
function write_csv_header($csvtype){
	if($csvtype=="service")
		echo "host,service,ok %,warning %,unknown %,critical %\n";
	else
		echo "host,up %,down %,unreachable %\n";
	}
	
function write_host_csv_data($hn,$up,$dn,$un){
	echo "\"$hn\",$up,$dn,$un\n";
	}

function write_service_csv_data($hn,$sn,$ok,$wa,$un,$cr){
	echo "\"$hn\",\"$sn\",$ok,$wa,$un,$cr\n";
	}
	
	
function get_availability_pdf(){
	global $lstr;
	global $request;
	
	require_once(dirname(__FILE__).'/../includes/fpdf/fpdf_alpha.php');
	require_once(dirname(__FILE__).'/../includes/fpdf/nagiospdf.php');

	// get values passed in GET/POST request
	$reportperiod=grab_request_var("reportperiod","last24hours");
	$startdate=grab_request_var("startdate","");
	$enddate=grab_request_var("enddate","");

	$host=grab_request_var("host","");
	$service=grab_request_var("service","");
	$hostgroup=grab_request_var("hostgroup","");
	$servicegroup=grab_request_var("servicegroup","");
	
	// should we show detail by default?
	$showdetail=1;
	if($host=="" && $service=="" && $hostgroup=="" && $servicegroup=="")
		$showdetail=0;
	
	$showdetail=grab_request_var("showdetail",$showdetail);

	// determine start/end times based on period
	get_times_from_report_timeperiod($reportperiod,$starttime,$endtime,$startdate,$enddate);
	
	// determine title
	if($service!="")
		$title="Service Availability";
	else if($host!="")
		$title="Host Availability";
	else if($hostgroup!="")
		$title="Hostgroup Availability";
	else if($servicegroup!="")
		$title="Servicegroup Availability";
	else
		$title="Availability Summary";
	
	$date_text="".get_datetime_string($starttime,DT_SHORT_DATE_TIME,DF_AUTO,"null")." To ".get_datetime_string($endtime,DT_SHORT_DATE_TIME,DF_AUTO,"null")."";
	

	$pdf=new NagiosReportPDF();
	$pdf->page_title=$title;
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
	//$pdf->page_subtitle.=$date_text."\n\n"."Showing ".$records." Events";
	$pdf->page_subtitle.=$date_text;
	//if($search!="")
	//	$pdf->page_subtitle.=" Matching '".$search."'";
	
	$pdf->AliasNbPages();
	$pdf->AddPage();
	$pdf->SetFont("times", "", 8);
	
	$html="";
	$fs=' size="8" style=""';

	
	///////////////////////////////////////////////////////////////////////////
	// SPECIFIC SERVICE
	///////////////////////////////////////////////////////////////////////////
	if($service!=""){

		// get service availability
		$args=array(
			"host" => $host,
			"service" => $service,
			"starttime" => $starttime,
			"endtime" => $endtime,
			);
		get_availability_data("service",$args,$servicedata);
		
		// check if we have data
		$have_data=false;
		if($servicedata && intval($servicedata->havedata)==1)
			$have_data=true;		
		if($have_data==false){
			$pdf->MultiCell(0,5,"Availability data is not available when monitoring engine is not running");
			}
			
		// we have data..
		else{
		
			$service_ok=0;
			$service_warning=0;
			$service_unknown=0;
			$service_critical=0;
			
			if($servicedata){
				foreach($servicedata->serviceavailability->service as $s){
					$service_ok=floatval($s->percent_known_time_ok);
					$service_warning=floatval($s->percent_known_time_warning);
					$service_unknown=floatval($s->percent_known_time_unknown);
					$service_critical=floatval($s->percent_known_time_critical);
					}
				}
				
			// service chart
			$url=get_base_url()."reports/availability.php?".time();
			$url.="&mode=getchart&type=service&title=Service+Availability&data=".$service_ok.",".$service_warning.",".$service_unknown.",".$service_critical."&legend=Ok,Warning,Unknown,Critical&colors=".get_avail_color("ok").",".get_avail_color("warning").",".get_avail_color("unknown").",".get_avail_color("critical");
			// add authentication stuff
			$url.="&username=".$_SESSION["username"]."&ticket=".get_user_attr(0,"backend_ticket");

			// get raw image
			$imgdata=file_get_contents($url);
			// create tmp file
			$temp=tempnam("/tmp","availability");
			// save image
			file_put_contents($temp,$imgdata);
			// rename file
			$imgfile=$temp.".png";
			rename($temp,$imgfile);
			
			// we have to scale the image...
			$width=350;
			$height=250;
			//$imgscale=190/$width;
			$imgscale=.25;
			$pdf->Image($imgfile,$pdf->GetX(),$pdf->GetY()+5,$width*$imgscale,$height*$imgscale);
			
			// delete temp image file
			unlink($imgfile);
			
			$pdf->SetY($pdf->GetY()+70);

	
			// service table
			if($servicedata){

				$pdf->SetFont('Arial','B',12);
				$pdf->MultiCell(0,5,"Service Data");

				$html.=get_service_pdf_table_header();
				
				$lasthost="";
				foreach($servicedata->serviceavailability->service as $s){
				
					$hn=strval($s->host_name);
					$sd=strval($s->service_description);
					$ok=floatval($s->percent_known_time_ok);
					$wa=floatval($s->percent_known_time_warning);
					$un=floatval($s->percent_known_time_unknown);
					$service_critical=floatval($s->percent_known_time_critical);
					
					// newline
					if($lasthost!=$hn && $lasthost!=""){
						$html.="<tr><td colspan='6'><hr noshade size='1'></td></tr>";
						}
					
					$html.="<tr>";
					if($lasthost!=$hn)
						$html.="<td ".$fs.">".$hn."&nbsp;&nbsp;</td>";
					else
						$html.="<td ".$fs.">&nbsp;&nbsp;</td>";
					$html.="<td ".$fs.">".$sd."&nbsp;&nbsp;</td>";
					$html.="<td ".$fs.">".$ok."%&nbsp;&nbsp;</td>";
					$html.="<td ".$fs.">".$wa."%&nbsp;&nbsp;</td>";
					$html.="<td ".$fs.">".$un."%&nbsp;&nbsp;</td>";
					$html.="<td ".$fs.">".$service_critical."%&nbsp;&nbsp;</td>";
					$html.="</tr>";	

			
					$lasthost=$hn;
					}
				
				$html.="</table>";
				$pdf->htmltable($html);	
				}

			}

		}
		
	///////////////////////////////////////////////////////////////////////////
	// SPECIFIC HOST
	///////////////////////////////////////////////////////////////////////////
	else if($host!=""){
	
		// get host availability
		$args=array(
			"host" => $host,
			"starttime" => $starttime,
			"endtime" => $endtime,
			);
		get_availability_data("host",$args,$hostdata);

		// getservice availability
		$args=array(
			"host" => $host,
			"starttime" => $starttime,
			"endtime" => $endtime,
			);
		get_availability_data("service",$args,$servicedata);
		
		// check if we have data
		$have_data=false;
		if($hostdata && $servicedata && intval($hostdata->havedata)==1 && intval($servicedata->havedata)==1)
			$have_data=true;		
		if($have_data==false){
			$pdf->MultiCell(0,5,"Availability data is not available when monitoring engine is not running");
			}
			
		// we have data..
		else{

			
			$host_up=0;
			$host_down=0;
			$host_unreachable=0;
			
			if($hostdata){
				foreach($hostdata->hostavailability->host as $h){
					$host_up=floatval($h->percent_known_time_up);
					$host_down=floatval($h->percent_known_time_down);
					$host_unreachable=floatval($h->percent_known_time_unreachable);
					}
				}
				
			// title
			//echo "<h2>Host Data</h2>";
				
			// host chart
			$url=get_base_url()."reports/availability.php?".time();
			$url.="&mode=getchart&type=host&title=Host+Availability&data=".$host_up.",".$host_down.",".$host_unreachable."&legend=Up,Down,Unreachable&colors=".get_avail_color("up").",".get_avail_color("down").",".get_avail_color("unreachable");
			// add authentication stuff
			$url.="&username=".$_SESSION["username"]."&ticket=".get_user_attr(0,"backend_ticket");

			// get raw image
			$imgdata=file_get_contents($url);
			// create tmp file
			$temp=tempnam("/tmp","availability");
			// save image
			file_put_contents($temp,$imgdata);
			// rename file
			$imgfile=$temp.".png";
			rename($temp,$imgfile);
			
			// we have to scale the image...
			$width=350;
			$height=250;
			//$imgscale=190/$width;
			$imgscale=.25;
			$pdf->Image($imgfile,$pdf->GetX(),$pdf->GetY()+5,$width*$imgscale,$height*$imgscale);
			
			// delete temp image file
			unlink($imgfile);
			
			$pdf->SetY($pdf->GetY()+60);


			
			$service_ok=0;
			$service_warning=0;
			$service_unknown=0;
			$service_critical=0;
			
			$avg_service_ok=0;
			$avg_service_warning=0;
			$avg_service_unknown=0;
			$avg_service_critical=0;
			$count_service_critical=0;
			$count_service_warning=0;
			$count_service_unknown=0;
			$count_service_critical=0;
			
			if($servicedata){
				foreach($servicedata->serviceavailability->service as $s){
					$service_ok=floatval($s->percent_known_time_ok);
					$service_warning=floatval($s->percent_known_time_warning);
					$service_unknown=floatval($s->percent_known_time_unknown);
					$service_critical=floatval($s->percent_known_time_critical);
					
					update_avail_avg($avg_service_ok,$service_ok,$count_service_ok);
					update_avail_avg($avg_service_warning,$service_warning,$count_service_warning);
					update_avail_avg($avg_service_unknown,$service_unknown,$count_service_unknown);
					update_avail_avg($avg_service_critical,$service_critical,$count_service_critical);
					}
				}
				
			// service chart
			$url=get_base_url()."reports/availability.php?".time();
			$url.="&mode=getchart&type=service&title=Service+Availability&data=".$avg_service_ok.",".$avg_service_warning.",".$avg_service_unknown.",".$avg_service_critical."&legend=Ok,Warning,Unknown,Critical&colors=".get_avail_color("ok").",".get_avail_color("warning").",".get_avail_color("unknown").",".get_avail_color("critical");
			// add authentication stuff
			$url.="&username=".$_SESSION["username"]."&ticket=".get_user_attr(0,"backend_ticket");

			// get raw image
			$imgdata=file_get_contents($url);
			// create tmp file
			$temp=tempnam("/tmp","availability");
			// save image
			file_put_contents($temp,$imgdata);
			// rename file
			$imgfile=$temp.".png";
			rename($temp,$imgfile);
			
			// we have to scale the image...
			$width=350;
			$height=250;
			//$imgscale=190/$width;
			$imgscale=.25;
			$pdf->Image($imgfile,$pdf->GetX(),$pdf->GetY()+5,$width*$imgscale,$height*$imgscale);
			
			// delete temp image file
			unlink($imgfile);
			
			$pdf->SetY($pdf->GetY()+70);
		

			// host table
			if($hostdata){
			
				$html="";

				$pdf->SetFont('Arial','B',12);
				$pdf->MultiCell(0,5,"Host Data");

				$html.=get_host_pdf_table_header();
				
				foreach($hostdata->hostavailability->host as $h){
					/*
					echo "HOST: ".$h->host_name."<BR>";
					echo "UP: ".$h->percent_known_time_up."<BR>";
					echo "DOWN: ".$h->percent_known_time_down."<BR>";
					echo "UNREACHABLE: ".$h->percent_known_time_unreachable."<BR>";
					*/
					$hn=strval($h->host_name);
					$up=floatval($h->percent_known_time_up);
					$dn=floatval($h->percent_known_time_down);
					$un=floatval($h->percent_known_time_unreachable);
					
					$html.="<tr>";
					$html.="<td ".$fs.">".$hn."&nbsp;&nbsp;</td>";
					$html.="<td ".$fs.">".$up."%&nbsp;&nbsp;</td>";
					$html.="<td ".$fs.">".$dn."%&nbsp;&nbsp;</td>";
					$html.="<td ".$fs.">".$un."%&nbsp;&nbsp;</td>";
					$html.="</tr>";		
					}
				$html.="</table>";
				$pdf->htmltable($html);	
				
				$pdf->SetY($pdf->GetY()+10);
				}

			// service table
			if($servicedata){
			
				$html="";

				$pdf->SetFont('Arial','B',12);
				$pdf->MultiCell(0,5,"Service Data");

				$html.=get_service_pdf_table_header();
				
				$lasthost="";
				foreach($servicedata->serviceavailability->service as $s){
				
					$hn=strval($s->host_name);
					$sd=strval($s->service_description);
					$ok=floatval($s->percent_known_time_ok);
					$wa=floatval($s->percent_known_time_warning);
					$un=floatval($s->percent_known_time_unknown);
					$service_critical=floatval($s->percent_known_time_critical);
					
					// newline
					if($lasthost!=$hn && $lasthost!=""){
						$html.="<tr><td colspan='6' ".$fs."><hr></td></tr>";
						}
					
					$html.="<tr>";
					if($lasthost!=$hn)
						$html.="<td ".$fs.">".$hn."&nbsp;&nbsp;</td>";
					else
						$html.="<td ".$fs.">&nbsp;&nbsp;</td>";
					$html.="<td ".$fs.">".$sd."&nbsp;&nbsp;</td>";
					$html.="<td ".$fs.">".$ok."%&nbsp;&nbsp;</td>";
					$html.="<td ".$fs.">".$wa."%&nbsp;&nbsp;</td>";
					$html.="<td ".$fs.">".$un."%&nbsp;&nbsp;</td>";
					$html.="<td ".$fs.">".$service_critical."%&nbsp;&nbsp;</td>";
					$html.="</tr>";	

					$lasthost=$hn;
					}
				// averages
				$html.="<tr><td colspan='6' ".$fs."><hr></td></tr>";
				$html.="<tr><td ".$fs.">&nbsp;</td><td ".$fs."><b>Average</b>&nbsp;&nbsp;</td><td ".$fs.">".number_format($avg_service_ok,3)."%&nbsp;&nbsp;</td><td ".$fs.">".number_format($avg_service_warning,3)."%&nbsp;&nbsp;</td><td ".$fs.">".number_format($avg_service_unknown,3)."%&nbsp;&nbsp;</td><td ".$fs.">".number_format($avg_service_critical,3)."%&nbsp;&nbsp;</td></tr>";
				
				$html.="</table>";
				$pdf->htmltable($html);	
				}
			}
				
		}
		
	///////////////////////////////////////////////////////////////////////////
	// SPECIFIC HOSTGROUP OR SERVICEGROUP
	///////////////////////////////////////////////////////////////////////////
	else if($hostgroup!="" || $servicegroup!=""){
	
		// get host availability
		$args=array(
			"host" => "",
			"starttime" => $starttime,
			"endtime" => $endtime,
			);
		if($hostgroup!="")
			$args["hostgroup"]=$hostgroup;
		else
			$args["servicegroup"]=$servicegroup;
		get_availability_data("host",$args,$hostdata);

		// getservice availability
		$args=array(
			"host" => "",
			"starttime" => $starttime,
			"endtime" => $endtime,
			);
		if($hostgroup!="")
			$args["hostgroup"]=$hostgroup;
		else
			$args["servicegroup"]=$servicegroup;
		get_availability_data("service",$args,$servicedata);
		
		// check if we have data
		$have_data=false;
		if($hostdata && $servicedata && intval($hostdata->havedata)==1 && intval($servicedata->havedata)==1)
			$have_data=true;		
		if($have_data==false){
			$pdf->MultiCell(0,5,"Availability data is not available when monitoring engine is not running");
			}
			
		// we have data..
		else{
			
			$host_up=0;
			$host_down=0;
			$host_unreachable=0;
			
			$avg_host_up=0;
			$avg_host_down=0;
			$avg_host_unreachable=0;
			$count_host_up=0;
			$count_host_down=0;
			$count_host_unreachable=0;
			
			if($hostdata){
				foreach($hostdata->hostavailability->host as $h){
					$host_up=floatval($h->percent_known_time_up);
					$host_down=floatval($h->percent_known_time_down);
					$host_unreachable=floatval($h->percent_known_time_unreachable);

					update_avail_avg($avg_host_up,$host_up,$count_host_up);
					update_avail_avg($avg_host_down,$host_down,$count_host_down);
					update_avail_avg($avg_host_unreachable,$host_unreachable,$count_host_unreachable);
					}
				}
				
			// host chart
			$url=get_base_url()."reports/availability.php?".time();
			$url.="&mode=getchart&type=host&title=Host+Availability&data=".$avg_host_up.",".$avg_host_down.",".$avg_host_unreachable."&legend=Up,Down,Unreachable&colors=".get_avail_color("up").",".get_avail_color("down").",".get_avail_color("unreachable");
			// add authentication stuff
			$url.="&username=".$_SESSION["username"]."&ticket=".get_user_attr(0,"backend_ticket");

			// get raw image
			$imgdata=file_get_contents($url);
			// create tmp file
			$temp=tempnam("/tmp","availability");
			// save image
			file_put_contents($temp,$imgdata);
			// rename file
			$imgfile=$temp.".png";
			rename($temp,$imgfile);
			
			// we have to scale the image...
			$width=350;
			$height=250;
			//$imgscale=190/$width;
			$imgscale=.25;
			$pdf->Image($imgfile,$pdf->GetX(),$pdf->GetY()+5,$width*$imgscale,$height*$imgscale);
			
			// delete temp image file
			unlink($imgfile);
			
			$pdf->SetY($pdf->GetY()+60);

			
			$service_ok=0;
			$service_warning=0;
			$service_unknown=0;
			$service_critical=0;
			
			$avg_service_ok=0;
			$avg_service_warning=0;
			$avg_service_unknown=0;
			$avg_service_critical=0;
			$count_service_critical=0;
			$count_service_warning=0;
			$count_service_unknown=0;
			$count_service_critical=0;
			
			if($servicedata){
				foreach($servicedata->serviceavailability->service as $s){
					$service_ok=floatval($s->percent_known_time_ok);
					$service_warning=floatval($s->percent_known_time_warning);
					$service_unknown=floatval($s->percent_known_time_unknown);
					$service_critical=floatval($s->percent_known_time_critical);
					
					update_avail_avg($avg_service_ok,$service_ok,$count_service_ok);
					update_avail_avg($avg_service_warning,$service_warning,$count_service_warning);
					update_avail_avg($avg_service_unknown,$service_unknown,$count_service_unknown);
					update_avail_avg($avg_service_critical,$service_critical,$count_service_critical);
					}
				}
				
			// service chart
			$url=get_base_url()."reports/availability.php?".time();
			$url.="&mode=getchart&type=service&title=Service+Availability&data=".$avg_service_ok.",".$avg_service_warning.",".$avg_service_unknown.",".$avg_service_critical."&legend=Ok,Warning,Unknown,Critical&colors=".get_avail_color("ok").",".get_avail_color("warning").",".get_avail_color("unknown").",".get_avail_color("critical");
			// add authentication stuff
			$url.="&username=".$_SESSION["username"]."&ticket=".get_user_attr(0,"backend_ticket");

			// get raw image
			$imgdata=file_get_contents($url);
			// create tmp file
			$temp=tempnam("/tmp","availability");
			// save image
			file_put_contents($temp,$imgdata);
			// rename file
			$imgfile=$temp.".png";
			rename($temp,$imgfile);
			
			// we have to scale the image...
			$width=350;
			$height=250;
			//$imgscale=190/$width;
			$imgscale=.25;
			$pdf->Image($imgfile,$pdf->GetX(),$pdf->GetY()+5,$width*$imgscale,$height*$imgscale);
			
			// delete temp image file
			unlink($imgfile);
			
			$pdf->SetY($pdf->GetY()+70);
			

			// host table
			if($hostdata){
			
				$html="";

				$pdf->SetFont('Arial','B',12);
				$pdf->MultiCell(0,5,"Host Data");

				$html.=get_host_pdf_table_header();
				
				if($showdetail==1){
					$lasthost="";
					foreach($hostdata->hostavailability->host as $h){
					
						$hn=strval($h->host_name);
						$up=floatval($h->percent_known_time_up);
						$dn=floatval($h->percent_known_time_down);
						$un=floatval($h->percent_known_time_unreachable);
						
						//if($lasthost!="" && $lasthost!=$hn)
						//	$html.="<tr><td colspan='4' ".$fs."></td></tr>";
					
						$html.="<tr>";
						$html.="<td ".$fs.">".$hn."&nbsp;&nbsp;</td>";
						$html.="<td ".$fs.">".$up."%&nbsp;&nbsp;</td>";
						$html.="<td ".$fs.">".$dn."%&nbsp;&nbsp;</td>";
						$html.="<td ".$fs.">".$un."%&nbsp;&nbsp;</td>";
						$html.="</tr>";		
						
						$lasthost=$hn;
						}
					}
				// averages
				if($showdetail==1)
					$html.="<tr><td colspan='4' ".$fs."></td></tr>";
				$html.="<tr><td ".$fs."><b>Average</b>&nbsp;&nbsp;</td><td ".$fs.">".number_format($avg_host_up,3)."%&nbsp;&nbsp;</td><td ".$fs.">".number_format($avg_host_down,3)."%&nbsp;&nbsp;</td><td ".$fs.">".number_format($avg_host_unreachable,3)."%&nbsp;&nbsp;</td></tr>";

				$html.="</table>";
				$pdf->htmltable($html);	
				
				$pdf->SetY($pdf->GetY()+10);
				}

			// service table
			if($servicedata){

				$html="";

				$pdf->SetFont('Arial','B',12);
				$pdf->MultiCell(0,5,"Service Data");

				$html.=get_service_pdf_table_header();
				
				if($showdetail==1){
					$lasthost="";
					foreach($servicedata->serviceavailability->service as $s){
					
						$hn=strval($s->host_name);
						$sd=strval($s->service_description);
						$ok=floatval($s->percent_known_time_ok);
						$wa=floatval($s->percent_known_time_warning);
						$un=floatval($s->percent_known_time_unknown);
						$service_critical=floatval($s->percent_known_time_critical);
						
						// newline
						if($lasthost!=$hn && $lasthost!=""){
							$html.="<tr><td colspan='6' ".$fs."></td></tr>";
							}
						
						$html.="<tr>";
						if($lasthost!=$hn)
							$html.="<td ".$fs.">".$hn."&nbsp;&nbsp;</td>";
						else
							$html.="<td ".$fs.">&nbsp;&nbsp;</td>";
						$html.="<td ".$fs.">".$sd."&nbsp;&nbsp;</td>";
						$html.="<td ".$fs.">".$ok."%&nbsp;&nbsp;</td>";
						$html.="<td ".$fs.">".$wa."%&nbsp;&nbsp;</td>";
						$html.="<td ".$fs.">".$un."%&nbsp;&nbsp;</td>";
						$html.="<td ".$fs.">".$service_critical."%&nbsp;&nbsp;</td>";
						$html.="</tr>";	

						$lasthost=$hn;
						}
					}
				// averages
				if($showdetail==1)
					$html.="<tr><td colspan='6' ".$fs."></td></tr>";
				$html.="<tr><td ".$fs.">&nbsp;</td><td ".$fs."><b>Average</b>&nbsp;&nbsp;</td><td ".$fs.">".number_format($avg_service_ok,3)."%&nbsp;&nbsp;</td><td ".$fs.">".number_format($avg_service_warning,3)."%&nbsp;&nbsp;</td><td ".$fs.">".number_format($avg_service_unknown,3)."%&nbsp;&nbsp;</td><td ".$fs.">".number_format($avg_service_critical,3)."%&nbsp;&nbsp;</td></tr>";
				
				$html.="</table>";
				$pdf->htmltable($html);	
				}
			}

		}
		
		
	///////////////////////////////////////////////////////////////////////////
	// OVERVIEW (ALL HOSTS AND SERVICES)
	///////////////////////////////////////////////////////////////////////////
	else{
	
		// get host availability
		$args=array(
			"host" => "all",
			"starttime" => $starttime,
			"endtime" => $endtime,
			);
		get_availability_data("host",$args,$hostdata);

		// getservice availability
		$args=array(
			"host" => "all",
			"starttime" => $starttime,
			"endtime" => $endtime,
			);
		get_availability_data("service",$args,$servicedata);
		
		// check if we have data
		$have_data=false;
		if($hostdata && $servicedata && intval($hostdata->havedata)==1 && intval($servicedata->havedata)==1)
			$have_data=true;		
		if($have_data==false){
			$pdf->MultiCell(0,5,"Availability data is not available when monitoring engine is not running");
			}
			
		// we have data..
		else{
			
			$host_up=0;
			$host_down=0;
			$host_unreachable=0;
			
			$avg_host_up=0;
			$avg_host_down=0;
			$avg_host_unreachable=0;
			$count_host_up=0;
			$count_host_down=0;
			$count_host_unreachable=0;
			
			if($hostdata){
				foreach($hostdata->hostavailability->host as $h){
					$host_up=floatval($h->percent_known_time_up);
					$host_down=floatval($h->percent_known_time_down);
					$host_unreachable=floatval($h->percent_known_time_unreachable);

					update_avail_avg($avg_host_up,$host_up,$count_host_up);
					update_avail_avg($avg_host_down,$host_down,$count_host_down);
					update_avail_avg($avg_host_unreachable,$host_unreachable,$count_host_unreachable);
					}
				}
				
			// host chart
			$url=get_base_url()."reports/availability.php?".time();
			$url.="&mode=getchart&type=host&title=Host+Availability&data=".$avg_host_up.",".$avg_host_down.",".$avg_host_unreachable."&legend=Up,Down,Unreachable&colors=".get_avail_color("up").",".get_avail_color("down").",".get_avail_color("unreachable");
			// add authentication stuff
			$url.="&username=".$_SESSION["username"]."&ticket=".get_user_attr(0,"backend_ticket");

			// get raw image
			$imgdata=file_get_contents($url);
			// create tmp file
			$temp=tempnam("/tmp","availability");
			// save image
			file_put_contents($temp,$imgdata);
			// rename file
			$imgfile=$temp.".png";
			rename($temp,$imgfile);
			
			// we have to scale the image...
			$width=350;
			$height=250;
			//$imgscale=190/$width;
			$imgscale=.25;
			$pdf->Image($imgfile,$pdf->GetX(),$pdf->GetY()+5,$width*$imgscale,$height*$imgscale);
			
			// delete temp image file
			unlink($imgfile);
			
			$pdf->SetY($pdf->GetY()+60);

			

			
			$service_ok=0;
			$service_warning=0;
			$service_unknown=0;
			$service_critical=0;
			
			$avg_service_ok=0;
			$avg_service_warning=0;
			$avg_service_unknown=0;
			$avg_service_critical=0;
			$count_service_critical=0;
			$count_service_warning=0;
			$count_service_unknown=0;
			$count_service_critical=0;
			
			if($servicedata){
				foreach($servicedata->serviceavailability->service as $s){
					$service_ok=floatval($s->percent_known_time_ok);
					$service_warning=floatval($s->percent_known_time_warning);
					$service_unknown=floatval($s->percent_known_time_unknown);
					$service_critical=floatval($s->percent_known_time_critical);
					
					update_avail_avg($avg_service_ok,$service_ok,$count_service_ok);
					update_avail_avg($avg_service_warning,$service_warning,$count_service_warning);
					update_avail_avg($avg_service_unknown,$service_unknown,$count_service_unknown);
					update_avail_avg($avg_service_critical,$service_critical,$count_service_critical);
					}
				}
				
			// service chart
			$url=get_base_url()."reports/availability.php?".time();
			$url.="&mode=getchart&type=service&title=Service+Availability&data=".$avg_service_ok.",".$avg_service_warning.",".$avg_service_unknown.",".$avg_service_critical."&legend=Ok,Warning,Unknown,Critical&colors=".get_avail_color("ok").",".get_avail_color("warning").",".get_avail_color("unknown").",".get_avail_color("critical");
			// add authentication stuff
			$url.="&username=".$_SESSION["username"]."&ticket=".get_user_attr(0,"backend_ticket");

			// get raw image
			$imgdata=file_get_contents($url);
			// create tmp file
			$temp=tempnam("/tmp","availability");
			// save image
			file_put_contents($temp,$imgdata);
			// rename file
			$imgfile=$temp.".png";
			rename($temp,$imgfile);
			
			// we have to scale the image...
			$width=350;
			$height=250;
			//$imgscale=190/$width;
			$imgscale=.25;
			$pdf->Image($imgfile,$pdf->GetX(),$pdf->GetY()+5,$width*$imgscale,$height*$imgscale);
			
			// delete temp image file
			unlink($imgfile);
			
			$pdf->SetY($pdf->GetY()+70);
			


			// host table
			if($hostdata){

				$html="";

				$pdf->SetFont('Arial','B',12);
				$pdf->MultiCell(0,5,"Host Data");

				$html.=get_host_pdf_table_header();
				
				if($showdetail==1){
					$lasthost="";
					foreach($hostdata->hostavailability->host as $h){
					
						$hn=strval($h->host_name);
						$up=floatval($h->percent_known_time_up);
						$dn=floatval($h->percent_known_time_down);
						$un=floatval($h->percent_known_time_unreachable);
						
						//if($lasthost!="" && $lasthost!=$hn)
						//	echo "<tr><td colspan='4'><hr noshade size='1'></td></tr>";
					
						$html.="<tr>";
						$html.="<td ".$fs.">".$hn."&nbsp;&nbsp;</td>";
						$html.="<td ".$fs.">".$up."%&nbsp;&nbsp;</td>";
						$html.="<td ".$fs.">".$dn."%&nbsp;&nbsp;</td>";
						$html.="<td ".$fs.">".$un."%&nbsp;&nbsp;</td>";
						$html.="</tr>";		
						
						$lasthost=$hn;
						}
					}
				// averages
				if($showdetail==1)
					$html.="<tr><td colspan='4' ".$fs."></td></tr>";
				$html.="<tr><td ".$fs."><b>Average</b>&nbsp;&nbsp;</td><td ".$fs.">".number_format($avg_host_up,3)."%&nbsp;&nbsp;</td><td ".$fs.">".number_format($avg_host_down,3)."%&nbsp;&nbsp;</td><td ".$fs.">".number_format($avg_host_unreachable,3)."%&nbsp;&nbsp;</td></tr>";


				$html.="</table>";
				$pdf->htmltable($html);	
				
				$pdf->SetY($pdf->GetY()+10);
				}

			// service table
			if($servicedata){

				$html="";

				$pdf->SetFont('Arial','B',12);
				$pdf->MultiCell(0,5,"Service Data");

				$html.=get_service_pdf_table_header();
				
				if($showdetail==1){
					$lasthost="";
					foreach($servicedata->serviceavailability->service as $s){
					
						$hn=strval($s->host_name);
						$sd=strval($s->service_description);
						$ok=floatval($s->percent_known_time_ok);
						$wa=floatval($s->percent_known_time_warning);
						$un=floatval($s->percent_known_time_unknown);
						$service_critical=floatval($s->percent_known_time_critical);
						
						// newline
						if($lasthost!=$hn && $lasthost!=""){
							$html.="<tr><td colspan='6' ".$fs."></td></tr>";
							}
						
						$html.="<tr>";
						if($lasthost!=$hn)
							$html.="<td ".$fs.">".$hn."&nbsp;&nbsp;</td>";
						else
							$html.="<td ".$fs.">&nbsp;&nbsp;</td>";
						$html.="<td ".$fs.">".$sd."&nbsp;&nbsp;</td>";
						$html.="<td ".$fs.">".$ok."%&nbsp;&nbsp;</td>";
						$html.="<td ".$fs.">".$wa."%&nbsp;&nbsp;</td>";
						$html.="<td ".$fs.">".$un."%&nbsp;&nbsp;</td>";
						$html.="<td ".$fs.">".$service_critical."%&nbsp;&nbsp;</td>";
						$html.="</tr>";	

						$lasthost=$hn;
						}
					}
				// averages
				if($showdetail==1)
					$html.="<tr><td colspan='6' ".$fs."></td></tr>";
				$html.="<tr><td ".$fs.">&nbsp;</td><td ".$fs."><b>Average</b>&nbsp;&nbsp;</td><td ".$fs.">".number_format($avg_service_ok,3)."%&nbsp;&nbsp;</td><td ".$fs.">".number_format($avg_service_warning,3)."%&nbsp;&nbsp;</td><td ".$fs.">".number_format($avg_service_unknown,3)."%&nbsp;&nbsp;</td><td ".$fs.">".number_format($avg_service_critical,3)."%&nbsp;&nbsp;</td></tr>";
				

				$html.="</table>";
				$pdf->htmltable($html);	
				}
			}

	
		}
	

	$pdf->Output("availability.pdf", "I");
	}

function get_host_pdf_table_header(){
	$fs=' style="times" size="10" style="bold"';
	
	$html='
	<table border="1" align="center">
	<tr repeat>
	<td '.$fs.'>Host</td>
	<td '.$fs.'>Up</td>
	<td '.$fs.'>Down</td>
	<td '.$fs.'>Unreachable</td>
	</tr>
	';
	
	return $html;
	}
	
function get_service_pdf_table_header(){
	$fs=' style="times" size="10" style="bold"';
	
	$html='
	<table border="1" align="center">
	<tr	repeat>
	<td '.$fs.'>Host</td>
	<td '.$fs.'>Service</td>
	<td '.$fs.'>Ok</td>
	<td '.$fs.'>Warning</td>
	<td '.$fs.'>Unknown</td>
	<td '.$fs.'>Critical</td>
	</tr>
	';
	
	return $html;
	}
	

?>

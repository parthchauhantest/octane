<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: nagioscorereports.php 75 2010-04-01 19:40:08Z egalstad $

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
	
	show_legacy_reports_page();
	}
	

function show_legacy_reports_page(){
	global $lstr;
	
	licensed_feature_check();

	do_page_start(array("page_title"=>$lstr['NagiosCoreReportsPageTitle']),true);

?>
	<h1><?php echo $lstr['NagiosCoreReportsPageHeader'];?></h1>
	
	<p><?php echo $lstr['NagiosCoreReportsMessage'];?></p>
	
	<div class="legacyreportslist">
	
<?php
	show_legacy_report("avail.php","avail.png",$lstr['LegacyReportAvailabilityTitle'],$lstr['LegacyReportAvailabilityDescription']);
	
	show_legacy_report("trends.php","trends.png",$lstr['LegacyReportTrendsTitle'],$lstr['LegacyReportTrendsDescription']);
	
	show_legacy_report("history.php?host=all","history.png",$lstr['LegacyReportHistoryTitle'],$lstr['LegacyReportHistoryDescription']);
	
	show_legacy_report("summary.php","summary.png",$lstr['LegacyReportSummaryTitle'],$lstr['LegacyReportSummaryDescription']);
	
	show_legacy_report("histogram.php","histogram.png",$lstr['LegacyReportHistogramTitle'],$lstr['LegacyReportHistogramDescription']);
	
	show_legacy_report("notifications.php?contact=all","notifications.png",$lstr['LegacyReportNotificationsTitle'],$lstr['LegacyReportNotificationsDescription']);
	
?>
	
	</div>
	
<?php
	do_page_end(true);
	}
	
function show_legacy_report($url,$img,$title,$desc){

	$baseurl=$nagioscoreui_path=nagioscore_get_ui_url().$url;
	$imgurl=get_base_url()."includes/components/xicore/images/legacyreports/".$img;
?>
	<div class="legacyreport">
	<div class="legacyreportimage">
	<a href="<?php echo $baseurl;?>"><img src="<?php echo $imgurl;?>" title="<?php echo $title;?>"></a>
	</div>
	<div class="legacyreportdescription">
	<div class="legacyreporttitle">
	<a href="<?php echo $baseurl;?>"><?php echo $title;?></a>
	</div>
	<?php echo $desc;?>
	</div>
	</div>
<?php
	}

?>


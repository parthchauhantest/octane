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

	do_page_start(array("page_title"=>$lstr['NagiosXIReportsPageTitle']),true);

?>
	<h1><?php echo $lstr['NagiosXIReportsPageHeader'];?></h1>
	
	<p><?php echo $lstr['NagiosXIReportsMessage'];?></p>
	
	<br>
	
	<div class="reportslist">
	
	<h2><a href="availability.php">Availability</a></h2>
	<p>Shows you what percentage of time monitored hosts and services were in different operational states.  Useful for SLA reports.  <a href="availability.php">Run availability report</a>.</p>
	
	
	<h2><a href="statehistory.php">State History</a></h2>
	<p>Provides a historical report of alerts/events that occurred over time.  <a href="statehistory.php">Run state history report</a>.</p>
	
	<h2><a href="topalertproducers.php">Top Alert Producers</a></h2>
	<p>Provides a report of hosts and services that experienced the most number of problems or events over a given time frame.  <a href="topalertproducers.php">Run top alert producers report</a>.</p>
	
	<h2><a href="histogram.php">Alert Histogram</a></h2>
	<p>Provides a report of host and service alerts grouped by time units.  Useful for spotting when problems occur most often.   <a href="histogram.php">Run alert histogram report</a>.</p>
	
	<h2><a href="alertheatmap.php">Alert Heatmap</a></h2>
	<p>Provides a visual heatmap image of host and service alerts over time.  Useful for spotting when problems occur most often.  <a href="alertheatmap.php">Run alert heatmap report</a>.</p>
	
	<h2><a href="notifications.php">Notifications</a></h2>
	<p>Provides a historical report of notifications that have been sent out over time.  <a href="notifications.php">Run notifications report</a>.</p>
	
	<h2><a href="eventlog.php">Event Log</a></h2>
	<p>Provides a detailed log of events captured by the Nagios Core monitoring engine.  Useful for debugging problems.  <a href="eventlog.php">Run event log report</a>.</p>
	
	
	</div>
	
<?php
	do_page_end(true);
	}

?>


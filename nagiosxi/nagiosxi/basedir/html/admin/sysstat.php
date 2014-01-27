<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: sysstat.php 75 2010-04-01 19:40:08Z egalstad $

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
	
	$pageopt=grab_request_var("pageopt","");
	
	if($pageopt=="monitoringengine")
		show_enginestat();
	else
		show_sysstat();
		
	exit;
	}


function show_sysstat($error=false,$msg=""){
	global $request;
	global $lstr;
	global $db_tables;
	global $sqlquery;
	

	do_page_start(array("page_title"=>$lstr['SystemStatusPageTitle']),true);

?>
	<h1><?php echo $lstr['SystemStatusPageHeader'];?></h1>
	

<?php
	display_message($error,false,$msg);
?>


	<div style="float: left; margin: 0 30px 30px 0;">
<?php
	display_dashlet("xicore_component_status","",null,DASHLET_MODE_OUTBOARD);
?>
	</div>

	<div style="float: left; margin: 0 30px 30px 0;">
<?php
	display_dashlet("xicore_server_stats","",null,DASHLET_MODE_OUTBOARD);
?>
	</div>
	

<?php
	do_page_end(true);
	exit();
	}
	

function show_enginestat($error=false,$msg=""){
	global $request;
	global $lstr;
	global $db_tables;
	global $sqlquery;
	

	do_page_start(array("page_title"=>$lstr['MonitoringEngineStatusPageTitle']),true);

?>
	<h1><?php echo $lstr['MonitoringEngineStatusPageHeader'];?></h1>
	

<?php
	display_message($error,false,$msg);
?>


	<div style="float: left; margin: 0 30px 30px 0;">
<?php
	display_dashlet("xicore_monitoring_process","",null,DASHLET_MODE_OUTBOARD);
?>
	</div>
	
	<div style="float: left; margin: 0 30px 30px 0;">
<?php
	display_dashlet("xicore_eventqueue_chart","",null,DASHLET_MODE_OUTBOARD);
?>
	</div>
	
	<br clear="all">
	
	<div style="float: left; margin: 0 30px 30px 0;">
<?php
	display_dashlet("xicore_monitoring_stats","",null,DASHLET_MODE_OUTBOARD);
?>
	</div>
	
	<div style="float: left; margin: 0 30px 30px 0;">
<?php
	display_dashlet("xicore_monitoring_perf","",null,DASHLET_MODE_OUTBOARD);
?>
	</div>
	

<?php
	do_page_end(true);
	exit();
	}
	
	
?>
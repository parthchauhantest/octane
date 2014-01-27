<?php
// XI Core Dashlet Functions
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: dashlets.inc.php 388 2010-11-28 19:19:51Z egalstad $

include_once(dirname(__FILE__).'/../componenthelper.inc.php');
include_once(dirname(__FILE__).'/../../utils-dashlets.inc.php');

include_once(dirname(__FILE__).'/dashlets-comments.inc.php');
include_once(dirname(__FILE__).'/dashlets-monitoringengine.inc.php');
include_once(dirname(__FILE__).'/dashlets-perfdata.inc.php');
include_once(dirname(__FILE__).'/dashlets-status.inc.php');
include_once(dirname(__FILE__).'/dashlets-sysstat.inc.php');
include_once(dirname(__FILE__).'/dashlets-tac.inc.php');
include_once(dirname(__FILE__).'/dashlets-tasks.inc.php');
include_once(dirname(__FILE__).'/dashlets-misc.inc.php');

init_xicore_dashlets();

////////////////////////////////////////////////////////////////////////
// CORE DASHLET INITIALIZATION
////////////////////////////////////////////////////////////////////////

// initializes all core dashlets
function init_xicore_dashlets(){

	// stuff that's common to all core dashlets
	$args=array(
		DASHLET_AUTHOR => "Nagios Enterprises, LLC",
		DASHLET_COPYRIGHT => "Dashlet Copyright &copy; 2009-2010 Nagios Enterprises. All rights reserved.",
		DASHLET_HOMEPAGE => "http://www.nagios.com",
		DASHLET_SHOWASAVAILABLE => true,
		);
	
	// XI news
	$args[DASHLET_NAME]="xicore_xi_news_feed";
	$args[DASHLET_TITLE]="Nagios XI News";
	$args[DASHLET_FUNCTION]="xicore_dashlet_xi_news_feed";
	$args[DASHLET_DESCRIPTION]="Shows the latest tutorials, howtos, videos, and news on Nagios XI.";
	$args[DASHLET_WIDTH]="350";
	$args[DASHLET_INBOARD_CLASS]="xicore_xi_news_feed_inboard";
	$args[DASHLET_OUTBOARD_CLASS]="xicore_xi_news_feed_outboard";
	$args[DASHLET_CLASS]="xicore_xi_news_feed";
	$args[DASHLET_SHOWASAVAILABLE]=true;
	register_dashlet($args[DASHLET_NAME],$args);

	// getting started tasks
	$args[DASHLET_NAME]="xicore_getting_started";
	$args[DASHLET_TITLE]="Getting Started Guide";
	$args[DASHLET_FUNCTION]="xicore_dashlet_getting_started";
	$args[DASHLET_DESCRIPTION]="Displays helpful information on getting started with Nagios XI.";
	$args[DASHLET_WIDTH]="350";
	$args[DASHLET_INBOARD_CLASS]="xicore_getting_started_inboard";
	$args[DASHLET_OUTBOARD_CLASS]="xicore_getting_started_outboard";
	$args[DASHLET_CLASS]="xicore_getting_started";
	$args[DASHLET_SHOWASAVAILABLE]=true;
	register_dashlet($args[DASHLET_NAME],$args);

	// admin tasks
	$args[DASHLET_NAME]="xicore_admin_tasks";
	$args[DASHLET_TITLE]="Administrative Tasks";
	$args[DASHLET_FUNCTION]="xicore_dashlet_admin_tasks";
	$args[DASHLET_DESCRIPTION]="Displays tasks that an administrator should take to setup and maintain the Nagios XI installation.";
	$args[DASHLET_WIDTH]="350";
	$args[DASHLET_INBOARD_CLASS]="xicore_admin_tasks_inboard";
	$args[DASHLET_OUTBOARD_CLASS]="xicore_admin_tasks_outboard";
	$args[DASHLET_CLASS]="xicore_admin_tasks";
	$args[DASHLET_SHOWASAVAILABLE]=true;
	register_dashlet($args[DASHLET_NAME],$args);

	// timed event queue summary - admin page
	$args[DASHLET_NAME]="xicore_eventqueue_chart";
	$args[DASHLET_TITLE]="Monitoring Engine Event Queue";
	$args[DASHLET_FUNCTION]="xicore_dashlet_eventqueue_chart";
	$args[DASHLET_DESCRIPTION]="Displays realtime status of the XI monitoring engine event queue.";
	$args[DASHLET_WIDTH]="350";
	$args[DASHLET_INBOARD_CLASS]="xicore_eventqueue_chart_inboard";
	$args[DASHLET_OUTBOARD_CLASS]="xicore_eventqueue_chart_outboard";
	$args[DASHLET_CLASS]="xicore_eventqueue_chart";
	$args[DASHLET_SHOWASAVAILABLE]=true;
	register_dashlet($args[DASHLET_NAME],$args);

	// component status - admin page
	$args[DASHLET_NAME]="xicore_component_status";
	$args[DASHLET_TITLE]="Core Component Status";
	$args[DASHLET_FUNCTION]="xicore_dashlet_component_status";
	$args[DASHLET_DESCRIPTION]="Displays realtime status of core XI system components.";
	$args[DASHLET_WIDTH]="300";
	$args[DASHLET_INBOARD_CLASS]="xicore_component_status_inboard";
	$args[DASHLET_OUTBOARD_CLASS]="xicore_component_status_outboard";
	$args[DASHLET_CLASS]="xicore_component_status";
	$args[DASHLET_SHOWASAVAILABLE]=true;
	register_dashlet($args[DASHLET_NAME],$args);

	// server stats - admin page
	$args[DASHLET_NAME]="xicore_server_stats";
	$args[DASHLET_TITLE]="Server Stats";
	$args[DASHLET_FUNCTION]="xicore_dashlet_server_stats";
	$args[DASHLET_DESCRIPTION]="Displays realtime statistics of the XI server.";
	$args[DASHLET_WIDTH]="300";
	$args[DASHLET_INBOARD_CLASS]="xicore_server_stats_inboard";
	$args[DASHLET_OUTBOARD_CLASS]="xicore_server_stats_outboard";
	$args[DASHLET_CLASS]="xicore_server_stats";
	$args[DASHLET_SHOWASAVAILABLE]=true;
	register_dashlet($args[DASHLET_NAME],$args);
	
	// monitoring engine stats - admin page
	$args[DASHLET_NAME]="xicore_monitoring_stats";
	$args[DASHLET_TITLE]="Monitoring Engine Stats";
	$args[DASHLET_FUNCTION]="xicore_dashlet_monitoring_stats";
	$args[DASHLET_DESCRIPTION]="Displays realtime check statistics of the XI monitoring engine.";
	$args[DASHLET_WIDTH]="300";
	$args[DASHLET_INBOARD_CLASS]="xicore_monitoring_stats_inboard";
	$args[DASHLET_OUTBOARD_CLASS]="xicore_monitoring_stats_outboard";
	$args[DASHLET_CLASS]="xicore_monitoring_stats";
	$args[DASHLET_SHOWASAVAILABLE]=true;
	register_dashlet($args[DASHLET_NAME],$args);

	// monitoring engine performance - admin page
	$args[DASHLET_NAME]="xicore_monitoring_perf";
	$args[DASHLET_TITLE]="Monitoring Engine Performance";
	$args[DASHLET_FUNCTION]="xicore_dashlet_monitoring_perf";
	$args[DASHLET_DESCRIPTION]="Displays realtime performance of the XI monitoring engine.";
	$args[DASHLET_WIDTH]="300";
	$args[DASHLET_INBOARD_CLASS]="xicore_monitoring_stats_inboard";
	$args[DASHLET_OUTBOARD_CLASS]="xicore_monitoring_stats_outboard";
	$args[DASHLET_CLASS]="xicore_monitoring_stats";
	$args[DASHLET_SHOWASAVAILABLE]=true;
	register_dashlet($args[DASHLET_NAME],$args);

	// monitoring engine process - admin page
	$args[DASHLET_NAME]="xicore_monitoring_process";
	$args[DASHLET_TITLE]="Monitoring Engine Process";
	$args[DASHLET_FUNCTION]="xicore_dashlet_monitoring_process";
	$args[DASHLET_DESCRIPTION]="Displays realtime information of the XI monitoring engine process.";
	$args[DASHLET_WIDTH]="300";
	$args[DASHLET_INBOARD_CLASS]="xicore_monitoring_process_inboard";
	$args[DASHLET_OUTBOARD_CLASS]="xicore_monitoring_process_outboard";
	$args[DASHLET_CLASS]="xicore_monitoring_process";
	$args[DASHLET_SHOWASAVAILABLE]=true;
	register_dashlet($args[DASHLET_NAME],$args);

	// performance graph chart
	$args[DASHLET_NAME]="xicore_perfdata_chart";
	$args[DASHLET_TITLE]="Performance Graph";
	$args[DASHLET_FUNCTION]="xicore_dashlet_perfdata_chart";
	$args[DASHLET_DESCRIPTION]="Displays a performance data graph for a specific host or service.";
	$args[DASHLET_WIDTH]="500";
	$args[DASHLET_HEIGHT]="225";
	$args[DASHLET_INBOARD_CLASS]="xicore_perfdata_chart_inboard";
	$args[DASHLET_OUTBOARD_CLASS]="xicore_perfdata_chart_outboard";
	$args[DASHLET_CLASS]="xicore_perfdata_chart";
	$args[DASHLET_SHOWASAVAILABLE]=false;
	register_dashlet($args[DASHLET_NAME],$args);
	
	// host status sumary
	$args[DASHLET_NAME]="xicore_host_status_summary";
	$args[DASHLET_TITLE]="Host Status Summary";
	$args[DASHLET_FUNCTION]="xicore_dashlet_host_status_summary";
	$args[DASHLET_DESCRIPTION]="Displays a table with a quick summary of host status.";
	$args[DASHLET_WIDTH]="250";
	$args[DASHLET_HEIGHT]="125";
	$args[DASHLET_INBOARD_CLASS]="xicore_host_status_summary_inboard";
	$args[DASHLET_OUTBOARD_CLASS]="xicore_host_status_summary_outboard";
	$args[DASHLET_CLASS]="xicore_host_status_summary";
	$args[DASHLET_SHOWASAVAILABLE]=true;
	register_dashlet($args[DASHLET_NAME],$args);
	
	// service status summary
	$args[DASHLET_NAME]="xicore_service_status_summary";
	$args[DASHLET_TITLE]="Service Status Summary";
	$args[DASHLET_FUNCTION]="xicore_dashlet_service_status_summary";
	$args[DASHLET_DESCRIPTION]="Displays a table with a quick summary of service status.";
	$args[DASHLET_WIDTH]="250";
	$args[DASHLET_HEIGHT]="125";
	$args[DASHLET_INBOARD_CLASS]="xicore_service_status_summary_inboard";
	$args[DASHLET_OUTBOARD_CLASS]="xicore_service_status_summary_outboard";
	$args[DASHLET_CLASS]="xicore_service_status_summary";
	$args[DASHLET_SHOWASAVAILABLE]=true;
	register_dashlet($args[DASHLET_NAME],$args);

	// comments
	$args[DASHLET_NAME]="xicore_comments";
	$args[DASHLET_TITLE]="Acknowledgements and Comments";
	$args[DASHLET_FUNCTION]="xicore_dashlet_comments";
	$args[DASHLET_DESCRIPTION]="Displays current acknowledgements and comments.";
	$args[DASHLET_WIDTH]="500";
	$args[DASHLET_HEIGHT]="125";
	$args[DASHLET_INBOARD_CLASS]="xicore_comments_inboard";
	$args[DASHLET_OUTBOARD_CLASS]="xicore_comments_outboard";
	$args[DASHLET_CLASS]="xicore_comments";
	$args[DASHLET_SHOWASAVAILABLE]=false;
	register_dashlet($args[DASHLET_NAME],$args);
	
	// hostgroup status overview
	$args[DASHLET_NAME]="xicore_hostgroup_status_overview";
	$args[DASHLET_TITLE]="Hostgroup Status Overview";
	$args[DASHLET_FUNCTION]="xicore_dashlet_hostgroup_status_overview";
	$args[DASHLET_DESCRIPTION]="Displays an overview of host and service status for a particular hostgroup.";
	$args[DASHLET_WIDTH]="250";
	$args[DASHLET_HEIGHT]="125";
	$args[DASHLET_INBOARD_CLASS]="xicore_hostgroup_status_overview_inboard";
	$args[DASHLET_OUTBOARD_CLASS]="xicore_hostgroup_status_overview_outboard";
	$args[DASHLET_CLASS]="xicore_hostgroup_status_overview";
	$args[DASHLET_SHOWASAVAILABLE]=false;
	register_dashlet($args[DASHLET_NAME],$args);
	
	// hostgroup status grid
	$args[DASHLET_NAME]="xicore_hostgroup_status_grid";
	$args[DASHLET_TITLE]="Hostgroup Status Grid";
	$args[DASHLET_FUNCTION]="xicore_dashlet_hostgroup_status_grid";
	$args[DASHLET_DESCRIPTION]="Displays a grid of host and service status for a particular hostgroup.";
	$args[DASHLET_WIDTH]="250";
	$args[DASHLET_HEIGHT]="125";
	$args[DASHLET_INBOARD_CLASS]="xicore_hostgroup_status_overview_inboard";
	$args[DASHLET_OUTBOARD_CLASS]="xicore_hostgroup_status_overview_outboard";
	$args[DASHLET_CLASS]="xicore_hostgroup_status_overview";
	$args[DASHLET_SHOWASAVAILABLE]=false;
	register_dashlet($args[DASHLET_NAME],$args);
	
	// servicegroup status overview
	$args[DASHLET_NAME]="xicore_servicegroup_status_overview";
	$args[DASHLET_TITLE]="Servicegroup Status Overview";
	$args[DASHLET_FUNCTION]="xicore_dashlet_servicegroup_status_overview";
	$args[DASHLET_DESCRIPTION]="Displays an overview of host and service status for a particular servicegroup.";
	$args[DASHLET_WIDTH]="250";
	$args[DASHLET_HEIGHT]="125";
	$args[DASHLET_INBOARD_CLASS]="xicore_servicegroup_status_overview_inboard";
	$args[DASHLET_OUTBOARD_CLASS]="xicore_servicegroup_status_overview_outboard";
	$args[DASHLET_CLASS]="xicore_servicegroup_status_overview";
	$args[DASHLET_SHOWASAVAILABLE]=false;
	register_dashlet($args[DASHLET_NAME],$args);

	// servicegroup status grid
	$args[DASHLET_NAME]="xicore_servicegroup_status_grid";
	$args[DASHLET_TITLE]="Servicegroup Status Grid";
	$args[DASHLET_FUNCTION]="xicore_dashlet_servicegroup_status_grid";
	$args[DASHLET_DESCRIPTION]="Displays a grid of host and service status for a particular servicegroup.";
	$args[DASHLET_WIDTH]="250";
	$args[DASHLET_HEIGHT]="125";
	$args[DASHLET_INBOARD_CLASS]="xicore_servicegroup_status_grid_inboard";
	$args[DASHLET_OUTBOARD_CLASS]="xicore_servicegroup_status_grid_outboard";
	$args[DASHLET_CLASS]="xicore_servicegroup_status_grid";
	$args[DASHLET_SHOWASAVAILABLE]=false;
	register_dashlet($args[DASHLET_NAME],$args);

	// hostgroup status summary
	$args[DASHLET_NAME]="xicore_hostgroup_status_summary";
	$args[DASHLET_TITLE]="Hostgroup Status Summary";
	$args[DASHLET_FUNCTION]="xicore_dashlet_hostgroup_status_summary";
	$args[DASHLET_DESCRIPTION]="Displays a summary of host and service status for all hostgroups.";
	$args[DASHLET_WIDTH]="350";
	$args[DASHLET_HEIGHT]="250";
	$args[DASHLET_INBOARD_CLASS]="xicore_hostgroup_status_summary_inboard";
	$args[DASHLET_OUTBOARD_CLASS]="xicore_hostgroup_status_summary_outboard";
	$args[DASHLET_CLASS]="xicore_hostgroup_status_summary";
	$args[DASHLET_SHOWASAVAILABLE]=true;
	register_dashlet($args[DASHLET_NAME],$args);
	
	// servicegroup status summary
	$args[DASHLET_NAME]="xicore_servicegroup_status_summary";
	$args[DASHLET_TITLE]="Servicegroup Status Summary";
	$args[DASHLET_FUNCTION]="xicore_dashlet_servicegroup_status_summary";
	$args[DASHLET_DESCRIPTION]="Displays a summary of host and service status for all servicegroups.";
	$args[DASHLET_WIDTH]="350";
	$args[DASHLET_HEIGHT]="250";
	$args[DASHLET_INBOARD_CLASS]="xicore_servicegroup_status_summary_inboard";
	$args[DASHLET_OUTBOARD_CLASS]="xicore_servicegroup_status_summary_outboard";
	$args[DASHLET_CLASS]="xicore_servicegroup_status_summary";
	$args[DASHLET_SHOWASAVAILABLE]=true;
	register_dashlet($args[DASHLET_NAME],$args);
	
	// available updates
	$args[DASHLET_NAME]="xicore_available_updates";
	$args[DASHLET_TITLE]="Available Updates";
	$args[DASHLET_FUNCTION]="xicore_dashlet_available_updates";
	$args[DASHLET_DESCRIPTION]="Displays the status of available updates for your Nagios XI installation.";
	$args[DASHLET_WIDTH]="350";
	$args[DASHLET_HEIGHT]="250";
	$args[DASHLET_INBOARD_CLASS]="xicore_available_updates_inboard";
	$args[DASHLET_OUTBOARD_CLASS]="xicore_available_updates_outboard";
	$args[DASHLET_CLASS]="xicore_available_updates";
	$args[DASHLET_SHOWASAVAILABLE]=true;
	register_dashlet($args[DASHLET_NAME],$args);
	
	// network outages
	$args[DASHLET_NAME]="xicore_network_outages";
	$args[DASHLET_TITLE]="Network Outages";
	$args[DASHLET_FUNCTION]="xicore_dashlet_network_outages";
	$args[DASHLET_DESCRIPTION]="Displays blocking network outages.";
	$args[DASHLET_WIDTH]="450";
	$args[DASHLET_HEIGHT]="250";
	$args[DASHLET_INBOARD_CLASS]="xicore_network_outages_inboard";
	$args[DASHLET_OUTBOARD_CLASS]="xicore_network_outages_outboard";
	$args[DASHLET_CLASS]="xicore_network_outages";
	$args[DASHLET_SHOWASAVAILABLE]=true;
	register_dashlet($args[DASHLET_NAME],$args);
	
	// network outages
	$args[DASHLET_NAME]="xicore_network_outages_summary";
	$args[DASHLET_TITLE]="Network Outages Summary";
	$args[DASHLET_FUNCTION]="xicore_dashlet_network_outages_summary";
	$args[DASHLET_DESCRIPTION]="Displays summary of network outages.";
	$args[DASHLET_WIDTH]="450";
	$args[DASHLET_HEIGHT]="250";
	$args[DASHLET_INBOARD_CLASS]="xicore_network_outages_summary_inboard";
	$args[DASHLET_OUTBOARD_CLASS]="xicore_network_outages_summary_outboard";
	$args[DASHLET_CLASS]="xicore_network_outages_summary";
	$args[DASHLET_SHOWASAVAILABLE]=false;
	register_dashlet($args[DASHLET_NAME],$args);
	
	// network health
	$args[DASHLET_NAME]="xicore_network_health";
	$args[DASHLET_TITLE]="Network Health";
	$args[DASHLET_FUNCTION]="xicore_dashlet_network_health";
	$args[DASHLET_DESCRIPTION]="Displays summary of network health.";
	$args[DASHLET_WIDTH]="450";
	$args[DASHLET_HEIGHT]="250";
	$args[DASHLET_INBOARD_CLASS]="xicore_network_health_inboard";
	$args[DASHLET_OUTBOARD_CLASS]="xicore_network_health_outboard";
	$args[DASHLET_CLASS]="xicore_network_health";
	$args[DASHLET_SHOWASAVAILABLE]=false;
	register_dashlet($args[DASHLET_NAME],$args);

	// host status tac summary
	$args[DASHLET_NAME]="xicore_host_status_tac_summary";
	$args[DASHLET_TITLE]="Host Status TAC Summary";
	$args[DASHLET_FUNCTION]="xicore_dashlet_host_status_tac_summary";
	$args[DASHLET_DESCRIPTION]="Displays summary of host status.";
	$args[DASHLET_WIDTH]="450";
	$args[DASHLET_HEIGHT]="250";
	$args[DASHLET_INBOARD_CLASS]="xicore_host_status_tac_summary_inboard";
	$args[DASHLET_OUTBOARD_CLASS]="xicore_host_status_tac_summary_outboard";
	$args[DASHLET_CLASS]="xicore_host_status_tac_summary";
	$args[DASHLET_SHOWASAVAILABLE]=false;
	register_dashlet($args[DASHLET_NAME],$args);

	// service status tac summary
	$args[DASHLET_NAME]="xicore_service_status_tac_summary";
	$args[DASHLET_TITLE]="Service Status TAC Summary";
	$args[DASHLET_FUNCTION]="xicore_dashlet_service_status_tac_summary";
	$args[DASHLET_DESCRIPTION]="Displays summary of service status.";
	$args[DASHLET_WIDTH]="450";
	$args[DASHLET_HEIGHT]="250";
	$args[DASHLET_INBOARD_CLASS]="xicore_service_status_tac_summary_inboard";
	$args[DASHLET_OUTBOARD_CLASS]="xicore_service_status_tac_summary_outboard";
	$args[DASHLET_CLASS]="xicore_service_status_tac_summary";
	$args[DASHLET_SHOWASAVAILABLE]=false;
	register_dashlet($args[DASHLET_NAME],$args);

	// feature status tac summary
	$args[DASHLET_NAME]="xicore_feature_status_tac_summary";
	$args[DASHLET_TITLE]="Feature Status TAC Summary";
	$args[DASHLET_FUNCTION]="xicore_dashlet_feature_status_tac_summary";
	$args[DASHLET_DESCRIPTION]="Displays summary of feature status.";
	$args[DASHLET_WIDTH]="450";
	$args[DASHLET_HEIGHT]="250";
	$args[DASHLET_INBOARD_CLASS]="xicore_feature_status_tac_summary_inboard";
	$args[DASHLET_OUTBOARD_CLASS]="xicore_feature_status_tac_summary_outboard";
	$args[DASHLET_CLASS]="xicore_feature_status_tac_summary";
	$args[DASHLET_SHOWASAVAILABLE]=false;
	register_dashlet($args[DASHLET_NAME],$args);
	}



?>
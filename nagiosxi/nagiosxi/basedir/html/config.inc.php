<?php
//
// Copyright (c) 2008-2010 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: config.inc.php 202 2010-07-13 21:22:57Z egalstad $

// base url
$cfg['base_url']="/nagiosxi";  // do not include http(s) or host name - this is the base from "http://localhost"

// base root directory where XI is installed
$cfg['root_dir']="/usr/local/nagiosxi";

// directory where scripts are installed
$cfg['script_dir']="/usr/local/nagiosxi/scripts";

$cfg['xidpe_dir'] = '/usr/local/nagios/var/spool/xidpe/';
$cfg['perfdata_spool'] = '/usr/local/nagios/var/spool/perfdata/';

// nom checkpoints
$cfg['nom_checkpoints_dir']="/usr/local/nagiosxi/nom/checkpoints/nagioscore/";

// force http/https
$cfg['use_https']=false;  // determines whether cron jobs and other scripts will force the use of HTTPS instead of HTTP

// allow for different http port for subsystem calls 
$cfg['port_number'] = false; 

// default server, db, connection settings
$cfg['dbtype']=''; // this setting is no longer used - use settings below
$cfg['dbserver']='localhost'; // this setting is no longer used - use settings below

// db-specific connection information
$cfg['db_info']=array(
	"nagiosxi" => array(
		"dbtype" => 'pgsql',
		"dbserver" => 'localhost',
		"user" => 'nagiosxi',
		"pwd" => 'n@gweb',
		"db" => 'nagiosxi',
		"dbmaint" => array(		// variables affecting maintenance of db
			"max_auditlog_age" => 30, // max time (in DAYS) to keep audit log entries
			"max_commands_age" => 480, // max time (minutes) to keep commands
			"max_events_age" => 480, // max time (minutes) to keep events
			"optimize_interval" => 60, // time (in minutes) between db optimization runs
			"repair_interval" => 0, // time (in minutes) between db repair runs
			),
		),
	"ndoutils" => array(
		"dbtype" => 'mysql',
		"dbserver" => 'localhost',
		"user" => 'ndoutils',
		"pwd" => 'n@gweb',
		"db" => 'nagios',
		"dbmaint" => array(		// variables affecting maintenance of ndoutils db
		
			"max_externalcommands_age" => 7, // max time (in DAYS) to keep external commands
			"max_logentries_age" => 90, // max time (in DAYS) to keep log entries
			"max_statehistory_age" => 730, // max time (in DAYS) to keep state history information
			"max_notifications_age" => 90, // max time (in DAYS) to keep notifications			
			"max_timedevents_age" => 5, // max time (minutes) to keep timed events
			"max_systemcommands_age" => 5, // max time (minutes) to keep system commands
			"max_servicechecks_age" => 5, // max time (minutes) to keep service checks
			"max_hostchecks_age" => 5, // max time (minutes) to keep host checks
			"max_eventhandlers_age" => 5, // max time (minutes) to keep event handlers
			"optimize_interval" => 60, // time (in minutes) between db optimization runs
			"repair_interval" => 0, // time (in minutes) between db repair runs
			),
		),
	"nagiosql" => array(
		"dbtype" => 'mysql',
		"dbserver" => 'localhost',
		"user" => 'nagiosql',
		"pwd" => 'n@gweb',
		"db" => 'nagiosql',
		"dbmaint" => array(		// variables affecting maintenance of db
			"max_logbook_age" => 480, // max time (minutes) to keep log book records
			"optimize_interval" => 60, // time (in minutes) between db optimization runs
			"repair_interval" => 0, // time (in minutes) between db repair runs
			),
		),
	);

// db-specific table prefixes
$cfg['db_prefix']=array(
	"ndoutils" => "nagios_",    // prefix for NDOUtils tables
	"nagiosxi" => "xi_",		// prefix for XI tables
	"nagiosql" => "tbl_",		// prefix for NagiosQL tables
	);

// component info
$cfg['component_info']=array(
	"nagioscore" => array(
		"cgi_dir" => "/usr/local/nagios/sbin",
		"import_dir" => "/usr/local/nagios/etc/import",
		"plugin_dir" => "/usr/local/nagios/libexec",
		"cgi_config_file" => "/usr/local/nagios/etc/cgi.cfg",
		"cmd_file" => "/usr/local/nagios/var/rw/nagios.cmd",
		"nom_checkpoint_interval" => 1440, // time (in minutes) between nom checkpoints
		),
	"pnp" => array(
		"perfdata_dir" => "/usr/local/nagios/share/perfdata",
		"share_dir" => "/usr/local/nagios/share/pnp",
		"direct_url" => "/nagios/pnp",
		"username" => 'nagiosxi', // don't change this!
		"password" => 'nagiosadmin', // this gets reset when security credentials are reset after installation
		),
	"nagiosql" => array(
		"dir" => "/var/www/html/nagiosql",
		"direct_url" => "/nagiosql",
		"username" => 'nagiosxi', // don't change this!
		"password" => 'n@gweb',  // this gets reset when security credentials are reset after installation
		),
	"nagvis" => array(
		"share_dir" => "/usr/local/nagios/share/nagvis",
		"direct_url" => "/nagios/nagvis",
		"username" => 'nagiosadmin', // don't change this!
		"password" => 'nagiosadmin', // this gets reset when security credentials are reset after installation
		),
	);

$cfg['demo_mode']=false; // is this in demo mode

$cfg['dashlet_refresh_multiplier']=1000;  // milliseconds (1 second = 1000)

// REFRESH RATES FOR VARIOUS DASHLETS (IN SECONDS UNLESS THE MULTIPLIER IS CHANGED)
$cfg['dashlet_refresh_rates']=array(
	"available_updates" => 24*60*60,		 // 24 hours
	"systat_eventqueuechart" => 5,
	"sysstat_monitoringstats" => 30,
	"systat_monitoringperf" => 30,
	"sysstat_monitoringproc" => 30,
	"perfdata_chart" => 60,  				// performance graphs
	"network_outages" => 30,
	"host_status_summary" => 60,
	"service_status_summary" => 60,
	"hostgroup_status_overview" => 60,
	"hostgroup_status_grid" => 60,
	"servicegroup_status_overview" => 60,
	"servicegroup_status_grid" => 60,
	"hostgroup_status_summary" => 60,
	"servicegroup_status_summary" => 60,
	"sysstat_componentstates" => 7,
	"sysstat_serverstats" => 5,
	"network_outages_summary" => 30,
	"network_health" => 30,
	"host_status_tac_summary" => 30,
	"service_status_tac_summary" => 30,
	"feature_status_tac_summary" => 30,
	"admin_tasks" => 60,
	"getting_started" => 60,
	"pagetop_alert_content" => 30, 		// not a dashlet yet, sits in page header
	"tray_alert" => 30, // sites in page footer
	);
	
	
// MEMCACHED SETUP	
$cfg['memcached_enable']=false; // should we use memcached or not?
$cfg['memcached_hosts']=array('127.0.0.1','192.168.1.3');  // one or more memcached servers
$cfg['memcached_port']=11211;  // default memcached port
$cfg['memcached_compress']=false;  // use true to store items compressed
$cfg['memcached_ttl']=10; // max number of seconds data (from SELECT statements) should be cached


// HTTP BASIC AUTHENTICATION INFO -- USED BY SUBSYSTEM
$cfg['use_basic_authentication']=false; // is HTTP Basic authentication being used? if so, set the two variables below...
$cfg['subsystem_basic_auth_username']='nagiosxi'; // subsystem credentials
$cfg['subsystem_basic_auth_password']='somepassword';

$cfg['default_language']='en';	// default language
$cfg['default_theme']='';	// default theme

// available languages
$cfg['languages']=array(
	"en" => "English",
	);


/*********   DO NOT MODIFY ANYTHING BELOW THIS LINE   **********/

$cfg['default_instance_id']=1;  // default ndoutils instance to read from
$cfg['default_result_records']=100000;  // max number of records to return by default

$cfg['online_help_url']="http://support.nagios.com/"; // comment this out to disable online help links
$cfg['feedback_url']="http://api.nagios.com/feedback/";
$cfg['privacy_policy_url']="http://www.nagios.com/legal/privacypolicy/";

//$cfg['db_version']=101;
$cfg['db_version']=113;
//$cfg['product_version']='2009RC1';

$cfg['subsystem_ticket']="12345";  // default - this gets reset...

$cfg['htaccess_file']="/usr/local/nagiosxi/etc/htpasswd.users";
$cfg['htpasswd_path']="/usr/bin/htpasswd";

$cfg['enable_analytics']=1; 



///////// keep these in order /////////

// include generic db defs
require_once(dirname(__FILE__).'/includes/db.inc.php');

// include generic  definitions
require_once(dirname(__FILE__).'/db/common.inc.php');
// include db-specific definitions
//require_once(dirname(__FILE__).'/db/'.$cfg['dbtype'].'.inc.php');

?>
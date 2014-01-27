#!/usr/bin/php -q
<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: cmdsubsys.php 1321 2012-08-16 16:34:43Z mguthrie $

define("SUBSYSTEM",1);
//define("BACKEND",1);

require_once(dirname(__FILE__).'/../html/config.inc.php');
require_once(dirname(__FILE__).'/../html/includes/utils.inc.php');

$max_time=59;
$logging = true;

init_cmdsubsys();
do_cmdsubsys_jobs();



function init_cmdsubsys(){

	// make database connections
	$dbok=db_connect_all();
	if($dbok==false){
		echo "ERROR CONNECTING TO DATABASES!\n";
		exit();
		}

	return;
	}

function do_cmdsubsys_jobs(){
	global $max_time;
	global $logging;
		
	//enable logging?  
	$logging = is_null(get_option('enable_subsystem_logging')) ? true : get_option("enable_subsystem_logging");
	
	$start_time=time();
	$t=0;

	while(1){
	
		$n=0;
	
		// bail if if we're been here too long
		$now=time();
		if(($now-$start_time)>$max_time)
			break;
	
		$n+=process_commands();
		$t+=$n;
		
		// sleep for 1 second if we didn't do anything...
		if($n==0){
			update_sysstat();
			if($logging)
				echo ".";
			usleep(1000000);
			}
		}
		
	update_sysstat();
	echo "\n";
	echo "PROCESSED $t COMMANDS\n";

	// handle misc background jobs (update checks, etc)
	do_uloop_jobs();
	}
	
	
function update_sysstat(){
	// record our run in sysstat table
	$arr=array(
		"last_check" => time(),
		);
	$sdata=serialize($arr);
	update_systat_value("cmdsubsys",$sdata);
	}
	
	
function process_commands(){
	global $db_tables;
	global $cfg;

	// get the next queued command
	$sql="SELECT * FROM ".$db_tables[DB_NAGIOSXI]["commands"]." WHERE status_code='0' AND event_time<=NOW() ORDER BY submission_time ASC";
	$args=array(
		"sql" => $sql,
		"useropts" => array(
			"records" => 1,
			),
		);
	$sql=limit_sql_query_records($args,$cfg['db_info'][DB_NAGIOSXI]['dbtype']);
	//echo "SQL: $sql\n";
	if(($rs=exec_sql_query(DB_NAGIOSXI,$sql,true,false))){
		if(!$rs->EOF){
			process_command_record($rs);
			return 1;
			}
		}
	return 0;
	}
	
function process_command_record($rs){
	global $db_tables;
	global $cfg;
	global $logging;
	
	if($logging)
		echo "PROCESSING COMMAND ID ".$rs->fields["command_id"]."...\n";
	
	$command_id=$rs->fields["command_id"];
	$command=intval($rs->fields["command"]);
	$command_data=$rs->fields["command_data"];
	
	
	
	// immediately update the command as being processed
	$sql="UPDATE ".$db_tables[DB_NAGIOSXI]["commands"]." SET status_code='".escape_sql_param(COMMAND_STATUS_PROCESSING,DB_NAGIOSXI)."', processing_time=NOW() WHERE command_id='".escape_sql_param($command_id,DB_NAGIOSXI)."'";
	exec_sql_query(DB_NAGIOSXI,$sql);

	// process the command
	$result_code=process_command($command,$command_data,$result);

	// mark the command as being completed
	$sql="UPDATE ".$db_tables[DB_NAGIOSXI]["commands"]." SET status_code='".escape_sql_param(COMMAND_STATUS_COMPLETED,DB_NAGIOSXI)."', result_code='".escape_sql_param($result_code,DB_NAGIOSXI)."', result='".escape_sql_param($result,DB_NAGIOSXI)."', processing_time=NOW() WHERE command_id='".escape_sql_param($command_id,DB_NAGIOSXI)."'";
	exec_sql_query(DB_NAGIOSXI,$sql);
	}
	

function process_command($command,$command_data,&$output){
	global $cfg;
	global $logging;
	
	//don't reveal password data for certain commands
	if($logging && ($command!=1100 && $command!=2881) )
		echo "PROCESS COMMAND: CMD=$command, DATA=$command_data\n";
	
	$output="";
	$return_code=0;
	
	// get the base dir for scripts
	$base_dir=$cfg['root_dir'];
	$script_dir=$cfg['script_dir'];
	
	// default to no command data
	$cmdline="";
	$script_name="";
	$script_data="";
	
	// post-command function call
	$post_func="";
	$post_func_args=array();
	
	
	switch($command){
	
		case COMMAND_NAGIOSCORE_SUBMITCOMMAND:
			echo "COMMAND DATA: $command_data\n";
		
			// command data  is serialized so decode it...
			$cmdarr=unserialize($command_data);
			
			if($logging) {
				echo "CMDARR:\n";
				print_r($cmdarr);
			}
			if(array_key_exists("cmd",$cmdarr))
				$corecmdid=strval($cmdarr["cmd"]);
			else
				return COMMAND_RESULT_ERROR;
			
			$nagioscorecmd=get_nagioscore_command($corecmdid,$cmdarr);

			// log it
			send_to_audit_log("cmdsubsys: User submitted a command to Nagios Core: ".$nagioscorecmd,AUDITLOGTYPE_INFO);
			
			echo "CORE CMD: $nagioscorecmd\n";
		
			// SECURITY CONSIDERATION:
			// we write directly to the Nagios command file to avoid shell interpretation of meta characters
			if($logging)
				echo "SUBMITTING A NAGIOSCORE COMMAND...\n";
			if(($result=submit_direct_nagioscore_command($nagioscorecmd,$output))==false)
				return COMMAND_RESULT_ERROR;
			else
				return COMMAND_RESULT_OK;
			break;

		case COMMAND_NAGIOSCORE_APPLYCONFIG:
			//$script_name="restart_nagios_with_export.sh";
			$script_name="reconfigure_nagios.sh";
			echo "APPLYING NAGIOSCORE CONFIG...\n";
			
			// log it
			send_to_audit_log("cmdsubsys: User applied a new configuration to Nagios Core",AUDITLOGTYPE_INFO);	

			//do callback functions
			$args=array(); 
			do_callbacks(CALLBACK_SUBSYS_APPLYCONFIG,$args); 
			
			break;
			
		case COMMAND_NAGIOSCORE_RECONFIGURE:
			$script_name="reconfigure_nagios.sh";
			echo "RECONFIGURING NAGIOSCORE ...\n";

			// log it
			send_to_audit_log("cmdsubsys: User reconfigured Nagios Core",AUDITLOGTYPE_INFO);	

			//do callback functions
			$args=array(); 
			do_callbacks(CALLBACK_SUBSYS_APPLYCONFIG,$args); 
			
			break;
			
		// NAGIOSQL COMMANDS
		case COMMAND_NAGIOSQL_DELETECONTACT:
			//$script_name="nagiosql_delete_contact.php";
			//$script_data="--id=".$command_data;
			$script_name="nagiosql_delete_object.sh";
			$script_data="contact ".$command_data;
			echo "DELETING CONTACT ...\n";

			// log it
			send_to_audit_log("cmdsubsys: User deleted contact '".$command_data."'",AUDITLOGTYPE_DELETE);			
			break;
		case COMMAND_NAGIOSQL_DELETETIMEPERIOD:
			$script_name="nagiosql_delete_object.sh";
			$script_data="timeperiod ".$command_data;
			echo "DELETING TIMEPERIOD ...\n";

			// log it
			send_to_audit_log("cmdsubsys: User deleted timeperiod '".$command_data."'",AUDITLOGTYPE_DELETE);			
			break;
		case COMMAND_NAGIOSQL_DELETESERVICE:
			$script_name="nagiosql_delete_object.sh";
			$script_data="service ".$command_data;
			echo "DELETING SERVICE ...\n";
			// log it
			send_to_audit_log("cmdsubsys: User deleted service '".$command_data."'",AUDITLOGTYPE_DELETE);			
			break;
		case COMMAND_NAGIOSQL_DELETEHOST:
			$script_name="nagiosql_delete_object.sh";
			$script_data="host ".$command_data;
			echo "DELETING HOST ...\n";
			// log it
			send_to_audit_log("cmdsubsys: User deleted host '".$command_data."'",AUDITLOGTYPE_DELETE);			
			break;
			
			
		// DAEMON COMMANDS
		// NAGIOS CORE
		case COMMAND_NAGIOSCORE_GETSTATUS:
			$cmdline="/etc/init.d/nagios status";
			break;
		case COMMAND_NAGIOSCORE_START:
			$cmdline="/etc/init.d/nagios start";
			// log it
			send_to_audit_log("cmdsubsys: User started Nagios Core",AUDITLOGTYPE_INFO);			
			break;
		case COMMAND_NAGIOSCORE_STOP:
			$cmdline="/etc/init.d/nagios stop";
			// log it
			send_to_audit_log("cmdsubsys: User stopped Nagios Core",AUDITLOGTYPE_INFO);			
			break;
		case COMMAND_NAGIOSCORE_RESTART:
			$cmdline="/etc/init.d/nagios restart";
			// log it
			send_to_audit_log("cmdsubsys: User restarted Nagios Core",AUDITLOGTYPE_INFO);			
			break;
		case COMMAND_NAGIOSCORE_RELOAD:
			$cmdline="/etc/init.d/nagios reload";
			// log it
			send_to_audit_log("cmdsubsys: User reloaded Nagios Core configuration",AUDITLOGTYPE_INFO);			
			break;
		case COMMAND_NAGIOSCORE_CHECKCONFIG:
			$cmdline="/etc/init.d/nagios checkconfig";
			break;
		// NDO2DB
		case COMMAND_NDO2DB_GETSTATUS:
			$cmdline="/etc/init.d/ndo2db status";
			break;
		case COMMAND_NDO2DB_START:
			$cmdline="/etc/init.d/ndo2db start";
			// log it
			send_to_audit_log("cmdsubsys: User started NDO2DB",AUDITLOGTYPE_INFO);			
			break;
		case COMMAND_NDO2DB_STOP:
			$cmdline="/etc/init.d/ndo2db stop";
			// log it
			send_to_audit_log("cmdsubsys: User stopped NDO2DB",AUDITLOGTYPE_INFO);			
			break;
		case COMMAND_NDO2DB_RESTART:
			$cmdline="/etc/init.d/ndo2db restart";
			// log it
			send_to_audit_log("cmdsubsys: User restarted NDO2DB",AUDITLOGTYPE_INFO);			
			break;
		case COMMAND_NDO2DB_RELOAD:
			$cmdline="/etc/init.d/ndo2db reload";
			// log it
			send_to_audit_log("cmdsubsys: User reloaded NDO2DB configuration",AUDITLOGTYPE_INFO);			
			break;
		// NPCD
		case COMMAND_NPCD_GETSTATUS:
			$cmdline="/etc/init.d/npcd status";
			break;
		case COMMAND_NPCD_START:
			$cmdline="/etc/init.d/npcd start";
			// log it
			send_to_audit_log("cmdsubsys: User started NPCD",AUDITLOGTYPE_INFO);			
			break;
		case COMMAND_NPCD_STOP:
			$cmdline="/etc/init.d/npcd stop";
			// log it
			send_to_audit_log("cmdsubsys: User stopped NPCD",AUDITLOGTYPE_INFO);			
			break;
		case COMMAND_NPCD_RESTART:
			$cmdline="/etc/init.d/npcd restart";
			// log it
			send_to_audit_log("cmdsubsys: User restarted NPCD",AUDITLOGTYPE_INFO);			
			break;
		case COMMAND_NPCD_RELOAD:
			$cmdline="/etc/init.d/npcd reload";
			// log it
			send_to_audit_log("cmdsubsys: User reloaded NPCD configuration",AUDITLOGTYPE_INFO);			
			break;
			
			
		case COMMAND_NAGIOSXI_SET_HTACCESS:
			$cmdarr=unserialize($command_data);	
			$cmdline=$cfg['htpasswd_path']." -b ".$cfg['htaccess_file']." ".$cmdarr["username"]." ".$cmdarr["password"];
			break;
        case COMMAND_NAGIOSXI_DEL_HTACCESS:
			$cmdarr=unserialize($command_data);	
			$cmdline=$cfg['htpasswd_path']." -D ".$cfg['htaccess_file']." ".$cmdarr["username"];
			break;

		case COMMAND_DELETE_CONFIGWIZARD:
			$dir=$command_data;	
			$dir=str_replace("..","",$dir);
			$dir=str_replace("/","",$dir);
			$dir=str_replace("\\","",$dir);
			if($dir=="")
				return COMMAND_RESULT_ERROR;
			$cmdline="rm -rf /usr/local/nagiosxi/html/includes/configwizards/".$dir;
			break;

		case COMMAND_INSTALL_CONFIGWIZARD:
			if($logging) {
				echo "INSTALLING CONFIGWIZARD...\n";
				echo "RAW COMMAND DATA: $command_data\n";
			}
			$file=$command_data;	
			$file=str_replace("..","",$file);
			$file=str_replace("/","",$file);
			$file=str_replace("\\","",$file);
			if($logging)
				echo "CONFIGWIZARD FILE: '".$file."'\n";
			if($file==""){
				echo "FILE ERROR!\n";
				return COMMAND_RESULT_ERROR;
				}
				
			// create a new temp directory for holding the unzipped wizard
			$tmpname=random_string(5);
			if($logging)
				echo "TMPNAME: $tmpname\n";
			$tmpdir="/usr/local/nagiosxi/tmp/".$tmpname;
			system("rm -rf ".$tmpdir);
			mkdir($tmpdir);
			
			// unzip wizard to temp directory
			$cmdline="cd ".$tmpdir." && unzip -o /usr/local/nagiosxi/tmp/configwizard-".$file;
			system($cmdline);
			
			// determine wizard directory/file name
			$cdir=system("ls -1 ".$tmpdir."/");
			$cname=$cdir;
			
			// make sure this is a config wizard
			$cmdline="grep register_configwizard ".$tmpdir."/".$cdir."/".$cname.".inc.php | wc -l";
			if($logging)
				echo "CMD=$cmdline";
			$out=system($cmdline,$rc);
			if($logging)
				echo "OUT=$out";
			if($out=="0"){
				// delete temp directory
				system("rm -rf ".$tmpdir);
				
				$output="Uploaded zip file is not a config wizard.";
				echo $output."\n";
				return COMMAND_RESULT_ERROR;
				}
			if($logging)	
				echo "Wizard looks ok...";
			
			// null-op
			$cmdline="/bin/true";
			
			// make new wizard directory (might exist already)
			@mkdir("/usr/local/nagiosxi/html/includes/configwizards/".$cname);
			
			// move wizard to production directory and delete temp directory
			$cmdline="chmod -R 755 ".$tmpdir." && chown -R nagios.nagios ".$tmpdir." && cp -rf ".$tmpdir."/".$cdir." /usr/local/nagiosxi/html/includes/configwizards/ && rm -rf ".$tmpdir;
			
			//$cmdline="cd /usr/local/nagiosxi/html/includes/configwizards && unzip -o /usr/local/nagiosxi/tmp/configwizard-".$file;
			$wizard_name=substr($file,0,strlen($file)-4);
			$post_func="install_configwizard";
			$post_func_args=array(
				"wizard_name" => $wizard_name,
				"wizard_dir" => "/usr/local/nagiosxi/html/includes/configwizards/".$wizard_name,
				);
			break;

		case COMMAND_PACKAGE_CONFIGWIZARD:
			$dir=$command_data;	
			$dir=str_replace("..","",$dir);
			$dir=str_replace("/","",$dir);
			$dir=str_replace("\\","",$dir);
			if($dir=="")
				return COMMAND_RESULT_ERROR;
			$cmdline="cd /usr/local/nagiosxi/html/includes/configwizards && zip -r /usr/local/nagiosxi/tmp/configwizard-".$dir.".zip ".$dir;
			break;

		case COMMAND_DELETE_DASHLET:
			$dir=$command_data;	
			$dir=str_replace("..","",$dir);
			$dir=str_replace("/","",$dir);
			$dir=str_replace("\\","",$dir);
			if($dir=="")
				return COMMAND_RESULT_ERROR;
			$cmdline="rm -rf /usr/local/nagiosxi/html/includes/dashlets/".$dir;
			break;

		case COMMAND_INSTALL_DASHLET:
			$file=$command_data;	
			$file=str_replace("..","",$file);
			$file=str_replace("/","",$file);
			$file=str_replace("\\","",$file);
			if($file=="")
				return COMMAND_RESULT_ERROR;

			// create a new temp directory for holding the unzipped dashlet
			$tmpname=random_string(5);
			if($logging)
				echo "TMPNAME: $tmpname\n";
			$tmpdir="/usr/local/nagiosxi/tmp/".$tmpname;
			system("rm -rf ".$tmpdir);
			mkdir($tmpdir);
			
			// unzip dashlet to temp directory
			$cmdline="cd ".$tmpdir." && unzip -o /usr/local/nagiosxi/tmp/dashlet-".$file;
			system($cmdline);
			
			// determine dashlet directory/file name
			$cdir=system("ls -1 ".$tmpdir."/");
			$cname=$cdir;
			
			// make sure this is a dashlet
			$isdashlet=true;

			// check for register_dashlet...
			$cmdline="grep register_dashlet ".$tmpdir."/".$cdir."/".$cname.".inc.php | wc -l";
			if($logging)
				echo "CMD=$cmdline";
			$out=system($cmdline,$rc);
			if($logging)
				echo "OUT=$out";		
			if($out=="0")
				$isdashlet=false;
			
			// check to make sure its not a component...
			$cmdline="grep register_component ".$tmpdir."/".$cdir."/".$cname.".inc.php | wc -l";
			if($logging)
				echo "CMD=$cmdline";
			$out=system($cmdline,$rc);
			if($logging)
				echo "OUT=$out";		
			if($out!="0")
				$isdashlet=false;

			if($isdashlet==false){
			
				// delete temp directory
				system("rm -rf ".$tmpdir);

				$output="Uploaded zip file is not a dashlet.";
				echo $output."\n";
				return COMMAND_RESULT_ERROR;
				}
			if($logging)	
				echo "Dashlet looks ok...";
			
			
			// make new dashlet directory (might exist already)
			@mkdir("/usr/local/nagiosxi/html/includes/dashlets/".$cname);
			
			// move dashlet to production directory and delete temp directory
			$cmdline="chmod -R 755 ".$tmpdir." && chown -R nagios.nagios ".$tmpdir." && cp -rf ".$tmpdir."/".$cdir." /usr/local/nagiosxi/html/includes/dashlets/ && rm -rf ".$tmpdir;

			//$cmdline="cd /usr/local/nagiosxi/html/includes/dashlets && unzip -o /usr/local/nagiosxi/tmp/dashlet-".$file;

			break;

		case COMMAND_PACKAGE_DASHLET:
			$dir=$command_data;	
			$dir=str_replace("..","",$dir);
			$dir=str_replace("/","",$dir);
			$dir=str_replace("\\","",$dir);
			if($dir=="")
				return COMMAND_RESULT_ERROR;
			$cmdline="cd /usr/local/nagiosxi/html/includes/dashlets && zip -r /usr/local/nagiosxi/tmp/dashlet-".$dir.".zip ".$dir;
			break;

		case COMMAND_DELETE_COMPONENT:
			$dir=$command_data;	
			$dir=str_replace("..","",$dir);
			$dir=str_replace("/","",$dir);
			$dir=str_replace("\\","",$dir);
			if($dir=="")
				return COMMAND_RESULT_ERROR;
			$cmdline="rm -rf /usr/local/nagiosxi/html/includes/components/".$dir;
			break;

		case COMMAND_INSTALL_COMPONENT:
			$file=$command_data;	
			$file=str_replace("..","",$file);
			$file=str_replace("/","",$file);
			$file=str_replace("\\","",$file);
			if($file=="")
				return COMMAND_RESULT_ERROR;

			// create a new temp directory for holding the unzipped component
			$tmpname=random_string(5);
			if($logging)
				echo "TMPNAME: $tmpname\n";
			$tmpdir="/usr/local/nagiosxi/tmp/".$tmpname;
			system("rm -rf ".$tmpdir);
			mkdir($tmpdir);
			
			// unzip component to temp directory
			$cmdline="cd ".$tmpdir." && unzip -o /usr/local/nagiosxi/tmp/component-".$file;
			system($cmdline);
			
			// determine component directory/file name
			$cdir=system("ls -1 ".$tmpdir."/");
			$cname=$cdir;
			
			// make sure this is a component
			$cmdline="grep register_component ".$tmpdir."/".$cdir."/".$cname.".inc.php | wc -l";
			if($logging)
				echo "CMD=$cmdline";
			$out=system($cmdline,$rc);
			if($logging)
				echo "OUT=$out";
			if($out=="0"){
			
				// delete temp directory
				system("rm -rf ".$tmpdir);

				$output="Uploaded zip file is not a component.";
				echo $output."\n";
				return COMMAND_RESULT_ERROR;
				}
				
			if($logging)	
				echo "Component looks ok...";
			
			// null-op
			$cmdline="/bin/true";
			
			// make new component directory (might exist already)
			@mkdir("/usr/local/nagiosxi/html/includes/components/".$cname);
			
			// move component to production directory and delete temp directory
			//added permissions fix to make sure all new components are executable
			$cmdline="chmod -R 755 ".$tmpdir." && chown -R nagios.nagios ".$tmpdir." && cp -rf ".$tmpdir."/".$cdir." /usr/local/nagiosxi/html/includes/components/ && rm -rf ".$tmpdir;

				
			$component_name=$cname;
			$post_func="install_component";
			$post_func_args=array(
				"component_name" => $component_name,
				"component_dir" => "/usr/local/nagiosxi/html/includes/components/".$component_name,
				);
				
			break;

		case COMMAND_PACKAGE_COMPONENT:
			$dir=$command_data;	
			$dir=str_replace("..","",$dir);
			$dir=str_replace("/","",$dir);
			$dir=str_replace("\\","",$dir);
			if($dir=="")
				return COMMAND_RESULT_ERROR;
			$cmdline="cd /usr/local/nagiosxi/html/includes/components && zip -r /usr/local/nagiosxi/tmp/component-".$dir.".zip ".$dir;
			break;

		case COMMAND_DELETE_CONFIGSNAPSHOT:
			$ts=$command_data;	
			$ts=str_replace("..","",$ts);
			$ts=str_replace("/","",$ts);
			$ts=str_replace("\\","",$ts);
			if($ts=="")
				return COMMAND_RESULT_ERROR;
			$cmdline="rm -rf ".$cfg['nom_checkpoints_dir']."errors/".$ts.".tar.gz";
			break;

		case COMMAND_RESTORE_CONFIGSNAPSHOT:
			$cmdline="/usr/local/nagiosxi/scripts/nom_restore_nagioscore_checkpoint_specific.sh ".$command_data;
			break;
        case COMMAND_RESTORE_NAGIOSQL_SNAPSHOT:
			$cmdline="/usr/local/nagiosxi/scripts/nagiosql_snapshot.sh ".$command_data;
			break;
            
		default:
			echo "INVALID COMMAND ($command)!\n";
			return COMMAND_RESULT_ERROR;
			break;
		}
	
	// we're running a script, so generate the command line to execute
	if($script_name!=""){
		if($script_data!="")
			$cmdline=sprintf("cd %s && ./%s %s",$script_dir,$script_name,$script_data);
		else
			$cmdline=sprintf("cd %s && ./%s",$script_dir,$script_name);
		}
		
	// run the system command
	echo "CMDLINE=$cmdline\n";
	$return_code=127;
	$output="";
	if($cmdline!="")
		$output=system($cmdline,$return_code);
	
	echo "OUTPUT=$output\n";
	echo "RETURNCODE=$return_code\n";
	
	// run the post function call
	if($return_code==0 && $post_func!="" && function_exists($post_func)){
		echo "RUNNING POST FUNCTION CALL: $post_func\n";
		$return_code=$post_func($post_func_args);
		echo "POST FUNCTION CALL RETURNCODE=$return_code\n";
		}
		
	//do callbacks
	$args=array(
		'command' => $command, 
		'command_data' => $command_data
		); 
	do_callbacks(CALLBACK_SUBSYS_GENERIC,$args); 	
	
	if($return_code!=0)
		return $return_code;		//changed from COMMAND_RESULT_ERROR -MG 8/17
	return COMMAND_RESULT_OK;
	}

?>
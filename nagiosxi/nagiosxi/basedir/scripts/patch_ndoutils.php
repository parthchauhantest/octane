#!/usr/bin/php -q
<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: patch_ndoutils.php 1251 2012-06-22 21:45:12Z mguthrie $

require_once(dirname(__FILE__).'/../html/config.inc.php');
require_once(dirname(__FILE__).'/../html/includes/utils.inc.php');

upgrade_ndoutils();

function upgrade_ndoutils(){
	global $db_tables;
	$current_patch_level=103;
	$optname="ndoutils_patch_level";
	
	echo "Patching NDOUtils...\n";

	// make database connections
	$dbok=db_connect_all();
	if($dbok==false){
		echo "ERROR CONNECTING TO DATABASES!\n";
		exit();
		}

	//$last_ndo=100;
	//set_option($optname,99);
	$last_ndo=get_option($optname);
	//echo "LAST: $last_ndo\n";
	
	if($last_ndo=="" || $last_ndo==null)
		$last_ndo=100;
		
	if($last_ndo==$current_patch_level){
		echo "NDOUtils already patched at level $current_patch_level\n";
		exit();
		}
		
	// apply patches sequentially
	for($current_level=$last_ndo;$current_level<=$current_patch_level;$current_level++){
	
		echo "Applying NDOUtils patch level $current_level\n";
		
		switch($current_level){
		
			// patches released in 2009R1.3F
			case 101:
				// log entries
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['logentries']."` ADD INDEX ( `logentry_time` ) ;");
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['logentries']."` ADD INDEX ( `logentry_data` ) ;");
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['logentries']."` ADD INDEX ( `instance_id` ) ;");
				// state history
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['statehistory']."` ADD INDEX ( `state_time` )  ;");
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['statehistory']."` ADD INDEX ( `object_id` )  ;");
				// notifications
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['notifications']."` ADD INDEX ( `start_time` ) ;");
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['notifications']."` ADD INDEX ( `object_id` )  ;");
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['notifications']."` ADD INDEX ( `instance_id` )  ;");
				// contact notifications
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['contactnotifications']."` ADD INDEX ( `notification_id` )  ;");
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['contactnotifications']."` ADD INDEX ( `contact_object_id` )  ;");
				// contact notification methods
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['contactnotificationmethods']."` ADD INDEX ( `contactnotification_id` )  ;");
				break;
			// 2001R1
			case 102:
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['objects']."` DROP INDEX `objecttype_id` ;");
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['objects']."` ADD INDEX ( `objecttype_id` ) ;");
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['objects']."` ADD INDEX ( `name1` ) ;");
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['objects']."` ADD INDEX ( `name2` ) ;");
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['objects']."` ADD INDEX ( `is_active` ) ;");

				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['timedeventqueue']."` ADD INDEX ( `queued_time` ) ;");
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['timedevents']."` ADD INDEX ( `queued_time` ) ;");			
				break;
			//2011R3.2 - Fixed duplicate table indexes inadvertently created by previous patches 	
			case 103:
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['logentries']."` DROP INDEX `logentry_time_2` ;");
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['logentries']."` DROP INDEX `logentry_data_2` ;");
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['logentries']."` DROP INDEX `instance_id_2` ;");
				// state history
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['statehistory']."` DROP INDEX `state_time_2`  ;");
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['statehistory']."` DROP INDEX `object_id_2`  ;");
				// notifications
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['notifications']."` DROP INDEX `start_time_2` ;");
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['notifications']."` DROP INDEX `object_id_2`  ;");
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['notifications']."` DROP INDEX `instance_id_2`  ;");
				// contact notifications
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['contactnotifications']."` DROP INDEX `notification_id_2`  ;");
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['contactnotifications']."` DROP INDEX `contact_object_id_2`  ;");
				// contact notification methods
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['contactnotificationmethods']."` DROP INDEX `contactnotification_id_2`  ;");
				//objects 
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['objects']."` DROP INDEX `objecttype_id_2` ;");
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['objects']."` DROP INDEX `name1_2` ;");
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['objects']."` DROP INDEX `name2_2` ;");
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['objects']."` DROP INDEX `is_active_2` ;");
				//timed events 
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['timedeventqueue']."` DROP INDEX `queued_time_2` ;");
				process_ndoutils_sql("ALTER TABLE `".$db_tables[DB_NDOUTILS]['timedevents']."` DROP INDEX `queued_time_2` ;");
				break; 
			default:
				break;
			}
		}
	
	set_option($optname,$current_patch_level);
	
	echo "NDOUtils patched to level $current_patch_level successfully.\n";
	}
	
function process_ndoutils_sql($sql){
	echo "\t".$sql."\n";
	$rs=exec_sql_query(DB_NDOUTILS,$sql);
	}

?>
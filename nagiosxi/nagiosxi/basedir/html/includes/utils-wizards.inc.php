<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// Development Started 03/22/2008
// $Id: utils-wizards.inc.php 75 2010-04-01 19:40:08Z egalstad $

//require_once(dirname(__FILE__).'/common.inc.php');

////////////////////////////////////////////////////////////////////////
// WIZARD FUNCTIONS
////////////////////////////////////////////////////////////////////////

function get_host_configwizard($hostname){
	global $db_tables;

	$wizardname="";

	// find the config wizard name for hostusing saved meta-data
	$keyname=get_configwizard_meta_key_name2($hostname);
	if($keyname!=""){

		$sql="SELECT * FROM ".$db_tables[DB_NAGIOSXI]["meta"]." WHERE metatype_id='".escape_sql_param(METATYPE_CONFIGWIZARD,DB_NAGIOSXI)."' AND keyname LIKE '%".escape_sql_param($keyname,DB_NAGIOSXI)."'";
		
		if(($rs=exec_sql_query(DB_NAGIOSXI,$sql))){
		
			// only find the first match
			if($rs->MoveFirst()){
			
				$dbkeyname=$rs->fields["keyname"];
				
				// get the wizard name from the key
				$pos=strpos($dbkeyname,$keyname);
				if($pos!==false){
					$wizardname=substr($dbkeyname,0,$pos);
					//$keyname.=" (".$dbkeyname." = POS:$pos = $wizardname), ";
					}
				}
			}
		}
	
	/*
	// get wizard from host custom variable
	$args=array(
		"host_name" => $hostname
		);
	$x=get_xml_custom_host_variable_status($args);
	if($x){
		foreach($x->customhostvarstatus as $cvs){
			foreach($cvs->customvars->customvar as $cv){
				//echo "HST CUSTOM VAR: name=[".$cv->name."]  value=[".$cv->value."]<BR>";
				if(!strcmp("XIWIZARD",strval($cv->name))){
					$wizardname=strval($cv->value);
					return $wizardname;
					}
				}
			}
		}
	*/
	
	return $wizardname;
	}

function get_service_configwizard($hostname,$servicename){
	global $db_tables;

	$wizardname="";
	
	// find the config wizard name for service using saved meta-data
	$keyname=get_configwizard_meta_key_name2($hostname,$servicename);
	if($keyname!=""){

		$sql="SELECT * FROM ".$db_tables[DB_NAGIOSXI]["meta"]." WHERE metatype_id='".escape_sql_param(METATYPE_CONFIGWIZARD,DB_NAGIOSXI)."' AND keyname LIKE '%".escape_sql_param($keyname,DB_NAGIOSXI)."'";
		
		if(($rs=exec_sql_query(DB_NAGIOSXI,$sql))){
		
			// only find the first match
			if($rs->MoveFirst()){
			
				$dbkeyname=$rs->fields["keyname"];
				
				// get the wizard name from the key
				$pos=strpos($dbkeyname,$keyname);
				if($pos!==false){
					$wizardname=substr($dbkeyname,0,$pos);
					//$keyname.=" (".$dbkeyname." = POS:$pos = $wizardname), ";
					}
				}
			}
		}
	
	/*
	// get wizard name from custom service variable
	$args=array(
		"host_name" => $hostname,
		"service_description" => $servicename
		);
	$x=get_xml_custom_service_variable_status($args);
	if($x){
		foreach($x->customservicevarstatus as $cvs){
			foreach($cvs->customvars->customvar as $cv){
				//echo "SVC CUSTOM VAR: name=[".$cv->name."]  value=[".$cv->value."]<BR>";
				if(!strcmp("XIWIZARD",strval($cv->name))){
					$wizardname=strval($cv->value);
					return $wizardname;
					}
				}
			}
		}
	*/
	
	// if no wizard was found, try the host
	if($wizardname=="")
		$wizardname=get_host_configwizard($hostname);
	
	return $wizardname;
	}

	

// generates a meta key name that can be used for saving/retrieving config wizard data for configured items
function get_configwizard_meta_key_name($wizardname, $hostname, $servicename=""){

	$keyname="";
	
	$keyname.=$wizardname;
	$keyname.=get_configwizard_meta_key_name2($hostname,$servicename);
	
	return $keyname;
	}
	
// generates the host/service portion of the meta key - used for saving and retreiving the config wizard data later
function get_configwizard_meta_key_name2($hostname, $servicename=""){

	$keyname="";
	
	$keyname.="__".$hostname;
	$keyname.="__".$servicename;
	
	return $keyname;
	}
	
// save config wizard data for later re-entrace
function save_configwizard_object_meta($wizardname, $hostname, $servicename, $meta_arr){

	// serialize the array
	$meta_ser=serialize($meta_arr);

	// save the data for possible later retrieval
	set_meta(METATYPE_CONFIGWIZARD,0,get_configwizard_meta_key_name($wizardname,$hostname,$servicename),$meta_ser);
	}
	
	
// retrieves config wizard data
function get_configwizard_object_meta($wizardname, $hostname, $servicename){

	$meta_arr=array();

	$meta_ser=get_meta(METATYPE_CONFIGWIZARD,0,get_configwizard_meta_key_name($wizardname,$hostname,$servicename));
	if($meta_ser!=null)
		$meta_arr=unserialize($meta_ser);
	
	return $meta_arr;
	}
	
	
// determines if config wizard data exists
function configwizard_object_meta_exists($wizardname, $hostname, $servicename){

	$meta_arr=array();

	$meta_ser=get_meta(METATYPE_CONFIGWIZARD,0,get_configwizard_meta_key_name($wizardname,$hostname,$servicename));
	if($meta_ser!=null)
		return true;
	
	return false;
	}
	
	
function delete_host_configwizard_meta($hostname){	

	$wizardname=get_host_configwizard($hostname);
	$keyname=$wizardname.get_configwizard_meta_key_name2($hostname);
	
	delete_meta(METATYPE_CONFIGWIZARD,0,$keyname);
	}

function delete_service_configwizard_meta($hostname,$servicename){	

	$wizardname=get_service_configwizard($hostname,$servicename);
	$keyname=$wizardname.get_configwizard_meta_key_name2($hostname,$servicename);
	
	delete_meta(METATYPE_CONFIGWIZARD,0,$keyname);
	}
	
?>
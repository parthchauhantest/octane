<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: handler-perms.inc.php 663 2011-06-07 18:41:32Z egalstad $

require_once(dirname(__FILE__).'/common.inc.php');


// INSTANCE PERMS *************************************************************************
function fetch_instanceperms(){
	global $DB;
	global $cfg;
	global $sqlquery;
	global $db_tables;
	global $request;

	// generate query
	$fieldmap=array(
		"instance_id" => $db_tables["ndoutils"]["instances"].".instance_id",
		"instance_name" => $db_tables["ndoutils"]["instances"].".instance_name",
		"instance_description" => $db_tables["ndoutils"]["instances"].".instance_description"
		);
	$instanceauthfields=array(
		"instance_id"
		);
	$args=array(
		"sql" => $sqlquery['GetInstances'],
		"fieldmap" => $fieldmap,
		"instanceauthfields" => $instanceauthfields,
		);
	$sql=generate_sql_query(DB_NDOUTILS,$args);

	$instance_perms=get_cached_instance_perms(0);

	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql)))
		handle_backend_db_error();
	else{
		output_backend_header();
		echo "<instancepermlist>\n";
		echo "  <recordcount>".$rs->RecordCount()."</recordcount>\n";
		
		if(!isset($request["totals"])){
			while(!$rs->EOF){

				$eperms=get_effective_instance_perms($rs->fields["instance_id"]);
				if($eperms==P_NONE){
					$rs->MoveNext();
					continue;
					}
				
				$perms=P_NONE;
				if(array_key_exists($rs->fields["instance_id"],$instance_perms))
					$perms=$instance_perms[$rs->fields["instance_id"]];

				echo "  <instance id='".db_field($rs,'instance_id')."'>\n";
				xml_field(2,'perms',$perms);
				xml_field(2,'perms_s',get_perm_string($perms));
				xml_field(2,'eperms',$eperms);
				xml_field(2,'eperms_s',get_perm_string($eperms));
				echo "  </instance>\n";

				$rs->MoveNext();
				}
			}
		echo "</instancepermlist>\n";
		}
	}



// OBJECT PERMS  *************************************************************************
function fetch_objectperms(){
	global $DB;
	global $cfg;
	global $sqlquery;
	global $db_tables;
	global $request;

	// generate query
	$fieldmap=array(
		"instance_id" => $db_tables["ndoutils"]["objects"].".instance_id",
		"object_id" => $db_tables["ndoutils"]["objects"].".object_id",
		"objecttype_id" => $db_tables["ndoutils"]["objects"].".objecttype_id",
		"name1" => $db_tables["ndoutils"]["objects"].".name1",
		"name2" => $db_tables["ndoutils"]["objects"].".name2",
		"is_active" => $db_tables["ndoutils"]["objects"].".is_active",
		);
	$instanceauthfields=array(
		"instance_id"
		);
	$args=array(
		"sql" => $sqlquery['GetObjects'],
		"fieldmap" => $fieldmap,
		"instanceauthfields" => $instanceauthfields,
		);
	$sql=generate_sql_query(DB_NDOUTILS,$args);
	
	$object_perms=get_cached_object_perms(0);
	$object_id_perms=$object_perms["0"];

	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql)))
		handle_backend_db_error();
	else{
		output_backend_header();
		echo "<objectpermlist>\n";
		echo "  <recordcount>".$rs->RecordCount()."</recordcount>\n";
		
		if(!isset($request["totals"])){
			while(!$rs->EOF){
			
				$eperms=get_effective_object_perms($rs->fields["object_id"]);
				if($eperms==P_NONE){
					$rs->MoveNext();
					continue;
					}

				$perms=P_NONE;
				if(array_key_exists($rs->fields["object_id"],$object_id_perms))
					$perms=$object_id_perms[$rs->fields["object_id"]];

				echo "  <object id='".db_field($rs,'object_id')."'>\n";
				xml_field(2,'perms',$perms);
				xml_field(2,'perms_s',get_perm_string($perms));
				xml_field(2,'eperms',$eperms);
				xml_field(2,'eperms_s',get_perm_string($eperms));
				echo "  </object>\n";

				$rs->MoveNext();
				}
			}
		echo "</objectpermlist>\n";
		}
	}

?>
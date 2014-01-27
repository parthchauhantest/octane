<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: queries.inc.php 1255 2012-06-23 02:50:10Z egalstad $

require_once(dirname(__FILE__).'/../includes/db.inc.php');
require_once(dirname(__FILE__).'/../includes/constants.inc.php');


//**********************************************************************************
//**
//** XI FRONTEND
//**
//**********************************************************************************

// USERS
$sqlquery['GetUsers']="SELECT * FROM ".$db_tables[DB_NAGIOSXI]['users']." WHERE TRUE";

//
$sqlquery['CheckXIInstall']="SELECT * FROM ".$db_tables[DB_NAGIOSXI]['options'];

// COMMANDS
$sqlquery['GetCommands']="SELECT * FROM ".$db_tables[DB_NAGIOSXI]['commands']." WHERE TRUE";


//**********************************************************************************
//**
//** AUDIT LOG
//**
//**********************************************************************************

// AUDIT LOG ENTRIES
$sqlquery['GetAuditLog']="SELECT * FROM ".$db_tables[DB_NAGIOSXI]['auditlog']." WHERE TRUE";

//**********************************************************************************
//**
//** MISC 
//**
//**********************************************************************************

// IS NDOUTILS INSTALLED?
$sqlquery['CheckNDOInstall']="SELECT * FROM ".$db_tables[DB_NDOUTILS]['dbversion'];

// NDOUTILS DB VERSION INFO
$sqlquery['GetNDODBVersionInfo']="SELECT * FROM ".$db_tables[DB_NDOUTILS]['dbversion']." WHERE TRUE";

// INSTANCE LIST
$sqlquery['GetInstances']="SELECT * FROM ".$db_tables[DB_NDOUTILS]['instances']." WHERE TRUE";

// CONN INFO
$sqlquery['GetConnInfo']="SELECT * FROM ".$db_tables[DB_NDOUTILS]['conninfo']." WHERE TRUE";



//**********************************************************************************
//**
//** OBJECTS
//**
//**********************************************************************************

// GENERIC OBJECTS
$sqlquery['GetObjects']="SELECT * FROM ".$db_tables[DB_NDOUTILS]['objects']." WHERE TRUE";

// CONTACTGROUP MEMBERSHIPS
$sqlquery['GetContactGroupMemberships']="SELECT 
".$db_tables[DB_NDOUTILS]['instances'].".instance_id
,".$db_tables[DB_NDOUTILS]['instances'].".instance_name
,".$db_tables[DB_NDOUTILS]['contactgroups'].".contactgroup_id
,".$db_tables[DB_NDOUTILS]['contactgroups'].".contactgroup_object_id
,obj1.name1 AS contactgroup_name
,".$db_tables[DB_NDOUTILS]['contactgroups'].".alias AS contactgroup_alias
,".$db_tables[DB_NDOUTILS]['contacts'].".contact_object_id 
,obj2.name1 AS contact_name
FROM ".$db_tables[DB_NDOUTILS]['contactgroups']."
INNER JOIN ".$db_tables[DB_NDOUTILS]['contactgroup_members']." ON ".$db_tables[DB_NDOUTILS]['contactgroups'].".contactgroup_id=".$db_tables[DB_NDOUTILS]['contactgroup_members'].".contactgroup_id 
INNER JOIN ".$db_tables[DB_NDOUTILS]['contacts']." ON ".$db_tables[DB_NDOUTILS]['contactgroup_members'].".contact_object_id=".$db_tables[DB_NDOUTILS]['contacts'].".contact_object_id
INNER JOIN ".$db_tables[DB_NDOUTILS]['objects']." AS obj1 ON ".$db_tables[DB_NDOUTILS]['contactgroups'].".contactgroup_object_id=obj1.object_id
INNER JOIN ".$db_tables[DB_NDOUTILS]['objects']." AS obj2 ON ".$db_tables[DB_NDOUTILS]['contactgroup_members'].".contact_object_id=obj2.object_id
INNER JOIN ".$db_tables[DB_NDOUTILS]['instances']." ON ".$db_tables[DB_NDOUTILS]['contactgroups'].".instance_id=".$db_tables[DB_NDOUTILS]['instances'].".instance_id WHERE TRUE";

// HOST CONTACT GROUPS
$sqlquery['GetHostContactGroups']="SELECT 
".$db_tables[DB_NDOUTILS]['hosts'].".host_object_id,
".$db_tables[DB_NDOUTILS]['hosts'].".instance_id,
".$db_tables[DB_NDOUTILS]['host_contactgroups'].".contactgroup_object_id
FROM ".$db_tables[DB_NDOUTILS]['hosts']."
LEFT JOIN ".$db_tables[DB_NDOUTILS]['host_contactgroups']." ON ".$db_tables[DB_NDOUTILS]['hosts'].".host_id=".$db_tables[DB_NDOUTILS]['host_contactgroups'].".host_id
LEFT JOIN ".$db_tables[DB_NDOUTILS]['contactgroups']." ON ".$db_tables[DB_NDOUTILS]['host_contactgroups'].".contactgroup_object_id=".$db_tables[DB_NDOUTILS]['contactgroups'].".contactgroup_object_id
WHERE TRUE ";

// HOST CONTACTS
$sqlquery['GetHostContacts']="SELECT 
".$db_tables[DB_NDOUTILS]['hosts'].".host_object_id,
".$db_tables[DB_NDOUTILS]['hosts'].".instance_id,
".$db_tables[DB_NDOUTILS]['host_contacts'].".contact_object_id
FROM ".$db_tables[DB_NDOUTILS]['hosts']."
LEFT JOIN ".$db_tables[DB_NDOUTILS]['host_contacts']." ON ".$db_tables[DB_NDOUTILS]['hosts'].".host_id=".$db_tables[DB_NDOUTILS]['host_contacts'].".host_id
LEFT JOIN ".$db_tables[DB_NDOUTILS]['contacts']." ON ".$db_tables[DB_NDOUTILS]['host_contacts'].".contact_object_id=".$db_tables[DB_NDOUTILS]['contacts'].".contact_object_id
WHERE TRUE ";

// HOST ESCALATION CONTACT GROUPS
$sqlquery['GetHostEscalationContactGroups']="SELECT 
".$db_tables[DB_NDOUTILS]['hostescalations'].".host_object_id,
".$db_tables[DB_NDOUTILS]['hostescalations'].".instance_id,
".$db_tables[DB_NDOUTILS]['hostescalation_contactgroups'].".contactgroup_object_id
FROM ".$db_tables[DB_NDOUTILS]['hostescalations']."
LEFT JOIN ".$db_tables[DB_NDOUTILS]['hostescalation_contactgroups']." ON ".$db_tables[DB_NDOUTILS]['hostescalations'].".hostescalation_id=".$db_tables[DB_NDOUTILS]['hostescalation_contactgroups'].".hostescalation_id
LEFT JOIN ".$db_tables[DB_NDOUTILS]['contactgroups']." ON ".$db_tables[DB_NDOUTILS]['hostescalation_contactgroups'].".contactgroup_object_id=".$db_tables[DB_NDOUTILS]['contactgroups'].".contactgroup_object_id
WHERE TRUE ";

// HOST ESCALATION CONTACTS
$sqlquery['GetHostEscalationContacts']="SELECT 
".$db_tables[DB_NDOUTILS]['hostescalations'].".host_object_id,
".$db_tables[DB_NDOUTILS]['hostescalations'].".instance_id,
".$db_tables[DB_NDOUTILS]['hostescalation_contacts'].".contact_object_id
FROM ".$db_tables[DB_NDOUTILS]['hostescalations']."
LEFT JOIN ".$db_tables[DB_NDOUTILS]['hostescalation_contacts']." ON ".$db_tables[DB_NDOUTILS]['hostescalations'].".hostescalation_id=".$db_tables[DB_NDOUTILS]['hostescalation_contacts'].".hostescalation_id
LEFT JOIN ".$db_tables[DB_NDOUTILS]['contacts']." ON ".$db_tables[DB_NDOUTILS]['hostescalation_contacts'].".contact_object_id=".$db_tables[DB_NDOUTILS]['contacts'].".contact_object_id
WHERE TRUE ";


// HOSTGROUP MEMBERS
$sqlquery['GetHostGroupMembers']="SELECT 
".$db_tables[DB_NDOUTILS]['hostgroups'].".hostgroup_id,
".$db_tables[DB_NDOUTILS]['hostgroups'].".instance_id,
".$db_tables[DB_NDOUTILS]['hostgroups'].".hostgroup_object_id,
".$db_tables[DB_NDOUTILS]['hostgroup_members'].".host_object_id,
obj1.name1 AS hostgroup_name, 
obj2.name1 AS host_name 
FROM ".$db_tables[DB_NDOUTILS]['hostgroups']." 
LEFT JOIN ".$db_tables[DB_NDOUTILS]['hostgroup_members']." ON ".$db_tables[DB_NDOUTILS]['hostgroups'].".hostgroup_id=".$db_tables[DB_NDOUTILS]['hostgroup_members'].".hostgroup_id
LEFT JOIN ".$db_tables[DB_NDOUTILS]['objects']." AS obj1 ON ".$db_tables[DB_NDOUTILS]['hostgroups'].".hostgroup_object_id=obj1.object_id
LEFT JOIN ".$db_tables[DB_NDOUTILS]['objects']." AS obj2 ON ".$db_tables[DB_NDOUTILS]['hostgroup_members'].".host_object_id=obj2.object_id 
WHERE TRUE";


// SERVICE CONTACT GROUPS
$sqlquery['GetServiceContactGroups']="SELECT 
".$db_tables[DB_NDOUTILS]['services'].".service_object_id,
".$db_tables[DB_NDOUTILS]['services'].".instance_id,
".$db_tables[DB_NDOUTILS]['service_contactgroups'].".contactgroup_object_id
FROM ".$db_tables[DB_NDOUTILS]['services']."
LEFT JOIN ".$db_tables[DB_NDOUTILS]['service_contactgroups']." ON ".$db_tables[DB_NDOUTILS]['services'].".service_id=".$db_tables[DB_NDOUTILS]['service_contactgroups'].".service_id
LEFT JOIN ".$db_tables[DB_NDOUTILS]['contactgroups']." ON ".$db_tables[DB_NDOUTILS]['service_contactgroups'].".contactgroup_object_id=".$db_tables[DB_NDOUTILS]['contactgroups'].".contactgroup_object_id
WHERE TRUE ";

// SERVICE CONTACTS
$sqlquery['GetServiceContacts']="SELECT 
".$db_tables[DB_NDOUTILS]['services'].".service_object_id,
".$db_tables[DB_NDOUTILS]['services'].".instance_id,
".$db_tables[DB_NDOUTILS]['service_contacts'].".contact_object_id
FROM ".$db_tables[DB_NDOUTILS]['services']."
LEFT JOIN ".$db_tables[DB_NDOUTILS]['service_contacts']." ON ".$db_tables[DB_NDOUTILS]['services'].".service_id=".$db_tables[DB_NDOUTILS]['service_contacts'].".service_id
LEFT JOIN ".$db_tables[DB_NDOUTILS]['contacts']." ON ".$db_tables[DB_NDOUTILS]['service_contacts'].".contact_object_id=".$db_tables[DB_NDOUTILS]['contacts'].".contact_object_id
WHERE TRUE ";

// SERVICE ESCALATION CONTACT GROUPS
$sqlquery['GetServiceEscalationContactGroups']="SELECT 
".$db_tables[DB_NDOUTILS]['serviceescalations'].".service_object_id,
".$db_tables[DB_NDOUTILS]['serviceescalations'].".instance_id,
".$db_tables[DB_NDOUTILS]['serviceescalation_contactgroups'].".contactgroup_object_id
FROM ".$db_tables[DB_NDOUTILS]['serviceescalations']."
LEFT JOIN ".$db_tables[DB_NDOUTILS]['serviceescalation_contactgroups']." ON ".$db_tables[DB_NDOUTILS]['serviceescalations'].".serviceescalation_id=".$db_tables[DB_NDOUTILS]['serviceescalation_contactgroups'].".serviceescalation_id
LEFT JOIN ".$db_tables[DB_NDOUTILS]['contactgroups']." ON ".$db_tables[DB_NDOUTILS]['serviceescalation_contactgroups'].".contactgroup_object_id=".$db_tables[DB_NDOUTILS]['contactgroups'].".contactgroup_object_id
WHERE TRUE ";

// SERVICE ESCALATION CONTACTS
$sqlquery['GetServiceEscalationContacts']="SELECT 
".$db_tables[DB_NDOUTILS]['serviceescalations'].".service_object_id,
".$db_tables[DB_NDOUTILS]['serviceescalations'].".instance_id,
".$db_tables[DB_NDOUTILS]['serviceescalation_contacts'].".contact_object_id
FROM ".$db_tables[DB_NDOUTILS]['serviceescalations']."
LEFT JOIN ".$db_tables[DB_NDOUTILS]['serviceescalation_contacts']." ON ".$db_tables[DB_NDOUTILS]['serviceescalations'].".serviceescalation_id=".$db_tables[DB_NDOUTILS]['serviceescalation_contacts'].".serviceescalation_id
LEFT JOIN ".$db_tables[DB_NDOUTILS]['contacts']." ON ".$db_tables[DB_NDOUTILS]['serviceescalation_contacts'].".contact_object_id=".$db_tables[DB_NDOUTILS]['contacts'].".contact_object_id
WHERE TRUE ";

// SERVICEGROUP MEMBERS
$sqlquery['GetServiceGroupMembers']="SELECT 
".$db_tables[DB_NDOUTILS]['servicegroups'].".servicegroup_id,
".$db_tables[DB_NDOUTILS]['servicegroups'].".instance_id,
".$db_tables[DB_NDOUTILS]['servicegroups'].".servicegroup_object_id,
".$db_tables[DB_NDOUTILS]['servicegroup_members'].".service_object_id,
obj1.name1 AS servicegroup_name, 
obj2.name1 AS host_name ,
obj2.name2 AS service_description 
FROM ".$db_tables[DB_NDOUTILS]['servicegroups']."
LEFT JOIN ".$db_tables[DB_NDOUTILS]['servicegroup_members']." ON ".$db_tables[DB_NDOUTILS]['servicegroups'].".servicegroup_id=".$db_tables[DB_NDOUTILS]['servicegroup_members'].".servicegroup_id 
INNER JOIN ".$db_tables[DB_NDOUTILS]['objects']." AS obj1 ON ".$db_tables[DB_NDOUTILS]['servicegroups'].".servicegroup_object_id=obj1.object_id
INNER JOIN ".$db_tables[DB_NDOUTILS]['objects']." AS obj2 ON ".$db_tables[DB_NDOUTILS]['servicegroup_members'].".service_object_id=obj2.object_id 
WHERE TRUE";


// SERVICEGROUP HOST MEMBERS
$sqlquery['GetServiceGroupHostMembers']="SELECT 
".$db_tables[DB_NDOUTILS]['servicegroups'].".servicegroup_id,
".$db_tables[DB_NDOUTILS]['servicegroups'].".instance_id,
".$db_tables[DB_NDOUTILS]['servicegroups'].".servicegroup_object_id,
".$db_tables[DB_NDOUTILS]['servicegroup_members'].".service_object_id,
obj1.name1 AS servicegroup_name, 
obj2.name1 AS host_name ,
obj2.name2 AS service_description,
".$db_tables[DB_NDOUTILS]['services'].".host_object_id 
FROM ".$db_tables[DB_NDOUTILS]['servicegroups']."
LEFT JOIN ".$db_tables[DB_NDOUTILS]['servicegroup_members']." ON ".$db_tables[DB_NDOUTILS]['servicegroups'].".servicegroup_id=".$db_tables[DB_NDOUTILS]['servicegroup_members'].".servicegroup_id 
INNER JOIN ".$db_tables[DB_NDOUTILS]['objects']." AS obj1 ON ".$db_tables[DB_NDOUTILS]['servicegroups'].".servicegroup_object_id=obj1.object_id
INNER JOIN ".$db_tables[DB_NDOUTILS]['objects']." AS obj2 ON ".$db_tables[DB_NDOUTILS]['servicegroup_members'].".service_object_id=obj2.object_id 
LEFT JOIN ".$db_tables[DB_NDOUTILS]['services']." ON ".$db_tables[DB_NDOUTILS]['servicegroup_members'].".service_object_id=".$db_tables[DB_NDOUTILS]['services'].".service_object_id 
WHERE TRUE";


// CONTACTGROUP MEMBERS
$sqlquery['GetContactGroupMembers']="SELECT 
".$db_tables[DB_NDOUTILS]['contactgroups'].".contactgroup_id,
".$db_tables[DB_NDOUTILS]['contactgroups'].".instance_id,
".$db_tables[DB_NDOUTILS]['contactgroups'].".contactgroup_object_id,
".$db_tables[DB_NDOUTILS]['contactgroup_members'].".contact_object_id,
obj1.name1 AS contactgroup_name, 
obj2.name1 AS contact_name 
FROM ".$db_tables[DB_NDOUTILS]['contactgroups']." 
LEFT JOIN ".$db_tables[DB_NDOUTILS]['contactgroup_members']." ON ".$db_tables[DB_NDOUTILS]['contactgroups'].".contactgroup_id=".$db_tables[DB_NDOUTILS]['contactgroup_members'].".contactgroup_id
INNER JOIN ".$db_tables[DB_NDOUTILS]['objects']." AS obj1 ON ".$db_tables[DB_NDOUTILS]['contactgroups'].".contactgroup_object_id=obj1.object_id
INNER JOIN ".$db_tables[DB_NDOUTILS]['objects']." AS obj2 ON ".$db_tables[DB_NDOUTILS]['contactgroup_members'].".contact_object_id=obj2.object_id 
WHERE TRUE";


// HOSTS
$sqlquery['GetHosts']="SELECT 
".$db_tables[DB_NDOUTILS]['hosts'].".*,
".$db_tables[DB_NDOUTILS]['objects'].".*
FROM ".$db_tables[DB_NDOUTILS]['hosts']."
 LEFT JOIN ".$db_tables[DB_NDOUTILS]['objects']." ON ".$db_tables[DB_NDOUTILS]['hosts'].".host_object_id=".$db_tables[DB_NDOUTILS]['objects'].".object_id
 WHERE TRUE";
 
 
 // PARENT HOSTS
 $sqlquery['GetParentHosts']="SELECT 
".$db_tables[DB_NDOUTILS]['host_parenthosts'].".*,
".$db_tables[DB_NDOUTILS]['hosts'].".host_object_id,
obj1.name1 AS parent_host_name,
obj2.name1 AS host_name
FROM ".$db_tables[DB_NDOUTILS]['host_parenthosts']."
LEFT JOIN ".$db_tables[DB_NDOUTILS]['objects']." AS obj1 ON ".$db_tables[DB_NDOUTILS]['host_parenthosts'].".parent_host_object_id=obj1.object_id
LEFT JOIN ".$db_tables[DB_NDOUTILS]['hosts']." ON nagios_host_parenthosts.host_id=".$db_tables[DB_NDOUTILS]['hosts'].".host_id
LEFT JOIN ".$db_tables[DB_NDOUTILS]['objects']." AS obj2 ON
".$db_tables[DB_NDOUTILS]['hosts'].".host_object_id=obj2.object_id
WHERE TRUE";


// SERVICES
$sqlquery['GetServices']="SELECT 
".$db_tables[DB_NDOUTILS]['services'].".*,
".$db_tables[DB_NDOUTILS]['objects'].".*
 FROM ".$db_tables[DB_NDOUTILS]['services']."
 LEFT JOIN ".$db_tables[DB_NDOUTILS]['objects']." ON ".$db_tables[DB_NDOUTILS]['services'].".service_object_id=".$db_tables[DB_NDOUTILS]['objects'].".object_id
 WHERE TRUE";

// CONTACTS
$sqlquery['GetContacts']="SELECT
".$db_tables[DB_NDOUTILS]['contacts'].".*,
".$db_tables[DB_NDOUTILS]['objects'].".*
FROM ".$db_tables[DB_NDOUTILS]['contacts']."
LEFT JOIN ".$db_tables[DB_NDOUTILS]['objects']." ON ".$db_tables[DB_NDOUTILS]['contacts'].".contact_object_id=".$db_tables[DB_NDOUTILS]['objects'].".object_id
WHERE TRUE";

// HOSTGROUPS
$sqlquery['GetHostGroups']="SELECT 
".$db_tables[DB_NDOUTILS]['hostgroups'].".*,
".$db_tables[DB_NDOUTILS]['objects'].".*
FROM ".$db_tables[DB_NDOUTILS]['hostgroups']." 
INNER JOIN ".$db_tables[DB_NDOUTILS]['objects']." ON ".$db_tables[DB_NDOUTILS]['hostgroups'].".hostgroup_object_id=".$db_tables[DB_NDOUTILS]['objects'].".object_id
WHERE TRUE";

// SERVICEGROUPS
$sqlquery['GetServiceGroups']="SELECT 
".$db_tables[DB_NDOUTILS]['servicegroups'].".*,
".$db_tables[DB_NDOUTILS]['objects'].".*
FROM ".$db_tables[DB_NDOUTILS]['servicegroups']." 
INNER JOIN ".$db_tables[DB_NDOUTILS]['objects']." ON ".$db_tables[DB_NDOUTILS]['servicegroups'].".servicegroup_object_id=".$db_tables[DB_NDOUTILS]['objects'].".object_id
WHERE TRUE";

// CONTACTGROUPS
$sqlquery['GetContactGroups']="SELECT 
".$db_tables[DB_NDOUTILS]['contactgroups'].".*,
".$db_tables[DB_NDOUTILS]['objects'].".*
FROM ".$db_tables[DB_NDOUTILS]['contactgroups']." 
INNER JOIN ".$db_tables[DB_NDOUTILS]['objects']." ON ".$db_tables[DB_NDOUTILS]['contactgroups'].".contactgroup_object_id=".$db_tables[DB_NDOUTILS]['objects'].".object_id
WHERE TRUE";



//**********************************************************************************
//**
//** HISTORICAL STATUS INFO
//**
//**********************************************************************************

/* all host and service objects: 

SELECT 
o.object_id AS objid, o.objecttype_id, o.name1, o.name2, s.object_id, s.state_time, s.state, s.state_change, s.state_type, s.last_state, s.last_hard_state, s.output
FROM (
	SELECT nagios_statehistory.object_id, max(state_time) as thetime
	FROM nagios_statehistory
	WHERE state_time < '2010-11-02'
	GROUP BY object_id

) AS x 
INNER JOIN nagios_statehistory AS s ON s.object_id = x.object_id and s.state_time = x.thetime
RIGHT JOIN nagios_objects AS o ON s.object_id=o.object_id
WHERE o.objecttype_id IN ('1','2')

*/


$sqlquery['GetHistoricalStatus']="
SELECT 
o.object_id, o.objecttype_id, o.name1 AS host_name, o.name2 AS service_description, o.is_active, s.instance_id, s.object_id AS objid, s.state_time, s.state, s.state_change, s.state_type, s.last_state, s.last_hard_state, s.output
FROM (
	SELECT ".$db_tables[DB_NDOUTILS]["statehistory"].".object_id, max(state_time) as thetime
	FROM ".$db_tables[DB_NDOUTILS]["statehistory"]."
	WHERE state_time <= FROM_UNIXTIME(STATUSTIME)
	GROUP BY object_id

) AS x 
INNER JOIN ".$db_tables[DB_NDOUTILS]["statehistory"]." AS s ON s.object_id = x.object_id and s.state_time = x.thetime
RIGHT JOIN ".$db_tables[DB_NDOUTILS]["objects"]." AS o ON s.object_id=o.object_id
WHERE o.objecttype_id IN ('1','2')
";


//**********************************************************************************
//**
//** CURRENT STATUS INFO
//**
//**********************************************************************************


// PROGRAM/DATASOURCE STATUS
$sqlquery['GetProgramStatus']="SELECT ".$db_tables[DB_NDOUTILS]['instances'].".instance_id AS datasource_id, ".$db_tables[DB_NDOUTILS]['instances'].".instance_name,  (TIMESTAMPDIFF(SECOND,".$db_tables[DB_NDOUTILS]["programstatus"].".program_start_time,NOW())) AS program_run_time, ".$db_tables[DB_NDOUTILS]['programstatus'].".* FROM ".$db_tables[DB_NDOUTILS]['instances']."  LEFT JOIN ".$db_tables[DB_NDOUTILS]['programstatus']." on ".$db_tables[DB_NDOUTILS]['instances'].".instance_id=".$db_tables[DB_NDOUTILS]['programstatus'].".instance_id WHERE TRUE";


// CONTACT STATUS
$sqlquery['GetContactStatus']="SELECT ".$db_tables[DB_NDOUTILS]['instances'].".instance_id, ".$db_tables[DB_NDOUTILS]['instances'].".instance_name, ".$db_tables[DB_NDOUTILS]['contactstatus'].".contact_object_id, obj1.name1 AS contact_name, ".$db_tables[DB_NDOUTILS]['contacts'].".alias AS contact_alias, ".$db_tables[DB_NDOUTILS]['contactstatus'].".* FROM `".$db_tables[DB_NDOUTILS]['contactstatus']."` LEFT JOIN ".$db_tables[DB_NDOUTILS]['objects']." as obj1 ON ".$db_tables[DB_NDOUTILS]['contactstatus'].".contact_object_id=obj1.object_id LEFT JOIN ".$db_tables[DB_NDOUTILS]['contacts']." ON ".$db_tables[DB_NDOUTILS]['contactstatus'].".contact_object_id=".$db_tables[DB_NDOUTILS]['contacts'].".contact_object_id LEFT JOIN ".$db_tables[DB_NDOUTILS]['instances']." ON ".$db_tables[DB_NDOUTILS]['contactstatus'].".instance_id=".$db_tables[DB_NDOUTILS]['instances'].".instance_id WHERE TRUE";

// CUSTOM CONTACT VARIABLE STATUS
$sqlquery['GetCustomContactVariableStatus']="SELECT ".$db_tables[DB_NDOUTILS]['instances'].".instance_id, obj1.object_id, obj1.objecttype_id, obj1.name1 AS contact_name, ".$db_tables[DB_NDOUTILS]['contacts'].".alias AS contact_alias, ".$db_tables[DB_NDOUTILS]['customvariablestatus'].".* FROM ".$db_tables[DB_NDOUTILS]['customvariablestatus']." LEFT JOIN ".$db_tables[DB_NDOUTILS]['objects']." as obj1 ON ".$db_tables[DB_NDOUTILS]['customvariablestatus'].".object_id=obj1.object_id LEFT JOIN ".$db_tables[DB_NDOUTILS]['contacts']." ON ".$db_tables[DB_NDOUTILS]['customvariablestatus'].".object_id=".$db_tables[DB_NDOUTILS]['contacts'].".contact_object_id LEFT JOIN ".$db_tables[DB_NDOUTILS]['instances']." ON obj1.instance_id=".$db_tables[DB_NDOUTILS]['instances'].".instance_id WHERE obj1.objecttype_id='10'";

// HOST STATUS
$sqlquery['GetHostStatus']="SELECT 
".$db_tables[DB_NDOUTILS]['hoststatus'].".host_object_id, 
obj1.name1 AS host_name, 
".$db_tables[DB_NDOUTILS]['hosts'].".alias as host_alias, 
".$db_tables[DB_NDOUTILS]['hosts'].".display_name, 
".$db_tables[DB_NDOUTILS]['hosts'].".icon_image,
".$db_tables[DB_NDOUTILS]['hosts'].".icon_image_alt,
".$db_tables[DB_NDOUTILS]['hoststatus'].".*
FROM ".$db_tables[DB_NDOUTILS]['hoststatus']." 
LEFT JOIN ".$db_tables[DB_NDOUTILS]['objects']." as obj1 ON ".$db_tables[DB_NDOUTILS]['hoststatus'].".host_object_id=obj1.object_id 
LEFT JOIN ".$db_tables[DB_NDOUTILS]['hosts']." ON ".$db_tables[DB_NDOUTILS]['hoststatus'].".host_object_id=".$db_tables[DB_NDOUTILS]['hosts'].".host_object_id 
WHERE TRUE";

// CUSTOM HOST VARIABLE STATUS
$sqlquery['GetCustomHostVariableStatus']="SELECT ".$db_tables[DB_NDOUTILS]['instances'].".instance_id, obj1.object_id, obj1.objecttype_id, obj1.name1 AS host_name, ".$db_tables[DB_NDOUTILS]['hosts'].".alias AS host_alias, ".$db_tables[DB_NDOUTILS]['hosts'].".display_name, ".$db_tables[DB_NDOUTILS]['customvariablestatus'].".* FROM ".$db_tables[DB_NDOUTILS]['customvariablestatus']." LEFT JOIN ".$db_tables[DB_NDOUTILS]['objects']." as obj1 ON ".$db_tables[DB_NDOUTILS]['customvariablestatus'].".object_id=obj1.object_id LEFT JOIN ".$db_tables[DB_NDOUTILS]['hosts']." ON ".$db_tables[DB_NDOUTILS]['customvariablestatus'].".object_id=".$db_tables[DB_NDOUTILS]['hosts'].".host_object_id LEFT JOIN ".$db_tables[DB_NDOUTILS]['instances']." ON obj1.instance_id=".$db_tables[DB_NDOUTILS]['instances'].".instance_id WHERE obj1.objecttype_id='1'";

// SERVICE AND HOST STATUS (COMBINED VIEW)
$sqlquery['GetServiceStatusWithHostStatus']="SELECT 
obj1.name1 AS host_name, 
obj1.name2 AS service_description, 
".$db_tables[DB_NDOUTILS]['services'].".display_name, 
".$db_tables[DB_NDOUTILS]['hosts'].".display_name AS host_display_name,
".$db_tables[DB_NDOUTILS]['services'].".host_object_id AS host_object_id,
".$db_tables[DB_NDOUTILS]['servicestatus'].".*, 

".$db_tables[DB_NDOUTILS]['services'].".icon_image,
".$db_tables[DB_NDOUTILS]['services'].".icon_image_alt,

".$db_tables[DB_NDOUTILS]['hosts'].".address AS host_address,
".$db_tables[DB_NDOUTILS]['hosts'].".icon_image AS host_icon_image,
".$db_tables[DB_NDOUTILS]['hosts'].".icon_image_alt AS host_icon_image_alt,

".$db_tables[DB_NDOUTILS]['hoststatus'].".status_update_time AS host_status_update_time,
".$db_tables[DB_NDOUTILS]['hoststatus'].".output AS host_output,
".$db_tables[DB_NDOUTILS]['hoststatus'].".perfdata AS host_perfdata,
".$db_tables[DB_NDOUTILS]['hoststatus'].".current_state AS host_current_state,
".$db_tables[DB_NDOUTILS]['hoststatus'].".has_been_checked AS host_has_been_checked,
".$db_tables[DB_NDOUTILS]['hoststatus'].".should_be_scheduled AS host_should_be_scheduled,
".$db_tables[DB_NDOUTILS]['hoststatus'].".current_check_attempt AS host_current_check_attempt,
".$db_tables[DB_NDOUTILS]['hoststatus'].".max_check_attempts AS host_max_check_attempts,
".$db_tables[DB_NDOUTILS]['hoststatus'].".last_check AS host_last_check,
".$db_tables[DB_NDOUTILS]['hoststatus'].".next_check AS host_next_check,
".$db_tables[DB_NDOUTILS]['hoststatus'].".check_type AS host_check_type,
".$db_tables[DB_NDOUTILS]['hoststatus'].".last_state_change AS host_last_state_change,
".$db_tables[DB_NDOUTILS]['hoststatus'].".last_hard_state_change AS host_last_hard_state_change,
".$db_tables[DB_NDOUTILS]['hoststatus'].".last_hard_state AS host_last_hard_state,
".$db_tables[DB_NDOUTILS]['hoststatus'].".last_time_up AS host_last_time_up,
".$db_tables[DB_NDOUTILS]['hoststatus'].".last_time_down AS host_last_time_down,
".$db_tables[DB_NDOUTILS]['hoststatus'].".last_time_unreachable AS host_last_time_unreachable,
".$db_tables[DB_NDOUTILS]['hoststatus'].".state_type AS host_state_type,
".$db_tables[DB_NDOUTILS]['hoststatus'].".last_notification AS host_last_notification,
".$db_tables[DB_NDOUTILS]['hoststatus'].".next_notification AS host_next_notification,
".$db_tables[DB_NDOUTILS]['hoststatus'].".no_more_notifications AS host_no_more_notifications,
".$db_tables[DB_NDOUTILS]['hoststatus'].".notifications_enabled AS host_notifications_enabled,
".$db_tables[DB_NDOUTILS]['hoststatus'].".problem_has_been_acknowledged AS host_problem_has_been_acknowledged,
".$db_tables[DB_NDOUTILS]['hoststatus'].".acknowledgement_type AS host_acknowledgement_type,
".$db_tables[DB_NDOUTILS]['hoststatus'].".current_notification_number AS host_current_notification_number,
".$db_tables[DB_NDOUTILS]['hoststatus'].".passive_checks_enabled AS host_passive_checks_enabled,
".$db_tables[DB_NDOUTILS]['hoststatus'].".active_checks_enabled AS host_active_checks_enabled,
".$db_tables[DB_NDOUTILS]['hoststatus'].".event_handler_enabled AS host_event_handler_enabled,
".$db_tables[DB_NDOUTILS]['hoststatus'].".flap_detection_enabled AS host_flap_detection_enabled,
".$db_tables[DB_NDOUTILS]['hoststatus'].".is_flapping AS host_is_flapping,
".$db_tables[DB_NDOUTILS]['hoststatus'].".percent_state_change AS host_percent_state_change,
".$db_tables[DB_NDOUTILS]['hoststatus'].".latency AS host_latency,
".$db_tables[DB_NDOUTILS]['hoststatus'].".execution_time AS host_execution_time,
".$db_tables[DB_NDOUTILS]['hoststatus'].".scheduled_downtime_depth AS host_scheduled_downtime_depth,
".$db_tables[DB_NDOUTILS]['hoststatus'].".failure_prediction_enabled AS host_failure_prediction_enabled,
".$db_tables[DB_NDOUTILS]['hoststatus'].".process_performance_data AS host_process_performance_data,
".$db_tables[DB_NDOUTILS]['hoststatus'].".obsess_over_host,
".$db_tables[DB_NDOUTILS]['hoststatus'].".modified_host_attributes,
".$db_tables[DB_NDOUTILS]['hoststatus'].".event_handler AS host_event_handler,
".$db_tables[DB_NDOUTILS]['hoststatus'].".check_command AS host_check_command,
".$db_tables[DB_NDOUTILS]['hoststatus'].".normal_check_interval AS host_normal_check_interval,
".$db_tables[DB_NDOUTILS]['hoststatus'].".retry_check_interval AS host_retry_check_interval,
".$db_tables[DB_NDOUTILS]['hoststatus'].".check_timeperiod_object_id AS host_check_timeperiod_object_id


FROM ".$db_tables[DB_NDOUTILS]['servicestatus']." 
LEFT JOIN ".$db_tables[DB_NDOUTILS]['objects']." as obj1 ON ".$db_tables[DB_NDOUTILS]['servicestatus'].".service_object_id=obj1.object_id 
LEFT JOIN ".$db_tables[DB_NDOUTILS]['services']." ON ".$db_tables[DB_NDOUTILS]['servicestatus'].".service_object_id=".$db_tables[DB_NDOUTILS]['services'].".service_object_id 
LEFT JOIN ".$db_tables[DB_NDOUTILS]['hosts']." ON ".$db_tables[DB_NDOUTILS]['services'].".host_object_id=".$db_tables[DB_NDOUTILS]['hosts'].".host_object_id 
LEFT JOIN ".$db_tables[DB_NDOUTILS]['hoststatus']." ON ".$db_tables[DB_NDOUTILS]['hosts'].".host_object_id=".$db_tables[DB_NDOUTILS]['hoststatus'].".host_object_id

WHERE TRUE";

// SERVICE STATUS
/*
$sqlquery['GetServiceStatus']="SELECT 
obj1.name1 AS host_name, 
obj1.name2 AS service_description, 
".$db_tables[DB_NDOUTILS]['services'].".display_name, 
".$db_tables[DB_NDOUTILS]['services'].".host_object_id AS host_object_id,
".$db_tables[DB_NDOUTILS]['services'].".icon_image,
".$db_tables[DB_NDOUTILS]['services'].".icon_image_alt,
".$db_tables[DB_NDOUTILS]['servicestatus'].".* 
FROM ".$db_tables[DB_NDOUTILS]['services']." 
LEFT JOIN ".$db_tables[DB_NDOUTILS]['servicestatus']." ON ".$db_tables[DB_NDOUTILS]['servicestatus'].".service_object_id=".$db_tables[DB_NDOUTILS]['services'].".service_object_id 
LEFT JOIN ".$db_tables[DB_NDOUTILS]['objects']." as obj1 ON ".$db_tables[DB_NDOUTILS]['services'].".service_object_id=obj1.object_id 
LEFT JOIN ".$db_tables[DB_NDOUTILS]['hosts']." ON ".$db_tables[DB_NDOUTILS]['services'].".host_object_id=".$db_tables[DB_NDOUTILS]['hosts'].".host_object_id 
WHERE obj1.is_active='1'";
*/
$sqlquery['GetServiceStatus']="SELECT 
obj1.name1 AS host_name, 
obj1.name2 AS service_description, 
".$db_tables[DB_NDOUTILS]['services'].".display_name, 
".$db_tables[DB_NDOUTILS]['services'].".host_object_id AS host_object_id,
".$db_tables[DB_NDOUTILS]['services'].".icon_image,
".$db_tables[DB_NDOUTILS]['services'].".icon_image_alt,
".$db_tables[DB_NDOUTILS]['servicestatus'].".* 
FROM ".$db_tables[DB_NDOUTILS]['servicestatus']." 
LEFT JOIN ".$db_tables[DB_NDOUTILS]['objects']." as obj1 ON ".$db_tables[DB_NDOUTILS]['servicestatus'].".service_object_id=obj1.object_id 
LEFT JOIN ".$db_tables[DB_NDOUTILS]['services']." ON ".$db_tables[DB_NDOUTILS]['servicestatus'].".service_object_id=".$db_tables[DB_NDOUTILS]['services'].".service_object_id 
LEFT JOIN ".$db_tables[DB_NDOUTILS]['hosts']." ON ".$db_tables[DB_NDOUTILS]['services'].".host_object_id=".$db_tables[DB_NDOUTILS]['hosts'].".host_object_id 
WHERE TRUE";

// CUSTOM SERVICE VARIABLE STATUS
$sqlquery['GetCustomServiceVariableStatus']="SELECT ".$db_tables[DB_NDOUTILS]['instances'].".instance_id, obj1.object_id, obj1.objecttype_id, obj1.name1 AS host_name, obj1.name2 AS service_description, ".$db_tables[DB_NDOUTILS]['services'].".display_name, ".$db_tables[DB_NDOUTILS]['customvariablestatus'].".* FROM ".$db_tables[DB_NDOUTILS]['customvariablestatus']." LEFT JOIN ".$db_tables[DB_NDOUTILS]['objects']." as obj1 ON ".$db_tables[DB_NDOUTILS]['customvariablestatus'].".object_id=obj1.object_id LEFT JOIN ".$db_tables[DB_NDOUTILS]['services']." ON ".$db_tables[DB_NDOUTILS]['customvariablestatus'].".object_id=".$db_tables[DB_NDOUTILS]['services'].".service_object_id LEFT JOIN ".$db_tables[DB_NDOUTILS]['instances']." ON obj1.instance_id=".$db_tables[DB_NDOUTILS]['instances'].".instance_id WHERE obj1.objecttype_id='2'";

// TIMED EVENT QUEUE
$sqlquery['GetTimedEventQueue']="SELECT ".$db_tables[DB_NDOUTILS]['instances'].".instance_id, ".$db_tables[DB_NDOUTILS]['instances'].".instance_name, ".$db_tables[DB_NDOUTILS]['timedeventqueue'].".event_type, ".$db_tables[DB_NDOUTILS]['timedeventqueue'].".timedeventqueue_id, ".$db_tables[DB_NDOUTILS]['timedeventqueue'].".queued_time, ".$db_tables[DB_NDOUTILS]['timedeventqueue'].".queued_time_usec, ".$db_tables[DB_NDOUTILS]['timedeventqueue'].".scheduled_time, ".$db_tables[DB_NDOUTILS]['timedeventqueue'].".recurring_event, obj1.objecttype_id, ".$db_tables[DB_NDOUTILS]['timedeventqueue'].".object_id, obj1.name1 AS host_name, obj1.name2 AS service_description FROM `".$db_tables[DB_NDOUTILS]['timedeventqueue']."` LEFT JOIN ".$db_tables[DB_NDOUTILS]['objects']." as obj1 ON ".$db_tables[DB_NDOUTILS]['timedeventqueue'].".object_id=obj1.object_id LEFT JOIN ".$db_tables[DB_NDOUTILS]['instances']." ON ".$db_tables[DB_NDOUTILS]['timedeventqueue'].".instance_id=".$db_tables[DB_NDOUTILS]['instances'].".instance_id WHERE TRUE";

// SCHEDULED DOWNTIME
$sqlquery['GetScheduledDowntime']="SELECT ".$db_tables[DB_NDOUTILS]['instances'].".instance_id, ".$db_tables[DB_NDOUTILS]['instances'].".instance_name, ".$db_tables[DB_NDOUTILS]['scheduleddowntime'].".object_id, obj1.objecttype_id, obj1.name1 AS host_name ,obj1.name2 AS service_description, ".$db_tables[DB_NDOUTILS]['scheduleddowntime'].".* FROM `".$db_tables[DB_NDOUTILS]['scheduleddowntime']."` LEFT JOIN ".$db_tables[DB_NDOUTILS]['objects']." as obj1 ON ".$db_tables[DB_NDOUTILS]['scheduleddowntime'].".object_id=obj1.object_id LEFT JOIN ".$db_tables[DB_NDOUTILS]['instances']." ON ".$db_tables[DB_NDOUTILS]['scheduleddowntime'].".instance_id=".$db_tables[DB_NDOUTILS]['instances'].".instance_id WHERE TRUE";

// COMMENTS
$sqlquery['GetComments']="SELECT ".$db_tables[DB_NDOUTILS]['instances'].".instance_id, ".$db_tables[DB_NDOUTILS]['comments'].".object_id, obj1.objecttype_id, obj1.name1 AS host_name, obj1.name2 AS service_description, ".$db_tables[DB_NDOUTILS]['comments'].".* FROM ".$db_tables[DB_NDOUTILS]['comments']." LEFT JOIN ".$db_tables[DB_NDOUTILS]['objects']." as obj1 ON ".$db_tables[DB_NDOUTILS]['comments'].".object_id=obj1.object_id LEFT JOIN ".$db_tables[DB_NDOUTILS]['instances']." ON ".$db_tables[DB_NDOUTILS]['comments'].".instance_id=".$db_tables[DB_NDOUTILS]['instances'].".instance_id WHERE TRUE";





//**********************************************************************************
//**
//** HISTORICAL INFO
//**
//**********************************************************************************

// LOG ENTRIES
$sqlquery['GetLogEntries']="SELECT ".$db_tables[DB_NDOUTILS]['logentries'].".* FROM ".$db_tables[DB_NDOUTILS]['logentries']." WHERE TRUE";

// EXTERNAL COMMANDS

// TIMED EVENTS

// SYSTEM COMMANDS

// STATE HISTORY
$sqlquery['GetStateHistory']="SELECT 
obj1.objecttype_id as objecttype_id,
obj1.name1 AS host_name, 
obj1.name2 AS service_description, 
".$db_tables[DB_NDOUTILS]['statehistory'].".* 
FROM ".$db_tables[DB_NDOUTILS]['statehistory']." 
LEFT JOIN ".$db_tables[DB_NDOUTILS]['objects']." as obj1 ON ".$db_tables[DB_NDOUTILS]['statehistory'].".object_id=obj1.object_id 
WHERE TRUE";


// TOP ALERT PRODUCERS
// NOTE: A 'GROUP BY' STATEMENT GETS ADDED LATER...
$sqlquery['GetTopAlertProducers']="SELECT 
COUNT(".$db_tables[DB_NDOUTILS]['statehistory'].".object_id) AS total_alerts,
obj1.objecttype_id as objecttype_id,
obj1.name1 AS host_name, 
obj1.name2 AS service_description, 
".$db_tables[DB_NDOUTILS]['statehistory'].".* 
FROM ".$db_tables[DB_NDOUTILS]['statehistory']." 
LEFT JOIN ".$db_tables[DB_NDOUTILS]['objects']." as obj1 ON ".$db_tables[DB_NDOUTILS]['statehistory'].".object_id=obj1.object_id 
WHERE TRUE";

// HISTOGRAM
// NOTE: A 'GROUP BY' STATEMENT GETS ADDED LATER...
//"SELECT 
//COUNT(".$db_tables[DB_NDOUTILS]['statehistory'].".object_id) AS total_alerts,
$sqlquery['GetHistogram']="
obj1.objecttype_id as objecttype_id,
obj1.name1 AS host_name, 
obj1.name2 AS service_description, 
DATE_FORMAT(".$db_tables[DB_NDOUTILS]['statehistory'].".state_time,'%c') AS month,
DATE_FORMAT(".$db_tables[DB_NDOUTILS]['statehistory'].".state_time,'%d') AS day_of_month,
DATE_FORMAT(".$db_tables[DB_NDOUTILS]['statehistory'].".state_time,'%w') AS day_of_week,
DATE_FORMAT(".$db_tables[DB_NDOUTILS]['statehistory'].".state_time,'%H') AS hour_of_day,
".$db_tables[DB_NDOUTILS]['statehistory'].".* 
FROM ".$db_tables[DB_NDOUTILS]['statehistory']." 
LEFT JOIN ".$db_tables[DB_NDOUTILS]['objects']." as obj1 ON ".$db_tables[DB_NDOUTILS]['statehistory'].".object_id=obj1.object_id 
WHERE TRUE";


// PROCESS EVENTS

// NOTIFICATIONS WITH CONTACTS
$sqlquery['GetNotificationsWithContacts']="SELECT 
obj1.objecttype_id as objecttype_id,
obj1.name1 AS host_name, 
obj1.name2 AS service_description, 
obj2.name1 AS contact_name,
obj3.name1 AS notification_command,
".$db_tables[DB_NDOUTILS]['contactnotifications'].".contactnotification_id,
".$db_tables[DB_NDOUTILS]['contactnotifications'].".contact_object_id,
".$db_tables[DB_NDOUTILS]['contactnotificationmethods'].".command_object_id,
".$db_tables[DB_NDOUTILS]['contactnotificationmethods'].".command_args,
".$db_tables[DB_NDOUTILS]['contactnotificationmethods'].".contactnotificationmethod_id,

".$db_tables[DB_NDOUTILS]['notifications'].".* 
FROM ".$db_tables[DB_NDOUTILS]['notifications']." 
LEFT JOIN ".$db_tables[DB_NDOUTILS]['objects']." as obj1 ON ".$db_tables[DB_NDOUTILS]['notifications'].".object_id=obj1.object_id 
LEFT JOIN ".$db_tables[DB_NDOUTILS]['contactnotifications']." ON ".$db_tables[DB_NDOUTILS]['notifications'].".notification_id=".$db_tables[DB_NDOUTILS]['contactnotifications'].".notification_id
LEFT JOIN ".$db_tables[DB_NDOUTILS]['objects']." as obj2 ON ".$db_tables[DB_NDOUTILS]['contactnotifications'].".contact_object_id=obj2.object_id 
LEFT JOIN ".$db_tables[DB_NDOUTILS]['contactnotificationmethods']." ON ".$db_tables[DB_NDOUTILS]['contactnotifications'].".contactnotification_id=".$db_tables[DB_NDOUTILS]['contactnotificationmethods'].".contactnotification_id
LEFT JOIN ".$db_tables[DB_NDOUTILS]['objects']." as obj3 ON ".$db_tables[DB_NDOUTILS]['contactnotificationmethods'].".command_object_id=obj3.object_id 
WHERE TRUE AND ".$db_tables[DB_NDOUTILS]["notifications"].".contacts_notified > '0'";

// NOTIFICATIONS
$sqlquery['GetNotifications']="SELECT 
obj1.objecttype_id as objecttype_id,
obj1.name1 AS host_name, 
obj1.name2 AS service_description, 
".$db_tables[DB_NDOUTILS]['notifications'].".* 
FROM ".$db_tables[DB_NDOUTILS]['notifications']." 
LEFT JOIN ".$db_tables[DB_NDOUTILS]['objects']." as obj1 ON ".$db_tables[DB_NDOUTILS]['notifications'].".object_id=obj1.object_id 
WHERE TRUE";


// SERVICE CHECKS

// HOST CHECKS

// EVENT HANDLERS

// DOWNTIME HISTORY

// COMMENT HSITORY

// FLAPPING HISTORY

// ACKNOWLEDGEMENTS






?>

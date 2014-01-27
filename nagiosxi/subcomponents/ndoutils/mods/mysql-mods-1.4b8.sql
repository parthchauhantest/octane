-- 05/03/08

ALTER TABLE `nagios_hosts` ADD INDEX ( `host_object_id` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `instance_id` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `status_update_time` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `current_state` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `check_type` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `state_type` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `last_state_change` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `notifications_enabled` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `problem_has_been_acknowledged` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `active_checks_enabled` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `passive_checks_enabled` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `event_handler_enabled` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `flap_detection_enabled` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `is_flapping` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `percent_state_change` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `latency` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `execution_time` );
ALTER TABLE `nagios_hoststatus` ADD INDEX ( `scheduled_downtime_depth` );

ALTER TABLE `nagios_services` ADD INDEX ( `service_object_id` );

ALTER TABLE `nagios_servicestatus` ADD INDEX ( `instance_id` ); 
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `status_update_time` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `current_state` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `check_type` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `state_type` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `last_state_change` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `notifications_enabled` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `problem_has_been_acknowledged` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `active_checks_enabled` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `passive_checks_enabled` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `event_handler_enabled` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `flap_detection_enabled` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `is_flapping` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `percent_state_change` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `latency` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `execution_time` );
ALTER TABLE `nagios_servicestatus` ADD INDEX ( `scheduled_downtime_depth` );


ALTER TABLE `nagios_timedeventqueue` ADD INDEX ( `instance_id` );
ALTER TABLE `nagios_timedeventqueue` ADD INDEX ( `event_type` );
ALTER TABLE `nagios_timedeventqueue` ADD INDEX ( `scheduled_time` );
ALTER TABLE `nagios_timedeventqueue` ADD INDEX ( `object_id` );

ALTER TABLE `nagios_timedevents` DROP INDEX `instance_id` ;
ALTER TABLE `nagios_timedevents` ADD INDEX ( `instance_id` );
ALTER TABLE `nagios_timedevents` ADD INDEX ( `event_type` );
ALTER TABLE `nagios_timedevents` ADD INDEX ( `scheduled_time` );
ALTER TABLE `nagios_timedevents` ADD INDEX ( `object_id` );

ALTER TABLE `nagios_systemcommands` DROP INDEX `instance_id`;  
ALTER TABLE `nagios_systemcommands` ADD INDEX ( `instance_id` );

ALTER TABLE `nagios_servicechecks` DROP INDEX `instance_id`;
ALTER TABLE `nagios_servicechecks` ADD INDEX ( `instance_id` );
ALTER TABLE `nagios_servicechecks` ADD INDEX ( `service_object_id` );
ALTER TABLE `nagios_servicechecks` ADD INDEX ( `start_time` );

--ALTER TABLE `nagios_configfilevariables` DROP INDEX `instance_id`;

-- 1/7/2009
ALTER TABLE `nagios_conninfo` ADD INDEX ( `instance_id` );

ALTER TABLE `nagios_contactstatus` ADD INDEX ( `instance_id` );

ALTER TABLE `nagios_customvariablestatus` ADD INDEX ( `instance_id` ); 
ALTER TABLE `nagios_customvariablestatus` DROP INDEX `object_id_2`;
ALTER TABLE `nagios_customvariablestatus` ADD INDEX ( `object_id` );
ALTER TABLE `nagios_customvariablestatus` ADD UNIQUE (`object_id` ,`varname`);

ALTER TABLE `nagios_eventhandlers` DROP INDEX `instance_id` ;
ALTER TABLE `nagios_eventhandlers` ADD INDEX ( `instance_id` );
ALTER TABLE `nagios_eventhandlers` ADD INDEX ( `object_id` ) ;
ALTER TABLE `nagios_eventhandlers` ADD INDEX ( `start_time` , `start_time_usec` ) ;
ALTER TABLE `nagios_eventhandlers` ADD UNIQUE (`instance_id` ,`object_id` ,`start_time` ,`start_time_usec`);

ALTER TABLE `nagios_instances` ADD INDEX ( `instance_name` ) ;

ALTER TABLE `nagios_objects` ADD INDEX ( `instance_id` );
ALTER TABLE `nagios_objects` DROP INDEX `objecttype_id` ;
ALTER TABLE `nagios_objects` ADD INDEX ( `objecttype_id` );
ALTER TABLE `nagios_objects` ADD INDEX ( `name1` );
ALTER TABLE `nagios_objects` ADD INDEX ( `name2` );

ALTER TABLE `nagios_statehistory` ADD INDEX ( `instance_id` ) ;
ALTER TABLE `nagios_statehistory` ADD INDEX ( `object_id` ) ;
ALTER TABLE `nagios_statehistory` ADD INDEX ( `state_time` , `state_time_usec` ) ;
ALTER TABLE `nagios_statehistory` ADD INDEX ( `state_type` ) ;




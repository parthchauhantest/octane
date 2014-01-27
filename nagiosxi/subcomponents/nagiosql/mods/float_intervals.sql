
ALTER TABLE `tbl_host` CHANGE `check_interval` `check_interval` FLOAT NULL DEFAULT NULL ,
CHANGE `retry_interval` `retry_interval` FLOAT NULL DEFAULT NULL;

ALTER TABLE `tbl_service` CHANGE `check_interval` `check_interval` FLOAT NULL DEFAULT NULL ,
CHANGE `retry_interval` `retry_interval` FLOAT NULL DEFAULT NULL;
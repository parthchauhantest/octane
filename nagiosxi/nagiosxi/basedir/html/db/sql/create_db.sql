CREATE DATABASE `nagiosxi` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE USER 'nagiosxi'@'localhost' IDENTIFIED BY '***';

GRANT USAGE ON * . * TO 'nagiosxi'@'localhost' IDENTIFIED BY '***' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;

GRANT ALL PRIVILEGES ON `nagiosxi` . * TO 'nagiosxi'@'localhost' WITH GRANT OPTION ;

SET PASSWORD FOR 'nagiosxi'@'localhost' = PASSWORD( 'n@gweb' ) ;


INSERT INTO `nagiosxi`.`xi_users` (
`user_id` ,
`username` ,
`password` ,
`name` ,
`email` ,
`backend_ticket` ,
`enabled`
)
VALUES (
NULL , 'nagiosadmin', MD5( 'nagiosadmin' ) , 'Nagios Admin', 'root@localhost', NULL , '1'
);

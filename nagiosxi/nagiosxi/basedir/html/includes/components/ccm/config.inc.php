<?php //config.inc.php

//global configuration options for Nagios CCM 
$CFG = array(); 

if(ENVIRONMENT=='nagiosxi') {
	//use XI's generated config 
	require_once('/usr/local/nagiosxi/etc/components/ccm_config.inc.php'); 
}
else { //nagioscore 
//nagios file locations 
$CFG['plugins_directory'] = '/usr/local/nagios/libexec';
$CFG['command_file'] = '/usr/local/nagios/var/rw/nagios.cmd'; 
$CFG['lock_file'] = '/usr/local/nagios/var/nagios.lock';
 
//mysql database connection info 
$CFG['db'] = array(
	'server'       => 'localhost',
	'port'     		=> '3306',
	'database'     => 'nagiosql',
	'username'     => 'nagiosql',
	'password'     => 'n@gweb',
	);
}

//misc global settings 	
$CFG['common']['install'] = 'passed'; 	
$CFG['domain'] = 'localhost'; 
$CFG['default_pagelimit'] = 15;
$CFG['lock_file'] = '/usr/local/nagios/var/nagios.lock';
$CFG['pear_include'] = '/usr/share/pear/HTML/Template/IT.php'; 
$CFG['audit_send'] = '/usr/local/nagiosxi/scripts/send_to_auditlog.php'; 

?>
#!/usr/bin/php -q
<?php
// SENDS AN XI REPORT VIA EMAIL AS A PDF ATTACHMENT
//
// Copyright (c) 2011 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: getrep.php 677 2011-07-08 14:47:52Z nscott $

require_once(dirname(__FILE__).'/../html/config.inc.php');
require_once(dirname(__FILE__).'/../html/includes/utils.inc.php');
require_once(dirname(__FILE__).'/../html/includes/common.inc.php');

main();

function main(){
	global $argv;
	$reportdir="/tmp";
	$filename="report.pdf";

	$args=parse_argv($argv);
	//print_r($argv);
	//print_r($args);
	
	$destemail=grab_array_var($args,"destemail","");
	$subject=grab_array_var($args,"subject"," ");	
	$body=grab_array_var($args,"body"," ");
	
	$user=grab_array_var($args,"user","nagiosadmin");
	$reportURL=grab_array_var($args,"reportURL","");
	
	if($destemail=="" || $reportURL==""){
		echo "Improper input.\n";
		echo "Example usage:\n";
		echo "./getrep.php --destemail=\"admin@yourhost.com\" --subject=\"Subject\"\n";
		echo "		--body=\"Email Body\" --user=\"nscott\"\n";
		echo "		--reportURL=\"reports/availability.php?\"\n";
		echo "\n";
		exit(1);
		}
	
	//Acquire PDF file and place it in $reportdir/$filename
	
	$realURL=exec("/usr/local/nagiosxi/tools/geturlas.php --user='$user' --url='$reportURL'");
	$realURL=trim($realURL);
	$out=exec("wget -O \"$reportdir/$filename\" '$realURL&mode=pdf'");
	
	//Send it using the Nagios mailer
	db_connect(DB_NAGIOSXI);
	$admin_email=get_option("admin_email");
	
	$opts=array(
		"from" 			=> "Nagios XI Scheduled Reporter <".$admin_email.">",
		"to" 			=> $destemail,
		"subject" 		=> $subject,
		"attachment" 	=> "$reportdir/$filename",
		"message"		=> $body,
	);

	send_email($opts,$debugmsg);
	//exec("rm $reportdir/$filename -f");
	
	}
?>

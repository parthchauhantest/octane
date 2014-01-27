<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: page-missing.php 75 2010-04-01 19:40:08Z egalstad $

require_once(dirname(__FILE__).'/common.inc.php');


route_request();

function route_request(){
	show_page();
	}

function show_page($error=false,$msg=""){
	global $cfg;
	global $request;
	global $lstr;
	
	// page start
	do_page_start($lstr['MissingPageTitle']);
?>
	<h1><?php echo $lstr['MissingPageHeader'];?></h1>
	
	<p>
	<?php echo $lstr['MissingPageNote'];?>
	</p>
	
	<p>
	<?php echo $lstr['MissingPageText'];?>: <?php echo grab_request_var("page");?>
	</p>
<?php
	do_page_end();
	}

?>



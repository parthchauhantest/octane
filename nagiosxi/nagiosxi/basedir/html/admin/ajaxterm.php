<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: ajaxterm.php 1258 2012-06-24 15:40:54Z egalstad $

require_once(dirname(__FILE__).'/../includes/common.inc.php');

// initialization stuff
pre_init();

// start session
init_session();

// grab GET or POST variables 
grab_request_vars();

// check prereqs
check_prereqs();

// check authentication
check_authentication(false);

// only admins can access this page
if(is_admin()==false){
	echo $lstr['NotAuthorizedErrorText'];
	exit();
	}

// route request
route_request();


function route_request(){
	global $request;
	
	show_ajaxterm();
	}
	
	
function show_ajaxterm($error=false,$msg=""){
	global $request;
	global $lstr;
	

	do_page_start(array("page_title"=>"SSH Terminal"),true);

?>

	
	<h1>SSH Terminal</h1>
	

<?php
	display_message($error,false,$msg);
?>


<?php
	// Enterprise Edition message
	echo enterprise_message();

	if(enterprise_features_enabled()==true){
		show_ajaxterm_content(true);
		}
	else{
		show_ajaxterm_content(false);
		}
?>

<?php

	do_page_end(true);
	exit();
	}

	
function show_ajaxterm_content($fullaccess=false){
	global $lstr;
	global $request;
	
	// use current URL to craft HTTPS url
	// this is needed to accomodate users who are connecting through a NAT redirect/port forwarding setup
	$current=get_current_url(false,true);
	$default_url=$current;
	$pos=strpos($default_url,"/nagiosxi/admin/ajaxterm.php");
	$newurl=substr($default_url,0,$pos)."/nagios/ajaxterm/";
	// force ssl
	$url=str_replace("http:","https:",$newurl);
	
	// user can override the URL
	$url=grab_request_var("url",$url);

	// check enterprise license
	$efe=enterprise_features_enabled();
?>

	<p>
	The terminal provides you with a convenient, web-based session to the terminal of your Nagios XI server.  You can login to your Nagios XI server using this interface to perform upgrades, run diagnostics, and more.
	</p>
	

<?php	
	if($efe==true){
?>

	<p>
	<strong>NOTE:</strong> You must re-enter your login credentials to access the SSH terminal.
	</p>
	
	
	<form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">

	<div id="formButtons">
	URL: <input type="text" size="45" name="url" id="urlBox" value="<?php echo encode_form_val($url);?>" class="textfield" />
	<input type="submit" class="submitbutton" name="updateButton" value="<?php echo $lstr['UpdateButton'];?>" id="updateButton">
	</div>	
	</form>

	<a href="javascript: void(0)" onclick="window.open('<?php echo $url;?>', 'windowname1', 'width=700,height=450'); 
   return false;">Open terminal in a new window</a>
	<iframe src="<?php echo $url;?>" width="700px" height="450px"></iframe> 
<?php
		}
	else{
?>	
	<p>
	<img src="<?php echo theme_image("ajaxterm.png");?>">
	</p>
<?php
		echo enterprise_limited_feature_message("This feature is only available in Enterprise Edition.  ");
		}
	}
	

?>
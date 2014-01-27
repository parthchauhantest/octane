<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: deleteobject.php 1160 2012-05-04 15:23:56Z egalstad $

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


// route request
route_request();


function route_request(){
	global $request;
	
	$host=grab_request_var("host","");
	$service=grab_request_var("service","");
	
	$reroute=false;
	
	// make sure host/service exists
	if(host_exists($host)==false || ($service!="" && service_exists($host,$service)==false)){
		$reroute=true;
		}
		
	// check perms
	if($service!=""){
		if(is_authorized_to_configure_service(0,$host,$service)==false)
			$reroute=true;
		}
	else{
		if(is_authorized_to_configure_host(0,$host)==false)
			$reroute=true;
		}

	if($reroute==true){
		header("Location: main.php");
		exit();		
		}
	
	if($service!=""){
		if(isset($request["delete"]))
			do_delete_service();
		else
			confirm_service_delete();
		}
	else{
		if(isset($request["delete"]))
			do_delete_host();
		else
			confirm_host_delete();
		}
	
	}
	
function confirm_service_delete($error=false,$msg=""){
	global $lstr;

	// grab variables
	$host=grab_request_var("host","");
	$service=grab_request_var("service","");
	$return=grab_request_var("return","");

	// can this service be deleted?
	check_service_deletion_prereqs($host,$service);
	
	do_page_start(array("page_title"=>$lstr['ConfirmDeleteServicePageTitle']),true);
?>

	<h1><?php echo $lstr['ConfirmDeleteServicePageHeader'];?></h1>
	
	<div class="servicestatusdetailheader">
	<div class="serviceimage">
	<!--image-->
	<?php show_object_icon($host,$service,true);?>
	</div>
	<div class="servicetitle">
	<div class="servicename"><a href="<?php echo get_service_status_detail_link($host,$service);?>"><?php echo $service;?></a></div>
	<div class="hostname"><a href="<?php echo get_host_status_detail_link($host);?>"><?php echo $host;?></a></div>
	</div>
	</div>
	

<?php
	display_message($error,false,$msg);
	
	/*
	$hcw=get_host_configwizard($host);
	$scw=get_service_configwizard($host,$service);
	echo "<BR>HCW=$hcw<BR>";
	echo "SCW=$scw<BR>";
	*/
?>
	
	<p><?php echo $lstr['ConfirmDeleteServicePageNotes'];?></p>
	
	<form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">
	<?php echo get_nagios_session_protector();?>
	<input type="hidden" name="delete" value="1" />
	<input type="hidden" name="host" value="<?php echo encode_form_val($host);?>" />
	<input type="hidden" name="service" value="<?php echo encode_form_val($service);?>" />
	<input type="hidden" name="return" value="<?php echo encode_form_val($return);?>" />
	
	<div id="formButtons">
	<input type="submit" class="submitbutton" name="deleteButton" value="<?php echo $lstr['DeleteButton'];?>" />
	<input type="submit" class="submitbutton" name="cancelButton" value="<?php echo $lstr['CancelButton'];?>"/>
	</div>
	</form>

<?php
	}
	
	
function do_delete_service($error=false,$msg=""){
	global $request;
	global $lstr;

	
	// check session
	check_nagios_session_protector();

	// grab variables
	$host=grab_request_var("host","");
	$service=grab_request_var("service","");
	$return=grab_request_var("return","");

	// user cancelled, so redirect them
	if(isset($request["cancelButton"])){
		$url=get_return_url($return,$host,$service);
		//echo "CANCELLED - REDIRECTING TO ".$url." ($return)";
		header("Location: ".$url);
		exit();
		}

	check_service_deletion_prereqs($host,$service);
			
	// log it
	send_to_audit_log("User deleted service '".$service."' on host '".$host."'",AUDITLOGTYPE_DELETE);

	// submit delete command
	delete_nagioscore_service($host,$service);
	
	do_page_start(array("page_title"=>$lstr['ServiceDeleteScheduledPageTitle']),true);
?>

	<h1><?php echo $lstr['ServiceDeleteScheduledPageHeader'];?></h1>
	
	<div class="servicestatusdetailheader">
	<div class="serviceimage">
	<!--image-->
	<?php show_object_icon($host,$service,true);?>
	</div>
	<div class="servicetitle">
	<div class="servicename"><a href="<?php echo get_service_status_detail_link($host,$service);?>"><?php echo $service;?></a></div>
	<div class="hostname"><a href="<?php echo get_host_status_detail_link($host);?>"><?php echo $host;?></a></div>
	</div>
	</div>
	
	<br clear="all">

	
<?php
	display_message($error,false,$msg);
?>

	<p>
	The requested service has been scheduled for deletion and should be removed shortly.
	</p>
	
<?php
	if(is_advanced_user()==true){
?>
	<p>
	If the service fails to be removed...
	</p>
	<ul>
<?php
	if(is_admin()==true){
	
		$url=get_base_url()."admin/coreconfigsnapshots.php";
		echo "<li>Check the most recent <a href='".$url."' target='_top'>configuration snapshots</a> for errors</li>";
		
		$url=get_base_url()."config/nagioscorecfg/";
		echo "<li>Use the <a href='".$url."' target='_top'>Nagios Core Configuration Manager</a> to delete the service</li>";
		}
	else{
		echo "<li>Ask your Nagios administrator to remove the service</li>";
		}
?>
	</ul>
<?php
		}
?>

<?php 
	if($return!=""){
?>
	<form method="post" action="<?php echo get_return_url($return,$host,$service,true);?>">
	
	<div id="formButtons">
	<input type="submit" class="submitbutton" name="backButton" value="<?php echo $lstr['ContinueButton'];?>" />
	</div>
	</form>

<?php
		}
?>

<?php
	exit();

	}
	
	
function check_service_deletion_prereqs($host,$service){

	$sid=nagiosql_get_service_id($host,$service);
	
	// check for errors...
	$errors=0;
	$errmsg=array();
	if($sid<=0)
		$errmsg[$errors++]="Could not find a unique id for this service";
	if(can_service_be_deleted($host,$service)==false)
		$errmsg[$errors++]="Service cannot be deleted using this method";
		
	// handle errors
	if($errors>0){
		show_service_delete_error(true,$errmsg);
		exit();
		}
	}
	
	
function show_service_delete_error($error=false,$msg=""){
	global $request;
	global $lstr;
	
	// grab variables
	$host=grab_request_var("host","");
	$service=grab_request_var("service","");
	$return=grab_request_var("return","");
	
	do_page_start(array("page_title"=>$lstr['DeleteServiceErrorPageTitle']),true);
?>

	<h1><?php echo $lstr['DeleteServiceErrorPageHeader'];?></h1>
	
	<div class="servicestatusdetailheader">
	<div class="serviceimage">
	<!--image-->
	<?php show_object_icon($host,$service,true);?>
	</div>
	<div class="servicetitle">
	<div class="servicename"><a href="<?php echo get_service_status_detail_link($host,$service);?>"><?php echo $service;?></a></div>
	<div class="hostname"><a href="<?php echo get_host_status_detail_link($host);?>"><?php echo $host;?></a></div>
	</div>
	</div>
	
	
<?php
	display_message($error,false,$msg);
?>

	<p>
	One or more errors were detected that prevent the service from being deleted.
	</p>
	
	<p>
	Possible causes include...
	</p>
	<ul>
	<li>The service is associated with other hosts, services, or objects that need to be deleted first</li>
	<li>The service is generated by an advanced monitoring configuration entry</li>
	<li>The service is maintained in a static or external configuration file</li>
	</ul>
	
	<p>
	To resolve this issue...
	</p>
	<ul>
<?php
	if(is_admin()==true){
		$url=get_base_url()."config/nagioscorecfg/";
		echo "<li>Use the <a href='".$url."' target='_top'>Nagios Core Configuration Manager</a> to delete the service  <b>- or -</b></li>";
		echo "<li>Manually delete the service definition from the appropriate external configuration file</li>";
		}
	else{
		echo "<li>Ask your Nagios administrator to remove this service</li>";
		}
?>
	</ul>
	
<?php
	if($return!=""){
?>
	<form method="post" action="<?php echo get_return_url($return,$host,$service);?>">
	
	<div id="formButtons">
	<input type="submit" class="submitbutton" name="backButton" value="<?php echo $lstr['BackButton'];?>" />
	</div>
	</form>

<?php
		}
?>

<?php
	exit();
	}



	
function confirm_host_delete($error=false,$msg=""){
	global $lstr;

	// grab variables
	$host=grab_request_var("host","");
	$return=grab_request_var("return","");

	// can this host be deleted?
	check_host_deletion_prereqs($host);

	do_page_start(array("page_title"=>$lstr['ConfirmDeleteHostPageTitle']),true);
?>

	<h1><?php echo $lstr['ConfirmDeleteHostPageHeader'];?></h1>
	
	<div class="hoststatusdetailheader">
	<div class="hostimage">
	<!--image-->
	<?php show_object_icon($host,"",true);?>
	</div>
	<div class="hosttitle">
	<div class="hostname"><a href="<?php echo get_host_status_detail_link($host);?>"><?php echo $host;?></a></div>
	</div>
	</div>
	

<?php
	display_message($error,false,$msg);
?>
	
	<p><?php echo $lstr['ConfirmDeleteHostPageNotes'];?></p>
	
	<form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">
	<input type="hidden" name="delete" value="1" />
	<input type="hidden" name="host" value="<?php echo encode_form_val($host);?>" />
	<input type="hidden" name="return" value="<?php echo encode_form_val($return);?>" />
	
	<div id="formButtons">
	<input type="submit" class="submitbutton" name="deleteButton" value="<?php echo $lstr['DeleteButton'];?>" />
	<input type="submit" class="submitbutton" name="cancelButton" value="<?php echo $lstr['CancelButton'];?>"/>
	</div>
	</form>

<?php
	}
	

function do_delete_host($error=false,$msg=""){
	global $request;
	global $lstr;
	
	// grab variables
	$host=grab_request_var("host","");
	$return=grab_request_var("return","");

	// user cancelled, so redirect them
	if(isset($request["cancelButton"])){
		$url=get_return_url($return,$host);
		//echo "CANCELLED - REDIRECTING TO ".$url." ($return)";
		header("Location: ".$url);
		exit();
		}
		
	check_host_deletion_prereqs($host);
		
	// log it
	send_to_audit_log("User deleted host '".$host."'",AUDITLOGTYPE_DELETE);

	// submit delete command
	delete_nagioscore_host($host);
	
	do_page_start(array("page_title"=>$lstr['HostDeleteScheduledPageTitle']),true);
?>

	<h1><?php echo $lstr['HostDeleteScheduledPageHeader'];?></h1>
	
	<div class="hoststatusdetailheader">
	<div class="hostimage">
	<!--image-->
	<?php show_object_icon($host,"",true);?>
	</div>
	<div class="hosttitle">
	<div class="hostname"><a href="<?php echo get_host_status_detail_link($host);?>"><?php echo $host;?></a></div>
	</div>
	</div>

	<br clear="all">
	
<?php
	display_message($error,false,$msg);
?>

	<p>
	The requested host has been scheduled for deletion and should be removed shortly.
	</p>
	
<?php
	if(is_advanced_user()==true){
?>
	<p>
	If the host fails to be removed...
	</p>
	<ul>
<?php
	if(is_admin()==true){
	
		$url=get_base_url()."admin/coreconfigsnapshots.php";
		echo "<li>Check the most recent <a href='".$url."' target='_top'>configuration snapshots</a> for errors</li>";
		
		$url=get_base_url()."config/nagioscorecfg/";
		echo "<li>Use the <a href='".$url."' target='_top'>Nagios Core Configuration Manager</a> to delete the host</li>";
		}
	else{
		echo "<li>Ask your Nagios administrator to remove the host</li>";
		}
?>
	</ul>
<?php
		}
?>

<?php 
	if($return!=""){
?>
	<form method="post" action="<?php echo get_return_url($return,$host,"",true);?>">
	
	<div id="formButtons">
	<input type="submit" class="submitbutton" name="backButton" value="<?php echo $lstr['ContinueButton'];?>" />
	</div>
	</form>

<?php
		}
?>

<?php
	exit();
	}
	
	
	
function check_host_deletion_prereqs($host){

	$hid=nagiosql_get_host_id($host);
	
	// check for errors...
	$errors=0;
	$errmsg=array();
	if($hid<=0)
		$errmsg[$errors++]="Could not find a unique id for this host";
	if(can_host_be_deleted($host)==false)
		$errmsg[$errors++]="Host cannot be deleted using this method";
		
	// handle errors
	if($errors>0){
		show_host_delete_error(true,$errmsg);
		exit();
		}
	}
	
	
function show_host_delete_error($error=false,$msg=""){
	global $request;
	global $lstr;
	
	// grab variables
	$host=grab_request_var("host","");
	$return=grab_request_var("return","");
	
	do_page_start(array("page_title"=>$lstr['DeleteHostErrorPageTitle']),true);
?>

	<h1><?php echo $lstr['DeleteHostErrorPageHeader'];?></h1>
	
	<div class="hoststatusdetailheader">
	<div class="hostimage">
	<!--image-->
	<?php show_object_icon($host,"",true);?>
	</div>
	<div class="hosttitle">
	<div class="hostname"><a href="<?php echo get_host_status_detail_link($host);?>"><?php echo $host;?></a></div>
	</div>
	</div>
	
	
<?php
	display_message($error,false,$msg);
?>

	<p>
	One or more errors were detected that prevent the host from being deleted.
	</p>
	
	<p>
	Possible causes include...
	</p>
	<ul>
	<li>The host is associated with other hosts, services, or objects that need to be deleted first</li>
	<li>The host is maintained in a static or external configuration file</li>
	</ul>
	
	<p>
	To resolve this issue...
	</p>
	<ul>
<?php
	if(is_admin()==true){
		$url=get_base_url()."config/nagioscorecfg/";
		echo "<li>Use the <a href='".$url."' target='_top'>Nagios Core Configuration Manager</a> to delete the host  <b>- or -</b></li>";
		echo "<li>Manually delete the host definition from the appropriate external configuration file</li>";
		}
	else{
		echo "<li>Delete all services associated with this host first <b>- or - </b></li>";
		echo "<li>Ask your Nagios administrator to remove this host</li>";
		}
?>
	</ul>

<?php 
	if($return!=""){
?>
	<form method="post" action="<?php echo get_return_url($return,$host);?>">
	
	<div id="formButtons">
	<input type="submit" class="submitbutton" name="backButton" value="<?php echo $lstr['BackButton'];?>" />
	</div>
	</form>

<?php
		}
?>

<?php
	exit();
	}


function get_return_url($return,$host,$service="",$deleteurl=false){

	$url="";

	switch($return){
		case "servicedetail";
			$url=get_service_status_detail_link($host,$service);
			if($deleteurl==true)
				$url=get_base_url()."includes/components/xicore/status.php?show=services";
			break;
		case "hostdetail";
			$url=get_host_status_detail_link($host);
			if($deleteurl==true)
				$url=get_base_url()."includes/components/xicore/status.php?show=hosts";
			break;
		default:
			$url="main.php";
			break;
		}
		
	return $url;
	}
	
	
	
?>
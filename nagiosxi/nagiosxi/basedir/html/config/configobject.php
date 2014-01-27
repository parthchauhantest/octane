<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: configobject.php 1160 2012-05-04 15:23:56Z egalstad $

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
		if(isset($request["apply"]))
			do_config_service();
		else
			show_service_config();
		}
	else{
		if(isset($request["apply"]))
			do_config_host();
		else
			show_host_config();
		}
	
	}
	
function show_service_config($error=false,$msg=""){
	global $lstr;

	// grab variables
	$host=grab_request_var("host","");
	$service=grab_request_var("service","");
	$return=grab_request_var("return","");

	// can this service be configured??
	check_service_config_prereqs($host,$service);
	
	// default values
	$check_interval="";
	$retry_interval="";
	$max_check_attempts="";
	$check_command="";
	$first_notification_delay="";
	$notification_interval="";
	$notification_options=""; // none, immediate, delayed
	$notification_targets=array(
		"myself" => "",
		"contacts" => "",
		"contactgroups" => "",
		);
	$contacts="";
	$contact_names=array();
	$contact_groups="";
	$contact_group_names=array();
	$contact_id=array();
	$contactgroup_id=array();
	$service_groups="";
	$service_group_names=array();
	$servicegroup_id=array();

	// read existing configuration
	$sa=nagiosql_read_service_config_from_file($host,$service);
	//print_r($sa);
	
	// process values
	$val=grab_array_var($sa,"check_interval");
	if($val!="")
		$check_interval=$val;
	$val=grab_array_var($sa,"retry_interval");
	if($val!="")
		$retry_interval=$val;
	$val=grab_array_var($sa,"max_check_attempts");
	if($val!="")
		$max_check_attempts=$val;
	$val=grab_array_var($sa,"check_command");
	if($val!="")
		$check_command=$val;

	$notifications_enabled=1;
	$val=grab_array_var($sa,"notifications_enabled");
	if($val!="")
		$notifications_enabled=$val;

	$val=grab_array_var($sa,"first_notification_delay");
	if($val!="")
		$first_notification_delay=$val;

	$val=grab_array_var($sa,"notification_interval");
	if($val!="")
		$notification_interval=$val;
		
	$val=grab_array_var($sa,"notification_options");
	if($val=="n" || $notifications_enabled==0)
		$notification_options="none";
	else if($first_notification_delay!="" && $first_notification_delay!="0")
		$notification_options="delayed";
	else
		$notification_options="immediate";
		
	$val=grab_array_var($sa,"contacts");
	if($val!="")
		$contacts=$val;
	$val=grab_array_var($sa,"contact_groups");
	if($val!="")
		$contact_groups=$val;

	$val=grab_array_var($sa,"servicegroups");
	if($val!="")
		$service_groups=$val;
		
	//echo "SERVICEGROUPS: $service_groups<BR>";

	// process contacts
	$c=explode(",",$contacts);
	// get user's name
	$username=get_user_attr(0,'username');
	foreach($c as $cid => $cname){
		// "myself"
		if($cname==$username){
			$notification_targets["myself"]="on";
			continue;
			}
		if($cname=="null" || $cname=="")
			continue;
		// other contacts
		$contact_names[]=$cname;
		}
	if(count($contact_names)>0)
		$notification_targets["contacts"]="on";
		
	// process contactgroups
	$c=explode(",",$contact_groups);
	foreach($c as $cid => $cname){
		if($cname=="null" || $cname=="")
			continue;
		$contact_group_names[]=$cname;
		}
	if(count($contact_group_names)>0 )
		$notification_targets["contactgroups"]="on";

	// set some defaults for update purposes
	if($first_notification_delay=="")
		$first_notification_delay=15;
		
	// process servicegroups
	$c=explode(",",$service_groups);
	foreach($c as $cid => $cname){
		if($cname=="null" || $cname=="")
			continue;
		$service_group_names[]=$cname;
		}
	
	do_page_start(array("page_title"=>$lstr['ReconfigureServicePageTitle']),true);
?>

	<h1><?php echo $lstr['ReconfigureServicePageHeader'];?></h1>
	
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

<?php
	
	/*
	echo "<BR>HOST:<BR>";
	print_r($ha);
	echo "<BR>SERVICE:<BR>";
	print_r($sa);
	echo "<BR>";
	
	echo "<BR>CONTACT NAMES:<BR>";
	print_r($contact_names);
	echo "<BR>";

	echo "<BR>CONTACT GROUP NAMES:<BR>";
	print_r($contact_group_names);
	echo "<BR>";
	*/
?>

<?php
	if(is_advanced_user()==true){
		$url=get_base_url()."config/nagioscorecfg/";
		echo "<p>Note: You may update basic settings for the service below or use the <a href='".$url."' target='_top'>Nagios Core Config Manager</a> to modify advanced settings for this service.  Service attribute values which are inherited from advanced templates are not shown below.</p>";
		}
?>

	
	<form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">
	<?php echo get_nagios_session_protector();?>
	<input type="hidden" name="apply" value="1" />
	<input type="hidden" name="host" value="<?php echo encode_form_val($host);?>" />
	<input type="hidden" name="service" value="<?php echo encode_form_val($service);?>" />
	<input type="hidden" name="return" value="<?php echo encode_form_val($return);?>" />
	<input type="hidden" name="originalservice" value="<?php echo base64_encode(serialize($sa));?>" />
	
	<br>
	
	<script type="text/javascript">
	$(document).ready(function() {
		$("#tabs").tabs();
	});
	</script>
	
	<div id="tabs">
	<ul>
	<li><a href="#monitoring-tab">Monitoring</a></li>
	<li><a href="#notifications-tab">Notifications</a></li>
	<li><a href="#groups-tab">Groups</a></li>
	</ul>

	<div id="monitoring-tab">

	<div class="sectionTitle">Monitoring Settings</div>
	
	<p>Specify the parameters that determine how the service should be monitored.</p>
	
	<table>

	
	<tr>
	<td><b>Under normal circumstances...</b><br>Monitor the service every <input type="text" size="2" name="check_interval" id="check_interval" value="<?php echo encode_form_val($check_interval);?>" class="textfield" /> minutes.</td>
	</tr>
	
	<tr>
	<td><b>When a potential problem is first detected...</b><br>Re-check the service every <input type="text" size="2" name="retry_interval" id="retry_interval" value="<?php echo $retry_interval;?>" class="textfield" /> minutes up to <input type="text" size="2" name="max_check_attempts" id="max_check_attempts" value="<?php echo encode_form_val($max_check_attempts);?>" class="textfield" /> times before generating an alert.</td>
	</tr>
	
<?php
	if(is_advanced_user()==true){
?>
	<tr>
	<td><b>Monitor the service with this command...</b> (Advanced users only)<br><input type="text" size="60" name="check_command" id="check_command" value="<?php echo htmlentities($check_command);?>" class="textfield" /></td>
	</tr>
<?php
		}
?>
	
	</table>

	</div><!--monitoring-tab-->
	<div id="notifications-tab">

	<div class="sectionTitle">Notification Settings</div>
	
	<p>Specify the parameters that determine how notifications should be sent for the service.</p>
	
	<table>
	
	<tr>
	<td>
	<b>When a problem is detected...</b><br>
	<input type="radio" name="notification_options" value="none" <?php echo is_checked($notification_options,"none");?>>Don't send any notifications<br>
	<input type="radio" name="notification_options" value="immediate"  <?php echo is_checked($notification_options,"immediate");?>>Send a notification immediately<br>
	<input type="radio" name="notification_options" value="delayed" <?php echo is_checked($notification_options,"delayed");?>>Wait <input type="text" size="2" name="first_notification_delay" id="first_notification_delay" value="<?php echo $first_notification_delay;?>" class="textfield" /> minutes before sending a notification
	</td>
	</tr>
	
	<tr>
	<td><b>If problems persist...</b><br>Send a notification every <input type="text" size="2" name="notification_interval" id="notification_interval" value="<?php echo encode_form_val($notification_interval);?>" class="textfield" /> minutes until the problem is resolved.</td>
	</tr>
	
	<tr>
	<td><b>Send alert notifications to...</b><br>
	
	<script type="text/javascript">
	function check_contacts(){
		$('#notification_targets_contacts').attr('checked',true);
		}
	function check_contactgroups(){
		$('#notification_targets_contactgroups').attr('checked',true);
		}
	</script>
	
	<input type="checkbox" name="notification_targets[myself]" id="notification_targets_myself" <?php echo is_checked($notification_targets["myself"],"on");?>>Myself (<a href="<?php echo get_base_url()."account/?xiwindow=notifyprefs.php";?>" target="_blank">Adjust settings</a>)<br>
	
	<input type="checkbox" name="notification_targets[contacts]" id="notification_targets_contacts" <?php echo is_checked($notification_targets["contacts"],"on");?>>Other individual contacts<br>
	<div style="overflow: auto; width: 275px; height: 80px; border: 1px solid gray; margin: 0 0 0 35px;">
	<?php
	$xml=get_xml_contact_objects(array("is_active"=>1,"orderby"=>"contact_name:a"));
	//print_r($xml);
	$username=get_user_attr(0,'username');
	foreach($xml->contact as $c){
	
		$cid=strval($c->attributes()->id);
		$cname=strval($c->contact_name);
		$calias=strval($c->alias);
	
		if(!strcmp($cname,$username))
			continue;
			
		if(array_key_exists($cid,$contact_id))
			$ischecked="CHECKED";
		else if(in_array($cname,$contact_names))
			$ischecked="CHECKED";
		else
			$ischecked="";
		
		echo "<input type='checkbox' name='contact_id[".$cid."]' ".$ischecked." onclick='check_contacts()'>".$calias." (".$cname.")<br>";
		}
	?>
	</div>
	
	<input type="checkbox" name="notification_targets[contactgroups]" id="notification_targets_contactgroups" <?php echo is_checked($notification_targets["contactgroups"],"on");?>>Specific contact groups<br>
	<div style="overflow: auto; width: 275px; height: 80px; border: 1px solid gray; margin: 0 0 0 35px;">
	<?php
	$xml=get_xml_contactgroup_objects(array("is_active"=>1,"orderby"=>"contactgroup_name:a"));
	//print_r($xml);
	foreach($xml->contactgroup as $c){
	
		$cid=strval($c->attributes()->id);
		$cname=strval($c->contactgroup_name);
		$calias=strval($c->alias);

		if(array_key_exists($cid,$contactgroup_id))
			$ischecked="CHECKED";
		else if(in_array($cname,$contact_group_names))
			$ischecked="CHECKED";
		else
			$ischecked="";
		
		echo "<input type='checkbox' name='contactgroup_id[".$cid."]' ".$ischecked." onclick='check_contactgroups()'>".$calias." (".$cname.")<br>";
		}
	?>
	</div>
	
	</td>
	</tr>
	
	</table>
	
	</div><!--notifications-tab-->
	<div id="groups-tab">

	
	<div class="sectionTitle">Service Groups</div>
	
	<p>Define which servicegroup(s) the monitored service(s) should belong to (if any).</p>

	<div style="overflow: auto; width: 275px; height: 80px; border: 1px solid gray; margin: 0 0 0 35px;">
	<?php
	$xml=get_xml_servicegroup_objects(array("is_active"=>1,"orderby"=>"servicegroup_name:a"));
	//print_r($xml);
	foreach($xml->servicegroup as $c){
	
		$cid=strval($c->attributes()->id);
		$cname=strval($c->servicegroup_name);
		$calias=strval($c->alias);

		if(array_key_exists($cid,$servicegroup_id))
			$ischecked="CHECKED";
		else if(in_array($cname,$service_group_names))
			$ischecked="CHECKED";
		else
			$ischecked="";
		
		echo "<input type='checkbox' name='servicegroup_id[".$cid."]' ".$ischecked." >".$calias." (".$cname.")<br>";
		}
	?>
	</div>
	
	</div><!--groups-tab-->
	</div><!--tabs-->
	
	<div id="formButtons">
	<input type="submit" class="submitbutton" name="updateButton" value="<?php echo $lstr['UpdateButton'];?>" />
	<input type="submit" class="submitbutton" name="cancelButton" value="<?php echo $lstr['CancelButton'];?>"/>
	</div>
	</form>

<?php
	}
	
	
function redirect_service_config($error=false,$msg=""){
	global $lstr;
	

	// grab variables
	$host=grab_request_var("host","");
	$service=grab_request_var("service","");
	$return=grab_request_var("return","");

	do_page_start(array("page_title"=>$lstr['ReconfigureServicePageTitle']),true);
?>

	<h1><?php echo $lstr['ReconfigureServicePageHeader'];?></h1>
	
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
<?php
	echo "This service appears to make use of an advanced configuration.  ";
	if(is_advanced_user()==true){
		$url=get_base_url()."config/nagioscorecfg/";
		echo "Use the <a href='".$url."' target='_top'>Nagios Core Config Manager</a> to modify the settings for this service.";
		}
	else
		echo "Contact your Nagios administrator to modify the settings for this service.";
?>
	</p>
	
	<form method="get" action="<?php echo get_base_url()."includes/components/xicore/status.php";?>">
	<input type="hidden" name="show" value="servicedetail">
	<input type="hidden" name="host" value="<?php echo encode_form_val($host);?>">
	<input type="hidden" name="service" value="<?php echo encode_form_val($service);?>">
	<div id="formButtons">
	<input type="submit" class="submitbutton" name="backButton" value="<?php echo $lstr['BackButton'];?>"/>
	</div>
	</form>

<?php
	exit();
	}
	
	
function check_service_config_prereqs($host,$service){

	if(is_service_configurable($host,$service)==false)
		redirect_service_config();
	}
	
	
function do_config_service($error=false,$msg=""){
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
		header("Location: ".$url);
		exit();
		}
		
	//echo "<BR>CONFIGURING SERVICE...";
	
	//echo "<BR>REQUEST:<BR>";
	//print_r($request);
	
	$original_service_s=grab_request_var("originalservice","");
	if($original_service_s=="")
		$original_service=array();
	else
		$original_service=unserialize(base64_decode($original_service_s));
		
	//echo "<BR>ORIGINAL SERVICE:<BR>";
	//print_r($original_service);
	
	
	// grab config variables
	$check_interval=grab_request_var("check_interval");
	$retry_interval=grab_request_var("retry_interval");
	$max_check_attempts=grab_request_var("max_check_attempts");
	$check_command=grab_request_var("check_command");
	$first_notification_delay=grab_request_var("first_notification_delay");
	$notification_interval=grab_request_var("notification_interval");
	$notification_options=grab_request_var("notification_options");
	$notification_targets=grab_request_var("notification_targets",array());
	$contact_id=grab_request_var("contact_id",array());
	$contactgroup_id=grab_request_var("contactgroup_id",array());
	$servicegroup_id=grab_request_var("servicegroup_id",array());
	
	// resolve contact names
	$contact_names="";
	$total_contacts=0;
	// this user
	if(array_key_exists("myself",$notification_targets)){
		$contact_names.=get_user_attr(0,"username");
		$total_contacts++;
		}	
	// additional individual contacts
	if(array_key_exists("contacts",$notification_targets)){
		$ids="";
		foreach($contact_id as $id => $val){
			$ids.=",".$id;
			}
		if($ids!=""){
			$args=array(
				"is_active" => 1,
				"contact_id" => "in:".$ids,
				);
			//echo "IDS: $ids<BR>";
			//print_r($args);	
			$xml=get_xml_contact_objects($args);
			foreach($xml->contact as $c){
				if($total_contacts>0)
					$contact_names.=",";
				$contact_names.=$c->contact_name;
				$total_contacts++;
				}
			}
		}
	//echo "<BR>CONTACTS: $contact_names<BR>";

	// resolve contactgroup names
	$contactgroup_names="";
	$total_contactgroups=0;
	// additional individual contactgroups
	if(array_key_exists("contactgroups",$notification_targets)){
		$ids="";
		foreach($contactgroup_id as $id => $val){
			$ids.=",".$id;
			}
		if($ids!=""){
			$args=array(
				"is_active" => 1,
				"contactgroup_id" => "in:".$ids,
				);
			//echo "IDS: $ids<BR>";
			//print_r($args);	
			$xml=get_xml_contactgroup_objects($args);
			foreach($xml->contactgroup as $cg){
				if($total_contactgroups>0)
					$contactgroup_names.=",";
				$contactgroup_names.=$cg->contactgroup_name;
				$total_contactgroups++;
				}
			}
		}
	//echo "<BR>CONTACTGROUPS: $contactgroup_names<BR>";

	// resolve servicegroup names
	$servicegroup_names="";
	$total_servicegroups=0;
	$ids="";
	if(is_array($servicegroup_id)){
		foreach($servicegroup_id as $id => $val){
			$ids.=",".$id;
			}
		if($ids!=""){
			$args=array(
				"is_active" => 1,
				"servicegroup_id" => "in:".$ids,
				);
			//echo "IDS: $ids<BR>";
			//print_r($args);	
			$xml=get_xml_servicegroup_objects($args);
			foreach($xml->servicegroup as $sg){
				if($total_servicegroups>0)
					$servicegroup_names.=",";
				$servicegroup_names.=$sg->servicegroup_name;
				$total_servicegroups++;
				}
			}
		}
	//echo "<BR>SERVICEGROUPS: $servicegroup_names<BR>";
	//exit();
	
	// new object config array
	$new_service=$original_service;
	
	// apply config settings to new object
	if($check_interval!="")
		$new_service["check_interval"]=$check_interval;
	if($retry_interval!="")
		$new_service["retry_interval"]=$retry_interval;
	if($max_check_attempts!="")
		$new_service["max_check_attempts"]=$max_check_attempts;
	if($check_command!="")
		$new_service["check_command"]=$check_command;
	if($notification_interval!="")
		$new_service["notification_interval"]=$notification_interval;
		
	// contacts (only if modified)
	if($contact_names!="")
		$new_service["contacts"]=$contact_names;
	else{
		$oc=grab_array_var($original_service,"contacts");
		if($oc!="")
			$new_service["contacts"]="null";
		}
	// contactgroups (only if modified)
	if($contactgroup_names!="")
		$new_service["contact_groups"]=$contactgroup_names;
	else{
		$ocg=grab_array_var($original_service,"contact_groups");
		if($ocg!="")
			$new_service["contact_groups"]="null";
		}
		
	// service groups (only if modified)
	if($servicegroup_names!="")
		$new_service["servicegroups"]=$servicegroup_names;
	else{
		$osg=grab_array_var($original_service,"servicegroups");
		if($osg!="")
			$new_service["servicegroups"]="null";
		}

	// notification options
	// defaults (needed to override old settings when we re-import into NagiosQL
	$new_service["notifications_enabled"]="1";
	$new_service["notification_options"]="w,u,c,r,f,s";
	$new_service["first_notification_delay"]="0";
	if($notification_options=="delayed")
		$new_service["first_notification_delay"]=$first_notification_delay;
	else if($notification_options=="none"){
		$new_service["notification_options"]="n";
		$new_service["notifications_enabled"]="0";
		}
		
	//echo "<BR>NEW SERVICE:<BR>";
	//print_r($new_service);
	//exit();
	
	// COMMIT THE SERVICE
	
	// log it
	send_to_audit_log("User reconfigured service '".$service."' on host '".$host."'",AUDITLOGTYPE_MODIFY);

	// create the import file
	$fname=$host; // use the hostname as part of the import file
	$fh=create_nagioscore_import_file($fname);

	// write the object definition to file
	fprintf($fh,"define service {\n");
	//print_r($new_service);
	foreach($new_service as $var => $val){
		//echo "PROCESSING $var=$val<BR>\n";
		fprintf($fh,$var."\t%s\n",$val);
		}
	fprintf($fh,"}\n");
	
	// commit the import file
	fclose($fh);
	commit_nagioscore_import_file($fname);	
	
	show_service_commit_complete();
	}

	
function show_service_commit_complete($error=false,$msg=""){
	global $request;
	global $lstr;
	
	// grab variables
	$host=grab_request_var("host","");
	$service=grab_request_var("service","");
	$return=grab_request_var("return","");

	
	do_page_start(array("page_title"=>$lstr['ReconfigureServiceCompletePageTitle']),true);
?>

	<h1><?php echo $lstr['ReconfigureServiceCompletePageHeader'];?></h1>
	
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

<ul class="commandresult">
<?php
	$error=false;
	$id=submit_command(COMMAND_NAGIOSCORE_APPLYCONFIG);
	//echo "COMMAND ID: $id<BR>";
	if($id>0){
		echo "<li class='commandresultok'>"."Configuration submitted for processing..."."</li>\n";
	
		echo "<li class='commandresultwaiting' id='commandwaiting'>"."Waiting for configuration verification...</li>"."</li>\n";
		}
	else{
		echo "<li class='commandresulterror'>"."An error occurred during command submission.  If this problem persists, contact your Nagios administrator."."</li>\n";
		$error=true;
		}
?>
</ul>

	

<div id="commandsuccesscontent" style="visibility: hidden;">
	
	<div class="sectionTitle"><?php echo $lstr['ReconfigureServiceSuccessSectionTitle'];?></div>
	
	<p><?php echo $lstr['ReconfigureServiceSuccessNotes'];?></p>


<?php
	$servicestatus_link=get_service_status_link($host,$service);
?>
	<ul>
	<li><a href="<?php echo $servicestatus_link;?>" target="_blank">View status details for <?php echo htmlentities($host)." / ".htmlentities($service);?></a></li>
<?php
	if(is_admin()==true){
?>
	<li><a href="<?php echo get_base_url();?>admin/?xiwindow=coreconfigsnapshots.php" target="_top">View the latest configuration snapshots</a></li>
<?php
		}
?>
	</ul>
	
	<form method="get" action="<?php echo get_base_url()."includes/components/xicore/status.php";?>">
	<input type="hidden" name="show" value="servicedetail">
	<input type="hidden" name="host" value="<?php echo encode_form_val($host);?>">
	<input type="hidden" name="service" value="<?php echo encode_form_val($service);?>">
	<div id="formButtons">
	<input type="submit" class="submitbutton" name="backButton" value="<?php echo $lstr['ContinueButton'];?>"/>
	</div>
	</form>

</div>

<div id="commanderrorcontent" style="visibility: hidden;">

	<div class="sectionTitle"><?php echo $lstr['ReconfigureServiceErrorSectionTitle'];?></div>
	
	<p><?php echo $lstr['ReconfigureServiceErrorNotes'];?></p>

<?php
	if(is_admin()==true){
?>
	<p><a href="<?php echo get_base_url();?>admin/?xiwindow=coreconfigsnapshots.php" target="_top">View the latest configuration snapshots</a></p>
<?php
		}
?>
	<form method="get" action="<?php echo get_base_url()."includes/components/xicore/status.php";?>">
	<input type="hidden" name="show" value="servicedetail">
	<input type="hidden" name="host" value="<?php echo encode_form_val($host);?>">
	<input type="hidden" name="service" value="<?php echo encode_form_val($service);?>">
	<div id="formButtons">
	<input type="submit" class="submitbutton" name="backButton" value="<?php echo $lstr['BackButton'];?>"/>
	</div>
	</form>
	
</div>

<script type="text/javascript">

get_apply_config_result(<?php echo $id;?>);

function get_apply_config_result(command_id){

	$(this).everyTime(1 * 1000, "commandstatustimer", function(i) {
	
		$(".commandresultwaiting").append(".");

		var csdata=get_ajax_data("getcommandstatus",command_id);
		eval('var csobj='+csdata);
		if(csobj.status_code==2){
			if(csobj.result_code==0){
				$('.commandresultwaiting').each(function(){
					$(this).removeClass("commandresultwaiting");
					$(this).addClass("commandresultok");
					});
				$('#commandsuccesscontent').each(function(){
					$(this).css("visibility","visible");
					});
				$('ul.commandresult').append("<li class='commandresultok'>Configuration applied successfully.</li>");
				}
			else{
				$('.commandresultwaiting').each(function(){
					$(this).removeClass("commandresultwaiting");
					$(this).addClass("commandresulterror");
					});
				$('#commandsuccesscontent').each(function(){
					$(this).css("display","none")
					});
				$('#commanderrorcontent').each(function(){
					$(this).css("visibility","visible")
					});
				$('ul.commandresult').append("<li class='commandresulterror'>Configuration verification failed.</li>");
				}
			$(this).stopTime("commandstatustimer");
			}
		});
		
	}
</script>

	
	
<?php

	do_page_end(true);
	exit();
	}

	
	
function show_host_config($error=false,$msg=""){
	global $lstr;

	// grab variables
	$host=grab_request_var("host","");
	$return=grab_request_var("return","");

	// can this host be configured??
	check_host_config_prereqs($host);
	
	// default values
	$address="";
	$check_interval="";
	$retry_interval="";
	$max_check_attempts="";
	$check_command="";
	$first_notification_delay="";
	$notification_interval="";
	$notification_options=""; // none, immediate, delayed
	$notification_targets=array(
		"myself" => "",
		"contacts" => "",
		"contactgroups" => "",
		);
	$contacts="";
	$contact_names=array();
	$contact_groups="";
	$contact_group_names=array();
	$contact_id=array();
	$contactgroup_id=array();
	$host_groups="";
	$hostgroup_id=array();
	$host_group_names=array();
	$parent_hosts="";
	$parenthost_id=array();
	$parent_host_names=array();

	// read existing configuration
	$ha=nagiosql_read_host_config_from_file($host);
	
	//print_r($ha);
	
	// process values
	$val=grab_array_var($ha,"address");
	if($val!="")
		$address=$val;
	$val=grab_array_var($ha,"check_interval");
	if($val!="")
		$check_interval=$val;
	$val=grab_array_var($ha,"retry_interval");
	if($val!="")
		$retry_interval=$val;
	$val=grab_array_var($ha,"max_check_attempts");
	if($val!="")
		$max_check_attempts=$val;
	$val=grab_array_var($ha,"check_command");
	if($val!="")
		$check_command=$val;

	$notifications_enabled=1;
	$val=grab_array_var($sa,"notifications_enabled");
	if($val!="")
		$notifications_enabled=$val;

	$val=grab_array_var($ha,"first_notification_delay");
	if($val!="")
		$first_notification_delay=$val;

	$val=grab_array_var($ha,"notification_interval");
	if($val!="")
		$notification_interval=$val;
		
	$val=grab_array_var($ha,"notification_options");
	if($val=="n" || $notifications_enabled==0)
		$notification_options="none";
	else if($first_notification_delay!="" && $first_notification_delay!="0")
		$notification_options="delayed";
	else
		$notification_options="immediate";
		
	$val=grab_array_var($ha,"contacts");
	if($val!="")
		$contacts=$val;
	$val=grab_array_var($ha,"contact_groups");
	if($val!="")
		$contact_groups=$val;

	$val=grab_array_var($ha,"hostgroups");
	if($val!="")
		$host_groups=$val;

	$val=grab_array_var($ha,"parents");
	if($val!="")
		$parent_hosts=$val;
		
	//echo "HOSTGROUPS: $host_groups<BR>";
	//echo "PARENTS: $parent_hosts<BR>";

	// process contacts
	$c=explode(",",$contacts);
	// get user's name
	$username=get_user_attr(0,'username');
	foreach($c as $cid => $cname){
		// "myself"
		if($cname==$username){
			$notification_targets["myself"]="on";
			continue;
			}
		if($cname=="null" || $cname=="")
			continue;
		// other contacts
		$contact_names[]=$cname;
		}
	if(count($contact_names)>0)
		$notification_targets["contacts"]="on";
		
	// process contactgroups
	$c=explode(",",$contact_groups);
	foreach($c as $cid => $cname){
		if($cname=="null" || $cname=="")
			continue;
		$contact_group_names[]=$cname;
		}
	if(count($contact_group_names)>0 )
		$notification_targets["contactgroups"]="on";

	// set some defaults for update purposes
	if($first_notification_delay=="")
		$first_notification_delay=15;

	// process hostgroups
	$c=explode(",",$host_groups);
	foreach($c as $cid => $cname){
		if($cname=="null" || $cname=="")
			continue;
		$host_group_names[]=$cname;
		}

	// process hostgroups
	$c=explode(",",$parent_hosts);
	foreach($c as $cid => $cname){
		if($cname=="null" || $cname=="")
			continue;
		$parent_host_names[]=$cname;
		}

		
	do_page_start(array("page_title"=>$lstr['ReconfigureHostPageTitle']),true);
?>

	<h1><?php echo $lstr['ReconfigureHostPageHeader'];?></h1>
	
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

<?php
	/*
	echo "<BR>HOST:<BR>";
	print_r($ha);
	echo "<BR>";
	
	echo "<BR>CONTACT NAMES:<BR>";
	print_r($contact_names);
	echo "<BR>";

	echo "<BR>CONTACT GROUP NAMES:<BR>";
	print_r($contact_group_names);
	echo "<BR>";
	*/
?>

<?php
	if(is_advanced_user()==true){
		$url=get_base_url()."config/nagioscorecfg/";
		echo "<p>Note: You may update basic settings for the host below or use the <a href='".$url."' target='_top'>Nagios Core Config Manager</a> to modify advanced settings for this host.  Host attribute values which are inherited from advanced templates are not shown below.</p>";
		}
?>

	
	<form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">
	<?php echo get_nagios_session_protector();?>
	<input type="hidden" name="apply" value="1" />
	<input type="hidden" name="host" value="<?php echo encode_form_val($host);?>" />
	<input type="hidden" name="return" value="<?php echo encode_form_val($return);?>" />
	<input type="hidden" name="originalhost" value="<?php echo base64_encode(serialize($ha));?>" />
	
	<script type="text/javascript">
	$(document).ready(function() {
		$("#tabs").tabs();
	});
	</script>
	
	<div id="tabs">
	<ul>
	<li><a href="#attributes-tab">Attributes</a></li>
	<li><a href="#monitoring-tab">Monitoring</a></li>
	<li><a href="#notifications-tab">Notifications</a></li>
	<li><a href="#groups-tab">Groups</a></li>
<?php
	if(is_advanced_user()){
?>
	<li><a href="#parents-tab">Parents</a></li>
<?php	
		}
?>
	</ul>

	<div id="attributes-tab">
	
	<div class="sectionTitle">Basic Host Settings</div>
	
	<p>Change basic host settings.</p>
	
	<table>

	<tr>
	<td valign="top">
	<label>Host Name:</label><br class="nobr" />
	</td>
	<td>
<input type="text" size="40" name="hostname2" id="hostname2" value="<?php echo encode_form_val($host);?>" class="textfield" disabled /><br class="nobr" />
	The unique name of the host.
	</td>
	</tr>
	
	<tr>
	<td valign="top">
	<label>Address:</label><br class="nobr" />
	</td>
	<td>
<input type="text" size="40" name="address" id="address" value="<?php echo encode_form_val($address);?>" class="textfield" /><br class="nobr" />
	The IP address or FQDNS name of the host.
	</td>
	</tr>
	
	
	</table>

	</div>
	<div id="monitoring-tab">

	<div class="sectionTitle">Monitoring Settings</div>
	
	<p>Specify the parameters that determine how the host should be monitored.</p>
	
	<table>

	
	<tr>
	<td><b>Under normal circumstances...</b><br>Monitor the host every <input type="text" size="2" name="check_interval" id="check_interval" value="<?php echo encode_form_val($check_interval);?>" class="textfield" /> minutes.</td>
	</tr>
	
	<tr>
	<td><b>When a potential problem is first detected...</b><br>Re-check the host every <input type="text" size="2" name="retry_interval" id="retry_interval" value="<?php echo $retry_interval;?>" class="textfield" /> minutes up to <input type="text" size="2" name="max_check_attempts" id="max_check_attempts" value="<?php echo encode_form_val($max_check_attempts);?>" class="textfield" /> times before generating an alert.</td>
	</tr>
	
<?php
	if(is_advanced_user()==true){
?>
	<tr>
	<td><b>Monitor the host with this command...</b> (Advanced users only)<br><input type="text" size="60" name="check_command" id="check_command" value="<?php echo htmlentities($check_command);?>" class="textfield" /></td>
	</tr>
<?php
		}
?>
	
	</table>

	</div>
	<div id="notifications-tab">

	<div class="sectionTitle">Notification Settings</div>
	
	<p>Specify the parameters that determine how notifications should be sent for the host.</p>
	
	<table>
	
	<tr>
	<td>
	<b>When a problem is detected...</b><br>
	<input type="radio" name="notification_options" value="none" <?php echo is_checked($notification_options,"none");?>>Don't send any notifications<br>
	<input type="radio" name="notification_options" value="immediate"  <?php echo is_checked($notification_options,"immediate");?>>Send a notification immediately<br>
	<input type="radio" name="notification_options" value="delayed" <?php echo is_checked($notification_options,"delayed");?>>Wait <input type="text" size="2" name="first_notification_delay" id="first_notification_delay" value="<?php echo $first_notification_delay;?>" class="textfield" /> minutes before sending a notification
	</td>
	</tr>
	
	<tr>
	<td><b>If problems persist...</b><br>Send a notification every <input type="text" size="2" name="notification_interval" id="notification_interval" value="<?php echo encode_form_val($notification_interval);?>" class="textfield" /> minutes until the problem is resolved.</td>
	</tr>
	
	<tr>
	<td><b>Send alert notifications to...</b><br>
	
	<script type="text/javascript">
	function check_contacts(){
		$('#notification_targets_contacts').attr('checked',true);
		}
	function check_contactgroups(){
		$('#notification_targets_contactgroups').attr('checked',true);
		}
	</script>
	
	<input type="checkbox" name="notification_targets[myself]" id="notification_targets_myself" <?php echo is_checked($notification_targets["myself"],"on");?>>Myself (<a href="<?php echo get_base_url()."account/?xiwindow=notifyprefs.php";?>" target="_blank">Adjust settings</a>)<br>
	
	<input type="checkbox" name="notification_targets[contacts]" id="notification_targets_contacts" <?php echo is_checked($notification_targets["contacts"],"on");?>>Other individual contacts<br>
	<div style="overflow: auto; width: 275px; height: 80px; border: 1px solid gray; margin: 0 0 0 35px;">
	<?php
	$xml=get_xml_contact_objects(array("is_active"=>1,"orderby"=>"contact_name:a"));
	//print_r($xml);
	$username=get_user_attr(0,'username');
	foreach($xml->contact as $c){
	
		$cid=strval($c->attributes()->id);
		$cname=strval($c->contact_name);
		$calias=strval($c->alias);
	
		if(!strcmp($cname,$username))
			continue;
			
		if(array_key_exists($cid,$contact_id))
			$ischecked="CHECKED";
		else if(in_array($cname,$contact_names))
			$ischecked="CHECKED";
		else
			$ischecked="";
		
		echo "<input type='checkbox' name='contact_id[".$cid."]' ".$ischecked." onclick='check_contacts()'>".$calias." (".$cname.")<br>";
		}
	?>
	</div>
	
	<input type="checkbox" name="notification_targets[contactgroups]" id="notification_targets_contactgroups" <?php echo is_checked($notification_targets["contactgroups"],"on");?>>Specific contact groups<br>
	<div style="overflow: auto; width: 275px; height: 80px; border: 1px solid gray; margin: 0 0 0 35px;">
	<?php
	$xml=get_xml_contactgroup_objects(array("is_active"=>1,"orderby"=>"contactgroup_name:a"));
	//print_r($xml);
	foreach($xml->contactgroup as $c){
	
		$cid=strval($c->attributes()->id);
		$cname=strval($c->contactgroup_name);
		$calias=strval($c->alias);

		if(array_key_exists($cid,$contactgroup_id))
			$ischecked="CHECKED";
		else if(in_array($cname,$contact_group_names))
			$ischecked="CHECKED";
		else
			$ischecked="";
		
		echo "<input type='checkbox' name='contactgroup_id[".$cid."]' ".$ischecked." onclick='check_contactgroups()'>".$calias." (".$cname.")<br>";
		}
	?>
	</div>
	
	</td>
	</tr>
	
	</table>
	
	</div>
	<div id="groups-tab">

	<div class="sectionTitle">Host Groups</div>
	
	<p>Define which hostgroup(s) the host should belong to (if any).</p>
	
	
	<div style="overflow: auto; width: 275px; height: 80px; border: 1px solid gray; margin: 0 0 0 35px;">
	<?php
	$xml=get_xml_hostgroup_objects(array("is_active"=>1,"orderby"=>"hostgroup_name:a"));
	//print_r($xml);
	foreach($xml->hostgroup as $hg){
	
		$hgid=strval($hg->attributes()->id);
		$hgname=strval($hg->hostgroup_name);
		$hgalias=strval($hg->alias);
	
		if(array_key_exists($hgid,$hostgroup_id))
			$ischecked="CHECKED";
		else if(in_array($hgname,$host_group_names))
			$ischecked="CHECKED";
		else
			$ischecked="";
		
		echo "<input type='checkbox' name='hostgroup_id[".$hgid."]' ".$ischecked.">".$hgalias." (".$hgname.")<br>";
		}
	?>
	</div>
	
	</div>
	<div id="parents-tab">
	

	<div class="sectionTitle">Parent Host</div>
	
	<p>Define which host(s) are considered the parents of the the monitored host (if any). Note: Typically only one (1) host is specified as a parent.</p>
	
	<div style="overflow: auto; width: 275px; height: 80px; border: 1px solid gray; margin: 0 0 0 35px;">
	<?php
	$xml=get_xml_host_objects(array("is_active"=>1,"orderby"=>"host_name:a"));
	//print_r($xml);
	foreach($xml->host as $h){
	
		$hid=strval($h->attributes()->id);
		$hname=strval($h->host_name);
		$halias=strval($h->alias);
	
		if(array_key_exists($hid,$parenthost_id))
			$ischecked="CHECKED";
		else if(in_array($hname,$parent_host_names))
			$ischecked="CHECKED";
		else
			$ischecked="";
		
		echo "<input type='checkbox' name='parenthost_id[".$hid."]' ".$ischecked.">".$halias." (".$hname.")<br>";
		}
	?>
	</div>
	
	</div><!--parents-tab-->
	</div><!--tabs-->


	

	<div id="formButtons">
	<input type="submit" class="submitbutton" name="updateButton" value="<?php echo $lstr['UpdateButton'];?>" />
	<input type="submit" class="submitbutton" name="cancelButton" value="<?php echo $lstr['CancelButton'];?>"/>
	</div>
	</form>


<?php
	}
	
	
function redirect_host_config($error=false,$msg=""){
	global $lstr;
	

	// grab variables
	$host=grab_request_var("host","");
	$return=grab_request_var("return","");

	do_page_start(array("page_title"=>$lstr['ReconfigureHostPageTitle']),true);
?>

	<h1><?php echo $lstr['ReconfigureHostPageHeader'];?></h1>
	
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
<?php
	echo "This host appears to make use of an advanced configuration.  ";
	if(is_advanced_user()==true){
		$url=get_base_url()."config/nagioscorecfg/";
		echo "Use the <a href='".$url."' target='_top'>Nagios Core Config Manager</a> to modify the settings for this host.";
		}
	else
		echo "Contact your Nagios administrator to modify the settings for this host.";
?>
	</p>
	
	<form method="get" action="<?php echo get_base_url()."includes/components/xicore/status.php";?>">
	<input type="hidden" name="show" value="hostdetail">
	<input type="hidden" name="host" value="<?php echo encode_form_val($host);?>">
	<div id="formButtons">
	<input type="submit" class="submitbutton" name="backButton" value="<?php echo $lstr['BackButton'];?>"/>
	</div>
	</form>

<?php
	exit();
	}
	
	
function check_host_config_prereqs($host){

	if(is_host_configurable($host)==false)
		redirect_host_config();
	}


function do_config_host($error=false,$msg=""){
	global $request;
	global $lstr;
	
	
	// check session
	check_nagios_session_protector();

	// grab variables
	$host=grab_request_var("host","");
	$return=grab_request_var("return","");

	// user cancelled, so redirect them
	if(isset($request["cancelButton"])){
		$url=get_return_url($return,$host);
		header("Location: ".$url);
		exit();
		}
		
	//echo "<BR>REQUEST:<BR>";
	//print_r($request);
	
	$original_host_s=grab_request_var("originalhost","");
	if($original_host_s=="")
		$original_host=array();
	else
		$original_host=unserialize(base64_decode($original_host_s));
		
	//echo "<BR>ORIGINAL HOST:<BR>";
	//print_r($original_host);
	
	
	// grab config variables
	$address=grab_request_var("address");
	$check_interval=grab_request_var("check_interval");
	$retry_interval=grab_request_var("retry_interval");
	$max_check_attempts=grab_request_var("max_check_attempts");
	$check_command=grab_request_var("check_command");
	$first_notification_delay=grab_request_var("first_notification_delay");
	$notification_interval=grab_request_var("notification_interval");
	$notification_options=grab_request_var("notification_options");
	$notification_targets=grab_request_var("notification_targets",array());
	$contact_id=grab_request_var("contact_id",array());
	$contactgroup_id=grab_request_var("contactgroup_id",array());
	$hostgroup_id=grab_request_var("hostgroup_id",array());
	$parenthost_id=grab_request_var("parenthost_id",array());
	
	// resolve contact names
	$contact_names="";
	$total_contacts=0;
	// this user
	if(array_key_exists("myself",$notification_targets)){
		$contact_names.=get_user_attr(0,"username");
		$total_contacts++;
		}	
	// additional individual contacts
	if(array_key_exists("contacts",$notification_targets)){
		$ids="";
		foreach($contact_id as $id => $val){
			$ids.=",".$id;
			}
		if($ids!=""){
			$args=array(
				"is_active" => 1,
				"contact_id" => "in:".$ids,
				);
			//echo "IDS: $ids<BR>";
			//print_r($args);	
			$xml=get_xml_contact_objects($args);
			foreach($xml->contact as $c){
				if($total_contacts>0)
					$contact_names.=",";
				$contact_names.=$c->contact_name;
				$total_contacts++;
				}
			}
		}
	//echo "<BR>CONTACTS: $contact_names<BR>";

	// resolve contactgroup names
	$contactgroup_names="";
	$total_contactgroups=0;
	// additional individual contactgroups
	if(array_key_exists("contactgroups",$notification_targets)){
		$ids="";
		foreach($contactgroup_id as $id => $val){
			$ids.=",".$id;
			}
		if($ids!=""){
			$args=array(
				"is_active" => 1,
				"contactgroup_id" => "in:".$ids,
				);
			//echo "IDS: $ids<BR>";
			//print_r($args);	
			$xml=get_xml_contactgroup_objects($args);
			foreach($xml->contactgroup as $cg){
				if($total_contactgroups>0)
					$contactgroup_names.=",";
				$contactgroup_names.=$cg->contactgroup_name;
				$total_contactgroups++;
				}
			}
		}
	//echo "<BR>CONTACTGROUPS: $contactgroup_names<BR>";

	// resolve hostgroup names
	$hostgroup_names="";
	$total_hostgroups=0;
	$ids="";
	if(is_array($hostgroup_id)){
		foreach($hostgroup_id as $id => $val){
			$ids.=",".$id;
			}
		if($ids!=""){
			$args=array(
				"is_active" => 1,
				"hostgroup_id" => "in:".$ids,
				);
			//echo "IDS: $ids<BR>";
			//print_r($args);	
			$xml=get_xml_hostgroup_objects($args);
			foreach($xml->hostgroup as $hg){
				if($total_hostgroups>0)
					$hostgroup_names.=",";
				$hostgroup_names.=$hg->hostgroup_name;
				$total_hostgroups++;
				}
			}
		}
	//echo "<BR>HOSTGROUPS: $hostgroup_names<BR>";
	
	// resolve parent host names
	$parenthost_names="";
	$total_parenthosts=0;
	$ids="";
	if(is_array($parenthost_id)){
		foreach($parenthost_id as $id => $val){
			$ids.=",".$id;
			}
		if($ids!=""){
			$args=array(
				"is_active" => 1,
				"host_id" => "in:".$ids,
				);
			//echo "IDS: $ids<BR>";
			//print_r($args);	
			$xml=get_xml_host_objects($args);
			foreach($xml->host as $h){
				if($total_parenthosts>0)
					$parenthost_names.=",";
				$parenthost_names.=$h->host_name;
				$total_parenthosts++;
				}
			}
		}
	//echo "<BR>PARENTHOSTS: $parenthost_names<BR>";
	
	//exit();
	
	// new object config array
	$new_host=$original_host;
	
	// apply config settings to new object
	if($address!="")
		$new_host["address"]=$address;
	if($check_interval!="")
		$new_host["check_interval"]=$check_interval;
	if($retry_interval!="")
		$new_host["retry_interval"]=$retry_interval;
	if($max_check_attempts!="")
		$new_host["max_check_attempts"]=$max_check_attempts;
	if($check_command!="")
		$new_host["check_command"]=$check_command;
	if($notification_interval!="")
		$new_host["notification_interval"]=$notification_interval;
		
	// contacts (only if modified)
	if($contact_names!="")
		$new_host["contacts"]=$contact_names;
	else{
		$oc=grab_array_var($original_host,"contacts");
		if($oc!="")
			$new_host["contacts"]="null";
		}
	// contactgroups (only if modified)
	if($contactgroup_names!="")
		$new_host["contact_groups"]=$contactgroup_names;
	else{
		$ocg=grab_array_var($original_host,"contact_groups");
		if($ocg!="")
			$new_host["contact_groups"]="null";
		}

	// hostgroups (only if modified)
	if($hostgroup_names!="")
		$new_host["hostgroups"]=$hostgroup_names;
	else{
		$ocg=grab_array_var($original_host,"hostgroups");
		if($ocg!="")
			$new_host["hostgroups"]="null";
		}

	// parents (only if modified)
	if($parenthost_names!="")
		$new_host["parents"]=$parenthost_names;
	else{
		$ocg=grab_array_var($original_host,"parents");
		if($ocg!="")
			$new_host["parents"]="null";
		}

	// notification options
	// defaults (needed to override old settings when we re-import into NagiosQL
	$new_host["notifications_enabled"]="1";
	$new_host["notification_options"]="d,u,r,f,s";
	$new_host["first_notification_delay"]="0";
	if($notification_options=="delayed")
		$new_host["first_notification_delay"]=$first_notification_delay;
	else if($notification_options=="none"){
		$new_host["notification_options"]="n";
		$new_host["notifications_enabled"]="0";
		}
		
	//echo "<BR>NEW HOST:<BR>";
	//print_r($new_host);
	
	// COMMIT THE HOST
	
	// log it
	send_to_audit_log("User reconfigured host '".$host."'",AUDITLOGTYPE_MODIFY);

	// create the import file
	$fname=$host; // use the hostname as part of the import file
	$fh=create_nagioscore_import_file($fname);

	// write the object definition to file
	fprintf($fh,"define host {\n");
	foreach($new_host as $var => $val){
		fprintf($fh,$var."\t".$val."\n");
		}
	fprintf($fh,"}\n");
	
	// commit the import file
	fclose($fh);
	commit_nagioscore_import_file($fname);	
	
	show_host_commit_complete();
	}

	
function show_host_commit_complete($error=false,$msg=""){
	global $request;
	global $lstr;
	
	// grab variables
	$host=grab_request_var("host","");
	$return=grab_request_var("return","");

	
	do_page_start(array("page_title"=>$lstr['ReconfigureHostCompletePageTitle']),true);
?>

	<h1><?php echo $lstr['ReconfigureHostCompletePageHeader'];?></h1>
	
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

<ul class="commandresult">
<?php
	$error=false;
	$id=submit_command(COMMAND_NAGIOSCORE_APPLYCONFIG);
	//echo "COMMAND ID: $id<BR>";
	if($id>0){
		echo "<li class='commandresultok'>"."Configuration submitted for processing..."."</li>\n";
	
		echo "<li class='commandresultwaiting' id='commandwaiting'>"."Waiting for configuration verification...</li>"."</li>\n";
		}
	else{
		echo "<li class='commandresulterror'>"."An error occurred during command submission.  If this problem persists, contact your Nagios administrator."."</li>\n";
		$error=true;
		}
?>
</ul>

	

<div id="commandsuccesscontent" style="visibility: hidden;">
	
	<div class="sectionTitle"><?php echo $lstr['ReconfigureHostSuccessSectionTitle'];?></div>
	
	<p><?php echo $lstr['ReconfigureHostSuccessNotes'];?></p>


<?php
	$hoststatus_link=get_host_status_link($host);
?>
	<ul>
	<li><a href="<?php echo $hoststatus_link;?>" target="_blank">View status details for <?php echo htmlentities($host);?></a></li>
<?php
	if(is_admin()==true){
?>
	<li><a href="<?php echo get_base_url();?>admin/?xiwindow=coreconfigsnapshots.php" target="_top">View the latest configuration snapshots</a></li>
<?php
		}
?>
	</ul>
	
	<form method="get" action="<?php echo get_base_url()."includes/components/xicore/status.php";?>">
	<input type="hidden" name="show" value="hostdetail">
	<input type="hidden" name="host" value="<?php echo encode_form_val($host);?>">
	<div id="formButtons">
	<input type="submit" class="submitbutton" name="backButton" value="<?php echo $lstr['ContinueButton'];?>"/>
	</div>
	</form>

</div>

<div id="commanderrorcontent" style="visibility: hidden;">

	<div class="sectionTitle"><?php echo $lstr['ReconfigureHostErrorSectionTitle'];?></div>
	
	<p><?php echo $lstr['ReconfigureHostErrorNotes'];?></p>

<?php
	if(is_admin()==true){
?>
	<p><a href="<?php echo get_base_url();?>admin/?xiwindow=coreconfigsnapshots.php" target="_top">View the latest configuration snapshots</a></p>
<?php
		}
?>
	<form method="get" action="<?php echo get_base_url()."includes/components/xicore/status.php";?>">
	<input type="hidden" name="show" value="hostdetail">
	<input type="hidden" name="host" value="<?php echo encode_form_val($host);?>">
	<div id="formButtons">
	<input type="submit" class="submitbutton" name="backButton" value="<?php echo $lstr['BackButton'];?>"/>
	</div>
	</form>
	
</div>

<script type="text/javascript">

get_apply_config_result(<?php echo $id;?>);

function get_apply_config_result(command_id){

	$(this).everyTime(1 * 1000, "commandstatustimer", function(i) {
	
		$(".commandresultwaiting").append(".");

		var csdata=get_ajax_data("getcommandstatus",command_id);
		eval('var csobj='+csdata);
		if(csobj.status_code==2){
			if(csobj.result_code==0){
				$('.commandresultwaiting').each(function(){
					$(this).removeClass("commandresultwaiting");
					$(this).addClass("commandresultok");
					});
				$('#commandsuccesscontent').each(function(){
					$(this).css("visibility","visible");
					});
				$('ul.commandresult').append("<li class='commandresultok'>Configuration applied successfully.</li>");
				}
			else{
				$('.commandresultwaiting').each(function(){
					$(this).removeClass("commandresultwaiting");
					$(this).addClass("commandresulterror");
					});
				$('#commandsuccesscontent').each(function(){
					$(this).css("display","none")
					});
				$('#commanderrorcontent').each(function(){
					$(this).css("visibility","visible")
					});
				$('ul.commandresult').append("<li class='commandresulterror'>Configuration verification failed.</li>");
				}
			$(this).stopTime("commandstatustimer");
			}
		});
		
	}
</script>

	
	
<?php

	do_page_end(true);
	exit();
	}

	



function get_return_url($return,$host,$service=""){

	$url="";

	switch($return){
		case "servicedetail";
			$url=get_service_status_detail_link($host,$service);
			break;
		case "hostdetail";
			$url=get_host_status_detail_link($host);
			break;
		default:
			$url="main.php";
			break;
		}
		
	return $url;
	}
	
	
	
?>
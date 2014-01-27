<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: main.php 370 2010-11-09 12:48:24Z egalstad $

require_once(dirname(__FILE__).'/../includes/common.inc.php');

require_once(dirname(__FILE__).'/../includes/configwizards.inc.php');


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
	
	$delete=grab_request_var("delete");
	$configure=grab_request_var("configure");
	
	if($delete==1)
		do_delete();
	else if($configure==1)
		do_configure();
	else
		show_objects();
	
	exit;
	}


function do_configure(){

	$hosts=grab_request_var("host",array());
	$services=grab_request_var("service",array());

	// check session
	check_nagios_session_protector();
		
	$errmsg=array();
	$errors=0;
	
	$wizard_url="http://exchange.nagios.org/directory/Addons/Configuration/Configuration-Wizards/Unconfigured-Passive-Object-Wizard/details";
	
	// check for errors
	if(in_demo_mode()==true)
		$errmsg[$errors++]=$lstr['DemoModeChangeError'];
	if(count($hosts)==0 && count($services)==0)
		$errmsg[$errors++]="No objects selected.";
	if(!file_exists("/usr/local/nagiosxi/html/includes/configwizards/passiveobject/passiveobject.inc.php"))
		$errmsg[$errors++]="You must install the unconfigured passive object wizard to configure the selected hosts and services.  You can get the wizard <a href='".$wizard_url."' target='_blank'>here</a>.";

	if($errors>0){
		show_objects(true,$errmsg);
		}
		
	$url="../config/monitoringwizard.php?wizard=passiveobject&update=1&nextstep=3&nsp=".get_nagios_session_protector_id();
	foreach($hosts as $host_name => $id)
		$url.="&host[".urlencode($host_name)."]=1";
	foreach($services as $host_name => $service_name)
		$url.="&service[".urlencode($host_name)."]=".urlencode($service_name);

	//show_objects(false,"Configured...");
	header("Location: ".$url);
	}
	
			
function do_delete(){

	$hosts=grab_request_var("host",array());
	$services=grab_request_var("service",array());

	// check session
	check_nagios_session_protector();
	
	
	$errmsg=array();
	$errors=0;

	// check for errors
	if(in_demo_mode()==true)
		$errmsg[$errors++]=$lstr['DemoModeChangeError'];
	if(count($hosts)==0 && count($services)==0)
		$errmsg[$errors++]="No objects selected";

	if($errors>0){
		show_objects(true,$errmsg);
		}
		
	// load object file
	$datas=file_get_contents("/usr/local/nagiosxi/var/corelog.newobjects");
	$newobjects=unserialize($datas);
	
	//echo "<BR>BEFORE DELETE:<BR>";	
	//print_r($newobjects);
	
	// delete hosts
	foreach($hosts as $hn => $id){
		// log it
		send_to_audit_log("User deleted host '".$hn."' from unconfigured objects",AUDITLOGTYPE_DELETE);		
		unset($newobjects[$hn]);
		}
	// delete services
	foreach($services as $hn => $sn){
		//echo "UNSETTING $hn / $sn<BR>";
		// log it
		send_to_audit_log("User deleted service '".$sn."' (on host '".$hn."') from unconfigured objects",AUDITLOGTYPE_DELETE);		
		unset($newobjects[$hn]["services"][$sn]);
		}
	
	//echo "<BR>AFTER DELETE:<BR>";	
	//print_r($newobjects);
	
	file_put_contents("/usr/local/nagiosxi/var/corelog.newobjects",serialize($newobjects));
	
	//show_objects(false,"Objects deleleted.");
	header("Location: ?deleted=1");
	}
	
	
function show_objects($error=false,$msg=""){
	global $request;
	global $lstr;
	
	$deleted=grab_request_var("deleted");
	$configured=grab_request_var("configured");
	
	$wizard_url="http://exchange.nagios.org/directory/Addons/Configuration/Configuration-Wizards/Unconfigured-Passive-Object-Wizard/details";
	
	//is the listener enabled? (as of 2011r2.3) -MG
	$listen = is_null(get_option('enable_unconfigured_objects')) ? true : get_option('enable_unconfigured_objects');
	$perflink = "<a href='performance.php' title='Performance Settings'>Performance Settings</a>"; 
	
	if($error==false && $deleted==1)
		$msg="Objects deleted.";
	if(!$listen){
		$error = true;
		$msg="Unconfigured objects listener is currently disabled.  This feature can be enabled from the {$perflink} page 
by selecting the 'Subsystem' tab.";
	}
	if($error==false && $configured==1)
		$msg="Objects configured.";
	
	$baseurl=get_base_url();
	
	do_page_start(array("page_title"=>$lstr['MissingObjectsPageTitle']),true);
?>

	<h1><?php echo $lstr['MissingObjectsPageHeader'];?></h1>
	

<?php
	display_message($error,false,$msg);
?>
	
	<p>
	This page shows host and services that check results have been received for, but which have not yet been configured in Nagios.  Passive checks may be received by NSCA or NRDP (as defined in your <a href="dtinbound.php">inbound transfer settings</a>) or through the direct check submission API.
	</p>
	<p>
	You may delete unneeded host and services or add them to your monitoring configuration through this page.
	</p>
<?php
	if(!file_exists("/usr/local/nagiosxi/html/includes/configwizards/passiveobject/passiveobject.inc.php"))
		echo "<p><strong>Note:</strong> You must install the unconfigured passive object wizard to configure the selected hosts and services.  You can get the wizard from <a href='".$wizard_url."' target='_blank'>Nagios Exchange</a>.</p>";
?>

	<form method="get" action="">
	<?php echo get_nagios_session_protector();?>
	
	<script type="text/javascript">
	$(document).ready(function(){
		$("#checkall").click(function(){
			var checked_status = this.checked;
			$("input[type='checkbox']").each(function(){
				this.checked = checked_status;
				});
			});
		});
	</script>

	
	<table class="standardtable">
	<thead>
	<tr><th><input type="checkbox" id="checkall"></th><th>Host</th><th>Service</th><th>Last Seen</th><th>Actions</th></tr>
	</thead>
	<tbody>
<?php
	$datas=@file_get_contents("/usr/local/nagiosxi/var/corelog.newobjects");
	if($datas=="" || $datas==null)
		$newobjects=array();
	else
		$newobjects=@unserialize($datas);
	
	/*
	$newobjects["localhost"]=array(
		"last_seen" => 100,
		"services" => array(
			"Current Load" => 200,
			"New Svc" => 200,
			),
		);
	$newobjects["localhost2"]=0;
	*/
		
	//print_r($newobjects);
		
	$current_host=0;
	$displayed=0;
	foreach($newobjects as $hn => $arr){
	
		$svcs=$arr["services"];
		$total_services=count($svcs);
		
		// skip if host/service already exists
		if($total_services==0 && host_exists($hn)==true)
			continue;
		else if($total_services>0){
			$missing=0;
			foreach($svcs as $sn => $sarr){
				if(service_exists($hn,$sn)==true)
					continue;
				$missing++;
				}
			if($missing==0)
				continue;
			}
			
		$displayed++;
		
		if($current_host>0)
			echo "<tr><td colspan='5'></td></tr>";

		echo "<tr>";
		echo "<td rowspan='".($total_services+1)."'><input type='checkbox' name='host[".$hn."]'></td>";
		echo "<td rowspan='".($total_services+1)."'>".$hn."</td>";
		echo "<td>-</td>";
		echo "<td>".get_datetime_string($arr["last_seen"])."</td>";
		echo "<td>";
		echo "<a href='?delete=1&amp;host[".$hn."]=1&nsp=".get_nagios_session_protector_id()."'><img class='tableItemButton' src='".theme_image("b_delete.png")."' border='0' alt='".$lstr['DeleteAlt']."' title='".$lstr['DeleteAlt']."'></a>";
		echo "<a href='?configure=1&amp;host[".$hn."]=1&nsp=".get_nagios_session_protector_id()."'><img class='tableItemButton' src='".theme_image("b_next.png")."' border='0' alt='Configure' title='Configure'></a>";
		echo "</td>";
		echo "</tr>";
		
		$svcs=$arr["services"];
		if($total_services>0){
			foreach($svcs as $sn => $sarr){
			
				if(service_exists($hn,$sn)==true)
					continue;
			
				echo "<tr>";
				echo "<td>".$sn."</td>";
				echo "<td>".get_datetime_string($arr["last_seen"])."</td>";
				echo "<td>";
				echo "<a href='?delete=1&amp;service[".$hn."]=".$sn."&nsp=".get_nagios_session_protector_id()."'><img class='tableItemButton' src='".theme_image("b_delete.png")."' border='0' alt='".$lstr['DeleteAlt']."' title='".$lstr['DeleteAlt']."'></a>";
				echo "</td>";
				echo "</tr>";
				}
			}
		
		$current_host++;
		}
	if($displayed==0){
		echo "<tr><td colspan='5'>No unconfigured passive objects found.</td></tr>";
		}
?>	
	</tbody>
	</table>
	
	<div class="tableListMultiOptions">
	<?php echo $lstr['WithSelectedText'];?> 
	<button class="tableMultiItemButton" title="<?php echo $lstr['DeleteAlt'];?>" value="1" name="delete" type="submit">
	<img class="tableMultiButton" src="<?php echo theme_image("b_delete.png");?>" border="0" alt="<?php echo $lstr['DeleteAlt'];?>" title="<?php echo $lstr['DeleteAlt'];?>">
	</button>
	<button class="tableMultiItemButton" title="Configure" value="1" name="configure" type="submit">
	<img class="tableMultiButton" src="<?php echo theme_image("b_next.png");?>" border="0" alt="Configure" title="Configure">
	</button>
	</div>

	
	</form>
		
<?php

	do_page_end(true);
	exit();
	}
?>
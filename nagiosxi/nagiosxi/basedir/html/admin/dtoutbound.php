<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: dtoutbound.php 1314 2012-08-15 15:25:40Z swilkerson $

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

	if(isset($request['update']))
		do_update_options();
	else
		show_options();
	exit;
	}
	
	
function show_options($error=false,$msg=""){
	global $request;
	global $lstr;
	
	//set_option("outbound_data_host_name_filters","");
	
	// default filters
	$outbound_data_host_name_filters=get_option("outbound_data_host_name_filters");
	if($outbound_data_host_name_filters=="")
		$outbound_data_host_name_filters="/^localhost/\n/^127\\.0\\.0\\.1/";
	
	// get options
	$enable_outbound_data_transfer=checkbox_binary(grab_request_var("enable_outbound_data_transfer",get_option('enable_outbound_data_transfer')));
	$enable_nsca_output=checkbox_binary(grab_request_var("enable_nsca_output",get_option('enable_nsca_output')));
	$outbound_data_host_name_filters=grab_request_var("outbound_data_host_name_filters",$outbound_data_host_name_filters);
	$outbound_data_filter_mode=grab_request_var("outbound_data_filter_mode",get_option("outbound_data_filter_mode"));
	$enable_nrdp_output=checkbox_binary(grab_request_var("enable_nrdp_output",get_option('enable_nrdp_output')));

	$r=get_option("nsca_target_hosts");
	if($r!="")
		$nsca_target_hosts=unserialize($r);
	else
		$nsca_target_hosts=array();
	$nsca_target_hosts=grab_request_var("nsca_target_hosts",$nsca_target_hosts);
	
	$r=get_option("nrdp_target_hosts");
	if($r!="")
		$nrdp_target_hosts=unserialize($r);
	else
		$nrdp_target_hosts=array();
	$nrdp_target_hosts=grab_request_var("nrdp_target_hosts",$nrdp_target_hosts);
	
	if(in_demo_mode()==true)
		$send_nsca_password="******";
	
	do_page_start(array("page_title"=>$lstr['OutboundDataTransferPageTitle']),true);

?>

	
	<h1><?php echo $lstr['OutboundDataTransferPageTitle'];?></h1>
	
	<img src="<?php echo theme_image("dtoutbound.png");?>">
	<p>
	These settings affect Nagios XI's ability to send host and service checks results to remote Nagios servers.  Enabling outbound checks is important in distributed monitoring environments.
	</p>

<?php
	display_message($error,false,$msg);
?>

	<form id="manageOptionsForm" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">


	<input type="hidden" name="options" value="1">
	<?php echo get_nagios_session_protector();?>
	<input type="hidden" name="update" value="1">
	
	
<script type="text/javascript">
	$(function() {
		$("#tabs").tabs();
	});
	</script>
	
	<div id="tabs">
	<ul>
		<li><a href="#tab-global">Global Options</a></li>
		<li><a href="#tab-nrdp">NRDP</a></li>
		<li><a href="#tab-nsca">NSCA</a></li>
	</ul>

	<div id="tab-global">
	
	<div class="sectionTitle">Global Settings</div>

	<table class="manageOptionsTable">

	<tr>
	<td valign='top'>
	<label>Enable Outbound Transfers:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" name="enable_outbound_data_transfer" <?php echo is_checked($enable_outbound_data_transfer,1);?> /><br class="nobr" />
	Determines whether or not outbound data transfers are enabled.<br><br>
	</td>
	<tr>
	
	</table>
	
	<div class="sectionTitle">Global Data Filters</div>
	
	<p>Filters allow you to optionally exclude (or only include) certain checks in outbound data based on various criteria.  Filters apply globally to data sent out via both NSCA and NRDP.</p>

	<table class="manageOptionsTable">

	<tr>
	<td valign='top'>
	<label>Filter Mode:</label><br class="nobr" />
	</td>
	<td>
	<select name="outbound_data_filter_mode" class="dropdown">
	<option value="exclude" <?php echo is_selected($outbound_data_filter_mode,"exclude");?>>Exclude matches</option>
	<option value="include" <?php echo is_selected($outbound_data_filter_mode,"include");?>>Include matches (only)</option>
	</select><br class="nobr" />
	The operating mode of any filter(s) you define.<br>
	<b>Exclude matches</b> will send only data that <i>does not</i> match defined filter(s).<br>
	<b>Include matches</b> will send only that that <i>does</i> match defined filter(s).<br><br>
	</td>
	</tr>

	<tr>
	<td valign='top'>
	<label>Host Name Filters:</label><br class="nobr" />
	</td>
	<td>
	Specify one or more regular expressions that match a defined host name pattern.  Specify each pattern/expression on a new line.  Slashes are required.<br>
	Example: <b>/^localhost/</b><br>
<textarea name="outbound_data_host_name_filters" rows="5" cols="40"><?php echo encode_form_val($outbound_data_host_name_filters);?>
</textarea>
	</td>
	</tr>
	
	</table>

	</div><!--tab-global-->
	<div id="tab-nrdp">

	<div class="sectionTitle">NRDP Settings</div>

	<table class="manageOptionsTable">
	
	<tr>
	<td valign='top' nowrap>
	<label>Enable NRDP Output:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" name="enable_nrdp_output" <?php echo is_checked($enable_nrdp_output,1);?> /><br class="nobr" />
	Determines whether or not outbound data transfers are sent via NRDP.<br><br>
	</td>
	<tr>
	
	<tr>
	<td valign='top'>
	<label>Target Hosts:</label><br class="nobr" />
	</td>
	<td>
	<table class="standardtable">
	<thead>
	<tr><th>IP Address</th><th>Method</th><th>Authentication Token</th></tr>
	</thead>
	<tbody>
	<?php
	for($x=0;$x<3;$x++){
        if(!array_key_exists($x,$nrdp_target_hosts)){
            $nrdp_target_hosts[$x]["address"]="";
            $nrdp_target_hosts[$x]["method"]="https";
            $nrdp_target_hosts[$x]["token"]="";
			}
        if(!array_key_exists("method",$nrdp_target_hosts[$x])){
			$nrdp_target_hosts[$x]["method"]="http";
			}
	?>
	<tr>
	<td>
	<input type="text" size="20" name="nrdp_target_hosts[<?php echo $x;?>][address]" value="<?php echo encode_form_val($nrdp_target_hosts[$x]["address"]);?>" class="textfield" />
	</td>
	<td>
	<select name="nrdp_target_hosts[<?php echo $x;?>][method]">
	<option value="http" <?php echo is_selected("http",$nrdp_target_hosts[$x]["method"]);?>>HTTP</option>
	<option value="https" <?php echo is_selected("https",$nrdp_target_hosts[$x]["method"]);?>>HTTPS</option>
	</select>
	</td>
	<td>
	<input type="text" size="20" name="nrdp_target_hosts[<?php echo $x;?>][token]" value="<?php echo encode_form_val($nrdp_target_hosts[$x]["token"]);?>" class="textfield" />
	</td>
	</tr>
	<?php
		}
	?>
	</tbody>
	</table>
	The IP address(es) of the host(s) that NRDP data should be sent to.  You must supply an authentication token for each target.<br><br>
	<b>Important:</b> Each target host must have NRDP installed and be configured with the corresponding token you specified above. Additionally, this Nagios XI server must be able to contact each remote host on port 80 TCP (HTTP) or 443 TCP (HTTPS) in order to access the NRDP API. You may have to open firewall ports to allow access.<br><br>  
	</td>
	<tr>
	
	
	</table>
	
	</div><!--tab-nrdp-->
	<div id="tab-nsca">

	<div class="sectionTitle">NSCA Settings</div>

	<table class="manageOptionsTable">
	
	<tr>
	<td valign='top' nowrap>
	<label>Enable NSCA Output:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" name="enable_nsca_output" <?php echo is_checked($enable_nsca_output,1);?> /><br class="nobr" />
	Determines whether or not outbound data transfers are sent via NSCA.<br><br>
	</td>
	<tr>
	

	<tr>
	<td valign='top'>
	<label>Target Hosts:</label><br class="nobr" />
	</td>
	<td>
	<table class="standardtable">
	<thead>
	<tr><th>IP Address</th><th>Encryption Method</th><th>Password</th></tr>
	</thead>
	<tbody>
	<?php
	for($x=0;$x<3;$x++){
    if (!array_key_exists($x,$nsca_target_hosts)) {
            $nsca_target_hosts[$x]["address"]="";
            $nsca_target_hosts[$x]["encryption"]="";
            $nsca_target_hosts[$x]["password"]="";
        }
	?>
	<tr>
	<td>
	<input type="text" size="20" name="nsca_target_hosts[<?php echo $x;?>][address]" value="<?php echo encode_form_val($nsca_target_hosts[$x]["address"]);?>" class="textfield" />
	</td>
	<td>
	<select name="nsca_target_hosts[<?php echo $x;?>][encryption]" class="dropdown">
<?php
	$methods=array();
	$methods[0]="None (Not secure)";
	$methods[1]="XOR (Not secure)";
	$methods[2]="DES";
	$methods[3]="3DES";
	$methods[4]="CAST-128";
	$methods[5]="CAST-256";
	$methods[6]="xTEA";
	$methods[7]="3WAY";
	$methods[8]="BLOWFISH";
	$methods[9]="TWOFISH";
	$methods[10]="LOKI97";
	$methods[11]="RC2";
	$methods[12]="ARCFOUR";

	foreach($methods as $id => $title){
?>
	<option value="<?php echo $id;?>" <?php echo is_selected($nsca_target_hosts[$x]["encryption"],$id);?>><?php echo $title."</option>\n";?>
<?php
		}
?>
	</select>
	</td>
	<td>
	<input type="password" size="20" name="nsca_target_hosts[<?php echo $x;?>][password]" value="<?php echo encode_form_val($nsca_target_hosts[$x]["password"]);?>" class="textfield" />
	</td>
	</tr>
	<?php
		}
	?>
	</tbody>
	</table>

	The IP address(es) of the host(s) that NSCA data should be sent to. <br><br>
	<b>Important:</b> Each target host must be running NSCA and be configured with the same password and encryption method you specified above.  Additionally, this Nagios XI server must be able to contact each remote host on port 5667 TCP in order to access NSCA. You may have to open firewall ports to allow access.<br><br>
	</td>
	<tr>
	
	
	</table>
	
	</div><!--tab-nsca-->
	
	</div><!--tabs-->
	

	<div id="formButtons">
	<input type="submit" class="submitbutton" name="updateButton" value="<?php echo $lstr['UpdateSettingsButton'];?>" id="updateButton">
	<input type="submit" class="submitbutton" name="cancelButton" value="<?php echo $lstr['CancelButton'];?>" id="cancelButton">
	</div>
	

	<!--</fieldset>-->
	</form>
	
	


<?php

	do_page_end(true);
	exit();
	}


function do_update_options(){
	global $request;
	global $lstr;
	
	// user pressed the cancel button
	if(isset($request["cancelButton"]))
		header("Location: datatransfer.php");
		
	// log it
	send_to_audit_log("User updated outbound check transfer settings",AUDITLOGTYPE_CHANGE);

	// check session
	check_nagios_session_protector();
	
	$errmsg=array();
	$errors=0;

	// get values
	$enable_outbound_data_transfer=checkbox_binary(grab_request_var("enable_outbound_data_transfer"));
	$enable_nsca_output=checkbox_binary(grab_request_var("enable_nsca_output"));
	$nsca_target_hosts=grab_request_var("nsca_target_hosts");
	$outbound_data_host_name_filters=grab_request_var("outbound_data_host_name_filters");
	$outbound_data_filter_mode=grab_request_var("outbound_data_filter_mode");
	$enable_nrdp_output=checkbox_binary(grab_request_var("enable_nrdp_output"));
	$nrdp_target_hosts=grab_request_var("nrdp_target_hosts");
	


	// make sure we have requirements
	if(in_demo_mode()==true)
		$errmsg[$errors++]=$lstr['DemoModeChangeError'];
	$targets=0;
	foreach($nsca_target_hosts as $id => $tharr){
		$ip=grab_array_var($tharr,"address");
		$en=grab_array_var($tharr,"encryption");
		$pa=grab_array_var($tharr,"password");
		if(trim($ip)==""){
			unset($nsca_target_hosts[$id]);
			continue;
			}
		if($pa=="")
			$errmsg[$errors++]="Missing password for NSCA target host '".$ip."'";
		if($en=="")
			$errmsg[$errors++]="Missing encryption method for NSCA target host '".$ip."'";
		
		$targets++;
		}
	if($enable_nsca_output==1 && $targets==0)
		$errmsg[$errors++]="You must specify at least one target NSCA host.";
	$targets=0;
	foreach($nrdp_target_hosts as $id => $tharr){
		$ip=grab_array_var($tharr,"address");
		$method=grab_array_var($tharr,"method");		
		$to=grab_array_var($tharr,"token");
		if(trim($ip)==""){
			unset($nrdp_target_hosts[$id]);
			continue;
			}
		if($to=="")
			$errmsg[$errors++]="Missing token for NRDP target host '".$ip."'";
		
		$targets++;
		}
	if($enable_nrdp_output==1 && $targets==0)
		$errmsg[$errors++]="You must specify at least one target NRDP host.";
		
	// handle errors
	if($errors>0)
		show_options(true,$errmsg);
		
	// update options
	set_option("enable_outbound_data_transfer",$enable_outbound_data_transfer);
	set_option("enable_nsca_output",$enable_nsca_output);
	set_option("nsca_target_hosts",serialize($nsca_target_hosts));
	set_option("outbound_data_host_name_filters",$outbound_data_host_name_filters);
	set_option("outbound_data_filter_mode",$outbound_data_filter_mode);
	set_option("enable_nrdp_output",$enable_nrdp_output);
	set_option("nrdp_target_hosts",serialize($nrdp_target_hosts));
	
	// save NSCA options to the file(s)
	//$contents=file_get_contents("/usr/local/nagios/etc/send_nsca.cfg");
	//$contents2=preg_replace('/password=.*/','password='.$send_nsca_password,$contents);
	//$contents3=preg_replace('/encryption_method=.*/','encryption_method='.$send_nsca_encryption_method,$contents2);
	//file_put_contents("/usr/local/nagios/etc/send_nsca.cfg",$contents3);
	foreach($nsca_target_hosts as $id => $tharr){

		$address=grab_array_var($tharr,"address");
		$encryption=grab_array_var($tharr,"encryption");
		$password=grab_array_var($tharr,"password");

		if($address=="")
			continue;
			
		$fname="send_nsca-".$address.".cfg";
		
		$contents="# CONFIGURED BY NAGIOS XI\npassword=".$password."\nencryption_method=".$encryption."\n";
		
		file_put_contents("/usr/local/nagios/etc/".$fname,$contents);
		};
	
	
	// success!
	show_options(false,"Settings Updated");
	}
		
		

function draw_menu(){
	//$m=get_admin_menu_items();
	//draw_menu_items($m);
	}
	
	

?>
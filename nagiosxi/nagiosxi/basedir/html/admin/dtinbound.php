<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: dtinbound.php 1208 2012-06-09 18:00:37Z egalstad $

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
	
	//set_option("have_configured_nsca",0);
	
	// no nrdp token yet, so generate on
	if(get_option('inbound_nrdp_tokens')==""){
		set_option('inbound_nrdp_tokens',random_string(12));
		}

	// get options
	$nsca_password=grab_request_var("nsca_password",get_option('nsca_password'));
	$nsca_encryption_method=grab_request_var("nsca_encryption_method",get_option('nsca_encryption_method'));
	$have_configured_nsca=checkbox_binary(grab_request_var("have_configured_nsca",get_option('have_configured_nsca')));
	$inbound_nrdp_tokens=grab_request_var("inbound_nrdp_tokens",get_option('inbound_nrdp_tokens'));
	
	if(in_demo_mode()==true){
		$nsca_password="******";
		$inbound_nrdp_tokens="******";
		}
	
	do_page_start(array("page_title"=>$lstr['InboundDataTransferPageTitle']),true);

?>

	
	<h1><?php echo $lstr['InboundDataTransferPageTitle'];?></h1>

	<img src="<?php echo theme_image("dtinbound.png");?>">
	<p>
	These settings affect Nagios XI's ability to accept and process passive host and service check results from external applications, services, and remote Nagios servers.  Enabling inbound checks is important in distributed monitoring environments, and in environments where external applications and services send data to Nagios.
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
		<li><a href="#tab-nrdp">NRDP</a></li>
		<li><a href="#tab-nsca">NSCA</a></li>
	</ul>
	
	
	<div id="tab-nrdp">
	
	<div class="sectionTitle">NRDP Settings</div>

	<table class="manageOptionsTable">
	
	<tr>
	<td valign='top'>
	<label>Access Info:</label><br class="nobr" />
	</td>
	<td>
<?php
	$url="http://".$_SERVER['SERVER_ADDR']."/nrdp/";
?>
	The NRDP API can be accessed at <a href="<?php echo $url;?>"><b><?php echo $url;?></b></a>.<br><br>
	<b>Note:</b> Remote clients must be able to contact this server on port 80 TCP (HTTP) in order to access the NRDP API and submit check results.  You may have to open firewall ports to allow access.<br><br>
	</td>
	</tr>
		
	<tr>
	<td valign='top'>
	<label>Authentication Tokens:</label><br class="nobr" />
	</td>
	<td>
	One or more (alphanumeric) tokens that remote hosts and applications must use when contacting the NRDP API on this server.  Specify one token per line.<br>
<textarea name="inbound_nrdp_tokens" row="4" cols="40"><?php echo encode_form_val($inbound_nrdp_tokens);?>
</textarea>
	</td>
	</tr>

	</table>
	
	</div><!--tab-nrdp-->
	<div id="tab-nsca">

	<div class="sectionTitle">NSCA Settings</div>

<?php
	if($have_configured_nsca!=1){
?>
	<div class="message" style="width: 600px;">
	<ul class="errorMessage">
	<li><img src="<?php echo theme_image("alert_bubble.png");?>"> <b>Configuration Required</b></li>
	<li><br>Before you can enable inbound data transfer via NSCA, you must configure settings to allow external hosts/devices to communicate with NSCA.<br><br>To do this, follow these steps:<br><br>
		<ol>
		<li>Login to the Nagios XI server as the <i>root</i> user</li>
		<li>Open the <i>/etc/xinetd.d/nsca</i> file for editing</li>
		<li>Modify the <i>only_from</i> statement to include the IP addresses of hosts that are allowed to send data (or comment it out to allow all hosts to send data)</li>
		<li>Save the file</li>
		</ol>
	</li><br><input type="checkbox" name="have_configured_nsca"> I have completed these steps.
	</ul>
	</div>
	
	<br clear="all">

<?php
	}
?>

	
	<table class="manageOptionsTable">
	
	<tr>
	<td valign='top'>
	<label>Access Info:</label><br class="nobr" />
	</td>
	<td>
	NSCA is configured to run on this machine on port <b>5667 TCP</b>.<br><br>
	<b>Note:</b> Remote clients must be able to contact this server on port 5667 TCP in order to access NSCA and submit check results.  You may have to open firewall ports to allow access.<br><br>
	</td>
	</tr>
	
	<tr>
	<td valign='top'>
	<label>Decryption Method:</label><br class="nobr" />
	</td>
	<td>
	<select name="nsca_encryption_method" class="dropdown">
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
	<option value="<?php echo $id;?>" <?php echo is_selected($nsca_encryption_method,$id);?>><?php echo $title."</option>\n";?>
<?php
		}
?>
	</select><br class="nobr" />
	The decryption method used on check data that is received via NSCA.<br><b>Important:</b> Each sender must be using the same encryption method as you specify for the decryption method here.<br><br>
	</td>
	</tr>

	<tr>
	<td valign='top'>
	<label>Password:</label><br class="nobr" />
	</td>
	<td>
	<input type="password" size="20" name="nsca_password" value="<?php echo encode_form_val($nsca_password);?>" class="textfield" /><br class="nobr" />
	The password used to decrypt check data that is received by NSCA.<br><b>Important:</b> Each sender must be using this same password.<br><br>
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
	send_to_audit_log("User updated inbound check transfer settings",AUDITLOGTYPE_CHANGE);

	// check session
	check_nagios_session_protector();
	
	$errmsg=array();
	$errors=0;

	// get values
	$nsca_password=grab_request_var("nsca_password");
	$nsca_encryption_method=grab_request_var("nsca_encryption_method");
	$have_configured_nsca=checkbox_binary(grab_request_var("have_configured_nsca"));
	$inbound_nrdp_tokens=grab_request_var("inbound_nrdp_tokens");
	


	// make sure we have requirements
	if(in_demo_mode()==true)
		$errmsg[$errors++]=$lstr['DemoModeChangeError'];
	if(have_value($nsca_password)==false)
		$errmsg[$errors++]="NSCA password cannot be empty.";
	$total_tokens=0;
	//print_r($inbound_nrdp_tokens);
	$tokens=explode("\n",$inbound_nrdp_tokens);
	foreach($tokens as $t){
		//echo "TOKEN: $t<BR>";
		$token=trim($t);
		if($token=="")
			continue;
		$total_tokens++;
		}
	if($total_tokens==0)
		$errmsg[$errors++]="No NRDP tokens specified.";
		
	// handle errors
	if($errors>0)
		show_options(true,$errmsg);
		
	// update options
	set_option("nsca_password",$nsca_password);
	set_option("nsca_encryption_method",$nsca_encryption_method);
	set_option("have_configured_nsca",$have_configured_nsca);
	set_option("inbound_nrdp_tokens",$inbound_nrdp_tokens);
	
	// save NSCA options to file
	$contents=file_get_contents("/usr/local/nagios/etc/nsca.cfg");
	$contents2=preg_replace('/password=.*/','password='.$nsca_password,$contents);
	$contents3=preg_replace('/decryption_method=.*/','decryption_method='.$nsca_encryption_method,$contents2);
	file_put_contents("/usr/local/nagios/etc/nsca.cfg",$contents3);
	
	// save NRDP options to file
	$tokens=explode("\n",$inbound_nrdp_tokens);
	$token_str="array(";
	foreach($tokens as $t){
		$token=trim($t);
		if($token=="")
			continue;
		$token_str.="\"".$token."\",";
		}
	$token_str.=");";
	
	$contents=file_get_contents("/usr/local/nrdp/server/config.inc.php");
	$match="/\\\$cfg\\['authorized_tokens'\\]=.*/";
	$replace="\\\$cfg['authorized_tokens']=$token_str";
	//$replace="REPLACED";
	$contents2=preg_replace($match,$replace,$contents);
	/*
	echo "TOKENSTRING: $token_str<BR>";
	echo "MATCH: $match<BR>";
	echo "REPLACE: $replace<BR>";
	echo "NEWCONTENTS:<BR>";
	echo $contents2;
	exit;
	*/
	file_put_contents("/usr/local/nrdp/server/config.inc.php",$contents2);
	
	
	// success!
	show_options(false,"Settings Updated");
	}
		
		

function draw_menu(){
	//$m=get_admin_menu_items();
	//draw_menu_items($m);
	}
	
	

?>
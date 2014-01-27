<?php
// XI Status Functions
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: status-object-detail.inc.php 1303 2012-07-19 15:50:34Z mguthrie $

include_once(dirname(__FILE__).'/../componenthelper.inc.php');

	
////////////////////////////////////////////////////////////////////////
// SERVICE DETAIL
////////////////////////////////////////////////////////////////////////
	
function show_service_detail(){
	global $lstr;
	
	$host=grab_request_var("host","");
	$service=grab_request_var("service","");
	
	$service_id=get_service_id($host,$service);
	
	if(is_authorized_for_service(0,$host,$service)==false){
		/*
		echo "HOST: $host<BR>";
		echo "SERVICE: $service<BR>";
		echo "SID: $service_id<BR>";
		print_r($request);
		exit();
		*/
		show_not_authorized_for_object_page();
		}
		
	// get additional tabs
	$cbdata=array(
		"host" => $host,
		"service" => $service,
		"tabs" => array(),
		);
	do_callbacks(CALLBACK_SERVICE_TABS_INIT,$cbdata);
	$customtabs=grab_array_var($cbdata,"tabs",array());
	//echo "CUSTOMTABS:<BR>";
	//print_r($customtabs);
		
	// save this for later
	$auth_command=is_authorized_for_service_command(0,$host,$service);

	// should configure tab be shown?
	//if(is_authorized_to_configure_service(0,$host,$service)==true && is_service_configurable($host,$service)==true)
	if(is_authorized_to_configure_service(0,$host,$service)==true)
		$show_configure=true;
	else
		$show_configure=false;

		
	// get service status
	$args=array(
		"cmd" => "getservicestatus",
		"service_id" => $service_id,
		);
	$xml=get_backend_xml_data($args);		

	do_page_start(array("page_title"=>$lstr['ServiceStatusDetailPageTitle']),true);

?>
	<h1><?php echo $lstr['ServiceStatusDetailPageHeader'];?></h1>
	
	<div class="servicestatusdetailheader">
	<div class="serviceimage">
	<!--image-->
	<?php show_object_icon($host,$service,true);?>
	</div>
	<div class="servicetitle">
	<div class="servicename"><?php echo htmlentities($service);?></div>
	<div class="hostname"><a href="<?php echo get_host_status_detail_link($host);?>"><?php echo htmlentities($host);?></a></div>
	</div>
	</div>
	
	<?php draw_service_detail_links($host,$service);?>
	<br clear="all">
	
<script type="text/javascript">
	$(document).ready(function() {
		$("#tabs").tabs();
	});
	</script>
	
	<div id="tabs">
	<ul class="tabnavigation">
		<li><a href="#tab-overview"><?php echo $lstr['ServiceDetailsOverviewTab'];?></a></li>
		<li><a href="#tab-perfgraphs"><?php echo $lstr['ServiceDetailsPerformanceGraphsTab'];?></a></li>
<?php
	if(is_advanced_user()){
?>
		<li><a href="#tab-advanced"><?php echo $lstr['ServiceDetailsAdvancedTab'];?></a></li>
<?php
		}
	if($show_configure==true){
?>
		<li><a href="#tab-configure"><?php echo $lstr['ServiceDetailsConfigureTab'];?></a></li>
<?php
		}
?>
<?php
	// custom tabs
	foreach($customtabs as $ct){
		$id=grab_array_var($ct,"id");
		$title=grab_array_var($ct,"title");
		echo "<li><a href='#tab-custom-".$id."'>".htmlentities($title)."</a></li>";
		}
?>
	</ul>
	
	<!-- overview tab -->
	<div id="tab-overview" class="ui-tabs-hide">
	
	<div class="statusdetail_panelspacer"></div>

	<div style="float: left; margin-bottom: 25px;"><!--state summary-->
<?php

	$args=array(
		"hostname" => $host,
		"servicename" => urlencode($service),
		"service_id" => $service_id,
		"display" => "simple",
		);

	// build args for javascript
	$n=0;
	$jargs="{";
	foreach($args as $var => $val){
		if($n>0)
			$jargs.=", ";
		$jargs.="\"$var\" : \"$val\"";
		$n++;
		}
	$jargs.="}";

	$id="service_state_summary_".random_string(6);
				$output='
	<div class="service_state_summary" id="'.$id.'">
	'.xicore_ajax_get_service_status_state_summary_html($args).'
	</div><!--service_state_summary-->
	<script type="text/javascript">
	$(document).ready(function(){
			
		$("#'.$id.'").everyTime(7*1000, "timer-'.$id.'", function(i) {
		var optsarr = {
			"func": "get_service_status_state_summary_html",
			"args": '.$jargs.'
			}
		var opts=array2json(optsarr);
		get_ajax_data_innerHTML("getxicoreajax",opts,true,this);
		});
		
	});
	</script>
			';
?>
	<?php echo $output;?>
	</div><!--state summary-->
	
	
	<br clear="all">

	<div style="float: left;"><!--state info-->
<?php

	$args=array(
		"hostname" => $host,
		"servicename" => urlencode($service),
		"service_id" => $service_id,
		"display" => "simple",
		);

	// build args for javascript
	$n=0;
	$jargs="{";
	foreach($args as $var => $val){
		if($n>0)
			$jargs.=", ";
		$jargs.="\"$var\" : \"$val\"";
		$n++;
		}
	$jargs.="}";

	$id="service_state_info_".random_string(6);
				$output='
	<div class="service_state_info" id="'.$id.'">
	'.xicore_ajax_get_service_status_detailed_info_html($args).'
	</div><!--service_state_info-->
	<script type="text/javascript">
	$(document).ready(function(){
			
		$("#'.$id.'").everyTime(7*1000, "timer-'.$id.'", function(i) {
		var optsarr = {
			"func": "get_service_status_detailed_info_html",
			"args": '.$jargs.'
			}
		var opts=array2json(optsarr);
		get_ajax_data_innerHTML("getxicoreajax",opts,true,this);
		});
		
		function fill_'.$id.'(data){
			$("#'.$id.'").innerHTML=data;
			}
		
	});
	</script>
			';
?>
	<?php echo $output;?>
	</div><!--state info-->

	<div style="float: left;"><!-- quick actions-->
	<div class="infotable_title">Quick Actions</div>
	<table class="infotable">
	<thead>
	</thead>
	<tbody>
	<tr><td>
	<!-- dynamic entries-->
	<ul class="quickactions dynamic">
<?php

	$args=array(
		"hostname" => $host,
		"servicename" => urlencode($service),
		"service_id" => $service_id,
		"display" => "simple",
		);

	// build args for javascript
	$n=0;
	$jargs="{";
	foreach($args as $var => $val){
		if($n>0)
			$jargs.=", ";
		$jargs.="\"$var\" : \"$val\"";
		$n++;
		}
	$jargs.="}";

	$id="service_state_quick_actions_".random_string(6);
				$output='
	<div class="service_state_quick_actions" id="'.$id.'">
	'.xicore_ajax_get_service_status_quick_actions_html($args).'
	</div><!--service_state_quick_actions-->
	<script type="text/javascript">
	$(document).ready(function(){
			
		$("#'.$id.'").everyTime(10*1000, "timer-'.$id.'", function(i) {
		var optsarr = {
			"func": "get_service_status_quick_actions_html",
			"args": '.$jargs.'
			}
		var opts=array2json(optsarr);
		get_ajax_data_innerHTML("getxicoreajax",opts,true,this);
		});
		
	});
	</script>
			';
?>
	<?php echo $output;?>	
	</ul>

	<!-- other entries-->
	</td></tr>
	</tbody>
	</table>
	</div><!-- quick actions-->

	<br clear="all">

	<script type="text/javascript">
	function show_ack(){
		$("#servicequickactionform").each(function(i) {
			$(this).css("visibility","visible");
			this.innerHTML="<br><div class='infotable_title'>Acknowledge Problem</div><form action='' method='get'><input type='hidden' name='show' value='servicedetail'><input type='hidden' name='host' value='<?php echo htmlentities($host);?>'><input type='hidden' name='service' value='<?php echo htmlentities($service);?>'><input type='hidden' name='submitcommand' value='1'><input type='hidden' name='cmd' value='ackservice'><label for='comment'><?php echo $lstr['AcknowledgementCommentBoxText'];?></label><br><input type='text' class='textfield' size='40' name='comment' id='comment'><input type='submit' name='btnSubmit' value='<?php echo $lstr['SubmitButton'];?>'></form>";
			});
		$("#servicequickactionformcontainer").each(function(i) {
			$(this).css("visibility","visible");
			});
		}
	</script>


	<div id="servicequickactionformcontainer"><!-- live action form -->
	<div id="servicequickactionform">
	<!--LIVE ACTION FORM-->
	</div>
	</div><!-- live action form -->

	<div style="float: left; margin-top: 25px;"><!--comments-->
<?php

	$args=array(
		"hostname" => $host,
		"servicename" => urlencode($service),
		"service_id" => $service_id,
		"display" => "simple",
		);

	// build args for javascript
	$n=0;
	$jargs="{";
	foreach($args as $var => $val){
		if($n>0)
			$jargs.=", ";
		$jargs.="\"$var\" : \"$val\"";
		$n++;
		}
	$jargs.="}";

	$id="service_comments_".random_string(6);
				$output='
	<div class="service_comments" id="'.$id.'">
	'.xicore_ajax_get_service_comments_html($args).'
	</div><!--service_comments-->
	<script type="text/javascript">
	$(document).ready(function(){
			
		$("#'.$id.'").everyTime(10*1000, "timer-'.$id.'", function(i) {
		var optsarr = {
			"func": "get_service_comments_html",
			"args": '.$jargs.'
			}
		var opts=array2json(optsarr);
		get_ajax_data_innerHTML("getxicoreajax",opts,true,this);
		});
		
	});
	</script>
			';
?>
	<?php echo $output;?>
	</div><!--comments-->

	


	</div>
	<!-- overview tab -->
	
	<!-- performance graphs tab -->
	<div id="tab-perfgraphs" class="ui-tabs-hide">
	
	<?php //draw_service_detail_links($host,$service);?>
	<div class="statusdetail_panelspacer"></div>

<?php
	$args=array(
		"hostname" => $host,
		"servicename" => urlencode($service),
		"service_id" => $service_id,
		);
		
	// build args for javascript
	$n=0;
	$jargs="{";
	foreach($args as $var => $val){
		if($n>0)
			$jargs.=", ";
		$jargs.="\"".htmlentities($var)."\" : \"".htmlentities($val)."\"";
		$n++;
		}
	$jargs.="}";
?>

			
	<script type="text/javascript">
	$(document).ready(function(){

	
		var service_perfgraphs_panel_displayed=false;

		var locationObj = window.location;
		if(locationObj.hash == "#tab-perfgraphs"){
			//alert('tab-perfgraphs');
			load_perfgraphs_panel();
			}

		var tabContainers = $('#tabs > div');
		$('#tabs ul.tabnavigation a').click(function () {
			//alert(this.hash + " selected");
			if(this.hash=="#tab-perfgraphs")
				load_perfgraphs_panel();
			return false;
			});

			
		function load_perfgraphs_panel(){
		
			if(service_perfgraphs_panel_displayed==true){
				//alert('already done');
				return;
				}
		
			service_perfgraphs_panel_displayed=true;
			
			var optsarr = {
				"func": "get_service_detail_perfgraphs_panel",
				"args": <?php echo $jargs;?>
				}
			var opts=array2json(optsarr);
			var panel=$('#servicedetails-perfgraphs-panel-content');
			var thepanel=panel[0];
			get_ajax_data_innerHTML("getxicoreajax",opts,true,thepanel);	


			}
						
	});
	</script>
	
	<div id="servicedetails-perfgraphs-panel-content">
	<img src="<?php echo theme_image("throbber.gif");?>"> Loading performance graphs...
	</div>
	

	
	</div>
	<!-- performance graphs tab -->
	
	<!-- advanced tab -->
<?php
	if(is_advanced_user()){
?>
	<div id="tab-advanced" class="ui-tabs-hide">
	
	<div class="statusdetail_panelspacer"></div>
	
	<div style="float: left; margin-bottom: 25px;"><!--state info-->
<?php

	$args=array(
		"hostname" => $host,
		"servicename" => urlencode($service),
		"service_id" => $service_id,
		"display" => "advanced",
		);

	// build args for javascript
	$n=0;
	$jargs="{";
	foreach($args as $var => $val){
		if($n>0)
			$jargs.=", ";
		$jargs.="\"$var\" : \"$val\"";
		$n++;
		}
	$jargs.="}";

	$id="service_state_info_".random_string(6);
				$output='
	<div class="service_state_info" id="'.$id.'">
	'.xicore_ajax_get_service_status_detailed_info_html($args).'
	</div><!--service_state_info-->
	<script type="text/javascript">
	$(document).ready(function(){
			
		$("#'.$id.'").everyTime(10*1000, "timer-'.$id.'", function(i) {
		var optsarr = {
			"func": "get_service_status_detailed_info_html",
			"args": '.$jargs.'
			}
		var opts=array2json(optsarr);
		get_ajax_data_innerHTML("getxicoreajax",opts,true,this);
		});
		
	});
	</script>
			';
?>
	<?php echo $output;?>
	</div><!--state info-->

	<div style="float: left;">
<?php

	$args=array(
		"hostname" => $host,
		"servicename" => urlencode($service),
		"service_id" => $service_id,
		"display" => "all",
		);

	// build args for javascript
	$n=0;
	$jargs="{";
	foreach($args as $var => $val){
		if($n>0)
			$jargs.=", ";
		$jargs.="\"$var\" : \"$val\"";
		$n++;
		}
	$jargs.="}";

	$id="advanced_servicestatus_attributes_".random_string(6);
				$output='
	<div class="advanced_servicestatus_attributes" id="'.$id.'">
	'.xicore_ajax_get_service_status_attributes_html($args).'
	</div><!--advanced_servicestatus_attributes-->
	<script type="text/javascript">
	$(document).ready(function(){
			
		$("#'.$id.'").everyTime(10*1000, "timer-'.$id.'", function(i) {
		var optsarr = {
			"func": "get_service_status_attributes_html",
			"args": '.$jargs.'
			}
		var opts=array2json(optsarr);
		get_ajax_data_innerHTML("getxicoreajax",opts,true,this);
		});
		
	});
	</script>
			';
?>
	<?php echo $output;?>
	</div>

	<br clear="all">

	<div style="float: left; margin-right: 50px;"><!--advanced commands-->
	
<?php
	if($auth_command){
?>
	<div class="infotable_title">Commands</div>
	
	<table class="infotable">
	<tbody>

<?php
	$urlbase=get_base_url()."includes/components/nagioscore/ui/cmd.php?cmd_typ=";
	$urlmod="&host=".urlencode($host)."&service=".urlencode($service);
?>	
	<?php
		if($xml && intval($xml->servicestatus->problem_acknowledged)==1){
	?>
	<tr><td nowrap><?php show_object_command_link($urlbase.NAGIOSCORE_CMD_REMOVE_SVC_ACKNOWLEDGEMENT.$urlmod,"noack.gif","Remove problem acknowledgement");?></td></tr>
	<?php
		}
	?>

	<tr><td nowrap><?php show_object_command_link($urlbase.NAGIOSCORE_CMD_SCHEDULE_SVC_DOWNTIME.$urlmod,"downtime.gif","Schedule downtime");?></td></tr>

	<tr><td nowrap><?php show_object_command_link($urlbase.NAGIOSCORE_CMD_PROCESS_SERVICE_CHECK_RESULT.$urlmod,"passiveonly.gif","Submit passive check result");?></td></tr>

	<tr><td nowrap><?php show_object_command_link($urlbase.NAGIOSCORE_CMD_SEND_CUSTOM_SVC_NOTIFICATION.$urlmod,"notify.gif","Send custom notification");?></td></tr>

	<tr><td nowrap><?php show_object_command_link($urlbase.NAGIOSCORE_CMD_DELAY_SVC_NOTIFICATION.$urlmod,"delay.gif","Delay next notification");?></td></tr>

	
	</tbody>
	</table>
<?php
		}
?>
	</div><!--advanced commands-->
	
	<div style="float: left;">
	<div class="infotable_title">More Options</div>
	<ul>
	<li><a href="<?php echo get_service_status_detail_link($host,$service,"core");?>">See this service in Nagios Core</a></li>
	</ul>
	</div>
	
	
		
	</div>
	<!-- advanced tab -->
<?php
		}
?>
	
	<!-- configure tab -->
<?php
	if($show_configure==true){
?>
	<div id="tab-configure" class="ui-tabs-hide">
<?php

	echo "<p>";
	echo "<img src='".theme_image("editsettings.png")."' style='float: left; margin-right: 10px;'>";
	
	$url=get_base_url()."config/configobject.php?host=".urlencode($host)."&service=".urlencode($service)."&return=servicedetail";
	echo "<a href='".$url."'>Re-configure this service</a>";

	/*
	if(is_service_configurable($host,$service)==true){
		$url=get_base_url()."config/modifyobject.php?host=".$host."&service=".$service."&return=servicedetail";
		echo "<a href='".$url."'>Modify the settings for this service</a>";
		}
	else{
		//echo"This services makes use of an advanced configuration.  ";
		if(is_advanced_user()==true){
			$url=get_base_url()."config/nagioscorecfg/";
			echo "<a href='".$url."' target='_top'>Enter the advanced configuration manager</a> to modify the settings for this service.";
			}
		else
			echo "Contact your Nagios administrator to modify the settings for this service.";
		}
	*/
		
	echo "<br>";
	echo "<p>";
	echo "<img src='".theme_image("delete.png")."' style='float: left; margin-right: 10px;'>";

	/*
	if(can_service_be_deleted($host,$service)==true){
		$url=get_base_url()."config/deleteobject.php?host=".$host."&service=".$service."&return=servicedetail";
		echo "<a href='".$url."'>Delete this service</a>";
		}
	else{
		if(is_advanced_user()==true){
			$url=get_base_url()."config/nagioscorecfg/";
			echo "<a href='".$url."' target='_top'>Enter the advanced configuration manager</a> to delete this service.";
			}
		else
			echo "Contact your Nagios administrator to delete this service.";
		}
	*/
	$url=get_base_url()."config/deleteobject.php?host=".urlencode($host)."&service=".urlencode($service)."&return=servicedetail";
	echo "<a href='".$url."'>Delete this service</a>";
		
?>
		
	</div>
<?php
		}
?>
	<!-- configure tab -->
	
<?php
	// custom tabs
	foreach($customtabs as $ct){
		$id=grab_array_var($ct,"id");
		$title=grab_array_var($ct,"title");
		$content=grab_array_var($ct,"content");
		echo "<div id='tab-custom-".$id."'>".$content."</div>";
		}
?>
	
</div>



<?php
	do_page_end(true);
	}
	
	
////////////////////////////////////////////////////////////////////////
// HOST DETAIL
////////////////////////////////////////////////////////////////////////
	
function show_host_detail(){
	global $lstr;

	global $lstr;
	
	$host=grab_request_var("host","");
	
	$host_id=get_host_id($host);
	
	if(is_authorized_for_host(0,$host)==false){
		/*
		echo "HOST: $host<BR>";
		print_r($request);
		exit();
		*/
		show_not_authorized_for_object_page();
		}
		
	// save this for later
	$auth_command=is_authorized_for_host_command(0,$host);
		
	// should configure tab be shown?
	//if(is_authorized_to_configure_host(0,$host)==true && is_host_configurable($host)==true)
	if(is_authorized_to_configure_host(0,$host)==true)
		$show_configure=true;
	else
		$show_configure=false;

	// get additional tabs
	$cbdata=array(
		"host" => $host,
		"service" => "",
		"tabs" => array(),
		);
	do_callbacks(CALLBACK_HOST_TABS_INIT,$cbdata);
	$customtabs=grab_array_var($cbdata,"tabs",array());
	//print_r($customtabs);

	// get host status
	$args=array(
		"cmd" => "gethoststatus",
		"host_id" => $host_id,
		);
	$xml=get_backend_xml_data($args);		
	
	$hostalias = $xml->hoststatus->alias;	//added host alias per feature request -MG 
	
	do_page_start(array("page_title"=>$lstr['HostStatusDetailPageTitle']),true);
	
?>
	<h1><?php echo $lstr['HostStatusDetailPageHeader'];?></h1>
	
	<div class="hoststatusdetailheader">
	<div class="hostimage">
	<!--image-->
	<?php show_object_icon($host,"",true);?>
	</div>
	<div class="hosttitle">
	<div class="hostname"><?php echo htmlentities($host);?></div>
	<div class="hostalias">Alias: <?php echo $hostalias; ?></div>
	</div>
	</div>
	
	<?php draw_host_detail_links($host);?>
	<br clear="all">
	
<script type="text/javascript">
	$(function() {
		$("#tabs").tabs();
	});
	</script>
	
	<div id="tabs">
	<ul class="tabnavigation">
		<li><a href="#tab-overview"><?php echo $lstr['HostDetailsOverviewTab'];?></a></li>
		<li><a href="#tab-perfgraphs"><?php echo $lstr['HostDetailsPerformanceGraphsTab'];?></a></li>
<?php
	if(is_advanced_user()){
?>
		<li><a href="#tab-advanced"><?php echo $lstr['HostDetailsAdvancedTab'];?></a></li>
<?php
		}
	if($show_configure==true){
?>
		<li><a href="#tab-configure"><?php echo $lstr['HostDetailsConfigureTab'];?></a></li>
<?php
		}
?>
<?php
	// custom tabs
	foreach($customtabs as $ct){
		$id=grab_array_var($ct,"id");
		$title=grab_array_var($ct,"title");
		echo "<li><a href='#tab-custom-".$id."'>".$title."</a></li>";
		}
?>
	</ul>
	
	<!-- overview tab -->
	<div id="tab-overview" class="ui-tabs-hide">
	
	<div class="statusdetail_panelspacer"></div>
	
	<div style="float: left; margin-bottom: 15px;"><!--state summary-->
<?php

	$args=array(
		"hostname" => $host,
		"host_id" => $host_id,
		"display" => "simple",
		);

	// build args for javascript
	$n=0;
	$jargs="{";
	foreach($args as $var => $val){
		if($n>0)
			$jargs.=", ";
		$jargs.="\"$var\" : \"$val\"";
		$n++;
		}
	$jargs.="}";

	$id="host_state_summary_".random_string(6);
				$output='
	<div class="host_state_summary" id="'.$id.'">
	'.xicore_ajax_get_host_status_state_summary_html($args).'
	</div><!--host_state_summary-->
	<script type="text/javascript">
	$(document).ready(function(){
			
		$("#'.$id.'").everyTime(7*1000, "timer-'.$id.'", function(i) {
		var optsarr = {
			"func": "get_host_status_state_summary_html",
			"args": '.$jargs.'
			}
		var opts=array2json(optsarr);
		get_ajax_data_innerHTML("getxicoreajax",opts,true,this);
		});
		
	});
	</script>
			';
?>
	<?php echo $output;?>
	</div><!--state summary-->
	
	
	<br clear="all">

	<div style="float: left; margin-bottom: 25px; clear: left;">
<?php
	// get host info
	$args=array(
		"host_id" => $host_id,
		);
	$configxml=get_xml_host_objects($args);

	//echo "<PRE>\n";
	//print_r($xml);
	//echo "</PRE>\n";
	
	// host address
	$address="";
	if($configxml && intval($configxml->recordcount)>0){
		foreach($configxml->host as $h){
			$address=strval($h->address);
			}
		}
?>
	<b>Address:</b> <?php echo $address;?>
	</div>
	
	<br clear="all">


	<div style="float: left;"><!--state info-->
<?php

	$args=array(
		"hostname" => $host,
		"host_id" => $host_id,
		"display" => "simple",
		);

	// build args for javascript
	$n=0;
	$jargs="{";
	foreach($args as $var => $val){
		if($n>0)
			$jargs.=", ";
		$jargs.="\"$var\" : \"$val\"";
		$n++;
		}
	$jargs.="}";

	$id="host_state_info_".random_string(6);
				$output='
	<div class="host_state_info" id="'.$id.'">
	'.xicore_ajax_get_host_status_detailed_info_html($args).'
	</div><!--host_state_info-->
	<script type="text/javascript">
	$(document).ready(function(){
			
		$("#'.$id.'").everyTime(7*1000, "timer-'.$id.'", function(i) {
		var optsarr = {
			"func": "get_host_status_detailed_info_html",
			"args": '.$jargs.'
			}
		var opts=array2json(optsarr);
		get_ajax_data_innerHTML("getxicoreajax",opts,true,this);
		});
		
		function fill_'.$id.'(data){
			$("#'.$id.'").innerHTML=data;
			}
		
	});
	</script>
			';
?>
	<?php echo $output;?>
	</div><!--state info-->

	<div style="float: left;"><!-- quick actions-->
	<div class="infotable_title">Quick Actions</div>
	<table class="infotable">
	<thead>
	</thead>
	<tbody>
	<tr><td>
	<!-- dynamic entries-->
	<ul class="quickactions dynamic">
<?php

	$args=array(
		"hostname" => $host,
		"host_id" => $host_id,
		"display" => "simple",
		);

	// build args for javascript
	$n=0;
	$jargs="{";
	foreach($args as $var => $val){
		if($n>0)
			$jargs.=", ";
		$jargs.="\"$var\" : \"$val\"";
		$n++;
		}
	$jargs.="}";

	$id="host_state_quick_actions_".random_string(6);
				$output='
	<div class="host_state_quick_actions" id="'.$id.'">
	'.xicore_ajax_get_host_status_quick_actions_html($args).'
	</div><!--host_state_quick_actions-->
	<script type="text/javascript">
	$(document).ready(function(){
			
		$("#'.$id.'").everyTime(10*1000, "timer-'.$id.'", function(i) {
		var optsarr = {
			"func": "get_host_status_quick_actions_html",
			"args": '.$jargs.'
			}
		var opts=array2json(optsarr);
		get_ajax_data_innerHTML("getxicoreajax",opts,true,this);
		});
		
	});
	</script>
			';
?>
	<?php echo $output;?>	
	</ul>

	<!-- other entries-->
	</td></tr>
	</tbody>
	</table>
	</div><!-- quick actions-->

	<br clear="all">


	<div style="float: left; margin-top: 25px;"><!--comments-->
<?php

	$args=array(
		"hostname" => $host,
		"host_id" => $host_id,
		"display" => "simple",
		);

	// build args for javascript
	$n=0;
	$jargs="{";
	foreach($args as $var => $val){
		if($n>0)
			$jargs.=", ";
		$jargs.="\"$var\" : \"$val\"";
		$n++;
		}
	$jargs.="}";

	$id="host_comments_".random_string(6);
				$output='
	<div class="host_comments" id="'.$id.'">
	'.xicore_ajax_get_host_comments_html($args).'
	</div><!--service_host-->
	<script type="text/javascript">
	$(document).ready(function(){
			
		$("#'.$id.'").everyTime(10*1000, "timer-'.$id.'", function(i) {
		var optsarr = {
			"func": "get_host_comments_html",
			"args": '.$jargs.'
			}
		var opts=array2json(optsarr);
		get_ajax_data_innerHTML("getxicoreajax",opts,true,this);
		});
		
	});
	</script>
			';
?>
	<?php echo $output;?>
	</div><!--comments-->

	


	</div>
	<!-- overview tab -->
	
	<!-- performance graphs tab -->
	<div id="tab-perfgraphs" class="ui-tabs-hide">
	
	<?php //draw_service_detail_links($host,$service);?>
	<div class="statusdetail_panelspacer"></div>

<?php
	$args=array(
		"hostname" => $host,
		"host_id" => $host_id,
		);
		
	// build args for javascript
	$n=0;
	$jargs="{";
	foreach($args as $var => $val){
		if($n>0)
			$jargs.=", ";
		$jargs.="\"".htmlentities($var)."\" : \"".htmlentities($val)."\"";
		$n++;
		}
	$jargs.="}";
?>

			
	<script type="text/javascript">
	$(document).ready(function(){

	
		var host_perfgraphs_panel_displayed=false;

		var locationObj = window.location;
		if(locationObj.hash == "#tab-perfgraphs"){
			//alert('tab-perfgraphs');
			load_perfgraphs_panel();
			}

		var tabContainers = $('#tabs > div');
		$('#tabs ul.tabnavigation a').click(function () {
			//alert(this.hash + " selected");
			if(this.hash=="#tab-perfgraphs")
				load_perfgraphs_panel();
			return false;
			});

			
		function load_perfgraphs_panel(){
		
			if(host_perfgraphs_panel_displayed==true){
				//alert('already done');
				return;
				}
		
			host_perfgraphs_panel_displayed=true;
			
			var optsarr = {
				"func": "get_host_detail_perfgraphs_panel",
				"args": <?php echo $jargs;?>
				}
			var opts=array2json(optsarr);
			var panel=$('#hostdetails-perfgraphs-panel-content');
			var thepanel=panel[0];
			get_ajax_data_innerHTML("getxicoreajax",opts,true,thepanel);	
			}
						
	});
	</script>
	
	<div id="hostdetails-perfgraphs-panel-content">
	<img src="<?php echo theme_image("throbber.gif");?>"> Loading performance graphs...
	</div>
	
	
	
<?php
?>
	</div>
	<!-- performance graphs tab -->
	
	<!-- advanced tab -->
<?php
	if(is_advanced_user()){
?>
	<div id="tab-advanced" class="ui-tabs-hide">
	
	<div class="statusdetail_panelspacer"></div>
	
	<div style="float: left; margin-bottom: 25px;"><!--state info-->
<?php

	$args=array(
		"hostname" => $host,
		"host_id" => $host_id,
		"display" => "advanced",
		);

	// build args for javascript
	$n=0;
	$jargs="{";
	foreach($args as $var => $val){
		if($n>0)
			$jargs.=", ";
		$jargs.="\"$var\" : \"$val\"";
		$n++;
		}
	$jargs.="}";

	$id="host_state_info_".random_string(6);
				$output='
	<div class="host_state_info" id="'.$id.'">
	'.xicore_ajax_get_host_status_detailed_info_html($args).'
	</div><!--host_state_info-->
	<script type="text/javascript">
	$(document).ready(function(){
			
		$("#'.$id.'").everyTime(10*1000, "timer-'.$id.'", function(i) {
		var optsarr = {
			"func": "get_host_status_detailed_info_html",
			"args": '.$jargs.'
			}
		var opts=array2json(optsarr);
		get_ajax_data_innerHTML("getxicoreajax",opts,true,this);
		});
		
	});
	</script>
			';
?>
	<?php echo $output;?>
	</div><!--state info-->

	<div style="float: left;">
<?php

	$args=array(
		"hostname" => $host,
		"host_id" => $host_id,
		"display" => "all",
		);

	// build args for javascript
	$n=0;
	$jargs="{";
	foreach($args as $var => $val){
		if($n>0)
			$jargs.=", ";
		$jargs.="\"$var\" : \"$val\"";
		$n++;
		}
	$jargs.="}";

	$id="advanced_hoststatus_attributes_".random_string(6);
				$output='
	<div class="advanced_hoststatus_attributes" id="'.$id.'">
	'.xicore_ajax_get_host_status_attributes_html($args).'
	</div><!--advanced_hoststatus_attributes-->
	<script type="text/javascript">
	$(document).ready(function(){
			
		$("#'.$id.'").everyTime(10*1000, "timer-'.$id.'", function(i) {
		var optsarr = {
			"func": "get_host_status_attributes_html",
			"args": '.$jargs.'
			}
		var opts=array2json(optsarr);
		get_ajax_data_innerHTML("getxicoreajax",opts,true,this);
		});
		
	});
	</script>
			';
?>
	<?php echo $output;?>
	</div>

	<br clear="all">

	<div style="float: left; margin-right: 50px;"><!--advanced commands-->
	
<?php
	if($auth_command){
?>
	<div class="infotable_title">Commands</div>
	
	<table class="infotable">
	<tbody>

<?php
	$urlbase=get_base_url()."includes/components/nagioscore/ui/cmd.php?cmd_typ=";
	$urlmod="&host=".urlencode($host);
?>	
	<?php
		if($xml && intval($xml->hoststatus->problem_acknowledged)==1){
	?>
	<tr><td nowrap><?php show_object_command_link($urlbase.NAGIOSCORE_CMD_REMOVE_HOST_ACKNOWLEDGEMENT.$urlmod,"noack.gif","Remove problem acknowledgement");?></td></tr>
	<?php
		}
	?>

	<tr><td nowrap><?php show_object_command_link($urlbase.NAGIOSCORE_CMD_SCHEDULE_HOST_DOWNTIME.$urlmod,"downtime.gif","Schedule downtime");?></td></tr>

	<tr><td nowrap><?php show_object_command_link($urlbase.NAGIOSCORE_CMD_PROCESS_HOST_CHECK_RESULT.$urlmod,"passiveonly.gif","Submit passive check result");?></td></tr>

	<tr><td nowrap><?php show_object_command_link($urlbase.NAGIOSCORE_CMD_SEND_CUSTOM_HOST_NOTIFICATION.$urlmod,"notify.gif","Send custom notification");?></td></tr>

	<tr><td nowrap><?php show_object_command_link($urlbase.NAGIOSCORE_CMD_DELAY_HOST_NOTIFICATION.$urlmod,"delay.gif","Delay next notification");?></td></tr>

	
	</tbody>
	</table>
<?php
		}
?>
	</div><!--advanced commands-->
	
	<div style="float: left;">
	<div class="infotable_title">More Options</div>
	<ul>
	<li><a href="<?php echo get_host_status_detail_link($host,"core");?>">See this host in Nagios Core</a></li>
	</ul>
	</div>
	
	
		
	</div>
	<!-- advanced tab -->
<?php
		}
?>
	
	<!-- configure tab -->
<?php
	if($show_configure==true){
?>
	<div id="tab-configure" class="ui-tabs-hide">
<?php

	echo "<p>";
	echo "<img src='".theme_image("editsettings.png")."' style='float: left; margin-right: 10px;'>";
	
	$url=get_base_url()."config/configobject.php?host=".urlencode($host)."&return=hostdetail";
	echo "<a href='".$url."'>Re-configure this host</a>";

	/*
	if(is_host_configurable($host)==true){
		$url=get_base_url()."config/modifyobject.php?host=".$host."&return=hostdetail";
		echo "<a href='".$url."'>Modify the settings for this host</a>";
		}
	else{
		if(is_advanced_user()==true){
			$url=get_base_url()."config/nagioscorecfg/";
			echo "<a href='".$url."' target='_top'>Enter the advanced configuration manager</a> to modify the settings for this host.";
			}
		else
			echo "Contact your Nagios administrator to modify the settings for this host.";
		}
	*/
		
	echo "<br>";
	echo "<p>";
	echo "<img src='".theme_image("delete.png")."' style='float: left; margin-right: 10px;'>";

	/*
	if(can_host_be_deleted($host)==true){
		$url=get_base_url()."config/deleteobject.php?host=".$host."&return=hostdetail";
		echo "<a href='".$url."'>Delete this host</a>";
		}
	else{
		if(is_advanced_user()==true){
			$url=get_base_url()."config/nagioscorecfg/";
			echo "<a href='".$url."' target='_top'>Enter the advanced configuration manager</a> to delete this host.";
			}
		else
			echo "Contact your Nagios administrator to delete this host.";
		}
	*/
	$url=get_base_url()."config/deleteobject.php?host=".urlencode($host)."&return=hostdetail";
	echo "<a href='".$url."'>Delete this host</a>";
		
?>

	</div>
<?php
		}
?>
	<!-- configure tab -->
	
	
<?php
	// custom tabs
	foreach($customtabs as $ct){
		$id=grab_array_var($ct,"id");
		$title=grab_array_var($ct,"title");
		$content=grab_array_var($ct,"content");
		echo "<div id='tab-custom-".$id."' class='ui-tabs-hide'>".$content."</div>";
		}
?>

</div>

<?php
	do_page_end(true);
	}



function xicore_ajax_get_host_detail_perfgraphs_panel($args=null){
	global $lstr;

	if($args==null)
		$args=array();
		
	$output="";
	
	//return "TESTING...";
		
	$host=grab_array_var($args,"hostname");
	$host_id=grab_array_var($args,"host_id");
	
	//$output.="HOSTNAME='$host'<BR>";
	//$output.=serialize($args)."<BR>";

	$have_chart=false;
	$current_graph=0;

	if(perfdata_chart_exists($host,"")==true){
	
		$current_graph++;
	
		$have_chart=true;
		
		// primary host performance graph
		$output.="<div class='serviceperfgraphcontainer'>\n";
		$dargs=array(
			DASHLET_ADDTODASHBOARDTITLE => "Add This Performance Graph To A Dashboard",
			DASHLET_ARGS => array(
				"host_id" => $host_id,
				"hostname" => $host,
				"servicename" => "_HOST_",
				"source" => 1,
				"view" => 1,
				//"start" => $start,
				//"end" => $end,
				//"width" => "",
				//"height" => "",
				"mode" => PERFGRAPH_MODE_HOSTOVERVIEW,
				),
			DASHLET_TITLE => $host." Host Performance Graph",
			);
		display_dashlet("xicore_perfdata_chart","",$dargs,DASHLET_MODE_OUTBOARD);
		$output.="</div>";
		}

	// get services
	$args=array(
		"cmd" => "getservicestatus",
		"host_name" => $host,
		"brevity" => 2,
		);
		
	$xml=get_backend_xml_data($args);

	// loop over all services
	foreach($xml->servicestatus as $s){
	
		$hostname=strval($s->host_name);
		$servicename=strval($s->name);
	
		// skip this if the service doesn't have any perfdata
		if(perfdata_chart_exists($hostname,$servicename)==false)
			continue;
			
		$current_graph++;
		
		// limit to 5 graphs
		if($current_graph>5)
			break;
	
		$have_chart=true;
	
		$output.="<div class='serviceperfgraphcontainer'>\n";
		
		$dargs=array(
			DASHLET_ADDTODASHBOARDTITLE => "Add This Performance Graph To A Dashboard",
			DASHLET_ARGS => array(
				"service_id" => intval($s->service_id),
				"hostname" => $hostname,
				"servicename" => $servicename,
				"source" => 1,
				"view" => 1,
				//"start" => $start,
				//"end" => $end,
				//"width" => "",
				//"height" => "",
				"mode" => PERFGRAPH_MODE_HOSTOVERVIEW,
				),
			DASHLET_TITLE => $hostname." ".$servicename." Performance Graph",
			);
		
		ob_start();
		display_dashlet("xicore_perfdata_chart","",$dargs,DASHLET_MODE_OUTBOARD);
		$dashlet_output=ob_get_clean();
		$output.=$dashlet_output;
		//ob_end_clean();
		
		$output.="</div>";
		}
		
	if($have_chart==true){			
		$output.='
		<script type="text/javascript">
		$(document).ready(function(){
			// initialize javascript for dashifying performance graphs
			init_dashlet_js();	
			});
		</script>
		';	
		}

	if($have_chart==false){
		$output.=$lstr['NoPerformanceGraphsFoundForHostText'];
		}
	else if($current_graph>5){
		$output.="<a href='".get_base_url()."perfgraphs/?host=".urlencode($hostname)."&mode=1'>More Performance Graphs</a>";
		}

	return $output;
	}
	
function xicore_ajax_get_service_detail_perfgraphs_panel($args=null){
	global $lstr;

	if($args==null)
		$args=array();
		
	$output="";
	
	$host=grab_array_var($args,"hostname");
	$service=grab_array_var($args,"servicename");
	$service_id=grab_array_var($args,"service_id");
	
	if(perfdata_chart_exists($host,$service)==true){
	
		$sources=perfdata_get_service_sources($host,$service);
		foreach($sources as $s){
	
			$output.="<div class='serviceperfgraphcontainer'>\n";
		
			$dargs=array(
				DASHLET_ADDTODASHBOARDTITLE => "Add This Performance Graph To A Dashboard",
				DASHLET_ARGS => array(
					"hostname" => $host,
					"servicename" => $service,
					"service_id" => $service_id,
					//"source" => 1,
					"source" => $s["id"], // fix by Antal Ferenc 01/27/2010
					"sourcename" => $s["name"],
					"sourcetemplate" => $s["template"],
					"view" => 1,
					//"start" => $start,
					//"end" => $end,
					"width" => "250",
					//"height" => "50",
					"mode" => PERFGRAPH_MODE_GOTOSERVICEDETAIL,
					),
				DASHLET_TITLE => htmlentities($host)." ".htmlentities($service)." Performance Graph",
				);
				
			ob_start();
			display_dashlet("xicore_perfdata_chart","",$dargs,DASHLET_MODE_OUTBOARD);	
			$dashlet_output=ob_get_clean();
			$output.=$dashlet_output;
			
		
			$output.="</div>";
			}
			
			$output.='
			<script type="text/javascript">
			$(document).ready(function(){
				// initialize javascript for dashifying performance graphs
				init_dashlet_js();	
				});
			</script>
			';			
		}
	else{
		$output.=$lstr['NoPerformanceGraphsFoundForServiceText'];
		}

	return $output;
	}
?>
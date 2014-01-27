<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: main.php 75 2010-04-01 19:40:08Z egalstad $

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


route_request();

function route_request(){
	global $request;

	if(isset($request['update']))
		do_add_report();
	if(isset($request['delete']))
		do_delete_report();
	else if(isset($request['add']))
		show_add_report();
	else if(isset($request['go']))
		visit_report();
	else
		show_reports();
	}
	

function show_reports($error=false,$msg=""){
	global $lstr;
	
	// start the HTML page
	do_page_start(array("page_title"=>$lstr['MyReportsPageTitle']),true);
	
?>
	<h1><?php echo $lstr['MyReportsPageTitle'];?></h1>
	
<?php
	display_message($error,false,$msg);
?>

	<p>
	Your saved reports are shown below.<br>Tip: You can add a new report to this list by clicking on the star (<img src="<?php echo theme_image("star.png");?>">) icon when viewing the report.
	</p>

	<table class="standardtable">
	<thead> 
	<tr><th>Report Name</th><th>Actions</th></tr>
	</thead>
	<tbody>
	
<?php
	$mr=get_myreports(0);
	foreach($mr as $id => $r){
		echo "<tr>";
		echo "<td>".htmlentities($r["title"])."</td>";
		echo "<td>";
		echo "<a href='?delete=1&id=".$id."&nsp=".get_nagios_session_protector_id()."'><img src='".theme_image("delete.png")."' alt='".$lstr['DeleteAlt']."' title='".$lstr['DeleteAlt']."'></a>&nbsp;";
		echo "<a href='?go=1&id=".$id."&nsp=".get_nagios_session_protector_id()."'><img src='".theme_image("b_next.png")."' alt='".$lstr['ViewAlt']."' title='".$lstr['ViewAlt']."'></a>";
		echo "</td>";
		echo "</tr>";
		}
	if(count($mr)==0)
		echo "<tr><td colspan='2'>You have no saved reports.</td></tr>";
?>
	
	</tbody>
	</table>
	
<?php		
	
	// closes the HTML page
	do_page_end(true);
	exit();
	}
	
function visit_report(){

	// grab variables
	$id=grab_request_var("id",0);

	$url=get_myreport_url(0,$id);
	
	if($url=="")
		show_reports(true,"Invalid report.  Please select a saved report from the list below.");
		
	//echo "REDIRECTING TO: $url<BR>";
	
	header("Location: ".$url);
	}

	
function show_add_report($error=false,$msg=""){
	global $lstr;
	
	// grab variables
	$title=grab_request_var("title","My Report");
	$url=grab_request_var("url","");
	$meta_s=grab_request_var("meta_s","");

	// start the HTML page
	do_page_start(array("page_title"=>$lstr['AddToMyReportsPageTitle']),true);
	
?>
	<h1><?php echo $lstr['AddToMyReportsPageTitle'];?></h1>
	
<?php
	display_message($error,false,$msg);
?>

	<p>
	Use this function to save reports that you frequently access to your "My Reports" menu.
	</p>

	<form id="manageOptionsForm" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">

	<?php echo get_nagios_session_protector();?>
	<input type="hidden" name="update" value="1">
	<input type="hidden" name="url" value="<?php echo encode_form_val($url);?>">
	<input type="hidden" name="meta_s" value="<?php echo encode_form_val($meta_s);?>">

	<table class="manageOptionsTable">

	<tr>
	<td valign="top">
	<label for="titleBox">Report Title:</label><br class="nobr" />
	</td>
	<td>
	<input type="text" size="40" name="title" id="titleBox" value="<?php echo encode_form_val($title);?>" class="textfield" /><br class="nobr" />
	The name you want to use for this report.
	</td>
	<tr>
	
	<tr>
	<td></td>
	<td>
	<div id="formButtons">
	<input type="submit" class="submitbutton" name="updateButton" value="<?php echo $lstr['SaveReportButton'];?>" id="updateButton">
	<input type="submit" class="submitbutton" name="cancelButton" value="<?php echo $lstr['CancelButton'];?>" id="cancelButton">
	</div>
	</td>
	</tr>
	
	</table>
	
	
	</form>
	

<?php		

	
	// closes the HTML page
	do_page_end(true);
	exit();
	}

	
function do_delete_report(){

	// check session
	check_nagios_session_protector();

	// grab variables
	$id=grab_request_var("id",-1);
	
	$errmsg=array();
	$errors=0;

	// check for errors
	if(in_demo_mode()==true)
		$errmsg[$errors++]=$lstr['DemoModeChangeError'];
	if($id==-1)
		$errmsg[$errors++]="Invalid report.";
		
	// handle errors
	if($errors>0)
		show_reports(true,$errmsg);
		
	// delete the report
	delete_myreport(0,$id);
		
	show_reports(false,"Report deleted.");	
	}

	
function do_add_report(){
	global $request;

	// grab variables
	$title=grab_request_var("title","My Report");
	$url=grab_request_var("url","");
	$meta_s=grab_request_var("meta_s","");
	
	if($meta_s=="")
		$meta=array();
	else
		$meta=unserialize($meta_s);
	
	
	// user pressed the cancel button
	if(isset($request["cancelButton"])){
		header("Location: myreports.php");
		exit();
		}
		
	// check session
	check_nagios_session_protector();

	$errmsg=array();
	$errors=0;

	// check for errors
	if(in_demo_mode()==true)
		$errmsg[$errors++]=$lstr['DemoModeChangeError'];
	if(have_value($url)==false)
		$errmsg[$errors++]="Invalid report URL.";
	if(have_value($title)==false)
		$errmsg[$errors++]="No report title specified.";
		
	// handle errors
	if($errors>0)
		show_add_report(true,$errmsg);
		
		
	/*
	echo "ADDING REPORT!<BR>";
	echo "TITLE: $title<BR>";
	echo "URL: $url<BR>";
	*/
	
	//  save  report
	add_myreport(0,$title,$url,$meta);
	
	show_reports(false,"Report saved.");
	
	}
	
	

	
?>


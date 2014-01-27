<?php
//
// Copyright (c) 2011 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: mytools.php 946 2011-12-30 15:13:27Z egalstad $

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
		do_update_tool();
	if(isset($request['delete']))
		do_delete_tool();
	else if(isset($request['edit']))
		show_edit_tool();
	else if(isset($request['go']))
		visit_tool();
	else
		show_tools();
	}
	

function show_tools($error=false,$msg=""){
	global $lstr;
	
	// start the HTML page
	do_page_start(array("page_title"=>$lstr['MyToolsPageTitle']),true);
	
?>
	<h1><?php echo $lstr['MyToolsPageHeader'];?></h1>
	
<?php
	display_message($error,false,$msg);
?>


	<p>
	Your personal tools that you have defined are available only to you.
	</p>
	<p>
	<a href="?edit=1">Add a new tool</a>
	</p>

	<table class="standardtable">
	<thead> 
	<tr><th>Tool Name</th><th>URL</th><th>Actions</th></tr>
	</thead>
	<tbody>
	
<?php
	$mr=get_mytools(0);
	foreach($mr as $id => $r){
		echo "<tr>";
		echo "<td>".$r["name"]."</td>";
		echo "<td><a href='".htmlentities($r["url"])."' target='_blank'>".htmlentities($r["url"])."</a></td>";
		echo "<td>";
		echo "<a href='?edit=1&id=".$id."&nsp=".get_nagios_session_protector_id()."'><img src='".theme_image("edit.png")."' alt='".$lstr['EditAlt']."' title='".$lstr['EditAlt']."'></a>&nbsp;";
		echo "<a href='?delete=1&id=".$id."&nsp=".get_nagios_session_protector_id()."'><img src='".theme_image("delete.png")."' alt='".$lstr['DeleteAlt']."' title='".$lstr['DeleteAlt']."'></a>&nbsp;";
		echo "<a href='?go=1&id=".$id."&nsp=".get_nagios_session_protector_id()."'><img src='".theme_image("b_next.png")."' alt='".$lstr['ViewAlt']."' title='".$lstr['ViewAlt']."'></a>";
		echo "</td>";
		echo "</tr>";
		}
	if(count($mr)==0)
		echo "<tr><td colspan='3'>You haven't defined any tools yet.  <a href='?edit=1'>Add one now</a></td></tr>";
?>
	
	</tbody>
	</table>
	
<?php		
	
	// closes the HTML page
	do_page_end(true);
	exit();
	}
	
function visit_tool(){

	// grab variables
	$id=grab_request_var("id",0);

	$url=get_mytool_url(0,$id);
	
	if($url=="")
		show_tools(true,"Invalid tool.  Please select a tool from the list below.");
		
	//echo "REDIRECTING TO: $url<BR>";
	//exit();
	
	header("Location: ".$url);
	}

	
function show_edit_tool($error=false,$msg=""){
	global $lstr;
	
	// defaults
	$name="New Tool";
	$url="";
	
	// grab variables
	$id=grab_request_var("id",-1);
	
	$add=false;
	if($id==-1)
		$add=true;

	if($add==true){
		$pagetitle=$lstr['AddToMyToolsPageTitle'];
		$pageheader=$lstr['AddToMyToolsPageHeader'];
		}
	else{
		$pagetitle=$lstr['EditMyToolsPageTitle'];
		$pageheader=$lstr['EditMyToolsPageHeader'];
		
		// load old values
		$mytool=get_mytool_id(0,$id);
		$name=grab_array_var($mytool,"name",$name);
		$url=grab_array_var($mytool,"url",$url);
		}

	// get posted variables
	$name=grab_request_var("name",$name);
	$url=grab_request_var("url",$url);
	

	// start the HTML page
	do_page_start(array("page_title"=>$pagetitle),true);
	
?>
	<h1><?php echo $pageheader;?></h1>
	
<?php
	display_message($error,false,$msg);
?>


	<form id="manageOptionsForm" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">

	<?php echo get_nagios_session_protector();?>
	<input type="hidden" name="update" value="1">
	<input type="hidden" name="id" value="<?php echo encode_form_val($id);?>">
	
<?php
	if($add==true){
?>
	<p>
	Use this form to define a new tool that you can quickly access from Nagios.  
	</p>
<?php
		}
	else{
?>
<?php
		}
?>

	<table class="manageOptionsTable">

	<tr>
	<td valign="top">
	<label for="nameBox">Tool Name:</label><br class="nobr" />
	</td>
	<td>
	<input type="text" size="40" name="name" id="nameBox" value="<?php echo encode_form_val($name);?>" class="textfield" /><br class="nobr" />
	The name you want to use for this tool.
	</td>
	<tr>
	
	<tr>
	<td valign="top">
	<label for="urlBox">URL:</label><br class="nobr" />
	</td>
	<td>
	<input type="text" size="40" name="url" id="urlBox" value="<?php echo encode_form_val($url);?>" class="textfield" /><br class="nobr" />
	The URL used to access this tool.
	</td>
	<tr>
	
	<tr>
	<td></td>
	<td>
	<div id="formButtons">
	<input type="submit" class="submitbutton" name="updateButton" value="<?php echo $lstr['SaveButton'];?>" id="updateButton">
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

	
function do_delete_tool(){

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
		$errmsg[$errors++]="Invalid tool.";
		
	// handle errors
	if($errors>0)
		show_tools(true,$errmsg);
		
	// delete the tool
	delete_mytool(0,$id);
		
	show_tools(false,"Tool deleted.");	
	}

	
function do_update_tool(){
	global $request;

	// grab variables
	$id=grab_request_var("id",-1);
	$name=grab_request_var("name","New Tool");
	$url=grab_request_var("url","");
	
	
	// user pressed the cancel button
	if(isset($request["cancelButton"])){
		header("Location: mytools.php");
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
		$errmsg[$errors++]="Invalid tool URL.";
	if(have_value($name)==false)
		$errmsg[$errors++]="No tool name specified.";
		
	// handle errors
	if($errors>0)
		show_edit_tool(true,$errmsg);

	
	//  save tool
	update_mytool(0,$id,$name,$url);
	
	show_tools(false,"Tool saved.");
	
	}
	
	

	
?>


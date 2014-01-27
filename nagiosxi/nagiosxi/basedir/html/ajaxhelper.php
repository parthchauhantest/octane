<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: ajaxhelper.php 1318 2012-08-15 21:38:02Z mguthrie $

require_once(dirname(__FILE__).'/includes/common.inc.php');

// initialization stuff
//pre_init();  // <!-- No need to initialize menus

// start session
init_session();

// grab GET or POST variables 
grab_request_vars();

// check prereqs
check_prereqs(false);

// check authentication
check_authentication(false);  // no redirect on install, show license, etc.

// route request
route_request();



function route_request(){
	global $request;
	
	// check session
	check_nagios_session_protector();

	$cmd=grab_request_var("cmd","");
	
	switch($cmd){
	
		// MISC
		case "keepalive":
			ah_keepalive();
			break;
		case "setsessionvars":
			ah_set_session_vars();
			break;
		case "getlangstring":
			ah_get_language_string();
			break;
		case "getformattednumber":
			ah_get_formatted_number();
			break;
		case "gettimestampstring":
			ah_get_timestamp_string();
			break;
		case "getdatetimestring":
			ah_get_datetime_string();
			break;
			
		// USER META
		case "setusermeta":
			ah_set_user_meta();
			break;
		case "getusermeta":
			ah_get_user_meta();
			break;
			
		// XI CORE AJAX
		case "getxicoreajax":
			session_write_close(); //dev experiment
			ah_get_xicore_ajaxdata();
			break;
			
		// COMMANDS
		case "getcommandstatus":
			ah_get_command_status();
			break;
			
		// COMMANDS
		case "submitcommand":
			ah_submit_command();
			break;
			
		// USERS
		case "masquerade":
			ah_masquerade();
			break;
			
		// VIEWS
		case "addview":
			ah_add_view();
			break;
		case "updateview":
			ah_update_view();
			break;
		case "getviewbynum":
			ah_get_view_by_number();
			break;	
		case "getviewbyid":
			ah_get_view_by_id();
			break;	
		case "deleteviewbyid":
			ah_delete_view_by_id();
			break;	
		case "getviewsmenuhtml":
			ah_get_views_menu_html();
			break;
		case "getviewnumfromid":
			ah_get_view_num_from_id();
			
		// DASHBOARDS
		case "adddashboard":
			ah_add_dashboard();
			break;
		case "updatedashboard":
			ah_update_dashboard();
			break;
		case "getdashboardbyid":
			ah_get_dashboard_by_id();
			break;	
		case "getdashboardbynum":
			ah_get_dashboard_by_number();
			break;	
		case "deletedashboardbyid":
			ah_delete_dashboard_by_id();
			break;	
		case "clonedashboardbyid":
			ah_clone_dashboard_by_id();
			break;
		case "getdashboardsmenuhtml":
			ah_get_dashboards_menu_html();
			break;
		case "getdashboardnumfromid":
			ah_get_dashboard_num_from_id();
			break;
			
		// DASHLETS
		case "addtodashboard":
			ah_add_to_dashboard();
			break;
		case "getdashboardselectmenuhtml":
			ah_get_dashboard_select_menu_html();
			break;
		case "getadddashletdata":
			session_write_close(); //dev experiment 
			ah_get_add_dashlet_data();
			break;
		case "setdashletproperty":
			ah_set_dashlet_property();
			break;
		case "deletedashlet":
			ah_delete_dashlet();
			break;
			
		// CONFIG WIZARDS
		case "doconfigwizardinstallpostback":
			ah_do_config_wizard_install_postback();
			break;
			
		default:
			break;
		}
	
	exit;
	}
	

////////////////////////////////////////////////////////////////////////
// CONFIG WIZARDS
////////////////////////////////////////////////////////////////////////
	
function ah_do_config_wizard_install_postback(){

	// get the command data
	$opts=grab_request_var("opts","");
	
	$optsarr_s=json_decode($opts);
	//echo "OPTSARR_s:\n";
	//print_r($optsarr_s);
	if($optsarr_s){
		$optsarr=(array)$optsarr_s;
		$wizard=$optsarr["wizard"];
		$wizard_result=$optsarr["result"];
		$wizard_request=(array)$optsarr["request"];
		}
	else
		return;

	//echo "WIZARD_REQUEST:\n";
	//print_r($wizard_request);
		
	// make config wizard callback
	$inargs=$wizard_request;
	$outargs=array();
	$result=0;
	
	//echo "INARGS:\n";
	//print_r($inargs);
	
	require_once(dirname(__FILE__).'/includes/configwizards.inc.php');
	
	if($wizard_result==0)
		$wo=make_configwizard_callback($wizard,CONFIGWIZARD_MODE_COMMITOK,$inargs,$outargs,$result);
	else
		$wo=make_configwizard_callback($wizard,CONFIGWIZARD_MODE_COMMITCONFIGERROR,$inargs,$outargs,$result);
	}


////////////////////////////////////////////////////////////////////////
// USER META FUNCTIONS
////////////////////////////////////////////////////////////////////////

function ah_set_user_meta(){
	
	// initial values
	$keyname="";
	$keyvalue="";
	$autoload=false;

	// get the command data
	$opts=grab_request_var("opts","");

	$optsarr_s=json_decode($opts);
	echo "OPTSARR_s:\n";
	print_r($optsarr_s);
	if($optsarr_s){
		$optsarr=(array)$optsarr_s;
		$keyname=$optsarr["keyname"];
		$keyvalue=$optsarr["keyvalue"];
		$autoload=$optsarr["autoload"];
		}
		
	// only allow some user metas to be set for security reasons
	$allow=false;
	if($keyname=="view_rotation_speed")
		$allow=true;
	else if($keyname=="show_login_alert_screen")
		$allow=true;
		
	if($allow==false){
		echo "NOT ALLOWED";
		return;
		}
		
	// set meta
	set_user_meta(0,$keyname,$keyvalue,$autoload);

	echo "DONE";
	}

function ah_get_user_meta(){
	
	// get the command data
	$keyname=grab_request_var("opts","");
		
	// set meta
	$keyvalue=get_user_meta(0,$keyname);
	if($keyvalue==null)
		$keyvalue=="";

	echo $keyvalue;
	}
	
	
////////////////////////////////////////////////////////////////////////
// COMMAND FUNCTIONS
////////////////////////////////////////////////////////////////////////

function ah_submit_command(){
	
	// initial values
	$command_id=COMMAND_NONE;
	$command_data="";
	$event_time=time();
	$command_args=array();
	$result=-1;

	// get the command data
	$opts=grab_request_var("opts","");

	$optsarr_s=json_decode($opts);
	//echo "OPTSARR_s:\n";
	//print_r($optsarr_s);
	if($optsarr_s){
		$optsarr=(array)$optsarr_s;
		$command_id=intval($optsarr["cmd"]);
		if(array_key_exists("cmddata",$optsarr)){
			$cmddata_arr=(array)($optsarr["cmddata"]);
			$command_data=serialize($cmddata_arr);
			//echo "CMDDATA_ARR:\n";
			//print_r($cmddata_arr);
			//echo "COMMAND_DATA: $command_data\n";
			}
		if(array_key_exists("cmdtime",$optsarr))
			$event_time=$optsarr["cmdtime"];
		if(array_key_exists("cmdargs",$optsarr))
			$command_args=$optsarr["cmdargs"];

		//echo "OPTSARR:\n";
		//print_r($optsarr);
		}
		
	//echo "COMMAND: $command_id, DATA: $command_data, TIME: $event_time, ARGS: ".serialize($command_args)."\n";
		
	// submit the command
	if($command_id!=COMMAND_NONE){
		$result=submit_command($command_id,$command_data,$event_time,0,$command_args);
		}
		
	//echo "RESULT: $result\n";

	echo $result;
	}

function ah_get_command_status(){

	// initialize the result array
	$result=array(
		"command_id" => -1,
		"status_code" => -1,
		"result_code" => -1,
		"submission_time" => "",
		"event_time" => "",
		"processing_time" => "",
		"result" => "",
		);

	// get the command id
	$opts=grab_request_var("opts","");

	// get command status from backend
	$args=array(
		"cmd" => "getcommands",
		"command_id" => $opts,
		);
	$xml=get_backend_xml_data($args);
	if($xml){
		$result["command_id"]=intval($opts);
		if($xml->command[0]){
			$result["submission_time"]=strval($xml->command[0]->submission_time);
			$result["event_time"]=strval($xml->command[0]->event_time);
			$result["processing_time"]=strval($xml->command[0]->processing_time);
			$result["status_code"]=strval($xml->command[0]->status_code);
			$result["result_code"]=strval($xml->command[0]->result_code);
			$result["result"]=strval($xml->command[0]->result);
			}
		}
		
	//print_r($xml);
		
	$output=json_encode($result);
	echo $output;
	}
	

////////////////////////////////////////////////////////////////////////
// USER FUNCTIONS
////////////////////////////////////////////////////////////////////////

function ah_masquerade(){
	$opts=grab_request_var("opts","");
	echo "OPTS: $opts\n";
	$urlopts=strstr($opts,"user_id=");
	echo "URLOPTS: $urlopts\n";
	$args=explode("?&",$urlopts);
	print_r($args);
	$userid=-1;
	// find the user id
	foreach($args as $varvalpair){
		$p=explode("=",$varvalpair);
		if($p[0]=="user_id")
			$userid=intval($p[1]);
		}
	echo "\nUSERID: $userid\n";
	masquerade_as_user_id($userid);
	}
	
	
	
////////////////////////////////////////////////////////////////////////
// CORE AJAX FUNCTIONS
////////////////////////////////////////////////////////////////////////

function ah_get_xicore_ajaxdata(){
	// what function does the user want to run
	$opts=grab_request_var("opts","");
	//echo "OPTS: $opts\n";
	$optsarr=json_decode($opts);
	//echo "OPTSARR:\n";
	//print_r($optsarr);
	$fname="xicore_ajax_".strval($optsarr->func);
	$args=array();
	if($optsarr->args){
		//echo "HAVEARGS\n";
		foreach($optsarr->args as $var => $val){
			//echo "VAR[$var]=$val\n";
			$args[strval($var)]=strval($val);
			}
		}
	//echo "ARGS: \n";
	//print_r($args);
	$output=$fname($args);
	echo $output;
	}


////////////////////////////////////////////////////////////////////////
// DASHLET FUNCTIONS
////////////////////////////////////////////////////////////////////////

function ah_delete_dashlet(){
	$opts=grab_request_var("opts","");
	//echo "OPTS: $opts\n";
	$optsarr=json_decode($opts);
	//echo "OPTS2: \n";
	//print_r($optsarr);
	
	$board_id=$optsarr->board;
	$dashlet_id=$optsarr->dashlet;
	
	echo "BOARD ID: $board_id\n";
	echo "DASHLET ID: $dashlet_id\n";
	
	remove_dashlet_from_dashboard(0,$board_id,$dashlet_id);
	}

function ah_set_dashlet_property(){
	$opts=grab_request_var("opts","");
	//echo "OPTS: $opts\n";
	$optsarr=json_decode($opts);
	//echo "OPTS2: \n";
	//print_r($optsarr);
	
	$board_id=$optsarr->board;
	$dashlet_id=$optsarr->dashlet;
	
	echo "BOARD ID: $board_id\n";
	echo "DASHLET ID: $dashlet_id\n";
	
	$props=array();
	foreach($optsarr->props as $propvar => $propval){
		$props[$propvar]=$propval;
		}
	echo "PROPS:\n";
	print_r($props);
	
	set_dashboard_dashlet_property(0,$board_id,$dashlet_id,$props);
	}

function ah_get_add_dashlet_data(){

	$opts=grab_request_var("opts",0);
	//echo "OPTS: $opts<BR>";
	//echo "OPTS1: ".base64_decode($opts)."\n";
	$a=unserialize(base64_decode($opts));
	//print_r($a);

	// initialize the return array
	$ret=array();
	$ret[DASHLET_NAME]="";
	$ret[DASHLET_TITLE]="";

	if(array_key_exists(DASHLET_NAME,$a))
		$ret[DASHLET_NAME]=$a[DASHLET_NAME];
	if(array_key_exists(DASHLET_TITLE,$a))
		$ret[DASHLET_TITLE]=$a[DASHLET_TITLE];
		
		
	$args=array();
	if(array_key_exists(DASHLET_ARGS,$a))
		$args=$a[DASHLET_ARGS];
		
	//echo "ABOUT TO PASS THESE ARGS\n";
	//print_r($args);
		
		
	// get config options from dashlet function...
	if($ret[DASHLET_NAME]!=""){
		$ret[DASHLET_CONFIGHTML]=get_dashlet_output($ret[DASHLET_NAME],"",DASHLET_MODE_GETCONFIGHTML,$args);
		}
	
	$output=json_encode($ret);
	echo $output;
	}

function ah_add_to_dashboard(){
	global $request;

	$name=grab_request_var("name",0);
	$title=grab_request_var("title",0);
	$board=grab_request_var("board",0);
	$paramsraw=grab_request_var("params",0);
	$params=unserialize(base64_decode($paramsraw));
	
	//echo "PARAMS:\n";
	//print_r($params);
	
	// get dashlet-specifc args
	$args=array();
	// save what was passed to us by default
	if(array_key_exists(DASHLET_ARGS,$params))
		$args=$params[DASHLET_ARGS];
	// add/override base on what was submitted
	foreach($request as $var => $val){
		// add args (skip some we use for other purposes)
		switch($var){
			case "cmd":
			case "submitButton":
			case "name":
			case "title":
			case "board":
			case "params":
				break;
			default:
				$args[$var]=$val;
				break;
			}
		}
		
	// opts are null for the time being - they should only contain opacity, height, width, info...
	$opts=array();
	
	add_dashlet_to_dashboard(0,$board,$name,$title,$opts,$args);
	}
	
function ah_get_dashboard_select_menu_html(){

	// add a default dashboard if none exists
	$dashboards=get_dashboards(0);
	if(count($dashboards)==0){
		$opts=array();
		add_dashboard(0,"Default Dashboard",$opts);
		}

	$html="";
	$dashboards=get_dashboards(0);
	foreach($dashboards as $d){
		$html.="<option value='".$d["id"]."'>".$d["title"]."</option>";
		}

	echo $html;
	}
	
	
////////////////////////////////////////////////////////////////////////
// DASHBOARD FUNCTIONS
////////////////////////////////////////////////////////////////////////
	
function ah_update_dashboard(){
	$id=grab_request_var("id",0);
	$optsraw=grab_request_var("opts",0);
	$title=grab_request_var("title",0);
	$background=grab_request_var("background",0);
	echo "ID: $id, TITLE: $title<BR>";
	
	$opts=array();
	$opts["background"]=$background;
	
	update_dashboard(0,$id,$title,$opts);
	}

function ah_get_dashboard_num_from_id(){
	$id=grab_request_var("opts",0);
	$dashboards=get_dashboards(0);
	//print_r($dashboards);
	$n=0;
	foreach($dashboards as $d){
		if($d["id"]==$id)
			break;
		$n++;
		}
	echo $n;
	}
	
	
function ah_get_dashboards_menu_html(){
	echo get_dashboards_menu_html(0);
	}
	
	
// gets dashboard details
function ah_get_dashboard_by_id(){
	global $request;
	
	$id=grab_request_var("opts",0);
	//echo "ID: $id<BR>";
	$dashboards=get_dashboards(0);
	$count=count($dashboards);

	// initialize the array
	$thedashboard=array(
		"id" => "nodashboardid",
		"url" => get_base_url()."/dashboards/dashboard.php",
		"title" => "",
		"background" => "",
		"number" => -1,
		);
	$n=0;
	foreach($dashboards as $dashboard){
		if($dashboard["id"]==$id){
			$thedashboard=array(
				"id" => $dashboards[$n]["id"],
				"url" => get_base_url()."/dashboards/dashboard.php?id=".$dashboards[$n]["id"],
				"title" => $dashboards[$n]["title"],
				"background" => $dashboards[$n]["opts"]["background"],
				"number" => $n,
				);
			}
		$n++;
		}
	
	$s=json_encode($thedashboard);

	echo $s;
	}
	
// gets dashboard details
function ah_get_dashboard_by_number(){
	global $request;
	
	$num=grab_request_var("opts",0);
	$dashboards=get_dashboards(0);
	$count=count($dashboards);
	if($count==0){
		$thedashboard=array(
			"id" => "nodashboardid",
			"url" => get_base_url()."/dashboards/dashboard.php",
			"title" => "",
			"background" => "",
			"number" => -1,
			);
		}
	else{
		$thedashboard=array(
			"id" => $dashboards[$num]["id"],
			"url" => get_base_url()."/dashboards/dashboard.php?id=".$dashboards[$num]["id"],
			"title" => $dashboards[$num]["title"],
			"background" => $dashboards[$num]["opts"]["background"],
			"number" => $num,
			);
		}
	$s=json_encode($thedashboard);

	echo $s;
	}
	
// deletes a dashboard
function ah_delete_dashboard_by_id(){
	global $request;
	
	$id=grab_request_var("opts",-1);
	//echo "ID: $id<BR>";
	delete_dashboard_id(0,$id);
	
	// add a default dashboard if that was the last one
	$dashboards=get_dashboards(0);
	if(count($dashboards)==0){
		$opts=array();
		add_dashboard(0,"Default Dashboard",$opts);
		}
	}

// clones a dashboard
function ah_clone_dashboard_by_id(){
	global $request;
	
	$id=grab_request_var("opts",-1);
	$title=grab_request_var("title","(Cloned)");
	//echo "ID: $id<BR>";
	clone_dashboard_id(0,$id,$title);
	}

// adds a dashboard
function ah_add_dashboard(){
	global $request;
	
	//$title=grab_request_var("title","(Untitled Dashboard - ajaxhelper)");
	$title=grab_request_var("title","");
	if($title==""){  // there is a bug somewhere in the js code that causes blank dashboards to be added  - this hack fixes that problem...
		echo "NO TITLE\n";
		return;
		}
	$opts=grab_request_var("opts","");
	$background=grab_request_var("background","");
	$opts["background"]=$background;
	
	echo "OPTS: \n";
	print_r($opts);
	
	add_dashboard(0,$title,$opts);
	}

	
////////////////////////////////////////////////////////////////////////
// VIEW FUNCTIONS
////////////////////////////////////////////////////////////////////////
	
function ah_update_view(){
	$id=grab_request_var("id",0);
	$url=grab_request_var("url",0);
	$title=grab_request_var("title",0);
	echo "ID: $id, URL: $url, TITLE: $title<BR>";
	update_view(0,$id,$url,$title);
	}

function ah_get_view_num_from_id(){
	$id=grab_request_var("opts",0);
	$views=get_views(0);
	//print_r($views);
	$n=0;
	foreach($views as $v){
		if($v["id"]==$id)
			break;
		$n++;
		}
	echo $n;
	}
	
	
function ah_get_views_menu_html(){
	echo get_views_menu_html(0);
	}
	
	
// gets view details
function ah_get_view_by_number(){
	global $request;
	
	$num=grab_request_var("opts",0);
	$views=get_views(0);
	$count=count($views);
	if($count==0){
		$theview=array(
			"id" => "noviewid",
			"url" => get_base_url()."/views/main.php",
			"title" => ""
			);
		}
	else{
		$thenum=$num%$count;
		$theview=$views[$thenum];
		}
	$s=json_encode($theview);

	echo $s;
	}
	
// gets view details
function ah_get_view_by_id(){
	global $request;
	
	$id=grab_request_var("opts",0);
	//echo "ID: $id<BR>";
	$views=get_views(0);
	$theview=$views[$id];
	$s=json_encode($theview);

	echo $s;
	}
	
// deletes a view
function ah_delete_view_by_id(){
	global $request;
	
	$id=grab_request_var("opts",0);
	delete_view_id(0,$id);
	}

// adds a view
function ah_add_view(){
	global $request;
	
	$url=grab_request_var("url","");
	$title=grab_request_var("title","(untitled)");
	
	if(!have_value($url))
		return;
		
	add_view(0,$url,$title);
	}

	
////////////////////////////////////////////////////////////////////////
// MISC FUNCTIONS
////////////////////////////////////////////////////////////////////////
	
// returns a formatted number
function ah_get_formatted_number(){
	global $request;
	global $lstr;
	
	$num=grab_request_var("num",0);
	$dec=grab_request_var("dec",-1);
	$fmt=grab_request_var("fmt","");
		
	echo get_formatted_number($num,$dec,$fmt);
	}

// returns a formatted date/time string
function ah_get_datetime_string(){
	global $request;
	global $lstr;
	
	$str=grab_request_var("t","");
	$tz=grab_request_var("tz","UTC");
	$type=grab_request_var("type",DT_SHORT_DATE_TIME);
	$fmt=grab_request_var("fmt",DF_AUTO);
	$zs=grab_request_var("zs","");
		
	echo get_datetime_string_from_datetime($str,$tz,$type,$fmt,$zs);
	}


// returns a formatted date/time string
function ah_get_timestamp_string(){
	global $request;
	global $lstr;
	
	$ts=grab_request_var("ts",time());
	$type=grab_request_var("type",DT_SHORT_DATE_TIME);
	$fmt=grab_request_var("fmt",DF_AUTO);
	$zs=grab_request_var("zs","");
		
	echo get_datetime_string($ts,$type,$fmt,$zs);
	}

// returns a language string - useful for javascript, etc.
function ah_get_language_string(){
	global $request;
	
	header("Content-Type: text/plain");
	
	if(isset($request["str"])){
		$str=$request["str"];
		if(is_array($str)){
			foreach($str as $s){
				ah_get_language_string2($s);
				}
			}
		else{
			$strs=explode(",",$str);
			foreach($strs as $s){
				ah_get_language_string2($s);
				}
			}
		}
	else
		echo "?\n";
	}
	
	
function ah_get_language_string2($str){
	global $lstr;

	if(isset($lstr[$str]))
		echo $lstr[$str]."\n";
	else
		echo "???\n";
	}

	
// session keepalive function used from frontend - this is a null-top
function ah_keepalive(){
	}
	
// set a session variable - ignore non-specified variables for security reasons
function ah_set_session_vars(){
	global $request;
	
	$optsarr=array();

	// get the data
	$opts=grab_request_var("opts","");

	$optsarr_s=json_decode($opts);
	echo "OPTSARR_s:\n";
	print_r($optsarr_s);
	if($optsarr_s){
		$optsarr=(array)$optsarr_s;
		}

	$sessionvars=$optsarr;

	
	foreach($sessionvars as $var => $val){
		switch($var){
			// only set non-security affecting variables
			case "ignore_notice_update":
			case "ignore_notice_language":
			case "ignore_trial_notice":
			case "ignore_free_notice":
				$_SESSION[$var]=$val;
				echo "SET VARIABLE '".$var."' to '".$val."'\n";
				break;
			default:
				// do nothing by default for security reasons
				break;
			}
		}
	echo "DONE\n";
	}


?>
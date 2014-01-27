<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// Development Started 03/22/2008
// $Id: utils-users.inc.php 1177 2012-05-09 21:58:50Z mguthrie $

//require_once(dirname(__FILE__).'/common.inc.php');

////////////////////////////////////////////////////////////////////////////////
// XML DATA
////////////////////////////////////////////////////////////////////////////////

function get_xml_users($args=array()){
	$x=simplexml_load_string(get_users_xml_output($args));
	//print_r($x);
	return $x;
	}

	
////////////////////////////////////////////////////////////////////////
// USER ACCOUNT FUNCTIONS
////////////////////////////////////////////////////////////////////////

function add_user_account($username,$password,$name,$email,$level,$forcepasschange,$addcontact,&$errmsg){
	global $db_tables;
	global $lstr;
	
	$error=false;
	$errors=0;
	
	$user_id=-1;

	// make sure we have required variables
	if(!have_value($username)){
		$error=true;
		$errmsg[$errors++]=$lstr['BlankUsernameError'];
		}
	if(!have_value($email)){
		$error=true;
		$errmsg[$errors++]=$lstr['BlankEmailError'];
		}
	else if(!valid_email($email)){
		$error=true;
		$errmsg[$errors++]=$lstr['InvalidEmailError'];
		}
	if(!have_value($name)){
		$error=true;
		$errmsg[$errors++]=$lstr['BlankNameError'];
		}
	if(!have_value($password)){
		$error=true;
		$errmsg[$errors++]=$lstr['BlankPasswordError'];
		}
	if(!have_value($level)){
		$error=true;
		$errmsg[$errors++]=$lstr['BlankSecurityLevelError'];
		}
		
	// does user account already exist?
	if(is_valid_user($username)==true){
		$error=true;
		$errmsg[$errors++]=$lstr['AccountNameCollisionError'];
		}

	// generate random backend ticket string
	$backend_ticket=random_string(64);
	
	// add account
	if($error==false){
		$sql="INSERT INTO ".$db_tables[DB_NAGIOSXI]["users"]." (username,email,name,password,backend_ticket) VALUES ('".escape_sql_param($username,DB_NAGIOSXI)."','".escape_sql_param($email,DB_NAGIOSXI)."','".escape_sql_param($name,DB_NAGIOSXI)."','".md5($password)."','".$backend_ticket."')";
		if(!exec_sql_query(DB_NAGIOSXI,$sql)){
			$error=true;
			$errmsg[$errors++]=$lstr['AddAccountFailedError'].": ".get_sql_error(DB_NAGIOSXI);
			}
		else
			$user_id=get_sql_insert_id(DB_NAGIOSXI,"xi_users_user_id_seq");
		}
	if($user_id<1){
		$errmsg[$errors++]="Unable to get insert id for new user account";
		$error=true;
		}
	if($error==false){
		// assign privs
		if(!set_user_meta($user_id,'userlevel',$level)){
			$error=true;
			$errmsg[$errors++]=$lstr['AddAccountPrivilegesFailedError'];
			}
		// force password change at next login
		if($forcepasschange==true)
			set_user_meta($user_id,'forcepasswordchange','1');

		// notification defaults
		set_user_meta($user_id,'enable_notifications','1',false);
		set_user_meta($user_id,'notify_by_email','1',false);
		
		set_user_meta($user_id,'notify_host_down','1',false);
		set_user_meta($user_id,'notify_host_unreachable','1',false);
		set_user_meta($user_id,'notify_host_recovery','1',false);
		set_user_meta($user_id,'notify_host_flapping','1',false);
		set_user_meta($user_id,'notify_host_downtime','1',false);
		set_user_meta($user_id,'notify_service_warning','1',false);
		set_user_meta($user_id,'notify_service_unknown','1',false);
		set_user_meta($user_id,'notify_service_critical','1',false);
		set_user_meta($user_id,'notify_service_recovery','1',false);
		set_user_meta($user_id,'notify_service_flapping','1',false);
		set_user_meta($user_id,'notify_service_downtime','1',false);

		$notification_times=array();
		for($day=0;$day<7;$day++){
			$notification_times[$day]=array(
				"start" => "00:00",
				"end" => "24:00",
				);
			}
		$notification_times_raw=serialize($notification_times);
		set_user_meta($user_id,'notification_times',$notification_times_raw,false);
		}
	
	// add/update corresponding contact to/in Nagios Core
	if($error==false && $addcontact==true){
		$contactargs=array(
			"contact_name" => $username,
			"alias" => $name,
			"email" => $email,
			);
		add_nagioscore_contact($contactargs);
		}

	if($error==false){
	
		// log it
		send_to_audit_log("New user account '".$username."' created",AUDITLOGTYPE_SECURITY);
	
		return $user_id;
		}
	else
		return null;
	}


function get_user_attr($user_id,$attr){
	global $db_tables;
	
	// use logged in user's id
	if($user_id==0 && isset($_SESSION["user_id"]))
		$user_id=$_SESSION["user_id"];
	
	// make sure we have required variables
	if(!have_value($user_id))
		return null;
	if(!have_value($attr))
		return null;

	// get attribute
	$sql="SELECT ".escape_sql_param($attr,DB_NAGIOSXI)." FROM ".$db_tables[DB_NAGIOSXI]["users"]." WHERE user_id='".escape_sql_param($user_id,DB_NAGIOSXI)."'";
	if(($rs=exec_sql_query(DB_NAGIOSXI,$sql,false))){
		if($rs->MoveFirst()){
			return $rs->fields[$attr];
			}
		}
	return null;
	}


function change_user_attr($user_id,$attr,$value){
	global $db_tables;
	
	// use logged in user's id
	if($user_id==0)
		$user_id=$_SESSION["user_id"];
	
	// make sure we have required variables
	if(!have_value($user_id))
		return error;
	if(!have_value($attr))
		return error;

	// update attribute
	$sql="UPDATE ".$db_tables[DB_NAGIOSXI]["users"]." SET ".escape_sql_param($attr,DB_NAGIOSXI)."='".escape_sql_param($value,DB_NAGIOSXI)."' WHERE user_id='".escape_sql_param($user_id,DB_NAGIOSXI)."'";
	if(!exec_sql_query(DB_NAGIOSXI,$sql))
		return false;
	return true;
	}


// checks if a user account exists
function is_valid_user($username){
	$id=get_user_id($username);
	if(!have_value($id))
		return false;
	return true;
	}

	
// checks if a user account exists (using id)
function is_valid_user_id($userid){
	global $db_tables;
	
	$sql="SELECT * FROM ".$db_tables[DB_NAGIOSXI]["users"]." WHERE user_id='".escape_sql_param($userid,DB_NAGIOSXI)."'";
	if(($rs=exec_sql_query(DB_NAGIOSXI,$sql))){
		if($rs->RecordCount()>0)
			return $rs->fields["user_id"];
		}
	return false;
	}

	
function get_user_id($username){
	global $db_tables;
	
	$sql="SELECT * FROM ".$db_tables[DB_NAGIOSXI]["users"]." WHERE username='".escape_sql_param($username,DB_NAGIOSXI)."'";
	if(($rs=exec_sql_query(DB_NAGIOSXI,$sql))){
		if($rs->RecordCount()>0)
			return $rs->fields["user_id"];
		}
	return null;
	}


// get all users in the database
function get_user_list(){
	global $db_tables;
	
	$sql="SELECT * FROM ".$db_tables[DB_NAGIOSXI]["users"]." ORDER BY username ASC";
	if(($rs=exec_sql_query(DB_NAGIOSXI,$sql)))
		return $rs;
	return null;
	}

	
function delete_user_id($userid,$deletecontact=true){
	global $db_tables;
	
	$username=get_user_attr($userid,"username");
	
	// log it
	send_to_audit_log("User deleted account '".$username."'",AUDITLOGTYPE_SECURITY);

	// delete corresponding contact from Nagios Core
	if($deletecontact==true){
		delete_nagioscore_contact($username);
		//return;
		}

	// delete user account
	$sql="DELETE FROM ".$db_tables[DB_NAGIOSXI]["users"]." WHERE user_id='".escape_sql_param($userid,DB_NAGIOSXI)."'";
	if(!($rs=exec_sql_query(DB_NAGIOSXI,$sql)))
		return false;
		
	// delete user meta
	$sql="DELETE FROM ".$db_tables[DB_NAGIOSXI]["usermeta"]." WHERE user_id='".escape_sql_param($userid,DB_NAGIOSXI)."'";
	if(!($rs=exec_sql_query(DB_NAGIOSXI,$sql)))
		return false;
	

	return true;
	}


////////////////////////////////////////////////////////////////////////
// USER AUTHORIZATION FUNCTION
////////////////////////////////////////////////////////////////////////

function is_admin($user_id=0){

	// subsystem cron jobs run with admin privileges
	if(defined("SUBSYSTEM"))
		return true;

	// use logged in user's id
	if($user_id==0)
		$user_id=$_SESSION["user_id"];
		
	$level=grab_array_var($_SESSION,"userlevel",get_user_meta($user_id,'userlevel'));
	// save level in session
	$SESSION["userlevel"]=$level;
	
	//return "[".$user_id."=".$level."]";
	if(intval($level)==L_GLOBALADMIN)
		return true;
	else
		return false;
	}


function get_authlevels(){
	global $lstr;

	$levels=array(
		L_USER => $lstr['UserLevelText'],
		L_GLOBALADMIN => $lstr['AdminLevelText']
		);

	return $levels;
	}


function is_valid_authlevel($level){

	$levels=get_authlevels();

	return array_key_exists($level,$levels);
	}


////////////////////////////////////////////////////////////////////////
// MISC USER FUNCTION
////////////////////////////////////////////////////////////////////////

function is_advanced_user($userid=0){

	if($userid==0)
		$userid=$_SESSION["user_id"];

	// admins are experts
	if(is_admin($userid)==true)
		return true;
		
	// certain users are experts
	$advanceduser=get_user_meta($userid,"advanced_user");
	if($advanceduser==1)
		return true;
	else
		return false;
	
	return false;
	}
	
function is_readonly_user($userid=0){

	if($userid==0)
		$userid=$_SESSION["user_id"];

	// admins are always read/write
	if(is_admin($userid)==true)
		return false;
		
	// certain users are experts
	$readonlyuser=get_user_meta($userid,"readonly_user");
	if($readonlyuser==1)
		return true;
	else
		return false;
	
	return false;
	}


	
////////////////////////////////////////////////////////////////////////
// USER META DATA FUNCTIONS
////////////////////////////////////////////////////////////////////////

function get_user_meta($user_id,$key){
	global $db_tables;

	// use logged in user's id
	if($user_id==0){
		if(!isset($_SESSION["user_id"]))
			return null;
		else
			$user_id=$_SESSION["user_id"];
		}
	
	$sql="SELECT * FROM ".$db_tables[DB_NAGIOSXI]["usermeta"]." WHERE user_id='".escape_sql_param($user_id,DB_NAGIOSXI)."' AND keyname='".escape_sql_param($key,DB_NAGIOSXI)."'";
	if(($rs=exec_sql_query(DB_NAGIOSXI,$sql))){
		if($rs->MoveFirst()){
			return $rs->fields["keyvalue"];
			}
		}
	return null;
	}


function get_all_user_meta($user_id){
	global $db_tables;
	
	$meta=array();

	// use logged in user's id
	if($user_id==0){
		if(!isset($_SESSION["user_id"]))
			return null;
		else
			$user_id=$_SESSION["user_id"];
		}
	
	$sql="SELECT * FROM ".$db_tables[DB_NAGIOSXI]["usermeta"]." WHERE user_id='".escape_sql_param($user_id,DB_NAGIOSXI)."'";
	if(($rs=exec_sql_query(DB_NAGIOSXI,$sql))){
		while(!$rs->EOF){
			$meta[$rs->fields["keyname"]]=$rs->fields["keyvalue"];
			$rs->MoveNext();
			}
		}
	return $meta;
	}

	
function get_user_meta_session_vars($overwrite=false){
	global $db_tables;
	
	if(!isset($_SESSION["user_id"]))
		return null;

	$sql="SELECT * FROM ".$db_tables[DB_NAGIOSXI]["usermeta"]." WHERE user_id='".escape_sql_param($_SESSION["user_id"],DB_NAGIOSXI)."' AND autoload='1'";
	if(($rs=exec_sql_query(DB_NAGIOSXI,$sql,false))){
		while(!$rs->EOF){
			// set session variable - skip some
			switch($rs->fields["keyname"]){
				case "user_id";  // security risk
					break;
				default:
					if(!($overwrite==false && isset($_SESSION[$rs->fields["keyname"]])))
						$_SESSION[$rs->fields["keyname"]]=$rs->fields["keyvalue"];
					break;
				}
			$rs->MoveNext();
			}
		}
	return null;
	}


function set_user_meta($user_id,$key,$value,$sessionload=true){
	global $db_tables;
	
	// use logged in user's id
	if($user_id==0)
		$user_id=$_SESSION["user_id"];
	
	$autoload=0;
	if($sessionload==true)
		$autoload=1;

	// see if data exists already
	$key_exists=false;
	$sql="SELECT * FROM ".$db_tables[DB_NAGIOSXI]["usermeta"]." WHERE user_id='".escape_sql_param($user_id,DB_NAGIOSXI)."' AND keyname='".escape_sql_param($key,DB_NAGIOSXI)."'";
	if(($rs=exec_sql_query(DB_NAGIOSXI,$sql))){
		if($rs->RecordCount()>0)
			$key_exists=true;
		}

	// insert new key
	if($key_exists==false){
		$sql="INSERT INTO ".$db_tables[DB_NAGIOSXI]["usermeta"]." (user_id,keyname,keyvalue,autoload) VALUES ('".escape_sql_param($user_id,DB_NAGIOSXI)."','".escape_sql_param($key,DB_NAGIOSXI)."','".escape_sql_param($value,DB_NAGIOSXI)."','".$autoload."')";
		return exec_sql_query(DB_NAGIOSXI,$sql);
		}

	// update existing key
	else{
		$sql="UPDATE ".$db_tables[DB_NAGIOSXI]["usermeta"]." SET keyvalue='".escape_sql_param($value,DB_NAGIOSXI)."', autoload='".$autoload."' WHERE user_id='".escape_sql_param($user_id,DB_NAGIOSXI)."' AND keyname='".escape_sql_param($key,DB_NAGIOSXI)."'";
		return exec_sql_query(DB_NAGIOSXI,$sql);
		}
		
	}

	
function delete_user_meta($user_id,$key){
	global $db_tables;

	// use logged in user's id
	if($user_id==0)
		$user_id=$_SESSION["user_id"];
	
	$sql="DELETE FROM ".$db_tables[DB_NAGIOSXI]["usermeta"]." WHERE user_id='".escape_sql_param($user_id,DB_NAGIOSXI)."' AND keyname='".escape_sql_param($key,DB_NAGIOSXI)."'";
	return exec_sql_query(DB_NAGIOSXI,$sql);
	}



////////////////////////////////////////////////////////////////////////
// USER MASQUERADE FUNCTIONS
////////////////////////////////////////////////////////////////////////


function masquerade_as_user_id($user_id=-1){

	// only admins can masquerade
	if(is_admin()==false)
		return;
		
	$original_user=$_SESSION["username"];
	
	if(!is_valid_user_id($user_id)){
		return;
		}
		
	$username=get_user_attr($user_id,"username");
	
	//echo "GOOD TO GO";

	///////////////////////////////////////////////////////////////
	// DESTROY CURRENT USER SESSION
	///////////////////////////////////////////////////////////////
	//  destroy the session.
	deinit_session();
	init_session();
	
	// reinitialize theme
	//init_theme();
	
	// reinitialize the menu
	//init_menus();
	
	///////////////////////////////////////////////////////////////
	// SETUP NEW USER SESSION
	///////////////////////////////////////////////////////////////

	// set session variables
	$_SESSION["user_id"]=$user_id;
	$_SESSION["username"]=$username;
				
	// load user session variables (e.g. preferences)
	get_user_meta_session_vars(true);

	// log it
	send_to_audit_log("User '".$original_user."' masqueraded as user '".$username."'",AUDITLOGTYPE_SECURITY);
	}

	

////////////////////////////////////////////////////////////////////////
// DEFAULT VIEWS/DASHBOARDS FUNCTIONS
////////////////////////////////////////////////////////////////////////

	
function add_default_views($userid=0){

	// add some views for the user if they don't have any
	$views=get_user_meta($userid,"views");
	if($views==null || $views==""){
		add_view($userid,"/nagiosxi/includes/components/nagioscore/ui/tac.php","Tactical Overview");
		add_view($userid,"/nagiosxi/includes/components/xicore/status.php?show=services&hoststatustypes=2&servicestatustypes=28&serviceattr=10","Open Problems");
		add_view($userid,"/nagiosxi/includes/components/xicore/status.php?show=hosts","Host Detail");
		add_view($userid,"/nagiosxi/includes/components/xicore/status.php?show=services","Service Detail");
		add_view($userid,"/nagiosxi/includes/components/xicore/status.php?show=hostgroups&hostgroup=all&style=overview","Hostgroup Overview");
		}
	}	
	
function add_default_dashboards($userid=0){

	// add some dashboards for the user if they don't have any
	$add=false;
	$dashboards=get_user_meta($userid,"dashboards");
	if($dashboards==null || $dashboards=="")
		$add=true;
	if($add==true){

		// home page dashboard
		$db=add_dashboard($userid,"Home Page",array(),HOMEPAGE_DASHBOARD_ID);
		// add some dashlets to the home dashboard (done later...)	
		
		// empty dashboard
		$db=add_dashboard($userid,"Empty Dashboard",array(),null);
		}
		
	// fix blank homepage dashboard
	init_home_dashboard_dashlets($userid);
	}
	
// add default dashlets to a blank home dashboard
function init_home_dashboard_dashlets($userid=0){

	$homedash=get_dashboard_by_id($userid,HOMEPAGE_DASHBOARD_ID);
	if($homedash==null)
		return;
		
	$dashcount=count($homedash["dashlets"]);
	if($dashcount==0){
		// getting started
		add_dashlet_to_dashboard($userid,HOMEPAGE_DASHBOARD_ID,"xicore_getting_started","Getting Started",array("height"=>365,"width"=>415,"top"=>60,"left"=>10,"pinned"=>0,"zindex"=>"1"),array());
	
		}
	}
?>
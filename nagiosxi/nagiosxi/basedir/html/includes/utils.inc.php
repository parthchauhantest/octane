<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// Development Started 03/22/2008
// $Id: utils.inc.php 1318 2012-08-15 21:38:02Z mguthrie $

$thedir=dirname(__FILE__);
//echo "UTILS.INC.PHP DIR:".$thedir."<BR>";
//echo "HI<BR>";

require_once($thedir.'/constants.inc.php');
require_once($thedir.'/errors.inc.php');
//echo "A<BR>";
require_once($thedir.'/utils-auditlog.inc.php');
require_once($thedir.'/utils-backend.inc.php');
//echo "B<BR>";
require_once($thedir.'/utils-ccm.inc.php');
require_once($thedir.'/utils-commands.inc.php');
//echo "C<BR>";
require_once($thedir.'/utils-components.inc.php');
require_once($thedir.'/utils-configwizards.inc.php');
require_once($thedir.'/utils-dashboards.inc.php');
require_once($thedir.'/utils-dashlets.inc.php');

require_once($thedir.'/utils-email.inc.php');
require_once($thedir.'/utils-events.inc.php');
require_once($thedir.'/utils-links.inc.php');
require_once($thedir.'/utils-graphtemplates.inc.php');
require_once($thedir.'/utils-menu.inc.php');
require_once($thedir.'/utils-mibs.inc.php');
require_once($thedir.'/utils-metrics.inc.php');
require_once($thedir.'/utils-nagioscore.inc.php');
require_once($thedir.'/utils-notifications.inc.php');
//echo "D<BR>";
require_once($thedir.'/utils-notificationmethods.inc.php');
//echo "E<BR>";
require_once($thedir.'/utils-objects.inc.php');
require_once($thedir.'/utils-perms.inc.php');
require_once($thedir.'/utils-reports.inc.php');
require_once($thedir.'/utils-status.inc.php');
require_once($thedir.'/utils-systat.inc.php');
require_once($thedir.'/utils-tables.inc.php');
require_once($thedir.'/utils-tools.inc.php');
require_once($thedir.'/utils-themes.inc.php');
require_once($thedir.'/utils-updatecheck.inc.php');
require_once($thedir.'/utils-users.inc.php');
require_once($thedir.'/utils-views.inc.php');
require_once($thedir.'/utils-wizards.inc.php');
require_once($thedir.'/utils-xmlauditlog.inc.php');
require_once($thedir.'/utils-xmlobjects.inc.php');
require_once($thedir.'/utils-xmlreports.inc.php');
require_once($thedir.'/utils-xmlstatus.inc.php');
require_once($thedir.'/utils-xmlsysstat.inc.php');
require_once($thedir.'/utils-xmlusers.inc.php');

require_once($thedir.'/utilsl.inc.php');
require_once($thedir.'/utilsx.inc.php');
//echo "F<BR>";


$request_vars_decoded=false;


////////////////////////////////////////////////////////////////////////
// SESSION FUNCTIONS
////////////////////////////////////////////////////////////////////////

// start session - require cookies
function init_session($lock=false){

	// we are running as a subsystem cron job
	if(defined("SUBSYSTEM")){
		$_SESSION["user_id"]=0;
		return;
		}
	
	session_name("nagiosxi");

	// require cookies
	ini_set("session.use_cookies","1"); 
	ini_set("session.use_only_cookies","1"); 
	ini_set("session.cookie_lifetime","0"); 
	$cookie_timeout=60*30; // in seconds
	$cookie_path="/";
	$garbage_timeout=$cookie_timeout+600; //in seconds
	session_set_cookie_params($cookie_timeout,$cookie_path);
	ini_set("session.gc_maxlifetime",$garbage_timeout);
		
	// start session
	if(!session_id())
		session_start();
		
	// CB suggestion for improving performance
	// XXX This is BAD.  Means nothing else can save session variables after this.  -MG
//	if($_REQUEST["page"]!="auth" && key($_REQUEST)!="logout" ){
//		session_write_close();
//		}		
	
	// adust cookie timeout to reset after page refresh
	if(isset($_COOKIE[session_name()]))
		setcookie(session_name(),$_COOKIE[session_name()],time()+$cookie_timeout,$cookie_path);

	// do callbacks
	$args=array();
	do_callbacks(CALLBACK_SESSION_STARTED,$args);
	
	if($lock)
		session_write_close(); 
	}

// clear session
function deinit_session(){

	// clear session variables
	$_SESSION=array();

	// delete the session cookie.
	if(isset($_COOKIE[session_name()]))
		setcookie(session_name(),'',time()-42000,'/');

	//  destroy the session.
	session_destroy();
	}

	
////////////////////////////////////////////////////////////////////////
// REQUEST FUNCTIONS
////////////////////////////////////////////////////////////////////////

$escape_request_vars=true;
$request_vars_decoded=false;

function map_htmlentities($arrval){

	if(is_array($arrval)){
		return array_map('map_htmlentities',$arrval);
		}
	else
		return htmlentities($arrval,ENT_QUOTES);
	}
function map_htmlentitydecode($arrval){

	if(is_array($arrval)){
		return array_map('map_htmlentitydecode',$arrval);
		}
	else
		return html_entity_decode($arrval,ENT_QUOTES);
	}


// grabs POST and GET variables
function grab_request_vars($preprocess=true,$type=""){
	global $escape_request_vars;
	global $request;
	
	// do we need to strip slashes?
	$strip=false;
	if((function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) || (ini_get('magic_quotes_sybase') && (strtolower(ini_get('magic_quotes_sybase'))!= "off")))
		$strip=true;
		
	$request=array();

	if($type=="" || $type=="get"){
		foreach ($_GET as $var => $val){
			if($escape_request_vars==true){
				if(is_array($val)){
					$request[$var]=array_map('map_htmlentities',$val);
					}
				else
					$request[$var]=htmlentities(strip_tags($val),ENT_QUOTES);
				}
			else
				$request[$var]=$val;
			//echo "GET: $var = \n";
			//print_r($val);
			//echo "<BR>";
			}
		}
	if($type=="" || $type=="post"){
		foreach ($_POST as $var => $val){
			if($escape_request_vars==true){
				if(is_array($val)){
					//echo "PROCESSING ARRAY $var<BR>";
					$request[$var]=array_map('map_htmlentities',$val);
					}
				else
					$request[$var]=htmlentities($val,ENT_QUOTES);
				}
			else
				$request[$var]=$val;
			//echo "POST: $var = ";
			//print_r($val);
			//echo "<BR>\n";
			//if(is_array($val)){
			//	echo "ARR=>";
			//	print_r($val);
			//	echo "<BR>";
			//	}
			}
		}
		
	// strip slashes - we escape them later in sql queries
	if($strip==true){
		foreach($request as $var => $val)
			$request[$var]=stripslashes($val);
		}
	
		
	if($preprocess==true)
		preprocess_request_vars();
	}

function grab_request_var($varname,$default=""){
	global $request;
	global $escape_request_vars;
	global $request_vars_decoded;
	
	$v=$default;
	if(isset($request[$varname])){
		if($escape_request_vars==true && $request_vars_decoded==false){
			if(is_array($request[$varname])){
				//echo "PROCESSING ARRAY [$varname] =><BR>";
				//print_r($request[$varname]);
				//echo "<BR>";
				$v=array_map('map_htmlentitydecode',$request[$varname]);
				}
			else
				$v=html_entity_decode($request[$varname],ENT_QUOTES);
			}
		else
			$v=$request[$varname];
		}
	//echo "VAR $varname = $v<BR>";
	return $v;
	}
	
function decode_request_vars(){
	global $request;
	global $request_vars_decoded;
	
	$newarr=array();
	foreach($request as $var => $val){
		$newarr[$var]=grab_request_var($var);
		}
		
	$request_vars_decoded=true;
		
	$request=$newarr;
	}

function preprocess_request_vars(){
	global $request;
	
	// set new language
	//if(isset($request['language']))
	//	set_language($request['language']);
	// set new theme
	//if(isset($request['theme']))
	//	set_theme($request['theme']);
	}
	
	
function get_pageopt($default=""){
	global $request;
	
	$popt="";
	$popt=grab_request_var("pageopt","");
	if($popt==""){
		if(count($request)>0){
			foreach($request as $var => $val){
				$popt=$var;
				break;
				}
			}
		else
			$popt=$default;
		}
	return $popt;
	}


function have_value($var){
	if($var==null)
		return false;
	if(!isset($var))
		return false;
	if(empty($var))
		return false;
	if(is_array($var))
		return true;
	if(!strcmp($var,""))
		return false;
	return true;
	}
	


////////////////////////////////////////////////////////////////////////
// LANGUAGE FUNCTIONS
////////////////////////////////////////////////////////////////////////

function set_language($language){
	// set session language
	$_SESSION["language"]=$language;
	}
	
function read_language_file($language){
	
	// make sure language file exists before switching
	$language_file="includes/lang/".$language.".inc.php";
	if(file_exists($language_file)){
		// include language file
//		include_once($language_file);
		include($language_file);
		return true;
		}
	return false;
	}

function init_language(){
	global $cfg;
	
	$result=true;

	
	// read language file (always read English first in case translators missed something)
	$default_language='en';
	$session_language=$default_language;
	$result=read_language_file($default_language);

	// read session language if available
	if(isset($_SESSION["language"]) && strcmp($_SESSION["language"],$default_language)){
		$session_language=$_SESSION["language"];
		$result=read_language_file($_SESSION["language"]);
		}
		
	// no session language yet - determine from defaults
	if(!isset($_SESSION["language"])){
	
		// try user-specific default language from DB
		$udblang=get_user_meta(0,"default_language");
		if(isset($udblang) && strcmp($udblang,$default_language)){
			$session_language=$udblang;
			$result=read_language_file($udblang);
			}
		// try global default language from DB
		$dblang=get_option("default_language");
		if(isset($dblang) && strcmp($dblang,$default_language)){
			$session_language=$dblang;
			$result=read_language_file($dblang);
			}

		// otherwise usedefault language from CFG
		else if(isset($cfg["default_language"]) && strcmp($cfg["default_language"],$default_language)){
			$session_language=$cfg["default_language"];
			$result=read_language_file($cfg["default_language"]);
			}
		}
		
	// if got language ok, set session language
	if($result==true){
		$_SESSION["language"]=$session_language;
		}
		
	return $result;
	}

function get_languages(){
	global $cfg;
	
	foreach($cfg['languages'] as &$lang){
		//$lang=htmlentities(utf8_encode($lang),ENT_QUOTES,'UTF-8',false);
		$lang=htmlentities(utf8_encode($lang),ENT_QUOTES,'UTF-8');
		}

	return $cfg['languages'];
	}


		
////////////////////////////////////////////////////////////////////////
// FORM FUNCTIONS
////////////////////////////////////////////////////////////////////////

function encode_form_val($rawval){
	return htmlentities($rawval);
	}

function yes_no($var){
	global $lstr;
	if(isset($var) && ($var==1 || $var==true))
		return $lstr['YesText'];
	return $lstr['NoText'];
	}

function is_selected($var1,$var2){
	if(is_string($var1) || is_string($var2)){
		if(!strcmp($var1,$var2))
			return "SELECTED";
		}
	else{
		if($var1==$var2)
			return "SELECTED";
		}
	return "";
	}

function is_checked($var1,$var2="on"){
	if($var1==$var2)
		return "CHECKED";
	else if(is_string($var1) && $var1=="on")
		return "CHECKED";
	else if(!strcmp($var1,$var2))
		return "CHECKED";
	else
		return "";
	}
	
function checkbox_binary($var1){
	if(is_numeric($var1)){
		if($var1==1)
			return 1;
		else
			return 0;
		}
	else if(is_string($var1) && $var1=="on")
		return 1;
	else
		return 0;
	}


////////////////////////////////////////////////////////////////////////
// MISC FUNCTIONS
////////////////////////////////////////////////////////////////////////

// gets value from array using default
function grab_array_var($arr,$varname,$default=""){
	global $request;
	
	$v=$default;
	if(is_array($arr)){
		if(array_key_exists($varname,$arr))
			$v=$arr[$varname];
		}
	return $v;
	}

// generates a random alpha-numeric string (password or backend ticket)
function random_string($len=6){
	$chars="023456789abcdefghijklmnopqrstuv";
	$rnd="";
	$charlen=strlen($chars);

	srand((double)microtime()*1000000);
	
	for($x=0;$x<$len;$x++){
		$num=rand()%$charlen;
		$ch=substr($chars,$num,1);
		$rnd.=$ch;
		}
		
	return $rnd;
	}
	

// see if NDOUtils tables exist
function ndoutils_exists(){
	if(!exec_named_sql_query('CheckNDOUtilsInstall',false))
		return false;
	return true;
	}
	
	
// see if installation is needed
function install_needed(){
	global $cfg;
	
	
	//return false;

	$db_version=get_db_version();
	if($db_version==null)
		return true;
		
	$installed_version=get_install_version();
	if($installed_version==null)
		return true;
		
	if(file_exists("/tmp/nagiosxi.forceinstall"))
		return true;
	
	return false;
	}
	
	
// see if upgrade is needed
function upgrade_needed(){
	global $cfg;
	
	$db_version=get_db_version();
	
	if(strcmp($db_version,$cfg['db_version']))
		return true;
	
	$installed_version=get_install_version();
	if($installed_version!=get_product_version())
		return true;

	return false;
	}
	
	
// get currently install db version
function get_db_version(){
	$db_version=get_option('db_version');
	return $db_version;
	}
	
function set_db_version($version=""){
	global $cfg;
	if($version=="")
		$dbv=$cfg['db_version'];
	else
		$dbv=$version;
	set_option('db_version',$dbv);
	}
	
// get currently installed version
function get_install_version(){
	$db_version=get_option('install_version');
	return $db_version;
	}
	
function set_install_version($version=""){
	global $cfg;
	if($version=="")
		$iv=get_product_version();
	else
		$iv=$version;
	set_option('install_version',$iv);
	}
		

////////////////////////////////////////////////////////////////////////
// URL FUNCTIONS
////////////////////////////////////////////////////////////////////////

// returns base URL used to access product
function get_base_url($usefullpath=true){
	return get_base_uri($usefullpath);
	}
	
// returns URL used to access XI from public networks
function get_external_url(){
	$url=get_option("external_url");
	if($url=="")
		$url=get_option("url");
	return $url;
	}

// returns base URI used to access product
function get_base_uri($usefullpath=true){
	global $cfg;
	
	$base_url=$cfg['base_url']."/";
	$url="";
	
	$cmdline=true;
	if(is_array($_SERVER) && array_key_exists("SERVER_NAME",$_SERVER))
		$cmdline=false;
	
	//if($usefullpath==true && $cmdline==false){
	if($usefullpath==true){  // removed cmdline statement 06/16/2011 EG
			if(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"]=="on")
				$proto="https";
			else
				$proto="http";
			if(isset($_SERVER["SERVER_PORT"]) && ($proto=="http" && $_SERVER["SERVER_PORT"]!="80") || ($proto=="https" && $_SERVER["SERVER_PORT"]!="443"))
				$port=":".$_SERVER["SERVER_PORT"];
			else
				$port="";

		$hostname="localhost"; // needed for command-line scripts
		if(isset($_SERVER['SERVER_NAME']))
			$hostname=$_SERVER['SERVER_NAME'];
		$url=$proto."://".$hostname.$port.$base_url;
		}
	else
		$url=$base_url;

	return $url;
	}

	
// returns URL to ajax helper
function get_ajax_helper_url(){

	// determine base url to access ajax helper
	$url=get_base_url(true);
	$url.=PAGEFILE_AJAXHELPER;

	return $url;
	}
	

// returns URL to ajax proxy
function get_ajax_proxy_url(){

	// determine base url to access ajax helper
	$url=get_base_url(true);
	$url.=PAGEFILE_AJAXPROXY;

	return $url;
	}
	

// returns URL to suggest
function get_suggest_url(){

	// determine base url to access ajax helper
	$url=get_base_url(true);
	$url.=PAGEFILE_SUGGEST;

	return $url;
	}
	
	
// returns URL to update check page
function get_update_check_url(){

	$url="http://www.nagios.com/checkforupdates/?product=".get_product_name(true)."&version=".get_product_version()."&build=".get_product_build();
	
	return $url;
	}
	
	
	
// returns URL used to access current page
function get_current_url($baseonly=false,$fulluri=false){
	if(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"]=="on")
		$proto="https";
	else
		$proto="http";
	if(($proto=="http" && $_SERVER["SERVER_PORT"]!="80") || ($proto=="https" && $_SERVER["SERVER_PORT"]!="443"))
		$port=":".$_SERVER["SERVER_PORT"];
	else
		$port="";
		
	if($fulluri==true){
		$uri="";
		$uri=$_SERVER["REQUEST_URI"];
		$url=$proto."://".$_SERVER['SERVER_NAME'].$port.$uri;
		}
	else{
		$page=$_SERVER['PHP_SELF'];
		if($baseonly==true && ($last_slash=strrpos($page,"/")))
			$page=substr($page,0,$last_slash+1);
		$url=$proto."://".$_SERVER['SERVER_NAME'].$port.$page;
		}
		
	return $url;
	}


// returns current page (used for online help and feedback submissions)
function get_current_page($baseonly=false){

	$page=$_SERVER['PHP_SELF'];
	
	if($last_slash=strrpos($page,"/")){
		$page_name=substr($page,$last_slash+1);
		}
	else{
		$page_name=$page;
		}
	
	// get rid of the 'backend/'
	if(defined("BACKEND") && BACKEND==true){
		}

	return $page_name;
	}
	
	
function build_url_from_current($args){
	global $request;

	//$url=$GLOBALS["HTTP_SERVER_VARS"]["REQUEST_URI"];
	$url=get_current_url();

	// possible override original request variables
	$r=$request;
	foreach($args as $var => $val){
		$r[$var]=$val;
		}

	// generate query string
	$url.="?";
	foreach($r as $var => $val){
		$url.="&".$var."=".$val;
		}

	return $url;
	}

	
function get_permalink_base(){
	global $request;

	$base="";
	
	if(!isset($request))
		grab_request_vars();

	// get current url
	$url=get_current_url(false,true);
	
	// parse url and remove permalink option from base
	$a=parse_url($url);

	// build base url
	$base=$a["scheme"]."://".$a["host"].$a["path"]."?";
	foreach($request as $var => $val){
		if($var=="xiwindow")
			continue;
		$base.="&".urlencode($var)."=";
		if(is_array($val))
			$base.=urlencode(serialize($val)); // doesn't work, but doesn't matter for now...
		else
			$base.=urlencode($val);
		}
		
	return $base;
	}
	

////////////////////////////////////////////////////////////////////////
// TIMING FUNCTIONS
////////////////////////////////////////////////////////////////////////

function get_timer(){
	$mtime = microtime();
	$mtime = explode(" ",$mtime);
	$mtime = $mtime[1] + $mtime[0];
	$starttime = $mtime; 
	return $starttime;
	}

function get_timer_diff($starttime,$endtime){
	$totaltime = ($endtime - $starttime);
	return number_format($totaltime,5);
	}
	
	





////////////////////////////////////////////////////////////////////////
// OPTION FUNCTIONS
////////////////////////////////////////////////////////////////////////

function get_option($name,$default=null){
	global $db_tables;

	$sql="SELECT * FROM ".$db_tables[DB_NAGIOSXI]["options"]." WHERE name='".escape_sql_param($name,DB_NAGIOSXI)."'";
	//echo "GET: $sql\n";
	if(($rs=exec_sql_query(DB_NAGIOSXI,$sql,false))){
		if($rs->MoveFirst()){
			return $rs->fields["value"];
			}
		}
	return $default;
	}


function set_option($name,$value){
	global $db_tables;

	// see if data exists already
	$key_exists=false;
	$sql="SELECT * FROM ".$db_tables[DB_NAGIOSXI]["options"]." WHERE name='".escape_sql_param($name,DB_NAGIOSXI)."'";
	if(($rs=exec_sql_query(DB_NAGIOSXI,$sql))){
		if($rs->RecordCount()>0)
			$key_exists=true;
		}

	// insert new key
	if($key_exists==false){
		$sql="INSERT INTO ".$db_tables[DB_NAGIOSXI]["options"]." (name,value) VALUES ('".escape_sql_param($name,DB_NAGIOSXI)."','".escape_sql_param($value,DB_NAGIOSXI)."')";
		//echo "NEW! $sql\n";
		return exec_sql_query(DB_NAGIOSXI,$sql);
		}

	// update existing key
	else{
		$sql="UPDATE ".$db_tables[DB_NAGIOSXI]["options"]." SET value='".escape_sql_param($value,DB_NAGIOSXI)."' WHERE name='".escape_sql_param($name,DB_NAGIOSXI)."'";
		return exec_sql_query(DB_NAGIOSXI,$sql);
		}
	}

	
function delete_option($name){
	global $db_tables;

	$sql="DELETE FROM ".$db_tables[DB_NAGIOSXI]["options"]." WHERE name='".escape_sql_param($name,DB_NAGIOSXI)."'";
	return exec_sql_query(DB_NAGIOSXI,$sql);
	}



////////////////////////////////////////////////////////////////////////
// MISC  FUNCTIONS
////////////////////////////////////////////////////////////////////////

function in_demo_mode(){
	global $cfg;
	
	if(isset($cfg['demo_mode']) && $cfg['demo_mode']==true)
		return true;
		
	return false;
	}


// returns attribute value of a simplexml object
function get_xml_attribute($obj,$att){
	foreach($obj->attributes() as $a => $b){
		if($a==$att)
			return $b;
		}
	return "";
	} 

	
function valid_ip($address){
	if(!have_value($address))
		return false;
	return true;
 	}
	
function valid_email($email){
	$email_array = explode("@", $email);
	if(count($email_array)!=2)
		return false;
	return true;
	}
	
	
function get_component_credential($component,$cname){
	global $cfg;

	$optname=$component."_".$cname;
	
	$optval=get_option($optname);
	if($optval==null || have_value($optval)==false){
		// default to config file value if we didn't find it in the database
		$optval=$cfg['component_info'][$component][$cname];
		set_option($optname,$optval);
		}
		
	return $optval;
	}
	
function set_component_credential($component,$cname,$val){
	$optname=$component."_".$cname;
	set_option($optname,$val);
	return true;
	}
	
function get_throbber_html(){
	$html="<img src='".theme_image("throbber.gif")."'>";
	return $html;
	}
	
	
////////////////////////////////////////////////////////////////////////
// DIRECTORY FUNCTIONS
////////////////////////////////////////////////////////////////////////

function get_current_dir(){
	global $argv;

	$cur_dir=realpath($argv[0]);

	return $cur_dir;
	}
	
function get_root_dir(){
	global $cfg;
	
	$root_dir="/usr/local/nagiosxi";

	if(array_key_exists("root_dir",$cfg))
		$root_dir=$cfg["root_dir"];
		
	return $root_dir;
	}
	
function get_base_dir(){
	global $cfg;

	$base_dir=get_root_dir()."/html";
	
	if(defined("BACKEND") && BACKEND==true)
		$base_dir=substr($base_dir,0,-8);
	
	return $base_dir;
	}
	
function get_tmp_dir(){
	$tmp_dir=get_root_dir()."/tmp";
	return $tmp_dir;
	}
	
function get_backend_dir(){

	/*
	if(defined("BACKEND") && BACKEND==true)
		$backend_dir=get_current_dir();
	else
		$backend_dir=get_base_dir()."/backend";
	*/
	
	$backend_dir=get_base_dir()."/backend";
		
	return $backend_dir;
	}

	
function get_subsystem_ticket(){
	$ticket=get_option("subsystem_ticket");
	if($ticket==null || have_value($ticket)==false){
		$ticket=random_string(8);
		set_option("subsystem_ticket",$ticket);
		}
	return $ticket;
	}
	
	

////////////////////////////////////////////////////////////////////////
// XML DB FUNCTIONS
////////////////////////////////////////////////////////////////////////

function get_xml_db_field($level, $rs, $fieldname, $nodename=""){
	if($nodename=="")
		$nodename=$fieldname;
	return get_xml_field($level,$nodename,get_xml_db_field_val($rs,$fieldname));
	}

function get_xml_db_field_val($rs, $fieldname){
	if(isset($rs->fields[$fieldname]))
		return xmlentities($rs->fields[$fieldname]);
	else
		return "";
	}
	
function get_xml_field($level, $nodename, $nodevalue){
	$output="";
	for($x=0;$x<$level;$x++)
		$output.="  ";
	$output.="<".$nodename.">".xmlentities($nodevalue)."</".$nodename.">\n";
	return $output;
	}
	
	

////////////////////////////////////////////////////////////////////////
// MISSING FEATURE FUNCTIONS :-)
////////////////////////////////////////////////////////////////////////
	
function do_missing_feature_page($fullhtml=true){
	global $lstr;
	
	if($fullhtml==true){
?>
<html>
<head>
	<title><?php echo $lstr['MissingFeaturePageTitle'];?></title>
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<?php do_page_head_links();?>
</head>
<body>
<?php
		}
?>
<h1><?php echo $lstr['MissingFeaturePageHeader'];?></h1>
<p>
<?php echo $lstr['MissingFeatureText'];?>
</p>

<?php
	if($fullhtml==true){
?>
</body>
</html>
<?php
		}
	}
	

////////////////////////////////////////////////////////////////////////
// META DATA FUNCTIONS
////////////////////////////////////////////////////////////////////////

function get_meta($type_id,$obj_id,$key){
	global $db_tables;

	$sql="SELECT * FROM ".$db_tables[DB_NAGIOSXI]["meta"]." WHERE metatype_id='".escape_sql_param($type_id,DB_NAGIOSXI)."' AND metaobj_id='".escape_sql_param($obj_id,DB_NAGIOSXI)."' AND keyname='".escape_sql_param($key,DB_NAGIOSXI)."'";
	if(($rs=exec_sql_query(DB_NAGIOSXI,$sql))){
		if($rs->MoveFirst()){
			return $rs->fields["keyvalue"];
			}
		}
	return null;
	}
	

function set_meta($type_id,$obj_id,$key,$value){
	global $db_tables;
	
	// see if data exists already
	$key_exists=false;
	if(get_meta($type_id,$obj_id,$key)!=null)
		$key_exists=true;

	// insert new key
	if($key_exists==false){
		$sql="INSERT INTO ".$db_tables[DB_NAGIOSXI]["meta"]." (metatype_id,metaobj_id,keyname,keyvalue) VALUES ('".escape_sql_param($type_id,DB_NAGIOSXI)."','".escape_sql_param($obj_id,DB_NAGIOSXI)."','".escape_sql_param($key,DB_NAGIOSXI)."','".escape_sql_param($value,DB_NAGIOSXI)."')";
		return exec_sql_query(DB_NAGIOSXI,$sql);
		}

	// update existing key
	else{
		$sql="UPDATE ".$db_tables[DB_NAGIOSXI]["meta"]." SET keyvalue='".escape_sql_param($value,DB_NAGIOSXI)."' WHERE metatype_id='".escape_sql_param($type_id,DB_NAGIOSXI)."' AND metaobj_id='".escape_sql_param($obj_id,DB_NAGIOSXI)."' AND keyname='".escape_sql_param($key,DB_NAGIOSXI)."'";
		return exec_sql_query(DB_NAGIOSXI,$sql);
		}
		
	}

	
function delete_meta($type_id,$obj_id,$key){
	global $db_tables;

	$sql="DELETE FROM ".$db_tables[DB_NAGIOSXI]["meta"]." WHERE metatype_id='".escape_sql_param($type_id,DB_NAGIOSXI)."' AND metaobj_id='".escape_sql_param($obj_id,DB_NAGIOSXI)."' AND keyname='".escape_sql_param($key,DB_NAGIOSXI)."'";
	return exec_sql_query(DB_NAGIOSXI,$sql);
	}


////////////////////////////////////////////////////////////////////////
// MISC FUNCTIONS
////////////////////////////////////////////////////////////////////////

// used to generate alert/info message boxes used in form pages...	
function get_message_text($error=true,$info=true,$msg=""){
	$output="";
	
	if(have_value($msg)){
		if($error==true)
			$divclass="errorMessage";
		else if($info==true)
			$divclass="infoMessage";
		else
			$divclass="actionMessage";

		$output.='
		<div class="message">
		<ul class="'.$divclass.'">
		';
		
		if(is_array($msg)){
			foreach($msg as $m)
				$output.="<li>".$m."</li>";
			}
		else
			$output.="<li>".$msg."</li>";
			
		$output.='
		</ul>
		</div>
		';
		}	
		
	return $output;
	}
	
// used for debugging and viewing arrays in the web browser
function array_dump($array) {
	print "<pre>".print_r($array,true)."</pre>"; 
}
	
?>
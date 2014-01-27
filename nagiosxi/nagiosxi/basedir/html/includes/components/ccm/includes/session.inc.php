<?php //session handler for CCM

//define environment
if(file_exists('/usr/local/nagiosxi/html/config.inc.php'))
	define('ENVIRONMENT','nagiosxi');
else
	define('ENVIRONMENT','nagioscore'); 

if(ENVIRONMENT=='nagiosxi')
    require_once(dirname(__FILE__).'/../../../common.inc.php');
//version #
define('CCMVERSION','1.03');
define('VERSION',103); //used for fresh JS and CSS files 

//constants 
define('INCDIR',BASEDIR.'includes/'); 
define('TPLDIR',BASEDIR.'page_templates/'); 
define('CLASSDIR',BASEDIR.'classes/'); 

// AUDIT LOG TYPES
if(!defined('AUDITLOGTYPE_NONE') || ENVIRONMENT != 'nagiosxi') {
	define("AUDITLOGTYPE_NONE",0);
	define("AUDITLOGTYPE_ADD",1); // adding objects /users
	define("AUDITLOGTYPE_DELETE",2); // deleting objects / users
	define("AUDITLOGTYPE_MODIFY",4); // modifying objects / users
	define("AUDITLOGTYPE_MODIFICATION",4); // modifying objects / users
	define("AUDITLOGTYPE_CHANGE",8); // changes (reconfiguring system settings)
	define("AUDITLOGTYPE_SYSTEMCHANGE",8); // changes (reconfiguring system settings)
	define("AUDITLOGTYPE_SECURITY",16);  // security-related events
	define("AUDITLOGTYPE_INFO",32); // informational messages
	define("AUDITLOGTYPE_OTHER",64); // everything else
}

//main includes 
require_once(BASEDIR.'config.inc.php'); 
require_once(INCDIR.'common_functions.inc.php'); 
require_once(INCDIR.'auth.inc.php'); 
require_once(INCDIR.'hidden_overlay_functions.inc.php'); 

//////////include class definitions////////////// 
//nagiosql classes
require_once(CLASSDIR.'config_class.php'); 
require_once(CLASSDIR.'data_class.php'); 
require_once(CLASSDIR.'mysql_class.php'); 
require_once(CLASSDIR.'nag_class.php'); 
require_once(CLASSDIR.'import_class.php'); 
//new CCM classes
require_once(CLASSDIR.'Db.php');
require_once(CLASSDIR.'CCM_Menu.php'); 
require_once(CLASSDIR.'Form_class.php');
	
//pear template class //XXX TODO: eventually phase this out so we don't need it anymore 
require_once($CFG['pear_include']); 

//CCM main includes 
require_once(TPLDIR.'ccm_table.php');
require_once(INCDIR.'page_router.inc.php');

//result limits 
define('DEFAULT_PAGELIMIT',$CFG['default_pagelimit']); 
$_SESSION['default_limit'] = DEFAULT_PAGELIMIT;

//global classes 
$ccmDB = new Db();
$Menu = new Main_Menu(); 

//load config settings 
$CFG['settings'] = array(); 
$settings = $ccmDB->query("SELECT * FROM tbl_settings;"); 
foreach($settings as $s) 
	$CFG[$s['category']][$s['name']] = $s['value']; 

// Add data to the session
// ===============
$_SESSION['SETS'] = $CFG;
$_SESSION['domain'] =1; //currently we only support single domain configs 
$_SESSION['pagelimit'] = $CFG['common']['pagelines']; 

//process $_POST an $_GET variables 
$escape_request_vars=true; 
ccm_grab_request_vars(); 

//show the menu? 
$see_menu = ccm_grab_request_var('menu',false);
if($see_menu) $_SESSION['menu']=$see_menu; 
if(!isset($_SESSION['menu'])) $_SESSION['menu']='visible';  

//always enable menu in nagioscore
if (ENVIRONMENT == 'nagioscore')
        $_SESSION['menu']='visible';


//initialize base classes
//==================================
// Initialize classes 
// ======================
$myVisClass    = new nagvisual;
$myDataClass   = new nagdata;
$myConfigClass = new nagconfig;
$myDBClass		= new mysqldb; 
$myImportClass = new nagimport;
//
// Classes reference each other 
// ===============================
$myVisClass->myDBClass    =& $myDBClass;
$myVisClass->myDataClass  =& $myDataClass;
$myVisClass->myConfigClass  =& $myConfigClass;
$myDataClass->myDBClass   =& $myDBClass;
$myDataClass->myVisClass  =& $myVisClass;
$myDataClass->myConfigClass =& $myConfigClass;
$myConfigClass->myDBClass =& $myDBClass;
$myConfigClass->myVisClass  =& $myVisClass;
$myConfigClass->myDataClass =& $myDataClass;	
$myImportClass->myDataClass   =& $myDataClass;
$myImportClass->myDBClass   =& $myDBClass;
$myImportClass->myConfigClass =& $myConfigClass;


//////////////authorization///////////////////
$AUTH = check_auth(); 

//global unique element ID used as a counter 
$unique = 100; 

//session_write_close(); 

///////////LANGUAGE//////////////////
if(ENVIRONMENT=='nagiosxi')
	ccm_init_language(); 
	
//////////DEBUG MODE ////////////////
$debug = ccm_grab_request_var('debug',false);
if($debug=='enable')
	$_SESSION['debug']=true;
if($debug=='verbose')
	$_SESSION['debug']='verbose';
if($debug=='disable')
	unset($_SESSION['debug']);


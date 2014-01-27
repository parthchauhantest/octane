<?php //auth.inc.php


/** function check_auth()
*	handles and verifies user authorization for all pages, also syncs login with nagiosql  
*	@global array $_SESSION sets all login related session variables 
*	@global object $myDBClass nagiosql object 
*	@global object $myDataClass nagiosql object 
*	@return bool $AUTH global variable if auth is good or not 
*/
function check_auth()
{

	global $myDBClass;
	global $myDataClass; 

	//grab any submitted login variables 
	$username = mysql_real_escape_string(ccm_grab_request_var('username','')); 
	$password = mysql_real_escape_string(ccm_grab_request_var('password',''));
	$loginID = ccm_grab_request_var('loginid',''); 
	$loginSubmitted = ccm_grab_request_var('loginSubmitted',false); 
	 	 
	//first check any existing login 
	if (isset($_SESSION['ccm_username']) && isset($_SESSION['ccm_login']) && $_SESSION['ccm_login']==true) {
		//echo "we are logged in, all is well"; 
		$_SESSION['loginMessage'] = gettext('Logged in as: ').$_SESSION['ccm_username']." <a href='index.php?cmd=logout'>".gettext('Logout')."</a>";
        $_SESSION['loginStatus'] = true;
		return true; 
	}
	
	//check if legacy CCM is already logged in		
	if(isset($_SESSION['username']) && isset($_SESSION['startsite']) && $_SESSION['startsite'] =='/nagiosql/admin.php') { 	
		//echo "logged in through nagiosql"; 
		$_SESSION['ccm_username'] = $_SESSION['username']; 
		$_SESSION['loginMessage'] = gettext('Logged in as: ').$_SESSION['ccm_username']." <a href='index.php?cmd=logout'>".gettext('Logout')."</a>";
		if(!isset($_SESSION['token'])) $_SESSION['token'] = md5(uniqid(mt_rand(),true));
        $_SESSION['loginStatus'] = true;
		return true; 	 
	} 
	
	//if login form was just submitted 
	elseif($loginSubmitted) {
		//echo "LOGIN SUBMITTED<br />"; 		
	  $strSQL    = "SELECT * FROM `tbl_user` WHERE `username`='".$username."' AND `password`='".md5($password)."' AND `active`='1'";
	  $booReturn = $myDBClass->getDataArray($strSQL,$arrDataUser,$intDataCount);
	  if ($booReturn == false)  {
	    if (!isset($strMessage)) 
	    	  $strMessage = ""; 
	    $_SESSION['loginMessage']= gettext('Error while selecting data from database:')."<br />".$myDBClass->strDBError."<br />";
        $_SESSION['loginStatus'] = false;
	  } 
	  else if ($intDataCount == 1) {//single user returned 	  		
	    // Set session variables 
	    $_SESSION['ccm_username']  = $username;
	    $_SESSION['ccm_login']=true;
	    $_SESSION['timestamp'] = mktime();
	    $_SESSION['token'] = md5(uniqid(mt_rand(),true));
	    $_SESSION['loginMessage'] = gettext('Logged in as').': '.$username." <a href='index.php?cmd=logout'>".gettext('Logout')."</a>";

		 //nagiosql overrides
		 $_SESSION['startsite'] = '/nagiosql/admin.php';
		 $_SESSION['username'] = $username; 
		 $_SESSION['keystring'] = '11111111'; 
		 $_SESSION['strLoginMessage'] = '';
         $_SESSION['loginStatus'] = true;

	    // Last login time to date
	    $strSQLUpdate = "UPDATE `tbl_user` SET `last_login`=NOW() WHERE `username`='".$username."'";
	    $booReturn    = $myDBClass->insertData($strSQLUpdate);
	    $myDataClass->writeLog(gettext('Login successful'));
		audit_log(AUDITLOGTYPE_SECURITY,$username." successfully logged into Nagios CCM");
		
		 return true; 
	  } 
	  else {
	    $_SESSION['loginMessage'] = gettext('Contact your Nagios XI administrator if you have forgotten your login credentials.<br />
Need to initialize or reset the config manager admin password? <a target="_blank" href="/nagiosxi/admin/?xiwindow=credentials.php">Click here</a>.');
        $_SESSION['loginStatus'] = false;
	    $myDataClass->writeLog(gettext('Login failed!')." - Username: ".$username);
		audit_log(AUDITLOGTYPE_SECURITY,"CCM Login failed - Username: {$username}");
	    return false; 
	  }
	} 
	//username is not set 
	else {
		//echo "NOTHING SET!"; 
		$_SESSION['loginMessage'] = "Login Required!";
        $_SESSION['loginStatus'] = true;
		//print_r($_SESSION); 
		//dump_request(); 
		 return false; 
	}
	
}	//end check_auth function 


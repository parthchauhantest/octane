<?php
// BACKEND AUTHENTICATION FUNCTIONS
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: auth.inc.php 75 2010-04-01 19:40:08Z egalstad $


// bail out if user is not authenticated
function check_backend_authentication(){
	if(is_backend_authenticated()==false){
		echo "ERROR: NOT AUTHENTICATED";
		exit();
		}
//	echo "AUTH OK - UID:".$_SESSION["user_id"];
//	exit();
	}

// checks if user is authenticated
// MOVED TO utils-backend.inc.php  11/19/09
//function is_backend_authenticated()


?>
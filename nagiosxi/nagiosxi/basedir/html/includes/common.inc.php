<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: common.inc.php 75 2010-04-01 19:40:08Z egalstad $

//echo "COMMON.INC.PHP DIR:".dirname(__FILE__)."<BR>";

// common includes
require_once(dirname(__FILE__).'/../config.inc.php');
require_once(dirname(__FILE__).'/constants.inc.php');

require_once(dirname(__FILE__).'/db.inc.php');

require_once(dirname(__FILE__).'/utils.inc.php');

require_once(dirname(__FILE__).'/auth.inc.php');
//require_once(dirname(__FILE__).'/utils.inc.php');
require_once(dirname(__FILE__).'/pageparts.inc.php');
require_once(dirname(__FILE__).'/dashlets.inc.php');
require_once(dirname(__FILE__).'/notificationmethods.inc.php');

require_once(dirname(__FILE__).'/components.inc.php');  // include these last!!

require_once(dirname(__FILE__).'/lang/en.inc.php');
?>
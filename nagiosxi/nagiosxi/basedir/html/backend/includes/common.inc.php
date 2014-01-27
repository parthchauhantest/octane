<?php
// COMMON BACKEND INCLUDES
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: common.inc.php 75 2010-04-01 19:40:08Z egalstad $

// backend defines - THIS COMES FIRST!
require_once(dirname(__FILE__).'/constants.inc.php');

// backend-specific routines
require_once(dirname(__FILE__).'/auth.inc.php');
require_once(dirname(__FILE__).'/errors.inc.php');
require_once(dirname(__FILE__).'/utils.inc.php');
require_once(dirname(__FILE__).'/handlers.inc.php');

// use frontend logic for most stuff
require_once(dirname(__FILE__).'/../../includes/common.inc.php');
require_once(dirname(__FILE__).'/../../includes/utils.inc.php');
//require_once(dirname(__FILE__).'/../../includes/components.inc.php');

?>
<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: showlog.php 75 2010-04-01 19:40:08Z egalstad $

require_once(dirname(__FILE__).'/../coreuiproxy.inc.php');
coreui_do_proxy("showlog.cgi");
?>
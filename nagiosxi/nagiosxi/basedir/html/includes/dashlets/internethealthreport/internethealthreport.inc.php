<?php
// HTML EXAMPLE DASHLET
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: internethealthreport.inc.php 75 2010-04-01 19:40:08Z egalstad $

include_once(dirname(__FILE__).'/../dashlethelper.inc.php');

// run the initialization function
internethealthreport_dashlet_init();

function internethealthreport_dashlet_init(){
	
	$name="internethealthreport";
	
	$args=array(

		// need a name
		DASHLET_NAME => $name,
		
		// informative information
		DASHLET_VERSION => "1.0",
		DASHLET_DATE => "09-26-2009",
		DASHLET_AUTHOR => "Nagios Enterprises, LLC",
		DASHLET_DESCRIPTION => "Keynote Internet Health Report delivers up-to-the-minute metrics on overall Internet performance, monitoring availability and latency between major Tier One backbones.",
		DASHLET_COPYRIGHT => "Dashlet Copyright &copy; 2009 Nagios Enterprises.  Data Copyright &copy; 2009 Keynote Systems, Inc.",
		DASHLET_LICENSE => "MIT",
		DASHLET_HOMEPAGE => "http://www.nagios.com",
		
		DASHLET_URL => "http://www.internetpulse.net/",
		DASHLET_PREVIEW_IMAGE => get_dashlet_url_base("internethealthreport")."/preview.png",
		
		DASHLET_TITLE => "Internet Health Report from Keynote Systems",
		
		DASHLET_OUTBOARD_CLASS => "internethealthreport_outboardclass",
		DASHLET_INBOARD_CLASS => "internethealthreport_inboardclass",
		DASHLET_PREVIEW_CLASS => "internethealthreport_previewclass",
		
		DASHLET_CSS_FILE => "internethealthreport.css",
		//DASHLET_JS_FILE => "internethealthreport.js",

		DASHLET_WIDTH => "800",
		DASHLET_HEIGHT => "200",
		DASHLET_OPACITY => "0.7",
		DASHLET_BACKGROUND => "",
		);
		
	register_dashlet($name,$args);
	}
	

?>
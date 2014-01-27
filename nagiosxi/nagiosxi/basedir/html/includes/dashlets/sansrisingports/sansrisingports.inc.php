<?php
// HELLO WORLD EXAMPLE DASHLET
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: sansrisingports.inc.php 75 2010-04-01 19:40:08Z egalstad $

include_once(dirname(__FILE__).'/../dashlethelper.inc.php');

// run the initialization function
sansrisingports_dashlet_init();

function sansrisingports_dashlet_init(){
	
	// respect the name!
	$name="sansrisingports";
	
	$args=array(

		// need a name
		DASHLET_NAME => $name,
		
		// informative information
		DASHLET_VERSION => "1.0",
		DASHLET_DATE => "09-26-2009",
		DASHLET_AUTHOR => "Nagios Enterprises, LLC",
		DASHLET_DESCRIPTION => "A graph of the top 10 rising ports from the SAN Internet Storm Center.  Useful for spotting trends related to virus and worm outbreaks.",
		DASHLET_COPYRIGHT => "Dashlet Copyright &copy; 2009 Nagios Enterprises.  Data Copyright &copy; SANS Internet Storm Center.",
		DASHLET_LICENSE => "Creative Commons Attribution-Noncommercial 3.0 United States License. ",
		DASHLET_HOMEPAGE => "http://www.nagios.com",
		
		// the good stuff - only one output method is used.  order of preference is 1) function, 2) url
		DASHLET_FUNCTION => "sansrisingports_dashlet_func",
		//DASHLET_URL => get_dashlet_url_base($name)."/$name.php",
		
		DASHLET_TITLE => "SANS Internet Storm Center Top 10 Rising Ports",
		
		DASHLET_OUTBOARD_CLASS => "sansrisingports_outboardclass",
		DASHLET_INBOARD_CLASS => "sansrisingports_inboardclass",
		DASHLET_PREVIEW_CLASS => "sansrisingports_previewclass",
		
		DASHLET_CSS_FILE => "sansrisingports.css",
		//DASHLET_JS_FILE => "sansrisingports.js",

		DASHLET_WIDTH => "300px",
		DASHLET_HEIGHT => "212px",
		DASHLET_OPACITY => "0.8",
		DASHLET_BACKGROUND => "",

//		DASHLET_REFRESHRATE => -1,
		);
	register_dashlet($name,$args);
	}
	
function sansrisingports_dashlet_func($mode=DASHLET_MODE_PREVIEW,$id="",$args=null){

	$output="";
	$imgbase=get_dashlet_url_base("sansrisingports")."/images/";

	switch($mode){
		case DASHLET_MODE_GETCONFIGHTML:
			break;
		case DASHLET_MODE_OUTBOARD:
		case DASHLET_MODE_INBOARD:
		
			
			$graphurl="http://isc.sans.org/trendgraph.png";
			$trendsurl="http://isc.sans.org/trends.html";

			$width="90%";
			$height="90%";
			
			$output="<div id='sansrisingports-container-".$id."' style='width: ".$width."; height: ".$height.";' ><div style='display: block;'><a href='".$trendsurl."' target='_blank' title='Go To SANS Internet Storm Center Trends'><img src='".$graphurl."' width='100%'></a></div></div>";
			break;
		case DASHLET_MODE_PREVIEW:
			$output="<p><img src='".$imgbase."preview.png'></p>";
			break;
		}
		
	return $output;
	}

?>
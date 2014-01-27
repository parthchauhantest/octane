<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// Development Started 03/22/2008
// $Id: utils-themes.inc.php 75 2010-04-01 19:40:08Z egalstad $

//require_once(dirname(__FILE__).'/common.inc.php');

	
////////////////////////////////////////////////////////////////////////
// THEME FUNCTIONS
////////////////////////////////////////////////////////////////////////

function set_theme($theme){
	
	// set session theme
	$_SESSION["theme"]=$theme;
	}
	
function init_theme(){
	global $cfg;
	
	$theme='default';
	// set session theme if its not already
	if(!isset($_SESSION["theme"])){
	
		// try default language from DB
		$dbtheme=get_option("default_theme");
		if(isset($dbtheme) && have_value($dbtheme)){
			$theme=$dbtheme;
			}
		// else use default from CFG
		else if(isset($cfg["default_theme"])){
			$theme=$cfg["default_theme"];
			}
			
		// set session theme
		$_SESSION["theme"]=$theme;
		}
	}

function get_themes(){

	$themes=array();

	$base_dir=get_base_dir();
	$theme_dir=$base_dir."/includes/themes/";
	$files=scandir($theme_dir);
	foreach($files as $f){
		if($f[0]=='.')
			continue;
		$themes[]=$f;
		}

	$themes[]="none";

	return $themes;
	}
	
/*
function theme_image($img=""){
	$url=get_base_url()."/includes/themes/".$_SESSION["theme"]."/images/".$img;
	return $url;
	}
*/

// just a placeholder for now...
function theme_image($img=""){
	$url=get_base_url();
	$url.="images/";
	$url.=$img;
	return $url;
	}

		

?>
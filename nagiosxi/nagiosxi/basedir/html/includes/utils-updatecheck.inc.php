<?php
//
// Copyright (c) 2008-2010 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: utils-updatecheck.inc.php 833 2011-10-17 15:09:06Z mguthrie $

////////////////////////////////////////////////////////////////////////
// UPDATE  FUNCTIONS
////////////////////////////////////////////////////////////////////////

function do_update_check($forced=false,$firstcheck=false){
	global $cfg;
	
	$now=time();
	
	// force check if we've never checked
	$last_update_check_time=get_option("last_update_check_time");
	if($last_update_check_time=="")
		$forced=true;
	
	// force check if last check didn't succeed
	$last_success=get_option("last_update_check_succeeded");
	if($last_success==0)
		$forced=true;

	// if not forced, see if we should check for updates yet
	if($forced==false){
	
		// we're not supposed to automatically check for updates
		$auto_check_updates=get_option('auto_update_check');
		if($auto_check_updates==false){
			//echo "NO AUTO CHECK<BR>\n";
			return false;
			}
			
		// we haven't waited long enough
		$last_check=get_option("last_update_check_time");
		if($last_check){
			// at least 24 hours should have passed since last auto check
			$timediff=$now-$last_check;
			if($timediff<(60*60*24)){
				//echo "TOO SOON<BR>\n";
				return false;
				}
			}
		}
		
	// save last check time
	set_option("last_update_check_time",$now);
	
	
	// build url
	if(array_key_exists("update_check_url",$cfg))
		$theurl=$cfg['update_check_url'];
	else
		$theurl="http://api.nagios.com/versioncheck/";
		
	// options to send
	$theurl.="?product=".get_product_name(true)."&version=".get_product_version()."&build=".get_product_build()."&stableonly=1&output=xml";
	if($firstcheck==true)
		$theurl.="&firstcheck=1";
	
	$options = array(
		'return_info'	=> true,
		'method'	=> 'post',
		'timeout'	=> 15
		);

	//echo "URL: $theurl\n";
	$proxy=false; 
	
	if(have_value(get_option('use_proxy')) )
		$proxy = true; 
	
	// fetch the url
	$result=load_url($theurl,$options,$proxy);
	$body=$result["body"];
	
	$xres=simplexml_load_string($body);
	// an error occurred
	if($xres==false){
		set_option("last_update_check_succeeded",0);
		return false;
		}
	
	$update_available=0;
	$update_version="";
	$release_date="";
	$release_notes="";

	/*
	$x=$xres->xpath("/versioninfo");
	print_r($x);
	if($x!=false){
		$update_available=$x->update_available[0];
		}
	print_r($update_available);
	
	$x=$xres->xpath("/versioninfo/update_version");
	if($x!=false){
		$update_version=$x[0]->version;
		$release_date=$x[0]->release_date;
		$release_notes=$x[0]->release_notes;
		}
	*/
	$update_available=$xres->update_available;
	$update_version=$xres->update_version->version;
	$release_date=$xres->update_version->release_date;
	$release_notes=$xres->update_version->release_notes;

	/*
	echo "AVAIL: ".$update_available."<BR>\n";
	echo "VERSION: ".$update_version."<BR>\n";
	echo "DATE: ".$release_date."<BR>\n";
	echo "NOTES: ".$release_notes."<BR>\n";
	*/

	// save this information
	set_option("update_available",$update_available);
	set_option("update_version",$update_version);
	set_option("update_release_date",$release_date);
	set_option("update_release_notes",$release_notes);

	// success!
	set_option("last_update_check_succeeded",1);
	
	return true;
	}
	

?>
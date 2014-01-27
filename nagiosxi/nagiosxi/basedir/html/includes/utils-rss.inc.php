<?php //utils.rss.inc.php 
//
// Copyright (c) 2008-20012 Nagios Enterprises, LLC.  All rights reserved.
//
// Development Started 08/22/2012
// 

function xi_fetch_rss($url) {
	global $cfg; 

	$base = grab_array_var($cfg,'root_dir','/usr/local/nagiosxi');
	$tmp=$base.'/tmp/'; 
	$xmlcache = $tmp.str_replace('/','_',$url).'.xml'; 
	
	$xml = false; 
	//check for cache, or update cache if it's older than 7 days
	if(file_exists($xmlcache) && filemtime($xmlcache) > (time() - (60*60*24*7) )) {
		//use cache
		$xml = @simplexml_load_file($xmlcache); 
		if($xml)
			return $xml->channel->item; 
			
	}
	else { //fetch live rss feed and cache it
		$xml = fetch_live_rss_and_cache($url,$xmlcache); 
		if($xml)
			return $xml->channel->item; 		
	}
	//false on failure 
	return false; 
}


function fetch_live_rss_and_cache($url,$xmlcache) {
	//use proxy component?
	$proxy=false; 
	if(have_value(get_option('use_proxy')) )
		$proxy = true; 
	
	$options = array(
		'return_info'	=> true,
		'method'	=> 'get',
		'timeout'	=> 10
		);

	// fetch the url
	$result=load_url($url,$options,$proxy);
	$body=trim($result["body"]);
	
	//cache contents 
	file_put_contents($xmlcache,$body); 
	
	$xml=simplexml_load_string($body);
	
	return $xml; 
}


?>
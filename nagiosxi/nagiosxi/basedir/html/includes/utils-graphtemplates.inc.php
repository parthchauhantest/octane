<?php
//
// Copyright (c) 2011 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: utils-graphtemplates.inc.php 321 2010-09-27 16:34:01Z egalstad $

//require_once(dirname(__FILE__).'/common.inc.php');




///////////////////////////////////////////////////////////////////////////////////////////
// MIB FUNCTIONS
///////////////////////////////////////////////////////////////////////////////////////////

function get_graph_template_dir(){
	return "/usr/local/nagios/share/pnp";
	}

function get_graph_templates(){
	global $cfg;
	
	$mibs=array();
	
	$basedir=get_graph_template_dir();
	
	$dirs=array(
		$basedir."/templates",
		$basedir."/templates.dist",
		);
		
	foreach($dirs as $dir){
	
		$p=$dir;
		$direntries=file_list($p,"");
		foreach($direntries as $de){

			$file=$de;
			$filepath=$dir."/".$file;
			$ts=filemtime($filepath);
			
			$perms=fileperms($filepath);
			$perm_string=file_perms_to_string($perms);
			
			$ownerarr=fileowner($filepath);
			if(function_exists('posix_getpwuid')){
				$ownerarr=posix_getpwuid($ownerarr);
				$owner=$ownerarr["name"];
				}
			else
				$owner=$ownerarr;
			$grouparr=filegroup($filepath);
			if(function_exists('posix_getgrgid')){
				$grouparr=posix_getgrgid($grouparr);
				$group=$grouparr["name"];
				}
			else
				$group=$grouparr;
				
			$dir_name=basename($dir);
			
			
			$templates[]=array(
				"dir" => $dir_name,
				"file" => $file,
				"timestamp" => $ts,
				"date" => get_datetime_string($ts),
				"perms" => $perms,
				"permstring" => $perm_string,
				"owner" => $owner,
				"group" => $group,
				);
			}
		}
	
	return $templates;
	}
	
		
?>
<?php

// an array of friendy data source name (by template) used in performance graphs
$lstr['PerfGraphDatasourceNames']=array(

	"defaults" => array(  // defaults are used if a specific template name cannot be found
		"time" => "Time",
		"size" => "Size",
		"pl" => "Packet Loss",
		"rta" => "Round Trip Average",
		"load1" => "1 Minute Load",
		"load5" => "5 Minute Load",
		"load15" => "15 Minute Load",
		"users" => "Users",
		),
		
	// specific template names
	"check_ping" => array(
		"rta" => "Round Trip Average",
		"pl" => "Packet Loss",
		),
	"check_http" => array(
		"time" => "Response Time",
		"size" => "Page Size",
		"ds1" => "Response Time",
		"ds2" => "Page Size",
		),
	"check_dns" => array(
		"time" => "Response Time",
		),
		
	// custom template names
	"check_local_load" => array(
		"ds1" => "CPU Load",
		),
		
	);
	
?>
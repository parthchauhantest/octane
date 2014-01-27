<?php
// Perfdata Sub-Component Functions
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: perfdata.inc.php 1300 2012-07-17 19:47:23Z swilkerson $

include_once(dirname(__FILE__).'/../componenthelper.inc.php');



// run the initialization function
perfdata_component_init();

////////////////////////////////////////////////////////////////////////
// COMPONENT INIT FUNCTIONS
////////////////////////////////////////////////////////////////////////

function perfdata_component_init(){

	$name="perfdata";
	
	$args=array(

		// need a name
		COMPONENT_NAME => $name,
		
		// informative information
		//COMPONENT_VERSION => "1.1",
		//COMPONENT_DATE => "11-27-2009",
		COMPONENT_TITLE => "Performance Graphing",
		COMPONENT_AUTHOR => "Nagios Enterprises, LLC",
		COMPONENT_DESCRIPTION => "Provides integrated performance graphing functionality.",
		//COMPONENT_COPYRIGHT => "Copyright (c) 2009 Nagios Enterprises",
		//COMPONENT_HOMEPAGE => "http://www.nagios.com",
		
		// do not delete
		COMPONENT_PROTECTED => true,
		COMPONENT_TYPE => COMPONENT_TYPE_CORE,

		// configuration function (optional)
		//COMPONENT_CONFIGFUNCTION => "perfdata_component_config_func",
		);
		
	register_component($name,$args);
	}


////////////////////////////////////////////////////////////////////////
// URL FUNCTIONS
////////////////////////////////////////////////////////////////////////

function perfdata_get_component_url($directory_only=false){
	$url=get_base_url();
	$url.="/includes/components/perfdata/";
	if($directory_only==false)
		$url.="perfdata.php";
	return $url;
	}
	
function perfdata_chart_exists($hostname="",$servicename="_HOST_"){
	return pnp_chart_exists($hostname,$servicename);
	}


///////////	
// modified 4/25/11 to fetch graph from new graphApi.php instead of PNP -MG 
//////////	
function perfdata_get_graph_image_url($hostname="",$servicename="_HOST_",$source=1,$view=PNP_VIEW_DEFAULT,$start="",$end="",$host_id=-1,$service_id=-1){

	// no perfdata exists in pnp
	if(pnp_chart_exists($hostname,$servicename)==false){
		$url=perfdata_get_component_url(true);
		$url.="noperfdata.png?";
		}
	// perfdata exists!
	else{
	/*
		$url=perfdata_get_component_url();
		$url.="?cmd=getimage";
		$url.="&host=".pnp_convert_object_name($hostname)."&display=image";
		if($servicename!="")
			$url.="&srv=".pnp_convert_object_name($servicename);
		else
			$url.="&srv=_HOST_";
		if($start!="")
			$url.="&start=".$start;
		if($end!="")
			$url.="&end=".$end;
		$url.="&source=".$source;
		$url.="&view=".$view;
		if($host_id>0)
			$url.="&host_id=".$host_id;
		if($service_id>0)
			$url.="&service_id=".$service_id;
		}
	*/
		////////////////////////////new graph API////////////////////// 4/25/11 -MG
		$url = perfdata_get_api_url($hostname,$servicename,$source,$view,$start,$end); 
		/////////////////////////////////////////////////////////////////////
	}
	return $url;
}

////////////////////////new graph api 4/25/11 -MG //////////////////
function perfdata_get_api_url($hostname="",$service="_HOST_",$source=1,$view=PNP_VIEW_DEFAULT,$start="",$end="")
{
	$service=urlencode(str_replace(" ","_",$service));
	$url = get_base_url()."includes/components/perfdata/graphApi.php?host=$hostname&service=$service&source=$source&view=$view&start=$start&end=$end";
	return $url; 
}


	
function perfdata_get_service_sources($hostname="",$servicename="_HOST_"){
	$views=pnp_read_service_views($hostname,$servicename);
	return $views;
	}
	
function perfdata_get_friendly_source_name($sourcename,$template="defaults"){
	global $lstr;

	$name=$sourcename;
	$havename=false;
	
	//$name="passed S:$sourcename, T:$template";
	//return $name;
	
	if(array_key_exists('PerfGraphDatasourceNames',$lstr)){
		if(array_key_exists($template,$lstr['PerfGraphDatasourceNames'])){
		
			//$name="have template: $template";
			//return $name;
		
			if(array_key_exists($sourcename,$lstr['PerfGraphDatasourceNames'][$template])){
				$name=$lstr['PerfGraphDatasourceNames'][$template][$sourcename];
				//$name="template";
				$havename=true;
				}
			}
		else if(array_key_exists($sourcename,$lstr['PerfGraphDatasourceNames']["defaults"])){
			$name=$lstr['PerfGraphDatasourceNames']["defaults"][$sourcename];
			//$name="defaults";
			$havename=true;
			}
		}
		
	return $name;
	}

////////////////////////////////////////////////////////////////////////
// OUTPUT FUNCTIONS
////////////////////////////////////////////////////////////////////////

function perfdata_get_direct_graph_image_html($hostname="",$servicename="",$source=1,$view=PNP_VIEW_DEFAULT,$start="",$end="",$title="",$width="",$height="",$host_id=-1,$service_id=-1)
{
	$url=perfdata_get_graph_image_url($hostname,$servicename,$source,$view,$start,$end,$host_id,$service_id);
	//$url.="&rand=".time();
	$html="<img src='".$url."' alt='".$title."' title='".$title."' class='perfdatachart'";
	if(have_value($width)==true)
		$html.=" width='$width'";
	if(have_value($height)==true)
		$html.=" height='$height'";
	$html.=">";
	//$html=$url;
	return $html;
}
	


?>
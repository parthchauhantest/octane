<?php
// GAUGE DASHLET
//
// Copyright (c) 2013 Nagios Enterprises, LLC.
//
// LICENSE:
//
// Except where explicitly superseded by other restrictions or licenses, permission
// is hereby granted to the end user of this software to use, modify and create
// derivative works or this software under the terms of the Nagios Software License, 
// which can be found online at:
//
// http://www.nagios.com/legal/licenses/
//  
// $Id: getdata.php 9 2010-10-05 20:34:33Z egalstad $


// include the required helper file (distributed with Nagios XI)
require_once(dirname(__FILE__).'/../dashlethelper.inc.php');


// initialization stuff
pre_init();

// start session
init_session();

// grab GET or POST variables 
grab_request_vars();

// check prereqs
check_prereqs();

// check authentication
check_authentication(false);

if(!defined('GAUGES_FORM')){
header('Content-Type: application/json');
echo get_gauge_json();
}

function get_gauge_json(){

    $request=$_REQUEST;

    if(empty($request['host']) || empty($request['service']) || empty($request['ds']))
        return json_encode(get_datasources(@$request['host'], @$request['service'], @$request['ds']));

    $result=get_datasources(@$request['host'], @$request['service'], @$request['ds']);
    foreach($result as $services)
        foreach($services as $service)
            foreach($service as $ds)
                return json_encode($ds);
}

function get_datasources($host=null, $service=null, $ds=null){

    $result=array();

    $backendargs=array();
    $backendargs["orderby"]="host_name:a,service_description:a";
    if ($host)
        $backendargs["host_name"]=$host;
    if($service)
        $backendargs["service_description"]=$service;  // service

    $services=get_xml_service_status($backendargs);
    $hosts=get_xml_host_status($backendargs);
    foreach($services->servicestatus as $status){
        $status=(array)$status;
        if(!empty($status['performance_data']))
            $result[$status['host_name']][$status['name']]= get_gauge_datasource($status, $ds);
        if(empty($result[$status['host_name']][$status['name']]))
            unset($result[$status['host_name']][$status['name']]);
        if(empty($result[$status['host_name']]))
            unset($result[$status['host_name']]);
    }
    if(empty($service) || $service == '_HOST_')
    foreach($hosts->hoststatus as $status){
        $status=(array)$status;
        if(!empty($status['performance_data']))
            $result[$status['name']]['_HOST_']= get_gauge_datasource($status, $ds);
        if(empty($result[$status['name']]['_HOST_']))
            unset($result[$status['name']]['_HOST_']);
        if(empty($result[$status['name']]))
            unset($result[$status['name']]);
    }

    return $result;
}

function get_gauge_datasource($status, $ds_label){
    
    $ds = array();

    $perfdata_datasources =str_getcsv($status['performance_data'], " ","&apos;");
    foreach($perfdata_datasources as $perfdata_datasource){

        $perfdata_s = explode('=',$perfdata_datasource);

        $perfdata_name = trim(str_replace("apos;","", $perfdata_s[0]));
        //strip bad char from key name and label
        $perfdata_name =str_replace('\\', '',$perfdata_name);
        if ($ds_label && $perfdata_name != $ds_label)
            continue;
        if(empty($perfdata_s[1]))
            continue;
        $perfdata = explode(';',$perfdata_s[1]);
        $ds[$perfdata_name]['label']=$perfdata_name;
        $ds[$perfdata_name]['current']=floatval(grab_array_var($perfdata,0,0));
        $ds[$perfdata_name]['uom']=str_replace($ds[$perfdata_name]['current'], '', $perfdata[0]);
        $ds[$perfdata_name]['warn']=floatval(grab_array_var($perfdata,1,0));
        $ds[$perfdata_name]['crit']=floatval(grab_array_var($perfdata,2,0));
        $ds[$perfdata_name]['min']=floatval(grab_array_var($perfdata,3,0));
        $ds[$perfdata_name]['max']=floatval(grab_array_var($perfdata,4,0));
        
        //do some guessing if max is not set
        if($ds[$perfdata_name]['max'] == 0){
            if($ds[$perfdata_name]['crit'] != 0 && $ds[$perfdata_name]['crit'] > 0)
                $ds[$perfdata_name]['max']=$ds[$perfdata_name]['crit'] * 1.1;
            elseif($ds[$perfdata_name]['uom'] == '%')
                $ds[$perfdata_name]['max']=100;
        }
        
        // remove the item if we were not able to determine the max
        if($ds[$perfdata_name]['max'] == 0){
            unset($ds[$perfdata_name]);
            continue;
            }
        
        // add yellowZones & redZones
        if($ds[$perfdata_name]['warn'] != 0)
            $ds[$perfdata_name]['yellowZones'] = array(
                array(
                    "from" => $ds[$perfdata_name]['warn'],
                    "to" => ($ds[$perfdata_name]['crit'] != 0) ? $ds[$perfdata_name]['crit'] : $ds[$perfdata_name]['max'],
                )
            );
        if($ds[$perfdata_name]['crit'] != 0)
            $ds[$perfdata_name]['redZones'] = array(
                array(
                    "from" => $ds[$perfdata_name]['crit'],
                    "to" => $ds[$perfdata_name]['max'],
                )
            );
    }
        
    return $ds;
}

if (!function_exists('str_getcsv')) {
 
    function str_getcsv($input, $delimiter=',', $enclosure='"', $escape=null, $eol=null) {
      $temp=fopen("php://memory", "rw");
      fwrite($temp, $input);
      fseek($temp, 0);
      $r = array();
      while (($data = fgetcsv($temp, 4096, $delimiter, $enclosure)) !== false) {
        $r[] = $data;
      }
      fclose($temp);
      return $r;
    } 
}
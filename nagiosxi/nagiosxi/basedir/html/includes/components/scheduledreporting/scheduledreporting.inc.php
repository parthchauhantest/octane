<?php
// Scheduled Reporting Component
//
// Copyright (c) 2011 Nagios Enterprises, LLC.  All rights reserved.
// 
// $Id: scheduledreporting.inc.php 1483 2013-11-15 18:54:11Z jomann $

require_once(dirname(__FILE__).'/../componenthelper.inc.php');
require_once(dirname(__FILE__).'/../../common.inc.php');


// respect the name
$scheduledreporting_component_name="scheduledreporting";

// run the initialization function
scheduledreporting_component_init();

////////////////////////////////////////////////////////////////////////
// COMPONENT INIT FUNCTIONS
////////////////////////////////////////////////////////////////////////

function scheduledreporting_component_init(){
    global $scheduledreporting_component_name;
   
    $versionok=scheduledreporting_component_checkversion();
   
    $desc="";
    if(!$versionok)
        $desc="<br><b>Error: This component requires Nagios XI 20011R1.7 or later.</b>";

    $args=array(

        // need a name
        COMPONENT_NAME => $scheduledreporting_component_name,
       
        // informative information
        COMPONENT_AUTHOR => "Nagios Enterprises, LLC",
        COMPONENT_DESCRIPTION => gettext("Adds scheduled reporting capability to Nagios XI.").$desc,
        COMPONENT_TITLE => "Scheduled Reporting",
        COMPONENT_VERSION => '1.92', 
        // configuration function (optional)
        //COMPONENT_CONFIGFUNCTION => "scheduledreporting_component_config_func",
        );
       
    register_component($scheduledreporting_component_name,$args);
   
    if($versionok){
   
        // configure action callbacks
        register_callback(CALLBACK_REPORTS_ACTION_LINK,'scheduledreporting_component_report_action');
       
        // add a menu link
        register_callback(CALLBACK_MENUS_INITIALIZED,'scheduledreporting_component_addmenu');
        }
    }
   

   
///////////////////////////////////////////////////////////////////////////////////////////
// VERSION CHECK FUNCTIONS
///////////////////////////////////////////////////////////////////////////////////////////

function scheduledreporting_component_checkversion(){

    if(!function_exists('get_product_release'))
        return false;
    if(get_product_release()<207)
        return false;

    return true;
    }

///////////////////////////////////////////////////////////////////////////////////////////
// HELPER FUNCTIONS
///////////////////////////////////////////////////////////////////////////////////////////
	
function scheduledreporting_component_get_report_options($rawurl){
    global $request;
	
	$opts=array(
		"source" => "report", // report or page
		"attachments" => array(	
			),
		);

	/*
    $base_url=get_base_url();
    $theurl=str_replace($base_url,"",$raw_url);
	*/
	
	// strip out args...
	$urlparts=parse_url($rawurl);
	
	$path=$urlparts["path"];
	
	/*
	echo "PATH: $path<BR>";
	echo "PARTS:<BR>";
	print_r($urlparts);
	echo "<BR>";
	*/

	$theurl=$path;
	$theurl=str_replace("/nagiosxi/reports/","",$theurl);
	$theurl=str_replace("reports/","",$theurl);
	
	//echo "FINAL URL: $theurl<BR>";
   
	switch($theurl){
		// Variable attachment reports
		case "availability.php":
			$opts["attachments"]["pdf"]=array(
				"type" => "PDF",
				"file" => "Availability_Report.pdf",
				"urlopts" => "mode=pdf",
				"icon" => "pdf.png",
				);
			$opts["attachments"]["csvhost"]=array(
				"type" => "CSV (Host Data)",
				"file" => "Host_Availability.csv",
				"urlopts" => "mode=csv&csvtype=host",
				"icon" => "csv.png",
				);
			$opts["attachments"]["csvservice"]=array(
				"type" => "CSV (Service Data)",
				"file" => "Service_Availability.csv",
				"urlopts" => "mode=csv&csvtype=service",
				"icon" => "csv.png",
				);
			break;
			
		// PDF-only reports
		case "execsummary.php":
            if(get_product_release()>=304) {
                $opts["attachments"]["pdf"]=array(
                    "type" => "PDF",
                    "file" => "execsummary.pdf",
                    "urlopts" => "mode=pdf",
                    "icon" => "pdf.png",
                    );
                }
			break;
        case "alertheatmap.php":
			$opts["attachments"]["pdf"]=array(
				"type" => "PDF",
				"file" => "Heatmap.pdf",
				"urlopts" => "mode=pdf",
				"icon" => "pdf.png",
				);
			break;
		// PDF and CSV reports
		case "statehistory.php":
		case "histogram.php":
		case "topalertproducers.php":
		case "notifications.php":
		case "eventlog.php":
			$fname=scheduledreporting_component_get_report_fname($theurl);
			$opts["attachments"]["pdf"]=array(
				"type" => "PDF",
				"file" => $fname.".pdf",
				"urlopts" => "mode=pdf",
				"icon" => "pdf.png",
				);
			$opts["attachments"]["csv"]=array(
				"type" => "CSV",
				"file" => $fname.".csv",
				"urlopts" => "mode=csv",
				"icon" => "csv.png",
				);
			break;
		default;
			break;
		}
		
	return $opts;
	}

function scheduledreporting_component_get_report_fname($url){
	$fname=$url;
	switch($url){
		case "statehistory.php":
			$fname="StateHistory";
			break;
		case "histogram.php":
			$fname="AlertHistogram";
			break;
		case "topalertproducers.php":
			$fname="TopAlertProducers";
			break;
		case "notifications.php":
			$fname="Notifications";
			break;
		case "eventlog.php":
			$fname="EventLog";
			break;
		default:
			break;
		}
	return $fname;
	}
	
function scheduledreporting_component_get_reports($userid=0){
  
	$scheduled_reports=array();
    $temp=get_user_meta($userid,'scheduled_reports');
	if($temp!=null)
		$scheduled_reports=unserialize($temp);

	return $scheduled_reports;
	}


function scheduledreporting_component_get_report_id($id=-1,$userid=0){

	$scheduled_reports=scheduledreporting_component_get_reports($userid);
	if(!array_key_exists($id,$scheduled_reports))
		return null;

	return $scheduled_reports[$id];
	}	
	
	
function scheduledreporting_component_get_scheduled_report_url($id,$userid=0){

	$report=scheduledreporting_component_get_report_id($id,$userid);
	if($report==null)
		return null;
	
	$url="";
	
	$bu=get_base_url();
	//echo "BU: $bu<BR>";
	
	$rawurl=$report["url"];
	
	//echo "RAW: $rawurl<BR>";
	

	// full url - don't mess with it
	$r=strpos($rawurl,"http");
	if($r==0 && $r!==FALSE)
		$url=$rawurl;
	// else append base url
	else
		$url=$bu.$rawurl;
	
	return $url;
	}
	
	
function scheduledreporting_component_delete_report($id,$userid=0){

	$scheduled_reports=scheduledreporting_component_get_reports($userid);
	unset($scheduled_reports[$id]);
	scheduledreporting_component_save_reports($scheduled_reports,$userid);

	// update cron
	scheduledreporting_component_delete_cron($id,$userid);
	}


function scheduledreporting_component_save_reports($reports,$userid=0){
  
    set_user_meta($userid,'scheduled_reports',serialize($reports),false);
	}
	
	
function scheduledreporting_component_update_cron($id,$userid=0){
	$croncmd=scheduledreporting_component_get_cron_cmdline($id,$userid);
	$crontimes=scheduledreporting_component_get_cron_times($id,$userid);
	
	$cronline=sprintf("%s\t%s > /dev/null 2>&1\n",$crontimes,$croncmd);
	scheduled_reporting_component_log("UPDATE CRON: {$cronline}\n");
	$tmpfile=get_tmp_dir()."/scheduledreport.".$id;
	file_put_contents($tmpfile,$cronline);
	
    
	$cmd="crontab -l | grep -v '".$croncmd."' | cat - ".$tmpfile." | crontab - ; rm -f ".$tmpfile;
    //echo "<BR>CMD: $cmd<BR>";	
	exec($cmd,$output,$bool);
	scheduled_reporting_component_log("CMD: $cmd\nRET: $bool\nOUTPUT: ".implode("\n",$output)); 
	
	if($bool > 0)
		echo "ERROR: ".implode("<br />\n",$output); 
}	
	
function scheduledreporting_component_delete_cron($id,$userid=0){

	$croncmd=scheduledreporting_component_get_cron_cmdline($id,$userid);
	$cmd="crontab -l | grep -v '".$croncmd."' | crontab -";
    //echo "<BR>CMD: $cmd<BR>";
	exec($cmd,$output,$bool);
	scheduled_reporting_component_log("CMD: $cmd\nRET: $bool\nOUTPUT: ".implode("\n",$output));
	
	if($bool > 0)
		echo "ERROR: ".implode("<br />\n",$output); 
	
	}
	
function scheduledreporting_component_get_cron_cmdline($id,$userid=0){
	$cmdline=scheduledreporting_component_get_cmdline($id,$userid);
	$cmd=$cmdline;
	return $cmd;
	}

function scheduledreporting_component_get_cmdline($id,$userid=0){
	$component_path="/usr/local/nagiosxi/html/includes/components/scheduledreporting";
	$username=get_user_attr($userid,"username");
	$cmd="/usr/bin/php ".$component_path."/sendreport.php --report=".$id." --username=".$username; //removed -f -MG
	return $cmd;
	}
	
function scheduledreporting_component_get_cron_times($id){

	$times="";
	
	$sr=scheduledreporting_component_get_report_id($id);
	if($sr==null)
		return $times;
		
	$frequency=grab_array_var($sr,"frequency","");

	$sched=grab_array_var($sr,"schedule",array());
	$hour=grab_array_var($sched,"hour",0);
	$minute=grab_array_var($sched,"minute",0);
	$ampm=grab_array_var($sched,"ampm","AM");
	$dayofweek=grab_array_var($sched,"dayofweek",0);
	$dayofmonth=grab_array_var($sched,"dayofmonth",1);
	
	$h=intval($hour);
	$m=intval($minute);
	if(($ampm=="PM") && ($h < 12))
		$h+=12;
	if(($ampm=="AM") && ($h == 12))
		$h=0;
	if($frequency=="Monthly")
		$dom=$dayofmonth;
	else
		$dom="*";
	if($frequency=="Weekly")
		$dow=$dayofweek;
	else
		$dow="*";
		
	$times=sprintf("%d %d %s * %s",$m,$h,$dom,$dow);
		
	return $times;
	}
 
	
	
///////////////////////////////////////////////////////////////////////////////////////////
// MENU ITEMS
///////////////////////////////////////////////////////////////////////////////////////////

function scheduledreporting_component_addmenu(){

    $desturl=get_component_url_base("scheduledreporting");

    $mi=find_menu_item(MENU_REPORTS,"menu-reports-sectionend-myreports","id");
    if($mi==null)
        return;
       
    $order=grab_array_var($mi,"order","");
    if($order=="")
        return;
	
	init_session();
	db_connect_all();
	
    $reports = get_user_meta( '0' , 'scheduled_reports' );
    ($reports) ? $reports = unserialize( $reports ) : $reports = array();
    
    $neworder=$order+.01;

    // scheduled reports
    add_menu_item(MENU_REPORTS,array(
        "type" => "menusection",
        "title" => gettext("Scheduled Reports"),
        "id" => "menu-reports-scheduledreportings",
        "order" => $neworder,
        "opts" => array(
            "id" => "scheduledreportings",
            "expanded" => true,
            "url" => $desturl.'/schedulereport.php',
            )
        ));

    $neworder+=0.01;

    foreach($reports as $key => $value){
		add_menu_item(MENU_REPORTS,array(
			"type" => MENULINK,
			"title" => $value['name'],
			"id" => "menu-reports-scheduledreporting$key",
			"order" => $neworder,
			"opts" => array(
				"href" => $desturl."/schedulereport.php?go=1&id=".$key,
				)));
		$neworder += .01;
    }
    // add each scheduled report


    add_menu_item(MENU_REPORTS,array(
        "type" => "menusectionend",
        "id" => "menu-reports-sectionend-scheduledreportings",
        "order" => $neworder,
        "title" => "",
        "opts" => ""
        ));   
           
    }
   
   
///////////////////////////////////////////////////////////////////////////////////////////
// ACTION FUNCTIONS
///////////////////////////////////////////////////////////////////////////////////////////

function scheduledreporting_component_report_action($cbtype,&$cbargs){
    global $request;
   
    $current_url=get_current_url();
    $base_url=get_base_url();
   
    // get report url (strip out protocol, ip, root)
    // NOTE: You can only use the relative url to http://server/nagiosxi because the IP address/protocol may be different when running a scheduled report from cron, compared to access the XI interface from the user's browser!
    $theurl=str_replace($base_url,"",$current_url);
   
    // should we even allow scheduling of this report?  only do so for reports we know about
    $show_link=true;
	$is_page=false;
    switch($theurl){
        case "reports/availability.php":
			$report_name = "Availability Report";
			break;
        case "reports/statehistory.php":
			$report_name = "State History Report";
			break;
        case "reports/topalertproducers.php":
			$report_name = "Top Alert Producers Report";
			break;
        case "reports/histogram.php":
			$report_name = "Histogram Report";
			break;
        case "reports/notifications.php":
			$report_name = "Notifications Report";
			break;
        case "reports/eventlog.php":
			$report_name = "Eventlog Report";
			break;
        case "reports/alertheatmap.php":
			$report_name = "Alert Heatmap Report";
            break;
        case "reports/execsummary.php":
			$report_name = "Executive Summary Report";
            break;			
        default;
			$show_link=false;
			$is_page=true;
            break;
        }
		
	//currently this does nothing...	
    if($show_link==false)
        return;
           
    $theurl.="?";
	if($is_page==true)
		$theurl.="type=page";
    // add GET/POST args to url
    foreach($request as $var => $val){
        $theurl.="&".urlencode($var)."=".urlencode($val);
        }

    // where should we direct people?
    $desturl=get_component_url_base("scheduledreporting",true)."/schedulereport.php?name=$report_name&url=".urlencode($theurl);
   
	$title=gettext("Schedule This Report");
    $cbargs["actions"][]="<a href='".$desturl."' alt='".$title."' title='".$title."'><img src='".theme_image("time.png")."' border='0'></a>";

    $title=gettext("Email This Report");
    $cbargs["actions"][]="<a href='".$desturl."&sendonce=1' alt='".$title."' title='".$title."'><img src='".theme_image("sendemail.png")."' border='0'></a>";
   
    return;
    }

	
	
function scheduled_reporting_component_log($msg='') {
	global $cfg;
	
	$base = grab_array_var($cfg,'root_dir','/usr/local/nagiosxi'); 
	$logfile = $base.'/var/scheduledreporting.log'; 
	
	//prepend time
	$msg = '['.date('r').'] '.$msg; 
	@file_put_contents($logfile,$msg,FILE_APPEND); 
}	
	

?>

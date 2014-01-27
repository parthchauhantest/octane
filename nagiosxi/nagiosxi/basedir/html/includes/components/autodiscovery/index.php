<?php
// AUTO-DISCOVERY
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: index.php 1096 2013-02-04 21:51:43Z mguthrie $

require_once(dirname(__FILE__).'/../../common.inc.php');


// initialization stuff
pre_init();

// start session
init_session(true);

// grab GET or POST variables 
grab_request_vars();

// check prereqs
check_prereqs();

// check authentication
check_authentication(false);

if(is_authorized_to_configure_objects()==false)
	header("Location: ".get_base_url());


route_request();

function route_request(){
	global $request;
	
	// check installation
	$installok=autodiscovery_component_checkinstall($installed,$prereqs,$missing_components);
	if(!$installok)
		display_install_error($installed,$prereqs,$missing_components);
			
	$mode=grab_request_var("mode");
	switch($mode){
		case "newjob":
		case "editjob":
			$cancelButton=grab_request_var("cancelButton");
			if ($cancelButton){
				display_jobs();
				break;
				}
			$update=grab_request_var("update");
			if($update==1)
				do_update_job();
			else
				display_add_job();
			break;
		case "deletejob":
			do_delete_job();
			break;
		case "viewjob":
			do_view_job();
			break;
		case "runjob":
			do_run_job();
			break;
		case "processjob":
			do_process_job();
			break;
		case "csv":
			do_csv();
			break;
        case "jobcomplete":
            is_job_complete();
		default:
			display_jobs();
			break;
		}
	}
	
	
function do_csv(){

	$jobid=grab_request_var("job");
	$show_old=grab_request_var("showold",0);
	
	$services=autodiscovery_component_parse_job_data($jobid);
	
	//echo "JOBID: $jobid<BR>";
	//echo "SERVICES:<BR>";
	//print_r($services);
	//exit();
	
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=\"autodiscovery.csv\"");

	echo "address,hostname,type,os,status\n";
	
	foreach($services as $address => $arr){
		if($show_old==0 && $arr["status"]=="old")
			continue;
		echo $arr["address"].",".$arr["fqdns"].",".$arr["type"].",".$arr["os"].",".ucwords($arr["status"])."\n";
		}
	
	exit();
	}

function is_job_complete() {
    $jobid = grab_request_var('jobid');
    $output_file=get_component_dir_base("autodiscovery")."/jobs/".$jobid.".xml";
    $error  = false; //Place for errors when situation arises
    $total_hosts=0;
    $new_hosts=0;
    $xml=@simplexml_load_file($output_file);
    if($xml){
        foreach($xml->device as $d){
            $status=strval($d->status);
            if($status=="new")
                $new_hosts++;
            $total_hosts++;
        }
    }
    $jobdone = file_exists($output_file);

    if($jobdone && !$xml) {
        $error = 'XML was not valid.';
    }
    
    $json = array(  'jobdone' => $jobdone,
                    'error' => $error,
                    'total_hosts' => $total_hosts,
                    'new_hosts' => $new_hosts,
                    'jobid' => $jobid
                    );
    
    $json_str = json_encode($json);
    header("Content-type: application/json");
    echo $json_str;
    exit();
}
    
    

function display_jobs($error=false,$msg=""){
	global $request;

	// makes sure user has appropriate license level
	licensed_feature_check();

	// generage messages...
	if($msg==""){
		if(isset($request["jobadded"]))
			$msg="Auto-discovery job added.";
		if(isset($request["jobupdated"]))
			$msg="Auto-discovery job updated.";
		if(isset($request["jobdeleted"]))
			$msg="Job deleted.";
		if(isset($request["jobrun"]))
			$msg="Auto-discovery job started.";			
		}

	// start the HTML page
	do_page_start(array("page_title"=>"Auto-Discovery Jobs"),true);
	
?>
	<h1><?php echo gettext("Auto-Discovery Jobs"); ?></h1>
	
<?php
	display_message($error,false,$msg);
?>
<script>
    $(document).ready(function() {
        function get_autodiscovery_jobs() {
            $('.job_throbber').each(function() {
                t_id = $(this).attr('id');
                tag_content = $('#job_info_' + t_id).text();
                if($.trim(tag_content) == 'N/A') {
                    var data = {};
                    data.mode = 'jobcomplete';
                    data.jobid = t_id;
                    $.getJSON(  'index.php',
                                data,
                                function(data) {
                                    jobid = data.jobid
                                    if(data.error) {
                                        $('#' + jobid).html(data.error);
                                        $('#job_info_' + jobid).html('Error.');
                                    }
                                    else if(data.jobdone) {
                                        $('#' + jobid).html('Finished');
                                        job_info = "<b><a href='?mode=processjob&job=" + encodeURI(jobid) + "'>" + data.new_hosts + " <?php echo gettext("New") ?> </a></b> / " + data.total_hosts + "<?php echo gettext(" Total") ?>";
                                        $('#job_info_' + jobid).html(job_info);
                                    }
                                }
                    );
                }
            });
        }
        get_autodiscovery_jobs();
        setInterval(get_autodiscovery_jobs, 10000);
    })
</script>


	<p>
	<a href="?mode=newjob"><img src="<?php echo theme_image("add.png");?>" alt="Start a new discovery job" title="Start a new discovery job"> Start a new discovery job</a>
	</p>
	
	<p>
	<a href="?"><img src="<?php echo theme_image("reload.png");?>" alt="Refresh job list" title="Refresh job list"> Refresh job list</a>
	</p>
	

	<table class="standardtable">
	<thead>
	<tr><!--<th>Job ID</th>-->
		<th><?php echo gettext("Scan Target"); ?></th>
		<th><?php echo gettext("Exclusions"); ?></th>
		<th><?php echo gettext("Schedule"); ?></th>
		<th><?php echo gettext("Last Run"); ?></th>
		<th><?php echo gettext("Devices Found"); ?></th>
		<th><?php echo gettext("Created By"); ?></th>
		<th><?php echo gettext("Status"); ?></th>
		<th><?php echo gettext("Actions"); ?></th></tr>
	</thead>
	<tbody>
<?php
	$jobs=autodiscovery_component_getjobs();
	if(count($jobs)==0)
		echo "<tr><td colspan='7'>".gettext("There are no auto-discovery jobs.")."  <a href='?mode=newjob'>".gettext("Add one now")."</a>.</td></tr>";
	else{
	
		// sort jobs by start time
		/*
		foreach($jobs as $jobid => $row){
			$search[$jobid] = $row['start_date'];
			}	
		array_multisort($search,SORT_DESC,$jobs);
		*/
		
		foreach($jobs as $jobid => $jobarr){

			$frequency=grab_array_var($jobarr,"frequency","Once");
			$sched=grab_array_var($jobarr,"schedule",array());
			
			$hour=grab_array_var($sched,"hour","");
			$minute=grab_array_var($sched,"minute","");
			$ampm=grab_array_var($sched,"ampm","");
			$dayofweek=grab_array_var($sched,"dayofweek","");
			$dayofmonth=grab_array_var($sched,"dayofmonth","");		
			
			$days = array(       
				0 => 'Sunday',
				1 => 'Monday',
				2 => 'Tuesday',
				3 => 'Wednesday',
				4 => 'Thursday',
				5 => 'Friday',
				6 => 'Saturday',
				);
		
			if($frequency=="Once")
				$fstr="";
			else
				$fstr=$hour.":".$minute." ".$ampm;
			if($frequency=="Weekly")
				$fstr.=" ".$days[$dayofweek];
			else if($frequency=="Monthly")
				$fstr.=", Day ".$dayofmonth;


		
			echo "<tr>";
			//echo "<td>".htmlentities($jobid)."</td>";
			echo "<td>".htmlentities($jobarr["address"])."</td>";
			$exclude_address=grab_array_var($jobarr,"exclude_address");
			if($exclude_address=="")
				$exclude_address="-";
			echo "<td>".htmlentities($exclude_address)."</td>";
			
			echo "<td>".htmlentities($frequency)."<BR>".$fstr."</td>";
			
			$output_file=get_component_dir_base("autodiscovery")."/jobs/".$jobid.".xml";
			$total_hosts=0;
			$new_hosts=0;
			$xml=@simplexml_load_file($output_file);
			if($xml){
				foreach($xml->device as $d){
					$status=strval($d->status);
					if($status=="new")
						$new_hosts++;
					$total_hosts++;
					}
				}

			$date_file=get_component_dir_base("autodiscovery")."/jobs/".$jobid.".out";	
			//$t=$jobarr["start_date"];
			$t=filemtime($date_file);
			echo "<td>".get_datetime_string($t)."</td>";

			if(file_exists($output_file))
				$running=false;
			else
				$running=true;
				
			echo "<td><div id='job_info_$jobid'>";
			//~ if($running==true)
				echo "N/A";
			//~ else{
				//~ echo "<b><a href='?mode=processjob&job=".urlencode($jobid)."'>".$new_hosts." ".gettext("New")."</a></b> / ".$total_hosts.gettext(" Total");
				//~ }
			echo "</div></td>";
			
			
			echo "<td>".htmlentities($jobarr["initiator"])."</td>";

			echo "<td><div id='$jobid' class='job_throbber'>";
			//~ if($running==true){
				echo "<img src='".theme_image("throbber.gif")."'> ";
				//~ echo "Running";
				//~ }
			//~ else{
				//~ echo "Finished";
				//~ }
            echo "</div>";
			echo "</td>";

			echo "<td>";
			if($running==true)
				echo "<a href='?mode=deletejob&job=".urlencode($jobid)."'><img src='".theme_image("b_delete.png")."' alt='Cancel' title='Cancel'></a>";
			else{
				echo "<a href='?mode=editjob&job=".urlencode($jobid)."'><img src='".theme_image("editsettings.png")."' alt='Edit Job' title='Edit Job'></a>&nbsp;";
				echo "<a href='?mode=runjob&job=".urlencode($jobid)."'><img src='".theme_image("reload.png")."' alt='Re-Run Job' title='Re-Run Job'></a>&nbsp;";
				echo "<a href='?mode=deletejob&job=".urlencode($jobid)."'><img src='".theme_image("b_delete.png")."' alt='Delete Job' title='Delete Job'></a>&nbsp;";
				echo "<a href='?mode=viewjob&job=".urlencode($jobid)."'><img src='".theme_image("detail.png")."' alt='View Job Results' title='View Job Results'></a>&nbsp;";
				//echo "<a href='?mode=csv&job=".urlencode($jobid)."'><img src='".theme_image("csv.png")."' alt='Get CSV Output' title='Get CSV Output'></a>&nbsp;";
				//echo "<a href='?mode=processjob&job=".urlencode($jobid)."'><img src='".theme_image("b_next.png")."' alt='Process Job Results' title='Process Job Results'></a>&nbsp;";
				}
			echo "</td>";
			echo "</tr>";
			}
		}
?>
	</tbody>
	</table>
<?php		
	
	// closes the HTML page
	do_page_end(true);
	}
	
	
function do_update_job(){
	global $lstr;
	
	// check session
	check_nagios_session_protector();
	
	// get variables
	$jobid=grab_request_var("job",-1);	
	if($jobid==-1)
		$add=true;
	else
		$add=false;

	$address=grab_request_var("address");
	$exclude_address=grab_request_var("exclude_address");
	$os_detection=grab_request_var("os_detection","on");
	$topology_detection=grab_request_var("topology_detection","on");
	
	$frequency=grab_request_var("frequency","Once");
	$hour=grab_request_var("hour","09");
	$minute=grab_request_var("minute","00");
	$ampm=grab_request_var("ampm","AM");
	$dayofweek=grab_request_var("dayofweek","");
	$dayofmonth=grab_request_var("dayofmonth","");
	

	$errmsg=array();
	$errors=0;
	
	// check for errors
	if(in_demo_mode()==true)
		$errmsg[$errors++]=$lstr['DemoModeChangeError'];
	if($address=="")
		$errmsg[$errors++]=gettext("Invalid address.");
		
	if(preg_match('/[^0-9 .\/,-]/',$address))
		$errmsg[$errors++]=gettext("Invalid characters in scan target.");
	if(preg_match('/[^0-9 .\/,-]/',$exclude_address))
		$errmsg[$errors++]=gettext("Invalid characters in exclude IPs.");
		
	if($frequency!="Once" && enterprise_features_enabled()==false){
		$errmsg[$errors++]=gettext("Scheduled scans are only available in the Enterprise Edition.");
		}
		
	// handle errors
	if($errors>0)
		display_add_job(true,$errmsg);
		
	// single ip address specified, so add netmask
	if(strstr($address,"/")===FALSE)
		$address=trim($address)."/32";
    if (strpos($address,"/32") || strpos($address,"/31")){
        $addressparts=explode("/",$address);
        $address=$addressparts[0]." ".$addressparts[0];
        $mask=$addressparts[1];
    }
	
	// okay, so add/update job
	if($jobid==-1)
		$jobid=random_string(6);
	$job=array(
		"address" => $address,
		"exclude_address" => $exclude_address,
		"os_detection" => $os_detection,
		"topology_detection" => $topology_detection,
		"initiator" => $_SESSION["username"],
		"start_date" => time(),
		
		"frequency" => $frequency,
		"schedule" => array(
			"hour" => $hour,
			"minute" => $minute,
			"ampm" => $ampm,
			"dayofweek" => $dayofweek,
			"dayofmonth" => $dayofmonth,
			),
		);
	autodiscovery_component_addjob($jobid,$job);
	
	// always delete the old cron job (it might not exit)
	autodiscovery_component_delete_cron($jobid);
	
	// add a new cron job if this should (now) be scheduled
	if($frequency!="Once")
		autodiscovery_component_update_cron($jobid);
		
	//exit();
		
	/*
	// run auto-discovery script
	$base_dir=get_component_dir_base("autodiscovery");
	$script_dir=$base_dir."/scripts/";
	$jobs_dir=$base_dir."/jobs/";
	
	$watch_file=$jobs_dir.$jobid.".watch";
	$out_file=$jobs_dir.$jobid.".out";
	$xml_file=$jobs_dir.$jobid.".xml";
	
	// create watch file
	touch($watch_file);
	
	$osd="";
	if($os_detection=="on")
		$osd="--detectos=1";
	$topod="";
	if($topology_detection=="on")
		$topod="--detecttopo=1";

	$cmdline="php ".$script_dir."autodiscover_new.php --addresses=\"".escapeshellcmd($address)."\"  --exclude=\"".escapeshellcmd($exclude_address)."\" --output=".$xml_file." --watch=".$watch_file." --onlynew=0 --debug=1 ".$osd." ".$topod." > ".$out_file." 2>&1 & echo $!";
	*/
	
	if($add==true && $frequency=="Once"){
		do_run_job($jobid,false);
		}
		
	// redirect user
	if($add==true)
		header("Location: ?jobadded");
	else
		header("Location: ?jobupdated");
	}
	
function do_run_job($jobid=-1,$redirect=true){
	global $lstr;
	
	// get variables
	if($jobid==-1)
		$jobid=grab_request_var("job",-1);	


	$errmsg=array();
	$errors=0;
	
	// check for errors
	if(in_demo_mode()==true)
		$errmsg[$errors++]=$lstr['DemoModeChangeError'];
	if($jobid==-1)
		$errmsg[$errors++]="Invalid job.";	
	else{
		$cmdline=autodiscovery_component_get_cmdline($jobid);
		if($cmdline=="")
			$errmsg[$errors++]="Invalid command.";	
		}
		
	// handle errors
	if($errors>0)
		display_jobs(true,$errmsg);		

	// prep files
	//autodiscovery_component_prep_job_files($jobid);		
	
	//echo "FILES PREPPED<BR>";
	//exit();
		
	// run the command	
	//echo "CMD: $cmdline<BR>";
	exec($cmdline,$op);
	//exit();
		
	// redirect user
	if($redirect==true)
		header("Location: ?jobrun");		
	}
	
	
function do_delete_job(){

	$jobid=grab_request_var("job");

	// delete files
	$base_dir=get_component_dir_base("autodiscovery");
	$output_file=$base_dir."/jobs/".$jobid.".xml";
	$watch_file=$base_dir."/jobs/".$jobid.".watch";
	$tmp_file=$base_dir."/jobs/".$jobid.".out";
	unlink($watch_file);
	unlink($output_file);
	unlink($tmp_file);

	//echo "WATCH: $watch_file<BR>";
	//echo "OUTPUT: $output_file<BR>";
	//echo "TMP: $tmp_file<BR>";
	
	// remove job
	autodiscovery_component_delete_jobid($jobid);
	
	//print_r($jobs);

	//exit();
	
	// redirect user
	header("Location: ?jobdeleted");
	}
	
	
	
function do_view_job(){
	global $request;

	// start the HTML page
	do_page_start(array("page_title"=>"Auto-Discovery Jobs"),true);
	
?>
	<h1><?php echo gettext("Scan Results"); ?></h1>

	<p><a href="?"><?php echo gettext("Back To Auto-Discovery Jobs"); ?></a></p>

<?php

	$jobid=grab_request_var("job");
	
	$show_services=grab_request_var("showservices",0);	
	$show_old=grab_request_var("showold",0);	
	
	$new_hosts=0;
	$total_hosts=0;
	
	$services=autodiscovery_component_parse_job_data($jobid,$new_hosts,$total_hosts);
	
	$jobarr=autodiscovery_component_get_jobid($jobid);
	
	//$t=$jobarr["start_date"];
	$date_file=get_component_dir_base("autodiscovery")."/jobs/".$jobid.".out";	
	$t=filemtime($date_file);
			
	// build url for later use
	$page_url="?1";
	foreach($request as $var => $val)
		$page_url.="&".urlencode($var)."=".urlencode($val);
	
?>

	<br>

	
	<div style="float: left">
	<table class="standardtable">
	<thead>
	<tr><th colspan="2"><?php echo gettext("Scan Summary"); ?></th></tr>
	</thead>
	<tbody>
	<tr><td><?php echo gettext("Scan Date"); ?>:</td><td><?php echo get_datetime_string($t);?></td></tr>
	<tr><td><?php echo gettext("Scan Address"); ?>:</td><td><?php echo $jobarr["address"];?></td></tr>
	<?php
	$exclude_address=grab_array_var($jobarr,"exclude_address");
	if($exclude_address=="")
		$exclude_address="-";
	?>
	<tr><td><?php echo gettext("Excludes"); ?>:</td><td><?php echo $exclude_address;?></td></tr>
	<tr><td><?php echo gettext("Initiated By"); ?>:</td><td><?php echo $jobarr["initiator"];?></td></tr>
	<tr><td><?php echo gettext("Total Hosts Found"); ?>:</td>
	<td><?php echo $total_hosts;?>&nbsp;&nbsp; 
<?php
	if($show_old==0)
		echo "<a href='".$page_url."&showold=1'>Show all</a>";
?>
	</td></tr>
	<tr><td><?php echo gettext("New Hosts Found"); ?>:</td><td><b><?php echo $new_hosts;?></b>&nbsp;&nbsp;
<?php
	if($show_old==1)
		echo "<a href='".$page_url."&showold=0'>Show only new</a>";
?>
	</td></tr>
	</tbody>
	</table>
	</div>
	
	<div style="float: left; margin-left: 25px;">
	<table class="standardtable">
	<thead>
	<tr><th colspan="2"><?php echo gettext("Processing Options"); ?></th></tr>
	</thead>
	<tbody>
	<tr>
	<td><?php echo gettext("Export Data As"); ?>:</td>
	<td>
	<a href="<?php echo $page_url;?>&mode=csv" target="_blank" alt="Export As CSV" title="Export As CSV"><img src="<?php echo theme_image("csv.png");?>"></a>
	</td>
	</tr>
	
	<tr>
	<td><?php echo gettext("Configure Basic Monitoring"); ?>:</td>
	<td>
	<?php
	if($show_old==1){
		echo "<a href='?mode=processjob&job=".urlencode($jobid)."&show=new'><img src='".theme_image("b_next.png")."'> New hosts only</a><br>";
		echo "<a href='?mode=processjob&job=".urlencode($jobid)."&show=all'><img src='".theme_image("b_next.png")."'> Both old and new hosts</a>";
		}
	else{
		echo "<a href='?mode=processjob&job=".urlencode($jobid)."&show=new'><img src='".theme_image("b_next.png")."'> New hosts</a><br>";
		}
	?>
	</p>

	</tbody>
	</table>
	</div>
	
	<br clear="all">
	
	
	

	<div class="sectionTitle"><?php echo gettext("Discovered Items"); ?></div>

<?php
	if($show_services==1)
		$str=" and services";
	else
		$str="";
?>

	<p><?php echo gettext("The hosts"); ?><?php echo $str;?> <?php echo gettext("below were discovered during the auto-discovery scan."); ?></p>
	
<?php
	if($show_services==0)
		echo "<p><a href='".$page_url."&showservices=1'>Show discovered services</a></p>";
	else
		echo "<p><a href='".$page_url."&showservices=0'>Hide services</a></p>";
?>	
	<table class="standardtable">
	<thead>
	<tr>
		<th rowspan="2"><?php echo gettext("Address"); ?></th>
		<th rowspan="2"><?php echo gettext("Host Name"); ?></th>
		<th rowspan="2"><?php echo gettext("Type"); ?></th>
		<th rowspan="2"><?php echo gettext("OS"); ?></th>
		<th rowspan="2"><?php echo gettext("Status"); ?></th>
<?php
	if($show_services==1)
		echo '<th colspan="3">Services</th>';
?>
	</tr>
<?php
	if($show_services==1)
		echo "<tr><th>Service Name</th><th>Port</th><th>Protocol</th></tr>";
?>
	</thead>
	<tbody>

<?php		
	$output="";
	foreach($services as $address => $arr){
	
		$status="";
		if($arr["status"]=="new"){
			$status="New";
			}
		else{
			$status="Old";
			}
			
		if($show_old==0 && $status=="Old")
			continue;
	
		$output.='<tr>';
		$output.='<td>'.$arr["address"].'</td>';
		$output.='<td>'.$arr["fqdns"].'</td>';
		$output.='<td>'.$arr["type"].'</td>';
		$output.='<td>'.$arr["os"].'</td>';
		$output.='<td colspan="5">'.$status.'</td>';
		$output.='</tr>';
	
		if($show_services==1){
			foreach($arr["ports"] as $pid => $parr){
				$output.='<tr>';
				$output.='<td colspan="5"></td>';
				$output.='<td>'.$parr["service"].'</td>';
				$output.='<td>'.$parr["port"].'</td>';
				$output.='<td>'.$parr["protocol"].'</td>';
				$output.='</tr>';
				}
			if(count($arr["ports"])==0){
				$output.='<tr><td colspan="5"></td><td colspan="3">'.gettext('No services found.').'</td></tr>';
				}
			}
			
		$output.='<tr></tr>';
		}
		
	echo $output;
?>
	</tbody>
	</table>
	
<?php		
	
	// closes the HTML page
	do_page_end(true);
	}
	
function do_process_job(){

	$jobid=grab_request_var("job");
	$show=grab_request_var("show","all");
	
	$url=get_base_url()."/config/monitoringwizard.php?update=1&nextstep=2&wizard=autodiscovery&job=".urlencode($jobid)."&nsp=".get_nagios_session_protector_id()."&show=".urlencode($show);
	header("Location: $url");
	}
	
function display_add_job($error=false,$msg=""){
	global $lstr;
	
	// defaults
	$address="192.168.1.0/24";
	$exclude_address="";
	$os_detection="on";
	$topology_detection="off";
	
	$frequency="Once";
	$hour="09";
	$minute="00";
	$ampm="AM";
	$dayofweek="1";
	$dayofmonth="1";	
	
	$jobid=grab_request_var("job",-1);	

	if($jobid==-1){
		$title="New Auto-Discovery Job";
		$add=true;
		}
	else{
		$title="Edit Auto-Discovery Job";
		$add=false;
		

		// get existing job
		$jobarr=autodiscovery_component_get_jobid($jobid);
		
		// vars default to saved values
		$address=grab_array_var($jobarr,"address","192.168.1.0/24");
		$exclude_address=grab_array_var($jobarr,"exclude_address","");
		$os_detection=grab_array_var($jobarr,"os_detection","on");
		$topology_detection=grab_array_var($jobarr,"topology_detection","on");

		$frequency=grab_array_var($jobarr,"frequency",$frequency);
		
		$sched=grab_array_var($jobarr,"schedule",array());
		$hour=grab_array_var($sched,"hour",$hour);
		$minute=grab_array_var($sched,"minute",$minute);
		$ampm=grab_array_var($sched,"ampm",$ampm);
		$dayofweek=grab_array_var($sched,"dayofweek",$dayofweek);
		$dayofmonth=grab_array_var($sched,"dayofmonth",$dayofmonth);		
		}

	$address=grab_request_var("address",$address);
	$exclude_address=grab_request_var("exclude_address",$exclude_address);
	$os_detection=grab_request_var("os_detection",$os_detection);
	$topology_detection=grab_request_var("topology_detection",$topology_detection);
	
	$frequency=grab_request_var("frequency",$frequency);
	$hour=grab_request_var("hour",$hour);
	$minute=grab_request_var("minute",$minute);
	$ampm=grab_request_var("ampm",$ampm);
	$dayofweek=grab_request_var("dayofweek",$dayofweek);
	$dayofmonth=grab_request_var("dayofmonth",$dayofmonth);
	

	

	// start the HTML page
	do_page_start(array("page_title"=>$title),true);
?>
	<h1><?php echo $title;?></h1>

<?php
	display_message($error,false,$msg);
?>

<?php
	// Enterprise Edition message
	if($frequency!="Once")
		echo enterprise_message();
?>
	
	<p>
	<?php echo gettext("Use this form to configure an auto-discovery job."); ?>
	</p>

	<form id="updateForm" method="post" action="">
	<input type="hidden" name="update" value="1">
	<input type="hidden" name="job" value="<?php echo  encode_form_val($jobid);?>">
	<?php echo get_nagios_session_protector();?>

	<table class="editDataSourceTable">

	<tr>
	<td valign="top">
	<label for="addressBox"><?php echo gettext("Scan Target"); ?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="text" size="15" name="address" id="addressBox" value="<?php echo encode_form_val($address);?>" class="textfield" /><br class="nobr" />
	<?php echo gettext("Enter an network address and netmask to define the IP ranges to scan."); ?><br><br>
	</td>
	</tr>

	<tr>
	<td valign="top">
	<label for="addressBox"><?php echo gettext("Exclude IPs"); ?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="text" size="30" name="exclude_address" id="excludeaddressBox" value="<?php echo encode_form_val($exclude_address);?>" class="textfield" /><br class="nobr" />
	<?php echo gettext("An optional comma-separated list of IP addresses and/or network addresses to exclude from the scan."); ?><br>
	<b><?php echo gettext("Note"); ?>:</b>
	<?php echo gettext("The excluded addresses may be pinged, but they will not be scanned for open/available services via nmap."); ?><br><br>
	</td>
	</tr>

	
<?php
	if(use_2012_features()==true){
?>
	<tr>
	<td valign="top">
	<label>Schedule:</label><br class="nobr" />
	</td>
	<td>
	
	<table border="0">
	<tr>
	<td valign="top"><label>Frequency:</label></td>
	<td>
	<select name='frequency' id='select_frequency' onchange='showTimeOpts()'>
	<option value='Once' <?php echo is_selected($frequency,"Once");?>>One Time</option>
	<!--<option value='Disabled' <?php echo is_selected($frequency,"Disabled");?>>Disabled</option>-->
	<option value='Daily' <?php echo is_selected($frequency,"Daily");?>>Daily</option>
	<option value='Weekly' <?php echo is_selected($frequency,"Weekly");?>>Weekly</option>
	<option value='Monthly' <?php echo is_selected($frequency,"Monthly");?>>Monthly</option>
	</select>	
	</td>
	</tr>
	<tr id="time-div">
	<td valign="top"><label>Time:</label></td><td>
	<select name="hour">
<?php
	for($x=1;$x<=12;$x++){
		$nstr=sprintf("%02d",$x);
		echo "<option value='".$nstr."' ".is_selected($hour,$nstr).">".$nstr."</option>";
		}
?>
	</select>:<select name="minute">
<?php
	for($x=0;$x<60;$x++){
		$nstr=sprintf("%02d",$x);
		echo "<option value='".$nstr."' ".is_selected($minute,$nstr).">".$nstr."</option>";
		}
?>
	</select>
	<select name="ampm">
	<option value="AM" <?php echo is_selected($ampm,"AM");?>>AM</option>
	<option value="PM" <?php echo is_selected($ampm,"PM");?>>PM</option>
	</select>
	</td>
	</tr>
	<tr id="dayofweek-div">
	<td><label>Weekday:</label> </td>
	<td>
	<select name='dayofweek'>
<?php
	$days = array(       
		0 => 'Sunday',
		1 => 'Monday',
		2 => 'Tuesday',
		3 => 'Wednesday',
		4 => 'Thursday',
		5 => 'Friday',
		6 => 'Saturday',
		);
	foreach($days as $did => $day){
		echo "<option value='".$did."' ".is_selected($dayofweek,$did).">".$day."</option>";
		}
?>
	</select>
	</td>
	</tr>
	<tr id="dayofmonth-div">
	<td valign="top"><label>Day of Month:</label> </td>
	<td>
	<select name='dayofmonth'>
<?php
	for($x=1;$x<=31;$x++){
		echo "<option value='".$x."' ".is_selected($dayofmonth,$x).">".$x."</option>";
		}
?>
	</select>
	</td>
	</tr>
	</table>
	<br>

	Specify the schedule you would like this job to be run.<br><br>

	</td>
	</tr>
	
	<script type='text/javascript'>
	$(document).ready(function() {
		showTimeOpts();
	});
	function showTimeOpts()
	{
	  var opt = $('#select_frequency').val();
	  //hide all options and then decide what to show
	  $('#time-div').hide();
	  $('#dayofweek-div').hide();
	  $('#dayofmonth-div').hide();
	  switch(opt)
	  {
		   case 'Daily':
			   $('#time-div').show('fast');
			   break;
		   case 'Weekly':
			   $('#time-div').show('fast');
			   $('#dayofweek-div').show('fast');
			   break;
		   case 'Monthly':
			   $('#time-div').show('fast');
			   $('#dayofmonth-div').show('fast');
			   break;
		   default:
			   break;
	   }
	}
	</script>	
	
<?php
		}
	else{
?>	
	<input type="hidden" name="frequency" value="Once">
<?php
		}
?>
	<script type='text/javascript'>
	$(document).ready(function() {
		$('#advopts1').hide();
		//$('#advopts2').hide();
		$('#advancedoptsbutton').click(function(){
			$('#advopts1').show();
			 $('#advancedoptsbutton').hide();
			//show('fast');
			});
	});
	</script>	
	
	<tr id="advancedoptsbutton">
	<td colspan="2"><a href="#">Show Advanced Options +</a></td>
	</tr>
	
	<tr id="advopts1">
	<td valign="top">
	<label>OS Detection:</label><br class="nobr" />
	</td>
	<td>
	<select name="os_detection">
	<option value="off" <?php echo is_selected($os_detection,"off");?>>Off</option>
	<option value="on" <?php echo is_selected($os_detection,"on");?>>On</option>
	</select>
	<br class="nobr" />
	Attempt to detect the operating system of each host.<br><b>Note:</b>OS detection may cause the scan to take longer to complete and may not be 100% accurate.<br><br>
	</td>
	</tr>

	<!--
	<tr id="advopts2">
	<td valign="top">
	<label>Topology Detection:</label><br class="nobr" />
	</td>
	<td>
	<select name="topology_detection">
	<option value="off" <?php echo is_selected($topology_detection,"off");?>>Off</option>
	<option value="on" <?php echo is_selected($topology_detection,"on");?>>On</option>
	</select>
	<br class="nobr" />
	Attempt to detect the topology of discovered host.<br><b>Note:</b>Topology detection may cause the scan to take longer to complete and may not be 100% accurate.<br><br>
	</td>
	</tr>

	//-->
	<input type="hidden" name="topology_detection" value="on">
	
	
	</table>
	
	<div id="formButtons">
	<input type="submit" class="submitbutton" name="updateButton" value="<?php echo $lstr['SubmitButton'];?>" id="updateButton">
	<input type="submit" class="submitbutton" name="cancelButton" value="<?php echo $lstr['CancelButton'];?>" id="cancelButton">
	</div>
	
	<!--</fieldset>-->
	
	</form>	
<?php
		
	// closes the HTML page
	do_page_end(true);
	
	exit();
	}
	
function display_install_error($installed,$prereqs,$missing_components){

	// start the HTML page
	do_page_start(array("page_title"=>"Installation Problem"),true);
?>
	<h1>Installation Problem</h1>

	<p>
	An installation errror was detected.  The following steps must be completed before using this component:
	</p>
	<ul>
<?php
	if($installed==false){
?>
	<li><b>Run the setup script.</b>  To do this, login to the Nagios XI server as the root user and issue the following commands:<br><br>
	<i>chmod +x /usr/local/nagiosxi/html/includes/components/autodiscovery/setup.sh</i><br>
	<i>/usr/local/nagiosxi/html/includes/components/autodiscovery/setup.sh</i></li>
<?php
		}
	if($prereqs==false){
?>
	<li><b>Make sure pre-requisite programs are installed.</b>  The following programs must be installed on your Nagios XI server:
	<ul>
	<?php echo $missing_components;?>
	</ul>
	</li>
<?php
		}
?>
	</ul>
<?php
		
	// closes the HTML page
	do_page_end(true);
	
	exit();
	}
	

<?php
// XI Recurring Scheduled Downtime
//
// Copyright (c) 2010 Nagios Enterprises, LLC.  All rights reserved.
// Written by Jeremy Yunis
//  
// $Id: downtime.php 127 2010-06-05 17:50:15Z egalstad $
//
// jyunis : 2010-05-17 : php front-end for the downtime-3.0 nagios add-in

include_once(dirname(__FILE__).'/../componenthelper.inc.php');

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


// jyunis : 2010-05-17 : a few helper functions for the downtime config
// FIXME: move these to a more appropriate place

// FIXME: is there a better place to put global defines like this?
define('RECURRINGDOWNTIME_CFG', '/usr/local/nagios/etc/recurringdowntime.cfg');

error_reporting(E_ERROR);

/*
 * Convert the string form of the recurring downtime config to a PHP array
 */
 // FIXME: make a regex that will pull out
 // *just* what's between the { and } in schedule.cfg.
function recurringdowntime_cfg_to_array($str_cfg) {
        $schedules = preg_split('/define schedule \{([\s]+)/', $str_cfg);
        $ret = array();
        if(count($schedules) > 0) {
                foreach($schedules as $k=>$schedule) {
                        $lines = explode("\n", $schedule);
                        $schedule_arr = array();
                        foreach($lines as $k=>$line) {
                                if($line == "" || $line == "}") {
                                        continue;
                                }
                                $line_arr = array();
                                foreach(explode("\t", trim($line)) as $k=>$val) {
                                        if($val != "") {
                                                $line_arr[] = trim($val);
                                        }
                                }
                                if($line_arr[0] == "sid") {
                                        $sid = $line_arr[1];
                                } else {
					$val = isset($line_arr[1]) ? $line_arr[1] : "";
                                        $schedule_arr[$line_arr[0]] = $val;
                                }
                        }
                        if(count($schedule_arr) > 0) {
                                $ret[$sid] = $schedule_arr;
                        }
                }
        }
        return $ret;
}

/*
 * convert a PHP array containing recurring downtime config to a string appropriate for
 * downtime's schedule.cfg
 */
function recurringdowntime_array_to_cfg($arr) {
        if(count($arr) == 0) {
                return "";
        }
        $cfg_str = "";
        foreach($arr as $sid=>$schedule) {
                if(count($schedule) == 0) {
                        continue;
                }
                $cfg_str .= "define schedule {\n";
                $cfg_str .= "\tsid\t\t$sid\n";
                foreach($schedule as $var => $val) {
                        $cfg_str .= "\t$var\t\t$val\n";
                }
                $cfg_str .= "}\n\n";
        }
        return $cfg_str;
}

/*
 * make sure cfg file exists, and if not, create it.
 */
function recurringdowntime_check_cfg() {
	if(!file_exists(RECURRINGDOWNTIME_CFG)) {
		$fh = @fopen(RECURRINGDOWNTIME_CFG, "w+");
		fclose($fh);
	}
}

/*
 * Write the configuration to disk
 */
function recurringdowntime_write_cfg($cfg) {
        if(is_array($cfg)) {
                $cfg_str = recurringdowntime_array_to_cfg($cfg);
        } else {
                $cfg_str = $cfg;
        }
	recurringdowntime_check_cfg();	
        $fh = fopen(RECURRINGDOWNTIME_CFG, "w+") or die("Error: Could not open downtime config file '".RECURRINGDOWNTIME_CFG."' for writing.");
        fwrite($fh,$cfg_str);
        fclose($fh);
        return true;
}

/*
 * Get config from cfg file
 */
function recurringdowntime_get_cfg() {
	recurringdowntime_check_cfg();
	$fh = fopen(RECURRINGDOWNTIME_CFG, "r") or die("Error: Could not open downtime config file '".RECURRINGDOWNTIME_CFG."' for reading.");
	$cfg = fread($fh, filesize(RECURRINGDOWNTIME_CFG));
	fclose($fh);
	return recurringdowntime_cfg_to_array($cfg);
}

/*
 * Get downtime schedule for specified host
 */
function recurringdowntime_get_host_cfg($host=false) {
        $cfg = recurringdowntime_get_cfg();
        $ret = array();
        foreach($cfg as $sid => $schedule) {
		if($schedule["schedule_type"] == "hostgroup") {
			continue;
		}
		if($schedule["schedule_type"] == "service" &&  !is_authorized_for_service(0, 
			$schedule["host_name"], $schedule["service_description"])) {
				continue;
		}
		if($host && !(strtolower($schedule["host_name"]) == strtolower($host))) {
			continue;
		}
		if(is_authorized_for_host(0, $schedule["host_name"])) {
	                $ret[$sid] = $schedule;
		}
	}
        return $ret;
}

/*
 * Get downtime schedule for specified hostgroup
 */
function recurringdowntime_get_hostgroup_cfg($hostgroup=false) {
        $cfg = recurringdowntime_get_cfg();
        $ret = array();
        foreach($cfg as $sid => $schedule) {
		if($schedule["schedule_type"] != "hostgroup") {
			continue;
		}
		if($hostgroup && !(strtolower($schedule["hostgroup_name"]) == strtolower($hostgroup))) {
			continue;
		}
		if(is_authorized_for_hostgroup(0, $schedule["hostgroup_name"])) {
	                $ret[$sid] = $schedule;
		}
	}
        return $ret;
}

/*
 * Get downtime schedule for specified servicegroup
 */
function recurringdowntime_get_servicegroup_cfg($servicegroup=false) {
        $cfg = recurringdowntime_get_cfg();
        $ret = array();
        foreach($cfg as $sid => $schedule) {
		if($schedule["schedule_type"] != "servicegroup") {
			continue;
		}
		if($servicegroup && !(strtolower($schedule["servicegroup_name"]) == strtolower($servicegroup))) {
			continue;
		}
		if(is_authorized_for_servicegroup(0, $schedule["servicegroup_name"])) {
	                $ret[$sid] = $schedule;
		}
	}
        return $ret;
}

/*
 * Generate random-ish sid for new entries
 */
function recurringdowntime_generate_sid() {
	return md5(uniqid(microtime()));
}


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

route_request();

function route_request(){

	$mode=grab_request_var("mode","");
	
	switch($mode){
		case "add":
			recurringdowntime_add_downtime();
			break;
		case "edit":
			recurringdowntime_add_downtime($edit=true);
			break;
		case "delete":
			recurringdowntime_delete_downtime();
			break;
		default:
			recurringdowntime_show_downtime();
			break;
		}

	}

//
// begin function recurringdowntime_delete_downtime()
//
function recurringdowntime_delete_downtime() {
	global $request;
	
	// check session
	check_nagios_session_protector();

	if(!$request["sid"]) {
		header("Location:".$_SERVER["HTTP_REFERER"]);
		exit;
	}

	$cfg = recurringdowntime_get_cfg();
	$cfg_to_delete = $cfg[$request["sid"]];
	if($cfg_to_delete["schedule_type"] == "hostgroup") {
		if(!is_authorized_for_hostgroup(0, $cfg_to_delete["hostgroup_name"])) {
			header("Location:".$_SERVER["HTTP_REFERER"]);
			exit;
		}
	} elseif($cfg_to_delete["schedule_type"] == "servicegroup") {
		if(!is_authorized_for_servicegroup(0, $cfg_to_delete["servicegroup_name"])) {
			header("Location:".$_SERVER["HTTP_REFERER"]);
			exit;
		}
	} else {
		if(!is_authorized_for_host(0, $cfg_to_delete["host_name"])) {
			header("Location:".$_SERVER["HTTP_REFERER"]);
			exit;
		}
		if($cfg_to_delete["schedule_type"] == "service") {
			if(!is_authorized_for_service(0, $cfg_to_delete["host_name"], $cfg_to_delete["service_description"])) {
				header("Location:".$_SERVER["HTTP_REFERER"]);
				exit;
			}
		}
	}
	unset($cfg[$request["sid"]]);
	recurringdowntime_write_cfg($cfg);
	header("Location:".$_SERVER["HTTP_REFERER"]);
	exit;
}
//
// end function recurringdowntime_delete_downtime()
//

//
// begin function recurringdowntime_add_downtime()
//
function recurringdowntime_add_downtime($edit=false) {
	global $request;

	// check session
	check_nagios_session_protector();

	if($edit && !$request["sid"]) {
		$edit=false;
	}

	if($edit) {
		$arr_cfg = recurringdowntime_get_cfg();
		$formvars = $arr_cfg[$request["sid"]];
		$days = array("mon", "tue", "wed", "thu", "fri", "sat", "sun");
		$selected_days = split(",", $formvars["days_of_week"]);
		unset($formvars["days_of_week"]);
		for($i=0; $i<7; $i++) {
			if(in_array($days[$i], $selected_days)) {
				$formvars["days_of_week"][$i] = "on";
			}
		}
			
		if(count($formvars) == 0) {
			echo "<strong>The requested schedule id (sid) is not valid.</strong>";
			exit;
		}
		if($arr_cfg[$request["sid"]]["schedule_type"] == "hostgroup") {
			$form_mode = "hostgroup";
		} elseif($arr_cfg[$request["sid"]]["schedule_type"] == "servicegroup") {
			$form_mode = "servicegroup";
		} else {
			$form_mode = "host";
		}
	} else {
		$form_mode = $request["type"];
		// host or host_name should work
		$formvars["host_name"]=grab_request_var("host_name",grab_request_var("host",""));
		//$formvars["host_name"] = isset($request["host_name"]) ? $request["host_name"] : "";
		$formvars["service_description"]=grab_request_var("service_description",grab_request_var("service",""));
		$formvars["hostgroup_name"] = isset($request["hostgroup_name"]) ? $request["hostgroup_name"] : "";
		$formvars["servicegroup_name"] = isset($request["servicegroup_name"]) ? $request["servicegroup_name"] : "";
		
		$formvars["duration"]="60";
	}

	$errors = array();

	if($_SERVER["REQUEST_METHOD"] == "POST") {
		// handle form
		if($form_mode == "host") {
			if(empty($request["host_name"]))
				$errors[] = "Please enter a host name.";
			else if(!is_authorized_for_host(0, $request["host_name"])) {
				$errors[] = "The host you specified is not valid for your user account.";
			}
			if($request["service_description"]) {
				if(!is_authorized_for_service(0, $request["host_name"], $request["service_description"])) {
					$errors[] = "The service you specified is not valid for your user account.";
				}
			}
		} elseif($form_mode == "servicegroup") {
			if(empty($request["servicegroup_name"]))
				$errors[]="Please enter a servicegroup name.";
			else if(!is_authorized_for_servicegroup(0, $request["servicegroup_name"])) {
				$errors[] = "The servicegroup you specified is not valid for your user account.";
			}
		} else {
			if(empty($request["hostgroup_name"]))
				$errors[]="Please enter a hostgroup name.";
			else if(!is_authorized_for_hostgroup(0, $request["hostgroup_name"])) {
				$errors[] = "The hostgroup you specified is not valid for your user account.";
			}
		}
		$required = array(
			"time"	=> "Please enter the start time for this downtime event.",
			"duration" => "Please enter the duration of this downtime event."
		);
		foreach($required as $field=>$errval) {
			if(empty($request[$field])) {
				$errors[] = $errval;
			}
		}
			
		if(!empty($request["time"])) {
			$exp = '/^(20|21|22|23|[01]\d|\d)(([:][0-5]\d){1,2})$/';
			if(!preg_match($exp, $request["time"])) {
				$errors[] = "Please enter a valid time in 24-hour format, e.g. 21:00.";
			}
		}
		if(!empty($request["duration"])) {
			if(!is_numeric($request["duration"])) {
				$errors[] = "Please enter a valid duration time in seconds, e.g. 120.";
			}
		}
		if(!empty($request["days_of_month"])) {
			$days=explode(",",$request["days_of_month"]);
			$dayerror=false;
			foreach($days as $day){
				if(!is_numeric($day))
					$dayerror=true;
				}
			if($dayerror==true)
				$errors[]="Invalid days of the month.";
		}
		if(!count($errors) > 0) {
			$str_days_of_week = "";
			$str_days_of_month = $request["days_of_month"];
			$i=0;
			foreach($request["days_of_week"] as $k => $day) {
				if($i++ > 0) {
					$str_days_of_week .= ",";
				}
				$str_days_of_week .= $day;
			}
			$new_cfg = array(
				"user" => $_SESSION["username"],
				"comment" => $request["comment"],
				"time" => $request["time"],
				"duration" => $request["duration"],
				"days_of_week" => $str_days_of_week,
				"days_of_month" => $str_days_of_month
			);

			if($edit) {
				$sid = $request["sid"];
			} else {
				$sid = recurringdowntime_generate_sid();
			}

			if($form_mode == "host") {
				if($request["service_description"]) {
					$new_cfg["schedule_type"] = "service";
					$new_cfg["service_description"] = $request["service_description"];
				} else {
					$new_cfg["schedule_type"] = "host";
				}
				$new_cfg["host_name"] = $request["host_name"];
			} elseif($form_mode == "servicegroup") {
				$new_cfg["schedule_type"] = "servicegroup";
				$new_cfg["servicegroup_name"] = $request["servicegroup_name"];
			} elseif($form_mode == "hostgroup") {
				$new_cfg["schedule_type"] = "hostgroup";
				$new_cfg["hostgroup_name"] = $request["hostgroup_name"];
			}

			$cfg = recurringdowntime_get_cfg();
			$cfg[$sid] = $new_cfg;
			recurringdowntime_write_cfg($cfg);
			if($request["return"]) {
				$go = $request["return"];
			} else {
				$go = $_SERVER["PHP_SELF"];
			}
			//echo "LOCATION: $go";
			//exit;
			header("Location: $go");
			exit;
		} else {
			$formvars = $request;
		}
	}

	do_page_start(array("page_title" => "Add Recurring Downtime Schedule"), true);
?>

	<h1><?php if($edit) { echo "Edit Recurring Downtime Schedule"; } else { echo "Add Recurring Downtime Schedule"; } ?></h1>
	<div><strong>Note:</strong> A new downtime schedule will be added to the monitoring engine one hour before it is set to activate, according to the parameters specified below.<br/></div>
	<?php if(count($errors) > 0) { ?>
	<div id="message">
		<ul class="errorMessage">
		<?php foreach($errors as $k=>$msg) { ?>
			<li><?php echo $msg; ?></li>
		<?php } ?>
	</div>
	<?php } ?>
	<form action="<?php echo htmlentities($_SERVER["REQUEST_URI"]); ?>" method="post">
	<input type="hidden" name="return" value="<?php echo encode_form_val($request["return"]);?>">
	<?php echo get_nagios_session_protector();?>
	
		<div class="sectionTitle">Schedule Settings</div>
			
			<p>
			<table class="editDataSourceTable">
				<tbody>
					<?php if($form_mode == "host") { ?>
					<tr>
						<td valign="top">
							<label for="hostBox">Host:</label>
							<br class="nobr" />
						</td>
						<td>
						<?php if($edit || (isset($_GET["host_name"]) || isset($_GET["host"]))) { ?>
							<input disabled="disabled" id="hostBox" class="textfield" type="text" name="host_name" value="<?php echo encode_form_val($formvars["host_name"]); ?>" size="25" />
							<input type="hidden" name="host_name" value="<?php echo encode_form_val($formvars["host_name"]); ?>" />
						<?php } else { ?>
							<input id="hostBox" class="textfield" type="text" name="host_name" value="<?php echo encode_form_val($formvars["host_name"]); ?>" size="25" />
							<script type="text/javascript">
							$(document).ready(function(){
								$("#hostBox").each(function(){
									$(this).autocomplete(suggest_url,{maxItemsToShow: 7, minChars: 1, extraParams: { type:"hosts"} });
									
									$(this).blur(function(){									
											var hostname=$("#hostBox").val();
										});
									$(this).change(function(){									
											var hostname=$("#hostBox").val();
										});
									
									});
							});
							</script>
						<?php } ?>
						<br>The host associated with this schedule.<br><br>
						</td>
					</tr>
					<tr>
						<td valign="top">
							<label for="serviceBox">Service:</label>
							<br class="nobr" />
						</td>
						<td>
							<input id="serviceBox" class="textfield" type="text" name="service_description" value="<?php echo encode_form_val($formvars["service_description"]); ?>" size="25" />
							<br>Optional service associated with this schedule.  
							<?php
							if(is_admin()){
							?>
							A wildcard can be used to specify multiple matches (e.g. 'HTTP*').
							<?php
								}
							?>
							<br><br>
							<script type="text/javascript">
							$(document).ready(function(){
									$("#serviceBox").each(function(){
										$(this).focus(function(){
											var hostname=$("#hostBox").val();
											// TODO - we should destroy the old autocomplete here (but the function doesn't exist) , because multiple calls get made if the user goes back and changes the host name...
											$(this).autocomplete(suggest_url,{maxItemsToShow: 7, minChars: 1, extraParams: { type:"services", host: hostname } });
											});
										});
							});
							</script>							
							
						</td>
					</tr>
					<?php } elseif($form_mode == "servicegroup") { ?>
					<tr>
						<td valign="top">
							<label for="servicegroupBox">Servicegroup:</label>
						</td>
						<td>
						<?php if($edit || isset($_GET["servicegroup_name"])) { ?>
							<input disabled="disabled" id="servicegroupBox" class="textfield" type="text" name="servicegroup_name" value="<?php echo encode_form_val($formvars["servicegroup_name"]); ?>" size="25" />
							<input type="hidden" name="servicegroup_name" value="<?php echo encode_form_val($formvars["servicegroup_name"]); ?>" />
						<?php } else { ?>
							<input id="servicegroupBox" class="textfield" type="text" name="servicegroup_name" value="<?php echo encode_form_val($formvars["servicegroup_name"]); ?>" size="25" />
							<br>The servicegroup associated with this schedule.<br><br>
							<script type="text/javascript">
							$(document).ready(function(){
								$("#servicegroupBox").each(function(){
									$(this).autocomplete(suggest_url,{maxItemsToShow: 7, minChars: 1, extraParams: { type:"servicegroups" } });
									});
							});
							</script>
						<?php } ?>
					</tr>
					<?php } else { ?>
					<tr>
						<td valign="top">
							<label for="hostgroupBox">Hostgroup:</label>
						</td>
						<td>
						<?php if($edit || isset($_GET["hostgroup_name"])) { ?>
							<input disabled="disabled" id="hostgroupBox" class="textfield" type="text" name="hostgroup_name" value="<?php echo encode_form_val($formvars["hostgroup_name"]); ?>" size="25" />
							<input type="hidden" name="hostgroup_name" value="<?php echo encode_form_val($formvars["hostgroup_name"]); ?>" />
						<?php } else { ?>
							<input id="hostgroupBox" class="textfield" type="text" name="hostgroup_name" value="<?php echo encode_form_val($formvars["hostgroup_name"]); ?>" size="25" />
							<br>The hostgroup associated with this schedule.<br><br>
							<script type="text/javascript">
							$(document).ready(function(){
								$("#hostgroupBox").each(function(){
									$(this).autocomplete(suggest_url,{maxItemsToShow: 7, minChars: 1, extraParams: { type:"hostgroups"} });
									});
							});
							</script>							
						<?php } ?>
					</tr>
					<?php } ?>
					<tr>
						<td valign="top">
							<label for="commentBox">Comment:</label>
						</td>
						<td>
							<input id="commentBox" class="textfield" type="text" name="comment" value="<?php echo encode_form_val($formvars["comment"]); ?>" size="60" /> <br>
							An optional comment associated with this schedule.<br><br>
						</td>
					</tr>
					<tr>
						<td valign="top">
							<label for="timeBox">Start Time:</label>
						</td>
						<td>
							<input id="timeBox" class="textfield" type="text" name="time" value="<?php echo encode_form_val($formvars["time"]); ?>" size="6" /> <br>
							Time of day the downtime should start in 24-hr format (e.g. 13:30).<br><br>
						</td>
					</tr>
					
					<script type="text/javascript">
					/*
			$(document).ready(function(){
				$('#timeBox').timepickr();
				});		
					*/				
					</script>
					
					<tr>
						<td valign="top">
							<label for="durationBox">Duration:</label>
						</td>
						<td>
							<input id="durationBox" class="textfield" type="text" name="duration" value="<?php echo encode_form_val($formvars["duration"]); ?>" size=6 /> <br>
							Duration of the scheduled downtime in minutes.<br><br>
						</td>
					</tr>
					<tr>
						<td valign="top">
							<label for="weekdaysBox">Valid Weekdays:</label>
						</td>
						<td>
							<input <?php if($formvars["days_of_week"][0]) { echo "checked"; } ?> type="checkbox" class="checkfield" name="days_of_week[0]" value="mon" />Mon &nbsp;
							<input <?php if($formvars["days_of_week"][1]) { echo "checked"; } ?> type="checkbox" class="checkfield" name="days_of_week[1]" value="tue" />Tue &nbsp;
							<input <?php if($formvars["days_of_week"][2]) { echo "checked"; } ?> type="checkbox" class="checkfield" name="days_of_week[2]" value="wed" />Wed &nbsp;
							<input <?php if($formvars["days_of_week"][3]) { echo "checked"; } ?> type="checkbox" class="checkfield" name="days_of_week[3]" value="thu" />Thu &nbsp;
							<input <?php if($formvars["days_of_week"][4]) { echo "checked"; } ?> type="checkbox" class="checkfield" name="days_of_week[4]" value="fri" />Fri &nbsp;
							<input <?php if($formvars["days_of_week"][5]) { echo "checked"; } ?> type="checkbox" class="checkfield" name="days_of_week[5]" value="sat" />Sat &nbsp;
							<input <?php if($formvars["days_of_week"][6]) { echo "checked"; } ?> type="checkbox" class="checkfield" name="days_of_week[6]" value="sun" />Sun &nbsp;
							<br/>
							Days of the week this schedule is valid. Defaults to every weekday if none selected.<br><br>
						</td>
					</tr>
					<tr>
						<td valign="top">
							<label for="monthdaysBox">Valid Days of Month:</label>
						</td>
						<td>
							<input id="monthdaysBox" class="textfield" type="text" name="days_of_month" value="<?php echo encode_form_val($formvars["days_of_month"]); ?>" size="15" />
							<br>Comma-separated list of days of month this schedule is valid.  Defaults to every day if empty.  If you specify weekdays <em>and</em> days of the month, then <em>both</em> must match for the downtime to be scheduled.<br><br>
						</td>
					</tr>
					<tr>
						<td></td>
						<td align="left">
							<input type="submit" name="submit" value="Submit" />&nbsp;
							<input type="button" name="cancel" value="Cancel" onClick="javascript:document.location.href='<?php echo $request["return"]; ?>'" />
						</td>
					</tr>
				</table>
							
<?php   
        do_page_end(true);
}

//
// end function recurringdowntime_add_downtime()
//
	
	
//
// begin function show_downtime()
//
function recurringdowntime_show_downtime(){
	global $request;
	global $lstr;
	
	do_page_start(array("page_title"=>$lstr['RecurringDowntimePageTitle']),true);

	if(isset($request["host"])) {
		$host_tbl_header = "Recurring Downtime for Host " . $request["host"];
		if(is_authorized_for_host(0, $request["host"])) {
			$host_data = recurringdowntime_get_host_cfg($request["host"]);
		}
	} elseif(isset($request["hostgroup"])) {
		$hostgroup_tbl_header = "Recurring Downtime for Hostgroup ". $request["hostgroup"];
		if(is_authorized_for_hostgroup(0, $request["hostgroup"])) {
			$hostgroup_data = recurringdowntime_get_hostgroup_cfg($request["hostgroup"]);
		}
	} elseif(isset($request["servicegroup"])) {
		$servicegroup_tbl_header = "Recurring Downtime for Servicegroup ". $request["servicegroup"];
		if(is_authorized_for_servicegroup(0, $request["servicegroup"])) {
			$servicegroup_data = recurringdowntime_get_servicegroup_cfg($request["servicegroup"]);
		}
	}

	if(!isset($request["host"]) && !isset($request["hostgroup"]) && !isset($request["servicegroup"])) {
		/*
		$host_tbl_header = "Recurring Downtime for All Hosts";
		$hostgroup_tbl_header = "Recurring Downtime for All Hostgroups";
		$servicegroup_tbl_header = "Recurring Downtime for All Servicegroups";
		*/
		$host_tbl_header = "Host/Service Schedules";
		$hostgroup_tbl_header = "Hostgroup Schedules";
		$servicegroup_tbl_header = "Servicegroup Schedules";
		$host_data = recurringdowntime_get_host_cfg($host=false);
		$hostgroup_data = recurringdowntime_get_hostgroup_cfg($hostgroup=false);
		$servicegroup_data = recurringdowntime_get_servicegroup_cfg($servicegroup=false);
		$showall = true;
	}

?>
	<h1><?php echo $lstr['RecurringDowntimePageHeader'];?></h1>
	
	<?php echo $lstr['RecurringDowntimePageNotes'];?>

<?php
	if(!isset($request["host"]) && !isset($request["hostgroup"]) && !isset($request["servicegroup"])) {
?>
	<!--
	<div><a href="<?php echo htmlentities($_SERVER["PHP_SELF"]); ?>?mode=add&type=host&return=<?php echo urlencode($_SERVER["REQUEST_URI"]); ?>">+ Add Host Downtime Schedule</a></div>
	<div><a href="<?php echo htmlentities($_SERVER["PHP_SELF"]); ?>?mode=add&type=hostgroup&return=<?php echo urlencode($_SERVER["REQUEST_URI"]); ?>">+ Add Hostgroup Downtime Schedule</a></div>
	<div><a href="<?php echo htmlentities($_SERVER["PHP_SELF"]); ?>?mode=add&type=servicegroup&return=<?php echo urlencode($_SERVER["REQUEST_URI"]); ?>">+ Add Servicegroup Downtime Schedule</a></div>
	//-->
	<p>
<?php } ?>

<script type="text/javascript">
	function do_delete_sid(sid) {
		input = confirm('Are you sure you wish to delete this downtime schedule?');
		if(input == true) {
			window.location.href='<?php echo htmlentities($_SERVER["PHP_SELF"]); ?>?mode=delete&sid='+sid+'&nsp=<?php echo get_nagios_session_protector_id();?>';
		}
	}
</script>

<?php
	if($showall){
?>
  <script type="text/javascript">
  $(document).ready(function() {
    $("#tabs").tabs();
  });
  </script>
  
  	<div id="tabs">
	<ul>
	<li><a href="#host-tab">Hosts/Services</a></li>
	<li><a href="#hostgroup-tab">Hostgroups</a></li>
	<li><a href="#servicegroup-tab">Servicegroups</a></li>
	</ul>
<?php
		}
?>

<?php if($_GET["host"] || $showall) { 

	// hosts tab
	if($showall)
		echo "<div id='host-tab'>";
?>

<div style="margin-top:20px;"></div>

<div class="infotable_title" style="float:left"><?php echo $host_tbl_header; ?></div>
<?php if($request["host"]) { ?>
	<div style="clear: left; margin: 0 0 10px 0;"><a href="<?php echo htmlentities($_SERVER["PHP_SELF"]); ?>?mode=add&type=host&host_name=<?php echo $request["host"]; ?>&return=<?php echo urlencode($_SERVER["REQUEST_URI"]); ?>%23host-tab&nsp=<?php echo get_nagios_session_protector_id();?>"><img src="<?php echo theme_image("add.png");?>"> Add schedule for this host</a></div>
<?php 
	} 
	else{
?>
<div style="clear: left; margin: 0 0 10px 0;"><a href="<?php echo htmlentities($_SERVER["PHP_SELF"]); ?>?mode=add&type=host&return=<?php echo urlencode($_SERVER["REQUEST_URI"]);?>%23host-tab&nsp=<?php echo get_nagios_session_protector_id();?>"><img src="<?php echo theme_image("add.png");?>"> Add Schedule</a></div>
<?php
		}
?>
	<table class="tablesorter" style="width:100%">
		<thead>
		<tr>
			<th>Host</th>
			<th>Type</th>
			<th>Service</th>
			<th>Comment</th>
			<th>Start Time</th>
			<th>Duration</th>
			<th>Weekdays</th>
			<th>Days in Month</th>
			<th>Actions</th>
		</tr>
		</thead>
		<tbody>
<?php
if($host_data) {
	$i=0;
	foreach($host_data as $sid => $schedule) {
		if($i++ % 2 == 0) {
			$cls = "odd";
		} else {
			$cls = "even";
		}
	
?>
		<tr class="<?php echo $cls; ?>">
			<td><?php echo $schedule["host_name"]; ?></td>
			<td><?php echo $schedule["schedule_type"]; ?></td>
			<td><?php if($schedule["schedule_type"] == "service") { echo $schedule["service_description"]; } else { echo "N/A"; } ?></td>
			<td><?php echo $schedule["comment"]; ?></td>
			<td><?php echo $schedule["time"]; ?></td>
			<td><?php echo $schedule["duration"]; ?></td>
			<td><?php if($schedule["days_of_week"]) { echo $schedule["days_of_week"]; } else { echo "All"; } ?></td>
			<td><?php if($schedule["days_of_month"]) { echo $schedule["days_of_month"]; } else { echo "All"; } ?></td>
			<td style="text-align:center;width:60px"><a href="<?php echo htmlentities($_SERVER["PHP_SELF"])."?mode=edit&sid=".$sid."&return=".urlencode($_SERVER["REQUEST_URI"]); ?>%23host-tab&nsp=<?php echo get_nagios_session_protector_id();?>" title="Edit Schedule">
				<img src="/nagiosxi/images/b_edituser.png" alt="Edit Schedule" /></a>
			    <a onClick="javascript:return do_delete_sid('<?php echo $sid; ?>');" href="javascript:void(0);" title="Delete Schedule">
				<img src="/nagiosxi/images/b_deleteuser.png" alt="Delete Schedule" /></a>
			</td>
		</tr>
<?php 	} // end foreach ?>

<?php } else { ?>
	<tr>
		<td colspan="9"><div style="padding:4px"><em>There are currently no host/service recurring downtime events defined.</em></div></td>
	</tr>
<?php } // end if host_data ?>
	</table>

<?php if($showall) echo "</div>"; // end host tab?>

<?php } // end if host or showall ?>

<div style="margin-top:20px;"></div>

<?php if($_GET["hostgroup"] || $showall) { 

	// hostgroups tab
	if($showall)
		echo "<div id='hostgroup-tab'>";
?>

<div class="infotable_title" style="float:left"><?php echo $hostgroup_tbl_header; ?></div>
<?php if($request["hostgroup"]) { ?>
	<div style="clear: left; margin: 0 0 10px 0;"><a href="<?php echo htmlentities($_SERVER["PHP_SELF"]); ?>?mode=add&type=hostgroup&hostgroup_name=<?php echo $request["hostgroup"]; ?>&return=<?php echo urlencode($_SERVER["REQUEST_URI"]); ?>%23hostgroup-tab&nsp=<?php echo get_nagios_session_protector_id();?>"><img src="<?php echo theme_image("add.png");?>"> Add schedule for this hostgroup</a></div>
<?php 
	}
	else{
?>
	<div style="clear: left; margin: 0 0 10px 0;"><a href="<?php echo htmlentities($_SERVER["PHP_SELF"]); ?>?mode=add&type=hostgroup&return=<?php echo urlencode($_SERVER["REQUEST_URI"]);?>%23hostgroup-tab&nsp=<?php echo get_nagios_session_protector_id();?>"><img src="<?php echo theme_image("add.png");?>"> Add Schedule</a></div>
<?php
		}
?>
	<table class="tablesorter" style="width:100%">
		<thead>
		<tr>
			<th>Hostgroup</th>
			<th>Comment</th>
			<th>Start Time</th>
			<th>Duration</th>
			<th>Weekdays</th>
			<th>Days in Month</th>
			<th>Actions</th>
		</tr>
		</thead>
		<tbody>

<?php

if($hostgroup_data) {
	$i=0;
	foreach($hostgroup_data as $sid => $schedule) {
		if($i++ % 2 == 0) {
			$cls = "odd";
		} else {
			$cls = "even";
		}
	
?>
		<tr class="<?php echo $cls; ?>">
			<td><?php echo $schedule["hostgroup_name"]; ?></td>
			<td><?php echo $schedule["comment"]; ?></td>
			<td><?php echo $schedule["time"]; ?></td>
			<td><?php echo $schedule["duration"]; ?></td>
			<td><?php if($schedule["days_of_week"]) { echo $schedule["days_of_week"]; } else { echo "All"; } ?></td>
			<td><?php if($schedule["days_of_month"]) { echo $schedule["days_of_month"]; } else { echo "All"; } ?></td>
			<td style="text-align:center;width:60px"><a href="<?php echo htmlentities($_SERVER["PHP_SELF"])."?mode=edit&sid=".$sid."&return=".urlencode($_SERVER["REQUEST_URI"]);?>%23hostgroup-tab&nsp=<?php echo get_nagios_session_protector_id();?>" title="Edit Schedule">
				<img src="/nagiosxi/images/b_edituser.png" alt="Edit Schedule" /></a>
			    <a onClick="javascript:return do_delete_sid('<?php echo $sid; ?>');" href="javascript:void(0);" title="Delete Schedule">
				<img src="/nagiosxi/images/b_deleteuser.png" alt="Delete Schedule" /></a>
			</td>
		</tr>
<?php } // end foreach ?>
<?php } else { ?>
		<tr>
			<td colspan="7"><div style="padding:4px"><em>There are currently no hostgroup recurring downtime events defined.</em></div></td>
		</tr>
<?php } // end if hostrgroup_data ?>
	</tbody>
	</table>

<?php if($showall) echo "</div>"; // end hostgroup tab?>

<?php } // end if hostgroup or showall ?>

<div style="margin-top:20px"></div>

<?php
	if($_GET["servicegroup"] || $showall) {
	
	// servicegroups tab
	if($showall)
		echo "<div id='servicegroup-tab'>";
?>
	<div class="infotable_title" style="float:left"><?php echo $servicegroup_tbl_header; ?></div>
<?php if($request["servicegroup"]) { ?>
	<div style="clear: left; margin: 0 0 10px 0;"><a href="<?php echo htmlentities($_SERVER["PHP_SELF"]); ?>?mode=add&type=servicegroup&servicegroup_name=<?php echo $request["servicegroup"];?>&return=<?php echo urlencode($_SERVER["REQUEST_URI"]);?>%23servicegroup-tab&nsp=<?php echo get_nagios_session_protector_id();?>"><img src="<?php echo theme_image("add.png");?>"> Add schedule for this servicegroup</a></div>
<?php 
	} 
	else{
?>
	<div style="clear: left; margin: 0 0 10px 0;"><a href="<?php echo htmlentities($_SERVER["PHP_SELF"]); ?>?mode=add&type=servicegroup&return=<?php echo urlencode($_SERVER["REQUEST_URI"]);?>%23servicegroup-tab&nsp=<?php echo get_nagios_session_protector_id();?>"><img src="<?php echo theme_image("add.png");?>"> Add Schedule</a></div>
<?php
		}
?>
	<table class="tablesorter" style="width:100%">
		<thead>
		<tr>
			<th>Servicegroup</th>
			<th>Comment</th>
			<th>Start Time</th>
			<th>Duration</th>
			<th>Weekdays</th>
			<th>Days in Month</th>
			<th>Actions</th>
		</tr>
		</thead>
		<tbody>
<?php

if($servicegroup_data) {

	$i=0;
	foreach($servicegroup_data as $sid => $schedule) {
		if($i++ % 2 == 0) {
			$cls = "odd";
		} else {
			$cls = "even";
		}
	
?>
		<tr class="<?php echo $cls; ?>">
			<td><?php echo $schedule["servicegroup_name"]; ?></td>
			<td><?php echo $schedule["comment"]; ?></td>
			<td><?php echo $schedule["time"]; ?></td>
			<td><?php echo $schedule["duration"]; ?></td>
			<td><?php if($schedule["days_of_week"]) { echo $schedule["days_of_week"]; } else { echo "All"; } ?></td>
			<td><?php if($schedule["days_of_month"]) { echo $schedule["days_of_month"]; } else { echo "All"; } ?></td>
			<td style="text-align:center;width:60px"><a href="<?php echo htmlentities($_SERVER["PHP_SELF"]);?>?mode=edit&sid=<?php echo $sid;?>&return=<?php echo urlencode($_SERVER["REQUEST_URI"]);?>%23servicegroup-tab&nsp=<?php echo get_nagios_session_protector_id();?>" title="Edit Schedule">
				<img src="/nagiosxi/images/b_edituser.png" alt="Edit Schedule" /></a>
			    <a onClick="javascript:return do_delete_sid('<?php echo $sid; ?>');" href="javascript:void(0);" title="Delete Schedule">
				<img src="/nagiosxi/images/b_deleteuser.png" alt="Delete Schedule" /></a>
			</td>
		</tr>
<?php 	} // end foreach ?>

<?php } else { ?>
		<tr>
			<td colspan="7"><div style="padding:4px"><em>There are currently no servicegroup recurring downtime events defined.</em></div></td>
		</tr>
<?php } // end if servicegroup_data ?>
		

	</tbody>
	</table>
	
<?php if($showall) echo "</div>"; // end servicegroup tab?>

<?php } // end if servicegroup or showall ?>

<?php
	if($showall){ // end of tabs container
?>
	</div>
<?php
		}
?>

<?php
	do_page_end(true);
}
// 
// end function recurringdowntime_show_downtime()
//	

?>

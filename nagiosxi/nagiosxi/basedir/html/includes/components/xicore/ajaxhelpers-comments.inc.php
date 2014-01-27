<?php
// XI Core Ajax Helper Functions
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: ajaxhelpers-comments.inc.php 75 2010-04-01 19:40:08Z egalstad $

include_once(dirname(__FILE__).'/../componenthelper.inc.php');
	

////////////////////////////////////////////////////////////////////////
// COMMENTS AJAX FUNCTIONS
////////////////////////////////////////////////////////////////////////		

function xicore_ajax_get_comments_html($args=null){
	global $lstr;

	// get comments
	$args=array(
		"cmd" => "getcomments",
		);
	$xml=get_backend_xml_data($args);

	$output='';
		
	$output.='<div class="infotable_title">Acknowledgements and Comments</div>';

	if($xml==null || intval($xml->recordcount)==0){
		$output.='No comments or acknowledgements.';
		}
	else{
		
		$output.='
		<table class="standardtable">
		<thead>
		<tr><th>Host</th><th>Service</th><th>Comment</th><th>Action</th></tr>
		</thead>
		<tbody>
		';
			
		$x=0;
		foreach($xml->comment as $c){
		
			if(($x%2)==0)
				$rowclass="even";
			else
				$rowclass="odd";
		
			$objecttype=intval($c->objecttype_id);
			
			$hostname=strval($c->host_name);
			$servicename=strval($c->service_description);
		
			switch(intval($c->entry_type)){
				case COMMENTTYPE_ACKNOWLEDGEMENT:
					$typeimg=theme_image("ack.png");
					break;
				default:
					$typeimg=theme_image("comment.png");
					break;
				}
			$type="<img src='".$typeimg."'>";
			$timestr=get_datetime_string_from_datetime($c->comment_time);
			$author=strval($c->author_name);
			$comment=strval($c->comment_data);
			
			$hoststr="<a href='".get_host_status_detail_link($hostname)."'>".$hostname."</a>";
			$servicestr="<a href='".get_service_status_detail_link($hostname,$servicename)."'>".$servicename."</a>";
			
			$output.='<tr class="'.$rowclass.'"><td valign="top" nowrap>'.$hoststr.'</td><td valign="top" nowrap>'.$servicestr.'</td><td><div style="float: left; margin-right: 5px;">'.$type.'</div><div style="float: left;">By <b>'.$author.'</b> at '.$timestr.'<br>'.$comment.'</div></td>';

			// is user authorized for command?
			if($objecttype==OBJECTTYPE_HOST)
				$auth_command=is_authorized_for_host_command(0,$hostname);
			else
				$auth_command=is_authorized_for_service_command(0,$hostname,$servicename);

			if($auth_command){
				$cmd["command_args"]["cmd"]=($objecttype==OBJECTTYPE_HOST)?NAGIOSCORE_CMD_DEL_HOST_COMMENT:NAGIOSCORE_CMD_DEL_SVC_COMMENT;
				$cmd["command_args"]["comment_id"]=intval($c->internal_id);
				$action="<a href='#' ".get_nagioscore_command_ajax_code($cmd)."><img src='".theme_image("delete.png")."' alt='".$lstr['DeleteAlt']."' title='".$lstr['DeleteAlt']."'></a>";
				$output.='<td>'.$action.'</td>';
				}
			else
				$output.='<td></td>';
			$output.='</tr>';
			
			$x++;
			}
		
		$output.='
		</tbody>
		</table>
		';
		}
		
	
	$output.='
	<div class="ajax_date">Last Updated: '.get_datetime_string(time()).'</div>
	';
	

	return $output;
	}
?>
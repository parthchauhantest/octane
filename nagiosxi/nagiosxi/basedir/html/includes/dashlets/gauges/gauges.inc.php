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
// $Id: gauges.inc.php 9 2010-10-05 20:34:33Z egalstad $


// include the required helper file (distributed with Nagios XI)
include_once(dirname(__FILE__).'/../dashlethelper.inc.php');


// run the initialization function
if(use_2014_features())
    gauges_dashlet_init();

function gauges_dashlet_init(){
	
	$name="gauges";  // CHANGE THIS
	
	$args=array(

		// need a name
		DASHLET_NAME => $name,
		
		// informative information
		DASHLET_VERSION => "1.0",
		DASHLET_DATE => "07-29-2013",
		DASHLET_AUTHOR => "Nagios Enterprises, LLC",
		DASHLET_DESCRIPTION => gettext("Displays gauges."),
		DASHLET_COPYRIGHT => "Copyright (c) 2013 Nagios Enterprises",
		DASHLET_LICENSE => "BSD",
		DASHLET_HOMEPAGE => "http://www.nagios.com",
		
		DASHLET_FUNCTION => "gauges_dashlet_func", // the function name to call
		
		DASHLET_TITLE => gettext("Gauge Dashlet"), // title used in the dashlet
		
		// optional CSS classes
		DASHLET_OUTBOARD_CLASS => "gauges_outboardclass", // used when the dashlet is embedded in a non-dashboard page
		DASHLET_INBOARD_CLASS => "gauges_inboardclass", // used when the dashlet is on a dashboard
		DASHLET_PREVIEW_CLASS => "gauges_previewclass", // used in the "Available Dashlets screen of Nagios XI
		
		//DASHLET_CSS_FILE => "gauges.css", // optional CSS file to include
		DASHLET_JS_FILE => "js/gauge.js", // optional Javascript file to cinlude

		DASHLET_WIDTH => "120", // default size of the dashlet when first placed on the dashboard
		DASHLET_HEIGHT => "180",
		DASHLET_OPACITY => "1", // transparency/opacity of the dashlet (0=invisible,1.0=visible)
		DASHLET_BACKGROUND => "", // background color of the dashlet (optional)

		);
		
	register_dashlet($name,$args); // this tells Nagios XI about the dashlet
    register_callback(CALLBACK_PAGE_HEAD,'gauges_component_head_include'); 
	}

function gauges_component_head_include($cbtype='',$args=null) {
    global $components;
    echo '';
	
}
	
// Dashlet function
// This gets called at various points by Nagios XI.  The $mode argument will be different, depending on what XI is asking of the dashlet
function gauges_dashlet_func($mode=DASHLET_MODE_PREVIEW,$id="",$args=null){

	$output="";
	$imgbase=get_dashlet_url_base("gauges")."/images/"; // the relative URL base for the "images" subfolder for the dashlet

	switch($mode){
	
		// the dashlet is being configured
		// add optioal form arguments (text boxes, dropdown lists, etc.) to capture data here
		case DASHLET_MODE_GETCONFIGHTML:
            define('GAUGES_FORM', true);
		include_once(dirname(__FILE__)."/getdata.php");
            $output="";
            $gaugejson = get_gauge_json();
            $gaugejson_array = json_decode($gaugejson,true);
            $output.=''.gettext('Host').'<br/><select id="gauges_form_name" name="host" onchange="getgaugejson()"><option selected="selected"></option>';
            foreach($gaugejson_array as $host => $services)
                $output.='<option value="'.$host.'">'.$host.'</option>';
            $output.='</select>';
            $output.=''.gettext('Services').'<br/><div id="gauges_services"><select id="gauges_form_services" name="service" onchange="getgaugeservices()"><option selected="selected"></option></select></div>';
            $output.=''.gettext('Datasource').'<br/><div id="gauges_datasource"><select id="gauges_form_ds" name="ds"><option selected="selected"></option></select></div>';
            $output.='';
            $output.='';
            $output.='';
			break;

		// for this example, we display the sample output whether we're on a dashboard or on a normal (non-dashboard) page
		case DASHLET_MODE_OUTBOARD:
		case DASHLET_MODE_INBOARD:

			$output="";
			if(empty($args['ds'])){
                $output .="ERROR: Missing Arguments";
                break;
            }
			$id="gauges_".random_string(6); // a random ID to assign to the <div> tag that wraps the output, so the sample dashlet can appear multiple times on the sample dashboard
			
			// ajax updater args
			$ajaxargs=$args;
			// build args for javascript
			$n=0;
			$jargs="{";
			foreach($ajaxargs as $var => $val){
				if($n>0)
					$jargs.=", ";
				$jargs.="\"$var\" : \"$val\"";
				$n++;
				}
			$jargs.="}";

			// here we output some HTML that contains a <div> that gets updated via ajax every 5 seconds...
			
			$output.='
			<div class="gauges_dashlet" id="'.$id.'">
			<div class="infotable_title">Gauge</div>
			'.get_throbber_html().'			
			</div>

			<script type="text/javascript">
			$(document).ready(function(){
			
				get_'.$id.'_content();
				
				function get_'.$id.'_content(){
					$("#'.$id.'").each(function(){
						var optsarr = {
							"func": "get_gauges_dashlet_html",
							"args": '.$jargs.'
							}
						var opts=array2json(optsarr);
						get_ajax_data_innerHTML("getxicoreajax",opts,true,this);
						});
					}
			});
			</script>
			';
			
			break;
			
		// dashlet is in "preview" mode
		// it is being shown either under the Admin menu, or under the "Available Dashlets" screen
		case DASHLET_MODE_PREVIEW:
			// thumbnail image shown in the preview mode
			$output="<p><img src='".$imgbase."preview.png'></p>";
			break;
		}
		
	return $output;
	}
	
// This is the function that XI calls when the dashlet javascript makes an AJAX call.
// Note how the function name is prepended with 'xicore_ajax_', compared to the function name we used in the javascript code when producing the wrapper <div> tag above
function xicore_ajax_get_gauges_dashlet_html($args=null){

	$imgbase=get_dashlet_url_base("gauges")."/images/"; // the relative URL base for the "images" subfolder for the dashlet
    //$args = array("host" => "www.twitter.com", "service" => "Ping", "ds" => "rta");
    $host = grab_array_var($args, 'host', '');
    $service = grab_array_var($args, 'service', '');
    $ds = grab_array_var($args, 'ds', '');
    
    $id="gauges_inner_".random_string(6);
	$output="<div class='infotable_title' id='$id'>$host - $service</div>";
	
	$output.='<script type="text/javascript">
			$(document).ready(function(){
			
				myShinyGauge_'.$id.'_url="'.get_dashlet_url_base("gauges").'/getdata.php?host='.$host.'&service='.$service.'&ds='.$ds.'"
                $.ajax({"url": myShinyGauge_'.$id.'_url,
                            "success": function(result) { 
                            myShinyGauge_'.$id.' = create_gauge("'.$id.'", 1, result)
                            myShinyGauge_'.$id.'.redraw(result["current"],500)
                        }
                    });
               setInterval(function() {  $.ajax({"url": myShinyGauge_'.$id.'_url,
                            "success": function(result) { 
                            myShinyGauge_'.$id.'.redraw(result["current"],500)
                        }
                    }); }, 60000);
                
			});
			</script>';
	
	return $output;
	}

?>
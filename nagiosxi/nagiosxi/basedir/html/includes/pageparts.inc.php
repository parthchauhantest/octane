<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: pageparts.inc.php 1308 2012-07-27 14:28:01Z mguthrie $

include_once(dirname(__FILE__).'/utils.inc.php');
include_once(dirname(__FILE__).'/auth.inc.php');
include_once(dirname(__FILE__).'/components.inc.php');


function do_page_start($opts=null,$child=false){

	if($opts==null)
		$opts=array();

	// what title should be used for the page?
	$title="";
	if(isset($opts["page_title"]))
		$title=$opts["page_title"];
	$pagetitle=get_product_name();
	if($title!="")
		$pagetitle.=" - $title";
		
	// body id
	$bid="";
	$body_id="";
	if(isset($opts["body_id"]))
		$bid=$opts["body_id"];
	if($bid!="")
		$body_id=" id='$bid'";
	
	// body class
	$bc="";
	$body_class=" class='";
	if($child==false)
		$body_class.=" parent";
	else
		$body_class.=" child";
	if(isset($opts["body_class"]))
		$bc=$opts["body_class"];
	if($bc!="")
		$body_class=" $bc";
	$body_class.="'";
	
	// body style
	$bs="";
	$body_style="";
	if(isset($opts["body_style"]))
		$bs=$opts["body_style"];
	if($bs!="")
		$body_style=" style='$bs'";
	
	// page id
	$pid="";
	$page_id="";
	if(isset($opts["page_id"]))
		$pid=$opts["page_id"];
	if($pid!="")
		$page_id=" id='$pid'";
		
	// page class
	$page_class="parentpage";
	if($child==true)
		$page_class="childpage";
	$pc="";
	if(isset($opts["page_class"]))
		$pc=$opts["page_class"];
	if($pc!="")
		$page_class.=" $pc";
	
	if($child==false){
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<?php
		}
	else{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php
//<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

		}
?>

<html>
	<!-- Produced by Nagios XI.  Copyyright (c) 2008-2011 Nagios Enterprises, LLC (www.nagios.com). All Rights Reserved. -->
	<!-- Powered by the Nagios Synthesis Framework -->
	<head>
	<title><?php echo $pagetitle;?></title>
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<?php do_page_head_links($child);?>
	<?php
		$cbargs=array("child"=>$child);
	?>
	<?php do_callbacks(CALLBACK_PAGE_HEAD,$cbargs);?>
</head>


<body <?php echo $body_id;?><?php echo $body_class;?> <?php echo $body_style;?>>

	<?php do_callbacks(CALLBACK_BODY_START,$cbargs);?>
	
	<div <?php echo $page_id;?> class="<?php echo $page_class;?>"><!-- page-->

		<div <?php if($child==false) echo 'id="header" class="parenthead" ' ; else echo 'id ="childheader" class="childhead" '; ?>>
<?php
		do_page_header($child);
		if($child==false){
?>
		<div id="throbber"></div>
<?php
		}
?>
		</div><!--header -->


<?php 
	$throbber_image=get_base_url()."images/throbber1.gif";
	if($child==false){
?>
	<div id="mainframe">
	<div id="parentcontentthrobber"><img src='<?php echo $throbber_image;?>' /></div>
<?php
		if(is_authenticated()==true){
			$page=get_current_page();
			if($page!=PAGEFILE_LOGIN && $page!=PAGEFILE_INSTALL && $page!=PAGEFILE_UPGRADE){
?>
	<div id="fullscreen"></div>
<?php
				}
			}
		}
	else{
?>
	<div id="childcontentthrobber"><img src='<?php echo $throbber_image;?>' /></div>
<?php
		}
		
	// display screen dashboard in parent if someone is logged in
	if($child==false && is_authenticated()==true){
		$db=get_dashboard_by_id(0,SCREEN_DASHBOARD_ID);
		if($db!=null){
			echo "<!-- SCREEN DASHBOARD START -->";
			display_dashboard_dashlets($db);
			echo "<!-- SCREEN DASHBOARD END -->";
			}
		}

	// display login alerts (maybe)
	$thispage=get_current_page();
	if(is_authenticated()==true && $child==false && ($thispage!="upgrade.php" && $thispage!="login.php")){
		do_login_alert_popup();
		}
		
	do_callbacks(CALLBACK_CONTENT_START,$cbargs);
	}
	
	
function do_page_head_links($child=false){

	$base_url=get_base_url();
?>
	<link rel="shortcut icon" href="<?php echo $base_url;?>images/favicon.ico" type="image/ico" />
	<link rel='stylesheet' type='text/css' href='<?php echo $base_url;?>includes/css/jquery.autocomplete.css' />
	
	
	<script type='text/javascript'>
		var base_url="<?php echo $base_url;?>";
		var backend_url="<?php echo urlencode(get_backend_url(false));?>";
		var ajax_helper_url="<?php echo get_ajax_helper_url();?>";
		var ajax_proxy_url="<?php echo get_ajax_proxy_url();?>";
		var suggest_url="<?php echo get_suggest_url();?>";
		var request_uri="<?php echo urlencode($_SERVER["REQUEST_URI"]);?>";
		var permalink_base="<?php echo get_permalink_base();?>";
		var demo_mode=<?php echo (in_demo_mode()==true)?1:0;?>;
		var nsp_str="<?php echo get_nagios_session_protector_id();?>";
	</script>

<!-- FIREBUG LITE! -->	
<!--
<script type='text/javascript' 
        src='http://getfirebug.com/releases/lite/1.2/firebug-lite-compressed.js'></script>
//-->	

<?php
	// use to prevent caching of css/javascript across sessions
	$rand=time();
?>

	<!--<script type='text/javascript' src='<?php echo $base_url;?>includes/js/jquery/jquery-current.min.js'></script>-->
	
	<script type='text/javascript' src='<?php echo $base_url;?>includes/js/jquery/jquery-1.6.2.min.js'></script>
	
	
	<script type='text/javascript' src='<?php echo $base_url;?>includes/js/jquery/jquery.colorBlend.js'></script>
	<script type='text/javascript' src='<?php echo $base_url;?>includes/js/jquery/jquery.checkboxes.js'></script>
	<script type='text/javascript' src='<?php echo $base_url;?>includes/js/jquery/jquery.autocomplete.js'></script>
	<script type='text/javascript' src='<?php echo $base_url;?>includes/js/jquery/jquery.easydrag.js'></script>
	<!--<script type='text/javascript' src='<?php echo $base_url;?>includes/js/jquery/jquery.timer.js'></script>-->
	<script type='text/javascript' src='<?php echo $base_url;?>includes/js/jquery/jquery.timers-1.1.3.js'></script>

	<!-- this causes problems with sparkline! -->
	<!--<script type='text/javascript' src='<?php echo $base_url;?>includes/js/jquery/jquery.dimensions.pack.js'></script>-->
	<script type='text/javascript' src='<?php echo $base_url;?>includes/js/jquery/jquery.bgiframe.pack.js'></script>
	<script type='text/javascript' src='<?php echo $base_url;?>includes/js/jquery/jquery.tooltip.pack.js'></script>

	<script type='text/javascript' src='<?php echo $base_url;?>includes/js/jquery/jquery.sparkline.js'></script>
	
	<script type='text/javascript' src='<?php echo $base_url;?>includes/js/jquery/jquery.inview.min.js'></script>

	<!--<script type='text/javascript' src='<?php echo $base_url;?>includes/js/jquery/jquery.onscreen.min.js'></script>-->
	
	<script type='text/javascript' src='<?php echo $base_url;?>includes/js/jquery/jquery-ui-1.7.2.custom.min.js'></script>
	<link type="text/css" href="<?php echo $base_url;?>includes/js/jquery/css/smoothness/jquery-ui-1.7.2.custom.css" rel="stylesheet" />
	
	<!-- colorpicker -->
	<link rel="stylesheet" href="<?php echo $base_url;?>includes/js/jquery/colorpicker/css/colorpicker.css" type="text/css" />
	<!--
    <link rel="stylesheet" media="screen" type="text/css" href="<?php echo $base_url;?>includes/js/jquery/colorpicker/css/layout.css" />
	//-->
	<script type="text/javascript" src="<?php echo $base_url;?>includes/js/jquery/colorpicker/js/colorpicker.js"></script>
	<!--
    <script type="text/javascript" src="<?php echo $base_url;?>includes/js/jquery/colorpicker/js/eye.js"></script>
    <script type="text/javascript" src="<?php echo $base_url;?>includes/js/jquery/colorpicker/js/utils.js"></script>
    <script type="text/javascript" src="<?php echo $base_url;?>includes/js/jquery/colorpicker/js/layout.js?ver=1.0.2"></script>
	//-->
	<!-- colorpicker -->
	<script type="text/javascript" src="<?php echo $base_url;?>includes/js/jquery/jquery.zclip.min.js"></script>

	<script type='text/javascript' src='<?php echo $base_url;?>includes/js/core.js'></script>
	<script type='text/javascript' src='<?php echo $base_url;?>includes/js/commands.js'></script>
	<script type='text/javascript' src='<?php echo $base_url;?>includes/js/views.js'></script>
	<script type='text/javascript' src='<?php echo $base_url;?>includes/js/dashboards.js'></script>
	<script type='text/javascript' src='<?php echo $base_url;?>includes/js/dashlets.js'></script>
	<script type='text/javascript' src='<?php echo $base_url;?>includes/js/tables.js'></script>
	<script type='text/javascript' src='<?php echo $base_url;?>includes/js/users.js'></script>
	<script type='text/javascript' src='<?php echo $base_url;?>includes/js/perfdata.js'></script>
	
	<!-- timepickr javascript -->
	<!-- from http://bililite.com/blog/2009/07/09/updating-timepickr/ -->
	<!--<script type="text/javascript" src="<?php echo $base_url;?>includes/js/jquery/jquery.timepickr.js"></script>-->
	<!--
	<link rel="stylesheet" href="<?php echo $base_url;?>includes/js/jquery/timepickr/css/ui.timepickr.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo $base_url;?>includes/js/jquery/timepickr/css/ui.dropslide.css" type="text/css" />
	<script type="text/javascript" src="<?php echo $base_url;?>includes/js/jquery/timepickr/ui.timepickr.js"></script>
	<script type="text/javascript" src="<?php echo $base_url;?>includes/js/jquery/timepickr/ui.dropslide.js"></script>
	//-->
	
	<link rel='stylesheet' type='text/css' href='<?php echo $base_url;?>includes/css/nagiosxi.css?<?php echo $rand;?>' />
	
<?php
	if($child==false){
?>
<!-- styles needed by jScrollPane -->
<link type="text/css" href="<?php echo $base_url;?>includes/js/jquery/css/jquery.jscrollpane.css" rel="stylesheet" media="all" />

<!-- the jScrollPane script -->
<script type="text/javascript" src="<?php echo $base_url;?>includes/js/jquery/jquery.jscrollpane.min.js"></script>

<!-- the mousewheel plugin - optional to provide mousewheel support -->
<script type="text/javascript" src="<?php echo $base_url;?>includes/js/jquery/jquery.mousewheel.js"></script>

<?php
	}
?>
	
<?php
	// include css/js stuff for dashlets
	echo get_dashlets_pagepart_includes();
	}
	

function do_page_header($child){

	$cbargs=array("child"=>$child);

	do_callbacks(CALLBACK_HEADER_START,$cbargs);

	if($child==true)
		include_once(dirname(__FILE__).'/header-child.inc.php');
	else
		include_once(dirname(__FILE__).'/header.inc.php');

	do_callbacks(CALLBACK_HEADER_END,$cbargs);
	}	

	
	
function do_page_end($child=false){

	$cbargs=array("child"=>$child);
?>

	<?php do_callbacks(CALLBACK_CONTENT_END,$cbargs);?>

<?php
	if($child==false){
?>
		</div><!--mainframe-->
<?php
		}
?>
	
	<!--	<div id="footer">  //there should only be one div with id of footer on any given page, moved to footer.inc.php  --> 
		<?php do_page_footer($child);?>
	<!-- 	</div>  -->
	
	</div><!--page-->

<noframes>
<!-- This page requires a web browser which supports frames. --> 
<h2><?php echo get_product_name();?></h2>
<p align="center">
<a href="http://www.nagios.com/">www.nagios.com</a><br>
Copyright (c) 2009-2012 Nagios Enterprises, LLC<br>
</p>
<p>
<i>Note: These pages require a browser which supports frames</i>
</p>
</noframes>

	<?php do_callbacks(CALLBACK_BODY_END,$cbargs);?>
	
<?php
	// analytics
	//if($child==false){
	if(1){
		global $cfg;
		$always_use_analytics=grab_array_var($cfg,"always_use_analytics",0);
		if(is_trial_license()==true || is_free_license()==true || $always_use_analytics==1){
			$enable_analytics=grab_array_var($cfg,"enable_analytics",1);
			if($enable_analytics==1 || $always_use_analytics==1){
				echo "<script type='text/javascript'>

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-2887186-1']);
  _gaq.push(['_setAllowLinker', true]);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();	

</script>";
				}
			
			}
		}
?>	

</body>

</html>
<?php
	}
	


function do_page_footer($child){

	$cbargs=array("child"=>$child);

	do_callbacks(CALLBACK_FOOTER_START,$cbargs);
	
	if($child===true)
		include_once(dirname(__FILE__).'/footer-child.inc.php');
	else
		include_once(dirname(__FILE__).'/footer.inc.php');
		
	do_callbacks(CALLBACK_FOOTER_END,$cbargs);
	}	


//displays page feedback in a formatted box
// Updated 4/27/2012, added option to get the output in a return value instead of a direct print
function display_message($error=true,$info=true,$msg="",$echo=false){
	if(!$echo)
		echo get_message_text($error,$info,$msg);
	else
		return get_message_text($error,$info,$msg);
	}	


function get_window_frame_url($default){
	global $request;
	
	// default window url may have been overridden with a permalink...
	$xiwindow=grab_request_var("xiwindow","");
	if($xiwindow!=""){
		$rawurl=urldecode($xiwindow);
		//$rawurl=urlencode($rawurl); // XSS fix!  Fixes:  /nagiosxi/account/?xiwindow="></iframe><script>alert('0a29')</script>
		}
	// otherwise use default url
	else
		$rawurl=$default;

	// parse url and remove permalink option from base
	$a=parse_url($rawurl);

	// build base url
	if(isset($a["host"])){
		if(isset($a["port"]) && $a["port"]!="80")
			$windowurl=$a["scheme"]."://".$a["host"].":".$a["port"].$a["path"]."?";
		else
			$windowurl=$a["scheme"]."://".$a["host"].$a["path"]."?";
		}
	else
		$windowurl=htmlspecialchars($a["path"])."?"; // XSS fix - must urlencode path
														//Changed to htmlspecial chars, urlencode broke some paths but keeps XSS fix 12/19/2011 -MG
	$q="";
	if(isset($a["query"]))
		$q=$a["query"];
		
	$pairs=explode("&",$q);
	foreach($pairs as $pair){
		$v=explode("=",$pair);
		if(is_array($v))
			$windowurl.="&".urlencode($v[0])."=".urlencode(isset($v[1])?$v[1]:"");
		}

	return $windowurl;
	}

function do_login_alert_popup(){
	global $lstr;
	
	// display login alerts if they haven't seen it already
	//$_SESSION["has_seen_login_alerts"]=false;
	if(isset($_SESSION["has_seen_login_alerts"]) && $_SESSION["has_seen_login_alerts"]==true)
		return;
	$_SESSION["has_seen_login_alerts"]=true;
	
	// user has alert screen disabled
	$show=get_user_meta(0,"show_login_alert_screen");
	if($show!="" && $show==0)
		return;

?>
	<div id="login_alert_popup" style="visibility: hidden;">
	
	<div id="close_login_alert_popup" style="float: right;">
	<a id="close_login_alert_popup_link" href="#"><img src="<?php echo get_base_url();?>images/b_close.png" border="0" alt="<?php echo $lstr['CloseAlt']?>" title="<?php echo $lstr['CloseAlt'];?>"> <?php echo $lstr['CloseAlt'];?></a>
	</div>
	
	<script type="text/javascript">
	$(document).ready(function(){
		$("#login_alert_popup").each(function(){
			//$(this).draggable();
			});
		$("#close_login_alert_popup_link").click(function(){
			$("#login_alert_popup").css("display","none");
			});
		});
	</script>
	

	<h1><img src='<?php echo theme_image("message_bubble.png");?>'> Notices</h1>
	<p> Some important information you should be aware of is listed below.
	</p>
	
	<div id="login_alert_popup_content" style="overflow: auto; border: 1px solid gray; margin: 0pt 0pt 0pt 0px; padding: 5px; height: 275px;">
	<?php //echo $html;?>
	Hello
	</div>
	
	<div id="no_login_alert_popup" style="float: right; clear: right;">
	<input type="checkbox" id="no_login_alert_popup_cb" name="no_login_alert_popup_cb" CHECKED> Show these alerts when I login
	</div>

	<script type="text/javascript">
	$(document).ready(function(){
		$("#no_login_alert_popup_cb").change(function(){
			//$("#login_alert_popup").css("display","none");

			var optsarr = {
				"keyname": "show_login_alert_screen",
				"keyvalue": 0,
				"autoload": false
				};
			var opts=array2json(optsarr);
			var result=get_ajax_data("setusermeta",opts);

			});
		});
	</script>
	
	<script type="text/javascript">
	
	// show the login alert popup only if we have some alerts!
	function display_login_alert_popup_content(edata){
		data=unescape(edata);
		if(data=="")
			$("#login_alert_popup").css("visibility","hidden");
		else
			$("#login_alert_popup").css("visibility","visible");
		}

	function get_login_alert_popup_content(){
		$("#login_alert_popup_content").each(function(){
			var optsarr = {
				"func": "get_login_alert_popup_html",
				"args": ""
				}
			var opts=array2json(optsarr);
			//get_ajax_data_innerHTML("getxicoreajax",opts,true,this);
			get_ajax_data_innerHTML_with_callback("getxicoreajax",opts,true,this,"display_login_alert_popup_content");
			});
		}

	$(document).ready(function(){
		get_login_alert_popup_content();		
		});
	</script>	
	
	</div>
<?php
	}
	

?>
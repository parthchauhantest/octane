<?php
//
// Copyright (c) 2008 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: header.inc.php 2025 2013-11-18 21:52:29Z jomann $

include_once(dirname(__FILE__).'/common.inc.php');

global $lstr;
?>

<!--- HEADER START -->

<?php
	// default logo stuff
	$logo="nagiosxi-logo-small.png";
	$logo_alt=get_product_name();
	$logo_url="http://www.nagios.com/products/nagiosxi/";
    $logo_target="_blank";

	// use custom logo if it exists
	$logosettings_raw=get_option("custom_logo_options");
	if($logosettings_raw=="")
		$logosettings=array();
	else
		$logosettings=unserialize($logosettings_raw);
	
	$custom_logo_enabled=grab_array_var($logosettings,"enabled");
	if($custom_logo_enabled==1){
		$logo=grab_array_var($logosettings,"logo",$logo);
		$logo_alt=grab_array_var($logosettings,"logo_alt",$logo_alt);
		$logo_url=grab_array_var($logosettings,"logo_url",$logo_url);
        $logo_target=grab_array_var($logosettings,"logo_target",$logo_target);
		}
	
?>

<div id="toplogo">
   <a href="<?php echo $logo_url;?>" target="<?php echo $logo_target;?>"><img src="<?php echo get_base_url();?>images/<?php echo $logo;?>" border="0" alt="<?php echo $logo_alt;?>" title="<?php echo $logo_alt;?>"></a>
</div>
<div id="pagetopalertcontainer">
<?php
	if(is_authenticated()==true){
	display_pagetop_alerts();
	}
?>
</div>
<div id="authinfo">
<?php
	if(is_authenticated()==true){
?>
<div id="authinfoname">
<?php echo gettext("Logged in as"); ?>: <a href="<?php echo get_base_url();?>account/"><?php echo $_SESSION["username"];?></a>
</div>
<?php
	if(is_http_basic_authenticated()==false){
?>
<div id="authlogout">
<a href="<?php echo get_base_url().PAGEFILE_LOGIN;?>?logout"><?php echo gettext("Logout"); ?></a>
</div>
<?php
		}
	}
?>
</div>

<?php
// If using the new style
if (use_2014_features() && get_option("theme") == "xi2014") {

// Find out what tab is active
$active = "home";

$filename = $_SERVER['SCRIPT_FILENAME'];
if (strpos($filename, "html/admin")) {
	$active = "admin";
} else if (strpos($filename, "html/views")) {
	$active = "views";
} else if (strpos($filename, "html/dashboards")) {
	$active = "dashboards";
} else if (strpos($filename, "html/reports")) {
	$active = "reports";
} else if (strpos($filename, "html/configure")) {
	$active = "configure";
} else if (strpos($filename, "html/tools")) {
	$active = "tools";
} else if (strpos($filename, "html/help")) {
	$active = "help";
} else if (strpos($filename, "login.php")) {
	$active = "login";
}

?>

<!-- New Nagios XI Navbar -->
<div class="navbar navbar-inverse">
	<div class="navbar-inner">
		<div class="container">
			<ul class="nav pull-left">
				<?php if (is_authenticated() === true) { ?>
				<li<?php if ($active == "home") { echo ' class="active"'; } ?>><a href="<?php echo get_base_url();?>"><?php echo gettext("Home"); ?></a></li>
				<li<?php if ($active == "views") { echo ' class="active"'; } ?>><a href="<?php echo get_base_url();?>views/"><?php echo gettext("Views"); ?></a></li>
				<li<?php if ($active == "dashboards") { echo ' class="active"'; } ?>><a href="<?php echo get_base_url();?>dashboards/"><?php echo gettext("Dashboards"); ?></a></li>
				<li<?php if ($active == "reports") { echo ' class="active"'; } ?>><a href="<?php echo get_base_url();?>reports/"><?php echo gettext("Reports"); ?></a></li>
					<?php if (is_authorized_to_configure_objects() === true) { ?>
					<li<?php if ($active == "configure") { echo ' class="active"'; } ?>><a href="<?php echo get_base_url();?>config/"><?php echo gettext("Configure"); ?></a></li>
					<?php
					} // End config objects authorized

					if (use_2012_features() === true) {
					?>
					<li<?php if ($active == "tools") { echo ' class="active"'; } ?>><a href="<?php echo get_base_url();?>tools/"><?php echo gettext("Tools"); ?></a></li>
					<?php
					} // End use 2012 features
					?>
					<li<?php if ($active == "help") { echo ' class="active"'; } ?>><a href="<?php echo get_base_url();?>help/"><?php echo gettext("Help"); ?></a></li>
					<?php if(is_admin() === true) { ?>
					<li<?php if ($active == "admin") { echo ' class="active"'; } ?>><a href="<?php echo get_base_url();?>admin/"><?php echo gettext("Admin"); ?></a></li>
					<?php } // End if is admin ?>
				<?php } else { // End if authorized ?>
				<li<?php if ($active == "login") { echo ' class="active"'; } ?>><a href="<?php echo get_base_url().PAGEFILE_LOGIN;?>"><?php echo gettext("Login"); ?></a></li>
				<?php } ?>
			</ul>
			<?php if (is_authenticated() === true) { ?>
			<ul class="nav pull-right">
				<li class="navbar-icons">
					<?php if (use_2012_features() === true) { ?>
					<div id="schedulepagereport">
						<a href="#" alt="<?php echo $lstr['SchedulePageAlt'];?>" title="<?php echo $lstr['SchedulePageAlt'];?>"><i class="fa fa-clock-o"></i></a>
					</div>
					<?php } ?>
					<div id="permalink">
						<a href="#" alt="<?php echo $lstr['GetPermalinkAlt'];?>" title="<?php echo $lstr['GetPermalinkAlt'];?>"><i class="fa fa-chain"></i></a>
					</div>
					<div id="feedback">
						<a href="#" alt="<?php echo $lstr['SendFeedbackAlt'];?>" title="<?php echo $lstr['SendFeedbackAlt'];?>"><i class="fa fa-comment"></i></a>
					</div>
				</li>
			</ul>
			<form method="post" class="navbar-search pull-right" target="maincontentframe" action="<?php echo get_base_url();?>includes/components/xicore/status.php?show=services">
				<input type="hidden" name="navbarsearch" value="1" />
				<input type="text" class="search-query" name="search" id="navbarSearchBox" value="" placeholder="<?php echo gettext('Search...'); ?>" />
			</form>
			<?php } // End if authenticated ?>
		</div>
	</div>
</div>

<?php
} else { // Use older style header
?>

<div id="topmenucontainer">
	<ul class="menu">
<?php
	if(is_authenticated()==true){
?>
	<li><a href="<?php echo get_base_url();?>"><?php echo gettext("Home"); ?></a></li>
	<li><a href="<?php echo get_base_url();?>views/"><?php echo gettext("Views"); ?></a></li>
	<li><a href="<?php echo get_base_url();?>dashboards/"><?php echo gettext("Dashboards"); ?></a></li>
	<li><a href="<?php echo get_base_url();?>reports/"><?php echo gettext("Reports"); ?></a></li>
<?php
		if(is_authorized_to_configure_objects()==true){
?>
	<li><a href="<?php echo get_base_url();?>config/"><?php echo gettext("Configure"); ?></a></li>
<?php
			}
?>
<?php
		if(use_2012_features()==true){
?>
	<li><a href="<?php echo get_base_url();?>tools/"><?php echo gettext("Tools"); ?></a></li>
<?php
			}
?>
	<li><a href="<?php echo get_base_url();?>help/"><?php echo gettext("Help"); ?></a></li>

<?php
		if(is_admin()==true){
?>
	<li><a href="<?php echo get_base_url();?>admin/"><?php echo gettext("Admin"); ?></a></li>
<?php
			}
?>
<?php
	}
	else{
?>
	<li><a href="<?php echo get_base_url().PAGEFILE_LOGIN;?>"><?php echo gettext("Login"); ?></a></li>
<?php
	}
?>
	</ul>
</div>
<?php
	if(is_authenticated()==true){
?>

<div id="primarybuttons">
<?php
		if(use_2012_features()==true){
?>

<div id="schedulepagereport">
<a href="#" alt="<?php echo $lstr['SchedulePageAlt'];?>" title="<?php echo $lstr['SchedulePageAlt'];?>"></a>
</div>

<?php
			}
?>
<div id="permalink">
<a href="#" alt="<?php echo $lstr['GetPermalinkAlt'];?>" title="<?php echo $lstr['GetPermalinkAlt'];?>"></a>
</div>
<div id="feedback">
<a href="#" alt="<?php echo $lstr['SendFeedbackAlt'];?>" title="<?php echo $lstr['SendFeedbackAlt'];?>"></a>
</div>
</div>
<?php
		}
} // End 2014 new style feature
?>
<?php display_feedback_layer();?>
<div id="popup_layer">
	<div id="popup_content">
	<div id="popup_close">
	<a id="close_popup_link" href="#"><img src="<?php echo get_base_url();?>images/b_close.png" border="0" alt="<?php echo $lstr['CloseAlt']?>" title="<?php echo $lstr['CloseAlt'];?>"> <?php echo $lstr['CloseAlt'];?></a>
	</div>
	<div id="popup_container">
	</div>
	</div>
</div>

<!-- HEADER END -->

<?php

function display_feedback_layer(){
	global $cfg;
	global $lstr;
	
	$name=get_user_attr(0,'name');
	$email=get_user_attr(0,'email');
?>
	<div id="feedback_layer">
	<div id="feedback_content">
	
	<div id="feedback_close">
	<a id="close_feedback_link" href="#"><img src="<?php echo get_base_url();?>images/b_close.png" border="0" alt="<?php echo $lstr['CloseAlt']?>" title="<?php echo $lstr['CloseAlt'];?>"> <?php echo $lstr['CloseAlt'];?></a>
	</div>
	
	<div id="feedback_container">
	
	<div id="feedback_header">
	<b><?php echo $lstr['FeedbackPopupTitle'];?></b>
	<p><?php echo $lstr['FeedbackSendIntroText'];?></p>
	</div><!-- feedback_header -->
	
	<div id="feedback_data">

	<form id="feedback_form" method="get" action="<?php echo get_ajax_proxy_url();?>">

	<input type="hidden" name="proxyurl" value="<?php echo $cfg['feedback_url'];?>">
	<input type="hidden" name="proxymethod" value="post">

	<input type="hidden" name="product" value="<?php echo get_product_name(true);?>">
	<input type="hidden" name="version" value="<?php echo get_product_version();?>">
	<input type="hidden" name="build" value="<?php echo get_product_build();?>">

	<label for="feedbackCommentBox"><?php echo $lstr['FeedbackCommentBoxText'];?>:</label><br class="nobr" />
	<textarea class="textarea" name="comment" cols="40" rows="3"></textarea><br class="nobr" />

	<label for="feedbackNameBox"><?php echo $lstr['FeedbackNameBoxTitle'];?>:</label><br class="nobr" />
	<input type="text" size="30" name="name" id="feedbackNameBox" value="<?php echo encode_form_val($name);?>" class="textfield" /><br class="nobr" />

	<label for="feedbackEmailAddressBox"><?php echo $lstr['FeedbackEmailBoxTitle'];?>:</label><br class="nobr" />
	<input type="text" size="30" name="email" id="feedbackEmailAddressBox" value="<?php echo encode_form_val($email);?>" class="textfield" /><br class="nobr" />

	<div id="feedbackFormButtons">
	<input type="submit" class="submitbutton" name="submitButton" value="<?php echo $lstr['SubmitButton'];?>" id="submitFeedbackButton">
	</div>
	
	<br clear="all">
	<p>
	<a href="<?php echo $cfg["privacy_policy_url"];?>" target="_blank"><?php echo $lstr['PrivacyPolicyLinkText'];?></a>
	</p>

	</form>
	
	</div><!-- feedback_data -->
	
	</div><!-- feedback_container-->
	
	
	</div><!--feedback_content-->
	</div><!--feedback_layer-->
<?php
	}

	
function display_pagetop_alerts(){

	$id="pagetopalertcontent";

	$output='
			<div id="'.$id.'"></div>

			<script type="text/javascript">
			$(document).ready(function(){

				get_'.$id.'_content();
					
				$("#'.$id.'").everyTime('.get_dashlet_refresh_rate(30,"pagetop_alert_content").', "timer-'.$id.'", function(i) {
					get_'.$id.'_content();
				});
				
				function get_'.$id.'_content(){
					$("#'.$id.'").each(function(){
						var optsarr = {
							"func": "get_pagetop_alert_content_html",
							"args": ""
							}
						var opts=array2json(optsarr);
						get_ajax_data_innerHTML("getxicoreajax",opts,true,this);
						});
					}

			});
			</script>
		';
		
	echo $output;

	}
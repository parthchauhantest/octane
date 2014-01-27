<?php
//
// Copyright (c) 2008-2012 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: page-default-splash.php 1334 2012-08-21 02:55:26Z egalstad $

require_once(dirname(__FILE__).'/common.inc.php');

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

route();

function route(){

	$getfeed=grab_request_var("getfeed","");
	
	if($getfeed!="")
		do_fetch_content($getfeed);
	else
		do_page();
	}

	
function do_fetch_content($feed){
	//xi rss handler include 
	require_once(dirname(__FILE__).'/utils-rss.inc.php');
	
	switch($feed){
	
		case "techsupport":
		
			$html="";
			
			$html.="<div style='float: left; width: 270px;'>";
			
			$html.="<h3>Support Options</h3>";
			
			$html.="<ul>
			";
			$html.="
			<li><a href='http://support.nagios.com/forum' target='_blank'>Online Support Forum</a></li>
			<li><a href='http://support.nagios.com/forum/viewforum.php?f=16' target='_blank'>Customer Support Forum</a></li>
			<li>Email Support: <a href='mailto:xisupport@nagios.com'>xisupport@nagios.com</a></li>
			<li>Phone Support: +1 651-204-9102 Ext. 4</li>
			</ul>";


			$html.="<h3>".gettext("Documentation and Tutorials")."</h3>";
			
			$html.="<ul>
			";
			if(is_admin()==true)
				$html.="<li><a href='http://assets.nagios.com/downloads/nagiosxi/guides/administrator/' target='_blank'>".gettext("Administrator Guide")."</a></li>";
			$html.="
			<li><a href='http://assets.nagios.com/downloads/nagiosxi/guides/user/' target='_blank'>".gettext("User Guide")."</a></li>
			<li><a href='http://library.nagios.com/library/products/nagiosxi/tutorials' target='_blank'>".gettext("Video Tutorials")."</a></li>
			<li><a href='http://library.nagios.com/library/products/nagiosxi/documentation' target='_blank'>".gettext("Documentation and HOWTOs")."</a></li>
			<li><a href='http://support.nagios.com/wiki/index.php/Nagios_XI:FAQs' target='_blank'>".gettext("FAQs")."</a></li>
			</ul>";
			
			$html.="</div>";
			
			$html.="<div style='float: left; margin-left: 10px; padding-bottom: 10px; width: 220px;'>";

			$html.="<h3>".gettext("We're Here To Help!")."</h3>";
			
			$html.="<p>
			".gettext("Our knowledgeable techs are happy to help you with any questions or problems you may have getting Nagios up and running.")."
			</p>";
			
			if(is_trial_license()==true){
				$html.="<h3>".gettext("Free Quickstart Services")."</h3>";
				
				$html.="<p>"."Our techs can help you get up and running quickly with Nagios XI so you can get the most out of your evaluation period."."</p>";
				
				$html.="<p>"."Click the link below to request free quickstart services."."</p>";				

				$html.="<a href='http://go.nagios.com/xi-quickstart-request' target='_blank'><b>".gettext("Request Quickstart Services")."</b></a>";
				}
			else{
				$html.="<h3>".gettext("Nagios Demos and Webinars")."</h3>";
				
				$html.="<p>"."Our techs can demonstrate the latest features of Nagios XI and show you how to make the most of your IT monitoring environment."."</p>";
				
				$html.="<p>"."Click the link below to request a demo."."</p>";				

				$html.="<a href='http://go.nagios.com/xi-demo-request' target='_blank'><b>".gettext("Request A Demo")."</b></a>";
				}
				
			$html.="</div>";

			
			$html.="<div style='float: left; margin-left: 10px; width: 210px;'>";
			
			$html.="<img src='".theme_image("techsupport-splash.png")."' style='padding: 10px;'>";
			
			$html.="<br>";
			
			$html.="<h3>".gettext("Connect With Us")."</h3>";			
			
			$html.='	<a target="_blank" href="http://www.facebook.com/pages/Nagios/194145247332208">
	<img title="Facebook" src="'.get_base_url().'images/social/facebook-32x32.png">
	</a>
	<a target="_blank" href="http://twitter.com/#%21/nagiosinc">
	<img title="Twitter" src="'.get_base_url().'images/social/twitter-32x32.png">
	</a>
	<a target="_blank" href="http://www.youtube.com/nagiosvideo">
	<img title="YouTube" src="'.get_base_url().'images/social/youtube-32x32.png">
	</a>
	<a target="_blank" href="http://www.linkedin.com/groups?gid=131532">
	<img title="LinkedIn" src="'.get_base_url().'images/social/linkedin-32x32.png">
	</a>
	<a target="_blank" href="http://www.flickr.com/photos/nagiosinc">
	<img title="Flickr" src="'.get_base_url().'images/social/flickr-32x32.png">
	</a>';

			$html.="</div>";

			print $html;

			break;	
	
		case "xipromo":
	
			$url="http://api.nagios.com/feeds/xipromo/";
			$rss=xi_fetch_rss($url);
			if($rss) {
				$x=0;
				$html = "
				<ul>\n"; 
			
				foreach ($rss as $item){
					$x++;
					if($x>5)
						break;
					$summary = strval($item->description);	
					$html .="<li>".$summary."</li>";
					}
				$html .=' 
				</ul>'; 
				
				print $html; 
				}
			else{
				$html = gettext("Stay on top of what our development team is up to by visiting").
				":<br /><a href='http://labs.nagios.com/' target='_blank'>http://labs.nagios.com/</a>.";
				print $html;
				}	
			break;
			
		case "labs":
	
			$url="http://labs.nagios.com/feed";
			$rss=xi_fetch_rss($url);
			if($rss) {
				$x=0;
				$html = "
				<ul>\n"; 
			
				foreach ($rss as $item){
					$x++;
					if($x>5)
						break;
					$href = strval($item->link);
					$title = strval($item->title);	
					$html .="<li><a href='$href' target='_blank'>".htmlentities($title, ENT_COMPAT, 'UTF-8')."</a></li>";
					}
				$html .='
				<li><a href="http://labs.nagios.com/" target="_blank">'.gettext("More blog posts").'...</a></li>
				</ul>'; 
				
				print $html; 
				}
			else{
				$html = gettext("Stay on top of what our development team is up to by visiting").":<br />
				<a href='http://labs.nagios.com/' target='_blank'>http://labs.nagios.com/</a>.
				";
				print $html;
				}	
			break;
			
		case "library":
		
			$url="http://library.nagios.com/library/products/nagiosxi/documentation?format=feed&type=rss";
			$rss=xi_fetch_rss($url);
			if($rss) {
				$x=0;
				$html = "
				<ul>\n"; 
			
				foreach ($rss as $item){
					$x++;
					if($x>5)
						break;
					$href = strval($item->link);
					$title = strval($item->title);	
					$html .="<li><a href='$href' target='_blank'>".htmlentities($title, ENT_COMPAT, 'UTF-8')."</a></li>";
					}
				$html .='
				<li><a href="http://library.nagios.com/" target="_blank">'.gettext("More tutorials").'...</a></li>
				</ul>'; 
				
				print $html; 
				}
			else{
				$html = gettext("Stay on top of new documentation by visiting").":<br />
				<a href='http://library.nagios.com/' target='_blank'>http://library.nagios.com/</a>.";
				print $html;
				}	
			break;			
		default:
			//echo "This is $feed";		
			break;
		}
	}


function do_page(){

	$page_title="Nagios XI";

	do_page_start(array("page_title"=>$page_title),true);

?>
	<!--<h1><?php echo $page_title;?></h1>-->
	
	
<script type='text/javascript'>
	//rss fetch by ajax to reduce page load time
	$(document).ready(function() {		
		 $('#techsupport-contents').load('?getfeed=techsupport');	
		 $('#xipromo-contents').load('?getfeed=xipromo');	
		 $('#libraryfeed-contents').load('?getfeed=library');				
		 $('#labsfeed-contents').load('?getfeed=labs');	
	}); 
</script>	

<style type="text/css">
	#leftcol{
		padding-top: 25px;
		float: left;
		width: 725px;
		clear: both;
		padding-right: 25px;
		}
	#rightcol{
		padding-top: 25px;
		float: left;
		width: 350px;
		}
	#techsupport{
		width: 740px;
		clear: both;
		}
	#xipromo{
		width: 735px;
		margin-top: 20px;
		}

	#xipromo li, #libraryfeed li, #labsfeed li {
		/*clear: both;*/
		clear: left;
		list-style: none;
		}
	#xipromo li{
		padding-bottom: 10px;
		}
	#xipromo ul, #libraryfeed ul, #labsfeed ul {
		padding: 0 5px;
		margin: 5px 0;
		}	
	#techsupport ul {
		padding: 0 5px;
		margin: 5px 10px;
		}	
	#techsupport h3 {		
		margin: 8px 4px 4px;
		}
		
	#libraryfeed, #labsfeed {
		margin-bottom: 15px;
		}
</style>

	
	<div style='float: right;'>
		<a href='/nagiosxi/includes/components/homepagemod/useropts.php'><?php echo gettext("Change my default home page"); ?></a>
	</div>
	
	<div id="leftcol">
	
	<div id="techsupport">
	<table class="infotable">
	<thead>
	<tr><th><?php echo gettext("Support Resources"); ?></th><th></th></tr>
	</thead>
	<tbody>
	<tr><td><div id="techsupport-contents"><img src="<?php echo theme_image("throbber1.gif");?>"> <?php echo gettext("Fetching data"); ?>...</div></td></tr>
	</tbody>
	</table>
	</div>	

	<div id="xipromo">
	<table class="infotable">
	<thead>
	<tr><th><?php echo gettext("Don't Miss Out"); ?></th><th></th></tr>
	</thead>
	<tbody>
	<tr><td><div id="xipromo-contents">
		<img src="<?php echo theme_image("throbber1.gif");?>"> <?php echo gettext("Fetching data"); ?>...</div></td></tr>
	</tbody>
	</table>
	</div>
	
	<!--
	<div id="social">
	<table class="infotable">
	<thead>
	<tr><th>Connect With Us</th><th></th></tr>
	</thead>
	<tbody>
	<tr><td>
	<a target="_blank" href="http://www.facebook.com/pages/Nagios/194145247332208">
	<img title="Facebook" src="http://assets.nagios.com/images/social/facebook-32x32.png">
	</a>
	<a target="_blank" href="http://twitter.com/#%21/nagiosinc">
	<img title="Twitter" src="http://assets.nagios.com/images/social/twitter-32x32.png">
	</a>
	<a target="_blank" href="http://www.youtube.com/nagiosvideo">
	<img title="YouTube" src="http://assets.nagios.com/images/social/youtube-32x32.png">
	</a>
	<a target="_blank" href="http://www.linkedin.com/groups?gid=131532">
	<img title="LinkedIn" src="http://assets.nagios.com/images/social/linkedin-32x32.png">
	</a>
	<a target="_blank" href="http://www.flickr.com/photos/nagiosinc">
	<img title="Flickr" src="http://assets.nagios.com/images/social/flickr-32x32.png">
	</a>
	</td></tr>	
	</tbody>
	</table>
	</div>
	//-->
	
	</div>

	
	<div id="rightcol">
	
	<div id="libraryfeed">
	<table class="infotable">
	<thead>
	<tr><th><?php echo gettext("Latest Documentation"); ?></th><th></th></tr>
	</thead>
	<tbody>
	<tr><td><div id="libraryfeed-contents"><img src="<?php echo theme_image("throbber1.gif");?>"> <?php echo gettext("Fetching data"); ?>...</div></td></tr>
	</tbody>
	</table>
	</div>
	
	<div id="labsfeed">
	<table class="infotable">
	<thead>
	<tr><th><?php echo gettext("Development Blog"); ?></th><th></th></tr>
	</thead>
	<tbody>
	<tr><td><div id="labsfeed-contents"><img src="<?php echo theme_image("throbber1.gif");?>"> <?php echo gettext("Fetching data"); ?>...</div></td></tr>
	</tbody>
	</table>
	</div>	
	
	</div>
	
<?php

	do_page_end(true);
	}
?>

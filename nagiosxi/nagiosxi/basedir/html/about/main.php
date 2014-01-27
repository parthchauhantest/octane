<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: main.php 1137 2012-04-23 15:22:37Z egalstad $

require_once(dirname(__FILE__).'/../includes/common.inc.php');

// initialization stuff
pre_init();

// start session
init_session();

// grab GET or POST variables 
grab_request_vars();

// check prereqs
check_prereqs();

// check authentication - not necessary!
//check_authentication(false);


route_request();

function route_request(){
	global $request;
	
	$pageopt=get_pageopt("info");
	//echo "PAGEOPT: '$pageopt'\n";
	//exit();
	switch($pageopt){
		case "legal":
			show_legal();
			break;
		case "license":
			show_license();
			break;
		default:
			show_about();
			break;
		}
	}
	

function show_about(){
	global $lstr;

	do_page_start(array("page_title"=>$lstr['AboutPageTitle']),true);

?>
	<h1><?php echo $lstr['AboutPageHeader'];?></h1>

	<p>
	<img src="<?php	echo theme_image("loginsplash.png");?>">
	</p>
	
	<p>
	Nagios&reg; XI&trade; Copyright &copy; 2008-2012 <a href="http://www.nagios.com/" target="_blank">Nagios Enterprises, LLC</a>.  All rights reserved.
	</p>
	
	<div class="sectionTitle">About Nagios XI</div>
	
	<p>
	Nagios XI is an enterprise-class monitoring and alerting solution that provides organizations with extended insight of their IT infrastructure before problems affect critical business processes. For more information on Nagios XI, visit <a href="www.nagios.com/products/nagiosxi/" target="_blank">www.nagios.com/products/nagiosxi/</a>
	</p>
	
	<div class="sectionTitle">License</div>

	<p>
	Use of Nagios XI is subject to acceptance of the <a href="?license">Nagios Software License Terms and Conditions</a>.
	</p>
	
	<div class="sectionTitle">Contact Us</div>
	<p>
	Have a question or technical problem? Contact us today:
	</p>
	<table border="0">
	<tr><td valign="top">Support:</td><td><a href="http://support.nagios.com/forum/" target="_blank">Online Support Forum</a></td></tr>
	<tr><td valign="top">Sales:</td><td>Phone: (651) 204-9102<br>Fax: (651) 204-9103<br>Email: sales@nagios.com</td></tr>
	<tr><td valign="top">Web:</td><td><a href="http://www.nagios.com/" target="_blank">www.nagios.com</a></td></tr>
	</table>

	<div class="sectionTitle">Credits</div>

	<p>
	We'd like to thank the many individuals, companies, partners, and customers who have shared their ideas and stories with us and participaged in developing some really great software solutions that have made Nagios XI a possibility.  Neither Nagios Enterprises nor Nagios XI are necessarily endorsed by any of these parties - we just wanted to list them here as a public way of thanking them for the contributions they've made in various ways.
	</p>
	
	<p>
	Some particular Open Source projects and development communities we'd like to thank include:<br>
	The PHP development community, the MySQL and Postgres development communities, the ADODB project team, The Jquery project team and expanded Jquery community, the Silk icon set author at famfamfam.com, the PHPMailer team, the RRDTool project, the Nagios Core project, the Nagios Plugins projects, the PNP project, the Nagvis project, the NagiosQL project, the Vartour Style project, the author of the F*Nagios image pack, and the entire Nagios Community and greater OSS community members who make great OSS solutions a possibility through their tireless contributions.  We just wanted to let you know that we think you rock.
	</p>
	
	<p>
	We'd like to give an extra special thanks to the individual founders and leaders of each OSS project mentioned above.  We know that it takes a lot to build something that stands head and shoulders above the competition.  Kudos for you to bringing awesomeness into the world.
	</p>
	
	<p>
	- The Nagios Enterprises Team
	</p>
		
	
	
<?php
	do_page_end(true);
	}

	

function show_legal(){
	global $lstr;

	do_page_start(array("page_title"=>$lstr['LegalPageTitle']),true);

?>
	<h1><?php echo $lstr['LegalPageHeader'];?></h1>
	
	<p>
	Nagios&reg; XI&trade; Copyright &copy; 2008-2011 <a href="http://www.nagios.com/" target="_blank">Nagios Enterprises, LLC</a>.  All rights reserved.
	</p>
	
	<div class="sectionTitle">License</div>

	<p>
	Use of Nagios XI is subject to acceptance of the <a href="?license">Nagios Software License Terms and Conditions</a>.
	</p>
	
	<div class="sectionTitle">Disclaimer of Warranty</div>

	<p>
	Nagios XI and all information, documentation, and software components contained in and distributed with it are provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A PARTICULAR PURPOSE. 
	</p>
	
	<div class="sectionTitle">Trademarks</div>

	<p>
	Nagios, Nagios XI, Nagios Core, and Nagios graphics are trademarks, servicemarks, registered servicemarks or registered trademarks of Nagios Enterprises. All other trademarks, servicemarks, registered trademarks, and registered servicemarks mentioned herein may be the property of their respective owner(s).  Use of our trademarks is subject to Nagios Enterprises' <a href="http://www.nagios.com/legal/" target="_blank">Trademark Use Restrictions</a>.
	</p>
	
<?php
	do_page_end(true);
	}


function show_license(){
	global $lstr;
	global $license_text;

	do_page_start(array("page_title"=>$lstr['LicensePageTitle']),true);

?>
	<h1><?php echo $lstr['LicensePageHeader'];?></h1>
	
	<p>
	Nagios&reg; XI&trade; Copyright &copy; 2008-2011 <a href="http://www.nagios.com/" target="_blank">Nagios Enterprises, LLC</a>.  All rights reserved.
	</p>

	<br>
	<p>
	<?php echo get_formatted_license_text();?>
	</p>
	
<?php
	do_page_end(true);
	}
?>


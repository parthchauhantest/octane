<?php //EXAMPLE template for custom login splash 
/**
*	This file can be modified for use as a custom login splash page for Nagios XI
*	Implement the use of this include by accessing the Admin->Manage Components->Custom Login Splash component, 
*	and specify this absolute directory location for the include file.  
*/

?>

<img src="<?php echo theme_image("loginsplash.png");?>"><br clear="all">
<h3><?php echo gettext("About Nagios XI"); ?></h3>
<p>
<?php echo gettext("Nagios XI is an enterprise-class monitoring and alerting solution that provides organizations with extended insight of their IT infrastructure before problems affect critical business processes.  For more information on Nagios XI, visit"); ?> 
<a href="http://www.nagios.com/products/nagiosxi/" target="_blank">www.nagios.com/products/nagiosxi/</a>
</p>
<h3><?php echo gettext("Nagios Learning Opportunities"); ?></h3>
<p>
<?php echo gettext("Learn about Nagios"); ?> 
<a href="http://www.nagios.com/services/training" target="_blank"><strong><?php echo gettext("training"); ?></strong></a>
<?php echo gettext("and"); ?> <a href="http://www.nagios.com/services/certification" target="_blank">
<strong><?php echo gettext("certification"); ?></strong></a>.
</p>
<p>
<?php echo gettext("Want to learn about how other experts are utilizing Nagios?  Don't miss your chance to attend the next"); ?>
<a href="http://go.nagios.com/nwcna" target="_blank"><strong><?php echo gettext("Nagios World Conference"); ?></strong></a>.
</p>
<h3><?php echo gettext("Contact Us"); ?></h3>
<p>
<?php echo gettext("Have a question or technical problem? Contact us today:"); ?>
</p>
<table border="0">
<tr><td valign="top"><?php echo gettext("Support"); ?>:</td>
	<td><a href="http://support.nagios.com/forum/" target="_blank"><?php echo gettext("Online Support Forum"); ?></a></td></tr>
<tr><td valign="top"><?php echo gettext("Sales"); ?>:</td><td><?php echo gettext("Phone"); ?>: (651) 204-9102
<br /><?php echo gettext("Fax"); ?>: (651) 204-9103
<br /><?php echo gettext("Email"); ?>: sales@nagios.com</td></tr>
<tr><td valign="top"><?php echo gettext("Web"); ?>:</td>
<td><a href="http://www.nagios.com/" target="_blank">www.nagios.com</a></td></tr>
</table>
<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: header-child.inc.php 75 2010-04-01 19:40:08Z egalstad $

include_once(dirname(__FILE__).'/common.inc.php');

global $lstr;
?>

<!--- CHILD HEADER START -->

<div id="child_popup_layer">
	<div id="child_popup_content">
	<div id="child_popup_close">
	<a id="close_child_popup_link" href="#"><img src="<?php echo get_base_url();?>images/b_close.png" border="0" alt="<?php echo $lstr['CloseAlt']?>" title="<?php echo $lstr['CloseAlt'];?>"> <?php echo $lstr['CloseAlt'];?></a>
	</div>
	<div id="child_popup_container">
	</div>
	</div>
</div>

<!--- CHILD HEADER END -->
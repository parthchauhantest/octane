<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: page-subcomponents-main.php 75 2010-04-01 19:40:08Z egalstad $

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


route_request();

function route_request(){
	global $request;
	
	$pageopt=grab_request_var("pageopt","info");
	switch($pageopt){
		default:
			show_subcomponents_page();
			break;
		}
	}
	

function show_subcomponents_page(){
	global $lstr;

	do_page_start(array("page_title"=>$lstr['SubcomponentsPageTitle']),true);

?>
	<h1><?php echo $lstr['SubcomponentsPageHeader'];?></h1>
	
	<p><?php echo $lstr['SubcomponentsMessage'];?></p>
	
	<div class="subcomponentslist">
	
<?php
	show_subcomponent("subcomponent-nagioscore","nagioscore.png","Nagios Core",$lstr['SubcomponentNagiosCoreDescription']);
	
	show_subcomponent("subcomponent-nagiocorecfg","nagioscorecfg.png","Nagios Core Config Manager",$lstr['SubcomponentNagiosCoreConfigDescription']);
	
	
?>
	
	</div>
	

	
<?php
	do_page_end(true);
	}
	
	

function show_subcomponent($page,$img,$title,$desc){

	$baseurl=get_base_url()."?page=".$page;
	$imgurl=get_base_url()."includes/components/xicore/images/subcomponents/".$img;
?>
	<div class="subcomponent">
	<div class="subcomponentimage">
	<a href="<?php echo $baseurl;?>" target="_top"><img src="<?php echo $imgurl;?>" title="<?php echo $title;?>"></a>
	</div>
	<div class="subcomponentdescription">
	<div class="subcomponenttitle">
	<a href="<?php echo $baseurl;?>" target="_top"><?php echo $title;?></a>
	</div>
	<?php echo $desc;?>
	</div>
	</div>
<?php
	}

?>

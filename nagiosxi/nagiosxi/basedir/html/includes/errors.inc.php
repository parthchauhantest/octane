<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: errors.inc.php 262 2010-08-12 21:22:20Z egalstad $

include_once(dirname(__FILE__).'/../config.inc.php');
include_once(dirname(__FILE__).'/auth.inc.php');
include_once(dirname(__FILE__).'/utils.inc.php');

function handle_db_connect_error($dbh){
?>
	DB Connect Error [<?php echo $dbh;?>]: <?php echo get_sql_error($dbh);?> 
<?php
	}

function handle_sql_error($dbh,$sql){
?>
	SQL: <?php //echo $sql;?>
	SQL Error [<?php echo $dbh;?>] :</b> <?php echo get_sql_error($dbh);?>
<?php
	}

	
function handle_install_needed(){
	header("Location: install.php");
	exit;
	}
	
	
function handle_upgrade_needed(){
	header("Location: install.php");
	exit;
	}
?>
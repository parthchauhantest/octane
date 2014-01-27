<?php

/**
*	Main_Menu class to handle modular menu system for Nagios CCM (UNDER DEVELOPMENT)
*	@TODO future revisions need a way to add, remove, sort, and modify menu items 
*/ 

class Main_Menu
{

	//needs main menu classes
		//menu subclasses with items
	//get, set, add, remove menu items 
	
	var $parentMenus = array(); //array of parent menus 
	private $menuItems = array(); 		//array of objects, child menu items 
	
/*	
	function __construct() {
		$this->add_menu_item('index.php?cmd=view&type=host')
	
	}
*/ 	
	/**
	*	for now this only prints a basic menu for the CCM if the $_SESSION['menu'] =='visible' 
	*
	*/ 
	public function print_menu_html()
	{
		if($_SESSION['menu']=='visible')  
		{
		// development navigation 
			print "<div id='mainNavMenu'>"; 
			print "<a href='index.php?cmd=view&type=host'>Hosts</a><br />";
			print "<a href='index.php?cmd=view&type=service'>Services<br />";
			print "<a href='index.php?cmd=view&type=hostgroup'>Host Groups</a><br />";
			print "<a href='index.php?cmd=view&type=servicegroup'>Service Groups</a><br />";
			print "<a href='index.php?cmd=view&type=hosttemplate'>Host Templates</a><br />";
			print "<a href='index.php?cmd=view&type=servicetemplate'>Service Templates</a><br />";
			print "<a href='index.php?cmd=view&type=contact'>Contacts</a><br />";
			print "<a href='index.php?cmd=view&type=contactgroup'>Contact Groups</a><br />";
			print "<a href='index.php?cmd=view&type=contacttemplate'>Contact Templates</a><br />";
			print "<a href='index.php?cmd=view&type=timeperiod'>Timeperiods</a><br />";
			print "<a href='index.php?cmd=view&type=command'>Commands</a><br />";
			print "<a href='index.php?cmd=view&type=hostescalation'>Host Escalations</a><br />";
			print "<a href='index.php?cmd=view&type=serviceescalation'>Service Escalations</a><br />";			
			print "<a href='index.php?cmd=view&type=hostdependency'>Host Dependencies</a><br />";
			print "<a href='index.php?cmd=view&type=servicedependency'>Service Dependencies</a><br />";
			print "<br />";
			print "<a href='index.php?cmd=admin&type=static'>Static Configurations</a><br />";
			//print "<a href='index.php?cmd=admin&type=bulk'>Bulk Modifications</a><br />";
			print "<a href='index.php?cmd=admin&type=import'>Import Configs</a><br />";
			print "<a href='index.php?cmd=apply'>Write Configs</a><br />";
			print "<a href='index.php?cmd=admin&type=corecfg'>Nagios Main Config</a><br />";
			print "<a href='index.php?cmd=admin&type=cgicfg'>Nagios CGI Config</a><br />";
			print "<br />";
			print "<a href='index.php?cmd=admin&type=user'>Manage CCM Users</a><br />";
			print "<a href='index.php?cmd=admin&type=log'>CCM Log</a><br />";
			print "<a href='index.php?cmd=admin&type=settings'>CCM Settings</a><br />";
			
			print "</div> <!-- mainNavMenu --> "; 
		}
	}	
	
	//array map
	/*	$menuitems = array( $keyID => array( 'title' => '',
															'href'  => '',
															'id'    => '',
															'order' => '',
															'view'  => '',
															)
	*/ 
	public function add_menu_item($href,$id,$order,$title,$target=false,$class=false) {
		$array = array( 'href' =>$href,
						'id' => $id,
						'order' =>  $order,
						'title' => $title,
						'target' => $target,
						'class' => $class  ); 
		$this->menuItems[] = $array; 	
	}
	
	

}




















?>
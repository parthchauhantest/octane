<?php //classes/Db.php  main Database object 




class Db
{
	/**
	*	Nagios CCM database handler (the new one, not nagiosql's)
	*	@author Mike Guthrie
	*	@TODO change connection info to CFG variables 
	*/ 
	
	//retain last query for debugging 
	var $last_query = '';
	var $affected_rows = 0;
	var $message = '';
	var $error = ''; 
	
	/**
	*	establishes DB connection upon initialization 
	*/ 
	function __construct()
	{
		$this->connect_select(); 
	}
	
	/**
	*	close DB connection upon de-initialization 
	*/ 
	function __deconstruct()
	{
		mysql_close(); 
	}
	 
	/**
	*	displays formatted error message
	*	@param string $query the query that was attempted 
	*/  
	//use array of auth info to login to db 
	function display_error($query)
	{
		print '<p class="error">Could complete the query because: <br />'.mysql_error().'</p>';
		print '<p class="error">The query being run was: '.$query.'</p>';
	}
	
	/**
	*	this function is not used in production 
	*	@deprecated no longer used 
	*/ 
	function success($msg='')
	{
		if($msg!='')
		{
			print "<p class='success'>$msg</p>";	
		}	
		else print "<p>Your transaction was successful!</p>";
	}
	
	/**
	*	establishes DB connection upon initialization 
	*/ 
	private function connect_select()
	{
		global $CFG;
		if($dbc=mysql_connect($CFG['db']['server'].':'.$CFG['db']['port'], $CFG['db']['username'], $CFG['db']['password']))
 		{
			mysql_select_db($CFG['db']['database']) or die("Cannot connect to database: {$CFG['db']['database']} "); 
			return true; 
			//else { print '<p class="error">Error selecting database</p>'; }
		}
		else { print '<p class="error">Error connecting to database.</p>'; return; }
	} //end connect_select() 
		
	/**
	*	executes an SQL query, returns results as an associative array OR returns NULL
	*	@param string $query  the SQL query to be run
	* 	@param bool $return do we want the data back? 
	*	@return mixed null | associative array with SQL results, if $return == false return mysql_error() string    
	*/ 
	function query($query,$return=true)
	{
		//$this->connect_select();
		$r = mysql_query($query);
		$this->last_query = $query;
		$this->affected_rows = mysql_affected_rows(); 
		
		if(!$r || !is_resource($r)) {
			$this->error= mysql_error()."<br />".$query; 
			return array(); 
		}
		
		if($return) {			
			$data = array();		
			while($row=(mysql_fetch_assoc($r)))
				$data[] = $row;
					
			return $data;
		}
		else
			return $this->error; 
	}
	
	/*
	*	generic search $tbl WHERE $field = $keywork function 
	*	@return array $data associative array with results. 
	*/ 	
	
	function search_query($tbl, $field, $keyword)
	{
		//$this->connect_select();
		$query = "SELECT * FROM `$tbl` WHERE `$field`=$keyword;";
		$r = mysql_query($query);
		$data = array();
		while($row=(mysql_fetch_assoc($r)))
			$data[] = $row;
		
		return $data;	
		//mysql_close();
	}
	
	/*
	*	generic insert wrapper function  
	*/ 	
	function insert_query($query)
	{
		//execute the query
		if(mysql_query($query)) {			
			print '<p>The DB entry has been added!</p>';
			print '<p><a href="index.php">Return To Main Page</a></p>';
		}
		else //query failed 
			$this->display_error($query);			
				
	}
	
	/*
	*	grabs id and name field from a selected table.  Use for select lists
	*	@param string $type nagios object type (host,service,etc) 
	*	@return array $results associtive array with any results  
	*/ 
	function get_tbl_opts($type)
	{
		///retrieve list of hostnames and id's from DB
		global $FIELDS; 
		$table = "tbl_".$type;
		//change name directive for templates 
		if($type=='hosttemplate' || $type=='servicetemplate' || $type=='contacttemplate') $type = 'template'; 
		//if($type=='parent'
		$query = "SELECT id,".$type."_name FROM `$table` ";	
		//and WHERE clause so objects can't have a relationship to themselves 
		if( isset($FIELDS['exactType']) && $FIELDS['exactType']== $type) // removed $FIELDS['exactType']!='service' 4/4/2013 - MG
			$query .="WHERE {$type}_name!='{$FIELDS['hidName']}'";	
		$query.=" ORDER BY {$type}_name ASC"; 	
		//echo $query."<br />"; 
		$results = $this->query($query);
		return $results;		
	}
	
	
	/*
	*	grabs all fields from commands table.  Used for select lists
	*	@param int $type command type (check command, misc,)  
	*	@return array $results associtive array with any results  
	*/ 
	function get_command_opts($type=1)
	{
		$query = "SELECT * FROM `tbl_command` WHERE `command_type`=$type ORDER BY `command_name`";
		$results = $this->query($query) or die($this->display_error($query));
		return $results;	
	}
	
	/**
	*	checks for table relationships, both master to slave, and slave to master 
	*	@param int $id  object id, primary key 
	*	@param string $tbl  lnkObjectToObject DB table to check 
	*	@param bool $opt used for special calls to get hosts/services/contacts with "use as template" fields
	*	@param bool $master = boolean, master to slave, or slave to master?  
	*	@return array $results assoc array of SQL results | empty array 
	*/			   
	function find_links($id,$tbl, $master,$opt=false)
	{	
		$key = ($master == 'master') ? 'idMaster' : 'idSlave'; 
		$table='tbl_lnk'.$tbl;
		if($opt==2)
		    $query = "SELECT * FROM `$table` WHERE `$key`=$id AND idTable=2;"; //named templates 
		elseif($opt==1)
		    $query = "SELECT * FROM `$table` WHERE `$key`=$id AND idTable=1;"; //default template definition 
		else
			$query = "SELECT * FROM `$table` WHERE `$key`=$id;"; 
			
		//echo $query; 		    
		    
		$results = $this->query($query);
		if(count($results) > 0) return $results;	
		else return array(); 	
	}	
	
	/**
	*	link finder for servicegroup to service relationships 
	*	@param int $id the object ID to find relationships for 
	*	@return string $strings  a string in the following format (hostid::hostgroupID::serviceid) 
	*/ 
	function find_service_links($id)
	{
		$table='tbl_lnkServicegroupToService';
		$query = "SELECT * FROM `$table` WHERE `idMaster`=$id;";
//		$this->connect_select();
		$results = $this->query($query);
		if(count($results) == 0) return array();
		else
		{
			$strings = array(); 
			foreach($results as $r)	$strings[] = $r['idSlaveH'].'::'.$r['idSlaveHG'].'::'.$r['idSlaveS']; 						
			return $strings; 
		}
	}
	
	
	/**
	*	retrieves array of H:host_name : service_description 
	*	@global object $ccmDB 
	*	@return array returns a list of services formatted H:host_name : service_description
	*/ 
	function get_hostservice_opts()
	{
		global $ccmDB; 
		$hostServiceList = array();   
		/*	
		//fetch services 
		$query = "SELECT id,service_description FROM tbl_service WHERE host_name=1;"; 	
		$service_result = $ccmDB->query($query); //returns multi-D array -> [0]=> 'id'= #, 'service_description='desc' 
		$services = array(); 
		//resample array  
		foreach($service_result as $s) $services[$s['id']] = $s['service_description']; 
		
		//fetch hosts 
		$query1 = "SELECT id,host_name FROM tbl_host;"; 	
		$host_result = $ccmDB->query($query1); 
		$hosts = array(); 
		//resample into host array by ID 
		foreach($host_result as $h)  $hosts[$h['id']] = $h['host_name']; 
		
		//fetch relationships 
		$query2 = "SELECT idSlave as host_id,idMaster as service_id FROM tbl_lnkServiceToHost;"; 
		
		*/
		$query = "SELECT a.idSlave as host_id,b.host_name, a.idMaster as service_id,c.service_description FROM tbl_lnkServiceToHost a
			JOIN tbl_host b ON a.idSlave=b.id JOIN tbl_service c ON a.idMaster=c.id ORDER BY b.host_name,c.service_description"; 
		$links = $ccmDB->query($query); 
	
		foreach($links as $lnk)
		{
			//get hostname 
			//$host = $hosts[$lnk['host_id']];
			//get service 
			//$service = $services[$lnk['service_id']]; 
			//create unique array key 
			$key = $lnk['host_id'].'::0::'.$lnk['service_id'];
			//add string to array 
			$hostServiceList[$key] = 'H:'.$lnk['host_name'].' : '.$lnk['service_description']; 		
		}
		//ksort($hostServiceList); 
		
		return $hostServiceList; 
	
	}//end get_hostservice_opts() 
	
	
	/**
	*	takes in an SQL query and retuns the count as an integer
	*	@TODO, change the $query param to be just a table????
	*/ 
	function count_results($query)
	{
		$r = $this->query($query);

		if(isset($r[0]['count(*)']) )
			 return $r[0]['count(*)'];
		if(isset($r[0]['COUNT(*)']) )
			return $r[0]['COUNT(*)'];
		return 0;	
	}
	
	/**
	*	simple data deletion function for SINGLE deletions. 
	*	BUG: $message variable hits a race condition if run from a loop.  Feedback can be unreliable 
	*/	
	function delete_entry($table,$field,$id)
	{
		$query = "DELETE FROM tbl_{$table} WHERE `{$field}`='$id';"; 
		$this->query($query); 		
		if($this->affected_rows == 0)
			$message = "Item $id failed to delete. <br />".mysql_error(); 
		else 
			$message = "Item $id deleted successfully!<br />"; 	 
			
		return $message; 	
	}
	
} //end Db class 


?>

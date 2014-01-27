<?php //ccm_log.inc.php



/**
*	handles CCM log management
*
*/
function ccm_log()
{
	global $myConfigClass; 
	global $myDataClass;
	global $ccmDB;
	
	//input request vars	
	$limit = ccm_grab_request_var('pagelimit',''); 
	$cmd = ccm_grab_request_var('cmd','');
	//$type = ccm_grab_request_var('type',''); 
	$id = ccm_grab_request_var('id',''); 
	$search = ccm_grab_request_var('search','');  
	$page = ccm_grab_request_var('page',1); 
	$submitted = ccm_grab_request_var('submitted',false);
	$delete = ccm_grab_request_var('delete_single',false); 
	$delete_multi = ccm_grab_request_var('delete_multi',false); 
	
	$query = 'SELECT COUNT(*) from tbl_logbook';
	$resultCount = $ccmDB->count_results($query); 
		
	$retClass='invisible';
	$returnMessage = '';
		
	if($submitted) {
		$errors = 0;
		require_once(INCDIR.'delete_object.inc.php'); 
		//delete or search for any requested items
		if(intval($delete) !=0) {
			$returnMessage .= $ccmDB->delete_entry('logbook','id',$delete);
			$retClass = strpos($returnMessage,'failed') ? 'error' : 'success';
		}	
			
		if($delete_multi == 'true') { 
			$startcount = $resultCount; 
//			echo "START: $startcount <br />"; 
			$checks = ccm_grab_request_var('checked',array());
			$selectedcount = count($checks); 
//			echo "SELECTED: $selectedcount <br />"; 
			foreach($checks as $c) 
				$ccmDB->delete_entry('logbook','id',$c);
			
			$diff = $ccmDB->count_results($query); 
			//verify correct number deleted 
			if($diff == ($startcount - $selectedcount)) {
				$returnMessage = "$selectedcount ".gettext("items deleted successfully")."!<br />"; 
				$retClass = 'success'; 	
			}	
			else {
				$returnMessage = ($startcount - $diff).gettext(" of ").$selectedcount.gettext("selected items were deleted").".<br />"; 
				$retClass = 'error';
			}					 									
		}			
	}
	
	//page limit 
	//if no post was submitted, use session limit  
	if($limit=='') $limit = $_SESSION['limit']; 
	//update session limit if post was submitted 
	if($limit != '') $_SESSION['limit'] = $limit;
	//override limit to match result count if turned off 
	$limit = ($limit=='none') ? $resultCount : $limit; 	
	
	//initializing variables 
	$query = 'SELECT * FROM tbl_logbook'; //get all log entries within limit 
	if($search !='')
		$query .= "WHERE (`user` LIKE '%".$search."%' OR `entry` LIKE '%".$search."%' ";
	$query .=" ORDER BY `time` DESC  ";	 
	
//	if($limit !=='')
//		$query .=" LIMIT ".($page * $limit);
		
	
	//get the main result set 
	$sqlData = $ccmDB->query($query); //object configuration data 
//sql_output($query);
	//get the result count 
	$query = 'SELECT count(*) FROM tbl_logbook';
	if($search !='')
		$query .= "WHERE (`user` LIKE '%".$search."%' OR `entry` LIKE '%".$search."%'"; 
	$resultCount = $ccmDB->count_results($query);
	 		
	//pagination
	$rowCounter = 0;			
	$pagenumbers = ''; //default is empty string 
	 
	//do pagination if necessary 
	if($resultCount > $limit) {
		//figure results for current table
		if($page==1) $start = 0;   
		else $start = (($page-1) * $limit);   
		//echo "START IS: $start"; 
		$end = (($start + $limit) > $resultCount) ? $resultCount : ($start+$limit);   		
		//figure results for pagenumbers, pass to function 
		$pagenumbers .= do_pagenumbers($page,$start,$limit,$resultCount,'log');
	}	
	else { //display results	without paging 		
		$start=0; 
		$end = $resultCount; 	
	}
		
	/////////////////BEGIN HTML BUILD //////////////////////
	$html = "
	<div id='contentWrapper'> 
	<h1 id='objectHeader'>".gettext('CCM Log')."</h1> 
	<div id='pagenumbersDiv'>{$pagenumbers}</div> <!-- pagenumbers div -->	
	
	<div id='returnContent' class='{$retClass}'>{$returnMessage}
		<div id='closeReturn'>
			<a href='javascript:void(0)' id='closeReturnLink' title='Close'>".gettext("Close")."</a>
		</div>
	</div> 
	
	<div id='ccmtablewrapper'> 
	
	  <!-- begin form --> 
	  <form id='frmDatalist' method='post' action='index.php'> 	  
		<div id='tableTopper'>			
			<div id='checkAllDiv'><a id='checkAll' href='javascript:checkAll()'>".gettext("Check All")."</a></div>
			<div id='searchBox'>
				<label for='search'>".gettext("Search")."</label>
				<input type='text' name='search' id='search' value='{$search}' />
				<input class='ccmButton' type='button' onclick='actionPic(\"admin\",\"\",\"\")' id='submitSearch' value='".gettext('Search')."' />
				<input class='ccmButton' type='button' id='clear' name='clear' value='".gettext("Clear")."' />
			</div>	
			<div id='resultCounter' class='ccm-label'>".gettext("Displaying")." {$start}-{$end} ".gettext("of")." {$resultCount} ".gettext("results")."</div>
		</div> <!-- end tableTopper --> 
	
	<!-- table header -->  
	<table class='standardtable ccmtable'>									
				<tr><th>&nbsp; </th><th>".gettext("Time")."</th>
				<th>".gettext("User")."</th><th>".gettext("IP Address")."</th>
				<th>".gettext("Entry")."</th><th>ID</th><th>".gettext("Delete")."</th></tr>
	";
	
	//////////////////////////////////Table Rows Loop//////////////////////////	
	for($i=$start; $i < $end; $i++) 
//	foreach($sqlData as $d) 
	//foreach($sqlData as $data) //for each object 
	{
		$d = $sqlData[$i];					
		//for table row class 
		$rowCounter % 2 == 1 ? $class = 'odd' : $class = 'even';
		$rowCounter++;		
		
		//begin heredoc string 
		$row=<<<ROW
		
	<tr class='{$class}'>
		<td><input type='checkbox' class='checkbox' name='checked[]' value='{$d['id']}'  id='chbId{$rowCounter}' /></td>
		<td>{$d['time']}</td>
		<td>{$d['user']}</td>
		<td>{$d['ipadress']}</td>
		<td>{$d['entry']}</td>
		<td>{$d['id']}</td>
						
		<!-- actions 	 action_command('command', 'id', 'host_name')	-->
 		<td> <div id="iconsDiv">	
				<img src='/nagiosql/images/delete.gif' alt='img' title='Delete' onclick="delete_single_log('{$d['id']}')" />			
			</div> <!-- end icons div -->
		</td>
		
	</tr>
		
ROW;
//end heredoc string 
		$html .= $row;				
	}	//end foreach loop 
	/////////////////////////End Table Rows Loop ////////////////
	//handle empty table sets
	if($start==0 && $end ==0) $html.="<tr><td colspan='6'>".gettext("No results returned from logbook table")."</td></tr>"; 	
	//close out table after loop 
	$html .= "</table><br />\n\n";
	
	$tableControls="
	<div id='tableControlsBottom'>
		
		<div id='addApplyButtons'>		      
		      <!-- hidden nav arguments -->		    		    
		      <input name='action' type='hidden' id='hiddenAction' value='false' />
		      <input name='submitted' type='hidden' id='submitted' value='true' />
		      <input name='cmd' id='cmd' type='hidden' value='admin' />
		      <input name='type' id='type' type='hidden' value='log' />
		      <input name='id' id='id' type='hidden' value='{$d['id']}' />
		      <input name='returnUrl' id='returnUrl' type='hidden' value='index.php?cmd=admin&type=log' />
		      <input name='token' id='token' type='hidden' value='{$_SESSION['token']}' />
		      <!-- special delete control for logs -->
		      <input name='delete_single' id='delete_single' type='hidden' value='false' />
		      		      
	   </div><!--end addApplyButtons-->
	   
   	<div id='withCheckedDiv'>
	      <label for='delete_multi'>".gettext("With Checked").": </label>
	      	<select name='delete_multi' id='delete_multi'>
	          <option value='false'>&nbsp;</option>
	          <option value='true'>".gettext("Delete")."</option> 
	        </select>
	       <input type='submit' class='ccmButton' value='".gettext("Go")."' /> 
      </div><!--end withCheckedDiv -->  
		<div id='pageLimitDiv'>
      	<label for='pagelimit'>".gettext("Limit Results")."</label>
      	<select name='pagelimit' id='pagelimit' onchange='actionPic('admin','','')'>
      		<option id='limit15' value='15'>15</option>
      		<option id='limit30' value='30'>30</option>
      		<option id='limit50' value='50'>50</option>      		
      		<option id='limit100' value='100'>100</option>
      		<option id='limit250' value='250'>250</option>
      		<option id='limitnone' value='none'>".gettext('None')."</option> 
      	</select>
			<script type='text/javascript'>	
				limit ='{$_SESSION['limit']}'; 
				$('#limit'+limit).attr('selected','selected'); 
			</script>      	
      	
      </div><!-- end pageLimitDiv -->
      {$pagenumbers}     
    </div><!--end tableControlsBottom div -->
    </form>"; 

	$html .= $tableControls;
	$html .= " 	</div> <!-- form wrapper div -->	
				</div> <!-- end contentWrapper -->";  	
	echo $html; 			


}





?>
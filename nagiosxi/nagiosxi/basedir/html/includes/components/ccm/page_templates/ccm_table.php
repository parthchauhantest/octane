<?php //ccm_table.inc.php 

/** function ccm_table() 
* 	generates an html data table from an SQL associative array, 
*
*	this loads the object listings for all config objects and handles copy,download,info,delete commands
*	as well as the search bar, pagination, etc.  This is the main display function of the CCM
*
*	@param array $args storage array for all relevant information for 
* 	@param	array $args['data'] - associative array of all DB table data for the selected table 
* 	@param	string $args['nameKey'] - array index to identify item (also table header) 
*	@param	string $args['descKey'] - Descriptive array index to call  (also table header)
*	@param 	array $returnContent array(int, string) code and status message to send back to the main table 
*	@global	array $_REQUEST - strings: limit,cmd,type,id,search - used to determine table data 
*	@return	string $html - returns large html string of table data for nagios objects 
*/ 
function ccm_table($args,$returnContent=array(0,''))
{
	//dump_request(); 

	global $myConfigClass; 
	global $ccmDB;
				
	//input request vars	
	$limit = ccm_grab_request_var('pagelimit',''); 
	//$cmd = ccm_grab_request_var('cmd','');
	$type = ccm_grab_request_var('type',''); 
	$id = ccm_grab_request_var('id',''); 
	$search = ccm_grab_request_var('search',''); 
	$session_search = ccm_grab_array_var($_SESSION,$type.'_search',''); 	
	$page = ccm_grab_request_var('page',1); 
	$orderby = ccm_grab_request_var('orderby',''); 
	$sort = ccm_grab_request_var('sort','ASC');
	$sortlist = ccm_grab_request_var('sortlist',false); 

	//if($sortlist==false || $sortlist=='false')
	//	$sort='ASC'; 
	
	//initializing variables 
	$sqlData = $args['data']; //object configuration data 
	$returnCode = $returnContent[0]; 
	$returnMessage = $returnContent[1]; 
	$selectConfigNames = config_names_html($type); //used for services page only 
	$sync_status = ''; //either a single line at the page top, or a td value for hosts/services 
	$sync_header = ''; //table header for sync status (host/service only), else empty string 
	
	//process args and prepare variables for html string 
	$th_name = ucwords(str_replace('_', ' ', $args['keyName'])); //turn array key into a title	 
	$th_desc = $args['keyDesc'] != '' ? ucwords(str_replace('_', ' ', $args['keyDesc'])) : gettext("Description"); //turn array key into description 

	//if we're here, our command is now view, otherwise we resubmit the last command that was given
	$cmd='view'; 
	$returnUrl = ($cmd != '' && $type != '') ? "?cmd=$cmd&type=$type" : ''; 
	
	//search?
	if($search!='') { //if there's a search entry
		$_SESSION[$type.'_search']=$search;
	}
	elseif($session_search != '') //use stored search value if its there 
		$search = $session_search; 
 	
	//objects with single config file will display sync status at the top of the page 
	if($type!='host' && $type!='service')  
	{
		$tbl = 'tbl_'.$type; 
		$myConfigClass->lastModified($tbl,$strTimeTable,$strTimeFile,$syncMessage);
		$sync_status = "<div id='singleSyncDiv'>{$syncMessage} </div>"; 
		//echo "STAT $sync_status"; 
	}
	else $sync_header = "<th class='sortsync'>".gettext("Sync Status")."</th>"; 	 	
	
	//return messages content? 
	if($returnContent[1]=='') $retClass='invisible';
	else $retClass = ($returnCode ==1) ? "error" : "success"; 
	
	//pagination
	$rowCounter = 0;
	$resultCount = count($sqlData); 			
	$pagenumbers = ''; //default is empty string 
	 
	//page limit 
	
	//update session limit if post was submitted 
	if($limit != '') $_SESSION['limit'] = $limit;	
	//if no post was submitted, use session limit  
	if($limit=='') $limit = isset($_SESSION['limit']) ? $_SESSION['limit'] : $_SESSION['default_limit']; 
	//override limit to match result count if turned off 
	$limit = ($limit=='none') ? $resultCount : $limit; 

	//do pagination if necessary 
	if($resultCount > $limit) 
	{
		//figure results for current table
		if($page==1) $start = 0;   
		else $start = (($page-1) * $limit);   
		//echo "START IS: $start"; 
		$end = (($start + $limit) > $resultCount) ? $resultCount : ($start+$limit);   		
		//figure results for pagenumbers, pass to function 
		$pagenumbers .= do_pagenumbers($page,$start,$limit,$resultCount,$type);
	}	
	else //display results	without paging 
	{		
		$start=0; 
		$end = $resultCount; 	
	}
	
	session_write_close();
	
	/////////////////BEGIN HTML BUILD //////////////////////
	$html = "
	<div id='contentWrapper'> 
	<h1 id='objectHeader'>".get_page_title($type,true)."</h1> 
	<div id='pagenumbersDiv'>$pagenumbers</div> <!-- pagenumbers div -->	
	
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
				<input class='ccmButton' type='button' onclick='actionPic(\"view\",\"\",\"\")' id='submitSearch' value='".gettext("Search")."' />
				<input class='ccmButton' type='button' id='clear' name='clear' value='".gettext("Clear")."' />
			</div>	
				{$selectConfigNames} <!-- config_name filter for services page only, else '' -->
				{$sync_status} <!-- sync status for single cfg files only, else '' -->
			<div id='resultCounter' class='ccm-label'>".gettext("Displaying")." {$start}-{$end} ".gettext("of")." {$resultCount} ".gettext("results")."</div>	
		</div> <!-- end tableTopper --> 

	
	<!-- table header -->  
	<table class='standardtable ccmtable'>												<!-- sync header only exists for host/services page -->
				<tr><th>&nbsp; </th>
				<th class='sortname'>{$th_name}</th>
				<th class='sortdesc'>{$th_desc}</th>
				<th class='sortactive'>".gettext("Active")."</th>
				{$sync_header}
				<th>".gettext("Actions")."</th>
				<th class='sortid'>ID</th>
				</tr>
	";
	
	//////////////////////////////////Table Rows Loop//////////////////////////	
	for($i=$start; $i < $end; $i++) 
	//foreach($sqlData as $data) //for each object 
	{	
		$id = $sqlData[$i]['id']; //var for action commands, object ID 
		$name = $sqlData[$i][$args['keyName']]; //name field
		if($type=='host' || $type=='service')
		{  
			$myConfigClass->lastModifiedDir($name,$id,$type,$strTime,$strTimeFile,$intOlder);
			$sync_status = ($intOlder == 0) ? '<td>'.gettext('Synced To File').'</td>' : '<td><span class="urgent">'.gettext('Sync Missed').'</span></td>';  
		}
		//sync status is already displayed above
		else $sync_status = '';   
				
		//for table row class 
		$rowCounter % 2 == 1 ? $class = 'odd' : $class = 'even';
		$rowCounter++;
		//set $desc if blank 
		$desc = isset($sqlData[$i][$args['keyName']]) ? htmlentities($sqlData[$i][$args['keyDesc']]) : "";
		
		//special case for service escalations 
		process_desc_exceptions($desc,$type,$sqlData,$i,$args,$id);
		//echo "DESC: $desc"; 
						 
		$active = is_active($sqlData[$i]['active'],$id,$name); 
		//used for config download 
		$line = ($type=='host' || $type=='service') ? $id : 0; 
		$clean_desc = htmlspecialchars_decode($desc);
		//begin heredoc string 
		$row=<<<ROW
		
	<tr class='{$class}'>
		<td><input type='checkbox' class='checkbox' name='checked[]' value='{$id}'  id='chbId{$rowCounter}' /></td>
		<td><a href="javascript:actionPic('modify', '{$id}', '')" title="Modify">{$name}</a></td>
		<td>{$clean_desc}</td>
		<td>{$active}</td>
		   {$sync_status} <!-- sync status td only exists for host/service pages, else empty '' --> 
				
		<!-- actions 	 action_command('command', 'id', 'host_name')	-->
 		<td class='iconsTd'><div id="iconsDiv">
			<img src='images/editsettings.png' alt='img' height="16" width="16" title='Modify' onclick="actionPic('modify', '{$id}', '')" />
			<img src='images/copy.gif' alt='img' height="16" width="16" title='Copy' onclick="actionPic('copy', '{$id}', '')" />		
			<img src='images/delete.png' alt='img' height="16" width="16" title='Delete' onclick="actionPic('delete', '{$id}', '{$name}')" />			
			<img src='images/download.png' alt='img' height="16" width="16" title='View Text Config' onclick="actionPic('download', '{$line}', '{$_SESSION['domain']}')" />
			<img src='images/info_small.png' alt='img' height="14" width="14" title='Info' onclick="actionPic('info', '{$id}', '{$name}')" />
			</div> <!-- end icons div -->
		</td>
		<td>{$id}</td>
	</tr>
		
ROW;
//end heredoc string 
		$html .= $row;				
	}	//end foreach loop 
	/////////////////////////End Table Rows Loop ////////////////
	//handle empty table sets
	if($start==0 && $end ==0) $html.="<tr><td colspan='6'>".gettext("No results returned from")." {$type} ".gettext("table")."</td></tr>"; 	
	//close out table after loop 
	$html .= "</table><br />\n\n";
	
	if ($limit>=250)
        $limit="none";
	$tableControls="
	<div id='tableControlsBottom'>
		
		<div id='addApplyButtons'>
			<input name='subAdd' class='ccmButton' type='button' id='subAdd' onclick='addDataset()' value='".gettext("Add New")."' />
		      <input name='applyConfig' class='ccmButton' type='button' id='applyConfig' onclick='apply_config()' value='".gettext("Apply Configuration")."' />
		      
		      <!-- hidden nav arguments -->		    		    
		      <input name='action' type='hidden' id='hiddenAction' value='false' />
		      <input name='submitted' type='hidden' id='submitted' value='true' />
		      <input name='cmd' id='cmd' type='hidden' value='{$cmd}' />
		      <input name='type' id='type' type='hidden' value='{$type}' />
		      <input name='id' id='id' type='hidden' value='{$id}' />
		      <input name='objectName' id='objectName' type='hidden' value='' />
		      <input name='mode' type='hidden' id='mode' value='insert' /> 
		      <input name='returnUrl' id='returnUrl' type='hidden' value='index.php?cmd={$cmd}&type={$type}' />
		      <input name='token' id='token' type='hidden' value='{$_SESSION['token']}' /> 
			  <input name='orderby' id='orderby' type='hidden' value='{$orderby}' /> 
			  <input name='sort' id='sort' type='hidden' value='{$sort}' />
			  <input name='typeName' id='typeName' type='hidden' value='{$args['keyName']}' />
			  <input name='typeDesc' id='typeDesc' type='hidden' value='{$args['keyDesc']}' />
		      <input name='sortlist' id='sortlist' type='hidden' value='{$sortlist}' />		      
	   </div><!--end addApplyButtons-->
	   
   	<div id='withCheckedDiv'>
	      <label for='select'>".gettext("With Checked").": </label>
	      	<select name='selModify' id='selModify'>
	          <option value='none'>&nbsp;</option>
	          <option value='delete_multi'>".gettext("Delete")."</option>
	          <option value='copy_multi'>".gettext("Copy")."</option>
	          <option value='activate_multi'>".gettext("Activate")."</option>
	          <option value='deactivate_multi'>".gettext("Deactivate")."</option>    
	        </select>
	       
	       <a href='javascript:void(0)' id='goButton' class='ccmButton addpadding' title='Go'>".gettext("Go")."</a> 
      </div><!--end withCheckedDiv -->  
		<div id='pageLimitDiv'>
      	<label for='pagelimit'>".gettext("Limit Results")."</label>
      	<select name='pagelimit' id='pagelimit' onchange=\"actionPic('view','','')\">
      		<option id='limit15' value='15'>15</option>
      		<option id='limit30' value='30'>30</option>
      		<option id='limit50' value='50'>50</option>      		
      		<option id='limit100' value='100'>100</option>
      		<option id='limit250' value='250'>250</option>
      		<option id='limitnone' value='none'>".gettext('None')."</option> 
      	</select>
			<script type='text/javascript'>	
				limit ='{$limit}'; 
				$('#limit'+limit).attr('selected','selected'); 
			</script>      	
      	
      </div><!-- end pageLimitDiv -->
      {$pagenumbers}     
    </div><!--end tableControlsBottom div -->
    </form>"; 

	$html .= $tableControls;
	$html .= " 	</div> <!-- form wrapper div -->	
				</div> <!-- end contentWrapper -->";  	
	return $html; 			
	
}//end ccm_table() 


/**  config_names_html()
*	if $type==service, creates html for "config_name" filter for services, else returns empty string 
*	@param string $type  nagios object type (host,service, contact, etc) 
*	@parem array $names array of config_name tbl options, OR empty array if $type!='service' 
*	@return string $html html select list, or '' 
*/ 
function config_names_html($type)
{
	global $ccmDB; 
	if($type!='service') return ''; 
	$filter = ccm_grab_array_var($_SESSION,'name_filter',''); 
	//print_r($names); 	
	//return ''; 
	$html = '<div id="config_filter_box"><label for="name_filter">'.gettext('Filter by Config Name').': </label>
					<select name="name_filter" id="name_filter" onchange="actionPic(\'view\',\'\',\'\')">'; 
	$html .= "<option value='null'>&nbsp;</option>\n"; 
	//option list for config name filter 
	$query = "SELECT `config_name` FROM tbl_service GROUP BY HEX(`config_name`) ORDER BY `config_name`  "; 
	$names = $ccmDB->query($query);
	foreach($names as $n)
	{	 
		$html .="<option ";
		if($filter !='' && $filter==$n['config_name']) $html .="selected='selected' "; 
		$html.="value='{$n['config_name']}'>{$n['config_name']}</option>"; 
	
	}
	$html .= "</select></div>"; 
	return $html; 	 
}



/**	function do_page_numbers() 
*
*	creates page numbers based on how many results are being processed for the tables
*	@param int $page the current page
*	@param int $start calculated starting number for results
*	@param int $limit the session result limit 
*	@param int $resultCount total number of results for the object selected  
*	@param string $type the nagios object type (host,service, contact, etc) 
*	@return string html string of page numbers with link 
*
*/
function do_pagenumbers($page,$start,$limit,$resultCount,$type)
{
	$cmd = ($type =='log' || $type=='user') ? 'admin' : 'view';     
	 //main function variables 
    $pageCount = $resultCount / $limit; 
    if( ($resultCount % $limit) > 0) $pageCount++;   

	//sorting options 
	$sortlist = ccm_grab_request_var('sortlist',false); 
	$sort = ccm_grab_request_var('sort','ASC'); 
	$orderby = ccm_grab_request_var('orderby',''); 
	
    $link_base = "index.php?cmd={$cmd}&type={$type}"; 
	
	//if the list is being sorted
	if($sortlist != false && $sortlist!='false')
		$link_base.="&sortlist=true&orderby={$orderby}&sort={$sort}"; 
	
    $pagenums = "<div id='innerPageNumbersDiv'>\n
    					<div class='pagenumbers'>".gettext("Pages").": </div>\n"; //start html string 
       
    //BACK arrow 
    $back_arrow = '';
    $back_arrow_entities = '&laquo;';
    if ($page > 1) 
    {
        //$link = $link_base . '&start='.($start-$limit).'&page='.($page-1);   
        $link = $link_base . '&page='.($page-1);       
        $back_arrow = "<a href='$link' title='".gettext("Previous Page")."' class='pagenumbers'>$back_arrow_entities</a>\n";
    } 
    $pagenums .= "$back_arrow";

    // Build the direct page links
    $begin = 0; 
    for($i = 1; $i <= $pageCount; $i++)
    {
    		  
        //if end is greater than total results, set end to be the resultCount
        //$link = $link_base . "&start=$begin&page=".($i); 
			$link = $link_base . "&page=".($i);
        //check if the link is the current page
        //if we're on current page, don't print a link
        if($i == $page) $pagenums .= "<div class='pagenumbers deselect'> $i </div>";
        else $pagenums .= "<a class='pagenumbers' href='$link'> $i </a>";
		  $begin = ($limit * $i);
        //submit a hidden post page number    
        //$begin = ($begin + $limit) < $resultsCount ? ($begin + $limit) : $resultsCount;
    }

    // FORWARD arrow 
    $forward_arrow = '';
    $forward_arrow_entities = '&raquo;';
    if ( ($start + $limit)  < $resultCount) 
    {
       // $link = $link_base . '&start='.($start+$limit)."&page=".($page+1); 
       $link = $link_base . "&page=".($page+1);
        $forward_arrow = "<a href='$link' title='".gettext("Next Page")."' class='pagenumbers'>$forward_arrow_entities</a>\n";
    } 
    $pagenums .= $forward_arrow;
	 //close page numbers div 
    $pagenums .= "\n\n</div>\n<!-- end innerPageNumbersDiv-->\n";
    return $pagenums;
}    //end do_pagenumbers()   


function process_desc_exceptions(&$desc,$type,$sqlData,$i,$args,$id){
	global $ccmDB;
	//$desc = '';

	if(($type=='serviceescalation' || $type=='servicedependency') && $sqlData[$i][$args['keyDesc']] ==1) {
		$table = ($type=='servicedependency') ? "tbl_lnkServicedependencyToService_S" : "tbl_lnkServiceescalationToService";
		$query = "SELECT `service_description` FROM `tbl_service` LEFT JOIN `{$table}` ON `id`=`idSlave` WHERE `idMaster`=".$id;
		$names = $ccmDB->query($query); 
		$desc = '';
		foreach($names as $array)  $desc .= $array['service_description'].',';  
		$desc = substr($desc, 0, strlen($desc)-1); 		
	}
	if(($type=='hostescalation' || $type=='hostdependency') && $sqlData[$i][$args['keyDesc']] ==1) {		 
		$table = ($type=='hostdependency') ? "tbl_lnkHostdependencyToHost_H" : "tbl_lnkHostescalationToHost"; 
		$query = "SELECT `host_name` FROM `tbl_host` LEFT JOIN `{$table}` ON `id`=`idSlave` WHERE `idMaster`=".$id;
		$names = $ccmDB->query($query); 
		$desc = '';
		foreach($names as $array)  $desc .= $array['host_name'].','; 
		$desc = substr($desc, 0, strlen($desc)-1); 
	}
	
	//echo $ccmDB->last_query; 
}



?>

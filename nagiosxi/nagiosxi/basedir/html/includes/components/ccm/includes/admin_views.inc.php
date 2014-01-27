<?php //misc_views.inc.php   handles non-object configuration pages of the CCM 


function ccm_import_page() 
{
	//process input variables 
	$search = ccm_grab_request_var('txtSearch','');
	$submitted = ccm_grab_request_var('importsubmitted',false);
	$filenames = ccm_grab_request_var('selImportFile',array()); 
	$overwrite   = ccm_grab_request_var('chbOverwrite',false);	
	//feedback based on if anything was imported, import_configs() defined below 	
	$feedback = ($submitted) ? import_configs($filenames,$overwrite,$returnClass) : '';  
	
	//page content 	
?>
	<div id='contentWrapper'>
	<h2 id='objectHeader'><?php echo gettext("Configuration Import"); ?></h2>
	<br />
	<div id='returnContent' class='<?php echo $returnClass; ?>'><?php echo $feedback; ?>
	</div>
	<form action='index.php' method='post' id='formInput' name='frmInput'>
	<label for='txtSearch'><?php echo gettext("Search"); ?>: </label>
	<input type="text" style="width:120px" value="<?php echo $search; ?>" name="txtSearch" id='txtSearch' />
	<img width="18" height="18" style="cursor: hand; cursor:pointer;" onclick="document.forms[0].submit()" title="Search" alt="Search" src="/nagiosql/images/lupe.gif" />
	<img width="18" height="18" style="cursor: hand; cursor:pointer;" onclick="$('#txtSearch').val('');document.forms[0].submit();" title="Delete" alt="Delete" src="/nagiosql/images/del.png">
	
	<select id="selImportFile" multiple="multiple" size="10" style='width:80%;' name="selImportFile[]">
<?php 
	//fetch file list here, function defined below  
	populate_config_files($search); 
?>
    </select>
    <br />
    <label for='chbOverwrite'> <?php echo gettext("Overwrite Database"); ?> </label>
    <input type="checkbox" checked="checked" value="1" id="chbOverwrite" class="checkbox" name="chbOverwrite" />
    <br />
    <p><?php echo gettext("To prevent errors or misconfigurations, you should import your configurations in an useful order. We recommend importing in the following order"); ?>:<br />
    <br />
    <strong><em>commands -> timeperiods -> contacttemplates -> contacts -> contactgroups -> <br />
    hosttemplates -> hosts -> hostgroups -> servicetemplates -> services -> servicegroups</em></strong>
    <br /><br />
    <em><?php echo gettext("The CCM import tool does not currently support object names that start with '#', or group exclusions that start with '!'"); ?></em><br />
    <br /> 
    <strong><?php echo gettext("Check your configurations after import before saving to file!"); ?></strong>
    </p>
    <br />
    
	<input type="submit" class='ccmButton' value="<?php echo gettext('Import'); ?>" id="subForm" name="subForm" />
	<input type="button" class='ccmButton' value="<?php echo gettext('Abort'); ?>" id="abort" name="abort" onclick="document.forms[0].reset();"/>
	<input type="hidden" value="true" id="importsubmitted" name="importsubmitted" />
	<input type='hidden' name='cmd' value='admin' />
	<input type='hidden' name='type' value='import' />
	
	
	</form>
	</div> <!-- end contentWrapper div -->


<?php 
}



/**
*	function to display the main nagios.cfg in a webform
*	
*/
function ccm_corecfg() 
{
	global $myConfigClass;
	$myConfigClass->getConfigData("nagiosbasedir",$nagiosEtc);
	
	//handle request variables
	$submitted = ccm_grab_request_var('submitted',false); 
	$newcfg = ccm_grab_array_var($_REQUEST,'newcfg',''); //don't process this text at all, use raw submission 	
	$filecheck = false;
	$feedback = ''; 
	$contents = '';
	$returnClass = '';
	$info = gettext("This file contains all global configuration information for Nagios Core.");
	$title = gettext('Nagios Core Configuration');	
	$nagioscfg = $nagiosEtc.'nagios.cfg';
	 
	if(file_exists($nagioscfg) && is_writeable($nagioscfg)) 
		$filecheck = true; 
		
	if($submitted && $newcfg != '') {
		//save file 
		if($filecheck) {
			$r = file_put_contents($nagioscfg,$newcfg); 
			$feedback = ($r) ? gettext("File saved successfully!")."<br />" : gettext("Unabled to save file")." :$nagioscfg, ".gettext("check permissions").".\n"; 
			$returnClass = 'success';
		}				
		else {
			$feedback= gettext("Unable to access nagios.cfg file at").": {$nagioscfg}.  ".gettext("Check permissions and verify write access").".\n";
			$returnClass = 'error';	
		}	
		
		$feedback .="<div id='closeReturn'>
				<a href='javascript:void(0)' id='closeReturnLink' title='Close'>".gettext("Close")."</a>
				</div>";	
	}	 

	if($filecheck)	
		$contents = file_get_contents($nagioscfg);
	else
		$contents = gettext("ERROR: Unable to read / write nagios.cfg file.  Check permissions!"); 	
		
	//begin page content	  
	text_editor_page($title,$returnClass,$feedback,$contents,$info,'corecfg')	;
				
} //end ccm_corecfg() 


/**
*	function to display the cgi.cfg in a webform
*	
*/
function ccm_cgicfg() 
{
	global $myConfigClass;
	$myConfigClass->getConfigData("nagiosbasedir",$nagiosEtc);
	
	//handle request variables
	$submitted = ccm_grab_request_var('submitted',false); 
	$newcfg = ccm_grab_array_var($_REQUEST,'newcfg',''); //don't process this text at all, use raw submission 	
	$filecheck = false;
	$feedback = ''; 
	$contents = '';
	$returnClass = '';
	$info = gettext("This file contains all CGI configuration information for Nagios Core.");
	$title = gettext('Nagios Core CGI Config');	
	$nagioscfg = $nagiosEtc.'cgi.cfg'; 
	
	if(file_exists($nagioscfg) && is_writeable($nagioscfg))
		$filecheck = true; 
		
	if($submitted && $newcfg != '') {
		//save file 
		if($filecheck) {
			$r = file_put_contents($nagioscfg,$newcfg); 
			$feedback = ($r) ? gettext("File saved successfully!")."<br />" : gettext("Unable to save file")." :$nagioscfg, ".gettext("check permissions").".\n"; 
			$returnClass = 'success';
		}				
		else {
			$feedback= gettext("Unable to access nagios.cfg file at").": {$nagioscfg}.  ".gettext("Check permissions and verify write access").".\n";
			$returnClass = 'error';	
		}	
		
		$feedback .="<div id='closeReturn'>
				<a href='javascript:void(0)' id='closeReturnLink' title='".gettext("Close")."'>".gettext("Close")."</a>
				</div>";	
	}	 

	if($filecheck)	
		$contents = file_get_contents($nagioscfg);
	else
		$contents = gettext("ERROR: Unable to read / write cgi.cfg file.  Check permissions!"); 	
		
	//begin page content	  
	text_editor_page($title,$returnClass,$feedback,$contents,$info,'cgicfg');	
				
} //end ccm_corecfg() 



function text_editor_page($title,$returnClass,$feedback,$contents,$info,$type) 
{
?>
	<div id='contentWrapper'>
	<h2><?php echo $title; ?></h2> 
	<br />
	<div id='returnContent' class='<?php echo $returnClass; ?>'>
		<?php echo $feedback; ?>
	</div>
	<br />
	<div id='centerdiv'>
	<form action='index.php' method='post' id='formInput' name='frmInput'>
	<textarea name="newcfg" rows="25" cols="110" id='newcfg'><?php echo $contents; ?></textarea><br />

    <p><?php echo $info; ?></p>
    <br />
    
	<input type="submit" class='ccmButton' value="<?php echo gettext("Save"); ?>" id="subForm" name="subForm" />
	<input type="button" class='ccmButton' value="<?php echo gettext("Abort"); ?>" id="reset" name="reset" onclick="javascript:window.location='index.php';"/>
	<input type="hidden" value="true" id="submitted" name="submitted" />
	<input type='hidden' name='cmd' value='admin' />
	<input type='hidden' name='type' value='<?php echo $type; ?>' />
		
	</form>
	</div><!-- end centerdiv -->
	</div> <!-- end contentWrapper div -->	
<?php	 

}//end text_editor_page() 



/**
*	creates html for add/edit user form
*
*
*/ 
function manage_user_html() 
{
	global $ccmDB;
	global $CFG; 

	$mode = ccm_grab_request_var('cmd','insert');
	$id = ccm_grab_request_var('id',false);  
	//form defaults 
	$username = '';
	$alias = ''; 
	$password=ccm_random_string(6);
	$confirm = ''; 
	$access = '11111111'; //not used in CCM
	$ws_auth = false; 		//not used in CCM 
	$active = 'checked="checked"'; 
	$languages = $CFG['languages']; 
	$lang = 'en_US'; 
	
	//editing an existing user 
	if($mode=='modify' && $id !='') {
		$query = "SELECT * FROM tbl_user WHERE id='$id';";
  		//@list($username,$alias,$active) = 
  		$array = $ccmDB->query($query); 
  		//ccm_array_dump($array); 
  		$username = ccm_grab_array_var($array[0],'username',''); 
  		$alias = ccm_grab_array_var($array[0],'alias',''); 
  		$active = ($array[0]['active']==1) ? 'checked="checked"' : ''; 	
		$lang = ccm_grab_array_var($array[0],'locale','en_US'); 
  		//$id = ccm_grab_array_var($array,'id',''); 
	}
	
	//begin html 
?>	
	<div id='tab1'>	
	<label for='username'>*<?php echo gettext("Username"); ?>:</label><br />
	<input type='text' class='required' name='username' id='username' value='<?php echo $username; ?>' />
	<br /><br />
	<label for='alias'><?php echo gettext("Alias"); ?>: </label><br />
	<input type='text' name='alias' id='alias' value='<?php echo $alias; ?>' /><br /><br />
	<label for='password'>*<?php echo gettext("Password"); ?>:</label><br />
	<input type='password' name='password' class='required'  id='password' value='<?php echo $password; ?>' />
	<br />
	<label for='config'>*<?php echo gettext("Confirm Password"); ?>:</label><br />
	<input type='password' name='confirm' class='required'  id='confirm' value='<?php echo $confirm; ?>' />
	<br /><br />
	<input type='checkbox' name='active' id='active' value='1' <?php echo $active; ?> />
	<label for='active'> <?php echo gettext("Active"); ?> </label><br />
	<input type='hidden' name='id' value='<?php echo $id; ?>' />
	<br /><br />
	<label for='lang'><?php echo gettext("Language"); ?></label><br />
	<select name='lang' id='lang' style="width:100px;"> 
<?php
	foreach($languages as $l) { 
		print "<option value='{$l}'";
		if($lang==$l) print " selected='selected' "; 
		print">{$l}</option>\n"; 
	}	
?>		
	</select><br />
	</div>

<?php 

}//end manage_user_html()


/**
*	handle add/insert submission for new user 
*	@author Mike Guthrie
*	@author Martin Willisegger
*	@return mixed array(int $errors, string $message) 
*/
function process_user_submission() 
{
	global $myDataClass; 

	//return variables 
	$errors = 0;
	$message =''; 

	//process input variables 
	$mode = ccm_grab_request_var('mode','insert');
	$id = ccm_grab_request_var('id',false);  
	$cmd = ccm_grab_request_var('cmd',false); 
	//form defaults 
	$username = ccm_grab_request_var('username','');
	$alias = ccm_grab_request_var('alias',''); 
	$password=ccm_grab_request_var('password','');
	$access = '11111111'; //not used in CCM
	$ws_auth = 0; 		//not used in CCM 
	$active = ccm_grab_request_var('active',false); 
	$lang = ccm_grab_request_var('lang','en_US'); 
	
	//statements below modified from Martin's admin/user.php 	
  	$strSQLx = "`tbl_user` SET `username`='$username', `alias`='$alias', `access_rights`='$access',
          `password`=MD5('$password'), `wsauth`='$ws_auth', `active`='$active', `locale`='{$lang}', `last_modified`=NOW()";
    if ($mode == "insert" && $cmd != 'delete')
        $strSQL = "INSERT INTO ".$strSQLx;
	elseif($cmd=='delete') 
		$strSQL="DELETE FROM tbl_user WHERE `id`='{$id}'"; 
    else
        $strSQL = "UPDATE ".$strSQLx." WHERE `id`=$id";
		
	ccm_set_language($lang);	
        
    //error handling 
	$errors = $myDataClass->dataInsert($strSQL,$intInsertId);
    $message = ($errors > 0)? $myDataClass->strDBMessage : gettext('User updated successfully!').'<br />';
    
    //logging 
    if ($mode  == "insert")   $myDataClass->writeLog(gettext('A new user added:')." ".$username);
    if ($mode  == "modify")   $myDataClass->writeLog(gettext('User modified:')." ".$username);	
    
	return array($errors,$message); 
	
}//end process_user_submission() 



/**
*	updates global CCM settings based on request vars 
*	@author Mike Guthrie
*	@author Martin Willisegger
*/
function update_ccm_settings() 
{
	global $CFG; 
	global $myDBClass;
	global $ccmDB;
	
	$errors = 0;
	$msg = ''; 

	//dump_request(); 
	$txtRootPath = ccm_grab_request_var('txtRootPath');
	$txtBasePath = ccm_grab_request_var('txtBasePath');
	$txtTempDir = ccm_grab_request_var('txtTempDir');
	$selProtocol = ccm_grab_request_var('selProtocol');
	$txtDBserver = ccm_grab_request_var('txtDBserver');
	$txtDBport = ccm_grab_request_var('txtDBport');
	$txtDBname = ccm_grab_request_var('txtDBname');
	$txtDBuser = ccm_grab_request_var('txtDBuser');
	$txtDBpassword = ccm_grab_request_var('txtDBpassword');
	$txtLogoff = ccm_grab_request_var('txtLogoff');
	$txtLines = ccm_grab_request_var('txtLines');
	$txtStaticDir = ccm_grab_request_var('txtStaticDir'); 
	
	// Write global settings to database
	$strSQL = "SET @previous_value := NULL";
	$booReturn = $myDBClass->insertData($strSQL);
	$strSQL  = "INSERT INTO `tbl_settings` (`category`,`name`,`value`) VALUES";
	$strSQL .= "('path','root','".str_replace("\\", "\\\\", $txtRootPath)."'),";
	$strSQL .= "('path','physical','".str_replace("\\", "\\\\", $txtBasePath)."'),";
	$strSQL .= "('path','protocol','".$selProtocol."'),";
	$strSQL .= "('path','tempdir','".str_replace("\\", "\\\\", $txtTempDir)."'),";
	
	//added option for static config directory 
	$strSQL .= "('path','staticdir','".str_replace("\\", "\\\\", $txtStaticDir)."'),";
	//$strSQL .= "('data','locale','".$selLanguage."'),";
	//$strSQL .= "('data','encoding','".$txtEncoding."'),";
	$strSQL .= "('security','logofftime','".$txtLogoff."'),";
	//$strSQL .= "('security','wsauth','".$selWSAuth."'),";
	$strSQL .= "('common','pagelines','".$txtLines."')"; //COMMA REMOVED!!! 
	//$strSQL .= "('common','seldisable','".$selSeldisable."'),";
	//$strSQL .= "('db','magic_quotes','".$txtMagicQuotes."') ";
	$strSQL .= "ON DUPLICATE KEY UPDATE value = IF((@previous_value := value) <> NULL IS NULL, VALUES(value), NULL);";
	$booReturn = $myDBClass->insertData($strSQL);
	if ( $booReturn == false ) {
		$errors++;
		$msg = gettext("An error occured while writing settings to database")."<br>".$myDBClass->strDBError;
	}	
	$strSQL = "SELECT @previous_note";
	$booReturn = $myDBClass->insertData($strSQL);	
	
	//update the settings.php file 
	$file = $txtBasePath."config/settings.php";
	if(file_exists($file) && is_writeable($file)) {
		$string = "
<?php\n
exit;\n;
?>\n
;\n
; Nagios CCM.  Based on NagiosQL (c) 2008, 2009 by Martin Willisegger\n
[db]\n
server       = ".$txtDBserver."\n
port         = ".$txtDBport."\n    
database     = ".$txtDBname."\n
username     = ".$txtDBuser."\n
password     = ".$txtDBpassword."\n
[common]\n
install      = passed\n"; //end content string 
		file_put_contents($file,$string);
		
	}
	else {
		$errors++;
		$msg .=gettext("Unable to save to file").": $file<br />";
	}
		
	//rebuild $CFG array after settings update
	$CFG['settings'] = array(); 
	$settings = $ccmDB->query("SELECT * FROM tbl_settings;"); 
	foreach($settings as $s) 
		$CFG[$s['category']][$s['name']] = $s['value']; 	
	//update session settings 
	$_SESSION['SETS'] = $CFG;
//	$_SESSION['pagelimit'] = $CFG['common']['pagelines']; 
	
	if($errors==0) $msg = gettext('Settings updated successfully!')."<br />"; 
	
	return array($errors,$msg);
	 
} //end update_ccm_settings() 


/**
*	html output for global CCM settings page
*
*/ 
function ccm_settings()
{
	global $CFG;

	//ccm_array_dump($CFG);
	$errors = 0;
	$msg = ''; 
	$submitted = ccm_grab_request_var('submitted',false); 
	$returnClass='hidden'; 

	if($submitted){
		list($errors,$msg) = update_ccm_settings(); 
		$returnClass = ($errors > 0) ? 'error' : 'success'; 
	}
	$https = ($CFG['path']['protocol'] =='https') ? "selected='selected'" : '';
	$http = ($CFG['path']['protocol'] =='http') ? "selected='selected'" : ''; 
	$staticDir = isset($CFG['path']['staticdir']) ? $CFG['path']['staticdir'] : '/usr/local/nagios/etc/static';
		 
	//begin html output 
?>
	<div id='contentWrapper'>
	<h2 id='objectHeader'><?php echo gettext("CCM Global Settings"); ?></h2>
	<br />
	<div id='returnContent' class='<?php echo $returnClass; ?>'><?php echo $msg; ?>
	</div>
	<form action='index.php' method='post' id='formInput' name='frmInput'>
	
	<h4><?php echo gettext("Paths"); ?>:</h4>
	<label for='txtRootPath'><?php echo gettext("Application Root Path"); ?>*</label><br />
    <input type="text" style="width:300px" class="required" value="<?php echo $CFG['path']['root']; ?>" id="txtRootPath" name="txtRootPath" /><br />
 	<label for='txtBasePath'><?php echo gettext("Application Base Path"); ?>*</label><br />
    <input type="text" style="width:300px" class="required" value="<?php echo $CFG['path']['physical']; ?>" id="txtBasePath" name="txtBasePath" /><br />   
 	<label for='txtTempDir'><?php echo gettext("Temp Directory"); ?>*</label><br />
    <input type="text" style="width:300px" class="required" value="<?php echo $CFG['path']['tempdir']; ?>" id="txtTempDir" name="txtTempDir" /><br /> 
 	<label for='txtStaticDir'><?php echo gettext("Static Configuration Directory"); ?>*</label><br />
    <input type="text" style="width:300px" class="required" value="<?php  echo $staticDir; ?>" id="txtStaticDir" name="txtStaticDir" /><br />     
     <label for='selProtocol'><?php echo gettext("Server Protocol"); ?>*</label><br />
    <select id="selProtocol" name="selProtocol">
    	<option value='http' <?php echo $http; ?> >http</option>
    	<option value='https' <?php echo $https; ?> >https</option>
    </select><br />     
    
    <!-- TODO: Language settings will go here once strings up are updated with gettext() -->
<!--    <input type='hidden' name='selLanguage' value='en_GB' />
    <input type="hidden" value="utf-8" id="txtEncoding" name="txtEncoding" /> -->
    
    <h4><?php echo gettext("Database"); ?>:</h4>
    <label for='txtDBserver'><?php echo gettext("MySQL Server"); ?>*</label><br />
    <input type="text" style="width:200px" class="required" value="<?php echo $CFG['db']['server']; ?>" id="txtDBserver" name="txtDBserver" /><br />
    <label for='txtDBport'><?php echo gettext("MySQL Server Port"); ?>*</label><br />
    <input type="text" style="width:200px" class="required" value="<?php echo $CFG['db']['port']; ?>" id="txtDBport" name="txtDBport" /><br />   
    <label for='txtDBname'><?php echo gettext("Database Name"); ?>*</label><br />
    <input type="text" style="width:200px" class="required" value="<?php echo $CFG['db']['database']; ?>" id="txtDBname" name="txtDBname" /><br />
    <label for='txtDBuser'><?php echo gettext("Database User"); ?>*</label><br />
    <input type="text" style="width:200px" class="required" value="<?php echo $CFG['db']['username']; ?>" id="txtDBuser" name="txtDBuser" /><br />       
    <label for='txtDBpassword'><?php echo gettext("Database Password"); ?>*</label><br />
    <input type="password" style="width:200px" class="required" value="<?php echo $CFG['db']['password']; ?>" id="txtDBpassword" name="txtDBpassword" /><br />
    
    <h4><?php echo gettext("Common"); ?></h4>
    <label for='txtLogoff'><?php echo gettext("Session Auto Logout Time"); ?></label><br />
	<input type="text" style="width:200px" value="<?php echo $CFG['security']['logofftime']; ?>" id="txtLogoff" name="txtLogoff" /><br />
    <label for='txtLines'><?php echo gettext("Default Result Limit"); ?></label> <br />
	<input type="text" style="width:200px" value="<?php echo  $CFG['common']['pagelines']; ?>" id="txtLines" name="txtLines" /><br />	
	<br />    
    <!-- buttons -->
	<input type="submit" class='ccmButton' value="<?php echo gettext("Save"); ?>" id="subForm" name="subForm" />
	<input type="button" class='ccmButton' value="<?php echo gettext("Abort"); ?>" id="abort" name="abort" onclick="window.location='index.php';"/>
	<input type="hidden" value="true" id="submitted" name="submitted" />
	<input type='hidden' name='cmd' value='admin' />
	<input type='hidden' name='type' value='settings' />
	
	
	</form>
	</div> <!-- end contentWrapper div -->
	
<?php 

} //end ccm_settings() 





function ccm_static_editor() {
	global $ccmDB;
	
	//$staticDir='/usr/local/nagios/etc/static';
	$returnClass='hidden';
	$feedback='';
	$submitted=ccm_grab_request_var('submitted',false);
	
	//get config option for static directory 
	$query = "SELECT `value` FROM tbl_settings WHERE `name`='staticdir'";
	$dir = $ccmDB->query($query);
	$staticDir = isset($dir[0]['value']) ? $dir[0]['value'] : '/usr/local/nagios/etc/static';
	//echo "DIR:".$staticDir;
	//get static files, dump to $output variable 	
	DirToArray($staticDir, '', '', $output,$errMessage);
	
	//save static file 	
	if($submitted) {
		$newcfg = ccm_grab_request_var('newcfg','');
		$file = urldecode(ccm_grab_request_var('staticFile','')); 
				
		if(is_writeable($file)) {
			if(!file_put_contents($file,$newcfg)) {
				$feedback = gettext("Unable to write to file").": $file.  ".gettext("Check permissions").".<br />"; 
				$returnClass='error';	
			}
			else {
				$feedback=gettext("File").": <strong>$file</strong> ".gettext("saved successfully!")."<br />";
				$returnClass='success';  
			}	
		}		
	} //end if submitted 
	
?>	

	</select>
	<div id='contentWrapper'>
	<h2><?php echo gettext("Static Configuration Files"); ?></h2> 

	<div id='returnContent' class='<?php echo $returnClass; ?>'>
		<?php echo $feedback; ?>
	</div>
	<p><?php echo gettext("This tool allows editing of configuration files that are NOT stored in the Nagios CCM database"); ?>.</p>	
	<div id='centerdiv'>
	<form action='index.php' method='post' id='formInput' name='frmInput'>
	<label for='staticFiles'><?php echo gettext("Static Files"); ?>: <?php echo $staticDir; ?></label><br />
	<select id='staticFiles' name='staticFile'>
	
<?php //file option list 
	foreach($output as $file)
		print "<option value='".urlencode($file)."'>$file</option>\n"; 

?>	
	</select>
	<input type='button' id='loadValue' value='<?php echo gettext('Load File'); ?>' class='ccmButton' onclick='getStaticFile()' />
	<br />

	<textarea name="newcfg" rows="25" cols="110" id='newcfg'></textarea><br />

    <br />
    
	<input type="submit" class='ccmButton' value="<?php echo gettext("Save"); ?>" id="subForm" name="subForm" />
	<input type="button" class='ccmButton' value="<?php echo gettext("Abort"); ?>" id="reset" name="reset" onclick="javascript:window.location='index.php';"/>
	<input type="hidden" value="true" id="submitted" name="submitted" />
	<input type='hidden' name='cmd' value='admin' />
	<input type='hidden' name='type' value='static' />
		
	</form>
	</div><!-- end centerdiv -->
	</div> <!-- end contentWrapper div -->	
		
<?php 
	
}



/**
*	Imports nagios configs based on request variable array of files 
*	modified from Martin's original import.php script 
*	@author Martin Willisegger
*	@param mixed $chkSelFilename array of files to import
*	@param int $chkOverwrite checkbox option to overwrite existing DB info
*	@param string $returnClass REFERENCE variable for div's CSS class: "success" | "error" 
*	@return string $message feedback message to tell if imports were all successful
*/ 
function import_configs($chkSelFilename,$chkOverwrite,&$returnClass) {

	global $myVisClass; 
	global $myDataClass;
	global $myImportClass;
	
	$imported_files = 0;
	$message = ''; 
	$errors = 0;
	//process selected files for import 
	if(!empty($chkSelFilename)) { 		
	  	foreach($chkSelFilename AS $elem) {
	    	$intReturn = $myImportClass->fileImport($elem,$chkOverwrite);
	    	$myDataClass->writeLog(gettext('File imported - File [overwite flag]:')." ".$elem." [".$chkOverwrite."]");
	    	if ($intReturn == 1) {
	    		$message .= $myVisClass->strDBMessage;
	    		$errors++;
	    	}	
	    	else 
	    		$imported_files++;	
	  	}//end foreach 
	}//end if files selected 
	
	if($errors==0) {
		$returnClass = 'success';
		$message = $imported_files. " ".gettext("file(s) imported successfully!")."<br />";
	}
	else {
		$returnClass = 'error';	
		$message .=$errors .gettext("items failed to import successfully")."<br />";
	}	
	
	//this is a terrible place for this... 
	$message .="<div id='closeReturn'>
				<a href='javascript:void(0)' id='closeReturnLink' title='Close'>".gettext("Close")."</a>
				</div>";
				 
	return $message;
}//end import_configs() 


/**
*	modified from Martin's original import.php script 
*	@author Martin Willisegger
*	@param string $chkSearch 
*/ 
function populate_config_files($chkSearch) 
{
	global $myConfigClass;
	//$myConfigClass->getConfigData("method",$intMethod);
	$myConfigClass->getConfigData("basedir",$strBaseDir);
	$myConfigClass->getConfigData("hostconfig",$strHostDir);
	$myConfigClass->getConfigData("serviceconfig",$strServiceDir);
	$myConfigClass->getConfigData("backupdir",$strBackupDir);
	$myConfigClass->getConfigData("hostbackup",$strHostBackupDir);
	$myConfigClass->getConfigData("servicebackup",$strServiceBackupDir);
	$myConfigClass->getConfigData("importdir",$strImportDir);
	$myConfigClass->getConfigData("nagiosbasedir",$strNagiosBaseDir);
	// Building local file list
	$output = array();
	$temp=DirToArray($strBaseDir, "\.cfg", "cgi.cfg|nagios.cfg|nrpe.cfg|nsca.cfg|ndo2db.cfg|ndomod.cfg|resource.cfg",$output,$errMessage);
	if ($strNagiosBaseDir != $strBaseDir) 
	    $temp=DirToArray($strNagiosBaseDir, "\.cfg", "cgi.cfg|nagios.cfg|nrpe.cfg|nsca.cfg|ndo2db.cfg|ndomod.cfg|resource.cfg",$output,$errMessage);
	
	$temp=DirToArray($strHostDir, "\.cfg", "",$output,$errMessage);
	$temp=DirToArray($strServiceDir, "\.cfg", "",$output,$errMessage);
	$temp=DirToArray($strHostBackupDir, "\.cfg_", "",$output,$errMessage);
	$temp=DirToArray($strServiceBackupDir, "\.cfg_", "",$output,$errMessage);
	
	if(($strImportDir != "") && ($strImportDir != $strBaseDir) && ($strImportDir != $strNagiosBaseDir)) 
	    $temp=DirToArray($strImportDir, "\.cfg", "",$output,$errMessage);
	
	$output=array_unique($output);

    if(is_array($output) && (count($output) != 0)) {
        foreach ($output AS $elem) {
        	if (($chkSearch == "") || (substr_count($elem,$chkSearch) != 0)) 
      	  		print "<option value='$elem'>$elem</option>\n";       
      	}//end foreach 
    }
}

/**
*	Function to add files of a given directory to an array
*	@author Martin Willisegger
*	@param string $sPath
* 	@param string $include string match to include
*	@param string $exclude expression match to exclude
*	@param string $output REFERENCE variable to output
*	@param string $errMEssage REFERENCE variable to error output message 
*/
function DirToArray($sPath, $include, $exclude, &$output,&$errMessage) 
{
  while (substr($sPath,-1) == "/" OR substr($sPath,-1) == "\\") {
    $sPath=substr($sPath, 0, -1);
  }
  $handle = @opendir($sPath);
  if( $handle === false ) {
    $errMessage .= gettext('Could not open directory')." ".$sPath."<br>";
  } else {
    while ($arrDir[] = readdir($handle)) {}
    closedir($handle);
    sort($arrDir);
    foreach($arrDir as $file) {
      if (!preg_match("/^\.{1,2}/", $file) and strlen($file)) {
        if (is_dir($sPath."/".$file) && strpos($file,'static')===false && strpos($file,'pnp')===false) {
          DirToArray($sPath."/".$file, $include, $exclude, $output, $errMessage);
        } else {
          if (preg_match("/".$include."/",$file) && (($exclude == "") || !preg_match("/".$exclude."/", $file))) {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
              $sPath=str_replace("/", "\\", $sPath);
              $output [] = $sPath."\\".$file;
            } else {
              $output [] = $sPath."/".$file;
            }
          }
        }
      }
    }
  }
}

?>
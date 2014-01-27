<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: users.php 1167 2012-05-08 16:12:22Z egalstad $

require_once(dirname(__FILE__).'/../includes/common.inc.php');

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

// only admins can access this page
if(is_admin()==false){
	echo $lstr['NotAuthorizedErrorText'];
	exit();
	}

// route request
route_request();


function route_request(){
	global $request;

	if(isset($request['update']))
		do_update_user();
	else if(isset($request['delete']) || (isset($request['multiButton']) && $request['multiButton']=='delete'))
		do_delete_user();
	else if(isset($request['edit']))
		show_edit_user();
	else if(isset($request['clone']))
		show_clone_user();
	else if(isset($request['doclone']))
		do_clone_user();
	else if(isset($request['masquerade']))
		do_masquerade();
	else
		show_users();
	exit;
	}


function show_users($error=false,$msg=""){
	global $request;
	global $lstr;
	global $db_tables;
	global $sqlquery;
	global $cfg;
	
	// generage messages...
	if($msg==""){
		if(isset($request["useradded"]))
			$msg=$lstr['UserAddedText'];
		if(isset($request["userupdated"]))
			$msg=$lstr['UserUpdatedText'];
		if(isset($request["usercloned"]))
			$msg=$lstr['UserClonedText'];
		}
	
	// defaults
	$sortby="username";
	$sortorder="asc";
	$page=1;
	$records=5;
	$search="";
	
	// default to use saved options
	$s=get_user_meta(0,'user_management_options');
	$saved_options=unserialize($s);
	if(is_array($saved_options)){
		if(isset($saved_options["sortby"]))
			$sortby=$saved_options["sortby"];
		if(isset($saved_options["sortorder"]))
			$sortorder=$saved_options["sortorder"];
		if(isset($saved_options["records"]))
			$records=$saved_options["records"];
		if(array_key_exists("search",$saved_options))
			$search=$saved_options["search"];
		}

	// get options
	$sortby=grab_request_var("sortby",$sortby);
	$sortorder=grab_request_var("sortorder",$sortorder);
	$page=grab_request_var("page",$page);
	$records=grab_request_var("records",$records);
	$user_id=array();
	$user_id=grab_request_var("user_id",array());
	$search=grab_request_var("search","");
	if($search==$lstr['SearchBoxText'])
		$search="";

	// save options for later
	$saved_options=array(
		"sortby" => $sortby,
		"sortorder" => $sortorder,
		"records" => $records,
		"search" => $search
		);
	$s=serialize($saved_options);
	set_user_meta(0,'user_management_options',$s,false);
	
	// generate query
	$fieldmap=array(
		"username" => $db_tables[DB_NAGIOSXI]["users"].".username",
		"name" => $db_tables[DB_NAGIOSXI]["users"].".name",
		"email" => $db_tables[DB_NAGIOSXI]["users"].".email"
		);
	$query_args=array();
	if(isset($sortby)){
		$query_args["orderby"]=$sortby;
		if(isset($sortorder) && $sortorder=="desc")
			$query_args["orderby"].=":d";
		else
			$query_args["orderby"].=":a";
		}
	if(isset($search) && have_value($search))
		$query_args["username"]="lks:".$search.";name=lks:".$search.";email=lks:".$search;

	// first get record count
	$sql_args=array(
		"sql" => $sqlquery['GetUsers'],
		"fieldmap" => $fieldmap,
		"default_order" => "username",
		"useropts" => $query_args,
		"limitrecords" => false
		);
	$sql=generate_sql_query(DB_NAGIOSXI,$sql_args);
	$rs=exec_sql_query(DB_NAGIOSXI,$sql);
	if(!$rs->EOF)
		$total_records=$rs->RecordCount();
	else
		$total_records=0;
	
	// get table paging info - reset page number if necessary
	$pager_args=array(
		"sortby" => $sortby,
		"sortorder" => $sortorder,
		"search" => $search
		);
	$pager_results=get_table_pager_info("",$total_records,$page,$records,$pager_args);
	



	do_page_start(array("page_title"=>$lstr['ManageUsersPageTitle']),true);

?>
	<h1><?php echo $lstr['ManageUsersPageHeader'];?></h1>
	

<?php
	display_message($error,false,$msg);
?>

	<form action="" method="post" id="userList">
	<?php echo get_nagios_session_protector();?>
	<input type="hidden" name="sortby" value="<?php echo encode_form_val($sortby);?>">
	<input type="hidden" name="sortorder" value="<?php echo encode_form_val($sortorder);?>">

	<div id="usersTableContainer" class="tableContainer">

	<div class="tableHeader">

	<div class="tableTopButtons">
	<a href="?users&amp;edit=1"><img class="tableTopButton" src="<?php echo theme_image("b_adduser.png");?>" border="0" alt="<?php echo $lstr['AddNewUserText'];?>" title="<?php echo $lstr['AddNewUserText'];?>"><?php echo $lstr['AddNewUserText'];?></a>
	<div class="tableListSearch">
<?php
	$searchclass="textfield";
	if(have_value($search)){
		$searchstring=$search;
		$searchclass.=" newdata";
		}
	else
		$searchstring=$lstr['SearchBoxText']
?>
	<input type="text" size="15" name="search" id="searchBox" value="<?php echo encode_form_val($searchstring);?>" class="<?php echo $searchclass;?>" />
	<input type="submit" class="submitbutton" name="searchButton" value="<?php echo $lstr['GoButton'];?>" id="searchButton">
	</div><!--table list search -->
	</div><!-- table top buttons -->
	
	<div class="tableTopText">
	<?php
	$clear_args=array(
		"sortby" => $sortby,
		"search" => ""
		);
	echo table_record_count_text($pager_results,$search,true,$clear_args);
	?>
	</div>
	
	<br />
	
	</div><!-- tableHeader -->

	<table id="usersTable" class="tablesorter hovercells" style="width: 100%;">
	<thead> 
	<tr>
	<th><input type='checkbox' name='userList_checkAll' id='checkall' value='0'></th>
<?php
	$extra_args=array();
	$extra_args["search"]=$search;
	$extra_args["records"]=$records;
	$extra_args["page"]=$page;
	echo sorted_table_header($sortby,$sortorder,"username",$lstr['UsernameTableHeader'],$extra_args);
	echo sorted_table_header($sortby,$sortorder,"name",$lstr['NameTableHeader'],$extra_args);
	echo sorted_table_header($sortby,$sortorder,"email",$lstr['EmailTableHeader'],$extra_args);
?>
	<th><?php echo $lstr['ActionsTableHeader'];?></th>
	</tr>
	
	</thead> 
	<tbody>
<?php
	// run record-limiting query
	$query_args["records"]=$records.":".(($pager_results["current_page"]-1)*$records);
	$sql_args["sql"]=$sql;
	$sql_args["useropts"]=$query_args;
	$sql=limit_sql_query_records($sql_args,$cfg['db_info'][DB_NAGIOSXI]['dbtype']);
	$rs=exec_sql_query(DB_NAGIOSXI,$sql);
	
	$x=0;
	
	if(!$rs || $rs->EOF){
		echo "<tr><td colspan='5'>".$lstr['NoMatchingRecordsFoundText']."</td></tr>";
		}
		
	else while(!$rs->EOF){
	
		$x++;
	
		$checked="";
		$classes="";
		
		if(($x%2)==0)
			$classes.=" even";
		else
			$classes.=" odd";
		
		$oid=$rs->fields["user_id"];
		
		if(is_array($user_id)){
			if(in_array($oid,$user_id)){
				$checked="CHECKED";
				$classes.=" selected";
				}
			}
		else if($oid==$user_id){
			$checked="CHECKED";
			$classes.=" selected";
			}
		
		echo "<tr";
		if(have_value($classes))
			echo " class='".$classes."'";
		echo ">";
		echo "<td><input type='checkbox' name='user_id[]' value='".$oid."' id='checkbox_".$oid."' ".$checked."></td>";
		echo "<td class='clickable'>".$rs->fields["username"]."</td>";
		echo "<td class='clickable'>".$rs->fields["name"]."</td>";
		echo "<td class='clickable'><a href='mailto:".$rs->fields["email"]."'>".$rs->fields["email"]."</a></td>";
		echo "<td>";
		echo "<a href='?edit=1&amp;user_id[]=".$oid."'><img class='tableItemButton' src='".theme_image("b_edituser.png")."' border='0' alt='".$lstr['EditAlt']."' title='".$lstr['EditAlt']."'></a> ";
		echo "<a href='?clone=1&amp;user_id[]=".$oid."'><img class='tableItemButton' src='".theme_image("b_cloneuser.png")."' border='0' alt='".$lstr['CloneAlt']."' title='".$lstr['CloneAlt']."'></a>";
		echo "<a href='?user_id=".$oid."&masquerade=1&nsp=".get_nagios_session_protector_id()."' class='masquerade_link'><img class='tableItemButton' src='".theme_image("b_masquser.png")."' border='0' alt='".$lstr['MasqueradeAlt']."' title='".$lstr['MasqueradeAlt']."'></a> ";
		echo "<a href='?delete=1&amp;user_id[]=".$oid."&nsp=".get_nagios_session_protector_id()."'><img class='tableItemButton' src='".theme_image("b_deleteuser.png")."' border='0' alt='".$lstr['DeleteAlt']."' title='".$lstr['DeleteAlt']."'></a>";
		echo "</td>";
		echo "</tr>\n";
		
		$rs->MoveNext();
		}
?>
	</tbody>
	<tfoot>
	<tr><td colspan='5' class='tablePagerLinks'>
	<?php table_record_pager($pager_results);?>
	</td></tr>
	</tfoot>
	</table>
	
	<div class="tableFooter">
	
	<div class="tableListMultiOptions">
	<?php echo $lstr['WithSelectedText'];?> 
	<button class="tableMultiItemButton" title="<?php echo $lstr['DeleteAlt'];?>" value="delete" name="multiButton" type="submit">
	<img class="tableMultiButton" src="<?php echo theme_image("b_delete.png");?>" border="0" alt="<?php echo $lstr['DeleteAlt'];?>" title="<?php echo $lstr['DeleteAlt'];?>">
	</button>
	</div>
	
	<br />
	
	</div><!-- tableFooter -->
	
	</div><!-- tableContainer -->
	
	</form>

<?php

	do_page_end(true);
	exit();
	}


function show_edit_user($error=false,$msg=""){
	global $request;
	global $lstr;
	
	// by default we add a new user
	$add=true;
	$user_id=0;
	
	// get languages and themes
	$languages=get_languages();
	$themes=get_themes();
	$authlevels=get_authlevels();
	$number_formats=get_number_formats();
	$date_formats=get_date_formats();
	
	// defaults
	$date_format=DF_ISO8601;
	$number_format=NF_2;
	$email="";
	$username="";
	$name="";
	$level="user";
	$language=get_option("default_language");
	$theme=get_option("default_theme");
	$add_contact=0;
	$authorized_for_all_objects=0;
	$authorized_to_configure_objects=0;
	$authorized_for_all_object_commands=0;
	$authorized_for_monitoring_system=0;
	$advanced_user=0;
	$readonly_user=0;
	
	//echo "READONLY1: $readonly_user<BR>";
	
	//echo "REQUEST<BR>";
	//print_r($request);
	
	// get options
	$user_id=grab_request_var("user_id");
	if(is_array($user_id)){
		$user_id=current($user_id);
		if($user_id!=0)
			$add=false;
		}
		
	//echo "USERID<BR>";
	//print_r($user_id);
		
	if($error==false){
		if(isset($request["updated"]))
			$msg=$lstr['UserUpdatedText'];
		else if(isset($request["added"]))
			$msg=$lstr['UserAddedText'];
		}
		
		
	// load current user info
	if($add==false){
	
		// make sure user exists first
		if(!is_valid_user_id($user_id)){
			show_users(true,$lstr['BadUserAccountError']." (ID=".$user_id.")");
			}
	
		$username=grab_request_var("username",get_user_attr($user_id,"username"));
		$email=grab_request_var("email",get_user_attr($user_id,"email"));
		$level=grab_request_var("level",get_user_meta($user_id,"userlevel"));
		$name=grab_request_var("name",get_user_attr($user_id,"name"));
		$language=grab_request_var("defaultLanguage",get_user_meta($user_id,"language"));
		$theme=grab_request_var("defaultTheme",get_user_meta($user_id,"theme"));
		$date_format=grab_request_var("defaultDateFormat",intval(get_user_meta($user_id,'date_format')));
		$number_format=grab_request_var("defaultNumberFormat",intval(get_user_meta($user_id,'number_format')));
		
		$authorized_for_all_objects=checkbox_binary(grab_request_var("authorized_for_all_objects",get_user_meta($user_id,"authorized_for_all_objects")));
		$authorized_to_configure_objects=checkbox_binary(grab_request_var("authorized_to_configure_objects",get_user_meta($user_id,"authorized_to_configure_objects")));
		$authorized_for_all_object_commands=checkbox_binary(grab_request_var("authorized_for_all_object_commands",get_user_meta($user_id,"authorized_for_all_object_commands")));
		$authorized_for_monitoring_system=checkbox_binary(grab_request_var("authorized_for_monitoring_system",get_user_meta($user_id,"authorized_for_monitoring_system")));
		$advanced_user=checkbox_binary(grab_request_var("advanced_user",get_user_meta($user_id,"advanced_user")));
		$readonly_user=checkbox_binary(grab_request_var("readonly_user",get_user_meta($user_id,"readonly_user")));
		//echo "READONLY2A: $readonly_user<BR>";

		
		$password1="";
		$password2="";
		$forcepasswordchange=get_user_meta($user_id,"forcepasswordchange");
		
		$passwordbox1title=$lstr['NewPassword1BoxTitle'];
		$passwordbox2title=$lstr['NewPassword2BoxTitle'];
		
		$sendemail="0";
		$sendemailboxtitle=$lstr['SendAccountPasswordEmailBoxTitle'];

		$page_title=$lstr['EditUserPageTitle'];
		$page_header=$lstr['EditUserPageHeader'].": ".htmlentities($username);
		$button_title=$lstr['UpdateUserButton'];
		}
	else{
		// get defaults to use for new user (or use submitted data)
		$username=grab_request_var("username","");
		$email=grab_request_var("email","");
		$level=grab_request_var("level","user");
		$name=grab_request_var("name","");
		$language=grab_request_var("defaultLanguage",$language);
		$theme=grab_request_var("defaultTheme",$theme);
		
		$add_contact=1;

		$authorized_for_all_objects=checkbox_binary(grab_request_var("authorized_for_all_objects",""));
		$authorized_to_configure_objects=checkbox_binary(grab_request_var("authorized_to_configure_objects",""));
		$authorized_for_all_object_commands=checkbox_binary(grab_request_var("authorized_for_all_object_commands",""));
		$authorized_for_monitoring_system=checkbox_binary(grab_request_var("authorized_for_monitoring_system",""));
		$advanced_user=checkbox_binary(grab_request_var("advanced_user",""));
		$readonly_user=checkbox_binary(grab_request_var("readonly_user",""));
		//echo "READONLY2B: $readonly_user<BR>";
		
		$password1=random_string(6);
		$password2=$password1;
		$forcepasswordchange="1";
		$passwordbox1title=$lstr['Password1BoxTitle'];
		$passwordbox2title=$lstr['Password2BoxTitle'];
		
		$sendemail="1";
		$sendemailboxtitle=$lstr['SendAccountInfoEmailBoxTitle'];

		$page_title=$lstr['AddUserPageTitle'];
		$page_header=$lstr['AddUserPageHeader'];
		$button_title=$lstr['AddUserButton'];
		}
		
	if($forcepasswordchange=="1")
		$forcechangechecked="CHECKED";
	else
		$forcechangechecked="";
	if($sendemail=="1")
		$sendemailchecked="CHECKED";
	else
		$sendemailchecked="";


	do_page_start(array("page_title"=>$page_title),true);

?>
	<h1><?php echo $page_header;?></h1>
	

<?php
	display_message($error,false,$msg);
?>

	<script type="text/javascript">
	$(document).ready(function() {
		$("#passwordBox1").change(function() {
			$("#updateForm").checkCheckboxes("#forcePasswordChangeBox", true);
			$("#updateForm").checkCheckboxes("#sendEmailBox", true);
			});
	});
	</script>
	
	<form id="updateForm" method="post" action="">
	<input type="hidden" name="update" value="1">
	<?php echo get_nagios_session_protector();?>
	<input type="hidden" name="users" value="1">
	<input type="hidden" name="user_id[]" value="<?php echo encode_form_val($user_id);?>">

	<div class="sectionTitle"><?php echo $lstr['UserAccountGeneralSettingsSectionTitle'];?></div>

	<table class="editDataSourceTable">


	<tr>
	<td>
	<label for="usernameBox"><?php echo $lstr['UsernameBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="text" size="15" name="username" id="usernameBox" value="<?php echo encode_form_val($username);?>" class="textfield" /><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label for="passwordBox1"><?php echo $passwordbox1title;?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="password" size="10" name="password1" id="passwordBox1" value="<?php echo encode_form_val($password1);?>" class="textfield" /><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label for="passwordBox2"><?php echo $passwordbox2title;?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="password" size="10" name="password2" id="passwordBox2" value="<?php echo encode_form_val($password2);?>" class="textfield" /><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label for="forcePasswordChangeBox"><?php echo $lstr['ForcePasswordChangeNextLoginBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="forcePasswordChangeBox" name="forcepasswordchange" <?php echo $forcechangechecked;?>><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label for="sendEmailBox"><?php echo $sendemailboxtitle;?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="sendEmailBox" name="sendemail" <?php echo $sendemailchecked;?>><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label for="nameBox"><?php echo $lstr['NameBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="text" size="30" name="name" id="nameBox" value="<?php echo encode_form_val($name);?>" class="textfield" /><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label for="emailAddressBox"><?php echo $lstr['EmailBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="text" size="30" name="email" id="emailAddressBox" value="<?php echo encode_form_val($email);?>" class="textfield" /><br class="nobr" />
	</td>
	</tr>
	
<?php
	if($add==true){
?>
	<tr>
	<td>
	<label for="addContactBox"><?php echo $lstr['CreateUserAsContactBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="addContactBox" name="add_contact" <?php echo is_checked($add_contact,1);?>><br class="nobr" />
	</td>
	</tr>
<?php
		}
?>
	</table>
	
	<div class="sectionTitle"><?php echo $lstr['UserAccountPreferencesSectionTitle'];?></div>

	<table class="editDataSourceTable">

	<!--
	<tr>
	<td>
	<label for="languageListForm"><?php echo $lstr['DefaultLanguageBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<select name="defaultLanguage" id="languageListForm" class="languageList dropdown">
<?php
	foreach($languages as $lang => $title){
?>
	<option value="<?php echo $lang;?>" <?php echo is_selected($language,$lang);?>><?php echo $title."</option>\n";?>
<?php
		}
?>
	</select><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label for="themeListForm"><?php echo $lstr['DefaultThemeBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<select name="defaultTheme" id="themeListForm" class="themeList dropdown">
<?php
	foreach($themes as $th){
?>
	<option value="<?php echo $th;?>" <?php echo is_selected($theme,$th);?>><?php echo $th."</option>\n";?>
<?php
		}
?>
	</select><br class="nobr" />
	</td>
	</tr>
	//-->

	<tr>
	<td>
	<label for="defaultDateFormat"><?php echo $lstr['DefaultDateFormatBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<select name="defaultDateFormat" class="dateformatList dropdown">
<?php
	foreach($date_formats as $id => $txt){
?>
	<option value="<?php echo $id;?>" <?php echo is_selected($id,$date_format);?>><?php echo $txt;?></option>
<?php
		}
?>
	</select><br class="nobr" />
	</td>
	</tr>
	
	<tr>
	<td>
	<label for="defaultNumberFormat"><?php echo $lstr['DefaultNumberFormatBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<select name="defaultNumberFormat" class="numberformatList dropdown">
<?php
	foreach($number_formats as $id => $txt){
?>
	<option value="<?php echo $id;?>" <?php echo is_selected($id,$number_format);?>><?php echo $txt;?></option>
<?php
		}
?>
	</select><br class="nobr" />
	</td>
	</tr>
	
	</table>
	
	<div class="sectionTitle"><?php echo $lstr['UserAccountSecuritySettingsSectionTitle'];?></div>

	<table class="editDataSourceTable">

	<tr>
	<td>
	<label for="authLevelListForm"><?php echo $lstr['AuthorizationLevelBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<select name="level" id="authLevelListForm" class="authLevelList dropdown">
<?php
	foreach($authlevels as $al => $at){
?>
	<option value="<?php echo $al;?>" <?php echo is_selected($level,$al);?>><?php echo $at."</option>\n";?>
<?php
		}
?>
	</select><br class="nobr" />
	</td>
	</tr>
	

	<tr>
	<td>
	<label for="authorizedAllObjectsCheckBox"><?php echo $lstr['AuthorizedForAllObjectsBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="authorizedAllObjectsCheckBox" name="authorized_for_all_objects" <?php echo is_checked($authorized_for_all_objects,1);?>><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label for="authorizedToConfigureObjectsCheckBox"><?php echo $lstr['AuthorizedToConfigureObjectsBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="authorizedToConfigureObjectsCheckBox" name="authorized_to_configure_objects" <?php echo is_checked($authorized_to_configure_objects,1);?>><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label for="authorizedAllObjectCommandsCheckBox"><?php echo $lstr['AuthorizedForAllObjectCommandsBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="authorizedAllObjectCommandsCheckBox" name="authorized_for_all_object_commands" <?php echo is_checked($authorized_for_all_object_commands,1);?>><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label for="authorizedMonitoringSystemCheckBox"><?php echo $lstr['AuthorizedForMonitoringSystemBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="authorizedMonitoringSystemCheckBox" name="authorized_for_monitoring_system" <?php echo is_checked($authorized_for_monitoring_system,1);?>><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label for="advancedUserCheckBox"><?php echo $lstr['AdvancedUserBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="advancedUserCheckBox" name="advanced_user" <?php echo is_checked($advanced_user,1);?>><br class="nobr" />
	</td>
	</tr>
	
	<tr>
	<td>
	<label for="readonlyUserCheckBox"><?php echo $lstr['ReadonlyUserBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="readonlyUserCheckBox" name="readonly_user" <?php echo is_checked($readonly_user,1);?>><br class="nobr" />
	</td>
	</tr>

	</table>
	
	<div id="formButtons">
	<input type="submit" class="submitbutton" name="updateButton" value="<?php echo $button_title;?>" id="updateButton">
	<input type="submit" class="submitbutton" name="cancelButton" value="<?php echo $lstr['CancelButton'];?>" id="cancelButton">
	</div>
	
	<!--</fieldset>-->
	
	</form>
	
	<script type="text/javascript" language="JavaScript">
	document.forms['updateForm'].elements['usernameBox'].focus();
	</script>
	

<?php

	do_page_end(true);
	exit();
	}


function do_update_user(){
	global $request;
	global $lstr;
	
	// user pressed the cancel button
	if(isset($request["cancelButton"])){
		show_users(false,"");
		exit();
		}
		
	// check session
	check_nagios_session_protector();
	
	$errmsg=array();
	$errors=0;

	$changepass=false;
	$user_id=0;
	$add=true;
	$add_contact=true;
	
	//print_r($request);
	//exit();

	// get values
	$username=grab_request_var("username","");
	$email=grab_request_var("email","");
	$name=grab_request_var("name","");
	$level=grab_request_var("level","user");
	$language=grab_request_var("defaultLanguage","");
	$theme=grab_request_var("defaultTheme","");
	$date_format=grab_request_var("defaultDateFormat",DF_ISO8601);
	$number_format=grab_request_var("defaultNumberFormat",NF_2);
	$password1=grab_request_var("password1","");
	$password2=grab_request_var("password2","");

	$add_contact=checkbox_binary(grab_request_var("add_contact",""));
	if($add_contact==1)
		$add_contact=true;
	else
		$add_contact=false;

	$authorized_for_all_objects=checkbox_binary(grab_request_var("authorized_for_all_objects",""));
	$authorized_to_configure_objects=checkbox_binary(grab_request_var("authorized_to_configure_objects",""));
	$authorized_for_all_object_commands=checkbox_binary(grab_request_var("authorized_for_all_object_commands",""));
	$authorized_for_monitoring_system=checkbox_binary(grab_request_var("authorized_for_monitoring_system",""));
	$advanced_user=checkbox_binary(grab_request_var("advanced_user",""));
	$readonly_user=checkbox_binary(grab_request_var("readonly_user",""));
	
	//echo "AUTHOBJ: $authorized_for_all_objects<BR>\n";
	//echo "AUTHCFG: $authorized_to_configure_objects<BR>\n";
	//echo "AUTHSYS: $authorized_for_monitoring_system<BR>\n";
	//exit();
	
	// check for errors
	if(in_demo_mode()==true)
		$errmsg[$errors++]=$lstr['DemoModeChangeError'];
	if(have_value($password1)==true && have_value($password2)==true){
		// user has entered a password
		if(have_value($password1)==true || have_value($password2)==true){
			if(strcmp($password1,$password2))
				$errmsg[$errors++]=$lstr['MismatchedPasswordError'];
			else
				$changepass=true;
			}
		}
	if(have_value($username)==false)
		$errmsg[$errors++]=$lstr['BlankUsernameError'];
	if(have_value($email)==false)
		$errmsg[$errors++]=$lstr['BlankEmailError'];
	else if(!valid_email($email))
		$errmsg[$errors++]=$lstr['InvalidEmailAddressError'];
	if(have_value($name)==false)
		$errmsg[$errors++]=$lstr['BlankNameError'];
	//if(have_value($language)==false)
		//$errmsg[$errors++]=$lstr['BlankDefaultLanguageError'];
	//if(have_value($theme)==false)
		//$errmsg[$errors++]=$lstr['BlankDefaultThemeError'];
	if(have_value($level)==false)
		$errmsg[$errors++]=$lstr['BlankAuthLevelError'];
	else if(!is_valid_authlevel($level))
		$errmsg[$errors++]=$lstr['InvalidAuthLevelError'];
	$user_id=grab_request_var("user_id");
	if(is_array($user_id)){
		$user_id=current($user_id);
		if($user_id!=0){
			$add=false;
			// make sure user exists
			if(!is_valid_user_id($user_id)){
				$errmsg[$errors++]=$lstr['BadUserAccountError']." (ID=".$user_id.")";
				}
			}
		}
	if($level!=L_GLOBALADMIN && $user_id==$_SESSION["user_id"])
		$errmsg[$errors++]=$lstr['AuthLevelDemotionError'];
		
	if(isset($request["forcepasswordchange"]))
		$forcechangepass=true;
	else
		$forcechangepass=false;

	// handle errors
	if($errors>0)
		show_edit_user(true,$errmsg);
	
	// add user
	if($add==true){
		if(!($user_id=add_user_account($username,$password1,$name,$email,$level,$forcechangepass,$add_contact,$errmsg))){
			$errmsg="UNABLE TO ADD USERID: ($user_id)<BR>\n";
			echo "ERROR: $errmsg<BR>\n";
			//show_edit_user(true,$errmsg);
			exit();
			}
		set_user_meta($user_id,'name',$name);
		set_user_meta($user_id,'language',$language);
		set_user_meta($user_id,'theme',$theme);
		set_user_meta($user_id,"date_format",$date_format);
		set_user_meta($user_id,"number_format",$number_format);
		set_user_meta($user_id,"authorized_for_all_objects",$authorized_for_all_objects);
		set_user_meta($user_id,"authorized_to_configure_objects",$authorized_to_configure_objects);
		set_user_meta($user_id,"authorized_for_all_object_commands",$authorized_for_all_object_commands);
		set_user_meta($user_id,"authorized_for_monitoring_system",$authorized_for_monitoring_system);
		set_user_meta($user_id,"advanced_user",$advanced_user);
		set_user_meta($user_id,"readonly_user",$readonly_user);
		
		// update nagios cgi config file
		update_nagioscore_cgi_config();
		
		// send email
		if(isset($request["sendemail"])){

			$email=$email;
			$username=$username;
			$password=$password1;
			$adminname=get_option("admin_name");
			$adminemail=get_option("admin_email");
			$url=get_option("url");
	
			$message=sprintf($lstr['AccountCreatedEmailMessage'],$username,$password,$url);
			$opts=array(
				"from" => $adminname." <".$adminemail.">\r\n",
				"to" => $email,
				"subject" => $lstr['AccountCreatedEmailSubject'],
				"message" => $message,
				);
			send_email($opts);
			}
			
		// log it
		if($level==L_GLOBALADMIN)
			send_to_audit_log("User account '".$original_user."' was created with GLOBAL ADMIN privileges",AUDITLOGTYPE_SECURITY);			

		// success!
		//header("Location: ?user_id[]=".$user_id."&edit=1&added");
		//show_users(false,$lstr['UserAddedText']);
		header("Location: ?useradded");
		}
		
	else{
	
		$oldlevel=get_user_meta($user_id,'userlevel');
	
		$oldname=get_user_attr($user_id,'username');
		if($username!=$oldname){
			change_user_attr($user_id,'username',$username);
			rename_nagioscore_contact($oldname,$username);
			}
		if($changepass==true)
			change_user_attr($user_id,'password',md5($password1));
		if($forcechangepass==true)
			set_user_meta($user_id,'forcepasswordchange',"1");
		else
			delete_user_meta($user_id,'forcepasswordchange');
		change_user_attr($user_id,'email',$email);
		change_user_attr($user_id,'name',$name);
		set_user_meta($user_id,'language',$language);
		set_user_meta($user_id,'theme',$theme);
		set_user_meta($user_id,"date_format",$date_format);
		set_user_meta($user_id,"number_format",$number_format);
		set_user_meta($user_id,'userlevel',$level);
		set_user_meta($user_id,"authorized_for_all_objects",$authorized_for_all_objects);
		set_user_meta($user_id,"authorized_to_configure_objects",$authorized_to_configure_objects);
		set_user_meta($user_id,"authorized_for_all_object_commands",$authorized_for_all_object_commands);
		set_user_meta($user_id,"authorized_for_monitoring_system",$authorized_for_monitoring_system);
		set_user_meta($user_id,"advanced_user",$advanced_user);
		set_user_meta($user_id,"readonly_user",$readonly_user);

		// set session vars if this is the current user
		if($user_id==$_SESSION["user_id"]){
			$_SESSION["language"]=$language;
			$_SESSION["theme"]=$theme;
			$_SESSION["date_format"]=$date_format;
			$_SESSION["number_format"]=$number_format;
			}

		// update nagios cgi config file
		update_nagioscore_cgi_config();

		// send email
		if(isset($request["sendemail"]) && $changepass==true){

			$email=$email;
			$username=$username;
			$password=$password1;
			$adminname=get_option("admin_name");
			$adminemail=get_option("admin_email");
			$url=get_option("url");
	
			$message=sprintf($lstr['PasswordChangedEmailMessage'],$username,$password,$url);
			$opts=array(
				"from" => $adminname." <".$adminemail.">\r\n",
				"to" => $email,
				"subject" => $lstr['PasswordChangedEmailSubject'],
				"message" => $message,
				);
			send_email($opts);
			}
			
		// log it (for privilege changes)
		if($level==L_GLOBALADMIN && $oldlevel!=L_GLOBALADMIN)
			send_to_audit_log("User account '".$original_user."' was granted GLOBAL ADMIN privileges",AUDITLOGTYPE_SECURITY);			
		if($level!=L_GLOBALADMIN && $oldlevel==L_GLOBALADMIN)
			send_to_audit_log("User account '".$original_user."' had GLOBAL ADMIN privileges revoked",AUDITLOGTYPE_SECURITY);			
			

		// success!
		//header("Location: ?user_id[]=".$user_id."&edit=1&updated");
		//show_users(false,$lstr['UserUpdatedText']);
		header("Location: ?userupdated");
		}
	}


function do_delete_user(){
	global $request;
	global $lstr;
	
	// check session
	check_nagios_session_protector();

	$errmsg=array();
	$errors=0;
	
	// check for errors
	if(in_demo_mode()==true)
		$errmsg[$errors++]=$lstr['DemoModeChangeError'];
	if(!isset($request["user_id"])){
		$errmsg[$errors++]=$lstr['NoUserAccountSelectedError'];
		}
	else{
		$user_id_arr=grab_request_var("user_id");
		foreach($user_id_arr as $user_id){
		
			// make sure user exists
			if(!is_valid_user_id($user_id)){
				$errmsg[$errors++]=$lstr['BadUserAccountError']." (ID=".$user_id.")";
				}
			// user can't delete their own account
			if($user_id==$_SESSION["user_id"]){
				$errmsg[$errors++]=$lstr['CannotDeleteOwnAccountError'];
				}
			}
		}
		
	// handle errors
	if($errors>0)
		show_users(true,$errmsg);
		
	// delete the accounts
	$user_id_arr=grab_request_var("user_id");
	foreach($user_id_arr as $user_id){
        update_nagioscore_cgi_config();
        $args=array(
            "username" => get_user_attr($user_id,'username'),
            );
        submit_command(COMMAND_NAGIOSXI_DEL_HTACCESS,serialize($args));
        delete_user_id($user_id);
		}

	// success!
	$users=count($request["user_id"]);
	if($users>1)
		show_users(false,$users." ".$lstr['UsersDeletedText']);
	else
		show_users(false,$lstr['UserDeletedText']);
	}

	

function show_clone_user($error=false,$msg=""){
	global $request;
	global $lstr;
	

	// defaults
	$email="";
	$username="";
	$name="";
	$add_contact=1;
	
	// get options
	$user_id=grab_request_var("user_id",0);
	if(is_array($user_id)){
		$user_id=current($user_id);
		}
	//echo "USERID";
	//print_r($user_id);
	//exit();
		
	if($error==false){
		if(isset($request["updated"]))
			$msg=$lstr['UserUpdatedText'];
		else if(isset($request["added"]))
			$msg=$lstr['UserAddedText'];
		}
		
		
	// make sure user exists first
	if(!is_valid_user_id($user_id)){
		show_users(true,$lstr['BadUserAccountError']." (ID=".$user_id.")");
		}
	
	$username=grab_request_var("username","");
	$email=grab_request_var("email","");
	$name=grab_request_var("name","");

	$password1="";
	$password2="";
	$forcepasswordchange=get_user_meta($user_id,"forcepasswordchange");
		
	$passwordbox1title=$lstr['NewPassword1BoxTitle'];
	$passwordbox2title=$lstr['NewPassword2BoxTitle'];
		
	$sendemail="0";
	$sendemailboxtitle=$lstr['SendAccountPasswordEmailBoxTitle'];

	$page_title=$lstr['CloneUserPageTitle'];
	$page_header=$lstr['CloneUserPageHeader'].": ".htmlentities(get_user_attr($user_id,"username"));
	$button_title=$lstr['CloneUserButton'];
		
	if($forcepasswordchange=="1")
		$forcechangechecked="CHECKED";
	else
		$forcechangechecked="";
	if($sendemail=="1")
		$sendemailchecked="CHECKED";
	else
		$sendemailchecked="";


	do_page_start(array("page_title"=>$page_title),true);

?>
	<h1><?php echo $page_header;?></h1>
	

<?php
	display_message($error,false,$msg);
?>

	<script type="text/javascript">
	$(document).ready(function() {
		$("#passwordBox1").change(function() {
			$("#updateForm").checkCheckboxes("#forcePasswordChangeBox", true);
			$("#updateForm").checkCheckboxes("#sendEmailBox", true);
			});
	});
	</script>
	
	<p>
	<?php echo $lstr['CloneUserDescription'];?>
	</p>
	
	<form id="updateForm" method="post" action="?">
	<input type="hidden" name="doclone" value="1">
	<?php echo get_nagios_session_protector();?>
	<input type="hidden" name="user_id[]" value="<?php echo encode_form_val($user_id);?>">

	<div class="sectionTitle"><?php echo $lstr['UserAccountGeneralSettingsSectionTitle'];?></div>

	<table class="editDataSourceTable">


	<tr>
	<td>
	<label for="usernameBox"><?php echo $lstr['UsernameBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="text" size="15" name="username" id="usernameBox" value="<?php echo encode_form_val($username);?>" class="textfield" /><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label for="passwordBox1"><?php echo $passwordbox1title;?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="password" size="10" name="password1" id="passwordBox1" value="<?php echo encode_form_val($password1);?>" class="textfield" /><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label for="passwordBox2"><?php echo $passwordbox2title;?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="password" size="10" name="password2" id="passwordBox2" value="<?php echo encode_form_val($password2);?>" class="textfield" /><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label for="forcePasswordChangeBox"><?php echo $lstr['ForcePasswordChangeNextLoginBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="forcePasswordChangeBox" name="forcepasswordchange" <?php echo $forcechangechecked;?>><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label for="sendEmailBox"><?php echo $sendemailboxtitle;?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="sendEmailBox" name="sendemail" <?php echo $sendemailchecked;?>><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label for="nameBox"><?php echo $lstr['NameBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="text" size="30" name="name" id="nameBox" value="<?php echo encode_form_val($name);?>" class="textfield" /><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label for="emailAddressBox"><?php echo $lstr['EmailBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="text" size="30" name="email" id="emailAddressBox" value="<?php echo encode_form_val($email);?>" class="textfield" /><br class="nobr" />
	</td>
	</tr>
	
	<tr>
	<td>
	<label for="addContactBox"><?php echo $lstr['CreateUserAsContactBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="addContactBox" name="add_contact" <?php echo is_checked($add_contact,1);?>><br class="nobr" />
	</td>
	</tr>

	</table>
	
	<div id="formButtons">
	<input type="submit" class="submitbutton" name="updateButton" value="<?php echo $button_title;?>" id="updateButton">
	<input type="submit" class="submitbutton" name="cancelButton" value="<?php echo $lstr['CancelButton'];?>" id="cancelButton">
	</div>
	
	<!--</fieldset>-->
	
	</form>
	
	<script type="text/javascript" language="JavaScript">
	document.forms['updateForm'].elements['usernameBox'].focus();
	</script>
	

<?php

	do_page_end(true);
	exit();
	}
	
	
function do_clone_user(){
	global $lstr;
	global $request;
	
	// user pressed the cancel button
	if(isset($request["cancelButton"])){
		show_users(false,"");
		exit();
		}
	
	// check session
	check_nagios_session_protector();

	$errmsg=array();
	$errors=0;

	$changepass=false;
	$user_id=0;
	$add=true;
	$add_contact=1;

	// get values
	$username=grab_request_var("username","");
	$email=grab_request_var("email","");
	$name=grab_request_var("name","");
	$password1=grab_request_var("password1","");
	$password2=grab_request_var("password2","");

	$add_contact=checkbox_binary(grab_request_var("add_contact",""));
	if($add_contact==1)
		$add_contact=true;
	else
		$add_contact=false;
		
	// check for errors
	if(in_demo_mode()==true)
		$errmsg[$errors++]=$lstr['DemoModeChangeError'];
	if(have_value($password1)==true && have_value($password2)==true){
		// user has entered a password
		if(have_value($password1)==true || have_value($password2)==true){
			if(strcmp($password1,$password2))
				$errmsg[$errors++]=$lstr['MismatchedPasswordError'];
			else
				$changepass=true;
			}
		}
	if(have_value($username)==false)
		$errmsg[$errors++]=$lstr['BlankUsernameError'];
	if(have_value($email)==false)
		$errmsg[$errors++]=$lstr['BlankEmailError'];
	else if(!valid_email($email))
		$errmsg[$errors++]=$lstr['InvalidEmailAddressError'];
	if(have_value($name)==false)
		$errmsg[$errors++]=$lstr['BlankNameError'];
		
	$user_id=grab_request_var("user_id",0);
	if(is_array($user_id)){
		$user_id=current($user_id);
		if($user_id!=0){
			$add=false;
			// make sure user exists
			if(!is_valid_user_id($user_id)){
				$errmsg[$errors++]=$lstr['BadUserAccountError']." (ID=".$user_id.")";
				}
			}
		}
		
	if(isset($request["forcepasswordchange"]))
		$forcechangepass=true;
	else
		$forcechangepass=false;
		
	// handle errors
	if($errors>0)
		show_clone_user(true,$errmsg);

	// log it
	$original_user=get_user_attr($user_id,"username");
	send_to_audit_log("User cloned account '".$original_user."'",AUDITLOGTYPE_SECURITY);
		
	// add the new user
	$level=get_user_meta($user_id,"userlevel");
	if(!($new_user_id=add_user_account($username,$password1,$name,$email,$level,$forcechangepass,$add_contact,$errmsg))){
		show_clone_user(true,$errmsg);
		}
	
	// copy over all meta data from original user
	$meta=get_all_user_meta($user_id);
	foreach($meta as $var => $val){

		// skip a few types of meta data
		if($var=="userlevel")
			continue;
		if($var=="forcepasswordchange")
			continue;
		if($var=="lastlogintime")
			continue;
		if($var=="timesloggedin")
			continue;

		set_user_meta($new_user_id,$var,$val);
		}
		
	// send email
	if(isset($request["sendemail"])){

		$email=$email;
		$username=$username;
		$password=$password1;
		$adminname=get_option("admin_name");
		$adminemail=get_option("admin_email");
		$url=get_option("url");
	
		$message=sprintf($lstr['AccountCreatedEmailMessage'],$username,$password,$url);
		$opts=array(
			"from" => $adminname." <".$adminemail.">\r\n",
			"to" => $email,
			"subject" => $lstr['AccountCreatedEmailSubject'],
			"message" => $message,
			);
		send_email($opts);
		}

	// success!
	header("Location: ?usercloned");
	}



function do_masquerade(){
	global $request;
	
	// check session
	check_nagios_session_protector();

	$user_id=grab_request_var("user_id",-1);
	
	if(!is_valid_user_id($user_id)){
		show_users(false,$lstr['InvalidUserAccountError']);
		exit();
		}
		
	// do the magic masquerade stuff...
	masquerade_as_user_id($user_id);
	
	// redirect to home page
	header("Location: ".get_base_url());
	}
	
?>
<?php //escalation_dependancy.inc.php
//escalation /dependency form 

/*
//XXX TODO: 
- For some reason contactgroups overlay is populating with hostgroups???
- Add pre-populated values to existing form elements
- Add unique service description list for hidden elements 
- Other object types 


*/


//host escalation:
	//hosts, hostgroups, contacts, contactgroups


//all	
	//Config Name

?>
<div id="visibleDivs">
<div id='tab1'>
	<div class="leftBox">  	

		  <label for="tfConfigName">*<?php echo gettext("Config Name"); ?></label><br />
		  <input name="tfConfigName" type="text" id="tfConfigName" class='required tf_wide' value="<?php @val($FIELDS['config_name']) ?>" /><br />		  
		  <br />	
		  
<?php ////////////ESCALATIONS///////////// 
if(strpos($this->exactType,'escalation')) { ?>

		<label>*<?php echo gettext("First Notification"); ?></label><br />
		<input type='textbox' class='required' name='tfFirstNotif' id='tfFirstNotif' size='2' value="<?php @val($FIELDS['first_notification']); ?>" /><br />
		<label for='tfLastNotif'>*<?php echo gettext("Last Notification"); ?></label><br />
		<input type='text' class='required' name='tfLastNotif' id='tfLastNotif' size='2' value="<?php @val($FIELDS['last_notification']); ?>" /><br />
		<label for='tfNotifInterval'>*<?php echo gettext("Notification Interval"); ?></label><br />
		<input type='text' class='required' name='tfNotifInterval' id='tfNotifInterval' size='2' value="<?php @val($FIELDS['notification_interval']); ?>" />mn<br />
		
		<label>*<?php echo gettext("Escalation Options"); ?></label><br />
<?php if($this->exactType =='hostescalation') { ?>		
		<input type='checkbox' name='chbEOd' id='chbEOd' <?php @check('escalation_options', 'd'); ?>/><label for='chbEOd'> d </label><br />
		<input type='checkbox' name='chbEOu' id='chbEOu' <?php @check('escalation_options', 'u'); ?>/><label for='chbEOu'> u </label><br />
		<input type='checkbox' name='chbEOr' id='chbEOr' <?php @check('escalation_options', 'r'); ?>/><label for='chbEOr'> r </label><br />	
<?php 
} //end IF hostescalation 
	  if($this->exactType =='serviceescalation') { ?>
		<!-- service escalation options --> 
		<input type='checkbox' name='chbEOw' id='chbEOw' <?php @check('escalation_options', 'w'); ?>/><label for='chbEOd'> w </label><br />
		<input type='checkbox' name='chbEOu' id='chbEOu' <?php @check('escalation_options', 'u'); ?>/><label for='chbEOu'> u </label><br />
		<input type='checkbox' name='chbEOc' id='chbEOc' <?php @check('escalation_options', 'c'); ?>/><label for='chbEOu'> c </label><br />		
		<input type='checkbox' name='chbEOr' id='chbEOr' <?php @check('escalation_options', 'r'); ?>/><label for='chbEOr'> r </label><br />			

<?php
	}
/////////////////end IF escalation////////////////
} 		
///////////////IF DEPENDENCY/////////////////////
if(strpos($this->exactType,'dependency')) {
?>
		<label for='chbInherit'><?php echo gettext("Inherit Parents"); ?></label>
		<input class="checkbox" type="checkbox" value="1" name="chbInherit" id="chbInherit" <?php @check('inherits_parent',1); ?> /><br />
		<br />
		<label for=''><?php echo gettext("Execution failure criteria"); ?></label><br />
		<input id="chbEOo" class="checkbox" type="checkbox" value="o" name="chbEOo" <?php @check('execution_failure_criteria', 'o'); ?>/>
		<label for='chbEOo'> o </label>
<?php
	if($this->exactType=='hostdependency')
		print "<input id='chbEOd' class='checkbox' type='checkbox' value='d' name='chbEOd' ".@check('execution_failure_criteria', 'd',true)." /><label for='chbEOd'> d </label>\n";
	if($this->exactType=='servicedependency') {
		 print "<input id='chbEOw' class='checkbox' type='checkbox' value='w' name='chbEOw' ".@check('execution_failure_criteria', 'w',true)."/><label for='chbEOw'> w </label>\n";	
		 print "<input id='chbEOc' class='checkbox' type='checkbox' value='c' name='chbEOc' ".@check('execution_failure_criteria', 'c',true)."/><label for='chbEOc'> c </label>\n";
	}	 
?>			
		<input id="chbEOu" class="checkbox" type="checkbox" value="u" name="chbEOu" <?php @check('execution_failure_criteria', 'u'); ?>/>
		<label for='chbEOu'> u </label>		
		<input id="chbEOp" class="checkbox" type="checkbox" value="p" name="chbEOp" <?php @check('execution_failure_criteria', 'p'); ?>/>
		<label for='chbEOp'> p </label>
		<input id="chbEOn" class="checkbox" type="checkbox" value="n" name="chbEOn" <?php @check('execution_failure_criteria', 'n'); ?>/>
		<label for='chbEOn'> n </label><br />
		<br />
		<label for=''><?php echo gettext("Notification failure criteria"); ?></label><br />
		<input id="chbNOo" class="checkbox" type="checkbox" value="o" name="chbNOo" <?php @check('notification_failure_criteria', 'o'); ?>/>
		<label for='chbNOo'> o </label>
<?php
	if($this->exactType=='hostdependency')
		print "<input id='chbNOd' class='checkbox' type='checkbox' value='d' name='chbNOd' ".@check('notification_failure_criteria', 'd',true)."/><label for='chbNOd'> d </label>\n";
	if($this->exactType=='servicedependency') {
		 print "<input id='chbNOw' class='checkbox' type='checkbox' value='w' name='chbNOw'".@check('notification_failure_criteria', 'w',true)."/><label for='chbNOw'> w </label>\n";	
		 print "<input id='chbNOc' class='checkbox' type='checkbox' value='c' name='chbNOc'".@check('notification_failure_criteria', 'c',true)."/><label for='chbNOc'> c </label>\n";
	}	 
?>		
		<input id="chbNOu" class="checkbox" type="checkbox" value="u" name="chbNOu" <?php @check('notification_failure_criteria', 'u'); ?>/>
		<label for='chbNOu'> u </label>		
		<input id="chbNOp" class="checkbox" type="checkbox" value="p" name="chbNOp" <?php @check('notification_failure_criteria', 'p'); ?>/>
		<label for='chbNOp'> p </label>
		<input id="chbNOn" class="checkbox" type="checkbox" value="n" name="chbNOn" <?php @check('notification_failure_criteria', 'n'); ?>/>
		<label for='chbNOn'> n </label><br /><br />		

<?php 
////////////////end IF DEPENDENCY///////////////
}
?>
		<label for='selPeriod'><?php echo get_page_title($this->exactType); ?> <?php echo gettext("Period"); ?></label><br />
		<select name='selPeriod' id='selPeriod'>
		<!-- options from DB -->
		
<?php
	    //timeperiod options
		print '<option value="0">&nbsp;</option>';	
		foreach($FIELDS['selTimeperiods'] as $opt)
		{
				print "<option ";
				if(isset($FIELDS['escalation_period']) && $FIELDS['escalation_period'] == $opt['id']) echo 'selected="selected" ';
				if(isset($FIELDS['dependency_period']) && $FIELDS['dependency_period'] == $opt['id']) echo 'selected="selected" ';
				print " value='".$opt['id']."'>".$opt['timeperiod_name']."</option>\n";
		}		
?>	
	
		</select><br /><br />
							
	</div><!-- end leftbox -->
		
	<div class="rightBox">	

		<!-- manage object select lists -->	
		<br /><br /><br />
		<p><a class="linkBox" href="javascript:overlay('hostBox')" title="Assign to Hosts"><?php echo gettext("Manage Hosts"); ?></a></p>
		<p><a class='linkBox' href="javascript:overlay('hostgroupBox')" title="Manage Hostgroups"><?php echo gettext("Manage Hostgroups"); ?></a></p>	
		
<?php 
	//print "manage <objects>" links based on object type  
	if(strpos($this->exactType,'escalation')) {		
		print "<p><a class='linkBox' href=\"javascript:overlay('contactBox')\" title='Manage Contacts'>".gettext("Manage Contacts")."</a></p>";
		print "<p><a class='linkBox' href=\"javascript:overlay('contactgroupBox')\" title='Manage Hostgroups'>".gettext("Manage Contactgroups")."</a></p>";	
	} //end dependency IF 
	//service specific items 
	if($this->exactType =='serviceescalation' || $this->exactType=='servicedependency') {	
		print "<p><a class='linkBox' href=\"javascript:overlay('serviceBox')\" title='Manage Services'>".gettext("Manage Services")."</a></p>";
	} 
	if($this->exactType =='hostdependency' || $this->exactType=='servicedependency'){
		print "<p><a class='linkBox' href=\"javascript:overlay('hostdependencyBox')\" title='Manage Dependent Hosts'>".gettext("Manage Dependent Hosts")."</a></p>";
		print "<p><a class='linkBox' href=\"javascript:overlay('hostgroupdependencyBox')\" title='Manage Dependent Hostgroups'>".gettext("Manage Dependent Hostgroups")."</a></p>";	
	}
	if($this->exactType=='servicedependency') {
		print "<p><a class='linkBox' href=\"javascript:overlay('servicedependencyBox')\" title='Manage Dependent Services'>".gettext("Manage Service Dependencies")."</a></p>";
	}

?>		
		
		
		<label for="chbActive"><?php echo gettext("Active"); ?></label>
	   <input name="chbActive" type="checkbox" class="checkbox" id="chbActive" value="1" 
	    <?php if((isset($FIELDS['active']) && $FIELDS['active'] == '1') || !isset($FIELDS['active'])) echo 'checked="checked" '; ?> />
	    <br />			  	 
	
	</div><!-- end rightBox -->

</div> <!-- end tab1 -->	      
</div> <!-- end visible divs -->

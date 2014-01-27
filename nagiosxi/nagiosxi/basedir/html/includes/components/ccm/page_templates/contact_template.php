<?php  //contact_template.php 

//print_r($FIELDS['pre_hostcommands']);
//print_r($FIELDS['pre_servicecommands']); 

$name = ($this->exactType=='contact') ? 'contact_name' : 'template_name'; 
?>



<div id="visibleDivs">

<div id="tab1">

	<div class="leftBox">
	<h3><?php echo gettext("Common Settings"); ?></h3>	  	

			<label for="tfName"><?php echo get_page_title($this->exactType); ?> <?php echo gettext("Name"); ?>*</label><br />
	      <input name="tfName" type="text" id="tfName" value="<?php @val($FIELDS[$name]) ?>" class="required tf_wide" /><br /> 
    
		  <label for="tfFriendly"><?php echo gettext("Description"); ?></label><br />
			<input name="tfFriendly" class='tf_wide' type="text" id="tfFriendly" value="<?php @val($FIELDS['alias']) ?>" />
			<br /> 	
			<!-- addresses and contact stuff --> 
			<label for="tfEmail"><?php echo gettext("Email Address"); ?></label><br />
			<input type="text" value="<?php @val($FIELDS['email']) ?>" class='tf_wide' id="tfEmail" name="tfEmail" /><br />
			<label for="tfPager"><?php echo gettext("Pager Number"); ?></label><br />
			<input type="text" value="<?php @val($FIELDS['pager']) ?>" class='tf_wide' id="tfPager" name="tfPager" /><br />
		
		<br />	
	<p><a class='linkBox' href="javascript:overlay('contactgroupBox')" title="Manage Contact Groups"><?php echo gettext("Manage Contact Groups"); ?></a></p>	

	<p><a class='linkBox' href="javascript:overlay('contacttemplateBox')" title="Manage Contact Templates"><?php echo gettext("Manage Contact Templates"); ?></a></p><br />
			
		<label for="chbActive"><?php echo gettext("Active"); ?></label>
	    <input name="Active" type="checkbox" class="checkbox" id="Active" value="1" <?php if((isset($FIELDS['active']) && $FIELDS['active'] == '1') || !isset($FIELDS['active'])) echo 'checked="checked" '; ?> />
	    <br />
			
	</div> <!-- end leftBox -->
	<div class="rightBox">		
			
			<label for="tfAddress1"><?php echo gettext("Addon Address 1"); ?></label><br />
			<input type="text" value="<?php @val($FIELDS['address1']) ?>" id="tfAddress1" name="tfAddress1" class='tf_wide' /><br />			
			<label for="tfAddress2"><?php echo gettext("Addon Address 2"); ?></label><br />
			<input type="text" value="<?php @val($FIELDS['address2']) ?>" id="tfAddress2" name="tfAddress2" class='tf_wide' /><br />
			<label for="tfAddress3"><?php echo gettext("Addon Address 3"); ?></label><br />
			<input type="text" value="<?php @val($FIELDS['address3']) ?>" id="tfAddress3" name="tfAddress3" class='tf_wide' /><br />
			<label for="tfAddress4"><?php echo gettext("Addon Address 4"); ?></label><br />
			<input type="text" value="<?php @val($FIELDS['address4']) ?>" id="tfAddress4" name="tfAddress4" class='tf_wide' /><br />
			<label for="tfAddress5"><?php echo gettext("Addon Address 5"); ?></label><br />
			<input type="text" value="<?php @val($FIELDS['address5']) ?>" id="tfAddress5" name="tfAddress5" class='tf_wide' /><br />
			<label for="tfAddress6"><?php echo gettext("Addon Address 6"); ?></label><br />
			<input type="text" value="<?php @val($FIELDS['address6']) ?>" id="tfAddress6" name="tfAddress6" class='tf_wide' /><br />
	
	</div> <!-- end rightBox -->	

</div> <!-- end tab1 --> 

<div id="tab2">

			<h3><?php echo gettext("Alert Settings"); ?></h3>
	<div class="leftBox">	
	 	<br />
	<!-- host /service notifications enabled? -->
	       <label><?php echo gettext("Host Notifications Enabled"); ?></label><br />           
          	 <input name="radHostNotifEnabled" type="radio" class="checkbox" id="radHostNotifEnabled1" value="1" <?php @check('host_notifications_enabled', '1'); ?> /> 
             <label for="radHostNotifEnabled1"><?php echo gettext("on"); ?>&nbsp;</label> 
             <input name="radHostNotifEnabled" type="radio" class="checkbox" id="radHostNotifEnabled0" value="0" <?php @check('host_notifications_enabled', '0'); ?> /> 
            <label for="radHostNotifEnabled0"><?php echo gettext("off"); ?>&nbsp;</label>
             <input name="radHostNotifEnabled" type="radio" class="checkbox" id="radHostNotifEnabled2" value="2" <?php @check('host_notifications_enabled', '2'); ?> /> 
            <label for="radHostNotifEnabled2"><?php echo gettext("skip"); ?>&nbsp;</label>
            <input name="radHostNotifEnabled" type="radio" class="checkbox" id="radHostNotifEnabled3" value="3" <?php @check('host_notifications_enabled', '3'); ?> /> 
            <label for="radHostNotifEnabled3"><?php echo gettext("null"); ?>&nbsp;</label>
             &nbsp;
             <img src="/nagiosql/images/tip.gif" class="helpImage" alt="Help" title="Help"  onclick="dialoginit('host','notification_enabled','all','Info')"  /> 
		   	<br /><br />
		  
		  <label for="selHostPeriod"><?php echo gettext("Host Notifications Timeperiod"); ?></label> <br />	
		  <select id="selHostPeriod" name="selHostPeriod">

<?php		//insert timeperiod options
				print '<option ';
				if(isset($FIELDS['host_notification_period']) &&$FIELDS['host_notification_period'] == '0') echo 'selected="selected" ';
				print ' value="0">&nbsp;</option>';	

				foreach($FIELDS['selTimeperiods'] as $opt)
				{ 					
					print "<option ";
					//.selected('check_period', $opt['id']).
					if(isset($FIELDS['host_notification_period']) && $FIELDS['host_notification_period'] == $opt['id']) echo 'selected="selected" ';
					print " value='".$opt['id']."'>".$opt['timeperiod_name']."</option>\n";
        		}	        		
?>				
				
        </select><br /> 	
        
        
        	<!-- notification options -->
		  <label for=""><?php echo gettext("Host Notification options"); ?></label>  <br />           
          	 <input name="chbHOd" type="checkbox" class="checkbox" id="chbHOd3" value="d" <?php @check('host_notification_options', 'd'); ?> /> 
            <label for="chbHOd3">d</label> &nbsp;
             <input name="chbHOu" type="checkbox" class=" checkbox" id="chbHOu3" value="u" <?php @check('host_notification_options', 'u'); ?> /> 
            <label for="chbHOu3">u</label>&nbsp; 
             <input name="chbHOr" type="checkbox" class=" checkbox" id="chbHOr3" value="r" <?php @check('host_notification_options', 'r'); ?> /> 
             <label for="chbHOr3">r</label> &nbsp;
             <input name="chbHOf" type="checkbox" class=" checkbox" id="chbHOf3" value="f" <?php @check('host_notification_options', 'f'); ?> /> 
            <label for="chbHOf3">f</label> &nbsp;
             <input name="chbHOs" type="checkbox" class=" checkbox" id="chbHOs3" value="s" <?php @check('host_notification_options', 's'); ?> /> 
            <label for="chbHOs3">s</label> &nbsp;
            <input name="chbHOn" type="checkbox" class=" checkbox" id="chbHOn3" value="n" <?php @check('host_notification_options', 'n'); ?> /> 
            <label for="chbHOn3">n</label> &nbsp;
             &nbsp;
             <img src="/nagiosql/images/tip.gif" class="helpImage" alt="Help" title="Help"  onclick="dialoginit('host','notification_options','all','Info')"  /> 
		   	<br /><br />    
		   	
		   <p><a class='linkBox' href="javascript:overlay('hostcommandBox')" title="Manage Host Commands"><?php echo gettext("Manage Host Notification Commands"); ?></a></p>	<br />
		   
		          <label for=""><?php echo gettext("Retain status information"); ?></label> <br />          
          	 <input name="radStatusInfos" type="radio" class="checkbox" id="radStatusInfos1" value="1" <?php @check('retain_status_information', '1'); ?> /> 
             <label for="radStatusInfos1"><?php echo gettext("on"); ?>&nbsp;</label> 
             <input name="radStatusInfos" type="radio" class="checkbox" id="radStatusInfos0" value="0" <?php @check('retain_status_information', '0'); ?> /> 
             <label for="radStatusInfos0"><?php echo gettext("off"); ?>&nbsp;</label> 
             <input name="radStatusInfos" type="radio" class="checkbox" id="radStatusInfos2" value="2" <?php @check('retain_status_information', '2'); ?> /> 
             <label for="radStatusInfos2"><?php echo gettext("skip"); ?>&nbsp;</label> 
            <input name="radStatusInfos" type="radio" class="checkbox" id="radStatusInfos3" value="3" <?php @check('retain_status_information', '3'); ?> /> 
            <label for="radStatusInfos3"><?php echo gettext("null"); ?>&nbsp;</label> 
             &nbsp;
             <img src="/nagiosql/images/tip.gif" class="helpImage" alt="Help" title="Help"  onclick="dialoginit('host','retain_status_information','all','Info')"  /> 
		   	<br /><br />
                 
	   <label for=""><?php echo gettext("Retain non-status information"); ?></label> <br />
          	 <input name="radNoStatusInfos" type="radio" class="checkbox" id="radNoStatusInfos1" value="1" <?php @check('retain_nonstatus_information', '1'); ?> /> 
             <label><?php echo gettext("on"); ?>&nbsp;</label> 
             <input name="radNoStatusInfos" type="radio" class="checkbox" id="radNoStatusInfos0" value="0" <?php @check('retain_nonstatus_information', '0'); ?> /> 
            <label><?php echo gettext("off"); ?>&nbsp;</label>
             <input name="radNoStatusInfos" type="radio" class="checkbox" id="radNoStatusInfos2" value="2" <?php @check('retain_nonstatus_information', '2'); ?> /> 
            <label><?php echo gettext("skip"); ?>&nbsp;</label>

            <input name="radNoStatusInfos" type="radio" class="checkbox" id="radNoStatusInfos3" value="3" <?php @check('retain_nonstatus_information', '3'); ?> /> 
            <label><?php echo gettext("null"); ?>&nbsp;</label>
             &nbsp;
             <img src="/nagiosql/images/tip.gif" class="helpImage" alt="Help" title="Help"  onclick="dialoginit('host','retain_nonstatus_information','all','Info')"  /> 
		   	<br /><br />
		   	

		   	
	</div>	<!-- end leftBox -->
	
<!-- /////////////////////////////////////////////////RIGHT BOX////////////////////////// -->	
	
	<div class="rightBox">			

<!-- TODO: these need to change for service states, also rename boxes to match input logic -->	
		   	<br />
		   	<label for=""><?php echo gettext("Service Notifications Enabled"); ?></label><br />           
          	 <input name="radServiceNotifEnabled" type="radio" class="checkbox" id="radServiceNotifEnabled1" value="1" <?php @check('service_notifications_enabled', '1'); ?> /> 
             <label for="radServiceNotifEnabled1"><?php echo gettext("on"); ?>&nbsp;</label> 
             <input name="radServiceNotifEnabled" type="radio" class="checkbox" id="radServiceNotifEnabled0" value="0" <?php @check('service_notifications_enabled', '0'); ?> /> 
            <label for="radServiceNotifEnabled0"><?php echo gettext("off"); ?>&nbsp;</label>
             <input name="radServiceNotifEnabled" type="radio" class="checkbox" id="radServiceNotifEnabled2" value="2" <?php @check('service_notifications_enabled', '2'); ?> /> 
            <label for="radServiceNotifEnabled2"><?php echo gettext("skip"); ?>&nbsp;</label>
            <input name="radServiceNotifEnabled" type="radio" class="checkbox" id="radServiceNotifEnabled3" value="3" <?php @check('service_notifications_enabled', '3'); ?> /> 
            <label for="radServiceNotifEnabled3"><?php echo gettext("null"); ?>&nbsp;</label>
             &nbsp;
             <img src="/nagiosql/images/tip.gif" class="helpImage" alt="Help" title="Help"  onclick="dialoginit('host','notification_enabled','all','Info')"  /> 
		   	<br /><br />
		   	
		   <label for="selServicePeriod"><?php echo gettext("Service Notifications Timeperiod"); ?></label> <br />	
		  <select id="selServicePeriod" name="selServicePeriod">

<?php		//insert timeperiod options
				print '<option ';
				if(isset($FIELDS['service_notification_period']) &&$FIELDS['service_notification_period'] == '0') echo 'selected="selected" ';
				print ' value="0">&nbsp;</option>';	

				foreach($FIELDS['selTimeperiods'] as $opt)
				{ 					
					print "<option ";
					//.selected('check_period', $opt['id']).
					if(isset($FIELDS['service_notification_period']) && $FIELDS['service_notification_period'] == $opt['id']) echo 'selected="selected" ';
					print " value='".$opt['id']."'>".$opt['timeperiod_name']."</option>\n";
        		}	        		
?>				
				
        </select><br /> 	

	<!-- notification options -->
		  <label for=""><?php echo gettext("Service Notification options"); ?></label> <br />           
          	 <input name="chbSOw" type="checkbox" class=" checkbox" id="chbSOw3" value="w" <?php @check('service_notification_options', 'w'); ?> /> 
            <label for="chbSOw3">w</label> &nbsp;
             <input name="chbSOu" type="checkbox" class=" checkbox" id="chbSOu3" value="u" <?php @check('service_notification_options', 'u'); ?> /> 
            <label for="chbSOu3">u</label> &nbsp;
             <input name="chbSOc" type="checkbox" class=" checkbox" id="chbSOc3" value="c" <?php @check('service_notification_options', 'c'); ?> /> 
             <label for="chbSOc3">c</label> &nbsp;
             <input name="chbSOf" type="checkbox" class=" checkbox" id="chbSOf3" value="f" <?php @check('service_notification_options', 'f'); ?> /> 
            <label for="chbSO3f">f</label> &nbsp;
             <input name="chbSOs" type="checkbox" class=" checkbox" id="chbSOs3" value="s" <?php @check('service_notification_options', 's'); ?> /> 
            <label for="chbSOs3">s</label> &nbsp;
            <input name="chbSOr" type="checkbox" class=" checkbox" id="chbSOr3" value="r" <?php @check('service_notification_options', 'r'); ?> /> 
            <label for="chbSOr3">r</label> &nbsp;
            <input name="chbSOn" type="checkbox" class=" checkbox" id="chbSOn3" value="n" <?php @check('service_notification_options', 'n'); ?> /> 
            <label for="chbSOn3">n</label>  &nbsp;           
             &nbsp;
             <img src="/nagiosql/images/tip.gif" class="helpImage" alt="Help" title="Help"  onclick="dialoginit('host','notification_options','all','Info')"  /> 
		   	<br /><br />         
		   	
		<p><a class='linkBox' href="javascript:overlay('servicecommandBox')" title="Manage Service Commands"><?php echo gettext("Manage Service Notification Commands"); ?></a></p><br />   
		
			<label><?php echo gettext("Can Submit Commands"); ?></label>  <br />
			
		 	<input type="radio" value="1" id="radCanSubCmds1" class="checkbox" name="radCanSubCmds" <?php @check('can_submit_commands', '1'); ?> />
		 	<label for="radCanSubCmds1"><?php echo gettext("On"); ?></label>&nbsp;
		 	
		 	<input type="radio" value="0" id="radCanSubCmds0" class="checkbox" name="radCanSubCmds" <?php @check('can_submit_commands', '0'); ?> />
		 	<label for="radCanSubCmds1"><?php echo gettext("Off"); ?></label>&nbsp;
		 	
		 	<input type="radio" value="2" id="radCanSubCmds2" class="checkbox" name="radCanSubCmds" <?php @check('can_submit_commands', '2'); ?> />
		 	<label for="radCanSubCmds1"><?php echo gettext("Skip"); ?></label>&nbsp;
		 	
		 	<input type="radio" value="3" id="radCanSubCmds3" class="checkbox" name="radCanSubCmds" <?php @check('can_submit_commands', '3'); ?> />
		 	<label for="radCanSubCmds1"><?php echo gettext("Null"); ?></label>&nbsp;
		 	<br />	     

	</div>	<!-- end rightBox -->
</div><!-- end tab 2 -->




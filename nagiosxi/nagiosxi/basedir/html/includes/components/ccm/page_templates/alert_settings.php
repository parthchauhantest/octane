 <div id="tab3"> 
 	
      <h3><?php echo gettext("Alert Settings"); ?></h3> 
	<div class='rightBox'>
	<p><a class="wideLinkBox" href="javascript:overlay('contactBox')" title="Manage Contacts"><?php echo gettext("Manage Contacts"); ?></a></p>	
	                      
<!-- moved Contacts to hidden field -->
	<p><a class="wideLinkBox" href="javascript:overlay('contactgroupBox')" title="Manage Contactgroups"><?php echo gettext("Manage Contactgroups"); ?></a></p>
				   

     <label for="selNotifPeriod"><?php echo gettext("Notification period"); ?>*</label><br /> 
      <select name="selNotifPeriod" id="selNotifPeriod">
		<!-- options from DB -->
<?php  //timeperiod options
				print '<option ';
				if(isset($FIELDS['notification_period']) && $FIELDS['notification_period'] == '0') echo 'selected="selected" ';
				print ' value="0">&nbsp;</option>';	
				
				foreach($FIELDS['selTimeperiods'] as $opt)
				{
						print "<option ";
						if(isset($FIELDS['notification_period']) && $FIELDS['notification_period'] == $opt['id']) echo 'selected="selected" ';
						print " value='".$opt['id']."'>".$opt['timeperiod_name']."</option>\n";
				}		
?>		
        </select> 
	  		<br />
	  		<br />
	  
	  <label for="chbNOd"><?php echo gettext("Notification options"); ?>: </label>    
<?php if($this->exactType=='host' || $this->exactType=='hosttemplate') { ?>	  
          	 <input name="chbNOd" type="checkbox" class=" checkbox" id="chbNOd" value="d" <?php @check('notification_options', 'd'); ?> /> 
            <label for="chbNOd"> d </label> 
<?php 
		} //else type is service 
		else {  ?>
          	 <input name="chbNOw" type="checkbox" class=" checkbox" id="chbNOw" value="w" <?php @check('notification_options', 'w'); ?> /> 
            <label for="chbNOw"> w </label> 
             <input name="chbNOc" type="checkbox" class=" checkbox" id="chbNOc" value="c" <?php @check('notification_options', 'c'); ?> /> 
            <label for="chbNOu"> c </label>			
		
<?php 	} ?>		
             <input name="chbNOu" type="checkbox" class=" checkbox" id="chbNOu" value="u" <?php @check('notification_options', 'u'); ?> /> 
            <label for="chbNOu"> u </label> 	
             <input name="chbNOr" type="checkbox" class=" checkbox" id="chbNOr" value="r" <?php @check('notification_options', 'r'); ?> /> 
             <label for="chbNOr"> r </label> 
             <input name="chbNOf" type="checkbox" class=" checkbox" id="chbNOf" value="f" <?php @check('notification_options', 'f'); ?> /> 
            <label for="chbNOf"> f </label> 
             <input name="chbNOs" type="checkbox" class=" checkbox" id="chbNOs" value="s" <?php @check('notification_options', 's'); ?> /> 
            <label for="chbNOs"> s </label> 
		   	<br />
            <div class='padd' ></div>    
     
 	 
       <label for="tfNotifInterval"><?php echo gettext("Notification interval"); ?> </label> 
       <input name="tfNotifInterval" type="text" size='3' id="tfNotifInterval" value="<?php @val($FIELDS['notification_interval']); ?>" /><?php echo gettext("min"); ?> 
	   	<br />
	   	<div class='padd' ></div>
	   
	   <label for="tfFirstNotifDelay"><?php echo gettext("First notification delay"); ?> </label>
	   <input name="tfFirstNotifDelay" type="text" size='3' id="tfFirstNotifDelay" value="<?php @val($FIELDS['first_notification_delay']); ?>" /><?php echo gettext("min"); ?>
      <br />
      <div class='padd' ></div>	 
    
	</div> <!-- end leftbox -->    
    
    <div class='rightBox'> 	 
       <label for="radNotifEnabled1"><?php echo gettext("Notification enabled"); ?>:</label><br />           
          	 <input name="radNotifEnabled" type="radio" class="checkbox" id="radNotifEnabled1" value="1" <?php @check('notifications_enabled', '1'); ?> /> 
             <label for="radNotifEnabled1"><?php echo gettext("on"); ?>&nbsp;</label> 
             <input name="radNotifEnabled" type="radio" class="checkbox" id="radNotifEnabled0" value="0" <?php @check('notifications_enabled', '0'); ?> /> 
            <label for="radNotifEnabled0"><?php echo gettext("off"); ?>&nbsp;</label>
             <input name="radNotifEnabled" type="radio" class="checkbox" id="radNotifEnabled2" value="2" <?php @check('notifications_enabled', '2'); ?> /> 
            <label for="radNotifEnabled2"><?php echo gettext("skip"); ?>&nbsp;</label>
            <input name="radNotifEnabled" type="radio" class="checkbox" id="radNotifEnabled3" value="3" <?php @check('notifications_enabled', '3'); ?> /> 
            <label for="radNotifEnabled3"> <?php echo gettext("null"); ?> </label>
		   	<br />
		   <div class='padd' ></div>
                 
	   <label for="chbSTo"><?php echo gettext("Stalking options"); ?>: </label><br /> 
<?php	if($this->exactType=='host' || $this->exactType=='hosttemplate') { ?>
             <input name="chbSTd" type="checkbox" class=" checkbox" id="chbSTd" value="d" <?php @check('stalking_options', 'd'); ?> /> 
            <label for="chbSTd">d</label> 
<?php 	} //else type is service
		else { ?>
         	 <input name="chbSTw" type="checkbox" class=" checkbox" id="chbSTw" value="w" <?php @check('stalking_options', 'w'); ?> /> 
             <label for="chbSTw">w</label> 
			 <input name="chbSTc" type="checkbox" class=" checkbox" id="chbSTc" value="c" <?php @check('stalking_options', 'c'); ?> /> 
             <label for="chbSTc">c</label> 
 
<?php	}	?>		
         	 <input name="chbSTo" type="checkbox" class=" checkbox" id="chbSTo" value="o" <?php @check('stalking_options', 'o'); ?> /> 
             <label for="chbSTo">o</label> 
             <input name="chbSTu" type="checkbox" class=" checkbox" id="chbSTu" value="u" <?php @check('stalking_options', 'u'); ?> /> 
            <label for="chbSTu">u</label> 	
		   	<br />
		   	<div class='padd' ></div>
	</div> <!-- end rightbox -->
	</div><!-- end tab3 -->
	
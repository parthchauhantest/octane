<?php  //group_template.php


?>

<div id="visibleDivs">

<div id="tab1">

	<div class="leftBox">
	<h3><?php echo get_page_title($this->exactType); ?> <?php echo gettext("Management"); ?></h3>	  	

			<label for="tfName"><?php echo get_page_title($this->exactType); ?> <?php echo gettext("Name"); ?>*</label><br />
	      <input name="tfName" type="text" id="tfName" value="<?php @val($FIELDS[$this->exactType.'_name']) ?>" class="required tf_wide" /><br /> 
    
		  <label for="tfFriendly"><?php echo gettext("Description"); ?>*</label><br />
			<input name="tfFriendly" class='required tf_wide' type="text" id="tfFriendly" value="<?php @val($FIELDS['alias']) ?>" />
			<br /> 		
				  
<?php if($this->exactType=='hostgroup' || $this->exactType=='servicegroup') { ?> 
	  
		<label for="tfNotes"><?php echo gettext("Notes"); ?></label> <br />
      <input name="tfNotes" type="text" id="tfNotes" value="<?php @val($FIELDS['notes']); ?>" class="tf_wide" /> 
      <br />
      <label for="tfNotesURL"><?php echo gettext("Notes URL"); ?></label> <br />
       <input name="tfNotesURL" type="text" id="tfNotesURL" value="<?php @val($FIELDS['notes_url']); ?>" class="tf_wide" />
       <br />		
       <label for="tfActionURL"><?php echo gettext("Action URL"); ?></label> <br />
       <input name="tfActionURL" type="text" id="tfActionURL" value="<?php @val($FIELDS['action_url']); ?>" class="tf_wide" /> 
       <br /> 	
       
<?php } //close IF for hostgroup and servicegroup 
?>       
       	
       <label for="chbActive"><?php echo gettext("Active"); ?></label>
	    <input name="chbActive" type="checkbox" class="checkbox" id="chbActive" value="1" <?php if((isset($FIELDS['active']) && $FIELDS['active'] == '1') || !isset($FIELDS['active'])) echo 'checked="checked" '; ?>" />
	    <br />
	    			
	</div><!-- end leftbox -->
		
		<div class="rightBox">	
			<h3><?php echo gettext("Memberships"); ?></h3>
<?php if($this->exactType =='hostgroup') { ?> 			
			<!-- Manage Hosts -->
			<p><a class='linkBox' href="javascript:overlay('hostBox')" title="Assign to Hosts"><?php echo gettext("Manage Hosts"); ?></a></p>
			<!-- manage hostgroups -->				
			<p><a class='linkBox' href="javascript:overlay('hostgroupBox')" title="Manage Hostgroups"><?php echo gettext("Manage Hostgroups"); ?></a></p>
			
<?php 
} //close hostgroup IF 
if($this->exactType == 'servicegroup') { ?>		

			<!-- manage services -->			
			<p><a class='linkBox' href="javascript:overlay('hostserviceBox')" title="Manage Services"><?php echo gettext("Manage Services"); ?></a></p>						
			<!-- manage servicegroups -->			
			<p><a class='linkBox' href="javascript:overlay('servicegroupBox')" title="Manage Servicegroups"><?php echo gettext("Manage Servicegroup"); ?></a></p>	

<?php 
}//close servicegroup IF 

if($this->exactType=='contactgroup') {
 ?> 

	<p><a class='linkBox' href="javascript:overlay('contactBox')" title="Manage Contacts"><?php echo gettext("Manage Contacts"); ?></a></p>	
	                      
<!-- moved Contacts to hidden field -->
	<p><a class='linkBox' href="javascript:overlay('contactgroupBox')" title="Manage Contactgroups"><?php echo gettext("Manage Contactgroups"); ?></a></p>


<?php 
} //close contactgroup IF  
?>
				  	 
	  	</div><!-- end rightBox -->

	 </div>   <!-- end tab 1 --> 
  
  </div>	 <!-- end visible divs --> 


	 
	 
	      

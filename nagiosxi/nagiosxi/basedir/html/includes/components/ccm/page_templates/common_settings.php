<?php //common_settings.php    common settings tab for CCM form 



// '44!3000.0!80%!5000.0!100%'

?>

<div id="visibleDivs">

<div id="tab1">

	<div class="leftBox">
	<h3><?php echo gettext("Common Settings"); ?></h3>	  	

<?php //host/hosttemplate form 

	if($this->exactType == 'host' || $this->exactType =='hosttemplate') { 
	  	$tfName = ($this->exactType == 'host') ? gettext('Host Name') : gettext('Template Name') ; 
	  	$name_field = ($this->exactType == 'host') ? 'host_name' : 'template_name';
	  	
		$form='  
			<label for="tfName">'.$tfName.'*</label><br />
	      <input name="tfName" class="tf_wide required" type="text" id="tfName" value="'. @val($FIELDS[$name_field],false) . '" /><br /> 
    
		  <label for="tfFriendly">Description</label><br />
			<input name="tfFriendly" class="tf_wide" type="text" id="tfFriendly" value="'. @val(htmlentities($FIELDS['alias']),false) .'" /><br />'; 
	
		//host specific elements 
		if($this->exactType == 'host') $form .=' 
	      <label for="tfAddress">Address*</label><br />
	      <input name="tfAddress" type="text" class="tf_wide required" id="tfAddress" value="'.  @val(htmlentities($FIELDS['address']),false) .'" /><br />
	   
		  <label for="tfDisplayName">Display name</label><br />
		  <input name="tfDisplayName" type="text" class="tf_wide" id="tfDisplayName" value="'. @val(htmlentities($FIELDS['display_name']),false) .'" /><br />'; 
		 
		$form .='   		  	
		<br />
		<!-- manage parents -->	
		
			<p><a class="wideLinkBox" href="javascript:overlay(\'parentBox\')" title="Manage Parents">'.gettext("Manage Parents").'</a></p>
		  
		  '; 	
		  	  
	}	//end host specific form elements 
	
	//service 
	if($this->exactType == 'service' || $this->exactType == 'servicetemplate' ) {
		$tfName = ($this->exactType == 'service') ? gettext('Config Name') : gettext('Template Name') ;
		$required = ($this->exactType == 'service') ? 'required' : ''; 
		$form  = '
			<label for="tfName">'.$tfName.'*</label><br />
	      <input name="tfName" class="tf_wide required" type="text" id="tfName" value="'. @val($FIELDS['config_name'],false) . @val($FIELDS['template_name'],false) . '" /><br /> 	   	      
    
		  <label for="tfFriendly">'.gettext('Description').'*</label><br />
			<input name="tfServiceDescription" class="tf_wide '.$required.'" type="text" id="tfServiceDescription" value="'. @val($FIELDS['service_description'],false) .'" /><br />
	   
		  <label for="tfDisplayName">'.gettext('Display name').'</label><br />
		  <input name="tfDisplayName" type="text" class="tf_wide" id="tfDisplayName" value="'. @val(htmlentities($FIELDS['display_name']),false) .'" /><br />		  
		  <br />		  
		  <p><a class="wideLinkBox" href="javascript:overlay(\'hostBox\')" title="Assign to Hosts">'.gettext("Manage Hosts").'</a></p>'; 	
		
				 
	} //end service specific form elements 
	print $form; 	  
		  
?>		  

<!-- manage templates --> 			           				      	                                     				
			<p><a class='wideLinkBox' href="javascript:overlay('templateBox')" title="Manage Templates"><?php echo gettext("Manage Templates"); ?></a></p>
<!-- manage hostgroups -->				
			<p><a class='wideLinkBox' href="javascript:overlay('hostgroupBox')" title="Manage Hostgroups"><?php echo gettext("Manage Hostgroups"); ?></a></p>	
<?php  if($this->exactType == 'service' || $this->exactType == 'servicetemplate') {  ?>	
		
<!-- manage servicegroups -->			
			<p><a class='wideLinkBox' href="javascript:overlay('servicegroupBox')" title="Manage Servicegroups"><?php echo gettext("Manage Servicegroups"); ?></a></p>		
		

<?php   }  ?>
							
	</div><!-- end leftbox -->
		
	<div class="rightBox">	
	<!-- check command dropdown -->
	      <label for="selHostCommand"><?php echo gettext('Check command'); ?></label><br />	      
	      <select name="selHostCommand" id="selHostCommand" onchange="reveal_command(this.value);">
	      	<option value="null">&nbsp;</option>
	      <!-- get options from DB -->
	      
<?php 	///////////////////////////Host Commands///////////////////////////////	
				print '<option ';
				if($FIELDS['sel_check_command'] == '0') echo 'selected="selected" ';
				print ' value="0">&nbsp;</option>';	
				
				foreach($FIELDS['selCommandOpts'] as $opt)
	      	{
	      		print "<option ";
	      		//.selected('sel_check_command', $opt['id']).
	      		if($FIELDS['sel_check_command'] == $opt['id']) echo 'selected="selected" ';
	      		print " value='".$opt['id']."'>".$opt['command_name']."</option>\n";		      		
	      	}
?>
	        </select>&nbsp;
		 		<br />
		 
		  <label for="chbActive"><?php echo gettext("Active"); ?></label>
	      <input name="chbActive" type="checkbox" class="checkbox" id="chbActive" value="1" 
	      	<?php if((isset($FIELDS['active']) && $FIELDS['active'] == '1') || !isset($FIELDS['active'])) echo 'checked="checked" '; ?> /><br />
	
	      <label for="fullcommand"><?php echo gettext('Command view'); ?></label><br />
	   	<div id="fullcommand"><?php @val($FIELDS['fullcommand']) ?></div><br /> <!-- output actual command from selectbox above -->
		
	<!-- ARGS -->
	      <label for="tfArg1">$ARG1$</label>
			<input name="tfArg1" class="tf_wide" type="text" id="tfArg1" value="<?php @val(htmlentities($FIELDS['tfArg1'])) ?>" /><br />
	       <label for="tfArg2">$ARG2$</label> 
	       <input name="tfArg2" class="tf_wide" type="text" id="tfArg2" value="<?php @val(htmlentities($FIELDS['tfArg2'])) ?>" /><br />
	       <label for="tfArg3">$ARG3$</label> 
	       <input name="tfArg3" class="tf_wide" type="text" id="tfArg3" value="<?php @val(htmlentities($FIELDS['tfArg3'])) ?>" /><br /> 
	       <label for="tfArg4">$ARG4$</label> 
	       <input name="tfArg4" class="tf_wide" type="text" id="tfArg4" value="<?php @val(htmlentities($FIELDS['tfArg4'])) ?>" /> <br />		  
		   <label for="tfArg5">$ARG5$</label> 
		   <input name="tfArg5" class="tf_wide" type="text" id="tfArg5" value="<?php @val(htmlentities($FIELDS['tfArg5'])) ?>" /><br /> 	   	        
		  <label for="tfArg6">$ARG6$</label> 
		   <input name="tfArg6" class="tf_wide" type="text" id="tfArg6" value="<?php @val(htmlentities($FIELDS['tfArg6'])) ?>" /><br /> 	      	 
		   <label for="tfArg7">$ARG7$</label>  
		   <input name="tfArg7" class="tf_wide" type="text" id="tfArg7" value="<?php @val(htmlentities($FIELDS['tfArg7'])) ?>" /><br /> 	       
		   <label for="tfArg8">$ARG8$</label> 
		   <input name="tfArg8" class="tf_wide" type="text" id="tfArg8" value="<?php @val(htmlentities($FIELDS['tfArg8'])) ?>" /><br /> 
		   
		   <div id="command_test_box"><a class="wideLinkBox" href="javascript:void(0);" id="command_test"><?php echo gettext("Test Check Command"); ?></a></div>
				  	 
	  	 </div><!-- end rightBox -->

	 </div>   <!-- end tab 1 --> 
	      

         
	

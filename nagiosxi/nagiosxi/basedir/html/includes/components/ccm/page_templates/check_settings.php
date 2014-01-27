

<div id="tab2">  
  
  <div class="leftBox">	 
   <h3><?php echo gettext("Check Settings"); ?></h3> 

      <label><?php echo gettext("Initial state"); ?></label> 
<?php	if($this->exactType=='host' || $this->exactType=='hosttemplate') { ?>
             <input name="radIS" type="radio" class="radio" id="radISd" value="d"  <?php @check('initial_state', 'd'); ?> /> 
             <label for="radISd"> d </label>

<?php	} //else service type
		else {	?>
             <input name="radIS" type="radio" class="radio" id="radISw" value="w"  <?php @check('initial_state', 'w'); ?> /> 
             <label for="radISw"> w </label>
             <input name="radIS" type="radio" class="radio" id="radISc" value="c"  <?php @check('initial_state', 'c'); ?> /> 
             <label for="radISc"> c </label>

		
<?php	}	?>		
          	 <input name="radIS" type="radio" class="radio" id="radISo" value="o" <?php @check('initial_state', 'o'); ?> /> 
             <label for="radISo"> o </label> 
             <input name="radIS" type="radio" class="radio" id="radISu" value="u"  <?php @check('initial_state', 'u'); ?> /> 
             <label for="radISu"> u </label>            
		   	<br /><br />


	  <label for="tfCheckInterval"><?php echo gettext("Check interval"); ?> </label> &nbsp;
	  <input name="tfCheckInterval" size="4" type="text" id="tfCheckInterval" value="<?php @val($FIELDS['check_interval']); ?>" /> <?php echo gettext("min"); ?>
      <br />
	 
	  <label for="tfRetryInterval"> <?php echo gettext("Retry interval"); ?> </label>&nbsp;&nbsp;
	  <input name="tfRetryInterval" size="4" type="text" id="tfRetryInterval" value="<?php @val($FIELDS['retry_interval']); ?>" /> <?php echo gettext("min"); ?>
	  <br />
 	 
      <label for="tfMaxCheckAttempts"><?php echo gettext("Max check attempts"); ?> </label> 
      <input name="tfMaxCheckAttempts" size="4" type="text" id="tfMaxCheckAttempts" value="<?php @val($FIELDS['max_check_attempts']); ?>"  />
	  <br />
	  

     <br />
	 
       <label><?php echo gettext("Active checks enabled"); ?></label>                 
          	 <input name="radActiveChecksEnabled" type="radio" class="checkbox" id="radActiveChecksEnabled0" value="1" <?php @check('active_checks_enabled', '1'); ?> /> 
             <label for="radActiveChecksEnabled0"><label for=""><?php echo gettext("on"); ?>&nbsp;</label></label> 
             <input name="radActiveChecksEnabled" type="radio" class="checkbox" id="radActiveChecksEnabled1" value="0" <?php @check('active_checks_enabled', '0'); ?> /> 
             <label for="radActiveChecksEnabled1"><?php echo gettext("off"); ?>&nbsp; </label>
             <input name="radActiveChecksEnabled" type="radio" class="checkbox" id="radActiveChecksEnabled2" value="2" <?php @check('active_checks_enabled', '2'); ?> /> 
             <label for="radActiveChecksEnabled2"><?php echo gettext("skip"); ?>&nbsp;</label> 
            <input name="radActiveChecksEnabled" type="radio" class="checkbox" id="radActiveChecksEnabled3" value="3" <?php @check('active_checks_enabled', '3'); ?> /> 
           <label for="radActiveChecksEnabled3"> <?php echo gettext("null"); ?>&nbsp;</label>  
		         <br />   
		         <div class='padd' ></div> 
		                    
	   <label><?php echo gettext("Passive checks enabled"); ?></label> 	             
          	 <input name="radPassiveChecksEnabled" type="radio" class="checkbox" id="radPassiveChecksEnabled0" value="1" <?php @check('passive_checks_enabled', '1'); ?> /> 
             <label for="radPassiveChecksEnabled"><label for=""><?php echo gettext("on"); ?>&nbsp;</label></label> 
             <input name="radPassiveChecksEnabled" type="radio" class="checkbox" id="radPassiveChecksEnabled1" value="0" <?php @check('passive_checks_enabled', '0'); ?> /> 
             <label for="radPassiveChecksEnabled1"><?php echo gettext("off"); ?>&nbsp;</label> 
             <input name="radPassiveChecksEnabled" type="radio" class="checkbox" id="radPassiveChecksEnabled2" value="2" <?php @check('passive_checks_enabled', '2'); ?> /> 
             <label for="radPassiveChecksEnabled2"><?php echo gettext("skip"); ?>&nbsp;</label> 
            <input name="radPassiveChecksEnabled" type="radio" class="checkbox" id="radPassiveChecksEnabled3" value="3" <?php @check('passive_checks_enabled', '3'); ?> /> 
             <label for="radPassiveChecksEnabled3"><?php echo gettext("null"); ?>&nbsp;</label> 
			<br /><br />

       <label for="selCheckPeriod"><?php echo gettext("Check period"); ?>* </label><br />      
		<select name="selCheckPeriod" id="selCheckPeriod" style="width:205px;">
<?php		//insert timeperiod options
				print '<option ';
				if(isset($FIELDS['check_period']) &&$FIELDS['check_period'] == '0') echo 'selected="selected" ';
				print ' value="0">&nbsp;</option>';	

				foreach($FIELDS['selTimeperiods'] as $opt)
				{ 					
					print "<option ";
					//.selected('check_period', $opt['id']).
					if(isset($FIELDS['check_period']) && $FIELDS['check_period'] == $opt['id']) echo 'selected="selected" ';
					print " value='".$opt['id']."'>".$opt['timeperiod_name']."</option>\n";
        		}	        		
?>
        </select>
		<br />
		<br />
	   
		<label for="tfFreshThreshold"><?php echo gettext("Freshness threshold"); ?> </label>
		<input name="tfFreshThreshold" size="4" type="text" id="tfFreshThreshold" value="<?php @val($FIELDS['freshness_threshold']); ?>" /> sec 
		<br />
		<div class='padd' ></div>
		
		<label><?php echo gettext("Check freshness"); ?></label>&nbsp;         
			 <input name="radFreshness" type="radio" class="checkbox" id="radFreshness1" value="1" <?php @check('check_freshness', '1'); ?> /> 
			 <label for="radFreshness1"><?php echo gettext("on"); ?>&nbsp;</label>  
			 <input name="radFreshness" type="radio" class="checkbox" id="radFreshness0" value="0" <?php @check('check_freshness', '0'); ?> /> 
			 <label for="radFreshness0"><?php echo gettext("off"); ?>&nbsp;</label> 
			 <input name="radFreshness" type="radio" class="checkbox" id="radFreshness2" value="2" <?php @check('check_freshness', '2'); ?> /> 
			 <label for="radFreshness2"><?php echo gettext("skip"); ?>&nbsp; </label> 
			<input name="radFreshness" type="radio" class="checkbox" id="radFreshness3" value="3" <?php @check('check_freshness', '3'); ?> /> 
			<label for="radFreshness3"><?php echo gettext("null"); ?>&nbsp;</label> 
				<br />
				
	   
  </div> <!-- end leftBox -->
	<div class="rightBox">  
       <br />

     <div class='padd' ></div>
<?php   if($this->exactType=='host' || $this->exactType=='hosttemplate') { ?>
		<label for="radObsess1"><?php echo gettext("Obsess over host"); ?> </label>	            
			 <input name="radObsess" type="radio" class="checkbox" id="radObsess1" value="1" <?php @check('obsess_over_host', '1'); ?> /> 
			 <label for="radObsess1"><?php echo gettext("on"); ?>&nbsp;</label>  
			 <input name="radObsess" type="radio" class="checkbox" id="radObsess0" value="0" <?php @check('obsess_over_host', '0'); ?> /> 
			 <label for="radObsess0"><?php echo gettext("off"); ?>&nbsp;</label> 
			 <input name="radObsess" type="radio" class="checkbox" id="radObsess2" value="2" <?php @check('obsess_over_host', '2'); ?> /> 
			 <label for="radObsess2"><?php echo gettext("skip"); ?>&nbsp;</label> 
			<input name="radObsess" type="radio" class="checkbox" id="radObsess3" value="3" <?php @check('obsess_over_host', '3'); ?> /> 
<?php   } //else service type
                else {  ?>
           <label for="radObsess1">Obsess over service </label>
                         <input name="radObsess" type="radio" class="checkbox" id="radObsess1" value="1" <?php @check('obsess_over_service', '1'); ?> />
                         <label for="radObsess1">on&nbsp;</label>
                         <input name="radObsess" type="radio" class="checkbox" id="radObsess0" value="0" <?php @check('obsess_over_service', '0'); ?> />
                         <label for="radObsess0">off&nbsp;</label>
                         <input name="radObsess" type="radio" class="checkbox" id="radObsess2" value="2" <?php @check('obsess_over_service', '2'); ?> />
                         <label for="radObsess2">skip&nbsp;</label>
                        <input name="radObsess" type="radio" class="checkbox" id="radObsess3" value="3" <?php @check('obsess_over_service', '3'); ?> />

<?php   }       ?>
			<label for="radObsess3"><?php echo gettext("null"); ?>&nbsp;</label> 
		   <br />
		    	 
       <label for="selEventHandler"><?php echo gettext("Event handler"); ?></label><br />  
		<select name="selEventHandler" id="selEventHandler">
			
<?php //group options
				print '<option ';
				if(isset($FIELDS['event_handler']) && $FIELDS['event_handler'] == '0') echo 'selected="selected" ';
				print ' value="0">&nbsp;</option>';	
				
				foreach($FIELDS['selEventHandlers'] as $opt){
					print "<option ";					
					if(isset($FIELDS['event_handler']) && $FIELDS['event_handler'] == $opt['id']) echo 'selected="selected" ';
					print " value='".$opt['id']."'>".$opt['command_name']."</option>\n";	
				}
?>
        </select>
       <br /><br />
       
	   <label for="radEventEnable1"><?php echo gettext("Event handler enabled"); ?></label>           
          	 <input name="radEventEnable" type="radio" class="checkbox" id="radEventEnable1" value="1" <?php @check('event_handler_enabled', '1'); ?> /> 
             <label for="radEventEnable1"><label for=""><?php echo gettext("on"); ?>&nbsp;</label></label> 
             <input name="radEventEnable" type="radio" class="checkbox" id="radEventEnable0" value="0" <?php @check('event_handler_enabled', '0'); ?> /> 
             <label for="radEventEnable0"><?php echo gettext("off"); ?>&nbsp;</label> 
             <input name="radEventEnable" type="radio" class="checkbox" id="radEventEnable2" value="2" <?php @check('event_handler_enabled', '2'); ?> /> 
             <label for="radEventEnable2"><?php echo gettext("skip"); ?>&nbsp;</label> 
            <input name="radEventEnable" type="radio" class="checkbox" id="radEventEnable3" value="3" <?php @check('event_handler_enabled', '3'); ?> /> 
            <label for="radEventEnable3"><?php echo gettext("null"); ?>&nbsp;</label>  
		   	<br /><div class='padd' ></div>
 
       <label for="tfLowFlat"><?php echo gettext("Low flap threshold"); ?> </label> &nbsp;&nbsp;
       <input name="tfLowFlat" type="text" size='3' id="tfLowFlat" value="<?php @val($FIELDS['low_flap_threshold']); ?>" /> % 
	   <br />
	   
	   <label for="tfHighFlat"><?php echo gettext("High flap threshold"); ?> </label>&nbsp;
	   <input name="tfHighFlat" type="text" size='3' id="tfHighFlat" value="<?php @val($FIELDS['high_flap_threshold']); ?>"  /> %
		<br /><div class='padd' ></div>
      	 
       <label for="radFlapEnable1"><?php echo gettext("Flap detection enabled"); ?> </label>          
	  	 <input name="radFlapEnable" type="radio" class="checkbox" id="radFlapEnable1" value="1" <?php @check('flap_detection_enabled', '1'); ?> /> 
	     <label for="radFlapEnable1"><?php echo gettext("on"); ?>&nbsp;</label>  	
	     <input name="radFlapEnable" type="radio" class="checkbox" id="radFlapEnable0" value="0" <?php @check('flap_detection_enabled', '0'); ?> /> 
	     <label for="radFlapEnable0"><?php echo gettext("off"); ?>&nbsp; </label>
	     <input name="radFlapEnable" type="radio" class="checkbox" id="radFlapEnable2" value="2" <?php @check('flap_detection_enabled', '2'); ?> /> 
	     <label for="radFlapEnable2"><?php echo gettext("skip"); ?>&nbsp; </label>	     
	    <input name="radFlapEnable" type="radio" class="checkbox" id="radFlapEnable3" value="3" <?php @check('flap_detection_enabled', '3'); ?> /> 
	    <label for="radFlapEnable3"><?php echo gettext("null"); ?>&nbsp;</label> 
	    	<br />
			<div class='padd' ></div>
			
		             
		<label for="chbFLo"><?php echo gettext("Flap detection options"); ?></label> 
<?php	if($this->exactType=='host' || $this->exactType=='hosttemplate') { ?>
             <input name="chbFLd" type="checkbox" class="checkbox" id="chbFLd" value="d" <?php @check('flap_detection_options', 'd'); ?> /> 
             <label for="chbFLd">d</label> 
<?php	} 
		else { ?>
          	 <input name="chbFLc" type="checkbox" class="checkbox" id="chbFLc" value="c" <?php @check('flap_detection_options', 'c'); ?> /> 
             <label for="chbFLc"> c </label> 
          	 <input name="chbFLw" type="checkbox" class="checkbox" id="chbFLw" value="w" <?php @check('flap_detection_options', 'w'); ?> /> 
             <label for="chbFLw"> w </label> 			 
		
<?php	} ?>		
          	 <input name="chbFLo" type="checkbox" class="checkbox" id="chbFLo" value="o" <?php @check('flap_detection_options', 'o'); ?> /> 
             <label for="chbFLo">o</label> 
             <input name="chbFLu" type="checkbox" class="checkbox" id="chbFLu" value="u" <?php @check('flap_detection_options', 'u'); ?> />
             <label for="chbFLu">u</label>  
		<br /><div class='padd' ></div>

       <label for="radStatusInfos1"><?php echo gettext("Retain status information"); ?></label><br />           
          	 <input name="radStatusInfos" type="radio" class="checkbox" id="radStatusInfos1" value="1" <?php @check('retain_status_information', '1'); ?> /> 
             <label for="radStatusInfos1">on&nbsp;</label> 
             <input name="radStatusInfos" type="radio" class="checkbox" id="radStatusInfos0" value="0" <?php @check('retain_status_information', '0'); ?> /> 
             <label for="radStatusInfos0">off&nbsp;</label> 
             <input name="radStatusInfos" type="radio" class="checkbox" id="radStatusInfos2" value="2" <?php @check('retain_status_information', '2'); ?> /> 
             <label for="radStatusInfos2">skip&nbsp;</label> 
            <input name="radStatusInfos" type="radio" class="checkbox" id="radStatusInfos3" value="3" <?php @check('retain_status_information', '3'); ?> /> 
            <label for="radStatusInfos3">null&nbsp;</label> 
		   	<br /><div class='padd' ></div>
                 
	   <label for="radNoStatusInfos1"><?php echo gettext("Retain non-status information"); ?></label><br /> 
          	 <input name="radNoStatusInfos" type="radio" class="checkbox" id="radNoStatusInfos1" value="1" <?php @check('retain_nonstatus_information', '1'); ?> /> 
             <label><?php echo gettext("on"); ?>&nbsp;</label> 
             <input name="radNoStatusInfos" type="radio" class="checkbox" id="radNoStatusInfos0" value="0" <?php @check('retain_nonstatus_information', '0'); ?> /> 
            <label><?php echo gettext("off"); ?>&nbsp;</label>
             <input name="radNoStatusInfos" type="radio" class="checkbox" id="radNoStatusInfos2" value="2" <?php @check('retain_nonstatus_information', '2'); ?> /> 
            <label><?php echo gettext("skip"); ?>&nbsp;</label>
            <input name="radNoStatusInfos" type="radio" class="checkbox" id="radNoStatusInfos3" value="3" <?php @check('retain_nonstatus_information', '3'); ?> /> 
            <label><?php echo gettext("null"); ?>&nbsp;</label>
		   	<br /><div class='padd' ></div>
	 
       <label><?php echo gettext("Process perf data"); ?> </label><br />        
          	 <input name="radPerfData" type="radio" class="checkbox" id="radPerfData1" value="1" <?php @check('process_perf_data', '1'); ?> /> 
             <label for="radPerfData1"><?php echo gettext("on"); ?>&nbsp;</label> 
             <input name="radPerfData" type="radio" class="checkbox" id="radPerfData0" value="0" <?php @check('process_perf_data', '0'); ?> /> 
            <label for="radPerfData0"><?php echo gettext("off"); ?>&nbsp;</label>
             <input name="radPerfData" type="radio" class="checkbox" id="radPerfData2" value="2" <?php @check('process_perf_data', '2'); ?> /> 
            <label for="radPerfData2"><?php echo gettext("skip"); ?>&nbsp;</label>
            <input name="radPerfData" type="radio" class="checkbox" id="radPerfData3" value="3" <?php @check('process_perf_data', '3'); ?> /> 
            <label for="radPerfData3"><?php echo gettext("null"); ?>&nbsp;</label>
		   	<br />
<?php
	if($this->exactType=='service' || $this->exactType=='servicetemplate') {
?>		
		<label><?php echo gettext("Is Volatile"); ?> </label><br /> 
		<input type="radio" value="1" id="radIsVolatile1" class="checkbox" name="radIsVolatile" <?php @check('is_volatile', '1'); ?> />
		<label for="radIsVolatile1"><?php echo gettext("on"); ?>&nbsp;</label> 	
		<input type="radio" value="0" id="radIsVolatile0" class="checkbox" name="radIsVolatile" <?php @check('is_volatile', '0'); ?> />
		<label for="radIsVolatile0"><?php echo gettext("off"); ?>&nbsp;</label> 	
		<input type="radio" value="2" id="radIsVolatile2" class="checkbox" name="radIsVolatile" <?php @check('is_volatile', '2'); ?> />
		<label for="radIsVolatile2"><?php echo gettext("skip"); ?>&nbsp;</label> 	
		<input type="radio" value="3" id="radIsVolatile3" class="checkbox" name="radIsVolatile" <?php @check('is_volatile', '3'); ?> />
		<label for="radIsVolatile3"><?php echo gettext("null"); ?>&nbsp;</label> 	
<?php
	} //end service / servicetemplate IF
?>		
		
	  </div> <!-- end rightbox -->
     
	</div><!-- end tab2 -->
	
	
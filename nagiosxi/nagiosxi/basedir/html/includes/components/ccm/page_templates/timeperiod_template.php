<?php   //timeperiod_template.php



?>
<div id="tab1">
	<div class='leftBox' style="width:300px;">
		<label for='tfName'>*<?php echo gettext("Timeperiod Name"); ?></label><br /> 
			<input type="input" class="required" value="<?php @val($FIELDS['timeperiod_name']); ?>" id='tfName' name='tfName' /><br />
		<label for='tfFriendly'>*<?php echo gettext("Description"); ?></label><br />
			<input type="text" class="required" value="<?php @val($FIELDS['alias']); ?>" id="tfFriendly" name="tfFriendly" /><br />
		<label for='tfTplName'><?php echo gettext("Template Name"); ?></label><br />
			<input type="text"  value="<?php @val($FIELDS['name']); ?>" id="tfTplName" name="tfTplName" /><br /><br />
			
		<label for='chbActive'><?php echo gettext("Active"); ?></label>	
			<input type="checkbox" 
				<?php if((isset($FIELDS['active']) && $FIELDS['active'] == '1') || !isset($FIELDS['active'])) echo 'checked="checked" '; ?>
				value="1" id="chbActive" class="checkbox" name="chbActive" /><br /><br />
			
		
	</div> <!-- end leftBox -->
	<!-- create overlay box for "Exclude" -->
	
	
	<div class='rightBox' style="width:300;">   	
       	<label for="txtTimedefinition"><?php echo gettext("Time Definition"); ?></label> <br />
         <input type="text" name="txtTimedefinition" id="txtTimedefinition" /> <br />
         <p class="label" style="margin-bottom:5px;"><?php echo gettext("Examples"); ?>: "monday", "december 25", "use"</p>
        
          <label for="txtTimerange"><?php echo gettext("Time Range"); ?></label> <br />
          <input type="text" name="txtTimerange" id="txtTimerange" /> <br />
          <p class='ccm-label' style="margin-bottom:5px;"><?php echo gettext("Examples"); ?>: "00:00-24:00", "09:00-17:00", "us-holidays"</p> 
    
          <p><a class='linkBox' href="javascript:void(0);" onclick="insertTimeperiod(false,false)"; title="Insert"><?php echo gettext("Insert Definition"); ?></a></p>
        	<!-- write special function for variable defs --> 
	  </div><!-- end right box --> 	
	      
	      
	      
	
	<div id="timeperiodBox"> 	
	      
			  <table class='outputTable' id="tblTimeperiods">
	         <tr><th><?php echo gettext("Time Definition"); ?></th>
			 <th><?php echo gettext("Time Range"); ?></th>
			 <th><?php echo gettext("Delete"); ?></th></tr>	
	        <!-- insert selected items here -->
	        </table>
		
			
	<!--		<p><a href="javascript:killOverlay('variableBox')" title="Close">Close</a></p> 	-->	
	           			
	</div> <!-- end timeperiodBox -->	

		<!-- manage excludes --> 			           				      	                                     				
			<p><a class="linkBox" href="javascript:overlay('excludeBox')" title="Manage Timeperiod Exclusions"><?php echo gettext("Manage Timeperiod Exclusions"); ?></a></p>	

</div> <!-- end tab1 div -->




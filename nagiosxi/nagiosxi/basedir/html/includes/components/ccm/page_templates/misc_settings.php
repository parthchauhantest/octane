 <div id="tab4">
     	 
      <h3><?php echo gettext("Misc Settings"); ?></h3> 

	<div class="leftBox">

<?php if($this->exactType!='contact' && $this->exactType!='contacttemplate') {  ?>  
			
		<label for="tfNotes"><?php echo gettext("Notes"); ?></label><br />
      <input name="tfNotes" class="tf_wide" type="text" id="tfNotes" value="<?php @val($FIELDS['notes']); ?>" /> 
      <br />
      
      <label for="tfVmrlImage"><?php echo gettext("VRML image"); ?></label><br /> 
       <input name="tfVmrlImage" class="tf_wide" type="text" id="tfVmrlImage" value="<?php @val($FIELDS['vrml_image']); ?>" />
	   <br />
          
       <label for="tfNotesURL"><?php echo gettext("Notes URL"); ?></label> <br />
       <input name="tfNotesURL" class="tf_wide" type="text" id="tfNotesURL" value="<?php @val(htmlentities($FIELDS['notes_url'])); ?>" />
       <br />
       
       <label for="tfStatusImage"><?php echo gettext("Status image"); ?></label> <br />
       <input name="tfStatusImage" class="tf_wide" type="text" id="tfStatusImage" value="<?php @val($FIELDS['statusmap_image']); ?>" />
     	<br />  

       <label for="tfActionURL"><?php echo gettext("Action URL"); ?></label> <br />
       <input name="tfActionURL" class="tf_wide" type="text" id="tfActionURL" value="<?php @val(htmlentities($FIELDS['action_url'])); ?>" /> 
       <br /> 
	   
       <label for="tfIconImage"><?php echo gettext("Icon image"); ?></label> <br />
       <input name="tfIconImage" class="tf_wide" type="text" id="tfIconImage" value="<?php @val($FIELDS['icon_image']); ?>"  />
		<br />
		
       <label for="tfIconImageAlt"><?php echo gettext("Icon image ALT text"); ?></label><br /> 
       <input name="tfIconImageAlt" class="tf_wide" type="text" id="tfIconImageAlt" value="<?php @val($FIELDS['icon_image_alt']); ?>" />
		<br />		
      
	</div><!-- end leftBox -->
	<div class="rightBox">       

       <label for="tf2DCoords"><?php echo gettext("2D coords"); ?> </label><br />
       <input name="tfD2Coords" class="tf_wide" type="text" id="tfD2Coords" value="<?php @val($FIELDS['2d_coords']); ?>"  />
        (x,y) 
     	<br />
     
       <label for="tfD3Coords"><?php echo gettext("3D coords"); ?></label> <br />
       <input name="tfD3Coords" class="tf_wide" type="text" id="tfD3Coords" value="<?php @val($FIELDS['3d_coords']); ?>" />
        (x,y,z)
     	<br /><br />

<?php
} //end contact IF 

?>

      <p><strong><?php echo gettext("Free variable definitions"); ?></strong></p> 
      <p><a class='wideLinkBox wideLinkBox' href="javascript:overlay('variableBox')" title="Free Variable Definitions"><?php echo gettext("Manage Variable Definitions"); ?></a></p>
      <br />	

<?php
if($this->exactType=='host' || $this->exactType=='service' || $this->exactType=='contact') {
?>       	 
		<p><strong><?php echo gettext("Use this configuration as a template"); ?></strong></p> 
         
      <label for="tfGenericName"><?php echo gettext("Generic name"); ?></label><br /> 
       <input type="text" class="tf_wide" name="tfGenericName" id="tfGenericName" value="<?php @val($FIELDS['name']); ?>" />
		<br /><br />

<?php 
} //end "use this as template" IF 

?>   
	</div> <!-- end rightBox -->      

</div><!-- end tab 4 -->

</div> <!-- end visibleDivs -->
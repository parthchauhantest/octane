<?php //form_footer.php //closing javascript and tags for the CCM form 

?>

	  <div class="bottomButtons">
	  	 
	<!-- save button -->
			<input class="ccmbutton" name="subForm" type="button" id="subForm1" value="<?php echo gettext("Save"); ?>" />
	      <input class="ccmbutton" name="subAbort" type="button" id="subAbort1" onclick="abort('<?php print $FIELDS['exactType']; ?>')" value="<?php echo gettext("Abort"); ?>" /> 
		  <div class='padd'></div>		  
		  *&nbsp;<?php echo gettext("required"); ?> <br />	  	     

	      <!-- output DB messages?? -->
	       <span class="dbmessage"></span> <br />
	     	  <input name="cmd" type="hidden" id="cmd" value="submit" /> 
	        <input name="mode" type="hidden" id="mode" value="<?php print $FIELDS['mode']; ?>" />
	        <input name="hidId" type="hidden" id="hidId" value="<?php print $FIELDS['hidId']; ?>" />
	        <input name="hidName" type="hidden" id="hidName" value="<?php print $FIELDS['hidName']; ?>" />
	        <input name="exactType" type="hidden" id="exactType" value="<?php print $FIELDS['exactType']; ?>" />
	        <input name="type" type="hidden" id="type" value="<?php print $FIELDS['exactType']; ?>" />
	        <input name="genericType" type="hidden" id="genericType" value="<?php print $FIELDS['genericType']; ?>" /> 
	        <input name="returnUrl" type="hidden" id="returnUrl" value="<?php print $FIELDS['returnUrl'] ?>" />
	        <input name="token" id="token" type="hidden" value="<?php print $_SESSION['token']; ?>" />
	        
	  </div>   <!-- end bottomButtons div -->	

</form> <!-- close mainCcmForm -->
</div><!-- end formContainer -->



</div> <!-- end mainWrapper div -->


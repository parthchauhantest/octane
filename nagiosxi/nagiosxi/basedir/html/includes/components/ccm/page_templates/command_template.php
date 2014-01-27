<?php   //command_template.php

//$plugins = scandir($CFG['plugins_directory']); 
//ccm_array_dump($plugins); 

?>

<div id="tab1">

<h3><?php echo gettext("Command Definition"); ?></h3>

<label for="tfName"><?php echo gettext("Command Name"); ?>*  &nbsp;  &nbsp; <?php echo gettext("Example"); ?>: check_example</label><br />
<input type="text" class="required"  value="<?php @val($FIELDS['command_name']); ?>" id="tfName" name="tfName" /><br />

<label for="tfCommand"><?php echo gettext("Command Line"); ?>*  &nbsp;  &nbsp;<?php echo gettext("Example"); ?>: $USER1$/check_example -H $HOSTADDRESS$ </label><br />
<input type="text" class="required" value="<?php @val(htmlentities($FIELDS['command_line'])); ?>" id="tfCommand" name="tfCommand" /><br />
<br />
<label for="selCommandType"><?php echo gettext("Command Type"); ?>: </label><br />
<select id="selCommandType" name="selCommandType">
   <option <?php ccm_is_selected('command_type',1); ?> value="1"><?php echo gettext("check command"); ?></option>
   <option <?php ccm_is_selected('command_type',2); ?> value="2"><?php echo gettext("misc command"); ?></option>
   <option <?php ccm_is_selected('command_type',0); ?> value="0"><?php echo gettext("unclassified"); ?></option>   
</select><br />
<br />
<label for='chbActive'><?php echo gettext("Active"); ?></label>	
<input type="checkbox" <?php if((isset($FIELDS['active']) && $FIELDS['active'] == '1') || !isset($FIELDS['active'])) echo 'checked="checked" '; ?> value="1" id="chbActive" class="checkbox" name="chbActive" /><br /><br />

<fieldset id="pluginDoc">
<legend><?php echo gettext("See Plugin Documentation"); ?></legend>
	<!-- check command dropdown -->
	<div id='pluginHelpWrapper'>
	      <label for="selPlugins"><?php echo gettext("Available Plugins"); ?></label><br />	      
	      <select name="selPlugins" id="selPlugins" onchange="get_plugin_help('<?php echo $_SESSION['token']; ?>')">
	      	<option value="null">&nbsp;</option>
	      <!-- get options from DB -->
	      
<?php 	
		$plugins = scandir($CFG['plugins_directory']); 
		foreach($plugins as $p){
			if($p =='.' || $p=='..' || strpos($p,'check_')===false) continue; 
			print "<option value='$p'>$p</option>"; 
		}

?>
	        </select>&nbsp;
	        
			<br />	
			
			<!-- plugin documentation output box  -->   	
	   	<div id="pluginhelp">
			   	
	   	</div> <!-- output actual command from selectbox above -->
		</div> <!-- end pluginHelpWrapper -->
</fieldset>

</div><!-- end tab1 --> 
<div id='documentation' class='overlay'></div>
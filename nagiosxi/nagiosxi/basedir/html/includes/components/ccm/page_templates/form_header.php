


<script type="text/javascript" src="javascript/form_js.js?<?php echo VERSION; ?>"></script> <!-- jquery functions for form manipulation -->
<script type="text/javascript">


var command_list = new Array();
<?php 
if(in_array($this->exactType, $this->mainTypes) )
{
	foreach($FIELDS['selCommandOpts'] as $c)
		print "command_list['".$c['id']."'] = '".addslashes(htmlentities($c['command_line'],ENT_NOQUOTES))."';\n";		
}
?>

//default is to load "Common Settings" 
//load preselected items
$(document).ready(function() {
  
<?php
	if(!empty($FIELDS['freeVariables'])) {
		foreach($FIELDS['freeVariables'] as $f)
			if(!empty($f['name']) && !empty($f['value']))
				print "insertDefinition('".$f['name']."','".addslashes($f['value'])."');";	  	
	} 
	  //preload timeperiods  	  
	  if($this->exactType=='timeperiod')
	  {
	  		for($i=0;$i<count($FIELDS['timedefinitions']);$i++)
	  			print "insertTimeperiod('".$FIELDS['timedefinitions'][$i]."','".$FIELDS['timeranges'][$i]."');"; 	  		
	  }	  	  
?>
}); 

</script>


<div id="mainWrapper">
<div id='returnUrlDiv'><a href="<?php echo $FIELDS['returnUrl']; ?>"> &laquo; <?php echo gettext("Go Back"); ?></a></div>


<?php //show help items if they exist 
if(!empty($FIELDS['info'])) { ?>
<div id='helpOptions'>
	<label for='helpList'><?php echo gettext("Documentation"); ?></label><br />
	<select id='helpList' onchange='getHelpOverlay("<?php echo $FIELDS["infotype"]; ?>")'>
		<option value=''></option>
<?php 
	foreach($FIELDS['info'] as $info)	
		print "<option value='".$info['key2']."'>".$info['key2']."</option>\n"; 

	print "</select>
	</div> <!-- end helpOptions -->\n"; 
}
?>	


  <h1 class='title'><?php echo get_page_title($this->exactType); ?> <?php echo gettext("Management"); ?></h1>

<?php if(in_array($this->exactType,$this->mainTypes) ) { ?>  

    <div class="navDiv">  
	    <ul class="navList">
	        <li id="commonSettings" class="navListItem"><a class="navLink" href="javascript:showHideTab('1');"><?php echo gettext("Common Settings"); ?></a></li>
	        <li class="navListItem"><a class="navLink" href="javascript:showHideTab('2');"><?php echo gettext("Check Settings"); ?></a></li>
	        <li class="navListItem"><a class="navLink" href="javascript:showHideTab('3');"><?php echo gettext("Alert Setting"); ?>s</a></li>
	        <li class="navListItem"><a class="navLink" href="javascript:showHideTab('4');"><?php echo gettext("Misc Settings"); ?></a></li> 
	    </ul> 
	</div>  <!-- end navDiv --> 

<?php } 
if($this->exactType=='contact' || $this->exactType=='contacttemplate') { ?>

    <div class="navDiv">  
	    <ul class="navList">
	        <li id="commonSettings" class="navListItem"><a class="navLink" href="javascript:showHideTab('1')"><?php echo gettext("Common Settings"); ?></a></li>
	        <li class="navListItem"><a class="navLink" href="javascript:showHideTab('2')"><?php echo gettext("Alert Settings"); ?></a></li>
	        <li class="navListItem"><a class="navLink" href="javascript:showHideTab('4')"><?php echo gettext("Misc Settings"); ?></a></li> 
	    </ul> 
	</div>  <!-- end navDiv --> 

<?php 
} //end contact template IF 
?>

	<div id="formContainer"> 
	<form id="mainCcmForm" method="post" action="index.php?type=<?php echo $this->exactType; ?>"> <!-- begin multipage form -->
	
	

	
	
     

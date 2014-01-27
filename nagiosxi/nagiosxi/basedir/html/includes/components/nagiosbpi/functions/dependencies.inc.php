<?php  //dependencies.inc.php

///TODO: this is a tentative feature for the future 

function bpi_view_dependencies()
{
	$map = get_host_parent_child_array_map();
	return "<pre>".print_r($map,true)."</pre>"; 
	
	/*
	//NEED A RECURSIVE FUNCTION 	
	
	//foreach hostchild
		check for children 
		if children, create a new group DEP:<groupname>
			//add child members 
			//check child array ->repeat process 
		
		
		
	*/ 	
}



?>
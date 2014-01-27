<?php  //login.inc.php


/** function build_login_form()
*	returns html for the login page 
*	@return string $html htm output 
*
*/ 
function build_login_form()
{
	$html="
	
	<div id='loginDiv'>
		<h3>Nagios CCM Login</h3>
		<form id='loginForm' action='index.php' method='post'>
			<label for='username'>".gettext("Username").": </label><br />
			<input type='text' name='username' id='username' size='20' /><br /><br />
			<label for='password'>".gettext("Password")."</label><br />
			<input type='password' name='password' id='password' size='20' /><br /><br />
			<input type='hidden' name='loginSubmitted' value='true' />
			<input type='hidden' name='menu' value='invisible' />
			<input class='ccmbutton' type='submit' name='submit' id='submit' value='Login' />
			
		
		</form>	
	
	</div>";

	return $html; 

}



?>
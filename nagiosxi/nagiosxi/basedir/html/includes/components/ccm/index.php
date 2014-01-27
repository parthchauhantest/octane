<?php //main index and page routing for Nagios CCM 

ob_start();
session_start(); 

//set the location of the CCM root directory
define('BASEDIR',dirname(__FILE__).'/');    
require_once('includes/session.inc.php'); 
//ini_set('display_errors','On'); 
?>
<?php
if(ENVIRONMENT=='nagiosxi')
    do_page_start(array("page_title"=>'Nagios Core Configuration Manager'),true);

else {
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Nagios Core Configuration Manager</title>

<script type="text/javascript">
var NAGIOSXI=<?php if(ENVIRONMENT=='nagiosxi'){ echo "true;\n";} else {echo "false;\n";}  ?>
</script>

<link rel='stylesheet' type='text/css' href='css/style.css?<?php echo VERSION; ?>' />
<script type="text/javascript" src="javascript/jquery-1.7.2.min.js?<?php echo VERSION; ?>"></script>

<script type="text/javascript" src="javascript/main_js.js?<?php echo VERSION; ?>"></script>

<style type="text/css">

<?php //adjust width based on menu visibility  
if($_SESSION['menu']=='visible') 
	echo '#contentWrapper { margin: 0px auto; width: 80%; float:left;}';
else 
	echo '#contentWrapper { margin: 0px auto; width: 95%; }'; 
?>



</style>
</head>
<body >
<?php }	?>
<div id='loginMsgDiv'>
    <span <?php if(!($_SESSION['loginStatus'] === false)) echo "class='deselect'"; ?>>
        <div <?php if($_SESSION['loginStatus'] === false) echo "class='error'"; ?>>
            <?php echo $_SESSION['loginMessage']; ?>
        </div>
    </span>
</div>

<?php 
//send to a case switcher, display page based on args
print page_router(); //cleans request variables and routes the page 


//print_r($_SESSION); 

if(ENVIRONMENT=='nagiosxi')
    do_page_end(true);

else {
?>


</body>
</html>
<?php } ?>
<?php ob_end_flush(); ?>

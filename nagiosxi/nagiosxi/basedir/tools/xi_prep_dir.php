#!/usr/bin/php 

<?php


if(!isset($argv[1]))
{
	echo "No directory location specified!\n";
	echo "Usage ./importAll.php /path/to/configs \n"; 
	exit(1);
}	
//absolute directory location here
//$dir = '/usr/local/nagios/etc/PREP/services/datacenter'; 
$dir = $argv[1]; 

$list = scandir($dir); 

foreach($list as $file)
{
	//ignore necessary files 
	if($file=='.' || $file=='..' || $file[0]=='.') continue; 
	//not a config file 
	if(!strpos($file,'.cfg')) continue; 
	$path = $dir.'/'.$file; 
	echo "Processing $path ...\n"; 
	system("/usr/bin/php /usr/local/nagiosxi/tools/xiprepimport.php '$path'"); 
}
echo "PREPPING COMPLETE!\n"; 

?>

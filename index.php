<?php

// Report all errors except E_NOTICE
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

require "./config.php";
require "./encrypt.php";

for ($i=1;$i<=count($maint);$i++)
{
	if ($datehour == $maint[$i])
	{
		$mainttime = '1';
	}
}

if ($debug == 0 || ($mainttime == '1' && ($datemin <= $maintmax && $datemin >= $maintmin)))
{
	include ("./off.php");
}
else
{
	include ("./on.php");
}


require "./footer.php";
?>
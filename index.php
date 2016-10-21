<?php

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
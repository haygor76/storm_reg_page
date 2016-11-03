<?php
require "./header.php";
require "./lang.php";

$lang = $_GET['lang'];

//echo strlen($lang);
//echo "<br />";
//echo strlen($_POST['lang']);

if (strlen($lang) == 0 && strlen($_POST['lang']) != 0)
{
	$lang = $_POST['lang'];
}


	// Display Form
	echo"		<div style=\"text-align: center\">\n";
	echo"			<table border=\"3\" width=\"100%\" class=\"LeftContent\">\n";
	echo"				<tr>\n";
	echo"					<td style=\"text-align: center\">\n";
	echo"						<img class=\"HeaderImage\" src=\"./reg_page_image.jpg\" alt=\"Registration Banner\" />\n";
	echo"					</td>\n";
	echo"				</tr>\n";
	echo"				<tr>\n";
	echo"					<td style=\"text-align: center\">\n";
	echo"						".$page_title." Account Management System v".$version." ".$designer."\n";
	echo"					</td>\n";
	echo"				</tr>\n";
	echo"				<tr>\n";
	echo"					<td style=\"text-align: center\">\n";
	echo"						&nbsp;\n";
	echo"					</td>\n";
	echo"				</tr>\n";
	echo"				<tr>\n";
	echo"					<td>\n";
	echo"						<form action=\"./index.php\" method=\"post\" style=\"text-align: center\">\n";
	echo"							Select language:<select name=\"lang\">\n";
	foreach ($ilang as $key => $value)
	{
		if ($value == $lang)
		{
			$highlight = " selected=\"selected\"";
		}
		else
		{
			$highlight = "";
		}
		echo"								<option value=\"".$value."\"".$highlight.">".$value."</option>\n";
	}
	echo"							</select>\n";
	echo"							<input type=\"submit\" name=\"sumbit\" value=\"Display\">\n";
	echo"						</form>\n";
	echo"					</td>\n";
	echo"				</tr>\n";
	if (strlen($lang) != 0)
	{
		include "./links.php";
	}
	echo"			</table>\n";
	echo"		</div>\n";

?>
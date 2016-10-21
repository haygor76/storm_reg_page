<?php
require "./header.php";
require "./lang.php";


$lang = $_GET['lang'];
if ($lang == "") 
{
	$lang = "English";
}
if ($mainttime == '1')
{
	// Display Form
	echo"		<div style=\"text-align: center\">\n";
	echo"			<table border =\"3\" width=\"100%\" class=\"LeftContent\">\n";
	echo"				<tr>\n";
	echo"					<td>\n";
	echo"						<table width=\"50%\" align=\"center\">\n";
	echo"							<tr>\n";
	echo"								<td colspan=\"2\" style=\"text-align: center\">\n";
	echo"									<img class=\"HeaderImage\" src=\"./reg_page_image.jpg\" alt=\"Registration Banner\" height=\"125px\" width=\"500px\"/>\n";
	echo"								</td>\n";
	echo"							</tr>\n";
	echo"							<tr>\n";
	echo"								<td colspan=\"2\" style=\"text-align: center\">\n";
	echo"									".$language[$lang][35]."\n";
	echo"								</td>\n";
	echo"							</tr>\n";
	echo"							<tr>\n";
	echo"								<td colspan=\"2\" style=\"text-align: center\">\n";
	echo"									".$language[$lang][54]."\n";
	echo"								</td>\n";
	echo"							</tr>\n";
	echo"						</table>\n";
	echo"					</td>\n";
	echo"				</tr>\n";
	echo"			</table>\n";
	echo"		</div>\n";
}
else if ($debug == 1)
{
	// Display Form
	echo"		<div style=\"text-align: center\">\n";
	echo"			<table border =\"3\" width=\"100%\" class=\"LeftContent\">\n";
	echo"				<tr>\n";
	echo"					<td>\n";
	echo"						<table width=\"50%\" align=\"center\">\n";
	echo"							<tr>\n";
	echo"								<td colspan=\"2\" style=\"text-align: center\">\n";
	echo"									<img class=\"HeaderImage\" src=\"./reg_page_image.jpg\" alt=\"Registration Banner\" height=\"125px\" width=\"500px\"/>\n";
	echo"								</td>\n";
	echo"							</tr>\n";
	echo"							<tr>\n";
	echo"								<td colspan=\"2\" style=\"text-align: center\">\n";
	echo"									".$language[$lang][35]."\n";
	echo"								</td>\n";
	echo"							</tr>\n";
	echo"							<tr>\n";
	echo"								<td colspan=\"2\" style=\"text-align: center\">\n";
	echo"									".$language[$lang][55]."\n";
	echo"								</td>\n";
	echo"							</tr>\n";
	echo"						</table>\n";
	echo"					</td>\n";
	echo"				</tr>\n";
	echo"			</table>\n";
	echo"		</div>\n";

}
else
{
	// Display Form
	echo"		<div style=\"text-align: center\">\n";
	echo"			<table border =\"3\" width=\"100%\" class=\"LeftContent\">\n";
	echo"				<tr>\n";
	echo"					<td>\n";
	echo"						<table width=\"50%\" align=\"center\">\n";
	echo"							<tr>\n";
	echo"								<td colspan=\"2\" style=\"text-align: center\">\n";
	echo"									<img class=\"HeaderImage\" src=\"./reg_page_image.jpg\" alt=\"Registration Banner\" height=\"125px\" width=\"500px\"/>\n";
	echo"								</td>\n";
	echo"							</tr>\n";
	echo"							<tr>\n";
	echo"								<td colspan=\"2\" style=\"text-align: center\">\n";
	echo"									".$language[$lang][35]."\n";
	echo"								</td>\n";
	echo"							</tr>\n";
	echo"							<tr>\n";
	echo"								<td colspan=\"2\" style=\"text-align: center\">\n";
	echo"									".$language[$lang][36]."\n";
	echo"								</td>\n";
	echo"							</tr>\n";
	echo"						</table>\n";
	echo"					</td>\n";
	echo"				</tr>\n";
	echo"			</table>\n";
	echo"		</div>\n";

}
?>
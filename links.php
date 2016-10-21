<?php
	echo"								<tr>\n";
	echo"									<td colspan=\"2\" style=\"text-align: center\">\n";
	echo"										<table border =\"3\" width=\"100%\" class=\"LeftContent\">\n";
	echo"											<tr>\n";
	echo"												<td style=\"text-align: center\">\n";
	echo"													".$language[$lang][28]."\n";
	echo"												</td>\n";
	echo"											</tr>\n";
	echo"											<tr>\n";
	echo"												<td style=\"text-align: center\">\n";
	echo"													<a href=\"./register.php?lang=".$lang."\">".$language[$lang][29]."</a>\n";
	echo"												</td>\n";
	echo"											</tr>\n";
	echo"											<tr>\n";
	echo"												<td colspan=\"2\" style=\"text-align: center\">\n";
	echo"													<a href=\"./changepass.php?lang=".$lang."\">".$language[$lang][30]."</a>\n";
	echo"												</td>\n";
	echo"											</tr>\n";
	echo"											<tr>\n";
	echo"												<td colspan=\"2\" style=\"text-align: center\">\n";
	echo"													<a href=\"./update.php?lang=".$lang."\">".$language[$lang][31]."</a>\n";
	echo"												</td>\n";
	echo"											</tr>\n";
	if ($mail == 1)
	{
		echo"											<tr>\n";
		echo"												<td colspan=\"2\" style=\"text-align: center\">\n";
		echo"													<a href=\"./activate.php?lang=".$lang."\">".$language[$lang][32]."</a>\n";
		echo"												</td>\n";
		echo"											</tr>\n";
		echo"											<tr>\n";
		echo"												<td colspan=\"2\" style=\"text-align: center\">\n";
		echo"													<a href=\"./requestactivation.php?lang=".$lang."\">".$language[$lang][33]."</a>\n";
		echo"												</td>\n";
		echo"											</tr>\n";
		echo"											<tr>\n";
		echo"												<td colspan=\"2\" style=\"text-align: center\">\n";
		echo"													<a href=\"./recover.php?lang=".$lang."\">".$language[$lang][34]."</a>\n";
		echo"												</td>\n";
		echo"											</tr>\n";
	}
	echo"										</table>\n";
	echo"									</td>\n";
	echo"								</tr>\n";
?>
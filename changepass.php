<?php
session_start();

require "./config.php";
require "./encrypt.php";

$lang = $_GET['lang'];
if ($lang == "") 
{
	$lang = $dlang;
}

for ($i=1;$i<=count($maint);$i++)
{
	if ($datehour == $maint[$i])
	{
		$mainttime = '1';
	}
}

if ($debug == 0 || ($mainttime == '1' && ($datemin <= $maintmax && $datemin >= $maintmin)))
{
	include './off.php';
}
else
{
	require "./header.php";

	if($_POST['change'] && $designer)
	{
		$remoteip = checkinject($_SERVER['REMOTE_ADDR']);
		$accountname = checkinject($_POST['accountname']);
		$dobm = checkinject($_POST['month']);
		$dobd = checkinject($_POST['day']);
		$doby = checkinject($_POST['year']);
		$patternDOB = "[[:digit:]]{1,2}";
		$patternDOB2 = "[[:alpha:]]{1,}";
		if ($imagever == 1)
		{
			if (strlen($_POST['human']) > 0)
			{
				$number = $_POST['human'];
			}
			else
			{
				$error .= $language[$lang][53];
			}
		}

		mssql_connect($dbhost,$dbuser,$dbpassword) OR die('Cant establish database connection.');
	
		// Check for all required fields
		if(!$_POST['accountname'])
		{
			$error .= $language[$lang][0];
		}
	
		// Check for illegal chars in the account name
		if (!$error)
		{
			$pattern = "^[[:alnum:]].{2,14}$";
			$string = $_POST['accountname'];
			if (!eregi($pattern, $string))
			{
				$error .= $language[$lang][1];
			}
		}

		// Make sure the IP address is not blacklisted
		if (!$error)
		{
			mssql_select_db($dbauth);
			$query = "SELECT * FROM blacklist with (nolock) WHERE ipaddress = '".$remoteip."';";
			$result = mssql_query( $query );
			// Run through the database and see if the address matches a black listed address
			if(mssql_num_rows($result))
			{
				$error .= $language[$lang][2];
			}
		}

		if(!$error)
		{
			// Search the database for the account
			mssql_select_db($dbauth);
			$query = mssql_query("SELECT * from user_auth with (nolock) where account = '".$accountname."';");
			// Check for the account, if it doesn't exist, error out
			if (!mssql_num_rows($query))
			{
				$error .= $language[$lang][7];
			}
		}

		// Make sure the old password was entered
		if (!$error && !$_POST['oldpass'])
		{
			$error .= $language[$lang][4];
		}

		// Make sure both password fields are entered
		if(!$error && (!$_POST['newpass'] || !$_POST['newpass2']))
		{
			$error .= $language[$lang][22];
		}
	
		// Make sure the paswords match
		if(!$error && ($_POST['newpass'] != $_POST['newpass2']))
		{
			$error .= $language[$lang][23];
		}
	
		// Check for a valid date of birth	
		if (!$error && ($_POST['month'] == 0 || $_POST['day'] == 0 || $_POST['year'] == 0))
		{
			$patternDOB = "^([0-9]){1,2}(/){1}([0-9]){1,2}(/){1}([0-9]){1,2}$";
			$patternDOB2 = "[[:alpha:]|[:space:]|[:punct:]]{1,}";
			$dob = $_POST['month']."/".$_POST['day']."/".$_POST['year'];
			if (!eregi($patternDOB, $dob) || eregi($patternDOB2, $dob))
			{
				$error .= $language[$lang][6];
			}
			if ((is_numeric($_POST['year']) === FALSE) || (is_numeric($_POST['day']) === FALSE) || (is_numeric($_POST['month']) === FALSE))
			{
				$error .= $language[$lang][6];
			}
		}

		// Check to see if an email address was entered
		if (!$error && !$_POST['email'])
		{
			$error .= $language[$lang][5];
		}
	
		// Check for a valid email address
		if (!$error && $_POST['email'])
		{
			if (!eregi("^[[:alnum:]][a-z0-9_.-]*@[a-z0-9.-]+\.[a-z]{2,4}$", stripslashes(trim($_POST['email'])))) 
			{
				$error .= $language[$lang][9];
			}
		}

		// Make sure the dob matches
		if (!$error)
		{
			if ((!eregi($patternDOB, $dobm)) || (!eregi($patternDOB, $dobd)) || (!eregi($patternDOB, $doby)))
			{
				if (eregi($patternDOB2, $dobm) || eregi($patternDOB2, $dobd) || eregi($patternDOB2, $doby))
				{
					$error .= $language[$lang][6];
				}
			}
			else
			{
				// Set DOB to a solid variable
				$dob = $dobm."/".$dobd."/".$doby;

				mssql_select_db($dbauth);
				// Contruct the query to compare the dob
				$query = "SELECT account, password FROM user_auth with (nolock) WHERE account = '".$accountname."' AND quiz1 = '".$dob."';";
				$result = mssql_query ($query);
				if(!mssql_num_rows($result))
				{
					$error .= $language[$lang][16];
				}
			}
		}

		// Make sure the email address matches
		if (!$error)
		{
			$mail = checkinject($_POST['email']);
			mssql_select_db($dbauth);
			// Contruct the query to compare the emails
			$query = "SELECT account, quiz2 FROM user_auth with (nolock) WHERE account = '".$accountname."' AND quiz2 = '".$mail."';";
			$result = mssql_query ($query);
			if(!mssql_num_rows($result))
			{
				$error .= $language[$lang][11];
			}
		}

		// Make sure the password for the account is correct to prevent someone from hacking the account
		if (!$error)
		{
			$pass = checkinject($_POST['oldpass']);
			// Connect to DB
			mssql_select_db($dbauth);

			// Contruct the query to compare the passwords
			$query = "SELECT account, password FROM user_auth with (nolock) WHERE account = '".$accountname."' AND password = CONVERT(binary, ".encrypt($pass).");";
			$result = mssql_query ($query);
			if(!mssql_num_rows($result))
			{
				$error .= $language[$lang][12];
			}
		}

		// Make sure the pw isn't corrupted after created
		if (!$error)
		{
			$pwcheck = encrypt($_POST['newpass']);
			if (strlen($pwcheck) != 34)
			{
				$error .= $language[$lang][10];
			}
			if (pw_str_check(checkinject($_POST['newpass']), checkinject($_POST['accountname'])) < $min_pw_str)
			{
				$error .= $language[$lang][57];
			}
		}

		// Check to see if the account was created before this page was made
		if (!$error)
		{
			// Connect to DB
			mssql_select_db($dbauth);
			// See if the account is banned
			$query = "SELECT pay_stat FROM user_account with (nolock) WHERE account = '".$accountname."' AND pay_stat <> '0';";
			$result = mssql_query ( $query );
			if(!mssql_num_rows($result))
			{
				$error .= $language[$lang][24];
			}
			// Accont was not banned, now check for valid info to compare
			if (!$error)
			{
				$query = "SELECT * FROM user_auth with (nolock) WHERE account = '".$accountname."' AND quiz1 = 'quiz1';";
				$result = mssql_query ( $query );
				// Lets see if this an older account or a current one
				if(mssql_num_rows($result) != 0)
				{
					// Old account, redirect them to a different page to set information
					$error .= $language[$lang][25];
				}
			}
		}
		if ($imagever == 1)
		{
			// Make sure the image verication matches
			if (strtolower($number) != $_SESSION['image_random_value'])
			{
				$error .= $language[$lang][18];
			}
			$_SESSION['image_random_value'] = "";
		}

		// Something errored, lets tell the user
		if ($error)
		{
			$message .= $error.$language[$lang][26];
		}

		// Check for errors before changing account password
		if(!$error)
		{
			// Set DOB to a solid variable
			$dob = $dobm."/".$dobd."/".$doby;

			//change PW
			mssql_select_db($dbauth);
			$query = "UPDATE user_auth SET password = CONVERT(binary, ".encrypt($_POST['newpass']).") WHERE account = '".$accountname."';";
			mssql_query($query);
			$message .= $language[$lang][27];

			if ($log == 1)
			{
				// Log the action into log history
				mssql_select_db($dbauth);
				$query = mssql_query("SELECT account from user_auth with (nolock) where account = '".$accountname."';");
				$result = mssql_fetch_row($query);
				$acc_name = $result[0];
				$query = mssql_query("SELECT uid from user_account with (nolock) where account = '".$accountname."';");
				$result = mssql_fetch_row($query);
				$acc_id = $result[0];
				mssql_select_db($dblog);
				$client_ip = getenv('REMOTE_ADDR');
				if (getenv(HTTP_X_FORWARDED_FOR))
				{
					$client_ip .= " - for ".getenv(HTTP_X_FORWARDED_FOR);
				}
				$query = "INSERT INTO Panel_History (acc_id, acc_name, char_id, char_name, panel_action, admin_name, time_stamp, reason, remote_ip) values ( '".$acc_id."', '".$acc_name."', 'n/a', 'n/a', 'Password Change', 'Reg Page', '".date ( 'Y/m/d H:i:s' )."', 'User Changed Password', '".$client_ip."');";
				mssql_query($query);
			}

			if ($mail == 1)
			{
				// Send an email to the address entered
				$body =  $language[$lang][101]."\n";
				$body .= "+--------------------------------------------------------------------------------\n";
				$body .= "|".$language[$lang][110]."\n";
				$body .= "|".$language[$lang][113]."\n";
				$body .= "|".$language[$lang][114].$dob."\n";
				$body .= "|".$language[$lang][115]."\n";
				$body .= "|".$language[$lang][112]."\n";
				$body .= "|\n";
				$body .= "+--------------------------------------------------------------------------------\n";
				$body .= "|".$language[$lang][124]."\n";
				$body .= "|".$language[$lang][121]."\n";
				$body .= "|".$language[$lang][122]."\n";
				$body .= "+--------------------------------------------------------------------------------\n";
				$body .= $language[$lang][123];
				$to = $_POST['email'];
				$subject = $language[$lang][101]."(".$language[$dlang][101].")";
				if ($audit == 1)
				{
					$headers .= "Bcc: ".$auditaddress."\r\n";
				}
				$headers .= "Reply-to: ".$from."\r\n";
				$headers .= "X-Mailer: PHP/".phpversion()."\r\n";
				$headers .= "X-MimeOLE: ".$page_title." Registration System\r\n";
				mail($to, $subject, $body, $headers) or die('Error during mail(). file: '.__FILE__.' line: '.__LINE__.' error: '.$php_errormsg.'.<br />Please report this error in its entirety to the '.$page_title.' staff.');
			}
		}
		mssql_close() or die('Error closing link to MSSQL server');
	} 
	else
	{
		$message = $language[$lang][21];
	}

	// Display Form
	echo"		<div style=\"text-align: center\">\n";
	echo"			<table border =\"3\" width=\"100%\" class=\"LeftContent\">\n";
	echo"				<tr>\n";
	echo"					<td>\n";
	echo"						<form action=\"".$_SERVER['PHP_SELF']."?lang=".$lang."&amp;p=changepass:".$_POST['accountname'].":".$_SERVER['REMOTE_ADDR']."\" method=\"post\" style=\"text-align: center\">\n";
	echo"							<table width=\"50%\" align=\"center\">\n";
	echo"								<tr>\n";
	echo"									<td colspan=\"2\" style=\"text-align: center\">\n";
	echo"										<img class=\"HeaderImage\" src=\"./reg_page_image.jpg\" alt = \"Registration Banner\" />\n";
	echo"									</td>\n";
	echo"								</tr>\n";
	echo"								<tr>\n";
	echo"									<td colspan=\"2\" style=\"text-align: center\">\n";
	echo"										<p>".$language[$lang][212]."</p>\n";
	echo"									</td>\n";
	echo"								</tr>\n";
	echo"								<tr>\n";
	echo"									<td style=\"text-align: right\">\n";
	echo"										".$language[$lang][201]."\n";
	echo"									</td>\n";
	echo"									<td style=\"text-align: left\">\n";
	echo"										<input type=\"text\" name=\"accountname\" value=\"".$_POST['accountname']."\" size=\"17\" maxlength=\"15\" /><font color=\"red\">*</font>\n";
	echo"									</td>\n";
	echo"								</tr>\n";
	echo"								<tr>\n";
	echo"									<td style=\"text-align: right\">\n";
	echo"										".$language[$lang][213]."\n";
	echo"									</td>\n";
	echo"									<td style=\"text-align: left\">\n";
	echo"										<input type=\"password\" name=\"oldpass\" size=\"14\" maxlength=\"16\" /><font color=\"red\">*</font>\n";
	echo"									</td>\n";
	echo"								</tr>\n";
	echo"								<tr>\n";
	echo"									<td style=\"text-align: right\">\n";
	echo"										".$language[$lang][214]."\n";
	echo"									</td>\n";
	echo"									<td style=\"text-align: left\">\n";
	echo"										<input type=\"password\" name=\"newpass\" size=\"14\" maxlength=\"12\" /><font color=\"red\">*</font>\n";
	echo"									</td>\n";
	echo"								</tr>\n";
	echo"								<tr>\n";
	echo"									<td style=\"text-align: right\">\n";
	echo"										".$language[$lang][215]."\n";
	echo"									</td>\n";
	echo"									<td style=\"text-align: left\">\n";
	echo"										<input type=\"password\" name=\"newpass2\" size=\"14\" maxlength=\"12\" /><font color=\"red\">*</font>\n";
	echo"									</td>\n";
	echo"								</tr>\n";
	echo"								<tr>\n";
	echo"									<td style=\"text-align: right\">\n";
	echo"										".$language[$lang][203]."\n";
	echo"									</td>\n";
	echo"									<td style=\"text-align: left\">\n";
	echo"										<select name=\"month\">\n";
	echo"											<option value=\"0\"></option>\n";
	foreach ($months as $key => $value)
	{
		if ($key == $_POST['month'])
		{
			$selected = " selected=\"selected\"";
		}
		else
		{
			$selected = "";
		}
		echo"											<option value=\"".$key."\"".$selected.">".$key." - ".$value."</option>\n";
	}
	echo"										</select>\n";
	echo"										<select name=\"day\">\n";
	echo"											<option value=\"0\"></option>\n";
	foreach ($days as $key => $value)
	{
		if ($value == $_POST['day'])
		{
			$selected = " selected=\"selected\"";
		}
		else
		{
			$selected = "";
		}
		echo"											<option value=\"".$value."\"".$selected.">".$value."</option>\n";
	}
	echo"										</select>\n";
	echo"										<select name=\"year\">\n";
	echo"											<option value=\"0\"></option>\n";
	foreach ($years as $key => $value)
	{
		if ($value == $_POST['year'])
		{
			$selected = " selected=\"selected\"";
		}
		else
		{
			$selected = "";
		}
		echo"											<option value=\"".$value."\"".$selected.">".$value."</option>\n";
	}
	echo"										</select>\n<font color=\"red\">*</font>";
	echo"									</td>\n";
	echo"								</tr>\n";
	echo"								<tr>\n";
	echo"									<td style=\"text-align: right\">\n";
	echo"										".$language[$lang][204]."\n";
	echo"									</td>\n";
	echo"									<td style=\"text-align: left\">\n";
	echo"										<input type=\"text\" name=\"email\" value=\"".$_POST['email']."\" size=\"20\" maxlength=\"40\" /><font color=\"red\">*</font>\n";
	echo"									</td>\n";
	echo"								</tr>\n";
	if ($imagever == 1)
	{
		echo"								<tr>\n";
		echo"									<td style=\"text-align: right\">\n";
		echo"										".$language[$lang][206]."<img src=\"human.php\" />\n";
		echo"									</td>\n";
		echo"									<td style=\"text-align: left\">\n";
		echo"										<input type=\"text\" name=\"human\" size=\"15\" maxlength=\"15\" /><font color=\"red\">*</font>&nbsp;&nbsp;<a href=\"".$_SERVER['PHP_SELF']."?lang=".$lang."\">Refresh Image</a>\n";
		echo"									</td>\n";
		echo"								</tr>\n";
	}
	echo"								<tr>\n";
	echo"									<td colspan=\"2\" style=\"text-align: center\">\n";
	echo"										".$language[$lang][216]."<br /><br />\n";
	echo"									</td>\n";
	echo"								</tr>\n";
	echo"								<tr>\n";
	echo"									<td colspan=\"2\" style=\"text-align: center\">\n";
	echo"										<input type=\"submit\" name=\"change\" value=\"".$language[$lang][210]."\">\n";
	echo"								   </td>\n";
	echo"								</tr>\n";
	echo"								<tr>\n";
	echo"									<td colspan=\"2\" style=\"text-align: center\">\n";
	echo"										<font color=\"red\">*</font>".$language[$lang][208]."\n";
	echo"									</td>\n";
	echo"								</tr>\n";
	echo"							</table>\n";
	echo"						</form>\n";
	echo"					</td>\n";
	echo"				</tr>\n";
	echo"				<tr style=\"text-align: center\">\n";
	echo"					<td colspan=\"2\" style=\"text-align: center\">\n";
	echo"						".$language[$lang][211]."";
	echo"					</td>\n";
	echo"				</tr>\n";	
	echo"				<tr>\n";
	echo"					<td colspan=\"2\" style=\"text-align: center\">\n";
	echo"						<b>".$message."</b>\n";
	echo"					</td>\n";
	echo"				</tr>\n";
	include "./links.php";
	echo"			</table>\n";
	echo"		</div>\n";
}
require './footer.php';
?>
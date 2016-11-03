<?php
session_start();

// Report all errors except E_NOTICE
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

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
	echo "<html>\n	<head>\n		<meta http-equiv=\"content-type\" content=\"text/html; charset=ISO-8859-1\" />\n";
	echo "		<title>".$page_title." User Management System".$designer."</title>\n		<link href=\"./styles.css\" rel=\"stylesheet\" type=\"text/css\" />\n	</head>\n	<body>\n";
	echo "<!-- Any debug messages will appear here -->\n";

	if($_POST['register'] && $designer)
	{
		$_POST['accountname'] = stripslashes($_POST['accountname']);
		$_POST['email'] = trim($_POST['email']);
		$_POST['newpass'] = trim($_POST['newpass']);
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
		if(!$_POST['accountname'] || (strlen($_POST['accountname']) > 14))
		{
			$error .= $language[$lang][0];

		}

		// Check for illegal chars in the account name
		if (!$error)
		{
			// Make sure numbers and letters only
			$pattern = "^[[:alnum:]]{2,14}$";
			// Check for blank spaces
			$pattern2 = "[[:space:]]{1,}";
			$string = $_POST['accountname'];
			if ((!eregi($pattern, $string)) || (eregi($pattern2, $string)))
			{
				$error .= $language[$lang][1];
			}
		}

		// Check for illegal chars in the verification code
		if (!$error)
		{
			// Make sure numbers and letters only
			$pattern = "^[[:alnum:]]{2,}$";
			// Check for blank spaces
			$pattern2 = "[[:space:]]{1,}";
			$string = $_POST['code'];
			if ((!eregi($pattern, $string)) || (eregi($pattern2, $string)))
			{
				$error .= $language[$lang][3];
			}
		}

		// Make sure the IP address is not blacklisted
		if (!$error)
		{
			mssql_select_db($dbauth);
			$remoteIP = checkinject($_SERVER['REMOTE_ADDR']);
			$query = "SELECT * FROM blacklist with (nolock) WHERE ipaddress = '".$remoteIP."';";
			$result = mssql_query($query);
			// Run through the database and see if the address matches a black listed address
			if(mssql_num_rows($result))
			{
				$error .= $language[$lang][2];
			}
		}

		// Make sure password field is entered
		if(!$error && (!$_POST['newpass']))
		{
			$error .= $language[$lang][4];
		}

		// Check to see if an email address was entered
		if (!$error && !$_POST['email'])
		{
			$error .= $language[$lang][5];
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

		if(!$error)
		{
			$accountname = checkinject($_POST['accountname']);
			// Search the database for the account
			mssql_select_db($dbauth);
			$query = mssql_query("SELECT * from user_auth with (nolock) where account = '".$accountname."';");
			// Check for the account, if it doesn't exist, error out
			if (!mssql_num_rows($query))
			{
				$error .= $language[$lang][7];
			}
			if (!$error)
			{
				$query = mssql_query("SELECT * FROM user_account with (nolock) where account = '".$accountname."';");
				if (!mssql_num_rows($query))
				{
					$error .= $language[$lang][8];
				}
			}
		}

		// Check for a valid email address
		if (!$error && $_POST['email'])
		{
			if (!eregi("^[[:alnum:]][a-z0-9_.-]*@[a-z0-9.-]+\.[a-z]{2,4}$", stripslashes(trim($_POST['email'])))) 
			{
				$error .= $language[$lang][9];
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

		// Make sure the email address matches
		if (!$error && $_POST['email'])
		{
			$accountname = checkinject($_POST['accountname']);
			$email = checkinject($_POST['email']);
			mssql_select_db($dbauth);
			// Contruct the query to compare the emails
			$query = "SELECT account, quiz2 FROM user_auth with (nolock) WHERE account = '".$accountname."' AND quiz2 = '".$email."';";
			$result = mssql_query ($query);
			if(!mssql_num_rows($result))
			{
				$error .= $language[$lang][11];
			}
		}

		// Make sure the password for the account is correct to prevent someone from hacking the account
		if (!$error)
		{
			$accountname = checkinject($_POST['accountname']);
			$password = checkinject($_POST['newpass']);
			// Connect to DB
			mssql_select_db($dbauth);

			// Contruct the query to compare the passwords
			$query = "SELECT account, password FROM user_auth with (nolock) WHERE account = '".$accountname."' AND password = CONVERT(binary, ".encrypt($password).");";
			$result = mssql_query ($query);
			if(!mssql_num_rows($result))
			{
				$error .= $language[$lang][12];
			}
		}

		// Check the activation code
		if (!$error)
		{
			$accountname = checkinject($_POST['accountname']);
			$code = checkinject($_POST['code']);
			if (!$_POST['code'])
			{
				$error .= $language[$lang][13];
			}
			if (!$error)
			{
				mssql_select_db($dbauth);
				$query = "SELECT account, verifystring from user_account with (nolock) where account = '".$accountname."' and verifystring = '".$code."';";
				$query = mssql_query($query);
				if (!mssql_num_rows($query))
				{
					$error .= $language[$lang][14];
				}
			}
			if (!$error)
			{
				mssql_select_db($dbauth);
				$query = "SELECT account, verifystring, login_flag from user_account with (nolock) where account = '".$accountname."' AND verifystring = '".$code."' AND login_flag = '1';";
				$query = mssql_query($query);
				if (!mssql_num_rows($query))
				{
					$error .= $language[$lang][15];
				}
			}
		}

		// Make sure the dob matches
		if (!$error)
		{
			$dobm = checkinject($_POST['month']);
			$dobd = checkinject($_POST['day']);
			$doby = checkinject($_POST['year']);
			$accountname = checkinject($_POST['accountname']);

			$patternDOB = "[[:digit:]]{1,2}";
			$patternDOB2 = "[[:alpha:]]{1,}";
			if ((eregi($patternDOB, $dobm)) && (eregi($patternDOB, $dobd)) && (eregi($patternDOB, $doby)))
			{
				if ((!eregi($patternDOB2, $dobm)) && (!eregi($patternDOB2, $dobd)) && (!eregi($patternDOB2, $doby)))
				{
					// Set DOB to a solid variable
					$dob = "'".$dobm."/".$dobd."/".$doby."'";

					mssql_select_db($dbauth);
					// Contruct the query to compare the dob
					$query = "SELECT account, quiz1 FROM user_auth with (nolock) WHERE account = '".$accountname."' AND quiz1 = ".$dob.";";
					$result = mssql_query ($query);
					if(!mssql_num_rows($result))
					{
						$error .= $language[$lang][16];
					}
				}
			}
			else
			{
				$error .= $language[$lang][17];
			}
		}
		if ($imagever == 1)
		{
			// Make sure the image verication matches
			if (strtolower($number) != strtolower($_SESSION['image_random_value']))
			{
				$error .= $language[$lang][18];
			}
			$_SESSION['image_random_value'] = "";
		}

		// Something errored, lets tell the user
		if ($error)
		{
			$message .= $error.$language[$lang][20];
		}

		// Check for errors before activating account
		if(!$error)
		{
			$accountname = checkinject($_POST['accountname']);
			// Connect to DB
			mssql_select_db($dbauth);

			// Contruct the query to do the actual DB injection
			$query = "UPDATE user_account SET login_flag = '0' where account = '".$accountname."';";
			mssql_query( $query );
			$message .= $language[$lang][19];

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
				$query = "INSERT INTO Panel_History (acc_id, acc_name, char_id, char_name, panel_action, admin_name, time_stamp, reason, remote_ip) values ( '".$acc_id."', '".$acc_name."', 'n/a', 'n/a', 'Account Activaction', 'Reg Page', '".date ( 'Y/m/d H:i:s' )."', 'Account Activation', '".$client_ip."');";
				mssql_query($query);
			}
			// Send an email to the address entered
			$body =  $language[$lang][100]."\n";
			$body .= "+---------------------------------------------------------------------------------+\n";
			$body .= "|".$language[$lang][110]."\n";
			$body .= "|".$language[$lang][111]."\n";
			$body .= "|".$language[$lang][112]."\n";
			$body .= "|\n";
			$body .= "+---------------------------------------------------------------------------------+\n";
			$body .= "|".$language[$lang][120]."\n";
			$body .= "|".$language[$lang][121]."\n";
			$body .= "|".$language[$lang][122]."\n";
			$body .= "+---------------------------------------------------------------------------------+\n";
			$body .= "|".$language[$lang][123]."\n";
			$to = $_POST['email'];
			$subject = $language[$lang][100]."(".$language[$dlang][100].")";
			if ($audit == 1)
			{
				$headers .= "Bcc: ".$auditaddress."\r\n";
			}
			$headers .= "Reply-to: ".$from."\r\n";
			$headers .= "X-Mailer: PHP/".phpversion()."\r\n";
			$headers .= "X-MimeOLE: ".$page_title." Registration System\r\n";
			mail($to, $subject, $body, $headers) or die('Error during mail(). file: '.__FILE__.' line: '.__LINE__.' error: '.$php_errormsg.'.<br />Please report this error in its entirety to the '.$page_title.' staff.');
		}
		mssql_close() or die('Error closing link to MSSQL service');
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
	echo"						<form action=\"".$_SERVER['PHP_SELF']."?lang=".$lang."&amp;p=activate:".$_POST['accountname'].":".$_SERVER['REMOTE_ADDR']."\" method=\"post\" style=\"text-align: center\">\n";
	echo"							<table width=\"50%\" align=\"center\">\n";
	echo"								<tr>\n";
	echo"									<td colspan=\"2\" style=\"text-align: center\">\n";
	echo"										<img class=\"HeaderImage\" src=\"./reg_page_image.jpg\" alt=\"Registration Banner\"/>\n";
	echo"									</td>\n";
	echo"								</tr>\n";
	echo"								<tr>\n";
	echo"									<td colspan=\"2\" style=\"text-align: center\">\n";
	echo"										<p>".$language[$lang][200]."</p>\n";
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
	echo"										".$language[$lang][202]."\n";
	echo"									</td>\n";
	echo"									<td style=\"text-align: left\">\n";
	echo"										<input type=\"password\" name=\"newpass\" size=\"16\" maxlength=\"12\" /><font color=\"red\">*</font>\n";
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
	echo"								<tr>\n";
	echo"									<td style=\"text-align: right\">\n";
	echo"										".$language[$lang][205]."\n";
	echo"									</td>\n";
	echo"									<td style=\"text-align: left\">\n";
	echo"										<input type=\"text\" name=\"code\" value=\"".$_POST['code']."\" size=\"20\" maxlength=\"40\" /><font color=\"red\">*</font>\n";
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
	echo"										".$language[$lang][207]."\n";
	echo"									</td>\n";
	echo"								</tr>\n";
	echo"								<tr>\n";
	echo"									<td colspan=\"2\" style=\"text-align: center\">\n";
	echo"										<font color=\"red\">*</font>".$language[$lang][208]."<br /><br />".$language[$lang][209]."\n";
	echo"									</td>\n";
	echo"								</tr>\n";
	echo"								<tr>\n";
	echo"									<td colspan=\"2\" style=\"text-align: center\">\n";
	echo"										<input type=\"submit\" name=\"register\" value=\"".$language[$lang][210]."\">\n";
	echo"								   </td>\n";
	echo"								</tr>\n";
	echo"							</table>\n";
	echo"						</form>\n";
	echo"					</td>\n";
	echo"				</tr>\n";
	echo"				<tr style=\"text-align: center\">\n";
	echo"					<td colspan=\"2\" style=\"text-align: center\">\n";
	echo"						".$language[$lang][211]."\n";
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
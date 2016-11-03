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
	require "./header.php";

	if($_POST['update'] && $designer)
	{
		mssql_connect($dbhost,$dbuser,$dbpassword) OR die('Cant establish database connection.');
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
			$query = "SELECT * FROM blacklist with (nolock) WHERE ipaddress = '".checkinject($_SERVER['REMOTE_ADDR'])."';";
			$result = mssql_query($query);
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
			$query = mssql_query("SELECT * from user_auth with (nolock) where account = '".checkinject($_POST['accountname'])."';");
			// Check for the account, if it doesn't exist, error out
			if (!mssql_num_rows($query))
			{
				$error .= $language[$lang][7];
			}
		}
	
		// Make sure password was entered
		if (!$error && !$_POST['oldpass'])
		{
			$error .= $language[$lang][4];
		}

		// Make sure the pw isn't corrupted after created
		if (!$error)
		{
			$pwcheck = encrypt($_POST['oldpass']);
			if (strlen($pwcheck) != 34)
			{
				$error .= $language[$lang][10];
			}
			if (pw_str_check(checkinject($_POST['oldpass']), checkinject($_POST['accountname'])) < $min_pw_str)
			{
				$error .= $language[$lang][57];
			}
		}

		// Make sure the password for the account is correct to prevent someone from hacking the account
		if (!$error)
		{
			$pass = checkinject($_POST['oldpass']);
			$accountname = checkinject($_POST['accountname']);
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
	
		// Check for a valid date of birth	
		if (!$error && ($_POST['month'] == 0 || $_POST['day'] == 0 || $_POST['year'] == 0))
		{
			$patternDOB = "[[:digit:]]{1,2}";
			if ((!eregi($patternDOB, $_POST['month'])) || (!eregi($patternDOB, $_POST['day'])) || (!eregi($patternDOB, $_POST['year'])))
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
	
		// Check to see if the account was created after this page was made
		if (!$error)
		{
			// Connect to DB
			mssql_select_db($dbauth);
			// See if the account is banned
			$query = "SELECT pay_stat FROM user_account with (nolock) WHERE account = '".checkinject($_POST['accountname'])."' AND pay_stat <> '0';";
			$result = mssql_query($query);
			if(!mssql_num_rows($result))
			{
				$error .= $language[$lang][24];
			}
			// Accont was not banned, now check for valid info to compare
			if (!$error)
			{
				$query = "SELECT * FROM user_auth with (nolock) WHERE account = '".checkinject($_POST['accountname'])."' AND quiz1 <> 'quiz1';";
				$result = mssql_query($query);
				// Lets see if this an older account or a current one
				if(mssql_num_rows($result))
				{
					// Old account, redirect them to a different page to set information
					$error .= $language[$lang][49];
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
			$message .= $error.$language[$lang][50];
		}
		// Check for errors before updating account information
		if(!$error)
		{
			// Set DOB to a solid variable
			$dob = $_POST['month']."/".$_POST['day']."/".$_POST['year'];
			// Connect to DB
			mssql_select_db($dbauth);
			// Contruct the query to do the actual DB injection
			$query = "UPDATE user_auth SET quiz1 = '".checkinject($dob)."', quiz2 = '".checkinject($_POST['email'])."', createdate = '".date ('Y/m/d H:i:s')."', ipcreatefrom = '".checkinject($_SERVER['REMOTE_ADDR'])."' WHERE account = '".checkinject($_POST['accountname'])."';";
			mssql_query ( $query );
			$query = "UPDATE user_account SET login_flag = '0' WHERE account = '".checkinject($_POST['accountname'])."';";
			mssql_query($query);
			$message = $language[$lang][51];
			if ($log == 1)
			{
				// Log the action into log history
				mssql_select_db($dbauth);
				$query = mssql_query("SELECT account from user_auth with (nolock) where account = '".checkinject($_POST['accountname'])."'");
				$result = mssql_fetch_row($query);
				$acc_name = $result[0];
				$query = mssql_query("SELECT uid from user_account with (nolock) where account = '".checkinject($_POST['accountname'])."'");
				$result = mssql_fetch_row($query);
				$acc_id = $result[0];
				mssql_select_db($dblog);
				$client_ip = getenv('REMOTE_ADDR');
				if (getenv(HTTP_X_FORWARDED_FOR))
				{
					$client_ip .= " - for ".getenv(HTTP_X_FORWARDED_FOR);
				}
				$query = "INSERT INTO Panel_History (acc_id, acc_name, char_id, char_name, panel_action, admin_name, time_stamp, reason, remote_ip) values ( '".$acc_id."', '".$acc_name."', 'n/a', 'n/a', 'Account Info Updated', 'Reg Page', '".date ( 'Y/m/d H:i:s' )."', 'Account Info Updated', '".$client_ip."');";
				mssql_query($query);
			}
			if ($mail == 1)
			{
				// Send an email to the address entered
				$body =  $language[$lang][105]."\n";
				$body .= "+--------------------------------------------------------------------------------------------------+\n";
				$body .= "|".$language[$lang][110]."\n";
				$body .= "|".$language[$lang][113]."\n";
				$body .= "|".$language[$lang][114].$dob."\n";
				$body .= "|".$language[$lang][115]."\n";
				$body .= "|".$language[$lang][112]."\n";
				$body .= "|\n";
				$body .= "+--------------------------------------------------------------------------------------------------+\n";
				$body .= "|".$language[$lang][130]."\n";
				$body .= "|".$language[$lang][121]."\n";
				$body .= "|".$language[$lang][122]."\n";
				$body .= "+--------------------------------------------------------------------------------------------------+\n";
				$body .= $language[$lang][123];
				$to = $_POST['email'];
				$subject = $language[$lang][105]."(".$language[$dlang][105].")";
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
	echo"						<form action=\"".$_SERVER['PHP_SELF']."?lang=".$lang."&amp;p=update:".$_POST['accountname'].":".$_SERVER['REMOTE_ADDR']."\" method=\"post\" style=\"text-align: center\">\n";
	echo"							<table width=\"50%\" align=\"center\">\n";
	echo"								<tr>\n";
	echo"									<td colspan=\"2\" style=\"text-align: center\">\n";
	echo"										<img class=\"HeaderImage\" src=\"./reg_page_image.jpg\" alt = \"Registration Banner\" />\n";
	echo"									</td>\n";
	echo"								</tr>\n";
	echo"								<tr>\n";
	echo"									<td colspan=\"2\" style=\"text-align: center\">\n";
	echo"										".$language[$lang][225]."\n";
	echo"									</td>\n";
	echo"								</tr>\n";
	echo"								<tr>\n";
	echo"									<td style=\"text-align: right\">\n";
	echo"										".$language[$lang][201]."\n";
	echo"									</td>\n";
	echo"									<td style=\"text-align: left\">\n";
	echo"										<input type=\"text\" name=\"accountname\" value=\"".$_POST['accountname']."\" size=\"17\" maxlength=\"15\" />\n";
	echo"									</td>\n";
	echo"								</tr>\n";
	echo"								<tr>\n";
	echo"									<td style=\"text-align: right\">\n";
	echo"										".$language[$lang][202]."\n";
	echo"									</td>\n";
	echo"									<td style=\"text-align: left\">\n";
	echo"										<input type=\"password\" name=\"oldpass\" size=\"14\" maxlength=\"12\" />\n";
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
	echo"										".$language[$lang][226]."<br /><br />".$language[$lang][227]."\n";
	echo"									</td>\n";
	echo"								</tr>\n";
	echo"								<tr>\n";
	echo"									<td colspan=\"2\" style=\"text-align: center\">\n";
	echo"										<input type=\"submit\" name=\"update\" value=\"".$language[$lang][210]."\">\n";
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
require "./footer.php";
?>
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
	
	if($_POST['register'] != NULL && $designer)
	{
		mssql_connect($dbhost,$dbuser,$dbpassword) OR die('Cant establish database connection.');
		// Trim off any extra spaces from username, email, and password
		$_POST['accountname'] = trim($_POST['accountname']);
		$_POST['accountname'] = stripslashes($_POST['accountname']);
		$_POST['email'] = trim($_POST['email']);
		$_POST['email2'] = trim($_POST['email2']);
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
	
		// Make sure the IP address is not blacklisted
		if (!$error)
		{
			mssql_select_db($dbauth);
			$query = "SELECT * FROM blacklist with (nolock) WHERE ipaddress = '".$_SERVER['REMOTE_ADDR']."'";
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
			$query = mssql_query("SELECT * from user_auth with (nolock) where account = '".$_POST['accountname']."'");
			// Check for the account, if it doesn't exist, error out
			if (mssql_num_rows($query))
			{
				$error .= $language[$lang][40];
			}
			if (!$error)
			{
				$query = mssql_query("SELECT * FROM user_account with (nolock) where account = '".$_POST['accountname']."'");
				if (mssql_num_rows($query))
				{
					$error .= $language[$lang][8];
				}
			}
		}
	
		// Make sure both password fields are entered
		if(!$error && (!$_POST['newpass'] || !$_POST['newpass2']))
		{
			$error .= $language[$lang][22];
		}
		// Make sure the paswords match
		if(!$error && ($_POST['newpass'] != $_POST['newpass2']))
		{
			$error .= $language[$lang][41];
		}
		// Check for a valid date of birth	
		if (!$error && ($_POST['month'] == 0 || $_POST['day'] == 0 || $_POST['year'] == 0))
		{
			$patternDOB = "[[:digit:]]{1,2}";
			if ((!eregi($patternDOB, $_POST['month'])) || (!eregi($patternDOB, $_POST['day'])) || (!eregi($patternDOB, $_POST['year'])))
			{
				$error .= $language[$lang][6];
			}
			if ((strlen($_POST['month']) > 2) || (strlen($_POST['day']) > 2) || (strlen($_POST['year']) > 4))
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
		// Check to see if both email address fields were entered
		if (!$error && !$_POST['email2'])
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
			if (!eregi("^[[:alnum:]][a-z0-9_.-]*@[a-z0-9.-]+\.[a-z]{2,4}$", stripslashes(trim($_POST['email2'])))) 
			{
				$error .= $language[$lang][9];
			}
			
/*
			$e_host = explode("@", $_POST['email']);
			if (!getmxrr($e_host[1], $mx_records))
			{
				$error .= $language[$lang][56];
			}
*/
			
		}
		// Make sure the emails match
		if(!$error && ($_POST['email'] != $_POST['email2']))
		{
			$error .= $language[$lang][42];
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

		if ($imagever == 1)
		{
			// Make sure the image veri matches
			if (strtolower($number) != $_SESSION['image_random_value'])
			{
				$error .= $language[$lang][18];
			}
			else
			{
				$_SESSION['image_random_value'] = "";
			}
		}

		// Something errored, lets tell the user
		if ($error)
		{
			$message .= $error.$language[$lang][52];
			
		}
		// Check for errors before registering account
		if(!$error)
		{
			// Set DOB to a solid variable
			$dobm = checkinject($_POST['month']);
			$dobd = checkinject($_POST['day']);
			$doby = checkinject($_POST['year']);
			$dob = $dobm."/".$dobd."/".$doby;
			$accountname = checkinject($_POST['accountname']);
			$pass = checkinject($_POST['newpass']);
			$remoteip = checkinject($_SERVER['REMOTE_ADDR']);
			$email = checkinject($_POST['email']);
			$timestamp = date ('Y/m/d H:i:s');
			
			if (is_array($_POST['optout']))
			{
				$optout = "1";
			}
			else
			{
				$optout = "0";
			}
	
			// Connect to DB
			mssql_select_db($dbauth);
	
			// Contruct the query to do the actual DB injection
			$query = "INSERT INTO user_auth (account, password, ipcreatefrom, answer1, answer2, quiz1, quiz2, createdate) ";
			// Account Name
			$query .= "values ('".$accountname."',";
			// Password
			$query .= " CONVERT(binary, ".encrypt($pass)."),";
			// IP created from field
			$query .=  "'".$remoteip."',";
			// Answer1 field
			$query .= " CONVERT(binary, ".encrypt($email)."),";
			// Answer2 field
			$query .= " CONVERT(binary, ".encrypt($dob)."),";
			// Quiz1 field
			$query .= " '" .$dob."',";
			// Quiz2 field
			$query .= " '".$email."',";
			// Create Date field
			$query .= "'".$timestamp."');";
			$success = mssql_query ($query);
			if ($success)
			{
				$message .= $language[$lang][43];
			}
	
	
			if ($mail == 0)
			{
				if ($massemail == 1)
				{
					$query = "INSERT INTO user_account (account, pay_stat, lastbanaction, lastadmin, lastdate, reason, verifystring, online, emailoptout) VALUES ('".checkinject($_POST['accountname'])."',1,'Creation','Creation','".date ( 'Y/m/d H:i:s' )."', 'Creation', 'mailsystemoff', '0', '".$optput."');";
				}
				else
				{
					$query = "INSERT INTO user_account (account, pay_stat, lastbanaction, lastadmin, lastdate, reason, verifystring, online) VALUES ('".checkinject($_POST['accountname'])."',1,'Creation','Creation','".date ( 'Y/m/d H:i:s' )."', 'Creation', 'mailsystemoff', '0');";
				}
			}
			if ($mail == 1)
			{
				$ver1 = strlen($_POST['accountname']);
				$ver2 = strlen($dob);
				$ver3 = strlen($_POST['email']);
				$varnamemult = encrypt(str_shuffle($_POST['accountname']));
				$var1encrypt = str_split($varnamemult);
				for ($i=count($var1encrypt); $i>=(count($var1encrypt) - $ver1); $i--)
				{
					$temp .= $var1encrypt[$i];
					$varnamemult = $temp;
				}
				$temp = "";
				$vardobmult = encrypt(str_shuffle($dob));
				$var2encrypt = str_split($vardobmult);
				for ($i=count($var2encrypt); $i>=(count($var2encrypt) - $ver2); $i--)
				{
					$temp .= $var2encrypt[$i];
					$vardobmult = $temp;
				}
				$verify = $varnamemult.$vardobmult.$varemailmult;
				$verify = str_shuffle(str_shuffle(str_shuffle(ranstring(rand(8, 14),5))));
				if ($massemail == 1)
				{
					$query = "INSERT INTO user_account (account, pay_stat, login_flag, verifystring, lastbanaction, lastadmin, lastdate, reason, online, emailoptout) VALUES ('".checkinject($_POST['accountname'])."',1,1,'".$verify."','Creation','Creation','".date ( 'Y/m/d H:i:s' )."','Creation', '0', '".$optout."');";
				}
				else
				{
					$query = "INSERT INTO user_account (account, pay_stat, login_flag, verifystring, lastbanaction, lastadmin, lastdate, reason, online) VALUES ('".checkinject($_POST['accountname'])."',1,1,'".$verify."','Creation','Creation','".date ( 'Y/m/d H:i:s' )."','Creation', '0');";
				}
			}
			$success = mssql_query($query);
			if ($success === TRUE || count($success) > 0)
			{
				$message .= $language[$lang][44];
			}
	
			if ($log == 1)
			{
				// Log the action into log history
				mssql_select_db($dbauth);
				$query = mssql_query("SELECT account from user_auth with (nolock) where account = '".checkinject($_POST['accountname'])."';");
				$result = mssql_fetch_row($query);
				$acc_name = $result[0];
				$query = mssql_query("SELECT uid from user_account with (nolock) where account = '".checkinject($_POST['accountname'])."';");
				$result = mssql_fetch_row($query);
				$acc_id = $result[0];
				mssql_select_db($dblog);
				$client_ip = getenv('REMOTE_ADDR');
				if (getenv(HTTP_X_FORWARDED_FOR))
				{
					$client_ip .= " - for ".getenv(HTTP_X_FORWARDED_FOR);
				}
				$query = "INSERT INTO Panel_History (acc_id, acc_name, char_id, char_name, panel_action, admin_name, time_stamp, reason, remote_ip) values ( '".$acc_id."', '".$acc_name."', 'n/a', 'n/a', 'Account Creation', 'Reg Page', '".date ( 'Y/m/d H:i:s' )."', 'Registered Account', '".$client_ip."');";
				mssql_query($query);
			}
	
			if ($mail == 1)
			{
				// Send an email to the address entered
				$body =  $language[$lang][103]."\n";
				$body .= "+---------------------------------------------------------------------------------+\n";
				$body .= "|".$language[$lang][110]."\n";
				$body .= "|".$language[$lang][113]."\n";
				$body .= "|".$language[$lang][114].$dob."\n";
				$body .= "|".$language[$lang][115]."\n";
				$body .= "|".$language[$lang][117]." ".$verify."\n";
				$body .= "|".$language[$lang][112]."\n";
				$body .= "|\n|".$language[$lang][127]."\n";
				$body .= "|".$language[$lang][128]."\n|\n";
				$body .= "+---------------------------------------------------------------------------------+\n";
				$body .= "|".$language[$lang][129]."\n";
				$body .= "|".$language[$lang][121]."\n";
				$body .= "|".$language[$lang][122]."\n";
				$body .= "+---------------------------------------------------------------------------------+\n";
				$body .= $language[$lang][123];
				$to = $_POST['email'];
				$subject = $language[$lang][103]."(".$language[$dlang][103].")";
				$headers .= "Reply-to: ".$from."\r\n";
				if ($audit == 1)
				{
					$headers .= "Bcc: ".$auditaddress."\r\n";
				}
				$headers .= "X-Mailer: PHP/".phpversion()."\r\n";
				$headers .= "X-MimeOLE: ".$page_title." Registration System\r\n";
				mail($to, $subject, $body, $headers) or die('Error during mail(). file: '.__FILE__.' line: '.__LINE__.' error: '.$php_errormsg.'.<br />Please report this error in its entirety to the '.$page_title.' staff.');
				$message .= $language[$lang][45];
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
	echo"						<form action=\"".$_SERVER['PHP_SELF']."?lang=".$lang."&amp;p=register:".$_POST['accountname'].":".$_SERVER['REMOTE_ADDR']."\" method=\"post\" style=\"text-align: center\">\n";
	echo"							<table width=\"50%\" align=\"center\">\n";
	echo"								<tr>\n";
	echo"									<td colspan=\"2\" style=\"text-align: center\">\n";
	echo"										<img class=\"HeaderImage\" src=\"./reg_page_image.jpg\" alt=\"Registration Banner\" />\n";
	echo"									</td>\n";
	echo"								</tr>\n";
	echo"								<tr>\n";
	echo"									<td colspan=\"2\" style=\"text-align: center\">\n";
	echo"										".$language[$lang][219]."\n";
	echo"									</td>\n";
	echo"								</tr>\n";
	echo"								<tr>\n";
	echo"									<td style=\"text-align: right\">\n";
	echo"										".$language[$lang][201]."\n";
	echo"									</td>\n";
	echo"									<td style=\"text-align: left\">\n";
	echo"										<input type=\"text\" name=\"accountname\" value=\"".$_POST['accountname']."\" size=\"15\" maxlength=\"15\" /><font color=\"red\">*</font>\n";
	echo"									</td>\n";
	echo"								</tr>\n";
	echo"								<tr>\n";
	echo"									<td style=\"text-align: right\">\n";
	echo"										".$language[$lang][202]."\n";
	echo"									</td>\n";
	echo"									<td style=\"text-align: left\">\n";
	echo"										<input type=\"password\" name=\"newpass\" size=\"12\" maxlength=\"12\" /><font color=\"red\">*</font>\n";
	echo"									</td>\n";
	echo"								</tr>\n";
	echo"								<tr>\n";
	echo"									<td style=\"text-align: right\">\n";
	echo"										".$language[$lang][220]."\n";
	echo"									</td>\n";
	echo"									<td style=\"text-align: left\">\n";
	echo"										<input type=\"password\" name=\"newpass2\" size=\"12\" maxlength=\"12\" /><font color=\"red\">*</font>\n";
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
	echo"										".$language[$lang][221]."\n";
	echo"									</td>\n";
	echo"									<td style=\"text-align: left\">\n";
	echo"										<input type=\"text\" name=\"email2\" size=\"20\" maxlength=\"40\" /><font color=\"red\">*</font>\n";
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
	echo"										".$language[$lang][222]."\n";
	echo"									</td>\n";
	echo"								</tr>\n";
	echo"								<tr>\n";
	echo"									<td colspan=\"2\" style=\"text-align: center\">\n";
	echo"										<font color=\"red\">*</font>".$language[$lang][208]."<br /><br />".$language[$lang][209];
	echo"									</td>\n";
	echo"								</tr>\n";
	echo"								<tr>\n";
	echo"									<td colspan=\"2\" style=\"text-align: center\">\n";
	echo"										<input type=\"submit\" name=\"register\" value=\"".$language[$lang][210]."\">\n";
	echo"								   </td>\n";
	echo"								</tr>\n";
	if ($massemail == 1)
	{
		echo"								<tr>\n";
		echo"									<td style=\"text-align: center; font-size: 7pt; font-family: monospace\" colspan=\"2\">\n";
		echo"										At times your email address may be used in mass emails, <br />check this box if you would like to \"opt out\" from these emails:\n";
		echo"									</td>\n";
		echo"								</tr>\n";
		echo"								<tr>\n";
		echo"									<td style=\"text-align: center;\" colspan=\"2\">\n";
		echo"										<input type=\"checkbox\" name=\"optout[]\" value=\"true\"/>\n";
		echo"									</td>\n";
		echo"								</tr>\n";
	}

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
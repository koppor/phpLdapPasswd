<?php/*

You should NOT see this in your web browser.  If you do, this means that PHP
is not correctly installed on this web server.  Please contact the system
administrator to report this problem.

*/?>

<?php

// Include the configuration and functions, and display an error message
// if unable to do so.
if ((!@include './config.php') || (!@include './functions.php')) {
?>
<html>
<head>
<title>Fatal Error</title>
</head>
<body>
<h1>Fatal Error</h1>
<p>
This system was unable to load a necessary configuration file.  Please contact
the system administrator to report this problem.
</p>
</body>
</html>
<?php
	exit;
}

// Just display the template and exit if the form has not yet been submitted.
if ($_POST['submitted'] != 1) {
	display_template($TEMPLATE_CHANGEPASS);
}

// Pull in the approved variables from the form submission.
$user = $_POST['user'];
$oldpass = $_POST['oldpass'];
$newpass = $_POST['newpass'];
$newpass2 = $_POST['newpass2'];

// Perform some basic error checking.
if (!$user) {
	display_template($TEMPLATE_CHANGEPASS, "You did not specify a user ID");
}
if (($IDCHARACTERS) && (strlen(preg_replace("/$IDCHARACTERS/", "", $user)) > 0)) {
	$user = "";
	display_template($TEMPLATE_CHANGEPASS, "Your user ID contains invalid characters");
}
if (!$oldpass) {
	display_template($TEMPLATE_CHANGEPASS, "You did not specify your current password");
}
if (!$newpass) {
	display_template($TEMPLATE_CHANGEPASS, "You did not specify your new password");
}
if (!$newpass2) {
	display_template($TEMPLATE_CHANGEPASS, "You did not confirm your new password");
}
if (strcmp("$newpass", "$newpass2") != 0) {
	display_template($TEMPLATE_CHANGEPASS, "Your new passwords do not match");
}

// Load the password policy check function.
if (!@include "$CHECKPASSWORD") {
	fatal_error("Unable to load password policies.");
}

// Check the new password to make sure it meets policy.
if ($error = check_password($newpass, $user)) {
	display_template($TEMPLATE_CHANGEPASS, $error);
}

// Get the DN for the user.
$ds = my_ldap_connect($LDAPSERVER, $LDAPPORT, $USETLS, $LDAPVERSION);
if (!($dn = get_dn($ds, $user))) {
	if ($PARANOID) {
		display_template($TEMPLATE_CHANGEPASS, "You gave an incorrect user ID and/or current password");
	}
	$attempted_user = $user;
	$user = "";
	display_template($TEMPLATE_CHANGEPASS, "User ID $attempted_user not found");
}

// Check the current password.
if (!($ldapbind = @ldap_bind($ds, $dn, $oldpass))) {
	if ($PARANOID) {
		display_template($TEMPLATE_CHANGEPASS, "You gave an incorrect user ID and/or current password");
	}
	display_template($TEMPLATE_CHANGEPASS, "You gave an incorrect current password");
}

// Change the current password.
$encodedpass = encode_password($newpass, $ENCODING);
if (!(@ldap_mod_replace($ds, $dn, array('userpassword' => $encodedpass)))) {
	fatal_error("Unable to change password.");
}

// Change the last change date.
if ($CHANGELASTDATE == 1) {
	if ($LASTDATEBINDDN) {
		if (!($ldapbind = @ldap_bind($ds, $LASTDATEBINDDN, $LASTDATEBINDPW))) {
			$error = "Unable to bind to directory to change last date";
		}
	}
	$current_day = (int)(time() / 86400);
	if (!(@ldap_mod_replace($ds, $dn, array($LASTDATEATTRIBUTE => $current_day)))) {
		$error = "Unable to change last change date";
	}
}

// Close the connection to the directory server.
if ($ds) {
	ldap_close($ds);
}

display_template($TEMPLATE_CHANGEPASS, $error, "Your password has been successfully changed");
?>

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
	display_template($TEMPLATE_ADMINPASS);
}

// Pull in the approved variables from the form submission.
$user = $_POST['user'];
$admindn = $_POST['admindn'];
$adminpw = $_POST['adminpw'];
$newpass = $_POST['newpass'];
$newpass2 = $_POST['newpass2'];

// Perform some basic error checking.
if (!$user) {
	display_template($TEMPLATE_ADMINPASS, "You did not specify a user ID");
}
if (($IDCHARACTERS) && (strlen(preg_replace("/$IDCHARACTERS/", "", $user)) > 0)) {
	$user = "";
	display_template($TEMPLATE_ADMINPASS, "The user ID contains invalid characters");
}
if (!$admindn) {
	display_template($TEMPLATE_ADMINPASS, "You did not specify an administrative DN");
}
if (!$adminpw) {
	display_template($TEMPLATE_ADMINPASS, "You did not specify the administrator's password");
}
if (!$newpass) {
	display_template($TEMPLATE_ADMINPASS, "You did not specify the new password");
}
if (!$newpass2) {
	display_template($TEMPLATE_ADMINPASS, "You did not confirm the new password");
}
if (strcmp("$newpass", "$newpass2") != 0) {
	display_template($TEMPLATE_ADMINPASS, "The new passwords do not match");
}

// Load the password policy check function.
if (!@include "$CHECKPASSWORD") {
	fatal_error("Unable to load password policies.");
}

// Check the new password to make sure it meets policy.
if ($error = check_password($newpass, $user)) {
	display_template($TEMPLATE_ADMINPASS, $error);
}

// Get the DN for the user.
$ds = my_ldap_connect($LDAPSERVER, $LDAPPORT, $USETLS, $LDAPVERSION);
if (!($dn = get_dn($ds, $user))) {
	if ($PARANOID) {
		display_template($TEMPLATE_ADMINPASS, "You gave an incorrect user ID and/or current password");
	}
	$attempted_user = $user;
	$user = "";
	display_template($TEMPLATE_ADMINPASS, "User ID $attempted_user not found");
}

// Check the administrator DN and password.
if (!($ldapbind = @ldap_bind($ds, $admindn, $adminpw))) {
	display_template($TEMPLATE_ADMINPASS, "You gave an incorrect administrator DN and/or administrator password");
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

display_template($TEMPLATE_ADMINPASS, $error, "The password has been successfully changed");
?>

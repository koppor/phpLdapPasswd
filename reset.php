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

// Display the form if it has not yet been submitted.
if ($_POST['submitted'] != 1) {
	display_template($TEMPLATE_RESETPASS);
}

if ($RESETFREQUENCY == -1) {
	display_template($TEMPLATE_RESETPASS, "Password resets are not currently allowed");
}

// Pull in the approved variables from the form submission.
$user = $_POST['user'];

// Perform some basic error checking.
if (!$user) {
	display_template($TEMPLATE_RESETPASS, "You did not specify a user ID");
}
if (($IDCHARACTERS) && (strlen(preg_replace("/$IDCHARACTERS/", "", $user)) > 0)) {
	$user = "";
	display_template($TEMPLATE_RESETPASS, "Your user ID contains invalid characters");
}

// Get the DN for the user.
$ds = my_ldap_connect($LDAPSERVER, $LDAPPORT, $USETLS, $LDAPVERSION);
if (!($dn = get_dn($ds, $user))) {
	if ($PARANOID) {
		display_template($TEMPLATE_RESETPASS, "You gave an incorrect user ID or the account is not configured for password resets");
	} else {
		$attempted_user = $user;
		$user = "";
		display_template($TEMPLATE_RESETPASS, "User ID $attempted_user not found");
	}
}

// Verify that there is an email address on file for the user.
if (!($mail = get_value($ds, $dn, $MAILATTRIBUTE))) {
	if ($PARANOID) {
		display_template($TEMPLATE_RESETPASS, "You gave an incorrect user ID or the account is not configured for password resets");
	}
	$attempted_user = $user;
	$user = "";
	display_template($TEMPLATE_RESETPASS, "User ID $attempted_user does not have an email address associated with it");
}

// Create the mail "to" field.
$mail_to = implode(",", $mail);

// Get the current day.
$current_day = (int)(time() / 86400);

// Verify that the current password is old enough to be reset.
if ($RESETFREQUENCY > 0) {
	if (!($last_change_day = get_value($ds, $dn, $LASTDATEATTRIBUTE))) {
		fatal_error("Unable to determine last password change date.");
	}

	if (($last_change_day[0] + $RESETFREQUENCY) > $current_day) {
		display_template($TEMPLATE_RESETPASS, "Your password has been changed too recently and may not be reset.");
	}
}

// Load the password policy check function.
if (!@include "$CHECKPASSWORD") {
	fatal_error("Unable to load password policies.");
}

// Load the password generation function.
if (!@include "$GENERATEPASSWORD") {
	fatal_error("Unable to load password generator.");
}

// Generate a new password until one is created that meets the current password
// policy.  Exit with an error if a good password is not created after a
// configured number of attempts.
$loop_count = 1;
while (TRUE) {
	if ($loop_count > $RESETATTEMPTS) {
		fatal_error("Unable to generate an acceptable password.  Please try again later.");
	}
	$loop_count++;

	$new_password = generate_password(GENERATE_PASSWORD);
	if (!check_password($new_password, $user)) {
		break;
	}
}

// Load the email body into memory.
if (!($mail_body_array = @file($RESET_MAILBODY))) {
	fatal_error("Unable to load email template.");
}
$mail_body = implode('', $mail_body_array);

// Replace any special tags in the email body with the correct value.
$mail_body = preg_replace("/<!-- INSERT PHPLDAPPASSWD PASSWORD -->/", $new_password, $mail_body);
$mail_body = preg_replace("/<!-- INSERT PHPLDAPPASSWD IP -->/", $_SERVER['REMOTE_ADDR'], $mail_body);
$mail_body = preg_replace("/<!-- INSERT PHPLDAPPASSWD DATE -->/", date("D M j G:i:s T Y"), $mail_body);

// Bind to the directory.
if (!($ldapbind = @ldap_bind($ds, $RESETBINDDN, $RESETBINDPW))) {
	fatal_error("Unable to bind to directory.");
}

// Change the current password.
$encodedpass = encode_password($new_password, $ENCODING);
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
	if (!(@ldap_mod_replace($ds, $dn, array($LASTDATEATTRIBUTE => $current_day)))) {
		$error = "Unable to change last change date";
	}
}

// Close the connection to the directory server.
if ($ds) {
	ldap_close($ds);
}

// Send the reset email.
if (mail($mail_to, $RESET_MAILSUBJECT, $mail_body, "From: $RESET_MAILFROM\r\n" . "Reply-To: $RESET_MAILFROM")) {
	$user = "";
	display_template($TEMPLATE_RESETPASS, $error, "Your password has been successfully changed and sent to your email address(es) on file");
}

display_template($TEMPLATE_RESETPASS, "Your password was reset, but an error occured when trying to send your new password in email");
?>

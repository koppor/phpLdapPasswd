<?php

// ****************************************************************************
// Function: display_form
//
// Purpose: Print a form for performing a given action.
//
// Usage: display_form($action, $type)
//
// - $action : The URL to use to submit the form data.
// - $type : The type of form to display.  Available types are:
//   FORM_CHANGE - A form to change a password.
//   FORM_RESET - A form to reset a password.
// ****************************************************************************
function display_form ($action = "", $type = FORM_NULL) {
	global $admindn;
	global $user;

	// Make sure that an action argument is provided.
	if (!$action) {
		fatal_error("No form submission action defined.");
	}

	// Make sure that a valid type argument is given.
	if (!(($type == FORM_CHANGE) || ($type == FORM_RESET) || ($type == FORM_ADMIN))) {
		fatal_error("Invalid form type requested ($type).");
	}

	// Display the common form data.
?>
<form action="<?php echo $action ?>" method="POST">
<input type="hidden" name="submitted" value="1">
<table class="phpLdapPasswdForm">
<tr>
<td><p><strong>User ID</strong></p></td>
<td><input type="text" name="user" value="<?php echo $user; ?>"></td>
</tr>
<?php

	// Display the change password form.
	if ($type == FORM_CHANGE) {
?>
<tr>
<td><p><strong>Current Password</strong></p></td>
<td><input type="password" name="oldpass"></td>
</tr>
<?php
	}

	// Display the administrator change password form.
	if ($type == FORM_ADMIN) {
?>
<tr>
<td><p><strong>Administrator DN</strong></p></td>
<td><input type="text" name="admindn" value="<?php echo $admindn; ?>"></td>
</tr>
<tr>
<td><p><strong>Administrator Password</strong></p></td>
<td><input type="password" name="adminpw"></td>
</tr>
<?php
	}

	// Display the common change password / administrator form fields.
	if (($type == FORM_ADMIN) || ($type == FORM_CHANGE)) {
?>
<tr>
<td><p><strong>New Password</strong></p></td>
<td><input type="password" name="newpass"></td>
</tr>
<tr>
<td><p><strong>New Password (again)</strong></p></td>
<td><input type="password" name="newpass2"></td>
</tr>
<tr>
<td colspan=2>
<center><input type="submit" value="Change Password"></center>
</td>
</tr>
<?php
	}

	// Display the reset password form.
	if ($type == FORM_RESET) {
?>
<tr>
<td colspan=2>
<center><input type="submit" value="Reset Password"></center>
<?php
	}

	// Display the common form data.
?>
</table>
</form>
<?php
}


// ****************************************************************************
// Function: display_div_message
//
// Purpose: Print a message inside a <div> tag.
//
// Usage: display_div_message($message, $class)
//
// - $message : The message to display.
// - $class : (optional) The class to use for the div tag.
// ****************************************************************************
function display_div_message ($message = "", $class = "") {
	if ($class) {
		echo "<div class=\"$class\">";
	} else {
		echo "<div>";
	}
	echo "$message";
	echo "</div>";
}


// ****************************************************************************
// Function: display_password_suggestions
//
// Purpose: Display one or more suggested passwords.
//
// Usage: display_password_suggestions($generator)
//
// - $generator : A file containing a definition for the generate_password()
//   function.  More information about this function may be found in the
//   main phpLdapPasswd README file.
// ****************************************************************************
function display_password_suggestions ($generator) {

	// Load the password generation function.
	if (!@include "$generator") {
		fatal_error("Unable to load password generator.");
	}

?>
<div class="phpLdapPasswdSuggestions">
<?php echo generate_password(GENERATE_SUGGESTIONS); ?>
</div>
<?php
}


// ****************************************************************************
// Function: display_template
//
// Purpose: Display a template file replacing any special tags with the
//  associated expanded output.  Exit the program after doing this.
//
// Usage: display_template($template, $error, $success)
//
// - $template : The name of the file to use as the template file.
// - $error : (optional) An error message to display if requested to do so
//   in the template by a special tag.
// - $success : (optional) A success message to display if requested to do so
//   in the template by a special tag.
// ****************************************************************************
function display_template ($template = "", $error = "", $success = "") {
	global $GENERATEPASSWORD;
	global $ERROR_MESSAGE;

	// Rewrite the error message if necessary.
	if ($error) {
		$error = preg_replace("/<!-- ERROR -->/", $error, $ERROR_MESSAGE);
	}

	// Open the template file to display.
	$templateFp = @fopen("$template", 'r');
	if (!$templateFp) {
		fatal_error("Unable to load template file '$template'.");
	}

	// Read in each line of the template and replace any special tags.
	while (!feof($templateFp)) {
		$buffer = fgets($templateFp, 1024);
		$tmpBuf = trim("$buffer");

		// Display the administrator form and any error or success
		// messages.
		if (strcmp("<!-- INSERT PHPLDAPPASSWD ADMINFORM -->", "$tmpBuf") == 0) {
			display_form($_SERVER[PHP_SELF], FORM_ADMIN);
			continue;
		}

		// Display the password change form and any error or
		// success messages.
		if (strcmp("<!-- INSERT PHPLDAPPASSWD CHANGEFORM -->", "$tmpBuf") == 0) {
			display_form($_SERVER[PHP_SELF], FORM_CHANGE);
			continue;
		}

		// Display error message.
		if (strcmp("<!-- INSERT PHPLDAPPASSWD ERROR -->", "$tmpBuf") == 0) {
			if ($error) {
				display_div_message($error, "phpLdapPasswdError");
			}
			continue;
		}

		// Display the password suggestions.
		if (strcmp("<!-- INSERT PHPLDAPPASSWD PASSWORDSUGGEST -->", "$tmpBuf") == 0) {
			display_password_suggestions($GENERATEPASSWORD);
			continue;
		}

		// Display the password reset form and any error or
		// success messages.
		if (strcmp("<!-- INSERT PHPLDAPPASSWD RESETFORM -->", "$tmpBuf") == 0) {
			display_form($_SERVER[PHP_SELF], FORM_RESET);
			continue;
		}

		// Display success message.
		if (strcmp("<!-- INSERT PHPLDAPPASSWD SUCCESS -->", "$tmpBuf") == 0) {
			if ($success) {
				display_div_message($success, "phpLdapPasswdSuccess");
			}
			continue;
		}

		// Display the buffered line.
		echo "$buffer";
	}

	exit;
}


// ****************************************************************************
// Function: encode_password
//
// Purpose: Encode a given password using a given encoding method.
//
// Usage: encode_password($password, $encoding)
//
// - $password : The password to encode.
// - $encoding : The method to use to encode the password.  The supported
//   methods may be found in the main phpLdapPasswd README file.
//
// Returns: The password encoded in whatever form requested.
// ****************************************************************************
function encode_password ($password = "", $encoding = "clear") {
	if (strcasecmp($encoding, "clear") == 0) {
		$encodedpass = $password;
	} elseif (strcasecmp($encoding, "crypt") == 0) {
		$encodedpass = "{CRYPT}".crypt($password);
	} elseif (strcasecmp($encoding, "md5") == 0) {
		$encodedpass = "{MD5}".base64_encode(pack("H*",md5($password)));
	} elseif (strcasecmp($encoding, "ssha") == 0) {
		mt_srand((double)microtime()*1000000);
		$salt = mhash_keygen_s2k(MHASH_SHA1, $password, substr(pack('h*', md5(mt_rand())), 0, 8), 4);
		$encodedpass = "{SSHA}".base64_encode(mhash(MHASH_SHA1, $password.$salt).$salt);
	} else {
		fatal_error("Invalid password encoding method configured: $encoding");
	}

	return($encodedpass);
}


// ****************************************************************************
// Function: fatal_error
//
// Purpose: Display an error message and exit the progeam.
//
// Usage: fatal_error($error)
//
// - $error : The message to display.
// ****************************************************************************
function fatal_error ($error = "Unknown error") {
	global $AUTOEMAIL, $SYSADMINEMAIL, $TEMPLATE_ERROR;

	// Print the top part of an output template if defined.  Otherwise,
	// print a default header.
	if ($templateFp = @fopen("$TEMPLATE_ERROR", 'r')) {
		while (!feof($templateFp)) {
			$buffer = fgets($templateFp, 1024);
			$tmpBuf = trim("$buffer");
			if (strcmp("<!-- INSERT PHPLDAPPASSWD ERROR -->", "$tmpBuf") == 0) {
				break;
			}
			echo "$buffer";
		}
	} else {
?>
<html>
<head>
<title>Fatal Error</title>
</head>
<body>
<p>
<h1>Fatal Error</h1>
<p>
There has been a fatal error in this application.  More information about this
error is as follows:
</p>
<?php
	}

	// Display the error details.
	echo "<p><strong>Fatal Error:</strong> $error</p>";

	// Email the error message to the system administrators or provide a
	// link for the user to do so.
	if ($SYSADMINEMAIL) {
		if ($AUTOEMAIL) {
			mail($SYSADMINEMAIL, "Fatal Error in phpLdapPasswd", $error);
?>
<p>
This message has automatically been sent to the system administrator for
review.
</p>
<?php
		} else {
?>
<p>
Please report this problem to the system administrator at
<a href="mailto:<?php echo $SYSADMINEMAIL; ?>?subject=Fatal Error in phpLdapPasswd&body=Error: <?php echo $error; ?>"><?php echo $SYSADMINEMAIL; ?></a>
</p>
<?php
		}
	}

	// Display the rest of the template file, or the default footer
	if ($templateFp) {
		fpassthru($templateFp);
	} else {
	?>
</body>
</html>
<?php
	}

	exit;
}


// ****************************************************************************
// Function: get_dn
//
// Purpose: Retrieve the DN (distinguished name) for a given user from an
//  LDAP directory.
//
// Usage: get_dn($ds, $user)
//
// - $ds : A handle to a connection to an LDAP server.
// - $user : The user for which to get the DN.
//
// Returns: The DN for the user or an empty string if the user is not found.
// ****************************************************************************
function get_dn ($ds, $user) {
	global $LDAPBASEDN, $IDATTRIBUTE;

	// Bind anonymously to the directory.
	if (!($ldapbind = @ldap_bind($ds))) {
		fatal_error("Unable to bind anonymously to the directory.");
	}

	// Search for the user entry.
	if (!($sr = @ldap_search($ds, "$LDAPBASEDN", "($IDATTRIBUTE=$user)", array("dn")))) {
		fatal_error("Unable to search directory.");
	}

	// Count the number of entries returned and check for errors.
	$entry_count = ldap_count_entries($ds, $sr);
	if ($entry_count == 0) {
		if (!ldap_free_result($sr)) {
			fatal_error("Unable to free search results");
		}	
		return;
	}
	if ($entry_count != 1) {
		fatal_error("Invalid number of DNs found ($entry_count).");
	}

	// Try to retrieve the DN.
	if (!($entry = ldap_first_entry($ds, $sr))) {
		fatal_error("Unable to retrieve search results.");
	}
	if (!($dn = ldap_get_dn($ds, $entry))) {
		fatal_error("Unable to retrieve DN.");
	}

	// Clean up memory.
	if (!ldap_free_result($sr)) {
		fatal_error("Unable to free search results");
	}

	return $dn;
}


// ****************************************************************************
// Function: get_value
//
// Purpose: Retrieve the value for a given attribute from a user's entry in an
//  LDAP directory.
//
// Usage: get_value($ds, $dn, $attribute)
//
// - $ds : A handle to a connection to an LDAP server.
// - $dn : The DN of the entry from which to get the attribute values.
// - $attribute : The attribute for which to get the values.
//
// Returns: The values of the given attirbute in the given LDAP entry.  This
//  is returned as an array even if only one value is found.
// ****************************************************************************
function get_value ($ds, $dn, $attribute) {
	// Bind anonymously to the directory.
	if (!($ldapbind = @ldap_bind($ds))) {
		fatal_error("Unable to bind anonymously to the directory.");
	}

	// Search the user entry.
	if (!($sr = @ldap_search($ds, $dn, "($attribute=*)", array("$attribute")))) {
		fatal_error("Unable to search directory.");
	}

	// Count the number of entries returned and check for errors.
	$entry_count = ldap_count_entries($ds, $sr);
	if ($entry_count == 0) {
		if (!ldap_free_result($sr)) {
			fatal_error("Unable to free search results");
		}	
		return;
	}
	if ($entry_count != 1) {
		fatal_error("Invalid number of entries found ($entry_count) for LDAP entry $dn.");
	}

	// Try to retrieve the requested value.
	if (!($entry = ldap_first_entry($ds, $sr))) {
		fatal_error("Unable to retrieve search results.");
	}
	$values = ldap_get_values($ds, $entry, "$attribute");
	if (!ldap_free_result($sr)) {
		fatal_error("Unable to free search results");
	}

	// Remove the "count" value
	unset($values["count"]);

	return $values;
}


// ****************************************************************************
// Function: my_ldap_connect
//
// Purpose: Open a connection to an LDAP server.
//
// Usage: my_ldap_connect($server, $port, $usetls)
//
// - $server : The name or IP address of the server to which to connect.
// - $port : (optional) (default 389) The port number to which to connect.
// - $usetls : (optional) (default 0) If 1, then use TLS for the connection.
//   Otherwise, TLS is not used.
// - $version : (optional) (default 3) LDAP protocol version to use for
//   communication with the server.
//
// Returns: A handle to the connection.
// ****************************************************************************
function my_ldap_connect ($server = "", $port = 389, $usetls = 0, $version = 3) {

	// Make sure that a server argument was given.
	if (!$server) {
		fatal_error("LDAP server is not defined.");
	}

	// Validate the LDAP version argument.
	if (($version != 2) && ($version != 3)) {
		fatal_error("Invalid LDAP version given.");
	}

	// Connect to the LDAP server.
	$ds = @ldap_connect($server, $port);
	if (!$ds) {
		fatal_error("Unable to connect to the LDAP server.");
	}

	// Set the LDAP protocol version.
	if (($usetls == 1) || ($version == 3)) {
		if (!@ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3)) {
			fatal_error("Failed to set LDAP protocol version to 3.  TLS not supported.");
		} 
	}

	// Turn on TLS if requested.
	if ($usetls == 1) {
		if (!@ldap_start_tls($ds)) {
			fatal_error("Unable to start TLS.");
		}
	}

	return $ds;
}
?>

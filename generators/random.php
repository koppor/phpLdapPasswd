<?php

// Function: generate_password
//
// PURPOSE
//
// This is a password generation function for use by phpLdapPasswd, (see
// http://www.karylstein.com/).  This function uses the randchar() function
// written by Erich Spencer, which must be in a file called randchar.php in
// the current directory.
//
// INSTALLATION
//
// Edit the config.php file of your phpLdapPasswd installation and change the
// $GENERATEPASSWORD variable to point to this file.
//
// CONFIGURATION
//
// Modify the variables found in the CONFIGURATION OPTIONS section as needed. 
//
// CHANGELOG
//
// 08/08/04 - Karyl F. Stein - Original program written.

function generate_password ($type) {

	// ************************************************
	// CONFIGURATION OPTIONS
	// ************************************************

	// Number of passwords to generate for suggestions.
	$number_suggestions = 6;

	// Length of the passwords.
	$length = 6;

	// Character makeup of the passwords (see the documentation in the
	// randchar.php file for more information).
	$range = 'anc';

	// Case to use for the passwords (see the documentation in the
	// randchar.php file for more information).
	$case = 'm';

	// ************************************************
	// END CONFIGURATION OPTIONS
	// ************************************************

	// Include the randchar() function.
	if ((!@include_once 'randchar.php') || (!function_exists('randchar'))) { 
		if ($type == GENERATE_PASSWORD) {
			return;
		} else {
			return "Unable to load random character generator.";
		}
	}

	// Set the number of passwords to return.
	if ($type == GENERATE_PASSWORD) {
		$number_passwords = 1;
	} else {
		$number_passwords = $number_suggestions;
	}

	// Generate the passwords.
	while ($number_passwords-- > 0) {
		$output .= randchar($length, $range, $case);
		if ($number_passwords > 0) {
			$output .= "<br>\n";
		}
	}

	// Format the output.
	if ($type == GENERATE_SUGGESTIONS) {
		$output = "<p style=\"margin-top:0px;margin-bottom:0px\">$output</p>";
	}
	return $output;
}
?>

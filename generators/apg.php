<?php

// Function: generate_password
//
// PURPOSE
//
// This is a password generation function for use by phpLdapPasswd, (see
// http://www.karylstein.com/).  This function uses the apg program, which
// may be found at http://www.adel.nursat.kz/apg/.  For password suggestions,
// the default settings for apg are used.  For password resets, a more random
// string is generated.
//
// INSTALLATION
//
// Edit the config.php file of your phpLdapPasswd installation and change the
// $GENERATEPASSWORD variable to point to this file.  Make sure that the apg
// program is installed and working.  For Debian systems, this may be done by
// executing apt-get install apg as root.
//
// CONFIGURATION
//
// Modify the variables found in the CONFIGURATION OPTIONS section as needed.
//
// CHANGELOG
//
// 08/07/04 - Karyl F. Stein - Original program written.

function generate_password ($type) {

	// ************************************************
	// CONFIGURATION OPTIONS
	// ************************************************

	// Location of the apg program.
	$program = "/usr/bin/apg";

	// Optional location for a word list to use as a filter.  This should
	// be a plain text file with one word per line.  Words in this file
	// will not be used as passwords.  Comment out this line or make it
	// empty to not use a filter list.
	$filterlist = "/usr/share/dict/words";

	// ************************************************
	// END CONFIGURATION OPTIONS
	// ************************************************


	// Set the arguments to apg.
	$arguments = "-q";
	if ($filterlist) {
		$arguments .= " -r $filterlist";
	}
	if ($type == GENERATE_PASSWORD) {
		$arguments .= " -a 1 -M NCL -n 1 -d";
	}

	// Run the apg program.
	$output = `$program $arguments`;

	// Modify the output as needed.
	if ($type == GENERATE_SUGGESTIONS) {
		$output = str_replace(")\n", ")<br>\n", "$output");
		$output = "<p style=\"margin-top:0px;margin-bottom:0px\">$output</p>";
	}

	return $output;
}
?>

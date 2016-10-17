<?php

function check_password ($password, $user) {

	// Minimum length of password.
	$MINLEN = 4;

	// Maximum length of password.
	$MAXLEN = 250;

	// Set to 1 if the password cannot contain the user ID
	$NOUSER = 1;

	// Minimum number of uppercase letters required.
	$MINUPPER = 0;

	// Minimum number of lowercase letters required.
	$MINLOWER = 0;

	// Minimum number of numbers required.
	$MINNUMBER = 0;

	// Minimum number of special characters required.
	$MINSPECIAL = 0;

	if (strlen($password) < $MINLEN) {
		return("Password must be at least $MINLEN characters long.");
	}

	if (strlen($password) > $MAXLEN) {
		return("Password can be no more than $MAXLEN characters long.");
	}

	if (stristr($password, $user)) {
		return("Password cannot contain your user ID");
	}

	$count = strlen($password);

	if (strlen(preg_replace("/[A-Z]/", "", $password)) + $MINUPPER > $count) {
		$string = ($MINUPPER>1)?"characters":"character";
		return("Password must contain at least $MINUPPER uppercase $string.");
	}

	if (strlen(preg_replace("/[a-z]/", "", $password)) + $MINLOWER > $count) {
		$string = ($MINLOWER>1)?"characters":"character";
		return("Password must contain at least $MINLOWER lowercase $string.");
	}

	if (strlen(preg_replace("/[0-9]/", "", $password)) + $MINNUMBER > $count) {
		$string = ($MINNUMBER>1)?"numbers":"number";
		return("Password must contain at least $MINNUMBER $string.");
	}

	if (strlen(preg_replace("/[~`!@#$%^&*()_\-\+\=\\|\{\[\}\]:;\"\'<,>.\?\/]/", "", $password)) + $MINSPECIAL > $count) {
		$string = ($MINSPECIAL>1)?"characters":"character";
		return("Password must contain at least $MINSPECIAL non-alphanumeric $string.");
	}
}

<?php

// This is the main configuration file for phpLdapPasswd.  The options in the
// first section MUST be configured for phpLdapPasswd to work.  To get a
// working system for testing purposes, only the first section should need to
// be modified.  For production use, it is recommended that this entire file be
// reviewed and configured to fit the specific environment and policies.  More
// configuration information may be found in the README file as part of the
// phpLdapPasswd distribution.  If that file is not available, please visit
// the phpLdapPasswd web site at http://www.karylstein.com/.

// ****************************************************************************
// GENERAL CONFIGURATION OPTIONS -- MUST CHANGE
//
// The options in this section are the ones that must be configured in order
// for the phpLdapPasswd software to work.
// ****************************************************************************

// The LDAPSERVER and LDAPPORT define the server to which phpLdapPasswd should
// connect to change the password.  This should be a server with write access
// and not a replica.
//
// To enable SSL, be sure to configure the LDAP server to accept SSL
// connections and make sure that the CN in the certificate used for the SSL
// connection matched what is placed in the LDAPSERVER line.  The LDAPSEVER
// and LDAPPORT lines should look like the following for SSL, assuming that
// your LDAP server uses the default LDAP SSL port:
//
// $LDAPSERVER = "ldaps://YOURLDAPSERVER/";
// $LDAPPORT = 636;
//
// You may also use TLS instead of SSL.  That option is defined below.
//
// The standard LDAP ports are 389 for non-SSL communications and 636 for SSL.
// If TLS is used, the port should probably be set to 389, although it is
// possible to do TLS over SSL.  (In that case, just using SSL should be
// sufficient, though.)
$LDAPSERVER = "ldap://masterdir.example.com/";
$LDAPPORT = 389;

// The LDAPBASEDN is the base DN that should be used for LDAP searches.  This
// should at least be set to the base DN for the LDAP server.  It may, however,
// be defined as a higher base DN such as ou=People,dc=example,dc=com for
// greater security or if there are multiple phpLdapPasswd instances serving
// different trees of users.
$LDAPBASEDN = "dc=example,dc=com";

// ****************************************************************************
// GENERAL CONFIGURATION OPTIONS
// ****************************************************************************

// Define what version of the LDAP protocol to use.  3 is suggested unless
// there is some need for 2.  If TLS is used, then version 3 is automatically
// picked.
$LDAPVERSION = 3;

// Set this to 1 if you want to use TLS.  Otherwise, set it to 0.  SSL may be
// a preferred option as it may be possible for TLS to negotiate an unencrypted
// channel depending on its configuration.
$USETLS = 0;

// Specify how the passwords should be encoded in the LDAP server.  The choices
// are CLEAR, CRYPT, MD5, and SSHA.  (SSHA is untested and does not work on a
// default Debian Woody system!)
$ENCODING = MD5;

// This should be set to the attribute for which to search when a login ID
// is entered.  This attribute should have a unique value in the $LDAPBASEDN
// given.
$IDATTRIBUTE = uid;

// A list of all characters that may be found in a user ID.  This is a security
// measure to make sure bad data is not submitted.  If you do not want to check
// the submitted ID, then leave this blank.  The list should be something that
// can be used as a PERL regular expression.
$IDCHARACTERS = "[A-Za-z0-9-_.@]";

// Set this to 1 if you want error messages regarding incorrect user IDs
// or passwords to be the same.  If set to 0, then the error message will
// specifically say if the user ID was not found or if the current password
// is incorrect.  Some may argue that saying a user ID is valid or not is
// a security risk.
$PARANOID = 0;

// If password modifications should also cause a "last change date" attribute
// to be modified, set $CHANGELASTDATE to 1 and define the $LASTDATEATTRIBUTE,
// $LASTDATABINDDN and $LASTDATEBINDPW variables.  The date put into the
// $LASTDATEATTRIBUTE is the number of days since the UNIX "epoch".
$CHANGELASTDATE = 0;

// NOTE: The following two variables are ONLY used if $CHANGELASTDATE is 1.
//
// The $LASTDATEBINDDN variable is used to define a user that has access to
// change the value of the $LASTDATEATTRIBUTE of a user.  The $LASTDATEBINDPW
// variable defines the password used to bind as $LASTDATEBINDDN.  In some
// configuration, the user has access to modify their own $LASTDATEATTRIBUTE.
// If that is the case, then these variables should be left blank.
$LASTDATEBINDDN = "cn=Proxy User,dc=example,dc=com";
$LASTDATEBINDPW = "YourSuperSecretPassword!";

// The attribute to use when changing or verifying the last change date.  In
// most cases, this should be shadowLastChange.
$LASTDATEATTRIBUTE = "shadowLastChange";

// Fatal errors may display an email address to which people may report
// problems.  Set $SYSADMINEMAIL to the email address that should be shown.
// If no email address should be given, leave this line blank.
$SYSADMINEMAIL = "staff@example.com";

// If you want phpLdapPasswd to automatically send email to the email address
// defined in $SYSADMINEMAIL instead of displaying a link that the user can
// click to send email, then set AUTOEMAIL to 1.  Otherwise, set AUTOEMAIL to
// 0.
$AUTOEMAIL = 0;

// Locations of the HTML templates to use for displaying the output generated
// by phpLdapPasswd.  These should be normal HTML type files with special
// tags in them.  For more information about the template files and the
// special tags, see the README file.
//
// The template used when an administrator is changing a password.
$TEMPLATE_ADMINPASS = "./templates/passwordAdmin.html";
//
// The template used when changing a password.
$TEMPLATE_CHANGEPASS = "./templates/passwordChange.html";
//
// The template used when resetting a password.
$TEMPLATE_RESETPASS = "./templates/passwordReset.html";
//
// The template used when displaying a fatal error.
$TEMPLATE_ERROR = "./templates/passwordError.html";

// Location of the file containing the check_password function.  This is
// used to check passwords to make sure that they adhere to policy.  For
// more information about this, see the README file.
$CHECKPASSWORD = "./policies/xenos.php";

// Location of the file containing the generate_password function.  This is
// used to generate passwords as hints or in the case of a password reset.  For
// more information about this, see the README file.
$GENERATEPASSWORD = "./generators/random.php";

// Any non-fatal error messages can be inserted into a template file by using
// a special tag.  The $ERROR_MESSAGE variable defines how the actual message
// should be displayed.  This is in addition to any CSS definitions used in
// the templates.  The string <!-- ERROR --> will be replaced by the actual
// error message.
$ERROR_MESSAGE = "<p style=\"margin-top:0px;margin-bottom:0px\"><strong style=\"color:red\">Error:</strong> <strong><!-- ERROR --></strong></p>";

// ****************************************************************************
// PASSWORD RESET OPTIONS
//
// These variables are only used if the password reset function is used.  If
// password resets should not be allowed, remove the reset.php file and/or
// set the $RESETFREQUENCY variable to -1.
// ****************************************************************************


// If the password reset function is used, then a bind DN and password need to
// be defined.  This DN should have access to change the userPassword attribute
// of an entry.  If the $CHANGELASTDATE variable is set to 1 then the
// $LASTDATEDN, if defined, will be used for changing the last date attribute.  
// Otherwise, the $RESETBINDDN information will be used.
$RESETBINDDN = "cn=Proxy User,dc=example,dc=com";
$RESETBINDPW = "YourSuperSecretPassword!";

// This should be set to the attribute for which to search when a password
// reset is requested.  The value for this should be an email address for
// the user.
$MAILATTRIBUTE = mail;

// This is the number of days that must pass between password resets.  If
// people should be allowed to reset their passwords multiple times a day,
// set this to 0.  If people should NOT be allowed to reset their password,
// set this to -1.  Please note that the frequency check only looks at the
// day and not the time.  Therefore, if a password is changed at 11:59 PM and
// the reset frequency is set to 1, then the password may be reset again at
// 12:01 AM the next day (2 minutes later).  Also note that the attribute
// defined in $LASTDATEATTRIBUTE is used to get the day of the last change.
// Other applications such as passwd may modify this value outside of
// phpLdapPasswd.  This should probably be at least 1 so someone doesn't go
// resetting other people's passwords every few seconds.
$RESETFREQUENCY = 1;

// Set $RESETATTEMPTS to the number of attempts allowed to generate a valid
// password using the process defined in the $GENERATEPASSWORD variable.  This
// is a safety valve to protect against an infinate loop.  Set this to 0 if
// the process should not be stopped.  However, doing so is not recommended.
$RESETATTEMPTS = 10;

// The template to use as the email body for password resets.
$RESET_MAILBODY = "templates/mailReset.txt";

// The subject to use for the password reset email.
$RESET_MAILSUBJECT = "Password Reset";

// The from and reply address to use for the password reset email.
$RESET_MAILFROM = $SYSADMINEMAIL;

// ****************************************************************************
// DO NOT CHANGE ANYTHING BELOW THIS LINE
// ****************************************************************************

// Define constants.
define("GENERATE_SUGGESTIONS", 1);
define("GENERATE_PASSWORD", 2);
define("FORM_NULL", 0);
define("FORM_ADMIN", 1);
define("FORM_CHANGE", 2);
define("FORM_RESET", 4);
?>

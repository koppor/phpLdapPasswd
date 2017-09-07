# phpLdapPasswd [![No Maintenance Intended](http://unmaintained.tech/badge.svg)](http://unmaintained.tech/)

Version: (not yet released: plan: 0.6)
Date: (not yet released)

phpLdapPasswd is a system that allows certain password management functions
to be initiated from a standard web browser.  As the name suggests,
phpLdapPasswd is designed to integrate with an LDAP directory.  The core
functionality includes password changes and password resets.  Templates, CSS
directives and external functions allow for a high degree of flexibility in
how phpLdapPasswd looks and acts.  However, quick deployments are also possible
by using the included default files and settings.


## OTHER SOFTWARE

Simpler versions of a similar functionality are available at:
 * https://gist.github.com/657334 - LDAP PHP Change Password Page
 * https://github.com/arthurfurlan/php-ldap-passwd - php-ldap-passwd 


## INTRODUCTION

Storing passwords in a central location such as an LDAP server can provide
significant administrative benefits to an organization.  However, in order
to fully reap the rewards of such a configuration, appropriate password
management services must be available.  phpLdapPasswd is just one tool that may
help in this scenario.  It provides the following functionality:

* Users can change their password using a web form,
* Users can request their password to be reset and sent to them via email,
* Administrators can change anyone's password,
* Password policies can be enforced using provided functions or creating
  custom ones, and
* Administrators have a wide range of configuration and template options to
  make the system look and operate as they want.

One goal of phpLdapPassword is to be highly flexible.  Unfortunately, this
can also cause the installation and configuration to be daunting.  Therefore,
phpLdapPasswd ships with a set of default settings that should allow for rapid
deployment in order to get a feel for the software.


## DOWNLOADS

The current release of phpLdapPasswd can be retrieved in the software section
on the web site http://www.karylstein.com/.


## PREREQUISITES

phpLdapPasswd has been reported as working on the following platforms:

* Debian Linux 3.0R2 (Woody)

We are interested in hearing of other platforms where phpLdapPasswd has been
successfully implemented.  Please see the SUPPORT section for contact
information.

The only thing that phpLdapPasswd needs to run is PHP with LDAP support.  A
web server is also suggested.  On Linux systems, the apache, php, and php-ldap
packages for the distribution in question should probably be installed.  (The
actual package names may be different.  For example, on a Debian Woody system,
the php4 and php4-ldap packages should probably be used.)

An LDAP server should also be installed, configured, and populated with user
data.  The process for doing that is outside the scope of this document.  One
description of using LDAP for user authentication may be found in the Documents
section on the web site http://www.karylstein.com/.


## QUICK INSTALL

The fastest way to get phpLdapPasswd operational is to follow the steps given
below.  For more details about the installation of phpLdapPasswd, please see
the installation section.

1. Make sure all the prerequisites are met.

2. Go into the "web root" for your system.  On a Linux system, you may enter
   a command like the following:

   Debian
   $ cd /var/www

   RedHat
   $ cd /var/www/html

3. Retrieve the phpLdapPasswd package.  You probably need to be root or
   other administrative user do do this if downloading to the web root
   directory.

4. Uncompress the package.  On a Linux system, you may be able to enter a
   command like the following:

   # tar xvzf phpLdapPasswd.tar.gz

5. Go into the phpLdapPasswd directory and edit the config.php file.  Only
   the items in the first "MUST EDIT" section need to be modified.  On a Linux
   system, you may be able to use the following commands to do this:

   # cd phpLdapPasswd
   # vi config.php

At this point, a web browser may be used to access the server on which
phpLdapPasswd was installed.  The path that should be given to the web server
is /phpLdapPasswd/index.php.  For example, if the web server is named
www.example.com, the URL http://www.example.com/phpLdapPasswd/index.php should
be used.

Once the basic configuration is installed and working, the entire config.php
file should be reviewed and edited as desired.


## INSTALLATION

The first step in installing phpLdapPasswd is to download the most recent
version of the software.  Please see the download section for more information
about doing that.  Once you have the most recent release, you should uncompress
the archive in an appropriate spot.  This may be any location of your choosing.
Some examples locations are:

* /usr/local/share : Suggested location
* /var/www/html : Default document root on RedHat 7.3 systems
* /var/www : Default document root on Debian Woody systems

The files may even be installed in a user's person web directory, (e.g.
public_html), for testing purposes.

Once the files are uncompressed, go into the newly created phpLdapPasswd
directory and open the file config.php in your favorite editor.  This file is
heavily commented with instructions on how to configure it.  Be sure to read
through all the options and set each one as desired.  The ones that you must
configure in order for phpLdapPasswd to work are in the first section titled
"GENERAL CONFIGURATION OPTIONS -- MUST CHANGE".

After the config.php file is configured as desired, the templates and password
policies should be modified as needed.  More information about templates and
password policies may be found in the USAGE section.

If necessary, the web server should be configured to locate the phpLdapPasswd
directory.  If the directory is in an area already accessible by the web server
then no further configuration is needed.  However, if the directory is placed
in an area outside of a web root, then the web server needs to be configured to
be able to find the application.

A file named /etc/http/conf.d/phpLdapPasswd.conf may be created on certain
RedHat systems containing the following line:

Alias /password/ /var/www/phpLdapPasswd/

This will configure the web server to look in /var/www/phpLdapPasswd when
http://YOURSERVER/password/ is loaded in a browser.  The web server must be
reloaded for this to take effect.

An apache.conf file for use on Debian Woody systems is also included.  A line
should be added to the /etc/apache-ssl/httpd.conf and/or /etc/apache/httpd.conf
to include this file.  This line may look something like the following:

Include /usr/local/share/phpLdapPasswd/apache.conf

At this point, you should be able to load the phpLdapPasswd application in a
web browser and test it out.


## USAGE

There are three main pieces that may be configured to fully integrate
phpLdapPasswd into a web site.  These are the templates, the password policies,
and the password generation function.


## Templates

Templates are plain files that contain special tags in them to mark where data
should be inserted.  The phpLdapPasswd program will read through a template and
send it verbatim to the user.  When a special tag is found, it is replaced with
whatever output phpLdapPasswd generates for the request.  There are templates
for web pages and a template for a password reset email.  These are handled
differently

## Web Page Templates

The tags in web pages must appear on a line all by themselves.  Spaces or other
white space are allowed before and/or after the tag, but no other character may
appear on the line or else the tag will not be matched.  The available special
tags for web pages are:

`<!-- INSERT PHPLDAPPASSWD ADMINFORM -->`
Insert a form requesting a user ID, administrator DN and password, and new
password.

`<!-- INSERT PHPLDAPPASSWD ERROR -->`
Insert any error messages for the current transaction.

`<!-- INSERT PHPLDAPPASSWD CHANGEFORM -->`
Insert a form requesting the user ID, old password and new password.

`<!-- INSERT PHPLDAPPASSWD PASSWORDSUGGESTIONS -->`
Insert a list of suggested passwords.

`<!-- INSERT PHPLDAPPASSWD RESETFORM -->`
Insert a form requesting the user ID.

`<!-- INSERT PHPLDAPPASSWD SUCCESS -->`
Insert any success messages for the current transaction.

## Email Templates

The tags in email templates may appear anywhere in the message and DO NOT have
to be on a line by themselves.  The available special tags for email messages
are:

`<!-- INSERT PHPLDAPPASSWD DATE -->`
Insert the current server date.

`<!-- INSERT PHPLDAPPASSWD IP -->`
Insert the IP address of the person making the request as reported by the
web server.

`<!-- INSERT PHPLDAPPASSWD PASSWORD -->`
Insert the new password.

Some examples of templates may be found in the templates directory as found in
the phpLdapPasswd distribution.


## CSS

There are some CSS directives that may be defined in the web templates.  An
example CSS file may be found in the templates directory.  The available class
definitions are:

phpLdapPasswdError - The style used when displaying error messages.
phpLdapPasswdForm - The style used by the table used to display the form input
 fields.
phpLdapPasswdSuccess - The style used when printing a success message.
phpLdapPasswdSuggestions - The style used when displaying password suggestions.


## Password Policies

Password policies define how an acceptable password should look.  For example,
a password may have to be a certain length, contain a certain number of upper
and lowercase letters, contain one or more numbers, etc.  Different policies
may also be defined for different users or classes of users.  Instead of trying
to generate a password policy definition system that may fit all cases, it is
up to those using phpLdapPasswd to create their own policy checking function.

An example policy check function is included in the phpLdapPasswd distribution.
This may be found as example.php in the policies directory.  The example
function allows you to define the minimum and maximum length of the password,
the minimum number of upper and lowercase letters if should have, the minimum
number of numbers it should have, and the minimum number of non-alphanumeric
characters it should have.  It also allows you to verify that the user ID does
not exist in the password.

The example password policy function may work for your needs after defining
the various parameters.  However, if you want to perform more advanced checks,
then a custom policy function will have to be generated.  This function is a
standard PHP function and takes the following format:

    string check_password ($password, $userid)

The first argument to the function is the password to check.  The second
argument is the user ID of the person changing their password.  The function
should return an error message if there is a problem with the check.  If the
password is valid, then the function should not return anything.


## Password Generation

Password generation is used when resetting passwords or when requested to
create a list of recommended passwords.  In order to provide maximum
flexibility, it is up to those using phpLdapPasswd to create their own
password generation functions.

Some example password generation functions are included in the phpLdapPasswd
distribution.  These may be found in the generators directory.  Please see the
README in that directory for more information about the available functions.
If you create a new function, please consider submitting it to the maintainers
for inclusion in phpLdapPasswd.


## SUPPORT

phpLdapPasswd is released in the hopes that it may be useful for others, but
USE AT YOUR OWN RISK.  phpLdapPasswd was written by Karyl F. Stein.  The
official web page is http://www.karylstein.com/.


## CREDITS

The initial idea for phpLdapPasswd came from http://logout.sh/computers/ldap/.

The following people have submitted bug reports or otherwise assisted with
development:

* Jubal Kessler

THANKS to you and anyone else that may have been missed, (please let me know!)

# PHP Registration package
This package contains a series of PHP scripts I developed a long time ago using Smarty Templates, HTML_QuickForm, and PearDB.  Some of the files contained here have not been tested in quite awhile so may or may not work and will probably need modification from you the user.  The basic purpose of these scripts is to allow users to register for a web based account of some sort and upon them registering, send them an email to ensure that they provide a valid email address.  The actual account creation occurs once they click the link contained in their email and not before.

There is a sample config file in /lib/config_inc-sample.php that WILL need to be renamed to /lib/config_inc.php before this package has any chance of working.  Be sure to customize that file too!

An explination of files follows:

autoban.php - This file is intended to be run via the shell after editing so as to test email addresses in the database for their validity and send emails out if an address is suspected to be invalid as well as disable that particular account.  Accounts that are valid will have a link sent via email that re-enables the account.  This code probably needs a rewrite and hasn't been tested.

chpass.php - This file is intended to provide users with an interface to either reset their password or change their existing password via a web page.

index.php - Main registration web page.  This is the starting point where all the magic happens.

lib/common_inc.php - Common include files used throughout the project including Pear packages.

lib/config_inc-sample.php - Rename to lib/config_inc.php and customize to suit your needs.

lib/db_inc.php - Include file related to Database operations.  Throughout the package, Pear::DB is the primary Pear database package used.

lib/debug_inc.php - Include this file if you want to turn debugging on.

lib/form_data_inc.php - Main form fields used throughout the package's forms.

lib/form_func2_inc.php - Main functions related to building and processing forms using HTML_QuickForm2.

lib/HTML/QuickForm2/Renderer/Smarty.php - Smarty template renderer I found on the Internet to allow Smarty to mesh well with HTML_QuickForm2.

lib/quickform.js - JavaScript portion of the Smarty <==> HTML_QuickForm2 mesh above.

lib/templates/error.tpl - HTML layout template when something goes severely wrong; used in lib/util_inc.php.

lib/util_inc.php - Various PHP utility functions used throughout the project.  Customize to your needs.

templates/* - Various HTML layouts for the different forms and pages displayed to the user.  Customize to your needs.

templates_c/ - Temporary file directory used to store compiled Smarty pages.  NEEDS TO BE WRITABLE by the web server user.

validate.php - Main web page where account creation happens.  Email links should point to this.

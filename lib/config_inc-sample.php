<?php

//  require_once($_SERVER["DOCUMENT_ROOT"] . "/phpreg/lib/debug_inc.php");
  require_once($_SERVER["DOCUMENT_ROOT"] . "/phpreg/lib/common_inc.php");

//  $GLOBALS["DEBUG"] = TRUE;

  // Database connection settings
  // if we are on the development server connect to the test MySQL database
  $GLOBALS["db_host_read_test"] 	= "localhost";
  $GLOBALS["db_host_write_test"] 	= "localhost";
  $GLOBALS["db_host_read_live"] 	= "localhost";
  $GLOBALS["db_host_write_live"] 	= "localhost";
  if(isset($_SERVER["SERVER_ADDR"]) && $_SERVER["SERVER_ADDR"] == "") {
    $GLOBALS["db_host_read"] 	= $GLOBALS["db_host_read_test"]; 	// Host name for reading from the database
    $GLOBALS["db_host_write"] 	= $GLOBALS["db_host_write_test"]; 	// Host name for writing to the database
  } else {
    $GLOBALS["db_host_read"] 	= $GLOBALS["db_host_read_live"]; 	// Host name for reading from the database
    $GLOBALS["db_host_write"] 	= $GLOBALS["db_host_write_live"]; 	// Host name for writing to the database
  }
  $GLOBALS["db_user"] 		= "db_user"; 			// Database username
  $GLOBALS["db_pass"] 		= "db_pass";			// Database password
  $GLOBALS["db_dbname"] 	= "dbase_name";			// Default database name
  $GLOBALS["db_type"] 		= "mysqli"; 			// Database type

  // If you change any of these tbl_ values, be sure to rename the associated DOCUMENT_ROOT/phpreg/sql/*.sql file and its contents
  $GLOBALS["tbl_signups"]	= "users";			// Signups table
  $GLOBALS["tbl_signup_menu"]	= "users_menu";			// Signup menu table to organize the fields for the above table

  // Organizational settings
  $GLOBALS["org_name_short"]	= "CompanyOrProject";
  $GLOBALS["org_name_long"]	= "CompanyOrProject : CompanyPurpose";
  $GLOBALS["org_email_support"]	= "support@example.com";

  $GLOBALS["homesite"]		= "http://" . $_SERVER["SERVER_NAME"];
  $GLOBALS["imgpath"]		= "/images";			// Used by DOCUMENT_ROOT/lib/randimg.php
  $GLOBALS["verbose_email"]	= FALSE;			// Used in /service/ forms.  If true, choose from below
  $GLOBALS["email_customer"]	= TRUE;				// Whether or not to send an email notification to the customer.
  $GLOBALS["email_tplcust"]	= "email_thanks";		// Email template to use when sending an email to the customer. 
  $GLOBALS["email_template"]	= "email";			// Email template to use:  emaila, emailb, emailc, emaild
  $GLOBALS["email_list"]	= array (			// Email address(es) which should be notified of a Signup.
             "someuser@example.com",
  );

  // GeoIP Database from http://geolite.maxmind.com/download/geoip/database/
  // This is what we will fall back on if a newer one is not available
  // Updates to this database are available on the 1st of every month.
  $GLOBALS["geoip_base"]	= "GeoLiteCity";
  $GLOBALS["geoip"]		= $GLOBALS["geoip_base"] . "_20090101.dat";	// Name format: GeoLiteCity_YYYYMMDD.dat
  $GLOBALS["geodir"]		= $_SERVER["DOCUMENT_ROOT"] . "/phpreg/lib/data";	// Permanent GeoIP database location
  // The following variable could be "/tmp"
  $GLOBALS["geotemp"]		= "/lib/data";	// File extraction location for .gz databases; can be relative to DOCUMENT_ROOT

  $GLOBALS["MasterReset"]	= "admin_reset";

  $GLOBALS["tpl"] = new Smarty();
  $GLOBALS["tpl"]->assign("GLOBALS",$GLOBALS);	// Make this config available in templates
  $GLOBALS["tpl"]->plugins_dir = array("plugins", $_SERVER["DOCUMENT_ROOT"] . "/lib/smarty_plugins");

?>

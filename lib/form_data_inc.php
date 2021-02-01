<?php

  /* Signups Form, List, and Delete data; Make the main code easier to read */

  $run_from_shell = false;
  if (isset($_ENV["USER"])) {		// Was the script run from shell?
    $run_from_shell = true;
    $_SERVER["DOCUMENT_ROOT"] = "/var/www/document_root.com";
  }
//  require_once($_SERVER["DOCUMENT_ROOT"] . "/phpreg/lib/debug_inc.php");
  require_once($_SERVER["DOCUMENT_ROOT"] . "/phpreg/lib/common_inc.php");

  $reglabel = array(
    "login" => "Login:",
    "passwd" => "Password:",
    "repasswd" => "Repeat password:",
    "email" => "Email:",
    "submit" => "Registration",
  );

  $passlabel = array(
    "login" => "Account Name:",
    "oldpass" => "Old Password:",
    "newpass1" => "New password:",
    "newpass2" => "Repeat password:",
    "submit" => "Change Password",
  );

  // Whether or not to show the column in the "show all" view
  $showrcol = array (
    "login" => true,
    "passwd" => false,
    "repasswd" => false,
    "email" => true,
  );

  // Sign_Menu fields
  $signmcol = array (
    "id" => "ID",
    "orig_id" => "Original Order",
    "acct_type" => "Account Type",
    "colname" => "Column Name",
    "description" => "Column Description",
    "order" => "Show Order",
    "show" => "Show/Hide",
  );

  // Whether or not to show the column in the "sign_menu" view
  $showmcol = array (
    "id" => false,
    "orig_id" => true,
    "acct_type" => true,
    "colname" => true,
    "description" => true,
    "order" => true,
    "show" => true,
  );

  $SELECT_BASE = "select_";

?>

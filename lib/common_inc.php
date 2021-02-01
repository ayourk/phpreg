<?php

  $GLOBALS["DEBUG"] = false;
  $run_from_shell = false;
  if (isset($_ENV["USER"])) {		// Was the script run from shell?
    $run_from_shell = true;
    $_SERVER["DOCUMENT_ROOT"] = "/var/www";
  }
  $cur_include_path = ini_get("include_path") . ":" . $_SERVER["DOCUMENT_ROOT"] . "/phpreg:" . $_SERVER["DOCUMENT_ROOT"] . "/phpreg/lib";
  ini_set("include_path", $cur_include_path);

//  require_once($_SERVER["DOCUMENT_ROOT"] . "/phpreg/lib/debug_inc.php");

  require_once("smarty3/Smarty.class.php");
  require_once("DB.php");
  require_once("HTML/QuickForm2.php");
  require_once("HTML/QuickForm2/Renderer/Smarty.php");

  require_once($_SERVER["DOCUMENT_ROOT"] . "/phpreg/lib/db_inc.php");
  require_once($_SERVER["DOCUMENT_ROOT"] . "/phpreg/lib/util_inc.php");
  include_once($_SERVER["DOCUMENT_ROOT"] . "/phpreg/lib/config_inc.php");

?>

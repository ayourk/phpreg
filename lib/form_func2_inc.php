<?php

  $run_from_shell = false;
  if (isset($_ENV["USER"])) {		// Was the script run from shell?
    $run_from_shell = true;
    $_SERVER["DOCUMENT_ROOT"] = "/var/www/document_root.com";
  }

//  require_once($_SERVER["DOCUMENT_ROOT"] . "/phpreg/lib/debug_inc.php");
  require_once($_SERVER["DOCUMENT_ROOT"] . "/phpreg/lib/common_inc.php");
  require_once($_SERVER["DOCUMENT_ROOT"] . "/phpreg/lib/form_data_inc.php");
//  require_once("Net/GeoIP.php");		// PEAR::Net_GeoIP; Geographical region(city,state) of IP accessing site.
//  include_once("File/Archive.php");	// PEAR::File_Archive for automatically extracting Net_GeoIP database

  function chk_oldpass($oldpass, $acctname = NULL) {
    global $qform;

    $old_salt = "0x" . md5($acctname . $oldpass);
    $dbh = db_connect($GLOBALS["db_dbname"], "r");
    $res =& $dbh->query("SELECT " . $dbh->quoteIdentifier("username") . " FROM " . 
      $dbh->quoteIdentifier("users") . " WHERE " . $dbh->quoteIdentifier("password") . 
      " = " . $old_salt . " AND " . $dbh->quoteIdentifier("username") . 
      " = " . $dbh->quoteSmart($acctname));
    if (PEAR::isError($res)) {
      var_export($old_salt);
      $sqlerror = "Database connection error: SQL connect error: " . $res->getUserInfo();
      $GLOBALS["tpl"]->assign("sqlerror", $sqlerror);
      show_error("Database connection error", "SQL connect error: " . $res->getUserInfo());
      exit;
    }
    $retval = ($res->numRows() > 0);
    $dbh->disconnect();
    return $retval;
  }

  function acct_dupe($acctname, $dbconn = null) {
    $dbh = db_connect($GLOBALS["db_dbname"], "r");
    $res =& $dbh->query("SELECT " . $dbh->quoteIdentifier("username") . " FROM " . 
      $dbh->quoteIdentifier("users") . " WHERE " . $dbh->quoteIdentifier("username") . 
      " = " . $dbh->quoteSmart($acctname));
    if (PEAR::isError($res)) {
      $sqlerror = "Database connection error: SQL connect error: " . $res->getUserInfo();
      $GLOBALS["tpl"]->assign("sqlerror", $sqlerror);
      show_error("Database connection error", "SQL connect error: " . $res->getUserInfo());
      exit;
    }
    $retval = ($res->numRows() == 0);
    $dbh->disconnect();
    return $retval;
  }

  function acct_exist($acctname) {
    return (!(acct_dupe($acctname)));
  }

  function process_phpreg_data($values, $banned=0) {
//    global $qform;

    $formtype = "phpreg";
    #encpass = $values["passwd"];
    // Simple encryption/obfustication; use it if you wish (see util_inc.php)
    $encpass = mypass_encrypt($values["passwd"], $values["login"]);

    $dbh = db_connect($GLOBALS["db_dbname"], "w");
    $currip = $_SERVER["REMOTE_ADDR"];
    if (isset($_GET["ip"])) $currip = $_GET["ip"];
    if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
      $currip = $_SERVER["HTTP_X_FORWARDED_FOR"];
//      echo "Account registrations are temporarily disabled."; die();
    }

    $myguid = md5(uniqid(mt_rand(), true));
    // PHP date info only required if database isn't set with proper defaults
    //date_default_timezone_set("US/Central");
    //$nowD = new DateTime();
    //$expireD = clone $nowD;
    //$expireD->modify("+5 days");
    // Use uniqid() instead:  md5(uniqid(mt_rand(), true));

    $sql = "INSERT INTO " . $dbh->quoteIdentifier("userverify") . " (" . $dbh->quoteIdentifier("guid") . ", " . 
//      $dbh->quoteIdentifier("created") . ", " . $dbh->quoteIdentifier("expires") . ", " . 
      $dbh->quoteIdentifier("username") . ", " . 
      $dbh->quoteIdentifier("password") . ", " . $dbh->quoteIdentifier("email") . ", " . 
      $dbh->quoteIdentifier("ip") . ") VALUES (" . 
      $dbh->quoteSmart($myguid) . ", " . 
//      $dbh->quoteSmart($nowD->format("Y-m-d H:i:s")) . ", " . $dbh->quoteSmart($expireD->format("Y-m-d H:i:s")) . ", " . 
      $dbh->quoteSmart($values["login"]) . ", " . $dbh->quoteSmart($encpass) . ", " . 
      $dbh->quoteSmart($values["email"]) . ", " . $dbh->quoteSmart($currip) . ")";

    $res =& $dbh->query($sql);
    chk4dberr($dbh, $res);

    $dbh->disconnect();	// Play nice with database connections.
//    do_render($qform, $GLOBALS["tpl"]);
//    $GLOBALS["tpl"]->assign("service_provider", $GLOBALS["org_name_short"]);
    $GLOBALS["tpl"]->assign("myguid", $myguid);
    send_signup_email($values, array($values["email"]), $GLOBALS["verbose_email"], "valid_email");
//    if ($GLOBALS["email_customer"] && isset($values["email"])) {
//      // Send out email to the [potential] customer.
//      send_signup_email($values, $values["email"], 2, $GLOBALS["email_tplcust"]);
//    }
//    header("Location: /phpreg/thanks.php");		// Redirect away to prevent any accidental submissions
    die($GLOBALS["tpl"]->fetch("thanks2.tpl"));
    return true;
  }

  function process_chpass_data($values) {
      $encpass = $values["newpass1"];
      // Simple encryption/obfustication; use it if you wish (see util_inc.php)
      $encpass = mypass_encrypt($values["newpass1"], $values["login"]);

    $dbh = db_connect($GLOBALS["db_dbname"], "w");
    $new_salt = "0x" . md5($values["login"] . $values["newpass1"]);

    // For Plain Text passwords (considered bad practice):
    $sql  = "UPDATE " . $dbh->quoteIdentifier("users") . 
      " SET " . $dbh->quoteIdentifier("password") . " = " . $dbh->quoteSmart($encpass) . 
      " WHERE " . $dbh->quoteIdentifier("email") . " = " . $dbh->quoteSmart($values["login"]);

    // If you need to use stored procedures for password changes:
    //$sql1 = "CALL changePasswd(" . $dbh->quoteSmart($values["login"]) . ", " . $new_salt . ")";
    //$sql2 = "CALL changePasswd2(" . $dbh->quoteSmart($values["login"]) . ", " . $new_salt . ")";

    $res =& $dbh->query($sql);
    chk4dberr($dbh, $res);

//    $res =& $dbh->query($sql1);
//    chk4dberr($dbh, $res);
//    $res =& $dbh->query($sql2);
//    chk4dberr($dbh, $res);

    $dbh->disconnect();	// Play nice with database connections.
    $GLOBALS["tpl"]->assign("values", $values);
    die($GLOBALS["tpl"]->fetch("thanks4.tpl"));
    return true;
  }

  function build_regform2(&$form, $label=array()) {
    // Set some standard text input attributes
    $text_attr = array();
    $elem = array();

    // Build the Form fields; Name these elements according to the Database Fields.
    $elem["login"] = $form->addElement("text", "login")->setLabel(trim($label["login"]));
    $elem["passwd"] = $form->addElement("password", "passwd")->setLabel(trim($label["passwd"]));
    $elem["repasswd"] = $form->addElement("password", "repasswd")->setLabel(trim($label["repasswd"]));
    $elem["email"] = $form->addElement("text", "email")->setLabel(trim($label["email"]));
    $elem["submit"] = $form->addElement("submit", "submit", array("value" => $label["submit"], "class" => "button"));

    return $elem;
  }

  function build_passform2(&$form, $label=array()) {
    // Set some standard text input attributes
    $text_attr = null;
    $elem = array();

    // Build the Form fields; Name these elements according to the Database Fields.
    $elem["resetpass"] = $form->addElement("hidden", "resetpass", array("value" => 
      isset($_REQUEST["resetpass"]) ? $_REQUEST["resetpass"] : ""));
    $elem["login"] = $form->addElement("text", "login")->setLabel(trim($label["login"]));
    $elem["oldpass"] = $form->addElement("password", "oldpass")->setLabel(trim($label["oldpass"]));
    $elem["newpass1"] = $form->addElement("password", "newpass1")->setLabel(trim($label["newpass1"]));
    $elem["newpass2"] = $form->addElement("password", "newpass2")->setLabel(trim($label["newpass2"]));
    $elem["submit"] = $form->addElement("submit", "submit", array("value" => $label["submit"], "class" => "button"));

    return $elem;
  }

?>

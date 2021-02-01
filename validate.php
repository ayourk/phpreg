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
//  include_once("File/Archive.php");		// PEAR::File_Archive for automatically extracting Net_GeoIP database

  function acct_dupe($acctname, $dbconn = null) {
    $dbh = db_connect($GLOBALS["db_dbname"], "r");
    $res =& $dbh->query("SELECT " . $dbh->quoteIdentifier("name") . " FROM " . 
      $dbh->quoteIdentifier("users") . " WHERE " . $dbh->quoteIdentifier("name") . 
      "=" . $dbh->quoteSmart($acctname));
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

  function process_phpreg_data($uniqueid, $banned=0) {
    global $qform;

    $formtype = "phpreg";

    $dbh = db_connect($GLOBALS["db_dbname"], "w");
    $currip = $_SERVER["REMOTE_ADDR"];
    if (isset($_GET["ip"])) $currip = $_GET["ip"];
    if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
      $currip = $_SERVER["HTTP_X_FORWARDED_FOR"];
//      echo "Account registrations are temporarily disabled."; die();
    }

    $validtpl = "thanks3.tpl";
    $guidfind = "SELECT v.* FROM " . $dbh->quoteIdentifier("userverify") . 
      " AS v " . 
      " LEFT JOIN " . $dbh->quoteIdentifier("users") . 
      " AS u ON u." . $dbh->quoteIdentifier("username") . " = v." . $dbh->quoteIdentifier("username") .
      " WHERE " . $dbh->quoteIdentifier("verified") . " = " . $dbh->quoteSmart(0) . 
      " AND " . $dbh->quoteIdentifier("expires") . " >= NOW() AND " . $dbh->quoteIdentifier("guid") . " = " . $dbh->quoteSmart($uniqueid) . 
      " AND u." . $dbh->quoteIdentifier("username") . " IS NULL";

    if ($banned == 1) {
      $validtpl = "thanks3.tpl";
        $guidfind = "SELECT v.* FROM " . $dbh->quoteIdentifier("userverify") . 
      " AS v " . 
      " LEFT JOIN " . $dbh->quoteIdentifier("users") . 
      " AS u ON u." . $dbh->quoteIdentifier("username") . " = v." . $dbh->quoteIdentifier("username") .
      " WHERE " . $dbh->quoteIdentifier("banned") . " = " . $dbh->quoteSmart(1) . 
      " AND " . $dbh->quoteIdentifier("expires") . 
      " >= NOW() AND " . $dbh->quoteIdentifier("guid") . " = " . $dbh->quoteSmart($uniqueid) . 
      " AND u." . $dbh->quoteIdentifier("username") . " IS NOT NULL";
    }

    $res =& $dbh->query($guidfind);
    chk4dberr($dbh, $res);
    if ($res->numRows() <= 0) {
      die("Sorry, but your registration information was not found or has already been used.");
    }
    $userinfo =& $dbh->getRow($guidfind);
    chk4dberr($dbh, $userinfo);

    if ($banned == 1) {
      $sql = "UPDATE " . $dbh->quoteIdentifier("users") . " SET " . 
        $dbh->quoteIdentifier("banned") . " = " . $dbh->quoteSmart(0) . 
        " WHERE " . $dbh->quoteIdentifier("verifyid") . " = " . $dbh->quoteSmart($userinfo["verifyid"]);
      $res =& $dbh->query($sql);
      chk4dberr($dbh, $res);

    } else {
      $encpass = $userinfo["password"];
      // Simple encryption; use it if you wish
      $encpass = mypass_encrypt($userinfo["password"], $userinfo["name"]);

      $sql = "INSERT INTO " . $dbh->quoteIdentifier("users") . " (" . 
        $dbh->quoteIdentifier("username") . ", " . 
        $dbh->quoteIdentifier("email") . ", " . 
        $dbh->quoteIdentifier("password") . ", " . 
        $dbh->quoteIdentifier("last_ip") . ", " . 
        $dbh->quoteIdentifier("access_level") . ") VALUES (" . 
        $dbh->quoteSmart($userinfo["username"]) . ", " . 
        $dbh->quoteSmart($userinfo["email"]) . ", " . 
        $dbh->quoteSmart($encpass) . ", " . 
        $dbh->quoteSmart($currip) . ", " . $dbh->quoteSmart(1) . ")";

      $res =& $dbh->query($sql);
      chk4dberr($dbh, $res);

      $sql = "SELECT " . $dbh->quoteIdentifier("user_id") . " FROM " . $dbh->quoteIdentifier("users") . 
        " WHERE " . $dbh->quoteIdentifier("username") . " = " . $dbh->quoteSmart($userinfo["username"]);

      $userID =& $dbh->getOne($sql);
      chk4dberr($dbh, $userID);

      $sql = "INSERT INTO " . $dbh->quoteIdentifier("iplog") . " (" . 
        $dbh->quoteIdentifier("userid") . ", " . 
        $dbh->quoteIdentifier("acctname") . ", " . 
        $dbh->quoteIdentifier("ip") . ", " . 
        $dbh->quoteIdentifier("email") . ") VALUES (" . 
        $dbh->quoteSmart($userID) . ", " . 
        $dbh->quoteSmart($userinfo["username"]) . ", " . 
        $dbh->quoteSmart($currip) . ", " . 
        $dbh->quoteSmart($userinfo["email"]) . ")";

      $res =& $dbh->query($sql);
      chk4dberr($dbh, $res);

      $sql = "UPDATE " . $dbh->quoteIdentifier("userverify") . " SET " . 
        $dbh->quoteIdentifier("userid") . " = " . $dbh->quoteSmart($userID) . ", " . 
        $dbh->quoteIdentifier("verified") . " = " . $dbh->quoteSmart(1) . 
        " WHERE " . $dbh->quoteIdentifier("verifyid") . " = " . $dbh->quoteSmart($userinfo["verifyid"]);

      $res =& $dbh->query($sql);
      chk4dberr($dbh, $res);
    }

    $dbh->disconnect();		// Play nice with database connections.
    die($GLOBALS["tpl"]->fetch($validtpl));
    return true;
  }

  $banned = 0;
  if (!isset($_GET["id"]) || empty($_GET["id"])) {
    header("Location: " . $_SERVER["SERVER_NAME"] . "/");
  }
  if (isset($_GET["banned"]) && $_GET["banned"] == "1") {
    $banned=1;
  }
  process_phpreg_data($_GET["id"], $banned);

?>

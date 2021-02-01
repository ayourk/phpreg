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

  function process_phpreg_data($userids) {
    $formtype = "phpreg";

    $dbh = db_connect($GLOBALS["db_dbname"], "w");
    $currip = '';
    if (isset($_GET["ip"])) $currip = $_GET["ip"];
    if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
      $currip = $_SERVER["HTTP_X_FORWARDED_FOR"];
      echo "Account registrations are temporarily disabled."; die();
    }

    $sql = "SELECT id,name,email FROM " . $dbh->quoteIdentifier("users") . 
           " WHERE " . $dbh->quoteIdentifier("id") . " IN (" . implode(",", $userids) . ")";

    $banstr = "Please click on the link provided in your email to unban";

    $userinfo =& $dbh->getAll($sql);
    chk4dberr($dbh, $userinfo);

    foreach ($userinfo as $curuser) {
      $sql = "CALL addForbid(" . $curuser["id"] . ", 100, 999999999, 0x" . bin2hex(mb_convert_encoding($banstr, "UTF-16LE")) . ", 0)";

      $res =& $dbh->query($sql);
      chk4dberr($dbh, $res);

      $myguid = md5(uniqid(mt_rand(), true));
      date_default_timezone_set("US/Central");
      $nowD = new DateTime();
      $expireD = clone $nowD;
      $expireD->modify("+5 days");

      $sql = "INSERT INTO " . $dbh->quoteIdentifier("userverify") . " (" . $dbh->quoteIdentifier("guid") . ", " . 
        $dbh->quoteIdentifier("created") . ", " . $dbh->quoteIdentifier("expires") . ", " . 
        $dbh->quoteIdentifier("userid") . ", " . 
        $dbh->quoteIdentifier("name") . ", " . $dbh->quoteIdentifier("email") . ", " . 
        $dbh->quoteIdentifier("ip") . ", " . $dbh->quoteIdentifier("banned") . ") VALUES (" . 
        $dbh->quoteSmart($myguid) . ", " . $dbh->quoteSmart($nowD->format("Y-m-d H:i:s")) . ", " . 
        $dbh->quoteSmart($expireD->format("Y-m-d H:i:s")) . ", " . $dbh->quoteSmart($curuser["id"]) . ", " . 
        $dbh->quoteSmart($curuser["name"]) . ", " . $dbh->quoteSmart($curuser["email"]) . ", " . 
        $dbh->quoteSmart($currip) . ", " . $dbh->quoteSmart(1) . ")";

      $res =& $dbh->query($sql);
      chk4dberr($dbh, $res);

      $GLOBALS["tpl"]->assign("myguid", $myguid);
      $GLOBALS["tpl"]->assign("userinfo", $curuser);
      send_signup_email($curuser, array($curuser["email"]), $GLOBALS["verbose_email"], "unban_email");
      echo "Banned account " . $curuser["id"] . " : " . $curuser["name"] . " and sent email to " . $curuser["email"] . ".\r\n";
    }
    $dbh->disconnect();	// Play nice with database connections.
    return true;
  }

//  process_phpreg_data(array(32, 512));
  process_phpreg_data(array(32));

?>

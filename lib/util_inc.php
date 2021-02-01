<?php
//  require_once($_SERVER["DOCUMENT_ROOT"] . "/phpreg/lib/debug_inc.php");
  require_once($_SERVER["DOCUMENT_ROOT"] . "/phpreg/lib/common_inc.php");

  function show_error($user_error = "", $debug_error = "") {
    $GLOBALS["tpl"]->assign("user_error", $user_error);
    $GLOBALS["tpl"]->assign("debug_error", $debug_error);
    $GLOBALS["tpl"]->display("file:" . $_SERVER["DOCUMENT_ROOT"] . "/phpreg/lib/templates/" . "error.tpl");
    exit;
  } //show_error

  function mydump(&$dvar, $recursive=false, $tag="pre") {
    if ($tag == "plain") {
      header("Content-type: text/plain");
    } else {
      echo "<" . $tag . ">";
    }
    if ($recursive) {
      var_dump($dvar);
    } else {
      var_export($dvar);
    }
    if ($tag != "plain") {
      echo "</" . $tag . ">\r\n";
    }
  }

  function mypass_encrypt($curpass, $salt="0xFE") {
    // Customize your password encryption scheme here
    $encpass = $encpass = "0x" . md5(strtolower($salt) . $curpasswd);
    return $encpass;
  }

  function send_signup_email($values=array(), $email_list=array(), $verbose=0, $template) {
    global $run_from_shell;
    $mailres="";
    $headers = "";
    $headers .= "From: " . $GLOBALS["org_name_short"] . " <" . $GLOBALS["org_email_support"] . ">\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $GLOBALS["tpl"]->assign("homesite", $GLOBALS["homesite"]);
    if ($verbose) {	// Descriptive email; example: $GLOBALS["email_template"]
      if ($verbose == 1) {
        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
      }
      $mailres = mail(implode(",", $email_list), 
        $GLOBALS["org_name_long"], 
        $GLOBALS["tpl"]->fetch($template . ".tpl"), 
        $headers);
    } else {		// Not so descriptive email
      $mailres = mail(implode(",", $email_list), 
        $GLOBALS["org_name_long"], 
        $GLOBALS["tpl"]->fetch($template . ".tpl"), 
        $headers);
    }
    if ($GLOBALS["DEBUG"]) {
      if (!$mailres) {
        $GLOBALS["tpl"]->assign("sqlerror", "No email sent");
        echo "No email sent";
      } else {
        $GLOBALS["tpl"]->assign("sqlerror", "Email sent successfully!");
        echo "Email sent successfully! " . var_export($mailres, true) . " " . implode(",", $email_list);
      }
    }
  }

  function do_render(&$form, &$smarty) {
    // Render the form for use with Smarty
    HTML_QuickForm2_Renderer::register("smarty", "HTML_QuickForm2_Renderer_Smarty");
    $renderer = HTML_QuickForm2_Renderer::factory("smarty");
    //$renderer =& HTML_QuickForm2_Renderer_ArraySmarty($smarty);
    $renderer->setRequiredTemplate($smarty->fetch("required.tpl"));
    $renderer->setOption("old_compat", true);
    $renderer->setOption("static_labels", true);
    //$renderer->setOption("key_id", true);
    $FormData = $form->render($renderer)->toArray();
    $smarty->assign("regform", $FormData);
    return $renderer;
  }

  function ip2location($ip="") {
    require_once("Net/GeoIP.php");		// PEAR::Net_GeoIP
    include_once("File/Archive.php");	// PEAR::File_Archive for automatically extracting Net_GeoIP database

    $iploc = array();
    $geodata = "";
    $today = getdate();
    $filedate = sprintf("%04d%2d%2d", $today["year"], $today["mon"], $today["mday"]);
    // Let's find a temporary file location for compressed files
    $tempdir = "/tmp";	// Fallback to /tmp
    // First, let's see if there is a temp location relative to DOCUMENT_ROOT
    if (file_exists($_SERVER["DOCUMENT_ROOT"] . $GLOBALS["geotemp"]) && 
        is_dir($_SERVER["DOCUMENT_ROOT"] . $GLOBALS["geotemp"]) && 
        is_writable($_SERVER["DOCUMENT_ROOT"] . $GLOBALS["geotemp"])) {
      $tempdir = $_SERVER["DOCUMENT_ROOT"] . $GLOBALS["geotemp"];
    } else if (file_exists($GLOBALS["geotemp"]) && 
        is_dir($GLOBALS["geotemp"]) && 
        is_writable($GLOBALS["geotemp"])) {
      $tempdir = $GLOBALS["geotemp"];
    } else if (file_exists($_SERVER["DOCUMENT_ROOT"] . $tempdir) && 
        is_dir($_SERVER["DOCUMENT_ROOT"] . $tempdir) && 
        is_writable($_SERVER["DOCUMENT_ROOT"] . $tempdir)) {
      // Site local temporary directory
      $tempdir = $_SERVER["DOCUMENT_ROOT"] . $tempdir;
    }
    if (file_exists($GLOBALS["geodir"] . "/" . 
        $GLOBALS["geoip_base"] . "_" . $filedate . ".dat")) {
      $geodata = $GLOBALS["geodir"] . "/" . $GLOBALS["geoip_base"] . "_" . $filedate . ".dat";
    } else if (file_exists($GLOBALS["geodir"] . 
        $GLOBALS["geoip_base"] . ".dat")) {
      $geodata = $GLOBALS["geodir"] . "/" . $GLOBALS["geoip_base"] . ".dat";
    } else if (file_exists($GLOBALS["geodir"] . "/" . 
        $GLOBALS["geoip_base"] . "_" . $filedate . ".dat.gz") &&
        file_exists($tempdir) && is_dir($tempdir) && is_writable($tempdir) &&
        extension_loaded("zlib") &&
        class_exists("File_Archive", false)) {
      $geodata = $GLOBALS["geoip_base"] . "_" . $filedate . ".dat";
      $geodatatmp = $tempdir . "/";
      $geodatasrc = $GLOBALS["geodir"] . "/" . $geodata . ".gz";
      $arc = new File_Archive();
      $res = $arc->extract($arc->read($geodatasrc."/"), $arc->toFiles($geodatatmp));
      if (PEAR::isError($res)) {
        $geodata = $GLOBALS["geodir"] . "/" . $GLOBALS["geoip"];
      } else {
        $geodata = $geodatatmp . $geodata;
      }
    } else if (file_exists($GLOBALS["geodir"] . "/" . 
        $GLOBALS["geoip_base"] . ".dat.gz") &&
        file_exists($tempdir) && is_dir($tempdir) && is_writable($tempdir) &&
        extension_loaded("zlib") &&
        class_exists("File_Archive", false)) {
      $geodata = $GLOBALS["geoip_base"] . ".dat";
      $geodatatmp = $tempdir . "/";
      $geodatasrc = $GLOBALS["geodir"] . "/" . $geodata . ".gz";
      $arc = new File_Archive();
      $res = $arc->extract($arc->read($geodatasrc."/"), $arc->toFiles($geodatatmp));
      if (PEAR::isError($res)) {
        $geodata = $GLOBALS["geodir"] . "/" . $GLOBALS["geoip"];
      } else {
        $geodata = $geodatatmp . $geodata;
      }
    } else {
      $geodata = $GLOBALS["geodir"] . "/" . $GLOBALS["geoip"];
    }

    try {
      $geoip = Net_GeoIP::getInstance($geodata);
      $location = $geoip->lookupLocation($ip);
      $iploc = get_object_vars($location);
    } catch (Exception $e) {
      // Handle Exception
    }
    return $iploc;
  }

  function utf8_to_unicode( $str ) {
     
    $unicode = array();       
    $values = array();
    $lookingFor = 1;
       
    for ($i = 0; $i < strlen( $str ); $i++ ) {
      $thisValue = ord( $str[ $i ] );
      if ( $thisValue < ord('A') ) {
        // exclude 0-9
        if ($thisValue >= ord('0') && $thisValue <= ord('9')) {
          // number
          $unicode[] = chr($thisValue);
        } else {
          $unicode[] = '%'.dechex($thisValue);
        }
      } else {
        if ( $thisValue < 128)
          $unicode[] = $str[ $i ];
        else {
          if ( count( $values ) == 0 ) $lookingFor = ( $thisValue < 224 ) ? 2 : 3;               
          $values[] = $thisValue;               
          if ( count( $values ) == $lookingFor ) {
            $number = ( $lookingFor == 3 ) ?
              ( ( $values[0] % 16 ) * 4096 ) + ( ( $values[1] % 64 ) * 64 ) + ( $values[2] % 64 ):
              ( ( $values[0] % 32 ) * 64 ) + ( $values[1] % 64 );
            $number = dechex($number);
            $unicode[] = (strlen($number)==3)?"%u0".$number:"%u".$number;
            $values = array();
            $lookingFor = 1;
          } // if
        } // if
      } // else
    } // for
    return implode("",$unicode);
  } // utf8_to_unicode

?>

<?php

  $run_from_shell = false;
  if (isset($_ENV["USER"])) {		// Was the script run from shell?
    $run_from_shell = true;
    $_SERVER["DOCUMENT_ROOT"] = "/var/www/document_root.com";
  }

  //require_once($_SERVER["DOCUMENT_ROOT"] . "/phpreg/lib/debug_inc.php");
  require_once($_SERVER["DOCUMENT_ROOT"] . "/phpreg/lib/common_inc.php");

  $curraction = "/phpreg/index.php";
  if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
    $curraction = "/phpreg/index.php";
//    echo "Account registrations are temporarily disabled."; die();
  }

  // Local variables
  $regform = new HTML_QuickForm2("regform", "post", $curraction, "", NULL, true);
  $qform =& $regform;
  $elem = array();
  // Make the code easier to read by putting arrays and functions in an include
  require_once($_SERVER["DOCUMENT_ROOT"] . "/phpreg/lib/form_data_inc.php");
  require_once($_SERVER["DOCUMENT_ROOT"] . "/phpreg/lib/form_func2_inc.php");

  function build_validation(&$form, $client_validate=HTML_QuickForm2_Rule::CLIENT_SERVER) {
    $elem =& $GLOBALS["elem"];

    if (!is_object($form)) return false;
    $required_fields = array (
      "login" => "Login cannot be empty",
      "passwd" => "Password cannot be empty",
      "repasswd" => "Password cannot be empty",
      "email" => "Email Address cannot be empty",
    );
    // Run all required fields through trim() to ensure they won't be blank
    $form->addRecursiveFilter("trim");
    foreach ($required_fields as $rfield => $rmesg) {
      $elem[$rfield]->addRule("required", $rmesg, $client_validate);
    }

    $elem["login"]->addRule("callback", "Login name already exists.", "acct_dupe");
    $elem["passwd"]->addRule("eq", "Passwords do not match.", $elem["repasswd"], null, $client_validate);
    //$form->addRule(array("passwd", "repasswd"), "Passwords do not match.", "compare", null, $client_validate);

    $validchars_regex = '/^[A-Za-z0-9_-]+$/';
    $elem["login"]->addRule("regex", "Login name has an incorrect format.", $validchars_regex, $client_validate);
    //$elem["passwd"]->addRule("regex", "Password has an incorrect format.", $validchars_regex, $client_validate);

    $elem["login"]->addRule("minlength", "Login name is too short(4).", 4, $client_validate);
    $elem["passwd"]->addRule("minlength", "Password is too short(8).", 8, $client_validate);

    $elem["login"]->addRule("maxlength", "Login name is too long(100).", 100, $client_validate);
    $elem["passwd"]->addRule("maxlength", "Password is too long(100).", 100, $client_validate);

    $elem["email"]->addRule("regex", "Email address has an incorrect format.", 
      '/^([a-z0-9])(([-a-z0-9._])*([a-z0-9]))*\@([a-z0-9])(([a-z0-9-])*' .
      '([a-z0-9]))+(\.([a-z0-9])([-a-z0-9_-])?([a-z0-9])+)+$/i', $client_validate);
    $elem["email"]->addRule("email", "Email address is still not valid", true, $client_validate);
  }

  // Do the work of the main functions: void main() { ... }
  $elem = build_regform2($regform, $reglabel);
  build_validation($regform);
  $formtype = "phpreg";

  if ($regform->isSubmitted()) {
    if ($regform->validate()) {
      $regform->toggleFrozen();
      process_phpreg_data($regform->getValue());  //$regform->process("process_phpreg_data", false);	// ...(..., mergeFiles = false);
    } else {
      $GLOBALS["tpl"]->assign("blanket_err", "There was an error processing your registration, please review the items marked in red below and correct before submitting again.");
    }
  }

  do_render($regform, $GLOBALS["tpl"]);

  // Assign needed template variables

  // Now to print out the formatted page
  $GLOBALS["tpl"]->display($formtype . ".tpl");
?>

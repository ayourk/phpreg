<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{$PAGE_TITLE} - Error</title>
</head>
<body>
    <span class="bold">Error</span>
    <br />
    <br />
      Error: {$user_error}<br />
      {if $is_administrator eq "1" or true}<br />Debug: <code>{$debug_error}</code><br />{/if}
     <br />
     <a href="#" onClick="history.back();return false" class="backButton">Go Back</a>
</body>
</html>

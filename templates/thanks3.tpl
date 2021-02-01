<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{$GLOBALS.org_name_short} : Validation completed; enjoy your stay!</title>
{if file_exists(("`$smarty.server.DOCUMENT_ROOT`/assets/style.css"))}
<link href="/assets/style.css" rel="stylesheet" type="text/css" />
{/if}
</head>
<body>
<h1>Thank you for validating your email address.</h1>
	<p><font size="+1">You may log into the site now.</font></p>
  <a href="http://{$smarty.server.SERVER_NAME}/">Continue to site</a>
  <!-- a href="http://{$smarty.server.SERVER_NAME}{pathinfo($smarty.server.SCRIPT_NAME, PATHINFO_DIRNAME)}/">Continue to site</a -->
</body>
</html>

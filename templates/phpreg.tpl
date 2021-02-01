<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{$GLOBALS.org_name_short} : Sign up for access</title>
{* JSCalendar *}{if $regform.trigger.html}{* dhtml_calendar_init path='/includes/js/jscalendar-1.0/' css="calendar-green.css" *}{/if}
{if file_exists(("`$smarty.server.DOCUMENT_ROOT`/assets/style.css"))}
<link href="/assets/style.css" rel="stylesheet" type="text/css" />
{/if}
{if isset($QuickForm2)}
<script type="text/javascript" src="/lib/quickform.js"></script>
{/if}
</head>
<body>
{if $regform.javascript}
{$regform.javascript}
{/if}
<form {$regform.attributes}>
{$regform.hidden}
{$regform.login.label}<br />
{$regform.login.html}<br /><br />
{$regform.passwd.label}<br />
{$regform.passwd.html}<br /><br />
{$regform.repasswd.label}<br />
{$regform.repasswd.html}<br /><br />
{$regform.email.label}<br />
{$regform.email.html}<br /><br />
{$regform.submit.html}
{if $regform.requirednote}
<br /><br />
{foreach name=eacherr from=$regform.errors item=curerr key=rowid}
<span class="formerr" id="err{$rowid}" style="color:#FF0000;"><font size=\"2\">{$curerr}</font></span><br />
{/foreach}
{$regform.requirednote}<br /><br />
{/if}
</form>
</body>

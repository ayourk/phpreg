The account of {$userinfo.username} with email address of {$userinfo.email} is suspected of having an invalid email address.

The validation link below will expire in 5 days.  Your account will not be unbanned until you click on a validation link.  Clicking on the validation link means you have read and agree to our terms and conditions as listed here:

http://{$smarty.server.SERVER_NAME}/terms/

Please click on the following link to unban your account:

http://{$smarty.server.SERVER_NAME}{pathinfo($smarty.server.SCRIPT_NAME, PATHINFO_DIRNAME)}/validate.php?banned=1&id={$myguid}

Thank you
{$GLOBALS.org_name_short} Administration

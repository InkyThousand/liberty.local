<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head><meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<title>Redirection page</title>
<link rel="stylesheet" href="<?php echo Site::themeRoot() ?>/css/content.css" type="text/css" />
<style>
html
{
  height: 100%;
}

body
{
  height: 100%;
  margin: 0px;
  padding: 0px;
  background-color: #fff;
  font-family: verdana,tahoma,sans-serif;
  font-size: 12px;
  line-height: 16px;
}

table
{
  width: 100%;
  height: 100%;
  border: 0px;
}

tr
{
  vertical-align: middle;
}

td
{
  text-align: center;
}
</style>
<script>
function redir()
{
  document.forms[0].submit();
}
</script>
</head>
<body>
<form method="post" action="<?php echo $redirect_url ?>">
<input type="hidden" name="sysname" value="<?php echo urlencode($sysname) ?>"/>
<input type="hidden" name="<?php echo urlencode($passkey_name) ?>" value="<?php echo urlencode($passkey_value) ?>"/>
<input type="hidden" name="company" value="<?php echo urlencode($company) ?>"/>
<input type="hidden" name="user_id" value="<?php echo urlencode($user_id) ?>"/>
<input type="hidden" name="dn" value="<?php echo urlencode($dn) ?>"/>
<input type="hidden" name="de" value="<?php echo urlencode($de) ?>"/>
<input type="hidden" name="sendconfirmation" value="<?php echo urlencode($sendconfirmation) ?>"/>
<input type="hidden" name="sendnotification" value="<?php echo urlencode($sendnotification) ?>"/>
</form>
<table>
<tr>
 <td>
     <p>Please wait</p>
     <img src="<?php echo Site::themeRoot() ?>/images/loading.gif" alt="Loading..." width="66" height="66" onload="setTimeout(redir, 2000)"/>
     <p>You will be redirected to Online Parts Store...</p>
 </td>
</tr>
</table>
</body>
</html>
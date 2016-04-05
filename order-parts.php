<?php
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', rtrim(dirname(__FILE__), '\\/'));
define('BACKEND', false);
define('MONSTRA_ACCESS', true);
require_once ROOT. DS . 'engine'. DS . '_init.php';

$params = Dealership::getDealershipLinkParams();

?>
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
  background-color: #FFF;
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
<form method="get" action="<?php echo $params['url'] ?>">
<input type="hidden" name="sysname" value="<?php echo urlencode($params['sysname']) ?>"/>
<input type="hidden" name="<?php echo urlencode($params['passkey_name']) ?>" value="<?php echo urlencode($params['passkey_value']) ?>"/>
<input type="hidden" name="company" value="<?php echo urlencode($params['company']) ?>"/>
<input type="hidden" name="doorback" value="<?php echo urlencode($params['doorback']) ?>"/>
<input type="hidden" name="sendconfirmation" value="<?php echo urlencode($params['sendconfirmation']) ?>"/>
<input type="hidden" name="sendnotification" value="<?php echo urlencode($params['sendnotification']) ?>"/>
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
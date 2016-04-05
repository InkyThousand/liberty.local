<div id="content-title"><h2>Distributor Login</h2></div>
<div id="content-section" align="center">
<?php
 if ($error)
 {
?>
<div class="page_error" style="text-align:center"><br><?php echo Html::toText($error) ?><br><br></div>
<?php 
 }
?>
<form method="post">
<input type="hidden" name="action" value="dealer-login">
<table>
<tr>
 <td>Email Address/Username:</td>
 <td><input type="text" name="user_id" value="" autocomplete="off" style="width:250px;border:1px black solid"></td>
</tr>
<tr>
 <td>Password:</td>
 <td><input type="password" name="password" value="" autocomplete="off" style="width:250px;border:1px black solid"></td>
</tr>
<tr align="center">
 <td colspan="2"><a href="restore-password">Forgot password?</a></td>
</tr>
<tr align="center">
 <td colspan="2">
  <br>
  <div style="float:left"><input class="distributorButton" type="submit" value="Login"></div>
  <div style="float:left;width:274px;padding-top:5px;text-align:center"><a href="https://parts.libertypumps.com/signup/">Request Access</a></div>
  <div style="float:right"><input class="distributorButton" type="reset" value="Clear"></div>
 </td>
</tr>
</table>
</form>
</div>
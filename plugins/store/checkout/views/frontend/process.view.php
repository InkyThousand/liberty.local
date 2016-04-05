<div id="content-title"><h2>Pending Orders</h2></div>
<br/>
<div id="content-section">
<?php
 if (!$error)
 {
?>
<p>Please wait, you will be redirected to the Online Order Checkout page.</p>
<p>If your browser does not support automatic redirection please follow <a href="<?php echo $redirectURL ?>">this link</a>.</p>
<?php
   Request::redirect($redirectURL, 302, 2000, false);
 }
 else
 {
?>
<p>Error processing order data. The error message is:</p>
<p class="page_error"><?php echo Html::toText($error) ?></p>
<p>Please contact our support for further assistance.</p>
<form action="https://www.libertypumps.com/ContactUs/">
<input class="submitButton" type="submit" value="Contact LibertyPumps"/>
</form>
<?php
 }
?>
</div>
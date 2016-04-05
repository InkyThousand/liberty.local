<div id="content-title"><h2>Order Canceled</h2></div>
<br/>
<div id="content-section">
<?php
 if (!$error)
 {
?>
<h1>Order Canceled</h1>
<p>You have canceled your payment process. Wherever you still can complete your pending order again
from the begining with Pending Orders menu chapter on left.</p>
<p>
If you have questions, or would like to change or check on your order, please call our 
On-line Support Line at: 1-800-543-2550. Representatives are available to assist you with your 
online order Monday - Friday, 8AM - 5PM Eastern Standard Time.
</p>
<p>
Please feel free to visit the website at any time to place additional orders.
</p>
<p>
Thank you!
<p/>
<?php
 }
 else
 {
?>
<p class="page_error"><?php echo Html::toText($error)?></p>
<form method="get">
<input type="hidden" name="token" value="<?php echo Html::toText($token) ?>"/>
<input class="submitButton" type="submit" value="Try again">
</form>
<?php
 }
?>
</div>
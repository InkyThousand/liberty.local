<div id="content-title"><h2>Online Order Checkout</h2></div>
<div id="content-section">
<?php
 if ($error)
 {
   if ($paypal_init_error)
   {
?>
<p class="page_error"><?php echo Html::toText($error)?></p>
<p>Please try again.</p>
<form method="get">
<input type="hidden" name="id" value="<?php echo Html::toText($payload_id)?>">
<input type="hidden" name="step" value="<?php echo Html::toText($step)?>">
<input type="hidden" name="shipping_type" value="<?php echo Html::toText($shipping_type)?>">
<?php
     if (Valid::hasValue($shipping_method))
     {
?>
<input type="hidden" name="shipping_method" value="<?php echo Html::toText($shipping_method)?>">
<?php
     }
     if (Valid::hasValue($shipping_instructions))
     {
?>
<input type="hidden" name="shipping_instructions" value="<?php echo Html::toText($shipping_instructions)?>">
<?php
     }
?>
<input type="hidden" name="payment_type" value="<?php echo Html::toText($payment_type)?>">
<input class="submitButton" type="submit" value="Try again">
</form>
<?php
   }
   else if ($incorrect_shipping_details)
   {
     $redirectURL = Site::url() . 'orders/change-shipping-details?id=' . urlencode($payload_id);
?>
<p>Warning! You need to provide correct shipping information in order to complete your request.</p>
<p>Now you will be redirected to the page where you can change your shipping details.
If your browser does not support automatic redirection please follow <a href="<?php echo $redirectURL ?>">this link</a>.</p>
<?php
     Request::redirect($redirectURL, 302, 3000, false);
   }
   else
   {
?>
<p class="page_error"><?php echo Html::toText($error)?></p>
<?php
   }
 }
 else
 {
   if ($step == 1)
   {
?>
<p>Shipping Company</p>
<form method="get">
<input type="hidden" name="id" value="<?php echo Html::toText($payload_id)?>">
<input type="hidden" name="step" value="<?php echo Html::toText($next_step)?>">
<div style="width:530px;height:230px">
<p>Please choose shipping company:</p>
<ul class="shipping_types">
<?php
     foreach ($shipping_types_allowed as $shipping_type_allowed)
     {
?>  
<li><input type="radio" id="shipping_<?php echo $shipping_type_allowed?>" name="shipping_type" value="<?php echo $shipping_type_allowed ?>" <?php echo $shipping_type == $shipping_type_allowed ? 'checked="checked"' : null ?>>&nbsp;<label for="shipping_<?php echo $shipping_type_allowed?>"><?php echo $shipping_type_labels[$shipping_type_allowed] ?></label></li>
<?php
     }
?>
</ul>
</div>
<div style="position:relative;top:18px;left:455px;width:70px"><input class="submitButton" type="submit" value="Next" style="width:70px"></div>
</form>
<?php
   }

       if ($step == 2)
       {
         $rate_result = null;
         $rates_error = false;
         $rates_error_message = null;

         try
         {                           
           $rate_result = Shipping::getUPSRates($pending_order_id);

           if (count($rate_result) == 0)
           {
             throw new Exception();
           }
         }
         catch (Exception $e)
         {
           $rates_error = true;
           $rates_error_message = $e->getMessage();
         }
?>
<p>Choose Shipping Method</p>
<form method="get">
<input type="hidden" name="id" value="<?php echo Html::toText($payload_id)?>">
<input type="hidden" name="step" value="<?php echo Html::toText($next_step)?>">
<input type="hidden" name="shipping_type" value="<?php echo Html::toText($shipping_type)?>">
<?php
         if ($rates_error)
         {
           if ($rates_error_message)
           {
?>
<input type="hidden" name="shipping_type" value="<?php echo Html::toText($shipping_type)?>">
<?php
           }
           else
           {
?>
<input type="hidden" name="shipping_type" value="none">
<?php   
           }
         }

         if (count($rate_result) > 0)
         {
?>
<div style="width:530px;height:230px">
 <table width="100%" cellpadding="0" cellspacing="0" border="0">
 <tr>
  <td bgcolor="#8C8A5A">
    <table width="100%" cellpadding="5" cellspacing="1" border="0">
    <tr>
     <td bgcolor="#9C9A63" width="1%">&nbsp;</td>
     <td bgcolor="#9C9A63" width="97%"><b>Shipping Method</b></td>
     <td bgcolor="#9C9A63" width="1%" style="text-align:center"><b>Rate</b></td>
     <td bgcolor="#9C9A63" width="1%" style="text-align:center"><b>Days to Delivery</b></td>
    </tr>
<?php
           foreach ($rate_result as $name => $rate)
           {
             if (strpos(strtolower(strip_tags($name)), 'next day') !== false)
             {
               $hour = (int)date('G');
               $day_of_week = (int)date('w');

               if ($day_of_week > 0 && $day_of_week < 6)
               {
                 if ($hour >= 12)
                 {
                   continue;
                 }
               }
               else
               {
                 continue;
               }
             }

             $service_value = strip_tags($name);
?>
    <tr>
      <td bgcolor="#D6D7B5"><input type="radio" name="shipping_method" value="<?php echo Html::toText($service_value) ?>" <?php if (strtolower($service_value) == strtolower($shipping_method)) echo 'checked="checked"'?>></td>
      <td bgcolor="#D6D7B5"><?php echo $name ?></td>
      <td bgcolor="#D6D7B5" style="white-space:nowrap;text-align:center"><?php echo Html::toText($rate['currency']) . Html::toText($rate['rate']) ?></td>
      <td bgcolor="#D6D7B5" style="white-space:nowrap;text-align:center"><?php echo Html::toText($rate['days_to_delivery']) ?></td>
    </tr>
<?php
           }
?>
    </table>
  </td>
 </tr>
 </table>
</div>
<div style="position:relative;top:18px;left:455px;width:70px"><input class="submitButton" type="submit" value="Next" style="width:70px"></div>
</form>
<form method="get">
<input type="hidden" name="id" value="<?php echo Html::toText($payload_id)?>">
<input type="hidden" name="step" value="<?php echo Html::toText($previous_step)?>">
<input type="hidden" name="shipping_type" value="<?php echo Html::toText($shipping_type)?>">
<div style="position:relative;top:-1px;width:70px"><input class="submitButton" type="submit" value="Previous" style="width:70px"></div>
</form>
<?php
         }

         if ($rates_error)
         {
           if (Valid::hasValue($rates_error_message))
           {
?>
<p>UPS Realtime shipping rate calculation service returned the following error:</p>
<p class="page_error"><?php echo Html::toText($rates_error_message)?></p>
<?php
           }
?>
</form>
<p>Unfortunately, we are unable to provide you with a shipping cost estimate for this order.</p>
<p>Please make sure you have correctly specified your shipping details. Click on the following button to verify.</p>
<form method="get" action="<?php echo Site::root()?>/orders/change-shipping-details">
<input type="hidden" name="id" value="<?php echo urlencode($payload_id)?>">
<input class="submitButton" type="submit" value="Change Shipping Details"/>
</form>
<p>If shipping details are correct then you should contact our support for further assistance on completing your order.</p>
<form method="get" action="https://www.libertypumps.com/ContactUs/">
<input class="submitButton" type="submit" value="Contact Liberty Pumps"/>
</form>
<p></p>
<form method="get">
<input type="hidden" name="id" value="<?php echo Html::toText($payload_id)?>">
<input type="hidden" name="step" value="<?php echo Html::toText($step)?>">
<input type="hidden" name="shipping_type" value="<?php echo Html::toText($shipping_type)?>">
<div style="position:relative;top:13px;left:455px;width:70px"><input class="submitButton" type="submit" value="Try Again" style="width:70px"></div>
</form>
<form method="get">
<input type="hidden" name="id" value="<?php echo Html::toText($payload_id)?>">
<input type="hidden" name="step" value="<?php echo Html::toText($previous_step)?>">
<input type="hidden" name="shipping_type" value="<?php echo Html::toText($shipping_type)?>">
<div style="position:relative;top:-6px;width:70px"><input class="submitButton" type="submit" value="Previous" style="width:70px"></div>
</form>
<?php
         }
       }
       if ($step == 3)
       {
?>
<p>Shipping Instructions</p>
<form method="get">
<input type="hidden" name="id" value="<?php echo Html::toText($payload_id)?>">
<input type="hidden" name="step" value="<?php echo Html::toText($next_step)?>">
<input type="hidden" name="shipping_type" value="<?php echo Html::toText($shipping_type)?>">
<input type="hidden" name="shipping_method" value="<?php echo Html::toText($shipping_method)?>">
<div style="width:530px;height:230px">
<p>Please type in any shipping instructions:</p>
<p><textarea id="shipping_instructions" name="shipping_instructions" style="width:520px;height:180px;border:1px black solid"><?php echo Html::toText($shipping_instructions)?></textarea></p>
</div>
<div style="position:relative;top:18px;left:455px;width:70px"><input class="submitButton" type="submit" value="Next" style="width:70px"></div>
</form>
<form method="get">
<input type="hidden" name="id" value="<?php echo Html::toText($payload_id)?>">
<input type="hidden" name="step" value="<?php echo Html::toText($previous_step)?>">
<input type="hidden" name="shipping_type" value="<?php echo Html::toText($shipping_type)?>">
<input type="hidden" name="shipping_method" value="<?php echo Html::toText($shipping_method)?>">
<div style="position:relative;top:-6px;width:70px"><input class="submitButton" type="submit" value="Previous" style="width:70px"></div>
</form>
<script>
document.getElementById('shipping_instructions').focus();
</script>
<?php
       }

       if ($step == 4)
       {
?>
<p>Payment Method</p>
<form method="get">
<input type="hidden" name="id" value="<?php echo Html::toText($payload_id)?>">
<input type="hidden" name="step" value="<?php echo Html::toText($next_step)?>">
<input type="hidden" name="shipping_type" value="<?php echo Html::toText($shipping_type)?>">
<?php
         if (Valid::hasValue($shipping_method))
         {
?>
<input type="hidden" name="shipping_method" value="<?php echo Html::toText($shipping_method)?>">
<?php
         }
         if (Valid::hasValue($shipping_instructions))
         {
?>
<input type="hidden" name="shipping_instructions" value="<?php echo Html::toText($shipping_instructions)?>">
<?php
         }
?>
<div style="width:530px;height:230px">
 <p>Choose prefered payment method</p>
 <div style="padding-left:25px">
  <table border="0" cellpadding="0" cellspacing="15">
  <tr>
   <td><input type="radio" id="payment_paypal" name="payment_type" value="paypal"<?php if ($payment_type == 'paypal') echo ' checked="checked"'?>></td>
   <td><img src="https://www.paypal.com/en_US/i/logo/PayPal_mark_37x23.gif" width="37" height="23" align="left" style="margin-right:7px;"><span>The safer, easier way to pay.</span></td>
  </tr>
  <tr>
   <td><input type="radio" id="payment_creditcard" name="payment_type" value="creditcard"<?php if ($payment_type == 'creditcard') echo ' checked="checked"'?>></td>
   <td><label for="payment_creditcard">Credit Card</label></td>
  </tr>
  </table>
 </div>
</div>
<div style="position:relative;top:18px;left:455px;width:70px"><input class="submitButton" type="submit" value="Next" style="width:70px"></div>
</form>
<form method="get">
<input type="hidden" name="id" value="<?php echo Html::toText($payload_id)?>">
<input type="hidden" name="step" value="<?php echo Html::toText($previous_step)?>">
<input type="hidden" name="shipping_type" value="<?php echo Html::toText($shipping_type)?>">
<?php
         if (Valid::hasValue($shipping_method))
         {
?>
<input type="hidden" name="shipping_method" value="<?php echo Html::toText($shipping_method)?>">
<?php
         }
         if (Valid::hasValue($shipping_instructions))
         {
?>
<input type="hidden" name="shipping_instructions" value="<?php echo Html::toText($shipping_instructions)?>">
<?php
         }
?>
<div style="position:relative;top:-6px;width:70px"><input class="submitButton" type="submit" value="Previous" style="width:70px"></div>
</form>
<?php
       }

       if ($step == 5)
       {
?>
<p>Redirecting to PayPal website...</p>
<p>If your browser does not support automatic redirection please follow <a href="<?php echo $redirectURL ?>">this link</a>.</p>
<?php
         Request::redirect($redirectURL, 302, 0, false);
       }
 }
?>
</div>
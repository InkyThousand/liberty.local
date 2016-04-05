<div id="content-title"><h2>Order Details</h2></div>
<br/>
<div id="content-section">
<?php
 if (Session::get('shipping_details_updated'))
 {
   Session::set('shipping_details_updated', null);
?>
<br/>
<div class="page_error" style="text-align:center">Shipping details was successfully updated</div>
<br/>
<?php
 }
 if (!$error)
 {
?>
<div>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
 <td bgcolor="#8C8A5A">
    <table width="100%" cellpadding="3" cellspacing="1" border="0" style="font-family: Arial, Helvetica, sans-serif;font-size:12px">
    <tr>
        <td bgcolor="#9C9A63" width="1%"><b>No.</b></td>
        <td bgcolor="#9C9A63" width="1%"><b>Part #</b></td>
        <td bgcolor="#9C9A63" width="94%"><b>Description</b></td>
        <td bgcolor="#9C9A63" width="1%"><b>Qty</b></td>
        <td bgcolor="#9C9A63" width="1%" style="text-align:center"><b>Price</b></td>
        <td bgcolor="#9C9A63" width="1%" style="text-align:center"><b>Total&nbsp;Price</b></td>
        <td bgcolor="#9C9A63" width="1%" style="text-align:center"><b>Weight, lbs.</b></td>
        <td bgcolor="#9C9A63" width="1%"><b>Action</b></td>
    </tr>
<?php
   $c = 0;
   $total_weight = 0;

   foreach ($details['order_items'] as $item)
   {
     $c++;
     $part_no = $item['code'];
     $description = $item['description'];
     $quantity = $item['quantity'];
     $price = (double)$item['price'];
     $total_price = (double)$item['price_total'];
     $total_weight += $item['weight'];
?>
    <tr style="vertical-align:top">
        <td bgcolor="#D6D7B5" style="text-align:right"><?php echo Html::toText($c)?></td>
        <td bgcolor="#D6D7B5"><?php echo Html::toText($part_no)?></td>
        <td bgcolor="#D6D7B5"><?php echo Html::toText($description)?></td>
        <td bgcolor="#D6D7B5" style="text-align:center"><?php echo Html::toText($quantity)?></td>
        <td bgcolor="#D6D7B5" style="text-align:right"><?php echo Html::toText($details['currency'] . sprintf('%.2f', $price))?></td>
        <td bgcolor="#D6D7B5" style="text-align:right"><?php echo Html::toText($details['currency'] . sprintf('%.2f', $total_price))?></td>
        <td bgcolor="#D6D7B5" style="text-align:right"><?php echo Html::toText(sprintf('%.2f', $item['weight']))?></td>
        <td bgcolor="#D6D7B5" style="text-align:center"><a href="?id=<?php echo Html::toText($payload_id)?>&amp;part=<?php echo urlencode($part_no)?>&amp;action=remove&amp;token=<?php echo Security::token() ?>" onclick="return confirmRemove('Remove item no. <?php echo $part_no ?>')" title="Remove"><img src="<?php echo Site::themeRoot() ?>/images/buttons/btn-delete.gif" title="Remove"></a></td>
    </tr>
<?php
   }
?>
    <tr>
      <td colspan="9" bgcolor="#D6D7B5" align="right">
       <div style="font-family: Arial, Helvetica, sans-serif;font-size:12px">
        <b>Total weight:</b>&nbsp;<?php echo Html::toText(sprintf('%.2f', $total_weight))?>&nbsp;lbs.
       </div>

       <div style="font-family: Arial, Helvetica, sans-serif;font-size:12px">
        <b>Sub total:</b>&nbsp;<?php echo Html::toText($details['currency']) . Html::toText(sprintf('%.2f', $details['subtotal']))?>
       </div>
<?php
   if ($details['handling'] > 0)
   {
?>
       <div style="font-family: Arial, Helvetica, sans-serif;font-size:12px">
        <b>Handling:</b>&nbsp;<?php echo Html::toText($details['currency']) . Html::toText(sprintf('%.2f', $details['handling']))?>
       </div>
<?php
   }
   if ($details['shipping_tax'] > 0)
   {
?>
       <div style="font-family: Arial, Helvetica, sans-serif;font-size:12px">
        <b><?php echo Html::toText($details['shipping_tax_description']) ?>:</b>&nbsp;<?php echo Html::toText($details['currency']) . Html::toText(sprintf('%.2f', $details['shipping_tax']))?>
       </div>
<?php
   }

   $total_price = $details['subtotal'] + $details['handling'] + $details['shipping_tax'];
?>
       <div style="font-family: Arial, Helvetica, sans-serif;font-size:12px">
        <b>Total:</b>&nbsp;<?php echo Html::toText($details['currency']) . Html::toText(sprintf('%.2f', $total_price))?>
       </div>
      </td>
    </tr>
    </table>
 </td>
</tr>
</table>
<?php
   if ($details['checkout_disabled'])
   {
?>
<p>Warning! You need to provide correct shipping information to complete your request.</p>
<?php
   }
   else
   {
?>
<br/>
<?php   
   }
?>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
 <td bgcolor="#8C8A5A">
  <table width="100%" cellpadding="3" cellspacing="1" border="0">
  <tr>
   <td bgcolor="#8C8A5A" colspan="2"><font color='#ffffff' face="Arial" size="2"><b>Shipping Info</b></font></td>
  </tr>
  <tr>
   <td align="right" valign="top" bgcolor="#9C9A63"><font size=2 face="Arial"><b>Full Name:</b></font></td>
   <td bgcolor="#D6D7B5"><font size="2" face="Arial"><?php echo Html::toText($details['shipping']['full_name'])?></font></td>
  </tr>
  <tr>
   <td align="right" valign="top" bgcolor="#9C9A63" style="width:20%"><font size="2" face="Arial"><b>Address:</b></font></td>
   <td bgcolor="#D6D7B5"><font size="2" face="Arial"><?php echo Html::toText($details['shipping']['address1'])?></font></td>
  </tr>
  <tr>
   <td align="right" valign="top" bgcolor="#9C9A63"><font size="2" face="Arial"><b>Address:</b></font></td>
   <td bgcolor="#D6D7B5"><font size="2" face="Arial"><?php echo Html::toText($details['shipping']['address2'])?></font></td>
  </tr>
  <tr>
    <td align="right" valign="top" bgcolor="#9C9A63"><font size="2" face="Arial"><b>City:</b></font></td>
    <td bgcolor="#D6D7B5"><font size="2" face="Arial">
     <?php echo Html::toText($details['shipping']['city'])?>&nbsp;&nbsp;
     <?php
          if (Countries::hasStates($details['shipping']['country_code']))
          {
     ?>
     <b>State:</b>&nbsp;<?php echo Html::toText(Countries::getStateName($details['shipping']['country_code'], $details['shipping']['state_code']))?>&nbsp;&nbsp;
     <?php
          }
     ?>
     <b>Postal Code:</b>&nbsp;<?php echo Html::toText($details['shipping']['postal_code'])?></font>
    </td>
  </tr>
  <tr>
   <td align="right" valign="top" bgcolor="#9C9A63"><font size="2" face="Arial"><b>Country:</b></font></td>
   <td bgcolor="#D6D7B5"><font size="2" face="Arial"><?php echo Html::toText(Countries::getCountryName($details['shipping']['country_code']))?></font></td>
  </tr>
  <tr>
   <td align="right" valign="top" bgcolor="#9C9A63"><font size="2" face="Arial"><b>Email:</b></font></td>
   <td bgcolor="#D6D7B5"><font size="2" face="Arial"><?php echo Html::toText($details['shipping']['email'])?></font><br/><br/></td>
  </tr>
  <tr>
   <td align="right" valign="top" bgcolor="#9C9A63"><font size="2" face="Arial"><b>Phone:</b></font></td>
   <td bgcolor="#D6D7B5"><font size="2" face="Arial"><?php echo Html::toText($details['shipping']['phone'])?></font></td>
  </tr>
  </table>
 </td>
</tr>
</table>
</div>
<br/>
<?php
   if (!$details['changes_disabled'])
   {
?>
<div style="float:left">
 <form method="get" action="<?php echo Site::root()?>/orders/change-shipping-details">
 <input type="hidden" name="id" value="<?php echo urlencode($payload_id)?>">
 <input class="submitButton" type="submit" value="Change Shipping Details"/>
 </form>
</div>
<?php
   }
   if (!$details['checkout_disabled'])
   {
?>
<div style="float:right">
 <form method="get" action="<?php echo Site::root()?>/checkout">
 <input type="hidden" name="id" value="<?php echo urlencode($payload_id)?>">
 <input class="submitButton" type="submit" value="Check Out"/>
 </form>
</div>
<?php
   }
 }
 else
 {
?>
<p class="page_error"><?php echo Html::toText($error)?></p>
<?php 
 }
?>
</div>
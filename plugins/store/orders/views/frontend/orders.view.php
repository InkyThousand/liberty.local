<div id="content-title"><h2>Pending Orders</h2></div>
<br/>
<div id="content-section">
<?php
 $incorrect_orders_found = false;

 if (!$error)
 {
?>
<div>
<table class="pending_orders">
<tr class="pending_orders_header">
 <td>#</td>
 <td>Order Id</td>
 <td>Date</td>
 <td colspan="3">Action</td>
</tr>
<?php
   if (count($pending_orders) < 1)
   {
?>
<tr>
 <td colspan="6" style="text-align:center">Nothing to display</td>
</tr>
<?php
   }
   else
   {
     $counter = 1;

     foreach ($pending_orders as $order)
     {
       $row_class = $counter % 2 == 0 ? 'odd' : 'even';

       if ($order['checkout_disabled'])
       {
         $incorrect_orders_found = true;
       }
?>
<tr class="pending_orders_item_<?php echo $row_class?>">
 <td><?php echo Html::toText($counter)?></td>
 <td>
<?php 
       echo Html::toText($order['payload_id']);

       if ($order['checkout_disabled'])
       {
         echo '<span style="color:red"><b>*</b></span>';
       }
?>
 </td>
 <td><?php echo Html::toText($order['date'])?></td>
 <td><a href="/orders/details?id=<?php echo Html::toText($order['payload_id'])?>">Details</a></td>
 <td>
<?php 
       if ($order['checkout_disabled'])
       {
         echo '<strike>Checkout</strike>';
       }
       else
       {
?>
 <a href="/checkout/?id=<?php echo Html::toText($order['payload_id'])?>">Checkout</a>
<?php
       }
?>
 </td>
 <td>
<?php
       if ($order['changes_disabled'])
       {
         echo '<strike>Remove</strike>';
       }
       else
       {
?> 
 <a href="/orders/?id=<?php echo Html::toText($order['payload_id'])?>&amp;action=remove&amp;token=<?php echo Security::token() ?>" onclick="return confirmRemove('Remove order with id &quot;<?php echo $order['payload_id'] ?>&quot;')">Remove</a></td>
<?php
       }
?>
</tr>
<?php
       $counter++;
     }
   }
?>
</table>
</div>
<?php
   if ($incorrect_orders_found)
   {
?>
<p>Orders marked with (<span style="color:red"><b>*</b></span>) cannot be processed until correct shipping details will be specified.</p>
<?php
   }
 }
 else
 {
?>
<p>Error retreiving pending orders list. The error message is:</p>
<p class="page_error"><?php echo Html::toText($error)?></p>
<?php
 }
?>
</div>
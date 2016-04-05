<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<h2><?php echo __('Order Details', 'orders'); ?></h2>
<br/>
<?php 
 if (isset($errors['page_error']))
 {
   Alert::error($errors['page_error'], 0);
 }
 else
 {
?>
<div>
<table class="table table-bordered" style="width:50%;white-space:pre">
<thead>
<tr>
 <td colspan="2">Order Information</td>
</tr>
</thead>
<tr>
 <td style="width:10%;text-align:right">Order Id:</td>
 <td style="width:50%;text-align:left"><?php echo Html::toText($order_details['order_id'])?></td>
</tr>
<tr>
 <td style="width:10%;text-align:right">Purchase Order number:</td>
 <td style="width:50%;text-align:left"><?php echo Html::toText($order_details['purchase_order_number'])?></td>
</tr>
<tr>
 <td style="text-align:right">Order Type:</td>
 <td style="text-align:left"><?php echo Html::toText($order_details['order_type'])?></td>
</tr>
<tr>
 <td style="text-align:right">Order Status:</td>
 <td style="text-align:left"><?php echo Html::toText($order_details['status'])?></td>
</tr>
</table>
</div>
<div>
<table class="table table-bordered" style="width:70%">
 <thead>
  <tr>
   <td>#</td>
   <td>Part No</td>
   <td>Description</td>
   <td style="text-align:center">Quantity</td>
   <td style="text-align:right">Price</td>
   <td style="text-align:right">Total Price</td>
  </tr>
 </thead>
 <tbody>
<?php
     $counter = 0;
     $subtotal_price = 0;

     foreach ($order_items as $item)
     {
       $counter++;
       $part_no = $item['code'];
       $description = $item['description'];
       $quantity = $item['quantity'];
       $price = (double)$item['price'];
       $total_price = $quantity * $price;
       $price = sprintf('%.2f', $price);
       $total_price = sprintf('%.2f', $total_price);
?>
<tr class="order_details_item_odd">
 <td><?php echo $counter?></td>
 <td><?php echo Html::toText($part_no)?></td>
 <td><?php echo Html::toText($description)?></td>
 <td style="text-align:center"><?php echo Html::toText($quantity)?></td>
 <td style="text-align:right"><?php echo $order_details['currency'] . Html::toText($price)?></td>
 <td style="text-align:right"><?php echo $order_details['currency'] . Html::toText($total_price)?></td>
</tr>
<?php
     }
?>
</tbody>
</table>
</div>
<div>
<table style="width:70%">
<tr>
 <td style="text-align:right;padding:5px">
       <div>
        <b>Sub total:</b>&nbsp;<?php echo Html::toText($order_details['currency']) . Html::toText(sprintf('%.2f', $order_details['subtotal']))?>
       </div>
<?php
     if ($order_details['handling'] > 0)
     {
?>
       <div>
        <b>Handling:</b>&nbsp;<?php echo Html::toText($order_details['currency']) . Html::toText(sprintf('%.2f', $order_details['handling']))?>
       </div>
<?php
     }
     
     if ($order_details['shipping_tax_mode'] == 'merchandise')
     {
       if ($order_details['shipping_tax'] > 0)
       {
?>
       <div>
        <b><?php echo Html::toText($order_details['shipping_tax_description']) ?>:</b>&nbsp;<?php echo Html::toText($order_details['currency']) . Html::toText(sprintf('%.2f', $order_details['shipping_tax']))?>
       </div>
<?php
       }
     }

     if ($order_details['shipping_cost'] > 0)
     {
?>
       <div>
        <b>Shipping cost:</b>&nbsp;<?php echo Html::toText($order_details['currency']) . Html::toText(sprintf('%.2f', $order_details['shipping_cost']))?>
       </div>
<?php
     }

     if ($order_details['shipping_tax_mode'] == 'total')
     {
       if ($order_details['shipping_tax'] > 0)
       {
?>
       <div>
        <b><?php echo Html::toText($order_details['shipping_tax_description']) ?>:</b>&nbsp;<?php echo Html::toText($order_details['currency']) . Html::toText(sprintf('%.2f', $order_details['shipping_tax']))?>
       </div>
<?php
       }
     }

     $total_price = $order_details['subtotal'] + $order_details['handling'] + $order_details['shipping_tax'] + $order_details['shipping_cost'];
?>
       <div>
        <b>Total:</b>&nbsp;<?php echo Html::toText($order_details['currency']) . Html::toText(sprintf('%.2f', $total_price))?>
       </div>
 </td>
</tr>
</table>
</div>

<table class="table table-bordered" style="width:70%;white-space:pre">
<thead>
<tr>
 <td colspan="2">Shipping Info</td>
</tr>
</thead>
<tr>
 <td style="width:10%;text-align:right">Shipping Method:</td>
 <td style="text-align:left"><?php echo Html::toText($order_details['shipping_method'])?></td>
</tr>
<tr>
 <td style="text-align:right">Full Name:</td>
 <td style="text-align:left"><?php echo Html::toText($order_details['ship_to']['name'])?></td>
</tr>
<tr>
 <td style="text-align:right">Address 1:</td>
 <td style="text-align:left"><?php echo Html::toText($order_details['ship_to']['address1'])?></td>
</tr>
<tr>
 <td style="text-align:right">Address 2:</td>
 <td style="text-align:left"><?php echo Html::toText($order_details['ship_to']['address2'])?></td>
</tr>
<tr>
 <td style="text-align:right">City:</td>
 <td style="text-align:left"><?php echo Html::toText($order_details['ship_to']['city'])?></td>
</tr>
<tr>
 <td style="text-align:right">Postal code:</td>
 <td style="text-align:left"><?php echo Html::toText($order_details['ship_to']['postal_code'])?></td>
</tr>
<tr>
 <td style="text-align:right">Country:</td>
 <td style="text-align:left"><?php echo Html::toText($order_details['ship_to']['country'])?></td>
</tr>
<tr>
 <td style="text-align:right">State:</td>
 <td style="text-align:left"><?php echo Html::toText($order_details['ship_to']['state'])?></td>
</tr>
<tr>
 <td style="text-align:right">Email:</td>
 <td style="text-align:left"><?php echo Html::toText($order_details['ship_to']['email'])?></td>
</tr>
<tr>
 <td style="text-align:right">Phone:</td>
 <td style="text-align:left"><?php echo Html::toText($order_details['ship_to']['phone'])?></td>
</tr>
</table>

<table class="table table-bordered" style="width:70%;white-space:pre">
<thead>
<tr>
 <td colspan="2">Payment Details:</td>
</tr>
</thead>
<tr>
 <td style="width:10%;text-align:right">Payment Type:</td>
 <td style="text-align:left"><?php echo Html::toText($order_details['payment_type'])?></td>
</tr>
<tr>
 <td style="width:10%;text-align:right">PayPal Transaction Id:</td>
 <td style="text-align:left"><?php echo Html::toText($order_details['payment_transaction_id'])?></td>
</tr>
<tr>
 <td style="width:10%;text-align:right">Payment Date:</td>
 <td style="text-align:left"><?php echo Html::toText($order_details['payment_date'])?></td>
</tr>
<tr>
 <td style="width:10%;text-align:right">Status:</td>
 <td style="text-align:left"><?php echo Html::toText($order_details['payment_status'])?></td>
</tr>
</table>

<table class="table table-bordered" style="width:70%;white-space:pre">
<thead>
<tr>
 <td colspan="2">Comments and Instructions</td>
</tr>
</thead>
<tr style="vertical-align:top">
 <td style="width:10%;text-align:right">Comments:</td>
 <td style="text-align:left"><?php echo Text::nl2br(Html::toText($order_details['comments']))?></td>
</tr>
<tr style="vertical-align:top">
 <td style="width:10%;text-align:right">Shipping Instructions:</td>
 <td style="text-align:left"><?php echo Text::nl2br(Html::toText($order_details['shipping_instructions']))?></td>
</tr>
</table>
<?php
 }
 echo (
       Form::open('index.php?id=orders&mode=' . Request::get('mode')) .
       Form::submit('cancel', __('Back', 'orders'), array('class' => 'btn')) .
       Form::close()
 );
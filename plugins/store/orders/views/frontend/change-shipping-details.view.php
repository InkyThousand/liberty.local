<div id="content-title"><h2>Change Shipping Details</h2></div>
<br/>
<div id="content-section">
<?php
 $first_item[''] = 'Please select...';

 if (!$error)
 {
   if ($update_error)
   {
?>
<div class="page_error" style="text-align:center"><?php echo Html::toText($update_error) ?></div>
<br/>
<?php  
   }
?>
<form method="post" action="<?php echo Site::root()?>/orders/change-shipping-details?id=<?php echo Html::toText($payload_id) ?>">
<?php echo Form::hidden('csrf', Security::token()) ?>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
 <td bgcolor="#8C8A5A">
  <table width="100%" cellpadding="3" cellspacing="1" border="0">
  <tr>
   <td align="right" valign="top" bgcolor="#9C9A63"><font size="2" face="Arial"><b>Full Name:</b></font></td>
   <td bgcolor="#D6D7B5"><?php echo Form::input('full_name', $details['full_name'], array('style' => 'width:350px;border:1px #9C9A60 solid'))?></td>
  </tr>
  <tr>
   <td align="right" valign="top" bgcolor="#9C9A63"><font size="2" face="Arial"><b>Address 1:</b></font></td>
   <td bgcolor="#D6D7B5"><?php echo Form::input('address1', $details['address1'], array('style' => 'width:350px;border:1px #9C9A60 solid'))?></td>
  </tr>
  <tr>
   <td align="right" valign="top" bgcolor="#9C9A63"><font size="2" face="Arial"><b>Address 2:</b></font></td>
   <td bgcolor="#D6D7B5"><?php echo Form::input('address2', $details['address2'], array('style' => 'width:350px;border:1px #9C9A60 solid'))?></td>
  </tr>
  <tr>
   <td align="right" valign="top" bgcolor="#9C9A63"><font size="2" face="Arial"><b>City:</b></font></td>
   <td bgcolor="#D6D7B5"><?php echo Form::input('city', $details['city'], array('style' => 'width:350px;border:1px #9C9A60 solid'))?></td>
  </tr>
  <tr>
   <td align="right" valign="top" bgcolor="#9C9A63"><font size="2" face="Arial"><b>Postal Code:</b></font></td>
   <td bgcolor="#D6D7B5"><?php echo Form::input('postal_code', $details['postal_code'], array('style' => 'width:350px;border:1px #9C9A60 solid'))?></td>
  </tr>
  <tr>
   <td align="right" valign="top" bgcolor="#9C9A63"><font size="2" face="Arial"><b>Country:</b></font></td>
   <td bgcolor="#D6D7B5"><?php echo Form::select('country_code', array_merge($first_item, Countries::getCountries(true)), $details['country_code'], array('style' => 'width:350px;border:1px #9C9A60 solid'))?></td>
  </tr>
<?php
   if (Valid::hasValue($details['country_code']) && Countries::hasStates($details['country_code']))
   {
?>
  <tr>
   <td align="right" valign="top" bgcolor="#9C9A63"><font size="2" face="Arial"><b>State:</b></font></td>
   <td bgcolor="#D6D7B5"><?php echo Form::select('state_code', array_merge($first_item, Countries::getStates($details['country_code'])),  $details['state_code'], array('style' => 'width:350px;border:1px #9C9A60 solid'))?></td>
  </tr>
<?php
   }
?>
  <tr>
   <td align="right" valign="top" bgcolor="#9C9A63"><font size="2" face="Arial"><b>Email:</b></font></td>
   <td bgcolor="#D6D7B5"><?php echo Form::input('email', $details['email'], array('style' => 'width:350px;border:1px #9C9A60 solid'))?></td>
  </tr>
  <tr>
   <td align="right" valign="top" bgcolor="#9C9A63"><font size="2" face="Arial"><b>Phone:</b></font></td>
   <td bgcolor="#D6D7B5"><?php echo Form::input('phone', $details['phone'], array('style' => 'width:350px;border:1px #9C9A60 solid'))?></td>
  </tr>
  </table>
 </td>
</tr>
</table>
<br/>
<div style="float:left">
<?php
   echo Form::submit('save', 'Save Changes', array('class' => 'submitButton'));
?>
</div>
</form>
<div style="float:right">
 <form method="get" action="<?php echo Site::root()?>orders/details">
 <input type="hidden" name="id" value="<?php echo urlencode($payload_id)?>">
 <input class="submitButton" type="submit" value="Cancel Changes"/>
 </form>
</div>
<?php
 }
 else
 {
?>
<p>Error processing data. The error message is:</p>
<p class="page_error"><?php echo Html::toText($error)?></p>
<?php 
 }
?>
</div>
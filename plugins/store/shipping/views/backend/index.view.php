<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<h2><?php echo __('Shipping Settings', 'shipping')?></h2>
<br/>
<?php if (Notification::get('success')) Alert::success(Notification::get('success')); ?>
<br />
<?php
 echo Form::open().
      Form::hidden('csrf', Security::token());
?>
<div class="tabbable">
    <ul class="nav nav-tabs">
        <li <?php if ($tab == 'settings') echo 'class="active"'?>>
         <a href="?id=shipping&amp;tab=settings"><?php echo __('Settings', 'shipping'); ?></a>
        </li>
        <li <?php if ($tab == 'credentials') echo 'class="active"'?>>
         <a href="?id=shipping&amp;tab=credentials"><?php echo __('Credentials', 'shipping'); ?></a>
        </li>
        <li <?php if ($tab == 'methods') echo 'class="active"'?>>
         <a href="?id=shipping&amp;tab=methods"><?php echo __('Methods', 'shipping'); ?></a>
        </li>
<?php
 if ($carrier)
 {
?>
        <li class="active">
        <a href="?id=shipping&amp;tab=options&amp;carrier=<?php echo Html::toText($carrier)?>"><?php echo __('Shipping Carrier Options', 'shipping'); ?></a>
        </li>
<?php
 }
?>
    </ul>
    <div class="tab-content tab-page">
     <div class="tab-pane active">
<?php
 if ($tab == 'settings')
 {
   $first_item[''] = 'Choose...';
?>
      <br/>
      <table style="width:100%">
      <tr style="vertical-align:top">
       <td style="width:50%">
       <h4><?php echo __('Shipping Methods Allowed', 'checkout')?></h4>
       <br/>
<?php
   echo 
        /*
        Form::checkbox('shipping_pickup', null, $shipping_pickup, array('id' => 'shipping_pickup')). Html::nbsp(2).
        Form::label('shipping_pickup', __('Pickup', 'shipping'), array('style' => 'display:inline')). Html::br(2).

        Form::checkbox('shipping_usps', null, $shipping_usps, array('id' => 'shipping_usps')). Html::nbsp(2).
        Form::label('shipping_usps', __('USPS', 'shipping'), array('style' => 'display:inline')).Html::br(2).
        */
        Form::checkbox('shipping_ups', null, $shipping_ups, array('id' => 'shipping_ups')). Html::nbsp(2).
        Form::label('shipping_ups', __('UPS', 'shipping'), array('style' => 'display:inline')).
        /*
        Html::br(2).
        Form::checkbox('shipping_fedex', null, $shipping_fedex, array('id' => 'shipping_fedex')). Html::nbsp(2).
        Form::label('shipping_fedex', __('FedEx', 'shipping'), array('style' => 'display:inline')).Html::br(2).

        Form::checkbox('shipping_call', null, $shipping_call, array('id' => 'shipping_call')). Html::nbsp(2).
        Form::label('shipping_call', __('Parts Representative call', 'shipping'), array('style' => 'display:inline')).
        */
        Html::br(3).

        Form::submit('edit_settings', __('Save', 'shipping'), array('class' => 'btn'));
?>
        </td>
        <td>
        <h4><?php echo __('Shipping Origination Address', 'shipping')?></h4>
        <br/>
<?php
   echo Form::label('origination_city', __('City', 'shipping')).
        Form::input('origination_city', $origination_city, array('class' => 'span6', 'id' => 'origination_city', 'style' => 'width:200px')).

        Form::label('origination_zip', __('Postal Code', 'shipping')).
        Form::input('origination_zip', $origination_zip, array('class' => 'span6', 'id' => 'origination_zip', 'style' => 'width:80px')).

        Form::label('origination_country', __('Country', 'shipping')).
        Form::select('origination_country', array_merge($first_item, $countries), $origination_country, array('id' => 'origination_country', 'class' => 'span6' . (isset($errors['origination_country_error']) ? ' error-field' : null)));

   if (isset($errors['origination_country_error'])) echo Html::nbsp(3).'<span class="error">'.$errors['origination_country_error'].'</span>';

   echo '<div id="state"'.(count($states) ? null : ' style="display:none"').'>';

   echo Form::label('origination_state', __('State', 'parts')).
        Form::select('origination_state', array_merge($first_item, $states), $origination_state, array('id' => 'origination_state', 'class' => 'span6' . (isset($errors['origination_state_error']) ? ' error-field' : null)));

   if (isset($errors['origination_state_error'])) echo Html::nbsp(3).'<span class="error">'.$errors['origination_state_error'].'</span>';

   echo '</div>';

   echo Html::br(2).
        Form::submit('edit_settings', __('Save', 'shipping'), array('class' => 'btn'));
?>
        </td>
       </tr>
       <tr>
        <td colspan="2"><hr/></td>
       </tr>
       <tr style="vertical-align:top">
        <td>
        <h4><?php echo __('Miscelaneous options', 'shipping')?></h4>
        <br/>
        <?php echo Form::label(null, __('Default dimensions of the shipping box', 'shipping')) ?>
        <?php echo Form::label(null, __('(for items with dimensions missing), inches', 'shipping')) ?>
        <table>
        <tr>
         <td><?php echo Form::label('default_width', __('Width', 'shipping')) ?></td>
         <td>&nbsp;</td>
         <td><?php echo Form::label('default_height', __('Height', 'shipping')) ?></td>
         <td>&nbsp;</td>
         <td><?php echo Form::label('default_length', __('Length', 'shipping')) ?></td>
        </tr>
        <tr style="vertical-align:middle">
         <td><?php echo Form::input('default_width', $default_width, array('class' => 'span6', 'style' => 'width:90px', 'id' => 'default_width')) ?></td>
         <td>&nbsp;</td>
         <td><?php echo Form::input('default_height', $default_height, array('class' => 'span6', 'style' => 'width:90px', 'id' => 'default_height')) ?></td>
         <td>&nbsp;</td>
         <td><?php echo Form::input('default_length', $default_length, array('class' => 'span6', 'style' => 'width:90px', 'id' => 'default_length')) ?></td>
        </tr>
        </table>
        <?php echo Form::label(null, __('Default weight', 'shipping')) ?>
        <?php echo Form::label(null, __('(for items with weight missing)', 'shipping')) ?>
        <table>
        <tr>
         <td><?php echo Form::label('default_pounds', __('Pounds', 'shipping')) ?></td>
         <td>&nbsp;</td>
         <td><?php echo Form::label('default_ounces', __('Ounces', 'shipping')) ?></td>
        </tr>
        <tr style="vertical-align:middle">
         <td><?php echo Form::input('default_pounds', $default_pounds, array('class' => 'span6', 'style' => 'width:90px', 'id' => 'default_pounds')) ?></td>
         <td>&nbsp;</td>
         <td><?php echo Form::input('default_ounces', $default_ounces, array('class' => 'span6', 'style' => 'width:90px', 'id' => 'default_ounces')) ?></td>
        </tr>
        </table>
<?php
     echo (Html::br().
        Form::submit('edit_settings', __('Save', 'shipping'), array('class' => 'btn'))
       );
?>
       </td>
       <td>
       <h4><?php echo __('Handling Settings', 'shipping')?></h4>
       <br/>
<?php
    echo (
        Form::label('handling_value', __('Add handling value to order total, USD', 'shipping')).
        Form::input('handling_value', $handling_value, array('class' => 'span7', 'style' => 'width:70px', 'id' => 'handling_value')).Html::br(2).
        Form::submit('edit_settings', __('Save', 'checkout'), array('class' => 'btn'))
       );
?>
       </td>
      </tr>
      </table>
<?php
 }
 if ($tab == 'credentials')
 {
?>
      <h4><?php echo __('UPS Credentials', 'shipping')?></h4>
      <br/>
<?php
 echo (
       Form::label('ups_endpoint_url', __('UPS API Endpoint URL', 'shipping')).
       Form::input('ups_endpoint_url', $ups_endpoint_url, array('class' => 'span7', 'id' => 'ups_endpoint_url')). Html::br().
       Form::label('ups_access_license_no', __('Access License Number', 'shipping')).
       Form::input('ups_access_license_no', $ups_access_license_no, array('class' => 'span7', 'id' => 'ups_access_license_no')). Html::br().
       Form::label('ups_user_id', __('User ID', 'shipping')).
       Form::input('ups_user_id', $ups_user_id, array('class' => 'span7', 'id' => 'ups_user_id')). Html::br().
       Form::label('ups_password', __('Password', 'shipping')).
       Form::input('ups_password', $ups_password, array('class' => 'span7', 'id' => 'ups_password')). Html::br(2).
       Form::submit('edit_settings', __('Save', 'shipping'), array('class' => 'btn'))
      );
 }
 if ($tab == 'methods')
 {
?>
     <style type="text/css">
      .TableHead {
        background-color: #c5d0e1;
        font-weight: 700;
        text-align: center;
      }

      .TableSubHead {
        background-color: #EEE;
        font-weight: 400;
      }

      .TableSubHeadPayment1 {
        background-color: #cde3c3;
        font-weight: 400;
      }

      .TableSubHeadPayment2 {
        background-color: #dff1e1;
        font-weight: 400;
      }

      .expand-strip
      {
        padding-bottom: 10px;
        text-align: right;
      }

      .shipping-methods
      {
       background-color:#F6F6F6;
       padding: 10px;
      }

      #toggle
      {
        padding-top: 5px;
        padding-bottom: 5px;
      }

      #toggle table
      {
        width: 100%;
      }
     </style>
     <script type="text/javascript">
      $(document).ready(function () 
      {
        $('a#check').click(function()
        {
          var id = $(this).parent().parent().children('table').get(0).id;
          $('#' + id + ' tbody tr td:last-child input:checkbox').each(function() 
          {
            this.checked = true;
          });
        });

        $('a#uncheck').click(function()
        {
          var id = $(this).parent().parent().children('table').get(0).id;
          $('#' + id + ' tbody tr td:last-child input:checkbox').each(function() 
          {
            this.checked = false;
          });
        });
      });
     </script>
        <h4><?php echo __('Real-time calculated shipping methods', 'shipping')?></h4>
        <br/>
        <div class="shipping-methods">
          <div id="toggle">
            <table>
            <tr>
              <td width="33%"><b>UPS</b></td>
              <td width="33%" nowrap><?php echo $ups_methods_active ?> active method(s) out of <?php echo count($ups_shipping_methods) ?></td>
              <td width="33%" nowrap><a href="?id=shipping&tab=options&carrier=ups">Options &gt;&gt;</a></td>
            </tr>
            </table>
            <div id="ups">
              <div align="right" style="line-height:170%"><a id="check" href="javascript:void(0);">Check all</a> / <a id="uncheck" href="javascript:void(0);">Uncheck all</a></div>
              <table cellpadding="5" cellspacing="5" id="ups_table">
              <tr class="TableHead">
                <td>Shipping method</td>
                <td>Delivery time</td>
                <td>Destination</td>
                <td nowrap="nowrap">Weight limit (lbs)</td>
                <td>Active</td>
              </tr>
<?php
              $counter = 1;

              foreach ($ups_shipping_methods as $method)
              {
?>
              <tr <?php echo $counter % 2 ? 'class="TableSubHead"' : null ?>>
                <td><?php echo $method['name'] ?></td>
                <td align="center"><input type="text" name="method[<?php echo $method['id'] ?>][delivery]" size="8" value="<?php echo $method['delivery'] ?>"/></td>
                <td align="center"><?php echo $method['national'] ? 'National' : 'International' ?></td>
                <td align="center" nowrap="nowrap"><input type="text" size="8" style="width:60px" name="method[<?php echo $method['id'] ?>][limit_low]" value="<?php echo $method['limit_low'] ?>"/> - <input type="text" size="8" style="width:60px" name="method[<?php echo $method['id'] ?>][limit_high]" value="<?php echo $method['limit_high'] ?>"/></td>
                <td align="center"><input type="checkbox" name="method[<?php echo $method['id'] ?>][active]" <?php if ($method['active']) echo 'checked="checked"' ?>/></td>
              </tr>
<?php
               $counter++;
             }
?>
              </table>
            </div>
          </div>
        </div>
        <br/>
<?php
   echo Form::submit('edit_settings', __('Save', 'shipping'), array('class' => 'btn'));
 }

 if ($tab == 'options')
 {
   if ($carrier == 'ups')
   {
?>
        <br/>
        <table style="width:100%">
        <tr style="vertical-align:top">
          <td style="width:50%">
            <h4><?php echo __('General Options', 'shipping')?></h4>
            <br/>
<?php
     echo (
           Form::label('ups_pickup_type', __('Your pickup type', 'shipping')).
           Form::select('ups_pickup_type', array('01' => 'Daily Pickup', '03' => 'Customer counter', '06' => 'One time pickup', '07' => 'On call air', '11' => 'Suggested retail rates', '19' => 'Letter center', '20' => 'Air service center'), $ups_pickup_type, array('id' => 'ups_pickup_type', 'style' => 'width:180px')).

           Form::label('ups_destination_type', __('Destination type', 'shipping')).
           Form::select('ups_destination_type', array('Y' => 'Residential address', 'N' => 'Commercial address'), $ups_destination_type, array('id' => 'ups_destination_type', 'style' => 'width:170px')).

           Form::label('ups_packaging_type', __('Packaging type', 'shipping')).
           Form::select('ups_packaging_type', array('00' => 'Unknown', '01' => 'UPS Letter / UPS Express Envelope', '02' => 'Package', '03' => 'UPS Tube', '04' => 'UPS Pak', '21' => 'UPS Express Box', '24' => 'UPS 25 Kg Box&#174;', '25' => 'UPS 10 Kg Box&#174;', '30' => 'Pallet (for GB or PL domestic shipments only)', '2a' => 'Small Express Box', '2b' => 'Medium Express Box', '2c' => 'Large Express Box'), $ups_packaging_type, array('id' => 'ups_packaging_type', 'style' => 'width:330px')).

           Html::br(2).
           Form::submit('edit_settings', __('Save', 'shipping'), array('class' => 'btn'))
          );
?>
          </td>
          <td>
            <h4><?php echo __('Delivery Confirmation', 'shipping')?></h4>
            <br/>
<?php
     echo (
           Form::label('ups_delivery_confirmation', __('Delivery confirmation *', 'shipping')).
           Form::select('ups_delivery_confirmation', array('0' => 'No confirmation', '1' => 'Delivery confirmation - no signature', '2' => 'Delivery confirmation - signature required', '3' => 'Delivery confirmation - adult signature required'), $ups_delivery_confirmation, array('id' => 'ups_delivery_confirmation', 'style' => 'width:350px'))
          )
?>
            <p>* Delivery confirmation is only allowed for shipments within US.</p>
            <br/>
<?php
     echo (
           Form::submit('edit_settings', __('Save', 'shipping'), array('class' => 'btn'))
          );
?>
          </td>
        </tr>
       <tr>
        <td colspan="2"><hr/></td>
       </tr>
        <tr style="vertical-align:top">
          <td>
            <h4><?php echo __('Service Options', 'shipping')?></h4>
            <br/>
<?php
     echo (
           Form::checkbox('ups_additional_handling', null, $ups_additional_handling, array('id' => 'ups_additional_handling')). Html::nbsp(2).
           Form::label('ups_additional_handling', __('Additional handling', 'checkout'), array('style' => 'display:inline')). Html::br(2).

           Form::checkbox('ups_saturday_pickup', null, $ups_saturday_pickup, array('id' => 'ups_saturday_pickup')). Html::nbsp(2).
           Form::label('ups_saturday_pickup', __('Saturday pickup', 'checkout'), array('style' => 'display:inline')). Html::br(2).

           Form::checkbox('ups_saturday_delivery', null, $ups_saturday_delivery, array('id' => 'ups_saturday_delivery')). Html::nbsp(2).
           Form::label('ups_saturday_delivery', __('Saturday delivery', 'checkout'), array('style' => 'display:inline')). Html::br(2).

           Form::submit('edit_settings', __('Save', 'shipping'), array('class' => 'btn'))
          );
?>
          </td>
        </tr>
        </table>
<?php
   }
 }
?>
     </div>
    </div>
</div>
<?php
 echo Form::close();
?>
<form>
 <input type="hidden" name="siteurl" value="<?php echo Site::root() ?>/admin/index.php?id=shipping">
</form>
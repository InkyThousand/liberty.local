<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<h2><?php echo __('Payment Settings', 'payment')?></h2>
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
        <a href="?id=payment&amp;tab=settings"><?php echo __('Settings', 'payment'); ?></a>
        </li>
        <li <?php if ($tab == 'credentials') echo 'class="active"'?>>
        <a href="?id=payment&amp;tab=credentials"><?php echo __('Credentials', 'payment'); ?></a>
        </li>
    </ul>
    <div class="tab-content tab-page">
     <div class="tab-pane active">
<?php
 if ($tab == 'settings')
 {
?>
       <h4><?php echo __('Payment Methods Allowed', 'payment')?></h4>
       <br/>
<?php
    echo (
        Form::checkbox('payment_paypal', 'on', $payment_paypal, array('id' => 'payment_paypal')). Html::nbsp(2).
        Form::label('payment_paypal', __('PayPal', 'payment'), array('style' => 'display:inline')). Html::br(2).

        Form::checkbox('payment_credit_card', 'on', $payment_credit_card, array('id' => 'payment_credit_card')). Html::nbsp(2).
        Form::label('payment_credit_card', __('Credit Card', 'payment'), array('style' => 'display:inline')).Html::br(2).
        Form::submit('edit_settings', __('Save', 'checkout'), array('class' => 'btn'))
    );
 }
 if ($tab == 'credentials')
 {
?>
        <h4><?php echo __('PayPal credentials', 'payment')?></h4>
        <br/>
        <table style="width:100%;margin-top:5px">
        <tr>
         <td>
<?php
   echo (
         Form::radio('paypal_mode', 'sandbox', $mode == 'sandbox', array('id' => 'paypal_mode_sandbox')). Html::nbsp(2).
         Form::label('paypal_mode_sandbox', __('Sandbox mode', 'payment'), array('style' => 'display:inline')).Html::br(2).

         Form::label('sandbox_username', __('API Username', 'payment')).
         Form::input('sandbox_username', $sandbox_username, array('class' => 'span9', 'id' => 'sandbox_username')). Html::br().

         Form::label('sandbox_password', __('API Password', 'payment')).
         Form::input('sandbox_password', $sandbox_password, array('class' => 'span9', 'id' => 'sandbox_password')). Html::br().

         Form::label('sandbox_signature', __('API Signature', 'payment')).
         Form::input('sandbox_signature', $sandbox_signature, array('class' => 'span9', 'id' => 'sandbox_signature')). Html::br().

         Form::label('sandbox_version', __('API Version', 'payment')).
         Form::input('sandbox_version', $sandbox_version, array('class' => 'span2', 'id' => 'sandbox_version')). Html::br(2).

         Form::submit('edit_settings', __('Save', 'checkout'), array('class' => 'btn'))
        );
?>
         </td>
         <td>
<?php
   echo (
         Form::radio('paypal_mode', 'real', $mode == 'real', array('id' => 'paypal_mode_real')). Html::nbsp(2).
         Form::label('paypal_mode_real', __('Real mode', 'payment'), array('style' => 'display:inline')).Html::br(2).

         Form::label('real_username', __('API Username', 'payment')).
         Form::input('real_username', $real_username, array('class' => 'span9', 'id' => 'real_username')). Html::br().

         Form::label('real_password', __('API Password', 'payment')).
         Form::input('real_password', $real_password, array('class' => 'span9', 'id' => 'real_password')). Html::br().

         Form::label('real_signature', __('API Signature', 'payment')).
         Form::input('real_signature', $real_signature, array('class' => 'span9', 'id' => 'real_signature')). Html::br().

         Form::label('real_version', __('API Version', 'payment')).
         Form::input('real_version', $real_version, array('class' => 'span2', 'id' => 'real_version')). Html::br(2).

         Form::submit('edit_settings', __('Save', 'checkout'), array('class' => 'btn'))
        );
?>
         </td>
        </tr>
        </table>
<?php
 }
?>
     </div>
    </div>
</div>
<?php
 echo Form::close();
?>
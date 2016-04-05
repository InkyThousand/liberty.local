<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<h2><?php echo __('E-Mail Notifications Settings', 'notifications')?></h2>
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
        <a href="?id=notifications&amp;tab=settings"><?php echo __('E-Mail Settings', 'notifications'); ?></a>
        </li>
        <li <?php if ($tab == 'customer') echo 'class="active"'?>>
        <a href="?id=notifications&amp;tab=customer"><?php echo __('Customer Notification', 'notifications'); ?></a>
        </li>
        <li <?php if ($tab == 'dealer') echo 'class="active"'?>>
        <a href="?id=notifications&amp;tab=dealer"><?php echo __('Dealer Notification', 'notifications'); ?></a>
        </li>
    </ul>
    <div class="tab-content tab-page">
     <div class="tab-pane active">
<?php
 if ($tab == 'settings')
 {
?>
      <br/>
      <table style="width:100%">
       <tr style="vertical-align:top">
        <td>
         <h4><?php echo __('Basic email options', 'notifications')?></h4>
         <br/>
<?php
 echo (
       Form::checkbox('use_html', null, $use_html, array('id' => 'use_html', 'disabled' => 'disabled')). Html::nbsp(2).
       Form::label('use_html', __('Send messages in HTML format', 'notifications'), array('style' => 'display:inline')).Html::br(2).
       Form::submit('edit_settings', __('Save', 'notifications'), array('class' => 'btn'))
      );
?>
        </td>
        <td>
         <h4><?php echo __('Sender Settings', 'notifications')?></h4>
         <br/>
<?php
 echo (
       Form::label('from_name', __('Sender name', 'notifications')).
       Form::input('from_name', $from_name, array('class' => 'span7', 'id' => 'from_name')).Html::br(2).

       Form::label('from_address', __('Sender address', 'notifications')).
       Form::input('from_address', $from_address, array('class' => 'span7', 'id' => 'from_address')).Html::br(2).
       Form::submit('edit_settings', __('Save', 'notifications'), array('class' => 'btn'))
      );
?>
        </td>
       </tr>
       <tr>
        <td>
         <h4><?php echo __('SMTP options', 'notifications')?></h4>
         <br/>
<?php
 echo (
       Form::checkbox('use_smtp', null, $use_smtp, array('id' => 'use_smtp')). Html::nbsp(2).
       Form::label('use_smtp', __('Use SMTP server instead of internal mailer', 'notifications'), array('style' => 'display:inline')). Html::br(2).

       Form::label('smtp_host', __('SMTP server', 'notifications')).
       Form::input('smtp_host', $smtp_host, array('class' => 'span7', 'id' => 'smtp_host')). Html::br().

       Form::label('smtp_port', __('SMTP server port', 'notifications')).
       Form::input('smtp_port', $smtp_port, array('class' => 'span7', 'id' => 'smtp_port')). Html::br().

       Form::label('smtp_auth_username', __('SMTP AUTH username', 'notifications')).
       Form::input('smtp_auth_username', $smtp_auth_username, array('class' => 'span7', 'id' => 'smtp_auth_username')). Html::br().

       Form::label('smtp_auth_password', __('SMTP AUTH password', 'notifications')).
       Form::input('smtp_auth_password', $smtp_auth_password, array('class' => 'span7', 'id' => 'smtp_auth_password')).Html::br(2).

       Form::submit('edit_settings', __('Save', 'notifications'), array('class' => 'btn')).Html::nbsp(2).
       Form::submit('check_settings', __('Check settings', 'notifications'), array('class' => 'btn', 'onclick' => 'return $.store.notifications.showCheckModal()'))
      );
?>
        </td>
       </tr>
      </table>
<?php
 }
 if ($tab == 'customer')
 {
?>
    <br/>
    <h4><?php echo __('Customer E-Mail Notification Settings', 'notifications')?></h4>
    <br/>
<?php
 echo (
       Form::checkbox('customer_allow', null, $customer_allow, array('id' => 'customer_allow')). Html::nbsp(2).
       Form::label('customer_allow', __('Send email notification to customer', 'notifications'), array('style' => 'display:inline')).Html::br(2).

       Form::label('customer_subject', __('Message Subject', 'notifications')).
       Form::input('customer_subject', $customer_subject, array('class' => 'span7', 'id' => 'customer_subject')).Html::br(2).

       Form::label('customer_body', __('Message Body', 'notifications')).
       Form::textarea('customer_body', Html::toText($customer_body), array('class' => 'span7', 'style' => 'width:90%;height:400px', 'id' => 'customer_body')).Html::br(2).

       Form::submit('edit_settings', __('Save', 'notifications'), array('class' => 'btn'))
      );
 }

 if ($tab == 'dealer')
 {
?>
    <br/>
    <h4><?php echo __('Dealer E-Mail Notification Settings', 'notifications')?></h4>
    <br/>
<?php
 echo (
       Form::checkbox('dealer_allow', null, $dealer_allow, array('id' => 'dealer_allow')). Html::nbsp(2).
       Form::label('dealer_allow', __('Send email notification to dealer', 'notifications'), array('style' => 'display:inline')).Html::br(2).

       Form::label('dealer_to_name', __('Recipient name', 'notifications')).
       Form::input('dealer_to_name', $dealer_to_name, array('class' => 'span7', 'id' => 'dealer_to_name')).Html::br(2).

       Form::label('dealer_to_address', __('Recipient address', 'notifications')).
       Form::input('dealer_to_address', $dealer_to_address, array('class' => 'span7', 'id' => 'dealer_to_address')).Html::br(2).

       Form::label('dealer_subject', __('Message Subject', 'notifications')).
       Form::input('dealer_subject', $dealer_subject, array('class' => 'span7', 'id' => 'dealer_subject')).Html::br(2).

       Form::label('dealer_body', __('Message Body', 'notifications')).
       Form::textarea('dealer_body', Html::toText($dealer_body), array('class' => 'span7', 'style' => 'width:90%;height:400px', 'id' => 'dealer_body')).Html::br(2).

       Form::submit('edit_settings', __('Save', 'notifications'), array('class' => 'btn'))
      );
 }
?>
     </div>
    </div>
</div>
<?php
 echo Form::close();
?>
<form><input type="hidden" name="siteurl" value="<?php echo Option::get('siteurl') ?>admin/index.php?id=notifications"></form>
<div class="modal hide" id="checkSMTPSettings">
    <div class="modal-header">
      <a class="close" data-dismiss="modal">×</a>
      <h3><?php echo __('SMTP checking', 'notifications'); ?></h3>
    </div>
    <div class="modal-body">
      <p>
        <label for="recipient_address">Recipient Address:</label><input type="text" id="recipient_address" name="recipient_address" class="span7" />
        <br />
        <input type="checkbox" id="smtp_debug" name="smtp_debug" />&nbsp;&nbsp;<label style="display:inline" for="smtp_debug">Show debug messages</label>
        <br />
        <br />
        <input type="submit" id="send_message" name="send_message" value="Send Message" class="btn" onclick="return $.store.notifications.sendMessage()" />
        <input type="submit" id="clear" name="clear" value="Clear Results" class="btn" onclick="return $.store.notifications.clearResults()" />
        <div class="response" id="response"></div>
      </p>
    </div>
</div>

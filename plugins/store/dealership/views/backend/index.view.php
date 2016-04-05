<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<h2><?php echo __('Dealership Settings', 'dealership') ?></h2>
<br />
<?php 
 if (Notification::get('success')) Alert::success(Notification::get('success'));

 echo Form::open().
      Form::hidden('csrf', Security::token()).
        
      Form::label('ezparts_url', __('EzParts URL', 'dealership')).
      Form::input('ezparts_url', $ezparts_url, array('class' => 'span6', 'id' => 'ezparts_url')).
      
      Form::label('partner_name', __('Partner name', 'dealership')).
      Form::input('partner_name', $partner_name, array('class' => 'span6', 'id' => 'partner_name')).

      Form::label('passkey_name', __('Passkey Variable Name', 'dealership')).
      Form::input('passkey_name', $passkey_name, array('class' => 'span6', 'id' => 'passkey_name')).

      Form::label('passkey_value', __('Passkey Variable Value', 'dealership')).
      Form::input('passkey_value', $passkey_value, array('class' => 'span6', 'id' => 'passkey_value')).

      Form::label('dealer_account', __('Dealer Account', 'dealership')).
      Form::input('dealer_account', $dealer_account, array('class' => 'span6', 'id' => 'dealer_account')).

      Form::label('doorback_page', __('Doorback Page', 'dealership')).
      Form::input('doorback_page', $doorback_page, array('class' => 'span6', 'id' => 'doorback_page')).

      Html::br(2).

      Form::submit('edit_settings', __('Save', 'dealership'), array('class' => 'btn')).
      Form::close();
?>
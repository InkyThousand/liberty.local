<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<h2><?php echo __('Edit State Details', 'countries') ?></h2>
<br/>
<?php
 if (isset($errors['page_error']))
 {
   Alert::error($errors['page_error'], 0);

   echo (
         Form::open().
         Form::submit('edit_state_cancel', __('Back', 'countries'), array('class' => 'btn')).
         Form::close()
        );
 }
 else
 {
   if (Notification::get('success')) Alert::success(Notification::get('success'));
        
   echo Form::open().
        Form::hidden('csrf', Security::token());

   echo Form::label('state_code', __('Code', 'countries')).
        Form::input('state_code', $state_code, array('maxlength' => 2, 'class' => 'span1' . (isset($errors['code_error']) ? ' error-field' : null), 'id' => 'state_code'));

   if (isset($errors['code_error'])) echo Html::nbsp(4).'<span class="error">'.$errors['code_error'].'</span>';

   echo Form::label('state_name', __('Name', 'countries')).
        Form::input('state_name', $state_name, array('class' => 'span3' . (isset($errors['name_error']) ? ' error-field' : null), 'id' => 'state_name'));

   if (isset($errors['name_error'])) echo Html::nbsp(4).'<span class="error">'.$errors['name_error'].'</span>';

   echo Html::br(2).
        Form::submit('edit_state_and_exit', __('Save and exit', 'countries'), array('class' => 'btn')).Html::nbsp(2).
        Form::submit('edit_state', __('Save', 'countries'), array('class' => 'btn')).Html::nbsp(2).
        Form::submit('cancel', __('Cancel', 'countries'), array('class' => 'btn')).
        Form::close();
 }
?>
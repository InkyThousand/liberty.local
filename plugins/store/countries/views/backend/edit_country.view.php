<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<h2><?php echo __('Edit Country Details', 'countries') ?></h2>
<br/>
<?php
 if (isset($errors['page_error']))
 {
   Alert::error($errors['page_error'], 0);

   echo (
         Form::open().
         Form::submit('edit_country_cancel', __('Back', 'countries'), array('class' => 'btn')).
         Form::close()
        );
 }
 else
 {
   if (Notification::get('success')) Alert::success(Notification::get('success'));
   if (Notification::get('error')) Alert::error(Notification::get('error'));
      
   echo Form::open().
        Form::hidden('csrf', Security::token());

   echo Form::label('country_code', __('Code', 'countries')).
        Form::input('country_code', $country_code, array('maxlength' => 2, 'class' => 'span1' . (isset($errors['code_error']) ? ' error-field' : null), 'id' => 'country_code'));

   if (isset($errors['code_error'])) echo Html::nbsp(4).'<span class="error">'.$errors['code_error'].'</span>';

   echo Form::label('country_name', __('Name', 'countries')).
        Form::input('country_name', $country_name, array('class' => 'span3' . (isset($errors['name_error']) ? ' error-field' : null), 'id' => 'country_name'));

   if (isset($errors['name_error'])) echo Html::nbsp(4).'<span class="error">'.$errors['name_error'].'</span>';

   echo Html::br().
        Form::checkbox('country_active', null, $country_active, array('id' => 'country_active')).
        Html::nbsp(2).
        Form::label('country_active', __('Active', 'countries'), array('style' => 'display:inline'));

   echo Html::br(2).
        Form::submit('edit_country_and_exit', __('Save and exit', 'countries'), array('class' => 'btn')).Html::nbsp(2).
        Form::submit('edit_country', __('Save', 'countries'), array('class' => 'btn')).Html::nbsp(2).
        Form::submit('cancel', __('Cancel', 'countries'), array('class' => 'btn')).
        Form::close();
 }
?>
<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<h2><?php echo __('Edit Price Type', 'price-types'); ?></h2>
<br/>
<?php 
 if (isset($errors['page_error']))
 {
   Alert::error($errors['page_error'], 0);
   echo Form::open().
        Form::submit('cancel', __('Back', 'price-types'), array('class' => 'btn')).
        Form::close();
 }
 else
 {
   if (Notification::get('success')) Alert::success(Notification::get('success'));
   if (Notification::get('error')) Alert::error(Notification::get('error'));

   echo (
         Form::open().
         Form::hidden('csrf', Security::token())
        );

   echo (
         Form::label('price_type_description', __('Description', 'price-types')).
         Form::input('price_type_description', $price_type_description, array('maxlength' => 255, 'class' => 'span6' . (isset($errors['description_error']) ? ' error-field' : null), 'id' => 'price_type_description'))
        );

   if (isset($errors['description_error'])) echo Html::nbsp(3).'<span class="error">'.$errors['description_error'].'</span>';

   echo (
         Form::label('price_type_sign', __('Sign', 'price-types')).
         Form::input('price_type_sign', $price_type_sign, array('maxlength' => 10, 'class' => 'span6', 'id' => 'price_type_sign', 'style' => 'width:100px'))
        );

   echo (
         Form::label('price_type_currency', __('Currency', 'price-types')).
         Form::input('price_type_currency', $price_type_currency, array('maxlength' => 3, 'class' => 'span6' . (isset($errors['currency_error']) ? ' error-field' : null), 'id' => 'price_type_currency', 'style' => 'width:100px'))
        );

   if (isset($errors['currency_error'])) echo Html::nbsp(3).'<span class="error">'.$errors['currency_error'].'</span>';

   echo (
         Html::br(2).
         Form::submit('edit_price_type_and_exit', __('Save and exit', 'price-types'), array('class' => 'btn')).Html::nbsp(2).
         Form::submit('edit_price_type', __('Save', 'price-types'), array('class' => 'btn')).Html::nbsp(2).
         Form::submit('cancel', __('Cancel', 'price-types'), array('class' => 'btn')).
         Form::close()
        );
 }
?>
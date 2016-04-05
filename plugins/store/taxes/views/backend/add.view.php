<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<h2><?php echo __('New Shipping Tax', 'taxes'); ?></h2>
<br/>
<?php 
 if (Notification::get('success')) Alert::success(Notification::get('success'));

 $first_item[''] = 'Choose...';

 $tax_modes['merchandise'] = 'Items only';
 $tax_modes['total'] = 'Items and Freight';
    
 echo (
       Form::open().
       Form::hidden('csrf', Security::token())
      );

 echo (
       Form::label('tax_description', __('Description', 'taxes')).
       Form::input('tax_description', $tax_description, array('class' => 'span5' . (isset($errors['description_error']) ? ' error-field' : null), 'id' => 'tax_description'))
      );

 if (isset($errors['description_error'])) echo Html::nbsp(3).'<span class="error">'.$errors['description_error'].'</span>';

 echo (
       Form::label('tax_display', __('Display', 'taxes')).
       Form::input('tax_display', $tax_display, array('class' => 'span5' . (isset($errors['display_error']) ? ' error-field' : null), 'id' => 'tax_display', 'maxlength' => 255))
      );

 if (isset($errors['display_error'])) echo Html::nbsp(3).'<span class="error">'.$errors['display_error'].'</span>';


 echo (
       Form::label('tax_country', __('Country', 'taxes')).
       Form::select('tax_country', array_merge($first_item, $countries), $tax_country, array('class' => 'span3' . (isset($errors['country_error']) ? ' error-field' : null), 'id' => 'tax_country'))
      );

 if (isset($errors['country_error'])) echo Html::nbsp(3).'<span class="error">'.$errors['country_error'].'</span>';

 echo '<div id="state"'.(count($states) ? null : ' style="display:none"').'>';
 
 echo (
       Form::label('tax_state', __('State', 'taxes')).
       Form::select('tax_state', array_merge($first_item, $states), $tax_state, array('class' => 'span3' . (isset($errors['state_error']) ? ' error-field' : null), 'id' => 'tax_state'))
      );

 if (isset($errors['state_error'])) echo Html::nbsp(3).'<span class="error">'.$errors['state_error'].'</span>';
 
 echo '</div>';

 echo (
       Form::label('tax_mode', __('Calculate Sales Tax on', 'taxes')).
       Form::select('tax_mode', array_merge($first_item, $tax_modes), $tax_mode, array('class' => 'span3' . (isset($errors['mode_error']) ? ' error-field' : null), 'id' => 'tax_mode'))
      );

 if (isset($errors['mode_error'])) echo Html::nbsp(3).'<span class="error">'.$errors['mode_error'].'</span>';

 echo (
       Form::label('tax_value', __('Value (%)', 'taxes')).
       Form::input('tax_value', $tax_value, array('class' => 'span1' . (isset($errors['value_error']) ? ' error-field' : null), 'id' => 'tax_value'))
      );

 if (isset($errors['value_error'])) echo Html::nbsp(3).'<span class="error">'.$errors['value_error'].'</span>';

 echo (
       Html::br(2).
       Form::submit('add_tax_and_exit', __('Save and exit', 'taxes'), array('class' => 'btn')).Html::nbsp(2).
       Form::submit('add_tax', __('Save', 'taxes'), array('class' => 'btn')).Html::nbsp(2).
       Form::submit('cancel', __('Cancel', 'taxes'), array('class' => 'btn')).
       Form::close()
      );
?>
<form>
 <input type="hidden" name="siteurl" value="<?php echo Site::root() ?>/admin/index.php?id=taxes">
</form>

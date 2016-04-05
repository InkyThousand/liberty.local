<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<h2><?php echo __('Import Distributors', 'distributors'); ?></h2>
<br />
<?php 
 if ($page_error)
 {
   Alert::error($page_error, 0);
   echo (
         Form::open().
         Form::submit('cancel', __('Back'), array('class' => 'btn')).
         Form::close()
        );
   exit;
 }

 if (Notification::get('success')) Alert::success(Notification::get('success'));

 echo Form::open(null, array('enctype' => 'multipart/form-data')).
      Form::hidden('csrf', Security::token());

 echo Form::label('distributors_data', __('File to import', 'distributors'));
 
 echo Form::file('distributors_data', array('class' => 'span6' . (isset($field_errors['data_error']) ? ' field-error' : null), 'id' => 'distributors_data', 'style' => 'width:400px'));
 
 if (isset($field_errors['data_error'])) echo Html::nbsp(3) . '<span class="error">' . $field_errors['data_error'] . '</span>';

 echo (
       Html::br(2).
       Form::submit('import_data', __('Import', 'distributors'), array('class' => 'btn')) . Html::nbsp(2).
       Form::submit('cancel', __('Cancel', 'distributors'), array('class' => 'btn')).
       Form::close()
      );
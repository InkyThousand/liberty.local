<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<h2><?php echo __('Import Part Data', 'parts'); ?></h2>
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

 echo Form::label('part_data', __('File to import', 'parts'));
 
 echo Form::file('part_data', array('class' => 'span6' . (isset($field_errors['data_error']) ? ' field-error' : null), 'id' => 'part_data', 'style' => 'width:400px'));
 
 if (isset($field_errors['data_error'])) echo Html::nbsp(3) . '<span class="error">' . $field_errors['data_error'] . '</span>';

 echo Html::br(2);

 echo Form::label('update_only_mode', Form::checkbox('update_only_mode', null, $update_only_mode, array('id' => 'update_only_mode', 'style' => 'margin-right:5px')) . __('"Update only" mode', 'parts'));

 echo (
       Html::br(2).
       Form::submit('import_data', __('Import', 'parts'), array('class' => 'btn')) . Html::nbsp(2).
       Form::submit('cancel', __('Cancel', 'parts'), array('class' => 'btn')).
       Form::close()
      );
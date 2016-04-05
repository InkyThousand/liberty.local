<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<h2><?php echo __('Part Details', 'parts'); ?></h2>
<br/>
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

 echo Form::open().
      Form::hidden('csrf', Security::token());

 echo Form::label('part_code', __('Part Number', 'parts'));

 echo Form::input('part_code', $part_code, array('class' => 'span6' . (isset($field_errors['code_error']) ? ' error-field' : null), 'id' => 'part_code'));

 if (isset($field_errors['code_error'])) echo Html::nbsp(3) . '<span class="error">' . $field_errors['code_error'] . '</span>';

 $languages = array();
 $descriptions = array();

 foreach ($part_descriptions as $language_code => $description)
 {
   $languages[$language_code] = $language_code;
   echo Form::hidden('lang_' . $language_code, $description);
   $descriptions[$language_code] = $description;
 }
?>
<div class="control-group">
<?php
 echo (
       Html::br().
       Form::label(null, __('Part Descriptions', 'parts'))
      );
?>
 <div class="controls">
  <table>
    <tr>
      <td><?php echo Form::select(null, $languages, null, array('style' => 'height:150px;width:80px', 'size' => '2', 'id' => 'part_languages')) ?></td>
      <td><?php echo Form::textarea(null, Html::toText($descriptions[$languages['en']]), array('style' => 'height:140px;width:570px', 'readonly' => 'readonly', 'id' => 'part_description')) ?></td>
    </tr>
  </table>
 </div>
</div>
<div class="control-group">
 <div class="controls">
  <table>
  <tr>
   <td style="width:320px">
<?php
 echo (
       Html::br().
       Form::label('part_weight', __('Shipping Box Dimensions', 'parts'))
      );
?>
   </td>
   <td style="width:10px"></td>
   <td>
<?php
 echo (
       Html::br().
       Form::label('part_weight', __('Weigth', 'parts'))
      );
?>
   </td>
  </tr>
  <tr>
   <td style="width:320px">
     <table>
     <tr>
      <td><?php echo Form::label('part_width', __('Width', 'parts')) ?></td>
      <td><?php echo Form::label('part_height', __('Height', 'parts')) ?></td>
      <td><?php echo Form::label('part_length', __('Length', 'parts')) ?></td>
     </tr>
     <tr>
      <td><?php echo Form::input('part_width', $part_width, array('class' => 'span6', 'style' => 'width:90px', 'id' => 'part_width')) ?></td>
      <td><?php echo Form::input('part_height', $part_height, array('class' => 'span6', 'style' => 'width:90px', 'id' => 'part_height')) ?></td>
      <td><?php echo Form::input('part_length', $part_length, array('class' => 'span6', 'style' => 'width:90px', 'id' => 'part_length')) ?></td>
     </tr>
     </table>
   </td>
   <td style="width:10px"></td>
   <td>
     <table>
     <tr>
       <td><?php echo Form::label('part_pounds', __('Pounds', 'parts')) ?></td>
       <td><?php echo Form::label('part_ounces', __('Ounces', 'parts')) ?></td>
     </tr>
     <tr>
       <td><?php echo Form::input('part_pounds', $part_pounds, array('class' => 'span6', 'style' => 'width:90px', 'id' => 'part_pounds')) ?></td>
       <td><?php echo Form::input('part_ounces', $part_ounces, array('class' => 'span6', 'style' => 'width:90px', 'id' => 'part_ounces')) ?></td>
     </tr>
     </table>
   </td>
  </tr>
  </table>
 <div>
</div>
<br/>
<div class="control-group">
 <div class="controls">
  <table>
  <tr style="vertical-align:top">
   <td style="width:320px">
<?php
  echo Form::checkbox('part_separate_box', 'on', $part_separate_box, array('id' => 'part_separate_box'));
  echo Html::nbsp();
  echo Form::label('part_separate_box', __('Ship in separate box', 'parts'), array('style' => 'display:inline'));
?>
   </td>
   <td style="width:10px"></td>
   <td>
<?php
 echo Form::label('part_items_per_box', __('Quantity per shipping box', 'parts'));
 echo Form::input('part_items_per_box', $part_items_per_box, array('class' => 'span6', 'id' => 'part_items_per_box', 'style' => 'width:90px'));
?>
   </td>
  </tr>
  </table>
 </div>
</div>
<div class="control-group">
<?php
 echo (
       Html::br().
       Form::label(null, __('Part Prices', 'parts'))
      );
?>
<div class="controls">
<table class="table table-bordered" id="prices" style="width:550px">
 <thead>
  <tr>
   <td>Price Type</td>
   <td>Value</td>
   <td>Action</td>
  </tr>
 </thead>
 <tbody>
<?php
 foreach ($part_prices as $part_price_id => $part_price_type_id_value)
 {
?>
<tr>
  <td>
<?php
   echo Form::hidden('part_price[]', $part_price_id);

   if (strpos($part_price_id, 'new') === false)
   {
     echo Form::hidden('part_price_type[]', $part_price_type_id_value['price_type_id']);
     echo Form::label(null, $price_type_descriptions[$part_price_type_id_value['price_type_id']]);
   }
   else
   {
     if (isset($prices_errors[$part_price_type_id_value['price_type_id']]))
     {
       echo '<div class="control-group error">';
     }
     echo Form::select('part_price_type[]', $price_type_descriptions, $part_price_type_id_value['price_type_id']);
     if (isset($prices_errors[$part_price_type_id_value['price_type_id']]))
     {
       echo '&nbsp;<span class="error">*</span>';
     }
   }
?>
  </td>
  <td>
<?php
   echo Form::input('part_price_value[]', sprintf('%.2f', $part_price_type_id_value['value']), array('class' => 'span6', 'style' => 'width:100px'));
?>
 </td>
 <td>
<?php 
   echo Html::anchor(__('Delete', 'parts'), '#', array('class' => 'btn btn-actions btn-actions-default', 'onclick' => 'return $.store.parts.deletePrice(this)'))
?>
 </td>
</tr>
<?php
 }
?>
 <tr>
  <td colspan="3" style="text-align:center">
<?php echo Html::anchor(__('Add...', 'parts'), '#', array('class' => 'btn btn-actions', 'onclick' => 'return $.store.parts.addPrice(this)'))?>
  </td>
 </tr>
 </tbody>
</table>
<?php
 if (count($prices_errors) > 0)
 {
?>
<div><span class="error">*&nbsp;Price(s) already exists.</span></div>
<br/>
<?php
 }
?>
</div>
</div>
<?php
    echo (
        Form::submit('edit_part_and_exit', __('Save and exit', 'parts'), array('class' => 'btn')) . Html::nbsp(2) .
        Form::submit('edit_part', __('Save', 'parts'), array('class' => 'btn')) . Html::nbsp(2) .
        Form::submit('cancel', __('Cancel', 'parts'), array('class' => 'btn')) .
        Form::close()
    );
?>
<form>
<input type="hidden" name="url" value="<?php echo Option::get('siteurl') ?>admin/index.php?id=parts">
</form>
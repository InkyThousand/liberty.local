<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<?php
 if ($page_error)
 {
?>
<tr>
  <td colspan="3"><?php echo Html::toText($page_error) ?></td>
</tr>
<?php
   exit;
 }
?>
<tr>
  <td>
<?php
 echo Form::hidden('part_price[]', 'new' . md5(rand()));
 echo Form::select('part_price_type[]', $price_type_descriptions);
?>
  </td>
  <td><?php echo Form::input('part_price_value[]', null, array('class' => 'span6', 'style' => 'width:100px')) ?></td>
  <td>
   <div class="btn-toolbar">
    <div class="btn-group">
<?php echo Html::anchor(__('Delete'), '#', array('class' => 'btn btn-actions', 'onclick' => 'return $.store.parts.deletePrice(this)')) ?>
    </div>
   </div>
  </td>
</tr>
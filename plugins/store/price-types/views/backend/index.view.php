<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<h2><?php echo __('Price Types', 'price-types'); ?></h2>
<br/>
<?php
 if (isset($errors['page_error']))
 {
   Alert::error($errors['page_error'], 0);
 }
 else
 {
   if (Notification::get('success')) Alert::success(Notification::get('success'));
   if (Notification::get('error')) Alert::error(Notification::get('error'), 0);

   echo Html::anchor(__('<i class="icon-plus"></i> Add new price type', 'price-types'), 'index.php?id=price-types&action=add_price_type', array('title' => __('Add new price type', 'price-types'), 'class' => 'btn'));
?>
<br/>
<br/>
<table class="table table-bordered">
    <thead>
        <tr>
            <th><?php echo __('#', 'price-types') ?></th>
            <th><?php echo __('Description', 'price-types') ?></th>
            <th><?php echo __('Sign', 'price-types') ?></th>
            <th><?php echo __('Currency', 'price-types') ?></th>
            <th width="40%"><?php echo __('Actions', 'price-types') ?></th>
        </tr>
    </thead>
    <tbody>
<?php
   if (count($price_types)) 
   { 
     $counter = 1;

     foreach ($price_types as $price_type)
     {
?>
     <tr>
        <td>
            <?php echo Html::toText($counter++) ?>
        </td>
        <td>
            <?php echo Html::toText($price_type['description']) ?>
        </td>
        <td>
            <?php echo Html::toText($price_type['sign']) ?>
        </td>
        <td>
            <?php echo Html::toText($price_type['currency']) ?>
        </td>
        <td>
            <?php echo Html::anchor(__('<i class="icon-edit"></i> Edit', 'price-types'), 'index.php?id=price-types&action=edit_price_type&price_type_id='.urlencode($price_type['id']), array('class' => 'btn btn-actions')); ?>
            <?php echo Html::anchor(__('<i class="icon-trash"></i> Delete', 'price-types'), 'index.php?id=price-types&action=delete_price_type&price_type_id='.urlencode($price_type['id']), array('class' => 'btn btn-actions', 'onclick' => "return confirmDelete('".__('Delete price type \":price_type\"', 'price-types', array(':price_type' => $price_type['description']))."')")); ?>
        </td>
     </tr> 
<?php
     } 
   }
?>
    </tbody>
</table>
<?php
 }
?>
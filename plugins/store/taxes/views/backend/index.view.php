<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<h2><?php echo __('Shipping Taxes', 'taxes'); ?></h2>
<br />
<?php
 if (Notification::get('success')) Alert::success(Notification::get('success'));
 if (Notification::get('error')) Alert::error(Notification::get('error'));

 echo Html::anchor(__('<i class="icon-plus"></i> Add new', 'taxes'), 'index.php?id=taxes&action=add_tax', array('title' => __('Add new shipping tax', 'taxes'), 'class' => 'btn'));
?>
<br /><br />
<table class="table table-bordered">
    <thead>
        <tr>
            <th><?php echo __('#', 'taxes'); ?></th>
            <th><?php echo __('Description', 'taxes'); ?></th>
            <th><?php echo __('Display', 'taxes'); ?></th>
            <th><?php echo __('Country', 'taxes'); ?></th>
            <th><?php echo __('State', 'taxes'); ?></th>
            <th><?php echo __('Mode', 'taxes'); ?></th>
            <th><?php echo __('Value, %', 'taxes'); ?></th>
            <th width="40%"><?php echo __('Actions', 'taxes'); ?></th>
        </tr>
    </thead>
    <tbody>
<?php
 if (count($taxes) != 0) 
 { 
   $counter = 1;

   foreach ($taxes as $tax)
   {
?>
     <tr>
        <td>
            <?php echo Html::toText($counter++) ?>
        </td>
        <td>
            <?php echo Html::toText($tax['description']) ?>
        </td>
        <td>
            <?php echo Html::toText($tax['display']) ?>
        </td>
        <td>
            <?php echo Html::toText($tax['country']) ?>
        </td>
        <td>
            <?php echo Html::toText($tax['state']) ?>
        </td>
        <td>
            <?php echo Html::toText($tax['mode']) ?>
        </td>
        <td>
            <div class="text-center"><?php echo Html::toText($tax['value']) ?></div>
        </td>
        <td>
            <?php echo Html::anchor(__('<i class="icon-edit"></i> Edit', 'taxes'), 'index.php?id=taxes&action=edit_tax&tax_id='.urlencode($tax['id']), array('class' => 'btn btn-actions')); ?>
            <?php echo Html::anchor(__('<i class="icon-trash"></i> Delete', 'taxes'), 'index.php?id=taxes&action=delete_tax&tax_id='.urlencode($tax['id']).'&token='.Security::token(), array('class' => 'btn btn-actions', 'onclick' => "return confirmDelete('".__('Delete tax: :tax', 'taxes', array(':tax' => Html::toText($tax['description'])))."')")); ?>
        </td>
     </tr> 
<?php
   } 
 }
?>
    </tbody>
</table>
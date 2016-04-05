<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<h2><?php echo __('Distributors', 'distributors'); ?></h2>
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

   echo Html::anchor(__('Import distributors', 'distributors'), 'index.php?id=distributors&action=import', array('title' => __('Import distributors', 'distributors'), 'class' => 'btn default btn-small'));
?>
<br/>
<br/>
<table class="table table-bordered">
    <thead>
        <tr>
            <th><?php echo __('#', 'distributors') ?></th>
            <th><?php echo __('User Id', 'distributors') ?></th>
            <th><?php echo __('Customer Id', 'distributors') ?></th>
            <th><?php echo __('Password', 'distributors') ?></th>
            <th><div class="text-center"><?php echo __('Approved', 'distributors') ?></div></th>
            <th><?php echo __('Price Level', 'distributors') ?></th>
        </tr>
    </thead>
    <tbody>
<?php
   if (count($distributors)) 
   { 
     $counter = 1;

     foreach ($distributors as $distributor)
     {
?>
     <tr>
        <td><?php echo Html::toText($counter++); ?></td>
        <td>
            <?php echo Html::toText($distributor['user_id']); ?>
        </td>
        <td>
            <?php echo Html::toText($distributor['customer_id']); ?>
        </td>
        <td>
            <?php echo Html::toText($distributor['password']); ?>
        </td>
        <td><div class="text-center">
            <?php echo $distributor['approved'] ? '<font color="green">YES</font>' : '<font color="red">NO</font>' ?>
            </div>
        </td>
        <td>
            <?php echo Html::toText(Valid::hasValue($distributor['price_level']) ? $distributor['price_level'] : 'No Price') ?>
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
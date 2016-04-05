<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<h2><?php echo __('Countries and States', 'countries') ?></h2>
<br/>
<h3><?php echo __('Country', 'countries') ?>:&nbsp;<i><?php echo Html::toText($country_name) ?></i></h3>
<br/>
<?php
 if (isset($error))
 {
   Alert::error($error, 0);
   echo Html::anchor(__('Back', 'countries'), 'index.php?id=countries&region='.urlencode($region), array('class' => 'btn btn-actions'));
 }
 else
 {
   if (Notification::get('success')) Alert::success(Notification::get('success'));
   echo Html::anchor(__('<i class="icon-arrow-left"></i> Back to Countries', 'countries'), 'index.php?id=countries&region='.urlencode($region), array('class' => 'btn', 'style' => 'float:left'));
   echo Html::anchor(__('<i class="icon-plus"></i> Add new state', 'countries'), 'index.php?id=countries&action=add_state&region='.urlencode($region).'&country='.urlencode($country_code), array('class' => 'btn', 'style' => 'margin-left:10px'));
?>
<br/>
<br/>
<table class="table table-bordered">
    <thead>
        <tr>
            <th><?php echo __('Code', 'countries') ?></th>
            <th><?php echo __('Name', 'countries') ?></th>
            <th width="40%"><?php echo __('Actions', 'countries') ?></th>
        </tr>
    </thead>
    <tbody>
<?php
   if (count($states) != 0)
   {
     foreach ($states as $state)
     {
?>
     <tr>
        <td>
            <?php echo Html::toText($state['code']) ?>
        </td>
        <td>
            <?php echo Html::toText($state['name']) ?>
        </td>
        <td>
           <div class="btn-toolbar">
            <div class="btn-group">
             <?php echo Html::anchor(__('<i class="icon-edit"></i> Edit', 'countries'), 'index.php?id=countries&action=edit_state&region='.urlencode($region).'&country='.urlencode($country_code).'&state_id='.urlencode($state['id']), array('class' => 'btn btn-actions')); ?>
             <ul class="dropdown-menu"></ul>
             <?php echo Html::anchor(__('<i class="icon-trash"></i> Delete', 'countries'),
                        'index.php?id=countries&action=delete_state&region='.urlencode($region).'&country='.urlencode($country_code).'&state_id='.$state['id'].'&token='.Security::token(),
                        array('class' => 'btn btn-actions btn-actions-default', 'onclick' => "return confirmDelete('".__("Delete state: :state", 'countries', array(':state' => Html::toText($state['name'])))."')"));
             ?>
            </div>
           </div>
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
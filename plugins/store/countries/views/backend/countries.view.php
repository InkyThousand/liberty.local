<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<h2><?php echo __('Countries and States', 'countries') ?></h2>
<br/>
<?php
 if (Notification::get('success')) Alert::success(Notification::get('success'));
 if (Notification::get('error')) Alert::error(Notification::get('error'));

 foreach ($regions as $r)
 {
   echo '<div style="float:left"><span style="white-space:nowrap;padding-right:20px">';

   if ($r['region'] == $region)
   {
     echo '<b>' . __($r['title'], 'countries') . '</b>';
   }
   else
   {
     echo Html::anchor(__($r['title'], 'countries'), 'index.php?id=countries&region=' . urlencode($r['region']));
   }

   echo '</span></div>';
 }

 echo Form::open('index.php?id=countries&action=update_countries&region='.urlencode($region)).
      Form::hidden('csrf', Security::token());
?>
<div style="float:right"><a id="checkAll" href="javascript:void(0)">Check all</a> / <a id="uncheckAll" href="javascript:void(0)">Uncheck all</a></div>
<br/>
<br/>
<table class="table table-bordered">
    <thead>
        <tr>
            <th><?php echo __('Code', 'countries') ?></th>
            <th><?php echo __('Country', 'countries') ?></th>
            <th><div class="text-center"><?php echo __('Active', 'countries') ?></div></th>
            <th width="40%"><?php echo __('Actions', 'countries') ?></th>
        </tr>
    </thead>
    <tbody>
<?php
 if (count($countries) != 0)
 {
   foreach ($countries as $country)
   {
?>
     <tr>
        <td>
            <?php echo Html::toText($country['code']) ?>
        </td>
        <td>
            <?php echo Html::toText($country['name']) ?>
        </td>
        <td>
          <div class="text-center">
            <?php 
              echo Form::hidden('country_data[' . $country['id'] . '][id]', $country['id']);
              echo Form::checkbox('country_data[' . $country['id'] . '][active]', null, $country['active']);
            ?>
          </div>
        </td>
        <td>
           <div class="btn-toolbar">
            <div class="btn-group">
             <?php echo Html::anchor(__('<i class="icon-edit"></i> Edit', 'countries'), 'index.php?id=countries&action=edit_country&region='.urlencode($region).'&country_id='.urlencode($country['id']), array('class' => 'btn btn-actions')) ?>
             <ul class="dropdown-menu"></ul>
             <?php echo Html::anchor(__('Edit States', 'countries'), 'index.php?id=countries&region='.urlencode($region).'&country='.urlencode($country['code']), array('class' => 'btn btn-actions')) ?>
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
<div style="text-align:right"><?php echo Form::submit('update_countries', __('Update', 'countries'), array('class' => 'btn')) ?></div>
<?php
  echo Form::close();
?>
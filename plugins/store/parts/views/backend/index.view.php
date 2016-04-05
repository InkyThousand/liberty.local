<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<h2><?php echo __('Parts', 'parts')?></h2>
<br/>
<?php
 if ($page_error)
 {
   Alert::error($page_error, 0);
 }
 else
 {
   if (Notification::get('success')) Alert::success(Notification::get('success'));

   echo Html::anchor(__('Import part data', 'parts'), 'index.php?id=parts&action=import', array('title' => __('Import part data', 'parts'), 'class' => 'btn'));
?>
<br/>
<?php
   if ($records_total)
   {
?>
<br/>
<table>
<tr>
 <td><?php echo __('Total records', 'visitors') ?>:&nbsp;<b><?php echo Html::toText($records_total) ?></b></td>
</tr>
<tr>
 <td><?php echo __('Displaying records from', 'visitors') ?>&nbsp;<b><?php echo Html::toText($records_start) ?></b>&nbsp;<?php echo __('to', 'visitors') ?>&nbsp;<b><?php echo Html::toText($records_end) ?></b></td>
</tr>
</table>
<?php
   }
?>
<br/>
<table class="table table-bordered">
    <thead>
        <tr>
            <th><?php echo __('#', 'parts'); ?></th>
            <th><?php 
                 if ($records_total)
                 {
                   echo Html::anchor(__('Code', 'parts'), 'index.php?id=parts&s=code&d=' . ($sort_field == $sort_fields[0] ? ($sort_direction == $sort_directions[0] ? $sort_directions[1] : $sort_directions[0]) : $sort_directions[0]) . ($pages_number == 'all' ? '&page=all' : null));
                   if ($sort_field == $sort_fields[0])
                   {
                     echo Html::nbsp() . Html::arrow($sort_direction == $sort_directions[1] ? 'up' : 'down');
                   }
                 }
                 else
                 {
                   echo __('Code', 'parts');
                 }
                ?>
            </th>
            <th><?php 
                 if ($records_total)
                 {
                   echo Html::anchor(__('Description', 'parts'), 'index.php?id=parts&s=description&d=' . ($sort_field == $sort_fields[1] ? ($sort_direction == $sort_directions[0] ? $sort_directions[1] : $sort_directions[0]) : $sort_directions[0]) . ($pages_number == 'all' ? '&page=all' : null));
                   if ($sort_field == $sort_fields[1])
                   {
                     echo Html::nbsp() . Html::arrow($sort_direction == $sort_directions[1] ? 'up' : 'down');
                   }
                 }
                 else
                 {
                   echo __('Description', 'parts');
                 }
                ?>
            </th>
            <th><?php echo __('Dimensions (W&nbsp;x&nbsp;H&nbsp;x&nbsp;L)', 'parts'); ?></th>
            <th><div class="text-center"><?php echo __('Weight', 'parts'); ?></div></th>
            <th><div class="text-center"><?php echo __('Price', 'parts'); ?></div></th>
            <th width="40%"><?php echo __('Actions', 'parts'); ?></th>
        </tr>
    </thead>
    <tbody>
<?php
   if (count($parts) != 0) 
   { 
     $not_set = '<span style="color:red;font-weight:bold">NOT SET</span>';
     $missing = '<span style="color:red;font-weight:bold">MISSING</span>';

     $counter = $records_start;

     foreach ($parts as $part)
     {
       $weight = $not_set;
       $dimensions = $not_set;

       if ($part['pounds'] || $part['ounces'])
       {
         $weight = $part['pounds'] ? $part['pounds'] . '&nbsp;' . __('lb', 'parts') : null;

         if ($part['ounces'])
         {
           $weight .= ' ' . Html::toText($part['ounces']) . '&nbsp;' . __('oz', 'parts');
         }
       }

       if ($part['width'] || $part['height'] || $part['length'])
       {
         $dimensions = ($part['width'] ? Html::toText($part['width']) : $not_set) . '&nbsp;x&nbsp;' .
                       ($part['height'] ? Html::toText($part['height']) : $not_set) . '&nbsp;x&nbsp;' .
                       ($part['length'] ? Html::toText($part['length']) : $not_set) . '&nbsp;in';
       }
?>
     <tr>
        <td>
            <?php echo Html::toText($counter++) ?>
        </td>
        <td>
            <?php echo Html::toText($part['code']) ?>
        </td>
        <td>
            <?php echo Html::toText($part['description']) ?>
        </td>   
        <td>
            <?php echo $dimensions ?>
        </td>
        <td style="text-align:center">
            <?php echo $weight ?>
        </td>
        <td style="text-align:center">
<?php
       if (count($part['prices']) > 0)
       {
         foreach ($part['prices'] as $price)
         {
           echo Html::toText($price['sign'] . $price['value']);
           echo '<br/>';
         }
       }
       else
       {
         echo $missing;
       }
?>
        </td>
        <td>
            <div class="btn-toolbar">
                <div class="btn-group">
                    <?php echo Html::anchor(__('<i class="icon-edit"></i> Edit', 'parts'), 'index.php?id=parts&action=edit&part_id=' . $part['id'], array('class' => 'btn btn-actions')); ?>
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
   if ($records_total)
   {
     $link_href = 'index.php?id=parts' . ($sort_field ? '&s=' . $sort_field . ($sort_direction ? '&d=' . $sort_direction : null) : null) . '&page=';
?>
<div class="adm-nav-pages-block">
<?php
     if (Valid::isInteger($pages_number) && $pages_number > 1)
     {
?>
 <a class="adm-nav-page adm-nav-page-prev" href="<?php echo $link_href . ($pages_number - 1) ?>"><span class="adm-subnav-page-prev-before"></span></a>
<?php
     }
     else
     {
?>
 <span class="adm-nav-page adm-nav-page-prev"></span>
<?php
     }

     $pages_window = 5;

     if ($pages_number > floor($pages_window/2) + 1 && $pages_total > $pages_window)
     {
       $start_page = $pages_number - floor($pages_window/2);
     }
     else
     {
       $start_page = 1;
     }
     
     if ($pages_number <= $pages_total - floor($pages_window/2) && $start_page + $pages_window-1 <= $pages_total)
     {
         $end_page = $start_page + $pages_window - 1;
     }
     else
     {
         $end_page = $pages_total;
         if ($end_page - $pages_window + 1 >= 1)
         {
           $start_page = $end_page - $pages_window + 1;
         }
     }

     $counter = 1;

     while ($counter <= $pages_total)
     {
       if ($pages_number == $counter)
       {
?>
 <span class="adm-nav-page-active adm-nav-page"><?php echo $counter ?></span>
<?php
       }
       else
       {
?>
 <a href="<?php echo $link_href . $counter ?>" class="adm-nav-page"><?php echo $counter ?></a>
<?php
       }

       if ($counter == 2 && $start_page > 3)
       {
         if ($start_page - $counter > 1)
         {
           $middle_page = ceil(($start_page + $counter)/2);
?>
        <a href="<?php echo $link_href . $middle_page ?>" class="adm-nav-page-separator"><?php echo $middle_page?></a>
<?php
         }
         $counter = $start_page;
        }
        elseif($counter == $end_page && $end_page < $pages_total - 2)
        {
          if($pages_total - 1 - $counter > 1)
          {
            $middle_page = floor(($pages_total + $end_page - 1 ) / 2);
?>
        <a href="<?php echo $link_href . $middle_page ?>" class="adm-nav-page-separator"><?php echo $middle_page?></a>
<?php
          }
          $counter = $pages_total - 1;
        }
        else
        {
          $counter++;
        }
     }

     if ($pages_total != 1)
     {
       if ($pages_number == 'all')
       {
?>
 <span class="adm-nav-page-active adm-nav-page"><?php echo __('All', 'visitors') ?></span>
<?php
       }
       else
       {
?>
 <a href="<?php echo $link_href . 'all' ?>" class="adm-nav-page"><?php echo __('All', 'visitors') ?></a>
<?php
       }
     }
     if (Valid::isInteger($pages_number) && $pages_number != $pages_total)
     {
?>
 <a class="adm-nav-page adm-nav-page-next" href="<?php echo $link_href . ($pages_number + 1) ?>"></a>
<?php
     }
     else
     {
?>
 <span class="adm-nav-page adm-nav-page-next"></span>
<?php
     }
?>
</div>
<?php
   }
 }
?>
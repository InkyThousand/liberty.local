<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<?php 
 switch ($mode)
 {
   case       'new': 
                     $title = __('New Orders', 'orders'); 
                     break;
   case 'processed': 
                     $title = __('Processed Orders', 'orders'); 
                     break;
   case  'archived': 
                     $title = __('Archived Orders', 'orders');
                     break;
 }
?>
<h2><?php echo $title?></h2>
<br/>
<?php
 if (isset($errors['page_error']))
 {
   Alert::error($errors['page_error'], 0);
 }
 else
 {
   if (Notification::get('success')) Alert::success(Notification::get('success'));
   if (Notification::get('error')) Alert::error(Notification::get('error'));

   if ($records_total)
   {
       echo Form::open('index.php?id=orders&mode=' . $mode);
       echo Form::hidden('csrf', Security::token());
?>
<div style="float:right"><a id="checkAll" href="javascript:void(0)">Select all</a> / <a id="uncheckAll" href="javascript:void(0)">Unselect all</a></div>
<table>
<tr>
 <td><?php echo __('Total records', 'orders') ?>:&nbsp;<b><?php echo Html::toText($records_total) ?></b></td>
</tr>
<tr>
 <td><?php echo __('Displaying records from', 'orders') ?>&nbsp;<b><?php echo Html::toText($records_start) ?></b>&nbsp;<?php echo __('to', 'orders') ?>&nbsp;<b><?php echo Html::toText($records_end) ?></b></td>
</tr>
</table>
<br/>
<?php
   }
?>
<table class="table table-bordered">
    <thead>
        <tr>
            <th><?php echo __('#', 'orders'); ?></th>
            <th>
                <div class="text-center">
<?php 
   if ($records_total)
   {
     echo Html::anchor(__('Date', 'orders'), 'index.php?id=orders&mode=' . $mode . '&s=' . $sort_fields[0] . '&d=' . ($sort_field == $sort_fields[0] ? ($sort_direction == $sort_directions[0] ? $sort_directions[1] : $sort_directions[0]) : $sort_directions[0]) . ($pages_number == 'all' ? '&page=all' : null)); 
     if ($sort_field == $sort_fields[0])
     {
       echo Html::nbsp() . Html::arrow($sort_direction == $sort_directions[1] ? 'up' : 'down');
     }
   }
   else
   {
     echo __('Date', 'orders');
   }
?>
                </div>
            </th>
            <th>
                <div class="text-center">
<?php 
   if ($records_total)
   {
     echo Html::anchor(__('Id', 'orders'), 'index.php?id=orders&mode=' . $mode . '&s=' . $sort_fields[1] . '&d=' . ($sort_field == $sort_fields[1] ? ($sort_direction == $sort_directions[0] ? $sort_directions[1] : $sort_directions[0]) : $sort_directions[0]) . ($pages_number == 'all' ? '&page=all' : null)); 
     if ($sort_field == $sort_fields[1])
     {
       echo Html::nbsp() . Html::arrow($sort_direction == $sort_directions[1] ? 'up' : 'down');
     }
   }
   else
   {
     echo __('Id', 'orders');
   }
?>
                </div>
            </th>
            <th>
                <div class="text-center">
<?php 
   if ($records_total)
   {
     echo Html::anchor(__('Number', 'orders'), 'index.php?id=orders&mode=' . $mode . '&s=' . $sort_fields[2] . '&d=' . ($sort_field == $sort_fields[2] ? ($sort_direction == $sort_directions[0] ? $sort_directions[1] : $sort_directions[0]) : $sort_directions[0]) . ($pages_number == 'all' ? '&page=all' : null)); 
     if ($sort_field == $sort_fields[2])
     {
       echo Html::nbsp() . Html::arrow($sort_direction == $sort_directions[1] ? 'up' : 'down');
     }
   }
   else
   {
     echo __('Number', 'orders');
   }
?>
                </div>
            </th>
            <th><div class="text-center"><?php echo __('Total Amount', 'orders'); ?></div></th>
<?php 
   if ($mode == 'archived')
   {
?>
            <th>
                <div class="text-center">
<?php
     if ($records_total)
     {
       echo Html::anchor(__('Status', 'orders'), 'index.php?id=orders&mode=' . $mode . '&s=' . $sort_fields[3] . '&d=' . ($sort_field == $sort_fields[3] ? ($sort_direction == $sort_directions[0] ? $sort_directions[1] : $sort_directions[0]) : $sort_directions[0]) . ($pages_number == 'all' ? '&page=all' : null)); 
       if ($sort_field == $sort_fields[3])
       {
         echo Html::nbsp() . Html::arrow($sort_direction == $sort_directions[1] ? 'up' : 'down');
       }
     }
     else
     {
       echo __('Status', 'orders');
     }
?>
                </div>
            </th>
<?php
   }
?>
            <th><div class="text-center"><?php echo __('Select', 'orders'); ?></div></th>
            <th width="40%"><?php echo __('Actions', 'orders'); ?></th>
        </tr>
    </thead>
    <tbody>
<?php
   if (count($orders)) 
   { 
     $counter = $records_start;

     foreach ($orders as $order)
     {
?>
     <tr id="row<?php echo $order['id'] ?>">
        <td>
            <?php echo Html::toText($counter++); ?>
        </td>
        <td style="text-align:center">
            <?php echo Html::toText($order['date']); ?>
        </td>
        <td style="text-align:center">
            <?php echo Html::toText($order['order_id']); ?>
        </td>
        <td style="text-align:center">
            <?php echo Html::toText($order['number']); ?>
        </td>
        <td style="text-align:center">
            <?php echo Html::toText($order['amount']); ?>
        </td>
<?php
       if ($mode == 'archived')
       {
?>
        <td style="text-align:center">
            <div id="order<?php echo $order['id'] ?>"><?php echo Html::toText($order['status']); ?></div>
        </td>
<?php
       }

?>

        <td style="text-align:center">
            <?php echo Form::checkbox('order_id[]', $order['id']) ?>
        </td>
        <td>
            <div class="btn-toolbar">
                <div class="btn-group">
                    <?php echo Html::anchor(__('Details', 'orders'), 'index.php?id=orders&mode='.$mode.'&action=details&order_id='.$order['id'], array('class' => 'btn btn-actions')) ?>
<?php
       if ($mode != 'archived')
       {
         if ($order['status'] == 'new')
         {
                    ?>
                    <noscript>
                    <ul class="dropdown-menu"></ul>
                    <?php echo Html::anchor(__('Mark as processed', 'orders'), 'index.php?id=orders&mode=' . $mode . '&action=mark_processed&order_id=' . $order['id'] . '&token=' . Security::token(), array('class' => 'btn btn-actions', 'title' => __('Mark as processed', 'orders'))) ?>
                    </noscript>
                    <a class="btn dropdown-toggle btn-actions" data-toggle="dropdown" href="#" style="display:none" rel="order<?php echo $order['id']?>"><span class="caret"></span></a>
                    <ul class="dropdown-menu">
                      <li><?php echo Html::anchor(__('Mark as processed', 'orders'), 
                                                 'index.php?id=orders&mode=' . $mode . '&action=mark_processed&order_id=' . $order['id'] . '&token=' . Security::token(),
                                                 array('title' => __('Mark as processed', 'orders'),
                                                       'onclick' => "return markOrderProcessed('" . $order['id'] . "', '".__('Mark order #:order_number as processed', 'orders', array(':order_number' => $order['order_number'])) . "', '" . Security::token() . "')")) ?></li>
                    </ul>
<?php
         }
       }
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
   if ($records_total)
   {
?>
<div style="text-align:right">
<?php
     switch ($mode)
     {
       case       'new': 
                         $orders_actions = array('' => 'With selected', 'mark_processed' => 'Mark as processed', 'archive' => 'Archive', 'delete' => 'Delete permanently');
                         break;
       case 'processed':
                         $orders_actions = array('' => 'With selected', 'archive' => 'Archive', 'delete' => 'Delete permanently');
                         break;
       case  'archived':
                         $orders_actions = array('' => 'With selected', 'delete' => 'Delete permanently');
                         break;
     }
     echo Form::select('action', $orders_actions, null);
     echo Html::nbsp(2);
     echo Form::submit('update_orders', __('Submit', 'orders'), array('class' => 'btn', 'style' => 'margin-top:-10px'))
?>
</div>
<?php
     echo Form::close();

     $link_href = 'index.php?id=orders&amp;mode=' . $mode . ($sort_field ? '&amp;s=' . $sort_field . ($sort_direction ? '&amp;d=' . $sort_direction : null) : null) . '&amp;page=';
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
?>
<form>
 <input type="hidden" name="siteurl" value="<?php echo Option::get('siteurl') ?>admin/index.php?id=orders&mode=<?php echo $mode ?>">
</form>
<?php
 }
?>
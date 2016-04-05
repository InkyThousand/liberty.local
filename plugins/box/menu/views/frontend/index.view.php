<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<?php

    $anchor_active = '';
    $li_active = '';
    $target = '';
    $events = '';

    if (count($items) > 0) 
    {
        foreach ($items as $item) 
        {
          $item['link'] = Html::toText($item['link']);
          $item['name'] = Html::toText($item['name']);

          $parts_store = !(strpos($item['link'], 'parts-store') === false);
          $pending_orders = !(strpos($item['link'], 'orders') === false);
          $order_details = !(strpos($item['link'], 'orders/details') === false);

          if ($pending_orders || $parts_store)
          {
            if ($parts_store)
            {
              $params = Dealership::getDealershipLinkParams();

              echo '<form id="orderparts" method="get" action="' . $params['url'] . '">';
              echo '<input type="hidden" name="sysname" value="' . Html::toText($params['sysname']) . '"/>';
              echo '<input type="hidden" name="' . Html::toText($params['passkey_name']) . '" value="' . Html::toText($params['passkey_value']) . '"/>';
              echo '<input type="hidden" name="company" value="' . Html::toText($params['company']) . '"/>';
              echo '<input type="hidden" name="doorback" value="' . Html::toText($params['doorback']) . '"/>';
              echo '<input type="hidden" name="sendconfirmation" value="' . Html::toText($params['sendconfirmation']) . '"/>';
              echo '<input type="hidden" name="sendnotification" value="' . Html::toText($params['sendnotification']) . '"/>';
              echo '</form>';
              
              $link = '/';
              $events = ' onclick="document.getElementById(\'orderparts\').submit(); return false"';
            }
            else
            {
              if ($pending_orders || $order_details)
              {
                if (!Orders::checkPendingOrders())
                {
                  continue;
                }
              }

              $link = Option::get('siteurl') . $item['link'];
            }
          }
          else
          {
            $pos = strpos($item['link'], '://');
            if ($pos === false) {
                $link = Option::get('siteurl').$item['link'];
            } else {
                $link = $item['link'];
            }

            if (isset($uri[1])) {
                $child_link = explode("/",$item['link']);
                if (isset($child_link[1])) {
                    if (in_array($child_link[1], $uri)) {
                        $anchor_active = ' class="current" ';
                    }
                }
            }

            if (isset($uri[0]) && $uri[0] !== '') {
                if (in_array($item['link'], $uri)) {
                    $anchor_active = ' class="current" ';
                }
            } else {
                if ($defpage == trim($item['link'])) {
                    $anchor_active = ' class="current" ';
                }
            }

            if (trim($item['target']) !== '') {
                $target = ' target="'.$item['target'].'" ';
            }
          }

          echo '<a href="'.$link.'"'.$anchor_active.$target.$events.'>'.$item['name'].'</a>';

          $anchor_active = '';
          $target = '';
          $events = '';
        }
    }
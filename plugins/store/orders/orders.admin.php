<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<?php
Navigation::add(array(
                    __('Orders', 'orders'), 
                    __('New', 'orders'), 
                    __('Processed', 'orders'), 
                    __('Archived', 'orders')
                   ), 'store', 
                   array('orders', 'new', 'processed', 'archived'), 9);

Action::add('admin_pre_render','OrdersAdmin::markOrderProcessed');

class OrdersAdmin extends Backend 
{
  public static function markOrderProcessed()
  {
    if (Request::isAjax())
    {
      if (Request::post('action') == 'mark_processed')
      {
        if (Security::check(Request::post('token')))
        {
          $order_id = Request::post('order_id');

          try
          {
            $query = 'UPDATE orders SET status = \'processed\' WHERE id = \'' . MySQL::escapeString($order_id) . '\'';
            MySQL::query($query);

            $code = 'ok';
          }
          catch(Exception $e)
          {
            $code = 'error';
          }

          echo '{' .
               '"code":' . json_encode($code) .
               '}';

          Request::shutdown();
        }
        else 
        { 
          die('csrf detected!'); 
        }
      }
    }
  }

  public static function main() 
  {        
    $mode = strtolower(Text::trimStr(Request::get('mode')));

    $modes = array('new', 'processed', 'archived');

    if (!in_array($mode, $modes))
    {
      $mode = $modes[0];
    }

    if (Request::post('cancel')) 
    {
      Request::redirect('index.php?id=orders&mode=' . $mode);
    }

    $errors = array();

    if (Request::post('action'))
    {
        switch (Request::post('action'))
        {
            case 'mark_processed':

             if (Security::check(Request::post('csrf')))
             {
                $ids_to_update = Request::post('order_id');

                if (is_array($ids_to_update) && count($ids_to_update))
                {
                  try
                  {
                    try
                    {
                      $query = 'UPDATE orders SET status = \'processed\' WHERE id IN (';

                      foreach ($ids_to_update as $id_to_update)
                      {
                        $query .= '\''. MySQL::escapeString($id_to_update)  . '\', ';
                      }

                      $query = substr($query, 0, strlen($query) - 2);

                      $query .= ')';

                      MySQL::query($query);

                      Notification::set('success', __('Orders was marked as processed.', 'orders'));
                    }
                    catch(Exception $e)
                    {
                      throw new Exception(__('Error changing orders status.', 'orders'));
                    }
                  }
                  catch(Exception $e)
                  {
                    Notification::set('error', $e->getMessage(), 'orders');
                  }
                }

                Request::redirect('index.php?id=orders&mode=new');
             }

             break;

          case 'archive':

             if (Security::check(Request::post('csrf')))
             {
                $ids_to_update = Request::post('order_id');

                if (is_array($ids_to_update) && count($ids_to_update))
                {
                  try
                  {
                    try
                    {
                      MySQL::startTransaction();

                      $query_orders = 'DELETE FROM orders WHERE id IN (';
                      $query_order_items = 'DELETE FROM order_items WHERE order_id IN (';
                      $query_order_shipping_details = 'DELETE FROM order_shipping_details WHERE order_id IN (';

                      foreach ($ids_to_update as $id_to_update)
                      {
                        $query_orders .= '\''. MySQL::escapeString($id_to_update)  . '\', ';
                        $query_order_items .= '\''. MySQL::escapeString($id_to_update)  . '\', ';
                        $query_order_shipping_details .= '\''. MySQL::escapeString($id_to_update)  . '\', ';

                        $query_orders_archive = 'INSERT INTO orders_archive(order_date, payload_id, number, price_type_id, language_code, subtotal, handling, '.
                                                'shipping_tax_mode, shipping_tax, shipping_tax_description, shipping_type, shipping_method, shipping_cost, '.
                                                'shipping_instructions, token, transaction_id, payment_type, payment_date, payment_status, total, xml_data, status) '.
                                                'SELECT order_date, payload_id, number, price_type_id, language_code, subtotal, handling, '.
                                                'shipping_tax_mode, shipping_tax, shipping_tax_description, shipping_type, shipping_method, shipping_cost, '.
                                                'shipping_instructions, token, transaction_id, payment_type, payment_date, payment_status, total, xml_data, status '.
                                                'FROM orders WHERE id = \'' . MySQL::escapeString($id_to_update) . '\'';

                        MySQL::query($query_orders_archive);

                        $order_archive_id = MySQL::getInsertId();

                        $query_order_items_archive = 'INSERT INTO order_items_archive(order_archive_id, part_id, quantity, price) SELECT \'' . MySQL::escapeString($order_archive_id) . '\', part_id, quantity, price '.
                                                     'FROM order_items WHERE order_id = \'' . MySQL::escapeString($id_to_update) . '\'';

                        MySQL::query($query_order_items_archive);

                        $query_order_shipping_details_archive = 'INSERT INTO order_shipping_details_archive(order_archive_id, full_name, address1, address2, city, '.
                                                                'postal_code, country_code, state_code, email, phone) SELECT \'' . MySQL::escapeString($order_archive_id) . '\', '.
                                                                'full_name, address1, address2, city, postal_code, country_code, state_code, email, phone '.
                                                                'FROM order_shipping_details WHERE order_id = \'' . MySQL::escapeString($id_to_update) . '\'';

                        MySQL::query($query_order_shipping_details_archive);
                      }

                      $query_orders = substr($query_orders, 0, strlen($query_orders) - 2);
                      $query_order_items = substr($query_order_items, 0, strlen($query_order_items) - 2);
                      $query_order_shipping_details = substr($query_order_shipping_details, 0, strlen($query_order_shipping_details) - 2);

                      $query_orders .= ')';
                      $query_order_items .= ')';
                      $query_order_shipping_details .= ')';

                      MySQL::query($query_orders);

                      MySQL::query($query_order_items);

                      MySQL::query($query_order_shipping_details);

                      MySQL::commitTransaction();

                      Notification::set('success', __('Orders was successfully moved to archive.', 'orders'));
                    }
                    catch(Exception $e)
                    {
                      try
                      {
                        MySQL::rollbackTransaction();
                      }
                      catch(Exception $ex)
                      {
                      }

                      throw new Exception(__('Error moving orders to archive.', 'orders'));
                    }
                  }
                  catch(Exception $e)
                  {
                    Notification::set('error', $e->getMessage(), 'orders');
                  }
                }

                Request::redirect('index.php?id=orders&mode=' . $mode);
             }

             break;

          case 'delete':

             if (Security::check(Request::post('csrf')))
             {
                $ids_to_update = Request::post('order_id');

                if (is_array($ids_to_update) && count($ids_to_update))
                {
                  try
                  {
                    try
                    {
                      if ($mode == 'archived')
                      {
                        $query_orders = 'DELETE FROM orders_archive WHERE id IN (';
                        $query_order_items = 'DELETE FROM order_items_archive WHERE order_archive_id IN (';
                        $query_order_shipping_details = 'DELETE FROM order_shipping_details_archive WHERE order_archive_id IN (';
                      }
                      else
                      {
                        $query_orders = 'DELETE FROM orders WHERE id IN (';
                        $query_order_items = 'DELETE FROM order_items WHERE order_id IN (';
                        $query_order_shipping_details = 'DELETE FROM order_shipping_details WHERE order_id IN (';
                      }

                      foreach ($ids_to_update as $id_to_update)
                      {
                        $query_orders .= '\''. MySQL::escapeString($id_to_update)  . '\', ';
                        $query_order_items .= '\''. MySQL::escapeString($id_to_update)  . '\', ';
                        $query_order_shipping_details .= '\''. MySQL::escapeString($id_to_update)  . '\', ';
                      }

                      $query_orders = substr($query_orders, 0, strlen($query_orders) - 2);
                      $query_order_items = substr($query_order_items, 0, strlen($query_order_items) - 2);
                      $query_order_shipping_details = substr($query_order_shipping_details, 0, strlen($query_order_shipping_details) - 2);

                      $query_orders .= ')';
                      $query_order_items .= ')';
                      $query_order_shipping_details .= ')';

                      try
                      {
                        MySQL::startTransaction();

                        MySQL::query($query_orders);

                        MySQL::query($query_order_items);

                        MySQL::query($query_order_shipping_details);

                        MySQL::commitTransaction();

                        Notification::set('success', __('Orders was successfully deleted.', 'orders'));
                      }
                      catch(Exception $e)
                      {
                        try
                        {
                          MySQL::rollbackTransaction();
                        }
                        catch(Exception $ex)
                        {
                        }

                        throw new Exception();
                      }
                    }
                    catch(Exception $e)
                    {
                      throw new Exception(__('Error deleting orders: ' . $e->getMessage(), 'orders'));
                    }
                  }
                  catch(Exception $e)
                  {
                    Notification::set('error', $e->getMessage(), 'orders');
                  }
                }

                Request::redirect('index.php?id=orders&mode=' . $mode);
             }

             break;

          default:
             break;
        }
    }

    if (Request::get('action'))
    {
        switch (Request::get('action'))
        {
            case 'mark_processed':

             if (Security::check(Request::get('token')))
             {
               $order_id = Request::get('order_id');

               try
               {
                 try
                 {
                   $query = 'SELECT id, payload_id, number FROM orders WHERE id = \'' . MySQL::escapeString($order_id) . '\'';
                   $row = MySQL::selectRow($query);
                 }
                 catch(Exception $e)
                 {
                   throw new Exception(__('Error retreiving order data.', 'orders'));
                 }

                 if ($row === null)
                 {
                   throw new Exception(__('Order was not found.', 'orders'));
                 }

                 $order_number = $row['number'] ? $row['number'] : $row['payload_id'];

                 try
                 {
                   $query = 'UPDATE orders SET status = \'processed\' WHERE id = \'' . MySQL::escapeString($order_id) . '\'';
                   MySQL::query($query);
                 }
                 catch(Exception $e)
                 {
                   throw new Exception(__('Error changing order status.', 'orders'));
                 }

                 Notification::set('success', __('Order <i>:order</i> was marked as processed.', 'orders', array(':order' => $order_number)));
               }
               catch(Exception $e)
               {
                 Notification::set('error', $e->getMessage(), 'orders');
               }

                Request::redirect('index.php?id=orders&mode=new');
             }
             else
             {
               die('csrf detected!'); 
             }

             break;

            case 'details':

             try
             {
               $order_id = Request::get('order_id');

               $order_details = array();
               $order_items = array();

               $orders_table = 'orders' . (Request::get('mode') == 'archived' ? '_archive' : null);
               $order_items_table = 'order_items' . (Request::get('mode') == 'archived' ? '_archive' : null);
               $order_shipping_details_table = 'order_shipping_details' . (Request::get('mode') == 'archived' ? '_archive' : null);

               try
               {
                 $query = 'SELECT * FROM ' . $orders_table . ' LEFT JOIN price_types ON price_types.id = orders.price_type_id WHERE ' . $orders_table . '.id = \'' . MySQL::escapeString($order_id) . '\'';
                 $row = MySQL::selectRow($query);
               }
               catch(Exception $e)
               {
                 throw new Exception(__('Error retreiving order data.', 'orders'));
               }

               if ($row === null)
               {
                 throw new Exception(__('Order was not found.', 'orders'));
               }

               $language_code = $row['language_code'];

               try
               {
                 $query = 'SELECT p.code, pl.description, oi.quantity, oi.price, oi.price * oi.quantity AS total_price FROM ' . $order_items_table . ' oi '.
                          'LEFT JOIN parts p ON p.id = oi.part_id LEFT JOIN part_localization pl ON pl.part_id = oi.part_id '.
                          'WHERE order_id = \'' . MySQL::escapeString($order_id) . '\'  AND pl.language_code = \'' . MySQL::escapeString($language_code) . '\'';

                 $rs_order_items = MySQL::query($query);
               }
               catch(Exception $e)
               {
                 throw new Exception(__('Error retreiving order details.', 'orders'));
               }

               try
               {
                 $query = 'SELECT * FROM ' . $order_shipping_details_table . ' WHERE order_id = \'' . MySQL::escapeString($order_id) . '\'';
                 $shipping_info_row = MySQL::selectRow($query);
               }
               catch(Exception $e)
               {
                 throw new Exception('Error retreiving shipping information.');
               }

               $xml_data = $row['xml_data'];

               libxml_use_internal_errors(true);
               $xml = @new SimpleXMLElement($xml_data);

               $order_data = $xml->xpath('/cXML/Request/OrderRequest/OrderRequestHeader');
               $order_type_data = @$xml->xpath('/cXML/Request/OrderRequest/OrderRequestHeader/Extrinsic[@name=\'OrderType\']');

               $order_details['order_date'] = date(MONSTRA_DATE_FORMAT, strtotime($row['order_date']));
               $order_details['order_id'] = $row['payload_id'];
               $order_details['purchase_order_number'] = $row['number'];
               $order_details['status'] = $row['status'];
               $order_details['order_type'] = $order_type_data[0];

               $order_details['subtotal'] = $row['subtotal'];
               $order_details['handling'] = $row['handling'];
               $order_details['shipping_tax_mode'] = $row['shipping_tax_mode'];
               $order_details['shipping_tax_description'] = $row['shipping_tax_description'];
               $order_details['shipping_tax'] = $row['shipping_tax'];
               $order_details['shipping_type'] = $row['shipping_type'];
               $order_details['shipping_method'] = $row['shipping_method'];
               $order_details['shipping_instructions'] = $row['shipping_instructions'];
               $order_details['shipping_cost'] = $row['shipping_cost'];

               $order_details['payment_type'] = $row['payment_type'] == 'paypal' ? 'PayPal account' : 'Credit Card';
               $order_details['payment_transaction_id'] = $row['transaction_id'];
               $order_details['payment_date'] = date(MONSTRA_DATE_FORMAT, strtotime($row['payment_date']));
               $order_details['payment_status'] = $row['payment_status'];

               $order_details['currency'] = $row['sign'];
               $order_details['total'] = $row['total'];

               $order_details['ship_to']['name'] = $shipping_info_row['full_name'];
               $order_details['ship_to']['address1'] = $shipping_info_row['address1'];
               $order_details['ship_to']['address2'] = $shipping_info_row['address2'];
               $order_details['ship_to']['city'] = $shipping_info_row['city'];
               $order_details['ship_to']['postal_code'] = $shipping_info_row['postal_code'];
               $order_details['ship_to']['country'] = Countries::getCountryName($shipping_info_row['country_code']);
               $order_details['ship_to']['state'] = Countries::hasStates($shipping_info_row['country_code']) ? Countries::getStateName($shipping_info_row['country_code'], $shipping_info_row['state_code']) : null;
               $order_details['ship_to']['email'] = $shipping_info_row['email'];
               $order_details['ship_to']['phone'] = $shipping_info_row['phone'];

               $order_details['comments'] = @$order_data[0]->Comments;

               $counter = 0;

               while ($row = MySQL::fetch($rs_order_items))
               {
                 $order_items[$counter]['code'] = $row['code'];
                 $order_items[$counter]['description'] = $row['description'];
                 $order_items[$counter]['quantity'] = $row['quantity'];
                 $order_items[$counter]['price'] = $row['price'];
                 $order_items[$counter]['total_price'] = $row['total_price'];

                 $counter++;
               }

               MySQL::free($rs_order_items);
             }
             catch(Exception $e)
             {
               $errors['page_error'] = $e->getMessage();
             }

             View::factory('store/orders/views/backend/details')
                     ->assign('order_details', $order_details)
                     ->assign('order_items', $order_items)
                     ->assign('errors', $errors)
                     ->display();
                     
            break;
                
        }
    } 
    else
    { 
      $records_per_page = 10;

      $orders = null;
      $rs_signs = null;

      $records_start = null;
      $records_end = null;
      $records_total = null;

      $pages_total = null;
      $pages_number = null;

      $page_error = null;

      $sort_fields = array('date', 'payload', 'number', 'status');
      $sort_query_fields = array($sort_fields[0] => 'order_date', $sort_fields[1] => 'payload_id', $sort_fields[2] => 'number', $sort_fields[3] => 'status');
      $sort_directions = array('up', 'down');
      $sort_query_directions = array($sort_directions[0] => 'asc', $sort_directions[1] => 'desc');

      $sort_field = Valid::hasValue(Request::get('s')) ? strtolower(Request::get('s')) : null;
      $sort_direction = Valid::hasValue(Request::get('d')) ? strtolower(Request::get('d')) : $sort_directions[0];

      $sort_field = in_array($sort_field, $sort_fields) ? $sort_field : null;
      $sort_direction = in_array($sort_direction, $sort_directions) ? $sort_direction : $sort_directions[0];

      try
      {
        try
        {
          $query = 'SELECT COUNT(id) FROM orders';

          if (Request::get('mode') == 'archived')
          {
            $query .= '_archive';
          }
          else
          {
            if (Request::get('mode') == 'processed')
            {
              $query .= ' WHERE status = \'processed\'';
            }
            else
            {
              $query .= ' WHERE status = \'new\'';
            }
          }

          $records_total = MySQL::selectCell($query);
          $pages_total = ceil($records_total / $records_per_page);
          $pages_number = Valid::hasValue(Request::get('page')) ? (Valid::isInteger(Request::get('page')) ? (integer)Request::get('page') : 'all') : 1;

          if (Valid::isInteger($pages_number))
          {
            if ($pages_number > $pages_total)
            {
              $pages_number = $pages_total;
            }
            if ($pages_number < 1)
            {
              $pages_number = 1;
            }
          }
          else
          {
            $pages_number = 'all';
          }

          $query = 'SELECT id, order_date, payload_id, number, price_type_id, total, status FROM orders';

          if ($mode == 'archived')
          {
            $query .= '_archive';
          }
          else
          {
            if ($mode == 'processed')
            {
              $query .= ' WHERE status = \'processed\'';
            }
            else
            {
              $query .= ' WHERE status = \'new\'';
            }
          }

          if ($sort_field)
          {
            $query .= ' ORDER BY ' . $sort_query_fields[$sort_field] . ' ' . ($sort_direction ? $sort_query_directions[$sort_direction] . ' ' : null);
          }

          if (Valid::isInteger($pages_number))
          {
            $query .= ' LIMIT ' . ($pages_number - 1) * $records_per_page . ', ' . $records_per_page;
          }

          $rs_orders = MySQL::query($query);

          $query = 'SELECT id, sign FROM price_types';

          $rs_signs = MySQL::query($query);

          while ($row = MySQL::fetch($rs_signs))
          {
            $signs[$row['id']] = $row['sign'];
          }

          MySQL::free($rs_signs);
        }
        catch (Exception $e)
        {
          throw new Exception(__('Data retreival error: :error', 'orders', array(':error' => $e->getMessage())));
        }

        $records_count = MySQL::rowCount($rs_orders);

        $records_start = Valid::isInteger($pages_number) ? ($pages_number - 1) * $records_per_page + 1: 1;
        $records_end = $records_start + $records_count - 1;

        $orders = array();

        $count = 0;

        for ($i = 1; $i <= $records_count; $i++)
        {
            $order = MySQL::fetch($rs_orders);

            $orders[$count]['id'] = $order['id'];
            $orders[$count]['order_id'] = $order['payload_id'];
            $orders[$count]['date'] = date(MONSTRA_DATE_FORMAT, strtotime($order['order_date']));
            $orders[$count]['number'] = $order['number'];
            $orders[$count]['order_number'] = $order['number'] ? $order['number'] : $order['payload_id'];
            $orders[$count]['amount'] = $signs[$order['price_type_id']] . $order['total'];
            $orders[$count]['status'] = $order['status'];

            $count++;
        }

        MySQL::free($rs_orders);

      }
      catch (Exception $e)
      {
        $errors['page_error'] = $e->getMessage();
      }

      View::factory('store/orders/views/backend/index')
              ->assign('orders', $orders)
              ->assign('mode', $mode)
              ->assign('sort_fields', $sort_fields)
              ->assign('sort_directions', $sort_directions)
              ->assign('sort_field', $sort_field)
              ->assign('sort_direction', $sort_direction)
              ->assign('records_start', $records_start)
              ->assign('records_end', $records_end)
              ->assign('records_total', $records_total)
              ->assign('pages_number', $pages_number)
              ->assign('pages_total', $pages_total)
              ->assign('errors', $errors)
              ->display();
    }
  }
}
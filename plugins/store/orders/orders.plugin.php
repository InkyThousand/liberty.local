<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<?php
Plugin::register(__FILE__,
                 __('Orders', 'orders'),
                 __('Orders management plugin', 'orders'),
                 '1.0.0',
                 'razorolog',
                 '',
                 'orders',
                 'store');

Plugin::Admin('orders', 'store');

Javascript::add('plugins/store/orders/js/' . Option::get('language') . '.orders.js', 'backend');

class Orders extends Frontend
{
  public static function main()
  {
  }

  protected static function route()
  {
    if (Uri::segment(0) == 'orders' && !Uri::segment(1)) return 'orders';
    if (Uri::segment(0) == 'orders' && Uri::segment(1) == 'details') return 'details';
    if (Uri::segment(0) == 'orders' && Uri::segment(1) == 'change-shipping-details') return 'change-shipping-details';
    return null;
  }

  public static function content()
  {
    switch(self::route())
    {
                      case 'details': 
                                      self::showPendingOrderDetails();
                                      break;

      case 'change-shipping-details': 
                                      self::changeShippingDetails();
                                      break;

                             default: 
                                      self::showPendingOrders();
                                      break;
    }
  }

  public static function showPendingOrders()
  {
    $error = null;
    $pending_orders = null;
    $incorrect_orders = false;

    $payload_id = Text::trimStr(Request::get('id'));

    try
    {
      if (strtolower(Text::trimStr(Request::request('action'))) == 'remove' && Valid::hasValue($payload_id) && Security::check(Request::get('token')))
      {
        self::removePendingOrder($payload_id);
        Request::redirect(Site::url() . 'orders', 302, 0, false);
      }

      $pending_orders = Orders::getPendingOrders();
    }
    catch (Exception $e)
    {
      $error = $e->getMessage();
    }

    View::factory('store/orders/views/frontend/orders')
        ->assign('pending_orders', $pending_orders)
        ->assign('error', $error)
        ->display();
  }

  public static function showPendingOrderDetails()
  {
    $error = null;

    $payload_id = Text::trimStr(Request::get('id'));

    try
    {
      $part_code = Text::trimStr(Request::request('part'));

      if (strtolower(Text::trimStr(Request::request('action'))) == 'remove' && Valid::hasValue($part_code) && Security::check(Request::get('token')))
      {
        if (Orders::removePendingOrderItems($payload_id, $part_code))
        {
          Request::redirect(Site::root() . '/orders/details?id=' . urlencode($payload_id), 302, 0, false);
        }
        else
        {
          Request::redirect(Site::root() . '/orders', 302, 0, false);
        }
      }

      $details = Orders::getPendingOrderDetails($payload_id);
    }
    catch(Exception $e)
    {
      $error = $e->getMessage();
    }

    View::factory('store/orders/views/frontend/details')
        ->assign('payload_id', $payload_id)
        ->assign('details', $details)
        ->assign('error', $error)
        ->display();
  }

  public static function changeShippingDetails()
  {
    $error = null;

    $update_error = null;

    $payload_id = Text::trimStr(Request::get('id'));
    $details = null;

    try
    {
      if (Request::post('save'))
      {
        $full_name = Text::trimStr(Request::post('full_name'), 255);
        $address1 = Text::trimStr(Request::post('address1'), 255);
        $address2 = Text::trimStr(Request::post('address2'), 255);
        $city = Text::trimStr(Request::post('city'), 255);
        $postal_code = Text::trimStr(Request::post('postal_code'), 255);
        $country_code = Text::trimStr(Request::post('country_code'), 2);
        $state_code = Text::trimStr(Request::post('state_code'), 2);
        $phone = Text::trimStr(Request::post('phone'), 255);
        $email = Text::trimStr(Request::post('email'), 255);

        $country_code = Countries::validateCountry($country_code);

        $details['full_name'] = $full_name;
        $details['address1'] = $address1;
        $details['address2'] = $address2;
        $details['city'] = $city;
        $details['postal_code'] = $postal_code;
        $details['country_code'] = $country_code;
        $details['state_code'] = $state_code;
        $details['email'] = $email;
        $details['phone'] = $phone;

        try
        {
          if (!Valid::hasValue($full_name))
          {
            throw new Exception('Please enter your full name.');
          }

          if (!Valid::hasValue($address1))
          {
            throw new Exception('Please fill in your address.');
          }

          if (!Valid::hasValue($city))
          {
            throw new Exception('Please specify your city.');
          }

          if (!Valid::hasValue($postal_code))
          {
            throw new Exception('Please specify postal code.');
          }

          if (!Valid::hasValue($country_code))
          {
            throw new Exception('Please select country.');
          }
          else
          {
            if (Countries::hasStates($country_code))
            {
              $state_code = Countries::validateState($country_code, $state_code);

              if (!Valid::hasValue($state_code))
              {
                throw new Exception('Please select state.');
              }
            }
            else
            {
              $state_code = null;
            }
          }

          if (Valid::hasValue($email))
          {
            if (!Valid::email($email))
            {
              throw new Exception('Email address is incorrect.');
            }
          }

          if (strlen(preg_replace('/[^0-9]/', '', $phone)) != 10)
          {
            throw new Exception('Phone number is incorrect. It should contain 10 digits.');
          }

          if (Security::check(Request::post('csrf')))
          {
            $details['phone'] = preg_replace('/[^0-9]/', '', $details['phone']);
            $details['phone'] = substr($phone, 0, 3) . '-' . substr($details['phone'], 3, 3) . '-' . substr($details['phone'], 6);

            self::updatePendingOrderShippingDetails($payload_id, $details);

            Session::set('shipping_details_updated', true);
            Request::redirect(Site::url() . 'orders/details?id=' . urlencode($payload_id), 302, 0, false);
          }
        }
        catch (Exception $e)
        {
          $update_error = $e->getMessage();
        }
      }
      else
      {
        $details = Orders::getPendingOrderShippingDetails($payload_id);

        if ($details['changes_disabled'])
        {
          throw new Exception('Changing of shipping details is not allowed at this time.');
        }
      }
    }
    catch(Exception $e)
    {
      $error = $e->getMessage();
    }

    View::factory('store/orders/views/frontend/change-shipping-details')
        ->assign('payload_id', $payload_id)
        ->assign('details', $details)
        ->assign('update_error', $update_error)
        ->assign('error', $error)
        ->display();
  }

  public static function title()
  {
    $title = 'Orders';

    switch(self::route())
    {
                      case 'details': 
                                      $title = 'Order Details';
                                      break;

      case 'change-shipping-details': 
                                      $title = 'Change Shipping Details';
                                      break;
    }

    return $title;
  }

  public static function processEzPartsOrder($xml_data)
  {
    $xml_data = (string)$xml_data;

    $add_new_parts = true;
    $add_or_update_prices = true;

    $xml_data = preg_replace('/(<\?xml[^?]+?)utf-16/i', '$1utf-8', $xml_data);
    $xml_data = str_replace('orderId', 'orderID', $xml_data);
    $xml_data = Text::trimStr($xml_data);

    $payload_id = null;
    $order_number = null;
    $currency = null;
    $language_code = null;
    $price_type_id = null;

    $order_items = array();

    try
    {
      try
      {
        try
        {
          libxml_use_internal_errors(true);
          $xml = @new SimpleXMLElement($xml_data);
        }
        catch (Exception $e)
        {
          throw new Exception('Input data was not correctly formed. ' . $e->getMessage() . '.');
        }

        $header_data = $xml->xpath('/cXML/Request/OrderRequest/OrderRequestHeader');
        $order_number_data = $xml->xpath('/cXML/Request/OrderRequest/OrderRequestHeader/Extrinsic[@name=\'PurchaseOrderNumber\']');
        $items_data = $xml->xpath('/cXML/Request/OrderRequest/ItemOut');

        if (count($header_data) == 0) 
        {
          throw new Exception('Order information elements are missing.');
        }

        if (count($items_data) == 0)
        {
          throw new Exception('Order items are missing.');
        }

        $payload_id = (string)$header_data[0]->attributes()->orderID;
        $payload_id = Text::trimStr($payload_id, 30);

        $order_number = (string)$order_number_data[0];
        $order_number = Text::trimStr($order_number, 30);

        if ($order_number == '')
        {
          $order_number = (string)$header_data[0]->ShipTo[0]->Address[0]->PostalAddress[0]->PONumber[0];
          $order_number = Text::trimStr($order_number, 30);
        }

        $currency = (string)$header_data[0]->Total[0]->Money[0]->attributes()->currency;
        $currency = Text::trimStr($currency);

        if (!Valid::hasValue($payload_id))
        {
          throw new Exception('OrderID is missing.');
        }

        if (!Valid::hasValue($currency))
        {
          throw new Exception('Order currency is missing.');
        }

        try
        {
          $query = 'SELECT id FROM price_types WHERE currency = \'' . MySQL::escapeString($currency) . '\'';
          $price_type_id = MySQL::selectCell($query);
        }
        catch(Exception $e)
        {
          throw new Exception('Error verifying order currency.');
        }

        if ($price_type_id === null)
        {
          throw new Exception('Unknown order currency specified.');
        }

        $shipping_state_code = null;

        $shipping_country_code = (string)$header_data[0]->ShipTo[0]->Address[0]->PostalAddress[0]->Country[0];
        $shipping_country_code = Text::trimStr($shipping_country_code);

        $shipping_country_code = Countries::validateCountry($shipping_country_code);

        if (!Valid::hasValue($shipping_country_code))
        {
          $shipping_country_code = (string)$header_data[0]->ShipTo[0]->Address[0]->PostalAddress[0]->Country[0]->attributes()->isoCountryCode;
          $shipping_country_code = Text::trimStr($shipping_country_code);
          $shipping_country_code = Countries::validateCountry($shipping_country_code);
        }

        if (Valid::hasValue($shipping_country_code))
        {
          $shipping_state_code = (string)$header_data[0]->ShipTo[0]->Address[0]->PostalAddress[0]->State[0];
          $shipping_state_code = Text::trimStr($shipping_state_code);

          if (Countries::hasStates($shipping_country_code))
          {
            $shipping_state_code = Countries::validateState($shipping_country_code, $shipping_state_code);
          }
          else
          {
            $shipping_state_code = null;
          }
        }

        $counter = 0;

        foreach ($items_data as $item)
        {
          $part_code = (string)$item[0]->ItemID[0]->SupplierPartID[0];
          $part_code = Text::trimStr($part_code);

          if (!Valid::hasValue($language_code))
          {
            $language_code = $item[0]->ItemDetail[0]->Description[0]->attributes('xml', true);
            $language_code = (string)$language_code['lang'];
            $language_code = Text::trimStr($language_code);
          }

          $description = (string)$item[0]->ItemDetail[0]->Description[0];
          $description = Text::trimStr($description);

          $price = (string)$item[0]->ItemDetail[0]->UnitPrice[0]->Money[0];
          $price = Text::trimStr($price);

          $quantity = (string)$item[0]->attributes()->quantity;
          $quantity = Text::trimStr($quantity);

          if (!Valid::hasValue($part_code) || !Valid::hasValue($quantity) || !Valid::hasValue($price))
          {
            throw new Exception('Part information is missing.');
          }

          $order_items[$counter]['code'] = $part_code;
          $order_items[$counter]['description'] = $description;
          $order_items[$counter]['quantity'] = $quantity;
          $order_items[$counter]['price'] = $price;

          $counter++;
        }

        if (!Valid::hasValue($language_code))
        {
          throw new Exception('Language code is missing.');
        }

      }
      catch(Exception $e)
      {
        self::insertBadOrder($xml_data);
        throw new Exception($e->getMessage());
      }

      try
      {
        $query = 'SELECT COUNT(id) > 0 FROM orders WHERE payload_id = \'' . MySQL::escapeString($payload_id) . '\'';
        $orders_count = (bool)MySQL::selectCell($query);
      }
      catch(Exception $e)
      {
        throw new Exception('Error verifying order number.');
      }

      if ($orders_count)
      {
        throw new Exception('Order was already processed.');
      }

      try
      {
        $query = 'SELECT COUNT(id) > 0 FROM pending_orders WHERE payload_id = \'' . MySQL::escapeString($payload_id) . '\' AND session_id = \'' . MySQL::escapeString(md5(Session::getSessionId())) . '\'';

        if ((bool)MySQL::selectCell($query))
        {
          return $payload_id;
        }
      }
      catch(Exception $e)
      {
        throw new Exception('Error verifying order number.');
      }

      $handling = Shipping::getHandlingValue();

      try
      {
        try
        {
          MySQL::startTransaction();

          $language_code = Text::trimStr($language_code, 3);

          $query = 'INSERT INTO pending_orders(session_id, payload_id, number, price_type_id, language_code, handling, xml_data) VALUES('.
                   '\'' . MySQL::escapeString(md5(Session::getSessionId())) . '\', '.
                   '\'' . MySQL::escapeString($payload_id) . '\', '.
                   '\'' . MySQL::escapeString($order_number) . '\', '.
                   '\'' . MySQL::escapeString($price_type_id) . '\', '.
                   '\'' . MySQL::escapeString($language_code) . '\', '.
                   '\'' . MySQL::escapeString($handling) . '\', '.
                   '\'' . MySQL::escapeString($xml_data) . '\')';

          MySQL::query($query);
        }
        catch (Exception $e)
        {
          throw new Exception();
        }

        $pending_order_id = MySQL::getInsertId();

        $subtotal = 0;

        foreach ($order_items as $item)
        {
          $part_code = $item['code'];
          $quantity = (int)$item['quantity'];

          try
          {
            $query = 'SELECT id FROM parts WHERE code = \'' . MySQL::escapeString($part_code) . '\'';
            $part_id = MySQL::selectCell($query);
          }
          catch (Exception $e)
          {
            throw new Exception();
          }

          if ($part_id === null)
          {
            if ($add_new_parts)
            {
              try
              {
                $part_code = Text::trimStr($part_code, 255);
                $query = 'INSERT INTO parts(code) VALUES(\'' . MySQL::escapeString($part_code) . '\')';
                MySQL::query($query);
                $part_id = MySQL::getInsertId();
                $query = 'INSERT INTO part_localization(part_id, description, language_code) VALUES(\'' . MySQL::escapeString($part_id) . '\', ' .
                         '\'' . MySQL::escapeString($item['description']) . '\', ' .
                         '\'' . MySQL::escapeString($language_code) . '\')';
                MySQL::query($query);
              }
              catch (Exception $e)
              {
                throw new Exception();
              }
            }
            else
            {
              throw new Exception('Incorrect order item details. Part code "' . $part_code . '" was not found.');
            }
          }

          try
          {
            $query = 'SELECT value FROM part_prices WHERE part_id = \'' . MySQL::escapeString($part_id) . '\' AND price_type_id = \'' . MySQL::escapeString($price_type_id) . '\'';
            $price = MySQL::selectCell($query);
          }
          catch (Exception $e)
          {
            throw new Exception();
          }

          if ($price === null)
          {
            if ($add_new_parts || $add_or_update_prices)
            {
              try
              {
                $price = $item['price'];
                $query = 'INSERT INTO part_prices(part_id, price_type_id, value) VALUES(\'' . MySQL::escapeString($part_id) . '\', \'' . MySQL::escapeString($price_type_id) . '\', \'' . MySQL::escapeString($price) . '\')';
                MySQL::query($query);
              }
              catch (Exception $e)
              {
                throw new Exception();
              }
            }
            else
            {
              throw new Exception('Incorrect order item details. Part price for part ' . $part_code . ' was not found.');
            }
          }
          elseif ($price != $item['price'] && $add_or_update_prices)
          {
            try
            {
              $price = $item['price'];
              $query = 'UPDATE part_prices SET value = \'' . MySQL::escapeString($price) .  '\' WHERE part_id = \'' . MySQL::escapeString($part_id) . '\' AND price_type_id = \'' . MySQL::escapeString($price_type_id) . '\'';
              MySQL::query($query);
            }
            catch (Exception $e)
            {
              throw new Exception();
            }
          }

          $price = (double)$price;
          $subtotal += $quantity * $price;

          try
          {
            $query = 'INSERT INTO pending_order_items(pending_order_id, part_id, quantity, price) VALUES('.
                     '\'' . MySQL::escapeString($pending_order_id) . '\', '.
                     '\'' . MySQL::escapeString($part_id) . '\', '.
                     '\'' . MySQL::escapeString($quantity) . '\', '.
                     '\'' . MySQL::escapeString($price) . '\')';
            MySQL::query($query);
          }
          catch (Exception $e)
          {
            throw new Exception();
          }
        }

        $shipping_tax_mode = null;
        $shipping_tax_description = null;
        $shipping_tax_percent = null;
        $shipping_tax = null;

        if (Valid::hasValue($shipping_country_code))
        {
          $shipping_tax_params = Taxes::getShippingTax($shipping_country_code, $shipping_state_code);

          if (Valid::hasValue($shipping_tax_params['mode']))
          {
            $shipping_tax_mode = $shipping_tax_params['mode'];
            $shipping_tax_description = Valid::hasValue($shipping_tax_params['description']) ? Text::trimStr($shipping_tax_params['description'], 255) : null;
            $shipping_tax_percent = $shipping_tax_params['value'];

            if ($shipping_tax_mode == 'merchandise')
            {
              $shipping_tax = ($subtotal / 100) * $shipping_tax_percent;
            }
          }
        }

        try
        {
          $query = 'UPDATE pending_orders SET subtotal = \'' . MySQL::escapeString($subtotal) . '\', '.
                   'shipping_tax_mode = ' . ($shipping_tax_mode === null ? 'NULL' : '\'' . MySQL::escapeString($shipping_tax_mode) . '\'') . ', '.
                   'shipping_tax_percent = ' . ($shipping_tax_percent === null ? 'NULL' : '\'' . MySQL::escapeString($shipping_tax_percent) . '\'') . ', '.
                   'shipping_tax_description = ' . ($shipping_tax_description === null ? 'NULL' : '\'' . MySQL::escapeString($shipping_tax_description) . '\'') . ', '.
                   'shipping_tax = ' . ($shipping_tax === null ? 'NULL' : '\'' . MySQL::escapeString($shipping_tax) . '\'') . ' '.
                   'WHERE id = \'' . MySQL::escapeString($pending_order_id) . '\'';

          MySQL::query($query);

          $full_name = (string)$header_data[0]->ShipTo[0]->Address[0]->PostalAddress[0]->FullName[0];
          $address1 = (string)$header_data[0]->ShipTo[0]->Address[0]->PostalAddress[0]->Street[0];
          $address2 = (string)$header_data[0]->ShipTo[0]->Address[0]->PostalAddress[0]->Street[1];
          $city = (string)$header_data[0]->ShipTo[0]->Address[0]->PostalAddress[0]->City[0];
          $postal_code = (string)$header_data[0]->ShipTo[0]->Address[0]->PostalAddress[0]->PostalCode[0];
          $email = (string)$header_data[0]->ShipTo[0]->Address[0]->Email[0];
          $phone = (string)$header_data[0]->ShipTo[0]->Address[0]->Phone[0]->TelephoneNumber[0]->AreaOrCityCode[0] . '-' . (string)$header_data[0]->ShipTo[0]->Address[0]->Phone[0]->TelephoneNumber[0]->Number[0];

          $full_name = Text::trimStr($full_name, 255);
          $address1 = Text::trimStr($address1, 255);
          $address2 = Text::trimStr($address2, 255);
          $city = Text::trimStr($city, 255);
          $postal_code = Text::trimStr($postal_code, 255);
          $email = Text::trimStr($email, 255);
          $phone = Text::trimStr($phone, 255);

          $query = 'INSERT INTO pending_order_shipping_details(pending_order_id, full_name, address1, address2, city, postal_code, country_code, state_code, email, phone) VALUES('.
                   '\'' . MySQL::escapeString($pending_order_id) . '\', '.
                   '\'' . MySQL::escapeString($full_name) . '\', '.
                   '\'' . MySQL::escapeString($address1) . '\', '.
                   '\'' . MySQL::escapeString($address2) . '\', '.
                   '\'' . MySQL::escapeString($city) . '\', '.
                   '\'' . MySQL::escapeString($postal_code) . '\', '.
                   '\'' . MySQL::escapeString($shipping_country_code) . '\', '.
                   '\'' . MySQL::escapeString($shipping_state_code) . '\', '.
                   '\'' . MySQL::escapeString($email) . '\', '.
                   '\'' . MySQL::escapeString($phone) . '\')';

          MySQL::query($query);

          MySQL::commitTransaction();
        }
        catch (Exception $e)
        {
          throw new Exception();
        }

        return $payload_id;
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

        $error_message = $e->getMessage();

        throw new Exception(Valid::hasValue($error_message) ? $error_message : 'Error saving order data.');
      }
    }
    catch(Exception $e)
    {
      throw new Exception('Could not process order. ' . $e->getMessage());
    }
  }

  public static function checkoutDisabled($payload_id)
  {
    $payload_id = (string)$payload_id;

    try
    {
      $query = 'SELECT country_code, state_code FROM pending_order_shipping_details posd LEFT JOIN pending_orders po ON posd.pending_order_id = po.id WHERE session_id = \'' . MySQL::escapeString(md5(Session::getSessionId())) . '\' AND payload_id = \'' . MySQL::escapeString($payload_id) . '\'';
      $row = MySQL::selectRow($query);
    }
    catch(Exception $e)
    {
      throw new Exception('Data retreival error.');
    }

    if ($row == null)
    {
      throw new Exception('Order was not found.');
    }

    return ! Countries::validateCountryAndState($row['country_code'], $row['state_code']);
  }

  public static function checkPendingOrders()
  {
    try
    {
      $query = 'SELECT COUNT(id) > 0 FROM pending_orders WHERE session_id = \'' . MySQL::escapeString(md5(Session::getSessionId())) . '\'';
      $pending_orders_count = (bool)MySQL::selectCell($query);
    }
    catch(Exception $e)
    {
      $pending_orders_count = false;
    }
    return $pending_orders_count;
  }


  public static function getPendingOrders()
  {
    try
    {
      $query = 'SELECT po.payload_id, po.order_date, po.status, posd.country_code, posd.state_code FROM pending_orders po LEFT JOIN pending_order_shipping_details posd ON posd.pending_order_id = po.id WHERE session_id = \'' . MySQL::escapeString(md5(Session::getSessionId())) . '\'';

      $rs_orders = MySQL::query($query);

      $orders_array = array();
      $counter = 0;

      while ($order = MySQL::fetch($rs_orders))
      {
        $orders_array[$counter]['payload_id'] = $order['payload_id'];
        $orders_array[$counter]['changes_disabled'] = $order['status'] != 'pending';
        $orders_array[$counter]['checkout_disabled'] = ! Countries::validateCountryAndState($order['country_code'], $order['state_code']);
        $orders_array[$counter]['date'] = date(MONSTRA_DATE_FORMAT, strtotime($order['order_date']));
        $counter++;
      }

      MySQL::free($rs_orders);

      return $orders_array;
    }
    catch (Exception $e)
    {
      throw new Exception('Data retreival error.');
    }
  }
  
  public static function removePendingOrder($payload_id)
  {
    $payload_id = (string)$payload_id;

    try
    {
      try
      {
        $query = 'SELECT id, status FROM pending_orders WHERE session_id = \'' . MySQL::escapeString(md5(Session::getSessionId())) . '\' AND payload_id = \'' . MySQL::escapeString($payload_id) . '\'';
        $row = MySQL::selectRow($query);
      }
      catch(Exception $e)
      {
        throw new Exception('Could not verify order data.');
      }

      if ($row === null)
      {
        throw new Exception('Order was not found.');
      }

      if ($row['status'] == 'paypaled')
      {
        throw new Exception('Order removal is not allowed at this time.');
      }

      $pending_order_id = $row['id'];

      try
      {
         MySQL::startTransaction();

         $query = 'DELETE FROM pending_order_shipping_details WHERE pending_order_id = \'' . MySQL::escapeString($pending_order_id) . '\'';

         MySQL::query($query);

         $query = 'DELETE FROM pending_order_items WHERE pending_order_id = \'' . MySQL::escapeString($pending_order_id) . '\'';

         MySQL::query($query);

         $query = 'DELETE FROM pending_orders WHERE id = \'' . MySQL::escapeString($pending_order_id) . '\'';

         MySQL::query($query);

         MySQL::commitTransaction();
      }
      catch (Exception $e)
      {
        try
        {
          MySQL::rollbackTransaction();
        }
        catch(Exception $ex)
        {
        }

        throw new Exception('Could not delete order.');
      }
    }
    catch (Exception $e)
    {
      throw new Exception($e->getMessage());
    }
  }


  public static function removePendingOrderItems($payload_id, $part_code)
  {
    $payload_id = (string)$payload_id;
    $part_code = (string)$part_code;

    try
    {
      try
      {
        $query = 'SELECT id FROM pending_orders WHERE session_id = \'' . MySQL::escapeString(md5(Session::getSessionId())) . '\' AND payload_id = \'' . MySQL::escapeString($payload_id) . '\'';
        $pending_order_id = MySQL::selectCell($query);
      }
      catch(Exception $e)
      {
        throw new Exception('Could not verify order data.');
      }

      if ($pending_order_id === null)
      {
        throw new Exception('Order was not found.');
      }

      try
      {
         MySQL::startTransaction();

         $query = 'DELETE FROM pending_order_items WHERE part_id = (SELECT id FROM parts WHERE code = \'' . MySQL::escapeString($part_code) . '\') AND pending_order_id = \'' . MySQL::escapeString($pending_order_id) . '\'';

         MySQL::query($query);

         $query = 'SELECT COUNT(id) > 0 FROM pending_order_items WHERE pending_order_id = \'' . MySQL::escapeString($pending_order_id) . '\'';

         $order_items_found = (bool)MySQL::selectCell($query);

         if (!$order_items_found)
         {
           $query = 'DELETE FROM pending_order_shipping_details WHERE pending_order_id = \'' . MySQL::escapeString($pending_order_id) . '\'';

           MySQL::query($query);

           $query = 'DELETE FROM pending_order_items WHERE pending_order_id = \'' . MySQL::escapeString($pending_order_id) . '\'';

           MySQL::query($query);

           $query = 'DELETE FROM pending_orders WHERE id = \'' . MySQL::escapeString($pending_order_id) . '\'';

           MySQL::query($query);
         }

         MySQL::commitTransaction();

         return $order_items_found;
      }
      catch (Exception $e)
      {
        try
        {
          MySQL::rollbackTransaction();
        }
        catch(Exception $ex)
        {
        }

        throw new Exception('Could not delete order item.');
      }
    }
    catch (Exception $e)
    {
      throw new Exception($e->getMessage());
    }
  }

  public static function getPendingOrderDetails($payload_id)
  {
    $payload_id = (string)$payload_id;

    try
    {
      try
      {
        $query = 'SELECT po.id AS poid, sign, language_code, subtotal, handling, shipping_tax_mode, shipping_tax, shipping_tax_description, status, posd.* '.
                 'FROM pending_orders po '.
                 'LEFT JOIN price_types ON price_types.id = po.price_type_id '.
                 'LEFT JOIN pending_order_shipping_details posd ON posd.pending_order_id = po.id '.
                 'WHERE payload_id = \'' . MySQL::escapeString($payload_id) . '\' AND session_id = \'' . MySQL::escapeString(md5(Session::getSessionId())) . '\'';

        $row = MySQL::selectRow($query);
      }
      catch(Exception $e)
      {
        throw new Exception('Could not verify order data.');
      }

      if ($row === null)
      {
        throw new Exception('Order was not found.');
      }

      $pending_order_id = $row['poid'];
      $language_code = $row['language_code'];

      try
      {
        $query = 'SELECT p.code, pl.description, oi.quantity, oi.price, oi.quantity * oi.price AS price_total, p.pounds, p.ounces FROM pending_order_items oi '.
                 'LEFT JOIN parts p ON p.id = oi.part_id LEFT JOIN part_localization pl ON pl.part_id = oi.part_id '.
                 'WHERE pending_order_id = \'' . MySQL::escapeString($pending_order_id) . '\' AND pl.language_code = \'' . MySQL::escapeString($language_code) . '\'';
        
        $rs_order_items = MySQL::query($query);
      }
      catch(Exception $e)
      {
        throw new Exception('Error retreiving order details.');
      }

      $order_details = array();

      $order_details['subtotal'] = $row['subtotal'];
      $order_details['handling'] = $row['handling'];
      $order_details['shipping_tax_mode'] = $row['shipping_tax_mode'];
      $order_details['shipping_tax_description'] = $row['shipping_tax_description'];

      if ($row['shipping_tax_mode'] != 'total')
      {
        $order_details['shipping_tax'] = $row['shipping_tax'];
      }
      else
      {
        $order_details['shipping_tax'] = null;
      }
      $order_details['currency'] = $row['sign'];

      $order_details['changes_disabled'] = $row['status'] != 'pending';
      $order_details['checkout_disabled'] = ! Countries::validateCountryAndState($row['country_code'], $row['state_code']);

      $order_details['shipping']['full_name'] = $row['full_name'];
      $order_details['shipping']['address1'] = $row['address1'];
      $order_details['shipping']['address2'] = $row['address2'];
      $order_details['shipping']['city'] = $row['city'];
      $order_details['shipping']['postal_code'] = $row['postal_code'];
      $order_details['shipping']['country_code'] = $row['country_code'];
      $order_details['shipping']['state_code'] = $row['state_code'];
      $order_details['shipping']['email'] = $row['email'];
      $order_details['shipping']['phone'] = $row['phone'];

      $order_details['order_items'] = array();

      $counter = 0;

      $defaultWeight = Shipping::getDefaultWeight();

      while ($row = MySQL::fetch($rs_order_items))
      {
        $order_details['order_items'][$counter]['code'] = $row['code'];
        $order_details['order_items'][$counter]['description'] = $row['description'];
        $order_details['order_items'][$counter]['quantity'] = $row['quantity'];
        $order_details['order_items'][$counter]['price'] = $row['price'];
        $order_details['order_items'][$counter]['price_total'] = $row['price_total'];
        
        if ($row['pounds'] === null && $row['ounces'] === null)
        {
          $pounds = $defaultWeight['pounds'];
          $ounces = $defaultWeight['ounces'];
        }
        else
        {
          $pounds = $row['pounds'];
          $ounces = $row['ounces'];
        }

        $order_details['order_items'][$counter]['weight'] = ((double)$pounds + (double)$ounces / 16) * $row['quantity'];

        $counter++;
      }

      MySQL::free($rs_order_items);

      return $order_details;
    }
    catch(Exception $e)
    {
      throw new Exception($e->getMessage());
    }
  }


  public static function getPendingOrderItems($payload_id)
  {
    $payload_id = (string)$payload_id;

    try
    {
      try
      {
        $query = 'SELECT id, language_code FROM pending_orders WHERE payload_id = \'' . MySQL::escapeString($payload_id) . '\' AND session_id = \'' . MySQL::escapeString(md5(Session::getSessionId())) . '\'';
        $row = MySQL::selectRow($query);
      }
      catch(Exception $e)
      {
        throw new Exception('Could not verify order data.');
      }

      if ($row === null)
      {
        throw new Exception('Order was not found.');
      }

      $pending_order_id = $row['id'];
      $language_code = $row['language_code'];

      try
      {
        $query = 'SELECT pl.description, oi.quantity, oi.price FROM pending_order_items oi '.
                 'LEFT JOIN parts p ON p.id = oi.part_id LEFT JOIN part_localization pl ON pl.part_id = oi.part_id '.
                 'WHERE pending_order_id = \'' . MySQL::escapeString($pending_order_id) . '\' AND pl.language_code = \'' . MySQL::escapeString($language_code) . '\'';
        
        $rs_order_items = MySQL::query($query);
      }
      catch(Exception $e)
      {
        throw new Exception('Error retreiving order details.');
      }

      $order_items = array();

      $counter = 0;

      while ($row = MySQL::fetch($rs_order_items))
      {
        $order_items[$counter]['description'] = $row['description'];
        $order_items[$counter]['quantity'] = $row['quantity'];
        $order_items[$counter]['price'] = $row['price'];
        $counter++;
      }

      MySQL::free($rs_order_items);

      return $order_items;
    }
    catch(Exception $e)
    {
      throw new Exception($e->getMessage());
    }
  }

  public static function getPendingOrderShippingDetails($payload_id)
  {
    try
    {
      try
      {
        $query = 'SELECT po.status, posd.* FROM pending_order_shipping_details posd LEFT JOIN pending_orders po ON po.id = posd.pending_order_id WHERE payload_id = \'' . MySQL::escapeString($payload_id) . '\' AND session_id = \'' . MySQL::escapeString(md5(Session::getSessionId())) . '\'';
        $row = MySQL::selectRow($query);
      }
      catch(Exception $e)
      {
        throw new Exception('Error retreiving shipping details.');
      }

      $details['changes_disabled'] = $row['status'] != 'pending';

      $details['full_name'] = $row['full_name'];
      $details['address1'] = $row['address1'];
      $details['address2'] = $row['address2'];
      $details['city'] = $row['city'];
      $details['postal_code'] = $row['postal_code'];
      $details['country_code'] = $row['country_code'];
      $details['state_code'] = $row['state_code'];
      $details['email'] = $row['email'];
      $details['phone'] = $row['phone'];

      return $details;
    }
    catch(Exception $e)
    {
      throw new Exception($e->getMessage());
    }
  }


  public static function updatePendingOrderShippingDetails($payload_id, $details)
  {
    try
    {
      try
      {
        $query = 'SELECT id, subtotal FROM pending_orders WHERE payload_id = \'' . MySQL::escapeString($payload_id) . '\' AND session_id = \'' . MySQL::escapeString(md5(Session::getSessionId())) . '\'';
        $row = MySQL::selectRow($query);
      }
      catch(Exception $e)
      {
        throw new Exception('Could not verify order data.');
      }

      if ($row === null)
      {
        throw new Exception('Order was not found.');
      }

      $pending_order_id = $row['id'];

      try
      {
        MySQL::startTransaction();

        $query = 'UPDATE pending_order_shipping_details SET '.
                 'full_name = \'' . MySQL::escapeString($details['full_name']) . '\', '.
                 'address1 = \'' . MySQL::escapeString($details['address1']) . '\', '.
                 'address2 = \'' . MySQL::escapeString($details['address2']) . '\', '.
                 'city = \'' . MySQL::escapeString($details['city']) . '\', '.
                 'postal_code = \'' . MySQL::escapeString($details['postal_code']) . '\', '.
                 'country_code = \'' . MySQL::escapeString($details['country_code']) . '\', '.
                 'state_code = \'' . MySQL::escapeString($details['state_code']) . '\', '.
                 'email = \'' . MySQL::escapeString($details['email']) . '\', '.
                 'phone = \'' . MySQL::escapeString($details['phone']) . '\' '.
                 'WHERE pending_order_id = \'' . MySQL::escapeString($pending_order_id) . '\'';

        MySQL::query($query);

        $shipping_country_code = $details['country_code'];
        $shipping_state_code = $details['state_code'];

        $shipping_tax = Taxes::getShippingTax($shipping_country_code, $shipping_state_code);

        $shipping_tax_mode = $shipping_tax['mode'];
        $shipping_tax_description = Valid::hasValue($shipping_tax['description']) ? Text::trimStr($shipping_tax['description'], 255) : null;

        if ($shipping_tax_mode == 'merchandise')
        {
          $shipping_tax_value = Valid::hasValue($shipping_tax['value']) ? ($subtotal / 100) * $shipping_tax['value'] : null;
        }
        else
        {
          $shipping_tax_value = $shipping_tax['value'];
        }

        $query = 'UPDATE pending_orders SET '.
                 'shipping_tax_mode = \'' . MySQL::escapeString($shipping_tax_mode) . '\', '.
                 'shipping_tax_description = ' . ($shipping_tax_description === null ? 'NULL' : '\'' . MySQL::escapeString($shipping_tax_description) . '\'') . ', '.
                 'shipping_tax = ' . ($shipping_tax_value === null ? 'NULL' : '\'' . MySQL::escapeString($shipping_tax_value) . '\'') . ' '. 
                 'WHERE id = \'' . MySQL::escapeString($pending_order_id) . '\'';

        echo $query;

        MySQL::query($query);

        MySQL::commitTransaction();
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

        throw new Exception('Could not update order details.');
      }
    }
    catch(Exception $e)
    {
      throw new Exception($e->getMessage());
    }
  }

  
  public static function updatePendingOrderDetails($payload_id, $fields)
  {
    try
    {
      $query = 'UPDATE pending_orders SET ';

      foreach ($fields as $field => $value)
      {
         $query .= $field . ' = ' . ($value ? '\'' . MySQL::escapeString($value) . '\'' : 'NULL') . ', ';
      }

      $query = substr($query, 0, strlen($query) - 2);

      $query .= ' WHERE payload_id = \'' . MySQL::escapeString($payload_id) . '\'';

      MySQL::query($query);
    }
    catch(Exception $e)
    {
      throw new Exception('Could not update order details.');
    }
  }

  public static function cancelPendingOrder($params)
  {
    if (isset($params['payload_id']))
    {
      try
      {
        $query = 'SELECT id FROM pending_orders WHERE payload_id = \'' . MySQL::escapeString($params['payload_id']) . '\' AND session_id = \'' . MySQL::escapeString(md5(Session::getSessionId())) . '\'';
        $pending_order_id = MySQL::selectCell($query);
      }
      catch(Exception $e)
      {
        throw new Exception('Could not retreive order details.');
      }

      if ($pending_order_id === null)
      {
        throw new Exception('Original order was not found.');
      }

      try
      {
        $query = 'UPDATE pending_orders SET shipping_tax = NULL, shipping_type = NULL, shipping_method = NULL, shipping_cost = NULL, shipping_instructions = NULL, payment_type = NULL, total = NULL, status = \'pending\' WHERE id = \'' . MySQL::escapeString($pending_order_id) . '\'';
        MySQL::query($query);
      }
      catch(Exception $e)
      {
        throw new Exception('Could not update order data.');
      }
    }
  }
  
  public static function completeOrder($params)
  {
    try
    {
      try
      {
        $pending_order_id = $params['pending_order_id'];

        MySQL::startTransaction();

        $query = 'INSERT INTO orders(payload_id, number, price_type_id, language_code, subtotal, handling, shipping_tax_mode, shipping_tax, '.
                 'shipping_tax_description, shipping_type, shipping_method, shipping_cost, shipping_instructions, payment_type, '.
                 'token, transaction_id, payment_date, payment_status, total, xml_data) '.
                 'SELECT payload_id, number, price_type_id, language_code, subtotal, handling, shipping_tax_mode, shipping_tax, '.
                 'shipping_tax_description, shipping_type, shipping_method, shipping_cost, shipping_instructions, payment_type, '.
                 (Valid::hasValue($params['token']) ? '\'' . MySQL::escapeString($params['token']) . '\'' : 'NULL') . ', '.
                 (Valid::hasValue($params['transaction_id']) ? '\'' . MySQL::escapeString($params['transaction_id']) . '\'' : 'NULL') . ', '.
                 (Valid::hasValue($params['payment_date']) ? '\'' . MySQL::escapeString(@date('Y-m-d H:i:s', strtotime($params['payment_date']))) . '\'' : 'NULL') . ', '.
                 (Valid::hasValue($params['payment_status']) ? '\'' . MySQL::escapeString($params['payment_status']) . '\'' : 'NULL') . ', '.
                 'total, xml_data FROM pending_orders WHERE id = \'' . MySQL::escapeString($pending_order_id) . '\'';

        MySQL::query($query);

        $order_id = MySQL::getInsertId();

        $query = 'INSERT INTO order_items(order_id, part_id, quantity, price) '.
                 'SELECT \'' . MySQL::escapeString($order_id) . '\', part_id, quantity, price '.
                 'FROM pending_order_items WHERE pending_order_id = \'' . MySQL::escapeString($pending_order_id) . '\'';

        MySQL::query($query);

        $query = 'INSERT INTO order_shipping_details(order_id, full_name, address1, address2, city, postal_code, country_code, state_code, email, phone) SELECT \'' . MySQL::escapeString($order_id) . '\', '.
                 'full_name, address1, address2, city, postal_code, country_code, state_code, email, phone FROM pending_order_shipping_details WHERE pending_order_id = \'' . MySQL::escapeString($pending_order_id) . '\'';

        MySQL::query($query);

        $query = 'DELETE FROM pending_orders WHERE id = \'' . MySQL::escapeString($pending_order_id) . '\'';

        MySQL::query($query);

        $query = 'DELETE FROM pending_order_items WHERE pending_order_id = \'' . MySQL::escapeString($pending_order_id) . '\'';

        MySQL::query($query);

        $query = 'DELETE FROM pending_order_shipping_details WHERE pending_order_id = \'' . MySQL::escapeString($pending_order_id) . '\'';

        MySQL::query($query);

        MySQL::commitTransaction();

        try
        {
          Notifications::sendConfirmationMail($order_id);
        }
        catch (Exception $e)
        {
          @file_put_contents(LOGS . DS . gmdate('Y_m_d') . '.log',
                             gmdate(MONSTRA_LOG_DATE_FORMAT) . ' --- ' . '[Notifications plugin]' . ' --- ' . $e->getMessage() . ' --- ' . 'Exception thrown on line '.$e->getLine().' in '.$e->getFile() . "\n",
                             FILE_APPEND);
        }

        try
        {
          $query = 'DELETE FROM shipping_cache WHERE session_id = \'' . MySQL::escapeString(md5(Session::getSessionId())) . '\'';
          MySQL::query($query);
        }
        catch (Exception $e)
        {
        }
      }
      catch (Exception $e)
      {
        try
        {
          MySQL::rollbackTransaction();
        }
        catch(Exception $ex)
        {
        }

        throw new Exception('Could not update order data.');
      }
    }
    catch (Exception $e)
    {
      throw new Exception($e->getMessage());
    }
  }

  public static function insertBadOrder($xml_data)
  {
    try
    {
      ob_start();
      print_r($_REQUEST);
      $request_data = ob_get_contents();
      ob_end_clean();
      // @file_put_contents(STORAGE . DS . 'bad_orders' . DS . date('Y-m-d-H-i-s') . '.xml', $xml_data . "\r\n\r\n" . $request_data);
      //$query = 'INSERT IGNORE INTO bad_orders SET md5_data = \'' . MySQL::escapeString(md5($xml_data)) . '\', session_id = \'' . MySQL::escapeString(md5(Session::getSessionId())) . '\', xml_data = \'' . MySQL::escapeString($xml_data) . '\'';
      $query = 'INSERT INTO bad_orders(xml_data, request_data) VALUES(\'' . MySQL::escapeString($xml_data) . '\', \'' . MySQL::escapeString($request_data) . '\')';
      MySQL::query($query);
    }
    catch(Exception $e)
    {
    }
  }
}
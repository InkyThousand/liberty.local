<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<?php
Plugin::register( __FILE__,
                __('Checkout', 'checkout'),
                __('Checkout plugin', 'checkout'),
                '1.0.0',
                'razorolog',
                '',
                'checkout',
                'store');

Plugin::Admin('checkout', 'store');

class Checkout extends Frontend
{
  public static function main()
  {
  }

  protected static function route()
  {
    if (Uri::segment(0) == 'checkout' && !Uri::segment(1)) return 'checkout';
    if (Uri::segment(0) == 'checkout' && Uri::segment(1) == 'process') return 'process';
    if (Uri::segment(0) == 'checkout' && Uri::segment(1) == 'complete') return 'complete';
    if (Uri::segment(0) == 'checkout' && Uri::segment(1) == 'cancel') return 'cancel';
    return null;
  }

  public static function content()
  {
    if (self::route() == 'process')
    {
      self::processEzPartsOrder();
    }
    if (self::route() == 'checkout')
    {
      self::processCheckout();
    }
    if (self::route() == 'complete')
    {
      self::completeOrder();
    }
    if (self::route() == 'cancel')
    {
      self::cancelOrder();
    }
  }

  public static function completeOrder()
  {
    $error = null;

    $payload_id = null;
    $token = Text::trimStr(Request::get('token'));
    $payer_id = Text::trimStr(Request::get('PayerID'));

    try
    {
      if (!Session::exists('payment_confirmed'))
      {
        if (!Valid::hasValue($token))
        {
          Request::redirect(Site::url());
        }
        $result = Payment::completePayment($token);
        Orders::completeOrder($result);
        Session::set('payment_confirmed', true);
        Request::redirect(Site::url() . 'checkout/complete?id=' . urlencode($result['payload_id']) , 302, 0, true);
      }
      else
      {
        $payload_id = Request::request('id');
        Session::set('payment_confirmed', null);
      }
    }
    catch (Exception $e)
    {
      $error = $e->getMessage();
    }

    View::factory('store/checkout/views/frontend/complete')
        ->assign('token', $token)
        ->assign('payer_id', $payer_id)
        ->assign('payload_id', $payload_id)
        ->assign('error', $error)
        ->display();
  }

  public static function cancelOrder()
  {
    $error = null;
    $token = Text::trimStr(Request::get('token'));

    if (!Valid::hasValue($token))
    {
      Request::redirect(Site::url());
    }

    try
    {
      $result = Payment::cancelPayment($token);
      Orders::cancelPendingOrder($result);
    }
    catch (Exception $e)
    {
      $error = $e->getMessage();
    }

    View::factory('store/checkout/views/frontend/cancel')
        ->assign('token', $token)
        ->assign('error', $error)
        ->display();
  }

  public static function processEzPartsOrder()
  {
    $error = null;
    $redirectURL = null;

    if (Request::request('debug') == 'debug')
    {
      $orderXML = file_get_contents(STORAGE . DS . 'debug' . DS . 'order.xml');
    }
    else
    {
      $orderXML = urldecode(Request::request('cxml-urlencoded'));
    }

    try
    {
      $payload_id = Orders::processEzPartsOrder($orderXML);
      $redirectURL = Site::url() . 'checkout?id=' . urlencode($payload_id);
    }
    catch(Exception $e)
    {
      $error = $e->getMessage();
    }

    View::factory('store/checkout/views/frontend/process')
        ->assign('redirectURL', $redirectURL)
        ->assign('error', $error)
        ->display();
  }

  public static function processCheckout()
  {
    $payment_types = array('paypal', 'creditcard');
    $shipping_types = array('pickup', 'usps', 'ups', 'fedex', 'other', 'call');
    $shipping_type_labels = array('pickup' => 'Customer pickup', 
                                  'usps' => 'USPS',
                                  'ups' => 'UPS',
                                  'fedex' => 'FedEx',
                                  'other' => 'Other',
                                  'call' => 'Parts Representative call');

    $payment_types_allowed = Payment::getPaymentTypesAllowed();
    $shipping_types_allowed = Shipping::getShippingTypesAllowed();

    $paypal_init_error = false;
    $incorrect_shipping_details = false;

    $error = null;

    $payload_id = Text::trimStr(Request::request('id'));

    $step = Text::trimStr(Request::request('step'));
    $step = abs((integer)$step);

    $previous_step = null;
    $next_step = null;
    $pending_order_id = null;
    $order_number = null;
    $order_currency = null;

    $shipping_type = strtolower(Text::trimStr(Request::request('shipping_type')));
    $shipping_method = Text::trimStr(Request::request('shipping_method'));
    $shipping_instructions = Text::trimStr(Request::request('shipping_instructions'));
    $shipping_tax_mode = null;
    $payment_type = strtolower(Text::trimStr(Request::request('payment_type')));

    $order_subtotal = null;
    $shipping_cost = null;
    $shipping_tax = null;
    $handling = null;
    $redirectURL = null;

    try
    {
      if ($step < 1 || $step > 5)
      {
        $step = 1;
      }

      if (!in_array($shipping_type, $shipping_types_allowed))
      { 
        $shipping_type = null;
      }

      if (!in_array($payment_type, $payment_types_allowed))
      { 
        $payment_type = null;
      }

      if (!Valid::hasValue($payload_id))
      {
        throw new Exception('Order identifier was not specified.');
      }

      try
      {
        $query = 'SELECT pending_orders.id, number, currency, subtotal, handling, shipping_tax_mode, shipping_tax_percent, shipping_tax FROM pending_orders LEFT JOIN price_types ON price_types.id = pending_orders.price_type_id WHERE payload_id = \'' . MySQL::escapeString($payload_id) . '\' AND session_id = \'' . MySQL::escapeString(md5(Session::getSessionId())) . '\'';
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

      $incorrect_shipping_details = Orders::checkoutDisabled($payload_id);

      if ($incorrect_shipping_details)
      {
        throw new Exception('Incorrect shipping details.');
      }

      $pending_order_id = $row['id'];
      $order_number = $row['number'];

      if (!Valid::hasValue($order_number))
      {
        $order_number = $payload_id;
      }

      $order_currency = $row['currency'];
      $order_subtotal = (double)$row['subtotal'];
      $handling = (double)$row['handling'];
      $shipping_tax_mode = $row['shipping_tax_mode'];
      $shipping_tax_percent = $row['shipping_tax_percent'];
      $shipping_tax = (double)$row['shipping_tax'];

      $shipping_cost = null;
      $shipping_code = 0;
      $order_total = 0;

      if (Valid::hasValue($shipping_method) && in_array($shipping_type, array($shipping_types[1], $shipping_types[2], $shipping_types[3])))
      {
        try
        {
          $rates = Shipping::getUPSRates($pending_order_id);

          $found = false;

          foreach ($rates as $service_name => $rate)
          {
            if (strtolower(strip_tags($service_name)) == strtolower($shipping_method))
            {
              $shipping_cost = $rate['rate'];
              $found = true;
              break;
            }
          }

          if (!$found)
          {
            $shipping_method = null;
          }
        }
        catch(Exception $e)
        {
          $shipping_method = null;
        }
      }

      $previous_step = 1;
      $next_step = 2;

      // validate step

      if (!Valid::hasValue($shipping_type) && $step > 1)
      {
        $step = 1;
      }
      else
      {
        if (in_array($shipping_type, array($shipping_types[1], $shipping_types[2], $shipping_types[3])) && !Valid::hasValue($shipping_method) && $step > 2)
        {
          $step = 2;
        }
        else
        {
          if (!Valid::hasValue($payment_type) && $step > 5)
          {
            $step = 5;
          }
        }
      }

      switch($step)
      {
        case 2:
                $previous_step = 1;

                if (in_array($shipping_type, array($shipping_types[0], $shipping_types[4])))
                {
                  $step = 4;
                  $next_step = 5;
                }
                else
                {
                  $next_step = 3;
                }

                break;

       case 3:
                if (in_array($shipping_type, array($shipping_types[0], $shipping_types[4])))
                {
                  $previous_step = 1;
                  $step = 4;
                  $next_step = 5;
                }
                else
                {
                  $previous_step = 2;
                  $next_step = 4;
                }

                break;

       case 4:
                if (in_array($shipping_type, array($shipping_types[1], $shipping_types[2], $shipping_types[3])))
                {
                  $previous_step = 3;
                  $step = 4;
                  $next_step = 5;
                }
                else
                {
                  $previous_step = 1;
                  $next_step = 5;
                }

                break;
       case 5:
                if ($shipping_type != $shipping_types[4])
                {
                  $previous_step = 4;
                  $next_step = 6;
                }
                else
                {
                  $previous_step = 3;
                  $step = 6;
                  $next_step = 6;
                }

                break;
       case 6:
                $previous_step = 5;
                $next_step = 6;

                break;
      }

      if (!Valid::hasValue($shipping_type))
      {
        $shipping_type = $shipping_types_allowed[0];
      }

      if (!Valid::hasValue($payment_type))
      {
        $payment_type = $payment_types_allowed[0];
      }

      if ($step == 5)
      {
        $error = null;

        $order_total = $order_subtotal + $shipping_cost;

        if ($shipping_tax_mode == 'total')
        {
          $shipping_tax = ($order_total / 100) * $shipping_tax_percent;
          $shipping_tax = sprintf('%.2f', $shipping_tax);
          $order_total += (double)$shipping_tax;
        }
        else
        {
          if ($shipping_cost != 0)
          {
            $order_total += $shipping_tax;
          }
        }

        $order_total += $handling;

        try
        {
          try
          {
            MySQL::startTransaction();
          }
          catch (Exception $e)
          {
            throw new Exception('Could not update order details.');
          }

          try
          {
            Orders::updatePendingOrderDetails($payload_id, array(
                                                                 'shipping_tax' => $shipping_tax,
                                                                 'shipping_type' => $shipping_type,
                                                                 'shipping_method' => $shipping_method,
                                                                 'shipping_cost' => $shipping_cost,
                                                                 'shipping_instructions' => $shipping_instructions,
                                                                 'payment_type' => $payment_type,
                                                                 'total' => $order_total,
                                                                 'status' => 'paypaled'
                                                                ));
          }
          catch (Exception $e)
          {
            throw new Exception('Could not update order details.');
          }

          $params['returnURL'] = Site::url() . 'checkout/complete';
          $params['cancelURL'] = Site::url() . 'checkout/cancel';
          $params['description'] = 'Payment for order #' . $order_number;
          $params['custom'] = $payload_id;
          $params['payment'] = $payment_type;
          $params['currency'] = $order_currency;
          $params['handling'] = $handling;
          $params['tax'] = $shipping_tax;
          $params['shipping'] = $shipping_cost;
          $params['subtotal'] = $order_subtotal;
          $params['total'] = $order_total;

          $order_items = Orders::getPendingOrderItems($payload_id);

          $counter = 0;

          foreach ($order_items as $item)
          {
            $params['items'][$counter]['name'] = $item['description'];
            $params['items'][$counter]['amount'] = sprintf('%.2f', (double)$item['price']);
            $params['items'][$counter]['qty'] = $item['quantity'];
            $counter++;
          }

          try
          {
            $redirectURL = Payment::initPayment($params);
          }
          catch (Exception $e)
          {
            $paypal_init_error = true;
            throw new Exception('Could not initialize payment request: ' . $e->getMessage());
          }
          
          try
          {
             MySQL::commitTransaction();
          }
          catch (Exception $e)
          {
            throw new Exception('Could not update order details.');
          }
        }
        catch (Exception $e)
        {
          try
          {
            MySQL::rollbackTransaction();
          }
          catch (Exception $ex)
          {
          }
          
          throw new Exception($e->getMessage());
        }
      }
    }
    catch(Exception $e)
    {
      $error = $e->getMessage();
    }

    View::factory('store/checkout/views/frontend/checkout')
        ->assign('step', $step)
        ->assign('next_step', $next_step)
        ->assign('previous_step', $previous_step)

        ->assign('shipping_types_allowed', $shipping_types_allowed)
        ->assign('payment_types_allowed', $payment_types_allowed)
        ->assign('shipping_type_labels', $shipping_type_labels)

        ->assign('payload_id', $payload_id)
        ->assign('pending_order_id', $pending_order_id)
        ->assign('order_number', $order_number)
        ->assign('order_currency', $order_currency)

        ->assign('payment_type', $payment_type)
        ->assign('shipping_type', $shipping_type)
        ->assign('shipping_method', $shipping_method)
        ->assign('shipping_instructions', $shipping_instructions)
        ->assign('shipping_tax_mode', $shipping_tax_mode)

        ->assign('order_subtotal', $order_subtotal)
        ->assign('shipping_cost', $shipping_cost)
        ->assign('shipping_tax', $shipping_tax)
        ->assign('handling', $handling)

        ->assign('redirectURL', $redirectURL)

        ->assign('incorrect_shipping_details', $incorrect_shipping_details)
        ->assign('paypal_init_error', $paypal_init_error)

        ->assign('error', $error)
        ->display();
  }

  public static function title()
  {
    return 'Checkout';
  }
}
<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<?php
Plugin::register(__FILE__,
                 __('Notifications', 'notifications'),
                 __('E-Mail Notifications plugin', 'notifications'),
                 '1.0.0',
                 'razorolog',
                 '',
                 null,
                 'store');

Plugin::Admin('notifications', 'store');

Javascript::add('public/assets/js/jquery.blockUI.js', 'backend');
Javascript::add('plugins/store/notifications/js/' . Option::get('language') . '.notifications.js', 'backend');

class Notifications
{
  protected static $instance = null;

  protected function __clone()
  {
  }

  function __construct()
  {
  }

  public static function init()
  {
    if (!isset(self::$instance))
     self::$instance = new Notifications();
    return self::$instance;
  }

  public static function sendConfirmationMail($order_id)
  {
    $notifications_options_tbl = new Table('notifications');
    $notifications_options = $notifications_options_tbl->select(null, null);

    $use_html = (bool)$notifications_options['use_html'];
    $use_smtp = (bool)$notifications_options['use_smtp'];
    $smtp_host = $notifications_options['smtp_host'];
    $smtp_port = $notifications_options['smtp_port'];
    $smtp_auth_username = $notifications_options['smtp_auth_username'];
    $smtp_auth_password = $notifications_options['smtp_auth_password'];

    $from_name = $notifications_options['from_name'];
    $from_address = $notifications_options['from_address'];

    $customer_allow = (bool)$notifications_options['customer_allow'];
    $customer_subject = $notifications_options['customer_subject'];
    $customer_body = @file_get_contents(STORAGE . DS . 'templates' . DS . 'customer.txt');

    $dealer_allow = (bool)$notifications_options['dealer_allow'];
    $dealer_to_name = $notifications_options['dealer_to_name'];
    $dealer_to_address = $notifications_options['dealer_to_address'];

    $dealer_subject = $notifications_options['dealer_subject'];
    $dealer_body = @file_get_contents(STORAGE . DS . 'templates' . DS . 'dealer.txt');

    $order_details = array();
    $order_items = array();

    try
    {
      $query = 'SELECT * FROM orders LEFT JOIN price_types ON price_types.id = orders.price_type_id WHERE orders.id = \'' . MySQL::escapeString($order_id) . '\'';

      if (($row = MySQL::selectRow($query)) === null)
      {
        throw new Exception('Order not found');
      }
    }
    catch(Exception $e)
    {
      throw new Exception($e->getMessage());
    }

    $language_code = $row['language_code'];

    try
    {
      $query = 'SELECT p.code, pl.description, oi.quantity, oi.price FROM order_items oi '.
               'LEFT JOIN parts p ON p.id = oi.part_id LEFT JOIN part_localization pl ON pl.part_id = oi.part_id '.
               'WHERE order_id = \'' . MySQL::escapeString($order_id) . '\'  AND pl.language_code = \'' . MySQL::escapeString($language_code) . '\'';

      $rs_order_items = MySQL::query($query);
    }
    catch(Exception $e)
    {
      throw new Exception($e->getMessage());
    }

    try
    {
      $query = 'SELECT * FROM order_shipping_details WHERE order_id = \'' . MySQL::escapeString($order_id) . '\'';
      $shipping_info_row = MySQL::selectRow($query);
    }
    catch(Exception $e)
    {
      throw new Exception($e->getMessage());
    }

    $xml_data = $row['xml_data'];

    libxml_use_internal_errors(true);
    $xml = @new SimpleXMLElement($xml_data);

    $order_data = $xml->xpath('/cXML/Request/OrderRequest/OrderRequestHeader');
    $order_type_data = @$xml->xpath('/cXML/Request/OrderRequest/OrderRequestHeader/Extrinsic[@name=\'OrderType\']');

    $order_details['order_type'] = $order_type_data[0];
    $order_details['comments'] = @$order_data[0]->Comments;

    $order_details['order_id'] = $row['payload_id'];
    $order_details['order_number'] = $row['number'];
    $order_details['order_date'] = date(MONSTRA_DATE_FORMAT, strtotime($row['order_date']));

    $order_details['subtotal'] = $row['subtotal'];
    $order_details['handling'] = $row['handling'];

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
    $order_details['ship_to']['state'] = Valid::hasValue($shipping_info_row['state_code']) ? Countries::getStateName($shipping_info_row['country_code'], $shipping_info_row['state_code']) : null;
    $order_details['ship_to']['email'] = $shipping_info_row['email'];
    $order_details['ship_to']['phone'] = $shipping_info_row['phone'];

    $order_contents = str_pad('Part No', 15, ' ', STR_PAD_RIGHT) . ' ' . str_pad('Description', 60, ' ', STR_PAD_RIGHT) . ' ' .  str_pad('Qty', 5, ' ', STR_PAD_BOTH) . ' ' . str_pad('UOM', 6, ' ', STR_PAD_BOTH) . ' ' .  str_pad('Price', 13, ' ', STR_PAD_LEFT) . ' ' . str_pad('Total Price', 13, ' ', STR_PAD_LEFT) . "\r\n";

    $width = strlen($order_contents) - 2;

    $order_contents .= str_pad('', $width, '-', STR_PAD_RIGHT) . "\r\n";

    while ($row = MySQL::fetch($rs_order_items))
    {
      $total_price = sprintf('%.2f', $row['price'] * $row['quantity']);
      $order_contents .= str_pad($row['code'], 15, ' ', STR_PAD_RIGHT) . ' ' . str_pad($row['description'], 60, ' ', STR_PAD_RIGHT) . ' ' . str_pad($row['quantity'], 5, ' ', STR_PAD_BOTH) . ' ' . str_pad('', 6, ' ', STR_PAD_BOTH) . ' ' . str_pad($order_details['currency'] . $row['price'], 13, ' ', STR_PAD_LEFT) . ' ' . str_pad($order_details['currency'] . $total_price, 13, ' ', STR_PAD_LEFT) . "\r\n";
    }

    MySQL::free($rs_order_items);

    $order_contents .= str_pad('', $width, '-', STR_PAD_RIGHT) . "\r\n";

    $order_contents .= str_pad('Order Subtotal: ' . $order_details['currency'] . $order_details['subtotal'], $width, ' ', STR_PAD_LEFT) . "\r\n";
    $order_contents .= str_pad('Handling: ' . $order_details['currency'] . $order_details['handling'], $width, ' ', STR_PAD_LEFT) . "\r\n";

    if ($order_details['shipping_cost'] > 0)
    {
      $order_contents .= str_pad('Shipping Cost: ' . $order_details['currency'] . $order_details['shipping_cost'], $width, ' ', STR_PAD_LEFT) . "\r\n";
    }

    if ($order_details['shipping_tax'] > 0)
    {
      $order_contents .= str_pad($order_details['shipping_tax_description'] . ': ' . $order_details['currency'] . $order_details['shipping_tax'], $width, ' ', STR_PAD_LEFT) . "\r\n";
    }

    $order_contents .= str_pad('Total: ' . $order_details['currency'] . $order_details['total'], $width, ' ', STR_PAD_LEFT) . "\r\n";

    $customer_name = $order_details['ship_to']['name'];
    $customer_email = $order_details['ship_to']['email'];

    $phpMailerInitialized = false;
    $phpMailer = null;
    
    if ($customer_allow && Valid::hasValue($customer_email))
    {
      $customer_body = str_replace('%CUSTOMER_NAME%', $customer_name, $customer_body);
      $customer_body = str_replace('%ORDER_ID%', $order_details['order_id'], $customer_body);
      $customer_body = str_replace('%ORDER_NUMBER%', $order_details['order_number'], $customer_body);

      $customer_body = str_replace('%ORDER_DATE%', $order_details['order_date'], $customer_body);
      $customer_body = str_replace('%ORDER_CONTENTS%', $order_contents, $customer_body);
      
      $customer_body = str_replace('%SHIPPING_INFO_START%', '', $customer_body);
      $customer_body = str_replace('%METHOD%', $order_details['shipping_method'], $customer_body);
      $customer_body = str_replace('%ADDRESS1%', $order_details['ship_to']['address1'], $customer_body);
      $customer_body = str_replace('%ADDRESS2%', $order_details['ship_to']['address2'], $customer_body);
      $customer_body = str_replace('%CITY%', $order_details['ship_to']['city'], $customer_body);
      $customer_body = str_replace('%POSTAL_CODE%', $order_details['ship_to']['postal_code'], $customer_body);
      $customer_body = str_replace('%COUNTRY%', $order_details['ship_to']['country'], $customer_body);

      if (Valid::hasValue($order_details['ship_to']['state']))
      {
        $customer_body = str_replace('%STATE%', $order_details['ship_to']['state'], $customer_body);
      }
      else
      {
        $customer_body = str_replace("State: %STATE%\r\n", null, $customer_body);
      }

      $customer_body = str_replace('%EMAIL%', $order_details['ship_to']['email'], $customer_body);
      $customer_body = str_replace('%PHONE%', $order_details['ship_to']['phone'], $customer_body);
      $customer_body = str_replace('%SHIPPING_INFO_END%', '', $customer_body);

      $customer_subject = str_replace('%ORDER_NUMBER%', $order_details['order_number'], $customer_subject);
      $customer_subject = str_replace('%ORDER_ID%', $order_details['order_id'], $customer_subject);

      try
      {
        $phpMailer = new PHPMailer(true);
        $phpMailer->IsHTML(false);

        if ($use_smtp)
        {
          $phpMailer->IsSMTP();
          $phpMailer->SMTPKeepAlive = true;

          $phpMailer->Host = $smtp_host;
          $phpMailer->Port = $smtp_port;

          if (Valid::hasValue($smtp_auth_username))
          {
            $phpMailer->SMTPAuth = true;
            $phpMailer->Username = $smtp_auth_username;
            $phpMailer->Password = $smtp_auth_password;
          }
        }

        $phpMailerInitialized = true;

        $phpMailer->SetFrom($from_address, $from_name);
        $phpMailer->AddAddress($customer_email, $customer_name);
        $phpMailer->Subject = $customer_subject;
        $phpMailer->Body = $customer_body;
        $phpMailer->Send();
      }
      catch (Exception $e)
      {
        throw new Exception($e->getMessage());
      }
    }

    if ($dealer_allow && Valid::hasValue($dealer_to_address))
    {
      $dealer_body = str_replace('%CUSTOMER_NAME%', $customer_name, $dealer_body);

      $dealer_body = str_replace('%ORDER_ID%', $order_details['order_id'], $dealer_body);
      $dealer_body = str_replace('%ORDER_NUMBER%', $order_details['order_number'], $dealer_body);
      $dealer_body = str_replace('%ORDER_DATE%', $order_details['order_date'], $dealer_body);
      $dealer_body = str_replace('%ORDER_CONTENTS%', $order_contents, $dealer_body);
      $dealer_body = str_replace('%SHIPPING_TYPE%', $order_details['shipping_type'], $dealer_body);

      $dealer_body = str_replace('%SHIPPING_METHOD%', $order_details['shipping_method'], $dealer_body);
      $dealer_body = str_replace('%SHIPPING_NAME%', $order_details['ship_to']['name'], $dealer_body);
      $dealer_body = str_replace('%SHIPPING_ADDRESS1%', $order_details['ship_to']['address1'], $dealer_body);
      $dealer_body = str_replace('%SHIPPING_ADDRESS2%', $order_details['ship_to']['address2'], $dealer_body);
      $dealer_body = str_replace('%SHIPPING_CITY%', $order_details['ship_to']['city'], $dealer_body);
      $dealer_body = str_replace('%SHIPPING_POSTAL_CODE%', $order_details['ship_to']['postal_code'], $dealer_body);
      $dealer_body = str_replace('%SHIPPING_COUNTRY%', $order_details['ship_to']['country'], $dealer_body);
      $dealer_body = str_replace('%SHIPPING_STATE%', $order_details['ship_to']['state'], $dealer_body);
      $dealer_body = str_replace('%SHIPPING_EMAIL%', $order_details['ship_to']['email'], $dealer_body);
      $dealer_body = str_replace('%SHIPPING_PHONE%', $order_details['ship_to']['phone'], $dealer_body);

      $dealer_body = str_replace('%SHIPPING_INSTRUCTIONS%', $order_details['shipping_instructions'], $dealer_body);
      $dealer_body = str_replace('%COMMENTS%', $order_details['comments'], $dealer_body);

      $dealer_body = str_replace('%PAYMENT_TYPE%', $order_details['payment_type'], $dealer_body);
      $dealer_body = str_replace('%TRANSACTION_ID%', $order_details['payment_transaction_id'], $dealer_body);
      $dealer_body = str_replace('%PAYMENT_DATE%', $order_details['payment_date'], $dealer_body);
      $dealer_body = str_replace('%PAYMENT_STATUS%', $order_details['payment_status'], $dealer_body);

      $dealer_subject = str_replace('%ORDER_NUMBER%', $order_details['order_number'], $dealer_subject);
      $dealer_subject = str_replace('%ORDER_ID%', $order_details['order_id'], $dealer_subject);

      try
      {
        if (!$phpMailerInitialized)
        {
          $phpMailer = new PHPMailer(true);
          $phpMailer->IsHTML(false);

          if ($use_smtp)
          {
            $phpMailer->IsSMTP();
            $phpMailer->SMTPKeepAlive = true;

            $phpMailer->Host = $smtp_host;
            $phpMailer->Port = $smtp_port;

            if (Valid::hasValue($smtp_auth_username))
            {
              $phpMailer->SMTPAuth = true;
              $phpMailer->Username = $smtp_auth_username;
              $phpMailer->Password = $smtp_auth_password;
            }
          }

          $phpMailerInitialized = true;
        }
        else
        {
          $phpMailer->ClearAllRecipients();
        }

        $phpMailer->SetFrom($from_address, $from_name);
        $phpMailer->AddAddress($dealer_to_address, $dealer_to_name);
        $phpMailer->Subject = $dealer_subject;
        $phpMailer->Body = $dealer_body;
        $phpMailer->Send();
      }
      catch (Exception $e)
      {
        throw new Exception($e->getMessage());
      }
    }

    if ($phpMailerInitialized)
    {
      $phpMailer->SmtpClose();
    }
  }
}
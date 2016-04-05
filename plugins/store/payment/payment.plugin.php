<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<?php
Plugin::register( __FILE__,
              __('Payment', 'payment'),
              __('Payment plugin', 'payment'),
              '1.0.0',
              'razorolog',
              '',
              null,
              'store');

Plugin::Admin('payment', 'store');

Payment::init();

class Payment 
{
  protected static $instance = null;
  private static $payment_options = null;

  protected function __clone() 
  {
  }

  function __construct()
  {
    $payment_options_tbl = new Table('payment');
    self::$payment_options = $payment_options_tbl->select(null, null);

    $payment_credentials_tbl = new Table('payment_credentials');
    $payment_credentials = $payment_credentials_tbl->select('[active="1"]', null);

    PayPal::init($payment_credentials['username'],
                 $payment_credentials['password'],
                 $payment_credentials['signature'],
                 $payment_credentials['version'],
                 $payment_credentials['mode']
                );
  }
  
  public static function init()
  {
    if (!isset(self::$instance))
     self::$instance = new Payment();
    return self::$instance;
  }
  
  public static function getPaymentTypesAllowed()
  {
    $result = array();

    if ((bool)self::$payment_options['payment_paypal'])
    {
      $result[] = 'paypal';
    }

    if ((bool)self::$payment_options['payment_credit_card'])
    {
      $result[] = 'creditcard';
    }

    return $result;
  }


  public static function initPayment($params)
  {
    return PayPal::initExpressCheckout($params);
  }


  public static function cancelPayment($token)
  {
    try
    {
      $detailsArray = PayPal::getExpressCheckoutDetails($token);
    }
    catch(Exception $e)
    {
      throw new Exception('Could not verify PayPal transaction status.');
    }

    $payload_id = isset($detailsArray['CUSTOM']) ? $detailsArray['CUSTOM'] : null;
  
    try
    {
      $query = 'SELECT id FROM pending_orders WHERE payload_id = \'' . MySQL::escapeString($payload_id) . '\' AND session_id = \'' . MySQL::escapeString(md5(Session::getSessionId())) . '\'';
      $pending_order_id = MySQL::selectCell($query);
    }
    catch(Exception $e)
    {
      throw new Exception('Could not retreive order details.');
    }

    if ($pending_order_id === null)
    {
      throw new Exception('Originating order was not found.');
    }

    $checkout_status = isset($detailsArray['CHECKOUTSTATUS']) ? $detailsArray['CHECKOUTSTATUS'] : null;
    $transaction_id = isset($detailsArray['PAYMENTREQUESTINFO_0_TRANSACTIONID']) ? strtoupper($detailsArray['PAYMENTREQUESTINFO_0_TRANSACTIONID']) : null;

    $result = null;

    if ($checkout_status == 'PaymentActionNotInitiated' && empty($transaction_id))
    {
      $result['payload_id'] = $payload_id;
    }

    return $result;
  }

  public static function completePayment($token)
  {
    $token = (string)$token;

    try
    {
      try
      {
        $detailsArray = PayPal::getExpressCheckoutDetails($token);
      }
      catch(Exception $e)
      {
        throw new Exception('Could not verify PayPal transaction status.');
      }

      $payload_id = isset($detailsArray['CUSTOM']) ? $detailsArray['CUSTOM'] : null;

      try
      {
        $query = 'SELECT id FROM pending_orders WHERE payload_id = \'' . MySQL::escapeString($payload_id) . '\' AND session_id = \'' . MySQL::escapeString(md5(Session::getSessionId())) . '\'';
        $pending_order_id = MySQL::selectCell($query);
      }
      catch(Exception $e)
      {
        throw new Exception('Could not retreive order details.');
      }

      if ($pending_order_id === null)
      {
        throw new Exception('Originating order was not found.');
      }

      $checkout_status = isset($detailsArray['CHECKOUTSTATUS']) ? $detailsArray['CHECKOUTSTATUS'] : null;
      $transaction_id = isset($detailsArray['PAYMENTREQUESTINFO_0_TRANSACTIONID']) ? strtoupper($detailsArray['PAYMENTREQUESTINFO_0_TRANSACTIONID']) : null;
       
      $result['payload_id'] = $payload_id;
      $result['token'] = $token;
      $result['pending_order_id'] = $pending_order_id;

      if ($checkout_status == 'PaymentActionNotInitiated' && empty($transaction_id))
      {
        try
        {
          $resArray = PayPal::confirmPayment($token, $detailsArray['PAYERID'], $detailsArray['PAYMENTREQUEST_0_CURRENCYCODE'], $detailsArray['PAYMENTREQUEST_0_AMT']);
          $result['transaction_id'] = $resArray['TRANSACTIONID'];
          $result['payment_date'] = $resArray['ORDERTIME'];
          $result['payment_status'] = $resArray['STATUS'];
        }
        catch(Exception $e)
        {
          throw new Exception('Could not confirm order with PayPal: ' . $e->getMessage());
        }
      }
      else
      {
        throw new Exception('Order already confirmed.');
      }

      return $result;
    }
    catch (Exception $e)
    {
      throw new Exception($e->getMessage());
    }
  }
}

class PayPal
{
  private static $APIUsername, $APIPassword, $APISignature, $APIVersion, $APIEndpoint, $APISOAPEndpoint, $redirectURL, $validationURL;

  protected function __construct() 
  {
  }

  public static function init($APIUsername, $APIPassword, $APISignature, $APIVersion, $mode)
  {
    $APIUsername = (string)$APIUsername;
    $APIPassword = (string)$APIPassword;
    $APISignature = (string)$APISignature;
    $APIVersion = (string)$APIVersion;

    self::$APIUsername = $APIUsername;
    self::$APIPassword = $APIPassword;
    self::$APISignature = $APISignature;
    self::$APIVersion = $APIVersion;

    if ($mode == 'sandbox')
    {
      self::$APIEndpoint = 'https://api-3t.sandbox.paypal.com/nvp';
      self::$APISOAPEndpoint = 'https://api-3t.sandbox.paypal.com:443/2.0/';
      self::$redirectURL = 'https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&useraction=commit&token=';
      self::$validationURL = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
    }
    else
    {
      self::$APIEndpoint = 'https://api-3t.paypal.com/nvp';
      self::$APISOAPEndpoint = 'https://api-3t.paypal.com:443/2.0/';
      self::$redirectURL = 'https://paypal.com/webscr?cmd=_express-checkout&useraction=commit&token=';
      self::$validationURL = 'https://paypal.com/cgi-bin/webscr';
    }
  }

  public static function initExpressCheckout($params)
  {
    $xml = '<?xml version="1.0" encoding="utf-8"?>'.
           '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">'.
           '<soap:Header>'.
           '<RequesterCredentials xmlns="urn:ebay:api:PayPalAPI">'.
           '<Credentials xmlns="urn:ebay:apis:eBLBaseComponents">'.
           '<Username>' . htmlspecialchars(self::$APIUsername) .'</Username>'.
           '<ebl:Password xmlns:ebl="urn:ebay:apis:eBLBaseComponents">' . htmlspecialchars(self::$APIPassword) . '</ebl:Password>'.
           '<Signature>' . htmlspecialchars(self::$APISignature) . '</Signature>'.
           '</Credentials>'.
           '</RequesterCredentials>'.
           '</soap:Header>'.
           '<soap:Body>'.
           '<SetExpressCheckoutReq xmlns="urn:ebay:api:PayPalAPI">'.
           '<SetExpressCheckoutRequest>'.
           '<Version xmlns="urn:ebay:apis:eBLBaseComponents">' . htmlspecialchars(self::$APIVersion) . '</Version>'.
           '<SetExpressCheckoutRequestDetails xmlns="urn:ebay:apis:eBLBaseComponents">'.
           '<ReturnURL>' . htmlspecialchars($params['returnURL']) . '</ReturnURL>'.
           '<CancelURL>' . htmlspecialchars($params['cancelURL']) . '</CancelURL>'.
           '<PaymentDetails>'.
           '<OrderTotal currencyID="' . htmlspecialchars($params['currency']) . '">' . htmlspecialchars($params['total']) . '</OrderTotal>'.
           '<ItemTotal currencyID="' . htmlspecialchars($params['currency']) . '">' . htmlspecialchars($params['subtotal']) . '</ItemTotal>'.
           '<ShippingTotal currencyID="' . htmlspecialchars($params['currency']) . '">' . htmlspecialchars($params['shipping']) . '</ShippingTotal>'.
           '<TaxTotal currencyID="' . htmlspecialchars($params['currency']) . '">' . htmlspecialchars($params['tax']) . '</TaxTotal>'.
           '<HandlingTotal currencyID="' . htmlspecialchars($params['currency']) . '">' . htmlspecialchars($params['handling']) . '</HandlingTotal>';

    foreach ($params['items'] as $item)
    {
       $xml.= '<PaymentDetailsItem>'.
              '<Name>' . htmlspecialchars($item['name']) .'</Name>'.
              '<Amount currencyID="' . htmlspecialchars($params['currency']) . '">' . htmlspecialchars($item['amount']) . '</Amount>'.
              '<Quantity>' . htmlspecialchars($item['qty']) . '</Quantity>'.
              '</PaymentDetailsItem>';
    }
    
    if (@$params['custom'])
    {
      $xml .= '<Custom>' . htmlspecialchars($params['custom']) . '</Custom>';
    }

    $xml .= '</PaymentDetails>'.
            '<PaymentAction>Sale</PaymentAction>'.
            '<SolutionType>Sole</SolutionType>'.
            '<LandingPage>' . ($params['payment'] == 'paypal' ? 'Login' : 'Billing') .'</LandingPage>'.
            '</SetExpressCheckoutRequestDetails>'.
            '</SetExpressCheckoutRequest>'.
            '</SetExpressCheckoutReq>'.
            '</soap:Body>'.
            '</soap:Envelope>';

    try
    {
      $resArray = PayPal::execPayPalSOAPMethod('SetExpressCheckout', $xml);
    }
    catch(Exception $e)
    {
      throw new Exception($e->getMessage());
    }

    $ack = strtoupper($resArray['ACK']);

    if ($ack != 'SUCCESS' && $ack != 'SUCCESSWITHWARNING')
    {
      throw new Exception(urldecode(trim($resArray['ERROR']['SHORTMESSAGE'], '.') . '. ' . trim($resArray['ERROR']['LONGMESSAGE'], '.') . '. (Error code: ' . $resArray['ERROR']['CODE'] . ')'));
    }

    return self::$redirectURL . $resArray['TOKEN'];
  }

  public static function getExpressCheckoutDetails($token)
  {
    $nvps = '&TOKEN=' . $token;

    try
    {
      $resArray = PayPal::execPayPalNVPMethod('GetExpressCheckoutDetails', $nvps);
    }
    catch(Exception $e)
    {
      throw new Exception($e->getMessage());
    }

    if (!array_key_exists('ACK', $resArray))
    {
      throw new Exception('ACK field is missing from PayPal response.');
    }

    $ack = strtoupper($resArray['ACK']);

    if ($ack != 'SUCCESS' && $ack != 'SUCCESSWITHWARNING')
    {
      throw new Exception(urldecode(trim($resArray['L_SHORTMESSAGE0'], '.') . '. ' . $resArray['L_LONGMESSAGE0']));
    }

    return $resArray;
  }

  public static function confirmPayment($token, $payer_id, $currency_code, $amount)
  {
    $nvps = '&TOKEN=' . $token .
            '&PAYERID=' . $payer_id .
            '&PAYMENTREQUEST_0_PAYMENTACTION=Sale' .
            '&PAYMENTREQUEST_0_CURRENCYCODE=' . $currency_code .
            '&PAYMENTREQUEST_0_AMT=' . $amount;

    try
    {
      $resArray = PayPal::execPayPalNVPMethod('DoExpressCheckoutPayment', $nvps);
    }
    catch(Exception $e)
    {
      throw new Exception($e->getMessage);
    }

    if (!array_key_exists('ACK', $resArray))
    {
      throw new Exception('ACK field is missing from PayPal response.');
    }

    $ack = strtoupper($resArray['ACK']);

    if ($ack != 'SUCCESS' && $ack != 'SUCCESSWITHWARNING')
    {
      throw new Exception(urldecode(trim($resArray['ERROR']['SHORTMESSAGE'], '.') . '. ' . trim($resArray['ERROR']['LONGMESSAGE'], '.') . '. (Error code: ' . $resArray['CODE'] . ')'));
    }

    $returnArray = array();

    $returnArray['TRANSACTIONID'] = $resArray['PAYMENTINFO_0_TRANSACTIONID'];
    $returnArray['ORDERTIME'] = $resArray['PAYMENTINFO_0_ORDERTIME'];
    $returnArray['STATUS'] = $resArray['PAYMENTINFO_0_PAYMENTSTATUS'];

    return $returnArray;
  }

  public static function execPayPalSOAPMethod($methodName, $params)
  {
    $ch = curl_init();

    if ($ch === false)
    {
      throw new Exception('Error initializing connection to PayPal server.');
    }

    curl_setopt($ch, CURLOPT_URL, self::$APISOAPEndpoint);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);

    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

    $response = curl_exec($ch);

    if ($response === false)
    {
      throw new Exception('Error performing PayPal request.');
    } 

    curl_close($ch);

    libxml_use_internal_errors(true);
    $doc = new DOMDocument('1.0', 'utf-8');
    $doc->recover = false;

    if ($doc->LoadXML($response, LIBXML_NOWARNING | LIBXML_NOERROR) === false)
    {
      throw new Exception('Error parsing PayPal server output.');
    }

    $xpath = new DOMXPath($doc);

    $fault = $xpath->query('/SOAP-ENV:Envelope/SOAP-ENV:Body/SOAP-ENV:Fault');

    if ($fault->length > 0)
    {
      $faultstring = $xpath->query('/SOAP-ENV:Envelope/SOAP-ENV:Body/SOAP-ENV:Fault/faultstring');
      $faultstring = $faultstring->item(0)->textContent;

      throw new Exception('Error calling PayPal method. ' . $faultstring);
    }

    $errors = $xpath->query('/SOAP-ENV:Envelope/SOAP-ENV:Body/ns:SetExpressCheckoutResponse/ebl:Errors');

    if ($errors->length > 0)
    {
      $result['ERROR']['SEVERITY'] = $xpath->query('/SOAP-ENV:Envelope/SOAP-ENV:Body/ns:SetExpressCheckoutResponse/ebl:Errors/ebl:SeverityCode');
      $result['ERROR']['SEVERITY'] = $result['ERROR']['SEVERITY']->item(0)->textContent;

      $result['ERROR']['CODE'] = $xpath->query('/SOAP-ENV:Envelope/SOAP-ENV:Body/ns:SetExpressCheckoutResponse/ebl:Errors/ebl:ErrorCode');
      $result['ERROR']['CODE'] = $result['ERROR']['CODE']->item(0)->textContent;

      $result['ERROR']['SHORTMESSAGE'] = $xpath->query('/SOAP-ENV:Envelope/SOAP-ENV:Body/ns:SetExpressCheckoutResponse/ebl:Errors/ebl:ShortMessage');
      $result['ERROR']['SHORTMESSAGE'] = $result['ERROR']['SHORTMESSAGE']->item(0)->textContent;

      $result['ERROR']['LONGMESSAGE'] = $xpath->query('/SOAP-ENV:Envelope/SOAP-ENV:Body/ns:SetExpressCheckoutResponse/ebl:Errors/ebl:LongMessage');
      $result['ERROR']['LONGMESSAGE'] = $result['ERROR']['LONGMESSAGE']->item(0)->textContent;
    }

    $result['ACK'] = $xpath->query('/SOAP-ENV:Envelope/SOAP-ENV:Body/ns:SetExpressCheckoutResponse/ebl:Ack');
    $result['ACK'] = $result['ACK']->length > 0 ? $result['ACK']->item(0)->textContent : null;

    $result['TOKEN'] = $xpath->query('/SOAP-ENV:Envelope/SOAP-ENV:Body/ns:SetExpressCheckoutResponse/ns:Token');
    $result['TOKEN'] = $result['TOKEN']->length > 0 ? $result['TOKEN']->item(0)->textContent : null;

    return $result;
  }

  public static function execPayPalNVPMethod($methodName, $params)
  {
    $post_fields = 'USER=' . urlencode(self::$APIUsername) .
                   '&PWD=' . urlencode(self::$APIPassword) .
                   '&SIGNATURE=' . urlencode(self::$APISignature) .
                   '&VERSION=' . urlencode(self::$APIVersion) .
                   '&METHOD=' . urlencode($methodName) .
                   $params;

    $ch = curl_init();

    if ($ch === false)
    {
      throw new Exception('Error initializing connection to PayPal server.');
    }

    curl_setopt($ch, CURLOPT_URL, self::$APIEndpoint);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);

    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

    $response = curl_exec($ch);

    if ($response === false)
    {
      throw new Exception('Error performing PayPal request.');
    }

    curl_close($ch);

    $response_array = explode('&', $response);
    $result = array();

    foreach ($response_array as $i => $value)
    {
      $tmp_array = explode('=', $value);

      if (sizeof($tmp_array) > 1) 
      {
        $result[$tmp_array[0]] = urldecode($tmp_array[1]);
      }
    }

    return $result;
  }
}
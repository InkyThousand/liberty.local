<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<?php
Plugin::register( __FILE__,
               __('Shipping', 'shipping'),
               __('Shipping plugin', 'shipping'),
               '1.0.0',
               'razorolog',
               '',
               null,
               'store');

Plugin::Admin('shipping', 'store');

Javascript::add('plugins/store/shipping/js/' . Option::get('language') . '.shipping.js', 'backend');

Shipping::init();

class Shipping
{
  protected static $instance = null;
  private static $shipping_settings = null;

  
  protected function __clone()
  {
  }

  
  function __construct()
  {
    $shipping_tbl = new Table('shipping');
    self::$shipping_settings = $shipping_tbl->select(null, null);
  }

  
  public static function init()
  {
    if (!isset(self::$instance))
     self::$instance = new Shipping();
    return self::$instance;
  }


  public static function getShippingTypesAllowed()
  {
    $result = array();

    if ((bool)self::$shipping_settings['shipping_pickup'])
    {
      $result[] = 'pickup';
    }

    if ((bool)self::$shipping_settings['shipping_usps'])
    {
      $result[] = 'usps';
    }

    if ((bool)self::$shipping_settings['shipping_ups'])
    {
      $result[] = 'ups';
    }

    if ((bool)self::$shipping_settings['shipping_fedex'])
    {
      $result[] = 'fedex';
    }

    if ((bool)self::$shipping_settings['shipping_other'])
    {
      $result[] = 'other';
    }

    if ((bool)self::$shipping_settings['shipping_call'])
    {
      $result[] = 'call';
    }

    return $result;
  }

  
  public static function getHandlingValue()
  {
    return (double)self::$shipping_settings['shipping_handling_value'];
  }


  public static function getDefaultWeight()
  {
    $result['pounds'] = (double)self::$shipping_settings['shipping_default_pounds'];
    $result['ounces'] = (double)self::$shipping_settings['shipping_default_ounces'];

    return $result;
  }


  public static function getUPSRates($pending_order_id)
  {
    try
    {
      try
      {
        $query = 'SELECT currency, sign FROM pending_orders LEFT JOIN price_types ON price_types.id = pending_orders.price_type_id WHERE pending_orders.id = \'' . MySQL::escapeString($pending_order_id) . '\'';
        $row = MySQL::selectRow($query);
      }
      catch(Exception $e)
      {
        throw new Exception(__('Could not verify order data.', 'shipping'));
      }

      if ($row === null)
      {
        throw new Exception(__('Order was not found.', 'shipping'));
      }

      try
      {
        $query = 'SELECT quantity, price, pounds, ounces, width, height, length, separate_box, items_per_box FROM pending_order_items JOIN parts ON parts.id = pending_order_items.part_id WHERE pending_order_id = \'' . MySQL::escapeString($pending_order_id) . '\'';
        $rs_order_items = MySQL::query($query);
      }
      catch(Exception $e)
      {
        throw new Exception(__('Could not retreive order details.', 'orders'));
      }

      try
      {
        $query = 'SELECT city, postal_code, country_code, state_code FROM pending_order_shipping_details WHERE pending_order_id = \'' . MySQL::escapeString($pending_order_id) . '\'';
        $shipping_info_row = MySQL::selectRow($query);
      }
      catch(Exception $e)
      {
        throw new Exception('Error retreiving shipping information.');
      }

      $currency = $row['currency'];
      $currency_sign = $row['sign'];

      $destination_city = $shipping_info_row['city'];
      $destination_zip = $shipping_info_row['postal_code'];
      $destination_country = $shipping_info_row['country_code'];
      $destination_state = $shipping_info_row['state_code'];

      $ups_services = array(
          '01' => array(
              'US' => 5,
              'CA' => 8,
              'PR' => 5
          ),
          '02' => array(
              'US' => 1,
              'CA' => 13,
              'PR' => 1
          ),
          '03' => array(
              'US' => 4,
              'PR' => 4
          ),
          '07' => array(
              'US' => 16,
              'EU' => 8,
              'CA' => 16,
              'PR' => 16,
              'MX' => 8,
              'OTHER_ORIGINS' => 8,
              'PL' => 8
          ),
          '08' => array(
              'US' => 15,
              'EU' => 13,
              'CA' => 15,
              'PR' => 15,
              'MX' => 13,
              'OTHER_ORIGINS' => 15,
              'PL' => 13
          ),
          '11' => array(
              'US' => 14,
              'EU' => 14,
              'CA' => 14,
              'MX' => 14,
              'PL' => 14,
              'OTHER_ORIGINS' => 14
          ),
          '12' => array(
              'US' => 3,
              'CA' => 3
          ),
          '13' => array(
              'US' => 7,
              'CA' => 12
          ),
          '14' => array(
              'US' => 6,
              'CA' => 9,
              'PR' => 6
          ),
          '54' => array(
              'US' => 17,
              'CA' => 17,
              'EU' => 17,
              'PR' => 17,
              'MX' => 11,
              'OTHER_ORIGINS' => 17,
              'PL' => 17
          ),
          '59' => array(
              'US' => 2
          ),
          '65' => array(
              'US' => 12,
              'EU' => 12,
              'CA' => 12,
              'PR' => 12,
              'MX' => 12,
              'OTHER_ORIGINS' => 12,
              'PL' => 12
          ),
          '82' => array(
              'PL' => 18
          ),
          '83' => array(
              'PL' => 19
          ),
          '85' => array(
              'PL' => 21
          ),
          '86' => array(
              'PL' => 22
          )
      );

      $ups_packages = array(
          '00' => array(
              'limits' => array(
                  'weight' => 150,
                  'length' => 108,
                  'width' => 108,
                  'height' => 108
              )
          ),
          '01' => array(
              'limits' => array(
                  'weight' => 1,
                  'length' => 9.5,
                  'width' => 12.5,
                  'height' => 0.25
              )
          ),
          '02' => array(
              'name' => 'Package'
          ),
          '03' => array(
              'limits' => array(
                  'length' => 6,
                  'width' => 38,
                  'height' => 6
              )
          ),
          '04' => array(
              'limits' => array(
                  'length' => 12.75,
                  'width' => 16,
                  'height' => 2
              )
          ),
          '21' => array(
              'limits' => array(
                  'length' => 13,
                  'width' => 18,
                  'height' => 3,
                  'weight' => 30
              )
          ),
          '24' => array(
              'limits' => array(
                  'length' => 17.375,
                  'width' => 19.375,
                  'height' => 14,
                  'weight' => 55.1
              )
          ),
          '25' => array(
              'limits' => array(
                  'length' => 13.25,
                  'width' => 16.5,
                  'height' => 10.75,
                  'weight' => 22
              )
          ),
          '30' => array(
          ),
          '2a' => array(
          ),
          '2b' => array(
          ),
          '2c' => array(
          )
      );


      $shipping_tbl = new Table('shipping');
      $shipping_credentials_tbl = new Table('shipping_credentials');
      $shipping_options_tbl = new Table('shipping_options');
      $shipping_methods_tbl = new Table('shipping_methods');

      $shipping_settings = $shipping_tbl->select(null, null);
      $shipping_options = $shipping_options_tbl->select(null, null);
      $shipping_credentials = $shipping_credentials_tbl->select(null, null);
      $shipping_methods = $shipping_methods_tbl->select('[shipper="ups"]', 'all', null, null);

      $ups_endpoint_url = $shipping_credentials['ups_endpoint_url'];
      $ups_access_license_no = $shipping_credentials['ups_access_license_no'];
      $ups_user_id = $shipping_credentials['ups_user_id'];
      $ups_password = $shipping_credentials['ups_password'];

      $origination_city = $shipping_settings['shipping_origination_city'];
      $origination_zip = $shipping_settings['shipping_origination_zip'];
      $origination_country = $shipping_settings['shipping_origination_country'];
      $origination_state = $shipping_settings['shipping_origination_state'];

      $default_width = $shipping_settings['shipping_default_width'];
      $default_height = $shipping_settings['shipping_default_height'];
      $default_length = $shipping_settings['shipping_default_length'];

      $default_pounds = $shipping_settings['shipping_default_pounds'];
      $default_ounces = $shipping_settings['shipping_default_ounces'];

      $ups_shipper_number = '';

      $ups_pickup_type = $shipping_options['ups_pickup_type'];
      $ups_destination_type = $shipping_options['ups_destination_type'];
      $ups_packaging_type = $shipping_options['ups_packaging_type'];

      $ups_delivery_confirmation = $shipping_options['ups_delivery_confirmation'];

      $ups_additional_handling = $shipping_options['ups_additional_handling'];
      $ups_saturday_pickup = $shipping_options['ups_saturday_pickup'];
      $ups_saturday_delivery = $shipping_options['ups_saturday_delivery'];

      $ups_negotiated_rates = false;

      if ($origination_country == 'US')
      {
        switch ($ups_pickup_type)
        {
          case '01':
                     $customer_classification = '01';
                     break;
          case '06':
          case '07':
          case '19':
          case '20':
                     $customer_classification = '03';
                     break;
          case '03':
          case '11':
                     $customer_classification = '04';
                     break;
          default:
                     $customer_classification = null;
        }
      }

      $package_limits = array();
      $specified_dims = array();

      if ($ups_packaging_type != '02')
      {
        $package_limits = $ups_packages[$ups_packaging_type]['limits'];
      }

      // if some limits are not specified, use limits for unknown packages
      
      $package_limits = Arr::merge($ups_packages['00']['limits'], $package_limits);

      $package_limits['girth'] = 165; // length + girth, inches
      $package_limits['price'] = 50000; // USD

      // fill items

      $items = array();

      while ($row = MySQL::fetch($rs_order_items))
      {
        if (!(Valid::hasValue($row['width']) && Valid::hasValue($row['height']) && Valid::hasValue($row['length'])))
        {
          $width = $default_width;
          $height = $default_height;
          $length = $default_length;
        }
        else
        {
          $width = $row['width'];
          $height = $row['height'];
          $length = $row['length'];
        }

        if (!Valid::hasValue($row['pounds']) && !Valid::hasValue($row['ounces']))
        {
          $weight = $default_pounds;

          if (Valid::hasValue($default_ounces))
          {
            $weight = $weight + $default_ounces * 0.062514;
          }
        }
        else
        {
          $weight = (double)$row['pounds'];

          if (Valid::hasValue($row['ounces']))
          {
            $weight = $weight + $row['ounces'] * 0.062514;
          }
        }

        $weight = sprintf('%.2f', $weight);

        $items[] = array(
                         'weight' => (double)$weight,
                         'amount' => (int)$row['quantity'],
                         'price' => (double)$row['price'],
                         'length' => (double)$length,
                         'width'  => (double)$width,
                         'height'  => (double)$height,
                         'separate_box' => $row['separate_box'] ? true : false,
                         'items_per_box' => $row['items_per_box']
                        );
      }

      MySQL::free($rs_order_items);

      $packages = Packages::func_get_packages($items, $package_limits, 200);

      $packages_xml = '';

      $total_weight = 0;

      if (!empty($packages) && is_array($packages)) 
      {
        foreach ($packages as $package) 
        {
          $pkgopt = array();

          $UPS_weight = max(0.1, $package['weight']);

          $total_weight += $UPS_weight;

          // Dimensions of a package

          $UPS_length = $package['length'];
          $UPS_width = $package['width'];
          $UPS_height = $package['height'];

          if ($UPS_length + $UPS_width + $UPS_height > 0)
          {
            // Insert the Dimensions section

            $dimensions_query = '<Dimensions>'.
                                '<UnitOfMeasurement>'.
                                '<Code>IN</Code>'.
                                '</UnitOfMeasurement>'.
                                '<Length>' . htmlspecialchars($UPS_length) . '</Length>'.
                                '<Width>' . htmlspecialchars($UPS_width) . '</Width>'.
                                '<Height>' . htmlspecialchars($UPS_height) . '</Height>'.
                                '</Dimensions>';

            $UPS_girth = $UPS_length + (2 * $UPS_width) + (2 * $UPS_height);

            if ($UPS_girth > 165) 
            {
              $dimensions_query .= '<LargePackageIndicator/>';
            }
          }
          else
          {
            $dimensions_query = null;
          }

          // Declared value

          $insvalue_xml = '';
          if (!empty($package['price'])) 
          {
            $insvalue = round(doubleval($package['price']), 2);
            if ($insvalue > 0.1) 
            {
              $pkgopt[] = '<InsuredValue>'.
                          '<CurrencyCode>' . htmlspecialchars($currency) . '</CurrencyCode>'.
                          '<MonetaryValue>' . htmlspecialchars($insvalue) . '</MonetaryValue>'.
                          '</InsuredValue>';
            }
          }

          // Delivery confirmation option

          $delivery_conf = intval($ups_delivery_confirmation);

          if ($delivery_conf > 0 && $delivery_conf < 4 && $origination_country == 'US' && $destination_country == 'US') 
          {
             $pkgopt[] = '<DeliveryConfirmation>'.
                         '<DCISType>' . htmlspecialchars($delivery_conf) . '</DCISType>'.
                         '</DeliveryConfirmation>';
          }

          $pkgparams = (count($pkgopt) > 0) ? '<PackageServiceOptions>' . join('', $pkgopt) . '</PackageServiceOptions>' : null;

          // Package description XML

          $package_xml = '<Package>'.
                         '<PackagingType>'.
                         '<Code>' . htmlspecialchars($ups_packaging_type) . '</Code>'.
                         '</PackagingType>'.
                         '<PackageWeight>'.
                         '<UnitOfMeasurement>'.
                         '<Code>LBS</Code>'.
                         '</UnitOfMeasurement>'.
                         '<Weight>' . htmlspecialchars($UPS_weight) . '</Weight>'.
                         '</PackageWeight>'.
                         $dimensions_query.
                         $pkgparams.
                         '</Package>';

          $packages_xml .= $package_xml;
        }
      }
      else
      {
        return;
      }

      $query = '<?xml version="1.0"?>'.
               '<AccessRequest xml:lang="en-US">'.
               '<AccessLicenseNumber>' . htmlspecialchars($ups_access_license_no) . '</AccessLicenseNumber>'.
               '<UserId>' . htmlspecialchars($ups_user_id) . '</UserId>'.
               '<Password>' . htmlspecialchars($ups_password) . '</Password>'.
               '</AccessRequest>'.
               '<?xml version="1.0"?>'.
               '<RatingServiceSelectionRequest xml:lang="en-US">'.
               '<Request>'.
               '<TransactionReference>'.
               '<CustomerContext>Rating and Service</CustomerContext>'.
               '<XpciVersion>1.0001</XpciVersion>'.
               '</TransactionReference>'.
               '<RequestAction>Rate</RequestAction>'.
               '<RequestOption>Shop</RequestOption>'.
               '</Request>'.
               '<PickupType>'.
               '<Code>' . htmlspecialchars($ups_pickup_type) .'</Code>'.
               '</PickupType>';

      if (Valid::hasValue($customer_classification))
      {
        $query .= '<CustomerClassification><Code>' . htmlspecialchars($customer_classification) . '</Code></CustomerClassification>';
      }

      $query .= '<Shipment>'.
                '<Shipper>';
     
      if (Valid::hasValue($ups_shipper_number))
      {
        $query .= '<ShipperNumber>' . htmlspecialchars($ups_shipper_number) . '</ShipperNumber>';
      }

      $query .= '<Address>'.
                '<City>' . htmlspecialchars($origination_city) . '</City>'.
                ($origination_state ? '<StateProvinceCode>' . htmlspecialchars($origination_state) . '</StateProvinceCode>' : null) .
                '<PostalCode>' . htmlspecialchars($origination_zip) . '</PostalCode>'.
                '<CountryCode>' . htmlspecialchars($origination_country) . '</CountryCode>'.
                '</Address>'.
                '</Shipper>'.
                '<ShipFrom>'.
                '<Address>'.
                '<City>' . htmlspecialchars($origination_city) . '</City>'.
                ($origination_state ? '<StateProvinceCode>' . htmlspecialchars($origination_state) . '</StateProvinceCode>' : null) .
                '<PostalCode>' . htmlspecialchars($origination_zip) . '</PostalCode>'.
                '<CountryCode>' . htmlspecialchars($origination_country) . '</CountryCode>'.
                '</Address>'.
                '</ShipFrom>'.
                '<ShipTo>'.
                '<Address>'.
                '<City>' . htmlspecialchars($destination_city) . '</City>'.
                ($destination_state ? '<StateProvinceCode>' . htmlspecialchars($destination_state) . '</StateProvinceCode>' : null) .
                '<PostalCode>' . htmlspecialchars($destination_zip) . '</PostalCode>'.
                '<CountryCode>'. htmlspecialchars($destination_country) .'</CountryCode>';

      if ($ups_destination_type == 'Y')
      {
        $query .= '<ResidentialAddressIndicator/>';
      }

      $query .= '</Address>'.
                '</ShipTo>';

      $query .= $packages_xml;

      if ($ups_saturday_pickup || $ups_saturday_delivery)
      {
        $query .= '<ShipmentServiceOptions> ' . ($ups_saturday_pickup ? '<SaturdayPickupIndicator/>' : null) . ($ups_saturday_delivery ? '<SaturdayDeliveryIndicator/>' : null) . '</ShipmentServiceOptions>';
      }

      if ($ups_negotiated_rates)
      {
        $query .= '<RateInformation><NegotiatedRatesIndicator/></RateInformation>';
      }

      $query .= '</Shipment>'.
                '</RatingServiceSelectionRequest>';

      $md5_request = md5($query);

      $result = null;

      if (self::isShippingResultInCache($md5_request))
      {
        $result = self::getShippingResultFromCache($md5_request);
      }

      if ($result)
      {
        return $result;
      }
                        
      $ch = curl_init();

      if ($ch === false)
      {
        throw new Exception('Error initializing connection to UPS rating server.');
      }

      curl_setopt($ch, CURLOPT_URL, $ups_endpoint_url);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_VERBOSE, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

      $response = curl_exec($ch);

      if ($response === false)
      {
        throw new Exception('Error establishing connection to UPS rating server.');
      }

      curl_close($ch);

      libxml_use_internal_errors(true);
      $doc = new DOMDocument('1.0', 'utf-8');
      $doc->recover = false;

      if ($doc->LoadXML($response, LIBXML_NOWARNING | LIBXML_NOERROR) === false)
      {
        throw new Exception('Error parsing UPS rate server output.');
      }

      $xpath = new DOMXPath($doc);

      $status_code = $xpath->query('/RatingServiceSelectionResponse/Response/ResponseStatusCode');
      $status_code = $status_code->item(0)->textContent;

      if ($status_code == '0')
      {
        $severity = $xpath->query('/RatingServiceSelectionResponse/Response/Error/ErrorSeverity');
        $severity = $severity->item(0)->textContent;

        $code = $xpath->query('/RatingServiceSelectionResponse/Response/Error/ErrorCode');
        $code = $code->item(0)->textContent;

        $description = $xpath->query('/RatingServiceSelectionResponse/Response/Error/ErrorDescription');
        $description = rtrim($description->item(0)->textContent, '.');

        throw new Exception($description . '. (Code: ' . $code . ')');
      }

      $ratedShipments = $xpath->query('/RatingServiceSelectionResponse/RatedShipment');

      $rates = array();

      $origin_code = self::get_country_code($origination_country);
      $dest_code = self::get_country_code($destination_country);

      $method_id = null;

      for ($i = 1; $i <= $ratedShipments->length; $i++)
      {
        $serviceCode = $xpath->query('/RatingServiceSelectionResponse/RatedShipment[' . $i . ']/Service/Code');
        $serviceCode = $serviceCode->item(0)->textContent;
        $serviceCode = $ups_services[$serviceCode][$origin_code];

        if ($serviceCode == '14' && $origin_code == 'US' && $dest_code == 'CA')
        {
          $method_id = 110; // UPS Standard to Canada
        }
        elseif ($serviceCode == '12') 
        {
          if ($dest_code == 'US' || $dest_code == 'PR')
           $method_id = $shipping_method['id']; // UPS Saver
          elseif ($origin_code == 'US' || $origin_code == 'PR')
           $method_id = 145; // UPS Worldwide Saver (SM)
          elseif (($origin_code == 'CA' && ($dest_code == 'US' || $dest_code == 'CA')) || ($origin_code == 'EU' && $dest_code == 'EU'))
           $method_id = 146; // UPS Express Saver (SM)
          else
           $method_id = 144; // UPS Worldwide Express Saver (SM)
        }
        else
        {
          $method_id = $shipping_methods_tbl->select('[service_code="' . $serviceCode . '"]', null);
          if (isset($method_id['id']))
          {
            $method_id = $method_id['id'];
          }
        }

        if (!$method_id)
        {
          continue;
        }

        $service = $shipping_methods_tbl->select('[id="' . $method_id . '"]', null);

        if ($service['active'])
        {
          if ($service['limit_low'] != 0 && $total_weight < $service['limit_low'])
          {
            continue;
          }

          if ($service['limit_high'] != 0 && $total_weight > $service['limit_high'])
          {
            continue;
          }

          $serviceName = $service['name'];

          $totalCharges = $xpath->query('/RatingServiceSelectionResponse/RatedShipment[' . $i . ']/TotalCharges');

          $currencyCode = $totalCharges->item(0)->getElementsByTagName('CurrencyCode')->item(0)->textContent;
          $totalCharges = $totalCharges->item(0)->getElementsByTagName('MonetaryValue')->item(0)->textContent;
                                                                                                            
          $guaranteedDaysToDelivery = $xpath->query('/RatingServiceSelectionResponse/RatedShipment[' . $i . ']/GuaranteedDaysToDelivery');
          $guaranteedDaysToDelivery = $guaranteedDaysToDelivery->item(0)->textContent;

          $rates[$serviceName] = array();
          $rates[$serviceName]['rate'] = $totalCharges;
          $rates[$serviceName]['currency'] = $currency_sign;
          $rates[$serviceName]['days_to_delivery'] = $guaranteedDaysToDelivery;
        }
      }

      self::saveShippingResultToCache($md5_request, $rates);

      return $rates;

    }
    catch(Exception $e)
    {
      throw new Exception($e->getMessage());
    }
  }

  private static function get_country_code($code)
  {
    $origin_code = '';

    $eu_members = array('AT', 'BE', 'BU', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'MC', 'NL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'GB');

    if (in_array($code, array('US','CA','PR','MX','PL'))) 
    {
      $origin_code = $code;

    } 
    elseif (in_array($code, $eu_members)) 
    {
      $origin_code = 'EU';
    } 
    else 
    {
      $origin_code = 'OTHER_ORIGINS';
    }

    return $origin_code;
  }

  private static function isShippingResultInCache($md5_str)
  {
    try
    {
      $query = 'SELECT COUNT(*) > 0 FROM shipping_cache WHERE md5_request = \'' . MySQL::escapeString($md5_str) . '\' AND session_id = \'' . MySQL::escapeString(md5(Session::getSessionId())) . '\'';
      $request_count = MySQL::selectCell($query);
      return $request_count > 0;
    }
    catch(Exception $e)
    {
      return false;
    }
  }

  private static function saveShippingResultToCache($md5_str, $result)
  {
    try
    {
      $query = 'INSERT INTO shipping_cache(md5_request, session_id, response) VALUES(\'' . MySQL::escapeString($md5_str) . '\', \'' . MySQL::escapeString(md5(Session::getSessionId())) . '\', \'' . MySQL::escapeString(serialize($result)) . '\')';
      MySQL::query($query);
    }
    catch(Exception $e)
    {
    }
  }

  private static function getShippingResultFromCache($md5_str)
  {
    try
    {
      $query = 'SELECT response FROM shipping_cache WHERE md5_request = \'' . MySQL::escapeString($md5_str) . '\' AND session_id = \'' . MySQL::escapeString(md5(Session::getSessionId())) . '\'';
      $response = MySQL::selectCell($query);
      return @unserialize($response);
    }
    catch(Exception $e)
    {
      return null;
    }
  }

}

define('MAX_ITEMS_FOR_PACKING', 2000);

class Packages
{
  protected function __construct() 
  {
  }

  public static function func_get_packages($items, $package_limits, $max_number_of_packs = 1000000)
  {
    $packages = self::func_get_packages_internal($items, $package_limits, $max_number_of_packs);
    return $packages;
  }

  private static function func_get_packages_internal($items, $package_limits, $max_number_of_packs)
  {
    if (!is_array($items))
    {
      return false;
    }

    $pending_items = $items;

    usort($pending_items, 'Packages::func_sort_items');

    $separate_boxes = self::func_prepare_separate_boxes($pending_items, $package_limits, $max_number_of_packs);

    $max_number_of_packs -= count($separate_boxes);
    if ($max_number_of_packs < 0) $separate_boxes = -1;

    if ($separate_boxes != -1 && !empty($pending_items)) 
    {
      $packages = self::func_pack_items($pending_items, $package_limits, $max_number_of_packs);
    }

    if ((isset($packages) && $packages == -1) || $separate_boxes == -1)
    {
      return -1;
    }

    // Save in the result packages array separate boxes
    $result_packages = $separate_boxes;

    // Save in the result packages array common packages boxes
    if (is_array($packages))
    {
      foreach ($packages as $package)
      {
        $result_packages[] = $package['box']; // Save only boxes
      }
    }

    // Free memory
    $packages = $separate_boxes = null;

    return $result_packages;
  }


  private static function func_prepare_separate_boxes(&$pending_items, $package_limits, $max_number_of_packs)
  {
    $separate_boxes = array();
    $_pending_items = array();
    $boxid = 0;
    $total_items_for_packing = 0;

    $item_is_placed = true;
    $stop_packing = false;

    while (!$stop_packing && ($item = array_pop($pending_items)) != null) 
    {
      $item['items_per_box'] = $item['items_per_box'] != 0 ? $item['items_per_box'] : 1;

      if (!isset($item['separate_box']) || $item['separate_box'] != '1') 
      {
        if ($total_items_for_packing < MAX_ITEMS_FOR_PACKING) 
        {
          $total_items_for_packing += $item['amount'];
          $pending_item = $item;
          $item['amount'] = max(0, $total_items_for_packing - MAX_ITEMS_FOR_PACKING);
          $pending_item['amount'] -= $item['amount'];
          $_pending_items[] = $pending_item;
          if ($item['amount'] <= 0)
          {
            continue;
          }
        }
        $item['items_per_box'] = 1;
      }

      // If packing routine should take in consideration product dimensions...

      if (!self::func_check_box_dimensions($item, $package_limits)) 
      {
        $_tmp_box = $item;
        $_tmp_box['length'] = $item['width'];
        $_tmp_box['width'] = $item['length'];

        if (!self::func_check_box_dimensions($_tmp_box, $package_limits)) 
        {
           $item_is_placed = false;
           break;
        }
      }

      $number_of_boxes = ceil($item['amount'] / $item['items_per_box']);

      for ($i = 0; $i < $number_of_boxes; $i++) 
      {
        $_item = $item;
        $_item['amount'] = min($item['items_per_box'], $item['amount']);
        $_item['weight'] *= $_item['amount'];

        if (!self::func_check_item_weight($_item['weight'], $package_limits)) 
        {
          $item_is_placed = false;
          break;
        }

        if (!self::func_check_item_price($_item['price'], $package_limits)) 
        {
          $item_is_placed = false;
          break;
        }

        $separate_boxes[$boxid++] = $_item;

        // check if the max number of packages is not exceeded
        if (count($separate_boxes) > $max_number_of_packs)
        {
          $stop_packing = true;
          $item_is_placed = false;
          break;
        }

        $item['amount'] -= $_item['amount'];
      }

      if (!$item_is_placed) break;
    }

    if (!$item_is_placed)
    {
      return -1;
    }

    // $_pending_items array is used for correction of numeric keys of $pending_items after unsetting some values
    $pending_items = array_reverse($_pending_items);

    return $separate_boxes;
  }

  /**
   * Generate packages depending on package limits and items weight/dimensions
   */
  private static function func_pack_items(&$pending_items, $package_limits, $max_number_of_packs)
  {
    $packages = array();

    $stop_packing = false;

    $current_item_number = 0;
    $package_level = 1;

    // Scan a pending items array until it's empty or $stop_packing flag occured
    while (!empty($pending_items) && !$stop_packing) 
    {
      // Get current package
      $current_package_id = 0;
      $current_package = self::func_get_current_package($packages, $current_package_id);

      // Get current item from pending items list
      $current_item = $pending_items[$current_item_number];

      // Always pack one item
      $current_item['amount'] = 1;

      $item_is_placed = false;

      // Check if item box weight do not exceeds package weight limit
      if (self::func_check_item_weight($current_item['weight'] + $current_package['box']['weight'], $package_limits) && self::func_check_item_price($current_item['price'] + $current_package['box']['price'], $package_limits)) 
      {
        // Try to place item box into the package according to package dimensions limits
        $box = self::func_place_item_by_dimensions($current_item, $current_package, $package_limits, $package_level);
        if ($box)
        {
          $item_is_placed = true;
        }
      }

      // If item has been placed successfully...
      if ($item_is_placed) 
      {
        // Add item box variant into the package
        $current_package = self::func_add_item_to_package($current_package, $box, $current_item, $package_level);
        // Add current item to the current package
        $packages[$current_package_id] = $current_package; // Save current package in the packages list
        // Update $pending_items
        self::func_update_pending_items_array($pending_items, $current_item_number);
      }
      // If item has not been placed...
      else 
      {
        // Go to next item in pending items list
        $current_item_number++;

        // If next item is not available...
        if (!isset($pending_items[$current_item_number])) 
        {
          $current_item_number = 0;
          // If current package level contains any items...
          if (!empty($current_package['level_'.$package_level]['items'])) 
          {
            $package_level++; // Go to next package level
          }
          // If entire package contains any items...
          elseif (!empty($current_package['level_1']['items'])) 
          {
            $duplicates = 0;
            while(self::func_check_duplicate_package($current_package, $pending_items)) 
            {
              $packages[] = $current_package;
              $duplicates++;
            }

            if ($duplicates>0) 
            {
              if(!empty($pending_items)) 
              {
                $packages[] = self::func_create_new_package(); // Add new package...
                $package_level = 1; // ...and try to fill it out with first level
              }
            } 
            else 
            {
              $packages[] = self::func_create_new_package(); // Add new package...
              $package_level = 1; // ...and try to fill it out with first level
            }

            // check if the max number of packages is not exceeded
            if (count($packages) > $max_number_of_packs) 
            {
              $stop_packing = true;
              break;
            }
          }
          // Stop packing if item could not be placed into package and package is empty
          else 
          {
            $stop_packing = true;
          }
        }
      }
    } // while

    // Return error code if packing has been stopped
    if ($stop_packing) 
    {
      $packages = null;
      return -1;
    }

    return $packages;
  }

  /**
   * Get the current package from packages array or generate a default package
   */
  private static function func_get_current_package($packages, &$current_package_id)
  {
    $current_package = array();

    // Get the current package - last package from the packages list
    $current_package_id = count($packages);
    if ($current_package_id > 0)
    {
      $current_package_id--;
    }

    // Initialize current package
    if (!empty($packages))
    {
      $current_package = $packages[$current_package_id];
    }
    else 
    {
      $current_package = self::func_create_new_package();
    }

    return $current_package;
  }

  /**
   * Create new package with default weight/dimensions
   */
  private static function func_create_new_package()
  {
    $package = array();

    $package['box'] = array(
        'weight' => 0,
        'length' => 0,
        'width'  => 0,
        'height' => 0,
        'price' => 0
    );

    $package['level_1']['box'] = $package['box'];
    $package['level_1']['items'] = array();

    return $package;
  }

  /**
   * Check if sum of item weight and current package weight exceeds the package weight limit
   */
  private static function func_check_item_weight($weight, $package_limits)
  {
    if (isset($package_limits['weight']) && $weight > $package_limits['weight'])
     return false;

    return true;
  }

  /**
   * Check if sum of item prices and current package price exceeds the package price limit
   */
  private static function func_check_item_price($price, $package_limits)
  {
    if (isset($package_limits['price']) && $price > $package_limits['price'])
     return false;

    return true;
  }

  /**
   * Check if current item could be placed into the current package according to the dimensions limit
   */
  private static function func_place_item_by_dimensions($current_item, $current_package, $package_limits, $package_level)
  {
    // Prepare all available configurations of the item box within package in one level
    // Note: it is supposed only length<->width replacement during packing items,
    // vertical rotation is not supposed
    $dim_keys = array('width'=>'length', 'length'=>'width');

    // Prepare current package level box for comparison
    if (isset($current_package['level_'.$package_level]['box']))
    {
      $current_level_box = $current_package['level_'.$package_level]['box'];
    }
    else 
    {
      $current_level_box = array();
      $current_level_box['price'] = $current_level_box['length'] = $current_level_box['width'] = $current_level_box['height'] = $current_level_box['weight'] = 0;
    }

    // Generate boxes list for each variant of placement item into the current package level
    $boxes = array();

    foreach ($dim_keys as $key_box)
    {
      foreach ($dim_keys as $key_item)
      {
        $_box = array();
        $_box[$key_box] = $current_level_box[$key_box] + $current_item[$key_item];
        $_box[$dim_keys[$key_box]] = max($current_level_box[$dim_keys[$key_box]], $current_item[$dim_keys[$key_item]]);
        $_box['height'] = max($current_level_box['height'], $current_item['height']);
        $_box['weight'] = $current_level_box['weight'] + $current_item['weight'];
        $_box['price'] = $current_level_box['price'] + $current_item['price'];

        $_current_package = self::func_add_item_to_package($current_package, $_box, $current_item, $package_level);

        // Check if package satisfies package limits after adding a current item box variant
        if (self::func_check_box_dimensions($_current_package['box'], $package_limits))
        {
          $boxes[] = $_box;
        }
      }
    }

    // If any available item box variant was found...
    if (!empty($boxes)) 
    {
      // Select box variant with minimal square (length/width)
      $box_id = 0;
      $box_square = 0;
      foreach ($boxes as $_box_id=>$_box) 
      {
        $_box_square = $_box['length'] * $_box['width'];
        if ($_box_square < $box_square)
        {
          $box_id = $_box_id;
        }
      }
      return $boxes[$box_id];
    }

    return false;
  }

  /**
   * Add item into the package
   */
  private static function func_add_item_to_package($package, $box, $item, $package_level)
  {
    // Update entire package box dimensions from selected item box variant
    if (isset($box['length']))
     $package['box']['length'] = max($package['box']['length'], $box['length']);
    if (isset($box['width']))
     $package['box']['width'] = max($package['box']['width'], $box['width']);

    // Update current level of package box weight/dimensions
    if (isset($package['level_'.$package_level]['box']))
     $package['level_'.$package_level]['box'] = array_merge($package['level_'.$package_level]['box'], $box);
    else
     $package['level_'.$package_level]['box'] = $box;

    // Add current item to the placed items list
    $package['level_'.$package_level]['items'][] = $item;

    // Update height and weight of the current package box
    self::func_update_package_box($package);

    return $package;
  }

  /**
   * Update package box weight and height
   */
  private static function func_update_package_box(&$package)
  {

    $box_height = 0;
    $box_weight = 0;
    $box_price = 0;
    $level = 1;
    $last_level = array();

    // Sum of an average heights of all box levels
    while (!empty($package['level_'.$level]['box'])) 
    {
      $last_level = $package['level_'.$level]['box'];
      $box_height += $last_level['height'];
      $box_weight += $last_level['weight'];
      $box_price += $last_level['price'];
      $level++;
    }

    $package['box']['height'] = $box_height;
    $package['box']['weight'] = $box_weight;
    $package['box']['price'] = $box_price;
  }

  /**
   * Update pending items list after removing specified item (which is placed into the package box)
   */
  private static function func_update_pending_items_array(&$pending_items, &$current_item_number)
  {
    // If specified item quantity more than one...
    if ($pending_items[$current_item_number]['amount'] > 1) 
    {
      $pending_items[$current_item_number]['amount']--; // Decrease quantity only
    }
    // If specified item quantity is one...
    else 
    {
      Arr::arrayUnset($pending_items, $current_item_number);

      // Update pending items array keys (reset integer keys value to 0,1,2,3...)
      $_pending_items = array();
      foreach ($pending_items as $_item)
       $_pending_items[] =$_item;
      $pending_items = $_pending_items;

      // Update current item number
      if ($current_item_number > 0)
       $current_item_number--;
    }
  }

  /**
   * Sort items by dimensional weight
   */
  private static function func_sort_items($pack1, $pack2)
  {
    $dim_weight1 = self::func_dim_weight($pack1);
    $dim_weight2 = self::func_dim_weight($pack2);

    return ($dim_weight1 > $dim_weight2 ? -1 : ($dim_weight1 < $dim_weight2 ? 1 : 0));
  }

  /**
   * Calculate girth of a box
   */
  private static function func_girth($box)
  {
    $girth = $box['length'] + $box['width'] * 2 + $box['height'] * 2;
    return $girth;
  }

  /**
   * Calculate dimensional weight of a box
   */
  private static function func_dim_weight($box, $usa_domestic = true)
  {
    $dim_weight = $box['length'] * $box['width'] * $box['height'];
    return round(($usa_domestic ? $dim_weight/194 : $dim_weight/166), 4);
  }

  /**
   * Check if box dimensions does not exceed package limit
   */
  private static function func_check_box_dimensions(&$box, $package_limits)
  {
    foreach ($package_limits as $key=>$value) 
    {
      if ($key == 'weight')
       continue;

      if ($key == 'girth' && !isset($box['girth']))
       $box['girth'] = self::func_girth($box);

      if ($key == 'dim_weight' && !isset($box['dim_weight']))
       $box['dim_weight'] = self::func_dim_weight($box);

      if (!isset($box[$key]))
       return false;

      if ($box[$key] > $value)
       return false;
    }
    return true;
  }

  /**
   * Check whether $pending_items array contains a duplication of $current_package
   * If so, remove the duplication from $pending_items and return true,
   */
  private static function func_check_duplicate_package(&$current_package, &$pending_items)
  {
    $dup_item = null;
    $dup_items_amount = 0;

    // Check whether all items in the current package are equal
    foreach ($current_package as $k=>$v) 
    {
      if (substr($k,0,6) == 'level_') 
      {
        foreach ($v['items'] as $item) 
        {
          $amount = $item['amount'];
          unset($item['amount']);
          if ($dup_item == null) 
          {
              $dup_item = $item;
          }
          elseif ($dup_item != $item) 
          {
              return false;
          }
          $dup_items_amount += $amount;
        }
      }
    }

    if ($dup_items_amount < 1) return false;

    // Check whether the head of $pending_items contains a duplication of $current_package

    $tmp_amount = $dup_items_amount;

    foreach ($pending_items as $item) 
    {
      foreach ($dup_item as $k=>$v) 
      {
        if ($v != $item[$k])
         return false;
      }

      $tmp_amount -= $item['amount'];

      if ($tmp_amount <= 0)
       break;
    }

    if ($tmp_amount > 0) return false;

    // Remove the duplicate package from the pending items

    foreach ($pending_items as $k=>$item) 
    {
      if ($dup_items_amount >= $item['amount']) 
      {
        $dup_items_amount -= $item['amount'];
        Arr::arrayUnset($pending_items, $k);
      }
      else 
      {
        $pending_items[$k]['amount'] -= $dup_items_amount;
        $dup_items_amount = 0;
      }
      if ($dup_items_amount <= 0)
       break;
    }

    // Re-index the pending items array
    $pending_items = array_values($pending_items);

    return true;
  }
}
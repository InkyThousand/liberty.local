<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<?php
Plugin::register( __FILE__,
              __('Taxes', 'taxes'),
              __('Shipping Taxes plugin', 'taxes'),
              '1.0.0',
              'razorolog',
              '',
              null,
              'store');

Plugin::Admin('taxes', 'store');

Javascript::add('plugins/store/taxes/js/' . Option::get('language') . '.taxes.js', 'backend');

Taxes::init();

class Taxes
{
  protected static $instance = null;
  private static $taxes = array();

  protected function __clone()
  {
  }

  function __construct()
  {
    $taxes_tbl = new Table('taxes');
    $taxes = $taxes_tbl->select();

    if ($taxes)
    {
      foreach ($taxes as $tax)
      {
        if (Valid::hasValue($tax['state']))
        {                                                               
          self::$taxes[$tax['country']][$tax['state']] = array('description' => $tax['display'], 'mode' => $tax['mode'], 'value' => $tax['value']);
        }
        else
        {
          self::$taxes[$tax['country']] = array('description' => $tax['description'], 'mode' => $tax['mode'], 'value' => $tax['value']);
        }
      }
    }
  }

  public static function init()
  {
    if (!isset(self::$instance))
     self::$instance = new Taxes();
    return self::$instance;
  }

  public static function getShippingTax($countryCode, $stateCode = null)
  {
    $result['description'] = null;
    $result['mode'] = 'total';
    $result['value'] = null;

    if (!empty(self::$taxes[$countryCode]))
    {
      if (Countries::hasStates($countryCode))
      {
        if (!empty(self::$taxes[$countryCode][$stateCode]))
        {
          $result = self::$taxes[$countryCode][$stateCode];
        }
      }
      else
      {
        $result = self::$taxes[$countryCode];
      }
    }

    return $result;
  }
}
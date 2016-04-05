<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<?php
Plugin::register(__FILE__,
                 __('Countries', 'countries'),
                 __('Countries and States plugin', 'countries'),
                 '1.0.0',
                 'razorolog',
                 '',
                 null,
                 'countries');

Plugin::Admin('countries', 'store');

Javascript::add('plugins/store/countries/js/' . Option::get('language') . '.countries.js', 'backend');

Countries::init();

class Countries 
{
    protected static $instance = null;
    private static $countries = array();
    private static $active_countries = array();
    private static $states = array();

    protected function __clone()
    {
    }

    function __construct()
    {
      $countries_tbl = new Table('countries');
      $states_tbl = new Table('states');

      $countries = $countries_tbl->select();
      if ($countries)
      {
        foreach ($countries as $country)
        {
          self::$countries[$country['code']] = $country['name'];

          if ($country['active'])
          {
            self::$active_countries[$country['code']] = $country['name'];
          }

          $states = $states_tbl->select('[country="'.$country['code'].'"]');

          if ($states)
          {
            foreach ($states as $state)
            {
              self::$states[$country['code']][$state['code']] = $state['name'];
            }
            asort(self::$states[$country['code']]);
          }
        }
      }
      asort(self::$countries);
      asort(self::$active_countries);
    }

    public static function init()
    {
      if (!isset(self::$instance))
       self::$instance = new Countries();
      return self::$instance;
    }

    public static function isCountryActive($countryCode)
    {
      $countryCode = (string)$countryCode;

      return empty(self::$active_countries[$countryCode]) ? false : true;
    }

    public static function validateCountryAndState($country, $state)
    {
      $country = (string)$country;
      $state = (string)$state;

      $country = self::validateCountry($country);

      if (Valid::hasValue($country))
      {
        if (Countries::hasStates($country))
        {
          $state = self::validateState($country, $state);
          return Valid::hasValue($state);
        }
        else
        {
          return true;
        }
      }
      else
      {
        return false;
      }
    }


    public static function validateCountry($country)
    {
      $country = (string)$country;
      $country = strtoupper($country);

      if (array_key_exists($country, self::$active_countries))
      {
        return ($country);
      }
      else
      {
        if ($key = array_search($country, Arr::changeValueCase(self::$active_countries)))
        {
          return $key;
        }
        else
        {
          return null;
        }
      }
    }

    public static function hasStates($countryCode)
    {
      $countryCode = (string)$countryCode;

      return !empty(self::$states[$countryCode]) ? (bool)count(self::$states[$countryCode]) : false;
    }

    public static function validateState($countryCode, $state)
    {
      $countryCode = (string)$countryCode;
      $state = (string)$state;

      $state = strtoupper($state);

      if (array_key_exists($state, self::$states[$countryCode]))
      {
        return $state;
      }
      else 
      {
        if ($key = array_search(strtoupper($state), Arr::changeValueCase(self::$states[$countryCode])))
        {
          return $key;
        }
        else
        {
          return null;
        }
      }
    }

    public static function getCountryName($code)
    {
      $code = (string)$code;
      
      return empty(self::$countries[$code]) ? null : self::$countries[$code];
    }

    public static function getStateName($countryCode, $stateCode)
    {
      $countryCode = (string)$countryCode;
      $stateCode = (string)$stateCode;

      return empty(self::$states[$countryCode][$stateCode]) ? null : self::$states[$countryCode][$stateCode];
    }

    public static function getCountries($active = true)
    {
      $active = (bool)$active;

      return $active ? self::$active_countries : self::$countries;
    }

    public static function getStates($countryCode)
    {
      $countryCode = (string)$countryCode;

      return empty(self::$states[$countryCode]) ? array() : self::$states[$countryCode];
    }
}
<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<?php
Navigation::add(__('Shipping', 'shipping'), 'store', 'shipping', 6);

class ShippingAdmin extends Backend 
{
  public static function main() 
  {
    if (Session::exists('user_role') && in_array(Session::get('user_role'), array('admin'))) 
    {
      $tab = strtolower(trim(Request::get('tab')));
      $carrier = strtolower(trim(Request::get('carrier')));

      switch ($tab)
      {
        case 'options':

             $shipping_options_tbl = new Table('shipping_options');

             if (Request::post('edit_settings'))
             {
               if (Security::check(Request::post('csrf'))) 
               {
                 $ups_pickup_type = Request::post('ups_pickup_type');

                 $ups_destination_type = Request::post('ups_destination_type');
                 $ups_packaging_type = Request::post('ups_packaging_type');

                 $ups_delivery_confirmation = Request::post('ups_delivery_confirmation');

                 $ups_additional_handling = Valid::hasValue(Request::post('ups_additional_handling'));
                 $ups_saturday_pickup = Valid::hasValue(Request::post('ups_saturday_pickup'));
                 $ups_saturday_delivery = Valid::hasValue(Request::post('ups_saturday_delivery'));

                 $shipping_options_tbl->update(1, array(
                                                        'ups_pickup_type' => $ups_pickup_type,
                                                        'ups_destination_type' => $ups_destination_type,
                                                        'ups_packaging_type' => $ups_packaging_type,
                                                        'ups_delivery_confirmation' => $ups_delivery_confirmation,
                                                        'ups_additional_handling' => $ups_additional_handling,
                                                        'ups_saturday_pickup' => $ups_saturday_pickup,
                                                        'ups_saturday_delivery' => $ups_saturday_delivery
                                                        ));

                 Notification::set('success', __('Your changes have been saved.', 'shipping'));
                 Request::redirect('index.php?id=shipping&tab=options&carrier=' . $carrier);
               }
               else
               {
                 die('csrf detected!');
               }
             }
             else
             {
               $shipping_options = $shipping_options_tbl->select(null, null);

               $ups_pickup_type = $shipping_options['ups_pickup_type'];

               $ups_destination_type = $shipping_options['ups_destination_type'];
               $ups_packaging_type = $shipping_options['ups_packaging_type'];

               $ups_delivery_confirmation = $shipping_options['ups_delivery_confirmation'];

               $ups_additional_handling = (bool)$shipping_options['ups_additional_handling'];
               $ups_saturday_pickup = (bool)$shipping_options['ups_saturday_pickup'];
               $ups_saturday_delivery = (bool)$shipping_options['ups_saturday_delivery'];
             }

             View::factory('store/shipping/views/backend/index')
                   ->assign('tab', $tab)
                   ->assign('carrier', $carrier)
                   ->assign('ups_pickup_type', $ups_pickup_type)
                   ->assign('ups_destination_type', $ups_destination_type)
                   ->assign('ups_packaging_type', $ups_packaging_type)
                   ->assign('ups_delivery_confirmation', $ups_delivery_confirmation)
                   ->assign('ups_additional_handling', $ups_additional_handling)
                   ->assign('ups_saturday_pickup', $ups_saturday_pickup)
                   ->assign('ups_saturday_delivery', $ups_saturday_delivery)
                   ->display();
             break;

        case 'methods':

             $shipping_methods_tbl = new Table('shipping_methods');

             if (Request::post('edit_settings'))
             {
               if (Security::check(Request::post('csrf'))) 
               {
                 $methods = Request::post('method');

                 foreach ($methods as $id => $params)
                 {
                   $shipping_methods_tbl->updateWhere('[id="' . $id . '"]', array('delivery' => $params['delivery'],
                                                                                  'limit_low' => $params['limit_low'],
                                                                                  'limit_high' => $params['limit_high'],
                                                                                  'active' => Valid::hasValue(@$params['active'])));
                 }

                 Notification::set('success', __('Your changes have been saved.', 'shipping'));

                 Request::redirect('index.php?id=shipping&tab=methods');
               }
               else
               {
                 die('csrf detected!');
               }
             }

             $ups_methods_list = $shipping_methods_tbl->select('[shipper="ups"]', 'all', null, null);

             $ups_methods = array();

             $count = 0;
             $ups_methods_active = 0;

             foreach ($ups_methods_list as $method)
             {
               $ups_methods[$count]['id'] = $method['id'];
               $ups_methods[$count]['shipper'] = $method['shipper'];
               $ups_methods[$count]['name'] = $method['name'];
               $ups_methods[$count]['delivery'] = $method['delivery'];
               $ups_methods[$count]['national'] = $method['national'];
               $ups_methods[$count]['limit_low'] = $method['limit_low'];
               $ups_methods[$count]['limit_high'] = $method['limit_high'];
               $ups_methods[$count]['active'] = $method['active'];
               $ups_methods_active += $method['active'];
               $count++;
             }

             // $ups_methods = Arr::subvalSort($ups_methods, 'id');

             View::factory('store/shipping/views/backend/index')
                   ->assign('tab', $tab)
                   ->assign('carrier', $carrier)
                   ->assign('ups_shipping_methods', $ups_methods)
                   ->assign('ups_methods_active', $ups_methods_active)
                   ->display();

             break;

         case 'credentials':

             $shipping_credentials_tbl = new Table('shipping_credentials');

             if (Request::post('edit_settings')) 
             {
               if (Security::check(Request::post('csrf'))) 
               {
                 $ups_endpoint_url =  Request::post('ups_endpoint_url');
                 $ups_access_license_no = Request::post('ups_access_license_no');
                 $ups_user_id = Request::post('ups_user_id');
                 $ups_password = Request::post('ups_password');

                 $shipping_credentials_tbl->update(1, array('ups_endpoint_url' => $ups_endpoint_url,
                                                            'ups_access_license_no' => $ups_access_license_no,
                                                            'ups_user_id' => $ups_user_id,
                                                            'ups_password' => $ups_password)
                                                           );

                 Notification::set('success', __('Your changes have been saved.', 'shipping'));

                 Request::redirect('index.php?id=shipping&tab=credentials');
               }
               else
               {
                 die('csrf detected!');
               }
             }

             $shipping_credentials = $shipping_credentials_tbl->select(null, null);

             $ups_endpoint_url = $shipping_credentials['ups_endpoint_url'];
             $ups_access_license_no = $shipping_credentials['ups_access_license_no'];
             $ups_user_id = $shipping_credentials['ups_user_id'];
             $ups_password = $shipping_credentials['ups_password'];

             View::factory('store/shipping/views/backend/index')
                   ->assign('tab', $tab)
                   ->assign('carrier', $carrier)
                   ->assign('ups_endpoint_url', $ups_endpoint_url)
                   ->assign('ups_access_license_no', $ups_access_license_no)
                   ->assign('ups_user_id', $ups_user_id)
                   ->assign('ups_password', $ups_password)
                   ->display();
             break;

         default:

               $tab = 'settings';

               $shipping_tbl = new Table('shipping');

               $countries = Countries::getCountries(false);
               $states = array();

               $errors = array();

               if (Request::post('edit_settings')) 
               {
                 if (Security::check(Request::post('csrf'))) 
                 {
                   $shipping_pickup = Valid::hasValue(Request::post('shipping_pickup'));
                   $shipping_usps = Valid::hasValue(Request::post('shipping_usps'));
                   $shipping_ups = Valid::hasValue(Request::post('shipping_ups'));
                   $shipping_fedex = Valid::hasValue(Request::post('shipping_fedex'));
                   $shipping_other = Valid::hasValue(Request::post('shipping_other'));
                   $shipping_call = Valid::hasValue(Request::post('shipping_call'));

                   $shipping_ups = 1;

                   $origination_city = Request::post('origination_city');
                   $origination_zip = Request::post('origination_zip');
                   $origination_country = Request::post('origination_country');
                   $origination_state = Request::post('origination_state');

                   if (!Valid::hasValue($origination_country)) $errors['origination_country_error'] = __('Country is not selected', 'shipping');

                   if (!isset($errors['origination_country_error']))
                   {
                     $states = Countries::getStates($origination_country);
                     if (count($states))
                     {
                       if (!Valid::hasValue($origination_state))
                       {
                         $errors['origination_state_error'] = __('State is not selected', 'shipping');
                       }
                     }
                     else
                     {
                       $origination_state = null;
                     }
                   }

                   $default_width = Request::post('default_width');
                   $default_height = Request::post('default_height');
                   $default_length = Request::post('default_length');

                   $default_pounds = Request::post('default_pounds');
                   $default_ounces = Request::post('default_ounces');
                   
                   $handling_value = Request::post('handling_value');

                   if (count($errors) == 0)
                   {
                     $shipping_tbl->update(1, array('shipping_pickup' => $shipping_pickup,
                                                    'shipping_usps' => $shipping_usps,
                                                    'shipping_ups' => $shipping_ups,
                                                    'shipping_fedex' => $shipping_fedex,
                                                    'shipping_other' => $shipping_other,
                                                    'shipping_call' => $shipping_call,
                                                    'shipping_origination_city' => $origination_city,
                                                    'shipping_origination_zip' => $origination_zip,
                                                    'shipping_origination_country' => $origination_country,
                                                    'shipping_origination_state' => $origination_state,
                                                    'shipping_default_width' => $default_width,
                                                    'shipping_default_height' => $default_height,
                                                    'shipping_default_length' => $default_length,
                                                    'shipping_default_pounds' => $default_pounds,
                                                    'shipping_default_ounces' => $default_ounces,
                                                    'shipping_handling_value' => $handling_value
                                                   ));

                     Notification::set('success', __('Your changes have been saved.', 'checkout'));

                     Request::redirect('index.php?id=shipping&tab=settings');
                   }
                 }
                 else
                 {
                   die('csrf detected!');
                 }
               }
               else
               {
                 $shipping_settings = $shipping_tbl->select(null, null);

                 $shipping_pickup = (bool)$shipping_settings['shipping_pickup'];
                 $shipping_usps = (bool)$shipping_settings['shipping_usps'];
                 $shipping_ups = (bool)$shipping_settings['shipping_ups'];
                 $shipping_fedex = (bool)$shipping_settings['shipping_fedex'];
                 $shipping_other = (bool)$shipping_settings['shipping_other'];
                 $shipping_call = (bool)$shipping_settings['shipping_call'];

                 $origination_city = $shipping_settings['shipping_origination_city'];
                 $origination_zip = $shipping_settings['shipping_origination_zip'];
                 $origination_country = $shipping_settings['shipping_origination_country'];
                 $origination_state = $shipping_settings['shipping_origination_state'];

                 $default_width = $shipping_settings['shipping_default_width'];
                 $default_height = $shipping_settings['shipping_default_height'];
                 $default_length = $shipping_settings['shipping_default_length'];

                 $default_pounds = $shipping_settings['shipping_default_pounds'];
                 $default_ounces = $shipping_settings['shipping_default_ounces'];

                 $handling_value = $shipping_settings['shipping_handling_value'];

                 $states = Countries::getStates($origination_country);
               }

               View::factory('store/shipping/views/backend/index')
                     ->assign('tab', $tab)
                     ->assign('carrier', $carrier)
                     ->assign('shipping_pickup', $shipping_pickup)
                     ->assign('shipping_usps', $shipping_usps)
                     ->assign('shipping_ups', $shipping_ups)
                     ->assign('shipping_fedex', $shipping_fedex)
                     ->assign('shipping_other', $shipping_other)
                     ->assign('shipping_call', $shipping_call)
                     ->assign('origination_city', $origination_city)
                     ->assign('origination_zip', $origination_zip)
                     ->assign('origination_country', $origination_country)
                     ->assign('origination_state', $origination_state)
                     ->assign('default_width', $default_width)
                     ->assign('default_height', $default_height)
                     ->assign('default_length', $default_length)
                     ->assign('default_pounds', $default_pounds)
                     ->assign('default_ounces', $default_ounces)
                     ->assign('handling_value', $handling_value)
                     ->assign('countries', $countries)
                     ->assign('states', $states)
                     ->assign('errors', $errors)
                     ->display();
              break;
      }
    } 
    else 
    {
      Request::redirect('index.php?id=users&action=edit&user_id='.Session::get('user_id'));
    }
  }
}
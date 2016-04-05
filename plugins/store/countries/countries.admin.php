<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<?php
Navigation::add(__('Countries and States', 'countries'), 'store', 'countries', 5);
Action::add('admin_pre_render','CountriesAdmin::getStates');

class CountriesAdmin extends Backend
{
  public static function getStates()
  {
    if (Request::request('action') == 'get_states')
    {
      $states_tbl = new Table('states');
      $states_list = $states_tbl->select('[country="'.strtoupper(Request::request('country')).'"]');
      
      $states = array();

      if ($states_list)
      {
        foreach ($states_list as $state)
        {
          $states[] = array('v' => $state['code'], 't' => $state['name']);
        }
      }
      $first_item[] = array('v' => '', 't' => 'Choose...');
      $states = array_merge($first_item, $states);
      echo json_encode($states);
      Request::shutdown();
    }
  }

  public static function main()
  {
    if (Session::exists('user_role') && in_array(Session::get('user_role'), array('admin')))
    {
      $countries_tbl = new Table('countries');

      $regions[] = array('region' => 'all', 'title' => 'All regions');
      $regions[] = array('region' => 'na', 'title' => 'North America');
      $regions[] = array('region' => 'eu', 'title' => 'Europe');
      $regions[] = array('region' => 'au', 'title' => 'Australia and Oceania');
      $regions[] = array('region' => 'la', 'title' => 'Latin America');
      $regions[] = array('region' => 'as', 'title' => 'Asia');
      $regions[] = array('region' => 'af', 'title' => 'Africa');
      $regions[] = array('region' => 'an', 'title' => 'Antarctica');

      if (Valid::hasValue(Request::get('region')))
      {
        $region = strtolower(Request::get('region'));

        $region_valid = false;

        foreach ($regions as $r)
        {
          if ($r['region'] == $region)
          {
            $region_valid = true;
            break;
          }
        }

        $region = $region_valid ? $region : 'all';
      }
      else
      {
        $shipping_tbl = new Table('shipping');
        $shipping_settings = $shipping_tbl->select(null, null);
        $region = $countries_tbl->select('[code="' . $shipping_settings['shipping_origination_country'] . '"]', null, 'all');
        $region = $region['region'];
      }

      $region = strtolower($region);
      $country = strtolower(Request::get('country'));

      if (Request::post('cancel'))
      {
        Request::redirect('index.php?id=countries&region='.urlencode($region).(Valid::hasValue($country) ? '&country='.urlencode($country) : null));
      }

      $states_tbl = new Table('states');

      $display = Valid::hasValue($country) ? 'states' : 'countries';

      if (Request::get('action')) 
      {
        $errors = array();

        switch (Request::get('action'))
        {
          case 'update_countries':

           if (Valid::HasValue(Request::post('update_countries')))
           {
             if (Security::check(Request::post('csrf')))
             {
               $countries_data = Request::post('country_data');

               foreach ($countries_data as $country_data)
               {
                 $countries_tbl->updateWhere('[id="' . $country_data['id'] . '"]', array('active' => isset($country_data['active'])));
               }
               
               Notification::set('success', __('Your changes have been saved.', 'countries'));
             }
             else
             {
               die('csrf detected!');
             }
           }

           Request::redirect('index.php?id=countries&region=' . urlencode($region));

           break;

          case 'add_state':

           if (Request::post('add_state') || Request::post('add_state_and_exit'))
           {
             if (Security::check(Request::post('csrf')))
             {
               $state_code = trim(Request::post('state_code'));
               $state_name = trim(Request::post('state_name'));

               if (!Valid::hasValue($state_code)) $errors['code_error'] = __('Required field', 'countries');
               if (!Valid::hasValue($state_name)) $errors['name_error'] = __('Required field', 'countries');

               if (!isset($errors['code_error']))
               {
                 $states = $states_tbl->select('[country="'.strtoupper($country).'" and code="'.$state_code.'"]');

                 if (count($states))
                 {
                   $errors['code_error'] = __('Specified code already exists', 'countries');
                 }
               }
               
               if (count($errors) == 0) 
               {
                 $states_tbl->insert(array('country' => strtoupper($country), 'code' => $state_code, 'name' => $state_name));

                 $state_id = $states_tbl->lastId();

                 Notification::set('success', __('State <i>:state</i> have been added successfully.', 'countries', array(':state' => Html::toText($state_name))));

                 if (Request::post('edit_state_and_exit'))
                 {
                   Request::redirect('index.php?id=countries&region='.urlencode($region).'&country='.urlencode($country));
                 } 
                 else 
                 {
                   Request::redirect('index.php?id=countries&action=edit_state&region='.urlencode($region).'&country='.urlencode($country).'&state_id=' . urlencode($state_id));
                 } 
               }
             }
             else
             {
                die('csrf detected!');
             }
           }
           else
           {
             $state_code = null;
             $state_name = null;
           }

           View::factory('store/countries/views/backend/add_state')
                 ->assign('state_code', $state_code)
                 ->assign('state_name', $state_name)
                 ->assign('errors', $errors)
                 ->display();

           break;

          case 'edit_state':

           $state_id = Request::get('state_id');
                      
           if (Request::post('edit_state') || Request::post('edit_state_and_exit'))
           {
             if (Security::check(Request::post('csrf')))
             {
               $state_code = trim(Request::post('state_code'));
               $state_name = trim(Request::post('state_name'));

               if ($state_code == '') $errors['code_error'] = __('Required field', 'countries');
               if ($state_name == '') $errors['name_error'] = __('Required field', 'countries');
               
               if (count($errors) == 0) 
               {
                 $states_tbl->updateWhere('[id="' . $state_id . '"]', array('code' => $state_code,
                                                                            'name' => $state_name));

                 Notification::set('success', __('Your changes to the state <i>:state</i> have been saved.', 'countries', array(':state' => Html::toText($state_name))));

                 if (Request::post('edit_state_and_exit'))
                 {
                   Request::redirect('index.php?id=countries&region='.urlencode($region).'&country='.urlencode($country));
                 } 
                 else 
                 {
                   Request::redirect('index.php?id=countries&action=edit_state&region='.urlencode($region).'&country='.urlencode($country).'&state_id=' . urlencode($state_id));
                 } 
               }
             }
             else
             {
                die('csrf detected!');
             }
           }
           else
           {
             $state = $states_tbl->select('[id="' . $state_id . '"]', null);

             if ($state)
             {
               $state_code = $state['code'];
               $state_name = $state['name'];
             }
             else
             {
               $state_code = null;
               $state_name = null;
               $errors['page_error'] = __('State was not found.', 'countries');
             }
           }

           View::factory('store/countries/views/backend/edit_state')
                 ->assign('state_code', $state_code)
                 ->assign('state_name', $state_name)
                 ->assign('errors', $errors)
                 ->display();

           break;

          case 'delete_state':

           if (Security::check(Request::get('token')))
           {
             $state = $states_tbl->select('[id="' . Request::get('state_id') . '"]', null);

             if ($state)
             {
               $states_tbl->delete(Request::get('state_id'));
               Notification::set('success', __('State <i>:state</i> have been deleted.', 'states', array(':state' => $state['name'])));
             }
             else
             {
               Notification::set('error', __('State was not found.', 'states'));
             }

             Request::redirect('index.php?id=countries&region='.urlencode($region).'&country='.urlencode($country));
           }
           else
           {
             die('csrf detected!');
           }

           break;

          case 'edit_country':

           $country_id = Request::get('country_id');
                      
           if (Request::post('edit_country') || Request::post('edit_country_and_exit'))
           {
             if (Security::check(Request::post('csrf')))
             {
               $country_code = trim(Request::post('country_code'));
               $country_name = trim(Request::post('country_name'));
               $country_active = Valid::hasValue(Request::post('country_active'));

               if (!Valid::hasValue($country_code)) $errors['code_error'] = __('Required field', 'users');
               if (!Valid::hasValue($country_name)) $errors['name_error'] = __('Required field', 'users');

               if (!isset($errors['code_error']))
               {
                 $countries = $countries_tbl->select('[id!="'.$country_id.'" and code="'.$country_code.'"]');
                 if (count($countries))
                 {
                   $errors['code_error'] = __('Specified code already exists', 'countries');
                 }
               }

               if (!isset($errors['name_error']))
               {
                 $countries = $countries_tbl->select('[id!="'.$country_id.'" and name="'.$country_name.'"]');
                 if (count($countries))
                 {
                   $errors['name_error'] = __('Specified name already exists', 'countries');
                 }
               }
               
               if (count($errors) == 0) 
               {
                 $countries_tbl->updateWhere('[id="' . $country_id . '"]', array('code' => $country_code,
                                                                                 'name' => $country_name,
                                                                                 'active' => $country_active));

                 Notification::set('success', __('Your changes to the country <i>:country</i> have been saved.', 'countries', array(':country' => Html::toText($country_name))));

                 if (Request::post('edit_country_and_exit'))
                 {
                   Request::redirect('index.php?id=countries&region=' . urlencode($region));
                 } 
                 else 
                 {
                   Request::redirect('index.php?id=countries&action=edit_country&region=' . urlencode($region) . '&country_id=' . urlencode($country_id));
                 } 
               }
             }
             else
             {
                die('csrf detected!');
             }
           }
           else
           {
             $country_data = $countries_tbl->select('[id="' . $country_id . '"]', null);

             if ($country_data)
             {
                $country_code = $country_data['code'];
                $country_name = $country_data['name'];
                $country_active = (bool)$country_data['active'];
             }
             else
             {
               $country_code = null;
               $country_name = null;
               $country_active = null;
               $errors['page_error'] = __('Country was not found.', 'countries');
             }
           }

           View::factory('store/countries/views/backend/edit_country')
                 ->assign('country_code', $country_code)
                 ->assign('country_name', $country_name)
                 ->assign('country_active', $country_active)
                 ->assign('errors', $errors)
                 ->display();

           break;
        
          default:

           Request::redirect('index.php?id=countries');
        }
      }
      else
      {
        switch ($display)
        {
          case 'states':

           $country_data = $countries_tbl->select('[code="' . strtoupper($country) . '"]', null);

           $states = array();

           if ($country_data)
           {
             $states_list = $states_tbl->select('[country="' . strtoupper($country) . '"]');

             if ($states_list)
             {
               $count = 0;

               $states_array = array();

               foreach ($states_list as $state)
               {
                 $states_array[$count]['id'] = $state['id'];
                 $states_array[$count]['code'] = $state['code'];
                 $states_array[$count]['name'] = $state['name'];
                 $count++;
               }

               $states = Arr::subvalSort($states_array, 'code');
             }

             $error = null;
           }
           else
           {
             $country_data = null;
             $error = 'Country does not exists';
           }

           View::factory('store/countries/views/backend/states')
                 ->assign('country_name', $country_data['name'])
                 ->assign('country_code', $country_data['code'])
                 ->assign('region', $region)
                 ->assign('states', $states)
                 ->assign('error', $error)
                 ->display();
           break;

          default:

           $countries_list = $countries_tbl->select($region != 'all' ? '[region="' . strtoupper($region) . '"]' : null);

           $countries = array();

           if ($countries_list)
           {
             $count = 0;

             $countries_array = array();

             foreach ($countries_list as $country_data)
             {
               $countries_array[$count]['id'] = $country_data['id'];
               $countries_array[$count]['code'] = $country_data['code'];
               $countries_array[$count]['name'] = $country_data['name'];
               $countries_array[$count]['active'] = (bool)$country_data['active'];
               $count++;
             }

             $countries = Arr::subvalSort($countries_array, 'name');
           }

           View::factory('store/countries/views/backend/countries')
                 ->assign('regions', $regions)
                 ->assign('region', $region)
                 ->assign('countries', $countries)
                 ->display();
        }
      }
    }    
    else 
    {
      Request::redirect('index.php?id=users&action=edit&user_id='.Session::get('user_id'));
    }
  }
}
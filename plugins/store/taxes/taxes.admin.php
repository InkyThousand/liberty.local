<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<?php
Navigation::add(__('Taxes', 'taxes'), 'store', 'taxes', 4);

class TaxesAdmin extends Backend 
{
  public static function main() 
  {
    if (Session::exists('user_role') && in_array(Session::get('user_role'), array('admin')))
    {
      if (Request::post('cancel')) 
      {
        Request::redirect('index.php?id=taxes');
      }

      $taxes_tbl = new Table('taxes');

      if (Request::get('action'))
      {
        $errors = array();
        $countries = Countries::getCountries(false);
        $states = array();

        switch (Request::get('action')) 
        {
          case 'add_tax':

           if (Request::post('add_tax') || Request::post('add_tax_and_exit'))
           {
             if (Security::check(Request::post('csrf')))
             {
               $tax_description = Request::post('tax_description');
               $tax_display = Request::post('tax_display');
               $tax_country = Request::post('tax_country');
               $tax_state = Request::post('tax_state');
               $tax_mode = Request::post('tax_mode');
               $tax_value = Request::post('tax_value');

               if (!Valid::hasValue($tax_description)) $errors['description_error'] = __('This field should not be empty', 'taxes');
               if (!Valid::hasValue($tax_display)) $errors['display_error'] = __('This field should not be empty', 'taxes');
               if (!Valid::hasValue($tax_country)) $errors['country_error'] = __('Country is not selected', 'taxes');

               if (!isset($errors['country_error']))
               {
                 $states = Countries::getStates($tax_country);

                 if (count($states))
                 {
                   if (Valid::hasValue($tax_state))
                   {
                     $taxes = $taxes_tbl->select('[country="'. $tax_country .'" and state="' . $tax_state . '"]');
                     if ($taxes) $errors['state_error'] = __('Tax for specified state already exists', 'taxes');
                   }
                   else
                   {
                     $errors['state_error'] = __('State is not selected', 'taxes');
                   }
                 }
                 else
                 {
                   $taxes = $taxes_tbl->select('[country="'. $tax_country .'"]');
                   if ($taxes) $errors['country_error'] = __('Tax for specified country already exists', 'taxes');
                 }
               }

               if (!Valid::hasValue($tax_mode)) $errors['mode_error'] = __('Please select value', 'taxes');
               if (!Valid::hasValue($tax_value)) $errors['value_error'] = __('This field should not be empty', 'taxes');

               if (count($errors) == 0)
               {
                 $taxes_tbl->insert(array('description' => $tax_description,
                                          'display' => $tax_display,
                                          'country' => $tax_country,
                                          'state' => $tax_state,
                                          'mode' => $tax_mode,
                                          'value' => $tax_value
                                         ));

                 $tax_id = $taxes_tbl->lastId();

                 Notification::set('success', __('Tax <i>:tax_description</i> have been added successfully.', 'taxes', array(':tax_description' => Html::toText($tax_description))));

                 if (Request::post('add_tax_and_exit')) 
                 {
                   Request::redirect('index.php?id=taxes');
                 } 
                 else 
                 {
                   Request::redirect('index.php?id=taxes&action=edit_tax&tax_id='.urlencode($tax_id), true); 
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
             $tax_description = null;
             $tax_display = null;
             $tax_country = null;
             $tax_state = null;
             $tax_mode = null;
             $tax_value = null;
           }

           View::factory('store/taxes/views/backend/add')
                 ->assign('tax_description', $tax_description)
                 ->assign('tax_display', $tax_display)
                 ->assign('tax_country', $tax_country)
                 ->assign('tax_state', $tax_state)
                 ->assign('tax_mode', $tax_mode)
                 ->assign('tax_value', $tax_value)
                 ->assign('countries', $countries)
                 ->assign('states', $states)
                 ->assign('errors', $errors)
                 ->display();
                
           break;
             
          case 'edit_tax':

           $tax_id = Request::get('tax_id');

           $countries = Countries::getCountries(false);

           $states = array();

           if (Request::post('edit_tax') || Request::post('edit_tax_and_exit')) 
           {
             if (Security::check(Request::post('csrf'))) 
             {
               $tax_description = Request::post('tax_description');
               $tax_display = Request::post('tax_display');
               $tax_country = Request::post('tax_country');
               $tax_state = Request::post('tax_state');
               $tax_mode = Request::post('tax_mode');
               $tax_value = Request::post('tax_value');

               if (!Valid::hasValue($tax_description)) $errors['description_error'] = __('This field should not be empty', 'taxes');
               if (!Valid::hasValue($tax_display)) $errors['display_error'] = __('This field should not be empty', 'taxes');
               if (!Valid::hasValue($tax_country)) $errors['country_error'] = __('Country is not selected', 'taxes');

               if (!isset($errors['country_error']))
               {
                 $states = Countries::getStates($tax_country);

                 if (count($states))
                 {
                   if (Valid::hasValue($tax_state))
                   {
                     $taxes = $taxes_tbl->select('[id!="' . $tax_id . '" and country="'. $tax_country .'" and state="' . $tax_state . '"]');
                     if ($taxes) $errors['state_error'] = __('Tax for specified state already exists', 'taxes');
                   }
                   else
                   {
                     $errors['state_error'] = __('State is not selected', 'taxes');
                   }
                 }
                 else
                 {
                   $taxes = $taxes_tbl->select('[id!="' . $tax_id . '" and country="'. $tax_country .'"]');
                   if ($taxes) $errors['country_error'] = __('Tax for specified country already exists', 'taxes');
                 }
               }

               if (!Valid::hasValue($tax_mode)) $errors['mode_error'] = __('Please select value', 'taxes');
               if (!Valid::hasValue($tax_value)) $errors['value_error'] = __('This field should not be empty', 'taxes');

               if (count($errors) == 0) 
               {
                 $taxes_tbl->updateWhere('[id="' . $tax_id . '"]', array('description' => $tax_description,
                                                                         'display' => $tax_display,
                                                                         'country' => $tax_country,
                                                                         'state' => $tax_state,
                                                                         'mode' => $tax_mode,
                                                                         'value' => $tax_value));

                 Notification::set('success', __('Your changes to the tax <i>:tax_description</i> have been saved.', 'taxes', array(':tax_description' => Html::toText($tax_description))));

                 if (Request::post('edit_tax_and_exit')) 
                 {
                   Request::redirect('index.php?id=taxes');
                 } 
                 else 
                 {
                   Request::redirect('index.php?id=taxes&action=edit_tax&tax_id='.urlencode($tax_id), true); 
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
             $tax = $taxes_tbl->select('[id="' . $tax_id . '"]', null);

             if ($tax)
             {
               $tax_description = $tax['description'];
               $tax_display = $tax['display'];
               $tax_country = $tax['country'];
               $tax_state = $tax['state'];
               $tax_mode = $tax['mode'];
               $tax_value = $tax['value'];

               $states = Countries::getStates($tax_country);
             }
             else
             {
               $tax_description = null;
               $tax_display = null;
               $tax_country = null;
               $tax_state = null;
               $tax_mode = null;
               $tax_value = null;

               $errors['page_error'] = __('Record was not found.', 'taxes');
             }
           }


           View::factory('store/taxes/views/backend/edit')
                 ->assign('tax_description', $tax_description)
                 ->assign('tax_display', $tax_display)
                 ->assign('tax_country', $tax_country)
                 ->assign('tax_state', $tax_state)
                 ->assign('tax_mode', $tax_mode)
                 ->assign('tax_value', $tax_value)
                 ->assign('countries', $countries)
                 ->assign('states', $states)
                 ->assign('errors', $errors)
                 ->display();

           break;

          case 'delete_tax':

           $tax_id = Request::get('tax_id');

           if (Security::check(Request::get('token')))
           {
             $tax = $taxes_tbl->select('[id="'.$tax_id.'"]', null);

             if ($tax)
             {
               $tax_description = $tax['description'];

               if ($taxes_tbl->deleteWhere('[id="'.$tax_id.'" ]'))
               {
                 Notification::set('success', __('Tax <i>:tax_description</i> was deleted', 'taxes', array(':tax_description' => Html::toText($tax_description))));
               }
               else
               {
                 Notification::set('error', __('Could not delete record.', 'taxes'));
               }
             }
             else
             {
               Notification::set('error', __('Record was not found.', 'taxes'));
             }

             Request::redirect('index.php?id=taxes');
           }
           else
           {
             die('csrf detected!');
           }

           break;

          default:

           Request::redirect('index.php?id=taxes');
        }
      }
      else
      {
        $taxes_list = $taxes_tbl->select();

        $taxes = array();

        if ($taxes_list)
        {
          $taxes_array = array();
          $count = 0;
          foreach ($taxes_list as $tax) 
          {
            $taxes_array[$count]['id'] = $tax['id'];
            $taxes_array[$count]['description'] = $tax['description'];
            $taxes_array[$count]['display'] = $tax['display'];
            $taxes_array[$count]['country'] = Countries::getCountryName($tax['country']);
            $taxes_array[$count]['state'] = Countries::getStateName($tax['country'], $tax['state']);
            $taxes_array[$count]['mode'] = $tax['mode'] == 'total' ? 'Items and Freight' : 'Items only';
            $taxes_array[$count]['value'] = $tax['value'];
            $count++;
          }
          $taxes = Arr::subvalSort($taxes_array, 'description');
        }

        View::factory('store/taxes/views/backend/index')
              ->assign('taxes', $taxes)
              ->display();
      }
    }    
    else 
    {
      Request::redirect('index.php?id=users&action=edit&user_id='.Session::get('user_id'));
    }
  }
}
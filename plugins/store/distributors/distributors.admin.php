<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<?php
Navigation::add(__('Distributors', 'distributors'), 'store', 'distributors', 3);

class DistributorsAdmin extends Backend 
{
  public static function main() 
  {
    if (Session::exists('user_role') && in_array(Session::get('user_role'), array('admin')))
    {
      if (Request::post('cancel'))
      {
        Request::redirect('index.php?id=distributors');
      }

      $errors = array();

      if (Request::get('action')) 
      {
         switch (Request::get('action')) 
         {
            case 'import':

             $field_errors = array();
             $page_error = null;

             if (Request::post('import_data'))
             {
               if (Security::check(Request::post('csrf'))) 
               {
                 $file = @$_FILES['distributors_data'];

                 if (!$file || $file['size'] == 0 || $file['error'] == 4)
                 {
                    $field_errors['data_error'] = __('Please specify file with data to be imported.', 'distributors');
                 }
                 else
                 {
                   try
                   {
                     $filename = $_FILES['distributors_data']['tmp_name'];
                     $xml_data = @file_get_contents($filename);

                     Distributors::importDistributors($xml_data);

                     Notification::set('success', __('Distributors was successfully imported.', 'parts'));
                     Request::redirect('index.php?id=distributors');
                   }
                   catch(Exception $e)
                   {
                     $page_error = $e->getMessage();
                   }
                 }
               }
               else
               {
                 die('csrf detected!');
               }
             }

             View::factory('store/distributors/views/backend/import')
                     ->assign('field_errors', $field_errors)
                     ->assign('page_error', $page_error)
                     ->display();
             break;

             case 'add_price_type':

                if (Request::post('price_type_description')) $price_type_description = Text::trimStr(Request::post('price_type_description'), 255); else $price_type_description = null;
                if (Request::post('price_type_sign')) $price_type_sign = Text::trimStr(Request::post('price_type_sign'), 10); else $price_type_sign = null;
                if (Request::post('price_type_currency')) $price_type_currency = Text::trimStr(Request::post('price_type_currency'), 3); else $price_type_currency = null;

                if (Request::post('add_price_type') || Request::post('add_price_type_and_exit')) 
                {
                  if (Security::check(Request::post('csrf'))) 
                  {

                    if ($price_type_description == '') $errors['description_error'] = __('This field should not be empty', 'price-types');
                    if ($price_type_currency == '') $errors['currency_error'] = __('This field should not be empty', 'price-types');

                    try
                    {
                      try
                      {
                        $query = 'SELECT COUNT(id) > 0 FROM price_types WHERE description = \'' . MySQL::escapeString($price_type_description) . '\'';
                        $descriptions_count = (boolean)MySQL::selectCell($query);

                        $query = 'SELECT COUNT(id) > 0 FROM price_types WHERE currency = \'' . MySQL::escapeString($price_type_currency) . '\'';
                        $currency_count = (boolean)MySQL::selectRow($query);
                      }
                      catch(Exception $e)
                      {
                        throw new Exception(__('Error retreiving information about price type.', 'price-types'));
                      }

                      if ($descriptions_count) $errors['description_error'] = __('Price type with such description already exists', 'price-types');
                      if ($currency_count) $errors['currency_error'] = __('Price type with such currency already exists', 'price-types');

                      if (count($errors) == 0)
                      {
                        try
                        {
                          $query = 'INSERT INTO price_types(description, sign, currency) VALUES(\''. MySQL::escapeString($price_type_description) . 
                                   '\', \''. MySQL::escapeString($price_type_sign) . 
                                   '\', \''. MySQL::escapeString($price_type_currency) . '\')';

                          MySQL::query($query);
                        }
                        catch(Exception $e)
                        {
                          throw new Exception(__('Error saving price type data.', 'price-types'));
                        }

                        $price_type_id = MySQL::getInsertId();

                        Notification::set('success', __('Price type &quot;<i>:price_type_description</i>&quot; have been added successfully.', 'price-types', array(':price_type_description' => $price_type_description)));

                        if (Request::post('add_price_type_and_exit')) 
                        {
                          Request::redirect('index.php?id=price-types');
                        } 
                        else 
                        {
                          Request::redirect('index.php?id=price-types&action=edit_price_type&price_type_id='.urlencode($price_type_id), true);
                        } 
                      }
                    }
                    catch(Exception $e)
                    {
                      Notification::setNow('error', $e->getMessage());
                    }
                  }
                  else
                  {
                     die('csrf detected!');
                  }
                }

                View::factory('store/price-types/views/backend/add')
                      ->assign('price_type_description', $price_type_description)
                      ->assign('price_type_sign', $price_type_sign)
                      ->assign('price_type_currency', $price_type_currency)
                      ->assign('errors', $errors)                                    
                      ->display();
                
                break;
             
             case 'edit_price_type':

                $price_type_id = Request::get('price_type_id');

                if (Request::post('edit_price_type') || Request::post('edit_price_type_and_exit')) 
                {
                  if (Security::check(Request::post('csrf'))) 
                  {
                    $price_type_description = Text::trimStr(Request::post('price_type_description'), 255);
                    $price_type_sign = Text::trimStr(Request::post('price_type_sign'), 10);
                    $price_type_currency = Text::trimStr(Request::post('price_type_currency'), 3);
                
                    if (trim($price_type_description) == '') $errors['description_error'] = __('This field should not be empty', 'price-types');
                    if (trim($price_type_currency) == '') $errors['currency_error'] = __('This field should not be empty', 'price-types');

                    try
                    {
                      try
                      {
                        $query = 'SELECT COUNT(id) > 0 FROM price_types WHERE description = \'' . MySQL::escapeString($price_type_description) . '\' AND id <> \'' . MySQL::escapeString($price_type_id) . '\'';
                        $descriptions_count = (boolean)MySQL::selectCell($query);

                        $query = 'SELECT COUNT(id) > 0 FROM price_types WHERE currency = \'' . MySQL::escapeString($price_type_currency) . '\' AND id <> \'' . MySQL::escapeString($price_type_id) . '\'';
                        $currency_count = (boolean)MySQL::selectCell($query);
                      }
                      catch(Exception $e)
                      {
                        throw new Exception(__('Error retreiving information about price type.', 'price-types'));
                      }

                      if ($descriptions_count) $errors['description_error'] = __('Price type with such description already exists', 'price-types');
                      if ($currency_count) $errors['currency_error'] = __('Price type with such currency already exists', 'price-types');

                      if (count($errors) == 0)
                      {
                        try
                        {
                          $query = 'UPDATE price_types SET description = \''. MySQL::escapeString($price_type_description) . '\', sign = \'' . MySQL::escapeString($price_type_sign) . '\', currency = \''. MySQL::escapeString($price_type_currency) . '\' WHERE id = \'' . MySQL::escapeString($price_type_id) . '\'';

                          MySQL::query($query);
                        }
                        catch(Exception $e)
                        {
                          throw new Exception(__('Error saving price type data', 'price-types'));
                        }

                        Notification::set('success', __('Your changes to the price type &quot;<i>:price_type</i>&quot; have been saved.', 'price-types', array(':price_type' => $price_type_description)));

                        if (Request::post('edit_price_type_and_exit'))
                        {
                          Request::redirect('index.php?id=price-types');
                        }
                        else
                        {
                          Request::redirect('index.php?id=price-types&action=edit_price_type&price_type_id='.urlencode($price_type_id), true);
                        }
                      }
                    }
                    catch (Exception $e)
                    {
                      Notification::setNow('error', $e->getMessage());
                    }
                  }
                  else
                  {
                    die('csrf detected!');
                  }
                }
                else
                {
                  $price_type_description = null;
                  $price_type_sign = null;
                  $price_type_currency = null;

                  try
                  {
                    try
                    {
                      $query = 'SELECT * FROM price_types WHERE id = \''. MySQL::escapeString($price_type_id) . '\'';
                      $row_price_types = MySQL::selectRow($query);
                    }
                    catch (Exception $e)
                    {
                      throw new Exception(__('Could not retreive data.', 'price-types'));
                    }

                    if ($row_price_types === null)
                    {
                      throw new Exception(__('Record was not found.', 'price-types'));
                    }

                    $price_type_description = $row_price_types['description'];
                    $price_type_sign = $row_price_types['sign'];
                    $price_type_currency =  $row_price_types['currency'];
                  }
                  catch (Exception $e)
                  {
                    $errors['page_error'] = $e->getMessage();
                  }
                }

                View::factory('store/price-types/views/backend/edit')
                      ->assign('price_type_id', $price_type_id)
                      ->assign('price_type_description', $price_type_description)
                      ->assign('price_type_sign', $price_type_sign)
                      ->assign('price_type_currency', $price_type_currency)
                      ->assign('errors', $errors)
                      ->display();

                break;

             case 'delete_price_type':

                $price_type_id = Request::get('price_type_id');

                try
                {
                  try
                  {
                    $query = 'SELECT description FROM price_types WHERE id = \''. MySQL::escapeString($price_type_id) . '\'';
                    $description = MySQL::selectCell($query);
                  }
                  catch(Exception $e)
                  {
                    throw new Exception(__('Error retreiving information about price type.', 'price-types'));
                  }

                  try
                  {
                    $query = 'SELECT COUNT(id) > 0 FROM part_prices WHERE price_type_id = \''. MySQL::escapeString($price_type_id) . '\'';
                    $prices_count = (boolean)MySQL::selectCell($query);
                  }
                  catch(Exception $e)
                  {
                    throw new Exception(__('Error deleting price type.', 'price-types'));
                  }
                   
                  if ($prices_count)
                  {
                    throw new Exception(__('Could not delete price type &quot;<i>:price_type_description</i>&quot; because it\'s used in part prices.', 'price-types', array(':price_type_description' => $price_type_description)));
                  }
                  else
                  {
                    try
                    {
                      $query = 'DELETE FROM price_types WHERE id = \''. MySQL::escapeString($price_type_id) . '\'';
                      MySQL::query($query);
                    }
                    catch(Exception $e)
                    {
                      throw new Exception(__('Error deleting price type.', 'price-types'));
                    }

                    Notification::set('success', __('Price type &quot;<i>:price_type_description</i>&quot; was deleted.', 'price-types', array(':price_type_description' => $price_type_description)));
                  }
                }
                catch (Exception $e)
                {
                  Notification::set('error', $e->getMessage());
                }

                Request::redirect('index.php?id=price-types');

                break;
        }
      } 
      else 
      { 
        $distributors = null;

        try
        {
          $query = 'SELECT * FROM distributors';
          $rs_distributors = MySQL::query($query);

          $distributors_array = array();

          $count = 0;

          while ($distributor = MySQL::fetch($rs_distributors))
          {
            $distributors_array[$count]['id'] = $distributor['id'];
            $distributors_array[$count]['user_id'] = $distributor['user_id'];
            $distributors_array[$count]['customer_id'] = $distributor['customer_id'];
            $distributors_array[$count]['password'] = $distributor['password'];
            $distributors_array[$count]['approved'] = $distributor['approved'];
            $distributors_array[$count]['price_level'] = $distributor['price_level'];
            $distributors_array[$count]['sort'] = $distributor['id'];
            $count++;
          }

          MySQL::free($rs_distributors);

          $distributors = Arr::subvalSort($distributors_array, 'sort');
        }
        catch (Exception $e)
        {
          $errors['page_error'] = __('Data retreival error: :error', 'distributors', array(':error' => $e->getMessage()));
        }

        View::factory('store/distributors/views/backend/index')
              ->assign('distributors', $distributors)
              ->assign('errors', $errors)
              ->display();
      }
    } 
    else 
    {
        Request::redirect('index.php?id=users&action=edit&user_id='.Session::get('user_id'));
    }
  }
}
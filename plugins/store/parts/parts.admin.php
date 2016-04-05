<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<?php
Navigation::add(__('Parts', 'parts'), 'store', 'parts', 10);

Action::add('admin_pre_render','PartsAdmin::_partsAddPrice');

class PartsAdmin extends Backend 
{
  private static function csv2array($str, $delim=',', $enclose='"', $preserve=false)
  { 
    $resArr = array(); 
    $n = 0; 
    $expEncArr = explode($enclose, $str);

    foreach($expEncArr as $EncItem)
    {
      if($n++%2)
      {
        array_push($resArr, array_pop($resArr) . ($preserve?$enclose:'') . $EncItem.($preserve?$enclose:''));
      }
      else
      {
        $expDelArr = explode($delim, $EncItem); 
        array_push($resArr, array_pop($resArr) . array_shift($expDelArr)); 
        $resArr = array_merge($resArr, $expDelArr); 
      }
    }
    return $resArr; 
  }

  public static function _partsAddPrice() 
  {
    if (Request::isAjax() && Request::post('action') == 'add_price')
    {
      $price_type_descriptions = array();
      $page_error = null;

      try
      {
        try
        {
          $query = 'SELECT id, description FROM price_types';
          $rs_price_type_descriptions = MySQL::query($query);
        }
        catch(Exception $e)
        {
          throw new Exception(__('Could not retreive price types list.', 'parts'));
        }

        $price_type_descriptions[''] = __('Please choose...', 'parts');

        while($row = MySQL::fetch($rs_price_type_descriptions))
        {
          $price_type_descriptions[(string)$row['id']] = $row['description'];
        }

        MySQL::free($rs_price_type_descriptions);
      }
      catch(Exception $e)
      {
        $page_error = $e->getMessage();
      }

      View::factory('store/parts/views/backend/add')
            ->assign('price_type_descriptions', $price_type_descriptions)
            ->assign('page_error', $page_error)
            ->display();

      Request::shutdown();
    }
  }

  public static function main() 
  {
    if (Request::post('cancel')) 
    {
      Request::redirect('index.php?id=parts');
    }

    if (Request::get('action')) 
    {
        switch (Request::get('action')) 
        {
            case 'import':

             $field_errors = array();
             $page_error = null;
             $update_only_mode = false;

             if (Request::post('import_data'))
             {
               if (Security::check(Request::post('csrf'))) 
               {
                 $file = @$_FILES['part_data'];
                 $update_only_mode = Request::post('update_only_mode') ? true : false;

                 if (!$file || $file['size'] == 0 || $file['error'] == 4)
                 {
                    $field_errors['data_error'] = __('Please specify file with data to be imported.', 'parts');
                 }
                 else
                 {
                   try
                   {
                     $filename = $_FILES['part_data']['tmp_name'];
                     $data_to_import = @file_get_contents($filename);

                     if (substr($data_to_import, 0, 3) == pack('CCC', 0xef, 0xbb, 0xbf))
                     {
                       $data_to_import = substr($data_to_import, 3);
                     } 

                     $importable_fields = array('code', 'description', 'language_code', 'pounds', 'ounces', 'width', 'height', 'length', 'separate_box', 'items_per_box', 'currency', 'price');

                     $rows = explode("\r\n", $data_to_import);
                     $rows_count = count($rows);

                     $field_names = self::csv2array(strtolower($rows[0]));
                     $fields_count = count($field_names);

                     $code_index = array_search($importable_fields[0], $field_names);
                     $description_index = array_search($importable_fields[1], $field_names);
                     $language_code_index = array_search($importable_fields[2], $field_names);
                     $currency_index = array_search($importable_fields[10], $field_names);
                     $price_index = array_search($importable_fields[11], $field_names);

                     if ($code_index === false)
                     {
                       throw new Exception(__('Incorrect file format. Part code field is missing.', 'parts'));
                     }

                     $queries = array();

                     try
                     {
                       MySQL::startTransaction();
                        
                       for ($i = 1; $i < $rows_count; $i++)
                       {
                         $field_values = self::csv2array($rows[$i]);

                         if (count($field_values) != $fields_count)
                         {
                           continue;
                         }

                         $part_code = $field_values[$code_index];
                       
                         if (!Valid::hasValue($part_code))
                         {
                           continue;
                         }

                         $part_id = null;
                         $localization_id = null;
                         $price_id = null;
                         $price_type_id = null;

                         try
                         {
                           $query = 'SELECT id FROM parts WHERE code = \'' . MySQL::escapeString($part_code) . '\'';
                           $part_id = MySQL::selectCell($query);
                         }
                         catch(Exception $e)
                         {
                           throw new Exception(__('Error veryfing part data.', 'parts'));
                         }

                         if (!$update_only_mode)
                         {
                           if ($part_id === null)
                           {
                             if ($description_index === false)
                             {
                               throw new Exception(__('Incorrect data for part "' . $part_code . '". Part description field is missing.', 'parts'));
                             }
                             if ($language_code_index === false) 
                             {
                               throw new Exception(__('Incorrect data for part "' . $part_code . '". Language code field is missing.', 'parts'));
                             }
                             if ($currency_index === false)
                             {
                               throw new Exception(__('Incorrect data for part "' . $part_code . '". Currency field is missing.', 'parts'));
                             }
                             if ($price_index === false)
                             {
                               throw new Exception(__('Incorrect data for part "' . $part_code . '". Price field is missing.', 'parts'));
                             }
                           }

                           if (!($currency_index === false))
                           {
                             $currency = $field_values[$currency_index];

                             if (Valid::hasValue($currency))
                             {
                               try
                               {
                                 $query = 'SELECT id FROM price_types WHERE currency = \'' . MySQL::escapeString($currency) . '\'';
                                 $price_type_id = MySQL::selectCell($query);
                               }
                               catch(Exception $e)
                               {
                                 throw new Exception(__('Error retreiving currency data.', 'parts'));
                               }

                               if ($price_type_id === null)
                               {
                                 throw new Exception(__('Unknown currency: "' . $currency . '". Please add corresponding price type first before importing data.' , 'parts'));
                               }
                             }
                           }
                         }

                         if ($part_id)
                         {
                           if (!($language_code_index === false))
                           {
                             $language_code = $field_values[$language_code_index];

                             if (!Valid::hasValue($language_code))
                             {
                               $language_code = 'en';
                             }
                             try
                             {
                               $query = 'SELECT id FROM part_localization WHERE language_code = \'' . MySQL::escapeString($language_code) . '\' AND part_id = \'' . MySQL::escapeString($part_id) . '\'';
                               $localization_id = MySQL::selectCell($query);
                             }
                             catch(Exception $e)
                             {
                               throw new Exception(__('Error retreiving localization data.', 'parts'));
                             }
                           }
                         }

                         if ($part_id && $price_type_id)
                         {
                           try
                           {
                             $query = 'SELECT id FROM part_prices WHERE part_id = \'' . MySQL::escapeString($part_id) . '\' AND price_type_id = \'' . MySQL::escapeString($price_type_id) . '\'';
                             $price_id = MySQL::selectCell($query);
                           }
                           catch(Exception $e)
                           {
                             throw new Exception(__('Error retreiving price data.', 'parts'));
                           }
                         }

                         $part_fields['parts'] = array();
                         $part_fields['part_localization'] = array();
                         $part_fields['part_prices'] = array();

                         if ($part_id)
                         {
                           $part_fields['parts']['id'] = $part_id;
                         }
                         else
                         {
                           $part_fields['parts']['code'] = $part_code;
                         }

                         if ($localization_id)
                         {
                           $part_fields['part_localization']['id'] = $localization_id;
                         }
                         else
                         {
                           if ($part_id)
                           {
                             $part_fields['part_localization']['part_id'] = $part_id;
                           }
                         }

                         if ($price_id)
                         {
                           $part_fields['part_prices']['id'] = $price_id;
                         }
                         else
                         {
                           if ($part_id)
                           {
                             $part_fields['part_prices']['part_id'] = $part_id;
                           }
                         }

                         for ($j = 1; $j < $fields_count; $j++)
                         {
                           if (!in_array($field_names[$j], $importable_fields))
                           {
                             continue;
                           }

                           if ($field_names[$j] == $importable_fields[1] || $field_names[$j] == $importable_fields[2])
                           {
                             if (Valid::hasValue($field_values[$j]))
                             {
                               $part_fields['part_localization'][$field_names[$j]] = $field_values[$j];
                             }
                           }
                           elseif ($field_names[$j] == $importable_fields[11] || $field_names[$j] == $importable_fields[10])
                           {
                             if ($field_names[$j] != 'currency')
                             {
                               if (Valid::hasValue($field_values[$j]))
                               {
                                 $part_fields['part_prices']['value'] = $field_values[$j];
                                 $part_fields['part_prices']['price_type_id'] = $price_type_id;
                               }
                             }
                           }
                           else
                           {
                             if (Valid::hasValue($field_values[$j]))
                             {
                               $part_fields['parts'][$field_names[$j]] = $field_values[$j];
                             }
                           }
                         }

                         if ($part_id)
                         {
                           foreach ($part_fields as $table => $fields)
                           {
                             if (count($fields) < 2)
                             {
                               continue;
                             }

                             if (isset($fields['id']))
                             {
                               $query = 'UPDATE ' . $table . ' SET ';
                               
                               foreach ($fields as $field => $value)
                               {
                                 if ($field != 'id')
                                 {
                                   $query .= $field . ' = \'' . MySQL::escapeString($value) . '\', ';
                                 }
                               }

                               $query = substr($query, 0, strlen($query) - 2);
                                 
                               $query .= ' WHERE id = \'' . MySQL::escapeString($fields['id']) . '\'';
                             }
                             else
                             {
                               if (!$update_only_mode)
                               {
                                 $new_fields = null;
                                 $new_values = null;

                                 foreach ($fields as $field => $value)
                                 {
                                   $new_fields .= $field . ', ';
                                   $new_values .= '\'' . MySQL::escapeString($value) . '\', ';
                                 }

                                 $new_fields = substr($new_fields, 0, strlen($new_fields) - 2);
                                 $new_values = substr($new_values, 0, strlen($new_values) - 2);

                                 $query = 'INSERT INTO ' . $table . '(' . $new_fields . ') VALUES(' . $new_values . ')';
                               }
                             }

                             MySQL::query($query);
                           }
                         }
                         else
                         {
                           if (!$update_only_mode)
                           {
                             $new_fields = null;
                             $new_values = null;

                             foreach ($part_fields['parts'] as $field => $value)
                             {
                               $new_fields .= $field . ', ';
                               $new_values .= '\'' . MySQL::escapeString($value) . '\', ';
                             }

                             $new_fields = substr($new_fields, 0, strlen($new_fields) - 2);
                             $new_values = substr($new_values, 0, strlen($new_values) - 2);

                             $query = 'INSERT INTO parts(' . $new_fields . ') VALUES(' . $new_values . ')';

                             MySQL::query($query);

                             $last_insert_id = MySQL::getInsertId();

                             $part_fields['part_localization']['part_id'] = $last_insert_id;

                             $new_fields = null;
                             $new_values = null;

                             foreach ($part_fields['part_localization'] as $field => $value)
                             {
                               $new_fields .= $field . ', ';
                               $new_values .= '\'' . MySQL::escapeString($value) . '\', ';
                             }

                             $new_fields = substr($new_fields, 0, strlen($new_fields) - 2);
                             $new_values = substr($new_values, 0, strlen($new_values) - 2);

                             $query = 'INSERT INTO part_localization(' . $new_fields . ') VALUES(' . $new_values . ')';

                             MySQL::query($query);

                             $part_fields['part_prices']['part_id'] = $last_insert_id;

                             $new_fields = null;
                             $new_values = null;

                             foreach ($part_fields['part_prices'] as $field => $value)
                             {
                               $new_fields .= $field . ', ';
                               $new_values .= '\'' . MySQL::escapeString($value) . '\', ';
                             }

                             $new_fields = substr($new_fields, 0, strlen($new_fields) - 2);
                             $new_values = substr($new_values, 0, strlen($new_values) - 2);

                             $query = 'INSERT INTO part_prices(' . $new_fields . ') VALUES(' . $new_values . ')';

                             MySQL::query($query);
                           }
                         }
                       }

                       MySQL::commitTransaction();

                       Notification::set('success', __('Data was successfully imported.', 'parts'));

                       Request::redirect('index.php?id=parts');
                     }
                     catch(Exception $e)
                     {
                       try
                       {
                         MySQL::rollbackTransaction();
                       }
                       catch(Exception $ex)
                       {

                       }

                       throw new Exception('Could not import data: ' . $e->getMessage() . '<br>' . $query);
                     }
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

             View::factory('store/parts/views/backend/import')
                     ->assign('update_only_mode', $update_only_mode)
                     ->assign('field_errors', $field_errors)
                     ->assign('page_error', $page_error)
                     ->display();
             break;

            case 'edit':

             $part_id = Request::get('part_id');

             $part_code = null;
             $part_width = null;
             $part_height = null;
             $part_length = null;
             $part_pounds = null;
             $part_ounces = null;
             $part_separate_box = false;
             $part_items_per_box = 1;

             $part_descriptions = null;

             $part_separate_box = false;
             $part_items_per_box = 1;

             $allowed_price_types_count = null;

             $part_prices = array();
             $part_descriptions = array();
             $price_type_descriptions = array();

             $page_error = null;
             $field_errors = array();
             $prices_errors = array();

             try
             {
               try
               {
                 $query = 'SELECT id, description FROM price_types ORDER BY id';
                 $rs_price_type_descriptions = MySQL::query($query);

                 while($row = MySQL::fetch($rs_price_type_descriptions))
                 {
                   $price_type_descriptions[$row['id']] = $row['description'];
                 }

                 MySQL::free($rs_price_type_descriptions);
               }
               catch(Exception $e)
               {
                 throw new Exception(__('Error retreiving list of price types.', 'parts'));
               }

               try
               {
                 $query = 'SELECT description, language_code FROM part_localization WHERE part_id = \'' . MySQL::escapeString($part_id) . '\'';
                 $rs_part_descriptions = MySQL::query($query);

                 while($row = MySQL::fetch($rs_part_descriptions))
                 {
                   $part_descriptions[$row['language_code']] = $row['description'];
                 }

                 MySQL::free($rs_part_descriptions);
               }
               catch(Exception $e)
               {
                 throw new Exception(__('Could not retreive list of part\'s descriptions.', 'parts'));
               }

               if (Request::post('edit_part') || Request::post('edit_part_and_exit'))
               {
                 if (Security::check(Request::post('csrf'))) 
                 {
                   $part_code = Request::post('part_code');

                   $part_width = Request::post('part_width');
                   $part_height = Request::post('part_height');
                   $part_length = Request::post('part_length');
                   $part_pounds = Request::post('part_pounds');
                   $part_ounces = Request::post('part_ounces');
                   $part_separate_box = Request::post('part_separate_box') ? 1 : 0;
                   $part_items_per_box = Request::post('part_items_per_box');

                   $part_price_ids = Request::post('part_price');
                   $part_price_type_ids = Request::post('part_price_type');
                   $part_price_values = Request::post('part_price_value');

                   $price_count = count($part_price_ids);

                   $ids = array();

                   for ($i = 0; $i < $price_count; $i++)
                   {
                     $part_price_type_id = $part_price_type_ids[$i];

                     if (array_key_exists($part_price_type_id, $ids))
                     {
                       $prices_errors[$part_price_type_id] = '';
                     }
                     else
                     {
                       $ids[$part_price_type_id] = $part_price_type_id;
                     }

                     $part_prices[$part_price_ids[$i]]['price_type_id'] = $part_price_type_ids[$i];
                     $part_prices[$part_price_ids[$i]]['value'] = (double)$part_price_values[$i];
                   }

                   if (trim($part_code) == '') $field_errors['code_error'] = __('This field should not be empty', 'parts');

                   if (count($field_errors) == 0 && count($prices_errors) == 0)
                   {
                     $query = 'UPDATE parts SET code =\'' . MySQL::escapeString($part_code) . '\', '.
                              'width = \'' . MySQL::escapeString($part_width) . '\', '.
                              'height = \'' . MySQL::escapeString($part_height) . '\', '.
                              'length = \'' . MySQL::escapeString($part_length) . '\', '.
                              'pounds = \'' . MySQL::escapeString($part_pounds) . '\', '.
                              'ounces = \'' . MySQL::escapeString($part_ounces) . '\', '.
                              'separate_box = \'' . MySQL::escapeString($part_separate_box) . '\', '.
                              'items_per_box = \'' . MySQL::escapeString($part_items_per_box) . '\' '.
                              'WHERE id = \'' . MySQL::escapeString($part_id) . '\'';

                     $insert_queries = array();
                     $update_queries = array();
                     $delete_ids = array();

                     if ($price_count > 0)
                     {
                       for ($i = 0; $i < $price_count; $i++)
                       {
                         $part_price_id = $part_price_ids[$i];
                         $part_price_type_id = $part_price_type_ids[$i];
                         $part_price_value = $part_price_values[$i];

                         if ($part_price_id && strpos($part_price_id, 'new') === false)
                         {
                           $update_queries[] = 'UPDATE part_prices SET value = \'' . MySQL::escapeString($part_price_value) . '\' WHERE id = \'' . MySQL::escapeString($part_price_id) . '\'';
                           $delete_ids[] = $part_price_id;
                         }
                         else
                         {
                           if ($part_price_type_id && $part_price_value)
                           {
                              $insert_queries[] .= '(' . $part_id . ', ' . $part_price_type_id . ', \'' . $part_price_value . '\')';
                           }
                         }
                       }
                     }

                     if (count($delete_ids) > 0)
                     {
                       $delete_query = 'DELETE FROM part_prices WHERE id NOT IN(';
                       foreach ($delete_ids as $delete_id)
                       {
                         $delete_query .= $delete_id . ', ';
                       }
                       $delete_query = substr($delete_query, 0, strlen($delete_query) - 2);
                       $delete_query .= ') AND part_id = \'' . MySQL::escapeString($part_id) . '\'';
                     }
                     else if ($price_count > 0)
                     {
                       $delete_query = NULL;
                     }
                     else
                     {
                       $delete_query = 'DELETE FROM part_prices WHERE part_id = \'' . MySQL::escapeString($part_id) . '\'';
                     }

                     $insert_query = NULL;

                     if (count($insert_queries) > 0)
                     {
                       foreach ($insert_queries as $sub_query)
                       {
                         $insert_query .= $sub_query . ', ';
                       }
                       $insert_query = 'INSERT INTO part_prices(part_id, price_type_id, value) VALUES' . substr($insert_query, 0, strlen($insert_query) - 2);
                     }

                     try
                     {
                       MySQL::startTransaction();

                       MySQL::query($query);

                       if ($delete_query)
                       {
                         MySQL::query($delete_query);
                       }

                       if ($insert_query)
                       {
                         MySQL::query($insert_query);
                       }

                       if (count($update_queries) > 0)
                       {
                         foreach ($update_queries as $update_query)
                         {
                           MySQL::query($update_query);
                         }
                       }

                       MySQL::commitTransaction();

                       Notification::set('success', __('Your changes have been saved.', 'parts'));
                     }
                     catch(Exception $e)
                     {
                       try
                       {
                         MySQL::rollbackTransaction();
                       }
                       catch(Exception $ex)
                       {
                       }

                       $page_error = 'Error updating part details.';
                     }

                     if (!$page_error)
                     {
                       if (Request::post('edit_part_and_exit'))
                       {
                         Request::redirect('index.php?id=parts');
                       }
                       else
                       {
                         Request::redirect('index.php?id=parts&action=edit&part_id=' . urlencode($part_id));
                       }
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
                 try
                 {
                   $query = 'SELECT * FROM parts WHERE id = \'' . MySQL::escapeString($part_id) . '\'';

                   try
                   {
                     $row_part = MySQL::selectRow($query);
                   }
                   catch(Exception $e)
                   {
                     throw new Exception(__('Could not retreive part details.', 'parts'));
                   }

                   if ($row_part === null)
                   {
                     throw new Exception(__('Record was not found.', 'parts'));
                   }

                   try
                   {
                     $query = 'SELECT pp.id, pt.id AS price_type_id, pp.value FROM part_prices pp LEFT JOIN price_types pt ON pt.id = pp.price_type_id WHERE part_id = \'' . MySQL::escapeString($part_id) . '\'';
                     $rs_part_prices = MySQL::query($query);
                   }
                   catch(Exception $e)
                   {
                     throw new Exception(__('Could not retreive part prices.', 'parts'));
                   }

                   $part_code = $row_part['code'];
                   $part_width = $row_part['width'];
                   $part_height = $row_part['height'];
                   $part_length = $row_part['length'];
                   $part_pounds = $row_part['pounds'];
                   $part_ounces = $row_part['ounces'];
                   $part_separate_box = $row_part['separate_box'] ? true : false;
                   $part_items_per_box = $row_part['items_per_box'];

                   while ($row = MySQL::fetch($rs_part_prices))
                   {
                     $id = $row['id'];
                     $part_prices[$id]['price_type_id'] = $row['price_type_id'];
                     $part_prices[$id]['value'] = $row['value'];
                   }

                   MySQL::free($rs_part_prices);
                 }
                 catch(Exception $e)
                 {
                   $page_error = $e->getMessage();
                 }
               }
             }
             catch(Exception $e)
             {
               $page_error = $e->getMessage();
             }

             View::factory('store/parts/views/backend/edit')
                   ->assign('part_id', $part_id)
                   ->assign('part_code', $part_code)
                   ->assign('part_width', $part_width)
                   ->assign('part_height', $part_height)
                   ->assign('part_length', $part_length)
                   ->assign('part_pounds', $part_pounds)
                   ->assign('part_ounces', $part_ounces)
                   ->assign('part_separate_box', $part_separate_box)
                   ->assign('part_items_per_box', $part_items_per_box)
                   ->assign('part_descriptions', $part_descriptions)
                   ->assign('price_type_descriptions', $price_type_descriptions)
                   ->assign('part_prices', $part_prices)
                   ->assign('allowed_price_types_count', $allowed_price_types_count)
                   ->assign('page_error', $page_error)
                   ->assign('field_errors', $field_errors)
                   ->assign('prices_errors', $prices_errors)
                   ->display();
             break;
        }
    } 
    else 
    {
        $records_per_page = 10;

        $parts = null;

        $records_start = null;
        $records_end = null;
        $records_total = null;

        $pages_total = null;
        $pages_number = null;

        $page_error = null;

        $sort_fields = array('code', 'description');
        $sort_query_fields = array($sort_fields[0] => 'code', $sort_fields[1] => 'description');
        $sort_directions = array('up', 'down');
        $sort_query_directions = array($sort_directions[0] => 'asc', $sort_directions[1] => 'desc');

        $sort_field = Valid::hasValue(Request::get('s')) ? strtolower(Request::get('s')) : null;
        $sort_direction = Valid::hasValue(Request::get('d')) ? strtolower(Request::get('d')) : $sort_directions[0];

        $sort_field = in_array($sort_field, $sort_fields) ? $sort_field : null;
        $sort_direction = in_array($sort_direction, $sort_directions) ? $sort_direction : $sort_directions[0];

        try
        {
          try
          {
            $query = 'SELECT COUNT(p.id) FROM parts p LEFT JOIN part_localization pl ON pl.part_id = p.id WHERE pl.language_code LIKE \'en%\'';

            $records_total = MySQL::selectCell($query);
            $pages_total = ceil($records_total / $records_per_page);
            $pages_number = Valid::hasValue(Request::get('page')) ? (Valid::isInteger(Request::get('page')) ? (integer)Request::get('page') : 'all') : 1;

            if (Valid::isInteger($pages_number))
            {
              if ($pages_number > $pages_total)
              {
                $pages_number = $pages_total;
              }
              if ($pages_number < 1)
              {
                $pages_number = 1;
              }
            }
            else
            {
              $pages_number = 'all';
            }

            $query = 'SELECT p.id, code, pl.description, pounds, ounces, width, height, length FROM parts p LEFT JOIN part_localization pl ON pl.part_id = p.id WHERE pl.language_code LIKE \'en%\'';

            if ($sort_field)
            {
              $query .= ' ORDER BY ' . $sort_query_fields[$sort_field] . ' ' . ($sort_direction ? $sort_query_directions[$sort_direction] . ' ' : null);
            }

            if (Valid::isInteger($pages_number))
            {
              $query .= ' LIMIT ' . ($pages_number - 1) * $records_per_page . ', ' . $records_per_page;
            }

            $rs_parts = MySQL::query($query);

            $records_count = MySQL::rowCount($rs_parts);
            $records_start = Valid::isInteger($pages_number) ? ($pages_number - 1) * $records_per_page + 1: 1;
            $records_end = $records_start + $records_count - 1;

            for ($count = 0; $count < $records_count; $count++)
            {
              $part = MySQL::fetch($rs_parts);

              $parts[$count]['id'] = $part['id'];
              $parts[$count]['code'] = $part['code'];
              $parts[$count]['description'] = $part['description'];
              $parts[$count]['width'] = $part['width'];
              $parts[$count]['height'] = $part['height'];
              $parts[$count]['length'] = $part['length'];
              $parts[$count]['pounds'] = $part['pounds'];
              $parts[$count]['ounces'] = $part['ounces'];

              $query = 'SELECT pp.id AS price_type_id, pt.sign, pp.value FROM part_prices pp LEFT JOIN price_types pt ON pp.price_type_id = pt.id WHERE pp.part_id = \'' . MySQL::escapeString($part['id']) . '\'';

              try
              {
                $rs_prices = MySQL::query($query);
              }
              catch (Exception $e)
              {
                throw new Exception();
              }

              $parts[$count]['prices'] = array();

              $price_count = 0;

              while ($price = MySQL::fetch($rs_prices))
              {
                $parts[$count]['prices'][$price_count]['price_type_id'] = $price['price_type_id'];
                $parts[$count]['prices'][$price_count]['sign'] = $price['sign'];
                $parts[$count]['prices'][$price_count]['value'] = $price['value'];
                $price_count++;
              }

              MySQL::free($rs_prices);
            }

            MySQL::free($rs_parts);
          }
          catch (Exception $e)
          {
            throw new Exception(__('Data retreival error: :error', 'parts', array(':error' => $e->getMessage())));
          }
        }
        catch (Exception $e)
        {
          $page_error = $e->getMessage();
        }

        View::factory('store/parts/views/backend/index')
              ->assign('parts', $parts)
              ->assign('sort_fields', $sort_fields)
              ->assign('sort_directions', $sort_directions)
              ->assign('sort_field', $sort_field)
              ->assign('sort_direction', $sort_direction)
              ->assign('records_start', $records_start)
              ->assign('records_end', $records_end)
              ->assign('records_total', $records_total)
              ->assign('pages_number', $pages_number)
              ->assign('pages_total', $pages_total)
              ->assign('page_error', $page_error)
              ->display();
    }
  }
}
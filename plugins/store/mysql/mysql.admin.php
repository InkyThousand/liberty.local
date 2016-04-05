<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<?php
Navigation::add(__('MySQL', 'mysql'), 'store', 'mysql', 1);

class MySQLAdmin extends Backend
{
  public static function main() 
  {
    if (Session::exists('user_role') && in_array(Session::get('user_role'), array('admin')))
    {
       $mysql_options_tbl = new Table('mysql');
       $mysql_options = $mysql_options_tbl->select(null, null);
       
       if (Valid::HasValue(Request::post('mysql_host'))) $host = Request::post('mysql_host'); else $host = $mysql_options['host'];
       if (Valid::HasValue(Request::post('mysql_user'))) $user = Request::post('mysql_user'); else $user = $mysql_options['user'];
       if (Valid::HasValue(Request::post('mysql_password'))) $password = Request::post('mysql_password'); else $password = $mysql_options['password'];
       if (Valid::HasValue(Request::post('mysql_database'))) $database = Request::post('mysql_database'); else $database = $mysql_options['database'];

       if (Request::post('check_connection'))
       {
         try
         {
           if (function_exists('mysqli_init'))
           {
             $link = @mysqli_init();

             if (!$link)
             {
               throw new Exception('Could not initialize MySQL interface');
             }

             if (!@mysqli_real_connect($link, $host, $user, $password, $database))
             {
               throw new Exception('(' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
             }

             @mysqli_close($link);
           }
           else
           {
             $link = @mysql_connect($host, $user, $password);

             if (!$link)
             {
               throw new Exception('(' . mysql_errno() . ') ' . mysql_error());
             }

             if (!@mysql_select_db($database))
             {
               throw new Exception('(' . mysql_errno() . ') ' . mysql_error());
             }

             @mysql_close($link);
           }

           Notification::setNow('success', __('Connection established successfully', 'mysql'));
         }
         catch(Exception $e)
         {
           Notification::setNow('error', __('Error: :error', 'mysql', array(':error' => Html::toText($e->getMessage()))));
         }
      }

      if (Request::post('edit_settings'))
      {
        if (Security::check(Request::post('csrf'))) 
        {
          $mysql_options_tbl->update(1, array('host' => $host, 
                                              'user' => $user, 
                                              'password' => $password, 
                                              'database' => $database));

          Notification::set('success', __('Your changes have been saved', 'mysql'));
          Request::redirect('index.php?id=mysql');
        }                                                                             
        else
        {
          die('csrf detected!');
        }
      }

      View::factory('store/mysql/views/backend/index')
            ->assign('host', $host)
            ->assign('user', $user)
            ->assign('password', $password)
            ->assign('database', $database)
            ->display();
    }
    else
    {
      Request::redirect('index.php?id=users&action=edit&user_id='.Session::get('user_id'));
    }
  }
}
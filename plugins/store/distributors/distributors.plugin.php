<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<?php
Plugin::register( __FILE__,
                __('Distributors', 'distributors'),
                __('Distributors', 'distributors'),
                '1.0.0',
                'razorolog',
                '',
                'distributors',
                'store');

Plugin::Admin('distributors', 'store');

class Distributors extends Frontend
{
  public static function main()
  {
  }

  protected static function route()
  {
    if (Uri::segment(0) == 'distributors' && !Uri::segment(1)) return 'login';
    if (Uri::segment(0) == 'distributors' && Uri::segment(1) == 'login') return 'login';
    if (Uri::segment(0) == 'distributors' && Uri::segment(1) == 'restore-password') return 'restore-password';
    if (Uri::segment(0) == 'distributors' && Uri::segment(1) == 'password-sent') return 'password-sent';

    return null;
  }

  public static function content()
  {
    if (self::route() == 'login')
    {
      self::showLoginForm();
    }
    if (self::route() == 'restore-password')
    {
      self::restorePassword();
    }
    if (self::route() == 'password-sent')
    {
      self::passwordSent();
    }
  }

  public static function showLoginForm()
  {
    $error = null;

    $action = Request::request('action');

    if ($action == 'dealer-login')
    {
      $user_id = Text::trimStr(Request::request('user_id'));
      $password = Request::request('password');

      if (Valid::hasValue($user_id) && Valid::hasValue($password))
      {
        $query = 'SELECT * FROM distributors WHERE user_id = \'' . MySQL::escapeString($user_id) . '\'';

        $row = MySQL::selectRow($query);

        if ($row == null)
        {
          $error = 'User does not exists';
        }
        else
        {
          if ($row['password'] != $password)
          {
            $error = 'Incorrect password';
          }
          else
          {
            if ($row['approved'])
            {
              $company = Valid::hasValue($row['price_level']) ? $row['price_level'] : 'No Price';
              $customer_id = Valid::hasValue($row['customer_id']) ? $row['customer_id'] : '';
              
              $dealership_options_tbl = new Table('dealership');
              $dealership_options = $dealership_options_tbl->select(null, null);
              
             // $redirect_url = $dealership_options['ezparts_url'] .  '?sysname=' . urlencode($dealership_options['partner_name']) . '&' . urlencode($dealership_options['passkey_name']) . '=' . urlencode($dealership_options['passkey_value']) . '&company=' . urlencode($company) . '&user_id=' . urlencode($user_id) . '&dn=&de=' . urlencode($user_id);
             // Request::redirect($redirect_url);
             
              $redirect_url = $dealership_options['ezparts_url'];
              
              $sysname = (string)$dealership_options['partner_name'];
              $passkey_name = (string)$dealership_options['passkey_name'];
              $passkey_value = (string)$dealership_options['passkey_value'];
              $company = (string)$company;
              $dn = (string)$customer_id;
              $de = (string)$user_id;
              $sendconfirmation = 'true';
              $sendnotification = 'true';

              ob_clean();

              View::factory('store/distributors/views/frontend/redirection')
                   ->assign('redirect_url', $redirect_url)
                   ->assign('sysname', $sysname)
                   ->assign('passkey_name', $passkey_name)
                   ->assign('passkey_value', $passkey_value)
                   ->assign('company', $company)
                   ->assign('user_id', $user_id)
                   ->assign('dn', $dn)
                   ->assign('de', $de)
                   ->assign('sendconfirmation', $sendconfirmation)
                   ->assign('sendnotification', $sendnotification)
                   ->display();

              Request::shutdown();
            }
            else
            {
              $error = 'Sorry, your account is not approved';
            }
          }
        }
      }
    }

    View::factory('store/distributors/views/frontend/login')
        ->assign('error', $error)
        ->display();
  }

  public static function restorePassword()
  {
    $error = null;

    $action = Request::request('action');

    if ($action == 'send-password')
    {
      $mode = 'send-password';

      $user_id = Text::trimStr(Request::request('user_id'));

      if (Valid::hasValue($user_id))
      {
        $query = 'SELECT password FROM distributors WHERE user_id = \'' . MySQL::escapeString($user_id) . '\'';

        $password = MySQL::selectCell($query);

        if ($password === null)
        {
          $error = 'User does not exists';
        }
        else
        {
          $notifications_options_tbl = new Table('notifications');
          $notifications_options = $notifications_options_tbl->select(null, null);

          $use_smtp = (bool)$notifications_options['use_smtp'];
          $smtp_host = $notifications_options['smtp_host'];
          $smtp_port = $notifications_options['smtp_port'];
          $smtp_auth_username = $notifications_options['smtp_auth_username'];
          $smtp_auth_password = $notifications_options['smtp_auth_password'];

          $from_name = $notifications_options['from_name'];
          $from_address = $notifications_options['from_address'];

          $phpMailer = new PHPMailer(true);
          $phpMailer->IsHTML(false);

          if ($use_smtp)
          {
            $phpMailer->IsSMTP();
            $phpMailer->SMTPKeepAlive = true;
            $phpMailer->Host = $smtp_host;
            $phpMailer->Port = $smtp_port;
            if (Valid::hasValue($smtp_auth_username))
            {
              $phpMailer->SMTPAuth = true;
              $phpMailer->Username = $smtp_auth_username;
              $phpMailer->Password = $smtp_auth_password;
            }
          }

          $site_name = Option::get('sitename');

          $subject = __('Password for distributor area on :site_name', 'distributors', array(':site_name' => $site_name));

          $body = @file_get_contents(STORAGE . DS . 'templates' . DS . 'distributor.txt');

          $body = str_replace('%SITE%', $site_name, $body);
          $body = str_replace('%PASSWORD%', $password, $body);
          $body = str_replace('%DISTRIBUTOR_LOGIN_PAGE%', Site::url() . 'distributors/login' , $body);

          $phpMailer->SetFrom($from_address, $from_name);
          $phpMailer->AddAddress($user_id, '');
          $phpMailer->Subject = $subject;
          $phpMailer->Body = $body;
          $phpMailer->Send();

          $redirect_url = Option::get('siteurl') . 'distributors/password-sent';

          Request::redirect($redirect_url);
        }
      }
    }

    View::factory('store/distributors/views/frontend/restore-password')
        ->assign('error', $error)
        ->display();
  }


  public static function passwordSent()
  {
    View::factory('store/distributors/views/frontend/password-sent')
        ->display();
  }

  public static function importDistributors($xml_data)
  {
    try
    {
      libxml_use_internal_errors(true);
      $xml = @new SimpleXMLElement($xml_data);
    }
    catch (Exception $e)
    {
      throw new Exception('Input data was not correctly formed. ' . $e->getMessage() . '.');
    }

    $distributors = $xml->xpath('/DocumentElement/UserInfo');

    try
    {
      MySQL::startTransaction();

      $query = 'TRUNCATE TABLE distributors';
      MySQL::query($query);

      foreach ($distributors as $distributor)
      {
        $user_id = Text::trimStr((string)$distributor[0]->UserID[0]);
        $customer_id = @Text::trimStr((string)$distributor[0]->CustID[0]);
        $password = Text::trimStr((string)$distributor[0]->Password[0]);
        $approved = strtolower($distributor[0]->Approved[0]) == 'true';
        $price_level = (string)$distributor[0]->PriceLevel[0];

        $query = 'INSERT INTO distributors(user_id, customer_id, password, approved, price_level) VALUES('.
                 '\''. MySQL::escapeString($user_id) . '\', '.
                 '\''. MySQL::escapeString($customer_id) . '\', '.
                 '\''. MySQL::escapeString($password) . '\', '.
                 ($approved ? '1' : '0') . ', '.
                 '\''. MySQL::escapeString($price_level) . '\')';
        MySQL::query($query);
      }

      MySQL::commitTransaction();

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

      throw new Exception('Could not import data. The error message is: ' . $e->getMessage());
    }

    @file_put_contents(STORAGE . DS . 'distributors' . DS . date('Y-m-d-H-i-s') . '.xml', $xml_data);
  }

  public static function title()
  {
    return 'Distributors Area';
  }
}
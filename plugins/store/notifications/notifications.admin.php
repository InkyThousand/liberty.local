<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<?php
Navigation::add(__('Notifications', 'notifications'), 'store', 'notifications', 8);
Action::add('admin_pre_render','NotificationsAdmin::checkSMTPSettings');

class NotificationsAdmin extends Backend 
{
  public static function checkSMTPSettings()
  {
    if (Request::post('action') == 'check_smtp' && Session::exists('user_role') && in_array(Session::get('user_role'), array('admin')))
    {
      $smtp_debug = strtolower(Request::post('smtp_debug')) == 'true';

      $from_address = Request::post('from_address');
      $from_name = Request::post('from_name');

      $recipient_address = Request::post('recipient_address');

      $use_smtp = strtolower(Request::post('use_smtp')) == 'true';
      $smtp_host = Request::post('smtp_host');
      $smtp_port = Request::post('smtp_port');
      $smtp_auth_username = Request::post('smtp_auth_username');
      $smtp_auth_password = Request::post('smtp_auth_password');

      echo Html::toText('Sending message from ' . $from_name . '<' . $from_address . '>' . ' to <' . $recipient_address . '> (using debug: ' . ($smtp_debug ? 'TRUE' : 'FALSE') . ')...') .
           Html::br(2);

      try
      {
        $phpMailer = new PHPMailer(true);
        $phpMailer->IsHTML(false);

        if ($use_smtp)
        {
          $phpMailer->IsSMTP();
          $phpMailer->SMTPDebug = $smtp_debug ? 4 : 0;

          $phpMailer->Host = $smtp_host;
          $phpMailer->Port = $smtp_port;

          if (Valid::hasValue($smtp_auth_username))
          {
            $phpMailer->SMTPAuth = true;
            $phpMailer->Username = $smtp_auth_username;
            $phpMailer->Password = $smtp_auth_password;
          }
        }

        $phpMailer->SetFrom($from_address, $from_name);
        $phpMailer->AddAddress($recipient_address);
        $phpMailer->Subject = 'Test message - do not reply';
        $phpMailer->Body = 'Test message body';
        $phpMailer->Send();

        echo Html::br(2);
        echo Html::toText(__('Message was sent successfully.', 'notifications'));
        echo Html::br(2);
      }
      catch (Exception $e)
      {
        echo Html::br(2);
        echo Html::toText(__('Error sending message: ', 'notifications') . $e->getMessage());
        echo Html::br(2);
      }
      Request::shutdown();
    }
  }

  public static function main() 
  {
    if (Session::exists('user_role') && in_array(Session::get('user_role'), array('admin'))) 
    {
      $notifications_options_tbl = new Table('notifications');
      $notifications_options = $notifications_options_tbl->select(null, null);

      $tab = strtolower(trim(Request::get('tab')));

      switch ($tab)
      {
        case 'dealer':

             if (Request::post('edit_settings')) 
             {
               if (Security::check(Request::post('csrf'))) 
               {
                 $dealer_allow = Valid::hasValue(Request::post('dealer_allow'));

                 $dealer_to_name = Request::post('dealer_to_name');
                 $dealer_to_address = Request::post('dealer_to_address');
                 $dealer_subject = Request::post('dealer_subject');
                 $dealer_body = Request::post('dealer_body');

                 $notifications_options_tbl->update(1, array('dealer_allow' => $dealer_allow,
                                                        'dealer_to_name' => $dealer_to_name,
                                                        'dealer_to_address'=> $dealer_to_address,
                                                        'dealer_subject'=> $dealer_subject
                                                       ));

                 file_put_contents(STORAGE . DS . 'templates' . DS . 'dealer.txt', $dealer_body);

                 Notification::set('success', __('Your changes have been saved.', 'notifications'));

                 Request::redirect('index.php?id=notifications&tab=dealer');
               }
               else
               {
                 die('csrf detected!');
               }
             }
             else
             {
               $dealer_allow = (bool)$notifications_options['dealer_allow'];

               $dealer_to_name = $notifications_options['dealer_to_name'];
               $dealer_to_address = $notifications_options['dealer_to_address'];
               $dealer_subject = $notifications_options['dealer_subject'];

               $dealer_body = file_get_contents(STORAGE . DS . 'templates' . DS . 'dealer.txt');

               View::factory('store/notifications/views/backend/index')
                     ->assign('tab', $tab)
                     ->assign('dealer_allow', $dealer_allow)
                     ->assign('dealer_to_name', $dealer_to_name)
                     ->assign('dealer_to_address', $dealer_to_address)
                     ->assign('dealer_subject', $dealer_subject)
                     ->assign('dealer_body', $dealer_body)
                     ->display();

             }
             break;

        case 'customer':

             if (Request::post('edit_settings')) 
             {
               if (Security::check(Request::post('csrf'))) 
               {
                 $customer_allow = Valid::hasValue(Request::post('customer_allow'));

                 $customer_subject = Request::post('customer_subject');
                 $customer_body = Request::post('customer_body');

                 $notifications_options_tbl->update(1, array('customer_allow' => $customer_allow,
                                                             'customer_subject'=> $customer_subject
                                                            ));

                 file_put_contents(STORAGE . DS . 'templates' . DS . 'customer.txt', $customer_body);

                 Notification::set('success', __('Your changes have been saved.', 'notifications'));

                 Request::redirect('index.php?id=notifications&tab=customer');
               }
               else
               {
                 die('csrf detected!');
               }
             }
             else
             {
               $customer_allow = (bool)$notifications_options['customer_allow'];
               $customer_subject = $notifications_options['customer_subject'];

               $customer_body = file_get_contents(STORAGE . DS . 'templates' . DS . 'customer.txt');

               View::factory('store/notifications/views/backend/index')
                     ->assign('tab', $tab)
                     ->assign('customer_allow', $customer_allow)
                     ->assign('customer_subject', $customer_subject)
                     ->assign('customer_body', $customer_body)
                     ->display();

             }
             break;

        default:

             $tab = 'settings';

             if (Request::post('edit_settings')) 
             {
               if (Security::check(Request::post('csrf'))) 
               {
                 $use_html = Valid::hasValue(Request::post('use_html'));

                 $use_smtp = Valid::hasValue(Request::post('use_smtp'));
                 $smtp_host = Request::post('smtp_host');
                 $smtp_port = Request::post('smtp_port');
                 $smtp_auth_username = Request::post('smtp_auth_username');
                 $smtp_auth_password = Request::post('smtp_auth_password');

                 $from_name = Request::post('from_name');
                 $from_address = Request::post('from_address');

                 $notifications_options_tbl->update(1, array('use_html' => $use_html,
                                                             'use_smtp' => $use_smtp,
                                                             'smtp_host' => $smtp_host,
                                                             'smtp_port' => $smtp_port,
                                                             'smtp_auth_username' => $smtp_auth_username,
                                                             'smtp_auth_password' => $smtp_auth_password,
                                                             'from_name' => $from_name,
                                                             'from_address'=> $from_address
                                                            ));

                 Notification::set('success', __('Your changes have been saved.', 'notifications'));

                 Request::redirect('index.php?id=notifications');
               }
               else
               {
                 die('csrf detected!');
               }
             }
             else
             {
               $use_html = (bool)$notifications_options['use_html'];

               $use_smtp = (bool)$notifications_options['use_smtp'];
               $smtp_host = $notifications_options['smtp_host'];
               $smtp_port = $notifications_options['smtp_port'];
               $smtp_auth_username = $notifications_options['smtp_auth_username'];
               $smtp_auth_password = $notifications_options['smtp_auth_password'];

               $from_name = $notifications_options['from_name'];
               $from_address = $notifications_options['from_address'];

               View::factory('store/notifications/views/backend/index')
                     ->assign('tab', $tab)
                     ->assign('use_html', $use_html)
                     ->assign('use_smtp', $use_smtp)
                     ->assign('smtp_host', $smtp_host)
                     ->assign('smtp_port', $smtp_port)
                     ->assign('smtp_auth_username', $smtp_auth_username)
                     ->assign('smtp_auth_password', $smtp_auth_password)
                     ->assign('from_name', $from_name)
                     ->assign('from_address', $from_address)
                     ->display();
             }
             break;
      }
    } 
    else 
    {
      Request::redirect('index.php?id=users&action=edit&user_id='.Session::get('user_id'));
    }
  }
}
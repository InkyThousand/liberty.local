<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<?php
Navigation::add(__('Payment', 'payment'), 'store', 'payment', 7);

class PaymentAdmin extends Backend 
{
  public static function main() 
  {
    if (Session::exists('user_role') && in_array(Session::get('user_role'), array('admin'))) 
    {
      $tab = strtolower(trim(Request::get('tab')));
      $carrier = strtolower(trim(Request::get('carrier')));

      switch ($tab)
      {
        case 'credentials':

               $payment_credentials_tbl = new Table('payment_credentials');

               $sandbox_credentials = $payment_credentials_tbl->select('[mode="sandbox"]', null);
               $real_credentials = $payment_credentials_tbl->select('[mode="real"]', null);

               if (Request::post('edit_settings'))
               {
                 if (Security::check(Request::post('csrf'))) 
                 {
                   $sandbox_username = Request::post('sandbox_username');
                   $sandbox_password = Request::post('sandbox_password');
                   $sandbox_signature = Request::post('sandbox_signature');
                   $sandbox_version = Request::post('sandbox_version');

                   $real_username = Request::post('real_username');
                   $real_password = Request::post('real_password');
                   $real_signature = Request::post('real_signature');
                   $real_version = Request::post('real_version');

                   $mode = Request::post('paypal_mode') == 'sandbox' ? 'sandbox' : 'real';

                   $payment_credentials_tbl->updateWhere('[mode="sandbox"]', array('username' => $sandbox_username,
                                                                                   'password' => $sandbox_password,
                                                                                   'signature' => $sandbox_signature,
                                                                                   'version' => $sandbox_version,
                                                                                   'active' => $mode == 'sandbox'
                                                                                  ));

                   $payment_credentials_tbl->updateWhere('[mode="real"]', array('username' => $real_username,
                                                                                'password' => $real_password,
                                                                                'signature' => $real_signature,
                                                                                'version' => $real_version,
                                                                                'active' => $mode == 'real'
                                                                               ));

                   Notification::set('success', __('Your changes have been saved.', 'checkout'));

                   Request::redirect('index.php?id=payment&tab=credentials');
                 }
                 else
                 {
                   die('csrf detected!');
                 }
               }
               else
               {
                 $sandbox_username = $sandbox_credentials['username'];
                 $sandbox_password = $sandbox_credentials['password'];
                 $sandbox_signature = $sandbox_credentials['signature'];
                 $sandbox_version = $sandbox_credentials['version'];

                 $real_username = $real_credentials['username'];
                 $real_password = $real_credentials['password'];
                 $real_signature = $real_credentials['signature'];
                 $real_version = $real_credentials['version'];

                 $mode = (bool)$sandbox_credentials['active'] ? 'sandbox' : 'real';
               }

               View::factory('store/payment/views/backend/index')
                     ->assign('tab', $tab)
                     ->assign('sandbox_username', $sandbox_username)
                     ->assign('sandbox_password', $sandbox_password)
                     ->assign('sandbox_signature', $sandbox_signature)
                     ->assign('sandbox_version', $sandbox_version)
                     ->assign('real_username', $real_username)
                     ->assign('real_password', $real_password)
                     ->assign('real_signature', $real_signature)
                     ->assign('real_version', $real_version)
                     ->assign('mode', $mode)
                     ->display();

               break;

        default:
               $tab = 'settings';

               $payment_options_tbl = new Table('payment');
               $payment_options = $payment_options_tbl->select(null, null);

               if (Request::post('edit_settings')) 
               {
                 if (Security::check(Request::post('csrf'))) 
                 {
                   $payment_paypal = Valid::hasValue(Request::post('payment_paypal'));
                   $payment_credit_card = Valid::hasValue(Request::post('payment_credit_card'));

                   $payment_options_tbl->update(1, array('payment_paypal' => $payment_paypal,
                                                         'payment_credit_card' => $payment_credit_card
                                                        ));

                   Notification::set('success', __('Your changes have been saved.', 'checkout'));

                   Request::redirect('index.php?id=payment&tab=settings');
                 }
                 else
                 {
                   die('csrf detected!');
                 }
               }
               else
               {
                 $payment_paypal = (bool)$payment_options['payment_paypal'];
                 $payment_credit_card = (bool)$payment_options['payment_credit_card'];
               }

               View::factory('store/payment/views/backend/index')
                     ->assign('tab', $tab)
                     ->assign('payment_paypal', $payment_paypal)
                     ->assign('payment_credit_card', $payment_credit_card)
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
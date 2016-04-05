<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<?php
Navigation::add(__('Dealership', 'dealership'), 'store', 'dealership', 2);

class DealershipAdmin extends Backend 
{
  public static function main() 
  {
    if (Session::exists('user_role') && in_array(Session::get('user_role'), array('admin'))) 
    {
      $dealership_options_tbl = new Table('dealership');
      $dealership_options = $dealership_options_tbl->select(null, null);

      if (Request::post('edit_settings')) 
      {
        if (Security::check(Request::post('csrf'))) 
        {
          $ezparts_url = Request::post('ezparts_url');
          $partner_name = Request::post('partner_name');
          $passkey_name = Request::post('passkey_name');
          $passkey_value = Request::post('passkey_value');
          $dealer_account = Request::post('dealer_account');
          $doorback_page = Request::post('doorback_page');

          $dealership_options_tbl->update(1, array('ezparts_url' => $ezparts_url,
                                                   'partner_name' => $partner_name,
                                                   'passkey_name' => $passkey_name,
                                                   'passkey_value' => $passkey_value,
                                                   'dealer_account' => $dealer_account,
                                                   'doorback_page' => $doorback_page));
           
          Notification::set('success', __('Your changes have been saved', 'dealership'));

          Request::redirect('index.php?id=dealership');
        }
        else
        {
          die('csrf detected!');
        }
      }

      $ezparts_url = $dealership_options['ezparts_url'];
      $partner_name = $dealership_options['partner_name'];
      $passkey_name = $dealership_options['passkey_name'];
      $passkey_value = $dealership_options['passkey_value'];
      $dealer_account = $dealership_options['dealer_account'];
      $doorback_page = $dealership_options['doorback_page'];

      View::factory('store/dealership/views/backend/index')
            ->assign('ezparts_url', $ezparts_url)
            ->assign('partner_name', $partner_name)
            ->assign('passkey_name', $passkey_name)
            ->assign('passkey_value', $passkey_value)
            ->assign('dealer_account', $dealer_account)
            ->assign('doorback_page', $doorback_page)
            ->display();
    } 
    else 
    {
      Request::redirect('index.php?id=users&action=edit&user_id='.Session::get('user_id'));
    }
  }
}
<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<?php
Plugin::register( __FILE__,
                __('Dealership', 'dealership'),
                __('Dealership plugin', 'dealership'),
                '1.0.0',
                'razorolog',
                '',
                null,
                'store');

Plugin::Admin('dealership', 'store');

class Dealership
{
  public static function getDealershipLinkParams()
  {
    $dealership_options_tbl = new Table('dealership');
    $dealership_options = $dealership_options_tbl->select(null, null);

    return array('url' => $dealership_options['ezparts_url'],
                 'sysname' => $dealership_options['partner_name'],
                 'passkey_name' => $dealership_options['passkey_name'],
                 'passkey_value' => $dealership_options['passkey_value'],
                 'company' => $dealership_options['dealer_account'],
                 'doorback' => Site::url() . $dealership_options['doorback_page'],
                 'sendconfirmation' => 'false',
                 'sendnotification' => 'false'
                );
  }
}
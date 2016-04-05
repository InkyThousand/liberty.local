<?php
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', rtrim(dirname(__FILE__), '\\/'));
define('BACKEND', false);
define('MONSTRA_ACCESS', true);
require_once ROOT. DS . 'engine'. DS . '_init.php';
if ('on' == Option::get('maintenance_status'))
{
  if (!((Session::exists('user_role')) and (Session::get('user_role') == 'admin' or Session::get('user_role') == 'editor')))
  {
    die (Text::toHtml(Option::get('maintenance_message')));
  }
}
Action::run('frontend_pre_render');
require MINIFY . DS . 'theme.' . Site::theme() . '.' . Site::template() . '.template.php';
Action::run('frontend_post_render');
ob_end_flush();
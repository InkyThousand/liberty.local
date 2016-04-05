<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<h2><?php echo __('MySQL Settings', 'mysql') ?></h2>
<br/>
<?php 
 if (Notification::get('success')) Alert::success(Notification::get('success'));
 if (Notification::get('error')) Alert::error(Notification::get('error'));
    
 echo Form::open().
      Form::hidden('csrf', Security::token()).
      Form::label('mysql_host', __('Hostname', 'mysql')).
      Form::input('mysql_host', $host, array('class' => 'span3', 'id' => 'mysql_host')).

      Form::label('mysql_user', __('User', 'mysql')).
      Form::input('mysql_user', $user, array('class' => 'span3', 'id' => 'mysql_user')).

      Form::label('mysql_password', __('Password', 'mysql')).
      Form::input('mysql_password', $password, array('class' => 'span3', 'id' => 'mysql_password')).

      Form::label('mysql_database', __('Database', 'mysql')).
      Form::input('mysql_database', $database, array('class' => 'span3', 'id' => 'mysql_database')).

      Html::br(2).

      Form::submit('edit_settings', __('Save', 'mysql'), array('class' => 'btn')).Html::nbsp(2).
      Form::submit('check_connection', __('Check connection', 'mysql'), array('class' => 'btn')).
      Form::close();
?>
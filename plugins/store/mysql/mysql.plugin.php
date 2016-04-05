<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<?php
Plugin::register(__FILE__,
                __('MySQL', 'mysql'),
                __('MySQL connection settings plugin', 'mysql'),
                '1.0.0',
                'razorolog',
                '',
                null,
                'store');

Plugin::Admin('mysql', 'store');

MySQL::init();

class MySQL
{
  protected static $instance = null;
  private static $link = null;
  private static $connected = false;
  private static $host = null;
  private static $user = null;
  private static $password = null;
  private static $database = null;

  protected function __clone() {
  }

  protected function __construct()
  {
    $mysql_options_tbl = new Table('mysql');
    $mysql_options = $mysql_options_tbl->select(null, null);

    self::$host = $mysql_options['host'];
    self::$user = $mysql_options['user'];
    self::$password = $mysql_options['password'];
    self::$database = $mysql_options['database'];
  }

  function __destruct()
  {
    if (self::$link) @mysqli_close(self::$link);
  }

  public static function init()
  {
    if (!isset(self::$instance)) 
     self::$instance = new MySQL();
    return self::$instance;
  }

  private static function connect()
  {
    try
    {
      self::$link = @mysqli_init();

      if (!self::$link)
      {
        throw new Exception();
      }

      if (!@mysqli_options(self::$link, MYSQLI_INIT_COMMAND, 'SET AUTOCOMMIT = 1'))
      {
        throw new Exception();
      }

      if (!@mysqli_options(self::$link, MYSQLI_INIT_COMMAND, 'SET NAMES \'utf8\''))
      {
        throw new Exception();
      }

      if (!@mysqli_options(self::$link, MYSQLI_INIT_COMMAND, 'SET CHARACTER SET \'utf8\''))
      {
        throw new Exception();
      }
    }
    catch(Exception $e)
    {
      throw new Exception('MySQL initialization failed');
    }

    if (@mysqli_real_connect(self::$link, self::$host, self::$user, self::$password, self::$database) === false)
    {
      throw new Exception('(' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
    }

    self::$connected = true;
  }

  public static function query($query)
  {
    $query = (string)$query;

    if (!self::$connected) self::connect();

    if (($mysqli_result = mysqli_query(self::$link, $query)) === false)
    {
      throw new Exception('(' . mysqli_errno(self::$link) . ') ' . mysqli_error(self::$link));
    }
    return $mysqli_result;
  }

  // returns NULL if row is absent
  public static function selectRow($query)
  {
    $mysqli_result = self::query($query);
    $result = mysqli_fetch_array($mysqli_result, MYSQLI_ASSOC);
    self::free($mysqli_result);
    return $result;
  }

  // returns NULL if row is absent or cell value is null
  public static function selectCell($query)
  {
    $mysqli_result = self::query($query);
    $result = mysqli_fetch_array($mysqli_result, MYSQLI_NUM);
    self::free($mysqli_result);
    return $result[0];
  }

  public static function fetch($result)
  {
    return mysqli_fetch_array($result, MYSQLI_ASSOC);
  }

  public static function free($result)
  {
    mysqli_free_result($result);
  }

  public static function rowCount($result)
  {
    return mysqli_num_rows($result);
  }

  public static function getInsertId()
  {
    return mysqli_insert_id(self::$link);
  }

  public static function escapeString($value)
  {
    $value = (string)$value;
    if (!self::$connected) self::connect();
    return mysqli_real_escape_string(self::$link, $value);
  }

  public static function startTransaction()
  {
    self::query('START TRANSACTION');
  }

  public static function commitTransaction()
  {
    self::query('COMMIT');
  }

  public static function rollbackTransaction()
  {
    self::query('ROLLBACK');
  }
}
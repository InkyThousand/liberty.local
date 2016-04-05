<?php
ini_set('max_execution_time', 0);
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', rtrim(str_replace(array('distributors'), array(''), dirname(__FILE__)), '\\/'));
define('BACKEND', true);
define('MONSTRA_ACCESS', true);
require_once ROOT. DS .'engine'. DS .'_init.php';

$distributors_filename = "S:\\EzParts5\\parts.libertypumps.com\\Database\\EZPartsUsers.xml";

//$distributors_filename = '2013-09-23-01-00-01.xml';

try
{
  if (file_exists($distributors_filename))
  {
    $xml_data = @file_get_contents($distributors_filename);
    if (Text::trimStr($xml_data) != '')
    {
      Distributors::importDistributors($xml_data);
      $result = @unlink($distributors_filename);
      if ($result === false)
      {
        exit(2);
      }
      echo 'Imported successfully';
    }
    else
    {
      echo 'Nothing to import';
    }
  }
  else
  {
    echo 'Nothing to import';
  }
}
catch(Exception $e)
{
  echo $e->getMessage();
  exit(1);
}
?>
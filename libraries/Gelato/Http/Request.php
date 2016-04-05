<?php

/**
 * Gelato Library
 *
 * This source file is part of the Gelato Library. More information,
 * documentation and tutorials can be found at http://gelato.monstra.org
 *
 * @package     Gelato
 *
 * @author      Romanenko Sergey / Awilum <awilum@msn.com>
 * @copyright   2012-2013 Romanenko Sergey / Awilum <awilum@msn.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Request
{
    /**
     * Protected constructor since this is a static class.
     *
     * @access  protected
     */
    protected function __construct()
    {
        // Nothing here
    }

    /**
     * Redirects the browser to a page specified by the $url argument.
     *
     *  <code>
     *      Request::redirect('test');
     *  </code>
     *
     * @param string  $url    The URL
     * @param integer $status Status
     * @param integer $delay  Delay
     */
    public static function redirect($url, $status = 302, $delay = 0, $shutdown = true)
    {
        // Redefine vars
        $url    = (string) $url;
        $status = (int) $status;

        $messages = array();
        $messages[301] = '301 Moved Permanently';
        $messages[302] = '302 Found';

        if (headers_sent()) 
        {
          if ($delay)
          {
            echo '<script type="text/javascript">function redir() { document.location = \'' . $url . '\'; } setTimeout(redir, ' . $delay .'); </script>';
          }
          else
          {
            echo '<script type="text/javascript">document.location.href = "' . $url . '";</script>';
          }
        } 
        else 
        {
            if ($delay)
            {
              $delay = $delay / 1000;
              Request::setHeaders('Refresh: ' . $delay . '; URL=' . $url);
            }
            else
            {
              Request::setHeaders('HTTP/1.1 ' . $status . ' ' . Arr::get($messages, $status, 302));
              Request::setHeaders("Location: $url");
            }
        }

        if ($shutdown)
        {
            Request::shutdown();
        }

    }

    /**
     * Set one or multiple headers.
     *
     *  <code>
     *      Request::setHeaders('Location: http://site.com/');
     *  </code>
     *
     * @param mixed $headers String or array with headers to send.
     */
    public static function setHeaders($headers)
    {
        // Loop elements
        foreach ((array) $headers as $header) {

            // Set header
            header((string) $header);

        }

    }

    /**
     * Request
     *
     *  <code>
     *      $action = Request::request('action');
     *  </code>
     *
     * @param string $key Key
     * @param mixed
     */
    public static function request($key)
    {
        return Arr::get($_REQUEST, $key);
    }

    /**
     * Get
     *
     *  <code>
     *      $action = Request::get('action');
     *  </code>
     *
     * @param string $key Key
     * @param mixed
     */
    public static function get($key)
    {
        return Arr::get($_GET, $key);
    }

    /**
     * Post
     *
     *  <code>
     *      $login = Request::post('login');
     *  </code>
     *
     * @param string $key Key
     * @param mixed
     */
    public static function post($key)
    {
        return Arr::get($_POST, $key);
    }

    /**
     * Returns whether this is an ajax request or not
     *
     *  <code>
     *      if (Request::isAjax()) {
     *          // do something...
     *      }
     *  </code>
     *
     * @return boolean
     */

    public static function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    /**
     * Terminate request
     *
     *  <code>
     *      Request::shutdown();
     *  </code>
     *
     */
    public static function shutdown()
    {
        exit(0);
    }

}

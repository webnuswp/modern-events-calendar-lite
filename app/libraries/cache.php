<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC Cache class.
 * @author Webnus <info@webnus.net>
 */
class MEC_cache
{
    protected static $instance = null;
    protected static $cache = array();
    protected static $enabled = true;

    /**
     * Constructor method
     * @author Webnus <info@webnus.net>
     */
    private function __construct()
    {
    }

    public function __clone()
    {
    }

    public function __wakeup()
    {
    }

    public static function getInstance()
    {
        // Get an instance of Class
        if(is_null(self::$instance)) self::$instance = new self();

        // Return the instance
        return self::$instance;
    }

    public static function set($key, $value)
    {
        self::$cache[$key] = $value;
    }

    public static function has($key)
    {
        return isset(self::$cache[$key]);
    }

    public static function get($key)
    {
        return (isset(self::$cache[$key]) ? self::$cache[$key] : NULL);
    }

    public static function delete($key)
    {
        if(MEC_cache::has($key))
        {
            unset(self::$cache[$key]);
            return true;
        }

        return false;
    }

    public function disable()
    {
        return self::$enabled = false;
    }

    public function enable()
    {
        return self::$enabled = true;
    }

    public function rememberOnce($key, $callback)
    {
        if($this->has($key) and self::$enabled) $data = $this->get($key);
        else
        {
            $data = call_user_func($callback);
            $this->set($key, $data);
        }

        return $data;
    }
}
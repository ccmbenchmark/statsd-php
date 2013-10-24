<?php
/**
 * StatsD client minimalistic implementation
 */
class statsd {
    /**
     * @var string A namespace to prepend to any key
     */
    public static $namespace;
    /**
     * @var float The default sending rate
     */
    public static $rate = 1;
    /**
     * @var string The statsd server name
     */
    public static $host = '127.0.0.1';
    /**
     * @var integer The StatsD connection port
     */
    public static $port = 8125;
    /**
     * @var array Buffer of measures
     */
    private static $data = array();
    /**
     * Increment the specified key (sends a hit)
     * @param $key string
     * @param $rate float [optionnal]
     */
    public static function increment($key, $rate = null) {
        self::count($key, 1, $rate);
    }
    /**
     * Decrement the specified key (-1)
     * @param $key string
     * @param $rate float [optionnal]
     */
    public static function decrement($key, $rate = null) {
        self::count($key, -1, $rate);
    }
    /**
     * Sends a counting value
     * @param $key string
     * @param $value integer
     * @param $rate float [optionnal]
     */
    public static function count($key, $value, $rate = null) {
        self::send('c', $key, $value, $rate);
    }
    /**
     * Sends a duration (in ms)
     * @param $key string
     * @param $value integer
     * @param $rate float [optionnal]
     */
    public static function time($key, $value, $rate = null) {
        if (is_float($value)) $value = round($value * 1000);
        self::send('ms', $key, (int)$value, $rate);
    }
    /**
     * Executes the specified closure and calculate the memory usage and its duration
     * @param $key string
     * @param $closure callable
     * @param [...$args mixed] List of arguments used to call the specified $closure
     */
    public static function profile() {
        $args = func_get_args();
        $key = array_shift($args);
        $closure = array_shift($args);
        $start = microtime(true);
        $mem = memory_get_usage();
        $result = call_user_func_array($closure, $args);
        $mem = memory_get_usage() - $mem;
        self::count( $key . '.memory', memory_get_usage() - $mem);
        self::time( $key . '.duration', round((microtime(true) - $start) * 1000));
        return $result;
    }
    /**
     * Force to flush collected data to the server
     */
    public static function flush() {
        if (!empty(self::$data)) {
            static $socket;
            if (!$socket) $socket = fsockopen('udp://' . self::$host, self::$port);
            fwrite($socket, implode(null, self::$data));
            self::$data = array();
        }
    }
    /**
     * Helper for sending data
     */
    private static function send($type, $key, $value, $rate = null) {
        if (is_null($rate)) $rate = self::$rate;
        if ($rate < 1 && mt_rand(0, 100) / 100 > $rate) return;
        if (!empty(self::$namespace)) {
            $key = self::$namespace . '.' . $key; 
        }
        $message = sprintf('%s:%d|%s', $key, $value, $type);
        if ($rate < 1) $message .= '|@' . $rate;
        self::$data[] = $message;
        if (count(self::$data) > 20) self::flush();
    }
}
if(defined('STATSD_NAMESPACE')) statsd::$namespace = STATSD_NAMESPACE; 
if(defined('STATSD_RATE')) statsd::$rate = STATSD_RATE; 
if(defined('STATSD_HOST')) statsd::$host = STATSD_HOST; 
if(defined('STATSD_PORT')) statsd::$port = STATSD_PORT; 
register_shutdown_function(array('statsd', 'flush'));
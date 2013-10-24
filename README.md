StatsD for PHP
==============

It'a minimalistic implementation of a StatsD client in PHP

# Features

* Lightweight (100 LoC approx)
* Common functions : Increment, Decrement, Counter, Timer
* Callback profiler
* Handles rates (avoid sending too much data to the server)
* Handles namespacing
* Regroups UDP packets (send 20 measures at once)
* Can avoids class autoload when it's configured
* Easy to install with composer (or your custom loading system - needs just 1 file)

# Usage

```php
<?php
require_once 'statsd.php';
statsd::$host = 'localhost';
statsd::profile(
    'test',
    function() {
        for($i = 0; $i = 10; $i ++ ) {
            statsd::increment('a');
            statsd::increment('b');
        }
        for($i = 0; $i = 5; $i ++ ) {
            statsd::decrement('b');
        }
        statsd::count('hello', 10);
        statsd::time('duration', 50);
    }
);
```

# Documentation :

## Configuration

### statsd::$namespace (default `empty`)
A namespace to prepend to any key

### statsd::$rate (default 1)
The default sending rate

### statsd::$host (default 127.0.0.1)
The statsd server name

### statsd::$port (default 8125)
The StatsD connection port

--- 

*TIP :* You can also define STATSD_`key` to avoid autoload (if not always used)

## Functions

### statsd::increment($key, $rate = null)
Increment the specified key (sends a hit)

### statsd::decrement($key, $rate = null)
Decrement the specified key (-1)

### statsd::count($key, $value, $rate = null)
Sends a counting value

### statsd::time($key, $value, $rate = null)
Sends a duration (in ms)

### statsd::profile($key, $callback [,$args...])
Executes the specified closure and calculate the memory usage and its duration

### statsd::flush()
Force to flush the current buffer of data
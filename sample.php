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

<?php

namespace Rooty\Support;

class Env {
    /**
     * Get the value of an environment variable.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        if (! function_exists('env')) {
            return null;
        }

        return env($key, $default);
    }
}

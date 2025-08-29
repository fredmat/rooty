<?php

namespace Rooty\Foundation\Bootstrap;

use Rooty\Contracts\Foundation\Application;

class BootProviders
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Rooty\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app): void
    {
        $app->boot();
    }
}

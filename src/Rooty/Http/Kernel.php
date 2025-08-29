<?php

namespace Rooty\Http;

use Exception;
// use Throwable;
// use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Facade;
use Rooty\Contracts\Foundation\Application;
use Rooty\Contracts\Http\Kernel as KernelContract;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Kernel implements KernelContract
{
    /**
     * The application instance.
     *
     * @var \Rooty\Contracts\Foundation\Application
     */
    protected Application $app;

    /**
     * The bootstrap classes for the application.
     *
     * @var string[]
     */
    protected array $bootstrappers = [
        \Rooty\Foundation\Bootstrap\LoadConfiguration::class,
        \Rooty\Foundation\Bootstrap\RegisterProviders::class,
        \Rooty\Foundation\Bootstrap\BootProviders::class,
    ];

    /**
     * Create a new HTTP kernel instance.
     *
     * @param  \Rooty\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Bootstrap the application for handling HTTP requests.
     *
     * @return void
     */
    public function bootstrap()
    {
        if (! $this->app->hasBeenBootstrapped()) {
            $this->app->bootstrapWith($this->bootstrappers());
        }
    }

    /**
     * Get the bootstrap classes for the application.
     *
     * @return array
     */
    protected function bootstrappers()
    {
        return $this->bootstrappers;
    }

    /**
     * Handle an incoming HTTP request.
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     * 
     * @throws \Exception
     */
    public function handle($request)
    {
        try {
            $this->app->instance('request', $request);

            Facade::clearResolvedInstance('request');

            $this->bootstrap();

            return $this->dispatchRequest($request);
        } catch (Exception $e) {
            // dump($e);
            throw new Exception($e->getMessage());

            // $this->reportException($e);

            // return $this->renderException($request, $e);
        }
    }

    /**
     * Process the request and generate a response.
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function dispatchRequest($request)
    {
        return new SymfonyResponse;
    }

    /**
     * Terminate the request lifecycle.
     *
     * @param  \Symfony\Component\HttpFoundation\Request    $request
     * @param  \Symfony\Component\HttpFoundation\Response   $response
     * @return void
     */
    public function terminate($request, $response)
    {
        $this->app->terminate();
    }

    // /**
    //  * Report an exception to the exception handler.
    //  *
    //  * @param  \Throwable  $e
    //  * @return void
    //  */
    // protected function reportException(Throwable $e)
    // {
    //     $this->app[ExceptionHandler::class]->report($e);
    // }

    // /**
    //  * Render an exception into an HTTP response.
    //  *
    //  * @param  \Symfony\Component\HttpFoundation\Request  $request
    //  * @param  \Throwable  $e
    //  * @return \Symfony\Component\HttpFoundation\Response
    //  */
    // protected function renderException($request, Throwable $e)
    // {
    //     return $this->app[ExceptionHandler::class]->render($request, $e);
    // }

    /**
     * Get the Rooty application instance.
     *
     * @return \Rooty\Contracts\Foundation\Application
     */
    public function getApplication()
    {
        return $this->app;
    }

    /**
     * Set the Rooty application instance.
     *
     * @param  \Rooty\Contracts\Foundation\Application  $app
     * @return self
     */
    public function setApplication(Application $app)
    {
        $this->app = $app;

        return $this;
    }
}

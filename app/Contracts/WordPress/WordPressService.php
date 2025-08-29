<?php

namespace App\Contracts\WordPress;

/**
 * Contract for all WordPress services.
 *
 * Every service must provide a boot() method.
 * This method may be empty if the service requires no initialization.
 */
interface WordPressService
{
    /**
     * Optional boot logic for the service.
     *
     * Called automatically from Api::boot().
     *
     * @return void
     */
    public function boot(): void;
}

<?php

namespace App\Services\Auth;

use App\Services\Capabilities;

/**
 * Class Guard
 *
 * Handles basic authentication gatekeeping for WordPress.
 * Can enforce login and delegate capabilities checks.
 *
 * @package App\Services\Auth
 */
class Guard
{
    /**
     * Create a new Guard instance.
     *
     * @param  \App\Services\Capabilities  $caps
     * @param  \App\Services\Auth\Auth  $auth
     * @return void
     */
    public function __construct(
        protected Capabilities $caps,
        protected Auth $auth,
    ) {
        //
    }

    /**
     * Require the user to be authenticated.
     *
     * If the user is not authenticated, they are redirected to the login page.
     *
     * @param  string|null  $redirectUrl  Optional custom redirect URL. Defaults to wp-login.php.
     * @return void
     */
    public function requireAuth(?string $redirectUrl = null): void
    {
        if (! $this->auth->check()) {
            $redirect = $redirectUrl ?? wp_login_url(admin_url());
            wp_safe_redirect($redirect);
            exit;
        }
    }

    // /**
    //  * Require that the current user is authenticated and has a specific capability.
    //  *
    //  * If the user is not authenticated, they will be redirected.
    //  * If the user lacks the required capability, the request will be denied.
    //  *
    //  * @param  string  $cap  The capability to check (e.g., 'edit_posts').
    //  * @param  string|null  $redirectUrl  Optional URL to redirect to if unauthenticated.
    //  * @return void
    //  */
    // public function requireCapability(string $cap, ?string $redirectUrl = null): void
    // {
    //     $this->requireAuth($redirectUrl);

    //     if (! $this->caps->currentUserCan($cap)) {
    //         $this->denyIfNotAllowed(false, __('You do not have the necessary permissions.', 'rooty'));
    //     }
    // }
}

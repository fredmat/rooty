<?php

namespace App\Services\WordPress;

use Illuminate\Support\Facades\View;
use App\Contracts\WordPress\WordPressService;

/**
 * Class Conflicts
 *
 * This service handles conflicts with bundled or restricted plugins.
 * It ensures that specific plugins (e.g. ACF) are not available in the
 * plugins list, cannot be activated manually, and displays a notice
 * when an activation attempt is blocked.
 *
 * @package App\Services\WordPress
 */
class Conflicts implements WordPressService
{
    /**
     * Return the list of blocked plugins with translated messages.
     *
     * Key   = plugin file path relative to the plugins dir (e.g. "advanced-custom-fields/acf.php").
     * Value = reason message to display when activation is blocked.
     *
     * @return array<string,string>
     */
    protected function blockedPlugins(): array
    {
        return [
            'advanced-custom-fields/acf.php' => __('The Advanced Custom Fields plugin cannot be activated because Rooty already bundles it.', 'rooty'),
        ];
    }

    /**
     * Boot conflict detection and blocking logic.
     *
     * @return void
     */
    public function boot(): void
    {
        // Remove blocked plugins from the plugins list.
        add_filter('all_plugins', function (array $plugins) {
            foreach ($this->blockedPlugins() as $pluginFile => $reason) {
                unset($plugins[$pluginFile]);
            }
            return $plugins;
        });

        // Register activation hooks for each blocked plugin.
        foreach (array_keys($this->blockedPlugins()) as $pluginFile) {
            add_action("activate_{$pluginFile}", function () use ($pluginFile) {
                $this->handleBlockedActivation($pluginFile);
            });
        }

        // Display admin notice if an activation was blocked.
        add_action('admin_notices', function () {
            if ($reason = get_option('acf_activation_blocked')) {
                echo View::make('admin.notice', [
                    'type'        => 'error',
                    'dismissible' => true,
                    'message'     => esc_html($reason),
                ])->render();

                delete_option('acf_activation_blocked');
            }
        });
    }

    /**
     * Handle a blocked plugin activation attempt.
     *
     * @param string $pluginFile
     * @return void
     */
    protected function handleBlockedActivation(string $pluginFile): void
    {
        deactivate_plugins($pluginFile);

        $reason = $this->blockedPlugins()[$pluginFile] ?? __('This plugin cannot be activated.', 'rooty');
        update_option('acf_activation_blocked', $reason);

        wp_safe_redirect(admin_url('plugins.php'));
        exit;
    }
}

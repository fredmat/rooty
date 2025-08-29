<?php

namespace App\Services\ACF;

use Rooty\Foundation\Application;
use App\Services\ACF\Helper;
use RuntimeException;

class ACF
{
    /**
     * Allowed ACF field types.
     *
     * @var array
     */
    protected const FIELD_TYPES = [
        'email',
        'number',
        'password',
        'range',
        'text',
        'textarea',
        'url',
        'button_group',
        'checkbox',
        'radio_button',
        'select',
        'true_false',
        'file',
        'gallery',
        'image',
        'oembed',
        'wysiwyg_editor',
        'color_picker',
        'date_picker',
        'date_time_picker',
        'google_map',
        'icon_picker',
        'time_picker',
        'accordion',
        'clone',
        'flexible_content',
        'group',
        'repeater',
        'tab',
        'link',
        'page_link',
        'post_object',
        'relationship',
        'taxonomy',
        'user',
    ];

    /**
     * ACF constructor.
     *
     * @param \Rooty\Foundation\Application $app
     */
    public function __construct(protected Application $app, public Helper $helper)
    {
        $this->applySettings();

        add_action('acf/init', fn () => $this->addOptionsPage());
        add_action('acf/init', fn () => $this->registerFieldGroups());
    }

    /**
     * Apply the settings defined in the configuration to ACF.
     *
     * @return void
     */
    protected function applySettings(): void
    {
        $settings = (array) $this->app['config']->get('acf.settings', []);

        foreach ($settings as $key => $value) {
            add_filter("acf/settings/{$key}", fn ($v) => $value);
        }
    }

    /**
     * Add options pages to ACF if defined in the configuration.
     *
     * @return void
     */
    protected function addOptionsPage(): void
    {
        if (! function_exists('acf_add_options_page')) {
            return;
        }

        $option_pages = (array) $this->app['config']->get('acf.option_pages', []);

        foreach ($option_pages as $page) {
            if (! empty($page['parent_slug'])) {
                acf_add_options_sub_page($page);
            } else {
                acf_add_options_page($page);
            }
        }
    }

    /**
     * Register ACF field groups from the configuration.
     *
     * @return void
     */
    protected function registerFieldGroups(): void
    {
        if (! function_exists('register_extended_field_group')) {
            return;
        }

        foreach ($this->getFieldGroups() as $filepath) {
            if (! file_exists($filepath)) {
                throw new RuntimeException("Missing ACF field group file: {$filepath}");
            }

            $settings = require $filepath;

            register_extended_field_group($settings);
        }
    }

    /**
     * Get the field groups configuration from the settings.
     *
     * @return array
     */
    protected function getFieldGroups(): array
    {
        return (array) $this->app['config']->get('acf.field_groups', []);
    }

    /**
     * Determine if a given field array represents a valid ACF field.
     *
     * @param  array  $field
     * @return bool
     */
    public function isValidField(array $field): bool
    {
        if (empty($field['key']) || ! is_string($field['key']) || empty($field['name']) || ! is_string($field['name'])) {
            return false;
        }

        return $this->isValidFieldType($field['type'] ?? null);
    }

    /**
     * Determine if a field type is supported.
     *
     * @param  mixed  $type
     * @return bool
     */
    public function isValidFieldType($type): bool
    {
        return in_array((string) $type, self::FIELD_TYPES, true);
    }

    /**
     * Get the allowed ACF field types.
     *
     * @return array
     */
    public function getAllowedFieldTypes(): array
    {
        return self::FIELD_TYPES;
    }
}

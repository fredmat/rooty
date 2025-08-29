<?php

namespace App\Services\ACF;

class Helper
{
    /**
     * Get all field groups based on a filter.
     *
     * @param array $filter
     * @return array
     */
    public function fieldGroups(array $filter = []): array
    {
        return function_exists('acf_get_field_groups')
            ? acf_get_field_groups($filter)
            : [];
    }

    /**
     * Retrieve a single ACF field group by key, ID or name.
     *
     * @param int|string $key The field group key or ID.
     * @return array<string, mixed>|null The field group settings, or null if not found.
     */
    public function fieldGroup(int|string $key): ?array
    {
        if (! function_exists('acf_get_field_group')) {
            return null;
        }

        $group = acf_get_field_group($key);

        return is_array($group) ? $group : null;
    }

    /**
     * Get all fields for a given parent.
     *
     * @param int|string|array $parent
     * @return array
     */
    public function fields(int|string|array $parent): array
    {
        return function_exists('acf_get_fields')
            ? acf_get_fields($parent)
            : [];
    }

    /**
     * Get field object by selector.
     *
     * @param string $selector
     * @param mixed $postId
     * @param bool $formatValue
     * @param bool $loadValue
     * @param bool $escapeHtml
     * @return array|false
     */
    public function fieldObject(string $selector, mixed $postId = false, bool $formatValue = true, bool $loadValue = true, bool $escapeHtml = false): array|false
    {
        return function_exists('get_field_object')
            ? get_field_object($selector, $postId, $formatValue, $loadValue, $escapeHtml)
            : false;
    }

    /**
     * Get all field objects for a specific post.
     *
     * @param mixed $postId
     * @param bool $formatValue
     * @param bool $loadValue
     * @param bool $escapeHtml
     * @return array|false
     */
    public function fieldObjects(mixed $postId = false, bool $formatValue = true, bool $loadValue = true, bool $escapeHtml = false): array|false
    {
        return function_exists('get_field_objects')
            ? get_field_objects($postId, $formatValue, $loadValue, $escapeHtml)
            : false;
    }

    /**
     * Get the value of a field.
     *
     * @param string $selector
     * @param mixed $postId
     * @param bool $formatValue
     * @param bool $escapeHtml
     * @return mixed
     */
    public function field(string $selector, mixed $postId = false, bool $formatValue = true, bool $escapeHtml = false): mixed
    {
        return function_exists('get_field')
            ? get_field($selector, $postId, $formatValue, $escapeHtml)
            : null;
    }

    /**
     * Get all fields and their values for a specific post.
     *
     * @param mixed $postId
     * @param bool $formatValue
     * @param bool $escapeHtml
     * @return array|false
     */
    public function fieldsAll(mixed $postId = false, bool $formatValue = true, bool $escapeHtml = false): array|false
    {
        return function_exists('get_fields')
            ? get_fields($postId, $formatValue, $escapeHtml)
            : false;
    }

    /**
     * Echo the field value safely.
     *
     * @param string $selector
     * @param mixed $postId
     * @param bool $formatValue
     * @return void
     */
    public function theField(string $selector, mixed $postId = false, bool $formatValue = true): void
    {
        if (function_exists('the_field')) {
            the_field($selector, $postId, $formatValue);
        }
    }

    /**
     * Get the current row index within a 'have_rows' loop.
     *
     * @return int
     */
    public function getRowIndex(): int
    {
        if (! function_exists('acf_get_loop') || ! function_exists('acf_get_setting')) {
            return 0;
        }

        $i = acf_get_loop('active', 'i');
        $offset = acf_get_setting('row_index_offset');

        return $offset + $i;
    }

    /**
     * Get the layout of the current row within a 'have_rows' loop.
     *
     * @return string|false
     */
    public function getRowLayout(): string|false
    {
        return self::getRow()['acf_fc_layout'] ?? false;
    }

    /**
     * Get the current row data within a 'have_rows' loop.
     *
     * @param bool $format Whether to format the value.
     * @return array|false
     */
    public function getRow(bool $format = false): array|false
    {
        if (! function_exists('acf_get_loop') || ! function_exists('acf_maybe_get')) {
            return false;
        }

        $loop = acf_get_loop('active');

        if (! $loop) {
            return false;
        }

        $value = acf_maybe_get($loop['value'], $loop['i']);

        return $value ?: false;
    }

    /**
     * Get a sub field object within a 'has_sub_field' loop.
     *
     * @param string  $selector    The field name or key.
     * @param bool    $formatValue Whether to format the value.
     * @param bool    $loadValue   Whether to load the field value.
     * @param bool    $escapeHtml  Whether to escape HTML in the returned value.
     * @return array|false
     */
    public function subFieldObject(string $selector, bool $formatValue = true, bool $loadValue = true, bool $escapeHtml = false): array|false {
        if (! function_exists('get_sub_field_object')) {
            return false;
        }

        return get_sub_field_object($selector, $formatValue, $loadValue, $escapeHtml);
    }

    /**
     * Get the value of a sub field within a 'has_sub_field' loop.
     *
     * @param string  $selector    The field name or key.
     * @param bool    $formatValue Whether to format the value.
     * @param bool    $escapeHtml  Whether to escape HTML in the returned value.
     * @return mixed
     */
    public function subField(string $selector = '', bool $formatValue = true, bool $escapeHtml = false): mixed {
        if (! function_exists('get_sub_field')) {
            return false;
        }

        return get_sub_field($selector, $formatValue, $escapeHtml);
    }

    /**
     * Check if a field (such as Repeater or Flexible Content) has rows to loop through.
     *
     * @param string $selector The field name or key.
     * @param mixed  $postId   The post ID where the value is stored. Defaults to the current post.
     * @return bool
     */
    public function haveRows(string $selector, mixed $postId = false): bool
    {
        if (! function_exists('have_rows')) {
            return false;
        }

        return have_rows($selector, $postId);
    }

    /**
     * Echo the value of a sub field with safe HTML output.
     *
     * @param string $fieldName   The field name.
     * @param bool   $formatValue Whether to format the value before output.
     * @return void
     */
    public function theSubField(string $fieldName, bool $formatValue = true): void
    {
        if (! function_exists('the_sub_field')) {
            return;
        }

        the_sub_field($fieldName, $formatValue);
    }

    /**
     * Add a row of data to a field.
     *
     * @param   string $selector
     * @param   array  $row
     * @param   mixed  $postId
     * @return  bool
     */
    public function addRow(string $selector, array $row = [], mixed $postId = false): bool
    {
        if (! function_exists('add_row')) {
            return false;
        }

        return add_row($selector, $row, $postId);
    }

    /**
     * Add a row of data to a sub field.
     *
     * @param   string $selector
     * @param   array  $row
     * @param   mixed  $postId
     * @return  bool
     */
    public function addSubRow(string $selector, array $row = [], mixed $postId = false): bool
    {
        if (! function_exists('add_sub_row')) {
            return false;
        }

        return add_sub_row($selector, $row, $postId);
    }

    /**
     * Delete a field value from the database.
     *
     * @param   string $selector
     * @param   mixed  $postId
     * @return  bool
     */
    public function deleteField(string $selector, mixed $postId = false): bool
    {
        if (! function_exists('delete_field')) {
            return false;
        }

        return delete_field($selector, $postId);
    }

    /**
     * Delete a row of data from a field.
     *
     * @param   string $selector
     * @param   int    $i
     * @param   mixed  $postId
     * @return  bool
     */
    public function deleteRow(string $selector, int $i = 1, mixed $postId = false): bool
    {
        if (! function_exists('delete_row')) {
            return false;
        }

        return delete_row($selector, $i, $postId);
    }

    /**
     * Delete a sub field value from the database.
     *
     * @param   string $selector
     * @param   mixed  $postId
     * @return  bool
     */
    public function deleteSubField(string $selector, mixed $postId = false): bool
    {
        if (! function_exists('delete_sub_field')) {
            return false;
        }

        return delete_sub_field($selector, $postId);
    }

    /**
     * Delete a sub row of data.
     *
     * @param   string $selector
     * @param   int    $i
     * @param   mixed  $postId
     * @return  bool
     */
    public function deleteSubRow(string $selector, int $i = 1, mixed $postId = false): bool
    {
        if (! function_exists('delete_sub_row')) {
            return false;
        }

        return delete_sub_row($selector, $i, $postId);
    }

    /**
     * Update a field value in the database.
     *
     * @param   string $selector
     * @param   mixed  $value
     * @param   mixed  $postId
     * @return  bool
     */
    public function updateField(string $selector, mixed $value, mixed $postId = false): bool
    {
        if (! function_exists('update_field')) {
            return false;
        }

        return update_field($selector, $value, $postId);
    }

    /**
     * Update a row of data in a field.
     *
     * @param   string $selector
     * @param   int    $i
     * @param   array  $row
     * @param   mixed  $postId
     * @return  bool
     */
    public function updateRow(string $selector, int $i = 1, array $row = [], mixed $postId = false): bool
    {
        if (! function_exists('update_row')) {
            return false;
        }

        return update_row($selector, $i, $row, $postId);
    }

    /**
     * Update a sub field value in the database.
     *
     * @param   string $selector
     * @param   mixed  $value
     * @param   mixed  $postId
     * @return  bool
     */
    public function updateSubField(string $selector, mixed $value, mixed $postId = false): bool
    {
        if (! function_exists('update_sub_field')) {
            return false;
        }

        return update_sub_field($selector, $value, $postId);
    }

    /**
     * Update a sub row of data.
     *
     * @param   string $selector
     * @param   int    $i
     * @param   array  $row
     * @param   mixed  $postId
     * @return  bool
     */
    public function updateSubRow(string $selector, int $i = 1, array $row = [], mixed $postId = false): bool
    {
        if (! function_exists('update_sub_row')) {
            return false;
        }

        return update_sub_row($selector, $i, $row, $postId);
    }

    /**
     * Alias of acf_options_page()->add_page().
     *
     * @param   mixed $page
     * @return  array
     */
    public function addOptionsPage($page = ''): array
    {
        if (! function_exists('acf_options_page')) {
            return [];
        }

        return acf_options_page()->add_page($page);
    }

    /**
     * Alias of acf_options_page()->add_sub_page().
     *
     * @param   mixed $page
     * @return  array
     */
    public function addOptionsSubPage($page = ''): array
    {
        if (! function_exists('acf_options_page')) {
            return [];
        }

        return acf_options_page()->add_sub_page($page);
    }

    /**
     * Alias of acf()->form->functions.
     *
     * @return  void
     */
    public function formHead(): void
    {
        if (! function_exists('acf')) {
            return;
        }

        acf()->form->functions();
    }

    /**
     * Renders an ACF form.
     *
     * @param   array $args
     * @return  void
     */
    public function renderForm(array $args = []): void
    {
        if (! function_exists('acf')) {
            return;
        }

        acf()->form_front->render_form($args);
    }

    /**
     * Registers a block type.
     *
     * @param   array $block
     * @return  array|false
     */
    public function registerBlockType(array $block)
    {
        if (! function_exists('acf_register_block_type')) {
            return false;
        }

        return acf_register_block_type($block);
    }

    /**
     * Registers a form.
     *
     * @param   array $args
     * @return  void
     */
    public function registerForm(array $args): void
    {
        if (! function_exists('acf')) {
            return;
        }

        acf()->form_front->add_form($args);
    }
}

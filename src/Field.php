<?php

namespace Log1x\AcfEditorPalette;

class Field extends \acf_field
{
    use Concerns\Asset;
    use Concerns\Palette;

    /**
     * The default field values.
     *
     * @var array
     */
    public $defaults = [
        'default_value' => null,
        'allowed_colors' => [],
        'exclude_colors' => [],
        'custom_colors' => [],
        'return_format' => 'slug',
    ];

    /**
     * Holds the URI to the plugin.
     */
    protected string $uri = '';

    /**
     * Holds the path to the plugin.
     */
    protected string $path = '';

    /**
     * Create a new Field instance.
     */
    public function __construct(callable $plugin)
    {
        $this->label = $plugin->label;
        $this->name = $plugin->name;
        $this->category = $plugin->category;
        $this->uri = $plugin->uri;
        $this->path = $plugin->path;

        parent::__construct();
    }

    /**
     * The rendered field type.
     *
     * @param  array  $field
     * @return void
     */
    public function render_field($field)
    {
        if (empty($palette = $this->palette(null, $field['custom_colors'] ?? []))) {
            echo __('The theme editor palette is empty.', 'acf-editor-palette');

            return;
        }

        if (! empty($excluded = $field['exclude_colors']) && is_array($excluded)) {
            $palette = array_filter($palette, function ($color) use ($excluded) {
                return ! in_array($color['slug'], $excluded);
            });
        }

        if (! empty($allowed = $field['allowed_colors']) && is_array($allowed)) {
            $palette = array_filter($palette, function ($color) use ($allowed) {
                return in_array($color['slug'], $allowed);
            });
        }

        if (empty($palette)) {
            echo __('There are no colors available.', 'acf-editor-palette');

            return;
        }

        $active = is_array($field['value']) ? $field['value']['slug'] : $field['value'];

        $id = esc_attr($field['id']);
        $name = esc_attr($field['name']);

        echo sprintf('<input type="hidden" id="%s" name="%s" value="">', $id, $name);

        echo sprintf('<div class="%s components-circular-option-picker">', esc_attr($field['class']));

        echo '<ul class="components-circular-option-picker__swatches">';

        echo sprintf(
            '<input class="empty-value" type="radio" id="%s-empty" name="%s" value="" %s>',
            $id,
            $name,
            checked(null, $active, false)
        );

        foreach ($palette as $color) {
            $checked = checked($color['slug'], $active, false);
            $color = array_map('esc_attr', $color);

            echo '<li class="components-circular-option-picker__option-wrapper">';

            echo sprintf(
                '<input type="radio" id="%1$s-%2$s" name="%3$s" value="%2$s" %4$s>',
                $id,
                $color['slug'],
                $name,
                $checked
            );

            echo sprintf(
                '<label
                    for="%1$s-%2$s"
                    aria-label="Color: %3$s"
                    title="%3$s"
                    class="components-button components-circular-option-picker__option acf-js-tooltip"
                    style="background-color: %4$s; color: %4$s;"
                    data-color="%4$s"
                ></label>',
                $id,
                $color['slug'],
                $color['name'],
                $color['color']
            );

            echo '</li>';
        }

        echo '</ul>';

        echo '<div class="components-circular-option-picker__custom-clear-wrapper">'.
            '<button type="button" class="components-button components-circular-option-picker__clear is-secondary is-small">'. // phpcs:ignore
                __('Clear', 'acf-editor-palette').
            '</button>'.
        '</div>';

        echo '</div>';
    }

    /**
     * The rendered field type settings.
     *
     * @param  array  $field
     * @return void
     */
    public function render_field_settings($field)
    {
        $colors = [];

        acf_render_field_setting($field, [
            'label' => __('Custom Colors', 'acf-editor-palette'),
            'name' => 'custom_colors',
            'instructions' => __('Replace the theme palette with custom colors. Slugs are generated from the name when left empty.', 'acf-editor-palette'),
            'type' => 'repeater',
            'layout' => 'table',
            'button_label' => __('Add Color', 'acf-editor-palette'),
            // Keys match names so rows load and save in the same shape as theme.json colors.
            'sub_fields' => array_map('acf_get_valid_field', [
                [
                    'label' => __('Name', 'acf-editor-palette'),
                    'name' => 'name',
                    'key' => 'name',
                    'type' => 'text',
                    'wrapper' => ['width' => 40],
                ],
                [
                    'label' => __('Slug', 'acf-editor-palette'),
                    'name' => 'slug',
                    'key' => 'slug',
                    'type' => 'text',
                    'wrapper' => ['width' => 30],
                    'placeholder' => __('Optional', 'acf-editor-palette'),
                ],
                [
                    'label' => __('Color', 'acf-editor-palette'),
                    'name' => 'color',
                    'key' => 'color',
                    'type' => 'color_picker',
                    'wrapper' => ['width' => 30],
                ],
            ]),
        ]);

        if (empty($palette = $this->palette(null, $field['custom_colors'] ?? []))) {
            return acf_render_field_setting($field, [
                'label' => __('No color palette found.', 'acf-editor-palette'),
                'name' => 'color_palette_empty',
                'instructions' => __('You must have a color palette to use this field.', 'acf-editor-palette'),
                'type' => 'message',
                'message' => __('The theme editor palette is empty. Add a color palette using <a target="_blank" rel="noopener noreferrer" href="https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-json/">theme.json</a> or <a target="_blank" rel="noopener noreferrer" href="https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-support/#block-color-palettes">add_theme_support()</a>.', 'acf-editor-palette'), // phpcs:ignore
            ]);
        }

        foreach ($palette as $item) {
            $colors[$item['slug']] = $item['name'];
        }

        acf_render_field_setting($field, [
            'label' => __('Default Color', 'acf-editor-palette'),
            'name' => 'default_value',
            'instructions' => __('The default color value.', 'acf-editor-palette'),
            'type' => 'select',
            'default_value' => null,
            'allow_null' => true,
            'placeholder' => __('None', 'acf-editor-palette'),
            'choices' => $colors,
        ]);

        acf_render_field_setting($field, [
            'label' => __('Allowed Colors', 'acf-editor-palette'),
            'name' => 'allowed_colors',
            'instructions' => __('Allow colors from the palette.', 'acf-editor-palette'),
            'type' => 'select',
            'ui' => '1',
            'default_value' => null,
            'allow_null' => true,
            'multiple' => true,
            'placeholder' => __('All colors', 'acf-editor-palette'),
            'choices' => $colors,
        ]);

        acf_render_field_setting($field, [
            'label' => __('Exclude Colors', 'acf-editor-palette'),
            'name' => 'exclude_colors',
            'instructions' => __('Exclude colors from the palette.', 'acf-editor-palette'),
            'type' => 'select',
            'ui' => '1',
            'default_value' => null,
            'allow_null' => true,
            'multiple' => true,
            'placeholder' => __('None', 'acf-editor-palette'),
            'choices' => $colors,
        ]);

        acf_render_field_setting($field, [
            'label' => __('Return Format', 'acf-editor-palette'),
            'name' => 'return_format',
            'instructions' => __('The format of the returned data.', 'acf-editor-palette'),
            'type' => 'select',
            'choices' => [
                'name' => 'Name',
                'slug' => 'Slug',
                'color' => 'Color',
                'text' => 'Text class',
                'background' => 'Background class',
                'array' => 'Array',
            ],
        ]);
    }

    /**
     * The formatted field value.
     *
     * @param  mixed  $value
     * @param  int  $post_id
     * @param  array  $field
     * @return mixed
     */
    public function format_value($value, $post_id, $field)
    {
        $format = $field['return_format'] ?? $this->defaults['return_format'];

        if (! empty($value) && is_string($value)) {
            $value = $this->palette($value, $field['custom_colors'] ?? []);
        } elseif (! empty($value['slug']) && is_array($color = $this->palette($value['slug'], $field['custom_colors'] ?? []))) {
            $value = $color;
        }

        return $format === 'array' ? $value : ($value[$format] ?? $value);
    }

    /**
     * The condition the field value must meet before
     * it is valid and can be saved.
     *
     * @param  bool  $valid
     * @param  mixed  $value
     * @param  array  $field
     * @param  array  $input
     * @return bool
     */
    public function validate_value($valid, $value, $field, $input)
    {
        if (
            $valid &&
            ! empty($value) &&
            empty($this->palette($value, $field['custom_colors'] ?? []))
        ) {
            return __('The current color does not exist in the editor palette.', 'acf-editor-palette');
        }

        return $valid;
    }

    /**
     * The field value before saving to the database.
     *
     * @param  mixed  $value
     * @param  int  $post_id
     * @param  array  $field
     * @return mixed
     */
    public function update_value($value, $post_id, $field)
    {
        if (empty($value)) {
            return $value;
        }

        $value = is_string($value) ? $value : $value['slug'];

        return $this->palette($value, $field['custom_colors'] ?? []);
    }

    /**
     * The field settings before saving to the database.
     *
     * @param  array  $field
     * @return array
     */
    public function update_field($field)
    {
        $field['custom_colors'] = $this->customPalette($field['custom_colors'] ?? []);

        if (empty($colors = $this->palette(null, $field['custom_colors']))) {
            return $field;
        }

        foreach (['allowed_colors', 'exclude_colors'] as $key) {
            if (! empty($field[$key]) && is_array($field[$key])) {
                $field[$key] = array_values(array_intersect($field[$key], array_keys($colors)));
            }
        }

        if (! empty($field['default_value']) && is_string($field['default_value']) && ! isset($colors[$field['default_value']])) {
            $field['default_value'] = null;
        }

        return $field;
    }

    /**
     * The assets enqueued when rendering the field.
     *
     * @return void
     */
    public function input_admin_enqueue_scripts()
    {
        wp_enqueue_style($this->name, $this->asset('field.css'), ['wp-components'], null);
        wp_enqueue_script($this->name, $this->asset('field.js'), [], null, true);
        wp_add_inline_script($this->name, $this->inlineAsset('runtime.js'));
    }

    /**
     * The assets enqueued when creating a field group.
     *
     * @return void
     */
    public function field_group_admin_enqueue_scripts()
    {
        $this->input_admin_enqueue_scripts();
    }
}

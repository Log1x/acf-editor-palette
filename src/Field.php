<?php

namespace Log1x\AcfEditorPalette;

class Field extends \acf_field
{
    use Concerns\Asset;

    /**
     * The default field values.
     *
     * @var array
     */
    public $defaults = [
        'default_value' => null,
        'exclude_colors' => [],
        'return_format' => 'slug',
    ];

    /**
     * Create a new Field instance.
     *
     * @param  callable $plugin
     * @return void
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
     * Retrieve the editor color palette.
     *
     * @param  string $color
     * @return string[]
     */
    protected function palette($color = null)
    {
        $colors = [];
        $themeJson = [];

        $palette = (array) current(
            get_theme_support('editor-color-palette')
        );

        if (function_exists('wp_get_global_settings')) {
            $themeJson = wp_get_global_settings(['color', 'palette', 'theme']);
        }

        if (! isset($themeJson['border'])) {
            $palette = array_merge($palette, $themeJson);
        }

        if (empty($palette)) {
            return $color ?: $colors;
        }

        foreach ($palette as $value) {
            $colors = array_merge($colors, [
                $value['slug'] => array_merge($value, [
                    'text' => sprintf('has-text-color has-%s-color', $value['slug']),
                    'background' => sprintf('has-background has-%s-background-color', $value['slug']),
                ])
            ]);
        }

        return ! empty($color) ? $colors[$color] : $colors;
    }

    /**
     * The rendered field type.
     *
     * @param  array $field
     * @return void
     */
    public function render_field($field)
    {
        if (empty($palette = $this->palette())) {
            return;
        }

        $palette = array_filter($palette, function ($color) use ($field) {
            return ! in_array(
                $color['slug'],
                ! empty($field['exclude_colors']) && is_array($field['exclude_colors']) ? $field['exclude_colors'] : []
            );
        });

        $active = is_array($field['value']) ? $field['value']['slug'] : $field['value'];

        echo sprintf(
            '<input type="hidden" id="%s" name="%s" value="">',
            $field['id'],
            $field['name']
        );

        echo sprintf('<div class="%s components-circular-option-picker">', $field['class']);

        echo '<ul class="components-circular-option-picker__swatches">';

        echo sprintf(
            '<input class="empty-value" type="radio" id="%s-%s" name="%s" value="%s" %s>',
            $field['id'],
            'empty',
            $field['name'],
            null,
            checked(null, $active, false)
        );

        foreach ($palette as $color) {
            echo '<li class="components-circular-option-picker__option-wrapper">';

            echo sprintf(
                '<input type="radio" id="%s-%s" name="%s" value="%s" %s>',
                $field['id'],
                $color['slug'],
                $field['name'],
                $color['slug'],
                checked($color['slug'], $active, false)
            );

            echo sprintf(
                '<label
                    for="%s-%s"
                    aria-label="Color: %s"
                    title="%s"
                    class="components-button components-circular-option-picker__option acf-js-tooltip"
                    style="background-color: %s; color: %s;"
                    data-color="%s",
                ></label>',
                $field['id'],
                $color['slug'],
                $color['name'],
                $color['name'],
                $color['color'],
                $color['color'],
                $color['color']
            );

            echo '</li>';
        }

        echo '</ul>';

        echo '<div class="components-circular-option-picker__custom-clear-wrapper">' .
            '<button type="button" class="components-button components-circular-option-picker__clear is-secondary is-small">' . // phpcs:ignore
                __('Clear', 'acf-editor-palette') .
            '</button>' .
        '</div>';

        echo '</div>';
    }

    /**
     * The rendered field type settings.
     *
     * @param  array $field
     * @return void
     */
    public function render_field_settings($field)
    {
        $colors = [];

        foreach ($this->palette() as $item) {
            $colors[$item['slug']] = sprintf(
                '<span style="display: inline-block;
                    background-color: %s;
                    width: 1em;
                    height: 1em;
                    margin: 0 3px -3px;
                    border: 1px solid #ccd0d4;
                    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);"
                ></span> %s',
                $item['color'],
                $item['name']
            );
        }

        acf_render_field_setting($field, [
            'label' => __('Default Color', 'acf-editor-palette'),
            'name' => 'default_value',
            'instructions' => __('The default color value.', 'acf-editor-palette'),
            'type' => 'select',
            'ui' => '1',
            'default_value' => null,
            'allow_null' => true,
            'placeholder' => __('Select a color (optional)', 'acf-editor-palette'),
            'choices' => $colors,
        ]);

        acf_render_field_setting($field, [
            'label' => __('Exclude Colors', 'acf-editor-palette'),
            'name' => 'exclude_colors',
            'instructions' => __('Exclude colors from palette.', 'acf-editor-palette'),
            'type' => 'select',
            'ui' => '1',
            'default_value' => null,
            'allow_null' => true,
            'multiple' => true,
            'placeholder' => __('Select colors (optional)', 'acf-editor-palette'),
            'choices' => $colors,
        ]);

        acf_render_field_setting($field, [
            'label' => __('Return Format', 'acf-editor-palette'),
            'name' => 'return_format',
            'instructions' => __('The format of the returned data.', 'acf-editor-palette'),
            'type' => 'select',
            'ui' => '1',
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
     * @param  mixed $value
     * @param  int   $post_id
     * @param  array $field
     * @return mixed
     */
    public function format_value($value, $post_id, $field)
    {
        $format = $field['return_format'] ?? $this->defaults['return_format'];

        return $format === 'array' ? $value : ($value[$format] ?? $value);
    }

    /**
     * The condition the field value must meet before
     * it is valid and can be saved.
     *
     * @param  bool  $valid
     * @param  mixed $value
     * @param  array $field
     * @param  array $input
     * @return bool
     */
    public function validate_value($valid, $value, $field, $input)
    {
        if (
            $valid &&
            ! empty($value) &&
            empty($this->palette($value))
        ) {
            return __('The current color does not exist in the editor palette.', 'acf-editor-palette');
        }

        return $valid;
    }

    /**
     * The field value before saving to the database.
     *
     * @param  mixed $value
     * @param  int   $post_id
     * @param  array $field
     * @return mixed
     */
    public function update_value($value, $post_id, $field)
    {
        return ! empty($value) ? $this->palette($value) : $value;
    }

    /**
     * The assets enqueued when rendering the field.
     *
     * @return void
     */
    public function input_admin_enqueue_scripts()
    {
        wp_enqueue_style($this->name, $this->asset('css/field.css'), ['wp-components'], null);
        wp_enqueue_script($this->name, $this->asset('js/field.js'), [], null, true);
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

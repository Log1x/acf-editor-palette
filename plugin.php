<?php

/**
 * Plugin Name: Advanced Custom Fields: Editor Palette Field
 * Plugin URI:  https://github.com/log1x/acf-editor-palette
 * Description: A Gutenberg-like editor palette color picker field for Advanced Custom Fields.
 * Version:     1.2.2
 * Author:      Brandon Nifong
 * Author URI:  https://github.com/log1x
 */

namespace Log1x\AcfEditorPalette;

add_filter('after_setup_theme', new class
{
    /**
     * The field label.
     *
     * @var string
     */
    public $label = 'Editor Palette';

    /**
     * The field name.
     *
     * @var string
     */
    public $name = 'editor_palette';

    /**
     * The field category.
     *
     * @var string
     */
    public $category = 'basic';

    /**
     * The field asset URI.
     *
     * @var string
     */
    public $uri;

    /**
     * The field asset path.
     *
     * @var string
     */
    public $path = 'public/';

    /**
     * Invoke the plugin.
     *
     * @return void
     */
    public function __invoke()
    {
        if (! class_exists('\ACF')) {
            return;
        }

        $this->uri = plugin_dir_url(__FILE__).$this->path;
        $this->path = plugin_dir_path(__FILE__).$this->path;

        if (file_exists($composer = __DIR__.'/vendor/autoload.php')) {
            require_once $composer;
        }

        $this->register();
    }

    /**
     * Register the field type with ACF.
     *
     * @return void
     */
    protected function register()
    {
        foreach (['acf/include_field_types', 'acf/register_fields'] as $hook) {
            add_filter($hook, function () {
                return new Field($this);
            });
        }

        if (function_exists('register_graphql_acf_field_type')) {
            $this->registerGraphQl();
        }
    }

    /**
     * Register the field type with WPGraphQL.
     *
     * @return void
     */
    protected function registerGraphQl()
    {
        add_action('graphql_register_types', function () {
            register_graphql_object_type('EditorPaletteColor', [
                'description' => __('Editor Palette Color Object', 'acf-editor-palette'),
                'fields' => [
                    'color' => [
                        'type' => 'String',
                        'description' => __('The color value', 'acf-editor-palette'),
                    ],
                    'name' => [
                        'type' => 'String',
                        'description' => __('The color name', 'acf-editor-palette'),
                    ],
                    'slug' => [
                        'type' => 'String',
                        'description' => __('The color slug', 'acf-editor-palette'),
                    ],
                    'text' => [
                        'type' => 'String',
                        'description' => __('The text color class', 'acf-editor-palette'),
                    ],
                    'background' => [
                        'type' => 'String',
                        'description' => __('The background color class', 'acf-editor-palette'),
                    ],
                ],
            ]);
        });

        add_action('wpgraphql/acf/registry_init', function () {
            register_graphql_acf_field_type($this->name, [
                'graphql_type' => 'EditorPaletteColor',
                'resolve' => function ($root, $args, $context, $info, $field_config) {
                    $id = isset($root['node']) && isset($root['node']->ID) ? $root['node']->ID : null;
                    $field = $info->fieldName ?? null;

                    if (! $id || ! $field) {
                        return null;
                    }

                    $field = preg_replace('/[A-Z]/', '_$0', $field);
                    $field = strtolower($field);

                    return get_field($field, $id);
                },
            ]);
        });
    }
});

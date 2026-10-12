<?php

use Log1x\AcfEditorPalette\Field;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Reset the stubbed WordPress state before each test.
|
*/

uses()->beforeEach(function () {
    $GLOBALS['theme_support'] = [];
    $GLOBALS['global_settings'] = [];
    $GLOBALS['field_settings'] = [];
})->in('Unit');

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| Helpers for creating the field and setting the theme palette.
|
*/

function field()
{
    return new Field(new class
    {
        public $label = 'Editor Palette';

        public $name = 'editor_palette';

        public $category = 'basic';

        public $uri = '';

        public $path = '';

        public function __invoke()
        {
            //
        }
    });
}

function themePalette(array $colors)
{
    $GLOBALS['theme_support']['editor-color-palette'] = [$colors];
}

/*
|--------------------------------------------------------------------------
| Stubs
|--------------------------------------------------------------------------
|
| Minimal WordPress and ACF stubs used by the field.
|
*/

class acf_field
{
    public $name = '';

    public $label = '';

    public $category = 'basic';

    public function __construct()
    {
        //
    }
}

function acf_get_valid_field($field)
{
    return array_merge(['_name' => $field['name'], 'wrapper' => ['width' => '']], $field);
}

function acf_render_field_setting($field, $setting)
{
    $GLOBALS['field_settings'][$setting['name']] = $setting;
}

function get_theme_support($feature)
{
    return $GLOBALS['theme_support'][$feature] ?? false;
}

function wp_get_global_settings($path = [])
{
    return $GLOBALS['global_settings'];
}

function sanitize_title($title)
{
    return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($title)), '-');
}

function checked($checked, $current = true, $display = true)
{
    return (string) $checked === (string) $current ? " checked='checked'" : '';
}

function esc_attr($text)
{
    return htmlspecialchars((string) $text, ENT_QUOTES);
}

function sanitize_text_field($text)
{
    return trim(strip_tags((string) $text));
}

function __($text, $domain = 'default')
{
    return $text;
}

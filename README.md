# ACF Editor Palette

![Latest Stable Version](https://img.shields.io/packagist/v/log1x/acf-editor-palette?style=flat-square)
![Total Downloads](https://img.shields.io/packagist/dt/log1x/acf-editor-palette?style=flat-square)
![Build Status](https://img.shields.io/github/workflow/status/log1x/acf-editor-palette/Compatibility%20Checks?style=flat-square)

A Gutenberg-like editor palette color picker field for Advanced Custom Fields.

![Field Example](https://i.imgur.com/bKKU4Sr.gif)

## Features

- Colors are automatically loaded from `theme.json` and/or the editor palette.
- Return format includes the default [palette keys](https://developer.wordpress.org/block-editor/developers/themes/theme-support/) as well as background and text color classes for convenience.
- Default value can optionally be set using the color's slug.
- Colors can optionally be allowed/excluded from the palette.

## Requirements

- [PHP](https://secure.php.net/manual/en/install.php) >= 7.2
- [ACF PRO](https://www.advancedcustomfields.com/pro/) >= 5.0

## Installation

### Bedrock

Install via Composer:

```bash
$ composer require log1x/acf-editor-palette
```

### Manual

Download the [latest release](https://github.com/Log1x/acf-editor-palette/releases/latest) `.zip` and install into `wp-content/plugins`.

## Usage

![Field Group Example](https://i.imgur.com/awXqkFA.png)

```php
^ array:5 [▼
  "name" => "Green (500)"
  "slug" => "green-500"
  "color" => "#0e9f6e"
  "text" => "has-text-color has-green-500-color"
  "background" => "has-background has-green-500-background-color"
]
```

### ACF Composer

If you are on Sage 10 and using my [ACF Composer](https://github.com/log1x/acf-composer) package:

```php
$field
  ->addField('my_color_field', 'editor_palette')
    ->setConfig('default_value', 'green-500')
    ->setConfig('allowed_colors', ['green-500', 'blue-500'])
    ->setConfig('exclude_colors', ['green-50', 'green-100'])
    ->setConfig('return_format', 'slug');
```

## Bug Reports

If you discover a bug in ACF Editor Palette, please [open an issue](https://github.com/log1x/acf-editor-palette/issues).

## Contributing

Contributing whether it be through PRs, reporting an issue, or suggesting an idea is encouraged and appreciated.

## License

ACF Editor Palette is provided under the [MIT License](LICENSE.md).

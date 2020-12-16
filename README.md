# ACF Editor Palette

![Latest Stable Version](https://img.shields.io/packagist/v/log1x/acf-editor-palette?style=flat-square)
![Total Downloads](https://img.shields.io/packagist/dt/log1x/acf-editor-palette?style=flat-square)

A Gutenberg-like editor palette color picker field for Advanced Custom Fields.

![Screenshot](https://i.imgur.com/aQnAIVD.png)

## Requirements

- [PHP](https://secure.php.net/manual/en/install.php) >= 7.2
- [Composer](https://getcomposer.org/download/)

## Installation

### Bedrock

Install via Composer:

```bash
$ composer require log1x/acf-editor-palette
```

### Manual

Download the release `.zip` and install into `wp-content/plugins`.

## Usage

Pretty straight forward.

- Colors are automatically loaded from the editor palette.
- Return format is keyed the same as when [registered](https://developer.wordpress.org/block-editor/developers/themes/theme-support/) in the palette.
- Default value can optionally be set using the color's slug.

```php
^ array:3 [▼
  "name" => "Green (500)"
  "slug" => "green-500"
  "color" => "#0e9f6e"
]
```

### ACF Composer

If you are on Sage 10 and using my [ACF Composer](https://github.com/log1x/acf-composer) package:

```php
$field
  ->addField('my_color_field', 'editor_palette')
    ->setConfig('default_value' => 'green-500')
    ->setConfig('return_value' => 'slug');
```

## Todo

- [ ] Style ACF tooltip to be more uniform with Gutenberg
- [ ] Fix focus style
- [ ] Add gradient support

## Bug Reports

If you discover a bug in ACF Editor Palette, please [open an issue](https://github.com/log1x/acf-editor-palette/issues).

## Contributing

Contributing whether it be through PRs, reporting an issue, or suggesting an idea is encouraged and appreciated.

## License

ACF Editor Palette is provided under the [MIT License](https://github.com/log1x/acf-editor-palette/blob/master/LICENSE.md).

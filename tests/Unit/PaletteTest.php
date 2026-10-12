<?php

beforeEach(function () {
    themePalette([
        ['name' => 'Green', 'slug' => 'green', 'color' => '#0e9f6e'],
        ['name' => 'Blue', 'slug' => 'blue', 'color' => '#3f83f8'],
    ]);
});

it('returns the theme palette', function () {
    expect(field()->palette())->toHaveKeys(['green', 'blue']);
});

it('merges the theme.json palette', function () {
    $GLOBALS['global_settings'] = [
        ['name' => 'Red', 'slug' => 'red', 'color' => '#f05252'],
    ];

    expect(field()->palette())->toHaveKeys(['green', 'blue', 'red']);
});

it('returns a color by slug', function () {
    expect(field()->palette('green'))->toBe([
        'name' => 'Green',
        'slug' => 'green',
        'color' => '#0e9f6e',
        'text' => 'has-text-color has-green-color',
        'background' => 'has-background has-green-background-color',
    ]);
});

it('returns null for an unknown slug', function () {
    expect(field()->palette('purple'))->toBeNull();
});

it('replaces the theme palette with custom colors', function () {
    $palette = field()->palette(null, [
        ['name' => 'Magenta', 'slug' => 'magenta', 'color' => '#ff00ff'],
    ]);

    expect($palette)->toHaveKeys(['magenta'])->not->toHaveKeys(['green', 'blue']);
});

it('generates a slug from the custom color name', function () {
    $color = field()->palette('brand-blue', [
        ['name' => 'Brand Blue', 'color' => '#0066cc'],
    ]);

    expect($color)
        ->slug->toBe('brand-blue')
        ->color->toBe('#0066cc')
        ->background->toBe('has-background has-brand-blue-background-color');
});

it('falls back to the theme palette when the custom colors are invalid', function () {
    expect(field()->palette(null, [['name' => 'No Color']]))->toHaveKeys(['green', 'blue']);
});

it('sanitizes the custom color slug', function () {
    expect(field()->customPalette([['name' => 'Magenta', 'slug' => 'Hot Pink', 'color' => '#ff00ff']]))
        ->toBe([['name' => 'Magenta', 'slug' => 'hot-pink', 'color' => '#ff00ff']]);
});

it('sanitizes the custom color name and color', function () {
    expect(field()->customPalette([['name' => '<b>Magenta</b>', 'color' => ' #ff00ff ']]))
        ->toBe([['name' => 'Magenta', 'slug' => 'magenta', 'color' => '#ff00ff']]);
});

it('skips custom colors that are empty once sanitized', function () {
    expect(field()->customPalette([
        ['name' => '<b></b>', 'color' => '#ff00ff'],
        ['name' => 'Magenta', 'color' => '<i></i>'],
        ['name' => '!!!', 'color' => '#ff00ff'],
    ]))->toBe([]);

    expect(field()->palette(null, [['name' => '<b></b>', 'color' => '#ff00ff']]))->toHaveKeys(['green', 'blue']);
});

it('only accepts hex, rgb and hsl custom colors', function () {
    $palette = field()->customPalette([
        ['name' => 'Hex', 'color' => '#ff00ff'],
        ['name' => 'Short Hex', 'color' => '#f0f'],
        ['name' => 'Alpha Hex', 'color' => '#BCEFE04D'],
        ['name' => 'Rgba', 'color' => 'rgba(242, 107, 91, 0.5)'],
        ['name' => 'Hsl', 'color' => 'hsl(6 85% 65%)'],
        ['name' => 'Injected', 'color' => 'red; background-image: url(//example.com/x)'],
        ['name' => 'Named', 'color' => 'red'],
    ]);

    expect(array_column($palette, 'name'))->toBe(['Hex', 'Short Hex', 'Alpha Hex', 'Rgba', 'Hsl']);
});

it('skips invalid custom colors', function () {
    $palette = field()->customPalette([
        ['name' => 'Magenta', 'color' => '#ff00ff'],
        ['name' => 'No Color'],
        ['color' => '#000000'],
        'not-a-color',
    ]);

    expect($palette)->toHaveCount(1);
});

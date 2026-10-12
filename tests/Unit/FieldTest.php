<?php

beforeEach(function () {
    themePalette([
        ['name' => 'Green', 'slug' => 'green', 'color' => '#0e9f6e'],
    ]);

    $this->field = array_merge(field()->defaults, [
        'id' => 'acf-field_color',
        'name' => 'acf[field_color]',
        'class' => '',
        'value' => null,
        'custom_colors' => [
            ['name' => 'Magenta', 'slug' => 'magenta', 'color' => '#ff00ff'],
            ['name' => 'Cyan', 'slug' => 'cyan', 'color' => '#00ffff'],
        ],
    ]);
});

it('formats a custom color value', function () {
    expect(field()->format_value('magenta', 1, $this->field))->toBe('magenta');

    expect(field()->format_value('magenta', 1, array_merge($this->field, ['return_format' => 'color'])))
        ->toBe('#ff00ff');
});

it('formats a saved color with the current custom color', function () {
    $saved = ['name' => 'Magenta', 'slug' => 'magenta', 'color' => '#cc00cc'];

    expect(field()->format_value($saved, 1, array_merge($this->field, ['return_format' => 'color'])))
        ->toBe('#ff00ff');
});

it('formats a saved color that is no longer in the palette', function () {
    $saved = ['name' => 'Orange', 'slug' => 'orange', 'color' => '#ff6600'];

    expect(field()->format_value($saved, 1, array_merge($this->field, ['return_format' => 'color'])))
        ->toBe('#ff6600');
});

it('validates against the custom colors', function () {
    expect(field()->validate_value(true, 'magenta', $this->field, ''))->toBeTrue();

    expect(field()->validate_value(true, 'green', $this->field, ''))->toBeString();
});

it('validates against the theme palette without custom colors', function () {
    $field = array_merge($this->field, ['custom_colors' => []]);

    expect(field()->validate_value(true, 'green', $field, ''))->toBeTrue();
});

it('saves the custom color', function () {
    expect(field()->update_value('cyan', 1, $this->field))
        ->name->toBe('Cyan')
        ->color->toBe('#00ffff');
});

it('normalizes the custom colors repeater when saving the field', function () {
    $field = field()->update_field(['custom_colors' => [
        'row-0' => ['name' => 'Magenta', 'slug' => '', 'color' => '#ff00ff'],
        'row-1' => ['name' => 'Brand Blue', 'slug' => 'brand', 'color' => '#0066cc'],
        'acfcloneindex' => ['name' => '', 'slug' => '', 'color' => ''],
    ]]);

    expect($field['custom_colors'])->toBe([
        ['name' => 'Magenta', 'slug' => 'magenta', 'color' => '#ff00ff'],
        ['name' => 'Brand Blue', 'slug' => 'brand', 'color' => '#0066cc'],
    ]);
});

it('drops allowed, excluded and default colors missing from the palette when saving the field', function () {
    $field = field()->update_field([
        'custom_colors' => [['name' => 'Magenta', 'color' => '#ff00ff'], ['name' => 'Cyan', 'color' => '#00ffff']],
        'allowed_colors' => ['green', 'magenta'],
        'exclude_colors' => ['green', 'cyan'],
        'default_value' => 'green',
    ]);

    expect($field)
        ->allowed_colors->toBe(['magenta'])
        ->exclude_colors->toBe(['cyan'])
        ->default_value->toBeNull();
});

it('keeps the field settings when there is no palette', function () {
    $GLOBALS['theme_support'] = [];

    $field = field()->update_field(['custom_colors' => [], 'allowed_colors' => ['green'], 'default_value' => 'green']);

    expect($field)->allowed_colors->toBe(['green'])->default_value->toBe('green');
});

it('renders the custom colors', function () {
    ob_start();
    field()->render_field($this->field);
    $html = ob_get_clean();

    expect($html)
        ->toContain('value="magenta"', 'value="cyan"', 'background-color: #ff00ff')
        ->not->toContain('value="green"');
});

it('escapes the custom colors when rendering', function () {
    $field = array_merge($this->field, ['custom_colors' => [
        ['name' => 'Bad" onmouseover="alert(1)', 'slug' => 'bad" onclick="alert(1)', 'color' => '#000000'],
    ]]);

    ob_start();
    field()->render_field($field);
    $html = ob_get_clean();

    expect($html)->not->toContain('" onmouseover=', '" onclick=');
});

it('filters the custom colors', function () {
    ob_start();
    field()->render_field(array_merge($this->field, ['exclude_colors' => ['cyan']]));
    $html = ob_get_clean();

    expect($html)->toContain('value="magenta"')->not->toContain('value="cyan"');
});

it('renders the custom colors repeater setting', function () {
    field()->render_field_settings($this->field);

    expect($GLOBALS['field_settings']['custom_colors'])
        ->type->toBe('repeater')
        ->and(array_column($GLOBALS['field_settings']['custom_colors']['sub_fields'], 'key', '_name'))
        ->toBe(['name' => 'name', 'slug' => 'slug', 'color' => 'color']);

    expect($GLOBALS['field_settings'])
        ->default_value->choices->toBe(['magenta' => 'Magenta', 'cyan' => 'Cyan']);
});

it('renders the custom colors setting without a theme palette', function () {
    $GLOBALS['theme_support'] = [];

    field()->render_field_settings(array_merge($this->field, ['custom_colors' => []]));

    expect($GLOBALS['field_settings'])->toHaveKeys(['custom_colors', 'color_palette_empty']);
});

<?php

namespace Log1x\AcfEditorPalette\Concerns;

trait Palette
{
    /**
     * Retrieve the editor color palette.
     *
     * @param  string  $color
     * @param  array  $custom
     * @return string[]
     */
    public function palette($color = null, $custom = [])
    {
        $colors = [];

        $palette = $this->customPalette($custom) ?: $this->themePalette();

        if (empty($palette)) {
            return $color ?: $colors;
        }

        foreach ($palette as $value) {
            if (empty($value['slug'])) {
                continue;
            }

            $colors = array_merge($colors, [
                $value['slug'] => array_merge($value, [
                    'text' => sprintf('has-text-color has-%s-color', $value['slug']),
                    'background' => sprintf('has-background has-%s-background-color', $value['slug']),
                ]),
            ]);
        }

        return ! empty($color) && is_string($color) && is_array($colors) ? (
            array_key_exists($color, $colors) ? $colors[$color] : null
        ) : $colors;
    }

    /**
     * Retrieve the theme color palette.
     *
     * @return array
     */
    protected function themePalette()
    {
        $themeJson = [];

        $palette = (array) current(
            get_theme_support('editor-color-palette') ?: []
        );

        if (function_exists('wp_get_global_settings')) {
            $themeJson = wp_get_global_settings(['color', 'palette', 'theme']);
        }

        if (! isset($themeJson['border'])) {
            $palette = array_merge($palette, $themeJson);
        }

        return $palette;
    }

    /**
     * Retrieve the custom color palette.
     *
     * @param  array  $colors
     * @return array
     */
    public function customPalette($colors = [])
    {
        if (! is_array($colors)) {
            return [];
        }

        $palette = [];

        foreach ($colors as $value) {
            if (! is_array($value)) {
                continue;
            }

            $name = sanitize_text_field($value['name'] ?? '');
            $color = sanitize_text_field($value['color'] ?? '');
            $slug = sanitize_title(! empty($value['slug']) ? $value['slug'] : $name);

            if (empty($name) || empty($slug) || ! $this->isColor($color)) {
                continue;
            }

            $palette[] = compact('name', 'slug', 'color');
        }

        return $palette;
    }

    /**
     * Determine if the value is a hex, rgb() or hsl() color.
     *
     * @param  string  $color
     * @return bool
     */
    protected function isColor($color)
    {
        return (bool) preg_match(
            '/^(#([0-9a-f]{3,4}|[0-9a-f]{6}|[0-9a-f]{8})|(rgb|hsl)a?\([\d\s.,%\/]+\))$/i',
            $color
        );
    }
}

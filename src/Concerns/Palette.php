<?php

namespace Log1x\AcfEditorPalette\Concerns;

trait Palette
{
    /**
     * Retrieve the editor color palette.
     *
     * @param  string  $color
     * @return string[]
     */
    public function palette($color = null, $custom_colors = [])
    {
        $colors = [];
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

        if (empty($palette)) {
            return $color ?: $colors;
        }

        if (! empty($custom_colors)) {
            $palette = $this->sanitize_custom_colors($custom_colors);
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

        return ! empty($color) && is_string($color) ? (
            $colors[$color] ?? null
        ) : $colors;
    }

    public function sanitize_custom_colors($customColors = [])
    {
        if (! is_array($customColors)) {
            return [];
        }

        return array_map(function ($color) {
            if (empty($color['slug'])) {
                $color['slug'] = sanitize_title($color['name']);
            }

            return $color;
        }, $customColors);
    }
}

<?php

namespace Log1x\AcfEditorPalette\Concerns;

trait Palette
{
    /**
     * Retrieve the editor color palette.
     *
     * @param  string  $color
     * @param  bool  $includeGradients
     * @return string[]
     */
    public function palette($color = null, $includeGradients = false)
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

        if ($includeGradients) {
            foreach ($this->gradients() as $slug => $value) {
                $colors[$slug] = $value;
            }
        }

        if (empty($colors)) {
            return $color ?: $colors;
        }

        return ! empty($color) && is_string($color) && is_array($colors) ? (
        array_key_exists($color, $colors) ? $colors[$color] : null
        ) : $colors;
    }

    /**
     * Retrieve the editor gradient palette.
     *
     * @return string[]
     */
    public function gradients()
    {
        $gradients = [];
        $themeJson = [];

        $palette = (array) current(
            get_theme_support('editor-gradient-presets') ?: []
        );

        if (function_exists('wp_get_global_settings')) {
            $themeJson = wp_get_global_settings(['color', 'gradients', 'theme']);
        }

        if (is_array($themeJson)) {
            $palette = array_merge($palette, $themeJson);
        }

        foreach ($palette as $value) {
            if (empty($value['slug'])) {
                continue;
            }

            $gradients[$value['slug']] = array_merge($value, [
                'text' => '',
                'background' => sprintf('has-background has-%s-gradient-background', $value['slug']),
            ]);
        }

        return $gradients;
    }
}

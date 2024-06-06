<?php

namespace Log1x\AcfEditorPalette\Concerns;

trait Asset
{
    /**
     * The manifest.
     */
    public array $manifest = [];

    /**
     * Retrieve the URI of an asset.
     */
    public function asset(?string $asset = null): string
    {
        $asset = $this->manifest($asset);

        return $this->uri . $asset;
    }

    /**
     * Retrieve the content of an asset.
     */
    public function inlineAsset(string $asset): ?string
    {
        $path = $this->path . $this->manifest($asset);

        if (! file_exists($path)) {
            return null;
        }

        return file_get_contents($path);
    }

    /**
     * Retrieve the manifest.
     */
    public function manifest(?string $asset = null): array|string
    {
        if ($this->manifest) {
            return $asset
                ? $this->manifest[$asset]
                : $this->manifest;
        }

        if (! file_exists($manifest = $this->path . 'manifest.json')) {
            return [];
        }

        $this->manifest = json_decode(file_get_contents($manifest), true);

        return $asset
            ? $this->manifest[$asset]
            : $this->manifest;
    }
}

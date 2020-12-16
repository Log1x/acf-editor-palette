<?php

namespace Log1x\AcfEditorPalette\Concerns;

trait Asset
{
    /**
     * Resolve an asset URI from Laravel Mix's manifest.
     *
     * @param  string $asset
     * @return string
     */
    public function asset($asset = null)
    {
        if (! file_exists($manifest = $this->path . 'mix-manifest.json')) {
            return $this->uri . $asset;
        }

        $manifest = json_decode(file_get_contents($manifest), true);

        return $this->uri . ($manifest[$asset] ?? $asset);
    }
}

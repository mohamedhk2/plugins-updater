<?php

namespace HK2\PluginsUpdater\Traits;

use Illuminate\Support\Collection;

trait PluginsTrait
{
    protected ?Collection $plugins = null;

    /**
     * @return Collection
     */
    protected function plugins(): Collection
    {
        if ($this->plugins)
            return $this->plugins;
        $this->plugins = collect(json_decode(file_get_contents(dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . HK2_UPDATER_PLUGINS_FILE), true));
        return $this->plugins;
    }
}

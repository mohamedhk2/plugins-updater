<?php

namespace HK2\PluginsUpdater\Traits;

use Illuminate\Support\Collection;
use JsonSchema\Validator;

trait CustomPluginsTrait
{
    protected ?Collection $custom_plugins = null;

    /**
     * @return Collection|null
     */
    protected function custom_plugins(): ?Collection
    {
        if ($this->custom_plugins)
            return $this->custom_plugins;
        else $this->custom_plugins = collect();
        if (!file_exists(storage_path(HK2_UPDATER_CUSTOM_PLUGINS_FILE)))
            goto file_not_exists;
        $data = json_decode(file_get_contents(storage_path(HK2_UPDATER_CUSTOM_PLUGINS_FILE)), true);
        $validator = new Validator;
        $validator->validate($data, json_decode(file_get_contents(dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . HK2_UPDATER_CUSTOM_PLUGINS_SCHEMA), true));
        if ($validator->isValid())
            $this->custom_plugins = collect($data);
        file_not_exists:
        $this->custom_plugins->push(...HK2_PLUGINS);
        return $this->custom_plugins;
    }
}

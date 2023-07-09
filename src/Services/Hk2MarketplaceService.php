<?php

namespace HK2\PluginsUpdater\Services;

use Botble\PluginManagement\Services\MarketplaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;

class Hk2MarketplaceService extends MarketplaceService
{
    /**
     * @param string $id
     * @param string $type
     * @param string $name
     * @param string|null $zip
     * @return bool|JsonResponse
     * @throws \Exception
     */
    public function beginInstall(string $id, string $type, string $name, string $zip = null): bool|JsonResponse
    {
        if (!$zip)
            throw new \Exception('Zip file not found');
        File::ensureDirectoryExists($this->publishedPath . $id);
        $destination = $this->publishedPath . $id . '/' . $name . '.zip';
        File::cleanDirectory($this->publishedPath . $id);
        File::put($destination, \Http::get($zip));
        $this->extractFile($id, $name);
        $this->copyToPath($id, $type, $name);
        return true;
    }

    /**
     * @param string $id
     * @param string $type
     * @param string $name
     * @return string
     */
    protected function copyToPath(string $id, string $type, string $name): string
    {
        $pathTemp = $this->publishedPath . $id;
        $path = ($type == 'plugin' ? plugin_path($name) : theme_path($name));

        if (File::isDirectory($pathTemp)) {
            $folders = File::directories($pathTemp);
            if (count($folders) == 1) {
                $pathTemp2 = $folders[0];
                File::copyDirectory($pathTemp2, $path);
            } else
                File::copyDirectory($pathTemp, $path);
            File::deleteDirectory($pathTemp);
        }
        return $path;
    }
}

<?php

namespace HK2\PluginsUpdater\Services;

use Botble\PluginManagement\Services\MarketplaceService;
use HK2\PluginsUpdater\Traits\CustomPluginsTrait;
use HK2\PluginsUpdater\Traits\PluginsTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;

class Hk2MarketplaceService extends MarketplaceService
{
    use PluginsTrait, CustomPluginsTrait;

    /**
     * @param string $id
     * @param string $type
     * @param string $name
     * @param string|null $zip
     * @param bool $use_token
     * @return bool|JsonResponse
     * @throws \Exception
     */
    public function beginInstall(string $id, string $type, string $name, string $zip = null, bool $use_token = false): bool|JsonResponse
    {
        if (!$zip)
            throw new \Exception('Zip file not found');
        File::ensureDirectoryExists($this->publishedPath . $id);
        $destination = $this->publishedPath . $id . '/' . $name . '.zip';
        File::cleanDirectory($this->publishedPath . $id);
        if ($use_token) {
            if (empty($github_token = setting(HK2_UPDATER_GITHUB_SETTING_NAME, '')))
                return false;
            $custom_plugin = $this->plugins()->merge($this->custom_plugins())->firstWhere('id', $id);
            if (!$custom_plugin)
                throw new \Exception('Plugin not found');
            $github_id = $custom_plugin['github_id'];
            $req = \Http::withHeaders([
                'accept' => 'application/vnd.github+json',
                'Authorization' => "Bearer {$github_token}",
                'owner' => \Str::before($github_id, '/'),
                'repo' => \Str::after($github_id, '/'),
                'ref' => 'HK2',
            ])->get($zip);
            if ($req->failed())
                throw new \Exception($req->json()->message);
            File::put($destination, $req->body());
        } else
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

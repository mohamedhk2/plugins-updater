<?php

namespace HK2\PluginsUpdater\Http\Controllers;

use Botble\PluginManagement\Http\Controllers\MarketplaceController;
use Botble\PluginManagement\Services\MarketplaceService;
use Botble\PluginManagement\Services\PluginService;
use Carbon\Carbon;
use HK2\PluginsUpdater\Services\Hk2MarketplaceService;
use HK2\PluginsUpdater\Traits\CustomPluginsTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Throwable;

class HK2PluginsUpdaterController extends MarketplaceController
{
    use CustomPluginsTrait;

    protected ?Collection $plugins = null;
    protected ?Hk2MarketplaceService $myService = null;

    public function __construct(
        protected MarketplaceService $marketplaceService,
        protected PluginService      $pluginService,
    )
    {
        parent::__construct($marketplaceService, $pluginService);
        $this->myService = new Hk2MarketplaceService;
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function install(string $id): JsonResponse
    {
        $plugin = $this->plugins()->firstWhere('id', $id);
        if (!$plugin)
            $plugin = $this->custom_plugins()->firstWhere('id', $id);
        if (!$plugin)
            return parent::install($id);
        #todo: check minimum_core_version
        $use_token = $plugin['use_token'] ?? false;
        $name = Str::afterLast($plugin['package_name'], '/');
        $latest = $this->githubLatest($plugin['github_id'], $plugin['id'], $message, $use_token);
        if ($message)
            return response()->json([
                'error' => true,
                'message' => $message,
            ]);
        try {
            $this->myService->beginInstall($id, 'plugin', $name, $latest['zipball_url'], $use_token);
        } catch (Throwable $exception) {
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage(),
            ]);
        }
        return response()->json([
            'error' => false,
            'message' => trans('packages/plugin-management::marketplace.install_success'),
            'data' => [
                'name' => $name,
                'id' => $id,
            ],
        ]);
    }

    /**
     * @return Collection
     */
    protected function plugins()
    {
        if ($this->plugins)
            return $this->plugins;
        $this->plugins = collect(json_decode(file_get_contents(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . HK2_UPDATER_PLUGINS_FILE), true));
        return $this->plugins;
    }

    /**
     * @param $github_id
     * @param string $id
     * @param string|null $message
     * @param bool $use_token
     * @return array|null
     */
    protected function githubLatest($github_id, string $id, string &$message = null, bool $use_token = false): ?array
    {
        if ($update = $this->getUpdateData($github_id))
            return $update;
        if ($use_token) {
            if (empty($github_token = setting(HK2_UPDATER_GITHUB_SETTING_NAME, '')))
                return null;
        }
        $req = \Http::withHeaders($use_token ? [
            'Authorization' => "Bearer {$github_token}",
        ] : [])->get("https://api.github.com/repos/{$github_id}/releases/latest");
        if ($req->failed()) {
            $message = $latest['message'] ?? null;
            return null;
        }
        $latest = $req->json();
        $req = \Http::withHeaders($use_token ? [
            'Authorization' => "Bearer {$github_token}",
        ] : [])->get("https://raw.githubusercontent.com/{$github_id}/{$latest['tag_name']}/plugin.json");
        if ($req->failed()) {
            $package_json = [
                'author' => $latest['author']['login'],
                'description' => $latest['body'],
                'version' => null,
                'url' => null,
            ];
        } else $package_json = $req->json();
        return $this->setUpdateData([
            'id' => $id,
            'github_id' => $github_id,
            'tag_name' => $latest['tag_name'],
            'version' => $package_json['version'],
            'zipball_url' => $latest['zipball_url'],
            'tarball_url' => $latest['tarball_url'],
            'author_name' => $package_json['author'],
            'author_url' => $latest['author']['html_url'],
            'published_at' => $latest['published_at'],
            'description' => $package_json['description'],
            'url' => $package_json['url'] ?? $update['url'],
        ]);
    }

    /**
     * @param $github_id
     * @return null
     */
    protected function getUpdateData($github_id)
    {
        $updates = $this->updates();
        return $updates->where('date', $this->verify_date())->firstWhere('github_id', $github_id);
    }

    /**
     * @return Collection
     */
    protected function updates()
    {
        if (!file_exists(storage_path(HK2_UPDATER_UPDATE_FILE)))
            file_put_contents(storage_path(HK2_UPDATER_UPDATE_FILE), '[]');
        return collect(json_decode(file_get_contents(storage_path(HK2_UPDATER_UPDATE_FILE)), true));
    }

    /**
     * @return string
     */
    protected function verify_date()
    {
        return Carbon::now()->format('Y-m-d H:');
    }

    /**
     * @param $github_data
     * @return array
     */
    protected function setUpdateData($github_data)
    {
        $updates = $this->updates();
        $exist = $updates->firstWhere('github_id', $github_data['github_id']);
        if ($exist) {
            $index = $updates->search($exist);
            $exist['tag_name'] = $github_data['tag_name'];
            $exist['zipball_url'] = $github_data['zipball_url'];
            $exist['tarball_url'] = $github_data['tarball_url'];
            $exist['date'] = $this->verify_date();
            $updates->put($index, $exist);
        } else {
            $updates->push($exist = $github_data + [
                    'date' => $this->verify_date()
                ]);
        }
        file_put_contents(storage_path(HK2_UPDATER_UPDATE_FILE), $updates->toJson(JSON_PRETTY_PRINT));
        return $exist;
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function update(string $id): JsonResponse
    {
        $plugin = $this->plugins()->firstWhere('id', $id);
        if (!$plugin)
            $plugin = $this->custom_plugins()->firstWhere('id', $id);
        if (!$plugin)
            return parent::update($id);
        $use_token = $plugin['use_token'] ?? false;
        $name = Str::afterLast($plugin['package_name'], '/');
        $installed = $this->pluginService->getPluginInfo($name);
        if (!$installed)
            return response()->json([
                'success' => false,
                'message' => trans('packages/plugin-management::plugin.plugin_not_exist'),
            ]);
        $latest = $this->githubLatest($plugin['github_id'], $plugin['id'], $message, $use_token);
        if ($message)
            return response()->json([
                'error' => true,
                'message' => $message,
            ]);
        if (version_compare($installed['version'], $latest['tag_name'], '<')) {
            try {
                $this->myService->beginInstall($id, 'plugin', $name, $latest['zipball_url'], $use_token);
            } catch (Throwable $exception) {
                return response()->json([
                    'error' => true,
                    'message' => $exception->getMessage(),
                ]);
            }
            $this->pluginService->runMigrations($name);
            $published = $this->pluginService->publishAssets($name);
            if ($published['error']) {
                return response()->json([
                    'error' => true,
                    'message' => $published['message'],
                ]);
            }
            $this->pluginService->publishTranslations($name);
        }
        return response()->json([
            'error' => false,
            'message' => trans('packages/plugin-management::marketplace.update_success'),
            'data' => [
                'name' => $name,
                'id' => $id,
            ],
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function checkUpdate()
    {
        $resp = [
            'success' => true,
            'data' => [],
            'message' => null,
            'meta' => [],
        ];
        $installedPlugins = $this->pluginService->getInstalledPluginIds();
        if (!$installedPlugins) {
            return response()->json();
        }
        foreach ($installedPlugins as $package_name => $package_version) {
            $plugin = $this->plugins()->firstWhere('package_name', $package_name);
            if (!$plugin)
                $plugin = $this->custom_plugins()->firstWhere('package_name', $package_name);
            if (!$plugin) continue;
            $use_token = $plugin['use_token'] ?? false;
            $latest = $this->githubLatest($plugin['github_id'], $plugin['id'], use_token: $use_token);
            if (!$latest) continue;
            $latest_version = $latest['version'] ?? $latest['tag_name'];
            version_compare($package_version, $latest_version, '<') && $resp['data'][] = [
                'id' => $plugin['id'],
                'name' => $plugin['package_name'],
                'version' => $latest_version
            ];
        }
        return response()->json($resp);
    }

    /**
     * @param Request $request
     * @return array|JsonResponse|mixed
     */
    public function list(Request $request)
    {
        if (setting(HK2_UPDATER_CUSTOM_ENABLE_SETTING_NAME, false)) {
            $per_page = 12;
            $current_page = $request->get('page', 1);
            $github_token = setting(HK2_UPDATER_GITHUB_SETTING_NAME, '');
            $return = $this->custom_plugins()->map(function ($custom_plugin) use ($github_token) {
                $use_token = $custom_plugin['use_token'] ?? false;
                $update = $this->githubLatest($custom_plugin['github_id'], $custom_plugin['id'], use_token: $use_token);
                if (!$update) return null;
                $image_url = "https://raw.githubusercontent.com/{$custom_plugin['github_id']}/{$update['tag_name']}/screenshot.png";
                if ($use_token) {
                    $image = \Http::withHeaders($use_token ? [
                        'Authorization' => "Bearer {$github_token}",
                    ] : [])->get("https://raw.githubusercontent.com/{$custom_plugin['github_id']}/{$update['tag_name']}/screenshot.png");
                    $image_url = 'data:image/png;base64,' . base64_encode($image->body());
                }
                return [
                    'author_name' => $update['author_name'],
                    'author_url' => $update['author_url'],
                    'content' => [],
                    'description' => $update['description'],
                    'downloads_count' => 0,
                    'image_url' => $image_url,
                    'last_updated_at' => $update['published_at'],
                    'latest_version' => $update['version'] ?? $update['tag_name'],
                    'id' => $custom_plugin['id'],
                    'license' => 'MIT',
                    'license_url' => 'https://opensource.org/licenses/MIT',
                    'minimum_core_version' => null,#"6.5.5",
                    'name' => $custom_plugin['name'],
                    'package_name' => $custom_plugin['package_name'],
                    'ratings_avg' => 0,
                    'ratings_count' => 0,
                    'screenshots' => null,
                    'type' => $custom_plugin['type'],
                    'url' => $update['url'],
                ];
            })->filter()->hk2up_paginate($per_page, page: $current_page);
            /**
             * @var LengthAwarePaginator $return
             */
            $paginate = $return->toArray();
            $links = $paginate['links'];
            $data = $paginate['data'];
            unset($paginate['links'], $paginate['data']);
            return collect([
                'data' => $data,
                'links' => $links,
                'message' => null,
                'meta' => $paginate,
                'success' => true,
            ]);
        }
        return parent::list($request);
    }
}

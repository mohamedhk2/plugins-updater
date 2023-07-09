<?php

namespace HK2\PluginsUpdater\Providers;

use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class HK2PluginsUpdaterServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        $this->setNamespace(HK2_UPDATER_PLUGIN_NAME)
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes();

        $this->app->booted(function () {
            $this->app->register(HookServiceProvider::class);
        });
    }

    public function register(): void
    {
        $this->setNamespace('plugins/hk2-plugins-updater')->loadHelpers();

        /**
         * Paginate a standard Laravel Collection.
         *
         * @param int $perPage
         * @param int $total
         * @param int $page
         * @param string $pageName
         * @return array
         *
         * thanks to simonhamp <https://github.com/simonhamp>
         * @source https://gist.github.com/simonhamp/549e8821946e2c40a617c85d2cf5af5e
         */
        Collection::macro('hk2up_paginate', function($perPage, $total = null, $page = null, $pageName = 'page'): LengthAwarePaginator {
            $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);
            return new LengthAwarePaginator(
                $this->forPage($page, $perPage)->values(),
                $total ?: $this->count(),
                $perPage,
                $page,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => $pageName,
                ]
            );
        });
    }
}

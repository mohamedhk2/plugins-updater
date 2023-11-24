<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;
use \HK2\PluginsUpdater\Http\Controllers\HK2PluginsUpdaterController;

Route::group(['namespace' => 'Botble\PluginManagement\Http\Controllers', 'middleware' => ['web', 'core']], function () {
    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {
        Route::group(['prefix' => 'plugins/marketplace'], function () {
            Route::group(['prefix' => 'ajax'], function () {
                Route::post('{id}/install', [
                    'as' => 'plugins.marketplace.ajax.install',
                    'uses' => '\\' . HK2PluginsUpdaterController::class . '@install',
                    'middleware' => 'preventDemo',
                ]);
                Route::post('{id}/update/{name?}', [
                    'as' => 'plugins.marketplace.ajax.update',
                    'uses' => '\\' . HK2PluginsUpdaterController::class . '@update',
                    'middleware' => 'preventDemo',
                ]);
                Route::post('/check-update', [
                    'as' => 'plugins.marketplace.ajax.check-update',
                    'uses' => '\\' . HK2PluginsUpdaterController::class . '@checkUpdate',
                ]);
                Route::get('plugins', [
                    'as' => 'plugins.marketplace.ajax.list',
                    'uses' => '\\' . HK2PluginsUpdaterController::class . '@list',
                ]);
            });
        });
    });
});


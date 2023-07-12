<?php

const HK2_UPDATER_PLUGIN_NAME = 'plugins/hk2-plugins-updater';
const HK2_UPDATER_LANG_NAME = 'hk2-updater';
const HK2_UPDATER_GITHUB_SETTING_NAME = 'hk2_github_api_key';
const HK2_UPDATER_FORCE_TOKEN_SETTING_NAME = 'hk2_updater_force_token';
const HK2_UPDATER_PLUGINS_FILE = 'Data/plugins.json';
const HK2_UPDATER_UPDATE_DIR = 'app/marketplace';
const HK2_UPDATER_CUSTOM_PLUGINS_FILE = HK2_UPDATER_UPDATE_DIR . DIRECTORY_SEPARATOR . 'custom-plugins.json';
const HK2_UPDATER_CUSTOM_PLUGINS_SCHEMA = 'schema/custom-plugins.json';
const HK2_UPDATER_UPDATE_FILE = HK2_UPDATER_UPDATE_DIR . DIRECTORY_SEPARATOR . 'updates.json';
const HK2_UPDATER_MARKETPLACE_TYPE_SETTING_NAME = 'hk2_updater_marketplace_type';
const HK2_UPDATER_DEFAULT_MARKETPLACE = 'default';
const HK2_UPDATER_CUSTOM_MARKETPLACE = 'custom';
const HK2_UPDATER_OVERRIDES_MARKETPLACE = 'overrides';

const HK2_PLUGINS = [
    [
        'type' => 'plugin',
        'id' => '96936d08-2e17-4c3d-ac07-48ccf7fe0b60',
        'package_name' => 'hk2/hk2-plugins-updater',
        'name' => 'HK2 Plugins Updater',
        'url' => 'https://github.com/mohamedhk2/plugins-updater',
        'github_id' => 'mohamedhk2/plugins-updater',
        'use_token' => false,
    ],
    [
        'type' => 'plugin',
        'id' => '313e3fa0-61b3-4a58-b41e-df8e1c5f6eb6',
        'package_name' => 'hk2/hk2-botble-activator',
        'name' => 'HK2 Botble Activator',
        'url' => 'https://github.com/mohamedhk2/botble-activator',
        'github_id' => 'mohamedhk2/botble-activator',
        'use_token' => false,
    ],
];

function hk2up_trans($key = null, $replace = [], $locale = null)
{
    return trans(HK2_UPDATER_PLUGIN_NAME . '::' . HK2_UPDATER_LANG_NAME . '.' . $key, $replace, $locale);
}

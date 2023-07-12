# HK2 Plugins Updater

<img height=200 src="./screenshot.png"/>

- This plugin is used to install/update Botble plugins for free.
- You can use it to install/update your custom plugins (public or private).
- You can use it to override Botble Marketplace plugins.

#### Add custom plugins

- file path:
  `storage/app/marketplace/custom-plugins.json`

- file schema validation:
    ```json
    {
        "type": "array",
        "items": {
            "type": "array",
            "properties": {
                "type": {
                    "type": "string",
                    "enum": [
                        "plugin"
                    ]
                },
                "id": {
                    "type": "string",
                    "format": "uuid"
                },
                "package_name": {
                    "type": "string"
                },
                "name": {
                    "type": "string"
                },
                "url": {
                    "type": "string",
                    "format": "uri"
                },
                "github_id": {
                    "type": "string"
                },
                "use_token": {
                    "type": "boolean",
                    "default": false
                }
            },
            "required": [
                "type",
                "id",
                "package_name",
                "name",
                "url",
                "github_id"
            ]
        }
    }
    ```

- example:

    ```json
    [
        {
            "type": "plugin",
            "id": "96936d08-2e17-4c3d-ac07-48ccf7fe0b60",
            "package_name": "hk2/hk2-plugins-updater",
            "name": "HK2 Plugins Updater",
            "url": "https://github.com/mohamedhk2/plugins-updater",
            "github_id": "mohamedhk2/plugins-updater",
            "use_token": false
        }
    ]
    ```
  :warning: set `use_token` to `true` for private plugins and add your `GitHub token` in Settings page :warning:

#### Install custom plugins

1. Go to `Settings` page and set `Marketplace` option to `Custom`.
1. Go to `Plugins` page and click `+ Add new` button.

<div class=flexbox-annotated-section id=hk2-updater>
    <div class="flexbox-annotated-section-annotation">
        <div class="annotated-section-title pd-all-20">
            <h2>{{ hk2up_trans('name') }}</h2>
        </div>
        <div class="annotated-section-description pd-all-20 p-none-t">
            <p class=color-note>{{ hk2up_trans('description') }}</p>
        </div>
    </div>
    <div class=flexbox-annotated-section-content>
        <div class="wrapper-content pd-all-20">
            <x-core-setting::text-input
                :name="HK2_UPDATER_GITHUB_SETTING_NAME"
                :label="hk2up_trans('label')"
                :value="setting(HK2_UPDATER_GITHUB_SETTING_NAME, '')"
                :placeholder="hk2up_trans('placeholder')"
                data-counter=40
                helper-text="get it from: <a href=https://github.com/settings/tokens target=_blank>https://github.com/settings/tokens</a>"
            />
            <x-core-setting::checkbox
                name="{{ HK2_UPDATER_CUSTOM_ENABLE_SETTING_NAME }}"
                :label="hk2up_trans('marketplace')"
                :value="setting(HK2_UPDATER_CUSTOM_ENABLE_SETTING_NAME, false)"
            />
            <x-core-setting::checkbox
                name="{{ HK2_UPDATER_FORCE_TOKEN_SETTING_NAME }}"
                :label="hk2up_trans('foce_token')"
                :value="setting(HK2_UPDATER_FORCE_TOKEN_SETTING_NAME, false)"
            />
        </div>
    </div>
</div>

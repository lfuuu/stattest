+function ($) {
    'use strict';

    $(function () {
        $('.formReload').on('change', function () {
            document.getElementById(frontendVariables.voipRegistryEdit.registryFormId).submit();
        });
    })

}(
    jQuery,
    window.frontendVariables.voipRegistryEdit.registryFormId
);
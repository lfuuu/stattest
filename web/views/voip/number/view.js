+function ($) {
    'use strict';

    function numberSubmitForm(scenario) {
        $('#scenario').val(scenario);
        $('#' + frontendVariables.voipNumberView.numberFormId).submit();
    }

    function saveTechNumber() {
        $('#scenario').val('setTechNumber');
        $('#' + frontendVariables.voipNumberView.numberFormId).submit();
    }

    function numberHoldSubmitForm(hold_month) {
        $('#scenario').val('startHold');
        $('#hold_month').val(hold_month);
        $('#' + frontendVariables.voipNumberView.numberFormId).submit();
    }

}(
    jQuery,
    window.frontendVariables.voipNumberView.numberFormId
);
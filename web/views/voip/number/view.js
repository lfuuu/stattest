function numberSubmitForm(scenario) {
    $('#scenario').val(scenario);
    $('#' + window.frontendVariables.voipNumberView.numberFormId).submit();
}

function saveTechNumber() {
    $('#scenario').val('setTechNumber');
    $('#' + window.frontendVariables.voipNumberView.numberFormId).submit();
}

function numberHoldSubmitForm(hold_month) {
    $('#scenario').val('startHold');
    $('#hold_month').val(hold_month);
    $('#' + window.frontendVariables.voipNumberView.numberFormId).submit();
}


+function ($) {
    'use strict';

    // Nothing

}(jQuery);
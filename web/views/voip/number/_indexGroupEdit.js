+function ($) {
    'use strict';

    $(function () {
        // не form.submit, потому что на форму навешаны обработчики онлайн-валидации, начинается дубрирование событий и другие глюки
        $('.group-submit-button').on('click', function () {
            return confirm('Установить всем отфильтрованным записям новые значения? Это необратимо.');
        });

        if (frontendVariables.voipNumberIndexGroupEdit.numberGroupEditClientAccountId) {
            $('#number-status').on('change', function (e, item) {
                var clientAccountId = '';
                if ($(this).val() == frontendVariables.voipNumberIndexGroupEdit.numberGroupEditStatus) {
                    clientAccountId = frontendVariables.voipNumberIndexGroupEdit.numberGroupEditClientAccountId;
                } else {
                    clientAccountId = frontendVariables.voipNumberIndexGroupEdit.numberGroupEditIsNull;
                }
                $('#number-client_id').val(clientAccountId);
            });
        }
    })

}(
    jQuery,
    window.frontendVariables.voipNumberIndexGroupEdit.numberGroupEditClientAccountId,
    window.frontendVariables.voipNumberIndexGroupEdit.numberGroupEditStatus,
    window.frontendVariables.voipNumberIndexGroupEdit.numberGroupEditIsNull
);
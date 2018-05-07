// Обновляем MSISDN текущей сим-карты и первой свободной симкарты с определенным статусом
$('#submitButtonChangeMSISDN').click(function () {
    var originCard = $('#origin_card');
    var virtualCard = $('#virtual_card');

    $('#submitButtonChangeMSISDN').attr('disabled', true);
    $.ajax({
        type: 'post',
        url: '/sim/card/change-msisdn',
        data: {
            'cards_iccid': {
                'origin': originCard.find('#card-iccid').attr('value'),
                'virtual': virtualCard.find('#virtualcard-iccid').attr('value')
            }
        }
    }).done(function (result) {
        if (result.status === 'success') {
            originCard.find('.signature_msisdn')
                .first().attr('value', result.data.msisdn.origin);
            virtualCard.find('.signature_msisdn')
                .first().attr('value', result.data.msisdn.virtual);
        }
        displayFlashMessage(result.status, result.message, '#submitButtonChangeMSISDN');
    });
});

// Метод создания OriginCard
$('#submitButtonCreateCard').click(function () {
    var originCard = $('#origin_card');
    $('#submitButtonCreateCard').attr('disabled', true);
    $.ajax({
        type: 'post',
        url: '/sim/card/create-card',
        data: originCard.serialize()
    }).done(function (result) {
        if (result.status === 'success') {
            window.location.href = result.data.redirect;
        } else {
            displayFlashMessage(result.status, result.message, '#submitButtonCreateCard');
        }
    });
});

// Метод обновления OriginCard
$('#submitButtonOriginCard').click(function () {
    var originCard = $('#origin_card');
    $('#submitButtonOriginCard').attr('disabled', true);
    $.ajax({
        type: 'post',
        url: '/sim/card/update-card',
        data: originCard.serialize()
    }).done(function (result) {
        displayFlashMessage(result.status, result.message, '#submitButtonOriginCard');
    });
});

// Метод обновления VirtualCard
$('#submitButtonVirtualCard').click(function () {
    var virtualCard = $('#virtual_card');
    $('#submitButtonVirtualCard').attr('disabled', true);
    $.ajax({
        type: 'post',
        url: '/sim/card/update-card',
        data: virtualCard.serialize()
    }).done(function (result) {
        displayFlashMessage(result.status, result.message, '#submitButtonVirtualCard');
    });
});

// Уведомляем о результате операции и удаляем сообщение через 10 секунд
function displayFlashMessage(type, message, button_id) {
    $('.layout-content').before(
        '<div id="flash_message" style="font-weight: bold;" class="alert alert-' + type + ' fade in text-center">' + message + '</div>'
    );
    setTimeout(function () {
        $('#flash_message').remove();
        $(button_id).attr('disabled', false);
    }, 10000);
}
// Метод обмена MSISDN между SIM-картами
$('#buttonBetweenCards').click(function () {
    let originCard = $('#origin_card');
    let virtualCard = $('#virtual_card');

    // Отключаем кнопку пока выполняется запрос
    $('#buttonBetweenCards').attr('disabled', true);

    // Добавляем предупреждение
    displayFlashMessage('warning', 'Не обновляйте и не закрывайте страницу, пока операция не будет завершена');

    $.ajax({
        type: 'post',
        url: $('#buttonBetweenCards').attr('value'),
        data: {
            'cards_iccid': {
                'origin': originCard.find('#card-iccid').attr('value'),
                'virtual': virtualCard.find('#virtualcard-iccid').attr('value')
            }
        }
    }).done(function (response) {
        if (response.status === 'success') {
            originCard.find('.signature_msisdn').first().attr('value', response.data.msisdn.origin);
            virtualCard.find('.signature_msisdn').first().attr('value', response.data.msisdn.virtual);
        }
        $('#flash_message').remove();
        handleResult(response.status, response.message, '#buttonBetweenCards');
    });
});

// Метод обмена MSISDN между SIM-картой и неназначенным номером
$('#buttonUnassignedNumber').click(function () {
    let originCard = $('#origin_card');
    let unassigned_number = $('#unassigned_number');

    // Отключаем кнопку пока выполняется запрос
    $('#buttonUnassignedNumber').attr('disabled', true);

    $.ajax({
        type: 'post',
        url: $('#buttonUnassignedNumber').attr('value'),
        data: {
            'origin_iccid': originCard.find('#card-iccid').attr('value'),
            'unassigned_number': unassigned_number.attr('value')
        }
    }).done(function (response) {
        if (response.status === 'success') {
            originCard.find('.signature_msisdn').first().attr('value', response.data.msisdn_origin);
            unassigned_number.attr('value', response.data.unassigned_number);
        }
        handleResult(response.status, response.message, '#buttonUnassignedNumber');
    });
});

// Метод замены потерянной SIM-карты
$('#buttonLostCard').click(function () {
    let originCard = $('#origin_card');
    let virtualCard = $('#virtual_card');

    // Отключаем кнопку пока выполняется запрос
    $('#buttonLostCard').attr('disabled', true);

    $.ajax({
        type: 'post',
        url: $('#buttonLostCard').attr('value'),
        data: {
            'cards_iccid': {
                'origin': originCard.find('#card-iccid').attr('value'),
                'virtual': virtualCard.find('#virtualcard-iccid').attr('value')
            }
        }
    }).done(function (response) {
        if (response.status === 'success') {
            originCard.find('.signature_msisdn').first().attr('value', response.data.msisdn_origin);
            unassigned_number.attr('value', response.data.unassigned_number);
        }
        handleResult(response.status, response.message, '#buttonLostCard');
    });
});

// Метод создания OriginCard
$('#submitButtonCreateCard').click(function () {
    let origin_card = $('#origin_card');
    $('#submitButtonCreateCard').attr('disabled', true);
    $.ajax({
        type: 'post',
        url: '/sim/card/create-card',
        data: origin_card.serialize()
    }).done(function (result) {
        if (result.status === 'success') {
            window.location.href = result.data.redirect;
        } else {
            handleResult(result.status, result.message, '#submitButtonCreateCard');
        }
    });
});

// Метод обновления OriginCard
$('#submitButtonOriginCard').click(function () {
    let origin_card = $('#origin_card');
    $('#submitButtonOriginCard').attr('disabled', true);
    $.ajax({
        type: 'post',
        url: '/sim/card/update-card',
        data: origin_card.serialize()
    }).done(function (result) {
        handleResult(result.status, result.message, '#submitButtonOriginCard');
    });
});

// Метод обновления VirtualCard
$('#submitButtonVirtualCard').click(function () {
    let virtual_card = $('#virtual_card');
    $('#submitButtonVirtualCard').attr('disabled', true);
    $.ajax({
        type: 'post',
        url: '/sim/card/update-card',
        data: virtual_card.serialize()
    }).done(function (result) {
        handleResult(result.status, result.message, '#submitButtonVirtualCard');
    });
});

// Обработка результата запроса выводом сообщения с результатом, разблокировка кнопки
function handleResult(type, message, button_id) {
    displayFlashMessage(type, message);
    setTimeout(function () {
        $('#flash_message').remove();
        $(button_id).attr('disabled', false);
    }, 10000);
}

// Уведомляем о результате операции и удаляем сообщение через 10 секунд
function displayFlashMessage(type, message) {
    $('.layout-content').before(
        '<div id="flash_message" style="font-weight: bold;" class="alert alert-' + type + ' fade in text-center">' + message + '</div>'
    );
}
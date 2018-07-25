// Событие создания OriginCard
$('#submitButtonCreateCard').click(function () {
    let origin_card = $('#origin_card');
    $('#submitButtonCreateCard').attr('disabled', true);
    $.ajax({
        type: 'post',
        url: '/sim/card/create-card',
        data: origin_card.serialize()
    }).done(function (response) {
        if (response.status === 'success') {
            window.location.href = response.data.redirect;
        } else {
            $('#submitButtonCreateCard').attr('disabled', false);
            displayMessage(response.status, response.message);
        }
    });
});

// Событие обновления OriginCard
$('#submitButtonOriginCard').click(function () {
    updateCard($('#origin_card'), '#submitButtonOriginCard');
});

// Событие обновления VirtualCard
$('#submitButtonVirtualCard').click(function () {
    updateCard($('#virtual_card'), '#submitButtonVirtualCard',);
});

// Метод обмена MSISDN между SIM-картами
$('#buttonBetweenCards').click(function () {
    let originCard = $('#origin_card');
    let virtualCard = $('#virtual_card');
    // Отключаем кнопку пока выполняется запрос
    $('#buttonBetweenCards').attr('disabled', true);
    // Добавляем предупреждение
    displayMessage('warning', 'Не обновляйте и не закрывайте страницу, пока операция не будет завершена');
    // Выполняем запрос на сервер
    $.ajax({
        type: 'post',
        url: $('#buttonBetweenCards').attr('value'),
        data: {
            'origin_imsi': getImsi(originCard),
            'virtual_imsi': getImsi(virtualCard),
        }
    }).done(function (response) {
        $('#flash_message').remove();
        if (response.status === 'success') {
            setMsisdn(originCard, response.data.origin_msisdn);
            setMsisdn(virtualCard, response.data.virtual_msisdn);
        }
        $('#buttonBetweenCards').attr('disabled', false);
        displayMessage(response.status, response.message);
    });
});

// Метод обмена MSISDN между SIM-картой и неназначенным номером
$('#buttonUnassignedNumber').click(function () {
    let originCard = $('#origin_card');
    // Отключаем кнопку пока выполняется запрос
    $('#buttonUnassignedNumber').attr('disabled', true);
    // Добавляем предупреждение
    displayMessage('warning', 'Не обновляйте и не закрывайте страницу, пока операция не будет завершена');
    // Выполняем запрос на сервер
    $.ajax({
        type: 'post',
        url: $('#buttonUnassignedNumber').attr('value'),
        data: {
            'origin_imsi': getImsi(originCard),
            'virtual_number': $('#raw_number').attr('value'),
        }
    }).done(function (response) {
        $('#flash_message').remove();
        if (response.status === 'success') {
            setMsisdn(originCard, response.data.origin_msisdn);
            $('#virtual_number').text(response.data.virtual_number);
        }
        $('#buttonUnassignedNumber').attr('disabled', false);
        displayMessage(response.status, response.message);
    });
});

// Общая функция обновления модели Card
function updateCard(object, button_id) {
    $(button_id).attr('disabled', true);
    $.ajax({
        type: 'post',
        url: '/sim/card/update-card',
        data: object.serialize()
    }).done(function (response) {
        $(button_id).attr('disabled', false);
        displayMessage(response.status, response.message);
        // Удаление сообщения при нажатии
        $('#flash_message').click(function () {
            $(this).alert('close');
        });
    });
}

// Функция отображения сообщения
function displayMessage(type, message) {
    $('.layout-content').before(
        '<div id="flash_message" style="font-weight: bold;" class="alert alert-' + type + ' fade in text-center">' +
        message +
        '</div>'
    );
}

// Блокировка поля ввода raw_number, если в поле warehouse_status выбрано какое-либо значение
$('#warehouse_status').on('change', function (e) {
    if ($(this).select2('data')[0].id !== '') {
        $('#raw_number').val(null);
        $("#raw_number").prop('disabled', true);
    } else {
        $("#raw_number").prop('disabled', false);
    }
});
// Блокировка поля warehouse_status, если в поле raw_number выбрано какое-либо значение
$('#raw_number').on('change', function (e) {
    if ($(this).val() !== '') {
        $('#warehouse_status').val(null).trigger('change.select2');
        $("#warehouse_status").prop('disabled', true);
    } else {
        $("#warehouse_status").prop('disabled', false);
    }
});

// Функция получения imsi значения из таблицы, основным условием является статус MVNO-патнера, равным одному
function getImsi(object) {
    let result = null;
    $.each((object).find('.chargePeriod .table tbody tr'), function (key, value) {
        let cells = value.cells;
        let partner = null;
        for (let i = 0; i < cells.length; i++) {
            if (cells[i].className === 'list-cell__imsi') {
                result = cells[i].firstChild.value;
                if (partner) {
                    break;
                }
            }
            if (cells[i].className === 'list-cell__partner_id') {
                partner = cells[i].firstChild.firstChild.value;
                if (result) {
                    break;
                }
            }
        }
        if (result && parseInt(partner) === 1) {
            return false;
        } else {
            result = null;
            partner = null;
        }
    });
    return result;
}

// Функция установки msisdn значения из полученных параметров, основным условием является статус MVNO-патнера, равным одному
function setMsisdn(object, msisdn) {
    $.each((object).find('.chargePeriod .table tbody tr'), function (key, value) {
        let cells = value.cells;
        let isChanged = false;
        let partner = null;
        for (let i = 0; i < cells.length; i++) {
            if (cells[i].className === 'list-cell__msisdn') {
                cells[i].firstChild.firstChild.value = msisdn;
                isChanged = true;
                if (partner) {
                    break;
                }
            }
            if (cells[i].className === 'list-cell__partner_id') {
                partner = cells[i].firstChild.firstChild.value;
                if (isChanged) {
                    break;
                }
            }
        }
        if (isChanged && parseInt(partner) === 1) {
            return false;
        } else {
            isChanged = null;
            partner = null;
        }
    });
}
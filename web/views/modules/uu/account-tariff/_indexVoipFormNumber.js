

$(document).ready(function() {
    // Обработчик для текстовых input'ов с классом esim
    $('input[type="text"].esim').on('blur', function() {
        var input = $(this);
        var id = input.data('id');
        var value = input.val().trim();

        // Если значение не изменилось, ничего не делаем
        // if (input.data('last-saved') === value) return;

        // Очищаем предыдущие сообщения об ошибках
        input.next('.error-message').remove();
        input.removeClass('error');

        // Если поле пустое, ничего не делаем
        if (!value && input.data('last-saved') == '') return;

        // AJAX запрос на сервер
        $.ajax({
            url: '/sim/card/set-esim-iccid',
            method: 'POST',
            data: {
                id: id,
                iccid: value
            },
            dataType: 'json',
            beforeSend: function() {
                input.addClass('saving');
                input.prop('disabled', true)
            },
            success: function(response) {
                input.removeClass('saving');
                input.prop('disabled', false)

                if (response.success) {
                    // Успешное сохранение - запоминаем последнее сохраненное значение
                    input.data('last-saved', value);

                    // Опционально: заменяем input на div с сохраненным значением
                    // var savedDiv = $('<div>', {
                    //     'class': 'esim-saved-value',
                    //     'text': value,
                    //     'id': 'saved-' + id
                    // });
                    // input.replaceWith(savedDiv);

                    // Или просто показываем успешное сохранение
                    input.css('border-color', '#4CAF50');
                    input.css('color', '#4CAF50');
                    setTimeout(function() {
                    //     input.css('border-color', '');
                            input.css('color', '');
                    }, 1500);
                } else {
                    // Ошибка от сервера
                    input.addClass('error');

                    // Показываем сообщение об ошибке
                    var errorSpan = $('<span>', {
                        'class': 'error-message',
                        'text': response.message || 'Ошибка сохранения'
                    });

                    input.after(errorSpan);
                }
            },
            error: function(xhr, status, error) {
                input.removeClass('saving');
                input.addClass('error');
                input.prop('disabled', false)

                var errorSpan = $('<span>', {
                    'class': 'error-message',
                    'text': 'Ошибка соединения: ' + error
                });

                input.after(errorSpan);
            }
        });
    });

    // восстановление редактирования при клике на сохраненное значение
    // $(document).on('click', '.esim-saved-value', function() {
    //     var div = $(this);
    //     var id = div.attr('id').replace('saved-', '');
    //     var value = div.text();
    //
    //     var input = $('<input>', {
    //         type: 'text',
    //         id: id,
    //         class: 'esm',
    //         val: value
    //     });
    //
    //     div.replaceWith(input);
    //     input.focus();
    // });
});

(function ($) {
    "use strict";

    var $filterQueryId = $('#filterQueryId');
    var $filterQueryName = $('#filterQueryName');

    var $filterQueryButtonAdd = $('#filterQueryButtonAdd');
    var $filterQueryButtonLoad = $('#filterQueryButtonLoad');
    var $filterQueryButtonReplace = $('#filterQueryButtonReplace');
    var $filterQueryButtonDelete = $('#filterQueryButtonDelete');

    $filterQueryId
        .on('showHideButtons', function () {
            if ($filterQueryId.val()) {
                // выбран сохраненный фильтр
                $filterQueryButtonAdd.hide();
                $filterQueryButtonLoad.show();
                $filterQueryButtonReplace.show();
                $filterQueryButtonDelete.show();
            } else if ($filterQueryName.val()) {
                // введено название нового фильтра
                $filterQueryButtonAdd.show();
                $filterQueryButtonLoad.hide();
                $filterQueryButtonReplace.hide();
                $filterQueryButtonDelete.hide();
            } else {
                // пусто
                $filterQueryButtonAdd.hide();
                $filterQueryButtonLoad.hide();
                $filterQueryButtonReplace.hide();
                $filterQueryButtonDelete.hide();
            }
        })
        .trigger('showHideButtons'); // ini


    // при вводе с клавиатуры - сбросить ранее выбранный фильтр
    $filterQueryName.on('keyup', function () {
        $filterQueryId.val('');
        $filterQueryId.trigger('showHideButtons');
    });

    // обработка клика на кнопки
    var filterQueryAjax = function (filterQueryAction) {
        $.ajax({
            url: window.location.href,
            method: 'POST',
            dataType: 'json',
            data: {
                'filterQueryAction': filterQueryAction,
                'filterQueryId': $filterQueryId.val(),
                'filterQueryName': $filterQueryName.val()
            },
            success: function (data) {
                if (data && data.location) {
                    window.location = data.location;
                } else {
                    window.location.reload();
                }
            },
            error: function (jqXHR, textStatus) {
                alert('Ошибка: ' + jqXHR.responseText);
            }
        });
    };

    // кнопка "создать новый"
    $filterQueryButtonAdd.on('click', function () {
        filterQueryAjax('add');
    });

    // кнопка "загрузить"
    $filterQueryButtonLoad.on('click', function () {
        filterQueryAjax('load');
    });

    // кнопка "заменить"
    $filterQueryButtonReplace.on('click', function () {
        if (!confirm('Ранее сохраненный фильтр будет удален, и вместо него сохранены текущие значения. Точно заменить?')) {
            return;
        }
        filterQueryAjax('replace');
    });

    // кнопка "удалить"
    $filterQueryButtonDelete.on('click', function () {
        if (!confirm('Точно удалить ранее сохраненный фильтр?')) {
            return;
        }
        filterQueryAjax('delete');
    });

})(window.jQuery);
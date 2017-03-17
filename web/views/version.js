+function ($) {
    'use strict';

    $(function () {
        $('.btn-delete-version').on('click', function () {
            if (confirm('Удалить версию?')) {
                var t = $(this),
                    params = {
                        model: t.data('model'),
                        modelId: t.data('model-id'),
                        date: t.data('date')
                    };

                $.getJSON('/version/delete', params, function (data) {
                    if (data['status'] == 'ok') {
                        window.location.reload(true);
                    } else {
                        alert('Ошибка удаления версии');
                    }
                })
            }
        })
    })

}(jQuery);
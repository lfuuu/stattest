+function ($) {
    'use strict';

    $(function () {
        $('div.row-ls').on('click', function () {
            self.location.href = '/client/view?id=' + $(this).data('contract-id');
        });

        $('.active-client').closest('.contragent-wrap').addClass('active-contragent');
        $('.active-client-mcm').closest('.contragent-wrap').addClass('active-contragent-mcm');

        $('.set-block').click(function (e) {
            e.stopPropagation();
            var id = $(this).data('id');
            t = $(this);
            if (confirm('Вы уверены, что хотите ' + t.text().toLowerCase().trim() + ' ЛС № ' + id + '?')) {
                if (t.hasClass('btn-danger')) {
                    t.addClass('btn-success').removeClass('btn-danger').text('Заблокировать');
                } else {
                    t.addClass('btn-danger').removeClass('btn-success').text('Разблокировать');
                }

                location.href = '/account/set-block?id=' + id;
            }
        });

        $('.set-voip-disabled').click(function (e) {
            e.stopPropagation();
            var id = $(this).data('id');
            t = $(this);
            if (confirm(t.hasClass('btn-danger') ? 'Выключить локальную блокировку' : 'Включить локальную блокировку')) {
                if (t.hasClass('btn-danger')) {
                    t.addClass('btn-success').removeClass('btn-danger').text('Лок. блок.').attr('title', 'Включить локальную блокировку');
                } else {
                    t.addClass('btn-danger').removeClass('btn-success').text('Лок. разблок.').attr('title', 'Выключить локальную блокировку');
                }

                location.href = '/account/set-voip-disable?id=' + id;
            }
        });
    })

}(jQuery);
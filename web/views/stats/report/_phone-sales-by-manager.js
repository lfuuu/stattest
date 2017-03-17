+function ($) {
    'use strict';

    $(function () {
        $('span.details-link').click(function(e) {
            if ( $(this).data().details != 'undefined') {
                var details = $(this).data().details;

                $.post(
                    './index_lite.php?module=stats&action=report_by_one_manager',
                    {
                        data: JSON.stringify(details)
                    },
                    function(r) {
                        var $table = $('<table class="details-table"></table>'),
                            tRowHead = $(
                                '<thead><tr>' +
                                '<th>ID</th>' +
                                '<th>Менеджер</th>' +
                                '<th>Регион</th>' +
                                '<th>Контрагент</th>' +
                                '<th>Актуально с</th>' +
                                '<th>Клиент</th>' +
                                '<th>Тип</th>' +
                                '<th>E164</th>' +
                                '<th>Количество линий</th>' +
                                '<th>Статус</th>' +
                                '<th>Адрес</th>' +
                                '</tr></thead>'
                            );

                        $table.append(tRowHead);

                        if (r.status == 'OK' && r.data.length > 0) {
                            r.data.forEach(function (row) {
                                var tRow = $('<tr></tr>');

                                tRow.append('<td>' + row.id + '</td>');
                                tRow.append('<td>' + row.manager_name + '</td>');
                                tRow.append('<td>' + row.region + '</td>');
                                tRow.append('<td>' + row.contragent + '</td>');
                                tRow.append('<td>' + row.actual_from + '</td>');
                                tRow.append('<td>' + row.client + '</td>');
                                tRow.append('<td>' + row.type_id + '</td>');
                                tRow.append('<td>' + row.E164 + '</td>');
                                tRow.append('<td>' + row.no_of_lines + '</td>');
                                tRow.append('<td>' + row.status + '</td>');
                                tRow.append('<td><abbr title="' + row.address + '">?</abbr></td>');

                                $table.append(tRow);
                            });
                        }

                        $('#report_details')
                            .html('')
                            .dialog({
                                width: '80%',
                                height: 400,
                                open: function() {
                                    $(this).append($table);
                                }
                            });
                    },
                    'json'
                );
            }
        });
    })

}(jQuery);
(function ($) {
    "use strict";

    $.fn.gridViewDrivers = function(options) {
        var that = this,
            gridUrl = window.location.href;

        this.options = $.extend({
            batchSize: 5000
        }, options);

        this.showError = function($dialog, message) {
            $dialog.find('.dialog-step:visible').hide();
            $dialog
                .find('.dialog-step[data-step="error"]')
                .addClass('alert alert-danger')
                .text('Экспорт: ' + message)
                .show();
        };

        this.actionIteration = function(driver, columns, $dialog, iterations, key, offset) {
            $.ajax({
                url: gridUrl,
                dataType: 'json',
                method: 'GET',
                data: {
                    'action': 'iteration',
                    'driver': driver,
                    'key': key,
                    'offset': offset,
                    'columns': columns,
                    'batchSize': that.options.batchSize
                },
                success: function(data) {
                    if (data.success) {
                        var nextIteration = data.iteration++;
                        $dialog.find('.dialog-export-progress-bar div').css('width', ((100 / iterations) * (nextIteration + 1)) + '%');

                        if (nextIteration < iterations) {
                            var subTotal = data.iteration * that.options.batchSize;
                            $dialog.find('span.dialog-export-total').text(
                                (subTotal > data.total ? data.total : subTotal) + ' \/ ' + data.total
                            );
                            that.actionIteration(driver, columns, $dialog, iterations, key, data.iteration++);
                        } else {
                            $dialog.find('.dialog-step:visible').hide();
                            $dialog.find('.dialog-step[data-step="complete"]').show();
                            self.location.href = gridUrl + '&action=download&key=' + key + '&driver=' + driver;
                            setTimeout(function() {
                                $dialog.modal('hide');
                            }, 3000);
                        }
                    } else {
                        that.showError($dialog, 'не удалось загрузить данные по итерации №' + offset);
                    }
                },
                error: function() {
                    that.showError($dialog, '(backend) итерация №' + offset + ' не может быть завершена');
                }
            });
        };

        this.actionInit = function(driver, columns, $dialog) {
            var iterations = 0;

            $.ajax({
                url: gridUrl,
                dataType: 'json',
                method: 'GET',
                data: {
                    'action': 'init',
                    'driver': driver,
                    'columns': columns
                },
                success: function(data) {
                    if (data.total) {
                        $dialog.find('span.dialog-export-total').text('0 \/ ' + data.total);
                        $dialog.find('.dialog-step:visible').hide().next().show();
                        $dialog.find('.dialog-export-progress-bar div').css('width', 0);

                        iterations = Math.ceil(data.total / that.options.batchSize);

                        if (data.key && iterations) {
                            that.actionIteration(driver, columns, $dialog, iterations, data.key, 0);
                        }
                    } else {
                        that.showError($dialog, 'не удалось получить данные');
                    }
                },
                error: function() {
                    that.showError($dialog, '(backend) возникла ошибка на сервере. Возможно, не хватает параметров');
                }
            });
        };

        return this.each(function() {
            $(this).off('click').on('click', function(e) {
                e.preventDefault();

                var
                    columns = [],
                    uid = $(this).data('uid'),
                    driver = $(this).data('export-gridview-format');
                    $dialog = $('#' + uid + '-export-dialog');

                $('[data-export-menu="' + uid + '"] ul.export-checkbox-list input[type="checkbox"][data-key]:checked').each(function() {
                    columns.push($(this).data('key'));
                });

                $dialog.on('shown.bs.modal', function() {
                    that.actionInit(driver, columns, $dialog);
                });

                $dialog.on('hide.bs.modal', function() {
                    $(this).find('.dialog-step').hide().eq(0).show();
                });

                $dialog.modal({
                    keyboard: false
                });

                $dialog.modal('show');
            });
        });

    };

    $.fn.gridViewMenu = function() {
        return this.each(function() {
            var $that = $(this),
                $columns = $that.find('.export-checkbox-list li'),
                $toggle = $that.find('input[name="export_gridview_columns_toggle"]');

            $columns.off('click').on('click', function (e) {
                e.stopPropagation();
            });
            $toggle.off('change').on('change', function () {
                $columns
                    .find('input[name="export_gridview_columns[]"]:not([disabled])')
                    .prop('checked', $toggle.is(':checked'));
            });
        });
    };

})(window.jQuery);
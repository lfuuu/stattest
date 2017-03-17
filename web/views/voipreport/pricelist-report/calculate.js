+function ($) {
    'use strict';

    $(function () {
        var
            $currencyForm = $('form#pricelist-report-currency-frm'),
            $filterForm = $('form#pricelist-report-filter-frm'),
            $modifyForm = $('form#pricelist-report-modify-frm'),
            $exportForm = $('form#pricelist-export-frm'),
            $reportTable = $('#pricelist-report-tbl'),
            $exportBtn = $('#export-btn'),
            $loadingOverlay = $('.fullScreenOverlay'),
            sourceUrl = '/voipreport/pricelist-report/get-pricelist-data?reportId=' + frontendVariables.voipreportPricelistReportCalculate.pricelistReportId,
            delay = $.Deferred(),
            sourceData = [],
            resultCellStyle = function (value, row, index, field) {
                return {
                    classes: 'result-cell'
                };
            },
            loadData = function () {
                $.ajax({
                    url: sourceUrl + '&currency=' + $currencyForm.find('select[name="currency"]').val(),
                    method: 'get',
                    dataType: 'json',
                    beforeSend: function () {
                        $loadingOverlay.show();
                    },
                    success: function (data) {
                        sourceData = data.data;
                        $reportTable.bootstrapTable('load', sourceData);
                        $loadingOverlay.hide();
                    },
                    error: function () {
                        $.notify('Загрузка данных не удалась', 'error');
                        $loadingOverlay.hide();
                    }
                });
            },
            inRange = function (rangeFrom, rangeTo, value) {
                if (rangeFrom && rangeTo && value >= rangeFrom && value <= rangeTo) {
                    return true;
                }

                if (rangeFrom && !rangeTo && value >= rangeFrom) {
                    return true;
                }

                if (!rangeFrom && rangeTo && value <= rangeTo) {
                    return true;
                }

                return false;
            },
            filterCountry = function (data, countryId) {
                return $.grep(data, function (row) {
                    return row.country == countryId;
                });
            },
            filterRegion = function (data, regionId) {
                return $.grep(data, function (row) {
                    return row.region == regionId;
                });
            },
            filterIsMobile = function (data, isMobile) {
                return $.grep(data, function (row) {
                    return row.mob == isMobile;
                });
            };

        $reportTable.bootstrapTable({
            toolbar: '#pricelist-report-tbl-toolbar',
            toolbarAlign: 'right',
            pagination: true,
            pageSize: 1000,
            locale: 'ru-RU',
            paginationVAlign: 'top',
            pageList: []
        });

        loadData();

        $currencyForm.find('button').on('click', function () {
            loadData();
            return false;
        });

        $filterForm.find('button').on('click', function () {
            var
                countryId = $filterForm.find('select[name="country_id"]').val(),
                regionId = $filterForm.find('select[name="region_id"]').val(),
                isMobile = $filterForm.find('input[name="is_mobile"]:checked').val(),
                data = sourceData;

            $loadingOverlay.show();
            setTimeout(function() { delay.resolve(); }, 100);
            delay
                .promise()
                .then(function () {
                    if (countryId) {
                        data = filterCountry(data, countryId);
                    }

                    if (regionId) {
                        data = filterRegion(data, regionId);
                    }

                    if (isMobile >= 0) {
                        data = filterIsMobile(data, isMobile);
                    }

                    $reportTable.bootstrapTable('load', data);
                })
                .done(function () {
                    $loadingOverlay.hide();
                });

            return false;
        });

        $modifyForm.find('button').on('click', function () {
            var
                countryId = $filterForm.find('select[name="country_id"]').val(),
                regionId = $filterForm.find('select[name="region_id"]').val(),
                isMobile = $filterForm.find('input[name="is_mobile"]:checked').val(),
                $elements = $modifyForm.find('.multiple-input').find('tr.multiple-input-list__item'),
                priceType = $modifyForm.find('input[name="best_price"]:checked').val(),
                modifiers = [],
                totalModified = 0,
                data = sourceData;

            $elements.each(function () {
                var $fields = $(this).find('input, select'),
                    modifier = {
                        range: [],
                        profit: {}
                    };

                $fields.each(function () {
                    var attribute = $(this).attr('class').match(/multiple\-([^\s]+)/)[1],
                        value = $(this).val();

                    switch (attribute) {
                        case 'range':
                            modifier.range.push(value);
                            break;
                        case 'value':
                            modifier.profit.value = parseFloat(value);
                            break;
                        case 'variant':
                            modifier.profit.variant = value;
                            break;
                        case 'price':
                            modifier.profit.summary = parseFloat(value);
                            break;
                    }
                });

                modifiers.push(modifier);
            });

            $loadingOverlay.show();
            setTimeout(function() { delay.resolve(); }, 100);
            delay
                .promise()
                .then(function () {
                    if (countryId) {
                        data = filterCountry(data, countryId);
                    }

                    if (regionId) {
                        data = filterRegion(data, regionId);
                    }

                    if (isMobile >= 0) {
                        data = filterIsMobile(data, isMobile);
                    }

                    $.map(data, function (row) {
                        var price = parseFloat(row[priceType]);

                        $.each(modifiers, function () {
                            if (inRange(parseFloat(this.range[0]), parseFloat(this.range[1]), price)) {
                                totalModified++;

                                if (this.profit.summary && !this.profit.value) {
                                    row.modify_result = this.profit.summary;
                                } else {
                                    switch (this.profit.variant) {
                                        case 'money':
                                            row.modify_result = (price + this.profit.value).toFixed(4);
                                            break;
                                        case 'percent':
                                            row.modify_result = (price + ((price * this.profit.value) / 100)).toFixed(4);
                                            break;
                                    }
                                }
                            }
                        });

                        return row;
                    });

                    $filterForm.find('button').trigger('click');
                })
                .done(function () {
                    $loadingOverlay.hide();

                    $.notify('Изменено ' + totalModified + ' значений', 'success');
                });

            return false;
        });

        $exportBtn.on('click', function () {
            $exportForm.find('[name="data"]').val(JSON.stringify($reportTable.bootstrapTable('getData')));
            $exportForm.submit();
            return false;
        });
    })

}(
    jQuery,
    window.frontendVariables.voipreportPricelistReportCalculate.pricelistReportId
);
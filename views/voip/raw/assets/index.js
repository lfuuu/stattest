/**
 * Обработчики изменения фильтров, от которых зависят други фильтры
 */

$(function () {
    var settings = {"theme":"krajee","width":"100%","language":"ru-RU"};

    $('select[name="CallsRawFilter[server_ids][]"], select[name="CallsRawFilter[src_routes_ids][]"], select[name="CallsRawFilter[src_contracts_ids][]"]')
        .on('change', function () {
            var server_ids = $('select[name="CallsRawFilter[server_ids][]"]'),
                src_contracts_ids = $('select[name="CallsRawFilter[src_contracts_ids][]"]'),
                src_routes_ids = $('select[name="CallsRawFilter[src_routes_ids][]"]');

            if (!$(this).is(src_routes_ids))
                $.get("/voip/raw/get-routes", {
                    serverIds: server_ids.val(),
                    serviceTrunkId: src_contracts_ids.val()
                }, function (data) {
                    src_routes_ids.html(data).select2(settings);
                });
            if (!$(this).is(src_contracts_ids))
                $.get("/voip/raw/get-contracts", {
                    serverIds: server_ids.val(),
                    serviceTrunkId: src_routes_ids.val()
                }, function (data) {
                    src_contracts_ids.html(data).select2(settings);
                });
        });

    $('select[name="CallsRawFilter[server_ids][]"], select[name="CallsRawFilter[dst_routes_ids][]"], select[name="CallsRawFilter[dst_contracts_ids][]"]')
        .on('change', function () {
            var server_ids = $('select[name="CallsRawFilter[server_ids][]"]'),
                dst_contracts_ids = $('select[name="CallsRawFilter[dst_contracts_ids][]"]'),
                dst_routes_ids = $('select[name="CallsRawFilter[dst_routes_ids][]"]');

            if (!$(this).is(dst_routes_ids))
                $.get("/voip/raw/get-routes", {
                    serverIds: server_ids.val(),
                    serviceTrunkId: dst_contracts_ids.val()
                }, function (data) {
                    dst_routes_ids.html(data).select2(settings);
                });
            if (!$(this).is(dst_contracts_ids))
                $.get("/voip/raw/get-contracts", {
                    serverIds: server_ids.val(),
                    serviceTrunkId: dst_routes_ids.val()
                }, function (data) {
                    dst_contracts_ids.html(data).select2(settings);
                });
        });

    $('select[name="CallsRawFilter[src_contries_ids][]"], select[name="CallsRawFilter[src_regions_ids][]"]')
        .on('change', function () {
            var src_contries_ids = $('select[name="CallsRawFilter[src_contries_ids][]"]'),
                src_regions_ids = $('select[name="CallsRawFilter[src_regions_ids][]"]'),
                src_cities_ids = $('select[name="CallsRawFilter[src_cities_ids][]"]');

            if (!$(this).is(src_regions_ids))
                $.get("/voip/raw/get-regions", {
                    countryCodes: src_contries_ids.val()
                }, function (data) {
                    src_regions_ids.html(data).select2(settings);
                });

            $.get("/voip/raw/get-cities", {
                countryCodes: src_contries_ids.val(),
                regionIds: src_regions_ids.val()
            }, function (data) {
                src_cities_ids.html(data).select2(settings);
            });
        });

    $('select[name="CallsRawFilter[dst_contries_ids][]"], select[name="CallsRawFilter[dst_regions_ids][]"]')
        .on('change', function () {
            var dst_contries_ids = $('select[name="CallsRawFilter[dst_contries_ids][]"]'),
                dst_regions_ids = $('select[name="CallsRawFilter[dst_regions_ids][]"]'),
                dst_cities_ids = $('select[name="CallsRawFilter[dst_cities_ids][]"]');

            if (!$(this).is(dst_regions_ids))
                $.get("/voip/raw/get-regions", {
                    countryCodes: dst_contries_ids.val()
                }, function (data) {
                    dst_regions_ids.html(data).select2(settings);
                });
            $.get("/voip/raw/get-cities", {
                countryCodes: dst_contries_ids.val(),
                regionIds: dst_regions_ids.val()
            }, function (data) {
                dst_cities_ids.html(data).select2(settings);
            });
        });

});

+function ($) {
    'use strict';

    // Все эти танцы с бубном для правильной ajax-загрузки данных в грид без запросов данных для фильтров
    $(function () {
        $(document).off('change.yiiGridView', '.beforeHeaderFilters input, .beforeHeaderFilters select');

      $('.grid-view:eq(0)').removeAttr('id');

        $(document)
            .on('pjax:start', 'div[data-pjax-container]', function () {
                $(this).find('.kv-grid-container > .kv-grid-table').addClass('kv-grid-loading');
            })
            .on('pjax:end', 'div[data-pjax-container]', function () {
                $(this).find('.kv-grid-container > .kv-grid-table').removeClass('kv-grid-loading');
                $('div[data-pjax-container] > div').yiiGridView({'filterUrl':'', 'filterSelector':'.beforeHeaderFilters input, .beforeHeaderFilters select'});

                $('a[data-uid]').gridViewDrivers();

                $(document).off('change.yiiGridView', '.beforeHeaderFilters input, .beforeHeaderFilters select');
            });

        $('select[name="CallsRawFilter[server_ids][]"], ' +
          'select[name="CallsRawFilter[src_logical_trunks_ids][]"], ' +
          'select[name="CallsRawFilter[src_contracts_ids][]"], ' +
          'select[name="CallsRawFilter[src_physical_trunks_ids][]"]')
          .on('change', function () {
            var server_ids = $('select[name="CallsRawFilter[server_ids][]"]'),
              src_contracts_ids = $('select[name="CallsRawFilter[src_contracts_ids][]"]'),
              src_logical_trunks_ids = $('select[name="CallsRawFilter[src_logical_trunks_ids][]"]'),
              src_physical_trunks_ids = $('select[name="CallsRawFilter[src_physical_trunks_ids][]"]');

            if (!$(this).is(src_logical_trunks_ids))
              $.get("/voip/raw/get-logical-trunks", {
                serverIds: server_ids.val(),
                contractIds: src_contracts_ids.val(),
                trunkIds: src_physical_trunks_ids.val()
              }, function (data) {
                src_logical_trunks_ids.html(data).trigger('change.select2');
              });
            if (!$(this).is(src_contracts_ids))
              $.get("/voip/raw/get-contracts", {
                serverIds: server_ids.val(),
                serviceTrunkIds: src_logical_trunks_ids.val(),
                trunkIds: src_physical_trunks_ids.val()
              }, function (data) {
                src_contracts_ids.html(data).trigger('change.select2');
              });
            if (!$(this).is(src_physical_trunks_ids))
              $.get("/voip/raw/get-physical-trunks", {
                serverIds: server_ids.val(),
                serviceTrunkIds: src_logical_trunks_ids.val(),
                contractIds: src_contracts_ids.val()
              }, function (data) {
                src_physical_trunks_ids.html(data).trigger('change.select2');
              });
          });

        $('select[name="CallsRawFilter[server_ids][]"], ' +
          'select[name="CallsRawFilter[dst_logical_trunks_ids][]"], ' +
          'select[name="CallsRawFilter[dst_contracts_ids][]"], ' +
          'select[name="CallsRawFilter[dst_physical_trunks_ids][]"]')
          .on('change', function () {
            var server_ids = $('select[name="CallsRawFilter[server_ids][]"]'),
              dst_contracts_ids = $('select[name="CallsRawFilter[dst_contracts_ids][]"]'),
              dst_logical_trunks_ids = $('select[name="CallsRawFilter[dst_logical_trunks_ids][]"]'),
              dst_physical_trunks_ids = $('select[name="CallsRawFilter[dst_physical_trunks_ids][]"]');

            if (!$(this).is(dst_logical_trunks_ids))
              $.get("/voip/raw/get-logical-trunks", {
                serverIds: server_ids.val(),
                contractIds: dst_contracts_ids.val(),
                trunkIds: dst_physical_trunks_ids.val()
              }, function (data) {
                dst_logical_trunks_ids.html(data).trigger('change.select2');
              });
            if (!$(this).is(dst_contracts_ids))
              $.get("/voip/raw/get-contracts", {
                serverIds: server_ids.val(),
                serviceTrunkIds: dst_logical_trunks_ids.val(),
                trunkIds: dst_physical_trunks_ids.val()
              }, function (data) {
                dst_contracts_ids.html(data).trigger('change.select2');
              });
            if (!$(this).is(dst_physical_trunks_ids))
              $.get("/voip/raw/get-physical-trunks", {
                serverIds: server_ids.val(),
                serviceTrunkIds: dst_logical_trunks_ids.val(),
                contractIds: dst_contracts_ids.val()
              }, function (data) {
                dst_physical_trunks_ids.html(data).trigger('change.select2');
              });
          });

        $('select[name="CallsRawFilter[src_countries_ids][]"], select[name="CallsRawFilter[src_regions_ids][]"]')
          .on('change', function () {
            var src_countries_ids = $('select[name="CallsRawFilter[src_countries_ids][]"]'),
              src_regions_ids = $('select[name="CallsRawFilter[src_regions_ids][]"]'),
              src_cities_ids = $('select[name="CallsRawFilter[src_cities_ids][]"]');

            if (!$(this).is(src_regions_ids))
              $.get("/voip/raw/get-regions", {
                countryCodes: src_countries_ids.val()
              }, function (data) {
                src_regions_ids.html(data).trigger('change.select2');
              });

            $.get("/voip/raw/get-cities", {
              countryCodes: src_countries_ids.val(),
              regionIds: src_regions_ids.val()
            }, function (data) {
              src_cities_ids.html(data).trigger('change.select2');
            });
          });

        $('select[name="CallsRawFilter[dst_countries_ids][]"], select[name="CallsRawFilter[dst_regions_ids][]"]')
          .on('change', function () {
            var dst_countries_ids = $('select[name="CallsRawFilter[dst_countries_ids][]"]'),
              dst_regions_ids = $('select[name="CallsRawFilter[dst_regions_ids][]"]'),
              dst_cities_ids = $('select[name="CallsRawFilter[dst_cities_ids][]"]');

            if (!$(this).is(dst_regions_ids))
              $.get("/voip/raw/get-regions", {
                countryCodes: dst_countries_ids.val()
              }, function (data) {
                dst_regions_ids.html(data).trigger('change.select2');
              });
            $.get("/voip/raw/get-cities", {
              countryCodes: dst_countries_ids.val(),
              regionIds: dst_regions_ids.val()
            }, function (data) {
              dst_cities_ids.html(data).trigger('change.select2');
            });
          });
    });

}(jQuery);
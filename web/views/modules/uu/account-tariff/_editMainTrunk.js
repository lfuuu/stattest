+function ($, param) {
    'use strict';

    $(function () {
        var $region = $('#accounttariff-region_id');
        var $trunk = $('#accounttariff-trunk_id');
        var $type = $('#accounttariff-trunk_type_id');
        var $cnode = $('#accounttariff-calligrapher_node_id');

        // при изменении точки присоединения обновить список транков
        $region.on('change', function () {
            $.get(
                '/uu/voip/get-trunks', {
                    regionId: $(this).val(),
                    format: param.format
                }, function (html) {
                    $trunk
                        .html(html)
                        .trigger('change');
                }
            );
        });

        if ($cnode.length) {
            $region.on('change', function () {
                $.get(
                    '/uu/voip/get-calligrapher-nodes', {
                        regionId: $(this).val(),
                        format: param.format
                    }, function (html) {
                        $cnode
                            .html(html)
                            .trigger('change');
                    }
                );
            });
        }

        // при мультитранке выключить точку присоединения и список транков
        $type.on('change', function () {
            var $isMultiTrunk = (2 == $type.val()); // 2 - мультитранк
            if ($isMultiTrunk) {
                $region.val('').trigger('change');
                $trunk.val('').trigger('change');
            }
            $region.prop('disabled', $isMultiTrunk);
            $trunk.prop('disabled', $isMultiTrunk);
            if ($cnode.length) {
                $cnode.prop('disabled', $isMultiTrunk);
            }
        });

    })
}(
    jQuery,
    window.frontendVariables.modulesUuAccountTariffEditMainTrunk
);
/**
 * Requirements:
 * Every element into chain must get attributes:
 *      data-chained - contains jQuery selector for chain elements (example: [name*="region_id"], [name*="city_id"])
 *      data-chained-tag - contains option filter value for chain elements (example: [data-country-id="?"])
 *                          "?" it will be replaced at previous chain element value
 *
 * Usage:
 *   $('.chained-select').chained({
 *       'withSelect2': true
 *   });
 *
 * Flag "withSelect2" = false by default.
 * Can be need for Select2 plugin can set label value.
 */

;(function($, window, document, undefined) {
    'use strict';

    $.fn.chained = function(settings) {
        var $options = $.extend({}, this.defaults, settings),
            $backups = {};

        function updateChain() {
            var $chained = $($(this).data('chained')),
                $chained_tag = $(this).data('chained-tag'),
                $current = $(this).find(':selected');

            $chained.each(function() {
                var $name = $(this).attr('name'),
                    $selected = $(this).find(':selected');

                if (!$backups[ $name ]) {
                    $backups[$name] = $(this).find('option').clone();
                }

                var $items = $backups[ $name ].filter($chained_tag.replace(/\?/, $current.val()));

                $(this)
                    .find('option:gt(0)')
                    .detach();

                $(this)
                    .append($items)
                    .find('option[value="' + (!$items.length ? 0 : $selected.val()) +  '"]')
                    .prop('selected', true);

                if ($options.withSelect2 === true)
                    $(this).select2('val', !$items.length ? 0 : $selected.val())
            });
        }

        return this.each(function() {
            $(this).change(updateChain).trigger('change');
        });
    };

    $.fn.chained.defaults = {
        'withSelect2': false
    };

})(window.jQuery, window, document);
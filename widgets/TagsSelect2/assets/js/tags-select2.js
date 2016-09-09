(function ($) {
    "use strict";

    $.fn.tagsSelect2 = function(options) {
        var
            settings = $.extend({
                'url': ''
            }, options),
            applyAction = function(resource, resourceId, tags) {
                $.ajax({
                    url: settings.url,
                    data: {
                        'resource': resource,
                        'resource_id': resourceId,
                        'tags': tags
                    },
                    dataType: 'json',
                    method: 'POST'
                })
            };

        return this.each(function() {
            $(this)
                .on('select2:select', function() {
                    applyAction($(this).data('tags-resource'), $(this).data('tags-resource-id'), $(this).val());
                })
                .on('select2:unselect', function() {
                    applyAction($(this).data('tags-resource'), $(this).data('tags-resource-id'), $(this).val());
                });
        });
    };


})(window.jQuery);
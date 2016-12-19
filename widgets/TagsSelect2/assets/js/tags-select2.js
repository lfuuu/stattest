(function ($) {
    "use strict";

    $.fn.tagsSelect2 = function(options) {
        var
            settings = $.extend({
                'url': ''
            }, options),
            applyAction = function(resource, resourceId, feature, tags) {
                $.ajax({
                    url: settings.url,
                    data: {
                        'resource': resource,
                        'resource_id': resourceId,
                        'feature': feature,
                        'tags': tags
                    },
                    dataType: 'json',
                    method: 'POST',
                    success: function(data) {
                        if (!data || !data.response) {
                            $.notify('Внутренняя ошибка', 'error');
                        } else if (data.response == 'success') {
                            $.notify('Список меток изменен', 'success');
                        }
                    },
                    error: function() {
                        $.notify('Список меток не может быть добавлен', 'error');
                    }
                })
            };

        return this.each(function() {
            $(this)
                .on('select2:select', function() {
                    applyAction(
                        $(this).data('tags-resource'),
                        $(this).data('tags-resource-id'),
                        $(this).data('tags-feature'),
                        $(this).val());
                })
                .on('select2:unselect', function() {
                    applyAction(
                        $(this).data('tags-resource'),
                        $(this).data('tags-resource-id'),
                        $(this).data('tags-feature'),
                        $(this).val()
                    );
                });
        });
    };


})(window.jQuery);
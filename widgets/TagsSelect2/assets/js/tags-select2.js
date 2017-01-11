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
                });
            },
            applyEditable = function($editBtn) {
                $editBtn
                    .prevAll('.tags-resource-list').show() // Show list of tags selectBox
                    .prevAll('span').hide(); // Hide all existing tags
                $editBtn
                    .hide() // Hide self
                    .next('.disable-edit').show(); // Show disable button
                return false;
            },
            restoreEditable = function($disableEditBtn) {
                var $listOfTags = $disableEditBtn.prevAll('.tags-resource-list');

                $listOfTags.prevAll('span').remove(); // Destroy all existing tags

                // Build list of tags
                $listOfTags
                    .find('select option:selected')
                    .each(function() {
                        $listOfTags
                            .parent()
                            .prepend($('<span>').addClass('label label-info tags-label').text($(this).val()));
                    });

                // Hide list of tags selectBox
                $listOfTags.hide();

                $disableEditBtn
                    .hide() // Hide self
                    .prev('.tags-inline-edit').show(); // Show edit button
                return false;
            };

        return this.each(function() {
            $(this)
                .on('select2:select', function() {
                    applyAction(
                        $(this).data('tags-resource'),
                        $(this).data('tags-resource-id'),
                        $(this).data('tags-feature'),
                        $(this).val()
                    );
                })
                .on('select2:unselect', function() {
                    applyAction(
                        $(this).data('tags-resource'),
                        $(this).data('tags-resource-id'),
                        $(this).data('tags-feature'),
                        $(this).val()
                    );
                });

            $('.tags-inline-edit').on('click', function() {
                return applyEditable($(this));
            });

            $('.tags-inline-edit.disable-edit').on('click', function() {
                return restoreEditable($(this));
            });
        });
    };


})(window.jQuery);
jQuery(document).ready(function() {

    $('.media-manager').MultiFile({
        list: 'div.media-manager-block',
        max: 5,
        STRING: {
            remove: '',
            selected: 'Выбран файл: $file',
            toomany: 'Достигнуто максимальнное кол-во файлов',
            duplicate: 'Файл "$file" уже добавлен'
        },
        afterFileSelect: function(element, value, master_element) {
            var $block = master_element.list.find('div.MultiFile-label:last');

            $block
                .find('.MultiFile-remove')
                    .EditFileName({
                        'element': {
                            'name': $(element).attr('name'),
                            'value': value
                        }
                    });

            $block
                .find('.MultiFile-label')
                    .each(function() {
                        var
                            originalRemove = $(this).parents('div').find('a.MultiFile-remove');
                            remove =
                                $('<a />')
                                    .attr('href', 'javascript:void(0)')
                                    .text('Открепить')
                                    .on('click', function(e) {
                                        e.preventDefault();
                                        originalRemove.trigger('click');
                                    });

                        $(this)
                            .append(
                                $('<div />')
                                    .css({'margin-left':'25px'})
                                    .append(remove)
                            )
                    });
        }
    });

    $('div.media-list').MediaManager({
        'preview': {
            'width': 100,
            'height': 100
        }
    });
});

if (window.jQuery)(function($) {

    $.fn.MediaManager = function(options) {
        var $settings = $.extend({
            'preview': {
                'width': 150,
                'height': 150
            }
        }, options)

        return this.each(function() {
            var $container = $(this),
                $container_documents = $('<ul class="media-list-documents" />'),
                $container_image = $('<div class="media-list-images" />');

            $container
                .append($container_documents)
                .append($container_image);

            $container.find('div[data-model][data-mime-type^="image/"]').each(function() {
                var $element =
                    $('<div />')
                        .addClass('media_manager_image')
                        .text($(this).text())
                        .append(
                            $('<a />')
                                .attr('href', '/file/get-file/?model=' + $(this).data('model') + '&id=' + $(this).data('file-id'))
                                .attr('target', '_blank')
                                .append(
                                    $('<div />')
                                        .addClass('center')
                                        .append(
                                            $('<img />')
                                                .attr('src', '/file/get-file/?model=' + $(this).data('model') + '&id=' + $(this).data('file-id'))
                                                .nailthumb({
                                                    width: $settings.preview['width'],
                                                    height: $settings.preview['height'],
                                                    fitDirection: 'center',
                                                    nostyle: true
                                                })
                                    )
                            )
                );

                $container_image.append($element);
                $(this).detach();
            });

            $container.find('div[data-model]').each(function() {
                $container_documents
                    .append(
                        $('<li />')
                            .append(
                                $('<a />')
                                    .attr('href', '/file/get-file/?model=' + $(this).data('model') + '&id=' + $(this).data('file-id'))
                                    .attr('target', '_blank')
                                    .text($(this).text())
                            )
                );
                $(this).detach();
            });

        });
    };

    $.fn.EditFileName = function(options) {

        var
            $settings = $.extend({
                'element': {
                    'name': '',
                    'value': ''
                },
                'editBlock': {
                    'class': 'file_name_edit_block'
                },
                'editLink': {
                    'class': 'file_name_edit',
                    'title': 'Редактировать прикрепленный файл'
                },
                'editField': {
                    'namePrefix': 'custom_name',
                    'class': 'edit_input'
                },
                'editButton': {
                    'title': 'Ок',
                    'class': 'edit_button'
                }
            }, options),
            $elements = {
                'block':
                    $('<div />')
                        .addClass($settings.editBlock['class']),
                'link':
                    $('<div />')
                        .addClass($settings.editLink['class'])
                        .attr('title', $settings.editLink['class'])
                        .on('click', function() {
                            $(this)
                                .hide()
                                    .next('div')
                                        .show()
                                            .next('span')
                                                .hide();
                        }),
                'field':
                    $('<input />')
                        .attr('type', 'text')
                        .attr('name', $settings.editField['namePrefix'] + '_' + $settings.element.name)
                        .val($settings.element.value.replace(/\.[^\.]+$/, ''))
                        .addClass($settings.editField['class']),
                'button':
                    $('<button>')
                        .addClass($settings.editButton['class'])
                        .attr('type', 'button')
                        .text($settings.editButton['title'])
                        .on('click', function() {
                            var parent = $(this).parent('div'),
                                fileElement = parent.next('span').find('span.MultiFile-title'),
                                newFileName = $(this).prev('input').val(),
                                fileName = fileElement.text();

                            parent.hide().prev('div').show();
                            parent.next('span')
                                .show()
                                .find('span.MultiFile-title')
                                    .text(fileName.replace(/.*?(\.[^\.]+$)/, newFileName + '$1'));
                        })
            };

        return this.each(function() {
            $elements.link.insertAfter($(this).hide());
            $elements.block
                .append($elements.field)
                .append($elements.button)
                .insertAfter($elements.link);
        });
    }

})(jQuery);
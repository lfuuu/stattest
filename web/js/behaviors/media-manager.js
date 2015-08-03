jQuery(document).ready(function() {

    $('.media-manager').MultiFile({
        list: 'div.media-manager-block',
        max: 2,
        STRING: {
            remove: '<i class="uncheck" />',
            selected: 'Выбран файл: $file',
            toomany: 'Достигнуто максимальнное кол-во файлов',
            duplicate: 'Файл "$file" уже добавлен'
        },
        afterFileSelect: function(element, value, master_element) {
            master_element.list.find('div.MultiFile-label:last').append(
                $('<input />')
                    .attr('custom_name_' + element.attr('name'))
                    .attr('type', 'text')
                    .val(value)
            );
        },
    });

    $(document)
        .on('click', 'span.MultiFile-title', function() {
            var
                file_ext = $(this).text().match(/\.([^\.]+)$/),
                file_name = $(this).text().replace(file_ext[0], '');
            $(this).replaceWith(
                $('<div />')
                    .addClass('MultiFile-title')
                    .css({
                        'display':'inline-block',
                        'width':'80%'
                    })
                    .append(
                        $('<input />')
                            .addClass('')
                            .css({
                                'width':'80%'
                            })
                            .attr('type', 'text')
                            .val(file_name)
                    )
            )
        });

    var $container = $('div.media-list'),
        $container_documents = $('<ul class="media-list-documents" />'),
        $container_image = $('<div class="media-list-images" />');

    $container
        .append($container_documents)
        .append($container_image)
        .find('div[data-model][data-mime-type^="image/"]').each(function() {
            var $element = $('<div />')
                .css({
                    'float':'left',
                    'width': '130',
                    'height':'130',
                    'border':'1px solid black',
                    'margin':'5px',
                    'text-align':'center'
                })
                .text($(this).text())
                .append(
                    $('<a />')
                        .attr('href', '/file/get-file/?model=' + $(this).data('model') + '&id=' + $(this).data('file-id'))
                        .attr('target', '_blank')
                        .append(
                            $('<div />')
                                .css({
                                    'overflow':'hidden',
                                    'margin':'5px auto',
                                    'position':'relative',
                                    'vertical-align':'bottom',
                                    'text-align':'center'
                                })
                                .append(
                                    $('<img />')
                                        .attr('src', '/file/get-file/?model=' + $(this).data('model') + '&id=' + $(this).data('file-id'))
                                        .nailthumb({
                                            width: 100,
                                            height: 100,
                                            fitDirection: 'center',
                                            nostyle: true
                                        })
                                )
                        )
                );

            $container_image.append($element);
            $(this).detach();
        })
        .find('div[data-model]').each(function() {
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
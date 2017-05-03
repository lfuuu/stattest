+function ($) {
    'use strict';

    $(function () {
        $('.media-manager').each(function () {
            var $that = $(this),
                $filesListBlock = $that.parent('div').next('.media-manager-block');

            $that.MultiFile({
                list: $filesListBlock,
                max: 1,
                STRING: {
                    remove: '<i class="glyphicon glyphicon-remove-circle"></i>',
                    selected: 'Выбран файл: $file',
                    toomany: 'Достигнуто максимальнное кол-во файлов',
                    duplicate: 'Файл "$file" уже добавлен'
                }
            });
        });
    })

}(jQuery);
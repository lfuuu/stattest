+function ($) {
    'use strict';

    $(function () {
        $('.media-manager-<?= $model->getLanguage() ?>').MultiFile({
            list: 'div.media-manager-block-<?= $model->getLanguage() ?>',
            max: 1,
            STRING: {
                remove: '<i class="glyphicon glyphicon-remove-circle"></i>',
                selected: 'Выбран файл: $file',
                toomany: 'Достигнуто максимальнное кол-во файлов',
                duplicate: 'Файл "$file" уже добавлен'
            }
        });
    })

}(jQuery);
/**
 * Использование:
 *   на элементе select выставляются аттрибуты:
 *      data-source = путь до расположения изображений (пример: /images/stamp),
 *      data-target = селектор элемента в котором будет отображаться изображение (пример: #full_frm_stamp_file_name)
 *      class = image_preview_select
 *
 * Подключение:
 *   <script type="text/javascript" src="/js/behaviors/image-preview-select.js"></script>
 *
 * Пример оформления элемента в котором будет отображаться изображение:
 *   .image_preview {
 *      position: relative;
 *      border: 1px solid;
 *      background-color: #FFFFFF;
 *      text-align: center;
 *      vertical-align: bottom;
 *      width: 250px;
 *      height: 250px;
 *      margin: 0 auto;
 *      overflow: hidden;
 *   }
 *      .image_preview img {
 *         position: absolute;
 *         margin: auto;
 *         top: -200px;
 *         bottom: -200px;
 *         left: -200px;
 *         right: -200px;
 *      }
 */

jQuery(document).ready(function() {

    $('.image_preview_select')
        .change(function() {
            var $source = $(this).data('source'),
                $target = $($(this).data('target')),
                $value = $(this).find('option:selected').val(),
                $image = ($value != '' ? $('<img />').attr('src', $source + $value) : false);

            if ($target.length)
                $target.html($value ? $image : '');
        })
        .trigger('change');

});
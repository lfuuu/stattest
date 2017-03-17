+function ($) {
    'use strict';

    alert('aaa');
    $(function () {
        tinymce.init({
            selector: 'textarea',
            relative_urls: false,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table contextmenu paste'
            ],
            toolbar: 'insertfile undo redo | styleselect fontsizeselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image'
        });
    })

}(jQuery);
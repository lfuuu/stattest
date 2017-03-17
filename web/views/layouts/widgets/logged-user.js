+function ($) {
    'use strict';

    $(function () {
        $('a.logged-user.name').on('click', function() {
            $('.block-user .menu').toggleClass('collapse');
            return false;
        });

        $('a.logged-user.password-change').on('click', function () {
            $(this).parents('ul').prev('a.name').trigger('click');
            showIframePopup(this);
            return false;
        });
    })

}(jQuery);
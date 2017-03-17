+function ($) {
    'use strict';

    $(function () {
        initNavigationBlocks();

        $('.navigation-block div.title').on('click', function () {
            toggleNavigationBlock($(this).parent('div').attr('id'));
        });

        $('.btn-toogle-nav-blocks').on('click', function () {
            switch ($(this).data('action')) {
                case 'open':
                    openAllNavigationBlocks();
                    break;
                case 'close':
                    closeAllNavigationBlocks();
                    break;
            }
            $('.btn-toogle-nav-blocks').toggle();
            return false;
        });
    })

}(jQuery);
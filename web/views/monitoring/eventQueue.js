+function ($) {
    'use strict';

    $(function () {
        var $popovers = $('[data-toggle="popover"]');
        $popovers.length && $popovers.popover();
    })

}(jQuery);
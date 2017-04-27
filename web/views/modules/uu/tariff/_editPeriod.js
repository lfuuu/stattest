+function ($) {
    'use strict';

    $(function () {
        $('.chargePeriod .multiple-input')
            .on('afterInit afterAddRow afterDeleteRow onChangePeriod', function () {
                setTimeout(function () {
                    // пустым строчкам установить 0
                    $(".chargePeriod .list-cell__price_setup input").each(function () {
                        var $this = $(this);
                        ($this.val() == '') && $this.val(0);
                    });
                    $(".chargePeriod .list-cell__price_min input").each(function () {
                        var $this = $(this);
                        ($this.val() == '') && $this.val(0);
                    });
                }, 400); // потому что select2 рендерится чуть позже
            });
    })

}(jQuery);
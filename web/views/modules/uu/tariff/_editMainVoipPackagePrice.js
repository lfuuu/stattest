+function ($) {
    'use strict';

    $(function () {
        $('.package-price .multiple-input')
            .on('afterInit afterAddRow afterDeleteRow onChangePeriod', function () {
                setTimeout(function () {
                    // пустым строчкам установить 0
                    $(".package-price .list-cell__interconnect_price input").each(function () {
                        var $this = $(this);
                        ($this.val() == '') && $this.val(0);
                    });
                }, 400); // потому что select2 рендерится чуть позже
            });
    })

}(jQuery);
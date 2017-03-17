+function ($) {
    'use strict';

    $(function () {
        $('.account-tariff-voip-button-edit')
            .on('click', function () {
                var $this = $(this),
                    $panel = $this.parents('.panel-info'),
                    $checkboxCheckAll = $panel.find('.panel-heading input'),
                    $checkboxes = $panel.find('.account-tariff-voip-numbers input'),
                    $editButtons = $('.account-tariff-voip-button'),
                    $div = $this.next();

                if (!$div.hasClass('account-tariff-voip-form')) {
                    // нет места для загрузки формы - создать
                    $div = $('<div>').addClass('account-tariff-voip-form').insertAfter($this);
                }

                if ($div.html()) {
                    // форма смены тарифа уже есть - убрать
                    $checkboxCheckAll.hide(); // убрать чекбокс "всё"
                    $checkboxes.hide(); // убрать чекбоксы у номеров
                    $div.slideUp(function () { // скрыть форму смены тарифа
                        $div.html('');
                    });
                    $editButtons.show(); // показать кнопки загрузки формы смены тарифа
                } else {
                    $checkboxCheckAll.show(); // показать чекбокс "всё"
                    $checkboxes.show(); // показать чекбоксы у номеров
                    $div.show()  // загрузить форму смены тарифа
                        .addClass('loading')
                        .load('/uu/account-tariff/edit-voip?id=' + $this.data('id') + '&cityId=' + $this.data('city_id') + '&serviceTypeId=' + $this.data('service_type_id'), function () {
                            $div.removeClass('loading');
                        });
                    // скрыть все остальные кнопки загрузки формы смены тарифа, чтобы не было нескольких форм на странице. Иначе это путает
                    $editButtons.hide();
                    $this.show();
                }
            });

        $('.check-all')
            .on("click", function () {
                var $this = $(this);
                var $panel = $this.parents('.panel-info');
                var $checkboxes = $panel.find('.account-tariff-voip-numbers input');

                $checkboxes.prop('checked', this.checked);
            });

        $('.account-tariff-button-cancel')
            .on("click", function () {
                return confirm("Отменить смену тарифа или закрытие услуги?");
            });

        $('body')
            .on('click', '.resource-tariff-form .btn-cancel', function (e, item) {
                e.preventDefault();
                $(this).parents('.account-tariff-voip-form').prev().click();
            });
    })

}(jQuery);
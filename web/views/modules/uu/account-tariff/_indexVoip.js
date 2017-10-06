+function ($) {
    'use strict';

    $(function () {

        /**
         * Показать/скрыть форму
         *
         * @param $that
         * @returns {boolean} Надо ли показать (true) или скрыть (false)
         */
        function togglePanelEdit($that) {
            var $panel = $that.parents('.panel-info'),
                $checkboxCheckAll = $panel.find('.panel-heading input'),
                $checkboxes = $panel.find('.account-tariff-voip-numbers input'),
                $cancelButton = $panel.find('.btn-cancel'),
                $editButtons = $('.account-tariff-voip-button');

            if ($checkboxCheckAll.is(":visible")) {

                // скрыть форму
                $checkboxCheckAll.hide(); // убрать чекбокс "всё"
                $checkboxes.hide(); // убрать чекбоксы у номеров
                $editButtons.show(); // показать кнопки смены режима
                $cancelButton.hide(); // скрыть кнопку выхода из режима редактирования
                $('.account-tariff-voip-form').html(''); // удалить форму смены тарифа
                $('.account-tariff-voip-resource-div').hide(); // скрыть форму смены количества ресурса
                return false;

            } else {

                // показать форму
                $checkboxCheckAll.show(); // показать чекбокс "всё"
                $checkboxes.show(); // показать чекбоксы у номеров
                $editButtons.hide(); // скрыть все остальные кнопки смены режима, чтобы не было нескольких форм на странице. Иначе это путает
                $cancelButton.show(); // показать кнопку выхода из режима редактирования
                return true;

            }
        }

        /**
         * Выйти из режима редактирования
         */
        $('.account-tariff-voip .panel-title .btn-cancel')
            .on('click', function () {
                togglePanelEdit($(this));
                return false;
            });

        /**
         * Перейти в режим редактирования, показать смену тарифа
         */
        $('.account-tariff-voip-button-edit')
            .on('click', function () {
                var $this = $(this),
                    $div = $this.next();

                if (!$div.hasClass('account-tariff-voip-form')) {
                    // нет места для загрузки формы - создать
                    $div = $('<div>').addClass('account-tariff-voip-form').insertAfter($this);
                }

                // показать форму
                $div.show()
                    .addClass('loading')
                    .load('/uu/account-tariff/edit-voip?id=' + $this.data('id') + '&cityId=' + $this.data('city_id') + '&ndcTypeId=' + $this.data('ndc_type_id') + '&serviceTypeId=' + $this.data('service_type_id'), function () {
                        $div.removeClass('loading');
                    });

                // перейти в режим редактирования
                togglePanelEdit($this);
            });

        /**
         * Перейти в режим редактирования, показать форму смены количества ресурса
         */
        $('.account-tariff-voip-resource-button-edit')
            .on('click', function () {
                var $this = $(this);

                // показать форму смены количества ресурса
                $this.parents('.well').find('.account-tariff-voip-resource-div').show();

                // перейти в режим редактирования
                togglePanelEdit($this);
            });

        /**
         * Галочка "выбрать все"
         */
        $('.check-all')
            .on("click", function () {
                var $this = $(this);
                var $panel = $this.parents('.panel-info');
                var $checkboxes = $panel.find('.account-tariff-voip-numbers input');

                $checkboxes.prop('checked', this.checked);
            });

        /**
         * Отмена смены тарифа
         */
        $('.account-tariff-button-cancel')
            .on("click", function () {
                return confirm("Отклонить запланированную смену тарифа или закрытие услуги?");
            });

        /**
         * Отмена смены количества ресурса
         */
        $('.account-tariff-resource-button-cancel')
            .on("click", function () {
                return confirm("Отклонить запланированную смену количества ресурса?");
            });

        $('body')
            .on('click', '.account-tariff-edit-voip .btn-cancel', function (e, item) {
                e.preventDefault();
                $(this).parents('.account-tariff-voip-form').prev().click();
            });
    })

}(jQuery);
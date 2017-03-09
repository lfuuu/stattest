<?php
/**
 * Список универсальных услуг с пакетами
 *
 * @link http://rd.welltime.ru/confluence/pages/viewpage.action?pageId=10715249
 *
 * @var \yii\web\View $this
 * @var AccountTariffFilter $filterModel
 */

use app\classes\uu\filter\AccountTariffFilter;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\ServiceType;
use yii\db\ActiveQuery;

$serviceType = $filterModel->getServiceType();

/** @var ActiveQuery $query */
$query = $filterModel->search()->query;

// сгруппировать одинаковые город-тариф-пакеты по строчкам
$rows = AccountTariff::getGroupedObjects($query);
?>

<p>
    <?= ($serviceType ? $this->render('//layouts/_buttonCreate', ['url' => AccountTariff::getUrlNew($serviceType->id)]) : '') ?>
</p>

<?php
foreach ($rows as $row) {
    /** @var AccountTariff $accountTariffFirst */
    $accountTariffFirst = reset($row);
    if (in_array($accountTariffFirst->service_type_id, ServiceType::$packages)) {
        // пакеты отдельно не выводим. Только в комплекте с базовой услугой
        continue;
    }

    $serviceType = $accountTariffFirst->serviceType;

    switch ($accountTariffFirst->service_type_id) {
        case ServiceType::ID_VOIP:
            $packageServiceTypeIds = [ServiceType::ID_VOIP_PACKAGE];
            break;
        case ServiceType::ID_TRUNK:
            $packageServiceTypeIds = [ServiceType::ID_TRUNK_PACKAGE_ORIG, ServiceType::ID_TRUNK_PACKAGE_TERM];
            break;
        default:
            $packageServiceTypeIds = [];
            break;
    }

    // сгруппировать пакеты по типу
    $packagesList = [];
    foreach ($accountTariffFirst->nextAccountTariffs as $accountTariffPackage) {
        $isPackageDefault = $accountTariffPackage->tariff_period_id && $accountTariffPackage->tariffPeriod->tariff->is_default;
        $packagesList[$isPackageDefault ? 0 : $accountTariffPackage->service_type_id][] = $accountTariffPackage;
    }

    // форма
    echo $this->render('_indexVoipForm', [
        'accountTariffFirst' => $accountTariffFirst,
        'packageServiceTypeIds' => $packageServiceTypeIds,
        'row' => $row,
        'serviceType' => $serviceType,
        'packagesList' => $packagesList,
    ]);

}
?>

<script type='text/javascript'>
    $(function () {

        $(".account-tariff-voip-button-edit")
            .on("click", function (e, item) {
                var $this = $(this);
                var $panel = $this.parents('.panel-info');
                var $checkboxCheckAll = $panel.find('.panel-heading input');
                var $checkboxes = $panel.find('.account-tariff-voip-numbers input');
                var $editButtons = $(".account-tariff-voip-button");
                var $div = $this.next();
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

        $(".check-all")
            .on("click", function (e, item) {
                var $this = $(this);
                var $panel = $this.parents('.panel-info');
                var $checkboxes = $panel.find('.account-tariff-voip-numbers input');

                $checkboxes.prop('checked', this.checked);
            });

        $(".account-tariff-button-cancel")
            .on("click", function (e, item) {
                return confirm("Отменить смену тарифа или закрытие услуги?");
            });

        $("body")
            .on("click", ".resource-tariff-form .btn-cancel", function (e, item) {
                e.preventDefault();
                $(this).parents('.account-tariff-voip-form').prev().click();
            });

    });
</script>

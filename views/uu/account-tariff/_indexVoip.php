<?php
/**
 * Список универсальных услуг Телефонии
 * @link http://rd.welltime.ru/confluence/pages/viewpage.action?pageId=10715249
 *
 * @var \yii\web\View $this
 * @var AccountTariffFilter $filterModel
 */

use app\classes\Html;
use app\classes\uu\filter\AccountTariffFilter;
use app\classes\uu\forms\AccountTariffEditForm;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
use yii\db\ActiveQuery;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$serviceType = $filterModel->getServiceType();

/** @var ActiveQuery $query */
$query = $filterModel->search()->query;

// сгруппировать одинаковые город-тариф-пакеты по строчкам
$rows = AccountTariff::getGroupedObjects($query);
?>

<p>
    <?= $this->render('//layouts/_buttonCreate', ['url' => AccountTariff::getUrlNew($serviceType->id)]) ?>
</p>

<?php foreach ($rows as $row) : ?>

    <?php $form = ActiveForm::begin(['action' => 'uu/account-tariff/save-voip']); ?>

    <div class="panel panel-info">
        <div class="panel-heading">
            <?php
            /** @var AccountTariff $accountTariffFirst */
            $accountTariffFirst = reset($row);
            $formModel = new AccountTariffEditForm([
                'id' => $accountTariffFirst->id,
            ]);
            $isEditable = $accountTariffFirst->tariff_period_id;
            ?>
            <?php // город ?>
            <h2 class="panel-title">
                <?= Html::checkbox(null, $checked = true, ['class' => 'check-all', 'style' => 'display: none;', 'title' => 'Отметить всё']) ?>

                <?= $accountTariffFirst->city ? $accountTariffFirst->city->name : Yii::t('common', '(not set)') ?>
            </h2>

        </div>

        <div class="panel-body">

            <div class="row">

                <div class="col-sm-2 account-tariff-voip-numbers">
                    <?php /** @var AccountTariff $accountTariff */ ?>
                    <?php foreach ($row as $accountTariff) : ?>

                        <?php // номера ?>
                        <div>
                            <?= Html::checkbox('AccountTariff[ids][]', $checked = true, ['value' => $accountTariff->id, 'style' => 'display: none;']) ?>
                            <?= Html::a($accountTariff->voip_number ?: Yii::t('common', '(not set)'), $accountTariff->getUrl()) ?>
                        </div>

                    <?php endforeach; ?>
                </div>

                <div class="col-sm-10">

                    <?php // тариф ?>
                    <div class="well">

                        <?php
                        $i = 0;
                        $isCancelable = $accountTariffFirst->isCancelable();
                        /** @var AccountTariffLog $accountTariffLog */
                        ?>
                        <?php foreach ($accountTariffFirst->accountTariffLogs as $accountTariffLog) : ?>
                            <div>
                                <b>
                                    <?= $accountTariffLog->getTariffPeriodLink() ?>
                                </b>
                                <span class="account-tariff-log-actual-from">с <?= Yii::$app->formatter->asDate($accountTariffLog->actual_from, 'medium') ?></span>

                                <?php $i++ && $isCancelable = $isEditable = false ?>

                                <?= $isCancelable ?
                                    Html::a(
                                        Html::tag('i', '', [
                                            'class' => 'glyphicon glyphicon-erase',
                                            'aria-hidden' => 'true',
                                        ]) . ' ' .
                                        Yii::t('common', 'Cancel'),
                                        Url::toRoute(['/uu/account-tariff/cancel', 'ids' => array_keys($row), 'tariffPeriodId' => $accountTariffFirst->tariff_period_id]),
                                        [
                                            'class' => 'btn btn-danger account-tariff-voip-button account-tariff-button-cancel btn-xs',
                                            'title' => 'Отменить смену тарифа',
                                        ]
                                    ) : '' ?>

                                <?= (!$isCancelable && $isEditable) ?
                                    Html::button(
                                        Html::tag('i', '', [
                                            'class' => 'glyphicon glyphicon-edit',
                                            'aria-hidden' => 'true',
                                        ]) . ' ' .
                                        'Сменить',
                                        [
                                            'class' => 'btn btn-primary account-tariff-voip-button account-tariff-voip-button-edit btn-xs',
                                            'title' => 'Сменить тариф или отключить услугу',
                                            'data-id' => $accountTariffFirst->id,
                                            'data-city_id' => (int)$accountTariff->city_id,
                                        ]
                                    ) : ''
                                ?>

                            </div>

                            <?php
                            if (!$isCancelable) {
                                // этот и последующие отменить нельзя
                                break;
                            }
                            ?>
                        <?php endforeach; ?>

                    </div>

                    <?php // пакеты ?>
                    <?php foreach ($accountTariffFirst->nextAccountTariffs as $accountTariffPackage) : ?>
                        <div class="well">

                            <?php
                            $i = 0;
                            $isPackageEditable = $accountTariffPackage->tariff_period_id;
                            $isPackageCancelable = $accountTariffPackage->isCancelable();
                            /** @var AccountTariffLog $accountTariffLog */
                            ?>
                            <?php foreach ($accountTariffPackage->accountTariffLogs as $accountTariffLog) : ?>
                                <div>
                                    <?= $accountTariffLog->getTariffPeriodLink() ?>
                                    <span class="account-tariff-log-actual-from">с <?= Yii::$app->formatter->asDate($accountTariffLog->actual_from, 'medium') ?></span>

                                    <?php $i++ && $isPackageCancelable = $isPackageEditable = false ?>

                                    <?= $isPackageCancelable ?
                                        Html::a(
                                            Html::tag('i', '', [
                                                'class' => 'glyphicon glyphicon-erase',
                                                'aria-hidden' => 'true',
                                            ]) . ' ' .
                                            Yii::t('common', 'Cancel'),
                                            Url::toRoute(['/uu/account-tariff/cancel', 'ids' => array_keys($row), 'tariffPeriodId' => $accountTariffPackage->tariff_period_id]),
                                            [
                                                'class' => 'btn btn-danger account-tariff-voip-button account-tariff-button-cancel btn-xs',
                                                'title' => 'Отменить смену тарифа для пакета',
                                            ]
                                        ) : '' ?>

                                    <?= (!$isPackageCancelable && $isPackageEditable) ?
                                        Html::button(
                                            Html::tag('i', '', [
                                                'class' => 'glyphicon glyphicon-edit',
                                                'aria-hidden' => 'true',
                                            ]) . ' ' .
                                            'Сменить',
                                            [
                                                'class' => 'btn btn-primary account-tariff-voip-button account-tariff-voip-button-edit btn-xs',
                                                'title' => 'Сменить тариф или отключить услугу для пакета',
                                                'data-id' => $accountTariffPackage->id,
                                                'data-city_id' => $accountTariffPackage->city_id,
                                            ]
                                        ) : ''
                                    ?>

                                </div>

                                <?php
                                if (!$isPackageCancelable) {
                                    // этот и последующие отменить нельзя
                                    break;
                                }
                                ?>
                            <?php endforeach; ?>

                        </div>
                    <?php endforeach ?>

                    <?= $isEditable ?
                        Html::button(
                            Html::tag('i', '', [
                                'class' => 'glyphicon glyphicon-plus',
                                'aria-hidden' => 'true',
                            ]) . ' ' .
                            'Добавить пакет',
                            [
                                'class' => 'btn btn-success account-tariff-voip-button account-tariff-voip-button-edit btn-xs',
                                'title' => 'Добавить пакет',
                                'data-id' => 0,
                                'data-city_id' => $accountTariffFirst->city_id,
                            ]
                        ) : ''
                    ?>

                </div>

            </div>

        </div>
    </div>

    <?php ActiveForm::end(); ?>
<?php endforeach; ?>

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
                        .load('/uu/account-tariff/edit-voip?id=' + $this.data('id') + '&cityId=' + $this.data('city_id'), function () {
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

    });
</script>

<?php

use app\helpers\DateTimeZoneHelper;
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;
use kartik\daterange\DateRangePicker;
use kartik\widgets\Select2;
use app\classes\Html;
use app\classes\DynamicModel;
use app\models\UsageVoipPackage;

/** @var UsageVoipPackage[] $packages */
/** @var DynamicModel $filter */
/** @var \app\classes\BaseView $this */

echo Html::formLabel('Отчет по использованию Телефония Пакеты');

echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Статистика'],
        ['label' => 'Телефония Пакеты', 'url' => Url::toRoute('/report/voip-package/use-report')]
    ],
]);

$packagesList = [];
foreach ($packages as $package) {
    $packagesList[$package->usageVoip->id][] = [
        'packageId' => $package->id,
        'packageTitle' =>
            $package->tariff->name .
            ' / ' .
            (new DateTime($package->actual_from))->format(DateTimeZoneHelper::DATE_FORMAT) .
            ' : ' .
            (new DateTime($package->actual_to))->format(DateTimeZoneHelper::DATE_FORMAT),
    ];
}

$this->registerJsVariables([
    'packageList' => $packagesList,
    'packageSelected' => (isset($filter->packages) ? $filter->packages : -1),
]);
?>

<?php if($clientAccount): ?>
    <div class="well" style="overflow-x: auto;">
        <form method="GET">
            <div class="col-sm-12">
                <div class="form-group col-sm-8">
                    <label>Период</label>
                    <?php
                    $dateRangeValue = $filter->range
                            ?:
                                (new DateTime('first day of this month'))->format(DateTimeZoneHelper::DATE_FORMAT) .
                                ' : ' .
                                (new DateTime('last day of this month'))->format(DateTimeZoneHelper::DATE_FORMAT);

                    echo DateRangePicker::widget([
                        'name' => 'filter[range]',
                        'hideInput' => true,
                        'value' => $dateRangeValue,
                        'pluginOptions' => [
                            'locale' => [
                                'format' => 'YYYY-MM-DD',
                                'separator' => ' : ',
                            ],
                        ],
                        'containerOptions' => [
                            'style' => 'overflow: hidden; min-width: 180px;',
                            'class' => 'drp-container input-group',
                            'title' => $dateRangeValue,
                        ],
                    ]);
                    ?>
                </div>

                <div class="form-group col-sm-4">
                    <label>Часовой пояс</label><br />
                    <b><?= $clientAccount->timezone->getName(); ?></b>
                </div>
            </div>

            <div class="col-sm-12">
                <div class="form-group col-sm-4">
                    <label>Номер</label>
                    <?php
                    echo Select2::widget([
                        'name' => 'filter[number]',
                        'data' => $numbers,
                        'value' => $filter->number,
                        'options' => [
                            'placeholder' => '-- Выбрать номер --',
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]);
                    ?>
                </div>

                <div class="form-group col-sm-4">
                    <label>Пакет</label>
                    <?php
                    echo Html::dropDownList(
                        'filter[packages]',
                        $filter->packages,
                        ['0' => 'Все пакеты'],
                        ['class' => 'form-control']
                    );
                    ?>
                </div>

                <div class="form-group col-sm-4">
                    <label>Вывод</label>
                    <?php
                    echo Html::dropDownList(
                        'filter[mode]',
                        $filter['mode'],
                        [
                            'by_package' => 'По пакетам',
                            'by_package_calls' => 'По звонкам в пакете',
                        ],
                        ['class' => 'form-control']
                    );
                    ?>
                </div>


            </div>
            <div class="col-sm-12 text-center">
                <div class="alert alert-danger collapse">На номере отсутствуют пакеты</div>
                <?php
                echo Html::submitButton('Сформировать', ['class' => 'btn btn-primary build-report', 'disabled' => true]);
                ?>
            </div>
        </form>

    </div>

    <?php
    if ($filter && count($report)) {
        echo $this->render('@app/views/report/voip-package/use-report-' . $filter->mode . '.php', [
            'report' => $report,
            'filter' => $filter,
        ]);
    }
    ?>
<?php endif; ?>
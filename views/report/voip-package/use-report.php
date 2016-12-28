<?php

use app\helpers\DateTimeZoneHelper;
use yii\widgets\Breadcrumbs;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use kartik\daterange\DateRangePicker;
use kartik\widgets\Select2;
use app\classes\Html;
use app\classes\DynamicModel;
use app\models\UsageVoipPackage;

/** @var UsageVoipPackage[] $packages */
/** @var DynamicModel $filter */

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
                <div class="alert alert-danger" style="display:none;">На номере отсутствуют пакеты</div>
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

<script type="text/javascript">
var
    packageList = <?= json_encode($packagesList); ?>,
    packageSelected = <?= (isset($filter->packages) ? $filter->packages : -1); ?>;

jQuery(document).ready(function() {
    $('select[name="filter[number]"]')
        .on('change', function() {
            var
                current = $(this).find('option:selected').val(),
                packages = $('select[name="filter[packages]"]'),
                mode = $('select[name="filter[mode]"]').val(),
                $buildBtn = $('button.build-report');

            packages.find('option:gt(0)').remove();

            if (current) {
                if (packageList[current]) {
                    $.each(packageList[current], function () {
                        $('<option />')
                            .text(this.packageTitle)
                            .val(this.packageId)
                            .prop('selected', this.packageId == packageSelected)
                            .appendTo(packages);
                    });
                }

                if (packages.find('option').length > 1) {
                    $buildBtn
                        .prop('disabled', false)
                        .prev('div')
                        .hide();
                }
                else {
                    $buildBtn
                        .prop('disabled', true)
                        .prev('div')
                        .show();
                }
            }
        })
        .trigger('change');

    $('select[name="filter[mode]"]')
        .on('change', function() {
            var current = $(this).find('option:selected').val(),
                packages = $('select[name="filter[packages]"]');

            packages.find('option:eq(0)').prop('disabled', (current == 'by_package_calls' ? true : false));

            if (!packageSelected) {
                packages.find('option:gt(0)').prop('selected', true);
            }
        })
        .trigger('change');
});
</script>

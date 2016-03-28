<?php

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
            (new DateTime($package->actual_from))->format('Y-m-d') .
            ' : ' .
            (new DateTime($package->actual_to))->format('Y-m-d'),
    ];
}
?>

<?php if($clientAccount): ?>
    <div class="well" style="overflow-x: auto;">
        <form method="GET">
            <div class="col-xs-12">
                <table border="0" width="100%" cellpadding="5" cellspacing="5">
                    <colgroup>
                        <col width="20%" />
                        <col width="20%" />
                        <col width="20%" />
                        <col width="20%" />
                        <col width="20%" />
                    </colgroup>
                    <thead>
                        <tr>
                            <th><div style="margin-left: 15px; font-size: 12px;">Период</div></th>
                            <th><div style="margin-left: 15px; font-size: 12px;">Номер</div></th>
                            <th><div style="margin-left: 15px; font-size: 12px;">Пакет</div></th>
                            <th><div style="margin-left: 15px; font-size: 12px;">Вывод</div></th>
                            <th><div style="margin-left: 15px; font-size: 12px;">Часовой пояс</div></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="col-xs-12">
                                    <?php
                                    echo DateRangePicker::widget([
                                        'name' => 'filter[range]',
                                        'presetDropdown' => true,
                                        'value' =>
                                            $filter->range
                                                ?:
                                                (new DateTime('first day of this month'))->format('Y-m-d') .
                                                ' : ' .
                                                (new DateTime('last day of this month'))->format('Y-m-d'),
                                        'pluginOptions' => [
                                            'locale' => [
                                                'format' => 'YYYY-MM-DD',
                                                'separator'=>' : ',
                                            ],
                                        ],
                                        'containerOptions' => [
                                            'style' => 'overflow: hidden;',
                                            'class' => 'drp-container input-group',
                                        ],
                                        'options' => [
                                            'style' => 'font-size: 12px; height: 30px;',
                                        ],
                                    ]);
                                    ?>
                                </div>
                            </td>
                            <td>
                                <div class="col-xs-12">
                                    <?php
                                    echo Select2::widget([
                                        'name' => 'filter[number]',
                                        'data' => ArrayHelper::map($numbers, 'id', 'E164'),
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
                            </td>
                            <td>
                                <div class="col-xs-12">
                                    <?php
                                    echo Html::dropDownList(
                                        'filter[packages]',
                                        $filter->packages,
                                        ['0' => 'Все пакеты'],
                                        ['class' => 'form-control']
                                    );
                                    ?>
                                </div>
                            </td>
                            <td>
                                <div class="col-xs-12">
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
                            </td>
                            <td>
                                <div class="col-xs-12">
                                    <b><?= $clientAccount->timezone->getName(); ?></b>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" align="center">
                                <br />
                                <?php
                                echo Html::submitButton('Сформировать', ['class' => 'btn btn-primary',]);
                                ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
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
                mode = $('select[name="filter[mode]"]').val();

            packages.find('option:gt(0)').remove();

            if (current && packageList[current]) {
                $.each(packageList[current], function () {
                    $('<option />')
                        .text(this.packageTitle)
                        .val(this.packageId)
                        .prop('selected', this.packageId == packageSelected)
                        .appendTo(packages);
                });
            }
        })
        .trigger('change');

    $('select[name="filter[mode]"]')
        .on('change', function() {
            var current = $(this).find('option:selected').val()
                packages = $('select[name="filter[packages]"]');
            if (current == 'by_package_calls') {
                packages.find('option:eq(0)').prop('disabled', true);
                packages.find('option:eq(1)').prop('selected', true);
            }
            else {
                packages
                    .find('option:eq(0)')
                    .prop('disabled', false)
                    .prop('selected', true);
            }
        })
        .trigger('change');
});
</script>

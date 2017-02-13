<?php
/**
 * @var app\classes\BaseView $this
 * @var int $pricelistReportId
 * @var array $pricelistReportData
 * @var string[] $currencyMap
 * @var array $countries
 * @var array $regions
 */

use app\classes\Html;
use app\models\CurrencyRate;
use app\widgets\MultipleInput\MultipleInput;
use app\widgets\MultipleInput\MultipleInputProfit;
use app\widgets\MultipleInput\MultipleInputRange;
use kartik\editable\Editable;
use kartik\form\ActiveForm;
use kartik\widgets\Select2;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

\app\assets\BootstrapTableAsset::register(Yii::$app->view);

echo Html::formLabel($title = 'Конструктор прайс-листов');
echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Межоператорка (отчеты)'],
        ['label' => 'Анализ прайс-листов', 'url' => '/voipreport/pricelist-report',],
        ['label' => $title, 'url' => '/voipreport/pricelist-report/calculate?reportId=' . $pricelistReportId],
    ],
]);
?>

<div class="well col-sm-12">
    <form id="pricelist-report-currency-frm">
        <div class="col-sm-2">
            <label>Валюта расчетов</label>
            <br />
            <?php foreach ($currencyMap as $currency => $currencyTitle) :
                if ($currency == \app\models\Currency::RUB) :
                    continue;
                endif;

                $currencyRate = CurrencyRate::dao()->getRate($currency);
                ?>
                <label class="label label-info"><?= $currencyTitle ?></label> <?= number_format($currencyRate, 2) ?>
            <?php endforeach; ?>
        </div>

        <div class="col-sm-4">
            <?= Select2::widget([
                'name' => 'currency',
                'data' => $currencyMap,
            ]) ?>
        </div>

        <div class="col-sm-6">
            <?= $this->render('//layouts/_button', [
                'text' => 'Применить',
                'glyphicon' => 'glyphicon glyphicon-saved',
                'params' => [
                    'class' => 'btn btn-primary',
                ],
            ]) ?>
        </div>
    </form>
</div>

<div class="well col-sm-12">
    <div class="row">
        <div class="col-sm-3">
            <form id="pricelist-report-filter-frm">
                <label>Фильтр выборки</label>
                <div class="pull-right">
                    <?= $this->render('//layouts/_buttonFilter') ?>
                </div>
                <hr />

                <label>Страна</label><br />
                <?= Select2::widget([
                    'name' => 'country_id',
                    'data' => $countries,
                ]) ?>

                <label>Регион</label><br />
                <?= Select2::widget([
                    'name' => 'region_id',
                    'data' => $regions,
                ]) ?>

                <label></label>
                <div class="btn-group center-block" data-toggle="buttons">
                    <label class="btn btn-default active">
                        <input type="radio" name="is_mobile" value="-1" checked="checked" /> Все
                    </label>
                    <label class="btn btn-default">
                        <input type="radio" name="is_mobile" value="0" /> Мобильные
                    </label>
                    <label class="btn btn-default">
                        <input type="radio" name="is_mobile" value="1" /> Стационарные
                    </label>
                </div>

            </form>
        </div>

        <?php $form = ActiveForm::begin(['id' => 'pricelist-report-modify-frm']); ?>
            <div class="col-sm-9">
                <label>Правила формирования прайс-листа</label>

                <div class="pull-right">
                    <?= $this->render('//layouts/_button', [
                        'text' => 'Сформировать',
                        'glyphicon' => 'glyphicon glyphicon-edit',
                        'params' => [
                            'class' => 'btn btn-primary',
                            'style' => 'margin-left: 20px;',
                        ],
                    ]) ?>
                </div>

                <div class="pull-right">
                    <div class="btn-group center-block" data-toggle="buttons">
                        <label class="btn btn-default">
                            <input type="radio" name="best_price" value="best_price_1" /> Лучшая цена #1
                        </label>
                        <label class="btn btn-default active">
                            <input type="radio" name="best_price" value="best_price_2" checked="checked" /> Лучшая цена #2
                        </label>
                    </div>
                </div>
                <hr />

                <?= MultipleInput::widget([
                    'name' => 'modifiers',
                    'allowEmptyList' => false,
                    'enableGuessTitle' => true,
                    'addButtonPosition' => MultipleInput::POS_HEADER,
                    'colgroup' => [
                        '40%',
                        '40%',
                        '20%',
                    ],
                    'columns' => [
                        [
                            'name' => 'range',
                            'title' => 'Диапазон цен',
                            'type' => MultipleInputRange::className(),
                            'options' => [
                                'step' => 0.5,
                            ],
                        ],
                        [
                            'name' => 'profit',
                            'title' => 'Добавить маржу',
                            'type' => MultipleInputProfit::className(),
                            'options' => [
                                'variants' => [
                                    'money' => 'денег',
                                    'percent' => '%',
                                ],
                            ],
                        ],
                        [
                            'name' => 'price',
                            'title' => 'Задать цену',
                            'type' => Editable::INPUT_TEXT,
                            'options' => [
                                'type' => 'number',
                                'step' => 1,
                                'class' => 'multiple-price',
                            ],
                        ],
                    ],
                ]) ?>
            </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>


<div id="pricelist-report-tbl-toolbar">
    <form
        id="pricelist-export-frm"
        action="<?= Url::toRoute(['/voipreport/pricelist-report/get-pricelist-export', 'reportId' => $pricelistReportId]) ?>"
        method="post"
        target="_blank"
    >
        <textarea name="data" class="collapse"></textarea>
        <?= $this->render('//layouts/_button', [
            'text' => 'Выгрузить',
            'glyphicon' => 'glyphicon glyphicon-export',
            'params' => [
                'id' => 'export-btn',
                'class' => 'btn btn-success',
            ],
        ]) ?>
    </form>
</div>

<table id="pricelist-report-tbl">
    <thead>
        <tr>
            <th rowspan="2" data-field="prefix">Префикс номера</th>
            <th rowspan="2" data-field="zone">Зона</th>
            <th rowspan="2" data-field="destination">Назначение</th>
            <th rowspan="2" data-field="best_price_1">Лучшая цена #1</th>
            <th rowspan="2" data-field="best_price_2">Лучшая цена #2</th>
            <th rowspan="2" data-field="modify_result">Результат</th>
            <?php
            foreach ($pricelistReportData as $row) {
                if (!$row['pricelist']) {
                    continue;
                }

                echo Html::tag('th', $row['pricelist']->name, [
                    'class' => 'pricelist-column',
                    'title' => $row['pricelist']->name,
                ]);
            }
            ?>
        </tr>
        <tr>
            <?php
            $index = 0;
            foreach ($pricelistReportData as $row) : ?>
                <th data-field="price_<?= $index++ ?>" data-halign="center" class="pricelist-column"><?= $row['date'] ?></th>
            <?php
            endforeach; ?>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>

<div class="fullScreenOverlay">
    <div class="fullScreenOverlayContent">
        <div class="loading"></div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function () {
    var
        $currencyForm = $('form#pricelist-report-currency-frm'),
        $filterForm = $('form#pricelist-report-filter-frm'),
        $modifyForm = $('form#pricelist-report-modify-frm'),
        $exportForm = $('form#pricelist-export-frm'),
        $reportTable = $('#pricelist-report-tbl'),
        $exportBtn = $('#export-btn'),
        $loadingOverlay = $('.fullScreenOverlay'),
        sourceUrl = '<?= Url::toRoute(['/voipreport/pricelist-report/get-pricelist-data', 'reportId' => $pricelistReportId]) ?>',
        sourceData = [],
        loadData = function () {
            $.ajax({
                url: sourceUrl + '&currency=' + $currencyForm.find('select[name="currency"]').val(),
                method: 'get',
                dataType: 'json',
                beforeSend: function () {
                    $loadingOverlay.show();
                },
                success: function (data) {
                    sourceData = data.data;
                    $reportTable.bootstrapTable('load', sourceData);
                    $loadingOverlay.hide();
                },
                error: function () {
                    $.notify('Загрузка данных не удалась', 'error');
                    $loadingOverlay.hide();
                }
            });
        },
        inRange = function (rangeFrom, rangeTo, value) {
            if (rangeFrom && rangeTo && value >= rangeFrom && value <= rangeTo) {
                return true;
            }

            if (rangeFrom && !rangeTo && value >= rangeFrom) {
                return true;
            }

            if (!rangeFrom && rangeTo && value <= rangeTo) {
                return true;
            }

            return false;
        },
        filterCountry = function (data, countryId) {
            return $.grep(data, function (row) {
                return row.country == countryId;
            });
        },
        filterRegion = function (data, regionId) {
            return $.grep(data, function (row) {
                return row.region == regionId;
            });
        },
        filterIsMobile = function (data, isMobile) {
            return $.grep(data, function (row) {
                return row.mob == isMobile;
            });
        };

    $reportTable.bootstrapTable({
        toolbar: '#pricelist-report-tbl-toolbar',
        toolbarAlign: 'right',
        pagination: true,
        pageSize: 1000,
        locale: 'ru-RU',
        paginationVAlign: 'top',
        pageList: []
    });

    loadData();

    $currencyForm.find('button').on('click', function () {
        loadData();
        return false;
    });

    $filterForm.find('button').on('click', function () {
        var
            countryId = $filterForm.find('select[name="country_id"]').val(),
            regionId = $filterForm.find('select[name="region_id"]').val(),
            isMobile = $filterForm.find('input[name="is_mobile"]:checked').val(),
            data = sourceData;

        $loadingOverlay.toggle(200, function () {
            if (countryId) {
                data = filterCountry(data, countryId);
            }

            if (regionId) {
                data = filterRegion(data, regionId);
            }

            if (isMobile >= 0) {
                data = filterIsMobile(data, isMobile);
            }

            $reportTable.bootstrapTable('load', data);
            $loadingOverlay.hide();
        });

        return false;
    });

    $modifyForm.find('button').on('click', function () {
        var
            countryId = $filterForm.find('select[name="country_id"]').val(),
            regionId = $filterForm.find('select[name="region_id"]').val(),
            isMobile = $filterForm.find('input[name="is_mobile"]:checked').val(),
            $elements = $modifyForm.find('.multiple-input').find('tr.multiple-input-list__item'),
            priceType = $modifyForm.find('input[name="best_price"]:checked').val(),
            modifiers = [],
            data = sourceData;

        $elements.each(function () {
            var $fields = $(this).find('input, select'),
                modifier = {
                    range: [],
                    profit: {}
                };

            $fields.each(function () {
                var attribute = $(this).attr('class').match(/multiple\-([^\s]+)/)[1],
                    value = $(this).val();

                switch (attribute) {
                    case 'range':
                        modifier.range.push(value);
                        break;
                    case 'value':
                        modifier.profit.value = parseFloat(value);
                        break;
                    case 'variant':
                        modifier.profit.variant = value;
                        break;
                    case 'price':
                        modifier.profit.summary = parseFloat(value);
                        break;
                }
            });

            modifiers.push(modifier);
        });

        $loadingOverlay.toggle(200, function () {
            var totalModified = 0;

            if (countryId) {
                data = filterCountry(data, countryId);
            }

            if (regionId) {
                data = filterRegion(data, regionId);
            }

            if (isMobile >= 0) {
                data = filterIsMobile(data, isMobile);
            }

            $.map(data, function (row) {
                var price = parseFloat(row[priceType]);

                $.each(modifiers, function () {
                    if (inRange(parseFloat(this.range[0]), parseFloat(this.range[1]), price)) {
                        totalModified++;

                        if (this.profit.summary && !this.profit.value) {
                            row.modify_result = this.profit.summary;
                        } else {
                            switch (this.profit.variant) {
                                case 'money':
                                    row.modify_result = (price + this.profit.value).toFixed(4);
                                    break;
                                case 'percent':
                                    row.modify_result = (price + ((price * this.profit.value) / 100)).toFixed(4);
                                    break;
                            }
                        }
                    }
                });

                return row;
            });

            $filterForm.find('button').trigger('click');
            $loadingOverlay.hide();

            $.notify('Изменено ' + totalModified + ' значений', 'success');
        });

        return false;
    });

    $exportBtn.on('click', function () {
        $exportForm.find('[name="data"]').val(JSON.stringify($reportTable.bootstrapTable('getData')));
        $exportForm.submit();
        return false;
    });
});
</script>

<style type="text/css">
th.pricelist-column {
    width: 100px;
}
th.pricelist-column .th-inner {
    width: 100px;
    font-size: 10px;
}
</style>
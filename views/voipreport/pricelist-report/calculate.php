<?php
/**
 * @var app\classes\BaseView $this
 * @var \app\models\billing\PricelistReport $pricelistReport
 * @var array $pricelistReportData
 * @var string[] $currencyMap
 * @var array $countries
 * @var array $regions
 */

use app\assets\BootstrapTableAsset;
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

BootstrapTableAsset::register(Yii::$app->view);

echo Html::formLabel($title = 'Конструктор прайс-листов');
echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Межоператорка (отчеты)'],
        ['label' => 'Анализ прайс-листов', 'url' => '/voipreport/pricelist-report',],
        ['label' => $title, 'url' => '/voipreport/pricelist-report/calculate?reportId=' . $pricelistReport->id],
        $pricelistReport->name ?: 'Без названия',
    ],
]);

$this->registerJsVariable('pricelistReportId', $pricelistReport->id);
?>

<div class="well col-sm-12 text-center">
    <b><?= $pricelistReport->name ?: 'Без названия' ?></b>
</div>

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
                        <input type="radio" name="is_mobile" value="1" /> Мобильные
                    </label>
                    <label class="btn btn-default">
                        <input type="radio" name="is_mobile" value="0" /> Стационарные
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
                            'type' => MultipleInputRange::class,
                            'options' => [
                                'step' => 0.5,
                            ],
                        ],
                        [
                            'name' => 'profit',
                            'title' => 'Добавить маржу',
                            'type' => MultipleInputProfit::class,
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
        action="<?= Url::toRoute(['/voipreport/pricelist-report/get-pricelist-export', 'reportId' => $pricelistReport->id]) ?>"
        method="post"
        target="_blank"
    >
        <textarea name="data" class="collapse"></textarea>
        <textarea name="columns" class="collapse"></textarea>
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

<?php
$missedPricelists = array_filter($pricelistReportData, function ($row) {
    return !$row['pricelist'];
});

if (count($missedPricelists)) : ?>
    <div class="col-sm-12 label label-danger text-left">
        <?php foreach ($missedPricelists as $id => $row) : ?>
            Прайс-лист #<?= $id ?> на дату <?= $row['date'] ?> не может быть обработан<br />
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<table id="pricelist-report-tbl">
    <thead>
        <tr>
            <th rowspan="2" data-field="prefix">Префикс номера</th>
            <th rowspan="2" data-field="zone">Зона</th>
            <th rowspan="2" data-field="destination">Назначение</th>
            <th rowspan="2" data-field="best_price_1">Лучшая цена #1</th>
            <th rowspan="2" data-field="best_price_2">Лучшая цена #2</th>
            <th rowspan="2" data-field="modify_result" data-cell-style="resultCellStyle">Результат</th>
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
<?php

use app\classes\grid\GridView;
use app\models\billing\DataRaw;
use app\models\billing\MCC;
use app\models\billing\MNC;
use app\modules\uu\models\AccountTariff;
use kartik\select2\Select2;
use kartik\widgets\DatePicker;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$fromDate = $searchModel->fromDate;
$toDate = $searchModel->toDate;

$previousMonthUrl = Url::to(['?fromDate=' . (new DateTime("$fromDate first day of last month"))->format('Y-m-01') .
                    '&toDate=' . (new DateTime("$toDate last day of last month"))->format('Y-m-t')]);

$currentMonthUrl = Url::to(['?fromDate=' . date('Y-m-01', time()) . '&toDate=' . date('Y-m-t', time())]);

function getMncForSelect2()
{
    $arr = MNC::find()->joinWith('mccModel')->asArray()->all();

    $result = [];
    foreach ($arr as $elem) {
        $result[$elem['mnc'] . ':' . $elem['mcc']] = $elem['network'] . ' (' . $elem['mccModel']['country'] . ')';
    }
    return $result;
}
?>

<form>
    <div class="row">
        <div class="col-xs-1">
            Дата от
            <?php echo DatePicker::widget([
                'name' => 'fromDate',
                'type' => DatePicker::TYPE_INPUT,
                'value' => $fromDate ? $fromDate : date('Y-m-01', time()),
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                    'todayHighlight' => true,
                ],
                'options' => [
                    'class' => 'process-datepicker',
                ],
            ]); ?>
        </div>

        <div class="col-xs-1">
            Дата до
            <?php echo DatePicker::widget([
                'name' => 'toDate',
                'type' => DatePicker::TYPE_INPUT,
                'value' => $toDate ? $toDate : date('Y-m-t', time()),
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                    'todayHighlight' => true,
                ],
                'options' => [
                    'class' => 'process-datepicker',
                ],
            ]); ?>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-xs-1">
            Группировать по
            <?= Select2::widget([
                'name' => 'groupBy',
                'data' => [
                    'mcc' => 'Стране',
                    'mnc' => 'Стране и Оператору',
                    'number' => 'Номеру',
                    'year' => 'Году',
                    'month' => 'Месяцу',
                    'day' => 'Дню',
                    'hour' => 'Часу'
                ],
                'value' => $searchModel->groupBy,
                'options' => [
                    'placeholder' => '-------'
                ],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ])
            ?>
        </div>
        <div class="col-xs-1">
            <input type="submit" value="Сформировать" style="margin-top: 20px">
        </div>
    </div>
</form>

<div><br>
    Показать статистику:
    <?= Html::a('За прошлый месяц', $previousMonthUrl); ?>&nbsp
    <?= Html::a('За этот месяц', $currentMonthUrl); ?>
</div>

<?php

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        'charge_time',
        [
            'attribute' => 'number_service_id',
            'format' => 'html',
            'value' => function($data) {
                $accountTariff = AccountTariff::findOne($data->number_service_id);
                return $accountTariff ? Html::a($accountTariff->getNameLight(), $accountTariff->getUrl()) : null;
            },
            'filter' => Select2::widget([
                'name' => 'number_service_id',
                'value' => $searchModel->number_service_id,
                'data' => AccountTariff::find()
                    ->where(['client_account_id' => $searchModel->account_id])
                    ->andWhere(['<>', 'voip_number', ''])
                    ->select('voip_number')
                    ->indexBy('id')
                    ->groupBy('voip_number')
                    ->column(),
                'options' => [
                    'class' => 'form-control',
                    'placeholder' => 'Выберите значение'
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                    'selectOnClose' => true,
                ]
            ]),
        ],
        'rate',
        [
            'attribute' => 'cost',
            'value' => function ($data)
            {   
                return number_format($data['cost'], 6);
            }
        ],
        [
            'attribute' => 'quantity',
            'value' => function ($data)
            {   
                return $data->getFormattedQuantity();
            }
        ],
        [
            'attribute' => 'mnc',
            'value' => 'mncModel.network',
            'filter' => Select2::widget([
                'name' => 'network',
                'value' => $searchModel->network,
                'data' => getMncForSelect2(),
                'options' => [
                    'class' => 'form-control',
                    'placeholder' => 'Выберите значение'
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                    'selectOnClose' => true,
                ]
            ]),
            'width' => '20%'
        ],
        [
            'attribute' => 'mcc',
            'value' => 'mccModel.country',
            'filter' => Select2::widget([
                'name' => 'mcc',
                'value' => $searchModel->mcc,
                'data' => MCC::find()->select('country')->indexBy('mcc')->column(),
                'options' => [
                    'class' => 'form-control',
                    'placeholder' => 'Выберите значение'
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                    'selectOnClose' => true,
                ]
            ]),
            'width' => '20%'
        ],
    ]

]);

?>

<div>Общая стоимость: <?= $searchModel->getAllCost() ?></div>
<div>Общее количество: <?= DataRaw::formatQuantity($searchModel->getAllQuantity()) ?></div>

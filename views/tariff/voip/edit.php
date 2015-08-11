<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use app\models\Currency;
use app\models\Region;
use app\models\Country;
use app\models\TariffVoip;
use app\models\billing\Pricelist;

$optionDisabled = $creatingMode ? [] : ['disabled' => 'disabled'];

$countries = Country::dao()->getList(true);

$connectionPoints = Region::find()->orderBy('name')->all();
$connectionPointsOptions = [];
foreach ($connectionPoints as $connectionPoint) {
    $connectionPointsOptions[$connectionPoint->id] = [
        'data-country-id' => $connectionPoint->country_id
    ];
}
$connectionPoints = ['' => '-- Точка подключения --'] + ArrayHelper::map($connectionPoints, 'id', 'name');

$destinations = ['0' => '-- Направление --'] + TariffVoip::$destinations;
$currencies = ['' => '-- Валюта --'] + Currency::dao()->getList('id', true);
$statuses = TariffVoip::$statuses;

$priceLists =
    Pricelist::find()
        ->select(['id', 'name', 'price_include_vat'])
        ->andWhere(['orig' => 1, 'local' => 0])
        ->orderBy('region desc, name asc')
        ->all();
$priceListsOptions = [];
foreach ($priceLists as $priceList) {
    $priceListsOptions[$priceList->id] = [
        'data-type' => $priceList->price_include_vat == 1 ? 1 : 0
    ];
}
$priceLists = ['0' => '-- Прайс-лист --'] + ArrayHelper::map($priceLists, 'id', 'name');
?>

<legend>
    <?= Html::a('Тарифы IP Телефонии', '/tariff/voip'); ?> ->
    <?= ($model->name ? Html::encode($model->name) : 'Новый тариф'); ?>
</legend>

<div class="well">
    <?php

    $form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 3,
        'attributes' => [
            'country_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $countries, 'options' => ['class' => 'select2'] + $optionDisabled],
            'connection_point_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $connectionPoints, 'options' =>
                [
                    'class' => 'select2',
                    'options' => $connectionPointsOptions,
                ] + $optionDisabled
            ],
            'dest' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $destinations, 'options' => ['class' => 'select2'] + $optionDisabled],
            'currency_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $currencies, 'options' => ['class' => 'select2'] + $optionDisabled],
            'status' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $statuses, 'options' => ['class' => 'select2']],
            'pricelist_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $priceLists, 'options' => [
                'class' => 'select2',
                'options' => $priceListsOptions,
            ]],
        ],
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 2,
        'attributes' => [
            'name' => ['type' => Form::INPUT_TEXT],
            'name_short' => ['type' => Form::INPUT_TEXT],
        ],
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 3,
        'attributes' => [
            'month_line' => ['type' => Form::INPUT_TEXT],
            'month_number' => ['type' => Form::INPUT_TEXT],
            'month_min_payment' => ['type' => Form::INPUT_TEXT],
            'once_line' => ['type' => Form::INPUT_TEXT],
            'once_number' => ['type' => Form::INPUT_TEXT],
            'free_local_min' => ['type' => Form::INPUT_TEXT],
        ],
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 3,
        'attributes' => [
            'freemin_for_number' => ['type' => Form::INPUT_CHECKBOX],
            'paid_redirect' => ['type' => Form::INPUT_CHECKBOX],
        ],
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 3,
        'attributes' => [
            'tariffication_by_minutes' => ['type' => Form::INPUT_CHECKBOX],
            'tariffication_full_first_minute' => ['type' => Form::INPUT_CHECKBOX],
            'tariffication_free_first_seconds' => ['type' => Form::INPUT_CHECKBOX],
        ],
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 3,
        'attributes' => [
            'is_virtual' => ['type' => Form::INPUT_CHECKBOX],
            'is_testing' => ['type' => Form::INPUT_CHECKBOX],
            'price_include_vat' => ['type' => Form::INPUT_CHECKBOX],
        ],
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'attributes' => [
            'id' => ['type' => Form::INPUT_RAW, 'value' => Html::activeHiddenInput($model, 'id')],
            'actions' => [
                'type' => Form::INPUT_RAW,
                'value' =>
                    '<div class="col-md-offset-2 col-md-10" style="text-align: right;">' .
                        Html::a(
                            'Отмена',
                            ['index'],
                            [
                                'class' => 'btn btn-default btn-sm',
                                'style' => 'margin-right: 15px;',
                            ]
                        ) .
                        Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) .
                    '</div>'
            ],
        ],
    ]);

    echo Html::activeHiddenInput($model, 'scenario', ['id' => 'scenario']);
    ActiveForm::end();
    ?>
</div>

<?php if ($model->id): ?>
    <div class="well">
        <b>Последние изменения</b><br />
        Пользователь, изменивший тариф последний раз: <?= $user->name; ?><br />
        Время последнего изменения тарифа: <?= (new DateTime($model->edit_time))->format('H:i d.m.Y'); ?>
    </div>
<?php endif; ?>

<script type="text/javascript" src="/js/behaviors/pricelist-voip-filter.js"></script>
<script type="text/javascript" src="/js/behaviors/connection-point-voip-filter.js"></script>
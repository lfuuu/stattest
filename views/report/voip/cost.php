<style type="text/css">
    * {
        outline: none !important;
    }

    .fit-container {
        width: 100%;
    }
</style>

<script type="text/javascript">
    $(document).ready(function() {
        $('#server').change(function (event) {
            event.preventDefault();
            ajaxTrunkUpdate();
        });

        $('#operator').change(function (event) {
            event.preventDefault();
            ajaxTrunkUpdate();
        });

        function ajaxTrunkUpdate()
        {
            $.post(
                '/report/voip/cost-report',
                {
                    'operation': 'update_trunks',
                    'server_id': $('#server').val(),
                    'operator_id': $('#operator').val()
                },
                function(r) {
                    if (r.status == 'success') {
                        $('#trunk option').remove();

                        $.each(r.data, function(index, value) {
                            $('#trunk').append('<option value="' + value.id + '">' + value.text + '</option>');
                        });
                        //$('#trunk').select2('data', r.data);
                    }
                },
                'json'
            );
        }
    });

</script>

<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 12/28/15
 * Time: 1:45 PM
 */
use yii\helpers\Html;

/** @var $trunkModel app\models\billing\Trunk */
?>

<div class="filter-head">
    <form action="/report/voip/cost-report" method="get">
    <div class="row">
        <!-- Транк -->
        <div class="col-md-3">
            <label for="trunk">Транк</label>
            <?php
            echo Html::dropDownList(
                'trunk',
                null,
                ['-- Транк --'] + \yii\helpers\ArrayHelper::map($trunkModel, 'id', 'name'),
                [
                    'class' => 'select2 fit-container',
                    'id' => 'trunk',
                ]
            ) ?>
        </div>

        <!-- Точка присоединения -->
        <div class="col-md-3">
            <label for="server">Точка присоединения</label>
            <?php echo Html::dropDownList(
                'server',
                null,
                ['-- Точка присоединения --'] +
                    \yii\helpers\ArrayHelper::map($server, 'id', 'name'),
                [
                    'class' => 'select2 fit-container',
                    'id' => 'server',
                ]
            ) ?>
        </div>

        <!-- Оператор -->
        <div class="col-md-3">
            <label for="operator">Оператор</label>
            <?php echo Html::dropDownList(
                'operator',
                null,
                ['-- Оператор --'] +
                    \yii\helpers\ArrayHelper::map($operator, 'id', 'short_name'),
                [
                    'class' => 'select2 fit-container',
                    'id' => 'operator',
                ]
            ) ?>
        </div>

        <!-- Номер договора -->
        <div class="col-md-3">
            <label for="contract_number">Номер договора</label>
            <?php
            echo \yii\helpers\Html::textInput('contract_number', null, ['class' => 'form-control active']);
            ?>
        </div>
    </div>

    <div class="row">
        <!-- Период отчета -->
        <div class="col-md-3">
            <label for="dateRange">Период отчета</label>
            <?php echo \kartik\daterange\DateRangePicker::widget([
                'name' => 'dateRange'
                                                            ]) ?>
        </div>

        <!-- Моб. / стац. -->
        <div class="col-md-3">
            <label for="mob_or_base">Моб./Стац.</label>
            <?php echo Html::dropDownList(
                'mob_or_base',
                null,
                [
                    1 => 'Моб./Стац.',
                    2 => 'Мобильные',
                    3 => 'Стационарные',
                ],
                [
                    'class' => 'select2 fit-container',
                    
                ]
            ) ?>
        </div>

        <!-- Оригинация / терминация -->
        <div class="col-md-3">
            <label for="orig_term">Оригинация / терминация</label>
            <?php echo Html::dropDownList(
                'orig_term',
                null,
                [
                    1 => 'Все',
                    2 => 'Оригинация / терминация',
                    3 => 'Оригинация',
                    4 => 'Терминация',
                ],
                [
                    'class' => 'select2 fit-container',
                    
                ]
            ) ?>
        </div>

        <!-- С длительностью -->
        <div class="col-md-3">
            <label for="time">С длительностью</label>
            <?php echo Html::dropDownList(
                'time',
                null,
                [
                    1 => 'С длительностью',
                    2 => 'Без длительности',
                    3 => 'Все',
                ],
                [
                    'class' => 'select2 fit-container',
                    
                ]
            ) ?>
        </div>
    </div>
    <div class="row">
        <!-- Часовой пояс ЛС -->

        <!-- Регион -->
        <div class="col-md-3">
            <label for="region">Регион</label>
            <?php echo Html::dropDownList(
                'region',
                null,
                ['-- Регион --'] +
                    \yii\helpers\ArrayHelper::map($regionModel, 'id', 'name'),
                [
                    'class' => 'select2 fit-container',
                    
                ]
            ) ?>
        </div>

        <!-- Страна -->
        <div class="col-md-3">
            <label for="country">Страна</label>
            <?php echo Html::dropDownList(
                'country',
                null,
                ['-- Страна --'] +
                    \yii\helpers\ArrayHelper::map($geoCountry, 'id', 'name'),
                [
                    'class' => 'select2 fit-container',
                    
                ]
            ) ?>
        </div>

        <div class="col-md-3"></div>
        <div class="col-md-3"></div>
    </div>

    <div class="row">
        <div class="col-md-10"></div>

        <div class="col-md-2">
            <button class="btn btn-success">Фильтровать</button>
        </div>
    </div>
    </form>

    <div class="row">
        <div class="col-md-3">
            Время: <?= round($totals['billed_time'] / 60) ?>
        </div>

        <div class="col-md-3">
            Стоимость: <?= round($totals['cost'], 2) ?>
        </div>

        <div class="col-md-3">
            Стоимость интерконнекта: <?= round($totals['interconnect_cost'], 2) ?>
        </div>

        <div class="col-md-3">
            Стоимость с интерконнектом: <?= round( ($totals['cost'] + $totals['interconnect_cost'] ), 2) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?php
            echo \kartik\grid\GridView::widget([
                'dataProvider' => $dataProvider,
                'pjax' => true,
                'responsive' => true,
                'columns' => [
                    'prefix',
                    'destination',
                    'calls_count',
                    'billed_time',
                    'interconnect_cost',
                    'cost',
                    [
                        'attribute' => 'No Interconnect Cost',
                        'format' => 'raw',
                        'value' => function($model, $key, $index, $column) {
                            return $model['cost'] + $model['interconnect_cost'];
                        }
                    ]
                ]
            ]);
            ?>
        </div>
    </div>
</div>

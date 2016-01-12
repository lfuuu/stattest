<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 12/28/15
 * Time: 1:45 PM
 */

/** @var $trunkModel app\models\billing\Trunk */
?>

<div class="filter-head">
    <form action="/report/voip/cost-report" method="post">
    <div class="row">
        <!-- Транк -->
        <div class="col-md-3">
            <label for="trunk">Транк</label>
            <?php echo \yii\bootstrap\Html::dropDownList(
                'trunk',
                null,
                ['-- Транк --'] + \yii\helpers\ArrayHelper::map($trunkModel, 'id', 'name')
            ) ?>
        </div>

        <!-- Точка присоединения -->
        <div class="col-md-3">
            <label for="server">Точка присоединения</label>
            <?php echo \yii\bootstrap\Html::dropDownList(
                'server',
                null,
                ['-- Точка присоединения --'] +
                    \yii\helpers\ArrayHelper::map($server, 'id', 'name')
            ) ?>
        </div>

        <!-- Оператор -->
        <div class="col-md-3">
            <label for="operator">Оператор</label>
            <?php echo \yii\bootstrap\Html::dropDownList(
                'operator',
                null,
                ['-- Оператор --'] +
                    \yii\helpers\ArrayHelper::map($operator, 'id', 'short_name')
            ) ?>
        </div>

        <!-- Номер договора -->
        <div class="col-md-3">
            <label for="contract_number">Номер договора</label>
            <?php
            echo \yii\bootstrap\Html::textInput('contract_number', null);
            ?>
        </div>

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
            <?php echo \yii\bootstrap\Html::dropDownList(
                'mob_or_base',
                null,
                [
                    1 => 'Моб./Стац.',
                    2 => 'Мобильные',
                    3 => 'Стационарные',
                ]
            ) ?>
        </div>

        <!-- Оригинация / терминация -->
        <div class="col-md-3">
            <label for="orig_term">Оригинация / терминация</label>
            <?php echo \yii\bootstrap\Html::dropDownList(
                'orig_term',
                null,
                [
                    1 => 'Все',
                    2 => 'Оригинация / терминация',
                    3 => 'Оригинация',
                    4 => 'Терминация',
                ]
            ) ?>
        </div>

        <!-- С длительностью -->
        <div class="col-md-3">
            <label for="time">С длительностью</label>
            <?php echo \yii\bootstrap\Html::dropDownList(
                'time',
                null,
                [
                    1 => 'С длительностью',
                    2 => 'Без длительности',
                    3 => 'Все',
                ]
            ) ?>
        </div>

        <!-- Часовой пояс ЛС -->

        <!-- Регион -->
        <div class="col-md-3">
            <label for="region">Регион</label>
            <?php echo \yii\bootstrap\Html::dropDownList(
                'region',
                null,
                ['-- Регион --'] +
                    \yii\helpers\ArrayHelper::map($regionModel, 'id', 'name')
            ) ?>
        </div>

        <!-- Страна -->
        <div class="col-md-3">
            <label for="country">Страна</label>
            <?php echo \yii\bootstrap\Html::dropDownList(
                'country',
                null,
                ['-- Страна --'] +
                    \yii\helpers\ArrayHelper::map($geoCountry, 'id', 'name')
            ) ?>
        </div>
        </div>

        <div class="row">
            <div class="col-md-10"></div>

            <div class="col-md-2">
                <button class="btn btn-success">Фильтровать</button>
            </div>
        </div>
    </form>

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
                        'no_interconnect_cost',
                        'interconnect_cost',
                    ]
                ]);
                ?>
            </div>
        </div>
</div>
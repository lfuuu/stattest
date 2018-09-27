<?php
/**
 * Редактирование номера
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\nnp\models\Number $number
 */

use app\modules\nnp\models\City;
use app\modules\nnp\models\Operator;
use app\modules\nnp\models\Region;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => $this->title = 'Портированные номера', 'url' => '/nnp/number/'],
        $number->full_number
    ],
]) ?>

<div class="well">
    <?php $form = ActiveForm::begin() ?>

    <div class="row">

        <?php // Номер ?>
        <div class="col-sm-3">
            <label><?= $number->getAttributeLabel('full_number') ?></label>
            <div><?= $number->full_number ?></div>
        </div>

        <?php // Исходный регион ?>
        <div class="col-sm-3">
            <label><?= $number->getAttributeLabel('region_source') ?></label>
            <div><?= htmlspecialchars($number->region_source) ?></div>
        </div>

        <?php // Исходный город ?>
        <div class="col-sm-3">
            <label><?= $number->getAttributeLabel('city_source') ?></label>
            <div><?= htmlspecialchars($number->city_source) ?></div>
        </div>

        <?php // Исходный оператор ?>
        <div class="col-sm-3">
            <label><?= $number->getAttributeLabel('operator_source') ?></label>
            <div><?= htmlspecialchars($number->operator_source) ?></div>
        </div>

    </div>
    <br/>

    <div class="row">

        <?php // Страна ?>
        <div class="col-sm-3">
            <label><?= $number->getAttributeLabel('country_code') ?></label>
            <div><?= $number->country->name_rus ?></div>
        </div>

        <?php // Регион ?>
        <div class="col-sm-3">
            <?= $form->field($number, 'region_id')->widget(Select2::class, [
                'data' => Region::getList($isWithEmpty = true, $isWithNullAndNotNull = false, $number->country_code),
            ]) ?>
        </div>

        <?php // Город ?>
        <div class="col-sm-3">
            <?= $form->field($number, 'city_id')->widget(Select2::class, [
                'data' => City::getList($isWithEmpty = true, $isWithNullAndNotNull = false, $number->country_code),
            ]) ?>
        </div>

        <?php // Оператор ?>
        <div class="col-sm-3">
            <?= $form->field($number, 'operator_id')->widget(Select2::class, [
                'data' => Operator::getList($isWithEmpty = true, $isWithNullAndNotNull = false, $number->country_code),
            ]) ?>
        </div>

    </div>

    <?php // кнопки ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . ($number->isNewRecord ? 'Create' : 'Save')) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

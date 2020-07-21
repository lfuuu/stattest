<?php
/**
 * Просмотр диапазона номеров
 *
 * @var \app\classes\BaseView $this
 * @var Form $formModel
 */

use app\modules\nnp2\forms\rangeShort\Form;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$model = $formModel->rangeShort;
$this->title = sprintf('%s - %s', $model->ndc . ' ' . $model->number_from, $model->ndc . ' ' . $model->number_to);

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план 2.0', 'url' => '/nnp2/'],
        ['label' => 'Готовый список номеров', 'url' => $cancelUrl = '/nnp2/range-short/'],
        $this->title
    ],
]) ?>

<div class="well">
    <?php
    $form = ActiveForm::begin();
    $viewParams = [
        'formModel' => $formModel,
        'form' => $form,
    ];
    ?>

    <div class="row">

        <div class="col-sm-3">
            <label>Готовый диапазон</label>
            <div>
                <?= $this->title ?>
            </div>
        </div>

        <?php // Страна ?>
        <div class="col-sm-3">
            <label><?= $model->getAttributeLabel('country_code') ?></label>
            <div><?= $model->country ? $model->country->name_rus : Yii::t('common', '(not set)') ?></div>
        </div>

        <?php // NDC ?>
        <div class="col-sm-3">
            <label><?= $model->getAttributeLabel('ndc') ?></label>
            <div><?= $model->ndc ?></div>
        </div>

    </div>
    <br/>

    <div class="row">

        <?php // Регион ?>
        <div class="col-sm-3">
            <label><?= $model->getAttributeLabel('region_id') ?></label>
            <div><?= $model->region ? $model->region->name : Yii::t('common', '(not set)') ?></div>
        </div>

        <?php // Город ?>
        <div class="col-sm-3">
            <label><?= $model->getAttributeLabel('city_id') ?></label>
            <div><?= $model->city ? $model->city->name : Yii::t('common', '(not set)') ?></div>
        </div>

        <?php // Оператор ?>
        <div class="col-sm-3">
            <label><?= $model->getAttributeLabel('operator_id') ?></label>
            <div><?= $model->operator ? $model->operator->name : Yii::t('common', '(not set)') ?></div>
        </div>

        <?php // Тип NDC ?>
        <div class="col-sm-3">
            <label><?= $model->getAttributeLabel('ndc_type_id') ?></label>
            <div>
                <?= $model->ndcType ? $model->ndcType->name : Yii::t('common', '(not set)') ?>
                <?= ' ('. ($model->ndc_type_id ? : Yii::t('common', '(not set)')).')' ?>
            </div>
        </div>

    </div>

    <?php // кнопки ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

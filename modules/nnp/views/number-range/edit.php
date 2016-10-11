<?php
/**
 * Создание/редактирование диапазона номеров
 *
 * @var \yii\web\View $this
 * @var Form $formModel
 */

use app\models\City;
use app\modules\nnp\forms\numberRange\Form;
use app\modules\nnp\models\Operator;
use app\modules\nnp\models\Region;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$numberRange = $formModel->numberRange;

if (!$numberRange->isNewRecord) {
    $this->title = $numberRange->ndc . ' ' . $numberRange->number_from . ' - ' . $numberRange->number_to;
} else {
    $this->title = Yii::t('common', 'Create');
}
?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => 'Диапазон номеров', 'url' => $cancelUrl = '/nnp/number-range/'],
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

        <?php // Номер от ?>
        <div class="col-sm-2">
            <label><?= $numberRange->getAttributeLabel('full_number_from') ?></label>
            <div><?= $numberRange->full_number_from ?></div>
        </div>

        <?php // Номер до ?>
        <div class="col-sm-2">
            <label><?= $numberRange->getAttributeLabel('full_number_to') ?></label>
            <div><?= $numberRange->full_number_to ?></div>
        </div>

        <?php // Вкл. ?>
        <div class="col-sm-2">
            <label><?= $numberRange->getAttributeLabel('is_active') ?></label>
            <div><?= Yii::t('common', $numberRange->is_active ? 'Yes' : 'No') ?></div>
        </div>

        <?php // Тип NDC ?>
        <div class="col-sm-4">
            <label><?= $numberRange->getAttributeLabel('ndc_type_id') ?></label>
            <div><?= $numberRange->ndc_type_id ? htmlspecialchars($numberRange->ndcType->name) : '' ?></div>
        </div>

    </div>
    <br/>

    <div class="row">

        <?php // Исходный регион ?>
        <div class="col-sm-6">
            <label><?= $numberRange->getAttributeLabel('region_source') ?></label>
            <div><?= htmlspecialchars(str_replace('|', ', ', $numberRange->region_source)) ?></div>
        </div>

        <?php // Исходный оператор ?>
        <div class="col-sm-4">
            <label><?= $numberRange->getAttributeLabel('operator_source') ?></label>
            <div><?= htmlspecialchars($numberRange->operator_source) ?></div>
        </div>

    </div>
    <br/>

    <div class="row">

        <?php // Регион ?>
        <div class="col-sm-3">
            <?= $form->field($numberRange, 'region_id')->widget(Select2::className(), [
                'data' => Region::getList($isWithEmpty = true, $isWithNullAndNotNull = false),
            ]) ?>
        </div>

        <?php // Город ?>
        <div class="col-sm-3">
            <?= $form->field($numberRange, 'city_id')->widget(Select2::className(), [
                'data' => City::dao()->getList($isWithEmpty = true, $isWithNullAndNotNull = false),
            ]) ?>
        </div>

        <?php // Оператор ?>
        <div class="col-sm-4">
            <?= $form->field($numberRange, 'operator_id')->widget(Select2::className(), [
                'data' => Operator::getList($isWithEmpty = true, $isWithNullAndNotNull = false),
            ]) ?>
        </div>

    </div>

    <?php // кнопки ?>
    <div class="form-group">
        <?= $this->render('//layouts/_submitButton' . ($numberRange->isNewRecord ? 'Create' : 'Save')) ?>
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
/**
 * Создание/редактирование оператора
 *
 * @var \yii\web\View $this
 * @var Form $formModel
 */

use app\models\Country;
use app\modules\nnp\forms\operator\Form;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$operator = $formModel->operator;

if (!$operator->isNewRecord) {
    $this->title = $operator->name;
} else {
    $this->title = Yii::t('common', 'Create');
}
?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => 'Операторы', 'url' => $cancelUrl = '/nnp/operator/'],
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

        <?php // Префикс страны ?>
        <div class="col-sm-2">
            <?= $form->field($operator, 'country_prefix')->widget(Select2::className(), [
                'data' => Country::getList(true, 'prefix'),
            ]) ?>
        </div>

        <?php // Название ?>
        <div class="col-sm-6">
            <?= $form->field($operator, 'name')->textInput() ?>
        </div>

    </div>

    <?php // кнопки ?>
    <div class="form-group">
        <?= $this->render('//layouts/_submitButton' . ($operator->isNewRecord ? 'Create' : 'Save')) ?>
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?php if (!$operator->isNewRecord) : ?>
            <?= $this->render('//layouts/_submitButtonDrop') ?>
        <?php endif ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

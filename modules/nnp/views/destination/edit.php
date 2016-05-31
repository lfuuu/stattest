<?php
/**
 * Создание/редактирование направления
 *
 * @var \yii\web\View $this
 * @var DestinationForm $formModel
 */

use app\modules\nnp\forms\DestinationForm;
use app\modules\nnp\models\Prefix;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$destination = $formModel->destination;

if (!$destination->isNewRecord) {
    $this->title = $destination->name;
} else {
    $this->title = Yii::t('common', 'Create');
}
?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => 'Направления', 'url' => $cancelUrl = '/nnp/destination/'],
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

    <?php
    // сообщение об ошибке
    if ($formModel->validateErrors) {
        Yii::$app->session->setFlash('error', $formModel->validateErrors);
    }

    $prefixList = Prefix::getList(false);
    ?>

    <div class="row">

        <?php // Название ?>
        <div class="col-sm-4">
            <?= $form->field($destination, 'name')->textInput() ?>
        </div>

        <?php // префиксы (сложение) ?>
        <div class="col-sm-4">
            <label>Префиксы (+)</label>
            <?= Select2::widget([
                'name' => 'AdditionPrefixDestination[]',
                'value' => array_keys((array)$destination->additionPrefixDestinations),
                'data' => $prefixList,
                'options' => [
                    'multiple' => true,
                ],
            ]) ?>
        </div>

        <?php // префиксы (вычитание) ?>
        <div class="col-sm-4">
            <label>Префиксы (-)</label>
            <?= Select2::widget([
                'name' => 'SubtractionPrefixDestination[]',
                'value' => array_keys((array)$destination->subtractionPrefixDestinations),
                'data' => $prefixList,
                'options' => [
                    'multiple' => true,
                ],
            ]) ?>
        </div>
    </div>

    <?php // кнопки ?>
    <div class="form-group">
        <?= $this->render('//layouts/_submitButton' . ($destination->isNewRecord ? 'Create' : 'Save')) ?>
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?php if (!$destination->isNewRecord) : ?>
            <?= $this->render('//layouts/_submitButtonDrop') ?>
        <?php endif ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

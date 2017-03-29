<?php
/**
 * Создание/редактирование направления
 *
 * @var \app\classes\BaseView $this
 * @var Form $formModel
 */

use app\modules\nnp\forms\destination\Form;
use app\modules\nnp\models\Land;
use app\modules\nnp\models\Prefix;
use app\modules\nnp\models\Status;
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

    <?php $prefixList = Prefix::getList(false) ?>

    <div class="row">

        <?php // Название ?>
        <div class="col-sm-4">
            <?= $form->field($destination, 'name')->textInput() ?>
        </div>

        <?php // Территория ?>
        <div class="col-sm-4">
            <?= $form->field($destination, 'land_id')->widget(Select2::className(), [
                'data' => Land::getList($isWithEmpty = true),
            ]) ?>
        </div>

        <?php // Статус ?>
        <div class="col-sm-4">
            <?= $form->field($destination, 'status_id')->widget(Select2::className(), [
                'data' => Status::getList($isWithEmpty = true),
            ]) ?>
        </div>

    </div>

    <div class="row">

        <?php // префиксы (сложение) ?>
        <div class="col-sm-6">
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
        <div class="col-sm-6">
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
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . ($destination->isNewRecord ? 'Create' : 'Save')) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

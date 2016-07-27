<?php
/**
 * Создание/редактирование пакета
 *
 * @var \yii\web\View $this
 * @var Form $formModel
 */

use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\modules\nnp\forms\package\Form;
use app\modules\nnp\models\Prefix;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$package = $formModel->package;

if (!$package->isNewRecord) {
    $this->title = (string)$package;
} else {
    $this->title = Yii::t('common', 'Create');
}
?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => 'Пакеты', 'url' => $cancelUrl = '/nnp/package/'],
        $this->title
    ],
]) ?>

<?php
$form = ActiveForm::begin();
$viewParams = [
    'formModel' => $formModel,
    'form' => $form,
];
?>

<?php $prefixList = Prefix::getList(false) ?>

<div class="well">
    <div class="row">

        <?php // Тариф ?>
        <div class="col-sm-4">
            <?= $form->field($package, 'tariff_id')->widget(Select2::className(), [
                'data' => Tariff::getList($package->isNewRecord, $isWithNullAndNotNull = false, $serviceTypeId = ServiceType::ID_VOIP_PACKAGE),
            ]) ?>
        </div>

    </div>
</div>

<?php // Пакеты. Предоплаченные минуты ?>
<?= $this->render('_editMinute', $viewParams) ?>

<?php // Пакеты. Цена по направлениям ?>
<?= $this->render('_editPrice', $viewParams) ?>

<?php // Пакеты. Прайслист с МГП (минимальный гарантированный платеж) ?>
<?= $this->render('_editPricelist', $viewParams) ?>

<?php // кнопки ?>
<div class="form-group">
    <?= $this->render('//layouts/_submitButton' . ($package->isNewRecord ? 'Create' : 'Save')) ?>
    <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
    <?php if (!$package->isNewRecord) : ?>
        <?= $this->render('//layouts/_submitButtonDrop') ?>
    <?php endif ?>
</div>

<?php ActiveForm::end(); ?>

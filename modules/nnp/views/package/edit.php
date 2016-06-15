<?php
/**
 * Создание/редактирование направления
 *
 * @var \yii\web\View $this
 * @var Form $formModel
 */

use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\modules\nnp\forms\package\Form;
use app\modules\nnp\models\Destination;
use app\modules\nnp\models\Package;
use app\modules\nnp\models\Prefix;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$package = $formModel->package;

if (!$package->isNewRecord) {
    $this->title = $package->name;
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
            <?= $form->field($package, 'name')->textInput() ?>
        </div>

        <?php // Тариф ?>
        <div class="col-sm-4">
            <?= $form->field($package, 'tariff_id')->widget(Select2::className(), [
                'data' => Tariff::getList($package->isNewRecord, $isWithNullAndNotNull = false, $serviceTypeId = ServiceType::ID_VOIP_PACKAGE),
            ]) ?>
        </div>

        <?php // Период ?>
        <div class="col-sm-4">
            <?= $form->field($package, 'period_id')->widget(Select2::className(), [
                'data' => Package::getPeriodList($package->isNewRecord),
            ]) ?>
        </div>

    </div>

    <div class="row">

        <?php // Тип пакета ?>
        <div class="col-sm-4">
            <?= $form->field($package, 'package_type_id')->widget(Select2::className(), [
                'data' => Package::getPackageTypeList($package->isNewRecord),
            ]) ?>
        </div>

        <?php // Направление ?>
        <div class="col-sm-4" id="div-destination">
            <?= $form->field($package, 'destination_id')->widget(Select2::className(), [
                'data' => Destination::getList($package->isNewRecord),
            ]) ?>
        </div>

        <?php // Прайслист ?>
        <div class="col-sm-4" id="div-pricelist">
            <?= $form->field($package, 'pricelist_id')->widget(Select2::className(), [
                'data' => \app\models\billing\Pricelist::getList($package->isNewRecord, $isWithNullAndNotNull = false, $type = 'client', $orig = true),
            ]) ?>
        </div>

        <?php // Цена ?>
        <div class="col-sm-4" id="div-price">
            <?= $form->field($package, 'price')->textInput() ?>
        </div>

        <?php // Минуты ?>
        <div class="col-sm-4" id="div-minute">
            <?= $form->field($package, 'minute')->textInput() ?>
        </div>

    </div>

    <?php // кнопки ?>
    <div class="form-group">
        <?= $this->render('//layouts/_submitButton' . ($package->isNewRecord ? 'Create' : 'Save')) ?>
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?php if (!$package->isNewRecord) : ?>
            <?= $this->render('//layouts/_submitButtonDrop') ?>
        <?php endif ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<script type='text/javascript'>
    $(function () {
        $("#package-package_type_id")
            .on("change", function (e, item) {
                var $typeId = $(this).val();
                switch ($typeId) {
                    case '<?= Package::PACKAGE_TYPE_MINUTE ?>':
                        $('#div-destination').show();
                        $('#div-pricelist').hide();
                        $('#div-price').hide();
                        $('#div-minute').show();
                        break;
                    case '<?= Package::PACKAGE_TYPE_PRICE ?>':
                        $('#div-destination').show();
                        $('#div-pricelist').hide();
                        $('#div-price').show();
                        $('#div-minute').hide();
                        break;
                    case '<?= Package::PACKAGE_TYPE_PRICELIST ?>':
                        $('#div-destination').hide();
                        $('#div-pricelist').show();
                        $('#div-price').hide();
                        $('#div-minute').hide();
                        break;
                    default:
                        $('#div-destination').hide();
                        $('#div-pricelist').hide();
                        $('#div-price').hide();
                        $('#div-minute').hide();
                        break;
                }
            })
            .trigger('change');

    });
</script>

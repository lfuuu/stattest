<?php
/**
 * Свойства тарифа для телефонии. Подраздел Типы NDC
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\modules\nnp\models\NdcType;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffVoipNdcType;
use kartik\select2\Select2;

$tariffVoipNdcTypes = $formModel->tariffNdcTypes;
$ndcTypeList = NdcType::getList($isWithEmpty = false);
$tariff = $formModel->tariff;

$tariffVoipNdcTypeTableName = TariffVoipNdcType::tableName();
$tariffTableName = Tariff::tableName();
?>

<div class="row">
    <div class="col-sm-12">
        <label>
            <?= Yii::t('models/' . $tariffVoipNdcTypeTableName, 'ndc_type_id') ?>
            <?= $this->render('//layouts/_helpConfluence', $tariff->serviceType->getHelpConfluence()) ?>
        </label>
        <?= Select2::widget([
            'name' => 'TariffVoipNdcType[]',
            'value' => array_keys($tariffVoipNdcTypes),
            'data' => $ndcTypeList,
            'options' => [
                'multiple' => true,
            ],
        ]) ?>
    </div>
</div>

<?php if (!$tariff->isNewRecord) : ?>
    <?= $this->render('//layouts/_showHistory', [
        'parentModel' => [new TariffVoipNdcType(), $tariff->id],
    ]) ?>
<?php endif; ?>

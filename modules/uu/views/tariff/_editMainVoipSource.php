<?php
/**
 * Свойства тарифа для телефонии. Подраздел Источник номера
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

$tariffVoipSource = $formModel->tariffSource;
$sourceList = \app\models\voip\Source::getList($isWithEmpty = false);
$tariff = $formModel->tariff;

$tariffVoipSourceTableName = \app\modules\uu\models\TariffVoipSource::tableName();
$tariffTableName = Tariff::tableName();
?>

<div class="row">
    <div class="col-sm-12">
        <label>
            <?= Yii::t('models/' . $tariffVoipSourceTableName, 'source_code') ?>
            <?= $this->render('//layouts/_helpConfluence', $tariff->serviceType->getHelpConfluence()) ?>
        </label>
        <?= Select2::widget([
            'name' => 'TariffVoipSource[]',
            'value' => array_keys($tariffVoipSource),
            'data' => $sourceList,
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

<?php
/**
 * кнопка сохранения
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\modules\uu\controllers\TariffController;
use yii\helpers\Url;

$tariff = $formModel->tariff;
?>

<?php if ($editableType != TariffController::EDITABLE_NONE) : ?>

    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => Url::to(['/uu/tariff', 'serviceTypeId' => $formModel->tariff->service_type_id])]) ?>
        <?= $this->render('//layouts/_submitButton' . ($tariff->isNewRecord ? 'Create' : 'Save')) ?>

        <?= $tariff->isNewRecord ? '' : $this->render('//layouts/_submitButton', [
            'text' => Yii::t('common', 'Clone'),
            'glyphicon' => 'glyphicon-transfer',
            'params' => [
                'name' => 'cloneButton',
                'value' => 1,
                'class' => 'btn btn-default pull-left',
                'aria-hidden' => 'true',
                'onClick' => sprintf('return confirm("%s");', Yii::t('common', 'Are you sure to clone?')),
            ],
        ]) ?>
    </div>

<?php endif ?>
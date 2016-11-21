<?php
/**
 * кнопка сохранения
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\controllers\uu\TariffController;
use yii\helpers\Url;

$tariff = $formModel->tariff;
?>

<?php if ($editableType != TariffController::EDITABLE_NONE) : ?>

    <div class="form-group">
        <?= $this->render('//layouts/_submitButton' . ($tariff->isNewRecord ? 'Create' : 'Save')) ?>
        <?= $this->render('//layouts/_buttonCancel', ['url' => Url::to(['uu/tariff', 'serviceTypeId' => $formModel->tariff->service_type_id])]) ?>
        <?php if (!$tariff->isNewRecord && $editableType == TariffController::EDITABLE_FULL) : ?>
            <?= $this->render('//layouts/_submitButtonDrop') ?>
        <?php endif ?>
    </div>

<?php endif ?>
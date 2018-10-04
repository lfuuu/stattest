<?php
/**
 * свойства тарифа для "телефонии. Пакеты Интернета"
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\modules\nnp\models\Package;
use app\modules\uu\controllers\TariffController;
use app\modules\uu\models\Tariff;

$tariff = $formModel->tariff;
$package = $tariff->package;
if (!$package) {
    $package = new Package;
}

$viewParams = [
    'formModel' => $formModel,
    'form' => $form,
    'editableType' => $editableType,
];

if ($editableType <= TariffController::EDITABLE_LIGHT) {
    $options = ['disabled' => 'disabled'];
} else {
    $options = [];
}

$helpConfluence = $this->render('//layouts/_helpConfluence', Tariff::getHelpConfluence());
?>

<div class="well">
    <?= $form->field($tariff, 'count_of_carry_period')
        ->textInput($options)
        ->label($tariff->getAttributeLabel('count_of_carry_period') . $helpConfluence)
    ?>
</div>
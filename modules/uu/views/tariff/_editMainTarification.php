<?php
/**
 * Свойства тарификации пакета
 *
 * @var \app\classes\BaseView $this
 * @var \yii\widgets\ActiveForm $form
 * @var Package $package
 * @var array $options
 */

use app\modules\nnp\models\Package;
use app\modules\uu\models\ServiceType;
use kartik\select2\Select2;

$helpConfluence = $this->render('//layouts/_helpConfluence', ServiceType::getHelpConfluenceById(ServiceType::ID_VOIP_PACKAGE_CALLS));
?>

<div class="row">
    <div class="col-sm-3">
        <?= $form->field($package, 'tarification_free_seconds')
            ->textInput($options + ['type' => 'number'])
            ->label($package->getAttributeLabel('tarification_free_seconds') . $helpConfluence)
        ?>
    </div>

    <div class="col-sm-3">
        <?= $form->field($package, 'tarification_min_paid_seconds')
            ->textInput($options + ['type' => 'number'])
            ->label($package->getAttributeLabel('tarification_min_paid_seconds') . $helpConfluence)
        ?>
    </div>

    <div class="col-sm-3">
        <?= $form->field($package, 'tarification_interval_seconds')
            ->textInput($options + ['type' => 'number'])
            ->label($package->getAttributeLabel('tarification_interval_seconds') . $helpConfluence)
        ?>
    </div>

    <div class="col-sm-3">
        <?= $form->field($package, 'tarification_type')
            ->widget(Select2::class, [
                'data' => [Package::TARIFICATION_TYPE_CEIL => 'В большую сторону (ceil)', Package::TARIFICATION_TYPE_ROUND => 'Математическое округление (round)'],
                'options' => $options,
            ])
            ->label($package->getAttributeLabel('tarification_type') . $helpConfluence)
        ?>
    </div>

</div>

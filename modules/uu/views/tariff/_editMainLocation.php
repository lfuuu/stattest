<?php
/**
 * Свойства местоположения (для мобильной телефонии)
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

    <div class="col-sm-2">
        <?= $form->field($package, 'location_id')
            ->widget(Select2::class, [
                'data' => Package::getListLocation(),
                'options' => $options,
            ])
            ->label($package->getAttributeLabel('location_id') . $helpConfluence)
        ?>
    </div>

</div>

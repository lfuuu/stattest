<?php
/**
 * Создание/редактирование VPS-тарифа
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\tariffVmForm $formModel
 */

use app\modules\uu\models\ServiceType;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$tariffVm = $formModel->tariffVm;
$this->title = $tariffVm->isNewRecord ? Yii::t('common', 'Create') : $tariffVm->name;
$helpConfluence = $this->render('//layouts/_helpConfluence', ServiceType::getHelpConfluenceById(ServiceType::ID_VPS));
?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => Yii::t('tariff', 'Tariff VPS'), 'url' => $cancelUrl = Url::to(['/uu/tariff-vm'])],
        [
            'label' => $helpConfluence,
            'encode' => false,
        ],
        $this->title,
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

    <div class="row">

        <div class="col-sm-4">
            <?= $form->field($tariffVm, 'id')
                ->textInput(['type' => 'number', 'step' => 1])
                ->label($tariffVm->getAttributeLabel('id') . $helpConfluence)
            ?>
        </div>

        <div class="col-sm-4">
            <?= $form->field($tariffVm, 'name')
                ->textInput()
                ->label($tariffVm->getAttributeLabel('name') . $helpConfluence)
            ?>
        </div>

    </div>

    <?php // кнопки ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . ($tariffVm->isNewRecord ? 'Create' : 'Save')) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

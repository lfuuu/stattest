<?php
/**
 * Создание/редактирование типа
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\serviceTypeForm $formModel
 */
use yii\helpers\Html;
use app\modules\uu\models\ServiceType;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;


$serviceType = $formModel->serviceType;
$serviceTypeResources = $formModel->serviceTypeResources;
$serviceTypeActive = $formModel->serviceTypeActive;

$this->title = $serviceType->isNewRecord ? Yii::t('common', 'Create') : $serviceType->name;
?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => Yii::t('tariff', 'Service types'), 'url' => $cancelUrl = Url::to(['/uu/service-type'])],
        [
            'label' => $this->render('//layouts/_helpConfluence', ServiceType::getHelpConfluenceById(0)),
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
            <?= $form->field($serviceType, 'name')->textInput() ?>
        </div>
        <div class="col-sm-4">
            <?= $form->field($serviceType, 'close_after_days')->textInput() ?>
        </div>
        <div class="col-sm-4">
            <?= Html::activeCheckBox($serviceTypeActive, 'is_active',[
                    'onclick'=>'$(this).is(":checked")? $("#resources").show() : $("#resources").hide();', 
                    'label' => 'Вознаграждение'
                ]);
            ?>
        </div>
    </div>
    
    <?php if ($serviceTypeActive->is_active) : ?>
        <div id="resources" style ="display: block;">
    <?php else : ?>
        <div id="resources" style ="display: none;">
    <?php endif; ?>
        <?php if ($serviceTypeResources) : ?>
            <?= $this->render('_editServiceTypeResources', $viewParams) ?>
        <? endif; ?>
    </div>
    
    <?php // кнопки ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . ($serviceType->isNewRecord ? 'Create' : 'Save')) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
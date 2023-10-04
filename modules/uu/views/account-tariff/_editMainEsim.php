<?php
/**
 * Свойства услуги для A2P
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\AccountTariffForm $formModel
 * @var ActiveForm $form
 */

use app\modules\uu\models\ServiceType;
use kartik\form\ActiveForm;

$accountTariff = $formModel->accountTariff;
$accountTariffParent = $accountTariff->prevAccountTariff;
?>

<div class="row">

    <div class="col-sm-4">
        <?=  ($accountTariff->isNewRecord) ?
            $form->field($accountTariff, 'iccid')
            ->widget(\kartik\widgets\Select2::class, [
                'data' => $accountTariff->iccid ? [$accountTariff->iccid => $accountTariff->iccid] : [],
                'pluginOptions' => [
                    'allowClear' => true,
                    'minimumInputLength' => 3,
                    'ajax' => [
                        'url' => \yii\helpers\Url::to(['/uu/voip/iccid-list']),
                        'dataType' => 'json',
                        'data' => new \yii\web\JsExpression('function(params) { return {q:params.term}; }')
                    ],
                    'escapeMarkup' => new \yii\web\JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new \yii\web\JsExpression('function(city) { return city.text; }'),
                    'templateSelection' => new \yii\web\JsExpression('function (city) { return city.text; }'),
                    'language' => [
                        'errorLoading' => new \yii\web\JsExpression("function () { return 'Waiting for results...'; }"),
                    ],
                ]
            ]) :
            $form->field($accountTariff, 'iccid')->textInput(['disabled' => true])->label('ICCID')
        ?>
    </div>
</div>


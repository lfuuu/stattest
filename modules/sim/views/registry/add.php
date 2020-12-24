<?php

use app\classes\BaseView;
use app\modules\sim\models\RegionSettings;
use app\modules\sim\forms\registry\Form as RegistryForm;
use yii\widgets\Breadcrumbs;
use kartik\widgets\ActiveForm;
use app\classes\Html;
use kartik\builder\Form;

/**
 * Добавление сим-карт
 *
 * @var BaseView $this
 * @var RegistryForm $model
 */

$this->title = 'Добавление SIM-карт';
echo Html::formLabel($this->title);

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'SIM-карты', 'url' => '/sim/'],
        ['label' => 'Реестр SIM-карт', 'url' => $cancelUrl = '/sim/registry/'],
        $this->title
    ],
]) ?>

<div class="well">

    <div class="well">
        <?php

        $form = ActiveForm::begin([
            'id' => 'SimForm',
            'type' => ActiveForm::TYPE_VERTICAL,
            'enableClientValidation' => true,
        ]);

        //$this->registerJsVariable('simFormId', $form->getId());

        // строка 1
        $line1Attributes = [];
        $line1Attributes['region_sim_settings_id'] = [
            'type' => Form::INPUT_DROPDOWN_LIST,
            'items' => RegionSettings::getList(),
            'options' => [
                    'class' => 'formReload',
                ],
        ];

        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => count($line1Attributes),
            'attributes' => $line1Attributes
        ]);

        // строка 2
        $line2Attributes = [];
        $line2Attributes['iccid_from'] = [
            'type' => Form::INPUT_TEXT,
            'options' => [
                    'class' => 'formReloadOnLostFocus'
                ],
        ];

        $line2Attributes['iccid_to'] = [
            'type' => Form::INPUT_TEXT,
            'options' => [
                    'class' => 'formReloadOnLostFocus'
                ],
        ];

        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => count($line2Attributes),
            'attributes' => $line2Attributes
        ]);

        // строка 3
        $line3Attributes = [];
        $line3Attributes['imsi_from'] = [
            'type' => Form::INPUT_TEXT,
            'options' => [
                    'class' => 'formReloadOnLostFocus'
                ],
        ];

        $line3Attributes['imsi_to'] = [
            'type' => Form::INPUT_TEXT,
            'options' => [
                    'class' => 'formReloadOnLostFocus'
                ],
        ];

        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => count($line3Attributes),
            'attributes' => $line3Attributes
        ]);

        // строка 4
        $line4Attributes = [];
        $line4Attributes['imsi_s1_from'] = [
            'type' => Form::INPUT_TEXT,
            'options' => [
                    'class' => 'formReloadOnLostFocus'
                ],
        ];

        $line4Attributes['imsi_s1_to'] = [
            'type' => Form::INPUT_TEXT,
            'options' => [
                    'class' => 'formReloadOnLostFocus'
                ],
        ];

        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => count($line4Attributes),
            'attributes' => $line4Attributes
        ]);

        // строка 5
        $line5Attributes = [];
        $line5Attributes['imsi_s2_from'] = [
            'type' => Form::INPUT_TEXT,
            'options' => [
                    'class' => 'formReloadOnLostFocus'
                ],
        ];

        $line5Attributes['imsi_s2_to'] = [
            'type' => Form::INPUT_TEXT,
            'options' => [
                    'class' => 'formReloadOnLostFocus'
                ],
        ];

        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => count($line5Attributes),
            'attributes' => $line5Attributes
        ]);




        echo Html::submitButton('Создать заливку', [
            'class' => 'btn btn-success',
            'name' => 'save',
            'value' => 'Создать заливку'
        ]);

        ActiveForm::end();
        ?>
    </div>
</div>
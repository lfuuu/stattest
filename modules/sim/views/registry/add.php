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
<style type="text/css">
    form div label {
        font-weight: normal;
    }
    form div.required label {
        font-weight: bold;
    }
    form div.required label:after {
        content:" * ";
        color:red;
    }
</style>

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
            'requiredCssClass'=>'required'
        ]);

        $addLabelPrefixParams = function ($lineAttributes, $field, $type) {
            $lineAttributes[$field . '_prefix'] = [
                'type' => Form::INPUT_RAW,
                'value' =>
                    '<br /><br />' .
                    Html::tag(
                        'div',
                        '---',
                        [
                            'id' => 'label_prefix_' . $field,
                            'class' => 'label_prefix_' . $type,
                            'style' => 'white-space:nowrap; float:right;',
                        ]
                    ),
            ];

            return $lineAttributes;
        };

        $addText = function ($lineAttributes, $html = '') {
            $lineAttributes[uniqid()] = [
                'type' => Form::INPUT_RAW,
                'value' => $html,
            ];;

            return $lineAttributes;
        };

        $this->registerJsVariable('simFormId', $form->getId());

        // строка 1
        $line1Attributes = [];

        $line1Attributes = $addText($line1Attributes);
        $line1Attributes['region_sim_settings_id'] = [
            'type' => Form::INPUT_DROPDOWN_LIST,
            'items' => RegionSettings::getList(),
            'options' => [
                'class' => 'formReload',
            ],
        ];
        $line1Attributes = $addText($line1Attributes);
        $line1Attributes = $addText($line1Attributes);
        $line1Attributes = $addText($line1Attributes);
        $line1Attributes = $addText($line1Attributes);

        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => count($line1Attributes),
            'attributes' => $line1Attributes
        ]);

        // строка 2
        $line2Attributes = [];

        $line2Attributes = $addLabelPrefixParams($line2Attributes, 'iccid_from', 'iccid');
        $line2Attributes['iccid_from'] = [
            'type' => Form::INPUT_TEXT,
            'label' => sprintf(
                    '%s (%s символов)',
                    $model->getAttributeLabel('iccid_from'),
                    Html::tag('span', '-',['class'=>'label_iccid_length'])
            ),
            'options' => [
                'id' => 'registry_add_iccid_from',
                'class' => 'formReloadOnLostFocus',
                'style'=>'width:150px',
            ],
        ];
        $line2Attributes = $addText($line2Attributes);

        $line2Attributes = $addLabelPrefixParams($line2Attributes, 'iccid_to', 'iccid');
        $line2Attributes['iccid_to'] = [
            'type' => Form::INPUT_TEXT,
            'label' => sprintf(
                '%s (%s символов)',
                $model->getAttributeLabel('iccid_to'),
                Html::tag('span', '-',['class'=>'label_iccid_length'])
            ),
            'options' => [
                'id' => 'registry_add_iccid_to',
                'class' => 'formReloadOnLostFocus',
                'style'=>'width:150px',
            ],
        ];
        $line2Attributes = $addText($line2Attributes);

        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => count($line2Attributes),
            'attributes' => $line2Attributes
        ]);

        // строка 3
        $line3Attributes = [];

        $line3Attributes = $addLabelPrefixParams($line3Attributes, 'imsi_from', 'imsi');
        $line3Attributes['imsi_from'] = [
            'type' => Form::INPUT_TEXT,
            'label' => sprintf(
                '%s (%s символов)',
                $model->getAttributeLabel('imsi_from'),
                Html::tag('span', '-',['class'=>'label_imsi_length'])
            ),
            'options' => [
                'id' => 'registry_add_imsi_from',
                'class' => 'formReloadOnLostFocus',
                'style'=>'width:150px',
            ],
        ];
        $line3Attributes = $addText($line3Attributes);

        $line3Attributes = $addLabelPrefixParams($line3Attributes, 'imsi_to', 'imsi');
        $line3Attributes['imsi_to'] = [
            'type' => Form::INPUT_TEXT,
            'label' => sprintf(
                '%s (%s символов)',
                $model->getAttributeLabel('imsi_to'),
                Html::tag('span', '-',['class'=>'label_imsi_length'])
            ),
            'options' => [
                'id' => 'registry_add_imsi_to',
                'class' => 'formReloadOnLostFocus',
                'style'=>'width:150px',
            ],
        ];
        $line3Attributes = $addText($line3Attributes);

        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => count($line3Attributes),
            'attributes' => $line3Attributes
        ]);

        // строка 4
        $line4Attributes = [];

        $line4Attributes = $addText($line4Attributes);
        $line4Attributes['imsi_s1_from'] = [
            'type' => Form::INPUT_TEXT,
            'options' => [
                'class' => 'formReloadOnLostFocus',
                'maxlength'=>15,
                'style'=>'width:300px',
            ],
        ];
        $line4Attributes = $addText($line4Attributes);

        $line4Attributes = $addText($line4Attributes);
        $line4Attributes['imsi_s1_to'] = [
            'type' => Form::INPUT_TEXT,
            'options' => [
                'class' => 'formReloadOnLostFocus',
                'maxlength'=>15,
                'style'=>'width:300px',
            ],
        ];
        $line4Attributes = $addText($line4Attributes);

        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => count($line4Attributes),
            'attributes' => $line4Attributes
        ]);

        // строка 5
        $line5Attributes = [];

        $line5Attributes = $addText($line5Attributes);
        $line5Attributes['imsi_s2_from'] = [
            'type' => Form::INPUT_TEXT,
            'options' => [
                'class' => 'formReloadOnLostFocus',
                'maxlength'=>15,
                'style'=>'width:300px',
            ],
        ];
        $line5Attributes = $addText($line5Attributes);

        $line5Attributes = $addText($line5Attributes);
        $line5Attributes['imsi_s2_to'] = [
            'type' => Form::INPUT_TEXT,
            'options' => [
                'class' => 'formReloadOnLostFocus',
                'maxlength'=>15,
                'style'=>'width:300px',
            ],
        ];
        $line5Attributes = $addText($line5Attributes);

        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => count($line5Attributes),
            'attributes' => $line5Attributes
        ]);

        // строка 6
        $line6Attributes = [];

        $line6Attributes = $addText($line6Attributes);
        $line6Attributes = $addText($line6Attributes);

        $line6Attributes['sim_type_id'] = [
            'type' => Form::INPUT_DROPDOWN_LIST,
            'items' => \app\modules\sim\models\CardType::getList($isWithEmpty = true),
            'options' => [
                'class' => 'select2',
                'maxlength'=>15,
                'style'=>'width:300px',
            ],
        ];

        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => count($line6Attributes),
            'attributes' => $line6Attributes
        ]);
    ?>

        <div style="height: 30px;">
    <?php
        echo Html::submitButton('Создать заливку', [
            'class' => 'btn btn-success',
            'style' => 'white-space:nowrap; float:right;',
            'name' => 'save',
            'value' => 'Создать заливку'
        ]);
    ?>
        </div>
    <?php
        ActiveForm::end();
        ?>
    </div>
</div>
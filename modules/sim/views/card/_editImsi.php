<?php
/**
 * SIM-карты. Карточка IMSI
 *
 * @var \app\classes\BaseView $this
 * @var Card $card
 * @var \yii\widgets\ActiveForm $form
 */

use app\modules\sim\models\Card;
use app\modules\sim\models\Imsi;
use app\modules\sim\models\ImsiStatus;
use kartik\editable\Editable;
use unclead\widgets\TabularColumn;
use unclead\widgets\TabularInput;

$imsi = new Imsi();
$imsi->is_active = true;
$imsi->status_id = ImsiStatus::ID_DEFAULT;
$attributeLabels = $imsi->attributeLabels();

$imsies = $card->imsies;
if (!$imsies) {
    // нет моделей, но виджет для рендеринга их обязательно требует
    // поэтому рендерим дефолтную модель и сразу ж ее удаляем
    $imsies = [$imsi];
    $this->registerJsVariable('isRemovePackageMinutes', true);
}

if (!$card->isNewRecord) {
    // это нужно сделать ДО TabularInput, иначе он попортит данные $imsies
    $showHistory = $this->render('//layouts/_showHistory', [
        'parentModel' => [new Card(), $card->iccid],
    ]);
} else {
    $showHistory = '';
}

?>

<div class="well chargePeriod">
    <?php

    echo TabularInput::widget([
            'models' => array_values($imsies), // ключ должен быть автоинкрементный
            'allowEmptyList' => false,
            'columns' => [
                [
                    'name' => 'imsi',
                    'title' => $attributeLabels['imsi'],
                ],
                [
                    'name' => 'msisdn',
                    'title' => $attributeLabels['msisdn'],
                ],
                [
                    'name' => 'did',
                    'title' => $attributeLabels['did'],
                ],
                [
                    'name' => 'actual_from',
                    'title' => $attributeLabels['actual_from'],
                    'type' => Editable::INPUT_DATE,
                    'options' => [
                        'removeButton' => false,
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                            'todayHighlight' => true,
                        ],
                    ],
                ],
                [
                    'name' => 'status_id',
                    'title' => $attributeLabels['status_id'],
                    'type' => Editable::INPUT_SELECT2,
                    'options' => [
                        'data' => ImsiStatus::getList(),
                    ],
                ],
                [
                    'name' => 'is_anti_cli',
                    'title' => $attributeLabels['is_anti_cli'],
                    'type' => Editable::INPUT_CHECKBOX,
                ],
                [
                    'name' => 'is_roaming',
                    'title' => $attributeLabels['is_roaming'],
                    'type' => Editable::INPUT_CHECKBOX,
                ],
                [
                    'name' => 'is_active',
                    'title' => $attributeLabels['is_active'],
                    'type' => Editable::INPUT_CHECKBOX,
                ],
                [
                    'name' => 'imsi', // чтобы идентифицировать модель
                    'type' => TabularColumn::TYPE_HIDDEN_INPUT,
                ],
            ],
        ]
    );
    ?>

    <?= $showHistory ?>

</div>
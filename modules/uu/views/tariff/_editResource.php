<?php
/**
 * свойства тарифа (ресурсы)
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\classes\Html;
use app\modules\uu\controllers\TariffController;
use app\modules\uu\models\Resource;
use app\modules\uu\models\TariffResource;

$tariffResourceTableName = TariffResource::tableName();
$resourceTableName = Resource::tableName();

$tariffResources = $formModel->tariffResources;

if ($editableType <= TariffController::EDITABLE_LIGHT) {
    $options = ['disabled' => 'disabled'];
} else {
    $options = [];
}
?>

<?php if ($tariffResources) { ?>
    <div class="well tariffResources">
        <div class="row">
            <div class="col-sm-3"><label><?= Html::encode(Yii::t('models/' . $tariffResourceTableName, 'resource_id')) ?></label></div>
            <div class="col-sm-3"><label>История изменений</label></div>
            <div class="col-sm-1"><label>Диапазон значений</label></div>
            <div class="col-sm-1"><label><?= Html::encode(Yii::t('models/' . $tariffResourceTableName, 'amount')) ?></label></div>
            <div class="col-sm-1"><label><?= Html::encode(Yii::t('models/' . $resourceTableName, 'unit')) ?></label></div>
            <div class="col-sm-1"><label><?= Html::encode(Yii::t('models/' . $tariffResourceTableName, 'price_per_unit')) ?></label></div>
            <div class="col-sm-1"><label><?= Html::encode(Yii::t('models/' . $tariffResourceTableName, 'price_min')) ?></label></div>
        </div>

        <?php
        /** @var TariffResource $tariffResource */
        foreach ($tariffResources as $i => $tariffResource) {
            $tariffResource->id = $tariffResource->isNewRecord ? $i : $tariffResource->id;
            $resource = $tariffResource->resource;
            $isNumber = $resource->isNumber();
            ?>
            <div class="row">

                <div class="col-sm-3">
                    <label for="resourcetariff-<?= $i ?>-amount"><?= Html::encode($resource->name) ?></label>
                </div>

                <div class="col-sm-3">
                    <?= $tariffResource->isNewRecord ? '' : $this->render('//layouts/_showHistory', ['model' => $tariffResource]) ?>
                </div>

                <div class="col-sm-1">
                    <?= $resource->getValueRange() ?>
                </div>

                <?php // имена инпутов сделаны для совместимости с multiple-input ?>
                <div class="col-sm-1">
                    <?= Html::activeHiddenInput($tariffResource, "[{$i}]id") ?>
                    <?= Html::activeHiddenInput($tariffResource, "[{$i}]resource_id") ?>
                    <?php
                    if ($isNumber) {
                        echo $form->field($tariffResource, "[{$i}]amount")->textInput($options)->label(false);
                    } else {
                        echo $form->field($tariffResource, "[{$i}]amount")->checkbox($options, false)->label(false);
                    }
                    ?>
                </div>

                <div class="col-sm-1">
                    <?= Html::encode($resource->getUnit()) ?>
                </div>

                <div class="col-sm-1">
                    <?php
                    $params = [];
                    if (in_array($tariffResource->resource_id, [Resource::ID_VOIP_CALLS, Resource::ID_TRUNK_CALLS])) {
                        $tariffResource->price_per_unit = 1; // стоимость звонков 1-в-1 из низкоуровневого биллинга
                        $params['readonly'] = 'readonly';
                    }
                    ?>
                    <?= $form->field($tariffResource, "[{$i}]price_per_unit")->textInput($options + $params)->label(false) ?>
                </div>

                <div class="col-sm-1">
                    <?= $form->field($tariffResource, "[{$i}]price_min")->textInput($options)->label(false) ?>
                </div>

            </div>
            <?php
        }
        ?>
    </div>

<?php } ?>

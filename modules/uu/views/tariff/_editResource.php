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
use app\modules\uu\models\AccountLogResource;
use app\modules\uu\models\ResourceModel;
use app\modules\uu\models\TariffResource;

$tariffResourceTableName = TariffResource::tableName();
$resourceTableName = ResourceModel::tableName();

$tariffResources = $formModel->tariffResources;

if ($editableType <= TariffController::EDITABLE_LIGHT) {
    $options = ['disabled' => 'disabled'];
} else {
    $options = [];
}
?>

<?php if ($tariffResources) { ?>
    <div class="well tariffResources">
        <h2>Ресурсы тарифа <?= $helpConfluence = $this->render('//layouts/_helpConfluence', AccountLogResource::getHelpConfluence()) ?></h2>
        <div class="row">
            <div class="col-sm-2 col-sm-offset-3">
                <label>
                    История изменений
                    <?= $helpConfluence ?>
                </label>
            </div>
            <div class="col-sm-1">
                <label>
                    Диапазон значений
                    <?= $helpConfluence ?>
                </label>
            </div>
            <div class="col-sm-1">
                <label>
                    <?= Html::encode(Yii::t('models/' . $tariffResourceTableName, 'amount')) ?>
                    <?= $helpConfluence ?>
                </label>
            </div>
            <div class="col-sm-1">
                <label>
                    <?= Html::encode(Yii::t('models/' . $resourceTableName, 'unit')) ?>
                    <?= $helpConfluence ?>
                </label>
            </div>
            <div class="col-sm-1">
                <label>
                    <?= Html::encode(Yii::t('models/' . $tariffResourceTableName, 'price_per_unit')) ?>
                    <?= $helpConfluence ?>
                </label>
            </div>
            <div class="col-sm-1">
                <label>
                    <?= Html::encode(Yii::t('models/' . $tariffResourceTableName, 'price_min')) ?>
                    <?= $helpConfluence ?>
                </label>
            </div>
            <div class="col-sm-1">
                <label>
                    <?= Html::encode(Yii::t('models/' . $tariffResourceTableName, 'is_can_manage')) ?>
                    <?= $helpConfluence ?>
                </label>
            </div>
            <div class="col-sm-1">
                <label>
                    <?= Html::encode(Yii::t('models/' . $tariffResourceTableName, 'is_show_resource')) ?>
                    <?= $helpConfluence ?>
                </label>
            </div>
        </div>

        <?php
        $helpConfluence = $this->render('//layouts/_helpConfluence', $formModel->tariff->serviceType->getHelpConfluence());

        /** @var TariffResource $tariffResource */
        foreach ($tariffResources as $i => $tariffResource) {
            $tariffResource->id = $tariffResource->isNewRecord ? $i : $tariffResource->id;
            $resource = $tariffResource->resource;
            $isNumber = $resource->isNumber();
            ?>
            <div class="row">

                <div class="col-sm-3">
                    <label for="resourcetariff-<?= $i ?>-amount"><?= Html::encode($resource->name) ?> <?= $helpConfluence ?></label>
                </div>

                <div class="col-sm-2">
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
                        echo $form->field($tariffResource, "[{$i}]amount")->textInput($options + ['type' => 'number'])->label(false);
                    } else {
                        echo $form->field($tariffResource, "[{$i}]amount")->checkbox(/*$options*/[], false)->label(false);
                    }
                    ?>
                </div>

                <div class="col-sm-1">
                    <?= Html::encode($resource->getUnit()) ?>
                </div>

                <div class="col-sm-1">
                    <?php
                    $params = [];
                    if (array_key_exists($tariffResource->resource_id, ResourceModel::$calls)) {
                        $tariffResource->price_per_unit = 1; // стоимость звонков 1-в-1 из низкоуровневого биллинга
                        $params['readonly'] = 'readonly';
                    }
                    ?>
                    <?= $form->field($tariffResource, "[{$i}]price_per_unit")->textInput($options + $params)->label(false) ?>
                </div>

                <div class="col-sm-1">
                    <?= $form->field($tariffResource, "[{$i}]price_min")->textInput($options)->label(false) ?>
                </div>

                <div class="col-sm-1">
                    <?= $form->field($tariffResource, "[{$i}]is_can_manage")->checkbox([]/*$options*/, false)->label(false) ?>
                </div>

                <div class="col-sm-1">
                    <?= $form->field($tariffResource, "[{$i}]is_show_resource")->checkbox([]/*$options*/, false)->label(false) ?>
                </div>

            </div>
            <?php
        }
        ?>
    </div>

<?php } ?>

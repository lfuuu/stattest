<?php
/**
 * свойства тарифа (ресурсы)
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\classes\Html;
use app\classes\uu\model\Resource;
use app\classes\uu\model\TariffResource;
use app\controllers\uu\TariffController;

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
            <div class="col-sm-2"><label><?= Html::encode(Yii::t('models/' . $tariffResourceTableName, 'resource_id')) ?></label></div>
            <div class="col-sm-1"><label><?= Html::encode(Yii::t('models/' . $resourceTableName, 'unit')) ?></label></div>
            <div class="col-sm-1"><label><?= Html::encode(Yii::t('models/' . $resourceTableName, 'min_value')) ?></label></div>
            <div class="col-sm-1"><label><?= Html::encode(Yii::t('models/' . $resourceTableName, 'max_value')) ?></label></div>
            <div class="col-sm-2"><label><?= Html::encode(Yii::t('models/' . $tariffResourceTableName, 'amount')) ?></label></div>
            <div class="col-sm-2"><label><?= Html::encode(Yii::t('models/' . $tariffResourceTableName, 'price_per_unit')) ?></label></div>
            <div class="col-sm-2"><label><?= Html::encode(Yii::t('models/' . $tariffResourceTableName, 'price_min')) ?></label></div>
        </div>

        <?php
        foreach ($tariffResources as $i => $tariffResource) {
            $tariffResource->id = $tariffResource->isNewRecord ? $i : $tariffResource->id;
            $resource = $tariffResource->resource;
            $isNumber = $resource->isNumber();
            ?>
            <div class="row">

                <div class="col-sm-2">
                    <label for="resourcetariff-<?= $i ?>-amount"><?= Html::encode($resource->name) ?></label>
                    <?php if (!$tariffResource->isNewRecord) : ?>
                        <?= $this->render('//layouts/_showHistory', ['model' => $tariffResource]) ?>
                    <?php endif; ?>
                </div>

                <div class="col-sm-1">
                    <?= $isNumber ? Html::encode($resource->unit) : '' ?>
                </div>

                <div class="col-sm-1">
                    <?= $isNumber ? $resource->min_value : '' ?>
                </div>

                <div class="col-sm-1">
                    <?= $isNumber ?
                        ($resource->max_value ?: '∞') :
                        '' ?>
                </div>

                <?php // имена инпутов сделаны для совместимости с multiple-input ?>
                <div class="col-sm-2">
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

                <div class="col-sm-2">
                    <?php
                    $params = [];
                    if (in_array($tariffResource->resource_id, [Resource::ID_VOIP_CALLS, Resource::ID_TRUNK_CALLS])) {
                        $tariffResource->price_per_unit = 1; // стоимость звонков 1-в-1 из низкоуровневого биллинга
                        $params['readonly'] = 'readonly';
                    }
                    ?>
                    <?= $form->field($tariffResource, "[{$i}]price_per_unit")->textInput($options + $params)->label(false) ?>
                </div>

                <div class="col-sm-2">
                    <?= $form->field($tariffResource, "[{$i}]price_min")->textInput($options)->label(false) ?>
                </div>

            </div>
            <?php
        }
        ?>
    </div>

    <?php // если ресурс может быть выключен/включен, то при его включении цену указывать нет смысла, потому что она входит в абонентку ?>
    <script type='text/javascript'>
        $(function () {
            $('.tariffResources input[type=checkbox]')
                .on('change', function () {
                    var $checkbox = $(this);
                    var $priceDiv = $checkbox.parent().parent().next();
                    var $priceInput = $priceDiv.find('input');
                    var $minPriceInput = $priceDiv.next().find('input');
                    if ($checkbox.is(':checked')) {
                        $priceInput.attr('readonly', 'readonly').val(0);
                        $minPriceInput.attr('readonly', 'readonly').val(0);
                    } else {
                        $priceInput.removeAttr('readonly');
                        $minPriceInput.removeAttr('readonly');
                    }
                })
                .trigger('change');
        });
    </script>

<?php } ?>

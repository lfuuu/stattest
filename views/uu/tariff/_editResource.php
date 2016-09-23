<?php
/**
 * свойства тарифа (ресурсы)
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 */

use app\classes\Html;
use app\classes\uu\model\Resource;
use app\classes\uu\model\TariffResource;

$tariffResourceTableName = TariffResource::tableName();
$resourceTableName = Resource::tableName();

$tariff = $formModel->tariff;
$tariffResources = $formModel->tariffResources;
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
            if ($resource->service_type_id != $tariff->service_type_id) {
                continue;
            }
            $isNumber = $resource->isNumber();
            ?>
            <div class="row">

                <div class="col-sm-2">
                    <label for="resourcetariff-<?= $i ?>-amount"><?= Html::encode($resource->name) ?></label>
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
                        echo $form->field($tariffResource, "[{$i}]amount")->textInput()->label(false);
                    } else {
                        echo $form->field($tariffResource, "[{$i}]amount")->checkbox([], false)->label(false);
                    }
                    ?>
                </div>

                <div class="col-sm-2">
                    <?= $form->field($tariffResource, "[{$i}]price_per_unit")->textInput()->label(false) ?>
                </div>

                <div class="col-sm-2">
                    <?= $form->field($tariffResource, "[{$i}]price_min")->textInput()->label(false) ?>
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

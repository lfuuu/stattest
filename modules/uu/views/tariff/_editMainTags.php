<?php
/**
 * Организации тарифа
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\models\Organization;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffOrganization;
use kartik\select2\Select2;

$tariffTags = $formModel->tariffTags;
$tariff = $formModel->tariff;

?>

<div class="row">
    <div class="col-sm-12">

        <label><?= (new \app\modules\uu\models\Tag)->getAttributeLabel('tags') ?></label>
        <?= Select2::widget([
            'name' => 'TariffTags[]',
            'value' => array_keys($tariffTags),
            'data' => \app\modules\uu\models\Tag::getList(),
            'options' => [
                'multiple' => true,
            ],
        ]) ?>

        <?php if (!$tariff->isNewRecord) : ?>
            <?= $this->render('//layouts/_showHistory', [
                'parentModel' => [new TariffOrganization(), $tariff->id],
            ]) ?>
        <?php endif; ?>

    </div>
</div>

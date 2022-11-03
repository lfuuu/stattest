<?php
/**
 * Организации тарифа
 *
 * @var BaseView $this
 * @var TariffForm $formModel
 * @var ActiveForm $form
 * @var int $editableType
 */

use app\classes\BaseView;
use app\modules\uu\forms\TariffForm;
use app\modules\uu\models\Tag;
use app\modules\uu\models\TariffTags;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

$tariffTags = $formModel->tariffTags;
$tariff = $formModel->tariff;

?>

<div class="row">
    <div class="col-sm-12">

        <label><?= (new Tag)->getAttributeLabel('tags') ?></label>
        <?= Select2::widget([
            'name' => 'TariffTags[]',
            'value' => array_keys($tariffTags),
            'data' => Tag::getList(),
            'options' => [
                'multiple' => true,
            ],
        ]) ?>

        <?php if (!$tariff->isNewRecord) : ?>
            <?= $this->render('//layouts/_showHistory', [
                'parentModel' => [new TariffTags(), $tariff->id],
            ]) ?>
        <?php endif; ?>

    </div>
</div>

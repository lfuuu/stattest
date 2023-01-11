<?php

use app\classes\Html;
use app\helpers\DateTimeZoneHelper;
use app\modules\sim\forms\registry\Form;
use yii\widgets\Breadcrumbs;

/**
 * @var \app\classes\BaseView $this
 * @var Form $form
 */

$this->title = 'Просмотр заливки SIM-карт';
echo Html::formLabel($this->title);

$model = $form->registry;
?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'SIM-карты', 'url' => '/sim/'],
        ['label' => 'Реестр SIM-карт', 'url' => $cancelUrl = '/sim/registry/'],
        $this->title
    ],
]) ?>

<table class="table table-hover">
    <thead>
    <tr class="info">
        <th>Поле</th>
        <th>Значение</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td><label><?= $model->getAttributeLabel('id'); ?></label></td>
        <td><?= $model->id ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('region'); ?></label></td>
        <td><?= $model->regionSettings->getRegionFullName() ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('state'); ?></label></td>
        <td>
            <label><?= $model->stateName ?></label>
        </td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('count'); ?></label></td>
        <td><?= $model->count ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('sim_type_id'); ?></label></td>
        <td><?= $model->type->name ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('iccid_from'); ?></label></td>
        <td><?= $model->iccid_from ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('iccid_to'); ?></label></td>
        <td><?= $model->iccid_to ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('imsi_from'); ?></label></td>
        <td><?= $model->imsi_from ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('imsi_to'); ?></label></td>
        <td><?= $model->imsi_to ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('imsi_s1_from'); ?></label></td>
        <td><?= $model->imsi_s1_from ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('imsi_s1_to'); ?></label></td>
        <td><?= $model->imsi_s1_to ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('imsi_s2_from'); ?></label></td>
        <td><?= $model->imsi_s2_from ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('imsi_s2_to'); ?></label></td>
        <td><?= $model->imsi_s2_to ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('created_at'); ?></label></td>
        <td><?= DateTimeZoneHelper::getDateTime($model->created_at) ?><?= ' (' . $model->createdBy->name . ')' ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('updated_at'); ?></label></td>
        <td><?= DateTimeZoneHelper::getDateTime($model->updated_at) ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('started_at'); ?></label></td>
        <td><?= DateTimeZoneHelper::getDateTime($model->started_at) ? : '---' ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('completed_at'); ?></label></td>
        <td><?= DateTimeZoneHelper::getDateTime($model->completed_at) ? : '---' ?></td>
    </tr>
    <?php if ($log = $model->log) : ?>
        <?php
            $items = array_reverse(array_filter(explode(PHP_EOL, $log)));
            $items = array_map(function ($item) {
                return wordwrap($item, 80, ' ', true);
            }, $items);
        ?>
        <tr>
            <td><label><?= $model->getAttributeLabel('log'); ?></label></td>
            <td><?= implode('<br /><br />', $items) ?></td>
        </tr>
    <?php endif ?>
    <?php if ($errors = $model->errors) : ?>
        <?php
            $items = array_reverse(array_filter(explode(PHP_EOL, $errors)));
            $items = array_map(function ($item) {
                return wordwrap($item, 80, ' ', true);
            }, $items);
        ?>
        <tr>
            <td><label><?= $model->getAttributeLabel('errors'); ?></label></td>
            <td><?= implode('<br /><br />', $items) ?></td>
        </tr>
    <?php endif ?>
    </tbody>
</table>

<div class="form-group text-right">
    <?= $this->render('//layouts/_buttonLink', [
        'url' => $cancelUrl,
        'text' => 'Назад к реестру SIM-карт',
        'glyphicon' => 'glyphicon-chevron-left',
    ]) ?>
</div>

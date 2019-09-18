<?php

/**
 * @var \app\classes\BaseView $this
 * @var ViewForm $form
 * @var string $indexUrl
 */

use app\classes\Html;
use app\helpers\DateTimeZoneHelper;
use app\modules\sbisTenzor\forms\document\ViewForm;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

$this->title = sprintf("Пакет документов #%s, номер %s от %s (%s)", $model->id, $model->number, $model->date, $model->comment)
?>

<?php

echo Html::formLabel($this->title);
echo Breadcrumbs::widget([
    'links' => [
        'СБИС',
        ['label' => 'Пакеты документов в СБИС', 'url' => $indexUrl],
        $this->title
    ],
]) ?>

<?php if ($form) : ?>
<div class="form-group text-right">

    <?php
        $model = $form->getDocument();

        $out = '';
        if ($form->getShowCancelButton()) {
            $out .= $this->render('//layouts/_button', [
                'text' => 'Отменить',
                'glyphicon' => 'glyphicon-remove',
                'params' => [
                    'class' => 'btn btn-warning',
                    'onClick' => sprintf('if (confirm("%s")) { window.location.href = "%s"; };', 'Отменить данный пакет?', $form->getCancelUrl()),
                ],
            ]);
        }

        if ($form->getShowRestoreButton()) {
            $out .= $this->render('//layouts/_button', [
                'text' => 'Восстановить',
                'glyphicon' => 'glyphicon-ok',
                'params' => [
                    'class' => 'btn btn-info',
                    'onClick' => sprintf('if (confirm("%s")) { window.location.href = "%s"; };', 'Восстановить данный пакет?', $form->getRestoreUrl()),
                ],
            ]);
        }

        if ($form->getShowSendButton()) {
            $out .= ($out ? '&nbsp;&nbsp;&nbsp;' : '') . $this->render('//layouts/_button', [
                'text' => 'Отправить',
                'glyphicon' => 'glyphicon-send',
                'params' => [
                    'class' => 'btn btn-success',
                    'onClick' => sprintf('window.location.href = "%s";', $form->getStartUrl()),
                ],
            ]);
        }

        echo $out;
    ?>
</div>

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
        <td><label>Отправитель</label></td>
        <td><?= $model->sbisOrganization->organization->name ?></td>
    </tr>
    <tr>
        <td><label>Направление</label></td>
        <td><?= sprintf(
                "%s >>> %s",
                $model->sbisOrganization->organization->name,
                $model->clientAccount->contragent->name
            ) ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('state'); ?></label></td>
        <td>
            <label><?= $model->stateName ?></label>
        </td>
    </tr>
    <tr>
        <td colspan="2" class="text-center">
            <?php
            $out = '';
            foreach ($model->getStatusesChain() as $chain) {
                $itemText = $chain['name'];
                $addedClass = $chain['passed'] ? 'btn-info' : 'btn-default';
                $itemText = '<label class="btn btn-xs ' . $addedClass . '">' . $itemText . '</label>';
                $out .= ($out ? '&nbsp;<i class="glyphicon glyphicon-menu-right"></i>&nbsp;' : '') . $itemText;
            }
            echo $out;
            ?>
        </td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('number'); ?></label></td>
        <td><?= $model->number ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('date'); ?></label></td>
        <td><?= $model->date ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('comment'); ?></label></td>
        <td><?= $model->comment ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('external_state_name'); ?></label></td>
        <td><?= $model->external_state_name ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('external_state_description'); ?></label></td>
        <td><?= $model->external_state_description ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('external_id'); ?></label></td>
        <td><?= $model->external_id ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('url_external'); ?></label></td>
        <td><?= $model->url_external ? Html::a('Скачать', $model->url_external, ['target' => '_blank']) : '' ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('url_our'); ?></label></td>
        <td><?= $model->url_our ? Html::a('Скачать', $model->url_our, ['target' => '_blank']) : '' ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('url_archive'); ?></label></td>
        <td><?= $model->url_archive ? Html::a('Скачать', $model->url_archive, ['target' => '_blank']) : '' ?></td>
    </tr>
    <tr>
        <td><label>Вложения</label></td>
        <td>
            <?php foreach ($model->attachments as $attachment): ?>
                <?=$attachment->number ?>. <?=$attachment->file_name ?>
                &nbsp;&nbsp;&nbsp;
                <a href="<?= Url::toRoute([
                    '/sbisTenzor/document/download-attachment',
                    'id' => $attachment->id,
                ]) ?>" class="btn btn-default">
                    <i class="glyphicon glyphicon-download"></i>
                    Скачать файл
                </a>
                &nbsp;&nbsp;&nbsp;
                <?=
                    $attachment->is_signed == $attachment->is_sign_needed ?
                        '<span class="text-success"><i class="glyphicon glyphicon-ok"></i>&nbsp;Подписан</span>' :
                        '<span class="text-warning"><i class="glyphicon glyphicon-question-sign"></i>&nbsp;Не подписан</span>'
                ?>
                <br /><br />
            <?php endforeach; ?>
        </td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('type'); ?></label></td>
        <td><?= $model->typeName ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('created_at'); ?></label></td>
        <td><?= DateTimeZoneHelper::getDateTime($model->created_at) ?><?= ' (' . $model->createdBy->name . ')' ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('updated_at'); ?></label></td>
        <td><?= DateTimeZoneHelper::getDateTime($model->updated_at) ?><?= ($model->updatedBy ? (' (' . $model->updatedBy->name . ')') : '') ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('started_at'); ?></label></td>
        <td><?= DateTimeZoneHelper::getDateTime($model->started_at) ? : '---' ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('saved_at'); ?></label></td>
        <td><?= DateTimeZoneHelper::getDateTime($model->saved_at) ? : '---' ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('prepared_at'); ?></label></td>
        <td><?= DateTimeZoneHelper::getDateTime($model->prepared_at) ? : '---' ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('signed_at'); ?></label></td>
        <td><?= DateTimeZoneHelper::getDateTime($model->signed_at) ? : '---' ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('sent_at'); ?></label></td>
        <td><?= DateTimeZoneHelper::getDateTime($model->sent_at) ? : '---' ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('last_fetched_at'); ?></label></td>
        <td><?= DateTimeZoneHelper::getDateTime($model->last_fetched_at) ? : '---' ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('read_at'); ?></label></td>
        <td><?= DateTimeZoneHelper::getDateTime($model->read_at) ? : '---' ?></td>
    </tr>
    <tr>
        <td><label><?= $model->getAttributeLabel('completed_at'); ?></label></td>
        <td><?= DateTimeZoneHelper::getDateTime($model->completed_at) ? : '---' ?></td>
    </tr>
    </tbody>
</table>

<?php endif ?>

<div class="form-group text-right">
    <?= $this->render('//layouts/_buttonLink', [
        'url' => $indexUrl,
        'text' => 'Назад к списку пакетов',
        'glyphicon' => 'glyphicon-chevron-left',
    ]) ?>
</div>

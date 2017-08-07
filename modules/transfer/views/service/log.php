<?php

use app\classes\Html;
use app\modules\transfer\components\services\PreProcessor;
use app\modules\transfer\forms\services\BaseForm;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

/** @var BaseForm $form */

echo Html::formLabel('Перенос услуг');
echo Breadcrumbs::widget([
    'links' => [
        'Лицевой счет',
        [
            'label' => $form->clientAccount->contract->contragent->name,
            'url' => Url::toRoute(['/client/view', 'id' => $form->clientAccount->id]),
        ],
        [
            'label' => 'Перенос услуг',
            'url' => '/transfer/service'
        ],
        'Результирующая сводка'
    ],
]);
?>

<table class="kv-grid-table table table-hover table-bordered table-striped">
    <colgroup>
        <col width="20px" />
        <col width="40%" />
        <col width="40%" />
        <col width="150" />

    </colgroup>
    <thead>
        <tr class="info">
            <th></th>
            <th>Исходная услуга</th>
            <th>Целевая услуга</th>
            <th>Дата переноса</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($form->processLog as $record):
            /** @var PreProcessor $object */
            $object = $record['object'];
            ?>

            <?php if ($record['type'] === 'error'): ?>
                <tr>
                    <td colspan="4" class="alert alert-danger">
                        <?= $record['message'] ?>
                    </td>
                </tr>
            <?php else:
                $source = $object->sourceServiceHandler->getServiceDecorator($object->sourceServiceHandler->getService());
                $target = $object->targetServiceHandler->getServiceDecorator($object->targetServiceHandler->getService());
                ?>
                <tr>
                    <td class="text-center">
                        <i class="glyphicon glyphicon-ok text-success"></i>
                    </td>
                    <td>
                        <?= $source->description ?>
                    </td>
                    <td>
                        <?= $target->description ?>
                    </td>
                    <td>
                        <?= $object->activationDate ?>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endforeach; ?>
    </tbody>
</table>
<?php

use app\classes\DateTimeWithUserTimezone;
use app\classes\Html;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use yii\widgets\Breadcrumbs;

/** @var ClientAccount|null $clientAccount */
/** @var array $result */

$clientName =
    !is_null($clientAccount)
        ? ' для клиента ' . $clientAccount->contract->contragent->name
        : '';

echo Html::formLabel('Перемещаемые услуги' . $clientName);

echo Breadcrumbs::widget([
    'links' => [
        'Мониторинг',
        ['label' => 'Перемещаемые услуги' . $clientName, 'url' => '/monitoring/transfered-usages'],
    ],
]);

?>

<div class="text-right">
    <?= $this->render('//layouts/_link', [
        'text' => ($clientName ? 'Показать для всех клиентов' : 'Показать для текущего клиента'),
        'glyphicon' => 'glyphicon-filter',
        'url' => \yii\helpers\Url::toRoute(['/monitoring/transfered-usages', 'isCurrentOnly' => ($clientName ? 0 : 1)]),
        'params' => [
            'class' => 'btn btn-primary',
        ],
    ]) ?>
</div>

<?php
$isUsagesExists = 0;
foreach ($result as $usageTitle => $records) {
    if (!count($records)) {
        continue;
    }
    $isUsagesExists = 1;
    ?>
    <label><?= $usageTitle; ?></label>
    <table class="table table-bordered table-striped table-condensed table-hover">
        <colgroup>
            <col width="15%" />
            <col width="150" />
            <col width="20%" />
            <col width="250" />
            <col width="250" />
            <col width="150" />
            <col width="20%" />
            <col width="250" />
            <col width="250" />
        </colgroup>
        <tr>
            <th rowspan="2" style="text-align: center; vertical-align: middle;">Услуга</th>
            <th colspan="4" style="border-right: 2px solid #A5A5A5;">Перемещена от</th>
            <th colspan="4">Перемещена к</th>
        </tr>
        <tr>
            <th>ID услуги</th>
            <th>Клиент</th>
            <th>Работает с</th>
            <th style="border-right: 2px solid #A5A5A5;">Работает до</th>
            <th>ID услуги</th>
            <th>Клиент</th>
            <th>Работает с</th>
            <th>Работает до</th>
        </tr>
        <?php
        foreach ($records as $usage):
            list($description) = $usage->helper->description;
            ?>
            <tr>
                <td><?= $description; ?></td>
                <td><?= Html::a($usage->helper->transferedFrom->id, $usage->helper->transferedFrom->helper->editLink, ['target' => '_blank']); ?></td>
                <td><?= Html::a($usage->helper->transferedFrom->clientAccount->contragent->name, ['/client/view', 'id' => $usage->helper->transferedFrom->clientAccount->id], ['target' => '_blank']); ?></td>
                <td><?= (new DateTimeWithUserTimezone($usage->helper->transferedFrom->actual_from))->formatWithInfinity(DateTimeZoneHelper::DATE_FORMAT); ?></td>
                <td style="border-right: 2px solid #A5A5A5;"><?= (new DateTimeWithUserTimezone($usage->helper->transferedFrom->actual_to))->formatWithInfinity(DateTimeZoneHelper::DATE_FORMAT); ?></td>
                <td><?= Html::a($usage->id, $usage->helper->editLink, ['target' => '_blank']); ?></td>
                <td><?= Html::a($usage->clientAccount->contragent->name, ['/client/view', 'id' => $usage->clientAccount->id], ['target' => '_blank']); ?></td>
                <td><?= (new DateTimeWithUserTimezone($usage->actual_from))->formatWithInfinity(DateTimeZoneHelper::DATE_FORMAT); ?></td>
                <td><?= (new DateTimeWithUserTimezone($usage->actual_to))->formatWithInfinity(DateTimeZoneHelper::DATE_FORMAT); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php
}
?>

<?php if (!$isUsagesExists): ?>
    <div class="row text-center">
        <div class="label label-info" style="padding: 10px;">
            Перемещаемых услуг не найдено
        </div>
    </div>
<?php endif; ?>

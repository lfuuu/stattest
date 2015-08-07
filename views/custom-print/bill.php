<?php
use app\classes\Utils;
use app\models\TroubleStage;

$organization = $document->organization;
?>

ID <?= $document->bill->clientAccount->id; ?><br />
<?= $document->bill->clientAccount->contragent->name; ?><br />
Юридический адрес: <?= $document->bill->clientAccount->contragent->address_jur; ?><br /><br />

Комментарии:<br />
<?php foreach ($document->bill->clientAccount->contract->comments as $comment): ?>
    <?php if ($comment->is_publish): ?>
        <b><?= $comment->user ?> <?= $comment->ts ?>:</b>
        <?= $comment->comment ?>
        <br />
    <?php endif; ?>
<?php endforeach; ?>

<br />Телефон:
<?php foreach ($document->bill->clientAccount->allContacts as $contact):
    if ($contact->type != 'phone' || !$contact->is_active || !$contact->data):
        continue;
    endif;
    echo $contact->data . ($contact->comment ? '&nbsp-&nbsp'. $contact->comment : '') . '; &nbsp;';
endforeach;
?>

<br /><br /><?= $document->bill->bill_no; ?><br />
<?= ($document->bill->extendsInfo->fio ?: $document->bill->clientAccount->contragent->name); ?><br /><br />

<table border="0" class="table table-condensed table-hover table-striped">
    <tr class=even style="font-weight: bold;">
        <th>&#8470;</th>
        <th width="1%">Артикул</th>
        <th>Наименование</th>
        <th>Количество</th>
        <th style="text-align: right">Цена (<?= ($document->bill->price_include_vat > 0 ? 'вкл. НДС' : 'без НДС'); ?>)</th>
        <?php if ($organization->isNotSimpleTaxSystem()): ?>
            <?php if ($document->bill->price_include_vat): ?>
                <th style="text-align: right">Сумма</th>
                <th style="text-align: right">Сумма НДС</th>
            <?php else: ?>
                <th style="text-align: right">Сумма</th>
                <th style="text-align: right">Сумма НДС</th>
                <th style="text-align: right">Сумма с НДС</th>
            <?php endif; ?>
        <?php else: ?>
            <td align="center"><b>Сумма</b></td>
        <?php endif; ?>
    </tr>
    <?php
    foreach ($document->lines as $position => $line): ?>
        <tr>
            <td><?= ($position + 1); ?>.</td>
            <td align="left">
                <span>
                    <?= $line['art']; ?><br />
                    <?php if ($line['type'] == 'good'): ?>
                        <?php if ($line['in_store'] == 'yes'): ?>
                            <b style="color: green;">Склад</b>
                        <?php elseif ($line['in_store'] == 'no'): ?>
                            <b style="color: blue;">Заказ</b>
                        <?php elseif ($line['in_store'] == 'remote'): ?>
                            <b style="color: #c40000;">ДалСклад</b>
                        <?php endif; ?>
                    <?php endif; ?>
                </span>
            </td>
            <td>
                <?= $line['item']; ?>
            </td>
            <td>
                <?= Utils::mround($line['amount'], 4,6); ?>
            </td>
            <td style="text-align: right"><?= Utils::round($line['price'], 4); ?></td>
            <?php if($organization->isNotSimpleTaxSystem()): ?>
                <?php if ($document->bill->price_include_vat == 0): ?>
                    <td style="text-align: right"><?= Utils::round($line['sum_without_tax'], 2); ?></td>
                    <td style="text-align: right"><?= Utils::round($line['sum_tax'], 2); ?> (<?= $line['tax_rate']; ?>%)</td>
                    <td style="text-align: right"><?= Utils::round($line['sum'], 2); ?></td>
                <?php else: ?>
                    <td style="text-align: right"><?= Utils::round($line['sum'], 2); ?></td>
                    <td style="text-align: right"><?= Utils::round($line['sum_tax'], 2); ?> (<?= $line['tax_rate']; ?>%)</td>
                <?php endif; ?>
            <?php else: ?>
                <td align="center">11-<?= Utils::round($line['sum'], 2); ?></td>
            <?php endif; ?>
        </tr>
    <?php endforeach; ?>

    <tr>
        <th colspan="5" style="text-align: right">Итого: </th>
        <?php if($organization->isNotSimpleTaxSystem()): ?>
            <?php if ($document->bill->price_include_vat): ?>
                <th style="text-align: right"><?= Utils::round($document->sum, 2); ?></th>
                <td style="text-align: right">в т.ч. <?= Utils::round($document->sum_with_tax, 2); ?></td>
            <?php else: ?>
                <th style="text-align: right"><?= Utils::round($document->sum_without_tax, 2); ?></th>
                <td style="text-align: right"><?= Utils::round($document->sum_with_tax, 2); ?></td>
                <th style="text-align: right"><?= Utils::round($document->sum, 2); ?></th>
            <?php endif; ?>
        <?php else: ?>
            <td align="center"><?= Utils::round($document->sum, 2); ?></td>
        <?php endif; ?>

    </tr>
</table>

<?php if ($trouble): ?>
    <?= ($trouble ? htmlspecialchars_decode($trouble->problem): ''); ?><br />

    <?php
    $comments =
        TroubleStage::find()
            ->andWhere(['trouble_id' => $trouble->id])
            ->andWhere(['!=', 'comment', ''])
            ->orderBy(['stage_id' => SORT_DESC])
            ->all();
    ?>
    <br />Комментарии:<br />
    <?php foreach ($comments as $comment): ?>
        <b><?= $comment->user_edit; ?>:</b> <?= $comment->comment; ?><br />
    <?php endforeach; ?>
<?php endif; ?>
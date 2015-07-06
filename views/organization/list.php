<?php

use DateTime;
use app\classes\Html;
?>

<h2>
    Организации
</h2>

<div style="text-align: right; padding-bottom: 10px;">
    <?php
    echo Html::a(
        '<i class="glyphicon glyphicon-plus"></i> Добавить',
        ['add'],
        [
            'data-pjax' => 0,
            'class' => 'btn btn-success btn-sm form-lnk',
            'style' => 'margin-right: 15px;',
        ]
    );
    ?>
</div>

<div class="container" style="width: 100%;">
    <div class="row">
        <?php foreach ($organizations as $index => $record): ?>
            <?php
            $break = (($index + 1) % 4 == 0);
            ?>

            <div class="col-md-3">
                <div class="well">
                    <div class="title">
                        <a href="/organization/edit/?id=<?= $record->organization_id; ?>&date=<?= $record->actual_from; ?>"><?= Html::decode($record->name); ?></a>
                    </div>
                    <hr size="1" />
                    <?= $record->legal_address; ?><br />
                    <div class="break"></div>
                    <b>Телефон:</b> <?= $record->contact_phone; ?><br />
                    <b>Факс:</b> <?= $record->contact_fax; ?><br />
                    <div class="break"></div>
                    <b>ИНН:</b> <?= $record->tax_registration_id; ?><br />
                    <b>КПП:</b> <?= $record->tax_registration_reason; ?><br />
                    <b>Банк:</b> <?= $record->bank_name; ?><br />
                    <?php if (!empty($record->bank_swift)): ?>
                        <b>Swift:</b> <?= $record->bank_swift; ?><br />
                    <?php endif; ?>
                    <b>р/с:</b> <span style="word-wrap: break-word;"><?= $record->bank_account; ?></span>
                    <div class="break"></div>
                    <b>Директор:</b> <?= $record->director->name_nominative; ?><br />
                    <b>Бухгалтер:</b> <?= $record->accountant->name_nominative; ?>
                    <div class="date">
                        <?= (new DateTime($record->actual_from))->format('d.m.Y'); ?> г.
                    </div>
                </div>
            </div>
            <?php if($break === true): ?>
                </div>
                <div class="row">
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>

<style type="text/css">
.well {
    background-color: #F8F8F8;
    border: 1px solid #AAAAAA;
    padding: 4px;
    box-shadow: 3px 3px 2px rgba(0,0,0,0.2);
}
    .well .title {
        font-size: 14px;
        font-weight: bold;
        text-align: center;
        padding: 4px;
    }
    .well hr {
        margin-bottom: 5px;
        margin-top: 5px;
        width: 100%;
    }
    .well .break {
        margin-bottom: 10px;
        margin-top: 10px;
    }
    .well .date {
        text-align: right;
        padding-top: 5px;
    }
</style>
<?php

/** @var \app\forms\client\ContractEditForm $contract */
/** @var string $firstAgreementNo */

use app\helpers\DateTimeZoneHelper;
use app\models\ClientDocument;
use kartik\widgets\DatePicker;
?>

<form action="/document/create" method="post">
    <input type="hidden" name="ClientDocument[contract_id]" value="<?= $contract->id ?>" />
    <input type="hidden" name="ClientDocument[type]" value="<?= ClientDocument::DOCUMENT_AGREEMENT_TYPE ?>" />

    <div class="col-sm-2">
        <input
            class="form-control"
            type="text"
            name="ClientDocument[contract_no]"
            value="<?= (int)$firstAgreementNo > 0 ? (int)$firstAgreementNo + 1 : 1 ?>"
        />
    </div>

    <div class="col-sm-2">
        <?= DatePicker::widget(
            [
                'name' => 'ClientDocument[contract_date]',
                'value' => date(DateTimeZoneHelper::DATE_FORMAT),
                'removeButton' => false,
                'options' => ['class' => 'form-control input-sm'],
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                ],
            ]
        ); ?>
    </div>

    <div class="col-sm-2">
        <input class="form-control" type="text" name="ClientDocument[comment]" placeholder="Комментарий" />
    </div>

    <div class="col-sm-2">
        <select
            class="form-control document-template"
            data-documents="<?= ClientDocument::DOCUMENT_AGREEMENT_TYPE ?>"
            data-folder-type="<?= ClientDocument::DOCUMENT_AGREEMENT_TYPE ?>">
        </select>
    </div>

    <div class="col-sm-2">
        <select
            class="form-control tmpl-documents"
            name="ClientDocument[template_id]"
            data-documents-type="<?= ClientDocument::DOCUMENT_AGREEMENT_TYPE ?>">
        </select>
    </div>

    <div class="col-sm-2">
        <?= $this->render('//layouts/_submitButton', [
            'text' => 'Зарегистрировать',
            'glyphicon' => 'glyphicon-save',
            'params' => [
                'class' => 'btn btn-primary col-sm-12',
            ],
        ]) ?>
    </div>
</form>
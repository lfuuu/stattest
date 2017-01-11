<?php

/** @var \app\forms\client\ContractEditForm $contract */

use app\classes\Html;
use app\dao\ClientDocumentDao;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientContract;
use app\models\ClientDocument;
use kartik\widgets\DatePicker;
?>

<?php if ($contract->state == ClientContract::STATE_UNCHECKED) : ?>
    <form action="/document/create" method="post">
        <input type="hidden" name="ClientDocument[contract_id]" value="<?= $contract->id ?>" />
        <input type="hidden" name="ClientDocument[type]" value="<?= ClientDocument::DOCUMENT_CONTRACT_TYPE ?>" />

        <div class="col-sm-1">
            <?= Html::dropDownList(
                'ClientDocument[is_external]',
                ClientContract::IS_INTERNAL,
                ClientContract::$externalType,
                [
                    'id' => 'change-external',
                    'class' => 'form-control',
                ]
            )?>
        </div>

        <div class="col-sm-1">
            <input
                class="form-control unchecked-contract-no"
                type="text"
                name="ClientDocument[contract_no]"
                value="<?= $contract->number ?>"
            />
        </div>

        <div class="col-sm-2">
            <?= DatePicker::widget(
                [
                    'name' => 'ClientDocument[contract_date]',
                    'value' => date(DateTimeZoneHelper::DATE_FORMAT),
                    'removeButton' => false,
                    'options' => ['class' => 'form-control'],
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
            <?php
            $defaultFolder = ClientDocumentDao::getFolderIsDefaultForBusiness($model->business_id);
            ?>

            <?= Html::dropDownList(
                    '',
                    (!is_null($defaultFolder) ? $defaultFolder->id : ''),
                    ClientDocumentDao::getFolders(),
                    [
                        'class' => 'form-control input-sm document-template',
                        'data-documents' => ClientDocument::DOCUMENT_CONTRACT_TYPE,
                        'data-folders' => ClientDocument::DOCUMENT_AGREEMENT_TYPE,
                    ]
                );
            ?>
        </div>

        <div class="col-sm-2">
            <select
                class="form-control input-sm tmpl-documents"
                name="ClientDocument[template_id]"
                data-documents-type="<?= ClientDocument::DOCUMENT_CONTRACT_TYPE ?>">
            </select>
        </div>
        <div class="col-sm-2">
            <?= $this->render('//layouts/_submitButton', [
                'text' => ($hasContract ? 'Обновить' : 'Зарегистрировать'),
                'glyphicon' => 'glyphicon-save',
                'params' => [
                    'class' => 'btn btn-primary col-sm-12',
                ],
            ]) ?>
        </div>
    </form>
<?php endif;
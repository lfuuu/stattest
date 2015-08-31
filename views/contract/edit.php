<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\classes\Language;
use app\models\ClientContract;
use app\models\ClientDocument;

$contragents = \app\models\ClientContragent::find()->andWhere(['super_id' => $model->getModel()->getContragent()->super_id])->all();;
$contragentsOptions = [];

foreach ($contragents as $contragent) {
    $contragentsOptions[ $contragent->id ] = [
        'data-legal-type' => $contragent->legal_type,
    ];
}
$contragents = ArrayHelper::map($contragents, 'id', 'name');

$language = Language::getLanguageByCountryId($contragents[0]['country_id']?:643);
$formFolderName = Language::getLanguageExtension($language);
?>

<div class="row">
    <div class="col-sm-12">
        <h2><?= ($model->isNewRecord) ? 'Создание' : 'Редактирование' ?> договора</h2>

        <?php $f = ActiveForm::begin(); ?>

        <?= $this->render($formFolderName.'/form', ['model' => $model, 'f' => $f, 'contragents' => $contragents, 'contragentsOptions' => $contragentsOptions]); ?>

        <div class="row" style="width: 1100px;">
            <div class="row">
                <div class="col-sm-4">
                    <div class="col-sm-12" type="textInput">
                        <label class="control-label" for="historyVersionStoredDate">Сохранить на</label>
                        <?= Html::dropDownList('ContractEditForm[historyVersionStoredDate]', null, $model->getModel()->getDateList(),
                            ['class' => 'form-control', 'style' => 'margin-bottom: 20px;', 'id' => 'historyVersionStoredDate']); ?>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="col-sm-12" type="textInput">
                        <label class="control-label" for="deferred-date-input">Выберите дату</label>
                        <?= DatePicker::widget(
                            [
                                'name' => 'kartik-date-3',
                                'value' => Yii::$app->request->get('date') ? Yii::$app->request->get('date') : date('Y-m-d', time()),
                                'removeButton' => false,
                                'options' => ['class' => 'form-control input-sm'],
                                'pluginOptions' => [
                                    'autoclose' => true,
                                    'format' => 'yyyy-mm-dd',
                                    'startDate' => '-5y',
                                ],
                                'id' => 'deferred-date-input'
                            ]
                        ); ?>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 form-group">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'id' => 'buttonSave']); ?>
            </div>
            <?php ActiveForm::end(); ?>

            <?php if (!$model->isNewRecord): ?>
                <div class="col-sm-12 form-group">
                    <a href="#" onclick="return showVersion({ClientContract:<?= $model->id ?>}, true);">Версии</a><br/>
                    <?= Html::button('∨', ['style' => 'border-radius: 22px;', 'class' => 'btn btn-default showhistorybutton', 'onclick' => 'showHistory({ClientContract:' . $model->id . '})']); ?>
                    <span>История изменений</span>
                </div>
            <?php endif; ?>
        </div>

        <script>
            $(function () {
                $('#deferred-date-input').parent().parent().hide();
            });

            $('#buttonSave').closest('form').on('submit', function (e) {
                if ($("#historyVersionStoredDate option:selected").val() == '')
                    $('#historyVersionStoredDate option:selected').val($('#deferred-date-input').val()).select();
                return true;
            });

            $('#historyVersionStoredDate').on('change', function () {
                console.log(this);
                var datepicker = $('#deferred-date-input');
                if ($("option:selected", this).val() == '') {
                    console.log('picker show');
                    datepicker.parent().parent().show();
                }
                else {
                    console.log('picker hide');
                    datepicker.parent().parent().hide();
                }
            });
        </script>
    </div>

    <?php $docs = $model->model->allDocuments; ?>

    <div class="col-sm-12">
        <div class="row"
             style="padding: 5px 0; color: white; background: black; font-weight: bold; margin-top: 10px; text-align: center;">
            <div class="col-sm-12">Договор</div>
        </div>
        <div class="row head3" style=" padding: 5px 0;">
            <div class="col-sm-1">Внут/Внеш</div>
            <div class="col-sm-1">№</div>
            <div class="col-sm-2">Дата</div>
            <div class="col-sm-2">Комментарий</div>
            <div class="col-sm-2">Кто добавил</div>
            <div class="col-sm-2">Когда</div>
            <div class="col-sm-2"></div>
        </div>
        <?php $hasContract = false; ?>
        <?php foreach ($docs as $doc) if ($doc->type == 'contract'): ?>
            <?php $hasContract = true; ?>
            <div class="row"
                 style=" border-top: 1px solid black; padding: 5px 0; <?= !$doc->is_active ? 'color:#CCC;' : '' ?>">
                <div class="col-sm-1"><b><?= ClientContract::$externalType[$doc->contract->is_external] ?></b></div>
                <div class="col-sm-1"><?= $doc->contract_no ?></div>
                <div class="col-sm-2"><?= $doc->contract_date ?></div>
                <div class="col-sm-2"><?= $doc->comment ?></div>
                <div class="col-sm-2"><?= $doc->user->name ?></div>
                <div class="col-sm-2"><?= $doc->ts ?></div>
                <div class="col-sm-2">
                    <?php if (!empty($doc->getFileContent())): ?>
                        <?php if ($model->state == ClientContract::STATE_UNCHECKED) : ?>
                            <a href="/document/edit?id=<?= $doc->id ?>" target="_blank">
                                <img class="icon" src="/images/icons/edit.gif">
                            </a>
                        <? endif; ?>
                        <a href="/document/print/?id=<?= $doc->id ?>"
                           target="_blank"><img class="icon" src="/images/icons/printer.gif"></a>
                        <a href="/document/send?id=<?= $doc->id ?>" target="_blank">
                            <img class="icon" src="/images/icons/contract.gif">
                        </a>
                        <?php if ($model->state == ClientContract::STATE_UNCHECKED) : ?>
                            <?php if ($doc->is_active) : ?>
                                <a href="<?= Url::toRoute(['document/activate', 'id' => $doc->id]) ?>">
                                    <img style="margin-left:-2px;margin-top:-3px" class="icon"
                                         src="/images/icons/delete.gif">
                                </a>
                            <?php else : ?>
                                <a href="<?= Url::toRoute(['document/activate', 'id' => $doc->id]) ?>">
                                    <img style="margin-left:-2px;margin-top:-3px" class="icon" src="/images/icons/add.gif">
                                </a>
                            <? endif; ?>
                        <? endif; ?>
                        <a href="/document/print-by-code?code=<?= $doc->link ?>" target="_blank">ссылка</a>
                    <?php else: ?>
                        <b>Не создан</b>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($model->state == ClientContract::STATE_UNCHECKED) : ?>
            <div class="row" style="padding-top: 5px;">
                <form action="/document/create" method="post">
                    <div class="col-sm-1">
                        <input type="hidden" name="ClientDocument[contract_id]" value="<?= $model->id ?>">
                        <input type="hidden" name="ClientDocument[type]" value="contract">
                        <select class="form-control input-sm" id="change-external" name="ClientDocument[is_external]">
                            <option value=<?=ClientContract::IS_INTERNAL?>><?=ClientContract::$externalType[ClientContract::IS_INTERNAL]?></option>
                            <option value=<?=ClientContract::IS_EXTERNAL?>><?=ClientContract::$externalType[ClientContract::IS_EXTERNAL]?></option>
                        </select>
                    </div>
                    <div class="col-sm-1">
                        <input class="form-control input-sm unchecked-contract-no" type="text" name="ClientDocument[contract_no]" value="<?= $model->number ?>" />
                    </div>
                    <div class="col-sm-2">
                        <?= DatePicker::widget(
                            [
                                'name' => 'ClientDocument[contract_date]',
                                'value' => date('Y-m-d'),
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
                        <input class="form-control input-sm" type="text" name="ClientDocument[comment]">
                    </div>

                    <div class="col-sm-2">
                        <select class="form-control input-sm tmpl-group" name="ClientDocument[group]"
                                data-type="contract" data-not-external="1"></select>
                    </div>
                    <div class="col-sm-2">
                        <select class="form-control input-sm tmpl" name="ClientDocument[template]"
                                data-type="contract" data-not-external="1"></select>
                    </div>
                    <div class="col-sm-2">
                        <button type="submit"
                                class="btn btn-primary btn-sm col-sm-12"><?= $hasContract ? 'Обновить' : 'Зарегистрировать' ?></button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>


    <div class="col-sm-12" data-not-external="1">
        <div class="row"
             style="padding:5px 0; color: white; background: black; font-weight: bold; margin-top: 10px; text-align: center;">
            <div class="col-sm-12">Доп. соглашения</div>
        </div>
        <div class="row head3" style=" padding: 5px 0;">
            <div class="col-sm-2">№</div>
            <div class="col-sm-2">Дата</div>
            <div class="col-sm-2">Комментарий</div>
            <div class="col-sm-2">Кто добавил</div>
            <div class="col-sm-2">Когда</div>
            <div class="col-sm-2"></div>
        </div>
        <?php $armnt = 0; ?>
        <?php foreach ($docs as $doc) if ($doc->type == 'agreement'): ?>
            <?php $armnt = $doc->contract_no; ?>
            <div class="row"
                 style="border-top: 1px solid black; padding: 5px 0;<?= !$doc->is_active ? 'color:#CCC;' : '' ?>">
                <div class="col-sm-2"><?= $doc->contract_no ?></div>
                <div class="col-sm-2"><?= $doc->contract_date ?></div>
                <div class="col-sm-2"><?= $doc->comment ?></div>
                <div class="col-sm-2"><?= $doc->user->name ?></div>
                <div class="col-sm-2"><?= $doc->ts ?></div>
                <div class="col-sm-2">
                    <a href="/document/edit?id=<?= $doc->id ?>"
                       target="_blank"><img
                            class="icon" src="/images/icons/edit.gif"></a>
                    <a href="/document/print/?id=<?= $doc->id ?>"
                       target="_blank"><img class="icon" src="/images/icons/printer.gif"></a>
                    <a href="/document/send?id=<?= $doc->id ?>"
                       target="_blank"><img class="icon" src="/images/icons/contract.gif"></a>
                    <?php if ($doc->is_active) : ?>
                        <a href="<?= Url::toRoute(['document/activate', 'id' => $doc->id]) ?>">
                            <img style="margin-left:-2px;margin-top:-3px" class="icon" src="/images/icons/delete.gif">
                        </a>
                    <?php else : ?>
                        <a href="<?= Url::toRoute(['document/activate', 'id' => $doc->id]) ?>">
                            <img style="margin-left:-2px;margin-top:-3px" class="icon" src="/images/icons/add.gif">
                        </a>
                    <? endif; ?>
                    <a href="/document/print-by-code?code=<?= $doc->link ?>" target="_blank">ссылка</a>
                </div>
            </div>
        <?php endif; ?>

        <div class="row" style="padding-top: 5px;">
            <form action="/document/create" method="post">
                <div class="col-sm-2">
                    <input type="hidden" name="ClientDocument[contract_id]" value="<?= $model->id ?>">
                    <input type="hidden" name="ClientDocument[type]" value="agreement">
                    <input class="form-control input-sm" type="text" name="ClientDocument[contract_no]"
                           value="<?= isset($armnt) && $armnt > 0 ? $armnt + 1 : 1 ?>"></div>
                <div class="col-sm-2">
                    <?= DatePicker::widget(
                        [
                            'name' => 'ClientDocument[contract_date]',
                            'value' => date('Y-m-d'),
                            'removeButton' => false,
                            'options' => ['class' => 'form-control input-sm'],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd',
                            ],
                        ]
                    ); ?>
                </div>
                <div class="col-sm-2"><input class="form-control input-sm" type="text" name="ClientDocument[comment]"></div>
                <div class="col-sm-2">
                    <select class="form-control input-sm tmpl-group" name="ClientDocument[group]"
                            data-type="agreement"></select>
                </div>
                <div class="col-sm-2">
                    <select class="form-control input-sm tmpl" name="ClientDocument[template]"
                            data-type="agreement"></select>
                </div>
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-primary btn-sm col-sm-12">Зарегистрировать</button>
                </div>
            </form>
        </div>
    </div>


    <?php $files = $model->model->allFiles; ?>

    <div class="col-sm-12"
         style="padding: 5px 0; color: white; background: black; font-weight: bold; margin-top: 10px; text-align: center;">
        Файлы
    </div>
    <div class="col-sm-12">
        <div class="row head3" style="padding: 5px 0;">
            <div class="col-sm-4">Имя файла</div>
            <div class="col-sm-4">Комментарий</div>
            <div class="col-sm-2">Кто</div>
            <div class="col-sm-2">Когда</div>
        </div>
        <?php foreach ($files as $file): ?>
            <div class="row" style="padding: 5px 0; border-top: 1px solid black;">
                <div class="col-sm-4">
                    <a href="/file/get-file?model=clients&id=<?= $file->id ?>" target="_blank">
                        <?= $file->name ?>
                    </a>
                    <a href="#" data-id="<?= $file->id ?>" class="fileSend">
                        <img border=0 src="/images/icons/envelope.gif" />
                    </a>
                </div>
                <div class="col-sm-4">
                    <?= $file->comment ?>
                </div>
                <div class="col-sm-2">
                    <?= $file->user->name ?>
                </div>
                <div class="col-sm-2">
                    <?= $file->ts ?>
                    <a href="#" class="deleteFile" data-id="<?= $file->id ?>">
                        <img style="margin: -3px 0 0 -2px;" class=icon src="/images/icons/delete.gif" alt="Удалить" />
                    </a>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="row" style="padding: 5px 0;">
            <form action="/file/upload-client-file?model=clients&contractId=<?= $model->model->id ?>" method="post"
                  enctype="multipart/form-data">
                <div class="col-sm-4">
                    <input class="form-control input-sm" type=text name="name" placeholder="Название файла">
                </div>
                <div class="col-sm-4">
                    <input class="form-control input-sm" type=text name="comment" placeholder="Комментарий">
                </div>
                <div class="col-sm-2">
                    <div class="file_upload form-control input-sm">Выбрать<input type="file" name="file"/></div>
                </div>
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-primary btn-sm col-sm-12">Загрузить</button>
                </div>
            </form>
        </div>
    </div>


    <div id="dialog-form" title="Отправить файл">
        <div class="col-sm-12">
            <div class="form-group">
                <form method="post" id="send-file-form" target="_blank"
                      action="http://thiamis.mcn.ru/welltime/?module=com_agent_panel&frame=new_msg&nav=mail.none.none&message=none&trunk=5">
                    <label for="client-email">Email</label>
                    <select id="client-email" class="form-control" name="to">
                        <?php foreach ($model->model->accounts[0]->allContacts as $contact)
                            if ($contact->is_active && $contact->type == 'email'):?>
                                <option value="<?= $contact->data ?>"><?= $contact->data ?></option>
                            <?php endif; ?>
                    </select>
                    <input type="hidden" name="file_content" id="file_content">
                    <input type="hidden" name="file_name" id="file_name">
                    <input type="hidden" name="file_mime" id="file_mime">
                    <input type="hidden" name="msg_session" id="msg_session">
                    <input type="hidden" name="send_from_stat" value="1">
                </form>
            </div>
        </div>
    </div>


    <script>
        var dialog;

        $(function () {
            dialog = $("#dialog-form").dialog({
                autoOpen: false,
                height: 200,
                width: 400,
                modal: true,
                buttons: {
                    "Отправить": function () {
                        $('#send-file-form').submit();
                        dialog.dialog("close");
                    },
                    "Отмена": function () {
                        dialog.dialog("close");
                    }
                }
            });
        });

        $('.fileSend').on('click', function (e) {
            e.preventDefault();
            $.getJSON('/file/send-client-file', {id: $(this).data('id')}, function (data) {
                $('#file_content').val(data['file_content']);
                $('#file_name').val(data['file_name']);
                $('#file_mime').val(data['file_mime']);
                $('#msg_session').val(data['msg_session']);
                dialog.dialog("open");
            });
        });

        $('.deleteFile').on('click', function (e) {
            e.preventDefault();
            var fid = $(this).data('id');
            var row = $(this).closest('.row');
            if (confirm('Вы уверены, что хотите удалить файл?')) {
                $.getJSON('/file/delete-client-file', {id: fid}, function (data) {
                    console.log(data);
                    console.log(data['status'] == 'ok');
                    if (data['status'] == 'ok')
                        row.remove();
                });
            }
        });
    </script>

    <style>
        .file_upload {
            position: relative;
            overflow: hidden;
            text-align: center;
            width: 100%;
        }

        .insblock td, .insblock th {
            padding: 2px 5px;
        }

        .file_upload input[type=file] {
            position: absolute;
            top: 0;
            right: 0;
            opacity: 0;
            filter: alpha(opacity=0);
            cursor: pointer;
        }
    </style>

    <script>
        var folderTranslates = <?= json_encode(\app\dao\ClientDocumentDao::$folders) ?>;
        var folders = <?= json_encode(\app\dao\ClientDocumentDao::templateList(true)) ?>;

        function generateTmplList(type, selected) {
            if (!selected)
                selected = $('.tmpl-group[data-type="' + type + '"]').val();
            var tmpl = $('.tmpl[data-type="' + type + '"]');
            tmpl.empty();
            if (typeof folders[type] !== 'undefined' && typeof folders[type][selected] !== 'undefined') {
                $.each(folders[type][selected], function (k, v) {
                    tmpl.append('<option value="' + v + '">' + v + '</option>');
                });
            }
        }

        $(function () {
            $('.tmpl-group').each(function () {
                var type = $(this).data('type');
                var t = $(this);
                var first = false;
                $.each(folders[type], function (k, v) {
                    t.append('<option value="' + k + '">' + folderTranslates[k] + '</option>');
                    if (first == false) {
                        first = k;
                    }
                });
                $('.tmpl-group[data-type="' + type + '"]').val(first);
                generateTmplList(type, first);
            });

            $('.tmpl-group').on('change', function () {
                generateTmplList($(this).data('type'), $(this).val());
            });


            if($('#contracteditform-business_id').val() == 3)
                $('#change-external').val('external');
            else
                $('#change-external').val('internal');

            $('#contracteditform-business_id').on('change', function(){
                if($('#contracteditform-business_id').val() == 3)
                    $('#change-external').val('external');
                else
                    $('#change-external').val('internal');

                $('#change-external').trigger('change');
            });

            $('#change-external').on('change', function () {
                var fields = $('.tmpl-group[data-type="contract"], .tmpl[data-type="contract"]');

                if($(this).val() == 'internal')
                    fields.show();
                else
                    fields.hide();
            }).trigger('change');
        });
    </script>
</div>

<script type="text/javascript" src="/js/behaviors/managers_by_contract_type.js"></script>
<script type="text/javascript" src="/js/behaviors/organization_by_legal_type.js"></script>
<script type="text/javascript" src="/js/behaviors/show-last-changes.js"></script>
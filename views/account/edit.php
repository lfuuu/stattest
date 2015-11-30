<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use yii\helpers\Url;
use app\classes\Language;
use app\models\ClientDocument;
use app\models\UserGroups;

$language = Language::getLanguageByCountryId(643);
?>
<div class="row">
    <div class="col-sm-12">
        <h2><?= ($model->isNewRecord) ? 'Создание' : 'Редактирование' ?> Лицевого Счета</h2>

        <?php $f = ActiveForm::begin(); ?>

        <?= $this->render($language . '/form', ['model' => $model, 'f' => $f]); ?>

        <div class="row" style="width: 1100px;">

            <div class="row">
                <div class="col-sm-4">
                    <div class="col-sm-12" type="textInput">
                        <label class="control-label" for="historyVersionStoredDate">Сохранить на</label>
                        <?= Html::dropDownList('AccountEditForm[historyVersionStoredDate]', null, $model->getModel()->getDateList(),
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
                <?= Html::button('Сохранить', ['class' => 'btn btn-primary', 'id' => 'buttonSave']); ?>
            </div>
            <?php ActiveForm::end(); ?>

            <?php if (!$model->isNewRecord): ?>
                <div class="col-sm-12 form-group">
                    <a href="#" onclick="return showVersion({ClientAccount:<?= $model->id ?>}, true);">Версии</a><br/>
                <?php if(Yii::$app->user->identity->usergroup == UserGroups::ADMIN): ?>
                    <?= Html::button('∨', ['style' => 'border-radius: 22px;', 'class' => 'btn btn-default showhistorybutton', 'onclick' => 'showHistory({ClientAccount:' . $model->id . '})']); ?>
                    <span>История изменений</span>
                <?php endif; ?>
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
                var datepicker = $('#deferred-date-input');
                if ($("option:selected", this).val() == '') {
                    datepicker.parent().parent().show();
                }
                else {
                    datepicker.parent().parent().hide();
                }
            });
        </script>
    </div>
    <?php if(!$model->getIsNewRecord()): ?>
    <div class="col-sm-12">
        <div class="row" style="padding: 5px 0; color: white; background: black; font-weight: bold; margin-top: 10px; text-align: center;">
            <div class="col-sm-12">Дополнительные ИНН</div>
        </div>

        <div class="row head3" style="padding: 5px 0; border-top: 1px solid black;">
            <div class="col-sm-2"><?= $addInnModel->getAttributeLabel('inn') ?></div>
            <div class="col-sm-2"><?= $addInnModel->getAttributeLabel('comment') ?></div>
            <div class="col-sm-2"><?= $addInnModel->getAttributeLabel('user_id') ?></div>
            <div class="col-sm-2"><?= $addInnModel->getAttributeLabel('ts') ?></div>
            <div class="col-sm-1"></div>
        </div>
        <?php foreach($model->getModel()->additionalInn as $inn) : ?>
            <div class="row" style="padding: 5px 0; border-top: 1px solid black;">
                <div class="col-sm-2"><?= $inn->inn ?></div>
                <div class="col-sm-2"><?= $inn->comment ?></div>
                <div class="col-sm-2"><?= $inn->user->name ?></div>
                <div class="col-sm-2"><?= $inn->ts ?></div>
                <div class="col-sm-1">
                    <a href="/account/additional-inn-delete?id=<?= $inn->id ?>">
                        <img style="margin-left:-2px;margin-top:-3px" class="icon" src="/images/icons/delete.gif" alt="Активность">
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
        <div class="row">
            <form action="/account/additional-inn-create?accountId=<?= $model->id ?>" method="post">
                <div class="col-sm-2">
                    <?= Html::activeTextInput($addInnModel, 'inn', ['class' => 'form-control']) ?>
                </div>
                <div class="col-sm-2">
                    <?= Html::activeTextInput($addInnModel, 'comment', ['class' => 'form-control has-error']) ?>
                </div>
                <div class="col-sm-3"></div>
                <div class="col-sm-2"><button type="submit" class="btn btn-primary col-sm-12">Добавить</button> </div>
            </form>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="row" style="padding: 5px 0; color: white; background: black; font-weight: bold; margin-top: 10px; text-align: center;">
            <div class="col-sm-12">Дополнительные Р/С</div>
        </div>

        <div class="row head3" style="padding: 5px 0;">
            <div class="col-sm-2"><?= $addAccModel->getAttributeLabel('pay_acc') ?></div>
            <div class="col-sm-2"><?= $addAccModel->getAttributeLabel('who') ?></div>
            <div class="col-sm-2"><?= $addAccModel->getAttributeLabel('date') ?></div>
            <div class="col-sm-2"></div>
            <div class="col-sm-1"></div>
        </div>
        <?php foreach($model->getModel()->additionalPayAcc as $payAcc) : ?>
            <div class="row" style="padding: 5px 0; border-top: 1px solid black;">
                <div class="col-sm-2"><?= $payAcc->pay_acc ?></div>
                <div class="col-sm-2"><?= $payAcc->user->name ?></div>
                <div class="col-sm-2"><?= $payAcc->date ?></div>
                <div class="col-sm-2"></div>
                <div class="col-sm-1">
                    <a href="/account/additional-pay-acc-delete?id=<?= $payAcc->id ?>">
                        <img style="margin-left:-2px;margin-top:-3px" class="icon" src="/images/icons/delete.gif" alt="Активность">
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
        <div class="row">
            <form action="/account/additional-pay-acc-create?accountId=<?= $model->id ?>" method="post">
                <div class="col-sm-2">
                    <?= Html::activeTextInput($addAccModel, 'pay_acc', ['class' => 'form-control']) ?>
                </div>
                <div class="col-sm-5"></div>
                <div class="col-sm-2"><button type="submit" class="btn btn-primary col-sm-12">Добавить</button> </div>
            </form>
        </div>
    </div>


    <?php $docs = ClientDocument::find()->accountId($model->id)->blank()->all(); ?>

    <div class="col-sm-12">
        <div class="row"
             style="padding: 5px 0; color: white; background: black; font-weight: bold; margin-top: 10px; text-align: center;">
            <div class="col-sm-12">Бланк заказа</div>
        </div>
        <div class="row head3" style="padding: 5px 0;">
            <div class="col-sm-2">№</div>
            <div class="col-sm-2">Дата</div>
            <div class="col-sm-2">Комментарий</div>
            <div class="col-sm-2">Кто добавил</div>
            <div class="col-sm-2">Когда</div>
            <div class="col-sm-2"></div>
        </div>
        <?php foreach ($docs as $doc) if ($doc->type == 'blank'): ?>
            <?php $blnk = $doc->contract_no; ?>
            <div class="row"
                 style="padding: 5px 0; border-top: 1px solid black; <?= !$doc->is_active ? 'color:#CCC;' : '' ?>">
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
        <div class="row" style="margin-top: 5px;">
            <form action="/document/create" method="post">
                <div class="col-sm-2">
                    <input type="hidden" name="ClientDocument[contract_id]" value="<?= $model->contract_id ?>">
                    <input type="hidden" name="ClientDocument[account_id]" value="<?= $model->id ?>">
                    <input type="hidden" name="ClientDocument[type]" value="<?=ClientDocument::DOCUMENT_BLANK_TYPE?>">
                    <input class="form-control" type="text" name="ClientDocument[contract_no]"
                           value="<?= isset($blnk) ? $blnk + 1 : 1 ?>">
                </div>
                <div class="col-sm-2">
                    <?= DatePicker::widget(
                        [
                            'name' => 'ClientDocument[contract_date]',
                            'value' => date('Y-m-d'),
                            'removeButton' => false,
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd',
                            ],
                        ]
                    ); ?>
                </div>
                <div class="col-sm-2"><input class="form-control input-sm" type="text" name="ClientDocument[comment]"></div>
                <div class="col-sm-2">
                    <select class="form-control input-sm tmpl-group" data-type="<?=ClientDocument::DOCUMENT_BLANK_TYPE?>"></select>
                </div>
                <div class="col-sm-2">
                    <select class="form-control input-sm tmpl" name="ClientDocument[template_id]"
                            data-type="<?=ClientDocument::DOCUMENT_BLANK_TYPE?>"></select>
                </div>
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-primary btn-sm col-sm-12">Зарегистрировать</button>
                </div>
            </form>
        </div>
    </div>

    <?php endif; ?>
</div>

<script> var templates = <?= json_encode(\app\dao\ClientDocumentDao::templateList()) ?>; </script>
<script type="text/javascript" src="/js/behaviors/find-bik.js"></script>
<script type="text/javascript" src="/js/behaviors/show-last-changes.js"></script>
<script type="text/javascript" src="/js/behaviors/change-doc-template.js"></script>

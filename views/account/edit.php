<?php
/**
 * @var \app\classes\BaseView $this
 * @var AccountEditForm $model
 * @var ClientPayAcc $addAccModel
 * @var ClientInn $addInnModel
 */

use app\assets\AppAsset;
use app\classes\Html;
use app\classes\Language;
use app\dao\ClientDocumentDao;
use app\forms\client\AccountEditForm;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientDocument;
use app\models\ClientInn;
use app\models\ClientPayAcc;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

$this->registerJsFile('@web/js/behaviors/find-bik.js', ['depends' => [AppAsset::className()]]);
$this->registerJsFile('@web/js/behaviors/show-last-changes.js', ['depends' => [AppAsset::className()]]);
$this->registerJsFile('@web/js/behaviors/change-doc-template.js', ['depends' => [AppAsset::className()]]);
$this->registerJsFile('@web/js/behaviors/history-version.js', ['depends' => [AppAsset::className()]]);

$language = Language::getLanguageByCountryId($model->getModel()->contragent->country_id);
$id = $model->clientM->id;
?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Аккаунт', 'url' => Url::to(['client/view', 'id' => $id])],
        ['label' => $this->title = ($model->isNewRecord ? 'Создание' : 'Редактирование') . ' ЛС', 'url' => Url::to(['account/edit', 'id' => $id])],
    ],
]) ?>

<div class="row">
    <div class="col-sm-12">
        <?php $f = ActiveForm::begin(); ?>

        <?= $this->render($this->getFormPath('account', $language), ['model' => $model, 'f' => $f]); ?>

        <div class="row max-screen">

            <div class="row">
                <div class="col-sm-4">
                    <div class="col-sm-12">
                        <label class="control-label" for="historyVersionStoredDate">Сохранить на</label>
                        <?= Html::dropDownList(
                            'AccountEditForm[historyVersionStoredDate]',
                            null,
                            $model->getModel()->getDateList(),
                            [
                                'class' => 'form-control',
                                'id' => 'historyVersionStoredDate'
                            ]
                        ); ?>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="col-sm-12">
                        <label class="control-label" for="deferred-date-input">Выберите дату</label>
                        <?= DatePicker::widget(
                            [
                                'name' => 'kartik-date-3',
                                'value' => Yii::$app->request->get('date') ? Yii::$app->request->get('date') : date(DateTimeZoneHelper::DATE_FORMAT),
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
                <?= $this->render('//layouts/_submitButtonSave') ?>
            </div>

            <?php if (!$model->isNewRecord) : ?>
                <div class="col-sm-12 form-group">
                    <?= $this->render('//layouts/_showVersion', ['model' => $model->clientM]) ?>
                    <?= $this->render('//layouts/_showHistory', ['model' => $model->clientM]) ?>
                </div>
            <?php endif; ?>

        </div>
        <?php ActiveForm::end(); ?>
    </div>

    <?php if (!$model->getIsNewRecord()) : ?>
        <div class="col-sm-12 extend-block">
            <div class="row title">
                <div class="col-sm-12">Дополнительные ИНН</div>
            </div>

            <div class="row record">
                <div class="col-sm-2"><?= $addInnModel->getAttributeLabel('inn') ?></div>
                <div class="col-sm-2"><?= $addInnModel->getAttributeLabel('comment') ?></div>
                <div class="col-sm-2"><?= $addInnModel->getAttributeLabel('user_id') ?></div>
                <div class="col-sm-2"><?= $addInnModel->getAttributeLabel('ts') ?></div>
                <div class="col-sm-1"></div>
            </div>

            <?php foreach ($model->getModel()->additionalInn as $inn) : ?>
                <div class="row record">
                    <div class="col-sm-2"><?= $inn->inn ?></div>
                    <div class="col-sm-2"><?= $inn->comment ?></div>
                    <div class="col-sm-2"><?= $inn->user->name ?></div>
                    <div class="col-sm-2"><?= $inn->ts ?></div>
                    <div class="col-sm-1">
                        <a href="<?= Url::toRoute(['/account/additional-inn-delete', 'id' => $inn->id]) ?>">
                            <img class="icon left-top-indent" src="/images/icons/delete.gif" alt="Активность" />
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="row">
                <form action="<?= Url::toRoute(['/account/additional-inn-create', 'accountId' => $model->id]) ?>" method="post">
                    <div class="col-sm-2">
                        <?= Html::activeTextInput($addInnModel, 'inn', ['class' => 'form-control']) ?>
                    </div>
                    <div class="col-sm-2">
                        <?= Html::activeTextInput($addInnModel, 'comment', ['class' => 'form-control has-error']) ?>
                    </div>
                    <div class="col-sm-3"></div>
                    <div class="col-sm-2">
                        <button type="submit" class="btn btn-primary col-sm-12">Добавить</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-sm-12 extend-block">
            <div class="row title">
                <div class="col-sm-12">Дополнительные Р/С</div>
            </div>

            <div class="row record">
                <div class="col-sm-2"><?= $addAccModel->getAttributeLabel('pay_acc') ?></div>
                <div class="col-sm-2"><?= $addAccModel->getAttributeLabel('who') ?></div>
                <div class="col-sm-2"><?= $addAccModel->getAttributeLabel('date') ?></div>
                <div class="col-sm-2"></div>
                <div class="col-sm-1"></div>
            </div>

            <?php foreach ($model->getModel()->additionalPayAcc as $payAcc) : ?>
                <div class="row record">
                    <div class="col-sm-2"><?= $payAcc->pay_acc ?></div>
                    <div class="col-sm-2"><?= $payAcc->user->name ?></div>
                    <div class="col-sm-2"><?= $payAcc->date ?></div>
                    <div class="col-sm-2"></div>
                    <div class="col-sm-1">
                        <a href="<?= Url::toRoute(['/account/additional-pay-acc-delete', 'id' => $payAcc->id]) ?>">
                            <img class="icon left-top-indent" src="/images/icons/delete.gif" alt="Активность" />
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="row">
                <form action="<?= Url::toRoute(['/account/additional-pay-acc-create', 'accountId' => $model->id]) ?>" method="post">
                    <div class="col-sm-2">
                        <?= Html::activeTextInput($addAccModel, 'pay_acc', ['class' => 'form-control']) ?>
                    </div>
                    <div class="col-sm-5"></div>
                    <div class="col-sm-2">
                        <button type="submit" class="btn btn-primary col-sm-12">Добавить</button>
                    </div>
                </form>
            </div>
        </div>

        <?php $docs = ClientDocument::find()->accountId($model->id)->blank()->all(); ?>

        <div class="col-sm-12 extend-block">
            <div class="row title">
                <div class="col-sm-12">Бланк заказа</div>
            </div>

            <div class="row record">
                <div class="col-sm-2">№</div>
                <div class="col-sm-2">Дата</div>
                <div class="col-sm-2">Комментарий</div>
                <div class="col-sm-2">Кто добавил</div>
                <div class="col-sm-2">Когда</div>
                <div class="col-sm-2"></div>
            </div>

            <?php foreach ($docs as $doc) :
                if ($doc->type == ClientDocument::DOCUMENT_BLANK_TYPE) : ?>
                    <?php $blnk = $doc->contract_no; ?>
                    <div class="row record<?= !$doc->is_active ? ' active' : '' ?>">
                        <div class="col-sm-2"><?= $doc->contract_no ?></div>
                        <div class="col-sm-2"><?= $doc->contract_date ?></div>
                        <div class="col-sm-2"><?= $doc->comment ?></div>
                        <div class="col-sm-2"><?= $doc->user->name ?></div>
                        <div class="col-sm-2"><?= $doc->ts ?></div>
                        <div class="col-sm-2">
                            <a href="<?= Url::toRoute(['/document/edit', 'id' => $doc->id]) ?>" target="_blank">
                                <img class="icon" src="/images/icons/edit.gif" />
                            </a>
                            <a href="<?= Url::toRoute(['/document/print/', 'id' => $doc->id]) ?>" target="_blank">
                                <img class="icon" src="/images/icons/printer.gif" />
                            </a>
                            <a href="<?= Url::toRoute(['/document/send', 'id' => $doc->id]) ?>" target="_blank">
                                <img class="icon" src="/images/icons/contract.gif" />
                            </a>
                            <?php if ($doc->is_active) : ?>
                                <a href="<?= Url::toRoute(['document/activate', 'id' => $doc->id]) ?>">
                                    <img class="icon left-top-indent" src="/images/icons/delete.gif" />
                                </a>
                            <?php else : ?>
                                <a href="<?= Url::toRoute(['document/activate', 'id' => $doc->id]) ?>">
                                    <img class="icon left-top-indent" src="/images/icons/add.gif" />
                                </a>
                            <?php endif ?>
                            <a href="<?= Url::toRoute(['/document/print-by-code', 'code' => $doc->link]) ?>" target="_blank">
                                ссылка
                            </a>
                        </div>
                    </div>
                <?php endif ?>
            <?php endforeach ?>
            <div class="row" style="margin-top: 5px;">
                <form action="/document/create" method="post">
                    <div class="col-sm-2">
                        <input type="hidden" name="ClientDocument[contract_id]" value="<?= $model->contract_id ?>" />
                        <input type="hidden" name="ClientDocument[account_id]" value="<?= $model->id ?>" />
                        <input type="hidden" name="ClientDocument[type]" value="<?= ClientDocument::DOCUMENT_BLANK_TYPE ?>" />
                        <input class="form-control" type="text" name="ClientDocument[contract_no]"
                               value="<?= isset($blnk) ? $blnk + 1 : 1 ?>" />
                    </div>
                    <div class="col-sm-2">
                        <?= DatePicker::widget(
                            [
                                'name' => 'ClientDocument[contract_date]',
                                'value' => date(DateTimeZoneHelper::DATE_FORMAT),
                                'removeButton' => false,
                                'pluginOptions' => [
                                    'autoclose' => true,
                                    'format' => 'yyyy-mm-dd',
                                ],
                            ]
                        ); ?>
                    </div>
                    <div class="col-sm-2">
                        <input class="form-control input-sm" type="text" name="ClientDocument[comment]" />
                    </div>
                    <div class="col-sm-2">
                        <?= Html::dropDownList('', null, ClientDocumentDao::getFolders($documentType = ClientDocument::DOCUMENT_BLANK_TYPE), [
                            'class' => 'form-control input-sm document-template',
                            'data-documents' => ClientDocument::DOCUMENT_BLANK_TYPE,
                        ]);
                        ?>
                    </div>
                    <div class="col-sm-2">
                        <select
                                class="form-control input-sm tmpl-documents"
                                name="ClientDocument[template_id]"
                                data-documents-type="<?= ClientDocument::DOCUMENT_BLANK_TYPE ?>">
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <button type="submit" class="btn btn-primary btn-sm col-sm-12">Зарегистрировать</button>
                    </div>
                </form>
            </div>
        </div>

    <?php endif; ?>
</div>

<script type="text/javascript">
    var
        documentFolders = <?= Json::encode(ClientDocumentDao::getFoldersByDocumentType([ClientDocument::DOCUMENT_BLANK_TYPE])) ?>,
        documentTemplates = <?= Json::encode(ClientDocumentDao::getTemplates()) ?>;
</script>
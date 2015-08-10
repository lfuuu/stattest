<?php

use yii\helpers\Html;
use app\models\Region;
use app\models\SaleChannel;
use app\models\PriceType;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;
use kartik\builder\Form;
use kartik\widgets\DatePicker;
use app\models\ClientAccount;
use app\models\Currency;
use yii\helpers\Url;

?>
<div class="row">
    <div class="col-sm-12">
        <h2><?= ($model->isNewRecord) ? 'Создание' : 'Редактирование' ?> Лицевого Счета</h2>

        <?php $f = ActiveForm::begin(); ?>

        <div class="row" style="width: 1100px;">
            <?php

            echo '<div>';
            echo Form::widget([
                'model' => $model,
                'form' => $f,
                'columns' => 4,
                'attributeDefaults' => [
                    'container' => ['class' => 'col-sm-12'],
                    'type' => Form::INPUT_TEXT
                ],
                'attributes' => [
                    'region' => [
                        'type' => Form::INPUT_RAW,
                        'value' => '<div class="col-sm-12" style="padding-bottom: 15px;"><label>' . $model->attributeLabels()['region'] . '</label>'
                            . Select2::widget([
                                'model' => $model,
                                'attribute' => 'region',
                                'data' => Region::getList(),
                                'options' => ['placeholder' => 'Начните вводить название'],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                            ])
                            . '</div>'
                    ],
                    'timezone_name' => ['type' => Form::INPUT_DROPDOWN_LIST, "items" => Region::getTimezoneList()],
                    'sale_channel' => [
                        'type' => Form::INPUT_RAW,
                        'value' => '<div class="col-sm-12" style="padding-bottom: 15px;"><label>' . $model->attributeLabels()['sale_channel'] . '</label>'
                            . Select2::widget([
                                'model' => $model,
                                'attribute' => 'sale_channel',
                                'data' => SaleChannel::getList(),
                                'options' => ['placeholder' => 'Начните вводить название'],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                            ])
                            . '</div>'
                    ],
                    'empty25' => ['type' => Form::INPUT_RAW,],

                    'nal' => ['type' => Form::INPUT_DROPDOWN_LIST, "items" => ClientAccount::$nalTypes],
                    'currency' => ['type' => Form::INPUT_DROPDOWN_LIST, "items" => Currency::map()],
                    'price_type' => ['type' => Form::INPUT_DROPDOWN_LIST, "items" => PriceType::getList()],
                    'empty1' => ['type' => Form::INPUT_RAW,],

                    'voip_disabled' => ['type' => Form::INPUT_CHECKBOX, 'columnOptions' => ['colspan' => 3],],
                    'empty15' => ['type' => Form::INPUT_RAW,],
                    'empty16' => ['type' => Form::INPUT_RAW,],
                    'empty17' => ['type' => Form::INPUT_RAW,],

                    'credit' => ['type' => Form::INPUT_CHECKBOX, 'options' => ['id' => 'credit'], 'columnOptions' => ['style' => 'margin-top: 20px;']],
                    'credit_size' => ['columnOptions' => ['id' => 'credit-size', 'style' => $model->credit > 0 ? '' : 'display:none;']],
                    'empty13' => ['type' => Form::INPUT_RAW,],
                    'empty14' => ['type' => Form::INPUT_RAW,],

                    'voip_credit_limit' => ['columnOptions' => ['colspan' => 2], 'options' => ['style' => 'width:20%;']],
                    'empty18' => ['type' => Form::INPUT_RAW,],
                    'empty19' => ['type' => Form::INPUT_RAW,],
                    'empty20' => ['type' => Form::INPUT_RAW,],

                    'voip_credit_limit_day' => ['columnOptions' => ['colspan' => 1],],
                    'voip_is_day_calc' => ['type' => Form::INPUT_CHECKBOX, 'columnOptions' => ['colspan' => 3, 'style' => 'margin-top: 35px;'],],
                    'empty21' => ['type' => Form::INPUT_RAW,],
                    'empty22' => ['type' => Form::INPUT_RAW,],

                    'mail_print' => ['type' => Form::INPUT_CHECKBOX, 'columnOptions' => ['style' => 'margin-top: 20px;', 'colspan' => 2],],
                    'is_with_consignee' => ['type' => Form::INPUT_CHECKBOX, 'columnOptions' => ['style' => 'margin-top: 20px;', 'colspan' => 2], 'options' => ['id' => 'with-consignee']],
                    'empty9' => ['type' => Form::INPUT_RAW,],
                    'empty10' => ['type' => Form::INPUT_RAW,],

                    'address_post' => ['columnOptions' => ['colspan' => 2],],
                    'head_company' => ['columnOptions' => ['colspan' => 2],],
                    'empty2' => ['type' => Form::INPUT_RAW,],
                    'empty3' => ['type' => Form::INPUT_RAW,],

                    'address_post_real' => ['columnOptions' => ['colspan' => 2],],
                    'head_company_address_jur' => ['columnOptions' => ['colspan' => 2],],
                    'empty4' => ['type' => Form::INPUT_RAW,],
                    'empty5' => ['type' => Form::INPUT_RAW,],

                    'mail_who' => ['columnOptions' => ['colspan' => 2],],
                    'consignee' => ['columnOptions' => ['colspan' => 2, 'id' => 'consignee']],
                    'empty7' => ['type' => Form::INPUT_RAW,],
                    'empty8' => ['type' => Form::INPUT_RAW,],

                    'form_type' => ['type' => Form::INPUT_DROPDOWN_LIST, "items" => ClientAccount::$formTypes],
                    'stamp' => ['type' => Form::INPUT_CHECKBOX, 'columnOptions' => ['style' => 'margin-top: 20px;'],],
                    'is_upd_without_sign' => ['type' => Form::INPUT_CHECKBOX, 'columnOptions' => ['style' => 'margin-top: 20px;'],],
                    'empty26' => ['type' => Form::INPUT_RAW,],

                    'bill_rename1' => ['type' => Form::INPUT_RADIO_LIST, "items" => ['yes' => 'Оказанные услуги по Договору', 'no' => 'Абонентская плата по Договору'],],
                    'empty27' => ['type' => Form::INPUT_RAW,],
                    'empty28' => ['type' => Form::INPUT_RAW,],
                    'empty29' => ['type' => Form::INPUT_RAW,],

                    'bik' => ['columnOptions' => ['colspan' => 2],],
                    'corr_acc' => ['columnOptions' => ['colspan' => 2], 'options' => ['disabled' => 'disabled']],
                    'empty30' => ['type' => Form::INPUT_RAW,],
                    'empty31' => ['type' => Form::INPUT_RAW,],

                    'pay_acc' => ['columnOptions' => ['colspan' => 2],],
                    'bank_name' => ['columnOptions' => ['colspan' => 2], 'options' => ['disabled' => 'disabled']],
                    'empty32' => ['type' => Form::INPUT_RAW,],
                    'empty33' => ['type' => Form::INPUT_RAW,],

                    'custom_properties' => ['type' => Form::INPUT_CHECKBOX, 'columnOptions' => ['style' => 'margin-top: 20px;', 'colspan' => 2],],
                    'bank_city' => ['columnOptions' => ['colspan' => 2], 'options' => ['disabled' => 'disabled']],
                    'empty35' => ['type' => Form::INPUT_RAW,],
                    'empty36' => ['type' => Form::INPUT_RAW,],

                    'bank_properties' => ['type' => Form::INPUT_TEXTAREA,'columnOptions' => ['colspan' => 4], 'options' => ['disabled' => 'disabled']],
                    'empty36' => ['type' => Form::INPUT_RAW,],
                    'empty36' => ['type' => Form::INPUT_RAW,],
                    'empty36' => ['type' => Form::INPUT_RAW,],
                ],
            ]);

            echo '</div>';
            ?>


            <div class="row">
                <div class="col-sm-4">
                    <div class="col-sm-12" type="textInput">
                        <label class="control-label" for="deferred-date">Сохранить на</label>
                        <?php $months = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сенября', 'октября', 'ноября', 'декабря']; ?>
                        <?= Html::dropDownList('deferred-date', null,
                            (Yii::$app->request->get('date') ? [Yii::$app->request->get('date') => 'Дату из истории'] : [])
                            +
                            [
                                date('Y-m-d', time()) => 'Текущую дату',
                                date('Y-m-01', strtotime('- 1 month')) => 'С 1го ' . $months[date('m', strtotime('- 1 month')) - 1],
                                date('Y-m-01') => 'С 1го ' . $months[date('m') - 1],
                                date('Y-m-01', strtotime('+ 1 month')) => 'С 1го ' . $months[date('m', strtotime('+ 1 month')) - 1],
                                '' => 'Выбраную дату'
                            ],
                            ['class' => 'form-control', 'style' => 'margin-bottom: 20px;', 'name' => 'deferred-date', 'id' => 'deferred-date']); ?>
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
                    <?= Html::button('∨', ['style' => 'border-radius: 22px;', 'class' => 'btn btn-default showhistorybutton', 'onclick' => 'showHistory({ClientAccount:' . $model->id . '})']); ?>
                    <span>История изменений</span>
                </div>
            <?php endif; ?>

        </div>

        <script>
            $(function () {
                $('#deferred-date-input').parent().parent().hide();
                $('#credit').on('click', function () {
                    $('#credit-size').toggle();
                });
            });

            $('#buttonSave').on('click', function (e) {
                if ($("#deferred-date option:selected").val() == '')
                    $('#deferred-date option:selected').val($('#deferred-date-input').val()).select();
                return true;
            });

            $('#deferred-date').on('change', function () {
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


    <?php $docs = \app\models\ClientDocument::find()->accountId($model->id)->blank()->all(); ?>

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
                    <input type="hidden" name="ClientDocument[type]" value="blank">
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
                    <select class="form-control input-sm tmpl-group" name="ClientDocument[group]"
                            data-type="blank"></select>
                </div>
                <div class="col-sm-2">
                    <select class="form-control input-sm tmpl" name="ClientDocument[template]"
                            data-type="blank"></select>
                </div>
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-primary btn-sm col-sm-12">Зарегистрировать</button>
                </div>
            </form>
        </div>
    </div>

    <?php endif; ?>

    <script>
        var folderTranslates = <?= json_encode(\app\dao\ClientDocumentDao::$folders) ?>;
        var folders = <?= json_encode(\app\dao\ClientDocumentDao::templateList(true)) ?>;

        function generateTmplList(type, selected) {
            if (!selected)
                selected = $('.tmpl-group[data-type="' + type + '"]').val();
            var tmpl = $('.tmpl[data-type="' + type + '"]');
            if (typeof folders[type] !== 'undefined' && typeof folders[type][selected] !== 'undefined') {
                tmpl.empty();
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
                    t.append('<option value="' + k + '" ' + (first ? 'selected=selected' : '') + ' >' + folderTranslates[k] + '</option>');
                    if (first == false) {
                        first = k;
                    }
                });
                generateTmplList(type, first);
            });

            $('.tmpl-group').on('change', function () {
                generateTmplList($(this).data('type'), $(this).val());
            })
        });
    </script>
</div>

<script type="text/javascript" src="/js/behaviors/find-bik.js"></script>
<script type="text/javascript" src="/js/behaviors/show-last-changes.js"></script>
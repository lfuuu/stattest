<?php

use app\assets\AppAsset;
use app\classes\Html;
use app\forms\usage\UsageTrunkCloseForm;
use app\forms\usage\UsageTrunkSettingsAddForm;
use app\forms\usage\UsageTrunkSettingsEditForm;
use app\helpers\DateTimeZoneHelper;
use app\models\billing\Number;
use app\models\billing\Pricelist;
use app\models\billing\Trunk;
use app\modules\auth\models\Trunk as AuthTrunk;
use app\models\UsageTrunkSettings;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use app\widgets\TagsSelect2\TagsSelect2;
use kartik\builder\Form;
use kartik\datecontrol\DateControl;
use kartik\widgets\ActiveForm;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

/**
 * @var \app\models\ClientAccount $clientAccount
 * @var \app\models\UsageTrunk $usage
 * @var \app\forms\usage\UsageTrunkEditForm $model
 * @var UsageTrunkSettings[] $origination
 * @var UsageTrunkSettings[] $termination
 * @var UsageTrunkSettings[] $destination
 * @var \app\classes\BaseView $this
 */

$this->registerJsFile('@web/js/behaviors/usage-trunk-pricelist-link.js', ['depends' => [AppAsset::class]]);

$trunk = Trunk::findOne($usage->trunk_id);

$trunks = ['' => '-- Выберите Транк -- '] + Trunk::dao()->getList(['serverIds' => $usage->connection_point_id]);

$srcNumbers = ['' => '-- Любой номер -- '] + Number::getList(Number::TYPE_SRC, $usage->connection_point_id);
$dstNumbers = ['' => '-- Любой номер -- '] + Number::getList(Number::TYPE_DST, $usage->connection_point_id);

$termPackages = ['' => '-- Пакет -- '];
$origPackages = ['' => '-- Пакет -- '];

$isUu = $usage->id > AccountTariff::DELTA;
if ($isUu) {
    // Построить список допустимых пакетов. Только тех, которые подключены на услугу транка
    /** @var AccountTariff[] $accountTariffs */
    $accountTariffs = AccountTariff::find()
        ->where(['prev_account_tariff_id' => $usage->id])
        ->all();
    foreach ($accountTariffs as $accountTariff) {
        if (!$accountTariff->isActive()) {
            continue;
        }

        $tariffPeriod = $accountTariff->tariffPeriod;
        if ($tariffPeriod) {
            $tariff = $tariffPeriod->tariff;
        } else {
            $tariffLog = reset($accountTariff->accountTariffLogs);
            if (!($tariffPeriod = $tariffLog->tariffPeriod)) {
                continue;
            }
            $tariff = $tariffPeriod->tariff;
        }

        // Дата подключения пакета
        $accountTariffLog = reset($accountTariff->accountTariffLogs);
        if ($accountTariffLog) {
            $accountTariffLogActualFrom = Yii::$app->formatter->asDate($accountTariffLog->actual_from, DateTimeZoneHelper::HUMAN_DATE_FORMAT);
            $value = sprintf('%s с (%s)', $tariff->name, $accountTariffLogActualFrom);
        }

        switch ($accountTariff->service_type_id) {
            case ServiceType::ID_TRUNK_PACKAGE_ORIG:
                $origPackages[$tariff->id] = $value ?: $tariff->name;
                break;
            case ServiceType::ID_TRUNK_PACKAGE_TERM:
                $termPackages[$tariff->id] = $value ?: $tariff->name;
                break;
        }
    }

    $termPricelists = [];
    $origPricelists = [];
} else {
    $termPricelists = ['' => '-- Прайслист -- '] +
        Pricelist::getList($isWithEmpty = false, $isWithNullAndNotNull = false, $type = Pricelist::TYPE_LOCAL, $orig = false, $priceIncludeVat = $usage->clientAccount->is_voip_with_tax) +
        Pricelist::getList($isWithEmpty = false, $isWithNullAndNotNull = false, $type = Pricelist::TYPE_OPERATOR, $orig = false, $priceIncludeVat = $usage->clientAccount->is_voip_with_tax);
    $origPricelists = ['' => '-- Прайслист -- '] +
        Pricelist::getList($isWithEmpty = false, $isWithNullAndNotNull = false, $type = Pricelist::TYPE_OPERATOR, $orig = true, $priceIncludeVat = $usage->clientAccount->is_voip_with_tax);
}

echo Html::formLabel('Редактирование транка');
echo Breadcrumbs::widget([
    'links' => [
        'homeLink' => [
            'label' => $clientAccount->company,
            'url' => ['client/view', 'id' => $clientAccount->id]
        ],
        ['label' => 'Телефония Транки', 'url' => Url::toRoute(['/', 'module' => 'services', 'action' => 'trunk_view'])],
        'Редактирование транка'
    ],
]);
?>

<div class="well">
    <?php
    /** @var ActiveForm $form */
    $form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL]);

    $this->registerJsVariables([
        'editFormId' => $form->getId(),
        'tariffEditFormId' => '',
    ], 'usage');
    ?>

    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <label class="control-label">Регион (точка подключения)</label>
                <input type="text" class="form-control" value="<?= $usage->connectionPoint->name ?>" readonly="readonly"/>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label class="control-label">Страна</label>
                <input type="text" class="form-control" value="<?= $clientAccount->country->name ?>" readonly="readonly"/>
            </div>
        </div>
        <div class="col-sm-2">
            <div class="form-group">
                <label class="control-label">Валюта</label>
                <input type="text" class="form-control" value="<?= $clientAccount->currency ?>" readonly="readonly"/>
            </div>
        </div>
        <div class="col-sm-2">
            <div class="form-group">
                <label class="control-label">Мега/мульти транк</label>
                <div>
                    <?php
                    $accountTariff = $usage->accountTariff;
                    if ($accountTariff && $accountTariff->trunk_type_id) {
                        $trunkTypes = AccountTariff::getTrunkTypeList();
                        echo $trunkTypes[$accountTariff->trunk_type_id];
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-4">
            <?= $form
                ->field($model, 'trunk_id')
                ->dropDownList($trunks, ['class' => 'select2'])
            ?>
        </div>
        <div class="col-sm-4">
            <?= $form
                ->field($model, 'actual_from')
                ->widget(DateControl::class, ['autoWidget' => false, 'readonly' => true])
            ?>
        </div>
        <div class="col-sm-2">
            <?= $form
                ->field($model, 'actual_to')
                ->widget(DateControl::class, [
                    'pluginOptions' => [
                        'startDate' => 'today',
                    ],
                ])
            ?>
        </div>
        <div class="col-sm-2">
            <?= $form
                ->field($model, 'transit_price')
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-4">
            <?= $form->field($model, 'ip')->textInput() ?>
        </div>
        <div class="col-sm-4">
            <div class="col-sm-4">
                <?= Html::label('Оператор:') . ' (' . Html::a($clientAccount->id, ['client/view', 'id' => $clientAccount->id]) . ') ' . $clientAccount->company ?>
            </div>
            <div class="col-sm-4">
                <?= $form->field($model, 'orig_enabled')->checkbox()->label('') ?>
            </div>
            <div class="col-sm-4">
                <?= $form->field($model, 'term_enabled')->checkbox()->label('') ?>
            </div>
        </div>
        <div class="col-sm-4">
            <?= $form->field($model, 'description')->textInput() ?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <?= $form->field($model, 'comment')->textarea() ?>
        </div>
    </div>
    <?php
    if ($usage->isActive()) :

        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'attributes' => [
                'actions' => [
                    'type' => Form::INPUT_RAW,
                    'value' =>
                        Html::tag(
                            'div',
                            ($isUu ?
                                Html::a('<span class="glyphicon glyphicon-magnet" aria-hidden="true"></span> УУ', ['/uu/account-tariff/', 'serviceTypeId' => ServiceType::ID_TRUNK]) : // УУ
                                '') .
                            Html::button('Отменить', [
                                'class' => 'btn btn-link',
                                'style' => 'margin-right: 15px;',
                                'onClick' => 'self.location = "' . Url::toRoute(['/', 'module' => 'services', 'action' => 'trunk_view']) . '";',
                            ]) .
                            Html::button('Сохранить', ['class' => 'btn btn-primary', 'onClick' => "submitForm('edit')"]),
                            ['style' => 'text-align: right; padding-right: 0px;']
                        )
                ],
            ],
        ]);

    endif;

    echo Html::hiddenInput('scenario', 'default', ['id' => 'scenario']);
    ActiveForm::end();
    ?>
    <div class="row" style="margin-bottom: 50px;">
        <div class="col-sm-12 form-group">
            <?= $this->render('//layouts/_showHistory', ['model' => $usage]) ?>
        </div>
    </div>
    <?php
        // Получение и перестройка необходимых данных с учетом связей транков, правил транков и префикс-листов
        $relations = AuthTrunk::getRulesAndPrefixlistRelations([$trunk->id]);
    ?>
    <?php if ($usage->orig_enabled) : ?>
        <div class="row">
            <div class="col-sm-2">
                <h2>Оригинация:</h2>
            </div>
            <div class="col-sm-4">
                <?= TagsSelect2::widget([
                    'model' => $model->getModel(),
                    'attribute' => 'tagsOrig',
                    'feature' => 'orig_enabled',
                    'label' => 'Тип транка',
                ]) ?>
            </div>
            <div class="col-sm-4">
                <label class="control-label">Группы для оригинации</label>
                <div id="orig_trunk_group"></div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div><b>Номер A</b></div>
                <?= AuthTrunk::graphicDistributionOfRules($relations, true, false) ?>
            </div>
            <div class="col-sm-6">
                <div><b>Номер B</b></div>
                <?= AuthTrunk::graphicDistributionOfRules($relations, true, true) ?>
            </div>
        </div>

        <table class="table table-condensed table-striped">
            <tr>
                <th width="26%">A номер</th>
                <th width="26%">B номер</th>
                <th width="33%"><?= $isUu ? 'Пакет' : 'Прайслист' ?></th>
                <th width="14%" colspan="2">Ограничение по минимальной марже</th>
                <th></th>
            </tr>
            <?php foreach ($origination as $rule): ?>
                <?php
                $formModel = new UsageTrunkSettingsEditForm();
                $formModel->setAttributes($rule->attributes, false);

                /** @var ActiveForm $form */
                $form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL, 'options' => ['style' => 'margin-bottom: 10px;']]);
                echo Html::activeHiddenInput($formModel, 'id');
                ?>
                <tr>
                    <td><?= $form->field($formModel, 'src_number_id', ['options' => ['class' => ''], 'errorOptions' => ['class' => '']])->label(false)->dropDownList($srcNumbers, ['class' => 'select2']) ?></td>
                    <td><?= $form->field($formModel, 'dst_number_id', ['options' => ['class' => ''], 'errorOptions' => ['class' => '']])->label(false)->dropDownList($dstNumbers, ['class' => 'select2']) ?></td>
                    <td>
                        <?=
                        $isUu ?
                            // пакет
                            $form->field($formModel, 'package_id', ['options' => ['class' => ''], 'errorOptions' => ['class' => '']])->label(false)
                                ->dropDownList($origPackages, ['class' => 'select2 package_with_link', 'data' => ['setting-id' => $formModel->id]]) :
                            // прайслист
                            $form->field($formModel, 'pricelist_id', ['options' => ['class' => ''], 'errorOptions' => ['class' => '']])->label(false)
                                ->dropDownList($origPricelists, ['class' => 'select2 pricelist_with_link', 'data' => ['setting-id' => $formModel->id]])
                        ?>
                        <?= Html::a('Цены', ['#'], ['class' => 'usage_trunk_pricelist_link', 'style' => 'display: none', 'id' => 'link_for_pricelist' . $formModel->id]) ?>
                    </td>
                    <td><?= $form->field($formModel, 'minimum_margin', ['options' => ['class' => ''], 'errorOptions' => ['class' => '']])->label(false)->textInput(['style' => 'min-width: 105px']) ?></td>
                    <td><?= $form->field($formModel, 'minimum_margin_type', ['options' => ['class' => ''], 'errorOptions' => ['class' => '']])->label(false)->dropDownList([
                            UsageTrunkSettings::MIN_MARGIN_ABSENT => 'нет',
                            UsageTrunkSettings::MIN_MARGIN_VALUE => 'денег',
                            UsageTrunkSettings::MIN_MARGIN_PERCENT => '%'
                        ], ['style' => 'min-width: 90px']) ?></td>
                    <td><?= $usage->isActive() ? Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) : ''; ?></td>
                </tr>
                <tr>
                    <td colspan="5">
                        <?= $this->render('//layouts/_showHistory', ['model' => $rule]); ?>
                    </td>
                </tr>
                <?php
                ActiveForm::end();
                ?>
            <?php endforeach; ?>
            <?php
            if ($usage->isActive()) :
                $formModel = new UsageTrunkSettingsAddForm();
                $formModel->usage_id = $usage->id;
                $formModel->type = UsageTrunkSettings::TYPE_ORIGINATION;

                /** @var ActiveForm $form */
                $form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL, 'options' => ['style' => 'margin-bottom: 10px;']]);
                echo Html::activeHiddenInput($formModel, 'usage_id');
                echo Html::activeHiddenInput($formModel, 'type');
                ?>
                <tr>
                    <td colspan="3"></td>
                    <td colspan="2"><?= ($isUu ? Html::a('<span class="glyphicon glyphicon-magnet" aria-hidden="true"></span> Добавить УУ-пакет', ['/uu/account-tariff/', 'serviceTypeId' => ServiceType::ID_TRUNK]) : '') ?></td>
                    <td><?= Html::submitButton('Добавить', ['class' => 'btn btn-primary btn-sm']); ?></td>
                    <td></td>
                </tr>
                <?php
                ActiveForm::end();
            endif;
            ?>
        </table>
    <?php endif; ?>

    <?php if ($usage->term_enabled) : ?>
        <div class="row">
            <div class="col-sm-2">
                <h2>Терминация:</h2>
            </div>
            <div class="col-sm-4">
                <?= TagsSelect2::widget([
                    'model' => $model->getModel(),
                    'attribute' => 'tagsTerm',
                    'feature' => 'term_enabled',
                    'label' => 'Тип транка',
                ]) ?>
            </div>
            <div class="col-sm-4">
                <label class="control-label">Группы для терминации</label>
                <div id="term_trunk_group"></div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div><b>Номер A</b></div>
                <?= AuthTrunk::graphicDistributionOfRules($relations, false, false) ?>
            </div>
            <div class="col-sm-6">
                <div><b>Номер B</b></div>
                <?= AuthTrunk::graphicDistributionOfRules($relations, false, true) ?>
            </div>
        </div>

        <table class="table table-condensed table-striped">
            <tr>
                <th width="33%">A номер</th>
                <th width="33%">B номер</th>
                <th width="33%"><?= $isUu ? 'Пакет' : 'Прайслист' ?></th>
                <th width="10%">Квота, минут</th>
                <th width="10%">Минимальный платеж</th>
                <th></th>
                <th></th>
            </tr>
            <?php foreach ($termination as $rule): ?>
                <?php
                $formModel = new UsageTrunkSettingsEditForm();
                $formModel->setAttributes($rule->attributes, false);

                /** @var ActiveForm $form */
                $form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL, 'options' => ['style' => 'margin-bottom: 10px;']]);
                echo Html::activeHiddenInput($formModel, 'id');
                ?>
                <tr>
                    <td><?= $form->field($formModel, 'src_number_id', ['options' => ['class' => ''], 'errorOptions' => ['class' => '']])->label(false)->dropDownList($srcNumbers, ['class' => 'select2']) ?></td>
                    <td><?= $form->field($formModel, 'dst_number_id', ['options' => ['class' => ''], 'errorOptions' => ['class' => '']])->label(false)->dropDownList($dstNumbers, ['class' => 'select2']) ?></td>
                    <td>
                        <?=
                        $isUu ?
                            // пакет
                            $form->field($formModel, 'package_id', ['options' => ['class' => ''], 'errorOptions' => ['class' => '']])->label(false)
                                ->dropDownList($termPackages, ['class' => 'select2 package_with_link', 'data' => ['setting-id' => $formModel->id]]) :
                            // прайслист
                            $form->field($formModel, 'pricelist_id', ['options' => ['class' => ''], 'errorOptions' => ['class' => '']])->label(false)
                                ->dropDownList($termPricelists, ['class' => 'select2 pricelist_with_link', 'data' => ['setting-id' => $formModel->id]])
                        ?>
                        <?= Html::a('Цены', ['#'], ['class' => 'usage_trunk_pricelist_link', 'style' => 'display: none', 'id' => 'link_for_pricelist' . $formModel->id]) ?>
                    </td>
                    <td><?= $form->field($formModel, 'minimum_minutes', ['options' => ['class' => ''], 'errorOptions' => ['class' => '']])->label(false)->textInput(['style' => 'min-width: 80px']) ?></td>
                    <td><?= $form->field($formModel, 'minimum_cost', ['options' => ['class' => ''], 'errorOptions' => ['class' => '']])->label(false)->textInput(['style' => 'min-width: 80px']) ?></td>
                    <td><?= $usage->isActive() ? Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) : ''; ?></td>
                </tr>
                <tr>
                    <td colspan="5">
                        <?= $this->render('//layouts/_showHistory', ['model' => $rule]); ?>
                    </td>
                </tr>
                <?php
                ActiveForm::end();
                ?>
            <?php endforeach; ?>
            <?php
            if ($usage->isActive()) :
                $formModel = new UsageTrunkSettingsAddForm();
                $formModel->usage_id = $usage->id;
                $formModel->type = UsageTrunkSettings::TYPE_TERMINATION;

                /** @var ActiveForm $form */
                $form = ActiveForm::begin();
                echo Html::activeHiddenInput($formModel, 'usage_id');
                echo Html::activeHiddenInput($formModel, 'type');
                ?>
                <tr>
                    <td colspan="3"></td>
                    <td colspan="2"><?= ($isUu ? Html::a('<span class="glyphicon glyphicon-magnet" aria-hidden="true"></span> Добавить УУ-пакет', ['/uu/account-tariff/', 'serviceTypeId' => ServiceType::ID_TRUNK]) : '') ?></td>
                    <td><?= Html::submitButton('Добавить', ['class' => 'btn btn-primary btn-sm']); ?></td>
                </tr>
                <?php
                ActiveForm::end();
            endif;
            ?>
        </table>
    <?php endif; ?>

    <h2>Направления:</h2>
    <table class="table table-condensed table-striped">
        <tr>
            <th width="100%">B номер</th>
            <th></th>
        </tr>
        <?php foreach ($destination as $rule): ?>
            <?php
            $formModel = new UsageTrunkSettingsEditForm();
            $formModel->setAttributes($rule->attributes, false);

            /** @var ActiveForm $form */
            $form = ActiveForm::begin();
            echo Html::activeHiddenInput($formModel, 'id');
            ?>
            <tr>
                <td><?= $form->field($formModel, 'dst_number_id', ['options' => ['class' => ''], 'errorOptions' => ['class' => '']])->label(false)->dropDownList($dstNumbers, ['class' => 'select2']) ?></td>
                <td><?= $usage->isActive() ? Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) : ''; ?></td>
            </tr>
            <tr>
                <td colspan="5">
                    <?= $this->render('//layouts/_showHistory', ['model' => $rule]); ?>
                </td>
            </tr>
            <?php
            ActiveForm::end();
            ?>
        <?php endforeach; ?>
        <?php
        if ($usage->isActive()) :
            $formModel = new UsageTrunkSettingsAddForm();
            $formModel->usage_id = $usage->id;
            $formModel->type = UsageTrunkSettings::TYPE_DESTINATION;

            /** @var ActiveForm $form */
            $form = ActiveForm::begin();
            echo Html::activeHiddenInput($formModel, 'usage_id');
            echo Html::activeHiddenInput($formModel, 'type');
            ?>
            <tr>
                <td></td>
                <td><?= Html::submitButton('Добавить', ['class' => 'btn btn-primary btn-sm']); ?></td>
            </tr>
            <?php
            ActiveForm::end();
        endif;
        ?>
    </table>

    <?php if ($usage->isActive()) : ?>
        <h2><span style="border-bottom: 1px dotted #000; cursor: pointer;" onclick="$('#div_close').toggle()">Отключение услуги:</span></h2>
        <div id="div_close" style="display: none">
            <?php
            $formModel = new UsageTrunkCloseForm();
            $formModel->usage_id = $usage->id;

            /** @var ActiveForm $form */
            $form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL, 'options' => ['style' => 'margin-bottom: 10px;']]);
            echo Html::activeHiddenInput($formModel, 'usage_id');
            echo Form::widget([
                'model' => $formModel,
                'form' => $form,
                'columns' => 3,
                'attributes' => [
                    'actual_to' => ['type' => Form::INPUT_WIDGET, 'widgetClass' => DateControl::class],
                    'x2' => [
                        'type' => Form::INPUT_RAW,
                        'value' => '
                                    <div style="padding-top: 22px">
                                        ' . Html::submitButton('Установить дату отключения', ['class' => 'btn btn-primary']) . '
                                    </div>
                                '
                    ],
                ],
            ]);
            ActiveForm::end();
            ?>
        </div>
    <?php endif; ?>
</div>

<?php
/**
 * Свойства услуги для телефонии
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\AccountTariffForm $formModel
 * @var bool $isReadOnly
 */

use app\assets\AppAsset;
use app\classes\Html;
use app\models\City;
use app\models\ClientAccount;
use app\modules\sim\models\CardStatus;
use app\models\Country;
use app\models\DidGroup;
use app\models\filter\FreeNumberFilter;
use app\models\Number;
use app\modules\nnp\models\NdcType;
use app\modules\uu\forms\AccountTariffAddForm;
use app\modules\uu\models\ServiceType;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Url;

$clientAccount = $formModel->accountTariff->clientAccount;
$accountTariffVoip = $formModel->accountTariffVoip;

$this->registerJsFile('@web/js/uu/accountTariffEdit.js', ['depends' => [AppAsset::class]]);
?>

<?php $form = ActiveForm::begin([
    'id' => 'addAccountTariffVoipForm',
]); ?>

    <div class="row">

        <?= Html::hiddenInput('', ServiceType::ID_VOIP, ['id' => 'voipServiceTypeId']) ?>
        <?= Html::hiddenInput('', $formModel->accountTariff->clientAccount->currency, ['id' => 'voipCurrency']) ?>
        <?= Html::hiddenInput('', $clientAccount->is_voip_with_tax, ['id' => 'isIncludeVat']) ?>
        <?= Html::hiddenInput('', $clientAccount->contract->organization_id, ['id' => 'organizationId']) ?>

        <div class="col-sm-2">
            <?php // страна ?>
            <?= $form->field($accountTariffVoip, 'voip_country_id')
                ->widget(Select2::class, [
                    'data' => Country::getList(false),
                    'options' => [
                        'id' => 'voipCountryId',
                    ],
                ])
                ->label('Страна *') ?>
        </div>

        <div class="col-sm-2">
            <?php // город ?>
            <?= $form->field($accountTariffVoip, 'city_id')
                ->widget(Select2::class, [
                    'data' => City::getList($isWithEmpty = true, $accountTariffVoip->voip_country_id), // страна выбрана от клиента
                    'options' => [
                            'id' => 'voipRegions',
                        ] +
                        ($accountTariffVoip->voip_country_id ?
                            [] :
                            ['disabled' => true]
                        ),
                ]) ?>
        </div>

        <div class="col-sm-2">
            <?php // тип номера ?>
            <?= $form->field($accountTariffVoip, 'voip_ndc_type_id')
                ->widget(Select2::class, [
                    'data' => NdcType::getList($isWithEmpty = true, $isWithNullAndNotNull = false, (bool)$accountTariffVoip->city_id),
                    'options' => [
                        'id' => 'voipNdcType',
                    ],
                ]) ?>
        </div>

        <div class="col-sm-2">
            <?php // тип красивости ?>
            <?= $form->field($accountTariffVoip, 'voip_did_group')
                ->widget(Select2::class, [
                    'data' => DidGroup::getList(true, $accountTariffVoip->voip_country_id, $accountTariffVoip->city_id ?: -1, $accountTariffVoip->voip_ndc_type_id),
                    'options' => [
                            'id' => 'voipDidGroup',
                        ] +
                        ($accountTariffVoip->voip_country_id ?
                            [] :
                            ['disabled' => true]
                        ),
                ])
                ->label('DID-группа *') ?>
        </div>

        <div class="col-sm-4">
            <?php // оператор ?>
            <?php
            $numbersTmp = new FreeNumberFilter;
            $numbersTmp->setCountry($accountTariffVoip->voip_country_id);
            $accountTariffVoip->city_id && $numbersTmp->setCity($accountTariffVoip->city_id);

            $operatorAccounts = ClientAccount::getListWithContragent(
                (int)$isWithEmpty,
                $isWithNullAndNotNull = false,
                $indexBy = 'id',
                $orderBy = ['id' => SORT_ASC],
                $where = ['id' => $numbersTmp->getDistinct('operator_account_id')]
            );
            ?>
            <?= $form->field($accountTariffVoip, 'operator_account_id')
                ->widget(Select2::class, [
                    'data' => $operatorAccounts,
                    'options' => [
                            'id' => 'voipOperatorAccount',
                        ] +
                        ($accountTariffVoip->voip_country_id ?
                            [] :
                            ['disabled' => true]
                        ),
                ]) ?>
        </div>

    </div>

<?php // фильтры списка номеров ?>
    <div class="row">

        <div class="col-sm-2">
            <?php // кол-во столбцов ?>
            <?= $form->field($accountTariffVoip, 'voip_numbers_list_class')
                ->widget(Select2::class, [
                    'data' => [ // класс bootstrap, соотвествующий кол-ву столбцов
                        12 => 1,
                        6 => 2,
                        4 => 3,
                        3 => 4,
                        2 => 6,
                        1 => 12,
                    ],
                    'options' => [
                        'id' => 'voipNumbersListClass',
                    ],
                ]) ?>
        </div>

        <div class="col-sm-2">
            <?php // сортировка (поле) ?>
            <?php $number = new Number(); ?>
            <?= $form->field($accountTariffVoip, 'voip_numbers_list_order_by_field')
                ->widget(Select2::class, [
                    'data' => [
                        'number' => $number->getAttributeLabel('number'),
                        'beauty_level' => $number->getAttributeLabel('beauty_level'),
                    ],
                    'options' => [
                        'id' => 'voipNumbersListOrderByField',
                    ],
                ]) ?>
        </div>

        <div class="col-sm-2">
            <?php // сортировка (тип) ?>
            <?= $form->field($accountTariffVoip, 'voip_numbers_list_order_by_type')
                ->widget(Select2::class, [
                    'data' => [
                        SORT_ASC => Yii::t('common', 'Ascending'),
                        SORT_DESC => Yii::t('common', 'Descending'),
                    ],
                    'options' => [
                        'id' => 'voipNumbersListOrderByType',
                    ],
                ]) ?>
        </div>

        <div class="col-sm-2">
            <?php // лимит ?>
            <?= $form->field($accountTariffVoip, 'voip_numbers_list_limit')
                ->input('integer', [
                    'id' => 'voipNumbersListLimit',
                ]) ?>
        </div>

        <div class="col-sm-4">
            <?php // маска ?>
            <?= $this->render('//layouts/_helpMysqlLike'); ?>
            <?= $form->field($accountTariffVoip, 'voip_numbers_list_mask')
                ->input('string', [
                    'id' => 'voipNumbersListMask',
                ]) ?>
        </div>

        <div class="col-sm-4" id="voipNumbersWarehouseStatusField" style="display: none;">
            <?php
                $statuses = CardStatus::getList($isWithEmpty = true, $isWithNullAndNotNull = false);
                // Добавление статуса "Отсутствует на складе".
                $statuses[Number::STATUS_WAREHOUSE_NO_RELATION] = 'Не привязан к сим-карте (вне склада)';
                echo $form->field($accountTariffVoip, 'voip_numbers_warehouse_status')
                    ->widget(Select2::class, [
                        'data' => $statuses,
                        'options' => [
                            'id' => 'voipNumbersWarehouseStatus',
                        ],
                    ])
            ?>
        </div>
    </div>

<?php // чекбокс "выбрать все" ?>
    <div id="voipNumbersListSelectAll" class="collapse">
        <?= Html::checkbox('voipNumbersListSelectAll', false, [
            'label' => Yii::t('common', 'Select all'),
        ]) ?>
    </div>


<?php // список номеров ?>
    <div id="voipNumbersList" class="alert"></div>
    <br/>


<?php // тариф ?>
    <div id="voipTariffDiv" class="collapse">
        <?= $this->render('_editLogInput', [
            'formModel' => $formModel,
            'form' => $form,
            'accountTariffVoip' => $accountTariffVoip
        ]) ?>
    </div>

    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => Url::to(['/uu/account-tariff', 'serviceTypeId' => $formModel->serviceTypeId])]) ?>
        <?= $this->render('//layouts/_submitButton', [
            'text' => Yii::t('common', 'Create'),
            'glyphicon' => 'glyphicon-save',
            'params' => [
                'class' => 'btn btn-primary',
                'id' => 'submit-button'
            ] + (($formModel instanceof AccountTariffAddForm) && ($formModel->isShowRoistatVisit() && $formModel->accountTariff->isNewRecord) ? ['disabled' => ''] : []),
        ]) ?>
    </div>

<?php ActiveForm::end();

<?php
/**
 * Мониторинг: СОРМ: Клиенты
 *
 * @var app\classes\BaseView $this
 * @var SormClientFilter $filterModel
 */

use app\classes\BillContract;
use app\classes\grid\column\DataColumn;
use app\classes\grid\column\universal\RegionColumn;
use app\classes\grid\GridView;
use app\models\ClientAccount;
use app\models\ClientContragent;
use app\models\filter\SormClientFilter;
use yii\widgets\Breadcrumbs;
use app\models\User;

?>

<?= app\classes\Html::formLabel($this->title = 'СОРМ: Клиенты') ?>
<?= Breadcrumbs::widget([
    'links' => [
        ['label' => $this->title, 'url' => '/monitoring/sorm-clients/'],
    ],
]) ?>

<?php

function hlValue(\app\models\ClientContragentPerson $model, $field, $isForceError = false)
{
    return $model->{$field} ?
        _hlHtml($model->{$field}, $isForceError)
        : _hlHtml($model->getAttributeLabel($field), true);
}

function _hlHtml($value, $isError)
{
    return !$isError ? $value : \app\classes\Html::tag('span', $value, ['style' => ['background-color' => 'red', 'color' => 'white', 'padding' => '0px 5px 0px 5px', 'border' => '1px solid #E9967A']]);
}

$baseView = $this;
$columns = [
    [
        'attribute' => 'id',
        'value' => function (ClientAccount $account) {
            return $account->getLink();
        },
        'format' => 'raw',
    ],
    [
        'label' => $filterModel->getAttributeLabel('name_full'),
        'format' => 'html',
        'value' => function (ClientAccount $account) {
            $returnStr = $account->contragent->name_full;

            if ($account->contragent->legal_type == ClientContragent::PERSON_TYPE) {
                $person = $account->contragent->person;

                $returnStr = hlValue($person, 'last_name') .
                    '*' . hlValue($person, 'first_name') .
                    '*' . hlValue($person, 'middle_name') .
                    '<br>' . hlValue($person, 'birthday') .
                    '*' . hlValue($person, 'passport_serial', strlen($person->passport_serial) != 4 || !preg_match('/^\d+$/', $person->passport_serial)) .
                    '*' . hlValue($person, 'passport_number', strlen($person->passport_number) != 6 || !preg_match('/^\d+$/', $person->passport_number)) .
                    '<br>' . hlValue($person, 'passport_issued', strlen($person->passport_number) < 6) .
                    '<br>' . hlValue($person, 'passport_date_issued');
            }

            return $returnStr;
        }
    ],
    [
        'label' => $filterModel->getAttributeLabel('account_manager'),
        'value' => 'clientContractModel.accountManagerName'
    ],
    [
        'attribute' => 'voip_credit_limit_day',
    ],
    [
        'label' => $filterModel->getAttributeLabel('legal_type'),
        'value' => function (ClientAccount $account) {
            return $account->contragent->legal_type;
        }
    ],
    [
        'label' => $filterModel->getAttributeLabel('contract_no'),
        'value' => function (ClientAccount $account) {
            $contractInfo = BillContract::getLastContract($account->contract_id, null, false);
            if ($contractInfo && isset($contractInfo['no'])) {
                return $contractInfo['no'];
            }
            return $account->contract_id;
        },
    ],
    [
        'label' => $filterModel->getAttributeLabel('inn'),

        'value' => function (ClientAccount $account) {
            return $account->contragent->inn;
        },
        'contentOptions' => function (ClientAccount $account) {
            $options = [
                'class' => 'sorm-client-cell',
                'data' => ['field' => 'inn']
            ];

            if ($account->contragent->legal_type != ClientContragent::PERSON_TYPE && !$account->contragent->inn) {
                $options['style'] = ['background-color' => 'red'];
            }

            return $options;
        }
    ],
    [
        'label' => $filterModel->getAttributeLabel('bik'),
        'contentOptions' => function (ClientAccount $account) {
            $options = [
                'class' => 'sorm-client-cell',
                'data' => ['field' => 'bik']
            ];

            if ($account->contragent->legal_type != ClientContragent::PERSON_TYPE && !$account->bik) {
                $options['style'] = ['background-color' => 'red'];
            }

            return $options;
        },
        'value' => 'bik',
    ], [
        'label' => $filterModel->getAttributeLabel('bank'),
        'value' => 'bank_name',
        'contentOptions' => function (ClientAccount $account) {
            $options = [];

            if ($account->contragent->legal_type != ClientContragent::PERSON_TYPE && !$account->bank_name) {
                $options['style'] = ['background-color' => 'red'];
            }

            return $options;
        }
    ],
    [
        'attribute' => 'pay_acc',
        'value' => 'pay_acc',
        'contentOptions' => function (ClientAccount $account) {
            $options = [
                'class' => 'sorm-client-cell',
                'data' => ['field' => 'pay_acc']
            ];

            if ($account->contragent->legal_type != ClientContragent::PERSON_TYPE && !$account->pay_acc) {
                $options['style'] = ['background-color' => 'red'];
            }

            return $options;
        }
    ],
    [
        'label' => $filterModel->getAttributeLabel('contact_fio'),

        'value' => function (ClientAccount $account) use ($filterModel) {
            /** @var \app\models\ClientContact $contact */
            $contact = SormClientFilter::getContactByAccount($account);

            if ($contact) {
                return $contact->comment;
            }

            return '';
        },
        'contentOptions' => function (ClientAccount $account) {
            $options = [
                'class' => 'sorm-client-cell',
                'data' => ['field' => 'contact_fio']
            ];

            if (!($contact = SormClientFilter::getContactByAccount($account)) || !$contact->comment) {
                $options['style'] = ['background-color' => 'red'];
            }

            return $options;
        }
    ],
    [
        'label' => $filterModel->getAttributeLabel('contact_phone'),
        'value' => function (ClientAccount $account) use ($contactWhere, $contactOrderBy) {
            /** @var \app\models\ClientContact $contact */
            $contact = SormClientFilter::getContactByAccount($account);

            if ($contact) {
                return $contact->data;
            }

            return '';
        },
        'contentOptions' => function (ClientAccount $account) {
            $options = [
                'class' => 'sorm-client-cell',
                'data' => ['field' => 'contact_phone']
            ];

            if (!($contact = SormClientFilter::getContactByAccount($account)) || !$contact->data) {
                $options['style'] = ['background-color' => 'red'];
            }

            return $options;
        }
    ],
    [
        'label' => $filterModel->getAttributeLabel('address_jur'),
        'value' => function (ClientAccount $account) {
            /** @var \app\models\ClientContact $contact */
            return
                $account->contragent->legal_type == ClientContragent::PERSON_TYPE
                    ? $account->contragent->person->registration_address
                    : $account->contragent->address_jur;
        },
        'contentOptions' => function (ClientAccount $account) {
            $options = [
                'class' => 'sorm-client-cell',
                'data' => ['field' => 'address_jur']
            ];

            if (!($account->contragent->legal_type == ClientContragent::PERSON_TYPE
                ? $account->contragent->person->registration_address
                : $account->contragent->address_jur)) {
                $options['style'] = ['background-color' => 'red'];
            }

            return $options;
        }
    ],
    [
        'attribute' => 'address_post',
        'contentOptions' => function (ClientAccount $account) {
            $options = [
                'class' => 'sorm-client-cell',
                'data' => ['field' => 'address_post']
            ];

            if ($account->contragent->legal_type != ClientContragent::PERSON_TYPE && !$account->address_post) {
                $options['style'] = ['background-color' => 'red'];
            }

            return $options;
        }
    ],

    [
        'label' => 'Польз. обордв.',
        'contentOptions' => function (ClientAccount $account) {
            $options = [
            ];

            if ($account->getEquipmentUsers()->count() == 0) {
                $options['style'] = ['background-color' => 'red'];
            }

            return $options;
        },
        'format' => 'raw',
        'value' => function (ClientAccount $account) {

            $eqUser = $account
                ->getEquipmentUsers()
                ->select(new \yii\db\Expression('group_concat(full_name SEPARATOR ", ")'))->scalar();

            if ($eqUser) {
                return $eqUser;
            }

            $fio = $account->contragent->fio;

            if (!$fio && $account->contragent->legal_type == ClientContragent::PERSON_TYPE) {
                $person = $account->contragent->personModel;
                if (!$person->last_name || !$person->middle_name || !$person->first_name) {
                    return '';
                }

                $fio = $person->last_name . ' ' . $person->first_name . ' ' . $person->middle_name;
            }

            if (!$fio && $account->contragent->legal_type == ClientContragent::IP_TYPE) {
                $fio = preg_replace('/^s*ИП\s+/u', '', $account->contragent->name_full);
            }

            if (preg_match('/^\s*[А-Я][а-яё]+\s+[А-Я][а-яё]+\s+[А-Я][а-яё]+\s*$/u', $fio)) {
                return $this->render('//layouts/_button', [
                    'text' => $fio,
                    'glyphicon' => 'glyphicon glyphicon-save',
                    'params' => [
                        'class' => 'btn btn-danger btn-sm sorm-save-equ',
                        'data' => ['account_id' => $account->id]
                    ],
                ]);
            }

            return $fio ? _hlHtml($fio, true) : '';
        }
    ]
];

$filterColumns = [
    [
        'attribute' => 'region_id',
        'class' => RegionColumn::class,
    ],
    [
        'attribute' => 'account_manager',
        'filterType' => GridView::FILTER_SELECT2,
        'filter' => User::getAccountManagerList(true),
        'class' => DataColumn::class
    ],
    [
        'attribute' => 'filter_by',
        'filterType' => GridView::FILTER_SELECT2,
        'filter' => SormClientFilter::$filterList,
        'class' => DataColumn::class
    ],
    [
        'attribute' => 'is_with_error',
        'class' => DataColumn::class,
        'filterType' => GridView::FILTER_SELECT2,
        'filter' => SormClientFilter::$errList,
    ],
];

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,
    'beforeHeader' => [ // фильтры вне грида
        'columns' => $filterColumns,
    ],
]);
